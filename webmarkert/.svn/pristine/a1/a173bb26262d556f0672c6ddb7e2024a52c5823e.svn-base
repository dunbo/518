<?php
include_once(dirname(realpath(__FILE__)).'/init.php');
$email = $_POST['email'];
$email_result= $user_logic->check_mail_dev($email);
//$result = json_decode($email_result);
echo $email_result['code'];