<?php 
class ThreadRecommendModel extends AdvModel {
	 protected $tablePrefix = 'zy_';
	 var $connect_id = 32;
	 public function __construct(){
		parent::__construct();
		$co_Connect = C('DB_ZHIYOO');
		$this -> addConnect($co_Connect, $this->connect_id);
		$this -> switchConnect($this->connect_id);
	}
	
	public function status($id,$status){
		$id = intval($id);
		$status = intval($status);
		return $this->where(array('rid'=>$id))->save(array('status'=>$status));
	}
	
	public function selectbyrid($rid){
		$rid = intval($rid);
		return $this->where(array('rid'=>$rid))->find();
	}
	
}