<?php 

Class RomNewAction extends CommonAction{
	private $model;
	private $zytable = "zy_tags";
	private $zymanagetable = "zy_rom_manage";
	private $zybrandstable = "zy_rom_brands";
	private $groupid = 1;	//设置手机品牌id
	
	public function _initialize() {
        parent::_initialize();
		$this->model = D('Zhiyoo.RomNew');
	}
	
	function index(){
        $res = $this->model->select();
		foreach($res as $key => $val){
			$tags = '';
			if($val['tagid']){
				$tang1 = $this->model->table($this->zytable)->where(array('tagid'=>$val['tagid']))->find();
				if($tang1['parentid']){
					$tang2 = $this->model->table($this->zytable)->where(array('tagid'=>$tang1['parentid']))->find();
					$tags = $tang2['tagname'].' > ';
					$res[$key]['pid'] = $tang1['parentid'];
				}
				$tags .= $tang1['tagname'];
				$res[$key]['tags'] = $tags;
			}
			$res[$key]['tid'] = $val['tagid'];
		}
		
		$this -> assign('res',$res);
		$this -> display();
	}
	
	function changestatus(){
		$id = intval($_GET['id']);
		$status = intval($_GET['status']) ? 0 : 1;
		$result = $this->model->status($id,$status);
		$this -> writelog("智友内容管理-众测后台-最新ROM运营位配置 已修改id为{$id}的状态为{$status}","zy_rom_new",$id,__ACTION__ ,"","edit");
		
		if($result !== false)$this->success("修改成功！");
		else $this->error("修改失败！");
	}
	
	function doedit(){
		$id = intval($_GET['id']);
		$tags = intval($_GET['tags']);
		$result = $this->model->where(array('id'=>array('neq',$id),'tagid'=>$tags))->find();
		if($result) $this->error("修改失败！已存在相同标签");
		$result = $this->model->where(array('id'=>$id))->save(array('tagid'=>$tags));
		$this -> writelog("智友内容管理-众测后台-最新ROM运营位配置 已修改id为{$id}的标签为{$tags}","zy_rom_new",$id,__ACTION__ ,"","edit");
		
		if($result !== false)$this->success("修改成功！");
		else $this->error("修改失败！");
	}
	
	function tag_list_show_n(){
		$bbs_modle = D('Zhiyoo.Zhiyoo');
		list($pid,$tid) = explode('_',$_GET['tagid']);
		//获取所有的一级分类
		$result = $bbs_modle ->table('zy_tagsgroup')->field(array('groupid','groupname'))->where(array('groupid'=>$this->groupid))->find();
		$cat[$this->groupid] = $result['groupname'];
		$result = $bbs_modle ->table('zy_tags')->field(array('tagid','tagname','group','parentid'))->where(array('group'=>$this->groupid,'status'=>1))->select();
		foreach($result as $val){
			$taginfo[$val['group']][] = $val;
			$tagpinfo[$val['parentid']][] = $val;
			$taglist[$val['tagid']] = $val;
		}
		
		$this->assign('pid',$pid);
		$this->assign('tid',$tid);
		$this->assign('taginfo',$taginfo);
		$this->assign('tagpinfo',$tagpinfo);
		$this->assign('tagpinfo_json',json_encode($tagpinfo));
		$this->assign('taglist',$taglist);
		$this->assign('taglist_json',json_encode($taglist));
		$this->assign('grouplist_json',json_encode($cat));
		$this->assign('cat',$cat);
		$this->display();
	}
}