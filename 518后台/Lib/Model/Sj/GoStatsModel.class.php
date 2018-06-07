<?php
class GoStatsModel extends AdvModel {
	//调整表前缀
	protected $trueTableName = 'activation_state';
	protected $connectid = 1;
	protected $tablePrefix = '';

	public function __construct()
	{
		parent::__construct();
		if (C('DB_HOST') == '192.168.0.99') {
			$myConnect1 = C('DB_CO_LGTV_99');
		}else{
			$myConnect1 = C('DB_CO_LGTV');
		}
		$this->addConnect($myConnect1, $this->connectid);
		$this->switchConnect($this->connectid);
	}
	function closeconnect(){
		$this -> closeConnect($this -> connectid);
	}
}
?>