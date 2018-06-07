<?php
	require_once dirname(__FILE__) . '/init.php';
	$receive = $_GET['data'];
	$pkg = $_GET['pkg'];
	if(!$pkg){
		echo "包名不可为空！";
		exit;
	}
	//$pkg = "com.ywqc.xuan";
	$active_id = 1132;		
	$soft_info = get_soft_info($active_id,$pkg);
	$str = "window.AnzhiActivitys.downloadForActivity({$active_id},{$soft_info[$pkg]['softid']},'{$pkg}','{$soft_info[$pkg]['softname']}',{$soft_info[$pkg]['version_code']},{$soft_info[$pkg]['filesize']},0);";
	echo $str;
	exit;
	function get_soft_info($active_id,$pkg){
		$model = new GoModel();
		$config = load_config('lottery_cache/redis',"lottery");
		if ($config) {
			$redis = new GoRedisCacheAdapter($config);
		} else {
			$redis = GoCache::getCacheAdapter('redis');
		}
		$prefix = "activity_soft_info";	
		$soft_info_key = $prefix.":".$active_id.':soft_info';
		$soft_info = $redis->get($soft_info_key);	
		if($soft_info === null){
			$option = array(
				'table' => 'sj_soft AS A',
				'where' => array(
					'A.status' => 1,
					'A.hide' => 1,
					'A.package' => $pkg,
					'B.package_status' => 1,
				),
				'join' => array(
					'sj_soft_file AS B' => array(
						'on' => array('A.softid','B.softid'),
					)
				),
				'field' => 'A.softid,A.softname,A.package,A.version_code,A.version_code,A.total_downloaded,B.iconurl_125,B.filesize',
				'order' => 'A.softid desc',
				'group' => 'A.package',
			);	
			$softinfo = $model->findAll($option);
			$soft_info = array();
			foreach($softinfo as $k => $v){
				$v['iconurl'] = getImageHost().$v['iconurl_125'];
				$soft_info[$v['package']] = $v;
			}
			$redis->set($soft_info_key,$soft_info,86400);	
		}
		return $soft_info;
	}	
	function ip_limit($ip) {
		//return true;
		$allow_ip = array(
			"192.168.0.*",
			"192.168.1.*",
			"192.168.3.*",
			"42.62.4.130",
			"103.103.12.82",
			"118.26.203.12"
		);	

		foreach($allow_ip as $key=>$val) {
			if(strpos($val,'*')===FALSE) {
				if($val==$ip) {
					return TRUE;
				}
			} else {
				$val = str_replace('*','[0-9]{1,3}',$val);
				$val = str_replace('.','\.',$val);
				if(preg_match("/{$val}/",$ip)) {
					return TRUE;
				}
			}
		}
		return false;
	}	