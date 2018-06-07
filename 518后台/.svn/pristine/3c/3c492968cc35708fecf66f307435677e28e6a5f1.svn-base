<?php
class SoftTemporaryModel extends Model{
	protected $trueTableName = 'sj_soft_temporary';
	//下架软件更新临时表状态为0
	public function update_soft_temporary($softid){
		$result=$this->where(array("softid"=>$softid,"status"=>1))->save(array("status"=>0));
		return $result;
	}


}

?>