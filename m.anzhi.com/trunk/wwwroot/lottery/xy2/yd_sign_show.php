<?php
include_once ('./fun.php');
$sid = $_GET['sid'] ? $_GET['sid'] : $_POST['sid'];
$active_id = $_GET['aid'] ? $_GET['aid'] : $_POST['aid'];
session_begin($sid);
$build_query = http_build_query($_GET);
$url = "http://promotion.anzhi.com/lottery/xy2/yd_index.php?".$build_query;

if(isset($_SESSION['USER_UID'])){//已登录
	$uid = $_SESSION['USER_UID'];
}else{//未登录 跳转到首页
	if($_POST){
		exit(json_encode(array('code'=>2,'url'=>$url)));
	}else{
		header("Location: {$url}");
                exit(0);
	}
}

$this_day=date('Ymd')-20160101+1;

$sign_str = $redis->get('yd:sign_str:uid:'.$uid,$sign_str);
if(is_null($sign_str)){
    $option = array(
            'where' => array(
                    'uid' => $uid,
            ),
            'table' => 'xy2_draw_userinfo',
            'field' => 'sign_str',
    );
    $sign = $model->findOne($option,'lottery/lottery');
    $redis->set('yd:sign_str:uid:'.$uid,$sign['sign_str'],86400*6);
    $sign_str = $sign['sign_str'];
}


$rs = strpos($sign_str,"1");
if($rs==false){
        $spot1='spot_gray';
    if(1<$this_day){
        //灰色
        $star1='star_gray';
    }else{
        //白色
        $star1='star_lightgray';
    }
}else{
    //金色
        $star1='star_gold';
        $spot1='spot_gold';
}

$rs = strpos($sign_str,"2");
if($rs==false){
        $spot2='spot_gray';
    if(2<$this_day){
        //灰色
        $star2='star_gray';
    }else{
        //白色
        $star2='star_lightgray';
    }
}else{
    //金色
        $star2='star_gold';
        $spot2='spot_gold';
}

$rs = strpos($sign_str,"3");
if($rs==false){
        $spot3='spot_gray';
    if(3<$this_day){
        //灰色
        $star3='star_gray';
    }else{
        //白色
        $star3='star_lightgray';
    }
}else{
    //金色
        $star3='star_gold';
        $spot3='spot_gold';
}

$rs = strpos($sign_str,"4");
if($rs==false){
    $spot4='spot_gray';
    if(4<$this_day){
        //灰色
        $star4='star_gray';
    }else{
        //白色
        $star4='star_lightgray';
    }
}else{
    //金色
        $star4='star_gold';
        $spot4='spot_gold';
}

$rs = strpos($sign_str,"5");
if($rs==false){
        $spot5='spot_gray';
    if(5<$this_day){
        //灰色
        $star5='star_gray';
    }else{
        //白色
        $star5='star_lightgray';
    }
}else{
    //金色
        $star5='star_gold';
        $spot5='spot_gold';
}


$option = array(
	'where' => array(
		'type' => 2,
		'status' => 1,
		'aid' => $active_id
	),
	'table' => 'xy2_draw_award',
	'field' => 'username,prizename',
	'order' => 'id',
        'cache_time'=>1200,
);
$prizeuser = $model->findAll($option,'lottery/lottery');
if(empty($prizeuser)){
	$lunbo = '活动已开启，快去翻卡吧！中奖率100%哦！';
}else{
    foreach($prizeuser as $v){
        $lunbo = $lunbo.'恭喜用户'.str_replace_cn_new($v['username'],1,-2).',好运连连,翻卡获得'.$v['prizename'].';';
    }
}
$tplObj -> out['lunbo'] = $lunbo;

$tplObj -> out['star1'] = $star1;
$tplObj -> out['star2'] = $star2;
$tplObj -> out['star3'] = $star3;
$tplObj -> out['star4'] = $star4;
$tplObj -> out['star5'] = $star5;
$tplObj -> out['spot1'] = $spot1;
$tplObj -> out['spot2'] = $spot2;
$tplObj -> out['spot3'] = $spot3;
$tplObj -> out['spot4'] = $spot4;
        $tplObj -> out['new_static_url'] = $configs['new_static_url'];
$is_sign = $redis->get('yd:sign:uid:'.$uid.':date:'.date('Ymd'));
$tplObj -> out['is_sign'] = $is_sign;
$fan_num= $redis->get('yd:sign:lottery_num:uid:'.$uid);
$fan_num = empty($fan_num)?0:$fan_num;
        $tplObj -> out['fan_num'] = $fan_num;
	$tplObj -> out['static_url'] = $configs['static_url'];
	$tplObj -> out['username'] = $_SESSION['USER_NAME'];
	$tplObj -> out['aid'] = $active_id;	
	$tplObj -> out['sid'] = $sid;	
	$tplObj -> out['version_code'] = $_SESSION['VERSION_CODE'];	
	$tplObj -> display('lottery/xy2/yd_sign_show.html');
