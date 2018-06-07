<?php
/**
 * @Author:      ct
 * @DateTime:    2015-07-29 14:45:30
 * @Description: 网易CPS合作游戏列表接口
 * 				 数据类型分为全量和增量
 */
ini_set("display_errors", 1);
error_reporting(1);

class softlist163{
	public $params;
	public $type_id;
	public $pre_url;
	public function __construct($param){
		$this->params = $param;
		$this->pre_url = getIconHost();
	}
	public function getData(){
        
		$type = $this->params['type'];
		$page = $this->params['start'] ? $this->params['start'] : 0;
		$pagesize = $this->params['limit'] ? $this->params['limit'] : 50;
		$channel_id = 47;
		$memcache = GoCache::getCacheAdapter('memcached');
		if (empty($type_id)) {
			$this->type_id = $type_id = $memcache->get('TYPE_ID');
		}
		if($type ==''){ #全量
			$return_cache  = $memcache->get('SOFTLIST163_ALL_'.$channel_id);
			if($return_cache){
// 				echo $return_cache;exit;
			}
			$softObj = load_model('softlist');
			$sql = "select g.*,f.iconurl_125,s.dev_name,s.category_id,s.language,s.intro,s.hide from  sj_soft as s
					join sj_soft_file as f on s.softid=f.softid 
					join sdk_channel_game as g  on s.softid=g.softid where  g.channel_id='".$channel_id."' and g.status = 1 and g.apk_status = 3";
			$query = $softObj->query($sql);
			$id_arr = array();
			$key = 0;
			while ($row = mysql_fetch_assoc($query)) {
				if(array_key_exists($row['softid'], $id_arr)){
					if( $row['update_tm'] > $_r[$id_arr[$row['softid']]]['modifytime'] && $_r[$id_arr[$row['softid']]]['modifytime'] > 0){
						unset($_r[$id_arr[$row['softid']]]);
						$id_arr[$row['softid']] = $key;
						$key ++;
						$_r[] = $this->formatData($row,$softObj);
					}
				}else{
					//add:
					$id_arr[$row['softid']] = $key;
					$key ++;
					$_r[] = $this->formatData($row,$softObj);
				}
			}
			$return['pagesize'] = 1000;
			$return['pagenum']  = 1;
			$return['gamelist'] = array_values((array)$_r);
			echo json_encode($return);
			if($_r){
				$memcache->set('SOFTLIST163_ALL_'.$channel_id,json_encode($return),3600);
			}
		}else{
			#增量
			$day =  $this->params['day'];
			if($day){
				$start_time = strtotime($day);
			}else{
				$start_time = strtotime(date('Y-m-d'));
			}
			$now_day = date('Ymd',$start_time);
			$cache_key = 'SOFTLIST163_'.$day.'_'.$channel_id;
			$end_time   = $start_time + 86400;
			$cache_data = $memcache->get($cache_key);
			if($cache_data){
// 			    return $cache_data;exit;
			}
			$softObj = load_model('softlist');
			$sql = "select g.*,f.iconurl_125,s.dev_name,s.category_id,s.language,s.intro,s.hide from  sj_soft as s 
					join sj_soft_file as f on s.softid=f.softid 
					join sdk_channel_game as g  on s.softid=g.softid where g.channel_id='".$channel_id."' and g.status = 1 and g.apk_status = 3 and g.review_time >'".$start_time."' and g.review_time < '".$end_time."' ";
			$query = $softObj->query($sql);
		    //$this->echomicrotime($sql);
			$id_arr = array();
			$key = 0;
			while ($row = mysql_fetch_assoc($query)) {
					$_r[] = $this->formatData($row,$softObj);							
			}
            #删除的数据
			$sql = "select g.*,f.iconurl_125,s.dev_name,s.category_id,s.language,s.intro,s.hide from  sj_soft as s
					join sj_soft_file as f on s.softid=f.softid
					join sdk_channel_game as g  on s.softid=g.softid where g.channel_id='".$channel_id."' and g.status = -1 and g.apk_status = 3 and g.update_tm >'".$start_time."' and g.update_tm < '".$end_time."' ";
			$query = $softObj->query($sql);
			while ($row = mysql_fetch_assoc($query)) {
			    $_r[] = $this->formatData($row,$softObj);
			}
			$return['pagesize'] = 1000;
			$return['pagenum']  = 1;
			$return['gamelist'] = array_values((array)$_r);
			echo json_encode($return);
			
			if($_r){
			    $memcache->set($cache_key,json_encode($return),3600);
			}
		}
	}
	/**
	 * 格式化数据
	 * @param  [array]  &$row     [数据库查询的数据]
	 * @param  [object] &$softObj [数据库操作对象]
	 * @return [array]            [格式化后api需要的数据]
	 */
	public function formatData(&$row,&$softObj){
		$_d['iconUrl'] 		= $this->getIconUrl($row['softid'],$row['iconurl_125'],$softObj);
		$_d['id'] 			= (int)$row['softid'];
		$_d['name']			= $row['name'];
		$_d['aliasname'] 	= $row['channel_softname'];
		$_d['author'] 		= $row['dev_name'];
		$_d['category'] 	= $this->categoryChange($row['category_id']);
		$_d['scheme'] 		= $row['package']; #应用包名
		$_d['platform'] 	= 2;#应用平台
		$_d['version'] 		= $row['version_code'];#应用版本
		$_d['versioncode']  = $row['version_code_num'];
		$_d['md5'] 			= $row['md5_file'];
		$_d['size'] 		= (int)$row['filesize'];
		$_d['apkUrl'] 		= getApkHost($row).$row['url'];
		//$_d['iconUrl'] = $row['softid'];
		$_d['screenshots'] 	= $this->getScreenHots($row['softid'],$softObj);#应用的截屏
		$_d['abstracts'] 	= $row['softid']; #摘要
		$_d['introduction'] = $row['intro']; #介绍
		$_d['languange']   = $this->languageChange($row['language']);
		$_d['delete']      = $row['status']==1?0:1;
		$_d['modifytime']  = $row['update_tm'];
		return $_d;
	}
	/**
	 * 获取软件icon图标
	 * @param  [int]    $softid [软件id]
	 * @param  [string] $url    [file表里的url]
	 * @return string         最终iconUrl
	 */
	public function getIconUrl($softid,$url,&$obj){
		$option = array(
            'table' => 'sj_icon',
            'where' => array(
                'softid' => $softid,
				'status' => 1
            ),
            'field'=>'softid,iconurl_125',
            'cache_time'=>3600,
        );
		$res = $obj->findOne($option);
		if($res){
			return $this->pre_url.$res['iconurl_125'];
		}else{
			return $this->pre_url.$url;
		}
	}
	/**
	 * 获取软件截屏
	 * @param  [int] 	$softid [软件id]
	 * @param  [object] &$obj   [数据操作对象]
	 * @return array          [图片列表]
	 */
	public function getScreenHots($softid,&$obj){
		$option['table'] = 'sj_soft_thumb';
		$option['where']['softid'] = $softid;
		$option['where']['status'] = 1;
		$option['cache_time'] = 3600;
		$option['field'] = 'softid,url';
		$res = $obj->findAll($option);
		$_d = array();
		foreach ($res as $key => $value) {
			$_d[] = $this->pre_url.$value['url'];
		}
		return $_d;
	}
	/**
	 * 转换分类
	 * @param  [int]    $category_id [soft 表存的category_id]
	 * @return string              [分类名称]
	 */
	public function categoryChange($category_id){
		$cate_id = trim($category_id,',');
		$cate_name = $this->type_id[$cate_id]['name'];
		return $cate_name?$cate_name:'';

	}
	/**
	 * 语言转换
	 * @param  [int] 	$type 
	 * @return string
	 */
	public function languageChange($type){
		if($type==1){
			return '中文';
		}elseif($type == 2){
			return '英文';
		}else{
			return '其他';
		}
	}
	public function makeDate(){
// 		echo "sss";
		$softObj = load_model('softlist');
		$sql = 'select * from sj_soft where 1  limit 100000';
		echo $sql;
		$query = $softObj->query($sql);
		print_r($query);
		while ($row = mysql_fetch_assoc($query)) {
			echo $in_sql = "insert into sdk_channel_game(`softid`,`name`,channel_softname,package,version_code_num,version_code,channel_id,url,filesize,md5_file,apk_status,add_tm,update_tm,status,http_sta) 
					values('".$row['softid']."','".$row['softname']."','".$row['softname']."','".$row['package']."','".$row['version_code']."','".$row['version']."','".rand(1,20)."','download_url','".rand(100,100000)."','".md5(rand(0,100000).'anzhi'.rand(0,100))."','3','".time()."','".time()."','1','1')";
			$softObj->query($in_sql);
			
		}
	}
	function echomicrotime($str=''){
	    if($_COOKIE['test']){
	        $key = $_SESSION['t_key']?$_SESSION['t_key']:1;
	        echo $str .'local->'.$key.' use :'. microtime()."<br />";
	        $_SESSION['t_key'] = $key + 1;
	    }
	    	
	}
}