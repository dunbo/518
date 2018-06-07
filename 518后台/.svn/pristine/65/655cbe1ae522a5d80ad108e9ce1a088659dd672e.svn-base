<?php 
class NameCertifyAction extends CommonAction{
    private $model;


    public function _initialize() {
        parent::_initialize();
        $this->model = D('Zhiyoo.Zhiyoo');
    }
    
    public function showSwitch(){
		
        $tab_comment = array(
            'telswitch' => '未绑定用户不能发帖(手机号验证开关)',
            'marketswitch' => '未绑定用户不能发帖(市场内置智友)',
            'welldoswitch' => '什么值得玩'
        );
        $tab = array_keys($tab_comment);
        $res = $this->model->table('zy_common_setting')->select();
        $list = array();
        foreach ($res as $key => $value) {
            if(in_array($value['skey'],$tab)){
                $value['comment'] = $tab_comment[$value['skey']];
                $list[] = $value;
            } 
        }
        $this->assign('list',$list);
        $this->display();
    }
    
    public function changeSwitch(){
        $tel = $_GET['tel'];
		$status = 0;
        $tips = '停用';
        if($_GET['status'] == 1){
            $status = 1;
            $tips = '开启';
        }
        
        $data = array('svalue'=>$status);
        if($tel){
            $where = "skey='".$tel."'";
            $result = $this->model->table('zy_common_setting')->where($where)->save($data);
        }
        $this -> writelog("智友内容管理-实名认证-绑定手机开关 {$tips}绑定手机开关","zy_common_setting",$result,__ACTION__ ,"","edit");
        $this->success($tips.'成功');
    }
    




}