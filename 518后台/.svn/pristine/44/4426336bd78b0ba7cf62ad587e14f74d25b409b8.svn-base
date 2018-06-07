<?php 

Class InstructionAction extends CommonAction{
	private $model;
	
	public function _initialize() {
        parent::_initialize();
        $this->model = D('Zhiyoo.Instruction');
    }
	
	public function index(){
        $result = $this->model->where('status=1')->select();
		$this -> assign('result',$result);
		$this -> display();
	}
	
	public function edit(){
        $where = array('id'=>$_GET['id']);
        $result = $this->model->where($where)->select();
		$this -> assign('result',$result[0]);
		$this -> display();
	}
	
	public function doedit(){
        $where = array('id'=>$_GET['id']);
        $name = trim($_POST['name']);
        $text = trim($_POST['text']);
        $data = array('name' => $name,'text' => $text);
        
        $this->model->where($where)->save($data);
        $this -> writelog("智友内容管理-说明文案管理 已编辑id为{$_GET['id']}的说明文案",'zy_instruction',$_GET['id'],__ACTION__,'','edit');
        $this -> assign('jumpUrl', '/index.php/Zhiyoo/Instruction/index');
		$this->success("编辑成功！");
	}
	
	public function add(){
		$this -> display();
	}
	
	public function doadd(){
        $name = trim($_POST['name']);
        $text = trim($_POST['text']);
        $data = array('name' => $name,'text' => $text);
        
        $res = $this->model->add($data);

        $this -> writelog("智友内容管理-说明文案管理 已添加id为{$res}的说明文案",'zy_instruction',$res,'/index.php/Zhiyoo/Instruction/index','','add');
        $this -> assign('jumpUrl', '/index.php/Zhiyoo/Instruction/index');
		$this->success("添加成功！");
	}
}