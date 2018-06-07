<?php
/*
 *   安智小卖店发验证码worker
 */
include dirname(__FILE__).'/../init.php';
$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
    $redis = new GoRedisCacheAdapter($config);
} else {
    $redis = GoCache::getCacheAdapter('redis');
}

$model = new GoModel();
$gift_base = array();
ini_set('default_socket_timeout', -1);

load_helper('task');
$worker = get_task_worker();
$worker->addFunction("store_sendnum", "get_award");
while ($worker->work());

//get_award();

function get_award($jobs){
//function get_award(){
                global $model;

                global $redis;

                $jobs= $jobs->workload();

                $jobs = json_decode($jobs,true);
                print_r($jobs);
                $mobile = $jobs['mobile'];
                $imei = $jobs['imei'];
                $city = $jobs['city'];
                $school = $jobs['school'];
                $store_name = $jobs['store_name'];
                $shopid = $jobs['shopid'];
                $redis->pingConn();


                $activity_option = array(
                    'where' => array(
                            'tel' => $mobile,
                            'imei' => $imei
                    ),
                    'table' => 'store_order'
                );
                $res= $model -> findOne($activity_option,'lottery/lottery');

                if(empty($res)){
                        //检查set是否丢失
                        $num = $redis->getx('scard','azstore:mobile:set');
                        if($num==0){
                            //查库
                                $option = array(
                                            'table' => 'store_order',
                                            'field' => 'tel',
                                        );
                                $tel_arr = $model->findAll($option,'lottery/lottery');
                                if(!empty($tel_arr)){
                                    foreach($tel_arr as $v){
                                        $redis->saddSet('azstore:mobile:set',$v['tel'],90*86400);
                                    }
                                }
                        }

                        $rs = $redis->sIsMemberSet('azstore:mobile:set',$mobile);
                        if($rs == true){ //自己设备没有绑定时 输入的手机号已绑定过
                            echo 3;
                            return 3;
                        }
                }else if($res['tel']!= $mobile){
                    //自己设备绑定了 输入了其他手机号
                        echo 3;
                        return 4;
                }else{
                    if($res['status']!=1){
                        return -1;
                    }
                }

                $now_num = $redis -> get('azstore:yzmnum:imei:'.$imei.':'.$mobile.date('Ymd',time()));
                if($now_num>=5){
                    return -2;
                }

                $redis -> setx('incr','azstore:yzmnum:imei:'.$imei.':'.$mobile.date('Ymd',time()),1);


                $yzm = $redis->get('azstore:yzm:imei:'.$imei.':'.$mobile);
                if(!empty($yzm)){ //有效直接返回
                    //发短信 supwater
                    //$content = '您好，您的验证码是：'.$yzm.'，若非本人操作请忽略';
                    $content = '您的<'.$store_name.'>5元代金券兑换码为'.$yzm.'，有效期10分钟，请及时兑换';
                    $send_arr = array('phone'=>$mobile,'content'=>$content);
                    $send_rs=http_post_mobile($send_arr);
                    var_dump($send_rs);
                    echo 'in 10;';
                    echo "\n";
                    echo $yzm;
                    $vercode = $yzm;
                }else{
                    $vercode = get_vercode();
                }

                //$vercode = $redis->rpop('store:vercode:list');
                //if(empty($vercode)){
                //}

                //放入集合, 写数据库 设定有效期缓存 

                
                if(empty($res)){
                    //插入
                    $award_data = array(
                            'ver_code' => $vercode,
                            'tel' => $mobile,
                            'imei' => $imei,
                            'school' => $school,
                            'store_name' => $store_name,
                            'receive_tm' => time(),
                            'store_id' => $shopid,
                            'city' => $city,
                            //'update_tm' => time(),
                            '__user_table' => 'store_order'
                    );
                    $ret = $model -> insert($award_data,'lottery/lottery');
                    if(!$ret){
                        echo -1;
                        return -1;
                    }
                    $redis->set('azstore:bd:imei:'.$imei,$mobile,86400*90);
                    $redis->saddSet('azstore:mobile:set',$mobile,90*86400);
                    $redis->saddSet('azstore:imei:set',$imei,90*86400);
                }else{
                    //更新
                    $where = array(
                        'tel' =>$mobile,
                        'imei' =>$imei,
                    );
                    $data = array(
                        'ver_code' => $vercode,
                        'store_name' => $store_name,
                        'store_id' => $shopid,
                        'city' => $city,
                        'school' => $school,
                        'receive_tm' => time(),
                        '__user_table' => 'store_order'
                    );
                    $rett = $model -> update($where,$data,'lottery/lottery');
                    if(!$rett){
                        return -1;
                    }
                }

                $redis->set('azstore:yzm:imei:'.$imei.':'.$mobile,$vercode,600);
                //$redis->set('azstore:yzm:store_id:'.$vercode.':'.$shopid,1,600);//卖家10分验证用
                $redis->saddSet('azstore:vercode:set',$vercode,90*86400);
                //发短信 supwater
                //$content = '您好，您的验证码是：'.$vercode.'，若非本人操作请忽略';
                $content = '您的<'.$store_name.'>5元代金券兑换码为'.$vercode.'，有效期10分钟，请及时兑换';
                $send_arr = array('phone'=>$mobile,'content'=>$content);
                $send_rs = http_post_mobile($send_arr);
                var_dump($send_rs);
                echo "\n";
                echo $vercode;
                return 1;
}

function get_vercode()
{
    global $redis;
    global $model;
    //检查set是否丢失
    $num = $redis->getx('scard','azstore:vercode:set');
    if($num==0){
        //查库
            $option = array(
                        'table' => 'store_order',
                        'field' => 'ver_code',
                    );
            $code_arr = $model->findAll($option,'lottery/lottery');
            if(!empty($code_arr)){
                foreach($code_arr as $v){
                    $redis->saddSet('azstore:vercode:set',$v['ver_code'],90*86400);
                }
            }
    }

    $code = rand(10000000,99999999);
    $rs = $redis->sIsMemberSet('azstore:vercode:set',$code);
    if($rs == true){
        return get_vercode();
    }
    if(empty($code)){
        return get_vercode();
    }
    return $code;
}

function http_post_mobile($vals, $class='changty') {
	//global $action;
        $action ='sendSms';
	var_dump($vals);
	if(preg_match('/^192\.168\.0/i',$_SERVER['SERVER_ADDR']) || $_SERVER['SERVER_ADDR']=='10.0.3.15'|| $_SERVER['SERVER_ADDR']=='114.247.222.131') {
		$url = 'http://118.26.224.18/service.php.php?do='.$action.'&class='.$class;
		$host = 'Host: smsapi.goapk.com';
	} else {
		$url = 'http://192.168.1.18/service.php?do='.$action.'&class='.$class;
		$host = 'Host: smsapi.goapk.com';
	}

	//$url .= '&key=61783727fc7597c8b392bae377d7e9ed&rand='.microtime(true);
	$url .= '&key=46c6fe2c705f8fb48ed1e4b537ae4dd0&rand='.microtime(true);

	$res = curl_init();
	curl_setopt($res, CURLOPT_URL, $url);
	curl_setopt($res, CURLOPT_TIMEOUT, 15);
	curl_setopt($res, CURLOPT_HTTPHEADER, array($host,'Expect:'));
	curl_setopt($res, CURLOPT_POST, true);
	curl_setopt($res, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($res, CURLOPT_POSTFIELDS, $vals);
	$result = curl_exec($res);
	$http_code = curl_getinfo($res,CURLINFO_HTTP_CODE);
	$errno = curl_errno($res);
	$error = curl_error($res);
	curl_close($res);
var_dump($result);
	return array(
		'ret' => $result,
		'http_code' => $http_code,
	);
}
