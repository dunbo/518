<?php 

Class PushConfigAction extends CommonAction{
	private $model;
    
	public function _initialize() {
        parent::_initialize();
        $this->model = D('Zhiyoo.Zhiyoo');
    }
    
	public function conflist(){
		$conf = $this->model->table('zy_push_conf')->find();
		$this->assign('conf',$conf);
		if($_GET['edit'] == 1){
			$this->assign('edit',1);
		}
		$this->display();
	}
	
	public function confsave(){
		$update['frequency'] = (int)$_POST['frequency'];
		$update['push_level'] = (int)$_POST['push_level'];
		$update['service_alive'] = $_POST['service_alive'];
		$update['min_push'] = (int)$_POST['min_push'];
		$update['con_alive'] = (int)$_POST['con_alive'];
		$update['push_switch'] = (int)$_POST['push_switch'];
		$update['hook_switch'] = (int)$_POST['hook_switch'];
		$res = $this->model->table('zy_push_conf')->find();
		if($res){
			$this->model->table('zy_push_conf')->where("id={$res['id']}")->save($update);
		}else{
			$this->model->table('zy_push_conf')->add($update);
		}
		$this -> writelog('编辑了推送配置');
		$this->assign("jumpUrl","__URL__/conflist/");
		$this->success("推送配置编辑成功");
		
	}
    
	
}