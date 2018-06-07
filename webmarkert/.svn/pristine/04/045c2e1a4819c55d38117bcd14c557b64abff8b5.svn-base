<?php
include_once(dirname(realpath(__FILE__)).'/init.php');
//软件评论
$pkg = trim($_GET['pkg']);
$intro = gomarket_action('soft.GoGetSoftDetailPackage', array('PACKAGE_NAME' => $pkg, 'VR' => 8, 'EXTRA_OPTION_FIELD' => array('A.category_name','A.category_id','parentid')));
$intro['PACKAGENAME'] = $pkg;
$softid = $intro['ID'];
$page = (int)$_GET['page'];
$start = ($page <= 1)? 0 : ($page - 1) * 10;
$comment = gomarket_action('comment.GoGetCommentList',array("ID" => $softid,"LIST_INDEX_START" => $start,"LIST_INDEX_SIZE" => 10,'GET_COUNT' => true));
$count = $comment['COUNT'];
$num = 10;
$area = 10;
$newanzhi = gomarket_action('soft.GoGetSoftDetailPackage', array('PACKAGE_NAME' => 'cn.goapk.market', 'VR' => 1));
$tplObj -> out['newanzhi'] = $newanzhi;
$tplObj->out['page'] =  pagination_arr($page, $count, $num, $area);
$tplObj -> out['comment']=$comment['DATA'];
$tplObj -> out['softid'] = $softid;
$tplObj -> out['packagename'] = $pkg;
$tplObj -> out['intro'] = $intro;
$tplObj -> out['category_id'] = $intro['category_id'];
$tplObj -> out['category_name'] = $intro['category_name'];
$tplObj -> out['soft_name'] = $intro['SOFT_NAME'];
$tplObj -> out['parentid'] = $intro['parentid'];
$tplObj -> out['count'] = $count;
$tplObj -> out['check_html']  =  "/post/".substr(md5($pkg),0,4)."_".$pkg.".html";
if(isset( $pkg) && $pkg=='cn.goapk.market') {
	$tplObj -> display("comment_anzhi.html");
} else {
	if($_GET['type1'] == 'post'){
		$tplObj -> out['type'] = 'comment_detail';
		$tplObj -> display('comment_detail.html');
	}else{	
		$tplObj -> display("comment.html");
	}
}
