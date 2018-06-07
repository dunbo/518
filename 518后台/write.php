<?php
//分析比对入最新软件库
//打开抓取软件库
//ini_set('display_errors', 'on');
//error_reporting(E_ERROR);
 
define('DS', DIRECTORY_SEPARATOR);
define('GO_APP_ROOT', dirname(realpath(__FILE__)));
require GO_APP_ROOT . DS . '..'.DS.'tools'.DS.'functions.php';

/* $link = mysqli_connect("192.168.0.99", "root", "southpark", "newgomarket");
if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
}  */
$is_new = false;
ping();
$nowdate = date("j");
$query5 = "select * from cj_software where  FROM_UNIXTIME(soft_adddate,'%e') =".$nowdate;
mysqli_set_charset($link,'utf8');	
$result5 = mysqli_query($link, $query5);
//error_log(date("Y-m-d H:i:s").'今天采集到信息条数为：'.mysqli_num_rows($result5),3,caiji.log);
$str = '今天采集到信息条数为：'.mysqli_num_rows($result5);
mylog($str);
//$is_caiji = false;
if($is_caiji && mysqli_num_rows($result5)==0){
/* 
$to = "1416193815@qq.com";
$subject = "采集出错";
$message = "采集出错！";
$from = "someonelse@example.com";
$headers = "From: $from";
mail($to,$subject,$message,$headers); */
$str='采集可能未完成入库11';
mylog($str);
 
} 


while($row5 = mysqli_fetch_array($result5))
{
 
	$apkinfo = go_apk_info($row5['soft_apk'], $ignore_icon = false); 
	/* var_dump($apkinfo);
    echo "\r\n";
    echo GO_APP_ROOT . DS . $row5['soft_apk'];
    echo "\r\n";   */
	 
 	$result6 = mysqli_query($link,"select * from cj_user_config where cid = '".$row5['soft_cid']."'");
	
	if(mysqli_num_rows($result6) > 0)
    {
	   $row6 = mysqli_fetch_array($result6);
	   if($apkinfo['packagename'] == $row6['apkname'])//提取用户配置表是否用户需要的包名
	   { 
	     
		 $result7 = mysqli_query($link,"select softid,version_code from sj_soft where package = '".$row6['apkname']."' and status=1 and hide=1 order by version_code desc limit 1");
		
		 if(mysqli_num_rows($result7) > 0)
         {   $row7 = mysqli_fetch_array($result7);
			 if($apkinfo['versionCode'] > $row7['version_code'])//去公司软件库查找此软件是否最新；
			  { 
				 $result8 = mysqli_query($link,"select *  from cj_new_sowftware where package = '".$row6['apkname']."' ");
				 if(mysqli_num_rows($result8) > 0) //采集最新软件表是否有此软件';
				 {  
					$row8 = mysqli_fetch_array($result8);
					$result9 = mysqli_query($link,"select * from cj_new_sowftware where package = '".$row6['apkname']."' and version_code < ".$apkinfo['versionCode']." and new_status=1");//是否最新软件
					if(mysqli_num_rows($result9) > 0)
					{
					   mysqli_query($link,"insert into cj_new_sowftware (new_sname,new_sver,package,new_stxt,new_sapk, new_sfromweb,new_sdate,sid)values ('{$row5['soft_name']}','{$apkinfo['versionCode']}','{$apkinfo['packagename']}', '{$row5['soft_txt']}','{$row5['soft_apk']}','{$row5['soft_web']}',".time().",'{$row7['softid']}')");  
					   
					   mysqli_query($link,"update cj_new_sowftware set new_status=0 where new_sid=".$row8['new_sid']."");   
					   mysqli_query($link,"update cj_software set soft_status = 1 where soft_id =".$row5['soft_id']."");   
					   $is_new = true;
					}
					mysqli_free_result($result9); 
				 }
				 else
				 { //最新软件表无此软件
				    mysqli_query($link,"insert into cj_new_sowftware (new_sname,new_sver,package,new_stxt,new_sapk, new_sfromweb,new_sdate,sid)values ('{$row5['soft_name']}','{$apkinfo['versionCode']}','{$apkinfo['packagename']}', '{$row5['soft_txt']}','{$row5['soft_apk']}','{$row5['soft_web']}',".time().",'{$row7['softid']}')");  
				   mysqli_query($link,"update cj_software set soft_status = 1 where soft_id =".$row5['soft_id'].""); 
                   $is_new = true;				   
				 }
				 mysqli_free_result($result8);
				 mysqli_free_result($result7);
			   }
            }
		}   

    }
	mysqli_free_result($result6);  
}
mysqli_free_result($result5);  
echo '比对是否有最新软件完成！' ;
if($is_new){
	echo '-->发现最新软件';

}else{
	echo '-->没有发现最新软件';

}
//mysqli_close($link);

function mylog($str) {
    $logtime = date("Y-m-d H:i:s", time());
    file_put_contents('/tmp/caiji.log', "${logtime}: ${str}\n", FILE_APPEND);
}

?>