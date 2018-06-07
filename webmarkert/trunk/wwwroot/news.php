<?php
include_once(dirname(realpath(__FILE__)).'/init.php');

$model = new GoModel();

$content_id = $_GET['id'];

if(!ctype_digit($content_id)){
	exit(json_encode(array('code'=>2)));
}

$content_info = $model->findOne(array(
	'where' => array(
		'status' => 1,
		'passed' => 2,
		'title' => array('exp', "!=''"),
		'template_select' => array('exp', "!=2"),
		'id' => $content_id,
	),
	'cache_time' => '600',
	'field' => 'id,package,title,show_style,visit_count,template_select,az_style_content,explicit_pic,create_tm',
	'table' => 'sj_soft_content_explicit'
));

if(!$content_info){
	exit(json_encode(array('code'=>0)));
}

$return = array();

$return['title_head'] = $content_info['title']; //1标题
$return['title_heat'] = $content_info['visit_count']; //2热度

$soft_info = $model->findOne(array(
	'where' => array(
		'status' => 1,
		'hide' => 1,
		'package' => $content_info['package'],
	),
	'order' => 'softid desc',
	'cache_time' => '600',
	'field' => 'softname',
	'table' => 'sj_soft'
));

$return['v_name'] = $soft_info['softname'] ? $soft_info['softname'] : ''; //3软件名

$text = '';
if($content_info['template_select'] == 1){
	//安智样式
	$content_info['az_style_content'] = json_decode($content_info['az_style_content'], true);
	foreach($content_info['az_style_content'] as $val){
		if($val['article'] == ''){
			break;
		}
		$text .= $val['article'];
	}
}else if($content_info['template_select'] == 3){
	//富文本样式
	$text = strip_tags($content_info['az_style_content']);
	$text = str_replace("&nbsp;", '', $text);
	$regex = '/poster=\"([^\"]*?)\"/i';
    preg_match_all($regex, $content_info['az_style_content'], $viedo_img_arr);
}

if(!empty($viedo_img_arr[1])){
	$return['title_vimg'] = $viedo_img_arr[1][0]; //3视频缩略图
	if($viedo_img_arr[1][0] == 'http://img3.anzhi.com//static/default_black.jpg'){
		$return['title_vimg'] = 'http://img3.anzhi.com/img/market/poster.jpg';
	}
}else{
	$return['title_vimg'] = '';
}

$len = mb_strlen($text, 'utf-8');
if($len > 200){
	$text = mb_substr($text, 0, 197, 'utf-8');
	$text .= '...';
}

$return['title_intro'] = $text; //4详情

$content_info['explicit_pic'] = json_decode($content_info['explicit_pic'], true);
if($content_info['show_style']==1){
	if($content_info['explicit_pic']['pic0']){
		$return['btn_img']['img'][] = getIconHost().$content_info['explicit_pic']['pic0'];
	}else{
		$return['btn_img']['img'][] = '';
	}
}elseif($content_info['show_style']==2){
	if($content_info['explicit_pic']['pic1'] && $content_info['explicit_pic']['pic2'] && $content_info['explicit_pic']['pic3']){
		$return['btn_img']['img'][] = getIconHost().$content_info['explicit_pic']['pic1'];
		$return['btn_img']['img'][] = getIconHost().$content_info['explicit_pic']['pic2'];
		$return['btn_img']['img'][] = getIconHost().$content_info['explicit_pic']['pic3'];
	}else{
		$return['btn_img']['img'][] = '';
	}
}

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://m.anzhi.com/softnews.php");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, array('package'=>$content_info['package'],'act'=>'get_maybe_like'));
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$res = curl_exec($ch);
curl_close($ch);

$res = json_decode($res, true);
if(is_array($res) && !empty($res)){
	foreach ($res as $key => $val) {
		$return['content_extend']['content_extend'.$key][] = $val['id'];
		$return['content_extend']['content_extend'.$key][] = getIconHost().$val['pic'];
		$return['content_extend']['content_extend'.$key][] = $val['title'];
		$return['content_extend']['content_extend'.$key][] = date('Y-m-d', $val['create_tm']);
	}
}else{
	$return['content_extend'] = '';
}

$return['code'] = 1;

echo json_encode($return);
