<?php
Class ShopSwitchAction extends CommonAction{
    
    private $bbsmodel;
	private $model;
	
	public function _initialize() {
        parent::_initialize();
		// $this->bbsmodel = D('Zhiyoo.bbs');
		$this->model = D('Zhiyoo.Zhiyoo');
	}
    public function showList(){
        $res = $this->model->table('zy_common_setting')->where(array('skey'=>'shopswitch'))->find();
        if(!$res){
            $this->model->table('zy_common_setting')->add(array('skey'=>'shopswitch','svalue'=>0));
            $res['svalue'] = 0;
            $res['skey'] = 'shopswitch';
        }
        $this->assign('res',$res);
        $this->display();
    }
    
    public function changeSwitch(){
        $status = 0;
        $tips = '停用';
        if($_GET['status'] == 1){
            $status = 1;
            $tips = '开启';
        }
        
        $data = array('svalue'=>$status);
        $result = $this->model->table('zy_common_setting')->where("skey='shopswitch'")->save($data);
        $this -> writelog("智友内容管理-商城入口开关 {$tips}商城入口开关","zy_common_setting",$result,__ACTION__ ,"","edit");
        $this->success($tips.'成功');
    }
    
}