<?php
	//错误信息：100,无SIM卡;2,未输入电话号码;3,输入电话号码错误;
	include_once (dirname(realpath(__FILE__)).'/init.php');
	$redis = new GoRedisCacheAdapter();
	$model = new GoModel();
	if($_GET['sid'] && eregi('[0-9a-zA-z]', $_GET['sid']) && strlen($_GET['sid']) == 32){
		session_id($_GET['sid']);
	}
	session_start();
	
	//展示比赛，软件列表
	//$all_package = $redis -> delete('all_package');exit;
	//$all_package = $redis -> delete(1);exit;
	$all_package = $redis -> get('all_package');
	
	if(!$all_package){
		$the_package = array(array('softname'=>'热血足球经理','package' =>'com.zhangqu.game.football.anzhi'),array('softname'=>'绝杀2014','package'=>'com.capstones.goal2014.anzhi'),array('softname'=>'天天世界杯','package'=>'com.sqage.football.anzhi'),array('softname'=>'世界足球经理','package'=>'air.com.canglan.Fmx.anzhi'),array('softname'=>'辉煌足球','package'=>'com.soco.football.anzhi'),array('softname'=>'进击的足球','package'=>'com.nvngame.we.anzhi'),array('softname'=>'FIFA2014巴西世界杯','package'=>'com.ea.game.chs.android.fwcs2014.anzhi'),array('softname'=>'街头足球','package'=>'com.zqgame.ss.anzhi'),array('softname'=>'刀塔传奇','package'=>'sh.lilith.dgame.anzhi'),array('softname'=>'时空猎人','package'=>'com.yinhan.hunter.anzhi'),array('softname'=>'捕鱼达人2','package'=>'org.cocos2dx.FishingJoy2'),array('softname'=>'神魔','package'=>'com.yinhan.shenmo.anzhi'),array('softname'=>'放开那三国','package'=>'com.babletime.fknsango_anzhi'),array('softname'=>'熊出没之熊大快跑','package'=>'com.joym.xiongdakuaipao'),array('softname'=>'开心消消乐','package'=>'com.happyelements.AndroidAnimal'),array('softname'=>'博雅斗地主','package'=>'com.boyaa.lordland.sina'),array('softname'=>'植物大战僵尸2高清版','package'=>'com.popcap.pvz2cthdaz'),array('softname'=>'赢话费斗地主','package'=>'com.mykj.game.ddz'),array('softname'=>'红警4 大国崛起','package'=>'com.elextech.alert.anzhi'),array('softname'=>'PopStar!消灭星星官方正版','package'=>'com.brianbaek.popstar'),array('softname'=>'雷霆战机2（完美版）','package'=>'a5game.leidian2_dx_5a'),array('softname'=>'3D暴力摩托','package'=>'com.sxiaoao.moto3dOnline'),array('softname'=>'泡泡龙亚特兰蒂斯','package'=>'com.soulgame.bubble'),array('softname'=>'三国志15','package'=>'net.crimoon.sgz15.anzhi'),array('softname'=>'捕鱼达人土豪金','package'=>'org.cocos2dx.GoldenFishGame'),array('softname'=>'神雕侠侣','package'=>'com.wanmei.mini.condor.anzhi'),array('softname'=>'神偷奶爸小黄人快跑','package'=>'com.gameloft.android.ANMP.GloftDMCN'),array('softname'=>'愤怒的小鸟（中文版）','package'=>'com.rovio.angrybirds.az'),array('softname'=>'保卫萝卜2','package'=>'com.carrot.iceworld'),array('softname'=>'暴走武侠','package'=>'com.KodGames.WuLin.android.anzhi'),array('softname'=>'3D终极狂飙3','package'=>'com.sxiaoao.car3d3'),array('softname'=>'博雅德州扑克','package'=>'com.boyaa.sina'),array('softname'=>'马上三国','package'=>'com.ay.mssg.anzhi'),array('softname'=>'风云天下','package'=>'com.mango.sanguo15'),array('softname'=>'亡灵杀手','package'=>'com.nhncorp.skundeadck'),array('softname'=>'神庙逃亡2','package'=>'com.imangi.templerun2'),array('softname'=>'天天消宝石','package'=>'mobi.popsoft.popjewels'),array('softname'=>'我叫MT','package'=>'com.locojoy.punchbox.immt_a_chs'),array('softname'=>'火影大人','package'=>'com.xckoo.renlong.anzhi'),array('softname'=>'超级英雄','package'=>'www.kaiqigu.com.anzhi'),array('softname'=>'点心桌面','package'=>'com.dianxinos.dxhome'),array('softname'=>'暴风影音','package'=>'com.storm.smart'),array('softname'=>'爱奇艺','package'=>'com.qiyi.video'),array('softname'=>'PPTV','package'=>'com.pplive.androidphone'),array('softname'=>'PPS','package'=>'tv.pps.mobile'),array('softname'=>'手机电视','package'=>'dopool.player'),array('softname'=>'百度视频','package'=>'com.baidu.video'),array('softname'=>'掌阅','package'=>'com.chaozh.iReaderFree'),array('softname'=>'I阅读','package'=>'com.iyd.reader.ReadingJoy.anzhi'),array('softname'=>'考拉FM','package'=>'com.itings.myradio'),array('softname'=>'好123','package'=>'com.baidu.hao123'),array('softname'=>'搜狗输入法','package'=>'com.sohu.inputmethod.sogou'),array('softname'=>'墨迹天气','package'=>'com.moji.mjweather'),array('softname'=>'搜狗搜索','package'=>'com.sogou.activity.src'),array('softname'=>'酷我音乐','package'=>'cn.kuwo.player'),array('softname'=>'酷狗音乐','package'=>'com.kugou.android'),array('softname'=>'美团团购','package'=>'com.sankuai.meituan'),array('softname'=>'百度知道','package'=>'com.baidu.iknow'),array('softname'=>'ES文件浏览器','package'=>'com.estrongs.android.pop'),array('softname'=>'WiFi万能钥匙','package'=>'com.snda.wifilocating'),array('softname'=>'美图秀秀','package'=>'com.mt.mtxx.mtxx'),array('softname'=>'小米桌面','package'=>'com.miui.mihome2'),array('softname'=>'携程旅行','package'=>'ctrip.android.view'),array('softname'=>'去哪儿旅行','package'=>'com.Qunar'),array('softname'=>'土豆','package'=>'com.tudou.android'),array('softname'=>'腾讯视频','package'=>'com.tencent.qqlive'),array('softname'=>'100tv播放器','package'=>'com.fone.player'),array('softname'=>'QQ音乐','package'=>'com.tencent.qqmusic'),array('softname'=>'易到用车','package'=>'com.yongche.android'),array('softname'=>'讯飞输入法','package'=>'com.iflytek.inputmethod'),array('softname'=>'掌上宝','package'=>'com.dft.hb.bakapp'),array('softname'=>'新浪微博','package'=>'com.sina.weibo'),array('softname'=>'天气通','package'=>'sina.mobile.tianqitong'),array('softname'=>'百度贴吧','package'=>'com.baidu.tieba'),array('softname'=>'高德地图','package'=>'com.autonavi.minimap'),array('softname'=>'百度地图','package'=>'com.baidu.BaiduMap'),array('softname'=>'京东','package'=>'com.jingdong.app.mall'),array('softname'=>'淘宝','package'=>'com.taobao.taobao'),array('softname'=>'亚马逊','package'=>'cn.amazon.mShop.android'),array('softname'=>'今日头条','package'=>'com.ss.android.article.news'),array('softname'=>'新浪新闻','package'=>'com.sina.news'),array('softname'=>'世纪佳缘','package'=>'com.jiayuan'),array('softname'=>'有缘婚恋','package'=>'com.youyuan.yyhl'),array('softname'=>'赶集生活','package'=>'com.ganji.android'),array('softname'=>'58同城','package'=>'com.wuba'),array('softname'=>'万年历','package'=>'com.youloft.calendar'));
		foreach($the_package as $key => $val){
			$soft_option = array(
				'where' => array(
					'package' => $val,
					'hide' => 1,
					'status' => 1
				),
				'field' => 'softid,softname,version_code',
				'order' => 'softid DESC',
				'limit' => '0,1',
				'table' => 'sj_soft'
			);
			$soft_result = $model -> findOne($soft_option);
			$val['softid'] = $soft_result['softid'];
			$val['version_code'] = $soft_result['version_code'];
			$soft_file_option = array(
				'where' => array(
					'softid' => $soft_result['softid'],
				),
				'field' => 'filesize,iconurl_72',
				'table' => 'sj_soft_file'
			);
			$soft_file_result = $model -> findOne($soft_file_option);
		
			$val['filesize'] = $soft_file_result['filesize'];
			$val['icon_72'] = $soft_file_result['iconurl_72'];
			
			if($val['filesize']){
				$val['size'] = formatFileSize('',$soft_file_result['filesize']);
			}else{
				$val['size'] = '0M';
			}
			$the_package[$key] = $val;
		}
		$redis -> set('all_package',json_encode($the_package),600);
		$all_package = $redis -> get('all_package');
	}

	$my_all_package = json_decode($all_package,true);
	$my_package = array_rand($my_all_package,8);

	foreach($my_all_package as $key => $val){
		if(in_array($key,$my_package)){
			$my_soft[] = $val;
		}
	}
	
	$match_option = array(
		'where' => array(
			'result' => 0,
			'status' => 1
		),
		'order' => 'begintime',
		'table' => 'cup_match'
	);
	$match_result = $model -> findAll($match_option,'world_cup');

	$guess_option = array(
		'where' => array(
			'award_status' => 1,
			'is_gua' => 1
		),
		'field' => 'mobile,award_level,award_time',
		'order' => 'award_time DESC',
		'limit' => 10,
		'table' => 'cup_guess'
	);
	
	$guess_result = $model -> findAll($guess_option,'world_cup');
	
	$award_option = array(
		'where' => array(
			'config_type' => 'WORLD_CUP_LEVEL',
			'status' => 1
		),
		'table' => 'pu_config'
	);
	$award_result = $model -> findOne($award_option);
	$award_level = json_decode($award_result['configcontent'],true);
	
	foreach($guess_result as $key => $val){
		$val['award_money'] = $award_level[$val['award_level']];
		$val['the_time'] = date('Y-m-d',$val['award_time']);
		$val['telphone'] = substr_replace($val['mobile'],'****',3,4);
		$guess_result[$key] = $val;
	}

	foreach($match_result as $key => $val){
		$ratio_option = array(
			'where' => array(
				'match_id' => $val['id']
			),
			'table' => 'cup_guess'
		);
		$ratio_result = $model -> findAll($ratio_option,'world_cup');

		$home_result = array();
		$client_result = array();
		$draw_result = array();
		foreach($ratio_result as $k => $v){
			if($v['guess_content'] == 1){
				$home_result[] = $v;
			}else if($v['guess_content'] == 2){
				$client_result[] = $v;
			}else if($v['guess_content'] == 3){
				$draw_result[] = $v;
			}
		}
		if($home_result){
			$home_all = count($home_result);
		}else{
			$home_all = 0;
		}
		$client_all = count($client_result);
		if($draw_result){
			$draw_all = count($draw_result);
		}else{
			$draw_all = 0;
		}

		$all_count = count($ratio_result);
		$val['ratio_home'] = floor(($home_all/$all_count)*100);
		$val['ratio_client'] = floor(($client_all/$all_count)*100);
		$val['ratio_draw'] = floor(($draw_all/$all_count)*100);
		
		$week=array('日','一','二','三','四','五','六');
		$val['the_time'] = date('Y年m月d日',$val['begintime']);
		$val['the_week'] = $week[date('w',$val['begintime'])];
		$match_result[$key] = $val;

	}
	
	//判断页面
	//if($_SESSION['USER_IMSI']){
		$imsi = $_SESSION['USER_IMSI'];
	//}else{
	//	$imsi = 10;
	//}
	
	
	if(!$imsi || $imsi == '000000000000000'){
		$tplObj -> out['status'] = 100;
		$tplObj -> out['my_soft'] = $my_soft;
	}else{
		$imsi_option = array(
			'where' => array(
				'imsi' => $imsi,
			),
			'table' => 'cup_user'
		);
		$imsi_result = $model -> findOne($imsi_option,'world_cup');

		if(!$imsi_result){
			$tplObj -> out['my_soft'] = $my_soft;
			$tplObj -> out['status'] = 200;
		}else{
			$my_packages = $redis -> get($imsi);
			$packages_arr = json_decode($my_packages,true);
			$package_arr = explode(',',$packages_arr['package']);
			$all_package = $redis -> get('all_package');
			$my_all_package = json_decode($all_package,true);
			//var_dump($package_arr);
			//var_dump($package_arr);
			foreach($my_all_package as $key => $val){
				if(!in_array($val['package'],$package_arr)){
					$self_all_package[] = $val;
				}
			}
	
			$myself_package = array_rand($self_all_package,8);
			
			foreach($self_all_package as $key => $val){
				if(in_array($key,$myself_package)){
					$my_softs[] = $val;
				}
			}
			//var_dump($my_softs);
		
			foreach($match_result as $key => $val){
				$my_match_option = array(
					'where' => array(
						'imsi' => $imsi,
						'match_id' => $val['id'],
					),
					'field' => 'guess_content',
					'table' => 'cup_guess'
				);
				$my_match_result = $model -> findOne($my_match_option,'world_cup');
				//var_dump($my_match_result);
				if($my_match_result){
					$val['guess_content'] = $my_match_result['guess_content'];
				}else{
					$val['guess_content'] = 0;
				}
				$match_result[$key] = $val;
			}
			$tplObj -> out['my_soft'] = $my_softs;
			$tplObj -> out['user_result'] = $imsi_result;
			$tplObj -> out['status'] = 300;
		}
	}
	
	if($_GET['sid']){
		$sid = $_GET['sid'];
	}else{
		$sid = 1;
	}
	
	$tplObj -> out['sid'] = $sid;
	$tplObj -> out['img_url'] = "http://apk.goapk.com/";
	
	$tplObj -> out['match_result'] = $match_result;
	$tplObj -> out['guess_result'] = $guess_result;
	$tplObj -> display('worldcup.html');
	
	
	
	
