<?php
/*
** 按钮日志
*/
require_once(dirname(realpath(__FILE__)).'/init.php');

$type = $_POST['type'];
$welfare_id = $_POST['welfare_id'];
$welfare_name = $_POST['welfare_name']; //福利名称
$from = $_POST['from'] ? $_POST['from'] : ''; //index 首页  detail 详情页
$pkg = $_POST['pkg']; //包名
$soft_id = $_POST['soft_id']; //软件id

if($type == 1){
	$key = 'download_soft';
}else if($type == 2){
	$key = 'install_soft';
}else if($type == 3){
    $key = 'open_soft';
}else if($type == 4){
    $key = 'share_soft';
}

// 记日志
$log_data = array(
    'imsi' => $imsi,
    'device_id' => $_SESSION['DEVICEID'],
    'ip' => $_SERVER['REMOTE_ADDR'],
    'sid' => $sid,
	'package' => $pkg, //包名
	'soft_id' => $soft_id, //软件id
    'welfare_id' => $welfare_id, //福利id
    'welfare_name' => $welfare_name, //福利名称
    'from' => $from, //index 首页  detail 详情页
    'time' => time(),
    'key' => $key, //download_soft下载软件  install_soft安装软件  open_soft打开软件
);
permanentlog($welfare_log_file, json_encode($log_data));

//领取人数处理
if($type == 3){
    $welfare_id = $_POST['welfare_id'];
    //获取福利信息
    $fl_info = $model->findOne(array(
        'where' => array(
            'id' => $welfare_id,
        ),
        'field' => 'begin_val,end_val,click_num,init_val',
        'table' => 'fl_welfare',
    ), 'lottery/lottery');
    $random_num = rand($fl_info['begin_val'], $fl_info['end_val']); //增加的随机领取数
    //更新数据库
    $where = array(
        'id' => $welfare_id,
    );
    $data = array(
        'click_num' => array('exp', '`click_num`+'.$random_num),
        'click_true_num' => array('exp', '`click_true_num`+1'),
        '__user_table' => 'fl_welfare', //游戏次数表
    );
    $ret = $model->update($where, $data, 'lottery/lottery');
    //更新缓存
    $rkey_receive_random_num = 'welfare:'.$welfare_id.':receive:num';
    if(empty($redis->get($rkey_receive_random_num))){
        $redis->setx('set', $rkey_receive_random_num, (int)($fl_info['init_val']+$fl_info['click_num']+$random_num), 600);
        $now_num = $redis->get($rkey_receive_random_num);
    }else{
        $now_num = $redis->setx('incrby', $rkey_receive_random_num, $random_num);
    }
    echo $now_num;
}