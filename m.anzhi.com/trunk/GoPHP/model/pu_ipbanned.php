<?php
/*
    单IP刷下载屏蔽model
    index格式:$ip
    data_info格式 array('softid1' = 下载数量, 'softid2' => 下载数量) 
*/

class GoPu_ipbannedModel extends GoPu_model
{
    public $index_name = 'ip';
    public $ip = '';

    function __construct($index = '')
    {
		$this->cache_timeout =  strtotime('tomorrow'); //无论cache是否更新都保存一天
        if (is_string($index) && preg_match('#[0-9\.]+#si', $index)) {
            parent::__construct(__CLASS__, $index);
        }
    }

    function make_cache_key($model_name, $index)
    {
        return $index; 
    }
}
