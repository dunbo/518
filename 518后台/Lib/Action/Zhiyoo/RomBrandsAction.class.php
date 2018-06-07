<?php
/*
 * ROM品牌链接管理
 * @author renhj
 * 2016-03-11 15:00
 */

Class RomBrandsAction extends CommonAction{
	private $model;
	private $zytable = "zy_tags";
	private $zymanagetable = "zy_rom_manage";
	private $zybrandstable = "zy_rom_brands";
	private $groupid = 1;	//设置手机品牌id
	public function _initialize() {
        parent::_initialize();
		$this->model = D('Zhiyoo.Zhiyoo');
	}
	/*
	 * ROM品牌链接管理列表
	 */
	public function brandslist(){
		
		$tagsdata = $this->model->table($this->zytable)->where(array('status'=>1,'group'=>$this->groupid,'parentid'=>0))->order('rank ASC')->field('tagid,tagname,rank')->select();
		$mdata = $this->model->table($this->zymanagetable)->where(array('status'=>0))->order('rank ASC,id ASC')->field('id, mname, rank')->select();
		$data = $this->model->table($this->zybrandstable)->where(array('status'=>0))->field('id,tagid,manageid,url_val')->select();
		$brandsdata = $brandslist = array();
		foreach ($data as $key=>$val){
			$brandsdata[$val['tagid']][$val['manageid']] = $val['url_val'];
			$brandsdata[$val['tagid']]['id'] = $val['id'];
		}
		foreach ($tagsdata as $tval){
			$tagid = $tval['tagid'];
			foreach ($mdata as $mval){
				$mvalid = $mval['id'];
				if (isset($brandsdata[$tagid]) && isset($brandsdata[$tagid][$mvalid])) {
					$brandslist[$tagid][$mvalid] = $brandsdata[$tagid][$mvalid];
				}else{
					$brandslist[$tagid][$mvalid] = '';
				}
			}
		}
		$this -> assign('tagsdata',$tagsdata);
		$this -> assign('mdata',$mdata);
		$this -> assign('listdata',$brandslist);
		$this -> display();
	}
	/*
	 * 编辑ROM品牌链接
	 */
	public function edit_brands(){
		$tagid = intval($_REQUEST['tagid']);
		if ($tagid<1) {
			$this -> writelog('编辑ROM品牌链接参数有误tagid为'.$tagid);
			$this->error("参数有误！");
		}
		if (isset($_POST) && isset($_POST['val_url'])) {
			$times = time();
			foreach ($_POST['val_url'] as $k=>$v){
				$urlval = addslashes(trim($v));
				if ($urlval!='') {
					if (strpos($urlval,"http://")===false) {
						$urlval = "http://".$urlval;
					}
					$preg_url = "/^(https?|ftp|mms):\/\/([A-z0-9]+[_\-]?[A-z0-9]+\.)*[A-z0-9]+\-?[A-z0-9]+\.[A-z]{2,}(\/.*)*\/?$/";
					if(!preg_match("$preg_url",$urlval)){
						$this->error("链接地址有误！");
					}
				}
				
				
				$manageid = intval($k);
				$iscount = $this->model->table($this->zybrandstable)->where(array('tagid'=>$tagid,'status'=>0,'manageid'=>$manageid))->count();
				if ($iscount) {
					$result = $this->model -> query("UPDATE $this->zybrandstable SET url_val='$urlval',modifytime='$times' where  tagid='$tagid' and manageid='$manageid'");
					$target_id = $tagid;
				}else{
					$data = array('tagid'=>$tagid,'manageid'=>$manageid,'url_val'=>$urlval,'createdate'=>$times,'modifytime'=>$times);
					$result = $this->model->table($this->zybrandstable)->add($data);
					$target_id = $result;
				}
				
			}
			$this->writelog("编辑了room品牌链接",$this->zybrandstable,$target_id,__ACTION__ ,'','edit');
			$this->success("编辑成功！");
		}
		$brandsdata = $listdata = array();
		$mdata = $this->model->table($this->zymanagetable)->where(array('status'=>0))->order('rank ASC,id ASC')->field('id, mname, rank')->select();
		$data = $this->model->table($this->zybrandstable)->where(array('tagid'=>$tagid,'status'=>0))->field('id,tagid,manageid,url_val')->select();
		foreach ($data as $dval){
			$brandsdata[$dval['manageid']] = $dval['url_val'];
		}
		foreach ($mdata as $mval){
			$mid = $mval['id'];
			if (isset($brandsdata[$mid])) {
				$listdata[$mid] = $brandsdata[$mid];
			}else{
				$listdata[$mid] = '';
			}
		}
		$this -> assign('mdata',$mdata);
		$this -> assign('listdata',$listdata);
		$this -> assign('tagid',$tagid);
		$this -> display();
	}
	/*
	 * ROM管理列表选项列表
	 */
	public function brandsmanage(){
		$order = "ASC";
		$orderBy = 'id '.$order;
		if (isset($_REQUEST['field']) and isset($_REQUEST['order'])) {
			$field = addslashes(trim($_REQUEST['field']));
			$order = addslashes(trim($_REQUEST['order']));
			$orderBy = "$field $order";
		}
		$mdata = $this->model->table($this->zymanagetable)->where(array('status'=>0))->order($orderBy)->select();
		$this -> assign('order',$order=='ASC' ? DESC : ASC);
		$this -> assign('mdata',$mdata);
		$this -> display();
	}
	/*
	 * 添加ROM管理列表选项
	 */
	public function add_manage(){
		if (isset($_POST) && isset($_POST['names'])) {
			$name = addslashes(trim($_POST['names']));
			$rank = isset($_POST['rank']) ? intval($_POST['rank']) : 0;
			if ($name=='') {
				$this->error("参数不能为空！");
			}
			$data = array('mname'=>$name,'rank'=>$rank,'createdate'=>time(),'modifytime'=>time());
			$result = $this->model->table($this->zymanagetable)->add($data);
			$this -> writelog('添加ROM管理列表选项id为'.$result,$this->zymanagetable,$result,__ACTION__,'','add');
			$this->success("添加成功！");
		}
		$this -> display();
	}
	/*
	 * 编辑ROM管理列表选项名称
	 */
	public function edit_manage(){
		$id = intval($_REQUEST['id']);
		if ($id<1) {
			$this -> writelog('编辑ROM管理列表选项 参数有误id为'.$id);
			$this->error("参数有误！");
		}
		if (isset($_POST) && isset($_POST['names'])) {
			$name = addslashes(trim($_POST['names']));
			if ($name=='') {
				$this->error("参数不能为空！");
			}
			$times = time();
			$result = $this->model -> query("UPDATE $this->zymanagetable SET mname='$name',modifytime='$times' where  id='$id'");
			$this -> writelog('编辑ROM管理列表选项展示名称:'.$name.' | id为'.$id,$this->zymanagetable,$id,__ACTION__,'','edit');
			$this->success("编辑成功！");
		}
	
		$mdata = $this->model->table($this->zymanagetable)->where(array('id'=>$id))->field('id,mname')->find();
		
		$this -> assign('mname',$mdata['mname']);
		$this -> assign('id',$id);
		$this -> display();
	}
	/*
	 * 编辑ROM管理列表选项优先级
	 */
	public function edit_manage_rank(){
		if (isset($_POST['level'])){
			$idstr = '';
			foreach ($_POST['level'] as $k=>$v){
				$v = abs(intval($v));
				$p_ret = $this->model -> query("UPDATE $this->zymanagetable SET rank='$v' where  id='$k'");
				$idstr .= $k.',';
			}
			$jsonarr = 'ROM管理列表选项 优先级 id:rank'.json_encode($_REQUEST['level']);
			$this -> writelog($jsonarr,$this->zymanagetable,$idstr,__ACTION__,'','edit');
			$this->success("编辑优先级成功！");
		}
	}
	/*
	 * 删除ROM管理列表选项
	 */
	public function update_manage(){
		$id = intval($_REQUEST['id']);
		$status = intval($_REQUEST['status']);
		if ($id<1) {
			$this -> writelog('修改ROM管理列表选项 状态参数有误id为'.$id);
			$this->error("参数有误！");
		}
		$modifytime = time();
		$ret = $this->model->query("UPDATE $this->zymanagetable SET status='$status' ,modifytime=$modifytime  WHERE `id` =$id");
		if($ret!==false){
			$this -> writelog('删除ROM管理列表选项id为'.$id,$this->zymanagetable,$id,__ACTION__,'','del');
			$this->success("删除成功！");
		}else{
			$this -> writelog('修改ROM管理列表选项状态参数有误id为'.$id,$this->zymanagetable,$id,__ACTION__,'','edit');
			$this->error("参数有误！");
		}
	}

}