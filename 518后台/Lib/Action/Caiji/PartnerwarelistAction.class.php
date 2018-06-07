<?php

class PartnerwarelistAction extends CommonAction {
	
	function partnersoftlist(){
		import("@.ORG.Page");
		$model = new Model();
		$where['status'] = 1;
		
		$soft_all = $model -> table('cj_partner') -> where($where) -> select();
		
		$count = count($soft_all);
        $page = new Page($count, 15);
		
		$soft_list = $model -> table('cj_partner') -> where($where) -> order('id desc')->limit($page->firstRow.','.$page->listRows) -> select();
	
		$page->setConfig('header','篇记录');
        $page->setConfig('first','<<');
        $page->setConfig('last','>>');
        $show =$page->show();
        $this->assign("page", $show);
		$this -> assign('soft_list',$soft_list);
		$this -> display('partner_list');
	}

	function add_soft_go(){
		$model = new Model();
		$this -> display('add_soft');
	}
	function add_soft(){
		$model = new Model();
		$soft_name = $_GET['soft_name'];
		$package = $_GET['package'];
		$partner = $_GET['partner'];
        $co_start_time = strtotime(trim($_GET['co_start_time']));
        $co_end_time = strtotime(trim($_GET['co_end_time'])) + 86399;
		$add_time = time();
		$where['status'] = 1;
        
        if (!$soft_name)
            $this->error("软件名称不能为空");
        if (!$package)
            $this->error("包名不能为空");
        if (!$partner)
            $this->error("合作方不能为空");
        if (!$co_start_time)
            $this->error("合作开始时间不能为空");
        if (!$co_end_time)
            $this->error("合作结束时间不能为空");
        if ($co_start_time >= $co_end_time)
            $this -> error("合作开始时间需小于合作结束时间！");
        if ($co_end_time <= $add_time)
            $this -> error("合作结束时间不能小于当前时间！");
	
		$where['package'] = $package;
		$result = $model -> table('sj_soft') -> where($where) -> select();
		$where_go['packagename'] = $package;
		$where_go['status'] = 1;
		$result_go = $model -> table('cj_partner') -> where($where_go) -> select();
		
		if($result_go){
			$this -> error("亲，此软件在合作软件中已存在哦！");
		}
		
		if($result == ''){
			$this -> error("亲，此软件不存在于软件列表哦！");
		}else{
			$data['soft_name'] = $soft_name;
			$data['packagename'] = $package;
			$data['partner'] = $partner;
            $data['co_start_time'] = $co_start_time;
            $data['co_end_time'] = $co_end_time;
			$data['add_time'] = $add_time;
            $data['operator'] = $_SESSION['admin']['admin_user_name'];
			$data['status'] = 1;
			$affect = $model -> table('cj_partner') -> add($data);

			if($affect){
				$this -> writelog("已添加软件采集_合作软件，软件名为".$soft_name.",软件包名为".$package."",'cj_partner',$affect,__ACTION__ ,"","add");
				$this -> assign('jumpUrl',"__URL__/partnersoftlist");
				$this -> success("添加成功");
			}else{
                var_dump($model->getlastsql());
				$this -> error("添加失败");
			}
		}
	}
	
	function delete_soft(){
		$model = new Model();
		$soft_id = $_GET['id'];
		$where['id'] = $soft_id;
        $data['operator'] = $_SESSION['admin']['admin_user_name'];
		$data['status'] = 0;
		$affect = $model -> table('cj_partner') -> where($where) -> save($data);
		if($affect){
			$this -> writelog("已删除软件采集_合作软件id为".$soft_id."的合作软件",'cj_partner',$soft_id,__ACTION__ ,"","del");
			$this -> assign('jumpUrl',"__URL__/partnersoftlist");
			$this -> success("删除成功");
		}else{
			$this -> error("删除失败");
		}
	}
	
	function update_list(){
		$model = new Model();
		$soft_id = $_GET['id'];
		$where['id'] = $soft_id;
		$where['status'] = 1;
		$result = $model -> table('cj_partner') -> where($where) -> select();
		$this -> assign('result',$result);
		$this -> display('update_soft');
	}
	
	function update_soft(){
		$model = new Model();
		$soft_id = $_GET['id'];
		$data['soft_name'] = $_GET['soft_name'];
		$data['packagename'] = $_GET['package'];
		$data['partner'] = $_GET['partner'];
        $co_start_time = strtotime(trim($_GET['co_start_time']));
        $co_end_time = strtotime(trim($_GET['co_end_time'])) + 86399;
        $data['co_start_time'] = $co_start_time;
        $data['co_end_time'] = $co_end_time;
        $data['operator'] = $_SESSION['admin']['admin_user_name'];
		$where['id'] = $soft_id;
		$where['status'] = 1;
        
        if (!$data['soft_name'])
            $this->error("软件名称不能为空");
        if (!$data['packagename'])
            $this->error("包名不能为空");
        if (!$data['partner'])
            $this->error("合作方不能为空");
        if (!$co_start_time)
            $this->error("合作开始时间不能为空");
        if (!$co_end_time)
            $this->error("合作结束时间不能为空");
        if ($co_start_time >= $co_end_time) {
            $this -> error("合作开始时间需小于合作结束时间！");
        }
        if ($co_end_time <= time())
            $this -> error("合作结束时间不能小于当前时间！");
        
		$result = $model -> table('cj_partner') -> where($where) -> select();
		$where_go['packagename'] = $data['packagename'];
		$result_go = $model -> table('cj_partner') -> where($where_go) -> select();

		if($result_go == ''){
			$this -> error("对不起，此软件不存在于软件列表");
		}
		if($result[0]['packagename'] != $_GET['package']){
			$no = $model -> table('cj_partner') -> where(array('packagename' => $_GET['package'],'status' => 1)) -> select();
		
			if($no){
				$this -> error("亲，此软件在合作软件中已存在哦！");
			}
		}else if($result[0]['soft_name'] == $_GET['soft_name'] && $result[0]['packagename'] == $_GET['package'] && $result[0]['partner'] == $_GET['partner']
            && $result[0]['co_start_time'] == $co_start_time && $result[0]['co_end_time'] == $co_end_time){
			$this -> assign('jumpUrl',"__URL__/partnersoftlist");
			$this -> success("编辑成功");
		}
		$log_result = $this->logcheck($where,'cj_partner',$data,$model);
		$affect = $model -> table('cj_partner') -> where($where) -> save($data);
		if($affect){
			$this -> writelog("已更新软件采集_合作软件id为{$soft_id}.{$log_result}",'cj_partner',$soft_id,__ACTION__ ,"","edit");
			$this -> assign('jumpUrl',"__URL__/partnersoftlist");
			$this -> success("编辑成功");
		}else{
			$this -> error("编辑失败");
		}
	}
}

?>