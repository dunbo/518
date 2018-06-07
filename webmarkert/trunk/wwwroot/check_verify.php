<?php
if($_GET['do'] == 'secc') {	//验证码检测
	include_once(dirname(realpath(__FILE__))."/checkcode/config.php");

	if(formcheck($_GET['secc'],1)) {	//即时验证,不更换验证码
		exit('1');	//验证通过
	} else {
		exit('0');
	}

}
exit('ERROR');