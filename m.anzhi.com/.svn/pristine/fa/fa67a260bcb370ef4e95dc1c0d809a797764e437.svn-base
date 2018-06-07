<?php
class softintro1
{
	public $params;
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
				break;
			}
		}

		$softfile = $softObj->getDataList('sj_soft_file', array('where' => array('softid' => $softid
                    ,'package_status'=>1)
				));


		//$comments = $softObj->nakedSQL("select * from comment where softid = " . $softid . " order by submit_tm desc");
		$comments = $softObj->getDataList('sj_soft_comment', array('where' => array('softid' => $softid
					,'status'=>1),
                    'order'=>'create_time desc'
				));

		$counts = count($comments);
        $comment_arr = array();
		if (count($counts) > 0)
		{
			foreach($comments as $comt)
			{
				$comment_arr[] = array('rank' => $counts,
					'comments' => $comt['content'],
					'score' => $comt['score']/2,
					'submit_tm' => date('Y-m-d',$comt['create_time'])
					);
				$counts--;
				$i++;
				if ($i == 2)
				{
					break;
				}
			}
		}
		$softinfo = array("softid" => $softid,
			"name" => $info['softname'],
			"icon" => STATIC_IMG_HOST . $softfile[0]['iconurl'],
			"upload_tm" => $upload_tm,
            "developer"=> $developer,
			"version" => $info['version'],
			"apksize" => $softfile[0]['filesize'],
			"desc" => $info['intro'],
			"downloaded" => $info['total_downloaded'],
			"score" => $info['score']/2,
			"thumb" => $thumb,
			"thumb_count" => $thumb_count,
			"comment" => $comment_arr,
			);
		return json_encode($softinfo);
	}
}

?>