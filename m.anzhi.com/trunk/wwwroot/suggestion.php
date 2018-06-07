<?php
//m反馈去掉
exit();
include_once (dirname(realpath(__FILE__)).'/init.php');
switch ($_GET['type']) {
    case 'submit':
            if ($_POST['message'] && $_POST['stype'] && $_POST['contact']) {
                gomarket_action('feedback.GoPostBug', array(
                    'KEY' => 'REEDBACK',
                    'MESSAGE' => $_POST['message']."\n".$_POST['phone_model'],
                	'TYPE' => $_POST['stype'],
                ));
                /*if ($_POST['referer_url']) {
                    //header('Location: '. $_POST['referer_url']);exit;
                    $url = $_POST['referer_url'];
                } else {
                    //header('Location: index.php');exit;
                    $url = '/';
                }
    			echo '<script>alert("反馈成功！");window.location.href="'.$url.'";</script>';*/
				$url = '/';
				if ($channel != 'm')  $url .= $channel;
                echo '<script>alert("反馈成功！");window.location.href="'.$url.'";</script>';
				exit;
            } else {
            	 echo '<script>alert("未知错误,请重试！");</script>';
                $_SERVER['HTTP_REFERER'] = $_POST['referer_url'];
            }
        break;
    default:
        break;
}
$tplObj->out['title'] = '反馈意见';
$tplObj->display('suggestion.html');
