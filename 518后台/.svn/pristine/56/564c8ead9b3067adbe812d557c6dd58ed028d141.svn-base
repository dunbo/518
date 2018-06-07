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
	//评论大家说 同一评论只能添加一个标签，同一标签排序不能相同
	public function check_repeat_special($table,$field,$val,$field1,$val1,$id)
	{
		$where_have =array(
			'status' =>1,
			$field =>$val,
			$field1 =>$val1,
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
	public function get_label_list()
	{
		$where=array(
			'status' =>1,
		);
		$result = $this->table('sj_dev_label')->where($where)->select();
		$label_list = array();
		foreach($result as $key => $val)
		{
			$label_list[$val['id']] = $val['label_name'];
		}
		return $label_list;
	}
	public function utf8_str_split($str, $split_len = 1)  
	{  
		if (!preg_match('/^[0-9]+$/', $split_len) || $split_len < 1)  
			return FALSE;  
	   
		$len = mb_strlen($str, 'UTF-8');  
		if ($len <= $split_len)  
			return array($str);  
	   
		preg_match_all('/.{'.$split_len.'}|[^x00]{1,'.$split_len.'}$/us', $str, $ar);  
	   
		return $ar[0];  
	}  
}
?>