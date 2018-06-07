<?php
define("SOAP_LOCATION",'http://searchapi.goapk.com/api/webserver/http.php');
define("SOAP_URI",'dict/api/webserver/http.php'); 
class getPackageType {
	public $params;
	public function __construct($param)
	{
		$this->params = $param;
	}
	
	public function getData()
	{
		$package = $this->params['package'];
		$softname = $this->params['softname'];
		
		$data = gomarket_action('soft.GoGetSoftDetailPackage', array('PACKAGE_NAME' => $package, 'EXTRA_OPTION_FIELD'=>array('category_id', 'category_name', 'filesize')));
		$result = array(
			'KEY' => 'getPackageType',
			'CODE' => 200,
		);
		
		if (!$data && $softname) {
			$soap_client= new SoapClient(null, array('location' => SOAP_LOCATION, 'uri' => SOAP_URI));
			$json = json_decode($soap_client->search($softname, array('j'=>1)),true);
			$res = $json['data'];
			$softlist = load_model('softlist');
			$softid = 0;
			foreach ($res as $k => $d) {
				$softid = $k;
				break;
			}
			if ($softid > 0) {
				$apps = $softlist->getSoftInfos($softid);
				$app = $apps[$softid];
				$result['DATA'] = array(
					'CATEGORY_ID' => $app['category_id'],
					'CATEGORY_NAME' => $app['category_name'],
					'SIZE' => $app['filesize'],
				);
			}
		} elseif($data) {
            $result['DATA'] = array(
                'CATEGORY_ID' => $data['category_id'],
                'CATEGORY_NAME' => $data['category_name'],
                'SIZE' => $data['filesize'],
            );			
		}
		
		if (!$result['DATA']) {
			$result['CODE'] = 204;
		}
		return json_encode($result);
	}
}
