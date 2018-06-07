<?php
exit("系统维护中");
ini_set("display_errors", true);
error_reporting(E_ERROR);
ini_set("memory_limit", "512M");
define('DS', DIRECTORY_SEPARATOR);
define('GO_APP_ROOT', dirname(realpath(__FILE__)));
define('GO_CONFIG_DIR', GO_APP_ROOT. DS. '..'. DS. 'newgomarket.goapk.com'. DS. 'config');
define('GO_HELPER_DIR', GO_APP_ROOT. DS. '..'. DS. 'newgomarket.goapk.com'. DS. 'helper');
include_once GO_APP_ROOT. DS. '..'. DS. 'GoPHP'. DS. 'Startup.php';
date_default_timezone_set('Asia/Shanghai');
$softObj = load_model('sjsoft');
session_start();
$self = basename(__FILE__);
$pagesize = 50;
$pagestart = isset($_REQUEST['start']) ? $_REQUEST['start'] : 0;
$username = isset($_REQUEST['username']) ? $_REQUEST['username'] : '';
$password = isset($_REQUEST['password']) ? $_REQUEST['password'] : '';
if ($password) {
    # XXX: typo
    $result = $softObj->getDataList('partner_user', array(
        'where' => array(
            'username' =>$username ,
            'passwd' =>$password,
            'status' => 1,
        )
    ));
    if (!$result) {
      $msg = "无权访问";
    } else {
        $result = $result[0];
        $_SESSION['3party_username'] = $username;
        $_SESSION['capability'] = explode(',', $result['cid_collect']);
		$h = date('H');
		pu_load_model_obj('pu_log',array('logfile' => "permanent_log_two".$h.".json",'message' => json_encode(array('username' => $username,'login_tm' => date('Y-m-d H:i:s'))
		))) ->save_data_info();
		
        header("content-type:text/html; charset=utf-8");
        header("Location: /${self}");
    }
}else{
     $msg = "";
}
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<title> 安智网 - 对外合作数据查询系统</title>
		<meta name="author" content="liruqi">
		<link rel="archives" title="安智网" href="http://www.anzhi.com/">
        <style type="text/css">
        form { display:inline; }
		.ctable{border-top:1px solid #666666; border-left:1px solid #666666;width:100%;}
		.ctable th,.ctable td{border-right:1px solid #666666;border-bottom:1px solid #666666;}
		.ctable th{ background-color:#44a0c2;}
		.date{font-size:10px; color:#CCCCCC;}
		.id{color:#fe0000;}
        .color{background-color:#44a0c0;}
        .clearColor{background-color:#FFFFFF;}
		.qd,.top{height:30px; line-height:30px;width:100%; overflow:hidden; font-size:12px;}
		.qd{ text-align:left;}
		.top{ text-align:right;}
		.qd span,.top span{ padding-right:20px;}
        </style>
<script language="JavaScript" type="text/javascript">
function openWindow(str1,str2,str3){
  window.open(str1,str2,str3);
}
function logout()
{
    new Ajax.Request('logout.php', {
      method: 'get',
      onSuccess: function(transport)
      {
        window.close();
      },
      onFailure: function(){ window.close();}
      });
}
</script>
</head>
<body>
<?php

if (isset($_SESSION['3party_username'])) {
?>
<!--首页-->
<link rel="stylesheet" type="text/css" href="/css/style.css" />
<div id="header">
  <h1 class="headerTitle">
    <span>安智网合作方查阅平台</span>
  </h1>
</div>

<?php

$page_list = array('渠道数据统计'=>'/3rdparty_statics.php','渠道信息反馈'=>'/3rdparty_feedback.php');
echo '<div class="leftSideBar">'."\n";
echo '<div id="menu"> <ul>'."\n";
foreach($page_list as $text => $url) {
    echo '<li><a href="'.$url.'">'.$text.'</a>'."\n";
}
echo '</ul> </div>'."\n";
echo '</div>'."\n";
?>
<div id="footer">
 <a align="right" href="/logout.php">退出</a>
 <a href="http://www.anzhi.com/" target="_blank">安智网</a>
</div>
<?php
} else {
?>
    <form id="login" name="login" method="post" action="/<?php  echo $self;?>">
    <fieldset style="width:300px; height:120px; background-color:#33CCFF; margin:0 auto; text-align:center; padding-top:20px;">
    <div class="item1" style="margin-bottom:10px;">登录</div>
    <div class="item1" style="margin-bottom:10px;">
    <label for="username">用户名: </label>
    <input name="username" id="username" class="text" type="text">
    </div>
    <div class="item1" style="margin-bottom:10px;">
    <label for="password">密　码: </label>
    <input name="password" id="password" class="text" type="password">
    </div>

    <div class="item2">
    <input value="登录" type="submit" class="bn-submit">
    </div>
    </fieldset>

    </form>
<?php
        if($msg!="") echo "<font color='red'>".$msg."</font>";
}
?>
</body>
<?php
include ('../view/inc/footer.php');
?>
