<?php
class ConfigModel extends Model {
	//调整表前缀
     protected $trueTableName = 'pu_config';
     
     public function getAdColumnDesc()
     {
     	$desc = array(
     		'adname' => '广告名称',
     		'zid' => '广告位',
     		'ad_type' => '广告类型',
     		'package' => '包名',
     		'image' => '图片',
     		'note' => '广告备注',
     		'begintime' => '开始时间',
     		'endtime' => '结束时间',
     		'featureid' => '专题id',
			'channel_id' => '可见渠道',
     	);
     	
     	return $desc;
     }
     
     public function getFeatureColumnDesc()
     {
     	$desc = array(
     		'name' => '专题名称',
     		'orderid' => '排序',
     		'note' => '备注',
			'channel_id' => '可见渠道',
			'oid' => '运营商可见',
			'match_abi' => 'cpu类型可见',
     	);
     	
     	return $desc;
     }
	 public function getExtentColumnDesc(){
		$desc = array(
			'cid' => '可见渠道',
			'oid' => '运营商可见',
     	);
		return $desc;
	 }
     public function getFeatureSoftColumnDesc()
     {
     	$desc = array(
     		'package' => '包名',
     		'rank' => '排序',
     	);
     	
     	return $desc;
     } 
	 public function getSearchColumnDesc()
     {
     	$desc = array(
     		'srh_key' => '关键词',
     		'start_tm' => '开始时间',
     		'stop_tm' => '结束时间',
     	);
     	
     	return $desc;
     }
	 public function getSearchPackageColumnDesc()
     {
     	$desc = array(
     		'package' => '包名',
     		'pos' => '排序',
     		'start_tm' => '开始时间',
     		'stop_tm' => '结束时间',
     	);
     	
     	return $desc;
     }
	 public function getNecessaryColumnDesc()
     {
     	$desc = array(
     		'name' => '分类名',
     		'rank' => '排序位置',
     	);
     	
     	return $desc;
     }  
	 public function getSoftColumnDesc()
     {
     	$desc = array(
     		'softname' => '软件中文名称',
     		'ename' => '软件英文名称',
			'category_id'=>'软件分类',
			'intro'=>'软件描述',
			'tags'=>'关键字',
			'operating'=>'运营商隐藏',
			'auth'=>'是否授权',
			'note'=>'软件备注',
			'dev_name'=>'开发者姓名',
			'dev_enname'=>'开发者英文名',
			'dever_email'=>'开发者邮箱',
			'dever_page'=>'开发者主页',
			'channel_id'=>'渠道id',
     	);
     	
     	return $desc;
     }
	//变更配置
	function save_config($config_type,$configname,$res){
		$configcontent = json_encode($_POST);
		$map = array(
			'configname'=> $configname,
			'configcontent' => $configcontent,
			'status' => 1,
			'uptime' => time()
		);
		if($res){
			$where = array(	'config_type' => $config_type);		
			$ret =  $this->table('pu_config')->where($where)->save($map);
		}else{
			$map['config_type'] = $config_type;
			$ret = $this->table('pu_config')->add($map);
		}	
		return $ret;
	}	
	//获取配置
	function get_config($config_type){
		$where = array(	'config_type' => $config_type);
		return $this->table('pu_config')->where($where)->find();		
	}	
}
?>