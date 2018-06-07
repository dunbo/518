<?php 
class getebooktop{

	public $params;

	public function __construct($param)
	{
		$this->params = $param;
	}
	
	public function getData()
	{
		$this -> params['start'] = $this -> params['start'] ? $this -> params['start'] : 0;
		$this -> params['limit'] =  $this -> params['limit'] ? $this -> params['limit'] : 10;
		$option = array('action' => $this -> params['action'],'start' => $this -> params['start'],'limit' => $this -> params['limit']);
		$result = getAPI('soft.get_ebooktop',$option);
		return $result;
	}

}