<?php
/*
    热门软件model
    index格式 array('type' => 'xxx', 'catalogid' => 'xxx', 'day' => 'xxx')
    data_info 格式 array(softid1, softid2, softid3,....);
*/

class GoPu_hotModel extends GoPu_model
{
    public $use_mysql_cache = True;
    public $index_name = 'option';
    public $option = array();

    function __construct($index = array()) 
    {
        parent::__construct(__CLASS__, $index);
    }

    function make_cache_key($model_name, $index)
    {
        //通过自己的规则生成cache_key, 不使用父类的规则
        $option = $index;
        switch ($option['type']) {
            case 'subrank':
					$cache_key = "SOFTLIST_CATEGORY_SOFTID_F_{$option['catalogid']}_HOT";
                break;
            case 'subrank_with_day':
                    $cache_key = 'hot_' . $option["day"] .'d_list' .  $option["catalogid"]. '_f';
                break;
            case 'hot':
                    $cache_key = 'hot_list_f';
                break;
            case 'hot':
                    $cache_key = 'hot_list_f';
                break;
            case 'hot_1d':
                    $cache_key = 'hot_list_1d_f';
                break;
            case 'hot_1d_1':
                    $cache_key = 'hot_list_1d_1_f';
                break;
            case 'hot_1d_2':
                    $cache_key = 'hot_list_1d_2_f';
                break;
            case 'hot_7d':
                    $cache_key = 'hot_list_7d_f';
                break;
            case 'hot_7d_1':
                    $cache_key = 'hot_list_7d_1_f';
                break;
            case 'hot_7d_2':
                    $cache_key = 'hot_list_7d_2_f';
                break;
            case 'hot_30d':
                    $cache_key = 'hot_list_30d_f';
                break;
            case 'hot_30d_1':
                    $cache_key = 'hot_list_30d_1_f';
                break;
            case 'hot_30d_2':
                    $cache_key = 'hot_list_30d_2_f';
                break;
        }
        return $cache_key;
    }
}
