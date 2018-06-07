<?php
/**
 * 公用脚本
 * 2017/11/16
 */
class PubScriptAction extends CommonAction {
	
	public function pub_index()
	{
		$model = M('');
		$table_arr = array();
		$tab_sql = "select table_name from information_schema.tables where table_schema='newgomarket' and table_type='base table'";
		$tab_arr = $model->query($tab_sql);
		foreach ($tab_arr as $val ) {
			$culm_sql = "SELECT count(*) as count FROM information_schema. COLUMNS WHERE TABLE_SCHEMA = 'newgomarket' AND TABLE_NAME = '{$val['table_name']}' AND COLUMN_NAME = 'admin_id'";
			$count = $model->query($culm_sql);
			if( $count[0]['count'] ) {
				//echo $val['table_name'].'<br/>';
				$table_arr[] = $val['table_name'];
			}
		}
		echo count($table_arr);
		echo '<br/>';
		echo json_encode($table_arr);
	}
	
	public function pub_contrast()
	{
		$model = M('');
		$tab_json = '["screenshot_log","sj_ad","sj_ad_zone","sj_admin_log","sj_admin_log_new","sj_admin_login_logs","sj_admin_role","sj_admin_role_copy_copy","sj_admin_statistics_role","sj_adopters","sj_adopters_activity","sj_adopters_chinesization","sj_adopters_exclusive","sj_adopters_feature","sj_adopters_gift","sj_animation_ad","sj_audit_adminuser","sj_audit_day_log","sj_category_extent","sj_category_extent_soft","sj_channel_coefficient","sj_coopersoft_search","sj_custom_list_channel","sj_custom_list_name","sj_custom_list_name_soft","sj_desk_icon","sj_dev_everybody_said","sj_dev_label","sj_developer_mail_notice","sj_download_recommend","sj_download_recommend_soft","sj_email_config","sj_extent_candidate_v1","sj_extent_soft","sj_extent_soft_v1","sj_extent_v1","sj_feature","sj_feature_soft","sj_feedback","sj_feedback_filter","sj_flexible_extent","sj_flexible_extent_soft","sj_keyword_shield","sj_lading_bei_soft","sj_lading_category","sj_lading_soft","sj_necessary_extent","sj_necessary_extent_soft","sj_perfect_soft","sj_processed_daily","sj_reason","sj_return_back_ad","sj_sameauthor_soft","sj_search_key","sj_search_key_package","sj_search_keywords","sj_search_related","sj_search_related_content","sj_search_tips","sj_search_tips_package","sj_search_tips_policy","sj_select_label","sj_service_statement","sj_soft_associate_hot_word","sj_soft_defaultkeywords","sj_soft_downloads_hidden_config","sj_soft_hotwords","sj_soft_keywords","sj_splash_manage","sj_staff_config","sj_text_page","sj_textquickentry_extent","sj_textquickentry_extent_soft","sj_think_words","sj_top_ready_soft","sj_upload_jobs"]';
		$tab_name = json_decode($tab_json, true);
		//print_r($tab_name);die;
		$str = $str2 = '';
		foreach ($tab_name as $val ) {
			$is_tab_sql = "SELECT count(*) as count FROM information_schema.TABLES WHERE table_name ='{$val}'";
			$is_tab_arr = $model->query($is_tab_sql);
			if( $is_tab_arr[0]['count'] ) {
				$culm_sql = "SELECT count(*) as count FROM information_schema. COLUMNS WHERE TABLE_SCHEMA = 'newgomarket' AND TABLE_NAME = '{$val}' AND COLUMN_NAME = 'admin_id'";
				$count = $model->query($culm_sql);
				if( !$count[0]['count'] ) {
					//echo $val."<br/>";
					$str .= $val."<br/>";
					$str2 .= "ALTER TABLE `{$val}` ADD COLUMN `admin_id`  int(11) NULL DEFAULT 0 COMMENT '操作人id' "."<br/>";
				}
			}
		}
		
		echo $str;
		echo $str2;
	}
	
	public function pub_import_flexible_soft(){
		ini_set('memory_limit', '1024M');
		$model = M(''); 
		//$desc_1 = $model->query("select COLUMN_NAME from information_schema.columns where table_name='sj_common_jump' ");
		//$desc_2 = $model->query("select COLUMN_NAME from information_schema.columns where table_name='sj_flexible_extent_soft' ");
		$common_jump_list = $model->table('sj_common_jump')->where(array('status'=>1,'f_s_id'=>0))->order('id asc')->select();
		foreach ($common_jump_list as $key => $val) {
			if($val['resource_type'] == 1) {
				$resource_type = 24;
				$extent_id = 1260;
			}elseif($val['resource_type'] == 2){
				$resource_type = 26;
				$extent_id = 1259;
			}elseif($val['resource_type'] == 3){
				$resource_type = 29;
				$extent_id = 1258;
			}else {
				continue;
			}
			$map = array(
				'extent_id'			=>	$extent_id,
				'content_type'		=>	$val['content_type'],
				'package'			=>	$val['package'],
				'activity_id'		=>	$val['activity_id'],
				'feature_id'		=>	$val['feature_id'],
				'page_type'			=>	$val['page_type'],
				'page_flag'			=>	$val['page_flag'],
				'page_id1'			=>	$val['page_id1'],
				'page_id2'			=>	$val['page_id2'],
				'website'			=>	$val['website'],
				'website_open_type'	=>	$val['website_open_type'],
				'gift_id'			=>	$val['gift_id'],
				'strategy_id'		=>	$val['strategy_id'],
				'uninstall_setting'	=>	$val['uninstall_setting'],
				'install_setting'	=>	$val['install_setting'],
				'lowversion_setting'=>	$val['lowversion_setting'],
				'start_to_page'		=>	$val['start_to_page'],
				'create_at'			=>	$val['create_at'],
				'update_at'			=>	$val['update_at'],
				'status'			=>	$val['status'],
				//'is_pop_random_packs'	=>	$val['is_pop_random_packs'],
				'parameter_field'	=>	$val['parameter_field'],
				'package_643'		=>	$val['package_643'],
				'image_url'			=>	$val['image_url'],
				'is_dev'			=>	$val['is_dev'],
				'package_name'		=>	$val['package_name'],
				'title'				=>	$val['title'],
				'resource_type'		=>	$resource_type,
				'high_image_url'	=>	$val['high_image_url'],
				'low_image_url'		=>	$val['low_image_url'],
				'video_url'			=>	$val['video_url'],
				'video_pic'			=>	$val['video_pic'],
			);
			$id = $model->table('sj_flexible_extent_soft')->add($map);
			if ($id) {
				$model->table('sj_common_jump')->where(array('id'=>$val['id'])) ->save(array('f_s_id'=>$id));
			}else {
				echo '导入失败的数据id：'.$val['id'].'<br/>';
			}
		}
		echo '完成';
	}
	
	public function pub_update_res_id(){
		ini_set('memory_limit', '1024M');
		$model = M('');
		$sql = "SELECT id,resource_id,res_id FROM sj_flexible_extent_soft where extent_id != 0 and `status` =1 and resource_id !=0";
		$soft_list = $model->query($sql);
		foreach ($soft_list as $key => $val) {
			$jump_info = $model->table('sj_common_jump')->where(array('id'=>$val['resource_id']))->find();
			if(!empty($jump_info['f_s_id'])) {
				$model->table('sj_flexible_extent_soft')->where(array('id'=>$val['id']))->save(array('res_id'=>$jump_info['f_s_id']));
			}
		}
		echo '完成';
	}
	
	//http://518test.anzhi.com/index.php/PubScript/pub_card_import
	public function pub_card_import(){
		ini_set('memory_limit', '1024M');
		$model = M('');
		$sql = "SELECT * FROM sj_flexible_extent where belong_page_type='otherfixed_homepage_recommend' and extent_type=28 and `status` = 1 ORDER BY extent_id asc";
		$extent_list = $model->query($sql);
		foreach ($extent_list as $key => $val){
			//区间下的软件
			$sql_soft = "SELECT * FROM sj_flexible_extent_soft where extent_id = {$val['extent_id']} and `status`=1";
			$soft_list = $model->query($sql_soft);
			$val['belong_page_type']=	'fixed_resource_channel';
			unset($val['extent_id']);
			$id = $model->table('sj_flexible_extent')->add($val);
			if($id) {
				foreach ($soft_list as $kk => $vv) {
					$vv['extent_id']		=	$id;
					$vv['resource_type']	=	28;
					unset($vv['id']);
					$model->table('sj_flexible_extent_soft')->add($vv);
				}
			}else {
				echo "失败key:{$key}<br/>";
			}
		}
		echo '非常成功';
	}
	
	//http://518test.anzhi.com/index.php/PubScript/pub_extent_import
	public function pub_extent_import(){
		$sql = "SELECT extent_id FROM sj_extent_v2 where `status` = 1 UNION SELECT extent_id from sj_flexible_extent where  belong_page_type = 'fixed_homepage_recommend' and `status` = 1";
		$model = M('');
		$list = $model->query($sql);
		
		if(!empty($list)) {
			exit('导入前先，请先清除数据');
		}
		
		for($i=1; $i<=500; $i++) {
			$mark = substr($i,-1);
			$map = array();
			if($mark == 1) {
				$map['extent_name'] = "第{$i}位-商业化";
				$map['extent_type'] = 5;
				$map['is_resource'] = 0;
			}elseif($mark == 2) {
				$map['extent_name'] = "第{$i}位";
				$map['extent_type'] = 24;
				$map['is_resource'] = 1;
			}elseif($mark == 3) {
				$map['extent_name'] = "第{$i}位";
				$map['extent_type'] = 29;
				$map['is_resource'] = 1;
			}elseif($mark == 4) {
				$map['extent_name'] = "第{$i}位-商业化";
				$map['show_form'] = 2;
			}elseif($mark == 5) {
				$map['extent_name'] = "第{$i}位";
				$map['extent_type'] = 24;
				$map['is_resource'] = 1;
			}elseif($mark == 6) {	
				$map['extent_name'] = "第{$i}位";
				$map['extent_type'] = 2;
				$map['is_resource'] = 1;
			}elseif($mark == 7) {
				$map['extent_name'] = "第{$i}位-商业化";
				$map['show_form'] = 2;
			}elseif($mark == 8) {
				$map['extent_name'] = "第{$i}位";
				$map['extent_type'] = 24;
				$map['is_resource'] = 1;
			}elseif($mark == 9) {
				$map['extent_name'] = "第{$i}位";
				$map['extent_type'] = 26;
				$map['is_resource'] = 1;
			}elseif($mark == 0){
				$map['extent_name'] = "第{$i}位";
				$map['extent_type'] = 28;
				$map['is_resource'] = 1;
			}
			
			$map['rank']		=	$i;
			$map['extent_size']	=	1;
			$map['create_at']	=	time();
			$map['update_at']	=	time();
			$map['status']		=	1;
			$map['admin_id']	=	$_SESSION['admin']['admin_id'];
			
			if($mark != 4 && $mark != 7) {
				$map['release_time'] = time();
				$map['belong_page_type'] = 'fixed_homepage_recommend';
				$table_name = "sj_flexible_extent";
			}else {
				$table_name = "sj_extent_v2";
			}
			$model->table($table_name)->add($map);
			unset($map);
		}
		
		echo '非常成功';
	}
	
	
	
	
	
	
	
	
	
	
	
}
?>
