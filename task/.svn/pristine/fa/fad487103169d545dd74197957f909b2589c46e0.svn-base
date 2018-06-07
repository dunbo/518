<?php
/*
 *   充值排行榜十连抽worker
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
$worker->addFunction("recharge_top_ten", "get_award");
while ($worker->work());

function get_award($jobs){
                global $model;

                global $redis;

                $jobs= $jobs->workload();

                $jobs = json_decode($jobs,true);
                
                $uid = $jobs['uid'];

                if(empty($uid)){
                    $resarr['type'] = -1;
                    return json_encode($resarr);
                }

                $redis->pingConn();

                var_dump($uid);

                $username= $jobs['username'];

                $rs = $redis->get('recharge_top_prize_ten');
                if(empty($rs))
                {
                    $rs = $redis->get('recharge_top_prize_ten');
                    echo 'begin 2....';
                    if(empty($rs))
                    {
                        echo 'begin 3....';
                        $rs = $redis->get('recharge_top_prize_ten');
                        if(empty($rs)){
                            //重新读库  建立缓存
                            echo 'two is empty';
                            $option = array(
                                    'where' => array(
                                            'num' => array('exp','!=0'),
                                    ),
                                    'table' => 'recharge_top_prize_ten',
                            );
                            $recharge_top_prize_ten= $model->findAll($option,'lottery/lottery');

                            $new_arr = array();
                            foreach($recharge_top_prize_ten as $v){
                                $new_arr[$v['id']]=$v['name'];
                                $id = $v['id'];
                                $num = intval($v['num']);
                                $name = $v['name'];
                                $redis->set('recharge_top_prize_ten_num_'.$id,$num,86400*10);
                                $redis->set('recharge_top_prize_ten_name_'.$id,$name,86400*10);
                            }
                            $redis->set('recharge_top_prize_ten',$recharge_top_prize_ten,86400*10);                            
                            $rs = $redis->get('recharge_top_prize_ten');
                        }
                    }
                }

                $pid_key = mt_rand(0,count($rs)-1);
                $pid = $rs[$pid_key]['id'];
                echo 'pid:'.$pid;
                
                //中奖，检查数量 如数量不足 返回未中奖

                // 奖品数量-1
                $now_num = $redis -> setx('incr','recharge_top_prize_ten_num_'.$pid, -1);
                echo 'now_num:'.$now_num;
                if ($now_num < 0) {
                    // 没有剩余奖品了，把缓存数量还原为0
                    $now_num = $redis -> set('recharge_top_prize_ten_num_'.$pid, 0);
                    unset($rs[$pid_key]);
                    $res = array_values($rs);
                    if(!empty($res)){
                        $redis->set('recharge_top_prize_ten',$res,86400*10);
                    }

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
                    $resarr['type'] = 2;
                    $resarr['gift_number'] = $gift_number;
                    return json_encode($resarr);
                }

                $name = $redis -> get('recharge_top_prize_ten_name_'.$pid);
                //减少库中实际数量
                $param =array();
                $param['type'] = 2;
                $param['uid'] = $uid;
                $param['username'] = $username;
                $param['pid'] = $pid;
                $param['prizename'] = $name;
                $param['now_num'] = $now_num;
                $param['table'] = 'recharge_top_prize_ten';

                $task_client = get_task_client();
                $task_client->doBackground('recharge_top_db',json_encode($param));


                            //写入缓存
                            /*
                            $imsiarr= array();
                            $imsiarr[$imsi] = array(
                            'level'=>$level,
                            'time'=>$now_tm,
                            'status'=>0,
                            'pid'=>$pid,
                        );
                        $redis -> sethash("general_lottery_imsi:info_{$aid}",$imsiarr);
                        */

                $resarr = array();
                $resarr['pid'] = $pid;
                $resarr['name'] = $name;
                $resarr['uid'] = $uid;
                $resarr['type'] = 1;
                return json_encode($resarr);
}
