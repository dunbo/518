<?php
class searchSoftList extends Action{
    public $params;
    public $device;
    public function __construct($param) {
		$this->params = $param;
    }
    public function getData() {
        $softObj = load_model('sjsoft');
        $option = array('where' => array('status' => 1));
        $page = isset($this -> params['start']) ? $this -> params['start'] : 0;
        $pagesize = isset($this -> params['limit']) ? $this -> params['limit'] : 10;
        $key       = addslashes($this -> params['key']);
        if(!$this->is_utf8($key)) $key = iconv("gb2312","utf-8",$key);
        $option['key'] = $key;
        $option['query_fields'] = "1234";
        $option['offset'] = $page * $pagesize;
        $option['pagesize'] = $pagesize ;
        if(isset($this -> params['order_key'])){
            $option['order_key'] = $this -> params['order_key'];
            $option['order']       = $this -> params['order'];
          }
         $softids = $this -> getCacheData($this -> device,'hot');
         $option['softids'] = $softids;
        $keylists  = $this -> searchBySoft('sj_soft',$option);
        $total = $option['total'];
        $catalist  = $softObj -> getDataList('sj_category', array('where' => array('status' => 1),'index' => 'category_id'));
        foreach($keylists as $keys){
            $data[] = array(
                'softid' => $keys['softid'],
                'icon'   => $keys['iconurl'],
                'softname' => $keys['softname'],
                'package' => $keys['package'],
                'type'      => $catalist[substr($keys['category_id'],1,-1)]['name'],
                'typeid'   => substr($keys['category_id'],1,-1),
                'score'    => $keys['score'],
                'upload_tm' => $keys['upload_tm'],
                'total_download' => $keys['total_downloaded'],
                'intro' => $keys['intro'],
             );
        }
        $data = array(
            'KEY' => strtoupper($this -> params['action']),
            'TOTAL' => $total,
            'DATA'  =>$data
        );
        return json_encode($data);
    }
       public function searchBySoft($table, & $option = array()){
         $softObj = load_model('sjsoft');
         $apps = array();
         $list = array();
         $sorted_fields = array(
            '2' => 'softname',
             '3' => 'dev_name',
             '1' => 'intro',
             '4' => 'package'
            );
         $key = trim($option["key"]);
         $fields = $option["query_fields"];
         $softids = "(".implode(',',$option['softids']).")";
         foreach ($sorted_fields as $label => $field){
             if (strpos($fields, $label . "") !== false){
                 $sql = "select * from " . $table . " where softid in ".$softids." and $field like '%" . $key . "%' and hide =1 and status=1 ";
                 $result = $softObj -> query($sql);
                 while($app = $softObj -> fetch($result)){
                     $sql = "select * from sj_soft_file where softid=" . $app['softid'];
                     $file = $softObj -> fetch($softObj -> query($sql));
                     $file['iconurl'] =STATIC_IMG_HOST . $file['iconurl'];
                     $file['apkurl'] = getApkHost($file) . $file['url'];
                     $app = array_merge($app, $file);
                     $results[$app['softid']] = $app;
                     }
                 }
             }
         if(empty($results)) return "empty";
         $option['total'] = count($results);
         $results = array_values($results);
         if(isset($option['order_key'])){
         $order_key = isset($option['order_key']) ? $option['order_key'] : 'upload_tm';
         $order        =  $option['order'] == 1 ? '1:-1;' : '-1:1;';
          uasort($results, create_function('$a, $b', 'return $a["'.$order_key.'"] < $b["'.$order_key.'"]?'.$order));
         }
         if (array_key_exists('pagesize', $option)) $list = array_slice($results, $option['offset'], $option['pagesize']);
         else $list = array_slice($results, 0, count($results));
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