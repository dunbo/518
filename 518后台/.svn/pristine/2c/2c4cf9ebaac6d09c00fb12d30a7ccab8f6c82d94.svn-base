<?php
class ContentTypeModel extends Model {
	static $search_result_page = 'search_result_page';
	static $search_result_content = 'search_result_content';
	static $exclusive = 'exclusive';
	static $external_special = 'external_special'; //外投专用频道
	static $attention_page = 'attention_page';  // 关注页
	static $no_attention_page = 'no_attention_page'; //无关注页
    /* 页面类型对应编码的开始字符串特征，目前只有1普通，2标签，3常用标签，4自定义列表四种类型
    ** 不同页面类型之间的编码开始的字符串特征一定不能相同，之后添加的页面编码需遵循这些特征
    */
    public static function getPageTypeFeature() {
        return array(
            // 普通类型页面
            1 => array(
                'top_',
                'olgame_',
                'fixed_',
                'otherfixed_',// 原来的其他类型合并到普通类型
				'bbs_detailpage_',//V6.2新增论坛详情页
				'game_widget', //V6.4.1新增游戏桌面widget
				self::$search_result_page, //v6.4.4新增搜索结果页
				self::$search_result_content, //v6.4.6 搜索内容推荐
				'fixed_app_hot',
				'fixed_game_hot',
				'recommend_nongift',
				'recommend_noncoupon',
				'recommend_strategy',
				self::$exclusive,
				self::$external_special,
				self::$attention_page,
				self::$no_attention_page
            ),
            // 标签页面
            2 => array(
                'tag_'
            ),
            // 常用标签页面
            3 => array(
                'commontag_'
            ),
            // 自定义列表
            4 => array(
                'customlist_',
            ),
            //榜单
            5 => array(
                'bdlist_',
            ),
			//礼包
			6 => array(
				'gift_'
			),
			//攻略
			7 => array(
				'strategy_'
			),
			8 => array(
				'coop_'
			),
			9 => array(
				'customlist1_',
			),
			// 自定义频道
            10 => array(
                'custom_list_channel_',
            ),
        );
    }

    // 获得普通、标签、常用标签、自定义列表类型页面的where条件
    public static function getWhereConditionOfPageType($general_page_type) {
        $page_type_feature = ContentTypeModel::getPageTypeFeature();
        if ($general_page_type > count($page_type_feature) || $general_page_type <= 0)
            return false;
        $result = array();
        $feature_arr = $page_type_feature[$general_page_type];
        $count = count($feature_arr);
        foreach ($feature_arr as $feature) {
            $result[] = array('like', "{$feature}%");
        }
        if ($count > 1)
            $result[] = 'or';
        return $result;
    }
    
    // 【频道运营】的可运营的【普通】类型的所有页面
    public static function getCategoryTypesOfCategory($pid=1) {
        $map = array(
			'' => '全部',
			'top_new' => '飙升',
			'top_hot' => '最热',
			'top_1d' => '日排行',
			'top_1d_1' => '应用日排行',
			'top_1d_2' => '游戏日排行',
			'top_7d' => '周排行',
			'top_30d' => '月排行',
			'olgame_down_5' => '下载最多',
			'olgame_hot_5' => '网游精选',
            'fixed_olgame' => '网游',//新增数据，以'fixed_'开头
            'fixed_offlinegame' => '单机',//新增数据，以'fixed_'开头
            'otherfixed_debut_list' => '首发专区',// special！！！只有频道运营才有这个可运营页面！！！
            'fixed_user_also_download' => '用户还下载',// special！！！只有频道运营才有这个可运营页面！！！

            'fixed_user_also_download_package' => '用户还下载-软件',// special！！！只有频道运营才有这个可运营页面！！！
            'fixed_user_also_download_class' => '用户还下载-分类',// special！！！只有频道运营才有这个可运营页面！！！

            'fixed_user_also_download_recommend' => '用户还下载推荐',// special！！！只有频道运营才有这个可运营页面！！！
			'fixed_user_also_download_recommend_class' => '用户还下载推荐-分类',
			'fixed_user_also_install_recommend' => '安装推荐',
			'fixed_user_also_install_recommend_package' => '安装推荐-软件',
			'fixed_user_also_install_recommend_class' => '安装推荐-分类',
			'fixed_discovery_recommend'=>'发现-推荐',
			//V6.0运营频道添加 发现-推荐
			'fixed_game_gift'=>'游戏-礼包',
			//V6.2运营频道添加 游戏-礼包
			'fixed_app_hot'=>'应用-最热2',
			'fixed_game_hot'=>'游戏-最热2'
		);
		
		$category = D('Sj.Category');
		$category_list = $category->getCategoryArray();
		foreach ($category_list as $v) {
			$map['top_' . $v['category_id'] . '_new'] = $v['name'] . '-最新';
			$map['top_' . $v['category_id'] . '_hot'] = $v['name'] . '-最热';
		}
		if($pid == 13){
			$map['recommend_nongift'] =  '无礼包页推荐';
			$map['recommend_noncoupon'] =  '无礼券页推荐';
			$map['recommend_strategy'] =  '无攻略页推荐';
		}
		return $map;
    }
    
    // 【灵活运营样式】的可运营的【普通】类型的所有页面
    public static function getCategoryTypesOfFlexible($pid=0,$general_page_type=0) {
        $map = array(
			'' => '全部',
			'top_new' => '飙升',
			//'top_hot' => '最热',//灵活运营已经不运营这个页面了
			//'top_1d' => '日排行',//灵活运营已经不运营这个页面了
			'top_1d_1' => '应用日排行',
			//'top_1d_2' => '游戏日排行',
			//'top_7d' => '周排行',//灵活运营已经不运营这个页面了
			//'top_30d' => '月排行',//灵活运营已经不运营这个页面了
			//'olgame_down_5' => '下载最多',//灵活运营已经不运营这个页面了
			//'olgame_hot_5' => '网游精选',//灵活运营已经不运营这个页面了
            'fixed_olgame' => '网游',//新增数据，以'fixed_'开头
            'fixed_offlinegame' => '单机',//新增数据，以'fixed_'开头
            'otherfixed_homepage_recommend' => '首页推荐(6.4.3及以下版本)',
            //6.4.5
            'fixed_homepage_recommend' => '首页推荐(6.4.4及以上版本)',//新增数据，以'fixed_'开头
            
			'fixed_discovery_recommend'=>'发现-推荐',
			'fixed_discovery_chinesize'=>'发现-汉化',
			//V6.0灵活运营添加 发现-推荐和发现-汉化
			//特权红包
			//'fixed_privilege_red'=>'特权红包',
			//V6.1增加开测页面和新服页面
			'fixed_open_test'=>'开测',
			'otherfixed_gamenewserver_list' => '游戏新服列表',
			//V6.2增加搞机列表和个人中心
			'fixed_personal_center'=>'个人中心', 
			'fixed_gaoji'=>'搞机',
			
			//V6.5增加安智钱包
			'fixed_my_wallet'=>'安智钱包',
			'fixed_app_hot'=>'应用-最热2',
			'fixed_game_hot'=>'游戏-最热2',
			//6.4.5
			//'fixed_resource_channel'=>'资源库',
			//6.4.9
			self::$attention_page => '关注页',
			self::$no_attention_page => '无关注页'
		);
		
		$category = D('Sj.Category');
		$category_list = $category->getCategoryArray();
		foreach ($category_list as $v) {
			$map['top_' . $v['category_id'] . '_new'] = $v['name'] . '-最新';
			$map['top_' . $v['category_id'] . '_hot'] = $v['name'] . '-最热';
		}
        // 去掉应用最新，游戏最新
        unset($map['top_1_new']);
        unset($map['top_2_new']);
		$map['game_widget'] = '游戏桌面widget';
		$map[self::$search_result_page] = '搜索结果-软件'; //v6.4.4新增搜索结果页，v6.4.6更名为搜索结果-软件
		$map[self::$search_result_content] = '搜索结果-内容'; //v6.4.5
		$map[self::$external_special] = '外投专用频道'; //v6.4.8
		//SDK5.0增加独家频道
		if($pid == 13&&$general_page_type == 1){
			$map['exclusive'] = '独家';
		}
		return $map;
    }
    
    // 【文字快捷入口】的可运营的【普通】类型的所有页面
    public static function getCategoryTypesOfTextQuickEntry() {
        $map = array(
			'' => '全部',
			'top_new' => '飙升',
			//'top_hot' => '最热',//灵活运营已经不运营这个页面了
			//'top_1d' => '日排行',//灵活运营已经不运营这个页面了
			'top_1d_1' => '应用日排行',
			//'top_1d_2' => '游戏日排行',
			//'top_7d' => '周排行',//灵活运营已经不运营这个页面了
			//'top_30d' => '月排行',//灵活运营已经不运营这个页面了
			//'olgame_down_5' => '下载最多',//灵活运营已经不运营这个页面了
			//'olgame_hot_5' => '网游精选',//灵活运营已经不运营这个页面了
            'fixed_olgame' => '网游',//新增数据，以'fixed_'开头
            'fixed_offlinegame' => '单机',//新增数据，以'fixed_'开头
           	'otherfixed_homepage_recommend' => '首页推荐(6.4.3及以下版本)',
		   //V6.0去掉首页 增加发现的推荐、汉化、专题
			'fixed_discovery_recommend'=>'发现-推荐',
			'fixed_discovery_chinesize'=>'发现-汉化',
			'fixed_discovery_special'=>'发现-专题',
			//特权红包
			//'fixed_privilege_red'=>'特权红包',
			//V6.2新增个人中心
			'fixed_personal_center'=>'个人中心',
			//'fixed_gaoji'=>'搞机列表',
			//V6.5新增金币
			'fixed_gold_coin_task_list'=>'金币列表',
			'fixed_my_wallet'=>'钱包-安智服务',
			'fixed_wallet_third'=>'钱包-三方服务',
        	'fixed_red_packet_task_list'=>'红包任务列表',
        	'fixed_app_hot'=>'应用-最热2',
			'fixed_game_hot'=>'游戏-最热2',
		);
		
		$category = D('Sj.Category');
		$category_list = $category->getCategoryArray();
		foreach ($category_list as $v) {
			$map['top_' . $v['category_id'] . '_new'] = $v['name'] . '-最新';
			$map['top_' . $v['category_id'] . '_hot'] = $v['name'] . '-最热';
		}
        // 去掉应用最新，游戏最新
        unset($map['top_1_new']);
        unset($map['top_2_new']);
		
        /////////////////////////////////////////////////////////////////////////////////////
        
        $map2 = array(
            'top_taste_fresh' => '首页尝鲜',
            'fixed_app_category' => '应用分类Tab',//新增数据，以'fixed_'开头
            'fixed_game_category' => '游戏分类Tab',//新增数据，以'fixed_'开头
			'otherfixed_homepage_necessary' => '首页必备',//改为快捷入口可运营 //V5.5增加  added by shitingting	  
        );
        
        /////////////////////////////////////////////////////////////////////////////////////
        
        $map = array_merge($map, $map2);
        
        return $map;
        
    }
    
    // 【可跳转页面】的【普通】类型的所有页面
    public static function getCategoryTypes() {
        $map = array(
			'' => '全部',
			'top_new' => '飙升',
			//'top_hot' => '最热',//灵活运营已经不运营这个页面了
			//'top_1d' => '日排行',//灵活运营已经不运营这个页面了
			'top_1d_1' => '应用日排行',
			//'top_1d_2' => '游戏日排行',
			//'top_7d' => '周排行',//灵活运营已经不运营这个页面了
			//'top_30d' => '月排行',//灵活运营已经不运营这个页面了
			//'olgame_down_5' => '下载最多',//灵活运营已经不运营这个页面了
			//'olgame_hot_5' => '网游精选',//灵活运营已经不运营这个页面了
            'fixed_olgame' => '网游',//新增数据，以'fixed_'开头
            'fixed_offlinegame' => '单机',//新增数据，以'fixed_'开头
            'otherfixed_homepage_recommend' => '首页推荐(6.4.3及以下版本)',
			//V6.0统一增加发现推荐、发现汉化、发现专题、应用榜单-榜单名称
			'fixed_discovery_recommend'=>'发现-推荐',
			'fixed_discovery_chinesize'=>'发现-汉化',
			'fixed_discovery_special'=>'发现-专题',
			//'fixed_bdlist_bdname'=>'应用榜单-榜单名称',
			//特权红包
			'fixed_privilege_red'=>'特权红包',
		);
		
		$category = D('Sj.Category');
		$category_list = $category->getCategoryArray();
		foreach ($category_list as $v) {
			$map['top_' . $v['category_id'] . '_new'] = $v['name'] . '-最新';
			$map['top_' . $v['category_id'] . '_hot'] = $v['name'] . '-最热';
		}
        // 去掉应用最新，游戏最新
        unset($map['top_1_new']);
        unset($map['top_2_new']);
        
        ///////////////////////////////////////////////////////////////////////////////////////////////
        
        $map2 = array(
            'top_taste_fresh' => '首页尝鲜',
            'fixed_app_category' => '应用分类Tab',//新增数据，以'fixed_'开头
            'fixed_game_category' => '游戏分类Tab',//新增数据，以'fixed_'开头
			'otherfixed_homepage_necessary' => '首页必备',//改为快捷入口可运营 //V5.5增加  added by shitingting	  
        );
        
        $map = array_merge($map, $map2);
        
        ///////////////////////////////////////////////////////////////////////////////////////////////
        
        $map2 = array(
            //'otherfixed_homepage_necessary' => '首页必备',//改为快捷入口可运营
            //'otherfixed_featurelist_1' => '专题列表', // 改成动态读取
            //'otherfixed_featurelist_3' => '汉化专区', // 改成动态读取
            //'otherfixed_featurelist_4' => '其他专题列表', // 改成动态读取
            'otherfixed_featurelist_5' => '品牌专区',
            'otherfixed_activity_list' => '活动列表',
            'otherfixed_debut_list' => '首发专区',
            'otherfixed_new_game' => '新锐游戏',
            'otherfixed_gamegift_list' => '游戏礼包列表',
            'otherfixed_gamenewserver_list' => '游戏新服列表',
            'otherfixed_appgift_list' => '应用礼包列表',
            'otherfixed_anzhiwellchosen' => '安智精选',
			'otherfixed_daily_lottery'=>'每日抽奖（安智）',//新增数据，每日抽奖 added by shitingting
			'otherfixed_daily_lottery_exchange'=>'每日抽奖（兑吧）',//V6.2增加
			'otherfixed_task_center'=>'任务中心',//新增数据，任务中心
			'otherfixed_integral_exchange'=>'积分兑换',//新增数据，积分兑换
			'otherfixed_pk_platform_plug'=>'对战平台插件',//新增数据，对战游戏平台插件
			//V6.1新增开测和个人中心
			'fixed_open_test'=>'开测',
			'fixed_personal_center'=>'个人中心',
			//V6.2新增论坛首页、论坛详情页、搞机
			'fixed_bbs_homepage'=>'论坛首页',
			'fixed_gaoji'=>'搞机',
			'fixed_single_bd'=>'单机榜单',
			'fixed_online_game_bd'=>'网游榜单',
			'fixed_operation_bd'=>'运营榜单',
			'fixed_everybody_said'=>'大家说',
			'fixed_bbs_detailpage'=>'论坛详情页',
			'fixed_gift_homepage'=>'礼包首页',
			//v6.3新增
			'otherfixed_anzhiwellchosen_new' => '精选页面（6.3）',
			'fixed_exchange_mall_homepage'=>'兑吧首页（奖品列表）',
			'fixed_exchange_prize'=>'兑吧奖品（兑换记录）',
			//'fixed_select_list'=>'精选列表',
			'fixed_soft_update_homepage'=>'软件更新首页',
			'fixed_soft_uninstaller_homepage'=>'软件卸载',
			'fixed_my_collection_homepage'=>'我的收藏',
			'fixed_my_message_homepage'=>'我的消息',
			'fixed_download_management_homepage'=>'下载管理首页',
			'fixed_install_package_management'=>'安装包管理',
			'fixed_phone_cleaning'=>'手机清理',
			'fixed_invite_installation'=>'邀请安装',
			'fixed_management_homepage'=>'设置',
			'fixed_feedback_page'=>'意见反馈页面',
			'fixed_content_coop'=>'内容合作',
			//带参数的示例
			/*'fixed_search_homepage'=>array(
				'搜索', 
				array(
					're_keyword'=>array(//keyword 作为页面input的name的值，所以不能重复。前面都加re_避免和页面其他name重复
						'搜索关键词1',//页面显示名称
						1,				// 1 文本， 2下拉
						'',
						2,				//1 必填 2 非必填
					),
					're_keywords'=>array(//第二个参数的配置
						'搜索关键词2',
						1,
						'',
						1,
					),
					're_my_prize_type' => array(//第三个参数的配置
						'我的奖品类型', 
						2,              //下拉
						array(			//选择内容
							1 => '礼包',
							2 => '开测',
							3 => '奖品',
						),
						1,				
					)
				),
			),*/
			'fixed_search_homepage'=>array(
				'搜索', 
				array(
					're_keyword'=>array(
						'搜索关键词',
						1,
						'',
						2,//非必填			
					),
				),
			),
			'fixed_video_search_more_list'=>array(
				'视频搜索更多列表',
				array(
					're_keyword'=>array(
						'搜索关键词',
						1,
						'',
						1,
					)
				),
			),
			'fixed_infor_search_more_list'=>array(
				'资讯搜索更多列表',
				array(
					're_keywords'=>array(
						'搜索关键词',
						1,
						'',
						1,
					)
				),
			),
			'fixed_my_prize'=> array(
				'我的奖品（存号箱）',
				array(
					're_my_prize_type' => array(
						'我的奖品类型', //显示名称
						2, // 1 文本， 2下拉
						array(
							1 => '礼包',
							2 => '开测',
							3 => '奖品',
						),
						1,
					)
				)
			),
			'fixed_exchange_single_prize'=>array(
				'兑吧单一奖品', 
				array(
					're_lottery_url'=>array(
						'抽奖地址',
						1,	
						'',
						1,
					),
				),
			),
			//V6.4增加红包助手
			//'fixed_red_assistant'=>'红包助手',
			'fixed_order_list'=>'预约列表',
			//V6.5增加跳转页面  每日签到/金币列表/红包任务列表
			'fixed_sign_in_day'=>'每日签到',
			'fixed_gold_coin_task_list'=>'金币列表',
			'fixed_red_packet_task_list'=>'红包任务列表',
			'fixed_my_wallet'=>'我的钱包',
			'fixed_share_activity'=>'分享指定活动页面',
			'fixed_share_soft'=>'分享指定软件页面',
			'fixed_attention'=>'关注管理',
			'fixed_my_attention' => '我的关注'
        );
        // 专题列表相关页面需要从表中动态读取（除了活动专题列表是写死的）
        $model = M();
        $where =array(
            'id' => array('neq', 2),//活动列表是特殊的，在此排除掉
            'status' => 1
        );
        $feature_type_list = $model->table('sj_feature_type')->where($where)->select();
        foreach ($feature_type_list as $feature_type) {
            $feature_type_id = $feature_type['id'];
            $map2['otherfixed_featurelist_'.$feature_type_id] = $feature_type['feature_type_name'] . "专题分类列表";
        }
        
        $map = array_merge($map, $map2);
        
        return $map;
    }

    //获取榜单
    public static function getbdList() {
        $map =array(''=>'全部');
        $model=M();
		$rs = $model->table('sj_list')->where('status=1')->select();
        foreach($rs as $v)
        {
			//区分榜单来源 6.0的应用榜单添加"应用_榜单名称"
			if($v['from_chl']==1)
			{
				$map['bdlist_'.$v['id']] ="应用_".$v['name'];
			}
			else if($v['from_chl']==2)
			{
				$map['bdlist_'.$v['id']] ="网游_".$v['name'];
			}
			else if($v['from_chl']==3)
			{
				$map['bdlist_'.$v['id']] ="单机_".$v['name'];
			}
			else
			{
				$map['bdlist_'.$v['id']] =$v['name'];
			}
        }
        return $map;
    }

    public static function getbdListname($type) {
        $list = ContentTypeModel::getbdList();
        return $list[$type];
    }
	//内容合作转换
	public static function convertCoopPageType2CoopPageName($page_type)
	{
		$coop_result = ContentTypeModel::getCoopChannel();
		foreach($coop_result as $key =>$val)
		{
			if($val['coop_key_val']==$page_type)
			{
				return $val['new_channel_name'];
			}
		}
	}
	
	//获取专题列表
    public static function getfeatureList() {
        $map =array(''=>'全部');
        $model=M();
		$rs = $model->table('sj_feature')->where('status=1')->select();
        foreach($rs as $v)
        {
			$map['feature_'.$v['feature_id']] =$v['name'];
        }
        return $map;
    }

    public static function getfeatureListname($type) {
        $list = ContentTypeModel::getfeatureList();
        return $list[$type];
    }
	
    public static function getotherlist() {
        $map = array(
            'search' => '搜索',
            'search_from_hot' => '点击大家都在搜',
            'search_from_suggest' => '点击搜索热词',
            'search_from_history' => '点击搜索历史',
            'search_suggest' => '搜索提示列表',
            'INSTANT_SEARCH_SUGGEST' => '搜索提示列表',
            'otherfixed_homepage_backup' => '首页备选库',

			'WELL_CHOSEN_APP_LIST' => '精选软件任务软件列表', 
			'FOLLOW_APP_LIST' => '活动软件列表',
			'GET_APP_INFO' => '获取软件信息',
			'GET_PLUGIN' => '插件接口',
			'RECOMMEND_INSTALL' => '一键装机',
			'DESKTOP_RECOMMEND_GAME_LIST' => '游戏文件夹',
			'NEW_PROMINENT_LIST' => '新锐游戏',
        );
        return $map;
    }

    // 自定义列表，暂时不可运营，只可跳转
    public static function getCustomList($name_like = '') {
        $model = D("Sj.CustomList");
        $list = $model->getNameRecordLikeName($name_like);
        $return_list = array();
        foreach ($list as $record) {
            $return_list[] = $record['name'];
        }
        return $return_list;
    }
    
    // 获得软件详情的展示码
    public static function getSoftDetailPageFlag() {
        return ContentTypeModel::getDetailPageFlag(1);
    }
    
    // 获得活动详情的展示码
    public static function getActivityDetailPageFlag() {
        return ContentTypeModel::getDetailPageFlag(2);
    }
    
    // 获得专题详情的展示码
    public static function getFeatureDetailPageFlag() {
        return ContentTypeModel::getDetailPageFlag(3);
    }
    
    // 获得网页的展示码
    public static function getWebsitePageFlag() {
        return ContentTypeModel::getDetailPageFlag(5);
    }
    // 获得礼包的展示码
    public static function getGiftDetailPageFlag() {
        return ContentTypeModel::getDetailPageFlag(6);
    }
	// 获得攻略的展示码
    public static function getStrategyDetailPageFlag() {
        return ContentTypeModel::getDetailPageFlag(7);
    }
    // 根据内容类型从sj_market_skip中查询页面的展示码，只支持1软件，2活动，3专题，页面的展示码不能调此函数
    private static function getDetailPageFlag($content_type) {
        if ($content_type != 1 && $content_type != 2 && $content_type != 3 && $content_type != 5 && $content_type !=6 && $content_type !=7) {
            return false;
        }
        $model = M();
        $where = array(
            'content_type' => $content_type,
        );
        $find = $model->table('sj_market_skip')->where($where)->find();
        if ($find['mark'])
            return $find['mark'];
        return false;
    }
    
    // 返回页面的展示码，如果有页面id，还会返回id数组
    public static function getPageFlagAndIds($page_type) {
        $ret = array(
            'page_flag' => '',
            'page_ids' => array(),
        );
        $model = M();
        $where = array(
            'content_type' => 4,
        );
        $order = "priority"; // 优先匹配优先级高的
        $list = $model->table('sj_market_skip')->where($where)->order($order)->select();
        foreach ($list as $key => $value) {
            $reg_exp = $value['reg_exp'];
            if (preg_match($reg_exp, $page_type, $matches)) {
                $page_flag = $value['mark'];
                $ret['page_flag'] = $value['mark'];
                $len = count($matches);
                for ($i=1; $i<$len; $i++) {
                    if (isset($matches[$i]) && is_numeric($matches[$i])) {
                        $ret['page_ids'][$i-1] = $matches[$i];
                    }
                }
                break;
            }
        }
        return $ret;
    }
    
    // 【可跳转页面】的【普通】类型的页面编码转页面名称
    public static function convertCategoryType2CategoryName($page_type,$method) {
        $category_list = ContentTypeModel::getCategoryTypes();
        unset($category_list['']);
		if(strpos($page_type,'bbs_detailpage_')!==false)//判断不是论坛详情页的page_type
		{
			return "论坛详情页";
		}
		elseif(strpos($page_type,'fixed_content_coop_')!==false)
		{
			return "内容合作";
		}
		else
		{
			if (array_key_exists($page_type, $category_list)) {
				if(is_array($category_list[$page_type]))
				{
					if($method=='js')
					{
						return $category_list[$page_type];
					}
					else
					{
						return $category_list[$page_type][0];
					}
				}
				else
				{
					return $category_list[$page_type];
				}
			}
		}
        return false;
    }
	
    // 将【频道运营】的【普通】类型的【可运营页面】编码转名称，主要是在频道运营的批量导入校验会用到这一功能
    public static function convertCategoryType2CategoryNameOfCategoryRunnable($page_type,$pid=1) {
        $category_list = ContentTypeModel::getCategoryTypesOfCategory($pid);
        unset($category_list['']);
        if (array_key_exists($page_type, $category_list)) {
            return $category_list[$page_type];
        }
        return false;
    }
    
    // 将【灵活运营样式】的【普通】类型的【可运营页面】编码转名称
    public static function convertCategoryType2CategoryNameOfFlexible($page_type,$general_page_type=1,$pid=0) {
        $category_list = ContentTypeModel::getCategoryTypesOfFlexible($pid,$general_page_type,$pid);
        unset($category_list['']);
        if (array_key_exists($page_type, $category_list)) {
            return $category_list[$page_type];
        }
        return false;
    }
    
    // 将【文字快捷入口】的【普通】类型的【可运营页面】编码转名称
    public static function convertCategoryType2CategoryNameOfTextQuickEntry($page_type) {
        $category_list = ContentTypeModel::getCategoryTypesOfTextQuickEntry();
        unset($category_list['']);
        if (array_key_exists($page_type, $category_list)) {
            return $category_list[$page_type];
        }
        return false;
    }
    
    // 【可跳转页面】的【普通】类型的页面名称转页面编码
    public static function convertCategoryName2CategoryType($page_name) {
        $category_list = ContentTypeModel::getCategoryTypes();
        unset($category_list['']);
        foreach ($category_list as $key => $value) {
			if(is_array($value))
			{
				if ($value[0] == $page_name) {
					return $key;
				}
			}
			else
			{
				if ($value == $page_name) {
					return $key;
				}
			} 
			
            
        }
        return false;
    }
    
    // 【频道运营】的【普通】类型的页面名称转页面编码
    public static function convertCategoryName2CategoryTypeOfCategory($page_name) {
        $category_list = ContentTypeModel::getCategoryTypesOfCategory();
        unset($category_list['']);
        foreach ($category_list as $key => $value) {
            if ($value == $page_name) {
                return $key;
            }
        }
        return false;
    }
    
    // 【灵活运营样式】的【普通】类型的页面名称转页面编码
    public static function convertCategoryName2CategoryTypeOfFlexible($page_name) {
        $category_list = ContentTypeModel::getCategoryTypesOfFlexible();
        unset($category_list['']);
        foreach ($category_list as $key => $value) {
            if ($value == $page_name) {
                return $key;
            }
        }
        return false;
    }
    
    // 【文字快捷入口】的【普通】类型的页面名称转页面编码
    public static function convertCategoryName2CategoryTypeOfTextQuickEntry($page_name) {
        $category_list = ContentTypeModel::getCategoryTypesOfTextQuickEntry();
        unset($category_list['']);
        foreach ($category_list as $key => $value) {
            if ($value == $page_name) {
                return $key;
            }
        }
        return false;
    }
    
    /*
    public static function convertOtherFixedPageType2OtherFixedPageName($page_type) {
        $other_fixed_list = ContentTypeModel::getOtherFixedPageTypes();
        if (array_key_exists($page_type, $other_fixed_list)) {
            return $other_fixed_list[$page_type];
        }
        return false;
    }
    
    public static function convertOtherFixedPageName2OtherFixedPageType($page_name) {
        $other_fixed_list = ContentTypeModel::getOtherFixedPageTypes();
        foreach ($other_fixed_list as $key => $value) {
            if ($value == $page_name) {
                return $key;
            }
        }
        return false;
    }
    */
    // 将标签页面名称转成标签页面代码
    public static function convertTagPageName2TagPageType($tag_page_name) {
        $split_str = "-";
        $arr = explode($split_str, $tag_page_name);
        $count = count($arr);
        if ($count < 2)
            return false;
        /* 现在需求又不要最新了，只有最热
        if ($arr[$count-1] != '最新' && $arr[$count-1] != '最热')
            return false;
        */
        if ($arr[$count-1] != '最热')
            return false;
        $real_name = '';
        for ($i = 0; $i < $count - 1; $i++) {
            if ($i != 0)
                $real_name .= $split_str;
            $real_name .= $arr[$i];
        }
        // 在标签表查找有没有此标签名
        $tags_model = D("Sj.Tags");
        $tag_id = $tags_model->getTagidbyname($real_name);
        if (!$tag_id) {
            return false;
        }
        //$suf = $arr[$count-1] == '最新' ? 'new' : 'hot';
        $suf = 'hot';
        $tag_page_type = "tag_{$tag_id}_{$suf}";
        return $tag_page_type;
    }
    
    // 将常用标签页面名称转成常用标签页面代码
    public static function convertCommonTagPageName2CommonTagPageType($commontag_page_name) {
        $split_str = "-";
        $arr = explode($split_str, $commontag_page_name);
        $count = count($arr);
        if ($count < 3)
            return false;
        if ($arr[$count-1] != '最新' && $arr[$count-1] != '最热')
            return false;
        $model = M();
        // 查找有没有此分类
        $category_name = $arr[0];
        $category_id = 0;
        // 获得所有有效的三级分类
        $category_model = D('Sj.Category');
        $third_level_category = $category_model->getThirdLevelCatgoryList();
        foreach ($third_level_category as $category_record) {
            if ($category_name == $category_record['name']) {
                 $category_id = $category_record['category_id'];
                 break;
            }
        }
        if (!$category_id) {
            return false;
        }
        // 在标签表查找有没有此标签名
        $tag_name = $arr[1];
        $tags_model = D("Sj.Tags");
        $tag_id = $tags_model->getTagidbyname($tag_name);
        if (!$tag_id) {
            return false;
        }
        // 查找此分类下有没有设此标签为常用标签(推荐标签id前有j字样)
        $where = array(
            'category_id' => $category_id,
            'tag_ids' => array(
                array('like', "%,{$tag_id},%"),
                array('like', "%,j{$tag_id},%"),
                'or',
            ),
        );
        $find = $model->table('sj_category')->where($where)->find();
        if (!$find)
            return false;
        $suf = $arr[$count-1] == '最新' ? 'new' : 'hot';
        $commontag_page_type = "commontag_{$category_id}_{$tag_id}_{$suf}";
        return $commontag_page_type;
    }
    
    // 将自定义列表名称转成页面编码
    // 自定义列表页面编码规则：customlist_{$id}
    public static function convertCustomListPageName2CustomListPageType($customlist_page_name) {
        // 根据名称查找id
        $model = D("Sj.CustomList");
        $find = $model->getNameRecordByName($customlist_page_name);
        if (!$find)
            return false;
        $id = $find['id'];
        $customlist_page_type = "customlist_{$id}";
        return $customlist_page_type;
    }
    public static function convertCustomListPageName2channelListPageType($customlist_page_name) {
        // 根据名称查找id
        $model = M('');
        $where = array(
            'channel_name' => $customlist_page_name,
            'status' => 1,
        );
        $find = $model->field('id,channel_name')->table('sj_custom_list_channel')->where($where)->find();
        // $find = $model->getNameRecordByName($customlist_page_name);
        if (!$find)
            return false;
        $id = $find['id'];
        $custom_list_channel_page_type = "custom_list_channel_{$id}";
        return $custom_list_channel_page_type;
    }
    
    public static function convertTagPageType2TagPageName($tag_page_type) {
        $split_str = "_";
        $arr = explode($split_str, $tag_page_type);
        $count = count($arr);
        if ($count < 3)
            return false;
        if ($arr[0] != "tag")
            return false;
        /* 现在需求又不要最新了，只有最热
        if ($arr[$count-1] != 'new' && $arr[$count-1] != 'hot')
            return false;
        */
        if ($arr[$count-1] != 'hot')
            return false;
        $tag_id = $arr[1];
        // 在标签表查找有没有此标签id
        $tags_model = D("Sj.Tags");
        $tag_name = $tags_model->getTagnamebyid($tag_id);
        if (!$tag_name)
            return false;
        // $suf = $arr[$count-1] == 'new' ? '最新' : '最热';
        $suf = '最热';
        $tag_page_name = "{$tag_name}-{$suf}";
        return $tag_page_name;
    }
    
    public static function convertCommonTagPageType2CommonTagPageName($commontag_page_type) {
        $split_str = "_";
        $arr = explode($split_str, $commontag_page_type);
        $count = count($arr);
        if ($count < 4)
            return false;
        if ($arr[$count-1] != 'new' && $arr[$count-1] != 'hot')
            return false;
        if ($arr[0] != 'commontag')
            return false;
        $model = M();
        // 查找有没有此分类id
        $category_id = $arr[1];
        $category_name = '';
        // 获得所有有效的三级分类
        $category_model = D('Sj.Category');
        $third_level_category = $category_model->getThirdLevelCatgoryList();
        foreach ($third_level_category as $category_record) {
            if ($category_id == $category_record['category_id']) {
                $category_name = $category_record['name'];
            }
        }
        if (!$category_name)
            return false;
        // 在标签表查找有没有此标签id
        $tag_id = $arr[2];        
        $tags_model = D("Sj.Tags");
        $tag_name = $tags_model->getTagnamebyid($tag_id);
        if (!$tag_name)
            return false;
        // 查找此分类下有没有设此标签为常用标签
        $where = array(
            'category_id' => $category_id,
            'tag_ids' => array(
                array('like', "%,{$tag_id},%"),
                array('like', "%,j{$tag_id},%"),
                'or',
            ),
        );
        $find = $model->table('sj_category')->where($where)->find();
        if (!$find)
            return false;
        $suf = $arr[$count-1] == 'new' ? '最新' : '最热';
        $commontag_page_name = "{$category_name}-{$tag_name}-{$suf}";
        return $commontag_page_name;        
    }
    
    // 将自定义列表编码转成页面名称
    // 自定义列表页面编码规则：customlist_{$id}
    public static function convertCustomListPageType2CustomListPageName($customlist_page_type) {
        // 将id提取出来
        $ret = preg_match("/^customlist_(\d+)$/", $customlist_page_type, $matches);
        if (!$ret)
            return false;
        $id = $matches[1];
        // 根据id查找名称
        $model = D("Sj.CustomList");
        $find = $model->getNameRecordById($id);
        if (!$find)
            return false;
        $name = $find['name'];
        return $name;
    }
    public static function convertCustomListPageType2channelListPageName($customlist_page_type) {
        // 将id提取出来
        $ret = preg_match("/^custom_list_channel_(\d+)$/", $customlist_page_type, $matches);
        if (!$ret)
            return false;
        $id = $matches[1];
        // 根据id查找名称
        $model = M('');
        $where = array(
            'id' => $id,
            'status' => 1,
        );
        $find = $model->field('*')->table('sj_custom_list_channel')->where($where)->find();
        // $find = $model->getNameRecordById($id);
        if (!$find)
            return false;
        $name = $find['channel_name'];
        return $name;
    }
    public static function convertCustomListPageType2custom_listid($customlist_page_type) {
        // 将id提取出来
        $ret = preg_match("/^custom_list_channel_(\d+)$/", $customlist_page_type, $matches);
        if (!$ret)
            return false;
        $channel_id = $matches[1];
        // 根据id查找名称
        $model = M('');
        $where = array(
            'channel_id' => $channel_id,
            'status' => 1,
        );
        $find = $model->field('*')->table('sj_custom_list_name')->where($where)->order('rank asc')->find();
        // var_dump($model->getlastsql());die;
        // $find = $model->getNameRecordById($id);
        if (!$find)
            return false;
        return $find['id'];
    }
    
    // 【可跳转页面】的根据页面代码转页面名称
    public static function convertPageType2PageName($page_type,$method) {
        $general_page_type = ContentTypeModel::getGeneralPageType($page_type);
        if (!$general_page_type)
            return false;
        if ($general_page_type == 1) {
            return ContentTypeModel::convertCategoryType2CategoryName($page_type,$method);
        } else if ($general_page_type == 2) {
            return ContentTypeModel::convertTagPageType2TagPageName($page_type);
        } else if ($general_page_type == 3) {
            return ContentTypeModel::convertCommonTagPageType2CommonTagPageName($page_type);
        } else if ($general_page_type == 4) {
            return ContentTypeModel::convertCustomListPageType2CustomListPageName($page_type);
        } else if ($general_page_type == 10) {
            return ContentTypeModel::convertCustomListPageType2channelListPageName($page_type);
        }
        return false;
    }
    
    // 【频道运营】的【可运营页面】根据页面代码转页面名称，主要是在频道运营的批量导入校验会用到这一功能
    public static function convertPageType2PageNameOfCategory($page_type,$pid=1) {
        $general_page_type = ContentTypeModel::getGeneralPageType($page_type);
        if (!$general_page_type)
            return false;
        if ($general_page_type == 1) {
            return ContentTypeModel::convertCategoryType2CategoryNameOfCategoryRunnable($page_type,$pid);
        } else if ($general_page_type == 2) {
            return ContentTypeModel::convertTagPageType2TagPageName($page_type);
        } else if ($general_page_type == 3) {
            return ContentTypeModel::convertCommonTagPageType2CommonTagPageName($page_type);
        } else if ($general_page_type == 5) {
            return ContentTypeModel::getbdListname($page_type);
        } else if($general_page_type == 4){
			return "自定义列表";
		}
        return false;
    }
    
    // 【灵活运营样式】的【可运营页面】根据页面代码转页面名称
    public static function convertPageType2PageNameOfFlexible($page_type,$pid=1) {
        $general_page_type = ContentTypeModel::getGeneralPageType($page_type);
        if (!$general_page_type)
            return false;
        if ($general_page_type == 1) {
            return ContentTypeModel::convertCategoryType2CategoryNameOfFlexible($page_type,$general_page_type,$pid);
        } else if ($general_page_type == 2) {
            return ContentTypeModel::convertTagPageType2TagPageName($page_type);
        } else if ($general_page_type == 3) {
            return ContentTypeModel::convertCommonTagPageType2CommonTagPageName($page_type);
        }else if ($general_page_type == 8) {//合作内容
            return "内容合作";
        }else if($general_page_type == 9){
			return "自定义列表";
        }
        return false;
    }
    
    // 【文字快捷入口】的【可运营页面】根据页面代码转页面名称
    public static function convertPageType2PageNameOfTextQuickEntry($page_type) {
        $general_page_type = ContentTypeModel::getGeneralPageType($page_type);
        if (!$general_page_type)
            return false;
        if ($general_page_type == 1) {
            return ContentTypeModel::convertCategoryType2CategoryNameOfTextQuickEntry($page_type);
        } else if ($general_page_type == 2) {
            return ContentTypeModel::convertTagPageType2TagPageName($page_type);
        } else if ($general_page_type == 3) {
            return ContentTypeModel::convertCommonTagPageType2CommonTagPageName($page_type);
        }else if ($general_page_type == 5) {
            return ContentTypeModel::getbdListname($page_type);
        }else if ($general_page_type == 8) { //内容合作
            return ContentTypeModel::convertCoopPageType2CoopPageName($page_type);
        }
        return false;
    }
    
    // 【可跳转页面】的根据页面名称返回页面代码
    public static function convertPageName2PageType($page_name, $general_page_type = 0) {
        if ($general_page_type) {
            if ($general_page_type == 1) {
                return ContentTypeModel::convertCategoryName2CategoryType($page_name);
            } else if ($general_page_type == 2) {
                return ContentTypeModel::convertTagPageName2TagPageType($page_name);
            } else if ($general_page_type == 3) {
                return ContentTypeModel::convertCommonTagPageName2CommonTagPageType($page_name);
            }else if ($general_page_type == 4) {
                return ContentTypeModel::convertCustomListPageName2CustomListPageType($page_name);
            }else if ($general_page_type == 10) {
                return ContentTypeModel::convertCustomListPageName2channelListPageType($page_name);
            }
            return false;
        } else {
            // 不确定是哪种类型，挨个找，找到就返回
            $page_type = ContentTypeModel::convertCategoryName2CategoryType($page_name);
            if ($page_type)
                return $page_type;
            $page_type = ContentTypeModel::convertTagPageName2TagPageType($page_name);
            if ($page_type)
                return $page_type;
            $page_type = ContentTypeModel::convertCommonTagPageName2CommonTagPageType($page_name);
            if ($page_type)
                return $page_type;
            $page_type = ContentTypeModel::convertCustomListPageName2CustomListPageType($page_name);
            if ($page_type)
                return $page_type;
            return false;
        }
    }
    
    
    // 【频道运营】的【可运营页面】根据页面名称转页面代码
    public static function convertPageName2PageTypeOfCategory($page_name, $general_page_type = 0) {
        if ($general_page_type) {
            if ($general_page_type == 1) {
                return ContentTypeModel::convertCategoryName2CategoryTypeOfCategory($page_name);
            } else if ($general_page_type == 2) {
                return ContentTypeModel::convertTagPageName2TagPageType($page_name);
            } else if ($general_page_type == 3) {
                return ContentTypeModel::convertCommonTagPageName2CommonTagPageType($page_name);
            }else if ($general_page_type == 4) {
                return ContentTypeModel::convertCustomListPageName2CustomListPageType($page_name);
            }
            return false;
        } else {
            // 不确定是哪种类型，挨个找，找到就返回
            $page_type = ContentTypeModel::convertCategoryName2CategoryTypeOfCategory($page_name);
            if ($page_type)
                return $page_type;
            $page_type = ContentTypeModel::convertTagPageName2TagPageType($page_name);
            if ($page_type)
                return $page_type;
            $page_type = ContentTypeModel::convertCommonTagPageName2CommonTagPageType($page_name);
            if ($page_type)
                return $page_type;
            $page_type = ContentTypeModel::convertCustomListPageName2CustomListPageType($page_name);
            if ($page_type)
                return $page_type;
            return false;
        }
    }
    
    // 【灵活运营样式】的【可运营页面】根据页面名称转页面代码
    public static function convertPageName2PageTypeOfFlexible($page_name, $general_page_type = 0) {
        if ($general_page_type) {
            if ($general_page_type == 1) {
                return ContentTypeModel::convertCategoryName2CategoryTypeOfFlexible($page_name);
            } else if ($general_page_type == 2) {
                return ContentTypeModel::convertTagPageName2TagPageType($page_name);
            } else if ($general_page_type == 3) {
                return ContentTypeModel::convertCommonTagPageName2CommonTagPageType($page_name);
            }else if ($general_page_type == 4) {
                return ContentTypeModel::convertCustomListPageName2CustomListPageType($page_name);
            }
            return false;
        } else {
            // 不确定是哪种类型，挨个找，找到就返回
            $page_type = ContentTypeModel::convertCategoryName2CategoryTypeOfFlexible($page_name);
            if ($page_type)
                return $page_type;
            $page_type = ContentTypeModel::convertTagPageName2TagPageType($page_name);
            if ($page_type)
                return $page_type;
            $page_type = ContentTypeModel::convertCommonTagPageName2CommonTagPageType($page_name);
            if ($page_type)
                return $page_type;
            $page_type = ContentTypeModel::convertCustomListPageName2CustomListPageType($page_name);
            if ($page_type)
                return $page_type;
            return false;
        }
    }
    
    // 【文字快捷入口】的【可运营页面】根据页面名称转页面代码
    public static function convertPageName2PageTypeOfTextQuickEntry($page_name, $general_page_type = 0) {
        if ($general_page_type) {
            if ($general_page_type == 1) {
                return ContentTypeModel::convertCategoryName2CategoryTypeOfTextQuickEntry($page_name);
            } else if ($general_page_type == 2) {
                return ContentTypeModel::convertTagPageName2TagPageType($page_name);
            } else if ($general_page_type == 3) {
                return ContentTypeModel::convertCommonTagPageName2CommonTagPageType($page_name);
            }else if ($general_page_type == 4) {
                return ContentTypeModel::convertCustomListPageName2CustomListPageType($page_name);
            }
            return false;
        } else {
            // 不确定是哪种类型，挨个找，找到就返回
            $page_type = ContentTypeModel::convertCategoryName2CategoryTypeOfTextQuickEntry($page_name);
            if ($page_type)
                return $page_type;
            $page_type = ContentTypeModel::convertTagPageName2TagPageType($page_name);
            if ($page_type)
                return $page_type;
            $page_type = ContentTypeModel::convertCommonTagPageName2CommonTagPageType($page_name);
            if ($page_type)
                return $page_type;
            $page_type = ContentTypeModel::convertCustomListPageName2CustomListPageType($page_name);
            if ($page_type)
                return $page_type;
            return false;
        }
    }
    
    // 获得页面类型，1为普通，2为标签页面，3为常用标签页面，4为自定义列表,5为自定义频道，其中前三种页面可运营
    public static function getGeneralPageType($page_type) {
        /*
        $category_list = ContentTypeModel::getCategoryTypes();
        unset($category_list['']);
        if (array_key_exists($page_type, $category_list)) {
            return 1;
        }
        // tag_type
        $tag_reg = "/^tag_/";
        $commontag_reg = "/^commontag_/";
        $otherfixed_reg = "/^otherfixed_/";
        if (preg_match($tag_reg, $page_type)) {
            return 2;
        } else if (preg_match($commontag_reg, $page_type)) {
            return 3;
        } else if (preg_match($otherfixed_reg, $page_type)) {
            return 4;
        }
        return false;
        */
        
        $page_type_feature = ContentTypeModel::getPageTypeFeature();
        foreach ($page_type_feature as $type => $feature_arr) {
            foreach ($feature_arr as $feature) {
                $reg = "/^{$feature}/";
                if (preg_match($reg, $page_type))
                    return $type;
            }
        }
        return false;
    }

    public static function convertGeneralPageType2GeneralPageName($general_page_type) {
        $general_page_name = '';
        switch ($general_page_type) {
            case 1:
                $general_page_name = '普通';
                break;
            case 2:
                $general_page_name = '标签';
                break;
            case 3:
                $general_page_name = '常用标签';
                break;
            /* 自定义列表为不可运营页面，不展示
            case 4:
                $general_page_name = '自定义列表';
                break;
            */
            default:
                $general_page_name = false;
                break;
        }
        return $general_page_name;
    }
    
    public static function checkIfPackagExists($package) {
        $model = M();
        $where = array(
            'package' => $package,
            'hide' => array('in', '1,1024'),
            'status' => 1,
        );
        $find = $model->table('sj_soft')->where($where)->find();
        return $find;
    }
    
    public static function convertPackage2Softname($package) {
        $model = M();
        $where = array(
            'package' => $package,
            'hide' => array('in', '1,1024'),
            'status' => 1,
        );
        $find = $model->table('sj_soft')->where($where)->find();
        if (!$find['softname'])
            return false;
        return $find['softname'];
    }
    
    public static function convertActivityName2ActivityId($activity_name) {
        $model = M();
        $where = array(
            'name' => $activity_name,
            'status' => 1,
        );
        $find = $model->table('sj_activity')->where($where)->find();
        if (!$find['id'])
            return false;
        return $find['id'];
    }
     public static function convertOrderName2OrderId($order_name) {
        $model = M();
        $where = array(
            'title' => $order_name,
            'status' => 1,
        );
        $find = $model->table('sj_game_subscriber')->where($where)->find();
        if (!$find['id'])
            return false;
        return $find['id'];
    }
	
    public static function convertActivityId2ActivityName($activity_id) {
        $model = M();
        $where = array(
            'id' => $activity_id,
            'status' => 1,
        );
        $find = $model->table('sj_activity')->where($where)->find();
        if (!$find['name'])
            return false;
        return $find['name'];
    }
	public static function convertOrderId2OrderName($order_id) {
        $model = M();
        $where = array(
            'id' => $order_id,
            'status' => 1,
        );
        $find = $model->table('sj_game_subscriber')->where($where)->find();
        if (!$find['title'])
            return false;
        return $find['title'];
    }
    
    public static function convertFeatureName2FeatureId($feature_name,$ppid) {
        $ppid = $ppid ==''?1:$ppid;
        $model = M();
        $where = array(
            'name' => $feature_name,
            'status' => 1,
            'pid' => array('like',"%,$ppid,%"),
        );
        
        $find = $model->table('sj_feature')->where($where)->find();
        
        if (!$find['feature_id'])
            return false;
        return $find['feature_id'];
    }
    
    public static function convertFeatureId2FeatureName($feature_id) {
        $model = M();
        $where = array(
            'feature_id' => $feature_id,
            'status' => 1,
        );
        $find = $model->table('sj_feature')->where($where)->find();
        if (!$find['name'])
            return false;
        return $find['name'];
    }
    
    public static function convertUsedName2UsedId($package) {
    	$model = M();
    	$where = array(
    			'package' => $package,
    			'status' => 1,
    	);
    	$find = $model->table('sj_soft_content_explicit')->where($where)->find();
    	if (!$find['id'])
    		return false;
    	return $find['id'];
    }
    
    public static function convertUsedId2UsedName($used_id) {
    	$model = M();
    	$where = array(
    			'id' => $used_id,
    			'status' => 1,
    	);
    	$find = $model->table('sj_soft_content_explicit')->where($where)->find();
    	if ( empty($find) ) {
    		return false;
    	}else {
    		$ret = array(
    			'package'	=>	$find['package'],
    			'title'		=>	$find['title'],
    			'softname'	=>	$find['softname'],
    		);
    		return $ret;
    	}
    }
    
    
    public static function check_url($url) {
        $reg = "/^((http:\/\/)|(https:\/\/))([\w\d-]+\.)+[\w-]+(\/[\x{4e00}-\x{9fa5}\d\w-.\/?%&=]*)?$/iu";
        if (!preg_match($reg, $url)) {
            return false;
        }
        return true;
    }
	public static function check_gift_id($gift_id)
	{
		$active_model = D('sendNum.sendNum');
		$where = array(
			'b.end_tm' => array('egt',time()),
			'a.status' => 1,
			'b.active_from'=>array('exp',"&4=4"),
			'b.status' => 1,
			'b.id' => $gift_id,
		); 
		$result = $active_model->table('olgame_active a')->field('a.id as rank_id,a.active_id,b.*')->join("sendnum_active b on a.active_id = b.id")->where($where)->find();
        if (!$result['id'])
            return false;
        return $result['id'];
	}
	//检查是否是已发布的攻略
	public static function check_strategy_id($stragety_id)
	{
		$model=M();
		$where = array(
            'id' => $stragety_id,
            'status' => 2,//已发布
			'info_type' =>3,//标识攻略
        );
        $find = $model->table('sj_olgame_news')->where($where)->find();
        if (!$find['id'])
            return false;
        return $find['id'];
	}
	function convertnum2MarkName($mark_num)
	{
		//1推广、2推荐、3活动、4专题、5精选、6汉化、7破解、30自定义
		$mark_name=array(
			'1'=>'推广',
			'2'=>'推荐',
			'3'=>'活动',
			'4'=>'专题',
			'5'=>'精选',
			'6'=>'汉化',
			'7'=>'破解',
			'30'=>'自定义',
		);
		return $mark_name[$mark_num];
	}
	function saveRecommendContent($data,$content_type,&$map)
	{
		//推荐内容处理
		if(trim($data['content_type']))//判断是添加的
		{
			$content_type=trim($data['content_type']);
		}
		else
		{
			$content_type=$content_type;//判断是编辑的
		}
		if ($content_type != 1 && $content_type != 2 && $content_type != 3 && $content_type != 4 && $content_type != 5 && $content_type !=6 &&$content_type !=7&&$content_type !=8&&$content_type !=9&&$content_type !=10) 
		{
			return "请添加推荐内容";
		}
		$map['content_type'] = $content_type;
		if ($content_type == 1) 
		{
			$package = trim($data['package']);
			if (!$package) {
				return "包名不能为空";
			}
			if (!ContentTypeModel::checkIfPackagExists($package)) {
				return "包名不存在";
			}
			$map['package'] = $package;
			// 跳转配置
			$uninstall_setting = $data['uninstall_setting'];
			$map['uninstall_setting'] = $uninstall_setting;
			$install_setting = $data['install_setting'];
			$map['install_setting'] = $install_setting;
			if ($install_setting == 4) {
				$start_to_page = $data['start_to_page'];
				$map['start_to_page'] = $start_to_page;
			}
			$lowversion_setting = $data['lowversion_setting'];
			$map['lowversion_setting'] = $lowversion_setting;
			// 获得软件详情页的展示编码
			$ret=ContentTypeModel::getSoftDetailPageFlag();
			if ($ret) {
				$map['page_flag'] = $ret;
			}
			//$map['page_flag'] = ContentTypeModel::getSoftDetailPageFlag();
			//灵活运营 有运营标识
			if($map['from']=='flexible')
			{
				$map['opera_mark_num'] = '2';
				$map['opera_mark_name'] = '推荐';
			}
			//V6.4新增行为id
			if(trim($data['recommend_behavior_id']))
			{
				if(!preg_match('/^\d+$/',$data['recommend_behavior_id'])){
					return "行为id格式错误";
				}
				else
				{
					$param_arr1=array(
						'recommend_behavior_id' =>trim($data['recommend_behavior_id']),
						'comment'=>'recommend_behavior_id:V6.4新增加行为id',
					);
					$map['parameter_field'] = json_encode($param_arr1);
				}	
			}
			else
			{
				$map['parameter_field'] = '';
			}
		} else if ($content_type == 2) 
		{
			$activity_id = trim($data['activity_id']);
			if (!$activity_id) 
			{
				return "活动不能为空";
			}
			if (!ContentTypeModel::convertActivityId2ActivityName($activity_id)) 
			{
				return "活动不存在";
			}
			$map['activity_id'] = $activity_id;
			// 获得活动详情页的展示编码
			$ret=ContentTypeModel::getActivityDetailPageFlag();
			if ($ret) {
				$map['page_flag'] = $ret;
			}
			//$map['page_flag'] = ContentTypeModel::getActivityDetailPageFlag();
			if($map['from']=='flexible')
			{
				$map['opera_mark_num'] = '3';
				$map['opera_mark_name'] = '活动';
			}
		}else if ($content_type == 3) {
			$feature_id = trim($data['feature_id']);
			if (!$feature_id) {
				return "专题不能为空";
			}
			if (!ContentTypeModel::convertFeatureId2FeatureName($feature_id)) {
				return "专题不存在";
			}
			$map['feature_id'] = $feature_id;
			// 获得专题详情页的展示编码
			$ret=ContentTypeModel::getFeatureDetailPageFlag();
			if ($ret) {
				$map['page_flag'] = $ret;
			}
			//$map['page_flag'] = ContentTypeModel::getFeatureDetailPageFlag();
			if($map['from']=='flexible')
			{
				$map['opera_mark_num'] = '4';
				$map['opera_mark_name'] = '专题';
			}
		}else if ($content_type == 4) {
			$page_type = trim($data['page_type']);
			if (!$page_type) 
			{
				return "页面不能为空";
			}
			if(strpos($page_type,'bbs_detailpage_')!=0&&strpos($page_type,'fixed_content_coop_')!=0)//判断不是论坛详情页的page_type、新增加的内容合作也没必要判断
			{
				if (!ContentTypeModel::convertPageType2PageName($page_type)) {
					return "页面不存在";
				}
			}
			$map['page_type'] = $page_type;
			// 生成page_flag和page_id值
			$ret = ContentTypeModel::getPageFlagAndIds($page_type);
			if ($ret['page_flag']) {
				$map['page_flag'] = $ret['page_flag'];
			}
			if (!empty($ret['page_ids'])) {
				if (isset($ret['page_ids'][0])) {
					$map['page_id1'] = $ret['page_ids'][0];
					if (isset($ret['page_ids'][1])) {
						$map['page_id2'] = $ret['page_ids'][1];
					}
				}
			}
				
			//外部推广 选择个人中心  是否弹出随机礼包 
			if($page_type=="fixed_personal_center"&&$data['is_pop_random_packs'])
			{
				$param_arr=array(
					'is_pop_random_packs' =>trim($data['is_pop_random_packs']),
					'comment'=>'is_pop_random_packs:页面选择个人中心时是否弹出随机礼包,1:是,2:否;',
				);
				$map['parameter_field'] = json_encode($param_arr);
			}
			//文字快捷入口 选择活动列表  活动类型的展示
			elseif($page_type=="otherfixed_activity_list")
			{
				if($data['activity_category_show'])
				{
					$active_category_show = trim($data['activity_category_show']);
				}
				else
				{
					$active_category_show = 0;
				}

				if($data['tab_index']){
					$tab_index = $data['tab_index'];
				}else{
					$tab_index = 0;
				}
				$param_arr1=array(
					'activity_category_show' =>$active_category_show,
					'comment'=>'activity_category_show:页面选择活动列表时选择活动类型,0:全部,1:游戏,2:应用;',
					'tab_index' => $tab_index
				);
				$map['parameter_field'] = json_encode($param_arr1);
			}
			//灵活运营样式和文字快捷入口 添加 每日抽奖兑吧
			elseif($page_type=="otherfixed_daily_lottery_exchange"&&$data['exchange_url'])
			{
				$param_arr2=array(
					'exchange_url' =>trim($data['exchange_url']),
					'comment'=>'exchange_url:页面选择每日抽奖兑吧，页面的地址',
				);
				$map['parameter_field'] = json_encode($param_arr2);
			}else if( $page_type=="fixed_red_packet_task_list" ){
				//6.5 红包任务列表
				if($data['red_task_category_show'])
				{
					$red_task_category_show = trim($data['red_task_category_show']);
				}
				else
				{
					$red_task_category_show = 0;
				}
				$param_arr1=array(
						'TAB_INDEX' => $red_task_category_show,
				);
				$map['parameter_field'] = json_encode($param_arr1);
			}else if(strpos($page_type, 'custom_list_channel_')!== false){
				$param_arr1=array(
					'title' => ContentTypeModel::convertCustomListPageType2channelListPageName($page_type),
					'tab_id' => ContentTypeModel::convertCustomListPageType2custom_listid($page_type),
				);
				$map['parameter_field'] = json_encode($param_arr1);
			}
			//根据配置 保存参数的值
			$method='js'; //加上一个参数来区分是展示导向页面还是获取配置中的参数
			$new_page_name = ContentTypeModel::convertPageType2PageName($page_type,$method);
			if(is_array($new_page_name))
			{
				$param_arr=array();
				$arr_page_name = $new_page_name[0];
				$config_param_arr=$new_page_name[1];
				foreach($config_param_arr as $k =>$v)
				{
					if($v[1]==1)
					{
						$param_arr[$k]=trim($data[$k]);
						$param_arr[$k.'_comment']=$k.'_comment:填写内容';
					}
					elseif($v[1]==2)
					{
						$param_arr[$k]=trim($data[$k]);
						$str_comment =implode(',',$v[2]);
						$param_arr[$k.'_comment']=$k.'_comment:从1开始'.$str_comment;
					}
				}
				$map['parameter_field'] = json_encode($param_arr);
			}
			//灵活样式
			if($map['from']=='flexible')
			{
				//获取运营标识
				$operate_mark=$data['operate_mark'];//返回来的是数字
				$custom_operate_mark=$data['custom_mark'];
				if(!$operate_mark&&!$custom_operate_mark)
				{
					return "运营标识必填";
				}
				if($operate_mark)
				{
					//如果有值说明不是自定义
					$map['opera_mark_num']=$operate_mark;
					$map['opera_mark_name'] = ContentTypeModel::convertnum2MarkName($operate_mark);
				}
				else
				{
					$operate_mark=30; //自定义值是30
					//返回自定义的名字
					$custom_operate_mark=$data['custom_mark'];
					if(mb_strlen($custom_operate_mark,'utf8')>2)
					{
						return "自定义的标志不能超过两个字";
					}
					else
					{
						//自定义的情况下 保存标识代号保存自定义名字
						$map['opera_mark_num']=$operate_mark;
						$map['opera_mark_name']=$custom_operate_mark;
					}
				}
			}
			//内容合作 添加内容
			if(strpos($page_type,'fixed_content_coop_')===0)
			{
				$page_type_arr = explode('_',$page_type);
				if($page_type_arr[3]==3) //合作详情页
				{
					$model = M();
					if($data['coop_detail_url'])//V6.4新增加的 说明是按照标题填写的
					{
						$coop_param_arr=array(
							'coop_site_id' =>trim($data['coop_site_id']),
							'coop_detail_url' =>trim($data['coop_detail_url']),
							'coop_detail_url_id' =>trim($data['coop_detail_url_id']),
							'coop_content_id_new' =>trim($data['coop_content_id_new']),
							'comment'=>'coop_site_id:内容合作：选择的站点id,coop_detail_url:内容合作：选择详情页的详情页地址',
						);
					}
					if($data['coop_detail_label_id']&&$data['coop_detail_pos'])//说明是按照标签
					{
						$coop_param_arr=array(
							'coop_site_id' =>trim($data['coop_site_id']),
							'coop_detail_label_id' =>trim($data['coop_detail_label_id']),
							'coop_detail_pos' =>trim($data['coop_detail_pos']),
							'comment'=>'coop_site_id:内容合作：选择的站点id,coop_detail_label_id:内容合作：选择详情页的标签id,coop_detail_pos:详情页选择标签中的位置',
						);
					}
				}
				else//频道和标签
				{
					$coop_param_arr=array(
						'coop_site_id' =>trim($data['coop_site_id']),
						'coop_channel_tag_id' =>trim($data['coop_channel_tag_id']),
						'comment'=>'coop_site_id:内容合作：选择的站点id,coop_channel_tag_id:站点对应的频道id或者标签id',
					);
				}
				$map['parameter_field'] = json_encode($coop_param_arr);
			}
		}else if ($content_type == 5) {
			$website = trim($data['website']);
			if (!$website) {
				return "网页不能为空";
			}
			if (!ContentTypeModel::check_url($website))
				return "请输入正确的url地址";
			$map['website'] = $website;
			$website_open_type = trim($data['website_open_type']);
			$map['website_open_type'] = $website_open_type;
			$ret=ContentTypeModel::getWebsitePageFlag();
			if ($ret) {
				$map['page_flag'] = $ret;
			}
			//$map['page_flag'] = ContentTypeModel::getWebsitePageFlag();
			//灵活样式
			if($map['from']=='flexible')
			{
				//获取运营标识
				$operate_mark=$data['operate_mark'];//返回来的是数字
				$custom_operate_mark=$data['custom_mark'];
				if(!$operate_mark&&!$custom_operate_mark)
				{
					return "运营标识必填";
				}
				if($operate_mark)
				{
					//如果有值说明不是自定义
					$map['opera_mark_num']=$operate_mark;
					$map['opera_mark_name'] = ContentTypeModel::convertnum2MarkName($operate_mark);
				}
				else
				{
					$operate_mark=30; //自定义值是30
					//返回自定义的名字
					$custom_operate_mark=$data['custom_mark'];
					if(mb_strlen($custom_operate_mark,'utf8')>2)
					{
						return "自定义的标志不能超过两个字";
					}
					else
					{
						//自定义的情况下 保存标识代号保存自定义名字
						$map['opera_mark_num']=$operate_mark;
						$map['opera_mark_name']=$custom_operate_mark;
					}
				}
			}
			$w_param_arr = array();
			if(isset($data['website_mobile_config'])) $w_param_arr['website_mobile_config'] = $data['website_mobile_config'];
			if(isset($data['website_is_sync_accout'])) $w_param_arr['website_is_sync_accout'] = $data['website_is_sync_accout'];
			if(isset($data['website_is_actionbar'])) $w_param_arr['website_is_actionbar'] = $data['website_is_actionbar'];
			if(isset($data['website_screen_show'])) $w_param_arr['website_screen_show'] = $data['website_screen_show'];
			if(isset($data['website_is_h5'])) $w_param_arr['website_is_h5'] = $data['website_is_h5'];
			$map['parameter_field'] = '';
			if(!empty($w_param_arr)){
				$map['parameter_field'] = json_encode($w_param_arr);
			}
		}else if ($content_type == 6) {
			$page_type = trim($data['page_type']);
			if (!$page_type) 
			{
				return "页面不能为空";
			}
			$map['page_type'] = $page_type;
			
			$gift_id = trim($data['gift_id']);
			if (!$gift_id) 
			{
				return "礼包ID不能为空";
			}
			if (!ContentTypeModel::check_gift_id($gift_id))
				return "请输入正确的礼包ID";
			$map['gift_id'] = $gift_id;
			$ret=ContentTypeModel::getGiftDetailPageFlag();
			if ($ret) {
				$map['page_flag'] = $ret;
			}
			//$map['page_flag'] = ContentTypeModel::getGiftDetailPageFlag();
			if($map['from']=='flexible')
			{
				$map['opera_mark_num'] = '8';
				$map['opera_mark_name'] = '礼包';
			}
		}else if ($content_type == 7) 
		{
			$page_type = trim($data['page_type']);
			if (!$page_type) 
			{
				return "页面不能为空";
			}
			$map['page_type'] = $page_type;
			
			$stragety_id = trim($data['strategy_id']);
			if (!$stragety_id) 
			{
				return "攻略ID不能为空";
			}
			if (!ContentTypeModel::check_strategy_id($stragety_id))
				return "请输入正确的攻略ID";
			$map['strategy_id'] = $stragety_id;
			$ret=ContentTypeModel::getStrategyDetailPageFlag();
			if ($ret) {
				$map['page_flag'] = $ret;
			}
			//$map['page_flag'] = ContentTypeModel::getStrategyDetailPageFlag();
			if($map['from']=='flexible')
			{
				$map['opera_mark_num'] = '9';
				$map['opera_mark_name'] = '攻略';
			}
		}else if ($content_type == 8) {
			$order_id = trim($data['recommend_order_id']);
			if (!$order_id) 
			{
				return "预约名称不能为空";
			}
			if (!ContentTypeModel::convertOrderId2OrderName($order_id)) 
			{
				return "预约不存在";
			}
			$map['activity_id'] = $order_id;
			// 获得活动详情页的展示编码
			//$ret=ContentTypeModel::getActivityDetailPageFlag();
			/*if ($ret) {
				$map['page_flag'] = $ret;
			}*/
			//$map['page_flag'] = ContentTypeModel::getActivityDetailPageFlag();
			if($map['from']=='flexible')
			{
				$map['opera_mark_num'] = '2';
				$map['opera_mark_name'] = '推荐';
			}
		}else if($content_type == 9) {
			$model = M();
			$used_id = (Int)$data['used_id'];
			if( !$used_id ) {
				return "该软件未有符合条件的应用预览的内容";
			}
			$where = array(
					'id' => $used_id,
			);
			$used_info = $model->table('sj_soft_content_explicit')->where($where)->find();
			$map['parameter_field'] = json_encode(array('used_id'=>$used_id,'package'=>$used_info['package'],'softname'=>$used_info['softname'],'title'=>$used_info['title'],'page_type'=>'fixed_content_explicit'));
		}else if($content_type == 10) {
			$order_id = trim($data['recommend_order_id']);
			if (!$order_id) 
			{
				return "预约名称不能为空";
			}
			if (!ContentTypeModel::convertOrderId2OrderName($order_id)) 
			{
				return "预约不存在";
			}
			$map['activity_id'] = $order_id;
			if($map['from']=='flexible')
			{
				$map['opera_mark_num'] = '2';
				$map['opera_mark_name'] = '推荐';
			}
		}else {
			return "类型错误";
		}

		if($map['from'])
		{
			unset($map['from']);
		}
		return true;
	}


	function save_flexible_data($data,&$map,$extent_type){
		if(!empty($data['resource_id_29'])){
			$map['res_id'] = $data['resource_id_29'];
		}
		$func = "save_flexible_{$extent_type}";
		$need_data = ContentTypeModel::$func($data);
		if(!empty($map['parameter_field'])){
			$decode_map = json_decode($map['parameter_field'],true);
			$map['parameter_field'] = json_encode(array_merge($decode_map,$need_data));
		}else{
			$map['parameter_field'] = json_encode($need_data);
		}
	}

	//多排新样式
	function save_flexible_5($data){
		$need_data = array();
		if(isset($data['position'])) $need_data['position'] = $data['position'];
		if(isset($data['show_down'])) $need_data['show_down'] = $data['show_down'];
		return $need_data;
	}

	//单软件(视频)
	function save_flexible_29($data){
		$need_data = array();
		if(isset($data['is_tag'])) $need_data['is_tag'] = $data['is_tag'];
		if(isset($data['tag_title'])) $need_data['tag_title'] = $data['tag_title'];
		if(isset($data['video'])&&empty($data['resource_id_29'])) $need_data['video_id'] = $data['video'];
		return $need_data;
	}

	//banner+软件
	function save_flexible_31($data){
		$need_data = array();
		if(isset($data['subtitle'])) $need_data['subtitle'] = $data['subtitle'];
		return $need_data;
	}

	//多排-专题/页面（自动取软件）
	function save_flexible_32($data){
		$need_data = array();
		if(isset($data['soft_rank'])) $need_data['soft_rank'] = $data['soft_rank'];
		if(isset($data['search_type'])) $need_data['search_type'] = $data['search_type'];
		return $need_data;
	}

	//搜索推荐内容
	function save_flexible_33($data){
		$need_data = array();
		if(isset($data['content_rank'])) $need_data['content_rank'] = $data['content_rank'];
		if(isset($data['search_type'])) $need_data['search_type'] = $data['search_type'];
		return $need_data;
	}

	//V6.4.9 多排（关注游戏）
	function save_flexible_35($data){
		$need_data = array();
		if(isset($data['rank'])) $need_data['rank'] = $data['rank'];
		return $need_data;
	}

	//V6.4.9 多排（关注论坛）
	function save_flexible_36($data){
		$need_data = array();
		if(isset($data['forum_id'])) $need_data['forum_id'] = intval($data['forum_id']);
		if(isset($data['forum_name'])) $need_data['forum_name'] = $data['forum_name'];
		if(isset($data['rank'])) $need_data['rank'] = $data['rank'];
		return $need_data;
	}

	//V6.4.9活动卡片
	function save_flexible_37($data){
		$need_data = array();
		if(isset($data['activity_id'])) $need_data['activity_id'] = $data['activity_id'];
		if(isset($data['activity_name'])) $need_data['activity_name'] = $data['activity_name'];
		return $need_data;
	}

	//V6.4.9多行软件
	function save_flexible_38($data){
		$need_data = array();
		if(isset($data['rank'])) $need_data['rank'] = $data['rank'];
		return $need_data;
	}

	//V6.4.9论坛内容
	function save_flexible_39($data){
		$need_data = array();
		if(isset($data['forum_id'])) $need_data['forum_id'] = intval($data['forum_id']);
		if(isset($data['forum_tid'])) $need_data['forum_tid'] = intval($data['forum_tid']);
		if(isset($data['is_force_single'])) $need_data['is_force_single'] = $data['is_force_single'];
		if(isset($data['recommend_behavior_id'])) $need_data['recommend_behavior_id'] = $data['recommend_behavior_id'];
		return $need_data;
	}

	//V6.4.9论坛大图内容
	function save_flexible_40($data){
		$need_data = array();
		if(isset($data['forum_id'])) $need_data['forum_id'] = intval($data['forum_id']);
		if(isset($data['forum_tid'])) $need_data['forum_tid'] = intval($data['forum_tid']);
		if(isset($data['recommend_behavior_id'])) $need_data['recommend_behavior_id'] = $data['recommend_behavior_id'];
		return $need_data;
	}

	function get_flexible_param(&$soft,$extent_type){
		if(!empty($soft['parameter_field'])) {
			$info = json_decode($soft['parameter_field'], true);
			if($extent_type==5){
				isset($info['position'])?$soft['position'] = $info['position']:'';
				isset($info['show_down'])?$soft['show_down'] = $info['show_down']:'';
			}elseif($extent_type==29){
				isset($info['video_id'])?$soft['video']=$info['video_id']:'';
				isset($info['is_tag'])?$soft['is_tag']=$info['is_tag']:'';
				isset($info['tag_title'])?$soft['tag_title']=$info['tag_title']:'';
				if($soft['video']){
					$model = M('');
					$video_info = $model->table('sj_soft_extra')->where("id={$soft['video']}")->field('id,video_pic')->find();
					$soft['video_pic'] = ATTACHMENT_HOST.$video_info['video_pic'];
				}
			}elseif($extent_type == 31||$extent_type==35||$extent_type==36){
				isset($info['subtitle'])?$soft['subtitle']=$info['subtitle']:'';
			}elseif($extent_type == 32){
				isset($info['soft_rank'])?$soft['soft_rank']=$info['soft_rank']:'';
			}elseif($extent_type == 33){
				isset($info['content_rank'])?$soft['content_rank']=$info['content_rank']:'';
			}
			if($extent_type == 32||$extent_type == 33){
				isset($info['search_type'])?$soft['search_type']=$info['search_type']:'';
			}
			if($extent_type == 35||$extent_type==36||$extent_type==38){
				isset($info['rank'])?$soft['rank']=$info['rank']:'';
			}
			if($extent_type==36){
				isset($info['forum_id'])?$soft['forum_id']=$info['forum_id']:'';
				isset($info['forum_name'])?$soft['forum_name']=$info['forum_name']:'';
			}
			if($extent_type==37){
				isset($info['activity_id'])?$soft['activity_id']=$info['activity_id']:'';
				isset($info['activity_name'])?$soft['activity_name']=$info['activity_name']:'';
			}
			if($extent_type==39){
				isset($info['forum_id'])?$soft['forum_id']=$info['forum_id']:'';
				isset($info['forum_tid'])?$soft['forum_tid']=$info['forum_tid']:'';
				isset($info['is_force_single'])?$soft['is_force_single']=$info['is_force_single']:'';
				isset($info['recommend_behavior_id'])?$soft['recommend_behavior_id']=$info['recommend_behavior_id']:'';
			}
			if($extent_type==40){
				isset($info['forum_id'])?$soft['forum_id']=$info['forum_id']:'';
				isset($info['forum_tid'])?$soft['forum_tid']=$info['forum_tid']:'';
				isset($info['recommend_behavior_id'])?$soft['recommend_behavior_id']=$info['recommend_behavior_id']:'';
			}
		}
	}

	function export_all_pages_operation()
	{
		//频道列表软件推荐的普通的页面
		$map = ContentTypeModel::getCategoryTypesOfCategory();
		
		//灵活运营的页面
		$map_flexible = ContentTypeModel::getCategoryTypesOfFlexible();
		
		//文字快捷入口的页面
		$map_quick =ContentTypeModel::getCategoryTypesOfTextQuickEntry();
		
		//通用跳转的页面
		$map_jump = ContentTypeModel::getCategoryTypes();
		
		//榜单的页面
		$map_bd = ContentTypeModel::getbdList();
		
		//$map_custom = ContentTypeModel::getCustomList();
		//标签的页面
		$map_tag = ContentTypeModel::getTagsCodeName();
		
		//常用标签的页面
		$map_common_tag = ContentTypeModel::getCommonTagesCodeName();
		
		//自定义的页面
		$map_custom = ContentTypeModel::getCustomCodeName();
		
		//专题的页面
		$map_feature = ContentTypeModel::getfeatureList();
        $map_other = ContentTypeModel::getotherlist();
        
        $string=array_merge($map,$map_flexible,$map_quick,$map_jump,$map_bd,$map_tag,$map_common_tag,$map_custom,$map_feature, $map_other);
		unset($string['']);
		
		return $string;
	}
	/**获取标签页面编码和名称   重新组装 
	 **add by shitingting 2015-9-30
	*/
	function getTagsCodeName()
	{
		$tags = D('Sj.Tags');
		$tags_list = $tags->getTagslist();
		foreach ($tags_list as $v) {
			//查看以前代码 标签的编码是 tag_"tag_id"_"hot"   在这里重新组装下  注：标签只有最热，没有最新了 
			//$map['tag_' . $v['tag_id'] . '_new'] = $v['tag_name'] . '-最新';
			$map['tag_' . $v['tag_id'] . '_hot'] = $v['tag_name'] . '-最热';
		}
		return $map;
	}
	/**获取常用标签编码和名称  常用的标签是三级的标签 
	 **
	*/
    function getCommonTagesCodeName()
    {
        // 获得所有有效的三级分类
        $category_model = D('Sj.Category');
        $third_level_category = $category_model->getThirdLevelCatgoryList();
        // 第一步：所有的三级分类记录
        $match_categorys = array();
        foreach ($third_level_category as $third) {
            //$name = $third['name'];
            //if (preg_match("/{$real_keyword}/", $name)) {
                $match_categorys[] = $third;
            //}
        }
        $tags_model = D("Sj.Tags");
        $tag_id_arr=array();
        foreach ($match_categorys as $v) 
        {
            if (!$v['tag_ids'])
                continue;
            $tag_ids = $v['tag_ids'];
            $tag_arr = explode(',', $tag_ids);
            
            foreach ($tag_arr as $tag_id) 
            {
                $tag_id = ltrim($tag_id, "j");
                if (!$tag_id)
                    continue;
                
                $tag_id_arr[]=$tag_id;
                //$tag_name = $tags_model->getTagnamebyid($tag_id);
                //if ($tag_name) 
                //{
                //  $map['commontag_' . $v['category_id'] . '_' . $tag_id.'_hot'] = $v['name'] .'_' . $tag_name.'-最热';
                //  $map['commontag_' . $v['category_id'] . '_' . $tag_id.'_new'] = $v['name'] .'_'. $tag_name.'-最新';
                //}
            }
        }
        $tag_id_data = $tags_model->field('tag_id,tag_name')->where(array('tag_id'=>array('in',$tag_id_arr),'status'=>1))->select();
        $tag_id_data_index=array();
        foreach($tag_id_data as $v){
            $tag_id_data_index[$v['tag_id']]=$v['tag_name'];
        }
        foreach ($match_categorys as $v) 
        {
            if (!$v['tag_ids'])
                continue;
            $tag_ids = $v['tag_ids'];
            $tag_arr = explode(',', $tag_ids);
            // var_dump($tag_arr);
            foreach ($tag_arr as $tag_id) 
            {
                if (!$tag_id)
                    continue;
                $tag_id = ltrim($tag_id, "j");
                
                // $tag_name = $tags_model->getTagnamebyid($tag_id);
                $tag_name = $tag_id_data_index[$tag_id];
                if ($tag_name) 
                {
                    $map['commontag_' . $v['category_id'] . '_' . $tag_id.'_hot'] = $v['name'] .'_' . $tag_name.'-最热';
                    $map['commontag_' . $v['category_id'] . '_' . $tag_id.'_new'] = $v['name'] .'_'. $tag_name.'-最新';
                }
            }
        }
        return $map;
    }
	/**获取自定义编码 
	 **
	*/
	function getCustomCodeName()
	{
		$custom_model = D('Sj.CustomList');
		$result = $custom_model ->getAllCustom();
        foreach ($result as $v) 
		{
			$map['customlist_' . $v['id']] = $v['name'];
		}
		return $map;
	}
	
	//专题改版  推荐内容单独处理
	function saveRecommendContent_new($data,$content_type,&$map,$content_key)
	{
		//推荐内容处理
		if(trim($data['content_type'][$content_key]))//判断是添加的
		{
			$content_type=trim($data['content_type'][$content_key]);
		}else{
			$content_type = $content_type;//判断是编辑的
		}
		if ($content_type != 1 && $content_type != 2 && $content_type != 3 && $content_type != 4 && $content_type != 5 && $content_type !=6 &&$content_type !=7&&$content_type !=8 &&$content_type !=9&&$content_type !=10)
		{
			return "请输入正确的类型";
		}
		$map['content_type'] = $content_type;
		if ($content_type == 1)
		{
			$package = trim($data['package'][$content_key]);
			if (!$package) {
				return "包名不能为空";
			}
			if (!ContentTypeModel::checkIfPackagExists($package)) {
				return "包名不存在";
			}
			$map['package'] = $package;
			// 跳转配置
			$uninstall_setting = $data['uninstall_setting'][$content_key];
			$map['uninstall_setting'] = $uninstall_setting;
			$install_setting = $data['install_setting'][$content_key];
			$map['install_setting'] = $install_setting;
			if ($install_setting == 4) {
				$start_to_page = $data['start_to_page'][$content_key];
				$map['start_to_page'] = $start_to_page;
			}
			$lowversion_setting = $data['lowversion_setting'][$content_key];
			$map['lowversion_setting'] = $lowversion_setting;
			// 获得软件详情页的展示编码
			$ret=ContentTypeModel::getSoftDetailPageFlag();
			if ($ret) {
				$map['page_flag'] = $ret;
			}
			//$map['page_flag'] = ContentTypeModel::getSoftDetailPageFlag();
			//灵活运营 有运营标识
			if($map['from']=='flexible')
			{
				$map['opera_mark_num'] = '2';
				$map['opera_mark_name'] = '推荐';
			}
			//V6.4新增行为id
			if(trim($data['recommend_behavior_id'][$content_key]))
			{
				if(!preg_match('/^\d+$/',$data['recommend_behavior_id'][$content_key])){
					return "行为id格式错误";
				}
				else
				{
					$param_arr1=array(
							'recommend_behavior_id' =>trim($data['recommend_behavior_id'][$content_key]),
							'comment'=>'recommend_behavior_id:V6.4新增加行为id',
					);
					$map['parameter_field'] = json_encode($param_arr1);
				}
			}
			else
			{
				$map['parameter_field'] = '';
			}
		} else if ($content_type == 2)
		{
			$activity_id = trim($data['activity_id'][$content_key]);
			if (!$activity_id)
			{
				return "活动不能为空";
			}
			if (!ContentTypeModel::convertActivityId2ActivityName($activity_id))
			{
				return "活动不存在";
			}
			$map['activity_id'] = $activity_id;
			// 获得活动详情页的展示编码
			$ret=ContentTypeModel::getActivityDetailPageFlag();
			if ($ret) {
				$map['page_flag'] = $ret;
			}
			//$map['page_flag'] = ContentTypeModel::getActivityDetailPageFlag();
			if($map['from']=='flexible')
			{
				$map['opera_mark_num'] = '3';
				$map['opera_mark_name'] = '活动';
			}
		} else if ($content_type == 3)
		{
			$feature_id = trim($data['feature_id'][$content_key]);
			if (!$feature_id) {
				return "专题不能为空";
			}
			if (!ContentTypeModel::convertFeatureId2FeatureName($feature_id)) {
				return "专题不存在";
			}
			$map['feature_id'] = $feature_id;
			// 获得专题详情页的展示编码
			$ret=ContentTypeModel::getFeatureDetailPageFlag();
			if ($ret) {
				$map['page_flag'] = $ret;
			}
			//$map['page_flag'] = ContentTypeModel::getFeatureDetailPageFlag();
			if($map['from']=='flexible')
			{
				$map['opera_mark_num'] = '4';
				$map['opera_mark_name'] = '专题';
			}
		} else if ($content_type == 4)
		{
			$page_type = trim($data['page_type'][$content_key]);
			if (!$page_type)
			{
				return "页面不能为空";
			}
			if(strpos($page_type,'bbs_detailpage_')!=0)//判断不是论坛详情页的page_type
			{
				if (!ContentTypeModel::convertPageType2PageName($page_type)) {
					return "页面不存在";
				}
			}
			$map['page_type'] = $page_type;
			// 生成page_flag和page_id值
			$ret = ContentTypeModel::getPageFlagAndIds($page_type);
			if ($ret['page_flag']) {
				$map['page_flag'] = $ret['page_flag'];
			}
			if (!empty($ret['page_ids'])) {
				if (isset($ret['page_ids'][0])) {
					$map['page_id1'] = $ret['page_ids'][0];
					if (isset($ret['page_ids'][1])) {
						$map['page_id2'] = $ret['page_ids'][1];
					}
				}
			}
			//外部推广 选择个人中心  是否弹出随机礼包
			if($page_type=="fixed_personal_center"&&$data['is_pop_random_packs'][$content_key])
			{
				$param_arr=array(
						'is_pop_random_packs' =>trim($data['is_pop_random_packs'][$content_key]),
						'comment'=>'is_pop_random_packs:页面选择个人中心时是否弹出随机礼包,1:是,2:否;',
				);
				$map['parameter_field'] = json_encode($param_arr);
			}
			//文字快捷入口 选择活动列表  活动类型的展示
			elseif($page_type=="otherfixed_activity_list")
			{
				if($data['activity_category_show'][$content_key])
				{
					$active_category_show = trim($data['activity_category_show'][$content_key]);
				}
				else
				{
					$active_category_show = 0;
				}
				if($data['tab_index'][$content_key]){
					$tab_index = $data['tab_index'];
				}else{
					$tab_index = 0;
				}
				$param_arr1=array(
						'activity_category_show' =>$active_category_show,
						'comment'=>'activity_category_show:页面选择活动列表时选择活动类型,0:全部,1:游戏,2:应用;',
						'tab_index' => $tab_index
				);
				$map['parameter_field'] = json_encode($param_arr1);
			}
			//灵活运营样式和文字快捷入口 添加 每日抽奖兑吧
			elseif($page_type=="otherfixed_daily_lottery_exchange"&&$data['exchange_url'][$content_key])
			{
				$param_arr2=array(
						'exchange_url' =>trim($data['exchange_url'][$content_key]),
						'comment'=>'exchange_url:页面选择每日抽奖兑吧，页面的地址',
				);
				$map['parameter_field'] = json_encode($param_arr2);
			}else if(strpos($page_type, 'custom_list_channel_')!== false){
				$param_arr1=array(
						'title' => ContentTypeModel::convertCustomListPageType2channelListPageName($page_type),
						'tab_id' => ContentTypeModel::convertCustomListPageType2custom_listid($page_type),
				);
				$map['parameter_field'] = json_encode($param_arr1);
			}
			//6.5红包任务列表
			elseif($page_type=="fixed_red_packet_task_list")
			{
				if($data['red_task_category_show'][$content_key])
				{
					$red_task_category_show = $data['red_task_category_show'][$content_key];
				}
				else
				{
					$red_task_category_show = 0;
				}
				$param_arr1=array(
						'TAB_INDEX' =>$red_task_category_show,
				);
				$map['parameter_field'] = json_encode($param_arr1);
			}
			//根据配置 保存参数的值
			$method='js'; //加上一个参数来区分是展示导向页面还是获取配置中的参数
			$new_page_name = ContentTypeModel::convertPageType2PageName($page_type,$method);
			if(is_array($new_page_name))
			{
				$param_arr=array();
				$arr_page_name = $new_page_name[0];
				$config_param_arr=$new_page_name[1];
				foreach($config_param_arr as $k =>$v)
				{
					if($v[1]==1)
					{
						$param_arr[$k]=trim($data[$k][$content_key]);
						$param_arr[$k.'_comment']=$k.'_comment:填写内容';
					}
					elseif($v[1]==2)
					{
						$param_arr[$k]=trim($data[$k][$content_key]);
						$str_comment =implode(',',$v[2]);
						$param_arr[$k.'_comment']=$k.'_comment:从1开始'.$str_comment;
					}
				}
				$map['parameter_field'] = json_encode($param_arr);
			}
			//灵活样式
			if($map['from']=='flexible')
			{
				//获取运营标识
				$operate_mark=$data['operate_mark'][$content_key];//返回来的是数字
				$custom_operate_mark=$data['custom_mark'][$content_key];
				if(!$operate_mark&&!$custom_operate_mark)
				{
					return "运营标识必填";
				}
				if($operate_mark)
				{
					//如果有值说明不是自定义
					$map['opera_mark_num']=$operate_mark;
					$map['opera_mark_name'] = ContentTypeModel::convertnum2MarkName($operate_mark);
				}
				else
				{
					$operate_mark=30; //自定义值是30
					//返回自定义的名字
					$custom_operate_mark=$data['custom_mark'][$content_key];
					if(mb_strlen($custom_operate_mark,'utf8')>2)
					{
						return "自定义的标志不能超过两个字";
					}
					else
					{
						//自定义的情况下 保存标识代号保存自定义名字
						$map['opera_mark_num']=$operate_mark;
						$map['opera_mark_name']=$custom_operate_mark;
					}
				}
			}
			//内容合作 添加内容
			if(strpos($page_type,'fixed_content_coop_')===0)
			{
				$page_type_arr = explode('_',$page_type);
				if($page_type_arr[3]==3) //合作详情页
				{
					$model = M();
					if($data['coop_detail_url'][$content_key])//V6.4新增加的 说明是按照标题填写的
					{
						$coop_param_arr=array(
								'coop_site_id' =>trim($data['coop_site_id'][$content_key]),
								'coop_detail_url' =>trim($data['coop_detail_url'][$content_key]),
								'coop_detail_url_id' =>trim($data['coop_detail_url_id'][$content_key]),
								'coop_content_id_new' =>trim($data['coop_content_id_new'][$content_key]),
								'comment'=>'coop_site_id:内容合作：选择的站点id,coop_detail_url:内容合作：选择详情页的详情页地址',
						);
					}
					if($data['coop_detail_label_id'][$content_key]&&$data['coop_detail_pos'][$content_key])//说明是按照标签
					{
						$coop_param_arr=array(
								'coop_site_id' =>trim($data['coop_site_id'][$content_key]),
								'coop_detail_label_id' =>trim($data['coop_detail_label_id'][$content_key]),
								'coop_detail_pos' =>trim($data['coop_detail_pos'][$content_key]),
								'comment'=>'coop_site_id:内容合作：选择的站点id,coop_detail_label_id:内容合作：选择详情页的标签id,coop_detail_pos:详情页选择标签中的位置',
						);
					}
				}
				else//频道和标签
				{
					$coop_param_arr=array(
							'coop_site_id' =>trim($data['coop_site_id'][$content_key]),
							'coop_channel_tag_id' =>trim($data['coop_channel_tag_id'][$content_key]),
							'comment'=>'coop_site_id:内容合作：选择的站点id,coop_channel_tag_id:站点对应的频道id或者标签id',
					);
				}
				$map['parameter_field'] = json_encode($coop_param_arr);
			}
		} else if ($content_type == 5) {
			$website = trim($data['website'][$content_key]);
			if (!$website) {
				return "网页不能为空";
			}
			if (!ContentTypeModel::check_url($website))
				return "请输入正确的url地址";
			$map['website'] = $website;
			$website_open_type = trim($data['website_open_type'][$content_key]);
			$map['website_open_type'] = $website_open_type;
			$ret=ContentTypeModel::getWebsitePageFlag();
			if ($ret) {
				$map['page_flag'] = $ret;
			}
			//$map['page_flag'] = ContentTypeModel::getWebsitePageFlag();
			//灵活样式
			if($map['from']=='flexible')
			{
				//获取运营标识
				$operate_mark=$data['operate_mark'][$content_key];//返回来的是数字
				$custom_operate_mark=$data['custom_mark'][$content_key];
				if(!$operate_mark&&!$custom_operate_mark)
				{
					return "运营标识必填";
				}
				if($operate_mark)
				{
					//如果有值说明不是自定义
					$map['opera_mark_num']=$operate_mark;
					$map['opera_mark_name'] = ContentTypeModel::convertnum2MarkName($operate_mark);
				}
				else
				{
					$operate_mark=30; //自定义值是30
					//返回自定义的名字
					$custom_operate_mark=$data['custom_mark'][$content_key];
					if(mb_strlen($custom_operate_mark,'utf8')>2)
					{
						return "自定义的标志不能超过两个字";
					}
					else
					{
						//自定义的情况下 保存标识代号保存自定义名字
						$map['opera_mark_num']=$operate_mark;
						$map['opera_mark_name']=$custom_operate_mark;
					}
				}
			}
		}else if ($content_type == 6) {
			$page_type = trim($data['page_type'][$content_key]);
			if (!$page_type)
			{
				return "页面不能为空";
			}
			$map['page_type'] = $page_type;
				
			$gift_id = trim($data['gift_id'][$content_key]);
			if (!$gift_id)
			{
				return "礼包ID不能为空";
			}
			if (!ContentTypeModel::check_gift_id($gift_id))
				return "请输入正确的礼包ID";
			$map['gift_id'] = $gift_id;
			$ret=ContentTypeModel::getGiftDetailPageFlag();
			if ($ret) {
				$map['page_flag'] = $ret;
			}
			//$map['page_flag'] = ContentTypeModel::getGiftDetailPageFlag();
			if($map['from']=='flexible')
			{
				$map['opera_mark_num'] = '8';
				$map['opera_mark_name'] = '礼包';
			}
		}else if ($content_type == 7)
		{
			$page_type = trim($data['page_type'][$content_key]);
			if (!$page_type)
			{
				return "页面不能为空";
			}
			$map['page_type'] = $page_type;
				
			$stragety_id = trim($data['strategy_id'][$content_key]);
			if (!$stragety_id)
			{
				return "攻略ID不能为空";
			}
			if (!ContentTypeModel::check_strategy_id($stragety_id))
				return "请输入正确的攻略ID";
			$map['strategy_id'] = $stragety_id;
			$ret=ContentTypeModel::getStrategyDetailPageFlag();
			if ($ret) {
				$map['page_flag'] = $ret;
			}
			//$map['page_flag'] = ContentTypeModel::getStrategyDetailPageFlag();
			if($map['from']=='flexible')
			{
				$map['opera_mark_num'] = '9';
				$map['opera_mark_name'] = '攻略';
			}
		}else if ($content_type == 8)
		{
			$order_id = trim($data['recommend_order_id'][$content_key]);
			if (!$order_id)
			{
				return "预约名称不能为空";
			}
			if (!ContentTypeModel::convertOrderId2OrderName($order_id))
			{
				return "预约名称不存在";
			}
			$map['activity_id'] = $order_id;
			// 获得活动详情页的展示编码
			/*$ret=ContentTypeModel::getActivityDetailPageFlag();
				if ($ret) {
			$map['page_flag'] = $ret;
			}*/
			//$map['page_flag'] = ContentTypeModel::getActivityDetailPageFlag();
			if($map['from']=='flexible')
			{
				$map['opera_mark_num'] = '2';
				$map['opera_mark_name'] = '推荐';
			}
		}else if($content_type == 9) {
			$model = M();
			$used_id = (Int)$data['used_id'][$content_key];
			if( !$used_id ) {
				return "该软件未有符合条件的应用预览的内容";
			}
			$where = array(
					'id' => $used_id,
			);
			$used_info = $model->table('sj_soft_content_explicit')->where($where)->find();
			$map['parameter_field'] = json_encode(array('used_id'=>$used_id,'package'=>$used_info['package'],'softname'=>$used_info['softname'],'title'=>$used_info['title'],'page_type'=>'fixed_content_explicit'));
		}else if($content_type == 10) {
			$order_id = trim($data['recommend_order_id'][$content_key]);
			if (!$order_id)
			{
				return "预约名称不能为空";
			}
			if (!ContentTypeModel::convertOrderId2OrderName($order_id))
			{
				return "预约不存在";
			}
			$map['activity_id'] = $order_id;
			if($map['from']=='flexible')
			{
				$map['opera_mark_num'] = '2';
				$map['opera_mark_name'] = '推荐';
			}
		}else {
			return "类型错误";
		}
		
		if($map['from'])
		{
			unset($map['from']);
		}
		return true;
	}
	
	//内容合作  内容获取
	function getCoopChannel($type)
	{
		$model = new Model();
		//合作站点和合作频道 展示
		if(is_array($type)){
			$coop_sql="SELECT a.*, b.website_name, b.anzhi_name FROM coop_channel AS a INNER JOIN coop_site AS b ON a.site_id=b.id AND a.del=0 and a.status=b.status=1 and a.type!=".$type['type'].";";
		}else{
			if($type)
			{
				$coop_sql="SELECT a.*, b.website_name, b.anzhi_name FROM coop_channel AS a INNER JOIN coop_site AS b ON a.site_id=b.id AND a.del=0 and a.status=b.status=1 and a.type=".$type.";";
			}
			else
			{
				$coop_sql="SELECT a.*, b.website_name, b.anzhi_name FROM coop_channel AS a INNER JOIN coop_site AS b ON a.site_id=b.id AND a.del=0 and a.status=b.status=1;";
			}
		}
		$all_channel_site = $model->query($coop_sql);
		$coop_new_arr=array();
		foreach($all_channel_site as $key => $ch_val)
		{
			$coop_new_arr[$key]['coop_key_val']='coop_'.$ch_val['site_id']."_".$ch_val['id'];
			$coop_new_arr[$key]['new_channel_name'] = $ch_val['website_name']."_".$ch_val['channel_name'];
		}
		return $coop_new_arr;
	}
	//内容合作  站点获取
	function getCoopSite()
	{
		$model = new Model();
		//合作站点和合作频道 展示
		$where=array(
			'status'=>1,
		);
		$site_result =$model->table('coop_site')->where($where)->select();
		return $site_result;
	}
	 public function getDetailTitlelike($like_title_name, $site_id, $offset=0, $size=10)
    {
		$model = new Model();
        $map['title'] = array('like','%'.$like_title_name.'%');
		$map['site_id'] = $site_id;
		$map['status'] = 1;
		$map['az_status'] = 1;
        $rs = $model->table('coop_content')->field('id,url,title,content_id_new')->where($map)->order('create_tm desc')->limit("$offset,$size")->select();
        return $rs;
    }

	function getCustomLIstChannel(){
		$model = new Model();
		//V6.4.8 隐藏外投频道，此频道为自动数据不展示在后台
		$rs = $model->table('sj_custom_list_channel')->field('id,channel_name')->where('status=1 and id != 27')->select();
		return $rs;
	}

	function getCustomListCategory($channelId,$type=''){
		$model = new Model();
		$rs = $model->table('sj_custom_list_name')->field('id,name')->where(array('status'=>1,'channel_id'=>$channelId))->select();
		if(!$type){
			$return = array("customlist1_{$channelId}_"=>'全部');
		}else{
			$return = array();
		}

		if($rs){
			foreach($rs as $k=>$v){
				$n_k = "customlist1_{$channelId}_{$v['id']}";
				if($type == 1){
					$return[] = $n_k;
				}else{
					$return[$n_k] = $v['name'];
				}

			}
		}
		return $return;
	}

	function getCustomListRec(){
		$model = new Model();
		$rs = $model->table('sj_custom_list_name')->field('id,name')->where(array('status'=>1,'data_type'=>2))->select();
		$return = array(''=>'全部');
		if($rs){
			foreach($rs as $k=>$v){
				$n_k = "customlist_{$v['id']}";
				$return[$n_k] = $v['name'];
			}
		}
		return $return;
	}

	/*****************************************************************
	 * @desc v6.4.6新增类型，数组中[0]表示类型名称，[1]表示在哪个频道显示,没有表示所有频道都显示  [2]表示再哪个频道不显示
	 * @desc v6.4.6 更新需求：33搜索推荐内容删除
	 * @return array
	 */
	public static function getExtentType(){
		$extentTypeArr = array(
			31 => array('banner+软件'),
			32 => array('多排-专题/页面（自动取软件）',self::$search_result_page),
			//33 => array('搜索推荐内容',self::$search_result_page)
			34 => array('原生广告','fixed_homepage_recommend'),
			35 => array('多排（关注游戏）',''),
			36 => array('多排（关注论坛版块）',''),
			37 => array('活动卡片',''),
			38 => array('多行软件',''),
			39 => array('论坛内容','fixed_homepage_recommend'),
			40 => array('大图论坛内容')
		);
		return $extentTypeArr;
	}

	/*****************************************************************
	 * @desc sdk5.0  控制推荐内容显示类型
	 * @param $belong_page_type
	 * @return string
	 */
	public static  function get_final_content_type($belong_page_type){
		$final_content_type = '';
		if($belong_page_type == self::$exclusive){
			//独家
			$final_content_type = '1,2,5,6,7,9';
		}
		return $final_content_type;
	}

	public  function get_activity($data){
		$model = new Model();
		$time = time();
		$where = array(
			'status' => 1,
			'start_tm' => array('exp'," <= {$time}"),
			'end_tm' => array('exp'," >= {$time}")
		);
		if(isset($data['id'])){
			if(!is_array($data['id'])){
				$where['id'] = $data['id'];
				$where['start_tm'] =  array('exp'," <= {$time}");
				$where['end_tm'] =  array('exp'," >= {$time}");
			}else{
				$ids = implode("','",$data['id']);
				$where['id'] = array('exp', " in('{$ids}')");
			}
		}
		$res = $model->table('sj_activity')->where($where)->field('id,name,start_tm,end_tm')->select();
		if(!is_array($data['id'])){
			return $res[0];
		}else{
			$return = array();
			foreach($res as $k=>$v){
				$return[$v['id']] = $v;
			}
			return $return;
		}

	}

	//活动卡片
	public function chk_flexible_soft_data_37($data, &$map){
		$activity_id = $data['activity_id'];
		if(!$activity_id){
			return '活动ID不存在';
		}
		$activity = ContentTypeModel::get_activity(array('id'=>$activity_id));
		if(!$activity) return '活动不存在';
	}
}
?>
