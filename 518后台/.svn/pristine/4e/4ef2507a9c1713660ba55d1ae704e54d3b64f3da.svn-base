<?php
return array(
	/* 默认设定 */
    'DEFAULT_APP'           => '@',     // 默认项目名称，@表示当前项目
    'DEFAULT_GROUP'         => 'Home',  // 默认分组
    'DEFAULT_MODULE'        => 'Index', // 默认模块名称
    'DEFAULT_ACTION'        => 'index', // 默认操作名称
    'DEFAULT_CHARSET'       => 'utf-8', // 默认输出编码
    'DEFAULT_TIMEZONE'      => 'PRC',	// 默认时区
    'DEFAULT_AJAX_RETURN'   => 'JSON',  // 默认AJAX 数据返回格式,可选JSON XML ...
    'DEFAULT_THEME'    => 'default',	// 默认模板主题名称
    'DEFAULT_LANG'          => 'zh-cn', // 默认语言

    /* 模板引擎设置 */
    'TMPL_ENGINE_TYPE'		=> 'Think',     // 默认模板引擎 以下设置仅对使用Think模板引擎有效
    'TMPL_DETECT_THEME'     => false,       // 自动侦测模板主题
    'TMPL_TEMPLATE_SUFFIX'  => '.html',     // 默认模板文件后缀
    'TMPL_CONTENT_TYPE'    =>'text/html', // 默认模板输出类型
    'TMPL_CACHFILE_SUFFIX'  => '.php',      // 默认模板缓存后缀
    'TMPL_DENY_FUNC_LIST'	=> 'echo,exit',	// 模板引擎禁用函数
    'TMPL_PARSE_STRING'     => '',          // 模板引擎要自动替换的字符串，必须是数组形式。
    'TMPL_L_DELIM'          => '{',			// 模板引擎普通标签开始标记
    'TMPL_R_DELIM'          => '}',			// 模板引擎普通标签结束标记
    'TMPL_VAR_IDENTIFY'     => 'array',     // 模板变量识别。留空自动判断,参数为'obj'则表示对象
    'TMPL_STRIP_SPACE'      => false,       // 是否去除模板文件里面的html空格与换行
    'TMPL_CACHE_ON'			=> true,        // 是否开启模板编译缓存,设为false则每次都会重新编译
    'TMPL_CACHE_TIME'		=>	600,         // 模板缓存有效期 -1 为永久，(以数字为值，单位:秒)
    'TMPL_ACTION_ERROR'     => 'Public:error', // 默认错误跳转对应的模板文件
    'TMPL_ACTION_SUCCESS'   => 'Public:success', // 默认成功跳转对应的模板文件
    'TMPL_TRACE_FILE'       => THINK_PATH.'/Tpl/PageTrace.tpl.php',     // 页面Trace的模板文件
    'TMPL_EXCEPTION_FILE'   => THINK_PATH.'/Tpl/ThinkException.tpl.php',// 异常页面的模板文件
    'TMPL_FILE_DEPR'=>'/', //模板文件MODULE_NAME与ACTION_NAME之间的分割符，只对项目分组部署有效
	
	//'配置项'=>'配置值'

    /* 数据库设置 */
    'DB_TYPE'               => 'mysql',     // 数据库类型
	'DB_HOST'               => 'dbm.goapk.com', // 服务器地址
	'DB_NAME'               => 'newgomarket',          // 数据库名
	'DB_PORT'               => 3306,        // 端口
	'DB_PREFIX'             => 'sj_',    // 数据库表前缀
	'DB_SUFFIX'             => '',          // 数据库表后缀
    'DB_FIELDTYPE_CHECK'    => false,       // 是否进行字段类型检查
    'DB_FIELDS_CACHE'       => false,        // 启用字段缓存
    'DB_CHARSET'            => 'utf8',      // 数据库编码默认采用utf8
    'DB_DEPLOY_TYPE'        => 0, // 数据库部署方式:0 集中式(单一服务器),1 分布式(主从服务器)
    'DB_RW_SEPARATE'        => false,       // 数据库读写是否分离 主从式有效
    'DB_STATICS_CONNECT'    => array(       //用于统计数据的数据库连接配置
		'dbms' => 'mysql',
		'username' => 'azmk',
		'password' => 'mkaz)#@)2012pwd',
		'hostname' => '192.168.1.243',
		'hostport' => '3306',
		'database' => 'gostatics',
    ),
    'DB_SLAVE_BASE'    => array(       //用于统计数据的数据库连接配置
		'dbms' => 'mysql',
		'username' => 'azmk',
		'password' => 'mkaz)#@)2012pwd',
		'hostname' => '192.168.1.243',
		'hostport' => '3306',
		'database' => 'newgomarket',
    ),
	
	'DB_STAT_BASE' => array(				//新统计库数据配置
		'dbms'=> 'mysql',
        'username' => 'azmk',
        'password' => 'mkaz)#@)2012pwd',
        'hostname' => '192.168.1.243',
        'hostport' => '3306',
		'database' => 'gostats'
	),

    'DB_COOPERATIVE' => array(              //渠道合作数据库
        'dbms'=> 'mysql',
        'username' => 'azmk',
        'password' => 'mkaz)#@)2012pwd',
        'hostname' => '192.168.1.243',
        'hostport' => '3306',
        'database' => 'cooperative_operations'
	),

	'DB_CO_CHANNEL' => array(
		'dbms'     => 'mysql',
        'username' => 'azmk',
        'password' => 'mkaz)#@)2012pwd',
        'hostname' => '192.168.1.243',
        'hostport' => '3306',
		'database' => 'channel_cooperation'
	),
    'DB_CO_SENDNUM' => array(
        'dbms'     => 'mysql',
        'username' => 'azmk',
        'password' => 'mkaz)#@)2012pwd',
        'hostname' => '192.168.1.243',
        'hostport' => '3306',
        'database' => 'sendNum'
    ),
	'DB_CHANNEL_COEFFICIENT' => array(
		'dbms'     => 'mysql',
        'username' => 'azmk',
        'password' => 'mkaz)#@)2012pwd',
        'hostname' => '192.168.1.243',
        'hostport' => '3306',
		'database' => 'gostats'
	),
    'DB_MARKETLOG' => array(
        'dbms'     => 'mysql',
        'username' => 'azmk',
        'password' => 'mkaz)#@)2012pwd',
        'hostname' => '192.168.1.243',
        'hostport' => '3306',
        'database' => 'marketlog'
	),
	'DB_SETTLEMENT' => array(
		'dbms' => 'mysql',
        'username' => 'azmk',
        'password' => 'mkaz)#@)2012pwd',
        'hostname' => '192.168.1.243',
        'hostport' => '3306',
		'database' => 'settlement'
	),
	'DB_CAIJI' => array(
		'dbms'=> 'mysql',
        'username' => 'azmk',
        'password' => 'mkaz)#@)2012pwd',
        'hostname' => '192.168.1.243',
        'hostport' => '3306',
		'database' => 'caiji'
	),
    'DB_YW' => array(
        'dbms'=> 'mysql',
        'username' => 'inouser',
        'password' => 'aZ@)!%inotify!@15',
        'hostname' => '172.16.1.243',
        'hostport' => '3306',
        'database' => 'inotify'
	),
	'DB_BBS' => array(//门户页数据库
		'dbms'     => 'mysql',
		'username' => 'bbSZYoO',
		'password' => 'wW35^b0%S^w!ZYo3O',
		'hostname' => 'dbm.zhiyoo.com',
		'hostport' => '3307',
		'database' => 'bbs_app_pc'
	),
	'DB_PUSH'    => array(       //用于统计数据的数据库连接配置
		'dbms' => 'mysql',
		'username' => 'azmk',
		'password' => 'mkaz)#@)2012pwd',
		'hostname' => '192.168.1.243',
		'hostport' => '3306',
		'database' => 'newgomarket',
    ),

     /* 错误设置 */
    'ERROR_MESSAGE' => '您浏览的页面暂时发生了错误！请稍后再试～',//错误显示信息,非调试模式有效
    'ERROR_PAGE'    => '',	// 错误定向页面
	'URL_CASE_INSENSITIVE'  => false,   // URL地址是否不区分大小写

    'TOKEN_ON'               =>   false,     // 开启令牌验证
    'TOKEN_NAME'             =>   '__hash__',    // 令牌验证的表单隐藏字段名称
    'TOKEN_TYPE'             =>    'md5',   // 令牌验证哈希规则
    'TAG_NESTED_LEVEL'		=> 5,    // 标签嵌套级别

    /* 运行时间设置 */
    'SHOW_RUN_TIME'			=> false,   // 运行时间显示
    'SHOW_ADV_TIME'			=> false,   // 显示详细的运行时间
    'SHOW_DB_TIMES'			=> false,   // 显示数据库查询和写入次数
    'SHOW_CACHE_TIMES'		=> false,   // 显示缓存操作次数
    'SHOW_USE_MEM'			=> false,   // 显示内存开销
    'SHOW_PAGE_TRACE'		=> false,   // 显示页面Trace信息 由Trace文件定义和Action操作赋值
    'SHOW_ERROR_MSG'        => false,    // 显示错误信息
    'DEFAULT_TIMEZONE'      => 'PRC',	// 默认时区
    /* 日志设置 */
    'LOG_EXCEPTION_RECORD'  => true,    // 是否记录异常信息日志(默认为开启状态)
    'LOG_RECORD'            => true,   // 默认不记录日志
    'LOG_FILE_SIZE'         => 2097152,	// 日志文件大小限制
    'LOG_RECORD_LEVEL'      => array('EMERG','ALERT','CRIT','ERR'),// 允许记录的日志级别
    'WEB_LOG_RECORD'        => true,//是否开启日志记录

    /* 项目设定 */
    'APP_DEBUG'				=> true,	// 是否开启调试模式
    'APP_DOMAIN_DEPLOY'     => false,   // 是否使用独立域名部署项目
    'APP_PLUGIN_ON'         => false,   // 是否开启插件机制
    'APP_GROUP_DEPR'        => '.',     // 模块分组之间的分割符
	'APP_GROUP_LIST'        => 'Admin,Sj,Partner,Webmarket,School,Coop,Caiji,Sl,mobile,Dev,Sendnum,Cooperate,Cooperative,Appquality,Channel_cooperation,Demo,Search,Settlement,Bbs_manage,operation,Zhiyoo',      // 项目分组设定,多个组之间用逗号分隔,例如'Home,Admin'

     /* SESSION设置 */
    'SESSION_AUTO_START'    => true,    // 是否自动开启Session

    /* 静态缓存设置 */
    'HTML_CACHE_ON'			=>true,   // 默认关闭静态缓存
    'HTML_CACHE_TIME'		=> 60,      // 静态缓存有效期
    'HTML_READ_TYPE'        => 0,       // 静态缓存读取方式 0 readfile 1 redirect
    'HTML_FILE_SUFFIX'      => '.shtml',// 默认静态文件后缀

    /* 语言设置 */
    'LANG_SWITCH_ON'        => false,   // 默认关闭多语言包功能
    'LANG_AUTO_DETECT'      => false,   // 自动侦测语言 开启多语言功能后有效

    /* 数据缓存设置 */
    'DATA_CACHE_TIME'		=> 60,      // 数据缓存有效期
    'DATA_CACHE_COMPRESS'   => false,   // 数据缓存是否压缩缓存
    'DATA_CACHE_CHECK'		=> false,   // 数据缓存是否校验缓存
    'DATA_CACHE_TYPE'		=> 'File',  // 数据缓存类型,支持:File|Db|Apc|Memcache|Shmop|Sqlite| Xcache|Apachenote|Eaccelerator
    'DATA_CACHE_PATH'       => TEMP_PATH,// 缓存路径设置 (仅对File方式缓存有效)
    'DATA_CACHE_SUBDIR'		=> false,    // 使用子目录缓存 (自动根据缓存标识的哈希创建子目录)
    'DATA_PATH_LEVEL'       => 1,        // 子目录缓存级别

    /* Cookie设置 */
    'COOKIE_EXPIRE'         => 7200,    // Coodie有效期
    'COOKIE_DOMAIN'         => '518.anzhi.com',      // Cookie有效域名
    'COOKIE_PATH'           => '/',     // Cookie路径
    'COOKIE_PREFIX'         => '',      // Cookie前缀 避免冲突


	'task_server' => 'gm.goapk.com',//gearman 服务器配置
	'task_port' => '4730',
    'gift_task_server' => '192.168.3.18',
    'gift_task_port' => '4730',
	'REDIS_HOST'                        => '192.168.1.242',
    'REDIS_PORT'                        => 6379,
    'DATA_CACHE_TIME'                    => 3600,

	'authentication_system' => array('LG TV市场' => 1), //定义平台认证 类别
	'BAOBIAO_PATH' => '/data/att/admin_baobiao/',//报表项目上传路径
	'MAIL_ADDRESS'=>'alarm@anzhi.com', // 邮箱地址
	'MAIL_SMTP'=>'smtp.qq.com', // 邮箱SMTP服务器
	'MAIL_LOGINNAME'=>'alarm@anzhi.com', // 邮箱登录帐号
	'MAIL_PASSWORD'=>'123456anzhi', // 邮箱密码
	
	'ADLIST_PATH' => '/data/att/518/adlist/', // 广告位列表项目路径
	'ZJBB_FEATURE_ID' => 31, //装机必备专题id
	'channel_url' => 'package-api.anzhi.com',

	//搜索获取第一个软件信息存放地址  added by shitingting
	'SEARCH_FIRST_SOFT' => '/data/att/518/search_first_soft',

	//PUSH、弹框和被动预下载上传CSV文件保存路径
	'MARKET_PUSH_CSV' => '/data/att/518/push_file',
	
	//渠道合作运营
	'COOPERATION_FILE' => '/data/att/518/cooperation_contract',
	'COOPERATION_TIMP_FILE' => '/tmp/channel_cooperation',
	'AD_FILE' => '/data/att/ad_file',
	'AD_TMP_FILE' => '/data/att/ad_file/ad_tmp_file',
	'AD_MODEL_FILE' => '/data/att/ad_file/ad_model',
	//扩展配置文件，只需要在项目的配置文件里面增加额外的配置文件名称即可，例如：
	'APP_CONFIG_LIST' => array('config_txt','proofread'),
    'sectiontype' => array(1 => '产品',2 => '游戏运营', 3 => 'cp审核', 4 => '市场编辑',5 => '技术',0=>'已删除'),
    'sectiontype_msg' => array(
		1 => '功能建议',
		2 => '游戏充值类、游戏的账号密码类、游戏更新、游戏使用咨询',
		3 => '质量（广告、病毒、盗版、山寨）；具体应用无法下载/安装/启动/搜不到等问题；电子书咨询',
		4 => '抓包、更新、中奖、专题',
		5 => '没有明确说明具体软件的无法下载/安装/启动/下载慢/闪退/死机等问题',
		6 => '暂无',
		7 => '英文、特殊字符等无实际内容'
	),
    'feedbacktype' => array(1 => '下载问题',2 => '安装问题',3 => '软件使用问题',4 => '其它问题',5 => '建议意见'),
    'son_authority' => array(
		'soft'=>array('app' => '创建应用', 'game' => '创建游戏','sdk'=>'接入sdk', 'gift' => '发布礼包', 'server' => '发布新服', 'debut' => '申请首发', 'screen' => '申请闪屏'),
		'finance'=>array('sale' => '销售汇总', 'order' => '订单查询', 'income' => '收入统计', 'selfservice' => '自助结算'),
		'user_data'=>array('recomment' => '用户评论', 'feedback' => '用户反馈','usercount' => '用户统计'),
		'info_manage'=>array('dev_info' => '开发者资料')
    ),
    'allow_multi_session' => true,//允许后台同一个账号多个用户登录，默认为false
    'SDK_on_off' => false,//开关SDK测试列表操作，默认为false
    'MC_PORT'   => 11211,        // MC端口
    'MC_HOST'   => '192.168.1.18',
    'newgame_id'   => '21',//网络游戏category_id
    'single_productFuns' => '1,2', //单机接入产品功能
    'other_productFuns' => '2',  //网游，棋牌接入产品功能
    'card_payTypes' => '1,4,6,8', //棋牌接入支付功能
    'single_payTypes' => '7', //单机接入支付功能
    'web_payTypes' => '1,4,6,8',//网游接入支付功能
	'reg_url'=>'http://i.anzhi.com',
   'appfrom'=>array('豌豆荚','乐商店','拇指玩','酷安','木蚂蚁','优亿','N多','百度api','应用宝','搜索失败'),
   'query_user' =>'http://i.anzhi.com/api/account/queryUser',
    'online_query_user' =>'http://i.anzhi.com/api/account/queryUser',
    'private_key' =>'lW0Akgr25Q5d8e91ym781L32',
    'online_private_key' =>'9sm32Kv4enKJ6jasEeiXCaB5',
	'appcert_status'=>array('1'=>'通过','2'=>'审核中','3'=>'不通过','4'=>'无法认证'), //app认证状态
    '30_edit_user_password' => false,//开关30天修改密码，默认为false
    'type_30' => false,//开关30天强制修改密码，默认为false
	'feature_id' => 288,//首发专题id
    'feed_url' =>'http://dev.i.anzhi.com:9011',//反馈渠道
    'userfeedback'=>'/service/userfeedback',
	'co_group'=>array('0'=>'未选择','1'=>'厂商','2'=>'捆绑','3'=>'门店','4'=>'平板','5'=>'刷机','6'=>'网盟','7'=>'线上合作'),//渠道类型
    'ad_type'=>array('1'=>'闪屏广告','2'=>'弹窗广告1','3'=>'弹窗广告2','4'=>'弹窗广告3','5'=>'banner广告','6'=>'文字链接'),//通用sdk广告类型
    'ad_content_type'=>array('1'=>'网页','2'=>'软件'),//通用sdk广告数据类型
    'ad_sdk_img'=>'/data2/img',//通用sdk广告图片上传路径
    'banner_type'=>array('1'=>'1','2'=>'2','3'=>'3'),//banner类型
    'download_flag'=>array('1'=>'点击广告开始下载','2'=>'弹出广告自动下载','3'=>'后台静默下载完成后弹出广告','5'=>'使用安智市场下载软件'),//下载方式
	'agreement_path'=> 'http://9.newdev.anzhi.com/doc/',//合同下载路径
    'only_search_devid' =>array(214170,6589057,5264673),//软件仅搜索devid   
	'sdk_3des_key'=>'eeUu5p6XElQbYGM26iCIOmo2', //sdk网游分渠道管理加密key
	'sdk_app_key'=>'142605894293bjc9VR9P3Xqv7jFTgh', //sdk网游分渠道管理appkey
	'sdk_host'=> 'http://42.62.70.157:9511', //sdk网游分渠道管理host 伪线网地址
	'updateChannel'=>'/game/sdkChannel/Cfgchannel/updateChannel', //修改渠道
	'addChannel'=>'/game/sdkChannel/Cfgchannel/addChannel', //新增渠道
	'addRelation'=>'/game/sdkChannel/CfgChannelGame/addRelation',//新增渠道游戏关系
	'updateRelation'=>'/game/sdkChannel/CfgChannelGame/updateRelation',//修改渠道游戏关系
	'ucenter' => array(
	//	'uri' => 'http://i.anzhi.com/',
		'uri' => 'http://dev.i.anzhi.com/',
	//	'task_uri' => 'http://tmsdev.anzhi.com/',
		'task_uri' => 'http://dev.task.anzhi.com/',
		'task_version' => 'v6',
		'imguri' => 'http://image.anzhi.com/header',
		'serviceId' => '007',
		'client_serviceId' => '014',
		'serviceVersion' => 'V5.3',
	//	'privatekey' => 'Ad5H2kP3ItBIqyWSMO224DA1',
	//	'client_privatekey' => 'f6URPVzU1CeX95k09kvzwsjc',
		'privatekey' => 'SWbqAzj182isUcUm4CF9h10k',
		'client_privatekey' => 'c01qUofs1y71L6Uzl61xVPj7',
	//	'task_privatekey' => 'Ad5H2kP3ItBIqyWSMO224DA1',
	//	'task_client_privatekey' => 'f6URPVzU1CeX95k09kvzwsjc',
		'task_privatekey' => 'SWbqAzj182isUcUm4CF9h10k',
		'task_client_privatekey' => 'c01qUofs1y71L6Uzl61xVPj7',
		'appchannel' => 'c01qUofs1y71L6Uzl61xVPj7',
	),	
	'ucenter_api' => array(
		'prefix'=>'task',
		'apiname'=>'/api/tms/data/notice',
		'passthrough'=>true
	),
	'sdk_game_sign_url'=>'/data3/sdk_game_sign/',
	'general_channel_id'=>'1', //通用渠道id

    //PUSH、弹框和被动预下载上传CSV文件保存路径
    'MARKET_PUSH_CSV' => '/data/att/518/push_file',
    'ACTIVITY_CSV' => '/data/att/518/activity',
    'GIFT_REDIS_HOST'      => '192.168.1.242',
    'GIFT_REDIS_PORT'                => 6379,	
	'LOTTERY_REDIS_HOST' => '192.168.1.242',
	'LOTTERY_REDIS_PORT'                => 6379,	
);
?>
