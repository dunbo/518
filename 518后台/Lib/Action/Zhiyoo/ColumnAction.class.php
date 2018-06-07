<?php

class ColumnAction extends CommonAction{
    private $model;
    private $slavemodel;
    
    public function _initialize() {
        parent::_initialize();
		$this->model = D('Zhiyoo.Zhiyoo');
		$this->slavemodel = D('Zhiyoo.ZhiyooSlave');
	}
    
	//显示栏目列表 old 废弃不用
	function column_list(){
		$whereplat = array('status'=>array('egt',1));
        $platId = '';
		if (isset($_REQUEST['platformid']) and $_REQUEST['platformid']>0) {
			$platId = intval($_REQUEST['platformid']);
			$whereplat['platform'] = $platId;
		}
		$order_sql = (!$_GET['type']||$_GET['type']=='num')?  'status asc,rank asc': "{$_GET['type']} {$_GET['order']}";
		
		$bbs_modle = D('Zhiyoo.Zhiyoo');
		//获取筛选规则名称 2015-09-07 11:40
		$filterdata = $this->filter_rule();
		
		$tags = $bbs_modle->gettags();
		$whereplat['type'] = $_GET['specialtype'] == 'special_column' ?  '1' : '0';
		$result = $bbs_modle ->table('zy_column_conf')->where($whereplat)->order($order_sql) ->select();
		//$result2 = $bbs_modle ->table('zy_column_conf')->where('status > 1')->order($order_sql) ->select();
		//$result = array_merge($result1,$result2);
		//调取平台名称  2015-10-20:11:30
		$platformarr = $bbs_modle->table('zy_schedule_platform')->where(array('status'=>1))->select();
		foreach($platformarr as $platinfo){
			$platform[$platinfo['platform']] = $platinfo['platformname'];
		}
		
		foreach($result as $k=>$val){
			$count1 = $count2 = 0;
			$extsql1 = $extsql2 = '';
			$idarr = array();
			$platformid = $val['platform'];
			$result[$k]['platformname'] = $platform[$platformid];
			$colruleinfo = explode('_',$result[$k]['rule']);
			$threadtadid = $tduniquetag = $unsettag =array();
			//取出每个栏目的tagid,并且记录下有子标签的父标签tagid
			foreach($colruleinfo as $tagid){
				$tduniquetag[$tagid] = $threadtadid[$tagid] = $tagid;
				if($tags[$tagid]['parentid']>0) $unsettag[] = $tags[$tagid]['parentid'];
			
			}
			//删除有子标签的父标签的tagid
			foreach($unsettag as $delid){unset($tduniquetag[$delid]);}
			
			foreach($tduniquetag as $tag){
				if($tags[$tag]['status'] == -1) continue;
				$columnrule[$result[$k]['cid']][$tags[$tag]['group']]['name'] = $tags[$tag]['groupname'];	
				$columnrule[$result[$k]['cid']][$tags[$tag]['group']]['tag_arr'][] = $tag;
			}

			//即时搜索线上数据统计线上的数量 start
			$extsql1 = '('.implode(',',$colruleinfo).')';
			$searchcnt = count($colruleinfo);
			//搜索符合条件的标签
			//修改规则  2015-09-07 11:40
			if ($result[$k]['filter']==2) {
				$idres = $bbs_modle->query("select id,count(*) as cnt from zy_idtotagid where tagid in {$extsql1} group by id ");
				$result[$k]['filterusr'] = "content_list_filter";
			}else {
				$idres = $bbs_modle->query("select * from (select id,count(*) as cnt from zy_idtotagid where tagid in {$extsql1} group by id ) as A  where A.cnt ={$searchcnt}");
				$result[$k]['filterusr'] = "content_list";
			}
// 			$idres = $bbs_modle->query("select * from (select id,count(*) as cnt from zy_idtotagid where tagid in {$extsql1} group by id ) as A  where A.cnt ={$searchcnt}");
			foreach($idres as $idinfo){
				$idarr[] = $idinfo['id'];
			}
			
			$now =time();
			$count1 = $count2 = 0;$aids=array();$cntsql='';
            //线上内容查找
			if(!empty($idarr)){
				$extsql2 =  '('.implode(',',$idarr).')';
				$result1 = $bbs_modle->table('zy_schedule as zs')->field('zs.id')->join('left join zy_collect_ext as zce on zs.id=zce.advid')->where("zce.platform=$platformid and zs.starttime<={$now} and zs.endtime>={$now} and zce.position = 1 and zs.status=0 and zs.colid in {$extsql2}")->select();
				$count1 = count($result1);
				foreach($result1 as $olaid){
					$aids[] =  $olaid['id'];
				}
				if(!empty($aids)) $cntsql = 'and zc.aid not in ('.implode(',',$aids).')';
			}
            //手动添加的统计 
			$count2 = $bbs_modle->table('zy_column_content as zc')->join('left join zy_schedule as zs on zc.aid=zs.id')->join('left join zy_collect_ext as zce on zc.aid=zce.advid')->where("zce.platform=$platformid and zc.source = 1 and zs.endtime>={$now} and zs.status=0 and zs.starttime<={$now} and zc.cid={$result[$k]['cid']} $cntsql")->count();
			$result[$k]['num'] = $count1 + $count2;
			//即时搜索线上数据统计线上的数量 end
			
			//$result[$k]['num'] = $bbs_modle->table('zy_column_content')->where("cid={$result[$k]['cid']}")->count();
			
			
			$numrank[$k] = $result[$k]['num'];
		}
		if($_GET['type']=='num'&& $_GET['order']=='asc') asort($numrank);
		if($_GET['type']=='num'&& $_GET['order']=='desc') arsort($numrank);
		if($_GET['type']=='num'){
			foreach($numrank as $key=>$val){
				$numrank[$key] = $result[$key];
			}
			$result = $numrank;
		}
		
		if($_GET['edit_rank']) {
			$edit_rank = 'edit_rank/1/';
			$this->assign('edit_rank',$edit_rank);
		}
		$th[$_GET['type']] =  $_GET['order'] == 'asc' ? 'desc' : 'asc';
		$this->assign('result',$result);
		$this->assign('th',$th);
		$this->assign('ruleinfo',$columnrule);
		$this->assign('tags',$tags);
		$this->assign('platId',$platId);
		$this->assign('platform',$platformarr);
		$this -> display();
	}
	
	function special_column(){
		$_GET['specialtype'] = 'special_column';
		$this->showColumn();
	}
	// 添加和编辑栏目
	function add_column_sub(){
		if(!$_POST['tags']) $this->error('没有选择任何标签');
		$_POST['colname'] = trim($_POST['colname']);
		if($_POST['colname'] == '请输入栏目名称'||$_POST['colname'] == '请输入专题名称'||!$_POST['colname']) $this->error('请输入正确的名称');
		//添加相应规则 2015-09-07 11:40
		if (!$_POST['filter']) {$this->error('请选择相应的规则');} else {$data['filter'] = intval($_POST['filter']);}
		//添加平台	2015-10-20 15:00
		if (!$_POST['platform']) {$this->error('请选择相应的平台');} else {$data['platform'] = $platFormId = intval($_POST['platform']);}
		
		$bbs_modle = D('Zhiyoo.Zhiyoo');
		$tags = array_values($_POST['tags']);
		sort($tags);
		$tagstr = implode('_',$tags);
		$data['rule'] = $tagstr;
		$data['name'] = $_POST['colname'];
		$data['common'] = $_POST['common'];
		$wherespecialtype = intval($_GET['specialtype']) === 1  ?  '1' : '0';//栏目专题区分添加 1专题 0 栏目
		//添加封面图 2015-10-20 15:30
		$img_path ='';
		if($_FILES['p_logo']['size']>0||$_FILES['p_logo']['size']>0){
			$imginfo = $this->_upload();
			if($imginfo['image'][0]['url']) $img_path = $imginfo['image'][0]['url'];
		}
		if ($img_path!='') {
			$data['cover_img'] = $img_path;
		}

		if($_POST['cid']){
			$cid = $_POST['cid'];
			$res = $bbs_modle -> table('zy_column_conf')->where("(name='{$data['name']}' or (rule='{$data['rule']}' and filter='{$data['filter']}')) and cid <> {$cid} and status >= 1 and type = ".$wherespecialtype." and platform=".$platFormId)->find();
			if($res) if($wherespecialtype)$this->error('专题筛选标签或者专题名称已存在,修改失败'); 
					else $this->error('栏目筛选标签或者栏目名称已存在,修改失败');
			$rule = $bbs_modle->table('zy_column_conf')->field('rule,filter,platform')-> where(array('cid'=>$cid))->find();
			//如果更换平台删除热门标签里的该栏目
			$upcount = $bbs_modle->table('zy_hot_tags')->where("cid={$cid} and status>0")->count();
			if ($rule['platform']!=$data['platform'] and $upcount) {
				$updata = array('status'=>'-1');
				$retup = $bbs_modle->table('zy_hot_tags')->where(array('cid'=>$cid))->save($updata);
				$this -> writelog("智友内容管理-内容标签/栏目-热门标签管理删除cid为{$cid}的栏目","zy_hot_tags",$cid,__ACTION__ ,"","del");
			}
			
			//如果修改栏目的规则，那么就删除栏目里面的主题，除了手动添加的主题
			//添加修改规则删除相应CID的数据  || $rule['filter']!=$data['filter'] 2015-09-07 15：00
			if($rule['rule'] != $data['rule'] || $rule['filter']!=$data['filter']) $bbs_modle->table('zy_column_content')->where(array('cid'=>$cid,'source'=>0))->delete();
			$bbs_modle->table('zy_column_conf') ->where(array('cid'=>$cid))->save($data);
			
		
			$this -> writelog("智友内容管理-内容标签/栏目-栏目管理 已编辑cid为{$cid}的栏目/专题","zy_column_conf",$cid,__ACTION__ ,"","edit");
			// $this -> assign('jumpUrl',"/index.php/Zhiyoo/Column/column_list");
			$this -> success("编辑成功");
			
		}else{
			$res = $bbs_modle -> table('zy_column_conf')->where("(name='{$data['name']}' or (rule='{$data['rule']}' and filter='{$data['filter']}')) and status >= 1 and type = ".$wherespecialtype." and platform=".$platFormId)->find();
			if($res) if($wherespecialtype)$this->error('专题筛选标签或者专题名称已存在,添加失败');
					else $this->error('栏目筛选标签或者栏目名称已存在,添加失败');
			$data['status'] = 1;
			$max = $bbs_modle->table('zy_column_conf')->field('max(rank) as maxrank')->where('type = '.$wherespecialtype)->find();
			$maxrank = $max['maxrank'] ? $max['maxrank']:0;
			$data['rank'] = $maxrank + 1;
			$data['type'] = $wherespecialtype;
			$res = $bbs_modle->table('zy_column_conf') ->add($data);
			if($res){
				$this -> writelog("智友内容管理-内容标签/栏目-栏目管理 已添加cid为{$res}的栏目/专题","zy_column_conf",$res,__ACTION__ ,"","add");
				// $this -> assign('jumpUrl',"/index.php/Zhiyoo/Column/column_list");
				$this -> success("添加成功");
			}else{
				$this -> error("添加失败");
			}
		}
	
		
	}
	
	//显示添加和编辑栏目的模版		
	function add_column(){
		$bbs_modle = D('Zhiyoo.Zhiyoo');
		//获取筛选规则名称 2015-09-07 11:40
		$filterdata = $this->filter_rule();
		
		$result = $bbs_modle ->table('zy_tagsgroup')->field(array('groupid','groupname'))->where('status = 1')->select();
		
		foreach($result as $val){
			$cat[$val['groupid']] = $val['groupname'];
		}
		
		$result = $bbs_modle ->table('zy_tags')->field(array('tagid','tagname','group','parentid'))->where('status = 1')->select();
		$stoptag = $bbs_modle ->table('zy_tags')->field('tagid,tagname')->where('status = 0')->select();
		foreach($result as $val){
			$taginfo[$val['group']][] = $val;
			$tagpinfo[$val['parentid']][] = $val;
			$taglist[$val['tagid']] = $val;
		}
	
		if($_GET['cid']){
			$cid = $_GET['cid'];
			$column = $bbs_modle ->table('zy_column_conf')->where(array('cid'=>$cid))->select();
			$rules = explode('_',$column[0]['rule']);
			$stoprule = $this->checktag($rules,$stoptag);
			$stoprule = implode('，',$stoprule);
			$this->assign('stoprule',$stoprule);
			$this->assign('tags',$rules);
			$this->assign('cid',$_GET['cid']);
			$this->assign('colunm',$column);
		}
		//调取平台内容 2015-10-20 11:50
		$platformarr = $bbs_modle->table('zy_schedule_platform')->where(array('status'=>1))->select();
		$this->assign('platform',$platformarr);
		$this->assign('taginfo',$taginfo);
		$this->assign('tagpinfo',$tagpinfo);
		$this->assign('taglist',$taglist);
		$this->assign('cat',$cat);
		$this->display();
	}
	
	
	
	// 编辑栏目的排序
	function edit_rank(){
		//添加当前平台编辑后调转到当前平台 2015-10-21 10:50
		$getPlatformid = intval($_GET['platformid']);
		$bbs_modle = D('Zhiyoo.Zhiyoo');
		foreach($_POST['rank'] as $cid =>$rank){
			$bbs_modle->table('zy_column_conf')->where(array('cid'=>$cid))->save(array('rank'=>$rank));	
			$this -> writelog("智友内容管理-内容标签/栏目-栏目管理 已编辑cid为{$cid}的栏目/专题的优先级为".$rank,"zy_column_conf",$cid,__ACTION__ ,"","edit");
		}
		if($_GET['specialtype'] == 'special_column') $this -> assign('jumpUrl',"/index.php/Zhiyoo/Column/special_column/platformid/".$getPlatformid);
		else $this -> assign('jumpUrl',"/index.php/Zhiyoo/Column/showColumn/platformid/".$getPlatformid);
		$this -> success("编辑优先级成功");
	}
	
	//编辑栏目内容的排序
	function edit_content_rank(){
		$cid = $_GET['cid'];
		$bbs_modle = D('Zhiyoo.Zhiyoo');
		foreach($_POST['rank'] as $aid =>$rank){
			$bbs_modle->table('zy_column_content')->where(array('aid'=>$aid,'cid'=>$cid))->save(array('rank'=>$rank));	
			$this -> writelog("智友内容管理-内容标签/栏目-栏目管理 已编辑aid为{$aid}的内容优先级为".$rank,"zy_column_content",$aid,__ACTION__ ,"","edit");
		}
		//修改跳转地址 2015-09-08 15:00
		$filterurl = $this->filter_rule_url($cid);
		$this -> assign('jumpUrl',"/index.php/Zhiyoo/Column/{$filterurl}/cid/{$cid}");
// 		$this -> assign('jumpUrl',"/index.php/Zhiyoo/Column/content_list/cid/{$cid}");
		$this -> success("编辑优先级成功");
	}
	
	// 手动为栏目添加内容
	function add_content(){
		$data['cid'] = $_POST['cid'];
		$data['tid'] = $_POST['addtid'];
		$data['rank'] = $_POST['addrank'];
		$data['comment'] = '人工添加';
		$data['source'] = '1';
		
		$bbs_modle = D('Zhiyoo.Zhiyoo');
		$now = time();
		$result = $bbs_modle -> table('zy_column_content') ->where(array('tid'=>$data['tid'],'cid'=>$data['cid']))->find(); 
		if(!empty($result)) $this->error('该栏目下tid已存在');
		$result = $bbs_modle -> table('zy_schedule') ->field('id,colid,addschtime')-> where("tid = {$data['tid']} and status = 0 and starttime<={$now} and endtime>={$now}")->select(); 
		if(empty($result))	$this->error('所添加的tid不在已上线状态,添加失败');
		
		
		foreach($result as $val){
			$data['id'] = $val['colid'];
			$data['aid'] = $val['id'];
			$data['addschtime'] = $val['addschtime'];
			$posi = $bbs_modle -> table('zy_collect_ext') ->where(array('advid'=>$val['id'],'position'=>1))->find(); 
			if(empty($posi)) {
				$nopois[] = $data['aid']; 
				continue;
			}
			$goposi[] = $data['aid'];
			$res = $bbs_modle->table('zy_column_content')->add($data);
			
		}
		if($goposi) {
			$this -> writelog("智友内容管理-内容标签/栏目-栏目管理 已添加id为".implode(',',$goposi)."的内容到{$data['cid']}的栏目","zy_column_content",$res,__ACTION__ ,"","add");
			$tips = "手动添加内容成功";
			$this -> success($tips);
		}
		
		if(!empty($nopois)) {
			$tips = "所添加的tid为{$data['tid']}广告id为".implode(',',$nopois)."的位置不是无,添加失败。";
			$this->error($tips);
		}	
		
	}
	
	// 显示栏目的内容
	function content_list(){
		!defined('BBSLUNTAN_HOST') && define('BBSLUNTAN_HOST','http://bbs.zhiyoo.com/forum.php?mod=post&action=edit');
		$cid = $_GET['cid'];
		$_GET['type'] = !$_GET['type'] ? 'rank' : $_GET['type'];
		$_GET['order'] = !$_GET['order'] ? 'asc' : $_GET['order'];
		switch($_GET['type']){
			case 'rank':
				$order_sql = 'cc.rank ';
				break;
			case 'id':
				$order_sql = 'cc.id ';
				break;
			case 'tid':
				$order_sql = 'cc.tid ';
				break;
			case 'author':
				$order_sql = 'zc.author ';
				break;
			case 'title':
				$order_sql = 'zc.title ';
				break;
			case 'advid':
				$order_sql = 'zs.id ';
				break;
			case 'addschtime':
				$order_sql = 'zs.addschtime ';
				break;
			case 'starttime':
				$order_sql = 'zs.starttime ';
				break;
			case 'endtime':
				$order_sql = 'zs.endtime ';
				break;
			case 'views':
				$order_sql = 'zc.views ';
				break;
			case 'replies':
				$order_sql = 'zc.replies ';
				break;
		}
		$order_sql =$order_sql.$_GET['order'].",zs.addschtime desc";
		$th[$_GET['type']] =  $_GET['order'] == 'asc' ? 'desc' : 'asc';
		$bbs_modle = D('Zhiyoo.Zhiyoo');
		
		//即时清理过期数据并且添加已上线的符合栏目规则的数据 start
		//清理过期数据
		$now = time();
		$result = $bbs_modle->table('zy_column_content as zc')->field('zc.aid')->join('left join zy_schedule as zs on zc.aid=zs.id')->where("(zs.endtime<={$now} or zs.starttime >={$now} or zs.status=-1) and zc.cid={$cid}")->order("zc.rank ASC,zc.addschtime DESC")->select();
		foreach($result as $val){
			$delaid[] = $val['aid'];
		}
		if(!empty($delaid)) {
			$bbs_modle->table('zy_column_content')->where('aid in('.implode(',',$delaid).')')->delete();
			$this->writelog("智友内容管理-内容标签/栏目-栏目管理 自动删除栏目过期数据aid为".implode(',',$delaid),"zy_column_content",implode(',',$delaid),__ACTION__ ,"","del");
		}
		//查询和栏目规则符合的所有的内容
		$rule = $bbs_modle->table('zy_column_conf')->field('rule,platform')->where(array('cid'=>$cid))->find();
		$ruleinfo = explode('_',$rule['rule']);
		$extsql = '('.implode(',',$ruleinfo).')';
		$searchcnt = count($ruleinfo);
		$platformid = $rule['platform'];
		$idarr = array();
		
		
		//筛选符合栏目标签规则的原始数据 
		$idres = $bbs_modle->query("select * from (select id,count(*) as cnt from zy_idtotagid where tagid in {$extsql} group by id ) as A  where A.cnt ={$searchcnt}");
// 		$idres = $bbs_modle->query("select id,count(*) as cnt from zy_idtotagid where tagid in {$extsql} group by id ");
		foreach($idres as $idinfo){
			$idarr[] = $idinfo['id'];
		}
		$extsql =  '('.implode(',',$idarr).')';
		$result = $bbs_modle->table('zy_schedule as zs')->field('zs.addschtime,zs.id,zs.colid,zs.tid')->join('left join  zy_collect_ext as zce on zs.id = zce.advid')->where("zs.starttime<={$now} and zs.endtime>={$now} and zs.status=0 and zce.position=1 and zs.colid in {$extsql} and zce.platform=$platformid")->select();
		//如果内容不在栏目内容表里面，则添加
		foreach($result as $val){
			$res = $bbs_modle->table('zy_column_content')->where(array('cid'=>$cid,'aid'=>$val['id']))->find();
			if(!$res){
				$bbs_modle->table('zy_column_content')->add(array('cid'=>$cid,'id'=>$val['colid'],'aid'=>$val['id'],'tid'=>$val['tid'],'addschtime'=>$val['addschtime']));
				$this->writelog("智友内容管理-内容标签/栏目-栏目管理 自动给id为{$cid}的栏目添加内容aid为{$val['id']}","zy_column_content",$cid,__ACTION__ ,"","add");
			}
		}
		//即时清理过期数据并且添加已上线的符合栏目规则的数据 end
		
		import("@.ORG.Page");
		$param = http_build_query($_GET);
		$count = $bbs_modle -> table('zy_column_content as cc') ->join('left join zy_collect_ext as zce on cc.aid = zce.advid')->where(array('cc.cid'=>$cid,'zce.platform'=>$platformid))->count();
		$Page = new Page($count, 20, $param);

		$con = $bbs_modle -> table('zy_column_content as cc') ->field('cc.id,cc.rank,cc.aid,cc.tid,cc.comment,zc.url,zc.fid,zc.pid,zc.author,zc.views,zc.replies,zs.addschtime,zs.starttime,zs.endtime,zce.ext_title,zce.platform,zce.img_path1,zce.img_path2,zce.description')-> join('left join zy_collect as zc on cc.id = zc.id left join zy_schedule as zs on  cc.aid = zs.id left join zy_collect_ext as zce on cc.aid = zce.advid')->where(array('cc.cid'=>$cid,'zce.platform'=>$platformid))->order($order_sql)->limit($Page->firstRow . ',' . $Page->listRows)-> select();
		
		$platformarr = $bbs_modle->table('zy_schedule_platform')->where(array('status'=>1))->select();
		foreach($platformarr as $platinfo){ 
		$platform[$platinfo['platform']] = $platinfo['platformname']; 
		}
		$platform[0] = '全部'; 
		$taglist = $bbs_modle->gettags();
		foreach($con as $key => $val){
			if($val['rank'] == 9999) $con[$key]['rank']=''; 
			$con[$key]['platform'] = $platform[$val['platform']];
			$thread_tags = $bbs_modle -> table('zy_idtotagid') -> where(array('id'=>$val['id'])) ->select();
			$threadtadid = $tduniquetag = $unsettag = array();
			//取出每个主题的tagid,并且记录下有子标签的父标签tagid
			foreach($thread_tags as $tagidinfo){
				$tduniquetag[$tagidinfo['tagid']] = $threadtadid[$tagidinfo['tagid']] = $tagidinfo['tagid'];
				if($taglist[$tagidinfo['tagid']]['parentid']>0) $unsettag[] = $taglist[$tagidinfo['tagid']]['parentid'];
			}
			//删除有子标签的父标签的tagid，并且把全部的该主题全部的tagid保存成url
			foreach($unsettag as $delid){unset($tduniquetag[$delid]);}
			foreach($tduniquetag as $tag){
				$tag_result[$key][$taglist[$tag]['group']]['name'] = $taglist[$tag]['groupname'];	
				$tag_result[$key][$taglist[$tag]['group']]['tag_arr'][] = $tag;
			}
			
		} 
	
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
		
        $model = D('Zhiyoo.ColumnConfTags');
        $alltags = $model->selectall($cid);
		$this -> assign('alltags',$alltags);
        
		$Page->setConfig('header', '篇记录');
		$Page->setConfig('`first', '<<');
		$Page->setConfig('last', '>>');
		$this -> assign('lr',$lr);
		$this -> assign('p',$p);
        $this -> assign("page", $show);
		$this->assign('th',$th);
		$this -> assign('taglist',$taglist);
		$this -> assign('con',$con);
		$this -> assign('tag_result',$tag_result);
		$this->assign('bbs_model',$bbs_modle);
		$this ->assign('cid',$cid);
		$this->display();
	}
	
	/*
	 * content_list_filter()
	 * 栏目管理查看编辑规则（规则2:选择多个标签，内容只要含有其中任意一个，则属于该栏目 ）
	 * 2015-09-07 11:30
	 */
	function content_list_filter(){
		!defined('BBSLUNTAN_HOST') && define('BBSLUNTAN_HOST','http://bbs.zhiyoo.com/forum.php?mod=post&action=edit');
		$cid = $_GET['cid'];
		$_GET['type'] = !$_GET['type'] ? 'rank' : $_GET['type'];
		$_GET['order'] = !$_GET['order'] ? 'asc' : $_GET['order'];
		switch($_GET['type']){
			case 'rank':
				$order_sql = 'cc.rank ';
				break;
			case 'id':
				$order_sql = 'cc.id ';
				break;
			case 'tid':
				$order_sql = 'cc.tid ';
				break;
			case 'author':
				$order_sql = 'zc.author ';
				break;
			case 'title':
				$order_sql = 'zc.title ';
				break;
			case 'advid':
				$order_sql = 'zs.id ';
				break;
			case 'addschtime':
				$order_sql = 'zs.addschtime ';
				break;
			case 'starttime':
				$order_sql = 'zs.starttime ';
				break;
			case 'endtime':
				$order_sql = 'zs.endtime ';
				break;
			case 'views':
				$order_sql = 'zc.views ';
				break;
			case 'replies':
				$order_sql = 'zc.replies ';
				break;
		}
		$order_sql =$order_sql.$_GET['order'].",zs.starttime desc";
		$th[$_GET['type']] =  $_GET['order'] == 'asc' ? 'desc' : 'asc';
		$bbs_modle = D('Zhiyoo.Zhiyoo');
	
		//即时清理过期数据并且添加已上线的符合栏目规则的数据 start
		//清理过期数据
		$now = time();
		$result = $bbs_modle->table('zy_column_content as zc')->field('zc.aid')->join('left join zy_schedule as zs on zc.aid=zs.id')->where("(zs.endtime<={$now} or zs.starttime >={$now} or zs.status=-1) and zc.cid={$cid}")->order("zc.rank ASC,zc.addschtime DESC")->select();
		foreach($result as $val){
			$delaid[] = $val['aid'];
		}
		if(!empty($delaid)) {
			$bbs_modle->table('zy_column_content')->where('aid in('.implode(',',$delaid).')')->delete();
			$this->writelog("智友内容管理-内容标签/栏目-栏目管理 自动删除栏目过期数据aid为".implode(',',$delaid),"zy_column_content",implode(',',$delaid),__ACTION__ ,"","del");
		}
		//查询和栏目规则符合的所有的内容
		$rule = $bbs_modle->table('zy_column_conf')->field('rule,platform')->where(array('cid'=>$cid))->find();
		$ruleinfo = explode('_',$rule['rule']);
		$extsql = '('.implode(',',$ruleinfo).')';
		$searchcnt = count($ruleinfo);
		$platformid = $rule['platform'];
		$idarr = array();
	
	
		//筛选符合栏目标签规则的原始数据
		// 		$idres = $bbs_modle->query("select * from (select id,count(*) as cnt from zy_idtotagid where tagid in {$extsql} group by id ) as A  where A.cnt ={$searchcnt}");
		$idres = $bbs_modle->query("select id,count(*) as cnt from zy_idtotagid where tagid in {$extsql} group by id ");
		foreach($idres as $idinfo){
			$idarr[] = $idinfo['id'];
		}
		$extsql =  '('.implode(',',$idarr).')';
		$result = $bbs_modle->table('zy_schedule as zs')->field('zs.addschtime,zs.id,zs.colid,zs.tid')->join('left join  zy_collect_ext as zce on zs.id = zce.advid')->where("zs.starttime<={$now} and zs.endtime>={$now} and zs.status=0 and zce.position=1 and zs.colid in {$extsql} and zce.platform=$platformid")->select();
		//如果内容不在栏目内容表里面，则添加
		foreach($result as $val){
			$res = $bbs_modle->table('zy_column_content')->where(array('cid'=>$cid,'aid'=>$val['id']))->find();
			if(!$res){
				$bbs_modle->table('zy_column_content')->add(array('cid'=>$cid,'id'=>$val['colid'],'aid'=>$val['id'],'tid'=>$val['tid'],'addschtime'=>$val['addschtime']));
				$this->writelog("智友内容管理-内容标签/栏目-栏目管理 自动给id为{$cid}的栏目添加内容aid为{$val['id']}","zy_column_content",$cid,__ACTION__ ,"","add");
			} 
		}
		//即时清理过期数据并且添加已上线的符合栏目规则的数据 end
	
		import("@.ORG.Page");
		$param = http_build_query($_GET);
		$count = $bbs_modle -> table('zy_column_content as cc') ->join('left join zy_collect_ext as zce on cc.aid = zce.advid')->where(array('cc.cid'=>$cid,'zce.platform'=>$platformid))->count();
		$Page = new Page($count, 20, $param);
	
		$con = $bbs_modle -> table('zy_column_content as cc') ->field('cc.id,cc.rank,cc.aid,cc.tid,cc.comment,zc.url,zc.fid,zc.pid,zc.author,zc.views,zc.replies,zs.addschtime,zs.starttime,zs.endtime,zce.ext_title,zce.platform,zce.img_path1,zce.img_path2,zce.description')-> join('left join zy_collect as zc on cc.id = zc.id left join zy_schedule as zs on  cc.aid = zs.id left join zy_collect_ext as zce on cc.aid = zce.advid')->where(array('cc.cid'=>$cid,'zce.platform'=>$platformid))->order($order_sql)->limit($Page->firstRow . ',' . $Page->listRows)-> select();
	
		$platformarr = $bbs_modle->table('zy_schedule_platform')->where(array('status'=>1))->select();
		foreach($platformarr as $platinfo){
			$platform[$platinfo['platform']] = $platinfo['platformname'];
		}
		$platform[0] = '全部';
		$taglist = $bbs_modle->gettags();
		foreach($con as $key => $val){
			if($val['rank'] == 9999) $con[$key]['rank']='';
			$con[$key]['platform'] = $platform[$val['platform']];
			$thread_tags = $bbs_modle -> table('zy_idtotagid') -> where(array('id'=>$val['id'])) ->select();
			$threadtadid = $tduniquetag = $unsettag = array();
			//取出每个主题的tagid,并且记录下有子标签的父标签tagid
			foreach($thread_tags as $tagidinfo){
				$tduniquetag[$tagidinfo['tagid']] = $threadtadid[$tagidinfo['tagid']] = $tagidinfo['tagid'];
				if($taglist[$tagidinfo['tagid']]['parentid']>0) $unsettag[] = $taglist[$tagidinfo['tagid']]['parentid'];
			}
			//删除有子标签的父标签的tagid，并且把全部的该主题全部的tagid保存成url
			foreach($unsettag as $delid){unset($tduniquetag[$delid]);}
			foreach($tduniquetag as $tag){
				$tag_result[$key][$taglist[$tag]['group']]['name'] = $taglist[$tag]['groupname'];
				$tag_result[$key][$taglist[$tag]['group']]['tag_arr'][] = $tag;
			}
	
		}
	
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
		
        $model = D('Zhiyoo.ColumnConfTags');
        $alltags = $model->selectall($cid);
		$this -> assign('alltags',$alltags);
        
	
		$Page->setConfig('header', '篇记录');
		$Page->setConfig('`first', '<<');
		$Page->setConfig('last', '>>');
		$this -> assign('lr',$lr);
		$this -> assign('p',$p);
		$this -> assign("page", $show);
		$this->assign('th',$th);
		$this -> assign('taglist',$taglist);
		$this -> assign('con',$con);
		$this -> assign('tag_result',$tag_result);
		$this->assign('bbs_model',$bbs_modle);
		$this ->assign('cid',$cid);
		$this ->assign('from','content_list_filter');
		$this->display('content_list');
	}
	
	//展示手动添加内容的模版
	function add_content_show(){	
		$this ->assign('cid',$_GET['cid']);
		$this->display();
	}
	
	//删除栏目，同时删除栏目下的所有内容
	function del_column(){
		$cid = $_GET['cid'];
		$bbs_modle = D('Zhiyoo.Zhiyoo');
		//删除栏目同时删除该热门标签 2015-10-21 16:15
		$upcount = $bbs_modle->table('zy_hot_tags')->where("cid={$cid} and status>0")->count();
		if ($upcount) {
			$updata = array('status'=>'-1');
			$retup = $bbs_modle->table('zy_hot_tags')->where(array('cid'=>$cid))->save($updata);
			$this -> writelog("智友内容管理-内容标签/栏目-热门标签管理删除cid为{$cid}的栏目","zy_column_content",$cid,__ACTION__ ,"","add");
		}
		
		$bbs_modle->table('zy_column_content')->where(array('cid'=>$cid))->delete();
		$result = $bbs_modle->table('zy_column_conf')->where(array('cid'=>$cid))->save(array('status'=>0));
		$bbs_modle->table('zy_pos_conf')->where(array('column'=>$cid))->save(array('column'=>0));
		if($result){
			$this->writelog("智友内容管理-内容标签/栏目-栏目管理 删除cid为{$cid}的栏目/专题","zy_column_content",$cid,__ACTION__ ,"","add");
			// $this->assign('jumpUrl',"/index.php/Zhiyoo/Column/column_list/");
			$this->success('删除栏目成功');
		}else{
			$this->error('删除栏目失败');
		}
	}
	
	//修改栏目状态
	function changestatus(){
		$cid = $_GET['cid'];
		$status = $_GET['status'];
		$bbs_modle = D('Zhiyoo.Zhiyoo');
		$res = $bbs_modle -> table('zy_column_conf') -> where(array('cid'=>$cid)) ->save(array('status'=>$status));
		$tips = '';
		if($status ==1)  $tips = '启用';
		if($status ==2){
			//如果更换栏目使用状态为停用将热门更改为停用 2015-10-21 18:25
			$upcount = $bbs_modle->table('zy_hot_tags')->where(array('cid'=>$cid,'status'=>1))->count();
			if ($upcount) {
				$updata = array('status'=>'2');
				$retup = $bbs_modle->table('zy_hot_tags')->where(array('cid'=>$cid))->save($updata);
				$this -> writelog("智友内容管理-内容标签/栏目-热门标签管理停用cid为{$cid}的栏目","zy_hot_tags",$cid,__ACTION__ ,"","edit");
			}
			$tips = '隐藏';
		}
		if($res){
			// $this -> assign('jumpUrl',"/index.php/Zhiyoo/Column/column_list/");
			$this->success("{$tips}栏目成功");
			$this->writelog("智友内容管理-内容标签/栏目-栏目管理 修改cid为{$cid}的状态值为{$status}","zy_column_content",$cid,__ACTION__ ,"","edit");
		}else{
			$this->error('修改状态失败');
			
		}
	}
	
	function overonline(){
		$aid = $_GET['advid'];
		$cid = $_GET['cid'];
		if(!$aid) $this -> error('停用失败');
		$bbs_modle = D('Zhiyoo.Zhiyoo');
		$bbs_modle -> table('zy_schedule') -> where(array('id'=>$aid))->save(array('endtime'=>time()));
		$res = $bbs_modle -> table('zy_column_content') -> where(array('aid'=>$aid,'cid'=>$cid))->delete();
		if($res) {
			//修改跳转地址 2015-09-08 15:00
			$filterurl = $this->filter_rule_url($cid);
			$this->assign('jumpUrl',"/index.php/Zhiyoo/Column/{$filterurl}/cid/{$cid}");
// 			$this->assign('jumpUrl',"/index.php/Zhiyoo/Column/content_list/cid/{$cid}");
			$this->success('停用成功');
		}else{
			$this->error('停用失败');
		}
		
	}
	/*
	 * filter_rule()
	 * 获取栏目管理规则
	 * 2015-09-07 11:30
	 */
	function filter_rule(){
		$filter_tabal = 'zy_colimn_filter_rule';
		$bbs_modle = D('Zhiyoo.Zhiyoo');
	
		$filter_data = $bbs_modle -> table($filter_tabal)->where('status=1')->select();
	
		$filterdata = array();
		foreach ($filter_data as $fval){
			$filterdata[$fval['id']] = $fval['description'];
		}
		// 		return $filterdata;
		$this->assign('filterdata',$filterdata);
	
	}
	/*
	 * filter_rule_url()
	 * 返回当前栏目跳模板
	 * 2015-09-08 15:00
	 */
	function filter_rule_url($cid){
		$bbs_modle = D('Zhiyoo.Zhiyoo');
		$column = $bbs_modle ->table('zy_column_conf')->field('filter')->where(array('cid'=>$cid))->find();
		if ($column['filter']==2) {
			$filterurl = "content_list_filter";
		}else{
			$filterurl = "content_list";
		}
		return $filterurl;
	}
	protected function checktag($rule,$stop){
		foreach($stop as $stopid){
			if(in_array($stopid['tagid'],$rule)) $stoprule[] = $stopid['tagname'];
		}
		return $stoprule;
	}
	
	/*
	 * 上传图片
	 * 2015-10-20 15:30
	 */
	protected function _upload(){
		$img_dir = UPLOAD_PATH ."/img/". date('Ym/d/', time());
		$config['multi_config']['p_logo'] = array(
				'savepath' => $img_dir,
				'saveRule' => 'getmsec',
		);
		$maxsize = 1024*20;
		if($_FILES['p_logo']['size'] > $maxsize){
			$config['multi_config']['p_logo']['img_p_size'] = $maxsize ;
			$config['multi_config']['p_logo']['img_p_width'] = 280;
		}else{
			$config['multi_config']['p_logo']['enable_resize'] = false;
		}
		$list = $this->_uploadapk(0, $config);
		if(!$list){
			$this -> error("生成图片失败");
		}
		return $list;
	}
	
    function edit_tags(){
        $model = D('Zhiyoo.ColumnConfTags');
        $model->refresh($_GET['cid']);
        $alltags = $model->selectall($_GET['cid']);
        $rank = false;
        foreach($alltags as $val){
            if($val['status'] == 1){
                $rank = true;
                break;
            }
        }
        // $cr = $model->getColumnRule($_GET['cid']);
		$this -> assign('alltags',$alltags);
		$this -> assign('rank',$rank);
		// $this -> assign('cr',$cr);
		$this -> display();
    }
	
    function doedit_tags(){
        $model = D('Zhiyoo.ColumnConfTags');
        $tag = array();
        foreach($_POST['tag'] as $key=>$val){
            $tag[] = $key;
        }
        $tag = implode(',',$tag);
        $tag = empty($tag) ? 0 : $tag ;
        $res = $model->where("cid={$_GET['cid']} and tagid in ({$tag})")->save(array('status'=>1));
        $res = $model->where("cid={$_GET['cid']} and tagid not in ({$tag})")->save(array('status'=>0));
        $this->writelog("智友内容管理-内容标签/栏目-栏目管理 修改cid为{$_GET['cid']}的标签ID{$tag}状态值为1","zy_column_conf_tags",$tag,__ACTION__ ,"","edit");
        $this->success('修改成功');
    }
	
    function edit_tags_rank(){
        $model = D('Zhiyoo.ColumnConfTags');
        $alltags = $model->table('zy_column_conf_tags c')->where(array('c.cid'=>$_GET['cid'],'c.status'=>1))->join('zy_tags t on c.tagid=t.tagid')->order('c.rank asc')->field('c.*,t.tagname')->select();
		$this -> assign('alltags',$alltags);
		$this -> display();
    }
	
    function doedit_tags_rank(){
        $model = D('Zhiyoo.ColumnConfTags');
        foreach($_POST['tag'] as $key=>$val){
           $res = $model->where(array('cid'=>$_GET['cid'],'tagid'=>$key))->save(array('rank'=>$val));
            $this->writelog("智友内容管理-内容标签/栏目-栏目管理 修改cid为{$_GET['cid']}的标签ID{$key}优先级为{$val}","zy_column_conf_tags",$key,__ACTION__ ,"","edit");
        }
        $this->success('修改成功');
    }

	function delimg(){
		$model = D('Zhiyoo.Zhiyoo');
        $cid = $_GET['cid'];
        $result = $model->table('zy_column_conf') ->where(array('cid'=>$cid))->save(array('cover_img'=>''));
        if($result) {
            $this -> writelog("智友内容管理-内容标签/栏目-栏目管理 已删除cid为{$cid}的图片","zy_column_conf",$cid,__ACTION__ ,"","edit");
            echo json_encode(array('su'=>1));
        }else{
            echo json_encode(array('su'=>0));
        }
    }
    //显示栏目列表
    function showColumn(){
		$whereplat = array('status'=>array('egt',1));
        $platId = '';
		if (isset($_REQUEST['platformid']) and $_REQUEST['platformid']>0) {
			$platId = intval($_REQUEST['platformid']);
			$whereplat['platform'] = $platId;
		}else{
			$whereplat['platform'] = array('in','1,2,4');
		}
		$order_sql = (!$_GET['type']||$_GET['type']=='num') ? 'status asc,rank asc': "{$_GET['type']} {$_GET['order']}";
		
		$whereplat['type'] = $_GET['specialtype'] == 'special_column' ?  '1' : '0';
        //查询栏目配置
		$result = $this->slavemodel->table('zy_column_conf')->where($whereplat)->order($order_sql) ->select();
		//查询栏目平台
		$platformarr = $this->slavemodel->table('zy_schedule_platform')->where(array('status'=>1))->select();
		foreach($platformarr as $platinfo){
			$platform[$platinfo['platform']] = $platinfo['platformname'];
		}
		
		foreach($result as $k=>$val){
			$platformid = $val['platform'];
			$result[$k]['platformname'] = $platform[$platformid];
		}
        
		if($_GET['edit_rank']) {
			$edit_rank = 'edit_rank/1/';
			$this->assign('edit_rank',$edit_rank);
		}
		$th[$_GET['type']] =  $_GET['order'] == 'asc' ? 'desc' : 'asc';
		$this->assign('result',$result);
		$this->assign('th',$th);
		$this->assign('platId',$platId);
		$this->assign('platform',$platformarr);
		$this -> display();
                
    }
    //显示栏目列表详情
    function showDetail(){
        if(!$_GET['cid']){
            $this->error('请指定正确的栏目id');
        }
        $platId = '';
		//获取筛选规则名称 2015-09-07 11:40
		$filterdata = $this->filter_rule();
		$tags = $this->slavemodel->gettags();
		$where['cid'] = $_GET['cid'];
		$where['status'] = array('gt',0);
		$result = $this->slavemodel->table('zy_column_conf')->where($where)->select();
		$platformarr = $this->slavemodel->table('zy_schedule_platform')->where(array('status'=>1))->select();
		foreach($platformarr as $platinfo){
			$platform[$platinfo['platform']] = $platinfo['platformname'];
		}
		foreach($result as $k=>$val){
			$count1 = $count2 = 0;
			$extsql1 = $extsql2 = '';
			$idarr = array();
			$platformid = $val['platform'];
			$result[$k]['platformname'] = $platform[$platformid];
			$colruleinfo = explode('_',$result[$k]['rule']);
			$threadtadid = $tduniquetag = $unsettag =array();
			//取出每个栏目的tagid,并且记录下有子标签的父标签tagid
			foreach($colruleinfo as $tagid){
				$tduniquetag[$tagid] = $threadtadid[$tagid] = $tagid;
				if($tags[$tagid]['parentid']>0) $unsettag[] = $tags[$tagid]['parentid'];
			
			}
			//删除有子标签的父标签的tagid
			foreach($unsettag as $delid){unset($tduniquetag[$delid]);}
			
			foreach($tduniquetag as $tag){
				if($tags[$tag]['status'] == -1) continue;
				$columnrule[$result[$k]['cid']][$tags[$tag]['group']]['name'] = $tags[$tag]['groupname'];	
				$columnrule[$result[$k]['cid']][$tags[$tag]['group']]['tag_arr'][] = $tag;
			}

			//即时搜索线上数据统计线上的数量 start
			$extsql1 = '('.implode(',',$colruleinfo).')';
			$searchcnt = count($colruleinfo);
			//搜索符合条件的标签
			//修改规则  2015-09-07 11:40
			if ($result[$k]['filter']==2) {
				$idres = $this->slavemodel->query("select id,count(*) as cnt from zy_idtotagid where tagid in {$extsql1} group by id ");
				$result[$k]['filterusr'] = "content_list_filter";
			}else {
				$idres = $this->slavemodel->query("select * from (select id,count(*) as cnt from zy_idtotagid where tagid in {$extsql1} group by id ) as A  where A.cnt ={$searchcnt}");
				$result[$k]['filterusr'] = "content_list";
			}
			foreach($idres as $idinfo){
				$idarr[] = $idinfo['id'];
			}
			
			$now =time();
			$count1 = $count2 = 0;$aids=array();$cntsql='';
            //线上内容查找
			if(!empty($idarr)){
				$extsql2 =  '('.implode(',',$idarr).')';
				$result1 = $this->slavemodel->table('zy_schedule as zs')->field('zs.id')->join('left join zy_collect_ext as zce on zs.id=zce.advid')->where("zce.platform=$platformid and zs.starttime<={$now} and zs.endtime>={$now} and zce.position = 1 and zs.status=0 and zs.colid in {$extsql2}")->select();
				$count1 = count($result1);
				foreach($result1 as $olaid){
					$aids[] =  $olaid['id'];
				}
				if(!empty($aids)) $cntsql = 'and zc.aid not in ('.implode(',',$aids).')';
			}
            //手动添加的统计 
			$count2 = $this->slavemodel->table('zy_column_content as zc')->join('left join zy_schedule as zs on zc.aid=zs.id')->join('left join zy_collect_ext as zce on zc.aid=zce.advid')->where("zce.platform=$platformid and zc.source = 1 and zs.endtime>={$now} and zs.status=0 and zs.starttime<={$now} and zc.cid={$result[$k]['cid']} $cntsql")->count();
			$result[$k]['num'] = $count1 + $count2;
			//即时搜索线上数据统计线上的数量 end
			
			
			$numrank[$k] = $result[$k]['num'];
		}
	
		$this->assign('result',$result);
		$this->assign('ruleinfo',$columnrule);
		$this->assign('tags',$tags);
		$this->assign('platId',$platId);
		$this->assign('platform',$platformarr);
		$this->assign('specialtype',$_GET['specialtype']);
		$this -> display();
        
        
    }
}