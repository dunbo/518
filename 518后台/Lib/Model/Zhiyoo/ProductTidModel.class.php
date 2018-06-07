<?php 
class ProductTidModel extends AdvModel {
	 protected $tablePrefix = 'zy_';
	 var $connect_id = 32;
	 public function __construct(){
		parent::__construct();
		$co_Connect = C('DB_ZHIYOO');
		$this -> addConnect($co_Connect, $this->connect_id);
		$this -> switchConnect($this->connect_id);
	}
    
	public function status($tid,$status){
		$tid = intval($tid);
		$status = intval($status);
		return $this->where(array('tid'=>$tid))->save(array('status'=>$status));
	}
}