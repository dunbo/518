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
    $redis =  GoCache::getCacheAdapter('redis');
    $key = $ip.":wap:".date("Ymd");
    $soft_num = $redis->gethash($key, $softid);
    if (!$soft_num)
    {
        $soft_num = 0;
    }

    if ($soft_num >= $threshold)
    {
        return True;;
    }
    $soft_num = $soft_num + 1;
    $r = $redis->sethash($key, array($softid=>$soft_num), 86400);

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
		array('/^(\/?)package\.php/', '/pkg/{$pkg}'),
		array('/^(\/?)inapp\.php/', '/necessary_{$morelist}.html'),
		array('/^(\/?)inapp\.php/', '/necessary.html'),

		array('/^(\/?)app\.php/', '/{$type}_{$sub_tag_id}_{$sub_cat_id}_{$order}_{$morelist}.html'),
		array('/^(\/?)app\.php/', '/{$type}_{$sub_tag_id}_{$sub_cat_id}_{$order}.html'),
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
    if (preg_match('#^https?://#i', $url)) {
        return $url;
    }
	$static_domain = array(
        '118.26.203.23' => 1,
        'm.anzhi.com' => 1,
        'm.test.anzhi.com' => 1,
        'bj.anzhi.com' => 1,
        'icity.anzhi.com' => 1,
        'fx.anzhi.com' => 1,
	);

	$url_info = parse_url($url);
	$url_path = $url_info['path'];
	$p = array();
	if ($url_info['query']) {
		parse_str($url_info['query'], $p);
	}
	$type = $p['type'];
	$softid = $p['softid'];
	static $allpkg;	
	if($url_path =="app.php" && $type == "info" && empty($allpkg[$softid])){	
		$softlist_model = load_model('softlist');
		$soft_file_data = $softlist_model -> getSoftInfos($softid);
		if($soft_file_data[$softid]['package']){
			$allpkg[$softid]['pkg'] = $soft_file_data[$softid]['package'];
			$allpkg[$softid]['md5'] = substr(md5($soft_file_data[$softid]['package']),0,4);
		}
	} 
	//file_put_contents("/tmp/zhuang.log",var_export($allpkg,true),FILE_APPEND);
	//http拆分响应漏洞
	foreach ($p as $k => $v) {
        $v = strip_tags($v, '');
        $v = str_replace("\r\n", '', $v);
        $v = str_replace("\n", '', $v);
        $p[$k] = $v;
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
					if($channel == 'm' && $concise != 1 && $url_path =="app.php" && $type == "info" && $softid){
						$tmp_url = "app_".$allpkg[$var_value]['md5']."_".$allpkg[$var_value]['pkg'].".html";						
					}else{
						$tmp_url = str_replace("{\${$var_name}}", $var_value, $tmp_url);
					}
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
    if(empty($sid)){
        $sid = $_GET['sid'];
    }
    //if($_GET['sid'] && eregi('[0-9a-zA-z]', $_GET['sid']) && strlen($_GET['sid']) == 32){
    //if($sid && eregi('[0-9a-zA-z]', $sid) && strlen($sid) == 32){
    if($sid && preg_match('/[0-9a-zA-z]/', $sid) && strlen($sid) == 32){
            session_id($sid);
    }
    @session_start();
}

//获取KEY值，prefix用来区分不同活动间的actsid
function get_key($prefix)
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
		$prefix = empty($prefix) ? '' : $prefix;
		$cookie_name = "{$prefix}:actsid";
		$actsid = $_COOKIE[$cookie_name];
		$get_actsid = $_GET['actsid'];
		if (!empty($get_actsid) && $actsid != $get_actsid) {
			// 以get的actsid为准
			$actsid = $get_actsid;
			setcookie($cookie_name, $actsid, time()+3600*24*60);//2个月
		}
		if(!$actsid) {
			// 需要重新生成
			$actsid = md5(time().mt_rand().mt_rand().$_SERVER['REMOTE_ADDR']);
			setcookie($cookie_name, $actsid, time()+3600*24*60);//2个月
		}
		return $actsid;
	}
}

function user_loging_new(){
	if(!isset($_SESSION['USER_UID']) || $_SESSION['USER_ID'] == 13176){
		if (!empty($_COOKIE['_AZ_COOKIE_'])) {
			$ucenter = new GoUcenter('www');
			$cookie_data = $ucenter->parse_uc_cookie();
			$user = $ucenter->token_userinfo();
			if ($_SESSION['USER_ID'] != $cookie_data['pid']) {
				if (isset($user['USER_ID']) && $user['USER_ID']!=13176 && isset($user['USER_NAME'])) {
					$_SESSION['USER_ID'] = $user['USER_ID'];
					$_SESSION['USER_UID'] = $user['USER_UID'];
					$_SESSION['USER_NAME'] = $user['USER_NAME'];
					$_SESSION['EMAIL'] = $user['EMAIL'];
					$_SESSION['MOBILE'] = $user['MOBILE'];
				}
			}else{
				if (isset($cookie_data['pid']) && $user['pid']!=13176 && isset($cookie_data['loginAccount'])) {
					$_SESSION['USER_ID'] = $cookie_data['pid'];
					$_SESSION['USER_UID'] = $cookie_data['uid'];
					$_SESSION['USER_NAME'] = $cookie_data['loginAccount'];	
					$_SESSION['EMAIL'] = $user['EMAIL'];
					$_SESSION['MOBILE'] = $user['MOBILE'];
				}				
			}
		}
	}
	//端上和web同步的登出的时候要清cookie	
	setcookie('_AZ_COOKIE_', '', time()-31536000, '/', 'anzhi.com');
	setcookie('_AZ_COOKIE_KEY', '', time()-31536000, '/', 'anzhi.com');
}
//兼容端外可区别注册和登录 
function user_loging_reserve(){
	if(!isset($_SESSION['USER_UID']) || $_SESSION['USER_ID'] == 13176){
		if (!empty($_COOKIE['_AZ_COOKIE_'])) 
		{
			$ucenter = new GoUcenter('activity');
			$cookie_data = $ucenter->parse_uc_cookie();
			$user = $ucenter->token_userinfo();
			if ($_SESSION['USER_ID'] != $cookie_data['pid']) 
			{
				if (isset($user['USER_ID']) && $user['USER_ID']!=13176 && isset($user['USER_NAME'])) 
				{
					$_SESSION['USER_ID'] = $user['USER_ID'];
					$_SESSION['USER_UID'] = $user['USER_UID'];
					$_SESSION['USER_NAME'] = $user['USER_NAME'];
				}
				else
				{
					$_SESSION['USER_ID'] = $cookie_data['pid'];
					$_SESSION['USER_UID'] = $cookie_data['uid'];
					$_SESSION['USER_NAME'] = $cookie_data['loginAccount'];	
				}
			}
			else
			{
				if (isset($cookie_data['pid']) && $user['pid']!=13176 && isset($cookie_data['loginAccount'])) 
				{
					$_SESSION['USER_ID'] = $cookie_data['pid'];
					$_SESSION['USER_UID'] = $cookie_data['uid'];
					$_SESSION['USER_NAME'] = $cookie_data['loginAccount'];	
				}			
			}
			if($cookie_data['memo'])//端外新增加的参数
			{
				$arr_param = json_decode($cookie_data['memo'],true);
				$_SESSION['REVE_TYPE'] = $arr_param['loginInterface'];//loginInterface：值为login是登录 register是注册
				$_SESSION['DEVICEID'] = $arr_param['deviceId'];//能获取到参数  但是该参数值为空
			}
		}
	}	
	setcookie('_AZ_COOKIE_', '', time()-31536000, '/', 'anzhi.com');
	setcookie('_AZ_COOKIE_KEY', '', time()-31536000, '/', 'anzhi.com');
}


function str_replace_cn_new($str, $start, $enlengthd ){  
	 if(preg_match("/[\x7f-\xff]/", $str)){  
		if(is_utf8_new($str)){  
			return substr_replace($str,'***',$start*3, -1*3);  
		}else{  
			return substr_replace($str,'***',$start*2, -1*2);  
		}  
	 }else{  
		return substr_replace($str,'***',$start*2, $enlengthd);  
	 }  
}
  
function is_utf8_new($word){   
     if(preg_match("/^([".chr(228)."-".chr(233)."]{1}[".chr(128)."-".chr(191)."]{1}[".chr(128)."-".chr(191)."]{1}){1}/",$word) == true || preg_match("/([".chr(228)."-".chr(233)."]{1}[".chr(128)."-".chr(191)."]{1}[".chr(128)."-".chr(191)."]{1}){1}$/",$word) == true || preg_match("/([".chr(228)."-".chr(233)."]{1}[".chr(128)."-".chr(191)."]{1}[".chr(128)."-".chr(191)."]{1}){2,}/",$word) == true) {   
      return true;   
     }else {   
      return false;   
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
	if($log_file){
		permanentlog($log_file,date('Y-m-d H:i:s')."|"."{$http_code}|{$errno}|{$error}\n".$url."\n". print_r($vals, true) . "\n" . print_r($result, true) . "\n\n");
	}
	return $result;
}
//用户我的奖品--实物
function get_user_kind_award_new($uid,$aid,$prefix,$table){
	list($redis,$model) = load_config_redis();	
	$kind_award_list = $redis -> getlist("{$prefix}:{$aid}_draw_award:{$uid}");
	if(!$kind_award_list){
		$option = array(
			'where' => array(
				'uid' => $uid,
				'aid' => $aid,
			),
			'table' => $table,
			//'field' => 'id,aid,uid,username,pid,prizename,create_tm',
		);
		$kind_award = $model->findAll($option,'lottery/lottery');	
		if(!$kind_award) return false;
		$kind_award_list = array();
		foreach((array)$kind_award as $k => $v){
			$kind_award_list[$v['id']] = $v;
			$kind_award_list[$v['id']]['time'] = date("Y-m-d",$v['create_tm']);
		}
		unset($kind_award);
		$redis -> setlist("{$prefix}:{$aid}_draw_award:{$uid}",$kind_award_list,30*60);
	}else{
		foreach($kind_award_list as $k => $v){
			$kind_award_list[$k] = json_decode($v,true);
		}		
	}	
	return $kind_award_list;
}
//用户我的奖品--礼包
function get_user_kind_gift_new($uid,$aid,$prefix,$table){
	list($redis,$model) = load_config_redis();	
	$gift_prize_list = $redis -> getlist("{$prefix}:{$aid}_gift_prize:{$uid}");
	if(!$gift_prize_list){
		$option = array(
			'where' => array(
				'status' => 1,
				'uid' => $uid,
				'aid' => $aid,				
			),
			'table' => $table,
			'field' => 'gift_number,uid,package,softname,update_tm',
		);
		$kind_gift = $model->findAll($option,'lottery/lottery');	
		if(!$kind_gift) return false;
		$gift_prize_list = array();
		foreach((array)$kind_gift as $k => $v){
			$gift_prize_list[$v['gift_number']] = $v;
			$gift_prize_list[$v['gift_number']]['time'] = date("Y-m-d",$v['update_tm']);
		}
		$redis -> setlist("{$prefix}:{$aid}_gift_prize:{$uid}",$gift_prize_list,30*60);
		unset($kind_gift);
	}else{
		foreach($gift_prize_list as $k => $v){
			$gift_prize_list[$k] = json_decode($v,true);
		}		
	}
	return $gift_prize_list;
	
}
//用户信息获取
function get_user_info_new($uid,$aid,$prefix,$table){
	list($redis,$model) = load_config_redis();
	$userinfo = $redis->get("{$prefix}:{$aid}_userinfo:".$uid);
	if($userinfo === null){
		$option = array(
			'where' => array(
				'uid' => $uid,
				'aid' => $aid
			),
			'table' => $table,
		);
		$userinfo = $model->findOne($option,'lottery/lottery');	
		if($userinfo){	
			$redis->set("{$prefix}:{$aid}_userinfo:".$uid,$userinfo,10*86400);
		}
	}	
	return $userinfo;			
}
//添加用户、修改用户信息
function add_user_new($data,$time,$prefix,$table){
	list($redis,$model) = load_config_redis();
	$option = array(
		'where' => array(
			'uid' => $data['uid'],
			'aid' => $data['aid']
		),
		'table' => $table,
	);
	$userinfo = $model->findOne($option,'lottery/lottery');	
    if($userinfo){
        $new_data = array(
			'uid' => $data['uid'],
			'username' => $_SESSION['USER_NAME'],
			'phone' => $data['phone'] ? $data['phone'] : $userinfo['phone'] ,
			'contact_name' => $data['contact_name'] ? $data['contact_name'] : $userinfo['contact_name'],
			'address' => $data['address'] ? $data['address'] : $userinfo['address'],
			'update_tm' => $time,
			'__user_table' => $table
        );
        $where = array(
			'uid' => $data['uid'],
			'aid' =>$data['aid'],
        );
        $ret =  $model->update($where, $new_data,'lottery/lottery');
    }else {//新增
        $new_data = array(
			'uid' => $data['uid'],
			'aid' => $data['aid'],
			'username' => $_SESSION['USER_NAME'],
			'phone' => $data['phone'] ? $data['phone'] : '' ,
			'contact_name' => $data['contact_name'] ? $data['contact_name'] : '',
			'address' => $data['address'] ? $data['address'] :'',
			'update_tm' => $time,
			'create_tm' => $time,
			'os_from' => 2,
			'__user_table' => $table
        );	
		if($data['deduction_num'])  $new_data['deduction_num'] = $data['deduction_num'];
        $ret =  $model->insert($new_data,'lottery/lottery');
    }
    $redis->set("{$prefix}:{$data['aid']}_userinfo:".$data['uid'],$new_data,86400*10);
	return 	$ret;
}
//用户已用抽奖次数
function save_deduction_num_new($uid,$aid,$user_name,$prefix,$table){
	list($redis,$model) = load_config_redis();		
	$time = time();
	$where = array(
		'uid' => $uid,
		'aid' => $aid 
	);
	$res = $redis->get("{$prefix}:{$aid}_userinfo:".$uid);
	if($res){
		$data_update = array(
			'deduction_num' => array('exp',"`deduction_num`+1"),
			'update_tm' => $time,
			'username' => $user_name ? $user_name : $_SESSION['USER_NAME'],
			'__user_table' => $table
		);
		return $model -> update($where,$data_update,'lottery/lottery');			
	}else{
		$data = array(
			'uid' => $uid,
			'aid' => $aid,
			'username' => $user_name ? $user_name : $_SESSION['USER_NAME'],
			'deduction_num' => 1,
		);
		$ret = add_user_new($data,$time,$prefix,$table);
		return $ret;
	}
}
//所有实物中奖信息
function get_award_all_new($aid,$prefix,$table,$where =''){
	list($redis,$model) = load_config_redis();		
	$award_list = $redis -> getlist("{$prefix}:{$aid}_draw_award");
	if(!$award_list){
		$limit = 1000;
		$start = 0;
		if($where == ''){
			$where = array(
				'status' => 1,
				'aid' => $aid,
			);
		}
		for($start=0;;$start++){
			$option = array(
				'where' => $where,
				'limit' => $limit,
				'offset' => $start*$limit,				
				'table' => $table,
				'field' => 'username,prizename',
			);
			$kind_award = $model->findAll($option,'lottery/lottery');	
			if(!$kind_award) break;
			$award_list = array();
			foreach((array)$kind_award as $k => $v){
				$award_list[$k]['username'] = str_replace_cn_new($v['username'], 1, -2 );
				$award_list[$k]['prizename'] = $v['prizename'];
			}
			unset($kind_award);
			$redis -> setlist("{$prefix}:{$aid}_draw_award",$award_list,30*60);
		}
	}else{
		foreach($award_list as $k => $v){
			$award_list[$k] = json_decode($v,true);
		}	
	}	
	return $award_list;
}
//获取礼包礼券id
function get_virtual_pid($aid,$prefix,$table){
	list($redis,$model) = load_config_redis();				
	$virtual_pid_key = "{$prefix}:{$aid}:virtual_pid";
	$virtual_pid = $redis->get($virtual_pid_key);
	if(!$virtual_pid){
		$option = array(
			'where' => array(
				'aid' => $aid,
				'type' => array(4,5)
			),
			'table' => $table,
			'field' => 'id',
			'cache_time' => 30*60
		);
		$pid_arr = $model->findAll($option,'lottery/lottery');	
		$virtual_pid_arr = array();
		foreach($pid_arr as $k => $v){
			$virtual_pid_arr[] = $v['id'];
		}
		$virtual_pid = implode(",",$virtual_pid_arr);
		$redis->set($virtual_pid_key,$virtual_pid,86400);
	}
	return 	$virtual_pid;
}
//a.优先抽中已安装游戏的虚拟礼包；
//b.同一用户不重复中同一款礼包；
//c.若用户抽奖的次数，超过了虚拟礼包的种类数量，则随机中虚拟礼包。
function get_gift_pkg($aid,$uid,$gift_pkg,$prefix){
	list($redis,$model) = load_config_redis();
	$user_gift_pkg = $redis->get("{$prefix}:{$aid}_gift_pkg:".$uid);
	$open_gift_pkg = explode(";",$gift_pkg);
	//file_put_contents("/tmp/christmas.log",var_export($open_gift_pkg,true)."\n".var_export($user_gift_pkg,true),FILE_APPEND);
	if(!$user_gift_pkg){
		$prize_gift_pkg = $redis->get("{$prefix}:{$aid}_gift_pkg");
		$redis -> set("{$prefix}:{$aid}_gift_pkg:".$uid,$prize_gift_pkg,10*86400);
        $user_gift_pkg = $prize_gift_pkg;
		$intersection =  array_intersect($open_gift_pkg, $prize_gift_pkg);
	}else{	
		$intersection =  array_intersect($open_gift_pkg, $user_gift_pkg);
	}
	if($intersection){
		//a.优先抽中已安装游戏的虚拟礼包；
		foreach($intersection as $v){
			return $v;
			exit;
		}
	}else{
		return $user_gift_pkg[0]; 
	}
}

//去除已获得的礼包包名
function del_gift_pkg($aid,$uid,$pkg,$prefix){
	list($redis,$model) = load_config_redis();	
	$user_gift_pkg = $redis->get("{$prefix}:{$aid}_gift_pkg:".$uid);	
	$new_gift_pkg = array();
	foreach($user_gift_pkg as $k => $v){
		if($v != $pkg){
			$new_gift_pkg[] = $v;
		}
	}
	//file_put_contents("/tmp/christmas.log",var_export($new_gift_pkg,true),FILE_APPEND);
	$redis -> set("{$prefix}:{$aid}_gift_pkg:".$uid,$new_gift_pkg,10*86400);
}
function activity_is_stop($aid){
	list($redis,$model) = load_config_redis();	
	$option = array(
		'where' => array(
			'id' => $aid,
		),
		'table' => 'sj_activity',
		'field' => 'activity_page_id,activity_end_id,end_tm,channel_id,cover_user_type,activation_date_start,activation_date_end',
		'cache_time' => 20*60
	);			
	$activity = $model->findOne($option);	
	if($activity['end_tm'] <= time()){
		return false;
	}else{
		return $activity;
	}	
}
//获取真实包名
function get_real_package($package)
{
	list($redis,$model) = load_config_redis();	
	$option = array(
		'where' => array(
			'package' => $package,
			'status' =>1,
			'hide' =>1025,
		),
		'table' => 'sj_soft',
		'field' => 'channel_id',
		//'cache_time' => 20*60
	);
	$soft = $model->findOne($option);
	if($soft['channel_id']&&$soft['channel_id']!=",,")
	{
		$options = array(
			'where' => array(
				'softid' => $soft['channel_id'],
				'status' =>1,
			),
			'table' => 'sj_soft',
			'field' => 'package',
		);
		$real_soft = $model->findOne($options);
		if($real_soft['package'])
		{
			$real_package = $real_soft['package']; 
		}
		else
		{
			$real_package = $package;
		}
	}
	else
	{
		$real_package = $package; 
	}
	$redis -> set("real_package:post_old_package_{$package}:",$real_package,20*60);
	return $real_package;
}
//获取真实softid
function get_real_softid($id)
{
	list($redis,$model) = load_config_redis();	
	$option = array(
		'where' => array(
			'softid' => $id,
			'status' =>1,
			'hide' =>1025,
		),
		'table' => 'sj_soft',
		'field' => 'channel_id',
		//'cache_time' => 20*60
	);
	$soft = $model->findOne($option);
	if($soft['channel_id']&&$soft['channel_id']!=",,")
	{
		$real_softid = $soft['channel_id'];
	}
	else
	{
		$real_softid = $id; 
	}
	$redis -> set("real_softid:post_old_softid_{$id}:",$real_softid,20*60);
	return $real_softid;
}

 //获取用户安智币
function get_azb($uid,$active_id){
	$urlParam = '/user/azb/get';
	$log_name = "azb_get_".$active_id.".log";
	$js_data = array(
		'uid'	=>	$uid,
	);
	$data = array(
		'serviceId'	=>	'014',
		'data'		=>	json_encode($js_data)
	);	
	$res = get_azb_data($data, $urlParam,$log_name);
	return $res; 
}
//消费安智币
function consume_azb($active_id,$uid,$pwd,$azbAmount,$orderDes,$channel_id='',$form_orderid=''){
	if(!$uid){
		return json_encode(array('code'=>201,'msg'=>'uid参数不完整'));	
	}
	//消费安智币
	$urlParam = '/user/azb/consume';
	$log_name = "azb_consume_".$active_id.".log";
	$cporderId = "activity_".$active_id."_".$form_orderid;
	$js_data = array(
		'uid'		=>	$uid,
		'appkey'	=>	'1392365303Jy1R97taJfdtops8Cxum',
		'orderDes'	=>	$orderDes,
		'payPwd'	=>	$pwd,
		'azbAmount'	=>	$azbAmount,
		'cpOrderId' => $cporderId,
		'activityId' => $active_id
	);
	if(!$form_orderid){
		$id = add_consume_azb_data($js_data,'','','',$channel_id);
		$js_data['cpOrderId'] = "activity_".$active_id."_".$id;
	}		
	$data = array(
		'serviceId'	=>	'014',
		'data'		=>	json_encode($js_data)
	);
	$res = get_azb_data($data, $urlParam,$log_name);
	if($form_orderid){
		//如果有平台直接insert
		add_consume_azb_data($js_data,$form_orderid,$res,$channel_id);
	}else{
		save_consume_azb_data($js_data,$res,$id);
	}
	return $res;		
}
function save_consume_azb_data($data,$res,$id){
	list($redis,$model) = load_config_redis();	
	$where = array('id'=>$id);
	$data_update = array(
		'cporderId' => $data['cpOrderId'],
		'orderid' => json_decode($res['data']),
		'msg' => $res['msg'],
		'__user_table' => 'consume'
	);
	$ret = $model -> update($where,$data_update,'lottery/lottery');		
}
//添加安智币消费记录
function add_consume_azb_data($data,$form_orderid,$res,$channel_id){
	list($redis,$model) = load_config_redis();	
	$time = time();
	$new_data = array(
		'uid' 			=> 	$data['uid'],
		'aid' 			=> 	$data['activityId'],
		'money' 		=> 	$data['azbAmount'],
		'add_tm' 		=> 	$time,
		'cporderId' 	=> 	$data['cpOrderId'],
		'orderid'       =>  json_decode($res['data']),
		'form_orderid'  =>  $form_orderid,
		'msg'       	=>  $res['msg'],
		'__user_table' 	=> 	'consume' //消费记录表
	);
	if($channel_id){
		$channel_arr = explode(",", substr($channel_id, 1, -1));
		if(in_array('3150',$channel_arr) || in_array('271',$channel_arr)){
			$new_data['is_test'] = 1;
		}
	}
	$id = $model->insert($new_data, 'lottery/lottery');	
	return $id;
}
/**
 * 获取安智币信息接口
 * 消费安智币接口
 * @param  array $val
 * @return array
 */
function get_azb_data($vals, $urlParam,$log_name){
	$host	=	load_config('pay_host');
	$url	=	$host.'/pay/internal'.$urlParam;
	$vals	=	http_build_query($vals);
	$res	=	httpGetInfo($url,$host, $vals,$log_name);
	$last	=	json_decode($res,true);
	return $last;
}
function load_config_redis(){
	$config = load_config('lottery_cache/redis',"lottery");
	if ($config) {
		$redis = new GoRedisCacheAdapter($config);
	} else {
		$redis = GoCache::getCacheAdapter('redis');
	}
	$model = new GoModel();	
	return array($redis,$model);
}

//根据包名获取简化通用软件列表v65
//var_dump(get_app_info("cn.dfkgskfld2.anzhi"));

function get_app_info($packages){
    global $configs;
    $sid = $_GET['sid'] ? $_GET['sid'] : $_POST['sid'];
    $url = $configs['goserver_url'].'?sid='.$sid;
    $post_data = array(
        "KEY"=>"GET_APP_INFO",
        "PACKAGE"=>$packages,
    );
    $post_data = json_encode($post_data);;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);

    $output = curl_exec($ch);
    curl_close($ch);
    $tmp = json_decode($output,true);

    return json_encode($tmp['data']);
}

//防刷 同设备登陆3个账号以上 则进入黑名单
function get_brush_byusernum($aid){
    $uid = $_SESSION['USER_UID'];
    list($redis,$model) = load_config_redis();	
	if($_SESSION['DEVICE_SN'] && $_SESSION['SN_STATUS']){
		$deviceid = $_SESSION['DEVICE_SN'];
	}else{
		$deviceid = $_SESSION['DEVICEID'];
	}
    $device_user = "brush:".$aid.':device_user:'.$deviceid;
    $uid_arr = $redis->get($device_user);
    $uid_arr[$uid] = 1;
    $redis->set($device_user,$uid_arr,60*86400);
    $total = count($uid_arr);
    return $total;
}

//防刷 同账号登陆3个设备以上 则进入黑名单
function get_brush_byimeinum($aid){
    $uid = $_SESSION['USER_UID'];
	if($_SESSION['DEVICE_SN'] && $_SESSION['SN_STATUS']){
		$deviceid = $_SESSION['DEVICE_SN'];
	}else{
		$deviceid = $_SESSION['DEVICEID'];
	}
    list($redis,$model) = load_config_redis();	
    $uid_user = "brush:".$aid.':uid_user:'.$uid;
    $uid_arr = $redis->get($uid_user);
    $uid_arr[$deviceid] = 1;
    $redis->set($uid_user,$uid_arr,60*86400);
    $total = count($uid_arr);
    return $total;
}

//防刷 同一秒操作
//@type 1:imsi,2:uid
function brush_second_do($aid,$type=2,$click_num=3){
	list($redis,$model) = load_config_redis();		
    $balck_cache_time = '5184000';//redis缓存时间为两个月
    $black_list_uid = "brush:{$aid}:black_name_list";
	if($type == 2){
		if($_SESSION['USER_UID'] && $_SESSION['USER_ID'] != 13176){
			$key = $_SESSION['USER_UID'];
		}else{
			return false;
		} 
	}else{
		$key = $_SESSION['USER_IMSI'];
		if(!$key) return false;
	}
	$black_list_name_uid = $redis->gethash($black_list_uid,$key);
	if(!$black_list_name_uid){//如果不在黑名单
		$tm = time();
		$rkey_uid_click = "brush:{$aid}:second:click:{$key}:{$tm}:click_num";
		$click_num_uid = $redis->setx('incr', $rkey_uid_click, 1);
		$redis->expire($rkey_uid_click,20);
		if($click_num_uid>=$click_num){
			$redis->sethash($black_list_uid, array($key=>1), $balck_cache_time);
		}
	}
}

//防刷 ip写入
function set_brush_byip($aid){
    $ip=$_SERVER['REMOTE_ADDR'];
    list($redis,$model) = load_config_redis();
    $ip_award = "brush:".$aid.':ip_award:'.$ip;
    $ip_award_num = $redis->setx('incr', $ip_award, 1);
	$redis->expire($ip_award,60*86400);
    return $ip_award_num;
}


//防刷 通过 ip读取
function get_brush_byip($aid){
    $ip=$_SERVER['REMOTE_ADDR'];
    list($redis,$model) = load_config_redis();
    $ip_award = "brush:".$aid.':ip_award:'.$ip;
    $ip_award_num = $redis->get($ip_award);
    return $ip_award_num;
}
//防刷 通用 读操作
//type 1:imsi,2:uid
function get_brush_all($aid,$type=2){
	brush_second_do($aid,$type);
	//序号无效 则进入黑名单
	if($_SESSION['DEVICE_SN'] && isset($_SESSION['SN_STATUS']) && $_SESSION['SN_STATUS'] == 0){
		$log = array(
			'aid' => $aid,
			'sn' => $_SESSION['DEVICE_SN'],
			'imei' => $_SESSION['DEVICEID'],
			'uid' =>$_SESSION['USER_UID'],
			'time' => time(),
		);
		permanentlog('sn_invalid.log',json_encode($log));
		return array('code' => 0,'msg'=>'[刷]序号无效,imei:'.$_SESSION['DEVICEID']."==sn:". $_SESSION['DEVICE_SN']);
	}
	list($redis,$model) = load_config_redis();	
	$uid = $_SESSION['USER_UID'];
	if($type ==2 && $uid && $_SESSION['USER_ID'] != 13176){
		//防刷 同账号登陆3个设备以上 则进入黑名单
		$imeinum = get_brush_byimeinum($aid);
		//防刷 同设备登陆3个账号以上 则进入黑名单
		$usernum = get_brush_byusernum($aid);	
		if($imeinum > 3){
			return array('code' => 0,'msg'=>'[刷]同账号登陆3个设备以上');
		}
		if($usernum > 3){
			return array('code' => 0,'msg'=>'[刷]同设备登陆3个账号以上');
		}
	}
	//防刷 同一秒操作
	$black_list_uid = "black_name_list:{$aid}";
	$black_list_name = $redis->gethash($black_list_uid,$uid);	
	if($black_list_name){
		return array('code' => 0,'msg'=>'[刷]同一秒操作');
	}
	//防刷 同一个IP实物中奖个数（非礼包）达到2个
	$ipnum = get_brush_byip($aid);
	if($ipnum>=2){
		return array('code' => 0,'msg'=>'[刷]同一个IP中奖个数（非礼包）达到2个');
	}
	return array('code' => 1);
}
/*******
SN没查到进入黑名单
库中和session中的IMEI为空或000 
库中和session中的IMEI不相等进入黑名单
********/
function get_brush_bysn(){
	$sn = $_SESSION['DEVICE_SN'];
	if($sn && !isset($_SESSION['SN_STATUS'])){
		if(strpos($sn,"temp") !== false){
			$_SESSION['SN_STATUS'] = 1;
			return false;
		}
		list($redis,$model) = load_config_redis();	
		$option = array(
			'where' => array('mkey' => $sn),
			'table' => 'skey',
			'field' => 'mkey,imei,imsi',
		);
		$list = $model->findOne($option,'sn/lottery');	
		if(!$list){
			$_SESSION['SN_STATUS'] = 0;
		}else{
			if(is_numeric($_SESSION['DEVICEID']) || is_numeric($list['imei'])){
				$deviceid = intval($_SESSION['DEVICEID']);
				$imei = intval($list['imei']);
				if(!$deviceid || !$imei){
					$_SESSION['SN_STATUS'] = 1;
				}else{
					if($list['imei'] == $_SESSION['DEVICEID']){
						$_SESSION['SN_STATUS'] = 1;
					}else{
						$_SESSION['SN_STATUS'] = 0;
					}
				}
			}else if(empty($list['imei']) || empty($_SESSION['DEVICEID'])){
				$_SESSION['SN_STATUS'] = 1;
			}else if($list['imei'] == $_SESSION['DEVICEID']){
				$_SESSION['SN_STATUS'] = 1;
			}else{
				$_SESSION['SN_STATUS'] = 0;
			}
		}
	}
}

//谢谢参与等级
function get_no_win_level($aid,$prefix,$table){	
	list($redis,$model) = load_config_redis();	
	$key_no_win_level = $prefix.":".$aid.':no_win_level';
	$no_win_level = $redis->get($key_no_win_level);
	if($no_win_level === null){
		$option = array(
			'where' => array('status' =>1,'aid'=>$aid,'type'=>3),
			'table' => $table,
			'field' => 'level',
		);
		$list = $model->findOne($option,'lottery/lottery');	
		$no_win_level = intval($list['level']);
		$redis->set($key_no_win_level,$no_win_level,20*60);		
	}
	return 	$no_win_level;
}
//验证是否做过该任务
function task_ishasdone($tasktype,$pkg,$pid){
	list($redis,$model) = load_config_redis();
	$task_ishasdone_key = "task_ishasdone:".$pid.":".$pkg.":".$tasktype;
	$ishasdone = $redis->get($task_ishasdone_key);
	if($ishasdone === null){
		$device_arr = getDeviceInfo();
		if(!$device_arr['deviceid']){
			return array('code'=>0,'msg'=>'无效设备');
		}
		$device_arr['appversion'] = '6500';
		$header = getHeaderInfo();
		$data = array(
			'taskType' => $tasktype,
			'uniqCons' => $pkg,
			'pid' => $pid,
			'imei' => $_SESSION['DEVICEID'],
			'mac'=>$_SESSION['MAC'],
			//'sid'=> $_SESSION['UCENTER_SID'],
		);
		$extra = array(
			'prefix'=>'task',
			'apiname'=>'/api/tms/task/isHasDone',
			'version' => 'v65',
			'passthrough'=>true
		);
		$app = defined('APP_NAME') ? APP_NAME : 'gomarket';
		$ucenter = load_config('ucenter/'.$app, 'uc');
		$request_serviceId = $ucenter['client_serviceId'];
		$apiname = $extra['apiname'];
		$api_info = array(
			'prefix'=>'task',
			'apiname'=> $apiname,
			'passthrough'=> true,
			'sid'=> $_SESSION['UCENTER_SID'],
		);
		$data['serviceId'] = $request_serviceId;
		$data_array = array(
			'data'=> $data,
			'device'=>$device_arr,
			'header'=>$header
		);
		load_helper('ucenter');		
		$result = request_task($api_info,$data_array, $extra);
		$ishasdone = $result;
		permanentlog('web_sign_task_ishasdone.log', date("Y-m-d H:i:s")."\n".print_r($api_info, true) . "\n" .print_r($data_array, true) . "\n" .print_r($extra, true) . "\n" . json_encode($result) . "\n\n");
		if($result['code'] && $result['code'] != 200){
			return array('code'=>0,'msg'=>$result['msg']);
		}
		$redis->set($task_ishasdone_key,$ishasdone,3600);
	}
	return array('code'=>1,'ishasdone'=>$ishasdone);
}
//是否是微信浏览器
function is_weixin(){  
    if ( strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false ) {  
        return true;  
    }    
    return false;  
}
//账号规则
function account_policy($username,$email,$mobile){
	if($mobile){
		return substr_replace($mobile,'*****',3, -3);  
	}else if($email || $username){
		if($email) $username = $email;
		$len = mb_strlen($username,"utf-8");
		//判断全是中文
		//if(preg_match('/^[\x{4e00}-\x{9fa5}]+$/u', $username)>0)
		if($len >= 11){
			$str1 = mb_substr($username,0,3,'utf-8');
			$str2 = mb_substr($username,-3,3,'utf-8');
			return $str1."*****".$str2;  
		}else if($len >= 6 && $len <= 10){
			$str1 = mb_substr($username,0,2,'utf-8');
			$str2 = mb_substr($username,-2,2,'utf-8');
			if($len == 6 ){
				$str3 = '**';
			}else if($len == 7){
				$str3 = '***';
			}else if($len == 8){	
				$str3 = '****';
			}else{
				$str3 = '*****';
			}
			return $str1.$str3.$str2;  
		}else if($len >= 3 && $len <= 5){
			$str1 = mb_substr($username,0,1,'utf-8');
			$str2 = mb_substr($username,$len-1,1,'utf-8');
			if($len == 3 ){
				$str3 = '*';
			}else if($len == 4){
				$str3 = '**';
			}else{
				$str3 = '***';
			}			
			return $str1.$str3.$str2;					
		}else if($len == 2){
			$str1 = mb_substr($username,0,1,'utf-8');
			return $str1."*";					
		}else{
			return $username;
		}			
	}
}  

function getGameChannelInfo($cid, $package)
{
    $model = new GoModel();
    $option = array(
        'table' => 'sdk_channel_game',
        'where' => array(
            'channel_id' => $cid,
            'status' => 1,
            'apk_status' => 3,
            'package' => $package
        ),
        'field' => 'package, softid, url',
        'cache_time' => 600
    );
    $info = $model->findOne($option);


    if (!$info) {
        $option = array(
            'table' => 'sdk_channel_game_bak',
            'where' => array(
                'channel_id' => $cid,
                'status' => 1,
                'apk_status' => 3,
                'package' => $package,
            ),
            'field' => 'package, softid, url',
            'cache_time' => 600
        );
        $info = $model->findOne($option);
    }

    return $info;
}
