<?php
/**
 * 软件官方审核
 */
class SoftOffiVerifyModel extends Model {

	protected $trueTableName = 'sj_soft_official_result';
	private $condition = array();
	private $order = '';
	private $where = Array();	
	//官方位 qq 1, anguanjia 2, kingsoft 4, 360 8
	private $offi_bit =	0;	

	public function unverifyCount() {
		$this->condition = 'SR.verify = 0 AND SR.status = 1';		
		$this->_search();
		$result = $this->table('sj_soft_official_result AS SR')
			->join('sj_soft AS SS on SS.softid = SR.softid')
			->join('sj_soft_file AS SF on SR.md5_file = SF.md5_file')
			->join('pu_developer AS PD on SS.dev_id = PD.dev_id')
			->where($this->condition)
			->count();
		return $result;
	}
	/**
	 * 未审核列表
	 */
	public function unverifyList($page) {
		$this->condition= 'SR.verify = 0 AND SR.status = 1';		
		$this->_search();
		$this->_order();
		$result = $this->table('sj_soft_official_result AS SR')
			->join('sj_soft AS SS on SS.softid = SR.softid')
			->join('sj_soft_file AS SF on SR.md5_file = SF.md5_file')
			->join('pu_developer AS PD on SS.dev_id = PD.dev_id')
			->where($this->condition)
			->order($this->order)
			->limit($page->firstRow . ',' . $page->listRows)
			->select();	
		return $result;
	}
	public function verifyPassCount() {
		$this->condition= 'SR.verify = 1 AND SR.status = 1';		
		$this->_search();
		$result = $this->table('sj_soft_official_result AS SR')
			->where($this->condition)
			->join('sj_soft AS SS on SS.softid = SR.softid')
			->join('sj_soft_file AS SF on SR.md5_file = SF.md5_file')
			->join('pu_developer AS PD on SS.dev_id = PD.dev_id')
			->count();	
		return $result;
	}
	/**
	 * 审核通过列表
	 */
	public function verifyPassList($page) {
		$this->condition= 'SR.verify = 1 AND SR.status = 1';		
		$this->_search();
		$this->_order();
		$result = $this->table('sj_soft_official_result AS SR')
			->where($this->condition)
			->join('sj_soft AS SS on SS.softid = SR.softid')
			->join('sj_soft_file AS SF on SR.md5_file = SF.md5_file')
			->join('pu_developer AS PD on SS.dev_id = PD.dev_id')
			->limit($page->firstRow . ',' . $page->listRows)
			->order($this->order)
			->select();	
		return $result;
	}
	public function verifyRefuseCount() {
		$this->condition= 'SR.verify = -1 AND SR.status = 1';		
		$this->_search();
		$result = $this->table('sj_soft_official_result AS SR')
			->where($this->condition)
			->join('sj_soft AS SS on SS.softid = SR.softid')
			->join('sj_soft_file AS SF on SR.md5_file = SF.md5_file')
			->join('pu_developer AS PD on SS.dev_id = PD.dev_id')
			->count();	
		return $result;
	}
	/**
	 * 审核拒绝列表
	 */
	public function verifyRefuseList($page) {
		$this->condition= 'SR.verify = -1 AND SR.status = 1';		
		$this->_search();
		$this->_order();
		$result = $this->table('sj_soft_official_result AS SR')
			->where($this->condition)
			->join('sj_soft AS SS on SS.softid = SR.softid')
			->join('sj_soft_file AS SF on SR.md5_file = SF.md5_file')
			->join('pu_developer AS PD on SS.dev_id = PD.dev_id')
			->limit($page->firstRow . ',' . $page->listRows)
			->order($this->order)
			->select();	
		return $result;
	}
	/**
	 * 搜索
	 */
	public function _search() {
		$this->condition .= ' AND SF.package_status = 1 AND SS.status = 1 AND SR.official_bitwise = SR.official_bitwise | ' . $this->offi_bit;

		if (($mainType = $_GET['searchMainType']) && ($mainStr = $_GET['searchMainStr'])) {
			switch ($mainType) {
				case '1':
					$this->condition .= ' AND (SS.softname like "' . "%{$mainStr}%" . '")';
					break;
				case '2':
					$this->condition .= ' AND (SS.package = "' . $mainStr . '")';
					break;
				case '3':
					$this->condition .= ' AND (SS.softid = ' . $mainStr . ')';
					break;
				case '4':
					$this->condition .= ' AND (PD.dev_name like "' . "%{$mainStr}%" . '")';
					break;
				case '5':
					$this->condition .= ' AND (PD.email like "' . "%{$mainStr}%" . '")';
					break;
			}	
		}
		if (($devType = $_GET['searchDevType']) != null) {
			$this->condition .= ' AND (PD.type = "' . $devType . '")';	
		}
		//来源
		if ($offiType = $_GET['offiType']) {
			if ($offiType == 8) {
				$this->condition .= ' AND (SR.official_bitwise in (8,10,11,12,13,14,15))';		
			} else if ($offiType == 4) {
				$this->condition .= ' AND (SR.official_bitwise in (4,5,6,7,12,13,14,15))';		
			} else if ($offiType == 2) {
				$this->condition .= ' AND (SR.official_bitwise in (2,3,6,7,10,11,14,15))';		
			} else if ($offiType == 1) {
				$this->condition .= ' AND (SR.official_bitwise in (1,3,5,7,9,11,13,15))';		
			}
		}
		//软件状态
		if (($hide = $_GET['hide']) != null) {
			$this->condition .= ' AND (SS.hide = ' . $hide . ')';	
		} else {
			$this->condition .= ' AND SS.hide in(0, 1)';	
		}
		//日期
		if ($s_tm = $_GET['add_stm']) {
			$this->condition .= ' AND (SR.dateline >=' . strtotime($s_tm) . ')';
		} else if ($s_tm = $_GET['edit_stm']) {
			$this->condition .= ' AND (SR.updatetm >=' . strtotime($s_tm) . ')';
		}
		if ($e_tm = $_GET['add_etm']) {
				$this->condition .= ' AND (SR.dateline <=' . strtotime($e_tm) . ')';
		} else if ($e_tm = $_GET['edit_etm']) {
			$this->condition .= ' AND (SR.updatetm <=' . strtotime($e_tm) . ')';
		} 
		if ($downloadMin = intval($_GET['downloadMin'])) {
			$this->condition .= ' AND (SS.total_downloaded >=' . $downloadMin . ')';
		}
		if ($downloadMax = intval($_GET['downloadMax'])) {
			$this->condition .= ' AND (SS.total_downloaded <=' . $downloadMax . ')';
		}

		if ($_GET['cateid']) {
			$rescateidArr = explode(',', trim($_GET['cateid'], ','));
			$rescateid = "'," . implode(",',',", $rescateidArr) . ",'";
			$this->condition .= " AND (SS.category_id in (" . $rescateid . "))";	
		}
	}
	public function _order($softid) {
		if ($_GET['orderType']) {
			if ($_GET['orderVal'] == -1) {
				$op = 'DESC'; 
			} else {
				$op = 'ASC';	
			}
			$this->order = $_GET['orderType'] . ' ' . $op;	
		} else if ($_GET['tab'] != 'manual') {
			if (ACTION_NAME == 'unverifyList') {
				$this->order = 'SR.dateline ASC';	
			} else if (ACTION_NAME == 'verifyPassList') {
				$this->order = 'SR.updatetm DESC';	
			} else if (ACTION_NAME == 'verifyRefuseList') {
				$this->order = 'SR.updatetm DESC';	
			}
		} 
	}
	/**
	 * 审核通过
	 */
	public function verifyPass($softid) {
		$result = $this->where(array('softid' => $softid))
			->data(array('verify' => "1", 'updatetm' => time()))
			->save();	
		return $result;
	}

	public function updateSoftisoffice($softid) {
		$result = $this->query("update sj_soft set isoffice = 1 where softid = {$softid}");
		return $result;
	}

	public function updateSoftisunoffice($softid) {
		$result = $this->query("update sj_soft set isoffice = 0 where softid = {$softid}");
		return $result;
	}
	/**
	 * 审核拒绝
	 */
	public function verifyRefuse($softid, $reason='') {
		$result = $this->where(array('softid' => $softid))
			->save(array('verify' => "-1", 'refuse_reason' => $reason, 'updatetm' => time()));	
		return $result;
	}
	public function verifyRestore($softid) {
		$result = $this->where(array('softid' => $softid))
			->save(array('verify' => "0", 'refuse_reason' => '', 'updatetm' => time()));	
		return $result;
	}
	/**
	 * 审核拒绝列表记录删除
	 */
	public function refuseDelDoc($softid) {
		$result = $this->where(array('softid' => $softid))->save(array('status' => "-1", 'updatetm' => time()));	
		return $result;
	}

	/**
	 * 得到软件
	 */
	public function getSoftOne($softid) {
		$result = $this->query("select * from sj_soft where softid = {$softid}");	
		return $result[0];
	}

	/**
	 * 得到软件File
	 */
	public function getSoftFileOne($softid) {
		$result = $this->query("select iconurl, url, md5_file, sha1_file 
				from sj_soft_file where softid = {$softid}");	
		return $result[0];
	}
	/**
	 * 得到软件分类
	 */
	public function getSoftCategoryOne($cid) {
		$result = $this->query("select name from sj_category where category_id = {$cid}");	
		return $result[0];
	}
	/**
	 * 得到开发者信息
	 */
	public function getSoftDeveloperOne($dev_id) {
		$result = $this->query("select * from pu_developer where dev_id = {$dev_id}");	
		return $result[0];
	}
	
	/**
	 * 签名
	 */
	public function getSign($package) {
		$result = $this->query("select * from sj_soft_sign where package = '{$package}'");	
		return $result[0];
	}
	public function updateSignStatusup($package) {
		$result = $this->query("update sj_soft_sign set status = 1 where package = '{$package}'");	
	}
	public function updateSignStatusdown($package) {
		$result = $this->query("update sj_soft_sign set status = 0 where package = '{$package}'");	
	}
	public function saveSign($data) {
		$this->query("insert sj_soft_sign(package, sign, dateline, status)
				values('{$data['package']}', '{$data['sign']}', '{$data['dateline']}', '{$data['status']}')");
	}

}
?>
