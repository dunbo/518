<?php
class PushAction extends Action {
	//初始化函数 限制ip
	public function _initialize() {
		//return true;
		$ip = $_SERVER['REMOTE_ADDR'];
		$allow_ip = array(
			"192.168.0.*",
			"192.168.1.*",
			"192.168.3.*",
			//"10.0.3.*",
			"10.0.3.2",
			"42.62.4.169",
			"42.62.4.171",
			"218.241.82.226",
			"127.0.0.1",
			"118.26.203.23",
			"58.220.10.57",
			"58.220.10.59"
		);	

		foreach($allow_ip as $key=>$val) {
			if(strpos($val,'*')===FALSE) {
				if($val==$ip) {
					return TRUE;
				}
			} else {
				$val = str_replace('*','[0-9]{1,3}',$val);
				$val = str_replace('.','\.',$val);
				if(preg_match("/{$val}/",$ip)) {
					return TRUE;
				}
			}
		}
		exit(json_encode(array('code'=>-1,'msg'=>'该ip无权限','ip'=>$ip)));
	}



	public function addPush() {

		//exit('############################');
		$model_push = D('Sj.Push');


		$data = array();

		
		$data['platform'] = 0;
		
		$int_map = array('start_time','end_time','item_show_time_start','item_show_time_end','channel');
		
		foreach ($int_map as $v) {
			if (!isset($_REQUEST[$v])) {
				continue;
			}
			$data[$v] = intval($_REQUEST[$v]);
		}
		


		//必选字段列表
		$req_map = array(
			'start_time',
			'end_time',
			'channel',
		);

		foreach($req_map as $v) {
			if(empty($data[$v])) {
				exit(json_encode(array('error' => array('code' => 1,'msg' => "缺少必要参数".$v))));
			}
		}


		if(!empty($_POST['obj_uids'])) {

			$tmp = json_decode($_POST['obj_uids'],true);
			if(is_array($tmp) && count($tmp)) {
				$data['obj_uids'] = json_encode($tmp);
			}
		}
		
		//print_r($data);exit;
		
		
		$pid = $model_push->addApiPush($data);

		
		


		if (empty($pid)) {
			exit(json_encode(array('error' => array('code' => 1,'msg' => "创建 push 失败"))));
		}

		exit(json_encode(array('pid' =>  $pid)));

	}



	public function editPush() {

		//exit('############################');
		$model_push = D('Sj.Push');


		$data = array();

		$pid = intval($_REQUEST['pid']);
		
		$int_map = array('start_time','end_time','item_show_time_start','item_show_time_end','channel','status');
		
		foreach ($int_map as $v) {
			if (!isset($_REQUEST[$v])) {
				continue;
			}
			$data[$v] = intval($_REQUEST[$v]);
		}

		if (isset($data['status']) && !in_array($data['status'],array(0,1))) {
			unset($data['status']);
		}
		

		

		if(!empty($_POST['obj_uids'])) {

			$tmp = json_decode($_POST['obj_uids'],true);
			if(is_array($tmp) && count($tmp)) {
				$data['obj_uids'] = json_encode($tmp);
			}
		}

		




		if(!count($data) || !$pid) {
			exit(json_encode(array('error' => array('code' => 1,'msg' => "缺少必要参数"))));
		}

		$model_push->editApiPush($pid,$data);

		exit(json_encode(array('pid' =>  $pid)));

	}


}
