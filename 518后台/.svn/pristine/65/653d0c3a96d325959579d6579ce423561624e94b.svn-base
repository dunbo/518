<?php
class AdSettingsAction extends CommonAction{
	function index(){
		$User = M("config");
		$re = $User->table('pu_config')->where(array('config_type' => 'saveminpay'))->select();//最低付款金额
        $huilv_no = $User->table('pu_config')->where(array('config_type' => 'savepersonal_no'))->select();//个人不扣税
		$huilv = $User->table('pu_config')->where(array('config_type' => 'savepersonal'))->select();//个人扣税
		$business_no = $User->table('pu_config')->where(array('config_type' => 'savebusiness_no'))->select();//企业不扣税
		$business = $User->table('pu_config')->where(array('config_type' => 'savebusiness'))->select();//企业扣税
		$company = $User->table('pu_config')->where(array('config_type' => 'savecompany'))->select();
		$this->assign('configcontent', $re[0]['configcontent']);
        $this->assign('huilv_no', $huilv_no[0]['configcontent']);
		$this->assign('huilv', $huilv[0]['configcontent']);
		$this->assign('business_no', $business_no[0]['configcontent']);
		$this->assign('business', $business[0]['configcontent']);
		$this->assign('company', $company[0]['configcontent']);
		$this->display();
	}
	/**
	 * 保存数据金额
	 */
	function savedata(){
		$User = M('config');
		$payment_amount = $_POST['payment_amount'];
        $data['configcontent'] = $payment_amount;
		$data['uptime'] = time();
		// 根据条件保存修改的数据
		$log_result = $this -> logcheck(array('config_type' => 'saveminpay'),'pu_config',$data,$User);
		$result = $User->table('pu_config')->where(array('config_type' => 'saveminpay'))->save($data); 
		if($result) {
			$this -> writelog("参数设置修改了最低付款金额".$log_result, 'pu_config','config_type:saveminpay',__ACTION__ ,'','edit');
			$this->ajaxReturn($result,"更新成功！",1);
		}else {
			$this->ajaxReturn(0,"更新失败！",0);
		}
		 
	}
     
	/**
	*	保存个人不扣税
	*/
	function save_huilv_no(){
	 	$User = M('config');
	 	$self_huilv_no = $_POST['self_huilv_no'];
	 	$data['configcontent'] = $self_huilv_no;
	 	$data['uptime'] = time();
	 	//$data['configcontent'] = $self_huilv;
	 	$log_result = $this -> logcheck(array('config_type' => 'savepersonal_no'),'pu_config',$data,$User);
	 	$result = $User->table('pu_config')->where(array('config_type' => 'savepersonal_no'))->save($data);
	 	if($result) {
			$this -> writelog("参数设置修改了个人不扣税".$log_result, 'pu_config','config_type:savepersonal_no',__ACTION__ ,'','edit');
			$this->ajaxReturn($result,"更新成功！",1);
	 	}else {
			$this->ajaxReturn(0,"更新失败！",0);
	 	}
	}

	 /**
	  *	保存个人汇率
	  */
	 function save_huilv(){
		 $User = M('config');
		 $self_huilv = $_POST['self_huilv'];
		 $data['configcontent'] = $self_huilv;
		 $data['uptime'] = time();
         //$data['configcontent'] = $self_huilv;
		 $log_result = $this -> logcheck(array('config_type' => 'savepersonal'),'pu_config',$data,$User);
		 $result = $User->table('pu_config')->where(array('config_type' => 'savepersonal'))->save($data);
		 if($result) {
			$this -> writelog("参数设置修改了个人税率".$log_result, 'pu_config','config_type:savepersonal',__ACTION__ ,'','edit');
			$this->ajaxReturn($result,"更新成功！",1);
		 }else {
			$this->ajaxReturn(0,"更新失败！",0);
		 }
	 }

	  /**
	  *	保存企业汇率  即企业不扣税
	  */
	 function savebusiness_no(){
		 $User = M('config');
		 $save_business_no = $_POST['save_business_no'];
		 $data['configcontent'] = $save_business_no;
		 $data['uptime'] = time();
         //$data['configcontent'] = $self_huilv;
		 $log_result = $this -> logcheck(array('config_type' => 'savebusiness_no'),'pu_config',$data,$User);
		 $result = $User->table('pu_config')->where(array('config_type' => 'savebusiness_no'))->save($data);
		 if($result) {
			$this -> writelog("参数设置修改了企业税率".$log_result, 'pu_config','config_type:savebusiness_no',__ACTION__ ,'','edit');
			$this->ajaxReturn($result,"更新成功！",1);
		 }else {
			$this->ajaxReturn(0,"更新失败！",0);
		 }
	 }

	 /**
	  *	保存企业扣税
	  */
	 function savebusiness(){
		 $User = M('config');
		 $save_koushui = $_POST['save_koushui'];
		 $data['configcontent'] = $save_koushui;
		 $data['uptime'] = time();
		 $log_result = $this -> logcheck(array('config_type' => 'savebusiness'),'pu_config',$data,$User);
		 $result = $User->table('pu_config')->where(array('config_type' => 'savebusiness'))->save($data);
		 if($result) {
			$this -> writelog("参数设置修改了企业（扣税）".$log_result, 'pu_config','config_type:savebusiness',__ACTION__ ,'','edit');
			$this->ajaxReturn($result,"更新成功！",1);
		 }else {
			$this->ajaxReturn(0,"更新失败！",0);
		 }
	 }

	//保存公司税率
	 function savecompany(){
		 $User = M('config');
		 $save_company = $_POST['save_company'];
		 $data['configcontent'] = $save_company;
		 $data['uptime'] = time();
		 $log_result = $this -> logcheck(array('config_type' => 'savecompany'),'pu_config',$data,$User);
		 $result = $User->table('pu_config')->where(array('config_type' => 'savecompany'))->save($data);
		 if($result) {
			$this -> writelog("参数设置修改了公司税率".$log_result, 'pu_config','config_type:savecompany',__ACTION__ ,'','edit');
			$this->ajaxReturn($result,"更新成功！",1);
		 }else {
			$this->ajaxReturn(0,"更新失败！",0);
		 }
	 }
}