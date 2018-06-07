<?php 
class ThreadRecommendRuleModel extends AdvModel {
	 protected $tablePrefix = 'zy_';
	 var $connect_id = 32;
	 public function __construct(){
		parent::__construct();
		$co_Connect = C('DB_ZHIYOO');
		$this -> addConnect($co_Connect, $this->connect_id);
		$this -> switchConnect($this->connect_id);
	}
	
	public function getrule(){
		$res = $this -> select();
		$status = array();
		foreach($res as $val){
			$status[$val['rule']] = $val['rulename'];
		}
		return $status;
	}
}