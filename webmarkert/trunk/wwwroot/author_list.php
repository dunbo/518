<?php
include_once(dirname(realpath(__FILE__)).'/init.php');

//热门搜索排行
$search_hot_result = gomarket_action('soft.GoGetHotWords');
$hot = array_slice($search_hot_result['DATA'],0,9);
foreach($hot as $key => $val){
	$val['rank'] = $key + 1;
	$hot[$key] = $val;
}

$tplObj -> out['hot'] = $hot;
$tplObj -> out['type'] = 'author';

//相同作者软件
$auth = $_GET['auth'];
$page = (int)$_GET['page'];
$start = ($page <= 1)? 0 : ($page - 1) * 15;
$author_list = gomarket_action('soft.GoSearchSoftExact',array("PACKAGE_NAME"=>$_GET['package'], "LIST_INDEX_SIZE"=>15,"SEARCH_QUERY"=>$auth,"LIST_INDEX_START"=>$start,"QUERY_CONDITION"=>"3","ADAPTER"=>0,'GET_COUNT' => true,'EXTRA_OPTION_FIELD' => array('A.intro')));
if($author_list){
	foreach($author_list['DATA'] as $key => $val){
		$val['download'] = num_format($val[11],2);
		$val['qrimg'] = get_qrimg($val[0],$val[7],$val[15],$val[1]);
		$author_list['DATA'][$key] = $val;
	}
	$num = 15;
	$area = 10;
	$tplObj->out['page'] =  pagination_arr($page, $author_list['COUNT'], $num, $area);
	$tplObj -> out['author_list'] = $author_list['DATA'];
	$tplObj -> out['author'] = $_GET['auth'];
	$tplObj -> display('author_list.html');
}else{
	$tplObj -> out['author'] = $_GET['auth'];
	$tplObj -> display('author_no.html');
}
