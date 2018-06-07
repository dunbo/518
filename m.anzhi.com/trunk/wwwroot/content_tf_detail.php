<?php
include_once(dirname(realpath(__FILE__)).'/init.php');
$model = new GoModel();

$tplObj->out['new_static_url'] = $configs['new_static_url'];
$tplObj->out['type'] = 'content_tf_detail';
$content_id = $_GET['content_id'];
$tplObj->out['content_id'] = $_GET['content_id'];
$morelist   = isset($_GET['morelist']) && !empty($_GET['morelist']) ? $_GET['morelist'] : 0;
$content_option = array(
	'where' => array(
		'status' => 1,
		'passed' => 2,
		'id' => $content_id,
		'title' => array('exp', "!=''"),
		'template_select' => array('exp', "!=2"),
	),
	'cache_time' => '600',
	'field' => 'id,package,title,visit_count,template_select,az_style_content,create_tm,show_style,explicit_pic',
	'table' => 'sj_soft_content_explicit'
);
$content_info = $model->findOne($content_option);
//软件详情
$pkg   = $content_info['package'];
$title = $content_info['title'];
$intro = gomarket_action('soft.GoGetSoftDetailPackage', array('PACKAGE_NAME' => $pkg, 'VR' => 8,'EXTRA_OPTION_FIELD' => array('A.upload_tm','B.min_firmware','A.intro','A.category_name','subname','A.category_id','A.costs','tags','min_firmware','max_firmware', 'status', 'hide', 'iconurl_72', 'parentid', 'parent_name','A.update_content','A.update_from')));
$intro['PACKAGENAME'] = $pkg;
if((empty($intro['SOFT_NAME']) || $intro['status'] ==0) && $morelist==0 ){		
	Header("HTTP/1.1 404 Not Found"); 
    $tplObj->display("search_none.html");
    exit;
}

$soft_size = formatFileSize('',$intro['SOFT_SIZE']);
$intro['SOFT_SIZE'] = $soft_size;
if (!empty($intro['iconurl_72'])) {
	$intro['ICON'] = getIconHost(). $intro['iconurl_72'];
}
$intro['score'] = scorehtml($intro['SOFT_STAR']);
$replace = array("\n","<br/>","<br>","\r");
$intro['SOFT_DESCRIBE'] = str_replace($replace, '', $intro['SOFT_DESCRIBE']);
//print_r($intro);die;
$tplObj -> out['title'] = $title;
$tplObj -> out['intro'] = $intro;
$tplObj -> out['ImageHost'] = getImageHost();
$ImageHost = getImageHost();

//获取软件对应的内览页（除去当前内览页前3条）
$content_option_top = array(
	'where' => array(
		'status'	=>	1,
		'passed'	=>	2,
		'package'	=>	$pkg,
		'id'		=>	array('exp', "!={$content_id}"),
		'title'		=>	array('exp', "!=''"),
		'template_select' => array('exp', "!=2"),
	),	
	'cache_time'	=> '600',
	'order' 		=> 'id desc',
	'offset'		=>	0,
	'limit'			=>	3,
	'field' 		=> 'id,package,title,visit_count,template_select,az_style_content,create_tm',
	'table' 		=> 'sj_soft_content_explicit'
);
$content_list_top = $model->findAll($content_option_top);

foreach ($content_list_top as $key => $val) {
	$len_top = mb_strlen($val['title'], 'utf-8');
	if($len_top > 18) {
		$content_list_top[$key]['is_zhehang'] = 1;
	}else {
		$content_list_top[$key]['is_zhehang'] = 0;
	}
}

// $model = load_model('softlist');
// $anzhilist = $model->getPackageToSoftId("cn.goapk.market");
// $tplObj -> out['anzhiid'] = $anzhilist[0];

$content_info['create_date'] = date('Y-m-d', $content_info['create_tm']);
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
	$regex = '/poster=\"([^\"]*?)\"/i';
    preg_match_all($regex,$content_info['az_style_content'],$viedo_img_arr);
	$text = strip_tags($content_info['az_style_content']);
}

$text = trim($text);
$len = mb_strlen($text, 'utf-8');

$pictrue_arr = json_decode($content_info['explicit_pic'], true);
if($content_info['show_style'] == 2){
        $pictrue_arr = array_slice($pictrue_arr, 1);
}
$content_str = $content_str_hide = '';

$button_hide_str  = '<div class="all_bg">'; 
$button_hide_str .= '<a href="javascript:;" class="all_more"></a>';
$button_hide_str .= '</div>';
$button_hide_str .= "<span class='app_info_hide' style='display:none;'>";
$span_end_str     = "</span>";

if($content_info['template_select'] == 3 && !empty($viedo_img_arr[1])) {
	$img_src  = $viedo_img_arr[1][0];
	$img_str .= '<div class="app_similar2_img">';
	$img_str .= '<a class="az_down_btn1" rel="'.$intro['ID'].','."'".$intro['PACKAGENAME']."'".'" href="javascript:;">';
	$img_str .=	'<img src="'.$img_src.'" alt="" style="margin: 0.1rem auto;max-width: 100%;">';
	$img_str .=	'<span class="video_icon"></span>';
	$img_str .= '</a>';
	$img_str .= '</div>';
	if($len > 300) {
		$before_sub_str  = mb_substr($text,0,300,'utf-8');
		$after_sub_str   = mb_substr($text,300,$len,'utf-8');
		$content_str = $img_str.$before_sub_str.$button_hide_str.$after_sub_str.$span_end_str ;
	}else{
		$content_str = $img_str.$text;
	}
}elseif($content_info['show_style'] == 1) {
 	//单图
	$img_str = $more_str = $before_sub_str = '';
	foreach ($pictrue_arr as $key => $value) {
		$img_src = $ImageHost.$value;
		$img_str .= '<ul class="des_img1">';
		$img_str .= "<li><img src='{$img_src}'></li>";
		$img_str .= '<ul class="des_img1">';
		$img_str .= '<div class="clear"></div>';
		$img_str .= '</ul>';
	}
	if($len > 200) {
		$before_sub_str = mb_substr($text,0,200,'utf-8');
		$after_sub_str  = mb_substr($text,200,$len,'utf-8');
		$content_str = $img_str.$before_sub_str.$button_hide_str.$after_sub_str.$span_end_str ;
	}else{
		$content_str = $img_str.$text;
	}
}elseif($content_info['show_style'] == 2){
	$img_str_1 = $img_str_2 = $img_str_3 = $more_str = '';
	$img_src_1 .= $ImageHost.$pictrue_arr['pic1'];
	$img_str_1 .= '<ul class="des_img1">';
	$img_str_1 .= "<li><img src='{$img_src_1}'></li>";
	$img_str_1 .= '<div class="clear"></div>';
	$img_str_1 .= '</ul>';
	if($len > 100) {
		$before_sub_str_1 = mb_substr($text,0,100,'utf-8');
		$after_sub_str_1  = mb_substr($text,100,$len,'utf-8');
		$len_1 = mb_strlen($after_sub_str_1, 'utf-8');
		if($len_1 > 100) {
			$img_src_2 .= $ImageHost.$pictrue_arr['pic2'];
			$img_str_2 .= '<ul class="des_img1">';
			$img_str_2 .= "<li><img src='{$img_src_2}'></li>";
			$img_str_2 .= '<div class="clear"></div>';
			$img_str_2 .= '</ul>';
			$before_sub_str_2 = mb_substr($text,100,100,'utf-8');
			$after_sub_str_2  = mb_substr($text,200,$len,'utf-8');
			$after_sub_str_3  = mb_substr($text,200,$len,'utf-8');

			$img_src_3 .= $ImageHost.$pictrue_arr['pic3'];
			$img_str_3 .= '<ul class="des_img1">';
			$img_str_3 .= "<li><img src='{$img_src_3}'></li>";
			$img_str_3 .= '<div class="clear"></div>';
			$img_str_3 .= '</ul>';
			$more_str  = '<div class="all_bg">';
			$more_str .= '<a href="javascript:;" class="all_more"></a>';
			$more_str .= '</div>';
			$content_str =	$img_str_1.$before_sub_str_1.$img_str_2.$before_sub_str_2.$button_hide_str.$img_str_3.$after_sub_str_3.$span_end_str;			
		}else {
			//不足全展示
			$img_src_2 .= $ImageHost.$pictrue_arr['pic2'];
			$img_src_3 .= $ImageHost.$pictrue_arr['pic3'];
			$img_str_2 .= '<ul class="des_img2">';
			$img_str_2 .= "<li><img src='{$img_src_2}'></li>";
			$img_str_2 .= "<li class='r'><img src='{$img_src_3}'></li>";
			$img_str_2 .= '<div class="clear"></div>';
			$img_str_2 .= '</ul>';
			$content_str 		=	$img_str_1.$before_sub_str_1.$img_str_2.$after_sub_str_1;
		}
	}else {
		$img_src_2 .= $ImageHost.$pictrue_arr['pic2'];
		$img_src_3 .= $ImageHost.$pictrue_arr['pic3'];
		$img_str_2 .= '<ul class="des_img2">';
		$img_str_2 .= "<li><img src='{$img_src_2}'></li>";
		$img_str_2 .= "<li class='r'><img src='{$img_src_3}'></li>";
		$img_str_2 .= '<div class="clear"></div>';
		$img_str_2 .= '</ul>';
		$content_str 		=	$img_str_1.$text.$img_str_2;
	}

}

$tplObj -> out['content_str'] 		=	$content_str;

//该软件其他内览
$content_option1 = array(
	'where' => array(
		'status' => 1,
		'passed' => 2,
		'package' 	 => $pkg,
		'id' => array('exp','!='.$content_id),
		'title' => array('exp', "!=''"),
		'template_select' => array('exp', "!=2"),
	),
	'cache_time' => '600',
	'limit' => 2,
	'order' => 'id desc',
	'field' => 'id,package,title,show_style,explicit_pic,template_select,az_style_content',
	'table' => 'sj_soft_content_explicit'
);
$content_info_1 = $model->findAll($content_option1);
foreach($content_info_1 as $key => $val){
    $explicit_pic = json_decode($val['explicit_pic'], true);
    if($val['show_style'] == 2){
        $explicit_pic = array_slice($explicit_pic, 1);
    }
	if($val['template_select'] == 3){
		//富文本样式
		$regex = '/poster=\"([^\"]*?)\"/i';
	    preg_match_all($regex,$val['az_style_content'],$viedo_img_arr);
		if( !empty($viedo_img_arr[1]) ) {
			$content_info_1[$key]['VIDEO_ICON'] = $img_src  = $viedo_img_arr[1][0]; 
			$content_info_1[$key]['IS_VIDEO']	= 1;
		}	
	}else {
		$content_info_1[$key]['IS_VIDEO']	= 0;
	}
    $content_info_1[$key]['explicit_pic'] = $explicit_pic;
    $pkg_info = gomarket_action('soft.GoGetSoftDetailPackage', array('PACKAGE_NAME' => $val['package'], 'VR' => 8,'EXTRA_OPTION_FIELD' => array('A.upload_tm','B.min_firmware','A.intro','A.category_name','subname','A.category_id','A.costs','tags','min_firmware','max_firmware', 'status', 'hide', 'iconurl_72', 'parentid', 'parent_name','A.update_content','A.update_from')));
	$content_info_1[$key]['SOFT_DOWNLOAD_REGION'] = $pkg_info['SOFT_DOWNLOAD_REGION'];
	$soft_size = formatFileSize('',$pkg_info['SOFT_SIZE']);
	$content_info_1[$key]['SOFT_ID']		=	$pkg_info['ID'];
	$content_info_1[$key]['SOFT_NAME']		=	$pkg_info['SOFT_NAME'];
	$content_info_1[$key]['SOFT_SIZE']		=	$soft_size;
	$content_info_1[$key]['DOWNLOAD_URL']	=	$pkg_info['DOWNLOAD_URL'];
	if (!empty($package_info['iconurl_72'])) {
		$content_info_1[$key]['ICON'] = getIconHost(). $pkg_info['iconurl_72'];
	}else {
		$content_info_1[$key]['ICON'] = $pkg_info['ICON'];
	}
}

$now_num = count($content_info_1);
$total_num = 10; //更多精彩内容条数
$more_num = 20; //查看更多
//除去该软件其他内览
$content_option2 = array(
	'where' => array(
		'status' => 1,
		'passed' => 2,
		'package' => array('exp',"!='".$pkg."'"),
		'title' => array('exp', "!=''"),
		'template_select' => array('exp', "!=2"),
	),
	'cache_time' => '600',
	'offset' => $morelist >= 1 ? ($morelist-1)*$more_num + $total_num-$now_num : 0,
	'limit' => $morelist >= 1 ? $more_num : $total_num-$now_num,
	'order' => 'id desc',
	'field' => 'id,package,title,show_style,explicit_pic,template_select,az_style_content',
	'table' => 'sj_soft_content_explicit'
);
$content_info_2 = $model->findAll($content_option2);
foreach($content_info_2 as $key => $val){
    $explicit_pic = json_decode($val['explicit_pic'], true);
    if($val['show_style'] == 2){
        $explicit_pic = array_slice($explicit_pic, 1);
    }
    if($val['template_select'] == 3){
		//富文本样式
		$regex = '/poster=\"([^\"]*?)\"/i';
	    preg_match_all($regex,$val['az_style_content'],$viedo_img_arr);
		if( !empty($viedo_img_arr[1]) ) {
			$content_info_2[$key]['VIDEO_ICON'] = $img_src  = $viedo_img_arr[1][0]; 
			$content_info_2[$key]['IS_VIDEO']	= 1;
		}	
	}else {
		$content_info_2[$key]['IS_VIDEO']	= 0;
	}
    $content_info_2[$key]['explicit_pic'] = $explicit_pic;
    $pkg_info = gomarket_action('soft.GoGetSoftDetailPackage', array('PACKAGE_NAME' => $val['package'], 'VR' => 8,'EXTRA_OPTION_FIELD' => array('A.upload_tm','B.min_firmware','A.intro','A.category_name','subname','A.category_id','A.costs','tags','min_firmware','max_firmware', 'status', 'hide', 'iconurl_72', 'parentid', 'parent_name','A.update_content','A.update_from')));
	$content_info_2[$key]['SOFT_DOWNLOAD_REGION'] = $pkg_info['SOFT_DOWNLOAD_REGION'];
	$soft_size = formatFileSize('',$pkg_info['SOFT_SIZE']);
	$content_info_2[$key]['SOFT_ID']		=	$pkg_info['ID'];
	$content_info_2[$key]['SOFT_NAME']		=	$pkg_info['SOFT_NAME'];
	$content_info_2[$key]['SOFT_SIZE']		=	$soft_size;
	$content_info_2[$key]['DOWNLOAD_URL']	=	$pkg_info['DOWNLOAD_URL'];
	if (!empty($package_info['iconurl_72'])) {
		$content_info_2[$key]['ICON'] = getIconHost(). $pkg_info['iconurl_72'];
	}else {
		$content_info_2[$key]['ICON'] = $pkg_info['ICON'];
	}
}
$tplObj -> out['content_list_top']	= $content_list_top;
$tplObj -> out['top_count']			= !empty($content_list_top)?count($content_list_top):0;
$tplObj -> out['content_info']		= $content_info;
$tplObj -> out['content_info_1']	= $content_info_1;
$tplObj -> out['content'] 			= $content_info_2;
$tplObj -> out['app_href'] 			= '/app_'.substr(md5($pkg), 0, 4).'_'.$pkg.'.html';

if ($morelist >= 1){
    $tplObj->out['morelist'] = $morelist;
    $tplObj->display("content_tf_ajax.html");
} else {
    $tplObj->display("content_tf_detail.html");
}

function scorehtml($score){
    $i = $k =0;
    $scorehtml = "";
    $i = floor($score / 2);
    $k = $score % 2;
    for($i1=$i;$i1>0;$i1--){
        $scorehtml .='<img alt="" src="/images/star_01.png">';
    }
    if($k!=0)
        $scorehtml .= '<img alt="" src="/images/star_02.png">';
    if(($i+$k)<5) {
        for($i2=(5-$i-$k);$i2>0;$i2--){
            $scorehtml .='<img alt="" src="/images/star_03.png">';
        }   
    }
    return  $scorehtml;
}
