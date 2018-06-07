<?php
/**
 * 安智网产品管理平台 权限管理之控制器
 * ============================================================================
 * 版权所有 2009-2011 北京力天无限网络有限公司，并保留所有权利。
 * 网站地址: http://www.goapk.com；
 * by:金山 2011.5.20
 * ----------------------------------------------------------------------------
 */
class UserPopedomAction extends CommonAction{
    private $sid;               //sessionid、userid
    private $map;               //条件
    private $admin_role_db;     //用户权限对应表
    private $admin_rolelist;    //用户权限对应列表
    private $admin_node_db;     //用户节点对应表
    private $admin_nodelist;    //用户节点对应列表
    private $conf_db;           //常用表
    private $conf_list;         //常用列表
    private $nodeid;            //节点id
    private $lists;             //列表
    //权限管理_权限组列表
    public function index(){

        $this->sid=$_GET['node_group'];
		$node_name = str_ireplace("$","/",$_GET['node_name']);
		$node_purpose = $_GET['node_purpose'];
        if(empty($this->sid)) {
            $this->map='A.node_id=B.node_id AND A.group_id=C.group_id';
        }else
        {
            $this->map='A.node_id=B.node_id  AND A.group_id=C.group_id and A.group_id='.$this->sid;
        }
		if(isset($node_name) && $node_name != "")
		{
			$this->map = $this->map ." AND B.nodename like '%".$node_name."%' ";
		}
		if(isset($node_purpose) && $node_purpose != "")
		{
			$this->map = $this->map ." AND B.postil like'%".$node_purpose."%' ";
		}
		$url_suffix = $this->get_url_suffix(array("node_name","node_purpose","node_group","node_type","p","lr"));
        $this->admin_node_db=M('admin_node');
        import("@.ORG.Page");
        $count= $this->admin_node_db->table('sj_admin_note_group A,sj_admin_node B, sj_admin_node_group C')->where($this->map)->count();
        $Page=new Page($count,50);
        $this->admin_nodelist=$this->admin_node_db->table('sj_admin_note_group A,sj_admin_node B, sj_admin_node_group C')->where($this->map)->field('A.id,A.group_id,C.group_name,B.node_id,B.nodename,B.postil,B.note')->limit($Page->firstRow.','.$Page->listRows)->select();


        $this->conf_db=M('admin_node_group');
        $this->conf_list=$this->conf_db->field('group_id,group_name')->select();
        $this->assign('conflist',$this->conf_list);
        $Page->setConfig('header','篇记录');
        $Page->setConfig('first','<<');
        $Page->setConfig('last','>>');
        $show =$Page->show();
        $this->assign ("page", $show );
        $this->assign ("node_name", $node_name );
        $this->assign ("node_purpose", $node_purpose );
        $this->assign ("node_group", $this->sid );
        $this->assign ("url_suffix", $url_suffix );
        $this->assign("nodelist", $this->admin_nodelist);
        $this->assign('thepid',$this->sid);
        $this->display('userpopedom');

    }
    //权限管理_权限组编辑_显示
    public function editnode() {
        $this->admin_node_db=M('admin_node');
        $this->nodeid=$_GET['nodeid'];
        $this->admin_nodelist = $this->admin_node_db->where(array('node_id'=>$this->nodeid))->select();
        //dump($this->admin_nodelist);
        $this->assign("nodelist", $this->admin_nodelist[0]);
		$node_db=M('admin_node_group');
        $node_list=$node_db->field('group_id,group_name')->select();
        $this->assign('conflist',$node_list);
		$this->assign("nowgroup", $_GET['group_id']);
		$this->assign("linkid", $_GET['linkid']);
       
		$url_suffix = $this->get_url_suffix(array("node_name","node_purpose","node_group","node_type","p","lr"));
        $this->url_suffix = $url_suffix;
        $this->display();

    }
   //权限管理_权限组编辑_执行
    public function node_edit() {

        $this->admin_node_db=M('admin_node');
        $this->nodeid=$_POST['node_id'];
        $this->admin_nodelist['nodename']=$_POST['nodename'];
        $this->admin_nodelist['postil']=$_POST['postil'];
        $this->admin_nodelist['type']=$_POST['type'];

        if(empty($this->admin_nodelist['nodename']) || empty($this->admin_nodelist['postil'])) {
            $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/UserPopedom/nodelist');
            $this->error('对不起,节点名称及节点用途不可为空！');
        }
		$url_suffix = $_POST['url_suffix'];
        $this->admin_nodelist['note']=$_POST['note'];

		$log_result = $this->logcheck(array('node_id'=>$this->nodeid),'sj_admin_node',$this->admin_nodelist,$this->admin_node_db);
        $list = $this->admin_node_db->where(array('node_id'=>$this->nodeid))->save($this->admin_nodelist);
		if(!empty($_POST['group_name']))
		{
			$map = array('group_name' => $_POST['group_name']);
			$model = M('admin_node_group');
			$group_id = $model->add($map);
		}
		else{
			$group_id = $_POST['nodegroup'][0];
		}
		$this->conf_db=M('admin_note_group');
		$link = $this->conf_db->where(array('id'=>$_POST['linkid']))->save(array('group_id' => $group_id));
        if(false!==$list) {
            $this->writelog('编辑了id为'.$this->nodeid.'的权限节点'.$log_result,'sj_admin_note_group',$this->nodeid,__ACTION__ ,"","edit");
            $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/UserPopedom/nodelist'.$url_suffix);
            $this->success("修改成功！");
        }else
        {
            $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/UserPopedom/nodelist');
            $this->error('修改失败！');
        }
    }
    //权限管理_权限组增加_显示
    public function addnode() {
		
		$url_suffix = $this->get_url_suffix(array("node_name","node_purpose","node_group","node_type","p","lr"));
        $this->url_suffix = $url_suffix;
		$node_db=M('admin_node_group');
        $node_list=$node_db->field('group_id,group_name')->select();
        $this->assign('conflist',$node_list);
        $this->display();

    }
    //权限管理_权限组增加_执行
    public function addnode_add() {

        $this->reload();
        $this->admin_node_db=M('admin_node');
        $this->admin_nodelist['nodename']=trim($_POST['nodename']);
        $this->admin_nodelist['postil']=trim($_POST['postil']);
        $this->admin_nodelist['note']=trim($_POST['note']);
        $this->admin_nodelist['type']=trim($_POST['type']);
		$url_suffix = $_POST['url_suffix'];
        if(empty($this->admin_nodelist['nodename']) || empty($this->admin_nodelist['postil'])) {
            $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/UserPopedom/addrole'.$url_suffix);
            $this->error('对不起,节点名称及节点用途不可为空！');
        }

        if($this->conf_list['node_id'] = $this->admin_node_db->where($this->admin_nodelist)->getfield('node_id')) {
            $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/UserPopedom/addrole'.$url_suffix);
            $this->error('对不起,已有该节点！');
        }else
        {
            if($id=$this->admin_node_db->add($this->admin_nodelist)) {
				$this->conf_list['node_id'] = $this->admin_node_db->where($this->admin_nodelist)->getfield('node_id');
                $this->writelog('增加了名字为'.$this->admin_nodelist['nodename'].'的权限节点','sj_admin_node',$id,__ACTION__ ,"","add");
				$i=0;
				if(empty($this->conf_list['group_id']) && !empty($_POST['group_name'])) {//新建分组
					$map = array(
					'group_name' => $_POST['group_name']
					);
					$model = M('admin_node_group');
					$_POST['nodegroup'][0] = $model->add($map);
				}
				while($_POST['nodegroup'][$i])
				{
					$this->conf_db=M('admin_note_group');
					$groupid = $this->conf_list['group_id']= $_POST['nodegroup'][$i] ;
					
					if(empty($this->conf_list['group_id'])) {
						$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/UserPopedom/addnode');
						$this->error('分组ID不能为空');
					}
					if($this->conf_db->where($this->conf_list)->getfield('node_id')) {
						$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/UserPopedom/addnode');
						$this->error('对不起,该类别已有该权限！');
					}else
					{
						if(false!==($result = $this->conf_db->add($this->conf_list))) {
						$this->writelog('增加了分组为'.$this->conf_list['group_id'].'的节点id为'.$this->conf_list['node_id'].'节点权限','sj_admin_note_group',$result,__ACTION__ ,"","add");
						$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/UserPopedom/addnode'.$url_suffix);
						}
					}
					$i++;
				}
				if($result) {
					$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/UserPopedom/addnode'.$url_suffix);
					$this->success("增加权限成功");
				}else
				{
					$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/UserPopedom/addnode'.$url_suffix);
					$this->error("删除失败,发生错误！");
				}
            }else
            {
               $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/UserPopedom/addnode'.$url_suffix);
               $this->error("删除失败,发生错误！");
            }
        }/*
		*/
    }

    //权限管理_权限列表

    public function nodelist() {
    	/*
    	 * 获取筛选条件
    	 */
		 $node_type = 1;
		$node_group = $_GET['node_group'];
		$trans_node_name = $_GET['node_name'];
    	$node_name = str_ireplace("$","/",$trans_node_name);

		$node_purpose = $_GET['node_purpose'];
		if(isset($_GET['node_type']) && $_GET['node_type'] != "")
		{
			$node_type = $_GET['node_type'];
		}
		$this->node_type = $node_type;
		$p = $_GET['p'];
		$lr = $_GET['lr'];
		$this->assign("node_group",$node_group);
		$this->assign("node_name",$node_name);
		$this->assign("node_purpose",$node_purpose);
		$sql = "";
		$url_suffix = $this->get_url_suffix(array("node_name","node_purpose","node_group","node_type","p","lr"));
		// echo $url_suffix;exit;
		$options = array();
		if($node_type == 1)
		{
				$this->map['_string'] = "A.node_id=B.node_id AND A.group_id=C.group_id";
				if(empty($node_group)) {
					$this->map['_string'] = "A.node_id=B.node_id AND A.group_id=C.group_id";
				}else
				{
					$this->map['A.group_id'] = $node_group;
				}
				if(isset($node_name) && $node_name != "")
				{
					$this->map['nodename'] = array('like',"%".$node_name."%");
				}
				if(isset($node_purpose) && $node_purpose != "")
				{
					$this->map['postil'] = array("like","%".$node_purpose."%");
				}

		}else
		{

				
				if(isset($node_name) && $node_name != "")
				{
					$options['nodename'] = array("like","%".$node_name."%");
				}
				if(isset($node_purpose) && $node_purpose != "")
				{
					$options['postil'] = array("like","%".$node_purpose."%");
				}
				if($node_type == 2){
					$subSql = M("admin_note_group")->field("node_id")->buildSql();
					$options['node_id'] = array("not in",$subSql);
				}
				
				
		}

		// }
		//是否导出文件
        $export_csv = $_GET['export_csv']?$_GET['export_csv']:NULL;
		$this->admin_node_db=M('admin_node');

        $this->conf_db=M('admin_node_group');
        $this->conf_list=$this->conf_db->field('group_id,group_name')->select();
        $this->assign('conflist',$this->conf_list);
		if($export_csv == "export")
		{//导出文件
			if($node_type == 1)
			{
				$nodelist=$this->admin_node_db->table('sj_admin_note_group A,sj_admin_node B, sj_admin_node_group C')->where($this->map)->field('B.nodename,B.postil,B.type,C.group_name')->select();
			}else{
				$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/UserPopedom/nodelist'.$url_suffix);
                $this->error("导出失败，只有已分组节点才可导出！");
			}
			$filename = "admin_nodes_".date("YmdHis").".csv";
			header("Content-type:text/csv");
			header("Content-Disposition:attachment;filename=".$filename);
			header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
			header('Expires:0');
			header('Pragma:public');
			$str = "";
			if(empty($nodelist))
			{
				$str.="没有任何节点信息";
			}else
			{
				foreach ($nodelist as $key => $val)
				{
					$str.= "'". iconv('utf-8','gb2312',$val['nodename'])."','". iconv('utf-8','gb2312',$val['postil'])."','". iconv('utf-8','gb2312',$val['type'])."','".iconv('utf-8','gb2312',$val['group_name'])."'\r\n";
				}
			}
			echo $str;
			return;

		}else
		{
			import("@.ORG.Page");
			if($node_type == 1)
			{
				$count= $this->admin_node_db->table('sj_admin_note_group A,sj_admin_node B, sj_admin_node_group C')->where($this->map)->count();
				$Page=new Page($count,50);
				$this->admin_nodelist=$this->admin_node_db->table('sj_admin_note_group A,sj_admin_node B, sj_admin_node_group C')->where($this->map)->field('A.id,A.group_id,C.group_name,B.node_id,B.nodename,B.postil,B.note')->order('node_id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
			}else{
				$count = $this->admin_node_db->where($options)->count();
				$Page=new Page($count,50);
				$this->admin_nodelist=$this->admin_node_db->where($options)->order('node_id desc')->limit($Page->firstRow.','.$Page->listRows)->select();

			}
			$this->conf_db=M('admin_node_group');
			$this->grouplist=$this->conf_db->field('group_id,group_name')->select();
	        $Page->setConfig('header','篇记录');
	        $Page->setConfig('first','<<');
	        $Page->setConfig('last','>>');
	        $show =$Page->show();
	        $this->assign ( "page", $show );
	        $this->assign('nodelist',$this->admin_nodelist);
		}
		$this->url_suffix = $url_suffix;
        $this->display();
    }
    /*原函数nodelist备份
      public function nodelist() {
        $this->admin_node_db=M('admin_node');
        import("@.ORG.Page");
        $count= $this->admin_node_db->count();
        $Page=new Page($count,15);
        $this->admin_nodelist=$this->admin_node_db->order('node_id asc')->limit($Page->firstRow.','.$Page->listRows)->select();
        $Page->setConfig('header','篇记录');
        $Page->setConfig('first','<<');
        $Page->setConfig('last','>>');
        $show =$Page->show();
        $this->assign ( "page", $show );
        $this->assign('nodelist',$this->admin_nodelist);
        $this->display();
    }
     */
    //权限管理_权限节点增加_显示
    public function addrole() {
        $this->admin_node_db=M('admin_node');
       $this->admin_nodelist = $this->admin_node_db->query("select * from sj_admin_node where sj_admin_node.node_id not in (select sj_admin_note_group.node_id from sj_admin_note_group)");

//         $this->admin_nodelist=$this->admin_node_db->order('node_id asc')->select();
        $this->assign('nodelist',$this->admin_nodelist);

		 $url_suffix = "";
		$url_suffix = $this->get_url_suffix(array("node_name","node_purpose","node_group","node_type","p","lr"));
        $this->conf_db=M('admin_node_group');
        $this->conf_list=$this->conf_db->field('group_id,group_name')->select();
        $this->assign('conflist',$this->conf_list);
        $this->assign('url_suffix',$url_suffix);

        $this->display();
    }
    /*addrole备份
    public function addrole() {
    	$this->admin_node_db=M('admin_node');

    	$this->admin_nodelist=$this->admin_node_db->order('node_id asc')->select();
    	$this->assign('nodelist',$this->admin_nodelist);

    	$this->conf_db=M('admin_node_group');
    	$this->conf_list=$this->conf_db->field('group_id,group_name')->select();
    	$this->assign('conflist',$this->conf_list);

    	$this->display();
    }
    */
    //权限管理_权限节点增加_执行
    public function addrole_add() {
        $this->conf_db=M('admin_note_group');
        $groupid = $this->conf_list['group_id']=$_POST['groupid'];
		$url_suffix = $_POST['url_suffix'];
        if(empty($this->conf_list['group_id']) && !empty($_POST['group_name'])) {
            $map = array(
            	'group_name' => $_POST['group_name']
            );
            $model = M('admin_node_group');
            $this->conf_list['group_id'] = $model->add($map);
        }
        if(empty($this->conf_list['group_id'])) {
            $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/UserPopedom/addrole');
            $this->error('分组ID不能为空');
        }
        $this->conf_list['node_id']=$_POST['node_id'];
        if($this->conf_db->where($this->conf_list)->getfield('node_id')) {
            $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/UserPopedom/addrole');
            $this->error('对不起,该类别已有该权限！');
        }else
        {
            if($result=$this->conf_db->add($this->conf_list)) {
			/*
            	$admin_users = $this->conf_db->Table('sj_admin_users AS A, sj_admin_group_map AS B')->where('B.group_id='. $groupid. ' AND A.admin_group=B.admin_group_id')->field('A.admin_user_id')->select();
            	foreach ($admin_users as $admin_user) {
            		$sql = "replace sj_admin_role set admin_id={$admin_user['admin_user_id']}, node_id='{$_POST['node_id']}'";
            		$this->conf_db->query($sql);
            	}
			*/
                $this->writelog('增加了分组为'.$this->conf_list['group_id'].'的节点id为'.$this->conf_list['node_id'].'节点权限','sj_admin_note_group',$result,__ACTION__ ,"","add");
                $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/UserPopedom/addrole'.$url_suffix);
                $this->success("增加权限成功");
            }else
            {
               $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/UserPopedom/addrole'.$url_suffix);
               $this->error("删除失败,发生错误！");
            }
        }

    }
    //权限管理_权限节点删除
    public function addrole_del() {
        $this->nodeid=$_GET['nodeid'];
        if($this->nodeid[0]==',')
        {
            $this->nodeid=substr($this->nodeid,1);
        }
        $this->map['id']=array('in',$this->nodeid);
        $this->conf_db=M('admin_note_group');
        $nodes = $this->conf_db->where($this->map)->select();
		$url_suffix = $this->get_url_suffix(array("node_name","node_purpose","node_group","node_type","p","lr"));
        if(false!==$this->conf_db->where($this->map)->delete())
        {
        	$map = array();
        	$map['node_id'] = array('in',$this->nodeid);
			$this->conf_db->Table('sj_admin_group_note')->where($map)->delete();
			$this->conf_db->Table('sj_admin_role')->where($map)->delete();

			$this->writelog('删除了id为'.$this->nodeid.'的权限','sj_admin_note_group',$this->nodeid,__ACTION__ ,"","del");
            $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/UserPopedom/nodelist'.$url_suffix);
            $this->success("删除成功");
        }else
        {
           $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/UserPopedom/nodelist'.$url_suffix);
     	   $this->error("删除失败,发生错误！");
        }
    }
    //权限管理_权限组删除
    public function delnode() {
        $this->nodeid=$_GET['id'];
        if($this->nodeid[0]==',')
        {
            $this->nodeid=substr($this->nodeid,1);
        }
        $this->map['node_id']=array('in',$this->nodeid);
        $this->admin_node_db=M('admin_node');
		$url_suffix = $this->get_url_suffix(array("node_name","node_purpose","node_group","node_type","p","lr"));
        if(false!==$this->admin_node_db->where($this->map)->delete())
        {
			$model = M('admin_role');
       		$model->where($this->map)->delete();

            $this->writelog('删除了节点id为'.$this->nodeid.'的节点','sj_admin_node',$this->nodeid,__ACTION__ ,"","del");
            $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/UserPopedom/nodelist'.$url_suffix);
            $this->success("删除成功");
        }else
        {
           $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/UserPopedom/nodelist'.$url_suffix);
     	   $this->error("删除失败,发生错误！");
        }
	}
	//导出权限临时列表
	public function node_list_out_selected()
	{
		$cookie = $_COOKIE['selected'];
		if($cookie != '' && preg_match('/^(\d+,)+$/',$cookie))
		{
			$selected = substr($cookie,0,-1);
			$db = M('admin_node');
			$result = $db->query('SELECT C.group_id,C.group_name,C.note g_note,C.status,C.platform,C.rank,B.node_id,B.nodename,B.postil,B.note n_note,B.type FROM sj_admin_note_group A,sj_admin_node B, sj_admin_node_group C WHERE A.node_id = B.node_id AND A.group_id = C.group_id AND A.node_id IN ('.$selected.')');
			$this->assign('arr',$result);			
		}
		$this->display();
	}
	
	public function node_list_out()
	{
		$cookie = $_COOKIE['selected'];
		if($cookie != '' && preg_match('/^(\d+,)+$/',$cookie))
		{
			header("Content-type:text/csv"); 
			header("Content-Disposition:attachment;filename=".time().'.csv'); 
			header('Cache-Control:must-revalidate,post-check=0,pre-check=0'); 
			header('Expires:0'); 
			header('Pragma:public'); 
			
			$out = '"group_id","group_name","g_note","status","platform","rank","node_id","nodename","postil","n_note","type"'."\n"; 
			
			$selected = substr($cookie,0,-1);
			$db = M('admin_node');
			$result = $db->query('SELECT C.group_id,C.group_name,C.note g_note,C.status,C.platform,C.rank,B.node_id,B.nodename,B.postil,B.note n_note,B.type FROM sj_admin_note_group A,sj_admin_node B, sj_admin_node_group C WHERE A.node_id = B.node_id AND A.group_id = C.group_id AND A.node_id IN ('.$selected.')');
			$i = 0;
			while($result[$i])
			{
				$out .= '"'.$result[$i]['group_id'].'","'.$result[$i]['group_name'].'","'.$result[$i]['g_note'].'","'.$result[$i]['status'].'","'.$result[$i]['platform'].'","'.$result[$i]['rank'].'","'.$result[$i]['node_id'].'","'.$result[$i]['nodename'].'","'.$result[$i]['postil'].'","'.$result[$i]['n_note'].'","'.$result[$i]['type']."\"\n";
				$i++;
			}
			
			$out = iconv('utf-8','gb2312',$out); 
			echo $out;
			setcookie("selected",'',time()-100,'/');
		}
		else echo '未选择节点';
		
	}
	public function node_list_t()
	{
	var_dump($_POST);
		
	}
	public function node_list_in()
	{
		if ($_GET['action'] == 'in') { //导入CSV 
		$filename = $_FILES['csvin']['tmp_name']; 
		if (empty ($filename)) { 
			echo '请选择要导入的CSV文件！'; 
			exit; 
		} 
		$handle = fopen($filename, 'r'); 
		$result = fgetcsv($handle);
		if(!$result)exit('空文件');
		while($result = fgetcsv($handle))
		{
			$group_name = iconv('gb2312', 'utf-8', $result[1]); //中文转码 
			$g_note = iconv('gb2312', 'utf-8', $result[2]); 
			$status = iconv('gb2312', 'utf-8', $result[3]); 
			$platform = iconv('gb2312', 'utf-8', $result[4]); 
			$rank = iconv('gb2312', 'utf-8', $result[5]); 
			$nodename = iconv('gb2312', 'utf-8', $result[7]); 
			$postil = iconv('gb2312', 'utf-8', $result[8]); 
			$n_note = iconv('gb2312', 'utf-8', $result[9]); 
			$type = iconv('gb2312', 'utf-8', $result[10]); 
			$db = M('admin_node');//节点表
			$where = null;
			$where['nodename'] = $nodename;
			if($row = $db->where($where)->select())//已存在节点，不导入
			{
				$cache['nodeid'] = $row[0]['node_id'];
				$cache['result'] = '已存在，未导入';
			}
			else{
				$values = null;
				$values['nodename'] = $nodename;//导入节点信息
				$values['postil'] = $postil;
				$values['note'] = $n_note;
				$values['type'] = $type;
				$cache['nodeid'] = $db->add($values);//入库
				$row = $db->where($where)->select();//返回插入节点ID值
				$cache['nodeid'] = $row[0]['node_id'];
				$db = M('admin_node_group');//组表 //导入节点组信息
				$where = null;
				$where['group_name'] = $group_name;
				if($row = $db->where($where)->select())//组已存在
				{
					$cache['group_id'] = $row[0]['group_id'];
				}
				else{
					$values = null;
					$values['group_name'] = $group_name;
					$values['note'] = $g_note;
					$values['status'] = $status;
					$values['platform'] = $platform;
					$values['rank'] = $rank;
					$db->add($values);//入库
					$row = $db->where($where)->select();//返回插入组ID值
					$cache['group_id'] = $row[0]['group_id'];
				}
				//关联 节点、组 信息
				$values = null;
				$db = M('admin_note_group');
				$values['group_id'] = $cache['group_id'];
				$values['node_id'] = $cache['nodeid'];
				$db->add($values);
				$cache['result'] = '导入成功';
			}
			$arr1['node_id'] =  $cache['nodeid'];
			$arr1['group_name'] =  $group_name;
			$arr1['nodename'] =  $nodename;
			$arr1['postil'] =  $postil;
			$arr1['n_note'] =  $n_note;
			$arr1['result'] =  $cache['result'];
			$arr[] = $arr1;
		} 
		$this->assign('arr',$arr);
		} 
		$this->display();
	}
    //权限管理_权限复制
	public function permission_copy()
	{
		$this->display();
	}
	//权限管理_权限复制_执行
	public function permission_copy_do()
	{
			$c_name = $_POST['c_name'];
			$t_name = $_POST['t_name'];
			if(!isset($c_name) || $c_name == "" || !isset($t_name) || $t_name == "")
			{
				$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/UserPopedom/permission_copy');
				$this->error("请完整填写用户名！");
			}
			//去除两个用户的admin_id
			$admin_user_db = M("admin_users");
			$c_admin_id = $admin_user_db->where("admin_user_name='".$c_name."'")->getfield("admin_user_id");
			$t_admin_id = $admin_user_db->where("admin_user_name='".$t_name."'")->getfield("admin_user_id");
			if(!isset($c_admin_id) || empty($c_admin_id) || $c_admin_id == false)
			{
				$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/UserPopedom/permission_copy');
				$this->error("权限来源者姓名错误！");
			}
			if(!isset($t_admin_id) || empty($t_admin_id) || $t_admin_id == false)
			{
				$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/UserPopedom/permission_copy');
				$this->error("被赋权限者姓名错误！");
			}
			//根据admin_id号选出c_user中含有而t_user中没有的权限
			$sql = "select node_id from sj_admin_role where admin_id=$c_admin_id AND node_id not in (select node_id from sj_admin_role where admin_id=$t_admin_id)";
			$this->admin_role_db = M("admin_role");
			$toAddedNodeId = $this->admin_role_db->query($sql);
			if(count($toAddedNodeId) > 0)
			{
				$data = array();
				$data['admin_id'] = $t_admin_id;
				$data['type'] = 1;
				//循环复制权限
				foreach ($toAddedNodeId as $val)
				{
					$data['node_id'] = $val['node_id'];
					$this->admin_role_db->add($data);
				}
				$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/UserPopedom/permission_copy');
				$this->success("权限复制成功！！");
			}else{
				$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/UserPopedom/permission_copy');
				$this->error("两者权限一样，无需复制！");
			}
	}

	public function admin_permission_copy()
	{
		$this->display();
	}

	public function admin_permission_copy_do()
	{
			$c_name = $_POST['c_name'];
			$t_name = $_POST['t_name'];
			if(!isset($c_name) || $c_name == "" || !isset($t_name) || $t_name == "")
			{
				$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/UserPopedom/admin_permission_copy');
				$this->error("请完整填写用户名！");
			}
			//去除两个用户的admin_id
			$admin_users_db = M("admin_users");
			$c_admin_id = $admin_user_db->where("admin_user_name='".$c_name."'")->getfield("admin_user_id");
			$t_admin_id = $admin_user_db->where("admin_user_name='".$t_name."'")->getfield("admin_user_id");
			if(!isset($c_admin_id) || empty($c_admin_id) || $c_admin_id == false)
			{
				$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/UserPopedom/admin_permission_copy');
				$this->error("权限来源者姓名错误！");
			}
			if(!isset($t_admin_id) || empty($t_admin_id) || $t_admin_id == false)
			{
				$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/UserPopedom/admin_permission_copy');
				$this->error("被赋权限者姓名错误！");
			}
			//根据admin_id号选出c_user中含有而t_user中没有的权限
			$sql = "SELECT target_value,filter_type FROM sj_admin_filter WHERE source_value=$c_admin_id AND target_type=2 AND sourc_type=1 AND target_value NOT IN (SELECT target_value FROM sj_admin_filter WHERE source_value=$t_admin_id AND target_type=2 AND sourc_type=1)";
			$admin_filter_db = M("admin_filter");
			$premission = $admin_filter_db->query($sql);
			if(count($premission) > 0)
			{
				$data = array();
				$data['target_type'] = 1;
				$data['source_value'] = $t_admin_id;
				$data['target_type'] = 2;
				$data['addtime'] = time();
				//循环复制权限
				foreach ($premission as $val)
				{
					$data['target_value'] = $val['target_value'];
					$data['filter_type'] = $val['filter_type'];
					$admin_filter_db->add($data);
				}
				$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/UserPopedom/permission_copy');
				$this->success("权限复制成功！！");
			}else{
				$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/UserPopedom/permission_copy');
				$this->error("两者权限一样，无需复制！");
			}
	}
}
?>