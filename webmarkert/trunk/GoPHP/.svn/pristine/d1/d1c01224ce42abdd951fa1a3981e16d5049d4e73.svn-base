<?php
/*
    用户logic
*/
define('LOGIN_NO_USER', -1);
define('LOGIN_PASSWORD_ERROR', -2);
define('LOGIN_FAIL',-8);

define('REG_USER_ERROR', -1);
define('REG_PASSWORD_ERROR', -2);
define('REG_EMAIL_ERROR', -3);
define('REG_EMAIL_REPEAT', -4);
define('REG_USER_REPEAT', -5);
define('REG_VER_CODE_ERROR', -6);
define('REG_SYSTEM_ERROR', -7);
define('REG_USERNAME_ALL_NUMBER', -9);

class GoUserLogic
{
    function register_user($data)
    {
        $error = 0;
        go_require_once(GO_APP_ROOT.'/checkcode/config.php');
        if(!(isset($data['checkcode_out']) && $data['checkcode_out'] == 'checkcode')){
            if (!formcheck($data['checkcode'])) {
                $error = REG_VER_CODE_ERROR;
            }
        }
        if (!$error) {
            $user_name = $data['user_name'];
            $n = mb_strlen($user_name);
            if ($n < 3 || $n > 15) {
                $error = REG_USER_ERROR;
            } elseif (preg_match('/^\d+$/',$user_name)){
                $error = REG_USERNAME_ALL_NUMBER;
            } else {
                $o_user_obj = pu_load_model_obj('pu_user');
                $user_obj = $o_user_obj->get_model_by_user_name($user_name);
                if ($user_obj) { $error = REG_USER_REPEAT; }
            }
        }
        if (!$error) {
            $email = $data['email'];
            $user_password = $data['user_password'];
            $n = mb_strlen($user_password);
//            if ( !preg_match('#^[a-zA-Z0-9_-]+([a-zA-Z0-9_-]|\.)+@([a-zA-Z0-9_-])+((\.[a-zA-Z0-9_-]{2,3}){1,2})$#si', $email) ) {
            if ( !preg_match('/^([A-Za-z0-9\-_.+]+)@([A-Za-z0-9\-]+[.][A-Za-z0-9\-.]+)$/', $email) ) {
                $error = REG_EMAIL_ERROR;
            } elseif ( $n < 6 || $n > 16) {
                $error = REG_PASSWORD_ERROR;
            }
        }
        if (!$error) {
            $user_obj = pu_load_model_new_obj('pu_user');
			$data_param = array('user_name' => $user_name, 'user_password' => $user_password, 'email' => $email,'clientip' => $_SERVER['REMOTE_ADDR']);
			if(isset($data['refer_id'])) $data_param['refer_id'] = $data['refer_id'];//登录源id
            list($userid,$message) = $user_obj->save_data_info_to_ucapi($data_param);
            if ($userid > 0) {
                $user_obj->userid = $user_obj->index = $userid;
                $user_obj->data_info['userid'] = $userid;
                $user_obj->data_info['user_name'] = $user_name;
                $user_obj->data_info['user_password'] = $user_password;
				$user_obj->data_info['last_ip'] = ip2long($_SERVER['REMOTE_ADDR']);
				$user_obj->data_info['status'] = 1;
				$user_obj->data_info['email'] = $email;
				$user_obj->data_info['last_time'] = time();
                $user_obj->is_new = True;
                if ( !$user_obj->save_data_info() ) {
                    $error = REG_SYSTEM_ERROR;
                }
            } else {
                $error = REG_SYSTEM_ERROR;
				file_put_contents('/tmp/bbstest.log', 'id:'.$userid.' message:'.$message.' date:'.date('Y-m-d H:i:s',time())."\n",FILE_APPEND);
            }
        }
        return array($error, $user_obj->data_info);
    }

    function login_user($user_name, $user_password)
    {
        return $this->login_user_sec($user_name, $user_password);

        $o_user_obj = pu_load_model_obj('pu_user');
        $user_obj = $o_user_obj->get_model_by_user_name($user_name);
        $error =  0;
        if ( !$user_obj ) {
            $user_obj =   $o_user_obj->get_model_by_ucapi(array('user_name' => $user_name, 'user_password' => $user_password));
            if ($user_obj == LOGIN_PASSWORD_ERROR || $user_obj == LOGIN_NO_USER) {
                $error = $user_obj;
				return array($error,0);
            } else {
                $user_obj->is_new = True;
                $user_obj->save_data_info();
            }
        } elseif ($user_obj->verify_password($user_password) === False) {
            $user_obj =   $o_user_obj->get_model_by_ucapi(array('user_name' => $user_name, 'user_password' => $user_password));
            if ($user_obj == LOGIN_PASSWORD_ERROR || $user_obj == LOGIN_NO_USER) {
                $error = $user_obj;
            } else if(is_object($user_obj)){
                $user_obj->save_data_info();
            }else{
				$error = LOGIN_FAIL;
				return array($error,0);
			}
        }
        return array($error, $user_obj->data_info);
    }
	
	function check_user($username) {
	    $o_user_obj = pu_load_model_obj('pu_user');
        $user_obj = $o_user_obj->get_username_by_ucapi($username);
		return $user_obj;
	}

	//修改密码
	function change_pwd($username,$new_password,$uid,$auth) {
	    $o_user_obj = pu_load_model_obj('pu_user');
        $user_obj = $o_user_obj->change_pwd_by_ucapi($username,$new_password,$uid,$auth);
		return $user_obj;
	}

	//获取通讯验证信息
	function get_auth($uid) {
	    $o_user_obj = pu_load_model_obj('pu_user');
        $user_obj = $o_user_obj->get_auth_by_ucapi($uid);
		return $user_obj;
	}

	//根据uid获取用户账号
	function get_user($uid,$auth) {
	    $o_user_obj = pu_load_model_obj('pu_user');
        $user_obj = $o_user_obj->get_user_by_ucapi($uid,$auth);
		return $user_obj;
	}
	//根据用户名获取用户信息
	function get_user_info($username) {
	    $o_user_obj = pu_load_model_obj('pu_user');
        $user_obj = $o_user_obj->get_userinfo_by_ucapi($username); 
		return $user_obj;
	}
	//修改bbs邮箱
	function change_user_email($uid,$username,$auth,$params) {
	    $o_user_obj = pu_load_model_obj('pu_user');
        $user_obj = $o_user_obj->change_userinfo_by_ucapi($uid,$username,$auth,$params);
		return $user_obj;
	}
	function login_user_sec($user_name, $user_password)
    {
        $error =  0;
        $o_user_obj = pu_load_model_obj('pu_user');
        $user_obj =   $o_user_obj->get_model_by_ucapi(array('user_name' => $user_name, 'user_password' => $user_password));
        if ($user_obj == LOGIN_PASSWORD_ERROR || $user_obj == LOGIN_NO_USER || $user_obj == -10 || $user_obj == -11 ) {
            $error = $user_obj;
            return array($error,0);
        } elseif (empty($user_obj)) {
            $user_obj = $o_user_obj->get_model_by_user_name($user_name);
            if ($user_obj->verify_password($user_password) === False) {
                return array($error,0);
            }
        
        } else {
            if (is_object($user_obj)) {
                $password = $user_obj->data_info['user_password'];
            } elseif (is_array($user_obj)) {
                $password = $user_obj['user_password'];
            }
            /*
            //$local_user_obj = $o_user_obj->get_model_by_user_name($user_name);
            if ( !$local_user_obj ) {
                $user_obj->is_new = True;
            } else {
                //$user_obj = $local_user_obj;
            }
            $user_obj->data_info['user_password'] = $password;
            //$user_obj->save_data_info();
            */
        }
        return array($error, $user_obj->data_info);
    }
    
	function get_user_info_dev($username){
	    $bbs_user = $this->get_user_info($username);
	    if($bbs_user){
	        $bbs_user = json_decode($bbs_user,true);
	        $res['id'] = $bbs_user[0]; 
	        $res['bbs_email'] = $bbs_user[2];	        
	        $model = new GoModel();
	        $option = array(
	                'where' => array(
	                        'dev_id' => $bbs_user[0],
	                ),
	                'table' => 'pu_developer',
	                'field' => 'dev_id ,dev_name,email,email_verified,mobile,mobile_verified',
	        );
	        $dev_user = $model->findOne($option);
	        if($dev_user){
	            $res['dev_email'] = $dev_user['email'];
	            $res['dev_email_verified'] = $dev_user['email_verified'];
	            $res['dev_mobile'] = $dev_user['mobile'];
	            $res['dev_mobile_verified'] = $dev_user['mobile_verified'];
	        }
	        return $res;
	    }else{
	        return false;
	    }	    
	}
	function dev_num_limit($sign,$num,$space=300) {
	    
	    $obj = new GoModel();
	    $time = time();
	    $str = "{$_SESSION['dev']['dev_id']}_".date('Ymd')."_{$sign}";
	    $arr = $obj->findOne(array('table'=>'dev_num_limit','where'=>array('str'=>$str),'field'=>'num,update_tm'));
	    if(!$arr) {
	        $obj->insert(array('str'=>$str,'num'=>1,'create_tm'=>$time,'update_tm'=>$time,'uid'=>$_SESSION['dev']['dev_id'],'sign'=>$sign,'__user_table'=>'dev_num_limit'));
	    } else {
	        if($arr['num']>=$num) {
	            return 2;	//达到最大次数限制
	        } else {
	            //间隔限制:active_email重新发送,chg_email修改邮箱,5分钟
	            //active_mobile发送验证码,3分钟
	            if($space) {
	                if(($time-$arr['update_tm'])<= $space) {
	                    return 3;			//在间隔限制内
	                }
	            }	
	            $obj->query("UPDATE dev_num_limit SET num=num+1,update_tm='{$time}' WHERE str='{$str}'");
	        }
	    }
	    return 1;
	}
	function send_code_mobile($dev_id){
	
	    $obj = new GoModel();
	    $time2 = $_SESSION['reset_pwd']['verify_time'];
	    if((time()-$time2)<600) {
	        $rt['error'] = 1;
	        $rt['msg'] = '验证码发送时间间隔不足10分钟';
	    } else {
	        $option = array(
	                'table' => 'pu_developer',
	                'where' => array(
	                        'dev_id' => $dev_id,
	                ),
	        );
	        $rs = $obj->findOne($option);
	        if(!$rs) {
	            $rt['error'] = 2;
	            $rt['msg'] = '账号信息错误';
	        } else {
	            $_SESSION['reset_pwd'] = $rs;	
	            if($rs['mobile_verified']!=1) {
	                $rt['error'] = 3;
	                $rt['msg'] = '该手机号码未经过验证！';
	            }else{
	                //检测当前手机发验证码次数
	                $res = $this->dev_num_limit('active_mobile'.$rs['mobile'],5,600);
	                if($res==2) {
	                    $rt['error'] = 4;
	                    $rt['msg'] = "重发验证码次数已达上限，建议您更换手机号码尝试";
	                } else if($res==3) {
	                    $rt['error'] = 5;
	                    $rt['msg'] = "重发验证码每次间隔需10分钟";
	                }
	                 
	                //发送手机验证码
	                $rt = $this->send_active_mobile_2($rs['mobile']);
	            }
	        }
	    }
	    return $rt;   
	}
	//生成随机码
	function rand_code($num) {
	    $str = '';
	    for($i=0;$i<$num;$i++) {
	        $str .= mt_rand(0,9);
	    }
	    return $str;
	}
	function send_active_mobile_2($mobile=FALSE) {	//登录后基本资料中修改手机号/未登录前密码重置
	    $model = new GoModel();
	
	    $rand = $this->rand_code(6);
	
	    $time = time();
	    $email_time = date('Y-m-d H:i:s',$time);
	    //短信内容必须带【安智】粗括号的签名，并且放到最后面，要不移动号码会收不到短信
	    $email_cont =<<<EOT
您的验证码是{$rand}。{$email_time}
EOT;
	
	    $uid = $_SESSION['dev']['dev_id'] ? $_SESSION['dev']['dev_id'] : $_SESSION['reset_pwd']['dev_id'];
	    if(!is_numeric($uid)) {
	        $rt['error'] = 1;
	        $rt['msg'] = '用户信息获取失败，请重试！';
	
	        return $rt;
	    }
	    //发送短信,开始
	    if($_GET['do']=='dev_info' && $_GET['type']=='verify_mobile') {
	        $_SESSION['dev_info']['verify_mobile']['new_mobile'] = $_POST['new_mobile'];
	    }
	    $mobile = $mobile ? $mobile : $_SESSION['dev']['mobile'];
	    if(!preg_match('/[0-9]{11}/',$mobile)) {
	        return array(
	                'error' => '2',
	                'msg' => '请提供要发送的手机号',
	        );
	    }
	    if(isset($_SESSION['send_active_mobile'][$mobile]) && $_SESSION['send_active_mobile'][$mobile]>=5) {
	        return array(
	                'error' => '7',
	                'msg' => '重发短信次数已达上限，建议您更换手机号码尝试',
	        );
	    }
	    $log_file = strtoupper(substr(PHP_OS,0,3))=='WIN' ? 'e:/mobile.log' : LOG.date('Y-m-d').'/mobile.log';
	    if(!is_dir(dirname($log_file))) mkdir(dirname($log_file),0777,true);
	    file_put_contents($log_file,"send|{$mobile}|".date('Y-m-d H:i:s')."\n",FILE_APPEND);
	
	    $tmp = $this->http_post_mobile_2(array('phone'=>$mobile,'content'=>$email_cont));
	    if($tmp['http_code']!=200) {
	        return array(
	                'error' => 3,
	                'msg' => '短信通讯失败，请重试！',
	        );
	
	    }
	    $tmp = json_decode($tmp['ret'],TRUE);
	    if(isset($tmp['code']) && is_numeric($tmp['code'])) {
	        if($tmp['code']!=0) {
	            return array(
	                    'error' => $tmp['code'],
	                    'msg' => $tmp['msg'],
	            );
	        }
	    } else {
	        return array(
	                'error' => 4,
	                'msg' => '短信发送无返回结果！',
	        );
	    }
	    //发送短信,结束
	
	    if($model->update(array('dev_id' => $uid), array('__user_table'=>'pu_developer','verify_email'=>'','verify_mobile'=>$rand,'verify_time'=>$time), 'master')) {
	
	        if(isset($_SESSION['reset_pwd'])) {		//重置密码
	            $_SESSION['reset_pwd']['verify_time'] = $time;
	        }
	        $_SESSION['active_mobile'] = $mobile;
	
	        //短信计数
	        $_SESSION['send_active_mobile'][$mobile] += 1;
	
	        $rt['error'] = 0;
	        $rt['msg'] = '验证码已成功发送到您的手机，请查收！';
	        $rt['verify_val'] = $this->mobile_replace($mobile,3,8);
	        $rt['counter'] = $_SESSION['send_active_mobile'][$mobile];
	    } else {
	        $rt['error'] = 1;
	        $rt['msg'] = '数据更新失败，请重试！';
	    }
	
	    return $rt;
	}
	function mobile_replace($val,$start,$end) {
	    if(!$val) return 'mobile_replace_error';
	    $val = (string)$val;
	    $len = strlen($val);
	    $str = '';
	    for($i=0;$i<$len;$i++) {
	        if($i>=$start && $i<$end) {
	            $str .= '*';
	        } else {
	            $str .= $val[$i];
	        }
	    }
	
	    return $str;
	}
	
	function http_post_mobile_2($vals) {
	    if(preg_match('/^192\.168\.0/i',$_SERVER['SERVER_ADDR']) || $_SERVER['SERVER_ADDR']=='10.0.3.15'|| $_SERVER['SERVER_ADDR']=='114.247.222.131') {
	        //$url = 'http://192.168.0.99/service.php?do=sendSms';
	        //$host = 'Host: 9.sms.anzhi.com';
	        //$url = 'http://192.168.0.74:91/service.php?do=sendSms';
	        //$host = 'Host: localhost';
	
	        $url = 'http://118.26.224.18/service.php?do=sendSms';
	        $host = 'Host: smsapi.goapk.com';
	    } else {
	        $url = 'http://192.168.1.18/service.php?do=sendSms';
	        $host = 'Host: smsapi.goapk.com';
	    }
	
	    $url .= '&key=87f337977106a8b12ca1ccb11b3c2637&rand='.microtime(true);
	
	    $res = curl_init();
	    curl_setopt($res, CURLOPT_URL, $url);
	    curl_setopt($res, CURLOPT_TIMEOUT, 15);
	    curl_setopt($res, CURLOPT_HTTPHEADER, array($host,'Expect:'));
	    curl_setopt($res, CURLOPT_POST, true);
	    curl_setopt($res, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($res, CURLOPT_POSTFIELDS, $vals);
	    $result = curl_exec($res);
	    $http_code = curl_getinfo($res,CURLINFO_HTTP_CODE);
	    $errno = curl_errno($res);
	    $error = curl_error($res);
	    curl_close($res);
	
	    $log_file = strtoupper(substr(PHP_OS,0,3))=='WIN' ? 'e:/mobile.log' : LOG.date('Y-m-d').'/mobile.log';
	    if(!is_dir(dirname($log_file))) mkdir(dirname($log_file),0777,true);
	    file_put_contents($log_file,"post|{$url}|{$result}|{$host}|{$http_code}|{$errno}|{$error}|".date('Y-m-d H:i:s')."\n",FILE_APPEND);
	
	    return array(
	            'ret' => $result,
	            'http_code' => $http_code,
	    );
	}
	
	//手机验证码检查
	function check_mobile_code($dev_id,$mobile_code){
	    $obj = new GoModel();
	    $rt = array();
	    $option = array(
	            'table' => 'pu_developer',
	            'where' => array(
	                    'dev_id' => $dev_id,
	            ),
	    );
	    $rs = $obj->findOne($option);
	    if(!$rs){
	        $rt['error'] = 1;
	        $rt['msg']	 = '用户不存在';
	    }else{
	        if($rs['verify_mobile'] == $mobile_code){
	            $rt['error'] = 0;
	        }else{
	            $rt['error'] = 1;
	            $rt['msg']	 = '验证码不正确';
	        }
	    }
	    return $rt;
	}
	
	//邮箱检测
	function check_mail_dev($email){
	    $obj = new GoModel();	     
	    if($obj->findOne(array('table' => 'pu_developer','where' => array('email'=>mysql_escape_string($email),'email_verified'=>1,'status'=>array('exp','!= 2'))))) {
	        $rt['code']=1;
	        $rt['msg']='邮箱已存在';
	    } else {
	        $rt['code']=0;
	    }
	    return $rt;
	}

}
