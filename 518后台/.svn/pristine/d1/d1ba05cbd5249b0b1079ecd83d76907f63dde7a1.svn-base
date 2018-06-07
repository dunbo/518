<?php
class CornerMarkAction extends CommonAction{
	function cornerMark_list(){
		$corner=M("corner_mark");
		$list=$corner->where(array("status"=>1))->select();

		//过滤
		$this->gpcFilter($list);

		$this->assign("list",$list);
		$this->display();
	}
	function cornerMark_add(){
		$corner=M("corner_mark");
		if($_POST){
			$data['name']=trim($_POST['name']);
			if (empty($data['name']) || $data['name']==''){
				$this->error("请填写角标名称！");
				return ;
			}
			$data['status']=1;
			$data['create_tm']=time();
			$data['update_tm']=time();
			$tmp_filename_h = $_FILES["img_url_h"]["name"];
			$tmp_filename_m = $_FILES["img_url_m"]["name"];
			$tmp_filename_xh_new = $_FILES["img_url_new_xh"]["name"];
			$tmp_filename_h_new = $_FILES["img_url_new_h"]["name"];
			$tmp_filename_m_new = $_FILES["img_url_new_m"]["name"];
			if(empty($tmp_filename_h)||empty($tmp_filename_m) ||empty($tmp_filename_xh_new)|| empty($tmp_filename_h_new)||empty($tmp_filename_m_new)){
				$this->error("请上传图片");
				return ;
			}
			$path=date("Ym/d/",time());
			$config = array(
					'multi_config' => array(
						'img_url_h' => array(
							'savepath' => UPLOAD_PATH. '/img/'. $path,
							'saveRule' => 'getmsec',
							//'img_p_size' =>  1024*50,
						),
						'img_url_m' => array(
							'savepath' => UPLOAD_PATH. '/img/'. $path,
							'saveRule' => 'getmsec',
							//'img_p_size' =>  1024*50,
						),
						'img_url_new_xh' => array(
							'savepath' => UPLOAD_PATH. '/img/'. $path,
							'saveRule' => 'getmsec',
							//'img_p_size' =>  1024*50,
						),
						'img_url_new_h' => array(
							'savepath' => UPLOAD_PATH. '/img/'. $path,
							'saveRule' => 'getmsec',
							//'img_p_size' =>  1024*50,
						),
						'img_url_new_m' => array(
							'savepath' => UPLOAD_PATH. '/img/'. $path,
							'saveRule' => 'getmsec',
							//'img_p_size' =>  1024*50,
						),
					),
				);
			$lists=$this->_uploadapk(0, $config);
				foreach($lists['image'] as $val) {
					if ($val['post_name'] == 'img_url_h') {
						$data['img_url_h']= $val['url'];
					}
					if ($val['post_name'] == 'img_url_m') {
						$data['img_url_m']= $val['url'];
					}
					if ($val['post_name'] == 'img_url_new_xh') {
						$data['img_url_new_xh']= $val['url'];
					}
					if ($val['post_name'] == 'img_url_new_h') {
						$data['img_url_new_h']= $val['url'];
					}
					if ($val['post_name'] == 'img_url_new_m') {
						$data['img_url_new_m']= $val['url'];
					}
				}
			if($id=$corner->add($data)){
				$this->writelog('增加了名称ID为['.$id.'],名称为['.$data['name'].']的角标', 'sj_corner_mark', $id,__ACTION__ ,"","add");
				$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/CornerMark/cornerMark_list');
				$this->success("添加角标成功！");
			}else{
				$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/CornerMark/cornerMark_list');
				$this->error("添加角标失败,发生错误！");
			}
		}else{
			$this->display();
		}
	}	
	function cornerMark_edit(){
		$corner=M("corner_mark");
		if($_POST){
			$config = array(
				'multi_config' => array(),
			);
			$path=date("Ym/d/",time());
			$data['name']=$_POST['name'];
			if (empty($data['name']) || $data['name']==''){
				$this->error("请填写角标名称！");
				return ;
			}
			$where['id']=$_POST['id'];
			$where['status']=1;
			$data['update_tm']=time();
			$tmp_filename_h = $_FILES["img_url_h"]["size"];
			$tmp_filename_m = $_FILES["img_url_m"]["size"];
			$tmp_filename_xh_new = $_FILES["img_url_new_xh"]["size"];
			$tmp_filename_h_new = $_FILES["img_url_new_h"]["size"];
			$tmp_filename_m_new = $_FILES["img_url_new_m"]["size"];
			if ($tmp_filename_h) {
				$config['multi_config']['img_url_h'] = array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					//'img_p_size' =>  1024*50,
				);
			}
			if ($tmp_filename_m) {
				$config['multi_config']['img_url_m'] = array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					//'img_p_size' =>  1024*50,
				);
			}
			if ($tmp_filename_xh_new) {
				$config['multi_config']['img_url_new_xh'] = array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					//'img_p_size' =>  1024*50,
				);
			}
			if ($tmp_filename_h_new) {
				$config['multi_config']['img_url_new_h'] = array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					//'img_p_size' =>  1024*50,
				);
			}
			if ($tmp_filename_m_new) {
				$config['multi_config']['img_url_new_m'] = array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					//'img_p_size' =>  1024*50,
				);
			}
			if(!empty($config['multi_config'])){
				$lists=$this->_uploadapk(0, $config);
				foreach($lists['image'] as $val) {
					if ($val['post_name'] == 'img_url_h') {
						$data['img_url_h']=$val['url'];
					}
					if ($val['post_name'] == 'img_url_m') {
						$data['img_url_m']= $val['url'];
					}
					if ($val['post_name'] == 'img_url_new_xh') {
						$data['img_url_new_xh']=$val['url'];
					}
					if ($val['post_name'] == 'img_url_new_h') {
						$data['img_url_new_h']=$val['url'];
					}
					if ($val['post_name'] == 'img_url_new_m') {
						$data['img_url_new_m']= $val['url'];
					}
				}
			}

			$log = $this->logcheck(array('id'=>$_POST['id']),'sj_corner_mark',$data,$corner);
			if($corner->where($where)->save($data)){
				//$this->writelog('修改了名称ID为['.$where[id].']的角标', 'sj_corner_mark', $where['id']);
				$this->writelog("软件管理-软件详管理角标编辑".$log, 'sj_corner_mark', $where['id'],__ACTION__ ,"","edit");
				$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/CornerMark/cornerMark_list');
				$this->success("修改角标成功！");
			}else{
				$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/CornerMark/cornerMark_list');
				$this->error("修改角标失败,发生错误！");
			}
		}else{
			if(!$_GET['id']){
				$this->error("修改出错！！！");
			}
			$where['id']=$_GET['id'];
			$where['status']=1;
			$list=$corner->where($where)->find();
			$this->assign("list",$list);
			$this->display();
		}
	}
	function cornerMark_del(){
		$corner=M("corner_mark");
		$id=$_GET['id'];
		$affect = $corner -> query("update __TABLE__ set status = 0 where id = " .$id);
		$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/CornerMark/cornerMark_list');
		$this->writelog("删除角标ID为".$id."的软件信息", 'sj_corner_mark', $id,__ACTION__ ,"","del");
		$this->success('删除成功');
	}
	/*
	//软件管理__软件详细角标列表  庄超滨 2012.11.15 
	function soft_cornerMark_list(){
		$soft_corner=M("soft_corner_mark");
		$list=$soft_corner->where("status=1")->select();
		$this->assign("list",$list);
		$this->display();
	}
	//软件管理__软件详细角标__添加  庄超滨 2012.11.15 
	function soft_cornerMark_add(){
		$soft_corner=M("soft_corner_mark");
		if($_POST){
			$data['name']=$_POST['name'];
			$data['status']=1;
			$data['create_tm']=time();
			$data['update_tm']=time();
			$tmp_filename_h = $_FILES["img_url_h"]["size"];
			$tmp_filename_m = $_FILES["img_url_m"]["size"];
			if(empty($tmp_filename_h)||empty($tmp_filename_m)){
				$this->error("请上传图片");
			}
			$path=date("Ym/d/",time());
			$config = array(
				'multi_config' => array(),
			);
			if(!empty($_FILES['img_url_h']['size'])){
				$config['multi_config']['img_url_h'] = array(
					'savepath' => UPLOAD_PATH . '/image/' . $path,
					'saveRule' => 'getmsec',
					'img_p_width' => 222,
					'img_p_height' => 111,
				);
			}
			if(!empty($_FILES['img_url_m']['size'])){
				$config['multi_config']['img_url_m'] = array(
					'savepath' => UPLOAD_PATH . '/image/' . $path,
					'saveRule' => 'getmsec',
					'img_p_width' => 222,
					'img_p_height' => 111,
				);
			}
			$lists=$this->_uploadapk(0, $config);
				foreach($lists['image'] as $val) {
					if ($val['post_name'] == 'img_url_h') {
						$data['img_url_h']= $val['url'];
					}
					if ($val['post_name'] == 'img_url_m') {
						$data['img_url_m']= $val['url'];
					}
				}
			if($id=$soft_corner->add($data)){
				$this->writelog('增加了名称ID为['.$id.']为['.$data['name'].']的软件详细角标', 'sj_soft_corner_mark', $id);
				$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/CornerMark/soft_cornerMark_list');
				$this->success("添加软件详细角标成功！");
			}else{
				$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/CornerMark/soft_cornerMark_list');
				$this->error("添加软件详细角标失败,发生错误！");
			}
		}else{
			$this->display();
		}
	}
	//软件管理__软件详细角标__编辑   庄超滨 2012.11.15 
	function soft_cornerMark_edit(){
		$soft_corner=M("soft_corner_mark");
		if($_POST){
			$path=date("Ym/d/",time());
			$data['name']=$_POST['name'];
			$where['id']=$_POST['id'];
			$where['status']=1;
			$data['update_tm']=time();
			$tmp_filename_h = $_FILES["img_url_h"]["size"];
			$tmp_filename_m = $_FILES["img_url_m"]["size"];
			$config = array(
				'multi_config' => array(),
			);
			if(!empty($_FILES['img_url_h']['size'])){
				$config['multi_config']['img_url_h'] = array(
					'savepath' => UPLOAD_PATH . '/image/' . $path,
					'saveRule' => 'getmsec',
					'img_p_width' => 222,
					'img_p_height' => 111,
				);
			}
			if(!empty($_FILES['img_url_m']['size'])){
				$config['multi_config']['img_url_m'] = array(
					'savepath' => UPLOAD_PATH . '/image/' . $path,
					'saveRule' => 'getmsec',
					'img_p_width' => 222,
					'img_p_height' => 111,
				);
			}
			if(!empty($config['multi_config'])){
				$lists=$this->_uploadapk(0, $config);
				foreach($lists['image'] as $val) {
					if ($val['post_name'] == 'img_url_h') {
						$data['img_url_h']=$val['url'];
					}
					if ($val['post_name'] == 'img_url_m') {
						$data['img_url_m']= $val['url'];
					}
				}
			}
			if($soft_corner->where($where)->save($data)){
				$this->writelog('修改了名称ID为['.$where[id].']的角标', 'sj_soft_corner_mark', $where['id']);
				$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/CornerMark/soft_cornerMark_list');
				$this->success("修改软件详细角标成功！");
			}else{
				$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/CornerMark/soft_cornerMark_list');
				$this->error("修改软件详细角标失败,发生错误！");
			}
		}else{
			if(!$_GET['id']){
				$this->error("修改出错！！！");
			}
			$where['id']=$_GET['id'];
			$where['status']=1;
			$list=$soft_corner->where($where)->find();
			$this->assign("list",$list);
			$this->display();
		}
	}
	//软件管理__软件详细角标__删除   庄超滨 2012.11.15 
	function soft_cornerMark_del(){
		$soft_corner=M("soft_corner_mark");
		$id=$_GET['id'];
		$map['status']=0;
		$affect = $soft_corner -> where("id='{$id}'")-> save($map);
		$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/CornerMark/soft_cornerMark_list');
		$this->success('删除成功');
	}*/
}
