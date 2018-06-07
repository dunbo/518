<?php 

Class WaitonAction extends CommonAction{
    private $bbs_attach_url = 'http://attach.zhiyoo.com/forum/';
    
	function result_list(){
		$address = $_SERVER['SERVER_ADDR'] == '192.168.0.99' ? 'forum.anzhi.com' : 'bbs.zhiyoo.com';
		// define('BBSLUNTAN_HOST','http://forum.anzhi.com/forum.php?mod=post&action=edit');
		!defined('BBSLUNTAN_HOST') && define('BBSLUNTAN_HOST','http://bbs.zhiyoo.com/forum.php?mod=post&action=edit');
		$platform = $_GET['platform'] ? $_GET['platform'] : 0 ;
		if($_GET['source'] == 'outline'){
			$order_sql = !$_GET['type']?  's.endtime desc,c.id asc': "{$_GET['type']} {$_GET['order']},s.endtime desc,c.id asc";
		}else{
			//$order_sql = !$_GET['type']?  's.addschtime desc,c.id asc': "{$_GET['type']} {$_GET['order']},s.addschtime desc,c.id asc";
			$order_sql = !$_GET['type']?  's.starttime desc,c.id asc': "{$_GET['type']} {$_GET['order']},s.starttime desc,c.id asc";
		}
		$th[$_GET['type']] = $_GET['order'] == 'asc' ? 'desc' : 'asc';
		$where_sql = array();
		$filterurl = '';
		$bbs_model = D('Zhiyoo.Zhiyoo');
		$slave_model = D('Zhiyoo.ZhiyooSlave');
			$where_sql['s.status'] = 0;
		
			$start = strtotime($_GET['start_tm']);
			$end = strtotime($_GET['end_tm']);
			$title = $_GET['title'];
			$tid = $_GET['tid'];
			$tagid = $_GET['tags'];
			$uniquetags = $_GET['uniquetags'];
			if($start) {
				$where_sql['s.addschtime'] =  array('egt',$start);	
				$filterurl .= '/start_tm/'.$_GET['start_tm'];
				
			}
			if($end) {
				$where_sql['s.addschtime'] = array('elt',$end);	
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
			if($_GET['position']) {
				$where_sql['e.position'] = $_GET['position'];	
				$filterurl .= '/position/'.$_GET['position'];
			}
			$taglist = $slave_model->gettags();
			if($tagid) {
				$tagrules = explode('_',$tagid);
				array_pop($tagrules);
				$extsql = '('.implode(',',$tagrules).')';
				$searchcnt = count($tagrules);
				//搜索符合条件的标签
				$tagres = $slave_model->query("select * from (select id,count(*) as cnt from zy_idtotagid where tagid in {$extsql} group by id ) as A  where A.cnt ={$searchcnt}"	);					
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
				
			}	
		$source = $_GET['source'] ? $_GET['source'] :'waiton';//var_dump($source);
		if( $source == 'waiton'){
			$where_sql['s.starttime'] = array('exp',' >='.time().' or s.endtime = 0');	//待上线页面开始时间大于当前时间
		}
		elseif($source == 'online'){
			$where_sql['s.starttime'] = array('elt',time());	
			$where_sql['s.endtime'] = array('egt',time());	
		}
		elseif($source == 'outline'){
			$where_sql['s.endtime'] = array(array('egt',9999),array('elt',time()));	
		}
		$filterurl .= '/source/'.$source;
		$platform==0 ? $where_sql['e.platform'] = array('in','1,2,4') :  $where_sql['e.platform'] = $platform;//展示平台分类
		
        $extpos = $slave_model->extpos();
		import("@.ORG.Page");
		$param = http_build_query($_GET);
		
		$issrch = $_GET['srch_h'] ? intval($_GET['srch_h']) : 0;
		if($platform || $issrch){
			$count = $slave_model -> table('zy_schedule s')->join('LEFT JOIN zy_collect_ext e ON s.eid=e.id')->join('LEFT JOIN zy_collect c ON s.colid=c.id')->join('LEFT JOIN zy_schedule_platform p ON e.platform=p.platform')->join('LEFT JOIN zy_schedule_position po ON e.position=po.position') -> where($where_sql)->count();
		}else{
			// $count = $slave_model -> table('zy_schedule s') -> where($where_sql)->count();
			$count = $slave_model -> table('zy_schedule s')->join('LEFT JOIN zy_collect_ext e ON s.eid=e.id')-> where($where_sql)->count();	
		}
		$prepage = isset($_GET['lr']) ? $_GET['lr'] : 20;
		$Page = new Page($count,$prepage , $param);
		$result = $slave_model ->table('zy_schedule s')->join('LEFT JOIN zy_collect_ext e ON s.eid=e.id')->join('LEFT JOIN zy_collect c ON s.colid=c.id')->join('LEFT JOIN zy_schedule_platform p ON e.platform=p.platform')->join('LEFT JOIN zy_schedule_position po ON e.position=po.position') -> where($where_sql)->order($order_sql)->limit("{$Page->firstRow},{$Page->listRows} ")->select();
		//echo $bbs_model ->getlastsql();
		//var_dump($result);s.eid,s.id as advid,s.tid,c.title,c.author,c.views,c.replies,s.addschtime,p.platformname,s.starttime,s.endtime
		foreach($result as $key => $val){
			if($val['img_path'] != '') {
				if($result[$key]['source']==0) $result[$key]['img_path'] = C('bbs_attach_url').$val['img_path'];
				}
			if($result[$key]['img_path1'])$result[$key]['img_path1'] = IMGATT_HOST.'/'.$val['img_path1'];
			if($result[$key]['img_path2'])$result[$key]['img_path2'] = IMGATT_HOST.'/'.$val['img_path2'];
			$thread_tags = $slave_model -> table('zy_idtotagid') -> where("id={$val['id']}") ->select();
			$threadtadid = $tduniquetag = $unsettag = array();
			//取出每个主题的tagid,并且记录下有子标签的父标签tagid
			foreach($thread_tags as $tagidlist){
				$tduniquetag[$tagidlist['tagid']] = $threadtadid[$tagidlist['tagid']] = $tagidlist['tagid'];
				if($taglist[$tagidlist['tagid']]['parentid']>0) $unsettag[] = $taglist[$tagidlist['tagid']]['parentid'];
			}
			//删除有子标签的父标签的tagid，并且把全部的该主题全部的tagid保存成url
			foreach($unsettag as $delid){unset($tduniquetag[$delid]);}
			foreach($tduniquetag as $tag){
				$tag_result[$val['advid']][$taglist[$tag]['group']]['name'] = $taglist[$tag]['groupname'];	
				$tag_result[$val['advid']][$taglist[$tag]['group']]['tag_arr'][] = $tag;
				$tag_result[$val['advid']][$taglist[$tag]['group']]['tag_url'] = implode('_',$threadtadid).'_';
			}
			//如果主题没有标签，修改主题的打标签状态为未打标签
			if(empty($thread_tags)) {
				$bbs_model->table('zy_collect')->where("id={$val['id']}")->save(array('tagstatus'=>0));
				$result[$key]['tagstatus'] = '0';
				}
		} 
		$grouplist = $slave_model->gettaggroup();
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
		$position = $slave_model -> query("select * from zy_schedule_position ");
        //平台列表
		$platform_list = $bbs_model->table('zy_schedule_platform')->where("status=1")->select();
        // $platform_list = array();
        // foreach($re as $v){
        //     $platform_list[$v['platform']] = $v['platformname'];
        // }
        
		$Page->setConfig('header', '篇记录');
		$Page->setConfig('`first', '<<');
		$Page->setConfig('last', '>>');
		$this -> assign('source',$source);
		$this -> assign('address',$address);
		$this -> assign('position',$position);
		$this -> assign('taglist',$taglist);
		$this -> assign('searchtag',$searchtag);
		$this -> assign('tag_result',$tag_result);
		$this -> assign('grouplist',$grouplist);
		$this -> assign('bbs_model',$bbs_model);
		$this -> assign('lr',$lr);
		$this -> assign('p',$p);
        $this -> assign("page", $show);
		$this -> assign(array('result'=>$result,'th'=>$th,'filterurl'=>$filterurl,'cat'=>$cat));
		$this -> assign('extpos',$extpos);
		$this -> assign('platform_list',$platform_list);
		$this -> display();
	}
	
	function result_online(){
		if(!isset($_GET['source'])) $_GET['source'] = 'online';
		if(!isset($_GET['platform'])) $_GET['platform'] = '4';
		$this -> result_list();
	}
	
	function result_outline(){
		if(!isset($_GET['source'])) $_GET['source'] = 'outline';
		$this -> result_list();
	}
	function sub_thread(){
		$source = $_GET['source'] ? $_GET['source'] : 0 ;
		$bbs_model = D('Zhiyoo.Zhiyoo');
		$slave_model = D('Zhiyoo.ZhiyooSlave');
		if($_GET['ids']) $ids = $_GET['ids']; else $this-error('请选择数据');
		$err_res = $slave_model -> table('zy_collect') -> field('id')->where('id in ('.$ids.') and (tagstatus = 0 or status = 0)') -> select();
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
		$result = $bbs_model -> table('zy_collect') -> where(array('id'=>array('in',$tids))) -> save(array('substatus' => 1,'addfoddertm'=>time()));
		if($result){
				$this -> writelog("智友内容管理-待用素材库-待用素材库列表 已经添加id为{$ids}内容至素材库","zy_collect",$ids,__ACTION__ ,"","add");
				$this -> assign("jumpUrl","/index.php/Zhiyoo/Collectresult/result_list/source/{$source}");
			if($err_res){
				$this -> error("id为".implode(',',$notags)."的内容记录还没有打标签或者已经被删除,其余添加成功");
			}else{
				$this -> success("添加成功");
			}
		}else{
			$this -> error("添加失败");
		}
			
		
	}
	
	function tag_list_show(){
		$bbs_modle = D('Zhiyoo.Zhiyoo');
		$slave_model = D('Zhiyoo.ZhiyooSlave');
		$source = $_GET['source'] ? $_GET['source'] : 0 ;
		//获取所有的一级分类
		$result = $slave_model ->table('zy_tagsgroup')->field(array('groupid','groupname'))->where('status = 1')->select();
		foreach($result as $val){
			$cat[$val['groupid']] = $val['groupname'];
		}
		$result = $slave_model ->table('zy_tags')->field(array('tagid','tagname','group','parentid'))->where('status = 1')->select();
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
	
	function over(){
		$bbs_model = D('Zhiyoo.Zhiyoo');
		$advid = $_GET['advid'];
		$time = time()-1;
		$result = $bbs_model->table('zy_schedule')->where(array('id'=>array('in',$advid)))->save(array('endtime'=>$time));//更新排期表时间过期
		$result = $bbs_model->table('zy_pos_conf')->where(array('advid'=>array('in',$advid),'source'=>1))->save(array('endtime'=>$time));//更新位置表时间过期
		//echo $bbs_model->getlastsql();
		//var_dump($result);
		if($result !== false){
			$this -> writelog("智友内容管理-排期上线 已经结束id为{$advid}内容","zy_schedule",$advid,__ACTION__ ,"","edit");
			$this -> success("结束成功");
		}
		else{
			$this -> error("失败");
		}
	}
	
	function back(){//撤回素材池
		$bbs_model = D('Zhiyoo.Zhiyoo');
		$advid = $_GET['advid'];
		$nowtime = time();
        $where = array('status'=>0);
        $where['id']=array('in',$advid);
		$result = $bbs_model->table('zy_schedule')->where($where)->field('tid')->select();
		foreach($result as $val){
			$tids .= ','.$val['tid'];
		}
		$tids = substr($tids,1);
        $where = array('status'=>0);
        $where['tid']=array('in',$tids);
		$result = $bbs_model->table('zy_schedule')->where($where)->field('id')->select();
		foreach($result as $val){
			$advids .= ','.$val['id'];
		}//var_dump($advids);exit;
		$advids = substr($advids,1);
        $bbs_model->table('zy_schedule')->where($where)->save(array('status'=>-1,'deltime'=>$nowtime));
        $bbs_model->table('zy_collect_ext')->where(array('tid'=>array('in',$tids)))->delete();
        $where = array('status'=>1);
        $where['advid']=array('in',$advids);
        $where['source']=1;
        $bbs_model->table('zy_pos_conf')->where($where)->save(array('status'=>-1));
        $result = $bbs_model->table('zy_collect')->where(array('tid'=>array('in',$tids)))->save(array('substatus'=>-2,'addfoddertm'=>$nowtime));//更新素材状态
		$this -> writelog("智友内容管理-排期上线 已经撤回id为{$advid}的内容到素材池","zy_schedule",$advid,__ACTION__ ,"","edit");
		$this -> success("撤回成功");
	}
	#撤回一条数据
	function back_one(){
		$bbs_model = D('Zhiyoo.Zhiyoo');
		$advid = $_GET['advid'];
		$nowtime = time();
		!is_numeric($advid) && $this -> error("id错误");
		$result = $bbs_model->table("zy_schedule")->where(array('id'=>$advid))->field('tid')->find();
		$tid = $result['tid'];//var_dump($tid);exit;
		$idcount = $bbs_model->table("zy_schedule")->where(array('status'=>0,'tid'=>$tid))->count();
        $bbs_model->table('zy_schedule')->where(array('id'=>$advid))->save(array('status'=>-1,'deltime'=>$nowtime));
        $bbs_model->table('zy_collect_ext')->where(array('advid'=>$advid))->delete();
        $where = array('status'=>1);
        $where['advid']=$advid;
        $where['source']=1;
        $bbs_model->table('zy_pos_conf')->where($where)->save(array('status'=>-1));
		if($idcount <= 1){
            $bbs_model->table('zy_collect')->where(array('tid'=>$tid))->save(array('substatus'=>-2,'addfoddertm'=>$nowtime));
		}
		$this -> writelog("智友内容管理-排期上线 已经撤回id为{$advid}的内容到素材池","zy_pos_conf",$advid,__ACTION__ ,"","edit");
		$this -> success("撤回成功");
	}
	function copy(){
		$bbs_model = D('Zhiyoo.Zhiyoo');
		$advid = $_GET['advid'];
		$tittle = $bbs_model ->table('zy_collect_ext e')->join('left join zy_collect c on c.id=e.colid')->where(array('e.advid'=>$advid))->find();
		$time = $bbs_model ->table('zy_schedule')->where(array('id'=>$advid))->field('starttime,endtime')->find();
		$platform = $bbs_model ->table('zy_schedule_platform')->select();
		$position = $bbs_model ->table('zy_schedule_position')->select();
		$result = $bbs_model ->table('zy_collect_ext')->where(array('tid'=>$tittle['tid']))->select();
		foreach($result as $val){
			$checked[] = $val['platform'].'_'.$val['position'];
		}
		$this -> assign("platform", $platform);
		$this -> assign("position", $position);
		$this -> assign("checked", $checked);
		$this -> assign("time", $time);
		$this->assign('tittle',$tittle['title']);
		$this->display();
	}
	function docopy(){
		$bbs_model = D('Zhiyoo.Zhiyoo');
		$sch_model = D('Zhiyoo.schedule');
		$zy_collect_extmodel = D('Zhiyoo.collect_ext');
		$pos_confmodel = D('Zhiyoo.pos_conf');
		$advid = $_GET['advid'];
		//$platform = $_POST['platform'];
		$time = time();
		/*!$_POST['starttime'] && $this -> error("时间不能为空");
		!$_POST['endtime'] && $this -> error("时间不能为空");*/
		$_POST['endtime'] < $_POST['starttime'] && $this -> error("开始时间须小于结束时间");
		$starttime = $_POST['starttime'] ? strtotime($_POST['starttime']) : 0;
		$endtime = $_POST['endtime'] ? strtotime($_POST['endtime']) : 0;
		$pp = is_array($_POST['pp']) ? $_POST['pp'] : $this->error('请选择位置');
		$result = $bbs_model ->table('zy_collect_ext')->where(array('advid'=>$advid))->find();//获取collect_ext表关联内容id
		$sdata = array();//排期表需要复制的内容
		$sdata['tid'] = $result['tid'];
		$sdata['colid'] = $result['colid'];
		$sdata['addschtime'] = $time;
		$sdata['starttime'] = $starttime;
		$sdata['endtime'] = $endtime;
		$edata = array();//ext表需要复制的内容
		$edata['colid'] = $result['colid'];
		$edata['tid'] = $result['tid'];
		$edata['ext_title'] = $result['ext_title'];
		$edata['jump_url'] = $result['jump_url'];
		$edata['jump_status'] = $result['jump_status'];
		$edata['img_path1'] = $result['img_path1'];
		$edata['img_path2'] = $result['img_path2'];
		$edata['description'] = $result['description'];
		$pdata = array();
		$pdata['addtime'] = $time;
		foreach($pp as $val){	
			$val2 = explode('_',$val);$log .= ','.$val;
			$edata['platform'] = $val2[0];
			$edata['position'] = $val2[1];//var_dump($advid);exit;
			$lastid = $sch_model->table('zy_schedule')-> data($sdata)->add();//插入一条新纪录,获取新 广告ID 
			$edata['advid'] = $lastid;
			$lasteid = $zy_collect_extmodel->table('zy_collect_ext')->data($edata)->add();//获取新 eid 
			$result = $bbs_model->query("update zy_schedule set eid='$lasteid' where id='$lastid'");//更新新内容关联eid
			$pdata['advid'] = $lastid;
			$pdata['tid'] = $edata['tid'];
			$pdata['platform'] = $val2[0];
			$pdata['position'] = $val2[1];
			$pdata['source'] = 1;
			$pdata['level'] = 999;
			$pdata['column'] = '';
			$pdata['starttime'] = $starttime;;
			$pdata['endtime'] = $endtime;
			$pos_confmodel -> table('zy_pos_conf') -> data($pdata) -> add();
		}
		
		$this -> writelog("智友内容管理-排期上线 已经复制id为{$advid}的内容到{$log}","zy_pos_conf",$advid,__ACTION__ ,"","add");
		$this -> success("复制成功");
		
	}
	
	function reonline(){
		$bbs_model = D('Zhiyoo.Zhiyoo');
		$advid = $_GET['advid'];
		if(is_numeric($advid )){
            $result = $bbs_model ->table('zy_schedule s')->join('left join zy_collect_ext e on s.id=e.advid')->where(array('s.id'=>$advid))->find();
			$platform = $result['platform'];
			$platform = $bbs_model->table('zy_schedule_platform')->where(array('platform'=>$platform))->find();//查询原平台
			$platform = $platform['platformname'];
			$tittle = $bbs_model->table('zy_collect')->where(array('id'=>$result['colid']))->find();
			$tittle = $tittle['title'];
		}
		else{
			$platform = '批量按原始平台提交';
			$tittle = '批量标题';
		}
		
		$result['starttime'] = $result['starttime'] ? date('Y-m-d H:i:s',$result['starttime']) : '';
		$result['endtime'] = $result['endtime'] ? date('Y-m-d H:i:s',$result['endtime']) : '';
		$this->assign('result',$result);
		$this->assign('platform',$platform);
		$this->assign('tittle',$tittle);
		$this->display();
	}
	
	function doreonline(){
		$bbs_model = D('Zhiyoo.Zhiyoo');
		$advid = $_GET['advid'];
		!$_POST['starttime'] && $this -> error("时间不能为空");
		!$_POST['endtime'] && $this -> error("时间不能为空");
		$_POST['endtime'] < $_POST['starttime'] && $this -> error("开始时间须小于结束时间");
		$starttime = strtotime($_POST['starttime']);
		$endtime = strtotime($_POST['endtime']);//var_dump($advid);
		$time = time();
        $result = $bbs_model->table('zy_schedule')->where(array('id'=>array('in',$advid)))->save(array('addschtime'=>$time,'starttime'=>$starttime,'endtime'=>$endtime));
        $result = $bbs_model->table('zy_pos_conf')->where(array('advid'=>array('in',$advid),'source'=>1))->save(array('addtime'=>$time,'starttime'=>$starttime,'endtime'=>$endtime));
		if($result !== false){
			$this -> writelog("智友内容管理-排期上线 已经重新编辑时间id为{$advid}内容{$_POST['starttime']}-{$_POST['endtime']}","zy_schedule",$advid,__ACTION__ ,"","edit");
			$this -> success("重新编辑时间成功");
		}
		else{
			$this -> error("重新编辑时间失败");
		}
	}
	
	function changestatus_imgonly(){
		$model = D('Zhiyoo.Zhiyoo');
		$tid = $_GET['tid'];
		$status = $_GET['status'];
        $result = $model->table('zy_collect')->where(array('tid'=>$tid))->save(array('imgonly'=>$status));
		if($result !== false){
			$this -> writelog("智友内容管理-排期上线 已经编辑读图模式tid为{$tid}状态为{$status}","zy_collect",$tid,__ACTION__ ,"","edit");
			$this -> success("编辑读图模式成功");
		}
		else{
			$this -> error("编辑读图模式失败");
		}
	}
	
	function change_display_type(){
		$eid = $_GET['eid'];
		$bbs_model = D('Zhiyoo.Zhiyoo');
		$type = $bbs_model->table('zy_collect_ext')->where(array('id'=>$eid))->find();
        $tid = $type['tid'];
        if($type['platform'] && $type['position']){//可用的选项
            $res = $bbs_model->table('zy_schedule_rela')->where(array('platform'=>$type['platform'],'position'=>$type['position'],'status'=>1))->select();
            $extpos = array();
            foreach($res as $v){
                $extpos[] = $v['extpos'];
            }
        }
		$time='';
		if($type['extpos']==3){	
			$time = $type['videotime'];
			$videoimg = $type['videoimg'];
		}
		if($_POST['formsubmit']){
			$imgonly = $_POST['imgonly'];
			// var_dump($imgonly);die;
			$time = $_POST['videotime'];
			$videoimg = $_POST['videoimg'];
			if($imgonly === '' ||($imgonly==3&&!$time)){
				$this->error('请提交正确的视频参数');
			}
			
            if($type['extpos'] != 1 && $imgonly == 1){//线上置顶只能有两条
                //判断今日推荐总数量
                $platform = $type['platform'];
                $count = $bbs_model->table('zy_pos_conf')->where(array('platform'=>$platform,'position'=>2,'extpos'=>1,'status'=>1,'starttime'=>array('elt',time()),'endtime'=>array('egt',time())))->count();
                if($count >=2){
                    $maxstarttime = $bbs_model->table('zy_pos_conf')->where(array('platform'=>$platform,'position'=>2,'extpos'=>1,'status'=>1,'starttime'=>array('elt',time()),'endtime'=>array('egt',time())))->getField('max(starttime)');
                    //小于等于最大开始时间的都从今日推荐中踢出在线内容
                    $res = $bbs_model->table('zy_pos_conf')->where(array('platform'=>$platform,'position'=>2,'extpos'=>1,'status'=>1,'starttime'=>array('elt',time()),'endtime'=>array('egt',time())))->field('advid,posid,starttime')->select();
                    $ids = $advids = array();
                    //只留一个内容
                    $only = 1;
                    foreach($res as $v){
                        if($only && $v['starttime']==$maxstarttime){
                            $only = 0;
                            continue;
                        }
                        $advids[] = $v['advid'];
                        $ids[] = $v['posid'];
                    }
                    $bbs_model->table('zy_pos_conf')->where(array('posid'=>array('in',$ids)))->save(array('extpos'=>0));
                    if($advids) $bbs_model->table('zy_collect_ext')->where(array('advid'=>array('in',$advids)))->save(array('extpos'=>0));
                }
            }
			
            if($imgonly == 2 ){//资源类型传图
                if($_FILES['resimg']['size']>0){
                    $config = array(
                        'tmp_file_dir' => '/tmp/',
                        'width' => 78,
                        'filesize' => 200000,
                        'real_width' => 78,
                    );
                    $datedir = '/zhiyoo/';
                    $savepath = UPLOAD_PATH.'/zhiyoo/';
                    $imgpath = $this -> _upload($_FILES['resimg'],$savepath,$config);
                    $extdata['img_path3'] = $datedir.$imgpath;		
                    
                }elseif(empty($type['img_path3'])){
                    $this->error('资源类型须传图');
                }
            }
			
            if($imgonly == 4 && empty($type['img_path2'])){//头条
                $this->error('头条类型须传大图');
            }
            $extdata['softtag'] = '';
            if($imgonly == 6 || $imgonly == 7 ){
                if(mb_strlen($_POST['softtag'],'utf8')>3){
                    $this->error('标签文字长度不能超过3个字');
                }
                $extdata['softtag'] = $_POST['softtag'];
            }

			$coldata['extpos'] = $imgonly;
			$extdata['extpos'] = $imgonly;
			$extdata['videotime'] = $imgonly == 3 ? $time : 0;
			$extdata['videoimg'] = $imgonly == 3 ? $videoimg : 0;
			$bbs_model->table('zy_pos_conf')->where(array('advid'=>$type['advid']))->data($coldata)->save();
			$bbs_model->table('zy_collect_ext')->where(array('id'=>$eid))->data($extdata)->save();
			//如果配置是读图模式，同步给以前的读图模式字段
			if($imgonly == 5){
				$bbs_model->table('zy_collect')->where(array('tid'=>$tid))->save(array('imgonly'=>1));
			}else{
				//如果操作前是读图模式，并且这个tid没有读图模式的配置了，同步更新以前的读图模式字段
				if($type['extpos'] == 5){
					$existimg = $bbs_model->table('zy_collect_ext')->where(array('tid'=>$tid,'extpos'=>5))->find();
					if(!$existimg){
						$bbs_model->table('zy_collect')->where(array('tid'=>$tid))->save(array('imgonly'=>0));
					}	
				}
			}
			
			$this -> writelog("智友内容管理-排期上线 编辑tid为{$tid}的显示模式状态为{$imgonly}","zy_collect",$tid,__ACTION__ ,"","edit");
			$this -> success("修改成功");
			
			
		}else{
			
			$this->assign('eid',$eid);
			$this->assign('tid',$tid);
			$this->assign('type',$type['extpos']);
			$this->assign('time',$time);
			$this->assign('videoimg',$videoimg);
			$this->assign('extpos',$extpos);
			$this->assign('res',$type);
			
			$this -> display();
		}
		
		
		
	}
	
	function getvideoinfo(){
		$zhiyoomodel = D('Zhiyoo.Zhiyoo');
		$bbsmodel = D('Zhiyoo.bbs');
		$tid = $_GET['tid'];
		$table = $bbsmodel->table("x15_forum_thread")->field("posttableid")->where("tid=".$tid)->find();
		$posttable = $table['posttableid'] ? 'x15_forum_post_'.$table['posttableid'] : 'x15_forum_post';
		$msg = $bbsmodel->table($posttable)->field('message')->where("tid={$tid} and first=1")->find();
		$videoid = $url = '';
		$message = $msg['message'];
		if(preg_match("/\[media=([\w,]+)\]\s*([^\[\<\r\n]+?)\s*\[\/media\]/is",$message,$match)){
			$url = $match[2];
		}elseif(preg_match("/\[flash(=(\d+),(\d+))?\]\s*([^\[\<\r\n]+?)\s*\[\/flash\]/is",$message,$match)){
			$url = $match[4];
		}
		
		
		if($url && strpos($url,'youku') !== false){
			if(preg_match('/player.php\/sid\/([^\/]+)\//i',$url,$vid) || preg_match('/v_show\/id_([^\.]+)\.html/i',$url,$vid)|| preg_match('/embed\/([^\/]+)/i',$url,$vid)){
				$videoid = $vid[1];	
			}
		}
		if($videoid){
			$client_id = 'deb188863209ff9b';
			$api = 'https://openapi.youku.com/v2/videos/show_basic.json?client_id='.$client_id.'&video_id='.$videoid;
			$ch = curl_init();
			curl_setopt($ch,CURLOPT_URL,$api);
			curl_setopt($ch,CURLOPT_HEADER,0);
			curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
			curl_setopt($ch,CURLOPT_TIMEOUT,60); //默认超时60秒
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // https请求 不验证证书和hosts
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			$return = curl_exec($ch);
			$httpstatus = curl_getinfo($ch);
			curl_close($ch);
			if($httpstatus['http_code'] != '200') {
				$return_msg = array('state'=>0,'msg'=>'请求失败请刷新重试');
			}
			$videoinfo = json_decode($return,true);
			$return_msg = array('duration'=>floor($videoinfo['duration']),'state'=>1,'img'=>$videoinfo['thumbnail']);
			
		}else{
			$return_msg = array('state'=>0,'msg'=>'该主题没有包含相应的优酷视频链接,不能配置成视频模式');
		}
		
		echo json_encode($return_msg);
		
	}
	
	protected function _upload($file,$savepath,$config){
		include dirname(realpath(__FILE__)).'/imagemagick.php';
		return  up_load_thumbimg($file,$savepath,$config);
	}
	
}