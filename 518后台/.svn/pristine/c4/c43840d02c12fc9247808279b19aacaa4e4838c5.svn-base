<?php 
class ProductPerModel extends AdvModel {
	 protected $tablePrefix = 'zy_';
	 var $connect_id = 32;
	 public function __construct(){
		parent::__construct();
		$co_Connect = C('DB_ZHIYOO');
		$this -> addConnect($co_Connect, $this->connect_id);
		$this -> switchConnect($this->connect_id);
	}
    
	public function datelinebyid($id){
		$id = intval($id);
		$res = $this->where(array('id'=>$id))->find();
		return $res['dateline'];
	}
    
}