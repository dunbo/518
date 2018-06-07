<?php
include_once (dirname ( realpath ( __FILE__ ) ) . '/init.php');
$tplObj -> out['type'] = 'list';
$parentid = (int)$_GET['parentid']?$_GET['parentid'] : 1;
$page = (int)$_GET['page']?$_GET['page'] : 1;
$limit = 15;
$offset = ($page - 1) *$limit; 
//1,hot;0,new
$order = isset($_GET['order']) ? (int)$_GET['order'] : 1;
$order_right = ($order == 1) ? 0 : 1;
$order_name = ($order == 1) ? '热门' : '最新';
$order_name_right = ($order == 1) ? '最新' : '热门';
$type_name = ($parentid == 1) ? '应用' : '游戏';
$type_name_right = ($parentid == 1) ? '游戏' : '应用';
$parentid_right = ($parentid == 1) ? 2 : 1;
$tplObj->out['parentid'] = $parentid;
$tplObj->out['order'] = $order;
$tplObj->out['order_right'] = $order_right;
$tplObj->out['parentid_right'] = $parentid_right;
$tplObj->out['order_name'] = $order_name;
$tplObj->out['order_name_right'] = $order_name_right;
$tplObj->out['type_name'] = $type_name;
$tplObj->out['type_name_right'] = $type_name_right;
$tplObj->out['channel'] = $_GET['channel'];

$parameters = array(
	'LIST_INDEX_START' => $offset,
	'LIST_INDEX_SIZE' => $limit,
	'VR' => 1,
	'GET_COUNT' => True,
	'EXTRA_OPTION_FIELD' => array(
		'intro',
		'parentid',
	),
	'ORDER'=> $order,
	'ID' => $parentid,
);

//各分类的软件
$results = gomarket_action('soft.GoGetCategoryAllSoftList',$parameters);
$count = $results['COUNT'];
foreach($results['DATA'] as $key => $val){
	$val['qrimg'] = get_qrimg($val[0],$val[7],$val[15],$val[1]);
	$results['DATA'][$key] = $val;
}
$tplObj->out['parentid'] = $parentid;
$tplObj->out['page'] =  pagination_arr($page, $count, $limit, 10);
$tplObj->out['applist'] = scorehtml($results['DATA']);

display('list.html');

