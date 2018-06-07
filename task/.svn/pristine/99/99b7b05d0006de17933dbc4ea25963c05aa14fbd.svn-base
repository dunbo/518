<?php
require_once(dirname(__FILE__).'/../init.new.php');

$ip = getServerIp();
//只在55使用
if ($ip != '192.168.1.55') {
    exit;
}

define("BSDIFF_PATH", "/usr/bin/bsdiff");
define("APK_PREFIX", "/data/att/m.goapk.com");
define("THIRD_APK_PREFIX", "");
define("APK_TARGET_PREFIX", "/data/att/m.goapk.com");
define("LOG_PREFIX", "/tmp/");
define("APK_TEMP_PREFIX", "/tmp");

$package_size_section = array("min" => 100 * 1024, "max" => 100 * 1024 * 1024);

$worker = new GoGearmanWorker(); //实例化可不指定配置，调用默认gearman服务器
$worker->addFunction("incremental_update", "incremental_update_func");
$worker->run();

function third_history_delta_func($job) {
    global $package_size_section;

	$string = $job->workload();
	if ( !($p = json_decode($string, true)) ) {
		return false;
	}
	
    $server = "master";
    if (empty($p))
        return false;
	
	$patch_dir_prefix = "/data2/third/patch/" . date("Ym/d/");
    $model = new GoModel();
    $redis = new GoRedisCacheAdapter(load_config("incremental/redis"));
    $option = array(
        'table' => 'sj_incremental_rule',
        'order' => "id DESC"
	);
    $rule_config = $model->findAll($option);
    $rule_config = $rule_config[0];
    $filesize_section = json_decode($rule_config['rule_config'], true);

    $option = array(
        'where' => array(
			"A.package" => $p['package'],
			"A.hide" => 1,
			"A.status" => 1,
			"A.version_code" => array('exp', '>'. $p['version']),
			"B.package_status" => 1,
		),
        'field' => "A.package AS package,A.version AS version,A.version_code AS version_code,A.softname AS softname,A.softid AS softid,B.filesize AS filesize,B.md5_file AS md5_file,B.id AS fileid,B.url as url",
        'table' => "sj_soft AS A",
        'join' => array(
            'sj_soft_file AS B' => array(
                'on' => array('A.softid', 'B.softid'),
            ),
        ),
    );
    $new_version_packages = $model->findAll($option, $server);
    if (empty($new_version_packages)) {
        write_log("exit: {$p['package']} new_package not found");
        return false;
    }
    $where = array(
        'package_name' => $p['package'],
    );
    $option = array(
        'where' => $where,
        'table' => 'sj_incrementalupdate_white_list',
        'field' => 'package_name'
    );
    $white_list = $model->findOne($option, $server);
    $is_pass_white_check = false;
    $is_enter_rule_check = false;
    $is_incremental_publish = false;
    if (empty($white_list)) {
        $where = array(
            'package' => $p['package'],
        );
        $option = array(
            'where' => $where,
            'table' => 'sj_soft_statistics',
            'field' => 'mob_dl_cnt,mob_up_cnt',
        );
        $soft_download_info = $model->findOne($option, $server);
        // write_log("download_info:"."\t".$p['package']."\t".json_encode($soft_download_info));
        if ($soft_download_info) {
            $soft_download_num = (int) ($soft_download_info['mob_dl_cnt']) + (int) $soft_download_info['mob_up_cnt'];
            write_log("download_num:" . "\t" . $p['package'] . "\t" . $soft_download_num);
            if ($soft_download_num < $rule_config['download_num']) {
                write_log("exit: {$p['package']} the download_num <200000");
                return false;
            } else {
                $is_enter_rule_check = true;
            }
        } else {
            write_log("exit: {$p['package']} have no download info");
        }
    } else {
        $is_pass_white_check = true;
    }
    if ($is_pass_white_check == true || $is_enter_rule_check == true) {
        if ($new_version_packages && count($new_version_packages) > 0) {
            foreach ($new_version_packages as $key => $new_version_package) {
				if ($new_version_package['filesize'] < $package_size_section['min'] || $new_version_package['filesize'] > $package_size_section['max']) {
					write_log("exit: {$p['package']} filesize limited");
					continue;
				}
                if (empty($new_version_package['md5_file'])) {
                    if (file_exists(APK_PREFIX . $new_version_package['url'])) {
                        $new_version_package['md5_file'] = md5_file(APK_PREFIX . $new_version_package['url']);
                        $new_version_package['sha1_file'] = sha1_file(APK_PREFIX . $new_version_package['url']);
                        $sql = "update sj_soft_file set sha1_file='{$new_version_package['sha1_file']}', md5_file='{$new_version_package['md5_file']}' where id={$old_version_package['fileid']} limit 1";
                        $model->query($sql);
                    }
                }
                if (empty($new_version_package['md5_file'])) {
                    write_log("exit: {$new_version_package['softid']} the md5 of new_package is  empty");
                    continue;
                }

                $option = array(
					'where' => array(
						'package' => $new_version_package['package'], 
						'new_md5' => $new_version_package['md5_file'], 
						'old_md5' => $p['md5_val'],
						'status' => array('exp', '>0'),
					),
                    'table' => 'sj_soft_patch',
                    'field' => 'id'
				);
                $exists_patch_info = $model->findOne($option, $server);
                if ($exists_patch_info) {
                    write_log("exit: {$new_version_package['package']} {$new_version_package['md5_file']} {$p['md5_val']} diff exists");
                    continue;
                }
                $incremental_update_info = make_incremental_update_file($new_version_package['url'], $p['path'], $new_version_package['package'], $patch_dir_prefix);
                if ($incremental_update_info) {
                    $incremental_update_filesize = $incremental_update_info['filesize'];
                    if ($is_enter_rule_check) {
                        $publish_flag = check_incremental_rule($incremental_update_filesize, $new_version_package['filesize'], $p['path'], $filesize_section);
                        if ($publish_flag) {
                            $is_incremental_publish = true;
                        } else {
                            //$redis->set($new_version_package['package'] . '_' . $new_version_package['md5_file'] . '_' . $old_version_package['md5_file'], 1);
                            write_log("exit:" . "\t" . $p['package'] . "\t" . "not fit rule_config");
                        }
                    }
                    if ($is_pass_white_check) {
                        $is_incremental_publish = true;
                    }
                    if ($is_incremental_publish || $is_pass_white_check) {
                        $data = array(
                            '__user_table' => "sj_soft_patch",
                            'package' => $new_version_package['package'],
                            'new_softid' => $new_version_package['softid'],
                            'old_softid' => $p['id'],
                            'new_fileid' => $new_version_package['fileid'],
                            'old_fileid' => $p['id'],
                            'new_md5' => $new_version_package['md5_file'],
                            'old_md5' => $p['md5_val'],
                            'new_version_code' => $new_version_package['version_code'],
                            'old_version_code' => $p['version'],
                            'new_package_filesize' => $new_version_package['filesize'],
                            'old_package_filesize' => $p['size'],
                            'url' => $incremental_update_info['url'],
                            'filesize' => $incremental_update_filesize,
                            'md5' => $incremental_update_info['md5'],
                            'status' => 3, //第三方采集历史软件增量状态
                            'create_at' => time(),
                            'update_at' => time(),
                        );
                        $insert_flag = $model->insert($data);
						$insert_flag = true;

                        //write_log("insert succ:"."\t".$p['package']."\t".$insert_flag);
                        if (!$insert_flag) {
                            //unlink(APK_PREFIX. $incremental_update_info['url']);
                            write_log("exit: {$p['package']} insert failed");
                            continue;
                        } else {
                            $patch_dir = dirname(APK_TARGET_PREFIX . $incremental_update_info['url']);
                            if (!is_dir($patch_dir)) {
                                $mkdir_cmd = shell_exec("mkdir -p  " . $patch_dir);
                            }
                            $cmd = "cp -f " . APK_TEMP_PREFIX . $incremental_update_info['url'] . "  " . APK_TARGET_PREFIX . $incremental_update_info['url'];
							shell_exec($cmd);
							echo $cmd,"\n";
                            go_make_links(APK_TARGET_PREFIX . $incremental_update_info['url']);
                            splitfile(APK_TARGET_PREFIX . $incremental_update_info['url'], dirname(APK_TARGET_PREFIX . $incremental_update_info['url']));
                        }
                    }
                } else {
                    write_log("exit:  {$p['package']} diff make  false");
                }
            }
            return true;
        }
    }
    return false;
}

function check_incremental_rule($incremental_filesize, $new_package_size, $old_package, $filesize_section) {
    $file_rate = ($incremental_filesize / $new_package_size);
	$flag = true;
	foreach ($filesize_section as $val) {
		if (($new_package_size > $val[0] && $new_package_size <= $val[1])) {
			if ($file_rate >= $filesize_section[0][2]) {
				$flag = false;
			}
			break;
		}
	}
	if (!$flag) {
		$incremental_filesize_mb = $incremental_filesize / (1024 * 1024);
		$new_package_size_mb = $new_package_size / (1024 * 1024);
		$rate = ($file_rate * 100) . '%';
		write_log("exit: {$old_package} package_filesize is:{$incremental_filesize_mb}  the new_package_filesize:{$new_package_size_mb}  the rate is:{$rate} ");
	}
    return $flag;
}

function make_incremental_update_file($new_package, $old_package, $package, $patch_dir_prefix) {
    $patch_dir = APK_TEMP_PREFIX . $patch_dir_prefix;
    list($s, $m) = explode(".", microtime(true));
    $patch_filename = "{$package}_{$s}_{$m}.patch";
    $patch_file = $patch_dir . $patch_filename;
    if (!is_dir($patch_dir)) {
        $mkdir_cmd = shell_exec("mkdir -p  " . $patch_dir);
    }

    $old_package = THIRD_APK_PREFIX . $old_package;
    if (!file_exists($old_package)) {
        write_log("exit: {$old_package} old_version_package is not existed:");
        return false;
    }
    $new_package = APK_PREFIX . $new_package;
    if (!file_exists($new_package)) {
        write_log("exit: {$new_package} new_version_package is not existed");
        return false;
    }
    $cmd = shell_exec(BSDIFF_PATH . " " . $old_package . " " . $new_package . " " . $patch_file);
    if (file_exists($patch_file)) {
        return array("url" => $patch_dir_prefix . $patch_filename, "filesize" => filesize($patch_file), "md5" => md5_file($patch_file));
    }

    return false;
}

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

function write_log($content) {
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
