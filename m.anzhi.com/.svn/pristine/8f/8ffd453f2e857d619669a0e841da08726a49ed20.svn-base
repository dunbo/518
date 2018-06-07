<?php

class thumblist1
{
	public $params;
	public function __construct($param)
	{
		$this->params = $param;
	}
	public function getData()
	{
        $softObj = load_model('sjsoft');
		$softid = $this->params['softid'];
		$option = array('softid' => $softid
			);
		$pics = $softObj->getDataList('sj_soft_thumb', array('where' => array('softid' => $softid
					,'status'=>1)
				));
		$thumb_count = count($pics);
        $data['KEY'] = strtoupper($this -> params['action']);
        $data['count'] = $thumb_count;
 		if (count($pics) > 0)
		{
			foreach($pics as $idx => $info)
			{
				$data['thumb_' . $idx . '_url'] =STATIC_IMG_HOST . $info['url'];
			}
		}

		return json_encode($data);
	}
}

?>