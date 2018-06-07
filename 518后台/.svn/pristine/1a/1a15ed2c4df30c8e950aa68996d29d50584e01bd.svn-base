function onFileChange(obj,type){	
	if( !obj.value.match( /.csv/i ) ){
		alert('文件格式不正确');
		return false;
	}else{
		if(type == 'del_sub'){
			$('#file_csv_d').html("<input type='submit' name='del_sub' value='导入文件'  />");
		}else{
			$('#file_csv_a').html("<input type='submit' name='add_sub' value='导入文件'  />");
		}
	}
}
