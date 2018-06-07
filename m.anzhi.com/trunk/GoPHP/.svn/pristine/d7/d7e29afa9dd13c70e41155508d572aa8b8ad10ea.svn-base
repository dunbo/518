<?php
class GoPu_messageModel extends GoPu_model
{
	public $table = 'sj_thread_memory';
	function __construct($index = '')
    {
        parent::__construct(__CLASS__, $index);
    }
    
    function getMessageList($op){
    	$op['re_status'] = 1;
    	$where = $op;
    	
    	$option = array( 'where' => $where,
                         'index' => 'tid');
        $data = $this->findAll($option);
        
        return $data;
    }
}
