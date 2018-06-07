<?php
require_once(dirname(__FILE__).'/../init.new.php');

$ip = getServerIp();
//只在55使用
if ($ip != '192.168.1.55' && $ip!= '192.168.1.105') {
    exit;
}

define("BSDIFF_PATH", "/usr/bin/bsdiff");
define("APK_PREFIX", "/data/att/m.goapk.com");
define("THIRD_APK_PREFIX", "");

define("APK_TARGET_PREFIX", "/data/att/m.goapk.com");
define("LOG_PREFIX", "/tmp/");
define("APK_TEMP_PREFIX", "/tmp");
$package_size_section = array(
	"min" => 100*1024,
	"max" => 250*1024*1024
);
$read_server = 'master';

function incremental_update_func($job)
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
	$rule_config = $rule_config[0];
	$filesize_config = json_decode($rule_config['rule_config'],true);

	$option = array(
		'where' => array(
			"A.softid" => $p['softid'],
			"B.package_status" => 1,
			"A.hide" =>1,
			"A.status"=>1,
		),
		'field'=> "A.total_downloaded, A.package,A.version_code, A.softid, B.filesize, B.md5_file, B.id AS fileid, B.url as url",
		'table'=> "sj_soft AS A",
		'join' => array(
			'sj_soft_file AS B' => array(
				'on' => array('A.softid', 'B.softid'),
			),
		),
	);
	$new_info = $model->findOne($option, $read_server);

	if(empty($new_info)) {
		write_log("exit: {$p['softid']} new_package not found");
		return false;
	}

	if ($new_info['total_downloaded'] < $rule_config['download_num']) {
		$option = array(
			'where' => array(
			   'package_name' => $new_info['package'],
			),
			'table' => 'sj_incrementalupdate_white_list',
		);
		$white_list = $model->findOne($option, $read_server);
		//下载量小于设定值，并且不属于白名单的话，不进行处理
		if (!$white_list) {
			write_log("exit: {$p['softid']} the download_num<{$rule_config['download_num']}");
			return false;
		}
	}

	//查找市场软件旧版本列表
	$patch_dir_prefix = "/data2/patch/" . date("Ym/d/");
	$option = array(
		'order' => "A.version_code DESC",
		'limit' => $rule_config['version_distance'],
		'field' => "A.version_code, A.softid, B.filesize, B.md5_file, B.id AS fileid, B.url",
		'table' => "sj_soft AS A",
		'join' => array(
			 'sj_soft_file AS B' => array(
				'on' => array('A.softid', 'B.softid'),
			),
		),
		'where' => array(
			"A.package" => $new_info['package'],
			"B.package_status" => 1,
			"A.hide" => array('exp','in(0,1)'),
			"A.status"=> 1,
			'A.version_code'=> array('exp', '<'. $new_info['version_code']),
		),
	);
	$old_infos = $model->findAll($option, $read_server);
	$old_path = APK_PREFIX;
	foreach ($old_infos as $old_info) {
        if($new_info['filesize']<$package_size_section['min']||$new_info['filesize']>$package_size_section['max']) {
            write_log("exit: {$p['softid']} filesize limited");
            continue;
        }
		if(empty($old_info['md5_file'])){
			$old_url = $old_path. $old_info['url'];
			if (file_exists($old_url)) {
				$old_info['md5_file'] = md5_file($old_url);
				$old_info['sha1_file'] = sha1_file($old_url);
				$sql = "update sj_soft_file set sha1_file='{$old_info['sha1_file']}', md5_file='{$old_info['md5_file']}' where id={$old_info['fileid']} limit 1";
				$model->query($sql);
			}
		}
		if(empty($old_info['md5_file'])){
			write_log("exit: {$p['softid']} the md5 of old_package is empty");
			continue;
		}
		process_incremental($new_info, $old_info, $patch_dir_prefix, $filesize_config, $old_path, 1);
	}

	//查找第三方采集软件旧版本列表
	$patch_dir_prefix = "/data2/third/patch/" . date("Ym/d/");
	$option = array(
		'order' => "version DESC",
		'limit' => $rule_config['version_distance'],
		'field' => "version as version_code, id as softid, size as filesize, md5_val as md5_file, id AS fileid, path as url",
		'table' => "cj_soft",
		'where' => array(
			'status' => 1,
			'package' => $new_info['package'],
			'version' => array('exp', '<'. $new_info['version_code'])
		),
	);
	$old_infos = $model->findAll($option, 'caiji');
	$old_path = THIRD_APK_PREFIX;
	foreach ($old_infos as $old_info) {
		if(empty($old_info['md5_file'])){
			write_log("exit: {$p['softid']} the md5 of old_package is empty");
			continue;
		}
		process_incremental($new_info, $old_info, $patch_dir_prefix, $filesize_config, $old_path, 3);
	}
}

function process_incremental($new_info, $old_info, $patch_dir_prefix, $filesize_config, $old_path, $status) 
{
	global $read_server;
	$model = new GoModel();
	$option = array(
		'where'	=> array(
			'package' => $new_info['package'], 
			'new_md5' => $new_info['md5_file'], 
			'old_md5' => $new_info['md5_file'],
			'status' => array('exp', '>0')
		),
		'table'	=> 'sj_soft_patch',
		'field'	=> 'id'
	);
	$exists_patch_info = $model->findOne($option, $read_server);
	if($exists_patch_info){
		write_log("exit: {$p['softid']} diff exists");
		return false;
	}

	$in_whitelist = false;
	$incremental_info = make_incremental_update_file($new_info['url'], $old_info['url'], $new_info['package'], $patch_dir_prefix, $old_path);
	if ($incremental_info) {
		$incremental_filesize = $incremental_info['filesize'];
		$insert_db = true;
		if (!$in_whitelist) {
			$flag = check_incremental_rule($incremental_filesize, $new_info['filesize'], $new_info['package'], $filesize_config);
			if (!$flag) {
				$insert_db = false;
				write_log("exit:" . "\t" . $p['package'] . "\t" . "not fit rule_config");
				return false;
			}
		}
		if ($insert_db) {
			$insert_flag = add_patch_to_db($new_info, $old_info, $incremental_info, $status);
			if (!$insert_flag) {
				write_log("exit: {$p['package']} insert failed");
				return false;
			} else {
				$src_path = APK_TEMP_PREFIX . $incremental_info['url'];
				$des_path = APK_TARGET_PREFIX . $incremental_info['url'];
				$patch_dir = dirname($des_path);
				if (!is_dir($patch_dir)) {
					$cmd = "mkdir -p {$patch_dir}";
					shell_exec($cmd);
				}
				$cmd = "cp -f {$src_path} {$des_path}";
				shell_exec($cmd);
//				go_make_links($des_path);
//				splitfile($des_path, dirname($des_path));
				unlink($src_path);
			}
		}
	}
}

function check_incremental_rule($incremental_filesize, $new_size, $package, $filesize_config) 
{
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
		'__user_table' => "sj_soft_patch",
		'package'=>$new_info['package'],
		'new_softid'=>$new_info['softid'],
		'old_softid'=>$old_info['softid'],
		'new_fileid'=>$new_info['fileid'],
		'old_fileid'=>$old_info['fileid'],
		'new_md5'=>$new_info['md5_file'],
		'old_md5'=>$old_info['md5_file'],
		'new_version_code'=>$new_info['version_code'],
		'old_version_code'=>$old_info['version_code'],
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
	
	return $insert_flag;
}

function make_incremental_update_file($new_url, $old_url, $package, $patch_dir_prefix, $old_path) 
{
    list($s, $m) = explode(".", microtime(true));
    $patch_filename = "{$package}_{$s}{$m}.patch";
	
    $patch_dir = APK_TEMP_PREFIX . $patch_dir_prefix;
    if (!is_dir($patch_dir)) {
		$cmd = "mkdir -p  {$patch_dir}";
        shell_exec($cmd);
    }
    $patch_file = $patch_dir .$patch_filename;

	//检查旧文件是否存在
    $old_url = $old_path. $old_url;
    if (!file_exists($old_url)) {
        write_log("exit: {$old_url} old_version_package is not existed:");
        return false;
    }
	
	//检查新文件是否存在
    $new_url = APK_PREFIX . $new_url;
    if (!file_exists($new_url)) {
        write_log("exit: {$new_url} new_version_package is not existed");
        return false;
    }
	
	//生成增量更新文件
	$cmd = BSDIFF_PATH . " {$old_url} {$new_url} {$patch_file}";
    shell_exec($cmd);
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
        shell_exec($cmd);
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
    if ($uppername != $basename)
        shell_exec("cd ${dirname}; ln -s '${basename}' '${uppername}'");
    $lowername = strtolower($basename);
    if ($lowername != $basename)
        shell_exec("cd ${dirname}; ln -s '${basename}' '${lowername}'");
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
