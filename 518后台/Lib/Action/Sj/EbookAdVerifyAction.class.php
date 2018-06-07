<?php
/**
 * 推荐管理-iReader轮播图审核
 * Added by 黄文强
 * 2013/05/15
 */
class EbookAdVerifyAction extends CommonAction
{
	/**
	 * 待审核列表
	 */
	public function UnverifiedList()
	{
		import("@.ORG.Page");
		$ad = D('Sj.EbookAd');
		$adverify = D('Sj.EbookAdVerify');
		$soft = M('soft');
		$now = time();
		if (isset($_GET['action']))
		{
			$action = $_GET['action'];
			$id = $_REQUEST['id'];
			$check = $adverify->where(array('id' => $id))->find();
			if ($check['verify_status'] != 0)
			{
				$this->error('操作失败，请重试');
			}
			$log_msg = '';
			switch ($action)
			{
				case 'pass':
					$id = $_GET['id'];
					if (empty($id))
						$this->error('非法请求，请修改后重试');
					$data = $adverify->where(array('id' => $id))->find();
					switch ($data['operation'])
					{
						case 1:
							$count = $ad->where(array('status' => 1, 'hide' => 1, 'endtime' => array('GT', $now)))->count();
							if ($count >= 10 && $data['endtime'] >= $now)
							{
								$this->error('当前已有超过10个轮播图');
							}
							$result = $ad->enableAd($data['adid']);
							break;

						case 2:
							$count = $ad->where(array('status' => 1, 'hide' => 1, 'endtime' => array('GT', $now)))->count();
							if ($count >= 10 && $data['endtime'] >= $now)
							{
								$flag = $ad->where(array('status' => 1, 'hide' => 1, 'endtime' => array('GT', $now), 'id' => $data['adid']))->count();
								if ($flag != 1)
								{
									$this->error('当前已有超过10个轮播图');
								}
							}
							$data['id'] = $data['adid'];
							$data['verify_status'] = 1;
							$result = $ad->editAd($data);
							break;

						case 3:
							$result = $ad->deleteAd($data['adid']);
							break;

						default:
							$result = false;
					}
					$log_msg = "推荐管理-iReader轮播图审核-通过了ID为" . $id . "的电子书轮播图修改申请";
					if ($result !== false)
					{
						$flag = $adverify->passVerify($id);
						//$flag2 = $ad->passAd($data['adid']);
					}
					if ($flag === false)
					{
						$this->error("审核通过失败");
					}
					else
					{
						$flag1 = $adverify->passVerify($id);
						$this->writelog($log_msg);
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
					$flag1 = $adverify->refuseVerify($id, $refuse_msg);
					$data = $adverify->where(array('id' => $id))->find();
					$flag2 = $ad->refuseAd($data['adid']);
					//var_dump($flag1, $flag2);
					$log_msg = "推荐管理-iReader轮播图审核-拒绝了ID为" . $id . "的电子书轮播图修改申请";
					if ($flag1 === false || $flag2 === false)
					{
						$this->error("审核拒绝失败");
					}
					else
					{
						$this->writelog($log_msg);
						$this->success("成功拒绝");
					}
					break;

				default:
					$this->error('非法请求，请重试');
			}
		}
		else
		{
			$start_time = isset($_GET['start_time']) ? $_GET['start_time'] : '';
			$end_time = isset($_GET['end_time']) ? $_GET['end_time'] : '';
			$search_key = isset($_GET['search_key']) ? $_GET['search_key'] : '';
			if (!empty($start_time) && !empty($end_time) && strtotime($start_time) > strtotime($end_time))
				$this->error('搜索开始时间不能大于结束时间');
			$this->assign('start_time', $start_time);
			$this->assign('end_time', $end_time);
			$this->assign('search_key', $search_key);
			$where = array();
			$join = 'WHERE';
			$idlist = array();
			$softname = array();
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
				$tosearch = $adverify->where('verify_status=0')->field('distinct(package) as pkg')->select();
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
			}
			$where[] = "a.verify_status=0";
			$wherestring = implode(' AND ', $where);
			//$data = $adverify->getUnverifiedList();
			$count = count($adverify->query("SELECT distinct(a.id) FROM sj_ebook_ad_verify AS a $join $wherestring"));
			//var_dump($count);
			$param = http_build_query($_GET);
			$Page = new Page($count, 30, $param);
			$show = $Page->show();
			if (empty($Page->listRows))
				$Page->listRows = 30;
			$data = $adverify->query("SELECT d.* FROM sj_ebook_ad_verify AS d where d.id in (SELECT DISTINCT(a.id) FROM sj_ebook_ad_verify AS a $join $wherestring) ORDER BY d.`last_refresh` ASC LIMIT " . $Page->firstRow . "," . $Page->listRows);
			//var_dump("SELECT d.* FROM sj_ebook_ad_verify AS d INNER JOIN (SELECT DISTINCT(a.id) FROM sj_ebook_ad_verify AS a $join $wherestring) as c on c.id=d.id ORDER BY d.`last_refresh` DESC LIMIT " . $Page->firstRow . "," . $Page->listRows);
			
			foreach ($data as $k => $v)
			{
				$idlist[] = $v['id'];
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
				//var_dump($temp);
			}
			//var_dump($data);
			$this->assign('idlist', json_encode($idlist));
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
		$ad = D('Sj.EbookAd');
		$soft = M('soft');

		$start_time = isset($_GET['start_time']) ? $_GET['start_time'] : '';
		$end_time = isset($_GET['end_time']) ? $_GET['end_time'] : '';
		$search_key = isset($_GET['search_key']) ? $_GET['search_key'] : '';
		if (!empty($start_time) && !empty($end_time) && strtotime($start_time) > strtotime($end_time))
			$this->error('搜索开始时间不能大于结束时间');
		$now = time();
		$this->assign('start_time', $start_time);
		$this->assign('end_time', $end_time);
		$this->assign('search_key', $search_key);
		$where = array();
		$join = 'WHERE';
		$idlist = array();
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
			$tosearch = $ad->where('status=1 and hide=1')->field('distinct(package) as pkg')->select();
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
		$where[] = "a.status=1 and a.hide=1 and a.endtime>={$now}";

		$wherestring = implode(' AND ', $where);
		//$data = $adverify->getUnverifiedList();
		$count = count($ad->query("SELECT distinct(a.id) FROM sj_ebook_ad AS a $join $wherestring"));
		//var_dump($count);

		$param = http_build_query($_GET);
		$Page = new Page($count, 30, $param);
		$show = $Page->show();
		if (empty($Page->listRows))
			$Page->listRows = 30;

		$data = $ad->query("SELECT d.* FROM sj_ebook_ad AS d where d.id in (SELECT DISTINCT(a.id) FROM sj_ebook_ad AS a $join $wherestring) ORDER BY d.`rank`,d.`begintime` ASC LIMIT " . $Page->firstRow . "," . $Page->listRows);
		//var_dump("SELECT d.* FROM sj_ebook_ad_verify AS d INNER JOIN (SELECT DISTINCT(a.id) FROM sj_ebook_ad_verify AS a $join $wherestring) as c on c.id=d.id ORDER BY d.`last_refresh` DESC LIMIT " . $Page->firstRow . "," . $Page->listRows);
		
		foreach ($data as $k => $v)
		{
			$idlist[] = $v['id'];
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
			//var_dump($temp);
		}
		//var_dump($data);
		$this->assign('idlist', json_encode($idlist));
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
		$ad = D('Sj.EbookAd');
		$soft = M('soft');
		
		$start_time = isset($_GET['start_time']) ? $_GET['start_time'] : '';
		$end_time = isset($_GET['end_time']) ? $_GET['end_time'] : '';
		$search_key = isset($_GET['search_key']) ? $_GET['search_key'] : '';
		if (!empty($start_time) && !empty($end_time) && strtotime($start_time) > strtotime($end_time))
			$this->error('搜索开始时间不能大于结束时间');
		$now = time();
		$this->assign('start_time', $start_time);
		$this->assign('end_time', $end_time);
		$this->assign('search_key', $search_key);
		$where = array();
		$join = 'WHERE';
		$idlist = array();
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
			$tosearch = $ad->where("status=0 or endtime<{$now}")->field('distinct(package) as pkg')->select();
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
		$where[] = "(a.status=0 or a.endtime<{$now})";

		$wherestring = implode(' AND ', $where);
		//$data = $adverify->getUnverifiedList();
		$count = count($ad->query("SELECT distinct(a.id) FROM sj_ebook_ad AS a $join $wherestring"));
		//var_dump($count);

		$param = http_build_query($_GET);
		$Page = new Page($count, 30, $param);
		$show = $Page->show();
		if (empty($Page->listRows))
			$Page->listRows = 30;

		$data = $ad->query("SELECT d.* FROM sj_ebook_ad AS d where d.id in (SELECT DISTINCT(a.id) FROM sj_ebook_ad AS a $join $wherestring) ORDER BY d.`last_refresh` DESC LIMIT " . $Page->firstRow . "," . $Page->listRows);
		//var_dump("SELECT d.* FROM sj_ebook_ad_verify AS d INNER JOIN (SELECT DISTINCT(a.id) FROM sj_ebook_ad_verify AS a $join $wherestring) as c on c.id=d.id ORDER BY d.`last_refresh` DESC LIMIT " . $Page->firstRow . "," . $Page->listRows);
		
		foreach ($data as $k => $v)
		{
			$idlist[] = $v['id'];
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
			//var_dump($temp);
		}
		//var_dump($data);
		$this->assign('idlist', json_encode($idlist));
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
		$adverify = D('Sj.EbookAdVerify');
		$soft = M('soft');
		
		$start_time = isset($_GET['start_time']) ? $_GET['start_time'] : '';
		$end_time = isset($_GET['end_time']) ? $_GET['end_time'] : '';
		$search_key = isset($_GET['search_key']) ? $_GET['search_key'] : '';
		if (!empty($start_time) && !empty($end_time) && strtotime($start_time) > strtotime($end_time))
			$this->error('搜索开始时间不能大于结束时间');
		$this->assign('start_time', $start_time);
		$this->assign('end_time', $end_time);
		$this->assign('search_key', $search_key);
		$where = array();
		$join = 'WHERE';
		$idlist = array();
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
			$tosearch = $adverify->where('verify_status != 0')->field('distinct(package) as pkg')->select();
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
		$where[] = "a.verify_status != 0";
		$wherestring = implode(' AND ', $where);
		$count = count($adverify->query("SELECT distinct(a.id) FROM sj_ebook_ad_verify AS a $join $wherestring"));
		//var_dump($count);
		$param = http_build_query($_GET);
		$Page = new Page($count, 30, $param);
		$show = $Page->show();
		if (empty($Page->listRows))
			$Page->listRows = 30;
		$data = $adverify->query("SELECT d.* FROM sj_ebook_ad_verify AS d where d.id in (SELECT DISTINCT(a.id) FROM sj_ebook_ad_verify AS a $join $wherestring) ORDER BY d.`last_refresh` DESC LIMIT " . $Page->firstRow . "," . $Page->listRows);
		//var_dump("SELECT d.* FROM sj_ebook_ad_verify AS d INNER JOIN (SELECT DISTINCT(a.id) FROM sj_ebook_ad_verify AS a $join $wherestring) as c on c.id=d.id ORDER BY d.`last_refresh` DESC LIMIT " . $Page->firstRow . "," . $Page->listRows);
		
		foreach ($data as $k => $v)
		{
			$idlist[] = $v['id'];
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
			//var_dump($temp);
		}
		//var_dump($data);
		$this->assign('idlist', json_encode($idlist));
		$this->assign('list', $data);
		$this->assign('page', $show);
		$this->display();
	}
}
?>
