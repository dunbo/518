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
$worker->addFunction("valentine", "get_award");
while ($worker->work());

function get_award($jobs){
                global $model;

                global $redis;

                $jobs= $jobs->workload();

                $jobs = json_decode($jobs,true);
                print_r($jobs);
                
                $uid = $jobs['uid'];
                
                $username= $jobs['username'];

                $package= $jobs['package'];

                $nowtime = strtotime(date('Y-m-d'));

                //如果缓存没了 重新生成
                //剩余奖品数量缓存
                $redis->pingConn();
                $res = $redis->get('valentine_prize_name_1');
                if(empty($res)){
                //if(true){
                            $option = array(
                                    'where' => array(
                                            'num' => array('exp','>0'),
                                            //'aid' => 1, //通用 活动ID
                                    ),
                                    'table' => 'valentine_draw_prize',
                            );
                            $prize_arr= $model->findAll($option,'lottery/lottery');
                            foreach($prize_arr as $v){
                                $id = $v['id'];
                                $num = intval($v['num']);
                                $name = $v['name'];
                                $redis->set('valentine_prize_num_'.$id,$num,1200);
                                $redis->set('valentine_prize_name_'.$id,$name,1200);
                            }
                }

                //通用改成读库
                $gift_base = array();
                $gift_base[1] = 30;
                $gift_base[2] = 218;
                $gift_base[4] = 20;
                $gift_base[5] = 36;
                $gift_base[6] = 27;
                $gift_base[7] = 10;
                $gift_base[8] = 5;
                $gift_base[9] = 5;
                $gift_base[11] = 2;
                $gift_base[12] = 1;
                $redis->get('valentine_prize_num_1');

                $pid = lottery($gift_base,5000);
                echo 'pid:'.$pid."\n";
                
                //中奖，检查数量 如数量不足 返回未中奖
                if($pid!=-1){
                            // 奖品数量-1
                            $now_num = $redis -> setx('incr','valentine_prize_num_'.$pid, -1);
                            echo 'now_num:'.$now_num."\n";
                            if ($now_num < 0) {
                                        echo 'no shiwu'."\n";
                                        // 没有剩余奖品了，把缓存数量还原为0
                                        $now_num = $redis -> set('valentine_prize_num_'.$pid, 0);

                                        $gift_number = get_gift($package);

                                        $param =array();
                                        $param['type'] = 1;
                                        $param['uid'] = $uid;
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
                            $name = $redis -> get('valentine_prize_name_'.$pid);

                            //减少库中实际数量
                            $param =array();
                            $param['type'] = 2;
                            $param['uid'] = $uid;
                            $param['username'] = $username;
                            $param['pid'] = $pid;
                            $param['prizename'] = $name;
                            $param['now_num'] = $now_num;
                            $param['table'] = 'valentine_draw_prize';

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

    $new_pkg = $redis->get('valentine_pkg');
    

    if(empty($package)||!in_array($package,$new_pkg)){
        $pkg_key = array_rand($new_pkg);
        $package = $new_pkg[$pkg_key];
    }


    //中礼包   包名 supwater
    //处理  redis 的礼包
    $gift_number = $redis -> rpop("valentine_gift:".$package);
    if(empty($gift_number)){
        $gift_number = $redis -> rpop("valentine_gift:".$package);
        if(empty($gift_number)){
            //删除 没有礼包的包名
            $new_pkg = $redis->get('valentine_pkg');
            foreach($new_pkg as $k=>$v){
                if($v==$package){
                    echo 'unset...pkg';
                    unset($new_pkg[$k]);
                }
            }
            $redis->set('valentine_pkg',$new_pkg);
            return get_gift();
        }
    }
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
