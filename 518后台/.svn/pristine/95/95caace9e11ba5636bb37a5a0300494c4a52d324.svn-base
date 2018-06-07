<?php 
// +----------------------------------------------------------------------
// | 智友功能配置（下载配置）
// +----------------------------------------------------------------------
// | Copyright (c) 2015.05.20 All rights reserved.
// +----------------------------------------------------------------------
// | Author: lizuofeng <892565767@qq.com>
// +----------------------------------------------------------------------
// | remarks: 下载配置管理所在位置：智友内容管理-智友功能配置
// +----------------------------------------------------------------------
class DownloadModel extends AdvModel{
	var $connect_id = '';
	public function __construct(){
		parent::__construct();
		$co_Connect = C('DB_MARKETLOG');
		$this -> addConnect($co_Connect, $this->connect_id);
		$this -> switchConnect($this->connect_id);
		//$list = $this -> table('lzf_download_conf') -> where('') -> select();
		//print_r($list);
	}
	
	public function getdownloadconf(){
		$list = $this -> table('lzf_download_conf') -> where('') -> select();
		foreach($list as $info){
			$blacklist[$info['fid']] = $info;
		}
		return $list;
	}
	
	public function editdownloadconf(){
		unset($_POST['id']);
		unset($_POST['__hash__']);
		unset($_POST['button']);
		//$status1 = 0;
		foreach($_POST as $k=>$v){
			$result = $this->execute('UPDATE lzf_download_conf SET value='.$v.' WHERE title = "'.$k.'"');
			if($result){
				$status1 = 1;
			};
		}
		return $status1;
	}
	
	


}