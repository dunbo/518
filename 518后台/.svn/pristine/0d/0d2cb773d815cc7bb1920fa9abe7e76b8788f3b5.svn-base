<?php
/**
 * 软件官方审核
 */
class Search_weightModel extends Model {

	protected $trueTableName = 'sj_soft_official_result';

	public function ebook_category_list() {
		$ebook_parent_category = $this->table('sj_category')->where('parentid=3')->find();	
		$category = $this->field("category_id, name")
			->table('sj_category')
			->where('parentid='.$ebook_parent_category['category_id'])
			->findAll();
		return $category;
	}

	public function getCategoryByCid($cid) {
		$category = $this->table('sj_category')->where('category_id='.$cid)->find();	
		return $category;
	}

	public function getPackage($where) {
		$doc = $this->table('sj_search_key_package')->where($where)->find();	
		return $doc;
	}

	public function delValidPackage($where) {
		$this->table('sj_search_key_package')->where($where)	
			->data(array('status' => 0))->save();
	}
	public function getSrh_key($where)
	{
		$doc = $this->table('sj_search_key')->where($where)->select();	
		return $doc;
	}
	public function add_srh($srh_key)
	{
		$sk_db = M("search_key");
        // 关键字统一转小写
        $srh_key = strtolower($srh_key);
		$data['srh_key'] = $srh_key;
		if(mb_strlen($data['srh_key'],'UTF-8')>50){
			$this -> error('添加的关键字不要超过50个字');
		}
		$data['create_tm'] = time();
		$data['update_tm'] = $data['create_tm'];
		$data['status'] = 1;
		$data['pid'] = 1;
		
		$affect = $this->table('sj_search_key')->add($data);
		return $affect;
	}
}
?>
