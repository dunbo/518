<?php   
Class ClientTestModAction extends CommonAction{
	private $bbsmodel;
	private $model;
	
	public function _initialize() {
        parent::_initialize();
		$this->model = D('Zhiyoo.ClientTestMod');
	}
	
	function index(){
		$res = $this -> model -> order('position asc') -> select();
		foreach($res as $key => $val){
			$zt = $this -> model -> table('zy_column_conf')-> where(array('cid'=>$val['pid'])) -> field('name') -> find();
			$res[$key]['zt'] = $zt['name'];
		}
		$this -> assign('res',$res);
		$this -> display();	
	}
	
	
	function edit(){
		$id = $_GET['id'];
		if(!$id){
			$this -> error('ID错误');
		}
		$res = $this -> model -> where(array('position'=>$id))->find();
		$pref = $this->model->table('zy_column_conf')->where('status=1 and type=1 and platform=2')->select();
		$this -> assign('id',$id);
		$this -> assign('res',$res);
		$this -> assign('pref',$pref);
		$this->display();
		
	}
	
	function doedit(){
		$id = $_GET['id'];
		if(!$id){
			$this -> error('ID错误');
		}
		$data['text'] = $_POST['text'];
		$data['pid'] = $_POST['pid'];
		$res = $this -> model -> where(array('position'=>$id))->save($data);
		if($res !== false){
			$this -> writelog("智友内容管理-众测页面模块配置 已编辑id为{$id}的运营位","zy_client_test_mod",$id,__ACTION__ ,"","edit");
			$this -> success("编辑成功");
		}
		
	}
	
	function changestatus(){
		$status = $_GET['status'] == 1 ? 0 : 1;
		$txt = $status == 0 ? '停用' : '启用';
		$id = $_GET['id'];
		if(!$id){
			$this -> error('id不能为空');
		}
		$data['status'] = $status;
		$data['position'] = $id;
		$res = $this -> model -> save($data);
		if($res){
			$this -> writelog("智友内容管理-众测页面模块配置 已{$txt}id为{$id}的运营位","zy_client_test_mod",$id,__ACTION__ ,"","edit");
			$this -> success("{$txt}成功");
		}
	}
	
	
	
	
	
	
	
	
}