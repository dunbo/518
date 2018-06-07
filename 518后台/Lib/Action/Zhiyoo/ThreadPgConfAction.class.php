<?php 

Class ThreadPgConfAction extends CommonAction{
	private $model = '';


	public function _initialize() {
        parent::_initialize();
		$this->model = D('Zhiyoo.Zhiyoo');
	}

	public function linkList(){
		$res = $this->model->table('zy_thread_link_conf')->find();
		$this->assign('result', $res);
		$this->display();
	}

	public function edit(){
		$res = $this->model->table('zy_thread_link_conf')->find();
		if($_POST['editsubmit']){
			if(!$_POST['linktext']){
				$this->error('文案不能为空');
			}
			if(!$_POST['linkhref']){
				$this->error('链接不能为空');
			}
			$data['linktext'] = $_POST['linktext'];
			$data['linkhref'] = $_POST['linkhref'];
			if(!$res){
				$this->model->table('zy_thread_link_conf')->add($data);
			}else{
				$this->model->table('zy_thread_link_conf')->where('1')->save($data);
			}
			$this->writelog("智友运营位管理-详情页文字链 编辑了详情页文字链配置", "zy_thread_link_conf", $result, __ACTION__ , "", "edit");
			$this->success('编辑成功');


		}else{
			$this->assign('res', $res);
			$this->display();
		}

	}

	public function changeSwitch(){
        $status = 0;
        $tips = '停用';
        if($_GET['status'] == 1){
            $status = 1;
            $tips = '开启';
        }
        
        $data = array('status'=>$status);
        $result = $this->model->table('zy_thread_link_conf')->where('1')->save($data);
        $this -> writelog("智友运营位管理-详情页文字链 {$tips}详情页文字链配置","zy_thread_link_conf",$result,__ACTION__ ,"","edit");
        $this->success($tips.'成功');
    }


}