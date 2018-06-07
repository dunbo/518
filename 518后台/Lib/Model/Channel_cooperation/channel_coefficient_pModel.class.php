<?php
class channel_coefficient_pModel extends AdvModel {
	protected $connect_id = 1812;
	protected $tablePrefix = 'activation_';
	public function __construct(){
		parent::__construct();
		$co_Connect = C('DB_CHANNEL_COEFFICIENT_P');
		$this -> addConnect($co_Connect, $this->connect_id);
		$this -> switchConnect($this->connect_id);
	}
	
}