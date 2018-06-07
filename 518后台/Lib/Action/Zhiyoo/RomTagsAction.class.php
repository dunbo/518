<?php 
/*
 * ROM页面标签配置 
 *  @author renhj
 *  2016-02-11 10:00
 */
Class RomTagsAction extends CommonAction{
	private $model;
	private $zyromtable = "zy_rom_tags";
	private $zygrouptable = "zy_tagsgroup";
	
	public function _initialize() {
        parent::_initialize();
		$this->model = D('Zhiyoo.Zhiyoo');
	}
	public function romtagslist(){
		$order = "ASC";
		$orderBy = 'id '.$order;
		if (isset($_REQUEST['field']) and isset($_REQUEST['order'])) {
			$field = addslashes(trim($_REQUEST['field']));
			$order = addslashes(trim($_REQUEST['order']));
			$orderBy = "$field $order";
		}
		$grouparr = array();
		$groupdata = $this->model->table($this->zygrouptable)->where(array('status'=>1))->order('rank')->select();
		foreach ($groupdata as $key=>$gval){
			$grouparr[$gval['groupid']] = $gval['groupname'];
		}
		$romdata = $this->model->table($this->zyromtable)->where(array('status'=>0))->order($orderBy)->select();
		foreach ($romdata as $rkey=>$rval){
			$romdata[$rkey]['groupname'] = $grouparr[$rval['groupid']];
		}
		$this -> assign('order',$order=='ASC' ? DESC : ASC);
		$this -> assign('romdata',$romdata);
		$this -> display();
	}
	public function add_romtags(){
		if (isset($_POST) && isset($_POST['names'])) {
			$name = addslashes(trim($_POST['names']));
			$groupid = intval($_POST['groupid']);
			if ($name=='' || !$groupid) {
				$this->error("参数不能为空！");
			}
			$data = array('romname'=>$name,'groupid'=>$groupid,'createdate'=>time(),'modifytime'=>time());
			$result = $this->model->table($this->zyromtable)->add($data);
			$this -> writelog('添加ROM页面标签配置id为'.$result,$this->zyromtable,$result,__ACTION__,'','add');
			$this->success("添加成功！");
		}
		
		$romdata = $this->model->table($this->zyromtable)->where(array('status'=>0))->field('id,groupid')->select();
		$gidarr = $grouparr = array();
		foreach ($romdata as $rval){
			$gidarr[] = $rval['groupid'];
		}
		$groupdata = $this->model->table($this->zygrouptable)->where(array('status'=>1))->order('rank')->select();
		foreach ($groupdata as $key=>$gval){
			$grouparr[$key] = $gval;
			$group_id = $gval['groupid'];
			$isexist =  (in_array($group_id, $gidarr)) ? 1 : 0;
			$grouparr[$key]['exist'] = $isexist;
		}
		$this -> assign('groupdata',$grouparr);
		$this -> display();
	}
	public function edit_romtags(){
		$id = intval($_REQUEST['id']);
		if ($id<1) {
			$this -> writelog('修改ROM页面标签配置 状态参数有误id为'.$id);
			$this->error("参数有误！");
		}
		if (isset($_POST) && isset($_POST['names'])) {
			$name = addslashes(trim($_POST['names']));
			$groupid = intval($_POST['groupid']);
			if ($name=='' || !$groupid) {
				$this->error("参数不能为空！");
			}
			$times = time();
			$data = array('romname'=>$name,'groupid'=>$groupid,'createdate'=>time());
			$result = $this->model -> query("UPDATE $this->zyromtable SET romname='$name', groupid='$groupid',modifytime='$times' where  id='$id'");
			$this -> writelog('编辑ROM页面标签配置展示名称:'.$name.' | 标签类型id:'.$groupid.' | id为'.$id,$this->zyromtable,$id,__ACTION__,'','edit');
			$this->success("编辑成功！");
		}
	
		$romdata = $this->model->table($this->zyromtable)->where(array('status'=>0))->field('id,romname,groupid')->select();
		$gidarr = $grouparr = array();
		$romname = '';
		$romgroupid = '';
		foreach ($romdata as $rval){
			$gidarr[] = $rval['groupid'];
			if ($rval['id'] == $id) {
				$romname = $rval['romname'];
				$romgroupid = $rval['groupid'];
			}
		}
		$groupdata = $this->model->table($this->zygrouptable)->where(array('status'=>1))->order('rank')->select();
		foreach ($groupdata as $key=>$gval){
			$grouparr[$key] = $gval;
			$group_id = $gval['groupid'];
			$isexist =  (in_array($group_id, $gidarr)) ? 1 : 0;
			if ($romgroupid == $group_id) {
				$isexist = 2;
			}
			$grouparr[$key]['exist'] = $isexist;
		}
		$this -> assign('groupdata',$grouparr);
		$this -> assign('romname',$romname);
		$this -> assign('id',$id);
		$this -> display();
	}
	public function edit_rank(){
		if (isset($_POST['level'])){
			$idstr = '';
			foreach ($_POST['level'] as $k=>$v){
				$v = abs(intval($v));
				$p_ret = $this->model -> query("UPDATE $this->zyromtable SET rank='$v' where  id='$k'");
				$idstr .= $k.',';
			}
			$jsonarr = 'ROM页面标签配置 优先级 id:rank'.json_encode($_REQUEST['level']);
			$this -> writelog($jsonarr,$this->zyromtable,$idstr,__ACTION__,'','edit');
			$this->success("编辑优先级成功！");
		}
	}
	public function updateStatus(){
		$id = intval($_REQUEST['id']);
		$status = intval($_REQUEST['status']);
		if ($id<1) {
			$this -> writelog('修改ROM页面标签配置 状态参数有误id为'.$id);
			$this->error("参数有误！");
		}
		$modifytime = time();
		$ret = $this->model->query("UPDATE $this->zyromtable SET status='$status' ,modifytime=$modifytime  WHERE `id` =$id");
		if($ret!==false){
			$this -> writelog('删除ROM页面标签配置id为'.$id,$this->zyromtable,$id,__ACTION__,'','del');
			$this->success("删除成功！");
		}else{
			$this -> writelog('修改ROM页面标签配置状态参数有误id为'.$id,$this->zyromtable,$id,__ACTION__,'','edit');
			$this->error("参数有误！");
		}
	}
	
}