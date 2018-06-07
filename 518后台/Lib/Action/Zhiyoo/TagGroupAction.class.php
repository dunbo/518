<?php 

Class TagGroupAction extends CommonAction{
	
	function group_list(){
// 		$sql = $_GET['nameorder'] == 'asc' ? 'groupname asc' : 'groupname desc';
		$order = 'ASC';$orderBy = 'groupid '.$order;
		if (isset($_REQUEST['field']) and isset($_REQUEST['order'])) {
			$field = addslashes(trim($_REQUEST['field']));
			$order = addslashes(trim($_REQUEST['order']));
			$orderBy = "$field $order";
		}
		$model = D('Zhiyoo.Zhiyoo');
		$group = $model ->table("zy_tagsgroup")->where(array('status'=>1))->order($orderBy)->select();//读取一级目录，按照优先级升序排列
		foreach($group as $key => $val){
			$count = $model -> table("zy_tags")->where(array('status'=>array('neq',-1),'parentid'=>0,'group'=>$val['groupid']))->count();
			$group[$key]['count'] = $count;
		}
        if(isset($_GET['corder'])){
            $groupid = $groupname = $status = $count = $g_rank = array();
            foreach ($group as $key => $row) 
            {
                $groupid[$key] = $row['groupid'];
                $groupname[$key] = $row['groupname'];
                $status[$key] = $row['status'];
                $count[$key] = $row['count'];
                $g_rank[$key] = $row['rank'];
            }
            if($_GET['corder'] == 'asc'){
                array_multisort($count, SORT_ASC, SORT_REGULAR, $groupid, $groupname,$status,$g_rank);
            }else{
                array_multisort($count, SORT_DESC, SORT_REGULAR, $groupid, $groupname,$status,$g_rank);
            }
            $group = array();
            $gcount = count($count);
            for($i=0;$i<$gcount;$i++){
                $group[$i]['count'] = $count[$i];
                $group[$i]['groupid'] = $groupid[$i];
                $group[$i]['groupname'] = $groupname[$i];
                $group[$i]['status'] = $status[$i];
                $group[$i]['rank'] = $g_rank[$i];
            }
        }
        
		$this -> assign('order',($order == 'ASC') ? 'DESC' : 'ASC');
		$this -> assign('corder',$_GET['corder'] == 'asc' ? 'desc' : 'asc');
		$this -> assign('list',$group);
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
		$this -> display();
	}
	
	function doedit(){
		$model = D('Zhiyoo.Zhiyoo');
		$action = $_GET['action'];
		if($action == 'rename'){
			$groupid  = $_POST['groupid'];
			$name = trim($_POST['name']);
            $result = $model->table("zy_tagsgroup")->where(array('groupname'=>$name,'status'=>1,'groupid'=>array('neq',$groupid)))->find();
			if(!empty($result)){
				$this->error("分类已存在请重新命名！");
			}
			$result = $model->table("zy_tagsgroup")->where(array('groupid'=>$groupid))->save(array('groupname'=>$name));
			$this -> writelog("智友内容管理-内容标签/栏目-标签分类管理 已重命名id为{$groupid}的标签分类为{$name}","zy_tagsgroup",$groupid,__ACTION__ ,"","edit");
		}
		elseif($action == 'del'){
			$groupid  = $_GET['groupid'];
			$result = $model->table("zy_tagsgroup")->where(array('groupid'=>$groupid))->save(array('status'=>0));
			$this -> writelog("智友内容管理-内容标签/栏目-标签分类管理 已删除标签分类ID{$groupid}","zy_tagsgroup",$groupid,__ACTION__ ,"","del");
		}
		elseif($action == 'add'){
			$name = trim($_POST['name']);
			
            $result = $model->table("zy_tagsgroup")->where(array('groupname'=>$name,'status'=>1))->find();
			if(!empty($result)){
				$this->error("分类已存在请重新命名！");
			}else{
                $result = $model->table("zy_tagsgroup")->add(array('groupname'=>$name));//新添加标签分类
			}
			$this -> writelog("智友内容管理-内容标签/栏目-标签分类管理 已添加标签分类[{$name}]","zy_tagsgroup",$result,__ACTION__ ,"","add");
			
		}
		$this->assign("jumpUrl", "/index.php/Zhiyoo/TagGroup/group_list");
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
				$p_ret = $model -> query("UPDATE zy_tagsgroup SET rank='$v' where  groupid='$k'");
			}
			$jsonarr = '标签分类管理优先级 groupid:rank'.json_encode($_REQUEST['level']);
			$this -> writelog($jsonarr,"zy_tagsgroup",$ids,__ACTION__ ,"","edit");
			$this->success("编辑优先级成功！");
		}
	}
}