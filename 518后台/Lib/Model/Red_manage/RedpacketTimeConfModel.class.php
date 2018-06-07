<?php 
class RedpacketTimeConfModel extends AdvModel {
	 protected $tablePrefix = 'sj_';
	 var $connect_id = 34;
	 public function __construct(){
		parent::__construct();
		$co_Connect = C('DB_REDPACKET');
		$this -> addConnect($co_Connect, $this->connect_id);
		$this -> switchConnect($this->connect_id);
	}
}