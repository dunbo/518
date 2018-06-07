<?php
class PageCodeAction extends CommonAction {

    function pub_index() {
		$map_all = ContentTypeModel::export_all_pages_operation();
		//修改$map_all的样式
		foreach($map_all as $ke =>$va)
		{
			if(is_array($va))
			{
				$map_all[$ke] = $va[0];
			}
		}
		 // 返回检查结果的错误信息，如果记录的flag为1表示有错误
		$error_msg = $this->pub_add_db($map_all);
		if ($error_msg) {
			$this->error($error_msg);
		}
    }
	function pub_add_db($map_all)
	{
		$model = M();
		$add_new_content=array();
		$chunks = array_chunk($map_all,500,true);
		foreach($chunks as $chunk_val)
		{ 
			$k_array=array();
			foreach($chunk_val as $k_val=>$k_val)
			{
				$k_array[]=$k_val;
			}
			
			$chunks_where=array(
				'list_str'=>array('in',$k_array),
			);
			
			$is_have_result = $model->table('sj_ad_info')->where($chunks_where)->select();
			
			if($is_have_result)
			{
				foreach($is_have_result as $have_array)
				{
					//去掉有的
					unset($chunk_val[$have_array['list_str']]);
				}
			}
			$add_new_content = array_merge($add_new_content,$chunk_val);
		}
		//插入到数据表中
		if($add_new_content)
		{
			$error_msg = "";
			foreach($add_new_content as $key=>$val)
			{
				$page_map=array();
				//哈希值不能重复
				$key_str = md5($key);
				$key_str = substr($key_str, 0, 4). substr($key_str, -3);
				$key_str_where=array(
					'list_hash'=>$key_str,
				);
				$key_str_result = $model->table('sj_ad_info')->where($key_str_where)->find();
				if($key_str_result)
				{
					$msg = $key."与".$key_str_result['list_str']."有重复的哈希值".$key_str;
					$error_msg .= $msg.",";
				}
				else
				{
					$page_map['list_str'] = $key;
					$page_map['list_name'] = $val;
					$page_map['list_hash'] = $key_str;
					$table_config = $this->pub_ad_table($key);
					$page_map['ad_table'] = $table_config['ad_table'];
					$page_map['ad_key'] = $table_config['ad_key'];
					
					$add_new = $model->table('sj_ad_info')->add($page_map);
				}
			}
		}
		return $error_msg;
	}

	function pub_ad_table($key)
	{
		$config = array(
			'otherfixed_homepage_recommend' => array(
				'ad_table'=>'sj_extent_soft_v1',
				'ad_key' =>'id',
			),
			'otherfixed_homepage_necessary' => array(
				'ad_table'=>'sj_necessary_extent_soft',
				'ad_key' =>'id',
			),
			'feature_\d+' => array(
				'ad_table'=>'sj_feature_soft',
				'ad_key' =>'id',
			),
			'customlist_\d+' => array(
				'ad_table'=>'sj_custom_list_name_soft',
				'ad_key' =>'id',
			),
		);

		$map = array(
			'ad_table'=>'sj_category_extent_soft',
			'ad_key' =>'id',
		);

		if(isset($config[$key])) {
			$map = $config[$key];
		} else {
			foreach ($config as $p => $v) {
				if (preg_match('/^'.$p.'$/', $key)) {
					$map = $config[$p];	
					break;
				}
			}
		}
		return $map;
	}
}