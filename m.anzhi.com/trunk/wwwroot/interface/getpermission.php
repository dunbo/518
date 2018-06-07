<?php 
class getpermission{
	public $params;
	public function __construct($param)
	{
		$this->params = $param;
	}   
	public function getData()
	{
		$option = array('softid' => $this -> params['softid'],'action' => $this -> params['action']);
		$result = getAPI('soft.get_permission',$option);
		return $result;
	}

}