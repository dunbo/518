<?php 
class Product_masterModel extends AdvModel {
	 protected $tablePrefix = 'zy_';
	 var $connect_id = 32;
	 public function __construct(){
		parent::__construct();
		$co_Connect = C('DB_ZY_PRODUCT_MASTER');
		$this -> addConnect($co_Connect, $this->connect_id);
		$this -> switchConnect($this->connect_id);
	}
    
}