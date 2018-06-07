<?php
class ActivityModel extends AdvModel {
	protected $connect_id = 18;
	protected $tablePrefix = '';
	public function __construct(){
		parent::__construct();
		$co_Connect = C('DB_ACTIVITY');
		$this -> addConnect($co_Connect, $this->connect_id);
		$this -> switchConnect($this->connect_id);
	}
}