<?php

class Bbs_manageModel extends AdvModel {
	protected $connect_id = 20;
	protected $tablePrefix = 'bbs_';
	public function __construct(){
		parent::__construct();
		$co_Connect = C('DB_BBS');
		$this -> addConnect($co_Connect, $this->connect_id);
		$this -> switchConnect($this->connect_id);
	}
}