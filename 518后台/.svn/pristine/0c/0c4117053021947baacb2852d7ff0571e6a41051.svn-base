<?php 
/*
 *  友情链接配置
 *  @author renhj
 *  2016-03-24 11:00
 */
Class LinkAction extends CommonAction{
	private $model;
	private $zytable = "zy_friends_link";
	
	public function _initialize() {
        parent::_initialize();
		$this->model = D('Zhiyoo.Zhiyoo');
	}
	public function linklist(){
		$order = "DESC";
		$orderBy = 'status '.$order;
		if (isset($_REQUEST['field']) and isset($_REQUEST['order'])) {
			$field = addslashes(trim($_REQUEST['field']));
			$order = addslashes(trim($_REQUEST['order']));
			$orderBy = "$field $order";
		}
		$linkarr = array();
		$linkdata = $this->model->table($this->zytable)->where(array('status'=>array('exp','>=0')))->order($orderBy)->select();
		
		$this -> assign('order',$order=='ASC' ? DESC : ASC);
		$this -> assign('linkdata',$linkdata);
		$this -> display();
	}
	public function add_link(){
		if (isset($_POST) && isset($_POST['names'])) {
			$names = addslashes(trim($_POST['names']));
			$link = addslashes(trim($_POST['link']));
			$remark = addslashes(trim($_POST['remark']));
			if ($names=='' || $link=='') {
				$this->error("参数不能为空！");
			}
			$data = array('names'=>$names,'link'=>$link,'remark'=>$remark,'createdate'=>time(),'modifytime'=>time());
			$result = $this->model->table($this->zytable)->add($data);
			$this -> writelog('添加友情链接id为'.$result,$this->zytable,$result,__ACTION__ ,"","add");
			$this->success("添加成功！");
		}
		
		$this -> display();
	}
	public function edit_link(){
		$id = intval($_REQUEST['id']);
		if ($id<1) {
			$this -> writelog('编辑友情链接 状态参数有误id为'.$id);
			$this->error("参数有误！");
		}
		if (isset($_POST) && isset($_POST['names'])) {
			$names = addslashes(trim($_POST['names']));
			$link = addslashes(trim($_POST['link']));
			$remark = addslashes(trim($_POST['remark']));
			if ($names=='' || $link=='') {
				$this->error("参数不能为空！");
			}
			$times = time();
			$result = $this->model -> query("UPDATE $this->zytable SET names='$names', link='$link', remark='$remark',modifytime='$times' where  id='$id'");
			$this -> writelog('编辑友情链接展示名称:'.$names.' | 跳转链接:'.$link.' | id为'.$id,$this->zytable,$id,__ACTION__ ,"","edit");
			$this->success("编辑成功！");
		}
	
		$linkdata = $this->model->table($this->zytable)->where(array('status'=>array('exp','>=0')))->find();
		$this -> assign('link',$linkdata);
		$this -> assign('id',$id);
		$this -> display();
	}
	
	public function edit_rank(){
		if (isset($_POST['level'])){
			$id_str = '';
			foreach ($_POST['level'] as $k=>$v){
				$v = abs(intval($v));
				$p_ret = $this->model -> query("UPDATE $this->zytable SET rank='$v' where  id='$k'");
				$id_str .= $k.',';
			}
			$jsonarr = '友情链接 优先级 id:rank'.json_encode($_REQUEST['level']);
			$this -> writelog($jsonarr,$this->zytable,$id_str,__ACTION__ ,"","edit");
			$this->success("编辑优先级成功！");
		}
	}
	public function updateStatus(){
		$id = intval($_REQUEST['id']);
		$status = intval($_REQUEST['status']);
		if ($id<1) {
			$this -> writelog('更改友情链接 状态参数有误id为'.$id);
			$this->error("参数有误！");
		}
		$modifytime = time();
		$ret = $this->model->query("UPDATE $this->zytable SET status='$status' ,modifytime=$modifytime  WHERE `id` =$id");
		if($ret!==false){
			if($status == -1){
				$this -> writelog('删除友情链接配置id为'.$id,$this->zytable,$id,__ACTION__ ,"","del");
			}else{
				$this -> writelog('更改友情链接配置id为'.$id,$this->zytable,$id,__ACTION__ ,"","edit");
			}

			$this->success("更改成功！");
		}else{
			$this -> writelog('更改友情链接状态参数有误id为'.$id);
			$this->error("参数有误！");
		}
	}
	
}