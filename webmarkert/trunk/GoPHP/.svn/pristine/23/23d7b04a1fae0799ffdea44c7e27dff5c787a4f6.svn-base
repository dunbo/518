<?php
/*
    数据过滤model
*/
class GoNewfilterModel
{
	protected $excludeSoftList = array();
	protected $excludeSecSoftList = array();
	protected $includeSoftList = array();
	protected $multiPackageInfo = array();
	protected $multiPackageInfoRealTime = array();
	protected $id_flip = array();
	protected $id_operate = array();
	protected $filter_redis;
	protected $filter_memcache;
	protected $filter_redis_write;
	protected $filter_redis_read;
	
	private $package;//"用户还下载" 需要用到的参数 软件包名
	
	function __construct()
	{
		/*
		 * 对于中心机来说，使用默认的缓存配置会将缓存写到各前端，读取的时候会随机从某台前端读取
		 * 因此中心机当过滤服务器时，需要单独配置下列配置，将缓存的读写限定在指定服务器中
		 */
		 
		$filter_redis_config = load_config('filter/redis');
		$filter_memcache_config = load_config('filter/memcache');
		$filter_redis_write_config = load_config('filter/redis_write');
		$filter_redis_read_config = load_config('filter/redis_read');
		if ($filter_redis_config) {
			$this->filter_redis = new GoRedisCacheAdapter($filter_redis_config);
		} else {
			$this->filter_redis = GoCache::getCacheAdapter('redis');
		}
		
		if ($filter_memcache_config) {
			$this->filter_memcache = new GoMemcachedCacheAdapter($filter_memcache_config);
		} else {
			$this->filter_memcache = GoCache::getCacheAdapter('memcached');
		}
		
		if($filter_redis_write_config){
			$this->filter_redis_write = new GoRedisCacheAdapter($filter_redis_write_config);		
		} else {
			$this->filter_redis_write = GoCache::getCacheAdapter('redis');

		}
		if($filter_redis_read_config){
			$this->filter_redis_read = new GoRedisCacheAdapter($filter_redis_read_config);	
		} else {
			$this->filter_redis_read = GoCache::getCacheAdapter('redis');
		}
	}
	
	function check_product_key($key, $product)
	{
		return $key;
		if (empty($product)) {
			$product = 1;
		}
		$need_trans_key = array(
			'HOT_LIST_F',
			'NEW_LIST_F',
			'RANK_LIST_F',
			'SOFTLIST_CATEGORY_SOFTID_F_',
			'EBOOK_TOP_HOT_LIST_3D_',
			'TOP_FREE_BOOK_DAY',
			'TOP_ALL_BOOK_DAY',
			'TOP_BOOK_WEEKLY_ALL',
			'HOT_LIST_',
			'LONG_DEV_', //相同作者 
			'SUGGEST_SOFTID', //猜你喜欢
		);
		
		foreach ($need_trans_key as $v) {
			if (stripos($key, $v)!==false) {
				$key = "PROD{$product}_{$key}";
				break;
			}
		}
		return $key;
	}
	
	function get_softlist($key, $offset, $size, $filter_option = array(), $cust_block_size = false, $is_return_id = true,$extra_option = array())
	{
		$product = 1;
		if (!empty($filter_option['product'])) {
			$product = $filter_option['product'];
		}
		$key = $this->check_product_key($key, $product);
		$start_at = microtime_float();
		$cache_time = 600;
		$md5 = md5(json_encode($filter_option));
		$extra_option['md5'] = $md5;
		if ($cust_block_size) {
			$block_size = $cust_block_size;
		} else {
			$block_size = $size * 10;
		}
		$return_flag = $is_return_id ? 1 : 0;
		$slice_flag = !$is_return_id;
		$hash_key = "CLIST:{$key}:{$return_flag}:{$md5}:HASH";
		$block_num = floor($offset / $block_size);
		$block_field = ($size > 0) ? "{$block_size}:{$block_num}" : 'all';

		$now = time();
		$day = date('Y-m-d', $now);
		$result = $this->filter_redis_read->gethash($hash_key,array($block_field,'total','expireAt','id_operate'));
		$total = !empty($result['total']) ? $result['total'] : 0;
		$ttl = !empty($result['expireAt']) ? $result['expireAt'] - $now : 0;
		$s = 0;
		$get_all = false;
		$all = array();
		$this->id_operate = array();
		if($ttl>0){	//	缓存未过期
			$this->id_operate = $result['id_operate'];
			if(!empty($result[$block_field])){	//	分块缓存存在则直接返回
				$list = $result[$block_field];
			}
			else{	//	分块缓存不存在
				$all = $this->filter_redis_read->gethash($hash_key,'all');
				if(!empty($all)){
					$list = array_slice($all, $block_num * $block_size, $block_size, $slice_flag);
					$this->filter_redis_write->sethash($hash_key,array($block_field=>$list));
				}
				else{	//	代码执行的时间差内，缓存已经失效
					file_put_contents('/tmp/filter_miss_'. $day. '.log', "{$key} {$_POST['referer']}\n", FILE_APPEND);
					$get_all = true;
				}
			}
		}
		else{
			$get_all = true;
		}
		if($get_all){	//	缓存过期
			$s1 = microtime_float();
			$all = $this->filterSoftId($key, $filter_option, $is_return_id,array(),$extra_option);
			$total = count($all);
			$s2 = microtime_float();
			$s = $s2 - $s1;

			$save_datas = array(
				'all'=>$all,
				'total'=>$total,
				'expireAt'=>$now+$cache_time,
				'id_operate' => $this->id_operate
			);
			if ($size > 0) {
				//当前分块缓存
				$list = array_slice($all, $block_num * $block_size, $block_size, $slice_flag);
				$save_datas[$block_field] = $list;
				//当前第一块缓存
				$block_num_1 = 0;
				$block_field_1 = "{$block_size}:{$block_num_1}";
				if($block_field_1 != $block_field){
					$list_1 = array_slice($all, $block_num_1 * $block_size, $block_size, $slice_flag);
					$save_datas[$block_field_1] = $list_1;
				}
			} else {
				$list = $all;
			}
			//写入缓存和设置TTL
			$this->filter_redis_write->sethash($hash_key, $save_datas);
			$this->filter_redis_write->expireAt($hash_key, $now+$cache_time);	//	为了让key自动过期设置过期时间
			$ttl = $cache_time;
		}
		$need_list = array();
		if ($list) {
			if ($size > 0) {
				//根据偏移量$offset，计算出在分块缓存中的偏移$start
				$start = $offset - ($block_num * $block_size);
				$need_list = array_slice($list, $start, $size, $slice_flag);
			} else {
				$need_list = $list;
			}
		}
		$end_at = microtime_float();
		$spend = $end_at - $start_at;
		if ($spend > 0.5) {
			$time = date('Y-m-d H:i:s', $now);
			$sid = session_id();
			file_put_contents('/tmp/filter_slow_'. $day. '.log', "{$time} {$key} {$spend} {$sid} {$s}\n", FILE_APPEND);
		}
        $return = array($need_list, $total, $ttl);
        if(isset($filter_option['getadinfo'])){
			$return[] = $this->id_operate;
        }
		return $return;
	}
	
	function initFilterCache($filter = array())
	{
		$filter_key = md5(json_encode($filter));
		
		if (!isset($this->excludeSoftList[$filter_key])) {
			$f = load_model('pu_filtercache');
			$filterCache = $f->getFilterCache($filter);
			$this->multiPackageInfo = $filterCache['multi_package'];
			$this->multiPackageInfoRealTime = $filterCache['multi_package_real_time'];
			$this->excludeSoftList[$filter_key] = $filterCache['exclude'];
			$this->includeSoftList[$filter_key] = $filterCache['include'];
			$this->excludeSecSoftList[$filter_key] = $filterCache['exclude_sec'];
			$this->excludeChannelSoftList[$filter_key] = $filterCache['exclude_channel'];
		}
	}

    function filterSoftId($key, $filter = array(), $is_return_id = true,$person_ctrl_list = array(),$extra_option = array()) {
		
		
		if ($filter['order_key'] && $filter['order_key'] == 'ebook_order:top_3_hot') {
			$filter['order_key'] = 'top_3_hot';
		}

		if (is_string($key)) {
			$this->id_flip = $this->filter_redis->get($key);
		} else {
			$this->id_flip = $key;
		}
		
        if (empty($this->id_flip) && empty($filter['order_key']))
            return array();


		if (isset($filter['order_key'])) {
			$order_cache_key = $filter['order_key'];
			unset($filter['order_key']);
		}
		
		$exclude_softids = array();
		if (isset($filter['exclude_softids'])){
			$exclude_softids = $filter['exclude_softids'];			
			unset($filter['exclude_softids']);
		}
		$this->initFilterCache($filter);
		
		$filter_key = md5(json_encode($filter));
		
		foreach($exclude_softids as $softid){
			$this->excludeSoftList[$filter_key][$softid] = $softid;	
		}
		if (!empty($order_key)) {
			$order_cache_key = $order_key;
		}
		
		$product = isset($filter['product']) ? $filter['product'] : 1;
		$cache_key = array();
		$ad_type_map = array();
		$tmp_array = array();
		
		
		if ($order_cache_key) {
			
			if (strstr($order_cache_key,'SEARCH_ORDER:') !== false){
				$cache_key[] = $order_cache_key;
				if($filter['order_key_cache_data']){
					$all_cache = $filter['order_key_cache_data'];
				}else{
					$all_cache = $this->filter_memcache->gets($cache_key);
				}				
				$order_cache = array();
				foreach ($all_cache[$order_cache_key] as $softid => $val) {
					list($adtype, $adid, $pos, $be_info,$percent) = $val;
					list($package, $beidsoft, $channel_id) = $be_info;

					//人工运营数据添加行为id支持，处理渠道软件与行为id的冲突
					$this->checkBeidSoft($tmp_array, $channel_id, $beidsoft, $package, $softid, $filter, $filter_key);

					$order_cache[$softid] = $pos;
					$this->id_flip[$softid] = $pos;
					$ad_type_map[$softid] = array('search', $adtype, $adid,$percent);
				}
			} elseif ( strstr($order_cache_key,'ebook_order') !== false){
				$cache_key[] = $order_cache_key;
				$all_cache = $this->filter_memcache->gets($cache_key);
				$res = $all_cache[$order_cache_key];
				foreach($res as $k => $v) {
					$this->id_flip[$k] = $v[0];
					$order_cache[$k] = $v[0];
				}
			} else {
				$rand_cache_key = 'rand:'. $order_cache_key;
				$extent_cache_key = "p{$product}_{$order_cache_key}_extent_list";
				
				$cache_key[] = $rand_cache_key;

				//包名
				if($order_cache_key == "fixed_user_also_download" || $order_cache_key == "fixed_user_also_install_recommend") {
					$cache_key[] = "p{$product}_{$order_cache_key}_package_{$this->package}_extent_list";
				}
				
				//分类
				if(in_array($order_cache_key,array('fixed_user_also_download','fixed_user_also_download_recommend','fixed_user_also_install_recommend'))) {
					$memcache = GoCache::getCacheAdapterNew('cache/soft_memcached');
					$tmp_array = $memcache->get($this->package.':UPDATE');
					if($tmp_array) {
						$cat_id = $tmp_array[array_keys($tmp_array)[count($tmp_array)-1]][2];
						//$cat_id = intval(str_replace(',','',$cat_id));
						$tmp_tpl = '';
						$tmp_tpl = "p{$product}_{$order_cache_key}_class_{$cat_id}_extent_list";
						$cache_key[] = $tmp_tpl;
					}
				}
				
				//全局
				$cache_key[] = $extent_cache_key;
				
				$all_cache = $this->filter_memcache->gets($cache_key);

				//随机排序配置
				if (!empty($all_cache[$rand_cache_key])) {
					$rand_cache = $all_cache[$rand_cache_key];
				}
				$extent_soft_list = array();
				$extent_list_all = array( 0 => array());
				
				// start 遍历区间
				foreach($cache_key as $k1 => $v1) {
					if(empty($all_cache[$v1]) || $k1 == $rand_cache_key) {
						continue;
					}
					$extent_list = $all_cache[$v1];
					
					$extent_soft_key = array();
					
					//获取区间软件列表缓存key
					foreach($extent_list[0] as $k => $val) {
						if (!empty($val['push_area'])) {
							$areas = explode(';', $val['push_area']);
							$enable = false;
							foreach($areas as $v) {
								$location = explode(',', $v);
								$province = $location[0];
								$city = $location[1];
								if (!empty($province) && $province != $filter['province']) continue;
								if (!empty($city) && $city != $filter['city']) continue;
								
								$enable = true;
								break;
							}
							
							if (!$enable) {
								unset($extent_list[0][$k]);
								continue;
							}
						}
						$extent_list_all[0][$k] = $val;
						$extent_soft_key[] = "category_extent_soft_list_{$val['extent_id']}";
					}
					
					//获取区间软件列表
					$result = $this->filter_memcache->gets($extent_soft_key);
					
					if ($result) {
						//$extent_soft_list = array();
						foreach($result as $k => $val) {
							$extent_id = str_replace('category_extent_soft_list_', '', $k);
							if(!isset($extent_soft_list[$extent_id])){
								$extent_soft_list[$extent_id] = array();
							}
							foreach ($val as $sid => $v) {
								$softids[$sid] = $extent_id;
								//unset($this->excludeSecSoftList[$filter_key][$sid]);
								list($package, $beidsoft, $channel_id) = $v[6];

								//人工运营数据添加行为id支持，处理渠道软件与行为id的冲突
								$this->checkBeidSoft($tmp_array, $channel_id, $beidsoft, $package, $sid, $filter, $filter_key);

								if ($filter['checkgift']) {
									//精选礼包列表，排除没有礼包的运营数据
									if (isset($this->id_flip[$sid])) {
										$this->id_flip[$sid] = $v;
										$extent_soft_list[$extent_id][$sid] = $v;				
									}
								} else {
									$this->id_flip[$sid] = $v;
									$extent_soft_list[$extent_id][$sid] = $v;
								}
							}
						}
					}
				}
				// end 遍历区间
			}
		}

		
		
		if ($filter['be_info_index']) {
			foreach ($this->id_flip as $softid => $val) {
				if (!empty($val[$filter['be_info_index']])) {
					list($package, $beidsoft, $channel_id) = $val[$filter['be_info_index']];

					//人工运营数据添加行为id支持，处理渠道软件与行为id的冲突
					$this->checkBeidSoft($tmp_array, $channel_id, $beidsoft, $package, $softid, $filter, $filter_key);
				}

			}
		}
		foreach($this->excludeChannelSoftList[$filter_key] as $softid => $info){
			$this->excludeSoftList[$filter_key][$softid] = $info;	
		}
		
		foreach($this->excludeSecSoftList[$filter_key] as $softid => $info){
			$this->excludeSoftList[$filter_key][$softid] = $info;	
		}
		if (count($this->excludeSoftList[$filter_key]) < count($this->id_flip)) {
			foreach ($this->excludeSoftList[$filter_key] as $softid => $info) {
				if (isset($this->id_flip[$softid])) {
					unset($this->id_flip[$softid]);
				}
			}
		} else {
			foreach ($this->id_flip as $softid => $info) {
				if (isset($this->excludeSoftList[$filter_key][$softid])) {
					unset($this->id_flip[$softid]);
				}
			}
		}
		
		if(!empty($this->includeSoftList[$filter_key])){
			foreach ($include as $k => $v) {
				if (isset($this->id_flip[$k])) {
					$id_include[$k] = $this->id_flip[$k];
				}
			}
			
			foreach ($this->id_flip as $softid => $info) {
				if (!isset($this->includeSoftList[$filter_key][$softid])) {
					unset($this->id_flip[$softid]);
				}
			}
		}

		$this->tripMultiSoftid();
		
		if($rand_cache) {
			load_helper('sort');
			$temp1 = array_slice($this->id_flip, 0, $rand_cache, true);
			$temp2 = array_slice($this->id_flip, $rand_cache, null, true);
			$temp1 = array_shuffle($temp1);
			$this->id_flip = $temp1 + $temp2;
		} elseif (!empty($filter['random'])) {
			load_helper('sort');
			$this->id_flip = array_shuffle($this->id_flip);
		}
		
		if ($extent_soft_list) {
			$new_extent_soft_list = array();
			foreach ($softids as $sid => $val) {
				if (!isset($this->id_flip[$sid])) {
					unset($softids[$sid]);
				}
			}
			
			$result = array();
			$used = array();
			$this->getExtentSoftid($extent_list_all, $extent_soft_list, 0, $softids, $result, $used, $filter);
			$order_cache = array_flip($result);
		}
		

		if ($order_cache) {
			foreach ($softids as $k=>$v){
				if (!isset($order_cache[$k])) {
					unset($this->id_flip[$k]);
				}
			}
		}
		if($person_ctrl_list){
			foreach($person_ctrl_list as $softid => $val){
				if(!isset($this->id_flip[$softid])){
					unset($person_ctrl_list[$softid]);
				}
				if(isset($order_cache[$softid])){
					unset($person_ctrl_list[$softid]);
				}
			}
			//人工干预 位置调整
			$order_cache_pos_list = array_flip($order_cache);
			$len = count($person_ctrl_list);
			$pl = array_keys($person_ctrl_list);
			$step = min($len,3);
			$i =0;
			for($pos=4; $i<$step; $pos++){
				if(!isset($order_cache_pos_list[$pos])){
					$softid = $pl[$i];
					$order_cache[$softid] = $pos;
					$i++;
				}
			}
		}
		if($order_cache) $this->insertOrderList($order_cache);
        if(!empty($order_cache)){
			foreach ($order_cache as $softid => $pos) {
				$this->id_operate[$softid] = $ad_type_map[$softid];
			}
        }

		if($is_return_id) {
			$this->id_flip = array_keys($this->id_flip);
		}
        
		

		return $this->id_flip;
    }
	
	function tripMultiSoftid()
	{
		$len1 = count($this->id_flip);
		if ($len1 == 1) {
			return $this->id_flip;
		}

		$result = array();
		foreach ($this->multiPackageInfo as $id => $p) {
			if (isset($this->id_flip[$id])) {
				if (isset($result[$p])) {
					unset($this->id_flip[$id]);
				} elseif(isset($this->id_flip[$id])) {
					$result[$p] = $id;
				}
			}
		}
		$result = array();
		foreach ($this->multiPackageInfoRealTime as $id => $p) {
			if (isset($this->id_flip[$id])) {
				if (isset($result[$p])) {
					unset($this->id_flip[$id]);
				} elseif(isset($this->id_flip[$id])) {
					$result[$p] = $id;
				}
			}
		}
	}
	
	function getExtentSoftid($extent_list, $extent_soft_list, $id, & $softids, & $result, & $used, $filter_option = array())
	{
		load_helper('sort');
		$data = $extent_list[$id];

		foreach($data as $val) {
			$i = 0;
			if (!empty($val['cid']) && $val['cid'] != $filter_option['channel']) continue; 
			if (!empty($val['oid']) && $val['oid'] != $filter_option['model_oid']) continue;
			$extent_id = $val['extent_id'];
			$extent_tmp = $tmp = array();

			if ($val['type'] == 1) {
				//1.普通区间
				$extent_tmp = $extent_soft_list[$extent_id];
				foreach($extent_tmp as $k => $v) {
					//清理无效或者已使用数据
					if (!isset($softids[$k]) || $used[$k]) {
						unset($extent_tmp[$k]);
						continue;
					}
					//进行设备高低配的区分
					if (!empty($v[2]) && $v[2] !=1) {
						//适用旧版本
						if ($v[3]==1 && $filter_option['under_4410']==1) {
							continue;
						}

						//仅高配置
						if ($v[2] == 2 && $filter_option['end'] == 'l') {
							unset($extent_tmp[$k]);
							continue;							
						}

						//仅低配置
						if ($v[2] == 3 && $filter_option['end'] == 'h') {
							unset($extent_tmp[$k]);
							continue;							
						}
					}
				}
				if (!$val['extent_size'] || !$extent_tmp) continue;

				$tmp = ad_generate($val['extent_size'], array_values($extent_tmp));
				shuffle($tmp);
				$r = $val['rank'];
				foreach($tmp as $sid) {
					if (!isset($result[$r])) {
						$result[$r] = $sid;
						$used[$sid] = 1;
					}
					$r++;
				}

			} elseif ($val['type'] == 2) {
				//2. 活动区间
				$this->getExtentSoftid($extent_list, $extent_soft_list, $extent_id, $softids, $result, $used, $filter_option);
			}
		}
	}	

	function insertOrderList($order_list)
	{
		asort($order_list);
		$result = array();
		$start = 0;
		$old_pos = 0;
		
		foreach($order_list as $k => $v) {
			if (isset($this->id_flip[$k])) {
				unset($this->id_flip[$k]);
			} else {
				unset($order_list[$k]);
			}
		}
		$total = count($this->id_flip);
		foreach($order_list as $k => $v) {
			$len = $v - 1 - $old_pos;
			$old_pos = $v;
			$tmp = array_slice($this->id_flip, $start, $len, true);
			$tmp[$k] = $v;
			$start = $start + $len;
			$result = $result + $tmp;
		}
		if ($start < $total) {
			$result = $result + array_slice($this->id_flip, $start, null, true);
		}
		$this->id_flip = $result;
	}
	
    function search($search_key, $offset, $count, $filter_option = array(), $pid = '', $extra_option = array())
    {
		if ($search_key == '') return false;
		$pid = $filter_option['product'] ? $filter_option['product'] : 1;
		$md5 = md5(json_encode($filter_option));
		$key = md5(strtolower($search_key));
		$hash_key = "CSEARCH:{$key}:{$md5}:HASH";
		$cache_time = 600;
		$now = time();
		$result = $this->filter_redis_read->gethash($hash_key,array('all','len','expireAt', 'id_operate'));
		$total = !empty($result['len']) ? $result['len'] : 0;
		$ttl = !empty($result['expireAt']) ? $result['expireAt'] - $now : 0;
		if ($ttl<=0) {
			if (!empty($pid)) {
				$index = array(
					'search_key' => $search_key,
					'pid' => $pid,
				);
			} else {
				$index = $search_key;
			}
			$word = strtolower($search_key);
			$md5_key = md5($word);
			
			$order_cache_key = "SEARCH_ORDER:{$md5_key}:{$pid}";
			$cache_force_word = "SEARCH_KEY_WORD_FORCE:{$pid}";
			$cache_key[] = $order_cache_key;
			$cache_key[] = $cache_force_word;			
			$all_cache = $this->filter_memcache->gets($cache_key);			
			if($all_cache[$order_cache_key]){
				unset($all_cache[$cache_force_word]);
				$filter_option['order_key_cache_data'] = $all_cache;
			}else{
				if($all_cache[$cache_force_word]){
					foreach($all_cache[$cache_force_word] as $force_word){
						if(stristr($word,$force_word)){
							$md5_key_new = md5($force_word);
							$order_cache_key = "SEARCH_ORDER:{$md5_key_new}:{$pid}";
							break;
						}
					}
				}				
			}
			
			if (!empty($pid)) {
				//$order_cache_key = "SEARCH_ORDER:{$md5_key}_{$pid}";
			}
			$is_search_adapter = $this->filter_redis->gethash('all_search_adapter_name', $md5_key);
			
			if ($is_search_adapter) {
				$ex_filetr_key = 'search_adapter:'. $md5_key;
			} else {
				$ex_filetr_key = 'all_search_adapter';
			}
			$filter_option['order_key'] = $order_cache_key;
			$filter_option['ex_filetr_key'] = $ex_filetr_key;
			if (true) {
				$search_obj = load_model('searchSec');
				$res = $search_obj->get_search_result($search_key);
				$person_contrl_list = $res['person_contrl'];
				$softid_arr = $res['softid_arr'];
			} else {
				$search_obj = pu_load_model('pu_search', $index);
				$softid_arr = $search_obj->data_info['softid_arr'];
				//人工干预
				$person_contrl_list = $search_obj->data_info['person_contrl'];
				$softid_arr = array_flip($softid_arr);
			}

			$softid_arr = $this->filterSoftId($softid_arr, $filter_option,true,$person_contrl_list,$extra_option);
			$total = count($softid_arr);
			if($total){
				$this->filter_redis_write->sethash($hash_key, array(
					'all' => $softid_arr, 
					'len' => $total, 
					'expireAt '=> $now + $cache_time,
					'id_operate' => $this->id_operate,
					)
				);
				$this->filter_redis_write->expireAt($hash_key, $now+$cache_time);
			}
			$softids = array_slice($softid_arr, $offset, $count);
			$ttl = $cache_time;
		} else {
			$softids = array_slice($result['all'], $offset, $count);
			$this->id_operate = $result['id_operate'];
		}
		$return = array($softids, $total, $ttl);
        if(isset($filter_option['getadinfo'])){
			$return[] = $this->id_operate;
        }
        return $return;
    }
	
    function get_soft_maybe_like($package, $offset, $size, $filter_option = array(),$bi_option = array())
    {
	    
		
		$this->package = strtolower(trim($package));
		$use_bi = false;
	    if(!empty($bi_option)){
		    $start = microtime_float();
		    $bi_option['pkgname'] = $package;
		    $url_prefix = load_config('suggest/prefix');
		    $bi_option_query = http_build_query($bi_option);
		    $url = $url_prefix.'?'.$bi_option_query;
		    $ch = curl_init();
		    curl_setopt($ch, CURLOPT_URL, $url);
		    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		    $result = curl_exec($ch);
		    $errno = curl_errno($ch);
		    $msg = date('Y-m-d H:i:s')."\t".$bi_option['imsi']."\t".$bi_option['imei']."\t".$bi_option['mac']."\t".$bi_option['pkgname'];
		    $day = date('Y-m-d');
		    if(!$errno){
			    $info = curl_getinfo($ch);
			    $code = $info['http_code'];
			    if($code===200){
				    $bi_result = json_decode($result,true);
				    $bi_packages = array();
				    if(isset($bi_result['item'])&&!empty($bi_result['item'])){
					    foreach($bi_result['item'] as $val){
						    $bi_packages[] = $val['pkg'];
					    }
					    $use_bi = true;
		//			    $new_msg = $msg."\t".json_encode($bi_result['item'])."\n";
		//			    @file_put_contents("/tmp/bi_maybe_like_{$day}.log", $new_msg, FILE_APPEND);
				    }
				    $end = microtime_float();
				    $s = $end - $start;
				    if ($s > 0.5) {
					    $new_msg = $msg."\tspend {$s}\n";
					    @file_put_contents("/tmp/bi_maybe_like_slow_{$day}.log", $new_msg, FILE_APPEND);
				    }
			    }
			    else{
				    $new_msg = $msg."\tno errno\n";
				    @file_put_contents("/tmp/bi_maybe_like_error_{$day}.log", $new_msg, FILE_APPEND);
			    }
		    }
		    else{
			    $error_message = curl_error($ch);
			    $new_msg = $msg."\t$error_message\n";
			    @file_put_contents("/tmp/bi_maybe_like_error_{$day}.log", $new_msg, FILE_APPEND);
		    }
		    curl_close($ch);
	    }
		
		
	    $cache_timeout = 1800;
	    if(!$use_bi){
		    $md5 = md5(json_encode($filter_option));
		    $cache_key = "SUGGEST_NEW:{$md5}:{$package}";
		    $cache = $this->filter_redis_read->get($cache_key);

			if(empty($cache)){
			    $maybe_like_softid_arr = $this->get_suggest_id($package);
		    }
		    else{
			    $cache_timeout = $this->filter_redis_read->getKeyTTL($cache_key);
		    }
	    }
	    else{
		    $bi_softids = $this->get_pkg2id($bi_packages);
		    foreach($bi_softids as $bi_softid){
			    if(is_array($bi_softid)){
				    foreach($bi_softid as $sid){
					    $maybe_like_softid_arr[$sid] = $sid;
				    }
			    }
			    else{
				    $maybe_like_softid_arr[$bi_softid] = $bi_softid;
			    }
		    }
	    }
	    
		
		if($use_bi || (!$use_bi && empty($cache))){
		    $softid_res = $this->get_pkg2id($package);
		    $softids = $softid_res[$package];
		    $filter = array();
		    if (!is_array($softids)) {
			    $filter[] = $softid;
		    } else {
			    foreach ($softids as $softid) {
				    $filter[] = $softid;
			    }
		    }
		    $filter_option['ex_filetr_key'] = 'ONLY_SEARCH_SOFT';
		    $filter_option['exclude_softids'] = $filter;
		    $maybe_like_softid_arr = $this->filterSoftId($maybe_like_softid_arr, $filter_option, false);
			
		    //		$filter_option['random'] = true;
		    list($hot_suggest, $total, $ttl) = $this->get_softlist('HOT:SUGGEST', 0, 0, $filter_option, false, false);
		    load_helper('sort');                                              
		    $hot_suggest = array_shuffle($hot_suggest);
		    //		unset($filter_option['random']);

		    load_helper('sort');
		    foreach ($maybe_like_softid_arr as $softid => $v) {
			    unset($hot_suggest[$softid]);
		    }

		    foreach ($hot_suggest as $softid => $v) {
			    $maybe_like_softid_arr[$softid] = $v;
		    }
		    $cache = array_keys($maybe_like_softid_arr) ;
		    if(!$use_bi){
			    $this->filter_redis_write->set($cache_key, $cache, $cache_timeout);
		    }
	    }

	    $total = count($cache);
	    if ($size>0){
		    $return = array_slice($cache, $offset, $size);
	    } elseif($size == 0) {
		    $return = $cache;
	    }
	    return array($return, $total, $cache_timeout);
    }

    function get_soft_maybe_like_pad($softid, $filter_option=array(),$size=10){
	    $md5 = md5(json_encode($filter_option));
	    $product = $filter_option['product'] ? $filter_option['product'] : 1;
	    $cache_key = "prod4_suggestsec:{$softid}:{$md5}:{$size}";
	    $cache_timeout = 1800;
	    $suggest_id_arr = array();
	    $suggest_id_arr = $this->filter_redis_read->get($cache_key);
	    if(!$suggest_id_arr){
		    $pad_key = "SUGGEST_SOFTID";
		    $filter_option['random'] = 1;
		    $filter_option['exclude_softids'] = array($softid);
		    $suggest_id_arr = $this -> get_softlist($pad_key,0,$size,$filter_option);
		    $this -> filter_redis_write -> get($cache_key,$suggest_id_arr,$cache_timeout);
		    $this -> filter_redis_write -> get($cache_key.'_time',time(),$cache_timeout);
	    }
	    return $suggest_id_arr;
    }
    
    function get_suggest_id($package)
    {
        $cache_key = 'SOFT_SUGGEST_ID:'. $package;
		$suggest_id_arr = $this->filter_redis_read->get($cache_key);
        if (!$suggest_id_arr) {
            $key = $package . '_related';
            $cache = $this->filter_redis->get($key);
            $result = array();
            $n = 0;
            foreach ($cache as $p => $val) {
                if (!isset($result[$p]) && $p!=$package) {
                    $result[$p] = 1;
                    $n += 1;
                }
                if ($n >= 100)
                break;
            }
            
            $packages = array_keys($result);
            
            $suggest_id_arr = array();
            $package_all = $this->get_pkg2id($packages);
            
            foreach ($packages as $p) {
                if (!isset($package_all[$p])) continue;
                if (is_array($package_all[$p])) {
                    foreach ($package_all[$p] as $sid) {
                        $suggest_id_arr[$sid] = $sid;
                    }
                } else {
                    $suggest_id_arr[$package_all[$p]] = $package_all[$p];
                }
            }
            $this->filter_redis_write->set($cache_key, $suggest_id_arr, 600);
        }
        return $suggest_id_arr;
    }
    
    function get_pkg2id($package)
    {
        $package = (array) $package;
        $p_keys = array();
        foreach ($package as $p) {
            $p_keys[] = $p. ':ID';
        }
		$memcache = GoCache::getCacheAdapterNew('cache/soft_memcached');
        $package_all = $memcache->gets($p_keys);
        $result = array();
        $p_keys = array();
        foreach ($package as $p) {
            $k = $p. ':ID';
            if (isset($package_all[$k])) {
                $result[$p] = $package_all[$k];
            }
        }
        return $result;
    }

    function get_recommend_list_discovery($filter_option = array(),$bi_option = array())
    {
		$start = microtime_float();
	    $filter_option['ex_filetr_key'] = 'ONLY_SEARCH_SOFT';
            $url_prefix = load_config('recommend/prefix');
            $bi_option_query = http_build_query($bi_option);
            $url = $url_prefix.'?'.$bi_option_query;

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            $errno = curl_errno($ch);

            $day = date('Y-m-d');
		$end = microtime_float();
		$s = $end - $start;
		if ($s > 0.5) {
			$msg = date('Y-m-d H:i:s');
			$new_msg = "{$msg}\tspend {$s}\n";
			@file_put_contents("/tmp/bi_discovery_slow_{$day}.log", $new_msg, FILE_APPEND);
		}		
            if(!$errno){
                    $rs_bi = json_decode($result,true);
                    $bi_packages = $rs_bi['item'];

                    $bi_softids = $this->get_pkg2id($bi_packages);
                    foreach($bi_softids as $bi_softid){
                            if(is_array($bi_softid)){
                                    foreach($bi_softid as $sid){
                                            $bi_softid_arr[$sid] = $sid;
                                    }
                            }
                            else{
                                    $bi_softid_arr[$bi_softid] = $bi_softid;
                            }
                    }
                    $bi_softid_arr = $this->filterSoftId($bi_softid_arr, $filter_option, false);
                    $bisoftids = array_keys($bi_softid_arr);
                    $count = 100-count($bisoftids);
                    if($count<0){
                        $count=0;
                    }
				$start = $end;
				$end = microtime_float();
				$s = $end - $start;
				if ($s > 0.5) {
					$msg = date('Y-m-d H:i:s');
					$new_msg = $msg."\tspend {$s}\n";
					@file_put_contents("/tmp/bi_discovery_filter_slow_{$day}.log", $new_msg, FILE_APPEND);
				}
            }else{
                    $count=100;
                    $error_message = curl_error($ch);
                    $new_msg = $msg."\t$error_message\n";
                    @file_put_contents("/tmp/bi_discovery_error_{$day}.log", $new_msg, FILE_APPEND);
            }
            curl_close($ch);

	    return array($bisoftids, $count);
    }

    function checkBeidSoft(& $tmp_array, $channel_id, $beidsoft, $package, $sid, $filter, $filter_key)
    {
		if ($channel_id) {
			if ($beidsoft && !$tmp_array[$package]) {
				$tmp_array[$package] = $this->excludeChannelSoftList[$filter_key][$sid];
				unset($this->excludeChannelSoftList[$filter_key][$sid]);
			} elseif ($tmp_array[$package] && stripos(",{$filter['channel_soft_cid']},", $channel_id)) {
				$this->excludeChannelSoftList[$filter_key][$sid] = $tmp_array[$package];
			}
		}
    }
}


