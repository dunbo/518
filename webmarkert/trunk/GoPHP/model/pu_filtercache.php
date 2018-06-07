<?php
class GoPu_FiltercacheModel extends GoModel
{
	public $filter;
	public $makeKey;
	protected $filter_memcache;
	
	function __construct()
	{
		$filter_memcache_config = load_config('filter/memcache');
		if ($filter_memcache_config) {
			$this->filter_memcache = new GoMemcachedCacheAdapter($filter_memcache_config);
		} else {
			$this->filter_memcache = GoCache::getCacheAdapter('memcached');
		}
	}
	
	public function getFilterCache($filter, $makeKey)
	{	
		$exclude = array();
		$include = array();
				
		$cache_key = array(
			'MULTI_PACKAGE_INFO',
			'MULTI_PACKAGE_INFO_REAL_TIME',
		);
		$exclude_key = array();
		$include_key = array();
		//exclude cache key start
		//运营商过滤
		if(!empty($filter['model_oid'])){
			$k = 'SOFTLIST_NG_SOFTID_OID'.$filter['model_oid'];
			$cache_key[] = $k;
			$exclude_key[$k] = 1;
		}
		if (isset($filter['device'])) {
			$k = 'SOFTLIST_NG_SOFTID_D' . $filter['device'];
			$cache_key[] = $k;
			$exclude_key[$k] = 1;
			
			$k = 'SOFTLIST_PG_SOFTID_D' . $filter['device'];
			$cache_key[] = $k;
			$include_key[$k] = 1;
		}
		if (isset($filter['firmware'])) {
			$k = 'SOFTLIST_NG_SOFTID_F' . $filter['firmware'];
			$cache_key[] = $k;
			$exclude_key[$k] = 1;
			
			$k = "SOFTLIST_NG_SOFTID_F${filter['firmware']}_INTIME";
			$cache_key[] = $k;
			$exclude_key[$k] = 1;
		}
		if (isset($filter['channel']) && empty($filter['ignore_channel'])) {
			$k = 'SOFTLIST_NG_SOFTID_C' . $filter['channel'];
			$cache_key[] = $k;
			$exclude_key[$k] = 1;
			$philips_channel = load_config('philips_channel', 'filter');
			if (isset($philips_channel[$filter['channel']])) {
				//飞利浦渠道不显示电子书
				$philips_filter_key = load_config('philips_filter_key', 'filter');
				
				foreach ($philips_filter_key as $k) {
					$cache_key[] = $k;
					$exclude_key[$k] = 1;
				}
			} else {
				$k = 'SOFTLIST_PG_SOFTID_C' . $filter['channel'];
				$cache_key[] = $k;
				$include_key[$k] = 1;
			}
			//需要特殊处理的渠道 
			$special_channel_filter = load_config('special_channel_filter','filter');
			if(isset($special_channel_filter[$filter['channel']])){
				$special_chl_filter = $special_channel_filter[$filter['channel']];
				foreach($special_chl_filter as $sk){
					$cache_key[] = $sk;
					$exclude_key[$sk] = 1;
				}
			}
		}
		$exclude_channel_key = array();
		if (!empty($filter['channel_soft_cid'])) {
			$k = 'channel_ng_softid_' . $filter['channel_soft_cid'];
			$cache_key[] = $k;
			$exclude_channel_key[$k] = 1;
		} else {
			$k = 'all_channel_softid_ng';
			$cache_key[] = $k;
			$exclude_channel_key[$k] = 1;			
		}
		
		if (isset($filter['authorized']) && $filter['authorized'] > 0) {
			$k = 'SOFTLIST_NA_SOFTID';
			$cache_key[] = $k;
			$exclude_key[$k] = 1;
		}
		if (isset($filter['abi'])) {
			$k = 'SOFTLIST_NG_SOFTID_A' . $filter['abi'];
			$cache_key[] = $k;
			$exclude_key[$k] = 1;
			
			$k = "SOFTLIST_NG_SOFTID_A${filter['abi']}_INTIME";
			$cache_key[] = $k;
			$exclude_key[$k] = 1;
		}
		if (isset($filter['mannual'])) {
        	foreach ($filter['mannual'] as $name) {
        		$k = 'SOFTLIST_NG_MANNUAL_' . strtoupper($name). '_'. $makeKey;
				$cache_key[] = $k;
				$exclude_key[$k] = 1;
        	}
		}
		
		if (!isset($filter['ignore_global_exclude'])) {
			$k = "SOFTLIST_NG_GLOBAL";
			
			$cache_key[] = $k;
			$exclude_key[$k] = 1;
		}

		if (!empty($filter['ex_filetr_key'])) {
			$k = $filter['ex_filetr_key'];
			
			$cache_key[] = $k;
			$exclude_key[$k] = 1;
		}

		//获取区域展示黑名单
		$exclude_area_key = array();
		if(is_array($filter['exclude_area_key'])&&!empty($filter['exclude_area_key'])){
			foreach($filter['exclude_area_key'] as $filter_areas_key){
				$cache_key[] = $filter_areas_key;
				$exclude_area_key[] = $filter_areas_key;
			}
		}
		//exclude cache key end
		
		//include cache key start
    	if (isset($filter['app2sd'])) {
    		$k = 'SOFTLIST_GD_SOFTID_SD';
			$cache_key[] = $k;
			$include_key[$k] = 1;
		}
		//北京地区不展示
		if (!empty($filter['area_bj'])) {
			$k = 'SOFTLIST_EXCLUDE_AREA_BJ';			
			$cache_key[] = $k;
			$exclude_key[$k] = 1;
		}
		//646及以下屏蔽第3方软件		
		if (!empty($filter['other_platform'])) {
			$k = 'SOFTLIST_EXCLUDE_OTHER_PLATFORM';			
			$cache_key[] = $k;
			$exclude_key[$k] = 1;
		}
		
		//获取区域展示白名单
		$include_area_key = array();
		if(is_array($filter['include_area_key'])&&!empty($filter['include_area_key'])){
			foreach($filter['include_area_key'] as $filter_areas_key){
				$cache_key[] = $filter_areas_key;
				$include_area_key[] = $filter_areas_key;
			}
		}
		//include cache key end

		$cache = $this->filter_memcache->gets($cache_key);

		$exclude_area_data = array();
		foreach ($exclude_area_key as $k) {
			if (!empty($cache[$k])) {
                $exclude_area_data = $exclude_area_data + $cache[$k];
			}
		}
		//从区域展示黑名单中剔除白名单数据，得到最终区域展示黑名单用于过滤
		foreach($include_area_key as $k){
			foreach ($cache[$k] as $softid => $v) {
				unset($exclude_area_data[$softid]);
			}
		}
		$exclude_sec = array();
		foreach ($exclude_area_data as $softid => $v) {
			$exclude_sec[$softid] = $v;
		}
		foreach ($exclude_key as $k => $v) {
			if (!empty($cache[$k])) {
                $exclude = $exclude + $cache[$k];
			}
		}
		$exclude_channel = array();
		foreach ($exclude_channel_key as $k => $v) {
			if (!empty($cache[$k])) {
                $exclude_channel = $exclude_channel + $cache[$k];
			}
		}
		
		foreach ($include_key as $k => $v) {
			if (!empty($cache[$k])) {
                $include = $include + $cache[$k];
			}
		}		
		$multi_package = $cache['MULTI_PACKAGE_INFO'];
		$multi_package_real_time = $cache['MULTI_PACKAGE_INFO_REAL_TIME'];
		$result = array(
			'exclude' => $exclude,
			'exclude_sec' => $exclude_sec,
			'exclude_channel' => $exclude_channel,
			'include' => $include,
			'multi_package' => $multi_package,
			'multi_package_real_time' => $multi_package_real_time,
		);
		
		return $result;
	}
}

