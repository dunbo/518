<?php
include_once (dirname(realpath(__FILE__)).'/../../init.php');
$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
$model = new GoModel();
$active_id = $_GET['aid'] ? $_GET['aid'] : $_POST['aid'];
//ctype_digit  检查时候是只包含数字字符的字符串（0-9）
if(!$_GET['ap_id'] && !ctype_digit($active_id)){
	exit;
}
$sid = $_GET['sid'] ? $_GET['sid'] : $_POST['sid'];
session_begin($sid);
$prefix = "singles_day";
$time  = time();
$today = date('Y-m-d',$time);
if($_SESSION['USER_IMSI']){
	$imsi = $_SESSION['USER_IMSI'];
}

//$imsi = '460028011771946';  //测试用
$imsi_status = 0;
if(!$imsi || $imsi == '000000000000000'){
	$imsi = '';
} else {
    $imsi_status = 1;
}
$activity_host = $configs['activity_url'];
$stop = $_GET['stop'] ? $_GET['stop'] : $_POST['stop'];
$tplObj -> out['stop'] = $stop;
if($stop != 1 && !$_GET['ap_id']){
	$res = activity_is_stop($active_id);
	if(!$res){
		$url = $activity_host."/lottery/{$prefix}/index.php?stop=1&aid=".$active_id."&sid=".$sid;
		header("Location: {$url}");
	}
}
//获取抽奖次数
function get_lottery_num() {
	global $model, $redis, $imsi,$active_id,$prefix;
	$r_cache_time = '5184000';//redis缓存时间为两个月
	$rkey_imsi_lottery_num = $prefix.":{$active_id}:lottery_num:{$imsi}";
	// 获得用户的可抽奖次数
	$lottery_num = $redis->get($rkey_imsi_lottery_num);
	if (empty($lottery_num) && $imsi) {
		// 可能没有此用户，或缓存已失效
		$option = array(
			'where' => array(
				'imsi' => $imsi,
				'aid' => $active_id,
			),
			'table' => 'imsi_lottery_num',
		);
		$find = $model->findOne($option, 'lottery/lottery');
		if ($find) {
			$lottery_num = (int)$find['lottery_num'];
		} else {
			//没有记录 插入一条记录
			$imsi_data = array(
				'aid' => $active_id,
				'imsi' => $imsi,
				'lottery_num'=>0,
				'time' => time(),
				'__user_table' => 'imsi_lottery_num',
			);
			$model -> insert($imsi_data,'lottery/lottery');
			$lottery_num = 0;
		}
		$redis->set($rkey_imsi_lottery_num,$lottery_num,$r_cache_time);
	}
	if(empty($lottery_num)&&!$imsi)//主要是要没有imsi的显示软件，lottery_num没值js会报错
	{
		$lottery_num = 0;
	}
	return $lottery_num;
}

function set_lottery_num($fields) {
	global $model,$imsi,$active_id;
	$where = array(
		'imsi' => $imsi,
		'aid' => $active_id,
	);
	$data = array(
		'lottery_num' => $fields,
		'time' => time(),
		'__user_table' => 'imsi_lottery_num',
	);
	$ret = $model->update($where, $data, 'lottery/lottery');
	return $ret;
}
function set_gm_num(){
	global $model,$imsi,$active_id,$time;	
	$day = date("Ymd");
	$option = array(
		'where' => array(
			'imsi' => $imsi,
			'aid' => $active_id,
			'date' => $day,
		),
		'table' => 'imsi_game_num',
	);
	$find = $model->findOne($option, 'lottery/lottery');
	if(!$find){
		$data = array(
			'aid' => $active_id,
			'imsi' => $imsi,
			'date'=>$day,
			'time' => $time,
			'game_num' => 1,
			'__user_table' => 'imsi_game_num',
		);
		$model -> insert($data,'lottery/lottery');
	}else{
		$where = array(
			'imsi' => $imsi,
			'date'=>$day,
			'aid' => $active_id,
		);
		$data = array(
			'game_num' => array("exp","`game_num`+1"),
			'time' => $time,
			'__user_table' => 'imsi_game_num',
		);
		$model->update($where, $data, 'lottery/lottery');	
	}
}
function add_lottery_num($is_share=1,$pkg=''){
	global $model, $redis, $imsi,$active_id,$prefix,$today;
//	if(!$imsi) return 0;
	if($is_share == 1){
		//分享
		$day_limit_key = $prefix.":".$active_id.":limit_gm_num:".$imsi.":".$today;
		$gm_num = $redis->get($day_limit_key);
		if($gm_num > 3) return false;
	}else{
		//下载
		$down_key =  $prefix.":".$active_id.":down_pkg:".$imsi.":".$pkg;
		$is_down_pkg = $redis->setnx($down_key,1);
        $redis->expire($down_key,86400*60);
		if(!$is_down_pkg) return false;
	}
	$lottery_num_key = $prefix.":{$active_id}:lottery_num:{$imsi}";
	$now_num = $redis->setx('incr',$lottery_num_key,1);
	set_lottery_num("`lottery_num`+1");
	return $now_num;
}
function get_soft_info($is_test){
	global $model,$redis,$prefix,$active_id;
	$soft_info_key = $prefix.":".$active_id.':soft_info';
	$soft_info = $redis->get($soft_info_key);	
	if($soft_info === null){
		if($is_test){
			$pkg_arr = array('com.knetp.lom2jni','cn.jj','com.netease.l10.anzhi','mgyly1.anzhi');
		}else{
			$pkg_arr = array('com.ss.android.article.lite','com.ss.android.ugc.live','com.ifeng.android','com.qq.reader');
		}
		$option = array(
			'table' => 'sj_soft AS A',
			'where' => array(
				'A.status' => 1,
				'A.hide' => 1,
				'A.package' => $pkg_arr,
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
			$v['describe'] = $describe[$v['package']];
			$soft_info[$v['package']] = $v;
		}
		$redis->set($soft_info_key,$soft_info,30*60);	
	}
	return $soft_info;
}

function set_imsi_address(){
	global $model,$imsi,$active_id, $prefix,$redis;
	$where = array('id' => $_POST['id']	);
	//lxname="+lxname+"&mobile_phone="+mobile_phone+"&address="+address,
	$data = array(
		'name' => $_POST['lxname'],
		'telephone' => $_POST['mobile_phone'],
		'address' => $_POST['address'],
		'time' => time(),
		'__user_table' => 'imsi_lottery_award',
	);
	$ret = $model->update($where, $data, 'lottery/lottery');
	$my_prize_key = $prefix.":".$active_id.':my_prize:'.$imsi;
	$redis->delete($my_prize_key);		
	return $ret;
}

function get_my_prize(){
	global $model,$redis,$imsi,$active_id,$prefix;
	$my_prize_key = $prefix.":".$active_id.':my_prize:'.$imsi;
	$my_prize = $redis->get($my_prize_key);	
	if($my_prize  == null){	
		$option = array(
			'where' => array(
				'imsi' => $imsi,
				'aid' => $active_id,
			),
			'table' => 'imsi_lottery_award',
		);
		$list = $model->findAll($option, 'lottery/lottery');
		$prize_name = get_prize_name();
		$my_prize = array();
		foreach($list as $key => $val){
			$my_prize[$val['id']]['prize_name'] = $prize_name[$val['award']];
			$my_prize[$val['id']]['name'] = $val['name'];
			$my_prize[$val['id']]['telephone'] = $val['telephone'];
			$my_prize[$val['id']]['address'] = $val['address'];
		}
		$redis->set($my_prize_key,$my_prize,30*60);	
	}
	return $my_prize;	
}
function get_prize_name(){
	global $model,$redis,$imsi,$active_id,$prefix;
	$prize_list_key = $prefix.":".$active_id.':prize_list';
	$prize_list = $redis->get($prize_list_key);	
	if($prize_list  == null){	
		$option = array(
			'where' => array('aid' => $active_id,'status'=>1),
			'table' => 'valentine_draw_prize',
			'field' => 'level,name'
		);
		$list = $model->findAll($option, 'lottery/lottery');
		$prize_list = array();
		foreach($list as $key => $val){
			$prize_list[$val['level']] = $val['name'];
		}
		$redis->set($prize_list_key,$prize_list,30*60);	
	}
	return $prize_list;		
}
function get_lottery_prize(){
	global $model,$redis,$imsi,$active_id,$prefix;
	$lottery_prize_key = $prefix.":".$active_id.':lottery_prize';
	$lottery_prize = $redis->get($lottery_prize_key);	
	if($lottery_prize  == null){	
		$option = array(
			'where' => array(
				'aid' => $active_id,
				'telephone'=> array("exp","!=''")
			),
			'table' => 'imsi_lottery_award',
			'order' => '`time` desc',
			'limit' => 10,
		);
		$list = $model->findAll($option, 'lottery/lottery');
		$prize_name = get_prize_name();
		$lottery_prize = array();
		foreach($list as $key => $val){
			$lottery_prize[$val['id']] = substr($val['telephone'],0,3)."***".substr($val['telephone'],7)." 获得".$prize_name[$val['award']]." ".date("Y-m-d",$val['time']);
		}
		$redis->set($lottery_prize_key,$lottery_prize,30*60);	
	}
	return $lottery_prize;		
}