<?php
class GoSjsoftModel extends GoModel
{
     public $table = 'sj_soft';
     public function getSoftListByMethod($method, & $option = array())
    {
         // 从缓存里面获取指定的id列表
        $softids = $this -> getCachedSoftList($method, $option);
         // if (array_key_exists('pagesize', $option)) $softids = array_slice($softids, $option['offset'], $option['pagesize']);
        $query_option = array(
            'where' => array(),
             'field' => 'costs,score,dev_name,msgnum,intro,package, safe, softid, intro, IF(version_code, version_code, 0) AS version_code,
             IFNULL(version, "new") AS version, softname, score, total_downloaded, last_refresh, upload_tm,
             category_id',
            );
         $result = array();
         $sort = 1;
		 $goapk_img_host = getIconHost();
         foreach ($softids as $softid){
             $query_option['where'] = array('softid' => $softid);
             $soft = $this -> findOne($query_option);
             $file_option = array(
                'where' => array(
                    'softid' => $softid,
                    'package_status' => 1,
                    ),
                 'table' => 'sj_soft_file',
                 'field' => 'iconurl, url,filesize',
                );
             $file = $this -> findOne($file_option);
             $soft['iconurl'] = $goapk_img_host . $file['iconurl'];
             $soft['apkurl'] = getApkHost($soft) . $file['url'];
             $soft['filesize'] = $file['filesize'];
             $result[$softid] = $soft;
             $sort++;
             }
         return $result;
         }
     public function getCachedSoftList($method, & $option = array()){
         load_helper('mysqlcache');
         //$softids = getMysqlCache($method, $option);
         $cache_list = array();
         if ($method == "type") {
            $cid = $option['catalogid'];
            if (empty($option["order_by"])) $option["order_by"] = "new";
            $method = "category_" . $option["order_by"];
            $cache_list = getMysqlCache($method, $cid);
        } elseif ($method == "suggest") {
            $cache_list = getMysqlCache('suggest_list');
            $Gogmarket = $this -> getGomarket();
            array_unshift($cache_list,$Gogmarket['softid']);
        } elseif ($method == "suggest_1") {
            $cache_list = getMysqlCache('suggest_list_1');
            $Gogmarket = $this -> getGomarket();
            array_unshift($cache_list,$Gogmarket['softid']);
        } elseif ($method == "suggest_2") {
            $cache_list = getMysqlCache('suggest_list_2');
            $Gogmarket = $this -> getGomarket();
            array_unshift($cache_list,$Gogmarket['softid']);
        } elseif ($method == "hot") {
            $cache_list = getMysqlCache('hot_list');
        } elseif ($method == "hot_1d") {
            $cache_list = getMysqlCache('hot_list_1d');
        } elseif ($method == "hot_1d_1") {
            $cache_list = getMysqlCache('hot_list_1d_1');
        } elseif ($method == "hot_1d_2") {
            $cache_list = getMysqlCache('hot_list_1d_2');
        } elseif ($method == "hot_7d") {
            $cache_list = getMysqlCache('hot_list_7d');
        } elseif ($method == "hot_7d_1") {
            $cache_list = getMysqlCache('hot_list_7d_1');
        } elseif ($method == "hot_7d_2") {
            $cache_list = getMysqlCache('hot_list_7d_2');
        } elseif ($method == "hot_30d") {
            $cache_list = getMysqlCache('hot_list_30d');
        } elseif ($method == "hot_30d_1") {
            $cache_list = getMysqlCache('hot_list_30d_1');
        } elseif ($method == "hot_30d_2") {
            $cache_list = getMysqlCache('hot_list_30d_2');
        } elseif ($method == "special") {
            $cache_list = getMysqlCache('special', $option["catalogid"]);
        } elseif ($method == "new") {
            $cache_list = getMysqlCache('new_list');
        } elseif ($method == "rank") {
            $cache_list = getMysqlCache('rank_list');
        } elseif ($method == "subrank") {
            $cache_list = getMysqlCache('hot_list', $option["catalogid"]);
        } elseif ($method == "subrank_with_day") {
            $cache_list = getMysqlCache('hot_' . $option["day"] . 'd_list' . $option["catalogid"]);
        } elseif ($method == "subnew") {
            $cache_list = getMysqlCache('new_list', $option["catalogid"]);
        } else {
            $cache_list = getMysqlCache('rank_list');
        }
		 $cache_list = $this->filterSoftId($cache_list);
         $option["total"] = count($cache_list);
         if (array_key_exists('pagesize', $option)) $cache_list = array_slice($cache_list, $option['offset'], $option['pagesize']);
         else $list = array_slice($cache_list, 0, count($cache_list));
         return $cache_list;
         }

     public function getSoftBySoftId($table, $softid)
    {
         $option = array(
            'where' => array(
                'A.softid' => $softid,
                ),
             'index' => 'softid',
            );
         return $this -> getDataList('sj_soft', $option);
         }

         public function getSoftByPackage($package, $extra_option, $filter = true)
         {
         	$option = array (
	            'where' => array (
	                'package' => $package,
	                 'hide' => 1,
	                 'status' => 1,
	         	),
	             'index' => 'softid',
         	);
         	if (!empty($extra_option)) {
         		foreach ($extra_option as $key => $val) {
         			if (isset($option[$key]) && is_array($option[$key])) {
         				$option[$key] = array_merge($option[$key], $val);
         			}
         			else
         			$option[$key] = $val;
         		}
         	}
         	//TODO 过滤 OK
         	$list = $this -> getDataList('sj_soft', $option);
         	if ($filter) {
	         	$softids = array_keys($list);
	         	$softids = $this->filterSoftId($softids);
	         	return $list[$softids[0]];
         	} else {
         		return $list;
         	}
         }

     public function getDataList($table, $option = array())
    {
         $newoption = array();
         isset($option['where']) && $newoption['where'] = $option['where'] ;
         isset($option['index']) && $newoption['index'] = $option['index'] ;
         isset($option['join']) && $newoption['join'] = $option['join'] ;
         isset($option['returnRs']) && $newoption['returnRs'] = $option['returnRs'] ;
         isset($option['limit']) && $newoption['limit'] = $option['limit'] ;
         isset($option['offset']) && $newoption['offset'] = $option['offset'] ;
         isset($option['order']) && $newoption['order'] = $option['order'] ;
         isset($option['field']) && $newoption['field'] = $option['field'] ;
         isset($option['_user_defined_condition']) && $newoption['_user_defined_condition'] = $option['_user_defined_condition'] ;
         $newoption['table'] = $table . ' AS A' ;

        /**
         * $option = array(
         * 'where' => $where,
         * 'table' => $this->table. ' AS A',
         *
         * 'join' => array(
         * 'sj_soft_file AS B' => array(
         * 'join_type' => 'left',
         * 'on' => array('B.softid', 'A.softid'),
         * ),
         * 'sj_soft_file AS B' => array(
         * 'join_type' => 'left',
         * 'on' => array('B.softid', 'A.softid'),
         * ),
         * ),
         * 'index' => 'softid',
         * 'field' => 'A.status, A.softid, A.softname, A.score, A.costs, A.safe,
         * A.msgnum, A.dev_name, A.package, A.version, A.version_code, A.hide,A.upload_tm,A.intro,
         * A.category_id, A.downloaded, B.iconurl, B.filesize, B.url',
         * );
         */
         return $this -> findAll($newoption);
         }

     public function getRenderedSoftList($method, & $option = array()){
         return $this -> getSoftListByMethod($method, $option);
         }
     public function getUserList($uids){
         $result = $this -> getDataList('pu_user', array(
                'index' => 'userid',
                 'where' => array(
                    'userid' => $uids
                    )
                ));
         return $result;
         }
     public function getLatestSoftByType(& $counter) {
     	/*
         load_helper('mysqlcache');
         $model = $_SESSION['MODEL_NO'];
         $model_list = getMysqlCache("adapted_models");
         if (!in_array($model, $model_list)) $model = "";
         $counter = getMysqlCache('latest_cnt', $model);
         if ($counter === false){
             $counter = getMysqlCache('latest_cnt');
             $model = "";
		 }
             //TODO 过滤
         return getMysqlCache('latest_soft', $model);
         */
     	load_helper('mysqlcache');
     	$counter = getMysqlCache('latest_cnt');
     	return getMysqlCache('latest_soft');

	 }

     public function getSoftSuggest($package, $limit = 5, $category_id = 0){
     	//TODO 过滤 OK
         $exclude = array();
         $exclude[] = "com.tencent.qq";
         $exclude[] = "com.uc.browser";
         $exclude[] = "com.sohu.inputmethod.sogou";
         if ($package){
             $exclude[] = $package;
             }
         $popular = GoCache :: get('suggest_lack');
         $category_good = GoCache :: get('suggest_good');
         $weekly_hot = GoCache :: get('suggest_week_hot');
         $weekly_new = GoCache :: get('suggest_week_new');
         // $popular = array();
        // $category_good = array();
        // $weekly_hot = array();
        // $weekly_new = array();
        $extra = array();
         $soft = load_model('soft');
         $option = array(
            'field' => array('category_id', 'tags'),
             'where' => array(
                'package' => $package
                )
            );
         $the_soft = $soft -> findOne($option);
         /*
         $tags = array();
         if ($the_soft){
             $tags = trim($the_soft['tags']);
             $tags = explode(' ', $tags);
             foreach ($tags as $idx => $val){
                 $tmp = trim($val);
                 if (strlen($tmp) == 0)
                     unset($tags[$idx]);
                 }
             }
         if (count($tags) > 0){
             foreach ($tags as $val){
                 $option = array(
                    'where' => array(
                        '_user_defined_condition' => "`tags` like '%${val}%'",
                        ),
                     'field' => 'package'
                    );
                 $data = $soft -> findAll($option);
                 if (!$data)
                     continue;
                 $extra[$data['package']] = 1;
                 }
             $extra = array_keys($extra);
             }
             */
         if ($imei){
             $option = array(
                'where' => array(
                    'imei' => $imei,
                    ),
                 'table' => 'sj_device_user',
                 'field' => 'id'
                );
             $res = $this -> findOne($option);
             $uid = $res['id'];
             if ($uid > 0){
                 $option = array(
                    'table' => 'sj_device_user_package',
                     'where' => array(
                        'id' => $uid
                        ),
                     'field' => 'packages'
                    );
                 $data = $soft -> findOne($option);
                 if ($data){
                     if($temp = json_decode($data['packages'])){
                         foreach ($temp as $val)
							 if (is_array($val)) {
								$p = $val[0];
							 } else {
								$p = $val;
							 }
							 $exclude[$p] = 1;
                         }
                     }
                 }
             }
         $result = array();
         $n = 0;
         foreach ($popular as $val){
             if (!isset($result[$val]) && !isset($exclude[$val])){
                 $result[$val] = 1;
                 $n += 1;
                 }
             if ($n >= 10)
                 break;
             }

         foreach ($extra as $val){
             if (!isset($result[$val]) && !isset($exclude[$val])){
                 $result[$val] = 1;
                 $n += 1;
                 }
             if ($n >= 30)
                 break;
             }
         $category = explode(',', $the_soft['category_id']);
         foreach ($category as $c){
             if (strlen($c) == 0 || !isset($category_good[$c]))
                 continue;
             foreach ($category_good[$c] as $val){
                 if (!isset($result[$val]) && !isset($exclude[$val])){
                     $result[$val] = 1;
                     $n += 1;
                     }
                 if ($n >= 50)
                     break;
                 }
             if ($n >= 50)
                 break;
             }
         foreach ($weekly_hot as $val){
             if (!isset($result[$val]) && !isset($exclude[$val])){
                 $result[$val] = 1;
                 $n += 1;
                 }
             if ($n >= 75)
                 break;
             }
         foreach ($weekly_new as $val){
             if (!isset($result[$val]) && !isset($exclude[$val])){
                 $result[$val] = 1;
                 $n += 1;
                 }
             if ($n >= 100)
                 break;
             }
         //foreach($packages as $idx => $val){
         //    if($val == $package)
         //        unset($packages[$idx]);
         //    }
         //$you_like = $this -> getDataList("sj_soft", array('where' => array('package' => $packages, 'status' => 1, 'hide' => 1),'index' => 'softid'));
         //$softids = array_keys($you_like);
         $youlike = array();
        if (!empty($result)) {
	         $packages = array_keys($result);
	        $model = load_model('commonFilter');
	        $softids = $model->getPackageToSoftId($packages);
	        $softids = $this->filterSoftId($softids);
	        load_config('utiltool');
	        $maybe_like_filters = getMysqlCache('maybe_like_filters');
	        if (!empty($maybe_like_filters)) $softids = array_diff($softids, $maybe_like_filters);
	        shuffle($softids);
	        if ($category_id) { //筛选分类，可能会有性能问题，以后要改 guokai add 2011-08-11
                static $category_id_arr;
     	        load_helper('mysqlcache');
                $category_id_arr[$category_id] =  $category_id_arr[$category_id]? $category_id_arr[$category_id] : getMysqlCache('category_new', $category_id);
                $softids = array_intersect($softids, $category_id_arr[$category_id]);
            }
            $softids = array_slice(array_values($softids), 0 ,$limit);
	        if (!empty($softids)) {
		         $you_like = $this -> getDataList("sj_soft", array('where' => array('softid' => $softids, 'status' => 1, 'hide' => 1),'index' => 'softid'));
		         $result = array();


		         shuffle($you_like);
		         foreach($you_like as $idx => $val){
		         	$infos = $this -> getDataList("sj_soft_file", array('where' => array('softid' => $val['softid'])));
		         	$youlike[$idx] = array_merge($val, $infos[0]);
		         }

	        }
         }
         return $youlike;
	}
     public function getSoftSuggestByDevice($package,$device,$limit) {
     	//TODO 过滤
         $suggests = $this -> getSoftSuggest($package,50);
         $devinfo = $this -> getDataList('pu_device', array('where' => array('dname' => $device, 'status' => 1)));
         $did = $devinfo[0]['did'];
         $option['did'] = $did;
         $pkg_ids = GoCache :: get('SOFTLIST_PG_D' . $did, 'memcached'); //适配通过的软件idzx
         $softids = array_values($pkg_ids);
         foreach($suggests as $idx => $info){
             if(!in_array($info['softid'],$softids)){
                  unset($suggests[$idx]);
             }
         }
         shuffle($suggests);
         if(count($suggests) >= $limit){
         $list = array_slice($suggests,0,$limit);
         }else{
           $diff = $limit - count($suggest);
           $fill = array_rand($softids,$diff);
           foreach($fill as $val){
            //$info =$this -> getDataList('sj_soft',array('where' => array('softid' => $softids[$val])));
            //$info1 = $this -> getDataList('sj_soft',array('where' => array('softid' => $softids[$val])));
            //$list[] = array_merge($info,$info1);
            $list[] = $this->getSoftBySoftId('sj_soft', $softid);
           }
         }
         return $list;
     }
     public function searchBySoft($table, & $option = array(), $category_id = 0){

         $apps = array();
         $list = array();
         $sorted_fields = array(
            '2' => 'softname',
             '3' => 'dev_name',
             '1' => 'intro',
             '4' => 'package'
            );
         $key = trim($option["key"]);
         $fields = $option["query_fields"];
		 $goapk_img_host = getIconHost();
         foreach ($sorted_fields as $label => $field){
             if (strpos($fields, $label . "") !== false){
                 $sql = "select * from " . $table . " where  $field like '%" . $key . "%' and hide =1 and status=1  and safe in (0,1) order by total_downloaded desc";
                 $result = $this -> query($sql);
                 while($app = $this -> fetch($result)){
                     $sql = "select * from sj_soft_file where softid=" . $app['softid']." and package_status = 1";
                     $file = $this -> fetch($this -> query($sql));
                     $file['iconurl'] = $goapk_img_host . $file['iconurl'];
                     $file['apkurl'] = getApkHost($app) . $file['url'];
                     $app = array_merge($app, $file);
                     $results[$app['softid']] = $app;
                     }
                 }
             }
         if(empty($results)) return "empty";
         //$results = array_values($results);
             $softids = array_keys($results);
             $softlist = array();
             $softids = $this->filterSoftId($softids);
             if ($category_id) { //筛选分类，可能会有性能问题，以后要改 guokai add 2011-08-11
                static $category_id_arr;
     	        load_helper('mysqlcache');
                $category_id_arr[$category_id] =  $category_id_arr[$category_id]? $category_id_arr[$category_id] : getMysqlCache('category_new', $category_id);
                $softids = array_intersect($softids, $category_id_arr[$category_id]);
             }
             foreach ($softids as $softid) {
             	$softlist[] = $results[$softid];
             }
         $option['total'] = count($softlist);
             if (array_key_exists('pagesize', $option)) {
             	$list = array_slice($softlist, $option['offset'], $option['pagesize']);
             } else {
             	$list = array_slice($softlist, 0, count($results));
             }

             return $list;
         }
     public function searchBySoftDevice($table, $device, & $option = array()){
//TODO 过滤 OK
             $apps = array();
             $list = array();
             $sorted_fields = array(
                '2' => 'softname',
                 '3' => 'dev_name',
                 '1' => 'intro',
                 '4' => 'package'
                );
             $key = trim($option["key"]);
             $fields = $option["query_fields"];
             $devinfo = $this -> getDataList('pu_device', array('where' => array('dname' => $device, 'status' => 1)));
             $did = $devinfo[0]['did'];
             $option['did'] = $did;
             $pkg_ids = GoCache :: get('SOFTLIST_PG_D' . $did, 'memcached'); //适配通过的软件id
             $ids_yes = array_values($pkg_ids);
			 $goapk_img_host = getImageHost();
             foreach ($sorted_fields as $label => $field){
                 if (strpos($fields, $label . "") !== false){
                     $sql = "select * from " . $table . " where  $field like '%" . $key . "%'  and hide =1 and status=1 and safe in (0,1) order by total_downloaded desc";
                     $result = $this -> query($sql);
                     while($app = $this -> fetch($result)){
                         if(!in_array($app['softid'], $ids_yes)){
                             continue;
                             }
                         $sql = "select * from sj_soft_file where softid=" . $app['softid'];
                         $file = $this -> fetch($this -> query($sql));
                         $file['iconurl'] = $goapk_img_host . $file['iconurl'];
                         $file['apkurl'] = getApkHost($app) . $file['url'];
                         $app = array_merge($app, $file);
                         $results[$app['softid']] = $app;
                         }
                     }
                 }
             if(empty($results)) return "empty";
             //$results = array_values($results);
             $softids = array_keys($results);
             $softlist = array();
             $softids = $this->filterSoftId($softids);
             foreach ($softids as $softid) {
             	$softlist[] = $results[$softid];
             }
             $option['total'] = count($softids);
             if (array_key_exists('pagesize', $option)) {
             	$list = array_slice($softlist, $option['offset'], $option['pagesize']);
             } else {
             	$list = array_slice($softlist, 0, count($results));
             }
             return $list;
	}
     public function getLatestMarkets(){
         $list = $this -> getDataList('sj_market', array(
                'where' => array(
                    'cid' => 0,
                     'status' => 1
                    )
                ));
         usort($list, create_function('$a,$b', 'if($a["version_code"]-$b["version_code"]) return $a["version_code"]<$b["version_code"]?1:-1;return $a["firmware"]<$b["firmware"]?1:-1;'));
         $last_version = 0;
         $result = array();
         foreach ($list as $idx => $market){
             $version = $market['version_code'];
             if ($version < $last_version) break;
             $last_version = $version;
             $result[] = $market;
             }
         return $result;
         }

     public function insertData($table, $option){
         $option['__user_table'] = $table;
         return $this -> insert($option);
         }
     public function updateData($table, $where, $data){
         $data['__user_table'] = $table;
         return $this -> update($where, $data);
         }
     public function getSoftListByDid($device, $method, & $option = array()){
     	//TODO 过滤 OK
         $devinfo = $this -> getDataList('pu_device', array('where' => array('dname' => $device, 'status' => 1)));
         $did = $devinfo[0]['did'];
         $option['did'] = $did;
         $pkg_ids = GoCache :: get('SOFTLIST_PG_D' . $did, 'memcached'); //适配通过的软件id
         //$pkg_ids = $this->filter
         $ids_yes = array_values($pkg_ids);
         if(isset($option['catalogid'])){
             $type['catalogid'] = $option['catalogid'];
             $softidarray = $this -> getCachedSoftList($method, $type);
             }else{
             $softidarray = $this -> getCachedSoftList($method);
             }
         $softids = array_intersect($softidarray, $ids_yes);
         $softids = $this->filterSoftId($softids);
         $option['total'] = count($softids);
         if (array_key_exists('pagesize', $option)) $softids = array_slice($softids, $option['offset'], $option['pagesize']);
         else $softids = array_slice($softids, 0, count($softids));

         $query_option = array(
            'where' => array(),
             'field' => 'package, safe, softid, intro, IF(version_code, version_code, 0) AS version_code, IF(version,version, "new") AS version, softname, score, total_downloaded, last_refresh, upload_tm,category_id',
            );
         $result = array();
         $sort = 1;
		 $goapk_img_host = getIconHost();
         foreach ($softids as $softid){
             $query_option['where'] = array('softid' => $softid);
             $soft = $this -> findOne($query_option);

             $file_option = array(
                'where' => array(
                    'softid' => $softid,
                    ),
                 'table' => 'sj_soft_file',
                 'field' => 'iconurl, url',
                );
             $file = $this -> findOne($file_option);
             $soft['iconurl'] = $goapk_img_host . $file['iconurl'];
             $soft['apkurl'] = getApkHost($soft) . $file['url'];
             $result[$softid] = $soft;
             $sort++;
             }
         return $result;
         }
     function getGomarket() {
         $package = 'cn.goapk.market';
         $softinfo = $this -> getDataList('sj_soft',array('where' => array('package' => $package,'hide'=>1,'status'=>1)));
         $softid   = $softinfo[0]['softid'];
         $file_info = $this -> getDataList('sj_soft_file',array('where' => array('softid' => $softid,'package_status' => 1)));
         $softinfo = array_merge($softinfo[0],$file_info[0]);
         return $softinfo;
     }

     function filterSoftId($softids)
     {
        $commonFilter = load_model('commonFilter');
        //webmarket 默认是3
        $filter_option = array(
			'abi' => 3
        );
        if ($this->exclude_cid) {
            $filter_option['channel'] = $this->exclude_cid;
        }

        $softids = $commonFilter->filterSoftId($softids, $filter_option, $this->exclude_package_arr);

        return $softids;
     }

     function filterPackage($packages)
     {
        $commonFilter = load_model('commonFilter');
        //webmarket 默认是3
        $filter_option = array(
			'abi' => 3
        );

        $packages = $commonFilter->filterPackage($packages, $filter_option);

        return $packages;
     }
}
