<?php
/*****上传文件
******
*******
*******
*************************/
class UploadfileModel extends Model {
	//摘自tools/ClsFactory.php中http_post函数
	public static function _http_post($vals) {
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
		curl_setopt($res, CURLOPT_URL, "http://${host}/service_dev.php");
		curl_setopt($res, CURLOPT_HTTPHEADER, array($host_dam));
		curl_setopt($res, CURLOPT_POST, true);
		curl_setopt($res, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($res, CURLOPT_POSTFIELDS, $vals);
		$result = curl_exec($res);
		$info = curl_getinfo($res);
		$errno = curl_errno($res);
		$error = curl_error($res);
		curl_close($res);
		return array('ret'=>json_decode($result,true),'info'=>$info,'errno'=>$errno,'error'=>$error);
	}
	
	//上传图片--获取随机数
	public static function rand_code($num) {
		$str = '';
		for($i=0;$i<$num;$i++) {
			$str .= mt_rand(0,9);
		}
		return $str;
	}
	//图片上传到tmp目录
	public function uploadfile_to_tmp(){
		$tmp_name = $_FILES['img']['tmp_name'];
		$array = array('jpg','jpeg','png','gif');
		$name = $_FILES['img']['name'];
		$info = pathinfo($name);
		$type =  $info['extension'];//获取文件件扩展名
		$error = '';
		if(!in_array($type,$array)){
			exit(json_encode(array('code'=>0,'msg'=> "图片上传格式错误")));
		}		
		if($_FILES['img']['size'] > 1024*100){
			exit(json_encode(array('code'=>0,'msg'=> "请不要上传大于100KB的图片")));
		}
		list($msec,$sec) = explode(' ',microtime());
		$msec = substr($msec,2);
		$file_str = "/tmp/$msec.".$type;
		if(!move_uploaded_file($tmp_name,$file_str)){
			$error .= "上传出错\n";
			exit(json_encode(array('code'=>0,'msg'=>$error)));
		}else{
			exit(json_encode(array('code'=>1,'file_path'=>$msec.'.'.$type)));
		}			
	}
}
?>
