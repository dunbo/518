<?php
class activationModel extends AdvModel{
	protected $connectid = 1; //0 为配置文件的主库 id 
	function __construct(){

	}
	function getConnection(){
		$this -> addConnect(C('DB_STATICS_CONNECT'),$this -> connectid);
		$this -> switchConnect($this -> connectid);
	}
	function closeconnect(){
		$this -> closeConnect($this -> connectid);
	}
}