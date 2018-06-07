<?php
//万能手机助手软件详情接口
class zsGetSoftDetail {
	public $params;
	public function __construct($param)
	{
		$this->params = $param;
	}
	
	public function getData()
	{
		$softid = $this->params['softid'];

		$parameters = array(
			'EXTRA_OPTION_FIELD' => array(
				'intro',
				'score',
				'file_time',
				'filesize',
			),
			'ID' => $softid,
			'VR' => 8
		);

		$_SESSION['RES'] = 'h';
		$res = gomarket_action('soft.GoGetSoftDetail',$parameters);
		$return = array(
			'CODE' => 200,
			'KEY' => $this -> params['action'],
		);
		//[应用id1, 应用名称,应用包名,应用图标,应用下载地址],
		if ($res) {
			$return['DATA'] = array(
				"softid" => $softid, //应用id
				"package" => $res['PACKAGENAME'], //应用包名
				"name" => $res['SOFT_NAME'], //应用名称
				"icon" => $res['ICON'], //应用图标地址
				"version" => $res['SOFT_VERSION'], //版本号
				"download" => $res['SOFT_DOWNLOAD_REGION'], //下载量
				"url" => "http://m.anzhi.com/interface/index.php?action=download&softid={$softid}&channel={$this->params['channel']}", //下载地址
				"intro" => $res['SOFT_DESCRIBE'], //应用简介,
				"thumb" => $res['SOFT_SCREENSHOT_WIFI_URL'] //截图
			);
		} else {
			$return['CODE'] = 204;
		}
		return json_encode($return);
	}
}