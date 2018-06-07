<?php 

Class TagClassAction extends CommonAction{
	
	function class_list(){
		$order = 'ASC';
		$orderBy = 'classid '.$order;
		if (isset($_REQUEST['field']) and isset($_REQUEST['order'])) {
			$field = addslashes(trim($_REQUEST['field']));
			$order = addslashes(trim($_REQUEST['order']));
			$orderBy = "$field $order";
		}
		$model = D('Zhiyoo.Zhiyoo');

		//分页
		import("@.ORG.Page");
		$param = http_build_query($_GET);
		$scount = $model -> table('zy_tagsclass')-> where(array('status'=>1))->count();
		$prepage = isset($_GET['lr']) ? $_GET['lr'] : 20;
		$Page = new Page($scount,$prepage , $param);

		$class = $model ->table("zy_tagsclass")->where(array('status'=>1))->order($orderBy)->limit("{$Page->firstRow} , {$Page->listRows}")->select();//读取一级目录，按照优先级升序排列
		foreach($class as $key => $val){
			//根据tagid获取名称
			if(!empty($val['tagsid'])){
				// $where['tagid'] = array('in',$val['tagsid']);
				// $classname = $model -> table("zy_tags")->where($where)->select();
				// $classtagnames = array();
				// foreach ($classname as $item) {
				// 	if(!empty($item['tagname'])){
				// 		$classtagnames[]['name'] = $item['tagname'];
				// 	}	
				// }
				$thread_tags = explode(',',$val['tagsid']);
				$count = count($thread_tags);
				// $class[$key]['classtagnames'] = $classtagnames;
				$class[$key]['count'] = $count ? $count : 0;
				/*==================*/
				$taglist = $model->gettags();
				
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

			
				
			/*==================*/
		}
	
        if(isset($_GET['corder'])){
            $classid = $classname = $status = $count = $c_rank = $c_tagsid = $c_remake = $c_classtagnames = array();
            foreach ($class as $key => $row) 
            {
                $classid[$key] = $row['classid'];
                $classname[$key] = $row['classname'];
                $status[$key] = $row['status'];
                $count[$key] = $row['count'];
                $c_rank[$key] = $row['rank'];
                $c_tagsid[$key] = $row['tagsid'];
                $c_remake[$key] = $row['remake'];
                // $c_classtagnames[$key] = $row['classtagnames'];
            }
            if($_GET['corder'] == 'asc'){
                array_multisort($count, SORT_ASC, SORT_REGULAR, $classid, $classname,$status,$c_rank,$c_tagsid,$c_remake);
            }else{
                array_multisort($count, SORT_DESC, SORT_REGULAR, $classid, $classname,$status,$c_rank,$c_tagsid,$c_remake);
            }
            $class = array();
            $ccount = count($count);
            for($i=0;$i<$ccount;$i++){
                $class[$i]['count'] = $count[$i];
                $class[$i]['classid'] = $classid[$i];
                $class[$i]['classname'] = $classname[$i];
                $class[$i]['status'] = $status[$i];
                $class[$i]['rank'] = $c_rank[$i];
                $class[$i]['tagsid'] = $c_tagsid[$i];
                $class[$i]['remake'] = $c_remake[$i];
                // $class[$i]['classtagnames'] = $c_classtagnames[$i];
            }
        }
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
		$this -> assign('scount',$scount);
		$this -> assign('lr',$lr);
		$this -> assign('p',$p);
        $this -> assign("page", $show);
		$this -> assign('order',($order == 'ASC') ? 'DESC' : 'ASC');
		$this -> assign('corder',$_GET['corder'] == 'asc' ? 'desc' : 'asc');
		$this -> assign('list',$class);
		$this -> assign('taglist',$taglist);
		$this -> assign('tag_result',$tag_result);
		$this -> display();
	}
	
	function rename(){
		$model = D('Zhiyoo.Zhiyoo');
		$groupid  = $_GET['groupid'];
		$group = $model -> table("zy_tagsgroup")->where(array('groupid'=>$groupid))->field('groupname')->find();
		$this -> assign('groupname',$group['groupname']);
		$this -> assign('groupid',$groupid);
		$this -> display();
	}
	
	function add(){
		$model = D('Zhiyoo.Zhiyoo');
		//获取所有的一级分类
		$result = $model ->table('zy_tagsgroup')->field(array('groupid','groupname'))->where('status = 1')->select();
		foreach($result as $val){
			$cat[$val['groupid']] = $val['groupname'];
		}
		$result = $model ->table('zy_tags')->field(array('tagid','tagname','group','parentid'))->where('status = 1')->select();
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
		$this -> display();
	}

	function edit(){
		$model = D('Zhiyoo.Zhiyoo');
		$classid = $_GET['classid'];
		//获取已选标签
		$class = $model -> table("zy_tagsclass")->where(array('classid'=>$classid))->find();
		$classtagid = explode(',',$class['tagsid']);
		
		//获取所有的一级分类
		$result = $model ->table('zy_tagsgroup')->field(array('groupid','groupname'))->where('status = 1')->select();
		foreach($result as $val){
			$cat[$val['groupid']] = $val['groupname'];
		}
		$result = $model ->table('zy_tags')->field(array('tagid','tagname','group','parentid'))->where('status = 1')->select();
		foreach($result as $val){
			$taginfo[$val['group']][] = $val;
			$tagpinfo[$val['parentid']][] = $val;
			$taglist[$val['tagid']] = $val;
		}
		
		$this->assign('class',$class);
		$this->assign('checklist',$classtagid);
		$this->assign('taginfo',$taginfo);
		$this->assign('tagpinfo',$tagpinfo);
		$this->assign('tagpinfo_json',json_encode($tagpinfo));
		$this->assign('taglist',$taglist);
		$this->assign('taglist_json',json_encode($taglist));
		$this->assign('grouplist_json',json_encode($cat));
		$this->assign('cat',$cat);
		$this -> display();
	}
	
	function doedit(){

		$model = D('Zhiyoo.Zhiyoo');
		$action = $_GET['action'];
		if($action == 'edit'){
			$classid  = $_GET['classid'];
			$classname  = $_GET['classname'];
			$status  = $_GET['status'];
			$tagsid  = $_GET['tagsid'];
			if($tagsid){
				dump($tagsid);
				$lengh_tagsid = explode(',',$tagsid);
				if(count($lengh_tagsid)>10){
					$this->error("标签个数最多为10个！");
				}
			}
			$rank  = $_GET['rank'];
			$remake  = $_GET['remake'];
            $result = $model->table("zy_tagsclass")->where(array('classname'=>$clssname,'status'=>1))->find();
			if(!empty($result)){
				$this->error("分类组已存在请重新命名！");
			}
			
			$result = $model->table("zy_tagsclass")->where(array('classid'=>$classid))->save(array('classname'=>$classname,'status'=>$status,'rank'=>$rank,'tagsid'=>$tagsid,'remake'=>$remake));
			$this -> writelog("智友内容管理-内容标签/栏目-标签分类管理 已修改id为{$calssid}的标签组为{$classname}","zy_tagsclass",$classid,__ACTION__ ,"","edit");
		}
		elseif($action == 'del'){
			$classid  = $_GET['classid'];
			$result = $model->table("zy_tagsclass")->where(array('classid'=>$classid))->save(array('status'=>0));
			$this -> writelog("智友内容管理-内容标签/栏目-标签分类管理 已删除标签组ID{$classid}","zy_tagsclass",$classid,__ACTION__ ,"","del");
		}
		elseif($action == 'add'){
			$classname = trim($_POST['classname']);
			$tagsid = trim($_POST['tagsid']) ? trim($_POST['tagsid']) : '';
			if($tagsid){
				$lengh_tagsid = explode(',',$tagsid);
				if(count($lengh_tagsid)>10){
					$this->error("标签个数最多为10个！");
				}
			}
			$remake = $_POST['remake'] ? $_POST['remake'] :'';
			$status = 1;
			$rank = 999;
            $result = $model->table("zy_tagsclass")->where(array('classname'=>$classname,'status'=>1))->find();
			if(!empty($result)){
				$this->error("标签组已存在请重新命名！");
			}else{
                $result = $model->table("zy_tagsclass")->add(array('classname'=>$classname,'status'=>$status,'rank'=>$rank,'tagsid'=>$tagsid,'remake'=>$remake));//新添加标签分类
			}
			$this -> writelog("智友内容管理-内容标签/栏目-标签分类管理 已添加标签分类[{$classname}]","zy_tagsclass",$result,__ACTION__ ,"","add");
			
		}

		$this->assign("jumpUrl", "/index.php/Zhiyoo/TagClass/class_list");
		if($result !== false){
			if($action != 'add')
			$this->success("修改成功！");
			else $this->success("添加成功！");
		}else $this->error("修改失败！");
	}
	function edit_rank(){
		$model = D('Zhiyoo.Zhiyoo');
		if (isset($_POST['level'])){
			$ids = '';
			foreach ($_POST['level'] as $k=>$v){
				$v = abs(intval($v));
				$ids .= $k.',';
				$p_ret = $model -> query("UPDATE zy_tagsclass SET rank='$v' where  classid='$k'");
			}
			$jsonarr = '标签组管理优先级 classid:rank'.json_encode($_REQUEST['level']);
			$this -> writelog($jsonarr,"zy_tagsclass",$ids,__ACTION__ ,"","edit");
			$this->success("编辑优先级成功！");
		}
	}
}