<?php

class commentList
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
        $softObj = load_model('sjsoft');
        /*TODO 过滤机型*/
       $deviceinfo = $softObj -> getDataList('pu_device', array('where'=> array('dname'=> $this -> device,'status' => 1)));
       $did = $deviceinfo[0]['did'];
       $show_soft_rule = $deviceinfo[0]['show_soft_rule'];
        if($show_soft_rule){
		  $memsoftids = GoCache :: get('SOFTLIST_NG_SOFTID_D'.$did);
          if(in_array($softid,$memsoftids)){
           header("HTTP/1.1 403 Forbidden");
           echo "No Content";
           exit;
         }
		}else{
			$memsoftids = GoCache :: get('SOFTLIST_PG_SOFTID_D'.$did);
          if(!in_array($softid,$memsoftids)){
           header("HTTP/1.1 403 Forbidden");
           echo "No Content";
           exit;
         }
		}
		$offset = isset($this->params['start']) ? $this-> params['start'] : 0;
		$pagesize = isset($this->params['limit']) ? $this-> params['limit'] : 10;
		// TODO: comments array_slice
        $softinfo  = $softObj -> getDataList('sj_soft',array('where' => array('softid' => $softid)));

		$option = array(
			'where' => array(
				'package' => $softinfo[0]['package'],
                'status' => '1'
			),
			'limit' => $pagesize,
			'offset' => $offset*$pagesize,
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

			$data[] = array(
                'score' => $info['score']/2,
				'submit_tm' => $info['create_time'],
				'comments' => $info['content'],
                'softid' => $info['softid'],
				'username' => $disp_name
				);
		}

		$option = array(
			'where' => array(
				'package' => $softinfo[0]['package'],
                'status' => '1'
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