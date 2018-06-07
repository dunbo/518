<?php

class WebOperationAction extends CommonAction {

    function index(){
        $model= D('Operation.Operation');
		if($_GET['file_name']){
			$_GET['file_name'] = base64_decode($_GET['file_name']);
		}	
		foreach($_POST AS $k => $v){
			if(empty($v)) continue;
			$_GET[$k] = $v;
		}
		if(empty($_GET['begintime']) && empty($_GET['endtime'])){
			$time = time();
			$last_day = date("Y-m-d 00:00:00",$time-86400);
			$day = date("Y-m-d 23:59:59",$time);
			$_GET['begintime'] = $last_day;
			$_GET['endtime'] = $day;
		}
		$this->check_where($where, 'ip_address');
		$this->check_where($where, 'operation');
		if($_GET['type'] == 1){
			$this->check_where($where, 'file_name', 'isset', 'like');
		}else{
			$this->check_where($where, 'file_name');
		}
		$this->check_range_where($where, 'begintime', 'endtime', 'attribute_time', true);		
		list($list,$total, $page) = $model -> get_data($where,$_GET,'inotify');
		$this->assign('list', $list);
		$this -> assign('page', $page->show());
		$this -> assign('total', $total);		
		if($_POST['from'] == 1){
			$list_json = array();
			foreach($list as $k=>$v){
				unset($list[$k]['ip_address'],$list[$k]['attribute_time'],$list[$k]['id'],$list[$k]['toal']);
			}
			echo json_encode($list);
		}else{
			$this ->display('index');
		}
    }
    function pub_operation_json(){
		$_POST['from'] = 1;
		$_POST['type'] = 1;
		// $time = time();
		// $last_day = date("Y-m-d 00:00:00",$time-86400);
		// $day = date("Y-m-d 23:59:59",$time);
		// $_POST['begintime'] = "2013-11-11 00:00:00";
		// $_POST['endtime'] = $day;	
		
		$_GET['limit'] = 1000;
		$res = $this -> index();
		
	}
}
?>
