<?php
#红包转零钱gearman脚本
require_once(dirname(__FILE__).'/../init.php');
ini_set('displays_errors', true);
error_reporting(E_ALL);
$model = new GoModel();
$_SERVER['HTTP_HOST']='api.test.anzhi.com';
$db_m = 'redpacket/redpk_m';
$db_s = 'redpacket/redpk_s';
$start = time();
$worker= new GearmanWorker();
$redis = new GoRedisCacheAdapter(load_config("cache/redis","redpacket"));
load_helper('utiltool');
$config = load_config('gearman/uctask');
$worker->addServer($config['task_server'], $config['task_port']);
$worker->addFunction('USER_RED_PACKE_STATUS_FUNCTION_NAME', 'change_bill_status'); 
while ($worker->work());

function change_bill_status($job)
{
	global $model;
    global $redis;
    
	$string = $job->workload();
    if(!$string){
        return false;
    }
    
/*     incomeStatus 入账类型   1.已入账   2.取消入账   3.入账异常
    phpId  phpid
    pid 用户id
    reId 红包ID
    type入账标识      1取消奖励标识   2.红包转零钱标识
    updateTime 入账时间 */
    $time = date("H:i:s");
	$db_m = 'redpk_m/redpacket';
	$db_s = 'redpk_s/redpacket';
    permanentlog('redpacket_worker.log',"{$time} worker_str: {$string}");
    $params = json_decode($string,true);
    if(empty($params)){
        permanentlog('redpacket_worker.log',"{$time} error: json format error,worker_str: {$string}");
        permanentlog('redpacket_worker_unparse.log',$string);
    }
    foreach($params as $val){
        $bill = $val['phpId'];
        $packid = $val['reId'];
        $uid = $val['pid'];
        $type = $val['type'];
        $reason = $val['memo'];
        $option = array(
            'where'=>array(
                'id'=>$bill,
                'packid'=>$packid,
                'uid' =>$uid,
                'status'=>2,
            ),
            'field'=>'cashnum',
            'table'=>'sj_redpacket_bill',
            
        );
        $billinfo = $model->findOne($option,$db_s);
        if(!$billinfo) 
        {
            permanentlog('redpacket_worker.log',"{$time} error:db record not exist,billid:{$bill},packid:{$packid},uid:{$uid},type:{$type}");
            permanentlog('redpacket_worker_failed.log',$string);
            continue;
        }
        $data['lasttime'] = time();
        if($type == 2){
            $data['status'] = 1;
        }else{
            $data['status'] = 3;
            $data['reason'] = mysql_escape_string($reason);
        }
       
        $data['__user_table'] = 'sj_redpacket_bill';
        $model->update(array('id'=>$bill),$data,$db_m);
        $sql = $model->getLatestSql();
        permanentlog('redpacket_worker.log',"{$time} excute_sql: {$sql}");
        
        if($type == 2){          
            //如果是红包入账，需要增加用户获得的红包总金额
            // $option = array(
                // 'where'=>array(
                    // 'uid'=>$uid,
                // ),
                // 'table'=>'sj_redpacket_user_sum',
            // );
            // $res = $model->findOne($option);
            // $userdata['__user_table'] = 'sj_redpacket_user_sum';
            // if(!$res){
                // $userdata['uid'] =$uid;
                // $userdata['totalcash'] = $billinfo['cashnum'];
                // $userdata['packnum'] = 1;
                // $model->insert($userdata);
            // }else{
                // $userdata['totalcash'] = $res['totalcash'] + $billinfo['cashnum'];
                // $userdata['packnum'] = $res['totalcash'] + 1;
                // $model->update(array('uid'=>$uid),$userdata);
                
            // }
        }else{
            //如果是红包废弃，需要增加配置表的废弃数和废弃金额
            $disdata['disnum'] = 'disnum + 1 ';
            $disdata['dismon'] = 'dismon + '.$billinfo['cashnum'];
            $disdata['__user_table'] = 'sj_red_packet_conf';
            $model->update(array('id'=>$packid),$disdata,$db_m);
			$userdata = array();
            $userdata['__user_table'] = 'sj_redpacket_user_sum';
			$userdata['totalcash'] = array('exp','totalcash - '.$billinfo['cashnum']); 
			#$userdata['packnum'] = array('exp','packnum - 1' );
            $model->update(array('uid'=>$uid),$userdata,$db_m);
            $sql2 = $model->getLatestSql();
            permanentlog('redpacket_worker.log',"{$time} excute_sql: {$sql2}");
            #修改用户相关的redis
            $cash = -$billinfo['cashnum'] * 100;
            $redis->pingConn();
            
            
            $packcash = $redis->getx('hget','USER_DETAIL_CASHES_I_UID:'.$uid,'REDPACK:'.$packid);
            $packRes = $redis->setx('hincrby','USER_DETAIL_CASHES_I_UID:'.$uid,'REDPACK:'.$packid,$cash);
            $current_packcash = $redis->getx('hget','USER_DETAIL_CASHES_I_UID:'.$uid,'REDPACK:'.$packid);
            //如果redis执行之后，红包金额还相等，记下错误日志
            if($packcash == $current_packcash){
                permanentlog('redpacket_worker.log',"{$time} redis_error:hincrby, USER_DETAIL_CASHES_I_UID:{$uid},REDPACK:{$packid},{$cash} ,res:".var_export($packRes,true)." ,sourceCash:".var_export($packcash,true));
                permanentlog('redpacket_worker_failed.log',$string);
                $data = json_encode(array('packid'=>$packid,'billid'=>$bill,'uid'=>$uid,'field'=>'packetcash'));
                permanentlog('redpacket_worker_redis_error.log',$data);
            }
            $totalcash = $redis->getx('hget','USER_DETAIL_CASHES_I_UID:'.$uid,'TOTAL_CASH');
            $totalcashRes = $redis->setx('hincrby','USER_DETAIL_CASHES_I_UID:'.$uid,'TOTAL_CASH',$cash);
			$current_totalcash = $redis->getx('hget','USER_DETAIL_CASHES_I_UID:'.$uid,'TOTAL_CASH');
             //如果redis执行之后，红包金额还相等，记下错误日志
            if($totalcash == $current_totalcash){
                permanentlog('redpacket_worker.log',"{$time} redis_error:hincrby, USER_DETAIL_CASHES_I_UID:{$uid},REDPACK:{$packid},{$cash} ,res:".var_export($totalcashRes,true)." ,sourceCash:".var_export($totalcash,true));
                permanentlog('redpacket_worker_failed.log',$string);
                $data = json_encode(array('packid'=>$packid,'billid'=>$bill,'uid'=>$uid,'field'=>'totalcash'));
                permanentlog('redpacket_worker_redis_error.log',$data);
            }
            
        }
        
       
        
        
    }
    
  
    
    
    
    
    
}
