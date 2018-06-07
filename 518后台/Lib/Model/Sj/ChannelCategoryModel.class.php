<?php
class ChannelCategoryModel extends Model {
	//调整表前缀
     protected $trueTableName = 'sj_channel_category';
	 
	 public function getCategory()
	 {
		$list = $this->select();
		$result = array(
			'0' => array(
				'name' => '未分类'
			)
		);
		
		foreach($list as $item){
		    if ($item['status'] == 0) continue;
			$result[$item['category_id']] = array(
				'name' => $item['name'],
				'category_id' => $item['category_id']
				
			);
		}
		return $result;
	 }

	 public function getteam()
	 {
	 	$list = $this->table('sj_channel_option')->where('type=3')->select();
		$result = array(
			'0' => array(
			'name' => '---'
			)
		);
		foreach($list as $item){
			$result[$item['name']] = array(
				'id' => $item['id'],
				'name' =>$item['name']
				
			);
		}
		return $result;
	 }

	 public function getdepartment()
	 {
	 	$list = $this->table('sj_channel_option')->where('type=2')->select();
		$result = array(
			'0' => array(
			'name' => '---'
			)
		);
		foreach($list as $item){
			$result[$item['name']] = array(
				'id' => $item['id'],
				'name' =>$item['name']
				
			);
		}
		return $result;
	 }

	 public function getfuzeren()
	 {
	 	$list = $this->table('sj_channel_option')->where('type=4')->select();
		$result = array(
			'0' => array(
			'name' => '---'
			)
		);
		foreach($list as $item){
			$result[$item['name']] = array(
				'id' => $item['id'],
				'name' =>$item['name']
				
			);
		}
		return $result;
	 }

}
?>