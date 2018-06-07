<?php
class ChannelStatsModel extends AdvModel {
	//调整表前缀
	protected $trueTableName = 'activation_state';
	protected $connectid = 2;
	protected $tablePrefix = '';

	public function __construct(){
        parent::__construct();
        if (C('DB_HOST') != '192.168.0.99') {
            $myConnect1 = array(
				'dbms'     => 'mysql',
				'username' => 'bireadonly',
				'password' => 'ZoEauICAQ4XnT',
				'hostname' => '172.16.1.50',
				'hostport' => '3306',
				'database' => 'activation_offline'
            );
            $this->addConnect($myConnect1, $this->connectid);
            $this->switchConnect($this->connectid);
        }
    }
	function closeconnect(){
		$this -> closeConnect($this -> connectid);
	}
}
?>