<?php
class commentlist1
{
	public $params;
	public function __construct($param)
	{
		$this->params = $param;
	}
	public function getData()
	{
		$softid = $this->params['softid'];
		$offset = $this->params['start'];
		$pagesize = $this->params['limit'];
		// TODO: comments array_slice
        $softObj = load_model('sjsoft');
		$option = array(
			'where' => array(
				'softid' => $softid,
			),
			'limit' => $pagesize,
			'offset' => $offset,
			'order' => 'create_time DESC'
		);


        $comments = $softObj->getDataList('sj_soft_comment', $option);

		$data = array();
		foreach ($comments as $idx => $info)
		{
			$uid = $info['userid'];
			if ($uid == 0) {
				$disp_name = "安智网友" . substr($info['imei'], 0, 4) . substr($info['imei'], -4);
			} else {
			    $disp_name = "安智网友" . $info['user_name'];
			}

			$data[] = array('score' => $info['score']/2,
				'submit_tm' => $info['create_time'],
				'comments' => $info['content'],
				'username' => $disp_name
				);
		}

		$option = array(
			'where' => array(
				'softid' => $softid,
			)
		);
        $comments = $softObj->getDataList('sj_soft_comment', $option);
		$return  = array(
			'TOTAL' => count($comments),
			'DATA' => $data,
			'KEY' => strtoupper($this -> params['action']),
		);
		return json_encode($return);
	}
}

?>