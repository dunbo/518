<?php   
Class SearchWordAction extends CommonAction{
	private $bbsmodel;
	private $model;
	
	public function _initialize() {
        parent::_initialize();
		$this->bbsmodel = D('Zhiyoo.bbs');
		$this->model = D('Zhiyoo.Zhiyoo');
	}
	
	function show_word(){
		$type = $_GET['type']?$_GET['type']:1;

		if($_GET['editrank']){
			$this -> assign('editrank',1);
		}
		$map = array('status'=>1,'type'=>$type);
		$res = $this -> model -> table('zy_test_searchkeyword') -> where($map)->order('rank asc') -> select();
		$this -> assign('keywords',$res);
		$this -> assign('type',$type);
		$this -> display();
	}
	
	function add(){
		$type = $_GET['type']?$_GET['type']:1;
		$this -> assign('type',$type);
		$this -> display();	
	}
	
	function add_submit(){
		$data['name'] = trim($_POST['wname']);
		$data['description'] = trim($_POST['desc']);
		$data['rank'] = trim($_POST['order']);
		$data['type'] = intval($_POST['type']);
		if(!$data['name']){
			$this ->error("添加失败，搜索词不能为空");
		}
		if(!$data['description']){
			$this ->error("添加失败，文案不能为空");
		}
		if(!$data['rank']){
			$this ->error("添加失败，序号不能为空");
		}
		
		if(!$_POST['id']){
			$result = $this ->model -> table('zy_test_searchkeyword') -> where("name={$data['name']} and status=1")->find();
			if($result){
				$this -> error('添加失败，搜索词已经存在');
			}
			$result = $this-> model -> table('zy_test_searchkeyword') -> add($data);	
			if($result){
				$this -> writelog("智友内容管理-众测搜索词配置 添加了id为{$result}的搜索词","zy_test_searchkeyword",$result,__ACTION__ ,"","add");
				$this -> assign('jumpUrl',"/index.php/Zhiyoo/SearchWord/show_word/type/{$data['type']}");
				$this -> success("添加成功");
				
			}
		}else{
			$result = $this ->model -> table('zy_test_searchkeyword') -> where("name={$data['name']} and id!={$_POST['id']} and status=1")->find();
			if($result){
				$this -> error('编辑失败，搜索词已经存在');
			}
			$result = $this-> model -> table('zy_test_searchkeyword') ->where("id={$_POST['id']}")-> save($data);	
			if($result){
				$this -> writelog("智友内容管理-众测搜索词配置 编辑了id为{$_POST['id']}的搜索词","zy_test_searchkeyword",$_POST['id'],__ACTION__ ,"","edit");
				$this -> assign('jumpUrl',"/index.php/Zhiyoo/SearchWord/show_word/type/{$data['type']}");
				$this -> success("编辑成功");
				
			}else{
				$this -> error('编辑失败,没有改动');
			}
			
			
		}
	}
	
	function del(){
		$id = $_GET['id'];
		$words = $this->model->table('zy_test_searchkeyword')->where(array('id'=>$id))->find();
		if(empty($id) || empty($words['id'])){
			$this->error('id为空，删除失败');
		}
		$data['id'] = $id;
		$data['status'] = 0;
		$res = $this -> model -> table('zy_test_searchkeyword') -> save($data);
		if($res){			
			$this -> writelog("智友内容管理-众测搜索词配置 已删除id为{$id}的运营位","zy_test_searchkeyword",$id,__ACTION__ ,"","del");
			$this -> assign('jumpUrl',"/index.php/Zhiyoo/SearchWord/show_word/type/{$words['type']}");
			$this -> success("删除成功");
		}else{
			$this -> error("删除失败");
		}
	}
	
	function edit(){
		$id = $_GET['id'];
		if(!$id){
			$this -> error('没有选择搜索词');
			
		}
		$res = $this -> model ->table('zy_test_searchkeyword') -> where("id={$id}")->find();

		$this -> assign('word',$res);
		$this -> assign('type',$res['type']);
		$this->display('add');
		
	}
	
	function editrank(){
		$type = $_POST['type'] ? $_POST['type'] :1;
		foreach($_POST['order'] as $id =>$order){
			$this->model->table('zy_test_searchkeyword')->where('id='.$id)->save(array('rank'=>$order));	
			$this -> writelog("智友内容管理-众测搜索词配置 已编辑id为{$id}运营位的优先级为".$order,"zy_test_searchkeyword",$id,__ACTION__ ,"","edit");
		}
		$this -> assign('jumpUrl',"/index.php/Zhiyoo/SearchWord/show_word/type/{$type}/");
		$this -> success("编辑优先级成功");
		
		
	}
	
	
	
	
	
	
	
	
}