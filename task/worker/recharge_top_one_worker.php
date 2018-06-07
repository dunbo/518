<?php
/*
 *   通用抽奖worker
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
$worker->addFunction("recharge_top_one", "get_award");
while ($worker->work());

function get_award($jobs){
                global $model;

                global $redis;

                $jobs= $jobs->workload();

                $jobs = json_decode($jobs,true);
                
                $uid = $jobs['uid'];
                
                $username= $jobs['username'];

                $nowtime = strtotime(date('Y-m-d'));

                $gift_base = array();
                $gift_base[1] = 3;
                $gift_base[2] = 8;
                $gift_base[3] = 2;
                $gift_base[4] = 2;
                $gift_base[5] = 6;
                $gift_base[6] = 2;
                $gift_base[7] = 4;
                $gift_base[8] = 3;
                $gift_base[9] = 2;
                $gift_base[10] = 40;

                $pid = lottery($gift_base,15000);
                
                //中奖，检查数量 如数量不足 返回未中奖
                if($pid!=-1){
                            $redis->pingConn();
                            // 奖品数量-1
                            $now_num = $redis -> setx('incr','recharge_top_prize_num_'.$pid, -1);
                            if ($now_num < 0) {
                                        // 没有剩余奖品了，把缓存数量还原为0
                                        $now_num = $redis -> set('recharge_top_prize_num_'.$pid, 0);

                                        //中礼包
                                        //处理  redis 的礼包
                                        $gift_number = $redis -> rpop("recharge_top_gift");
                                        if(empty($gift_number)){
                                            $gift_number = $redis -> rpop("recharge_top_gift");
                                        }

                                        $param =array();
                                        $param['type'] = 1;
                                        $param['uid'] = $uid;
                                        $param['gift_number'] = json_decode($gift_number,true);

                                        $task_client = get_task_client();
                                        $task_client->doBackground('recharge_top_db',json_encode($param));

                                        $resarr = array();
                                        $resarr['uid'] = $uid;
                                        $resarr['type'] = 2;//礼包
                                        $resarr['gift_number'] = $gift_number;
                                        return json_encode($resarr);
                            }
                            $name = $redis -> get('recharge_top_prize_name_'.$pid);

                            //减少库中实际数量
                            $param =array();
                            $param['type'] = 2;
                            $param['uid'] = $uid;
                            $param['username'] = $username;
                            $param['pid'] = $pid;
                            $param['prizename'] = $name;
                            $param['now_num'] = $now_num;
                            $param['table'] = 'recharge_top_prize_one';

                            $task_client = get_task_client();
                            $task_client->doBackground('recharge_top_db',json_encode($param));

                            $resarr = array();
                            $resarr['pid'] = $pid;
                            $resarr['name'] = $name;
                            $resarr['uid'] = $uid;
                            $resarr['type'] = 1;//实体
                            return json_encode($resarr);
                }else{
                            $redis->pingConn();
                            //处理  redis 的礼包
                            $gift_number = $redis -> rpop("recharge_top_gift");
                            var_dump($gift_number);
                            if(empty($gift_number)){
                                echo '2....';
                                $gift_number = $redis -> rpop("recharge_top_gift");
                            }

                            $param =array();
                            $param['type'] = 1;
                            $param['uid'] = $uid;
                            $param['gift_number'] = json_decode($gift_number,true);

                            $task_client = get_task_client();
                            $task_client->doBackground('recharge_top_db',json_encode($param));
                            $resarr = array();
                            $resarr['uid'] = $uid;
                            $resarr['type'] = 2;//礼包
                            $resarr['gift_number'] = $gift_number;
                            return json_encode($resarr);
                }
                return -1;
}

function lottery($gift_base, $sum) {
    $gift_line = array();
    $nows = 0;
    if(!$gift_base){
        return -1;
    }

    foreach ($gift_base as $k=>$v) {
        $gift_line[$k] = array($nows+1, $nows+$v);
        $nows += $v;
    }

    $rand = mt_rand(1, $sum);

    foreach ($gift_line as $k => $v) {
        if ($rand >= $v[0] && $rand <= $v[1]) {
            return $k;
        }
    }
    return -1;
}
