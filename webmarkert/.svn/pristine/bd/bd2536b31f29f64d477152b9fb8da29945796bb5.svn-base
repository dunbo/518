<?php
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
			'intro',
			'version'
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
    $list_count =   $old_list['COUNT'];
    $old_list   =   $old_list['DATA'];
    $list = array();
	
    foreach ($old_list as $k => $v) {
        //原来为了兼容老的手机端应用, 所有的数据都是数字索引，在wap端不需要这样做， 转换为K-V形式
        $list[$k] = array( 'softid' => $v[0], 'iconurl' => $v[1], 'softname' => $v[2], 'score' => $v[3], 'msgnum' => $v[4], 'dev_name' => $v[5], 'costs' => $v[6], 'package' => $v[7], 'safe' => $v[8], 'filesize' => $v[9], 'category_id' => $v[10], 'total_downloaded' => $v[11], 'url' => $v[12], 'version_code' => $v[13], 'upload_tm' => $v[14], 'min_firmware' => $v[15], 'official_icon' => $v[21],'intro' => $v[22]);
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
    $action_file = GO_APP_ROOT . DS . '..' . DS . 'newgomarket.goapk.com' . DS . 'modules' . DS. strtolower($module). DS. $action. '.php';
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

//获取软件的下载信息
function getApkUrl(){
    if ( !defined('CHANNEL') || !in_array(CHANNEL, array('qqhelper'))) { return False; }
    global $tplObj;
    $g_soft_info = array();
    $key_arr =  array("homeFeature","hot","applist","apps","feature_soft_arr","day_soft","bibei_arr","feature_mine","intro","result","softapplist","anzhi","like");
    foreach ($key_arr as $key) {
		if($key == 'anzhi'){
			$anzhi_info = $tplObj -> out[$key]; 
		}elseif($key == 'like'){
			$like_info = $tplObj -> out[$key];
		}elseif($key == 'feature_soft_arr'){
			$feature_soft_arr_info = $tplObj -> out[$key];
		}elseif($key == 'intro'){
			$intro_info = $tplObj -> out[$key];
		}elseif($key == 'result'){
			$search_info = $tplObj -> out[$key];
		}else{
			if (is_array($tplObj->out[$key])) { $g_soft_info += $tplObj->out[$key]; }
		}
    }
	$apklist = array();
	if($anzhi_info){
		$softid   = $anzhi_info['ID'];
		$package  = $anzhi_info['PACKAGENAME'];
		$softname = $anzhi_info['SOFT_NAME'];
		$version  = $anzhi_info['SOFT_VERSION'];
		$category_id = $anzhi_info['category_id'];
		list($twoid,$threeid,$type) = getParentById($category_id);
		$url = "http://tencent.anzhi.com/dl_app.php?s=".$softid."&channel=qqhelper|&title=".$softname."V".$version."&type=".$type."&pk=".$package."&tx=0&categoryid=".$category_id."&twoid=".$twoid."&threeid=".$threeid."&dp=14";
		$apklist[$softid] = "qqapp://".base64_encode($url);
	}
	if($intro_info){
		$softid   = $intro_info['ID'];
		$package  = $intro_info['PACKAGENAME'];
		$softname = $intro_info['SOFT_NAME'];
		$version  = $intro_info['SOFT_VERSION'];
		$category_id = $intro_info['category_id'];
		list($twoid,$threeid,$type) = getParentById($category_id);
		$url = "http://tencent.anzhi.com/dl_app.php?s=".$softid."&channel=qqhelper|&title=".$softname."V".$version."&type=".$type."&pk=".$package."&tx=0&categoryid=".$category_id."&twoid=".$twoid."&threeid=".$threeid."&dp=14";
		$apklist[$softid] = "qqapp://".base64_encode($url);
	}
	if($like_info){
		foreach($like_info as $k => $v){
			$softid = $v[3];
			$package  = $v[0];
			$softname = $v[2];
			$version  = $v[7];
			$category_id = $v[6];
			list($twoid,$threeid,$type) = getParentById($category_id);
			$url = "http://tencent.anzhi.com/dl_app.php?s=".$softid."&channel=qqhelper|&title=".$softname."V".$version."&type=".$type."&pk=".$package."&tx=0&categoryid=".$category_id."&twoid=".$twoid."&threeid=".$threeid."&dp=14";
			$apklist[$softid] = "qqapp://".base64_encode($url);
		}
	}
	if($search_info){
		foreach($search_info as $key => $val){
			$softid   = $val['softid'];
			$package  = $val['package'];
			$softname = $val['softname'];
			$version  = $val['upload_tm'];
			$category_id = $val['category_id'];
			list($twoid,$threeid,$type) = getParentById($category_id);
			$url = "http://tencent.anzhi.com/dl_app.php?s=".$softid."&channel=qqhelper|&title=".$softname."V".$version."&type=".$type."&pk=".$package."&tx=0&categoryid=".$category_id."&twoid=".$twoid."&threeid=".$threeid."&dp=14";
			$apklist[$softid] = "qqapp://".base64_encode($url);
		}
	}
	if($feature_soft_arr_info){
		foreach($feature_soft_arr_info as $k => $v){
			$softid = $v[0];
			$package  = $v[7];
			$softname = $v[2];
			$version  = $v[17];
			$category_id = $v[10];
			list($twoid,$threeid,$type) = getParentById($category_id);
			$url = "http://tencent.anzhi.com/dl_app.php?s=".$softid."&channel=qqhelper|&title=".$softname."V".$version."&type=".$type."&pk=".$package."&tx=0&categoryid=".$category_id."&twoid=".$twoid."&threeid=".$threeid."&dp=14";
			$apklist[$softid] = "qqapp://".base64_encode($url);
		}
	}

	foreach ($g_soft_info as $k => $v) {
		if(!empty($v['CHILD_GROUP'])){
			foreach($v['CHILD_GROUP'] as $key => $val){
				$softid   = $val[0];
				$package  = $val[7];
				$softname = $val[2];
				$version  = $val[14];
				$category_id = $val[10];
			
				list($twoid,$threeid,$type) = getParentById($category_id);
				$url = "http://tencent.anzhi.com/dl_app.php?s=".$softid."&channel=qqhelper|&title=".$softname."V".$version."&type=".$type."&pk=".$package."&tx=0&categoryid=".$category_id."&twoid=".$twoid."&threeid=".$threeid."&dp=14";
				$apklist[$softid] = "qqapp://".base64_encode($url);
			}
		}else{
			$softid   = $v[0];
			$package  = $v[7];
			$softname = $v[2];
			$version  = $v[14];
			$category_id = $v[10];
			list($twoid,$threeid,$type) = getParentById($category_id);
			$url = "http://tencent.anzhi.com/dl_app.php?s=".$softid."&channel=qqhelper|&title=".$softname."V".$version."&type=".$type."&pk=".$package."&tx=0&categoryid=".$category_id."&twoid=".$twoid."&threeid=".$threeid."&dp=14";
			$apklist[$softid] = "qqapp://".base64_encode($url);
		}
	}
	$url = "http://m.anzhi.com/redirect.php?do=dlapk&puid=996|&title=安智市场V5.2.1&type=1&pk=cn.goapk.market&tx=0&"."categoryid=99&twoid=0&threeid=0&dp=14&kword=安智市场";
    $tplObj -> out['qqapps'] = $apklist;
	
	$tplObj -> out['go_market'] = "qqapp://".base64_encode($url);
}

//获取软件分类的父类和顶级分类
function getParentById($catid){
	$category_logic = pu_load_logic('category');
	$list = $category_logic -> get_all_category();
	$fatherid = $list[$catid]['parentid'];
	//$grandid  = $list[$fatherid]['parentid']; 4.0版本为三级分类，此处只用二级分类
	if($fatherid == 1){
	   $type = 1;
	}
	if($fatherid == 2){
	   $type = 2;
	}
	//array($fatherid,$grandid,$type)
    return array($fatherid,0,$type);
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
    $key = $ip.":web:".date("Ymd");
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

//首页图片轮播图
function get_pic_scroll($chl) {
	global $tplObj;
	$web_pic_model = pu_load_model_obj("pu_webmarket_show_picture");
	list($result,$picount) = $web_pic_model -> get_picture($chl);
	foreach($result as $key => $info){
		$result[$key]['link'] = str_replace('http://www.anzhi.com/','',$info['link']);
	}
	if($result){
		$tplObj ->out['web_pic_info'] =  $result;
	}else{
		$tplObj ->out['web_pic_info']=array();
	}
	if($picount){
		$tplObj -> out['web_pic_count'] = $picount;
	}else{
		$tplObj -> out['web_pic_count']=array();
	}
}
//专题图片
function get_pic_subject($limit) {
    global $tplObj;
    $subject_pic_model = pu_load_model_obj("pu_subject");
    $tplObj -> out['subject_pic_info'] = $subject_pic_model -> get_feature_pic($limit);
	//$list = gomarket_action('soft.GoGetSoftSubject',array('LIST_INDEX_START' => 0,"LIST_INDEX_SIZE" => $limit,"VR" => 3,"IS_WEB" => 1));
    //$tplObj -> out['subject_pic_info'] = $list['DATA'];
}

//系统支持版本
function get_support_system($min_system,$max_system=''){
	global $tplObj;
	$min_system = firmware2os($min_system);
	$max_system = firmware2os($max_system);
	if(!empty($max_system) && !empty($min_system)){
		$system = "".$min_system."及以上,".$max_system."以下";
	}elseif(empty($max_system) && !empty($min_system)){
		$system = "".$min_system."及以上";
	}elseif(!empty($max_system) && empty($min_system)){
		$system = "".$max_system."及以下";
	}elseif(empty($max_system) && empty($min_system)){
		$system = "无限制";
	}
	return $system;
}

function get_login_register_referer($referer)
{
    if ($referer && !strstr($referer, 'login.php') && !strstr($referer, 'register.php') ) {
        $url = $referer;
    } else {
        $url = '/';
    }
    return $url;
}

function scorehtml($result){
	foreach($result as $key => $value) {
		$i = $k =0;
		$result[$key]['scorehtml']="";
		$i = floor($value[3] / 2);
		$k = $value[3] % 2;
		for($i1=$i;$i1>0;$i1--){
			$result[$key]['scorehtml'] .='<img alt="" src="images/star_01.png">';
		}
		if($k!=0)
			$result[$key]['scorehtml'] .= '<img alt="" src="images/star_02.png">';
		if(($i+$k)<5) {
			for($i2=(5-$i-$k);$i2>0;$i2--){
				$result[$key]['scorehtml'] .='<img alt="" src="images/star_03.png">';
			}	
		}
	}
	return 	$result;
}	

function get_route_rule()
{
/**
		array(
			'/^(\/?)applist\.php\?type=(appcat|gamecat)/', //匹配的url
			'/sort_{$sub_cat_id}_{$page}_{$order}.html', //生成的静态地址
			array(
				'page' => array(1),	//page是参数名，对应数组第一个值是默认值
				'order' => array(
					0,
					array(0=>'new', 1=>'hot'), //传递值到静态字符串的转换
				),
			)
		),
**/
	$parten_arr = array(
		array('/^(\/?)index\.php/', '/index.html'),
		array('/^(\/?)good_recommend\.php/', '/recommend_{$page}.html', array('page' => array(1))),
		array('/^(\/?)subject_detail\.php/', '/subject_{$id}_{$page}.html'),
		array('/^(\/?)subject_detail\.php/', '/subject_{$id}.html'),
		array('/^(\/?)detail\.php/', '/soft_{$id}.html'),
		array('/^(\/?)package\.php/', '/pkg/{$pkg}'),
		array('/^(\/?)widget_hotkey\.php/', '/widgethotkey_{$theme}.html', array('theme' => array(1))),
		array('/^(\/?)widget_cat\.php/', '/widgetcat_{$parentid}.html'),
		array('/^(\/?)widget_catetag\.php/', '/widgetcatetag_{$parentid}.html'),
		array('/^(\/?)widget_top\.php/', '/widgettop_{$id}.html'),
		array('/^(\/?)widget_sort\.php/', '/widgetsort_{$id}_{$order}_{$theme}.html', array('theme' => array(1), 'order' => array(1))),
		array('/^(\/?)widget_tsort\.php/', '/widgettsort_{$tag_id}_{$cat_id}_{$order}.html', array('order' => array(1))),
		array('/^(\/?)widget_subject\.php/', '/widgetsubject_{$id}_{$size}_{$theme}.html', array('theme' => array(1), 'size' => array(12))),
		array('/^(\/?)applist.php\?type=applist/', '/applist.html'),
		array('/^(\/?)applist.php\?type=gamelist/', '/gamelist.html'),
		array('/^(\/?)newsstand\.php/', '/newsstand/{$ftype}_{$fftype}_{$page}.html',array('ftype' => array(),'fftype' => array(),'page'=>array(1))),
		array('/^(\/?)newsstand\.php/', '/newsstand/{$ftype}_{$page}.html',array('ftype' => array(),'page'=>array(1))),
		array('/^(\/?)newsstand.php\?is_details=1/', '/newsstand/{$id}.html',array('id'=>array(1))),
		array('/^(\/?)newsstand\.php/', '/newsstand_{$page}.html',array('page'=>array(1))),
		array(
			'/^(\/?)applist\.php\?type=(appcat|gamecat)/', 
			'/sort_{$sub_cat_id}_{$page}_{$order}.html', 
			array(
				'page' => array(1),
				'order' => array(
					1,
					array(0=>'new', 1=>'hot')
				),
			)
		),
		array(
			'/^(\/?)applist\.php\?type=(appctag|gamectag)/',
			'/tsort_{$sub_tag_id}_{$sub_cat_id}_{$page}_{$order}.html',
			array(
				'page' => array(1),
				'order' => array(
					1,
					array(0=>'new', 1=>'hot')
				),
			)
		),
		array('/^(\/?)subject\.php/', '/subject.html'),
		array('/^(\/?)join\.php/', '/friendlink.html'),
		array('/^(\/?)subject_list\.php/', '/subjects_{$page}.html', array('page' => array(1))),
		array(
			'/^(\/?)list\.php/', 
			'/list_{$parentid}_{$page}_{$order}.html', 
			array(
				'parentid' => array(1),
				'page' => array(1),
				'order' => array(
					1,
					array(0=>'new', 1=>'hot')
				),
			)
		),
	);
	return $parten_arr;
}

function url2static_url($url, $return_cache_path = false)
{
	$static_domain = array(
        'anzhi.com' => 1,
        'goapk.com' => 1,
		'zte.anzhi.com' => 1,
		'hq.anzhi.com' => 1,
		'tcl.anzhi.com' => 1,
		'wdj.anzhi.com' => 1,
		'web.anzhi.com' => 1,
		'360.anzhi.com' => 1,
		'www.anzhi.com' => 1,
        'tencent.anzhi.com' => 1,
		//'assistant.anzhi.com'=>1
	);
    parse_str(preg_replace('#.+?\.php[\?]?#si', '', $url), $p);
	$static_url = '';
	if (defined('CHANNEL') && preg_match('/[a-z0-9_]+/i', CHANNEL) && CHANNEL != 'www') {
		$p['channel'] = CHANNEL;
        if (CHANNEL == '360_app' ) {
            $static_url .= '/app';
        } elseif (CHANNEL == '360_game' ) {
            $static_url .= '/game';
        }
	}
	$n = count($p);
	if ($n > 0) {
		$query_string = http_build_query($p);
        $url = preg_replace('/\?(.*)$/', '', $url);
		$query_name = $url;
        $url = $url. '?'. $query_string;
	}
	// $type = $p['type'];
	$softid = $p['id'];
	static $allpkg;
	if($query_name == "detail.php" && empty($allpkg[$softid])){		
		$softlist_model = load_model('softlist');
		$soft_file_data = $softlist_model -> getSoftInfos($softid);
		if($soft_file_data[$softid]['package']){
			$allpkg[$softid]['pkg'] = $soft_file_data[$softid]['package'];
			$allpkg[$softid]['md5'] = substr(md5($soft_file_data[$softid]['package']),0,4);
		}
	} 	
	//file_put_contents("/tmp/zhuang.log",var_export($allpkg,true),FILE_APPEND);
    /*if (!isset($static_domain[strtolower($_SERVER['HTTP_HOST'])])){
		permanentlog("web_url_error.log",$_SERVER['HTTP_HOST'].' '.$url);
		return $url;
	}*/

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
					if($query_name == "detail.php" && $softid){
						$tmp_url = "/pkg/".$allpkg[$var_value]['md5']."_".$allpkg[$var_value]['pkg'].".html";	
						
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
	//var_dump($has_rule,$static_url);
	if ($has_rule) {
		return $static_url;
	} else {
		return $url;
	}
}

function url2static_file($url)
{
	return false;
}

//数据整合 用于第三方合作
function get_appList()
{
    global $tplObj;
    $g_soft_info = array();
    $new_g_soft_info = array();
    $key_arr =  array("homeFeature","applist","apps","feature_soft_arr","day_soft","bibei_arr","feature_mine","result","softapplist");
    foreach ($key_arr as $key) {
        if (is_array($tplObj->out[$key])) { 		
			$g_soft_info += $tplObj->out[$key];
		}
    }
	if(isset($tplObj -> out['anzhi'])){
		$anzhi = $tplObj -> out['anzhi'];
		$result = array();
		$result['id'] = $anzhi['ID'];
        $result['package'] = 'cn.goapk.market';
        $result['versionCode'] = $anzhi['SOFT_VERSION_CODE'];
        $result['appname'] = $anzhi['SOFT_NAME'];
		$new_g_soft_info[] = $result;
	}
	if(isset($tplObj -> out['intro'])){
		$anzhi = $tplObj -> out['intro'];
		$result = array();
		$result['id'] = $anzhi['ID'];
        $result['package'] = $anzhi['PACKAGENAME'];
        $result['versionCode'] = $anzhi['SOFT_VERSION_CODE'];
        $result['appname'] = $anzhi['SOFT_NAME'];
		$new_g_soft_info[] = $result;
	}
	if(isset($tplObj -> out['like'])){
		$like = $tplObj -> out['like'];
		foreach($like as $info){
			$result = array();
			$result['id'] = $info[3];
			$result['package'] = $info[0];
			$result['versionCode'] = $info[5];
			$result['appname'] = $info[2];
			$new_g_soft_info[] = $result;
		}
	}
	if(isset($tplObj -> out['result'])){
		$anzhi = $tplObj -> out['result'];
		foreach($anzhi as $info){
			$result = array();
			$result['id'] = $info['softid'];
			$result['package'] = $info['package'];
			$result['versionCode'] = $info['version_code'];
			$result['appname'] = $info['softname'];
			$new_g_soft_info[] = $result;
		}
	}
	
    foreach($tplObj -> out['bibei_arr'] as $info){
        $pkg_arr = $info['CHILD_GROUP'];
        foreach($pkg_arr as $soft){
            $result = array();
            $result['id'] = $soft[0];
            $result['package'] = $soft[7];
            $result['versionCode'] = $soft[13];
            $result['appname'] = $soft[2];
            $new_g_soft_info[] = $result;
        }
    }
	
    foreach ($g_soft_info as $k => $v) {
		$result = array();
        $result['id'] = $v['0'];
        $result['package'] = $v['7'];
		$result['versionCode'] = $v['13'];
        $result['appname'] = $v['2'];
		$new_g_soft_info[] = $result;
	}

	$result = array();
	$result['id'] = 19;
	$result['package'] = 'cn.goapk.market';
	$result['versionCode'] = '4200';
	$result['appname'] = '安智市场';
    $new_g_soft_info[] = $result;
    $tplObj->out['appList'] = json_encode($new_g_soft_info);
}



function display($tpl){	
    global $tplObj;
	if(CHANNEL == 'qqhelper'){
		getApkUrl();
		get_appList();
	}else if(CHANNEL == '360_app' || CHANNEL == '360_game' || CHANNEL == '360'){\
		get_g_soft_info();
	}
    $tplObj->display($tpl);
}

function get_g_soft_info()
{
    //针对360
    global $tplObj;
    $g_soft_info = array();
    $new_g_soft_info = array();
    $key_arr =  array("homeFeature","applist","apps","feature_soft_arr","day_soft","bibei_arr","feature_mine","hotAppResult");
    foreach ($key_arr as $key) {
        if (is_array($tplObj->out[$key])) { 		
			$g_soft_info += $tplObj->out[$key];
		}
    }
	if(isset($tplObj -> out['anzhi'])){
		$anzhi = $tplObj -> out['anzhi'];
		$new_g_soft_info[$anzhi['ID']]['url'] = 'http://'.$_SERVER['HTTP_HOST']."/dl_app.php?s=".$anzhi['ID']."&channel=".CHANNEL;
        $new_g_soft_info[$anzhi['ID']]['packageName'] = 'cn.goapk.market';
        $new_g_soft_info[$anzhi['ID']]['version'] = $anzhi['SOFT_VERSION'];
        $new_g_soft_info[$anzhi['ID']]['iconUrl'] = $anzhi['ICON'];
        $new_g_soft_info[$anzhi['ID']]['size'] = $anzhi['SOFT_SIZE'];
        $category_model = pu_load_model_data('pu_category',$anzhi['10']);
        $category_model = (array)$category_model;
        $new_g_soft_info[$anzhi['ID']]['apkCate'] = $category_model['name'];
        $new_g_soft_info[$anzhi['ID']]['versionCode'] = $anzhi['SOFT_VERSION_CODE'];
        $new_g_soft_info[$anzhi['ID']]['softname'] = $anzhi['SOFT_NAME'];
        $new_g_soft_info[$anzhi['ID']]['apkMd5'] = '';
        $new_g_soft_info[$anzhi['ID']]['channelName'] = '安智网';
	}
	
	if(isset($tplObj -> out['360_special'])){
		foreach($tplObj -> out['360_special'] as $anzhi){
			$new_g_soft_info[$anzhi['ID']]['url'] = 'http://'.$_SERVER['HTTP_HOST']."/dl_app.php?s=".$anzhi['ID']."&channel=".CHANNEL;
			$new_g_soft_info[$anzhi['ID']]['packageName'] = $anzhi['PACKAGE'];
			$new_g_soft_info[$anzhi['ID']]['version'] = $anzhi['SOFT_VERSION'];
			$new_g_soft_info[$anzhi['ID']]['iconUrl'] = $anzhi['ICON'];
			$new_g_soft_info[$anzhi['ID']]['size'] = $anzhi['SOFT_SIZE'];
			$category_model = pu_load_model_data('pu_category',$anzhi['10']);
			$category_model = (array)$category_model;
			$new_g_soft_info[$anzhi['ID']]['apkCate'] = $category_model['name'];
			$new_g_soft_info[$anzhi['ID']]['versionCode'] = $anzhi['SOFT_VERSION_CODE'];
			$new_g_soft_info[$anzhi['ID']]['softname'] = $anzhi['SOFT_NAME'];
			$new_g_soft_info[$anzhi['ID']]['apkMd5'] = '';
			$new_g_soft_info[$anzhi['ID']]['channelName'] = '安智网';
		}		
	}
	if(isset($tplObj -> out['intro'])){
		$anzhi = $tplObj -> out['intro'];
		$new_g_soft_info[$anzhi['ID']]['url'] = 'http://'.$_SERVER['HTTP_HOST']."/dl_app.php?s=".$anzhi['ID']."&channel=".CHANNEL;
        $new_g_soft_info[$anzhi['ID']]['packageName'] = $anzhi['PACKAGENAME'];
        $new_g_soft_info[$anzhi['ID']]['version'] = $anzhi['SOFT_VERSION'];
        $new_g_soft_info[$anzhi['ID']]['iconUrl'] = $anzhi['ICON'];
        $new_g_soft_info[$anzhi['ID']]['size'] = $anzhi['file_size'];
        $category_model = pu_load_model_data('pu_category',$anzhi['10']);
        $category_model = (array)$category_model;
        $new_g_soft_info[$anzhi['ID']]['apkCate'] = $category_model['name'];
        $new_g_soft_info[$anzhi['ID']]['versionCode'] = $anzhi['SOFT_VERSION_CODE'];
        $new_g_soft_info[$anzhi['ID']]['softname'] = $anzhi['SOFT_NAME'];
        $new_g_soft_info[$anzhi['ID']]['apkMd5'] = '';
        $new_g_soft_info[$anzhi['ID']]['channelName'] = '安智网';
	}
	if(isset($tplObj -> out['like'])){
		$like = $tplObj -> out['like'];
		foreach($like as $info){
			$new_g_soft_info[$info[3]]['url'] = 'http://'.$_SERVER['HTTP_HOST']."/dl_app.php?s=".$info['3']."&channel=".CHANNEL;
			$new_g_soft_info[$info[3]]['packageName'] = $info[0];
			$new_g_soft_info[$info[3]]['version'] = $info[7];
			$new_g_soft_info[$info[3]]['iconUrl'] = $info[1];
			$new_g_soft_info[$info[3]]['size'] = $info[8];
			$category_model = pu_load_model_data('pu_category',$info[6]);
			$category_model = (array)$category_model;
			$new_g_soft_info[$info[3]]['apkCate'] = $category_model['name'];
			$new_g_soft_info[$info[3]]['versionCode'] = $info[5];
			$new_g_soft_info[$info[3]]['softname'] = $info[2];
			$new_g_soft_info[$info[3]]['apkMd5'] = '';
			$new_g_soft_info[$info[3]]['channelName'] = '安智网';
		}
	}
	if(isset($tplObj -> out['result'])){
		$anzhi = $tplObj -> out['result'];
		foreach($anzhi as $info){
			$new_g_soft_info[$info['softid']]['url'] = 'http://'.$_SERVER['HTTP_HOST']."/dl_app.php?s=".$info['softid']."&channel=".CHANNEL;
			$new_g_soft_info[$info['softid']]['packageName'] = $info['package'];
			$new_g_soft_info[$info['softid']]['version'] = $info['version'];
			$new_g_soft_info[$info['softid']]['iconUrl'] = $info['iconurl'];
			$new_g_soft_info[$info['softid']]['size'] = $info['filesize'];
			$category_model = pu_load_model_data('pu_category',$info['category_id']);
			$category_model = (array)$category_model;
			$new_g_soft_info[$info['softid']]['apkCate'] = $category_model['name'];
			$new_g_soft_info[$info['softid']]['versionCode'] = $info['version_code'];
			$new_g_soft_info[$info['softid']]['softname'] = $info['softname'];
			$new_g_soft_info[$info['softid']]['apkMd5'] = '';
			$new_g_soft_info[$info['softid']]['channelName'] = '安智网';
		}
	}
    foreach ($g_soft_info as $k => $v) {
        $new_g_soft_info[$v['0']]['url'] = 'http://'.$_SERVER['HTTP_HOST']."/dl_app.php?s=".$v['0']."&channel=".CHANNEL;
        $new_g_soft_info[$v['0']]['packageName'] = $v['7'];
        $new_g_soft_info[$v['0']]['version'] = $v['14'];
        $new_g_soft_info[$v['0']]['iconUrl'] = $v['1'];
        $new_g_soft_info[$v['0']]['size'] = $v['9'];
        $category_model = pu_load_model_data('pu_category',$v['10']);
        $category_model = (array)$category_model;
        $new_g_soft_info[$v['0']]['apkCate'] = $category_model['name'];
        $new_g_soft_info[$v['0']]['versionCode'] = $v['13'];
        $new_g_soft_info[$v['0']]['softname'] = $v['2'];
        $new_g_soft_info[$v['0']]['apkMd5'] = '';
        $new_g_soft_info[$v['0']]['channelName'] = '安智网';
    }
	$link = 'http://m.anzhi.com/redirect.php?do=dlapk&puid=95';
	$new_g_soft_info['95'] = add_soft_info($link);
    $tplObj->out['g_soft_info'] = json_encode($new_g_soft_info);
}

function add_soft_info($link) {
        $data = array();
        $data['url'] = $link;
        $data['packageName'] = "cn.goapk.market";
        $data['version'] = "4.4";
        $data['iconUrl'] = "http://img5.anzhi.com/icon/201305/21/cn.goapk.market_69636100_0.png";
        $data['size'] = "2320322";
        $data['apkCate'] = "系统工具";
        $data['versionCode'] = "4400";
        $data['softname'] = "安智市场_360版";
        $data['apkMd5'] = '';
        $data['channelName'] = '安智网';
        return $data;
}

function links(){
	$model = new GoModel();
	$option = array(
		'table' => 'sj_frendly_link',
		'where' => array(
			'state' => 1,
			'status' => 1,
			'type'=>1
		),
		'order' => 'rank',
	);
	$link = $model->findAll($option);
	return $link;
}

function get_download_url($softid,$status = "",$num = "0", $increase = true)
{
//      load_helper('download');
//      $can_download = is_can_donwload($softid);
//      if (!$can_download) return DOWNLOAD_NO_SOFT;
        $memcache = GoCache::getCacheAdapter('memcached');
        $bad_soft_id_key = 'bad_softid:'. $softid;
//        $bad_res = $memcache->get($bad_soft_id_key);
        if ($bad_res) {
            $log = array(
                'softid' => $softid,
                'package' => '',
                'action' => ACTION_WEB_DOWNLOAD,
                'ip' => onlineip(),
                'submit_tm' => time() ,
            );
            pu_load_model_obj(
                'pu_log', 
                array('logfile' => 'bad_download.json', 'message' => json_encode($log))
            )->save_data_info();

            return DOWNLOAD_NO_SOFT;
        }


        $softlist_model = load_model('softlist');
        $soft_file_data = $softlist_model -> getSoftInfos($softid);
        if (!$soft_file_data || $soft_file_data[$softid]['status'] !=1 || !in_array($soft_file_data[$softid]['hide'], array(0, 1, 1024))) {
//          $memcache->set($bad_soft_id_key, 1, 86400);
            return DOWNLOAD_NO_SOFT;
        }

        //包名不在上架列表中
        /*
        if (!$softlist_model->getPkg2Id($soft_file_data[$softid]['package'])) {
            $memcache->set($bad_soft_id_key, 1, 3600);
            $log = array(
                'softid' => $softid,
                'package' => $soft_file_data[$softid]['package'],
                'action' => ACTION_WEB_DOWNLOAD,
                'ip' => onlineip(),
                'submit_tm' => time() ,
            );
            pu_load_model_obj(
                'pu_log', 
                array('logfile' => 'bad_download.json', 'message' => json_encode($log))
            )->save_data_info();
            
            return DOWNLOAD_NO_SOFT;            
        }
        */
        $down_url = $soft_file_data[$softid]['url'];
        $package = $soft_file_data[$softid]['package'];
		$category = $soft_file_data[$softid]['category_id'];
        $down_url= getApkHost($soft_file_data) .$down_url;
        $docurl = $soft_file_data[$softid]['docurl'];
        $softname = $soft_file_data[$softid]['softname'];	

        $ip = onlineip();
		
        $threshold = 50;
         if($num == 0){
        	$threshold = 50;
        }else if ($num == 1){
        	$threshold = 5;
        }else{
        	return  array($down_url,$package,DOWNLOAD_IPBANNED,$category);
        }
        if($status == ""){
			if (isIpBanned($ip, $softid, $threshold)) {
				return array($down_url,$package,DOWNLOAD_IPBANNED,$category);
			}
		}

        return array($down_url,$package,$ip,$category, $docurl, $softname);
}

function get_ad_pic($type){
	$model = new GoModel();
	$option = array(
		'where' => array(
			'type' => $type,
			'status' => 1,
			'start_tm' => array('exp','< '.time().''),
			'end_tm' => array('exp','> '.time().'')
		),
		'table' => 'sj_advertise_picture'
	);
	$result = $model -> findOne($option);
	return $result;
}

function sub_str_cn($string, $length, $append = '...') {
    if ( $length <= 0 ) {
        return '';
    }

    $string = trim($string);
    $strlength = strlen($string);
    if ($length == 0 || $length >= $strlength) {
        return $string;
    } elseif ($length < 0) {
        $length = $strlength + $length;
        if ($length < 0) {
            $length = $strlength;
        }
    }
    $newstr = '';
    $j = 0;
    for ($i = 0; $i < $strlength; $i ++) {
        $ord = ord ($string[$i]);
        if ($ord<192) {
            $newstr .= $string[$i];
        } elseif ($ord <224) {
            $newstr .= $string[$i]. $string[++$i];
            $j++;
        } else {
            $newstr .= $string[$i]. $string[++$i]. $string[++$i];
            $j++;
        }
        $j++;
        if ($j>=$length) break;
    }
    if ($append && $newstr != $string) {
        $newstr .= $append;
    }
    return $newstr;
}

function  imgurl_trans($params,$url){
    if(!defined('IMGURL_TRANS') || IMGURL_TRANS != 1) return $url;
    $pattner = "/^http:\/\/([a-z0-9\.]+)\/(.*)$/i";
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

function send_webmarket_mail($email,$dev_name,$title,$content){
	$email_info = array('email'=>$email,'name'=>$dev_name,'subject'=>$title,'content'=>$content);
	$tmp = _http_post_email($email_info);
	$rt = array();
	$ret = json_decode($tmp['ret'],true);
	if($ret['code']<0) {
		return array(
			'error' => $ret['code'],
			'msg' => $ret['msg'],
		);
	} else {	//成功进入发送队列
		$rt['error'] = $ret['code'];
		$rt['msg'] = $ret['msg'];
	}

	return $rt;
}
function _http_post_email($vals) {
	if(preg_match('/^192\.168\.0/i',$_SERVER['SERVER_ADDR']) || $_SERVER['SERVER_ADDR']=='10.0.2.15'|| $_SERVER['SERVER_ADDR']=='114.247.222.131') {
		//$url = 'http://192.168.0.74:92/service.php';
		//$host = 'Host: localhost';
		//$url = 'http://118.26.203.22/service.php';
		$url = 'http://42.62.4.183/service.php';
		$host = 'Host: mail.goapk.com';
	} else {
		$url = 'http://192.168.1.143/service.php';
		$host = 'Host: mail.goapk.com';
	}

	$url .= '?key=f3778b2d59c276233de4f73b2ebf46ea';

	$res = curl_init();
	curl_setopt($res, CURLOPT_URL, $url);
	curl_setopt($res, CURLOPT_TIMEOUT, 5);
	curl_setopt($res, CURLOPT_HTTPHEADER, array($host));
	curl_setopt($res, CURLOPT_POST, true);
	curl_setopt($res, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($res, CURLOPT_POSTFIELDS, $vals);
	$result = curl_exec($res);
	$http_code = curl_getinfo($res,CURLINFO_HTTP_CODE);
	curl_close($res);
	if(!defined('LOG')) define('LOG','/tmp/');
	$log_file = strtoupper(substr(PHP_OS,0,3))=='WIN' ? 'e:/email.log' : LOG.date('Y-m-d').'/email.log';
	if(!is_dir(dirname($log_file))) mkdir(dirname($log_file),0777,true);
	file_put_contents($log_file,"post|{$url}|{$host}|{$vals['email']}|{$vals['subject']}|{$vals['content']}|{$http_code}|".date('Y-m-d H:i:s')."\n",FILE_APPEND);

	return array(
		'ret' => $result,
		'http_code' => $http_code,
	);
}


function jsmsg($msg,$url='') {
	global $tplObj;
	header('Content-type:text/html;charset=utf-8');
	$tplObj->assign ( 'msg', $msg );
	$tplObj->assign ( 'url', $url );
	$tplObj->display ( 'dev_jsmsg.html' );
	
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
function  formatFileSize($len,$keepZero) {
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


//反网络病毒白名单
function get_white_list(){
	$pack_name = array(
		"cn.goapk.market"=>1,
		"com.zhiyoo"=>1,
		"com.netqin.ps"=>1,
		"com.walnutsec.pass"=>1,
		"com.anguanjia.safe"=>1,
		"com.stonete.qrtoken"=>1,
		"com.nqmobile.antivirus20"=>1,
		"com.android.bankabc"=>1,
		"com.anguanjia.security"=>1,
		"cn.kuwo.player"=>1,
		"com.anguanjia.safe.optimizer"=>1,
		"com.miercnnew.app"=>1,
		"com.anguanjia.safe.battery"=>1,
		"com.autonavi.minimap"=>1,
		"sogou.mobile.explorer"=>1,
		"com.eshore.ezone"=>1,
		"com.egame"=>1,
		"cn.opda.a.phonoalbumshoushou"=>1,
		"com.ijinshan.browser_fast"=>1,
		"com.xiaoao.tinytroopers2.anzhi"=>1,
		"com.ijinshan.duba"=>1,
		"com.xiaoao.car3d5.anzhi"=>1,
		"com.ijinshan.kbatterydoctor"=>1,
		"com.xiaoao.moto3d2.anzhi"=>1,
		"com.xiaoao.ultraman.anzhi"=>1,
		"com.ijinshan.zhuhai.k8"=>1,
		"com.xiaoao.lddogfighter.anzhi"=>1,
		"com.xiaoao.riskSnipe.anzhi"=>1,
		"com.kugou.android"=>1,
		"com.xiaoao.riskSnipe2"=>1,
		"com.sxiaoao.moto3dOnline"=>1,
		"com.xiaoao.car3d4.anzhi"=>1,
		"com.imohoo.shanpao"=>1,
		"com.qihoo.cleandroid_cn"=>1,
		"cn.emagsoftware.gamehall"=>1,
		"com.qihoo.mkiller"=>1,
		"com.baidu.appsearch"=>1,
		"com.qihoo360.mobilesafe"=>1,
		"com.qihoo360.mobilesafe.opti"=>1,
		"com.qihoo360.mobilesafe.strongbox"=>1,
		"com.tencent.android.qqdownloader"=>1,
		"com.tencent.mtt"=>1,
		"com.tencent.qqpimsecure"=>1,
		"kvpioneer.cmcc"=>1,
		"project.rising"=>1,
		"com.antiy.avlpro"=>1,
		"com.eshore.ezone.whole"=>1,
		"com.sxiaoao.game.ddz2"=>1,
		"com.migu.game.ddzzr"=>1,

	);
	return $pack_name;
}

//反网络病毒v开发者的软件
function get_v_list() {
	$v_pack_name = array(
		'com.hiyoulin.app'=>1,
		'cn.com.syan.trusttracker'=>1
	);
	return $v_pack_name;
}


function rand_code_md5(){
    return md5(rand(1,100000).microtime());
}

function get_qrimg($softid,$package,$SOFT_PROMULGATE_TIME,$iconurl=''){
	//二维码
	$date_times=preg_replace("/[^\d]/","",$SOFT_PROMULGATE_TIME);
	$publish_tm = strtotime($date_times);

	$url = 'http://m.anzhi.com/share_'. $softid. '.html?azfrom=anzhi&host=details&pkg='. $package. '&flag=1&aztype=qr';
	return 'http://nf.anzhi.com/QRcode.php?softid='.$softid.'&url='.urlencode($url)."&time=".$publish_tm."&key=".md5($url."anzhi.com/QRcode")."&iconurl=".urlencode($iconurl);
}
