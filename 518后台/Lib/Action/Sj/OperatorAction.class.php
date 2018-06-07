<?php
class OperatorAction extends CommonAction {
 function operator_list(){
	$model = new Model();
	$operator_list = $model -> table('pu_operating') -> select();
	$this -> assign('operator_list',$operator_list);
	$this -> display('operator_list');
 }
 function operator_add(){
	$model = new Model();
	$data['mname'] = $_POST['mname'];
	$data['imsi'] = $_POST['imsi'] ? $_POST['imsi'] : '0';
	$data['alone_update'] = $_POST['alone_update'] ? $_POST['alone_update'] : 0;
	$data['only_auth'] = $_POST['only_auth'] ? $_POST['only_auth'] : 0;
	$data['note'] = $_POST['note'];
	$data['create_tm'] = time();
	$mname_cnt = $model -> table('pu_operating') -> where(array("mname"=>$_POST['mname'])) -> count();
	if($mname_cnt > 0){
	 $this -> error("该运营商名称已经存在！！");
	}
	if($_POST['imsi']){
		$imsi_cnt = $model -> table('pu_operating') -> where(array("imsi"=>$_POST['imsi'])) -> count();
		if($imsi_cnt > 0){
		 $this -> error("该imsi已经存在！！");
		}
	}
	$affect = $model -> table('pu_operating') -> add($data);
	if($affect){
		$this -> writelog("运营商管理_运营商列表_添加运营商 oid:".$affect,'pu_operating',$affect,__ACTION__ ,"","add");
		$this -> assign('jumpUrl','/index.php/'.GROUP_NAME.'/Operator/operator_list');
		$this -> success("添加运营商成功");
	}else{
		$this -> error("添加运营商失败");
	}
 }
 function operator_edit(){
	$model = new Model();
	if(isset($_POST['do'])){
	$data['mname'] = $_POST['mname'];
	$data['imsi'] =  $_POST['imsi'] ? $_POST['imsi'] : '0';
	$data['alone_update'] = $_POST['alone_update'] ? $_POST['alone_update'] : 0;
	$data['only_auth'] = $_POST['only_auth'] ? $_POST['only_auth'] : 0;
	$data['note'] = $_POST['note'];	
	$log = $this -> logcheck(array('oid'=>$_POST['oid']),'pu_operating',$data,$model);
	$affect = $model -> table('pu_operating') -> where(array('oid'=>$_POST['oid'])) -> save($data); 
		if($affect){
		$this -> writelog("运营商管理_运营商列表_修改运营商 oid:".$_POST['oid']."修改内容:".$log,'pu_operating',$_POST['oid'],__ACTION__ ,"","edit");
		$this -> assign('jumpUrl','/index.php/'.GROUP_NAME.'/Operator/operator_list');
		$this -> success("修改运营商成功");		
		}else{
		$this -> error("修改运营商失败");				
		}
	}else{
		$query_str = $_GET['oid'];
		$str_arr = explode('&',$query_str);
		$oid = $str_arr[0];
		$operator_info = $model -> table('pu_operating') -> where(array('oid'=>$oid)) -> select();
		$this -> assign('operator_info',$operator_info[0]);
		$this -> display('operator_edit');
	}
 }
 function operator_soft_adapter(){
	$oid = $_REQUEST['oid'];
	$model = new Model();
	if(!isset($_REQUEST['do'])){
	$search = escape_string($_GET['search']) ? " and package like '%".escape_string($_GET['search'])."%' or softname like '%".escape_string($_GET['search'])."%' " : '';
	$count = $model -> table('sj_soft') -> where('status = 1 and hide = 1 '.$search) -> count();
	//echo $count; 
    import("@.ORG.Page");
	$page = new Page($count,20);
	$softlist = $model -> table('sj_soft') -> where('status = 1 and hide = 1'.$search) ->limit($page ->firstRow.','.$page -> listRows)-> select();
	$operator_list = $model -> table('pu_operating') -> select();
	$this -> assign('search',$_GET['search']);
	$this -> assign('page',$page->show());
	$this -> assign('operator_list',$operator_list);
	$this -> assign('softlist',$softlist);
	$this -> display('operator_soft_adapter');
	}else{
		$pkg_arr = $_POST['pkgs'];
		$time = time();
		//过滤
		if(isset($_POST['adapter'])){
		 if($_POST['oid'] == 'selected'){
			$this -> error("请选择运营商！");
		 }
		 $ret='';
		foreach($pkg_arr as $pkg){
			$data['oid'] = $_POST['oid'];
			$data['package'] = $pkg;
			$pagarr[] = $pkg;
			$data['status'] = 1;
			$data['create_tm'] = time();
			$pkgcount = $model -> table('sj_operator_adapter') -> where(array('oid'=>$_POST['oid'],'package'=>$pkg,'status' =>1)) -> count();
			if($pkgcount > 0) continue;

			$ret.=$model -> table('sj_operator_adapter') -> add($data);
			$ret.=',';
		}
		$this->writelog('运营商管理_运营商列表_批量添加过滤软件'.implode(',',$pagarr).'到运营商ID为'.$_POST['oid'],'sj_operator_adapter',$ret,__ACTION__ ,"","add");
		$this -> assign('jumpUrl','/index.php/'.GROUP_NAME.'/Operator/operator_adapter_list/oid/'.$_POST['oid']);
		$this -> success("过滤添加成功！");
		}else if(isset($_POST['adapter_delete'])){
			$ids = $_POST['id'];
			$id_str="";
			foreach($ids as $id){
				$id_str.=','.$id;
				$data['status'] = 0;
				$data['create_tm'] = time();
				$package = $model -> table('sj_operator_adapter') -> where(array('id'=>$id)) -> find();
				$packagearr[] = $package['package'];
				$model -> table('sj_operator_adapter') -> where(array('id'=>$id)) -> save($data);
			}
			$this->writelog('运营商管理_运营过滤软件_批量过滤删除软件'.implode(',',$packagearr),'sj_operator_adapter',trim($id_str,','),__ACTION__ ,"","del");
			$this -> assign('jumpUrl','/index.php/'.GROUP_NAME.'/Operator/operator_adapter_list/oid/'.$_POST['oid']);
			$this -> success("过滤删除成功！");		
		}
	}
 }
 function operator_adapter_list(){
	$oid = $_GET['oid'];
	$status = 1;
	$model = new Model();
    import("@.ORG.Page");
	$operator_list = $model -> table('pu_operating') -> select();
	$count = $model -> table('sj_operator_adapter') -> where(array('oid'=>$oid,'status'=>$status)) ->count();
	$page = new Page($count,20);
	$osoftlist = $model -> table('sj_operator_adapter') -> where(array('oid'=>$oid ,'status' => $status)) ->order('create_tm desc') -> limit($page -> firstRow.','.$page -> listRows) -> select();
	foreach($osoftlist as $idx => $info){
	$result = $model -> table('sj_soft') -> where("package='".$info['package']."' and status=1 and hide=1") -> getField('softname');
	if(!$result){
		unset($osoftlist[$idx]);
		continue;
	}
		$osoftlist[$idx]['softname'] = $result;
	}
	$this -> assign('oid',$oid);
	$this -> assign('page',$page -> show());
	$this -> assign('operator_list',$operator_list);
	$this -> assign('osoftlist',$osoftlist); 
	$this -> display('operator_adapter_list');
 }
}