<?php

class MessageModel extends Model {
	//获取回复列表数据
	function get_reply_list($where) {
		$softid_sql="";
		$comment_sql="";
		if($_GET['package']){
			$subQuery = $this->table('sj_soft')->field('softid')->where("package='{$_GET['package']}'")->buildSql(); 
			//$where['softid'] = array('in',$subQuery); 
			$softid_sql=$subQuery;
		}
		if($_GET['feature_name'])
		{
			$where_feature = array(
				'status' => 1,
			);
			$where_feature['name'] = array('like', '%'.escape_string($_GET['feature_name']).'%');
			$sub_feature_query = $this->table('sj_feature')->field('distinct feature_id')->where($where_feature)->buildSql();
			$softid_sql=$sub_feature_query;
			//$where['softid'] = array('in',$sub_feature_query); 
		}
		if($subQuery&&$sub_feature_query)
		{
			$softid_sql="{$subQuery} union {$sub_feature_query} ";
		}
		if($_GET['package']||$_GET['feature_name'])
		{
			$where['softid'] = array('in',$softid_sql);
		}
		
		if($_GET['choose_type'])
		{
			$sub_type_Query = $this->table('sj_soft_comment')->field('id')->where("comment_type='{$_GET['choose_type']}'")->buildSql(); 
			$comment_sql=$sub_type_Query;
			//$where['comment_id'] = array('in',$sub_type_Query);
		}
		if($_GET['everybody_say'])
		{
			if($_GET['everybody_say']==1)
			{
				$sub_say_Query = $this->table('sj_soft_comment')->field('id')->where("everybody_said_id != 0 ")->buildSql(); 
			}
			elseif($_GET['everybody_say']==2)
			{
				$sub_say_Query = $this->table('sj_soft_comment')->field('id')->where("everybody_said_id = 0 ")->buildSql(); 	
			} 
			$comment_sql=$sub_say_Query;
			//$where['comment_id'] = array('in',$sub_say_Query);
		}
		if($sub_type_Query&&$sub_say_Query)
		{
			$comment_sql="{$sub_type_Query} union {$sub_say_Query} ";
		}
		if($_GET['everybody_say']||$_GET['choose_type'])
		{
			$where['comment_id'] = array('in',$comment_sql);
		}

		list($c_p_id_arr,$comment_extent_data)=$this->get_comment_extent_id($_GET,2);

		if(is_array($c_p_id_arr)){
			$where['pid'] = array('IN', $c_p_id_arr);
		}
		if(!$comment_extent_data){
			$comment_extent_data=array();
		}
		//分页		
		import('@.ORG.Page2');
		if($_GET['processed'] == 0){
			$where['conver_id'] = 0;
			//未处理
			$total = $this -> table('sj_post')->where($where)->count();		
			$param = http_build_query($_GET);
			$limit = isset($_GET['limit']) ? $_GET['limit'] : 10;		
			$Page = new Page($total,$limit,$param);
			$list = $this -> table('sj_post')->where($where)->limit($Page->firstRow.','.$Page->listRows)->order('dateline desc')->select();
		}elseif($_GET['processed'] == 1 || $_GET['processed'] == 2 || $_GET['processed'] == 3){
			if($_GET['processed'] == 1){
				$status = 2;//已处理
			}else if($_GET['processed'] == 2){
				$status = 0;//已删除
			}else if($_GET['processed'] == 3){
				$status = 1;//再次回复
				unset($where['processed']);
			}
			
			$where_new = array(
				'a.status' =>  $status,
				'b.conver_id' => array('neq',0)
			);
			foreach($where AS $k => $v){
				$where_new["b.".$k] = $v;
			}
			unset($where);
			$table = "sj_conversation as a left join sj_post b on a.id = b.conver_id ";
			$total = $this->table($table)->where($where_new)->field("count(DISTINCT conver_id) as total")->find();	
			$total = $total['total'];
			$param = http_build_query($_GET);
			$limit = isset($_GET['limit']) ? $_GET['limit'] : 10;		
			$Page = new Page($total,$limit,$param);			
			$subQuery = $this->table($table)->where($where_new)->field('b.*')->order('pid desc')->buildSql();

			// $list = $this->table("({$subQuery}) A")->limit($Page->firstRow.','.$Page->listRows)->order('dateline desc')->group('conver_id')->select(); 
			$sql_last = $this->table("({$subQuery}) A")->limit($Page->firstRow.','.$Page->listRows)->order('dateline desc')->group('conver_id')->buildSql(); 
			$p="/`'(\d+)'`/i";
			$sql_last=preg_replace($p,"\\1",$sql_last);
			$list =$this->query($sql_last);
			
		}elseif($_GET['processed'] == -5){
			// $where['conver_id'] = 0;
			//未处理
			unset($where['processed']);
			$total = $this -> table('sj_post')->where($where)->count();		
			$param = http_build_query($_GET);
			$limit = isset($_GET['limit']) ? $_GET['limit'] : 10;		
			$Page = new Page($total,$limit,$param);
			$list = $this -> table('sj_post')->where($where)->limit($Page->firstRow.','.$Page->listRows)->order('dateline desc')->select();
		}
		$softid = array();
		$commentid = array();
		$featureid = array();
		$did = array();
		$adminid = array();
		$before_id = array();
		$replace_id = array();
		foreach ($list as $k => $v){
			//if($v['softid']) $softid[] = $v['softid'];
			if($v['comment_id']) $commentid[] = $v['comment_id'];
			if($v['did']) $did[] = $v['did'];
			if($v['pid']) $replace_id[] = $v['pid'];
			if($v['system_userid']) $adminid[] = $v['system_userid'];
			//取得组的第一条pid
			if($_GET['processed'] == 1){
				$reply_list = $this->table("sj_post")->where("conver_id={$v['conver_id']}")->field('imei,user_name,device,reply_ip,contents')->order('pid asc')->find(); 
				$list[$k]['imei'] = $reply_list['imei'];
				$list[$k]['user_name'] = $reply_list['user_name'];
				$list[$k]['device'] = $reply_list['device'];
				$list[$k]['reply_ip'] = $reply_list['reply_ip'];
				$list[$k]['reply_contents'] = $reply_list['contents'];
			}
			if($v['before_id']) $before_id[] = $v['before_id'];
		}
		//获取评论表的信息 分别获取softid和featureid
		$commentid = array_unique($commentid);
		$where_comment = array('id'=>array('in',$commentid));
		$comment_info = get_table_data($where_comment,"sj_soft_comment","id","id,comment_type,everybody_said_id,softid");
		foreach($comment_info as $key=>$val)
		{
			if($val['comment_type']==1)
			{
				if($val['softid']) $softid[] = $val['softid'];
			}
			if($val['comment_type']==2)
			{
				if($val['softid']) $featureid[] = $val['softid'];
			}
		}
		
		//软件信息
		if($softid){
			$softid = array_unique($softid);
			$where = array('softid'=>array('in',$softid));
			$soft_info = get_table_data($where,"sj_soft","softid","softid,package,softname,hide");
			$file_info = get_table_data($where,"sj_soft_file","softid","softid, iconurl,apk_name");
			foreach($soft_info as $key => $val){
				$soft_info[$key]['iconurl'] = $file_info[$key]['iconurl'];
			}
		}
		//专题信息
		if($featureid)
		{
			$featureid = array_unique($featureid);
			$where_feature = array('feature_id'=>array('in',$featureid));
			$feature_info = get_table_data($where_feature,"sj_feature","feature_id","feature_id,name");
		}
		//机型
		if($did){
			$did = array_unique($did);
			$where = array('did'=>array('in',$did));
			$dinfo = get_table_data($where,"pu_device","did","did,dname");	
		}
		//机型
		if($replace_id){
			$replace_id = array_unique($replace_id);
			$where = array('c_p_id'=>array('in',$replace_id),'type'=>array('exp','&2=2'),'c_type'=>2);
			$replace_data = get_table_data($where,"sj_soft_comment_extent","c_p_id","c_p_id,id");	
		}
		//上一条记录
		if($before_id){
			$where = array(
				'pid'=>array('in',$before_id),
			);
			$admin_reply = get_table_data($where,"sj_post","pid","pid,system_userid,dateline,contents");
			$system_userid = array();
			foreach($admin_reply as $k => $v){
				$adminid[] = $v['system_userid']; 
			}
		}
		//操作人员
		if($adminid){
			$adminid = array_unique($adminid);
			$where = array('admin_user_id'=>array('in',$adminid));
			$admin_info = get_table_data($where,"sj_admin_users","admin_user_id","admin_user_name,admin_user_id");
		}			
		$return = array(
			'list' => $list,
			'total' => $total,
			'soft_info' => $soft_info,
			'feature_info' => $feature_info,
			'comment_info' => $comment_info,
			'dinfo' => $dinfo,
			'admin_info' => $admin_info,
			'admin_reply' => $admin_reply,
			'page' => $Page,
			'comment_extent_data'=>$comment_extent_data,
			'replace_data'=>$replace_data,

		);
		return $return;
	}
	//获取删除原因
	function get_reason($reason_type){
		$where = array(
			'status' => 1,
			'reason_type' => $reason_type,
		);
		$reasons = $this->table('dev_reason')->where($where)->order('pos')->select();
		foreach($reasons as &$val){
			if($val['content2']){
				$val['content2'] = explode('<br />', $val['content2']);
			}
		}
		return 	$reasons;
	}	
	//删除提交
	function del_reply_post(){
		if (!empty($_POST['id'])) {
			$where = array(	'pid' => $_POST['id']);
		} else if (!empty($_POST['ids'])) {
			$where = array(
				'pid' => array('IN', explode(',', $_POST['ids']))
			);
		}
		// $where = array(	'pid' => $_POST['id']);
		// $lists  = $this -> table('sj_post') -> where($where)->field('pid,conver_id,processed,before_id,nick_name,hide')->select();
		$lists  = $this -> table('sj_post') -> where($where)->select();
		// echo "<pre>";var_dump($lists);die;
		foreach($lists as $list){
			if($list['processed'] == 2){
				exit(json_encode(array('code'=>'0','msg'=>"该记录已经删除请不要重复删除")));
			}
			$data = array(
				'processed' => 2,
				'status' => 0,
				'update_time' => time(),
				'system_userid' => $_SESSION['admin']['admin_id'],
			);
			//没有回复的数据设置为删除状态
			if($list['reply_count'] == 0){
				$data['post_status'] = 0;
			}

			if (!empty($_POST['content'])){ 
				$reject = $_POST['content'];
				$data['denymsg'] = $reject;
			}
			if($list){
				//把一组数据删除
				$where = array(	'conver_id' =>$list['conver_id']);
				if($list['processed'] == 0){
					//如果是未处理列表删除加一条数据到sj_conversation表
					$where['pid'] = $list['pid'];
					$map = array('status'=>0,'update_tm'=>time(),'last_pid'=>$list['pid']);
					$conver_id = $this -> table('sj_conversation') ->add($map);		
					$data['conver_id'] = $conver_id;				
				}
			}
			$ret = $this->table('sj_post')->where($where)->save($data);		
			//echo $this->getlastsql();exit;
			if($ret){
				$where_c = array(
					'conver_id' => $conver_id ? $conver_id : $list['conver_id']
				);
				$reply_total = $this->table('sj_post')->where($where_c)->count();
				$reply_total = $reply_total ? $reply_total : 1;		
				
				$data = array(
					'all_post' => array('exp',"`all_post`-{$reply_total}")
				);
				if($list['hide'] == 0){
					$data['reply_count'] = array('exp',"`reply_count`-{$reply_total}");
				}
				$where['all_post'] = array('gt',0);
				$where['reply_count'] = array('gt',0);
				$where['nick_name'] = array('exp',"!=''");
				$this->table('sj_post')->where($where)->save($data);	
					
				$where = array(
					'id'=>$list['comment_id'],
					'all_post' => array('gt',0),
					'reply_count' => array('gt',0),
					'nick_name' => array('exp',"!=''")
				);
				$this->table('sj_soft_comment')->where($where)->save($data);	
				if($list['processed'] > 0){
					$where_c = array('id'=>$list['conver_id']); 
					$map = array('status'=>0,'update_tm'=>time());
					$this -> table('sj_conversation') -> where($where_c)->save($map);	
				}
				if($list['all_post'] == 0){
					//没有跟后台二次交互的时候才操作这条
					$where_c = array('child_id' => $list['pid']); 	
					$map = array('status' => 0 );
					$this -> table('sj_post_backtrace')-> where($where_c)->save($map);		
				}		
			}
		}
		
		return $ret;
	}
	//获取评论回复内容
	function get_reply(){
		$where = array('pid'=>$_GET['id']);
		$list = $this->table('sj_post')->where($where) -> field('comment_id,conver_id,dateline,processed')->find();
		$where = array(
			'conver_id'=>$list['conver_id'],
			'reply_type' => 1,
		);
		if($_GET['processed'] == 0){
			$where['pid'] = $_GET['id'];
			$where['status'] = 1;
		}else if($_GET['processed'] == 3 || $_GET['processed'] == 1){
			$where['status'] = 1;
		}else if($_GET['processed'] == 2){
			$where['status'] = 0;
		}		
		if($list['conver_id'] == 0 ){//未处理回复
			$where['comment_id'] = $list['comment_id'];
		}		
		$data = $this->table('sj_post')->where($where) ->order('dateline asc')->field('dateline,contents,user_type,system_userid,dev_id')->select();	
		$adminid = array();
		$dev_id =  array();
		foreach($data as $v){
			if($v['system_userid']) $adminid[] = $v['system_userid'];
			if($v['dev_id']) $dev_id[] = $v['dev_id'];
		}
		//操作人
		if($adminid){
			$adminid = array_unique($adminid);
			$where = array(	'admin_user_id' => array('in',$adminid));
			$admin_info = get_table_data($where,"sj_admin_users","admin_user_id","admin_user_name,admin_user_id");		
		}
		//开发者客服
		if($dev_id){
			$dev_id = array_unique($dev_id);		
			$where = array('userid' => array('in',$dev_id));
			$dev_arr = get_table_data($where,"pu_user","userid","user_name,userid");		
		}
		return array($data,$admin_info,$dev_arr);
	}
	function reply_post(){
		$time = time();
		$where = array('pid'=>$_POST['id']);
		$list  = $this -> table('sj_post') -> where($where)->find();
		if($list['conver_id']){
			//如果没有昵称就用有昵称的最后一条
			 $where2 = array(
				'conver_id'=>$list['conver_id'],
				'nick_name' => array('exp',"!=''")
			 );
			 $list2 =  $this -> table('sj_post') -> where($where2)->field('nick_name,contents')->order('pid desc')->find();
		}	
		$postdata = array(
			'comment_id' => $list['comment_id'] ,
			'tid' => $list['tid'] ,
			'contents' => $_POST['content'] ,
			'dateline' => $time , 
			'update_time' => $time , 
			'status' => '1' , 
			'user_type' => '1' , 
			'reply_type' => '1' , 
			'system_userid' => $_SESSION['admin']['admin_id'],
			'user_name' => $_SESSION['admin']['admin_user_name'],
			'before_id' => $list['pid'] ? $list['pid'] : 0,
			'processed' => '1',
			'reply_ip' => $list['reply_ip'] ? $list['reply_ip'] : 0,
			'reply_userid' => $list['userid'] ? $list['userid'] : 0,
			'reply_imei' => $list['imei'] ? $list['imei'] : '',
			'reply_user_name' => $list2['nick_name'] ? $list2['nick_name'] : $list['nick_name'],
			'reply_device' => $list['device'] ? $list['device'] : '',
			'reply_vname' => $list['vname'] ? $list['vname'] : '',
			'reply_avatar' => $list['nick_name'] ? IMGATT_HOST . $list['avatar'] : $list['avatar'],
			'reply_content' => $list2['contents'] ? $list2['contents'] : $list['contents'],
			'package' => $list['package'] ? $list['package'] : '',
			'softid' => $list['softid'] ? $list['softid'] : 0 ,
			'conver_id' => $list['conver_id'] ? $list['conver_id'] : 0,
			'hide' => $list['hide'],
		);
 		$ret = $this -> table('sj_post') ->add($postdata);	
		$map = array(
			'processed' => 1,
			'update_time'=>$time,
			'all_post' => array('exp',"`all_post`+1"),
		);
		$data = array('status' => 2,'update_tm'=>$time,'last_pid'=>$ret);
		if($list['conver_id'] > 0){
			$where_c = array('id'=>$list['conver_id']); 
			$this -> table('sj_conversation') -> where($where_c)->save($data);
		}else{
			$conver_id = $this -> table('sj_conversation')->add($data);	
			$this -> table('sj_post') -> where(array('pid'=>$ret))->save(array('conver_id'=>$conver_id));
			$map['conver_id'] = $conver_id ;		
		}			
 		$this -> table('sj_post') -> where($where)->save($map);
		$where = array(
			'id'=>$list['comment_id'],
		);
		$map = array();
		$map['all_post'] = array('exp',"`all_post`+1");
		if($list['hide'] == 0){
			set_redis_comment($list['userid'],$list['imei'], $ret, $list['comment_id'], $list['mac']);
			$map['reply_count'] = array('exp',"`reply_count`+1");
		}
		$this -> table('sj_soft_comment') -> where($where)->save($map);
		$map = array(
			'parent_id' => $_POST['id'],
			'child_id' => $ret
		);
		$this -> table('sj_post_backtrace') ->add($map);	
		return $ret;		
	}
	function get_comment_reply(){
		$comment_model = D('Dev.Comment');
		$id = $_GET['id'];
        $res = $comment_model->table('sj_soft_comment')->where(array('status'=>1,'id'=>$id))->find();
        if($res){
            $data = $this->table('sj_post')->where(array('comment_id'=>$id,'status'=>1))->order('dateline asc')->select();
        }
		$adminid = array();
		$dev_id =  array();
		foreach($data as $v){
			if($v['system_userid']) $adminid[] = $v['system_userid'];
			if($v['dev_id']) $dev_id[] = $v['dev_id'];
		}
		//操作人
		if($adminid){
			$adminid = array_unique($adminid);
			$where = array(	'admin_user_id' => array('in',$adminid));
			$admin_info = get_table_data($where,"sj_admin_users","admin_user_id","admin_user_name,admin_user_id");		
		}
		//开发者客服
		if($dev_id){
			$dev_id = array_unique($dev_id);		
			$where = array('userid' => array('in',$dev_id));
			$dev_arr = get_table_data($where,"pu_user","userid","user_name,userid");		
		}
		return array($res,$data,$admin_info,$dev_arr);		
	}
	//上传评论头像
	function upload_img(){
		//图片路径
		$verifyToken = md5('unique_salt' . $_POST['timestamp']);
		if (!empty($_FILES) && $_POST['token'] == $verifyToken) {
			if($_FILES['Filedata']['size'] > 1024*1024){
				return(array('code'=>0,'msg'=>'图片大于1M'));
			}
			$image = getimagesize($_FILES['Filedata']['tmp_name']);
			if($image[0] != 120 || $image[1] != 120){
				return(array('code'=>0,'msg'=>'上传图片必须为120*120'));
			}	
			$tempFile = $_FILES['Filedata']['tmp_name'];
			$day = date('Ym/d');
			$dir = UPLOAD_PATH.'/img/'.$day.'/';
			if (!is_dir($dir)) {
				mkdir($dir, 0777, true);
			}
			$fileTypes = array('jpg','jpeg','gif','png'); // File extensions
			$fileParts = pathinfo($_FILES['Filedata']['name']);
			//命令微秒
			list($msec,$sec) = explode(' ',microtime());
			$msec = substr($msec,2);
			$targetFile = $dir . $msec.'.'.$fileParts['extension'];
			if (in_array(strtolower($fileParts['extension']),$fileTypes)) {
				move_uploaded_file($tempFile,$targetFile);	
				$path = '/img/'.$day.'/'.$msec. '.'.$fileParts['extension'];
				$time = time();
				$map = array(
					'pictures_url' => $path,
					'add_tm' => $time,
					'update_tm' => $time,
				);
				$res = $this -> table('sj_comment_pictures') -> add($map);
				return(array('code'=>1,'msg'=>$path,'id'=>$res));
			}else{
				return(array('code'=>0,'msg'=>'上传格式错误'));
			}
		}	
	}	
	//获取评论头像
	function get_comment_pictures(){
		$where = array('status' => 1);
		//分页		
		import('@.ORG.Page2');	
		$param = http_build_query($_GET);
		$limit = isset($_GET['limit']) ? $_GET['limit'] : 80;		
		$total = $this -> table('sj_comment_pictures ') -> where($where)->count();
		$Page = new Page($total,$limit,$param);		
		$ret = $this -> table('sj_comment_pictures ') -> where($where)->limit($Page->firstRow.','.$Page->listRows)->select();
		return array($ret,$Page);
	}
	function get_show_data($val){
		require_once(SERVER_ROOT.'/tools/SearchKeyClient_new.php');
		$client = new SearchKeyClient_new();
		if($_SERVER['SERVER_ADDR']=='192.168.0.99' || $_SERVER['SERVER_ADDR']=='10.0.3.15') {	//99测试
			$host = "103.17.40.76";
		}else{
			$host = "192.168.1.109";
		} 	
		$port = 1119;
		$client->SetServer($host,$port);
		$client->SetConnectTimeout(4);
		$data = $client->query('',json_encode($val));
		//var_dump($data);
		return $data;
	}
	function get_comment_extent_id($val,$c_type){
		// $model = M('soft_comment_extent');
		$where=array();
		if($word_num=trim($val['word_num'])){
			$where['word_num']=array('elt',$word_num);
		}
		if($repeat_num=trim($val['repeat_num'])){
			$where['repeat_num']=array('gt',3);
		}
		if($is_replace=trim($val['is_replace'])){
			$where['type']=array('exp','&2=2');
		}
		$where['c_type']=$c_type;
		if(!$where['repeat_num'] && !$where['word_num']&&!$where['type']){
			return array(0,0);
		}
		$extent_list = $this->table('sj_soft_comment_extent')->field('c_p_id,content,admin_id,update_tm')->where($where)->select();
		// echo $this->getlastsql();
		$c_p_id_arr=array();
		$comment_extent_arr=array();
		$admin_ids=array();
		foreach($extent_list as $k=>$v){
			$admin_ids[]=$v['admin_id'];
		}
		if($admin_ids){
			$admin_data=$this ->table('sj_admin_users')->field('admin_user_id,admin_user_name')->where(array('admin_user_id'=>array('in',$admin_ids)))->select();
			$admin_data_new=array();
			foreach($admin_data as $k=>$v){
				$admin_data_new[$v['admin_user_id']]=$v['admin_user_name'];
			}
		}
		
		foreach($extent_list as $k=>$v){
			$c_p_id_arr[]=$v['c_p_id'];
			$v['update_tm']=date('Y-m-d H:i:s',$v['update_tm']);
			$v['admin_id_name']=$admin_data_new[$v['admin_id']];
			$comment_extent_arr[$v['c_p_id']]=$v;
		}
		return array($c_p_id_arr,$comment_extent_arr);
	}
}
?>