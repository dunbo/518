<?php
class EbookAdVerifyModel extends Model{
	
	protected $trueTableName = 'sj_ebook_ad_verify';
	
	public function getUnverifiedList()
	{
		$result = $this->where(array('verify_status' => 0))->select();
		return $result;
	}
	
	public function getRefusedList()
	{
		$result = $this->where(array('verify_status' => 2))->select();
		return $result;
	}
	
	public function getVerifyList()
	{
		$result = $this->where(array('status' => array('NEQ', 0)))->select();
		return $result;
	}
	
	public function passVerify($id)
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
	
	public function refuseVerify($id, $refuse_msg)
	{
		$where = array(
			'id' => $id,
		);
		$data = array(
			'refuse_msg' => $refuse_msg,
			'verify_status' => 2,
			'last_refresh' => time(),
		);
		$result = $this->where($where)->save($data);
		return $result;
	}
}