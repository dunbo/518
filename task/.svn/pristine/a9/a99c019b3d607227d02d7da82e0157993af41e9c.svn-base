<?php
/**
 * 
 * sj_user_package表中的package字段保存了用户的安装软件
 * 
 * package为json_encode 的数组，有可能有三种格式
 * 1. 保存包名的一维数组
 * 2. 保存package,version_code的二维数组
 * 3. 保存package,version_code,version_name的二维数组
 * 在程序处理的时候需要做兼容
 * 
 * 注意，本脚本是实时更新的，对于软件的下载总量无法统计，所以不在本脚本更新总下载数，而在cron2/suggest_lack10.php里执行
 * 
 */
require_once(dirname(__FILE__).'/../init.php');
$worker->addFunction("update_lack", "update_lack_func");  
while ($worker->work());  
function update_lack_func($job)  
{  
    if ( !($p = json_decode($job->workload(), true)) ) {
        return False;
    }

	$id = trim($p['id']);

	//$id为表sj_user_package中的id字段
	if (empty($id)) {
		return False;;
	}
	$server = 'master';
	$model = new GoModel();

	$where = array(
		'id' => $id
	);
	$option = array(
		'where' => $where,
		'table' => 'sj_device_user_package',
		'field' => 'packages'
	);

	$result = $model->findOne($option, $server);
	$packages = json_decode($result['packages'], true);

	if (!$packages) return False;;
	foreach ($packages as $val) {
		$user_version_code = false;
		$user_version_name = '';
		if (!is_array($val)) {
			//旧记录，只记录了包名，所以只能进行缺乏软件的检查
			$package = $val;
		} else{
			$package = $val[0];	
			$user_version_code = $val[1];
			if (count($val) == 3) {
				//记录了version_code和version_name
				$user_version_name = $val[2];
			}
		}
		
		$option = array(
			'where' => array(
				'package' => $package
			),
			'table' => 'sj_soft_lack',
			'field' => 'status, user_version_code'
		);
		$lack = $model->findOne($option, $server);
		
		if ($lack && $lack['status'] == 0) continue;
		
		$option = array(
			'where' => array(
				'package' => $package,
				'status' => 1,
				'hide' => 1,
			),
			'table' => 'sj_soft',
			'field' => 'package, version_code, version,softname'
		);
		$soft = $model->findOne($option, $server);
		
			
		$data = array(
			'status' => 1,
			'package' => $package,
			'last_refresh' => time(),
			'__user_table' => 'sj_soft_lack',
		);
		if (!$soft) {
			$data['type'] = 1;
			$data['user_version_code'] = $user_version_code;
			$data['user_version_name'] = $user_version_name;
			//软件不存在，进行缺乏检查 
			if ($lack) {
				if ($user_version_code >= $lack['user_version_code']) {
					$where = array(
						'package' => $package
					);
					$model->update($where, $data, $server);
				}
			} else {
				$data['upload_time'] = time();
				$data['numbers'] = 1;
				$model->insert($data, $server);
			}
		} else {
			$data['type'] = 2;

			//软件存在，进行版本检查
			if ($user_version_code === false) {
				continue;
			} elseif ($user_version_code > $soft['version_code']) {
				$data['user_version_code'] = $user_version_code;
				$data['user_version_name'] = $user_version_name;
				$data['version_code'] = $soft['version_code'];
				$data['version_name'] = $soft['version'];
				$data['softname'] = $soft['softname'];
				if ($lack) {
					if ($user_version_code >= $lack['user_version_code']) {
						$where = array(
							'package' => $package
						);
						$model->update($where, $data, $server);
					}

				} else {
					$data['upload_time'] = time();
					$data['numbers'] = 1;
					$model->insert($data, $server);
				}
			}
		}
	}
}