<?php
//万能手机助手软件推荐接口，获取指定专题的软件列表
class zsGetRecommend {
	public $params;
	public function __construct($param)
	{
		$this->params = $param;
	}
	
	public function getData()
	{
		$offset = 0;
		$limit = 40;

		$parameters = array(
			'LIST_INDEX_START' => $offset,
			'LIST_INDEX_SIZE' => $limit,
			'GET_COUNT' => true,
			'EXTRA_OPTION_FIELD' => array(
				'intro',
				'score',
				'file_time',
				'filesize',
			),
			'ID' => 31,
			'VR' => 3
		);

		$_SESSION['RES'] = 'h';
		$results = gomarket_action('soft.GoGetSoftSubjectAllList',$parameters);
		$return = array(
			'CODE' => 200,
			'KEY' => $this -> params['action'],
		);
		//[应用id1, 应用名称,应用包名,应用图标,应用下载地址],
		if ($results) {
			$data = array();
			foreach($results['DATA'] as $info){
				$tmp = array();
				$tmp['softid'] = $info[0];
				$tmp['name'] = $info[2];
				$tmp['package'] = $info[7];
				$tmp['icon'] = $info[1];
				$tmp['url'] = "http://m.anzhi.com/interface/index.php?action=download&softid={$tmp['softid']}&channel={$this->params['channel']}";
				$data[] = $tmp;
			}
			$return['DATA'] = $data;
		} else {
			$return['CODE'] = 204;
		}
		return json_encode($return);
	}
}