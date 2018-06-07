<?php 
class bbsModel extends AdvModel{
	var $connect_id = 21;
	public function __construct(){
		parent::__construct();
		$co_Connect = C('DB_BBS_DB');
		$this -> addConnect($co_Connect, $this->connect_id);
		$this -> switchConnect($this->connect_id);
	}
	
	public function getForumList($type,$pid=0){
		$where = '1';
		if($type == 'group'){
			$where = " type = 'group'";
		}else if($pid > 0){
			$where = " fup = {$pid} and  type = '{$type}'";
		}else{
			return false;
		}
		$list = $this -> table('x15_forum_forum') -> where($where) -> select();
		return $list;
	}
	
		
	public function getthread($tid){
		$res = $this ->query('select ft.fid,ft.tid,ft.posttableid,ft.author,ft.authorid ,ff.name,ft.subject,ft.views,ft.replies,ft.dateline from x15_forum_thread as ft left join x15_forum_forum as ff on ft.fid = ff.fid where ft.tid='.$tid.' limit 1');
		$thread = $res[0];
		if(empty($thread)) return false;
		$post_table = $res[0]['posttableid']?'x15_forum_post_'.$res[0]['posttableid']:'x15_forum_post';
		$res = $this -> table($post_table)->field('message,pid')->where('first =1 and tid='.$tid)->select();
		preg_match('/\[attach\](\d+)\[\/attach\]/i',$res[0]['message'],$match);
		$attach_table = 'x15_forum_attachment_'.substr($tid,-1);
		if($match[1]) $imgpath = $this->table($attach_table) -> field('attachment,isimage') -> where('aid='.$match[1])->select();
		if(!$imgpath[0]||$imgpath[0]['isimage']==0) $imgpath[0]['attachment'] = ''; 
		if(!$res[0]) $res[0]['message'] = '';
		$thread = $thread+$res[0]+$imgpath[0] ;
		return $thread;
	}
	
	public function getForumByFid($fids){
		$fidstring = implode(',',$fids);
		$list = $this -> table('x15_forum_forum') -> where("fid in ({$fidstring})") -> select();
		$forumlist = array();
		foreach($list as $info){
			$forumlist[$info['fid']] = $info;
		}
		return $forumlist;
	}
	
	public function getForumThreadtypeByFid($fid){
		$res = $this -> table('x15_forum_forumfield') -> where("fid = {$fid}")->field('threadsorts') -> select();
        $sort = $this->mb_unserialize($res[0]['threadsorts']);
        $typeid = array();
        foreach($sort['types'] as $k => $v){
            $typeid[] = $k;
        }
        $typeid = implode(',',$typeid);
        $result = $this -> table('x15_forum_threadtype') -> where("typeid in ({$typeid})")->order('displayorder asc') -> select();
        
		return $result;
	}
	
	
	public function getForumtTypeByFid($fid){
		$res = $this -> table('x15_forum_forumfield') -> where("fid = {$fid}")->field('threadtypes') -> find();
        $types = $this->mb_unserialize($res['threadtypes']);
		return $types['types'];
	}
    
    public function mb_unserialize($res) {
        return mb_unserialize($res);
    }

}