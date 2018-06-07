<?php
class ActivityModel extends Model{

	protected $trueTableName = 'sj_activity';

	public function getActivityList($options)
	{
		$result = $this->where($options)->order("id desc")->select();
		return $result;
	}
/**备份
	 public function getActivityList()
	{
		$result = $this->where(array('status' => 1))->select();
		return $result;
	}
 */
	public function getcount($options)
	{
		$result = $this->where($options)->count();
		return $result;
	}
/*备份原来
	public function getcount()
	{
		$result = $this->where(array('status' => 1))->count();
		return $result;
	}
	*/
	public function arr_arr($options)
	{
		$arr = $this->where($options)->limit($Page->firstRow.','.$Page->listRows)->select();
		return $arr;
	}
/*备份原来
 	public function arr_arr()
	{
		$arr = $this->where(array('status' => 1))->limit($Page->firstRow.','.$Page->listRows)->select();
		return $arr;
	}
 */
	public function addActivity($data)
	{
		$timestamp = time();
		$data['create_at'] = $timestamp;
		$data['last_refresh'] = $timestamp;
		$result = $this->add($data);
		return $result;
	}

	public function editActivity($data)
	{
		$timestamp = time();
		$data['last_refresh'] = $timestamp;
		$result = $this->where(array('id' => $data['id']))->save($data);
		return $result;
	}

	public function delActivity($id)
	{
		$result = $this->where(array('id' => $id))->save(array('status' => 0));
		return $result;
	}
}