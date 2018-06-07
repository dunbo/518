<?php
//标准头,开始
include(dirname(realpath(__FILE__)).'/init.php');
header('content-type:text/html;charset=utf-8');
$model = new GoModel();
$data = array();
$rs = $model->findOne(array('table' => 'pu_developer','where' => array('dev_id'=>$_GET['id'])));

if((time()-$rs['verify_time'])>86400) {
	$model->update(array('dev_id' => $_GET['id']), array('verify_email'=>'','verify_time'=>0,'__user_table'=>'pu_developer'), 'master');
	jsmsg('修改密码连接已过期,请从新发送修改连接 ','index.php');
	exit();
}
if((strlen($rs['verify_email'])!=32 && $rs['verify_email']!=$_GET['code']))  {
	
	jsmsg('修改密码连接已失效','index.php');
	exit();
}
if($_GET['verify_type'] == 2){
	$rs1 = $model -> findOne(array('table' => 'pu_user','where' => array('userid'=>$_GET['id'])));
	if(!$rs){
		jsmsg('用户名不存在','index.php');
		exit();
	}else{
	    $user = $model->findOne(array('table' => 'pu_user','where' => array('userid'=>$_GET['id'])));

	   	if(!$user){
		    jsmsg('用户名不存在','index.php');
		    exit();
		}
	    $verify_code = md5($rs['dev_id'].$rs['email'].$rs['mobile'].$rs['verify_email'].$rs['verify_mobile'].$rs['verify_time']);
	    $tplObj->assign('reset_pwd_dev',1);
	    $tplObj->assign('user_name',$user['user_name']);
	    $tplObj->assign('str',$verify_code);
	    $tplObj->assign('id',$user['userid']);
	    $tplObj->assign('type',2);
	    $tplObj->display('login.html');	
	}
}else if($_GET['verify_type'] == 1){
	    $verify_code = md5($rs['dev_id'].$rs['email'].$rs['mobile'].$rs['verify_email'].$rs['verify_mobile'].$rs['verify_time']);
	    $_GET['css']='index';
	    $user = $model->findOne(array('table' => 'pu_user','where' => array('userid'=>$_GET['id'])));
	    if(!$user){
	    	jsmsg('用户名不存在','index.php');
	    	exit();
	    }
		$auth_json = $user_logic -> get_auth($rs['dev_id']);
		$auth  = json_decode($auth_json,true);
		$str_auth = $auth['msg'];
		$bbs_user = $user_logic->get_user($rs['dev_id'],$str_auth);
		$bbs_user = json_decode($bbs_user,true);
		if (isset($bbs_user['msg']['username']) && $bbs_user['msg']['username'] != $user['user_name']) {
			$data = array(
				'user_name' => $bbs_user['msg']['username'],
				'__user_table' => 'pu_user'
			);
			$where = array(
				'userid'=>$rs['dev_id']
			);
			$model->update($where, $data);
			$user['user_name'] = $bbs_user['msg']['username'];
		}
		
	    $tplObj->assign('reset_pwd_dev',1);
	    $tplObj->assign('user_name',$user['user_name']);
	    $tplObj->assign('str',$verify_code);
		$tplObj->assign('authstr',$str_auth);
	    $tplObj->assign('id',$rs['dev_id']);
	    $tplObj->assign('type',1);	    
	    $tplObj->display('login.html');
}
