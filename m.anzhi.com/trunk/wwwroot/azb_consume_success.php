<?php
	require_once dirname(__FILE__) . '/init.php';
	$receive = $_GET['data'];
	$ip = $_SERVER['REMOTE_ADDR'];	
	$ret = ip_limit($ip);
	if(!$ret){
		echo "ip:".$ip."无权限";
		exit;
	}else{
		permanentlog('azb_consume_success.log',date("Y-m-d H:i:s")."|success|".$receive );
		echo 'success';
		return 'success';
	}
	function ip_limit($ip) {
		//return true;
		$allow_ip = array(
			"192.168.0.*",
			"192.168.1.*",
			"192.168.3.*",
			"42.62.4.130",
			"218.241.82.226",
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