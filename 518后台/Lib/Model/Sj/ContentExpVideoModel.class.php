<?php
class ContentExpVideoModel extends AdvModel {
    //调整表前缀
     protected $trueTableName = 'sj_soft_content_exp_video';


     public static function new_http_post($file,$extra) {
     	$ext = substr($file['name'],strrpos($file['name'], '.')+1);
     	if($ext == 'jpg' || $ext = 'png'){
     		$mimetype = 'image/'.$ext;
     	}elseif($ext == 'mp4'){
     		$mimetype = 'video/mp4';
     	}else{
     		$mimetype = 'application/octet-stream';
     	}
     	if(class_exists('\CURLFile')){
            $file_arr = array(
            	'upfile'=>curl_file_create($file['tmp_name'],$mimetype,$file['name'])
            );
        }else{
            $file_arr = array('upfile'=>"@{$file['tmp_name']};filename={$file['name']}".($mimetype ? ";type={$mimetype}":""));
        }
        $file_arr['do'] = $extra['do'];

		 $pro_env = C('PRO_ENV');
		 if($pro_env == 1){
			 //线上
			 $host = '192.168.1.18';
			 $host_dam = 'Host: dummy.goapk.com';
		 }else if($pro_env == 2){
			 $host = 'dummy.goapk.com';
			 $host_dam = 'Host: dummy.goapk.com';
		 }else if($pro_env == 3||$pro_env == 4){
			 $host = '192.168.0.99';
			 $host_dam = 'Host: 9.dummy.goapk.com';
		 }

		$res = curl_init();
		curl_setopt($res, CURLOPT_URL, "http://${host}/service_up.php");
		curl_setopt($res, CURLOPT_HTTPHEADER, array($host_dam));
		curl_setopt($res, CURLOPT_POST, true);
		if(class_exists('\CURLFile')){
			curl_setopt($res, CURLOPT_SAFE_UPLOAD, true);
		}else{
			curl_setopt($res, CURLOPT_SAFE_UPLOAD, false);
		}
		curl_setopt($res, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($res, CURLOPT_POSTFIELDS, $file_arr);
		$result = curl_exec($res);
		$info = curl_getinfo($res);
		$errno = curl_errno($res);
		$error = curl_error($res);
		// var_dump($info,$file_arr,$result);die;
		curl_close($res);
		return array('ret'=>json_decode($result,true),'info'=>$info,'errno'=>$errno,'error'=>$error);
	}
}