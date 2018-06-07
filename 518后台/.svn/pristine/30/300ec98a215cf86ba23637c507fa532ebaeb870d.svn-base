<?php
class ContionsModel extends Model {
	
	public function getAbiList($match_abi = 0)
	{
		$abilist = array(
			'1' => array('ABI_ARMEABI (普通机型)', $match_abi & 1),
			'2' => array('ABI_ARMEABI_V7A (普通机型)',$match_abi & 2),
			'4' => array('ABI_X86 (pc)',$match_abi & 4),
			'8' => array('ABI_MIPS (mips机型，例如君正)',$match_abi & 8),
		);
		
		return $abilist;
	}
	
	public function getHomeExtentSoftTypeList($type = 0)
	{
		$typelist = array(
			0  => array('请选择合作形式', $type == 0),
			1  => array('CPT广告', $type == 1),
			2  => array('CPS广告', $type == 2),
			3  => array('CPA广告', $type == 3),
			4  => array('换量广告', $type == 4),
			5  => array('首发广告', $type == 5),
			6  => array('配送广告', $type == 6),
			7  => array('保量优化', $type == 7),
			8  => array('市场合作', $type == 8),
			9  => array('内部推广', $type == 9),
			10 => array('编辑推荐', $type == 10),
			11 => array('免费', $type == 11),
			12 => array('厂商合作', $type == 12),
		);
		
		return $typelist;
	}
	
	public function getFirmwareList($user_selected = array())
	{
		$selected = array();
		foreach($user_selected as $val) {
			if ($val != '') {
				$selected[$val] = 1;
			}
		}
		$result = array();
		$firmwares = $this->table('pu_config')->field('configname,configcontent')->where('config_type="firmware" and status=1')->select();
		foreach ($firmwares as $val) {
			$result[$val['configname']] = array($val['configcontent'], isset($selected[$val['configname']]));
		}
		return $result;
	}

	public function getOperators($user_selected = array())
	{
		$selected = array();
		foreach($user_selected as $val) {
			if ($val != '') {
				$selected[$val] = 1;
			}
		}
		$result = array();
		$operator_list = $this->table('pu_operating')->field("oid,mname")->select();
		$result[0] = array('未插卡', isset($selected[0]));
		foreach ($operator_list as $val) {
			$result[$val['oid']] = array($val['mname'], isset($selected[$val['oid']]));
		}
		return $result;
	}
	
	public function getMarketVersion($user_selected = array())
	{
		$selected = array();
		foreach($user_selected as $val) {
			if ($val != '') {
				$selected[$val] = 1;
			}
		}
		$market_list = $this->table('sj_market')->field('version_code,version_name')->where('status=1 and version_code>=3900')->order('cid asc')->group('version_code')->select();
		$version_list = array();
		foreach ($market_list as $val) {
			$version_list[$val['version_code']] = array($val['version_name'], isset($selected[$val['version_code']]));
		}
		/*
		$version_list = array(
			'3900' => array('4.0 test', in_array(3900, $user_selected)),
			'3900' => array('4.0 test', in_array(3900, $user_selected)),
			'4000' => array('4.0', in_array(4000, $user_selected)),
		);
		*/
	
		$version_list[3990] = array('4.0 (3990)', isset($selected[3990]));
		$version_list[3903] = array('4.0 (3903)', isset($selected[3903]));
		$version_list[4001] = array('4.0 (4001)', isset($selected[4001]));
		$version_list[4002] = array('4.0 (4002)', isset($selected[4002]));
		$version_list[4003] = array('4.0 (4003)', isset($selected[4003]));
		$version_list[4100] = array('4.1 (4100)', isset($selected[4100]));
		$version_list[4150] = array('4.1.5 (4150)', isset($selected[4150]));
		$version_list[4200] = array('4.2 (4200)', isset($selected[4200]));
		$version_list[4300] = array('4.3 (4300)', isset($selected[4300]));
		$version_list[4310] = array('4.3.1 (4310)', isset($selected[4310]));
		$version_list[4400] = array('4.4 (4400)', isset($selected[4400]));
		$version_list[4410] = array('4.4.1 (4410)', isset($selected[4410]));
		return $version_list;
	}
	
	public function filter_word($param)
	{
        $model = D('Sj.Config');
        $config_type = isset($param['type']) ? $param['type'] : 'soft_badword';
        $map = array(
			'config_type' => $config_type,
			'status' => 1
		);
		
		$res = $model->where($map)->find();
		$notallowword = $res['configcontent'];
		
		$wordarray = array();
		$wordarray = explode('|', $notallowword);
	//提醒词过滤
		$map_remind = array(
			'config_type' => 'soft_remind_words'
		);
		
		$res_remind = $model->where($map_remind)->find();
		$remindword = $res_remind['configcontent'];
		$remindarray = array();
		$remindarray = explode('|', $remindword);
		$result = array();
		foreach($param as $k => $v) {
			$result[$k] = array(true, '',true,'');
			
			foreach($wordarray as $value){
				if(strpos($v, $value) !== false){
					$result[$k][0] = false;
					$result[$k][1] .= ' '. $value. ' ';
				}
			}
			foreach($remindarray as $remind_value){
				if(strpos($v, $remind_value) !== false){
					$result[$k][2] = false;
					$result[$k][3] .= ' '. $remind_value. ' ';
				}
			}
		}
        return $result;
	}
	public function getProducts($user_selected = array())
	{
		$selected = array();
		foreach((array)$user_selected as $val) {
			if ($val != '') {
				$selected[$val] = 1;
			}
		}
		$result = array();
		$product_list = $this->table('pu_product')->field("pid,pname")->select();
		foreach ($product_list as $val) {
			$result[$val['pid']] = array($val['pname'], isset($selected[$val['pid']]));
		}
		return $result;
	}
	
	public function getAddress()
	{
		$model = new AdvModel();
		if (C('DB_HOST') != '192.168.0.99') {
			$model->addConnect(C('DB_IP2LOC_BASE'),2);  
			$model->switchConnect(2);
		}
		
		$sql = 'select * from ip2loc.ipdistrict';
		$res = $model->query($sql);
		$result = array();
		
		foreach ($res as $val) {
			$pid = $val['pid'];
			$id = $val['id'];
			if (!isset($result[$pid])) {
				$result[$pid] = array();
			}
			$result[$pid][$id] = array($val['district_name'], $val['pid']);
		}
		if (C('DB_HOST') != '192.168.0.99') {
			$model->closeConnect(2);
		}
		return $result;
	}
}
?>
