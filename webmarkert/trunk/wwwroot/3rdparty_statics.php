<?php
//?fromdate=2012-1-1&todate=2012-01-09&c=209&submit=确定
define('DS', DIRECTORY_SEPARATOR);
define('GO_APP_ROOT', dirname(realpath(__FILE__)));
define('GO_CONFIG_DIR', GO_APP_ROOT. DS. '..'. DS. 'newgomarket.goapk.com'. DS. 'config');
define('GO_HELPER_DIR', GO_APP_ROOT. DS. '..'. DS. 'newgomarket.goapk.com'. DS. 'helper');
include_once GO_APP_ROOT. DS. '..'. DS. 'GoPHP'. DS. 'Startup.php';
date_default_timezone_set('Asia/Shanghai');

$softObj = load_model('sjsoft');
ini_set("memory_limit", "512M");
ini_set("display_errors", 1);
error_reporting(E_ERROR);

session_start();

$self = basename(__FILE__);

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
        echo "<script>alert('无访问权限！')</script>";
       header("Location:/3rdparty.php");
    } else {
        $result = $result[0];
        $_SESSION['3party_username'] = $username;
        $_SESSION['capability'] = explode(',', $result['cid_collect']);
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
		<meta name="author" content="master">
		<link rel="archives" title="安智网" href="http://www.anzhi.com/">
		<!-- 
        <link type="text/css" rel="stylesheet" href="http://60.29.241.148/xncooperation/css/xtree.css">
        <link type="text/css" rel="stylesheet" href="http://60.29.241.148/xncooperation/css/filelist.css">
        <link href="http://60.29.241.148/xncooperation/css/css1.css" rel="stylesheet" type="text/css">
        <script language=javascript type="text/javascript" src="http://60.29.241.148/xncooperation/js/prototype.js"></script>
         -->
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
function changeColor(obj){
    //background-color
  obj.className="color";
}
function clearColor(obj){
  obj.className="clearColor";
}
</script>
<link rel="stylesheet" type="text/css" media="all" href="js/calendar-win2k-cold-1.css" title="win2k-cold-1" />
<script type="text/javascript" src="js/calendar_bak.js"></script>
<script type="text/javascript" src="js/calendar-zh.js"></script>
<script type="text/javascript" src="js/calendar-setup.js"></script>
</head>
<body>
<?php

if (isset($_SESSION['3party_username'])) {

$model = new GoModel();
$db = 'master';
//$gostatics_db = 'gostatics';
$gostatics_db = 'gostats';
$chl = isset($_REQUEST['c']) ? $_REQUEST['c'] : -1;
if ($chl == -1 || !in_array($chl, $_SESSION['capability']))
    $chl = $_SESSION['capability'];
$time_start = 0;
$time_end = time();
//时间处理
function days_away($date, $away=0) {
    $date_array = explode('-',$date);
    $dst_time  = mktime(1, 1, 1, $date_array[1], $date_array[2], $date_array[0]);
    $today = getdate($dst_time);
    $dt = getdate(mktime(1, 1, 1, $today['mon'], $today['mday']+$away, $today['year']));
    return $dt['year'].'-'.sprintf("%02d" ,$dt['mon']).'-'.sprintf("%02d",$dt['mday']);
}
function short_date($date) {
    $date_array = explode('-',$date);
    return $date_array[1].'/'.$date_array[2];
}
#增加月份和日期的前置
function fix_date($date) {
    $date_array = explode('-',$date);
    return $date_array[0].'-'.sprintf("%02d",intval($date_array[1])).'-'.sprintf("%02d",intval($date_array[2]));
}
$global_max_p = 8;
$to_value = strftime("%G-%m-%d", time());                  //转化时间格式
if (array_key_exists('todate', $_GET))
    $to_value = $_GET['todate'];
$to_value = days_away($to_value, 0);                       //获取截止时间
$from_value = days_away($to_value, 1-$global_max_p);
if (array_key_exists('fromdate', $_GET))
    $from_value = $_GET['fromdate'];                       //获取起始时间
$date_array = explode('-',$from_value);
$from_sec  = mktime(1, 1, 1, $date_array[1], $date_array[2], $date_array[0]);
$date_array = explode('-',$to_value);
$to_sec = mktime(1, 1, 2, $date_array[1], $date_array[2], $date_array[0]);
$days = round(($to_sec-$from_sec)/(24*3600))+1;
$max_points = $global_max_p;
if ($days < $max_points)
    $max_points = $days;
$step=floor($days/$max_points);

function formdate($data){
    $data_arr = explode('-',$data);
    if(strlen($data_arr[1]) < 2){
      $data_arr[1] = '0'.$data_arr[1];
    }
    if(strlen($data_arr[2]) < 2){
      $data_arr[2] = '0'.$data_arr[2];
    }
    return implode('-',$data_arr);
}
$fromdate = isset($_GET['fromdate']) ? $_GET['fromdate'] : date("Y-m-d",time()-7*24*3600);
$todate = isset($_GET['todate']) ? $_GET['todate'] : date("Y-m-d",time());
$datequery = "&fromdate=${fromdate}&todate=${todate}";
$time_start = strtotime(formdate($fromdate));
$time_end   = strtotime(formdate($todate)." 23:59:59");

$sql = "select chl,device,sum(apps) as apps,sum(games) as games from mobile_chl_soft_stat where date >= ${time_start} and date < ${time_end}";
if (is_array($chl))
    $sql .= " and chl in (". implode(',', $chl). ") group by chl";
else
    $sql .= " and chl=${chl} group by device";
$sql .= " order by apps desc,games desc limit 50;";
$data = false;
if($_GET['test']) echo $sql;
$rc = $model->query($sql, $gostatics_db);
//echo $model->getSql();
if ($rc) {
    $data = array();
    while ($row = $model->fetch($rc)){
        $data[] = $row;
	}
}
?>
<div class="top"><span>用户名：</span><span><?php echo $_SESSION["3party_username"]; ?></span><span>   <a href="/logout_3rd.php">退出</a></span></div>
<div class="qd"><span><b>渠道数据统计:</b></span><span><a href="/3rdparty.php">返回</a></span></div>
<div id="titletable">
    <table><tr><td>
    <form method="get" action="3rdparty_statics.php">
    选择查看日期(双击日期确定)：
    <span id="WebCalendar3" style="border:1px solid #7F9DB9;align:absmiddle;cursor:hand;display:inline-block;width:124px;padding:1px">
<?php
echo '<input id="fromdate" name="fromdate" style="cursor:hand;width:100px; border:none 0px black;" value="' . $from_value . '" size="15" type="text"><img src="js/calendar.gif" onclick="return showCalendar(\'fromdate\', \'y-m-d\');" style="margin:1px;cursor:hand;" width="16px" align="absmiddle" height="15px">';
?>
</span>
<span id="WebCalendar3" style="border: 1px solid rgb(127, 157, 185); padding: 1px; display: inline-block; width: 124px;">
<?php
echo '<input id="todate" name="todate" style="border: 0px none black; width: 100px;" value="' . $to_value . '" size="15" type="text"><img src="js/calendar.gif" onclick="return showCalendar(\'todate\', \'y-m-d\');" style="margin: 1px;" width="16px" align="absmiddle" height="15px">';
?>
    <?php if(isset($_GET['c'])){?><input type='hidden' name='c' value='<?php echo $_GET['c']?>'><?php }?>
    </span>
    <input type="submit" name="submit" value="确定" style="height:22px; vertical-align:middle;"/>
    </form>
    </td></tr></table>
</div>
<?php
$option = array(
    'table' => 'sj_channel',
    'where' => array('cid' => $_SESSION['capability'], 'status' => 1),
    'field' => array('cid', 'chname'),
    'index' => 'cid',
);
$channels = $model->findAll($option);

$option = array(
    'table' => 'pu_device',
    'field' => array('did', 'dname'),
    'where' => array('status' => 1),
    'index' => 'did',
);
$devices = $model->findAll($option);
$activated = array();
//$gostatics_db = "sj_channel_coefficient_state";
$gostatics_table = 'activation_coefficient_state';
if (is_array($chl)) {
    $sql = "select sum(counts) as count,cid from ".$gostatics_table." where submit_tm>=${time_start} and submit_tm <${time_end} and status=1 and cid in (". implode(',', $chl). ") group by cid;";

    $rc = $model->query($sql,$gostatics_db);
    if ($rc) {
        while ($row = $model->fetch($rc))
            $activated[$row['cid']] = $row;
    }
}
?>
<table border="0" cellpadding="0" cellspacing="0" class="ctable">
<?
if (is_array($chl)) {
?>
<tr><th width="25%">渠道</th><th width="25%">激活量</th><th width="25%">应用下载量</th><th width="25%">游戏下载量</th></tr>
<?
}
else {
$chname = $channels[$chl]['chname'];
?>
<tr><td width="40%">查看渠道:<?php echo $chname;?><a href="/3rdparty_statics.php">返回</a></td><td width="30%"></td><td width="30%"></td></tr>
<tr><th width="40%">机型</th><th width="30%">应用下载量</th><th width="30%">游戏下载量</th></tr>
<?
}
?>
<?php
$i=0;
if(is_array($chl)){
    $dl_data = array();
    foreach($activated as $cid => $val){
        $dl_data[$cid]['count'] = $val['count'];
		$dl_data[$cid]['chl'] = $cid;
    }
    foreach($data as $val){
       $dl_data[$val['chl']]['apps'] =$val['apps'];
       $dl_data[$val['chl']]['games'] =$val['games'];
       $dl_data[$val['chl']]['chl'] =$val['chl'];
    }
    $data = $dl_data;
}

foreach ($data as $val) {
   $i++;
echo "<tr id='".$i."' onmouseout=\"clearColor(this)\" onmouseover=\"changeColor(this)\">";
if (is_array($chl)) {
$cid = $val['chl'];
$chname = $channels[$cid]['chname'];
if (empty($chname)) $chname = "未知";
echo "<td =\"id\"><a href=\"/${self}?c=${cid}${datequery}\">${chname}</a></td>";
//$count = $activated[$cid]['count'];
echo "<td>${val['count']}</td>";
}
else {
$did = $val['device'];
$dname = $devices[$did]['dname'];
if (empty($dname)) $dname = "未知";
echo "<td>${dname}</td>";
}
echo "<td>${val['apps']}</td>";
echo "<td>${val['games']}</td>";
echo "</tr>";
}
?>
</table>
<br>
<?
}
else {
	echo "<script>alert('您还未登录！请登录')</script>";
	header("Location:/3rdparty.php");
}

?>
</body>
<?php
include ('../view/inc/footer.php');
?>
