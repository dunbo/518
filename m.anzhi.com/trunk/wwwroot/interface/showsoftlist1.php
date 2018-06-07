<?php

class showsoftlist1
{
	public $params;
	public function __construct($param)
	{
		$this->params = $param;
	}
	public function getData()
	{
        $softObj = load_model('sjsoft');
		$view = $this->params['view'];
		$page = $this->params['start'] ? $this->params['start'] : 0;
		$pagesize = $this->params['limit'] ? $this->params['limit'] : 5;

		$option = array(
            'offset' => $page * $pagesize,
			'pagesize' => $pagesize,
			);
		if ($this->params['cid'])
		{
			$cid = $this->params['cid'];
			$option['catalogid'] = $cid;
		}
		$views = array('new', 'hot_7d', 'hot_30d', 'type','special');
		$catalog = $softObj->getDataList('sj_category', array('index' => 'category_id'));
		if ($view == 'suggest')
		{
			$infos = $softObj->getSoftListByMethod($view, $option);
		}
		else if (in_array($view, $views))
		{
			$infos = $softObj->getRenderedSoftList($view, $option);
		}
		$softids = array();
        $data = array();
		foreach ($infos as $idx => $app)
		{
			extract($app);

			if ($view == 'suggest')
			{
				$iconurl =  $iconurl;
				$apkurl =  $apkurl;
			}

            if($category_id[0]==',')
            {
                $category_id=substr($category_id,1);
            }

            $tnum=strlen($category_id);
            $tnum--;
            if($category_id[$tnum]==',')
            {
                $category_id=substr($category_id,0,-1);
            }

			$tmp = array();
            $tmp['softid']  = $softid;
			$tmp['package'] = $package;
			$tmp['name'] = $softname;
			$tmp['icon'] = $iconurl;
			$tmp['type'] = $catalog[$category_id]['name'];
            $tmp['typeid'] = $category_id;
			$tmp['score'] = $score/2;
			$data[] = $tmp;
		}
		$return  = array(
			'TOTAL' => $option['total'],
			'DATA' => $data,
			'KEY' => strtoupper($this -> params['action']),
		);
		return json_encode($return);
	}
}

?>