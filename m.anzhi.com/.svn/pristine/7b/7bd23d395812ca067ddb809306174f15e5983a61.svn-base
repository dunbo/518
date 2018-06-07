<?php
define('LOG', '/data/att/permanent_log/m.anzhi.com/');

//通过调用gomarket 的module 取得soft list, 并做整合与格式化
function get_softlist($action, $index_start = 0, $index_size = 15, $id = 0, $extra_parameters = array())
{
    ($index_start = (int)$index_start) >= 0? True : $index_start = 0;
    ($index_size = (int)$index_size) >= 0? True : $index_size = 10;
    $parameters = array(
        'LIST_INDEX_START' => $index_start,
        'LIST_INDEX_SIZE' => $index_size,
        'GET_COUNT' => True,
        'EXTRA_OPTION_FIELD' => array(
            'A.upload_tm',
            'B.min_firmware',
			'isoffice',
            'category_name',
        ),
        'PARENT_CAT_ID' => $id,
        'ID' => $id,
		'VR' => 1
    );
    if ($extra_parameters) {
        $parameters = array_merge($parameters, $extra_parameters);
    }
    if ( !($old_list   =   gomarket_action($action, $parameters))) {
        return False;
    }
	$more_field = array();
	$paramter_extra_option_field = $parameters['EXTRA_OPTION_FIELD'];
	if ($paramter_extra_option_field) {
		foreach ($paramter_extra_option_field as $v) {
			$v = ($fk = strrchr($v, '.'))? substr($fk, 1) : $v;
			$more_field[] = $v;
		}
	}
    $list_count =   $old_list['COUNT'];
    $old_list   =   $old_list['DATA'];
    $list = array();

    foreach ($old_list as $k => $v) {
        //原来为了兼容老的手机端应用, 所有的数据都是数字索引，在wap端不需要这样做， 转换为K-V形式
        $list[$k] = array( 'softid' => $v[0], 'iconurl' => $v[1], 'softname' => $v[2], 'score' => $v[3], 'msgnum' => $v[4], 'dev_name' => $v[5], 'costs' => $v[6], 'package' => $v[7], 'safe' => $v[8], 'filesize' => $v[9], 'category_id' => $v[10], 'total_downloaded' => $v[11], 'url' => $v[12], 'version_code' => $v[13]);
		//获取官方图标
		$n = count($v);
		$j = count($more_field);
		foreach ($more_field as $kk) {
				$list[$k][$kk] = $v[$n-$j];
				$j--;
		}
        $list[$k]['requirements'] = firmware2os($list[$k]['min_firmware']); //系统要求
        $list[$k]['down_url'] = 'download.php?softid='.$list[$k]['softid'];
        $cat_name = array();
        if ($cat_id_arr = explode(',', trim($list[$k]['category_id'], ','))) {
            foreach ($cat_id_arr as $cat_id) {
                $cat = get_category($cat_id);
                $cat_name[] = $cat['name'];
            }
        }
        $list[$k]['cat_name'] = implode(', ', $cat_name);
		$list[$k]['isoffice'] = $v['isoffice'];
    }
    $result = array(
        'list' => $list,
		'count' => $list_count,
        'list_page' => make_list_page($index_start, $index_size, $list_count)
    );
    return $result;
}
//获取专题区的数据
function get_featureid_softlist($extent_id,$offset=0,$limit=999,$unset_softid){
	//global $cacheObj;
	//$cache_key = 'cache_softlist_featureid'.$id;
/* 	if($result = $cacheobj -> get($cache_key)){
		$count = count($result);
		$result = array_slice($result,$offset,$limit);
		return array($result,$count);
	} */
	$softlist = load_model('softlist');
	$now = time();
	$option = array(
		'table' => 'sj_extent_soft AS A',
		'where' => array(
			'A.status' => 1,
			'A.start_at' => array('exp','<='.$now),
			'A.end_at' => array('exp','>'.$now),
			'A.extent_id' => $extent_id,
			'B.status' => 1,
			'B.hide' => 1,
		),
		'join' => array(
			'sj_soft AS B' => array(
				'on' => array('A.package','B.package'),
			)
		),
		'field' => 'B.softid as softid',
	);
	//$cache_time = 300;
	$softids = $softlist -> findAll($option);
	$softid_arr = array();

	foreach($softids as $info){
		if(in_array($info['softid'],$unset_softid)) continue;  //过滤不需要的软件
		$softid_arr[$info['softid']] = $info['softid'];
	}
	$softid_arr = $softlist -> filterSoftId($softid_arr,array(),false);
	$extra_option = array(
		'field' => array(
			'A.dev_name',
			'A.category_id',
			'A.safe',
			'A.version_code',
			'A.version',
			'A.upload_tm',
			'A.last_refresh',
		),
		'download_format' => 2
	);
	$filter_option = array();
	$res = $softlist->getSoftInfos($softid_arr, $filter_option, $extra_option);
	foreach ($softid_arr as $k => $softid) {
		$val = $res[$softid];
		$result[$k] = $val;
		$result[$k]['iconurl'] = $val['iconurl'] ? getIconHost() . $val['iconurl'] : '';
		$result[$k]['url'] = getApkHost($val). $val['url'];
		$result[$k]['upload_tm'] = date('Y-m-d', $val['last_refresh'] ? $val['last_refresh'] : $val['upload_tm']);
        $result[$k]['requirements'] = firmware2os($result[$k]['min_firmware']); //系统要求
        $result[$k]['down_url'] = 'download.php?softid='.$result[$k]['softid'];
        $cat_name = array();
        if ($cat_id_arr = explode(',', trim($result[$k]['category_id'], ','))) {
            foreach ($cat_id_arr as $cat_id) {
                $cat = get_category($cat_id);
                $cat_name[] = $cat['name'];
            }
        }
        $result[$k]['cat_name'] = implode(', ', $cat_name);
	}
/* 	$cacheObj->set($cache_key, $result, 86400); */
	$count = count($result);
	$result = array_slice($result,$offset,$limit);
	//$list_page = make_list_page($index_start, $limit, $count);
	return array($result,$count);
}

//获取专题区的数据
function get_featureid_softlist1($extent_id,$offset=0,$limit=999,$unset_softid){
	//global $cacheObj;
	//$cache_key = 'cache_softlist_featureid'.$id;
/* 	if($result = $cacheobj -> get($cache_key)){
		$count = count($result);
		$result = array_slice($result,$offset,$limit);
		return array($result,$count);
	} */
	$softlist = load_model('softlist');
	$now = time();
	$option = array(
		'table' => 'sj_extent_soft AS A',
		'where' => array(
			'A.status' => 1,
			'A.start_at' => array('exp','<='.$now),
			'A.end_at' => array('exp','>'.$now),
			'A.extent_id' => $extent_id,
			'B.status' => 1,
			'B.hide' => 1,
		),
		'join' => array(
			'sj_soft AS B' => array(
				'on' => array('A.package','B.package'),
			)
		),
		'field' => 'B.softid as softid',
	);
	//$cache_time = 300;
	$softids = $softlist -> findAll($option);
	$softid_arr = array();

	foreach($softids as $info){
		if(in_array($info['softid'],$unset_softid)) continue;  //过滤不需要的软件
		$softid_arr[$info['softid']] = $info['softid'];
	}
	$softid_arr = $softlist -> filterSoftId($softid_arr,array(),false);
	$extra_option = array(
		'field' => array(
			'A.dev_name',
			'A.category_id',
			'A.safe',
			'A.version_code',
			'A.version',
			'A.upload_tm',
			'A.last_refresh',
		)
	);
	$filter_option = array();
	$res = $softlist->getSoftInfos($softid_arr, $filter_option, $extra_option);
	foreach ($softid_arr as $k => $softid) {
		$val = $res[$softid];
		$result[$k] = $val;
		$result[$k]['iconurl'] = $val['iconurl'] ? getIconHost() . $val['iconurl'] : '';
		$result[$k]['url'] = getApkHost($val). $val['url'];
		$result[$k]['upload_tm'] = date('Y-m-d', $val['last_refresh'] ? $val['last_refresh'] : $val['upload_tm']);
        $result[$k]['requirements'] = firmware2os($result[$k]['min_firmware']); //系统要求
        $result[$k]['down_url'] = 'download.php?softid='.$result[$k]['softid'];
        $cat_name = array();
        if ($cat_id_arr = explode(',', trim($result[$k]['category_id'], ','))) {
            foreach ($cat_id_arr as $cat_id) {
                $cat = get_category($cat_id);
                $cat_name[] = $cat['name'];
            }
        }
        $result[$k]['cat_name'] = implode(', ', $cat_name);
	}
/* 	$cacheObj->set($cache_key, $result, 86400); */
	$count = count($result);
	$result = array_slice($result,$offset,$limit);
	//$list_page = make_list_page($index_start, $limit, $count);
	return array($result,$count);
}
//调用gomarket 的module
function gomarket_action($module_action, $parameters)
{
    list($module, $action) = explode('.', $module_action);
    $action_file = GO_APP_ROOT. DS. 'modules' . DS. strtolower($module). DS. $action. '.php';
    if (!file_exists($action_file)) {
        return False;
    }
    include_once $action_file;
    if (!class_exists($action)) {
        return False;
    }
    $actionClass = new $action;
    $actionClass->parameters = $parameters;
    return $actionClass->execute();
}
//重写GoAction的getParameters 改为手动指定传输参数
abstract class GoAction
{
	abstract public function execute();
	public function getParameter($key = null, $default = '')
	{
        $parameter = isset($this->parameters[$key])? $this->parameters[$key] : $default;
        return $parameter;
    }
}

//取得单个分类的信息
function get_category($cat_id)
{
    if (!$cat_id) {
        return False;
    }
    static $category;
    global $cacheObj;
    $cache_key = 'wap_category';
    $category? True : $category = $cacheObj->get($cache_key);
    if (!$category) {
        $categoryObj = load_model('category');
        $option = array(
        	'where' => array('status' => 1),
            'field' => array('category_id', 'parentid', 'name'),
            'index' => 'category_id',
        );
        $category = $categoryObj->findAll($option);
        $cacheObj->set($cache_key, $category, 86400);
    }
    return $category[$cat_id];
}

//取得分类列表
function get_sub_category($action, $parent_cat_id, $index_start = 0, $index_size = 100)
{
    ($index_start = (int)$index_start) >= 0? True : $index_start = 0;
    ($index_size = (int)$index_size) >= 0? True : $index_size = 10;
    $parameters = array(
        'LIST_INDEX_START' => $index_start,
        'LIST_INDEX_SIZE' => $index_size,
        'ID' => $parent_cat_id,
        'GET_COUNT' => True,
    );
    $old_list = gomarket_action($action, $parameters);
    $list_count =   $old_list['COUNT'];
    $old_list   =   $old_list['DATA'];
    $list = array();
    foreach ($old_list as $k => $v) {
       //原来为了兼容老的手机端应用, 所有的数据都是数字索引，在wap端不需要这样做， 转换为K-V形式
        $list[$k] = array('category_id' => $v[4], 'name' => $v[5], 'soft_num' => $v[6] + $v[7]);
    }
    $result = array(
        'list' => $list,
        'list_page' => make_list_page($index_start, $index_size, $list_count),
    );
    return $result;
}

//分页
function make_list_page($index_start, $index_size, $list_count)
{
    if (!$list_count) {
        return False;
    }
    $pre_url = '';
    $next_url = '';
    $this_page = ceil(($index_start + 1) / $index_size);
    $all_page = ceil($list_count / $index_size);
    $require_uri = strstr($_SERVER['REQUEST_URI'], '?')? $_SERVER['REQUEST_URI'] : $_SERVER['REQUEST_URI'].'?';
    if ($index_start != 0) {
        ($pre_index_start = $index_start - $index_size) > 0? True: $pre_index_start = 0;
        $pre_url = isset($_GET['index_start'])? preg_replace('#index_start=[0-9]+#si', 'index_start='.$pre_index_start, $require_uri) : $require_uri.'&index_start='. $pre_index_start;
   }
    if ($this_page < $all_page) {
        ($next_index_start = $index_start + $index_size) > 0? True: $next_index_start = 0;
        $next_url = isset($_GET['index_start'])? preg_replace('#index_start=[0-9]+#si', 'index_start='.$next_index_start, $require_uri) : $require_uri.'&index_start='. $next_index_start;
	}
    return array(
        'count' => $list_count,
        'this_page' => $this_page,
        'all_page' =>  $all_page,
        'pre_url' => $pre_url,
        'next_url' => $next_url,
    );
}

//取得subject列表
function get_subject($action, $index_start = 0, $index_size = 8)
{
    ($index_start = (int)$index_start) >= 0? True : $index_start = 0;
    ($index_size = (int)$index_size) >= 0? True : $index_size = 10;
    $parameters = array(
        'LIST_INDEX_START' => $index_start,
        'LIST_INDEX_SIZE' => $index_size,
        'GET_COUNT' => True,
    );
    $old_list = gomarket_action($action, $parameters);
    $list_count =   $old_list['COUNT'];
    $old_list   =   $old_list['DATA'];
    $list = array();
    foreach ($old_list as $k => $v) {
        //原来为了兼容老的手机端应用, 所有的数据都是数字索引，在wap端不需要这样做， 转换为K-V形式
        $list[$k] = array('subject_id' => $v[1], 'name' => $v[2], 'soft_num' => $v[3] + $v[4]);
    }
    $result = array(
        'list' => $list,
        'list_page' => make_list_page($index_start, $index_size, $list_count),
    );
    return $result;
}

//通过ua 匹配机型
function ua2device($ua)
{
    preg_match('#(zh[_\-]cn);(.*?)(;|Build)#si', $_SERVER['HTTP_USER_AGENT'], $device);
    return trim($device[2]);
}

function isIpBanned($ip, $softid, $threshold = 50)
{
	// 单ip单软件每天最大下载量
	global $cacheObj;
	$day = date('Ymd');
	$cache_key = "wap:download:{$ip}:{$day}";
	$soft_list = $cacheObj->get($cache_key);
	if (!array_key_exists($softid, $soft_list))
	{
		$soft_list[$softid] = 0;
	}
	if ($soft_list[$softid] >= $threshold)
	{
		return True;
	}
	$soft_list[$softid] = $soft_list[$softid] + 1;
	$cacheObj->set($cache_key, $soft_list, 86400);
	return False;
}

function get_route_rule(){
	$parten_arr = array(
		array('/^(\/?)$/', '/index.html'),

		array('/^(\/?)activity\.php/', '/activity_{$id}.html'),
		array('/^(\/?)activate\.php/', '/activate_{$id}.html'),

		array('/^(\/?)index\.php/', '/index_{$type}_{$morelist}.html'),
		array('/^(\/?)index\.php/', '/index_{$type}.html'),
		array('/^(\/?)index\.php/', '/index_{$morelist}.html'),
		array('/^(\/?)index\.php/', '/index.html'),

		array('/^(\/?)inapp\.php/', '/necessary_{$morelist}.html'),
		array('/^(\/?)inapp\.php/', '/necessary.html'),

		array('/^(\/?)app\.php/', '/{$type}_{$sub_cat_id}_{$order}_{$morelist}.html'),
		array('/^(\/?)app\.php/', '/{$type}_{$sub_cat_id}_{$order}.html'),
		array('/^(\/?)app\.php/', '/{$type}_{$parent_cat_id}_{$morelist}.html'),
		array('/^(\/?)app\.php/', '/{$type}_{$parent_cat_id}.html'),
        array('/^(\/?)app\.php/', '/{$type}_{$softid}_{$morelist}.html'),
		array('/^(\/?)app\.php/', '/{$type}_{$softid}.html'),
		array('/^(\/?)app\.php/', '/{$type}_{$hanhua_id}_{$morelist}.html'),
		array('/^(\/?)app\.php/', '/{$type}_{$hanhua_id}.html'),

		array('/^(\/?)subject\.php/', '/subjectapp_{$subject_id}.html'),
		array('/^(\/?)subject\.php/', '/subject.html'),

		array('/^(\/?)activate\.php/', '/activateapp_{$activate_id}.html'),
		array('/^(\/?)activity\.php/', '/activityapp_{$activity_id}.html'),
		array('/^(\/?)anzhiapk\.php/', '/anzhiapk_{$type}.html'),
		array('/^(\/?)anzhiapk\.php/', '/anzhiapk.html'),
		
		array('/^(\/?)perfect\.php\?method=history&ajax/', '/pfhisjson_{$page}.html'),
		array('/^(\/?)perfect\.php\?method=history/', '/perfect_history.html'),
		array('/^(\/?)perfect\.php\?method=comment&ajax/', '/pfcomjson_{$id}_{$page}.html'),
		array('/^(\/?)perfect\.php\?method=comment/', '/perfect_comment_{$id}.html'),
		array('/^(\/?)perfect\.php\?id/', '/perfect_{$id}.html'),
		
	);
	return $parten_arr;
}
function url2static_url($url, $return_cache_path = false){
	$static_domain = array(
        '118.26.203.23' => 1,
        'm.anzhi.com' => 1,
        'bj.anzhi.com' => 1,
        'icity.anzhi.com' => 1,
	);

	$url_info = parse_url($url);
	$url_path = $url_info['path'];

	$p = array();
	if ($url_info['query']) {
		parse_str($url_info['query'], $p);
	}

	$static_url = '';
	$concise = CONCISE;
	if (isset($p['concise'])) {
		$concise = $p['concise'];
	} elseif (isset($_GET['concise'])) {
        $concise = $_GET['concise'];
        $p['concise'] = $_GET['concise'];
    }
	if (defined('CHANNEL') && preg_match('/[a-z0-9_]+/i', CHANNEL)) {
		$channel = CHANNEL;
	}
	if (isset($p['channel'])) {
		$channel = $p['channel'];
	}

	if ($channel == 'm') {
		if ($concise == 1) {
			$static_url .= '/concise';
		}
	} elseif ($channel) {
		$static_url .= '/'. $channel;

		if ($concise == 1) {
			$static_url .= '_concise';
		}
	}
	unset($p['channel']);
	$n = count($p);
	$new_url = $url;
	if ($n > 0) {
		$query_string = http_build_query($p);
        $new_url = $url_path. '?'. $query_string;
	}

    if (!isset($static_domain[strtolower($_SERVER['HTTP_HOST'])])) return $new_url;

	$parten_arr = get_route_rule();
	$has_rule = false;
	foreach ($parten_arr as $val) {
		//匹配动态文件名规则
		if (preg_match($val[0], $url)) {
			$tmp_url = $static_url;
			$tmp_url .= $val[1];
			//处理变量{$xxx}
			$has_rule = true;
			if (preg_match_all('/\{\$([0-9a-z_]+)\}/', $val[1], $m)) {
				foreach ($m[1] as $var_name) {
					$var_value = '';
					$var_config = isset($val[2][$var_name]) ? $val[2][$var_name] : array();
					if (isset($p[$var_name])) {//http query中已经设定了变量值
						$var_value = $p[$var_name];
					} elseif (isset($var_config[0])) { //配置中指定了默认值
						$var_value = $var_config[0];
					}
					if (isset($var_config[1])) { //存在变量值到显示名称的映射
						$var_value = $var_config[1][$var_value];
					}
					if ($var_value == '') {
						$has_rule = false;
						break; //如果指定的变量值不存在，则跳出
					}

					$tmp_url = str_replace("{\${$var_name}}", $var_value, $tmp_url);
				}
			}
			if ($has_rule) {
				$static_url = $tmp_url;
				break;
			}
		}
	}

	if ($has_rule) {
		return $static_url;
	} else {
		return $new_url;
	}
}


function url2static_file($url){
	return false;
}


function  imgurl_trans($params,$url){
	if(!defined('IMGURL_TRANS') || IMGURL_TRANS != 1) return $url;
	$pattner = "/^http:\/\/([a-z0-9\.]+)\/([a-z0-9_\.\/]+)$/i";
	preg_match($pattner,$url,$match);
	$doman_host = $match[1];
	$path = $match[2];
	$file_arr = explode('/',$path);
	$pos = count($file_arr) - 1;
	$filename = $file_arr[$pos];
	$filename = imageurl_parse($filename);
	$file_arr[$pos] = $filename;
	$path = base64_encode(implode('|',$file_arr));
	$avatar = '/icon.php?u='.$path;
	return $avatar;
}

//取指定小数位
function numf($number, $unit, $precision=0) {
	$number = $number / $unit;
	if ($precision > 0) $precision += 1;
	$p = stripos($number, '.'); 
	$size = $p ? substr($number,0,$p+$precision) : $number;
	$size = (float) $size;
	return $size;
}

//修改数据单位
function  formatFileSize($params,$len) {
		$keepZero=1;
        #DecimalFormat formatKeepTwoZero = new DecimalFormat(keepDecimal ? "#.00" : "#");
        #DecimalFormat formatKeepOneZero = new DecimalFormat(keepDecimal ? "#.0" : "#");
	$KB = 1024;
	$MB = 1024 * 1024;
	$GB = 1024 * 1024 * 1024;
	if ($len < 1024) {
		$size = $len . "B";
	} else if ($len < 10 * 1024) {
		// [0, 10KB)，保留两位小数
		$size = numf($len, $KB, 2)  . "K";
	} else if ($len < 100 * 1024) {
		// [10KB, 100KB)，保留一位小数
		$size = numf($len, $KB, 1)  . "K";
	} else if ($len < 1024 * 1024) {
		// [100KB, 1MB)，个位四舍五入
		$size = numf($len, $KB) . "K";
	} else if ($len < 10 * 1024 * 1024) {
		// [1MB, 10MB)，保留两位小数
		if ($keepZero) {
			$size = numf($len, $MB, 2)  . "M";
		} else {
			$size = numf($len, $MB)  . "M";
		}
	} else if ($len < 100 * 1024 * 1024) {
		// [10MB, 100MB)，保留一位小数
		if ($keepZero) {
			$size = numf($len, $MB, 1) . "M";
		} else {
			$size = numf($len, $MB)  . "M";
		}
	} else if ($len < 1024 * 1024 * 1024) {
		// [100MB, 1GB)，个位四舍五入
		$size = numf($len, $MB) . "M";
	} else {
		// [1GB, ...)，保留两位小数
		$size = numf($len, $GB, 2). "G";
	}
	return $size;
}

//session 处理方法
function session_begin($sid){
	if($_GET['platform'] && $_GET['platform'] == 13){
		$platform = $_GET['platform'];
		$sid = $_GET['sid'];
		$imsi = $_GET['imsi'];
		$imei = $_GET['imei'];
		$device_id = $_GET['device_id'];
		$mac = $_GET['mac'];
		$osver = $_GET['sdk_int'];
		$screen = $_GET['resolution'];
		$abi = $_GET['abi'];
		$firmware = $_GET['firmware'];
		$ip = $_SERVER['REMOTE_ADDR'];
		load_helper("ucenter");
		$device_arr = array(
			'deviceid'=>$deviceid,
			'osver'=>$osver,
			'screen'=>$screen,
			'imsi'=>$imsi,
			'mac'=>$mac,
			'ip'=>$ip,
			'abi'=>$abi
		);
		$uc_result = uc_getUserinfoBySid($sid, $device_arr);
		$message = array(
			'sid' => $sid,
			'imsi' => $imsi,
			'imei' => $imei,
			'device_id' => $device_id,
			'mac' => $mac,
			'osver' => $osver,
			'screen' => $screen,
			'abi' => $abi,
			'platform' => $platform,
			'uc_result' => $uc_result
		);
	}else{
		if(empty($sid)){
			$sid = $_GET['sid'];
		}
		if($sid && eregi('[0-9a-zA-z]', $sid) && strlen($sid) == 32){
				session_id($sid);
		}
		session_start();
		$imsi = $_SESSION['USER_IMSI'];
		$imei = $_SESSION['USER_IMEI'];
		$device_id = $_SESSION['DEVICEID'];
		$alone_update = $_SESSION['alone_update'];
		$version_code = $_SESSION['VERSION_CODE'];
		$message = array(
			'sid' => $sid,
			'imsi' => $imsi,
			'imei' => $imei,
			'device_id' => $device_id,
			'alone_update' => $alone_update,
			'version_code' => $version_code
		);
	}
	return $message;
}
//获取KEY值
function get_key($key)
{
	$sid=$_GET['sid'];
	if($sid)
	{
		return;
	}
	else
	{
		$config = load_config('lottery_cache/redis','lottery');
		if ($config) {
			$redis = new GoRedisCacheAdapter($config);
		} else {
			$redis = GoCache::getCacheAdapter('redis');
		}
		$actsid=$_COOKIE['actsid'];
		if(!$actsid)
		{
			$actsid=$_GET['actsid'];
			if(!$actsid)
			{
				$actsid = md5(time().mt_rand().mt_rand().$_SERVER['REMOTE_ADDR']);
				setcookie("actsid", $actsid);
			}
		}
		return $actsid;
	}
}


function httpGetInfo($url,$host,$vals,$log_file) {
	$res = curl_init();
	curl_setopt($res, CURLOPT_URL, $url);
	curl_setopt($res, CURLOPT_TIMEOUT, 15);//超时时间
	if($host){
		curl_setopt($res, CURLOPT_HTTPHEADER, array($host, 'Expect:'));	
	}
	curl_setopt($res, CURLOPT_POST, true);
	curl_setopt($res, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($res, CURLOPT_POSTFIELDS, $vals);
	$result = curl_exec($res);
	$http_code = curl_getinfo($res, CURLINFO_HTTP_CODE);
	$errno = curl_errno($res);
	$error = curl_error($res);
	curl_close($res);	
	return $result;
}

