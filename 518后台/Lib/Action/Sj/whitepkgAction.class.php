<?php
class whitepkgAction extends CommonAction{
  //白名单软件列表  
  function whitepkglist(){
	$model = new Model();
	$data = array();
	if($_GET['search']){
		$search = trim($_GET['search']);
		$where['package'] = array("like","%{$search}%"); 
	}
	$table = 'sj_safe_white_package';
	$where['status'] = 1;
	$count = $model -> table($table) -> where($where) -> count();
	import('@.ORG.Page');
	$page = new Page($count,20);
	$list = $model -> table($table) -> where($where) -> order('update_tm desc') -> limit($page -> firstRow .','.$page -> listRows) -> select();
	foreach($list as $id => $info){
		$softname = $model -> table('sj_soft') -> where("package = '{$info['package']}'") -> getField('softname');
		$list[$id]['softname'] = $softname;
	}
	$this -> assign('page',$page -> show());
	$this -> assign('list',$list);
	$this -> display('whitepkglist');
  }
 //添加白名单软件
  function whitepkg_add(){
	$pkg = trim($_POST['package']);
	$model  = new Model();
	$data = array();
	$time =  time();
	$data['package'] = $pkg;
	$data['add_tm'] = $time;
	$data['update_tm'] = $time;
	$data['status'] = 1;
	$where = array('package' => $pkg,'status' => 1);
	$count = $model -> table('sj_soft') -> where($where) -> count();
	if($count == 0){
		$this -> error('该包名的软件为非上线软件！！');
	}
	$where = array();
	$where['package'] = $pkg;
	$where['status'] = 1;
	$count = $model -> table('sj_safe_white_package') -> where($where) -> count();
	if($count > 0){
		$this -> error('该包名的软件已存在白名单中！');
	}
	$affect = $model -> table('sj_safe_white_package') -> add($data);
	if($affect){
		$this->writelog('商务合作软件白名单_白名单软件列表_添加 package 为' . $pkg . ' 软件安全白名单列表', 'sj_safe_white_package',$affect,__ACTION__ ,'','add');
		$this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/whitepkg/whitepkglist');
		$this->success("添加成功！");
	}
  }
//删除白名单内的软件 
  function whitepkg_delete(){
	$id = $_GET['id'];
	$model = new Model();
	$where['id'] = $id;
	$where['status'] = 1;
	$count = $model -> table('sj_safe_white_package') -> where($where) -> count();
	if($count ==0){
		$this -> error('您要操作的软件有误');
	}
	$save = array();
	$save['status'] = 0;
	$save['update_tm'] = time();
	$package = $model -> table('sj_safe_white_package') -> where($where) -> find();
	$affect = $model -> table('sj_safe_white_package') -> where($where) -> save($save);
	if($affect){
		$this -> writelog("商务合作软件白名单_白名单软件列表_id 为 {$id} 包名为{$package['package']} 移除白名单！！", 'sj_safe_white_package',$id,__ACTION__ ,'','del');
		$this -> assign('jumpUrl','/index.php/'.GROUP_NAME.'/whitepkg/whitepkglist');
		$this -> success('修改成功！');
	}else{
		$this -> error('修改失败');
	}
  }
  //编辑白名单内的软件 
  function whitepkg_edit(){
	$id = $_GET['id'];
	$where = array();
	$where['id'] = $id;
	$model = new Model();
	$pkg_info = $model -> table('sj_safe_white_package') -> where($where) -> limit(1) -> select();
	$package = $pkg_info[0]['package'];
	$where = array();
	$where['package'] = $package; 
	$softname = $model -> table('sj_soft') -> where($where) -> getField('softname');
	$this -> assign('softname',$softname);
	$this -> assign('pkginfo',$pkg_info[0]);
	$this -> display('whitepkg_edit');
  }
  //编辑白名单内的软件 
  function whitepkg_edit_do(){
	$id = $_GET['id'];
	$model = new Model();
	$where['id'] = $id;
	$where['status'] = 1;
	$count = $model -> table('sj_safe_white_package') -> where($where) -> count();
	if($count ==0){
		$this -> error('您要操作的软件有误');
	}
	$w = array();
	$w['package'] = trim($_POST['package']);
	$w['status'] = 1;
	$w['_string'] = " hide <> 3 and hide <> 0";
	$count = $model -> table('sj_soft') -> where($w) -> count();
	if($count == 0){
		$this -> error('您要修改的软件可能为历史软件或已下架或不存在！！');
	}
	$save = array();
	$save['package'] = trim($_POST['package']);
	$save['update_tm'] = time();
	$log1 = $this->logcheck(array('id'=>$id),'sj_safe_white_package',$save,$model);
	$affect = $model -> table('sj_safe_white_package') -> where($where) -> save($save);
	if($affect){
		//$this -> writelog("id 为 {$id} package 修改为 {$_POST['package']}！！");
		$this->writelog("商务合作软件白名单_白名单软件列表_id为$id".$log1, 'sj_safe_white_package',$id,__ACTION__ ,'','edit');
		$this -> assign('jumpUrl','/index.php/'.GROUP_NAME.'/whitepkg/whitepkglist');
		$this -> success('修改成功！');
	}else{
		$this -> error('修改失败');
	}
  }
  //白名单软件列表
  function  whitecheckpkg(){
	$model = new Model();
	$where = array();
	if($_GET['softname']){
		$w = array();
		$w['softname'] = array("like","%{$_GET['softname']}%");
		$list = $model -> table('sj_soft') -> where($w) -> field('package') -> select();
		$pkg_list = array();
		foreach($list as $info){
			$pkg_list[] = $info['package'];
		}
		$where['package'] = array('in',$pkg_list);
		$this -> assign('softname',$_GET['softname']);
 	}
	if($_GET['package']){
		$where['package'] = array("like","%{$_GET['package']}%");
		$this -> assign('package',$_GET['package']);
 	}
	$where['safe'] = 2; 
	$count = $model -> table('sj_safe_white_package') -> where($where) -> count();
	import("@.ORG.Page");
	$page = new Page($count,20);
	$soft_list = $model -> table('sj_safe_white_package') -> where($where) -> limit($page -> firstRow .','.$page -> listRows) -> select();
	foreach($soft_list as $idx => $info){
		$softname = $model -> table('sj_soft') -> where("package = '".$info['package']."'") -> getField('softname');
		$soft_list[$idx]['softname'] = $info['softname'];
 	}
	$this -> assign('page',$page -> show());
	$this -> assign('softlist',$soft_list);	
	$this -> display('whitecheckpkg');	
  }
  //确认软件手否下架
  function modifypkgsaft_list(){
	$model = new Model();
	$where = array();
	if($_GET['softid']){
		$where['softid'] = $_GET['softid'];
		$this -> assign('softid',$_GET['softid']);
 	}
	if($_GET['softname']){
		$where['softname'] = array("like","%{$_GET['softname']}%");
		$this -> assign('softname',$_GET['softname']);
 	}
	if($_GET['package']){
		$where['package'] = array("like","%{$_GET['package']}%");
		$this -> assign('package',$_GET['package']);
 	}
	//处于白名单软件中 safe 为 -1
	$where['_string'] = 'status =1 and hide <> 3 and hide <> 0 and safe = -1';
	import('@.ORG.Page');
	$count = $model -> table('sj_soft') -> where($where)-> count();
	$page = new Page($count,20);
	$soft_list = $model -> table('sj_soft') -> where($where) ->field('softid,softname,package') -> limit($page -> firstRow.','.$page->listRows) -> select();
	
	foreach($soft_list as $idx => $info){
		$sfid = $model -> table('sj_soft_file') -> where('softid ='.$info['softid']." and package_status = 1") -> getField('id');
		$scan_list = $model -> table('sj_soft_scan_result') -> where('sfid ='.$sfid.' and safe > 1') -> field('provider,time_req,time_rep,description') -> select();
		$scan_result = '';
		$virus = '';
		foreach($scan_list as $info){
			$des = json_decode($info['description'], true);
			if($info['provider'] == 1){
				$scan_result .= 'QQ : 发送时间 '.date("Y-m-d H:i:s",$info['time_req'])."<br/>";
                $virus .= 'QQ 返回信息:'.$des["response"]["trojan"]["description"]."<br/>";
			}
			if($info['provider'] == 2){
				$scan_result .= ' 安全管家 : 发送时间 '.date("Y-m-d H:i:s",$info['time_req'])."<br/>";
				$virus .= "安全管家 返回信息:";
			        if (isset($des["res"])){
                    		 	$virus .= $des["res"]."<br/>";
                		}else if(isset($des["app"]["des"])){
                    		 	$virus .= $des["app"]["des"]."<br/>";
				}else if(isset($des["des"])){
					$virus .= $des["des"]."<br/>";
				}
			}
			if($info['provider'] == 3){
				$scan_result .= ' 网秦 : 发送时间 '.date("Y-m-d H:i:s",$info['time_req'])."<br/>";
				$virus .= '网秦 返回信息:'.$des['ScanInfo']['responseInfo']['reason']."<br/>";
				
			}
			$scan_result .= ' 返回时间 '.date("Y-m-d H:i:s",$info['time_rep'])."<br/>";
		}
		$soft_list[$idx]['safe_desc'] = $virus;
		$soft_list[$idx]['time'] = $scan_result;
	}
	$this -> assign('page',$page -> show());
	$this -> assign('softlist',$soft_list);
	$this -> display('modifypkgsaft_list');
  }
  
  function modifypkgsafe(){
	$model = new Model();	
	$where = array();
	$where['softid'] = $_GET['id'];
	$where['safe'] = -1;
	$data = array();
	$count = $model -> table('sj_soft') -> where($where) -> count();
	if($count == 0){
		$this -> error('你操作为非法操作');
	}
	if($_GET['type'] == 1){ //软件下线
		$data['hide'] = 3;
		$data['safe'] = 2;
		$data['last_refresh'] = time();
		$mark = '下线';
		$data['deny_msg'] = '不安全审核下线软件';
	} elseif($_GET['type'] == 2) { //软件saft = 1
		$data['safe'] = 1;
		$data['deny_msg'] = '';
		$data['last_refresh'] = time();
		$mark = '变为安全软件';
 	}
	$package = $model -> table('sj_soft') -> where($where) -> find();
	$affect = $model -> table('sj_soft') -> where($where) -> save($data);
	if($affect){
		//$this -> writelog("softid 为 ".$_GET['id'].'包名为'.$package['package'].' '.$mark);
		$this->writelog("softid 为 ".$_GET['id']."软件已{$mark}", 'sj_soft',$_GET['id'],__ACTION__ ,'','edit');
		$this -> assign("jumpUrl","/index.php/".GROUP_NAME."/whitepkg/modifypkgsaft_list");
		$this -> success("该软件已".$mark);
	}
  }
}
