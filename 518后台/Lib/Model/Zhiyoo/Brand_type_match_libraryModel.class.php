<?php 
class Brand_type_match_libraryModel extends AdvModel {
     protected $trueTableName = 'zy_brand_type_match_library';
	 protected $tablePrefix = 'zy_';
	 var $connect_id = 32;
	 public function __construct(){
		parent::__construct();
		$co_Connect = C('DB_ZHIYOO');
		$this -> addConnect($co_Connect, $this->connect_id);
		$this -> switchConnect($this->connect_id);
	}
}