<?php
include_once ('./fun.php');
$sid = $_GET['sid'] ? $_GET['sid'] : $_POST['sid'];
$active_id = $_GET['aid'] ? $_GET['aid'] : $_POST['aid'];
session_begin($sid);
$build_query = http_build_query($_GET);
$url = "http://promotion.anzhi.com/lottery/xy2/yx_index.php?".$build_query;

if(isset($_SESSION['USER_UID'])){//已登录
	$uid = $_SESSION['USER_UID'];
}else{//未登录 跳转到首页
	if($_POST){
		exit(json_encode(array('code'=>2,'url'=>$url)));
	}else{
		header("Location: {$url}");
	}
}
if($_POST){
		$kind_award_gift = get_user_kind_gift_new_yd(2,$uid,$active_id,"yd","xy2_draw_gift");	

		$prize_list = get_user_kind_award_new_yd(2,$uid,$active_id,"yd","xy2_draw_award");	


                if(empty($kind_award_gift)&&empty($prize_list)){
                    echo 1;exit(0);
                }else{
                    echo 2;exit(0);
                }
}

	$tplObj -> out['pid'] = $_GET['pid'];	
	$tplObj -> out['package'] = $_GET['package'];	
	$tplObj -> out['softname'] = $_GET['softname'];	
	$tplObj -> out['gift_num'] = $_GET['gift_num'];	
	$tplObj -> out['prizename'] = $_GET['prizename'];	

        $nowday = date('m月d日',time());
	$tplObj -> out['nowday'] = $nowday;

		$kind_award_gift = get_user_kind_gift_new_yd(2,$uid,$active_id,"yd","xy2_draw_gift");	
		$tplObj -> out['gift_prize_arr'] = $kind_award_gift;

		$prize_list = get_user_kind_award_new_yd(2,$uid,$active_id,"yd","xy2_draw_award");	
                $tplObj -> out['prize_list'] = $prize_list;



                if(empty($prize_list)&&empty($prize_list_ka)){
		    $tplObj -> out['is_yincang'] = 1;
                }else{
		    $tplObj -> out['is_yincang'] = 2;
                }


	$tplObj -> out['static_url'] = $configs['static_url'];
	//用户信息
	$userinfo = get_user_info_new($uid,$active_id,'yd','xy2_draw_userinfo');
	$tplObj -> out['phone'] = $userinfo['phone'];	
	$tplObj -> out['contact_name'] = $userinfo['contact_name'];	
	$tplObj -> out['address'] = $userinfo['address'];	
        $tplObj -> out['aid'] = $active_id;	
        $tplObj -> out['lfrom'] = $_GET['lfrom'];	
	$tplObj -> out['sid'] = $sid;	
        $tplObj -> out['new_static_url'] = $configs['new_static_url'];
	$tplObj -> out['version_code'] = $_SESSION['VERSION_CODE'];	
	$tplObj -> display('lottery/xy2/yx_myprize_page.html');


        //用户我的奖品--实物
function get_user_kind_award_new_yd($type,$uid,$aid,$prefix,$table){
	list($redis,$model) = load_config_redis();	
	$kind_award_list = $redis -> getlist("{$prefix}:{$aid}_type:{$type}_draw_award:{$uid}");
	if(!$kind_award_list){
		$option = array(
			'where' => array(
				'uid' => $uid,
				'type' => $type,
				'aid' => $aid,
			),
			'table' => $table,
			'field' => 'id,aid,uid,username,pid,prizename,create_tm',
		);
		$kind_award = $model->findAll($option,'lottery/lottery');	
		if(!$kind_award) return false;
		$kind_award_list = array();
		foreach((array)$kind_award as $k => $v){
			$kind_award_list[$v['id']] = $v;
			$kind_award_list[$v['id']]['time'] = date("Y-m-d",$v['create_tm']);
		}
		unset($kind_award);
		$redis -> setlist("{$prefix}:{$aid}_type:{$type}_draw_award:{$uid}",$kind_award_list,30*60);
	}else{
		foreach($kind_award_list as $k => $v){
			$kind_award_list[$k] = json_decode($v,true);
		}		
	}	
	return $kind_award_list;
}
//用户我的奖品--礼包
function get_user_kind_gift_new_yd($type,$uid,$aid,$prefix,$table){
	list($redis,$model) = load_config_redis();	
	$gift_prize_list = $redis -> getlist("{$prefix}:{$aid}_type:{$type}_gift_prize:{$uid}");
	if(!$gift_prize_list){
		$option = array(
			'where' => array(
				'status' => 1,
				'type' => $type,
				'uid' => $uid,
				'aid' => $aid,				
			),
			'table' => $table,
			'field' => 'gift_number,uid,package,softname,update_tm',
		);
		$kind_gift = $model->findAll($option,'lottery/lottery');	
		if(!$kind_gift) return false;
		$gift_prize_list = array();
		foreach((array)$kind_gift as $k => $v){
			$gift_prize_list[$v['gift_number']] = $v;
			$gift_prize_list[$v['gift_number']]['time'] = date("Y-m-d",$v['update_tm']);
		}
		$redis -> setlist("{$prefix}:{$aid}_type:{$type}_gift_prize:{$uid}",$gift_prize_list,86400*10);
		unset($kind_gift);
	}else{
		foreach($gift_prize_list as $k => $v){
			$gift_prize_list[$k] = json_decode($v,true);
		}		
	}
	return $gift_prize_list;
	
}
