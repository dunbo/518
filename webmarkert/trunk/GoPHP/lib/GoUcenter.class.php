<?php
/*
用户中心
*/

class GoUcenter{
	private $state = 'xxxxxxxxx';
	private $serviceid = 'bbsform';
	private $service_secret = "Ss5gM84Yt0fBFD202p2NPawL";
	private $redirect_uri = '';
	private $uc_api = "http://dev.i.anzhi.com/api/account/queryUserBySid"; //用户中心接口
	private $cookie_key = "_AZ_COOKIE_";
	private $isChangeName = false;
	private $header = array();
	private $request_data = array();
	private $device = array();
	private $serviceVersion = 'bbs.1.0';
	private $serviceType = '1';
	private $token_key_array = array();
	private $token_key = '';
	private $writelogtype = array(
		'error' => 'uc_error.log',
		'strace' => 'uc_strace.log',
		'success' => 'uc_success.log',
	);
	private $logdir = "/data/att/market_uc_log/";
	public function __construct($appname = APP_NAME){
		$config = load_config('ucenter/' . $appname, 'uc');
		if (empty($config))
			return false;
		$this->serviceid = $config['serviceId'];
		$this->service_secret = $config['privatekey'];
		$this->serviceVersion = $config['serviceVersion'];
		$this->serviceType = $config['serviceType'];
		if(isset($config['uc_api'])){
			$this -> uc_api = $config['uc_api'];
		}
		if(isset($config['service_secret'])){
			$this -> service_secret = $config['service_secret'];
		}
		$this->token_key = $this->get_token_key(); //获取密钥的key
		$this->request_header();
	}
	//数据请求参数
	function request_header(){

		$this->header = json_encode(array(
			'state' => time(),
			'timestamp' => time(),
			'ucenterver' => '',
			'appkey' => '',
		));
		$this->device = json_encode(array(
			'deviceid' => '',
			'os' => '',
			'osver' => '',
			'nettype' => '',
			'tel' => '',
			'netserver' => '',
			'screen' => '',
			'imsi' => '',
			'mac' => '',
			'abi' => '',
			'ip' => $_SERVER['REMOTE_ADDR'],
			'useragent' => $_SERVER['HTTP_USER_AGENT'],
		));

		$this->data = array();
	}
	//检测cookie type @ login / register
	function token_userinfo(){
		//获取cookie
			#_AZ_KEY__
			#解析cookie
			#pid username
		//回调 usercenter 接口获取信息
		$ucinfo = $this->parse_uc_cookie();
		$data = array();
		$loginIp = $userinfo['loginip'];
		if(isset($ucinfo['sid'])){
			$data['sid'] = $ucinfo['sid'];
			$data['pid'] = $ucinfo['pid'];
		}
		$info = array();
		$userinfo = array();
		if(!empty($data)){		    
			$data_str = $this->encode($data,$this->service_secret);
			$request_param['data'] = $data_str;
			$request_param['serviceId'] = $this->serviceid;
			$request_param['serviceVersion'] = $this->serviceVersion;
			$request_param['serviceType'] = $this->serviceType;
			$request_param['header'] = $this->header;
			$request_param['device'] = $this->device;
			//$data = json_encode($data);
			$userinfo = json_decode($this->cURL($request_param),true);
			/*$json = '{"code":200,"msg":"操作成功","time":1403001486,"user":{"pid":206345,"uid":"20140527110044B1rCNmCCC6","loginName":"lipengallen","nameChangeTimes":1,"password":"5b4c5a90c700148578911d76b5ee7211","salt":"1c1b0b","email":"lipeng@anzhi.com","emailValidStatus":"YES","telephone":"15011588079","telephoneValidStatus":"YES","encryption":"UCENTER","regtime":1401159644,"status":"ACTIVED","createTime":1401159644,"updateTime":1402971325,"userInfoLoginName":"info_login_name","nickName":"a","album":"album","sex":"FAMALE","birthday":"2014-06-11","address":"上海市|黄浦区","constellation":"白羊座","userInfoCreateTime":1401159644,"userInfoUpdateTime":1402572670,"userWealth":{"pid":876884,"uid":"20140527110044B1rCNmCCC6","payPassword":"2ece3294f7b8f5a3499cc2e059613e07","salt":"Kknfn","azmoney":0,"points":0,"level":0,"createTime":1402919014,"updateTime":1402971325}}}';
			$userinfo = json_decode($json,true);*/
			$code = $userinfo['code'];
			if( $code == 200){
				$userinfo = $userinfo['data']['user'];
				$info = array(
					'USER_ID' => $userinfo['pid'],
					'USER_NAME' => $userinfo['loginName'],
					'USER_UID' => $userinfo['uid'],
					'EMAIL' => $userinfo['email'],
					'MOBILE' => $userinfo['telephone'],
				);
				return $info;
			}
		}
		return $info;
	}

	function token_userinfo_portal($token){
        //获取cookie
            #_AZ_KEY__
            #解析cookie
            #pid username
        //回调 usercenter 接口获取信息
        $data['token'] = $token;
        $info = array();
        $userinfo = array();
        if(!empty($data)){
            $data_str = $this->encode($data,$this->service_secret);
            $request_param['data'] = $data_str;
            $request_param['serviceId'] = $this->serviceid;
            $request_param['serviceVersion'] = $this->serviceVersion;
            $request_param['serviceType'] = $this->serviceType;
            $request_param['header'] = $this->header;
            $request_param['device'] = $this->device;		
            $userinfo = json_decode($this->cURL($request_param),true);
        }
        return $userinfo;
    }
	public function cURL($data=array()){
		if (empty($data)) return false;
		$ch = curl_init();
		$url = $this->uc_api;
		$header = array();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER,$header);
		if ($data) curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
		$result = curl_exec($ch);
		curl_close($ch);
		return $result;
	}
	public function get_token_key(){
		$acr = 'eyIwIjoiNGMwODFJY2c0MGlmVjY3aUw4SzZvN3FyIiwiMSI6ImM2OERqbXR4UU1wdUx3eFhOOXpKY1FybiIsIjIiOiJ4dzRnSVFVQTdESmw1NzRWVUhEOFhDV28iLCIzIjoiMjIwdEliRjV3VXVYTjdHWEZGQ1R3Nk9iIiwiNCI6IjB1bTBYZjQ1Rzc5RTFBRDYyR1NvOTlWVSIsIjUiOiIzRnk0QTA3MnZzOTRldjIyYjU1dHkxOHIiLCI2Ijoic2JNOFZuVHljNXBiMGk4T2llS2RTbnpBIiwiNyI6Im00YmI2NDJIQ0g3UEpqN2ZxZU9neFlJQSIsIjgiOiJIZHNFejd6MXlxRlM5MHptbzB2MTk3MVUiLCI5IjoiVzRrbHAzc3RsdUtPY3lHVEhsd0RpQVdOIiwiMTAiOiI0QWNZUUkxNnh5bEQ2ZXg1b1h5WDI3SmQiLCIxMSI6IjRVVWluYUwwcmRhNUJwMjZxc29KcVBTTyIsIjEyIjoiMHI3SU40N0pFbTI4SFlESFhoMHBqMkFyIiwiMTMiOiI5VUU4M1RHajU1bHMwRTJ1TXE2SlJsTTUiLCIxNCI6IkpSTjJYNFdJME9Ka2NwV1ZUTXNMOU1JMCIsIjE1IjoiODY1aDcxVFlDTzRKakc0MjFVeDVoazR6IiwiMTciOiI5c0lCMkhINDNqMzlQcGU3MzNZTGs5M20iLCIxNiI6IkpxTDU4VDlnTllWR3AyOVI4UTQ1MjdUeSIsIjE5IjoiRDlEM0EyUG1rM1NpODlkZGh3V0w0TThWIiwiMTgiOiJhNjNKbFJpREJVcEkyelJxUXhNZjNJNU8ifQ==';
		$this->token_key_array  =  json_decode(base64_decode($acr),true);
		$cookie_key = $_COOKIE['_AZ_COOKIE_KEY_'];
		return $this->token_key_array[$cookie_key];
	}
	//3des 加密
	function encode($data,$encrpty_key){
		if(empty($data)) return array();
		$data = json_encode($data);
		include_once(dirname(__FILE__) . '/GoDes.class.php');
		$des = & new GoDes($encrpty_key);
		if($data){
			$data = $des->getCodedEncrypt($data);
			return $data;
		}
	}

	//3des 解密
	function decode($data,$encrpty_key){
		if(empty($data)) return array();
		include_once(dirname(__FILE__) . '/GoDes.class.php');
		$des = & new GoDes($encrpty_key);
		if($data){
			$data = $des->getDecodedDecrypt($data);
			return json_decode($data,true);
		}
	}

	//解析cookie
	function parse_uc_cookie($cookie_type){
		$key = $this->cookie_key;
		$encode_str = $_COOKIE[$key];
		//{"sid":"ODkyMDE1M3wxNDAyNjI1NzI3fG51bGx8TU9WRV9URVJNSU5BTA==","uid":"MjAxNDA2MDUxOTAxMDJrNHdITHk2MEhP","pid":8920153,"scopeTime":2592000,"lastLoginTime":1402625727,"loginAccount":"aaaaaa","autoLoginTime":2592000}
		$ucinfo = $this->decode($encode_str,$this->token_key);
		return $ucinfo;
	}

}
