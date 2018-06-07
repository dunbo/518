<?php
include_once (dirname(realpath(__FILE__)).'/init.php');
if ($_SERVER['HTTP_HOST'] == 'promotion.anzhi.com') {
    header('location:http://m.anzhi.com');
    exit;
}
$pkg = trim($_GET['package']);
$intro = gomarket_action('soft.GoGetSoftDetailPackage', array(
	'PACKAGE_NAME' => $pkg,
	'VR' => 3,
	'EXTRA_OPTION_FIELD' => array(
	'A.category_id','A.category_name','A.hide','A.status','A.update_content','parentid'
	),
));
$tplObj->out['info'] = $intro;
$tplObj->out['referer'] = $_SERVER['HTTP_REFERER'];
$morelist = isset($_GET['morelist']) && !empty($_GET['morelist']) ? $_GET['morelist'] : 0;
$softid = $intro['ID'];
$page = 5;
$comment = gomarket_action('comment.GoGetCommentList', array("ID" => $softid, 'GET_COUNT' => True, "LIST_INDEX_START" => $morelist * $page,"LIST_INDEX_SIZE" => $page, 'VR' => 1));
	$i = $k =0;
	$comment['scorehtml']="";
	$i = floor($comment['AVERAGE_SCORE'] / 2);
	$k = $comment['AVERAGE_SCORE'] % 2;
	for($i1=$i;$i1>0;$i1--){
		$comment['scorehtml'] .='<img alt="" src="/images/star_01.png">';
	}
	if($k!=0)
		$comment['scorehtml'] .= '<img alt="" src="/images/star_02.png">';
	if(($i+$k)<5) {
		for($i2=(5-$i-$k);$i2>0;$i2--){
			$comment['scorehtml'] .='<img alt="" src="/images/star_03.png">';
		}   
	}
	foreach($comment['DATA'] as $key => $value) {
		$i = $k =0;
		$comment['DATA'][$key]['scorehtml']="";
		$i = floor($value[1] / 2);
		$k = $value[1] % 2;
		for($i1=$i;$i1>0;$i1--){
			$comment['DATA'][$key]['scorehtml'] .='<img alt="" src="/images/star_01.png">';
		}
		if($k!=0)
			$comment['DATA'][$key]['scorehtml'] .= '<img alt="" src="/images/star_02.png">';
		if(($i+$k)<5) {
			for($i2=(5-$i-$k);$i2>0;$i2--){
				$comment['DATA'][$key]['scorehtml'] .='<img alt="" src="/images/star_03.png">';
			}   
		}
	}
$max = max($comment['STARS']);
$arr[0] = round(($comment['STARS'][0] / $max) * 100) . '%';
$arr[1] = round(($comment['STARS'][1] / $max) * 100) . '%';
$arr[2] = round(($comment['STARS'][2] / $max) * 100) . '%';
$arr[3] = round(($comment['STARS'][3] / $max) * 100) . '%';
$arr[4] = round(($comment['STARS'][4] / $max) * 100) . '%';
$comment['percentage'] = $arr;

//分类最新更新
$sub_cat_id = $intro['category_id'];
$tplObj->out['sub_cat_id'] = $sub_cat_id;
$order = isset($_GET['order']) ? (int)$_GET['order'] : 0;
$tplObj->out['order'] = $order;
$morelist = isset($_GET['morelist']) && !empty($_GET['morelist']) ? $_GET['morelist'] : 0;
$app_num = 5; //显示软件个数
$result = get_softlist('soft.GoGetCategoryAllSoftList', $morelist * PAGE_LIMITE, $app_num, $sub_cat_id, array('ORDER'=> $order, 'EXTRA_OPTION_FIELD' => array('upload_tm','min_firmware', 'parentid','isoffice')));
$result['list'] = scorehtml($result['list']);
$tplObj->out['app_classifyapp'] = $result['list'];
$memcache = GoCache::getCacheAdapter('memcached');
/*$type_id = $memcache->get('TYPE_ID');
$parentid = $type_id[$sub_cat_id]['parentid'];
$tplObj -> out['parent_cat_id'] =  $parentid;*/
//分类最新更新完
$tplObj->out['commentlist'] = scorehtml2($comment['DATA']);
$tplObj->out['type'] = 'comment_detail';
$tplObj->out['parentid'] = $intro['parentid'];
$tplObj->out['soft_name'] = $intro['SOFT_NAME'];
$tplObj->display('comment_detail.html');

function scorehtml($result){
    foreach($result as $key => $value) {
        $i = $k =0;
        $result[$key]['scorehtml']="";
        $i = floor($value[score] / 2);
        $k = $value[score] % 2;
        for($i1=$i;$i1>0;$i1--){
            $result[$key]['scorehtml'] .='<img alt="" src="/images/star_01.png">';
        }
        if($k!=0)
            $result[$key]['scorehtml'] .= '<img alt="" src="/images/star_02.png">';
        if(($i+$k)<5) {
            for($i2=(5-$i-$k);$i2>0;$i2--){
                $result[$key]['scorehtml'] .='<img alt="" src="/images/star_03.png">';
            }   
        }
    }
    return  $result;
}

function scorehtml2($result){
    foreach($result as $key => $value) {
        $i = $k =0;
        $result[$key]['scorehtml']="";
        $i = floor($value[1] / 2);
        $k = $value[1] % 2;
        for($i1=$i;$i1>0;$i1--){
            $result[$key]['scorehtml'] .='<img alt="" src="/images/star_01.png">';
        }
        if($k!=0)
            $result[$key]['scorehtml'] .= '<img alt="" src="/images/star_02.png">';
        if(($i+$k)<5) {
            for($i2=(5-$i-$k);$i2>0;$i2--){
                $result[$key]['scorehtml'] .='<img alt="" src="/images/star_03.png">';
            }   
        }
    }
    return  $result;
}