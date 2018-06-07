<?php

class mayBeLike
{
	public $params;
	public $device;
	public function __construct($param)
	{
		$this->params = $param;
	}
	public function getData()
	{
		$softid = $this->params['softid'];
		$page = $this->params['start'] ? $this->params['start'] : 0;
		$pagesize = $this->params['limit'] ? $this->params['limit'] : 5;
		$offset = $page * $pagesize;
		
		$soft_logic = pu_load_logic('soft');
		$you_like = $soft_logic->get_soft_maybe_like($softid, 100);

		$softObj = load_model('sjsoft');

        /* TODO 过滤机型*/
		$deviceinfo = $softObj -> getDataList('pu_device', array('where'=> array('dname'=> $this -> device,'status' => 1)));
		$did = $deviceinfo[0]['did'];
		$show_soft_rule = $deviceinfo[0]['show_soft_rule'];
		if($show_soft_rule){
			$memsoftids = GoCache :: get('SOFTLIST_NG_SOFTID_D'.$did);
			$ids_filp = array_flip($memsoftids);
			if(isset($ids_filp[$softid])){
				header("HTTP/1.1 403 Forbidden");
				echo "No Content";
				exit;
			}
			
			foreach ($you_like as $k => $v) {
				if (isset($ids_filp[$you_like['softid']])) {
					unset($you_like[$k]);
				}
			}
		}else{
			$memsoftids = GoCache :: get('SOFTLIST_PG_SOFTID_D'.$did);
			$ids_filp = array_flip($memsoftids);
			if(!isset($ids_filp[$softid])){
				header("HTTP/1.1 403 Forbidden");
				echo "No Content";
				exit;
			}
			
			foreach ($you_like as $k => $v) {
				if (!isset($ids_filp[$you_like['softid']])) {
					unset($you_like[$k]);
				}
			}
		}
				
		$data = array();
		$img_host = getIconHost();
		foreach($you_like as $info) {
			$data[] = array(
				'package' => $info["package"],
				"icon" => $img_host . $info['iconurl'],
				'typeid' => $info['category_id'],
				'type' => $info['category_name'],
				'softname' => $info["softname"],
				'softid' => $info["softid"]
			);
		}
		$total = count($data);
		if($total == 0){
			header("HTTP/1.1 204 No Content");
			exit;
		}
		$data = array_slice($data, $offset, $pagesize);
		$return  = array(
            'KEY' => strtoupper($this -> params['action']),
			'TOTAL' => $total,
			'DATA' => $data,
		);
		return json_encode($return);
	}
}

?>