<?php
class searchHSoftList extends Action {

    public $params;
    public $device;
    public $order_rule = array('01'=> "upload_tm asc", '02'=> "upload_tm desc",'11' => "total_downloaded asc",'12' => "total_downloaded desc",'21' => "score asc",'22' => "score desc" );
    public $time   = array('1' => 'today','2'=>'week','3'=> 'moon');
    public function __construct($param) {
		$this->params = $param;
    }
    public function getData() {
        $softObj = load_model('sjsoft');
        if(isset($this -> params['key'])) {
         $key       = addslashes($this -> params['key']);
        if(!$this->is_utf8($key)) $key = iconv("gb2312","utf-8",$key);
            $option['key'] = $key;
        }
        if(isset($this -> params['order'])) $option['order'] = $this -> params['order'];
        if(isset($this -> params['upload_tm'])) $option['upload_tm'] = $this -> params['upload_tm'];
        if(isset($this -> params['total_download_1'])) $option['total_download_1'] = $this -> params['total_download_1'];
        if(isset($this -> params['total_download_2'])) $option['total_download_2'] = $this -> params['total_download_2'];
        if(isset($this -> params['score'])) $option['score'] = $this -> params['score'];
        if(isset($this -> params['type'])) $option['type'] = $this -> params['type'];
        isset($this -> params['start']) ? $page = $this -> params['start'] : 0;
        isset($this -> params['limit']) ? $pagesize = $this -> params['limit'] : 10;
        $option['query_fields'] = "1234";

        $option['offset'] = $page * $pagesize ;
        $option['pagesize'] = $pagesize;
        $softids = $this -> getCacheData($this -> device,'hot');
        $option['softids'] = $softids;
        $keylists  = $this -> searchBySoft('sj_soft',$option);
        $total = $option['total'];
        $catalist  = $softObj -> getDataList('sj_category', array('where' => array('status' => 1),'index' => 'category_id'));
        foreach($keylists as $keys){
            $data[] = array(
                'softid' => $keys['softid'],
                'softname' => $keys['softname'],
                'package' => $keys['package'],
                'icon'   => STATIC_IMG_HOST.$keys['iconurl'],
                'type'      => $catalist[substr($keys['category_id'],1,-1)]['name'],
                'typeid'   => substr($keys['category_id'],1,-1),
                'intro'    => $keys['intro'],
                'score'   => $keys['score'],
                'total_download'    => $keys['total_downloaded'],
                'upload_tm' => $keys['upload_tm'],
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
         $where = " softid in (".implode(',',$option['softids']).") and ";
         if(isset($option['order'])){
         $order = " order by ".$this ->order_rule[$option['order']];
         }else{
           $order = '';
         }
         if(isset($option['upload_tm'])){
             if($this -> time[$option['upload_tm']] == 'today')
             $time = " and    upload_tm >=".time();
             if($this -> time[$option['upload_tm']] == 'week')
             $time = " and    upload_tm >=".(time()-7*24*60*60)." and upload_tm <=".time();
             if($this ->time[$option['upload_tm']] == 'moon')
             $time = " and    upload_tm >=".(time()-30*24*60*60)." and upload_tm <=".time();
         }else{
             $time = '';
         }
         if(isset($option['total_download_1'])){
           $download = " and  total_downloaded >= ".$option['total_download_1'];
         }
         if(isset($option['total_download_2'])){
           $download .= " and  total_downloaded <= ".$option['total_download_2'];
         }
         if(isset($option['type'])){
          $ids_array = $this -> getCategoryIds($option['type']);
          foreach($ids_array as $value){
                  $idsarr[] = "',".$value.",'";
          }
          if(!empty($idsarr)) $idstr = ' and category_id in ('.implode(",",$idsarr).')';
          else $idstr='';
         }else{
          $idstr = '';
         }
         foreach ($sorted_fields as $label => $field){
             if (strpos($fields, $label . "") !== false){
                 $sql = "select * from " . $table . " where ".$where." $field like '%" . $key . "%'  ".$time." ".$download." ". $idstr ." and hide =1 and status=1 ".$order;
                 $result = $softObj -> query($sql);
                 while($app = $softObj -> fetch($result)){
                     $sql = "select * from sj_soft_file where softid=" . $app['softid'];
                     $file = $softObj -> fetch($softObj -> query($sql));
                     $file['iconurl'] = STATIC_IMG_HOST . $file['iconurl'];
                     $file['apkurl'] = getApkHost($file) . $file['url'];
                     $app = array_merge($app, $file);
                     $results[$app['softid']] = $app;
                     }
                 }
             }
         $option['total'] = count($results);
         $results = array_values($results);
         if (array_key_exists('pagesize', $option)) $list = array_slice($results, $option['offset'], $option['pagesize']);
         else $list = array_slice($results, 0, count($results));
         return $list;
         }
        public function getCategoryIds($categoryid) {
            $softObj = load_model('sjsoft');
            $cateparent = $softObj -> getDataList('sj_category', array('where' => array('parentid' => 0 ,'status' => 1),'index' => 'category_id'));
            $parentids = array_keys($cateparent);
            if(in_array($categoryid,$parentids)){
            $cate_info= $softObj -> getDataList('sj_category',array('where' => array('parentid' => $categoryid ,'status' => 1),'index'=>'category_id'));
            }
            else{
             $cate_info= $softObj -> getDataList('sj_category',array('where' => array('category_id' => $categoryid,'status' =>1),'index'=>'category_id'));
            }
            $ids =array();
            foreach($cate_info as $info){
                $ids[] = $info['category_id'];
            }
            return $ids;
        }
}

?>