<?php
//华豚搜索接口
class getSearchResult {
	public $params;
	public function __construct($param)
	{
		$this->params = $param;
	}
	
	public function getData()
	{
		$offset = $this->params['start'];
		$limit = $this->params['size'];
		if ($limit>=40) $limit = 40;
		$keyword = $this->params['keyword'];

		$parameters = array(
			'LIST_INDEX_START' => $offset,
			'LIST_INDEX_SIZE' => $limit,
			'GET_COUNT' => true,
			'SEARCH_QUERY' => $keyword,
			'EXTRA_OPTION_FIELD' => array(
				'category_name',
			),
		);
		//各分类的软件
		$_SESSION['RES'] = 'h';
		$results = gomarket_action('soft.GoSearchSoft',$parameters);
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
				$tmp['score'] = $info[3];
				$tmp['filesize'] = $info[9];
				$tmp['is_office'] = $info[20];
				$tmp['version'] = $info[14];
				$tmp['download'] = $info[11];
				$tmp['url'] = "http://m.anzhi.com/interface/index.php?action=download&softid={$tmp['softid']}&channel={$this->params['channel']}";
				$tmp['category_id'] = $info[10];
				$tmp['category_name'] = $info['category_name'];
				$data[] = $tmp;
			}
			$count = $results['COUNT'];
			$return['DATA'] = $data;
			$return['TOTAL'] = $count;
		} else {
			$return['CODE'] = 204;
		}
		return json_encode($return);
	}
}