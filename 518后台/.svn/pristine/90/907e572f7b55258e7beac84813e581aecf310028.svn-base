<?php

class AdactivityAction extends  CommonAction{


	function activity_list(){
		$model = D('sendNum.Activity');	
		$where = array(
			'activate_type' => 3,
			'status' => 1
		);
		$this->check_where($where, 'ap_name', 'isset', 'like');
		$limit = isset($_GET['limit']) ? $_GET['limit'] : 10;
		list($result,$total,$Page) = $model -> get_activity_page($where,$limit);
		if(EVN == '9test' || EVN == 'prod'){
			$my_host = 'http://m.anzhi.com';
		}elseif(EVN == '518test'){
			$my_host = 'http://118.26.203.23';
		}
		$this -> assign('my_host',$my_host);
		$this -> assign('page', $Page->show());		
		$this -> assign('result',$result);
		$this -> display();
	
	}
	
	function add_activity_show(){
		$this -> display();
	}
	
	function add_activity_do(){
		$model = new Model();
		$ap_name = trim($_POST['ap_name']);
		$ap_type = $_POST['ap_type'];
		$ap_rule = $_POST['ap_rule'];
		$back_top = 2;
		$ap_notice = $_POST['ap_notice'];
		$have_been = $model -> table('sj_activity_page') -> where(array('ap_name' => $ap_name,'status' => 1)) -> select();
		if($have_been){
			$this -> error("该活动名称已存在");
		}
		$ap_pic = $_FILES['ap_pic'];
		if($ap_pic){
			$path=date("Ym/d/",time());
			$config = array(
				'multi_config' => array(
					'ap_pic' => array(
						'savepath' => UPLOAD_PATH. '/img/'. $path,
						'saveRule' => 'getmsec',
						'img_p_size' => 1024*50,
					),
				)
			);
			$lists=$this->_uploadapk(0, $config);
			$ap_imgurl = $lists['image'][0]['url'];
		}else{
			$this -> error("请上传活动图片");
		}

		$data = array(
			'ap_name' => $ap_name,
			'ap_type' => $ap_type,
			'ap_rule' => str_replace("\n","<br />",$ap_rule),
			'ap_imgurl' => $ap_imgurl,
			'back_top' => $back_top,
			'activate_type' => 3,
			'ap_notice' => str_replace("\n","<br />",$ap_notice),
			'ap_ctm' => time(),
			'ap_utm' => time()
		);
		
		$result = $model -> table('sj_activity_page') -> add($data);
		$p = $_POST['p'];
		$lr = $_POST['lr'];
		if($result){
			$page = "/adactivity_".$result.".html";
			$my_result = $model -> table('sj_activity_page') -> where(array('ap_id' => $result)) -> save(array('ap_link' => $page));
			$this -> writelog("已添加id为{$result}的广告活动");
			$this -> assign('jumpUrl',"/index.php/Sendnum/Adactivity/activity_list");
			$this -> success("添加成功");
		}else{
			$this -> error("添加失败");
		}
	}
	
	function edit_activity_show(){
		$model = new Model();
		$id = $_GET['id'];
		$result = $model -> table('sj_activity_page') -> where(array('ap_id' => $id)) -> find();
		$result['ap_rule'] = str_replace("<br />","\n",$result['ap_rule']);
		$result['ap_notice'] = str_replace("<br />","\n",$result['ap_notice']);
		$this -> assign('result',$result);
		$this -> display();
	}
	
	function edit_activity_do(){
		$model = new Model();
		$id = $_POST['id'];
		$ap_name = trim($_POST['ap_name']);
		$ap_rule = $_POST['ap_rule'];
		$ap_notice = $_POST['ap_notice'];
		$have_where['_string'] = "ap_name = {$ap_name} and ap_id != {$id} and status = 1";
		$have_been = $model -> table('sj_activity_page') -> where($have_where) -> select();
		if($have_been){
			$this -> error("该活动名称已存在");
		}
		
		$ap_pic = $_FILES['ap_pic'];
		if($ap_pic['size']){
			$path=date("Ym/d/",time());
			$config = array(
				'multi_config' => array(
					'ap_pic' => array(
						'savepath' => UPLOAD_PATH. '/img/'. $path,
						'saveRule' => 'getmsec',
						'img_p_size' => 1024*50,
					),
				)
			);
			$lists=$this->_uploadapk(0, $config);
			$ap_imgurl = $lists['image'][0]['url'];
			$data['ap_imgurl'] = $ap_imgurl;
		}
		$data['ap_name'] = $ap_name;
		$data['ap_rule'] = str_replace("\n","<br />",$ap_rule);
		$data['ap_notice'] = str_replace("\n","<br />",$ap_notice);
		$data['ap_utm'] = time();
		
		$log_result = $this -> logcheck(array('ap_id' => $id),'sj_activity_page',$data,$model);
		$result = $model -> table('sj_activity_page') -> where(array('ap_id' => $id)) -> save($data);

		if($result){
			$this -> writelog("已编辑id为{$id}的广告活动".$log_result);
			$this -> assign('jumpUrl',"/index.php/Sendnum/Adactivity/activity_list");
			$this -> success("编辑成功");
		}else{
			$this -> error("编辑失败");
		}
	
	
	
	}
	
	
	function del_activity(){
		$model = new Model();
		$id = $_GET['id'];
		$result = $model -> table('sj_activity_page') -> where(array('ap_id' => $id)) -> save(array('status' => 0));
		if($result){
			$this -> writelog("已删除ap_id为{$id}的广告活动");
			$this -> assign('jumpUrl',"/index.php/Sendnum/Adactivity/activity_list");
			$this -> success("删除成功");
		}else{
			$this -> error("删除失败");
		}
	}









}