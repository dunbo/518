<?php
//找回密码
if($_POST['mark'] ==3 ){
	include_once(dirname(realpath(__FILE__)).'/init.php');
	$username = trim($_POST['username']);
	$verify_arr = $user_logic->get_user_info_dev($username);
	$mark_cnt = 0;
	$mark_cnt  += $verify_arr['bbs_email'] ? 1 : 0;
	if($verify_arr['dev_email_verified']==0 && $verify_arr['dev_email']){
		$mark_cnt  +=  2;
	}else if ($verify_arr['dev_email_verified']==1 && $verify_arr['dev_email']){
		$mark_cnt = 8;
	}
	$mark_cnt  += ($verify_arr['dev_mobile_verified'] && $verify_arr['dev_mobile']) ? 4 : 0;
	$verify_arr['mark_cnt'] = $mark_cnt;

	$patterns = "/@.+/";
	if($verify_arr['bbs_email']){
		$first = substr($verify_arr['bbs_email'],0,1);
		preg_match_all($patterns,$verify_arr['bbs_email'],$arr);
		$verify_arr['email'] = $first."******".$arr[0][0];
		$first_bbs = substr($verify_arr['dev_email'],0,1);
		$first_dev = substr($verify_arr['bbs_email'],0,1);
		preg_match_all($patterns,$verify_arr['bbs_email'],$arr_bbs);
		preg_match_all($patterns,$verify_arr['dev_email'],$arr_dev);
		$verify_arr['b_email'] = $first_bbs."******".$arr_dev[0][0];
		$verify_arr['d_email'] = $first_dev."******".$arr_bbs[0][0];
	}
	if($verify_arr['bbs_email'] && $verify_arr['dev_email'] && $verify_arr['dev_email_verify']==0 && $verify_arr['dev_mobile'] && $verify_arr['dev_mobile_verify']==0){

		$first_bbs = substr($verify_arr['dev_email'],0,1);
		$first_dev = substr($verify_arr['bbs_email'],0,1);
		preg_match_all($patterns,$verify_arr['bbs_email'],$arr_bbs);
		preg_match_all($patterns,$verify_arr['dev_email'],$arr_dev);
		$verify_arr['b_email'] = $first_bbs."******".$arr_dev[0][0];
		$verify_arr['d_email'] = $first_dev."******".$arr_bbs[0][0];
		$first_mobile = substr($verify_arr['dev_mobile'],0,3);
		$last_dev = substr($verify_arr['dev_mobile'],-3);
		$verify_arr['dev_mobile'] = $first_mobile."*****".$last_dev;
	}
	
	if($verify_arr['bbs_email'] && $verify_arr['dev_email'] && $verify_arr['dev_email_verify']==1 && $verify_arr['dev_mobile'] && $verify_arr['dev_mobile_verify']==1){
		$first_bbs = substr($verify_arr['dev_email'],0,1);
		$first_dev = substr($verify_arr['bbs_email'],0,1);
		preg_match_all($patterns,$verify_arr['bbs_email'],$arr_bbs);
		preg_match_all($patterns,$verify_arr['dev_email'],$arr_dev);
		$verify_arr['b_email'] = $first_bbs."******".$arr_dev[0][0];
		$verify_arr['d_email'] = $first_dev."******".$arr_bbs[0][0];
		$first_mobile = substr($verify_arr['dev_mobile'],0,3);
		$last_dev = substr($verify_arr['dev_mobile'],-3);
		$verify_arr['dev_mobile'] = $first_mobile."*****".$last_dev;
	}
	if($verify_arr['dev_mobile'] && $verify_arr['dev_mobile_verify']!=0){
		$first_bbs = substr($verify_arr['dev_email'],0,1);
		$first_dev = substr($verify_arr['bbs_email'],0,1);
		preg_match_all($patterns,$verify_arr['bbs_email'],$arr_bbs);
		preg_match_all($patterns,$verify_arr['dev_email'],$arr_dev);
		$verify_arr['b_email'] = $first_bbs."******".$arr_dev[0][0];
		$verify_arr['d_email'] = $first_dev."******".$arr_bbs[0][0];
		$first_mobile = substr($verify_arr['dev_mobile'],0,3);
		$last_dev = substr($verify_arr['dev_mobile'],-3);
		$verify_arr['dev_mobile'] = $first_mobile."*****".$last_dev;
	}
	unset($verify_arr['bbs_email']);
	unset($verify_arr['id']);
	unset($verify_arr['dev_email']);
	echo json_encode($verify_arr);
}

?>