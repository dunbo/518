<?php
/*
    分类软件列表model
    index格式: array('catalogid' => 'xxx', 'order_by' => ''xxx)
    data_info格式: array(softid1, softid2, softid3)
*/

class GoPu_typeModel extends GoPu_model
{
    public $use_mysql_cache = True;
    public $option;
    public $index_name = 'option';

    function __construct($index = array()) 
    {
        parent::__construct(__CLASS__, $index);
    }

    function make_cache_key($model_name, $index) 
    {
        $option = $index;
        if ($cid = $option['catalogid']) {
            if (!$option['order_by']) { $option['order_by'] = 'NEW'; }
			$cache_key = "SOFTLIST_CATEGORY_SOFTID_F_${cid}_". strtoupper($option['order_by']);
        }
        return $cache_key;
    }
	
	function get_cache_data_info()
	{
		$cache = GoCache::getCacheAdapter('redis');
		$this->data_info = $cache->get($this->cache_key);
		return $this->data_info;
	}
}
