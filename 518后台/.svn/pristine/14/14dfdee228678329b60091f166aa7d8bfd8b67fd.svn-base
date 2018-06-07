<?php
class toufangModel extends AdvModel {
	protected $connect_id = 18;
	protected $tablePrefix = 'tf_';
	public function __construct(){
		parent::__construct();
		$co_Connect = C('DB_TOUFANG');
		$this -> addConnect($co_Connect, $this->connect_id);
		$this -> switchConnect($this->connect_id);
	}
	
}