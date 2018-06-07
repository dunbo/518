<?php
//万能手机助手banner接口
class zsGetBanner {
	public $params;
	public function __construct($param)
	{
		$this->params = $param;
	}
	
	public function getData()
	{
		$chl = $this->params['channel'];
		$chl = 'webmarket';
		$web_pic_model = load_model("pu_webmarket_show_picture");
		list($result,$picount) = $web_pic_model -> get_picture($chl);
		
		$return = array(
			'CODE' => 200,
			'KEY' => $this -> params['action'],
		);
		if ($result) {
			$data = array();
			$img_host = getImageHost();
			foreach ($result as $val) {
				$softid = 0;
				$url = $val['link'];
				if (preg_match('/www\.anzhi\.com\/pkg\/(.*)/i', $url, $m)) {
					$package = $m[1];
					$res = gomarket_action('soft.GoGetSoftDetailPackage', array('PACKAGE_NAME' => $package));
					$softid = $res['ID'];
				} elseif (preg_match('/www\.anzhi\.com\/soft_(\d+)\.html/i', $url, $m)) {
					$softid = $m[1];
				}
				if (!empty($softid)) {
					$data[] = array($softid, $img_host. $val['picurl']);
				}
			}
			$return['DATA'] = $data;
		} else {
			$return['CODE'] = 204;
		}
		return json_encode($return);
	}
}