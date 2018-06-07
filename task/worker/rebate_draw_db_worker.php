<?php

/*
   返利抽奖 db处理 worker
*/
include dirname(__FILE__).'/../init.php';
$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
    $redis = new GoRedisCacheAdapter($config);
} else {
    $redis = GoCache::getCacheAdapter('redis');
}

$model = new GoModel();
$gift_base = array();
ini_set('default_socket_timeout', -1);
load_helper('task');
$worker = get_task_worker();
$worker->addFunction("rebate_draw_db", "handle");
while ($worker->work());

function handle($jobs){
    global $model;
    $param = $jobs->workload();
	$param = json_decode($param,true);
	$type = $param['type'];
	$uid = $param['uid'];
	$username= $param['username'];
	$gift_number = $param['gift_number'];
	$pid = $param['pid'];
	$prizename= $param['prizename'];
	$now_num = $param['now_num'];
	$now_tm= time();

        if($type==1){//处理礼包

                $where = array(
                    'gift_number' =>$gift_number['gift_number'],
                );
                $data = array(
                    'uid' => $uid,
                    'status' => 1,
                    'update_tm' => $now_tm,
                    '__user_table' => 'rebate_draw_gift'
                );
                $model -> update($where,$data,'lottery/lottery');

				$award_data = array(
                        'uid' => $uid,
                        'username' => $username,
                        'create_tm' => $now_tm,
                        'status' => 1,
                        'pid' => 6,
                        'prizename' => json_encode($gift_number),
                        '__user_table' => 'rebate_draw_award'
                );
                $model -> insert($award_data,'lottery/lottery');
				

        }else if($type==2){ //处理库存及中奖记录

                $where = array(
                    'id' =>$pid,
                );
                $data = array(
                    'num' => $now_num,
                    '__user_table' => 'rebate_draw_prize'
                );
                $model -> update($where,$data,'lottery/lottery');                

                $award_data = array(
                        'uid' => $uid,
                        'username' => $username,
                        'create_tm' => $now_tm,
                        'status' => 1,
                        'pid' => $pid,
                        'prizename' => $prizename,
                        '__user_table' => 'rebate_draw_award'
                );
                $model -> insert($award_data,'lottery/lottery');
        }
}
