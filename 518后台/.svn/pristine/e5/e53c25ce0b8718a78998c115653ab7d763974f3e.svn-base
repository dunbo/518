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
    'TMPL_CACHE_TIME'		=>	-1,         // 模板缓存有效期 -1 为永久，(以数字为值，单位:秒)
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
          'hostname' => '192.168.1.170',
          'hostport' => '3306',
          'database' => 'gostatics',
    ),
    'DB_SLAVE_BASE'    => array(       //用于统计数据的数据库连接配置
          'dbms' => 'mysql',
          'username' => 'azmk',
          'password' => 'mkaz)#@)2012pwd',
          'hostname' => '192.168.1.125',
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
    'APP_DEBUG'				=> false,	// 是否开启调试模式
    'APP_DOMAIN_DEPLOY'     => false,   // 是否使用独立域名部署项目
    'APP_PLUGIN_ON'         => false,   // 是否开启插件机制
    'APP_GROUP_DEPR'        => '.',     // 模块分组之间的分割符
    'APP_GROUP_LIST'        => 'Admin,Sj,Partner,Webmarket,School,Coop,Caiji,Sl',      // 项目分组设定,多个组之间用逗号分隔,例如'Home,Admin'
    

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
    'COOKIE_EXPIRE'         => 3600,    // Coodie有效期
    'COOKIE_DOMAIN'         => 'newadmin.goapk.com',      // Cookie有效域名
    'COOKIE_PATH'           => '/',     // Cookie路径
    'COOKIE_PREFIX'         => '',      // Cookie前缀 避免冲突


	'task_server' => 'sph.goapk.com',//gearman 服务器配置
	'task_port' => '4730',
	'REDIS_HOST'                        => '192.168.1.13',
    'REDIS_PORT'                        => 6379,
    'DATA_CACHE_TIME'                    => 3600,
);
?>
