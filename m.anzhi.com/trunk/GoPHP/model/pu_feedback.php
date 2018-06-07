<?php
/*
    反馈信息model
    index格式 : array('feedbackid' =>  $feedbackid)
    data_info格式 :  sj_feedback数据库表下的字段;
*/
class GoPu_feedbackModel extends GoPu_model
{
    public $table = 'sj_feedback';
    public $index_name = 'feedbackid';
    public $feedbackid = 0;
    public $softid = 0;
    public $userid = 0;

    function __construct($index = array())
    {
        parent::__construct(__CLASS__, $index);
    }

    function data2property()
    {
        if (isset($this->data_info['softid'])) {
            $this->softid = $this->data_info['softid'];
        }
        if (isset($this->data_info['userid'])) {
            $this->userid = $this->data_info['userid'];
        }
    }
}
