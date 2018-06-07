<?php
class softIntro
{
	public $params;
    public $device;
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
         //getCacheDataByDevice($softid,$this->device);  TODO 过滤机型
        /* TODO 过滤机型*/
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

		$app = $softObj->getDataList('sj_soft', array('where' => $option));
		$info = $app[0];
		$upload_tm = date('Y-m-d',$info['upload_tm']);
		$pics = $softObj->getDataList('sj_soft_thumb', array('where' => array('softid' => $softid
					,'status'=>1)
				));
        $developer = $info['dev_name'];
		$thumb_count = count($pics);
        $thumb  = array();
		if (count($pics) > 0)
		{
			foreach($pics as $idx => $pic)
			{
				$thumb[] = array('thumb_' . $idx . '_url'=>STATIC_IMG_HOST . $pic['url']);
			}
		}

		$softfile = $softObj->getDataList('sj_soft_file', array('where' => array('softid' => $softid
                    ,'package_status'=>1)
				));

		$softinfo = array("softid" => $softid,
			"softname" => $info['softname'],
			"icon" => STATIC_IMG_HOST.$softfile[0]['iconurl'],
			"upload_tm" => $upload_tm,
            "developer"=> $developer,
			"version" => $info['version'],
            "version_code" => $info['version_code'],
			"apksize" => $softfile[0]['filesize'],
			"intro" => $info['intro'],
			"total_download" => $info['total_downloaded'],
			"score" => $info['score']/2,
			"thumb" => $thumb,
			"thumb_count" => $thumb_count,
			);
		return json_encode($softinfo);
	}
}

?>