<?php
	include_once (dirname ( realpath ( __FILE__ ) ) . '/init.php');
	$tplObj->out['type'] = 'index';
	$tplObj->out['channel'] = $_GET['channel'];	
	// 专题 位置１　位置２　位置３
	$model = new GoModel();
	$option = array(
		'table' => 'sj_special_list',
		'where' => array(
			'status' => 1
		),
	);
	$data = $model->findAll($option);
	
	$ad_info = array();
	foreach ($data as $val) {
		$ad_info[$val['special_place']] = $val;
	}
	if($channel == '360' || $channel == '360_app' || $channel == '360_game') $ad_info['ad1']['soft_num'] = 8;
	// 首页轮播图
	$chlcode =  isset($channel) ?  strtolower($channel) : "www";
	$chlcode = $channel_map[$chlcode];
	get_pic_scroll($chlcode);
	// 编辑推荐soft.GoGetSoftSubjectAllList
	$chl = $_GET['channel'];
	$params1 = array('ID' => $ad_info['ad2']['special_show'], 'TYPE' => 0, 'GET_INFO' => TRUE, 'LIST_INDEX_START' => 0, 'LIST_INDEX_SIZE' => 10,'VR' => 1);
	$chl_arr_params = array('360','zte','tencent','wandoujia','tcl');
	if(in_array($chl,$chl_arr_params)){
		$params1['LIST_INDEX_SIZE'] = 8;
	}else{
		$params1['LIST_INDEX_SIZE'] = 12;
	}
	
	$tplObj->out['subject1name'] = $ad_info['ad1']['special_name'];
	$tplObj->out['subject1id'] = $ad_info['ad1']['special_show'];
	$tplObj->out['subject1size'] = $ad_info['ad1']['soft_num'];
	
	$tplObj->out['subject2name'] = $ad_info['ad2']['special_name'];
	$tplObj->out['subject2id'] = $ad_info['ad2']['special_show'];
	$tplObj->out['subject2size'] = 10;//$ad_info['ad2']['soft_num'];
	
	$tplObj->out['subject3name'] = $ad_info['ad3']['special_name'];
	$tplObj->out['subject3id'] = $ad_info['ad3']['special_show'];
	$tplObj->out['subject3size'] = $ad_info['ad3']['soft_num'];

	
	// 客户端　手机版、HD版soft.GoGetSoftDetailCategory
	$anzhi = gomarket_action('soft.GoGetSoftDetailPackage', array('PACKAGE_NAME' => 'cn.goapk.market', 'VR' => 1,'EXTRA_OPTION_FIELD' => array('isoffice')));
	$anzhi['qrimg'] = get_qrimg($anzhi['ID'],'cn.goapk.market',$anzhi['SOFT_PROMULGATE_TIME'],$anzhi['ICON']);
	$anzhi['SOFT_SIZE'] = formatFileSize($anzhi['SOFT_SIZE'],1);
	$tplObj->out['anzhi'] = $anzhi;		// 手机版

	$softlist = load_model('softlist');
	$dev_model = load_model('dev');
	$game_ids = array(2826020,2920269);
	$game_infos = $softlist->getSoftInfos($game_ids);
	$game_data = $dev_model->formatListSoftDetail($game_ids, $game_infos);
	//anzhi.pad
	$anzhipad = gomarket_action('soft.GoGetSoftDetailPackage', array('PACKAGE_NAME' => 'anzhi.pad', 'VR' => 1));
	$tplObj->out['anzhipad'] = $anzhipad;		// 手机版
	
	// 精品聚焦soft.GoGetExtentHomeFeature
	if($channel == 'zte' || $channel == 'wandoujia'){
		$LIST_INDEX_SIZE = 20;
	}elseif($channel == 'qqhelper'){
		$LIST_INDEX_SIZE = 24;
	}elseif( $channel == '360_app' || $channel == '360_game'){
        $LIST_INDEX_SIZE = 17;
        $pkg360_1 = gomarket_action('soft.GoGetSoftDetailPackage', array('PACKAGE_NAME' => 'com.qihoo360.mobilesafe', 'VR' => 1));
        $pkg360_1['PACKAGE'] = 'com.qihoo360.mobilesafe';
        $pkg360_2 = gomarket_action('soft.GoGetSoftDetailPackage', array('PACKAGE_NAME' => 'com.qihoo360.mobilesafe.opti.powerctl', 'VR' => 1));
        $pkg360_2['PACKAGE'] = 'com.qihoo360.mobilesafe.opti.powerctl';
        $pkg360_3 = gomarket_action('soft.GoGetSoftDetailPackage', array('PACKAGE_NAME' => 'com.qihoo360.mobilesafe.opti', 'VR' => 1));
        $pkg360_3['PACKAGE'] = 'com.qihoo360.mobilesafe.opti';
        $tplObj -> out['360_special'] = array($pkg360_1,$pkg360_2,$pkg360_3);
	}else{
		$LIST_INDEX_SIZE = 36;
		if(time() <  strtotime('2013-06-16')){
			//$LIST_INDEX_SIZE -= 1;
			$gionee = gomarket_action('soft.GoGetSoftDetailPackage', array('PACKAGE_NAME' => 'com.gionee.client', 'VR' => 1));
			$gionee['qrimg'] = get_qrimg($gionee['ID'],$gionee['PACKAGE'],$gionee['SOFT_PROMULGATE_TIME'],$gionee['ICON']);
			$gionee['size'] = formatFileSize($gionee['SOFT_SIZE'],1);
			$tplObj->out['gionee'] = $gionee;	
		}
	}
	if ($anzhi){
		$LIST_INDEX_SIZE -= 1;
	}

	$HomeFeature = gomarket_action('softv2.GoGetExtentHomeFeatureOld', array('LIST_INDEX_START' => 0, 'LIST_INDEX_SIZE' => $LIST_INDEX_SIZE,'VR' => 13, 'FULL_LIST' => 1,'GET_COUNT' => true,'EXTRA_OPTION_FIELD' => array('intro','isoffice')));

	$home_data = array();
	foreach($HomeFeature['DATA'] as $key => $val){
		
		$val['qrimg'] = get_qrimg($val[0],$val[7],$val[15],$val[1]);
		$val['size'] = formatFileSize($val[9],1);
		$home_data[] = $val;
		if ($key == 3 || $key == 5) {
			$val = array_pop($game_data);
			$val['qrimg'] = get_qrimg($val[0],$val[7],$val[15],$val[1]);
			$val['size'] = formatFileSize($val[9],1);
			$home_data[] = $val;
		}		
	}
	
	$tplObj->out['homeFeature'] = $home_data;
	// 专题轮播图
	if($channel == 'qqhelper'){
		get_pic_subject(4);
	}else{
		get_pic_subject(50);
	}
	$tplObj->out['GOAPK_IMG_HOST'] = GOAPK_IMG_HOST;
	// 友情链接
	$link = links();
	$tplObj->out['links'] = $link;
    //360 首页区分应用和游戏
    if($channel == '360_app'){
        $tplObj -> out['360_pid'] = 1;
    }else if($channel =='360_game'){
        $tplObj -> out['360_pid'] = 2;
    }
	display( "index.html" );
