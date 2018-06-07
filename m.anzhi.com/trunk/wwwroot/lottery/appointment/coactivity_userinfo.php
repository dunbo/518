<?php

include_once (dirname(realpath(__FILE__)).'/../../init.php');

$model = new GoModel();
session_begin();
$imsi = $_SESSION['USER_IMSI'];
$aid = $_GET['aid'] ? $_GET['aid'] : $_POST['aid'];
if(ctype_digit($aid)==false){
    exit(0);
}
$uid = $_SESSION['USER_UID'];
$imei = $_SESSION['DEVICEID'];
$model = new GoModel();

if($_POST){

        $aid=$_POST['aid'];
	$option = array(
		'where' => array(
			'uid' => $uid,
			'aid' => $aid
		),
		'table' => 'gm_lottery_num',
	);
	$userinfo = $model->findOne($option,'lottery/lottery');	
    if($userinfo){
        $new_data = array(
			'tel' => $_POST['mobile_phone'],
			'name' => $_POST['lxname'],
			'address' => $_POST['address'],
                        'imsi'=>$imsi,
			'update_tm' => time(),
			'__user_table' => 'gm_lottery_num'
        );
        $where = array(
			'uid' => $uid,
			'aid' =>$aid,
        );
        $ret =  $model->update($where, $new_data,'lottery/lottery');
    }else {//新增
        $new_data = array(
			'uid' => $uid,
			'aid' => $aid,
                        'imsi'=>$imsi,
                        'imei'=>$imei,
			'tel' => $_POST['mobile_phone'],
			'name' => $_POST['lxname'],
			'address' => $_POST['address'],
			'update_tm' => time(),
			'__user_table' => 'gm_lottery_num'
        );	
        $ret =  $model->insert($new_data,'lottery/lottery');
    }

		$log_data = array(
			'uid'=>$uid,
			'username' => $_SESSION['USER_NAME'],
			'tel' => $_POST['mobile_phone'],
			'name' => $_POST['lxname'],
			'address' => $_POST['address'],
			'time' => time(),
			'activity_id' => $aid,
			'key' => 'info_edit'
		);
		permanentlog('activity_'.$aid.'.log', json_encode($log_data));	


    echo 1;
    exit(0);
}


$prize_option = array(
	'where' => array(
		'A.uid' => $uid,
		'A.aid' => $aid,
		//'A.status' => 1
	),
	'table' => 'gm_lottery_award AS A',
	//'field' => 'A.id,A.level,A.telphone,A.name as my_name,A.address,A.time as my_time,A.pid,B.name,B.type,B.desc',
        'field' => 'A.prizename,A.gift_code,A.id,A.level,A.telphone,A.name as my_name,A.address,A.time as my_time,A.pid,B.name,B.type,B.desc',
	'join' => array(
		'gm_lottery_prize AS B' => array(
			'on' => array('A.pid', 'B.pid'),
		),
	)
);
$prize_result = $model -> findAll($prize_option,'lottery/lottery');
$have_shi=2;
foreach($prize_result as $pv){
    if($pv['type']==1){
        $have_shi=1;
        break;
    }
}

$tplObj -> out['have_shi'] = 1;

$probability_option = array(
	'where' => array(
		'A.uid' => $uid,
		'A.aid' => $aid,
		//'A.status' => 1
	),
	'table' => 'gm_virtual_prize AS A',
	'field' => 'A.pid,A.first_text,A.second_text,A.third_text,A.pid,A.status,A.aid,A.imsi,A.update_tm as my_time,B.pid,B.name,B.type,B.level,B.aid,B.status,B.desc',
	'join' => array(
		'gm_lottery_prize AS B' => array(
			'on' => array('A.pid', 'B.pid')
		)
	)
);
$probability_result = $model -> findAll($probability_option,'lottery/lottery');

$tplObj -> out['gift_num'] = count($probability_result);//是否展示个人信息

foreach($probability_result as $key => $val){
	$softinfo = gomarket_action('soft.GoGetSoftDetailPackage', array('PACKAGE_NAME' => $val['third_text']));
	$val['softid'] = $softinfo['ID'];
	$val['soft_name'] = $softinfo['SOFT_NAME'];
	$val['versioncode'] = $softinfo['SOFT_VERSION_CODE'];
	$val['size'] = $softinfo['SOFT_SIZE'];
	$probability_result[$key] = $val;
}

if($prize_result && $probability_result){
	$all_result = array_merge($prize_result,$probability_result);
}elseif(!$prize_result && $probability_result){
	$all_result = $probability_result;
}elseif($prize_result && !$probability_result){
	$all_result = $prize_result;
}

foreach($all_result as $key => $val){
	$result_time[] = $val['level'];
}

array_multisort($result_time,SORT_NUMERIC,SORT_ASC,$all_result);

$activity_option = array(
	'where' => array(
		'id' => $aid
	),
	'cache_time' => 300,
	'table' => 'sj_activity'
);
$activity_result = $model -> findOne($activity_option);

$page_option = array(
	'where' => array(
		'ap_id' => $activity_result['activity_page_id']
	),
	'cache_time' => 300,
	'table' => 'sj_activity_page'
);
$page_result = $model -> findOne($page_option);

    $tplObj -> out['gift_name'] = '礼包码';

	$option = array(
		'where' => array(
			'uid' => $uid,
			'aid' => $aid
		),
		'table' => 'gm_lottery_num',
	);
	$userinfo = $model->findOne($option,'lottery/lottery');	


//礼包
	$gift_option = array(
		'where' => array(
			'uid' => $uid,
			'ap_id' => $page_result['ap_id'],
			'status' => 1
		),
		'table' => 'gm_bespoke_gift_code',
	);
	$gift_result = $model -> findAll($gift_option,'lottery/lottery');


//后台通用样式替换
if($page_result['show_award']==1){
    $page_result['second_text_color']='#414141';
    $page_result['third_text_color']='#02b2ff';
    $page_result['bg_color']='#ffffff';
    $page_result['button_text_color']='#ffffff';
    $page_result['button_color']='#ffb926';
    $page_result['info_color']='#ffffff';
    $page_result['submit_button_color']='#ffb926';
    $page_result['button_pic']='';
}


$tplObj -> out['gift_result'] = $gift_result;
$version_code = $_SESSION['VERSION_CODE'];
$tplObj -> out['version_code'] = $version_code;

$tplObj -> out['end_tm'] = date('Y年m月d日',$activity_result['end_tm']);
$tplObj -> out['userinfo'] = $userinfo;
$tplObj -> out['aid'] = $aid;
$tplObj -> out['sid'] = $_GET['sid'];
$tplObj -> out['from'] = $_GET['from'];
$tplObj -> out['afrom'] = $_GET['afrom'];
$tplObj -> out['imgurl'] = getImageHost();
$tplObj -> out['static_url'] = $configs['static_url'];
$tplObj -> out['page_result'] = $page_result;
$tplObj -> out['activity_result'] = $activity_result;
$tplObj -> out['all_result'] = $all_result;
$tplObj -> display('lottery/appointment/user_info.html');
