<?php

class thumblist
{
	public $params;
	public function __construct($param)
	{
		$this->params = $param;
	}
	public function getData()
	{
		$softid = $this->params['softid'];
        $data['KEY'] = strtoupper($this -> params['action']);
		$result = getAPI('soft.getdetail',array('softid'  => $softid, 'action' => $this -> params['action']));
		$info = json_decode($result,true);
		$thumb_count = count($info['SOFT_SCREENSHOT_URL']);
		$data['count'] = $thumb_count;
		if ($thumb_count > 0)
		{    $idx = 0;
			foreach($info['SOFT_SCREENSHOT_URL'] as  $pic)
			{   $idx++;
				$data['thumb_' . $idx . '_url'] = $pic;
			}
		}
		return json_encode($data);
	}
}
?>
