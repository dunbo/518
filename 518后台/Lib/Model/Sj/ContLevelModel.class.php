<?php
class ContLevelModel extends AdvModel {
    //调整表前缀
    protected $trueTableName = 'cont_level_stat';
	 
	public function addData($contid,$cont_level,$cont_quality,$cont_type,$from,$resid,$bgcard_id){
		if(!is_array($resid)) $ids = array($resid);
		else $ids = $resid;
		if(!empty($ids)){
			#目前批量操作都为通过 无 同步
			foreach($ids as $id){
				$_data = array();
				$_data['cont_type'] = $cont_type;
				$_data['cont_id'] = $contid ? $contid : 0;
				$_data['resid'] = $id;
				$_data['cont_level'] = $cont_level;
				$_data['cont_quality'] = $cont_quality;
				$_data['modify_tm'] = time();
				$_data['cont_from'] = $from;
				$_data['status'] = 1;
				$_data['bgcard_id'] = $bgcard_id;
				$rest = $this -> add($_data);
			}
		}else{
			$_data = array();
			$_data['cont_type'] = $cont_type;
			$_data['cont_id'] = $contid;
			$_data['resid'] = $resid;
			$_data['cont_level'] = $cont_level;
			$_data['cont_quality'] = $cont_quality;
			$_data['modify_tm'] = time();
			$_data['cont_from'] = $from;
			$_data['status'] = 1;
			$_data['bgcard_id'] = $bgcard_id;
			$rest = $this -> add($_data);
		}
		
		return $rest;
	}
	
	public function saveStatus($resid,$status,$from){
		if(is_array($resid)){
			$id_arr = $resid;
		}else{
			$id_arr = explode("','",$resid);
		}
		$_data['status'] = $status;
		foreach ($id_arr as $value) {
			$rest = $this -> where(array('resid'=>$value,'cont_from'=>$from))->save($_data);
		}
	}
}