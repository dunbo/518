<?php
include_once ('./fun.php');
session_begin();
$build_query = http_build_query($_GET);
$url = $activity_host."/lottery/{$prefix}/index.php?".$build_query;

if( isset($_SESSION['USER_UID']) ) {//已登录
	$uid = $_SESSION['USER_UID'];
}else {
	//未登录 跳转到首页
	if($_POST) {
		exit(json_encode(array('code'=>2,'url'=>$url)));
	}else {
		header("Location: {$url}");
	}
}

$uid = $_SESSION['USER_UID'];
$aid = $active_id;
$pkg = trim($_POST['pkg']);
if( $_POST['type'] == 1 ) {
	//防刷
	brush_second_do($aid, 2, 3);
	//防刷处理
	$aollow_key	=	$prefix.':'.$active_id.':'.$uid.':downlaod_soft:'.$pkg;
	$res		=	$redis -> setnx($aollow_key, 1, 3);
	if( $res === false ) {
		exit(json_encode(array('code'=>3,'msg'=>'操作频繁')));
	}
	//加入软件任务
	$res = soft_task_add($aid, $uid, $pkg);
	$redis -> delete($aollow_key);
	exit($res);
}elseif( $_POST['type'] == 2 ) {
	//防刷处理
	$aollow_key	=	$prefix.':'.$active_id.':'.$uid.':open_soft:'.$pkg;
	$res		=	$redis -> setnx($aollow_key, 1, 3);
	if( $res === false ) {
		exit(json_encode(array('code'=>3,'msg'=>'操作频繁')));
	}
	//打开软件
	$res = open_soft($aid, $uid, $pkg);
	$redis -> delete($aollow_key);
	exit($res);	
}elseif( $_POST['type'] == 3 ) {
	//再来一局
	//防刷处理
	$aollow_key	=	$prefix.':'.$active_id.':'.$uid.':reset_game';
	$res		=	$redis -> setnx($aollow_key, 1, 20);
	if( $res === false ) {
		exit(json_encode(array('code'=>3,'msg'=>'操作频繁')));
	}
	$g_num = $_POST['g_num'];//当前局
	$res = reset_games($aid, $uid);
	$redis -> delete($aollow_key);
	exit($res);
}elseif( $_POST['type'] == 4 ) {
	//点击开始游戏
	//防刷处理
	$aollow_key	=	$prefix.':'.$active_id.':'.$uid.':start_game';
	$res		=	$redis -> setnx($aollow_key, 1, 10);
	if( $res === false ) {
		exit(json_encode(array('code'=>3,'msg'=>'操作频繁')));
	}
	$res = start_game($aid, $uid, $_SESSION);
	if( $res ) {
		$redis -> delete($aollow_key);
		exit(json_encode(array('code'=>1,'msg'=>'成功')));
	}else {
		exit(json_encode(array('code'=>0,'msg'=>'服务器配置有误！')));
	}
}elseif( $_POST['type'] == 5 ) {
	$inserId = $_POST['inserId'];
	$res = open_red($aid, $uid, $inserId);
	if( $res ) {
		exit(json_encode(array('code'=>1,'data'=> $res,'msg'=>'成功')));
	}else {
		exit(json_encode(array('code'=>0,'msg'=>'获取红包信息失败！')));
	}
}elseif( $_POST['type'] == 6 ) {
	$g_num		=	isset($_POST['g_num']) && $_POST['g_num'] ? (Int)$_POST['g_num'] : null;
	$position	=	isset($_POST['position']) && $_POST['position'] ? (Int)$_POST['position'] : null;
	//先删除是红包的翻牌位置
	$r = lottery_in_level_delete($aid, $uid, $g_num, $position, 1);
	//添加为未中奖翻牌状态
	if( $r ) {
		$res = lottery_level_add($aid, $uid, $g_num, $position, 2);
		if( $res ) {
			exit(json_encode(array('code'=>1,'msg'=>'成功')));
		}else {
			exit(json_encode(array('code'=>0,'msg'=>'')));
		}
	}else {
		exit(json_encode(array('code'=>0,'msg'=>'')));
	}
	
}


