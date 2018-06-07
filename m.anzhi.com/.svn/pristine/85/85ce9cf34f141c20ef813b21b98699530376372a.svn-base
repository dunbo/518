<?php
include_once (dirname(realpath(__FILE__)).'/../init.php');
$page_id = $_GET['id'];
$model = new GoModel();
$opts = array(
	'table' => 'sj_external_promotion',
	//'cache_time' => 3603,
	'where' => array(
		'id' => $page_id
	),
);
$pages = $model->findOne($opts);
$key = 'external_promotion';
if ($pages && $pages['channel_id']) {
	$option = array(
		'table' => 'sj_channel',
		'where' => array(
			'cid' => $pages['channel_id']
		)
	);
	$channel = $model->findOne($option);
	$pages['chl_cid'] = $channel['chl_cid'];
}
if($page_id)
{
	//复制链接打开的处理方式  写日志
	$log_data = array(
			'id'=>$pages['id'],
			'page_name'=>$pages['page_name'],
			'page_title'=>$pages['page_title'],
			'ip' => $_SERVER['REMOTE_ADDR'],
			'content_id'=>$pages['content_id'],
			'channel_id'=>$pages['channel_id'],
			'time' => time(),
			'key'  => $key,
		);
		permanentlog('anzhi_external_promotion.log', json_encode($log_data));
		$tpl->out['image_url'] = $image_url = getImageHost();
		$content = str_replace('<!--{ANZHI_IMAGE_HOST}-->', $image_url, $pages['page_content']);
}
else
{
	//预览展示的效果
	if($_POST) 
	{
		$img_pre = '';
		if($_SERVER['SERVER_ADDR']=='192.168.0.99') 
		{
			$img_pre = 'http://9.admin.goapk.com/Public/js/kindeditor/attached';
		}
		else if($_SERVER['SERVER_ADDR']=='118.26.203.23') {
			$img_pre = 'http://518test.anzhi.com/Public/js/kindeditor/attached';
		} 
		else 
		{
			$img_pre = 'http://518.anzhi.com/Public/js/kindeditor/attached';
		}
		$pages = array(
			'page_name' => $_POST['tmp_page_name'],
			'page_title' => $_POST['tmp_title'],
			'content' => str_replace('/Public/js/kindeditor/attached',$img_pre,$_POST['tmp_content']),
			'page_btn_type' => $_POST['tmp_btn_type'],
			'btn_text_color' => $_POST['tmp_text_color'],
			'page_text_font_size' => $_POST['tmp_text_font_size'],
			'btn_text_alignment' => $_POST['tmp_text_alignment'],
			'btn_text_content' => $_POST['tmp_text_content'],
			'btn_pic' => $_POST['tmp_btn_pic'],
		);
		$content = $pages['content'];
	}
}
if($pages['page_btn_type'] ==2)
{
	$tpl->out['image_url'] = $image_url = getImageHost();
	$btn_pic_url = $image_url.$pages['btn_pic'];
	$tplObj->out['btn_pic_url'] = $btn_pic_url;
}
else
{
	if($pages['btn_text_alignment'] ==1)
	{
		$tplObj->out['alignment'] = "left";
	}
	else if($pages['btn_text_alignment'] ==2)
	{
		$tplObj->out['alignment'] = "center";
	}
	else
	{
		$tplObj->out['alignment'] = "right";
	}
}
$tplObj->out['content'] = $content;
$tplObj->out['pages'] = $pages;
$tplObj->display("promotion.html");
?>
