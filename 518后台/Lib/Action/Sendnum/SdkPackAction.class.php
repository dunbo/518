<?php

/**
 * Desc:   融合SDK打包
 */
class SdkPackAction extends CommonAction {


	private $make_host = '124.243.198.94:85';
	
	
	//列表页
	public function pack_list(){


		$this->make_host = C('APK_BUILD_SERVER');

		//print_r($this->make_host);exit;

		$v_date = '';
		$v_package = '';
		$v_cn_sdk_id = '0';
		$v_soft_name = '';
		$v_type = 'wait_test';
		
		$pa = '';

		
		if(isset($_GET['type']) && $_GET['type'] == 'support') {
			$v_type = 'support';
		}

		//var_dump($_GET);

		if($_GET['type'] == 'upload_apk') {

			$this->upload_apk();
			exit;
		} else if($_GET['type'] == 'pass') {
			$this->pass_apk(intval($_POST['oid']));
			exit;
		} else if($_GET['type'] == 'reject') {
			$this->reject_apk(intval($_POST['oid']),$_POST['rfr']);
			exit;
		}


		


		
		if(!empty($_POST['soft_name'])) {
			$pa .= '&soft_name='.urlencode(trim($_POST['soft_name']));
			$v_soft_name = $_POST['soft_name'];

		}

		if(!empty($_POST['date'])) {
			$start_time = strtotime($_POST['date']);
			$end_time = $start_time + 86400;
			$pa .= '&start_time='.$start_time.'&end_time='.$end_time;
			$v_date = $_POST['date'];
		}

		if(!empty($_POST['package'])) {
			$pa .= '&package='.urlencode(trim($_POST['package']));
			$v_package = $_POST['package'];
		}

		if(!empty($_POST['cn_sdk_id'])) {
			$pa .= '&cn_sdk_id='.intval($_POST['cn_sdk_id']);
			$v_cn_sdk_id = $_POST['cn_sdk_id'];
		}





        $str = file_get_contents('http://'. $this->make_host .'/api.php?act=cn_sdk_list');
		
		$tmp = json_decode($str,true);
		$tpl_data = $tmp['data']['list'];
		$url = "";
		if($v_type == 'support') {
			$pa .= '&status=3,4';
		} else {
			$pa .= '&status=2';
		}

		
		
		$url = 'http://'. $this->make_host .'/api.php?act=get_task_list'.$pa;
		
		
		
		
		$str = file_get_contents($url);
		//print_r($url);
		$tmp = json_decode($str,true);
		$data = $tmp['data']['list'];
        
		foreach($data as $k => &$v) {
			$v['xu'] = $k + 1;
			
			/*
			if($v['url']) {
				$v['url'] = 'http://'.$this->make_host.$v['url'];
			}
			
			
			if($v['log_url']) {
				$v['log_url'] = 'http://'.$this->make_host.$v['log_url'];
			}
			if($v['testing_report']) {
				$v['testing_report'] = 'http://'.$this->make_host.$v['testing_report'];
			}
			*/
		}

		$type_count = array();
		if($v_type == 'support') {
			$type_count['support'] = count($data);
			$tmp = json_decode(file_get_contents('http://'. $this->make_host .'/api.php?act=get_task_list&status=2'),true);
			$type_count['wait_test'] = count($tmp['data']['list']);
		} else {
			$tmp = json_decode(file_get_contents('http://'. $this->make_host .'/api.php?act=get_task_list&status=3,4'),true);
			$type_count['support'] = count($tmp['data']['list']);
			$type_count['wait_test'] = count($data);
		}

		$this->assign('type_count',$type_count);
		$this->assign('tpl_list',$tpl_data);
		$this->assign('task_list',$data);
		$this->assign('v_soft_name',$v_soft_name);
		$this->assign('v_date',$v_date);
		$this->assign('v_package',$v_package);
		$this->assign('v_cn_sdk_id',$v_cn_sdk_id);
		$this->assign('v_type',$v_type);
		$this->display();

	}

	//列表页
	public function upload_apk(){

		$oid = intval($_POST['win_oid']);

		if(!empty($_FILES['apk']['error']) || empty($_FILES['apk']['size'])) {

			exit('upload error');

		}

		//print_r($_POST);
		//print_r($_FILES);


		$apk_file = curl_file_create($_FILES['apk']['tmp_name'],$_FILES['apk']['type'],$_FILES['apk']['name']);

		$url = 'http://'. $this->make_host .'/api.php?act=update_task';
		$res = curl_init();
		curl_setopt($res, CURLOPT_URL, $url);
		//curl_setopt($res, CURLOPT_HTTPHEADER, array($host_dam));
		curl_setopt($res, CURLOPT_POST, true);
		curl_setopt($res, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($res, CURLOPT_POSTFIELDS, array('apk' => $apk_file,'id' => $oid,'status' => 2));
		$result = curl_exec($res);
		$http_code = curl_getinfo($res, CURLINFO_HTTP_CODE);
		$errno = curl_errno($res);
		$error = curl_error($res);
		//var_dump($host,__LINE__);
		curl_close($res);

		//print_r($result);
		//exit;

		$result = json_decode($result,true);

		$msg = "";
		if (@$result['data']['id']) {
			$msg = '操作成功';
		} else {
			$msg = '操作失败';
		}
		exit('<script>alert("'.$msg.'");top.window.location.reload();</script>');
	}	
	

	private function pass_apk($oid) {
		$url = 'http://'. $this->make_host .'/api.php?act=update_task&status=6&id='.$oid;
		$result = file_get_contents($url);
		$result = json_decode($result,true);
		
		if (@$result['data']['id']) {
			$msg = '操作成功';
		} else {
			$msg = '操作失败';
		}
		echo json_encode(array('msg' => $msg));
		exit;
	}

	private function reject_apk($oid,$rfr) {
		$url = 'http://'. $this->make_host .'/api.php?act=update_task&status=2&id='.$oid.'&rfr='.urlencode($rfr);
		$result = file_get_contents($url);
		$result = json_decode($result,true);
		
		if (@$result['data']['id']) {
			$msg = '操作成功';
		} else {
			$msg = '操作失败';
		}
		echo json_encode(array('msg' => $msg));
		exit;
	}


}