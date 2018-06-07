<?php
class channel_coefficientModel extends AdvModel {
	protected $connect_id = 18;
	protected $tablePrefix = 'activation_';
	public function __construct(){
		parent::__construct();
		$co_Connect = C('DB_CHANNEL_COEFFICIENT');
		$this -> addConnect($co_Connect, $this->connect_id);
		$this -> switchConnect($this->connect_id);
	}
	
}