<?php
include_once(dirname(realpath(__FILE__)).'/init.php');
$tplObj->out['type'] = 'detail';
isset($_GET['id']) && $softid = $_GET['id'];
isset($_GET['softid']) && $softid = $_GET['softid'];
$claim = $_GET['v'];
//软件详情
if(isset($_GET['pkg']) && !empty($_GET['pkg'])){
	$pkg = trim($_GET['pkg']);
	$intro = gomarket_action('soft.GoGetSoftDetailPackage', array('PACKAGE_NAME' => $pkg, 'VR' => 1));
	$intro['PACKAGENAME'] = $pkg;
}else{
	$intro = gomarket_action('soft.GoGetSoftDetailCategory', array('ID' => $softid,'VR' => 3,'EXTRA_OPTION_FIELD' => array('A.upload_tm','B.min_firmware','A.intro','A.category_name','subname','A.category_id','tags','min_firmware','max_firmware',  'parentid','status', 'hide', 'iconurl_72')));
}
if(empty($intro['SOFT_NAME']) || $intro['status'] ==0 || !in_array($intro['hide'], array(1, 1024))){
	$search_hot_result = gomarket_action('soft.GoGetHotWords');
	$hot = array_slice($search_hot_result['DATA'],0,8);
	foreach($hot as $key => $val){
		$val['rank'] = $key + 1;
		$hot[$key] = $val;
	}
	$tplObj -> out['hot'] = $hot;
	// 客户端　手机版、HD版soft.GoGetSoftDetailCategory
	$anzhi = gomarket_action('soft.GoGetSoftDetailPackage', array('PACKAGE_NAME' => 'cn.goapk.market', 'VR' => 1,'EXTRA_OPTION_FIELD' => array('isoffice')));
	$anzhi['qrimg'] = get_qrimg($anzhi['ID'],'cn.goapk.market',$anzhi['SOFT_PROMULGATE_TIME'],$anzhi['ICON']);
	$anzhi['SOFT_SIZE'] = formatFileSize($anzhi['SOFT_SIZE'],1);
	$tplObj->out['anzhi'] = $anzhi;		// 手机版	
	$HomeFeature = gomarket_action('softv2.GoGetExtentHomeFeatureOld', array('LIST_INDEX_START' => 0, 'LIST_INDEX_SIZE' => 11,'VR' => 13, 'FULL_LIST' => 1,'GET_COUNT' => true,'EXTRA_OPTION_FIELD' => array('intro','isoffice')));

	foreach($HomeFeature['DATA'] as $key => $val){
		$val['qrimg'] = get_qrimg($val[0],$val[7],$val[15],$val[1]);
		$val['size'] = formatFileSize($val[9],1);
		$HomeFeature['DATA'][$key] = $val;
	}
	$tplObj->out['homeFeature'] = $HomeFeature['DATA'];		
	Header("HTTP/1.1 404 Not Found"); 
	$tplObj -> display("404.html");
	exit;
}
//版本支持
$system = get_support_system($intro['min_firmware'],$intro['max_firmware']);
$intro['system'] = $system;
$time_publish = $intro['SOFT_PROMULGATE_TIME'];
$time_publish_arr = explode('-',$time_publish);
$time_need = $time_publish_arr[0].'年'.$time_publish_arr[1].'月'.$time_publish_arr[2].'日';
$intro['SOFT_PROMULGATE_TIME'] = $time_need;
$soft_size = round($intro['SOFT_SIZE']/(1024*1024),1);
$intro['SOFT_SIZE'] = $soft_size;

if ( !empty($intro['iconurl_72'])) {
	$intro['ICON'] = getIconHost(). $intro['iconurl_72'];
}

//用户还下载了
$like = gomarket_action('soft.GoGetSuggest',array("PACKAGE_NAME"=>$intro['PACKAGENAME'],'EXTRA_OPTION_FIELD' => array('total_downloaded','version_code','category_id','version','filesize')));
foreach($like['DATA_LIKE'] as $key => $val){
	$val['6'] = substr($val['6'],1,-1);
	$like['DATA_LIKE'][$key] = $val;
}
//二维码
$date_times=preg_replace("/[^\d]/","",$intro['SOFT_PROMULGATE_TIME']);
$publish_tm = strtotime($date_times);
//$qrimg = '<img src="http://nf.anzhi.com/QRcode.php?url='.urlencode("http://www.anzhi.com/dl_app.php?s=".$softid)."&time=".$publish_tm."&key=".md5("http://www.anzhi.com/dl_app.php?s=".$softid."anzhi.com/QRcode").'" />';
$qrimg = '<img src="http://nf.anzhi.com/QRcode.php?src=www&softid='.$softid."&time=".$publish_tm."&key=".md5("http://www.anzhi.com/dl_app.php?s=".$softid."anzhi.com/QRcode").'" />';
$tplObj -> out['qrimg'] = $qrimg;
//历史版本
$history_vode = gomarket_action('soft.GoGetHistorySoft',array("ID"=>$softid));
//广告图
$top_ad = get_ad_pic(1);
$bottom_ad = get_ad_pic(2);
//热门搜索排行
$page = $_GET['page'];
$tplObj -> out['page'] = $page;
$tplObj -> out['claim'] = 0;
if(isset($_GET['v']) && $claim){
	$tplObj -> out['claim'] = 1;
	$model = new GoModel();
	$option = array(
		'table' => 'sj_soft',
		'where' => array(
			'softid' => $intro['ID'],
		),
		'field' => 'dev_id,package,claim_status',
	);
	$result = $model -> findOne($option);
	$tplObj -> out['claim_info'] = $result;
}
$tplObj -> out['GOAPK_IMG_HOST'] = GOAPK_IMG_HOST;
$tplObj -> out['count'] = $count;
$tplObj -> out['top_ad'] = $top_ad;
$tplObj -> out['bottom_ad'] = $bottom_ad;
$tplObj -> out['history_vode'] = $history_vode['DATA'];
$tplObj -> out['like'] = $like['DATA_LIKE'];
$replace = array("\n","<br/>","<br>","\r\n","\n\r");
$intro['SOFT_DESCRIBE'] = str_replace($replace, '', $intro['SOFT_DESCRIBE']);
$tplObj -> out['intro'] = $intro;
$tplObj -> out['check_html']  =  "/comment/".substr(md5($pkg),0,4)."_".$pkg.".html";
$tplObj -> display("detail.html");
