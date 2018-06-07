<?php
ini_set("display_errors", 1);
error_reporting(E_ERROR);
class showfulllist1
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
		$channel = $this->params['channel'];

		$option = array(
            'offset' => $page * $pagesize,
			'pagesize' => $pagesize,
		);

		if($view=='fulltype'){
			$view = "type";
		}elseif($view=='fullspecial'){
			$view = "special";
		}else{
			header("HTTP/1.1 403 Forbidden");
            exit;
		}

		$catalog = $softObj->getDataList('sj_category', array('index' => 'category_id'));
		$pname = "";
		if ($this->params['cid'])
		{
			$cid = $this->params['cid'];
			$option['catalogid'] = $cid;
			$pname = $this->getParentCategory($cid,$catalog);
		}

		$infos = $softObj->getRenderedSoftList($view, $option);

		$softids = array();
        $data = array();

		foreach ($infos as $idx => $app)
		{
			extract($app);

			$tmp = array();
            $tmp['softid']  = $softid;
			$tmp['package'] = $package;
			$tmp['name'] = $softname;
			$tmp['icon'] = $iconurl;
			$tmp['version_code'] = $version_code;
			$tmp['costs'] = $costs;
			$tmp['pay_category'] = $costs>0?2:1;
			$tmp['source_type'] = 0;
			$tmp['score'] = $score;
			$tmp['filesize'] = $filesize;
			$tmp['developer'] = $dev_name;
			$tmp['msgnum'] = $msgnum;
			$tmp['intro'] = $intro;
			$tmp['upload_tm'] = $upload_tm;
			$tmp['version'] = $version;
			$tmp['type'] = $this->getCategory(&$category_id, $catalog);
			$tmp['parent_type'] = $pname;
            $tmp['typeid'] = $category_id;
			$tmp['score'] = $score/2;
			$tmp['thumb'] = $this->getThumb($softid);
			$tmp['download'] = "http://m.goapk.com/interface/index.php?action=download&softid=$softid&channel=$channel";
			$data[] = $tmp;
		}
		$return  = array(
			'TOTAL' => $option['total'],
			'DATA' => $data,
			'KEY' => strtoupper($this -> params['action']),
		);
		return json_encode($return);
	}

	public function getCategory(&$category_id,$catalog){
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
		$category_id_array = explode(",", $category_id);

		$re = "";
		foreach($category_id_array as $v){
			if($v=='') continue;
			$re .= $catalog[$v]['name'].",";
		}
		$re = substr($re,0,-1);
		return $re;
	}

	public function getParentCategory($category_id,$catalog){
		$pid = $catalog[$category_id]["parentid"];
		$pname = $catalog[$pid]["name"];
		return $pname;
	}

	public function getThumb($softid){
		$softObj = load_model('sjsoft');
		$pics = $softObj->getDataList('sj_soft_thumb', array('where' => array('softid' => $softid
					,'status'=>1)
				));

        $thumb  = array();
		if (count($pics) > 0)
		{
			foreach($pics as $idx => $pic)
			{
				$thumb[] = array('thumb_' . $idx . '_url'=>STATIC_IMG_HOST . $pic['url']);
			}
		}

		return $thumb;
	}

}

?>