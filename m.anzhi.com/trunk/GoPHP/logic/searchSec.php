<?php
/*
    数据过滤logic
*/
class GoSearchSecLogic
{
	public $filter_logic;

    function __construct($parameter)
    {
        if (isset($parameter['filter_logic']) && is_object($parameter['filter_logic'])) {
            $this->filter_logic = $parameter['filter_logic'];
        }
    }

    function search($search_key, $pid = '')
    {
		if ($search_key == '') return false;
		
		if (!empty($pid)) {
			$index = array(
				'search_key' => $search_key,
				'pid' => $pid,
			);
		} else {
			$index = $search_key;
		}
		//针对qq2012显示历史版本
		$special_keys = array(
			'QQ2012' => 'qq2012',
			'手机QQ2012' => 'qq2012',
			'2012' => 'qq2012',
			'2012QQ' => 'qq2012',
			'手机qq2012' => 'qq2012',
			'2012qq' => 'qq2012',
		);
		if (isset($_GET['DEBUG']) && isset($special_keys[$search_key])) {
			$md5_key = md5(strtolower($special_keys[$search_key]));
		} else {
			$md5_key = md5(strtolower($search_key));
		}
		
		$order_cache_key = "SEARCH_KEY_SOFTID_ORDER{$md5_key}";
		if (!empty($pid)) {
			$order_cache_key = "SEARCH_KEY_SOFTID_ORDER{$md5_key}_{$pid}";
		}
		$search_obj = load_model('searchSec');
		$res = $search_obj->get_search_result($search_key);
		$softid_arr = $res['softid_arr'];
		//return $search_obj;
		$softid_arr = $search_obj->data_info['softid_arr'];
		//return $softid_arr;
        if ($this -> filter_logic) {
        	$softid_arr = $this->filter_logic->filter_softid($softid_arr, isset($softid_arr[0]), $order_cache_key);
        }

		return $softid_arr;
    }
}
