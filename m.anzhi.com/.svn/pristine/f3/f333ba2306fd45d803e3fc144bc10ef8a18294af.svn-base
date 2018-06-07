<?php
/**
 * @Author:      ct
 * @DateTime:    2015-07-29 14:45:30
 * @Description: 网易CPS合作游戏列表接口
 * 				 数据类型分为全量和增量
 */
ini_set("display_errors", 1);
error_reporting(1);

class softlist_lyx{
	public $params;
	public $type_id;
	public $pre_url;
	public function __construct($param){
		$this->params = $param;
		$this->pre_url = getIconHost();
	}
	public function getData(){
		$page = $this->params['start'] ? $this->params['start'] : 0;
		$pagesize = $this->params['limit'] ? $this->params['limit'] : 30;
		$channel_id = 47;
		
		$model = load_model('softlist');
		$app = $model->getsoftinfos(2333655, getFilterOption());
		$return = array(
			'softid'=>$app['softid'],
			'name'=>$app['softname'],
			'package'=>$app['package'],
			'icon'=>$this->pre_url.$app['iconurl'],
			'download'=>$app['url'],
			'desc'=>$app['desc'],
			'apksize'=>$app['filesize'],
			'total_download'=>$app['total_downloaded'],
		);	
		echo json_encode($return);
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
	 * @return [string]         最终iconUrl
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
	 * @return [array]          [图片列表]
	 */
	public function getScreenHots($softid,&$obj){
		$option['table'] = 'sj_soft_thumb';
		$option['where']['softid'] = $softid;
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
	 * @return [string]              [分类名称]
	 */
	public function categoryChange($category_id){
		$cate_id = trim($category_id,',');
		$cate_name = $this->type_id[$cate_id]['name'];
		return $cate_name?$cate_name:'';

	}
	/**
	 * 语言转换
	 * @param  [int] 	$type 
	 * @return [string]       
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