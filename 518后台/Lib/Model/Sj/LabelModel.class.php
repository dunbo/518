<?php

class LabelModel extends Model {	
	public function check_repeat($table,$field,$val,$id)
	{
		$where_have =array(
			'status' =>1,
			$field =>$val,
		);
		if($id)
		{
			$where_have['_string'] .="id !={$id}";
		}
		$have_result = $this->table($table)->where($where_have)->select();
		if($have_result)
		{
			return 1;
		}
	} 
}
?>