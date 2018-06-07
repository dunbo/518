<?php
class commentlist
{
	public $params;
	public function __construct($param)
	{
		$this->params = $param;
	}
	public function getData()
	{
		$softid = $this->params['softid'];
		$start = $this->params['start'] ? $this->params['start'] : 0;
		$pagesize = $this->params['limit'] ? $this->params['limit'] :5;
		$offset = $start * $pagesize;
        $json = getAPI('soft.getcomment',array('softid' => $softid,'start' => $offset,'limit' => $pagesize,'action' => $this -> params['action']));
		$comment = json_decode($json,true);
		$return  = array(
			'TOTAL' => $comment['TOTAL'],
			'DATA' => $comment['DATA'],
			'KEY' => strtoupper($this -> params['action']),
		);
		return json_encode($return);
	}
}

?>
