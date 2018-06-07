<?php
class CodeModel extends AdvModel {

    protected $connect_id = 26;
    function myConnect($database){
		$connect = array(
			'dbms'=> 'mysql',
			'username' => 'root',
			'password' => 'southpark',
			'hostname' => '192.168.0.99',
			'hostport' => '3306',
			'database' => $database
		);
		$this -> addConnect($connect, $this->connect_id);
		$this -> switchConnect($this->connect_id);		
    }       
}
