<?php 

Class ThreadRecommendAction extends CommonAction{
	private $model;
	private $model_bbs;
	private $model_tid;
	private $model_rule;
	
	public function _initialize() {
        parent::_initialize();
        $this->model = D('Zhiyoo.ThreadRecommend');
        $this->model_bbs = D('Zhiyoo.bbs');
        $this->model_tid = D('Zhiyoo.ThreadRecommendTids');
        $this->model_rule = D('Zhiyoo.ThreadRecommendRule');
    }
	
	public function index(){
		$order = $_GET['order'] ? 'rank desc ' : 'rank asc ';
		$rule = $this->model_rule-> getrule();
		$result = $this->model->order($order)-> select();
		foreach($result as $key => $val){
			$result[$key]['fnames'] = '';
			$forums = $val['fids'];
			$typeids = $val['typeids'];
			if($forums){
				$res = explode('|',$forums);
				$res = $this-> model_bbs ->getForumByFid($res);
				foreach($res as $fname){
					$result[$key]['fnames'] .= $fname['name'].'<br/>';
					$fidname[$fname['fid']] = $fname['name'];
				}
			}
			if($typeids){
				$typeids = explode('|',$typeids);
				$res = $this-> model_bbs ->table('x15_forum_threadclass')->where(array('typeid'=>array('in',$typeids)))->select();
				foreach($res as $tnames){
					$typeid[$tnames['fid']] .= $tnames['name'].' ';
				}
				foreach($typeid as $fid => $tnames){
					$result[$key]['tnames'] .= $fidname[$fid].':<br/>'.$typeid[$fid].'<br/>';
				}
				
			}
		}
		$this -> assign('rule',$rule);
		$this -> assign('result',$result);
		$this -> assign('order',$_GET['order'] ? 0 : 1);
		$this -> display();
	}
	
	public function forum_list(){
		$rid = intval($_GET['rid']);
		$res = $this-> model -> selectbyrid($rid); 
		$forums = $res['fids'];
		if($forums){
			$res = explode('|',$forums);
			$chkfid = array();
			foreach($res as $val){
				$chkfid[$val] = 1;
			}
		}else{
			$chkfid = 0;
		}
		$grouplist = $this-> model_bbs -> getForumList('group'); //论坛分区数据
		$forumlist = array();
		$subforumlist = array();
		foreach($grouplist as $index =>  $ginfo){
			$forums = $this ->model_bbs -> getForumList('forum',$ginfo['fid']); //论坛板块数据
			if(!$forums){
				unset($grouplist[$index]);
				continue;
			}
			$forumlist[$ginfo['fid']] = $forums;
		}
		$this -> assign('rid',$rid);
		$this -> assign('chkfid',$chkfid);
		$this -> assign('grouplist',$grouplist);
		$this -> assign('forumlist',$forumlist);
		$this -> display();
	}
	
	public function forum_submit(){
		$rid = intval($_GET['rid']);
		$fids = implode('|',$_POST['fids']);
		$result = $this-> model -> where(array('rid'=>$rid))->save(array('fids'=>$fids)); 
		
		if($result !== false){
			$this -> writelog("智友内容管理-推荐帖子内容配置 编辑rid为{$rid}的模块","zy_thread_recommend",$rid,__ACTION__ ,"","edit");
			$this -> success("编辑成功");
		}else{
			$this -> error("编辑失败");
		}
	}
	
	public function type_list(){
		$rid = intval($_GET['rid']);
		$res = $this-> model -> selectbyrid($rid); 
		$forums = $res['fids'];
		if($forums){
			$forums = explode('|',$forums);
			$grouplist = $this-> model_bbs ->getForumByFid($forums);
			$typeids = explode('|',$res['typeids']);
			$chkfid = $typelist = array();
			foreach($grouplist as $key => $val){
				$result = $this ->model_bbs -> table('x15_forum_forumfield')->where(array('fid'=>$val['fid']))->find();
				$threadtypes = $result['threadtypes'];
				$threadtypes = mb_convert_encoding($threadtypes,'gbk','utf-8');
				$threadtypes = unserialize($threadtypes);
				$result = $this ->model_bbs -> table('x15_forum_threadclass')->where(array('fid'=>$val['fid']))->select();
				foreach($result as $v){
					if(isset($threadtypes['types'][$v['typeid']])) {
						$typelist[$val['fid']][$v['typeid']] = $v['name'];
					}
				}
			}
			foreach($typeids as $val){
				$chkfid[$val] = 1;
			}
		}else{
			exit('请先选择板块');
		}
		$this -> assign('forums',$forums);
		$this -> assign('rid',$rid);
		$this -> assign('chkfid',$chkfid);
		$this -> assign('grouplist',$grouplist);
		$this -> assign('typelist',$typelist);
		$this -> display();
	}
	
	public function type_submit(){
		$rid = intval($_GET['rid']);
		$typeids = implode('|',$_POST['typeids']);
		$result = $this-> model -> where(array('rid'=>$rid))->save(array('typeids'=>$typeids)); 
		
		if($result !== false){
			$this -> writelog("智友内容管理-推荐帖子内容配置 编辑rid为{$rid}的模块","zy_thread_recommend",$rid,__ACTION__ ,"","edit");
			$this -> success("编辑成功");
		}else{
			$this -> error("编辑失败");
		}
	}
	
	public function edit(){
		$rid = intval($_GET['rid']);
		$result = $this-> model -> selectbyrid($rid); 
		$rule = $this->model_rule-> select();
		
		$this -> assign('result',$result);
		$this -> assign('rule',$rule);
		$this -> assign('rid',$rid);
		$this -> display();
	}
	
	public function doedit(){
		$rid = intval($_GET['rid']);
		$data = array(
			'subject'=>$_POST['subject'],
			'rule'=>intval($_POST['rule']),
			'count_num'=>intval($_POST['count_num']),
			'display_num'=>intval($_POST['display_num'])
		);
		$result = $this-> model ->where( array('rid'=>$rid))->save($data);
		
		if($result !== false){
			$this -> writelog("智友内容管理-推荐帖子内容配置 编辑rid为{$rid}的模块".json_encode($data),"zy_thread_recommend",$rid,__ACTION__ ,"","edit");
			$this -> success("编辑成功");
		}else{
			$this -> error("编辑失败");
		}
	}
	
	public function chstatus(){
		$rid = intval($_GET['rid']);
		$status = intval($_GET['status']);
		$result = $this-> model ->status($rid,$status);
		
		if($result !== false){
			$this -> writelog("智友内容管理-推荐帖子内容配置 改变rid为{$rid}的模块状态".$status,"zy_thread_recommend",$rid,__ACTION__ ,"","edit");
			$this -> success("编辑成功");
		}else{
			$this -> error("编辑失败");
		}
	}
	
	public function level(){
		foreach($_POST['level'] as $rid => $rank){
			$rid = intval($rid);
			$rank = intval($rank);
			$result = $this-> model ->where(array('rid'=>$rid))->save(array('rank'=>$rank));
			if($result)$this -> writelog("智友内容管理-推荐帖子内容配置 改变rid为{$rid}的模块排序值".$rank,"zy_thread_recommend",$rid,__ACTION__ ,"","edit");
		}
		
		if($result !== false){
			$this -> success("编辑成功");
		}else{
			$this -> error("编辑失败");
		}
	}
	
	public function thread_list(){
		$order = $_GET['order'] ? 'rank desc ' : 'rank asc ';
		$rid = intval($_GET['rid']);
		$result = $this->model_tid-> where(array('rid'=>$rid,'status'=>array('egt',0)))->order($order)->select();
		
		$this -> assign('result',$result);
		$this -> assign('order',$_GET['order'] ? 0 : 1);
		$this -> assign('rid',$rid);
		$this -> display();
	}
	
	public function deltid(){
		$id = intval($_GET['id']);
		$result = $this-> model_tid ->status($id,-1);
		
		if($result !== false){
			$this -> writelog("智友内容管理-推荐帖子内容配置 删除id为{$id}的内容","zy_thread_recommend_tids",$id,__ACTION__ ,"","del");
			$this -> success("删除成功");
		}else{
			$this -> error("删除失败");
		}
	}
	
	public function statustid(){
		$id = intval($_GET['id']);
		$status = intval($_GET['status']);
		$result = $this-> model_tid ->status($id,$status);
		
		if($result !== false){
			$this -> writelog("智友内容管理-推荐帖子内容配置 改变id为{$id}的内容状态".$status,"zy_thread_recommend",$id,__ACTION__ ,"","del");
			$this -> success("编辑成功");
		}else{
			$this -> error("编辑失败");
		}
	}
	
	public function add_thread(){
		$rid = intval($_GET['rid']);
		
		$this -> assign('rid',$rid);
		$this -> display();
	}
	
	public function add_thread_do(){
		$data['rid'] = intval($_GET['rid']);
		$data['rank'] = intval($_POST['rank']);
		$data['tid'] = intval($_POST['tid']);
		$res = $this-> model_bbs ->table('x15_forum_thread')->where(array('tid'=>$data['tid']))->find();
		if(!$res) $this -> error("帖子不存在");
		$data['subject'] = $res['subject'];
		$result = $this-> model_tid ->where(array('rid'=>$data['rid'],'tid'=>$data['tid'],'status'=>array('egt',0)))->find();
		if($result) $this -> error("帖子已存在");
		$data['status'] = 0;
		$result = $this-> model_tid ->add($data);
		
		if($result !== false){
			$this -> writelog("智友内容管理-推荐帖子内容配置 插入id为{$result}的内容","zy_thread_recommend_tids",$result,__ACTION__ ,"","add");
			$this -> success("插入成功");
		}else{
			$this -> error("插入失败");
		}
	}
	
	public function content_rank(){
		foreach($_POST['level'] as $id => $rank){
			$id = intval($id);
			$rank = intval($rank);
			$result = $this-> model_tid ->where(array('id'=>$id))->save(array('rank'=>$rank));
			if($result)$this -> writelog("智友内容管理-推荐帖子内容配置 改变id为{$id}的内容排序值".$rank,"zy_thread_recommend_tids",$id,__ACTION__ ,"","edit");
		}
		
		if($result !== false){
			$this -> success("编辑成功");
		}else{
			$this -> error("编辑失败");
		}
	}
}