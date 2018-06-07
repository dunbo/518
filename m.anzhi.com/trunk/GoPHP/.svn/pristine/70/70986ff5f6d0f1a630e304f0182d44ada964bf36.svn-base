<?php

/**
* 用于替换系统默认SESSION机制
* 
*
*
*/


/**
 * 临时打log用的。以后去掉
 * @param unknown $str
 */
function hf_log($str)
{
	//5.4支持全部参数
	//$arrDebug = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS,2);

	/* 暂时关闭日志功能
	$arrDebug = debug_backtrace();
	$base_path = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR;
	$arrDebug[0]['file'] = str_replace($base_path, '', $arrDebug[0]['file']);
	$methodName = sprintf(
			"[%s]\"%s\"(%s) %s\n",
			date('Y-m-d H:i:s'),
			$arrDebug[0]['file'].':'.$arrDebug[0]['line'],
			$arrDebug[1]['class']."->".$arrDebug[1]['function'],
			$str
	);
	
	$path = '/tmp/session_'.date('Y-m-d').'.log';
	file_put_contents($path, $methodName, FILE_APPEND);
	*/
}


class GoSession extends ArrayObject {
	
	/**
	 * 会话是否已经被初始化过了(防止多次初始化)
	 * @var bool
	 */
	private static $init = false;
	
	/**
	 * 会话 ID
	 * @var string 
	 */
	private $sid;
	
	/**
	 * 客户端携带的数据
	 * @var array
	 */
	private $clientData = array();
	/**
	 * 本地服务器SESSION数据
	 * @var array
	 */
	private $localData = array();
	/**
	 * 缓存句柄
	 * @var Memcache
	 */
	private $cache = null;
	
	/**
	 * 是否修改了数据
	 * @var bool
	 */
	private $change = false;
	
	
	/**
	 * 客户端SESSION数据是否发生变化
	 * @var bool
	 */
	private $clientDataChange = false; 
	
	
	
	
	
	/**
	 * 启动会话
	 * @param string $sid
	 * @return void
	 */
	public static function start($sid = null){
		if(!self::$init){
			$_SESSION = new self($sid);
			self::$init = true;
		}
		
	}
	
	/**
	 * 构造方法(不要在外部实例化) 请使用start() ;
	 * @param string $sid
	 */
	function __construct($sid){

		//hf_log('start_sid:'.$sid);
		parent::__construct();
		$this->sid = $sid;
		if(empty($this->sid)){
			$config = load_config('session/server_address');
			
			//生成SESSION（算法可优化）
			$this->sid = $this->createSid();
			session_id($this->sid);
		}
		session_start();
		register_shutdown_function(array($this,'flushData'));
	}
	
	
	
	
	
	/**
	 * 检查下标是否存在
	 * @see ArrayObject::offsetExists()
	 * @param string $index
	 * @return bool
	 */
	public function offsetExists($index) {

		/**
		 * 会话状态 0：未初始化,1:回话时间合法 2:回话超时
		 */
		
		static $status = 0;
		
		//会话超时检测
		switch ($status){
			case 0:
				$status = 1;
				$lifetime = $this->offsetGet('__lifetime');
				//新会话
				if ($lifetime == null){
					//设置会话时间以后可以放到配置文件中
					$this->offsetSet('__lifetime', time() + 3600);
				}else if($lifetime < time()){
					$status = 2;
					return false;
				}
				break;
			case 2:
				return false;
				break;
			default:
				break;
		}
		
		
		
		
		//渠道标识特殊处理
		if($index == 'CHANNEL_ID'){
			$model_cid = $this->offsetGet('MODEL_CID');
			if(empty($model_cid)){
				return false;
			}
			$goModel = new GoModel();
			$option = array(
				'where' => array(
					'cid' => $model_cid,
					'status' => 1,
				),
				'table' => 'sj_channel',
				'field' => 'cid,chl',
				'cache_time' => 86400
			);
			$channel = $goModel->findOne($option);
			if(empty($channel['chl'])){
				return false;
			}

			parent::offsetSet($index, $channel['chl']);
			return true;
			
		}
		
		if(!isset($this->clientData[$index]) && empty($this->cache)){ 
			hf_log("get data index:$index");
			$this->initLocalData();
		}
		return parent::offsetExists($index);
	}

	/**
	 * 下标取值
	 * @see ArrayObject::offsetGet()
	 * @param string $index
	 * @return mixed
	 */
	public function offsetGet($index){
		
		if($this->offsetExists($index)){
			return parent::offsetGet($index);
		}

		return null;
	}

	/**
	 * 根据下标给数组赋值
	 * @see ArrayObject::offsetSet()
	 * @param string $index
	 * @param mixed $newval
	 * @return void
	 */
	public function offsetSet($index, $newval){
		
		if($this->isClientData($index)){
			$this->clientData[$index] = $newval;
			$this->clientDataChange = true;
		}else{
			$this->localData[$index] = $newval;
		}

		hf_log("set val $index = $newval");

		$this->change = true;
		return parent::offsetSet($index, $newval);
	}

	/**
	 * 根据下标移除数据
	 * @see ArrayObject::offsetUnset()
	 * @param string $index
	 * @return bool
	 */
	public function offsetUnset($index){
		
		if(!$this->offsetExists($index)){
			return false;
		}
		
		if(isset($this->clientData[$index])){
			$this->clientData[$index] = null;
		}
		if(isset($this->localData[$index])){
			$this->localData[$index] = null;
		}
		$this->change = true;
		return parent::offsetUnset($index);
	}
	
	/**
	* 把本地SESSION数据存储到缓存中
	* @param void
	*/
	public function flushData(){
		
		if($this->change){
			
			//hf_log("data:change!!!");

			
			if(empty($this->cache)){
				$this->initCache();
			}
			
			//在失效时间中加入一分钟的服务器容错
			$lifeTime =  $this->offsetExists('__lifetime') ? ((parent::offsetGet('__lifetime') - time()) + 60) : 3600;
			$ordData = $this->cache->get('sess_'.$this->sid);
			$is = $this->cache->set('sess_'.$this->sid, array_merge(empty($ordData) ? array() : $ordData, $this->localData,$this->clientData), $lifeTime);
			
			//hf_log(sprintf("sid:%s,is:%s,data:%s",$this->sid,$is,print_r(array_merge(empty($ordData) ? array() : $ordData, $this->localData,$this->clientData),true)));
			
		}
	}
	
	/**
	* 获取会话ID
	* @return array
	*/
	public function getSid(){
		return $this->sid;
	}
	
	
	/**
	* 获取客户端携带数据
	* @return array
	*/
	public function getClientData(){
		return $this->clientData;
	}
	

	/**
	* 初始化客户端携带数据 
	* @param array $data
	* @return bool
	*/
	public function setClientData($data = array()){
		if(!is_array($data)){
			return false;
		}
		
		foreach ($data as $k => $v){
			$this->clientData[$k] = $v;
			parent::offsetSet($k, $v);
		}
		
		return true;
	}
	
	/**
	 * 重新生成SID(该方法主要是为了兼容2.X版本的。以后可以去掉)
	 * @return string
	 */
	public function regenerateSid() {
		
		$sid = $this->createSid();
		
		//$this->initLocalData();
		//$this->change = true;
		hf_log("old sid:$this->sid new sid : $sid");
		$this->sid = $sid;
		setcookie('PHPSESSID',$sid);
		
		return $sid;
	}
	
	
	
	
	/**
	* 初始化memcache链接
	* @return void
	*/
	private function initCache(){
		$config = load_config('session/memcached/'.substr($this->sid,-1));
		if (!$config){
			hf_log('session/memcached/'.substr($this->sid,-1).':'.json_encode($config));
		}

		//hf_log('memcache connect '.load_config('session/server_address').' to '.substr($this->sid,-1));

		$this->cache = new GoMemcachedCacheAdapter($config);
		if (!$this->cache){
			hf_log(json_encode($config));
		}
	}
	
	/**
	* 初始化本地SESSION
	* @return void
	*/
	private function initLocalData(){
		if(empty($this->cache)){
			$this->initCache();
		}
		
		static $init = false;
		//防止多次初始化
		if($init){
			return;
		}
		
		$data = $this->cache->get('sess_'.$this->sid);
		if(empty($data) || !is_array($data)){
			$data = array();	
		}
		
		foreach($data as $k => $v){
			
			if($this->isClientData($k) && !isset($this->clientData[$k])){
				$this->clientData[$k] = $v;
				$this->clientDataChange = true;
			}
			if(!isset($this->localData[$k])){
				$this->localData[$k] = $v;
			}
			
			parent::offsetSet($k, $v);
		}
		$init = true;
	}
	
	/**
	* 检查SESSION类型归属
	* @param string $key 
	* @return bool
	*/
	private function isClientData($key){
		
		static $config = null;
		
		if (empty($config)){
			$config = load_config('client_session/key');
		}

		return isset($config[$key]);
	}
	
	/**
	 * 生成SID
	 * @return string
	 */
	private function createSid() {
		$config = load_config('session/server_address');
		//生成SESSION（算法可优化）
		return md5(microtime(true).mt_rand().mt_rand()).'-'.$config;
	}
	
	/**
	 * 返回客户端SESSION数据是否变化
	 * @param void
	 * @return bool
	 */
	public function isClientDataChange(){

		return $this->clientDataChange;
	}

	
}


