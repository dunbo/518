//内容属性、标签js函数
function checklist(obj){
	var column_select = '';
	/*var obj_column = document.getElementsByName("cont_column[]");
	for(k in obj_column){
		if(obj_column[k].checked)
		  column_select += obj_column[k].title+',';
	}*/
	$("input[name^='cont_column']:checked").each(function(){
		column_select += this.title+',';
	});
	$("#selectlist").text(column_select);
}

function tag_three(objs){
	var value_id = objs.value;
	//隐藏二级标签下的三级标签
	var classname = $('#tag_'+value_id).attr('class');
	var slect_class = $('.'+classname);
	for(var k=0;k<slect_class.length;k++){
		var tagsecond = document.getElementById('tagt_'+slect_class[k].value);
		tagsecond.style.display = 'none';
	}
	//隐藏二级标签下的三级标签	
	var childobj = document.getElementById('tagt_'+value_id);
	if(objs.checked == true){
		childobj.style.display = 'block';
	}else{
		childobj.style.display = 'none';
		//取消三级标签中的值
		var objthree = $('.tagth_'+value_id);
		for(var i=0;i<objthree.length;i++){
			if(objthree[i].checked == true){
				var v_id = objthree[i].value;
				var rankobj = document.getElementById('rank_'+v_id);
				rankobj.style.display = 'none';
				$("input[name='"+v_id+"_rank']:checked").each(function(){
					this.checked = false;
				});
				objthree[i].checked = false;
			}
		}
	}
	show_tags();
}
function tag_rank(ob){
	var v_id = ob.value;
	var rankobj = document.getElementById('rank_'+v_id);
	if(ob.checked == true){
		rankobj.style.display = 'block';
		/*$("input[name='"+v_id+"_rank']").each(function(){
			if(this.value == v_id+'_1') this.checked = true;
		});*/
	}else{
		rankobj.style.display = 'none';
		$("input[name='"+v_id+"_rank']:checked").each(function(){
			this.checked = false;
		});
	}
	show_tags(v_id);
}

function tag_trank(val){
	var v_id = val;
	var rankobj = document.getElementById('rank_'+v_id);
	var th_tag = document.getElementById('tag_'+v_id);
	if(th_tag.checked == true){
		th_tag.checked = false;
		rankobj.style.display = 'none';
		$("input[name='"+v_id+"_rank']:checked").each(function(){
			this.checked = false;
		});
	}else{
		th_tag.checked = true;
		rankobj.style.display = 'block';
		/*$("input[name='"+v_id+"_rank']").each(function(){
			if(this.value == v_id+'_1') this.checked = true;
		});*/
	}
	show_tags(v_id);
}

function select_tag(objslect){
	var rank = objslect.title;
	var tag_id = objslect.value;
	show_tags();
}
function select_tt(vals){
	var tag_id = vals.split('_');
	$("input[name='"+tag_id[0]+"_rank']").each(function(){
		if(this.value == vals){
			this.checked = true;
		}
	});
	show_tags();
}

function show_three(value){
	document.getElementById('tag_'+value).checked = true;
	var classname = $('#tag_'+value).attr('class');
	var slect_class = $('.'+classname);
	for(var k=0;k<slect_class.length;k++){
		var show_value = slect_class[k].value;
		var tagsecond = document.getElementById('tagt_'+show_value);
		if(show_value == value){
			tagsecond.style.display = 'block';
		}else{
			tagsecond.style.display = 'none';
		}
	}
	show_tags();
}


function show_tags(values){
	var len = 3;
	var slelect_tags = '';
	for(var a =1;a<=len;a++){
		var _show1 = $('.tag_first_'+a);
	var k = 0;
		for (var i = 0;i<_show1.length;i++) {
			var tagid = _show1[i].value;
			var sta2 = '';
			if(_show1[i].checked == true){
				var _showthree1 = $('.tagth_'+tagid);
				for(var j=0;j<_showthree1.length;j++){
					var sta3 = '';
					if(_showthree1[j].checked == true){
						if((a == 1|| a == 2) && k>=3){
							alert('最多可选三个三级标签！');
							re_tag_reset(_showthree1[j].value);
							return false;
						}
						var id1 = _showthree1[j].value;
						var slelct_rank = '';
						$("input[name='"+id1+"_rank']:checked").each(function(){

							slelct_rank = '<div style="float:left;margin-left:5px;"><span style="color:#00f;">'+this.title+'</span><img width="15" height="15" src="/Public/img/miniclose.jpg" onclick="reset_select(\''+this.value+'\')"></div>';
						});
						if(slelct_rank == '' || slelct_rank == undefined){
							sta3 = '<div style="float:left;margin-left:5px;"><span style="color:#00f;">'+_showthree1[j].title+'</span><img width="15" height="15" src="/Public/img/miniclose.jpg" onclick="reset_select(\''+_showthree1[j].value+'_0\')"></div>';
						}else{
							sta3 = slelct_rank;
						}
						k++;
					}
					if(sta3 != ''){
						sta2 += sta3;
					}
				}
				if(sta2 != ''){
					slelect_tags += sta2;
				}
			}
		}
	}
	$("#tags_select").empty();
	$("#tags_select").append(slelect_tags);
}
function reset_select(val){
	var value = val;
	if(typeof(value) == "number"){
		$("input[name^='tag']:checked").each(function(){
			if(value == this.value){
				this.checked = false;
				document.getElementById('tagt_'+value).style.display = 'none';
			}	
		});
	}else{
		var tagid = value.split('_');
		$("input[name^='tag']:checked").each(function(){
			if(tagid[0] == this.value){
				this.checked = false;
			}	
		});
		$("input[name='"+tagid[0]+"_rank']:checked").each(function(){
			this.checked = false;
		});
	}
	show_tags();
}

function cont_tags(){
	//此长度根据一级标签的个数定
	var len = 3; 
	var tag = '';
	for(var a =1;a<=len;a++){
		var _show1 = $('.tag_first_'+a);
		for (var i = 0;i<_show1.length;i++) {
			if(_show1[i].checked == true){
				var tagid = _show1[i].value;
				var _showthree1 = $('.tagth_'+tagid);
				var three_tag = '';
				for(var j=0;j<_showthree1.length;j++){
					if(_showthree1[j].checked == true){
						var id1 = _showthree1[j].value;
						var slelct_rank = '';
						$("input[name='"+id1+"_rank']:checked").each(function(){
							slelct_rank = this.value;
						});
						if(slelct_rank  == '' || slelct_rank == undefined){
							var m_sg = _show1[i].title+'下的子标签：'+_showthree1[j].title+' 优先级不能为空！';
							alert(m_sg);
							return false;
							// three_tag += a+'_'+_show1[i].value+'_'+_showthree1[j].value+',';
						}else{
							three_tag += a+'_'+_show1[i].value+'_'+slelct_rank+',';
						}
					}	
				}
				if(three_tag == ''){
					var _msg = _show1[i].title+' 的子分类至少选择一个标签';
					alert(_msg);
					return false;
					// tag += a+'_'+_show1[i].value+',';
				}else{
					tag += three_tag;
				}
			}
		}
	}
	$("#content_tags").val(tag);
	return true;
}

function re_tag_reset(num){
	var v_id = num;
	var rankobj = document.getElementById('rank_'+v_id);
	var th_tag = document.getElementById('tag_'+v_id);
	if(th_tag.checked == true){
		th_tag.checked = false;
		rankobj.style.display = 'none';
		$("input[name='"+v_id+"_rank']:checked").each(function(){
			this.checked = false;
		});
	}
}

function column_select(obs){
	var _column_select = '';
	$("input[name^='cont_column']:checked").each(function(){
	    _column_select += '<div style="float:left;margin-left:5px;"><span style="color:#00f;">'+this.title+'</span><img width="15" height="15" src="/Public/img/miniclose.jpg" onclick="reset_column('+this.value+')"></div>';
    });
    $("#selectlist").empty();
    $("#selectlist").append(_column_select);
}
function reset_column(osb){
	document.getElementById('column_'+osb).checked = false;
	column_select(osb);
}


