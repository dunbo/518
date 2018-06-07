<?php
ini_set("memory_limit", "2000M");
error_reporting(E_ERROR);
ini_set("display_errors", true);
set_time_limit(1000);
define('DS', DIRECTORY_SEPARATOR);
define('GO_APP_ROOT', dirname(dirname(realpath(__FILE__))));
define('GO_CONFIG_DIR', GO_APP_ROOT. DS. 'Conf');
define('GO_HELPER_DIR', GO_APP_ROOT. DS. '..'. DS. 'newgomarket.goapk.com'. DS. 'helper');
include_once GO_APP_ROOT. DS. '..'. DS. 'GoPHP'. DS. 'Startup.php';
define('STATDB', 'slave');
$server = 'db30';
$read_server = load_config('cron/read_db');
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
$a = $_POST['a1']? $_POST['a1'] : $_POST['a2'];
$b = $_POST['b1']? $_POST['b1'] : $_POST['b2'];
$c = $_POST['c1']? $_POST['c1'] : $_POST['c2'];
$d = $_POST['d1']? $_POST['d1'] : $_POST['d2'];
$e = $_POST['e1']? $_POST['e1'] : $_POST['e2'];
$f = $_POST['f1']? $_POST['f1'] : $_POST['f2'];

$option = array('table' => 'pu_config','where' => array('config_type' => 'olgame_config', 'configname'=>'olgame_config'));
$config_info = $model -> findOne($option);
if($_POST['modify'] == 2){
	//ini_set('display_errors',true);
	//error_reporting(E_ALL);
	
	if(!$config_info){
		$data['__user_table'] = 'pu_config';
		$info = array();
		if($_POST['gongshi']){
			$content['params']['gongshi'] = $gongshi;
			$content['params']['a'] = $a ? $a : 0;
			$content['params']['b'] = $b ? $b : 0;
			$content['params']['c'] = $c ? $c : 0;
			$content['params']['d'] = $d ? $d : 0;
			$content['params']['e'] = $e ? $e : 0;
			$content['params']['f'] = $f ? $f : 0;
			$content['params']['min'] = $_POST['mt1'];
			$content['params']['max'] = $_POST['mt2'];
			$content['params']['dlcount'] = $_POST['dlcount'];
			$content['params']['time_area'] = $_POST['time_area'];
			// $content['params']['start_tm'] =dateform(trim($_POST['fromdate']));
			// $content['params']['end_tm'] = $_POST['todate1'] ? dateform(trim($_POST['todate1'])) : dateform(trim($_POST['todate']));
		}else{
		//	$content['params']['cur_date'] = dateform(trim($_POST['currdate']));
		}
		$info[$_POST['act']] = $content;
		$info[$_POST['act']]['time'] = time();
		$data['configname'] = 'olgame_config';
		$data['config_type'] = 'olgame_config';
		$data['configcontent'] = json_encode($info);
		$data['uptime'] = time();
		$affect = $model -> insert($data);
		writelog('添加 configname为 olgame_config 的配置');
	}else{
		$info = json_decode($config_info['configcontent'],true);
			$where['configname'] = 'olgame_config';
			$where['config_type'] = 'olgame_config';
		if($_POST['gongshi']){
			$info[$_POST['act']]['params']['gongshi'] = $gongshi;
			$info[$_POST['act']]['params']['a'] = $a ? $a : 0;
			$info[$_POST['act']]['params']['b'] = $b ? $b : 0;
			$info[$_POST['act']]['params']['c'] = $c ? $c : 0;
			$info[$_POST['act']]['params']['d'] = $d ? $d : 0;
			$info[$_POST['act']]['params']['e'] = $e ? $e : 0;
			$info[$_POST['act']]['params']['f'] = $f ? $f : 0;
			$info[$_POST['act']]['params']['time_area'] = $_POST['time_area'];
			//$info[$_POST['act']]['params']['start_tm'] =dateform(trim($_POST['fromdate']));
			//$info[$_POST['act']]['params']['end_tm'] = $_POST['todate1'] ? dateform(trim($_POST['todate1'])) : dateform(trim($_POST['todate']));
		}else{
			//$info[$_POST['act']]['params']['cur_date'] = dateform(trim($_POST['currdate']));
		}
			$info[$_POST['act']]['params']['min'] = $_POST['mt1'];
			$info[$_POST['act']]['params']['max'] = $_POST['mt2'];
			$info[$_POST['act']]['params']['dlcount'] = $_POST['dlcount'];
			$info[$_POST['act']]['time'] = time();
			$info_json = json_encode($info);
			$affect = $model -> update($where,array('__user_table' => 'pu_config','configcontent' => $info_json,'uptime'=> time()),'master');
			writelog('安卓游戏排行规则:修改olgame_config 的参数配置 修改参数为 '.$info_json, 'pu_config','config_type:olgame_config', __ACTION__, '','edit');
	}
}

$option = array('table' => 'sj_soft','where' => array('status' => 1,'hide' => 1,'category_id'=> 2),'field' => 'softid,package,softname');
$softarr = $model -> findAll($option);
foreach($softarr as $info){
	$softname_array[$info['package']] = $info['softname'];
	$softid_array[$info['package']] = $info['softid'];
}
if($_POST['act'] == 'daily'){
		$time_area = $_POST['time_area'];
	$current_time = strtotime(date('Y-m-d',time()));
	for($i=1;$i<=$time_area;$i++){
		$start = $current_time - $i * 3600*24;
		$time_arr[] = date('Ymd',$start);
		$table_arr[] = date('Ymd',$start);
		$where_time_arr[] = $start;
	}
	/*$fromdate = dateform(trim($_POST['fromdate']));
	$todate = dateform(trim($_POST['todate']).' 23:59:59');
	$limit = 3;
	$i = 0;
	for($start = strtotime($fromdate);$start <= strtotime($todate);$start+=3600*24){
		if($i >= 3) break;
		$time_arr[] = date('Ymd',$start);
		$table_arr[] = date('Ymd',$start);
		$where_time_arr[] = $start;
		$i++;
	}*/
}else if($_POST['act'] == 'weekey'){
	$time_area = $_POST['time_area'];
		$current_time = strtotime(date('Y-m-d',time()));
		for($i=1;$i<=$time_area;$i++){
				$start = $current_time - $i * 3600*24;
				$time_arr[] = date('Ymd',$start);
				$table_arr[] = date('Ymd',$start);
				$where_time_arr[] = $start;
		}
	/*$fromdate = dateform(trim($_POST['fromdate']));
	$todate = dateform(trim($_POST['todate'])." 23:59:59");
	for($start = strtotime($fromdate);$start <= strtotime($todate);$start+=3600*24){
		$time_arr[] = date('Ymd',$start);
		$table_arr[] = date('Ymd',$start);
		$where_time_arr[] = $start;
	}*/
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
	$sql = 'select count(id) as cnt,sum(score) as sum_score,package from sj_soft_comment where create_time BETWEEN '.$start_time.' AND '.$end_time.' group by package';
	$source = $model -> query($sql,$read_server);
	while($info = $model -> fetch($source)){
		$package = $info['package'];
		$soft_comment_score[$package]['score'] = $info['sum_score'];
		$soft_comment_score[$package]['comment'] = $info['cnt'];
		$soft_comment_cnt[$package] = $info['cnt'];
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
		$sum_score = $soft_comment_score[$pkg]['score'];
		$sum_comment = $soft_comment_score[$pkg]['comment'];//评论次数
		$aver_score = $sum_score/$sum_comment;
		$B = $aver_score/10;//好评度=期间该产品平均评论分数/10
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
	unset($soft_comment_score);
}

	if($_POST['act'] == 'month'){
		$min = (int) $_POST['mt1'];
		$max = (int) $_POST['mt2'];
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
function writelog($actionexp, $category = '', $value= '', $acname='/index.php/Sj/TopRule/display_form', $log_key="",$type=''){
		//日志配置
	global $model;
		$action_array=array(
			'11'=>array(
				'edit'=>"11_1",
				'update'=>"11_2",
				'new'=>"11_3",
			),
			'12'=>array(
				'edit'=>"12_1",
				'update'=>"12_2",
				'new'=>"12_3",
			),
			'117'=>array(
				'0'=>"117_1",
				'1'=>"117_2",
			),
			'soft_config'=>array(
				'18'=>"zh_999",
				'722'=>"zh_999",
			),
			'feature_config'=>array(
				'116'=>"zh_888",
				'522'=>"zh_888",
			),
		);
		if(!empty($actionexp)) {
		$option = array(
			'table' => 'sj_admin_node',
			'where' => array(
				'nodename' => $acname,
			),
			'field' => 'node_id'
		);
		$node_arr = $model -> findOne($option);
		$action_id = $node_arr['node_id'];
			//$this->action_id=$model->findOne($option);
			//dump($this->action_id);
			//$this->display('Public:header');
			if(isset($_GET['sess_id'])) session_id($_GET['sess_id']);
			session_start();
			$map=array();
			$map['logtime']=time();
			$map['fromip']=$_SERVER['REMOTE_ADDR'];
			$map['actionexp']=$actionexp;
			$map['action_id']=$action_id;
			$map['admin_id']=$_SESSION['admin']['admin_id'];
			$map['category']=$category;
			$map['value']=$value;
			$map['type']=$type;
			if(isset($action_array[$action_id])){
				if($action_array[$action_id][$log_key]){
					$map['log_key']=$action_array[$action_id][$log_key];
				}else{
					if(isset($action_array[$log_key][$action_id])){
						$map['log_key']=$action_array[$log_key][$action_id];
					}else{
						$map['log_key']=$action_id;
					}
				}
			}else{
				if(isset($action_array[$log_key][$action_id])){
					$map['log_key']=$action_array[$log_key][$action_id];
				}else{
					$map['log_key']=$action_id;
				}
			}
			$map['__user_table'] = 'sj_admin_log';
			//dump($this->map);
			$model->insert($map);
			//dump($S);$this->display('Public:header');
		}
	}
?>
