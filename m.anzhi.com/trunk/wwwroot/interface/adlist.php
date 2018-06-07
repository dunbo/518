<?php

class adlist
{
	public $params;
	public $pre_url;
	public function __construct($param)
	{
		$this->params = $param;
		$this->pre_url = getIconHost();
	}
	public function getData()
	{	
		$ip_addr = array('192.168.1.165'=>1,'218.241.82.226'=>1,'120.55.247.10'=>1);
		$ip = onlineip();
		if (!isset($ip_addr[$ip])) {
			exit('ip is error');
		}
	
		$page = (int)$this->params['start'] ? (int)$this->params['start'] : 1;
		$page = $page - 1;
		$pagesize = (int)$this->params['limit'] ? (int)$this->params['limit'] : 5;
        $pagesize = ($pagesize > 30) ? 30 : $pagesize; //对 limit 进行限制  加入 大于30  设置为30
		$offset = $page * $pagesize;
		$model = new GoModel();
		$option = array(
			'table' => 'sj_ad_library',
			'where' => array(
				'status' => 1,
			),
			'field' => 'count(*) as tpcount',
			'cache_time' => 3600
		);
		$tpcount = $model -> findOne($option);
		$total = $tpcount['tpcount'];
		
		$option = array(
			'table' => 'sj_ad_library',
			'where' => array(
				'status' => 1,
			),
			'field' => 'package',
			'offset' => $offset,
			'limit' => $pagesize,
			'cache_time' => 3600,
			'order' => 'id desc',
		);
		$info = $model -> findAll($option);
		$p = array();
		foreach ($info as $pkg) {
			$p[] = $pkg['package'];
		}
		$softlist = load_model('softlist');
		$res = $softlist->getPkg2Id($p);
		$softids = array();
		foreach ($res as $val) {
			if (is_array($val)) {
				foreach ($val as $v) {
					$softids[$v] = $v;
				}
			} else {
				$softids[$val] = $val;
			}
		}

		$dev_model = load_model('dev');
		$softids = $dev_model->filterSoftId($softids);
		$softinfos = $softlist->getSoftInfos($softids);
		
		
		$data = array();
		
		$data['TOTAL'] = $total;
		foreach ($softinfos as $v) {
			$tmp['softid'] = $v['softid'];
			$tmp['package'] = $v['package'];
			$tmp['softname'] = $v['softname'];
			$tmp['icon'] = $this->pre_url.$v['iconurl_125'];
			$tmp['version_name'] = $v['version'];
			$data['DATA'][] = $tmp;
		}
		
		$return  = array(
			'TOTAL' => $data['TOTAL'],
			'DATA' => $data['DATA'],
			'KEY' => strtoupper($this -> params['action']),
		);
		return json_encode($return);
	}
}

?>
