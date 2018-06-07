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
$worker->addFunction("rebate_draw", "get_award");
while ($worker->work());


function get_award($jobs){
    global $model;
    global $redis;
    $jobs = $jobs->workload();
    $jobs = json_decode($jobs,true);
    $uid = $jobs['uid'];
    $username= $jobs['username'];
    $user_game = $jobs['user_game'];

    $nowtime = strtotime(date('Y-m-d'));

    $gift_base = array();
    $gift_base[1] = 0;
    $gift_base[2] = 5;
    $gift_base[3] = 20;
    $gift_base[4] = 50;
	$uid_arr = array('20140305212403FdHd0trr74','20140803212921zJQ1YkcypM','20150219142012CmU3cQNH6J','2015022216221818l4WS1H59','20150328092426LsSbeHXaTk','20150426095434693EA9Vj6n','20150501110540j217LxOnzv');
	if(in_array($uid,$uid_arr)){
		$pid = -1;
	}else{
		$pid = lottery($gift_base,7500);
	}
    
    //中奖，检查数量 如数量不足 返回未中奖
    if($pid!=-1){
        $redis->pingConn();
        // 奖品数量-1
        $now_num = $redis -> setx('incr','rebate_draw_prize_num_'.$pid, -1);
        if($now_num < 0) {
            // 没有剩余奖品了，把缓存数量还原为0
            $redis -> set('rebate_draw_prize_num_'.$pid, 0);
        }
    }
    if($pid!=-1 && $now_num >=0){
        $name = $redis -> get('rebate_draw_prize_name_'.$pid);
        //减少库中实际数量
        $param =array();
        $param['type'] = 2;
        $param['uid'] = $uid;
        $param['username'] = $username;
        $param['pid'] = $pid;
        $param['prizename'] = $name;
        $param['now_num'] = $now_num;

        $task_client = get_task_client();
        $task_client->doBackground('rebate_draw_db',json_encode($param));
        $resarr = array();
        $resarr['pid'] = $pid;
        $resarr['name'] = $name;
        $resarr['uid'] = $uid;
        $resarr['type'] = 1;//实体
        return json_encode($resarr);
    }else{
        $redis->pingConn();
        //处理  redis 的礼包
        $gift_number = getPackagegift($redis,$user_game);
        //var_dump($gift_number);
        if(empty($gift_number)){
            //echo '2....';
            $gift_number = getPackagegift($redis,$user_game);
        }

        $param =array();
        $param['type'] = 1;
        $param['uid'] = $uid;
        $param['username'] = $username;
        $param['gift_number'] = json_decode($gift_number,true);
        $task_client = get_task_client();
        $task_client->doBackground('rebate_draw_db',json_encode($param));
        //handle(json_encode($param));
        $resarr = array();
        $resarr['uid'] = $uid;
        $resarr['pid'] = 6;
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

function getPackagegift($redis,$user_game=''){
    $gift_num = $redis->get('rebate_draw_gift_num');
    $package = '';
    if($user_game){
        $user_game = array_filter(explode(',',$user_game));
        $key = array_rand($user_game);
        $game = $user_game[$key];
        $num = $gift_num[$game];
        if($num>0){
            $package = $game;
        }else{
            $i = 0;
            foreach($user_game as $v){
                if($gift_num[$v] > 0) $package = $v;
            }
        }
    }
    if(!$package){
        $package = array_rand($gift_num);
        if($gift_num[$package] <= 0){       
            foreach($gift_num as $p=>$val){
                if($val > 0) $package = $p;
            }
        }
    }
    $gift_num[$package] = $gift_num[$package]-1;
    $redis->set('rebate_draw_gift_num',$gift_num,86400*10);
    return $redis->rpop('rebate_draw_gift_'.$package);

}