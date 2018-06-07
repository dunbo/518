<?php 
include_once (dirname(realpath(__FILE__)).'/../init.php');
$model = new GoModel();
$type = $_GET['type'];
$show_count = $_GET['count'];

session_begin();

$dev_id = '5361479';
$package = "com.sogou.novel";
$option = array(
	'where' => array(
		'status' => 1,
		'dev_id' => $dev_id,
		'category_id' => array(
			',73,',
			',74,',
			',75,',
			',76,',
			',77,',
			',79,',
		),
	),
	'order' => 'upload_tm desc',
	'table' => 'sj_soft',
);
$list_all = $model->findAll($option);
$softids = array();
foreach($list_all as $key =>$val)
{
	$softids[]= $val['softid'];
}
$soft_model = load_model('softlist');
$list = $soft_model->getsoftinfos($softids, getFilterOption());

$show_soft = array();
foreach ($softids as $k =>$softid) 
{
	$row = $list[$softid];
	$show_soft[$k]['softid'] = $row['softid'];
	$show_soft[$k]['iconurl_r'] = getImageHost() . $row['iconurl'];
	$show_soft[$k]['softname'] = $row['softname'];
	$show_soft[$k]['filesize'] = $row['filesize'];
	$show_soft[$k]['package'] = $row['package'];
	$show_soft[$k]['category_name'] = $row['category_name'];
	$show_soft[$k]['intro'] = $row['intro'];
	//分解包
	$package_arr = explode(".",$row['package']);
	$pack_count = count($package_arr);
	$suffix2 = $package_arr[$pack_count-2];
	$suffix1 = $package_arr[$pack_count-1];
	$show_soft[$k]['apk_url'] = "sogounovel://k.sogou.com/novel/detail?nn=".$row['softname']."&md=".$suffix1."&id=".$suffix2;
	$need_data =array(
		'soft_id' =>$row['softid'],
		'soft_package' =>$row['package'],
		'soft_name' =>$row['softname'],
		'filesize' =>$row['filesize'],
		'version_code' =>$row['version_code'],
	);
	$show_soft[$k]['need_data'] = json_encode($need_data);
}
$all_count = count($show_soft);
$show_info_count = 20; //每页显示的条数
$tplObj->out['all_count'] = $all_count;
$tplObj->out['show_info_count'] = $show_info_count;

//加载更多
if($show_count)
{
	$start = $show_count;
	$more_soft = array_slice($show_soft,$start,$show_info_count,true);
	if($more_soft)
	{
		$tplObj->out['sougou_list'] = $more_soft;
		$tplObj->display("sogou_list.html");
	}
	exit;
}
if($all_count>$show_info_count)
{
	$some_list = array_slice($show_soft, 0, $show_info_count,true);
	$tplObj->out['sougou_list'] = $some_list;
}
else
{
	$tplObj->out['sougou_list'] = $show_soft;
}
//打开页面记日志
$active_id="sougou";
$log_data = array(
	'imei' => $_SESSION['USER_IMEI'],
    'device_id' => $_SESSION['DEVICEID'],
    'ip' => $_SERVER['REMOTE_ADDR'],
    'sid' => $_GET['sid'],
	'activity_id' => $active_id,
    'time' => time(),
    'key' => 'sogou_index'
);
permanentlog('activity_'.$active_id.'.log', json_encode($log_data));

$tplObj -> out['sid'] = $_GET['sid'];
$tplObj->out['package'] = $package;
$tplObj -> out['static_url'] = $configs['static_url'];
$tplObj->display("sogou_index.html");
?>