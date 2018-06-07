<?php

class ApkModel extends Model {
	
	//本页面的功能块整理为函数
	function getCategory() {
		$model = new Model();

		//获取分类信息
		if(!$_SESSION['category']) {
			$where = array(
				'status' => array('in','1,3')	
			);	
			$arr = $model->table('sj_category')->where($where)->field('category_id,name,parentid')->select();
			if(!$arr) {
				return '获取软件分类时通信失败，请重试';
			} else {
				foreach($arr as $val) {
					$ret[$val['parentid']][] = $val;
				}
				$_SESSION['category'] = $ret;
			}
			$_SESSION['category_js'] = json_encode($_SESSION['category']);

			//获取应用,游戏各分类下的所有id
			$cate_arr = array();
			foreach($_SESSION['category'][0] as $key=>$val) {
				$_tmp_id = $val['category_id'];
				$cate_arr[$_tmp_id][] = $_tmp_id;
				if($_SESSION['category'][$_tmp_id]) {
					foreach($_SESSION['category'][$_tmp_id] as $k=>$v) {
						$cate_arr[$_tmp_id][] = $v['category_id'];
						if($_SESSION['category'][$v['category_id']]) {
							foreach($_SESSION['category'][$v['category_id']] as $_k=>$_v) {
								$cate_arr[$_tmp_id][] = $_v['category_id'];
							}
						}
					}
				}
			}
			$_SESSION['category_arr'] = $cate_arr;
		}

		//需要上传版权的分类id,./config.php 中 $conf['copyright_id']
		$_SESSION['copyright_id'] = '36,55,53';

		return 'ok';
	}
		
	//同 dev.anzhi.com/common.php 中 _http_post()
	function _http_post($vals) {
		$acname = __ACTION__;
		$model = new Model();
		//$admin_node_db =  M('admin_node');
		$action_id=$model->table('sj_admin_node')->where('nodename="'.$acname.'"')->getField('node_id');
		$vals['action_id'] = $action_id;
		$pro_env = C('PRO_ENV');
		if($pro_env == 1){
			//线上
			$conf['dummy'] = array(
				'host' => '192.168.1.18',
				'host_dam' => 'Host: dummy.goapk.com',
			);
		}else if($pro_env == 2){
			$conf['dummy'] = array(
				'host' => 'dummy.goapk.com',
				'host_dam' => 'Host: dummy.goapk.com',
			);
		}else if($pro_env == 3){
			$conf['dummy'] = array(
				'host' => '192.168.0.99',
				'host_dam' => 'Host: 9.dummy.goapk.com',
			);
		}else if($pro_env == 4){
			$conf['dummy'] = array(
				'host' => '127.0.0.1',
				'host_dam' => 'Host: dummy.goapk.local',
			);
		}
		$host = $conf['dummy']['host'];
		//$host = '192.168.0.99';
		$host_dam = $conf['dummy']['host_dam'];
		//$host_dam = 'Host: 9.dummy.goapk.com';
		$vals['softname'] && $vals['softname'] = 'dev_mark_'.$vals['softname'];
		$vals['intro'] && $vals['intro'] = 'dev_mark_'.$vals['intro'];
		$vals['update_content'] && $vals['update_content'] = 'dev_mark_'.$vals['update_content'];
		foreach($vals as $k => $v){
			$pos = substr($v,0,1);
			if($pos == '@'){
				if (class_exists('\CURLFile')) {
					$vals[$k] = new \CURLFile(substr($v,1));
				} else {
					$vals[$k] = $v;
				}			
			}
		}		
		///function_exists
		$res = curl_init();
		curl_setopt($res, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($res, CURLOPT_POST, true);
		curl_setopt($res, CURLOPT_HTTPHEADER, array($host_dam));
		curl_setopt($res, CURLOPT_URL, "http://${host}/service_dev.php");
		if (class_exists('\CURLFile')) {
			curl_setopt($res, CURLOPT_SAFE_UPLOAD, true);
		} else {
			if (defined('CURLOPT_SAFE_UPLOAD')) {
				curl_setopt($res, CURLOPT_SAFE_UPLOAD, false);
			}
		}		
		curl_setopt($res, CURLOPT_POSTFIELDS, $vals);
		$result = curl_exec($res);
		$http_code = curl_getinfo($res,CURLINFO_HTTP_CODE);
		$errno = curl_errno($res);
		$error = curl_error($res);
		curl_close($res);
		$this -> writelog('dummy_admin.log',"{$http_code}|{$errno}|{$error}|{$host}|{$host_dam}\n".print_r($vals, true)."\n".print_r($result,true)."\n\n",'_http_post');

		return $result;
	}

	// 同 dev.anzhi.com/common.php 中同名函数

	//写日志
	function writelog($file,$str,$sign) {
		$log = '';
		if(is_array($str)) {
			foreach($str as $key=>$val) {
				$log .= "{$key}:{$val},";
			}
		} else {
			$log = $str;
		}
		$log = $sign.'|'.$log.'|'.date('Y-m-d H:i:s')."\n";

		$log_path = LOG.date('Y-m-d').'/';
		if(!is_dir($log_path))  mkdir($log_path,0777,true);
		file_put_contents($log_path.$file,$log,FILE_APPEND);
	}

	//同 dev.anzhi.com/add_new_confirm.php 中 getTopCategory()
	function getTopCategory($id) {
		$id = intval(substr($id,1));

		foreach($_SESSION['category_arr'] as $key=>$val) {
			if(in_array($id, $val)) return $key;
		}

		return 0;
	}
}
?>