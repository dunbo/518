<?php
/**
 * 安智网产品管理平台 管理员管理之控制器
 * ============================================================================
 * 版权所有 2009-2011 北京力天无限网络有限公司，并保留所有权利。
 * 网站地址: http://www.goapk.com；
 * by:金山 2010.5.18
 * ----------------------------------------------------------------------------
*/
class WebAdminAction extends CommonAction {
    private $uid;           //类型id
    private $adminuser_db;  //用户文章表
    private $adminuserlist; //用户文章列表
    private $map;           //存储条件
    private $conf_db;       //配置文件
    private $hashs;         //hash
    private $checkid;       //检测id
    private $adminrole_db;  //管理员权限对应表
    private $adminnode_db;  //管理员权限节点表
    private $admingroup_db; //管理员权限分组表
    private $adminlog_db;   //管理员日志表
    private $adminrolelist; //管理员权限对应表
    private $admingrouplist;//管理员分组列表
    private $adminnodelist; //管理员节点列表
    private $adminloglist;  //管理员日志列表
    private $checks;        //选中列表
    private $lists;         //信息列表
    private $type_id;       //类型id
    private $begintime;     //开始时间
    private $endtime;       //结束时间
    private $user_data_db;  //会员附加信息表
    private $plist;         //归属列表

    //管理员管理_管理员列表
	public function index() {
        import("@.ORG.Page");
        $this->adminuser_db=M('admin_users');
		
		$where = array();
		if (isset($_GET['admin_group'])) {
			$where['A.admin_group'] = $_GET['admin_group'];
			$this->assign ("admin_group", $_GET['admin_group'] );
		}
		
		if (isset($_GET['admin_user_name'])) {
			$where['A.admin_user_name'] = array('like', '%' . mysql_escape_string($_GET['admin_user_name']) . '%');
			$this->assign ("admin_user_name", $_GET['admin_user_name'] );
		}
		
        $count= $this->adminuser_db->table('sj_admin_users A')->where($where)->count();
        $Page=new Page($count,10);
        $this->adminuserlist = $this->adminuser_db->table('sj_admin_users A LEFT JOIN  sj_admin_group B ON A.admin_group=B.group_id')
			->where($where)
        	->limit($Page->firstRow.','.$Page->listRows)
        	->field('A.*, B.group_name')
        	-> order('admin_user_id desc')
        	-> select();
        	
        //dump($this->adminuserlist);
		
        $admingroup_db=M('admin_group');
        $admingrouplist=$admingroup_db->select();
        $Page->setConfig('header','篇记录');
        $Page->setConfig('first','<<');
        $Page->setConfig('last','>>');
        $show =$Page->show();
        $this->assign ("page", $show );
        $this->assign('adminlist',$this->adminuserlist);
        $this->assign('admingrouplist',$admingrouplist);
        $this->display();
	}
    //管理员管理_管理员开启停用
    public function admindel() {
		//登录管理员密码验证,开始
		if(!$this->login_pwd_chk()) {
			$this->writelog(($_GET['state']==0 ? '停用' : '启用')."管理员{$this->getNameById($_GET['uid'])}操作时，密码验证失败");
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/WebAdmin/index');
			$this->error('密码验证失败，操作失败！');
		}
		//登录管理员密码验证,结束
        $this->uid=$_GET['uid'];
        $this->adminuser_db=M('admin_users');
        if($_GET['state']) {
            $this->map['admin_state']=1;
            $this->writelog('启用ID为['.$this->uid.']的管理员','sj_admin_users',$this->uid,__ACTION__ ,"","edit");
        }else
        {
            $this->writelog('停用ID为['.$this->uid.']的管理员','sj_admin_users',$this->uid,__ACTION__ ,"","edit");
            $this->map['admin_state']=0;
            $this->map['sessionid']=0;
        }
        if(false!==$this->adminuser_db->where(array('admin_user_id'=>$this->uid))->save($this->map)) {
            //修改用户最后更新时间
			$admin_users = M('admin_users');
			$data['admin_user_id']= $this->uid;
			$data['update_time']  = time();
			$admin_users->save($data);
            $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/WebAdmin/index');
            $this->success("操作成功");
        }else
        {
            $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/WebAdmin/index');
            $this->error('操作失败');
        }
    }
    //管理员管理_管理员删除
    public function adminremove() {
		//登录管理员密码验证,开始
		if(!$this->login_pwd_chk()) {
			$this->writelog("删除管理员{$this->getNameById($_GET['uid'])}操作时，密码验证失败");
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/WebAdmin/index');
			$this->error('密码验证失败，操作失败！');
		}
		//登录管理员密码验证,结束
        $this->uid=$_GET['uid'];
        $this->adminuser_db=M('admin_users');
        if(false!==$this->adminuser_db->where(array('admin_user_id'=>$this->uid))->delete()) {
		    $this->writelog('删除ID为['.$this->uid.']的管理员','sj_admin_users',$this->uid,__ACTION__ ,"","del");
            $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/WebAdmin/index');
            $this->success("操作成功");
        }else
        {
            $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/WebAdmin/index');
            $this->error('操作失败');
        }
    }
    
    //管理员管理_管理员信息编辑_显示
    public function adminedit() {
        $this->uid=$_GET['uid'];
        $this->adminuser_db=M('admin_users');
        $this->adminuserlist=$this->adminuser_db->where(array('admin_user_id'=>$this->uid))->select();
        $this->assign('adminlist',$this->adminuserlist[0]);

        $this->admingroup_db=M('admin_group');
		$this->admingrouplist=$this->admingroup_db->where('status=1')->field('group_id,group_name')->select();
        $this->assign('list',$this->admingrouplist);
        $this->display();
    }
    //管理员管理_管理员信息编辑_执行
    public function adminedit_edit() {
		
		//登录管理员密码验证,开始
		if(!$this->login_pwd_chk()) {
			$this->writelog("编辑管理员{$_POST['admin_user_name']}基本信息时，密码验证失败");
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/WebAdmin/index');
			$this->error('密码验证失败，操作失败！');
		}
		//登录管理员密码验证,结束
        $this->uid=$_GET['uid'];
        $this->adminuser_db=M('admin_users');
        $this->adminuserlist['admin_user_name']=trim($_POST['admin_user_name']);
        $this->adminuserlist['admin_state']=$_POST['admin_state'];
		$this->adminuserlist['admin_group'] = $_POST['admin_group'];
		$this->adminuserlist['note'] = $_POST['note'];
		if (!empty($_POST['admin_user_email'])) {
			$this->adminuserlist['admin_user_email'] = $_POST['admin_user_email'];
		}
		$user = $this->adminuser_db->where(array('admin_user_id'=>$this->uid))->field('admin_group, admin_user_id')->select();
        if(!empty($_POST['editpassword'])) {	    
			if(!preg_match("/^(?![0-9]+$)(?![a-zA-Z]+$)[0-9A-Za-z\#\!@\$\%\^\(\)]{8,32}$/",$_POST['admin_user_password'])){
				$this -> error('密码必须是大小字符和数字的混合，长度在8-32之间，请重新设置');
			}
            $this->conf_db = D('Sj.Config');
            $this->map['configname']='hash';
			$this->map['status']=1;
            $this->hashs=$this->conf_db->where($this->map)->getfield('configcontent');
            $this->adminuserlist['admin_user_password']=md5($_POST['admin_user_password'].$this->hashs);
            $this->adminuserlist['plain_password'] = $this->encrypt($_POST['admin_user_password']);
        }
		$log_result = $this->logcheck(array('admin_user_id'=>$this->uid),'sj_admin_users',$this->adminuserlist,$this->adminuser_db);
        $list = $this->adminuser_db->where(array('admin_user_id'=>$this->uid))->save($this->adminuserlist);
        
		import("@.ORG.Input");
        if(false!==$list) {

			    //判断管理员所属组是否更改
				if ($user[0]['admin_group'] != Input::getVar($_POST['admin_group'])) {
					//删除组信息并清空权限节点
					$model = M('sj_admin_role');
					$model->Table('sj_admin_role')->where(array('admin_id'=>$this->uid))->delete();
					
					$nodes = $model->Table('sj_admin_group_note')->where(array('group_id'=>$_POST['admin_group']))->field('node_id')->select();
					foreach ($nodes as $node_item) {
						$sql = "replace into sj_admin_role set admin_id={$this->uid}, node_id='{$node_item['node_id']}'";
						$model->query($sql);
					}
				}
            //修改用户最后更新时间
			$admin_users = M('admin_users');
			$data['admin_user_id']= $this->uid;
			$data['update_time']  = time();
			if(!empty($_POST['editpassword'])) {	
				$data['last_pwd_tm']  = time();
			}
            $log_result = $this->logcheck(array('admin_user_id'=>$this->uid),'sj_admin_users',$data,$admin_users);
			$admin_users->save($data);
            $this->writelog('编辑ID为['.$this->uid.']的管理员基本信息'.$log_result,'sj_admin_users',$this->uid,__ACTION__ ,"","edit");
            $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/WebAdmin/index');
            $this->success("修改成功！");
        }else
        {
            $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/WebAdmin/index');
            $this->error('修改失败！');
        }

    }
    //管理员管理_添加管理员
    public function adminadd() {
        $this->admingroup_db=M('admin_group');
		$this->admingrouplist=$this->admingroup_db->where('status=1')->field('group_id,group_name')->select();
        $this->assign('list',$this->admingrouplist);
        //dump($this->admingrouplist);
        $this->display();
    }
    //管理员添加执行
    public function adminuseradd() {
		$time = time();
		//登录管理员密码验证,开始
		if(!$this->login_pwd_chk()) {
			$this->writelog("添加管理员{$_POST['admin_user_name']}时，密码验证失败");
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/WebAdmin/adminuseradd');
			$this->error('密码验证失败，操作失败！');
		}
		//登录管理员密码验证,结束
        $this->adminuser_db=M('admin_users');
        $this->adminuserlist['admin_user_name']=trim($_POST['admin_user_name']);
        $this->conf_db = D('Sj.Config');
        $this->map['configname']='hash';
		$this->map['status']=1;
        $this->hashs=$this->conf_db->where($this->map)->getfield('configcontent');
        $this->adminuserlist['admin_user_password']=md5($_POST['admin_user_password'].$this->hashs);
		$this->adminuserlist['admin_state'] = $_POST['admin_state'];
		$this->adminuserlist['admin_group'] = $_POST['group_id'];
		$this->adminuserlist['note'] = $_POST['note'];
		//$this->adminuserlist['last_logintime'] = $time;
		$this->adminuserlist['add_tm'] = $time;
		$this->adminuserlist['last_pwd_tm'] = $time;
		$this->adminuserlist['update_time'] = $time;
		$this->adminuserlist['plain_password'] = $this->encrypt($_POST['admin_user_password']);
		if (!empty($_POST['admin_user_email'])) {
			$this->adminuserlist['admin_user_email'] = $_POST['admin_user_email'];
		}
        if(empty($this->adminuserlist['admin_user_name']) || empty($_POST['admin_user_password'])  ) {
            $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/WebAdmin/adminadd');
            $this->error('对不起,用户名、密码不可为空');
            exit;
        }
        $this->checkid=$this->adminuser_db->where(array(admin_user_name=>$this->adminuserlist['admin_user_name']))->getfield('admin_user_id');
        if(!empty($this->checkid)) {
            $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/WebAdmin/adminadd');
            $this->error('对不起,用户名已经存在');
            exit;
        }

        if($admin_user_id = $this->adminuser_db->add($this->adminuserlist)) {
        	$this->adminrole_db=M('admin_role');
        	$node_list = $this->adminrole_db->Table('sj_admin_group_note')->where(array('group_id'=>$_POST['group_id']))->field('node_id')->select();
        	
        	foreach ($node_list as $node) {
	        	$role = array();
	        	$role['admin_id'] = $admin_user_id;
	        	$role['node_id'] = $node['node_id'];
	        	$role['type'] = 1;
	        	$this->adminrole_db->add($role);
        	}
        	
            $this->writelog('创建姓名为['.$this->adminuserlist['admin_user_name'].']的管理员','sj_admin_users',$admin_user_id,__ACTION__ ,"","add");
            $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/WebAdmin/index');
            $this->success("添加成功！");
        }else
        {
            $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/WebAdmin/index');
            $this->error('添加失败！');
        }
    }
    //管理员管理_管理员权限检查_查看
    public function admincommision() {         //权限列表_显示
        $this->uid=escape_string($_GET['id']);
		$admin_group_id = $_SESSION['admin']['admin_group'];

		$model = M('admin_note_group');
        $node_group_list_result = $model->Table('sj_admin_node_group')->select();

		$node_list_result = $model
			->Table('sj_admin_node B, sj_admin_note_group AS C')
			->where('C.node_id=B.node_id')
			->field('B.node_id, B.nodename, B.postil, B.type, B.note, C.group_id')
			->select();

        
		$admin_node_list_result = $model
			->Table('sj_admin_group_note A,sj_admin_node B, sj_admin_note_group AS C')
			->where('A.group_id="'.$this->uid.'" AND A.node_id=B.node_id AND C.node_id=B.node_id')
			->field('B.node_id, B.nodename, B.postil, B.type, B.note, C.group_id')
			->select();
		
		$admin_node_list = array();
		foreach ($admin_node_list_result as $node) {
			$admin_node_list[$node['node_id']] = $node;
		}

		$node_group_list = array();
        foreach($node_group_list_result as $key => $value)     // 循环判断该类别是否被选中
        {
        	$group_id = $value['group_id'];
        	$node_group_list[$group_id] = $value;
        	$node_group_list[$group_id]['nodes'] = array();
        	$node_group_list[$group_id]['checked'] = false;
        }
		foreach ($node_list_result as $node) {
			$node_id = $node['node_id'];
			$group_id = $node['group_id'];
			$node['checked'] = isset($admin_node_list[$node_id]);
			$node_group_list[$group_id]['nodes'][] = $node;
			isset($admin_node_list[$node_id]) && $node_group_list[$group_id]['checked'] = true;
		}

        $this->assign('theadminuid',$this->uid);
        $this->assign('list', $node_group_list);
        $this->display();

    }
    //管理员管理_管理员权限检查_更改
    public function upadmincommision() {
		//登录管理员密码验证,开始
		if(0 && !$this->login_pwd_chk('_login_password_ag_com')) {
			$this->writelog("管理员分组的权限管理时，密码验证失败");
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/WebAdmin/admincommision/id/'.$_GET['id']);
			$this->error('密码验证失败，操作失败！');
		}
		//登录管理员密码验证,结束

        $this->uid=escape_string($_GET['id']);

        $model = M('admin_group_note');
        $admin_group_list = $model->Table('sj_admin_group_note A')->where('A.group_id='. $this->uid)->field('A.node_id')->select();
        $new_checks = array();
		$node_value = explode(',', $_POST['node_value']);
		foreach ($node_value as $node) {
			if (!empty($node) && is_numeric($node)){
				$new_checks[] = $node;
			}
		}
		$old_checks = array();
		foreach ((array)$admin_group_list as $key=>$item) {
			$old_checks[] = $item['node_id'];
		}
        $delete_node = array_diff($old_checks, $new_checks);
        $new_node = array_diff($new_checks, $old_checks);
        $text='授予用户ID为['.$this->uid.']';       //临时存储修改项目
        
        foreach ($delete_node as $node) {
        	$sql = "delete from sj_admin_group_note where group_id={$this->uid} and node_id='{$node}' limit 1\n";
	        $model->query($sql);
        	$this->writelog('用户ID为['.$this->uid."],删除了节点{$node}",'sj_admin_group_note',$this->uid,__ACTION__ ,"","del");
        	$admin_users = $model->Table('sj_admin_users AS A')->where('A.admin_group='. $this->uid)->field('A.admin_user_id')->select();
        	foreach ($admin_users as $admin_user) {
        		$sql = "delete from sj_admin_role where admin_id={$admin_user['admin_user_id']} and node_id='{$node}' limit 1\n";
        		$model->query($sql);
                $this->writelog('admin_id为['.$admin_user['admin_user_id']."],删除了节点{$node}",'sj_admin_role',$admin_user['admin_user_id'],__ACTION__ ,"","del");
        	}
        }
        
        $sj_admin_group_note_sql = "insert into sj_admin_group_note (`group_id`, `node_id`) values ";
        $sj_admin_group_note_arr = array();

        $sj_admin_role_sql = "insert into sj_admin_role (`admin_id`, `node_id`) values ";
        $sj_admin_role_arr = array();

        $admin_users = $model->Table('sj_admin_users AS A')->where('A.admin_group='. $this->uid)->field('A.admin_user_id')->select();
        foreach ($new_node as $node) {
            $sj_admin_group_note_arr[] = "({$this->uid}, {$node})";
            
            foreach ($admin_users as $admin_user) {
                $sj_admin_role_arr[] = "({$admin_user['admin_user_id']}, {$node})";
            }
        }
        $tmp = array_chunk($sj_admin_group_note_arr, 500);
        foreach ($tmp as $chunk) {
            $sql = $sj_admin_group_note_sql. implode(',', $chunk);
            $model->query($sql);
            $this->writelog("group_id为{$this->uid},添加了节点{$node}",'sj_admin_group_note',$this->uid,__ACTION__ ,"","add");
        }

        $tmp = array_chunk($sj_admin_role_arr, 500);
        foreach ($tmp as $chunk) {
            $sql = $sj_admin_role_sql. implode(',', $chunk). ' ON DUPLICATE KEY UPDATE type=type';
            $model->query($sql);
            $this->writelog("admin_id为{$admin_user['admin_user_id']},添加了节点{$node}",'sj_admin_role',$admin_user['admin_user_id'],__ACTION__ ,"","add");
        }
        
        if($text=='')
        {
            //$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/WebAdmin/admingroup_list');
            //$this->error('您没有对管理员权限进行修改！');
        }
        else
        {   $this->writelog($text);
            $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/WebAdmin/admingroup_list');
            $this->success($text);
        }
    }
    //管理员管理_管理员分组列表
    public function admingroup_list() {

        import("@.ORG.Page");
        $this->admingroup_db=M('admin_group');
        $count= $this->admingroup_db->count();
        $Page=new Page($count,10);
        $this->admingrouplist=$this->admingroup_db->limit($Page->firstRow.','.$Page->listRows)->select();
        $Page->setConfig('header','篇记录');
        $Page->setConfig('first','<<');
        $Page->setConfig('last','>>');
        $show =$Page->show();
        $this->assign ("page", $show );
        $this->assign('grouplist',$this->admingrouplist);
        //dump($this->grouplist);

        $this->display();
    }
    //管理员管理_添加管理组_显示
    public function admingroup_add() {
        $this->display();
    }

    //管理员管理_添加管理组_执行
    public function admingroupadd() {
		//登录管理员密码验证,开始
		if(!$this->login_pwd_chk('_login_password_aga')) {
			$this->writelog("添加后台管理员分组({$_POST['group_name']})时，密码验证失败");
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/WebAdmin/admingroup_add');
			$this->error('密码验证失败，操作失败！');
		}
		//登录管理员密码验证,结束
        $this->admingroup_db=M('admin_group');
        $this->admingrouplist['group_name']=trim($_POST['group_name']);
		$this->admingrouplist['status'] = $_POST['status'];
		$this->admingrouplist['note'] = $_POST['note'];

        if(empty($this->admingrouplist['group_name']) ) {
            $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/WebAdmin/admingroup_list');
            $this->error('对不起,分组名称不可为空！');
        }


        $this->checkid=$this->admingroup_db->where(array("group_name"=>$this->admingrouplist['group_name']))->getfield('group_id');

        if(!empty($this->checkid)) {
            $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/WebAdmin/admingroup_list');
            $this->error('对不起,用户名已经存在');
            exit;
        }

        if($id=$this->admingroup_db->add($this->admingrouplist)) {
            $this->writelog('创建分组名为['.$this->admingrouplist['group_name'].']的管理分组','sj_admin_group',$id,__ACTION__ ,"","add");
            $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/WebAdmin/admingroup_list');
            $this->success("添加成功！");
        }else
        {
            $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/WebAdmin/admingroup_list');
            $this->error('添加失败！');
        }
    }
    //管理员管理_管理组开启停用
    public function admingroupdel() {
		//登录管理员密码验证,开始
		if(!$this->login_pwd_chk('_login_password_agl')) {
			$this->writelog("停用管理员分组(ID:{$_GET['id']})时，密码验证失败");
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/WebAdmin/admingroup_list');
			$this->error('密码验证失败，操作失败！');
		}
		//登录管理员密码验证,结束

        $this->uid=$_GET['id'];
        $this->admingroup_db=M('admin_group');
        if($_GET['state']) {
            $this->map['status']=1;
            $this->writelog('启用ID为['.$this->uid.']的管理员分组','sj_admin_group',$this->uid,__ACTION__ ,"","edit");
        }else
        {
            $this->writelog('停用ID为['.$this->uid.']的管理员分组','sj_admin_group',$this->uid,__ACTION__ ,"","edit");
            $this->map['status']=0;
        }
        if(false!==$this->admingroup_db->where(array('group_id'=>$this->uid))->save($this->map)) {

            if($this->map['status']==0) {
                $this->adminuser_db=M('admin_users');
                $this->adminuserlist=$this->adminuser_db->where(array('admin_group'=>$this->uid))->field('admin_user_id')->select();
                for($i=0;$i<count($this->adminuserlist);$i++) {
                    $thumbmap='';
                    $thumbmap['sessionid']='0';
                    $this->adminuser_db->where('admin_user_id='.$this->adminuserlist[$i]['admin_user_id'].'')->save($thumbmap);
                }
            }
            $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/WebAdmin/admingroup_list');
            $this->success("操作成功");
        }else
        {
            $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/WebAdmin/admingroup_list');
            $this->error('操作失败');
        }
    }
    //管理员管理_管理组信息编辑_执行
    public function admingroupedit() {
        $this->uid=$_GET['id'];
        $this->admingroup_db=M('admin_group');
        $this->admingrouplist=$this->admingroup_db->where(array('group_id'=>$this->uid))->find();
        $this->assign('adminlist',$this->admingrouplist);

        //dump($this->admingrouplist);
        $this->display();
    }


    //管理员管理_管理组信息编辑_执行
    public function admingroupedit_edit() {
		//登录管理员密码验证,开始
		if(!$this->login_pwd_chk('_login_password_agedit')) {
			$this->writelog("编辑后台管理员分组({$_POST['group_name']})时，密码验证失败");
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/WebAdmin/admingroupedit/id/'.$_GET['id']);
			$this->error('密码验证失败，操作失败！');
		}
		//登录管理员密码验证,结束

        $this->uid=$_GET['id'];
        $this->admingroup_db=M('admin_group');

        $this->admingrouplist['group_name']=trim($_POST['group_name']);
		$this->admingrouplist['status'] = $_POST['status'];
		$this->admingrouplist['note'] = $_POST['note'];
		$log_result = $this->logcheck(array('group_id'=>$this->uid),'sj_admin_group',$this->admingrouplist,$this->admingroup_db);
        $list = $this->admingroup_db->where(array('group_id'=>$this->uid))->save($this->admingrouplist);
        if(false!==$list) {
            $this->writelog('编辑ID为['.$this->uid.']的管理员分组信息'.$log_result,'sj_admin_group',$this->uid,__ACTION__ ,"","edit");
            $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/WebAdmin/admingroup_list');
            $this->success("修改成功！");
        }else
        {
            $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/WebAdmin/admingroup_list');
            $this->error('修改失败！');
        }

    }
    //管理员管理_管理组权限检查_查看
    public function adminusercommision() {         //权限列表_显示
        $this->uid=$_GET['uid'];

        //查询所有权限分组
		$model = M('admin_note_group');
        $node_group_list_result = $model->Table('sj_admin_node_group')->select();

		$node_list_result = $model
			->Table('sj_admin_node B, sj_admin_note_group AS C')
			->where('C.node_id=B.node_id')
			->field('B.node_id, B.nodename, B.postil, B.type, B.note, C.group_id')
			->select();

        $adminrole_db = M('admin_role');
        //查看用户已定用分组
        $admin_node_list_result = $adminrole_db->where(array('admin_id'=>$this->uid))->field('node_id')->select();
        //dump($this->lists);
        $admin_node_list = array();
    	foreach ($admin_node_list_result as $node) {
			$admin_node_list[$node['node_id']] = $node;
		}
		
		$node_group_list = array();
        foreach($node_group_list_result as $key => $value)     // 循环判断该类别是否被选中
        {
        	$group_id = $value['group_id'];
        	$node_group_list[$group_id] = $value;
        	$node_group_list[$group_id]['nodes'] = array();
        	$node_group_list[$group_id]['checked'] = false;
        }
		foreach ($node_list_result as $node) {
			$node_id = $node['node_id'];
			$group_id = $node['group_id'];
			$node['checked'] = isset($admin_node_list[$node_id]);
			$node_group_list[$group_id]['nodes'][] = $node;
			isset($admin_node_list[$node_id]) && $node_group_list[$group_id]['checked'] = true;
		}
		
        $this->assign('theadminuid',$this->uid);
        $this->assign('list',$node_group_list);
        $this->display();

    }
    
    
    //管理员管理_管理组权限检查_更改
    public function upadminusercommision() {
		//登录管理员密码验证,开始
		if(!$this->login_pwd_chk()) {
			$this->writelog("编辑管理员{$this->getNameById($_GET['uid'])}特殊权限时，密码验证失败");
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/upadminusercommision/uid/'.$_SESSION['admin']['admin_id']);
			$this->error('密码验证失败，操作失败！');
		}
		//登录管理员密码验证,结束
		$this->uid=escape_string($_GET['uid']);

        $model = M('admin_role');
        $admin_group_list = $model->where('admin_id='. $this->uid)->field('node_id')->select();
        $new_checks = array();
		$node_value = explode(',', $_POST['node_value']);
		foreach ($node_value as $node) {
			if (!empty($node) && is_numeric($node)){
				$new_checks[] = $node;
			}
		}
		$old_checks = array();
		foreach ((array)$admin_group_list as $key=>$item) {
			$old_checks[] = $item['node_id'];
		}
        $delete_node = array_diff($old_checks, $new_checks);
        $new_node = array_diff($new_checks, $old_checks);
        $text='授予用户ID为['.$this->uid.']';       //临时存储修改项目
        
        foreach ($delete_node as $node) {
        	$sql = "delete from sj_admin_role where admin_id={$this->uid} and node_id='{$node}' limit 1";
	        $model->query($sql);
            $this->writelog("admin_id为{$this->uid},删除了节点{$node}",'sj_admin_role',$this->uid,__ACTION__ ,"","del");
        }
        
        foreach ($new_node as $node) {
            $sql = "insert into sj_admin_role (`admin_id`, `node_id`) values ({$this->uid}, {$node});";
	        $model->query($sql);
            $this->writelog("admin_id为{$this->uid},添加了节点{$node}",'sj_admin_role',$this->uid,__ACTION__ ,"","add");
        }
		
		//修改用户最后更新时间
		$admin_users = M('admin_users');
		$data['admin_user_id']= $this->uid;
		$data['update_time']  = time();
		$admin_users->save($data);
		
        if($text=='')
        {
            $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/WebAdmin/index');
            $this->error('您没有对管理员权限进行修改！');
        }
        else
        {   $this->writelog($text);
            $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/WebAdmin/index');
            $this->success($text);
        }
    }
    //管理员管理_管理员日志查看
    public function adminlog() {
        $this->uid=$_GET['uid'];


        $thisyear=date('Y',time());
        $thismonths=date('m',time());
        $thisday=date('d',time());
        $thismonthsdate= mktime(0,0,0,$thismonths,1,$thisyear);  //本月初时间戳

        $thisweek=date('N',time());
        $thisdayfirstdate= mktime(0,0,0,$thismonths,$thisday,$thisyear);  //本日第一秒时间戳
        $thisdayenddate= mktime(23,59,59,$thismonths,$thisday,$thisyear);  //本日最后一秒时间戳
        $thiswekdatefrist=$thisdayenddate-(3600*24)*$thisweek;             //本周第一天时间戳

        $this->begintime=strtotime(strip_tags(trim(htmlspecialchars($_POST['begintime']))))?strtotime(strip_tags(trim(htmlspecialchars($_POST['begintime'])))):$thisdayfirstdate;;
        $this->endtime=strtotime(strip_tags(trim(htmlspecialchars($_POST['endtime']))))?strtotime(strip_tags(trim(htmlspecialchars($_POST['endtime'])))):$thisdayenddate;

        $this->type_id=strip_tags(trim(htmlspecialchars($_GET['typeid'])))?strip_tags(trim(htmlspecialchars($_GET['typeid']))):1;
        switch($this->type_id) {
        case 1: //本日
            $this->map['logtime']=array(array('gt',$thisdayfirstdate),array('lt',$thisdayenddate),'and');
            break;
        case 2: //本周
            $this->map['logtime']=array(array('gt',$thiswekdatefrist),array('lt',$thisdayenddate),'and');
            break;
        case 3: //月
            $this->map['logtime']=array(array('gt',$thismonthsdate),array('lt',$thisdayenddate),'and');
            break;
        case 4: //指定日期
            if(empty($_GET['p'])) {
                $this->map['logtime']=array(array('gt',$this->begintime),array('lt',$this->endtime),'and');
                $_SESSION['admin']['begintime']=$this->begintime;
                $_SESSION['admin']['endtime']=$this->endtime;
            }else
            {
              $this->map['logtime']=array(array('gt',$_SESSION['admin']['begintime']),array('lt',$_SESSION['admin']['endtime']),'and');
            }
            break;
        }
        import("@.ORG.Page");
        $this->adminlog_db=M('admin_log');
        $this->map['admin_id']=$this->uid;
        $count= $this->adminlog_db->where($this->map)->count();
        $Page=new Page($count,10);
        $this->adminloglist=$this->adminlog_db->where($this->map)->limit($Page->firstRow.','.$Page->listRows)->select();
		
        $this->adminuser_db=M('admin_users');
        $this->adminnode_db=M('admin_node');

        for($i=0;$i<count($this->adminloglist);$i++)
        {

             //管理员名称
            if(empty($this->adminloglist[$i]['admin_id'])) {
                $this->adminloglist[$i]['admin_id']='-';
            }else
            {
                $this->adminloglist[$i]['admin_id']=$this->adminuser_db->where('admin_user_id='.$this->adminloglist[$i]['admin_id'].'')->getField('admin_user_name');
            }
             //管理员名称
            if(empty($this->adminloglist[$i]['action_id'])) {
                $this->adminloglist[$i]['action_id']='-';
                $this->adminloglist[$i]['nodename']='-';
                $this->adminloglist[$i]['postil']='-';
            }else
            {
                $this->adminnodelist=$this->adminnode_db->where('node_id='.$this->adminloglist[$i]['action_id'].'')->field('nodename,postil')->select();
                $this->adminloglist[$i]['nodename']=$this->adminnodelist[0]['nodename'];
                $this->adminloglist[$i]['postil']=$this->adminnodelist[0]['postil'];

            }

        }

        $Page->setConfig('header','篇记录');
        $Page->setConfig('first','<<');
        $Page->setConfig('last','>>');

        $show =$Page->show();
        //dump($this->type_id);
        //dump($this->adminloglist);
        $this->assign('adminid',$this->uid);
        $this->assign('adminlogtypeid',$this->type_id);
        $this->assign ("page", $show );
        $this->assign('adminloglist',$this->adminloglist);
        $this->display();

    }
    
    public function softReplaceLog(){
    	import("@.ORG.Page");
    	
    	if(empty($_GET['p'])) {
	    	$time = $this -> gettimeArr();
			$from_value = $time[0];
			$to_value = $time[count($time)-1]." 23:59:59";
			$sid = $_REQUEST['id'];
			$user = isset($_REQUEST['admin_user'])?$_REQUEST['admin_user']:'';
			
			$_SESSION['WebAdmin']['softReplaceLog']['params'] = array('time'=>$time,'user'=>$user, 'id' => $sid);
    	}else{
    		$time = $_SESSION['WebAdmin']['softReplaceLog']['params']['time'];
			$from_value = $time[0];
			$to_value = $time[count($time)-1];
			$user = $_SESSION['WebAdmin']['softReplaceLog']['params']['user'];
			$sid = $_SESSION['WebAdmin']['softReplaceLog']['params']['id'];
    	}
		
		$model = new Model();
		
		$sql = "
			SELECT COUNT(*) AS C FROM sj_admin_log
			WHERE  action_id in ('5','7') 
				AND admin_id <> 1
				AND actionexp LIKE '%包进行替换%'
				AND logtime >= ".strtotime($from_value)."	
				AND logtime <= ".strtotime($to_value)."
		";
		if($user){
			$sql .= " AND admin_id=$user";
		}
		
		$info = $model->query($sql);
		$count = $info[0]["C"];
		$Page=new Page($count,15);
		$Page->firstRow.','.$Page->listRows;
		$actionexp = "AND actionexp LIKE '%包进行替换%'";
		
		if (!empty($sid)) {
			$actionexp .= "AND actionexp LIKE '%$sid%'";
		}
		$sql = "
			SELECT * FROM sj_admin_log 
			WHERE  action_id in ('5','7') 
				AND admin_id <> 1
				{$actionexp}
				AND logtime >= ".strtotime($from_value)."	
				AND logtime <= ".strtotime($to_value)."
			";
    	if($user){
			$sql .= " AND admin_id=$user";
		}
		$sql .= "	
			ORDER BY admin_log_id DESC
			LIMIT {$Page->firstRow},{$Page->listRows}
			";
		
		$info = $model->query($sql);
		
		$sql = "
			SELECT * FROM sj_admin_users WHERE admin_state = 1 and admin_user_id <> 1
		";
		$admin_users_d = $model->query($sql);
		$admin_users = array();
		foreach ($admin_users_d as $a){
			$id = $a['admin_user_id'];
			$admin_users[$id] = $a;
		}
		
		$display_ary = array();
		foreach ($info as $v){
			$t = array();
			
			$admin_id = $v['admin_id'];
			$admin_name = $admin_users[$admin_id]['admin_user_name'];
			$log = $v['actionexp'];
			preg_match('/对softid为(\d+) 软件的包进行替换/', $log, $m);
			$soft_id = $m[1];			
			
			$sql = "SELECT * FROM sj_soft WHERE softid=$soft_id;";
			$soft = $model->query($sql);
			$soft = isset($soft[0])?$soft[0]:array('softname'=>"未知的softid，$soft_id");
			$softname = $soft['softname'];
			
			$t['admin_log_id'] = $v['admin_log_id'];
			$t['user'] = $admin_name;
			$t['softname'] = $softname;
			$t['dev_name'] = isset($soft['dev_name'])?$soft['dev_name']:'';
			$t['logtime'] = isset($v['logtime'])?$v['logtime']:'';
			$t['actionexp'] = $v['actionexp'];
			
			$display_ary[] = $t;
		}
		
		$Page->setConfig('header','篇记录');
        $Page->setConfig('first','<<');
        $Page->setConfig('last','>>');

        $show =$Page->show();
        $this->assign ("page", $show );
        $this->assign ("admin_users", $admin_users );
        $this->assign ("display_ary", $display_ary );
        $this->assign("selected_user", $user);
        $this->assign('fromdate',$time[0]);
		$this->assign('todate',$time[count($time)-1]);
		$this->assign('to_value',$time[count($time)-1]);
		$this->assign('from_value',$time[0]);
		$this->assign('id',$sid);
        $this->display();
    }
    
    
    //统计管理_时间函数
	function days_away($date, $away=0) {
	    $date_array = explode('-',$date);
	    $dst_time  = mktime(1, 1, 1, $date_array[1], $date_array[2], $date_array[0]);
	    $today = getdate($dst_time);
	    $dt = getdate(mktime(1, 1, 1, $today['mon'], $today['mday']+$away, $today['year']));
	    return $dt['year'].'-'.sprintf("%02d" ,$dt['mon']).'-'.sprintf("%02d",$dt['mday']);
	}
    //统计管理_时间段函数
    function gettimeArr($url){
		$time = array();
		if (array_key_exists('fromdate', $_REQUEST)&&array_key_exists('todate', $_REQUEST)){
			$fromdate = $_REQUEST['fromdate'];
			$todate = $_REQUEST['todate'];

			$fromdate = $this->days_away($fromdate, 0);
			$todate = $this->days_away($todate, 0);
			if($todate < $fromdate){
				$this->assign('jumpUrl','/index.php/Message/'.$url);
				$this->error('起始时间大于截止时间,请重新选择');
			}
			if($todate > date('Y-m-d',time())){
				$this->assign('jumpUrl','/index.php/Message/'.$url);
				$this->error('截止时间超出当前时间,请重新选择');
			}
			$fromdatetime = strtotime($fromdate);
			$tovaluetime = strtotime($todate);
			$chatime = $tovaluetime - $fromdatetime;
//			if ($chatime/86400 <7){
				$len = $chatime/86400;
				for($i=0;$i<=$len;$i++){
					$time[$i] = date('Y-m-d',$fromdatetime + $i * 86400);
				}
//			}else{
//				$lintime = ($tovaluetime - $fromdatetime)/7;
//				$len = 7;
//				for($i=$len;$i>=0;$i--){
//					$time[$i] = date('Y-m-d',$fromdatetime + $i * $lintime);
//				}
//			}
		}else{
			$todate = date('Y-m-d',time());
			$tovaluetime = strtotime($todate);
			for($i=7;$i>=0;$i--){
				$time[7-$i] = date('Y-m-d',$tovaluetime - $i * 86400);
			}
		}
		return $time;
	}
	
	
	function alllog()
	{
        $this->uid=$_GET['uid'];
        
        $model = new Model();
        $sql = "SELECT * FROM sj_admin_users WHERE admin_state = 1 and admin_user_id <> 1";
		$admin_users_d = $model->query($sql);
		$admin_users = array();
		foreach ($admin_users_d as $a){
			$id = $a['admin_user_id'];
			$admin_users[$id] = $a;
		}        
        $this->assign ("admin_users", $admin_users );
        $map = array();
        
        if(!empty($_POST['button'])) {
	        if (!empty($_POST['begintime']) && !empty($_POST['endtime'])) {
	        	$map['logtime'] = array(array('gt',strtotime($_POST['begintime'])),array('lt',strtotime($_POST['endtime'])+86399),'and');
	        	$_SESSION['admin']['alllog_begintime'] = $_POST['begintime'];
	        	$_SESSION['admin']['alllog_endtime'] = $_POST['endtime'];
	        	
	        } elseif(!empty($_POST['begintime'])) {
	        	$_SESSION['admin']['alllog_begintime'] = $_POST['begintime'];
	        	$map['logtime'] = array('gt', strtotime($_POST['begintime']));
	        } elseif(!empty($_POST['endtime'])) {
	        	$_SESSION['admin']['alllog_endtime'] = $_POST['endtime'];
	        	$map['logtime'] = array('lt', (strtotime($_POST['endtime'])+86399));
	        }
	        if (!empty($_POST['admin_user'])) {
		        $map['admin_id'] = $_POST['admin_user'];
	        }
	        if (!empty($_POST['value'])) {
	        	$_SESSION['admin']['alllog_value'] = $_POST['value'];
                // $map['_string'] = "`value`='{$_POST['value']}' OR `actionexp` like '%{$_POST['value']}%'";
		        $map['_string'] = "`value`='{$_POST['value']}'";
	        } else {
	        	unset($_SESSION['admin']['alllog_value']);
	        }
	        if (!empty($_POST['actionexp'])) {
		        $map['actionexp'] = array('like', '%'. $_POST['actionexp'] .'%');
		        $_SESSION['admin']['alllog_actionexp'] = $_POST['actionexp'];
	        } else {
	        	unset($_SESSION['admin']['alllog_actionexp']);
	        }
	        
	        if (!empty($_POST['admin_user'])) {
		        $map['admin_id'] = $_REQUEST['admin_user'];
	        }
	        if (isset($_POST['operation'])) {
		        $_SESSION['admin']['alllog_operation'] = $_POST['operation'];
		        
		        switch($_POST['operation']) {
		        	case 'recomend':
		        		$map['category'] = array('in', array('sj_ad', 'sj_ad_zone'));
		        		break;
		        		
		        	case 'feature':
		        		$map['category'] = array('in', array('sj_feature', 'sj_feature_soft'));
		        		break;
		        }
	        }
            if (!empty($_POST['category'])) {
                $_SESSION['admin']['alllog_category'] = $_POST['category'];
                // $map['category'] = array('like', '%'. $_POST['category'] .'%');
                $map['category'] = $_POST['category'];
            }else{
                unset($_SESSION['admin']['alllog_category']);
            }
        	$_SESSION['admin']['alllog_map'] = $map;
        } else {
        	$map = $_SESSION['admin']['alllog_map'];
        }
        
        
        if (isset($map['admin_id'])) {
        	$this->assign ("admin_id", $map['admin_id'] );
        }

        if (isset($_SESSION['admin']['alllog_endtime'])) {
        	$this->assign ("endtime", $_SESSION['admin']['alllog_endtime']);
        }
        if (isset($_SESSION['admin']['alllog_begintime'])) {
        	$this->assign ("begintime", $_SESSION['admin']['alllog_begintime'] );
        }
        
        if (isset($_SESSION['admin']['alllog_operation'])) {
        	$this->assign ("operation", $_SESSION['admin']['alllog_operation'] );
        }
        if (isset($_SESSION['admin']['alllog_actionexp'])) {
        	$this->assign ("actionexp", $_SESSION['admin']['alllog_actionexp'] );
        }
        if (isset($_SESSION['admin']['alllog_value'])) {
        	$this->assign ("value", $_SESSION['admin']['alllog_value'] );
        }
        if (isset($_SESSION['admin']['alllog_category'])) {
            $this->assign ("category", $_SESSION['admin']['alllog_category'] );
        }
        
        
        
        
        
        import("@.ORG.Page");
        $this->adminlog_db=M('admin_log');
        if($map){
            $count= $this->adminlog_db->where($map)->count();
        }else{
            $count=0;
        }
        
        // echo $this->adminlog_db->getlastsql();die;
        $Page=new Page($count,20);
        if($map){
            $this->adminloglist=$this->adminlog_db->where($map)->order('logtime desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        }
        $this->adminuser_db=M('admin_users');
        $this->adminnode_db=M('admin_node');

        for($i=0;$i<count($this->adminloglist);$i++)
        {
             //管理员名称
            if(empty($this->adminloglist[$i]['admin_id'])) {
                $this->adminloglist[$i]['admin_name']='-';
            }else
            {
                $this->adminloglist[$i]['admin_name']=$this->adminuser_db->where('admin_user_id='.$this->adminloglist[$i]['admin_id'].'')->getField('admin_user_name');
            }
             //管理员名称
            if(empty($this->adminloglist[$i]['action_id'])) {
                $this->adminloglist[$i]['action_id']='-';
                $this->adminloglist[$i]['nodename']='-';
                $this->adminloglist[$i]['postil']='-';
            }else
            {
                $this->adminnodelist=$this->adminnode_db->where('node_id='.$this->adminloglist[$i]['action_id'].'')->field('nodename,postil')->select();
                $this->adminloglist[$i]['nodename']=$this->adminnodelist[0]['nodename'];
                $this->adminloglist[$i]['postil']=$this->adminnodelist[0]['postil'];

            }

        }

        $Page->setConfig('header','篇记录');
        $Page->setConfig('first','<<');
        $Page->setConfig('last','>>');

        $show =$Page->show();
        $this->assign('adminid',$this->uid);
        $this->assign('adminlogtypeid',$this->type_id);
        $this->assign ("page", $show );
        $this->assign('adminloglist',$this->adminloglist);
        $this->display();
	}



    function alllog_p()
    {
        $this->uid=$_GET['uid'];
        
        $model = new Model();
        $sql = "SELECT * FROM sj_admin_users WHERE admin_state = 1 and admin_user_id <> 1";
        $admin_users_d = $model->query($sql);
        $admin_users = array();
        $admin_maps = array();
        foreach ($admin_users_d as $a){
            $id = $a['admin_user_id'];
            $admin_users[$id] = $a;
            $admin_maps[$a['admin_user_name']] = $id;
        }        
        $this->assign ("admin_users", $admin_users );
        $map = array();

        $search_user = '';
        
        if(!empty($_POST['button'])) {
            if (!empty($_POST['begintime']) && !empty($_POST['endtime'])) {
                $map['logtime'] = array(array('gt',strtotime($_POST['begintime'])),array('lt',strtotime($_POST['endtime'])+86399),'and');
                $_SESSION['admin']['alllog_begintime'] = $_POST['begintime'];
                $_SESSION['admin']['alllog_endtime'] = $_POST['endtime'];
                
            } elseif(!empty($_POST['begintime'])) {
                $_SESSION['admin']['alllog_begintime'] = $_POST['begintime'];
                $map['logtime'] = array('gt', strtotime($_POST['begintime']));
            } elseif(!empty($_POST['endtime'])) {
                $_SESSION['admin']['alllog_endtime'] = $_POST['endtime'];
                $map['logtime'] = array('lt', (strtotime($_POST['endtime'])+86399));
            }
            if (!empty($_POST['admin_user'])) {
                
                $tmp_user = trim($_POST['admin_user']);
                //$tmp_admins =  array_flip($admin_maps);

                if(empty($admin_maps[$tmp_user])) {
                    //var_dump($tmp_user,$admin_maps);exit;

                    $this->assign('jumpUrl','/index.php/Admin/WebAdmin/alllog_p');
                    $this->error('用户不存在');
                }
                $_POST['admin_user'] = $admin_maps[$tmp_user];
                $search_user = $tmp_user;
                $map['admin_id'] = $_POST['admin_user'];
            }
            if (!empty($_POST['value'])) {
                $_SESSION['admin']['alllog_value'] = $_POST['value'];
                // $map['_string'] = "`value`='{$_POST['value']}' OR `actionexp` like '%{$_POST['value']}%'";
                $map['_string'] = "`value`='{$_POST['value']}'";
            } else {
                unset($_SESSION['admin']['alllog_value']);
            }
            if (!empty($_POST['actionexp'])) {
                $map['actionexp'] = array('like', '%'. $_POST['actionexp'] .'%');
                $_SESSION['admin']['alllog_actionexp'] = $_POST['actionexp'];
            } else {
                unset($_SESSION['admin']['alllog_actionexp']);
            }
            
            if (!empty($_POST['admin_user'])) {
                $map['admin_id'] = $_POST['admin_user'];
            }
            if (isset($_POST['operation'])) {
                $_SESSION['admin']['alllog_operation'] = $_POST['operation'];
                
                switch($_POST['operation']) {
                    case 'recomend':
                        $map['category'] = array('in', array('sj_ad', 'sj_ad_zone'));
                        break;
                        
                    case 'feature':
                        $map['category'] = array('in', array('sj_feature', 'sj_feature_soft'));
                        break;
                }
            }
            if (!empty($_POST['category'])) {
                $_SESSION['admin']['alllog_category'] = $_POST['category'];
                // $map['category'] = array('like', '%'. $_POST['category'] .'%');
                $map['category'] = $_POST['category'];
            }else{
                unset($_SESSION['admin']['alllog_category']);
            }
            $_SESSION['admin']['alllog_map'] = $map;
        } else {
            $map = $_SESSION['admin']['alllog_map'];
        }
        
        
        if (isset($map['admin_id'])) {
            $this->assign ("admin_id", $map['admin_id'] );
            $this->assign ("search_user", $admin_users[$map['admin_id']]['admin_user_name'] );
        }

        if (isset($_SESSION['admin']['alllog_endtime'])) {
            $this->assign ("endtime", $_SESSION['admin']['alllog_endtime']);
        }
        if (isset($_SESSION['admin']['alllog_begintime'])) {
            $this->assign ("begintime", $_SESSION['admin']['alllog_begintime'] );
        }
        
        if (isset($_SESSION['admin']['alllog_operation'])) {
            $this->assign ("operation", $_SESSION['admin']['alllog_operation'] );
        }
        if (isset($_SESSION['admin']['alllog_actionexp'])) {
            $this->assign ("actionexp", $_SESSION['admin']['alllog_actionexp'] );
        }
        if (isset($_SESSION['admin']['alllog_value'])) {
            $this->assign ("value", $_SESSION['admin']['alllog_value'] );
        }
        if (isset($_SESSION['admin']['alllog_category'])) {
            $this->assign ("category", $_SESSION['admin']['alllog_category'] );
        }
        
        
        
        
        
        import("@.ORG.Page");
        $this->adminlog_db=M('admin_log_p');
        if($map){
            $count= $this->adminlog_db->where($map)->count();
        }else{
            $count=0;
        }
        
        //echo $this->adminlog_db->getlastsql();die;
        $Page=new Page($count,20);
        if($map){
            $this->adminloglist=$this->adminlog_db->where($map)->order('logtime desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        }
        $this->adminuser_db=M('admin_users');
        $this->adminnode_db=M('admin_node');

        for($i=0;$i<count($this->adminloglist);$i++)
        {
             //管理员名称
            if(empty($this->adminloglist[$i]['admin_id'])) {
                $this->adminloglist[$i]['admin_name']='-';
            }else
            {
                $this->adminloglist[$i]['admin_name']=$this->adminuser_db->where('admin_user_id='.$this->adminloglist[$i]['admin_id'].'')->getField('admin_user_name');
            }
             //管理员名称
            if(empty($this->adminloglist[$i]['action_id'])) {
                $this->adminloglist[$i]['action_id']='-';
                $this->adminloglist[$i]['nodename']='-';
                $this->adminloglist[$i]['postil']='-';
            }else
            {
                $this->adminnodelist=$this->adminnode_db->where('node_id='.$this->adminloglist[$i]['action_id'].'')->field('nodename,postil')->select();
                $this->adminloglist[$i]['nodename']=$this->adminnodelist[0]['nodename'];
                $this->adminloglist[$i]['postil']=$this->adminnodelist[0]['postil'];

            }

        }

        $Page->setConfig('header','篇记录');
        $Page->setConfig('first','<<');
        $Page->setConfig('last','>>');

        $show =$Page->show();
        
        $this->assign('adminid',$this->uid);
        //$this->assign('search_user',$search_user);
        $this->assign('adminlogtypeid',$this->type_id);
        $this->assign ("page", $show );
        $this->assign('adminloglist',$this->adminloglist);
        $this->display();
    }
	
	function useralllog()
	{
        $model = new Model();
        print_R($admin_users);
        import("@.ORG.Page");
        $where =  '1';
        if($_GET['value'])
        {
        	$where .= " and user_id = {$_GET['value']} ";
        }
        if($_GET['begintime'])
        {
        	$time = strtotime($_GET['begintime']);
        	$where .= " and logtime >= $time ";
        }
        if($_GET['endtime'])
        {
        	$time1 = strtotime($_GET['endtime']);
        	$where .= " and logtime <= $time1 ";
        }
        $count= $model->where($where)->table('pu_user_log')->count();

        $Page=new Page($count,20);
        $weblist=$model->table('pu_user_log')->where($where)->order('logtime desc')->limit($Page->firstRow.','.$Page->listRows)->select();
				
				foreach($weblist as $key => $value)
				{
					$ret = $model->where("dev_id = {$value['user_id']}")->table('pu_developer')->find();
					$weblist[$key]['name'] = $ret['dev_name'];
				}
        $Page->setConfig('header','篇记录');
        $Page->setConfig('first','<<');
        $Page->setConfig('last','>>');

        $show =$Page->show();
        $this->assign ("page", $show );
        $this->assign('adminloglist',$weblist);
        $this->display();
	}
	
	/*
	 * 加密，可逆
	 * 可接受任何字符
	 * 安全度非常高
	 */
	function encrypt($txt, $key = 'goapk')
	{
		$chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_.";
		$ikey ="-x6g6ZWm2G9dfsaevEE_FWUKL.pOq3kRIxsZ6rm-";
		$nh1 = rand(0,64);
		$nh2 = rand(0,64);
		$nh3 = rand(0,64);
		$ch1 = $chars{$nh1};
		$ch2 = $chars{$nh2};
		$ch3 = $chars{$nh3};
		$nhnum = $nh1 + $nh2 + $nh3;
		$knum = 0;$i = 0;
		while(isset($key{$i})) $knum +=ord($key{$i++});
		$mdKey = substr(md5(md5(md5($key.$ch1).$ch2.$ikey).$ch3),$nhnum%8,$knum%8 + 16);
		$txt = base64_encode($txt);
		$txt = str_replace(array('+','/','='),array('-','_','.'),$txt);
		$tmp = '';
		$j=0;$k = 0;
		$tlen = strlen($txt);
		$klen = strlen($mdKey);
		for ($i=0; $i<$tlen; $i++) {
			$k = $k == $klen ? 0 : $k;
			$j = ($nhnum+strpos($chars,$txt{$i})+ord($mdKey{$k++}))%64;
			$tmp .= $chars{$j};
		}
		$tmplen = strlen($tmp);
		$tmp = substr_replace($tmp,$ch3,$nh2 % ++$tmplen,0);
		$tmp = substr_replace($tmp,$ch2,$nh1 % ++$tmplen,0);
		$tmp = substr_replace($tmp,$ch1,$knum % ++$tmplen,0);
		return $tmp;
	}
	 
	/*
	 * 解密
	 *
	 */
	function decrypt($txt, $key = 'goapk')
	{
		$chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_.";
		$ikey ="-x6g6ZWm2G9dfsaevEE_FWUKL.pOq3kRIxsZ6rm-";
		$knum = 0;$i = 0;
		$tlen = strlen($txt);
		while(isset($key{$i})) $knum +=ord($key{$i++});
		$ch1 = $txt{$knum % $tlen};
		$nh1 = strpos($chars,$ch1);
		$txt = substr_replace($txt,'',$knum % $tlen--,1);
		$ch2 = $txt{$nh1 % $tlen};
		$nh2 = strpos($chars,$ch2);
		$txt = substr_replace($txt,'',$nh1 % $tlen--,1);
		$ch3 = $txt{$nh2 % $tlen};
		$nh3 = strpos($chars,$ch3);
		$txt = substr_replace($txt,'',$nh2 % $tlen--,1);
		$nhnum = $nh1 + $nh2 + $nh3;
		$mdKey = substr(md5(md5(md5($key.$ch1).$ch2.$ikey).$ch3),$nhnum % 8,$knum % 8 + 16);
		$tmp = '';
		$j=0; $k = 0;
		$tlen = strlen($txt);
		$klen = strlen($mdKey);
		for ($i=0; $i<$tlen; $i++) {
			$k = $k == $klen ? 0 : $k;
			$j = strpos($chars,$txt{$i})-$nhnum - ord($mdKey{$k++});
			while ($j<0) $j+=64;
			$tmp .= $chars{$j};
		}
		$tmp = str_replace(array('-','_','.'),array('+','/','='),$tmp);
		return base64_decode($tmp);
	}
	function adminusercommision_csv(){
		ini_set('memory_limit', '1024M');
        set_time_limit(0);
		$adminuser_db=M('admin_users');
		$model = M('admin_note_group');
		$adminrole_db = M('admin_role');
		$admin_id=$adminuser_db->field("admin_user_id,admin_user_name")->where("admin_state=1")->select();
		//print_r($admin_id);exit;
		foreach($admin_id as $k=>$val){
		//echo $val[];
			$node_group_list_result = $model->Table('sj_admin_node_group')->select();

			$node_list_result = $model
				->Table('sj_admin_node B, sj_admin_note_group AS C')
				->where('C.node_id=B.node_id')
				->field('B.node_id, B.nodename, B.postil, B.type, B.note, C.group_id')
				->select();
			//查看用户已定用分组
			$admin_node_list_result = $adminrole_db->where(array('admin_id'=>$val['admin_user_id']))->field('node_id')->select();
			//dump($this->lists);
			//print_r($admin_node_list_result);exit;
			$admin_node_list = array();
			foreach ($admin_node_list_result as $node) {
				$admin_node_list[$node['node_id']] = $node;
			}
			
			$node_group_list = array();
			foreach($node_group_list_result as $key => $value)     // 循环判断该类别是否被选中
			{
				$group_id = $value['group_id'];
				$node_group_list[$group_id] = $value;
				$node_group_list[$group_id]['nodes'] = array();
				$node_group_list[$group_id]['checked'] = false;
			}
			//print_r($node_list_result);exit;
			foreach ($node_list_result as $node) {
				$node_id = $node['node_id'];
				$group_id = $node['group_id'];
				$node['checked'] = isset($admin_node_list[$node_id]);
				if(isset($admin_node_list[$node_id])){
					$node_group_list[$group_id]['checked'] = true;
					$node_group_list[$group_id]['nodes'][] = $node;
				}
			}
				$list[$k]['info']=$node_group_list;
				$list[$k]['user_name']=$val['admin_user_name'];
		}
		//print_r($list);
		header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="webadmin.csv"');
        header('Cache-Control: max-age=0');
        $fp = fopen('php://output', 'a');
		foreach($list as $key=>$zh_info){
		$user_name_list=array();
		$user_name_list['name'] = iconv('utf-8', 'gbk', $zh_info['user_name']) . "\t";
			//$user_name=$zh_info['user_name'];
			 fputcsv($fp, $user_name_list);
			$info=$zh_info['info'];
			foreach($info as $n =>$m){
				$group_name_list=array();
				$group_name_list['name'] = iconv('utf-8', 'gbk', $m['group_name']) . "\t";
				//$group_name=$m['group_name'];
				fputcsv($fp, $group_name_list);
				foreach($m['nodes'] as $k=>$info){
					$node_list=array();
					$node_list['postil'] = iconv('utf-8', 'gbk', str_replace(array("\r\n", "\n", "\r", "\t"), "", $info['postil'])) . "\t";
					fputcsv($fp, $node_list);
				}
				
			}
			
		}
	}

	//登录时设置指定ip,问答问题设置
	function login_qa() {
		$login_qa = M('admin_login_qa');
		$fields = $login_qa->select();
		$this->assign('fields',$fields);
		$this->display();
	}

	function login_qa_edit() {
		$this->gpcFilter();
		//登录管理员密码验证,开始
		if(!$this->login_pwd_chk()) {
			$this->writelog("编辑登录指定IP及问答设置时，密码验证失败");
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/WebAdmin/login_qa');
			$this->error('密码验证失败，操作失败！');
		}
		//登录管理员密码验证,结束

		$login_qa = M('admin_login_qa');
		$fields = array();
		$fields['ips'] = $_POST['ips'];
		$login_qa->where("id=1")->save($fields);

		$ids = array();
		if($_POST) {
			foreach($_POST as $k=>$v) {
				if(strpos($k,'question_')!==FALSE) {
					$_k = str_replace('question_','',$k);
					$ids[$_k] = $v;
				}
			}
		}
		if($ids) {
			foreach($ids as $k=>$v) {
				if($login_qa->where("id='{$k}'")->select()) {
					$login_qa->where("id='{$k}'")->save(array('question'=>$v));
				} else {
					$login_qa->add(array('question'=>$v));
				}
			}
		}

        $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/WebAdmin/login_qa');
        $this->success("提交成功！");
	}
	//随机生成密码
	function pub_rand_password(){
		$az = "abcdefghijklmnopqrstuvwxyz";
		$az1 = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
		$str = "0123456789";
		$str1 = "~!@#$%^&*()_+";
		$password = '';  
		for ( $i = 0; $i < 3; $i++ ) {  
			$password .= $az[ mt_rand(0, strlen($az) - 1) ]; 
		}
		for ( $i = 0; $i < 2; $i++ ) {  
			$password .= $str[ mt_rand(0, strlen($str) - 1) ]; 
		}
		for ( $i = 0; $i < 2; $i++ ) {  
			$password .= $str1[ mt_rand(0, strlen($str1) - 1) ]; 
		}		
		for ( $i = 0; $i < 3; $i++ ) {  
			$password .= $az1[ mt_rand(0, strlen($az1) - 1) ]; 
		}
		// $password_str = '';
		// for ( $i = 0; $i < 10; $i++ ) {  
			// $password_str .= $password[ mt_rand(0, strlen($password) - 1) ]; 
		// }
		exit(json_encode(array('msg'=>$password)));	
	}	
}
?>