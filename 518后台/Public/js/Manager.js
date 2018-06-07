//验证登陆用
  function check(o){
            var username=document.getElementById("username");
            var password=document.getElementById("password");
            username.value=username.value.replace(/ |&|(\')|(\\)|;|%|>|<|=|--|(\.)|"|(\*)|(\+)|(\!)|(\,)|(\()|(\))|(\|)/g,"");
            password.value=password.value.replace(/ |&|(\')|(\\)|;|%|>|<|=|--|(\.)|"|(\*)|(\+)|(\!)|(\,)|(\()|(\))|(\|)/g,"");
            if (username.value==""){
                Ext.MessageBox.alert("对不起","请输入管理员名称!");
                username.focus();
                return false;
            }
            if (password.value==""){
              Ext.MessageBox.alert("对不起","请输入密码!");
                password.focus();
                return false;
            }
           return true;
        }
//隐藏显示
function display(obj){
    var str = document.getElementById(obj);
    if (str.style.display == "") {
        str.style.display = "none";
    } else {
        str.style.display = "";
    }
}
//验证密码
	function checkPas(){
		var p1 = document.getElementById("password1");
		var p2 = document.getElementById("password");
		if(p1.value != p2.value){
			alert("二次密码不一样");
			return false;
		}
        if(p1.value==''||p2.value=='')
        {
            alert("不能为空！");
			return false;
        }
        if(p1.value.length<6)
        {
            alert("密码长度不能小于6位");
			return false;
        }
	}
//单击一个单选 选择下所有DIV 的checkbox
function docheckbox(uid) {

    $('#'+uid+" :checkbox").attr("checked", true);

}

//仅能输入数字
 function checkNumber(e) {

       var obj=window.event?window.event:e;
       var k=window.event?obj.keyCode:obj.which;

       if ((event.keyCode>=48)&&(event.keyCode<=57)) {
                 event.returnValue=true;
       }else {
            event.returnValue=false;
       }

    }
//全选
/*
function SelectAll(obj) {
	var sid = document.getElementsByName("id[]");
	if ( obj.value == "全选" ) {
		obj.value = "反选";
		Select( sid , true );
	} else {
		obj.value = "全选";
		Select( sid , false );
	}
}
*/

function SelectAll(obj) {
	var sid = document.getElementsByName("id[]");
        if(obj.checked  == true){
		Select( sid , true );
        } 
        if(obj.checked  == false){
		Select( sid , false );
	}
}

function Select( obj , check ) {
	for ( i = 0 ; i < obj.length ; i++ ) {
		obj[i].checked = check;
	}
	return;
}
//删除 ?号传值
function Delete( url ) {
	var sid = document.getElementsByName("id[]");
	var id = "";
	var is_select = false;
	for ( i = 0 ; i < sid.length ; i++ ) {
		if ( sid[i].checked == true ) {
			id += "," + sid[i].value;
			is_select = true;
		} else {

		}
	}
	if ( is_select ) {
		if ( confirm("该操作将不可逆！\n您确定要执行该操作吗？")) {
			window.location = url + "?id=" + id;
		} else {
			return false;
		}
	} else {
		alert( "请至少选择一个条目，才能进行操作" );
		return false;
	}
}
//删除 /路由传值
function Delete2( url ) {
	var sid = document.getElementsByName("id[]");
	var id = "";
	var is_select = false;
	for ( i = 0 ; i < sid.length ; i++ ) {
		if ( sid[i].checked == true ) {
			id += "," + sid[i].value;
			is_select = true;
		} else {

		}
	}
	if ( is_select ) {
		if ( confirm("该操作将不可逆！\n您确定要执行该操作吗？")) {
			window.location = url + "/id/" + id;
		} else {
			return false;
		}
	} else {
		alert( "请至少选择一个条目，才能进行操作" );
		return false;
	}
}
//批量操作 /路由传值
function Lotdo( url ,returnurl) {
		//alert(url);
	var sid = document.getElementsByName("id[]");
	var id = "";
	var is_select = false;
	for ( i = 0 ; i < sid.length ; i++ ) {
		if ( sid[i].checked == true ) {
			id += "," + sid[i].value;
			is_select = true;
		} else {

		}
	}
	if ( is_select ) {
		if ( confirm("该操作将不可逆！\n您确定要执行该操作吗？")) {
			window.location = url + "/softid/" + id +"/type/"+returnurl;
		} else {
			return false;
		}
	} else {
		alert( "请至少选择一个条目，才能进行操作" );
		return false;
	}
}

//评论专用

function Pass2( url ) {
	var sid = document.getElementsByName("id[]");
	var id = "";
	var is_select = false;
	for ( i = 0 ; i < sid.length ; i++ ) {
		if ( sid[i].checked == true ) {
			id += "," + sid[i].value;
			is_select = true;
		} else {

		}
	}
	if ( is_select ) {
		if ( confirm("该操作将不可逆！\n您确定要执行该操作吗？")) {
			window.location = url + "/id/" + id;
		} else {
			return false;
		}
	} else {
		alert( "请至少选择一个条目，才能进行操作" );
		return false;
	}
}

//by张辉 评论驳回专用
function zh_msg( url ) {
	var sid = document.getElementsByName("id[]");
	var id = "";
	var is_select = false;
	for ( i = 0 ; i < sid.length ; i++ ) {
		if ( sid[i].checked == true ) {
			id += "," + sid[i].value;
			is_select = true;
		} else {

		}
	}
	 var denymsg=document.getElementById("denymsg").value;
    if (denymsg=='' ||denymsg==null) {
        denymsg='未通过';
    }
	if ( is_select ) {
		if ( confirm("该操作将不可逆！\n您确定要执行该操作吗？")) {
			window.location = url + "/id/" + id + "/zh_msg/" +denymsg;
		} else {
			return false;
		}
	} else {
		alert( "请至少选择一个条目，才能进行操作" );
		return false;
	}
}


//by张辉评论驳回单条专用
function zh_one_msg( url ) {
	var id = document.getElementById("zh_id").value;
	 var denymsg=document.getElementById("denymsg").value;
    if (denymsg=='' ||denymsg==null) {
        denymsg='未通过';
    }
	if (id) {
		if ( confirm("该操作将不可逆！\n您确定要执行该操作吗？")) {
			window.location = url + "/id/" + id + "/zh_one_msg/" + denymsg;
		} else {
			return false;
		}
	} else {
		alert( "操作失败" );
		return false;
	}
}




//驳回专用
function deny_msg( url,returnurl ) {
	var sid = document.getElementsByName("id[]");
	var id = "";
	var is_select = false;
	for ( i = 0 ; i < sid.length ; i++ ) {
		if ( sid[i].checked == true ) {
			id += "," + sid[i].value;
			is_select = true;
		} else {

		}
	}
    var denymsgid=document.getElementById("denymsgid").value;
    //alert(denymsgid);
    var denymsg=document.getElementById("denymsg").value;
    if (trim(denymsg)=='' ||trim(denymsg)==null || trim(denymsg).lenth==0) {
        denymsg='未通过';
    }
	//name+="/m_card/"+document.getElementById("m_card").value;

	if ( is_select ) {
		if ( confirm("该操作将不可逆！\n您确定要执行该操作吗？")) {
			window.location = url + "/softid/" + encodeURI(id) + "/denyid/"+ encodeURI(denymsgid) + "/denymsg/" + encodeURI(denymsg) + "/type/" + encodeURI(returnurl);
		} else {
			return false;
		}
	} else {
		alert( "请至少选择一个条目，才能进行操作" );
		return false;
     }

}
function trim(str){
	// 去除两边空字符串by haoxian
	return str.replace(/^\s*(.*?)[\s\n]*$/g,  '$1');
}

function selectperson()
{
  var r=window.showModalDialog("selecttheperson","","dialogHeight:100,dialogHeight:100");
  if(r)
  {
        document.getElementById("person").value=r;
  }
  else
  {
        document.getElementById("person").value='请选择或填写收件人';
  }
}


function opens(url) {
	window.open(url, '', 'height=500, width=850, top=200, left=150, toolbar=no, menubar=no, scrollbars=yes, resizable=no,location=n o, status=no');
}
function checkit()
{
    if(confirm("确定要这么执行该操作吗？"))
    {
        return true;
    }
    return false;
}

function checkit_remind()
{
    if(confirm("请确认是否存在提醒词？在确定提交！"))
    {
        return true;
    }
    return false;
}
