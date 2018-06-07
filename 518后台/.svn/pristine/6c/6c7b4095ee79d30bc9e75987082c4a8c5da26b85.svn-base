<?php 
class AwardUserModel extends AdvModel {
	 protected $tablePrefix = 'zy_';
	 var $connect_id = 35;
	 public function __construct(){
		parent::__construct();
		$co_Connect = C('DB_ZHIYOO');
		$this -> addConnect($co_Connect, $this->connect_id);
		$this -> switchConnect($this->connect_id);
	}
    
	
}