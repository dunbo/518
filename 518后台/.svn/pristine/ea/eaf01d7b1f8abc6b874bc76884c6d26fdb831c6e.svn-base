<?php
/**
 * 官方软件审核
 */
class SoftOffiVerifyAction extends CommonAction {
	//model
	private $verifytable;
	private $softid;
	private $soft;
	private $page;

	public function __construct() {
		include_once SERVER_ROOT . '/tools/functions.php';
		parent::__construct();
		$this->verifytable = D('Sj.SoftOffiVerify');			
		$this->softid = addslashes($_GET['softid']);
		if ($this->softid) {
			$this->soft = $this->verifytable->getSoftOne($this->softid);	
		}
		$this->softType();
	}
	/**
	 * 未审核列表
	 */
	public function unverifyList() {
		$count = $this->verifytable->unverifyCount();
		$this->addPage($count);
		$unverifyData = $this->verifytable->unverifyList($this->page);
		$unverifyData = $this->addOffiDesc($unverifyData);
		$unverifyData = $this->addExtInfo($unverifyData);
		$this->assign('unverifyData', $unverifyData);
		$this->assign('docs', $count);
		$this->assign('page', $this->page->show());
		$this->assignSearch();
		$this->display('unverifyList');
	}
	/**
	 * 已通过列表
	 */
	public function verifyPassList() {
		$this->assignSearch();
		$this->verifyPassList_system();
	}

	private function verifyPassList_system() {
		$count = $this->verifytable->verifyPassCount();
		$this->addPage($count);
		$verifyPassData = $this->verifytable->verifyPassList($this->page);
		$verifyPassData = $this->addOffiDesc($verifyPassData);
		$verifyPassData = $this->addExtInfo($verifyPassData);
		$this->assign('verifyPassData', $verifyPassData);
		$this->assign('docs', $count);
		$this->assign('page', $this->page->show());
		$this->display('verifyPassList');
	}
	/**
	 * 已拒绝列表
	 */
	public function verifyRefuseList() {
		$count = $this->verifytable->verifyRefuseCount();
		$this->addPage($count);
		$verifyRefuseData = $this->verifytable->verifyRefuseList($this->page);
		$verifyRefuseData = $this->addOffiDesc($verifyRefuseData);
		$verifyRefuseData = $this->addExtInfo($verifyRefuseData);
		$this->assign('verifyRefuseData', $verifyRefuseData);
		$this->assign('docs', $count);
		$this->assign('page', $this->page->show());
		$this->assignSearch();
		$this->display('verifyRefuseList');
	}
	/**
	 * 添加额外信息
	 */
	private function addExtInfo($softlist) {
		foreach ($softlist as $index => $soft) {
			$softinfo = $this->verifytable->getSoftOne($soft['softid']);	
			$softFileinfo = $this->verifytable->getSoftFileOne($soft['softid']);	
			$softCategoryinfo = $this->verifytable->getSoftCategoryOne(trim($softinfo['category_id'], ','));	
			$softDeveloperinfo = $this->verifytable->getsoftDeveloperOne($softinfo['dev_id']);
			//组合数据 
			$softlist[$index]['softid_reset'] = $soft['softid'] . "<br/>
				<a target='_blank' href='/index.php/Sj/Soft/soft_list/softid/" . $soft['softid'] . "'>
					<img src='" . IMGATT_HOST . $softFileinfo['iconurl'] . "'>
				</a>";
			$softlist[$index]['softinfo_reset'] = "<a target='_blank' href='http://www.anzhi.com/soft_" . 
				$soft['softid'] . ".html'>" . $soft['softname'] . "</a><br/>" . $soft['package'] . "<br/>" . 
				$softCategoryinfo['name'] . "</p><p>" . $this->getLanguage($soft['language']) . 
				" | 版本号：" .	$soft['version_code'] . " | 版本名：" . $soft['version'] . "</p>";
			$softlist[$index]['developer_reset'] = "
				<a target='_blank' href='/index.php/Admin/User/userlists/dev_id/" . 
				$softDeveloperinfo['dev_id'] . "'>" . $softinfo['dev_name'] . "</a><br />" . 
				$this->getDeveloperType($softDeveloperinfo['type']) . "<br />" . $softinfo['dever_email']; 
			$softlist[$index]['download_reset'] = intval($softinfo['total_downloaded']);
			$softlist[$index]['hide_reset'] = $this->getSoftHide($softinfo['hide']);
			$softlist[$index]['dateline_reset'] = date('Y-m-d H:i:s', $soft['dateline']);
			$softlist[$index]['updatetm_reset'] = date('Y-m-d H:i:s', $soft['updatetm']);
			$softlist[$index]['reason_reset'] = $soft['refuse_reason'];
			$softlist[$index]['offidesc_reset'] = $soft['official_desc'];
		}	
		return $softlist;
	}
	private function getLanguage($language) {
		return $language == 1 ? '中文' : ($language == 2 ? '英文' : '未知');	
	}
	private function getDeveloperType($type) {
		return $type == 0 ? '公司' : ($type == 1 ? '个人' : ($type == 2 ? '团队' : '未知'));	
	}
	private function getSoftHide($hide) {
		return $hide == 0 ? '历史' : ($hide == 1 ? '正常' : ($hide == 2 ? '新软件' : ($hide == 3
		? '下架' : ($hide == 4 ? '编辑软件' : ($hide == 5 ? '更新软件' : ($hide == 6 ? '驳回'
		: ($hide == 7 ? '驳回审核' : ($hide == 1024 ? '渠道软件' : '未知'))))))));
	}
	/**
	 * 添加分页
	 */
	public function addPage($count) {
		import('@.ORG.Page');
		$this->page = new Page($count,10);
	}
	/**
	 * 输出搜索条件数据
	 */
	public function assignSearch() {
		$this->assign('searchMainType', $_GET['searchMainType']);	
		$this->assign('searchMainStr', $_GET['searchMainStr']);	
		$this->assign('searchDevType', $_GET['searchDevType']);	
		$this->assign('offiType', $_GET['offiType']);	
		$this->assign('hide', $_GET['hide']);	
		$this->assign('s_tm', $_GET['s_tm']);	
		$this->assign('e_tm', $_GET['e_tm']);	
		$this->assign('downloadMin', $_GET['downloadMin']);	
		$this->assign('downloadMax', $_GET['downloadMax']);	
		$this->assign('Val', $_GET['orderVal'] ? $_GET['orderVal'] : 1);
		$this->assign('Type', $_GET['orderType']);
	}
	/**
	 * 批量审核操作 
	 */
	public function verifyAction() {
		if (!$_POST['app']) {
			$this->error('操作失败,请选择需要操作的记录');	
		}
		if (addslashes($_POST['actType']) == 'pass') {
			foreach ($_POST['app'] as $id) {
				$this->passAction($id);	
				$this->verifytable->updateSoftisoffice($id);
			}		
			$this->success('审核通过成功');	
		} else if (addslashes($_POST['actType']) == 'restore') {
			foreach ($_POST['app'] as $id) {
				$this->restoreAction($id);	
				$this->verifytable->updateSoftisunoffice($id);
			}			
			$this->success('审核撤销成功');	
		} else if (addslashes($_POST['actType']) == 'deldoc') {
			foreach ($_POST['app'] as $id) {
				$this->deldocAction($id);	
			}			
			$this->success('审核删除成功');	
		}
	}

	/**
	 * 审核拒绝
	 */
	public function verifyRefuse() {
		if (!$this->softid)  {
			$this->error('操作失败,请选择需要操作的记录');	
		}
		$reason = '';
		if ($_GET['reason']) {
			$reason .= $_GET['reason'];
		}
		if ($_GET['otherReason']) {
			$reason .= ' 其他原因:' . $_GET['otherReason'];	
		}
		$softArr = explode(',', $this->softid);
		foreach ($softArr as $softid) {
			$res = $this->refuseAction($softid, $reason);
		}
		if ($res) {
			$this->success('审核拒绝成功');	
			$this->verifytable->updateSoftisunoffice($this->softid);
		} else {
			$this->error('审核拒绝失败');	
		}
	}
	/**
	 * 审核通过
	 */
	public function verifyPass() {
		$res = $this->passAction($this->softid);
		if ($res) {
			$this->verifytable->updateSoftisoffice($this->softid);
			$this->success('审核通过成功');	
		} else {
			$this->error('审核通过失败');	
		}
	}

	public function verifyRestore() {
		$res = $this->restoreAction($this->softid);	
		if ($res) {
			$this->verifytable->updateSoftisunoffice($this->softid);
			$this->success('审核撤销成功');	
		} else {
			$this->error('审核撤销失败');	
		}
	}

	public function refuseDelDoc() {
		$res = $this->deldocAction($this->softid);	
		if ($res) {
			$this->success('审核删除成功');	
		} else {
			$this->error('审核删除失败');	
		}
	}

	private function refuseAction($softid, $reason='') {
		$this->writelog('softid为:' . $softid . ';包名为:' . $this->soft['package'] . '，软件官方审核拒绝');
		$res = $this->verifytable->verifyRefuse($softid, $reason);
		$this->verifytable->updateSignStatusdown($this->soft['package']);
		return $res;
	}

	private function passAction($softid) {
		$this->writelog('softid为:' . $softid . ';包名为:' . $this->soft['package'] . '，软件官方审核通过');
		//签名
		$this->passSign($softid);

		$res = $this->verifytable->verifyPass($softid);
		return $res;
	}
	private function restoreAction($softid) {
		$this->writelog('softid为:' . $softid . ';包名为:' . $this->soft['package'] . '，软件官方审核撤销');
		$this->verifytable->updateSignStatusdown($this->soft['package']);
		$res = $this->verifytable->verifyRestore($softid);
		return $res;
	}
	/**
	 * 审核拒绝列表删除记录
	 */
	public function deldocAction($softid) {
		$this->writelog('softid为:' . $softid . ';包名为:' . $this->soft['package'] . '，审核拒绝软件删除');
		$res = $this->verifytable->refuseDelDoc($softid);	
		return $res;
	}
	/**
	 * 添加官方检测详细信息
	 */
	public function addOffiDesc($data) {
		foreach ($data as $key => $val) {
			$data[$key]['official_desc'] = $this->officialDesc($val['official_bitwise']);	
		}
		return $data;
	}
	/**
	 * 官方状态处理
	 */
	private function officialDesc($bitwise) {
		$desc = '';
		if (($bitwise | 1) == $bitwise) {
			$desc .= ' | 腾讯';	
		}

		if (($bitwise | 2) == $bitwise) {
			$desc .= ' | 安全管家';	
		}

		if (($bitwise | 4) == $bitwise) {
			$desc .= ' | 金山';	
		}

		if (($bitwise | 8) == $bitwise) {
			$desc .= ' | 360';	
		}	

		return trim($desc, ' |');
	}

	private function passSign($softid) {
		if ($this->verifytable->getSign($this->soft['package'])) {
			$this->verifytable->updateSignStatusup($this->soft['package']);	
		} else {
			$softfile = $this->verifytable->getSoftFileOne($softid);
			if (!($sign = getSignFromApk('/data/att/m.goapk.com' . $softfile['url']))) {
				$sign = '';
			}
			$data['sign'] = $sign;
			$data['package'] = $this->soft['package'];
			$data['sha1_file'] = $softfile['sha1_file'];
			$data['md5_file'] = $softfile['md5_file'];
			$data['dateline'] = time();
			$data['status'] = 1;
			$this->verifytable->saveSign($data);
		}
	}

	private function softType() {
		$soft_tmp = D("Dev.Softaudit");
		$catname = $soft_tmp ->getCategoryArray();
		$cname = array();
		foreach($catname[0] as $n){
			$threecat = array();
			foreach($catname[$n['category_id']] as $v){
				foreach( $catname[$v['category_id']] as $m){
					$threecat[] = $m;
				}
			}
			$n['sub'] = $threecat;
			$cname[] = $n;			
		}
		$this->assign('cname', $cname);
	}
} 
