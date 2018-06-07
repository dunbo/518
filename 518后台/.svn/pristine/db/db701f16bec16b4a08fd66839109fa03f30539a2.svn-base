<?php 

Class DataAction extends CommonAction{
	function result_list(){
		// define('BBSLUNTAN_HOST','http://forum.anzhi.com/forum.php?mod=post&action=edit');
		!defined('BBSLUNTAN_HOST') && define('BBSLUNTAN_HOST','http://bbs.zhiyoo.com/forum.php?mod=post&action=edit');
		$order_sql = !$_GET['type']?  'c.addfoddertm desc': "{$_GET['type']} {$_GET['order']},c.addfoddertm desc";//var_dump($order_sql);
		$th[$_GET['type']] = $_GET['order'] == 'asc' ? 'desc' : 'asc';
		$where_sql = array();
		$cat = $filterurl = '';
		$bbs_model = D('Zhiyoo.Zhiyoo');
		if(isset($_GET['substatus'])) {
			$where_sql['c.substatus'] = $_GET['substatus'];
			$cat .= '/substatus/'.$_GET['substatus'];
		}
		else{
			$where_sql['c.substatus'] = array(array('eq',1),array('eq',-2), 'or');
		}
		if(isset($_GET['status'])) {
			$where_sql['c.status'] = $_GET['status'];
			$cat .= '/status/'.$_GET['status'];
		}else{
			$where_sql['c.status'] = 1;
		}
		
			$rid = $_GET['rule']== '全部'?'':$_GET['rule'];
			$start = strtotime($_GET['start_tm']);
			$end = strtotime($_GET['end_tm']);
			$title = $_GET['title'];
			$tid = $_GET['tid'];
			$fid = $_GET['fid'];
			$tagid = $_GET['tags'];
			$uniquetags = $_GET['uniquetags'];
			if($rid) {
				$where_sql['c.rules'] = $rid;
				$filterurl .= '/rule/'.$rid;
				}	
			if($start) {
				$where_sql['c.addfoddertm'] = array('egt',$start);	
				$filterurl .= '/start_tm/'.$_GET['start_tm'];
				
			}
			if($end) {
				$where_sql['c.addfoddertm'] = array('elt',$end);	
				$filterurl .= '/end_tm/'.$_GET['end_tm'];
			}	
			if($title) {
				$where_sql['_string'] = "c.title like '%{$title}%' or e.ext_title like '%{$title}%'";	
				$filterurl .= '/title/'.$title;
			}
			if($tid) {
				$where_sql['c.tid'] = $tid;	
				$filterurl .= '/tid/'.$tid;
			}
			if($fid) {
				$where_sql['c.fid'] = $fid;	
				$filterurl .= '/fid/'.$fid;
			}
			$taglist = $bbs_model->gettags();
			if($tagid) {
				$tagrules = explode('_',$tagid);
				array_pop($tagrules);
				$extsql = '('.implode(',',$tagrules).')';
				$searchcnt = count($tagrules);
				//搜索符合条件的标签
				$tagres = $bbs_model->query("select * from (select id,count(*) as cnt from zy_idtotagid where tagid in {$extsql} group by id ) as A  where A.cnt ={$searchcnt}"	);					
				$unitags = explode('_',$uniquetags);		
				array_pop($unitags);
				foreach($tagres as $val){
					$tagarr[] = $val['id'];
				}
				$where_sql['c.id'] =  array('in',$tagarr);	
				$filterurl .= '/tags/'.$tagid;
				$filterurl .= '/uniquetags/'.$uniquetags;
				foreach ($unitags as $tag){
					$searchtag[$taglist[$tag]['group']]['name'] = $taglist[$tag]['groupname'];	
					$searchtag[$taglist[$tag]['group']]['tag_arr'][] = $tag;
				}
				
			}///var_dump($searchtag);

		if($_GET['source'] != ''){
			$where_sql['c.source'] =  $_GET['source'] ;
			$filterurl .= '/source/'.$_GET['source'];
        }
            
		import("@.ORG.Page");
		$param = http_build_query($_GET);
		$count = $bbs_model -> table('zy_collect c')->join('left join zy_collect_ext e on e.colid=c.id') -> where($where_sql)->count();
		$prepage = isset($_GET['lr']) ? $_GET['lr'] : 20;
		$Page = new Page($count,$prepage , $param);
        $result = $bbs_model -> table('zy_collect c')->join('left join zy_collect_ext e on e.colid=c.id') -> where($where_sql)-> order($order_sql)->limit("{$Page->firstRow} , {$Page->listRows}")->field(array('c.*','e.ext_title','e.img_path1','e.img_path2','e.description'))->select();
		//echo $bbs_model ->getlastsql();
		//var_dump($result);
		$bbs = D('Zhiyoo.bbs');
		foreach($result as $key => $val){
			//===是否已编辑外部内容判断====
			if($val['source'] == 1){
				$threadedit = $bbs -> table('x15_forum_thread')->where(array('tid'=>$val['tid']))->field('lastposter,displayorder')->find();
				if(!empty($threadedit['lastposter']) && $threadedit['displayorder'] >=0 ){
					$result[$key]['edited']=1;
				}
			}
			//====END===
			// if($val['source'] == 0){//论坛采集内容状态
			// 	$temp = $bbs->table('x15_common_member')->where(array('uid'=>$val['authorid']))->field('status')->find();//查用户状态
			// 	if($temp['status'] != 0 ){
			// 		$result[$key]['ustatus'] = true;
			// 		$result[$key]['alt'] = '该贴用户被禁言';
			// 	}else{
			// 		$temp = $bbs->table('x15_forum_thread')->where(array('tid'=>$val['tid']))->field('displayorder')->find();//查帖子状态
			// 		if($temp['displayorder'] < 0 ){
			// 			$result[$key]['ustatus'] = true;
			// 			$result[$key]['alt'] = '该贴为不显示状态';
			// 		}
			// 	}
			// }
			if($val['img_path'] != '') {
				if($result[$key]['source']==0) $result[$key]['img_path'] = C('bbs_attach_url').$val['img_path'];
			}
			if($result[$key]['img_path1'])$result[$key]['img_path1'] = IMGATT_HOST.'/'.$val['img_path1'];
			if($result[$key]['img_path2'])$result[$key]['img_path2'] = IMGATT_HOST.'/'.$val['img_path2'];
			$thread_tags = $bbs_model -> table('zy_idtotagid') -> where(array('id'=>$val['id'])) ->select();
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
				$bbs_model->table('zy_collect')->where(array('id'=>$val['id']))->save(array('tagstatus'=>0));
				$result[$key]['tagstatus'] = '0';
				}
		} 
		$grouplist = $bbs_model->gettaggroup();
		//保留标签功能
		$show = $Page->show();
		if($_GET['lr']){
			$lr = $_GET['lr'];
			$filterurl .= '/lr/'.$lr;
		}else{
			$lr = 20;
		}
		if($_GET['p']){
			$p = $_GET['p'];
			$filterurl .= '/p/'.$p;
		}else{
			$p = 1;
		}
		//var_dump($Page);
		$Page->setConfig('header', '篇记录');
		$Page->setConfig('`first', '<<');
		$Page->setConfig('last', '>>');
		$this -> assign('source',$source);
		$this -> assign('taglist',$taglist);
		$this -> assign('searchtag',$searchtag);
		$this -> assign('tag_result',$tag_result);
		$this -> assign('grouplist',$grouplist);
		$this -> assign('bbs_model',$bbs_model);
		$this -> assign('lr',$lr);
		$this -> assign('p',$p);
        $this -> assign("page", $show);
		$this -> assign(array('result'=>$result,'th'=>$th,'filterurl'=>$filterurl,'rulelist'=>$rulelist,'cat'=>$cat));
		$this -> display();
	}
	
	function add_list(){
		$model = D('Zhiyoo.Zhiyoo');
		if(is_numeric($_GET['ids'])){
			$tittle = $model->table('zy_collect')->where(array('id'=>$_GET['ids']))->field('title')->find();
			$tittle = $tittle['title'];
		}
		else{
			$tittle = '批量标题';
		}
		//if(is_numeric($_GET['ids']))
		//$platform = $model-> query("select platform from zy_collect_ext where colid={$_GET['ids']} limit 1");//查询是否已配置平台
		$platform = $model-> query("select * from zy_schedule_platform where status=1");
		$position = $model-> query("select * from zy_schedule_position where status=1");
		$this -> assign("platform", $platform);
		$this -> assign("position", $position);
		$this -> assign("tittle", $tittle);
		$this->display();
	}
	
	function sub_thread(){
		$bbs_model = D('Zhiyoo.Zhiyoo');
		$zy_collect_extmodel = D('Zhiyoo.collect_ext');
		$zy_schedulemodel = D('Zhiyoo.schedule');
		$pos_confmodel = D('Zhiyoo.pos_conf');
		if(isset($_GET['ids'])) $ids = $_GET['ids']; else $this->error('请选择数据');
		$pp = is_array($_POST['pp']) ? $_POST['pp'] : $this->error('请选择位置');
/* 		!$_POST['starttime'] && $this -> error("时间不能为空");
		!$_POST['endtime'] && $this -> error("时间不能为空");
		$_POST['endtime'] < $_POST['starttime'] && $this -> error("开始时间须小于结束时间"); */
		$starttime = $_POST['starttime'] ? strtotime($_POST['starttime']) : 0;
		$endtime = $_POST['endtime'] ? strtotime($_POST['endtime']) : 0;
		$starttime > $endtime  && $this -> error("开始时间须小于结束时间");
		//===================移除未编辑外部内容================
		$bbs = D('Zhiyoo.bbs');
		$result = $bbs_model->query("select tid,id,authorid,author from zy_collect where id in ($ids)");
		$ids = explode(',',$ids);	
		foreach($result as $val){
			$tid = $val['tid'];
			if($val['source'] == 1){
				$result1 = $bbs ->table('x15_forum_thread')->where(array('tid'=>$tid))->field('typeid,lastposter,displayorder')->find();
				if($result1['displayorder'] <0 ){
					$undoids .= ','.$tid;
					$key = array_search($val['id'],$ids);
					unset($ids[$key]);
				}
			}
			if($val['source'] == 0){//论坛采集内容状态
				$temp = $bbs->table('x15_common_member')->where(array('uid'=>$val['authorid']))->field('status')->find();//查用户状态
				if($temp['status'] != 0 ){
					$this -> error("序号:{$val['id']} 用户:{$val['author']}被禁言");
				}else{
					$temp = $bbs->table('x15_forum_thread')->where(array('tid'=>$tid))->field('displayorder')->find();//查帖子状态
					if($temp['displayorder'] < 0 ){
						$this -> error("序号:{$val['id']} TID:{$tid}为不显示状态");
					}
				}
			}
		}
		
		$ids = implode(',',$ids);
		//=================
		if($ids == '')$this -> error("添加失败，未编辑的外部采集内容无法提交。");
		$err_res = $bbs_model -> table('zy_collect') ->where('id in ('.$ids.') and (substatus = 1 or substatus = -2) and status=1') -> select();//得到内容信息

		if($err_res){//分条插入排期表
			foreach ($err_res as $val){
				$data['tid'] = $val['tid'];
				$data['colid'] = $val['id'];
				$data['addschtime'] = time();//添加到排期表时间
				$data['starttime'] = $starttime;
				$data['endtime'] = $endtime;
				$secext = $zy_collect_extmodel -> table('zy_collect_ext') ->where("colid={$val['id']}") -> find();//查询是否已配置
				//获取ext表id，准备写入schedule表
				if(empty($secext)){//未配置
					$edata = array();
					$edata['colid'] = $val['id'];
					$edata['tid'] = $val['tid'];
					foreach($pp as $val2){					
						$val3 = explode('_',$val2);
						$edata['platform'] = $val3[0];
						$edata['position'] = $val3[1];
						$extautoid = $zy_collect_extmodel -> table('zy_collect_ext') -> data($edata) -> add();
						$data['eid'] = $extautoid;
						$autoid = $zy_schedulemodel -> table('zy_schedule') -> data($data) -> add();//获取schedule表ID，
				
						//更新ext表advid
						$bbs_model -> query("update zy_collect_ext set advid='$autoid' where id='$extautoid'");
						//插入位置表
						$pdata = array();
						$pdata['advid'] = $autoid;
						$pdata['tid'] = $val['tid'];
						$pdata['platform'] = $val3[0];
						$pdata['position'] = $val3[1];
						$pdata['source'] = 1;
						$pdata['level'] = 999;
						$pdata['column'] = '';
						$pdata['addtime'] = $data['addschtime'];
						$pdata['starttime'] = $starttime;;
						$pdata['endtime'] = $endtime;
						$pos_confmodel -> table('zy_pos_conf') -> data($pdata) -> add();
					}
				}else{
					
					$edata = array();//读取已配置信息
					$edata['colid'] = $val['id'];
					$edata['tid'] = $val['tid'];
					$edata['ext_title'] = $secext['ext_title'];
					$edata['jump_url'] = $secext['jump_url'];
					$edata['jump_status'] = $secext['jump_status'];
					$edata['img_path1'] = $secext['img_path1'];
					// $edata['img_path2'] = $secext['img_path2'];
					$edata['img_path4'] = $secext['img_path4'];
					$edata['description'] = $secext['description'];
					foreach($pp as $val2){					
						$val3 = explode('_',$val2);
						$edata['platform'] = $val3[0];
						$edata['position'] = $val3[1];

						// 如果平台勾选位置有轮播图，添加时把宣传标题改为轮播图标题，即把img_title赋值给ext_title
						if($edata['position'] == '3'){
							$edata['ext_title'] = $secext['img_title'];
						}else{
							$edata['ext_title'] = $secext['ext_title'];
						}
						//如果平台为PC端，则大图用PC宣传图,即当img_path5有值时给img_path2
						if($edata['platform'] == '1'){
						    $edata['img_path2'] = empty($secext['img_path5']) ? $secext['img_path2'] : $secext['img_path5']; 
						}
						if($edata['platform'] == '2'){
						    $edata['img_path2'] = $secext['img_path2']; 
						}
						//如果平台为市场或者什么值得玩，则大图用PC市场宣传图,即当img_path6有值时给img_path2
						if($edata['platform'] == '3' || $edata['platform'] == '4'){
						    $edata['img_path2'] = empty($secext['img_path6']) ? $secext['img_path2'] : $secext['img_path6']; 
						}

						$extautoid = $zy_collect_extmodel -> table('zy_collect_ext') -> data($edata) -> add();
						$data['eid'] = $extautoid;
						$autoid = $bbs_model -> table('zy_schedule') -> data($data) -> add();//获取schedule表ID，
						//更新ext表advid
						$bbs_model ->table('zy_collect_ext')->where(array('id'=>$extautoid))->save(array('advid'=>$autoid));
						//插入位置表
						$pdata = array();
						$pdata['advid'] = $autoid;
						$pdata['tid'] = $val['tid'];
						$pdata['platform'] = $val3[0];
						$pdata['position'] = $val3[1];
						$pdata['source'] = 1;
						$pdata['level'] = 999;
						$pdata['column'] = '';
						$pdata['addtime'] = $data['addschtime'];
						$pdata['starttime'] = $starttime;;
						$pdata['endtime'] = $endtime;
						$pos_confmodel -> table('zy_pos_conf') -> data($pdata) -> add();
					}
					$bbs_model ->table('zy_collect_ext')->where(array('id'=>$secext['id']))->delete();//重置平台
				}
			}
			
		}
		$collectmodel = D('Zhiyoo.collect');
		$result = $collectmodel -> table('zy_collect') -> where('id in ('.$ids.')') -> save(array('substatus' => 2));//改变内容状态为在排期中
		//echo $collectmodel -> getlastsql();
		//var_dump($result);exit;
		if($result !== false){
				$this -> writelog("智友内容管理-待用素材库-待用素材库列表 已经添加id为{$ids}内容至排期表","zy_collect",$ids,__ACTION__ ,"","add");
				$this -> assign("jumpUrl","/index.php/Zhiyoo/Data/result_list");
				if($undoids){
					$this -> success("除{$undoids}外其余添加成功！");
				}else{
					$this -> success("添加成功！");
				}
		}else{
			$this -> error("添加失败");
		}
			
		
	}
	
	function tag_list_show(){
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
		}
		
		$id = $_GET['id']?$_GET['id']:0;
		$this->assign('tid' ,$id);
		if($_GET['tags']) {
			$tags = explode('_',$_GET['tags']);
			$this->assign('tags' ,$tags);
		}
		$this->assign('source',$source);
		$this->assign('taginfo',$taginfo);
		$this->assign('tagpinfo',$tagpinfo);
		$this->assign('cat',$cat);
		$this->display();
	}
	
	function is_edited(){
		$model = D('Zhiyoo.Zhiyoo');
		$bbs = D('Zhiyoo.bbs');
		$ids = $_GET['ids'];
		$result = $model->query("select tid from zy_collect where id in ($ids) and source=1");
		foreach($result as $val){
			$tid = $val['tid'];
			$result = $bbs -> query("select typeid,lastposter from x15_forum_thread where tid='$tid' limit 1");
			if(empty($result[0]['typeid']) || empty($result[0]['lastposter']) ){
				echo $tid;exit;
			}
		}
		echo 1;
	}
	
}