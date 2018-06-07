<?php
/**
 * @Author:      ct
 * @DateTime:    2015-07-29 14:45:30
 * @Description: 通用数据接口通过配置channel类区分
 *
 */
//ini_set("display_errors", 1);
//error_reporting(1);
class commonSoftList{
	public $params;
	public $type_id;
	public $pre_url;
	public $downLoadUrl;
	public $channel_id;//
	public $iconQuality; //配置渠道icon质量
	public $iconSub; //配置渠道icon质量
	public $cache_key;
	public $count_cache_key;
	public function __construct($param){
		$this->params = $param;
		$this->pre_url = getIconHost();
		#配置渠道icon质量 channel_id => 1 | 0
		$this->iconQuality = array(
			//'47'=>1,
		);
		
		#配置渠道icon是否有角标的 channel_id => 1 | 0
		$this->iconSub = array(
			'3'=>1,
		);
		
		#配置渠道thumb是否有水印的 channel_id => 1 | 0
		$this->thumbSub = array(
			'3'=>1,
		);
		
		$this->downLoadUrl = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?';
	}
	public function getData(){
		$page = $this->params['start'] ? $this->params['start'] : 0;
		$pageSize = $this->params['limit'] ? $this->params['limit'] : 30;
		$debug = $this->params['debug'];
//		$channel_id = $this->getChannelId();
		$this->channel_id = $channel_id = $_SESSION['API_CID'];
		$memcache = GoCache::getCacheAdapter('memcached');
		$memcache = GoCache::getCacheAdapter('memcached');
		$this->type_id = $type_id = $memcache->get('TYPE_ID');
		$this->cache_key =  'commonSoftList_'.$channel_id.'_'.$page.'_'.$pageSize;
		$this->count_cache_key = 'commonSoftList_'.$channel_id;
		if($channel_id ){
//			$channel_cache_key = 'COMMONSOFTLIST_ALL_'.$channel_id;
			$return_cache  = $memcache->get($this->cache_key);
			if($return_cache && empty($debug)){
				$return_cache = json_decode($return_cache,1);
				$list = $return_cache['list'];
				$total = $return_cache['total'];
				$return['TOTAL'] = $total;
				$return['DATA'] = $list;
				$return['KEY'] = 'commonSoftList';
				return json_encode($return);
			}
			$limit_sql = 'limit '.$page.','.$pageSize;
			$softObj = load_model('softlist');
			$sql = "select g.*,f.iconurl_125 as iiconurl_125,f.id as sfid,s.tags,s.dev_name,s.category_id,s.language,s.intro,s.hide,
					s.total_downloaded,s.total_downloaded_add,s.total_downloaded_detain from  sj_soft as s
					join sj_soft_file as f on s.softid=f.softid 
					join sdk_channel_game as g  on s.softid=g.softid
					where  g.channel_id='".$channel_id."' and g.status = 1 and g.apk_status = 3 AND s.status=1 AND s.hide=1  AND f.`package_status`=1 order by s.softid asc ".$limit_sql;
			$query = $softObj->query($sql);
			$id_arr = $_r = array();
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
			$this->getTestData($_r);
			$return['TOTAL'] = $this->getCount($softObj,$memcache,$debug);
			$return['DATA'] = $_r;
			$return['KEY'] = 'commonSoftList';
			if($_r){
				$cacheData['list'] = $_r;
				$cacheData['total'] = $return['TOTAL'];
				$memcache->set($this->cache_key,json_encode($cacheData),3600);
			}
			echo  json_encode($return);exit;
		}else{
			header("HTTP/1.1 404 Not Found");
			header("Status: 404 Not Found");
			exit;
		}
	}
	public function getCount(&$softObj,&$memcache,$debug=false){
		$_count = $memcache->get($this->count_cache_key);
		if($_count && (!$debug) ){
			return $_count;
		}else{
			$sql_all =  "select count(*) as count from  sj_soft as s
					join sj_soft_file as f on s.softid=f.softid
					join sdk_channel_game as g  on s.softid=g.softid
					where  g.channel_id='".$this->channel_id."' and g.status = 1 and g.apk_status = 3 AND s.status=1 AND s.hide=1 AND f.`package_status`=1 order by s.softid asc ";
			$query_all = $softObj->query($sql_all);
			$count_arr = mysql_fetch_assoc($query_all);
			$count = $count_arr['count'];
			if($count){
				return $count;
				$memcache->set($this->count_cache_key,$count,3600);
			}else{
				return 0;
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
		$_d['icon'] 		= $this->getIconUrl($row['softid'],$row['iconurl_125'],$softObj);
		$_d['softid'] 		= (int)$row['softid'];
		$_d['name']			= ($row['name'])?$row['name']:$row['softname'];
		$_d['package']		= $row['package'];
		$_d['desc'] 		= $row['intro']; #介绍
		$_d['apksize'] 		= (int)$row['filesize'];
		$_d['total_download']	= num_format($row['total_downloaded']+$row['total_downloaded_add']-$row['total_downloaded_detain'],2);
		$_d['download'] 		= $this->getDownloadUrl($row);
//		$_d['aliasname'] 	= $row['channel_softname'];
		$_d['author'] 		= $row['dev_name'];
		$_d['category'] 	= $this->categoryChange($row['category_id']);
//		$_d['scheme'] 		= $row['package']; #应用包名
//		$_d['platform'] 	= 2;#应用平台
		$_d['version'] 		= $row['version_code'];#应用版本
		$_d['versioncode']  = $row['version_code_num'];
		$_d['md5'] 			= $row['md5_file'];
		//$_d['iconUrl'] 	= $row['softid'];
		$_d['screenshots'] 	= $this->getScreenHots($row['softid'],$softObj);#应用的截屏
//		$_d['abstracts'] 	= $row['softid']; #摘要

		$_d['languange']   = $this->languageChange($row['language']);
//		$_d['delete']      = $row['status']==1?0:1;
		$_d['modifytime']  = $row['update_tm'] ? $row['update_tm'] : 0;
		$_d['permission']  = $this->getPermission($row['sfid'], $softObj);
		
		return $_d;
	}
	/**
	 * 获取软件icon图标
	 * @param  [int]    $softid [软件id]
	 * @param  [string] $url    [file表里的url]
	 * @param  [object] $obj    数据库操作对象
	 * @return string    最终 iconUrl
	 */
	public function getIconUrl($softid,$url,&$obj){
		$option = array(
            'table' => 'sj_icon',
            'where' => array(
                'softid' => $softid ,
				'status' => 1
            ),
            'field'=>'softid,iconurl_125,apk_icon,iconurl_512',
            'cache_time'=>3600,
        );
		$res = $obj->findOne($option);
		if($res){
			if($this->iconQuality[$this->channel_id]){ #判断该频道是否配置icon质量
				return $this->pre_url.$res['apk_icon'];
			}else if($this->iconSub[$this->channel_id] && !empty($res['iconurl_512'])){ #判断该频道是否配置icon下标
				return $this->pre_url.$res['iconurl_512'];
			}else{
				return $this->pre_url.$res['iconurl_125'];
			}
		}else{
			return $this->pre_url.$url;
		}
	}
	
	
	
	/**
	 * 获取软件权限
	 * @param  [int] 	$sfid [文件id]
	 * @param  [object] &$obj   [数据操作对象]
	 * @return array          [权限列表]
	 */
	public function getPermission($sfid,&$obj){
		$sql_all =  "SELECT b.* FROM sj_soft_permission a JOIN sj_soft_permission_details b ON a.`permissionid`=b.`id`  and a.`fileid` = $sfid;";
		$query = $obj->query($sql_all);
		$r = array();
		while ($row = mysql_fetch_assoc($query)) {
			$r[] = $row['name'];
		}
		return $r;
	}
	
	/**
	 * 获取软件截屏
	 * @param  [int] 	$softid [软件id]
	 * @param  [object] &$obj   [数据操作对象]
	 * @return array          [图片列表]
	 */
	public function getScreenHots($softId,&$obj){
		$option['table'] = 'sj_soft_thumb';
		$option['where']['softid'] = $softId;
		$option['where']['status'] = 1;
		$option['cache_time'] = 3600;
		$option['field'] = 'softid,url,image_raw';
		$res = $obj->findAll($option);
		$_d = array();
		foreach ($res as $key => $value) {
			if($this->iconQuality[$this->channel_id]){
				$_d[] = $this->pre_url.$value['image_raw'];
			}else if($this->thumbSub[$this->channel_id] && !empty($value['image_raw'])){
				$_d[] = $this->pre_url.$value['image_raw'];
			}else{
				$_d[] = $this->pre_url.$value['url'];
			}
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
	/**
	 * 获取软件下载地址
	 * @param $row
	 * @return string
	 */
	public function getDownloadUrl(&$row){
		$data['channel'] = $this->params['channel_old'];
		$data['softid'] = $row['softid'];
		$data['action'] = 'commonDownload';
		return $this->downLoadUrl.http_build_query($data);
	}
	public function makeDate(){
		$softObj = load_model('softlist');
		$sql = 'select * from sj_soft where 1  limit 100000';
		echo $sql;
		$query = $softObj->query($sql);
		print_r($query);
		while ($row = mysql_fetch_assoc($query)) {
			//echo $in_sql = "insert into sdk_channel_game(`softid`,`name`,channel_softname,package,version_code_num,version_code,channel_id,url,filesize,md5_file,apk_status,add_tm,update_tm,status,http_sta)
					//values('".$row['softid']."','".$row['softname']."','".$row['softname']."','".$row['package']."','".$row['version_code']."','".$row['version']."','".rand(1,20)."','download_url','".rand(100,100000)."','".md5(rand(0,100000).'anzhi'.rand(0,100))."','3','".time()."','".time()."','1','1')";
			//$softObj->query($in_sql);
		}
	}
	function echomicrotime($str=''){
	    if($_COOKIE['test']){
	        $key = $_SESSION['t_key']?$_SESSION['t_key']:1;
	        echo $str .'local->'.$key.' use :'. microtime()."<br />";
	        $_SESSION['t_key'] = $key + 1;
	    }
	}
	function getTestData(&$_r){
		if(count($_r) < 1){
			$softObj = load_model('softlist');
			$sql = "SELECT s.softid,s.softname,s.package,s.intro,s.total_downloaded,f.filesize,f.iconurl_125 as ficonurl_125,i.apk_icon,i.iconurl_125 as iiconurl_125 from sj_soft as s join sj_soft_file as f on s.softid = f.softid join sj_icon as i on s.softid=i.softid where s.package ='cn.goapk.market' and s.status =1 and s.hide =1 order by s.softid desc limit 1";
			$query = $softObj->query($sql);
			$soft = mysql_fetch_assoc($query);
			if($soft){
				$_t = $this->formatData($soft,$softObj);
			}else{
				echo 'no data';exit;
			}
			$softList = array();
			for($i=0;$i<50;$i++){
				$softList[] = $_t;
			}
			$_r = $softList;
		}
	}
}


