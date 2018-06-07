function onFileChange_two(obj,id) {
    var index = $(obj).attr('name');
    if (index == id) {
        if (!obj.value.match(/.jpg|.png|.pdf/i)) {
            alert('上传格式出错');
            var file = $("#"+id);
            file.after(file.clone().val(""));
            file.remove();
            return false;
        }
    }
    if(id=='record_url'){
        $('#record_url_pre').val($('#record_url').val());
        // $('#show_del_1').show();
        $('#del_ba').val('');
    }else if(id=='publication_url'){
        $('#publication_url_pre').val($('#publication_url').val());
        // $('#show_del_2').show();
        $('#del_cb').val('');
    }else if(id=='dev_auth_url'){
        $('#dev_auth_url_pre').val($('#dev_auth_url').val());
    }else if(id=='coop_auth_url'){
        $('#coop_auth_url_pre').val($('#coop_auth_url').val());
    }else if(id=='soft_auth_url'){
        $('#soft_auth_url_pre').val($('#soft_auth_url').val());
    }else if(id=='ip_auth_url'){
        $('#ip_auth_url_pre').val($('#ip_auth_url').val());
        $('#del_ip').val('');
    }
}
function del_pb_ba(id){
    if(id=='record_url'){
        $('#del_ba').val(1);
        $('#record_url_pre').val('');
        var file = $("#record_url");
        file.after(file.clone().val(""));
        file.remove();
    }else if(id=='publication_url'){
    	var file = $("#publication_url");
        file.after(file.clone().val(""));
        file.remove();
        $('#del_cb').val(1);
        $('#publication_url_pre').val('');
    }else if(id=='ip_auth_url'){
    	var file = $("#ip_auth_url");
        file.after(file.clone().val(""));
        file.remove();
        $('#del_ip').val(1);
        $('#ip_auth_url_pre').val('');
    }

}

function myBrowser(){
    var userAgent = navigator.userAgent; 
    if (userAgent.indexOf("Firefox") > -1) {
        $('#record_url').css('margin-top','0px');
        $('#record_url').css('margin-left','-94px');
        $('#publication_url').css('margin-top','0px');
        $('#publication_url').css('margin-left','-94px');

        $('#dev_auth_url').css('margin-top','0px');
        $('#dev_auth_url').css('margin-left','-94px');
        $('#coop_auth_url').css('margin-top','0px');
        $('#coop_auth_url').css('margin-left','-94px');
        $('#soft_auth_url').css('margin-top','0px');
        $('#soft_auth_url').css('margin-left','-94px');
        $('#ip_auth_url').css('margin-top','0px');
        $('#ip_auth_url').css('margin-left','-94px');
        
    } 
    if (userAgent.indexOf("Chrome") > -1){
		$('#record_url').css('margin-top','-30px');
        $('#record_url').css('margin-left','13px');
        $('#publication_url').css('margin-top','-30px');
        $('#publication_url').css('margin-left','13px');

        $('#dev_auth_url').css('margin-top','-30px');
        $('#dev_auth_url').css('margin-left','13px');
        $('#coop_auth_url').css('margin-top','-30px');
        $('#coop_auth_url').css('margin-left','13px');
        $('#soft_auth_url').css('margin-top','-30px');
        $('#soft_auth_url').css('margin-left','13px');
        $('#ip_auth_url').css('margin-top','-30px');
        $('#ip_auth_url').css('margin-left','13px');
	}
}
window.onload = myBrowser;
function is_dev_auth_url_display(){
	if($('#nature').val()=='代理'){
		$('#dev_auth_url_display').show();
	}else{
		$('#dev_auth_url_display').hide();
	}
}
