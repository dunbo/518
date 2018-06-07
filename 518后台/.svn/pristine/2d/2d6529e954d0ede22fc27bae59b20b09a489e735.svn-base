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
$sql="select * from sj_channel where chname='{$_POST[chname]}' and status=1";
$result=mysql_query($sql,$conn);
$row=mysql_num_rows($result);
if($row){
	echo 1;
}else{
	echo 0;
}
mysql_close($conn);
