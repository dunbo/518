<?php

class ChanneluserModel extends AdvModel{
	protected $connect_id = 18;
	protected $tablePrefix = 't_';
	public function __construct(){
		$co_Connect = array(
			'dbms'     => 'mysql',
			'username' => 'root',
			'password' => 'southpark',
			'hostname' => '192.168.0.99',
			'hostport' => '3306',
			'database' => 'co_operations'
		);
		$this -> addConnect($co_Connect, $this->connect_id);
		$this -> switchConnect($this->connect_id);
	}
	
	//添加用户
	public function add_user($data){
		$this -> trueTableName = 't_user';
		$this -> fields = array('uid','username','passwd','account_type','create_person','create_tm','update_tm','status');
		$affectid = $this -> add($data);
		return $affectid;
	}
	//添加账号
	public function add_account($data){
		$this -> trueTableName = 't_account';
		$this -> fields = array('uid','account_name','account_type','bank_name','bank_account','bank_addr',
		'min_balance','create_tm','update_tm','status');
		$affectid = $this -> add($data);
		return $affectid;
	}
	
	//添加渠道
	public function add_channel($data){
		$this -> trueTableName = 't_user_channel';
		$this -> fields = array('id','uid','cid','create_uid','create_tm','update_tm','status');
		$affectid = $this -> add($data);
		return $affectid;
	}
	
	//添加渠道配置
	public function add_channel_config($data){
		$this -> trueTableName = 't_channel_config';
		$this -> fields = array('cid','active_price','active_switch','ad_price','ad_switch','max_down','ad_cut_pre','game_cut_pre','game_switch','create_tm','update_tm','status');
		$affectid = $this -> add($data);
		return $affectid;
	}
	
	//添加节点
	public function add_node($data){
		$this -> trueTableName = 't_user_node';
		$this -> fields = array('uid','node','create_tm','update_tm','status');
		$affectid = $this -> add($data);
		return $affectid;
	}
	
	//更新用户
	public function save_user($where,$save){
		$this -> trueTableName = 't_user';
		$this -> fields = array('uid','username','passwd','account_type','create_person','create_tm','update_tm','status');
		$affect = $this -> where($where) -> save($save);
		return $affect;
	}
	
	//更新账号
	public function save_account($where,$save){
		$this -> trueTableName = 't_account';
		$this -> fields = array('uid','account_name','account_type','bank_name','bank_account','bank_addr',
		'min_balance','create_tm','update_tm','status');
		$affect = $this -> where($where) -> save($save);
		return $affect;
	}
	
	//更新渠道
	public function save_channel($where,$save){
		$this -> trueTableName = 't_user_channel';
		$this -> fields = array('uid','cid','create_tm','update_tm','status');
		$affect = $this -> where($where) -> save($save);
		return $affect;
	}
	
	//更新渠道配置
	public function save_channel_config($where,$save){
		$this -> trueTableName = 't_channel_config';
		$this -> fields = array('cid','active_price','active_switch','ad_price','ad_switch','max_down','ad_cut_pre','game_cut_pre','game_switch','create_tm','update_tm','status');
		$affect = $this -> where($where) -> save($save);
		return $affect;
	}
	
	//更新节点
	public function save_node($where,$data){
		$this -> trueTableName = 't_user_node';
		$this -> fields = array('uid','node','update_tm','status');
		$affectid = $this -> where($where) -> save($data);
		return $affectid;
	}
	
	//更细默认配置系数
	public function save_code($where,$data){
		$this -> trueTableName = 't_init_config';
		$this -> fields = array('coefficient_name','coefficient_value','remark','updatetime');
		$affectid = $this -> where($where) -> save($data);
		return $affectid;
	}
	
	
}