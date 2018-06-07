<?php

class channel_cooperation_pModel extends AdvModel {
	protected $connect_id = 1811;
	protected $tablePrefix = 'co_';
	public function __construct(){
		parent::__construct();
		$co_Connect = C('DB_CO_CHANNEL_P');
		$this -> addConnect($co_Connect, $this->connect_id);
		$this -> switchConnect($this->connect_id);
	}

	//添加用户
	public function add_client($data){
		$this -> trueTableName = 'co_client_list';
		$this -> flush();
		$affectid = $this -> add($data);
		return $affectid;
	}
	
	//编辑用户
	public function edit_client($where,$data){
		$this -> trueTableName = 'co_client_list';
		$this -> flush();
		$affectid = $this -> where($where) -> save($data);
		return $affectid;
	}

	//添加附件临时文件
	public function add_tmp_affix($data){
		$this -> trueTableName = 'co_tmp_affix';
		$this -> flush();
		$affectid = $this -> add($data);
		return $affectid;
	}
	
	//编辑附件临时文件
	public function edit_tmp_affix($where,$data){
		$this -> trueTableName = 'co_tmp_affix';
		$this -> flush();
		$affectid = $this -> where($where) -> save($data);
		return $affectid;
	}
	
	//添加附件
	public function add_affix($data){
		$this -> trueTableName = 'co_affix';
		$this -> flush();
		$affectid = $this -> add($data);
		return $affectid;
	}
	
	//编辑附件
	public function edit_affix($where,$data){
		$this -> trueTableName = 'co_affix';
		$this -> flush();
		$affectid = $this -> where($where) -> save($data);
		return $affectid;
	}
	
	//添加渠道
	public function add_channel($data){
		$this -> trueTableName = 'co_client_channel';
		$this -> flush();
		$affectid = $this -> add($data);
		return $affectid;
	}
	
	//编辑渠道
	public function edit_channel($where,$data){
		$this -> trueTableName = 'sj_channel';
		$this -> flush();
		$affectid = $this -> save($where,$data);
		return $affectid;
	}
	public function check_general_switch_client_id(){
		$model = new Model();
		$admin_id = $_SESSION['admin']['admin_id'];
		$count_power_result = $model -> table('sj_admin_filter') -> where(array('source_type' => 1,'source_value' => $admin_id,'target_type' => 9)) -> find();
		
		if($count_power_result['filter_type'] == 1){
			return false;
		} else {
			//屏蔽用户开关新增
			$admin_filter_result = $model -> table('sj_admin_filter') -> where(array('source_type' => 1,'source_value' => $admin_id,'target_type' => 2,'filter_type' => 2)) -> field('target_value') -> select();
			foreach($admin_filter_result as $key => $val){
				$admin_cid[] = $val['target_value'];
			}

			$all_client = $this -> table('co_client_list') -> order('id') -> select();

			if($admin_cid){
				foreach($all_client as $key => $val){
					$client_channel_result = $this -> table('co_client_channel') -> where(array('client_id' => $val['id'])) -> select();
					$my_cid_power = array();
					foreach($client_channel_result as $k => $v){
						$my_cid_power[] = $v['cid'];
					}
					if(!array_diff($my_cid_power,$admin_cid)){
						$my_client_id_str .= $val['id'].',';
					}
				}
			}else{
				$my_client_id = '';
			}
			$my_client_id = substr($my_client_id_str,0,-1);
			return $my_client_id?$my_client_id:'-1';
		}
		//屏蔽用户开关新增	
	}
	public function check_general_switch_cid($admin_id){
		//屏蔽用户开关新增
		$model = new Model();
		
		$count_power_result = $model -> table('sj_admin_filter') -> where(array('source_type' => 1,'source_value' => $admin_id,'target_type' => 9)) -> find();
		if($count_power_result['filter_type'] == 1){
			return false;
		} else {
			$my_channel_where['_string'] = "source_type = 1 and source_value = {$admin_id} and target_type = 2 and filter_type = 2";
			$my_channel_result = $model -> table('sj_admin_filter') -> where($my_channel_where) -> select();

			foreach($my_channel_result as $key => $val){
				$the_channel .= $val['target_value'].',';
			}
			$channel_id = substr($the_channel,0,-1);
			return $channel_id?$channel_id:'-1';
		}
	}
}