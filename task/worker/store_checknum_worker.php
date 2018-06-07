<?php
/*
 *   安智小卖店校验验证码worker
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
$worker->addFunction("store_checknum", "get_award");
while ($worker->work());

//get_award();

function get_award($jobs){
//function get_award(){
                global $model;

                global $redis;

                $jobs= $jobs->workload();

                $jobs = json_decode($jobs,true);
                print_r($jobs);
                $store_id = $jobs['store_id'];
                $yzm = $jobs['yzm'];
                $check_num = $jobs['check_num'];
                $redis->pingConn();


                $activity_option = array(
                    'where' => array(
                            'store_id' => $store_id,
                            'status' => array('exp','!=1'),
                            'exchange_tm' => array('exp','>='.strtotime(date('Y-m-d')).' and exchange_tm<='.time())
                    ),
                    'field'=>'count(*)',
                    'table' => 'store_order'
                );
                $rest= $model -> findOne($activity_option,'lottery/lottery');

                if($rest['count(*)']>=$check_num){
                    echo 5;
                    return 5;
                }


                $activity_option = array(
                    'where' => array(
                            'ver_code' => $yzm,
                            'store_id' => $store_id,
                            'status' =>1
                    ),
                    'table' => 'store_order'
                );
                $res= $model -> findOne($activity_option,'lottery/lottery');

                if(empty($res)){
                    echo 2;
                    return 2;
                }else{
                    if(time()-$res['receive_tm']>600){
                        echo 2;
                        return 2;
                    }
                    
                    //开始兑换
                    $where = array(
                            'ver_code' => $yzm,
                            'store_id' => $store_id,
                            'status' =>1
                    );
                    $data = array(
                        'status' => 2,
                        'exchange_tm' => time(),
                        '__user_table' => 'store_order'
                    );
                    $rett = $model -> update($where,$data,'lottery/lottery');
                    if(!$rett){
                        return -1;
                    }

                    //金额检测，发邮件
                    $is_send = $redis->get('azstore:sendmail'.$store_id.date('Ymd',time()));
                    if(empty($is_send)){

                        $activity_option = array(
                            'where' => array(
                                    'id' => $store_id,
                                    'status' => 1,
                            ),
                            'cache_time' => 3600,
                            //'field'=>'store_name,remind_num,remind_mails',
                            'table' => 'store_user'
                        );
                        $userinfo = $model -> findOne($activity_option,'lottery/lottery');
                        if($userinfo['remind_num']==0){
                            return 1;
                        }

                        $activity_option = array(
                            'where' => array(
                                    'store_id' => $store_id,
                                    'status' => array('exp','!=1'),
                                    'exchange_tm' => array('exp','>='.strtotime(date('Y-m-d')).' and exchange_tm<='.time())
                            ),
                            'field'=>'count(*)',
                            'table' => 'store_order'
                        );
                        $ress= $model -> findOne($activity_option,'lottery/lottery');
                        echo $model->getsql();


                        if($ress['count(*)']*5>=$userinfo['remind_num']){//supwater
                            echo 'jinru';
                            //报警
                            $mailarr = explode(';',$userinfo['remind_mails']);
                            var_dump($userinfo['remind_num'],$mailarr);

                            foreach($mailarr as $mv){
                                if(!empty($mv)){
                                    $tmp = _http_post_email(array(
                                        'email'=>trim($mv),
                                        'name'=>'安智管理',
                                        'subject'=>'[报警]'.$userinfo['school'].'-'.$userinfo['store_name'].'今日订单金额已超限',
                                        'content'=>$userinfo['city'].'-'.$userinfo['school'].'-'.$userinfo['store_name'].'-'.$userinfo['shopkeeper'].'今日订单金额已超过'.$userinfo['remind_num'].'元（后台设置的报警金额）'
                                    ));
                                }
                            }
                            $redis->set('azstore:sendmail'.$store_id.date('Ymd',time()),1);
                        }
                    }
                    return 1;
                }
}

function _http_post_email($vals) {
	$url = 'http://192.168.1.143/service.php';
	//$url = 'http://118.26.203.22/service.php';
	//$url = 'http://42.62.4.179/service.php';
	$host = 'Host: mail.goapk.com';
	$url .= '?key=019f160f2ae0c8990eb94653bd101857';

	$res = curl_init();
	curl_setopt($res, CURLOPT_URL, $url);
	curl_setopt($res, CURLOPT_TIMEOUT, 5);
	curl_setopt($res, CURLOPT_HTTPHEADER, array($host));
	curl_setopt($res, CURLOPT_POST, true);
	curl_setopt($res, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($res, CURLOPT_POSTFIELDS, $vals);
	$result = curl_exec($res);
	$http_code = curl_getinfo($res,CURLINFO_HTTP_CODE);
	curl_close($res);
	return array(
		'ret' => $result,
		'http_code' => $http_code,
	);
}
