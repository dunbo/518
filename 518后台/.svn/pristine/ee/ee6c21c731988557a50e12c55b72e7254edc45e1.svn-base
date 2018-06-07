<?php

class AdvertisePictureAction extends CommonAction{
	
	//广告图列表
	function ad_picture_list(){
		$model = M('advertise_picture');
		if(!empty($_GET['pic_type'])){
			$where['type'] = $_GET['pic_type'];
			$pic_type = $_GET['pic_type'];
		}else{
			$where['type'] = 1;
			$pic_type = 1;
		}
		$where['status'] = 1;
		if(!empty($_GET['time_go'])){
			if($_GET['time_go'] == 1){
				$where['start_tm'] = array("exp","< ".time()."");
				$where['end_tm'] = array("exp","> ".time()."");
				$state = 1;
			}elseif($_GET['time_go'] == 2){
				$where['start_tm'] = array("exp","> ".time()."");
				$state = 2;
			}elseif($_GET['time_go'] == 3){
				$where['end_tm'] = array("exp","< ".time()."");
				$state = 3;
			}
		}else{
			$state = 1;
			$where['start_tm'] = array("exp","< ".time()."");
			$where['end_tm'] = array("exp","> ".time()."");
		}

		$order =  "upload_tm";
		$count = $model -> where($where) -> count();
		import("@.ORG.Page");
        $param = http_build_query($_GET);
        $Page = new Page($count, 10, $param);
        $ad_list = $model ->where($where)->order($order)->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$Page->setConfig('header', '篇记录');
        $Page->setConfig('first', '<<');
        $Page->setConfig('last', '>>');
        $show = $Page->show();
		$this->assign("pic_type",$pic_type);
		$this->assign("page", $show);
		$this->assign("result",$ad_list);
		$this->assign("state",$state);
		$this->display();

	}
	//添加广告图显示
	function ad_picture_add(){
		$pic_type = $_GET['type'];
		$this -> assign("pic_type",$pic_type);
		$this -> display();
	}
	//添加广告图提交
	function ad_picture_add_do(){
		$model = M('advertise_picture');
		if(!empty($_POST['title'])){
			$data['title'] = trim($_POST['title']);
		}
		$data['link'] = trim($_POST['link']);
		if(!empty($_POST['link'])){
			if(!preg_match("/^(http:\/\/[^\/]+)/i",$data['link'])){
				$this -> error("请填写正确格式的链接");
			}
		}
		if(!empty($_POST['start_tm'])){
			$data['start_tm'] = strtotime(date('Ymd 00:00:00',strtotime($_POST['start_tm'])));
		}else{
			$this -> error("对不起，开始时间不能为空");
		}
		if(!empty($_POST['end_tm'])){
			$data['end_tm'] = strtotime(date('Ymd 23:59:59',strtotime($_POST['end_tm'])));
		}else{
			$this -> error("对不起，结束时间不能为空");
		}

		if(strtotime(date('Ymd 00:00:00',strtotime($_POST['start_tm']))) >= strtotime(date('Ymd 23:59:59',strtotime($_POST['end_tm'])))){
			$this -> error("对不起，结束时间必须大于开始时间");
		}
		if($_FILES['picture']['type'] != 'image/jpeg'){
			$this -> error("请上传jpg格式的图片");
		}
		if(!empty($_FILES['picture']['size'])){
			$path = date('Ym/d/', time());
				$config = array(
					'multi_config' => array(
						'picture' => array(
							'savepath' => UPLOAD_PATH. '/img/'. $path,
							'saveRule' => 'time'
						),
					),
					'img_p_width' =>  250,
					'img_p_height' => 150,
				);
			$upload=$this->_uploadapk(0, $config);	
			$data['pic_url'] = $upload['image'][0]['url'];
		}else{
			$this -> error("对不起，图片不能为空");
		}
		$data['upload_tm'] = time();
		if(!$_POST['pic_type']){
			$_POST['pic_type'] = 1;
		}
		$data['type'] = $_POST['pic_type'];
		$been_where['_string'] = " start_tm <= ".strtotime(date('Ymd 23:59:59',strtotime($_POST['end_tm'])))." and end_tm >= ".strtotime(date('Ymd 00:00:00',strtotime($_POST['start_tm'])))." and type = ".$_POST['pic_type']." and status = 1 ";
		$have_been = $model -> where($been_where) -> select();

		$data['status'] = 1;
		if($have_been){
			$this -> error("对不起，您所添加的广告排期已存在其他广告");
		}else{
			$affect = $model -> add($data);
			if($affect){
				$this -> success("添加成功");
			}else{
				$this -> error("对不起，添加失败");
			}
		}
	}
	
	function delete_ad(){
		$model = M('advertise_picture');
		$data['status'] = 0;
		$affect = $model -> where(array('id' => $_GET['id'])) -> save($data);
		if($affect){
			$this -> success("删除成功");
		}else{
			$this -> error("删除失败");
		}
	}
	
	function ad_picture_edit(){
		$model = M("advertise_picture");
		$result = $model -> where(array('id' => $_GET['id'])) -> select();
		$pic_type = $_GET['pic_type'];
		$this -> assign("pic_type",$pic_type);
		$this -> assign("result",$result);
		$this -> display();
	}
	
	function ad_picture_edit_do(){
		$model = M("advertise_picture");
		if(!empty($_POST['title'])){
			$data['title'] = trim($_POST['title']);
		}
		$data['link'] = trim($_POST['link']);
		if(!empty($_POST['link'])){
			if(!preg_match("/^(http:\/\/[^\/]+)/i",$data['link'])){
				$this -> error("请填写正确格式的链接");
			}
		}
		if(!empty($_POST['start_tm'])){
			$data['start_tm'] = strtotime(date('Ymd 00:00:00',strtotime($_POST['start_tm'])));
		}else{
			$this -> error("对不起，开始时间不能为空");
		}
		if(!empty($_POST['end_tm'])){
			$data['end_tm'] = strtotime(date('Ymd 23:59:59',strtotime($_POST['end_tm'])));
		}else{
			$this -> error("对不起，结束时间不能为空");
		}
		if(strtotime(date('Ymd 00:00:00',strtotime($_POST['start_tm']))) >= strtotime(date('Ymd 23:59:59',strtotime($_POST['end_tm'])))){
			$this -> error("对不起，结束时间必须大于开始时间");
		}
		
		if(!empty($_FILES['picture']['size'])){
			$path = date('Ym/d/', time());
				$config = array(
					'multi_config' => array(
						'picture' => array(
							'savepath' => UPLOAD_PATH. '/img/'. $path,
							'saveRule' => 'time'
						),
					),
					'img_P_size' => 1024*50,
					'img_p_width' =>  250,
					'img_p_height' => 150,
				);

			$upload=$this->_uploadapk(0, $config);	
			$data['pic_url'] = $upload['image'][0]['url'];
		}
		
		if(empty($_POST['pic_type'])){
			$_POST['pic_type'] = 1;
		}
		$data['upload_tm'] = time();
		$been_where['_string'] = " start_tm <= ".strtotime(date('Ymd 23:59:59',strtotime($_POST['end_tm'])))." and end_tm >= ".strtotime(date('Ymd 00:00:00',strtotime($_POST['start_tm'])))." and type = ".$_POST['pic_type']." and status = 1 ";
		$have_been = $model -> where($been_where) -> select();

		if(count($have_been) > 1){
			$this -> error("对不起，您所添加的广告排期已存在其他广告");
		}else{
			$log_result = $this->logcheck(array('id'=>$_POST['id']),'sj_advertise_picture',$data,$model);
		
			$affect = $model -> where(array('id' => $_POST['id'])) -> save($data);
			if($affect){
				$this -> writelog("已编辑侧栏广告位管理列表id为{$id}".$log_result);
				$this -> success("编辑成功");
			}else{
				$this -> error("对不起，编辑失败");
			}
		}
	
	}
	
	
	
	
}

?>