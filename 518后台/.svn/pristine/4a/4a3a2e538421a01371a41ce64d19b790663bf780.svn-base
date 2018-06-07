<?php
class VideoCollectManageModel extends AdvModel {
	protected $connect_id = 18;
	protected $tablePrefix = 'colle_';
	public function __construct(){
		parent::__construct();
		$co_Connect = C('DB_COLLECT_VIDEO');
		//print_r($co_Connect);die;
		$this -> addConnect($co_Connect, $this->connect_id);
		$this -> switchConnect($this->connect_id);
	}
	
}