<?php

class SoftPiracyWarningAction extends CommonAction {
	/*
	 *  盗版校验软件列表
	 *  create by 黄文强 at 2013/01/31
	 */
	public function ShowPiracy()
	{
		import('@.ORG.Page');
		$cond = array();
		$where = '';
		if (!empty($_GET['softname']))
		{
			$softname = $_GET['softname'];
			$where = "softname like '%$softname%' and ";
			$this->assign('softname', $softname);
		}
		
		$limit = isset($_GET['limit']) ? $_GET['limit'] : 15;
		$param = http_build_query($_REQUEST);

		$model = new Model();
		$where .= 'status=1';
		$total = $model->table('sj_piracy_soft')->where($where)->count();
		$page = new Page($total, $limit);
		$piracyList = $model->table('sj_piracy_soft')->where($where)->field('*')->limit($page->firstRow . ',' . $page->listRows)->select();
		//var_dump($conlist);
		
		$page -> setConfig('header', '篇记录');
		$page -> setConfig('first', '<<');
		$page -> setConfig('last', '>>');
		$this->assign('page', $page->show());
		$this->assign('piracyList', $piracyList);
		$this->display('ShowPiracy');
	}
	
	public function DeletePiracy()
	{
		$time = time();
		$flag = true;
		if (!isset($_GET['id']))
		{
			//$this->assign('jumpUrl', '/index.php/Sj/SoftOfficial/confirm');
			$this->error('ID不能为空');
		}
		$id = json_decode($_GET['id']);
		if (!$id)
		{
			//$this->assign('jumpUrl', '/index.php/Sj/SoftOfficial/confirm');
			$this->error('ID格式错误');
		}
		$model = new Model();
		foreach ($id as $v)
		{
			$ret = $model->table('sj_piracy_soft')->where("id = $v")->field('status')->select();
			if ($ret[0]['status'] != 0)
			{
				$ret = $model->table('sj_piracy_soft')->where("id = $v")->save(array('status' => 0, 'update_at' => $time));
				if (!$ret)
					$flag = false;
				else
					$this->writelog('删除了ID为' . $v . '盗版校验软件');
			}
		}
		if ($flag == false)
		{
			//$this->assign('jumpUrl', '/index.php/Sj/SoftOfficial/confirm');
			$this->error('删除失败');
		}
		else
		{
			//$this->assign('jumpUrl', '/index.php/Sj/SoftOfficial/confirm');
			$this->success('删除成功');
		}
	}
	
	public function EditPiracy()
	{
		$time = time();
		$flag = true;
		$model = new Model();
		if (!isset($_GET['action']) || ($_GET['action'] != 'edit' && $_GET['action'] != 'add'))
		{
			//$this->assign('jumpUrl', '/index.php/Sj/SoftOfficial/confirm');
			$this->error('非法操作，请联系管理员');
		}
		$action = $_GET['action'];
		if (!isset($_GET['softname']))
		{
			$this->error('软件名称不能为空');
		}
		$softname = $_GET['softname'];
		
		switch ($action)
		{
			case 'add':
				$count = $model->table('sj_piracy_soft')->where("softname='$softname' and status=1")->count();
				if ($count > 0)
				{
					$this->error('已有相同软件名');
				}
				$data = array(
					'softname' => $softname,
					'create_at' => time(),
					'update_at' => time(),
				);
				$ret = $model->table('sj_piracy_soft')->data($data)->add();
				if(empty($ret))
				{
					$this->error('添加失败');
				}
				else
				{
					$this->writelog('添加了名称为“' . $softname . '”的盗版校验软件');
					$this->success('添加成功');
				}
				break;
			case 'edit':
				if (!isset($_GET['edit_id']))
				{
					//$this->assign('jumpUrl', '/index.php/Sj/SoftOfficial/confirm');
					$this->error('出错了，ID不能为空');
				}
				$id = json_decode($_GET['edit_id']);
				$count = $model->table('sj_piracy_soft')->where("softname='$softname' and status=1 and id!=$id")->count();
				if ($count > 0)
				{
					$this->error('已有相同软件名');
				}
				$data = array(
					'softname' => $softname,
					'update_at' => time(),
				);
				$ret = $model->table('sj_piracy_soft')->where("id=$id")->save($data);
				if (empty($ret))
				{
					$this->error('编辑失败');
				}
				else
				{
					$this->writelog('编辑了ID为' . $id . '的盗版校验软件，软件名称改为“' . $softname . '”');
					$this->success('编辑成功');
				}
				break;
			default:
				$this->error('非法操作，请联系管理员');
		}
	}
}