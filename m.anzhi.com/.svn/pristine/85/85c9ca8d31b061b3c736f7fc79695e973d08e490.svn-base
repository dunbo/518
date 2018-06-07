<?php
class softintro
{
	public $params;
	public function __construct($param)
	{
		$this->params = $param;
	}
	public function getData()
	{
		$pas = array(
            'softid'  => $this -> params['softid'], 
            'action' => $this -> params['action']
        );
        $pas['vr'] = 0;
		$result = getAPI('soft.getdetail',$pas);
		$softid = $this -> params['softid'];
		$info = json_decode($result,true);
		$thumb_count = count($info['SOFT_SCREENSHOT_URL']);
        $thumb  = array();
		if ($thumb_count > 0)
		{    $idx = 0;
			foreach($info['SOFT_SCREENSHOT_URL'] as  $pic)
			{   $idx++;
				$thumb[] = array('thumb_' . $idx . '_url'=> $pic);
			}
		}
		$comment_json = getAPI('soft.getcomment',array('softid' => $this -> params['softid'],'start' => 0,'limit' => 5));
		$comment = json_decode($comment_json,true);
		$comment_data = $comment['DATA'];
		$comment_arr = array();
		$counts = $comment['TOTAL'];
		if (count($comment_data) > 0)
		{
			foreach($comment_data as $comt)
			{
				$i++;
				$comment_arr[] = array(
					'rank' => $i,
					'comments' => $comt['comments'],
					'score' => $comt['score'],
					'submit_tm' => $comt['submit_tm']
					);
			}
		}
		$softinfo = array(
			"softid" => $this -> params['softid'],
			"name" => $info['SOFT_NAME'],
		    "package" => $info['PACKAGENAME'],
			"icon" => $info['ICON'],
			"upload_tm" => $info['upload_tm'],
			'type' => $info['subname'],
			'typeid' => $info['category_id'],
      		"developer"=> $info['DEVELOPER'],
			"version" => $info['SOFT_VERSION'],
		    "version_code" => $info['SOFT_VERSION_CODE'],
		    "sign" => $info['SIGNATURE'],
			"apksize" => $info['SOFT_SIZE'],
			"desc" => $info['SOFT_DESCRIBE'],
			"downloaded" => $info['SOFT_DOWNLOAD_REGION'],
			"score" => $info['SOFT_STAR']/2,
			"thumb" => $thumb,
			"thumb_count" => $thumb_count,
			"comment" => $comment_arr,
			);      

		if($this->params['channel']=="2aa2b5c2b6cb4772bee74f05d86a159bfabfc092")//只在盛大的渠道会有二维码
			$softinfo["qrcodeurl"] = TF_SITE_HOST."/QRcode.php?url=".urlencode(SITE_HOST."/dl_app.php?s=".$softid)."&time=".$info['upload_tm']."&key=".md5(SITE_HOST."/dl_app.php?s=".$softid.GO_QRCODE_KEY);
		return json_encode($softinfo);
	}
}

?>
