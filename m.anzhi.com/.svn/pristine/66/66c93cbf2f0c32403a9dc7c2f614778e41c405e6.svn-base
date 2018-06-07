<?php
include dirname(__FILE__).'/../newgomarket.goapk.com/init.php';
$dltime = time();
$hour = date('H');
$ip = onlineip();
$do = $_GET["do"];
$softObj = load_model('sjsoft');
$model = new GoModel();
if ($do == "dlapk"){
	if(array_key_exists('puid',$_GET)){
		$puid = $_GET['puid'];
		//$popularapk = $softObj -> getDataList('pu_pupolarlink',array('where' => array('puid' => $puid , 'status' => 1)));
		if (!empty($_GET['appkey'])) {
			$option = array(
				'table' => 'sj_channel',
				'where' => array(
					'status' => 1,
					'chl_cid' => $_GET['appkey'],
				),
				'field' => 'cid',
			);
			$channel = $model->findOne($option);
			if (!empty($channel)) {
				$option = array(
					'table' => 'sj_market',
					'where' => array(
						'status' => 1,
						'cid' => $channel['cid'],
					),
					'order' => 'version_code desc'
				);
				$market = $model->findOne($option);
				if (!empty($market['apkurl'])) {
					permanentlog('popularize_' . $hour . '.json', json_encode(array(
						'action' => "link",
						'dl_time' => $dltime,
						'type' => "popularize",
						'apk' => $market['apkurl'],
						'appkey' => $_GET['appkey'],
						'ip' => $ip,
						'puid' => $puid ? $puid : "",
						'referer' => $_SERVER['HTTP_REFERER'] ? $_SERVER['HTTP_REFERER'] : '',
					)));
					toLocation(getApkHost($market) . $market['apkurl']);
					exit;
				}
			}
		} else {
			$option = array(
				'table' => 'pu_pupolarlink',
				'where' => array('puid' => $puid , 'status' => 1),
				'cache_time' => 600,
			);
			$popularapk = $model->findAll($option);
			$pkg_name = $popularapk[0]['pkg_name'];
			$url = $popularapk[0]['url'];
			if(empty($url)){
				$crawl_uri = getApkHost()  . "market/{$pkg_name}";
			}else{
				$crawl_uri = getApkHost()  . "{$url}{$pkg_name}";
			}
			$src = $pkg_name;
		}
	}else if(array_key_exists('src',$_GET)){
		$src = $_GET["src"];
		$crawl_uri = getApkHost() . "/market/{$src}";
	}

	if (true){
		permanentlog('popularize_' . $hour . '.json', json_encode(array(
			'action' => "link",
			'dl_time' => $dltime,
			'type' => "popularize",
			'apk' => $src,
			'ip' => $ip,
			'puid' => $puid ? $puid : "",
			'referer' => $_SERVER['HTTP_REFERER'] ? $_SERVER['HTTP_REFERER'] : '',
		)));
		if(empty($url)){
			toLocation(getApkHost() . "/market/{$src}");
		}else{
			toLocation(getApkHost() . "{$url}{$src}");
		}
		exit;
	}

	$versions = array("1.5.1", "1.5", "1.4", "1.3.6", "1.3.5", "1.3.4");
	foreach($versions as $idx => $version){
		$types = array("apk", "zip");
		foreach ($types as $idy => $type){
			if($src) {
				if(strpos($src,"apk")||strpos($src,"zip")) $the_file = "GoMarket{$version}_{$src}";
				else $the_file = "GoMarket{$version}_{$src}.{$type}";
			}else{
				$the_file = "GoMarket{$version}.{$type}";
			}
			$path = STATIC_DATA . "/market/{$the_file}";
			$crawl_uri = getApkHost() . "/market/{$the_file}";
			$headers = get_headers($crawl_uri);
			$status = substr($headers[0], 9, 3);
			if (file_exists($path) || $status == 200){
				permanentlog('popularize_' . $hour . '.json', json_encode(array(
					'action' => "link",
					'dl_time' => $dltime,
					'type' => "popularize",
					'apk' => $the_file,
					'ip' => $ip,
					'referer' => $_SERVER['HTTP_REFERER'] ? $_SERVER['HTTP_REFERER'] : '',
				)));
				toLocation(getApkHost() . "/market/{$the_file}");
				exit;
			}
		}
	}


	$markets = $softObj -> getLatestMarkets();
	foreach ($markets as $idx => $info) if ($info["firmware"] > "3"){
		permanentlog('popularize_' . $hour . '.json', json_encode(array(
			'action' => "link",
			'dl_time' => $dltime,
			'type' => "popularize",
			'apk' => substr($info["apkurl"], 1),
			'ip' => $ip,
			'referer' => $_SERVER['HTTP_REFERER'] ? $_SERVER['HTTP_REFERER'] : '',
		)));
		toLocation(getApkHost() . $info["apkurl"]);
		exit;
	}
}
toLocation("http://m.goapk.com");
