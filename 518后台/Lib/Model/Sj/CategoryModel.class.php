<?php
class CategoryModel extends Model {
	//调整表前缀
	protected $trueTableName = 'sj_category';

	public function getCategory($array_config)
	{
		$conf_list=$this->where("status=1 and parentid=0")->field('category_id,name,orderid')->order('orderid')->select();
		foreach($conf_list as $m=>$n){
			$lists=$this->where('status=1 and parentid='.$n['category_id'])->order('orderid')->select();
			$conf_list[$m][$n['category_id']]=$lists;
			foreach($lists as $key=>$val){
				$lists[$key]['category_id']=$val['category_id'];
				$list_three=$this->where('status=1 and parentid='.$lists[$key]['category_id'])->order('orderid')->select();
				$conf_list[$m][$n['category_id']][$val['category_id']]=$list_three;
			} 
		}
		$category='<select name='.$array_config['categoryid'].' id='.$array_config['categoryid'].'>
			<option value="0" selected>无</option>';
		//print_r($conf_list);
		if($array_config['key']==1){
			foreach($conf_list as $m=>$n){

				//$category.='<optgroup label='.$n["name"].'>'; 
				$category.='<option value='.$n["category_id"];
				if($n["category_id"]==$array_config['selected']){
					$category.=' selected';
				}else{
					$category.='';
				}
				$category.='>——'.$n["name"].'——</option>'; 
				foreach($n[$n['category_id']] as $k=>$val){
					if($val["name"]){

						//$category.='<optgroup label=——'.$val["name"].'——>';
						$category.='<option value='.$val["category_id"];
						if($val["category_id"]==$array_config['selected']){
							$category.=' selected';
						}else{
							$category.='';
						}
						$category.='>————'.$val["name"].'————</option>';
					}
					foreach($n[$n['category_id']][$val['category_id']] as $key=>$info){
						//$category.='<optgroup label='.$info["name"].'>';
						$category.='<option value='.$info["category_id"];
						if($info["category_id"]==$array_config['selected']){
							$category.=' selected';
						}else{
							$category.='';
						}
						$category.='>'.$info["name"].'</option>'; 

					}
				}
			}
		}else{
			foreach($conf_list as $m=>$n){

				$category.='<optgroup label='.$n["name"].'>'; 
				foreach($n[$n['category_id']] as $k=>$val){
					if($val["name"]){

						$category.='<optgroup label=——'.$val["name"].'——>';
					}
					foreach($n[$n['category_id']][$val['category_id']] as $key=>$info){
						//$category.='<optgroup label='.$info["name"].'>';
						$category.='<option value='.$info["category_id"];
						if($info["category_id"]==$array_config['selected']){
							$category.=' selected';
						}else{
							$category.='';
						}
						$category.='>'.$info["name"].'</option>'; 

					}
				}
			}
		}
		$category .='</select>';
		return $category;
	}
	
	public function getCategoryArray()
	{
		static $types=array();
		if(count($types)){
			return $types;
		}
		$conf_list = $this->where("status=1")->field('category_id,name,orderid,parentid,tag_ids')->order('orderid')->select();
		$types = array();
		foreach($conf_list as $val){
			$category_id = $val['category_id'];
			$types[$category_id] = $val;
		}
		foreach($types as $key => $val){
			$parentid = $types[$key]['parentid'];
			
			if ($parentid == 1 || $parentid==2 || $parentid==3 || $parentid==0) continue;
			
			if (isset($types[$parentid])) {
				$types[$key]['subid'] = $parentid;
				$types[$key]['parentid'] = $types[$parentid]['parentid'];
				
				$tmp[$parentid] = $parentid;
			}
		}
		foreach($tmp as $k) {
			unset($types[$k]);
		}
		return $types;
	}
    public function getBdArray()
	{
		//表 sj_list
		$where =array(
			'status' =>1,
		);
		$conf_list = $this->table('sj_list')->where($where)->field('id,name,order_num,from_chl')->order('order_num')->select();
		return $conf_list;
	}
	
    // 所有有效的三级分类列表
    public function getThirdLevelCatgoryList() {
    	static $third_level_category = array();
		if(count($third_level_category)){
			return $third_level_category;
		}
		$category_alldata=$this->getCategoryArray();
		
	    $second_level_category_ids = array();
	    $third_level_category_new = array();
	    foreach ($category_alldata as $first) {
	    	if($first['subid']){
	        	$third_level_category_new[$first['subid']][]=$first;
	    	}
	        
	    }
	    ksort($third_level_category_new);
	    
	    
	    foreach ($third_level_category_new as $k=>$first) {
	    	$category_id=array();
	    	foreach($first as $kk=>$v){
	    		$category_id[]=$v['category_id'];
	    		$first[$kk]['parentid']=$v['subid'];
	    	}
	    	array_multisort($category_id, SORT_ASC, $first);
	    	$third_level_category=array_merge($third_level_category,$first);
	        
	    }
	    return $third_level_category;

        
    }
}
?>
