<?php
ini_set("memory_limit", "1024M");
set_time_limit(1000);
define('DS', DIRECTORY_SEPARATOR);
define('GO_APP_ROOT', dirname(realpath(__FILE__)));
define('GO_CONFIG_DIR', GO_APP_ROOT. DS. 'Conf');
define('GO_HELPER_DIR', GO_APP_ROOT. DS. '..'. DS. 'newgomarket.goapk.com'. DS. 'helper');
include_once GO_APP_ROOT. DS. '..'. DS. 'GoPHP'. DS. 'Startup.php';
//$server = 'master';
$gongshi = trim($_POST['gongshi']); 
$gongshi = str_replace(' ','',$gongshi);
preg_match_all("/^([v|m|c|r]|[*|\-|+|\/]|[\(\)])+?$/i",$gongshi,$match);
if($_POST['gongshi'] && !$match[0]){
 $jump = "window.self.location=soft_score_rank.php;";
 echo "请根据提供的公式组合!";
 exit;
}
if(!$_POST) exit;
//Rank = (v/(v+m))*R + (m/(v+m))*C
$gongshi = str_replace('v', '$v',$gongshi);
$gongshi = str_replace('m', '$m',$gongshi);
$gongshi = str_replace('R', '$R',$gongshi);
$gongshi = str_replace('C', '$C',$gongshi);
$model = new GoModel();
$option = array('table' => 'sj_soft_comment','where' => array('status' => 1), 'field' => 'sum(score) scoresum,count(userid) usercount');
$result = $model -> findOne($option);
$C =  $result['scoresum'] / $result['usercount'];
$sql = "select ss.softname,ss.package,sum(ssc.score) scoresum,count(ssc.userid) usercount from sj_soft_comment as ssc left join sj_soft as ss on ssc.softid = ss.softid where ss.status = 1 and ssc.status = 1 group by ssc.softid";
$source = $model -> query($sql);
/* Rank = (v/(v+m))*R + (m/(v+m))*C
Rank = 排行总分
星级评分
分为10档，为1~10的整数
R = 软件A的平均评分（即为软件A的所有评分之和 除以 参与软件A评分的用户总数）
C = 安智市场所有软件的平均评分（即为所有软件的评分之和 除以 所有参与评分的用户总数）
v = 软件A的评分人数
m为调整基数（假设软件A最低参与评分人数），该值暂定为10，将根据结果进行调整。
按照此规则进行排行之后，参与评分人数低于10的软件将不再排行榜中出现。 */
$m = $_POST['m'] ? $_POST['m'] : 10;
while($result = $model -> fetch($source)){
$R = $result['scoresum'] / $result['usercount'];
$v = $result['usercount'];
$rank = 0;
//$rank = ($v/($v+$m))*$R + ($m/($v+$m))*$C;
eval("\$rank = ${gongshi};");
if($m<=$v){
$rank_arr_mv[$result['softname']]['rank'] = $rank; 
$rank_arr_mv[$result['softname']]['v'] = $v;
$rank_arr_mv[$result['softname']]['R'] = $R;

}
if($v<$m){
$rank_arr_vm[$result['softname']]['rank'] = $rank; 
$rank_arr_vm[$result['softname']]['v'] = $v;
$rank_arr_vm[$result['softname']]['R'] = $R;
}
}
arsort($rank_arr_mv);
arsort($rank_arr_vm);
?>
<table>
<tr><td>v大于等于m</td><td>v小于m</td></tr>
<tr>
<td valign="top">
<div>
<table border=1>
<tr><td>rank</td><td>softname</td><td>value</td><td>v</td><td>R</td></tr>
<?php
foreach($rank_arr_mv as $softname =>$value){
?>
<tr><td><?php echo ++$i;?></td><td><?php echo $softname;?></td><td><?php echo $value['rank'];?></td><td><?php echo $value['v'];?></td><td><?php echo $value['R'];?></td></tr>
<?php
}
?>
</table>
</div>
</td>
<td  valign="top">
<div>
<table border=1>
<tr><td>rank</td><td>softname</td><td>value</td><td>v</td><td>R</td></tr>
<?php
foreach($rank_arr_vm as $softname =>$value){
?>
<tr><td><?php echo ++$a;?></td><td><?php echo $softname;?></td><td><?php echo $value['rank'];?></td><td><?php echo $value['v'];?></td><td><?php echo $value['R'];?></td></tr>
<?php
}
?>
</table>
</div>
</td>
<tr>
</table>