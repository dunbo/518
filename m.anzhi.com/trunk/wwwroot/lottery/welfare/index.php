<?php
/*
** 福利首页
*/

include_once(dirname(realpath(__FILE__)).'/init.php');

// $_GET['typeid']用于判断是不是分类列表页
$tplObj->out['typeid'] = $_GET['typeid'];

// 获取所有有效福利类型
$option = array(
    'order' => 'pos asc,id desc',
    'cache_time' => 600,
    'table' => 'fl_welfare_type'
);
$option['where']['status'] = 1;
if(!empty($_GET['typeid'])){
	$option['where']['id'] = $_GET['typeid'];
}
$result = $model->findAll($option, 'lottery/lottery');

$time = time();
// 关联所有单个福利
if(!empty($result)){
	foreach($result as $key => $val){
		$where = array(
			'where' => array(
				'typeid' => $val['id'],
				'status' => 1,
				'start_tm' => array('exp', "<{$time}"),
				'end_tm' => array('exp', ">{$time}"),
			),
			'order' => 'pos asc,end_tm asc',
			'table' => 'fl_welfare',
			'cache_time' => 600,
		);
		if(count($result)>1){
			$where['limit'] = $val['list_num'];
		}
		$welfare = $model->findAll($where, 'lottery/lottery');
		// 领取人数实时更新
		foreach($welfare as $k => $v){
			$rkey_receive_random_num = 'welfare:'.$v['id'].':receive:num';
			if(!empty($redis->get($rkey_receive_random_num))){
			    $welfare[$k]['click_num'] = $redis->get($rkey_receive_random_num);
			}else{
				$welfareOne = $model->findOne(array(
					'where' => array(
						'id' => $v['id'],
					),
					'field' => 'click_num,init_val',
					'table' => 'fl_welfare',
				), 'lottery/lottery');
				$redis->setx('set', $rkey_receive_random_num, (int)($welfareOne['init_val']+$welfareOne['click_num']), 600);
				$welfare[$k]['click_num'] = $welfareOne['click_num'] + $welfareOne['init_val'];
			}
			
			$welfare[$k]['list_pic'] = json_decode($v['list_pic'], true);
		}
		$result[$key]['welfare'] = $welfare;
	}
}

//关联软件信息
if(!empty($result)){
	foreach($result as $key => $val){
		if(!empty($val['welfare'])){
			foreach($val['welfare'] as $k => $v){
				$pkg_info = gomarket_action('soft.GoGetSoftDetailPackage', array(
					'PACKAGE_NAME' => $v['package'],
					'VR' => 3,
					'EXTRA_OPTION_FIELD' => array(
						'A.iconurl_72','A.desc',
					),
				));
				$val['pkg_info'][$k] = $pkg_info;
			}
			$result[$key] = $val;
		}else{
			unset($result[$key]);
		}
	}
}

if(empty($_GET['typeid'])){
	// 福利首页日志
	$log_data = array(
	    'imsi' => $imsi,
	    'device_id' => $_SESSION['DEVICEID'],
	    'ip' => $_SERVER['REMOTE_ADDR'],
	    'sid' => $sid,
	    'time' => time(),
	    'key' => 'show_homepage'
	);
	permanentlog($welfare_log_file, json_encode($log_data));
}else{
	// 分类列表页日志
	$log_data = array(
	    'imsi' => $imsi,
	    'device_id' => $_SESSION['DEVICEID'],
	    'ip' => $_SERVER['REMOTE_ADDR'],
	    'sid' => $sid,
	    'time' => time(),
	    'welfare_type_id' => $_GET['typeid'], //福利类型id
	    'welfare_type_name' => $result[0]['name'], //福利类型名称
	    'key' => 'welfare_type'
	);
	permanentlog($welfare_log_file, json_encode($log_data));
}

$tplObj->out['result'] = $result;
if(!empty($result)){
	if(count($result)>1){
		$tplObj->out['count'] = 2;
	}else{
		$tplObj->out['count'] = 1;
	}	
}else{
	$tplObj->out['count'] = 0;
}
$tplObj->display('lottery/welfare/index.html');