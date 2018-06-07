<?php
//exit("服务器维护中！");
include_once ("init.php");
ini_set("memory_limit", "512M");
ini_set("display_errors", 1);
error_reporting(E_ERROR);
//$softObj = load_model("sjsoft");
$softObj = new GoModel();
//$db_gostatic = 'gostatics';
$db_gostatic = 'gostats';
$pw = "";
if (array_key_exists('password', $_GET)) $pw = $_GET['password'];
if (array_key_exists('password', $_POST)) $pw = $_POST['password'];
if (array_key_exists('username', $_GET)) $username = $_GET['username'];
if (array_key_exists('username', $_POST)) $username = $_POST['username'];
if ($pw) {
$map = $softObj->findOne( 
	array(
		'table' => 'sj_channel',
		'where' => array(
			'checkname' =>$username ,
			'checkpassword' =>$pw,
			'status' => 1,
		)
	)
);
	if ($map) {
		$channel_id = $map['chl'];
		if ($map[$pw]['alias']) {
			$alias = json_decode($map['alias'], true);
			$alias[] = $channel_id;
			$channel_id = $alias;
		}
		$_SESSION["CHANNEL_ID"] = $map['cid'];
		$_SESSION["chname"] = $map['chname'];
		$_SESSION["CHANNEL_NAME"] = $map['checkname'];
		$_SESSION["CHANNEL_PW"] = $pw;
		
		$h = date('H');
		pu_load_model_obj('pu_log',array('logfile' => "permanent_log_one".$h.".json",'message' => json_encode(array('username' => $username,'login_tm' => date('Y-m-d H:i:s'))
		))) ->save_data_info();

		header("content-type:text/html; charset=utf-8");
		header("Location: /statics.php");
	}
	$msg = "此用户找不到";
} else {
	$msg = "";
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<title> 安智网 - 对外合作数据查询系统</title>
		<meta name="author" content="liruqi">
		<link rel="archives" title="安智网" href="http://www.goapk.com/">

		<!--<link type="text/css" rel="stylesheet" href="http://60.29.241.148/xncooperation/css/xtree.css">
		<link type="text/css" rel="stylesheet" href="http://60.29.241.148/xncooperation/css/filelist.css">
		<link href="http://60.29.241.148/xncooperation/css/css1.css" rel="stylesheet" type="text/css">
		<script language=javascript type="text/javascript" src="http://60.29.241.148/xncooperation/js/prototype.js"></script>-->
		<style type="text/css">
		form { display:inline; }
		.logobox{width:700px;background-color:#f4f1f1;height:435px; margin:20px auto;padding-top:15px;}
		.ibox{width:650px;height:395px; margin:0 auto; border:5px solid #cfd3c8; background-color:#FFFFFF;}
		.headline{width:100%;height:58px; background:url(/images/bg.png) repeat-x scroll transparent; border-bottom:1px solid #c0bfbf;line-height:58px;color:#474545; font-family:"微软雅黑";font-weight:bold;}
		.headline span{display:block; float:left;padding-left:50px;font-size:30px; }
		.headline a{display:block; float:right;padding-right:50px;font-size:18px;color:#474545;}
		.table{padding:20px 0 0 50px;}
		#dl{height:46px;width:116px; background:url(/images/bg.png) center repeat-x scroll transparent; border:1px solid #c0bfbf;}
		#txt1{font-size:18px; color:#474545;}
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
<?php
if ($_SESSION["CHANNEL_ID"]) {
	$cid =$_SESSION["CHANNEL_ID"];
	$list = $softObj->findAll( 
	array(
		"table" => "activation_coefficient_state",
		"where" => array(
			"cid" => $cid,
			"status" =>1
		)
	),
	$db_gostatic);
	$day_cnt = array();
	$all_year = array();
	$all_month = array();
	$this_year = "0";
	$this_month = "0";
	$use_default = true;
	if (array_key_exists('month', $_POST)) {
		$use_default = false;
		$this_month = $_POST['month'];
	}
	if (array_key_exists('year', $_POST)) {
		$this_year = $_POST['year'];
	}
	foreach ($list as $idx => $info) {
		$submit_tm = date('Y-m-d H:i:s',$info["submit_tm"]);
		$day = explode(" ", $submit_tm);
		$ymd = explode("-", $day[0]);
		$all_year[$ymd[0]] = true;
		$all_month[$ymd[1]] = true;
		if ($use_default) {
			$this_month = $ymd[1];
			$this_year = $ymd[0];
		}
	}
	$total_count = 0;
	foreach ($list as $idx => $info) {
		$submit_tm = date('Y-m-d H:i:s',$info["submit_tm"]);
		$day = explode(" ", $submit_tm);
		$ymd = explode("-", $day[0]);
		if ($this_month != $ymd[1]) continue;
		if ($this_year != $ymd[0]) continue;
		if (!array_key_exists($day[0], $day_cnt)) $day_cnt[$day[0]] = 0;
		$day_cnt[$day[0]] = $info["counts"];
		$total_count+= $info["counts"];
	}
	$all_month=array();
	$now_month = date("m");
	for($i=(int)$now_month;$i>=(int)$now_month-3;$i--){
		if($i<10){
			$key="0".$i;
		}else{
			$key=$i;
		}
		$all_month[$key] = 'true';
	}
	//$all_month=array('01'=>'true','02'=>'true','03'=>'true','04'=>'true','05'=>'true','06'=>'true','07'=>'true','08'=>'true','09'=>'true','10'=>'true','11'=>'true','12'=>'true');
?>
<body topmargin="0" leftmargin="0" width="100%">
<form name=item method=post>
<table width="1000" border=0 cellPadding=6 cellSpacing=0 align="center">
	<tr>
		<td width="45">用户名:</td>
		<td><? echo $_SESSION["CHANNEL_NAME"]; ?></td>
		<td width="30">渠道:</td>
		<td>
				<? echo $_SESSION["chname"]; ?>
		</td>
		<td width="80">
			<a href="/statics_logout.php">退出</a>
		</td>
	</tr>
</table>
<table width="1000" border=0 cellPadding=6 cellSpacing=0 align="center">
	<tr>
		<td width="75">按月份查询：</td>
		<td width="50">
			<select name="year" id="year">
				<option value="<?php echo date("Y")-2;?>" <?php if((date("Y")-2) == $this_year) echo "selected"?>><?php echo date("Y")-2;?></option>
				<option value="<?php echo date("Y")-1;?>" <?php if((date("Y")-1) == $this_year) echo "selected"?>><?php echo date("Y")-1;?></option>
				<option value="<?php echo date("Y");?>" <?php if((date("Y")) == $this_year) echo "selected"?>><?php echo date("Y");?></option>
			</select>
		</td>
		<td width="5">年</td>
		<td width="20">
			<select name="month" id="month">
				<?php
					//foreach ($all_month as $month => $val) {
					//    $selected = "";
					//    if ($this_month == $month) $selected = "selected";
					//    echo "<option value=\"{$month}\" $selected>{$month}</option>";
					//}
					for($ii=1;$ii<=12;$ii++){
						$selected = '';
						if ((int)$this_month == (int)$ii) $selected = "selected";
						
						$key=$ii;
						if($ii<10){
							$key="0".$ii;
						}
						
						echo "<option value=\"{$key}\" $selected>{$key}</option>";
					}
				?>
			</select>
		</td>
		<td width="5">月</td>
		<td><input name=submit type="submit" value="查询"></td>
	</tr>
</table>
</form>
<table width="1000" class="td3" border=0 cellpadding=2 cellspacing=0 align="center">
	<tr class="td5">
		<td width="100">日期</TD>
		<td width="100"> <a href="#" title="单个IMEI第一次激活">有效激活数</a></TD>
	</tr>
	<?php
		ksort($day_cnt);
		$color = 0;
		foreach ($day_cnt as $day => $cnt) {
			$id = $color;
			$color = $color ^ 1;
			$id+= 3;
			$html = "<tr class=\"td{$id}\"> <td>$day</TD> <td>$cnt</TD> </tr> ";
			echo $html;
		}
	?>
	<tr class="td5"> <td></td> <td>总数：<? echo $total_count; ?></td> </tr>
</table>

<?php
} else {
if ($_GET['fromaz'] != 666) {
	exit("服务停止，有疑问请咨询商务！");
}
?>
  <div class="logobox">
	<div class="ibox">
		 <form id="login" name="login" method="post" action="/statics.php">

	<div class="headline"><span>查询登录</span><a href="http://www.goapk.com">返回首页</a></div>
	<div class="table">
	  <table width="594" border="0" cellpadding="0" cellspacing="0">
		<tr>
		  <td>&nbsp;</td>
		  <td>&nbsp;</td>
		  <td colspan="1" height="50" style="color:#fe0000; font-size:18px;"><?
	echo $msg;
	?>
</td>
		</tr>
		<tr>
		  <td width="110" rowspan="2"><img src="/images/logo.png" /></td>
		  <td width="100" height="60" id="txt1">用户名：</td>
		  <td><input name="username" id="username" class="text" type="text" /></td>
		</tr>
		<tr>
		  <td width="100" height="60"  id="txt1">密  码：</td>
		  <td><input name="password" id="password" class="text" type="password" /></td>
		</tr>
		<tr>
		  <td colspan="2">&nbsp;</td>
		  <td><input name="submit2" type="submit" class="bn-submit" id="dl" value="登录" /></td>
		</tr>
	  </table>
	</div>
	</form>
<?php }?>
	<div class="item2" style="text-align:center;height:60px; line-height:60px;">
  © 2010 安智网
	</div>
	</div>

  </div>

