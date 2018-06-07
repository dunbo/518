<?php
require_once(dirname(__FILE__).'/../init.php');
$firmware = array(3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16);
$abi_test = array(1, 2, 4, 8, 3);
$server = 'master';
$model = new GoModel();

load_helper('utiltool');
$worker->addFunction("sync_filter_cache", "sync_filter_cache_func");  
while ($worker->work());

function getCache($key)
{
	return GoCache::get($key, 'memcached');
}

function writeCache($key, $val) {
	//»º´æÄ¬ÈÏ±£´æ10·ÖÖÓ
	return GoCache::set($key, $val, 600, 'memcached');
}

function sync_filter_cache_func($job)
{
	global $firmware, $abi_test, $server, $model;
	
    if ( !($p = json_decode($job->workload(), true)) ) {
        return False;
    }
	echo date('Y-m-d H:i:s', time()).' '.var_export($p, true)."\n";
    if ( isset($p['softid'])) {
		$option = array(
			'table' => 'sj_soft AS A',
			'field' => array(
				'A.softid',
				'A.package',
				'B.min_firmware AS sj_soft_file_min_firmware',
				'B.max_firmware AS sj_soft_file_max_firmware',
				'B.abi',
			),
			'join' => array(
				'sj_soft_file AS B' => array(
					'on' => array('A.softid', 'B.softid'),
				),
			),
			'where' => array(
				'A.softid' => $p['softid'],
				'B.package_status' => 1,
			),
		);
		$soft_info = $model->findAll($option, $server);
		$id_to_package = array();
		foreach ($abi_test as $abi) {
			$ng_count = array();
			$ng_maybe = array();
			foreach ($soft_info as $idx => $val) {
				$p = $val['softid'];//softid
				$id_to_package[$p] = $val['package'];
				if (!isset($ng_count[$p]))
					$ng_count[$p] = 0;
				$ng_count[$p] += 1;
				$apk_abi = intval($val['abi']);
				if ($apk_abi != 0 && ($abi & $apk_abi) == 0 ) {
					if (!isset($ng_maybe[$p])) {
						$ng_maybe[$p] = 0;
					}
					$ng_maybe[$p] += 1;
				}
			}
			$result = array();
			foreach ($ng_maybe as $p => $v) {
				if ($v == $ng_count[$p]) {
					$result[$p] = $id_to_package[$p];
				}
			}
			
			$cache_key = "SOFTLIST_NG_SOFTID_A${abi}_INTIME";
			$new_cache = $old_cache = getCache($cache_key);
			foreach($result as $k => $v){
				if (!isset($old_cache[$k])) {
					$new_cache[$k] = $v;
				}
			}
			if (count($new_cache) != count($old_cache)) {
				file_put_contents('/tmp/sync_filter_cache.log', var_export($result, true), FILE_APPEND);
				writeCache($cache_key, $new_cache);
			}
			
		}
		
		foreach ($firmware as $fid) {
			$ng_count = array();
			$ng_maybe = array();
			foreach ($soft_info as $idx => $val) {
				$p = $val['softid'];
				if (!isset($ng_count[$p]))
					$ng_count[$p] = 0;
				$ng_count[$p] += 1;
				$max_firmware = intval($val['sj_soft_file_max_firmware']);
				if ($max_firmware > 0 && $fid > $max_firmware) {
					if (!isset($ng_maybe[$p]))
						$ng_maybe[$p] = 0;
					$ng_maybe[$p] += 1;
					continue;
				}
				$min_firmware = intval($val['sj_soft_file_min_firmware']);
				if ($min_firmware > 0 && $fid < $min_firmware) {
					if (!isset($ng_maybe[$p]))
						$ng_maybe[$p] = 0;
					$ng_maybe[$p] += 1;
					continue;
				}
			}
			$result = array();
			foreach ($ng_maybe as $p => $v) {
				if ($v == $ng_count[$p]) {
					$result[$p] = $id_to_package[$p];
				}
			}
			
			$cache_key = "SOFTLIST_NG_SOFTID_F${fid}_INTIME";
			$new_cache = $old_cache = getCache($cache_key);
			foreach($result as $k => $v){
				if (!isset($old_cache[$k])) {
					$new_cache[$k] = $v;
				}
			}
			if (count($new_cache) != count($old_cache)) {
				//file_put_contents('/tmp/sync_filter_cache.log', var_export($result, true), FILE_APPEND);
				writeCache($cache_key, $new_cache);
			}
		}
    }
}
