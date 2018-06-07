<?php 
class ForumBlackAction extends CommonAction{
	public function forumblacklist(){
		
		$bbsdb = D('Zhiyoo.bbs');	
		//group forum sub
		$grouplist = $bbsdb -> getForumList('group'); //论坛分区数据
		$forumlist = array();
		$subforumlist = array();
		foreach($grouplist as $index =>  $ginfo){
			$forums = $bbsdb -> getForumList('forum',$ginfo['fid']); //论坛板块数据
			if(!$forums){
				unset($grouplist[$index]);
				continue;
			}
			$forumlist[$ginfo['fid']] = $forums;
		}
		$zhiyoodb = D('Zhiyoo.Zhiyoo');
		$blacklist = $zhiyoodb  -> getbbsblack();
		$this -> assign('grouplist',$grouplist);
		$this -> assign('blacklist',$blacklist);
		$this -> assign('forumlist',$forumlist);
		$this -> display('ForumBlack');
	}
	
	function addBlacklist(){
		$fids = $_POST['fids'];
		if(empty($fids)){
			$this -> error('请选择版块');
		}
		$zhiyoodb = D('Zhiyoo.Zhiyoo');		
		$zhiyoodb ->query("TRUNCATE TABLE zy_forum_black");
		//$blacklist = $zhiyoodb  -> getbbsblack();
		//$blackfids = array_keys($blacklist);
		//if($blacklist && $blackfids) $fids = array_diff($fids,$blackfids);
		$bbsdb = D('Zhiyoo.bbs');
		$finfos = $bbsdb -> getforumbyfid($fids);
		$ids = '';
		foreach($finfos as $info){
			$data = array(
				'fid' => $info['fid'],
				'fname' => $info['name'],
				'status' => 1,
			);
			$r = $zhiyoodb -> table('zy_forum_black') -> add($data);
			$ids .= $r.',';
		}
		$this -> assign('jumpUrl', '/index.php/Zhiyoo/Forumblack/blacklist');
		$this -> writelog("黑名单添加 fid为".implode(',',$fids).",id为{$ids}","zy_forum_black",$ids,__ACTION__ ,"","add");
		$this -> success('黑名单添加成功');
	}

	public function blacklist(){
		$zhiyoodb = D('Zhiyoo.Zhiyoo');		
		$blacklist = $zhiyoodb  -> getbbsblack();
		$this -> assign('blacklist',$blacklist);	
		$this -> display('blacklist');
	}
	
	public function deleteblack(){
		$id = $_GET['id'];	
		$zhiyoodb = D('Zhiyoo.Zhiyoo');		
		if($_POST['blackids']){
			$id = implode(',',$_POST['blackids']);
		}
		if(!$id){
			$this -> assign('jumpUrl', '/index.php/Zhiyoo/Forumblack/blacklist');
			$this -> error('请选择你要删除的内容');		
		}
		$data['status'] = 0;
		$blacklist = $zhiyoodb  -> table('zy_forum_black') -> where('id in ('.$id.')' ) -> save($data);
		$this -> assign('jumpUrl', '/index.php/Zhiyoo/Forumblack/blacklist');
		$this -> writelog("删除了id为{$id}的黑名单",'zy_forum_black',$id,__ACTION__ ,"","del");
		$this -> success('黑名单删除成功');
	}

}