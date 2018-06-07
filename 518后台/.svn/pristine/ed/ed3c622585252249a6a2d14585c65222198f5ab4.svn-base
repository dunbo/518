<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);
header("Content-type: text/html; charset=utf-8");
set_time_limit(0);
include_once dirname(dirname(realpath(__FILE__))).'/GoPHP/config/config.inc.php';
$zhiyoo_host = $config['db']['zhiyoo']['host'];
$zhiyoo_port = $config['db']['zhiyoo']['port'];
$zhiyoo_user = $config['db']['zhiyoo']['username'];
$zhiyoo_password = $config['db']['zhiyoo']['password'];
$zhiyoo_db = $config['db']['zhiyoo']['database'];

$bbs_host = $config['db']['slave']['host'];
$bbs_port = $config['db']['slave']['port'];
$bbs_user = $config['db']['slave']['username'];
$bbs_password = $config['db']['slave']['password'];
$bbs_db = $config['db']['slave']['database'];

$bbstabal = 'x15_forum_forum';
$zhiyootabale = "zy_bbs_plate";

// 连接数据库
$bbslink = mysql_connect($bbs_host,$bbs_user,$bbs_password);
$zhiyoolink = mysql_connect($zhiyoo_host,$zhiyoo_user,$zhiyoo_password);

if (!$bbslink or !$zhiyoolink) {
	printf("Connect failed: %s\n", mysql_error());
	exit();
}

mysql_query("SET NAMES utf8", $bbslink);
mysql_query("SET NAMES utf8", $zhiyoolink);
mysql_select_db($bbs_db,$bbslink);
mysql_select_db($zhiyoo_db,$zhiyoolink);
// 数据库字段名
$fids = "fid,fup,type,name,status";
$fidstr = "b_fid, b_fup, b_type, b_name, b_status, createdate";

// 查询专区数据
$bbsSql = "SELECT $fids FROM $bbstabal WHERE fup=0 and type='group' ORDER BY fid asc";
$zhiyooSql = "SELECT $fidstr FROM $zhiyootabale WHERE b_fup=0 and b_type='group' and b_fid<10000 ORDER BY b_fid asc";
$bbsResult = mysql_query($bbsSql,$bbslink);
$zhiyooResult = mysql_query($zhiyooSql,$zhiyoolink);

$bbsRowData =  $bbsRowFid = $zhiyooRowData = $zhiyooRowFid = array();

while($bbsRow = mysql_fetch_array($bbsResult)){
	// 将该专区fid写入数组为对比调用
	$bbsRowFid[] = $bbsRow['fid']; 
	// 将该专区fid的数据写入数组，为方便添加fid数据调用
	$bbsRowData[$bbsRow['fid']]['fid'] = $bbsRow['fid'];
	$bbsRowData[$bbsRow['fid']]['fup'] = $bbsRow['fup'];
	$bbsRowData[$bbsRow['fid']]['type'] = $bbsRow['type'];
	$bbsRowData[$bbsRow['fid']]['name'] = $bbsRow['name'];
	$bbsRowData[$bbsRow['fid']]['status'] = $bbsRow['status'];
}
// 将zhiyoo专区b_fid写入数组为对比调用
while($zhiyooRow = mysql_fetch_array($zhiyooResult)){
	$zhiyooRowFid[] = $zhiyooRow['b_fid'];
}
// 正向对比专区fid（bbs->zhiyoo）为增加数据
$bbsFid = array_diff($bbsRowFid, $zhiyooRowFid);
// 反向对比专区fid（zhiyoo->bbs）为删除数据
$zhiyooFid = array_diff($zhiyooRowFid,$bbsRowFid);

// 增加专区信息
if (count($bbsFid)) {
	$fidval = '';
	foreach ($bbsFid as $bbsvalfid){
		$b_fid = $bbsRowData[$bbsvalfid]['fid'];
		$b_fup = $bbsRowData[$bbsvalfid]['fup'];
		$b_type = $bbsRowData[$bbsvalfid]['type'];
		$b_name = $bbsRowData[$bbsvalfid]['name'];
		$b_status = $bbsRowData[$bbsvalfid]['status'];
		$createdate = $createdate = date('Y-m-d H:i:s');
		// 循环拼接插入专区数据字符串
		$fidval .= "('$b_fid','$b_fup','$b_type','$b_name','$b_status','$createdate'),";
	}
	$fidval = substr($fidval, 0,-1);
	// 增加专区数据
	$zhiyooinsertsql = "INSERT INTO $zhiyootabale ($fidstr) VALUES $fidval ";
	$instr = mysql_query($zhiyooinsertsql,$zhiyoolink);
//	var_dump($instr);
}

// 删除专区 以及专区下的版块
if (count($zhiyooFid)) {
	foreach ($zhiyooFid as $zhiyoovalfid){
		$zhiyoodelsql = "DELETE FROM $zhiyootabale WHERE `b_fid` = $zhiyoovalfid ";
		$delstr = mysql_query($zhiyoodelsql,$zhiyoolink);
		if ($delstr) {
			// 删除该专区下的版块
			$zhiyoodelsql = "DELETE FROM $zhiyootabale WHERE `b_fup` = $zhiyoovalfid ";
			$delstr = mysql_query($zhiyoodelsql,$zhiyoolink);
		}
// 		var_dump($delstr);
	}
}

//////////
// 2015-10-13 19:50 
// 循环对比专区下的版块,是否存在有变更专区的版块
foreach ($bbsRowFid as $bbsfidval){
	// 查询该专区的版块
	$bbsSqlfup = "SELECT $fids FROM $bbstabal WHERE fup='$bbsfidval' ORDER BY fid asc";
	$zhiyooSqlfup = "SELECT $fidstr FROM $zhiyootabale WHERE b_fup='$bbsfidval' ORDER BY b_fid asc";
	$bbsResultfup = mysql_query($bbsSqlfup,$bbslink);
	$zhiyooResultfup = mysql_query($zhiyooSqlfup,$zhiyoolink);
	$bbsRowDatafup =  $bbsRowfup = $zhiyooRowDatafup = $zhiyooRowfup = array();

	while($bbsRows = mysql_fetch_array($bbsResultfup)){
		// 将该版块fid写入数组为对比调用
		$bbsRowfup[] = $bbsRows['fid'];
	}
	// 将zhiyoo版块b_fid写入数组为对比调用
	while($zhiyooRows = mysql_fetch_array($zhiyooResultfup)){
		$zhiyooRowfup[] = $zhiyooRows['b_fid'];
	}

	// 反向对比版块fid（zhiyoo->bbs）为删除更改数据
	$zhiyooFup = array_diff($zhiyooRowfup,$bbsRowfup);
	// 删除该专区缺少的版块
	if (count($zhiyooFup)) {
		foreach ($zhiyooFup as $zhiyoovalfup){
			$bbsSqlfid = "SELECT $fids FROM $bbstabal WHERE fid='$zhiyoovalfup'";
			$bbsResultfid = mysql_query($bbsSqlfid,$bbslink);
			$bbsfidRows = mysql_fetch_array($bbsResultfid);
			if ($bbsfidRows) {
				$bbs_fup = $bbsfidRows['fup'];
				$upzhiyoodelsql = "UPDATE $zhiyootabale SET b_fup=$bbs_fup WHERE `b_fid` = $zhiyoovalfup ";
				$updatestr = mysql_query($upzhiyoodelsql,$zhiyoolink);
// 				var_dump($updatestr);
			}
		}
	}
}
//////////


// 循环对比专区下的版块
foreach ($bbsRowFid as $bbsfidval){
	// 查询该专区的版块
	$bbsSqlfup = "SELECT $fids FROM $bbstabal WHERE fup='$bbsfidval' ORDER BY fid asc";
	$zhiyooSqlfup = "SELECT $fidstr FROM $zhiyootabale WHERE b_fup='$bbsfidval' ORDER BY b_fid asc";
	$bbsResultfup = mysql_query($bbsSqlfup,$bbslink);
	$zhiyooResultfup = mysql_query($zhiyooSqlfup,$zhiyoolink);
	$bbsRowDatafup =  $bbsRowfup = $zhiyooRowDatafup = $zhiyooRowfup = array();
	
	while($bbsRows = mysql_fetch_array($bbsResultfup)){
		// 将该版块fid写入数组为对比调用
		$bbsRowfup[] = $bbsRows['fid'];
		// 将该版块fid的数据写入数组，为方便添加fid数据调用
		$bbsRowDatafup[$bbsRows['fid']]['fid'] = $bbsRows['fid'];
		$bbsRowDatafup[$bbsRows['fid']]['fup'] = $bbsRows['fup'];
		$bbsRowDatafup[$bbsRows['fid']]['type'] = $bbsRows['type'];
		$bbsRowDatafup[$bbsRows['fid']]['name'] = $bbsRows['name'];
		$bbsRowDatafup[$bbsRows['fid']]['status'] = $bbsRows['status'];
	}
	// 将zhiyoo版块b_fid写入数组为对比调用
	while($zhiyooRows = mysql_fetch_array($zhiyooResultfup)){
		$zhiyooRowfup[] = $zhiyooRows['b_fid'];
	}

	// 正向对比版块fid（bbs->zhiyoo）为增加数据(对比fid)
	$bbsFup = array_diff($bbsRowfup, $zhiyooRowfup);
	// 反向对比版块fid（zhiyoo->bbs）为删除数据
	$zhiyooFup = array_diff($zhiyooRowfup,$bbsRowfup);

	// 增加该专区的版块信息
	if (count($bbsFup)) {
		$fidval = '';
		foreach ($bbsFup as $bbsvalfup){
			$b_fid = $bbsRowDatafup[$bbsvalfup]['fid'];
			$b_fup = $bbsRowDatafup[$bbsvalfup]['fup'];
			$b_type = $bbsRowDatafup[$bbsvalfup]['type'];
			$b_name = $bbsRowDatafup[$bbsvalfup]['name'];
			$b_status = $bbsRowDatafup[$bbsvalfup]['status'];
			$createdate = $createdate = date('Y-m-d H:i:s');
			// 循环拼接插入版块数据字符串
			$fidval .= "('$b_fid','$b_fup','$b_type','$b_name','$b_status','$createdate'),";
		}
		$fidval = substr($fidval, 0,-1);
		// 增加版块数据
		$zhiyooinsertsql = "INSERT INTO $zhiyootabale ($fidstr) VALUES $fidval ";
		$instr = mysql_query($zhiyooinsertsql,$zhiyoolink);
//		var_dump($instr);
	}
	// 删除该专区缺少的版块
	if (count($zhiyooFup)) {
		foreach ($zhiyooFup as $zhiyoovalfup){
			$zhiyoodelsql = "DELETE FROM $zhiyootabale WHERE `b_fid` = $zhiyoovalfup ";
			$delstr = mysql_query($zhiyoodelsql,$zhiyoolink);
//			var_dump($delstr);
		}
	}
	
}



