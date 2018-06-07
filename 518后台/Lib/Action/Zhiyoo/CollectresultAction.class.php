<?php 

Class CollectresultAction extends CommonAction{
	function result_list(){
		// define('BBSLUNTAN_HOST','http://forum.anzhi.com/forum.php?mod=post&action=edit');
		!defined('BBSLUNTAN_HOST') && define('BBSLUNTAN_HOST','http://bbs.zhiyoo.com/forum.php?mod=post&action=edit');
		$source = $_GET['source'] ? $_GET['source'] : 0 ;
		$order_sql = !$_GET['type']?  'addtime desc': "{$_GET['type']} {$_GET['order']},addtime desc";
		$th[$_GET['type']] = $_GET['order'] == 'asc' ? 'desc' : 'asc';
		$where_sql = array();
		$cat = $filterurl = '';
		$where_sql[] = ' substatus = 0';
		$bbs_modle = D('Zhiyoo.Zhiyoo');
		$slavemodel = D('Zhiyoo.ZhiyooSlave');
		if(isset($_GET['tagstatus'])) {
			$where_sql[] = ' tagstatus = '.$_GET['tagstatus'];
			$where_sql[] = 'status = 1';
			$cat .= '/tagstatus/'.$_GET['tagstatus'];
		}
		if(isset($_GET['status'])) {
			$where_sql[] = 'status = '.$_GET['status'];
			$cat .= '/status/'.$_GET['status'];
		}else{
			$where_sql[] = 'status = 1';
		}
		
			$rid = $_GET['rule']== '全部'?'':$_GET['rule'];
			$start = strtotime($_GET['start_tm']);
			$web = $_GET['website_name'];
			$end = strtotime($_GET['end_tm']);
			$title = $_GET['title'];
			$tid = $_GET['tid'];
			$fid = $_GET['fid'];
			$tagid = $_GET['tags'];
			$uniquetags = $_GET['uniquetags'];
			if($rid) {
				$where_sql[] = 'rules = '.$rid;
				$filterurl .= '/rule/'.$rid;
				}
			if($web) {
				$where_sql[] = "website_name = '{$web}'";
				$filterurl .= '/website_name/'.$web;
				}					
			if($start) {
				$where_sql[] = 'addtime >= '.$start;	
				$filterurl .= '/start_tm/'.$_GET['start_tm'];
				
			}
			if($end) {
				$where_sql[] = 'addtime <= '.$end;	
				$filterurl .= '/end_tm/'.$_GET['end_tm'];
			}	
			if($title) {
				$where_sql[] = "title like '%{$title}%'";	
				$filterurl .= '/title/'.$title;
			}
			if($tid) {
				$where_sql[] = 'tid = '.$tid;	
				$filterurl .= '/tid/'.$tid;
			}
			if($fid) {
				$where_sql[] = 'fid = '.$fid;	
				$filterurl .= '/fid/'.$fid;
			}
			$taglist = $slavemodel->gettags();
			if($tagid) {
				$tagrules = explode('_',$tagid);
				array_pop($tagrules);
				$extsql = '('.implode(',',$tagrules).')';
				$searchcnt = count($tagrules);
				//搜索符合条件的标签
				$tagres = $slavemodel->query("select * from (select id,count(*) as cnt from zy_idtotagid where tagid in {$extsql} group by id ) as A  where A.cnt ={$searchcnt}"	);					
				$unitags = explode('_',$uniquetags);		
				array_pop($unitags);
				foreach($tagres as $val){
					$tagarr[] = $val['id'];
				}
				$where_sql[] = 'id in ('.implode(',',$tagarr).')';	
				$filterurl .= '/tags/'.$tagid;
				$filterurl .= '/uniquetags/'.$uniquetags;
				foreach ($unitags as $tag){
					$searchtag[$taglist[$tag]['group']]['name'] = $taglist[$tag]['groupname'];	
					$searchtag[$taglist[$tag]['group']]['tag_arr'][] = $tag;
				}
				
			}		
			
		

		$where_sql[]= "source = {$source}";
		$filterurl .= $srcurl = '/source/'.$source;
		if(!empty($where_sql)){
			$where_sql = implode(' and ',$where_sql);
		}else{
			$where_sql = 1;
		}
		
		import("@.ORG.Page");
		$param = http_build_query($_GET);
		$count = $slavemodel -> table('zy_collect') -> where($where_sql)->count();
		
		$Page = new Page($count, 20, $param);
		$result = $slavemodel -> table('zy_collect') -> where($where_sql)-> limit($Page->firstRow . ',' . $Page->listRows)->order($order_sql)-> select();
		$model_bbs = D('Zhiyoo.bbs');
		foreach($result as $key => $val){
			// if($val['source'] == 0){//论坛采集内容状态
   //              $result[$key]['url'] = 'http://bbs.zhiyoo.com/thread-'.$val['tid'].'-1-1.html';
			// 	$temp = $model_bbs->query("select status from x15_common_member where uid={$val['authorid']} limit 1");//查用户状态
			// 	if($temp[0]['status'] != 0 ){
			// 		$result[$key]['ustatus'] = true;
			// 		$result[$key]['alt'] = '该贴用户被禁言';
			// 	}else{
			// 		$temp = $model_bbs->query("select displayorder from x15_forum_thread where tid={$val['tid']} limit 1");//查帖子状态
			// 		if($temp[0]['displayorder'] < 0 ){
			// 			$result[$key]['ustatus'] = true;
			// 			$result[$key]['alt'] = '该贴为不显示状态';
			// 		}
			// 	}
			// }
			if($val['img_path'] != '') {
				if($result[$key]['source']==0) $result[$key]['img_path'] = C('bbs_attach_url').$val['img_path'];
				}

			$thread_tags = $slavemodel -> table('zy_idtotagid') -> where("id={$val['id']}") ->select();
			$threadtadid = $tduniquetag = $unsettag = array();
			//取出每个主题的tagid,并且记录下有子标签的父标签tagid
			foreach($thread_tags as $tagidlist){
				$tduniquetag[$tagidlist['tagid']] = $threadtadid[$tagidlist['tagid']] = $tagidlist['tagid'];
				if($taglist[$tagidlist['tagid']]['parentid']>0) $unsettag[] = $taglist[$tagidlist['tagid']]['parentid'];
			}
			//删除有子标签的父标签的tagid，并且把全部的该主题全部的tagid保存成url
			foreach($unsettag as $delid){unset($tduniquetag[$delid]);}
			foreach($tduniquetag as $tag){
				$tag_result[$val['id']][$taglist[$tag]['group']]['name'] = $taglist[$tag]['groupname'];	
				$tag_result[$val['id']][$taglist[$tag]['group']]['tag_arr'][] = $tag;
				$tag_result[$val['id']][$taglist[$tag]['group']]['tag_url'] = implode('_',$threadtadid).'_';
			}
			//如果主题没有标签，修改主题的打标签状态为未打标签
			if(empty($thread_tags)) {
				$slavemodel->table('zy_collect')->where("id={$val['id']}")->save(array('tagstatus'=>0));
				$result[$key]['tagstatus'] = '0';
				}
		} 
		if($source == 1) $websitename = $slavemodel->query("SELECT website_name FROM `zy_collect` WHERE source=1 and website_name <>'' GROUP BY website_name");

		$rulelist = $slavemodel->getrules();
		
		$grouplist = $slavemodel->gettaggroup();
		$forumlist = $slavemodel->getforum();
		//保留标签功能
		$show = $Page->show();
		if($_GET['lr']){
			$lr = $_GET['lr'];
		}else{
			$lr = 20;
		}
		if($_GET['p']){
			$p = $_GET['p'];
		}else{
			$p = 1;
		}
		$Page->setConfig('header', '篇记录');
		$Page->setConfig('first', '<<');
		$Page->setConfig('last', '>>');
		$this -> assign('source',$source);
		$this -> assign('websitename',$websitename);
		$this -> assign('taglist',$taglist);
		$this -> assign('searchtag',$searchtag);
		$this -> assign('srcurl',$srcurl);
		$this -> assign('tag_result',$tag_result);
		$this -> assign('grouplist',$grouplist);
		$this -> assign('forumlist',$forumlist);
		$this->assign('bbs_model',$bbs_modle);
		$this -> assign('lr',$lr);
		$this -> assign('p',$p);
        $this -> assign("page", $show);
		$this -> assign(array('result'=>$result,'th'=>$th,'filterurl'=>$filterurl,'rulelist'=>$rulelist,'cat'=>$cat));
		$this -> display();
	}
	
	
	function add_list(){
		$tid =(int) $_POST['addtid'];
		if(!$tid){
			$this->error('请输入正确的tid');
		} 
		$zhiyoo_model = D('Zhiyoo.Zhiyoo');
		$result = $zhiyoo_model -> table('zy_collect') -> where('tid='.$tid)->select();
		if($result){
			$this -> error("该主题已存在素材池");
		}
			
		$bbs_modle = D('Zhiyoo.bbs');
		$thread = $bbs_modle -> getthread($tid);
		$temp = $bbs_modle->query("select status from x15_common_member where uid={$thread['authorid']} limit 1");//查用户状态
		if($temp[0]['status'] != 0 ){
			$this -> error("该贴用户被禁言");
		}else{
			$temp = $bbs_modle->query("select displayorder from x15_forum_thread where tid={$tid} limit 1");//查帖子状态
			if($temp[0]['displayorder'] < 0 ){
				$this -> error("该贴为不显示状态");
			}
		}	
	
		if(empty($thread)){
			$this -> error("论坛主题不存在");
		}
		$data['tid'] = $tid;
		$data['author'] = $thread['author'];
		$data['authorid'] = $thread['authorid'];
		$data['title'] = $thread['subject'];
		$data['img_path'] = $thread['attachment'];
		$data['content'] = $thread['message'];
		$data['url'] = 'http://bbs.zhiyoo.com/thread-'.$tid.'-1-1.html';
		$data['rules'] = '99';
		$data['fid'] = $thread['fid'];
		$data['fname'] =  $thread['name'];
		$data['views'] = $thread['views'];
		$data['replies'] = $thread['replies'];
		$data['dateline'] = $thread['dateline'];
		$data['addtime'] = time();
		$data['pid'] = $thread['pid'];
		$data['status'] = 1;
		$data['source'] = 0;
		$data['substatus'] = 0;
		$data['tagstatus'] = 0;
		$result = $zhiyoo_model -> table('zy_collect') -> add($data);
		$res = $zhiyoo_model->table('zy_forum_forum')->where('fid = '.$data['fid'])->select();
		if(!$res){
		
			$zhiyoo_model->table('zy_forum_forum')->add(array('fid'=>$data['fid'],'fname'=>$data['fname']));
		}
		
		if($result){
			$this -> writelog("智友内容管理-素材采集池 已添加tid为{$tid},id为{$result}的论坛主题","zy_collect",$result,__ACTION__ ,"","add");
			$this -> assign('jumpUrl',"/index.php/Zhiyoo/Collectresult/result_list");
			$this -> success("添加成功");
		}else{
			$this -> error("添加失败");
		}
	}
	
	function add_list_show(){
		$this -> display();
	}
	
	function sub_thread(){
		$source = $_GET['source'] ? $_GET['source'] : 0 ;
		$bbs_modle = D('Zhiyoo.Zhiyoo');
		if(isset($_GET['substatus']))$fromurl .= "/substatus/".$_GET['substatus'];
		if(isset($_GET['tagstatus']))$fromurl .= "/tagstatus/".$_GET['tagstatus'];
		if(isset($_GET['status']))$fromurl .= "/status/".$_GET['status'];
		if(isset($_GET['platform']))$fromurl .= "/platform/".$_GET['platform'];
		if($_GET['ids']) $ids = $_GET['ids']; else $this-error('请选择数据');
		$err_res = $bbs_modle -> table('zy_collect') -> field('id')->where('id in ('.$ids.') and (tagstatus = 0 or status = 0)') -> select();
		if($err_res){
			foreach ($err_res as $val){
				$notags[] = $val['id'];
			}
			
		}
		if($err_res){
			$ids_arr = explode(',',$ids);
			$tids = array_diff($ids_arr,$notags); 
			$ids = implode(',',$tids);
		}
		$result = $bbs_modle -> table('zy_collect') -> where('id in ('.$ids.')') -> save(array('substatus' => 1,'addfoddertm'=>time()));
		if($result){
				$this -> writelog("智友内容管理-素材采集池 已经添加id为{$ids}内容至素材库","zy_collect",$ids,__ACTION__ ,"","add");
				$to ='result_list' ;if($source==1) $to = 'outer_result';
				$this -> assign("jumpUrl","/index.php/Zhiyoo/Collectresult/{$to}/source/{$source}".$fromurl);
			if($err_res){
				$ids = implode(',',$notags);
				$this -> error("id为{$ids}的内容记录还没有打标签或者已经被删除,其余添加成功","zy_collect",$ids,__ACTION__ ,"","del");
			}else{
				$this -> success("添加成功");
			}
		}else{
			$this -> error("添加失败");
		}
			
		
	}
	
	function del_list(){
		$bbs_modle = D('Zhiyoo.Zhiyoo');
		$ids = $_GET['ids'];
		$result = $bbs_modle -> table('zy_collect') -> field('id')->where('id in ('.$ids.')')->save(array('status' => 0,'addtime'=>time(),'addfoddertm'=>time()));
		//$bbs_modle -> table('zy_idtotagid') -> where('id in ('.$ids.')') -> delete(); 
		if($result){
			echo 1;
			$this -> writelog("智友内容管理-素材采集池 已删除id为{$ids}的素材池的内容记录","zy_collect",$ids,__ACTION__ ,"","del");
		}else{
			echo 2;
		}
	}
	
	function reback_list(){
		$source = $_GET['source'] ? $_GET['source'] : 0 ;
		$bbs_modle = D('Zhiyoo.Zhiyoo');
		$id = $_GET['id'];
		if(isset($_GET['substatus']))$fromurl .= "/substatus/".$_GET['substatus'];
		if(isset($_GET['tagstatus']))$fromurl .= "/tagstatus/".$_GET['tagstatus'];
		if(isset($_GET['status']))$fromurl .= "/status/".$_GET['status'];
		if(isset($_GET['platform']))$fromurl .= "/platform/".$_GET['platform'];
		$result = $bbs_modle -> table('zy_collect') -> where('id in ('.$id.')') -> save(array('status' => 1,'addtime'=>time(),'addfoddertm'=>time()));
		if($result){
			$this -> writelog("智友内容管理-素材采集池 已经撤销删除id为{$id}的内容记录","zy_collect",$id,__ACTION__ ,"","del");
			if($_GET['from']=='data'){$this -> assign("jumpUrl","/index.php/Zhiyoo/Data/result_list".$fromurl);
			}elseif($_GET['from']=='taglist'){
				$this -> assign("jumpUrl","/index.php/Zhiyoo/Tag/result_list/tagid/".$_GET['tagid']);
			}else{
				$to ='result_list' ;if($source==1) $to = 'outer_result';
				$this -> assign("jumpUrl","/index.php/Zhiyoo/Collectresult/{$to}/source/{$source}".$fromurl);
				}
			$this -> success("撤销删除成功");
		}else{
			$this -> error("撤销删除失败");
		}
	}
	
	function add_tags(){
		$id = $_GET['id'];
		$source = $_GET['source'] ? $_GET['source'] : 0 ;
		$tags = explode('_',$_GET['tags']);
		$bbs_modle = D('Zhiyoo.Zhiyoo');
		array_pop($tags);
		sort($tags);
		$bbs_modle -> table('zy_id_tagstr') -> where('id='.$id)->delete();
		$bbs_modle -> table('zy_idtotagid') -> where('id='.$id) -> delete();
		//当标签有改动是，删除掉栏目配置表的内容记录
		$bbs_modle -> table('zy_column_content') -> where("id={$id} and source=0")->delete();
		if(!empty($tags)){
			$tagstr = implode('_',$tags);
			$bbs_modle -> table('zy_id_tagstr') -> add(array('id'=>$id,'tagstr'=>$tagstr));
			
			foreach($tags as $tagid){
				$data['id'] = $id;
				$data['tagid'] = $tagid;
				$result = $bbs_modle ->  table('zy_idtotagid') ->data($data)-> add();
			}
			$bbs_modle ->  table('zy_collect') ->where('id='.$id)->save(array('tagstatus'=>1));
		}else{
			$result = $bbs_modle ->table('zy_collect') ->where("id={$id}")->save(array('tagstatus'=>0));
			if($_GET['from']=='edit_content') {echo 0;die;}
		}
	
		if($result){
			$this -> writelog("智友内容管理-素材采集池 已经编辑id为{$id}的内容记录的标签","zy_collect",$id,__ACTION__ ,"","edit");
			if($_GET['from']=='edit_content') {echo 1;die;}
			if($_GET['from']=='data'){$this -> assign("jumpUrl","/index.php/Zhiyoo/Data/result_list/source/{$source}/");
			}else{ 
				$to ='result_list' ;if($source==1) $to = 'outer_result';
				$this -> assign("jumpUrl","/index.php/Zhiyoo/Collectresult/{$to}/source/{$source}");
			}
			$this -> success("编辑标签成功"); 
		}else{
			 if($_GET['from']=='edit_content') {echo 2;die;}
			$this -> error("编辑标签失败");
		}
	}
	
	
	function add_tags_n(){
		$ids = explode(',',$_GET['id']);
		$ids = array_unique($ids);//id去重
		$_GET['id'] = implode(',',$ids);//记日志用
		// $source = $_GET['source'] ? $_GET['source'] : 0 ;
		$tags = explode('_',$_GET['tags']);
		$bbs_modle = D('Zhiyoo.Zhiyoo');
		
		if(empty($ids)){
			$this -> error("没有相应的内容！");
		}
		
		if(!empty($tags)){
			foreach($ids as $id){
				//选出内容原有标签
				$result = $bbs_modle -> table('zy_idtotagid') -> where(array('id'=>$id))->select();
				$oldtag = array();
				foreach($result as $val){
					$oldtag[$val['tagid']] = 1;
				}
				foreach($tags as $val){
					$oldtag[$val] = 1;
				}
				$newtag = array_keys($oldtag);
				
				//当标签有改动是，删除掉栏目配置表的内容记录
				//栏目规则更改，去掉这一条
				$bbs_modle -> table('zy_column_content') -> where("id={$id} and source=0")->delete();
				
				//更新内容标签
				sort($newtag);
				$tagstr = implode('_',$newtag);
				$result = $bbs_modle -> table('zy_id_tagstr')->where(array('id'=>$id)) ->find();
				if(!$result){
					$bbs_modle -> table('zy_id_tagstr')->add(array('id'=>$id,'tagstr'=>$tagstr));
				}else{
					$bbs_modle -> table('zy_id_tagstr')->where(array('id'=>$id)) -> save(array('tagstr'=>$tagstr));
				}
				foreach($newtag as $tagid){
					$result = $bbs_modle -> table('zy_idtotagid') -> where(array('id'=>$id,'tagid'=>$tagid))->find();
					if(!$result) $bbs_modle ->  table('zy_idtotagid') ->data(array('id'=>$id,'tagid'=>$tagid))-> add();
				}
				//更新内容标签状态
				$bbs_modle ->  table('zy_collect') ->where(array('id'=>$id))->save(array('tagstatus'=>1));
			
			}
			
			
		}else{
			$this -> error("请选择标签！");
		}
	
		if($result!==false){
			$this -> writelog("智友内容管理-批量添加标签 已经添加id为{$_GET['id']}的内容记录的标签id{$_GET['tags']}","zy_collect",$_GET['id'],__ACTION__ ,"","edit");
			$this -> success("编辑标签成功"); 
		}else{
			 if($_GET['from']=='edit_content') {echo 2;die;}
			$this -> error("编辑标签失败");
		}
	}

	function add_tags_c(){
		$ids = explode(',',$_GET['id']);
		$ids = array_unique($ids);//id去重
		$_GET['id'] = implode(',',$ids);//记日志用
		// $source = $_GET['source'] ? $_GET['source'] : 0 ;
		$tags = explode('_',$_GET['tags']);
		$bbs_modle = D('Zhiyoo.Zhiyoo');
		
		if(empty($ids)){
			$this -> error("没有相应的内容！");
		}
		
		if(!empty($tags)){
			foreach($ids as $id){
				//删除内容原有标签
				$result = $bbs_modle -> table('zy_idtotagid') -> where(array('id'=>$id))->delete();
				$newtags = array();
				foreach($tags as $val){
					$newtags[$val] = 1;
				}
				$newtag = array_keys($newtags);
				
				//当标签有改动是，删除掉栏目配置表的内容记录
				//栏目规则更改，去掉这一条
				$bbs_modle -> table('zy_column_content') -> where("id={$id} and source=0")->delete();
				
				//更新内容标签
				sort($newtag);
				$tagstr = implode('_',$newtag);
				$result = $bbs_modle -> table('zy_id_tagstr')->where(array('id'=>$id)) ->find();
				if(!$result){
					$bbs_modle -> table('zy_id_tagstr')->add(array('id'=>$id,'tagstr'=>$tagstr));
				}else{
					$bbs_modle -> table('zy_id_tagstr')->where(array('id'=>$id)) -> save(array('tagstr'=>$tagstr));
				}
				foreach($newtag as $tagid){
					$result = $bbs_modle -> table('zy_idtotagid') -> where(array('id'=>$id,'tagid'=>$tagid))->find();
					if(!$result) $bbs_modle ->  table('zy_idtotagid') ->data(array('id'=>$id,'tagid'=>$tagid))-> add();
				}
				//更新内容标签状态
				$bbs_modle ->  table('zy_collect') ->where(array('id'=>$id))->save(array('tagstatus'=>1));
			
			}
			
			
		}else{
			$this -> error("请选择标签组！");
		}
	
		if($result!==false){
			$this -> writelog("智友内容管理-批量添加标签组 已经添加id为{$_GET['id']}的内容记录的标签组id{$_GET['tags']}","zy_collect",$_GET['id'],__ACTION__ ,"","edit");
			$this -> success("编辑标签组成功"); 
		}else{
			 if($_GET['from']=='edit_content') {echo 2;die;}
			$this -> error("编辑标签失败");
		}
	}
	
	function tag_list_show1(){
		$bbs_modle = D('Zhiyoo.Zhiyoo');
		$source = $_GET['source'] ? $_GET['source'] : 0 ;
		//获取所有的一级分类
		$result = $bbs_modle ->table('zy_tagsgroup')->field(array('groupid','groupname'))->where('status = 1')->select();
		foreach($result as $val){
			$cat[$val['groupid']] = $val['groupname'];
		}
		$result = $bbs_modle ->table('zy_tags')->field(array('tagid','tagname','group','parentid'))->where('status = 1')->select();
		foreach($result as $val){
			$taginfo[$val['group']][] = $val;
			$tagpinfo[$val['parentid']][] = $val;
			$taglist[$val['tagid']] = $val;
		}
		
		$id = $_GET['id']?$_GET['id']:0;
		$this->assign('tid' ,$id);
		if($_GET['tags']) {
			$tags = explode('_',$_GET['tags']);
			$this->assign('tags' ,$tags);
		}elseif($id){
			$res = $bbs_modle -> table('zy_idtotagid')->field('tagid')->where('id='.$id)->select();
			foreach($res as $val){
				$tags[]=$val['tagid'];
			}
			$this->assign('tags' ,$tags);
		}
		$this->assign('source',$source);
		$this->assign('taginfo',$taginfo);
		$this->assign('tagpinfo',$tagpinfo);
		$this->assign('tagpinfo_json',json_encode($tagpinfo));
		$this->assign('taglist',$taglist);
		$this->assign('taglist_json',json_encode($taglist));
		$this->assign('grouplist_json',json_encode($cat));
		$this->assign('cat',$cat);
		$this->display();
	}
	
	function tag_list_show_n(){//批量添加
		$bbs_modle = D('Zhiyoo.Zhiyoo');
		// $source = $_GET['source'] ? $_GET['source'] : 0 ;
		//获取所有的一级分类
		$result = $bbs_modle ->table('zy_tagsgroup')->field(array('groupid','groupname'))->where('status = 1')->select();
		foreach($result as $val){
			$cat[$val['groupid']] = $val['groupname'];
		}
		$result = $bbs_modle ->table('zy_tags')->field(array('tagid','tagname','group','parentid'))->where('status = 1')->select();
		foreach($result as $val){
			$taginfo[$val['group']][] = $val;
			$tagpinfo[$val['parentid']][] = $val;
			$taglist[$val['tagid']] = $val;
		}
		
		$this->assign('taginfo',$taginfo);
		$this->assign('tagpinfo',$tagpinfo);
		$this->assign('tagpinfo_json',json_encode($tagpinfo));
		$this->assign('taglist',$taglist);
		$this->assign('taglist_json',json_encode($taglist));
		$this->assign('grouplist_json',json_encode($cat));
		$this->assign('cat',$cat);
		$this->assign('cat',$cat);
		$this->display();
	}

	function tag_list_show_c(){//批量添加标签组
		$bbs_modle = D('Zhiyoo.Zhiyoo');
		//获取所有的一级分类
		$orderBy = 'rank asc';
		$class = $bbs_modle ->table("zy_tagsclass")->where(array('status'=>1))->order($orderBy)->select();//读取一级目录，按照优先级升序排列
		foreach($class as $key => $val){
			//根据tagid获取名称
			if(!empty($val['tagsid'])){
				// $where['tagid'] = array('in',$val['tagsid']);
				// $classname = $bbs_modle -> table("zy_tags")->where($where)->select();
				// $classtagnames = array();
				// foreach ($classname as $item) {
				// 	if(!empty($item['tagname'])){
				// 		$classtagnames[]['name'] = $item['tagname'];
				// 	}	
				// }
				$thread_tags = explode(',',$val['tagsid']);
				
				// $class[$key]['classtagnames'] = $classtagnames;
				$taglist = $bbs_modle->gettags();
				
				$threadtadid = $tduniquetag = $unsettag = array();
				foreach($thread_tags as $tagidlist){
					$tduniquetag[$tagidlist] = $threadtadid[$tagidlist] = $tagidlist;
					if($taglist[$tagidlist]['parentid']>0) $unsettag[] = $taglist[$tagidlist]['parentid'];
				}
				
				foreach($unsettag as $delid){unset($tduniquetag[$delid]);}
				foreach($tduniquetag as $tag){
					$tag_result[$val['classid']][$taglist[$tag]['group']]['name'] = $taglist[$tag]['groupname'];	
					$tag_result[$val['classid']][$taglist[$tag]['group']]['tag_arr'][] = $tag;
					$tag_result[$val['classid']][$taglist[$tag]['group']]['tag_url'] = implode('_',$threadtadid).'_';
				}
			}
		}
		
		$this -> assign('list',$class);
		$this -> assign('tag_result',$tag_result);
		$this -> assign('taglist',$taglist);
		$this->display();
	}
	
	function edit_content(){
		$from = $_GET['from'];
		$id = $_GET['id'];
		$cid = $_GET['cid'];
		$advid = $_GET['advid'];
		$source = $_GET['source'] ? $_GET['source'] : 0 ;
		$bbs_modle = D('Zhiyoo.Zhiyoo');
		//对标签页内容的特殊处理
		if($from == 'taglist'){
			if(!empty($_GET['advid'])){
				$tagfrom = 'taglist_a';
			}elseif(isset($_GET['id'])){
				$tagfrom = 'taglist_c';
			}
		}
		if($from == 'result_list' || $from == 'data'||$from == 'outer_result'||($from == 'taglist' && $tagfrom == 'taglist_c')){
			$result = $bbs_modle ->table('zy_collect as c')->field('c.title,c.author,c.tagstatus,ce.*')->join('left join zy_collect_ext as ce on c.id=ce.colid')->where('c.id='.$id)->find();
			
		}
		if($from == 'column_content'|| $from == 'content_list_filter'|| $from == "schedule"|| ($from == "taglist" && $tagfrom == "taglist_a")){
				$exist = $bbs_modle -> table('zy_collect_ext') -> where("advid={$advid}")->find();
				if($exist){ 
					$result = $bbs_modle ->table('zy_collect_ext as zce')->field('zc.title,zc.author,zc.tagstatus,zce.*')->join('left join zy_collect as zc on zce.colid = zc.id')->where("zce.advid={$advid}")->find();
				}else{
					$result = $bbs_modle ->table('zy_collect as c')->field('c.title,c.author,c.tagstatus,ce.*')->join('left join zy_collect_ext as ce on c.id=ce.colid')->where('c.id='.$id)->find();	
				}
		}
		if($from == 'care' || $from == 'wrap' || $from == 'dailyrecom'){
				$exist = $bbs_modle -> table('zy_collect_ext') -> where("advid={$advid}")->find();
				$result = $bbs_modle ->table('zy_collect_ext as zce')->field('zc.title,zc.author,zc.tagstatus,zce.*')->join('left join zy_collect as zc on zce.colid = zc.id')->where("zce.advid={$advid}")->find();
				$id = $result['colid'];
		}
		$platform = $bbs_modle->table('zy_schedule_platform')->where('status=1')->select();
		$position = $bbs_modle -> query("select * from zy_schedule_position");
		$allresult = $bbs_modle ->query("select advid,platform,position from zy_collect_ext where colid=".$result['colid']);
		foreach($allresult as $val){
			if($val['advid'] != $advid)
			$disable[] = $val['platform'].'_'.$val['position'];
			$checked[] = $val['platform'].'_'.$val['position'];
		}
		//对宣传标题中带有英文双引号进行转义
		$result['ext_title'] = htmlspecialchars($result['ext_title']);
		$result['img_title'] = htmlspecialchars($result['img_title']);
		//如果有标签，将标签按行输出 分类：父标签-子标签 格式输出
		$thread_tags = $bbs_modle->table('zy_idtotagid')->where('id ='.$id)->select();
		$taglist = $bbs_modle->gettags();
		//把标签id按分类归并
		
		$threadtadid = $tduniquetag = $unsettag = array();
		//取出每个主题的tagid,并且记录下有子标签的父标签tagid
		foreach($thread_tags as $tagidlist){
			$tduniquetag[$tagidlist['tagid']] = $threadtadid[$tagidlist['tagid']] = $tagidlist['tagid'];
			if($taglist[$tagidlist['tagid']]['parentid']>0) $unsettag[] = $taglist[$tagidlist['tagid']]['parentid'];
		}
		//删除有子标签的父标签的tagid，并且把全部的该主题全部的tagid保存成url
		foreach($unsettag as $delid){unset($tduniquetag[$delid]);}
		foreach($tduniquetag as $tag){
			$tag_result[$taglist[$tag]['group']]['name'] = $taglist[$tag]['groupname'];	
			$tag_result[$taglist[$tag]['group']]['tag_arr'][] = $tag;
			$tag_result[$taglist[$tag]['group']]['tag_url'] = implode('_',$threadtadid).'_';
		}
		$result['ext_title'] = htmlspecialchars($result['ext_title']);
		$result['img_title'] = htmlspecialchars($result['img_title']);
		$this->assign('from',$from);
		$this->assign('platform',$platform);
		$this->assign('position',$position);
		$this -> assign("checked", $checked);
		$this -> assign("disable", $disable);
		$this->assign('taglist',$taglist);
		$this->assign('result',$result);
		$this->assign('source',$source);
		$this->assign('id',$id);
		$this->assign('cid',$cid);
		$this->assign('advid',$advid);
		$this->assign('tag_result',$tag_result);
		$this->assign('referer',$_SERVER['HTTP_REFERER']);
		$this->display();
		
	}
	


	function edit_content_sub(){
		$id = $_POST['id'];
		if($_POST['advid']) $advid = $_POST['advid'];
		if($_POST['source']) $source = $_POST['source'];
		$from = $_POST['from'];
		if($_POST['cid']) $cid = $_POST['cid'];
		// var_dump($id,$advid,$from);exit;
		$bbs_modle = D('Zhiyoo.Zhiyoo');
		$zy_collect_extmodel = D('Zhiyoo.collect_ext');
		$zy_schedulemodel = D('Zhiyoo.schedule');
		$pos_confmodel = D('Zhiyoo.pos_conf');
		
		$tid = $bbs_modle -> table('zy_collect') ->field('tid')->where("id=$id")->find();
		$data['colid'] = $id;
		$data['tid'] = $tid['tid'];
		$data['ext_title'] = $_POST['ext_title'] ?  $_POST['ext_title'] : ''; 
		$data['img_title'] = $_POST['img_title'] ?  $_POST['img_title'] : ''; 
		mb_internal_encoding('utf-8');
		if(mb_strlen($data['ext_title']) > 50) $this ->error('宣传标题最多可添加50个字符');
		//if($_POST['jumpstatus']) $data['jump_status'] = $_POST['jumpstatus'];
		//if($_POST['jumpurl']&&$_POST['jumpstatus']) $data['jump_url'] = $_POST['jumpurl'];
		if($_POST['pp']) $pp = $_POST['pp'];
		$data['description'] = $_POST['description'] ? $_POST['description'] : '';
		$yearmonth = date("Ym");
		$day=date('d');
		$datedir = '/zhiyoo/';
		$savepath = UPLOAD_PATH.$datedir;
		//对标签页内容的特殊处理
		if($from == 'taglist'){
			if(!empty($advid)){
				$tagfrom = 'taglist_a';
			}elseif(!empty($id)){
				$tagfrom = 'taglist_c';
			}
		}

		if($_FILES['img1']['size']>0){
			$config = array(
				'tmp_file_dir' => '/tmp/',
				'width' => 200,
				'filesize' => 100000,
				'real_width' => 200,
			);
			$imgpath = $this -> _upload($_FILES['img1'],$savepath,$config);
			$data['img_path1'] = $datedir.$imgpath;
		}
		
		if($_FILES['img2']['size']>0){
			$config = array(
				'tmp_file_dir' => '/tmp/',
				'width' => 800,
				'filesize' => 200000,
				'real_width' => 700,
			);
			$imgpath = $this -> _upload($_FILES['img2'],$savepath,$config);
			$data['img_path2'] = $datedir.$imgpath;			
		}
		if($_FILES['img4']['size']>0){
			$config = array(
				'tmp_file_dir' => '/tmp/',
				'width' => 800,
				'filesize' => 200000,
				'real_width' => 700,
			);
			$imgpath = $this -> _upload($_FILES['img4'],$savepath,$config);
			$data['img_path4'] = $datedir.$imgpath;			
		}
		if($_FILES['img5']['size']>0){
			$config = array(
				'tmp_file_dir' => '/tmp/',
				'width' => 800,
				'filesize' => 200000,
				'real_width' => 700,
			);
			$imgpath = $this -> _upload($_FILES['img5'],$savepath,$config);
			$data['img_path5'] = $datedir.$imgpath;			
		}
		if($_FILES['img6']['size']>0){
			$config = array(
				'tmp_file_dir' => '/tmp/',
				'width' => 800,
				'filesize' => 200000,
				'real_width' => 700,
			);
			$imgpath = $this -> _upload($_FILES['img6'],$savepath,$config);
			$data['img_path6'] = $datedir.$imgpath;			
		}
		if($from == "result_list" || $from == 'data'||$from == "outer_result"||($from == 'taglist' && $tagfrom == 'taglist_c')){
			//如果是在素材池编辑内容列表，用colid去查询又没有在collect_ext有无记录
			$exist = $bbs_modle->table('zy_collect_ext')->where("colid = {$id}")->find();
			if($exist) 
				$res = $bbs_modle->table('zy_collect_ext')->where("colid={$id}")->save($data);
			else 
				$res = $bbs_modle->table('zy_collect_ext')->add($data);
		}elseif($from == 'column_content' || $from == 'content_list_filter' || $from == "schedule"|| $from == "care"|| $from == "wrap"|| $from == "dailyrecom"||($from == 'taglist' && $tagfrom == 'taglist_a')){
			$content = $bbs_modle->query("select platform,position,tid,img_path1,img_path2 from zy_collect_ext where advid='$advid'");//检查当前内容是否被取消
			$copp = $content[0]['platform'].'_'.$content[0]['position'];
			
			if(!isset($data['img_path1'])){
				$data['img_path1'] = $content[0]['img_path1'] ? $content[0]['img_path1'] : '';
			}
			if(!isset($data['img_path2'])){
				$data['img_path2'] = $content[0]['img_path2'] ? $content[0]['img_path2'] : '';
			}
			if(!isset($data['img_path4'])){
				$data['img_path4'] = $content[0]['img_path4'] ? $content[0]['img_path4'] : '';
			}
			// var_dump($data,$imginfo,$content);die;
			if(array_search($copp,$pp)===false || !isset($pp)){//内容被取消
				$bbs_modle->query("delete from zy_collect_ext where advid='$advid'");
				$bbs_modle->query("delete from zy_column_content where aid='$advid'");
				$bbs_modle->query("update zy_schedule set status=-1 where id='$advid'");
				$bbs_modle->query("update zy_pos_conf set status=-1 where advid='$advid' and source=1");
			}
			if(is_array($pp)){
				foreach($pp as $val){//遍历添加平台位置
					$val2 = explode('_',$val);
					$data['platform'] = $val2[0];
					$data['position'] = $val2[1];
					if($copp == $val){//更新当前内容信息
					
						$res = $bbs_modle->table('zy_collect_ext')->where("colid={$id} and advid ={$advid}")->save($data);	
					}
					else{
						$eid = $zy_collect_extmodel->table('zy_collect_ext')->add($data);//
						$data1 = $bbs_modle->table('zy_schedule')->where("id = {$advid}")->find();
						$data1['eid'] = $eid;
						$data1['addschtime'] = time();
						unset($data1['id']);
						unset($data1['deltime']);
						unset($data1['status']);
						$eaid = $zy_schedulemodel->table('zy_schedule')->add($data1);//var_dump($eaid);exit;
						if($eaid !== false){
							//echo $zy_schedulemodel -> getlastsql()."<br/>";
							$zy_collect_extmodel->table('zy_collect_ext')->where("id={$eid}")->save(array('advid'=>$eaid));
							$posdata['advid'] = $eaid;
							$posdata['tid'] = $content[0]['tid'];
							$posdata['platform'] = $data['platform'];
							$posdata['position'] = $data['position'];
							$posdata['source'] = 1;
							$posdata['level'] = 999;
							$posdata['column'] = 0;
							$posdata['addtime'] = $data1['addschtime'];
							$posdata['starttime'] = $data1['starttime'];
							$posdata['endtime'] = $data1['endtime'];
							$eaid = $pos_confmodel->table('zy_pos_conf')->add($posdata);
						}
							//echo $pos_confmodel -> getlastsql()."<br/>";exit;
					}
				
				}
			}
			
		}
		if(isset($_POST['substatus']))$fromurl .= "/substatus/".$_POST['substatus'];
		if(isset($_POST['tagstatus']))$fromurl .= "/tagstatus/".$_POST['tagstatus'];
		if(isset($_POST['status']))$fromurl .= "/status/".$_POST['status'];
		if(isset($_POST['platform']))$fromurl .= "/platform/".$_POST['platform'];
		if($eaid !== false){
			if($from == 'result_list') {
				//$to ='result_list' ;if($source==1) $to = 'outer_result';
				$this -> assign('jumpUrl',"/index.php/Zhiyoo/Collectresult/result_list{$fromurl}");
			}
			if($from == 'outer_result') $this -> assign('jumpUrl',"/index.php/Zhiyoo/Collectresult/outer_result{$fromurl}");
			if($from == 'column_content') $this -> assign('jumpUrl',"/index.php/Zhiyoo/Column/content_list/cid/{$cid}");
			if($from == 'content_list_filter') $this -> assign('jumpUrl',"/index.php/Zhiyoo/Column/content_list_filter/cid/{$cid}");
			if($from == 'data') $this -> assign('jumpUrl',"/index.php/Zhiyoo/Data/result_list{$fromurl}");
			if($from == 'schedule')if($source == 'outline') $this -> assign('jumpUrl',"/index.php/Zhiyoo/Waiton/result_outline/source/{$source}{$fromurl}");
								elseif($source == 'online') $this -> assign('jumpUrl',"/index.php/Zhiyoo/Waiton/result_online/source/{$source}{$fromurl}");
								else $this -> assign('jumpUrl',"/index.php/Zhiyoo/Waiton/result_list/source/{$source}{$fromurl}");
			if($from == 'care') $this -> assign('jumpUrl',"/index.php/Zhiyoo/Care/result_list/postime/{$source}");
			if($from == 'dailyrecom') $this -> assign('jumpUrl',"/index.php/Zhiyoo/Care/dailyrecom/postime/{$source}");
			if($from == 'wrap') $this -> assign('jumpUrl',"/index.php/Zhiyoo/Care/result_wrap/position/wrap/postime/{$source}");
			if($from == 'taglist') $this -> assign('jumpUrl',"/index.php/Zhiyoo/Tag/result_list/tagid/{$_POST['tagid']}");
			if($advid)  $this -> writelog("智友内容管理 已经编辑advid为{$advid}内容","zy_collect_ext",$advid,__ACTION__ ,"","edit");
			$this -> writelog("智友内容管理 已经编辑colid为{$id}内容","zy_collect_ext",$id,__ACTION__ ,"","edit");
            $jumpUrl = parse_url($_POST['referer'],PHP_URL_PATH);
            if(strpos($jumpUrl, 'delimg') !== false){ $jumpUrl = '';}
            $this -> assign('jumpUrl',$jumpUrl);
			$this -> success('编辑内容成功');
		} else{
			$this ->error('编辑内容失败');
		}
		
		
		
		
	}
	
	
	protected function _uploadold($savepath) {
		import ( "@.ORG.UploadFile" );
		$upload = new UploadFile();
		//设置上传文件大小
		$upload->maxSize = 3292200;
		//设置上传文件类型
		$upload->allowExts = explode ( ',', 'jpg,gif,png,jpeg' );
		//设置附件上传目录
		$upload->savePath = $savepath;
		//设置需要生成缩略图，仅对图像文件有效
		$upload->thumb = true;
		// 设置引用图片类库包路径
		$upload->imageClassPath = '@.ORG.Image';
		//设置需要生成缩略图的文件后缀
		$upload->thumbPrefix = 'm_,s_'; //生产2张缩略图
		//设置缩略图最大宽度
		$upload->thumbMaxWidth = '480,128';
		//设置缩略图最大高度
		$upload->thumbMaxHeight = '1000,1000';
		//设置上传文件规则
		$upload->saveRule = uniqid;
		//删除原图
		$upload->thumbRemoveOrigin = true;
		if (! $upload->upload()) {
			//捕获上传异常
			$this->error ( $upload->getErrorMsg());
		} else {
			//取得成功上传的文件信息
			$uploadList = $upload->getUploadFileInfo();
			
		}
		foreach($uploadList as $val){
			if($val['post_name'] == 'img1') unlink($val['savepath'].'m_'.$val['savename'])  ;
			if($val['post_name'] == 'img2') unlink($val['savepath'].'s_'.$val['savename'])  ;
			$list[$val['post_name']]['name']=$val['savename'];
			
		}
		return $list;
	}

	function outer_result(){
		if(!isset($_GET['source'])) $_GET['source'] = 1;
		$this->result_list();
	}
	
	protected function _upload($file,$savepath,$config){
		include_once dirname(realpath(__FILE__)).'/imagemagick.php';
		return  up_load_thumbimg($file,$savepath,$config);
	}

	function delimg(){
		$model = D('Zhiyoo.Zhiyoo');
        $advid = intval($_GET['advid']);
        $colid = intval($_GET['colid']);
        if($_GET['path'] == 1) $save = array('img_path1'=>'');
        elseif($_GET['path'] == 2) $save = array('img_path2'=>'');
        elseif($_GET['path'] == 4) $save = array('img_path4'=>'');
        $result = $model->table('zy_collect_ext') ->where(array('advid'=>$advid,'colid'=>$colid))->save($save);
        if($result) {
            $this -> writelog("智友内容管理 已删除advid为{$advid}colid为{$colid}的图片","zy_collect_ext",$advid,__ACTION__ ,"","del");
            $this -> success('编辑内容成功');
        }else{
            $this ->error('删除内容图片失败');
        }
	}

}