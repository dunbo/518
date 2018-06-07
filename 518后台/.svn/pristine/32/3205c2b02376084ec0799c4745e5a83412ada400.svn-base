<?php
class MarketLogModel extends AdvModel{
	protected $connect_id = 19;
	protected $tablePrefix = '';
	protected $table = 'sj_market_exception';
	protected $fields = '';
	public function __construct()
	{
		//parent::__construct();
		$myConnect = C('DB_MARKETLOG');
		$this -> addConnect($myConnect, $this->connect_id);
		$this -> switchConnect($this->connect_id);
	}
	
	function closeconnect($connect_id){
		$this -> closeConnect($connect_id);
	}
}