<?php

class mybelike
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
		$model = new GoModel();
		$option = array(
			'table' => 'sj_soft',
			'where' => array(
				'softid' => $softid,
			),
			'field' => 'package',
		);
		$info = $model -> findOne($option);
		$package = $info['package'];
		$result = getAPI('soft.getmaybelike',array(
			'package' => $package,
		));
		$data = json_decode($result,true);
		$return  = array(
			'TOTAL' => $data['TOTAL'],
			'DATA' => $data['DATA'],
			'KEY' => strtoupper($this -> params['action']),
		);
		return json_encode($return);
	}
}

?>