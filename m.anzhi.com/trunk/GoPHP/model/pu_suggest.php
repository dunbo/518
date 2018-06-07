<?php
/*
    推荐列表的model
    index格式: array('type' => 'xxx')
    data_info格式 array(softid1, softid2, softid3..................)
*/
class GoPu_suggestModel extends GoPu_model
{
    public $use_mysql_cache = True;
    public $option;
    public $index_name = 'option';

    function __construct($index = array()) 
    {
        parent::__construct(__CLASS__, $index);
    }

    function make_cache_key($model_name, $index = '')
    {
        $cache_key = 'suggest_list';
        $option = $index; 
        if ($type = $option['type']) {
            $cache_key .= '_'.$type;
        }
        return $cache_key;
    }
}
