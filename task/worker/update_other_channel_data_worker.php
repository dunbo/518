<?php
/* 软件测试通过 操作普通渠道软件 */

include(dirname(realpath(__FILE__)).'/../init.php');
header('content-type:text/html;charset=utf-8');
error_reporting(E_ALL);
$ip = getServerIp();
//只在18使用
 if ($ip != '192.168.1.18' && $ip != '192.168.0.99') {
     exit;
 }
$general_channel_id = 27;
$data;

ini_set('default_socket_timeout', -1);

load_helper('task');
$worker = get_task_worker();
$worker->addFunction("update_other_channel_data", "update_other_chanel");
while ($worker->work());

function update_other_chanel($jobs){
	global $general_channel_id;
	$model = new GoModel();

	$jobs = $jobs->workload();
    $data = json_decode($jobs,true);
	write_channel_log(var_export($data,true)."\n");
	//不是通用渠道软件 不处理
	if(empty($data['channel_id']) || $data['channel_id'] != $general_channel_id){
		return false;
	}

	$option = array(
		'where' => array(
			'package' => $data['package'] ,
			'status' => 1,
			'channel_id' => array('exp','!=27'),
		),        
		'table' => 'sdk_channel_game',
	);
	$game_data = $model->findAll($option);
	
	foreach($game_data as $value){
		//版本低于通用渠道版本
		echo $value['version_code_num']."--".$data['version_code'];
		if($value['version_code_num'] < $data['version_code']){
			deal_single_game($value,$data);
		}		
	}
}

//处理单个游戏
function deal_single_game($game,$game_sdk){
	$model = new GoModel();
	$time = time();
	//$model->query('BEGIN');
	//sdk_channel_game
	$where = array('id'=>$game['id']);
	$update_data = array('__user_table' => 'sdk_channel_game','softid'=>$game_sdk['softid'],'name'=>$game_sdk['softname'],'channel_softname'=>$game_sdk['softname'],'version_code_num'=>$game_sdk['version_code'],'version_code'=>$game_sdk['version'],'url'=>'','filesize'=>'','md5_file'=>'','apk_status'=>'0','update_tm'=>$time);
	$update_res = $model->update($where,$update_data);
	//echo $model->getSql()."\n";
	if(!$update_res) write_channel_log($model->getSql()."\n");
	//sdk_channel_game_sdk
	$data = array(
		'softid' => $game_sdk['softid'],
		'softname' => $game_sdk['softname'],
		'package' => $game_sdk['package'],
		'game_type' => $game_sdk['game_type'],
		'sdk_version' => $game_sdk['sdk_version'],
		'version' => $game_sdk['version'],
		'version_code' => $game_sdk['version_code'],
		'record_type' => $game_sdk['record_type'],
		'url' =>		$game_sdk['url'],
		'sdk_status' => 4 ,
		'channel_id' => $game['channel_id'],
		'need_test' => 0,
		'update_tm' => $time,
		'create_tm'=> $time,
		'__user_table' => 'sdk_channel_game_sdk'
	);
	$res_game_sdk = $model->insert($data);
	//echo $model->getSql()."\n";
	if(!$res_game_sdk) write_channel_log($model->getSql()."\n");
	//sdk_channel_game_bak
	$data_bak = array();
	$data_bak_field = array('softid','name','channel_softname','package','version_code_num','version_code','channel_id','url','filesize','md5_file','apk_status','add_tm','update_tm','sign','http_sta');
	foreach($game as $key=>$val){
		if(in_array($key,$data_bak_field)){
			$data_bak[$key] = $val;
		}
	}
	$data_bak['add_tm'] = $time;
	$data_bak['__user_table'] = 'sdk_channel_game_bak';
	$res_game_bak = $model->insert($data_bak);
	//echo $model->getSql()."\n";
	if(!$res_game_bak) write_channel_log($model->getSql()."\n");

	if($update_res&&$res_game_bak&&$res_game_sdk){
		//$model->query('COMMIT');
		write_channel_log("更新其他渠道成功");
	}else{
		//$model->query('ROLLBACK');
		write_channel_log("更新其他渠道失败");
	}
	
}

function write_channel_log($msg){
	$log = "/data/att/permanent_log/channel_apk_log/".date('Y-m-d').'/';
	if(!is_dir($log)) {
		mkdir($log,0777,true) ;
	}
    file_put_contents($log.'make_orther_channel.log', date("Y-m-d H:i:s")."\n".$msg."\n\n", FILE_APPEND);
}

