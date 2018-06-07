<?php

/*
    软件截图model
    index格式: $id 
    data_info格式 :  sj_soft_thumb数据库表下的字段;
*/

class GoPu_soft_thumbModel extends GoPu_model
{
    public $table = 'sj_soft_thumb';
    public $softid = 0;
    public $index_name = 'id';
    public $id = 0;
    public $softname = '';
    public $package = '';
    
    function __construct($index = array())
    {
        parent::__construct(__CLASS__, $index);
    }

    function data2property()
    {
        if (isset($this->data_info['softid'])) {
            $this->softid = $this->data_info['softid'];
        }
    }
}
