function edit_load(data)
{
	// 推荐内容值
	var content_arr = Array();
	content_arr['content_type'] = data['content_type'];
	content_arr['parameter_field'] = data['parameter_field'];
	if (content_arr['content_type'] == 1) {
		content_arr['package'] =  data['package'];
		content_arr['uninstall_setting'] = data['uninstall_setting'];
		content_arr['install_setting'] = data['install_setting'];
		if (content_arr['install_setting'] == 4) {
			content_arr['start_to_page'] = data['start_to_page'];
		}
		content_arr['lowversion_setting'] = data['lowversion_setting'];
	} else if (content_arr['content_type'] == 2) {
		content_arr['activity_id'] = data['activity_id'];
	} else if (content_arr['content_type'] == 3) {
		content_arr['feature_id'] = data['feature_id'];
	} else if (content_arr['content_type'] == 4) {
		content_arr['page_type'] = data['page_type'];
		//v6.0编辑的时候显示运营标识内容
		content_arr['operate_mark'] = data['opera_mark_num'];
		content_arr['custom_mark'] = data['opera_mark_name'];
	} else if (content_arr['content_type'] == 5) {
		content_arr['website'] = data['website'];
		content_arr['website_open_type'] = data['website_open_type'];
		content_arr['operate_mark'] = data['opera_mark_num'];
		content_arr['custom_mark'] = data['opera_mark_name'];
	}else if (content_arr['content_type'] == 6) {
		content_arr['gift_id'] = data['gift_id'];
		content_arr['page_type'] = data['page_type'];
	}else if (content_arr['content_type'] == 7) {
		content_arr['strategy_id'] = data['strategy_id'];
		content_arr['page_type'] = data['page_type'];
	}else if (content_arr['content_type'] == 8) {
		content_arr['recommend_order_id'] = data['activity_id'];
	}else if(content_arr['content_type'] == 10) {
		content_arr['recommend_order_id'] = data['activity_id'];
	}
	return content_arr;
}