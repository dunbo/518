<?php
include_once ('./init.php');
session_begin();
$sid = $_GET['sid'];

if(empty($_SESSION['admin']['admin_id'])) {
        header("Location: http://".$prefix_url."/lottery/store/login.php?sid=".$sid);
}

if($_POST){
    $activity_option = array(
        'where' => array(
            'password'=>md5($_POST['password_old']),
            'status'=>1,
            'id'=>$_SESSION['admin']['admin_id']
        ),
	'table' => 'store_user'
    );
    $userinfo= $model -> findOne($activity_option,'lottery/lottery');
    if(empty($userinfo)){
        echo 2;exit(0);
    }else{
        //更新记录
        $data = array(
                'password' => md5($_POST['password']),
                '__user_table' => 'store_user'
        );
        $where = array(
                'id' =>  $_SESSION['admin']['admin_id']
        );
        $model->update($where, $data,'lottery/lottery');
        echo 1;exit(0);
    }
}

$tplObj -> out['static_url'] = $configs['static_url'];
$tpl = "lottery/store/seller_change_password.html";
$tplObj -> display($tpl);
