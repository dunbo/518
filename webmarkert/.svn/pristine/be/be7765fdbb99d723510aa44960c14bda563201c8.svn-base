<?php
include_once(dirname(realpath(__FILE__)).'/init.php');

$model = new GoModel();

$sub_len = 500;
$tplObj->out['type'] = 'content_detail';
$content_id = $_GET['content_id'];
$content_option = array(
	'where' => array(
		'status' => 1,
		'passed' => 2,
		'title' => array('exp', "!=''"),
		'template_select' => array('exp', "!=2"),
		'id' => $content_id,
	),
	'cache_time' => '600',
	'field' => 'id,package,title,visit_count,template_select,az_style_content,create_tm,title2',
	'table' => 'sj_soft_content_explicit'
);
$content_info = $model->findOne($content_option);

//软件详情
$pkg = $content_info['package'];
$intro = gomarket_action('soft.GoGetSoftDetailPackage', array('PACKAGE_NAME' => $pkg, 'VR' => 8,'EXTRA_OPTION_FIELD' => array('A.upload_tm','B.min_firmware','A.intro','A.category_name','subname','A.category_id','A.costs','tags','min_firmware','max_firmware', 'status', 'hide', 'iconurl_72', 'parentid', 'parent_name','A.update_content','A.update_from')));
$intro['PACKAGENAME'] = $pkg;

if(empty($intro['SOFT_NAME']) || $intro['status'] ==0 ){
	//客户端、手机版、HD版soft.GoGetSoftDetailCategory
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

$soft_size_push = round($intro['SOFT_SIZE']/(1024*1024),1);
$soft_size = formatFileSize($intro['SOFT_SIZE'],1);
$intro['SOFT_SIZE'] = $soft_size;
$softid = $intro['ID'];
if (!empty($intro['iconurl_72'])) {
	$intro['ICON'] = getIconHost(). $intro['iconurl_72'];
}

//二维码
$qrimg = get_qrimg($softid, $intro['PACKAGENAME'], $intro['SOFT_PROMULGATE_TIME'], $intro['ICON']);
$qrimg = "<img src='{$qrimg}' />";
$tplObj -> out['qrimg'] = $qrimg;
//历史版本
$history_vode = gomarket_action('soft.GoGetHistorySoft',array("ID"=>$softid,'GET_COUNT' => true));
$history_vodes = array_slice($history_vode['DATA'],0,6);
$history_vodes_count = $history_vode['COUNT'];

$newanzhi = gomarket_action('soft.GoGetSoftDetailPackage', array('PACKAGE_NAME' => 'cn.goapk.market', 'VR' => 1));

$intro['update_content'] = trim($intro['update_content']);
$replace = array("\n","<br/>","<br>","\r");
$intro['SOFT_DESCRIBE'] = str_replace($replace, '', $intro['SOFT_DESCRIBE']);

$pack_name = get_white_list();
if(isset($pack_name[$pkg])){
	$intro['white'] = 1;
}

$v_pack_name = get_v_list();
if(isset($v_pack_name[$pkg])){
	$intro['v_dev'] = 1;
}
$tplObj -> out['intro'] = $intro;
$tplObj -> out['newanzhi'] = $newanzhi;
$tplObj -> out['soft_size_push'] = $soft_size_push;
$tplObj -> out['GOAPK_IMG_HOST'] = GOAPK_IMG_HOST;
$tplObj -> out['history_vode'] = $history_vodes;
$tplObj -> out['history_vodes_count'] = $history_vodes_count;

$content_info['create_date'] = date('Y-m-d', $content_info['create_tm']);
$content_info['title'] = $content_info['title2']?$content_info['title2']:$content_info['title']; //配置标题

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
	$len = mb_strlen($text, 'utf-8');
	if($len >= $sub_len){
	    $text = mb_substr($text, 0, $sub_len, 'utf-8');
	}else{
	    $text = mb_substr($text, 0, ceil($len*2/3), 'utf-8');
	}
}else if($content_info['template_select'] == 3){
	//富文本样式 上线180529
    //$text = strip_tags($content_info['az_style_content']);
	$text = strip_tags($content_info['az_style_content'], '<p> <img> <video> <span>');
	$text = str_replace("&nbsp;", '', $text);
	$len = mb_strlen($text, 'utf-8');
	if($len >= 1000){
	    $text = mb_substr($text, 0, 1000, 'utf-8');
	}
	$text = mb_substr($text, 0, ceil($len*0.7), 'utf-8');
	$old_text = $text;
	$p_r_end = mb_strrpos($text, '</p>', 'utf-8');
	if(mb_substr($text, -4, $len, 'utf-8') !='</p>'){
	    $text = mb_substr($text, 0, $p_r_end+4, 'utf-8');
	}

	if (mb_strlen($text, 'utf-8') < 200){
	    $p_r_start = mb_strrpos($old_text, '<span', 'utf-8');
	    $p_r_end = mb_strrpos($old_text, '</span>', 'utf-8');
	    //var_dump($p_r_start ." /" . $p_r_end );exit;
	    if($p_r_end < $p_r_start){
	        $text = mb_substr($old_text, 0, $p_r_end + 7, 'utf-8')."</p>";
	    }
	}	
}

/*$p_r_start = mb_strrpos($text, '<p', 'utf-8');
$p_r_end = mb_strrpos($text, '</', 'utf-8');
if($p_r_end < $p_r_start){
	$text = mb_substr($text, 0, $p_r_start, 'utf-8');
}
$img_r_start = mb_strrpos($text, '<', 'utf-8');
$img_r_end = mb_strrpos($text, '>', 'utf-8');
if($img_r_end < $img_r_start){
	$text = mb_substr($text, 0, $img_r_start, 'utf-8');
}*/

$content_info['text'] = $text.'...';

/* 需求去掉
//插入安智市场id
$model = load_model('softlist');
$anzhilist = $model->getPackageToSoftId("cn.goapk.market");
$anzhiid = $anzhilist[0];
$tplObj -> out['anzhiid'] = $anzhiid;*/


//该软件其他内览
$content_option1 = array(
	'where' => array(
		'status' => 1,
		'passed' => 2,
		'package' 	 => $pkg,
		'id' => array('exp','!='.$content_id),
		'title' => array('exp', "!=''"),
	),
	'cache_time' => '600',
	'limit' => 2,
	'order' => 'id desc',
	'field' => 'id,package,title,title2',
	'table' => 'sj_soft_content_explicit'
);
$content_info1 = $model->findAll($content_option1);
$now_count = count($content_info1);
$total_num = 20; //更多精彩内容总条数

//除去该软件其他内览
$content_option2 = array(
	'where' => array(
		'status' => 1,
		'passed' => 2,
		'package' => array('exp',"!='".$pkg."'"),
		'title' => array('exp', "!=''"),
	),
	'cache_time' => '600',
	'limit' => $total_num-$now_count,
	'order' => 'id desc',
	'field' => 'id,package,title,title2',
	'table' => 'sj_soft_content_explicit'
);
$content_info2 = $model->findAll($content_option2);
$tplObj -> out['content_info'] = $content_info;
$tplObj -> out['content_info1'] = $content_info1;
$tplObj -> out['content_info2'] = $content_info2;
/*echo '<pre>';
print_r($content_info);
print_r($content_info1);
print_r($content_info2);
exit('</pre>');*/
$tplObj -> display("content_detail.html");
