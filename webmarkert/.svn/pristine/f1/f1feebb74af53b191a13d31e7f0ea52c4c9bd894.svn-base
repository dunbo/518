<?php
include_once(dirname(realpath(__FILE__)).'/init.php');
$feedback_logic = pu_load_logic('feedback');
$report_type = $_POST['report_no'];
$data['ipmsg'] = $_SERVER["REMOTE_ADDR"];                                                                                                                                                                                                                                                                               
$data['jbori'] = (int)$_POST['jbori'];
$data['content'] = $_POST['report_content'];
$data['softid'] = $_GET['softid'];
$data['userid'] = $_SESSION['user_data']['userid']? $_SESSION['user_data']['userid'] : GO_UID_DEFAULT;
$data['version_code'] = $_GET['version_code'];
$data['status'] = 1;

$url = $_SERVER['HTTP_REFERER'];
if(in_array(5,$report_type) && empty($data['content'])){
	echo <<<html
    <script>
        alert('请输入具体原因');
		window.location.href="{$url}";
    </script>
html;
exit;
}
foreach($report_type as $key => $val){

	if(!empty($val)){
		$data['feedbacktype'] = (int)$val;
		$result = $feedback_logic->post_feedback($data);
	}
}
if($_POST['softid'] == 1111){
if ($result >= 0) {
	$arr['msg']='举报成功!';
	$arr['url']=$url;
	exit(json_encode($arr));
    echo <<<html
    <script>
        alert('举报成功');
        window.location.href="{$url}";
    </script>
html;
}
}else{
if ($result >= 0) {
    echo <<<html
    <script>
        alert('举报成功');
        window.location.href="{$url}";
    </script>
html;
}
}