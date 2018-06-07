<?php

/*
    通用cpd_month_data_db处理 worker
*/
// include dirname(__FILE__).'/../init.php';
require_once(dirname(__FILE__).'/../../init.php');
ini_set('default_socket_timeout', -1);
load_helper('task');
$worker = get_task_worker();
$worker->addFunction("cpd_month_data_db", "handle");
while ($worker->work());
// $jobs=array('contract_id'=>102,'bill_month'=>'2016-12');
// handle($jobs);
//work执行开始函数
function handle($jobs){    
    $param = $jobs->workload();
	$param = json_decode($param,true);
	// $param=$jobs;
	$contract_id = $param['contract_id'];
	// //本月数据不处理
	// if($param['bill_month']==date('Y-m',time())){
	// 	return;
	// }
	$bill_month_begin = strtotime($param['bill_month']);
	$bill_month_end = strtotime(date('Y-m',time()));
	if(!$contract_id){
		return;
	}
	cpd_writelog("\n".'通用cpd_month_data_db处理:handle::::本次合同id'.$contract_id.'---本次流程开始');
	$model = new GoModel();
    
	$contract_data=sync_query('cpd_contract',array('id'=>$contract_id,'flexible_sys'=>1));
	if(strtotime(date('Y-m',$contract_data['start_tm']))<$bill_month_begin){
		$bill_month_begin=strtotime(date('Y-m',$contract_data['start_tm']));
	}

	$option = array(
	    'table'=>'cpd_prestore',
	    'field'=>"*",
	    'order'=>'prestores_tm asc',
	    'where'=>array('contract_id'=>$contract_id,'status'=>1),

    );
    $prestore_data = $model->findOne($option,'master');

	if(strtotime(date('Y-m',$prestore_data['prestores_tm']))<$bill_month_begin){
		$bill_month_begin=strtotime(date('Y-m',$prestore_data['prestores_tm']));
	}
	if(!$contract_data){		
		return;
	}
    $contract_data_b=sync_query('cpd_contract',array('flexible_c_code'=>$contract_data['contract_code']));

	if(!$contract_data_b){		
		return;
	}
	for($i=$bill_month_begin;$i<=$bill_month_end;$i+=(date('t',$i))*86400){
		//处理合同的这个月的数据
		sync_data_a_b(array('contract_id'=>$contract_id,'softname'=>$contract_data['softname'],'contract_id_b'=>$contract_data_b['id'],'month_begin'=>$i,'month_end'=>($i+(date('t',$i))*86400-86400)));
	}
	deal_month_prestore(array('contract_id'=>$contract_id,'bs'=>1,'softname'=>$contract_data['softname'],'contract_id_b'=>$contract_data_b['id'],'month_begin'=>$bill_month_end,'month_end'=>($bill_month_end+(date('t',$bill_month_end))*86400-86400)));
	deal_month_balance(array('contract_id'=>$contract_id,'bs'=>1,'softname'=>$contract_data['softname'],'contract_id_b'=>$contract_data_b['id'],'month_begin'=>$bill_month_end,'month_end'=>($bill_month_end+(date('t',$bill_month_end))*86400-86400)));
	cpd_writelog("\n".'通用cpd_month_data_db处理:handle::::本次合同id'.$contract_id.'---本次流程结束');
}
//同步数据
//需要同步以下数据表的数据
//cpd_balance_change,cpd_download_config,cpd_expend,cpd_prestore
function sync_data_a_b($data){
	//处理计费限额和月消耗
	deal_month_expend($data);
	//处理余额管理
	deal_month_balance($data);
	//处理预存/收款/发票/转出/转入
	deal_month_prestore($data);
}
//简单的通用查询 

function sync_query($table,$where){
	$model = new GoModel();
	$option = array(
	    'table'=>$table,
	    'field'=>"*"
    );
    foreach($where as $k=>$v){
    	$option['where'][$k]=$v;
    }
    $data = $model->findOne($option,'master');
    cpd_writelog('sync_query::::'.$model->getSql());
    return $data;
}
function get_task_id($contract_id_b,$month_begin,$month_end){
	$model = new GoModel();
	$option = array(
	    'table'=>'cpd_task',
	    'field'=>"*"
    );
    $option['where']['contract_id']=$contract_id_b;
    $option['where']['status']=1;
    $option['where']['start_tm']=array('exp',"<=$month_begin && end_tm>=$month_end");
    $task_data = $model->findOne($option,'master');
    cpd_writelog('get_task_id::::'.$model->getSql());
    cpd_writelog('get_task_id::::'.$task_data['task_id']);
    return $task_data['task_id'];
}
//处理计费限额和月消耗
function deal_month_expend($data){
	$model = new GoModel();
	$contract_id=$data['contract_id'];
	$softname=$data['softname'];
	$contract_id_b=$data['contract_id_b'];
	$month_begin=$data['month_begin'];
	$month_end=$data['month_end'];

	$option = array(
	    'table'=>'cpd_expend',
	    'field'=>"*"
    );
    $option['where']['contract_id']=$contract_id;
    $option['where']['status']=1;
    $option['where']['expend_tm']=array('exp',"<=$month_end && expend_tm>=$month_begin");
    $expend_data = $model->findAll($option,'master');
    cpd_writelog('deal_month_expend::::'.$model->getSql());
    // cpd_writelog('deal_month_expend::::'.var_export($expend_data,true));
    if(!$expend_data){		
		return;
	}
	if(date('Y-m')==date('Y-m',$month_begin)){
    	return;
    }
	$task_id=get_task_id($contract_id_b,$month_begin,$month_end);

	//添加
    $options = array(
	    '__user_table' => 'cpd_expend',
	    'download_count' => '',
	    'download_total' => '',
	    'price'=>'',      
	    'download_invalid'=>'',    
	    'nature_num'=>'',   
	    'expend_tm' => $month_begin,
	    'add_tm' => $month_begin,
	    'contract_id' => $contract_id_b,
	    'package' => $expend_data[0]['package'],
	    'download_count_anzhi' => '',
	    'download_count_waitou' => '',
	    'task_id' => $task_id,
	    'download_recharge' => '',
	    'download_delivery' => '',
    );
    //添加
    $options_download_config = array(
	    '__user_table' => 'cpd_download_config',
	    'softname' => $softname,
	    'www_downloaded' => '',
	    'm_downloaded'=>'',    
	    'coop_downloaded'=>'',    
	    'other_downloaded'=>'',    
	    'op_tm' => $month_begin,
	    'add_tm' => $month_begin,
	    'flexible_sys' => 2,
	    'finger_play_downloaded' => '',
	    'market_downloaded' => '',
	    'mm_downloaded' => '',
	    'package' => $expend_data[0]['package'],
    );
    //添加
    $options_nature = array(
	    '__user_table' => 'cpd_nature',  
	    'nature_tm' => $month_begin,
	    'contract_id' => $contract_id_b,
	    'add_tm' => $month_begin,
	    'nature_num' => '',
    );
    // //添加余额管理
    // $options_balance = array(
	   //  '__user_table' => 'cpd_balance_change',  
	   //  'contract_id' => $contract_id_b,
	   //  'add_tm' => $month_begin,
	   //  'task_id' => $task_id,
	   //  'type' => '5',
	   //  'recharge_sum' => '',
	   //  'delivery_sum' => '',
    // );
    foreach($expend_data as $k=>$v){
    	$options['download_count']+=$v['download_count'];
    	$options['download_total']+=$v['download_total'];
    	$options['nature_num']+=$v['nature_num'];
    	$options['download_invalid']+=$v['download_invalid'];

    	$options['download_count_anzhi']+=$v['download_count_anzhi'];
    	$options['download_count_waitou']+=$v['download_count_waitou'];
    	$options['download_recharge']+=$v['download_recharge'];
    	$options['download_delivery']+=$v['download_delivery'];
    	//处理计费限额
    	$option_config = array(
        'table'=>'cpd_download_config',
        'order' => 'op_tm desc',
        );
        $option_config['where']['op_tm']=array('exp',"<={$v['expend_tm']}");
        $option_config['where']['package']=$v['package'];
        $download_config_data = $model->findOne($option_config,'master');
        if(!$options_download_config['softname']){
        	$options_download_config['softname']=$download_config_data['softname'];
        }
        $options_download_config['www_downloaded']+=$download_config_data['www_downloaded'];
        $options_download_config['m_downloaded']+=$download_config_data['m_downloaded'];
        $options_download_config['coop_downloaded']+=$download_config_data['coop_downloaded'];
        $options_download_config['other_downloaded']+=$download_config_data['other_downloaded'];
        $options_download_config['finger_play_downloaded']+=$download_config_data['finger_play_downloaded'];
        $options_download_config['market_downloaded']+=$download_config_data['market_downloaded'];
        $options_download_config['mm_downloaded']+=$download_config_data['mm_downloaded'];
    } 
    // $options_balance['recharge_sum']=$options['download_recharge'];
    // $options_balance['delivery_sum']=$options['download_delivery'];
    $options['price']=($options['download_recharge']+$options['download_delivery'])/($options['download_total']-$options['nature_num']-$options['download_invalid']);
    cpd_writelog('deal_month_expend::::'.var_export($options,true));

    $expend_data_old=sync_query('cpd_expend',array('contract_id'=>$contract_id_b,'expend_tm'=>$month_begin,'status'=>1));
    if($expend_data_old){
    	$model->update(array('id'=>$expend_data_old['id']),$options);
    }else{
    	 $model->insert($options);
    }
    
   
    cpd_writelog('deal_month_expend::::'.$model->getSql());
    cpd_writelog('deal_month_expend::::'.var_export($options_download_config,true));

    $download_config_data_old=sync_query('cpd_download_config',array('package'=>$expend_data[0]['package'],'op_tm'=>$month_begin,'status'=>1,'flexible_sys'=>2));
    if($download_config_data_old){
    	$model->update(array('id'=>$download_config_data_old['id']),$options_download_config);
    }else{
    	 $model->insert($options_download_config);
    }
    cpd_writelog('deal_month_expend::::'.$model->getSql());

    $options_nature['nature_num']=$options['nature_num'];
    cpd_writelog('deal_month_expend::::'.var_export($options_nature,true));
    $nature_data_old=sync_query('cpd_nature',array('contract_id'=>$contract_id_b,'nature_tm'=>$month_begin,'status'=>1));
    if($nature_data_old){
    	$model->update(array('id'=>$nature_data_old['id']),$options_nature);
    }else{
    	 $model->insert($options_nature);
    }
    cpd_writelog('deal_month_expend::::'.$model->getSql());


    // $balance_change_data_old=sync_query('cpd_balance_change',array('contract_id'=>$contract_id_b,'add_tm'=>$month_begin,'status'=>1,'type'=>5,'task_id'=>$task_id));
    // if($balance_change_data_old){
    // 	$model->update(array('id'=>$balance_change_data_old['id']),$options_balance);
    // }else{
    // 	 $model->insert($options_balance);
    // }
    // cpd_writelog('deal_month_expend::::'.$model->getSql());
}
//处理余额管理
function deal_month_balance($data){
	$model = new GoModel();
	$contract_id=$data['contract_id'];
	$contract_id_b=$data['contract_id_b'];
	$month_begin=$data['month_begin'];
	$month_end=$data['month_end'];
	$month_end=$month_end+86399;
	//添加
    $options_balance = array(
	    '__user_table' => 'cpd_balance_change',  
	    'contract_id' => $contract_id_b,
	    'add_tm' => $month_begin,
	    // 'stat_tm' => $month_begin,
	    'task_id' => get_task_id($contract_id_b,$month_begin,$month_end-86399),
	    'type' => '',
	    'recharge_sum' => '',
	    'delivery_sum' => '',
    );
    if($data['bs']==1){
    	$type_arr=array('one'=>1,'two'=>2,'three'=>3,'four'=>4);
    }else{
    	if(date('Y-m')==date('Y-m',$month_begin)){
    		$type_arr=array('one'=>1,'two'=>2,'three'=>3,'four'=>4);
    	}else{
    		$type_arr=array('one'=>1,'two'=>2,'three'=>3,'four'=>4,'five'=>5);
    	}
    }
	
	$option = array(
	    'table'=>'cpd_balance_change',
	    'field'=>"SUM(recharge_sum) AS recharge_sum,SUM(delivery_sum) AS delivery_sum"
    );
    $option['where']['contract_id']=$contract_id;
    $option['where']['status']=1;
    $option['where']['add_tm']=array('exp',"<=$month_end && add_tm>=$month_begin");
	foreach($type_arr as $k=>$v){
		$option['where']['type']=$v;
	    $balance_change_data = $model->findOne($option,'master');
	     cpd_writelog('deal_month_balance::::'.$model->getSql());
	    if(!$balance_change_data){		
			continue;
		}
	    $options_balance['type']=$v;
	    $options_balance['recharge_sum']=$balance_change_data['recharge_sum'];
	    $options_balance['delivery_sum']=$balance_change_data['delivery_sum'];
	    if(!($options_balance['recharge_sum']!=0 || $options_balance['delivery_sum']!=0)){
	    	continue;
	    }
	    cpd_writelog('deal_month_balance::::'.var_export($options_balance,true));

	    $balance_change_data_old=sync_query('cpd_balance_change',array('contract_id'=>$contract_id_b,'add_tm'=>$month_begin,'status'=>1,'type'=>$v));
	    if($balance_change_data_old){
	    	$model->update(array('id'=>$balance_change_data_old['id']),$options_balance);
	    }else{
	    	 $model->insert($options_balance);
	    }
	    // $model->insert($options_balance);
	    cpd_writelog('deal_month_balance::::'.$model->getSql());
	}
}
//处理预存/收款/发票/转出/转入
function deal_month_prestore($data){
	$model = new GoModel();
	$contract_id=$data['contract_id'];
	$contract_id_b=$data['contract_id_b'];
	$month_begin=$data['month_begin'];
	$month_end=$data['month_end'];
	$month_end=$month_end+86399;
	//添加
    $options_balance = array(
	    '__user_table' => 'cpd_prestore',  
	    'contract_id' => $contract_id_b,
	    'prestores_tm' => $month_begin,
	    'recharge_sum' => '',
	    'delivery_sum' => '',
	    'type' => '',
	    'add_tm' => $month_begin,

	    'recharge_expend_sum' => '',
	    'recharge_sum_zhuan' => '',
	    'delivery_expend_sum' => '',
	    'delivery_sum_zhuan' => '',
	    'receipts_sum' => '',
	    'receipts_tm' => '',
	    'receipts_status' => '',
	    'invoice_sum' => '',
	    'invoice_tm' => '',
	    'invoice_status' => '',
    );

	$type_arr=array('zero'=>0,'one'=>1,'two'=>2,'three'=>3);
	$option = array(
	    'table'=>'cpd_prestore',
	    'field'=>"SUM(recharge_sum) AS recharge_sum,SUM(delivery_sum) AS delivery_sum,SUM(recharge_expend_sum) AS recharge_expend_sum,SUM(recharge_sum_zhuan) AS recharge_sum_zhuan,SUM(delivery_expend_sum) AS delivery_expend_sum,SUM(delivery_sum_zhuan) AS delivery_sum_zhuan,SUM(receipts_sum) AS receipts_sum,SUM(invoice_sum) AS invoice_sum"
    );
    $option['where']['contract_id']=$contract_id;
    $option['where']['status']=1;
    $option['where']['prestores_tm']=array('exp',"<=$month_end && prestores_tm>=$month_begin");
	foreach($type_arr as $k=>$v){
		$option['where']['type']=$v;
	    $balance_change_data = $model->findOne($option,'master');
	    cpd_writelog('deal_month_balance::::'.$model->getSql());
	    if(!$balance_change_data){		
			continue;
		}
	    $options_balance['type']=$v;
	    $options_balance['recharge_sum']=$balance_change_data['recharge_sum'];
	    $options_balance['delivery_sum']=$balance_change_data['delivery_sum'];
	    $options_balance['recharge_expend_sum']=$balance_change_data['recharge_expend_sum'];
	    $options_balance['recharge_sum_zhuan']=$balance_change_data['recharge_sum_zhuan'];
	    $options_balance['delivery_expend_sum']=$balance_change_data['delivery_expend_sum'];
	    $options_balance['delivery_sum_zhuan']=$balance_change_data['delivery_sum_zhuan'];
	    if(!($options_balance['recharge_sum']!=0 || $options_balance['delivery_sum']!=0 || $options_balance['recharge_expend_sum']!=0 || $options_balance['recharge_sum_zhuan']!=0 || $options_balance['delivery_expend_sum']!=0 || $options_balance['delivery_sum_zhuan']!=0)){
	    	continue;
	    }
	    if($k=='zero'){
	    	$options_balance['receipts_sum']=$balance_change_data['receipts_sum'];
	        $options_balance['invoice_sum']=$balance_change_data['invoice_sum'];

	        $options_balance['receipts_tm']=$balance_change_data['receipts_sum']?$month_begin:'';
	        $options_balance['receipts_status']=($balance_change_data['receipts_sum']>0)?1:'';

	        $options_balance['invoice_tm']=$balance_change_data['invoice_sum']?$month_begin:'';
	        $options_balance['invoice_status']=($balance_change_data['invoice_sum']>0)?1:'';
	    }
	    cpd_writelog('deal_month_prestore::::'.var_export($options_balance,true));
	    $prestore_data_old=sync_query('cpd_prestore',array('contract_id'=>$contract_id_b,'prestores_tm'=>$month_begin,'status'=>1,'type'=>$v));
	    if($prestore_data_old){
	    	$model->update(array('id'=>$prestore_data_old['id']),$options_balance);
	    }else{
	    	 $model->insert($options_balance);
	    }
	    // $model->insert($options_balance);
	    cpd_writelog('deal_month_prestore::::'.$model->getSql());
	}
	empty_expend_record(array('expend_tm'=>strtotime(date('Y-m',time())),'contract_id'=>$contract_id_b,'contract_id_two'=>$contract_id));
}
//日志记录
function cpd_writelog($msg){
	$now = time();
	$filename = 'cpd_month_data_db_worker.log';
	$path = "/data/att/permanent_log/admin_cron_log/".date("Y-m-d", $now);
	if(!file_exists($path)){
		mkdir($path, 0755, true);
	}
	$path_log = $path."/".$filename;
	$msg = date('Y-m-d H:i:s', $now). " {$msg}\n";
	file_put_contents($path_log, $msg, FILE_APPEND);
}


 //获取此合同这条消耗之前消耗的总值
function get_before_expend_money($data,$bs=''){
	$model = new GoModel();
	$expend_tm = $data['expend_tm'];
	$contract_id = $data['contract_id'];
	$option = array(
		'table'=>'cpd_expend',
		'field'=>'price,download_recharge,download_delivery,download_count,nature_num,download_invalid'
	);
	if(!$bs){
		$option['where']['expend_tm']=array('exp',"<$expend_tm");
	}else{
		$option['where']['expend_tm']=array('exp',">=$expend_tm");
	}
	
	$option['where']['status']=1;
	$option['where']['contract_id']=$contract_id;
	$expend_data = $model->findAll($option,'master');
	cpd_writelog('get_before_expend_money::::'.$model->getSql());
	//已经消耗的钱数总和
	$before_expend_money=0;
	if($expend_data){
		foreach($expend_data as  $k=>$v){
			$before_expend_money+=($v['download_recharge']+$v['download_delivery']);
		}
	}
	
	return $before_expend_money?$before_expend_money:0;
}
//获取此合同预存的总和的分段数据
function get_prestore_money_distribution_interval($data){
	$model = new GoModel();
	$contract_id = $data['contract_id'];
	$option = array(
		'table'=>'cpd_prestore',
		'field'=>'*',
		'order'=>'prestores_tm asc'
	);
	$option['where']['status']=1;
	$option['where']['`recharge_sum`']=array('exp',">0 || delivery_sum>0");
	$option['where']['contract_id']=$contract_id;
	$prestore_data = $model->findAll($option,'master');
	cpd_writelog('get_prestore_money_distribution_interval::::'.$model->getSql());
	//已经预存的钱数
	$prestore_money=array();
	$prestore_money_cd=array();
	if($prestore_data){
		foreach($prestore_data as  $k=>$v){
			$count=count($prestore_money);
			$delivery_delivery_sum=$v['recharge_sum']+$v['delivery_sum']-$v['recharge_sum_zhuan']-$v['delivery_sum_zhuan'];
			if($count){
				$prestore_money[]=$prestore_money[$count-1]+$delivery_delivery_sum;
				$money_cd=$prestore_money[$count-1]+$delivery_delivery_sum;
				$prestore_money_cd[$money_cd]['recharge_sum']=$v['recharge_sum'];
				$prestore_money_cd[$money_cd]['recharge_sum_zhuan']=$v['recharge_sum_zhuan'];
				$prestore_money_cd[$money_cd]['delivery_sum_zhuan']=$v['delivery_sum_zhuan'];
				$prestore_money_cd[$money_cd]['delivery_sum']=$v['delivery_sum'];
				$prestore_money_cd[$money_cd]['id']=$v['id'];
			}else{
				$prestore_money[]=$delivery_delivery_sum;
				$prestore_money_cd[$delivery_delivery_sum]['recharge_sum']=$v['recharge_sum'];
				$prestore_money_cd[$delivery_delivery_sum]['delivery_sum']=$v['delivery_sum'];
				$prestore_money_cd[$delivery_delivery_sum]['recharge_sum_zhuan']=$v['recharge_sum_zhuan'];
				$prestore_money_cd[$delivery_delivery_sum]['delivery_sum_zhuan']=$v['delivery_sum_zhuan'];
				$prestore_money_cd[$delivery_delivery_sum]['id']=$v['id'];
			}
		}
	}
	cpd_writelog('get_prestore_money_distribution_interval::::prestore_money:'.var_export($prestore_money,true).'------prestore_money_cd:'.var_export($prestore_money_cd,true));
	return array('prestore_money'=>$prestore_money,'prestore_money_cd'=>$prestore_money_cd);
}
//清除此合同本条日消耗和后续消耗累积在预存中的充值和配送消耗
function  empty_expend_record($data){
	$model = new GoModel();
	//获取此合同这条消耗之前消耗的总值
	$before_expend_money=get_before_expend_money($data);
	//获取此合同预存的总和的分段数据 详见函数内的数据结构
	$money_arr=get_prestore_money_distribution_interval($data);
	$prestore_money=$money_arr['prestore_money'];
	$prestore_money_cd=$money_arr['prestore_money_cd'];
	$prestore_money_min=0;
	$prestore_money_max=0;
	$i=0;
	//recharge_expend_sum 充值消耗金额
	//delivery_expend_sum 配送消耗金额
	$count=count($prestore_money);
	if($prestore_money){
		if($prestore_money[$count-1]>$before_expend_money){
			foreach($prestore_money as $k=>$v){
				if($v>$before_expend_money){
					$i++;
					if($before_expend_money>0){
						if($i==1){
							//remain_expend 本条数据总消耗
							$remain_expend=$prestore_money_cd[$v]['recharge_sum']-$prestore_money_cd[$v]['recharge_sum_zhuan']+$prestore_money_cd[$v]['delivery_sum']-$prestore_money_cd[$v]['delivery_sum_zhuan']-($v-$before_expend_money);
							//充值未消耗完
							if(($prestore_money_cd[$v]['recharge_sum']-$prestore_money_cd[$v]['recharge_sum_zhuan']) >= $remain_expend){							
								$sql="update cpd_prestore set `recharge_expend_sum`={$remain_expend},`delivery_expend_sum`=0 where id={$prestore_money_cd[$v]['id']}";
								$res=$model->query($sql);
								cpd_writelog('empty_expend_record::::'.$model->getSql());
								cpd_writelog('expend_ratio_update::::'.var_export($res,true));
							}else{
								//充值出去转出充值 还消耗完了 配送也消耗一部分
								$delivery_expend_sum = $remain_expend - ($prestore_money_cd[$v]['recharge_sum']-$prestore_money_cd[$v]['recharge_sum_zhuan']);
								$recharge_expend_sum = $prestore_money_cd[$v]['recharge_sum']-$prestore_money_cd[$v]['recharge_sum_zhuan'];
								$sql="update cpd_prestore set `delivery_expend_sum`={$delivery_expend_sum},`recharge_expend_sum`={$recharge_expend_sum} where id={$prestore_money_cd[$v]['id']}";
								$res=$model->query($sql);
								cpd_writelog('empty_expend_record::::'.$model->getSql());
								cpd_writelog('expend_ratio_update::::'.var_export($res,true));
							}
						}else{
							//$before_expend_money未消耗到后续数据 因此可以直接恢复未消耗前状态
							$sql="update cpd_prestore set `recharge_expend_sum`=0,`delivery_expend_sum`=0 where id={$prestore_money_cd[$v]['id']}";
							$res=$model->query($sql);
							cpd_writelog('empty_expend_record::::'.$model->getSql());
							cpd_writelog('expend_ratio_update::::'.var_export($res,true));
						}	
					}else{
						//之前未产生消耗 因此可以直接恢复未消耗前状态
						$sql="update cpd_prestore set `recharge_expend_sum`=0,`delivery_expend_sum`=0 where id={$prestore_money_cd[$v]['id']}";
						$res=$model->query($sql);
						cpd_writelog('empty_expend_record::::'.$model->getSql());
						cpd_writelog('expend_ratio_update::::'.var_export($res,true));
					}
				}
			}
		}else{
			$arr=array('expend_tm'=>strtotime(date('Y-m',time())),'contract_id'=>$data['contract_id_two']);
			$after_expend_money=get_before_expend_money($arr,1);
			$id=$prestore_money_cd[$prestore_money[$count-1]]['id'];
			$sql="update cpd_prestore set `recharge_expend_sum`=`recharge_expend_sum`-{$after_expend_money} where id={$id}";
			$res=$model->query($sql);
			cpd_writelog('empty_expend_record::::'.$model->getSql());
			cpd_writelog('expend_ratio_update::::'.var_export($res,true));
		}
	}
	
}