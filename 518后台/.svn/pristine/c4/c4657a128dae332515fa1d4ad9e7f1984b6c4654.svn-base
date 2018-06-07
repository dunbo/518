<?php
class CollectionModel extends Model {
     protected $trueTableName = 'caiji_config';
	function get_reason(){
		$reason_list = $this -> table("dev_reason") -> where(array("status" => 1,"reason_type" => 12 ))->order('pos asc,id desc')->select();
		foreach($reason_list as &$val){
			if($val['content2']){
				$val['content2'] = explode('<br />', $val['content2']);
			}
		}
		return 	$reason_list;
	} 
}
?>
