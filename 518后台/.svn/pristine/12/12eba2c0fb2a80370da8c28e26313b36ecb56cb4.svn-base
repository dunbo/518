<?php
/***********
****邮件发送********
*****对开发者发消息提醒********
*************
********/
class SendemailModel extends Model {
	//发送邮件
	function _http_post_email($vals) {
		//$url = 'http://192.168.1.143/service.php';
		$url = 'http://124.243.198.92/service.php';
		$host = 'Host: mail.goapk.com';
		$url .= '?key=f3778b2d59c276233de4f73b2ebf46ea';
		$res = curl_init();
		curl_setopt($res, CURLOPT_URL, $url);
		curl_setopt($res, CURLOPT_TIMEOUT, 5);
		curl_setopt($res, CURLOPT_HTTPHEADER, array($host));
		curl_setopt($res, CURLOPT_POST, true);
		curl_setopt($res, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($res, CURLOPT_POSTFIELDS, $vals);
		$result = curl_exec($res);
		$http_code = curl_getinfo($res,CURLINFO_HTTP_CODE);
		curl_close($res);
		return array(
			'ret' => $result,
			'http_code' => $http_code,
		);
	}
	function realsend($email, $name, $subject, $message,$cc_email='') {
		//调用发邮件接口
		if(strpos($_SERVER['SERVER_ADDR'],'192.168.0')!==FALSE || $_SERVER['SERVER_ADDR']=='127.0.0.1' || $_SERVER['SERVER_ADDR']=='10.0.3.15' || $_SERVER['SERVER_ADDR']=='114.247.222.131') {
			//测试
			$is_test = true;
			if ($is_test) {
				$email_array = array(
					'1216063767@qq.com',
					'467947645@qq.com',
					'qingfeng130227@qq.com',
					'158796378@qq.com',
					'yuanming@anzhi.com',
					'anzhi_test_1@163.com',
                    'wuqiaojun@anzhi.com',
					'527159802@qq.com',
					'knight385@126.com',
					'463356425@qq.com',
					'keleryan@163.com',
					'yinj09@qq.com',
					'1472212069@qq.com',
					'jiayin0105@126.com',
					'anzhi_test_25@163.com',
					'anzhitest135@163.com',
					'anzhitest137@163.com',
					'anzhitest139@163.com',
					'anzhitest150@163.com',
					'by141205@163.com',
					'zhuangchaobin@anzhi.com',
					'linhongqing@anzhi.com',
				    'anzhitest323@126.com',
					'anzhitest200@163.com',
				);
				// 从配置中读数据出来，可以发邮件
                $email_config_find = $this->table('pu_config')->where(array('config_type'=> 'EXTENDV1_EMAIL_SEND', 'status'=> 1))->find();
                if ($email_config_find && $email_config_find['configcontent']) 
				{
                    $mail_arr = explode(';', $email_config_find['configcontent']);
                    $email_array = array_merge($email_array, $mail_arr);
                }
				
                $email_arr = explode(';', $email);
                foreach ($email_arr as $email_address) {
                    if (!$email_address)
                        continue;
                    if(!in_array($email_address,$email_array)){
                        return false;
                    }
                }
				//$data['interior_send'] = 1;
			}

		}	
		$data = array(
			'email'=>$email,
			'name'=>$name,
			'subject'=> $subject,
			'content'=>$message
		);
		if($cc_email){
			$data['cc_email'] = $cc_email;
		}	
		$tmp = $this-> _http_post_email($data);
		if($tmp['http_code']!=200) {
			return array(
				'error' => 5,
				'msg' => '和邮件服务器通讯失败！',
			);
		} else {
			$ret = json_decode($tmp['ret'],true);
			if($ret['code']<0) {//进入发送队列失败
				return array(
					'error' => $ret['code'],
					'msg' => $ret['msg'],
				);
			}
			$tm  = date("Y-m-d",time());
			$file = "/data/www/wwwroot/newadmin.goapk.com/data_log/{$tm}_softauit_sendEmail.log";
			$content = "name:{$name} email:{$email} mag:{$message} {tm} ";
			file_put_contents($file ,$content,FILE_APPEND);
		}
		return true;
	}
	//消息提醒入库
	function dev_remind_add($dev_id,$content,$remind_id = ''){
		$model = new Model();
		if($dev_id && $content){
			$data['dev_id'] = $dev_id;
			$data['content'] = $content;
			$data['create_tm'] = time();			
			$data['read_status'] = 0;			
			$data['status'] = 1;			
			$data['remind_id'] = $remind_id;			
			$remindres = $model->table('dev_remind')->data($data)->add();
			if($remindres){
				return true;
			}else{
				$this->permanentlog("dev_remind.log","提醒消息ID为{$dev_id}内容为{$content}插入失败");
				return false;
			}
		}elseif(empty($content)){
			//echo "<script>alert('内容不能为空');</script>";
			return 2;
		}
	}
}

?>	