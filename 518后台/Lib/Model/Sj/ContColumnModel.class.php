<?php
class ContColumnModel extends AdvModel {
    //调整表前缀
    protected $trueTableName = 'cont_column_advertise';
	 
	public function getall_cont_column(){
		$column_list = array();
		$all_column = $this->where(array('status'=>1))->select();
		foreach ($all_column as $value) {
			$column_list[$value['cont_id']] = $value;
		}
		return $column_list;
	}
}