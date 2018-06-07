<?php
include_once ('./fun.php');
//$rs = get_app_info('com.joym.Rowing3D.anzhi');
//$rs = get_app_info('com.zwwx.sgzs.anzhi');
//var_dump($rs);


//短链接
$a_url = $configs['activity_url']."/lottery/appointment/index.php?is_share=1&cbm=1&aid=".$aid;
load_helper('utiltool');
$aa_url= shortenSinaUrl($a_url);
$tplObj -> out['aa_url'] = $aa_url;


$build_query = http_build_query($_GET);
if($configs['is_test'] == 1){
	$h_str = 'dev.';
	$tplObj -> out['is_test'] = 1;
}
$center_url = "http://".$h_str."i.anzhi.com/mweb/account/login?serviceId=014&serviceVersion=5410&serviceType=0&redirecturi=";
$login_url = $center_url.$configs['activity_url']."lottery/appointment/index.php?".$build_query;
//$login_url = $center_url."http://activity.test.anzhi.com/lottery/appointment/index.php?".$build_query;//6.4测试完 切换回来
$tplObj -> out['login_url'] = $login_url;	
$share = $_GET['share'];
$tplObj->out ['prefix_url'] = $configs['activity_url'];
$tplObj->out ['activity_video_url'] = $configs['activity_video_url'];

//日志
$log_data = array(
		"imsi" => $_SESSION['USER_IMSI'],
		"device_id" => $_SESSION['DEVICEID'],
		"activity_id" => $active_id,
		"ip" => $_SERVER['REMOTE_ADDR'],
		"sid" => $sid,
		"time" => $time,
		"user" => $_SESSION['USER_UID'] && $_SESSION['USER_ID'] != 13176 ? $_SESSION['USER_NAME'] : '',
		'uid'=> $_SESSION['USER_UID'] && $_SESSION['USER_ID'] != 13176 ? $_SESSION['USER_UID'] : '',
		'key' => 'show_homepage'
);
permanentlog('activity_'.$active_id.'.log', json_encode($log_data));


brush_second_do($aid);
get_brush_bysn();
                //$home_key_today = 'gen_pre_brush:home:uid:'.$uid.':aid:'.$aid.':'.date('Ymd',time());
                //$redis->setnx($home_key_today,time());
                //$redis->expire($home_key_today,86400);


$activity_option = array(
	'where' => array(
		'id' => $aid
	),
	'cache_time' => 300,
	'table' => 'sj_activity'
);
$activity_result = $model -> findOne($activity_option);

$page_option = array(
	'where' => array(
		'ap_id' => $activity_result['activity_page_id']
	),
	'cache_time' => 300,
	'table' => 'sj_activity_page'
);

$page_result = $model -> findOne($page_option);
//$page_result['ap_desc'] = htmlspecialchars_decode($page_result['ap_desc']);
//$page_result['no_marquee'] = htmlspecialchars_decode($page_result['no_marquee']);

$page_result['ap_desc'] = str_replace(array("\r\n", "\r", "\n"), "", htmlspecialchars_decode($page_result['ap_desc']));
$page_result['no_marquee'] = str_replace(array("\r\n", "\r", "\n"), "", htmlspecialchars_decode($page_result['no_marquee']));
$page_result['soft_bg'] = str_replace(array("\r\n", "\r", "\n"), "", htmlspecialchars_decode($page_result['soft_bg']));
$page_result['soft_bg'] = str_replace("'",'"',$page_result['soft_bg']);


                //与市场接口共用的缓存
		$option = array(
			'select_option' => array(
				'table' => 'sj_activity_page',
				'field' => '*',
				),
			'id_field' => 'ap_id',
			'cache_time' => '1800',
		);
		$model = new GoModel();
		$model->custom_detail_option = $option; 
		$model->custom_detail_option['cache'] = 'soft_redis/redis';
		$rett = $model->getDetailById($activity_result['activity_page_id']);
		$tplObj -> out['yy_num'] = s_num_format($rett[$activity_result['activity_page_id']]['lottery_num_limit']+$rett[$activity_result['activity_page_id']]['marquee_text_color']);


user_loging_new();
if(isset($_SESSION['USER_UID']) && $_SESSION['USER_ID'] != 13176){//已登录
	if($_GET['is_register'] == 1){
		//注册成功日志
		$log_data = array(
			'uid' => $_SESSION['USER_UID'],
			'imsi' => $_SESSION['USER_IMSI'],
			'device_id' => $_SESSION['DEVICEID'],
			'activity_id' => $active_id,
			'ip' => $_SERVER['REMOTE_ADDR'],
			'sid' => $sid,
			'time' => $time,
			'key' => 'register'
		);
		permanentlog('activity_'.$active_id.'.log', json_encode($log_data));
	}else{	
		//登录日志
		$log_data = array(
			'uid' => $_SESSION['USER_UID'],
			'imsi' => $_SESSION['USER_IMSI'],
			'device_id' => $_SESSION['DEVICEID'],
			'activity_id' => $active_id,
			'ip' => $_SERVER['REMOTE_ADDR'],
			'sid' => $sid,
			'time' => $time,
			'key' => 'login'
		);
		permanentlog('activity_'.$active_id.'.log', json_encode($log_data));
	}
	$tplObj -> out['username'] = $_SESSION['USER_NAME'];
	$tplObj -> out['is_login'] = 1;
	$tplObj -> out['uid'] = $_SESSION['USER_UID'];

}else{//未登录
	$tplObj -> out['is_login'] = 2;
}




//$mark = md5($mac.$imei);
//$user_id_key = 'SUBSCRIBER:DATA:'."user_id:{$mark}";


$redis_soft = new GoRedisCacheAdapter(load_config('cache/soft_redis'));
$pre_info = $redis_soft->gethash($user_id_key,$active_id);

if(!empty($pre_info)) //设备预约过了
{
    $tplObj -> out['is_yuyue'] = 1;
    if(!empty($uid))
	{
		//登陆账号处理
		$is_give_key = 'gen_pre:sign:uid:'.$uid.':aid:'.$active_id;
		$notexist_t3 = $redis->setnx($is_give_key,1);
		$redis->expire($is_give_key,86400*90);
		$gift_window=0;
		if($notexist_t3===false){
			//该账号已预约过
		}
		else
		{
			$now_num = $redis -> setx('incr',$imsi_num,1);
                        $redis->expire($imsi_num,86400*90);
			$where = array(
				'aid' => $active_id,
				'uid' => $uid
			);
			$data = array(
				'lottery_num' => $now_num,
				'update_tm' => time(),
				'__user_table' => 'gm_lottery_num'
			);
			$result = $model -> update($where,$data,'lottery/lottery');
			if(!$result)
			{
				$data = array(
					'aid' => $active_id,
					'uid' => $uid,
					'username' => $username,
					'imsi' => $imsi,
					'imei' => $imei,
                                        'mac' => $_SESSION['MAC'],
					'lottery_num' => $now_num,
					'update_tm' => time(),
					'__user_table' => 'gm_lottery_num'
				);
				$result = $model -> insert($data,'lottery/lottery');
			}

			//自动发礼包
			$gift_option_new = array(
				'where' => array(
					'ap_id' => $page_result['ap_id'],
					'is_give_gift' => 1,
					'status' => 1
				),
				'table' => 'gm_bespoke_gift',
			);
			$gift_arr = $model -> findAll($gift_option_new,'lottery/lottery');

			$gift_names = '';
			foreach($gift_arr as $v)
			{
				if($v['gtype']!=2){ //2 是礼券
				    $g_id = $v['id'];
				    $is_give_gift_key = 'gen_pre:gift:uid:'.$uid.':aid:'.$active_id.':'.$g_id;
				    $is_real_get_gift= 'gen_pre:realgetgift:uid:'.$uid.':aid:'.$aid.':'.$g_id;
				    //$is_over_gift_key = 'gen_pre:giftover:aid:'.$active_id.':'.$g_id;
				    $notexist_t3 = $redis->setnx($is_give_gift_key,1);
				    $redis->expire($is_give_key,86400*90);
				    if($notexist_t3===false)
				    {
				        continue;
				        //echo 2;exit(0); //已领取过了
				    }
				    else
				    {
				        $gift_number = $redis -> rpop("bespoke_gift:virtual_{$g_id}");
				        $gift_number = json_decode($gift_number,true);
				        if(!empty($gift_number)){
				            $gift_names = $gift_names.$v['name'].',';
				            $gift_window=1;
				            $where = array(
				                'first_text' =>$gift_number['first_text'], //redis里来的
				                'pid' =>$g_id,
				            );
				            $data = array(
				                'imsi' => $imsi,
				                'uid' => $uid,
				                'status' => 1,
				                'update_tm' => time(),
				                '__user_table' => 'gm_bespoke_gift_code'
				            );
				            $model -> update($where,$data,'lottery/lottery');
				    
				            //用户领取礼包
				            $log_data = array(
				                'gift'=>$gift_number['first_text'],
				                'gid'=>$g_id,
				                'uid'=>$uid,
				                'username' => $_SESSION['USER_NAME'],
				                'imsi' => $_SESSION['USER_IMSI'],
				                'device_id' => $_SESSION['DEVICEID'],
				                'time' => time(),
				                'activity_id' => $aid,
				                'key' => 'receive_gift'
				            );
				            $redis->set($is_real_get_gift,1,86400*30);
				            permanentlog('activity_'.$aid.'.log', json_encode($log_data));
				        }else{
				            $redis->set($is_over_gift_key,1);//礼包已发完 暂时没用
				        }
				    }
				    $tplObj -> out['gift_window'] = $gift_window;
				    $tplObj -> out['gift_names'] = substr($gift_names,0,-1);
				}else{
				   // /m.anzhi.com/trunk/wwwroot/lottery/xyq/fun.php
				    //include_once (dirname(realpath(__FILE__)).'/../xyq/fun.php');
				    if($tplObj -> out['is_login']==1)
				    {
				        $res = grant_coupon($tplObj -> out['uid']);
				    
				        if($_SERVER['SERVER_ADDR'] == '118.26.203.23' ){
				            $url = 'http://dev2.user.anzhi.com:9021/pay/internal/coupon/exchange';//测试地址
				        }else{
				            $url = 'http://pay.anzhi.com/pay/internal/coupon/exchange';//线上地址
				        }
				        $url = 'http://dev2.user.anzhi.com:9021/pay/internal/coupon/exchange';
				        
				        $data_arr =  array(
				            'uid' => $tplObj -> out['uid'],
				            'couponId' => $v['gift_file'],
				            'exchangeTime' =>time(),
				            'exchangeNum' => 1,
				            //'activityName' => '充值抽大奖100%中奖',
				            'activityName' => '活动预约礼券发放',
				            'activityId' => $active_id,
				        );
				        $js_data = json_encode($data_arr);
				        $data = array(
				            'serviceId' => '',
				            'data' => $js_data
				        );
				        $res = httpGetInfo($url,'', http_build_query($data),"");
				        //用户领取礼券
				        $log_data = array(
				            'gift'=>$gift_number['first_text'],
				            'gid'=>$g_id,
				            'uid'=>$uid,
				            'username' => $_SESSION['USER_NAME'],
				            'imsi' => $_SESSION['USER_IMSI'],
				            'device_id' => $_SESSION['DEVICEID'],
				            'time' => time(),
				            'activity_id' => $aid,
				            'httpGetInfo'=>$res,
				            'key' => 'receive_lijuan'
				        );
				        permanentlog('activity_'.$aid.'.log', json_encode($log_data));
				    }
				    
				}
			}
        }
    }else{

		//登陆账号处理
		$open_login = 'gen_pre:openlogin:imei:'.$imei.':aid:'.$active_id;
		$notexist_login = $redis->setnx($open_login,time());
		$redis->expire($open_login,86400*90);
                if($notexist_login===false){
                    $tplObj -> out['is_open_login'] = 0;
                }else{
                    //设备预约，但是没登录，提示登录引导
                    $tplObj -> out['is_open_login'] = 1;
                }

    }
}else{
    //设备没预约 但是账号预约过，将设备设为已预约
    $is_give_key = 'gen_pre:sign:uid:'.$uid.':aid:'.$active_id;
    $notexist_t3 = $redis->get($is_give_key);
    if($notexist_t3==1){
        yuyue();
        $tplObj -> out['is_yuyue'] = 1;
        $tplObj -> out['is_tell_client'] = 1;//改变客户端的预约状态
    }else{
        $tplObj -> out['is_yuyue'] = 0;
    }
}

if(!empty($uid)){

        //每日赠送次数
	if($page_result['free_day_switch'] == 1){
		$is_give_lotterynum_key = 'gen_pre:is_give_lotterynum:uid:'.$uid.':aid:'.$active_id.':'.date('Ymd',time());
		$notexist_t3_give_num = $redis->setnx($is_give_lotterynum_key,1);
		$redis->expire($notexist_t3_give_num,86400);
		if($notexist_t3_give_num===true){
			$now_num = $redis -> setx('incr',$imsi_num,1);
                        $redis->expire($imsi_num,86400*90);
			$where = array(
				'aid' => $active_id,
				'uid' => $uid
			);
			$data = array(
				'lottery_num' => $now_num,
				'update_tm' => time(),
				'__user_table' => 'gm_lottery_num'
			);
			$result = $model -> update($where,$data,'lottery/lottery');
			if(!$result)
			{
				$data = array(
					'aid' => $active_id,
					'uid' => $uid,
					'username' => $username,
					'imsi' => $imsi,
					'imei' => $imei,
                                        'mac' => $_SESSION['MAC'],
					'lottery_num' => $now_num,
					'update_tm' => time(),
					'__user_table' => 'gm_lottery_num'
				);
				$result = $model -> insert($data,'lottery/lottery');
			}
                }
        }


        $now_num = $redis -> get($imsi_num);
        if(empty($now_num)){
                $user_option_new = array(
                        'where' => array(
                                'aid' => $active_id,
                                'uid' => $uid,
                        ),
                        'table' => 'gm_lottery_num',
                );
                $user_option_new = $model -> findOne($user_option_new,'lottery/lottery');
                $redis -> set($imsi_num,intval($user_option_new['lottery_num']),86400*60);
                $now_num = $redis -> get($imsi_num);
        }
	
	$tplObj -> out['now_num'] = $now_num;
}


//抽奖奖品相关操作
$prize_option = array(
	'where' => array(
		'aid' => $aid,
		'status' => 1
	),
        'field' => 'name,pid,level,pic_path,type',
	'table' => 'gm_lottery_prize'
);
$prize_result = $model -> findAll($prize_option,'lottery/lottery');
foreach($prize_result as $key => $val){
	$prize_level[] = $val['level'];
}


$gift_option = array(
	'where' => array(
		'ap_id' => $page_result['ap_id'],
		'status' => 1,
	    'gtype' =>1
	),
	'order' => 'rank desc',
	'cache_time' => 300,
	'table' => 'gm_bespoke_gift'
);
if($page_result['is_telephone']){$gift_option['where']['gtype']=2;} //is_telephone   礼券标识

$gift_result = $model -> findAll($gift_option,'lottery/lottery');
foreach($gift_result as $k=>$v){
    $is_real_get_gift= 'gen_pre:realgetgift:uid:'.$uid.':aid:'.$active_id.':'.$v['id'];
    //$is_give_gift_key = 'gen_pre:gift:uid:'.$uid.':aid:'.$active_id.':'.$v['id'];
    //$is_over_gift_key = 'gen_pre:giftover:aid:'.$active_id.':'.$v['id'];
    $ress = $redis->get($is_real_get_gift);
    if($ress==1){
        $gift_result[$k]['is_use'] = 1;
    }
}

$package_option = array(
	'where' => array(
		'page_id' => $page_result['ap_id'],
		'rank' => 2,
		'status' => 1
	),
	'cache_time' => 300,
	'table' => 'sj_actives_soft'
);
$package_result = $model -> findOne($package_option);


//资讯
$news_option = array(
	'where' => array(
		'apply_pkg' => $package_result['package'],
		'status' => 2
	),
        'order' => 'release_tm desc',
	'cache_time' => 300,
	'limit' => 3,
	'table' => 'sj_olgame_news'
);
$news_result = $model -> findAll($news_option);

$news_arr = array();
foreach($news_result as $kk=>$vv){
    $news_arr[$vv['id']]['LAUNCH']['FLG']='33619968';
    $news_arr[$vv['id']]['LAUNCH']['PARAM']['FLG']='33619968';
    $news_arr[$vv['id']]['LAUNCH']['PARAM']['ID']=$vv['id'];
    $news_arr[$vv['id']]['LAUNCH']['PARAM']['PACKAGE_NAME']=$vv['apply_pkg'];
    $news_arr[$vv['id']]['LAUNCH']['PARAM']['TITLE']=$vv['news_name'];
    $news_arr[$vv['id']]['LAUNCH']['PARAM']['URL']=$configs['activity_url'].'gamenews_'.$vv['id'].'.html';
}



//print_r($news_arr);



$tplObj -> out['news_json'] = json_encode($news_arr);


$tplObj -> out['news_count'] = count($news_result);

$tplObj -> out['news_result'] = $news_result;
$tplObj -> out['gift_result'] = $gift_result;

array_multisort($prize_level,SORT_ASC,$prize_result);

//轮播图处理
$qianzhui = getImageHost();

$pic_arr = array();
if($page_result['ranking_no_pic1']!=null&&$page_result['ranking_no_pic1']!=''){
    $pic_arr[] = $qianzhui.$page_result['ranking_no_pic1'];
}
if($page_result['ranking_pic1']!=null&&$page_result['ranking_pic1']!=''){
    $pic_arr[] = $qianzhui.$page_result['ranking_pic1'];
}

if($page_result['uppage_color']!=null&&$page_result['uppage_color']!=''){
    $pic_arr[] = $qianzhui.$page_result['uppage_color'];
}
if($page_result['ap_download_link']!=null&&$page_result['ap_download_link']!=''){
    $pic_arr[] = $qianzhui.$page_result['ap_download_link'];
}
if($page_result['telephone_warning']!=null&&$page_result['telephone_warning']!=''){
    $pic_arr[] = $qianzhui.$page_result['telephone_warning'];
}

$jianjie = strip_tags($page_result['soft_bg']);
$tplObj -> out['jianjie_cale'] = $jianjie;


//后台通用样式替换
if($page_result['show_award']==1){
    $page_result['second_text_color']='#414141';
    $page_result['third_text_color']='#02b2ff';
    $page_result['bg_color']='#eaeaea';
    $page_result['button_text_color']='#ffffff';
    $page_result['button_color']='#ffb926';
    $page_result['info_color']='#ffffff';
    $page_result['submit_button_color']='#ffb926';
    $page_result['share_bgcolor']='#8dd8ff';
    $page_result['button_pic']='';
}

    //$page_result['soft_bg']="【安智市场】".$page_result['soft_bg'].$aa_url;

if($_GET['azfrom']=='azplay')
{
    $t_arr = ['安智市场','安智'];
    $page_result['share_text'] = str_replace($t_arr,'什么值得玩',$page_result['share_text']);
}

$tplObj -> out['pic_json'] = json_encode($pic_arr);
$tplObj -> out['is_share'] = $_GET['is_share'];

$tplObj -> out['share'] = $share;
$tplObj -> out['imgurl'] = $qianzhui;
$tplObj -> out['prize_results'] = $prize_result;
$tplObj -> out['prize_result_str'] = json_encode($prize_result);
$tplObj -> out['prize_count'] = count($prize_result);
$tplObj -> out['activity_result'] = $activity_result;
$tplObj -> out['page_result'] = $page_result;
$tplObj -> out['imsi'] = $_SESSION['USER_IMSI'];
$tplObj -> out['sid'] = $_GET['sid'];
$version_code = $_SESSION['VERSION_CODE'];
$tplObj -> out['version_code'] = $version_code;
$tplObj -> out['aid'] = $aid;
$tplObj -> out['static_url'] = $configs['static_url'];
$tplObj -> out['new_static_url'] = $configs['new_static_url'];

if($page_result['lottery_style'] == 1){
    $tplObj -> display('lottery/appointment/coactivity_tiger.html');
}elseif($page_result['lottery_style'] == 2){
        $tplObj -> display('lottery/appointment/coactivity_sudoku.html');
}elseif($page_result['lottery_style'] == 3){
        $tplObj -> display('lottery/appointment/coactivity_turntable.html');
}
