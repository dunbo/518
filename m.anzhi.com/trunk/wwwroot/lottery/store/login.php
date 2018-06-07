<?php
include_once ('./init.php');
session_begin();

if($_GET['logout']==1){
    session_destroy();
    header("Location: http://".$prefix_url."/lottery/store/login.php?sid=".$_GET['sid']);
}

if($_POST){
    $activity_option = array(
            'where' => array(
                    'username' =>$_POST['account'] ,
                    'password' =>md5($_POST['password']),
                    'status' =>1
            ),
            'table' => 'store_user'
    );
    $rs = $model -> findOne($activity_option,'lottery/lottery');

    if(empty($rs)){
        $activity= array(
                'where' => array(
                        'tel' =>$_POST['account'] ,
                        'password' =>md5($_POST['password']),
                        'status' =>1
                ),
                'table' => 'store_user'
        );
        $res = $model -> findOne($activity,'lottery/lottery');
        if(empty($res)){
            echo 2;exit(0);
        }else{
            $_SESSION['admin']['admin_id'] = $res['id'];
            echo 1;exit(0);
        }
    }else{
        $_SESSION['admin']['admin_id'] = $rs['id'];
        echo 1;exit(0);
        //header("Location: http://".$prefix_url."/lottery/store/seller_index.php?sid=".$_GET['sid']);
    }
}


$activity_option = array(
	'where' => array(
		'id' => 1
	),
	'cache_time' => 3600,
	'table' => 'store_config'
);
$config_info = $model -> findOne($activity_option,'lottery/lottery');

$tpl = "lottery/store/login.html";
$tplObj -> out['static_url'] = $configs['static_url'];
$tplObj -> out['config_info'] = $config_info;
$tplObj -> out['imgurl'] = getImageHost();
$tplObj -> display($tpl);
