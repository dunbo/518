<?php
/*
    软件评论model
    index格式 : $id
    data_info格式 : sj_soft_comment数据库表中的字段
*/
class GoPu_commentModel extends GoPu_model
{
	public $table = 'sj_soft_comment';
    public $softid = 0;
    public $index_name = 'id';
    public $id = 0;

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
   function get_soft_commented_by_userid($softid,$userid) {
       $option = array("where"=> array("softid" => $softid,"userid"=>$userid,"status" => 1));
       $result = $this -> findOne($option);
       if(!$result)
           return 1;  // 未评论
       else
           return 2;  //已评论
   }
    //保存评论以后 应该上出对应的软件评论ID的cache
    function insert_complate_hook()
    {
        $soft_obj = pu_load_model_obj('pu_soft', $this->data_info['softid']);
        $soft_obj->clear_cache_comment_id();
        return $this->data_info;
    }
}
