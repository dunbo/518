<?
if (!defined('IN_QIXIANG')) {
    header("HTTP/1.1 403 Forbidden");
    exit;
}

function getDataFromDB($start, $count) {
    $model = new GoModel();
    $option = array(
        'table' => 'sj_category',
        'field' => array('category_id', 'name'),
        'where' => array(
        'status' => 1,
        ),
        'index' => 'category_id',
    );
    $category = $model->findAll($option);
    $option = array(
        'table' => 'sj_soft',
        'where' => array(
        'hide' => 1,
        'status' => 1,
        ),
        'field' => array('softid', 'softname', 'package', 'version', 'category_id', 'costs', 'intro', 'score', 'msgnum'),
        'index' => 'softid',
    );
    if ($start >= 0 && $count > 0) {
        $option['offset'] = $start;
        $option['limit'] = $count;
    }
    $soft = $model->findAll($option);
    if (empty($soft))
        return array();
    $option = array(
        'table' => 'sj_soft_file',
        'where' => array(
        'softid' => array_keys($soft),
        'package_status' => 1,
        ),
        'field' => array('softid', 'min_firmware', 'iconurl'),
    );
    $soft_file_temp = $model->findAll($option);
    # sj_soft_file可能有多个值，取min_firmware最小的一个
    $soft_file = array();
    foreach ($soft_file_temp as $val) {
        $softid = $val['softid'];
        $min_firmware = $val['min_firmware'];
        if (isset($soft_file[$softid])) {
        $min_firmware_old = $soft_file[$softid]['min_firmware'];
        if ($min_firmware_old > $min_firmware) {
            $soft_file[$softid] = $val;
        }
        continue;
        }
        $soft_file[$softid] = $val;
    }
    $data = array();
    foreach ($soft as $softid => $val) {
        $single = array();
        $single['softid'] = $softid;
        $single['name'] = $val['softname'];
        $single['min_sdk'] = $soft_file[$softid]['min_firmware'];
        $single['icon_path'] = load_config('static_host') . $soft_file[$softid]['iconurl'];
        # 第一个类别
        $cid = explode(',', $val['category_id']);
        $cid = $cid[1];
        $single['type'] = $category[$cid]['name'];
        $single['package_name'] = $val['package'];
        $single['version'] = $val['version'];
        $single['rating'] = $val['score'];
        $single['rating_count'] = $val['msgnum'];
        $single['price'] = $val['costs'];
        $single['desc'] = $val['intro'];
        $data[] = $single;
    }
    return $data;
}

function memcacheSaveBig($key, $val, $expire = 0) {
	$cache = GoCache::getCacheAdapter('Memcached');
	$chunk = 128;
	$failed = false;
	while (true) {
		if ($chunk < 1)
			break;
		$subkey_arr = array();
		for ($i = 0; $i < count($val); $i += $chunk) {
			$subkey = "${key}_${i}";
			$subval = array_slice($val, $i, $chunk);
			if (!$cache->set($subkey, $subval, $expire)) {
				$failed = true;
				break;
			}
			$subkey_arr[] = $subkey;
		}
		# 减小chunk再试
		if ($failed) {
			foreach ($subkey_arr as $k)
				$cache->delete($k);
			$chunk /= 2;
			continue;
		}
		# 所有的子数据保存成功
		if ($cache->set($key, $subkey_arr, 0))
			return true;
		else {
			foreach ($subkey_arr as $k)
				$cache->delete($k);
			return false;
		}
	}
	return false;
}

function memcacheLoadBig($key) {
	$cache = GoCache::getCacheAdapter('Memcached');
	$subkey_arr = $cache->get($key);
	if (empty($subkey_arr))
		return false;
	$result = array();
	foreach ($subkey_arr as $k) {
		$val = $cache->get($k);
		if (empty($val))
			continue;
		$result = array_merge($result, $val);
	}
	return $result;
}

function getData($rebuild = false) {
    load_helper('mysqlcache');
	$key = 'MARKET_API_QIXIANG_DATA';
	$data = getMysqlCache($key);
	if (!$data || $rebuild) {
		$data = getDataFromDB(0, 0);
		setMysqlCache($key, $data, 0);
	}
	return $data;
}

