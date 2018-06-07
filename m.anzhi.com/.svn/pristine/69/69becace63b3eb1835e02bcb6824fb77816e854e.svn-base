<?php
class Action {
	public function getData() {
	}
    public function getCacheDataByDevice($softid,$device,&$option=array()) {
        $softObj = load_model('sjsoft');
		$deviceinfo = $softObj -> getDataList('pu_device', array('where'=> array('dname'=> $device,'status' => 1)));
		$did = $deviceinfo[0]['did'];
		$show_soft_rule = $deviceinfo[0]['show_soft_rule'];
        $memsoftids = array();
        if($show_soft_rule){
			$memsoftids = GoCache :: get('SOFTLIST_NG_SOFTID_D'.$did);
          if(in_array($softid,$memsoftids)){
           header("HTTP/1.1 403 Forbidden");
           echo "No Content";
           exit;
         }
		}else{
			$memsoftids = GoCache :: get('SOFTLIST_PG_SOFTID_D'.$did);
          if(!in_array($softid,$memsoftids)){
           header("HTTP/1.1 403 Forbidden");
           echo "No Content";
           exit;
         }
		}
    }
	public function getCacheData($device, $cache_key,&$option=array()) {
		$softObj = '';
		$softObj = load_model('sjsoft');
		$deviceinfo = $softObj -> getDataList('pu_device', array('where'=> array('dname'=> $device,'status' => 1)));
		$did = $deviceinfo[0]['did'];
		$show_soft_rule = $deviceinfo[0]['show_soft_rule'];
		if(isset($option['catalogid'])) $opts['catalogid'] = $option['catalogid'];else $opts = array();
		$cachesoftids = $softObj -> getCachedSoftList($cache_key,$opts);
		$memsoftids = array();
		if($show_soft_rule){
			$memsoftids = GoCache :: get('SOFTLIST_NG_SOFTID_D'.$did);
			$packages =  array_keys($memsoftids);
			$mem_ids  =  array_values($memsoftids);
			$inter_ids= array_diff($cachesoftids,$mem_ids);
		}else{
			$memsoftids = GoCache :: get('SOFTLIST_PG_SOFTID_D'.$did);
			$packages =  array_keys($memsoftids);
			$mem_ids  =  array_values($memsoftids);
			$inter_ids= array_intersect($cachesoftids,$mem_ids);
		}
		if(isset($option['order_key'])){
			$softids = $softObj -> getDataList('sj_soft' ,array('where' => array('softid' => $inter_ids,'status'=>1),'index' => 'softid','order' => $option['order_key'] .' '. $option['order']) );
			$inter_ids   = array_keys($softids);
		}
		$option["total"] = count($inter_ids);
		if (array_key_exists('pagesize', $option)) $list = array_slice($inter_ids, $option['offset'], $option['pagesize']);
		else $list = array_slice($inter_ids, 0, count($inter_ids));
		return $list;
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
}
?>