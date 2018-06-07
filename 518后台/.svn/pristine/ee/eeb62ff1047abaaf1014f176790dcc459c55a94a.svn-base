<?php

//如果未登录，拒绝用户访问
session_start();
if(!$_SESSION['admin']['admin_id']){
    echo 'access deny';
    exit();
}

header('Access-Control-Allow-Headers: X-Requested-With,X_Requested_With');


//获取远程图片数据
foreach ($_POST as $key => $value) {
	if(strrpos($key,'oImg_') !== false){
		$data[$key] = $value;
	}
}

$list = array();
//获取当前域名
// $host = 'http://'.$_SERVER['SERVER_NAME'];
// $root = $_SERVER['DOCUMENT_ROOT'];
$root = $CONFIG['serverDir'];
$host = $CONFIG['attachUrl'];

//遍历下载图片
foreach($data as $key=>$value){
	//判断图片链接是否存在
	if(empty($value)){
		$list[$key]['code'] = 404;
		$list[$key]['url'] = $list[$key]['size'] = '';
		$list[$key]['searchurl'] = $value;
		$list[$key]['message'] = urlencode('图片链接地址为空');
		continue;
	}

	//获取远程图片
    /*------------------------------------------------------------------*/	
 	$ch = curl_init();
  	curl_setopt($ch,  CURLOPT_RETURNTRANSFER, 1);
  	curl_setopt($ch, CURLOPT_URL,  $value);
  	curl_setopt($ch, CURLOPT_HEADER, 0);
  	// curl_setopt($ch, CURLOPT_POST, 1);
  	curl_setopt($ch,  CURLOPT_FOLLOWLOCATION, 2); // 302 redirect
 	// curl_setopt($ch,  CURLOPT_POSTFIELDS, 'post');
 	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // https请求 不验证证书和hosts
 	curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,10); //连接超时时间
    curl_setopt($ch,CURLOPT_TIMEOUT,30); //等待超时时间
 	$img = curl_exec($ch);
  	$Headers =  curl_getinfo($ch);
  	curl_close($ch);
  	if(!isset($Headers['content_type']) || !stristr($Headers['content_type'], "image")){
  		$list[$key]['code'] = 404;
		$list[$key]['url'] = $list[$key]['size'] = '';
		$list[$key]['searchurl'] = $value;
		$list[$key]['message'] = urlencode('无法下载该图片1');
		continue;
  	}
    
    $type = $Headers['content_type'];
	$_ext = '.'.strtolower(substr($type,strrpos($type,'/')+1));
    if(!in_array($_ext,$CONFIG['imgLinkAllowFiles'])){	
    	$list[$key]['code'] = 404;
		$list[$key]['url'] = $list[$key]['size'] = '';
		$list[$key]['searchurl'] = $value;
		$list[$key]['message'] = urlencode('图片类型错误');
		continue;
    }
    
     
    /*------------------------------------------------------------*/
    if(!$img){
    	$list[$key]['code'] = 404;
		$list[$key]['url'] = $list[$key]['size'] = '';
		$list[$key]['searchurl'] = $value;
		$list[$key]['message'] = urlencode('无法下载该图片');
    	continue;
    }else{//判断图片大小
    	$filesize = strlen($img);
    	if($filesize > $CONFIG['imgLinkMaxSize']){
			$list[$key]['code'] = 404;
    		$list[$key]['url'] = $list[$key]['size'] = '';
			$list[$key]['searchurl'] = $value;
    		$list[$key]['message'] = urlencode('图片大小超出限制');
    		continue;
    	}
    }
// ========================================================================
    $t = time();
	$d = explode('-', date("Y-y-m-d-H-i-s"));
	$format = $CONFIG["imgLinkPathFormat"];
	$format = str_replace("{yyyy}", $d[0], $format);
	$format = str_replace("{yy}", $d[1], $format);
	$format = str_replace("{mm}", $d[2], $format);
	$format = str_replace("{dd}", $d[3], $format);
	$format = str_replace("{hh}", $d[4], $format);
	$format = str_replace("{ii}", $d[5], $format);
	$format = str_replace("{ss}", $d[6], $format);
	$format = str_replace("{time}", $t, $format);
	$randNum = rand(1, 10000000000) . rand(1, 10000000000);
	if (preg_match("/\{rand\:([\d]*)\}/i", $format, $matches)) {
	    $format = preg_replace("/\{rand\:[\d]*\}/i", substr($randNum, 0, $matches[1]), $format);
	}
// =========================================================================
    //把图片写入本地
	$dir = $root.dirname($format);
	$filename = $root.$format.$_ext;
	//创建目录
	if(!file_exists($dir)){  
        mkdir($dir,0777,true);  
    }

 	// @file_put_contents($filename, $img);
	$write_fd = @fopen($filename,"a");
	@fwrite($write_fd, $img);  //将采集来的远程数据写入本地文件
	@fclose($write_fd);

	//检测是否保存成功
	if(file_exists($filename)){
		list($width, $height, $type, $attr) = getimagesize($filename);
		$maxwidth = 340;
        if($width>$maxwidth){
            $bl = $height/$width;
            $height = floor($maxwidth*$bl);
            $width = $maxwidth;
        }
		$list[$key]['url'] = $host.$format.$_ext;
		$list[$key]['code'] = 200;
		$list[$key]['searchurl'] = urlencode($value);
		$list[$key]['size'] = $filesize;
		$list[$key]['message'] = '';
		$list[$key]['width'] = $width;
		$list[$key]['height'] = $height;
	}else{
		$list[$key]['code'] = 404;
		$list[$key]['url'] = $list[$key]['size'] = '';
		$list[$key]['searchurl'] = $value;
		$list[$key]['message'] = urlencode('无法下载该图片2');
    	continue;
    }

}

return urldecode(json_encode($list));

