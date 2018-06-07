<?php
class OlgamerankingAction extends CommonAction{
	public function display_form(){
		$model = new Model();
		$where = array(
			'config_type' => 'olgame_config',
			'configname' => 'olgame_config'
		);
		$list = $model -> table('pu_config') -> where($where) -> limit(1) -> select();
		$content = json_decode($list[0]['configcontent'],true);
		$this -> assign('content',$content);  
		$this -> display('displayform');
	}
	//排行榜备选库
	public function addreadysoft(){
		if($_GET['do'] == 'html'){
			$this -> display('addreadysoft');
			exit;
		}
		$model = new Model();
		$where = array(
			'package' => trim($_POST['package']),
			'status' => 1,
			'hide' => 1,
		);
		if(!preg_match('/^[0-9]+$/',trim($_POST['level']))){
			$this -> error('请输入有效数字');
		}
		if($_POST['level'] > 999 || $_POST['level'] < 1){
			$this -> error('优先级不能超过999 不能低于1');
		}
		$cnt = $model -> table('sj_soft') -> where($where) -> count(); 
		if($cnt > 0){
			$where = array(
				'package' => trim($_POST['package']),
				'status' => 1,
 			);
			$cnt = $model -> table('sj_olgame_ready_soft') -> where($where) -> count(); 
			if($cnt > 0) $this -> error('该软件已经存在备选库');
			$data['package'] = trim($_POST['package']);
			$data['level'] = trim($_POST['level']);
			
			$data['create_tm'] = time();
			$data['updated_tm'] = time();
			$data['status'] = 1;
			$affect = $model -> table('sj_olgame_ready_soft') -> add($data);
			if($affect){
				$this -> writelog('添加备选软件包名 pkg:'.trim($_POST['package']), 'sj_olgame_ready_soft', $affect,__ACTION__ ,'','add');
				$this -> assign('jumpUrl','/index.php/'.GROUP_NAME.'/Olgameranking/readysoftlist');
				$this -> success('备选软件添加成功');
			}
		}else{
			$this -> error('你选择的软件为无效软件');
		}
	}
	//备选库软件列表
    function readysoftlist(){
		import('@.ORG.Page');
		$model = new Model();		
		$where = array(
			'status' =>1,
		);
		if($_GET['softname']){
			$w = array();
			$w['softname'] = array('like',"%".trim($_GET['softname'])."%");
			$w['hide'] = 1;
			$w['status'] = 1;
			$list = $model -> table('sj_soft') -> where($w) ->field('package')-> select();
			$package_arr = array();
			foreach($list as $info){
				$package_arr[] = $info['package'];
			}
			$where['package'] = array('in',$package_arr);
			$this -> assign('softname',$_GET['softname']);
		}
		if($_GET['package']){
			$where['package'] = array('like',"%".trim($_GET['package'])."%");
			$this -> assign('package',$_GET['package']);
		}
		if($_GET['level']){
			$where['level'] = array('like',"%".trim($_GET['level'])."%");
			$this -> assign('level',$_GET['level']);
		}
 		$count = $model -> table('sj_olgame_ready_soft') -> where($where) -> count(); 
		$page = new Page($count,20);
		$list = $model -> table('sj_olgame_ready_soft') -> where($where) ->order('level asc') -> limit($page -> firstRow .','. $page -> listRows) -> select();
		foreach($list as $idx => $info){
			$where = array(
				'package' => $info['package'],
				'status' => 1,
				'hide' => 1,
 			);
			$softname = $model -> table('sj_soft') -> where($where) ->  getField('softname');
			$list[$idx]['softname'] = $softname;
		}
		$this -> assign('page',$page -> show());
		$this -> assign('list',$list);
		$this -> display('readysoftlist');				
	}
	//编辑备选库软件
	function editreadysoft(){
		$id = $_GET['id'];
		$package = $_GET['package'];
		$model  = new Model();
		$where = array(
			'id' => $id,
			'package' => $package,
			'status' => 1
		);
		$info = $model -> table('sj_olgame_ready_soft') -> where($where) ->select();

		$where = array(
			'package' => $package,
		);
		$softname = $model -> table('sj_soft') -> where($where) -> limit(1) -> getField('softname');

		if($info){
			$this -> assign('softname',$softname);
			$this -> assign('package',$info[0]['package']);
			$this -> assign('id',$info[0]['id']);
			$this -> assign('level',$info[0]['level']);
			$this -> display('editreadysoft');
		}else{
			$this -> error('该软件不存在');
		}		
	}
	//编辑备选库软件_do
	function editreadysoft_do(){
		$id = $_POST['id'];
		$package = $_POST['package'];
		$model  = new Model();
		$where = array(
			'id' => $id,
			'package' => $package,
			'status' => 1,
		);
		if(!preg_match('/^[0-9]+$/',trim($_POST['level']))){
			$this -> error('请输入有效数字');
		}
		if($_POST['level'] > 999 || $_POST['level'] < 1){
			$this -> error('优先级不能超过999 不能低于1');
		}
		$count = $model -> table('sj_olgame_ready_soft') -> where($where) ->count(); 
		if($count == 1){
			$data = array(
				'level' => trim($_POST['level']),
				'updated_tm' => time(), 
			);
			$affect = $model -> table('sj_olgame_ready_soft') -> where($where) -> save($data);
			if($affect){
				$this -> writelog('编辑备选软件包'.$_POST['package'].' level修改为 '.$_POST['level'], 'sj_olgame_ready_soft', $affect,__ACTION__ ,'','edit');
				$this -> assign('jumpUrl','/index.php/'.GROUP_NAME.'/Olgameranking/readysoftlist');
				$this -> success('编辑成功');
			}else{
				$this -> error('编辑失败');
			}
		}		
	}
	//删除软件
	function deletesoft(){
		$model = new Model();
		$id = $_GET['id'];
		$package = $_GET['package'];
		$where = array(
			'id' => $id,
			'package' => $package,
		);
		$count = $model -> table('sj_olgame_ready_soft') -> where($where) -> count();
		if($count){
			$data = array(
				'status' => 0,
			);
			$affect = $model -> table('sj_olgame_ready_soft') -> where($where) -> save($data);
			if($affect){
				$this -> writelog('id为 '.$id.'包名为'.$package.'被删除 ', 'sj_olgame_ready_soft', $id,__ACTION__ ,'','del');
				$this -> assign('jumpUrl','/index.php/'.GROUP_NAME.'/Olgameranking/readysoftlist');
				$this -> success('删除成功');
			}else{
				$this -> error('删除失败');
			}
		}else{
			$this -> error('不存在该软件');
		}
	}
	
}
