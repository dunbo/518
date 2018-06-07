<?php

class CPMmanageAction extends CommonAction{


	function cpm_list(){
		$model = new Model();
		$count = $model -> table('sj_cpm_manage') -> where(array('status' => 1)) -> count();
		import("@.ORG.Page");
		$param = http_build_query($_GET);
		$Page = new Page($count, 20, $param);
		$result = $model -> table('sj_cpm_manage') -> where(array('status' => 1)) -> limit($Page->firstRow . ',' . $Page->listRows)->select();
		if(!$_GET['p']){
			$_GET['p'] = 1;
		}
		if(!$_GET['lr']){
			$_GET['lr'] = 20;
		}
		foreach($result as $key => $val){
			$val['num'] = $key + 1 + ($_GET['p'] - 1)*$_GET['lr'];
			$result[$key] = $val;
		}
		$Page->setConfig('header', '篇记录');
		$Page->setConfig('first', '<<');
		$Page->setConfig('last', '>>');
		$show = $Page->show();
		$this->assign("page", $show);
		$this -> assign('result',$result);
		$this -> display();
	}

	function add_cpm_show(){
		$this -> display();
	}

	function add_cpm(){
		$model = new Model();
		$active_name = $_POST['active_name'];
		$extend_range = $_POST['extend_range'];
		$pic_url = $_FILES['pic_url'];
		$jump_type = $_POST['jump_type'];
		$jump_des = $_POST['jump_des'];
		$start_tm = strtotime($_POST['start_tm']);
		$end_tm = strtotime($_POST['end_tm']);
		$have_where['_string'] = "start_tm <= {$end_tm} and end_tm >= {$start_tm} and status = 1";
		$have_result = $model -> table('sj_cpm_manage') -> where($have_where) -> select();
		if(!preg_match('/^\d+$/',$extend_range) && $extend_range){
			$this -> error("覆盖人数格式错误");
		}
		if($have_result){
			$this -> error("对不起，该时间段已有弹窗广告");
		}
		if(empty($active_name)){
			$this -> error("请输入活动名称");
		}

		if(!$pic_url['size']){
			$this -> error("请上传弹窗图片");
		}
		if(empty($jump_type)){
			$thiss -> error("请选择跳转类别");
		}

		if(empty($jump_des)){
			$this -> error("请输入跳转目的地");
		}
		
		if($jump_type == 2 && $jump_des){
			$have_result = $model -> table('sj_soft') -> where(array('package' => $jump_des,'hide' => 1)) -> select();
			if(!$have_result){
				$this -> error("包名不存在");
			}
		}
		
		if(!$start_tm || !$end_tm){
			$this -> error("请选择开始时间和结束时间");
		}
		if($start_tm > $end_tm){
			$this -> error('对不起，开始时间不能大于结束时间');
		}
		if($pic_url['size']){
			$path=date("Ym/d/",time());
			$config = array(
				'multi_config' => array(
					'pic_url' => array(
						'savepath' => UPLOAD_PATH. '/img/'. $path,
						'saveRule' => 'getmsec',
						'img_p_width' => '480',
						'img_p_height' => '580'
					),
				),
			);
			$list = $this -> _uploadapk(0, $config);
		}
	
		$data = array(
			'active_name' => $active_name,
			'extend_range' => $extend_range,
			'pic_url' => $list['image'][0]['url'],
			'jump_type' => $jump_type,
			'jump_des' => $jump_des,
			'start_tm' => $start_tm,
			'end_tm' => $end_tm,
			'update_tm' => time(),
			'status' => 1
		);
		
		$result = $model -> table('sj_cpm_manage') -> add($data);
	
		if($result){
			$this -> writelog("市场综合管理下已添加id为{$result}的弹窗广告");
			$this -> assign('jumpUrl','/index.php/Sj/CPMmanage/cpm_list');
			$this -> success('添加成功');
		}else{
			$this -> error('添加失败');
		}
		
	}
	
	function edit_cpm_show(){
		$model = new Model();
		$id = $_GET['id'];
		$result = $model -> table('sj_cpm_manage') -> where(array('id' => $id)) -> select();
		$this -> assign('result',$result);
		$this -> display();
	}
	
	function edit_cpm(){
		$model = new Model();
		$active_name = $_POST['active_name'];
		$extend_range = $_POST['extend_range'];
		$pic_url = $_FILES['pic_url'];
		$jump_type = $_POST['jump_type'];
		$jump_des = $_POST['jump_des'];
		$start_tm = strtotime($_POST['start_tm']);
		$end_tm = strtotime($_POST['end_tm']);
		$have_where['_string'] = "start_tm <= {$end_tm} and end_tm >= {$start_tm} and status = 1 and id != {$_POST['id']}";
		$have_result = $model -> table('sj_cpm_manage') -> where($have_where) -> select();
		if($have_result){
			$this -> error("对不起，该时间段已有弹窗广告");
		}
		if(!preg_match('/^\d+$/',$extend_range) && $extend_range){
			$this -> error("覆盖人数格式错误");
		}
		if(empty($active_name)){
			$this -> error("请输入活动名称");
		}

		if(empty($jump_type)){
			$thiss -> error("请选择跳转类别");
		}
		if(empty($jump_des)){
			$this -> error("请输入跳转目的地");
		}
		if($jump_type == 2 && $jump_des){
			$have_result = $model -> table('sj_soft') -> where(array('package' => $jump_des,'hide' => 1)) -> select();
			if(!$have_result){
				$this -> error("包名不存在");
			}
		}
		if(!$start_tm || !$end_tm){
			$this -> error("请选择开始时间和结束时间");
		}
		if($start_tm > $end_tm){
			$this -> error('对不起，开始时间不能大于结束时间');
		}
		if($pic_url['size']){
			$path=date("Ym/d/",time());
			$config = array(
				'multi_config' => array(
					'pic_url' => array(
						'savepath' => UPLOAD_PATH. '/img/'. $path,
						'saveRule' => 'getmsec',
						'img_p_width' => '480',
						'img_p_height' => '580'
					),
				),
			);
			$list = $this -> _uploadapk(0, $config);
			
		}
		if($pic_url['size']){
			$data = array(
				'active_name' => $active_name,
				'extend_range' => $extend_range,
				'pic_url' => $list['image'][0]['url'],
				'jump_type' => $jump_type,
				'jump_des' => $jump_des,
				'start_tm' => $start_tm,
				'end_tm' => $end_tm,
				'update_tm' => time(),
				'status' => 1
			);
		}else{
			$data = array(
				'active_name' => $active_name,
				'extend_range' => $extend_range,
				'jump_type' => $jump_type,
				'jump_des' => $jump_des,
				'start_tm' => $start_tm,
				'end_tm' => $end_tm,
				'update_tm' => time(),
				'status' => 1
			);
		}
		$log_result = $this -> logcheck(array('id' => $_POST['id']),'sj_cpm_manage',array('active_name' => $active_name,'extend_range' => $extend_range,'jump_type' => $jump_type,'jump_des' => $jump_des,'start_tm' => $start_tm,'end_tm' => $end_tm,'status' => 1),$model);

		$result = $model -> table('sj_cpm_manage') -> where(array('id' => $_POST['id'])) -> save($data);
	
		if($result){
			$this -> writelog("市场综合管理下已编辑id为{$_POST['id']}的弹窗广告".$log_result);
			$this -> assign('jumpUrl','/index.php/Sj/CPMmanage/cpm_list');
			$this -> success('编辑成功');
		}else{
			$this -> error('编辑失败');
		}
	
	}

	function delete_cpm(){
		$model = new Model();
		$id = $_GET['id'];
		$data = array(
			'status' => 0
		);
		$result = $model -> table('sj_cpm_manage') -> where(array('id' => $id)) -> save($data);
		if($result){
			$this -> writelog("市场综合管理下已删除id为{$_GET['id']}的弹窗广告");
			$this -> assign('jumpUrl','/index.php/Sj/CPMmanage/cpm_list');
			$this -> success('删除成功');
		}else{
			$this -> error('删除失败');
		}
	}














}
