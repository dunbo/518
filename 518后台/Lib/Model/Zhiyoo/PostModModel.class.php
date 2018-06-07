<?php 
class PostModModel extends AdvModel {
	 protected $tablePrefix = 'zy_';
	 var $connect_id = 32;
	 public function __construct(){
		parent::__construct();
		$co_Connect = C('DB_ZHIYOO');
		$this -> addConnect($co_Connect, $this->connect_id);
		$this -> switchConnect($this->connect_id);
	}
    
	public function settype($id,$type){
		$id = intval($id);
		$type = intval($type);
		return $this->where(array('id'=>$id))->save(array('type'=>$type));
	}
}