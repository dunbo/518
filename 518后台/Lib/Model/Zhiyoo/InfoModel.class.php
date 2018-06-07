<?php 
// +----------------------------------------------------------------------
// | 智友功能配置（信息类型）
// +----------------------------------------------------------------------
// | Copyright (c) 2015.05.20 All rights reserved.
// +----------------------------------------------------------------------
// | Author: lizuofeng <892565767@qq.com>
// +----------------------------------------------------------------------
// | remarks: 信息类型管理的配置，所在位置：智友内容管理-智友功能配置
// +----------------------------------------------------------------------
class InfoModel extends AdvModel{
	var $connect_id = '';
	public function __construct(){
		parent::__construct();
		$co_Connect = C('DB_MARKETLOG');
		$this -> addConnect($co_Connect, $this->connect_id);
		$this -> switchConnect($this->connect_id);
	}
	//信息类型配置 显示功能
	public function getinfoconf(){
		$list = $this -> table('lzf_info_conf') -> where('status = 1') ->order('sort asc') -> select();
		foreach($list as $k=>$v){
			$list2[$v['id']] = $v;
		}
		return $list2;
	}
	//信息类型配置 添加功能
	public function infoadd(){
		$etime = date("Y-m-d H:i:s",time());
		$result = $this->execute("INSERT INTO  lzf_info_conf(sort,infotype,stime) values('{$_POST['px']}','{$_POST['content']}','{$etime}')");
		return $result; 
	}
	//信息类型配置 删除功能
	public function infodel(){
		$result = $this->execute('UPDATE lzf_info_conf SET status="0" WHERE id = "'.$_GET['id'].'"');
		return $result; 
	}
	//信息类型配置	 修改显示功能
	public function infoedit(){
		unset($_POST['id']);
		unset($_POST['__hash__']);
		unset($_POST['paixu']);
		foreach($_POST['ids'] as $k=>$v){
			//echo 'UPDATE lzf_info_conf SET sort='.$_POST['num'][$k].' WHERE id = "'.$v.'"';exit;
			$result = $this->execute('UPDATE lzf_info_conf SET sort='.$_POST['num'][$k].' WHERE id = "'.$v.'"');
			if($result){
				$status = 1;
			}
		}
		return $status; 
	}
	//信息类型配置	 修改处理功能
	public function infoeditdo(){
		$etime = date("Y-m-d H:i:s",time());
		//echo 'UPDATE lzf_info_conf SET sort="'.$_POST['px'].'",etime="'.$etime.'",infotype="'.$_POST['content'].'" WHERE id = "'.$_POST['id'].'"';exit;
		$result = $this->execute('UPDATE lzf_info_conf SET sort="'.$_POST['px'].'",etime="'.$etime.'",infotype="'.$_POST['content'].'" WHERE id = "'.$_POST['id'].'"');
		return $result; 
	}
	


}