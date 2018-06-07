<?php
include_once ('./init.php');
session_begin();
$sid = $_GET['sid'];

if(empty($_SESSION['admin']['admin_id'])) {
        header("Location: http://".$prefix_url."/lottery/store/login.php?sid=".$sid);
}

$activity_option = array(
    'where' => array(
        'status'=>2,
        'store_id'=>$_SESSION['admin']['admin_id'],
    ),
    'field'=> 'count(*)',
    'table' => 'store_order',
);

$wjs= $model -> findOne($activity_option,'lottery/lottery');
$tplObj -> out['wjs'] = 10*$wjs['count(*)'];

$activity_option = array(
    'where' => array(
        'status'=>3,
        'store_id'=>$_SESSION['admin']['admin_id'],
    ),
    'field'=> 'count(*)',
    'table' => 'store_order',
);

$yjs= $model -> findOne($activity_option,'lottery/lottery');
$tplObj -> out['yjs'] = 10*$yjs['count(*)'];


//$where['store_id'] = $_SESSION['admin']['admin_id'];

$where['store_id'] = $_SESSION['admin']['admin_id'];

if(strlen($_GET['begintime'])>0){
    $where['exchange_tm']  = array('exp','>='.strtotime($_GET['begintime'].'00:00:00').' and exchange_tm <='.strtotime($_GET['endtime'].'23:59:59'));
    //$where['exchange_tm']  = array('exp','<='.strtotime($_GET['endtime'].'23:59:59'));
}

$where['status'] = array('exp','!=1');
if($_GET['status']=='未结算'){
    $where['status']=2;
}

if($_GET['status']=='已结算'){
    $where['status']=3;
}

$activity_option = array(
	'where' => $where,
	'table' => 'store_order',
        'order' => 'exchange_tm desc'
);

if(isset($_GET['is_sub'])){
    $orderlist = $model -> findAll($activity_option,'lottery/lottery');
}

foreach($orderlist as $k=>$v){
    $orderlist[$k]['yz_date'] = date('Y-m-d',$v['exchange_tm']);
    $orderlist[$k]['yz_time'] = date('H:i:s',$v['exchange_tm']);
    if($v['status']==2){
        $orderlist[$k]['status_str'] = '未结算';
    }
    if($v['status']==3){
        $orderlist[$k]['status_str'] = '已结算';
    }
}


$tplObj -> out['orderlist'] = $orderlist;
$tplObj -> out['get'] = $_GET;
$tplObj -> out['static_url'] = $configs['static_url'];
$tpl = "lottery/store/seller_balance.html";
$tplObj -> display($tpl);
