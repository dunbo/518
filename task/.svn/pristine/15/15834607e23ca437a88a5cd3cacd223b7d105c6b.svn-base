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
$worker->addFunction("sdk_channel_online_sdk", "make_online_sdk");
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
    write_log("TEST:".$jobs['sdk_version'], $sid);
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
	//unlink($apk_tmp);
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


    $where = array( 'package' => $data['package'],
            		'softid'  => $data['softid'],
            		'channel_id' => $data['channel_id'],
            		'version_code_num' =>$data['version_code']
    				); 
    $option = array(
            'url' => $apk_name,
            'md5_file' => $md5_file,
            'filesize' => $filesize, 
          	'sign' => $sign,   
			'update_tm' => time(),
            '__user_table' => 'sdk_channel_game'
    );
    $model -> update($where,$option);
    
    $where2 = array(
            'package' => $data['package'],
            'softid'  => $data['softid'],
            'channel_id' => $data['channel_id'],
            'version_code' =>$data['version_code']
    );
    $option2 = array(
            'url_apk' => $apk_name,
            'sdk_status' => 2,
            '__user_table' => 'sdk_channel_game_sdk'
    );

    $model -> update($where2,$option2);
    $msg = "step8end-setp9start ".$model->getSql();
	write_log($msg, $sid);
	//通用渠道不需要测试时将推广状态更改为推广中
	if(isset($data['is_general_channel'])&&$data['need_test']==0){
		$extend_where = array('package'=>$data['package']);
		$extend_data = array(
			'extend_sta' => 2,
			'update_tm' => time(),
			'__user_table' => 'sdk_channel_extend'
		);
		$model -> update($extend_where,$extend_data);
	}
	$msg = "step9end-setp10start ".$model->getSql();
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
file_put_contents($log.'make_online_sdk.log', "{$t} {$sid}: {$msg}\n", FILE_APPEND);
}