<?php
include_once(dirname(realpath(__FILE__)).'/init.php');
$username = $_POST['username'];
$dev_id = $user_logic->get_user_info_dev($username);
$result = $user_logic->send_code_mobile($dev_id['id']);
if($result['error']==-29){
	$result['msg'] = "重发短信次数已达上限，建议您更换手机号码尝试";
}

echo $result['msg'];
?>