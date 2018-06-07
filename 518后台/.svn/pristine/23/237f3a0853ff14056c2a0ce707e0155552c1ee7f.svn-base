<?php

class SaftyInforAction extends CommonAction{

	function safty_list(){
		$model = new Model();
		$where['status'] = 1;
		$where['version_dis'] = 2;
		$result = $model -> table('sj_soft_icon') -> where($where) -> select();
		$this -> assign('result',$result);
		$this -> display();
	
	}
	
	function add_safty_show(){
		$this -> display();
	}

	function add_safty(){
		$model = new Model();
		$criteria = $_POST['criteria'];
		$icon_name = $_POST['icon_name'];
		$describe = $_POST['describe'];
		$img_url_h = $_FILES['img_url_h'];
		$img_url_m = $_FILES['img_url_m'];
		if(!$describe){
			$this -> error('请填写描述');
		}
		if(!$criteria){
			$this -> error('请填写判断条件');
		}
		if(!$icon_name){
			$this -> error('请填写名称');
		}
		if(!$img_url_h['size']){
			$this -> error('请上传高分辨率图');
		}
		if(!$img_url_m['size']){
			$this -> error('请上传中分辨率图');
		}
		$path = date("Ym/d/");
		if($img_url_h['size'] && $img_url_m['size']){
			$config['multi_config']['img_url_h'] = array(
				'savepath' => UPLOAD_PATH. '/img/'. $path,
				'saveRule' => 'getmsec',
				'img_p_width' => '30',
				'img_p_height' => '30'
			);
			$config['multi_config']['img_url_m'] = array(
				'savepath' => UPLOAD_PATH. '/img/'. $path,
				'saveRule' => 'getmsec',
				'img_p_width' => '20',
				'img_p_height' => '20'
			);
			$list = $this->_uploadapk(0, $config);
			foreach($list['image'] as $key => $val){
				if($val['post_name'] == 'img_url_h'){
					$data['img_url_h'] = $val['url'];
				}
				if($val['post_name'] == 'img_url_m'){
					$data['img_url_m'] = $val['url'];
				}
			}
		}
		
		$data['icon_name'] = $icon_name;
		$data['status'] = 1;
		$data['criteria'] = $criteria;
		$data['version_dis'] = 2;
		$data['describe'] = $describe;
		$data['icon_update_time'] = time();
		
		$result = $model -> table('sj_soft_icon') -> add($data);
		
		if($result){
			$this -> writelog("已添加id为{$result}的V4.5的安全信息配置");
			$this->assign('jumpUrl', '/index.php/Sj/SaftyInfor/safty_list');
			$this->success('添加成功');
		}else{
			$this -> error('添加失败');
		}
	}
	
	function edit_safty_show(){
		$model = new Model();
		$id = $_GET['id'];
		$result = $model -> table('sj_soft_icon') -> where(array('id' => $id)) -> select();
		$this -> assign('result',$result);
		$this -> display();
	
	}
	
	function edit_safty(){
		$model = new Model();
		$criteria = $_POST['criteria'];
		$icon_name = $_POST['icon_name'];
		$describe = $_POST['describe'];
		$img_url_h = $_FILES['img_url_h'];
		$img_url_m = $_FILES['img_url_m'];
		if(!$describe){
			$this -> error('请填写描述');
		}
		if(!$criteria){
			$this -> error('请填写判断条件');
		}
		if(!$icon_name){
			$this -> error('请填写名称');
		}
		$path = date("Ym/d/");
		if($img_url_h['size'] || $img_url_m['size']){
			$config['multi_config']['img_url_h'] = array(
				'savepath' => UPLOAD_PATH. '/img/'. $path,
				'saveRule' => 'getmsec',
			);
			$config['multi_config']['img_url_m'] = array(
				'savepath' => UPLOAD_PATH. '/img/'. $path,
				'saveRule' => 'getmsec',
			);
			$list = $this->_uploadapk(0, $config);
			foreach($list['image'] as $key => $val){
				if($val['post_name'] == 'img_url_h'){
					$data['img_url_h'] = $val['url'];
				}
				if($val['post_name'] == 'img_url_m'){
					$data['img_url_m'] = $val['url'];
				}
			}
		}
		
		$data['icon_name'] = $icon_name;
		$data['criteria'] = $criteria;
		$data['describe'] = $describe;
		$data['icon_update_time'] = time();
		$id = $_POST['id'];
		$log_result = $this -> logcheck(array('id' => $id),'sj_soft_icon',array('criteria' => $criteria,'icon_name' => $icon_name,'describe' => $describe,'img_url_h' => $data['img_url_h'],'img_url_m' => $data['img_url_m']),$model);
		$result = $model -> table('sj_soft_icon') -> where(array('id' => $id)) -> save($data);
	
		if($result){
			$this -> writelog("已编辑id为{$result}的V4.5的安全信息配置".$log_result);
			$this->assign('jumpUrl', '/index.php/Sj/SaftyInfor/safty_list');
			$this->success('编辑成功');
		}else{
			$this -> error('编辑失败');
		}
	}


	function delete_safty(){
		$model = new Model();
		$id = $_GET['id'];
		$data['status'] = 0;
		$result = $model -> table('sj_soft_icon') -> where(array('id' => $id)) -> save($data);
	
		if($result){
			$this -> writelog("已删除id为{$result}的V4.5的安全信息配置");
			$this->assign('jumpUrl', '/index.php/Sj/SaftyInfor/safty_list');
			$this->success('删除成功');
		}else{
			$this -> error('删除失败');
		}
	}



}