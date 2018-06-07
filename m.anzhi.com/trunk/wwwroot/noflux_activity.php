<?php 

include_once (dirname(realpath(__FILE__)).'/init.php');
//0:发送失败;1:发送成功;2:电话号段不存在;3电话格式错误;4:未获取验证码;5:未获取电话号码;
$model = new GoModel();

$aid = $_GET['aid'];
$IMSI = $_GET['IMSI'];
if($_GET['TEL']){
	$telphone = $_GET['TEL'];
}else{
	$telphone = $_GET['telphone'];
}
//$telphone = '13141008303';
$sid = $_GET['sid'];

if($telphone && $sid){
	if(preg_match("/1[3458]{1}\d{9}$/",$telphone)){
		$my_telphone = substr($telphone,0,7);
		$option = array(
			'where' => array(
				'number' => $my_telphone,
				'type' => 1
			),
			'table' => 'mobile_number_area'
		);
		$result = $model -> findOne($option);
		if($result){
			$log_data = array(
				'telphone' => $telphone,
				'ip' => $_SERVER['REMOTE_ADDR'],
				'aid' => $aid,
				'sid' => $sid,
				'IMSI' => $IMSI,
				'time' => time(),
				'key' => 'no_flow'
			);
			permanentlog('noflux_activity.log', json_encode($log_data));
		}
		if($_GET['TEL']){
			if($result){
				setCookie("NOFLUX_{$aid}",$aid,time()+3600*24*6);
				setCookie("TELPHONE_{$aid}",$telphone,time()+3600*24*6);
				setCookie("IMSI_{$aid}",$IMSI,time()+3600*24*6);
				echo 100;
				return 100;
				exit;
			}else{
				echo 2;
				return 2;
				exit;
			}
		}else{
			if($result){
				$config = load_config('cache/noflux');
				if ($config) {
					$memcache = new GoMemcachedCacheAdapter($config);
				} else {
					$memcache = GoCache::getCacheAdapter('memcached');
				}	
				$my_code = $memcache -> get($telphone);  //查询缓存是否有未过期的验证码
				if(!$my_code){   //若缓存已过期，查询数据库是否有未过期验证码
					$my_time = time() - 600;
					$my_option = array(
						'where' => array(
							'telphone' => $telphone,
							'sid' => $sid,
							'create_tm' => array('exp',">{$my_time}"),
							'status' => 0
						),
						'table' => 'sj_noflux_activity'
					);
					$my_result = $model -> findOne($my_option);
					if($my_result){
						$my_code = $my_result['checkcode'];
						$memcache -> set($telphone,$my_code,600);
					}else{				//若数据库也没有未过期验证码，重新生成验证码并写入缓存
						$my_code = random_code(6);
						$data = array(
							'telphone' => $telphone,
							'checkcode' => $my_code,
							'create_tm' => time(),
							'update_tm' => time(),
							'status' => 0,
							'__user_table' => 'sj_noflux_activity'
						);
						$code_result = $model -> insert($data);
						if($code_result){
							$memcache -> set($telphone,$my_code,600);
						}
					}
				}
		
				if($my_code){
					
					
					//发送验证码
					load_helper('task');
					$task_client = get_task_client();
					$rs = array('phone' => $telphone, 'content' => "您好，您的验证码是：{$my_code}  有效期10分钟");
					$result = $task_client -> doBackground("send_message",serialize($rs));
					echo 1;
					return 1;
					
				}else{
					echo 4;
					return 4;
				}
			}else{
				echo 2;
				return 2;
			}
		}
	}else{
		echo 3;
		return 3;
	}
}else{
	echo 5;
	return 5;
}


function  random_code($num){
	for($i= 0;$i< $num;$i++ ){
		$my_code .= mt_rand(0,9);
	}
	return $my_code;
}