<?php
//渠道通用配置管理
class UcAction extends CommonAction{

	private $host;
	
	private $api_suffix;

	private $token;

	//平台通用配置管理
	Public function lists(){
		
		$this->init2();
		//var_dump($_GET);exit;
		$act = empty($_GET['act']) ? 'list' : $_GET['act'];
		
		if($act == 'news_list') {
			$cid = intval($_GET['cid']);
			
			$f = $_GET['f'] > 0 ? true : false;
			$item = $this->getNewsList($cid,$_GET['grab_time'],$_GET['recoid'],$f);
			
			//print_r($_GET);

			$p_session = array('old' => $this->get_last_article($item['articles']),'new' => $this->get_new_article($item['articles']));

			$data = array();
			foreach ($item['articles'] as $k => $v) {
				$v['date'] = date('Y-m-d h:i:s',$v['publish_time'] / 1000);
				$data[] = $v;
			}

			echo json_encode(array('list' =>$data,'session' => $p_session));
			exit;
		}

		
		$classs = $this->getInfoClass();
		$this -> assign('classs',$classs);
		$this -> display();
	}


	
	//编辑广告分成显示
	private function init2(){
		
		$redis = new Redis();
		$c = C('task_redis');

		$redis->connect($c['host'],$c['port']);

		
		$cache = json_decode($redis->get("uc_token"),true);

		$this->token = $cache['access_token'];

		$this->host = 'https://open.uczzd.cn';
		
		$params = array(
			'access_token'	=> $this->token,
			'app'			=> 'anzhi-iflow',
			'dn'			=> '123456',
			'fr'			=> 'android',
			've'			=> '5.0.0.0',
			'imei'			=> 'eeeeeeeeeeeeeeee',
			'nt'			=> '2'
		);

		$this->api_suffix = array();
		foreach($params as $k => $v) {
			$this->api_suffix[] = $k.'='.$v;
		}
		$this->api_suffix = implode('&',$this->api_suffix);

	}


	//获取新闻列表
	function getNewsList($id,$time = 0,$recoid = '',$method = false) {
		
		$method = $method ? 'his' : 'new';
		//$meth 
		//$method = $time ? 'his' : 'new';
		$url = $this->host."/openiflow/openapi/v2/channel/{$id}?method={$method}&ftime={$time}&recoid={$recoid}&".$this->api_suffix;
		
		//var_dump($url);
		$text = $this->http_get($url);

		$text = json_decode($text,true);
		return $text['data'];
	}


		//取得信息分类
	function getInfoClass(){
		$url = $this->host."/openiflow/openapi/v2/channels?".$this->api_suffix;
		$text = $this->http_get($url);
		$data = json_decode($text,true);
		if($data) {
			return $data['data']['channel'];
		}
		return array();		
	}

	private function http_get($url,$posts = array()) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.1.4322)");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		//curl_setopt($ch, CURLOPT_POST, $posts ? 0 : 1); 
		//curl_setopt($ch, CURLOPT_POSTFIELDS,$posts);
		$icerik = curl_exec($ch);
		curl_close($ch);
		return $icerik;
	}
	
	private function get_last_article($data) {
		$min = PHP_INT_MAX;
		$index = 0;
		$result = null;
		foreach($data as $k => $v) {
			if($v['grab_time'] < $min) {
				$min = $v['grab_time'];
				$index = $k;
			}
		}
		if(!empty($data[$index])) {
			$result = array('grab_time' => $data[$index]['grab_time'],'recoid' => $data[$index]['recoid']);
		}
		
		return $result;
	}

	private function get_new_article($data) {
		$max = 0;
		$index = 0;
		$result = null;
		foreach($data as $k => $v) {
			if($v['grab_time'] > $max) {
				$max = $v['grab_time'];
				$index = $k;
			}
		}
		if(!empty($data[$index])) {
			$result = array('grab_time' => $data[$index]['grab_time'],'recoid' => $data[$index]['recoid']);
		}
		
		return $result;
	}


}