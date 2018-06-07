<?php
/*
    最新软件model
    index格式 array('type' => 'xxx', 'catalogid' => 'xxx')
    data_info 格式 array(softid1, softid2, softid3,....);
*/

class GoPu_newModel extends GoPu_model
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
        $option = $index;
        switch ($option['type']) {
            case 'subnew':
					$cache_key = "SOFTLIST_CATEGORY_SOFTID_F_{$option['catalogid']}_NEW";
                break;
            case 'new':
                   $cache_key = 'new_list_f';
               break;
        }
        return $cache_key;
    }
}

