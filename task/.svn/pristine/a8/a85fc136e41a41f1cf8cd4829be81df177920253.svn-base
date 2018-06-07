<?php
/*
 *   大话西游抽奖worker
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
$worker->addFunction("xy2", "get_award");
while ($worker->work());

function get_award($jobs){
                global $model;

                global $redis;

                $jobs= $jobs->workload();

                $jobs = json_decode($jobs,true);
                print_r($jobs);
                
                $uid = $jobs['uid'];
                
                $username= $jobs['username'];

                $package= 'com.netease.dhxy.anzhi';

                $nowtime = strtotime(date('Y-m-d'));

                //如果缓存没了 重新生成
                //剩余奖品数量缓存
                $redis->pingConn();
                $res = $redis->get('xy2_prize_name_1');
                if(empty($res)){
                //if(true){
                            $option = array(
                                    'where' => array(
                                            'num' => array('exp','>0'),
                                            //'aid' => 1, //通用 活动ID
                                    ),
                                    'table' => 'xy2_draw_prize',
                            );
                            $prize_arr= $model->findAll($option,'lottery/lottery');
                            foreach($prize_arr as $v){
                                $id = $v['id'];
                                $num = intval($v['num']);
                                $name = $v['name'];
                                $redis->set('xy2_prize_num_'.$id,$num,1200);
                                $redis->set('xy2_prize_name_'.$id,$name,1200);
                            }
                }

                //通用改成读库
                $gift_base = array();
                $gift_base[1] = 20;
                $gift_base[2] = 30;
                $gift_base[3] = 40;
                $gift_base[4] = 30;
                $gift_base[5] = 25;
                $gift_base[6] = 45;
                $gift_base[7] = 1;
                $redis->get('xy2_prize_num_1');

                $pid = lottery($gift_base,4000);
                echo 'pid:'.$pid."\n";
                
                //中奖，检查数量 如数量不足 返回未中奖
                if($pid!=-1){
                            // 奖品数量-1
                            $now_num = $redis -> setx('incr','xy2_prize_num_'.$pid, -1);
                            echo 'now_num:'.$now_num."\n";
                            if ($now_num < 0) {
                                        echo 'no shiwu'."\n";
                                        // 没有剩余奖品了，把缓存数量还原为0
                                        $now_num = $redis -> set('xy2_prize_num_'.$pid, 0);

                                        $gift_number = get_gift($package);

                                        $param =array();
                                        $param['type'] = 1;
                                        $param['uid'] = $uid;
                                        $param['table_gift'] = 'xy2_draw_gift';
                                        $param['gift_number'] = json_decode($gift_number,true);

                                        $task_client = get_task_client();
                                        $task_client->doBackground('recharge_top_db',json_encode($param));

                                        $resarr = array();
                                        //$resarr['uid'] = $uid;
                                        $resarr['pid'] = 0;
                                        //$resarr['type'] = 2;//礼包
                                        $resarr['gift_number'] = $gift_number;
                                        print_r($resarr);
                                        return json_encode($resarr);


                            }
                            $name = $redis -> get('xy2_prize_name_'.$pid);

                            //减少库中实际数量
                            $param =array();
                            $param['type'] = 2;
                            $param['uid'] = $uid;
                            $param['username'] = $username;
                            $param['pid'] = $pid;
                            $param['prizename'] = $name;
                            $param['now_num'] = $now_num;
                            $param['table'] = 'xy2_draw_prize';
                            $param['table_award'] = 'xy2_draw_award';

                            $task_client = get_task_client();
                            $task_client->doBackground('recharge_top_db',json_encode($param));

                            $resarr = array();
                            $resarr['pid'] = $pid;
                            $resarr['name'] = $name;
                            //$resarr['uid'] = $uid;
                            //$resarr['type'] = 1;//实体
                            print_r($resarr);
                            return json_encode($resarr);
                }else{
                            $redis->pingConn();
                            $gift_number = get_gift($package);

                            $param =array();
                            $param['type'] = 1;
                            $param['uid'] = $uid;
                            $param['table_gift'] = 'xy2_draw_gift';
                            $param['gift_number'] = json_decode($gift_number,true);

                            $task_client = get_task_client();
                            $task_client->doBackground('recharge_top_db',json_encode($param));
                            $resarr = array();
                            $resarr['pid'] = 0;
                            //$resarr['uid'] = $uid;
                            //$resarr['type'] = 2;//礼包
                            $resarr['gift_number'] = $gift_number;
                            print_r($resarr);
                            return json_encode($resarr);
                }
}

function get_gift($package){
    global $redis;
    $gift_number = $redis -> rpop("xy2_gift:".$package);
    return $gift_number;
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
