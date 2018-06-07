<?php 

Class PostModAction extends CommonAction{
	private $model;
	
	public function _initialize() {
        parent::_initialize();
        $this->model = D('Zhiyoo.PostMod');
    }
	
	public function index(){
		$result = $this->model-> select();
		$this -> assign('result',$result);
		$this -> display();
	}
	
	public function settype(){
		foreach($_POST['type'] as $id => $type){
			$result = $this->model ->settype($id,$type);
			if($result) $this -> writelog("智友-智友功能配置-发帖功能配置 已更改id{$id}状态为".$type,"zy_post_mod",$id,__ACTION__ ,"","edit");
		}
		
		if($result !== false){
			$this->success("更改状态成功！");
		}else{
			$this->error("更改状态失败！");
		}
	}
}