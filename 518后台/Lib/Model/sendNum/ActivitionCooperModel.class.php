<?php
class ActivitionCooperModel extends Model {
	protected $tableName = 'dev_cooper_rebate_config';
	
	//添加等级折扣配置
	function add_rebate_config($data){
		$model = new model();	
		$insert_arr = array(
			'rebate_name'      => $data['rebate_name'],
			'discount'         => $data['discount'],
			'gold_coefficient' => $data['gold_coefficient'],
			'add_tm'           => time()
		);
		$insert_res = $model->table($this->tableName)->add($insert_arr);		
		return $insert_res;
	}
	
	//获取等级折扣配置列表
	function get_rebate_list($get='',$field='*'){
		$model = new model();	
		$total = $model->table($this->tableName)->count();
		$limit = isset($get['limit']) ? $get['limit'] : 10;
        $param = http_build_query($get);
		 import('@.ORG.Page2');
        $Page = new Page($total, $limit, $param);
        $Page->rollPage = 1;
        $Page->setConfig('header', '篇记录');
        $Page->setConfig('first', '首页');
        $Page->setConfig('last', '尾页');
		$rebate_list = $model
						->table($this->tableName)
						->field($field)
						->limit($Page->firstRow . ',' . $Page->listRows)
						->order('id desc')
						->select();
		return array($Page,$rebate_list);
	}
	
	//获取投放日期
	function get_ad_day(){
		$this->tableName = 'pu_config';
		$model = new model();
		$day_config = $model
						->table($this->tableName)
						->where(array(
							'config_type'=>array('in',array('AD_CONFIG_START_DAY','AD_CONFIG_END_DAY'))
						))
						->field('config_type,configcontent')
						->select();				
		foreach($day_config as $k=>$v){
			if($v['config_type']=="AD_CONFIG_START_DAY") $ad_day_arr['start_day'] = $v['configcontent']; 
			if($v['config_type']=="AD_CONFIG_END_DAY") $ad_day_arr['end_day'] = $v['configcontent']; 
		}
		return $ad_day_arr;
	}
	
	//获取默认广告刊例
	function get_rate_card_ad(){
		$this->tableName = 'settlement.ad_rate_cards';
		$model = new model();
		$defalt_rate = $model->table($this->tableName)
							 ->where(
								array('status'=>1,'is_defaulted'=>1,'is_disabled'=>0)
							 )
							 ->field('id,rate_card_name')
							 ->find();
        $total = $model->table('settlement.ad_advertisings')
					    ->where(
							array('rate_card_id'=>$defalt_rate['id'])
						 )
					   ->count();
		$limit = isset($get['limit']) ? $get['limit'] : 10;
        $param = http_build_query($get);
		 import('@.ORG.Page2');
        $Page = new Page($total, $limit, $param);
        $Page->rollPage = 1;
        $Page->setConfig('header', '篇记录');
        $Page->setConfig('first', '首页');
        $Page->setConfig('last', '尾页');		
		$ad_info = $model->table('settlement.ad_advertisings')
						 ->where(
							array('rate_card_id'=>$defalt_rate['id'])
						 )
						 ->field('id,advertising_name,advertising_code,cp_ad_num,cp_ad_demo')
						 ->limit($Page->firstRow . ',' . $Page->listRows)
						 ->select();
        foreach($ad_info as $k=>$v){
			$ad_info[$k]['rate_card_name'] = $defalt_rate['rate_card_name'];
		}						 
		return array($Page,$ad_info);
	}
	/*
	*设置投放日期
	*@desc $type(1起始日 2截止日) $day 天数
	*/
	function save_ad_day($type,$day){
		$this->tableName = 'pu_config';
		$model = new model();
		$where = array('status'=>1);
		if($type==1){
			$where['config_type'] = 'AD_CONFIG_START_DAY';			
		}else if($type==2){
			$where['config_type'] = 'AD_CONFIG_END_DAY';
		}
		$data['configcontent'] = $day;
		$save_info = $model->table($this->tableName)
						   ->where($where)
						   ->save($data);
		return $save_info;
							
	}
	
	/*
	* 设置广告数量和示意图
	*/
	function save_ad_info($id,$data){
		$this->tableName = 'settlement.ad_advertisings';
		$model = new model();
		$where = array('id'=>$id);
		if(isset($data['num'])) $save_data['cp_ad_num'] = $data['num'];
		if(isset($data['cp_ad_demo'])) $save_data['cp_ad_demo'] = $data['cp_ad_demo'];
		$save_info = $model->table($this->tableName)
						   ->where($where)
						   ->save($save_data);
						   //echo $model->getLastSql();
		return $save_info;
	}
	
}