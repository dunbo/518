<?php
include_once ('./init.php');
session_begin();
$sid = $_GET['sid'];

if(empty($_SESSION['admin']['admin_id'])) {
        header("Location: http://".$prefix_url."/lottery/store/login.php?sid=".$sid);
}


$where['id'] = $_SESSION['admin']['admin_id'];
//$where['id']=7;
$activity_option = array(
	'where' => $where,
	'table' => 'store_user'
);
$userinfo= $model -> findOne($activity_option,'lottery/lottery');

if($_POST){
    $activity_option = array(
        'where' => array(
            'tel'=>$_POST['mobile'],
            'status'=>1,
            'id'=>array('exp','!='.$_SESSION['admin']['admin_id']),
        ),
	'table' => 'store_user'
    );
    $userinfo= $model -> findOne($activity_option,'lottery/lottery');
    if(!empty($userinfo)){
        echo 2;exit(0);
    }else{
        //更新记录
        $data = array(
                'tel' => $_POST['mobile'],
                '__user_table' => 'store_user'
        );
        $where = array(
                'id' => $_SESSION['admin']['admin_id']
        );
        $model->update($where, $data,'lottery/lottery');
        echo 1;exit(0);
    }
}


$tplObj -> out['static_url'] = $configs['static_url'];
$tplObj -> out['userinfo'] = $userinfo;
$tpl = "lottery/store/seller_info.html";
$tplObj -> display($tpl);
