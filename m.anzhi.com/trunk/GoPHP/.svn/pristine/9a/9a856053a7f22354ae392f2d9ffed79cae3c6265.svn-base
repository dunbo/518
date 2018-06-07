
<?php
function str2param($string)
{
	$result = array();
	$key_map = array(
		//order_key, list_key, flag, param, order_key1, limit

		//专题软件列表
		array('feature_(\d+)', 'SOFTLIST_FEATURE_SOFTID_V4_$1', 0x00150000, 'SUBJECT_ID=$1&SUBJECT_NAME=<{feature_name}>'),

		//最新
		//array('top_new', 'NEW_LIST_F', 0x00120000),
		//飙升
		array('top_new', 'HOME_SOARING_LIST', 0x001f0000),
		//最热
		array('top_hot', 'HOT_LIST_F'),

		//日周月排行
		array('top_(\d+)d', 'HOT_LIST_$1D_2_F', 0x00360000, '', '', 100),
		//分类日周月排行
		array('top_(\d+)d_(\d+)', 'HOT_LIST_$1D_$2_F', 0x00360000, '', '', 100),

		//分类最热
		array('top_1_hot', 'SOFTLIST_CATEGORY_SOFTID_F_1_HOT', 0x00310000, 'CATEGORY_ID=1&CATEGORY_NAME=<{category_name}>'), //应用热门
		array('top_2_hot', 'SOFTLIST_CATEGORY_SOFTID_F_2_HOT', 0x00410000, 'CATEGORY_ID=2&CATEGORY_NAME=<{category_name}>'), //游戏精选

		array('top_(\d+)_hot', 'SOFTLIST_CATEGORY_SOFTID_F_$1_HOT', 0x00330002, 'CATEGORY_ID=$1&PARENT_CATE_ID=<{parent_id}>&CATEGORY_NAME=<{category_name}>'),
		//标签最热
		array('tag_(\d+)_hot', 'TAG_$1_HOT', 0x00330002, 'TAG_ID=$1&TAG_NAME=<{tag_name}>'),
		//分类标签最热
		array('commontag_(\d+)_(\d+)_hot', 'TAG_$1_$2_HOT', 0x00330002, 'CATEGORY_ID=$1&PARENT_CATE_ID=<{parent_id}>&CATEGORY_NAME=<{category_name}>&TAG_ID=$2&TAG_NAME=<{tag_name}>', 'COMMONTAG_$1_$2_HOT'),

		//分类最新
		array('top_(\d+)_new', 'SOFTLIST_CATEGORY_SOFTID_F_$1_NEW', 0x00330001, 'CATEGORY_ID=$1&PARENT_CATE_ID=<{parent_id}>&CATEGORY_NAME=<{category_name}>'),
		//标签最新
		array('tag_(\d+)_new', 'TAG_$1_NEW', 0x00330001, 'TAG_ID=$1&TAG_NAME=<{tag_name}>'),
		//分类标签最新
		array('commontag_(\d+)_(\d+)_new', 'TAG_$1_$2_NEW', 0x00330001, 'CATEGORY_ID=$1&PARENT_CATE_ID=<{parent_id}>&CATEGORY_NAME=<{category_name}>&TAG_ID=$2&TAG_NAME=<{tag_name}>', 'COMMONTAG_$1_$2_NEW'),

		array('page_ebook_hot', 'page_ebook_hot'),
		array('page_ebook_rank', 'TOP_BOOK_WEEKLY_ALL', 0, '', 'top_3_hot', 0),
		array('olgame_down_5', 'HOT_LIST_30D_F', 0, '', '', 100),
		array('olgame_hot_5', 'SOFTLIST_CATEGORY_SOFTID_F_21_HOT'),

		array('otherfixed_gamegift_list', '', 0x00470000), //游戏礼包列表
		array('otherfixed_appgift_list', '', 0x00200000), //应用礼包列表
		array('otherfixed_gamenewserver_list', '', 0x00480000), //游戏新服列表
		array('otherfixed_chinesization_list', '', 0x001b0000), //汉化专区
		array('otherfixed_new_game', 'NEW_PROMINENT_LIST', 0x001a0000), //新锐游戏
		//array('otherfixed_debut_list', 'SOFTLIST_FEATURE_SOFTID_V4_288', 0x00190000, 'SUBJECT_ID=288&SUBJECT_NAME=首发专区'), //首发专区,id一旦调整需要这里进行相应的调整
		array('otherfixed_debut_list', 'FIRST_RELEASE_LIST', 0x00260000, 'SUBJECT_ID=288&SUBJECT_NAME=首发专区&TITLE=首发专区'),
		array('otherfixed_activity_list', '', 0x00160000, 'TITLE=活动列表&TYPE=<{activity_category_show}>&TAB_INDEX=<{tab_index}>&TAB_NAMES=<{tab_names}>&TAB_SWITCH=<{tab_switch}>'), //活动列表
		array('otherfixed_feature_list', '', 0x00140000), //专题列表
		/*
		array('otherfixed_featurelist_1', '', 0x01c00000, 'TITLE=火热专题&CATEGORY_ID=1'),
		array('otherfixed_featurelist_3', '', 0x01c00000, 'TITLE=安智汉化&CATEGORY_ID=3'),
		array('otherfixed_featurelist_4', '', 0x01c00000, 'TITLE=其他专题&CATEGORY_ID=4'),
		array('otherfixed_featurelist_5', '', 0x01c00000, 'TITLE=品牌专区&CATEGORY_ID=5'),
		*/
		array('otherfixed_featurelist_(\d+)', 'P<{pid}>:T$1:FEATURE_SOFTLIST:CACHE', 0x01c00000, 'TITLE=<{feature_category_name}>&CATEGORY_ID=$1'),
		array('otherfixed_homepage_necessary', '', 0x00130000), //首页-必备
		array('otherfixed_homepage_recommend', '', 0x00110000), //首页-推荐
		array('fixed_olgame', 'SOFTLIST_CATEGORY_SOFTID_F_21_HOT', 0x004c0000), //网游
		array('fixed_offlinegame', 'SOFTLIST_CATEGORY_SOFTID_F_CONSOLEGAME_HOT', 0x004b0000), //单机
		array('top_taste_fresh', '', 0x00180000), //首页-尝鲜
		array('fixed_app_category', '', 0x00320000), //应用-分类Tab
		array('fixed_game_category', '', 0x00420000), //游戏-分类Tab
		array('otherfixed_anzhiwellchosen', 'ANZHI_JINGXUAN_LIST', 0x00270000, 'TITLE=安智精选'),
		array('customlist_(\d+)', 'CUSTOMLIST_$1', 0x01800000, 'ID=$1&TITLE=<{title}>&SERVICE_ID=&SRC=4'),
		array('otherfixed_daily_lottery', '', 0x02510000),
		array('otherfixed_task_center', '', 0x02520000),
		array('otherfixed_integral_exchange', '', 0x02530000),
		array('otherfixed_pk_platform_plug', '', 0x02600000, 'PKG_NAME=com.anzhi.battleplatform'),
		array('fixed_discovery_recommend', '', 0x02810000),//发现推荐
		array('fixed_discovery_chinesize', '', 0x02820000),//发现汉化
		array('fixed_discovery_special', '', 0x02830000),//发现专题
		array('fixed_privilege_red', 'PRIVILEGE_GIFT_LIST', 0x00370000),//特权红包
		array('gift_(\d+)', '', 0x00490000, 'GIFT_ID=$1'),//礼包
		array('strategy_(\d+)', '', 0x02010000, 'ID=$1&URL=<{url}>&TITLE=<{title}>&PACKAGE_NAME=<{package}>'),//攻略
		array('fixed_open_test', '', 0x029b1000),//开测
		array('fixed_personal_center', '', 0x02500000, 'NEXT_OPT=<{is_pop_random_packs}>'),//个人中心
		array('fixed_single_bd', '', 0x02b10000), //单机榜单
		array('fixed_operation_bd', '', 0x02b10000), //运营榜单
		array('fixed_online_game_bd', '', 0x02f00000), //网游榜单
		array('fixed_bbs_homepage', '', 0x02c00000), //论坛首页
		array('bbs_detailpage_(\d+)', '', 0x02c10000, 'URL='.urlencode(load_config('zhiyoo_thread_url', 'common')).'$1'), //论坛详情页
		array('fixed_gaoji', '', 0x02d00000), //搞机
		array('fixed_everybody_said', '', 0x02e00000), //大家说
		array('fixed_gift_homepage', '', 0x03100000), //礼包首页
		array('otherfixed_daily_lottery_exchange', '', 0x03210000, 'URL=<{exchange_url}>'), //兑吧抽奖页面

		//v63新增页面
		array('otherfixed_anzhiwellchosen_new', '', 0x03360000), //精选列表
		array('fixed_select_list', '', 0x03360000), //精选列表
		array('fixed_soft_update_homepage', '', 0x00740000), //软件更新首页
		array('fixed_soft_uninstaller_homepage', '', 0x00750000), //软件卸载页面
		array('fixed_my_collection_homepage', '', 0x00900000), //我的收藏页面
		array('fixed_my_message_homepage', '', 0x02920000), //我的消息页面
		array('fixed_download_management_homepage', '', 0x01300000), //下载管理首页
		array('fixed_exchange_mall_homepage', '', 0x0250000d), //兑吧首页(奖品列表)
		array('fixed_exchange_prize', '', 0x0250000c), //兑吧奖品(兑换记录)
		array('fixed_install_package_management', '', 0x00760000), //安装包管理
		array('fixed_phone_cleaning', '', 0x00770000), //手机清理
		array('fixed_invite_installation', '', 0x00780000), //邀请安装
		array('fixed_management_homepage', '', 0x00790000), //设置
		array('fixed_feedback_page', '', 0x02980000), //意见反馈页
		
		array('fixed_search_homepage', '', 0x00600000, 'KEYWORD=<{re_keyword}>'), //搜索
		array('fixed_video_search_more_list', '', 0x00630000, 'KEYWORD=<{re_keyword}>'), //视频搜索更多列表
		array('fixed_infor_search_more_list', '', 0x00640000, 'KEYWORD=<{re_keyword}>'), //资讯搜索更多列表
		array('fixed_my_prize', '', 0x02560000, 'TYPE=<{re_my_prize_type}>'), //我的奖品（存号箱
		array('fixed_exchange_single_prize', '', 0x0250000e, 'URL=<{re_lottery_url}>'), //兑吧单一奖品

		//合作方内容管理
		array('fixed_content_coop_1_0', '', 0x00500000, 'ID=<{coop_channel_tag_id}>'),
		array('fixed_content_coop_1_1', '', 0x00520000, 'ID=<{coop_channel_tag_id}>&CID=<{coop_site_id}>&TYPE=1&NAME=<{name}>'),
		array('fixed_content_coop_1_2', '', 0x00510000, 'ID=<{coop_channel_tag_id}>&CID=<{coop_site_id}>&TYPE=1&NAME=<{name}>'),

		array('fixed_content_coop_2_1', '', 0x00520000, 'ID=<{coop_channel_tag_id}>&CID=<{coop_site_id}>&TYPE=2&NAME=<{name}>'),
		array('fixed_content_coop_2_2', '', 0x00510000, 'ID=<{coop_channel_tag_id}>&CID=<{coop_site_id}>&TYPE=2&NAME=<{name}>'),

		array('fixed_content_coop_3', '', 0x00501000, 'URL=<{coop_detail_url}>&CID=<{coop_site_id}>&TYPE=2&NAME=<{name}>&PACKAGE_NAME=<{package}>&LCID=<{coop_site_id}>&ID=<{coop_detail_url_id}>&TID=<{tid}>&SHARE=<{share}>'),

		array('fixed_feature_wap_(\d+)', '', 0x02910000, 'SUBJECT_ID=$1&SUBJECT_NAME=<{feature_name}>&URL=<URL>'),
		array('fixed_order_list', '', 0x03510000),//预约列表

		array('fixed_desktop_red_packet', '', 0x03540000, 'PACKET_ID=<{red_racket_id}>&ICON_URL=<{icon_url}>&TITLE=<{title}>&RESULT_TITLE=<{result_title}>&SUBHEADING=<{subheading}>&DISMISSTIME=<{over_time}>&LAUNCH=<{lanch_info}>&LOGO=<{logo_show}>&APP_INFO=<{app_info}>&APP_INFO=<{app_info}>&LRTS=<{lrts}>&PUSHID=<{push_id}>&DOWNLOAD_TIP=<{download_tip}>'),//桌面红包弹框页
		array('fixed_red_packet_task_list', '', 0x03550000,'TAB_INDEX=<{TAB_INDEX}>'),//红包任务列表页面
		array('fixed_gold_coin_task_list', '', 0x03560000),//金币任务列表页面
		array('fixed_sign_in_day', '', 0x03570000),//每日签到页面
		array('fixed_my_wallet', '', 0x03580000),//我的钱包页面
		array('fixed_share_activity', '', 0x03590000),//分享指定活动页面
		array('fixed_share_soft', '', 0x035a0000),//分享指定软件页面
		
		array('fixed_content_explicit', '', 0x02041000, 'URL=<{url}>&TITLE=<{title}>&PACKAGE_NAME=<{package}>&APP_ID=<{softid_643}>'),//內览详情wap页
        array('fixed_content_explicit_list', '', 0x02040000, 'TAB_FLAG=<{tag_flag}>&TAB_TYPE=<{tab_type}>&TITLE=<{title}>&APP_INFO=<{app_info}>'),//內览列表

		array('custom_list_channel_(\d+)', 'custom_list_channel_$1', 0x035b0000, 'CHANNEL_ID=$1&TAB_ID=<{tab_id}>&TITLE=<{title}>'),//自定义列表
		array('fixed_my_attention', '', 0x036a0000),//关注推荐
		array('fixed_attention', '', 0x029c0000,'TAB_INDEX=0'),//我的关注
		array('fixed_attention', '', 0x03600000,'BANNER_ID=<{banner_id}>&BANNER_TYPE=<{type}>&EXTRA_TITLE<{title}>'),//banner更多
		
	);
	foreach($key_map as $v) {
		$pattern = "/^{$v[0]}$/";
		if (preg_match($pattern, $string)) {
			$list_key = preg_replace($pattern, $v[1], $string);
			$result['list_key'] = $list_key;

			if (!empty($v[2])) {
				$result['flag'] = $v[2];
			}

			if (!empty($v[3])) {
				$result['param_tpl'] = preg_replace($pattern, $v[3], $string);
			}

			if (!empty($v[4])) {
				$result['order_key'] = $v[4];
			} else {
				$result['order_key'] = $string;
			}

			if (!empty($v[5])) {
				$recommend_max = $v[5];
				$result['max'] = $recommend_max;
			}
			break;
		}
	}
	return $result;
}

function render_param($param_tpl, $param)
{
	$action_param = array();
	parse_str($param_tpl, $action_param);
	$need_replace = array();
	foreach ($action_param as $k => $v) {
		if (preg_match('/^<\{([^\}]+)\}>$/', $v, $m)) {
			if (isset($param[$m[1]])) {
				$action_param[$k] = $param[$m[1]];
			} else {
				$need_replace[$k] = $v;
				unset($action_param[$k]);
			}
		}
	}
	return array($action_param, $need_replace);
}
