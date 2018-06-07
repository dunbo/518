<?php
include dirname(__FILE__).'/../init.php';
ini_set('default_socket_timeout', -1);
//define('FROM', 4);
if (isset($argv[1]))
{
	$suffix = '_' . $argv[1];
	$now_active_id = $argv[1];
}
else
	exit;
$db = 'sendnum';
$redis = new GoRedisCacheAdapter(load_config('sendNum/redis', 'sendNum'));
load_helper('task');
$worker = get_task_worker('sendNum');
$task_client = get_task_client('sendNum');
$uc_task_client = get_task_client('uctask');
$worker->addFunction("send_number_v55" . $suffix, "send_number_func");

while ($worker->work());
function send_number_func($jobs) {
	global $now_active_id;
	global $redis;
	global $task_client, $uc_task_client;
	$from_count = load_config('gift_from', 'common');
	if ( !($p = json_decode($jobs->workload(), true)) ) {
		return False;
	}
	$redis->pingConn();

	//传参
	$uid = $p['uid'];//论坛uid或者是用户唯一码sn
	$active_id = $p['active_id'];//活动id
	$ip = $p['ip'];//参与者的ip
	$now_time = $p['take_tm'];//参加的时间
    $from = $p['from'];
	$product = $p['product'];
	if (empty($from))
		$from = 4;
	$field = $from_count[$from];
	$date = date('Y-m-d',$now_time);//提交的日期
	$user_key = "UID:{$uid}:NUMBER_BOX";//用户礼包key
	$prize_key = "NUMBER_LIST:{$active_id}";//活动的奖品池
	$error_key = "ERROR:ACT:{$active_id}:UID:{$uid}:FROM:{$from}";//发号错误缓存key
	$count_key = "";
	$field_name = $active_id;

	$live_time = 600;//存活时间
	if ($now_active_id != $active_id)
		return false;

	try
	{
		$ret = $redis->gethash($user_key, $active_id);
	}
	catch (Exception $e)
	{
		file_put_contents('/tmp/redis-error.log', json_encode(array($p, $user_key)), FILE_APPEND);
	}
	if (!empty($ret))
	{
		if ($ret['status'] == 0)
		{
			$ret['status'] = 1;
			$redis->sethash($user_key, array($active_id => $ret));
		}
		return false;
	}

	//根据活动的限定条件判断是否能取到奖品
	$key = "ACTIVE:{$active_id}:DATE:{$date}:FROM:" . $from . ":DAY_CNT"; //按天限量
	$sended_key = "ACTIVE:{$active_id}:FROM:" . $from . ":SENDED_NUMBER"; //已发放号码
	$status_key = 'ACTIVE:'.$activity_id.':status';
	$status = $redis->get($status_key);
	
	if ($status > 0) {
		//已经进入淘号状态的数据
		$redis->set($error_key, 9, $live_time);
		return;
	}
	
	$error_no = 6;
	$active_info = get_active_info($active_id);
	$olgame_info = get_olgame_info($active_id);

	if ((intval($active_info['active_from']) & $from) != $from) {
		return;
	}
	if ($active_info['status'] == 0) {//已停用
		$redis->set($error_key, 3, $live_time);
		exit;
		return;
	}
	if($active_info['start_tm'] > $now_time) { //未开 始
		$redis->set($error_key, 1, $live_time);
		return;
	} elseif ($active_info['end_tm'] < $now_time) {//已结束
		$redis->set($error_key, 2, $live_time);
		exit;
		return;
	}
	$sended_num = $redis->getx("HLEN", $sended_key);
	//	var_dump($sended_num, $active_info['market_conf_cnt']);
	if ($sended_num >= $active_info[$field]) {
		//平台发放完毕
		$redis->set($error_key, 5, $live_time);
		return;
	}
	$v = $redis->exists($key);
	if ($v) {
		$v = $redis->get($key);
	} else {
		$v = get_active_usedcnt($active_id, $date, $ip, $uid, $from);
		$redis->set($key, $v, 88400);
	}
	if ($v >= $active_info['conf_cnt'] && $active_info['conf_cnt'] > 0) {
		//到达每天发放限制
		$redis->set($error_key, $error_no, $live_time);
		return;
	}

	//通过验证，从号码队列取号
	$num = $redis->rpop($prize_key);
	$num = json_decode($num, true);
	if (!empty($num)) {
		if ($from == 1 && !empty($active_info['bbs_score'])) {
			$ret = decrease_coin($active_id, $uid, $active_info['bbs_score'], $num);
			file_put_contents('/tmp/coin.log', $active_id . " $ret". "\n", FILE_APPEND);
			if ($ret != 1) {
				$redis->setlist($prize_key, $num);
				if ($ret == 0) {
					$error_no = 7;
				} else {
					$error_no = 8;
				}
				$redis->set($error_key, $error_no, $live_time);
				return;
			}
		}
		
		$v = $v + 1;
		$redis->pingConn();
		$redis->set($key, $v, 88400);
		$redis->sethash($user_key, array($active_id => array('code' => $num, 'status' => 1, 'take_tm' => $now_time)));
		$redis->sethash($sended_key, array($num => 1));
		$data = $p;
		$data['active_num'] = $num;
		$data['status'] = 1;
		$data['from'] = $from;
		$data['user_type'] = 1;
		#队列写入数据库
		$task_client->doBackground('update_sendnum_db', json_encode($data));
		
		$sended_num = $redis->getx("HLEN", $sended_key);
		if ($sended_num >= $active_info[$field]) {
			#生成最后领取的标示位
			$redis->set("ACTIVE:{$active_id}:from{$from}:COMPLETE_DATE",time(),36000);
			#记录活动id
			$redis->setx('lpush','ACTIVE:COMPLETE_LIST', "{$active_id}:{$from}");
		}		
		
		
		if($uid){
			$prizeFrom = 3;
			if($from == 1){
				$ucfrom = '004';//论坛
			}else if($from == 2){
				$ucfrom = '010';//游戏
			}else if($from == 4){
				$ucfrom = '014';//市场
                if ($product == 20) {
                    $ucfrom = '052';//什么值得玩
                }
			}else if($from == 8){
				$ucfrom = '002';//sdk
			}else if($from == 32){
                $ucfrom = '002';//sdk
				$prizeFrom = 8;
			}else if($from == 64){
                $ucfrom = '002';//sdk
                $prizeFrom = 12;
            }else if($from == 128){
                $ucfrom = '002';//sdk
                $prizeFrom = 13;
            }
			$gift_data = array(
				'serviceId' => $ucfrom,       //业务线id  ,礼包的使用业务线
				'pid' => $uid,//用户pid
				'userName' => '',//用户账户名
				'thrGiftId' => $active_id,//第三方奖品id
				'giftName' =>$active_info['active_name'],//奖品名称
				'prType' => 2,//奖品类型。2:礼包
				'IMEI' => '',
				'ip' => $ip,
				'drawTime' => $now_time,//奖品生成时间
				'sendStatus' => 1,//发放状态。0：未发送。1：已发送
				'endTime' => $olgame_info['exchange_end'],//奖品失效时间
				'sendTime' => $olgame_info['exchange_start'],//发放时间
				'giftQty' => 1,//数量
				'memo' => '',//备注
				'prizeFrom' => $prizeFrom,//数据来源 1 :抽奖  2：兑换  3：领取 
				'secrects' => $num,//卡密
				'status' => 0,//  0:未过期  1：已过期
				'softPack' =>  $olgame_info['apply_pkg'],//软件包名
				'fromServiceId' => '007',//数据来源业务线
				'use_way' => $olgame_info['usage'] ,//使用方法
				'use_range' => $olgame_info['usable'],//使用范围
				'image' => !empty($olgame_info['iconurl']) ? 'http://img3.anzhi.com'.$olgame_info['iconurl'] : '',//奖品图片
				'fileName' => $olgame_info['iconurl_gif'] ? 'http://img3.anzhi.com' .$olgame_info['iconurl_gif'] : '',//gif图片
			);
			//if($active_info['sync'] == 1){
				$uc_task_client->doBackground("user_gift_record", json_encode($gift_data));
			//}else{
				//日志
				$log_path = '/data/att/permanent_log/gift_work/'.date('Y-m-d').'/';
				if(!is_dir($log_path))  mkdir($log_path,0777,true);
				$log = date('Y-m-d H:i:s').':'.json_encode($gift_data)."\n";
				file_put_contents($log_path.'gift_record.log',$log,FILE_APPEND);
			//}
		}
	} else {
		//平台发放完毕
		$error_no = 5;
		$redis->set($error_key, $error_no, $live_time);
		return;
	}
}

//获取活动信息
function get_active_info($active_id) {
	global $db;
	$model = new GoModel();
	$option = array(
		'table' => 'sendnum_active',
		'where' => array(
			'id' => $active_id,
		),
		'cache_time' => '601'
	);
	$result = $model -> findOne($option,$db);
	return $result;
}

function get_olgame_info($active_id) {
    global $db;
    $model = new GoModel();
    $option = array(
        'table' => 'olgame_active',
        'where' => array(
            'active_id' => $active_id,
        ),
        'field' => 'apply_pkg,exchange_start,exchange_end,usable,`usage`',
        'cache_time' => 600,
    );
    $result = $model -> findOne($option,$db);
    
    $where = array(
        'A.package' => $result['apply_pkg'],
    );
    $option = array(
        'where' => $where,
        'table' => 'sj_soft AS A',
        'field' => 'A.softname,A.softid,B.iconurl,iconurl_96,iconurl_72,iconurl_125',
        'join' => array(
            'sj_soft_file AS B' => array(
                'on' => array('A.softid', 'B.softid')
            )
        ),
        'order' => 'A.softid desc'
    );
    $soft = $model->findOne($option);
    if ($soft) {
        $icon_order = array(
            'iconurl_160',
            'iconurl_125',
            'iconurl_96',
            'iconurl_72',
            'iconurl',
        );
        $result['softname'] = $soft['softname'];
        $option = array(
            'table' => 'sj_icon',
            'where' => array(
                'softid' => $soft['softid']
            ),
        );
        $res = $model->findOne($option);
        if ($res) {
            foreach ($icon_order as $icon_key) {
                if (!empty($res[$icon_key])) {
                    $result['iconurl'] = $res[$icon_key];
                    break;
                }
            }
            $result['iconurl_gif'] = $res['iconurl_gif'];
        } 
        if (empty($result['iconurl'])) {
            foreach ($icon_order as $icon_key) {
                if (!empty($soft[$icon_key])) {
                    $result['iconurl'] = $soft[$icon_key];
                    break;
                }
            }
        }
    }
    return $result;
}

function get_active_usedcnt($active_id, $date, $ip, $uid, $from) {
	global $db;
	$option = array(
		'table' => 'sendnum_number_' . $active_id,
		'where' => array(
			//'active_id' => $active_id,
			'from' => $from,
			'status' => 1,
		),
		'field' => 'count(id) as cnt',
	);
	$option['where']['take_tm'] = array('exp','>='.strtotime($date).' and take_tm <= '.strtotime($date.' 23:59:59'));
	$model = new GoModel();
	$result = $model -> findOne($option,$db);
	$cnt = $result['cnt'];
	return $cnt;
}

function decrease_coin($gift_id, $uid, $coin, $giftcode) {
	static $salt = 'EWdi9dR81';
	$tm = time();
	$auth = md5($gift_id . $uid . $tm . $salt);
	$data = array(
		'delcredit' => $coin,
		'giftid' => $gift_id,
		'giftcode' => $giftcode,
		'uid' => $uid,
		'tm' => $tm,
		'auth' => $auth,
	);
	$ch = curl_init();
	$url = 'http://bbs.zhiyoo.com/api/gift.php?action=delcredit';

    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    $content = curl_exec($ch);
    $info = curl_getinfo($ch);
    //echo "{$info['http_code']} \n";
    curl_close($ch);
	//file_put_contents('/tmp/hwq.log', json_encode($data) . $content . "\n", FILE_APPEND);
	$content = json_decode($content, true);
	if ($content['error'] === 0) {
		return 1;
	} elseif ($content['error'] === 4) {
		return 2;
	} else {
		return 0;
	}
}
