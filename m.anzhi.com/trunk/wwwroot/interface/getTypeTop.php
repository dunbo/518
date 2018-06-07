<?php
//华豚获取软件推荐接口，返回对应分类top30
class getTypeTop {
	public $params;
	public function __construct($param)
	{
		$this->params = $param;
	}
	
	public function getData()
	{
		$offset = 0;
		$limit = 30;
		$sub_cate_id = $this->params['id'];

		$parameters = array(
			'LIST_INDEX_START' => $offset,
			'LIST_INDEX_SIZE' => $limit,
			'GET_COUNT' => True,
			'EXTRA_OPTION_FIELD' => array(
				'intro',
				'score',
				'file_time',
				'filesize',
			),
			'ORDER'=> 1,
			'ID' => $sub_cate_id,
			'VR' => 1
		);
		//各分类的软件
		$_SESSION['RES'] = 'h';
		$results = gomarket_action('soft.GoGetCategoryAllSoftList',$parameters);
		$return = array(
			'CODE' => 200,
			'KEY' => $this -> params['action'],
		);
		if ($results) {
			$data = array();
			foreach($results['DATA'] as $info){
				$tmp = array();
				$tmp['softid'] = $info[0];
				$tmp['package'] = $info[7];
				$tmp['name'] = $info[2];
				$tmp['icon'] = $info[1];
				$tmp['intro'] = $info['intro'];
				$tmp['score'] = $info['score'];
				$tmp['url'] = "http://m.anzhi.com/interface/index.php?action=download&softid={$tmp['softid']}&channel={$this->params['channel']}";
				$tmp['update_time'] = $info['file_time'];
				$tmp['size'] = $info['filesize'];
				$data[] = $tmp;
			}
			$count = $results['COUNT'];
			$return['DATA'] = $data;
		} else {
			$return['CODE'] = 204;
		}
		return json_encode($return);
	}
}