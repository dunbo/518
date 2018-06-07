<?php

class RechargeFlowModel extends AdvModel {
	protected $connect_id = 18;
	protected $TableName = 'recharge_flow_bill';
	public function __construct(){
		parent::__construct();
		$co_Connect = C('DB_ACTIVITY');
		$this -> addConnect($co_Connect, $this->connect_id);
		$this -> switchConnect($this->connect_id);
	}
	
	public function get_activity_page($where,$limit){
	    $total =  $this ->table($this->TableName)->where($where) -> count();	
	    import('@.ORG.Page2');
	    $param = http_build_query($_GET);
	    $Page = new Page($total,$limit,$param);
	    $Page->rollPage = 10;
	    $Page->setConfig('header','篇记录');
	    $Page->setConfig('first','首页');
	    $Page->setConfig('last','尾页');
	    $result = $this->table($this->TableName)->where($where)-> limit($Page->firstRow . ',' . $Page->listRows)  -> order('day desc')->select();
	    return array($result,$total,$Page);
	}
}
