<?php

class RunwhitemailAction extends CommonAction {
    
    public function index() 
	{
        $model = D('Caiji.RunWhiteMail');
        
		$this->check_range_where($where, 'begintime', 'endtime', 'real_send_time', true);	

		if($_GET['search']==1&&$_GET['begintime']==null&&$_GET['endtime']==null)
		{
			$ago_time = strtotime('-30 day');
			$where=array(
				'real_send_time'=>array('egt',$ago_time),
			);
		}
		$count = $model -> table('cj_white_mail') -> where($where) -> count();
		
		//分页
		import('@.ORG.Page2');
        $param = http_build_query($_GET);
        $Page = new Page($count, 10, $param);
		$show = $Page->show();
		if($_GET['lr'])
		{
			$lr = $_GET['lr'];
		}
		else
		{
			$lr = 10;
		}
		if($_GET['p'])
		{
			$p = $_GET['p'];
		}
		else
		{
			$p = 1;
		}

		$result = $model -> table('cj_white_mail') -> where($where) -> order('send_time desc')->limit($Page->firstRow . ',' . $Page->listRows) -> select();
		//var_dump($model->getLastSql());exit;
		
		$Page->setConfig('header', '篇记录');
		$Page->setConfig('`first', '<<');
		$Page->setConfig('last', '>>');
		$this -> assign('lr',$lr);
		$this -> assign('p',$p);
        $this -> assign("page", $show);
        $this->assign('list', $result);
        $this->display('index');
    }
    function ignore_name_manage()
	{
		$cjmodel = D('Caiji.RunWhiteMail');
		
		$where = array(
			'status' => 1
		);	
		$this->check_range_where($where, 'begintime', 'endtime', 'create_tm', true);
		$this->check_where($where, 'package_name');

		$count = $cjmodel -> table('cj_ignore_manage') -> where($where) -> count();
		import("@.ORG.Page2");
        $param = http_build_query($_GET);
        $Page = new Page($count, 10, $param);
		$result = $cjmodel -> table('cj_ignore_manage') -> where($where) -> order('update_tm desc')->limit($Page->firstRow . ',' . $Page->listRows) -> select();
		$model=M();
		foreach ($result as $key => $record) 
		{
			// 将用户id转成用户名称
			$edit_ignore_os_id = $record['edit_ignore_os_id'];
			$find = $model->table('sj_admin_users')->where(array('admin_user_id'=>$edit_ignore_os_id))->find();
			$result[$key]['edit_ignore_os_name'] = $find['admin_user_name'];
		}
		
		$show = $Page->show();
		if($_GET['lr']){
			$lr = $_GET['lr'];
		}else{
			$lr = 10;
		}
		if($_GET['p']){
			$p = $_GET['p'];
		}else{
			$p = 1;
		}
		$Page->setConfig('header', '篇记录');
		$Page->setConfig('`first', '<<');
		$Page->setConfig('last', '>>');
		$this -> assign('lr',$lr);
		$this -> assign('p',$p);
        $this -> assign('page', $show);
        $this->assign('list',$result);
        $this->display();
	}
	function add_ignore()
	{
		$model = D('Caiji.RunWhiteMail');
		if ($_POST)
		{
            $data = array();
            $package = trim($_POST['package']);
            if (!$package) 
			{
                $this->error("包名不能为空！");
            }
            $data['package_name'] = $package;
            
            $soft_name = trim($_POST['soft_name']);
            if (!$soft_name) 
			{
				$this->error("软件名称不能为空！");
            } 
            $data['soft_name'] = $soft_name;
            
            $ignore_version = trim($_POST['ignore_version']);
            $data['ignore_version'] = $ignore_version ? $ignore_version : '';
			
			$dev_name = trim($_POST['dev_name']);
            $data['dev_name'] = $dev_name ? $dev_name : '';
			
			$az_download = trim($_POST['az_download']);
            $data['az_download'] = $az_download ? $az_download : '';
   
            $now = time();
            $data['create_tm'] = $now;
            $data['update_tm'] = $now;
			$data['status'] = 1;
			
			$add_ignore_os_id = $_SESSION['admin']['admin_id'];
            $data['edit_ignore_os_id'] = $add_ignore_os_id;
			
            $ret = $model->table('cj_ignore_manage')->add($data);
            if ($ret) 
			{
                // 写日志
                $this->writelog("采集/质管--配置管理--运营白名单邮件--添加忽略名单：添加了id为{$ret}的记录",'cj_ignore_manage',$ret,__ACTION__ ,"","add");
                $this->assign('jumpUrl', '__URL__/ignore_name_manage');
                $this->success("添加成功！");
            } 
			else 
			{
                $this->error("添加失败！");
            }
        } 
		else 
		{
            $this->display('add_ignore');
        }
	}
	function edit_ignore()
	{
		$model = D('Caiji.RunWhiteMail');
		if ($_POST) 
		{
            $id = $_POST['id'];
            $data = array();
            $package = trim($_POST['package']);
            if (!$package) 
			{
                $this->error("包名不能为空！");
            }
            $data['package_name'] = $package;
            
            $soft_name = trim($_POST['soft_name']);
            if (!$soft_name) 
			{
				$this->error("软件名称不能为空！");
            } 
            $data['soft_name'] = $soft_name;
            
            $ignore_version = trim($_POST['ignore_version']);
            $data['ignore_version'] = $ignore_version ? $ignore_version : '';
			
			$dev_name = trim($_POST['dev_name']);
            $data['dev_name'] = $dev_name ? $dev_name : '';
			
			$az_download = trim($_POST['az_download']);
            $data['az_download'] = $az_download ? $az_download : '';
			
			$data['update_tm'] = time();
			
			$add_ignore_os_id = $_SESSION['admin']['admin_id'];
            $data['edit_ignore_os_id'] = $add_ignore_os_id;
            $where = array('id'=>$id);
            // 编辑前记录信息
            $log = $this->logcheck($where, 'cj_ignore_manage', $data, $model);
            $ret = $model->table('cj_ignore_manage')->where($where)->save($data);
            if ($ret) 
			{
                // 写日志
                $this->writelog("采集/质管--配置管理--运营白名单邮件--忽略名单，编辑了id为{$id}的记录：{$log}",'cj_ignore_manage',$id,__ACTION__ ,"","edit");
                $this->assign('jumpUrl', '__URL__/ignore_name_manage');
                $this->success("编辑成功！");
            }
			else 
			{
                $this->error("编辑失败！");
            }
        }
		else
		{
            $id = $_GET['id'];
            $where = array(
                'id' => array('eq', $id)
            );
            
            $find = $model->table('cj_ignore_manage')->where($where)->find();
            
            $this->assign('list', $find);
            $this->display('edit_ignore');
        }
	}
	function delete_ignore()
	{
		$model = D('Caiji.RunWhiteMail');
		$id = $_GET['id'];
        $where = array(
            'id' => $id
        );
        $data = array(
            'update_tm' => time(),
            'status' => 0
        );
		$add_ignore_os_id = $_SESSION['admin']['admin_id'];
		$data['edit_ignore_os_id'] = $add_ignore_os_id;
		
		$log = $this->logcheck($where, 'cj_ignore_manage', $data, $model);
		
        $ret = $model->table('cj_ignore_manage')->where($where)->save($data);
        if ($ret) 
		{
            $this->writelog("采集/质管--配置管理--运营白名单邮件--忽略名单：删除了id为{$id}的记录",'cj_ignore_manage',$id,__ACTION__ ,"","del");
            $this->success("删除成功！");
        } 
		else 
		{
            $this->error("删除失败！");
        }
	}
	public function get_softname() 
	{
		$model = D('Caiji.RunWhiteMail');
        /*$model = new Model();
        $package = trim($_GET['package']);
		$where=array(
		'status'=>1,
		'package'=>$package,
		//'hide'=>array('in',array(1,1024)),
		'hide'=>1,
		);
        $softname_result = $model->table('sj_soft')->where($where)->field('softname,dev_name,downloaded,total_downloaded')->order('softid DESC')->limit(1)->select();
		*/
		$package = trim($_GET['package']);
		$where=array(
		'sync_from'=>"运营白名单",
		'package'=>$package,
		);
        $softname_result = $model->table('cj_soft_coop')->where($where)->field('softname,dev_name,az_downloaded')->order('softid DESC')->limit(1)->select();
        $data = array(
            'soft_name' => $softname_result[0]['softname'],
            'dev_name' => $softname_result[0]['dev_name'],
			'az_downloaded' => $softname_result[0]['az_downloaded'],
        );
        if ($softname_result) 
		{
            echo json_encode($data);
            return json_encode($data);
        } 
		else 
		{
            echo 2;
            return 2;
        }
    }
}

?>