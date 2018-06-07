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
$sql="insert into sj_channel(chl,chname,checkname,checkpassword,oid,alone_update,only_auth,submit_tm,last_refresh,status,soft_update,category_id,is_filter,activation_type,purpose,channel_ad,platform,pid,switch)values('$_POST[chl]','$_POST[chname]','$_POST[checkname]','$_POST[checkpassword]','$_POST[oid]','$_POST[alone_update]',$_POST[only_auth],$_POST[submit_tm],$_POST[last_refresh],$_POST[status],$_POST[soft_update],$_POST[category_id],$_POST[is_filter],$_POST[activation_type],$_POST[purpose],$_POST[channel_ad],$_POST[platform],$_POST[platform],$_POST[switch])";
$result=mysql_query($sql,$conn);
$channel_id=mysql_insert_id();
if($channel_id){
	$chl_id_go = substr($_POST['chl'],0,8);
	$chl_id = $chl_id_go.$channel_id;
	$upsql="update sj_channel set chl_cid='{$chl_id}' where cid={$channel_id} and status=1";
	//echo $upsql;exit;
	$updateresult=mysql_query($upsql,$conn);
}
if($channel_id && $updateresult){
	echo $channel_id;
}else{
	echo  0;
}
mysql_close($conn);
