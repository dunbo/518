<?php

class IncomemanageModel extends AdvModel{
	protected $connect_id = 20;
	protected $tablePrefix = 't_';
	public function __construct(){
		$co_Connect = C('DB_COOPERATIVE');
		$this -> addConnect($co_Connect, $this->connect_id);
		$this -> switchConnect($this->connect_id);
	}
	
	//更新激活收入
	public function update_active($where,$data){
		$this -> trueTableName = 't_active_income';
		$this -> flush();
		$affectid = $this -> where($where) -> save($data);
		return $affectid;
	}
	
	//更新广告收入
	public function update_ad($where,$data){
		$this -> trueTableName = 't_ad_income';
		$this -> flush();
		$affectid = $this -> where($where) -> save($data);
		return $affectid;
	}
	
	//更新游戏收入
	public function update_game($where,$data){
		$this -> trueTableName = 't_game_income';
		$this -> flush();
		$affectid = $this -> where($where) -> save($data);
		return $affectid;
	}
	
	//更新总收入
	public function update_all($where,$data){
		$this -> trueTableName = 't_all_income';
		$this ->  flush();
		$affectid = $this -> where($where) -> save($data);
		return $affectid;
	}
	
	//添加报警设置
	public function add_warning($data){
		$this -> trueTableName = 't_warning';
		$this -> flush();
		$affectid = $this -> add($data);
		return $affectid;
	}
	
	//更新报警设置
	public function update_warning($where,$data){
		$this -> trueTableName = 't_warning';
		$this -> flush();
		$affectid = $this -> where($where) -> save($data);
		return $affectid;
	}
	
	//添加业务审核信息
	public function add_operation($data){
		$this -> trueTableName = 't_settle_account';
		$this -> flush();
		$affectid = $this -> add($data);
		return $affectid;
	}
	
	//编辑业务审核信息
	public function update_operation($where,$data){
		$this -> trueTableName = 't_settle_account';
		$this -> flush();
		$affectid = $this -> where($where) -> save($data);
		return $affectid;
	}
	
	//编辑业务审核信息
	public function add_remark($data){
		$this -> trueTableName = 't_audit_remark';
		$this -> flush();
		$affectid = $this -> add($data);
		return $affectid;
	}
	
}