<?php
include_once (dirname(realpath(__FILE__)).'/../init.php');
$model = new GoModel();
$aid = $_GET['aid'];
$time = time();
$option = array(
	'where' => array(
		'id' => $aid,
	),
	'table' => 'sj_activity',
	'field' => 'pre_url,end_url,url,start_tm,end_tm',
	'cache_time' => 30*60
);
$activity = $model->findOne($option);	
$build_query = http_build_query($_GET);
if($activity['end_tm'] <= $time){
	//结束
	$pos = strpos($activity['end_url'],"?");
	if($pos !== false){
		$str = "&";
	}else{
		$str = "?";
	}	
	//$url = $activity['end_url'].$str."aid=".$aid;
	$url = $activity['end_url'].$str.$build_query;
}else if($activity['start_tm'] > $time){
	if(empty($activity['pre_url'])){
		exit(json_encode(array('code'=>0,'msg'=>'无设置预告页面')));
	}
	//未开始
	$pos = strpos($activity['pre_url'],"?");
	if($pos !== false){
		$str = "&";
	}else{
		$str = "?";
	}		
	//$url = $activity['pre_url'].$str."aid=".$aid;
	$url = $activity['pre_url'].$str.$build_query;
}else{
	$pos = strpos($activity['url'],"?");
	if($pos !== false){
		$str = "&";
	}else{
		$str = "?";
	}
	//已开始
	//$url = $activity['url'].$str."aid=".$aid;
	$url = $activity['url'].$str.$build_query;
}
//var_dump($url);exit;
header("Location: {$url}"); 
