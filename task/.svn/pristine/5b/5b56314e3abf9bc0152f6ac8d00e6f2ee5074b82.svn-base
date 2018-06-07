<?php
/*
 *   荣耀广告活动抽奖worker
 */
include dirname(__FILE__).'/../../init.php';
$config = load_config('honor_lottery_cache/redis',"honor");
if ($config) {
    $redis = new GoRedisCacheAdapter($config);
} else {
    $redis = GoCache::getCacheAdapter('redis');
}

$model = new GoModel();
$gift_base = array();
ini_set('default_socket_timeout', -1);

/*
$tmp =array(
    'uid'=>'111',
    'aid'=>1
);
$rs = do_lottery(json_encode($tmp));
var_dump($rs);
 */

load_helper('task');
$worker = get_task_worker();
$worker->addFunction("honor_lottery_worker", "do_lottery");
while ($worker->work());

function do_lottery($jobs){
    $jobs= $jobs->workload();
    $jobs = json_decode($jobs,true);
    $honor_db = 'honor_lottery/honor';
    global $redis;
    global $model;
    $uid = $jobs['uid'];
    $aid = $jobs['aid'];
    $posid = $jobs['posid'];
    $ad_str= $jobs['ad_str'];
    $ua = $jobs['ua'];
    $referer = $jobs['referer'];
    $ip= $jobs['ip'];
    $ad_list_key = 'honor:adlist:uid:'.$uid.':posid:'.$posid; 

    $rs = get_award($jobs);
    if($rs==-1)//广告
    {
        $ad = $redis->rpop($ad_list_key);
        if(empty($ad)){
            //$ad_url = 'http://u.anzhi.com/ui/index/ad_data?app_id=35&ip=&url=http%3A%2F%2Fhonoractivity.com%2FIndex%2Findex%3Faid%3D1&u_h=768&u_w=1366&rf=&ui_ssid=123';
            echo $ad_url = 'http://u.anzhi.com/ui/index/ad_data?'.$ad_str.'&ui_ssid='.$uid.'&ip='.$ip;

            $opts = array(
                'http'=>array(
                    'method'=>"GET",
                    'header'=>"User-Agent:".$ua."\r\n".
                    "Referer: ".$referer
                )
            );

            $context = stream_context_create($opts);

            $res = file_get_contents($ad_url,false, $context);
            var_dump($res);
            writelog('honor_lottery_'.$aid.'.log',json_encode($res));


            $tmp_data = json_decode($res,true);
            if(empty($tmp_data)){
                var_dump('no ad!!');
                writelog('honor_lottery_'.$aid.'.log','no ad');

            }
            $redis->setlist($ad_list_key,$tmp_data['data'],300);
            $ad = $redis->rpop($ad_list_key);
        }
        $tmp_ad = json_decode($ad,true);

        $award_data = array(
            'uid' => $uid,
            'time' => time(),
            'aid' => $aid,
            'prizename' => '广告',
            'prize_type' => 5,
            'gift_code' => $ad,
            'ad_id' => $tmp_ad['ad_id'],
            '__user_table' => 'media_lottery_award'
        );
        $insertid = $model -> insert($award_data,$honor_db);

        $resarr = array(
            'ad' => 	$ad,
            'type' => 	5
        );
        var_dump($resarr);
        writelog('honor_lottery_'.$aid.'.log',json_encode($resarr));
        return json_encode($resarr);//后期扩展 区分 奖品广告和不中奖广告 //todo
    }else
            {
                return $rs;
            }
}

function get_award($jobs){
    $honor_db = 'honor_lottery/honor';
    global $model;
    global $redis;
    //$jobs = json_decode($jobs,true);
    $prefix = 'honor_lottery';
    $uid = $jobs['uid'];
    $aid = $jobs['aid'];
    //$pos_id= $jobs['pos_id'];//广告位ID //todo

    writelog('honor_lottery_'.$aid.'.log',json_encode($jobs));
    var_dump($jobs);
    if(empty($uid)){
        return -1;
    }

    //如果活动结束了或者未开始 直接-1
    $option = array(
        'where' => array(
            'aid' => $aid
        ),
        'table' => 'media_activity',
    );

    $activity = $model->findOne($option,$honor_db);
    //var_dump($model->getsql(),$activity);


    if(time()>$activity['end_tm']||$activity['status']!=1||time()<$activity['start_tm']){
        writelog('honor_lottery_'.$aid.'.log','time is error');
        var_dump('time is error');
        return -2;
    }

    //如果缓存没了 重新生成
    //剩余奖品数量缓存
    $redis->pingConn();
    $base_key = "{$prefix}:{$aid}:base_list";

    $basenum_key = "{$prefix}:{$aid}:basenum";

    $gift_base = $redis->get($base_key);
    $basenum = $redis->get($basenum_key);
    if(empty($basenum) || empty($gift_base)){
        $option = array(
            'where' => array(
                'prize_num' => array('exp','>0'),
                'probability' => array('exp','!=0'),//中奖概率
                'aid' => $aid,
                'status' => 1,
            ),
            'table' => 'media_prize',
        );

        $prize_arr= $model->findAll($option,$honor_db);
        $gift_base = array();
        foreach($prize_arr as $v){
            $id = $v['id'];
            $num = intval($v['prize_num']);
            $name = $v['name'];
            $url= $v['url'];
            //$level = intval($v['level']);
            $type = intval($v['type']);	

            $redis->set("{$prefix}:{$aid}:prize_type:".$id,$type,1200);
            $redis->set("{$prefix}:{$aid}:prize_num:".$id,$num,1200);
            $redis->set("{$prefix}:{$aid}:prize_name:".$id,$name,1200);
            $redis->set("{$prefix}:{$aid}:prize_url:".$id,$url,1200);
            $redis->set("{$prefix}:{$aid}:my_prize_img:".$id,$v['my_prize_img'],1200);
            $redis->set("{$prefix}:{$aid}:prize_intro:".$id,$v['prize_intro'],1200);
            //$redis->set("{$prefix}:{$aid}:prize_level:".$id,$level,1200);

            //处理中奖率
            $rs = explode('/',$v['probability']);	
            if($rs[0]==0 || empty($v['probability'])){
                continue;
            }
            if(empty($basenum)){
                $basenum = $rs[1];
            }else{
                if(empty($basenum) || !$rs[1]){
                    continue;	
                }
                $basenum = min_multiple($basenum,$rs[1]);
            }
        }

        foreach($prize_arr as $v){
            $rs = explode('/',$v['probability']);	
            writelog('honor_lottery_'.$aid.'.log','jisuan:'.json_encode($rs).json_encode($basenum));
            $gift_base[$v['id']] =  $rs[0]/$rs[1]*$basenum;			
        }
        $redis->set($basenum_key,$basenum,1200);
        $redis->set($base_key,$gift_base,1200);
    }

    if(!$basenum){
        $basenum = 0;
    }

    writelog('honor_lottery_'.$aid.'.log',json_encode($gift_base).json_encode($basenum));
    var_dump($gift_base,$basenum);
    $pid = lottery($gift_base,$basenum);
    $type = $redis->get("{$prefix}:{$aid}:prize_type:".$pid);
    if($type==2||$type==3){
        $ipnum = get_brush_byip($aid);
        if($ipnum>=2000000){//todo
            writelog('honor_lottery_'.$aid.'.log','防刷:ip规则超过2人');
            var_dump('防刷:ip规则超过2人');
            return -1;
        }
    }
    //中奖，检查数量 如数量不足 返回未中奖
    //$pid=3;
    if($pid!=-1){
        if($type==5){
            return -1;
        }
        //$level = $redis->get("{$prefix}:{$aid}:prize_level:".$pid); //level
        $url = $redis->get("{$prefix}:{$aid}:prize_url:".$pid);

        // 奖品数量-1
        $now_num = $redis -> setx('incr',"{$prefix}:{$aid}:prize_num:".$pid, -1);
        echo 'now_num:'.$now_num."\n";
        writelog('honor_lottery_'.$aid.'.log','pid is '.$pid.',num is '.$now_num);
        var_dump('pid is '.$pid.',num is '.$now_num);
        if ($now_num < 0) {
            echo 'no shiwu'."\n";
            // 没有剩余奖品了，把缓存数量还原为0
            $now_num = $redis -> set("{$prefix}:{$aid}:prize_num:".$pid, 0);
            writelog('honor_lottery_'.$aid.'.log','no prize,return -1');
            return -1;
        }

        //减少实际数量
        $where = array(
            'id' =>$pid,
            'aid' => $aid,
        );
        $data = array(
            'prize_num' => array('exp','`prize_num`-1'),
            '__user_table' => 'media_prize',
        );
        $model -> update($where,$data,$honor_db);		

        $prizename = $redis -> get("{$prefix}:{$aid}:prize_name:".$pid);
        $my_prize_img = $redis -> get("{$prefix}:{$aid}:my_prize_img:".$pid);
        $prize_intro = $redis -> get("{$prefix}:{$aid}:prize_intro:".$pid);

        if($type==2||$type==3){ //实体  话费
            $award_data = array(
                'uid' => $uid,
                //'level' => $level,
                'time' => time(),
                //'status' => 0,
                'aid' => $aid,
                'pid' => $pid,
                'prizename' => $prizename,
                'prize_type' => $type,
                'my_prize_img' => $my_prize_img,
                'prize_intro' => $prize_intro,
                '__user_table' => 'media_lottery_award'
            );
            $insertid = $model -> insert($award_data,$honor_db);
            set_brush_byip($aid);
            var_dump($model->getsql());
        }else if($type==1){ //虚拟 优惠券
            //处理  redis 的礼包
            //$gift_number = json_decode($redis -> rpop("honor_gift_aid:{$aid}:pid:{$pid}"),true);
            $gift_number = $redis -> rpop("honor_gift_aid:{$aid}:pid:{$pid}");
            var_dump('gift:',$gift_number);
            writelog('honor_lottery_'.$aid.'.log','gift:'.json_encode($gift_number));
            if(empty($gift_number)){
                return -1;
            }

            $award_data = array(
                'uid' => $uid,
                //'level' => $level,
                'time' => time(),
                //'status' => 0,
                'aid' => $aid,
                'pid' => $pid,
                'prizename' => $prizename,
                'prize_type' => $type,
                'gift_code' => $gift_number,
                '__user_table' => 'media_lottery_award'
            );
            $insertid = $model -> insert($award_data,$honor_db);
            var_dump($model->getsql());

            //修改礼包表的状态
            $where = array(
                'coupon_code' =>$gift_number, //redis里来的
                'aid' =>$aid,
                'awardid' =>$pid,
            );
            $data = array(
                'status' => 1,
                'update_tm' => time(),
                'uid' => $uid,
                '__user_table' => 'media_coupon'
            );
            $model -> update($where,$data,$honor_db);
            var_dump($model->getsql());
        }

        $resarr = array(
            'pid' => $pid,
            //'level' => $level,
            'prizename' => 	$prizename,
            'gift_number' => 	$gift_number,
            'type' => 	$type,
            'url' => 	$url,
            'insertid' => 	$insertid,
        );
        print_r($resarr);
        writelog('honor_lottery_'.$aid.'.log',json_encode($resarr));
        var_dump($resarr);
        return json_encode($resarr);
    }else{
        writelog('honor_lottery_'.$aid.'.log','return -1');
        var_dump('-1');
        return -1;
    }
}


//最小公倍数    
function min_multiple($a,$b){
    $m = max($a,$b);
    $n = min($a,$b);
    for($i=1; ; $i++)
    {
        if (is_int($m*$i/$n))
        {
            return $mix=$m*$i;
        }
    }
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

//日志
function writelog($filename,$msg){
    $now = time();
    $path = "/data/att/permanent_log/admin_cron_log/".date("Y-m-d", $now);
    if(!file_exists($path)){
        mkdir($path, 0755, true);
    }	
    $path_log = $path."/".$filename;
    $msg = date('Y-m-d H:i:s', $now). " {$msg}\n";
    file_put_contents($path_log, $msg, FILE_APPEND);
}


//防刷 ip写入
function set_brush_byip($aid){
    global $redis;
    $ip=$_SERVER['REMOTE_ADDR'];
    $ip_award = "brush:".$aid.':ip_award:'.$ip;
    $ip_award_num = $redis->setx('incr', $ip_award, 1);
    $redis->expire($ip_award,60*86400);
    return $ip_award_num;
}

//防刷 通过 ip读取
function get_brush_byip($aid){
    global $redis;
    $ip=$_SERVER['REMOTE_ADDR'];
    $ip_award = "brush:".$aid.':ip_award:'.$ip;
    $ip_award_num = $redis->get($ip_award);
    return $ip_award_num;
}
