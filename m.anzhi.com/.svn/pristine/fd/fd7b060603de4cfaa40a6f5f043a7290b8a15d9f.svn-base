<?php
include_once (dirname(realpath(__FILE__)).'/init.php');

$subject_id = isset($_GET['subject_id'])&&!empty($_GET['subject_id']) ? (int)$_GET['subject_id'] : 0;
$id_arr = array(1091,1092,1093,1094,1095,1096,1097,1098);
if(!in_array($subject_id,$id_arr)){
	echo "<script>window.location.href='/';</script>";
	exit;
}
$res = gomarket_action('soft.GoGetSoftSubjectAllList', array('GET_INFO' => TRUE, 'ID' => $subject_id, 'TYPE' => 1, 'VR' => 1, 'LIST_INDEX_SIZE' => 500,'EXTRA_OPTION_FIELD' => array('isoffice',))
);
foreach($res['DATA'] as $key => $value) {
	$i = $k =0;
	$res['DATA'][$key]['scorehtml']="";
	$i = floor($value[3] / 2);
	$k = $value[3] % 2;
	for($i1=$i;$i1>0;$i1--){
		$res['DATA'][$key]['scorehtml'] .='<img alt="" src="/images/star_01.png">';
	}
	if($k!=0)
		$res['DATA'][$key]['scorehtml'] .= '<img alt="" src="/images/star_02.png">';
	if(($i+$k)<5) {
		for($i2=(5-$i-$k);$i2>0;$i2--){
			$res['DATA'][$key]['scorehtml'] .='<img alt="" src="/images/star_03.png">';
		}	
	}
}

$download_info = array(
	'1091' => array(194, '75e9f8714504'),
	'1092' => array(195, '2988c2b94505'),
	'1093' => array(196, '309b2a584506'),
	'1094' => array(197, '2e4b4a014507'),
	'1095' => array(198, '0d610da94508'),
	'1096' => array(199, '9fc898754509'),
	'1097' => array(200, 'adc4870e4510'),
	'1098' => array(201, '671fffd54511'),
);

$weixin_hint = '微信用户请点击微信右上角<br />选择「在浏览器中打开」';
$tplObj->out['weixin_hint'] = $weixin_hint;
$tplObj->out['title'] = $res['NAME'];
$tplObj->out['subject_app'] = $res;
$tplObj->out['subject_id'] = $subject_id;
$tplObj->out['download_info'] = $download_info[$subject_id];
$tplObj->display("subject_wifi_app.html"); 

function scorehtml($result){
	foreach($result as $key => $value) {
		$i = $k =0;
		$result[$key]['scorehtml']="";
		$i = floor($value[score] / 2);
		$k = $value[score] % 2;
		for($i1=$i;$i1>0;$i1--){
			$result[$key]['scorehtml'] .='<img alt="" src="/images/star_01.png">';
		}
		if($k!=0)
			$result[$key]['scorehtml'] .= '<img alt="" src="/images/star_02.png">';
		if(($i+$k)<5) {
			for($i2=(5-$i-$k);$i2>0;$i2--){
				$result[$key]['scorehtml'] .='<img alt="" src="/images/star_03.png">';
			}	
		}
	}
	return 	$result;
}
