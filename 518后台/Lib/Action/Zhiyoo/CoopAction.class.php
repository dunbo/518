<?php 

Class CoopAction extends CommonAction{
	private $model;
	
	public function _initialize() {
        parent::_initialize();
        $this->model = D('Zhiyoo.Coop');
    }
	
	public function index(){
        $proname = empty($_GET['proname']) ? '' : trim($_GET['proname']);
        $comname = empty($_GET['comname']) ? '' : trim($_GET['comname']);
        
        $where = array('status' => 1);
        if(isset($_GET['wstatus'])){
            $where['wstatus'] = intval($_GET['wstatus']);
        }
        if(isset($_GET['procate'])){
            $where['procate'] = intval($_GET['procate']);
        }
        if($proname){
            $where['proname'] = array('like','%'.$proname.'%');
        }
        if($comname){
            $where['comname'] = array('like','%'.$comname.'%');
        }
        import("@.ORG.Page");
		$param = http_build_query($_GET);
		$count = $this->model -> where($where)->count();
		
		$Page = new Page($count, 20, $param);
		$result = $this->model -> where($where)-> limit($Page->firstRow . ',' . $Page->listRows)->order('dateline desc')-> select();
		$wstatus = $this->model -> wstatus();
		$procate = $this->model -> procate();
		
		//保留标签功能
		$show = $Page->show();
		$lr = isset($_GET['lr']) ? $_GET['lr'] : 20;
		$p = isset($_GET['p']) ? $_GET['p'] : 1;
		
		$Page->setConfig('header', '篇记录');
		$Page->setConfig('first', '<<');
		$Page->setConfig('last', '>>');
		$this -> assign('lr',$lr);
		$this -> assign('p',$p);
        $this -> assign("page", $show);
		$this -> assign('wstatus',$wstatus);
		$this -> assign('procate',$procate);
		$this -> assign('result',$result);
		$this -> display();
	}
	
	
	public function procate(){
        import("@.ORG.Page");
		$param = http_build_query($_GET);
		$count = $this->model->table('zy_coop_procate') -> where('status=1')->count();
		
		$Page = new Page($count, 20, $param);
		$procate = $this->model->table('zy_coop_procate') -> where('status=1')-> limit($Page->firstRow . ',' . $Page->listRows)-> select();
        foreach($procate as $k => $v){
            $procate[$k]['count'] = $this->model ->where(array('procate'=>$v['procate']))->count();
        }
		$show = $Page->show();
        $this -> assign("page", $show);
		$this -> assign('procate',$procate);
		$this -> display();
	}
    
	public function wstatus(){
        import("@.ORG.Page");
		$param = http_build_query($_GET);
		$count = $this->model->table('zy_coop_wstatus') -> where('status=1')->count();
		
		$Page = new Page($count, 20, $param);
		$wstatus = $this->model->table('zy_coop_wstatus') -> where('status=1')-> limit($Page->firstRow . ',' . $Page->listRows)-> select();
        foreach($wstatus as $k => $v){
            $wstatus[$k]['count'] = $this->model ->where(array('wstatus'=>$v['wstatus']))->count() ;
        }
		$show = $Page->show();
        $this -> assign("page", $show);
		$this -> assign('wstatus',$wstatus);
		$this -> display();
	}
    
	public function add(){
		
		$this -> assign('type',$_GET['type']);
		$this -> display();
	}
    
	public function doadd(){
        $data = array();
        if($_GET['type'] == 'wstatus'){
            $data['wsname'] = trim($_POST['name']);
            $data['status'] = array('egt',0);
            if(empty($data['wsname'])) $this->error("添加失败！名称不能为空!");
            $result = $this->model->table('zy_coop_wstatus')-> where(array('wsname'=>$data['wsname'],'status' => array('egt',0)))->find();
            if($result) $this->error("添加失败！名称已存在");
            $result = $this->model->table('zy_coop_wstatus')-> add($data);
        }elseif($_GET['type'] == 'procate'){
            $data['procname'] = trim($_POST['name']);
            if(empty($data['procname'])) $this->error("添加失败！名称不能为空!");
            $result = $this->model->table('zy_coop_procate')-> where(array('procname'=>$data['procname'],'status' => array('egt',0)))->find();
            if($result) $this->error("添加失败！名称已存在");
            $result = $this->model->table('zy_coop_procate')-> add($data);
        }
        
		if($result !== false){
			$this -> writelog("智友-厂商合作 添加id{$result}",'zy_coop_'.$_GET['type'],$result,__ACTION__,'','add');
			$this->success("添加成功！");
		}else{
			$this->error("添加失败！");
		}
	}
	
	public function info(){
        $result = $this->model-> where('coopid = '.$_GET['id'])->find();
		$this -> assign('val',$result);
		$this -> display();
	}
	
	public function edit(){
		$wstatus = $this->model -> wstatus();
		$procate = $this->model -> procate();
        $result = $this->model-> where('coopid = '.$_GET['id'])->find();
		$this -> assign('wstatus',$wstatus);
		$this -> assign('procate',$procate);
		$this -> assign('val',$result);
		$this -> display();
	}
	
	public function edit_name(){
        if($_GET['type'] == 'wstatus'){
            $result = $this->model->table('zy_coop_wstatus')->where('wstatus='.$_GET['id'])-> find($data);
            $name = $result['wsname'];
        }elseif($_GET['type'] == 'procate'){
            $result = $this->model->table('zy_coop_procate')->where('procate='.$_GET['id'])-> find($data);
            $name = $result['procname'];
        }
		$this -> assign('type',$_GET['type']);
		$this -> assign('name',$name);
		$this -> display();
	}
	
	public function doedit(){
        $data = array();
        if($_GET['type'] == 'com'){
            $data['procate'] = $_POST['procate'];
            $data['wstatus'] = $_POST['wstatus'];
            $data['remark'] = $_POST['remark'];
            $result = $this->model->where('coopid='.$_GET['id'])-> save($data);
        }elseif($_GET['type'] == 'wstatus'){
            $data['wsname'] = trim($_POST['name']);
            $result = $this->model->table('zy_coop_wstatus')-> where(array('wsname'=>$data['wsname'],'wstatus'=>array('neq',$_GET['id']),'status' => array('egt',0)))->find();
            if($result) $this->error("编辑失败！名称已存在");
            $result = $this->model->table('zy_coop_wstatus')->where('wstatus='.$_GET['id'])-> save($data);
        }elseif($_GET['type'] == 'procate'){
            $data['procname'] = trim($_POST['name']);
            $result = $this->model->table('zy_coop_procate')-> where(array('procname'=>$data['procname'],'procate'=>array('neq',$_GET['id']),'status' => array('egt',0)))->find();
            if($result) $this->error("编辑失败！名称已存在");
            $result = $this->model->table('zy_coop_procate')->where('procate='.$_GET['id'])-> save($data);
        }
        
		if($result !== false){
			$this -> writelog("智友-厂商合作 编辑id{$_GET['id']}",'zy_coop'.($_GET['type'] == 'com' ? '' : '_'.$_GET['type']),$_GET['id'],__ACTION__,'','edit');
			$this->success("编辑成功！");
		}else{
			$this->error("编辑失败！");
		}
	}
	
	public function del(){
        if($_GET['type'] == 'com'){
            $result = $this->model->where('coopid='.$_GET['id'])-> save(array('status'=>-1));
        }elseif($_GET['type'] == 'wstatus'){
            $result = $this->model->table('zy_coop_wstatus')->where('wstatus='.$_GET['id'])-> save(array('status'=>-1));
        }elseif($_GET['type'] == 'procate'){
            $result = $this->model->table('zy_coop_procate')->where('procate='.$_GET['id'])-> save(array('status'=>-1));
        }
        
		if($result !== false){
			$this -> writelog("智友-厂商合作 已删除id{$_GET['id']}",'zy_coop'.($_GET['type'] == 'com' ? '' : '_'.$_GET['type']),$_GET['id'],__ACTION__,'','del');
			$this->success("删除成功！");
		}else{
			$this->error("删除失败！");
		}
	}
}