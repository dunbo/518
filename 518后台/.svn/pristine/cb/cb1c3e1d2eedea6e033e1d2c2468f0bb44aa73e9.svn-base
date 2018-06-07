<?php
/**
 * 推荐管理-iReRecommender推荐位审核
 * Recommendded by 黄文强
 * 2013/05/22
 */
class EbookRecommendVerifyAction extends CommonAction
{
	/**
	 * 待审核列表
	 */
	public function UnverifiedList()
	{
		import("@.ORG.Page");
		$recommend = D('Sj.EbookRecommend');
		$recommendverify = D('Sj.EbookRecommendVerify');
		$category = M('category');
		$soft = M('soft');
		$now = time();
		$flag = false;
		
		if (isset($_GET['action']))
		{
			$action = $_GET['action'];
			$id = $_REQUEST['id'];
			$data = $recommendverify->where(array('id' => $id))->find();
			if ($data['verify_status'] != 0)
			{
				$this->error('操作失败，请重试');
			}
			switch ($action)
			{
				case 'pass':
					$id = $_GET['id'];
					if (empty($id))
						$this->error('非法请求，请修改后重试');
					$data = $recommendverify->where(array('id' => $id))->find();
					switch ($data['operation'])
					{
						case 1:
							$count = $recommend->where(array('status' => 1, 'hide' => 1, 'endtime' => array('LT', $now)))->count();
							$result = $recommend->enableRecommend($data['recommend_id']);
							break;

						case 2:
							$data['id'] = $data['recommend_id'];
							$data['verify_status'] = 1;
							$result = $recommend->editRecommend($data);
							break;

						case 3:
							$result = $recommend->deleteRecommend($data['recommend_id']);
							break;

						default:
							$result = false;
					}
					$log_msg = "推荐管理-iReader推荐位审核-通过了ID为" . $id . "的电子书推荐列表修改申请";
					if ($result !== false)
						$flag = $recommendverify->passVerify($id);
					if ($flag === false)
						$this->error("审核通过失败");
					else
					{
						$flag1 = $recommendverify->passVerify($id);
						$this->writelog($log_msg,'sj_ebook_recommend_verify',$id,__ACTION__ ,"","edit");
						$this->success("成功通过");
					}
					break;

				case 'refuse':
					$refuse_msg = trim($_POST['refuse_msg']);
					if (empty($refuse_msg))
						$this->error('拒绝原因不能为空');
					$id = $_POST['id'];
					if (empty($id))
						$this->error('非法请求，请修改后重试');
					$flag1 = $recommendverify->refuseVerify($id, $refuse_msg);
					$data = $recommendverify->where(array('id' => $id))->find();
					$flag2 = $recommend->refuseRecommend($data['recommend_id']);
					$log_msg = "推荐管理-iReader推荐位审核-拒绝了ID为" . $id . "的电子书推荐列表修改申请";
					if ($flag1 === false || $flag2 === false)
					{
						$this->error("审核拒绝失败");
					}
					else
					{
						$this->writelog($log_msg,'sj_ebook_recommend_verify',$id,__ACTION__ ,"","edit");
						$this->success("成功拒绝");
					}
					break;

				default:
					$this->error('非法请求，请修改后重试');
			}
		}
		else
		{
			$start_time = isset($_GET['start_time']) ? $_GET['start_time'] : '';
			$end_time = isset($_GET['end_time']) ? $_GET['end_time'] : '';
			$search_key = isset($_GET['search_key']) ? $_GET['search_key'] : '';
			$recommend_type = isset($_GET['recommend_type']) ? $_GET['recommend_type'] : '';
			if (!empty($start_time) && !empty($end_time) && strtotime($start_time) > strtotime($end_time))
				$this->error('搜索开始时间不能大于结束时间');
			$key = isset($_GET['key']) ? $_GET['key'] : '';
			$this->assign('start_time', $start_time);
			$this->assign('end_time', $end_time);
			$this->assign('search_key', $search_key);
			$this->assign('recommend_type', $recommend_type);
			$this->assign('cache_key', $key);
			
			$where = array();
			$join = 'WHERE';
			if (!empty($start_time))
			{
				$where[] = "(a.endtime>=" . strtotime($start_time) . ")";
			}
			if (!empty($end_time))
			{
				$where[] = "(a.begintime<=" . (strtotime($end_time)  + 86399) . ")";
			}
			if (!empty($search_key))
			{
				$pkglist = array("''");
				$tosearch = $recommendverify->where('verify_status=0')->field('distinct(package) as pkg')->select();
				foreach ($tosearch as $v)
				{
					$name = $soft->where(array('package' => $v['pkg'], 'status' => 1))->order('softid desc')->find();
					if (preg_match('/' . $search_key . '/i', $name['softname']) > 0)
					{
						$pkglist[] = "'" . $v['pkg'] . "'";
						$softname[$v['pkg']] = $name['softname'];
					}
				}
				$pkgstr = '(' . implode(',', $pkglist) . ')';
				$where[] = "(a.package in $pkgstr or a.package like '%$search_key%')";
				//$where[] = "(a.`package`=b.`package` AND (a.`package` LIKE '%" . $search_key . "%' OR (b.softname LIKE '%" . $search_key . "%' AND b.status=1)))";
			}
			if (!empty($recommend_type))
			{
				$where[] = "(a.recommend_type=$recommend_type)";
			}
			if (($recommend_type == 3) && !empty($key))
			{
				$where[] = "(a.key='$key')";
			}
			$where[] = "a.verify_status=0";
			$wherestring = implode(' AND ', $where);
			//$data = $recommendverify->getUnverifiedList();
			$count = count($recommendverify->query("SELECT distinct(a.id) FROM sj_ebook_recommend_verify AS a $join $wherestring"));
			//var_dump($count);
			$param = http_build_query($_GET);
			$Page = new Page($count, 30, $param);
			$show = $Page->show();
			if (empty($Page->listRows))
				$Page->listRows = 30;
			$data = $recommendverify->query("SELECT d.* FROM sj_ebook_recommend_verify AS d WHERE d.id IN (SELECT DISTINCT(a.id) FROM sj_ebook_recommend_verify AS a $join $wherestring)  ORDER BY d.`last_refresh` ASC LIMIT " . $Page->firstRow . "," . $Page->listRows);
			//var_dump("SELECT d.* FROM sj_ebook_recommend_verify AS d WHERE d.id IN (SELECT DISTINCT(a.id) FROM sj_ebook_recommend_verify AS a $join $wherestring)  ORDER BY d.`last_refresh` DESC LIMIT " . $Page->firstRow . "," . $Page->listRows);
			
			
			foreach ($data as $k => $v)
			{
				if (!empty($softname[$v['package']]))
				{
					$temp['softname'] = $softname[$v['package']];
				}
				else
				{
					$temp = $soft->where(array('package' => $v['package'], 'status' => 1))->order('softid desc')->find();
					$softname[$v['package']] = $temp['softname'];
				}
				if ($temp)
					$data[$k]['softname'] = $temp['softname'];
				else
					$data[$k]['softname'] = '(包名不存在)';
				$data[$k]['begintime'] = date('Y-m-d', $data[$k]['begintime']);
				$data[$k]['endtime'] = date('Y-m-d', $data[$k]['endtime']);
				$data[$k]['last_refresh'] = date('Y-m-d H:i:s', $data[$k]['last_refresh']);
				switch ($v['recommend_type'])
				{
					case 1:
						$data[$k]['page'] = '推荐';
						break;
					
					case 2:
						$data[$k]['page'] = '排行榜';
						break;
						
					case 3:
						$data[$k]['page'] = '分类-';
						$result = $category->where(array('category_id' => $v['category_id']))->find();
						$data[$k]['page'] .= $result['name'];
						if (strpos($v['key'], 'new'))
							$data[$k]['page'] .= '-最新';
						elseif (strpos($v['key'], 'hot'))
							$data[$k]['page'] .= '-最热';
						break;
					case 4:
						$data[$k]['page'] = '免费榜';
						break;
				}
				//var_dump($temp);
			}
			//var_dump($data);
			//var_dump($category);
			$category_list = $category->query('select * from sj_category where status=1 and parentid in (select category_id from sj_category where parentid=3)');
			$this->assign('category', $category_list);
			$this->assign('verifylist', $data);
			$this->assign('page', $show);
			$this->display();
		}
	}

	/**
	 * 正在运营列表
	 */
	public function AvailableList()
	{
		import("@.ORG.Page");
		$recommend = D('Sj.EbookRecommend');
		$category = M('category');
		$soft = M('soft');

		$start_time = isset($_GET['start_time']) ? $_GET['start_time'] : '';
		$end_time = isset($_GET['end_time']) ? $_GET['end_time'] : '';
		if (!empty($start_time) && !empty($end_time) && strtotime($start_time) > strtotime($end_time))
			$this->error('搜索开始时间不能大于结束时间');
		$search_key = isset($_GET['search_key']) ? $_GET['search_key'] : '';
		$recommend_type = isset($_GET['recommend_type']) ? $_GET['recommend_type'] : '';
		$key = isset($_GET['key']) ? $_GET['key'] : '';
		$now = time();
		$this->assign('start_time', $start_time);
		$this->assign('end_time', $end_time);
		$this->assign('search_key', $search_key);
		$this->assign('recommend_type', $recommend_type);
		$this->assign('cache_key', $key);
		$where = array();
		$join = 'WHERE';
		if (!empty($start_time))
		{
			$where[] = "(a.endtime>=" . strtotime($start_time) . ")";
		}
		if (!empty($end_time))
		{
			$where[] = "(a.begintime<=" . (strtotime($end_time) + 86399) . ")";
		}
		if (!empty($_GET['search_key']))
		{
			$pkglist = array("''");
			$tosearch = $recommend->where("status=1 and endtime>{$now}")->field('distinct(package) as pkg')->select();
			foreach ($tosearch as $v)
			{
				$name = $soft->where(array('package' => $v['pkg'], 'status' => 1))->order('softid desc')->find();
				if (preg_match('/' . $search_key . '/i', $name['softname']) > 0)
				{
					$pkglist[] = "'" . $v['pkg'] . "'";
					$softname[$v['pkg']] = $name['softname'];
				}
			}
			$pkgstr = '(' . implode(',', $pkglist) . ')';
			$where[] = "(a.package in $pkgstr or a.package like '%$search_key%')";
			//$where[] = "(a.`package`=b.`package` AND (a.`package` LIKE '%" . $search_key . "%' OR (b.softname LIKE '%" . $search_key . "%' AND b.status=1)))";
		}
		if (!empty($recommend_type))
		{
			$where[] = "(a.recommend_type=$recommend_type)";
		}
		if (($recommend_type == 3) && !empty($key))
		{
			$where[] = "(a.key='$key')";
		}
		$where[] = "a.status=1 and a.hide=1 and a.endtime>={$now}";

		$wherestring = implode(' AND ', $where);
		//$data = $recommendverify->getUnverifiedList();
		$count = count($recommend->query("SELECT distinct(a.id) FROM sj_ebook_recommend AS a $join $wherestring"));
		//var_dump($count);

		$param = http_build_query($_GET);
		$Page = new Page($count, 30, $param);
		$show = $Page->show();
		if (empty($Page->listRows))
			$Page->listRows = 30;

		$data = $recommend->query("SELECT d.* FROM sj_ebook_recommend AS d WHERE d.id IN (SELECT DISTINCT(a.id) FROM sj_ebook_recommend AS a $join $wherestring)  ORDER BY d.`rank`,d.`begintime` ASC LIMIT " . $Page->firstRow . "," . $Page->listRows);
		//var_dump("SELECT d.* FROM sj_ebook_recommend_verify AS d WHERE d.id IN (SELECT DISTINCT(a.id) FROM sj_ebook_recommend_verify AS a $join $wherestring)  ORDER BY d.`last_refresh` DESC LIMIT " . $Page->firstRow . "," . $Page->listRows);
		
		foreach ($data as $k => $v)
		{
			if (!empty($softname[$v['package']]))
			{
				$temp['softname'] = $softname[$v['package']];
			}
			else
			{
				$temp = $soft->where(array('package' => $v['package'], 'status' => 1))->order('softid desc')->find();
				$softname[$v['package']] = $temp['softname'];
			}
			if ($temp)
				$data[$k]['softname'] = $temp['softname'];
			else
				$data[$k]['softname'] = '(包名不存在)';
			$data[$k]['begintime'] = date('Y-m-d', $data[$k]['begintime']);
			$data[$k]['endtime'] = date('Y-m-d', $data[$k]['endtime']);
			switch ($v['recommend_type'])
			{
				case 1:
					$data[$k]['page'] = '推荐';
					break;
				
				case 2:
					$data[$k]['page'] = '排行榜';
					break;
					
				case 3:
					$data[$k]['page'] = '分类-';
					$result = $category->where(array('category_id' => $v['category_id']))->find();
					$data[$k]['page'] .= $result['name'];
					if (strpos($v['key'], 'new'))
						$data[$k]['page'] .= '-最新';
					elseif (strpos($v['key'], 'hot'))
						$data[$k]['page'] .= '-最热';
					break;
				case 4:
					$data[$k]['page'] = '免费榜';
					break;
			}
			//var_dump($temp);
		}
		//var_dump($data);
		$category_list = $category->query('select * from sj_category where status=1 and parentid in (select category_id from sj_category where parentid=3)');
		$this->assign('category', $category_list);
		$this->assign('list', $data);
		$this->assign('page', $show);
		$this->display();
	}

	/**
	 * 已结束
	 */
	public function UnavailableList()
	{
		import("@.ORG.Page");
		$recommend = D('Sj.EbookRecommend');
		$category = M('category');
		$soft = M('soft');

		$start_time = isset($_GET['start_time']) ? $_GET['start_time'] : '';
		$end_time = isset($_GET['end_time']) ? $_GET['end_time'] : '';
		if (!empty($start_time) && !empty($end_time) && strtotime($start_time) > strtotime($end_time))
			$this->error('搜索开始时间不能大于结束时间');
		$search_key = isset($_GET['search_key']) ? $_GET['search_key'] : '';
		$recommend_type = isset($_GET['recommend_type']) ? $_GET['recommend_type'] : '';
		$key = isset($_GET['key']) ? $_GET['key'] : '';
		$now = time();
		$this->assign('start_time', $start_time);
		$this->assign('end_time', $end_time);
		$this->assign('search_key', $search_key);
		$this->assign('recommend_type', $recommend_type);
		$this->assign('cache_key', $key);
		$where = array();
		$join = 'WHERE';
		if (!empty($start_time))
		{
			$where[] = "(a.endtime>=" . strtotime($start_time) . ")";
		}
		if (!empty($end_time))
		{
			$where[] = "(a.begintime<=" . (strtotime($end_time) + 86399) . ")";
		}
		if (!empty($_GET['search_key']))
		{
			$pkglist = array("''");
			$tosearch = $recommend->where("status=0 or endtime<{$now}")->field('distinct(package) as pkg')->select();
			foreach ($tosearch as $v)
			{
				$name = $soft->where(array('package' => $v['pkg'], 'status' => 1))->order('softid desc')->find();
				if (preg_match('/' . $search_key . '/i', $name['softname']) > 0)
				{
					$pkglist[] = "'" . $v['pkg'] . "'";
					$softname[$v['pkg']] = $name['softname'];
				}
			}
			$pkgstr = '(' . implode(',', $pkglist) . ')';
			$where[] = "(a.package in $pkgstr or a.package like '%$search_key%')";
			//$join = 'INNER JOIN sj_soft AS b ON';
			//$where[] = "(a.`package`=b.`package` AND (a.`package` LIKE '%" . $search_key . "%' OR (b.softname LIKE '%" . $search_key . "%' AND b.status=1)))";
		}
		if (!empty($recommend_type))
		{
			$where[] = "(a.recommend_type=$recommend_type)";
		}
		if (($recommend_type == 3) && !empty($key))
		{
			$where[] = "(a.key='$key')";
		}
		$where[] = "(a.status=0 or a.endtime<{$now})";

		$wherestring = implode(' AND ', $where);
		//$data = $recommendverify->getUnverifiedList();
		$count = count($recommend->query("SELECT distinct(a.id) FROM sj_ebook_recommend AS a $join $wherestring"));
		//var_dump($count);

		$param = http_build_query($_GET);
		$Page = new Page($count, 30, $param);
		$show = $Page->show();
		if (empty($Page->listRows))
			$Page->listRows = 30;

		$data = $recommend->query("SELECT d.* FROM sj_ebook_recommend AS d WHERE d.id IN (SELECT DISTINCT(a.id) FROM sj_ebook_recommend AS a $join $wherestring)  ORDER BY d.`last_refresh` DESC LIMIT " . $Page->firstRow . "," . $Page->listRows);
		//var_dump("SELECT d.* FROM sj_ebook_recommend_verify AS d WHERE d.id IN (SELECT DISTINCT(a.id) FROM sj_ebook_recommend_verify AS a $join $wherestring)  ORDER BY d.`last_refresh` DESC LIMIT " . $Page->firstRow . "," . $Page->listRows);
		
		foreach ($data as $k => $v)
		{
			if (!empty($softname[$v['package']]))
			{
				$temp['softname'] = $softname[$v['package']];
			}
			else
			{
				$temp = $soft->where(array('package' => $v['package'], 'status' => 1))->order('softid desc')->find();
				$softname[$v['package']] = $temp['softname'];
			}
			if ($temp)
				$data[$k]['softname'] = $temp['softname'];
			else
				$data[$k]['softname'] = '(包名不存在)';
			$data[$k]['begintime'] = date('Y-m-d', $data[$k]['begintime']);
			$data[$k]['endtime'] = date('Y-m-d', $data[$k]['endtime']);
			if ($v['status'] == 0)
				$data[$k]['reason'] = '合作方申请删除';
			else
				$data[$k]['reason'] = '到期结束';
			switch ($v['recommend_type'])
			{
				case 1:
					$data[$k]['page'] = '推荐';
					break;
				
				case 2:
					$data[$k]['page'] = '排行榜';
					break;
					
				case 3:
					$data[$k]['page'] = '分类-';
					$result = $category->where(array('category_id' => $v['category_id']))->find();
					$data[$k]['page'] .= $result['name'];
					if (strpos($v['key'], 'new'))
						$data[$k]['page'] .= '-最新';
					elseif (strpos($v['key'], 'hot'))
						$data[$k]['page'] .= '-最热';
					break;
				case 4:
					$data[$k]['page'] = '免费榜';
					break;
			}
			//var_dump($temp);
		}
		//var_dump($data);
		$category_list = $category->query('select * from sj_category where status=1 and parentid in (select category_id from sj_category where parentid=3)');
		$this->assign('category', $category_list);
		$this->assign('list', $data);
		$this->assign('page', $show);
		$this->display();
	}

	/**
	 * 审核记录
	 */
	public function VerifyHistory()
	{
		import("@.ORG.Page");
		$recommendverify = D('Sj.EbookRecommendVerify');
		$category = M('category');
		$soft = M('soft');
		
		$start_time = isset($_GET['start_time']) ? $_GET['start_time'] : '';
		$end_time = isset($_GET['end_time']) ? $_GET['end_time'] : '';
		if (!empty($start_time) && !empty($end_time) && strtotime($start_time) > strtotime($end_time))
			$this->error('搜索开始时间不能大于结束时间');
		$search_key = isset($_GET['search_key']) ? $_GET['search_key'] : '';
		$recommend_type = isset($_GET['recommend_type']) ? $_GET['recommend_type'] : '';
		$key = isset($_GET['key']) ? $_GET['key'] : '';
		$this->assign('start_time', $start_time);
		$this->assign('end_time', $end_time);
		$this->assign('search_key', $search_key);
		$this->assign('recommend_type', $recommend_type);
		$this->assign('cache_key', $key);
		$where = array();
		$join = 'WHERE';	
		if (!empty($start_time))
		{
			$where[] = "(a.endtime>=" . strtotime($start_time) . ")";
		}
		if (!empty($end_time))
		{
			$where[] = "(a.begintime<=" . (strtotime($end_time) + 86399) . ")";
		}
		if (!empty($_GET['search_key']))
		{
			$pkglist = array("''");
			$tosearch = $recommendverify->where('verify_status != 0')->field('distinct(package) as pkg')->select();
			foreach ($tosearch as $v)
			{
				$name = $soft->where(array('package' => $v['pkg'], 'status' => 1))->order('softid desc')->find();
				if (preg_match('/' . $search_key . '/i', $name['softname']) > 0)
				{
					$pkglist[] = "'" . $v['pkg'] . "'";
					$softname[$v['pkg']] = $name['softname'];
				}
			}
			$pkgstr = '(' . implode(',', $pkglist) . ')';
			$where[] = "(a.package in $pkgstr or a.package like '%$search_key%')";
			//$join = 'INNER JOIN sj_soft AS b ON';
			//$where[] = "(a.`package`=b.`package` AND (a.`package` LIKE '%" . $search_key . "%' OR (b.softname LIKE '%" . $search_key . "%' AND b.status=1)))";
		}
		if (!empty($recommend_type))
		{
			$where[] = "(a.recommend_type=$recommend_type)";
		}
		if (($recommend_type == 3) && !empty($key))
		{
			$where[] = "(a.key='$key')";
		}
		$where[] = "a.verify_status != 0";
		$wherestring = implode(' AND ', $where);
		$count = count($recommendverify->query("SELECT distinct(a.id) FROM sj_ebook_recommend_verify AS a $join $wherestring"));
		//var_dump($count);
		$param = http_build_query($_GET);
		$Page = new Page($count, 30, $param);
		$show = $Page->show();
		if (empty($Page->listRows))
			$Page->listRows = 30;
		$data = $recommendverify->query("SELECT d.* FROM sj_ebook_recommend_verify AS d WHERE d.id IN (SELECT DISTINCT(a.id) FROM sj_ebook_recommend_verify AS a $join $wherestring)  ORDER BY d.`last_refresh` DESC LIMIT " . $Page->firstRow . "," . $Page->listRows);
		//var_dump("SELECT d.* FROM sj_ebook_recommend_verify AS d WHERE d.id IN (SELECT DISTINCT(a.id) FROM sj_ebook_recommend_verify AS a $join $wherestring)  ORDER BY d.`last_refresh` DESC LIMIT " . $Page->firstRow . "," . $Page->listRows);
		
		foreach ($data as $k => $v)
		{
			if (!empty($softname[$v['package']]))
			{
				$temp['softname'] = $softname[$v['package']];
			}
			else
			{
				$temp = $soft->where(array('package' => $v['package'], 'status' => 1))->order('softid desc')->find();
				$softname[$v['package']] = $temp['softname'];
			}
			if ($temp)
				$data[$k]['softname'] = $temp['softname'];
			else
				$data[$k]['softname'] = '(包名不存在)';
			$data[$k]['begintime'] = date('Y-m-d', $data[$k]['begintime']);
			$data[$k]['endtime'] = date('Y-m-d', $data[$k]['endtime']);
			$data[$k]['last_refresh'] = date('Y-m-d H:i:s', $data[$k]['last_refresh']);
			switch ($v['recommend_type'])
			{
				case 1:
					$data[$k]['page'] = '推荐';
					break;
				
				case 2:
					$data[$k]['page'] = '排行榜';
					break;
					
				case 3:
					$data[$k]['page'] = '分类-';
					$result = $category->where(array('category_id' => $v['category_id']))->find();
					$data[$k]['page'] .= $result['name'];
					if (strpos($v['key'], 'new'))
						$data[$k]['page'] .= '-最新';
					elseif (strpos($v['key'], 'hot'))
						$data[$k]['page'] .= '-最热';
					break;
				case 4:
					$data[$k]['page'] = '免费榜';
					break;
			}
			//var_dump($temp);
		}
		//var_dump($data);
		$category_list = $category->query('select * from sj_category where status=1 and parentid in (select category_id from sj_category where parentid=3)');
		$this->assign('category', $category_list);
		$this->assign('list', $data);
		$this->assign('page', $show);
		$this->display();
	}
}
?>
