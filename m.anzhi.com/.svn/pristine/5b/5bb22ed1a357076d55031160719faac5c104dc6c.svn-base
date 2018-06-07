<?php
//include_once ("../../../stdafx.php");
//ini_set("display_errors", true);
//error_reporting(E_ALL);
include dirname(__FILE__).'/../../newgomarket.goapk.com/init.php';
include_once ("../../data/config_plus.php");
include_once ("./function.php");
include_once (dirname(__FILE__).'/../../www.anzhi.com/function.php');
//ini_set('display_errors', 1);
ini_set("display_errors", 0);
ini_set("memory_limit", "512M");
error_reporting(0);
/**
 * Dispatch class
 */
class Api
{
	public $params = null;
	public $action_arr = array(
        'showsoftlist',
		'showcatagory',
		'softintro',
		'commentlist',
		'thumblist',
		'mybelike',
		'download',
		'search',
		'otherdev',
		'getchange',
		'showfulllist',
//        'wrapper/qixiang/init',
//        'wrapper/qixiang/change',
        'softalllist',
		'softupdatelist',
		'launcher',
		'getpermission',
		'ebooktop',
        'getTypeTop',
        'getPackageType',
        'softlist163',
		'mxlist',
		'adlist',
		'adinfo',
		'addownload',
		'softlist_baidu',
		'infoByPackage',
		);
	public $channel_arr = array(
		'f8698628bc993a89172d1e22c971cff17989f962', //号码百事通应用渠道号
//		'6b624ae40826106689b51d1ddef4a8efcd4fb44e', //台湾电信
//		'3697a396063ce5d588720f2ed3029f766821b736', //梆梆分享
		'fd813da0e82fc130ce6a284793f02c2b57ca3073', //海信
//		'a766b170dcc5b0b5ef0c72e8443cf89fb82953d7', //奇享博迅
//		'6e2f80bbd420c7577e3e602c1ad63e5ce0328cba', //金立接口合作
		//'wandoujia', //豌豆夹
//		'2aa2b5c2b6cb4772bee74f05d86a159bfabfc092', //盛大创新院－机越网
		//'8bd0770f437d84f87a9d85d46c12c3ab7b7aa628', //步步高服务器接口合作
		'53ed65e7138876d81633fdedd1a65579e18304e6', //丰华合瑞
		//'5c9f4dd7f9b2c34546edac036ba967b3caeb8ff9',//文和时代
        //'4427299997dc303978b6aa88871df6108652ff87',//华录休闲游戏频道
        '2331490ce634512bf435d5bf73ed4e938a829c73',//中兴
        '6301ddaf1890e6925e65ed5dc30556a42a7bbc09',//LG_TV项目搜索接口合作，不算在第三番接口合作
        'a751b949643a178893d3e4ba5e35344a5027b289',//XDA-WEB
        'd1cb53da4f7f5401148c55625f3a475c43094ad7',//苏宁(XDA)
        '536acbc6e6182bb1ada29264c9ad03bbb29f83ee',//天语搜索
		'a4cbf188b6e7f9659415d52414a4262df2e7148e',//tcl渠道
        'b0497fd211915abd4e472593c4ef4b8cab72f99a',//华豚渠道
        '187e82988529ed0ec5e92a203b067e9402d33f5b',//万能手机助手合作
        '163cps',//自定义的163接口
		'e14e68f61a0f47de6bcce72b2d50aa216adab7b7',//魔秀桌面
		'b506a44240aecf039d42f802ec295df44e05d9c0',//安智外部补量-嘉楠
		'bdhk',
        'gdt',
        'jrtt',
        'shenmasem',//神马SEM动态落地页接口对接
	);
	public function getClassByAct()
	{
		$this->chlAdapter();
		if (in_array($this->params['action'], $this->action_arr))
		{
			if (is_file($this->params['action'] . ".php"))
			{
				include("./".$this->params['action'] . ".php");

			}else{
				header("HTTP/1.1 404 Not Found");
				header("Status: 404 Not Found");
				exit;
			}
		}else{
			header("HTTP/1.1 417 Expectation Failed");
            exit;
		}
	}

	public function getData()
	{
		$data = "";
        if(in_array($this -> params['channel'],$this -> channel_arr)){
			$class = $this->params['action'];
            $class = preg_replace('/\//', '_', $class);
            $datacls = new $class($this->params);
            $data = $datacls->getData();
        }else{
        	header("HTTP/1.1 403 Forbidden");
            exit;
        }

        if($data=="[]"){
        	header("HTTP/1.1 204 No Content");
            exit;
        }
		$ip = onlineip();
		permanentlog('third_partner_access.log',json_encode(array(
			'action' => $this -> params['action'],
			'channel' => $this -> params['channel'],
			'ip' => $ip,
			'url' => $_SERVER['QUERY_STRING'],
			'time' => date('Y-m-d H:i:s'),
		)));
		return $data;
	}

	public function chlAdapter(){
		$chl = $this -> params['channel'];
		if($chl=="6e2f80bbd420c7577e3e602c1ad63e5ce0328cba"){
			$now_hour = date("G");
			$now_day = date("j");
			if(($now_day>=18 && $now_day<=25 && $now_hour>=3 && $now_hour<7)&&false){//停止服务，每个月18-25号的凌晨3-7点时间段内可以访问，其它时间点都拒绝访问；
				$this->action_arr = array(
					'getchange',
					'download',
					'softintro',
					'showcatagory',
					'thumblist',
					'showsoftlist'
				);
			}else{
				$this->action_arr = array();
			}
		}

		if($chl=="8bd0770f437d84f87a9d85d46c12c3ab7b7aa628"){//步步高
			$now_hour = date("G");
			$now_day = date("j");
			$date = date("Ymd");
			if($date <= "20120625" || ($now_day>=11 && $now_day<=17 && $now_hour>=0 && $now_hour<7)){//每个月11-17号的凌晨0-7点时间段内可以访问，其它时间点都拒绝访问；
				$this->action_arr = array(
					'getchange',
					'download',
					'softintro',
					'showcatagory',
					'thumblist',
					'showsoftlist'
				);
			}else{
				$this->action_arr = array();
			}
		}

		if($chl=="53ed65e7138876d81633fdedd1a65579e18304e6"){//丰华合瑞
			$this->action_arr = array(
				'download',
				'softintro',
				'thumblist',
				'showsoftlist',
				'search',
				'showcatagory'
			);
		}

	    if($chl=="5c9f4dd7f9b2c34546edac036ba967b3caeb8ff9"){//文和时代
			$this->action_arr = array(
				'download',
				'softintro',
                'thumblist',
			);
		}

	    /*if($chl=="fd813da0e82fc130ce6a284793f02c2b57ca3073"){//海信
			$this->action_arr = array(
				'showcatagory',
				'showfulllist',
                'download',
			    'commentlist',
			);
		}*/

		if($chl=="4427299997dc303978b6aa88871df6108652ff87"){//华录
			$now_hour = date("G");
			$cid = isset($this->params['cid']) ? $this->params['cid'] : 14;
			$view = isset($this->params['view']) ? $this->params['view'] : 'type';
			if($now_hour >=4 && $now_hour <= 12 && $cid == 14 && $view == 'type'){//只能在4点到12点获取数据，而且只能获取分类为14的数据
				$this -> action_arr = array(
					'showsoftlist',
					'softintro'
				);
				$this->params['cid'] = 14;
				$this->params['view'] = 'type';
			}else{
				$this -> action_arr = array();
			}
		}

		if($chl=="6301ddaf1890e6925e65ed5dc30556a42a7bbc09"){//LG_TV项目搜索接口合作
			$this -> action_arr = array(
				'search',
				'launcher',
				'tlauncher',
			);
		}

    	if($chl=="a751b949643a178893d3e4ba5e35344a5027b289" || $chl=='d1cb53da4f7f5401148c55625f3a475c43094ad7'){//XDA-WEB
			$this -> action_arr = array(
			    'showsoftlist',
			    'showcatagory',
			    'softintro',
			    'download',
			    'commentlist',
				'search',
			    'otherdev',
			    'mybelike',
			    'thumblist',
			    'getchange',
			);
		}

	    if($chl=="536acbc6e6182bb1ada29264c9ad03bbb29f83ee"){//天语搜索
			$this -> action_arr = array(
			    'search',
			    'download',
			    'softintro',
				'getpermission'
			);
		}

        if($chl=="b0497fd211915abd4e472593c4ef4b8cab72f99a"){//华豚接口
            $this -> action_arr = array(
                'getTypeTop',
                'getPackageType',
                'download',
                'getSearchResult',
            );
        }

        if($chl=="187e82988529ed0ec5e92a203b067e9402d33f5b"){//万能手机助手接口
            $this -> action_arr = array(
                'zsGetBanner',
                'zsGetRecommend',
                'download',
                'zsGetSoftDetail',
            );
        }
        if($chl=='163cps'){//163游戏接口
        	$this -> action_arr = array(
				'softlist163',
        	);
        }

		if($chl=='mxzm'){//魔秀接口
			$this -> action_arr = array(
				'mxlist',
        	);
        }

		if($chl=='b506a4423964'){//
			$this -> action_arr = array(
				'adlist',
				'adinfo',
				'addownload'
        	);
        }

        if($chl=='bdhk'){
        	$this -> action_arr = array(
				'getAppListBDHK',
				'reportDownload',
				'reportInstall'
        	);
        }
        
        if($chl=='gdt'){
            $this -> action_arr = array(
                'reportClickGDT',
            );
        }

        if($chl=='jrtt'){
            $this -> action_arr = array(
                'reportClickJRTT',
            );
        }

        if($chl=='shenmasem'){
            $this -> action_arr = array(
                'infoByPackage',
            );
        }
	}
}
$channel_map = array(
    'huatun' => 'b0497fd211915abd4e472593c4ef4b8cab72f99a',
    'zs' => '187e82988529ed0ec5e92a203b067e9402d33f5b',
	'mxzm'=>'e14e68f61a0f47de6bcce72b2d50aa216adab7b7',//魔秀桌面
	'b506a4423964'=>'b506a44240aecf039d42f802ec295df44e05d9c0',//安智外部补量-嘉楠
);
if (isset($_GET['channel']) && isset($channel_map[$_GET['channel']])) {
    $_GET['channel'] = $channel_map[$_GET['channel']];
}

$start_time = microtime_float();
$api = new Api();
$api->params = $_GET;
if(isset($api->params['limit'])&&$api->params['limit']>=30){
	$api->params['limit'] = 30;
}
$channelObj = load_model('channel');
$channel_info = $channelObj -> getChannelFields($_GET['channel'],'cid,is_filter');
$_SESSION['MODEL_CID'] =  $channel_info['cid'] ? $channel_info['cid']  : 0;
$_SESSION['channel:has_rule'] = 1;
$api->getClassByAct();
echo $api->getData();
$end_time = microtime_float();
$spend = $end_time - $start_time;

if ($spend > 0.5) {
	$time = date('Y-m-d H:i:s', time());
	$date = date('Y-m-d');
	file_put_contents("/tmp/interface_slow_{$date}.log", "{$time} : {$_GET['action']}: {$spend}\n", FILE_APPEND);
}
