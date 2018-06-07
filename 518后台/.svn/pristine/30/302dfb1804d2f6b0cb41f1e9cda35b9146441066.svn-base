<?php 
ini_set("memory_limit", "2000M");
error_reporting(E_ERROR);
ini_set("display_errors", true);
set_time_limit(1000);
define('DS', DIRECTORY_SEPARATOR);
define('GO_APP_ROOT', dirname(realpath(__FILE__)));
define('GO_CONFIG_DIR', GO_APP_ROOT. DS. 'Conf');
define('GO_HELPER_DIR', GO_APP_ROOT. DS. '..'. DS. 'newgomarket.goapk.com'. DS. 'helper');
include_once GO_APP_ROOT. DS. '..'. DS. 'GoPHP'. DS. 'Startup.php';
define('STATDB', 'slave');
$server = 'db30';
$model = new GoModel();
$gongshi = trim($_POST['gongshi']); 
//echo $gongshi;
preg_match_all("/^([A-Fa-f]|[*|\-|+|\/]|[\(\)])+?$/i",$gongshi,$match);
if($_POST['gongshi'] && !$match[0]){
echo "请根据提供的指标和权重组合公式!";
exit;
}

$gongshi = str_replace('（', '(', $gongshi);
$gongshi = str_replace('）', ')',$gongshi);
$gongshi = str_replace('A', '$A',$gongshi);
$gongshi = str_replace('B', '$B',$gongshi);
$gongshi = str_replace('C', '$C',$gongshi);
$gongshi = str_replace('D', '$D',$gongshi);
$gongshi = str_replace('E', '$E',$gongshi);
$gongshi = str_replace('F', '$F',$gongshi);
$gongshi = str_replace('a', '$a',$gongshi);
$gongshi = str_replace('b', '$b',$gongshi);
$gongshi = str_replace('c', '$c',$gongshi);
$gongshi = str_replace('d', '$d',$gongshi);
$gongshi = str_replace('e', '$e',$gongshi);
$gongshi = str_replace('f', '$f',$gongshi);
$a = $_POST['a'];
$b = $_POST['b'];
$c = $_POST['c'];
$d = $_POST['d'];
$e = $_POST['e'];
$f = $_POST['f'];
$option = array('table' => 'sj_soft','where' => array('status' => 1,'hide' => 1),'field' => 'softid,package,softname');
$softarr = $model -> findAll($option);
foreach($softarr as $info){
$softname_array[$info['package']] = $info['softname'];
$softid_array[$info['package']] = $info['softid'];
}
if($_POST['act'] == 'daily'){
	$fromdate = dateform(trim($_POST['fromdate']));
	$todate = dateform(trim($_POST['todate']));
	$limit = 3;
	$i = 0;
	for($start = strtotime($fromdate);$start <= strtotime($todate);$start+=3600*24){
	 if($i >= 3) break;
	 $time_arr[] = date('Ymd',$start);
	 $table_arr[] = date('Ymd',$start);
	 $where_time_arr[] = $start;	
	 $i++;
	}
}else if($_POST['act'] == 'weekey'){
	$fromdate = dateform(trim($_POST['fromdate']));
	$todate = dateform(trim($_POST['todate']));
	for($start = strtotime($fromdate);$start <= strtotime($todate);$start+=3600*24){
	 $time_arr[] = date('Ymd',$start);
	 $table_arr[] = date('Ymd',$start);
	 $where_time_arr[] = $start;		
	}
}else if($_POST['act'] == 'month'){
   $data = array();
   $currdate = dateform($_POST['currdate']);
   $currtime = strtotime($currdate);
   for($i=0;$i<30;$i++){
	 $date_arr[] = $currtime - $i*3600*24;
   }
   foreach($date_arr as $date){
   $sql = 'select * from sj_download_count where submit_day = '.$date.' and mob_dl_cnt <> 0 and mob_up_cnt <> 0';
   $source = $model -> query($sql, STATDB);
	   while($result = $model -> fetch($source)){
		  $rank_list[$result['package']] += $result['mob_dl_cnt']+$result['mob_up_cnt'];
	   }
   }
}
if(in_array($_POST['act'],array('daily','weekey'))){
	$result = array();
	foreach($where_time_arr as $where_time){
		$option = array('table' => 'sj_download_count','where' => array('submit_day' => $where_time));
		$result = $model -> findAll($option, STATDB);
		foreach($result as $info){
            if(!$softid_array[$info['package']]){
                continue;
            }
			$date = date('Y-m-d',$info['submit_day']);
			$softdl[$info['package']][$date] = $info['mob_dl_cnt']+$info['mob_up_cnt'];
			$softdl[$info['package']]['sum'] += $softdl[$info['package']][$date];
			$softdl_sum[$info['package']] += $softdl[$info['package']][$date];
		}//softdl以上是下载量源
	}
	$where = "";
	$where1 = "";
	if($_POST['mt1'] && $_POST['mt2']){
	$days = count($where_time_arr);
	$min = intval($_POST['mt1']);
	$max = intval($_POST['mt2']);
		foreach($softdl as $pkg => $info){
			if(($info['sum'] / $days)> $min && ($info['sum']/$days) <= $max){
				$info['average'] = $info['sum'] / $days;
				$soft_dl[$pkg] = $info;
				$package[] = $pkg;
				$softid[] = $softid_array[$pkg];
			}
		}
	$where = "where package in ('".implode("','",$package)."')";
	$where1 = " softid in (".implode(",",$softid).") and ";
	}else{
/* 		foreach($softdl as $pkg => $info){
			if($info['sum']>$_POST['dlcount']){
				$soft_dl[$pkg] = $info;
				$package[] = $pkg;
				$softid[] = $softid_array[$pkg];
			}
		} */
		$soft_dl = $softdl;
	}
	unset($softdl);
	//if($_POST['act'] == 'weekey'){var_dump(count($soft_dl));}
	unset($result);
	$soft = array();
	$result = array();
	foreach($table_arr as $table){
	$sql = 'select sum(count) cnt,action,package from sj_channel_daily_install_'.$table.' '.$where.' group by package,action';
	$source = $model -> query($sql,$server);
		while($result = $model -> fetch($source)){
			if(!isset($soft[$result['package']][$result['action']])){
			$soft[$result['package']][$result['action']] = $result['cnt'];
			}else{
			$soft[$result['package']][$result['action']] += $result['cnt'];		
			}
		}
	} //$soft以上是安装量/卸载量数据源
	unset($result);
	$result = array();
	rsort($where_time_arr);
	$start_time = $where_time_arr[count($where_time_arr)-1];
	$end_time = $where_time_arr[0];
	$sql = 'SELECT id,score,softid,create_time FROM sj_soft_comment 
	WHERE 
	'.$where1.' 
	 create_time BETWEEN '.$start_time.' AND '.$end_time;
	$source = $model -> query($sql,'master');
	$softid_pkg_flip = array_flip($softid_array);
	while($info = $model -> fetch($source)){
		$date = date('Y-m-d',$info['create_time']);
		$package = $softid_pkg_flip[$info['softid']];
		if(!isset($soft_comment_score[$package])){
			$soft_comment_score[$package]['score'][$date] = $info['score'];
			$soft_comment_score[$package]['comment'][$date] = 1;
			$soft_comment_cnt[$package] = 1;
		}else{
			$soft_comment_score[$package]['score'][$date] += $info['score'];
			$soft_comment_score[$package]['comment'][$date] += 1;	
			$soft_comment_cnt[$package] += 1;
			
		}
	}
	//以上是评论评分数据源
	rsort($softdl_sum);
	rsort($soft_comment_cnt);
	$top_softdl_sum = $softdl_sum[0];
	$top_comment_sum = $soft_comment_cnt[0];
	unset($softdl_sum);
	unset($soft_comment_cnt);
	foreach($soft_dl as $pkg => $info){
		$yuinfo = $info;
		$A = $info['sum']/$top_softdl_sum; //下载=期间该产品客户端下载量总和/期间客户端下载量总和最高
		$score = $soft_comment_score[$pkg]['score'];
		//rsort($score);
		//$max_score = $score[0];
		$sum_score = array_sum($score);
		$comment = $soft_comment_score[$pkg]['comment'];
		$sum_comment = array_sum($comment);  //评论次数		
		$aver_score = $sum_score/$sum_comment; 
		$B = $aver_score/10;//好评度=期间该产品平均评论分数/10
		//rsort($comment);
		$max_comment = $top_comment_sum; //期间安智市场中所有产品中最高的评论条数总
		$C = $sum_comment/ $max_comment;//评论条数=期间该产品评论条数/期间安智市场中所有产品中最高的评论条数
		$install_cnt = $soft[$pkg]['1'];
		$uninstall_cnt = $soft[$pkg]['0'];
		$cha = (($install_cnt-$uninstall_cnt) > 0) ? ($install_cnt-$uninstall_cnt) : 0;
		$D = $cha/$install_cnt; //留存率=（期间该产品安装量-期间安装用户卸载量）/期间该产品安装量
		//$download_cnt = $info[0];
		$E = $install_cnt/$info['sum'];
		//安装率=期间该产品安装量/期间该产品下载量
		$start = date('Y-m-d',$start_time);
		$end = date('Y-m-d',$end_time);
		$cha = (($yuinfo[$end] - $yuinfo[$start]) > 0) ? ($yuinfo[$end] - $yuinfo[$start]) : 0;
		$F = $cha / $yuinfo[$end];	//增速=（期间该产品最后一天下载量-期间该产品第一天下载量）/期间该产品最后一天下载量【若增速值为负数，取0
		$point_arr[$pkg]['A'] = $A ? $A : 0;
		$point_arr[$pkg]['B'] = $B ? $B : 0;
		$point_arr[$pkg]['C'] = $C ? $C : 0;
		$point_arr[$pkg]['D'] = $D ? $D : 0;
		$point_arr[$pkg]['E'] = $E ? $E : 0;
		$point_arr[$pkg]['F'] = $F ? $F : 0;	
		$E = $E > 1 ? 1 : $E;
		eval("\$rank_list[\$pkg]=${gongshi};");
        if($_POST['dlcount'] && $rank_list[$pkg] < $_POST['dlcount'])  unset($rank_list[$pkg]); 
		//$rank_list[$pkg] = $A*30+$B*5+$C*5+$D*20+$E*30+$F*10;
	}
}

	if($_POST['act'] == 'month'){
		$min = (int) $_POST['min'];
		$max = (int) $_POST['max'];
 		foreach($rank_list as $pkg => $dl){
			if($dl < $min || $dl > $max ){
				unset($rank_list[$pkg]);
			}
		}
	}
	arsort($rank_list);

?>
<center>
<table border=1>
<tr>
<td>排行</td>
<td>软件名</td>
<td>包名</td>
<?php if(in_array($_POST['act'],array('daily','weekey'))){ ?>
<td>平均下载量</td>
<td>指标A</td>
<td>指标B</td>
<td>指标C</td>
<td>指标D</td>
<td>指标E</td>
<td>指标F</td>
<?php } ?>
<td>数据</td>
</tr>
<?php 
$i = 0;
foreach($rank_list as  $key => $info){ ?>
<?
if(!isset($softname_array[$key])) continue;
//if($_POST['dlcount'] && $info < $_POST['dlcount'])  break; 
?>
<tr <?php if($point_arr[$key]['E'] > 1) echo 'style="background-color:red;"';?>>
<td><?php echo ++$i;?></td><td><?php echo $softname_array[$key];?></td>
<td><?php echo $key;?></td>
<?php if(in_array($_POST['act'],array('daily','weekey'))){ ?>
<td><?php echo $soft_dl[$key]['average'];?></td>
<td><?php echo $point_arr[$key]['A'];?></td>
<td><?php echo $point_arr[$key]['B'];?></td>
<td><?php echo $point_arr[$key]['C'];?></td>
<td><?php echo $point_arr[$key]['D'];?></td>
<td>
<?php 
echo $point_arr[$key]['E'] > 1 ? 1 : $point_arr[$key]['E'];
if($point_arr[$key]['E'] > 1 ) echo "<p style=\"font-size:10\">原始值:".$point_arr[$key]['E']."</p>";
?>
</td>
<td><?php echo $point_arr[$key]['F'];?></td>
<?php } ?>
<td><?php echo $info;?></td>
</tr>
<?php 
}
?>
</table>
</center>
</html>
<?php 
function dateform($date){
	$arr = explode('-',$date);
	foreach($arr as $val){
	if(strlen($val)==1) $date_arr[] = '0'.$val;
	else $date_arr[] = $val;
	}
	$date = implode('-',$date_arr);
	return $date;
}
?>
