	  function changeAction(frm, act){
	     var objForm = document.forms[frm];
	     //alert(objForm.action)
	     alert(act)
	     document.del_url.action= act;
	     document.del_url.submit();
	  }
	  function checkdelete(frm, act) {
	     if(confirm('È·ÊµÒªÉ¾³ýÂð?')) {
	         //changeAction(frm, act);
	         document.del_url.submit();
	         return true;
	     }
	  }
	    function setValue(frm, id, newValue){
	     var objForm = document.forms[frm];
	     for(idx = 0; idx < objForm.elements.length; idx ++) {
	        if(objForm.elements[idx].name == id){
	           objForm.elements[idx].value = newValue;
	        }
	     }
	  }