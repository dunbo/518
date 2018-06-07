<?php

//清理智友管理栏目过期数据和添加数据的自动脚本，5分钟执行一次


define('MY_PATH', dirname(realpath(__FILE__)));
$config = include_once MY_PATH.'/../Conf/config.php';
$host = $config['DB_ZHIYOO']['hostname'];
$user = $config['DB_ZHIYOO']['username'];
$password = $config['DB_ZHIYOO']['password'];
$db = $config['DB_ZHIYOO']['database'];

mysql_connect($host,$user,$password);
mysql_select_db($db);
//清理过期数据
$now = time();
$query = mysql_query("select zc.aid from zy_column_content as zc left join zy_schedule as zs on zc.aid=zs.id left join zy_collect_ext as zce on zc.aid = zce.advid  where zs.endtime<={$now} or zs.starttime>={$now} or zs.status=-1 or zce.position >1 ") ;

while($res = mysql_fetch_assoc($query)){
	if(!$res['aid']) continue;
	$delaid[] = $res['aid'];
}

if(!empty($delaid)) {
	$sql = 'delete from zy_column_content where aid in ('.implode(',',$delaid).')';
	mysql_query($sql);
	file_put_contents('/tmp/clear_outlinedata.'.date('md').'.log',"删除栏目数据:\t".$sql."\n",FILE_APPEND);
}

//查询和栏目规则符合的所有的内容
$query = mysql_query('select rule, cid, filter from zy_column_conf where status >=1');
while($rule = mysql_fetch_assoc($query)){
	$ruleinfo = explode('_',$rule['rule']);
	$extsql = '('.implode(',',$ruleinfo).')';
	$searchcnt = count($ruleinfo);
	
	$idarr = array();
	//筛选符合栏目标签规则的原始数据 
	if ($rule['filter']==2) {
		$queryid = mysql_query("select id,count(*) as cnt from zy_idtotagid where tagid in {$extsql} group by id ");
	}else{
		$queryid = mysql_query("select * from (select id,count(*) as cnt from zy_idtotagid where tagid in {$extsql} group by id ) as A  where A.cnt ={$searchcnt}");
	}
// 	$queryid = mysql_query("select * from (select id,count(*) as cnt from zy_idtotagid where tagid in {$extsql} group by id ) as A  where A.cnt ={$searchcnt}");
	
	while($res = mysql_fetch_assoc($queryid)){
		if(!$res['id']) continue;
		$idarr[] = $res['id'];
	}

	if(!empty($idarr)){
		$extsql =  '('.implode(',',$idarr).')';
		$onlineid = mysql_query("select zs.id,zs.tid,zs.addschtime,zs.colid from zy_schedule as zs left join zy_collect_ext as zce on zs.id=zce.advid  where zs.starttime<={$now} and zs.endtime>={$now} and zs.status=0 and zce.position = 1 and zs.colid in {$extsql}");

		//如果内容不在栏目内容表里面，则添加
		while($result = mysql_fetch_assoc($onlineid)){
			
			$exist = mysql_query("select * from zy_column_content where cid={$rule['cid']} and aid={$result['id']} limit 1");
			$existone = mysql_fetch_assoc($exist);
			if(!$existone){ 
				$sql = "insert into zy_column_content (cid,id,aid,tid,addschtime)values({$rule['cid']},{$result['colid']},{$result['id']},{$result['tid']},{$result['addschtime']})";
				mysql_query($sql);
				file_put_contents('/tmp/clear_outlinedata.'.date('md').'.log',"插入栏目数据:\t".$sql."\n",FILE_APPEND);
			}
			
		}
	}
}