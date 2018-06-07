<?php 

Class CareAction extends CommonAction{
	function result_list(){
		$address = $_SERVER['SERVER_ADDR'] == '192.168.0.99' ? 'forum.anzhi.com' : 'bbs.zhiyoo.com';
		// define('BBSLUNTAN_HOST','http://forum.anzhi.com/forum.php?mod=post&action=edit');
		!defined('BBSLUNTAN_HOST') && define('BBSLUNTAN_HOST','http://bbs.zhiyoo.com/forum.php?mod=post&action=edit');
		$platform = $_GET['platform'] ? $_GET['platform'] : 0 ;
		$column = $_GET['column'] ? $_GET['column'] : 0 ;
		if($_GET['postime'] == 'outline'){
			$order_sql = !$_GET['type']?  'pos.endtime desc': "{$_GET['type']} {$_GET['order']},pos.endtime desc,pos.tid asc";
		}elseif($_GET['postime'] == 'waiton'){
			$order_sql = !$_GET['type']?  'pos.starttime desc': "{$_GET['type']} {$_GET['order']},pos.starttime desc,pos.tid asc";
		}else{
			$order_sql = !$_GET['type']?  'pos.level ASC,pos.starttime desc': "{$_GET['type']} {$_GET['order']},pos.starttime desc,pos.tid asc";
		}
		$th[$_GET['type']] = $_GET['order'] == 'asc' ? 'desc' : 'asc';
		$where_sql = $where_sql1 = $advsql = array();
		$filterurl = '';
		$bbs_model = D('Zhiyoo.Zhiyoo');
		$where_sql = array('pos.status'=>1);
		if($_GET['position']=='wrap'){
			$where_sql['pos.position'] = 3;
			$where_sql1['pos.position'] = 3;
			$advsql['ad.position'] = 3;
			$filterurl .= $srcurl = '/position/wrap';
		}else{
			$where_sql['pos.position'] = 2;
			$where_sql1['pos.position'] = 2;
			$advsql['ad.position'] = 2;
			$filterurl .= $srcurl = '/position/care';
		}
			$start = strtotime($_GET['start_tm']);
			$end = strtotime($_GET['end_tm']);
			$title = $_GET['title'];
			$tid = $_GET['tid'];
			$platform = $_GET['platform'];
			$column = $_GET['column'];
			if($start) {
				$where_sql['pos.addtime'][] = array('egt',$start);
				$where_sql1['pos.addtime'][] = array('egt',$start);
				$advsql['ad.addtime'][] = array('egt',$start);
				$filterurl .= '/start_tm/'.$_GET['start_tm'];
			}
			if($end) {
				$where_sql['pos.addtime'][] = array('elt',$end);
				$where_sql1['pos.addtime'][] = array('elt',$end);
				$advsql['ad.addtime'][] = array('elt',$end);
				$filterurl .= '/end_tm/'.$_GET['end_tm'];
			}
			if($platform) {
				$where_sql['pos.platform'] = $platform;	
				$where_sql1['pos.platform'] = $platform;
				$advsql['ad.platform'] = $platform;
				$filterurl .= '/platform/'.$platform;
			}else{
				$where_sql['pos.platform'] = array('in','1,2,4');	
				$where_sql1['pos.platform'] = array('in','1,2,4');
				$advsql['ad.platform'] = array('in','1,2,4');
			}
			if($column) {
				$where_sql['pos.`column`'] = $column;	
				$where_sql1['pos.`column`'] = $column;
				$advsql['ad.`column`'] = $column;
				$filterurl .= '/column/'.$column;
			}
			if($tid) {
				$where_sql['pos.tid'] = $tid;	
				$filterurl .= '/tid/'.$tid;
			}	
			
		$postime = $_GET['postime'] ? $_GET['postime'] :'online';//var_dump($postime);
		if( $postime == 'waiton'){
			$where_sql['pos.starttime'] = array('exp','>='.time().' or pos.endtime = 0');	//待上线页面开始时间大于当前时间
			$where_sql1['pos.starttime'] = array('exp','>='.time().' or pos.endtime = 0');
			$advsql['ad.starttime'] = array('exp','>='.time().' or ad.endtime = 0');
		}
		elseif($postime == 'online'){
			$where_sql1['pos.starttime'] = $where_sql['pos.starttime'] = array('elt',time());	
			$where_sql1['pos.endtime'] = $where_sql['pos.endtime'] = array('egt',time());
			$advsql['ad.starttime'] = array('elt',time());
			$advsql['ad.endtime'] = array('egt',time());
		}
		elseif($postime == 'outline'){
			$where_sql1['pos.endtime'] = $where_sql['pos.endtime'] = array(array('elt',time()),array('neq',0));
			$advsql['ad.endtime'] = array(array('elt',time()),array('neq',0));
		}
		$postimeurl .= '/postime/'.$postime;
		//$platform==0 ? '' :  $where_sql[]="e.platform = {$platform}";//展示平台分类
		//$filterurl .= $srcurl = '/platform/'.$platform;
		
		import("@.ORG.Page");
		$param = http_build_query($_GET);
		//=======================分页\查数据======================
		if( !isset($_GET['title'])){
			//没有标题的查询不涉及采集池
			
			$count = $bbs_model -> table('zy_pos_conf pos') -> where($where_sql)->count();
			
			$prepage = isset($_GET['lr']) ? $_GET['lr'] : 20;
			$Page = new Page($count,$prepage , $param);
			
			$result = $bbs_model -> table('zy_pos_conf pos')->join('LEFT JOIN zy_schedule_platform p ON pos.platform=p.platform LEFT JOIN zy_column_conf co ON pos.`column`=co.cid')->where($where_sql)->order($order_sql)->limit("{$Page->firstRow},{$Page->listRows} ")->field('*,source as psource')->select();
			
		}
		else{	
			if($title) {
				$where_sql1['c.title'] = array('exp'," like '%{$title}%' or e.ext_title like '%{$title}%'");	
				$where_sql1['pos.status'] = 1;	
				$advsql['ad.ext_title'] = array('exp'," like '%{$title}%'");
				$advsql['ad.status'] = 1;
				$filterurl .= '/title/'.$title;
			}
            
			$count1 = $bbs_model -> table('zy_pos_conf pos')-> join('left join zy_collect_ext e on e.advid=pos.advid')->join("left join zy_collect c on e.colid=c.id") -> where($where_sql1)->count();
			$count2 = $bbs_model -> table('zy_advdata ad') -> where($advsql)->count();
			$count = $count1 + $count2;
			$prepage = isset($_GET['lr']) ? $_GET['lr'] : 20;
			$Page = new Page($count,$prepage , $param);
			//采集池查询
			$result = $bbs_model -> table('zy_pos_conf pos')->join('left join zy_collect_ext e on e.advid=pos.advid left join zy_collect c on e.colid=c.id')->where($where_sql1)->field('e.advid')->select();//找出所有符合条件的advid
			$ids = array();
			foreach($result as $val){
				$ids[] = $val['advid'];
			}
			//$ids = substr($ids,1);
			//广告池查询
			$result = $bbs_model -> table('zy_advdata ad')->where($advsql)->field('ad.advdid')->select();//找出所有符合条件的advid
			//$ids2 = null;
			foreach($result as $val){
				$ids[] = $val['advdid'];
			}
			$where_sql['pos.advid'] = array('in',$ids);
            
			$result = $bbs_model ->table('zy_pos_conf pos')->join('LEFT JOIN zy_schedule_platform p ON pos.platform=p.platform LEFT JOIN zy_column_conf co ON pos.`column`=co.cid')->where($where_sql)->order($order_sql)->limit("{$Page->firstRow},{$Page->listRows}")->field('*,source as psource')->select();
		}
	
		
		//echo $bbs_model ->getlastsql();
		//var_dump($result);
		//================END==================
		$taglist = $bbs_model->gettags();	
		foreach($result as $key => $val){
			if($result[$key]['psource']==1){//获取当前内容的标题/宣传标题等信息
				$result2 = $bbs_model->table('zy_collect_ext e')->join('LEFT JOIN zy_collect c ON e.colid=c.id')->where(array('e.advid'=>$val['advid']))->field('e.*,c.author,c.title,c.img_path,c.url,c.source')->find();
				$result[$key] = array_merge($result[$key],$result2);
			}
			if($result[$key]['psource']==2){//获取当前广告内容的宣传标题等信息
                $result2 = $bbs_model->table('zy_advdata ad')->where(array('ad.advdid'=>$val['advid']))->find();
				$result[$key] = array_merge($result[$key],$result2);
			}
		}
		foreach($result as $key => $val){
				if($val['img_path'] != '') {
					if($result[$key]['source']==0) $result[$key]['img_path'] = C('bbs_attach_url').$val['img_path'];
				}
				if($result[$key]['img_path1'])$result[$key]['img_path1'] = IMGATT_HOST.'/'.$val['img_path1'];
				if($result[$key]['img_path2'])$result[$key]['img_path2'] = IMGATT_HOST.'/'.$val['img_path2'];
				$thread_tags = $bbs_model -> table('zy_idtotagid') -> where("id={$val['colid']}") ->select();
				$threadtadid = $tduniquetag = $unsettag = array();
				//取出每个主题的tagid,并且记录下有子标签的父标签tagid
				foreach($thread_tags as $tagidlist){
					$tduniquetag[$tagidlist['tagid']] = $threadtadid[$tagidlist['tagid']] = $tagidlist['tagid'];
					if($taglist[$tagidlist['tagid']]['parentid']>0) $unsettag[] = $taglist[$tagidlist['tagid']]['parentid'];
				}
				//删除有子标签的父标签的tagid，并且把全部的该主题全部的tagid保存成url
				foreach($unsettag as $delid){unset($tduniquetag[$delid]);}
				foreach($tduniquetag as $tag){
					$tag_result[$val['posid']][$taglist[$tag]['group']]['name'] = $taglist[$tag]['groupname'];	
					$tag_result[$val['posid']][$taglist[$tag]['group']]['tag_arr'][] = $tag;
					$tag_result[$val['posid']][$taglist[$tag]['group']]['tag_url'] = implode('_',$threadtadid).'_';
				}
				//如果主题没有标签，修改主题的打标签状态为未打标签
				if(empty($thread_tags)) {
					$bbs_model->table('zy_collect')->where("id={$val['colid']}")->save(array('tagstatus'=>0));
					$result[$key]['tagstatus'] = '0';
					}
		} //var_dump($result);
		//$grouplist = $bbs_model->gettaggroup();
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
		//
		$platform = $bbs_model->table('zy_schedule_platform')->where("status=1")->select();
		$column = $bbs_model->table('zy_column_conf')->where("status!=0")->field('cid,name')->select();
		$Page->setConfig('header', '篇记录');
		$Page->setConfig('`first', '<<');
		$Page->setConfig('last', '>>');
		$this -> assign('address',$address);
		$this -> assign('postime',$postime);
		$this -> assign('taglist',$taglist);
		$this -> assign('postimeurl',$postimeurl);
		$this -> assign('srcurl',$srcurl);
		$this -> assign('tag_result',$tag_result);
		//$this -> assign('grouplist',$grouplist);
		$this -> assign('platform',$platform);
		$this -> assign('column',$column);
		$this -> assign('lr',$lr);
		$this -> assign('p',$p);
        $this -> assign("page", $show);
		$this -> assign(array('result'=>$result,'th'=>$th,'filterurl'=>$filterurl));
		$this -> display();
	}
	function result_wrap(){
		if(!isset($_GET['position'])) $_GET['position'] = 'wrap';
		$this->result_list();
	}
	function editcolumn(){
		$bbs_model = D('Zhiyoo.Zhiyoo');
		$posid = $_GET['posid'];
		$now = $bbs_model->table('zy_pos_conf')->where(array('posid'=>$posid))->field(array('column'))->find();
		$column = $bbs_model->table('zy_column_conf')->where("status=1 and type=0 and platform != 3")->field('cid,name')->select();
		//echo $bbs_model->getlastsql();
		//var_dump($result);
		$this -> assign('column',$column);
		$this -> assign('now',$now['column']);
		$this->display();
	}
	
	function doeditcolumn(){
		$bbs_model = D('Zhiyoo.Zhiyoo');
		$posid = $_GET['posid'];
		$column = $_POST['column'];//var_dump($column);exit;
		$result = $bbs_model->table('zy_pos_conf')->where(array('posid'=>$posid))->save(array('column'=>$column));
		//echo $bbs_model->getlastsql();
		//var_dump($result);
		$to = $_GET['position'] == 'wrap' ?'result_wrap':'result_list';
		if($result !== false){
			$this -> writelog("智友内容管理-智友精选栏目-智友精选 已修改智友精选posid为{$posid}的链接栏目为{$column}","zy_pos_conf",$posid,__ACTION__ ,"","edit");
			$this -> success("修改成功");
		}else{
			$this -> error("修改失败");
		}
	}
	
	function doeditlevel(){
		$bbs_model = D('Zhiyoo.Zhiyoo');
		$level = $_POST['level'];
		foreach($level as $key => $val){
			$result = $bbs_model->table('zy_pos_conf')->where(array('posid'=>$key))->save(array('level'=>$val));
			$this -> writelog("智友内容管理-智友精选栏目-智友精选 已修改posid为{$key}的优先级为{$val}","zy_pos_conf",$key,__ACTION__ ,"","edit");
		}
		$to = $_GET['position'] == 'wrap' ?'result_wrap':'result_list';
		$_GET['position'] == 'dailyrecom' && $to = 'dailyrecom';
		$this -> assign('jumpUrl',"/index.php/Zhiyoo/Care/".$to.$_POST['url']);
		$this -> success("修改成功");
	}
	
	function dailyrecom(){
		$address = $_SERVER['SERVER_ADDR'] == '192.168.0.99' ? 'forum.anzhi.com' : 'bbs.zhiyoo.com';
		!defined('BBSLUNTAN_HOST') && define('BBSLUNTAN_HOST','http://bbs.zhiyoo.com/forum.php?mod=post&action=edit');
		$th[$_GET['type']] = $_GET['order'] == 'asc' ? 'desc' : 'asc';
		$where_sql = $where_sql1 = $advsql = array();
		$filterurl = '';
		$bbs_model = D('Zhiyoo.Zhiyoo');
		$where_sql = array('pos.position'=>2,'pos.extpos'=>1,'pos.status'=>1);
		$platformselect = $_GET['platforms'];//获取平台
		if($platformselect){
			$where_sql['pos.platform'] = intval($platformselect);
		}else{
			$where_sql['pos.platform'] = array('neq',3);
		}

		$postime = $_GET['postime'] ? $_GET['postime'] :'online';
		if( $postime == 'waiton'){
			$where_sql['pos.starttime'] = array('exp','>='.time().' or pos.endtime = 0');	//待上线页面开始时间大于当前时间
			$order_sql = !$_GET['type']?  'pos.starttime desc': "{$_GET['type']} {$_GET['order']},pos.starttime desc,pos.tid asc";
		}
		elseif($postime == 'online'){
			$where_sql['pos.starttime'] = array('elt',time());	
			$where_sql['pos.endtime'] = array('egt',time());
			$order_sql = !$_GET['type']?  'pos.level ASC,pos.starttime desc': "{$_GET['type']} {$_GET['order']},pos.starttime desc,pos.tid asc";
		}
		elseif($postime == 'outline'){
			$where_sql['pos.endtime'] = array(array('elt',time()),array('neq',0));
			$order_sql = !$_GET['type']?  'pos.endtime desc': "{$_GET['type']} {$_GET['order']},pos.endtime desc,pos.tid asc";
		}
		$postimeurl .= '/postime/'.$postime;
		
		import("@.ORG.Page");
		$param = http_build_query($_GET);
        $count = $bbs_model -> table('zy_pos_conf pos') -> where($where_sql)->count();
        
        $prepage = isset($_GET['lr']) ? $_GET['lr'] : 20;
        $Page = new Page($count,$prepage , $param);
        $result = $bbs_model -> table('zy_pos_conf pos')->join('LEFT JOIN zy_column_conf co ON pos.`column`=co.cid')->where($where_sql)->order($order_sql)->limit("{$Page->firstRow},{$Page->listRows} ")->field('*,source as psource')->select();
			
		$re = $bbs_model->table('zy_schedule_platform')->where("status=1")->select();
        $platform = array();
        foreach($re as $v){
            $platform[$v['platform']] = $v['platformname'];
        }
		$taglist = $bbs_model->gettags();	
		foreach($result as $key => $val){
			if($result[$key]['psource']==1){//获取当前内容的标题/宣传标题等信息
				$result2 = $bbs_model->table('zy_collect_ext e')->join('LEFT JOIN zy_collect c ON e.colid=c.id')->where(array('e.advid'=>$val['advid']))->field('e.*,c.author,c.title,c.img_path,c.url,c.source')->find();
			}elseif($result[$key]['psource']==2){//获取当前广告内容的宣传标题等信息
                $result2 = $bbs_model->table('zy_advdata ad')->where(array('ad.advdid'=>$val['advid']))->find();
			}
            $result[$key] = array_merge($result[$key],$result2);
            if(!empty($result[$key]['img_path']) && $result[$key]['source']==0) $result[$key]['img_path'] = C('bbs_attach_url').$result[$key]['img_path'];
            if($result[$key]['img_path1'])$result[$key]['img_path1'] = IMGATT_HOST.'/'.$result[$key]['img_path1'];
            if($result[$key]['img_path2'])$result[$key]['img_path2'] = IMGATT_HOST.'/'.$result[$key]['img_path2'];
            $thread_tags = $bbs_model -> table('zy_idtotagid') -> where(array('id'=>$result[$key]['colid'])) ->select();
            $threadtadid = $tduniquetag = $unsettag = array();
            //取出每个主题的tagid,并且记录下有子标签的父标签tagid
            foreach($thread_tags as $tagidlist){
                $tduniquetag[$tagidlist['tagid']] = $threadtadid[$tagidlist['tagid']] = $tagidlist['tagid'];
                if($taglist[$tagidlist['tagid']]['parentid']>0) $unsettag[] = $taglist[$tagidlist['tagid']]['parentid'];
            }
            //删除有子标签的父标签的tagid，并且把全部的该主题全部的tagid保存成url
            foreach($unsettag as $delid){unset($tduniquetag[$delid]);}
            foreach($tduniquetag as $tag){
                $tag_result[$val['posid']][$taglist[$tag]['group']]['name'] = $taglist[$tag]['groupname'];	
                $tag_result[$val['posid']][$taglist[$tag]['group']]['tag_arr'][] = $tag;
                $tag_result[$val['posid']][$taglist[$tag]['group']]['tag_url'] = implode('_',$threadtadid).'_';
            }
            //如果主题没有标签，修改主题的打标签状态为未打标签
            if(empty($thread_tags)) {
                $bbs_model->table('zy_collect')->where(array('id'=>$result[$key]['colid']))->save(array('tagstatus'=>0));
                $result[$key]['tagstatus'] = '0';
            }
		}
		$show = $Page->show();
        
		$column = $bbs_model->table('zy_column_conf')->where("status!=0")->field('cid,name')->select();
		$Page->setConfig('header', '篇记录');
		$Page->setConfig('first', '<<');
		$Page->setConfig('last', '>>');
		$this -> assign('address',$address);
		$this -> assign('postime',$postime);
		$this -> assign('taglist',$taglist);
		$this -> assign('postimeurl',$postimeurl);
		$this -> assign('srcurl',$srcurl);
		$this -> assign('tag_result',$tag_result);
		$this -> assign('platforms',$platformselect);
		$this -> assign('platform',$platform);
		$this -> assign('platform_list',$re);
		$this -> assign('column',$column);
        $this -> assign("page", $show);
		$this -> assign(array('result'=>$result,'th'=>$th,'filterurl'=>$filterurl));
		$this -> display();
	}
	
	function dailyicon(){
        $model = D('Zhiyoo.DailyrecomIcon');
		if(!$_POST){
            $res = $model -> get();
            if($res['img_path'])$img_path = IMGATT_HOST.'/'.$res['img_path'];
            $this -> assign("img_path", $img_path);
            $this -> display();
        }else{
            $datedir = '/zhiyoo/';
            $savepath = UPLOAD_PATH.$datedir;
            if($_FILES['img']['size']>0){
                $config = array(
                    'tmp_file_dir' => '/tmp/',
                    'width' => 68,
                    'filesize' => 100000,
                    'real_width' => 68,
                );
                $imgpath = $this -> _upload($_FILES['img'],$savepath,$config);
                $data = array('img_path'=>$datedir.$imgpath);
                if($imgpath){
                    $model -> delall();
                    $result = $model -> add($data);
                }
            }
            if($result !== false){
                $this -> writelog("智友内容管理-智友精选栏目-每日推荐 已修改 配置图标id为{$result}","zy_pos_conf",$result,__ACTION__ ,"","edit");
                $this -> success("修改成功");
            }else{
                $this -> error("修改失败");
            }
        }
	}
	
	protected function _upload($file,$savepath,$config){
		include dirname(realpath(__FILE__)).'/imagemagick.php';
		return  up_load_thumbimg($file,$savepath,$config);
	}
}