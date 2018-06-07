<?php
/*
    数据过滤logic
*/
class GoFilterLogic
{
	public $filter_option = array();
    function __construct($parameter)
    {
        if ($parameter['filter_option']) {
            $this->filter_model = pu_load_model_obj('pu_filter', $parameter['filter_option']);
			$this->filter_option = $parameter['filter_option'];
        }
    }

    function filter_softid($id, $flip = true, $order_key = '', $is_return_id = true)
    {
		if (empty($id)) return $id;
        if (!$this->filter_model) {return $id; }
		
        if (empty($id))
            return array();
		if (!is_array($id)) {
			$id = array($id => 0);
			$flip = false;
		}	
        $ng = $this->filter_model->data_info['exclude'][$this->filter_model->filter_hash_key];
        if ($flip) {
			$id_fllip = array_flip($id);
		} else {
			$id_fllip = $id;
		}
		unset($id);
		$cache = GoCache::getCacheAdapter('memcached');
		
		if (isset($filter['order_key'])) {
			$order_cache_key = $filter['order_key'];
			unset($filter['order_key']);
		}
		
		if (!empty($order_key)) {
			$order_cache_key = $order_key;
		}
		
		$product = isset($filter['product']) ? $filter['product'] : 1;
		if ($order_cache_key) {
			if (strstr($order_cache_key,'SEARCH_KEY_SOFTID_ORDER') === false) {
				$rand_cache_key = 'rand:'. $order_cache_key;
				$extent_cache_key = "p{$product}_{$order_cache_key}_extent_list";
				$cache_key = array(
					$rand_cache_key,
					$extent_cache_key
				);
				$top_cache = $cache->gets($cache_key);
				
				//随机排序配置
				if (!empty($top_cache[$rand_cache_key])) {
					$rand_cache = $top_cache[$rand_cache_key];
				}
				
				//运营区间广告位
				if (!empty($top_cache[$extent_cache_key])) {
					//获取区间列表
					$extent_list = $top_cache[$extent_cache_key];
					$extent_soft_key = array();
					
					//获取区间软件列表缓存key
					foreach($extent_list[0] as $val) {
						$extent_soft_key[] = "category_extent_soft_list_{$val['extent_id']}";
					}
					//获取区间软件列表
					$result = $cache->gets($extent_soft_key);
					if ($result) {
						$extent_soft_list = array();
						foreach($result as $key => $val) {
							$extent_id = str_replace('category_extent_soft_list_', '', $key);
							if(!isset($extent_soft_list[$extent_id])){
								$extent_soft_list[$extent_id] = array();
							}
							foreach ($val as $sid => $prob) {
								$softids[$sid] = $extent_id;
								$id_fllip[$sid] = $prob;
								$extent_soft_list[$extent_id][$sid] = $prob;
							}
						}
					}
				}
				
			} else {
				$order_cache = $cache->get($order_cache_key);
				foreach($order_cache as $k => $v) {
					$id_fllip[$k] = $v;
				}
			}
		}
		
        $foreachlist = array();

		if (count($ng) < count($id_fllip)) {
			$foreachlist = $ng;
			$tmplist = $id_fllip;
		} else {
			$foreachlist = $id_fllip;
			$tmplist = $ng;
		}
		unset($ng);
        foreach ($foreachlist as $softid => $info) {
        	if (isset($tmplist[$softid])) {
        		unset($id_fllip[$softid]);
        	}
        }
		
		$include = $this->filter_model->data_info['include'][$this->filter_model->filter_hash_key];
		$id_include = array();
		if(!empty($include)){
			$len_a = count($id_fllip);
			$len_b = count($include);
			
			if ($len_a <= $len_b) {
				foreach ($id_fllip as $k => $v) {
					if (!isset($include[$k])) {
						unset($id_fllip[$k]);
					}
				}
			} else {
				$tmp_keys = array_intersect_key($id_fllip, $include);
				$id_include = array();
				foreach ($tmp_keys as $k => $v) {
					$id_include[$k] = $id_fllip[$k];
				}
				$id_fllip = $id_include;
			}
		}

		unset($tmplist);
		unset($foreachlist);
		unset($include_softid);
		unset($for_insert_key);
		unset($id_include);
		
        $id_fllip = $this->tripMultiSoftid($id_fllip, false);
		
		if($rand_cache) {
			load_helper('sort');
			$temp1 = array_slice($id_fllip, 0, $rand_cache, true);
			$temp2 = array_slice($id_fllip, $rand_cache, null, true);
			$temp1 = array_shuffle($temp1);
			$id_fllip = $temp1 + $temp2;
		}
		
		if ($extent_soft_list) {
			load_helper('sort');
			$new_extent_soft_list = array();
			foreach ($softids as $sid => $val) {
				if (!isset($id_fllip[$sid])) {
					unset($softids[$sid]);
				}
			}
			//对过滤后的结果进行梳理
			foreach ($softids as $sid => $extent_id) {
				if(!isset($new_extent_soft_list[$extent_id])){
					$new_extent_soft_list[$extent_id] = array();
				}
				$new_extent_soft_list[$extent_id][$sid] = $extent_soft_list[$extent_id][$sid];
			}
			//过滤后的结果进行概率计算
			$result = $this->getExtentSoftid($extent_list, $new_extent_soft_list, 0, $filter_option);
			
			//对计算后的结果做排序
			$order_cache = array();
			foreach ($result as $extent_id => $val) {
				$i = intval($val['rank']);
				foreach ($val['list'] as $v) {
					list($softid, $prob) = $v;
					$order_cache[$softid] = $i;
					$list[$softid] = $i;
					$i++;
				}
			}
		}
		
		if ($order_cache) {
			load_helper('sort');
			$id_fllip = insertOrderList($id_fllip, $order_cache);
		}
		if($is_return_id) {
			$id_fllip = array_keys($id_fllip);
		}
		return $id_fllip;
    }
	
	function tripMultiSoftid($id_fllip, $return_keys=true)
	{
		$len1 = count($id_fllip);
		if ($len1 == 1) {
			return $return_keys ? array_keys($id_fllip) : $id_fllip;
		}
		if (empty($this->filter_model->data_info['multipackage'])) {
			$this->filter_model->initFilterCache();
		}
		
		$result = array();
		$multi_keys = array_intersect_key($this->filter_model->data_info['multipackage'], $id_fllip);
		if ($multi_keys) {
			foreach($multi_keys as $id => $p) {
				if (isset($result[$p])) {
					unset($id_fllip[$id]);
				} elseif(isset($id_fllip[$id])) {
					$result[$p] = $id;
				}
			}
		}
		return $return_keys ? array_keys($id_fllip) : $id_fllip;		
	}

	function getExtentSoftid($extent_list, $extent_soft_list, $id, $filter_option = array())
	{
		$data = $extent_list[$id];
		load_helper('sort');
		$softlist = array();
		$unused = array();

		foreach($data as $val) {
			$i = 0;
			if (!empty($val['cid']) && $val['cid'] != $filter_option['channel']) continue; 
			if (!empty($val['oid']) && $val['oid'] != $filter_option['model_oid']) continue;
			$extent_id = $val['extent_id'];
			$unused[$extent_id] = array();
			$extent_tmp = $tmp = array();

			if ($val['type'] == 1) {
				$extent_data[$extent_id] = array();
				$extent_data[$extent_id]['rank'] = $val['rank'];
				$extent_data[$extent_id]['list'] = array();
				//1.普通区间
				$extent_tmp = $extent_soft_list[$extent_id];
				if (!$val['extent_size'] || !$extent_tmp) continue;

				$tmp = ad_generate($val['extent_size'], array_values($extent_tmp));
				shuffle($tmp);
				foreach($tmp as $sid) {
					$extent_data[$extent_id]['list'][] = $extent_tmp[$sid];
					unset($extent_tmp[$sid]);
				}

			} elseif ($val['type'] == 2) {
				//2. 活动区间
				$res = $this->getExtentSoftid($extent_list, $extent_soft_list, $extent_id, $filter_option);
				foreach($res as $k => $extent_item) {
					$extent_data[$k] = array();
					$extent_data[$k]['rank'] = $val['rank'];
					$extent_data[$k]['list'] = array();
					foreach($extent_item['list'] as $v) {
						$extent_data[$k]['list'][] = $v;
					}
				}
			}
		}
		return $extent_data;
	}
	
    public function filterByPackageKey($data) {
		$ng = $this->filter_model->data_info['exclude'][$this->filter_model->filter_hash_key];
		if(!empty($this->filter_model->data_info['include'][$this->filter_model->filter_hash_key])){
			$gd = (array)$this->filter_model->data_info['include'];
		}
		if (empty($data)) return array();
		$tmp_data = array();
		foreach($data as $p => $v){
			foreach($v as $id => $vv) {
				if (isset($ng[$id])) {
					unset($data[$p][$id]);
					continue;
				}
				if ($gd && !isset($gd[$id])) {
					unset($data[$p][$id]);
					continue;
				}
			}
			if (empty($data[$p])) {
				unset($data[$p]);
				continue;
			}
			
			foreach($data[$p] as $id => $vv) {
				$tmp_data[$id] = $p;
			}
		}
		$r = $this->tripMultiSoftid($tmp_data);
		$result = array();
		foreach ($r as $id) {
			$p = $tmp_data[$id];
			$result[$p] = $id;
		}
        return $result;
    }
}

