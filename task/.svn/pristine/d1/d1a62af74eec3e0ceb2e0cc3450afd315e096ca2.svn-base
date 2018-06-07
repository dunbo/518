<?php
/*
 *   生成  测试渠道的sdk包
 */
include dirname(__FILE__).'/../init.php';
include_once(dirname(__FILE__).'/../../tools/functions.php');

$static_data = '/data/att/m.goapk.com';
$static_dir = '/data3';
$java_path = '/usr/bin/';

$bin_path = '/data/www/wwwroot/new-wwwroot/config/gnu';
if (!is_file("{$bin_path}/aapt")) {
    $bin_path = '/data/www/wwwroot/config/gnu';
}


$model = new GoModel();
$gift_base = array();
ini_set('default_socket_timeout', -1);

load_helper('task');
$worker = get_task_worker();
$worker->addFunction("sdk_channel_online_sdk_tmp", "make_online_sdk");
while ($worker->work());

function make_online_sdk($jobs){    

    global $java_path;
    global $static_data;
    global $static_dir;

    $jobs = $jobs->workload();
    $jobs = json_decode($jobs,true); 
	$sid = rand_code_md5();
	write_log(date('Y-m-d H:i:s',time()).json_encode($jobs), $sid);
	
    $url = $static_data.$jobs['url'];
    if(isset($jobs['sign_url']) && $jobs['sign_url'] != '' ){
        $sign_url = $static_data.$jobs['sign_url'];
        $sign_pwd = $jobs['sign_pwd'];
        $alias_name = $jobs['alias_name'];
    }else{		
		$sign_url = '/data/att/m.goapk.com/data3/sdk_game_sign/anzhi.keystore';
		$sign_pwd = 'anzhi@)!$';
		$alias_name = 'newkey.keystore';
	}
    $date = date('Y-m-d');
	$rand_path = "/tmp/sdk_game_channel_pack/{$date}/". $sid;
	mkdir($rand_path, 0777, true);
	mkdir($rand_path.'/assets/',0777,true); 
	
    $apk_tmp = $rand_path.'/'.$jobs['package'].'.apk.zip';
    $msg = "step1start cp {$url} {$apk_tmp}";
    write_log($msg, $sid);
    go_shell_exec("cp {$url} {$apk_tmp}");
	

	//无角标icon 替换
	$jobs['iconurl_512'] = $jobs['iconurl_512']?'/data/att/m.goapk.com'.$jobs['iconurl_512']:'';
	change_icon_apk($apk_tmp,$jobs['iconurl_512'],$rand_path, $sid);
    
	if($jobs['sdk_version']&&$jobs['sdk_version']=='3.2'){//3.2 文件夹
        $c_code_file = 'az_'.$jobs['channel_code']; 
		mkdir($rand_path.'/'.$c_code_file,0777,true); 
		go_shell_exec("cd  {$rand_path} ; zip -r {$apk_tmp} {$c_code_file}");
    }elseif($jobs['sdk_version']&&$jobs['sdk_version']=='3.3'){
        
    }elseif($jobs['sdk_version']){              
        file_put_contents($rand_path.'/assets/anzhikey', $jobs['channel_code']);
        go_shell_exec("cd  {$rand_path} ; zip -r {$apk_tmp} 'assets/anzhikey'");	
    }else{
        write_log("sdk_version is null return false", $sid);
    	return false;
    }
    
	$msg = "step1end-step2start "."zip -d {$apk_tmp} META-INF/*";
	write_log($msg, $sid);	
    go_shell_exec("zip -d {$apk_tmp} META-INF/*");
    	
    $apk_name = $rand_path.'/'.$jobs['softid'].'.apk.zip';
    
	$cmd = "{$java_path}jarsigner -storepass '{$sign_pwd}' -verbose -keystore {$sign_url} -signedjar {$apk_name} {$apk_tmp} {$alias_name}";
	$msg = "step2end-step3start ".$cmd;
    write_log($msg, $sid);
	
	$rc = go_shell_exec($cmd);
	unlink($apk_tmp);
	$msg = "step3end-setp4start";
	write_log($msg, $sid);
	
	if ($rc != 0 || !file_exists($apk_name) ) {
	    	write_log('正式包生成失败'."${cmd} could not be done.", $sid);			
	}else{
	    $jobs['apk_url'] = $apk_name;
	   
	    update_online_sdk($jobs, $sid);
	}    
}

function update_online_sdk($data, $sid){
    global $model;    
    global $static_data;
    global $static_dir;   
    $dir_apk = $static_data.$static_dir.'/apk/'.date('Ym/d').'/';
	$msg = "step4end-setp5start ".$dir_apk;
	write_log($msg, $sid);
	
    if(!is_dir($dir_apk)) {
        mkdir($dir_apk,0777,true);
		chmod($dir_apk,0777);
    }
	$msg = "step5end-setp6start";
	write_log($msg, $sid);
	
    $apk_name = $dir_apk.rand_code_md5().'.apk';    
    
    $channel_num = $data['channel_code'];
    
    
	if($data['sdk_version'] == '3.3'){//3.3写注释
	    $zip = new ZipArchive;
	    $res = $zip->open($data['apk_url'], ZipArchive::CREATE);
	    $zip->setArchiveComment($channel_num);
	    $zip->close();
	}        
			
    $msg = "step6end-setp7start cp {$data['apk_url']} {$apk_name}";
	write_log($msg, $sid);
    $cmd = "cp {$data['apk_url']} {$apk_name}";
    go_shell_exec($cmd);
    unlink($data['apk_url']);
    
	$msg = "step7end-setp8start";
	write_log($msg, $sid);
    $md5_file = md5_file($apk_name);
    $filesize = filesize($apk_name);
    $sign = getSignFromApk($apk_name);//获得签名
    
    $apk_name = str_replace($static_data,'',$apk_name);


    $where = array( 'id' => $data['id']); 
    $option = array(
            'apk_url' => $apk_name,
            'md5_file' => $md5_file,
            'filesize' => $filesize, 
          	'sign' => $sign,   
			'update_tm' => time(),
			'sdk_status' => 2,
			'apk_status' => 3,
            '__user_table' => 'sdk_channel_game_tmp'
    );
	if(!isset($data['is_general_channel'])){
		$option['sdk_status'] = 1;
	}
    $model -> update($where,$option);
    
    $msg = "step8end-setp9start ".$model->getSql();
	write_log($msg, $sid);
	write_log(var_export($data,true), $sid);
	if(!isset($data['is_general_channel'])){
		$msg = "test";
		write_log($msg, $sid);
		//其他渠道打完包判断此版本是否审核通过，审核通过处理game相关正式表
		$tmp_option = array(
			'table'=>'sj_soft_tmp',
			'where'=>array(
				'id' => $data['tmp_id']
			),
			'field'=>'status,sdk_status,softid,softname'
		);
		$tmp_info = $model->findOne($tmp_option);
		$msg = $model->getSql();
		write_log($msg, $sid);
		write_log(var_export($tmp_info,true), $sid);
		if($tmp_info['sdk_status']==1&&$tmp_info['status']==1){
			//软件审核已通过
			$data['softid'] = $tmp_info['softid'];
			$data['softname'] = $tmp_info['softname'];
			$data['apk_url'] = $apk_name;
			$data['apk_name'] = $apk_name;
			$data['md5_file'] = $md5_file;
			$data['filesize'] = $filesize;
			$data['sign'] = $sign;
			save_channel_game($data);
		}
	}
	

}

function save_channel_game($data){
	global $model;
	$option = array(
		'table'=>'sdk_channel_game',
		'where'=>array(
			'package' => $data['package'],
			'version_code_num' => array('exp'," < {$data['version_code']}"),
			'channel_id' => $data['channel_id']
		)
	);
	$game_info = $model->findOne($option);
	if($game_info){
		insert_old_version($game_info);
		$where = array('package'=>$data['package'],'channel_id'=>$data['channel_id']);
		$save_data = array(
			'softid'=>$data['softid'],
			'name'=>$data['softname'],
			'channel_softname'=>$data['softname'],
			'version_code_num'=>$data['version_code'],
			'version_code'=>$data['version'],
			'url'=>$data['apk_url'],
			'filesize'=>$data['filesize'],
			'md5_file'=>$data['md5_file'],
			'sign'=>$data['sign'],
			'apk_status'=>3,
			'update_tm'=>time(),
			'__user_table' => 'sdk_channel_game'
		);
		$res = $model->update($where,$save_data);
		$msg = "step11end-setp12start ".$model->getSql();
		write_log($msg, $sid);
		if($res){
			incremental_update_to_worker($data);
			insert_game_sdk($data);
			$url = '/game/sdkChannel/CfgChannelGame/updateRelation';
			$http_data = array(
					'version'=>$data['version'],
					'id'=>$game_info['id']
			);
			$http_info = update_date_by_import($url,$http_data);
			if(!$http_info){
				$model->update(array('id'=>$game_info['id']), array('__user_table'=>'sdk_channel_game','http_sta'=>'3'));
				$msg = "step13end-setp14start ".$model->getSql();
				write_log($msg, $sid);				
			}

		}
	}
}

function incremental_update_to_worker($data){
	$task_client = get_task_client();
	$task_data = array();
	$task_data['softid'] = $data['softid'];
	$task_data['package'] = $data['package'];
	$task_data['channel_id'] = $data['channel_id'];
	$task_data['version_code_num'] = $data['version_code'];
	$task_data['is_general_channel'] = ($data['channel_id']==$general_channel_id)?1:0;
	$task_client->doBackground('incremental_update_channel_sdk', json_encode($task_data));
}
function insert_game_sdk($data){
	global $model;
	$s_option = array(
		'table' => 'sdk_channel_game_sdk',
		'where' => array(
			'package'=>$data['package'],'status'=>1,'version_code'=>$data['version_code'],'channel_id'=>$data['channel_id'],'softid'=>$data['softid']
		)
	);

	$sdk = $model->findOne($s_option);
	
	if(!$sdk){
		$insert_data = array(
				'softid'=>$data['softid'],
				'softname'=>$data['softname'],
				'package'=>$data['package'],
				'game_type'=>'网游',
				'sdk_version'=>$data['sdk_version'],
				'version'=>$data['version'],
				'version_code'=>$data['version_code'],
				'record_type'=>3,
				'url_apk'=>$data['apk_url'],
				'url'=>$data['url'],
				'sdk_status'=>1,
				'status'=>1,
				'channel_id'=>$data['channel_id'],
				'update_tm'=>time(),
				'create_tm'=>time(),
				'need_test'=>$data['channel_id']==27?1:0,
				'sign'=>$data['sign'],
				'reviewer'=>3000, //新逻辑设定
				'__user_table' => 'sdk_channel_game_sdk'
		);
		$new_res = $model->insert($insert_data);
		$msg = "step12end-setp13start ".$model->getSql();
		write_log($msg, $sid);	
	}
}
function insert_old_version($val){
	global $model;
	$old_data = array(
			'softid'=>$val['softid'],
			'name'=>$val['name'],
			'channel_softname'=>$val['channel_softname'],
			'package'=>$val['package'],
			'version_code_num'=>$val['version_code_num'],
			'version_code'=>$val['version_code'],
			'channel_id'=>$val['channel_id'],
			'url'=>$val['url'],
			'filesize'=>$val['filesize'],
			'md5_file'=>$val['md5_file'],
			'apk_status'=>$val['apk_status'],
			'add_tm'=>$val['add_tm'],
			'update_tm'=>time(),
			'status'=>$val['status'],
			'http_sta'=>$val['http_sta'],
			'__user_table' => 'sdk_channel_game_bak'
	);
	$old_res = $model->insert($old_data);

	$msg = "step10end-setp11start ".$model->getSql();
	write_log($msg, $sid);
}
function change_icon_apk($tmp_path ,$iconfile,$rand_path, $sid){
	global $bin_path;
	if (empty($iconfile)) {
        return false;
    }
	if (!file_exists($iconfile)) {
        return false;
    }
    if (!file_exists($tmp_path)) {
        return false;
    }
	$cmd = "{$bin_path}/aapt d badging '{$tmp_path}' 2>/dev/null";
    $info = shell_exec($cmd);
    preg_match_all("/([a-z0-9\-A-Z_]+:.*')\n/sU", $info, $m);

    $icons = array();
    foreach($m[1] as $str){
        if (preg_match("/application:\ *?label='(.*?)'\ *?icon='([^']*?)'/", $str, $m)) {
            $app_icon = $m[2];
            $icons[$app_icon] = 1;
        }

        if(preg_match("/application\-icon\-(\d+):'([^']*?)'/i",$str,$m)) {
			$dpi_icon = $m[2];
			$icons[$dpi_icon] = 1;
        }
    }

    $files = array();
    foreach ($icons as $icon => $v) {
        $icon_path = $rand_path.'/'. $icon;
        $dir = dirname($icon_path);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        $files[] = $icon;
        copy($iconfile, $icon_path);
    }
	$cmd = "cd {$rand_path} ; zip -r {$tmp_path} ". implode(' ', $files);
	write_log($cmd, $sid);
    go_shell_exec($cmd);
	write_log("icon copy end", $sid);
}

function rand_code_md5() {
    return md5(rand(1, 100000) . microtime().uniqid());
}

function go_shell_exec($cmd) {
    $ret = shell_exec("${cmd}; echo $?");
    if ($ret != 0)
        file_put_contents('/tmp/go_shell_exec.err', "${cmd}\n", FILE_APPEND);
    return intval($ret);
}

function write_log($msg, $sid){
	$log = "/data/att/permanent_log/channel_apk_log/".date('Y-m-d').'/';
	if(!is_dir($log)) {
		mkdir($log,0777,true) ;
	}
	$t = date('[Y-m-d H:i:s]');
file_put_contents($log.'make_online_sdk_tmp.log', "{$t} {$sid}: {$msg}\n", FILE_APPEND);
}
function update_date_by_import($url,$data){		
	//$host = 'http://42.62.70.157:9511';
	$host = 'http://192.168.3.136:8089';
	//$url = '/game/sdkChannel/CfgChannelGame/addRelation';
	$privatekey = 'eeUu5p6XElQbYGM26iCIOmo2';
	$des = new GoDes($privatekey);
	$data['pid'] = 1; 
	//var_dump($data);
	$temp_data = $des->encrypt(json_encode($data));
	$i_data = base64_encode($temp_data);
	$vals['data'] = $i_data;
	$vals['appKey'] = '142605894293bjc9VR9P3Xqv7jFTgh';
	//var_dump($host.$url);
	//var_dump($vals);
	// return false;
	$res = httpGetInfo($host.$url, $vals,'sdk_channel_http.log'); 
	$last = json_decode($res,true);
	//var_dump($last);
	if($last['statusCode']!='200'){
		return false;
	}else{
		return true;
	}
}

function httpGetInfo($url, $vals,$log_name="sdkchannel_http.log") {
	$res = curl_init();
	curl_setopt($res, CURLOPT_URL, $url);
	curl_setopt($res, CURLOPT_POST, true);
	curl_setopt($res, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($res, CURLOPT_POSTFIELDS, $vals);
	$result = curl_exec($res);
	$http_code = curl_getinfo($res, CURLINFO_HTTP_CODE);
	$errno = curl_errno($res);
	$error = curl_error($res);
	curl_close($res);
	write_log("{$http_code}|{$errno}|{$error}\n" .$url. print_r($vals, true) . "\n" . print_r($result, true) . "\n\n", $sid);
	return $result;
}