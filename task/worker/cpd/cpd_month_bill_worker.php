<?php
include dirname(__FILE__).'/../../init.php';
ini_set('display_errors', true);
error_reporting(E_WARNING);

load_helper('task');
$worker = get_task_worker();
$worker->addFunction("cpd_month_bill", "process_bill");
while ($worker->work());
function process_bill($jobs){
	$jobs = $jobs->workload();
	cpd_writelog($jobs."\n\n");
	$jobs = json_decode($jobs,true);
	$contract_id = $jobs['contract_id'];
	$month = $jobs['month'];
	$cron = $jobs['cron']?$jobs['cron']:0;
	if(!$contract_id||!$month) return false;
	if($month>=date("Ym")) return false;
	$c_t_info = get_cpd_task($contract_id,$month);
	cpd_writelog("任务基本信息：".var_export($c_t_info,true)."\n\n");
	get_expend($c_t_info);
	cpd_writelog("任务消耗信息：".var_export($c_t_info,true)."\n\n");
	//var_dump($c_t_info);
	$bill = month_bill($c_t_info);
	get_p_data($bill);
	cpd_writelog("任务账单信息：".var_export($bill,true)."\n\n");
	//处理月账
	save_bill($bill,$contract_id,$month,$cron);
}

function save_bill($bill,$contract_id,$month,$cron){
	$model = new GoModel();
	if(!$bill) return;
	if(empty($bill[$contract_id])) return;
	$option = array(
		'table' => 'cpd_month_bill',
		'where' => array(
			'contract_id' => $contract_id,
			'bill_month' => $month,
			'status' => array('exp',' != 0 '),
		)
	);
	$old_bill = $model->findOne($option,'master');

//		echo $model->getSql();
//		var_dump($bill);
	//获取上月余额
	$option = array(
		'table' => 'cpd_month_bill',
		'where' => array(
			'status' => array('exp',' != 0 '),
			'contract_id' => $contract_id,
			'bill_month' => date("Ym",strtotime("{$month}01 - 1month"))
		),
		'field'=>'month_balance'
	);
	$last_m_bill = $model->findOne($option,'master');
//	var_dump($old_bill);
	if($old_bill){
		//脚本刷月账时只做插入动作，不更新月账
		if($cron==1){
			$msg = "月账脚本刷数据：不更新月账直接退出\n";
			cpd_writelog($msg);
			return;
		}
		$msg = "补消耗前月账单数据：".var_export($old_bill,true).'\n';
		cpd_writelog($msg);
		$u_where = array(
			'id' => $old_bill['id']
		);
		$u_option = array(
			'__user_table' => 'cpd_month_bill',
			'status' => 1,
			'lt_month_balance' => $last_m_bill?$last_m_bill['month_balance']:0, //上月余额
			'month_prestore' => isset($bill[$contract_id][$month]['month_prestore'])?$bill[$contract_id][$month]['month_prestore']:0, //本月预存
			'month_receipts' => $bill[$contract_id][$month]['month_receipts'], //月收款
			'month_out' => isset($bill[$contract_id][$month]['month_out'])?$bill[$contract_id][$month]['month_out']:0, //月转出
			'month_download' => $bill[$contract_id][$month]['month_download'], //月下载
			'month_cost' => $bill[$contract_id][$month]['month_cost_recharge']+$bill[$contract_id][$month]['month_cost_delivery'], //月总消耗
			'month_cost_recharge' => $bill[$contract_id][$month]['month_cost_recharge'], //月消耗充值
			'month_cost_delivery' => $bill[$contract_id][$month]['month_cost_delivery'], //月消耗配送
			'update_tm' => time()
		);
		$res = $model->update($u_where,$u_option);
		$sql = $model->getSql();
		if($res){
			cpd_writelog('更新月账状态成功：'.$sql."\n");
		}else{
			cpd_writelog('更新月账状态失败：'.$sql."\n");
		}
	}else{
		$insert_data = array(
			'__user_table' =>'cpd_month_bill',
			'add_tm' => time(),
			'update_tm' => time(),
			'lt_month_balance' => $last_m_bill?$last_m_bill['month_balance']:0, //上月余额
			'month_prestore' => isset($bill[$contract_id][$month]['month_prestore'])?$bill[$contract_id][$month]['month_prestore']:0, //本月预存
			'month_receipts' => $bill[$contract_id][$month]['month_receipts'], //月收款
			'month_out' => isset($bill[$contract_id][$month]['month_out'])?$bill[$contract_id][$month]['month_out']:0, //月转出
			'month_download' => $bill[$contract_id][$month]['month_download'], //月下载
			'month_cost' => $bill[$contract_id][$month]['month_cost_recharge']+$bill[$contract_id][$month]['month_cost_delivery'], //月总消耗
			'month_cost_recharge' => $bill[$contract_id][$month]['month_cost_recharge'], //月消耗充值
			'month_cost_delivery' => $bill[$contract_id][$month]['month_cost_delivery'], //月消耗配送
			'contract_id' => $contract_id,
			'bill_month' => $month
		);
		$res = $model->insert($insert_data);
		$sql = $model->getSql();
		if(!$res){
			$msg = "ID为{$contract_id}的合同{$month}月份月账插入失败\n";
			cpd_writelog($msg);
		}
		cpd_writelog($sql."\n");
	}
}

/***************************
 *  获取消耗相关
 * @param $c_t_info(任务信息)
 * @return array
 ***************************/
function get_expend(&$c_t_info){
	$model = new GoModel();
	foreach($c_t_info as $k=>$v){
		if(isset($v['day'])&&count($v['day'])>0){
			$option = array(
				'table'=> 'cpd_expend',
				'where' => array(
					'expend_tm' => $v['day'],
					'status' => 1,
					'task_id' => $v['task_id']
				),
				'field'=>'id,expend_tm,download_recharge,download_delivery,price'
			);
			$expend = $model->findAll($option,'master');
//			echo $model->getSql();
//			var_dump($expend);

			
			if(!$expend){
				if($v['acture_sys']==2){
					$msg = $model->getSql();
					cpd_writelog($msg);
					//CPD二期B系统消耗按月
					$msg = "系统B合同ID为{$v['contract_id']},任务ID为{$v['task_id']}没有完成消耗，月为{$v['month']}";cpd_writelog($msg);
					continue;				
				}else{
					$none_day = $v['day'];
				}				
			}else{
				if($v['acture_sys']==1){
					$msg = $model->getSql();
					cpd_writelog($msg);
					$msg = "系统A合同ID为{$v['contract_id']},任务ID为{$v['task_id']}没有完成消耗，其中包括天为";
					$expend_day = array();
					foreach($expend as $e_k=>$e_v){
						$expend_day[] = $e_v['expend_tm'];
					}
					$none_day = array_diff($v['day'],$expend_day);
				}
				
			}
			//如果有未产生消耗的天则暂时不产生月账
			if($none_day){
				//var_dump($v['day']);
				$msg .= implode(',',format_date($none_day));
//				var_dump($msg);
				cpd_writelog($msg);
				continue;
			}

			//按月计算消耗
//			var_dump($v['month']);
//			var_dump($expend_day);
			foreach($expend as $e_k=>$e_v){
				$b_key = date("Ym",$e_v['expend_tm']);
				if(!isset($v['expend'][$b_key])) $v['expend'][$b_key] = array('month_cost_recharge'=>0,'month_cost_delivery'=>0,'month_download'=>0);
				$v['expend'][$b_key]['month_cost_recharge'] += $e_v['download_recharge'];
				$v['expend'][$b_key]['month_cost_delivery'] += $e_v['download_delivery'];
				$v['expend'][$b_key]['month_download'] += $e_v['download_recharge']/$e_v['price'] + $e_v['download_delivery']/$e_v['price'];
			}
			$c_t_info[$k] = $v;
		}
	}
}


function get_cpd_task($c_id,$month){
	$model = new GoModel();
	list($start,$end) = get_month_day($month);
//	var_dump($start);
//	var_dump($c_id);
	//获取包含本月的任务
	$option = array(
		'table' => 'cpd_task',
		'where' => array(
			'status' => 1,
			'end_tm' => array('exp'," >= '{$start}'"),
			'contract_id' => $c_id
		),
		'field' => 'id,start_tm,end_tm,contract_id,task_id,acture_sys'
	);
	$task = $model->findAll($option,'master');
//	echo $model->getSql();
	$c_t_info = array();
	foreach($task as $k=>$v){
		$c_t_info[$v['id']] = array(
			'contract_id' => $v['contract_id'],
			'start_tm' => $v['start_tm'],
			'end_tm' => $v['end_tm'],
			'task_id' => $v['task_id'],
			'acture_sys' => $v['acture_sys']
		);
		for($i=$v['start_tm'];$i<=$v['end_tm'];$i=$i+86400){
			//只计算本月的所有天
			if(date('Ym',$i) == $month){
				$c_t_info[$v['id']]['day'][] = $i;				
			}
		}
		if($c_t_info[$v['id']]['day'])
		$c_t_info[$v['id']]['month'] = $month;
	}
	//cpd_writelog(var_export($c_t_info)."\n\n");
	return $c_t_info;
}

function month_bill($c_t_info){
	$bill = array();
	if($c_t_info){
		foreach($c_t_info as $k=>$v){
			$bo = true;
			if(!isset($v['month'])) continue;
			if(isset($v['expend'][$v['month']])){
				if(!isset($bill[$v['contract_id']][$v['month']])) $bill[$v['contract_id']][$v['month']] = array('month_cost_recharge'=>0,'month_cost_delivery'=>0,'month_download'=>0);
				$bill[$v['contract_id']][$v['month']]['month_cost_recharge'] += $v['expend'][$v['month']]['month_cost_recharge'];  //月消耗充值
				$bill[$v['contract_id']][$v['month']]['month_cost_delivery'] += $v['expend'][$v['month']]['month_cost_delivery'];	//月消耗配送
				$bill[$v['contract_id']][$v['month']]['month_download'] += $v['expend'][$v['month']]['month_download'];		//月消耗下载量(核减后)
			}else{
				//如果有没生成消耗的日则不生成月账
				$bo = false;
			}
			if(!$bo){
				unset($bill[$v['contract_id']][$v['month']]);
			}

		}
	}
	return $bill;
}

/*
*获取收款
*CPD二期更改为从预存表获取
*/
function get_receipts($start_tm,$end_tm,$k,&$m_v){
	$model = new GoModel();
	$where = array(
		//'status' => 1,
		'receipts_status' => 1,
		'contract_id' => $k,
		'receipts_tm' => array('exp'," >= {$start_tm} and receipts_tm <= {$end_tm}")
	);
	$option = array(
		//'table' => 'cpd_receipts',
		'table' => 'cpd_prestore',
		'where' => $where
	);
	$receipts = $model->findAll($option,'master');
	$m_v['month_receipts'] = 0;
	if($receipts){
		foreach($receipts as $r_k=>$r_v){
			$m_v['month_receipts'] += $r_v['receipts_sum'];
		}
	}
}
//获取预存相关
function get_prestore($start_tm,$end_tm,$k,&$m_v){
	$model = new GoModel();
	$where = array(
		'status' => 1,
		'contract_id' => $k,
		'prestores_tm' => array('exp'," >= {$start_tm} and prestores_tm <= {$end_tm}")
	);
	$option = array(
		'table' => 'cpd_prestore',
		'where' => $where
	);
	$prestore = $model->findAll($option,'master');
//			echo $model->getSql();
//			var_dump($prestore);
	if($prestore){
		$m_v['month_prestore'] = $m_v['month_out'] = 0;
		foreach($prestore as $p_k=>$p_v){
			//存入(预存及同客转入)
			if($p_v['type']=='0'||$p_v['type']=='2'){
				$m_v['month_prestore'] += $p_v['recharge_sum']+$p_v['delivery_sum'];
			}
			//转出(同客转出，其他用途转出)
			if($p_v['type']=='1'||$p_v['type']=='3'){
				$m_v['month_out'] += $p_v['recharge_sum']+$p_v['delivery_sum'];
			}
		}
	}
}

//根据任务消耗获取月的预存及收款
function get_p_data(&$bill){
	foreach($bill as $k=>$v){
		foreach($v as $m_k=>$m_v){
			list($start_tm,$end_tm) = get_month_day($m_k);
			get_prestore($start_tm,$end_tm,$k,$m_v);	//获取预存
			get_receipts($start_tm,$end_tm,$k,$m_v);	//获取收款
			$v[$m_k] = $m_v;
		}
		$bill[$k] = $v;
	}
}

function get_month_day($month){
	$start_tm = strtotime("{$month}01");
	$end_tm = strtotime("{$month}01 + 1month  - 1day");
	return array($start_tm,$end_tm);
}

function format_date($arr){
	//echo $key;
	$new_arr = array();
	foreach($arr as $k=>$v){
		$new_arr[] = date('Y-m-d',$v);
	}
	return $new_arr;
}

function cpd_writelog($msg){
	$now = time();
	$filename = 'cpd_month_bill_worker.log';
	$path = "/data/att/permanent_log/admin_cron_log/".date("Y-m-d", $now);
	if(!file_exists($path)){
		mkdir($path, 0755, true);
	}
	$path_log = $path."/".$filename;
	$msg = date('Y-m-d H:i:s', $now). " {$msg}\n";
	file_put_contents($path_log, $msg, FILE_APPEND);
}