<?php
include_once(dirname(realpath(__FILE__)).'/init.php');
//不在本站做登录操作
header('Location:/');
exit;
    $type = isset($_POST['type'])?$_POST['type']:'';
	if($type != "ajax"){
		$tplObj->out['type'] = 'login';
		
		if ($_GET['act'] == 'logout') {
		    unset($_SESSION['user_data']);
		    if($_COOKIE['autoLogin'] == true) setcookie('autoLogin','',-1);
			
			$cross_domain = preg_replace('/^([a-z0-9_-]+\.)*([a-z0-9_-]+\.[a-z0-9_-]+)$/', '\2', $_SERVER['HTTP_HOST']);
			//同步退出
			header('Location:http://www.anzhi.com/site_logout.php?refer=www');
			//setcookie('__gosession', '', 0, '/', $cross_domain);
		    //header('location: '.get_login_register_referer($_GET['referer']));
		    exit;
		} elseif ($_SERVER['REQUEST_METHOD'] == 'GET' || $_GET['act'] != 'submit') {
		        $_GET['referer'] = $_SERVER['HTTP_REFERER'];
		        if (empty($_GET['referer'])) {
		            $_GET['referer'] = 'http://'. $_SERVER['HTTP_HOST'];
		        }
		        $tplObj->display ( "login.html" );
		        return ;
		}
	}
	$user_name = $_POST['user_name'];
	$user_password = $_POST['user_password'];
	list($error, $user_data) = $user_logic->login_user($user_name, $user_password);
	if ($error) {
		if($type == 'ajax')
		{
			$json = array("status"=>0);
			exit(json_encode($json));
		}
	    $tplObj->out['error'] = '登录名或密码错误！';
	    $_GET['referer'] = $_POST['referer'];
		if ($error == -10) {
			if (preg_match('/@/', $user_name)) {
				$tplObj->out['error'] = '请尝试将“@”换为“#”，进行登录';
			} else {
				$tplObj->out['error'] = '请尝试在用户名后缀增加“_lt”或“_az”，进行登录';
			}
			$tplObj->out['duplicate_name_error'] = 1;
		}
	    $tplObj->display ( "login.html" );
		return;
	} else {
		if($user_data['status'] == 0){
			if($type == "ajax")
			{
			   exit(json_encode(array("status"=>0)));
			}
			$tplObj ->out['deny_user'] = 1;		// 您的账户已经被管理员屏蔽！
			$_GET['referer'] = $_POST['referer'];
			$tplObj->display ( "login.html" );
			return ;
		}
	    $_SESSION['user_data'] = $user_data;
	    $_SESSION['USER_NAME'] = $user_data['user_name'];
	    $_SESSION['USER_ID'] = $user_data['userid'];
	    $salt = "12321312211";
	    if($_POST['autoLogin'] == 2){
	        $uid = $user_data['userid'];
	        $md5_password = $user_data['user_password'];
	        $mycrypt = crypt($uid.$md5_password,$salt);
	        setcookie('__SSSDsdasdaasd123213',$mycrypt,time()+3600*24*7);   //真实校验
	        setcookie('__SSSDDS567454csa',md5($mycrypt.time()),time()+3600*24*7);
	        setcookie('__SSqweqweqw123DS213',md5(time().$mycrypt),time()+3600*24*7);
	        setcookie('SESSID',time().$uid,time()+3600*24*7);
	     }
		$referer = $_POST['referer'];
		$user = array(
			'USER_NAME' => $_SESSION['USER_NAME'],
			'USER_ID' => $_SESSION['USER_ID'],
		);
		include_once('../tools/functions.php');
		$encrypt = rc4_encode($user);
		$cross_domain = preg_replace('/^([a-z0-9_-]+\.)*([a-z0-9_-]+\.[a-z0-9_-]+)$/', '\2', $_SERVER['HTTP_HOST']);
		
		setcookie('__gosession', $encrypt, 0, '/', $cross_domain);
		if ($user_data['code'] == -200) {
			$tplObj ->out['src'] = $user_data['renameurl'];
			$tplObj ->out['url'] = 'http://'. $_SERVER['HTTP_HOST'];
			$tplObj->display ( "modify_user.html" );
			return;
		}
		if($type == 'ajax')
        {
        	$json = json_encode(array("status"=>200));
        	exit($json);
        }
		
		
		header('location: '.get_login_register_referer($_POST['referer']));
	    exit;
	}
