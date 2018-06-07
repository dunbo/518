<?php
class SoftNameCountModel extends Model{
	protected $trueTableName = 'sj_soft';
	//获取软件名称数量
	
	public function get_soft_count($softname,$package){
		static $result;
		if(!$result){
			$result=$this->where(array("softname"=>$softname,"status"=>1,"hide"=>1,"package"=>array("neq",$package)))->count();
		}
		return $result;
	}


}

?>