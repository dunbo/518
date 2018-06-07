<?php
class ReasonAction extends CommonAction {
    /**
		* 内置原因展示
	*/
	function reason_list(){
		$reason = $_GET['reason_type'] ? $_GET['reason_type']:1;
		if(isset($_GET['p'])){
			$p =  $_GET['p'];
		}else{
			$p = 1;
		}
		if(isset($_GET['lr'])){
			$lr =  $_GET['lr'];
		}else{
			$lr = 10;
		}
		$model = new Model();
		import("@.ORG.Page");
		$count = $model -> table("dev_reason") -> where(array("status" => 1,"reason_type" => $reason ))->count();
		$param = http_build_query($_GET);
		$Page = new Page($count, 10, $param);
		$list = $model -> table("dev_reason") -> where(array("status" => 1,"reason_type" => $reason ))->order("id desc")->limit($Page->firstRow . ',' . $Page->listRows)->select(); 
		foreach($list as $k => $v){
			$list[$k]['create_date'] = date("Y-m-d",$v['create_tm']);
		} 
		$show = $Page->show();
		$this->assign("page", $show);
		$this->assign("reason", $reason);
		$this->assign("p", $p);
		$this->assign("lr", $lr);
		$this -> assign("list",$list);
		$this -> display();
	}
	/**
		内置原因添加
	*/
	function reason_add(){
		$model = new Model();
		$reason = $_GET['reason'] ? $_GET['reason'] : 1;
		if($_POST){
			$reason_add = $_POST['reason'];
			if($reason_add==1){
				$reason_info ="驳回";
			}else{
				$reason_info ="屏蔽";
			}
			$reason_list['content'] = $_POST['content'];
			$reason_list['status'] = 1;
			$reason_list['reason_type'] = $reason_add;
			$reason_list['create_tm'] = time();
			$result = $model -> table("dev_reason") -> add($reason_list);
			if(empty($result)){
				$this->error('添加内置原因失败');
			}else{
				$this->writelog("开发者管理-内置原因管理添加开发者{$reason_info}原因成功","dev_reason",$result);
				$this->assign("jumpUrl", "/index.php/" . GROUP_NAME . "/Reason/reason_list/reason_type/{$reason_add}/");
				$this->success('添加内置原因成功');
			}
		}
		$this -> assign("reason",$reason);
		$this -> display();
	}
	/**
		* 内置原因编辑
	*/
	function reason_edit(){
		$model = new Model();
		if($_POST){
			$reason_add = $_POST['reason']?$_POST['reason']:1;
			if($reason_add==1){
				$reason_info ="驳回";
			}else{
				$reason_info ="屏蔽";
			}
			$reason_list['content'] = $_POST['content']; 
			$reason_list['update_tm'] = time(); 
			$where = array(
				'id' => $_POST['id'],
				'status' => 1
			);
			$log = $this -> logcheck(array('id' =>$_POST['id']),'dev_reason',$reason_list,$model);
			$result = $model -> table("dev_reason") -> where($where) -> save($reason_list);
			if(empty($result)){
				$this->error('编辑内置原因失败');
			}else{
				$this->writelog("开发者管理-内置原因管理编辑id为{$_POST['id']}的开发者{$reason_info}原因成功".$log);
				$this->assign("jumpUrl", "/index.php/" . GROUP_NAME . "/Reason/reason_list/reason_type/{$reason_add}/");
				$this->success('编辑内置原因成功');
			}
		}else{
			$reason = $_GET['reason'] ? $_GET['reason'] : 1;
			if(isset($_GET['p'])){
			$p =  $_GET['p'];
			}else{
				$p = 1;
			}
			if(isset($_GET['lr'])){
				$lr =  $_GET['lr'];
			}else{
				$lr = 10;
			}
			$id = $_GET['id'];
			$reason_info = $model -> table("dev_reason") -> where(array("status" => 1,"id" => $id))->find();
			$this -> assign("reason_info",$reason_info);
			$this -> assign("reason",$reason);
			$this -> assign("id",$id);
			$this -> display();
		}
	}
	/**
		* 内置原因删除
	*/
	function reason_del(){
		$model = new Model();
		$reason = $_GET['reason']?$_GET['reason']:1;
		$p = $_GET['p']?$_GET['p']:1;
		$lr = $_GET['lr']?$_GET['lr']:10;
		if($reason==1){
			$reason_info ="驳回";
		}else{
			$reason_info ="屏蔽";
		}
		$id = $_GET['id'];
		$reason_list['status'] = 0;
		$reason_list['update_tm'] = time();
		$affect = $model -> table("dev_reason")-> where(array("status" =>1,"id" => $id))->save($reason_list);
		if($affect){
			$this->writelog("开发者管理-内置原因管理删除了id为{$_GET['id']}的开发者{$reason_info}原因成功","dev_reason",$id);
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME."/Reason/reason_list/reason_type/{$reason}/p/{$p}/lr/{$lr}");
			$this->success('删除成功');
		}else{
			$this->error('删除失败');
		}
		
	}
}
?>