<?php
/*
    软件关联文件model
    index格式 : array('id' =>  $id)
    data_info格式 :  sj_soft_file数据库表下的字段;
*/

class GoPu_soft_fileModel extends GoPu_model
{
    public $table = 'sj_soft_file';
    public $index_name = 'id';
    public $softid = 0;
    public $id = 0;
    public $softname = '';
    public $package = '';
    public $cache_timeout = 300; 

    function __construct($index = array())
    {
        parent::__construct(__CLASS__, $index);
    }

    function data2property()
    {
        if (isset($this->data_info['softid'])) {
            $this->softid = $this->data_info['softid'];
        }
        if (isset($this->data_info['id'])) {
            $this->id = $this->data_info['id'];
        }
    }
}
