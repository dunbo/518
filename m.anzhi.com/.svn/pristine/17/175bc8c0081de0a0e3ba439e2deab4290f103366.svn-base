<?php
/*
** 抽奖接口
*/
include_once ('./init.php');

//抽奖日志
$log_data = array(
    'imsi' => $imsi,
    'device_id' => $_SESSION['DEVICEID'],
    'activity_id' => $aid,
    'ip' => $_SERVER['REMOTE_ADDR'],
    'sid' => $_GET['sid'],
    'time' => time(),
	'type_lottery'=>5,
	'type_name'=>'其他',
    'key' => 'lottery'
);
permanentlog($activity_log_file, json_encode($log_data));

$return_arr = array(
    'status' => 0,
    'info' => array()
);

if (!$imsi_status) {
    $return_arr['status'] = 300;
    echo json_encode($return_arr);;
    exit;
}

// 尝试将用户可抽奖次数-1
$reduce_ret = reduce_lottery_num_by_1();
if (!$reduce_ret) {
	$return_arr['status'] = 300;
	echo json_encode($return_arr);;
	exit;
}
// 可以抽奖，获得用户剩余抽奖次数
$now_num = get_lottery_num();
//抽奖
//刷出来的次数 抽奖直接不中
//$share_key_today = 'gen_brush:share:imsi:'.$imsi.':aid:'.$aid.':'.date('Ymd',time());
$down_key_today = 'gen_brush:down:imsi:'.$imsi.':aid:'.$aid.':'.date('Ymd',time());
$home_key_today = 'gen_brush:home:imsi:'.$imsi.':aid:'.$aid.':'.date('Ymd',time());

//$share_key_today_time = $redis->get($share_key_today);
$down_key_today_time = $redis->get($down_key_today);
$home_key_today_time = $redis->get($home_key_today);

if($down_key_today_time==$home_key_today_time||$home_key_today_time==time())
{
	//刷的直接不中奖
	$the_award = -1;
	$brush = 1;
}else{	
	$brush = 2;		
	load_helper('task');
	$task_client = get_task_client();
	$prefix = "custom_imsi";
	$new_array=array(
			'aid'=>$aid,
			'imsi'=>$imsi,
			'prefix'=>$prefix,
			//'package' =>$_POST['pkg'],
		);

	$task_ret = $task_client->do('custom_imsi_lottery',json_encode($new_array));
	if($task_ret==-1)
	{
		$the_award = -1;
	}
	else
	{
		$task_ret = json_decode($task_ret, true);
		$the_award = $task_ret['prize_rank'];
		$award_id = $task_ret['award_id'];
	}
}


$return_arr['status'] = 200;
$return_arr['info']['award_level'] = $the_award;
$return_arr['info']['left_num'] = $now_num;
$return_arr['info']['award_id'] = $award_id;


// 抽到奖品记日志
if($the_award&&$the_award!=-1)
{
	$log_data = array(
		'imsi' => $imsi,
		'device_id' => $_SESSION['DEVICEID'],
		'activity_id' => $aid,
		'ip' => $_SERVER['REMOTE_ADDR'],
		'sid' => $_GET['sid'],
		'award_level' => $the_award,
		'time' => time(),
		'type_lottery'=>5,
		'type_name'=>'其他',
		'brush' => $brush,
		'award_id' => $award_id,//中奖纪录在表里的id
		'key' => 'object_award'
	);
	permanentlog($activity_log_file, json_encode($log_data));
}

// 无中奖
if ($the_award < 0) 
{
    // 没有中奖
    echo json_encode($return_arr);
    exit;
}
else
{
	// 将中奖信息放在数组中，并返回
	$return_arr['info']['award_level_name'] = $award_level_name_arr[$the_award];
	$return_arr['info']['award_name'] = get_prize_name($the_award);
	$return_arr['info']['award_type'] = 1;
	echo json_encode($return_arr);
	exit;
}