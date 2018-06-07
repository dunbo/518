<?php
/**
 * @Author:      ct
 * @DateTime:    2015-07-29 14:45:30
 * @Description: 网易CPS合作游戏列表接口
 * 				 数据类型分为全量和增量
 */
ini_set("display_errors", 1);
error_reporting(1);

class getAppListBDHK{
	public $params;
	public $type_id;
	public $pre_url;
	public function __construct($param){
		$this->params = $param;
		$this->pre_url = getIconHost();
		# 百度好看渠道ID，上线后需修改为线上对应ID
        $this->channel_id = 518;
	}
	public function getData(){
		$key = 'das56!%KAV';
		$input = file_get_contents('php://input');
        $args = json_decode($input, true);
		# 验证渠道签名合法性
		if ($args['channel'] != 'bdhk')
			return '{"ret":403}';
		$sign = md5($args['listType'].$args['page'].$args['channel'].$args['timestamp'].$key);
		if ($sign != $args['sign'])
			return '{"ret":403}';
		if ($args['listType'] != 2)
			return '{"ret":204}';
		# 默认分页为50
        $limit_size = 50;
		$limit_start = ($args['page'] - 1) * $limit_size;
		$channel_id = $this->channel_id;
		$memcache = GoCache::getCacheAdapter('memcached');
		$cache_key = 'SOFTLIST_BDHK_' . md5($input);
		#$return_cache  = $memcache->get($cache_key);
		if($return_cache){
// 				echo $return_cache;exit;
		}
		$softObj = load_model('softlist');
		$sql = "select g.*,f.iconurl_125,s.dev_name,s.category_id,s.language,s.intro,s.hide,
				       s.total_downloaded,s.min_firmware,c.name as category_name from  sj_soft as s
				join sj_soft_file as f on s.softid=f.softid 
				join sdk_channel_game as g  on s.softid=g.softid
				join sj_category as c on trim(both ',' from s.category_id)=c.category_id 
				where g.channel_id='".$channel_id."' and g.status = 1 and g.apk_status = 3
		        order by g.update_tm desc
		        limit $limit_start,$limit_size";
		$query = $softObj->query($sql);
        if (empty($query))
			return '{"ret":204}';
		$id_arr = array();
		$key = 0;
		while ($row = mysql_fetch_assoc($query)) {
			$_r[] = $this->formatData($row,$softObj);
		}
		$return['appList'] = array_values((array)$_r);
        $return['ret'] = 0;
		echo json_encode($return);
		if($_r){
			$memcache->set($cache_key,json_encode($return),3600);
		}
	}
	/**
	 * 格式化数据
	 * @param  [array]  &$row     [数据库查询的数据]
	 * @param  [object] &$softObj [数据库操作对象]
	 * @return [array]            [格式化后api需要的数据]
	 */
	public function formatData(&$row,&$softObj){
		$_d['appID']			= (int)$row['softid'];
		$_d['pkgName'] 			= $row['package']; #应用包名
		$_d['name']		 		= $row['channel_softname'];
		$_d['desc'] 			= $row['intro']; #摘要
		$_d['categoryName']		= $row['category_name'];
		$_d['size'] 			= (int)$row['filesize'];
		$_d['author'] 			= $row['dev_name'];
		$_d['iconUrl'] 			= $this->getIconUrl($row['softid'],$row['iconurl_125'],$softObj);
		$_d['download'] 		= "http://m.anzhi.com/dl_game.php?package={$row['package']}&gcid=".$this->channel_id;
		$_d['apkDate'] 			= $row['update_tm'];
		$_d['versionCode']  	= $row['version_code_num'];
		$_d['versionName']		= $row['version_code'];
		$_d['minVersion']		= $row['min_firmware'];
		$_d['screenshot'] 		= $this->getScreenHots($row['softid'],$softObj);#应用的截屏
		$_d['downloadCount']	= $row['total_downloaded'];
		$_d['apkMD5']			= $row['md5_file'];
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
		return implode($_d, '|');
	}
}
