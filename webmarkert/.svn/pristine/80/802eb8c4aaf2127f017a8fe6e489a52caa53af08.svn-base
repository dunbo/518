<?php
include_once(dirname(realpath(__FILE__)).'/init.php');
ini_set('display_errors', 'on');
error_reporting(E_ERROR);
$tplObj->out['type'] = 'detail';
isset($_GET['id']) && $softid = $_GET['id'];
isset($_GET['softid']) && $softid = $_GET['softid'];

//软件详情
if(isset($_GET['pkg']) && !empty($_GET['pkg'])){
	$pkg = trim($_GET['pkg']);
    $intro = gomarket_action('soft.GoGetSoftDetailPackage', array('PACKAGE_NAME' => $pkg, 'VR' => 8, 'EXTRA_OPTION_FIELD' => array('A.upload_tm','B.min_firmware','A.intro','A.category_name','subname','A.category_id','A.costs','tags','min_firmware','max_firmware', 'status', 'hide', 'iconurl_72', 'parent_name','A.update_content','parentid','A.update_from')));
	$intro['PACKAGENAME'] = $pkg;
	
}else{
	$intro = gomarket_action('soft.GoGetSoftDetailCategory', array('ID' => $softid,'VR' => 8,'EXTRA_OPTION_FIELD' => array('A.upload_tm','B.min_firmware','A.intro','A.category_name','subname','A.category_id','A.costs','A.update_content','tags','min_firmware','max_firmware', 'status', 'hide', 'iconurl_72', 'parent_name','parentid','A.update_from')));
	if((empty($intro['SOFT_NAME']) || $intro['status'] ==0 || !in_array($intro['hide'], array(1, 1024)))){
		//id有可能是历史软件，需要去获取最新的有效id
        $soft_model = load_model('soft');
		$new_softid = $soft_model->getRealSoftid($softid);
        if (!empty($new_softid)) {
			//最新id不为空，则使用最新id
			$softid = $new_softid;
            $intro = gomarket_action('soft.GoGetSoftDetailCategory', array('ID' => $new_softid,'VR' => 8,'EXTRA_OPTION_FIELD' => array('A.upload_tm','B.min_firmware','A.intro','A.category_name','subname','A.category_id','A.costs','A.update_content','tags','min_firmware','max_firmware', 'status', 'hide', 'iconurl_72', 'parent_name','A.update_from')));
		} else {
            $intro = array();
        }
		
    }
}

if(empty($intro)){
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


$package = $intro['PACKAGENAME'];
$pack_name = get_white_list();
if(isset($pack_name[$package])){
	$intro['white'] = 1;
}

$v_pack_name = get_v_list();
if(isset($v_pack_name[$package])){
	$intro['v_dev'] = 1;
}


// 收费描述
switch ($intro['costs']) {
	case 0:
		$intro['costs'] = '免费';
	break;
	case 1:
		$intro['costs'] = '部分章节收费';
		break;
	case 2:
		$intro['costs'] = '按本计费';
	break;
	default:
		$intro['costs'] = '免费';
	break;
}


//版本支持
$system = get_support_system($intro['min_firmware'],$intro['max_firmware']);
$intro['system'] = $system;
$intro['SOFT_PROMULGATE_TIME'] = date('Y年m月d日', strtotime($intro['SOFT_PROMULGATE_TIME']));
$intro['file_size'] = $intro['SOFT_SIZE'];
$soft_size_push = round($intro['SOFT_SIZE']/(1024*1024),1);
$soft_size = formatFileSize($intro['SOFT_SIZE'],1);
$intro['SOFT_SIZE'] = $soft_size;
$replace = array("\n","<br/>","<br>","\r\n","\n\r");
$intro['SOFT_DESCRIBE'] = str_replace($replace, '', $intro['SOFT_DESCRIBE']);
$tplObj -> out['intro'] = $intro;

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
$qrimg = get_qrimg($softid, $intro['PACKAGENAME'], $intro['SOFT_PROMULGATE_TIME'], $intro['ICON']);
$qrimg = "<img src='{$qrimg}' />";
$tplObj -> out['qrimg'] = $qrimg;

//广告图
$top_ad = get_ad_pic(1);
$bottom_ad = get_ad_pic(2);

$newanzhi = gomarket_action('soft.GoGetSoftDetailPackage', array('PACKAGE_NAME' => 'cn.goapk.market', 'VR' => 1));

$tplObj -> out['newanzhi'] = $newanzhi;
//热门搜索排行
$page = $_GET['page'];
$tplObj -> out['soft_size_push'] = $soft_size_push;
$tplObj -> out['page'] = $page;
$tplObj -> out['GOAPK_IMG_HOST'] = GOAPK_IMG_HOST;
$tplObj -> out['count'] = $count;
$tplObj -> out['top_ad'] = $top_ad;
$tplObj -> out['bottom_ad'] = $bottom_ad;
$tplObj -> out['history_vode'] = $history_vode_show;
$tplObj -> out['like'] = $like['DATA_LIKE'];
$intro['update_content'] = trim($intro['update_content']);
$tplObj -> out['intro'] = $intro;
$tplObj -> out['check_html']  =  "/comment/".substr(md5($pkg),0,4)."_".$pkg.".html";
if($intro['PACKAGENAME']=='cn.goapk.market') {
	$tplObj -> out['packagename'] = $intro['PACKAGENAME'];
	display("detail_anzhi.html");
} elseif($intro['PACKAGENAME'] == 'anzhi.pad'){
	$tplObj -> out['packagename'] = $intro['PACKAGENAME'];
	display("detail_anzhi_hd.html");
}else {
	$tplObj -> out['is_softid_info'] = 1;
	display("detail.html");
}
