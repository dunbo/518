<?php
include_once (dirname(realpath(__FILE__)).'/init.php');
$model = new GoModel();
$id = $_GET['id'];
//$result = $model -> table('sj_activity_page') -> where(array('ap_id' => $id)) -> select();
$opt = array(
	'table' => 'sj_activity_page',
	'where' =>array(
		'ap_id' =>$id
	),
);
$result = $model->findAll($opt);

if(!$result[0]['winning_comment']){
	$result[0]['winning_comment'] ="您共有X次获奖机会";
}
$result[0]['winning_comment'] = str_replace('X','<span id="times"></span>',$result[0]['winning_comment']);
if(!$result[0]['button_comment']){
	$result[0]['button_comment'] = "提交并一键下载";
}
if(!$result[0]['download_comment']){
	$result[0]['download_comment'] = '恭喜，您已成功参与该活动！ 请前往"下载"页面确保成功下载并安装活动应用，即可获得X次获奖机会。';
}
$result[0]['download_comment'] = str_replace('X','<span id="packages"></span>',$result[0]['download_comment']);
$result[0]['ap_rule'] = nl2br($result[0]['ap_rule']);

//$category_result = $model -> table('sj_actives_category') -> where(array('active_id' => $result[0]['ap_id'],'status' => 1)) -> order('rank') ->select();

$opts = array(
	'table' => 'sj_actives_category',
	'where' =>array(
		'active_id' => $result[0]['ap_id'],
		'status' => 1,
	),
	'order' => 'rank'
);
$category_result = $model->findAll($opts);
foreach($category_result as $key => $val){
	//$soft_result = $model -> table('sj_actives_soft') -> where(array('category_id' => $val['id'],'status' => 1)) -> order('rank') -> select();
	$optes = array(
		'table' => 'sj_actives_soft',
		'where' =>array(
			'category_id' =>$val['id'],
			'status' => 1,
		),
		'order' => 'rank'
	);
	$soft_result = $model->findAll($optes);

	if($soft_result){
		$category_soft_count[$key] = 1;
	}
	foreach($soft_result as $k => $v){
		//$soft_have_result = $model -> table('sj_soft') -> where(array('package' => $v['package'],'status' => 1,'hide' => 1)) -> order('softid DESC') -> limit('0,1') -> select();
		//$soft_size = $model -> table('sj_soft_file') -> where(array('softid' => $soft_have_result[0]['softid'],'package_status' => 1)) -> select();
		$option = array(
			'table' => 'sj_soft',
			'where' =>array(
				'package' =>$v['package'],
				'status' => 1,
				'hide' => 1,
			),
			'order' => 'softid DESC',
			'limit' =>1
		);
		$soft_have_result = $model->findAll($option);
		
		$options = array(
			'table' => 'sj_soft_file',
			'where' =>array(
				'softid' => $soft_have_result[0]['softid'],
				'package_status' => 1
			),
		);
		$soft_size = $model->findAll($options);
		
		$v['soft_size'] = sprintf('%.1f',$soft_size[0]['filesize']/1024/1024);
		$v['iconurl_72'] = $soft_size[0]['iconurl_72'];
		$v['softid'] = $soft_have_result[0]['softid'];
		$soft_result[$k] = $v;
	}

	$val['soft_result'] = $soft_result;
	$category_result[$key] = $val;
}
$my_category_soft = array_sum($category_soft_count);
//$this -> assign('my_category_soft',$my_category_soft);
//$this -> assign('result',$result);
//$this -> assign('category_result',$category_result);

$tplObj -> out['imgurl'] = getImageHost();
if($result[0]['ap_type']==1){
	$tplObj->out['my_category_soft'] = $my_category_soft;
	$tplObj->out['result'] = $result;
	$tplObj->out['category_result'] = $category_result;
	$download_page = 'downloaded_'.$_GET['id'].'.html';
	$tplObj->out['download_html'] = $download_page;
	$tplObj->display("activepage.html");
}else if($result[0]['ap_type']==2){
	$tplObj->out['result'] = $result;
	$tplObj->display("preheat.html");
}else if($result[0]['ap_type']==3){
	$tplObj->out['result'] = $result;
	$tplObj->display("preheat.html");
}else if($result[0]['ap_type']==4){
	$tplObj->out['result'] = $result;
	$tplObj->display("preheat.html");
}else if($result[0]['ap_type']==5){
	$tplObj->out['result'] = $result;
	$tplObj->display("noflux_activity.html");
}