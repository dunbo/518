<?php
 
class IncomemanageAction extends CommonAction{

	//待审核列表
	function check_pending_list(){
		$model = new Model();
		$co_model = D('Cooperative.Incomemanage');
		$admin_id = $_SESSION['admin']['admin_id'];
		$userid_arr_manage = $this -> cooperative_manager($admin_id);
		if($_GET['charge']){
			$charge = $_GET['charge'];
		}elseif($_GET['charge_name']){
			$charge = $this -> search_charge($_GET['charge_name']);
		}

		$channel_type = $_GET['channel_type'];
		$income_types = $_GET['income_types'];
		$account_name = $_GET['account_name'];
		$channel_name = $_GET['channel_name'];
		
		
		if($charge){
			$where_user['_string'] = "charge_person = '{$charge}' and status = 1";
			$user_result = $co_model -> table('t_user') -> where($where_user) -> select();

			foreach($user_result as $key => $val){
				$userid_arr_user[] = $val['uid'];
			}
		}elseif(isset($_GET['charge_name']) && !empty($_GET['charge_name'])){
			$where_the_charge['_string'] = "charge_name = {$_GET['charge_name']}";
			$charge_result = $co_model -> table('t_charge') -> where($where_the_charge) -> select();
			$where_user['_string'] = "charge_person = '{$charge_result[0]['id']}' and status = 1";
			$user_result = $co_model -> table('t_user') -> where($where_user) -> select();
			foreach($user_result as $key => $val){
				$userid_arr_user[] = $val['uid'];
			}
		}
		if($channel_type){
			$where_channel_category['_string'] = " category_id = {$channel_type} and status = 1";
			$channel_category_result = $model -> table('sj_channel') -> where($where_channel_category) -> select();
	
			foreach($channel_category_result as $key => $val){
				$cid_arr .= $val['cid'].',';
			}
			$cid_str = substr($cid_arr,0,strlen($cid_arr) - 1);
			$where_all .= " and cid in ({$cid_str})";
		}
		if($account_name){
			$where_account['_string'] = " user_name like '%{$account_name}%' and status = 1";
			$account_result = $co_model -> table('t_user') -> where($where_account) -> select();
			foreach($account_result as $key => $val){
				$userid_arr_account[] = $val['uid'];
			}
		}
		if($channel_name){
			$where_channel['_string'] = " chname like '%{$channel_name}%' and status = 1";
			$channel_result = $model -> table('sj_channel') -> where($where_channel) -> select();
			foreach($channel_result as $key => $val){
				$cid_arr .= $val['cid'].',';
			}
			$cid_str = substr($cid_arr,0,strlen($cid_arr) - 1);
			$where_all .= " and cid in ({$cid_str})";
		}
		
		if(isset($_GET['edit_status']) && $_GET['edit_status'] == 1){
			$where_all .= " and update_tm = 0 ";
		}elseif(isset($_GET['edit_status']) && $_GET['edit_status'] == 2){
			$where_all .= " and update_tm != 0";
		}
		
		
		
		//根据负责人，登录账号，账号名称取交集确定账号范围
		if($userid_arr_user && $userid_arr_account){
			$userid_arr_all = array_intersect($userid_arr_manage,$userid_arr_user,$userid_arr_account);
		}elseif($userid_arr_user && !$userid_arr_account){
			if(!$account_name){
				$userid_arr_all = array_intersect($userid_arr_manage,$userid_arr_user);
			}else{
				$userid_arr_all = array();
			}
		}elseif($userid_arr_account && !$userid_arr_user){
			if(!$charge){
				$userid_arr_all = array_intersect($userid_arr_manage,$userid_arr_account);
			}else{
				$userid_arr_all = array();
			}
			
		}elseif(!$userid_arr_account && !$userid_arr_user){
			if(!$charge && !$account_name){
				$userid_arr_all = $userid_arr_manage;
			}else{
				$userid_arr_all = array();
			}
		}
		
		if($_GET['uid']){
			$userid_get = array($_GET['uid']);
			$userid_arr_all = array_intersect($userid_arr_all,$userid_get);
		}
		
		foreach($userid_arr_all as $key => $val){
			$userid_arr_all_str .= $val.',';
		}
		
		$userid_str = substr($userid_arr_all_str,0,strlen($userid_arr_all_str) - 1);
	
		//昨日时间
		$theday = date('Ymd',time() - 3600*24);
		
		//前天时间
		$yesterday = date('Ymd',time() - 3600*24*2);
		//上周时间
		$week = date('Ymd',time() - 3600*24);
		$timestamp = strtotime($week);
		$n = time() - 86400 * date('N', strtotime($theday));
		$week_star = date('Ymd', $n - 86400 * 8 );
		$week_end = date('Ymd', $n - 86400);
		
		//上月时间
		$month_star_go = date('Ym',strtotime('-1 month'));
		$month_star = $month_star_go.'01';
		$month_end_go = date('Ym');
		$month_end = date('Ymd',strtotime($month_end_go.'01') - 3600*24);
		$month_errand = (strtotime($month_end) - strtotime($month_star))/(3600*24) + 1;
		
		//查询报警条件
		$warning_active_result = $co_model -> table('t_warning') -> where(array('income_type' => 1,'target' => '1','status' => 1)) -> select();
		$warning_ad_result = $co_model -> table('t_warning') -> where(array('income_type' => 2,'target' => '1','status' => 1)) -> select();
		$warning_game_result = $co_model -> table('t_warning') -> where(array('income_type' => 4,'target' => '1','status' => 1)) -> select();
		$warning_active_income_result = $co_model -> table('t_warning') -> where(array('income_type' => 1,'target' => '2','status' => 1)) -> select();
		$warning_ad_income_result = $co_model -> table('t_warning') -> where(array('income_type' => 2,'target' => '2','status' => 1)) -> select();
		
		$warning_game_income_result = $co_model -> table('t_warning') -> where(array('income_type' => 4,'target' => '2','status' => 1)) -> select();
		$warning_active_nincome_result = $co_model -> table('t_warning') -> where(array('income_type' => 1,'target' => '3','status' => 1)) -> select();
		$warning_ad_nincome_result = $co_model -> table('t_warning') -> where(array('income_type' => 2,'target' => '3','status' => 1)) -> select();
		$warning_game_nincome_result = $co_model -> table('t_warning') -> where(array('income_type' => 4,'target' => '3','status' => 1)) -> select();
		import("@.ORG.Page");
		$param = http_build_query($_GET);
		//查询所有
		if($userid_str){
			$where_go['_string'] = "theday = {$theday} and userid in ({$userid_str})".$where_all;
		}else{
			$where_go['_string'] = "theday = {$theday} and userid in (0)".$where_all;
		}
		
		if(isset($_GET['order_by_num']) && !empty($_GET['order_by_num'])){
			if($_GET['order_by_num'] == 1){
				$order = 'income_num ASC';
				$this -> assign('order_by_num',$_GET['order_by_num']);
			}elseif($_GET['order_by_num'] == 2){
				$order = 'income_num DESC';
				$this -> assign('order_by_num',$_GET['order_by_num']);
			}elseif($_GET['order_by_num'] == 3){
				$order = 'new_income ASC';
				$this -> assign('order_by_num',$_GET['order_by_num']);
			}elseif($_GET['order_by_num'] == 4){
				$order = 'new_income DESC';
				$this -> assign('order_by_num',$_GET['order_by_num']);
			}elseif($_GET['order_by_num'] == 5){
				$order = 'income_ratio ASC';
				$this -> assign('order_by_num',$_GET['order_by_num']);
			}elseif($_GET['order_by_num'] == 6){
				$order = 'income_ratio DESC';
				$this -> assign('order_by_num',$_GET['order_by_num']);
			}
		}else{
			$order = 'income_ratio DESC';
		}
		
		if(isset($_GET['income_types']) && $_GET['income_types'] == 1){
			$where_go['type'] = 1;
			$count = $co_model -> table('t_all_income') -> where($where_go) -> count();
			$Page = new Page($count, 10, $param);
			$active_result = $co_model -> table('t_all_income') -> where($where_go) -> limit($Page->firstRow . ',' . $Page->listRows) -> order($order) -> select();
			foreach($active_result as $key => $val){
				$channel_result = $model -> table('sj_channel') -> where(array('cid' => $val['cid'])) -> select();
				$val['chname'] = $channel_result[0]['chname'];
				$category_result = $model -> table('sj_channel_category') -> where(array('category_id' => $channel_result[0]['category_id'])) -> select();
				$val['category_name'] = $category_result[0]['name'];
				$where_user['_string'] = "uid = {$val['userid']}";
				$user_result = $co_model -> table('t_user') -> where($where_user) -> select();
				$charge_result = $co_model ->  table('t_charge') -> where(array('id' => $user_result[0]['charge_person'])) -> select();
				$val['user_name'] = $user_result[0]['user_name'];
				$val['charge_name'] = $charge_result[0]['charge_name'];
				$val['type'] = 1;
				//报警标红
				if($warning_active_result[0]['top']){
					if($val['income_ratio'] > $warning_active_result[0]['top']){
						$val['active_ratio_warning'] = 1;
					}
				}
				if($warning_active_result[0]['below']){
					if($val['income_ratio'] < $warning_active_result[0]['below']){
						$val['active_ratio_warning'] = 1;
					}
				}
				if($warning_active_income_result[0]['top']){
					if($val['income_num'] > $warning_active_income_result[0]['top']){
						$val['active_income_warning'] = 1;
					}
				}
				if($warning_active_income_result[0]['below']){
					if($val['income_num'] < $warning_active_income_result[0]['below']){
						$val['active_income_warning'] = 1;
					}
				}
				if($warning_active_nincome_result[0]['top']){
					if($val['new_income'] > $warning_active_nincome_result[0]['top']){
						$val['active_nincome_warning'] = 1;
					}
				}
				if($warning_active_nincome_result[0]['below']){
					if($val['new_income'] < $warning_active_nincome_result[0]['below']){
						$val['active_nincome_warning'] = 1;
					}
				}
				$active_result[$key] = $val;
			}
			$result = $active_result;
		}elseif(isset($_GET['income_types']) && $_GET['income_types'] == 2){
			$where_go['type'] = 2;
			$count = $co_model -> table('t_all_income') -> where($where_go) -> field('cid,userid,new_income') -> group('cid') -> count();
			$Page = new Page($count, 10, $param);
			$ad_result = $co_model -> table('t_all_income') -> where($where_go) -> field('cid,userid,new_income,before_deduct_income,deduct_ratio,adjust_value,settlement_patterns,front_show_type,income_show_type,income_show,income_ratio,income_num,update_tm') -> group('cid') -> limit($Page->firstRow . ',' . $Page->listRows)-> order($order) ->select();
			
			foreach($ad_result as $key => $val){
				$channel_result = $model -> table('sj_channel') -> where(array('cid' => $val['cid'])) -> select();
				$val['chname'] = $channel_result[0]['chname'];
				$category_result = $model -> table('sj_channel_category') -> where(array('category_id' => $channel_result[0]['category_id'])) -> select();
				$val['category_name'] = $category_result[0]['name'];
				$where_user['_string'] = "uid = {$val['userid']}";
				$user_result = $co_model -> table('t_user') -> where($where_user) -> select();
				$charge_result = $co_model ->  table('t_charge') -> where(array('id' => $user_result[0]['charge_person'])) -> select();
				$val['user_name'] = $user_result[0]['user_name'];
				$val['charge_name'] = $charge_result[0]['charge_name'];
				$val['type'] = 2;
				
				//报警标红
				if($warning_ad_result[0]['top']){
					if($val['income_ratio'] > $warning_ad_result[0]['top']){
						$val['ad_ratio_warning'] = 1;
					}
				}
				if($warning_ad_result[0]['below']){
					if($val['income_ratio'] < $warning_ad_result[0]['below']){
						$val['ad_ratio_warning'] = 1;
					}
				}
				if($warning_ad_income_result[0]['top']){
					if($val['income_num'] > $warning_ad_income_result[0]['top']){
						$val['ad_income_warning'] = 1;
					}
				}
				if($warning_ad_income_result[0]['below']){
					if($val['income_num'] < $warning_ad_income_result[0]['below']){
						$val['ad_income_warning'] = 1;
					}
				}
				if($warning_ad_nincome_result[0]['top']){
					if($val['new_income'] > $warning_ad_nincome_result[0]['top']){
						$val['ad_nincome_warning'] = 1;
					}
				}
				if($warning_ad_nincome_result[0]['below']){
					if($val['new_income'] < $warning_ad_nincome_result[0]['below']){
						$val['ad_nincome_warning'] = 1;
					}
				}
				$ad_result[$key] = $val;
			} 
			$result = $ad_result;
		}elseif(isset($_GET['income_types']) && $_GET['income_types'] == 4){
			$where_go['type'] = 4;
			$count = $co_model -> table('t_all_income') -> where($where_go) -> field('cid,userid,new_income') -> group('cid') -> count();
			$Page = new Page($count, 10, $param);
			$game_result = $co_model -> table('t_all_income') -> where($where_go) -> field('cid,userid,new_income,before_deduct_income,deduct_ratio,adjust_value,settlement_patterns,front_show_type,income_show_type,income_show,income_ratio,income_num,update_tm') -> group('cid') -> limit($Page->firstRow . ',' . $Page->listRows)-> order($order) ->select();
		
			foreach($game_result as $key => $val){
				$channel_result = $model -> table('sj_channel') -> where(array('cid' => $val['cid'])) -> select();
				$val['chname'] = $channel_result[0]['chname'];
				$category_result = $model -> table('sj_channel_category') -> where(array('category_id' => $channel_result[0]['category_id'])) -> select();
				$val['category_name'] = $category_result[0]['name'];
				$where_user['_string'] = "uid = {$val['userid']}";
				$user_result = $co_model -> table('t_user') -> where($where_user) -> select();
				$charge_result = $co_model ->  table('t_charge') -> where(array('id' => $user_result[0]['charge_person'])) -> select();
				$val['user_name'] = $user_result[0]['user_name'];
				$val['type'] = 4;
				$val['user_name'] = $user_result[0]['user_name'];
				$val['charge_name'] = $charge_result[0]['charge_name'];
				$val['new_income'] = $val['new_income'];
				
				//报警标红
				if($warning_game_result[0]['top']){
					if($val['income_ratio'] > $warning_game_result[0]['top']){
						$val['game_ratio_warning'] = 1;
					}
				}
				if($warning_game_result[0]['below']){
					if($val['income_ratio'] < $warning_game_result[0]['below']){
						$val['game_ratio_warning'] = 1;
					}
				}
				if($warning_game_income_result[0]['top']){
					if($val['income_num'] > $warning_game_income_result[0]['top']){
						$val['game_income_warning'] = 1;
					}
				}
				if($warning_game_income_result[0]['below']){
					if($val['income_num'] < $warning_game_income_result[0]['below']){
						$val['game_income_warning'] = 1;
					}
				}
				if($warning_game_nincome_result[0]['top']){
					if($val['new_income'] > $warning_game_nincome_result[0]['top']){
						$val['game_nincome_warning'] = 1;
					}
				}
				if($warning_game_nincome_result[0]['below']){
					if($val['new_income'] < $warning_game_nincome_result[0]['below']){
						$val['game_nincome_warning'] = 1;
					}
				}
				$game_result[$key] = $val;
			}
			$result = $game_result;
		}else{
			$count = $co_model -> table('t_all_income') -> where($where_go) -> count();
			$Page = new Page($count, 10, $param);
			$all_result = $co_model -> table('t_all_income') -> where($where_go) -> limit($Page->firstRow . ',' . $Page->listRows)-> order($order) ->select();
			
			foreach($all_result as $key => $val){
				$channel_result = $model -> table('sj_channel') -> where(array('cid' => $val['cid'])) -> select();
				$val['chname'] = $channel_result[0]['chname'];
				$category_result = $model -> table('sj_channel_category') -> where(array('category_id' => $channel_result[0]['category_id'])) -> select();
				$val['category_name'] = $category_result[0]['name'];
				$where_user['_string'] = "uid = {$val['userid']}";
				$user_result = $co_model -> table('t_user') -> where($where_user) -> select();
				$charge_result = $co_model ->  table('t_charge') -> where(array('id' => $user_result[0]['charge_person'])) -> select();
				$val['user_name'] = $user_result[0]['user_name'];
				$val['charge_name'] = $charge_result[0]['charge_name'];
				//报警标红
				if($warning_active_result[0]['top']){
					if($val['income_ratio'] > $warning_active_result[0]['top']){
						$val['active_ratio_warning'] = 1;
					}
				}
				if($warning_active_result[0]['below']){
					if($val['income_ratio'] < $warning_active_result[0]['below']){
						$val['active_ratio_warning'] = 1;
					}
				}
				if($warning_active_income_result[0]['top']){
					if($val['income_num'] > $warning_active_income_result[0]['top']){
						$val['active_income_warning'] = 1;
					}
				}
				if($warning_active_income_result[0]['below']){
					if($val['income_num'] < $warning_active_income_result[0]['below']){
						$val['active_income_warning'] = 1;
					}
				}
				if($warning_active_nincome_result[0]['top']){
					if($val['new_income'] > $warning_active_nincome_result[0]['top']){
						$val['active_nincome_warning'] = 1;
					}
				}
				if($warning_active_nincome_result[0]['below']){
					if($val['new_income'] < $warning_active_nincome_result[0]['below']){
						$val['active_nincome_warning'] = 1;
					}
				}
				if($warning_ad_result[0]['top']){
					if($val['income_ratio'] > $warning_ad_result[0]['top']){
						$val['ad_ratio_warning'] = 1;
					}
				}
				if($warning_ad_result[0]['below']){
					if($val['income_ratio'] < $warning_ad_result[0]['below']){
						$val['ad_ratio_warning'] = 1;
					}
				}
				if($warning_ad_income_result[0]['top']){
					if($val['income_num'] > $warning_ad_income_result[0]['top']){
						$val['ad_income_warning'] = 1;
					}
				}
				if($warning_ad_income_result[0]['below']){
					if($val['income_num'] < $warning_ad_income_result[0]['below']){
						$val['ad_income_warning'] = 1;
					}
				}
				if($warning_ad_nincome_result[0]['top']){
					if($val['new_income'] > $warning_ad_nincome_result[0]['top']){
						$val['ad_nincome_warning'] = 1;
					}
				}
				if($warning_ad_nincome_result[0]['below']){
					if($val['new_income'] < $warning_ad_nincome_result[0]['below']){
						$val['ad_nincome_warning'] = 1;
					}
				}
				if($warning_game_result[0]['top']){
					if($val['income_ratio'] > $warning_game_result[0]['top']){
						$val['game_ratio_warning'] = 1;
					}
				}
				if($warning_game_result[0]['below']){
					if($val['income_ratio'] < $warning_game_result[0]['below']){
						$val['game_ratio_warning'] = 1;
					}
				}
				if($warning_game_income_result[0]['top']){
					if($val['income_num'] > $warning_game_income_result[0]['top']){
						$val['game_income_warning'] = 1;
					}
				}
				if($warning_game_income_result[0]['below']){
					if($val['income_num'] < $warning_game_income_result[0]['below']){
						$val['game_income_warning'] = 1;
					}
				}
				if($warning_game_nincome_result[0]['top']){
					if($val['new_income'] > $warning_game_nincome_result[0]['top']){
						$val['game_nincome_warning'] = 1;
					}
				}
				if($warning_game_nincome_result[0]['below']){
					if($val['new_income'] < $warning_game_nincome_result[0]['below']){
						$val['game_nincome_warning'] = 1;
					}
				}
				$all_result[$key] = $val;
			}
			$result = $all_result;
		}
		
		$charge_result = $co_model -> table('t_charge') -> where(array('status' => 1)) -> select();
	
		//查询渠道类型
		$channel_result = $model -> table('sj_channel_category') -> where(array('status' => 1)) -> select();
		
		$this -> assign('warning_active_result',$warning_active_result);
		$this -> assign('warning_ad_result',$warning_ad_result);
		$this -> assign('warning_game_result',$warning_game_result);
		$this -> assign('warning_ad_income_result',$warning_ad_income_result);
		$this -> assign('warning_game_income_result',$warning_game_income_result);
		$this -> assign('warning_active_income_result',$warning_active_income_result);
		$this -> assign('warning_active_nincome_result',$warning_active_nincome_result);
		$this -> assign('warning_ad_nincome_result',$warning_ad_nincome_result);
		$this -> assign('warning_game_nincome_result',$warning_game_nincome_result);
		$this -> assign('order_by_num',$_GET['order_by_num']);
		$this -> assign('channel_category',$channel_result);
		$this -> assign('charge_result',$charge_result);
		$this -> assign('charge',$charge);
		$this -> assign('channel_type',$_GET['channel_type']);
		$this -> assign('income_types',$_GET['income_types']);
		$this -> assign('account_name',$_GET['account_name']);
		$this -> assign('channel_name',$_GET['channel_name']);
		$this -> assign('edit_status',$_GET['edit_status']);
		$this -> assign('theday',$theday);
		$Page->setConfig('header', '篇记录');
		$Page->setConfig('first', '<<');
		$Page->setConfig('last', '>>');
		$show = $Page->show();
		$this->assign("page", $show);
		$this -> assign('result',$result);
		$this -> display('check_pending_list');

	}
 
 
	//待审核编辑显示
	function check_edit_show(){
		$co_model = D('Cooperative.Incomemanage');
		$income_type = $_GET['income_type'];
		$cid = $_GET['cid'];
		
		//昨日时间
		$theday = date('Ymd',time() - 3600*24);
		//前天时间 
		$yesterday = date('Ymd',time() - 3600*24*2);
		if($income_type == 1){
			$result = $co_model -> table('t_active_income') -> where(array('cid' => $cid,'theday' => $theday)) -> select();
			$all_result = $co_model -> table('t_all_income') -> where(array('cid' => $cid,'theday' => $theday,'type' => 1)) -> select();
			$theday_income = $result[0]['activate_num']*$result[0]['active_price'];
			$yesterday_result = $co_model -> table('t_active_income') -> where(array('cid' => $cid,'theday' => $yesterday)) -> select();
			$yesterday_income = $yesterday_result[0]['activate_num']*$yesterday_result[0]['active_price'];
			$ratio = sprintf("%.2f",($theday_income/$yesterday_income - 1)*100);
			$this -> assign('ratio',$ratio);
			$this -> assign('theday_income',$theday_income);
			$tpl_html = 'check_active_edit';
		}elseif($income_type == 2){
			$result = $co_model -> table('t_ad_income')	-> where(array('cid' => $cid,'theday' => $theday)) -> select();
			$all_result = $co_model -> table('t_all_income') -> where(array('cid' => $cid,'theday' => $theday,'type' => 2)) -> select();
			$yesterday_result = $co_model -> table('t_ad_income') -> where(array('cid' => $cid,'theday' => $yesterday)) -> field('sum(new_income)') -> group() -> select();
			$tpl_html = 'check_else_edit';
		}elseif($income_type == 4){
			$result = $co_model -> table('t_game_income')	-> where(array('cid' => $cid,'theday' => $theday)) -> select();
			$all_result = $co_model -> table('t_all_income') -> where(array('cid' => $cid,'theday' => $theday,'type' => 4)) -> select();
			$yesterday_result = $co_model -> table('t_game_income') -> where(array('cid' => $cid,'theday' => $yesterday)) -> select();
			$tpl_html = 'check_else_edit';
		}

		$charge = $_GET['charge'];
		$channel_type = $_GET['channel_type'];
		$income_types = $_GET['income_types'];
		$account_name = $_GET['account_name'];
		$channel_name = $_GET['channel_name'];
		$edit_status = $_GET['edit_status'];
		$this -> assign('charge',$charge);
		$this -> assign('channel_type',$_GET['channel_type']);
		$this -> assign('income_types',$_GET['income_types']);
		$this -> assign('account_name',$_GET['account_name']);
		$this -> assign('channel_name',$_GET['channel_name']);
		$this -> assign('edit_status',$_GET['edit_status']);
		$this -> assign('cid',$cid);
		$this -> assign('income_type',$income_type);
		$this -> assign('result',$result);
		$this -> assign('all_result',$all_result);
		$this -> display($tpl_html);
	}
	
	
	//待审核编辑未提交修改数据
	function ajax_edit(){
		$co_model = D('Cooperative.Incomemanage');
		$cid = $_GET['cid'];
		$income_type = $_GET['income_type'];
		//昨日时间
		$theday = date('Ymd',time() - 3600*24);
		//前天时间
		$yesterday = date('Ymd',time() - 3600*24*2);
		if($income_type == 1){
			$new_activate_num = $_GET['new_activate_num'];
			$result_theday = $co_model -> table('t_active_income') -> where(array('cid' => $cid,'theday' => $theday)) -> select();
			$result_yesterday = $co_model -> table('t_active_income') -> where(array('cid' => $cid,'theday' => $yesterday)) -> select();
			$yesterday_income = sprintf("%.2f", $new_activate_num*$result_theday[0]['active_price']);
			$yesterday_ratio = sprintf("%.2f", ($yesterday_income/$result_yesterday[0]['new_income'] - 1) * 100);
			$data['yesterday_income'] = $yesterday_income;
			$data['yesterday_ratio'] = $yesterday_ratio;
			echo json_encode($data);
			return json_encode($data);
		}elseif($income_type == 2){
			$new_pre_income = $_GET['new_pre_income'];
			$result_theday = $co_model -> table('t_ad_income') -> where(array('cid' => $cid,'theday' => $theday)) -> select();
			$ad_result = $co_model -> table('t_ad_income') -> where(array('cid' => $cid,'theday' => $theday)) -> field('sum(income_num)') -> select();
			$result_yesterday = $co_model -> table('t_all_income') -> where(array('cid' => $cid,'theday' => $yesterday,'type' => 2)) -> field('new_income') -> select();
		
			$per_ratio = sprintf("%.2f", ($new_pre_income/$result_yesterday[0]['new_income'] - 1) * 100);
			foreach($result_theday as $key => $val){
				$ratio_need = $new_pre_income/$ad_result[0]['sum(income_num)'];
				$new_income_1 = $ratio_need * $val['income_num'];
				$new_income_2 = explode('.',$new_income_1);
				$new_income_3 = substr($new_income_2[1],0,2);
				$new_income_need[$key] = $new_income_2[0].'.'.$new_income_3; //砍掉小数点两位后数值
			}
			$new_income = array_sum($new_income_need);
			$pre_yesterday_ratio = sprintf("%.2f", ($new_pre_income/$result_yesterday[0]['new_income'] - 1) * 100);
		
			$sys_yesterday_ratio = sprintf("%.2f", ($new_income/$result_yesterday[0]['new_income'] - 1) * 100);
			
			$data['new_income'] = $new_income;
			$data['pre_yesterday_ratio'] = $pre_yesterday_ratio;
			$data['sys_yesterday_ratio'] = $sys_yesterday_ratio;
			echo json_encode($data);
			return json_encode($data);
		}elseif($income_type == 4){
			$new_pre_income = $_GET['new_pre_income'];
			
			$result_theday = $co_model -> table('t_game_income') -> where(array('cid' => $cid,'theday' => $theday)) -> select();
			$game_result = $co_model -> table('t_game_income') -> where(array('cid' => $cid,'theday' => $theday)) -> field('sum(income_num)') -> select();
			$result_yesterday = $co_model -> table('t_all_income') -> where(array('cid' => $cid,'theday' => $yesterday,'type' => 4)) -> field('new_income') -> select();

			$per_ratio = sprintf("%.2f", ($new_pre_income/$result_yesterday[0]['sum(income_num)'] - 1) * 100);
			
			foreach($result_theday as $key => $val){
				$ratio_need = $new_pre_income/$game_result[0]['sum(income_num)'];
				$new_income_1 = $ratio_need * $val['income_num'];
				$new_income_2 = explode('.',$new_income_1);
				$new_income_3 = substr($new_income_2[1],0,2);
				$new_income_need[$key] = $new_income_2[0].'.'.$new_income_3; //砍掉小数点两位后数值
			}
	
			$new_income = array_sum($new_income_need);
			$pre_yesterday_ratio = sprintf("%.2f", ($new_pre_income/$result_yesterday[0]['new_income'] - 1) * 100);
			$sys_yesterday_ratio = sprintf("%.2f", ($new_income/$result_yesterday[0]['new_income'] - 1) * 100);
	
			$data['new_income'] = $new_income;
			$data['pre_yesterday_ratio'] = $pre_yesterday_ratio;
			$data['sys_yesterday_ratio'] = $sys_yesterday_ratio;
			echo json_encode($data);
			return json_encode($data);
			
		}
	}
 
	//待审核编辑提交
	function check_edit_do(){
		$co_model = D('Cooperative.Incomemanage');
		$income_type = $_POST['income_type'];
		$cid = $_POST['cid'];
		$userid = $_POST['userid'];
		$model = new Model();
		$channel_result = $model -> table('sj_channel') -> where(array('cid' => $cid)) -> select();
		$user_result = $co_model -> table('t_user') -> where(array('uid' => $userid)) -> select();
		$path = 'Cooperative_'.date('Ym/d',time()).'.log';
		if($income_type == 1){
			//昨日时间
			$theday = date('Ymd',time() - 3600*24);
			//前天时间
			$yesterday = date('Ymd',time() - 3600*24*2);
			$active_result = $co_model -> table('t_active_income') -> where(array('cid' => $cid,'theday' => $theday)) -> select();
			$where['cid'] = $cid;
			$where['theday'] = $theday;
			$data['new_activate_num'] = $_POST['new_activate_num'];
			if(!preg_match("/^\d*$/",$_POST['new_activate_num'])){
				$this -> error("新激活个数格式错误");
			}
			if($_POST['new_activate_num'] > $active_result[0]['before_activate_num']){
				$this -> error("新激活个数不能大于激活值上限");
			}

			$new_income = $active_result[0]['active_price']*$_POST['new_activate_num'];
			$yesterday_where['_string'] = "theday = {$yesterday} and cid = {$cid} and type = 1";
			$yesterday_active_result = $co_model -> table('t_all_income') -> where($yesterday_where) -> select();
			$active_ratio = sprintf("%.2f", ($new_income/$yesterday_active_result[0]['income_num'] - 1)*100);
			$data['income_ratio'] = $active_ratio;
			$data_child['new_income'] = $new_income;
			if((1&$active_result[0]['income_show_type']) == 1){
				$data_child['income_show'] = $new_income;
			}else{
				$data_child['income_show'] = 0;
			}
			$data_child['new_activate_num'] = $_POST['new_activate_num'];
			$data_child['income_ratio'] = $active_ratio;
			$data_child['update_tm'] = time();
			$data_child['deduct_ratio'] = sprintf("%.2f", (($yesterday_active_result[0]['before_deduct_income'] - $new_income)/$yesterday_active_result[0]['before_deduct_income'])*100);
			$data['update_tm'] = time();
			$data['new_income'] = $new_income;
			if((1&$active_result[0]['income_show_type']) == 1){
				$data['income_show'] = $new_income;
			}else{
				$data['income_show'] = 0;
			}
			$data['deduct_ratio'] = sprintf("%.2f", (($yesterday_active_result[0]['before_deduct_income'] - $new_income)/$yesterday_active_result[0]['before_deduct_income'])*100);
			$where_all['cid'] = $cid;
			$where_all['theday'] = $theday;
			$where_all['type'] = 1;
			$log_model = D('Cooperative.SysManager');
			$log_all_result = $co_model -> table('t_all_income') -> where($where_all) -> select();
			$log_all_need = $log_model -> logcheck(array('id' => $log_all_result[0]['id']),'t_all_income',array('new_activate_num'=> $data['new_activate_num']));
			$where_all['cid'] = $cid;
			$where_all['theday'] = $theday;
			$where_all['type'] = 1;
			$result = $co_model -> update_all($where_all,$data);
	
			$child_result = $co_model -> update_active($where,$data_child);
			$type_name = "调整安智市场激活分成激活个数(账号:{$user_result[0]['user_name']};渠道:{$channel_result[0]['chname']}),调整前{$log_all_need['new_activate_num'][1]},调整后{$log_all_need['new_activate_num'][2]}";
		}elseif($income_type == 2){
			//昨日时间
			$theday = date('Ymd',time() - 3600*24);
			//前天时间
			$yesterday = date('Ymd',time() - 3600*24*2);
			$ad_result = $co_model -> table('t_ad_income') -> where(array('cid' => $cid,'theday' => $theday)) -> field('sum(new_income),sum(before_deduct_income),sum(income_num)') -> group('cid') -> select();
			$all_result = $co_model -> table('t_all_income') -> where(array('cid' => $cid,'theday' => $theday,'type' => 2)) -> field('new_income,before_deduct_income') -> select();
			$data['adjust_value'] = $_POST['adjust_value'];
			if(intval($_POST['adjust_value']) > $all_result[0]['before_deduct_income']){
				$this -> error("调整值不能大于收入值上限");
			}
			if(intval($_POST['adjust_value']) < 0){
				$this -> error("调整值不能小于0");
			}
			$val_my = explode('.',trim($_POST['adjust_value']));
		
			if(preg_match_all("/[A-Za-z]/", $_POST['adjust_value'], $match)){
				$this -> error("调整值不可输入字母");
			}
	
			if(preg_match_all("/([\x81-\xfe][\x40-\xfe])/", $_POST['adjust_value'], $match)){
				$this -> error("调整值不可输入汉字");
			}
			if(strlen($val_my[1]) > 2){
				$this -> error("调整值调整值格式错误");
			}
			$yesterday_where['_string'] = "theday = {$yesterday} and cid = {$cid} and type = 2";
			$yesterday_ad_result = $co_model -> table('t_all_income') -> where($yesterday_where) -> field('new_income,before_deduct_income') -> select();
			$data['adjust_ratio'] = sprintf("%.2f",($_POST['adjust_value']/$yesterday_ad_result[0]['new_income'] - 1)*100);
			$ratio_go = $_POST['adjust_value']/$ad_result[0]['sum(income_num)'];
			$ad_result_go = $co_model -> table('t_ad_income') -> where(array('cid' => $cid,'theday' => $theday)) -> select();
			
			$where['cid'] = $cid;
			$where['theday'] = $theday;
			$where['type'] = 2;
			mysql_query("SET AUTOCOMMIT=0");//设置为不自动提交，因为MYSQL默认立即执行
			mysql_query("BEGIN");//开始事务定义
			foreach($ad_result_go as $key => $val){
				$new_income_1 = $val['income_num']*$ratio_go;
				$new_income_2 = explode('.',$new_income_1);
				$new_income_3 = substr($new_income_2[1],0,2);
				$data_child['new_income'] = $new_income_2[0].'.'.$new_income_3; //砍掉小数点两位后数值
				$data_child['adjust_value'] = $_POST['adjust_value'];
				if((2&$val['income_show_type']) == 2){
					$data_child['income_show'] = $data_child['new_income'];
				}else{
					$data_child['income_show'] = 0;
				}
				$data_child['deduct_ratio'] = sprintf("%.2f", (($ad_result_go[0]['before_deduct_income'] - $data_child['new_income'])/$ad_result_go[0]['before_deduct_income'])*100);
				$data_child['update_tm'] = time();
				$where_ad['id'] = $val['id'];
				$where_ad['cid'] = $cid;
				$where_ad['theday'] = $theday;
				$child_result_go = $co_model -> update_ad($where_ad,$data_child);
				$child_result[] = $child_result_go;
				if ($child_result_go) {
					
				} else {
					$error_data['child'] = $data_child;	
					$error_sql['child'] = $co_model -> getLastSql();
					$errorInsert['child'] = true;
					break;
				}
			}

			$new_ad_result = $co_model -> table('t_ad_income') -> where(array('cid' => $cid,'theday' => $theday)) -> field('sum(new_income)') -> group('cid') -> select();
			$data['new_income'] = $new_ad_result[0]['sum(new_income)'];
			$data['update_tm'] = time();
			$data['deduct_ratio'] = sprintf("%.2f", (($yesterday_ad_result[0]['before_deduct_income'] - $new_ad_result[0]['sum(new_income)'])/$yesterday_ad_result[0]['before_deduct_income'])*100);
			$log_model = D('Cooperative.SysManager');
			$log_all_result = $co_model -> table('t_all_income') -> where($where) -> select();
			$log_all_need = $log_model -> logcheck(array('id' => $log_all_result[0]['id']),'t_all_income',array('adjust_value'=> $data['adjust_value']));
			if((2&$log_all_result[0]['income_show_type']) == 2){
				$data['income_show'] = $data['new_income'];
			}else{
				$data['income_show'] = 0;
			}
			$data['income_ratio'] = sprintf("%.2f",($new_ad_result[0]['sum(new_income)']/$yesterday_ad_result[0]['new_income'] - 1)*100);
			$result = $co_model -> update_all($where,$data);
			if ($result) {
					
			} else {
				$error_data['parent'] = $data;	
				$error_sql['parent'] = $co_model -> getLastSql();
				$errorInsert['parent'] = true;
			}
			
			if($errorIsert){
				$file_log = '/tmp/'.$path;
				file_put_contents($file_log,'错误数据：(父表：'.json_encode($error_data['parent']).',子表:'.json_encode($error_data['child']).');错误sql：(父表：'.json_encode($error_sql['parent']).',子表:'.$error_sql['child'].')');
				mysql_query("ROOLBACK");//判断当执行失败时回滚
			}
			mysql_query("COMMIT");//执行事务
			if($log_all_result[0]['adjust_value']){
				$type_name = "调整广告分成收入值(账号:{$user_result[0]['user_name']};渠道:{$channel_result[0]['chname']}),调整前{$log_all_need['adjust_value'][1]},调整后{$log_all_need['adjust_value'][2]}";
			}else{
				$type_name = "添加广告分成收入值(账号:{$user_result[0]['user_name']};渠道:{$channel_result[0]['chname']}),调整前:0,调整后:{$data['adjust_value']}";
			}
		}elseif($income_type == 4){
			//昨日时间
			$theday = date('Ymd',time() - 3600*24);
			//前天时间
			$yesterday = date('Ymd',time() - 3600*24*2);
			$game_result = $co_model -> table('t_game_income') -> where(array('cid' => $cid,'theday' => $theday)) -> field('sum(new_income),sum(before_deduct_income),sum(income_num)') -> group('cid') -> select();
			$all_result = $co_model -> table('t_all_income') -> where(array('cid' => $cid,'theday' => $theday,'type' => 4)) -> field('new_income,before_deduct_income') -> select();
			$data['adjust_value'] = $_POST['adjust_value'];

			if($_POST['adjust_value'] > $all_result[0]['before_deduct_income']){
				$this -> error("调整值不能大于收入值上限");
			}
			if($_POST['adjust_value'] < 0){
				$this -> error("调整值不能小于0");
			}
			$val_my = explode('.',trim($_POST['adjust_value']));
			if(strlen($val_my[1]) > 2){
				$this -> error("调整值格式错误");
			}
			if(preg_match_all("/[A-Za-z]/", $_POST['adjust_value'], $match)){
				$this -> error("调整值不可输入字母");
			}
	
			if(preg_match_all("/([\x81-\xfe][\x40-\xfe])/", $_POST['adjust_value'], $match)){
				$this -> error("调整值不可输入汉字");
			}
			$yesterday_where['_string'] = "theday = {$yesterday} and cid = {$cid} and type = 4";
			$yesterday_game_result = $co_model -> table('t_all_income') -> where($yesterday_where) -> field('new_income,before_deduct_income') -> select();
	
			$data['adjust_ratio'] = sprintf("%.2f",($_POST['adjust_value']/$yesterday_game_result[0]['new_income'] - 1)*100);
			$ratio_go = $_POST['adjust_value']/$game_result[0]['sum(income_num)'];
			$game_result_go = $co_model -> table('t_game_income') -> where(array('cid' => $cid,'theday' => $theday)) -> select();
			$where['cid'] = $cid;
			$where['theday'] = $theday;
			$where['type'] = 4;
			mysql_query("SET AUTOCOMMIT=0");//设置为不自动提交，因为MYSQL默认立即执行
			mysql_query("BEGIN");//开始事务定义
			foreach($game_result_go as $key => $val){
				$new_income_1 = $val['income_num']*$ratio_go;
				$new_income_2 = explode('.',$new_income_1);
				$new_income_3 = substr($new_income_2[1],0,2);
				$data_child['new_income'] = $new_income_2[0].'.'.$new_income_3; //砍掉小数点两位后数值
				$data_child['adjust_value'] = $_POST['adjust_value'];
				if((4&$val['income_show_type']) == 4){
					$data_child['income_show'] = $data_child['new_income'];
				}else{
					$data_child['income_show'] = 0;
				}
				$data_child['deduct_ratio'] = sprintf("%.2f", (($game_result_go[0]['before_deduct_income'] - $data_child['new_income'])/$game_result_go[0]['before_deduct_income'])*100);
				$data_child['update_tm'] = time();
				$where_game['id'] = $val['id'];
				$where_game['cid'] = $cid;
				$where_game['theday'] = $theday;
				$child_result_go = $co_model -> update_game($where_game,$data_child);
				$child_result[] = $child_result_go;
				if ($child_result_go) {
					
				} else {
					$error_data['child'] = $data_child;	
					$error_sql['child'] = $co_model -> getLastSql();
					$errorInsert['child'] = true;
					break;
				}
			}
	
			$new_game_result = $co_model -> table('t_game_income') -> where(array('cid' => $cid,'theday' => $theday)) -> field('sum(new_income)') -> group('cid') -> select();
			
			$data['new_income'] = $new_game_result[0]['sum(new_income)'];
			$data['update_tm'] = time();
			$data['deduct_ratio'] = sprintf("%.2f", (($yesterday_ad_result[0]['before_deduct_income'] - $new_game_result[0]['sum(new_income)'])/$yesterday_ad_result[0]['before_deduct_income'])*100);
			$data['income_ratio'] = sprintf("%.2f",($new_game_result[0]['sum(new_income)']/$yesterday_game_result[0]['new_income'] - 1)*100);
	
			$log_model = D('Cooperative.SysManager');
			$log_all_result = $co_model -> table('t_all_income') -> where($where) -> select();
			if((4&$log_all_result[0]['income_show_type']) == 4){
				$data['income_show'] = $data['new_income'];
			}else{
				$data['income_show'] = 0;
			}
			$log_all_need = $log_model -> logcheck(array('id' => $log_all_result[0]['id']),'t_all_income',array('adjust_value'=> $data['adjust_value']));
			$result = $co_model -> update_all($where,$data);
			if ($result) {
					
			} else {
				$error_data['parent'] = $data;	
				$error_sql['parent'] = $co_model -> getLastSql();
				$errorInsert['parent'] = true;
			}
			if($errorIsert){
				$file_log = '/tmp/'.$path;
				file_put_contents($file_log,'错误数据：(父表：'.json_encode($error_data['parent']).',子表:'.json_encode($error_data['child']).');错误sql：(父表：'.json_encode($error_sql['parent']).',子表:'.$error_sql['child'].')');
				mysql_query("ROOLBACK");//判断当执行失败时回滚
			}
			mysql_query("COMMIT");//执行事务
			if($log_all_result[0]['adjust_value']){
				$type_name = "调整游戏分成收入值(账号:{$user_result[0]['user_name']};渠道:{$channel_result[0]['chname']}),调整前{$log_all_need['adjust_value'][1]},调整后{$log_all_need['adjust_value'][2]}";
			}else{
				$type_name = "添加游戏分成收入值(账号:{$user_result[0]['user_name']};渠道:{$channel_result[0]['chname']}),调整前:0,调整后:{$data['adjust_value']}";
			}
		}
		$charge = $_POST['charge'];
		$channel_type = $_POST['channel_type'];
		$income_types = $_POST['income_types'];
		$account_name = $_POST['account_name'];
		$channel_name = $_POST['channel_name'];
		$edit_status = $_POST['edit_status'];
		if($charge){
			$go .= "/charge/{$charge}";
		}
		if($channel_type){
			$go .= "/channel_type/{$channel_type}";
		}

		if($income_types){
			$go .= "/income_types/{$income_types}";
		}
		if($account_name){
			$go .= "/account_name/{$account_name}";
		}
		if($channel_name){
			$go .= "/channel_name/{$channel_name}";
		}
		if($edit_status){
			$go .= "/edit_status/{$edit_status}";
		}
		
		if($child_result || $result){
			$this -> writelog($type_name);
			$this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Incomemanage/check_pending_list'.$go);
			$this -> success("修改成功");
		}else{
			$this -> error("修改失败");
		}
	}

	
	//收入统计
	function income_statistics(){
		$model = new Model();
		$co_model = D('Cooperative.Incomemanage');
		$admin_id = $_SESSION['admin']['admin_id'];
		$userid_arr_manage = $this -> cooperative_manager($admin_id);
		$charge = $_GET['charge'];
		if(isset($_GET['charge']) && !empty($_GET['charge'])){
			$where_user['_string'] = "charge_person = {$charge} and status = 1";
			$user_result = $co_model -> table('t_user') -> where($where_user) -> select();
			foreach($user_result as $key => $val){
				$userid_arr_user[] = $val['uid'];
			}
			$charge = $_GET['charge'];
		}elseif($_GET['charge_name']){
			$charge = $this -> search_charge($_GET['charge_name']);
			$where_user['_string'] = "charge_person = {$charge} and status = 1";
			$user_result = $co_model -> table('t_user') -> where($where_user) -> select();
			foreach($user_result as $key => $val){
				$userid_arr_user[] = $val['uid'];
			}
		}
		
		$channel_type = $_GET['channel_type'];
		if(isset($_GET['channel_type']) && !empty($_GET['channel_type'])){
			$where_channel_category['_string'] = " category_id = {$channel_type} and status = 1";
			$channel_category_result = $model -> table('sj_channel') -> where($where_channel_category) -> select();
			foreach($channel_category_result as $key => $val){
				$cid_arr .= $val['cid'].',';
			}	
			$cid_str = substr($cid_arr,0,strlen($cid_arr) - 1);
			$where_all .= " and cid in ({$cid_str})";
		}
		
		$income_type = $_GET['income_type'];
		if(isset($_GET['income_type']) && !empty($_GET['income_type'])){
			$where_all .= " and type = {$income_type}";
		}
		
		$channel_name = $_GET['channel_name'];
		if(isset($_GET['channel_name']) && !empty($_GET['channel_name'])){
			$where_channel['_string'] = " chname like '%{$channel_name}%' and status = 1";
			$channel_result = $model -> table('sj_channel') -> where($where_channel) -> select();
			foreach($channel_result as $key => $val){
				$cid_arrs .= $val['cid'].',';
			}
			$cid_strs = substr($cid_arrs,0,strlen($cid_arrs) - 1);
			$where_all .= " and cid in ({$cid_strs})";
		}
		
		$account_name = $_GET['account_name'];
	
		if(isset($_GET['account_name']) && !empty($_GET['account_name'])){
			$where_account['_string'] = " user_name like '%{$account_name}%'";
			$account_result = $co_model -> table('t_user') -> where($where_account) -> select();
			foreach($account_result as $key => $val){
				$userid_arr_account[] = $val['uid'];
			}
		}
		
		//根据负责人，登录账号，账号名称取交集确定账号范围
		if($userid_arr_user && $userid_arr_account){
			$userid_arr_all = array_intersect($userid_arr_manage,$userid_arr_user,$userid_arr_account);
		}elseif($userid_arr_user && !$userid_arr_account){
			if(!$account_name){
				$userid_arr_all = array_intersect($userid_arr_manage,$userid_arr_user);
			}else{
				$userid_arr_all = array();
			}
		}elseif($userid_arr_account && !$userid_arr_user){
			if(!$charge){
				$userid_arr_all = array_intersect($userid_arr_manage,$userid_arr_account);
			}else{
				$userid_arr_all = array();
			}
			
		}elseif(!$userid_arr_account && !$userid_arr_user){
			if(!$charge && !$account_name){
				$userid_arr_all = $userid_arr_manage;
			}else{
				$userid_arr_all = array();
			}
		}
		
		if($_GET['uid']){
			$get_uid = array($_GET['uid']);
			$userid_arr_all = array_intersect($userid_arr_all,$get_uid);
		}
		
		foreach($userid_arr_all as $key => $val){
			$userid_arr_all_str .= $val.',';
		}
		
		$userid_str = substr($userid_arr_all_str,0,strlen($userid_arr_all_str) - 1);
		
		$theday_start = date('Ymd',strtotime($_GET['start_tm']));
		$theday_end = date('Ymd',strtotime($_GET['end_tm']));
		if(isset($_GET['start_tm']) && isset($_GET['end_tm']) && !empty($_GET['start_tm']) && !empty($_GET['end_tm'])){
			if(strtotime($_GET['start_tm']) > strtotime($_GET['end_tm'])){
				$this -> error("开始时间不能大于结束时间");
			}
			$where_all .= " and theday >= {$theday_start} and theday <= {$theday_end}";
		}
		$yesterday = date('Ymd',time() - 3600*24);
		if($userid_str){
			$where_go['_string'] = "type !=0 and theday < {$yesterday} and userid in({$userid_str})".$where_all;
		}else{
			$where_go['_string'] = "type !=0 and theday < {$yesterday} and userid in (0)".$where_all;
		}
		

		if(isset($_GET['order_by_num']) && !empty($_GET['order_by_num'])){
			if($_GET['order_by_num'] == 1){
				$order = 'income_num ASC';
				$this -> assign('order_by_num',$_GET['order_by_num']);
			}elseif($_GET['order_by_num'] == 2){
				$order = 'income_num DESC';
				$this -> assign('order_by_num',$_GET['order_by_num']);
			}elseif($_GET['order_by_num'] == 3){
				$order = 'new_income ASC';
				$this -> assign('order_by_num',$_GET['order_by_num']);
			}elseif($_GET['order_by_num'] == 4){
				$order = 'new_income DESC';
				$this -> assign('order_by_num',$_GET['order_by_num']);
			}elseif($_GET['order_by_num'] == 5){
				$order = 'before_deduct_income ASC';
				$this -> assign('order_by_num',$_GET['order_by_num']);
			}elseif($_GET['order_by_num'] == 6){
				$order = 'before_deduct_income DESC';
				$this -> assign('order_by_num',$_GET['order_by_num']);
			}elseif($_GET['order_by_num'] == 7){
				$order = 'deduct_ratio ASC';
				$this -> assign('order_by_num',$_GET['order_by_num']);
			}elseif($_GET['order_by_num'] == 8){
				$order = 'deduct_ratio DESC';
				$this -> assign('order_by_num',$_GET['order_by_num']);
			}
		}else{
			$order = "theday DESC,type";
		}
		
		$count = $co_model -> table('t_all_income') -> where($where_go) -> order($order) -> count();
		import("@.ORG.Page");
		$param = http_build_query($_GET);
		$Page = new Page($count, 10, $param);
		$result = $co_model -> table('t_all_income') -> where($where_go) -> order($order) -> limit($Page->firstRow . ',' . $Page->listRows)-> select();

		foreach($result as $key => $val){
			$where_channel['_string'] =  "cid = {$val['cid']} and status = 1";
			$channel_result = $model -> table('sj_channel') -> where($where_channel) -> select();
			$val['chname'] = $channel_result[0]['chname'];
			$where_channel_category['_string'] = "category_id = {$channel_result[0]['category_id']} and status = 1";
			$channel_category_result = $model -> table('sj_channel_category') -> where($where_channel_category) -> select();
			$val['category_name'] = $channel_category_result[0]['name'];
			$where_user['_string'] = "uid = {$val['userid']}";
			$user_result = $co_model -> table('t_user') -> where($where_user) -> select();
			$charge_result = $co_model ->  table('t_charge') -> where(array('id' => $user_result[0]['charge_person'])) -> select();
			$val['user_name'] = $user_result[0]['user_name'];
			$val['charge_name'] = $charge_result[0]['charge_name'];
			$val['deduct_ratio'] = sprintf("%.2f",(($val['before_deduct_income'] - $val['new_income'])/$val['before_deduct_income'])*100);
			$result[$key] = $val;
		}
		
		//导出收入统计列表
		$derive = $_GET['derive'];
		if(isset($_GET['derive']) && $derive == 1){
			$result_go = $co_model -> table('t_all_income') -> where($where_go) -> order($order) -> select();
			foreach($result_go as $key => $val){
				$where_channel['_string'] =  "cid = {$val['cid']} and status = 1";
				$channel_result = $model -> table('sj_channel') -> where($where_channel) -> select();
				$val['chname'] = $channel_result[0]['chname'];
				$where_channel_category['_string'] = "category_id = {$channel_result[0]['category_id']} and status = 1";
				$channel_category_result = $model -> table('sj_channel_category') -> where($where_channel_category) -> select();
				$val['category_name'] = $channel_category_result[0]['name'];
				$where_user['_string'] = "uid = {$val['userid']}";
				$user_result = $co_model -> table('t_user') -> where($where_user) -> select();
				$charge_result = $co_model ->  table('t_charge') -> where(array('id' => $user_result[0]['charge_person'])) -> select();
				$val['user_name'] = $user_result[0]['user_name'];
				$val['charge_name'] = $charge_result[0]['charge_name'];
				$result_go[$key] = $val;
			}
			foreach($result_go as $key => $val){
				if($val['type'] == 1){
					$val['type_name'] = "安智市场激活";
				}elseif($val['type'] == 2){
					$val['type_name'] = "广告分成收入";
				}elseif($val['type'] == 4){
					$val['type_name'] = "游戏分成收入";
				}
				$val['theday'] = date('Y/m/d',strtotime($val['theday']));
				$file_str .= $val['theday'].','.$val['chname'].','.$val['category_name'].','.$val['user_name'].','.$val['charge_name'].','.$val['type_name'].','.$val['before_deduct_income'].','.$val['income_num'].','.$val['new_income'].','.$val['deduct_ratio']."\n";
			}
			$file_go = 'income_statistics_'.date('YmdHis').".csv";//文件名
			header( "Cache-Control: public" );
			header( "Pragma: public" );
			header("Content-type:application/vnd.ms-excel");
			header('Content-Disposition:attachment;filename='.$file_go.'.csv');
			header('Content-Type:APPLICATION/OCTET-STREAM');
			ob_start();
			$header_str =  iconv("UTF-8",'GBK',"日期,渠道名称,渠道类型,账号名称,负责人,收入类型,扣量前,原收入,新收入,扣量比例");
			$file_str_go=  iconv("UTF-8",'GBK',$file_str);
			echo $header_str."\n";
			echo $file_str_go;
			ob_end_flush();
			exit;
		}
		
		//查询渠道类型
		$channel_result = $model -> table('sj_channel_category') -> where(array('status' => 1)) -> select();
		$charge_result = $co_model -> table('t_charge') -> where(array('status' => 1)) -> select();
		$this -> assign('charge_result',$charge_result);
		$this -> assign('channel_category',$channel_result);
		$this -> assign('charge',$charge);
		$this -> assign('channel_type',$_GET['channel_type']);
		$this -> assign('income_type',$_GET['income_type']);
		$this -> assign('channel_name',$_GET['channel_name']);
		$this -> assign('account_name',$_GET['account_name']);
		$this -> assign('start_tm',$_GET['start_tm']);
		$this -> assign('end_tm',$_GET['end_tm']);
		$Page->setConfig('header', '篇记录');
		$Page->setConfig('first', '<<');
		$Page->setConfig('last', '>>');
		$show = $Page->show();
		
		$this->assign("page", $show);
		$this -> assign("result",$result);
		$this -> display("income_statistics");
		
		

	}
	
	
	
	//报警设置列表
	function warning_manage_list(){
		$co_model = D('Cooperative.Incomemanage');
		$model = new Model();
		$result = $co_model -> table('t_warning') -> where(array('status' => 1)) -> select();
		foreach($result as $key => $val){
			$where_admin['_string'] = "admin_user_id = {$val['update_admin_id']}";
			$admin_result = $model -> table('sj_admin_users') -> where($where_admin) -> select();
			if($val['target'] != 1){
				$top_go = explode('.',$val['top']);
				$below_go = explode('.',$val['below']);
				$val['top'] = $top_go[0];
				$val['below'] = $below_go[0];
			}
			$val['admin_user_name'] = $admin_result[0]['admin_user_name'];
			$result[$key] = $val;
		}
		
		$this -> assign("result",$result);
		$this -> display("warning_manage_list");	
		
	}
	
	//添加报警设置显示
	function add_warning_show(){
		$this -> display("add_warning_show");
	}
	
	//添加报警设置提交
	function add_warning_do(){
		$co_model = D('Cooperative.Incomemanage');
		$income_type = $_GET['income_type'];
		$target = trim($_GET['target']);
		$top = $_GET['top'];
		$below = $_GET['below'];
		$status = 1;
		$have_result = $co_model -> table('t_warning') -> where(array('income_type' => $income_type,'target' => $target,'status' => 1)) -> select();
		if(empty($income_type)){
			$this -> error("请选择收入类型");
		}
		if(empty($target)){
			$this -> error("请选择指标类型");
		}
		if($top == '' && $below == ''){
			$this -> error("请至少输入一项限制指标");
		}
		if($have_result){
			$this -> error("该报警指标已存在");
		}
		if($target == 1 && $top != ''){
			$top = sprintf("%.2f",$top);
		}else{
			if(strlen($top) > 8){
				$this -> error("上限值格式错误");
			}
		}
		if($target == 1 && $below != ''){
			$below = sprintf("%.2f",$below);
		}else{
			if(strlen($below) > 8){
				$this -> error("下限值格式错误");
			}
		}

		if($top != '' && $below != '' && ($top < $below)){
			$this -> error("下限数值不能大于上限数值");
		}
		if($top != '' && $below != ''){
			if(!is_numeric($top) || !is_numeric($below)){
				$this -> error("上限值或下限值格式错误");
			}
			$point_top = explode('.',$top);
			$point_below = explode('.',$below);
			if(strlen($point_top[1]) > 2 || strlen($point_below[1]) >2){	
				$this -> error("上限值或下限值格式错误");
			}
		}
		$data['income_type'] = $income_type;
		$data['target'] = $target;
		$have_result = $co_model -> table('t_warning') -> where(array('income_type' => $income_type,'target' => $target,'status' => 1)) -> select();
		if($have_result){
			$this -> error("该项报警条件已存在");
		}
		if($data['top'] != ''){
			$data['top'] = $top;
		}else{
			$data['top'] = null;
		}
		if($data['below'] != ''){
			$data['below'] = $below;
		}else{
			$data['below'] = null;
		}
		$data['below'] = $below;
		$data['top'] = $top;
		$data['status'] = $status;
		$data['update_admin_id'] = $_SESSION['admin']['admin_id'];
		$data['update_tm'] = time();
		$result = $co_model -> add_warning($data);
		if($income_type == 1){
			$income = '安智市场激活';
		}elseif($income_type == 2){
			$income = '广告分成收入';
		}else{
			$income = '游戏分成收入';
		}
		if($target == 1){
			$mark = '日环比率';
		}elseif($target == 2){
			$mark = '原收入';
		}elseif($target == 3){
			$mark = '新收入';
		}
		if($result){
			$this -> writelog("已添加{$income}的{$mark}报警设置上限值为{$top},下限为{$below}");
			$this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . "/Incomemanage/warning_manage_list");
			$this -> success("添加成功");
		}
	}
	
	//编辑报警设置显示
	function update_warning_show(){
		$co_model = D('Cooperative.Incomemanage');
		$id = $_GET['id'];
		$where['id'] = $id;
		$result = $co_model -> table('t_warning') -> where($where) -> select();
		if($result[0]['target'] != 1){
			$top_go = explode('.',$result[0]['top']);
			$below_go = explode('.',$result[0]['below']);
			$top = $top_go[0];
			$below = $below_go[0];
			$result[0]['top'] = $top;
			$result[0]['below'] = $below;
		}
		$this -> assign("result",$result);
		$this -> display();
	}
	
	
	//编辑报警设置提交
	function update_warning_do(){
		$co_model = D('Cooperative.Incomemanage');
		$id = $_POST['id'];
		$my_result = $co_model -> table('t_warning') -> where(array('id' => $id)) -> select();
		$top = $_POST['top'];
		$target = $_POST['target'];
		$below = $_POST['below'];
		
		if($top == '' && $below == ''){
			$this -> error("请至少输入一项限制指标");
		}
		if($target == 1 && $top != ''){
			$top = sprintf("%.2f",$top);
		}else{
			if(strlen($top) > 8){
				$this -> error("上限值格式错误");
			}
		}
		if($target == 1 && $below != ''){
			$below = sprintf("%.2f",$below);
		}else{
			if(strlen($below) > 8){
				$this -> error("下限值格式错误");
			}
		}
		if($top != '' && $below != '' && ($top < $below)){
			$this -> error("下限数值不能大于上限数值");
		}
		
		if($top != '' && $below != '' && $target != 1){
			if(!is_numeric($top) || !is_numeric($below)){
				$this -> error("上限值或下限值格式错误");
			}
			$point_top = explode('.',$top);
			$point_below = explode('.',$below);
			if(strlen($point_top[1]) > 0 || strlen($point_below[1]) > 0){
				$this -> error("上限值或下限值格式错误");
			}
		}
	
		if($top != ''){
			$data['top'] = $top;
		}else{
			$data['top'] = null;
		}
	
		if($below != ''){
			$data['below'] = $below;
		}else{
			$data['below'] = null;
		}
		$where['id'] = $id;
		$data['update_admin_id'] = $_SESSION['admin']['admin_id'];
		$data['update_tm'] = time();
		
		if($income_type == 1){
			$income = '安智市场激活';
		}elseif($income_type == 2){
			$income = '广告分成收入';
		}else{
			$income = '游戏分成收入';
		}
		
		if($target == 1){
			$mark = '日环比率';
		}elseif($target == 2){
			$mark = '原收入';
		}elseif($target == 3){
			$mark = '新收入';
		}
		
		$log_model = D('Cooperative.SysManager');
		$log_all_result = $co_model -> table('t_warning') -> where($where) -> select();
		$log_all_need = $log_model -> logcheck(array('id' => $log_all_result[0]['id']),'t_warning',array('top'=> $data['top'],'below' => $data['below']));
		foreach($log_all_need as $key => $val){
			$msg .= "{$val[0]}(编辑前:{$val[1]};编辑后{$val[2]}),";
		}
		$result = $co_model -> update_warning($where,$data);
		
		if($result){
			$this -> writelog("已编辑{$income}的{$mark}报警设置".$msg);
			$this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . "/Incomemanage/warning_manage_list");
			$this -> success("编辑成功");
		}else{
			$this -> error("编辑失败");
		}
	}
	
	
	//删除报警设置
	function delete_warning(){
		$co_model = D('Cooperative.Incomemanage');
		$id = $_GET['id'];
		$data['status'] = 0;
		$where['id'] = $id;
		$been_result = $co_model -> table('t_warning') -> where(array('id' => $id)) -> select();
		$income = array(1=>'安智市场激活收入',2=>'广告分成收入',3=>'游戏分成收入');
		$target_name = array(1=>'日环比率',2=>'原收入',3=>'新收入');
		$result = $co_model -> update_warning($where,$data);
		if($result){
			$this -> writelog("已删除{$income[$been_result[0]['income_type']]}的{$target_name[$been_result[0]['target']]}的报警设置");
			$this -> assign('jumpUrl','/index.php/'.GROUP_NAME.'/Incomemanage/warning_manage_list');
			$this -> success("删除成功");
		}
		
	}
	
	//检查是否保留指定小数点，范围是否正确
	protected function check_point($val,$point,$max){
		$str = explode(',',$val);
		$length = strlen($str[1]);
		if($length > $point || $val > $max){
			return true;
		}else{
			return false;
		}
	
	}
	
	function search_charge($charge_name){
		$co_model = D('Cooperative.Incomemanage');
		$result = $co_model -> table('t_charge') -> where(array('charge_name' => $charge_name)) -> select();

		return $result[0]['id'];
	}
}