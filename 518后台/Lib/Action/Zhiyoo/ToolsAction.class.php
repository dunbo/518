<?php 
// +----------------------------------------------------------------------
// | 智友功能配置（下载配置、信息类型）
// +----------------------------------------------------------------------
// | Copyright (c) 2015.05.20 All rights reserved.
// +----------------------------------------------------------------------
// | Author: lizuofeng <892565767@qq.com>
// +----------------------------------------------------------------------
// | remarks: 下载配置管理，信息类型管理的配置，所在位置：智友内容管理-智友功能配置
// +----------------------------------------------------------------------
Class ToolsAction extends CommonAction{
	//下载配置管理
	function DownloadConf(){
		define('CAIJI_ATTACHMENT_HOST', 'http://192.168.0.99/testdata/data3');
		define('BBSLUNTAN_HOST','http://forum.anzhi.com/forum.php?mod=post&action=edit');
		
		$download = D('Zhiyoo.Download');
		$status = $download->getdownloadconf();//下载配置管理 显示功能
		//下载配置管理 修改功能
		if(isset($_POST['id']) && $_POST['id']=='edit'){
			$status1 = $download->editdownloadconf();
			if($status1){
				foreach($status as $v){
					if($_POST[$v['title']] != $v['value'] ){
						if($v['value']==1){
							if(in_array($v['id'],array('3','4'))){
								$v['value'] = '第三方浏览器改为内嵌浏览器';
							}else{
								$v['value']='开 改为 关';
							}

						}else{
							if(in_array($v['id'],array('3','4'))){
								$v['value'] = '内嵌浏览器改为第三方浏览器';
							}else{
								$v['value']='关 改为 开';
							}
						}
						$info .= 'ID为'.$v['id'].$v['value']."&nbsp;,";
						$this -> writelog("智友内容管理-智友功能配置-下载配置管理=>{$info}","lzf_download_conf",$v['id'],__ACTION__ ,"","edit");
					}
				}

				$this -> success("修改成功");
			}else{
				$this -> error("您没有任何修改");
			}
		}
		$this -> assign('status',$status);
		$this -> assign('status1',$status1);
		$this -> display();
	}
	//信息类型配置
	function InfoConf(){
		$download = D('Zhiyoo.Info');
		$list = $download->getinfoconf();//默认显示功能
		$disabled = 'disabled';	
		$bjpx = '';	
		$bcpx = 'none';	
		if($_GET['id']=='px'){
			$disabled = '';	
			$bcpx = '';	//保存排序 显示/隐藏
			$bjpx = 'none';	//编辑排序 显示/隐藏
		}
		//修改功能
		/*if(isset($_POST['id']) && $_POST['id']=='edit'){
			$status1 = $download->editdownloadconf();
			if($status1){
				$this->assign('jumpUrl',SITE_URL.'/index.php/Zhiyoo/Tools/InfoConf1');
				$this -> success("修改成功");
			}else{
				$this -> error("您没有任何修改");
			}
		}*/
		
		$this -> assign('disabled',$disabled);
		$this -> assign('bjpx',$bjpx);//编辑排序 显示/隐藏
		$this -> assign('bcpx',$bcpx);//编辑排序 显示/隐藏
		$this -> assign('list',$list);
		$this -> display();
	}
	//信息类型配置 添加
	function InfoAdd(){
		$download = D('Zhiyoo.Info');
		//添加功能
		if(isset($_POST['id']) && $_POST['id']=='do'){
			$status = $download->infoadd();
			$id = $download->getLastInsID();
			if($status){
				$this->writelog("智友内容管理-智友功能配置-信息类型配置=>添加信息","lzf_info_conf",$id,__ACTION__ ,"","add");
				$this->assign( 'jumpUrl',SITE_URL.'/index.php/Zhiyoo/Tools/InfoConf');
				$this -> success("添加成功");
			}else{
				$this -> error("添加失败");
			}
		}
		$this -> display();
	}
	//信息类型	修改
	function InfoEdit(){
		$download = D('Zhiyoo.Info');
		//修改功能
		$list = $download->getinfoconf();//默认查询出数据

		if(isset($_POST['action']) && $_POST['action']=='do'){
			$status = $download->infoeditdo();
			if($status){
				$this->writelog("智友内容管理-智友功能配置-信息类型配置=>修改信息类型","lzf_info_conf",$_POST['id'],__ACTION__ ,"","edit");
				$this->assign('jumpUrl',SITE_URL.'/index.php/Zhiyoo/Tools/InfoConf');
				$this -> success("修改成功");
			}else{
				$this -> error("没有修改");
			}
		}
		//修改排序
		if(isset($_POST['id']) && $_POST['id']=='edit'){
			$status = $download->infoedit();
			$ids = implode(',',$_POST['ids']);
			if($status){
				$this->writelog("智友内容管理-智友功能配置-信息类型配置=>修改排序","lzf_info_conf",$ids,__ACTION__ ,"","edit");
				$this->assign('jumpUrl',SITE_URL.'/index.php/Zhiyoo/Tools/InfoConf');
				$this -> success("修改成功");
			}else{
				$this -> error("没有修改");
			}
		}
		$this->assign("list",$list);
		$this -> display();
	}
	//信息类型	删除
	function InfoDel(){
		$download = D('Zhiyoo.Info');
		//修改功能
		if(isset($_GET['id'])){
			$status = $download->infodel();
			if($status){
				$this->writelog("智友内容管理-智友功能配置-信息类型配置=>删除信息","lzf_info_conf",$_GET['id'],__ACTION__ ,"","del");
				$this -> success("删除成功");
			}else{
				$this -> error("删除失败");
			}
		}else{
			$this->error("非法访问");
		}
	}
}