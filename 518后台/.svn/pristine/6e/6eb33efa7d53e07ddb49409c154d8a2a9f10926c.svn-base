<?php
class EbookRecommendModel extends Model{
	
	protected $trueTableName = 'sj_ebook_recommend';
	
	public function getActiveRecommend()
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
	
	public function getUnactiveRecommend()
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
	
	public function disableRecommend($id)
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
	
	public function enableRecommend($id)
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
	
	public function deleteRecommend($id)
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
	
	public function refuseRecommend($id)
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
	
	public function addRecommend($data)
	{
	}
	
	public function editRecommend($data)
	{
		$data['last_refresh'] = time();
		$where = array(
			'id' => $data['id'],
		);
		unset($data['operation']);
		unset($data['recommend_id']);
		unset($data['refuse_msg']);
		$result = $this->where($where)->save($data);
		return $result;
	}
}
