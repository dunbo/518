<?php
include_once ('./init.php');
session_begin();
$sid = $_GET['sid'];

if(empty($_SESSION['admin']['admin_id'])) {
        header("Location: http://".$prefix_url."/lottery/store/login.php?sid=".$sid);
}



                $activity_option = array(
                    'where' => array(
                            'store_id' => $_SESSION['admin']['admin_id'],
                            'status' => array('exp','!=1'),
                            'exchange_tm' => array('exp','>='.strtotime(date('Y-m-d')).' and exchange_tm<='.time())
                    ),
                    'field'=>'count(*)',
                    'table' => 'store_order'
                );
                $rest= $model -> findOne($activity_option,'lottery/lottery');

                $ke_use = 20-$rest['count(*)'];
$tplObj -> out['ke_use'] = $ke_use;

if($_POST){
    load_helper('task');
    $task_client = get_task_client();
    $data['store_id'] = $_SESSION['admin']['admin_id'];
    if(empty($_SESSION['admin']['admin_id'])){
        echo 4;exit(0);
    }
    $data['yzm'] = $_POST['yzm'];
    $data['check_num'] = 20;
    $the_award = $task_client->do('store_checknum', json_encode($data));

    $active_id = 360;

    if($the_award==1){
        $activity_option = array(
                'where' => array(
                        'ver_code' => $_POST['yzm'],
                ),
                'table' => 'store_order'
        );
        $shop= $model -> findOne($activity_option,'lottery/lottery');

        $log_data = array(
                'status' => $shop['status'],
                'device_id' => $shop['imei'],
                'telphone' => $shop['tel'],
                'city' => $shop['city'],
                'school' => $shop['school'],
                'store_name' => $shop['store_name'],
                'activity_id' => $active_id,
                'ip' => $_SERVER['REMOTE_ADDR'],
                'time' => time(),
                'ver_code' => $_POST['yzm'],
                'shopid' => $_SESSION['admin']['admin_id'],
                'key' => 'store_user_exchange'
        );
        permanentlog('activity_'.$active_id.'.log', json_encode($log_data));
    }

    if($the_award==1){
                $activity_option = array(
                    'where' => array(
                            'store_id' => $_SESSION['admin']['admin_id'],
                            'status' => array('exp','!=1'),
                            'exchange_tm' => array('exp','>='.strtotime(date('Y-m-d')).' and exchange_tm<='.time())
                    ),
                    'field'=>'count(*)',
                    'table' => 'store_order'
                );
                $rest= $model -> findOne($activity_option,'lottery/lottery');

                $ke_use = 20-$rest['count(*)'];
                $re_data=array();
                $re_data['ke_use'] = $ke_use;
                $re_data['code'] = 1;
                echo json_encode($re_data);
                exit(0);
    }else if($the_award==2){
        echo 2;exit(0);
    }else if($the_award==-1){
        echo 3;exit(0);
    }else if($the_award==5){
        echo 5;exit(0);
    }
}

$tplObj -> out['static_url'] = $configs['static_url'];

$tpl = "lottery/store/seller_index.html";
$tplObj -> display($tpl);
