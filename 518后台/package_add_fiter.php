<?php
header('content-type:text/html;charset=utf-8');
$str_md5="abcdefgh";
$md5_key=md5($str_md5);
if($md5_key!=$_POST['md5_key']){
	echo 2;
	exit;
}
$host = "192.168.0.99";
$user = "root";
$pass = "southpark";
$db = "newgomarket";
$conn = mysql_connect($host, $user, $pass);
if (!$conn) {
	echo "error";
	exit;
}
$ret = mysql_select_db($db, $conn);
mysql_query("SET NAMES 'utf8'");
$sql="insert into sj_admin_filter(source_type,source_value,target_type,target_value,filter_type,addtime)values($_POST[source_type],$_POST[source_value],$_POST[target_type],$_POST[target_value],$_POST[filter_type],$_POST[addtime])";

$result=mysql_query($sql,$conn);
$fiter_id=mysql_insert_id();
if($fiter_id){
	echo $fiter_id;
}else{
	echo 0;
}
mysql_close($conn);
