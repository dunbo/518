<?php

class otherdev
{
	public $params;
	public function __construct($param)
	{
		$this->params = $param;
	}
	public function getData()
	{
		$softid = $this->params['softid'];
		$page = $this->params['start'] ? $this->params['start'] : 0;
		$pagesize = $this->params['limit'] ? $this->params['limit'] : 5;
        $pagesize = ($pagesize > 30) ? 30 : $pagesize; //对 limit 进行限制  加入 大于30  设置为30
		$offset = $page * $pagesize;
        $softObj = load_model('sjsoft');
		$softinfo = $softObj->getDataList('sj_soft', array('where' => array('softid' => $softid)));
		$owner = $softinfo[0]['dev_name'];
        $data = array();
		$result = getAPI('soft.getotherdev',array(
			'auth' => $owner,
			'action' => $this -> params['action'],
			'start' => $offset,
			'limit' => $pagesize,
		));
		return $result;
	}
}

?>
