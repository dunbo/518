<?php 
class ZhiyooSlaveModel extends AdvModel{
    #ÖÇÓÑºóÌ¨´Ó¿â
	var $connect_id = 40;
    protected $tablePrefix = 'zy_';
	public function __construct(){
		parent::__construct();
		$co_Connect = C('DB_ZHIYOO_SLAVE');
		$this -> addConnect($co_Connect, $this->connect_id);
		$this -> switchConnect($this->connect_id);
	}
    
    public function getbbsblack(){
		$list = $this -> table('zy_forum_black') -> where('status = 1') -> select();
		foreach($list as $info){
			$blacklist[$info['fid']] = $info;
		}
		return $blacklist;
	}
	
	public function getrules(){
		$rules = $this -> table('zy_bbs_rule') -> select();
		foreach($rules as $info){
			$rulelist[$info['rid']] = $info;
		}
		return $rulelist;
	}
	
	public function getforum(){
		$forum = $this -> table('zy_forum_forum')->order('fname asc') -> select();
		foreach($forum as $info){
			$fname = trim($info['fname']);
			if(!empty($fname))
			$forumlist[$info['fid']] = $info;
		}
		return $forumlist;
	}
	
	public function gettags(){
		
		$tagres = $this->query('select t.tagid,t.tagname,t.group,t.parentid,tg.groupname,t.status from zy_tags as t left join zy_tagsgroup as tg on t.group=tg.groupid');
		foreach($tagres as $info){
			$tags[$info['tagid']] = $info;
		} 
		return $tags;
	}
	
	public function gettaggroup(){
		$result = $this -> table('zy_tagsgroup') -> where('status = 1') ->select();
		foreach($result as $group){
			$grouplist[$group['groupid']] = $group;
			
		}
		return $grouplist;
	}

	public function anzhiauthkey($tid){
		$key = 'WNDSI6#kjsd';
		return md5($tid . $key . date('Ymd'));
	}

	public function extpos(){
		$result = $this -> table('zy_schedule_extpos') -> where('status = 1') ->select();
        $re = array();
        foreach($result as $val){
            $re[$val['extpos']] = $val['extposname'];
        }
		return $re;
	}



}