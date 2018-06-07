<?php 
Class FriendLinkAction extends CommonAction{
	private $model;
	private $modelApply;
	
	public function _initialize() {
        parent::_initialize();
		$this->model = D('Zhiyoo.FriendLink');
		$this->modelApply = D('Zhiyoo.FriendLinkApply');
	}
	
	public function linklist(){
		$type = intval($_GET['type']) ? intval($_GET['type']) : 1;
		$order = "status desc,rank asc";
		$_GET['ordertype'] == 'rank' && $order = 'rank '.$_GET['order'];
		
		$linkdata = $this->model->where(array('status'=>array('exp','>=0'),'type'=>$type))->order($order)->select();
		
		$this -> assign('order',$_GET['order']=='ASC' ? 'DESC' : 'ASC');
		$this -> assign('linkdata',$linkdata);
		$this -> assign('type',$type);
		$this -> display();
	}
	
	public function add_link(){
		if($_POST){
			$names = trim($_POST['names']);
			$link = trim($_POST['link']);
			$rank = intval($_POST['rank']);
			$type = intval($_GET['type']);
			if (empty($names) ||empty($link) ||empty($type) ) {
				$this->error("参数不能为空！");
			}
			$data = array('names'=>$names,'link'=>$link,'rank'=>$rank,'type'=>$type);
			$result = $this->model->add($data);
			$this -> writelog('添加友情链接id为'.$result,"zy_friend_link",$result,__ACTION__ ,"","add");
			$this->success("添加成功！");
		}
		$this -> display();
	}
	
	public function edit_link(){
		$id = intval($_GET['id']);
		if ($id<1) {
			$this->error("参数有误！");
		}
		if ($_POST && isset($_POST['names'])) {
			$names = trim($_POST['names']);
			$link = trim($_POST['link']);
			$rank = intval($_POST['rank']);
			if (empty($names) ||empty($link)) {
				$this->error("参数不能为空！");
			}
			$times = time();
			$result = $this->model->where(array('id'=>$id)) -> save(array('names'=>$names,'link'=>$link,'rank'=>$rank));
			$this -> writelog('编辑友情链接展示名称:'.$names.' | 跳转链接:'.$link.'  | rank:'.$rank.' | id为'.$id,"zy_friend_link",$id,__ACTION__ ,"","edit");
			$this->success("编辑成功！");
		}
	
		$linkdata = $this->model->where(array('status'=>array('exp','>=0'),'id'=>$id))->find();
		$this -> assign('link',$linkdata);
		$this -> assign('id',$id);
		$this -> display();
	}
	
	public function edit_rank(){
		if (isset($_POST['level'])){
			$idstr = '';
			foreach ($_POST['level'] as $k=>$v){
				$v = abs(intval($v));
				$k = abs(intval($k));
				$p_ret = $this->model->where(array('id'=>$k)) -> save(array('rank'=>$v));
				$idstr .= $k.',';
			}
			$jsonarr = '友情链接 优先级 id:rank'.json_encode($_POST['level']);
			$this -> writelog($jsonarr,"zy_friend_link",$idstr,__ACTION__ ,"","edit");
			$this->success("编辑优先级成功！");
		}
	}
	public function updateStatus(){
		$id = intval($_GET['id']);
		$status = intval($_GET['status']);
		if ($id<1) {
			$this -> writelog('更改友情链接 状态参数有误id为'.$id);
			$this->error("参数有误！");
		}
		$ret = $this->model->where(array('id'=>$id)) -> save(array('status'=>$status));
		if($ret!==false){
			$this -> writelog('更改友情链接配置id为'.$id.' status '.$status,"zy_friend_link",$id,__ACTION__ ,"","del");
			$this->success("更改成功！");
		}else{
			$this -> writelog('更改友情链接状态参数有误id为'.$id,"zy_friend_link",$id,__ACTION__ ,"","del");
			$this->error("参数有误！");
		}
	}
	
	public function apply(){
        
		$count = $this->modelApply->where(array('status'=>1))->count();
        
		import("@.ORG.Page");
		$param = http_build_query($_GET);
		$Page = new Page($count, 20, $param);
		$show = $Page->show();
        
		$linkdata = $this->modelApply->where(array('status'=>1))->order('dateline desc')-> limit($Page->firstRow . ',' . $Page->listRows)->select();
		
        $this -> assign("page", $show);
		$this -> assign('linkdata',$linkdata);
		$this -> display();
	}
	
	public function applyBox(){
		$id = intval($_GET['id']) ? intval($_GET['id']) : 1;
		$linkdata = $this->modelApply->where(array('id'=>$id))->find();
        
		$this -> assign('val',$linkdata);
		$this -> display();
	}
    
	public function applyStatus(){
		$id = intval($_GET['id']);
		$status = intval($_GET['status']);
		if ($id<1) {
			$this -> writelog('删除友情链接-申请友链 状态参数有误id为'.$id);
			$this->error("参数有误！");
		}
		$ret = $this->modelApply->where(array('id'=>$id)) -> save(array('status'=>$status));
		if($ret!==false){
			$this -> writelog('删除友情链接-申请友链配置id为'.$id.' status '.$status);
			$this->success("删除成功！");
		}else{
			$this -> writelog('更改友情链接状态参数有误id为'.$id);
			$this->error("参数有误！");
		}
	}
	
}