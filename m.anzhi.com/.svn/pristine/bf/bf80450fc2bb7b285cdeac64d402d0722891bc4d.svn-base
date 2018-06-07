<?php
$args_key = '3d19#1k2@9li';
$args_iv = '2fo10al1';

function getChannel($chl) {
	global $model;
	$option = array(
		'table' => 'tf_channel',
		'where' => array(
				'status' =>1,
				'username' =>$chl,
		),
		'field' => '*',
	);
	$res = $model->findOne($option,'toufang/toufang');
	if ($res) {
		return $res;
	} else {
		return false;
	}
}

function getSdkChannel($cid) {
	global $model;
	$option = array(
		'table' => 'sdk_channel',
		'where' => array(
			'status' => 1,
			'market_c_id' => $cid,
		),
		'field' => 'id,channel_name',
	);
	$res = $model->findOne($option);
	if ($res) {
		return $res;
	} else {
		return false;
	}
}

function getChannelRatio($channel_id, $date) {
	global $model;
	$option = array(
		'table' => 'tf_split_ratio',
		'where' => array(
			'status' =>1,
			'channel_id' =>$channel_id,
			'ef_date' => array("exp","<='".$date."'"),
		),
		'field' => 'anzhi,other',
		'order' => 'ef_date desc,update_tm desc'
	);
	$res= $model->findOne($option,'toufang/toufang');
	if ($res) {
		return $res;
	} else {
		return false;
	}
}

function getChannelPackage($channel_id, $date, $package='') {
	global $model;
	$option = array(
		'table' => 'tf_soft',
		'where' => array(
			'tf_date' => array("exp","<='".$date."'"),
			'type' => 0,
			'channel_id' => $channel_id,
			'status' =>1
		),
	);
	if (!empty($package)) {
		$option['where']['package'] = $package;
	}
	$res = $model->findAll($option,'toufang/toufang');
	if ($res) {
		return $res;
	} else {
		return false;
	}
}

function getChannelPackageDetail($channelSoft) {
	global $model;
	$filter = load_model('newfilter');
	$softmodel = load_model('softlist');
	foreach ($channelSoft as $v) {
		$packages[] = $v['package'];
	}
	$package_list = $filter->get_pkg2id($packages);
	$id2pkg = array();
	foreach ($package_list as $package => $ids) {
		foreach ($ids as $id) {
			$id2pkg[$id] = $package;
		}
	}
	$pkg2id = array_flip($filter->filterSoftId($id2pkg, array(), false));
	foreach($channelSoft as $k=>$v){
		$package = $v['package'];
		$beid = $v['beid'];
		if($beid!=0){
			$option = array(
				'table' => 'sj_push_behavior',
				'where' => array(
					'status' => 1,
					'beid' => $beid,
				),
				'field' => 'value',
			);
			$ret_beid = $model->findOne($option);
			$channel_id = $ret_beid['value'];

			$option = array(
				'table' => 'sj_soft',
				'where' => array(
					'status' => 1,
					'hide' => 1024,
					'package' => $package,
					'channel_id' => array('exp', "like ',$channel_id,'"),
				),
				'field' => 'softid,softname,channel_id',
				'order' => 'softid desc'
			);
			$ret = $model->findOne($option);
			if ($ret) {
				$pkg2id[$package] = $ret['softid'];
			}
		}
	}
	$infos = $softmodel->getsoftinfos(array_values($pkg2id), array());
	$result = array();
	foreach ($channelSoft as $k=>$v) {
		$package = $v['package'];
		$softid = $pkg2id[$package];
		if (!empty($softid)) {
			$v['info'] = $infos[$softid];
			$result[] = $v;
		}
	}
	return $result;
}

function formatSoftDetail($infos, $channel, $date) {
	$channel_id = $channel['id'];
	$rs_sdk = getSdkChannel($channel['cid']);
	$icon_url = getIconHost();
	$pre_down_url = "http://mm.anzhi.com/dl_app.php?channel={$chl}&s=";
	$dev_model = load_model('dev');
	$ratio = getChannelRatio($channel_id, $date);
	$other = $ratio['other'];
	$time = time();
	$result = array();
	foreach($infos as $k => $v){
		$package = $v['package'];
		$introduce = $v['introduce'];
		$info = $v['info'];
		$softid = $v['info']['softid'];

		$app = $dev_model->formatSoftDetail($info);

		$price = $v['price'] * $other / 100;
		$price = substr(sprintf("%.4f", $price), 0, -2);
		$piece = array();
		$piece['id'] = $softid;
		$piece['name'] = $info['softname'];
		$piece['bid_type'] = 1;
		$piece['price'] = (double)$price;
		$piece['pkg'] = $package;
		$request = array(
			'q' => mt_rand(0,9999999), //随机值
			'softid' => $softid,
			'package' => $package,
			'ts' => $time,
			'chl' => $channel['username'],
		);
		if ($channel['co_type'] == 1) {
			//直接返回下载链接
			$request['act'] = 'download';
			$piece['replaceurl'] = 'http://' . $_SERVER['HTTP_HOST'] . '/interface/distribute/download.php?s=' . urlencode(encryptArgs($request));
		} elseif ($channel['co_type'] == 2) {
			//返回下载中间接口
			$request['act'] = 'info';
			$piece['replaceurl'] = 'http://' . $_SERVER['HTTP_HOST'] . '/interface/distribute/info.php?s=' . urlencode(encryptArgs($request));
		} else {
			$piece['replaceurl'] = '';
		}
		$piece['expire'] = $time + 86400 * 365;
		$piece['introduce'] = $introduce;
		$piece['filesize'] = $app['SOFT_SIZE'];
		$piece['parentid'] = $info['parentid'];
		$piece['parent_name'] = $info['parent_name'];
		$piece['category_id'] = $info['category_id'];
		$piece['category_name'] = $info['category_name'];
		$piece['iconurl'] = $icon_url . $info['iconurl_96'];
		$piece['screen_hot_surl'] = $app['SOFT_SCREENSHOT_URL'];
		$piece['developer'] = $app['DEVELOPER'];
		$piece['download_num'] = num_format($app['SOFT_DOWNLOAD_REGION'], 2);
		$piece['version'] = $app['SOFT_VERSION'];#应用版本
		$piece['version_code'] = $app['SOFT_VERSION_CODE'];#应用版本
		$result[] = $piece;
	}
	return $result;
}

function writeLog($tolog) {
	$h = date('H');
	permanentlog("distribute_access_{$h}.log", json_encode($tolog), '', 'mm.anzhi.com');
}

function encryptArgs($data) {
	global $args_key;
	global $args_iv;
	$enc_str = json_encode($data);
	$res = encrypt3Des($args_key, $args_iv, $enc_str);
	return $res;
}

function decryptArgs($crypted_str) {
	global $args_key;
	global $args_iv;
	$decrypted = decrypt3Des($args_key, $args_iv, $crypted_str);
	return json_decode($decrypted, true);
}

function getSoftUrl($softid) {
	$softlist = load_model('softlist');
	$info = $softlist->getsoftinfos($softid);
	$host = getApkHost();
	return $host . $info[$softid]['url'];
}

function generateReqId() {
	list($msec, $sec) = explode(' ', microtime());
	$suffix = sprintf('%06d',mt_rand(0,999999));
	$id = md5($sec . $msec . $suffix);
	return $id;
}

function decrypt3Des($key, $iv, $str) {
	$des = new GoDes($key, $iv);
	$des->setMode('cbc');
	$decrypted = $des->getDecodedDecrypt($str);
	return $decrypted;
}

function encrypt3Des($key, $iv, $str) {
	$des = new GoDes($key, $iv);
	$des->setMode('cbc');
	$encrypted = $des->getCodedEncrypt($str);
	return $encrypted;
}

function encryptChannelArgs($str, $channel) {
	$key = calcChannelKey($channel);
	$iv = $channel['iv'];
	$res = encrypt3Des($key, $iv, $str);
	return $res;
}

function decryptChannelArgs($crypted_str, $channel) {
	$key = calcChannelKey($channel);
	$iv = $channel['iv'];
	$decrypted = decrypt3Des($key, $iv, $crypted_str);
	return json_decode($decrypted, true);
}

function calcChannelKey($channel) {
	$key = substr(md5($channel['username'] . $channel['secret_keys']), 0, 24);
	return $key;
}
