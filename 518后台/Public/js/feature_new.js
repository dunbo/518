//专题配置 js

//页面设置  选择原生页面和H5页面 
function show_feature_page_type()
{
	var feature_page_type = $("input[name='feature_page_type']:checked").val();
	if(feature_page_type==1)//新页面
	{
		$('.old_type').css('display','none');
		$('.new_type').css('display','');
		$('.new_type_bg').css('display','');
		$('.new_type_label').css('display','');
		$('.new_type_text').css('display','');
	}
	else
	{
		$('.old_type').css('display','');
		$('.new_type').css('display','none');
		$('.new_type_bg').css('display','none');
		$('.new_type_label').css('display','none');
		$('.new_type_text').css('display','none');
	}
}

//页面加载的js
//增加的背景颜色

//段落头图  显示
function show_header_type_div(obj)
{
	var value = $(obj).val();
	if(value==1)
	{
		$(obj).parent().find("#header_type_divs").css('display','');
	}
	else
	{
		$(obj).parent().find("#header_type_divs").css('display','none');
	}
}
//段落头图  选择头图的样式
function select_header_type(obj)
{
	var type=$(obj).val();
	if(type==1)
	{
		$(obj).parent().find("span").html('尺寸：宽度480&nbsp&nbsp&nbsp&nbsp格式：jpg、png、gif');
		$(obj).parent().parent().find(".fixed_type_div1").css('display','');
		$(obj).parent().parent().find(".fixed_type_div2").css('display','none');
		$(obj).parent().parent().find(".scroll_divs").css('display','none');
		$(obj).parent().parent().find(".scroll_divs_multiple").css('display','none');
	}
	if(type==2)
	{
		$(obj).parent().find("span").html('尺寸：宽220&nbsp&nbsp&nbsp&nbsp格式：jpg、png、gif');
		$(obj).parent().parent().find(".fixed_type_div1").css('display','none');
		$(obj).parent().parent().find(".fixed_type_div2").css('display','');
		$(obj).parent().parent().find(".scroll_divs").css('display','none');
		$(obj).parent().parent().find(".scroll_divs_multiple").css('display','none');
	}
	if(type==3)
	{
		$(obj).parent().find("span").html('尺寸：宽480&nbsp&nbsp&nbsp&nbsp格式：jpg、png、gif');
		$(obj).parent().parent().find(".fixed_type_div1").css('display','none');
		$(obj).parent().parent().find(".fixed_type_div2").css('display','none');
		$(obj).parent().parent().find(".scroll_divs").css('display','');
		$(obj).parent().parent().find(".scroll_divs_multiple").css('display','none');
	}
	if(type==4)
	{
		$(obj).parent().find("span").html('尺寸：宽220&nbsp&nbsp&nbsp&nbsp格式：jpg、png、gif');
		$(obj).parent().parent().find(".fixed_type_div1").css('display','none');
		$(obj).parent().parent().find(".fixed_type_div2").css('display','none');
		$(obj).parent().parent().find(".scroll_divs").css('display','none');
		$(obj).parent().parent().find(".scroll_divs_multiple").css('display','');
	}
}
//段落头图 选择轮播图可以增加多张图片
var div_num=2;
function add_imgs(obj,section,start)
{
	//单张轮播图添加不能超过6张 否则前端展示不完样式会改变
	//var node_parent = $(obj).parent().attr('id');
	//var chil_count = $("#"+node_parent).children('div').length;
	var chil_count = $(obj).parent().children('div').length;
	if(chil_count>=6)
	{
		alert("单张轮播图最多上传6张图片");
		return false;
	}
	if(start)
	{
		if(start>div_num)
		{
			div_num=start;
		}
	}
	var html = '<div class="scroll_type_div_single" id="scroll_divs' + div_num + '">';
	html += '<br />图片：<input type="file" name="scroll_img[header_scroll_section_' +section +'_'+ div_num + ']" onchange="checkImgPX(this,480)" />&nbsp&nbsp<a id="content_type_a_header_scroll_section_' +section +'_'+ div_num + '" href="javascript:void(0)" class="thickbox"><font color="blue">选择推荐内容</font></a>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<input type="button" value="删除" onclick="deldiv(this)"/><img width="150"/><div id="content_type_div_header_scroll_section_' +section +'_'+ div_num + '" style="margin-left:255px;"></div>';
	html += '</div>';
	$(obj).parent().append(html);
	var div_real_num=div_num;
	tb_init('#scroll_divs' + div_num +' a.thickbox');
	var href = generate_content_type_href('content_type_div_header_scroll_section_'+section+'_'+ div_real_num);
	$('#content_type_a_header_scroll_section_'+section+'_'+ div_real_num).attr("href", href);
	div_num++;
}
var div_num_multiple=2;
function add_imgs_multiple(obj,section,start)
{
	//两张轮播图添加不能超过12张 否则前端展示不完样式会改变
	//var node_parent = $(obj).parent().attr('id');
	//var chil_count = $("#"+node_parent).children('div').length;
	var chil_count = $(obj).parent().children('div').length;
	if(chil_count>=12)
	{
		alert("显示两张轮播图最多上传12张图片");
		return false;
	}
	if(start)
	{
		if(start>div_num_multiple)
		{
			div_num_multiple=start;
		}
	}
	var html = '<div id="scroll_divs_multiple' + div_num_multiple + '">';
	html += '<br />图片：<input type="file" name="scroll_img_multiple[header_scroll_multiple_section_' +section +'_'+ div_num_multiple + ']" onchange="checkImgPX(this,220)" />&nbsp&nbsp<a id="content_type_a_header_scroll_multiple_section_' +section +'_'+ div_num_multiple + '" href="javascript:void(0)" class="thickbox"><font color="blue">选择推荐内容</font></a>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<input type="button" value="删除" onclick="deldiv(this)"/><img width="150"/><div id="content_type_div_header_scroll_multiple_section_' +section +'_'+ div_num_multiple + '" style="margin-left:255px;"></div>';
	html += '</div>';
	$(obj).parent().append(html);
	var div_real_num=div_num_multiple;
	tb_init('#scroll_divs_multiple' + div_num_multiple +' a.thickbox');
	var href = generate_content_type_href('content_type_div_header_scroll_multiple_section_'+section+'_'+ div_real_num);
	$('#content_type_a_header_scroll_multiple_section_'+section+'_'+ div_real_num).attr("href", href);
	div_num_multiple++;
}
//段落头图 选择轮播图可以选择删除增加的图片
function deldiv(obj)
{
	$(obj).parent().remove();
}

//段落标题  显示
function show_title_type_div(obj)
{
	var value = $(obj).val();
	if(value==1)
	{
		$(obj).parent().find("#title_type_divs").css('display','');
	}
	else
	{
		$(obj).parent().find("#title_type_divs").css('display','none');
	}
}
//段落标题   选择标题 内容是文字还是图片
function select_title_type(obj)
{
	var type = $(obj).val();
	if(type==1)
	{
		$(obj).parent().parent().find(".title_text_div").css('display','inline');
		$(obj).parent().parent().find(".title_img_div").css('display','none');
	}
	if(type==2)
	{
		$(obj).parent().parent().find(".title_text_div").css('display','none');
		$(obj).parent().parent().find(".title_img_div").css('display','inline');
	}
}
//段落标题  标题 选择标题背景
function show_title_bg_type(obj)
{
	var type=$(obj).val();
	if(type==1)
	{
		$(obj).parent().find(".paragraph_title_bg_pic").css('display','none');
		$(obj).parent().find(".paragraph_title_bg_color").css('display','none');
	}
	else if(type==2)
	{
		$(obj).parent().find(".paragraph_title_bg_pic").css('display','none');
		$(obj).parent().find(".paragraph_title_bg_color").css('display','');
	}
	else if(type==3)
	{
		$(obj).parent().find(".paragraph_title_bg_pic").css('display','');
		$(obj).parent().find(".paragraph_title_bg_color").css('display','none');
	}
}
//段落背景 显示
function show_bg_type_div(obj)
{
	var value = $(obj).val();
	if(value==1)
	{
		$(obj).parent().find("#bg_type_divs").css('display','');
	}
	else
	{
		$(obj).parent().find("#bg_type_divs").css('display','none');
	}
}
//段落背景 样式选择
function select_bg_type(obj)
{
	var value = $(obj).val();
	if(value==1)
	{
		$(obj).parent().find('#paragraph_bg_color').css('display','');
		$(obj).parent().find('#paragraph_bg_pic').css('display','none');
	}
	else if(value==2)
	{
		$(obj).parent().find('#paragraph_bg_color').css('display','none');
		$(obj).parent().find('#paragraph_bg_pic').css('display','');
	}
}
//段落顶部  显示
function show_top_type_div(obj)
{
	var value = $(obj).val();
	if(value==1)
	{
		$(obj).parent().find('#top_type_divs').css('display','');
	}
	else
	{
		$(obj).parent().find('#top_type_divs').css('display','none');
	}
}
//段落顶部  样式选择
function select_top_type(obj)
{
	var select_val = $(obj).val();
	if(select_val==1)
	{
		$(obj).parent().children('img').css('display','');
		$(obj).parent().children('img').attr('src','/Public/images/feature/top_type1.png');
		$(obj).parent().children('div').css('display','none');
	}
	else if(select_val==2)
	{
		$(obj).parent().children('img').css('display','');
		$(obj).parent().children('img').attr('src','/Public/images/feature/long_line.png');
		$(obj).parent().children('div').css('display','none');
	}
	else if(select_val==3)
	{
		$(obj).parent().children('img').css('display','');
		$(obj).parent().children('img').attr('src','/Public/images/feature/short_line.png');
		$(obj).parent().children('div').css('display','none');
	}
	else if(select_val==4)
	{
		$(obj).parent().children('img').css('display','none');
		$(obj).parent().children('div').css('display','');
	}
}
//段落结尾  样式选择
function select_end_type(obj)
{
	var select_val = $(obj).val();
	if(select_val==1)
	{
		$(obj).parent().children('img').css('display','');
		$(obj).parent().children('img').attr('src','/Public/images/feature/end_type1.png');
		$(obj).parent().children('div').css('display','none');
	}
	else if(select_val==2)
	{
		$(obj).parent().children('img').css('display','');
		$(obj).parent().children('img').attr('src','/Public/images/feature/long_line.png');
		$(obj).parent().children('div').css('display','none');
	}
	else if(select_val==3)
	{
		$(obj).parent().children('img').css('display','');
		$(obj).parent().children('img').attr('src','/Public/images/feature/short_line.png');
		$(obj).parent().children('div').css('display','none');
	}
	else if(select_val==4)
	{
		$(obj).parent().children('img').css('display','none');
		$(obj).parent().children('div').css('display','');
	}
}
//段落标签   显示
function show_label_type_div(obj)
{
	var value = $(obj).val();
	if(value==1)
	{
		$(obj).parent().find('#label_type_divs').css('display','');
	}
	else
	{
		$(obj).parent().find('#label_type_divs').css('display','none');
	}
}
//段落标签  选择样式
function select_label_type(obj)
{
	var select_val = $(obj).val();
	if(select_val==1)
	{
		$(obj).parent().children('img').css('display','');
		$(obj).parent().children('img').attr('src','/Public/images/feature/label_type1.png');
		$(obj).parent().find('#label_pre_words').css('display','');
		$(obj).parent().find('#label_type_text_div').css('display','none');
		$(obj).parent().find('#label_type_pic_div').css('display','none');
	}
	else if(select_val==2)
	{
		$(obj).parent().children('img').css('display','');
		$(obj).parent().children('img').attr('src','/Public/images/feature/label_type2.png');
		$(obj).parent().find('#label_pre_words').css('display','');
		$(obj).parent().find('#label_type_text_div').css('display','none');
		$(obj).parent().find('#label_type_pic_div').css('display','none');
	}
	else if(select_val==3)
	{
		$(obj).parent().children('img').css('display','');
		$(obj).parent().children('img').attr('src','/Public/images/feature/label_type3.png');
		$(obj).parent().find('#label_pre_words').css('display','');
		$(obj).parent().find('#label_type_text_div').css('display','none');
		$(obj).parent().find('#label_type_pic_div').css('display','none');
	}
	else if(select_val==6)
	{
		$(obj).parent().children('img').css('display','');
		$(obj).parent().children('img').attr('src','/Public/images/feature/label_type4.png');
		$(obj).parent().find('#label_pre_words').css('display','');
		$(obj).parent().find('#label_type_text_div').css('display','none');
		$(obj).parent().find('#label_type_pic_div').css('display','none');
	}
	else if(select_val==7)
	{
		$(obj).parent().children('img').css('display','');
		$(obj).parent().children('img').attr('src','/Public/images/feature/label_type5.png');
		$(obj).parent().find('#label_pre_words').css('display','');
		$(obj).parent().find('#label_type_text_div').css('display','none');
		$(obj).parent().find('#label_type_pic_div').css('display','none');
	}
	else if(select_val==4)
	{
		$(obj).parent().children('img').css('display','none');
		$(obj).parent().find('#label_pre_words').css('display','none');
		$(obj).parent().find('#label_type_text_div').css('display','');
		$(obj).parent().find('#label_type_pic_div').css('display','none');
	}
	else if(select_val==5)
	{
		$(obj).parent().children('img').css('display','none');
		$(obj).parent().find('#label_pre_words').css('display','none');
		$(obj).parent().find('#label_type_text_div').css('display','none');
		$(obj).parent().find('#label_type_pic_div').css('display','');
	}
}
//段落标签  一键下载 是否显示
function show_is_one_download(obj)
{
	var value = $(obj).val();
	if(value==1)
	{
		$(obj).parent().find('#one_download_type').css('display','');
	}
	else
	{
		$(obj).parent().find('#one_download_type').css('display','none');
	}
}
//段落标签 标签背景样式选择
function select_label_bg(obj)
{
	var value = $(obj).val();
	if(value==1)
	{
		$(obj).parent().find('#label_bg_color_div').css('display','');
		$(obj).parent().find('#label_bg_pic_div').css('display','none');
	}
	else
	{
		$(obj).parent().find('#label_bg_color_div').css('display','none');
		$(obj).parent().find('#label_bg_pic_div').css('display','');
	}
}
//段落软件 显示
function show_soft_type_div(obj)
{
	var value = $(obj).val();
	if(value==1)
	{
		$(obj).parent().find('#soft_type_divs').css('display','');
	}
	else
	{
		$(obj).parent().find('#soft_type_divs').css('display','none');
	}
}
//段落软件 标签显示预定义样式
function select_soft_label_type(obj)
{
	var select_val = $(obj).val();
	if(select_val==1)
	{
		$(obj).parent().find('img').attr('src','/Public/images/feature/soft_label1.png');
	}
	else if(select_val==2)
	{
		$(obj).parent().find('img').attr('src','/Public/images/feature/soft_label2.png');
	}
	else if(select_val==3)
	{
		$(obj).parent().find('img').attr('src','/Public/images/feature/soft_label3.png');
	}
	else if(select_val==4)
	{
		$(obj).parent().find('img').attr('src','/Public/images/feature/soft_label4.png');
	}
	else if(select_val==5)
	{
		$(obj).parent().find('img').attr('src','/Public/images/feature/soft_label5.png');
	}
}
//段落软件 分割线样式选择
function select_soft_split_line_type(obj)
{
	var select_val = $(obj).val();
	if(select_val==1)
	{
		$(obj).parent().children('img').css('display','');
		$(obj).parent().children('img').attr('src','/Public/images/feature/long_line.png');
		$(obj).parent().find('#soft_split_line_custom_pic').css('display','none');
	}
	else if(select_val==2)
	{
		$(obj).parent().children('img').css('display','');
		$(obj).parent().children('img').attr('src','/Public/images/feature/soft_split2.png');
		$(obj).parent().find('#soft_split_line_custom_pic').css('display','none');
	}
	else if(select_val==3)
	{
		$(obj).parent().children('img').css('display','none');
		$(obj).parent().find('#soft_split_line_custom_pic').css('display','');
	}
}
//段落软件  软件样式选择显示
function select_soft_type(obj)
{
	var select_val = $(obj).val();
	if(select_val==1)
	{
		$(obj).parent().find('#download_button').css('display','');
		$(obj).parent().find('.soft_info_color_div').css('display','');
		$(obj).parent().find('.soft_recommend_info_div').css('display','none');
		$(obj).parent().find('.soft_recommend_people_div').css('display','none');
		$(obj).parent().find('.soft_label_type_div').css('display','none');
		$(obj).parent().find('#soft_title_div').css('display','none');
		$(obj).parent().find('#soft_abstract_div').css('display','none');
		$(obj).parent().find('#soft_recomment_bg_div').css('display','none');
		$(obj).parent().find('#soft_special1_bg_div').css('display','none');
		$(obj).parent().find('#soft_special2_bg_div').css('display','none');
		$(obj).parent().find('#soft_specila_one_download').css('display','none');
		$(obj).parent().find('#soft_split_line_type_divs').css('display','');
	}
	else if(select_val==2)
	{
		$(obj).parent().find('#download_button').css('display','');
		$(obj).parent().find('.soft_info_color_div').css('display','none');
		$(obj).parent().find('.soft_recommend_info_div').css('display','');
		$(obj).parent().find('.soft_recommend_people_div').css('display','');
		$(obj).parent().find('.soft_label_type_div').css('display','');
		$(obj).parent().find('#soft_title_div').css('display','none');
		$(obj).parent().find('#soft_abstract_div').css('display','none');
		$(obj).parent().find('#soft_recomment_bg_div').css('display','none');
		$(obj).parent().find('#soft_special1_bg_div').css('display','none');
		$(obj).parent().find('#soft_special2_bg_div').css('display','none');
		$(obj).parent().find('#soft_specila_one_download').css('display','none');
		$(obj).parent().find('#soft_split_line_type_divs').css('display','');
	}
	else if(select_val==3)
	{
		$(obj).parent().find('#download_button').css('display','');
		$(obj).parent().find('.soft_info_color_div').css('display','');
		$(obj).parent().find('.soft_recommend_info_div').css('display','');
		$(obj).parent().find('.soft_recommend_people_div').css('display','none');
		$(obj).parent().find('.soft_label_type_div').css('display','none');
		$(obj).parent().find('#soft_title_div').css('display','none');
		$(obj).parent().find('#soft_abstract_div').css('display','none');
		$(obj).parent().find('#soft_recomment_bg_div').css('display','none');
		$(obj).parent().find('#soft_special1_bg_div').css('display','none');
		$(obj).parent().find('#soft_special2_bg_div').css('display','none');
		$(obj).parent().find('#soft_specila_one_download').css('display','none');
		$(obj).parent().find('#soft_split_line_type_divs').css('display','');
	}
	else if(select_val==4)
	{
		$(obj).parent().find('#download_button').css('display','');
		$(obj).parent().find('.soft_info_color_div').css('display','none');
		$(obj).parent().find('.soft_recommend_info_div').css('display','none');
		$(obj).parent().find('.soft_recommend_people_div').css('display','none');
		$(obj).parent().find('.soft_label_type_div').css('display','none');
		$(obj).parent().find('#soft_title_div').css('display','');
		$(obj).parent().find('#soft_abstract_div').css('display','');
		$(obj).parent().find('#soft_recomment_bg_div').css('display','');
		$(obj).parent().find('#soft_special1_bg_div').css('display','');
		$(obj).parent().find('#soft_special2_bg_div').css('display','none');
		$(obj).parent().find('#soft_specila_one_download').css('display','');
		$(obj).parent().find('#soft_split_line_type_divs').css('display','none');
	}
	else if(select_val==5)
	{
		$(obj).parent().find('#download_button').css('display','');
		$(obj).parent().find('.soft_info_color_div').css('display','none');
		$(obj).parent().find('.soft_recommend_info_div').css('display','');
		$(obj).parent().find('.soft_recommend_people_div').css('display','none');
		$(obj).parent().find('.soft_label_type_div').css('display','none');
		$(obj).parent().find('#soft_title_div').css('display','');
		$(obj).parent().find('#soft_abstract_div').css('display','');
		$(obj).parent().find('#soft_recomment_bg_div').css('display','');
		$(obj).parent().find('#soft_special1_bg_div').css('display','none');
		$(obj).parent().find('#soft_special2_bg_div').css('display','');
		$(obj).parent().find('#soft_specila_one_download').css('display','');
		$(obj).parent().find('#soft_split_line_type_divs').css('display','none');
	}
}
//段落推荐  显示
function show_recommend_type_div(obj)
{
	var value = $(obj).val();
	if(value==1)
	{
		$(obj).parent().find("#recommend_type_divs").css('display','');
	}
	else
	{
		$(obj).parent().find("#recommend_type_divs").css('display','none');
	}
}
//段落推荐  选择推荐图片的样式
function select_recommend_type(obj)
{
	var type=$(obj).val();
	if(type==1)
	{
		$(obj).parent().find("span").html('尺寸：宽度480&nbsp&nbsp&nbsp&nbsp格式：jpg、png、gif');
		$(obj).parent().parent().find(".recommend_fixed_type_div1").css('display','');
		$(obj).parent().parent().find(".recommend_fixed_type_div2").css('display','none');
		$(obj).parent().parent().find(".recommend_scroll_divs").css('display','none');
		$(obj).parent().parent().find(".recommend_scroll_divs_multiple").css('display','none');
	}
	if(type==2)
	{
		$(obj).parent().find("span").html('尺寸：宽度220&nbsp&nbsp&nbsp&nbsp格式：jpg、png、gif');
		$(obj).parent().parent().find(".recommend_fixed_type_div1").css('display','none');
		$(obj).parent().parent().find(".recommend_fixed_type_div2").css('display','');
		$(obj).parent().parent().find(".recommend_scroll_divs").css('display','none');
		$(obj).parent().parent().find(".recommend_scroll_divs_multiple").css('display','none');
	}
	if(type==3)
	{
		$(obj).parent().find("span").html('尺寸：宽度480&nbsp&nbsp&nbsp&nbsp格式：jpg、png、gif');
		$(obj).parent().parent().find(".recommend_fixed_type_div1").css('display','none');
		$(obj).parent().parent().find(".recommend_fixed_type_div2").css('display','none');
		$(obj).parent().parent().find(".recommend_scroll_divs").css('display','');
		$(obj).parent().parent().find(".recommend_scroll_divs_multiple").css('display','none');
	}
	if(type==4)
	{
		$(obj).parent().find("span").html('尺寸：宽度220&nbsp&nbsp&nbsp&nbsp格式：jpg、png、gif');
		$(obj).parent().parent().find(".recommend_fixed_type_div1").css('display','none');
		$(obj).parent().parent().find(".recommend_fixed_type_div2").css('display','none');
		$(obj).parent().parent().find(".recommend_scroll_divs").css('display','none');
		$(obj).parent().parent().find(".recommend_scroll_divs_multiple").css('display','');
	}
}
//段落推荐 选择轮播图可以增加多张图片
var recommend_div_num=2;
function recommend_add_imgs(obj,section,start)
{
	//单张轮播图添加不能超过6张 否则前端展示不完样式会改变
	//var node_parent = $(obj).parent().attr('id');
	//var chil_count = $("#"+node_parent).children('div').length;
	var chil_count = $(obj).parent().children('div').length;
	if(chil_count>=6)
	{
		alert("单张轮播图最多上传6张图片");
		return false;
	}
	if(start)
	{
		if(start>recommend_div_num)
		{
			recommend_div_num=start;
		}
	}
	var html = '<div id="recommend_scroll_divs' + recommend_div_num + '">';
	html += '<br />图片：<input type="file" name="recommend_scroll_img[recommend_scroll_section_'+section+'_'+recommend_div_num+']" onchange="checkImgPX(this,480)"/>&nbsp&nbsp<a id="content_type_a_recommend_scroll_section_'+section+'_'+recommend_div_num+'" href="javascript:void(0)" class="thickbox"><font color="blue">选择推荐内容</font></a>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<input type="button" value="删除" onclick="deldiv(this)"/><img width="150"/><div id="content_type_div_recommend_scroll_section_'+section+'_'+recommend_div_num+'" style="margin-left:255px;"></div>';
	html += '</div>';
	$(obj).parent().append(html);
	var recommend_real_div_num=recommend_div_num;
	tb_init('#recommend_scroll_divs' + recommend_div_num +' a.thickbox');
	var href = generate_content_type_href('content_type_div_recommend_scroll_section_'+section+'_'+ recommend_real_div_num);
	$('#content_type_a_recommend_scroll_section_'+section+'_'+ recommend_real_div_num).attr("href", href);
	recommend_div_num++;
}
var recommend_multiple_div_num=2;
function recommend_add_imgs_multiple(obj,section,start)
{
	//两张轮播图添加不能超过12张 否则前端展示不完样式会改变
	//var node_parent = $(obj).parent().attr('id');
	//var chil_count = $("#"+node_parent).children('div').length;
	var chil_count = $(obj).parent().children('div').length;
	if(chil_count>=12)
	{
		alert("显示两张轮播图最多上传12张图片");
		return false;
	}
	if(start)
	{
		if(start>recommend_multiple_div_num)
		{
			recommend_multiple_div_num=start;
		}
	}
	var html = '<div id="recommend_scroll_multiple_divs' + recommend_multiple_div_num + '">';
	html += '<br />图片：<input type="file" name="recommend_scroll_img_multiple[recommend_scroll_multiple_section_'+section+'_'+recommend_multiple_div_num+']" onchange="checkImgPX(this,220)" />&nbsp&nbsp<a id="content_type_a_recommend_scroll_multiple_section_'+section+'_'+recommend_multiple_div_num+'" href="javascript:void(0)" class="thickbox"><font color="blue">选择推荐内容</font></a>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<input type="button" value="删除" onclick="deldiv(this)"/><img width="150"/><div id="content_type_div_recommend_scroll_multiple_section_'+section+'_'+recommend_multiple_div_num+'" style="margin-left:255px;"></div>';
	html += '</div>';
	$(obj).parent().append(html);
	var recommend_real_div_num=recommend_multiple_div_num;
	tb_init('#recommend_scroll_multiple_divs' + recommend_multiple_div_num +' a.thickbox');
	var href = generate_content_type_href('content_type_div_recommend_scroll_multiple_section_'+section+'_'+ recommend_real_div_num);
	$('#content_type_a_recommend_scroll_multiple_section_'+section+'_'+ recommend_real_div_num).attr("href", href);
	recommend_multiple_div_num++;
}
//段落结尾  显示
function show_ending_type_div(obj)
{
	var value = $(obj).val();
	if(value==1)
	{
		$(obj).parent().find('#ending_type_divs').css('display','');
	}
	else
	{
		$(obj).parent().find('#ending_type_divs').css('display','none');
	}
}
//段落结尾  样式选择
/*function select_ending_type(obj)
{
	var select_val = $(obj).val();
	if(select_val==1)
	{
		$(obj).parent().children('img').css('display','');
		$(obj).parent().children('img').attr('src','/Public/images/feature/downloads1.png');
		$(obj).parent().children('div').css('display','none');
	}
	else if(select_val==2)
	{
		$(obj).parent().children('img').css('display','');
		$(obj).parent().children('img').attr('src','/Public/images/feature/downloads2.png');
		$(obj).parent().children('div').css('display','none');
	}
	else if(select_val==3)
	{
		$(obj).parent().children('img').css('display','');
		$(obj).parent().children('img').attr('src','/Public/images/feature/downloads3.png');
		$(obj).parent().children('div').css('display','none');
	}
	else if(select_val==4)
	{
		$(obj).parent().children('img').css('display','none');
		$(obj).parent().children('div').css('display','');
	}
}*/
function check_submit(obj)
{
	var name = $('#name').val();
	if(!name)
	{
		alert("专题类别名称不能为空");
		return false;
	}
	var remark = $('#remark').val();
	if(!remark||remark=="请输入专题详情简介")
	{
		alert("专题详情简介不能为空");
		return false;
	}
	var public_tm = $('#public_time').val();
	if(public_tm=="")
	{
		alert("上线时间不能为空");
		return false;
	}
	var pid=document.getElementsByName("pid[]");
	var pids="";
	for(var i=0;i<pid.length;i++)
	{
	   if(pid[i].checked == true)
	   {
		pids +=  pid[i].value;
	   }
	}
	if(!pids)
	{
		alert("请勾选产品类型");
		return false;
	}
	//图文段落判断
	var feature_page_type = $('[name="feature_page_type"]:checked').val();
	if(feature_page_type==1)//h5 main_key[]
	{
		var main_key=document.getElementsByName("main_key[]");
		var length =main_key.length-1;
		for(var i=0;i<length;i++)
		{
			var key = main_key[i].value;
			var paragraph_detail_value=document.getElementById("paragraph_detail_"+key+"").value;
			if(!paragraph_detail_value)
			{
				alert("请填写段落详情！");
				return false;
			}
		}
			
	}
	else
	{
		var paragraph_detail=document.getElementsByName("editor_content[]");
		for(var j=0;j<paragraph_detail.length;j++)
		{
			var paragraph_detail_value =  paragraph_detail[j].value;
			if(!paragraph_detail_value)
			{
				alert("请填写图文！");
				return false;
			}
		}
	}
	if(!obj)//说明是添加  带参数的是编辑
	{
		//上传图片判断  根据php文档 判断专题图片（尝鲜和web图片必填）
		var fresh_hoticon = document.getElementById("fresh_hoticon").value;
		var webicon = document.getElementById("webicon").value;
		if(!fresh_hoticon||!webicon)
		{
			alert("请选择图片！！！");
			return false;
		}
		//增加  高分低分图片必填
		var icon_604_204 = document.getElementById("icon_604_204").value;
		var icon_444_150 = document.getElementById("icon_444_150").value;
		if(!icon_604_204||!icon_444_150)
		{
			alert("请选择高低分图片！！！");
			return false;
		}
	}
	var init_val = parseInt($.trim($('#init_val').val()));
	var random_start = parseInt($.trim($('#random_start').val()));
	var random_end = parseInt($.trim($('#random_end').val()));
	var r=/^[1-9]\d*$/;
	if(init_val){
		if(r.test(init_val) == false){
			alert("初始值请填写正整数");
			return false;
		}
	}else{
		alert("点赞数初始值为必填");
		return false;
	}
	if(random_start){
		if(r.test(random_start) == false){
			alert("随机值最小值请填写正整数");
			return false;
		}
	}else{
		alert("点赞数随机值最小值为必填");
		return false;
	}
	if(random_end){
		if(r.test(random_end) == false){
			alert("随机值最大值请填写正整数");
			return false;
		}
	}else{
		alert("点赞数随机值最大值为必填");
		return false;
	}
	if(random_start >= random_end){
		alert("点赞数随机值最小值不能大于最大值");
		return false;
	}
}
//获取文件大小的函数 暂时没用到
function bytesToSize(bytes) {
	var sizes = ['Bytes', 'KB', 'MB'];
	if (bytes == 0) return 'n/a';
	var i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)));
	return (bytes / Math.pow(1024, i)).toFixed(1) + ' ' + sizes[i];
}
function checkImgPX(ths,width,height) {
	if(window.FileReader) {  
		var oFile = ths.files[0];
		// filter for image files
		var rFilter = /^(image\/bmp|image\/gif|image\/jpeg|image\/png|image\/tiff)$/i;
		if (! rFilter.test(oFile.type)) {
			//document.getElementById('error').style.display = 'block';
			alert("图片格式不正确！");
			return;
		}
		// get preview element
		var oImage = $(ths).parent().children('img')[0];//这样写才等同与document.getElementById()

		// prepare HTML5 FileReader
		var oReader = new FileReader();
		oReader.onload = function(e){
			// e.target.result contains the DataURL which we will use as a source of the image
			oImage.src = e.target.result;
			oImage.onload = function () { // binding onload event
				// we are going to display some custom image information here
				//sResultFileSize = bytesToSize(oFile.size);//文件大小
				//alert(sResultFileSize);
				/*document.getElementById('fileinfo').style.display = 'block';
				document.getElementById('filename').innerHTML = 'Name: ' + oFile.name;//文件名称
				document.getElementById('filesize').innerHTML = 'Size: ' + sResultFileSize;
				document.getElementById('filetype').innerHTML = 'Type: ' + oFile.type;//文件类型
				document.getElementById('filedim').innerHTML = 'Dimension: ' + oImage.naturalWidth + ' x ' + oImage.naturalHeight;*///文件图片宽和高
				if(width&&height)
				{
					if(oImage.naturalWidth!=width||oImage.naturalHeight!=height)
					{
						$(ths).val("");
						$(ths).parent().children('img').removeAttr('src');
						alert("请上传宽为"+width+",高为"+height+"的图片！");
						return false;
					}
				}
				else if(width&&!height)
				{
					if(oImage.naturalWidth!=width)
					{
						$(ths).val("");
						$(ths).parent().children('img').removeAttr('src');
						alert("请上传宽为"+width+"的图片！");
						return false;
					}
				}
				else if(!width&&height)
				{
					if(oImage.naturalHeight!=height)
					{
						$(ths).val("");
						$(ths).parent().children('img').removeAttr('src');
						alert("请上传高为"+height+"的图片！");
						return false;
					}
				}
				else
				{
					
				}
			};
		};
		// read selected file as DataURL
		oReader.readAsDataURL(oFile);
	}  
	else {  
		alert("该浏览器不支持H5!");  
	}  
}