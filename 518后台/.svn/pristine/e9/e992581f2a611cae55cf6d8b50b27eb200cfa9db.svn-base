/* 
** 灵活运营里编辑推荐内容时用来生成推荐内容相关数据字符串及hidden的input
** 生成的内容最好和public里的promotion一致
*/

var content_type_arr = ['', '软件', '活动', '专题', '页面', '网页','礼包','攻略','预约','应用内览','游戏预约'];//v6.4增加预约v6.4。4增加应用内览,游戏预约
var general_page_type_arr = ['', '普通', '标签', '常用标签', '自定义列表', '','','','','','自定义频道'];
var setting_arr = ['', '进软件详情不下载', '进软件详情自动下载', '启动软件', '启动软件至某个页面', '前端不显示（安智5.3及以前版本正常显示，跳转软件详情不下载）','不进软件详情-自动下载','不进详情自动下载'];
var website_open_type_arr = ['', '内置', '外置'];
var website_mobile_config_arr = ['不区分','仅高配','仅低配'];
var website_is_sync_accout_arr = ['是','否'];
var website_is_actionbar_arr = {1:'是',0:'否'};
var website_screen_show_arr = {4:'横屏',0:'竖屏'};
var website_is_h5_arr = {2:'是',0:'否'};
var operate_mark_arr = ['','推广','推荐','活动','专题','精选','汉化','破解','自定义'];//运营标识  自定义是30

function generate_append_html(append_div_id, content_arr) {
	//content_type_div_header_scroll_section_1_1
    var content_type = content_arr['content_type'];
	//一个页面多个推荐内容 hidden的name的值
	if(append_div_id!="content_type_div"&&append_div_id.indexOf('content_type_div')==0)	
	{
		var div_param = append_div_id.substring(17);//获取传来的参数
	}
	if(div_param)
	{
		var content_type_element_name = getElementName(content_arr, "content_type["+div_param+"]");
	}
	else
	{
		var content_type_element_name = getElementName(content_arr, "content_type");
	}	
    var content_type_hidden_input_text = assemble_hidden_input_element(content_type_element_name, content_type);
    
    var all_hidden_input = '';
    if (content_type == 1) {
        var package = content_arr['package'];
        var uninstall_setting = content_arr['uninstall_setting'];
        var install_setting = content_arr['install_setting'];
        var lowversion_setting = content_arr['lowversion_setting'];
        var start_to_page = content_arr['start_to_page'];
		var function_from = content_arr['function_from'];
		if(content_arr['parameter_field']&&content_arr['parameter_field']!='null')
		{
			var param_arr=eval('('+content_arr['parameter_field']+')'); 
			if(param_arr['recommend_behavior_id'])
			{
				var recommend_behavior_id = param_arr['recommend_behavior_id'];
				if(div_param)
				{
					var recommend_behavior_id_element_name = getElementName(content_arr, "recommend_behavior_id["+div_param+"]");
				}
				else
				{
					var recommend_behavior_id_element_name = getElementName(content_arr, "recommend_behavior_id");
				}
				all_hidden_input += assemble_hidden_input_element(recommend_behavior_id_element_name, recommend_behavior_id);
			}
		}
		else
		{
			var recommend_behavior_id = '';
		}
        
		if(div_param)
		{
			var package_element_name = getElementName(content_arr, "package["+div_param+"]");
			var uninstall_setting_element_name = getElementName(content_arr, "uninstall_setting["+div_param+"]");
			var install_setting_element_name = getElementName(content_arr, "install_setting["+div_param+"]");
			var lowversion_setting_element_name = getElementName(content_arr, "lowversion_setting["+div_param+"]");
		}
		else
		{
			var package_element_name = getElementName(content_arr, "package");
			var uninstall_setting_element_name = getElementName(content_arr, "uninstall_setting");
			var install_setting_element_name = getElementName(content_arr, "install_setting");
			var lowversion_setting_element_name = getElementName(content_arr, "lowversion_setting");
		}
		
        all_hidden_input += assemble_hidden_input_element(package_element_name, package);
        all_hidden_input += assemble_hidden_input_element(uninstall_setting_element_name, uninstall_setting);
        all_hidden_input += assemble_hidden_input_element(install_setting_element_name, install_setting);
        all_hidden_input += assemble_hidden_input_element(lowversion_setting_element_name, lowversion_setting);
        
        if (install_setting == 4) {
			if(div_param)
			{
				var start_to_page_element_name = getElementName(content_arr, "start_to_page["+div_param+"]");
			}
			else
			{
				var start_to_page_element_name = getElementName(content_arr, "start_to_page");
			}
				
            all_hidden_input += assemble_hidden_input_element(start_to_page_element_name, start_to_page);
        }
        
    } else if (content_type == 2) {
        var activity_id = content_arr['activity_id'];
		if(div_param)
		{
			var activity_id_element_name = getElementName(content_arr, "activity_id["+div_param+"]");
		}
		else
		{
			var activity_id_element_name = getElementName(content_arr, "activity_id");
		}
        all_hidden_input += assemble_hidden_input_element(activity_id_element_name, activity_id);
    } else if (content_type == 3) {
        var feature_id = content_arr['feature_id'];
		if(div_param)
		{
			var feature_id_element_name = getElementName(content_arr, "feature_id["+div_param+"]");
		}
		else
		{
			var feature_id_element_name = getElementName(content_arr, "feature_id");
		}
        all_hidden_input += assemble_hidden_input_element(feature_id_element_name, feature_id);
    } else if (content_type == 4) {
        var page_type = content_arr['page_type'];
		if(div_param)
		{
			var page_type_element_name = getElementName(content_arr, "page_type["+div_param+"]");
		}
		else
		{
			var page_type_element_name = getElementName(content_arr, "page_type");
		}
        all_hidden_input += assemble_hidden_input_element(page_type_element_name, page_type);
		//如果content_arr['operate_mark']有值说明是灵活运营的运营标识
		if(content_arr['operate_mark'])
		{
			var operate_mark = content_arr['operate_mark'];
			var custom_mark = content_arr['custom_mark'];
			if(div_param)
			{
				var operate_mark1 = getElementName(content_arr, "operate_mark["+div_param+"]");
				var custom_operate_mark1 = getElementName(content_arr, "custom_mark["+div_param+"]");
			}
			else
			{
				var operate_mark1 = getElementName(content_arr, "operate_mark");
				var custom_operate_mark1 = getElementName(content_arr, "custom_mark");
			}
			all_hidden_input += assemble_hidden_input_element(operate_mark1, operate_mark);
			all_hidden_input += assemble_hidden_input_element(custom_operate_mark1, custom_mark);
		}
		//如果个人中心 会有是否弹出随机礼包选项
		if(page_type=='fixed_personal_center')
		{
			if(content_arr['parameter_field']&&content_arr['parameter_field']!='null')
			{
				var param_arr=eval('('+content_arr['parameter_field']+')'); 
				if(param_arr['is_pop_random_packs'])
				{
					var is_pop_random_packs_val = param_arr['is_pop_random_packs'];
					if(div_param)
					{
						var is_pop_random_packs = getElementName(content_arr, "is_pop_random_packs["+div_param+"]");
					}
					else
					{
						var is_pop_random_packs = getElementName(content_arr, "is_pop_random_packs");
					}
					all_hidden_input += assemble_hidden_input_element(is_pop_random_packs, is_pop_random_packs_val);
				}
			}
		}
		//如果活动列表 文字快捷入口 活动类型选项
		if(page_type=='otherfixed_activity_list')
		{
			if(content_arr['parameter_field']&&content_arr['parameter_field']!='null')
			{
				var param_arr=eval('('+content_arr['parameter_field']+')'); 
				//if(param_arr['activity_category_show'])
				//{
				var activity_category_val = param_arr['activity_category_show'];
				var tab_index_val = param_arr['tab_index'];
				if(div_param)
				{
					var activity_category_show = getElementName(content_arr, "activity_category_show["+div_param+"]");
					var tab_index = getElementName(content_arr, "tab_index["+div_param+"]");
				}
				else
				{
					var activity_category_show = getElementName(content_arr, "activity_category_show");
					var tab_index = getElementName(content_arr, "tab_index");
				}
				all_hidden_input += assemble_hidden_input_element(activity_category_show, activity_category_val);
				all_hidden_input += assemble_hidden_input_element(tab_index, tab_index_val);

				//}
			}
		}
		//6.5 红包活动列表
		if(page_type=='fixed_red_packet_task_list')
		{
			if(content_arr['parameter_field']&&content_arr['parameter_field']!='null')
			{
				var param_arr=eval('('+content_arr['parameter_field']+')'); 
					var red_task_category_val = param_arr['red_task_category_show'];
					if(div_param)
					{
						var red_task_category_show = getElementName(content_arr, "red_task_category_show["+div_param+"]");
					}
					else
					{
						var red_task_category_show = getElementName(content_arr, "red_task_category_show");
					}
					all_hidden_input += assemble_hidden_input_element(red_task_category_show, red_task_category_val);
			}
		}
		//如果每日抽奖兑吧
		if(page_type=='otherfixed_daily_lottery_exchange')
		{
			if(content_arr['parameter_field']&&content_arr['parameter_field']!='null')
			{
				var param_arr=eval('('+content_arr['parameter_field']+')'); 
				if(param_arr['exchange_url'])
				{
					var exchange_url_val = param_arr['exchange_url'];
					if(div_param)
					{
						var exchange_url_show = getElementName(content_arr, "exchange_url["+div_param+"]");
					}
					else
					{
						var exchange_url_show = getElementName(content_arr, "exchange_url");
					}
					all_hidden_input += assemble_hidden_input_element(exchange_url_show, exchange_url_val);
				}
			}
		}
		//内容合作 展示
		if(page_type.indexOf('fixed_content_coop_')==0)
		{
			var str_arr = new Array();
			str_arr = page_type.split('_');
			if(content_arr['parameter_field']&&content_arr['parameter_field']!='null')
			{
				var param_arr = eval('('+content_arr['parameter_field']+')');
				var coop_pos_val = param_arr['coop_site_id'];
				if(div_param)
				{
					var coop_site_id_show = getElementName(content_arr, "coop_site_id["+div_param+"]");
				}
				else
				{
					var coop_site_id_show = getElementName(content_arr, "coop_site_id");
				}
				all_hidden_input += assemble_hidden_input_element(coop_site_id_show, coop_pos_val);
				if(str_arr[3]==3)
				{
					if(param_arr['coop_detail_url'])//说明是按照标题
					{
						var coop_detail_url3_val = param_arr['coop_detail_url'];
						var coop_detail_url_id_val = param_arr['coop_detail_url_id'];
						var coop_content_id_new_val = param_arr['coop_content_id_new'];
						if(div_param)
						{
							var coop_detail_url3 = getElementName(content_arr, "coop_detail_url["+div_param+"]");
							var coop_detail_url_id = getElementName(content_arr, "coop_detail_url_id["+div_param+"]");
							var coop_content_id_new = getElementName(content_arr, "coop_content_id_new["+div_param+"]");
						}
						else
						{
							var coop_detail_url3 = getElementName(content_arr, "coop_detail_url");
							var coop_detail_url_id = getElementName(content_arr, "coop_detail_url_id");
							var coop_content_id_new = getElementName(content_arr, "coop_content_id_new");
						}
						all_hidden_input += assemble_hidden_input_element(coop_detail_url3, coop_detail_url3_val);
						all_hidden_input += assemble_hidden_input_element(coop_detail_url_id, coop_detail_url_id_val);
						all_hidden_input += assemble_hidden_input_element(coop_content_id_new, coop_content_id_new_val);
					}
					if(param_arr['coop_detail_label_id']&&param_arr['coop_detail_pos'])
					{
						var coop_detail_label_id_val = param_arr['coop_detail_label_id'];
						var coop_detail_pos_val = param_arr['coop_detail_pos'];
						if(div_param)
						{
							var coop_detail_label_id = getElementName(content_arr, "coop_detail_label_id["+div_param+"]");
							var coop_detail_pos = getElementName(content_arr, "coop_detail_pos["+div_param+"]");
						}
						else
						{
							var coop_detail_label_id = getElementName(content_arr, "coop_detail_label_id");
							var coop_detail_pos = getElementName(content_arr, "coop_detail_pos");
						}
						all_hidden_input += assemble_hidden_input_element(coop_detail_label_id, coop_detail_label_id_val);
						all_hidden_input += assemble_hidden_input_element(coop_detail_pos, coop_detail_pos_val);
					}
				}
				else
				{
					var coop_channel_tag_val = param_arr['coop_channel_tag_id'];
					if(div_param)
					{
						var coop_channel_tag_id_show = getElementName(content_arr, "coop_channel_tag_id["+div_param+"]");
					}
					else
					{
						var coop_channel_tag_id_show = getElementName(content_arr, "coop_channel_tag_id");
					}
					all_hidden_input += assemble_hidden_input_element(coop_channel_tag_id_show, coop_channel_tag_val);
				}
			}
		}
		//带参数的跳转
		var new_page_name = convertPageType2PageName(page_type,'js');
		//返回的值有的是  汉字  有的是json字符串
		var is_json;
		try{
			var con = eval("("+new_page_name+")");//说明传来的是json参数
			is_json=1;
		}catch(e){
			is_json=2;
		}
		if(is_json==1)
		{
			var arr_page_name = con[0];
			var config_param_arr = eval(con[1]);
			for(m in config_param_arr)
			{
				if(content_arr['parameter_field']&&content_arr['parameter_field']!='null')
				{
					var param_arr=eval('('+content_arr['parameter_field']+')'); 
					if(param_arr[m])
					{
						var m_val = param_arr[m];
						if(div_param)
						{
							var param_show = getElementName(content_arr, m+"["+div_param+"]");
						}
						else
						{
							var param_show = getElementName(content_arr, m);
						}
					
						all_hidden_input += assemble_hidden_input_element(param_show, m_val);
					}
				}
			}
		}
		
    } else if (content_type == 5) {
        var website = content_arr['website'];
        website_open_type = content_arr['website_open_type'];
		if(content_arr['parameter_field']&&content_arr['parameter_field']!='null')
		{
			var param_arr=eval('('+content_arr['parameter_field']+')');
			var mobile_config = param_arr['website_mobile_config'];
			var is_sync_accout = param_arr['website_is_sync_accout'];
			var website_is_actionbar = param_arr['website_is_actionbar'];
			var website_screen_show = param_arr['website_screen_show'];
			var website_is_h5 =  param_arr['website_is_h5'];
		}
		if(div_param)
		{
			var website_element_name = getElementName(content_arr, "website["+div_param+"]");
			var website_open_type_element_name = getElementName(content_arr, "website_open_type["+div_param+"]");
			var website_mobile_config_element_name = getElementName(content_arr, "website_mobile_config["+div_param+"]");
			var website_is_sync_accout_element_name = getElementName(content_arr, "website_is_sync_accout["+div_param+"]");
			var website_is_actionbar_element_name = getElementName(content_arr, "website_is_actionbar["+div_param+"]");
			var website_screen_show_element_name = getElementName(content_arr, "website_screen_show["+div_param+"]");
			var website_is_h5_element_name = getElementName(content_arr, "website_is_h5["+div_param+"]");
		}
		else
		{
			var website_element_name = getElementName(content_arr, "website");
			var website_open_type_element_name = getElementName(content_arr, "website_open_type");
			var website_mobile_config_element_name = getElementName(content_arr, "website_mobile_config");
			var website_is_sync_accout_element_name = getElementName(content_arr, "website_is_sync_accout");
			var website_is_actionbar_element_name = getElementName(content_arr, "website_is_actionbar");
			var website_screen_show_element_name = getElementName(content_arr, "website_screen_show");
			var website_is_h5_element_name = getElementName(content_arr, "website_is_h5");
		}
        all_hidden_input += assemble_hidden_input_element(website_element_name, website);
        all_hidden_input += assemble_hidden_input_element(website_open_type_element_name, website_open_type);
		if(mobile_config&&mobile_config!='undefined'){
			all_hidden_input += assemble_hidden_input_element(website_mobile_config_element_name, mobile_config);
		}
		if(website_open_type==1){
			all_hidden_input += assemble_hidden_input_element(website_is_sync_accout_element_name, is_sync_accout);
			all_hidden_input += assemble_hidden_input_element(website_is_actionbar_element_name, website_is_actionbar);
			all_hidden_input += assemble_hidden_input_element(website_screen_show_element_name, website_screen_show);
			all_hidden_input += assemble_hidden_input_element(website_is_h5_element_name, website_is_h5);
		}

		//如果content_arr['operate_mark']有值说明是灵活运营的运营标识
		if(content_arr['operate_mark'])
		{
			var operate_mark = content_arr['operate_mark'];
			if(div_param)
			{
				var operate_mark1 = getElementName(content_arr, "operate_mark["+div_param+"]");
			}
			else
			{
				var operate_mark1 = getElementName(content_arr, "operate_mark");
			}
			all_hidden_input += assemble_hidden_input_element(operate_mark1, operate_mark);
			var custom_mark = content_arr['custom_mark'];
			if(div_param)
			{
				var custom_operate_mark1 = getElementName(content_arr, "custom_mark["+div_param+"]");
			}
			else
			{
				var custom_operate_mark1 = getElementName(content_arr, "custom_mark");
			}
			all_hidden_input += assemble_hidden_input_element(custom_operate_mark1, custom_mark);

		}
    }else if (content_type == 6) {
        var gift_id = content_arr['gift_id'];
		var page_type = content_arr['page_type'];
		if(div_param)
		{
			var gift_id_element_name = getElementName(content_arr, "gift_id["+div_param+"]");
			var page_type_element_name = getElementName(content_arr, "page_type["+div_param+"]");
		}
		else
		{
			var gift_id_element_name = getElementName(content_arr, "gift_id");
			var page_type_element_name = getElementName(content_arr, "page_type");
		}
        all_hidden_input += assemble_hidden_input_element(gift_id_element_name, gift_id);
        all_hidden_input += assemble_hidden_input_element(page_type_element_name, page_type);
    }else if (content_type == 7) {
        var strategy_id = content_arr['strategy_id'];
		var page_type = content_arr['page_type'];
		if(div_param)
		{
			var strategy_id_element_name = getElementName(content_arr, "strategy_id["+div_param+"]");
			var page_type_element_name = getElementName(content_arr, "page_type["+div_param+"]");
		}
		else
		{
			var strategy_id_element_name = getElementName(content_arr, "strategy_id");
			var page_type_element_name = getElementName(content_arr, "page_type");
		}
        all_hidden_input += assemble_hidden_input_element(strategy_id_element_name, strategy_id);
        all_hidden_input += assemble_hidden_input_element(page_type_element_name, page_type);
    }else if (content_type == 8) {
        var recommend_order_id = content_arr['recommend_order_id'];
		if(div_param)
		{
			var recommend_order_id_element_name = getElementName(content_arr, "recommend_order_id["+div_param+"]");
		}
		else
		{
			var recommend_order_id_element_name = getElementName(content_arr, "recommend_order_id");
		}
        all_hidden_input += assemble_hidden_input_element(recommend_order_id_element_name, recommend_order_id);
    }else if (content_type == 9) {
    	var recommend_obj = eval('(' +  content_arr['parameter_field'] + ')');
    	var used_id = recommend_obj['used_id'];
    	if(div_param) {
    		var used_id_element_name = getElementName(content_arr, "used_id["+div_param+"]");
    	}else {
    		var used_id_element_name = getElementName(content_arr, "used_id");
    	}
        all_hidden_input += assemble_hidden_input_element(used_id_element_name, used_id);
    }else if(content_type == 10) {
    	/*var recommend_obj = eval('(' +  content_arr['parameter_field'] + ')');
    	var recommend_order_id = recommend_obj['recommend_order_id'];*/
    	 var recommend_order_id = content_arr['recommend_order_id'];
    	if(div_param)
		{
			var recommend_order_id_element_name = getElementName(content_arr, "recommend_order_id["+div_param+"]");
		}
		else
		{
			var recommend_order_id_element_name = getElementName(content_arr, "recommend_order_id");
		}
        all_hidden_input += assemble_hidden_input_element(recommend_order_id_element_name, recommend_order_id);
    }
    
    // 生成展示内容
    var content_text = '';
    if (content_type == 1) {
        content_text += assemble_package_display_text(package, uninstall_setting, install_setting, lowversion_setting, start_to_page, function_from,recommend_behavior_id);
    } else if (content_type == 2) {
        // 将activity_id转成activity_name
        var activity_name = convertActivityId2ActivityName(activity_id);
        content_text += assemble_activity_display_text(activity_name);
    } else if (content_type == 3) {
        // 将feature_id转成feature_name
        var feature_name = convertFeatureId2FeatureName(feature_id);
        content_text += assemble_feature_display_text(feature_name);
    } else if (content_type == 4) {
        // 获得页面分类
        var general_page_type = getGeneralPageType(page_type);
        var page_name = convertPageType2PageName(page_type);
        content_text += assemble_page_display_text(general_page_type, page_name);
		//v6.0网页和页面添加运营标识
		if(content_arr['operate_mark'])
		{
			if(content_arr['operate_mark']==30)
			{
				content_text +='<label>运营标识：【' + content_arr['custom_mark'] + '】</label><br/>';	
			}
			else
			{
				var operate_mark_display=convertnum2name(content_arr['operate_mark'])
				content_text +='<label>运营标识：【' + operate_mark_display + '】</label><br/>';
			}
		}
		//v6.2页面个人中心时添加是否弹出随机礼包
		if(page_type=='fixed_personal_center')
		{
			if(content_arr['parameter_field']&&content_arr['parameter_field']!='null')
			{
				var param_arr=eval('('+content_arr['parameter_field']+')'); 
				if(param_arr['is_pop_random_packs'])
				{
					var is_pop_random_packs = param_arr['is_pop_random_packs'];
					if(is_pop_random_packs==1)
					{
						var is_pop_random_packs_display="是";
					}
					if(is_pop_random_packs==2)
					{
						var is_pop_random_packs_display="否";
					}
					content_text +='<label>是否弹出随机礼包：【' + is_pop_random_packs_display + '】</label><br/>';
				}
			}
		}
		//v6.2页面文字快捷入口活动列表时添加活动类型
		if(page_type=='otherfixed_activity_list')
		{
			if(content_arr['parameter_field']&&content_arr['parameter_field']!='null')
			{
				var param_arr=eval('('+content_arr['parameter_field']+')'); 
				//if(param_arr['activity_category_show'])
				//{
					var activity_category_show = param_arr['activity_category_show'];
					if(activity_category_show==1)
					{
						var activity_category_show_display="游戏";
					}
					else if(activity_category_show==2)
					{
						var activity_category_show_display="应用";
					}
					else
					{
						var activity_category_show_display="全部";
					}
					content_text +='<label>活动类型：【' + activity_category_show_display + '】</label><br/>';
				if(activity_category_show==1){
					var tab_index = param_arr['tab_index'];
					if(tab_index==1){
						var tab_index_display = '游戏预约tab';
					}else{
						var tab_index_display = '热门活动tab';
					}
					content_text +='<label>页面类型：【' + tab_index_display + '】</label><br/>';
				}

				//}
			}
		}
		//v6.5 红包活动列表
		if(page_type=='fixed_red_packet_task_list')
		{
			if(content_arr['parameter_field']&&content_arr['parameter_field']!='null')
			{
				var param_arr=eval('('+content_arr['parameter_field']+')'); 
				//if(param_arr['activity_category_show'])
				//{
					var red_task_category_show = param_arr['TAB_INDEX'];
					if(red_task_category_show==1)
					{
						var red_task_category_show_display="非软件任务列表";
					}else{
						var red_task_category_show_display="软件任务列表";
					}
					content_text +='<label>列表类型：【' + red_task_category_show_display + '】</label><br/>';
				//}
			}
		}
		if(page_type.indexOf('bbs_detailpage_')==0)
		{
			//论坛详情的page_type为bbs_detailpage_id格式
			var str_arr = new Array();
			str_arr = page_type.split('_');
			content_text +='<label>论坛详情页TID：【' + str_arr[2] + '】</label><br/>';
		}
		//内容合作
		if(page_type.indexOf('fixed_content_coop_')==0)
		{
			var str_arr = new Array();
			str_arr = page_type.split('_');
			if(content_arr['parameter_field']&&content_arr['parameter_field']!='null')
			{
				var param_arr = eval('('+content_arr['parameter_field']+')');
				var coop_pos_val = parseInt(param_arr['coop_site_id']);
				var coop_site_name = coopid2name(1,coop_pos_val);
				content_text +='<label>站点名称：【' + coop_site_name + '】</label><br/>';
				if(str_arr[3]==3)
				{
					if(param_arr['coop_detail_url'])
					{
						var coop_detail_url3_val = param_arr['coop_detail_url'];
						var coop_detail_url_id_val = param_arr['coop_detail_url_id'];
						var coop_content_id_new_val = param_arr['coop_content_id_new'];
						var coop_detail_title_val = detailurl2title(coop_detail_url3_val);
						content_text +='<label>合作详情页标题：【'+coop_detail_url_id_val+':'+coop_content_id_new_val+':'+ coop_detail_title_val + '】</label><br/>';
					}
					if(param_arr['coop_detail_label_id'])
					{
						var coop_detail_label_val = param_arr['coop_detail_label_id'];
						var coop_detail_pos = param_arr['coop_detail_pos'];
						var coop_detail_label = coopid2name(3,param_arr['coop_detail_label_id']);
						content_text +='<label>合作详情页标签：【' + coop_detail_label + '】</label><br/>';
						content_text +='<label>合作详情页标签下的位置：【' + coop_detail_pos + '】</label><br/>';
					}
				}
				else
				{
					var coop_channel_tag_val = parseInt(param_arr['coop_channel_tag_id']);
					if(str_arr[3]==2)
					{
						var coop_tag_name = coopid2name(3,coop_channel_tag_val);
						content_text +='<label>标签名称：【' + coop_tag_name + '】</label><br/>';
					}
					else
					{
						var coop_channel_name = coopid2name(2,coop_channel_tag_val);
						content_text +='<label>频道名称：【' + coop_channel_name + '】</label><br/>';
					}
				}
			}
		}
		
		//如果每日抽奖兑吧
		if(page_type=='otherfixed_daily_lottery_exchange')
		{
			if(content_arr['parameter_field']&&content_arr['parameter_field']!='null')
			{
				var param_arr=eval('('+content_arr['parameter_field']+')'); 
				if(param_arr['exchange_url'])
				{
					var exchange_url_show = param_arr['exchange_url'];
					content_text +='<label>每日抽奖兑吧地址：【' + exchange_url_show + '】</label><br/>';
				}
			}
		}
		//V6.3优化带参数的跳转
		if(is_json==1)
		{
			var arr_page_name = con[0];
			var config_param_arr = eval(con[1]);
			for(m in config_param_arr)
			{
				if(content_arr['parameter_field']&&content_arr['parameter_field']!='null')
				{
					var param_arr=eval('('+content_arr['parameter_field']+')'); 
					if(param_arr[m])
					{
						if(config_param_arr[m][1]==1)//input的值
						{
							content_text +='<label>'+config_param_arr[m][0]+'：【' + param_arr[m] + '】</label><br/>';
						}
						else if(config_param_arr[m][1]==2)//select的值
						{
							content_text +='<label>'+config_param_arr[m][0]+'：【' + config_param_arr[m][2][param_arr[m]] + '】</label><br/>';
						}
					}
				}
			}
		}
    } else if (content_type == 5) {
		var my_param = [website_is_actionbar,website_screen_show,website_is_h5];
        content_text += assemble_website_display_text(website, website_open_type,mobile_config,is_sync_accout,my_param);
		//v6.0网页和页面添加运营标识
		if(content_arr['operate_mark'])
		{
			if(content_arr['operate_mark']==30)
			{
				content_text +='<label>运营标识：【' + content_arr['custom_mark'] + '】</label><br/>';	
			}
			else
			{
				var operate_mark_display=convertnum2name(content_arr['operate_mark'])
				content_text +='<label>运营标识：【' + operate_mark_display + '】</label><br/>';
			}
		}
    }else if (content_type == 6) {
        // 礼包id不需要转换名称
        content_text += assemble_gift_display_text(gift_id);
    }else if (content_type == 7) {
        // 攻略ID不需要转换名称
        content_text += assemble_strategy_display_text(strategy_id);
    } else if (content_type == 8) {
        // 将recommend_order_id转成recommend_order_name
        var recommend_order_name = convertOrderId2OrderName(recommend_order_id);
        content_text += assemble_order_display_text(recommend_order_name);
    }else if( content_type == 9 ) {
    	  // 应用内览
    	var used_info = convertUsedId2UsedName(used_id);
    	var used_obj = eval('(' +  used_info + ')');
        content_text += assemble_used_display_text(used_obj.package,used_obj.title);
    }else if( content_type == 10 ) {
        var recommend_order_name = convertOrderId2OrderName(recommend_order_id);
        content_text += assemble_game_display_text(recommend_order_name);
    }
    $('#'+append_div_id).append(content_text);
    $("#"+append_div_id).append(content_type_hidden_input_text);
    $("#"+append_div_id).append(all_hidden_input);
}

function generate_content_type_href() {
    var append_div_id = arguments[0];
    var enabled_content_type = arguments[1];
    var content_arr = arguments[2];
	var function_from = arguments[3];//添加第四个变量区别从哪个功能过来的，灵活样式、返回运营、悬浮窗、、
    if (!content_arr) {
        content_arr = new Array();
    }
    // 可以推荐的内容类型
    var content_type_ename = content_arr['content_type_ename'];
    // 内容类型下对应的input命名值
    var package_ename = content_arr['package_ename'];
    var activity_id_ename = content_arr['activity_id_ename'];
    var feature_id_ename = content_arr['feature_id_ename'];
    var page_type_ename = content_arr['page_type_ename'];
    var website_ename = content_arr['website_ename'];
    var website_open_type_ename = content_arr['website_open_type_ename'];
    var gift_id = content_arr['gift_id'];
	var strategy_id = content_arr['strategy_id'];
	
    var href = "/index.php/ContentType/showContentType?";
    href += "append_div_id=" + append_div_id;
    if (enabled_content_type) href += "&enabled_content_type=" + enabled_content_type;
    if(function_from) href +="&function_from=" + function_from;
    if (content_type_ename) href += "&content_type_ename=" + content_type_ename;
    if (package_ename) href += "&package_ename=" + package_ename;
    if (activity_id_ename) href += "&activity_id_ename=" + activity_id_ename;
    if (feature_id_ename) href += "&feature_id_ename=" + feature_id_ename;
    if (page_type_ename) href += "&page_type_ename=" + page_type_ename;
    if (website_ename) href += "&website_ename=" + website_ename;
    if (website_open_type_ename) href += "&website_open_type_ename=" + website_open_type_ename;
	if(gift_id) href += "&gift_id=" + gift_id;
	if(strategy_id) href += "&strategy_id=" + strategy_id;
	
    // 编辑传的值
    if (content_arr['content_type']) {
        href += "&content_type=" + encodeURIComponent(content_arr['content_type']);
        if (content_arr['content_type'] == 1) {
            href += "&package=" + encodeURIComponent(content_arr['package']);
            href += "&uninstall_setting=" + encodeURIComponent(content_arr['uninstall_setting']);
            href += "&install_setting=" + encodeURIComponent(content_arr['install_setting']);
            if (content_arr['install_setting'] == 4) {
                href += "&start_to_page=" + encodeURIComponent(content_arr['start_to_page']);
            }
            href += "&lowversion_setting=" + encodeURIComponent(content_arr['lowversion_setting']);
			if(content_arr['parameter_field']&&content_arr['parameter_field']!='null')
			{
				href +="&parameter_field=" + encodeURIComponent(content_arr['parameter_field']);
			}
        } else if (content_arr['content_type'] == 2) {
            href += "&activity_id=" + encodeURIComponent(content_arr['activity_id']);
        } else if (content_arr['content_type'] == 3) {
            href += "&feature_id=" + encodeURIComponent(content_arr['feature_id']);
        } else if (content_arr['content_type'] == 4) {
            href += "&page_type=" + encodeURIComponent(content_arr['page_type']);
			//灵活运营的运营标识
			if(content_arr['operate_mark'])
			{
				if(content_arr['operate_mark']==30)
				{
					href +="&operate_mark=" + encodeURIComponent(content_arr['operate_mark']);
					href +="&custom_mark=" + encodeURIComponent(content_arr['custom_mark']);
				}
				else
				{
					href +="&operate_mark=" + encodeURIComponent(content_arr['operate_mark']);
				}
			}
			//页面选择个人中心  是否弹出随机礼包
			if(content_arr['parameter_field']&&content_arr['parameter_field']!='null')
			{
				href +="&parameter_field=" + encodeURIComponent(content_arr['parameter_field']);
			}
			//V6.4文字链 新增自动关联
			if(content_arr['is_auto_connect'])
			{
				href +="&is_auto_connect=" + encodeURIComponent(content_arr['is_auto_connect']);
			}
        } else if (content_arr['content_type'] == 5) {
            href += "&website=" + encodeURIComponent(content_arr['website']);
            href += "&website_open_type=" + encodeURIComponent(content_arr['website_open_type']);
			if(content_arr['operate_mark'])
			{
				if(content_arr['operate_mark']==30)
				{
					href +="&operate_mark=" + encodeURIComponent(content_arr['operate_mark']);
					href +="&custom_mark=" + encodeURIComponent(content_arr['custom_mark']);
				}
				else
				{
					href +="&operate_mark=" + encodeURIComponent(content_arr['operate_mark']);
				}
			}

        }else if (content_arr['content_type'] == 6) {
            href += "&gift_id=" + encodeURIComponent(content_arr['gift_id']);
        }else if (content_arr['content_type'] == 7) {
            href += "&strategy_id=" + encodeURIComponent(content_arr['strategy_id']);
        }else if (content_arr['content_type'] == 8) {
            href += "&recommend_order_id=" + encodeURIComponent(content_arr['recommend_order_id']);
        }else if(content_arr['content_type'] == 9) {
        	var recommend_obj = eval('(' +  content_arr['parameter_field'] + ')');
        	var used_id = recommend_obj['used_id'];
        	href += "&used_id=" + encodeURIComponent(used_id);
        }else if(content_arr['content_type'] == 10) {
        	/*var recommend_obj = eval('(' +  content_arr['parameter_field'] + ')');
        	var recommend_order_id = recommend_obj['recommend_order_id'];
        	href += "&recommend_order_id=" + encodeURIComponent(recommend_order_id);*/
        	href += "&recommend_order_id=" + encodeURIComponent(content_arr['recommend_order_id']);
        } 
    }
	if(content_arr['content_type'] == 5){
		if(content_arr['parameter_field']&&content_arr['parameter_field']!='null')
		{
			href +="&parameter_field=" + encodeURIComponent(content_arr['parameter_field']);
		}
	}
    href = encodeURI(href);
    return href;
}

function assemble_hidden_input_element(element_name, element_value) {
    var hidden_input_element = '<input type="hidden" name="' + element_name + '" id="' + element_name + '" value="' + element_value + '"/>';
    return hidden_input_element;
}

function assemble_package_display_text(package, uninstall_setting, install_setting, lowversion_setting, start_to_page,function_from,recommend_behavior_id) {
	
    var display_text = '<label>内容类型：【' + content_type_arr[1] + '】</label><br/>';
    display_text += '<label>包名：【' + package + '】</label><br/>';
	display_text += '<label>未安装：【' + shorten_text(setting_arr[uninstall_setting]) + '】</label><br/>';
	if(function_from != "custom_push")
	{
		display_text += '<label>已安装：【' + shorten_text(setting_arr[install_setting]) + '】</label><br/>';
		if (install_setting == 4) {
			display_text += '<label>启动页面：【' + shorten_text(start_to_page) + '】</label><br/>';
		}
		display_text += '<label>低版本：【' + shorten_text(setting_arr[lowversion_setting]) + '】</label><br/>';
	}
	if(recommend_behavior_id)
	display_text += '<label>行为ID：【' + shorten_text(recommend_behavior_id) + '】</label><br/>';
    return display_text;
}

function assemble_activity_display_text(activity_name) {
    var display_text = '<label>内容类型：【' + shorten_text(content_type_arr[2]) + '】</label><br/>';
    display_text += '<label>活动名称：【' + shorten_text(activity_name) + '】</label><br/>';
    return display_text;
}
function assemble_order_display_text(order_name) {
    var display_text = '<label>内容类型：【' + shorten_text(content_type_arr[8]) + '】</label><br/>';
    display_text += '<label>预约名称：【' + shorten_text(order_name) + '】</label><br/>';
    return display_text;
}

function assemble_feature_display_text(feature_name) {
    var display_text = '<label>内容类型：【' + shorten_text(content_type_arr[3]) + '】</label><br/>';
    display_text += '<label>专题名称：【' + shorten_text(feature_name) + '】</label><br/>';
    return display_text;
}
function assemble_gift_display_text(gift_id) {
    var display_text = '<label>内容类型：【' + shorten_text(content_type_arr[6]) + '】</label><br/>';
    display_text += '<label>礼包ID：【' + shorten_text(gift_id) + '】</label><br/>';
    return display_text;
}
function assemble_strategy_display_text(strategy_id) {
    var display_text = '<label>内容类型：【' + shorten_text(content_type_arr[7]) + '】</label><br/>';
    display_text += '<label>攻略ID：【' + shorten_text(strategy_id) + '】</label><br/>';
    return display_text;
}
function convertnum2name(num)
{
	//1推广、2推荐、3活动、4专题、5精选、6汉化、7破解、30自定义
	if(num==30)
	{
		opera_name="自定义";
	}
	else
	{
		opera_name=operate_mark_arr[num];
	}
	return opera_name;
}
function assemble_page_display_text(general_page_type, page_name) {
    var display_text = '<label>内容类型：【' + content_type_arr[4] + '】</label><br/>';
    display_text += '<label>页面分类：【' + general_page_type_arr[general_page_type] + '】</label><br/>';
    display_text += '<label>页面名称：【' + page_name + '】</label><br/>';
    return display_text;
}

function assemble_website_display_text(website, website_open_type,mobile_config,is_sync_accout,my_param) {
    var display_text = '<label>内容类型：【' + content_type_arr[5] + '】</label><br/>';
    display_text += '<label>网页：【' + shorten_text(website) + '】</label><br/>';
	if(mobile_config&&mobile_config!='undefined'){
		display_text += '<label>高低配：【' + website_mobile_config_arr[mobile_config-1] + '】</label><br/>';
	}
    display_text += '<label>打开方式：【' + website_open_type_arr[website_open_type] + '】</label><br/>';
	if(website_open_type==1){
		display_text += '<label>同步账号：【' + website_is_sync_accout_arr[is_sync_accout-1] + '】</label><br/>';
		if(my_param[0]){
			display_text += '<label>是否使用端内actionbar：【' + website_is_actionbar_arr[my_param[0]] + '】</label><br/>';
		}
		if(my_param[1]){
			display_text += '<label>横竖屏：【' + website_screen_show_arr[my_param[1]] + '】</label><br/>';
		}
		if(my_param[2]){
			display_text += '<label>是否使用h5加载：【' + website_is_h5_arr[my_param[2]] + '】</label><br/>';
		}
	}

    return display_text;
}

function assemble_used_display_text(package,title) {
    var display_text = '<label>内容类型：【' + shorten_text(content_type_arr[9]) + '】</label><br/>';
    display_text += '<label>软件包名：【' + package + '】</label><br/>';
    display_text += '<label>内容标题：【' + title + '】</label><br/>';
    return display_text;
}

function assemble_game_display_text(activity_name) {
	var display_text = '<label>内容类型：【' + shorten_text(content_type_arr[10]) + '】</label><br/>';
    display_text += '<label>活动名称：【' + shorten_text(activity_name) + '】</label><br/>';
    return display_text;
}

function checkIfPackagExists(package) {
    var package_exists = 0;
    var param = {package:package};
    $.ajax({
        url : "/index.php/ContentType/checkIfPackagExists",
        type : "post",
        data : param,
        async : false,
        success : function(data) {
            package_exists = data;
        }
    });
    return package_exists;
}

function convertActivityName2ActivityId(activity_name) {
    var activity_id = 0;
    var param = {activity_name : activity_name};
    $.ajax({
        url : "/index.php/ContentType/convertActivityName2ActivityId",
        type : "post",
        data : param,
        async : false,
        success : function(data) {
            activity_id = data;
        }
    });
    return activity_id;
}

function convertOrderName2OrderId(order_name) {
    var order_id = 0;
    var param = {order_name : order_name};
    $.ajax({
        url : "/index.php/ContentType/convertOrderName2OrderId",
        type : "post",
        data : param,
        async : false,
        success : function(data) {
            order_id = data;
        }
    });
    return order_id;
}

function convertActivityId2ActivityName(activity_id) {
    var activity_name = '';
    var param = {activity_id : activity_id};
    $.ajax({
        url : "/index.php/ContentType/convertActivityId2ActivityName",
        type : "post",
        data : param,
        async : false,
        success : function(data) {
            activity_name = data;
        }
    });
    return activity_name;
}
function convertOrderId2OrderName(order_id) {
    var order_name = '';
    var param = {order_id : order_id};
    $.ajax({
        url : "/index.php/ContentType/convertOrderId2OrderName",
        type : "post",
        data : param,
        async : false,
        success : function(data) {
            order_name = data;
        }
    });
    return order_name;
}

function convertFeatureName2FeatureId(feature_name,ppid) {
    var feature_id = 0;
    var param = 
    {
        feature_name : feature_name,
        ppid : ppid 
    };
    $.ajax({
        url : "/index.php/ContentType/convertFeatureName2FeatureId",
        type : "post",
        data : param,
        async : false,
        success : function(data) {
            feature_id = data;
        }
    });
    return feature_id;
}

function convertFeatureId2FeatureName(feature_id) {
    var feature_name = '';
    var param = {feature_id : feature_id};
    $.ajax({
        url : "/index.php/ContentType/convertFeatureId2FeatureName",
        type : "post",
        data : param,
        async : false,
        success : function(data) {
            feature_name = data;
        }
    });
    return feature_name;
}

function convertPageName2PageType(general_page_type, page_name) {
    var page_type = '';
    var param = {
        general_page_type : general_page_type,
        page_name : page_name
    };
    $.ajax({
        url : "/index.php/ContentType/convertPageName2PageType",
        type : "post",
        data : param,
        async : false,
        success : function(data) {
            page_type = data;
        }
    });
    return page_type;
}

function convertUsedName2UsedId(package) {
   var used_id = '';
	var param = {
    	package : package,
    };
    $.ajax({
        url : "/index.php/ContentType/pub_convertUsedName2UsedId",
        type : "post",
        data : param,
        async : false,
        success : function(data) {
			used_id = data;
        }
    });
    return used_id;
}


function convertUsedId2UsedName(used_id) {
    var info = '';
    var param = {used_id : used_id};
    $.ajax({
        url : "/index.php/ContentType/pub_convertUsedId2UsedName",
        type : "post",
        data : param,
        async : false,
        success : function(data) {
        	info = data;
        }
    });
    return info;
}

function convertOrderId2OrderName(order_id) {
    var order_name = '';
    var param = {order_id : order_id};
    $.ajax({
        url : "/index.php/ContentType/convertOrderId2OrderName",
        type : "post",
        data : param,
        async : false,
        success : function(data) {
            order_name = data;
        }
    });
    return order_name;
}

function convertPageType2PageName(page_type,method) {
    var page_name = '';
    var param = {
        page_type : page_type,
		method:method,
    };
    $.ajax({
        url : "/index.php/ContentType/convertPageType2PageName",
        type : "post",
        data : param,
        async : false,
        success : function(data) {
			page_name = data;
        }
    });
    return page_name;
}

function getGeneralPageType(page_type) {
    var general_page_type = ''
    var param = {
        page_type : page_type
    };
    $.ajax({
        url : "/index.php/ContentType/getGeneralPageType",
        type : "post",
        data : param,
        async : false,
        success : function(data) {
            general_page_type = data;
        }
    });
    return general_page_type;
}

function checkUrl(website) {
    var ok = 0;
    var param = {
        website : website
    };
    $.ajax({
        url : "/index.php/ContentType/checkUrl",
        type : "post",
        data : param,
        async : false,
        success : function(data) {
            ok = data;
        }
    });
    return ok;
}
function check_gift_id(gift_id)
{
	var ok = 0;
    var param = {
        gift_id : gift_id
    };
    $.ajax({
        url : "/index.php/ContentType/check_gift_id",
        type : "post",
        data : param,
        async : false,
        success : function(data) {
            ok = data;
        }
    });
    return ok;
}
function check_strategy_id(strategy_id)
{
	var ok = 0;
    var param = {
        strategy_id : strategy_id
    };
    $.ajax({
        url : "/index.php/ContentType/check_strategy_id",
        type : "post",
        data : param,
        async : false,
        success : function(data) {
            ok = data;
        }
    });
    return ok;
}
//判断推荐内容是否正确
function checkRcontent(form)
{
	var content_type = $(form).find("#content_type").val();
	if (content_type == 1) {
		var package = $(form).find("#package").val();
		if (!package) {
			alert("包名不能为空！");
			return false;
		}
		// 跳转配置
		var uninstall_setting = $(form).find("#uninstall_setting").val();
		if (!uninstall_setting) {
			alert("未安装配置不能为空！");
			return false;
		}
		var install_setting = $(form).find("#install_setting").val();
		if (!install_setting) {
			alert("已安装配置不能为空！");
			return false;
		}
		if (install_setting == 4) {
			var start_to_page = $(form).find("#start_to_page").val();
			if (!start_to_page) {
				alert("启动页面不能为空！");
				return false;
			}
		}
		var lowversion_setting = $(form).find("#lowversion_setting").val();
		if (!lowversion_setting) {
			alert("低版本配置不能为空！");
			return false;
		}
	} else if (content_type == 2) {
		var activity_id = $(form).find("#activity_id").val();
		if (!activity_id) {
			alert("活动不能为空！");
			return false;
		}
	} else if (content_type == 3) {
		var feature_id = $(form).find("#feature_id").val();
		if (!feature_id) {
			alert("专题名称不能为空！");
			return false;
		}
	} else if (content_type == 4) {
		var page_type = $(form).find("#page_type").val();
		if (!page_type) {
			alert("页面不能为空！");
			return false;
		}
	} else if (content_type == 5) {
		var website = $(form).find("#website").val();
		if (!website) {
			alert("网页不能为空！");
			return false;
		}
		var website_open_type = $(form).find("#website_open_type").val();
		if (!website_open_type) {
			alert("网页不能为空！");
			return false;
		}
	}else if (content_type == 6) {
		var gift_id = $(form).find("#gift_id").val();
		if (!gift_id) {
			alert("礼包id不能为空！");
			return false;
		}
	}else if (content_type == 7) {
		var strategy_id = $(form).find("#strategy_id").val();
		if (!strategy_id) {
			alert("攻略id不能为空！");
			return false;
		}
	}else if (content_type == 8) {
		var order_id = $(form).find("#recommend_order_id").val();
		if (!order_id) {
			alert("预约名称不能为空！");
			return false;
		}
	}else if (content_type == 9) {
		var used_id = $(form).find("#used_id").val();
		if (!used_id) {
			alert("该软件未有符合条件的应用预览的内容");
			return false;
		}
	}else if(content_type == 10) {	
		var order_id = $(form).find("#recommend_order_id").val();
		if (!order_id) {
			alert("预约名称不能为空！");
			return false;
		}
	}else {
		alert("内容类型错误！");
		return false;
	}
}

// 获得元素名称（如果用户有指定，则用用户指定的，如果没有，则用默认值）
function getElementName(content_arr, element) {
    element_name = content_arr[element + '_ename'];
    if (!element_name) {
        element_name = element;
    }
    return element_name;
}

function shorten_text(str) {
    var max = 15;
    var new_str = str;
    if (str.length > max) {
        new_str = str.substr(0, max) + '...';
    }
    return new_str;
}
//页面添加 合作内容  用到的js
//内容合作 显示
function show_coop_category(type)
{
	if(type==1)//频道  合作站点和安智市场都有   
	{
		$("#coop_pos").css('display','');
		$("#coop_detail_type").css('display','none');
		$("#coop_detail_url3").css('display','none');
		$("#coop_detail_label_list").css('display','none');
		$("#coop_detail_pos3").css('display','none');
		$("#coop_pos option:eq(0)").css("display","");
		$("#coop_pos option:eq(0)").attr("selected",true);
		show_coop_pos(1);
	}
	else if(type==2)//标签 合作站点有
	{
		$("#coop_pos").css('display','');
		$("#coop_detail_type").css('display','none');
		$("#coop_detail_url3").css('display','none');
		$("#coop_detail_label_list").css('display','none');
		$("#coop_detail_pos3").css('display','none');
		$("#coop_pos option:eq(0)").css("display","none");
		$("#coop_pos option:eq(1)").attr("selected",true);
		show_coop_pos(2);
	}
	else if(type==3) //详情页  合作站点的
	{
		$("#coop_pos").css('display','');
		$("#coop_pos option:eq(0)").css("display","none");
		$("#coop_pos option:eq(1)").attr("selected",true);
		$("#coop_detail_type").css('display','');
		$("#coop_detail_url3").css('display','');
		$("#coop_detail_label_list").css('display','none');
		$("#coop_detail_pos3").css('display','none');
		$('#coop_choose_channel_list').css("display","none");
		$('#coop_choose_label_list').css("display","none");
	}
}
function show_detail_content(type)
{
	var id = $("#coop_pos").val();
	if(type==1)//按详情页标题添加
	{
		$("#coop_detail_url3").css('display','');
		$("#coop_detail_label_list").css('display','none');
		$("#coop_detail_pos3").css('display','none');
		options = {
			serviceUrl:'/index.php/ContentType/DetailTitleName/id/'+id,
			minChars:1,
			width: '300px',
			deferRequestBy: 0,
			onSelect: function(value, data){
			}
		};
		$('#coop_detail_url3').autocomplete(options);
	}
	else if(type==2)//按详情页标签添加
	{
		$("#coop_detail_url3").css('display','none');
		$("#coop_detail_label_list").css('display','');
		$("#coop_detail_pos3").css('display','');
		$.ajax({
			url: '/index.php/ContentType/getcooptag/id/'+id,
			type: 'get',
			async: false,
			success: function(data){
				if(data!='null')
				{
					var data = eval('(' + data + ')');
					var str="";
					for(i in data)
					{
						str += "<option value="+data[i]['id']+">"+data[i]['tag_name']+"</option>";
					}
					$('#coop_detail_label_list').html(str);
				}
				else
				{
					var str = "<option value=0>无</option>";
					$('#coop_detail_label_list').html(str);
					//alert("请选择有标签的站点！");
					//return false;
				}
			}
		});
	}
}
function show_coop_pos(id)
{
	var type = $("#coop_category").val();
	var atl2 = $("#coop_category").attr('atl2');
	
	$('#pubpro_tr_coop_choose_show').css("display","");
	if(type==1) //频道
	{
		$.ajax({
			url: '/index.php/ContentType/getcoopchannel/id/'+id+'/atl2/'+atl2,
			type: 'get',
			async: false,
			success: function(data){
				$("#coop_detail_type").css('display','none');
				$("#coop_detail_url3").css('display','none');
				$("#coop_detail_label_list").css('display','none');
				$("#coop_detail_pos3").css('display','none');
				$('#coop_choose_channel_list').css("display","");
				$('#coop_choose_label_list').css("display","none");
				if(data!='null')
				{
					var data = eval('(' + data + ')');
					var str="";
					for(i in data)
					{
						str += "<option value="+data[i]['id']+">"+data[i]['channel_name']+"</option>";
					}
					$('#coop_choose_channel_list').html(str);
				}
				else
				{
					var str = "<option value=0>无</option>";
					$('#coop_choose_channel_list').html(str);
					//alert("请选择有频道的站点！");
					//return false;
				}
			}
		});
	}
	else if(type==2) //标签
	{
		$.ajax({
			url: '/index.php/ContentType/getcooptag/id/'+id,
			type: 'get',
			async: false,
			success: function(data){
				$("#coop_detail_type").css('display','none');
				$("#coop_detail_url3").css('display','none');
				$("#coop_detail_label_list").css('display','none');
				$("#coop_detail_pos3").css('display','none');
				$('#coop_choose_channel_list').css("display","none");
				$('#coop_choose_label_list').css("display","");
				if(data!='null')
				{
					var data = eval('(' + data + ')');
					var str="";
					for(i in data)
					{
						str += "<option value="+data[i]['id']+">"+data[i]['tag_name']+"</option>";
					}
					$('#coop_choose_label_list').html(str);
				}
				else
				{
					var str = "<option value=0>无</option>";
					$('#coop_choose_label_list').html(str);
					//alert("请选择有标签的站点！");
					//return false;
				}
			}
		});
	}
	else if(type==3) //详情页
	{
		var detail_type = $("#coop_detail_type").val();
		$("#coop_detail_url3").val('');
		if(detail_type==1)//按照标题
		{
			options = {
				serviceUrl:'/index.php/ContentType/DetailTitleName/id/'+id,
				minChars:1,
				width: '300px',
				deferRequestBy: 0,
				onSelect: function(value, data){
					$("#coop_detail_type").css('display','');
					$("#coop_detail_url3").css('display','');
					$("#coop_detail_label_list").css('display','none');
					$("#coop_detail_pos3").css('display','none');
					$('#coop_choose_channel_list').css("display","none");
					$('#coop_choose_label_list').css("display","none");
					//展示url
					var str_span="<div>"+data+"</div>";
					$("#pubpro_tr_coop_choose_show").find("td div").remove();
					$("#pubpro_tr_coop_choose_show").find("td").last().append(str_span);
				}
			};
			$("div[id^='AutocompleteContainter_']").remove(); //删除之前生成的
			$('#coop_detail_url3').autocomplete(options);
		}
		else if(detail_type==2)//按照标签
		{
			$.ajax({
				url: '/index.php/ContentType/getcooptag/id/'+id,
				type: 'get',
				async: false,
				success: function(data){
					$("#coop_detail_type").css('display','');
					$("#coop_detail_url3").css('display','none');
					$("#coop_detail_label_list").css('display','');
					$("#coop_detail_pos3").css('display','');
					$('#coop_choose_channel_list').css("display","none");
					$('#coop_choose_label_list').css("display","none");
					if(data!='null')
					{
						var data = eval('(' + data + ')');
						var str="";
						for(i in data)
						{
							str += "<option value="+data[i]['id']+">"+data[i]['tag_name']+"</option>";
						}
						$('#coop_detail_label_list').html(str);
					}
					else
					{
						var str = "<option value=0>无</option>";
						$('#coop_detail_label_list').html(str);
						//alert("请选择有标签的站点！");
						//return false;
					}
				}
			});
		}
	}
}
function coopid2name(type,id) //type 1:站点 2：频道,3:标签
{
	var name='';
    $.ajax({
        url : "/index.php/ContentType/coopid2name/id/"+id+"/type/"+type,
        type : "get",
        async : false,
        success : function(data) {
            name = data;
        }
    });
    return name;
}
function detailurl2title(url)
{
	url = encodeURIComponent(url);
	var param = "url=" +url;
	$.ajax({
        url : "/index.php/ContentType/detailurl2title",
        type : "post",
		data : param,
        async : false,
        success : function(data) {
            title = data;
        }
    });
    return title;
}
function detailtitle2url(title)
{
	title=encodeURIComponent(title);
	var param = "title=" +title;
	$.ajax({
        url : "/index.php/ContentType/detailtitle2url",
        type : "post",
		data : param,
        async : false,
        success : function(data) {
            url = data;
        }
    });
    return url;
}
function detailid2url(id)
{
	var param = "id=" +id;
	$.ajax({
        url : "/index.php/ContentType/detailid2url",
        type : "post",
		data : param,
        async : false,
        success : function(data) {
            url = data;
        }
    });
    return url;
}
function check_site2id(site_id,id)
{
	var param = "id="+id+"&site_id="+site_id;
	$.ajax({
        url : "/index.php/ContentType/check_site2id",
        type : "post",
		data : param,
        async : false,
        success : function(data) {
            is_same = data;
        }
    });
    return is_same;
}
function check_site2title(site_id,title)
{
	title=encodeURIComponent(title);
	var param = "title="+title+"&site_id="+site_id;
	$.ajax({
        url : "/index.php/ContentType/check_site2title",
        type : "post",
		data : param,
        async : false,
        success : function(data) {
            is_same = data;
        }
    });
    return is_same;
}

function change_open(){
	var pubpro_website_open_type = $("#pubpro_website_open_type").val();
	if(pubpro_website_open_type==1){
		$(".sync_a").css('display','');
	}else{
		$(".sync_a").css('display','none');
	}
}