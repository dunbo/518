<?php 

Class ForumFirstPageAction extends CommonAction{
	private $model;
	
	public function _initialize() {
        parent::_initialize();
        $this->model = D('Zhiyoo.ForumFirstPage');
    }
	
	public function index(){
		$result = $this->model-> select();
		$this -> assign('result',$result);
		$this -> display();
	}
	
	public function chstatus(){
		$result = $this->model ->save(array('id'=>1,'status'=>$_GET['status']));
		
		if($result !== false){
			$this->writelog("智友-智友功能配置-发帖功能配置 已更改id为1状态为".$_GET['status'],"zy_forum_first_page",1,__ACTION__ ,"","edit");
			$this->success("更改状态成功！");
		}else{
			$this->error("更改状态失败！");
		}
	}
}