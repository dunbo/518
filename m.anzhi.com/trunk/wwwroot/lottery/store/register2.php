<?php
include_once ('./init.php');
session_begin();
$imei= $_SESSION['DEVICEID'];
$ret = $redis->get('azstore:sale:register:'.$imei);

$activity_option = array(
        'where' => array(
                'pid' => 0,
                'status' => 1
        ),
	'cache_time' => 3600,
	'table' => 'store_school'
);
$citylist = $model -> findAll($activity_option,'lottery/lottery');
$tplObj -> out['citylist'] = $citylist;

if($_POST){
    if(!empty($_POST['city_select'])){

            $activity_option = array(
                    'where' => array(
                            'pname' => $_POST['city_select'],
                            'status' => 1
                    ),
                    'cache_time' => 3600,
                    'table' => 'store_school'
            );
            $schoolist = $model -> findAll($activity_option,'lottery/lottery');
        echo json_encode($schoolist);exit(0);
    }

    if(empty($imei)){
        echo 3;exit(0);
    }
    //用户名已存在
    $activity_option = array(
        'where' => array(
                'tel' => $_POST['tel'],
                'status' => 1
        ),
        'table' => 'store_user'
    );
    $res= $model -> findOne($activity_option,'lottery/lottery');
    if(!empty($res)){
        echo 2;exit(0);
    }

    //验证码验证

    $rett = $redis->get('azstore:sale:register:'.$imei);
    $activity_option = array(
        'where' => array(
                'imei' => $imei,
                'code' => $_POST['yzm'],
                'username' => $rett['username'],
                'status' => 0
        ),
        'table' => 'store_recommend_vercode'
    );
    $ress= $model -> findOne($activity_option,'lottery/lottery');
    if(empty($ress)){
        echo 4;exit(0);
    }else{
        if(time()-$ress['create_tm']>1800){
            echo 4;exit(0);
        }
    }

    $award_data = array(
            'username' => $rett['username'],
            'password' => md5($rett['password']),
            'create_tm' => time(),
            'city' => $_POST['city'],
            'school' => $_POST['school'],
            'store_name' => $_POST['store_name'],
            'shopkeeper' => $_POST['shopkeeper'],
            'tel' => $_POST['tel'],
            'alipay' => $_POST['alipay'],
            'alipay_name' => $_POST['alipay_name'],
            'recommend' => $rett['recommend'],
            'id_number' => $_POST['id_number'],
            '__user_table' => 'store_user'
    );
    $ret = $model -> insert($award_data,'lottery/lottery');
    if($ret==false){
        echo 5;exit(0);
    }

    $data = array(
            'status' => 1,
            'update_tm' => time(),
            '__user_table' => 'store_recommend_vercode'
    );
    $where = array(
                'imei' => $imei,
                'code' => $_POST['yzm'],
                'username' => $rett['username'],
                'status' => 0
    );
    $model->update($where, $data,'lottery/lottery');

    $redis->delete('azstore:sale:register:'.$imei);
    $_SESSION['admin']['admin_id'] = $ret;
    echo 1;exit(0);
}

$tplObj -> out['ret'] = $ret;
$tpl = "lottery/store/register2.html";
$tplObj -> out['static_url'] = $configs['static_url'];
$tplObj -> display($tpl);
