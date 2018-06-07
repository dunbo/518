<?php
/*
** 福利详情页
*/
include_once(dirname(realpath(__FILE__)).'/init.php');

$welfare_id = $_GET['welfare_id'];
// 获取单个福利信息
$welfare = $model->findOne(array(
	'where' => array(
		'A.id' => $welfare_id,
	),
	'field' => 'A.*,B.name as typename',
	'join' => array(
		'fl_welfare_type AS B' => array(
			'on' => array('A.typeid', 'B.id'),
		),
	),
	'table' => 'fl_welfare AS A',
	'cache_time' => 600,
), 'lottery/lottery');
// 关联软件信息
$pkg_info = gomarket_action('soft.GoGetSoftDetailPackage', array(
	'PACKAGE_NAME' => $welfare['package'],
	'VR' => 3,
	'EXTRA_OPTION_FIELD' => array(
		'A.iconurl_72','A.desc',
	),
));
$welfare['pkg_info'] = $pkg_info;
// 结束时间用于倒计时显示
$welfare['end_tm'] = $welfare['end_tm'] - time();
// 富文本内容处理
$content = str_replace('http://118.26.224.18/cmcc', getImageHost(), $welfare['content']);
$content = str_replace('&lt;', '<', $content);
$content = str_replace('&gt;', '>', $content);
$content = str_replace('&amp;', '&', $content);
$content = str_replace('&quot;', '"', $content);

$path_preg = "/<embed.+?src=\"<!--{ANZHI_IMAGE_HOST}-->(.*?)\".+?imgurl=\"(.*?)\".+?\/>/u";

if(strpos($_SERVER['SERVER_ADDR'], '192.168.0')!==FALSE || $_SERVER['SERVER_ADDR']=='127.0.0.1' || $_SERVER['SERVER_ADDR']=='124.243.198.97'){
	$new_path_preg='<video id="my-video" class="video-js vjs-big-play-centered" controls="" width="320px" height="240px" style="margin:0 auto;" preload="${3}" data-setup="{}" poster="${2}" ><source src="http://m.test.anzhi.com/cmcc${1}" type="video/mp4"></source></video>';
}else{
	$new_path_preg='<video id="my-video" class="video-js vjs-big-play-centered" controls="" width="320px" height="240px" style="margin:0 auto;" preload="${3}" data-setup="{}" poster="${2}" ><source src="http://v.cdn.anzhi.com${1}" type="video/mp4"></source></video>';
}

$content = preg_replace($path_preg, $new_path_preg, $content);
$content = str_replace('<!--{ANZHI_IMAGE_HOST}-->', getImageHost(), $content);

$path_preg = "/<iframe.+?src=\"(.*?)\".+?>/u";
$new_path_preg = '<iframe frameborder="0" width="100%" height="100%" src="${1}" allowfullscreen="">';
$content = preg_replace($path_preg, $new_path_preg, $content);
if(strstr($content, '</video>')){
	$tplObj->out['show_video'] = 1;
}

$tplObj->out['content'] = $content;
// 领取人数实时更新
$rkey_receive_random_num = 'welfare:'.$welfare_id.':receive:num';
if(!empty($redis->get($rkey_receive_random_num))){
    $welfare['click_num'] = $redis->get($rkey_receive_random_num);
}else{
	$welfareOne = $model->findOne(array(
		'where' => array(
			'id' => $welfare_id,
		),
		'field' => 'click_num,init_val',
		'table' => 'fl_welfare',
	), 'lottery/lottery');
	$redis->setx('set', $rkey_receive_random_num, (int)($welfareOne['click_num']+$welfareOne['init_val']), 600);
	$welfare['click_num'] = $welfareOne['click_num'] + $welfareOne['init_val'];
}
if(!empty($welfare_id)){
	// 福利详情页日志
	$log_data = array(
	    'imsi' => $imsi,
	    'device_id' => $_SESSION['DEVICEID'],
	    'ip' => $_SERVER['REMOTE_ADDR'],
	    'sid' => $sid,
	    'time' => time(),
	    'welfare_id' => $welfare_id, //福利id
	    'welfare_name' => $welfare['name'], //福利名称
	    'key' => 'welfare_detail'
	);
	permanentlog($welfare_log_file, json_encode($log_data));
}

$tplObj->out['welfare'] = $welfare;

if(!empty($welfare['id'])){
	$tplObj->display('lottery/welfare/detail.html');
}else {
	$tplObj->display('lottery/welfare/end.html');
}