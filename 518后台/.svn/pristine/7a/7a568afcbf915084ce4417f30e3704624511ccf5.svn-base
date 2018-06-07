<?php

/************************************************************************
 * @Desc: 换量合作
 * @Date: 2015_7_24
 ************************************************************************/
class ActivitionCooperAction extends CommonAction {

	//等级折扣配置
	public function rebate_config(){
		$ActivitionCooper = D('sendNum.ActivitionCooper');
		$rebateInfo = $ActivitionCooper->get_rebate_list();
		$Page = $rebateInfo[0];
		$this->assign('page', $Page->show());
		$this->assign('rebateList',$rebateInfo[1]);
		$this->display();
	}
	
	//添加等级折扣配置
	public function add_rebate_config(){
		if($_POST){
			if(!isset($_POST['rebate_name'])||empty($_POST['rebate_name'])){
				$this->error('请填写折扣名称');
			}
			if(!isset($_POST['discount'])||empty($_POST['discount'])){
				$this->error('请填写折扣率');
			}
			if(!preg_match('/^\d+$/',$_POST['discount'])){
				$this->error('折扣率为整数');
			}
			if(!isset($_POST['gold_coefficient'])||empty($_POST['gold_coefficient'])){
				$this->error('请填写换量金系数');
			}
			if(!preg_match('/^\d+\.\d+$/',$_POST['gold_coefficient'])){
				$this->error('换量金系数为小数');
			}
			$ActivitionCooper = D('sendNum.ActivitionCooper');
			//入库
			$res = $ActivitionCooper->add_rebate_config($_POST);
			if($res){
				$this->writelog('换量合作-等级折扣配置：添加了id为'.$res.'的等级折扣配置','dev_cooper_rebate_config',$res,__ACTION__ ,'','add');
				$this->success('添加成功');
			}else{
				$this->error('添加失败');
			}
		}
		$this->display();
	}
	
	//广告投放配置
	public function advertise_config(){
		$ActivitionCooper = D('sendNum.ActivitionCooper');
		//广告投放日期
		$ad_day = $ActivitionCooper->get_ad_day();
		//默认刊例广告
		$ad_info = $ActivitionCooper->get_rate_card_ad();
		$Page = $ad_info[0];
		$this->assign('page', $Page->show());
		$this->assign('ad_info',$ad_info[1]);
		$this->assign('ad_day',$ad_day);
		$this->display();
	}
	
	//设置广告投放日期
	public function set_ad_day(){
		//修改类型 1起始日 2截止日
		$type = $_POST['day_type'];
		if(!$type){
			$res['code'] = '-1';
			$res['msg'] = '参数错误';
			echo json_encode($res);
			exit();
		}
		if($type == '1'){
			//设置起始日
			if($_POST['day'] < 1){
				$res['code'] = '-1';
				$res['msg'] = '起始日需≥1，截止日需≤365，起始日需小于截止日';
				echo json_encode($res);
				exit();
			}
		}else if($type == '2'){
			//设置截止日
			if($_POST['day'] > 365){
				$res['code'] = '-1';
				$res['msg'] = '起始日需≥1，截止日需≤365，起始日需小于截止日';
				echo json_encode($res);
				exit();
			}
		}
		if($res['code']!='-1'){
			$ActivitionCooper = D('sendNum.ActivitionCooper');
			$save_info = $ActivitionCooper->save_ad_day($type,$_POST['day']);
			if(!$save_info){
				$res['code'] = '-1';
				$res['msg'] = '保存失败';
				echo json_encode($res);
				exit();
			}else{
				$res['code'] = '0';
				$res['msg'] = '保存成功';
				echo json_encode($res);
				exit();
			}
		}
		
	}
	
	//设置投放广告数和示意图
	public function save_ad_info(){
		$ActivitionCooper = D('sendNum.ActivitionCooper');
		$id = $_POST['ad_id'];
		
		if(!$id){
			$res['code'] = '-1';
			$res['msg'] = '参数错误';
			echo json_encode($res);
			exit();
		}
		if(isset($_FILES)&&!$_POST['num']){
			$file_name = 'ad_img_'.$id;
			if(!empty($_FILES[$file_name]['name'])){
				$_POST['cp_ad_demo'] = $this->upload_img($_FILES[$file_name]);
			}else{
				$this->error('请上传文件');
			}
		}
		if(isset($_POST['num'])){
			if($_POST['num']==''||!preg_match('/^\d+$/',$_POST['num'])){
				$res['code'] = '-1';
				$res['code'] = '需为≥0的整数，请重新输入';
				echo json_encode($res);
				exit();
			}
		}	
		$save_ad_info = $ActivitionCooper->save_ad_info($id,$_POST);
		if(!$save_ad_info){
			if(isset($_POST['cp_ad_demo'])) $this->error('上传失败');
			$res['code'] = '-1';
			$res['msg'] = '保存失败';
			echo json_encode($res);
			exit();
		}else{
			if(isset($_POST['cp_ad_demo'])) $this->success('上传成功');
			$res['code'] = '0';
			$res['msg'] = '保存成功';
			echo json_encode($res);
			exit();
		}
	}
	
	//上传示意图
	public function upload_img($data){
		global $conf;
		$size = $data['size'];
		if($size > 1048576){ //1M
			$this->error('格式限jpg和png，大小1M以内');	
		}
		$ytype = $data['name'];
		$info = pathinfo($ytype);
		$type = strtolower($info['extension']); //获取文件件扩展名
		$type_arr = array('jpg','png');
		if(!in_array($type,$type_arr)){
			$this->error('格式限jpg和png，大小1M以内');
		}

		$path = UPLOAD_PATH .C('sdk_game_sign_url');
		list($msec, $sec) = explode(' ', microtime());
		$msec = substr($msec, 2);
		$this->mkDirs($path);
		$img_path = $path . 'sign_' . $msec . '.' . $type;
		if (move_uploaded_file($data['tmp_name'], $img_path)){
			 $last_path = str_replace(UPLOAD_PATH, '', $img_path);
		}else{
			$this->error('上传签名失败');	
		};
		return $last_path;
	}
	
	/*
	*	删除示意图
	*/
	public function del_img(){
		$id = $_GET['id'];
		if(!$id){
			$this->error('参数错误');
		}else{
			$ActivitionCooper = D('sendNum.ActivitionCooper');
			$save_ad_info = $ActivitionCooper->save_ad_info($id,array('cp_ad_demo'=>''));	
			if($save_ad_info){
				$this->success('删除成功');
			}else{
				$this->error('删除失败');
			}
		}
	}
	
	/*
	*	渠道管理
	*/
	public	function cooper_channel(){
		$this->display();
	}
}

?>