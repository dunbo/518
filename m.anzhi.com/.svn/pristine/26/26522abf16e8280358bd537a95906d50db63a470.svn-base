<?php
include_once(dirname(realpath(__FILE__)).'/init.php');

$model = new GoModel();

$tplObj->out['type'] = 'content_detail';
$content_id = $_GET['content_id'];
$morelist = isset($_GET['morelist']) && !empty($_GET['morelist']) ? $_GET['morelist'] : 0;
$content_option = array(
	'where' => array(
		'status' => 1,
		'passed' => 2,
		'id' => $content_id,
		'title' => array('exp', "!=''"),
		'template_select' => array('exp', "!=2"),
	),
	'cache_time' => '600',
	'field' => 'id,package,title,visit_count,template_select,az_style_content,create_tm,title2',
	'table' => 'sj_soft_content_explicit'
);
$content_info = $model->findOne($content_option); //页面信息

//软件详情
$pkg = $content_info['package'];
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

$tplObj -> out['intro'] = $intro;
$tplObj -> out['ImageHost'] = getImageHost();

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
	$len = mb_strlen($text, 'utf-8');
	if($len >= 500){
	    $text = mb_substr($text, 0, 500, 'utf-8');
	}else{
	    $text = mb_substr($text, 0, ceil($len*2/3), 'utf-8');
	}
}else if($content_info['template_select'] == 3){
	//富文本样式
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

$content_info['text'] = $text.'...';

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
	'field' => 'id,package,title,show_style,explicit_pic,title2',
	'table' => 'sj_soft_content_explicit'
);
$content_info1 = $model->findAll($content_option1);
foreach($content_info1 as $key => $val){
    $explicit_pic = json_decode($val['explicit_pic'], true);
    if($val['show_style'] == 2){
        $explicit_pic = array_slice($explicit_pic, 1);
    }
    $content_info1[$key]['explicit_pic'] = $explicit_pic;
}
$now_num = count($content_info1);
$total_num = 10; //更多精彩内容条数
$more_num = 20; //查看更多

//除去该软件其他内览
$content_option2 = array(
	'where' => array(
		'status' => 1,
		'passed' => 2,
		'package' => array('exp',"!='".$pkg."'"),
		'title' => array('exp', "!=''"),
	),
	'cache_time' => '600',
	'offset' => $morelist >= 1 ? ($morelist-1)*$more_num + $total_num-$now_num : 0,
	'limit' => $morelist >= 1 ? $more_num : $total_num-$now_num,
	'order' => 'id desc',
	'field' => 'id,package,title,show_style,explicit_pic,title2',
	'table' => 'sj_soft_content_explicit'
);
$content_info2 = $model->findAll($content_option2);
foreach($content_info2 as $key => $val){
    $explicit_pic = json_decode($val['explicit_pic'], true);
    if($val['show_style'] == 2){
        $explicit_pic = array_slice($explicit_pic, 1);
    }
    $content_info2[$key]['explicit_pic'] = $explicit_pic;
}

//插入安智市场id
$model = load_model('softlist');
$anzhilist = $model->getPackageToSoftId("cn.goapk.market");
$anzhiid = $anzhilist[0];
$tplObj -> out['anzhiid'] = $anzhiid;

$tplObj -> out['content_info'] = $content_info;
$tplObj -> out['content_info1'] = $content_info1;
$tplObj -> out['content'] = $content_info2;
$tplObj -> out['app_href'] = '/app_'.substr(md5($pkg), 0, 4).'_'.$pkg.'.html';

if ($morelist >= 1){
    $tplObj->out['morelist'] = $morelist;
    $tplObj->display("content_ajax.html");
} else {
    $tplObj->display("content_detail.html");
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