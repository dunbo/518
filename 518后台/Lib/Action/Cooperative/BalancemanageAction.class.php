<?php
class BalancemanageAction extends CommonAction{

	//业务审核列表
	function operation_check_list(){
		$co_model = D('Cooperative.Incomemanage');
		
		//统计按月结算信息（一个自然月一条）
		$admin_id = $_SESSION['admin']['admin_id'];
		$userid_arr = $this -> cooperative_manager($admin_id);
/* 		foreach($userid_arr as $key => $val){
			$active_count_sql = "select FROM_UNIXTIME(UNIX_TIMESTAMP(theday), '%Y%m'),userid,sum(new_income),settlement_patterns,income_show_type from t_all_income where userid = {$val} and type = 1 group by FROM_UNIXTIME(UNIX_TIMESTAMP(theday), '%Y%m') order by FROM_UNIXTIME(UNIX_TIMESTAMP(theday), '%Y%m') DESC ";
			$active_count = $co_model -> query($active_count_sql);  //账号每月激活收入
			
			foreach($active_count as $k => $v){
				$ad_count_sql = "select FROM_UNIXTIME(UNIX_TIMESTAMP(theday), '%Y%m'),userid,sum(new_income) from t_all_income where userid = {$v['userid']} and type = 2 and FROM_UNIXTIME(UNIX_TIMESTAMP(theday), '%Y%m') = {$v["FROM_UNIXTIME(UNIX_TIMESTAMP(theday), '%Y%m')"]} group by FROM_UNIXTIME(UNIX_TIMESTAMP(theday), '%Y%m') order by FROM_UNIXTIME(UNIX_TIMESTAMP(theday), '%Y%m') DESC ";
				$ad_count = $co_model -> query($ad_count_sql);
				$game_count_sql = "select FROM_UNIXTIME(UNIX_TIMESTAMP(theday), '%Y%m'),userid,sum(new_income) from t_all_income where userid = {$v['userid']} and type = 2 and FROM_UNIXTIME(UNIX_TIMESTAMP(theday), '%Y%m') = {$v["FROM_UNIXTIME(UNIX_TIMESTAMP(theday), '%Y%m')"]} group by FROM_UNIXTIME(UNIX_TIMESTAMP(theday), '%Y%m') order by FROM_UNIXTIME(UNIX_TIMESTAMP(theday), '%Y%m') DESC ";
				$game_count = $co_model -> query($game_count_sql);
				$data['user_id'] = $v['userid'];
				$data['balance_period'] = $v["FROM_UNIXTIME(UNIX_TIMESTAMP(theday), '%Y%m')"];
				$data['active_income'] = $v['sum(new_income)'];
				$data['income_show_type'] = $v['income_show_type'];
				$data['ad_income'] = $ad_count[0]['sum(new_income)'];
				$data['game_income'] = $ad_count[0]['sum(new_income)'];
				$data['salvation'] = 0;
				$data['settlement_patterns'] = $v['settlement_patterns'];
				$data['status'] = 1;
				$my_result = $co_model -> add_operation($data);
			}
		}
		
		$need_result = $co_model -> table('t_settle_account') -> where(array('status' => 1)) -> select();
		$tax_result = $co_model -> table('t_init_config') -> select(); //税率
		foreach($need_result as $key => $val){
			$where_user['uid'] = $val['user_id'];
			$where_user['status'] = 1;
			$user_result = $co_model -> table('t_account') -> where($where_user) -> select();
			if($user_result[0]['account_property'] == 1){
				$data_need['tax_rate'] = $tax_result[0]['corporate_pre'];
			}else{
				$data_need['tax_rate'] = $tax_result[0]['preson_pre'];
			}
			
			if($val['settlement_patterns'] == 1){	//合计值A
				$data_need['amount'] = $val['active_income'];
			}elseif($val['settlement_patterns'] == 2){	//合计值B
				$data_need['amount'] = $val['ad_income'];
			}elseif($val['settlement_patterns'] == 3){	//合计值C
				$data_need['amount'] = $val['game_income'];
			}elseif($val['settlement_patterns'] == 4){	//合计值A+B
				$data_need['amount'] = $val['active_income'] + $val['ad_income'];
			}elseif($val['settlement_patterns'] == 5){	//合计值A+C
				$data_need['amount'] = $val['active_income'] + $val['game_income'];
			}elseif($val['settlement_patterns'] == 6){	//合计值B+C
				$data_need['amount'] = $val['ad_income'] + $val['game_income'];
			}elseif($val['settlement_patterns'] == 7){	//合计值A+B+C
				$data_need['amount'] = $val['active_income'] + $val['ad_income'] + $val['game_income'];
			}elseif($val['settlement_patterns'] == 8){	//最大值A与B
				if($val['active_income'] >= $val['ad_income']){
					$data_need['amount'] = $val['active_income'];
				}else{
					$data_need['amount'] = $val['ad_income'];
				}
			}elseif($val['settlement_patterns'] == 9){	//最大值A与C
				if($val['active_income'] >= $val['game_income']){
					$data_need['amount'] = $val['active_income'];
				}else{
					$data_need['amount'] = $val['game_income'];
				}
			}elseif($val['settlement_patterns'] == 10){	//最大值B与C
				if($val['ad_income'] >= $val['game_income']){
					$data_need['amount'] = $val['ad_income'];
				}else{
					$data_need['amount'] = $val['game_income'];
				}
			}elseif($val['settlement_patterns'] == 11){	//最大值A与B+C
				if($val['active_income'] >= ($val['game_income'] + $val['ad_income'])){
					$data_need['amount'] = $val['active_income'];
				}else{
					$data_need['amount'] = $val['game_income'] + $val['ad_income'];
				}
			}elseif($val['settlement_patterns'] == 12){	//最大值B与A+C
				if($val['ad_income'] >= ($val['game_income'] + $val['active_income'])){
					$data_need['amount'] = $val['ad_income'];
				}else{
					$data_need['amount'] = $val['game_income'] + $val['active_income'];
				}
			}elseif($val['settlement_patterns'] == 13){	//最大值C与A+B
				if($val['game_income'] >= ($val['ad_income'] + $val['active_income'])){
					$data_need['amount'] = $val['game_income'];
				}else{
					$data_need['amount'] = $val['ad_income'] + $val['active_income'];
				}
			}
			
			$data_need['pre_tax'] = $data_need['amount'] + $val['salvation'];
			$data_need['after_tax'] = $data_need['pre_tax'] - $data_need['pre_tax']*$data_need['tax_rate'];
			$data_need['account_name'] = $user_result[0]['account_name'];
			$data_need['bank_name'] = $user_result[0]['bank_name'];
			$data_need['account_nature'] = $user_result[0]['account_property'];
			$data_need['opening_bank'] = $user_result[0]['bank_addr'];
			$data_need['bank_addr'] = $user_result[0]['bank_location'];
			$data_need['bank_account'] = $user_result[0]['bank_account'];
			$data_need['card_url'] = $user_result[0]['bank_file'];
			$data_need['min_pay'] = $user_result[0]['min_balance'];
			$where_need['user_id'] = $val['user_id'];
			$where_need['balance_period'] = $val['balance_period'];
			$update_result = $co_model -> update_operation($where_need,$data_need);
		}
		 */
		if(isset($_GET['balance_period']) && !empty($_GET['balance_period'])){
			$where_all .= " and balance_period = {$_GET['balance_period']}";
		}
		if(isset($_GET['settlement_patterns']) && !empty($_GET['settlement_patterns'])){
			$where_all .= " and settlement_patterns = {$_GET['settlement_patterns']}";
		}
		if(isset($_GET['username']) && !empty($_GET['username'])){
			$where_user['_string'] = "user_name like '%{$_GET['username']}%'";
			$user_result = $co_model -> table('t_user') -> where($where_user) -> select();
			foreach($user_result as $key => $val){
				$userid_arr_username[] = $val['uid'];
			}
		}
	
		if(isset($_GET['charge']) && !empty($_GET['charge'])){
			$where_charge['_string'] = "charge_person = {$_GET['charge']}";
			$user_result = $co_model -> table('t_user') -> where($where_charge) -> select();
			foreach($user_result as $key => $val){
				$userid_arr_charge[] = $val['uid'];
			}
		}
		if(isset($_GET['salvation']) && !empty($_GET['salvation'])){
			if($_GET['salvation'] == 1){
				$where_all .= " and salvation != 0";
			}else{
				$where_all .= " and salvation = 0";
			}
		}
		
		if(isset($_GET['user_id']) && !empty($_GET['user_id'])){
			$where_all .= " and user_id = {$_GET['user_id']}";
		}
		
		if($userid_arr_username && $userid_arr_charge){
			$userid_arr_all = array_intersect($userid_arr_username,$userid_arr,$userid_arr_charge);
		}elseif($userid_arr_username && !$userid_arr_charge){
			if(empty($_GET['charge'])){
				$userid_arr_all = array_intersect($userid_arr_username,$userid_arr);
			}else{
				$userid_arr_all = array();
			}
		}elseif($userid_arr_charge && !$userid_arr_username){
			if(empty($_GET['username'])){
				$userid_arr_all = array_intersect($userid_arr,$userid_arr_charge);
			}else{
				$userid_arr_all = array();
			}
		}elseif(!$userid_arr_charge && !$userid_arr_username){
			if(empty($_GET['username']) && empty($_GET['charge'])){
				$userid_arr_all = $userid_arr;
			}else{
				$userid_arr_all = array();
			}
		}
	
		foreach($userid_arr_all as $key => $val){
			$userid_arr_all_str .= $val.',';
		}
		
		$userid_str = substr($userid_arr_all_str,0,strlen($userid_arr_all_str) - 1);
		if($userid_str && !$_GET['uid']){
			$where['_string'] = "status = 1 and user_id in ({$userid_str})".$where_all;
		}elseif(($userid_str || !$userid_str) && $_GET['uid']){
			$where['_string'] = "status = 1 and user_id = {$_GET['uid']}".$where_all;
		}else{
			$where['_string'] = "status = 1 and user_id in (0)".$where_all;
		}
		$count = $co_model -> table('t_settle_account') -> where($where) -> count();
		import("@.ORG.Page");
		$param = http_build_query($_GET);
		$Page = new Page($count, 10, $param);
		$result = $co_model -> table('t_settle_account') -> where($where) -> limit($Page->firstRow . ',' . $Page->listRows) -> order('balance_period DESC,after_tax DESC') -> select();
	
		foreach($result as $key => $val){
			$where_username['uid'] = $val['user_id'];
			$username_result = $co_model -> table('t_user') -> where($where_username) -> select();
			$val['user_name'] = $username_result[0]['user_name'];
			$val['balance_period'] = date('Y年m月',strtotime($val['balance_period'].'01'));
			$result[$key] = $val;
		}
		
		$charge_result = $co_model -> table('t_charge') -> where(array('status' => 1)) -> select();
		$balance_period_result = $co_model -> table('t_settle_account') -> order('balance_period DESC') -> select();

		foreach($balance_period_result as $key => $val){
			if($val['balance_period'] !=0 ){
				$balance_period_arr_go[] = $val['balance_period'];
			}
		}
	
		$balance_period_arr = array_unique($balance_period_arr_go);
					
		foreach($balance_period_arr as $key => $val){
			$v['value_go'] = $val;
			$v['time_go'] = date('Y年m月',strtotime($val.'01'));
			$balance_period_need[$key] = $v;
		}
		$this -> assign('balance_period_need',$balance_period_need);
		$this -> assign('charge_result',$charge_result);
		$this -> assign("balance_period",$_GET['balance_period']);
		$this -> assign("settlement_patterns",$_GET['settlement_patterns']);
		$this -> assign("username",$_GET['username']);
		$this -> assign("charge",$_GET['charge']);
		$this -> assign("salvation",$_GET['salvation']);
		$Page->setConfig('header', '篇记录');
		$Page->setConfig('first', '<<');
		$Page->setConfig('last', '>>');
		$show = $Page->show();
		$this->assign("page", $show);
		$this -> assign("result",$result);
		$this -> display();
	
	}
	
	//编辑业务审核账号显示
	function edit_account_show(){
		$co_model = D('Cooperative.Incomemanage');
		$id = $_GET['id'];
		$where['id'] = $id;
		$where['status'] = 1;
		$result = $co_model -> table('t_settle_account') -> where($where) -> select();
		//控制可编辑的结算模式选项
		$income_show_type = $co_model -> table('t_account') -> where(array('uid' => $result[0]['user_id'])) -> select();
		$first_select = $_GET['first_select'];
		if($first_select == 1){
			if($income_show_type[0]['income_reveal'] == 1){ //A
				$settle_type = array(1=>'A');
			}elseif($income_show_type[0]['income_reveal'] == 2){ //B
				$settle_type = array(2=>'B');
			}elseif($income_show_type[0]['income_reveal'] == 4){ //C
				$settle_type = array(3=>'C');
			}elseif($income_show_type[0]['income_reveal'] == 3){ //A,B
				$settle_type = array(1=>'A',2=>'B',4=>'A+B');
			}elseif($income_show_type[0]['income_reveal'] == 5){ //A,C
				$settle_type = array(1=>'A',3=>'C',5=>'A+C');
			}elseif($income_show_type[0]['income_reveal'] == 6){ //B,C
				$settle_type = array(2=>'B',3=>'C',6=>'B+C');
			}elseif($income_show_type[0]['income_reveal'] == 7){ //A,B,C
				$settle_type = array(1=>'A',2=>'B',3=>'C',4=>'A+B',5=>'A+C',6=>'B+C',7=>'A+B+C');
			}
		}elseif($first_select == 2){
			if($income_show_type[0]['income_reveal'] == 3){ //A,B
				$settle_type = array(8=>'A与B');
			}elseif($income_show_type[0]['income_reveal'] == 5){ //A,C
				$settle_type = array(9=>'A与C');
			}elseif($income_show_type[0]['income_reveal'] == 6){ //B,C
				$settle_type = array(10=>'B与C');
			}elseif($income_show_type[0]['income_reveal'] == 7){ //A,B,C
				$settle_type = array(11=>'A与B+c',12=>'B与A+C',13=>'C与A+B');
			}
		}
		
		$balance_period = $_GET['balance_period'];
		$settlement_patterns = $_GET['settlement_patterns'];
		$username = $_GET['username'];
		$charge = $_GET['charge'];
		$salvation = $_GET['salvation'];
		
		$this -> assign('balance_period',$balance_period);
		$this -> assign('settlement_patterns',$settlement_patterns);
		$this -> assign('username',$username);
		$this -> assign('charge',$charge);
		$this -> assign('salvation',$salvation);
		$this -> assign("settle_type",$settle_type);
		$this -> assign("result",$result);
		$this -> display();
	
	}
	
	//编辑业务审核账号提交
	function edit_account_do(){
		$co_model = D('Cooperative.Incomemanage');
		$id = $_POST['id'];
		$settle = $_POST['my_settle'];
		$been_result  = $co_model -> table('t_settle_account') -> where(array('id' => $id)) -> select();
		if(isset($_POST['settlement_patterns']) && !empty($_POST['settlement_patterns'])){
			$settlement_patterns = $_POST['settlement_patterns'];
		}else if(isset($_POST['settlement_patterns_1']) && !empty($_POST['settlement_patterns_1'])){
			$settlement_patterns = $_POST['settlement_patterns_1'];
		}

		if(!isset($settlement_patterns) || empty($settlement_patterns)){
			$this -> error('请选择结算模式');
		}
		
		$income_show_start = $been_result[0]['balance_period'].'01';
		$income_show_end = date('Ymd',strtotime("+1 month",strtotime($income_show_start)));
		$where_income_show['_string'] = "userid = {$been_result[0]['user_id']} and theday >= {$income_show_start} and theday < {$income_show_end}";
		$income_show_result = $co_model -> table('t_all_income') -> where($where_income_show) -> field('sum(income_show)') -> group('type') -> select();
	
		if($settle == 1){
			$data['settlement_patterns'] = $settlement_patterns;
			if($settlement_patterns == 1){
				$settlement_patterns_do = "A";
				$data['amount'] =  $income_show_result[0]['sum(income_show)'];
				$data['pre_tax'] = $data['amount'] + $been_result[0]['salvation'];
				$data['after_tax'] = $data['pre_tax'] - $data['pre_tax']*$been_result[0]['tax_rate']/100;
			}
			if($settlement_patterns == 2){
				$settlement_patterns_do = "B";
				$settlement_patterns_do = "A";
				$data['amount'] =  $income_show_result[1]['sum(income_show)'];
				$data['pre_tax'] = $data['amount'] + $been_result[0]['salvation'];
				$data['after_tax'] = $data['pre_tax'] - $data['pre_tax']*$been_result[0]['tax_rate']/100;
			}
			if($settlement_patterns == 3){
				$settlement_patterns_do = "C";
				$data['amount'] =  $income_show_result[2]['sum(income_show)'];
				$data['pre_tax'] = $data['amount'] + $been_result[0]['salvation'];
				$data['after_tax'] = $data['pre_tax'] - $data['pre_tax']*$been_result[0]['tax_rate']/100;
			}
			if($settlement_patterns == 4){
				$settlement_patterns_do = "A+B";
				$data['amount'] =  $income_show_result[0]['sum(income_show)'] + $income_show_result[1]['sum(income_show)'];
				$data['pre_tax'] = $data['amount'] + $been_result[0]['salvation'];
				$data['after_tax'] = $data['pre_tax'] - $data['pre_tax']*$been_result[0]['tax_rate']/100;
			}
			if($settlement_patterns == 5){
				$settlement_patterns_do = "A+C";
				$data['amount'] =  $income_show_result[0]['sum(income_show)'] + $income_show_result[2]['sum(income_show)'];
				$data['pre_tax'] = $data['amount'] + $been_result[0]['salvation'];
				$data['after_tax'] = $data['pre_tax'] - $data['pre_tax']*$been_result[0]['tax_rate']/100;
			}
			if($settlement_patterns == 6){
				$settlement_patterns_do = "B+C";
				$data['amount'] =  $income_show_result[1]['sum(income_show)'] + $income_show_result[2]['sum(income_show)'];
				$data['pre_tax'] = $data['amount'] + $been_result[0]['salvation'];
				$data['after_tax'] = $data['pre_tax'] - $data['pre_tax']*$been_result[0]['tax_rate']/100;
			}
			if($settlement_patterns == 7){
				$settlement_patterns_do = "A+B+C";
				$data['amount'] = $income_show_result[0]['sum(income_show)'] + $income_show_result[1]['sum(income_show)'] + $income_show_result[2]['sum(income_show)'];
				$data['pre_tax'] = $data['amount'] + $been_result[0]['salvation'];
				$data['after_tax'] = $data['pre_tax'] - $data['pre_tax']*$been_result[0]['tax_rate']/100;
			}
		}else{
			$data['settlement_patterns'] = $settlement_patterns;
			if($settlement_patterns == 8){
				$settlement_patterns_do = "A与B";
				if($income_show_result[0]['sum(income_show)'] >= $income_show_result[1]['sum(income_show)']){
					$data['amount'] = $income_show_result[0]['sum(income_show)'];
				}else{
					$data['amount'] = $income_show_result[1]['sum(income_show)'];
				}
				$data['pre_tax'] = $data['amount'] + $been_result[0]['salvation'];
				$data['after_tax'] = $data['pre_tax'] - $data['pre_tax']*$been_result[0]['tax_rate']/100;
			}
			if($settlement_patterns == 9){
				$settlement_patterns_do = "A与C";
				if($income_show_result[0]['sum(income_show)'] >= $income_show_result[2]['sum(income_show)']){
					$data['amount'] = $income_show_result[0]['sum(income_show)'];
				}else{
					$data['amount'] = $income_show_result[2]['sum(income_show)'];
				}
				$data['pre_tax'] = $data['amount'] + $been_result[0]['salvation'];
				$data['after_tax'] = $data['pre_tax'] - $data['pre_tax']*$been_result[0]['tax_rate']/100;
			}
			if($settlement_patterns == 10){
				$settlement_patterns_do = "B与C";
				if($income_show_result[1]['sum(income_show)'] >= $income_show_result[2]['sum(income_show)']){
					$data['amount'] = $income_show_result[1]['sum(income_show)'];
				}else{
					$data['amount'] = $income_show_result[2]['sum(income_show)'];
				}

				$data['pre_tax'] = $data['amount'] + $been_result[0]['salvation'];
				$data['after_tax'] = $data['pre_tax'] - $data['pre_tax']*$been_result[0]['tax_rate']/100;
			}
			if($settlement_patterns == 11){
				$settlement_patterns_do = "A与B+C";
				if($income_show_result[0]['sum(income_show)'] >= ($income_show_result[1]['sum(income_show)'] + $income_show_result[2]['sum(income_show)'])){
					$data['amount'] = $income_show_result[0]['sum(income_show)'];
				}else{
					$data['amount'] = $income_show_result[1]['sum(income_show)'] + $income_show_result[2]['sum(income_show)'];
				}
				$data['pre_tax'] = $data['amount'] + $been_result[0]['salvation'];
				$data['after_tax'] = $data['pre_tax'] - $data['pre_tax']*$been_result[0]['tax_rate']/100;
			}
			if($settlement_patterns == 12){
				$settlement_patterns_do = "B与A+C";
				if($income_show_result[1]['sum(income_show)'] >= ($income_show_result[0]['sum(income_show)'] + $income_show_result[2]['sum(income_show)'])){
					$data['amount'] = $income_show_result[1]['sum(income_show)'];
				}else{
					$data['amount'] = $income_show_result[0]['sum(income_show)'] + $income_show_result[2]['sum(income_show)'];
				}
				$data['pre_tax'] = $data['amount'] + $been_result[0]['salvation'];
				$data['after_tax'] = $data['pre_tax'] - $data['pre_tax']*$been_result[0]['tax_rate']/100;
			}
			if($settlement_patterns == 13){
				$settlement_patterns_do = "C与A+B";
				if($income_show_result[2]['sum(income_show)'] >= ($income_show_result[0]['sum(income_show)'] + $income_show_result[1]['sum(income_show)'])){
					$data['amount'] = $income_show_result[2]['sum(income_show)'];
				}else{
					$data['amount'] = $income_show_result[0]['sum(income_show)'] + $income_show_result[1]['sum(income_show)'];
				}
				$data['pre_tax'] = $data['amount'] + $been_result[0]['salvation'];
				$data['after_tax'] = $data['pre_tax'] - $data['pre_tax']*$been_result[0]['tax_rate']/100;
			}
		}
		
		$data['account_nature'] = $_POST['account_nature'];
		if(empty($_POST['account_nature'])){
			$this -> error("请选择账号属性");
		}
		if($_POST['account_nature'] == 1){
			$account_nature_go = "企业";
		}else{
			$account_nature_go = "个人";
		}
		$data['account_name'] = $_POST['account_name'];
		if(empty($_POST['account_name'])){
			$this -> error("请输出户名");
		}
		if(strlen($_POST['account_name']) < 2 && strlen($_POST['account_name']) > 50){
			$this -> error("户名请输入2-50个字符内的中文、英文大小写及数字");
		}
		$data['bank_name'] = $_POST['bank_name'];
		if(empty($_POST['bank_name'])){
			$this -> error("请输入所属银行");
		}
		if(strlen($_POST['bank_name']) < 2 && strlem($_POST['bank_name']) > 50){
			$this -> error("所属银行请输入2-50个字符内的中文、英文大小写及数字");
		}
		
		$data['opening_bank'] = $_POST['opening_bank'];
		if(empty($_POST['opening_bank'])){
			$this -> error("请输入开户行");
		}
		if(strlen($_POST['opening_bank']) < 2 && strlem($_POST['opening_bank']) > 50){
			$this -> error("开户行请输入2-50个字符内的中文、英文大小写及数字");
		}
		
		$data['bank_addr'] = $_POST['bank_addr'];
		if(empty($_POST['bank_addr'])){
			$this -> error("请输入开户行所在地");
		}
		if(strlen($_POST['bank_addr']) < 2 && strlem($_POST['bank_addr']) > 100){
			$this -> error("开户行所在地请输入2-50个字符内的中文、英文大小写及数字");
		}
		
		$data['bank_account'] = $_POST['bank_account'];
		if(empty($_POST['bank_account']) || !isset($_POST['bank_account'])){
			$this -> error("请输入银行账号");
		}
		if(strlen($_POST['bank_account']) < 10 && strlen($_POST['bank_account']) > 20){
			$this -> error("银行账号请输入10-20个字符内的数字");
		}
		
		$card_pic = $_FILES['card_pic'];
		if(!empty($_FILES['card_pic']['size'])){
			if($card_pic['size'] > 1024*1024){
				$this -> error("银行卡扫描件尺寸大于1M");
			}
	
			if($card_pic['type'] != 'image/jpg' && $card_pic['type'] != 'image/png' && $card_pic['type'] != 'image/jpeg'){
				$this -> error("银行卡扫描件格式错误");
			}
			
			$config = array(
				'multi_config' => array(
					'card_pic' => array(
						'savepath' => UPLOAD_PATH. '/img/'. $path,
						'saveRule' => 'getmsec'
					),
				),
			);
			$list = $this -> _uploadapk(0, $config);
			$news_url = $list['image'][0]['url'];
			$data['card_url'] = $news_url;
		}
		if($data['card_url']){
			$msg = ",银行卡扫描件为{$data['card_url']}";
		}
		$where['id'] = $id;
		$where['status'] = 1;
		$log_model = D('Cooperative.SysManager');
		$bef_result = $co_model -> table('t_settle_account') -> where($where) -> select();
		$log_all_need = $log_model -> logcheck(array('id' => $bef_result[0]['id']),'t_settle_account',array('settlement_patterns'=> $data['settlement_patterns'],'account_nature' => $data['account_nature'],'account_name' => $data['account_name'],'bank_name' => $data['bank_name'],'opening_bank' => $data['opening_bank'],'bank_addr' => $data['bank_addr'],'bank_account' => $data['bank_account'],'card_url' => $data['card_url']));
	
		foreach($log_all_need as $key => $val){
			$msg .= "{$val[0]}(编辑前:{$val[1]};编辑后{$val[2]}),";
		}
		$user_result = $co_model -> table('t_user') -> where(array('uid' => $bef_result[0]['user_id'])) -> select();
		$data['update_tm'] = time();
		$result = $co_model -> update_operation($where,$data);
		$account_type = array(1=>'企业',2=>'个人');
		$settle_name = array(1=>'A',2=>'B',3=>'C',4=>'A+B',5=>'A+C',6=>'B+C',7=>'A+B+C',8=>'A与B',9=>'A与C',10=>'B与C',11=>'A与B+C',12=>'B与A+C',13=>'C与A+B');
		
		$balance_period = $_POST['balance_period'];
		$settlement_patternss = $_POST['settlement_patternss'];
		$username = $_POST['username'];
		$charge = $_POST['charge'];
		$salvation = $_POST['salvation'];
		if($balance_period){
			$go .= "/balance_period/{$balance_period}";
		}
		if($settlement_patternss){
			$go .= "/settlement_patterns/{$settlement_patternss}";
		}
		if($username){
			$go .= "/username/{$username}";
		}
		if($charge){
			$go .= "/charge/{$charge}";
		}
		if($salvation){
			$go .= "/salvation/{$salvation}";
		}
		if($result){
			$this -> writelog("编辑业务审核列表记录(账号:{$user_result[0]['user_name']},结算周期:{$bef_result[0]['balance_period']})".$msg);
			$this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Balancemanage/operation_check_list'.$go);
			$this -> success("编辑成功");
		}else{
			$this -> error("编辑失败");
		}
	}

	//业务审核审核显示
	function operation_check_show(){
		
		$co_model = D('Cooperative.Incomemanage');
		$id = $_GET['id'];
		$where['id'] = $id;
		$where['status'] = 1;
		$result = $co_model -> table('t_settle_account') -> where($where) -> select();
		$balance_period = $_GET['balance_period'];
		$settlement_patterns = $_GET['settlement_patterns'];
		$username = $_GET['username'];
		$charge = $_GET['charge'];
		$salvation = $_GET['salvation'];
		
		$this -> assign('balance_period',$balance_period);
		$this -> assign('settlement_patterns',$settlement_patterns);
		$this -> assign('username',$username);
		$this -> assign('charge',$charge);
		$this -> assign('salvation',$salvation);
		$this -> assign("result",$result);
		$this -> display();
	}
	
	//业务审核审核通过提交
	function operation_check_do(){
		$co_model = D('Cooperative.Incomemanage');
		$id = $_GET['id'];
		if($_GET['check'] == 1){
			$where['id'] = $id;
			$where['status'] = 1;
			$data['status'] = 2;
			$data['update_tm'] = time();
			$data['operation_tm'] = time();
			$bef_result = $co_model -> table('t_settle_account') -> where($where) -> select();
			$user_result = $co_model -> table('t_user') -> where(array('uid' => $bef_result[0]['user_id'])) -> select();
			$result = $co_model -> update_operation($where,$data);
			if($result){
				$balance_period = $_GET['balance_period'];
				$settlement_patternss = $_GET['settlement_patternss'];
				$username = $_GET['username'];
				$charge = $_GET['charge'];
				$salvation = $_GET['salvation'];
				if($balance_period){
					$go .= "/balance_period/{$balance_period}";
				}
				if($settlement_patternss){
					$go .= "/settlement_patterns/{$settlement_patternss}";
				}
				if($username){
					$go .= "/username/{$username}";
				}
				if($charge){
					$go .= "/charge/{$charge}";
				}
				if($salvation){
					$go .= "/salvation/{$salvation}";
				}
				
				$this -> writelog("编辑业务审核列表记录流转状态(账号:{$user_result[0]['user_name']}),结算周期:{$bef_result[0]['balance_period']}}),状态(编辑前:业务审核;编辑后:财务审核)");
				$this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Balancemanage/operation_check_list'.$go);
				$this -> success("操作成功");
			}else{
				$this -> error("操作失败");
			}
		}
	}
	
	//业务审核审核冻结提交
	function operation_freeze_do(){
		
		$co_model = D('Cooperative.Incomemanage');
		$id = $_GET['id'];
		$where['id'] = $id;
		$where['status'] = 1;
		$data['status'] = 0;
		$data['update_tm'] = time();
		$data['freeze_tm'] = time();
		$bef_result = $co_model -> table('t_settle_account') -> where($where) -> select();
		$user_result = $co_model -> table('t_user') -> where(array('uid' => $bef_result[0]['user_id'])) -> select();
		$result = $co_model -> update_operation($where,$data);
		if($result){
			$balance_period = $_GET['balance_period'];
			$settlement_patternss = $_GET['settlement_patternss'];
			$username = $_GET['username'];
			$charge = $_GET['charge'];
			$salvation = $_GET['salvation'];
			if($balance_period){
				$go .= "/balance_period/{$balance_period}";
			}
			if($settlement_patternss){
				$go .= "/settlement_patterns/{$settlement_patternss}";
			}
			if($username){
				$go .= "/username/{$username}";
			}
			if($charge){
				$go .= "/charge/{$charge}";
			}
			if($salvation){
				$go .= "/salvation/{$salvation}";
			}

			$this -> writelog("编辑业务审核列表记录流转状态(账号:{$user_result[0]['user_name']}),结算周期:{$bef_result[0]['balance_period']}}),状态(编辑前:业务审核;编辑后:已冻结)");
			$this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Balancemanage/operation_check_list'.$go);
			$this -> success("操作成功");
		}else{
			$this -> error("操作失败");
		}
	}
	
	//业务审核添加备注显示
	function operation_remark_show(){
		$co_model = D('Cooperative.Incomemanage');
		$model = new Model();
		$id = $_GET['id'];
		$where['balance_id'] = $id;
		$where['status'] = 1;
		
	
		$result = $co_model -> table('t_audit_remark') -> where($where) -> select();
		
		foreach($result as $key => $val){
			$where_admin['admin_user_id'] = $val['update_admin'];
			$where_admin['admin_state'] = 1;
			$admin_result = $model -> table('sj_admin_users') -> where($where_admin) -> select();
			$val['admin_name'] = $admin_result[0]['admin_user_name'];
			$result[$key] = $val;
		}
		if($from == 1){
			$balance_period = $_GET['balance_period'];
			$settlement_patterns = $_GET['settlement_patterns'];
			$username = $_GET['username'];
			$charge = $_GET['charge'];
			$salvation = $_GET['salvation'];
			
			$this -> assign('balance_period',$balance_period);
			$this -> assign('settlement_patterns',$settlement_patterns);
			$this -> assign('username',$username);
			$this -> assign('charge',$charge);
			$this -> assign('salvation',$salvation);
		}else{
			$balance_period = $_GET['balance_period'];
			$username = $_GET['username'];
			$charge = $_GET['charge'];
			$salvation = $_GET['salvation'];
			$start_tm = $_GET['start_tm'];
			$end_tm = $_GET['end_tm'];
			$this -> assign('balance_period',$balance_period);
			$this -> assign('username',$username);
			$this -> assign('charge',$charge);
			$this -> assign('salvation',$salvation);
			$this -> assign('start_tm',$start_tm);
			$this -> assign('end_tm',$end_tm);
		}
		$this -> assign('from',$_GET['from']);
		$this -> assign("id",$id);
		$this -> assign("result",$result);
		$this -> display();
	}
	
	//业务审核添加备注提交
	function operation_remark_add(){
		$co_model = D('Cooperative.Incomemanage');
		$id = $_GET['id'];
		$data['update_admin'] = $_SESSION['admin']['admin_id'];
		$data['update_tm'] = time();
		$data['balance_id'] = $id;
		$data['status'] = 1;
		$data['from'] = $_GET['from'];

		$data['content'] = $_GET['content'];
		if(empty($data['content'])){
			$this -> error("备注信息不能为空");
		}
		$where['id'] = $id;
		$bef_result = $co_model -> table('t_settle_account') -> where($where) -> select();
		$user_result = $co_model -> table('t_user') -> where(array('uid' => $bef_result[0]['user_id'])) -> select();
		$result = $co_model -> add_remark($data);
	
		if($_GET['from'] == 1){
			$mfrom = '业务审核列表';
			$go_from = 'operation_check_list';
			$balance_period = $_GET['balance_period'];
			$settlement_patternss = $_GET['settlement_patterns'];
			$username = $_GET['username'];
			$charge = $_GET['charge'];
			$salvation = $_GET['salvation'];
			if($balance_period){
				$go .= "/balance_period/{$balance_period}";
			}
			if($settlement_patternss){
				$go .= "/settlement_patterns/{$settlement_patternss}";
			}
			if($username){
				$go .= "/username/{$username}";
			}
			if($charge){
				$go .= "/charge/{$charge}";
			}
			if($salvation){
				$go .= "/salvation/{$salvation}";
			}
		}else{
			$mfrom = '财务审核列表';
			$go_from = 'finance_check_list';
			$balance_period = $_GET['balance_period'];
			$username = $_GET['username'];
			$charge = $_GET['charge'];
			$salvation = $_GET['salvation'];
			$start_tm = $_GET['start_tm'];
			$end_tm = $_GET['end_tm'];
			if($balance_period){
				$go .= "/balance_period/{$balance_period}";
			}
			if($username){
				$go .= "/username/{$username}";
			}
			if($charge){
				$go .= "/charge/{$charge}";
			}
			if($salvation){
				$go .= "/salvation/{$salvation}";
			}
			if($start_tm && $end_tm){
				$go .= "/start_tm/{$start_tm}/end_tm/{$end_tm}";
			}
	
		}
		
		if($result){
			$this -> writelog("添加{$mfrom}记录(账号:{$user_result[0]['user_name']},结算周期:{$bef_result[0]['balance_period']}),备注:{$data['content']}");
			$this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . "/Balancemanage/{$go_from}".$go);
			$this -> success("添加成功");
		}else{
			$this -> error("添加失败");
		}
	}
	
	//修改补差显示
	function update_salvation_show(){
		$co_model = D("Cooperative.Incomemanage");
		$id = $_GET['id'];
		$result = $co_model -> table('t_settle_account') -> where(array('id' => $id,'status' => 1)) -> select();
		if($result[0]['salvation'] < 0){
			$result[0]['salvation_my'] = str_replace("-", "", $result[0]['salvation']);
		}else{
			$result[0]['salvation_my'] = $result[0]['salvation'];
		}
		$balance_period = $_GET['balance_period'];
		$settlement_patterns = $_GET['settlement_patterns'];
		$username = $_GET['username'];
		$charge = $_GET['charge'];
		$salvation = $_GET['salvation'];
		
		$this -> assign('balance_period',$balance_period);
		$this -> assign('settlement_patterns',$settlement_patterns);
		$this -> assign('username',$username);
		$this -> assign('charge',$charge);
		$this -> assign('salvation',$salvation);
		$this -> assign("result",$result);
		$this -> display();
	
	}
	
	
	//修改补差
	function update_salvation(){
		$co_model = D('Cooperative.Incomemanage');
		
		$id = $_GET['id'];
		$where['id'] = $id;
		$where['status'] = 1;
		$data['update_tm'] = time();
		$been_result = $co_model -> table('t_settle_account') -> where($where) -> select();
		$user_result = $co_model -> table('t_user') -> where(array('uid' => $been_result[0]['user_id'])) -> select();
		if(!isset($_GET['salvation'])){
			$this -> error('补差值不能为空');
		}
		if($_GET['salvation'] < 0){
			$this -> error('补差值格式错误');
		}
		if($_GET['sign'] == 1){
			$data['salvation'] = sprintf("%.2f",trim($_GET['salvation']));
		}else{
			$data['salvation'] = '-'.sprintf("%.2f",trim($_GET['salvation']));
		}
		$data['pre_tax'] = $been_result[0]['amount'] + $data['salvation'];
		$data['update_tm'] = time();
		$data['after_tax'] = sprintf("%.2f",($data['pre_tax'] - $data['pre_tax']*$been_result[0]['tax_rate']/100));
		$result = $co_model -> update_operation($where,$data);
		$balance_period = $_GET['balance_period'];
		$settlement_patternss = $_GET['settlement_patterns'];
		$username = $_GET['username'];
		$charge = $_GET['charge'];
		$salvations = $_GET['salvations'];
		if($balance_period){
			$go .= "/balance_period/{$balance_period}";
		}
		if($settlement_patternss){
			$go .= "/settlement_patterns/{$settlement_patternss}";
		}
		if($username){
			$go .= "/username/{$username}";
		}
		if($charge){
			$go .= "/charge/{$charge}";
		}
	
		if($salvations){
			$go .= "/salvation/{$salvations}";
		}
		if($result){
			$this -> writelog("编辑业务审核记录(账号:{$user_result[0]['user_name']},结算周期:{$been_result[0]['balance_period']}),补差值(编辑前:{$been_result[0]['salvation']};编辑后:{$data['salvation']})");
			$this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Balancemanage/operation_check_list'.$go);
			$this -> success("修改成功");
		}else{
			$this -> error("修改失败");
		}
	}
	
	//修改税率显示
	function update_tax_show(){
		$co_model = D('Cooperative.Incomemanage');
		$id = $_GET['id'];
		$result = $co_model -> table('t_settle_account') -> where(array('id' => $id,'status' => 1)) -> select();
		$balance_period = $_GET['balance_period'];
		$settlement_patterns = $_GET['settlement_patterns'];
		$username = $_GET['username'];
		$charge = $_GET['charge'];
		$salvation = $_GET['salvation'];
		
		$this -> assign('balance_period',$balance_period);
		$this -> assign('settlement_patterns',$settlement_patterns);
		$this -> assign('username',$username);
		$this -> assign('charge',$charge);
		$this -> assign('salvation',$salvation);
		$this -> assign("result",$result);
		$this -> display();
	
	}
	
	//修改税率提交
	function update_tax(){
		$co_model = D('Cooperative.Incomemanage');
		$id = $_GET['id'];
		$where['id'] = $id;
		$where['status'] = 1;
		$been_result = $co_model -> table('t_settle_account') -> where($where) -> select();
		$user_result = $co_model -> table('t_user') -> where(array('uid' => $been_result[0]['user_id'])) -> select();
		if(!isset($_GET['tax_rate'])){
			$this -> error('税率不能为空');
		}
		if($_GET['tax_rate'] < 0){
			$this -> error('税率格式错误');
		}
		$data['tax_rate'] = sprintf("%.2f",trim($_GET['tax_rate']));
		$data['after_tax'] = sprintf("%.2f",$been_result[0]['pre_tax'] - $been_result[0]['pre_tax']*$data['tax_rate']/100);
		$data['update_tm'] = time();
		$result = $co_model -> update_operation($where,$data);
		
		$balance_period = $_GET['balance_period'];
		$settlement_patternss = $_GET['settlement_patterns'];
		$username = $_GET['username'];
		$charge = $_GET['charge'];
		$salvation = $_GET['salvation'];
		if($balance_period){
			$go .= "/balance_period/{$balance_period}";
		}
		if($settlement_patternss){
			$go .= "/settlement_patterns/{$settlement_patternss}";
		}
		if($username){
			$go .= "/username/{$username}";
		}
		if($charge){
			$go .= "/charge/{$charge}";
		}
		if($salvation){
			$go .= "/salvation/{$salvation}";
		}
		if($result){
			$this -> writelog("编辑业务审核记录(账号:{$user_result[0]['user_name']},结算周期:{$been_result[0]['balance_period']}),税率(编辑前:{$been_result[0]['tax_rate']};编辑后:{$data['tax_rate']})");
			$this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Balancemanage/operation_check_list'.$go);
			$this -> success("修改成功");
		}else{
			$this -> error("修改失败");
		}
	
	}
	
	
	//财务审核列表
	function finance_check_list(){
		$co_model = D('Cooperative.Incomemanage');
		if(isset($_GET['balance_period']) && !empty($_GET['balance_period'])){
			$where_all .= " and balance_period = {$_GET['balance_period']}";
		}
		
		if($_GET['start_tm'] && $_GET['end_tm'] && !empty($_GET['start_tm']) && !empty($_GET['end_tm'])){
			if(strtotime($_GET['start_tm']) > strtotime($_GET['end_tm'])){
				$this -> error("查询审核的开始时间不能大于结束时间");
			}
			$where_all .= " and operation_tm >= ".strtotime($_GET['start_tm'])." and operation_tm < ".strtotime($_GET['end_tm']);
		
		}
		
		$admin_id = $_SESSION['admin']['admin_id'];
		$userid_arr = $this -> cooperative_manager($admin_id);
		if(isset($_GET['username']) && !empty($_GET['username'])){
			$where_user['_string'] = "user_name like '%{$_GET['username']}%'";
			$user_result = $co_model -> table('t_user') -> where($where_user) -> select();
			foreach($user_result as $key => $val){
				$userid_arr_username[] = $val['uid'];
			}
		}
		
		if(isset($_GET['charge']) && !empty($_GET['charge'])){
			$where_charge['_string'] = "charge_person = {$_GET['charge']} and status = 1";
			$user_result = $co_model -> table('t_user') -> where($where_charge) -> select();
			foreach($user_result as $key => $val){
				$userid_arr_charge[] = $val['uid'];
			}
		}
		
		if(isset($_GET['user_id']) && !empty($_GET['user_id'])){
			$where_all .= " and user_id = {$_GET['user_id']}";
		}
		
		if($userid_arr_username && $userid_arr_charge){
			$userid_arr_all = array_intersect($userid_arr_username,$userid_arr,$userid_arr_charge);
		}elseif($userid_arr_username && !$userid_arr_charge){
			if(empty($_GET['charge'])){
				$userid_arr_all = array_intersect($userid_arr_username,$userid_arr);
			}else{
				$userid_arr_all = array();
			}
		}elseif($userid_arr_charge && !$userid_arr_username){
			if(empty($_GET['username'])){
				$userid_arr_all = array_intersect($userid_arr,$userid_arr_charge);
			}else{
				$userid_arr_all = array();
			}
		}elseif(!$userid_arr_charge && !$userid_arr_username){
			if(empty($_GET['username']) && empty($_GET['charge'])){
				$userid_arr_all = $userid_arr;
			}else{
				$userid_arr_all = array();
			}
		}
	
		foreach($userid_arr_all as $key => $val){
			$userid_arr_all_str .= $val.',';
		}
		
		$userid_str = substr($userid_arr_all_str,0,strlen($userid_arr_all_str) - 1);
		
		if(isset($_GET['salvation']) && !empty($_GET['salvation'])){
			if($_GET['salvation'] == 1){
				$where_all .= " and salvation != 0";
			}else{
				$where_all .= " and salvation = 0";
			}
		}
		if($userid_str && !$_GET['uid']){
			$where['_string'] = "status = 2 and user_id in ({$userid_str})".$where_all;
		}elseif(($userid_str || !$userid_str) && $_GET['uid']){
			$where['_string'] = "status = 2 and user_id = {$_GET['uid']}".$where_all;
		}else{
			$where['_string'] = "status = 2 and user_id in (0)".$where_all;
		}
	
		$count = $co_model -> table('t_settle_account') -> where($where) -> count();
		import("@.ORG.Page");
		$param = http_build_query($_GET);
		$Page = new Page($count, 10, $param);
		$result = $co_model -> table('t_settle_account') -> where($where) -> limit($Page->firstRow . ',' . $Page->listRows) -> order('operation_tm DESC') -> select();
		
		foreach($result as $key => $val){
			$where_username['uid'] = $val['user_id'];
			$username_result = $co_model -> table('t_user') -> where($where_username) -> select();
			$val['user_name'] = $username_result[0]['user_name'];
			$val['balance_period'] = date('Y年m月',strtotime($val['balance_period'].'01'));
			$result[$key] = $val;
		}
		
		$charge_result = $co_model -> table('t_charge') -> where(array('status' => 1)) -> select();
		$balance_period_result = $co_model -> table('t_settle_account') -> order('balance_period DESC') -> select();

		foreach($balance_period_result as $key => $val){
			if($val['balance_period'] != 0){
				$balance_period_arr_go[] = $val['balance_period'];
			}
		}

		$balance_period_arr = array_unique($balance_period_arr_go);
					
		foreach($balance_period_arr as $key => $val){
			$v['value_go'] = $val;
			$v['time_go'] = date('Y年m月',strtotime($val.'01'));
			$balance_period_need[$key] = $v;
		}

		$this -> assign('balance_period_need',$balance_period_need);
		$this -> assign('charge_result',$charge_result);
		$this -> assign("balance_period",$_GET['balance_period']);
		$this -> assign("start_tm",$_GET['start_tm']);
		$this -> assign("end_tm",$_GET['end_tm']);
		$this -> assign("username",$_GET['username']);
		$this -> assign("charge",$_GET['charge']);
		$this -> assign("salvation",$_GET['salvation']);
		$Page->setConfig('header', '篇记录');
		$Page->setConfig('first', '<<');
		$Page->setConfig('last', '>>');
		$show = $Page->show();
		$this->assign("page", $show);
		$this -> assign("result",$result);
		$this -> display();
	
	}
	
	//财务审核列表审核显示
	function finance_check_show(){
		$id= $_GET['id'];
		$balance_period = $_GET['balance_period'];
		$username = $_GET['username'];
		$charge = $_GET['charge'];
		$salvation = $_GET['salvation'];
		$start_tm = $_GET['start_tm'];
		$end_tm = $_GET['end_tm'];
		$this -> assign('balance_period',$balance_period);
		$this -> assign('username',$username);
		$this -> assign('charge',$charge);
		$this -> assign('salvation',$salvation);
		$this -> assign('start_tm',$start_tm);
		$this -> assign('end_tm',$end_tm);
		$this -> assign("id",$id);
		$this -> display();
	}
	
	//财务审核列表审核
	function finance_check_do(){
		$co_model = D('Cooperative.Incomemanage');
		$id = $_GET['id'];
		$where_all['id'] = $id;
		$bef_result = $co_model -> table('t_settle_account') -> where($where_all) -> select();
		$user_result = $co_model -> table('t_user') -> where(array('uid' => $bef_result[0]['user_id'])) -> select();
		if(strtotime($_GET['billing_tm']) > time() || strtotime($_GET['invoice_tm']) > time()){
			$this -> error("开票日期和收票日期不能大于当前时间");
		}
		$balance_period = $_GET['balance_period'];
		$username = $_GET['username'];
		$charge = $_GET['charge'];
		$salvation = $_GET['salvation'];
		$start_tm = $_GET['start_tm'];
		$end_tm = $_GET['end_tm'];
		if($balance_period){
			$go .= "/balance_period/{$balance_period}";
		}
		if($username){
			$go .= "/username/{$username}";
		}
		if($charge){
			$go .= "/charge/{$charge}";
		}
		if($salvation){
			$go .= "/salvation/{$salvation}";
		}
		if($start_tm && $end_tm){
			$go .= "/start_tm/{$start_tm}/end_tm/{$end_tm}";
		}
	
		if($_GET['check'] == 1){
			$billing_tm = $_GET['billing_tm'];
			$invoice_tm = $_GET['invoice_tm'];
			if(isset($_GET['billing_tm'])){
				$data['billing_tm'] = strtotime($billing_tm);
			}else{
				$data['billing_tm'] = '';
			}
			if(isset($_GET['billing_tm'])){
				$data['invoice_tm'] = strtotime($invoice_tm);
			}else{
				$data['invoice_tm'] = '';
			}
			
			
			$data['finance_tm'] = time();
			$data['status'] = 3;
			$where['id'] = $id;
			$where['status'] = 2;
			$result = $co_model -> update_operation($where,$data);
		
			if($result){
				$this -> writelog("编辑财务审核记录(账号:{$user_result[0]['user_name']},结算周期:{$bef_result[0]['balance_period']}),流转状态(编辑前:财务审核;编辑后:待付款),开票时间{$billing_tm},收票时间{$invoice_tm}");
				$this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Balancemanage/finance_check_list'.$go);
				$this -> success("操作成功");
			}else{
				$this -> error("操作失败");
			}
		}elseif($_GET['check'] == 2){
			$data['status'] = 1;
			$where['id'] = $id;
			$where['status'] = 2;
			$data['finance_tm'] = time();
			$result = $co_model -> update_operation($where,$data);
			if($result){
				$this -> writelog("编辑财务审核记录(账号:{$user_result[0]['user_name']}),流转状态(编辑前:财务审核;编辑后:业务审核)");
				$this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Balancemanage/finance_check_list'.$go);
				$this -> success("操作成功");
			}else{
				$this -> error("操作失败");
			}
		}
	
	}
	
	//财务审核添加备注显示
	function finance_remark_show(){
		$co_model = D('Cooperative.Incomemanage');
		$model = new Model();
		$id = $_GET['id'];
		$where['balance_id'] = $id;
		$where['status'] = 1;
		$where['from'] = 2;
		$result = $co_model -> table('t_audit_remark') -> where($where) -> select();
	
		foreach($result as $key => $val){
			$where_admin['admin_user_id'] = $val['update_admin'];
			$where_admin['admin_state'] = 1;
			$admin_result = $model -> table('sj_admin_users') -> where($where_admin) -> select();
			$val['admin_name'] = $admin_result[0]['admin_user_name'];
			$result[$key] = $val;
		}
		
		$this -> assign("id",$id);
		$this -> assign('from',2);
		$this -> assign("result",$result);
		$this -> display("operation_remark_show");
	}

	//财务审核添加备注提交
	function finance_remark_add(){
		$co_model = D('Cooperative.Incomemanage');
		$id = $_GET['id'];
		$where['id'] = $id;
		$data['update_admin'] = $_SESSION['admin']['admin_id'];
		$data['update_tm'] = time();
		$data['balance_id'] = $id;
		$data['status'] = 1;
		$data['from'] = 2;
		$data['content'] = $_GET['content'];
		if(empty($data['content'])){
			$this -> error("备注信息不能为空");
		}
		
		$result = $co_model -> add_remark($data);
		$bef_result = $co_model -> table('t_settle_account') -> where($where) -> select();
		$user_result = $co_model -> table('t_user') -> where(array('uid' => $bef_result[0]['user_id'])) -> select();
		if($result){
			$this -> writelog("添加财务审核11112(账号:{$user_result[0]['user_name']},结算周期:{$bef_result[0]['balance_period']}),备注:({$data['content']})");
			$this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Balancemanage/finance_check_list');
			$this -> success("添加成功");
		}else{
			$this -> error("添加失败");
		}
	}
	
	//财务审核查看详情
	function finance_detail(){
		$co_model = D('Cooperative.Incomemanage');
		$model = new Model();
		$id = $_GET['id'];
		$where['id'] = $id;
		$result = $co_model -> table('t_settle_account') -> where($where) -> select();
		foreach($result as $key => $val){
			$where_account['uid'] = $val['user_id'];
			$account_result = $co_model -> table('t_user') -> where($where_account) -> select();
			$val['user_name'] = $account_result[0]['user_name'];
			$result[$key] = $val;
		}
		
		$where_remark['balance_id'] = $id;
		$where_remark['status'] = 1;
		$remark_result = $co_model -> table('t_audit_remark') -> where($where_remark) -> select();
		foreach($remark_result as $key => $val){
			$where_admin['admin_user_id'] = $val['update_admin'];
			$admin_result = $model -> table('sj_admin_users') -> where($where_admin) -> select();
			$val['admin_name'] = $admin_result[0]['admin_user_name'];
			$remark_result[$key] = $val;
		}
		if($result[0]['status'] == 2){
			$index = 'finance_check_list';
		}elseif($result[0]['status'] == 3){
			$index = 'obligation_list';
		}elseif($result[0]['status'] == 4){
			$index = 'account_paid_list';
		}elseif($result[0]['status'] == 0){
			$index = 'freeze_list';
		}
		
		$this -> assign('index',$index);
		$this -> assign("remark_result",$remark_result);
		$this -> assign("result",$result);
		$this -> display();
	}
	
	
	//待付款列表
	function obligation_list(){
		$co_model = D('Cooperative.Incomemanage');
		$admin_id = $_SESSION['admin']['admin_id'];
		$userid_arr = $this -> cooperative_manager($admin_id);
		if(isset($_GET['user_id']) && !empty($_GET['user_id'])){
			$where_all .= "and user_id = {$_GET['user_id']}";
		}
		
		if(isset($_GET['balance_period']) && !empty($_GET['balance_period'])){
			$where_all .= " and balance_period = {$_GET['balance_period']}";
		}

		if(isset($_GET['username']) && !empty($_GET['username'])){
			$where_user['_string'] = "user_name like '%{$_GET['username']}%'";
			$user_result = $co_model -> table('t_user') -> where($where_user) -> select();
			foreach($user_result as $key => $val){
				$userid_arr_username[] = $val['uid'];
			}
		}
		
		
		if(isset($_GET['charge']) && !empty($_GET['charge'])){
			$where_charge['_string'] = "charge_person = {$_GET['charge']} and status = 1";
			$user_result = $co_model -> table('t_user') -> where($where_charge) -> select();
			foreach($user_result as $key => $val){
				$userid_arr_charge[] = $val['uid'];
			}
			$charge = $_GET['charge'];
		}elseif($_GET['charge_name']){
			$my_charge = $this -> search_charge($_GET['charge_name']);
		
			$charge = $my_charge;
		}

		if($userid_arr_username && $userid_arr_charge){
			$userid_arr_all = array_intersect($userid_arr_username,$userid_arr,$userid_arr_charge);
		}elseif($userid_arr_username && !$userid_arr_charge){
			if(empty($_GET['charge'])){
				$userid_arr_all = array_intersect($userid_arr_username,$userid_arr);
			}else{
				$userid_arr_all = array();
			}
		}elseif($userid_arr_charge && !$userid_arr_username){
			if(empty($_GET['username'])){
				$userid_arr_all = array_intersect($userid_arr,$userid_arr_charge);
			}else{
				$userid_arr_all = array();
			}
		}elseif(!$userid_arr_charge && !$userid_arr_username){
			if(empty($_GET['username']) && empty($_GET['charge'])){
				$userid_arr_all = $userid_arr;
			}else{
				$userid_arr_all = array();
			}
		}
		
		foreach($userid_arr_all as $key => $val){
			$userid_arr_all_str .= $val.',';
		}
		
		$userid_str = substr($userid_arr_all_str,0,strlen($userid_arr_all_str) - 1);
		
		if($_GET['charge_name'] && !$_GET['uid']){
			$charge_get_result = $co_model -> table('t_charge') -> where(array('charge_name' => $_GET['charge_name'])) -> select();
			$user_get_result = $co_model -> table('t_user') -> where(array('charge_person' => $charge_get_result[0]['id'])) -> select();
			foreach($user_get_result as $key => $val){
				$charge_get[] = $val['uid'];
			}
			$userid_get_go = array_intersect($charge_get,$userid_arr);
			
			foreach($userid_get_go as $key => $val){
				$userid_get .= $val.',';
			}
	
			$userid_get_str = substr($userid_get,0,strlen($userid_get) - 1);
			$where['_string'] = "status = 3 and user_id in ({$userid_get_str})".$where_all;
		}elseif(($_GET['charge_name'] || !$_GET['charge_name'])&& $_GET['uid']){
			$where['_string'] = "status = 3 and user_id = {$_GET['uid']}".$where_all;
		}else{
			$where['_string'] = "status = 3 and user_id in ({$userid_str})".$where_all;
		}
		$count = $co_model -> table('t_settle_account') -> where($where) -> count();
		import("@.ORG.Page");
		$param = http_build_query($_GET);
		$Page = new Page($count, 50, $param);
		$result = $co_model -> table('t_settle_account') -> where($where) -> limit($Page->firstRow . ',' . $Page->listRows) -> order('finance_tm DESC') -> select();
		//税后合计值
		$after_tax_result = $co_model -> table('t_settle_account') -> where($where) -> field('user_id,sum(after_tax)') -> group('user_id') -> select();
		
		foreach($result as $key => $val){
			$where_username['uid'] = $val['user_id'];
			$username_result = $co_model -> table('t_user') -> where($where_username) -> select();
			foreach($after_tax_result as $k => $v){
				if($val['user_id'] == $v['user_id']){
					if($v['sum(after_tax)'] > $val['min_pay']){
						$val['warning'] = 1;
					}else{
						$val['warning'] = 0;
					}
				}
			}
			$val['user_name'] = $username_result[0]['user_name'];
			$val['user_status'] = $username_result[0]['status'];
			$charge_result = $co_model -> table('t_charge') -> where(array('id' => $username_result[0]['charge_person'])) -> select();
			$val['charge_name'] = $charge_result[0]['charge_name'];
			$val['balance_period'] = date('Y年m月',strtotime($val['balance_period'].'01'));
			$result[$key] = $val;
		}
	
		
		if($_GET['derive'] == 1){
			foreach($result as $key => $val){
				$where_user_down['uid'] = $val['user_id'];
				$user_status_result = $co_model -> table('t_user') -> where($where_user_down) -> select();
				$charge_result = $co_model -> table('t_charge') -> where(array('id' => $user_status_result[0]['charge_person'])) -> select();
				if($user_status_result[0]['status'] == 1){
					$user_status = '正常';
				}else{
					$user_status = '暂停';
				}
				$file_str .= $val['user_id'].','.$val['user_name'].','.$user_status.','.$charge_result[0]['charge_name'].','.$val['balance_period'].','.$val['after_tax'].','.$val['min_pay'].','.$val['account_name'].','.$val['bank_name'].','.$val['opening_bank'].','.$val['bank_addr'].','.$val['bank_account'].','.'待付款'.','.date('Y-m-d',$val['finance_tm'])."\n";
			}
			
			$file_go = 'obligation_listdate_'.date('YmdHis').".csv";//文件名
			header( "Cache-Control: public" );
			header( "Pragma: public" );
			header("Content-type:application/vnd.ms-excel");
			header('Content-Disposition:attachment;filename='.$file_go.'.csv');
			header('Content-Type:APPLICATION/OCTET-STREAM');
			ob_start();
			$header_str =  iconv("UTF-8",'GBK',"账号ID,账号名称,账号状态,负责人,结算周期,税后金额,最低付款值,户名,所属银行,开户行,所属城市,银行账号,流转状态,财务审核日期");
			$file_str_go=  iconv("UTF-8",'GBK',$file_str);
			echo $header_str."\n";
			echo $file_str_go;
			ob_end_flush();
			exit;
		
		}
	
		$charge_result = $co_model -> table('t_charge') -> where(array('status' => 1)) -> select();
		$balance_period_result = $co_model -> table('t_settle_account') -> order('balance_period DESC') -> select();

		foreach($balance_period_result as $key => $val){
			if($val['balance_period'] != 0){
				$balance_period_arr_go[] = $val['balance_period'];
			}
		}

		$balance_period_arr = array_unique($balance_period_arr_go);
					
		foreach($balance_period_arr as $key => $val){
			$v['value_go'] = $val;
			$v['time_go'] = date('Y年m月',strtotime($val.'01'));
			$balance_period_need[$key] = $v;
		}

		$this -> assign('balance_period_need',$balance_period_need);
		$this -> assign('charge_result',$charge_result);
		$this -> assign("balance_period",$_GET['balance_period']);
		$this -> assign("username",$_GET['username']);
		$this -> assign("charge",$charge);
		$Page->setConfig('header', '篇记录');
		$Page->setConfig('first', '<<');
		$Page->setConfig('last', '>>');
		$show = $Page->show();
		$this->assign("page", $show);
		$this -> assign("result",$result);
		$this -> display();
		
	
		
	}
	
	//待付款驳回
	function obligation_check(){
		$co_model = D('Cooperative.Incomemanage');
		$id = $_GET['id'];
		$data['status'] = 2;
		$where['id'] = $id;
		$where['status'] = 3;
		$bef_result = $co_model -> table('t_settle_account') -> where($where) -> select();
		$user_result = $co_model -> table('t_user') -> where(array('uid' => $bef_result[0]['user_id'])) -> select();
		$result = $co_model -> update_operation($where,$data);
		$balance_period = $_GET['balance_period'];
		$username = $_GET['username'];
		$charge = $_GET['charge'];
		if($balance_period){
			$go .= "/balance_period/{$balance_period}";
		}
		if($username){
			$go .= "/username/{$username}";
		}
		if($charge){
			$go .= "/charge/{$charge}";
		}
		if($result){
			$this -> writelog("编辑待付款记录(账号:{$user_result[0]['user_name']},结算周期:{$bef_result[0]['balance_period']}),流转状态(编辑前:待审核;编辑后:财务审核)");
			$this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Balancemanage/obligation_list'.$go);
			$this -> success("操作成功");
		}else{
			$this -> error("操作失败");
		}
	
	}
	
	
	//待付款查看详情
	function obligation_detail(){
		$co_model = D('Cooperative.Incomemanage');
		$id = $_GET['id'];
		$where['id'] = $id;
		$where['status'] = 3;
		$result = $co_model -> table('t_settle_account') -> select();
		$this -> assign("result",$result);
		$this -> display();
	}
	
	
	//待付款补全付款日期显示
	function obligation_complemented_show(){
		$co_model = D('Cooperative.Incomemanage');
		if($_POST['id_arr']){
			$id = $_POST['id_arr'];
			foreach($id as $key => $val){
				$where['id'] = $val;
				$result = $co_model -> table('t_settle_account') -> where($where) -> select();
				$money_arr[] = $result[0]['after_tax'];
				$id_str_go .= $val.',';
			}
			$id_str = substr($id_str_go,0,strlen($id_str_go) - 1);
			$my_money = array_sum($money_arr);
			$back = 1;
			$this -> assign('id',$id_str);
		}elseif($_GET['id']){
			$where['id'] = $_GET['id'];
			$result = $co_model -> table('t_settle_account') -> where($where) -> select();
			$my_money = $result[0]['after_tax'];
			$back = 2;
			$this -> assign("id",$_GET['id']);
		}
		$balance_period = $_GET['balance_period'];
		$username = $_GET['username'];
		$charge = $_GET['charge'];
		$this -> assign('balance_period',$balance_period);
		$this -> assign('username',$username);
		$this -> assign('charge',$charge);
		$this -> assign('back',$back);
		$this -> assign("my_money",$my_money);
		$this -> display();
	}
	
	//待付款补全付款日期提交
	function obligation_complemented_do(){
		$co_model = D('Cooperative.Incomemanage');
		$id = $_GET['id'];
		
		$is_arr_go = strpos($id,',');
		
		if($is_arr_go){
			$id = explode(',',$id);
		}
		$pay_tm = strtotime($_GET['pay_tm']);
		if(!$_GET['pay_tm']){
			$this -> error("请输入付款日期");
		}
		if(strtotime($_GET['pay_tm']) > time()){
			$this -> error("付款日期不能大于当前时间");
		}
	
		if(is_array($id)){
			foreach($id as $key => $val){
				$where['id'] = $val;
				$data['pay_tm'] = $pay_tm;
				$data['status'] = 4;
				$data['update_pay_tm'] = time();
				$result_go = $co_model -> update_operation($where,$data);
				$where_all['id'] = $val;
				$bef_result = $co_model -> table('t_settle_account') -> where($where_all) -> select();
				$user_result = $co_model -> table('t_user') -> where(array('uid' => $bef_result[0]['user_id'])) -> select();
				$result[] = $result_go;
				$msg .= "编辑待付款列表记录(账号:{$user_result[0]['user_name']},结算周期:{$bef_result[0]['balance_period']}),付款日期({$data['pay_tm']})";
			}
	
		}else{
			$where['id'] = $id;
			$data['pay_tm'] = $pay_tm;
			$data['status'] = 4;
			$data['update_pay_tm'] = time();
			$result = $co_model -> update_operation($where,$data);
			
			$msg .= "编辑待付款列表记录(账号:{$user_result[0]['user_name']},结算周期:{$bef_result[0]['balance_period']}),付款日期({$data['pay_tm']})";

		}
		$balance_period = $_GET['balance_period'];
		$username = $_GET['username'];
		$charge = $_GET['charge'];
		if($balance_period){
			$go .= "/balance_period/{$balance_period}";
		}
		if($username){
			$go .= "/username/{$username}";
		}
		if($charge){
			$go .= "/charge/{$charge}";
		}
		if($result){
			$this -> writelog($msg);
			$this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Balancemanage/obligation_list'.$go);
			$this -> success("操作成功");
		}else{
			$this -> error("操作失败");
		}
	
	}
	
	
	//已付款列表
	function account_paid_list(){
		$co_model = D('Cooperative.Incomemanage');
		$admin_id = $_SESSION['admin']['admin_id'];
		$userid_arr = $this -> cooperative_manager($admin_id);
		if(isset($_GET['balance_period']) && !empty($_GET['balance_period'])){
			$where_all .= " and balance_period = {$_GET['balance_period']}";
		}
		if(isset($_GET['settlement_patterns']) && !empty($_GET['settlement_patterns'])){
			$where_all .= " and settlement_patterns = {$_GET['settlement_patterns']}";
		}
		if(isset($_GET['username']) && !empty($_GET['username'])){
			$where_user['_string'] = "user_name like '%{$_GET['username']}%'";
			$user_result = $co_model -> table('t_user') -> where($where_user) -> select();
			foreach($user_result as $key => $val){
				$userid_arr_username[] = $val['uid'];
			}
		}
		if(isset($_GET['charge']) && !empty($_GET['charge'])){
			$where_charge['_string'] = "charge_person = {$_GET['charge']} and status = 1";
			$user_result = $co_model -> table('t_user') -> where($where_charge) -> select();
			foreach($user_result as $key => $val){
				$userid_arr_charge[] = $val['uid'];
			}
		}
		if(isset($_GET['salvation']) && !empty($_GET['salvation'])){
			if($_GET['salvation'] == 1){
				$where_all .= " and salvation != 0";
			}else{
				$where_all .= " and salvation = 0";
			}
		}
		
		if(isset($_GET['user_id']) && !empty($_GET['user_id'])){
			$where_all .= " and user_id = {$_GET['user_id']}";
		}
		
		if($userid_arr_username && $userid_arr_charge){
			$userid_arr_all = array_intersect($userid_arr_username,$userid_arr,$userid_arr_charge);
		}elseif($userid_arr_username && !$userid_arr_charge){
			if(empty($_GET['charge'])){
				$userid_arr_all = array_intersect($userid_arr_username,$userid_arr);
			}else{
				$userid_arr_all = array();
			}
		}elseif($userid_arr_charge && !$userid_arr_username){
			if(empty($_GET['username'])){
				$userid_arr_all = array_intersect($userid_arr,$userid_arr_charge);
			}else{
				$userid_arr_all = array();
			}
		}elseif(!$userid_arr_charge && !$userid_arr_username){
			if(empty($_GET['username']) && empty($_GET['charge'])){
				$userid_arr_all = $userid_arr;
			}else{
				$userid_arr_all = array();
			}
		}
	
		foreach($userid_arr_all as $key => $val){
			$userid_arr_all_str .= $val.',';
		}
		
		$userid_str = substr($userid_arr_all_str,0,strlen($userid_arr_all_str) - 1);
		
		if($_GET['time_type'] == 1){
			if(isset($_GET['start_tm']) && isset($_GET['end_tm'])){
				if(strtotime($_GET['start_tm']) > strtotime($_GET['end_tm'])){
					$this -> error("查询付款开始时间不能大于结束时间");
				}
				$where_all .= " and pay_tm > ".strtotime($_GET['start_tm'])." and pay_tm < ".strtotime($_GET['end_tm'])."";
			}
		}elseif($_GET['time_type'] == 2){
			if(isset($_GET['start_tm']) && isset($_GET['end_tm'])){
				if(strtotime($_GET['start_tm']) > strtotime($_GET['end_tm'])){
					$this -> error("查询审核开始时间不能大于结束时间");
				}
				$where_all .= " and operation_tm > ".strtotime($_GET['start_tm'])." and operation_tm < ".strtotime($_GET['end_tm'])."";
			}
		}
		if($userid_str && !$_GET['uid']){
			$where['_string'] = "status = 4 and user_id in ({$userid_str})".$where_all;
		}elseif(($userid_str || !$userid_str) && $_GET['uid']){
			$where['_string'] = "status = 4 and user_id = {$_GET['uid']}".$where_all;
		}else{
			$where['_string'] = "status = 4 and user_id in (0)".$where_all;
		}	

		$count = $co_model -> table('t_settle_account') -> where($where) -> count();
		import("@.ORG.Page");
		$param = http_build_query($_GET);
		$Page = new Page($count, 10, $param);
		$result = $co_model -> table('t_settle_account') -> where($where) -> limit($Page->firstRow . ',' . $Page->listRows) -> order('pay_tm DESC') -> select();
		
		foreach($result as $key => $val){
			$where_username['uid'] = $val['user_id'];
			$username_result = $co_model -> table('t_user') -> where($where_username) -> select();
			$val['user_name'] = $username_result[0]['user_name'];
			$val['balance_period'] = date('Y年m月',strtotime($val['balance_period'].'01'));
			$result[$key] = $val;
		}
	
		$charge_result = $co_model -> table('t_charge') -> where(array('status' => 1)) -> select();
		$balance_period_result = $co_model -> table('t_settle_account') -> order('balance_period DESC') -> select();

		foreach($balance_period_result as $key => $val){
			if($val['balance_period'] != 0){
				$balance_period_arr_go[] = $val['balance_period'];
			}
		}

		$balance_period_arr = array_unique($balance_period_arr_go);
					
		foreach($balance_period_arr as $key => $val){
			$v['value_go'] = $val;
			$v['time_go'] = date('Y年m月',strtotime($val.'01'));
			$balance_period_need[$key] = $v;
		}

		$this -> assign('balance_period_need',$balance_period_need);
		$this -> assign('charge_result',$charge_result);
		$this -> assign("balance_period",$_GET['balance_period']);
		$this -> assign("settlement_patterns",$_GET['settlement_patterns']);
		$this -> assign("username",$_GET['username']);
		$this -> assign("charge",$_GET['charge']);
		$this -> assign("time_type",$_GET['time_type']);
		$this -> assign("start_tm",$_GET['start_tm']);
		$this -> assign("end_tm",$_GET['end_tm']);
		$Page->setConfig('header', '篇记录');
		$Page->setConfig('first', '<<');
		$Page->setConfig('last', '>>');
		$show = $Page->show();
		$this->assign("page", $show);
		$this -> assign("result",$result);
		$this -> display();
	
	}

	//已付款列表_查看详情
	function account_paid_detail(){
		$co_model = D('Cooperative.Incomemanage');
		$id = $_GET['id'];
		$where['id'] = $id;
		$where['status'] = 4;
		$result = $co_model -> table('t_settle_account') -> where($where) -> select();
		$this -> assign("result",$result);
		$this -> display();
	}
	
	
	//已冻结列表
	function freeze_list(){
		$co_model = D('Cooperative.Incomemanage');
		$admin_id = $_SESSION['admin']['admin_id'];
		$userid_arr = $this -> cooperative_manager($admin_id);
		if(isset($_GET['balance_period']) && !empty($_GET['balance_period'])){
			$where_all .= " and balance_period = {$_GET['balance_period']}";
		}
		if(isset($_GET['settlement_patterns']) && !empty($_GET['settlement_patterns'])){
			$where_all .= " and settlement_patterns = {$_GET['settlement_patterns']}";
		}
		if(isset($_GET['user_id']) && !empty($_GET['user_id'])){
			$where_all .= " and user_id = {$_GET['user_id']}";
		}
		if(isset($_GET['username']) && !empty($_GET['username'])){
			$where_user['_string'] = "user_name like '%{$_GET['username']}%'";
			$user_result = $co_model -> table('t_user') -> where($where_user) -> select();
			foreach($user_result as $key => $val){
				$userid_arr_username[] = $val['uid'];
			}
		}
		if(isset($_GET['charge']) && !empty($_GET['charge'])){
			$where_charge['_string'] = "charge_person = {$_GET['charge']} and status = 1";
			$user_result = $co_model -> table('t_user') -> where($where_charge) -> select();
			foreach($user_result as $key => $val){
				$userid_arr_charge[] = $val['uid'];
			}
		}
	
		if(isset($_GET['salvation']) && !empty($_GET['salvation'])){
			if($_GET['salvation'] == 1){
				$where_all .= " and salvation != 0";
			}else{
				$where_all .= " and salvation = 0";
			}
		}
		
		if($userid_arr_username && $userid_arr_charge){
			$userid_arr_all = array_intersect($userid_arr_username,$userid_arr,$userid_arr_charge);
		}elseif($userid_arr_username && !$userid_arr_charge){
			if(empty($_GET['charge'])){
				$userid_arr_all = array_intersect($userid_arr_username,$userid_arr);
			}else{
				$userid_arr_all = array();
			}
		}elseif($userid_arr_charge && !$userid_arr_username){
			if(empty($_GET['username'])){
				$userid_arr_all = array_intersect($userid_arr,$userid_arr_charge);
			}else{
				$userid_arr_all = array();
			}
		}elseif(!$userid_arr_charge && !$userid_arr_username){
			if(empty($_GET['username']) && empty($_GET['charge'])){
				$userid_arr_all = $userid_arr;
			}else{
				$userid_arr_all = array();
			}
		}
		
		foreach($userid_arr_all as $key => $val){
			$userid_arr_all_str .= $val.',';
		}
		
		$userid_str = substr($userid_arr_all_str,0,strlen($userid_arr_all_str) - 1);
		
		if(isset($_GET['start_tm']) && isset($_GET['end_tm'])){
			if(strtotime($_GET['start_tm']) > strtotime($_GET['end_tm'])){
				$this -> error("付款开始时间不能大于结束时间");
			}
			$where_all .= " and pay_tm > ".strtotime($_GET['start_tm'])." and pay_tm < ".strtotime($_GET['end_tm'])."";
		}

		if($userid_str && !$_GET['uid']){
			$where['_string'] = "status = 0 and user_id in ({$userid_str})".$where_all;
		}elseif(($userid_str || !$userid_str) && $_GET['uid']){
			$where['_string'] = "status = 0 and user_id = {$_GET['uid']}".$where_all;
		}else{
			$where['_string'] = "status = 0 and user_id in (0)".$where_all;
		}
		
		$count = $co_model -> table('t_settle_account') -> where($where) -> count();
		import("@.ORG.Page");
		$param = http_build_query($_GET);
		$Page = new Page($count, 10, $param);
		$result = $co_model -> table('t_settle_account') -> where($where) -> limit($Page->firstRow . ',' . $Page->listRows) -> order('freeze_tm DESC') -> select();
		
		foreach($result as $key => $val){
			$where_username['uid'] = $val['user_id'];
			$username_result = $co_model -> table('t_user') -> where($where_username) -> select();
			$val['user_name'] = $username_result[0]['user_name'];
			$val['balance_period'] = date('Y年m月',strtotime($val['balance_period'].'01'));
			$result[$key] = $val;
		}
		
		
		$charge_result = $co_model -> table('t_charge') -> where(array('status' => 1)) -> select();
		$balance_period_result = $co_model -> table('t_settle_account') -> order('balance_period DESC') -> select();

		foreach($balance_period_result as $key => $val){
			if($val['balance_period'] != 0){
				$balance_period_arr_go[] = $val['balance_period'];
			}
		}

		$balance_period_arr = array_unique($balance_period_arr_go);
					
		foreach($balance_period_arr as $key => $val){
			$v['value_go'] = $val;
			$v['time_go'] = date('Y年m月',strtotime($val.'01'));
			$balance_period_need[$key] = $v;
		}

		$this -> assign('balance_period_need',$balance_period_need);
		$this -> assign('charge_result',$charge_result);
		$this -> assign('balance_period',$_GET['balance_period']);
		$this -> assign('settlement_patterns',$_GET['settlement_patterns']);
		$this -> assign('username',$_GET['username']);
		$this -> assign('charge',$_GET['charge']);
		$this -> assign('salvation',$_GET['salvation']);
		$this -> assign('start_tm',$_GET['start_tm']);
		$this -> assign('end_tm',$_GET['end_tm']);
		$Page->setConfig('header', '篇记录');
		$Page->setConfig('first', '<<');
		$Page->setConfig('last', '>>');
		$show = $Page->show();
		$this->assign("page", $show);
		$this -> assign("result",$result);
		$this -> display();
	
	}
	
	//冻结列表解冻
	function unfreeze_do(){
		$co_model = D('Cooperative.Incomemanage');
		$id = $_POST['id_arr'];

		if($_POST['id_arr']){
			foreach($id as $key => $val){
				$where['id'] = $val;
				$data['unfreeze_tm'] = time();
				$data['status'] = 1;
				$bef_result = $co_model -> table('t_settle_account') -> where($where) -> select();
				$user_result = $co_model -> table('t_user') -> where(array('uid' => $bef_result[0]['user_id'])) -> select();
				$result_go = $co_model -> update_operation($where,$data);
				$result[] = $result_go;
				$msg .= "编辑冻结列表记录(账号:{$user_result[0]['user_name']},结算周期:{$bef_result[0]['balance_period']}),流转状态(编辑前:已冻结;编辑后:业务审核)";
			}
		}elseif($_GET['id']){
			$where['id'] = $_GET['id'];
			$data['unfreeze_tm'] = time();
			$data['status'] = 1;
			$result = $co_model -> update_operation($where,$data);
			$bef_result = $co_model -> table('t_settle_account') -> where($where) -> select();
			$user_result = $co_model -> table('t_user') -> where(array('uid' => $bef_result[0]['user_id'])) -> select();
			$msg = "编辑冻结列表记录(账号:{$user_result[0]['user_name']},结算周期:{$bef_result[0]['balance_period']}),流转状态(编辑前:已冻结;编辑后:业务审核)";
		}
		$balance_period = $_GET['balance_period'];
		$settlement_patterns = $_GET['settlement_patterns'];
		$start_tm = $_GET['start_tm'];
		$end_tm = $_GET['end_tm'];
		$charge = $_GET['charge'];
		$salvation = $_GET['salvation'];
		$username = $_GET['username'];
		if($balance_period){
			$go .= "/balance_period/{$balance_period}";
		}
		if($settlement_patterns){
			$go .= "/settlement_patterns/{$settlement_patterns}";
		}
		if($start_tm && $end_tm){
			$go .= "/start_tm/{$start_tm}/end_tm/{$end_tm}";
		}
		if($charge){
			$go .= "/charge/{$charge}";
		}
		if($salvation){
			$go .= "/salvation/{$salvation}";
		}
		if($username){
			$go .= "/username/{$username}";
		}
		if($result){
			$this -> writelog($msg);
			$this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Balancemanage/freeze_list'.$go);
			$this -> success("操作成功");
		}else{
			$this -> error("操作失败");
		}
	
	}
	
	//冻结列表查看详情
	function freeze_detail(){
		$co_model = D('Cooperative.Incomemanage');
		$id = $_GET['id'];
		$where['id'] = $id;
		$where['status'] = 4;
		$result = $co_model -> table('t_settle_account') -> where($where) -> select();
		$this -> assign("result",$result);
		$this -> display();
	}
	
	
	//全局搜索列表
	function global_search_list(){
		$co_model = D('Cooperative.Incomemanage');
		$admin_id = $_SESSION['admin']['admin_id'];
		$userid_arr = $this -> cooperative_manager($admin_id);
		if(isset($_GET['balance_period']) && !empty($_GET['balance_period'])){
			$where_all .= " and balance_period = {$_GET['balance_period']}";
		}
		if(isset($_GET['settlement_patterns']) && !empty($_GET['settlement_patterns'])){
			$where_all .= " and settlement_patterns = {$_GET['settlement_patterns']}";
		}
		if(isset($_GET['username']) && !empty($_GET['username'])){
			$where_user['_string'] = "user_name like '%{$_GET['username']}%'";
			$user_result = $co_model -> table('t_user') -> where($where_user) -> select();
			foreach($user_result as $key => $val){
				$userid_arr_username[] = $val['uid'];
			}
		}
		if(isset($_GET['charge']) && !empty($_GET['charge'])){
			$where_user['_string'] = "charge_person = {$_GET['charge']} and status = 1";
			$user_result = $co_model -> table('t_user') -> where($where_user) -> select();
		
			foreach($user_result as $key => $val){
				$userid_arr_charge[] = $val['uid'];
			}
		}

		if(isset($_GET['salvation']) && !empty($_GET['salvation'])){
			if($_GET['salvation'] == 1){
				$where_all .= " and salvation != 0";
			}else{
				$where_all .= " and salvation = 0";
			}
		}
		
		if($userid_arr_username && $userid_arr_charge){
			$userid_arr_all = array_intersect($userid_arr_username,$userid_arr,$userid_arr_charge);
		}elseif($userid_arr_username && !$userid_arr_charge){
			if(empty($_GET['charge'])){
				$userid_arr_all = array_intersect($userid_arr_username,$userid_arr);
			}else{
				$userid_arr_all = array();
			}
		}elseif($userid_arr_charge && !$userid_arr_username){
			if(empty($_GET['username'])){
				$userid_arr_all = array_intersect($userid_arr,$userid_arr_charge);
			}else{
				$userid_arr_all = array();
			}
		}elseif(!$userid_arr_charge && !$userid_arr_username){
			if(empty($_GET['username']) && empty($_GET['username'])){
				$userid_arr_all = $userid_arr;
			}else{
				$userid_arr_all = array();
			}
		}
	
		foreach($userid_arr_all as $key => $val){
			$userid_arr_all_str .= $val.',';
		}
		
		$userid_str = substr($userid_arr_all_str,0,strlen($userid_arr_all_str) - 1);
		
		if($_GET['time_type'] == 1){
			if(isset($_GET['start_tm']) && isset($_GET['end_tm'])){
				if(strtotime($_GET['start_tm']) > strtotime($_GET['end_tm'])){
					$this -> error("查询付款开始时间不能大于结束时间");
				}
				$where_all .= " and pay_tm > ".strtotime($_GET['start_tm'])." and pay_tm < ".strtotime($_GET['end_tm'])."";
			}
		}elseif($_GET['time_type'] == 2){
			if(isset($_GET['start_tm']) && isset($_GET['end_tm'])){
				if(strtotime($_GET['start_tm']) > strtotime($_GET['end_tm'])){
					$this -> error("查询审核开始时间不能大于结束时间");
				}
				$where_all .= " and operation_tm > ".strtotime($_GET['start_tm'])." and operation_tm < ".strtotime($_GET['end_tm'])."";
			}
		}
		
		if(isset($_GET['status']) && $_GET['status'] != '' && $_GET['status'] != 11){
			$where_all .= " and status = {$_GET['status']}";
		}
		if($_GET['uid']){
			$where['_string'] = "user_id = ({$_GET['uid']})".$where_all;
		}else{
			$where['_string'] = "user_id in ({$userid_str})".$where_all;
		}
		$count = $co_model -> table('t_settle_account') -> where($where) -> count();
		import("@.ORG.Page");
		$param = http_build_query($_GET);
		$Page = new Page($count, 10, $param);
		$result = $co_model -> table('t_settle_account') -> where($where) -> limit($Page->firstRow . ',' . $Page->listRows) -> order('balance_period DESC') -> select();
		
		foreach($result as $key => $val){
			$where_username['uid'] = $val['user_id'];
			$username_result = $co_model -> table('t_user') -> where($where_username) -> select();
			$val['user_name'] = $username_result[0]['user_name'];
			$val['balance_period'] = date('Y年m月',strtotime($val['balance_period'].'01'));
			if($val['status'] == 1){
				$val['link'] = "__URL__/operation_check_list/username/{$val['user_name']}";
			}elseif($val['status'] == 2){
				$val['link'] = "__URL__/finance_check_list/username/{$val['user_name']}";
			}elseif($val['status'] == 3){
				$val['link'] = "__URL__/obligation_list/username/{$val['user_name']}";
			}elseif($val['status'] == 4){
				$val['link'] = "__URL__/account_paid_list/username/{$val['user_name']}";
			}elseif($val['status'] == 0){
				$val['link'] = "__URL__/freeze_list/username/{$val['user_name']}";
			}
			$result[$key] = $val;
		}
		
		
		$charge_result = $co_model -> table('t_charge') -> where(array('status' => 1)) -> select();
		$balance_period_result = $co_model -> table('t_settle_account') -> order('balance_period DESC') -> select();

		foreach($balance_period_result as $key => $val){
			if($val['balance_period'] != 0){
				$balance_period_arr_go[] = $val['balance_period'];
			}
		}

		$balance_period_arr = array_unique($balance_period_arr_go);
					
		foreach($balance_period_arr as $key => $val){
			$v['value_go'] = $val;
			$v['time_go'] = date('Y年m月',strtotime($val.'01'));
			$balance_period_need[$key] = $v;
		}
	
		$this -> assign('balance_period_need',$balance_period_need);
		$this -> assign('charge_result',$charge_result);
		$this -> assign('balance_period',$_GET['balance_period']);
		$this -> assign('settlement_patterns',$_GET['settlement_patterns']);
		$this -> assign('username',$_GET['username']);
		$this -> assign('charge',$_GET['charge']);
		$this -> assign('salvation',$_GET['salvation']);
		$this -> assign('time_type',$_GET['time_type']);
		$this -> assign('start_tm',$_GET['start_tm']);
		$this -> assign('end_tm',$_GET['end_tm']);
		$this -> assign('status',$_GET['status']);
		$Page->setConfig('header', '篇记录');
		$Page->setConfig('first', '<<');
		$Page->setConfig('last', '>>');
		$show = $Page->show();
		$this->assign("page", $show);
		$this -> assign("result",$result);
		$this -> display();
	}
	
	//搜索负责人
	function search_charge($charge_name){
		$co_model = D('Cooperative.Incomemanage');
		$result = $co_model -> table('t_charge') -> where(array('charge_name' => $charge_name)) -> select();

		return $result[0]['id'];
	}
	
}  