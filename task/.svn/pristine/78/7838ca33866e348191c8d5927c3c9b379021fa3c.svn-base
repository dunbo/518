<?php
require_once(dirname(__FILE__).'/../init.new.php');

$ip = getServerIp();
//只在55使用
if ($ip != '192.168.1.55') {
    //exit;
}

define("BSDIFF_PATH", "/usr/local/sbin/bsdiff");
define("APK_PREFIX", "/data/att/m.goapk.com");
define("THIRD_APK_PREFIX", "");

define("APK_TARGET_PREFIX", "/data/att/m.goapk.com");
define("LOG_PREFIX", "/tmp/");
define("APK_TEMP_PREFIX", "/tmp");
$package_size_section = array(
	"min" => 100*1024,
	"max" => 100*1024*1024
);
$read_server = 'master';

$worker = new GoGearmanWorker(); //实例化可不指定配置，调用默认gearman服务器
$worker->addFunction("incremental_update_channel_sdk", "incremental_update_channel_sdk_func");
$worker->run();

function incremental_update_channel_sdk_func($job)
{
	global $package_size_section, $read_server;
	
	$string = $job->workload();
	if ( !($p = json_decode($string, true)) ) {
		return false;
	}
	
	$model = new GoModel();
	$option = array(
		'table'=>'sj_incremental_rule',
		'order' => "id DESC",
	);
	$rule_config = $model->findAll($option, $read_server);
	
	$msg = date('Y-m-d H:i:s',time())."\n\nstep1start\n".$model->getSql()."\n".var_export($p) ;
    write_log1($msg);
	
	$rule_config = $rule_config[0];
	$filesize_config = json_decode($rule_config['rule_config'],true);
	
	$msg = "step1end-step2start\n";
    write_log1($msg);
	
	$option = array(
		'where' => array(
			"softid"  =>  $p['softid'],
			"package" => $p['package'],
		    "channel_id" => $p['channel_id'],
			"version_code_num" => $p['version_code_num'],
			"status"=>1,
		),
		'field'=> "package,version_code_num,channel_id,softid, filesize, md5_file, url",
		'table'=> "sdk_channel_game"
	);
	$new_info = $model->findOne($option, $read_server);
	
	$msg = "step2end-step3start\n".var_export($new_info);
    write_log1($msg);
	
	//查找市场软件旧版本列表
	$patch_dir_prefix = "/data2/patch/" . date("Ym/d/");
	$option = array(
		'order' => "version_code_num DESC",
		'limit' => $rule_config['version_distance'],
		'field' => "softid,filesize,md5_file,url,version_code_num,package,channel_id",
		'table' => "sdk_channel_game_bak",
		'where' => array(
			"package" => $new_info['package'],
		    "channel_id" => $new_info['channel_id'],
			'version_code_num'=> array('exp', '<'. $new_info['version_code_num']),
		),
	);
	if(isset($p['is_general_channel'])&&$p['is_general_channel']==1){
	    unset($option['where']['channel_id']);
	    unset($option['limit']);
	}
	$old_infos = $model->findAll($option, $read_server);
	
	$msg = "step3end-step4start\n".var_export($old_infos);
    write_log1($msg);
	
	$old_path = APK_PREFIX;
	$version_num = array();
	foreach ($old_infos as $old_info) {
        if($new_info['filesize']<$package_size_section['min']||$new_info['filesize']>$package_size_section['max']) {
            write_log1("exit: {$old_info['softid']} -- {{$old_info['channel_id']}} filesize limited");
            continue;
        }
		if(empty($old_info['md5_file'])){
			write_log1("exit: {$old_info['softid']} -- {$old_info['channel_id']} the md5 of old_package is empty");
			continue;
		}
		if(!in_array($old_info['version_code_num'],$version_num)){
		    $version_num[] = $old_info['version_code_num'];		    
		}
		if(count($version_num)>8){
		    write_log1("版本数已经有8个退出\n");
		    return false;
		}
		
		process_incremental($new_info, $old_info, $patch_dir_prefix, $filesize_config, $old_path, 1);
	}
}

function process_incremental($new_info, $old_info, $patch_dir_prefix, $filesize_config, $old_path, $status) 
{
	global $read_server;
	$model = new GoModel();
	
	$msg = "step4end-step5start\n";
    write_log1($msg);
	
	$option = array(
		'where'	=> array(
			'package' => $new_info['package'],
			'new_md5' => $new_info['md5_file'],
			'old_md5' => $old_info['md5_file'],
			'status' => array('exp', '>0')
		),
		'table'	=> 'sdk_channel_soft_patch',
		'field'	=> 'id'
	);
	$exists_patch_info = $model->findOne($option, $read_server);
	
	$msg = "step5end-step6start\n".$model->getSql().'\n'.var_export($exists_patch_info);
    write_log1($msg);
	
	if($exists_patch_info){
		write_log1("exit: {$p['softid']} diff exists");
		return false;
	}

	$in_whitelist = false;
	$incremental_info = make_incremental_update_file($new_info['url'], $old_info['url'], $new_info['package'], $patch_dir_prefix, $old_path);
	
	$msg = "step6end-step7start\n".var_export($incremental_info);
    write_log1($msg);
	
	if ($incremental_info) {
		$incremental_filesize = $incremental_info['filesize'];
		$insert_db = true;
		
		$msg = "step7end-step8start\n";
		write_log1($msg);
		
		if (!$in_whitelist) {
			$flag = check_incremental_rule($incremental_filesize, $new_info['filesize'], $new_info['package'], $filesize_config);
			if (!$flag) {
				$insert_db = false;
				write_log1("exit:" . "\t" . $p['package'] . "\t" . "not fit rule_config");
				return false;
			}
		}
		
		$msg = "step8end-step9start\n";
		write_log1($msg);
		
		if ($insert_db) {
			$insert_flag = add_patch_to_db($new_info, $old_info, $incremental_info, $status);
			
			$msg = "step9end-step10start\n";
			write_log1($msg);
			
			if (!$insert_flag) {
				write_log("exit: {$p['package']} insert failed");
				return false;
			} else {
				$src_path = APK_TEMP_PREFIX . $incremental_info['url'];
				$des_path = APK_TARGET_PREFIX . $incremental_info['url'];
				$patch_dir = dirname($des_path);
				
				$msg = "step10end-step11start\n"."mkdir -p {$patch_dir}";
				write_log1($msg);
				
				if (!is_dir($patch_dir)) {
					$cmd = "mkdir -p {$patch_dir}";
					shell_exec($cmd);
				}
				
				$msg = "step11end-step12start\n"."cp -f {$src_path} {$des_path}";
				write_log1($msg);
				
				$cmd = "cp -f {$src_path} {$des_path}";
				shell_exec($cmd);
				
				$msg = "step12end-step13start\n"."cp -f {$src_path} {$des_path}";
				write_log1($msg);
				
				go_make_links($des_path);
				
				$msg = "step13end-step14start\n"."cp -f {$src_path} {$des_path}";
				write_log1($msg);
				
				splitfile($des_path, dirname($des_path));
				
				$msg = "step14end\n"."cp -f {$src_path} {$des_path}";
				write_log1($msg);
			}
		}
	}
}

function check_incremental_rule($incremental_filesize, $new_size, $package, $filesize_config) 
{
	$msg = "step8(1)start\n";
	write_log1($msg);
	
    $file_rate = ($incremental_filesize / $new_size);
	$flag = true;
	foreach ($filesize_config as $val) {
		if (($new_size > $val[0] && $new_size <= $val[1])) {
			if ($file_rate >= $filesize_config[0][2]) {
				$flag = false;
			}
			break;
		}
	}
	
	$msg = "step8(2)start\n";
	write_log1($msg);
	
	if (!$flag) {
		$incremental_filesize_mb = $incremental_filesize / (1024 * 1024);
		$new_size_mb = $new_size / (1024 * 1024);
		$rate = ($file_rate * 100) . '%';
		write_log("exit: {$package} incremental_filesize:{$incremental_filesize_mb} new_filesize:{$new_size_mb} rate:{$rate}");
	}
    return $flag;
}

function add_patch_to_db($new_info, $old_info, $incremental_info, $status)
{
	$model = new GoModel();
	$data = array(
		'__user_table' => "sdk_channel_soft_patch",
		'package'=>$new_info['package'],
	    'channel_id'=>$new_info['channel_id'],	   
	    'channel_id_old'=>$old_info['channel_id'],
		'new_softid'=>$new_info['softid'],
		'old_softid'=>$old_info['softid'],
		'new_md5'=>$new_info['md5_file'],
		'old_md5'=>$old_info['md5_file'],
		'new_version_code'=>$new_info['version_code_num'],
		'old_version_code'=>$old_info['version_code_num'],
		'new_package_filesize'=>$new_info['filesize'],
		'old_package_filesize'=>$old_info['filesize'],
		'url'=>$incremental_info['url'],
		'filesize'=>$incremental_info['filesize'],
		'md5'=>$incremental_info['md5'],
		'status'=> $status,
		'create_at' => time(),
		'update_at' => time(),
	);
	$insert_flag = $model->insert($data);
	
	$msg = "step9(1)start\n".$model->getSql().'\n'.'insert is'.$insert_flag;
	write_log1($msg);
	
	return $insert_flag;
}

function make_incremental_update_file($new_url, $old_url, $package, $patch_dir_prefix, $old_path) 
{
    list($s, $m) = explode(".", microtime(true));
	$rand_num = rand(100,1000);
    $patch_filename = "{$package}_{$s}{$m}{$rand_num}.patch";
	
    $patch_dir = APK_TEMP_PREFIX . $patch_dir_prefix;
	
	$msg = "step6(1)start\n".$patch_dir;
    write_log1($msg);
	
    if (!is_dir($patch_dir)) {
		$cmd = "mkdir -p  {$patch_dir}";
        shell_exec($cmd);
    }
	
    $patch_file = $patch_dir .$patch_filename;

	//检查旧文件是否存在
    $old_url = $old_path. $old_url;
	
	$msg = "step6(1)end-step6(2)start\n".$old_url;
    write_log1($msg);
	
    if (!file_exists($old_url)) {
        write_log("exit: {$old_url} old_version_package is not existed:");
        return false;
    }
	
	//检查新文件是否存在
    $new_url = APK_PREFIX . $new_url;
	
	$msg = "step6(2)end-step6(3)start\n".$new_url;
    write_log1($msg);
	
    if (!file_exists($new_url)) {
        write_log("exit: {$new_url} new_version_package is not existed");
        return false;
    }
	
	//生成增量更新文件
	$cmd = BSDIFF_PATH . " {$old_url} {$new_url} {$patch_file}";
	
	$msg = "step6(3)end-step6(4)start\n".$cmd;
    write_log1($msg);
	
    shell_exec($cmd);
	
	$msg = "step6(4)end\n".$cmd;
    write_log1($msg);
	
    if (file_exists($patch_file)) {
        return array(
			"url" => $patch_dir_prefix .$patch_filename, 
			"filesize" => filesize($patch_file), 
			"md5" => md5_file($patch_file)
		);
    }

    return false;
}

function splitfile($fn, $out_dir, $bs = 524288, $dir_name_num = 2) 
{
    if (!is_file($fn)) {
        return false;
    }
    $file = realpath($fn);
    $dir = dirname($file);
    $name = basename($fn);
    $fs = filesize($fn);
    $n = 0;
    $out_dir = "${out_dir}";
    for ($i = 0; $i < $fs; $i += $bs) {
        $out = sprintf("${out_dir}/${name}.%04d", $n);
        if ($fs <= $bs) {
            $cmd = "mkdir -p ${out_dir} && cp ${file} ${out}";
        } else {
            $cmd = "mkdir -p ${out_dir} && dd if=\"${file}\" of=\"${out}\" bs=${bs} skip=${n} count=1 2>&1  >> /tmp/splitfile.log";
        }
		$msg = "step14(1)\n".$cmd;
		write_log1($msg);
		
        shell_exec($cmd);
		
		$msg = "step14(2)\n".$cmd;
		write_log1($msg);
		
        $n += 1;
        go_make_links($out);
    }
    return $n;
}

function go_make_links($file) 
{
    $dirname = dirname($file);
    $basename = basename($file);
    $uppername = strtoupper($basename);
    if ($uppername != $basename){
		$msg = "step13(1)\n"."cd ${dirname}; ln -s '${basename}' '${uppername}'";
		write_log1($msg);
		
        shell_exec("cd ${dirname}; ln -s '${basename}' '${uppername}'");
		
		$msg = "step13(2)\n"."cd ${dirname}; ln -s '${basename}' '${uppername}'";
		write_log1($msg);
	}
	
		
    $lowername = strtolower($basename);
    if ($lowername != $basename){
		$msg = "step13(3)\n"."cd ${dirname}; ln -s '${basename}' '${lowername}'";
		write_log1($msg);
		
        shell_exec("cd ${dirname}; ln -s '${basename}' '${lowername}'");
		
		$msg = "step13(4)\n"."cd ${dirname}; ln -s '${basename}' '${lowername}'";
		write_log1($msg);
	}
		
		
}

function write_log($content) 
{
    if (empty($content))
        return false;
    $t = date('Y-m-d H:i:s');
    $log_dir = LOG_PREFIX . "incremental/" . date("Ym/d/");
    if (!is_dir($log_dir)) {
        shell_exec("mkdir -p  " . $log_dir);
    }
    $log_url = $log_dir . "incremental.log";
    file_put_contents($log_url, "{$t}\t{$content}\n", FILE_APPEND);
}

function write_log1($msg){
    file_put_contents('/data/att/permanent_log/incremental_sdk.log', $msg."\n\n", FILE_APPEND);
}
