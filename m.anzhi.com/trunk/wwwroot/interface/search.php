<?php

class search
{
	public $params;
	public function __construct($param)
	{
		$this->params = $param;
	}
	public function getData(){
		$key = addslashes($this->params['key']);
		$ftype = addslashes($this->params['ftype']);
		if(empty($key)) exit("search key is null");
		if(!$this->is_utf8($key)) $key = iconv("gb2312","utf-8",$key);
        $softObj = load_model('sjsoft');
		if($this -> params['channel'] == '536acbc6e6182bb1ada29264c9ad03bbb29f83ee' && in_array($key,array('炸金花','扎金花','金花','斗牛'))) {
			header("HTTP/1.1 204 No Content");
			exit;
		}
		$page = $this->params['start'] ? $this->params['start'] : 0;
		$pagesize = $this->params['limit'] ? $this->params['limit'] : 5;
        $pagesize = ($pagesize > 30) ? 30 : $pagesize; //对 limit 进行限制  加入 大于30  设置为30
		$offset = $page * $pagesize;
		$result = getAPI('soft.getsearch',array(
				'start' => $offset,
				'limit' => $pagesize,
				'keyword' => $key,
				'channel' => $this -> params['channel'],
				'st' => $this -> params['st'],
				'action' => $this -> params['action'],
		));
		
        $data = array();
		if($ftype == 'xml'){
			$infos = json_decode($result,true);
			$count = $infos['TOTAL'];
			$result_str = $this->get_data_xml($infos['DATA'],$count);
			echo $result_str;
		}else{
			return $result;
		}
	}

	public function is_utf8($string) {
	    // From http://w3.org/International/questions/qa-forms-utf-8.html
	    return preg_match('%^(?:
	          [\x09\x0A\x0D\x20-\x7E]            # ASCII
	        | [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
	        |  \xE0[\xA0-\xBF][\x80-\xBF]        # excluding overlongs
	        | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte
	        |  \xED[\x80-\x9F][\x80-\xBF]        # excluding surrogates
	        |  \xF0[\x90-\xBF][\x80-\xBF]{2}     # planes 1-3
	        | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
	        |  \xF4[\x80-\x8F][\x80-\xBF]{2}     # plane 16
	    )*$%xs', $string);

	}
	
	
	protected function get_data_json($infos,$count,$catalog){
		$data = array();
		$img_host = getImageHost();
		foreach($infos as $info){
			$data_info = array('package' => $info["package"],
				"icon" => $info['icon'],
				'typeid' => $info['typeid'],
				'type' => $info['type'],
				'name' => $info["name"],
				'softid' => $info["softid"]
			);
			$showtype = isset($this -> params['st']) ? $this -> params['st'] : '';
			if($showtype == 'full'){
				$data_info['version'] = $info['version'];
				$data_info['version_code'] = $info['version'];
				$data_info['apksize'] = $info['apksize'];
				$data_info['downloaded'] = $info['downloaded'];
			}
			$data[] = $data_info;
		}
		
		$result  = array(
				'TOTAL' => $count,
				'DATA' => $data,
				'KEY' => strtoupper($this -> params['action']),
		);
		return $result;
	}
	
	protected function get_data_xml($infos,$count,$catalog){
		$showtype = isset($this -> params['st']) ? $this -> params['st'] : '';
		foreach($infos as $info){
		    $data_str .= "<info>";
			$data_str .= "<package>";
			$data_str .= $info['package'];
			$data_str .= "</package>";
			$data_str .= "<icon>";
			$data_str .= $info['icon'];
			$data_str .= "</icon>";
			$data_str .= "<typeid>";
			$data_str .= $info['typeid'];
			$data_str .= "</typeid>";
			$data_str .= "<type>";
			$data_str .= $info['type'];
			$data_str .= "</type>";
			$data_str .= "<name>";
			$data_str .= $info['softname'];
			$data_str .= "</name>";
			$data_str .= "<softid>";
			$data_str .= $info['softid'];
			$data_str .= "</softid>";
			if($showtype == 'full'){
				$data_str .= '<version>'.$info['version'].'</version>';
				$data_str .= '<version_code>'.$info['version_code'].'</version_code>';
				$data_str .= '<apksize>'.$info['apksize'].'</apksize>';
				$data_str .= '<downloaded>'.$info['downloaded'].'</downloaded>';
			}
			$data_str .= "</info>";
		}
		
		$result_str = '<?xml version="1.0" encoding="UTF-8"?>';
		$result_str .= "<ALL>";
		$result_str .= "<TOTAL>";
		$result_str .= $count;
		$result_str .= "</TOTAL>";
		$result_str .= "<DATA>";
		$result_str .= $data_str;
		$result_str .= "</DATA>";
		$result_str .= "<KEY>";
		$result_str .= strtoupper($this -> params['action']);
		$result_str .= "</KEY>";
		$result_str .= "</ALL>";
		return $result_str;
	}
}


?>
