<?php
error_reporting(0);
//error_reporting(E_ALL ^ E_NOTICE);
define('APP_NAME', 'www');
date_default_timezone_set('Asia/Shanghai');
define('ROOT',dirname(__FILE__).'/');

if($_SERVER['SERVER_ADDR']=='192.168.0.99') {
	define('IMGATT_HOST','http://192.168.0.99/testdata');
} elseif($_SERVER['SERVER_ADDR']=='118.26.203.23'){
	define('IMGATT_HOST','http://118.26.203.23/cmcc');
}else {
	define('IMGATT_HOST', 'http://img1.anzhi.com');
}

require_once(dirname(__FILE__).'/../../GoPHP/config/config.inc.php');

$link = @mysql_connect('dbs.goapk.com','azmk','mkaz)#@)2012pwd');
if(!$link) {
	exit("mysql_connect error");
} else {
	mysql_set_charset('utf8',$link);
	mysql_query("sql_mode=''",$link);
	mysql_select_db('newgomarket',$link);
}

$year_soft = $soft = $soft_file = $package = $scenephoto = $mediapartners = array();
$package_str = $scenephoto_more = '';
$query = $rs = '';

$year_query = mysql_query("SELECT * FROM sl_year_list WHERE year = 2013  AND status = 1 ORDER BY year ");
$year_result = mysql_fetch_array($year_query,MYSQL_ASSOC);
$year_channel_query = mysql_query("SELECT * FROM sl_year_channel WHERE status = 1 AND year_id = ".$year_result['id']."");
while($year_channel_result = mysql_fetch_array($year_channel_query,MYSQL_ASSOC)){
	$channel_arr[] = $year_channel_result['channel_id'];
}
foreach($channel_arr as $k => $v){
	$channel_query = mysql_query("SELECT * FROM sl_channel_list WHERE id = ".$v."");
	$channel_result = mysql_fetch_array($channel_query,MYSQL_ASSOC);
	$channel[] = $channel_result;
}

$query = mysql_query("SELECT * FROM sj_year_soft WHERE year = ".$year_result['id']."  ORDER BY pos ASC,id ASC",$link);
while($rs = mysql_fetch_array($query,MYSQL_ASSOC)) {
	if($rs['package']){
		$year_soft[$rs['channel']][] = $rs;
		$package[] = $rs['package'];
	}
}

//软件对应的icon,下载量,星星评级
//下载量,评分
$package_str = "'".implode("','",$package)."'";
$query = mysql_query("SELECT softid,package,total_downloaded,score FROM sj_soft WHERE package IN ({$package_str}) AND status=1 AND hide=1 AND channel_id=''",$link);
while($rs = mysql_fetch_array($query,MYSQL_ASSOC)) {
	$soft[$rs['package']] = $rs;
}
//icon图标
$query = mysql_query("SELECT apk_name,iconurl FROM sj_soft_file WHERE apk_name IN ({$package_str})",$link);
while($rs = mysql_fetch_array($query,MYSQL_ASSOC)) {
	$soft_file[$rs['apk_name']] = $rs;
}

//软件信息汇总
if($year_soft) {
	foreach($year_soft as $key=>$val) {
		foreach($val as $k=>$v) {
			$year_soft[$key][$k]['href'] = "http://www.anzhi.com/soft_{$soft[$v['package']]['softid']}.html";
			$year_soft[$key][$k]['icon'] = IMGATT_HOST.$soft_file[$v['package']]['iconurl'];
			$year_soft[$key][$k]['stars'] = $soft[$v['package']]['score'];
			$year_soft[$key][$k]['str_downloaded'] = num_format($soft[$v['package']]['total_downloaded'],2);

		}
	}
}


//现场图片
$query = mysql_query("SELECT * FROM sj_year_scenephoto WHERE year = 2 ORDER BY id ASC LIMIT 0,16",$link);
while($rs = mysql_fetch_array($query,MYSQL_ASSOC)) {
	$rs['photo'] = IMGATT_HOST.$rs['photo'];
	$scenephoto[] = $rs;
}

//现场图片_查看更多
$query = mysql_query("SELECT * FROM sj_year_scenephoto_more WHERE year = 2",$link);
$rs = mysql_fetch_array($query,MYSQL_ASSOC);
$scenephoto_more = $rs['url'];

//合作媒体
$query = mysql_query("SELECT * FROM sj_year_mediapartners WHERE year = 2 ORDER BY id ASC");
while($rs = mysql_fetch_array($query,MYSQL_ASSOC)) {
	$rs['pic'] = IMGATT_HOST.$rs['pic'];
	$mediapartners[] = $rs;
}
require_once(ROOT.'tpl/index.html');

$output=ob_get_contents();
ob_end_clean();
file_put_contents(ROOT.'index.html',$output);

echo $output;
exit;

function num_format($num, $type)
{
	if ($num === '') return $num;
	$num = intval($num);
	switch ($type) {
		case 1 :
			return $num;
		break;
		
		case 2:
			if ($num <= 100) {
				return '小于100次';
			} elseif ($num < 1000) {
				$n = floor($num / 100); 
				return "大于{$n}00次";
			} elseif ($num < 10000) {
				$n = floor($num / 1000); 
				return "大于{$n}千次";
			} elseif ($num < 100000) {
				$n = floor($num / 10000); 
				return "大于{$n}万次";
			} elseif ($num < 1000000) {
				$n = floor($num / 100000); 
				return "大于{$n}0万次";
			} elseif ($num < 10000000) {
				$n = floor($num / 1000000); 
				return "大于{$n}00万次";
			} else {
				return "大于1000万次";
			}
		break;
		default :
			return $num;
		break;
	}
}

function getImageHost()
{
	global $config;
	$cdn = $config['cdn'];
	$app_name = defined('APP_NAME') ? APP_NAME : 'www';
	$conf = $cdn['img_host'][$app_name];
	if (is_array($conf)) {
		$k = array_rand($conf);
		$host = $conf[$k];
	} else {
		$host = $conf;
	}
	return $host;
}

