<?php
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

$update_flag = false;
$update_msg = '';

if (isset($_REQUEST['check']) && isset($_SESSION['username'])) {
    $update_flag = true;
    $feedbackid = $_REQUEST['check'];
    $model = new GoModel();
    $option = array(
        'table' => 'sj_feedback',
        'where' => array(
            'cid' => $_SESSION['capability'],
            'feedbackid' => $feedbackid,
        ),
    );
    $result = $model->findOne($option);
    if ($result) {
        if($result['operate_status'] != 2){
        $operate_status = $result['operate_status'];
        $operate_status |= 2;
        $result = $model->query("update sj_feedback set operate_status=${operate_status} where feedbackid=${feedbackid};");
        $update_msg = $result ? "删除成功。" : "删除失败。";
        if($result){
           echo "<script>alert('".$update_msg."')</script>";
          header("content-type:text/html; charset=utf-8");
          header("Location: /3rdparty_feedback.php");
        }
        }
    }
    else {
        $update_msg = '您无权更改状态。';
    }
}
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<title> 安智网 - 对外合作数据查询系统</title>
		<meta name="author" content="liruqi">
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
function checkit()
{
     var r = confirm("确定要这么执行该操作吗？");
     return r;
}
</script>
</head>
<body>
<?php
if (isset($_SESSION['3party_username'])) {

$model = new GoModel();
$rc = $model->query("select count(*) as count from sj_feedback where cid in (". implode(',', $_SESSION['capability']). ")  and operate_status in (0,1) and softid = 0;");
$data = $model->fetch($rc);
$count = isset($data['count']) ? $data['count'] : 0;
//分页配置
    $page = $_REQUEST['page'] ? $_REQUEST['page'] : 0;
    $targetpage = $_SERVER["PHP_SELF"].'?';
    $start = $page*20 ? ($page-1)*20:$page*20;
    $num   = 20;
    $totalRows = $count;

    if ($page == 0) $page = 1;
    $limit = 20;
    $adjacents = 3;
//end

$option = array(
    'table' => 'sj_feedback AS A',
    //'offset' => isset($_REQUEST['start']) ? isset($_REQUEST['start']) : $pagestart,
     'offset' => $start,
    //'limit' => isset($_REQUEST['size']) ? isset($_REQUEST['size']) : $pagesize,
     'limit' => $limit,
    'where' => array(
        'A.cid' => $_SESSION['capability'],
        'A.status' => 1,
        'A.operate_status' => array("0", "1"),
        'A.softid' => 0,
    ),
    'join' => array(
        'pu_device AS B' => array(
            'on' => array('A.did', 'B.did'),
        ),
        'sj_channel AS C' => array(
            'on' => array('A.cid', 'C.cid'),
        ),
        'sj_soft AS D' => array(
            'on' => array('A.softid', 'D.softid'),
        ),
    ),
    'field' => array('A.feedbackid', 'A.softid', 'A.feedbacktype', 'A.content', 'A.submit_tm', 'A.version_code', 'B.dname', 'C.chname', 'D.softname', 'D.version'),
    'order' => 'A.submit_tm DESC',
);
$data = $model->findAll($option);
?>
<div class="top"><span>用户名：</span><span><?php echo $_SESSION["3party_username"]; ?></span><span>   <a href="/logout_3rd.php">退出</a></span></div>
<div class="qd"><span><b>渠道信息反馈:</b></span><span><a href="/3rdparty.php">返回</a></span></div>
<?php

if ($data) {
?>
<center>
<table border="0" cellpadding="0" cellspacing="0" class="ctable">
<tr><th width="5%">ID</th><th width="15">渠道</th><th width="60%">内容</th><th width="15%">时间</th><th width="5%">操作</th></tr>
<?php
 $i=0;
foreach ($data as $val) {
    $i++;
echo "<tr id=\"".$i."\" onmouseout=\"clearColor(this)\" onmouseover=\"changeColor(this)\">";
echo "<td class='id'>${val['feedbackid']}</td>";
echo "<td>${val['chname']}</td>";
echo "<td>${val['content']}</td>";
$time_str = date('Y-m-d H:i:s', $val['submit_tm']);
echo "<td class='date'>${time_str}</td>";
if (isset($_REQUEST['start']))
    echo "<td><a href=\"/${self}?start=${_REQUEST['start']}&check=${val['feedbackid']}\" onclick=\"return checkit();\">删除</a></td>";
else
    echo "<td><a href=\"/${self}?check=${val['feedbackid']}\" onclick=\"return checkit();\">删除</a></td>";
echo "</tr>";
}
?>
</table>
<br>
<?php
echo  pagenation($page,$totalRows,$limit,$targetpage,$adjacents);
?>
</center>
<?php
}
else {
    echo "未找到记录。";
}
} else {
?>
    <form id="login" name="login" method="post" action="/<?php  echo $self;?>">
    <fieldset style="width:300px; height:100px; background-color:#33CCFF; margin:0 auto; text-align:center; padding-top:20px;">

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
function pagenation($page, $totalRows, $limit, $targetpage, $adjacents){
     	/**
     	 * Now we apply our rules and draw the pagination object.
     	 * We're actually saving the code to a variable in case we want to draw it more than once.
     	 */
     	$lastpage = ceil($totalRows / $limit);
     	$prev = $page - 1; //previous page is page - 1
     	$next = $page + 1; //next page is page + 1
     	$lpm1 = $lastpage - 1;
     	$pagination = "";
     	if (strpos($targetpage, '?')){
     		$target_arr = explode('?', $targetpage);
     		$targetpage = $target_arr[0] . '?' . $target_arr[1] . '&';
     	}else{
     		$targetpage = $targetpage . '?';
     	}
     	$style = "<style>div.pagination {
    padding: 3px;
    margin: 3px;
}
div.pagination a {
    padding: 2px 5px 2px 5px;
    margin: 2px;
    border: 1px solid #AAAADD;
    text-decoration: none; /* no underline */
    color: #000099;
}
div.pagination a:hover, div.pagination a:active {
    border: 1px solid #000099;
    color: #000;
}
div.pagination span.current {
    padding: 2px 5px 2px 5px;
    margin: 2px;
    border: 1px solid #000099;

    font-weight: bold;
    background-color: #000099;
    color: #FFF;
}
div.pagination span.disabled {
    padding: 2px 5px 2px 5px;
    margin: 2px;
    border: 1px solid #EEE;
    color: #DDD;
}</style>";
     	if ($lastpage > 1){
     		$pagination .= "<div class=\"pagination\">";
     		// previous button
     		if ($page > 1) $pagination .= "<a href=\"$targetpage" . "page=$prev\">« previous</a>";
     		else $pagination .= "<span class=\"disabled\">« previous</span>";
     		// pages
     		if ($lastpage < 7 + ($adjacents * 2)){ // not enough pages to bother breaking it up
     			for ($counter = 1;$counter <= $lastpage;$counter++){
     				if ($counter == $page) $pagination .= "<span class=\"current\">$counter</span>";
     				else $pagination .= "<a href=\"$targetpage" . "page=$counter\">$counter</a>";
     			}
     		}elseif ($lastpage > 5 + ($adjacents * 2)){ // enough pages to hide some
     			// close to beginning; only hide later pages
     			if ($page < 1 + ($adjacents * 2)){
     				for ($counter = 1;$counter < 4 + ($adjacents * 2);$counter++){
     					if ($counter == $page) $pagination .= "<span class=\"current\">$counter</span>";
     					else $pagination .= "<a href=\"$targetpage" . "page=$counter\">$counter</a>";
     				}
     				$pagination .= "...";
     				$pagination .= "<a href=\"$targetpage" . "page=$lpm1\">$lpm1</a>";
     				$pagination .= "<a href=\"$targetpage" . "page=$lastpage\">$lastpage</a>";
     			}
     			// in middle; hide some front and some back
     			elseif ($lastpage - ($adjacents * 2) > $page && $page > ($adjacents * 2)){
     				$pagination .= "<a href=\"$targetpage" . "page=1\">1</a>";
     				$pagination .= "<a href=\"$targetpage" . "page=2\">2</a>";
     				$pagination .= "...";
     				for ($counter = $page - $adjacents;$counter <= $page + $adjacents;$counter++){
     					if ($counter == $page) $pagination .= "<span class=\"current\">$counter</span>";
     					else $pagination .= "<a href=\"$targetpage" . "page=$counter\">$counter</a>";
     				}
     				$pagination .= "...";
     				$pagination .= "<a href=\"$targetpage" . "page=$lpm1\">$lpm1</a>";
     				$pagination .= "<a href=\"$targetpage" . "page=$lastpage\">$lastpage</a>";
     			}
     			// close to end; only hide early pages
     			else{
     				$pagination .= "<a href=\"$targetpage" . "page=1\">1</a>";
     				$pagination .= "<a href=\"$targetpage" . "page=2\">2</a>";
     				$pagination .= "...";
     				for ($counter = $lastpage - (2 + ($adjacents * 2));$counter <= $lastpage;$counter++){
     					if ($counter == $page) $pagination .= "<span class=\"current\">$counter</span>";
     					else $pagination .= "<a href=\"$targetpage" . "page=$counter\">$counter</a>";
     				}
     			}
     		}
     		// next button
     		if ($page < $counter - 1) $pagination .= "<a href=\"$targetpage" . "page=$next\">next »</a>";
     		else $pagination .= "<span class=\"disabled\">next »</span>";
     		$pagination .= "</div>\n";
     	}
     	return $style . $pagination;
     }
?>
