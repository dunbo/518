<?php
include_once ('./fun.php');
session_begin($sid);
$build_query = http_build_query($_GET);
$url = "http://promotion.anzhi.com/lottery/{$prefix}/index.php?".$build_query;

if(isset($_SESSION['USER_UID'])){//已登录
	$uid = $_SESSION['USER_UID'];
}else{//未登录 跳转到首页
	if($_POST){
		exit(json_encode(array('code'=>2,'url'=>$url)));
	}else{
		header("Location: {$url}");
	}
}


	if($_GET['types'] == 1){
		//我的奖品
		$kind_award_list = get_user_kind_award_new($uid,$active_id,"{$prefix}",'valentine_draw_award');

                foreach($kind_award_list as $k=>$v){
                    $kind_award_list[$k]['num']=findNum($v['prizename']);
                }

		$tplObj -> out['kind_award_arr'] = $kind_award_list;	
		$res = get_couponid();
		$is_solid = 0;
		foreach($kind_award_list as $v){
			if($v['pid'] != $res['id']) $is_solid = 1;
		}
		$tplObj -> out['is_solid'] = $is_solid;//是否是实物奖品
	}else if($_GET['types'] == 2){
		//恭喜中奖
		$tplObj -> out['now'] = date('Y-m-d');
		$tplObj -> out['prizename'] = $_GET['prizename'];
		$tplObj -> out['prize_rank'] = $_GET['prize_rank'];
		if($_GET['prize_rank'] != 4){
			$tplObj -> out['is_solid'] = 1;//是否是实物奖品
		}
	}
	if($_GET['stop'] == 1){
		$tplObj -> out['stop'] = 1;
        }



function findNum($str=''){
        $str=trim($str);
        if(empty($str)){return '';}
        $result='';
        for($i=0;$i<strlen($str);$i++){
            if(is_numeric($str[$i])){
                $result.=$str[$i];
            }
        }
        return $result;
    }


$activity_option = array(
	'where' => array(
		'aid' => $active_id,
		'uid' => $uid,
	),
	//'cache_time' => 60*20,
	'table' => 'recharge_top_order'
);
$ret = $model -> findAll($activity_option,'lottery/lottery');
$num = count($ret);
$money = 0;
$deduction_num = 0;
foreach($ret as $v){
    $money = $money+$v['money'];
    $deduction_num = $deduction_num+$v['deduction_num'];
}
	$tplObj -> out['num'] = $num;
	$tplObj -> out['money'] = $money;
	$tplObj -> out['deduction_num'] = $deduction_num;


	$tplObj -> out['types'] = $_GET['types'];
	$tplObj -> out['static_url'] = $configs['static_url'];
	$tplObj -> out['new_static_url'] = $configs['new_static_url'];
	//用户信息
	$userinfo = get_user_info_new($uid,$active_id,"{$prefix}","recharge_top_order");


	$tplObj -> out['aid'] = $active_id;	
	$tplObj -> out['sid'] = $sid;	
	$tplObj -> out['prefix'] = $prefix;	
	$tplObj -> out['version_code'] = $_SESSION['VERSION_CODE'];	
	$tplObj -> display("lottery/{$prefix}/userinfo.html");	
