<?php 
class DailyrecomIconModel extends AdvModel {
	 protected $tablePrefix = 'zy_';
	 var $connect_id = 32;
	 public function __construct(){
		parent::__construct();
		$co_Connect = C('DB_ZHIYOO');
		$this -> addConnect($co_Connect, $this->connect_id);
		$this -> switchConnect($this->connect_id);
	}
    
	public function get(){
		return $this->where(array('status'=>1))->find();
	}
    
	public function delall(){
		return $this->where(array('status'=>1))->save(array('status'=>-1));
	}
}