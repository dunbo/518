<?php
set_time_limit(0);
ini_set('display_errors', 'on');
error_reporting(E_ERROR);
define('MY_PATH', dirname(realpath(__FILE__)));
include_once MY_PATH.'/../load_gophp.php';
$statics_db = load_config('db/master');
$host = $statics_db['host'];
$user = $statics_db['username'];//'azmk';
$pwd = $statics_db['password'];//'mkaz)#@)2012pwd';
$database = $statics_db['database'];
$link = mysqli_connect($host, $user, $pwd, $database);
if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
}
$query = "SELECT * FROM cj_user_config where status=1";
mysqli_set_charset($link,'utf8');	
$result = mysqli_query($link, $query);
//apk保存路径
$downdir ='/data/att/downapkfile/'.date("Y-m-d");
$downdir_db = 'downapkfile/'.date("Y-m-d");
$is_caiji = false;
while($row = mysqli_fetch_array($result))
{
	$query2 = "SELECT * FROM cj_admin_config where cj_status=1 and id=".$row['scansite_num'];
	$result2 = mysqli_query($link, $query2);
	while($row2 = mysqli_fetch_array($result2))
	{
		if($row2['cj_submittype']=='GET'){
			$weblist = requestGet($row2['cj_url'].$row['searchname'], $timeout = 4);
			//echo $weblist;
			//exit;
			
		}else{
		    $weblist = requestPost($row2['cj_url'], $row['searchname']);
		}
		//获取列表页链接
	    if(empty($weblist )){
			error_log(date("Y-m-d H:i:s", time()).'没有抓取到页面\r\n',3,'caiji.log');
			exit;
		}
	    $num = strrpos($weblist , trim($row2["cj_liebaostr"]));
		$weblist = substr($weblist,$num) ;
		$link_array = match_links($weblist);
		$is_sql=false;
		  if(empty($link_array)){
			error_log(date("Y-m-d H:i:s", time()).'没有获得页面链接数组\r\n',3,'caiji.log');
			exit;
		}
		//第一页抓取 
		 for($i=0; $i<10; $i++)
		 {  
			if(empty($link_array['link'][$i])){
				exit;
			}
		 
		    $cler_link = str_replace('amp;','',$link_array['link'][$i]);
			 
			$query3 = "select soft_url from cj_software where soft_url='$cler_link'" ;
	        $result3 = mysqli_query($link, $query3);
			
			if(mysqli_num_rows($result3) == 0){
				$is_caiji = true;
				$contentinfo = requestGet($cler_link, $timeout = 6); 
				$content_arry = match_context($contentinfo,$row2);
			    //文件名称处理
				$file = date("YmdHis").rand(0,100).'.apk';
				if(httpcopy($content_arry['apkurl'], $downdir, $file,  $timeout=60))
			     {
				 
				   $soft_apk = $downdir_db.'/'.$file;
				   $softxt = mysqli_real_escape_string($link,strip_tags($content_arry['softxt']));
				   $cler_link = mysqli_real_escape_string($link,$cler_link);
				// echo $cler_link.'--'.$file.'<br>';
				   $insertstr.="('{$content_arry['softname']}','{$row['cid']}','$softxt','$soft_apk','{$row2['cj_website']}','$cler_link','".time()."'),";  
				   $is_sql = true;
				   
			    }  

			}
		 
		 } 
		if($is_sql){
			 $insertstr = substr($insertstr,0,strlen($insertstr)-1);  
			 ping();
			 
			 mysqli_query($link,"insert into cj_software (soft_name,soft_cid,soft_txt,soft_apk,soft_web, soft_url,soft_adddate)values ".$insertstr."")or die(mysqli_error($link));   
         }
		echo '第一列表页抓取完毕!!'; 
	
		//剩余4页抓取
		 
	 	for($l=10; $l<14; $l++){
			if(empty($link_array['link'][$l])){
				exit;
			}
			 $insertstr='';
			 $is_sql = false;
			//var_dump($fenyelink_array);
			$weblist2 = requestGet('m.baidu.com'. str_replace('amp;','',$link_array['link'][$l]), $timeout = 4);
			$num2 = strrpos($weblist2 , trim($row2["cj_liebaostr"]));
			$weblist2 = substr($weblist2,$num2) ;
			$link_array2 = match_links($weblist2);
			 
			 for($ii=0; $ii<10; $ii++)
			 { 
				$cler_link = str_replace('amp;','',$link_array2['link'][$ii]);

				$query4 = "select soft_url from cj_software where soft_url='$cler_link'" ;
				$result4 = mysqli_query($link, $query4);
				if(mysqli_num_rows($result4) == 0){
				    $is_caiji = true;
					$contentinfo = requestGet($cler_link, $timeout = 6); 
					$content_arry = match_context($contentinfo,$row2);
					$file = date("YmdHis").rand(0,100).'.apk';
				    if( httpcopy($content_arry['apkurl'], $downdir, $file,  $timeout=60))
					{	 
			
						 $soft_apk = $downdir_db.'/'.$file;
						  //echo $cler_link.'--'.$file.'<br>';
						$softxt = mysqli_real_escape_string(strip_tags($content_arry['softxt']));
						$cler_link = mysqli_real_escape_string($cler_link);
						$insertstr.="('{$content_arry['softname']}','{$row['cid']}','$softxt','$soft_apk','{$row2['cj_website']}','$cler_link','".time()."'),";
						$is_sql = true;
						
					}
 
			   }
		 
		     } 
			if($is_sql){ 
				$insertstr = substr($insertstr,0,strlen($insertstr)-1);  
				ping();
				 
				mysqli_query($link,"insert into cj_software (soft_name,soft_cid,soft_txt,soft_apk,soft_web, soft_url,soft_adddate)values ".$insertstr."") or die(mysqli_error($link));   
				
				//echo $link_array['link'][$l];
			}
		}     
	 echo '第'.$l.'列表页抓取完毕!!'.'<br>';
    }
	
	
}
	

mysqli_free_result($result4); 
mysqli_free_result($result3);
mysqli_free_result($result2);
mysqli_free_result($result);




//分析比对入最新软件库
include('write.php');
mysqli_close($link);
echo '-->采集结束';




function requestPost($url, $str){
	if (empty($str)) return false;
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POST, 1);
	if ($str) curl_setopt($ch, CURLOPT_POSTFIELDS, $str);
	$re = curl_exec($ch);
	$http_info = curl_getinfo($ch);
	curl_close($ch);
	return $re;
}
	//$str="keyword=新浪";
	//$url="http://apk.hiapk.com/search";		
	//print_r( requestPost($url, $str));
	
	
	
 /**
	 * GET 请求封装
	 * $url: 请求地址
	 * $data: GET 参数
	 * $timeout: 超时时间
	 */
function requestGet($url, $timeout = 6){
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	//curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
	curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
	$result = curl_exec($ch);
	$http_info = curl_getinfo($ch);
	curl_close($ch);
	//return array("http_code"=>$http_info['http_code'],"re"=>$result,"url" => $url);
	return $result;
}	
    // $url="http://m.baidu.com/s?st=10a001&tn=webmkt&pre=web_am_index&word=新浪";
       
		
 /**
 *  获取列表页软件url和列表页url正则
 */ 
function match_links($document) {   
	preg_match_all("'<\s*a\s.*?href\s*=\s*([\"\'])?(?(1)(.*?)\\1|([^\s\>]+))[^>]*>?(.*?)</a>'isx",$document,$links);                       
	while(list($key,$val) = each($links[2])) {
		if(!empty($val))
			$match['link'][] = $val;
	}
	while(list($key,$val) = each($links[3])) {
		if(!empty($val))
			$match['link'][] = $val;
	} 
/*	
	while(list($key,$val) = each($links[4])) {
		if(!empty($val))
			$match['content'][] = $val;
	}
	while(list($key,$val) = each($links[0])) {
		if(!empty($val))
			$match['all'][] = $val;
	}   */           
	return $match;
}

  /**
	 *  获取内容页软件信息
	 */ 
function match_context($html, $row){
	$result = array();
	preg_match($row['cj_softname'], $html, $m); 
	$result['softname'] = $m[1];
	preg_match($row['cj_apkurl'], $html, $m); 
	$result['apkurl'] = $m[1];
	preg_match($row['cj_softxt'], $html, $m); 
	$result['softxt'] = $m[1];
	return $result;
}



   /**
	 *  下载apk包
	 */ 

function httpcopy($url, $downdir, $file, $timeout=60) {
   // $file = pathinfo($url,PATHINFO_BASENAME) ;
   // $dir = pathinfo($file,PATHINFO_DIRNAME);
    !is_dir($downdir) && @mkdir($downdir,0777,true);
    $url = str_replace(" ","%20",$url);
    $flag = false;
    if(function_exists('curl_init')) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1); 
		//curl_setopt($ch, CURLOPT_HTTPHEADER, array("Range: Bytes=0-50")); 
        $temp = curl_exec($ch);
		//var_dump(curl_getinfo($ch));
		//var_dump($downdir.'/'.$file);
        if(file_put_contents($downdir.'/'.$file, $temp) && !curl_error($ch)) {
          $flag = true;
        }
    } 
	 
	 if(!$flag){
        $opts = array(
            "http"=>array(
            "method"=>"GET",
            "header"=>"",
            "timeout"=>$timeout)
        );
        $context = stream_context_create($opts);
        if(copy($url, $downdir.'/'.$file, $context)) {
            //$http_response_header
            $flag = true;
        } 
    } 
	var_dump($flag);
	return $flag;
	
}

 function ping(){
    global $link;
    if(!mysqli_ping($link)){
		mysqli_close($link);
	    $link = mysqli_connect("192.168.0.99", "root", "southpark", "newgomarket");
		mysqli_set_charset($link,'utf8');	
	} 
     if($link)
	{
		echo '连接';
	}
} 



?>