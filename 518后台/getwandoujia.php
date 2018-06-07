<?php 
$now_date = date('Y-m-d');
$start_date = isset($_POST['fromdate']) ? $_POST['fromdate'] : $now_date;
$end_date = isset($_POST['todate']) ? $_POST['todate'] : $now_date;

$start_time = strtotime($start_date);
$end_time = strtotime($end_date);
?>

<link rel="stylesheet" type="text/css" media="all" href="/Public/js/calendar-win2k-cold-1.css" title="win2k-cold-1" />
<script type="text/javascript" src="/Public/js/calendar_bak.js"></script>
<script type="text/javascript" src="/Public/js/calendar-zh.js"></script>
<script type="text/javascript" src="/Public/js/calendar-setup.js"></script>
<script type="text/javascript" src="/Public/js/result.js"></script>
<script type="text/javascript" src="/Public/js/action.js"></script>

<form method="post" action="/getwandoujia.php">选择查看日期(双击日期确定)： <span
	id="WebCalendar3"
	style="border: 1px solid #7F9DB9; align: absmiddle; cursor: hand; display: inline-block; width: 124px; padding: 1px">
<input id="fromdate" name="fromdate"
	style="cursor: hand; width: 100px; border: none 0px black;"
	value="<?php echo $start_date?>" size="15" type="text"><img
	src="/Public/js/calendar.gif"
	onclick="return showCalendar('fromdate', 'y-m-d');"
	style="margin: 1px; cursor: hand;" width="16px" align="absmiddle"
	height="15px"></span> <span id="WebCalendar3"
	style="border: 1px solid rgb(127, 157, 185); padding: 1px; display: inline-block; width: 124px;">

<input id="todate" name="todate"
	style="border: 0px none black; width: 100px;" value="<?php echo $end_date?>"
	size="15" type="text"><img src="/Public/js/calendar.gif"
	onclick="return showCalendar('todate', 'y-m-d');"
	style="margin: 1px;" width="16px" align="absmiddle" height="15px">
</span> <input type="submit" name="submit" value="确定"
	style="height: 22px; vertical-align: middle;" /></form>

<div id="wrap" class="wrap with_side s_clear">

<?php 

//$start = strtotime($_POST['start']);
//$end = strtotime($_POST['end']);
$logs = array(
	"http://192.168.1.114:81/www.goapk.com/",
	"http://192.168.1.115:81/www.goapk.com/",
	"http://192.168.1.84:81/www.goapk.com/",
);
for ($i=$start_time;$i<=$end_time;$i=$i+86400) {
	$day = date('Y-m-d', $i);
	
	for ($j=0;$j<24;$j++){
		$hour = sprintf('%02d', $j);
		$file = $day. '/parter_'. $hour. '.json';
		foreach($logs as $log) {
			$fp = fopen($log. $file, "r");
			if ($fp) {
				while (!feof($fp)) {
					$line = fgets($fp);
					if (strlen($line) == 0)
					continue;
					$json = json_decode($line, true);
					if (!array_key_exists('package', $json)||$json["action"]!="wandoujia") {
						continue;
					}
					$download_data[$day] += 1;
				}
				fclose($fp);
			}
		}
	}	
}
foreach ($download_data as $day_key => $value) {
	echo "{$day_key} : {$value}<br>";
}
?>
</div>