<?php
class EbookAdModel extends Model{
	
	protected $trueTableName = 'sj_ebook_ad';
	
	public function getActiveAd()
	{
		$now = time();
		$where = array(
			'status' => 1,
			'start_tm' => array('ELT', $now),
			'end_tm' => array('EGT', $now),
		);
		$result = $this->where($where)->select();
		return $result;
	}
	
	public function getUnactiveAd()
	{
		$now = time();
		$where = array(
			'status' => 0,
			'end_tm' => array('LT', $now),
			'_logic' => 'OR'
		);
		$result = $this->where($where)->select();
		return $result;
	}
	
	public function disableAd($id)
	{
		$where = array(
			'id' => $id,
		);
		$data = array(
			'hide' => 2,
			'verify_status' => 1,
			'last_refresh' => time(),
		);
		$result = $this->where($where)->save($data);
		return $result;
	}
	
	public function enableAd($id)
	{
		$where = array(
			'id' => $id,
		);
		$data = array(
			'hide' => 1,
			'verify_status' => 1,
			'last_refresh' => time(),
		);
		$result = $this->where($where)->save($data);
		return $result;
	}
	
	public function deleteAd($id)
	{
		$where = array(
			'id' => $id,
		);
		$data = array(
			'status' => 0,
			'verify_status' => 1,
			'last_refresh' => time(),
		);
		$result = $this->where($where)->save($data);
		return $result;
	}
	
	public function refuseAd($id)
	{
		$where = array(
			'id' => $id,
		);
		$data = array(
			'verify_status' => 2,
			'last_refresh' => time(),
		);
		$result = $this->where($where)->save($data);
		return $result;
	}
	
	public function passAd($id)
	{
		$where = array(
			'id' => $id,
		);
		$data = array(
			'verify_status' => 1,
			'last_refresh' => time(),
		);
		$result = $this->where($where)->save($data);
		return $result;
	}
		
	public function editAd($data)
	{
		$data['last_refresh'] = time();
		$where = array(
			'id' => $data['id'],
		);
		$result = $this->where($where)->save($data);
		return $result;
	}
}