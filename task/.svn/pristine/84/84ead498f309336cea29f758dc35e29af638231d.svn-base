<?php

/*
    通用cpd_db处理 worker
*/
// include dirname(__FILE__).'/../init.php';
require_once(dirname(__FILE__).'/../../init.php');
$gift_base = array();
ini_set('default_socket_timeout', -1);
load_helper('task');
$worker = get_task_worker();
$worker->addFunction("cpd_expend_db", "handle");
while ($worker->work());


//日志说明如下
//cpd_writelog('cpd_month_bill::::调用月账work');
//调用月账work 之间即为一个流程执行的sql
//cpd_writelog('distribution_expend::::'.$model->getSql());
//distribution_expend 为函数名 执行的sql 


//work执行开始函数
function handle($jobs){    
    $param = $jobs->workload();
	$param = json_decode($param,true);
	$expend_id = $param['expend_id'];
	if(!$expend_id){
		return;
	}
	cpd_writelog("\n".'通用cpd_db处理:handle::::本次消耗id'.$expend_id.'---本次流程开始');
	$model = new GoModel();
	$option = array(
	    'table'=>'cpd_expend',
	    'field'=>"*"
    );
    $option['where']['id']=$expend_id;
    $expend_d = $model->findOne($option,'master');
	cpd_writelog('通用cpd_db处理:handle::::本次消耗id'.$expend_id.'取消耗'.var_export($expend_d,true));
    if(!$expend_d){		
		return;
	}
    $contract_id=$expend_d['contract_id'];
    $expend_tm=$expend_d['expend_tm'];
    $d=date('Ym',$expend_tm);
    //获取多条数据
    $option = array(
    'table'=>'cpd_expend',
    'field'=>"*",
    'order'=>"expend_tm asc",
    );
    $option['where']['status']=1;
    $option['where']['contract_id']=$contract_id;
    $option['where']['expend_tm']=array('exp',">=$expend_tm");
    $expend_ds = $model->findAll($option,'master');
	cpd_writelog('通用cpd_db处理:handle::::本次消耗id'.$expend_id.'取合同所有消耗条数:'.count($expend_ds));
    if(!$expend_ds){
		return;
	}
    cpd_writelog('handle::::'.$model->getSql());
    //清空消耗记录
    empty_expend_record(array('expend_tm'=>$expend_tm,'contract_id'=>$contract_id));
    if($expend_ds){
    	foreach($expend_ds as $k=>$v){
	    	//具体消耗分配处理及更新入库
	    	distribution_expend($v);
	    }

	    $task_client = get_task_client();
	    $option_task = array(
			'table'=>'cpd_task',
			'field'=>'*'
		);
	
		$option_task['where']['status']=1;
		$option_task['where']['contract_id']=$contract_id;
		$option_task['where']['start_tm']=array('exp',"<={$expend_tm} and `end_tm`>={$expend_tm}");
		$task_data = $model->findOne($option_task,'master');
		$begin_tm_task=strtotime(date('Y-m',$task_data['start_tm']));
		$end_tm_task=strtotime(date('Y-m',$task_data['end_tm']));
		for($i=$begin_tm_task;$i<=$end_tm_task;$i+=(date('t',$i))*86400){
			$task_client->doBackground("cpd_month_bill", json_encode(array("contract_id" =>$contract_id,'month'=>date('Ym',$i))));   
		}
    	cpd_writelog('cpd_month_bill::::调用月账work');
        $task_client->doBackground("cpd_month_data_db", json_encode(array("contract_id" =>$contract_id,'bill_month'=>date('Y-m',$expend_tm))));
    }
   
    
}
//具体消耗充值和配送下载量分配处理及更新入库
function distribution_expend($expend_data){
	$model = new GoModel();
	$time=$expend_data['expend_tm'];
	$contract_id=$expend_data['contract_id'];
	//处理具体每天消耗记录
	//$expend_m 当天时间消耗总额
	//$expend_c 当天时间消耗总下载量
	//$expend_c_download_recharge 当天时间消耗下载量充值
	//$expend_c_download_delivery 当天时间消耗下载量配送

	$expend_m=($expend_data['download_recharge']+$expend_data['download_delivery']);
	$expend_c_download_recharge=$expend_data['download_recharge'];
	$expend_c_download_delivery=$expend_data['download_delivery'];

	// $expend_c=$expend_data['download_count']-$expend_data['nature_num']-$expend_data['download_invalid'];
	$expend_c=$expend_data['download_recharge']/$expend_data['price_before']+$expend_data['download_delivery']/$expend_data['price_before'];
	$expend_ratio=expend_ratio_update($contract_id,($expend_data['download_count']-$expend_data['download_invalid']-$expend_data['nature_num']),$expend_data['price']);
    $options = array(
		'__user_table' => 'cpd_expend',
		'download_recharge' => $expend_ratio['recharge_cha_money'],
		'download_delivery' => $expend_ratio['delivery_cha_money'],
		'price_before' => $expend_data['price'],

	);	
    $model->update(array('id'=>$expend_data['id']),$options,'master');
    cpd_writelog('distribution_expend::::'.$model->getSql());
    //更新余额管理 recharge_sum：充值金额  delivery_sum：配送金额
    $sql="update `cpd_balance_change` set `recharge_sum` = 0-{$expend_ratio['recharge_cha_money']},`delivery_sum` =0-{$expend_ratio['delivery_cha_money']} WHERE `contract_id` = {$contract_id} and `add_tm`={$time} and `type`=5";
    $model->query($sql);
    cpd_writelog('distribution_expend::::'.$model->getSql());
    //更新任务表 t_total_count:消耗金额累积，t_total_download：下载量总和累积，download_recharge_sum：累积下载量充值，download_delivery_sum：累积下载量配送
    $sql="update `cpd_task` set  `t_total_count` = `t_total_count`+{$expend_ratio['recharge_cha_money']}+{$expend_ratio['delivery_cha_money']}-{$expend_m},`t_total_download` = `t_total_download`+{$expend_ratio['recharge_cha']}+{$expend_ratio['delivery_cha']}-{$expend_c},`download_recharge_sum` = `download_recharge_sum`+{$expend_ratio['recharge_cha_money']}-{$expend_c_download_recharge},`download_delivery_sum` = `download_delivery_sum`+{$expend_ratio['delivery_cha_money']}-{$expend_c_download_delivery} WHERE `contract_id` = {$contract_id} and `status` = 1 and`start_tm`<={$time} and `end_tm`>={$time} ";
    $model->query($sql);
    cpd_writelog('distribution_expend::::'.$model->getSql());
}
 function expend_ratio_update($contract_id,$download_recharge_delivery,$price){
        //$contract_id 合同id
        //$download_recharge_delivery 当日充值和配送消耗总和
 		//字段说明如下
 		//`recharge_sum`  COMMENT '充值金额',
	  // `delivery_sum` COMMENT '配送金额',
	  // `recharge_expend_sum`  COMMENT '充值消耗金额',
	  // `recharge_sum_zhuan`  COMMENT '充值转出金额',
	  // `delivery_expend_sum` COMMENT '配送消耗金额',
	  // `delivery_sum_zhuan` COMMENT '配送转出金额',
        $model = new GoModel();
        $option = array(
            'table'=>'cpd_prestore',
            'field'=>'*',
            'order'=>'prestores_tm asc'
        );
        $option['where']['`recharge_sum`+`delivery_sum`-`recharge_expend_sum`-`delivery_sum_zhuan`-`recharge_sum_zhuan`-`delivery_expend_sum`']=array('exp',">0");

        $option['where']['status']=1;
        $option['where']['contract_id']=$contract_id;
        $prestores_data = $model->findAll($option,'master');
        cpd_writelog('expend_ratio_update::::'.$model->getSql());
        //返回值 充值和配送各消耗多少下载量 各自消耗多少钱
        //以及记录每个预存应该消耗多少钱
        $recharge_cha_money=0;
        $delivery_cha_money=0;
        $money_sum=$download_recharge_delivery*$price;
        $count=count($prestores_data);
        cpd_writelog('expend_ratio_update::::'.var_export($count,true));
        cpd_writelog('expend_ratio_update::::'.var_export($prestores_data,true));
        cpd_writelog('expend_ratio_update::::'.var_export($money_sum,true));
        if($count){
            foreach($prestores_data as $k=>$v){
            	$v['recharge_sum_zhuan']=$v['recharge_sum_zhuan']?$v['recharge_sum_zhuan']:0;
            	$v['recharge_expend_sum']=$v['recharge_expend_sum']?$v['recharge_expend_sum']:0;
            	$v['delivery_expend_sum']=$v['delivery_expend_sum']?$v['delivery_expend_sum']:0;
            	$v['delivery_sum_zhuan']=$v['delivery_sum_zhuan']?$v['delivery_sum_zhuan']:0;
            	$v['delivery_sum']=$v['delivery_sum']?$v['delivery_sum']:0;
                $remain_recharge=$v['recharge_sum']-$v['recharge_sum_zhuan']-$v['recharge_expend_sum'];
                $delivery_recharge=$v['delivery_sum']-$v['delivery_expend_sum']-$v['delivery_sum_zhuan'];
                cpd_writelog('get_prestore_money_distribution_interval::::remain_recharge:'.var_export($remain_recharge,true).'------delivery_recharge:'.var_export($delivery_recharge,true));

                // if($remain_recharge>0){
                    if($remain_recharge>$money_sum){
                        // $recharge_cha+=$money_sum/$price;
                        $recharge_cha_money+=$money_sum;
                        $delivery_cha_money+=0;
                        $sql="update cpd_prestore set `recharge_expend_sum`=`recharge_expend_sum`+{$money_sum} where id={$v['id']}";
                        $res=$model->query($sql);
                        cpd_writelog('expend_ratio_update::::'.$model->getSql());
                        cpd_writelog('expend_ratio_update::::'.var_export($res,true));
                        break;
                    }else{
                        // $recharge_cha+=$remain_recharge/$price;
                        $recharge_cha_money+=$remain_recharge;
                        $delivery_cha_money+=0;
                        $sql="update cpd_prestore set `recharge_expend_sum`=`recharge_expend_sum`+{$remain_recharge} where id={$v['id']}";
                        $res=$model->query($sql);
                        cpd_writelog('expend_ratio_update::::'.$model->getSql());
                        cpd_writelog('expend_ratio_update::::'.var_export($res,true));
                        $money_sum-=$remain_recharge;
                    }
                    if($delivery_recharge>$money_sum){
                        $recharge_cha_money+=0;
                        $delivery_cha_money+=$money_sum;
                        $sql="update cpd_prestore set `delivery_expend_sum`=`delivery_expend_sum`+{$money_sum} where id={$v['id']}";
                        $res=$model->query($sql);
                        cpd_writelog('expend_ratio_update::::'.$model->getSql());
                        cpd_writelog('expend_ratio_update::::'.var_export($res,true));
                        break;
                    }else{
                        $delivery_cha_money+=$delivery_recharge;
                        $recharge_cha_money+=0;
                        $sql="update cpd_prestore set `delivery_expend_sum`=`delivery_expend_sum`+{$delivery_recharge} where id={$v['id']}";
                        $res=$model->query($sql);
                        cpd_writelog('expend_ratio_update::::'.$model->getSql());
                        cpd_writelog('expend_ratio_update::::'.var_export($res,true));
                        $money_sum-=$delivery_recharge;
                    }
                // }
                if($count==($k+1) && $money_sum>0){
                    $recharge_cha_money+=$money_sum;
                    $sql="update cpd_prestore set `recharge_expend_sum`=`recharge_expend_sum`+{$money_sum} where id={$v['id']}";
                    $model->query($sql);
                    cpd_writelog('expend_ratio_update::::'.$model->getSql());
                }
            }
        }else{
            $option = array(
                'table'=>'cpd_prestore',
                'field'=>'*',
                'order'=>'prestores_tm desc'
            );
            $option['where']['status']=1;
            $option['where']['recharge_sum']=array('exp','>0 || delivery_sum>0');
            $option['where']['contract_id']=$contract_id;
            $prestores_data = $model->findOne($option,'master');
            cpd_writelog('expend_ratio_update::::'.$model->getSql());
            $recharge_cha_money+=$money_sum;
            $sql="update cpd_prestore set `recharge_expend_sum`=`recharge_expend_sum`+{$money_sum} where id={$prestores_data['id']}";
            $res=$model->query($sql);
            cpd_writelog('expend_ratio_update::::'.$model->getSql());
            cpd_writelog('expend_ratio_update::::'.var_export($res,true));

        }

        return array('recharge_cha'=>$recharge_cha_money/$price,'delivery_cha'=>$delivery_cha_money/$price,'recharge_cha_money'=>$recharge_cha_money,'delivery_cha_money'=>$delivery_cha_money);
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
	//字段说明如下
	//`recharge_sum`  COMMENT '充值金额',
  // `delivery_sum` COMMENT '配送金额',
  // `recharge_expend_sum`  COMMENT '充值消耗金额',
  // `recharge_sum_zhuan`  COMMENT '充值转出金额',
  // `delivery_expend_sum` COMMENT '配送消耗金额',
  // `delivery_sum_zhuan` COMMENT '配送转出金额',
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
	//获取数据结构如下
	// prestore_money:array (
	//   0 => 15000,
	// )------prestore_money_cd:array (
	//   15000 => 
	//   array (
	//     'recharge_sum' => '10000.00',
	//     'delivery_sum' => '5000.00',
	//     'recharge_sum_zhuan' => '0.00',
	//     'delivery_sum_zhuan' => '0.00',
	//     'id' => '141',
	//   ),
	// )
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
			$after_expend_money=get_before_expend_money($data,1);
			$id=$prestore_money_cd[$prestore_money[$count-1]]['id'];
			$sql="update cpd_prestore set `recharge_expend_sum`=`recharge_expend_sum`-{$after_expend_money} where id={$id}";
			$res=$model->query($sql);
			cpd_writelog('empty_expend_record::::'.$model->getSql());
			cpd_writelog('expend_ratio_update::::'.var_export($res,true));
		}
	}
	
}

function cpd_writelog($msg){
	$now = time();
	$filename = 'cpd_expend_db_worker.log';
	$path = "/data/att/permanent_log/admin_cron_log/".date("Y-m-d", $now);
	if(!file_exists($path)){
		mkdir($path, 0755, true);
	}
	$path_log = $path."/".$filename;
	$msg = date('Y-m-d H:i:s', $now). " {$msg}\n";
	file_put_contents($path_log, $msg, FILE_APPEND);
}