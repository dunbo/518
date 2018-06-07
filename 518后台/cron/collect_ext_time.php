<?php
#zy_collect_ext添加时间参数脚本
error_reporting(E_ERROR);
ini_set('display_errors', 'on');
include dirname(realpath(__FILE__)). '/../load_gophp.php';

$model = new GoModel();

//先获取数据再批量更新
$table = 'zy_collect_ext';
$table1 = 'zy_pos_conf';
//分页获取
$pagesize = 10000;
$res = $model -> query("select max(posid) as count from $table1");
$resource = $model -> fetch($res);
$count = $resource['count'];
$page = ceil($count/$pagesize);

//从$table1获取数据
for($i = 1; $i <= $page; $i++){
	$max_posid = $pagesize*$i;
	$min_posid = $pagesize*($i-1);
	$result = $model -> query("select advid,addtime,starttime,endtime from $table1 where status=1 and posid>$min_posid and posid<=$max_posid");
	while ($row = $model -> fetch($result)) {
		//更新到$table中
		$addtime = $row['addtime'];
		$starttime = $row['starttime'];
		$endtime = $row['endtime'];
		$advid = $row['advid'];
		echo "update $table set addtime=$addtime, starttime=$starttime, endtime=$endtime where advid=$advid limit 1"."\n";
	}
}

exit;
