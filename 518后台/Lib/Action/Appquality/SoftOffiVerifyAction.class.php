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
	private $jb = array(
		0 => '普通',
		1 => '首发',
		2 => '汉化',
		null => '无'
	);

	public function __construct() {
		include_once SERVER_ROOT . '/tools/functions.php';
		parent::__construct();
		$this->verifytable = D('Appquality.SoftOffiVerify');			
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
				<a target='_blank' href='/index.php/Dev/Soft/softlist/softid/" . $soft['softid'] . "'>
					<img src='" . IMGATT_HOST . $softFileinfo['iconurl'] . "'>
				</a>";
			$softlist[$index]['softinfo_reset'] = "<a target='_blank' href='http://www.anzhi.com/soft_" . 
				$soft['softid'] . ".html'><b>" . $soft['softname'] . "</b></a><br/><font style='color:#009600'>" . $soft['package'] . "<br/>" . $softCategoryinfo['name'] . '|' . $this->jb[$soft['type']] . "</font><p>" . $this->getLanguage($soft['language']) . " | 版本号：" .	$soft['version_code'] . " | 版本名：" . $soft['version'] . "</p>";
			$softlist[$index]['developer_reset'] = "
				<a target='_blank' href='/index.php/Dev/User/userlists/dev_id/" . 
				$softDeveloperinfo['dev_id'] . "'>" . $softDeveloperinfo['dev_name'] . "</a><br />" . 
				$this->getDeveloperType($softDeveloperinfo['type']) . "<br />" . $softDeveloperinfo['email']; 
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
		return is_numeric($type) && $type == 0 ? '公司' : ($type == 1 ? '个人' : ($type == 2 ? '团队' : '未知'));	
	}
	private function getSoftHide($hide) {
		return is_numeric($hide) && $hide == 0 ? '历史' : ($hide == 1 ? '上架' : ($hide == 2 ? '新软件' : ($hide == 3
		? '下架' : ($hide == 4 ? '编辑软件' : ($hide == 5 ? '更新软件' : ($hide == 6 ? '驳回'
		: ($hide == 7 ? '驳回审核' : ($hide == 1024 ? '渠道软件' : '未知'))))))));
	}
	/**
	 * 添加分页
	 */
	public function addPage($count) {
		import('@.ORG.Page2');
		$this->page = new Page($count,20);
		$this->page->rollPage = 10; 
		$this->page->setConfig('header','条记录&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
		$this->page->setconfig('first','首页');
		$this->page->setconfig('last','尾页');
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
		if ($_GET['add_stm']) {
			$this->assign('add_stm', $_GET['add_stm']);	
		}
		if ($_GET['add_etm']) {
			$this->assign('add_etm', $_GET['add_etm']);	
		}
		if ($_GET['edit_stm']) {
			$this->assign('edit_stm', $_GET['edit_stm']);	
		}
		if ($_GET['edit_stm']) {
			$this->assign('edit_etm', $_GET['edit_etm']);	
		}
		$this->assign('downloadMin', $_GET['downloadMin']);	
		$this->assign('downloadMax', $_GET['downloadMax']);	
		if (ACTION_NAME == 'unverifyList') {
			$this->assign('Val', $_GET['orderVal'] ? $_GET['orderVal'] : 1);
		} else {
			$this->assign('Val', $_GET['orderVal'] ? $_GET['orderVal'] : -1);
		}
		$this->assign('Type', $_GET['orderType']);
		if ($_GET['cateid']) {
			$this->assign('cateid', array_flip(explode(',',$_GET['cateid'])));
		}
	}
	/**
	 * 批量审核操作 
	 */
	public function verifyAction() {
		if (addslashes($_POST['actType']) == 'pass') {
			foreach ($_POST['app'] as $id) {
				$this->passAction($id);	
				$this->verifytable->updateSoftisoffice($id);
			}		
			$this->success('审核通过成功');	
		} else if (addslashes($_GET['actType']) == 'restore') {
			$apps = explode(',', $_GET['app']);
			foreach ($apps as $id) {
				$this->restoreAction($id);	
				$this->verifytable->updateSoftisunoffice($id);
			}			
			//$this->success('审核撤销成功');	
		} else if (addslashes($_GET['actType']) == 'deldoc') {
			$apps = explode(',', $_GET['app']);
			foreach ($apps as $id) {
				$this->deldocAction($id);	
			}			
			//$this->success('审核删除成功');	
		}
	}

	/**
	 * 审核拒绝
	 */
	public function verifyRefuse() {
		if (!$this->softid)  {
			$this->error('操作失败,请选择需要操作的记录');	
		}
		$reason = $_GET['reason'];
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
		}
	}

	public function refuseDelDoc() {
		$res = $this->deldocAction($this->softid);	
//		if ($res) {
//			$this->success('审核删除成功');	
//		} else {
//			$this->error('审核删除失败');	
//		}
	}

	private function refuseAction($softid, $reason='') {
		$this->writelog('softid为:' . $softid . ';包名为:' . $this->soft['package'] . '，软件官方审核驳回','sj_soft_official_result',"softid:{$softid}",__ACTION__ ,"","edit");
		$res = $this->verifytable->verifyRefuse($softid, $reason);
		$this->verifytable->updateSignStatusdown($this->soft['package']);
		return $res;
	}

	private function passAction($softid) {
		$this->writelog('softid为:' . $softid . ';包名为:' . $this->soft['package'] . '，软件官方审核通过','sj_soft_official_result',"softid:{$softid}",__ACTION__ ,"","edit");
		//签名
		$this->passSign($softid);

		$res = $this->verifytable->verifyPass($softid);
		return $res;
	}
	private function restoreAction($softid) {
		$this->writelog('softid为:' . $softid . ';包名为:' . $this->soft['package'] . '，软件官方审核撤销','sj_soft_official_result',"softid:{$softid}",__ACTION__ ,"","edit");
		$this->verifytable->updateSignStatusdown($this->soft['package']);
		$res = $this->verifytable->verifyRestore($softid);
		return $res;
	}
	/**
	 * 审核拒绝列表删除记录
	 */
	public function deldocAction($softid) {
		$this->writelog('softid为:' . $softid . ';包名为:' . $this->soft['package'] . '，审核拒绝软件删除','sj_soft_official_result',"softid:{$softid}",__ACTION__ ,"","del");
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
			$desc .= '<p>腾讯</p>';	
		}

		if (($bitwise | 2) == $bitwise) {
			$desc .= '<p>安全管家</p>';	
		}

		if (($bitwise | 4) == $bitwise) {
			$desc .= '<p>金山</p>';	
		}

		if (($bitwise | 8) == $bitwise) {
			$desc .= '<p>360</p>';	
		}	

		return $desc;
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
