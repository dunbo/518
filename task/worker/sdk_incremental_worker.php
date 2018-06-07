<?php

require_once(dirname(__FILE__).'/../init.php');
$ip = getServerIp();
//只在18使用
if ($ip != '192.168.1.18' && $ip != '192.168.0.99' && $ip != '192.168.1.242') {
    exit;
}

$bsdiff_file = "/usr/bin/bsdiff";
if (!file_exists($bsdiff_file)) {
	$bsdiff_file = "/usr/local/sbin/bsdiff";
}

define("BSDIFF_PATH", $bsdiff_file);
define("APK_PREFIX","/data/att/m.goapk.com");
define("APK_TEMP_PREFIX","/tmp");
define("LOG_PREFIX","/tmp/");
define("IS_OPEN_INCREMENTAL",true);

//task_work
$worker->addFunction("sdk_incremental_update", "sdk_incremental_update_func");
while($worker->work());
function sdk_incremental_update_func($job) {
	if( !($p = json_decode($job->workload(), true)) ) {
		return False;
	}
	if(empty($p['id']) || empty($p['oid'])) {
		return false;
	}
	$inc_type = isset($p['inc_type']) ? $p['inc_type'] : 'insert';  
	$server = 'master';
	$pattern = "/^\/apk\/([\d]+)\/([\d]+)\/(.*).apk$/i";
	$model = new GoModel();
	$where = array(
		'id'=>$p['id'],
		'status'=>1,
	);
	$option = array(
		'where'=>$where,
		'field'=>'apkurl,apksize,target_version_code,md5_file,id,version_code',
		'table'=>'sj_sdk',
	);
	$new_market_info = $model->findOne($option);
	if (empty($new_market_info)) {
		return true;
	}
	
	$old_version_id = implode(",",$p['oid']);
	
	$new_market_package = '';
	if(preg_match($pattern,$new_market_info['apkurl'],$match)) {
		$new_market_package = $match[3];
	}
	if(empty($new_market_info['md5_file'])) {
		if(file_exists(APK_PREFIX.$new_market_info['apkurl'])) {
			$new_market_info['md5_file'] = md5_file(APK_PREFIX. $new_market_info['apkurl']);
			$sql = "update sj_sdk set md5_file='{$new_market_info['md5_file']}' where id={$p['id']} limit 1";
			$model->query($sql);
		}
	}
	$sql = "select apkurl,apksize,target_version_code,id,md5_file,version_code from sj_sdk where id in (".$old_version_id.")";
	$query = $model->query($sql);
	while($old_market_info = $model->fetch($query)) {
		$old_market_package = '';
		if(preg_match($pattern,$old_market_info['apkurl'],$match)) {
			$old_market_package = $match[3];
		}
		if (empty($new_market_package) || empty($old_market_package)) {
			// todo, error
			write_log("exit:package name preg_match error, new_market_package:{$new_market_package}, old_market_package:{$old_market_package}!");
			continue;
		}
		$package = $new_market_package."_".$old_market_package;
		if(empty($old_market_info['md5_file'])) {
			if (file_exists(APK_PREFIX. $old_market_info['apkurl'])) {
				$old_market_info['md5_file'] = md5_file(APK_PREFIX. $old_market_info['apkurl']);
				$sql = "update sj_sdk set md5_file='{$old_market_info['md5_file']}' where id={$old_market_info['id']} limit 1";
				$model->query($sql);
			}
		}
		// 查找此高版本是否对此低版本已生成过ota
		$where = array('new_md5'=>$new_market_info['md5_file'],'old_md5'=>$old_market_info['md5_file'],"status"=>1);
		$option = array(
			'where'	=>	$where,
			'table'	=>	'sj_sdk_patch',
			'field'	=>	'id'
		);
		$exists_patch_info = $model->findOne($option,$server);
		if($exists_patch_info && $inc_type == 'insert') {
			write_log("exit:the incemental_file is exists!");
			continue;
		}
		// 生成ota
		$new_incremental_patch = make_incremental_update_file($new_market_info['apkurl'],$old_market_info['apkurl'],$package);
		if (empty($new_incremental_patch)) {
			continue;
		}
		// 入ota表
		$data = array(
			 '__user_table' => "sj_sdk_patch",
			 'new_market_id' => $new_market_info['id'],
			 'old_market_id' => $old_market_info['id'],
			 'new_version_code' => $new_market_info['version_code'],
			 'old_version_code' => $old_market_info['version_code'],   
			 'new_md5' => $new_market_info['md5_file'],
			 'old_md5' => $old_market_info['md5_file'],
			 'patch_url'=>$new_incremental_patch['url'],
			 'filesize'=>$new_incremental_patch['filesize'],
			 'md5'=>$new_incremental_patch['md5'],
			 'status'=> IS_OPEN_INCREMENTAL?1:0,
			 'create_at' => time(),
			 'update_at' => time(),
		);
		if ($inc_type == 'insert') {
			$insert_flag = $model->insert($data);
		} else {
			$insert_flag = $model->update(array('id'=>$exists_patch_info['id']), $data);
		}
		if(!$insert_flag) {   
			write_log("exit:incrmental_recode insert false!");
			continue;
		}
		// 入表/更新表成功
		$patch_dir =  dirname(APK_PREFIX.$new_incremental_patch['url']);
		if(!is_dir($patch_dir)){
			$mkdir_cmd = shell_exec("mkdir -p  ".$patch_dir);
		}
		$cmd = shell_exec("cp -f ".APK_TEMP_PREFIX.$new_incremental_patch['url']."  ".APK_PREFIX.$new_incremental_patch['url']);
		go_make_links(APK_PREFIX. $new_incremental_patch['url']);
		// 没必要拆成小块了
		//splitfile(APK_PREFIX. $new_incremental_patch['url'], dirname(APK_PREFIX. $new_incremental_patch['url']));
	}
	return true;
}

// 生成ota
function make_incremental_update_file($new_package, $old_package, $package) {
	$patch_dir_prefix = "/patch/".date("Ym/d/");
	$patch_dir =  APK_TEMP_PREFIX.$patch_dir_prefix;
	$patch_prefix = $package."_".time().".patch";
	$patch_file = $patch_dir.$patch_prefix;
	if(!is_dir($patch_dir))   {
		$mkdir_cmd = shell_exec("mkdir -p  ".$patch_dir);
	}

	$old_package = APK_PREFIX.$old_package;
	if(!file_exists($old_package))
	{
		write_log("exit:{$old_package} old_version_package is not existed");
		return false;
	}
	$new_package = APK_PREFIX.$new_package;
	if(!file_exists($new_package))
	{
		write_log("exit:{$new_package} new_version_package is not existed");
		return false;
	}
	$cmd = shell_exec(BSDIFF_PATH." ".$old_package." ".$new_package." ".$patch_file);
	if(file_exists($patch_file))
	{
		return array("url"=>$patch_dir_prefix.$patch_prefix,"filesize"=>filesize($patch_file),"md5"=>md5_file($patch_file));
	}

	return false;
}

//分割文件
function splitfile($fn, $out_dir, $bs = 524288, $dir_name_num = 2) {
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
		if ($fs <= $bs){
			$cmd = "mkdir -p ${out_dir} && cp ${file} ${out}";
	        }else{
	        	$cmd = "mkdir -p ${out_dir} && dd if=\"${file}\" of=\"${out}\" bs=${bs} skip=${n} count=1 2>&1  >> /tmp/splitfile.log";
	        }
	        shell_exec($cmd);
	        $n += 1;
	        go_make_links($out);
	    }
	    return $n;
}

//生成软连接
function go_make_links($file) {
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
	if(empty($content)) return false;
	$t = date('Y-m-d H:i:s');
	$log_dir  = LOG_PREFIX."market_incremetal/".date("Ym/d/");
	if(!is_dir($log_dir))
	{
		shell_exec("mkdir -p  ".$log_dir);
	}
	$log_url = $log_dir."market_incremental.log";
	//$file_url = LOG_PREFIX."incremental/".date("Ym/d/")."incremental.log";
	file_put_contents($log_url,"{$t}\t{$content}\n",FILE_APPEND);
}