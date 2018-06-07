<?php 

class Channelsettle_pAction extends CommonAction{
	// 商务确认列表
	function operation_check_list(){
		ini_set('memory_limit','200M');
		$client_model = D('Channel_cooperation.channel_cooperation_p');
		$model = new Model();
		
		//限制客户查看权限
		$admin_id = $_SESSION['admin']['admin_id'];
		$admin_filter_result = $model -> table('sj_admin_filter') -> where(array('source_type' => 1,'source_value' => $admin_id,'target_type' => 2,'filter_type' => 2)) -> field('target_value') -> select();
		foreach($admin_filter_result as $key => $val){
			$admin_cid[] = $val['target_value']; //过滤目标值
		}
		
		$all_client = $client_model -> table('co_client_list') -> order('id') -> select();
		if($admin_cid){
			foreach($all_client as $key => $val){
				$client_channel_result = $client_model -> table('co_client_channel') -> where(array('client_id' => $val['id'])) -> select();
				$my_cid_power = array();
				foreach($client_channel_result as $k => $v){
					$my_cid_power[] = $v['cid'];
				}
				if(!array_diff($my_cid_power,$admin_cid)){
					$my_client_id_str .= $val['id'].',';
				}
			}
		}else{
			$my_client_id = '';
		}
		$my_client_id = substr($my_client_id_str,0,-1);
		
		$client_name = trim($_GET['client_name']); //模糊查询条件客户名称
		$chname = trim($_GET['chname']); // 模糊查询条件渠道名称
		$start_tm  = date('Ym',strtotime($_GET['start_tm'].'-01')); //模糊查询条件开始日期
		$end_tm = date('Ym',strtotime($_GET['end_tm'].'-01')); //模糊查询条件结束日期
		if($_GET['start_tm'] && strtotime($_GET['start_tm'].'-01') > strtotime($_GET['end_tm'].'-01')){
			$this -> error("开始时间不能大于结束时间");
		}
		
		$count_power_result = $model -> table('sj_admin_filter') -> where(array('source_type' => 1,'source_value' => $admin_id,'target_type' => 9)) -> find();
	
		if($count_power_result['filter_type'] == 2){
			$where_go .= " and client_id in({$my_client_id})";
		}
		
		if($chname){
			$channel_where['_string'] = "chname like '%{$chname}%' and status = 1";
			$channel_result = $model -> table('sj_channel') -> where($channel_where) -> select();
			foreach($channel_result as $key => $val){
				$channel_cid_str .= $val['cid'].',';
			}
			$cid_str = rtrim($channel_cid_str,','); //渠道主键id用于in查询
			$where_client['_string'] = "cid in ({$cid_str})";
			$where_my_channel['_string'] = "account_attr<>4 AND cid in ({$cid_str}) and status = 1";
			$my_channel_result = $client_model -> table('co_channel_check') -> where($where_my_channel) -> select();
			foreach($my_channel_result as $key => $val){
				$my_month_str_go .= $val['month'].',';
			}
			$my_month_str = substr($my_month_str_go,0,-1); //账号属性不为4(论坛)的月份用于in查询
			$client_channel_result = $client_model -> table('co_client_channel') -> where($where_client) -> select();
		
			foreach($client_channel_result as $key => $val){
				$channel_client_id_arr[] = $val['client_id']; //根据渠道id从co_client_channel关联表中得到的客户主键id数组
			}
		}
	
		if($client_name){
			$client_where['_string'] = "client_name like '%{$client_name}%' and status != 0";
			$client_result = $client_model -> table('co_client_list') -> where($client_where) -> select();
			foreach($client_result as $key => $val){
				$client_id_arr[] = $val['id']; //根据客户模糊查询结果得到的客户主键id数组
			}
		}
		
		if($_GET['from'] == 1 && $_GET['check_ids']){
			$check_ids = $_GET['check_ids']; //629,638,612,613,614,
			$check_ids_str = substr($check_ids,0,-1); //629,638,612,613,614
			$where_go .= " and id in ({$check_ids_str})";
		}elseif($client_name && $chname){
			$client_arr = array_intersect($channel_client_id_arr,$client_id_arr); //数组取交集
			foreach($client_arr as $key => $val){
				$client_str_go .= $val.',';
			}
			$client_str = substr($client_str_go,0,-1);
			$where_go .= " and client_id in ({$client_str}) and month in ({$my_month_str})";
		}elseif($client_name && !$chname){
			$client_arr = $client_id_arr;
			foreach($client_arr as $key => $val){
				$client_str_go .= $val.',';
			}
			$client_str = substr($client_str_go,0,-1);
			$where_go .= " and client_id in ({$client_str})";
		}elseif(!$client_name && $chname){
			$client_arr = $channel_client_id_arr;
			foreach($client_arr as $key => $val){
				$client_str_go .= $val.',';
			}
			$client_str = substr($client_str_go,0,-1);
			
			$where_go .= " and client_id in ({$client_str}) and month in ({$my_month_str})";
		}

		if($_GET['start_tm']){
			$where_go .= " and month >= {$start_tm}";
		}
		if($_GET['end_tm']){
			$where_go .= " and month <= {$end_tm}";
		}
		$where['_string'] = "account_attr<>4 AND status = 1".$where_go; //总查询条件
		
		$count = $client_model -> table('co_channel_check') -> where($where) -> group('client_id,month') ->  order('month DESC') -> select(); //总记录数
		import("@.ORG.Page");
		$param = http_build_query($_GET);
		$Page = new Page(count($count), 50, $param);
        if($_GET['from'] == 1){
		    $result = $client_model -> table('co_channel_check') -> where($where) -> field('client_id,month,sum(activation)') -> group('client_id,month') -> order('month DESC,sum(activation) DESC') -> select();
        }else{
		    $result = $client_model -> table('co_channel_check') -> where($where) -> limit($Page->firstRow . ',' . $Page->listRows) -> field('client_id,month,sum(activation)') -> group('client_id,month') -> order('month DESC,sum(activation) DESC') -> select();
                }
		if(!$_GET['p']){
			$_GET['p'] = 1;
		}
		if(!$_GET['lr']){
			$_GET['lr'] = 50;
		}

		foreach($result as $key => $val){ //co_channel_check表client_id,month,sum(activation)字段
			$val['num'] = 1+$key+$_GET['lr']*($_GET['p']-1);
			$channel_result = $client_model -> table('co_channel_check') -> where(array('month' => $val['month'],'client_id' => $val['client_id'],'account_attr'=>array('neq', '4'),'status' => 1)) -> order('activation DESC') -> select();
			$client_result = $client_model -> table('co_client_list') -> where(array('id' => $val['client_id'])) -> select();
			$val['client_name'] = $client_result[0]['client_name']; //关联客户名称
			
			$all_activation_arr = array(); //激活量数组，用于求和
			$all_settle_amount_arr = array(); //结算金额数组，用于求和
			$all_salvation_arr = array(); //补差数组，用于求和
			$all_amount_pay_arr = array(); //应付金额数组，用于求和
			$settle_entity_status = 1;
			foreach($channel_result as $k => $v){
				$v['comments'] = mb_substr($v['comment'],0,4,'utf-8'); //截取备注
				$all_activation_arr[] = $v['activation'];
				$v['activation'] = number_format($v['activation'],0,'.',','); //激活量取整
				$v['average'] = number_format($v['average'],0,'.',','); //日均激活量取整
				$chname_result = $model -> table('sj_channel') -> where(array('cid' => $v['cid'])) -> select();
				$v['chname'] = $chname_result[0]['chname']; //关联渠道名称
				if ($v['settle_entity'] == '2') {
					$settle_entity_status = 2;
				}
				$all_settle_amount_arr[] = $v['settle_amount'];
				$all_salvation_arr[] = $v['salvation'];
				$all_amount_pay_arr[] = $v['amount_pay'];
				
				//最低结算值
				$min_result = $model -> table('pu_config') -> where(array('config_type' => 'saveminpay','status' => 1)) -> select();
				if($v['settle_amount'] < $min_result[0]['configcontent']){
					$v['warning'] = 1;
				}else{
					$v['warning'] = 0;
				}
				$v['settle_amount'] = number_format($v['settle_amount'],2,'.',',');
				$v['amount_pay'] = number_format($v['amount_pay'],2,'.',',');
				if($v['price_type'] == 1){
					$where_price['_string'] = "cid = {$v['cid']} and month <= {$v['month']} and price_type = 1";
					$price_result = $client_model -> table('co_channel_price') -> where($where_price) -> order('month DESC') -> limit('0,1') -> select();
					$my_price_result = $client_model -> table('co_price_history') -> where(array('did' => $price_result[0]['id'])) -> select();
					$v['price_name'] = $my_price_result[0]['price_name'];
					$v['my_price'] = json_decode($my_price_result[0]['price'],true);
			
				}
				$channel_result[$k] = $v;
			}
			
			
			
			$the_activation_arr[] = array_sum($all_activation_arr);
			if($settle_entity_status == 1){
				$the_settle_amount_arr[] = array_sum($all_settle_amount_arr);
				$the_salvation_arr[] = array_sum($all_salvation_arr);
				$the_amount_pay_arr[] = array_sum($all_amount_pay_arr);
			}else{
				$the_settle_amount_arr[] = $all_settle_amount_arr[0];
				$the_salvation_arr[] = $all_salvation_arr[0];
				$the_amount_pay_arr[] = $all_amount_pay_arr[0];
			}
			
			$val['cid_result'] = $channel_result;
			$result[$key] = $val;
		}

		$all_activation = number_format(array_sum($the_activation_arr),0,'.',',');
		$all_settle_amount = number_format(array_sum($the_settle_amount_arr),2,'.',',');
		$all_salvation = number_format(array_sum($the_salvation_arr),2,'.',',');
		$all_amount_pay = number_format(array_sum($the_amount_pay_arr),2,'.',',');
		
		$Page->setConfig('header', '篇记录');
		$Page->setConfig('first', '<<');
		$Page->setConfig('last', '>>');
		$show = $Page->show();
		$this -> assign('all_activation',$all_activation);
		$this -> assign('all_settle_amount',$all_settle_amount);
		$this -> assign('all_salvation',$all_salvation);
		$this -> assign('all_amount_pay',$all_amount_pay);
		$this -> assign('min_pay',$min_result[0]['configcontent']);
		$this->assign("page", $show);
		$this -> assign('p',$_GET['p']);
		$this -> assign('lr',$_GET['lr']);
		$this -> assign('all_settle_amount',$all_settle_amount);
		$this -> assign('all_salvation',$all_salvation);
		$this -> assign('all_amount_pay',$all_amount_pay);
		$this -> assign('result',$result);
		$this -> assign('client_name',$client_name);
		$this -> assign('chname',$chname);
		$this -> assign('start_tm',$_GET['start_tm']);
		$this -> assign('end_tm',$_GET['end_tm']);
		if($_GET['from'] == 1){
            $file_xls = '运营审核报表_'.date('Ymd').".xls";//文件名
            header("Content-type:text/csv");
            header("Content-Disposition:attachment;filename=".$file_xls);
            header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
            header('Expires:0');
            header('Pragma:public');
            $str = $this -> fetch('operation_check_list2');
            echo $str;
            exit;
			
			/*foreach($result as $key => $val){
				foreach($val['cid_result'] as $k => $v){
					$file_str .= $val['num'].','.$val['month'].','.$val['client_name'].','.$v['chname'].',"'.$v['activation'].'","'.$v['average'].'",'.$v['price'].',"'.$v['settle_amount'].'","'.$v['salvation'].'",'.$v['taxt'].'%,"'.$v['amount_pay'].'"'."\n";
				}
			}
		
			$file_gos = '运营审核报表_'.date('Ymd').".csv";//文件名
			if(strpos($_SERVER["HTTP_USER_AGENT"],"MSIE")){  
				$file_go = urlencode($file_gos); 
			}else{
				$file_go = $file_gos;
			}
			header( "Cache-Control: public" );
			header( "Pragma: public" );
			header("Content-type:application/vnd.ms-excel");
			header('Content-Disposition:attachment;filename='.$file_go);
			header('Content-Type:APPLICATION/OCTET-STREAM');
			ob_start();
			$header_str =  iconv("UTF-8",'GBK',"序号,月份,客户名称,渠道名称,激活量,日均激活量,单价,结算金额,补差,税率,应付金额\n");
			$file_str_go=  iconv("UTF-8",'GBK',$file_str);
			echo $header_str;
			echo $file_str_go;
			echo iconv("UTF-8",'GBK','总计,-,,-,"'.$all_activation.'",,-,"'.$all_settle_amount.'","'.$all_salvation.'",,"'.$all_amount_pay.'"');
			ob_end_flush();
			exit;*/
		}
		
		$this -> display();
	
	}

	function channel_activation(){
		$coefficient_model = D('Channel_cooperation.channel_coefficient_p');
		$model = new Model();
		$cid = $_GET['cid'];
		$month = $_GET['month'];
		$start_tm = strtotime($month.'01');
		$end_tm = strtotime(date('Ymt',strtotime($month.'01')));
		$where['_string'] = "cid = {$cid} and submit_tm >= {$start_tm} and submit_tm <= {$end_tm} and status = 1";
		$activation_result = $coefficient_model -> table('activation_coefficient_state') -> where($where) -> select();
		$channel_result = $model -> table('sj_channel') -> where(array('cid' => $cid)) -> select();
		foreach($activation_result as $key => $val){
			$val['counts'] = number_format($val['counts']);
			$activation_result[$key] = $val;
		}
		$this -> assign('activation_result',$activation_result);
		$this -> assign('chname',$channel_result[0]['chname']);
		$this -> assign('cid',$cid);
		$this -> assign('month',$month);
		if($_GET['from'] == 1){
			foreach($activation_result as $key => $val){
				$val['the_tm'] = date('Y-m-d',$val['submit_tm']);
				$file_str .= $val['the_tm'].',"'.$val['counts'].'"'."\n";
			}
			
			$file_gos = "{$channel_result[0]['chname']}{$month}激活量_".date('Ymd').".csv";//文件名
			if(strpos($_SERVER["HTTP_USER_AGENT"],"MSIE")){  
				$file_go = urlencode($file_gos); 
			}else{
				$file_go = $file_gos;
			}
			header( "Cache-Control: public" );
			header( "Pragma: public" );
			header("Content-type:application/vnd.ms-excel");
			header('Content-Disposition:attachment;filename='.$file_go);
			header('Content-Type:APPLICATION/OCTET-STREAM');
			ob_start();
			$header_str =  iconv("UTF-8",'GBK',"日期,激活量");
			$file_str_go=  iconv("UTF-8",'GBK',$file_str);
			echo $header_str."\n";
			echo $file_str_go;
			ob_end_flush();
			exit;
		}
		$this -> display();
	}

	// 补差编辑窗口
	function edit_salvation_show(){
		$client_model = D('Channel_cooperation.channel_cooperation_p');
		$chname = $_GET['chname'];
		$client_name = $_GET['client_name'];
		$co_group = $_GET['co_group'];
		$billing = $_GET['billing'];
		$p = $_GET['p'];
		$lr = $_GET['lr'];
		$cid = $_GET['cid'];
		$settle_entity = $_GET['settle_entity'];
		
		$month = date('Ym',strtotime($_GET['month'].'01'));
		if ($settle_entity == '2') {
			$where['_string'] = "client_id = {$cid} and month = {$month} and account_attr <> 4";
			$salvation_result = $client_model -> table('co_channel_check') -> where($where) -> select();
		} else {
			$where['_string'] = "cid = {$cid} and month = {$month} and account_attr <> 4";
			$salvation_result = $client_model -> table('co_channel_check') -> where($where) -> select();
		}
		if($salvation_result[0]['salvation'] < 0){
			$salvation = abs($salvation_result[0]['salvation']);
			$salvation_type = 2;
		}else{
			$salvation = $salvation_result[0]['salvation'];
			$salvation_type = 1;
		}
		
		$this -> assign('salvation',$salvation);
		$this -> assign('salvation_type',$salvation_type);
		$this -> assign('chname',$chname);
		$this -> assign('client_name',$client_name);
		$this -> assign('co_group',$co_group);
		$this -> assign('billing',$billing);
		$this -> assign('start_tm',$_GET['start_tm']);
		$this -> assign('end_tm',$_GET['end_tm']);
		$this -> assign('p',$p);
		$this -> assign('lr',$lr);
		$this -> assign('cid',$cid);
		$this -> assign('month',$month);
		$this -> assign('settle_entity',$settle_entity);
		$this -> display();
	}
	
	// 补差编辑接口
	function edit_salvation(){
		$client_model = D('Channel_cooperation.channel_cooperation_p');
		$chname = $_GET['chname'];
		$client_name = $_GET['client_name'];
		$p = $_GET['p'];
		$lr = $_GET['lr'];
		$co_group = $_GET['co_group'];
		$billing = $_GET['billing'];
		$start_tm = $_GET['start_tm'];
		$end_tm = $_GET['end_tm'];
		$cid = $_GET['cid'];
		$month = $_GET['month'];
		$settle_entity = $_GET['settle_entity'];
		if($chname){
			$where_go .= "/chname/{$chname}";
		}
		if($client_name){
			$where_go .= "/client_name/{$client_name}";
		}
		if(isset($co_group)){
			$where_go .= "/co_group/{$co_group}";
		}
		if($billing){
			$where_go .="/billing/{$billing}";
		}
		if($p){
			$where_go .= "/p/{$p}";
		}
		if($lr){
			$where_go .= "/lr/{$lr}";
		}
		if($start_tm){
			$where_go .= "/start_tm/{$start_tm}";
		}
		if($end_tm){
			$where_go .= "/end_tm/{$end_tm}";
		}
		if($_GET['salvation_type'] == 2){
			$salvation = '-'.$_GET['salvation'];
		}else{
			$salvation = $_GET['salvation'];
		}
		if($_GET['salvation'] == "请输入调整金额" || !$_GET['salvation']){
			$salvation = 0;
		}
		
		if ($settle_entity == '2') {
			$where['_string'] = "client_id = {$cid} and month = {$month} and account_attr != 4";
			$have_been = $client_model -> table('co_channel_check') -> where($where) -> select();
			$data = array(
				'amount_pay' => round(($salvation + $have_been[0]['price']*$have_been[0]['activation_sum'])*(1-$have_been[0]['taxt']/100),2),
				'salvation' => $salvation,
				'update_tm' => time()
			);
			$log_result = $this -> logcheck(array('client_id' => $cid,'month' => $month),'co_channel_check',$data,$client_model);
		} else {
			$where['_string'] = "cid = {$cid} and month = {$month} and account_attr <> 4";
			$have_been = $client_model -> table('co_channel_check') -> where($where) -> select();
			$data = array(
				'amount_pay' => round(($salvation + $have_been[0]['settle_amount'])*(1-$have_been[0]['taxt']/100),2),
				'salvation' => $salvation,
				'update_tm' => time()
			);
			$log_result = $this -> logcheck(array('cid' => $cid,'month' => $month),'co_channel_check',$data,$client_model);
		}

		$result = $client_model -> table('co_channel_check') -> where($where) -> save($data);

		if($result){
			$this -> writelog("已修改cid为{$cid}，月份为{$month}的运营审核记录".$log_result, 'co_channel_check', $cid,__ACTION__ ,'','edit');
			$this -> assign('jumpUrl','/index.php/Channel_cooperation/Channelsettle_p/business_list'.$where_go);
			$this -> success("编辑成功");
		}else{
			$this -> error("编辑失败");
		}
		
	}
	
	// 备注编辑窗口
	function edit_comment_show(){
		$client_model = D('Channel_cooperation.channel_cooperation_p');
		$charge_id = $_GET['charge_id'];
		$chname = $_GET['chname'];
		$client_name = $_GET['client_name'];
		$p = $_GET['p'];
		$lr = $_GET['lr'];
		$start_tm = $_GET['start_tm'];
		$end_tm = $_GET['end_tm'];
		$cid = $_GET['cid'];
		$from = $_GET['from'];
		$month = $_GET['month'];
		$price = $_GET['price'];
		$status_id = $_GET['status_id'];
		$category_id = $_GET['category_id'];
		$co_group = $_GET['co_group'];
		$channel_attribute = $_GET['channel_attribute'];
		$billing = $_GET['billing'];
		$settle_entity = $_GET['settle_entity'];
		
		if ($settle_entity == '2') {
			$where['_string'] = "client_id = {$cid} and month = {$month} and account_attr <> 4";
			$comment_result = $client_model -> table('co_channel_check') -> where($where) -> select();
		} else{
			$where['_string'] = "cid = {$cid} and month = {$month} and account_attr <> 4";
			$comment_result = $client_model -> table('co_channel_check') -> where($where) -> select();
		}

		$this -> assign('comment',$comment_result[0]['comment']);
		$this -> assign('charge_id',$charge_id);
		$this -> assign('chname',$chname);
		$this -> assign('client_name',$client_name);
		$this -> assign('settle_entity',$settle_entity);
		$this -> assign('start_tm',$_GET['start_tm']);
		$this -> assign('end_tm',$_GET['end_tm']);
		$this -> assign('from',$from);
		$this -> assign('p',$p);
		$this -> assign('lr',$lr);
		$this -> assign('cid',$cid);
		$this -> assign('month',$month);
		$this -> assign('price',$price);
		$this -> assign('status_id',$status_id);
		$this -> assign('category_id',$category_id);
		$this -> assign('co_group',$co_group);
		$this -> assign('channel_attribute',$channel_attribute);
		$this -> assign('billing',$billing);
		$this -> display();
	}

	// 备注编辑接口
	function edit_comment(){
		$client_model = D('Channel_cooperation.channel_cooperation_p');
		$charge_id = $_GET['charge_id'];
		$chname = $_GET['chname'];
		$client_name = $_GET['client_name'];
		$p = $_GET['p'];
		$lr = $_GET['lr'];
		$start_tm = $_GET['start_tm'];
		$end_tm = $_GET['end_tm'];
		$cid = $_GET['cid'];
		$month = $_GET['month'];
		$price = $_GET['price'];
		$status_id = $_GET['status_id'];
		$category_id = $_GET['category_id'];
		$co_group = $_GET['co_group'];
		$channel_attribute = $_GET['channel_attribute'];
		$billing = $_GET['billing'];
		$settle_entity = $_GET['settle_entity'];
		if($charge_id){
			$where_go .= "/charge_id/{$charge_id}";
		}
		if($price){
			$where_go .= "/price/{$price}";
		}
		if($status_id){
			$where_go .= "/status_id/{$status_id}";
		}
		if($category_id){
			$where_go .= "/category_id/{$category_id}";
		}
		if(isset($co_group)){
			$where_go .= "/co_group/{$co_group}";
		}
		if($channel_attribute){
			$where_go .= "/channel_attribute/{$channel_attribute}";
		}
		if($billing){
			$where_go .= "/billing/{$billing}";
		}
		if($chname){
			$where_go .= "/chname/{$chname}";
		}
		if($client_name){
			$where_go .= "/client_name/{$client_name}";
		}
		if($p){
			$where_go .= "/p/{$p}";
		}
		if($lr){
			$where_go .= "/lr/{$lr}";
		}
		if($start_tm){
			$where_go .= "/start_tm/{$start_tm}";
		}
		if($end_tm){
			$where_go .= "/end_tm/{$end_tm}";
		}
		$comment = $_GET['comment'];
		$data = array(
			'comment' => $comment,
		);
		if ($settle_entity == '2') {
			$log_result = $this -> logcheck(array('client_id' => $cid,'month' => $month),'co_channel_check',$data,$client_model);
			$where['_string'] = "client_id = {$cid} and month = {$month} and account_attr <> 4";
			$result = $client_model -> table('co_channel_check') -> where($where) -> save($data);
		} else {
			$log_result = $this -> logcheck(array('cid' => $cid,'month' => $month),'co_channel_check',$data,$client_model);
			$where['_string'] = "cid = {$cid} and month = {$month} and account_attr <> 4";
			$result = $client_model -> table('co_channel_check') -> where($where) -> save($data);
		}
		
		if($result){
			if($log_result != 'get old null!'){
				$this -> writelog("已编辑cid为{$cid}，月份为{$month}的运营渠道".$log_result, 'co_channel_check', $cid,__ACTION__ ,'','edit');
			}else{
				$this -> writelog("已添加cid为{$cid}，月份为{$month}的运营渠道备注为".$comment, 'co_channel_check', $cid,__ACTION__ ,'','add');
			}
			if($_GET['from'] == 2){
				$this -> assign('jumpUrl','/index.php/Channel_cooperation/Channelsettle_p/financial_settle'.$where_go);
			}elseif($_GET['from'] == 4){
				$this -> assign('jumpUrl','/index.php/Channel_cooperation/Channellist_p/settle_list'.$where_go);
			}elseif($_GET['from'] == 5){
				$this -> assign('jumpUrl','/index.php/Channel_cooperation/Channelsettle_p/business_list'.$where_go);
			}elseif($_GET['from'] == 99){
					$this -> assign('jumpUrl','/index.php/Channel_cooperation/Channelsettle_p/forum_settle'.$where_go);
			}else{
				$this -> assign('jumpUrl','/index.php/Channel_cooperation/Channelsettle_p/operation_check_list'.$where_go);
			}
			$this -> success("编辑成功");
		}else{
			$this -> error("编辑失败");
		}
	}

	function show_channel_settle(){
		$coefficient_model = D('Channel_cooperation.channel_coefficient_p');
		$model = new Model();
		$client_model = D('Channel_cooperation.channel_cooperation_p');
		include (dirname(__FILE__).'/../../ORG/PHPExcel/PHPExcel.php');
		$objPHPExcel = new PHPExcel();
		//设置属性
		$objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
		->setLastModifiedBy("Maarten Balliauw")
		->setTitle("Office 2007 XLSX Test Document")
		->setSubject("Office 2007 XLSX Test Document")
		->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
		->setKeywords("office 2007 openxml php")
		->setCategory("Test result file");
		
		$cid = $_GET['cid'];
		
		$month = $_GET['month'];
	
		$start_tm = strtotime($month.'01');
		$end_tm = strtotime(date('Ymt',strtotime($month.'01')));
		$my_month = date('Y年m月',$start_tm);
		$channel_result = $model -> table('sj_channel') -> where(array('cid' => $cid)) -> select();
	
		$where['_string'] = "cid = {$cid} and submit_tm >= {$start_tm} and submit_tm <= {$end_tm} and status = 1";
		$activation_result = $coefficient_model -> table('activation_coefficient_state') -> where($where) -> order('submit_tm') -> select();
		
		foreach($activation_result as $key => $val){
			$activation_arr[] = $val['counts'];
			$val['counts'] = number_format($val['counts']);
			$activation_result[$key] = $val;
		}
		$all_activation = array_sum($activation_arr);
		$price_result = $client_model -> table('co_channel_check') -> where(array('cid' => $cid,'month' => $month)) -> select();

		$amount_account = sprintf('%.2f',$all_activation*$price_result[0]['price']);
		$all_activation = number_format($all_activation);
		
		$client_result = $client_model -> table('co_client_channel') -> where(array('cid' => $cid)) -> select();
		$client_attr_result = $client_model -> table('co_client_list') -> where(array('id' => $client_result[0]['client_id'])) -> select();
        $account_attr = $price_result[0]['account_attr'];
		//if($account_attr == 1){
		//	$taxt_result = $model -> table('pu_config') -> where(array('config_type' => 'savebusiness','status' => 1)) -> select();
		//}elseif($account_attr == 2){
		//	$taxt_result = $model -> table('pu_config') -> where(array('config_type' => 'savepersonal','status' => 1)) -> select();
		//}elseif($account_attr == 3){
		//	$taxt_result = $model -> table('pu_config') -> where(array('config_type' => 'savepersonal_no','status' => 1)) -> select();
		//}
		//$taxt = $taxt_result[0]['configcontent'].'%'; //税率
		$taxt = $price_result[0]['taxt'];
        $taxt_account = $amount_account*$taxt/100;  //税款
        $actual_amount_account = $amount_account - $taxt_account; //实际结算金额=结算金额-税款
        if (!$taxt) $taxt = 0;
        if (!$taxt_account) $taxt_account = 0;
        
        // assign前将数据展示格式转换一下
        $taxt_account = number_format($taxt_account,2,'.',',');
        $amount_account = number_format($amount_account,2,'.',',');
        $actual_amount_account = number_format($actual_amount_account,2,'.',',');
        
		$this -> assign('chnames',$channel_result[0]['chname']);
		$this -> assign('my_month',$my_month);
		$this -> assign('all_activation',$all_activation);
		$this -> assign('price',$price_result[0]['price']);
        $this -> assign('account_attr', $account_attr);
        $this -> assign('taxt', $taxt);
        $this -> assign('taxt_account', $taxt_account);
		$this -> assign('amount_account',$amount_account);
        $this -> assign('actual_amount_account', $actual_amount_account);
		$this -> assign('activation_result',$activation_result);
		$this -> assign('chname',$chname);
		$this -> assign('client_name',$client_name);
		$this -> assign('cid',$cid);
		$this -> assign('month',$month);
		if($_GET['from'] == 1){
			$sheet1 = $objPHPExcel->createSheet();
			$objPHPExcel->setActiveSheetIndex(1)
			->setCellValue('A1', '安智市场对账单')
			->setCellValue('A2', '渠道名称')
			->setCellValue('B2', "{$channel_result[0]['chname']}")
			->setCellValue('A3', "月份")
			->setCellValue('B3', "{$month}")
			->setCellValue('A4', "日期")
			->setCellValue('B4', "激活量");
			for($i=0;$i<count($activation_result);$i++){
				$objPHPExcel->setActiveSheetIndex(1)
				->setCellValue('A'.($i+5).'',date('Y/m/d',$activation_result[$i]['submit_tm']))
				->setCellValue('B'.($i+5).'',$activation_result[$i]['counts']);
			}
            // 后面的位置不同情况会不一样，所以设置成相对位置，变化时只需统一改此相对位置
            $pos_all_activation = 5;//总激活量行相对位置
            $pos_price = 6;//单价行相对位置
            $pos_shift = 0;
            // 如果当前渠道所属客户为个人税率属性，还会展示多两行，税率和税额
            if ($account_attr == 2) {
                $pos_shift = 2;//插入了两行，后面的位置需加上此数据
                $pos_taxt = 7;//税率行相对位置
                $pos_taxt_account = 8;//税额行相对位置
            }
            $pos_amount_account = 7 + $pos_shift;//结算金额行相对位置
            $pos_tec_confirm = 8 + $pos_shift;//技术确认(激活数)行相对位置
            $pos_dep_confirm = 10 + $pos_shift;//部门确认行相对位置
            
            // 如果是个人税率属性，则展示【实际结算金额】，非【结算金额】（因为展示在同一行，这里直接替换展示值）
            $amount_account_title = "结算金额";
            if ($account_attr == 2) {
                $amount_account_title = "实际结算金额";
                $amount_account = $actual_amount_account;
            }
            
			$objPHPExcel->setActiveSheetIndex(1)
			->setCellValue('A'.(count($activation_result)+$pos_all_activation).'', "总激活量")
			->setCellValue('B'.(count($activation_result)+$pos_all_activation).'', "{$all_activation}")
			->setCellValue('A'.(count($activation_result)+$pos_price).'', "单价")
			->setCellValue('B'.(count($activation_result)+$pos_price).'', "￥{$price_result[0]['price']}");
            if ($account_attr == 2) {
                $objPHPExcel->setActiveSheetIndex(1)
                ->setCellValue('A'.(count($activation_result)+$pos_taxt).'', "税率")
                ->setCellValue('B'.(count($activation_result)+$pos_taxt).'', "{$taxt}%")
                ->setCellValue('A'.(count($activation_result)+$pos_taxt_account).'', "税额")
                ->setCellValue('B'.(count($activation_result)+$pos_taxt_account).'', "￥{$taxt_account}");
            }
            $objPHPExcel->setActiveSheetIndex(1)
			->setCellValue('A'.(count($activation_result)+$pos_amount_account).'', "{$amount_account_title}")
			->setCellValue('B'.(count($activation_result)+$pos_amount_account).'', "￥{$amount_account}");
			if($_GET['type'] != 2){
				$objPHPExcel->setActiveSheetIndex(1)
				->setCellValue('A'.(count($activation_result)+$pos_tec_confirm).'', "技术确认(激活数)：\r\n日期：")
				->setCellValue('A'.(count($activation_result)+$pos_dep_confirm).'', "部门确认：\r\n日期：");
				$objPHPExcel->getActiveSheet()->getStyle('A'.(count($activation_result)+$pos_tec_confirm).'')->getAlignment()->setWrapText(true);
				$objPHPExcel->getActiveSheet()->getStyle('A'.(count($activation_result)+$pos_dep_confirm).'')->getAlignment()->setWrapText(true);
			}
			
			
			//合并单元格
			$objPHPExcel->getActiveSheet()->mergeCells('A1:B1');
			if($_GET['type'] != 2){
			$objPHPExcel->getActiveSheet()->mergeCells('A'.(count($activation_result)+$pos_tec_confirm).':B'.(count($activation_result)+$pos_tec_confirm+1).'');
			$objPHPExcel->getActiveSheet()->mergeCells('A'.(count($activation_result)+$pos_dep_confirm).':B'.(count($activation_result)+$pos_dep_confirm+1).'');
			}
			//设置宽度
			$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
			$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
			$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
			$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
			//设置居中
			$objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$objPHPExcel->getActiveSheet()->getStyle('A2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$objPHPExcel->getActiveSheet()->getStyle('B2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$objPHPExcel->getActiveSheet()->getStyle('A3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$objPHPExcel->getActiveSheet()->getStyle('B3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$objPHPExcel->getActiveSheet()->getStyle('A4')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$objPHPExcel->getActiveSheet()->getStyle('B4')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			for($i=0;$i<count($activation_result);$i++){
				$objPHPExcel->getActiveSheet()->getStyle('A'.($i+5))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$objPHPExcel->getActiveSheet()->getStyle('B'.($i+5))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			}
			$objPHPExcel->getActiveSheet()->getStyle('A'.(count($activation_result)+5))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$objPHPExcel->getActiveSheet()->getStyle('B'.(count($activation_result)+5))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$objPHPExcel->getActiveSheet()->getStyle('A'.(count($activation_result)+6))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$objPHPExcel->getActiveSheet()->getStyle('B'.(count($activation_result)+6))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            if ($account_attr == 2) {
                $objPHPExcel->getActiveSheet()->getStyle('A'.(count($activation_result)+$pos_taxt))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $objPHPExcel->getActiveSheet()->getStyle('B'.(count($activation_result)+$pos_taxt))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $objPHPExcel->getActiveSheet()->getStyle('A'.(count($activation_result)+$pos_taxt_account))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $objPHPExcel->getActiveSheet()->getStyle('B'.(count($activation_result)+$pos_taxt_account))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            }
            $objPHPExcel->getActiveSheet()->getStyle('A'.(count($activation_result)+$pos_amount_account))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$objPHPExcel->getActiveSheet()->getStyle('B'.(count($activation_result)+$pos_amount_account))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	
			
			//设置边框
			$objPHPExcel->getActiveSheet()->getStyle('A1')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A1')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A1')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A1')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B1')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B1')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B1')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A2')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A2')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A2')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A2')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B2')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B2')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B2')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A3')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A3')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A3')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A3')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B3')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B3')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B3')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A4')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A4')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A4')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A4')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B4')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B4')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B4')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			for($i=0;$i<count($activation_result);$i++){
				$objPHPExcel->getActiveSheet()->getStyle('A'.($i+5))->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('A'.($i+5))->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('A'.($i+5))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('A'.($i+5))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('B'.($i+5))->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('B'.($i+5))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('B'.($i+5))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			}
			$objPHPExcel->getActiveSheet()->getStyle('A'.(count($activation_result)+5))->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A'.(count($activation_result)+5))->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A'.(count($activation_result)+5))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A'.(count($activation_result)+5))->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A'.(count($activation_result)+5))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B'.(count($activation_result)+5))->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B'.(count($activation_result)+5))->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B'.(count($activation_result)+5))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B'.(count($activation_result)+5))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			
			$objPHPExcel->getActiveSheet()->getStyle('A'.(count($activation_result)+6))->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A'.(count($activation_result)+6))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A'.(count($activation_result)+6))->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A'.(count($activation_result)+6))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B'.(count($activation_result)+6))->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B'.(count($activation_result)+6))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B'.(count($activation_result)+6))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			if ($account_attr == 2) {
                $objPHPExcel->getActiveSheet()->getStyle('A'.(count($activation_result)+$pos_taxt))->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                $objPHPExcel->getActiveSheet()->getStyle('A'.(count($activation_result)+$pos_taxt))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                $objPHPExcel->getActiveSheet()->getStyle('A'.(count($activation_result)+$pos_taxt))->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                $objPHPExcel->getActiveSheet()->getStyle('A'.(count($activation_result)+$pos_taxt))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                $objPHPExcel->getActiveSheet()->getStyle('B'.(count($activation_result)+$pos_taxt))->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                $objPHPExcel->getActiveSheet()->getStyle('B'.(count($activation_result)+$pos_taxt))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                $objPHPExcel->getActiveSheet()->getStyle('B'.(count($activation_result)+$pos_taxt))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
               // if($_GET['type'] != 2){
					$objPHPExcel->getActiveSheet()->getStyle('A'.(count($activation_result)+$pos_taxt_account))->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
					$objPHPExcel->getActiveSheet()->getStyle('A'.(count($activation_result)+$pos_taxt_account))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
					$objPHPExcel->getActiveSheet()->getStyle('A'.(count($activation_result)+$pos_taxt_account))->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
					$objPHPExcel->getActiveSheet()->getStyle('A'.(count($activation_result)+$pos_taxt_account))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
					$objPHPExcel->getActiveSheet()->getStyle('B'.(count($activation_result)+$pos_taxt_account))->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
					$objPHPExcel->getActiveSheet()->getStyle('B'.(count($activation_result)+$pos_taxt_account))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
					$objPHPExcel->getActiveSheet()->getStyle('B'.(count($activation_result)+$pos_taxt_account))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				//}
			}
			
			
			$objPHPExcel->getActiveSheet()->getStyle('A'.(count($activation_result)+$pos_amount_account))->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A'.(count($activation_result)+$pos_amount_account))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A'.(count($activation_result)+$pos_amount_account))->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A'.(count($activation_result)+$pos_amount_account))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B'.(count($activation_result)+$pos_amount_account))->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B'.(count($activation_result)+$pos_amount_account))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B'.(count($activation_result)+$pos_amount_account))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			if($_GET['type'] != 2){
			$objPHPExcel->getActiveSheet()->getStyle('A'.(count($activation_result)+$pos_tec_confirm))->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A'.(count($activation_result)+$pos_tec_confirm))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A'.(count($activation_result)+$pos_tec_confirm))->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A'.(count($activation_result)+$pos_tec_confirm))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B'.(count($activation_result)+$pos_tec_confirm))->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B'.(count($activation_result)+$pos_tec_confirm))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B'.(count($activation_result)+$pos_tec_confirm))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			
			$objPHPExcel->getActiveSheet()->getStyle('A'.(count($activation_result)+$pos_tec_confirm+1))->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A'.(count($activation_result)+$pos_tec_confirm+1))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A'.(count($activation_result)+$pos_tec_confirm+1))->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A'.(count($activation_result)+$pos_tec_confirm+1))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B'.(count($activation_result)+$pos_tec_confirm+1))->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B'.(count($activation_result)+$pos_tec_confirm+1))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B'.(count($activation_result)+$pos_tec_confirm+1))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
            
            $objPHPExcel->getActiveSheet()->getStyle('A'.(count($activation_result)+$pos_dep_confirm))->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A'.(count($activation_result)+$pos_dep_confirm))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A'.(count($activation_result)+$pos_dep_confirm))->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A'.(count($activation_result)+$pos_dep_confirm))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B'.(count($activation_result)+$pos_dep_confirm))->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B'.(count($activation_result)+$pos_dep_confirm))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B'.(count($activation_result)+$pos_dep_confirm))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
            
            $objPHPExcel->getActiveSheet()->getStyle('A'.(count($activation_result)+$pos_dep_confirm+1))->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A'.(count($activation_result)+$pos_dep_confirm+1))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A'.(count($activation_result)+$pos_dep_confirm+1))->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A'.(count($activation_result)+$pos_dep_confirm+1))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B'.(count($activation_result)+$pos_dep_confirm+1))->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B'.(count($activation_result)+$pos_dep_confirm+1))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B'.(count($activation_result)+$pos_dep_confirm+1))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
            }
			//设置颜色
			$objPHPExcel->getActiveSheet()->getStyle('A2')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
			$objPHPExcel->getActiveSheet()->getStyle('A2')->getFill()->getStartColor()->setARGB('#EE9A00');
			$objPHPExcel->getActiveSheet()->getStyle('B2')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
			$objPHPExcel->getActiveSheet()->getStyle('B2')->getFill()->getStartColor()->setARGB('#EE9A00');
			$objPHPExcel->getActiveSheet()->getStyle('A3')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
			$objPHPExcel->getActiveSheet()->getStyle('A3')->getFill()->getStartColor()->setARGB('#EE9A00');
			$objPHPExcel->getActiveSheet()->getStyle('B3')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
			$objPHPExcel->getActiveSheet()->getStyle('B3')->getFill()->getStartColor()->setARGB('#EE9A00');
			$objPHPExcel->getActiveSheet()->getStyle('A4')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
			$objPHPExcel->getActiveSheet()->getStyle('A4')->getFill()->getStartColor()->setARGB('#EE9A00');
			$objPHPExcel->getActiveSheet()->getStyle('B4')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
			$objPHPExcel->getActiveSheet()->getStyle('B4')->getFill()->getStartColor()->setARGB('#EE9A00');
            $objPHPExcel->getActiveSheet()->getStyle('A'.(count($activation_result)+$pos_amount_account))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
			$objPHPExcel->getActiveSheet()->getStyle('A'.(count($activation_result)+$pos_amount_account))->getFill()->getStartColor()->setARGB('#EEEE00');
			$objPHPExcel->getActiveSheet()->getStyle('B'.(count($activation_result)+$pos_amount_account))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
			$objPHPExcel->getActiveSheet()->getStyle('B'.(count($activation_result)+$pos_amount_account))->getFill()->getStartColor()->setARGB('#EEEE00');
			
			$cid_result = $model -> table('sj_channel') -> where(array('cid' => $cid)) -> select();
		
			if($_GET['type'] != 2){
				$excel_title = $cid_result[0]['chname'].'_'.$month.'_(内部)对账单';
			}else{
				$excel_title = $cid_result[0]['chname'].'_'.$month.'_对账单';
			}
			header ( 'Content-Type: application/vnd.ms-excel' );
			$ua = $_SERVER["HTTP_USER_AGENT"];
			if (preg_match("/MSIE/", $ua)) {  
				header('Content-Disposition: attachment; filename="' . urlencode($excel_title) . '.xls"');
			} else if (preg_match("/Firefox/", $ua)) {  
				header('Content-Disposition: attachment; filename="' . $excel_title . '.xls"');
			} else {  
				header('Content-Disposition: attachment; filename="'.$excel_title.'.xls"');
			}	
			header('Cache-Control: max-age=0');
			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
			$objWriter->save('php://output');
		}
		$this -> display();
	}
	
	function change_status(){
		$client_model = D('Channel_cooperation.channel_cooperation_p');
		$chname = $_GET['chname'];
		$client_name = $_GET['client_name'];
		$co_group = $_GET['co_group'];
		$billing = $_GET['billing'];
		$p = $_GET['p'];
		$lr = $_GET['lr'];
		$start_tm = $_GET['start_tm'];
		$end_tm = $_GET['end_tm'];
		$cid = $_GET['cid'];
		$month = $_GET['month'];
		if($chname){
			$where_go .= "/chname/{$chname}";
		}
		if($client_name){
			$where_go .= "/client_name/{$client_name}";
		}
		if(isset($co_group)){
			$where_go .= "/co_group/{$co_group}";
		}
		if($billing){
			$where_go .= "/billing/{$billing}";
		}
		if($p){
			$where_go .= "/p/{$p}";
		}
		if($lr){
			$where_go .= "/lr/{$lr}";
		}
		if($start_tm){
			$where_go .= "/start_tm/{$start_tm}";
		}
		if($end_tm){
			$where_go .= "/end_tm/{$end_tm}";
		}
		$client_id = $_GET['client_id'];
		$status = $_GET['status'];
		$my_cid = $_GET['my_cid'];
		
		$cid_str_go = substr($my_cid,0,-1);
		$cid_str_arr = explode(',',$cid_str_go);
		foreach($cid_str_arr as $key => $val){
			$cid_arr = explode('_',$val);
			if($cid_arr[1]){
				$cid_arr_go []= $cid_arr[1];
			}
		}

		foreach($cid_arr_go as $key => $val){
			$where['_string'] = "client_id = {$client_id} and month = {$month} and cid = {$val}";
			$data = array(
				'status' => 3,
				'update_tm' => time()
			);
			$log_result = $this -> logcheck(array('client_id' => $client_id,'month' => $month,'cid' => $val),'co_channel_check',$data,$client_model);
		
			$result = $client_model -> table('co_channel_check') -> where($where) -> save($data);
		
			if($result){
				$this -> writelog("已编辑客户id为{$client_id}，月份为{$month}运营审核".$log_result, 'co_channel_check', $client_id,__ACTION__ ,'','edit');
			}
			$results[] = $result;
		}
		if($results){
			//$this -> writelog("已编辑客户id为{$client_id}，月份为{$month}运营审核".$log_result);
			$this -> assign('jumpUrl','/index.php/Channel_cooperation/Channelsettle_p/business_list'.$where_go);
			$this -> success("编辑成功");
		}else{
			$this -> error("编辑失败");
		}
	
	}
	
	// 运营审核列表操作对账单按钮（按渠道）
	function client_settle(){
		$co_group_arr =  C('co_group');
		$client_model = D('Channel_cooperation.channel_cooperation_p');
		$coefficient_model = D('Channel_cooperation.channel_coefficient_p');
		include (dirname(__FILE__).'/../../ORG/PHPExcel/PHPExcel.php');
		$objPHPExcel = new PHPExcel();
		//设置属性
		$objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
		->setLastModifiedBy("Maarten Balliauw")
		->setTitle("Office 2007 XLSX Test Document")
		->setSubject("Office 2007 XLSX Test Document")
		->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
		->setKeywords("office 2007 openxml php")
		->setCategory("Test result file");
		
		$model = new Model();
		$client_id = $_GET['client_id'];
		$month = $_GET['month'];
		$my_cid = $_GET['my_cid'];
		$cid_str_go = substr($my_cid,0,-1); //客户id_cid_month,客户id_cid_month,客户id_cid_month
		$cid_arr_go = explode(',',$cid_str_go);
		foreach($cid_arr_go as $key => $val){
			$my_cid_arr_go = explode('_',$val);
			if($my_cid_arr_go[1]){
				$my_cid_arr .= $my_cid_arr_go[1].',';
			}
		}
		$cid_str = substr($my_cid_arr,0,-1); //cid,cid,cid
		$start_tm = strtotime($month.'01');
		$end_tm = strtotime(date('Ymt',strtotime($month.'01')));
		$my_month = date('Y年m月',$start_tm);
		$client_channel_where['_string'] = "account_attr<>4 and client_id = {$client_id} and month = {$month} and cid in ({$cid_str})"; 
		$client_channel_result = $client_model -> table('co_channel_check') -> where($client_channel_where) ->order('amount_pay desc')-> select();
		$client_name_result = $client_model -> table('co_client_list') -> where(array('id' => $client_id)) -> select(); //关联客户名称
		//是否显示税率
		$show_taxt = false;
		$show_billing = array(); //结算方式：预装，激活
		foreach($client_channel_result as $key => $val){
			$channel_result = $model -> table('sj_channel') -> where(array('cid' => $val['cid'])) -> select();
			$price_result = $client_model -> table('co_channel_check') -> where(array('cid' => $val['cid'],'account_attr'=>array('neq', '4'),'month' => $month))-> select();
			$activation_where['_string'] = "cid = {$val['cid']} and submit_tm >= {$start_tm} and submit_tm <= {$end_tm} and status = 1";
			$activation_result = $coefficient_model -> table('activation_coefficient_state') -> where($activation_where) -> select();
			
			$activation_count = $val['activation']; //激活量
			$val['activation_count'] = $activation_count; //激活量
			$val['activation_count'] = number_format($val['activation_count']); //激活量
			
			$amount_account = sprintf('%.2f',$activation_count*$price_result[0]['price']); //激活量*单价
			$val['amount_account'] = $amount_account; //激活量*单价
			$val['amount_account'] = number_format($val['amount_account'],2,'.',','); //激活量*单价

			$val['chname'] = $channel_result[0]['chname']; //渠道名称
			$show_billing[] = $channel_result[0]['billing']; //结算方式
			$val['price'] = $price_result[0]['price']; //单价
			
			$pay_amount = $amount_account + $price_result[0]['salvation']; //激活量*单价+补差
			$val['no_amount_account'] = $pay_amount; //no_激活量*单价
			$val['pay_amount'] = number_format($pay_amount,2,'.',','); //激活量*单价+补差
			
			$val['co_group'] = $co_group_arr[$channel_result[0]['co_group']]; //性质:换量、激活、预装等
			if($val['taxt']>0){
				$show_taxt = true;
			}
			$my_channel_result[] = $val;
			$all_activation_arr[] = $activation_count;
			$all_account_arr[]= $pay_amount;
			$all_pay_account_arr[]= $pay_amount;
		}
		//结算方式如果有不一样的则显示空，没有则直接显示
		$show_billing=array_unique($show_billing);
		if(count($show_billing)>1){
			$show_bill = '';
		}else{
			$bill_arr = array('1'=>'激活','2'=>'预装');
			$show_bill = $bill_arr[$show_billing[0]];
		}
		$all_activation = array_sum($all_activation_arr);
		$all_activation = number_format($all_activation); //总计激活量
		$all_account = sprintf('%.2f',array_sum($all_account_arr));
        $no_all_account = $all_account;
		$all_account = number_format($all_account,2,'.',','); //总计激活量*单价
		$all_pay_account =sprintf('%.2f',array_sum($all_pay_account_arr));
		$all_pay_account = number_format($all_pay_account,2,'.',','); //总计激活量*单价+补差
		$this -> assign('show_taxt',$show_taxt);
		$this -> assign('show_bill',$show_bill);
		$this -> assign('my_channel_result',$my_channel_result);
		$this -> assign('all_activation',$all_activation); //总计激活量（总计合计激活）
		$this -> assign('all_pay_account',$all_pay_account); //总计激活量*单价+补差（总计金额）
		$this -> assign('all_account',$all_account); //总计激活量*单价
		$this -> assign('no_all_account',$no_all_account); //总计激活量*单价（总计扣税点）
		$this -> assign('my_month',$my_month);
		$this -> assign('client_name',$client_name_result[0]['client_name']);
		$this -> assign('client_id',$client_id);
		$this -> assign('month',$month);
		$this -> assign('my_cid',$my_cid);
		
		
		if($_GET['from'] == 1){
			$sheet1 = $objPHPExcel->createSheet();
			$objPHPExcel->setActiveSheetIndex(1)
			->setCellValue('A1', '安智市场对账单')
			->setCellValue('A2', '客户名称')
			->setCellValue('B2', "{$client_name_result[0]['client_name']}")
			->setCellValue('A3', "月份")
			->setCellValue('B3', "{$my_month}")
			->setCellValue('A4', "渠道名称");
			if($_GET['type'] != 2){
				$objPHPExcel->setActiveSheetIndex(1)
				->setCellValue('B4', "渠道分类")
				->setCellValue('C4', "单价(元)")
				->setCellValue('D4', "合计激活")
				->setCellValue('E4', "金额(元)");
				$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
			}else{
				$objPHPExcel->setActiveSheetIndex(1)
				->setCellValue('B4', "单价(元)")
				->setCellValue('C4', "合计激活")
				->setCellValue('D4', "金额(元)");
			}
			if($show_taxt&&$_GET['type'] != 2){
				$objPHPExcel->setActiveSheetIndex(1)
				->setCellValue('F4', "扣税点{$my_channel_result[0]['taxt']}%");
				$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
			}
			if($_GET['type'] != 2){
				if($show_taxt){
					$objPHPExcel->setActiveSheetIndex(1)
								->setCellValue('G2', "结算方式")
								->setCellValue('I2', $show_bill);
				}else{
					$objPHPExcel->setActiveSheetIndex(1)
								->setCellValue('F2', "结算方式")
								->setCellValue('H2', $show_bill);
				}
			}
			
			//填充渠道名称--扣税点
			for($i=0;$i<count($my_channel_result);$i++){
				$objPHPExcel->setActiveSheetIndex(1)
				->setCellValue('A'.($i+5).'',"{$my_channel_result[$i]['chname']}");
				if($_GET['type'] != 2){
					$objPHPExcel->setActiveSheetIndex(1)
					->setCellValue('B'.($i+5).'',"{$my_channel_result[$i]['co_group']}")
					->setCellValue('C'.($i+5).'',"{$my_channel_result[$i]['price']}")
					->setCellValue('D'.($i+5).'',"{$my_channel_result[$i]['activation_count']}")
					->setCellValue('E'.($i+5).'',"￥{$my_channel_result[$i]['pay_amount']}");
					$objPHPExcel->getActiveSheet()->getStyle('E'.($i+5))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				}else{
					$objPHPExcel->setActiveSheetIndex(1)
					->setCellValue('B'.($i+5).'',"{$my_channel_result[$i]['price']}")
					->setCellValue('C'.($i+5).'',"{$my_channel_result[$i]['activation_count']}")
					->setCellValue('D'.($i+5).'',"￥{$my_channel_result[$i]['pay_amount']}");
				}
				
				if($_GET['type'] != 2&&$show_taxt){
					$kou_money = round($my_channel_result[$i]['no_amount_account']*(1-$my_channel_result[$i]['taxt']/100),2);
					$objPHPExcel->setActiveSheetIndex(1)
								->setCellValue('F'.($i+5).'',"￥".$kou_money);
				}
			}
			
			$objPHPExcel->setActiveSheetIndex(1)
			->setCellValue('A'.(count($my_channel_result) + 5).'', "合计");
			//填充合计激活和金额
			if($_GET['type'] != 2){
				$objPHPExcel->setActiveSheetIndex(1)
				->setCellValue('D'.(count($my_channel_result) + 5).'', "{$all_activation}")
				->setCellValue('E'.(count($my_channel_result) + 5).'', "￥{$all_pay_account}");
			}else{
				$objPHPExcel->setActiveSheetIndex(1)
				->setCellValue('C'.(count($my_channel_result) + 5).'', "{$all_activation}")
				->setCellValue('D'.(count($my_channel_result) + 5).'', "￥{$all_pay_account}");
			}
			
			
			
			if($_GET['type'] != 2){
				$objPHPExcel->setActiveSheetIndex(1)
				->setCellValue('A'.(count($my_channel_result) + 6).'', "技术确认(激活数)：\r\n日期")
				->setCellValue('D'.(count($my_channel_result) + 6).'', "部门确认：\r\n日期");
				if($show_taxt){
					$all_kou_money = round($no_all_account*(1-$my_channel_result[0]['taxt']/100),2);
					$objPHPExcel->setActiveSheetIndex(1)
								->setCellValue('F'.(count($my_channel_result) + 5).'', "￥{$all_kou_money}");
					$objPHPExcel->getActiveSheet()->getStyle('F'.(count($my_channel_result)+5))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);			
					$objPHPExcel->getActiveSheet()->getStyle('F'.(count($my_channel_result) + 5).'')->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
				}
			}
			
			$objPHPExcel->getActiveSheet()->getStyle('A'.(count($my_channel_result) + 6).'')->getAlignment()->setWrapText(true);
			$objPHPExcel->getActiveSheet()->getStyle('D'.(count($my_channel_result) + 6).'')->getAlignment()->setWrapText(true);
			$objPHPExcel->getActiveSheet()->setTitle($this->convertUTF8("运营审核客户{$client_name_result[0]['client_name']}对账单"));
			
			
			//合并单元格
			//
			// 2014.10.9 jiwei
			// 根据参数来处理单元格合并，因为增加了两列
			//
			if($_GET['type'] != 2){
				if($show_taxt){
					$objPHPExcel->getActiveSheet()->mergeCells('A1:I1');
					$objPHPExcel->getActiveSheet()->mergeCells('B2:F2');
					$objPHPExcel->getActiveSheet()->mergeCells('G2:H2');
					$objPHPExcel->getActiveSheet()->mergeCells('B3:I3');
					
				}else{
					$objPHPExcel->getActiveSheet()->mergeCells('A1:H1');
					$objPHPExcel->getActiveSheet()->mergeCells('B2:E2');
					$objPHPExcel->getActiveSheet()->mergeCells('F2:G2');
					$objPHPExcel->getActiveSheet()->mergeCells('B3:H3');
				}

			}else{
				$objPHPExcel->getActiveSheet()->mergeCells('A1:D1');
				$objPHPExcel->getActiveSheet()->mergeCells('B2:D2');
				$objPHPExcel->getActiveSheet()->mergeCells('B3:D3');
			}
			//合计合并单元格
			if($_GET['type'] != 2){
				$objPHPExcel->getActiveSheet()->mergeCells("A".(count($my_channel_result)+5).":C".(count($my_channel_result)+5)."");
			}else{
				$objPHPExcel->getActiveSheet()->mergeCells("A".(count($my_channel_result)+5).":B".(count($my_channel_result)+5)."");
			}
			
			
			//设置宽度
			$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
			$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(10);
			$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(10);
			$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(10);
			$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15.71);
			if($show_taxt){
				$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15.71);
				$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(9);
				$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
				$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
				$objPHPExcel->getActiveSheet()->getStyle('I4')->getAlignment()->setWrapText(true);
			}else{
				$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(9);
				$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
				$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
				$objPHPExcel->getActiveSheet()->getStyle('H4')->getAlignment()->setWrapText(true);
			}
			
			//设置居中
			for($i='A';$i<='J';$i++){
				$objPHPExcel->getActiveSheet()->getStyle($i.'4')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
			}
			$objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$objPHPExcel->getActiveSheet()->getStyle('A'.(count($my_channel_result)+5))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$objPHPExcel->getActiveSheet()->getStyle('A2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$objPHPExcel->getActiveSheet()->getStyle('B2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$objPHPExcel->getActiveSheet()->getStyle('A3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$objPHPExcel->getActiveSheet()->getStyle('B3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$objPHPExcel->getActiveSheet()->getStyle('A4')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$objPHPExcel->getActiveSheet()->getStyle('B4')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$objPHPExcel->getActiveSheet()->getStyle('C4')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$objPHPExcel->getActiveSheet()->getStyle('D4')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$col_index = 'F';
			if($show_taxt){
				$col_max = 'I';
			}else{
				$col_max = 'H';
			}
			for($i=0;$i<count($my_channel_result);$i++){
				$objPHPExcel->getActiveSheet()->getStyle('A'.($i+5))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$objPHPExcel->getActiveSheet()->getStyle('B'.($i+5))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$objPHPExcel->getActiveSheet()->getStyle('C'.($i+5))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$objPHPExcel->getActiveSheet()->getStyle('D'.($i+5))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

				for($j=$col_index;$j<$col_max;$j++){
					$objPHPExcel->getActiveSheet()->getStyle($j.($i+5))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);	
				}
			}
			$objPHPExcel->getActiveSheet()->getStyle('A'.(count($my_channel_result)+5))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$objPHPExcel->getActiveSheet()->getStyle('C'.(count($my_channel_result)+5))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$objPHPExcel->getActiveSheet()->getStyle('D'.(count($my_channel_result)+5))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$objPHPExcel->getActiveSheet()->getStyle('A1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
			$objPHPExcel->getActiveSheet()->getStyle('A1')->getFill()->getStartColor()->setARGB('#458B00');
			if($_GET['type'] != 2){
				$objPHPExcel->getActiveSheet()->getStyle('E'.(count($my_channel_result) + 5).'')->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
				$objPHPExcel->getActiveSheet()->getStyle('E'.(count($my_channel_result) + 5))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			}else{
				$objPHPExcel->getActiveSheet()->getStyle('D'.(count($my_channel_result) + 5).'')->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
			}
			
			
			//设置边框
			$objPHPExcel->getActiveSheet()->getStyle('A1')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A1')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A1')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B1')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B1')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('C1')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('C1')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('D1')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('D1')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('D1')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('E1')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A2')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A2')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A2')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B2')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('C2')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('D2')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('D2')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

			$objPHPExcel->getActiveSheet()->getStyle('A3')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A3')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A3')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B3')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('C3')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('D3')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('D3')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

			$objPHPExcel->getActiveSheet()->getStyle('A4')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A4')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A4')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B4')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B4')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('C4')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('C4')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('D4')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('D4')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			
			if($_GET['type'] != 2){
				$objPHPExcel->getActiveSheet()->getStyle('E1')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('E2')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('E2')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('E3')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('E4')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('E4')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			}
			for($i=0;$i<count($my_channel_result);$i++){
				$objPHPExcel->getActiveSheet()->getStyle('A'.($i+5))->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('A'.($i+5))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('A'.($i+5))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('B'.($i+5))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('B'.($i+5))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('C'.($i+5))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('C'.($i+5))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('D'.($i+5))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('D'.($i+5))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				if($_GET['type'] != 2){
					$objPHPExcel->getActiveSheet()->getStyle('E'.($i+5))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
					$objPHPExcel->getActiveSheet()->getStyle('E'.($i+5))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				}
				
			}
			
			$objPHPExcel->getActiveSheet()->getStyle('A'.(count($my_channel_result)+5))->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A'.(count($my_channel_result)+5))->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A'.(count($my_channel_result)+5))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A'.(count($my_channel_result)+5))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B'.(count($my_channel_result)+5))->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B'.(count($my_channel_result)+5))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B'.(count($my_channel_result)+5))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('C'.(count($my_channel_result)+5))->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('C'.(count($my_channel_result)+5))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('C'.(count($my_channel_result)+5))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('D'.(count($my_channel_result)+5))->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('D'.(count($my_channel_result)+5))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('D'.(count($my_channel_result)+5))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			
			if($_GET['type'] != 2){
				$objPHPExcel->getActiveSheet()->getStyle('A'.(count($my_channel_result)+6))->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('A'.(count($my_channel_result)+6))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('A'.(count($my_channel_result)+6))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('B'.(count($my_channel_result)+6))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('B'.(count($my_channel_result)+6))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('C'.(count($my_channel_result)+6))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('C'.(count($my_channel_result)+6))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('D'.(count($my_channel_result)+6))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('D'.(count($my_channel_result)+6))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('E'.(count($my_channel_result)+6))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('E'.(count($my_channel_result)+6))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				
				$objPHPExcel->getActiveSheet()->getStyle('A'.(count($my_channel_result)+7))->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('A'.(count($my_channel_result)+7))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('A'.(count($my_channel_result)+7))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('B'.(count($my_channel_result)+7))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('B'.(count($my_channel_result)+7))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('C'.(count($my_channel_result)+7))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('C'.(count($my_channel_result)+7))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('D'.(count($my_channel_result)+7))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('D'.(count($my_channel_result)+7))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('E'.(count($my_channel_result)+7))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('E'.(count($my_channel_result)+7))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			}
			
			//
			// 2014.10.9 jiwei
			// 对“临时需求_4~8月份_第二期”的“#8” 需求进行处理
			// 增加付费率、备注列，部门主管确认和运营总监确认单元格
			//
			if($_GET['type'] != 2){
				$_n = count($my_channel_result);
				
				//设置表格内容
				if($show_taxt){
					$objPHPExcel->setActiveSheetIndex(1)
					->setCellValue('G4', "ROI")
					->setCellValue('H4', "建议结算金额")
					->setCellValue('I4', "建议扣减金额");
				}else{
					$objPHPExcel->setActiveSheetIndex(1)
					->setCellValue('F4', "ROI")
					->setCellValue('G4', "建议结算金额")
					->setCellValue('H4', "建议扣减金额");
				}
				$objPHPExcel->setActiveSheetIndex(1)
					->setCellValue('A'.($_n+8).'', "部门主管确认：\r\n日期")
					->setCellValue('D'.($_n+8).'', "运营总监确认：\r\n日期");

				
				//合并单元格
				$objPHPExcel->getActiveSheet()->mergeCells("A".(count($my_channel_result)+6).":C".(count($my_channel_result)+7)."");
				$objPHPExcel->getActiveSheet()->mergeCells("A".(count($my_channel_result)+8).":C".(count($my_channel_result)+9)."");

				if($show_taxt){
					$objPHPExcel->getActiveSheet()->mergeCells("D".(count($my_channel_result)+6).":I".(count($my_channel_result)+7)."");
					$objPHPExcel->getActiveSheet()->mergeCells("D".(count($my_channel_result)+8).":I".(count($my_channel_result)+9)."");
				}else{
					$objPHPExcel->getActiveSheet()->mergeCells("D".(count($my_channel_result)+6).":H".(count($my_channel_result)+7)."");
					$objPHPExcel->getActiveSheet()->mergeCells("D".(count($my_channel_result)+8).":H".(count($my_channel_result)+9)."");
				}
				
				
				
				//设置对齐方式
				$objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$objPHPExcel->getActiveSheet()->getStyle('E4')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$objPHPExcel->getActiveSheet()->getStyle('F4')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$objPHPExcel->getActiveSheet()->getStyle('A'.(count($my_channel_result) + 8).'')->getAlignment()->setWrapText(true);
				$objPHPExcel->getActiveSheet()->getStyle('D'.(count($my_channel_result) + 8).'')->getAlignment()->setWrapText(true);
				
				for($i=$col_index;$i<=$col_max;$i++){
					for($j=1;$j<=4;$j++){
						//设置对齐方式
						$objPHPExcel->getActiveSheet()->getStyle($i.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
						//设置边框
						$objPHPExcel->getActiveSheet()->getStyle($i.$j)->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
						$objPHPExcel->getActiveSheet()->getStyle($i.$j)->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
						$objPHPExcel->getActiveSheet()->getStyle($i.$j)->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
					}
					
				}

				for($i=0; $i<$_n; $i++){
					for($j=$col_index;$j<=$col_max;$j++){
						$objPHPExcel->getActiveSheet()->getStyle($j.($i+5))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
						$objPHPExcel->getActiveSheet()->getStyle($j.($i+5))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
					}
				
				}
				
				$row = 5;
				for($j=$col_index;$j<=$col_max;$j++){
					for($i=$row;$i<=9;$i++){
						$objPHPExcel->getActiveSheet()->getStyle($j.($_n+$i))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
						$objPHPExcel->getActiveSheet()->getStyle($j.($_n+$i))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
					}
				}
				$objPHPExcel->getActiveSheet()->getStyle('E'.($_n+5))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('E'.($_n+5))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

				$objPHPExcel->getActiveSheet()->getStyle('A'.($_n+8))->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('A'.($_n+8))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('A'.($_n+8))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('B'.($_n+8))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('B'.($_n+8))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('C'.($_n+8))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('C'.($_n+8))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('D'.($_n+8))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('D'.($_n+8))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('E'.($_n+8))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('E'.($_n+8))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				
				$objPHPExcel->getActiveSheet()->getStyle('A'.($_n+9))->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('A'.($_n+9))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('A'.($_n+9))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('B'.($_n+9))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('B'.($_n+9))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('C'.($_n+9))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('C'.($_n+9))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('D'.($_n+9))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('D'.($_n+9))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('E'.($_n+9))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('E'.($_n+9))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			}
			// END（2014.10.9 jiwei）
			
			
			if(count($cid_arr_go) > 1){
				$my_cid_arrs = explode('_',$cid_arr_go[0]);
				$client_result = $client_model -> table('co_client_list') -> where(array('id' => $my_cid_arrs[0])) -> select();
				if($_GET['type'] != 2){
					$excel_title = $client_result[0]['client_name'].'系列_'.$my_cid_arrs[2].'_(内部)对账单';
				}else{
					$excel_title = $client_result[0]['client_name'].'系列_'.$my_cid_arrs[2].'_对账单';
				}
			}else{
				$my_cid_arrs = explode('_',$cid_arr_go[0]);
				$cid_result = $model -> table('sj_channel') -> where(array('cid' => $my_cid_arrs[1])) -> select();
				if($_GET['type'] != 2){
					$excel_title = $cid_result[0]['chname'].'_'.$my_cid_arrs[2].'_(内部)对账单';
				}else{
					$excel_title = $cid_result[0]['chname'].'_'.$my_cid_arrs[2].'_对账单';
				}
			}

			header ( 'Content-Type: application/vnd.ms-excel' );
			$ua = $_SERVER["HTTP_USER_AGENT"];
			if (preg_match("/MSIE/", $ua)) {  
				header('Content-Disposition: attachment; filename="' . urlencode($excel_title) . '.xls"');
			} else if (preg_match("/Firefox/", $ua)) {  
				header('Content-Disposition: attachment; filename="' . $excel_title . '.xls"');
			} else {  
				header('Content-Disposition: attachment; filename="'.$excel_title.'.xls"');
			}	
			header('Cache-Control: max-age=0');
			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
			$objWriter->save('php://output');
		
		}
		$this -> display();
	
	}
	
	// 运营审核列表操作对账单按钮（按客户）
	function client_settle2(){
		/*$co_group_arr =  C('co_group');*/
		/*$coefficient_model = D('Channel_cooperation.channel_coefficient_p');*/
		$client_model = D('Channel_cooperation.channel_cooperation_p');
		include (dirname(__FILE__).'/../../ORG/PHPExcel/PHPExcel.php');
		$objPHPExcel = new PHPExcel();
		//设置属性
		$objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
		->setLastModifiedBy("Maarten Balliauw")
		->setTitle("Office 2007 XLSX Test Document")
		->setSubject("Office 2007 XLSX Test Document")
		->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
		->setKeywords("office 2007 openxml php")
		->setCategory("Test result file");
		
		$model = new Model();
		$client_id = $_GET['client_id'];
		$month = $_GET['month'];
		$my_cid = $_GET['my_cid'];
		$cid_str_go = substr($my_cid,0,-1); //客户id_cid_month,客户id_cid_month,客户id_cid_month
		$cid_arr_go = explode(',',$cid_str_go);
		foreach($cid_arr_go as $key => $val){
			$my_cid_arr_go = explode('_',$val);
			if($my_cid_arr_go[1]){
				$my_cid_arr .= $my_cid_arr_go[1].',';
			}
		}
		$cid_str = substr($my_cid_arr,0,-1); //cid,cid,cid
		$zhouqi_start_tm = date('Y.m.d',strtotime($month.'01'));
		$zhouqi_end_tm = date('Y.m.t',strtotime($month.'01'));
		$zhouqi_tm = $zhouqi_start_tm.'-'.$zhouqi_end_tm;
		/*$start_tm = strtotime($month.'01');
		$end_tm = strtotime(date('Ymt',strtotime($month.'01')));
		$my_month = date('Y年m月',$start_tm);*/
		$client_channel_where['_string'] = "account_attr<>4 and client_id = {$client_id} and month = {$month} and cid in ({$cid_str})"; 
		$client_channel_result = $client_model -> table('co_channel_check') -> where($client_channel_where) ->order('amount_pay desc')-> select();
		$client_name_result = $client_model -> table('co_client_list') -> where(array('id' => $client_id)) -> select(); //用于关联客户名称
		$show_taxt = false; //是否显示税率
		/*$show_billing = array(); //结算方式：预装，激活*/
		foreach($client_channel_result as $key => $val){
			$channel_result = $model -> table('sj_channel') -> where(array('cid' => $val['cid'])) -> select();
			$price_result = $client_model -> table('co_channel_check') -> where(array('cid' => $val['cid'],'account_attr'=>array('neq', '4'),'month' => $month))-> select();
			/*$activation_where['_string'] = "cid = {$val['cid']} and submit_tm >= {$start_tm} and submit_tm <= {$end_tm} and status = 1";
			$activation_result = $coefficient_model -> table('activation_coefficient_state') -> where($activation_where) -> select();*/
			$val['chname'] = $channel_result[0]['chname']; //渠道名称
			$val['price'] = $price_result[0]['price']; //单价
			$val['price'] = number_format($val['price'],2,'.',',');
			$val['activation_sum'] = number_format($val['activation_sum']);
			$val['taxt'] = number_format($val['taxt'],2,'.',',');
			$val['cny_money'] = $this -> cny($val['amount_pay']);
			$val['amount_pay'] = number_format($val['amount_pay'],2,'.',',');
			if($val['taxt']>0){
				$show_taxt = true;
			}
			/*
			$activation_count = $val['activation']; //激活量
			$val['activation_count'] = $activation_count; //激活量
			$val['activation_count'] = number_format($val['activation_count']); //激活量
			$amount_account = sprintf('%.2f',$activation_count*$price_result[0]['price']); //激活量*单价
			$val['amount_account'] = $amount_account; //激活量*单价
			$val['no_amount_account'] = $val['amount_account']; //no_激活量*单价
			$val['amount_account'] = number_format($val['amount_account'],2,'.',','); //激活量*单价
			$pay_amount = $amount_account + $price_result[0]['salvation']; //激活量*单价+补差
			$val['pay_amount'] = number_format($pay_amount,2,'.',','); //激活量*单价+补差
			$show_billing[] = $channel_result[0]['billing']; //结算方式
			$val['co_group'] = $co_group_arr[$channel_result[0]['co_group']]; //性质:换量、激活、预装等
			$all_account_arr[]= $amount_account;
			$all_pay_account_arr[]= $pay_amount;
			$all_activation_arr[] = $activation_count;*/
			$my_channel_result[] = $val;
		}
		//结算方式如果有不一样的则显示空，没有则直接显示
		/*$show_billing=array_unique($show_billing);
		if(count($show_billing)>1){
			$show_bill = '';
		}else{
			$bill_arr = array('1'=>'激活','2'=>'预装');
			$show_bill = $bill_arr[$show_billing[0]];
		}
		$all_activation = array_sum($all_activation_arr);
		$all_activation = number_format($all_activation); //总计激活量
		$all_account = sprintf('%.2f',array_sum($all_account_arr));
        $no_all_account = $all_account;
		$all_account = number_format($all_account,2,'.',','); //总计激活量*单价
		$all_pay_account =sprintf('%.2f',array_sum($all_pay_account_arr));
		$all_pay_account = number_format($all_pay_account,2,'.',','); //总计激活量*单价+补差
		$this -> assign('show_bill',$show_bill);
		$this -> assign('my_month',$my_month);
		$this -> assign('all_pay_account',$all_pay_account); //总计激活量*单价+补差（总计金额）
		$this -> assign('all_account',$all_account); //总计激活量*单价
		$this -> assign('no_all_account',$no_all_account); //总计激活量*单价（总计扣税点）
		$this -> assign('all_activation',$all_activation); //总计激活量（总计合计激活）*/
		$this -> assign('my_channel_result',$my_channel_result);
		$this -> assign('show_taxt',$show_taxt);
		$this -> assign('client_name',$client_name_result[0]['client_name']);
		$this -> assign('client_id',$client_id);
		$this -> assign('zhouqi_tm',$zhouqi_tm);
		$this -> assign('month',$month);
		$this -> assign('my_cid',$my_cid);
		
        if($_GET['from'] == 1){
			$sheet1 = $objPHPExcel->createSheet();
			// 第一、二、三行
			$objPHPExcel->setActiveSheetIndex(1)
			->setCellValue('A1', '安智市场对账单')
			->setCellValue('A2', '客户名称')
			->setCellValue('B2', '渠道名称')
			->setCellValue('C2', '结算周期')
			->setCellValue('D2', '合计激活')
			->setCellValue('E2', '单价(元)')
			->setCellValue('A3', "{$client_name_result[0]['client_name']}")
			->setCellValue('B3', "{$my_channel_result[0]['chname']}")
			->setCellValue('C3', "{$zhouqi_tm}")
			->setCellValue('D3', "{$my_channel_result[0]['activation_sum']}")
			->setCellValue('E3', "￥{$my_channel_result[0]['price']}");
			if($show_taxt){
				$objPHPExcel->setActiveSheetIndex(1)
				->setCellValue('F2', "税点")
				->setCellValue('G2', "结算金额(元)")
				->setCellValue('F3', "{$my_channel_result[0]['taxt']}")
				->setCellValue('G3', "￥{$my_channel_result[0]['amount_pay']}");
			}else{
				$objPHPExcel->setActiveSheetIndex(1)
				->setCellValue('F2', "结算金额(元)")
				->setCellValue('F3', "￥{$my_channel_result[0]['amount_pay']}");
			}
			
			//第四行(如果有)
			for($i=1;$i<count($my_channel_result);$i++){
				$objPHPExcel->setActiveSheetIndex(1)
				->setCellValue('A'.($i+3).'',"{$client_name_result[0]['client_name']}")
				->setCellValue('B'.($i+3).'',"{$my_channel_result[$i]['chname']}");
			}
			
			//填充合计激活和金额
			$objPHPExcel->setActiveSheetIndex(1)
			->setCellValue('A'.(count($my_channel_result) + 3).'', "合计")
			->setCellValue('D'.(count($my_channel_result) + 3).'', "{$my_channel_result[0]['activation_sum']}");
			if ($show_taxt) {
				$objPHPExcel->setActiveSheetIndex(1)
			    ->setCellValue('G'.(count($my_channel_result) + 3).'', "￥{$my_channel_result[0]['amount_pay']}");
			} else {
				$objPHPExcel->setActiveSheetIndex(1)
			    ->setCellValue('F'.(count($my_channel_result) + 3).'', "￥{$my_channel_result[0]['amount_pay']}");
			}
			
			//最后一行
			$objPHPExcel->setActiveSheetIndex(1)
			->setCellValue('A'.(count($my_channel_result) + 5).'', "结算金额合计:")
			->setCellValue('B'.(count($my_channel_result) + 5).'',"￥{$my_channel_result[0]['amount_pay']}")
			->setCellValue('C'.(count($my_channel_result) + 5).'', "人民币(大写)：{$my_channel_result[0]['cny_money']}");
			
			$objPHPExcel->getActiveSheet()->setTitle($this->convertUTF8("运营审核客户{$client_name_result[0]['client_name']}对账单"));
			
			//合并单元格
			if($show_taxt){
				$objPHPExcel->getActiveSheet()->mergeCells('A1:G1');
				$objPHPExcel->getActiveSheet()->mergeCells("C".(count($my_channel_result)+5).":G".(count($my_channel_result)+5)."");
			}else{
				$objPHPExcel->getActiveSheet()->mergeCells('A1:F1');
				$objPHPExcel->getActiveSheet()->mergeCells("C".(count($my_channel_result)+5).":F".(count($my_channel_result)+5)."");
			}
			
			//设置宽度
			$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
			$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
			$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
			$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(10);
			$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(10);
			if($show_taxt){
				$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(10);
				$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
			}else{
				$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
			}
			
			//设置居中
			$objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$objPHPExcel->getActiveSheet()->getStyle('A'.(count($my_channel_result)+5))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$objPHPExcel->getActiveSheet()->getStyle('B'.(count($my_channel_result)+5))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			if($show_taxt){
				$col_max = 'G';
			}else{
				$col_max = 'F';
			}
			for($i='A';$i<=$col_max;$i++){
				for($j=2;$j<count($my_channel_result)+5;$j++){
					$objPHPExcel->getActiveSheet()->getStyle($i.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
					$objPHPExcel->getActiveSheet()->getStyle($i.$j)->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
					$objPHPExcel->getActiveSheet()->getStyle($i.$j)->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				}
			}
		
			$objPHPExcel->getActiveSheet()->getStyle('A1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
			$objPHPExcel->getActiveSheet()->getStyle('A1')->getFill()->getStartColor()->setARGB('#458B00');
			
			//设置边框
			for($i='A';$i<=$col_max;$i++){
				for($j=1;$j<count($my_channel_result)+6;$j++){
					$objPHPExcel->getActiveSheet()->getStyle($i.$j)->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
					$objPHPExcel->getActiveSheet()->getStyle($i.$j)->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				}
			}
				
			if(count($cid_arr_go) > 1){
				$my_cid_arrs = explode('_',$cid_arr_go[0]);
				$client_result = $client_model -> table('co_client_list') -> where(array('id' => $my_cid_arrs[0])) -> select();
				if($_GET['type'] != 2){
					$excel_title = $client_result[0]['client_name'].'系列_'.$my_cid_arrs[2].'_(内部)对账单';
				}else{
					$excel_title = $client_result[0]['client_name'].'系列_'.$my_cid_arrs[2].'_对账单';
				}
			}else{
				$my_cid_arrs = explode('_',$cid_arr_go[0]);
				$cid_result = $model -> table('sj_channel') -> where(array('cid' => $my_cid_arrs[1])) -> select();
				if($_GET['type'] != 2){
					$excel_title = $cid_result[0]['chname'].'_'.$my_cid_arrs[2].'_(内部)对账单';
				}else{
					$excel_title = $cid_result[0]['chname'].'_'.$my_cid_arrs[2].'_对账单';
				}
			}

			header ( 'Content-Type: application/vnd.ms-excel' );
			$ua = $_SERVER["HTTP_USER_AGENT"];
			if (preg_match("/MSIE/", $ua)) {  
				header('Content-Disposition: attachment; filename="' . urlencode($excel_title) . '.xls"');
			} else if (preg_match("/Firefox/", $ua)) {  
				header('Content-Disposition: attachment; filename="' . $excel_title . '.xls"');
			} else {  
				header('Content-Disposition: attachment; filename="'.$excel_title.'.xls"');
			}	
			header('Cache-Control: max-age=0');
			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
			$objWriter->save('php://output');
		
		}

		$this -> display();
	}

	////运营审核列表操作申请单按钮
	function client_cheque(){
		$client_model = D('Channel_cooperation.channel_cooperation_p');
		$coefficient_model = D('Channel_cooperation.channel_coefficient_p');
		$model = new Model();
		include (dirname(__FILE__).'/../../ORG/PHPExcel/PHPExcel.php');
		$objPHPExcel = new PHPExcel();
		//设置属性
		$objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
		->setLastModifiedBy("Maarten Balliauw")
		->setTitle("Office 2007 XLSX Test Document")
		->setSubject("Office 2007 XLSX Test Document")
		->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
		->setKeywords("office 2007 openxml php")
		->setCategory("Test result file");
		
		$client_id = $_GET['client_id'];
		$month = $_GET['month'];
		$start_tm = strtotime($month.'01');
		$end_tm = strtotime(date('Ymt',strtotime($month.'01')));
		$my_month = date('m月',$start_tm);
		$my_cid = $_GET['my_cid']; //客户id_month_cid,客户id_month_cid,客户id_month_cid,
		$my_cid_str_go = substr($my_cid,0,-1);
		$my_cid_arr = explode(',',$my_cid_str_go);
		foreach($my_cid_arr as $key => $val){
			$my_cid_arr_go = explode('_',$val);
			if($my_cid_arr_go[1]){
				$my_cid_str .= $my_cid_arr_go[1] .',';
			}
		}
		$cid_str = substr($my_cid_str,0,-1);
		$client_channel_where['_string'] = "account_attr<>4 and client_id = {$client_id} and month = {$month} and cid in ({$cid_str})";
		$client_channel_result = $client_model -> table('co_channel_check') -> where($client_channel_where) -> select();
	
		$settle_entity_status = 1;
		foreach($client_channel_result as $key => $val){
			$price_result = $client_model -> table('co_channel_check') -> where(array('cid' => $val['cid'],'account_attr'=>array('neq', '4'),'month' => $month,'status' => 1)) -> select();
			$activation_where['_string'] = "cid = {$val['cid']} and submit_tm >= {$start_tm} and submit_tm <= {$end_tm} and status = 1";
			$activation_result = $coefficient_model -> table('activation_coefficient_state') -> where($activation_where) -> select();
			$activation_count_arr = array();
			foreach($activation_result as $k => $v){
				$activation_count_arr[] = $v['counts'];
			}
			$activation_count = array_sum($activation_count_arr);
			$amount_account = $val['amount_pay'];
			
			$all_activation_arr[] = $activation_count;
			if ($val['settle_entity'] == '2') {
				$settle_entity_status = 2;
			}
			$all_account_arr[]= $amount_account;
			$all_cid_arr[] = $val['cid'];
		}

		if ($settle_entity_status == 2) {
			$all_account = $all_account_arr[0];
		} else {
			$all_account = array_sum($all_account_arr);
		}

		$all_activation = array_sum($all_activation_arr);
		
		$all_cid = count($all_cid_arr);
		$client_result = $client_model -> table('co_client_list') -> where(array('id' => $client_id)) -> select();
		$charge_result = $client_model -> table('co_charge') -> where(array('id' => $client_result[0]['charge_id'])) -> select();
		$last_time = date('Y/m/d',time() + 86400*5);
		$type_account = $this -> cny($all_account);
		$all_account = number_format($all_account,2,'.',',');
		$this -> assign('charge_name',$charge_result[0]['charge_name']);
		$this -> assign('all_account',$all_account);
		$this -> assign('all_cid',$all_cid);
		$this -> assign('my_cid',$my_cid);
		$this -> assign('my_month',$my_month);
		$this -> assign('client_result',$client_result);
		$this -> assign('last_time',$last_time);
		$this -> assign('client_id',$client_id);
		$this -> assign('month',$month);
		$this -> assign('type_account',$type_account);
		
		if($_GET['from'] == 1){
			$my_channel_result = $my_month.'/'.$all_account.'元/'.$all_cid.'个';
			$sheet1 = $objPHPExcel->createSheet();
			$objPHPExcel->setActiveSheetIndex(1)
			->setCellValue('A1', '                                                           支票/电汇申请单                                      凭证编号:')
			->setCellValue('A2', '付款公司名称：北京力天无限网络技术有限公司')
			->setCellValue('C2', "申请人：{$charge_result[0]['charge_name']}")
			
			->setCellValue('A4', "收款账户：{$client_result[0]['account_gathering']}")
			->setCellValue('C4', "币种：人民币")
			->setCellValue('E4', "付款目的：付费推广")
			->setCellValue('A5', "开户银行：{$client_result[0]['opening_bank']}")
			->setCellValue('C5', "金额小写：{$all_account}")
			->setCellValue('E5', "客户名称：{$client_result[0]['client_name']}")
			->setCellValue('A6', "银行账户：{$client_result[0]['bank_account']}")
			->setCellValue('C6', "金额大写：{$type_account}")
			->setCellValue('E6', "结算月份/金额/渠道数量：\r\n{$my_channel_result}")
			->setCellValue('A7', "付款方式：电汇（√） 支票（）")
			->setCellValue('C7', "最后付款期限：{$last_time}")
			->setCellValue('A9', "申请人签字：\r\n")
			->setCellValue('C9', "部门主管签字：\r\n\r\n\r\n\r\n日期：")
			->setCellValue('E9', "事业部总经理签字：\r\n\r\n\r\n\r\n日期：")
			->setCellValue('A11', "财务审核签字：\r\n\r\n\r\n\r\n日期：")
			->setCellValue('C11', "财务负责人签字：\r\n\r\n\r\n\r\n日期：")
			->setCellValue('E11', "总经理签字：\r\n\r\n\r\n\r\n日期：");
			$objPHPExcel->getActiveSheet()->getStyle( 'A9')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
			$objPHPExcel->getActiveSheet()->getStyle( 'C9')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
			$objPHPExcel->getActiveSheet()->getStyle( 'E9')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
			$objPHPExcel->getActiveSheet()->getStyle( 'A11')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
			$objPHPExcel->getActiveSheet()->getStyle( 'C11')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
			$objPHPExcel->getActiveSheet()->getStyle( 'E11')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP); 
			$objPHPExcel->getActiveSheet()->getStyle('A7')->getAlignment()->setWrapText(true);
			$objPHPExcel->getActiveSheet()->getStyle('C7')->getAlignment()->setWrapText(true);
			$objPHPExcel->getActiveSheet()->getStyle('E7')->getAlignment()->setWrapText(true);
			$objPHPExcel->getActiveSheet()->getStyle('A9')->getAlignment()->setWrapText(true);
			$objPHPExcel->getActiveSheet()->getStyle('C9')->getAlignment()->setWrapText(true);
			$objPHPExcel->getActiveSheet()->getStyle('E9')->getAlignment()->setWrapText(true);
			$objPHPExcel->getActiveSheet()->getStyle('A11')->getAlignment()->setWrapText(true);
			$objPHPExcel->getActiveSheet()->getStyle('C11')->getAlignment()->setWrapText(true);
			$objPHPExcel->getActiveSheet()->getStyle('E11')->getAlignment()->setWrapText(true);
			$objPHPExcel->getActiveSheet()->getStyle('C6')->getAlignment()->setWrapText(true);
			$objPHPExcel->getActiveSheet()->getStyle('A4')->getAlignment()->setWrapText(true);
			$objPHPExcel->getActiveSheet()->getStyle('A2')->getAlignment()->setWrapText(true);
			$objPHPExcel->getActiveSheet()->getStyle('A5')->getAlignment()->setWrapText(true);
			$objPHPExcel->getActiveSheet()->getStyle('E5')->getAlignment()->setWrapText(true);
			$objPHPExcel->getActiveSheet()->getStyle('A6')->getAlignment()->setWrapText(true);
			$objPHPExcel->getActiveSheet()->getStyle('E4')->getAlignment()->setWrapText(true);
			
			$objPHPExcel->getActiveSheet()->mergeCells('A1:F1');
			$objPHPExcel->getActiveSheet()->mergeCells('A2:B2');
			$objPHPExcel->getActiveSheet()->mergeCells('C2:F2');
			$objPHPExcel->getActiveSheet()->mergeCells('E6:F7');
			$objPHPExcel->getActiveSheet()->mergeCells('A9:B10');
			$objPHPExcel->getActiveSheet()->mergeCells('C9:D10');
			$objPHPExcel->getActiveSheet()->mergeCells('E9:F10');
			$objPHPExcel->getActiveSheet()->mergeCells('A11:B12');
			$objPHPExcel->getActiveSheet()->mergeCells('C11:D12');
			$objPHPExcel->getActiveSheet()->mergeCells('E11:F12');
			$objPHPExcel->getActiveSheet()->mergeCells('A8:F8');
			$objPHPExcel->getActiveSheet()->mergeCells('A3:F3');
			$objPHPExcel->getActiveSheet()->mergeCells('C3:F3');
			$objPHPExcel->getActiveSheet()->mergeCells('A4:B4');
			$objPHPExcel->getActiveSheet()->mergeCells('C4:D4');
			$objPHPExcel->getActiveSheet()->mergeCells('E4:F4');
			$objPHPExcel->getActiveSheet()->mergeCells('A5:B5');
			$objPHPExcel->getActiveSheet()->mergeCells('C5:D5');
			$objPHPExcel->getActiveSheet()->mergeCells('E5:F5');
			$objPHPExcel->getActiveSheet()->mergeCells('A6:B6');
			$objPHPExcel->getActiveSheet()->mergeCells('C6:D6');
			$objPHPExcel->getActiveSheet()->mergeCells('A7:B7');
			$objPHPExcel->getActiveSheet()->mergeCells('C7:D7');
			$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(25);
			$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
			$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
			$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(25);
			$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(25);
			$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(25);
			$objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setSize(16);

			//设置行高
			$objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(36.75);
			$objPHPExcel->getActiveSheet()->getRowDimension('2')->setRowHeight(36.75);
			$objPHPExcel->getActiveSheet()->getRowDimension('3')->setRowHeight(36.75);
			$objPHPExcel->getActiveSheet()->getRowDimension('4')->setRowHeight(36.75);
			$objPHPExcel->getActiveSheet()->getRowDimension('5')->setRowHeight(36.75);
			$objPHPExcel->getActiveSheet()->getRowDimension('6')->setRowHeight(36.75);
			$objPHPExcel->getActiveSheet()->getRowDimension('7')->setRowHeight(36.75);
			$objPHPExcel->getActiveSheet()->getRowDimension('8')->setRowHeight(36.75);
			$objPHPExcel->getActiveSheet()->getRowDimension('9')->setRowHeight(36.75);
			$objPHPExcel->getActiveSheet()->getRowDimension('10')->setRowHeight(36.75);
			$objPHPExcel->getActiveSheet()->getRowDimension('11')->setRowHeight(36.75);
			$objPHPExcel->getActiveSheet()->getRowDimension('12')->setRowHeight(36.75);
			//设置列宽
			$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(21.29);
			$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(25.14);
			$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(19);
			$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(22.43);
			$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(19);
			$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(19);
			
			$objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
			$objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
			//设置边框
			$objPHPExcel->getActiveSheet()->getStyle('A2')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A2')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A2')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B2')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B2')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B2')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('C2')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('C2')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			
			$objPHPExcel->getActiveSheet()->getStyle('D2')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('D2')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			
			$objPHPExcel->getActiveSheet()->getStyle('E2')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('E2')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('F2')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('F2')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('F2')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A4')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A4')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B4')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A4')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('C4')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('D4')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B4')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B4')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('C4')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('C4')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('D4')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('D4')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('E4')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('E4')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('E4')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('F4')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('F4')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('F4')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A5')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A5')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B5')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B5')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('C5')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('D5')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('D5')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('E5')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('F5')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('F5')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A6')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A6')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B6')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B6')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('C6')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('D6')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('D6')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('F6')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A7')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A7')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B7')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B7')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('C7')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('D7')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('D7')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('E7')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('F7')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('F7')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A9')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A9')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B9')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A10')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B10')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B10')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B9')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('C10')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('C9')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('D10')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('D10')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('D9')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('D9')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('E10')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('E9')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('F10')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('F10')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('F9')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('F9')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A11')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B11')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('D11')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('F11')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A11')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A11')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B11')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B11')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('C11')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('D11')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('D11')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('E11')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('F11')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('F12')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B12')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('D12')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A12')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B12')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('C12')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('D12')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('E12')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('F12')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
		

			
			$range1 = "A8:B8";
			$range2 = "E6:F6";
			$range3 = "C8:D8";
			$range5 = "C10:D10";
			$range4 = "E8:F8";
			$objPHPExcel->getActiveSheet()->getStyle($range4)-> getAlignment()->setWrapText(true);
			$objPHPExcel->getActiveSheet()->getStyle($range5)-> getAlignment()->setWrapText(true);
			$objPHPExcel->getActiveSheet()->getStyle($range1)-> getAlignment()->setWrapText(true);
			$objPHPExcel->getActiveSheet()->getStyle($range2)-> getAlignment()->setWrapText(true);
			$objPHPExcel->getActiveSheet()->setTitle($this->convertUTF8('运营审核申请单'));
		
			if(count($my_cid_arr) > 1){
				$my_cid_arrs = explode('_',$my_cid_arr[0]);
				$client_result = $client_model -> table('co_client_list') -> where(array('id' => $my_cid_arrs[0])) -> select();
				$excel_title = $client_result[0]['client_name'].'系列_'.$my_cid_arrs[2].'_打款申请单';
			}else{
				$my_cid_arrs = explode('_',$my_cid_arr[0]);
				$cid_result = $model -> table('sj_channel') -> where(array('cid' => $my_cid_arrs[1])) -> select();
				$excel_title = $cid_result[0]['chname'].'_'.$my_cid_arrs[2].'_打款申请单';
			}
			
			header ( 'Content-Type: application/vnd.ms-excel' );
			$ua = $_SERVER["HTTP_USER_AGENT"];
			if (preg_match("/MSIE/", $ua)) {  
				header('Content-Disposition: attachment; filename="' . urlencode($excel_title) . '.xls"');
			} else if (preg_match("/Firefox/", $ua)) {  
				header('Content-Disposition: attachment; filename="' . $excel_title . '.xls"');
			} else {  
				header('Content-Disposition: attachment; filename="'.$excel_title.'.xls"');
			}	
			header('Cache-Control: max-age=0');
			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
			$objWriter->save('php://output');
		}
		
		$this -> display();
		
	}

	// 金额大写
	function cny($data){
		$capnum=array("零","壹","贰","叁","肆","伍","陆","柒","捌","玖");
		$capdigit=array("","拾","佰","仟");
		$subdata=explode(".",$data);
		$yuan=$subdata[0];
		$j=0; $nonzero=0;
		for($i=0;$i<strlen($subdata[0]);$i++){
			if(0==$i){ //确定个位
				if($subdata[1]){
					$cncap=(substr($subdata[0],-1,1)!=0)?"元":"元"; //修改过
				}else{
					$cncap="元";
				}
			}
			if(4==$i){ $j=0; $nonzero=0; $cncap="万".$cncap; } //确定万位
			if(8==$i){ $j=0; $nonzero=0; $cncap="亿".$cncap; } //确定亿位
			$numb=substr($yuan,-1,1); //截取尾数
			$cncap=($numb)?$capnum[$numb].$capdigit[$j].$cncap:(($nonzero)?"零".$cncap:$cncap);
			$nonzero=($numb)?1:$nonzero;
			$yuan=substr($yuan,0,strlen($yuan)-1); //截去尾数
			$j++;
		}

		if($subdata[1]){
			$chiao=(substr($subdata[1],0,1))?$capnum[substr($subdata[1],0,1)]."角":""; //修改过
			$cent=(substr($subdata[1],1,1))?$capnum[substr($subdata[1],1,1)]."分":"";
		}
		$cncap .= $chiao.$cent;
		$cncap=preg_replace("/(零)+/","零",$cncap); //合并连续“零”
		$cncap=str_replace("亿万","亿",$cncap);
		return $cncap;
	}
	
	// 财务结算列表
	function financial_settle(){
		$client_model = D('Channel_cooperation.channel_cooperation_p');
		$model = new Model();
		$client_name = trim($_GET['client_name']);
		$chname = trim($_GET['chname']);
		$start_tm  = date('Ym',strtotime($_GET['start_tm'].'-01'));
		$end_tm = date('Ym',strtotime($_GET['end_tm'].'-01'));
		if($_GET['start_tm'] && strtotime($_GET['start_tm'].'-01') > strtotime($_GET['end_tm'].'-01')){
			$this -> error("开始时间不能大于结束时间");
		}
		
		//限制客户查看权限
		$admin_id = $_SESSION['admin']['admin_id'];
		$admin_filter_result = $model -> table('sj_admin_filter') -> where(array('source_type' => 1,'source_value' => $admin_id,'target_type' => 2,'filter_type' => 2)) -> field('target_value') -> select();
		foreach($admin_filter_result as $key => $val){
			$admin_cid[] = $val['target_value'];
		}
		$all_client = $client_model -> table('co_client_list') -> order('id') -> select();
	
		if($admin_cid){
			foreach($all_client as $key => $val){
				$client_channel_result = $client_model -> table('co_client_channel') -> where(array('client_id' => $val['id'])) -> select();
				$my_cid_power = array();
				foreach($client_channel_result as $k => $v){
					$my_cid_power[] = $v['cid'];
				}
				if(!array_diff($my_cid_power,$admin_cid)){
					$my_client_id_str .= $val['id'].',';
				}
			}
		}else{
			$my_client_id = '';
		}
		$my_client_id = substr($my_client_id_str,0,-1);
		
		$count_power_result = $model -> table('sj_admin_filter') -> where(array('source_type' => 1,'source_value' => $admin_id,'target_type' => 9)) -> find();

		if($count_power_result['filter_type'] == 2){
			$where_go .= " and client_id in({$my_client_id})";
		}
		if($chname){
			$channel_where['_string'] = "chname like '%{$chname}%' and status = 1";
			$channel_result = $model -> table('sj_channel') -> where($channel_where) -> select();

			foreach($channel_result as $key => $val){
				$channel_cid_str .= $val['cid'].',';
			}
			$cid_str = substr($channel_cid_str,0,-1);
			
			$where_client = "cid in ({$cid_str}) AND account_attr<>4";
			$where_my_channel = "cid in ({$cid_str}) AND account_attr<>4";
			$my_channel_result = $client_model -> table('co_channel_check') -> where($where_my_channel) -> select();
			
			foreach($my_channel_result as $key => $val){
				$my_id_go .= $val['id'].',';
			}
		
			$my_id = substr($my_id_go,0,-1);
			//$my_month_str = substr($my_month_str_go,0,-1);
			
			$client_channel_result = $client_model -> table('co_channel_check') -> where($where_client) -> select();
			
			foreach($client_channel_result as $key => $val){
				$channel_client_id_arr[] = $val['client_id'];
			}
			
			$channel_client_id_arr = array_unique($channel_client_id_arr);
		}
		
		if($client_name){
			$client_where['_string'] = "client_name like '%{$client_name}%' and status != 0";
			$client_result = $client_model -> table('co_client_list') -> where($client_where) -> select();
			foreach($client_result as $key => $val){
				$client_id_arr[] = $val['id'];
			}
		}
				
		if($_GET['from'] == 1 && $_GET['check_ids']){
			$check_ids = $_GET['check_ids']; //629,638,612,613,614,
			$check_ids_str = substr($check_ids,0,-1); //629,638,612,613,614
			$where_go .= " and id in ({$check_ids_str})";
		}elseif($client_name && $chname){
			$client_arr = array_intersect($channel_client_id_arr,$client_id_arr);
			foreach($client_arr as $key => $val){
				$client_str_go .= $val.',';
			}
			$client_str = substr($client_str_go,0,-1);
			$where_go .= " and client_id in ({$client_str}) and id in ({$my_id})";
		}elseif($client_name && !$chname){
			$client_arr = $client_id_arr;
			foreach($client_arr as $key => $val){
				$client_str_go .= $val.',';
			}
			$client_str = substr($client_str_go,0,-1);
			$where_go .= " and client_id in ({$client_str})";
		}elseif(!$client_name && $chname){
			$client_arr = $channel_client_id_arr;
			foreach($client_arr as $key => $val){
				$client_str_go .= $val.',';
			}
			$client_str = substr($client_str_go,0,-1);
			
			$where_go .= " and client_id in ({$client_str}) and id in ({$my_id})";
		}
		
		if($_GET['start_tm']){
			$where_go .= " and month >= {$start_tm}";
		}
		if($_GET['end_tm']){
			$where_go .= " and month <= {$end_tm}";
		}
		$my_status = $_GET['my_status'];
		
		if($my_status && $my_status != 4){
			if($my_status == 5){
				$where_go .= " and status = 0";
			}else{
				$where_go .= " and status = {$my_status}";
			}
		}
	
		if($_GET['client_id']){
			if(!$my_status){
				$where['_string'] = "account_attr<>4 AND (status = 2 or status = 3 or status = 0) and client_id = {$_GET['client_id']}";
			}else{
				$where['_string'] = "account_attr<>4 AND status = {$my_status} and client_id = {$_GET['client_id']}";
			}
		}else{
			if(!$my_status){
				$where['_string'] = "account_attr<>4 AND (status = 2 or status = 3 or status = 0)".$where_go;
			}else{
				$where['_string'] = "account_attr<>4".$where_go;
			}
		}
	
		$count = $client_model -> table('co_channel_check') -> where($where) -> group('status,client_id,month') ->  order('month DESC') -> select();
		import("@.ORG.Page");
		$param = http_build_query($_GET);
		$Page = new Page(count($count), 50, $param);
                if($_GET['from'] == 1){
		$result = $client_model -> table('co_channel_check') -> where($where) -> group('status,client_id,month') -> order('month DESC,status DESC') -> select();
                }else{
		$result = $client_model -> table('co_channel_check') -> where($where) -> limit($Page->firstRow . ',' . $Page->listRows) -> group('status,client_id,month') -> order('month DESC,status DESC') -> select();
                }
		if(!$_GET['p']){
			$_GET['p'] = 1;
		}
		if(!$_GET['lr']){
			$_GET['lr'] = 50;
		}
		
		foreach($result as $key => $val){
			$val['num'] = 1+$key+$_GET['lr']*($_GET['p']-1);
			if($my_status && $my_status != 5){
				$where_channel_result['_string'] = "account_attr<>4 AND month = {$val['month']} and client_id = {$val['client_id']} and status = {$my_status} ";
			}else{
				$where_channel_result['_string'] = "account_attr<>4 AND month = {$val['month']} and client_id = {$val['client_id']} and status = {$val['status']} ";
			}
			$channel_result = $client_model -> table('co_channel_check') -> where($where_channel_result) -> order('activation DESC') -> select();
			//echo $client_model ->getLastSql();
			$client_result = $client_model -> table('co_client_list') -> where(array('id' => $val['client_id'])) -> select();
			$val['client_name'] = $client_result[0]['client_name'];
			$all_activation_arr = array();
			$all_settle_amount_arr = array();
			$all_salvation_arr = array();
			$all_amount_pay_arr = array();
			$all_bill_amount = array();
			$all_my_salvation_arr = array();
			$channel_paid_new = array();
			$all_no_paid = array();
			$bill_amount_new = array();
			$bill_channel_result = $client_model -> table('co_channel_check') -> where($where_channel_result) -> group('bath_tm,client_id,month') -> order('activation DESC') -> select();
			foreach($bill_channel_result as $k => $v){
				$all_bill_amount[] = $v['bill_amount'];
				$v['bill_amount'] = number_format($v['bill_amount'],2,'.',',');
			}
			$settle_entity_status = 1;
			foreach($channel_result as $k => $v){
				$v['comments'] = mb_substr($v['comment'],0,4,'utf-8');
				$chname_result = $model -> table('sj_channel') -> where(array('cid' => $v['cid'])) -> select();
				$v['chname'] = $chname_result[0]['chname'];
				$all_activation_arr[] = $v['activation'];
				if ($v['settle_entity'] == '2') {
					$settle_entity_status = 2;
					$channel_paid_new[] = $v['amount_paid'];
				} else {					
					if($v['settle_group']==0){
					    $channel_paid_new[$v['settle_group']] = $v['amount_paid'];
					}else{
					    $channel_paid_new[] = $v['amount_paid'];
					}
					
				}
				$all_settle_amount_arr[] = $v['settle_amount'];
				$all_salvation_arr[] = $v['salvation'];
				$all_amount_pay_arr[] = $v['amount_pay'];
				$bill_amount_new[$v['settle_group']] = $v['bill_amount'];
				if($v['status'] == 2){
					$v['self_salvation'] = $v['amount_pay'] - $v['amount_paid'];
				}else{
					$v['self_salvation'] = 0;
				}
				if($v['status'] == 3){
					$all_no_paid[] = $v['amount_pay'];					
				}
				$v['no_paid'] = $v['amount_pay'] - $v['amount_paid'] - $v['self_salvation'];
				$v['self_salvation'] = number_format($v['self_salvation'],2,'.',',');
				$v['settle_amount'] = number_format($v['settle_amount'],2,'.',',');
				$v['amount_pay'] = number_format($v['amount_pay'],2,'.',',');
				$v['amount_paid'] = number_format($v['amount_paid'],2,'.',',');
				$v['activation'] = number_format($v['activation'],0,'.',',');
				$v['no_paid'] = number_format($v['no_paid'],2,'.',',');
				
				$channel_result[$k] = $v;
				$settle_group[$v['month'].$v['client_id'].$v['status']][] = $v['settle_group'];
				
			}
			if($settle_entity_status == 1){
				$the_settle_amount_arr[] = array_sum($all_settle_amount_arr);
				$the_salvation_arr[] = array_sum($all_salvation_arr);
				$all_amount_pay_tmp = array_sum($all_amount_pay_arr);
				$the_amount_pay_arr[] = array_sum($all_amount_pay_arr);
				$val['bill_amount'] = array_sum($bill_amount_new);
				$channel_paid_new_tmp = array_sum($channel_paid_new);
				$the_no_paid_arr[] = array_sum($all_no_paid);
			}else{
				$the_settle_amount_arr[] = $all_settle_amount_arr[0];
				$the_salvation_arr[] = $all_salvation_arr[0];
				$all_amount_pay_tmp = $all_amount_pay_arr[0];
				$the_amount_pay_arr[] = $all_amount_pay_arr[0];
				$val['bill_amount'] = $bill_amount_new[0];
				$channel_paid_new_tmp = $channel_paid_new[0];
				$the_no_paid_arr[] = $all_no_paid[0];
			}
			$the_activation_arr[] = array_sum($all_activation_arr);
			if($val['status'] == 2){
				//$channel_amount_paid = str_replace(',','',$channel_result[0]['amount_paid']);
				$val['my_salvation'] = $all_amount_pay_tmp -$channel_paid_new_tmp;
				$val['no_paid'] = $all_amount_pay_tmp - $channel_paid_new_tmp - $val['my_salvation'];
				$val['no_paid'] = number_format($val['no_paid'],2,'.',',');
			}else{
				$val['my_salvation'] = 0;
				$val['no_paid'] = $all_amount_pay_tmp;
				$val['no_paid'] = number_format($val['no_paid'],2,'.',',');
			}
			$all_my_salvation[] = $val['my_salvation'];
			$val['my_salvation'] = number_format($val['my_salvation'],2,'.',',');
			$all_bill_amount_arr[] = $val['bill_amount'];
			$all_amount_paid_arr[] = $channel_paid_new_tmp;
			$val['amount_paid'] = number_format($val['amount_paid'],2,'.',',');
			$all_no_paid = $all_no_paid_arr[0];
			$all_amount_pay = $all_amount_pay_tmp;
			$val['all_no_paid'] = number_format($all_no_paid,2,'.',',');
			$val['all_amount_pay'] = number_format($all_amount_pay,2,'.',',');
			$val['all_amount_paid'] = number_format($channel_paid_new_tmp,2,'.',',');
			$val['all_my_salvation'] = number_format($all_my_salvation,2,'.',',');
			$num = count($channel_result);
			$max_time = 0;
			for($i=0; $i<$num ;$i++){
				for($j=1;$j<$num-$i;$j++){
					if($channel_result[$j-1]['settle_group']>$channel_result[$j]['settle_group']){
						$temp = $channel_result[$j];
						$channel_result[$j] = $channel_result[$j-1];
						$channel_result[$j-1] = $temp;
					}					
					
				}
				if($channel_result[$i]['update_tm']>$max_time){
				    	$max_time = $channel_result[$i]['update_tm'];			    	
				}		
			}
			$val['max_update_tm'] = $max_time;
			$val['cid_result'] = $channel_result;
			$result[$key] = $val;
			//var_dump($channel_result);
		}
		
		$the_my_salvation = number_format(array_sum($all_my_salvation),2,'.',',');
		$the_bill_amount = number_format(array_sum($all_bill_amount_arr),2,'.',',');
		
		$the_activation = number_format(array_sum($the_activation_arr),0,'.',',');
		$the_settle_amount = number_format(array_sum($the_settle_amount_arr),2,'.',',');
		$the_salvation = number_format(array_sum($the_salvation_arr),2,'.',',');
		$the_amount_pay = number_format(array_sum($the_amount_pay_arr),2,'.',',');
		$the_amount_paid = number_format(array_sum($all_amount_paid_arr),2,'.',',');
		$the_no_paid = number_format(array_sum($the_no_paid_arr),2,'.',',');

		$this -> assign('the_bill_amount',$the_bill_amount); //总计发票金额
		$this -> assign('the_my_salvation',$the_my_salvation); //总计差额补齐
		$this -> assign('the_activation',$the_activation); //总计激活量
		$this -> assign('the_settle_amount',$the_settle_amount); //总计结算金额
		$this -> assign('the_salvation',$the_salvation); //总计补差
		$this -> assign('the_amount_pay',$the_amount_pay); //总计应付金额
		$this -> assign('the_amount_paid',$the_amount_paid); //总计已付金额
		$this -> assign('the_no_paid',$the_no_paid); //总计未付金额
		$this -> assign('settle_group',$settle_group);
		$Page->setConfig('header', '篇记录');
		$Page->setConfig('first', '<<');
		$Page->setConfig('last', '>>');
		$show = $Page->show();
		$this -> assign('min_pay',$min_result[0]['configcontent']);
		$this->assign("page", $show);
		$this -> assign('p',$_GET['p']);
		$this -> assign('lr',$_GET['lr']);
		$this -> assign('result',$result);			
		$this -> assign('my_status',$my_status);
		$this -> assign('client_name',$client_name);
		$this -> assign('chname',$chname);
		$this -> assign('start_tm',$_GET['start_tm']);
		$this -> assign('end_tm',$_GET['end_tm']);
	
		
        if($_GET['from'] == 1){

            $file_xls = '财务结算报表_'.date('Ymd').".xls";//文件名
            header("Content-type:text/csv");
            header("Content-Disposition:attachment;filename=".$file_xls);
            header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
            header('Expires:0');
            header('Pragma:public');
            $str = $this -> fetch('financial_settle2');
            echo $str;
            exit;

                    /*
			foreach($result as $key => $val){
				foreach($val['cid_result'] as $k => $v){
					if($v['update_tm']){
						$my_time = date('Y-m-d H:i:s',$v['update_tm']);
					}
					$v['salvation'] = number_format($v['salvation'],2,'.',',');
					if($v['status'] == 0){ $tstatus = '已冻结';}elseif($v['status'] == 2){$tstatus='已付款';}elseif($v['status'] == 3){$tstatus='未付款';}
					$file_str .= $val['num'].','.$val['month'].','.$val['client_name'].','.$v['chname'].',"'.$v['activation'].'",'.$v['price'].',"'.$v['settle_amount'].'","'.$v['salvation'].'","'.$val['cid_result'][0]['taxt'].'"%,"'.$v['amount_pay'].'","'.$v['amount_paid'].'","'.$v['self_salvation'].'","'.$v['no_paid'].'","'.$v['bill_amount'].'",'.$my_time.",{$tstatus}"."\n";
				}
			}
	
			$file_gos = '财务结算报表_'.date('Ymd').".csv";//文件名
			if(strpos($_SERVER["HTTP_USER_AGENT"],"MSIE")){  
				$file_go = urlencode($file_gos); 
			}else{
				$file_go = $file_gos;
			}
			header( "Cache-Control: public" );
			header( "Pragma: public" );
			header("Content-type:application/vnd.ms-excel");
			header('Content-Disposition:attachment;filename='.$file_go);
			header('Content-Type:APPLICATION/OCTET-STREAM');
			ob_start();
			$header_str =  iconv("UTF-8",'GBK',"序号,月份,客户名称,渠道名称,激活量,单价,结算金额,补差,税率,应付金额,已付金额,差额补齐,未付金额,发票金额,操作时间,状态");
			$file_str_go=  iconv("UTF-8",'GBK',$file_str);
			echo $header_str."\n";
			echo $file_str_go;
			echo iconv("UTF-8",'GBK','总计,-,,-,"'.$the_activation.'",-,"'.$the_settle_amount.'","'.$the_salvation.'",,"'.$the_amount_pay.'","'.$the_amount_paid.'","'.$the_my_salvation.'","'.$the_no_paid.'","'.$the_bill_amount.'"');
			ob_end_flush();
			exit;
                     */
		
		}

		$this -> display('financial_settle');
	}
	
	// 财务结算结算按钮窗口
	function settle_entering_show(){
		$client_model = D('Channel_cooperation.channel_cooperation_p');
		$model = new Model();
		$chname = $_GET['chname'];
		$my_cid = substr($_GET['my_cid'],0,-1);
		$client_name = $_GET['client_name'];
		$p = $_GET['p'];
		$lr = $_GET['lr'];
		$start_tm = $_GET['start_tm'];
		$end_tm = $_GET['end_tm'];
		$client_id = $_GET['client_id'];
		$month = $_GET['month'];
		$settle_entity = $_GET['settle_entity'];
		$my_status = $_GET['my_status'];
		$settle_result = $client_model -> table('co_channel_check') -> where(array('client_id' => $client_id,'month' => $month,'status' => 3,'id'=>array('exp',"in ({$my_cid})"))) -> field('amount_pay,settle_amount,id,cid') -> select();
		$cid = array();
		foreach($settle_result as $val){
			$cid[] = $val['cid'];
		}
		
		$cid_str = implode(',',$cid);	
		$where['cid'] = array('in',"{$cid_str}");
		$channel_result = $model -> table('sj_channel') -> where($where)->field('cid,chname') -> select();
		foreach($channel_result as $val){
			$c_result[$val['cid']] = $val['chname'];		
		}
		
		if ($settle_entity == '2') {
			$settle_result = array($settle_result[0]);
			$client_result = $client_model -> table('co_client_list') -> where(array('id' => $client_id)) -> field('client_name') -> select();
			$now_client_name = $client_result[0]['client_name'];
		}
		//$this -> assign('amount_pay',$settle_result[0]['sum(amount_pay)']);
		//$this -> assign('settle_amount',$settle_result[0]['sum(settle_amount)']);

		$this -> assign('now_client_name',$now_client_name);
		$this -> assign('c_result',$c_result);
		$this -> assign('settle_result',$settle_result);
		$this -> assign('chname',$chname);
		$this -> assign('client_name',$client_name);
		$this -> assign('start_tm',$_GET['start_tm']);
		$this -> assign('end_tm',$_GET['end_tm']);
		$this -> assign('client_id',$client_id);
		$this -> assign('month',$month);
		$this -> assign('settle_entity',$settle_entity);
		$this -> assign('p',$p);
		$this -> assign('my_status',$my_status);
		$this -> assign('lr',$lr);
		$this -> display();
	}
	
	// 财务结算结算按钮接口
	function settle_entering(){
		$client_model = D('Channel_cooperation.channel_cooperation_p');
		$chname = $_POST['chname'];
		$client_name = $_POST['client_name'];
		$p = $_POST['p'];
		$lr = $_POST['lr'];
		$start_tm = $_POST['start_tm'];
		$end_tm = $_POST['end_tm'];
		$client_id = $_POST['my_client'];
		$month = $_POST['month'];
		$settle_entity = $_POST['settle_entity'];
		$my_status = $_POST['my_status'];
		$attr_where['_string'] = "client_id = {$client_id} and month = {$month} and account_attr<>4";
		$attr_result = $client_model -> table('co_channel_check') -> where($attr_where) ->select();
		$time = time();
		//echo $client_model->getLastSql();
		if($chname){
			$where_go .= "/chname/{$chname}";
		}
		if($client_name){
			$where_go .= "/client_name/{$client_name}";
		}
		if($p){
			$where_go .= "/p/{$p}";
		}
		if($lr){
			$where_go .= "/lr/{$lr}";
		}
		if($start_tm){
			$where_go .= "/start_tm/{$start_tm}";
		}
		if($end_tm){
			$where_go .= "/end_tm/{$end_tm}";
		}
		if($my_status){
			$where_go .= "/status/{$my_status}";
		}
		
		$amount_paid = $_POST['amount_paid']; //付款金额
		$my_salvation = $_POST['my_salvation']; //差额补齐
		$bill_amount = $_POST['bill_amount']; //发票金额

		if($attr_result[0]['account_attr'] == 1){
			if(!is_numeric($bill_amount)){
				$this -> error("发票金额格式错误");
			}
		}else{
			if(!is_numeric($bill_amount) && $bill_amount){
				$this -> error("发票金额格式错误");
			}
		}
		if($attr_result[0]['account_attr'] == 1){
			if(!$_POST['invoice_tm'] || !$_POST['paid_tm']){
				$this -> error("请选择收票日期和付款日期");
			}
		}else{
			if(!$_POST['paid_tm']){
				$this -> error("请选择收票日期和付款日期");
			}
		}
		$invoice_tm = strtotime(date('Ymd 23:59:59',strtotime($_POST['invoice_tm'])));
		$paid_tm = strtotime(date('Ymd 23:59:59',strtotime($_POST['paid_tm'])));
		$settle_group = 0;
		foreach($attr_result as $attr_v){
			if($attr_v['settle_group']>$settle_group){
				$settle_group = $attr_v['settle_group'];	
			}
		}
		$settle_group += 1;
		$where['client_id'] = $client_id;
		$where['month'] = $month;
		$where['status'] = 3;
		$client_result = $client_model -> table('co_client_list') -> where(array('id' => $client_id)) -> select();
		$affix_result = $client_model -> table('co_affix') -> where(array('client_id' => $client_id)) -> select();
		foreach($affix_result as  $k => $v){
			$my_file_str .= $v['id'].',';
		}
		$my_file = substr($my_file_str,0,-1);
		if(!$my_file){
			$my_file = '';
		}
		$result = '';
		foreach($amount_paid as $key => $v){			
			if(!is_numeric($v)){
				$this -> error("付款金额格式错误");
			}
			$data = array(
				'amount_paid' => $v,
				'bill_amount' => $bill_amount,
				'invoice_tm' => $invoice_tm,
				'paid_tm' => $paid_tm,
				'status' => 2,
				'update_tm' => $time,
				'account_attr' => $client_result[0]['account_attr'],
				'company_name' => $client_result[0]['company_name'],
				'account_gathering' => $client_result[0]['account_gathering'],
				'opening_bank' => $client_result[0]['opening_bank'],
				'bank_account' => $client_result[0]['bank_account'],
				'contract_num' => $client_result[0]['contract_num'],
				'my_file' => $my_file,
				'bath_tm' => $time,
				'settle_group' => $settle_group
			);
			if ($settle_entity == '2') {
				$where['account_attr'] = array('neq', '4'); 
			} else {
				$where['id'] = $key;
			}
			$check_id .= $key.',';
			$log_result = $this -> logcheck(array('client_id'=> $client_id,'month' => $month,'status' => 3),'co_channel_check',$data,$client_model);
			$result = $client_model -> table('co_channel_check') -> where($where) -> save($data);
			
		}

		if($result){
			$this -> writelog("已录入客户id为{$client_id}，渠道id为{$check_id}月份为{$month}的财务结算".$log_result, 'co_channel_check', $client_id,__ACTION__ ,'','edit');
			$this -> assign('jumpUrl','/index.php/Channel_cooperation/Channelsettle_p/financial_settle'.$where_go);
			$this -> success("编辑成功");
		}else{
			$this -> error("编辑失败");
		}
		
		
	}
	
	function settle_detail(){
		$client_model = D('Channel_cooperation.channel_cooperation_p');
		$model = new Model();
		$client_id = $_GET['client_id'];
		$month = $_GET['month'];
		$affixs_where['_string'] = "client_id = {$client_id} and status = 1";
		$affixs_result = $client_model -> table('co_affix') -> where($affixs_where) -> select();
		$status = $_GET['status'];
		$client_result = $client_model -> table('co_client_list') -> where(array('id' => $client_id)) -> select();
		foreach($client_result as $key => $val){
			$val['affix_result'] = $affixs_result;
			$client_result[$key] = $val;
		}
		
		if($status == 2){
			$client_channel_result = $client_model -> table('co_channel_check') -> where(array('client_id' => $client_id,'month' => $month,'status' => 2)) ->order('settle_group asc')-> select();
			
			foreach($client_channel_result as $key => $val){
				$val['the_time'] = date('Y年m月',$val['month'].'01');
				$amount_pay_arr[] = $val['amount_pay'];
				$settle_amount_arr[] = $val['settle_amount'];
				$salvation_arr[] = $val['salvation'];
				$channel_result = $model -> table('sj_channel') -> where(array('cid' => $val['cid'])) -> select();
				$chname_str .= $channel_result[0]['chname'].',';
				$val['chname'] = substr($chname_str,0,-1);
				$affix_where['_string'] = "id in ({$val['my_file']})";
				$affix_result = $client_model -> table('co_affix') -> where($affix_where) -> select();
				$val['affix_result'] = $affix_result;
				$client_channel_result[$key] = $val;
				$bill_amount[$val['settle_group']] = number_format($val['bill_amount'],2,'.',',').'元';
				$invoice_tm[$val['settle_group']] = date('Y-m-d',$val['invoice_tm']);
				$paid_tm[$val['settle_group']] = date('Y-m-d',$val['paid_tm']);
				$amount_paid[] = $val['amount_paid'];
			}
			$amount_paid = array_sum($amount_paid);

			$client_name_result = $client_model -> table('co_client_list') -> where(array('id' => $client_id)) -> select();
			$client_results[0]['the_time'] = date('Y年m月',strtotime($client_channel_result[0]['month'].'01'));
			$client_results[0]['amount_pay'] = number_format(array_sum($amount_pay_arr),2,'.',',');
			$client_results[0]['account_attr'] = $client_channel_result[0]['account_attr'];
			$client_results[0]['company_name'] = $client_channel_result[0]['company_name'];
			$client_results[0]['account_gathering'] = $client_channel_result[0]['account_gathering'];
			$client_results[0]['opening_bank'] = $client_channel_result[0]['opening_bank'];
			$client_results[0]['contract_num'] = $client_channel_result[0]['contract_num'];
			$client_results[0]['bank_account'] = $client_channel_result[0]['bank_account'];
			$client_results[0]['affix_result'] = $client_channel_result[0]['affix_result'];
	
			$client_results[0]['client_name'] = $client_result[0]['client_name'];
			//$channel_amount_paid = str_replace(',','',$client_channel_result[0]['amount_paid']);
			$client_results[0]['my_salvation'] = array_sum($amount_pay_arr) - $amount_paid;
			
			$client_results[0]['my_salvation'] = number_format($client_results[0]['my_salvation'],2,'.',',');
			$client_results[0]['settle_amount'] = array_sum($settle_amount_arr);
			$client_results[0]['settle_amount'] = number_format($client_results[0]['settle_amount'],2,'.',',');
			$client_results[0]['chname'] = substr($chname_str,0,-1);

			//$client_results[0]['bill_amount'] = $client_channel_result[0]['bill_amount'];
			//$client_results[0]['bill_amount'] = number_format($client_results[0]['bill_amount'],2,'.',',');
			$client_results[0]['bill_amount'] = implode(',',$bill_amount);

			$client_results[0]['salvation'] = array_sum($salvation_arr);
			$client_results[0]['salvation'] = number_format($client_results[0]['salvation'],2,'.',',');
			//收票日期
			//$client_results[0]['invoice_tm'] = $client_channel_result[0]['invoice_tm'];
			$client_results[0]['invoice_tm'] = implode(',',$invoice_tm);
			//已付金额
			
			//$client_results[0]['amount_paid'] = $client_channel_result[0]['amount_paid'];
			$client_results[0]['amount_paid'] = number_format($amount_paid,2,'.',',');

			$client_results[0]['taxt'] = $client_channel_result[0]['taxt'];
			//付款日期
			//$client_results[0]['paid_tm'] = $client_channel_result[0]['paid_tm'];
			$client_results[0]['paid_tm'] = implode(',',$paid_tm);
		
			$this -> assign('client_result',$client_results[0]);
		}else{
			$client_channel_result = $client_model -> table('co_channel_check') -> where(array('client_id' => $client_id,'month' => $month)) -> select();
			$client_result[0]['account_attr'] = $client_channel_result[0]['account_attr'];
			$this -> assign('client_result',$client_result[0]);
		}
		$this -> assign('status',$status);
		
		
		$this -> display();
		
		
	}
	
	function upload_file(){
		$client_model = D('Channel_cooperation.channel_cooperation_p');
		$id = $_GET['id'];
	
		$result = $client_model -> table('co_affix') -> where(array('id' => $id)) -> select();
		$file = $result[0]['affix_url'];
		$file_name = $result[0]['affix_name'];
		if($file_name){
			if(strpos($_SERVER["HTTP_USER_AGENT"],"MSIE")){  
				$file_names = urlencode($file_name); 
			}else{
				$file_names = $file_name;
			}
		}
		if(!file_exists($file)){
			$this -> error("文件不存在");
		}else{
			$open_file = fopen($file,"r");
			Header("Content-type: application/octet-stream");
			Header("Accept-Ranges: bytes");
			Header("Accept-Length: ".filesize($file));
			Header("Content-Disposition: attachment; filename=" . $file_names);
			// 输出文件内容
			echo fread($open_file,filesize($file));
			fclose($file);
			exit();
		}
	}
	
	function change_freeze(){
		$client_model = D('Channel_cooperation.channel_cooperation_p');
		$chname = $_GET['chname'];
		$client_name = $_GET['client_name'];
		$p = $_GET['p'];
		$lr = $_GET['lr'];
		$start_tm = $_GET['start_tm'];
		$end_tm = $_GET['end_tm'];
		$client_id = $_GET['client_id'];
		$month = $_GET['month'];
		$my_status = $_GET['my_status'];
		$my_cid = $_GET['my_cid'];
		$my_cid_str = substr(trim($my_cid),0,-1);
		if($chname){
			$where_go .= "/chname/{$chname}";
		}
		if($client_name){
			$where_go .= "/client_name/{$client_name}";
		}
		if($p){
			$where_go .= "/p/{$p}";
		}
		if($lr){
			$where_go .= "/lr/{$lr}";
		}
		if($start_tm){
			$where_go .= "/start_tm/{$start_tm}";
		}
		if($end_tm){
			$where_go .= "/end_tm/{$end_tm}";
		}
		if($my_status){
			$where_go .= "/status/{$my_status}";
		}
		if($_GET['change'] == 5){
			$change = 0;
			$where['_string'] = "account_attr<>4 and client_id = {$client_id} and month = {$month} and status = 3";
			$data = array(
				'comment' => $_GET['comment'],
				'status' => $change,
				'update_tm' => time()
			);
		}else{
			$where['_string'] = "account_attr<>4 and client_id = {$client_id} and month = {$month} and status = 0";
			$change = $_GET['change'];
			$data = array(
				'status' => $change,
				'update_tm' => time()
			);
		}

		if(!empty($my_cid_str)){
			$where['_string'] .= " and id in ({$my_cid_str})";
		}
		$log_result = $this -> logcheck(array('client_id' => $client_id,'month' => $month),'co_channel_check',$data,$client_model);

		$result = $client_model -> table('co_channel_check') -> where($where) -> save($data);
		if($result){
			$this -> writelog("已修改财务结算客户id为{$client_id}，月份为{$month}的记录".$log_result, 'co_channel_check', $client_id,__ACTION__ ,'','edit');
			if($_GET['from']==99){
				$this -> assign('jumpUrl','/index.php/Channel_cooperation/Channelsettle_p/forum_settle'.$where_go);
			} else {
				$this -> assign('jumpUrl','/index.php/Channel_cooperation/Channelsettle_p/financial_settle'.$where_go);
			}
			$this -> success("编辑成功");
		}else{
			$this -> error("编辑失败");
		}
	}
	
	function batch_entering_show(){
		$client_model = D('Channel_cooperation.channel_cooperation_p');
		$chname = $_GET['chname'];
		$client_name = $_GET['client_name'];
		$p = $_GET['p'];
		$lr = $_GET['lr'];
		$start_tm = $_GET['start_tm'];
		$end_tm = $_GET['end_tm'];
		$my_status = $_GET['my_status'];
		
		$my_client = $_GET['my_client'];
		$my_client = rtrim($my_client, ',');
		$client_arr = explode(',',$my_client);
		print_r($client_arr);exit;
		foreach($client_arr as $key => $val){
			if($val){
				$client_month = explode('_',$val);
				$client_id = $client_month[0];
				$month = $client_month[1];
				$month_arr[] = $client_month[1];
				$amount_result = $client_model -> table('co_channel_check') -> where(array('client_id' => $client_id,'month' => $month,'status' => 3)) -> field('sum(amount_pay) as amount_pay') -> select();
			
				$all_amount_arr[] = $amount_result[0]['amount_pay'];
			}
		}
		foreach($all_amount_arr as $key => $val){
			$my_amount_str_go .= $val.',';
		}
		$my_amount_str = substr($my_amount_str_go,0,-1);
		$all_amount = array_sum($all_amount_arr);
		$this -> assign('my_amount_str',$my_amount_str);
		$this -> assign('my_client',$my_client);
		$this -> assign('all_amount',$all_amount);
		$this -> assign('chname',$chname);
		$this -> assign('client_name',$client_name);
		$this -> assign('start_tm',$_GET['start_tm']);
		$this -> assign('end_tm',$_GET['end_tm']);
		$this -> assign('p',$p);
		$this -> assign('my_status',$my_status);
		$this -> assign('lr',$lr);
		$this -> display();
	}
	
	function batch_entering(){
	
		$chname = $_GET['chname'];
		$client_name = $_GET['client_name'];
		$p = $_GET['p'];
		$lr = $_GET['lr'];
		$start_tm = $_GET['start_tm'];
		$end_tm = $_GET['end_tm'];
		$my_status = $_GET['my_status'];
		$client_model = D('Channel_cooperation.channel_cooperation_p');
		$my_client = $_GET['my_client'];

		$invoice_tm = strtotime(date('Ymd 23:59:59',strtotime($_GET['invoice_tm'])));
		$paid_tm = strtotime(date('Ymd 23:59:59',strtotime($_GET['paid_tm'])));
		$my_time_result = $client_model -> table('co_channel_check') -> where(array('client_id' => $my_client,'month' => $_GET['month'])) -> select();

		if($invoice_tm < $my_time_result[0]['create_tm']){
			$this -> error("收票时间不能小于报表生成时间");
		}
		if($paid_tm < $my_time_result[0]['create_tm']){
			$this -> error("付款时间不能小于报表生成时间");
		}
		$client_arr = explode(',',$my_client);
		$client_arrs = array();
		foreach($client_arr as $key => $val){
			if($val){
				$client_month = explode('_',$val);
				$client_id[] = $client_month[0];
				$client_months = $client_month[1];
				$client_arrs[] = $val;
			}
		}
		
		$client_id = array_unique($client_id);
		if(count($client_id) > 1){
			$this -> error("不可选择不同客户批量录入");
		}
		foreach($client_months as $k => $v){
			$client_attr_result = $client_model -> table('co_channel_check') -> where(array('client_id' => $client_id,'month' => $v)) -> select();
			if($client_attr_result[0]['account_attr'] == 1){
				if(!$_GET['paid_tm']){
					$this -> error("请选择收票时间和付款时间");
				}
			}
		}
		if(!$_GET['paid_tm']){
			$this -> error("请选择付款时间");
		}
		
		$my_amount_str = $_GET['my_amount_str'];
		$my_amount_arr = explode(',',$my_amount_str);
	
		foreach($client_arrs as $key => $val){
			if($val){
				$client_month = explode('_',$val);
				$client_id = $client_month[0];
				if($client_month[1]){
					$month = $client_month[1];
				}else{
					$month = $_GET['month'];
				}
				$where['client_id'] = $client_id;
				$where['month'] = $month;
				$where['status'] = 3;
				$client_result = $client_model -> table('co_client_list') -> where(array('id' => $client_id)) -> select();
				$affix_result = $client_model -> table('co_affix') -> where(array('client_id' => $client_id)) -> select();
				foreach($affix_result as  $k => $v){
					$my_file_str .= $v['id'].',';
				}
				$my_file = substr($my_file_str,0,-1);
				if(!$my_file){
					$my_file = '';
				}
				
				$data = array(
					'invoice_tm' => $invoice_tm,
					'paid_tm' => $paid_tm,
					'status' => 2,
					'bill_amount' => $my_amount_arr[$key],
					'amount_paid' => $my_amount_arr[$key],
					'update_tm' => time(),
					'account_attr' => $client_result[0]['account_attr'],
					'company_name' => $client_result[0]['company_name'],
					'account_gathering' => $client_result[0]['account_gathering'],
					'opening_bank' => $client_result[0]['opening_bank'],
					'bank_account' => $client_result[0]['bank_account'],
					'contract_num' => $client_result[0]['contract_num'],
					'my_file' => $my_file,
					'bath_tm' => time()
				);
				$log_result = $this -> logcheck(array('client_id' => $client_id,'month' => $month),'co_channel_check',$data,$client_model);
				$result = $client_model -> table('co_channel_check') -> where($where) -> save($data);
				
				if($result){
					$this -> writelog("已编辑客户id为{$client_id},月份为{$month}的财务结算".$log_result, 'co_channel_check', $client_id,__ACTION__ ,'','edit');
				}
				$results[] = $result;
			}
		}
		
		if($chname){
			$where_go .= "/chname/{$chname}";
		}
		if($client_name){
			$where_go .= "/client_name/{$client_name}";
		}
		if($p){
			$where_go .= "/p/{$p}";
		}
		if($lr){
			$where_go .= "/lr/{$lr}";
		}
		if($start_tm){
			$where_go .= "/start_tm/{$start_tm}";
		}
		if($end_tm){
			$where_go .= "/end_tm/{$end_tm}";
		}
		if($my_status){
			$where_go .= "/status/{$my_status}";
		}
		
		if($results){
			$this -> assign('jumpUrl','/index.php/Channel_cooperation/Channelsettle_p/financial_settle'.$where_go);
			$this -> success("编辑成功");
		}else{
			$this -> error("编辑失败");
		}
	}
	
	// 导出申请单接口
	function drive_cheque(){
		ini_set('memory_limit','512M');
		include (dirname(__FILE__).'/../../ORG/PHPExcel/PHPExcel.php');
		$objPHPExcel = new PHPExcel();
		$model = new Model();
		//error_reporting(E_ALL);
		//ini_set('display_errors',true);
		//设置属性
		$objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
		->setLastModifiedBy("Maarten Balliauw")
		->setTitle("Office 2007 XLSX Test Document")
		->setSubject("Office 2007 XLSX Test Document")
		->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
		->setKeywords("office 2007 openxml php")
		->setCategory("Test result file");
		
		$client_model = D('Channel_cooperation.channel_cooperation_p');
		$coefficient_model = D('Channel_cooperation.channel_coefficient_p');
		$my_client = $_GET['my_client']; //客户id_month_zhuti,客户id_month_zhuti,客户id_month_zhuti,
		$my_cid = $_GET['my_cid']; //客户id_cid_month,客户id_cid_month,客户id_cid_month,
		$my_cid_str = substr($my_cid,0,-1); //客户id_cid_month,客户id_cid_month,客户id_cid_month
		$my_client = rtrim($my_client,','); //客户id_month_zhuti,客户id_month_zhuti,客户id_month_zhuti
		$my_cid_arr_go = explode(',',$my_cid_str); //array(client_cid_month,client_cid_month)
		//echo count($my_cid_arr_go);exit;
		$my_client_arr = explode(',',$my_client);
		$client_month_arr = array();
		foreach($my_client_arr as $val){
			$val_arr = explode('_', $val);
			if($val_arr[2]){
				unset($val_arr[2]);
			}
			$val_str = implode('_', $val_arr);
			$client_month_arr[] = $val_str; //array(客户id_month,客户id_month,客户id_month)
		}
		foreach($my_client_arr as $key => $val){
			if($val){
				$my_client_arrs[] = $val;
			}
		}
		
		foreach($my_cid_arr_go as $key => $val){
			$my_cid_arr_go_be = explode('_',$val);
			$my_cid_arr_client_go = $my_cid_arr_go_be[0].'_'.$my_cid_arr_go_be[2];
			
			if(in_array($my_cid_arr_client_go,$client_month_arr)){
				$my_cid_arr[] = explode('_',$val); //只保留序号、渠道同时选中的渠道value值
			}
		}

		foreach($my_cid_arr as $key => $val){
			$month_arr[] = $val[2];
			$cm = $val[0].'_'.$val[2];
			$mc = $val[2].'_'.$val[0];
			$cid_arr[$cm][$mc][] = $val[1]; //$cid_arr['client_month']['month_client'][] = cid;
	
		}
		foreach($cid_arr as $key => $val){
			foreach($val as $k => $v){
				$cid_str = '';
				foreach($v as $m => $n){
					$cid_str .= $n.',';
				}
				$val[$k]['count'] = count($v);
				$val[$k]['cid'] = $cid_str;
			}
			$cid_arr[$key] = $val;
		}
		
		//$start_tm = strtotime($month.'01');
		//$end_tm = strtotime(date('Ymt',strtotime($month.'01')));
		//$my_month = date('m月',$start_tm);
		$idx = 0;
		foreach($cid_arr as $k => $v){
			$all_pay = array();
			$channel_pay = '';
			foreach($v as $m => $n){
				$month_client_arr = explode('_', $m);
				$k = $month_client_arr[1];
				$m = $month_client_arr[0];
				$my_cid_str = substr($n['cid'],0,-1);
				$client_channel_where['_string'] = "account_attr<>4 and client_id = {$k} and month = {$m} and cid in ({$my_cid_str})";
				$client_channel_result = $client_model -> table('co_channel_check') -> where($client_channel_where) -> field('settle_entity,amount_pay,sum(amount_pay)')  -> select();
				if ($client_channel_result[0]['settle_entity'] == '2') {
					$all_pay[] = $client_channel_result[0]['amount_pay'];
				} else {
					$all_pay[] = $client_channel_result[0]['sum(amount_pay)'];
				}
				$client_channel_result[0]['sum(amount_pay)'] = number_format($client_channel_result[0]['sum(amount_pay)'],2,'.',',');
				$client_channel_result[0]['amount_pay'] = number_format($client_channel_result[0]['amount_pay'],2,'.',',');
				$my_month_go = date('m',strtotime($m.'01'));
				if ($client_channel_result[0]['settle_entity'] == '2') {
					$channel_pay .= "{$my_month_go}月/{$client_channel_result[0]['amount_pay']}元/{$n['count']}个;";
				} else {
					$channel_pay .= "{$my_month_go}月/{$client_channel_result[0]['sum(amount_pay)']}元/{$n['count']}个;";
				}
				$v['channel_pay'] = $channel_pay;
			}
			
			//$all_activation = array_sum($all_activation_arr);
			$all_account = array_sum($all_pay);
			//$all_cid = count($all_cid_arr);
			$client_result = $client_model -> table('co_client_list') -> where(array('id' => $k)) -> select();
			$charge_result = $client_model -> table('co_charge') -> where(array('id' => $client_result[0]['charge_id'])) -> select();
			
			$last_time = date('Y/m/d',time() + 86400*5);
			$type_account = $this -> cny($all_account);
			$from = $_GET['from'];
			$all_account = number_format($all_account,2,'.',',');
			if($from == 1){
				$sheet1 = $objPHPExcel->createSheet();
				$objPHPExcel->setActiveSheetIndex($idx)
				->setCellValue('A1', '支票/电汇申请单                                      凭证编号:')
				->setCellValue('E1', '凭证编号：')
				->setCellValue('A2', '付款公司名称：北京力天无限网络技术有限公司')
				->setCellValue('C2', "申请人：{$charge_result[0]['charge_name']}")
				->setCellValue('A4', "收款账户：{$client_result[0]['account_gathering']}")
				->setCellValue('C4', "币种：人民币")
				->setCellValue('E4', "付款目的：付费推广")
				->setCellValue('A5', "开户银行：{$client_result[0]['opening_bank']}")
				->setCellValue('C5', "金额小写：{$all_account}")
				->setCellValue('E5', "客户名称：{$client_result[0]['client_name']}")
				->setCellValue('A6', "银行账户：{$client_result[0]['bank_account']}")
				->setCellValue('C6', "金额大写：{$type_account}")
				->setCellValue('E6', "结算月份/金额/渠道数量：\r\n{$v['channel_pay']}")
				->setCellValue('A7', "付款方式：电汇（√） 支票（）")
				->setCellValue('C7', "最后付款期限：{$last_time}")
				->setCellValue('A9', "申请人签字：\r\n")
				->setCellValue('A9', "申请人签字：\r\n")
				->setCellValue('C9', "部门主管签字：\r\n\r\n\r\n\r\n日期：")
				->setCellValue('E9', "事业部总经理签字：\r\n\r\n\r\n\r\n日期：")
				->setCellValue('A11', "财务审核签字：\r\n\r\n\r\n\r\n日期：")
				->setCellValue('C11', "财务负责人签字：\r\n\r\n\r\n\r\n日期：")
				->setCellValue('E11', "总经理签字：\r\n\r\n\r\n\r\n日期：");
			}
			$objPHPExcel->getActiveSheet()->getStyle( 'A9')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
			$objPHPExcel->getActiveSheet()->getStyle( 'C9')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
			$objPHPExcel->getActiveSheet()->getStyle( 'E9')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
			$objPHPExcel->getActiveSheet()->getStyle( 'A11')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
			$objPHPExcel->getActiveSheet()->getStyle( 'C11')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
			$objPHPExcel->getActiveSheet()->getStyle( 'E11')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP); 
			$objPHPExcel->getActiveSheet()->getStyle('A7')->getAlignment()->setWrapText(true);
			$objPHPExcel->getActiveSheet()->getStyle('C7')->getAlignment()->setWrapText(true);
			$objPHPExcel->getActiveSheet()->getStyle('E7')->getAlignment()->setWrapText(true);
			$objPHPExcel->getActiveSheet()->getStyle('A9')->getAlignment()->setWrapText(true);
			$objPHPExcel->getActiveSheet()->getStyle('C9')->getAlignment()->setWrapText(true);
			$objPHPExcel->getActiveSheet()->getStyle('E9')->getAlignment()->setWrapText(true);
			$objPHPExcel->getActiveSheet()->getStyle('A11')->getAlignment()->setWrapText(true);
			$objPHPExcel->getActiveSheet()->getStyle('C11')->getAlignment()->setWrapText(true);
			$objPHPExcel->getActiveSheet()->getStyle('E11')->getAlignment()->setWrapText(true);
			$objPHPExcel->getActiveSheet()->getStyle('C6')->getAlignment()->setWrapText(true);
			$objPHPExcel->getActiveSheet()->getStyle('A4')->getAlignment()->setWrapText(true);
			$objPHPExcel->getActiveSheet()->getStyle('A2')->getAlignment()->setWrapText(true);
			$objPHPExcel->getActiveSheet()->getStyle('A5')->getAlignment()->setWrapText(true);
			$objPHPExcel->getActiveSheet()->getStyle('E5')->getAlignment()->setWrapText(true);
			$objPHPExcel->getActiveSheet()->getStyle('A6')->getAlignment()->setWrapText(true);
			$objPHPExcel->getActiveSheet()->getStyle('E4')->getAlignment()->setWrapText(true);
			
			$objPHPExcel->getActiveSheet()->mergeCells('A1:F1');
			$objPHPExcel->getActiveSheet()->mergeCells('A2:B2');
			$objPHPExcel->getActiveSheet()->mergeCells('C2:F2');
			$objPHPExcel->getActiveSheet()->mergeCells('E6:F7');
			$objPHPExcel->getActiveSheet()->mergeCells('A9:B10');
			$objPHPExcel->getActiveSheet()->mergeCells('C9:D10');
			$objPHPExcel->getActiveSheet()->mergeCells('E9:F10');
			$objPHPExcel->getActiveSheet()->mergeCells('A11:B12');
			$objPHPExcel->getActiveSheet()->mergeCells('C11:D12');
			$objPHPExcel->getActiveSheet()->mergeCells('E11:F12');
			$objPHPExcel->getActiveSheet()->mergeCells('A8:F8');
			$objPHPExcel->getActiveSheet()->mergeCells('A3:F3');
			$objPHPExcel->getActiveSheet()->mergeCells('C3:F3');
			$objPHPExcel->getActiveSheet()->mergeCells('A4:B4');
			$objPHPExcel->getActiveSheet()->mergeCells('C4:D4');
			$objPHPExcel->getActiveSheet()->mergeCells('E4:F4');
			$objPHPExcel->getActiveSheet()->mergeCells('A5:B5');
			$objPHPExcel->getActiveSheet()->mergeCells('C5:D5');
			$objPHPExcel->getActiveSheet()->mergeCells('E5:F5');
			$objPHPExcel->getActiveSheet()->mergeCells('A6:B6');
			$objPHPExcel->getActiveSheet()->mergeCells('C6:D6');
			$objPHPExcel->getActiveSheet()->mergeCells('A7:B7');
			$objPHPExcel->getActiveSheet()->mergeCells('C7:D7');
			$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(25);
			$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
			$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
			$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(25);
			$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(25);
			$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(25);
			$objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setSize(16);
			//设置行高
			$objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(36.75);
			$objPHPExcel->getActiveSheet()->getRowDimension('2')->setRowHeight(36.75);
			$objPHPExcel->getActiveSheet()->getRowDimension('3')->setRowHeight(36.75);
			$objPHPExcel->getActiveSheet()->getRowDimension('4')->setRowHeight(36.75);
			$objPHPExcel->getActiveSheet()->getRowDimension('5')->setRowHeight(36.75);
			$objPHPExcel->getActiveSheet()->getRowDimension('6')->setRowHeight(36.75);
			$objPHPExcel->getActiveSheet()->getRowDimension('7')->setRowHeight(36.75);
			$objPHPExcel->getActiveSheet()->getRowDimension('8')->setRowHeight(36.75);
			$objPHPExcel->getActiveSheet()->getRowDimension('9')->setRowHeight(36.75);
			$objPHPExcel->getActiveSheet()->getRowDimension('10')->setRowHeight(36.75);
			$objPHPExcel->getActiveSheet()->getRowDimension('11')->setRowHeight(36.75);
			$objPHPExcel->getActiveSheet()->getRowDimension('12')->setRowHeight(36.75);
			//设置列宽
			$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(21.29);
			$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(25.14);
			$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(19);
			$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(22.43);
			$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(19);
			$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(19);
			
			$objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
			$objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
			//设置边框
			$objPHPExcel->getActiveSheet()->getStyle('A2')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A2')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A2')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B2')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B2')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B2')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('C2')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('C2')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			
			$objPHPExcel->getActiveSheet()->getStyle('D2')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('D2')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			
			$objPHPExcel->getActiveSheet()->getStyle('E2')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('E2')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('F2')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('F2')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('F2')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A4')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A4')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B4')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A4')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('C4')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('D4')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B4')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B4')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('C4')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('C4')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('D4')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('D4')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('E4')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('E4')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('E4')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('F4')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('F4')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('F4')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A5')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A5')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B5')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B5')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('C5')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('D5')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('D5')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('E5')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('F5')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('F5')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A6')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A6')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B6')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B6')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('C6')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('D6')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('D6')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('F6')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A7')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A7')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B7')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B7')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('C7')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('D7')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('D7')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('E7')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('F7')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('F7')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A9')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A9')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B9')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A10')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B10')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B10')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B9')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('C10')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('C9')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('D10')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('D10')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('D9')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('D9')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('E10')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('E9')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('F10')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('F10')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('F9')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('F9')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A11')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B11')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('D11')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('F11')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A11')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A11')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B11')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B11')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('C11')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('D11')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('D11')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('E11')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('F11')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('F12')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B12')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('D12')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A12')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B12')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('C12')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('D12')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('E12')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('F12')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			
			

			
			$range1 = "A8:B8";
			$range2 = "E6:F6";
			$range3 = "C8:D8";
			$range5 = "C10:D10";
			$range4 = "E8:F8";
			$objPHPExcel->getActiveSheet()->getStyle($range4)-> getAlignment()->setWrapText(true);
			$objPHPExcel->getActiveSheet()->getStyle($range5)-> getAlignment()->setWrapText(true);
			$objPHPExcel->getActiveSheet()->getStyle($range1)-> getAlignment()->setWrapText(true);
			$objPHPExcel->getActiveSheet()->getStyle($range2)-> getAlignment()->setWrapText(true);
			$objPHPExcel->getActiveSheet()->setTitle($this->convertUTF8('运营审核申请单'));
			$idx ++;
		}
		if(count($my_cid_arr) > 1 && count($my_client_arrs) < 2){
			$client_id = explode('_',$my_client_arrs[0]);
			$client_name_result = $client_model -> table('co_client_list') -> where(array('id' => $client_id[0])) -> select();
			$excel_title = $client_name_result[0]['client_name'].'系列_'.$client_id[1].'_打款申请单';
		}elseif(count($my_cid_arr) < 2 && count($my_client_arrs) < 2){
		
			$cid_result = $model -> table('sj_channel') -> where(array('cid' => $my_cid_arr[0][1])) -> select();
			$excel_title = $cid_result[0]['chname'].'_'.$my_cid_arr[0][2].'_打款申请单';
		}else{
			$excel_title = '运营审核申请单' . date("Y-m-d",time());
		}
			
		header ( 'Content-Type: application/vnd.ms-excel' );
		$ua = $_SERVER["HTTP_USER_AGENT"];
		if (preg_match("/MSIE/", $ua)) {  
			header('Content-Disposition: attachment; filename="' . urlencode($excel_title) . '.xls"');
		} else if (preg_match("/Firefox/", $ua)) {  
			header('Content-Disposition: attachment; filename="' . $excel_title . '.xls"');
		} else {  
			header('Content-Disposition: attachment; filename="'.$excel_title.'.xls"');
		}	
		header('Cache-Control: max-age=0');
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
	}
	
	public function convertUTF8($str){
		if(empty($str)) return '';
		if(mb_check_encoding($str,"utf-8") != true){
			return iconv("gbk","utf-8", $str);
		}else{
			return $str;
		}
	}
	
	// 导出对账单窗口
	function drive_settle_show(){
		$chname = $_GET['chname'];
		$client_name = $_GET['client_name'];
		$p = $_GET['p'];
		$lr = $_GET['lr'];
		$start_tm = $_GET['start_tm'];
		$end_tm = $_GET['end_tm'];
		$my_client = $_GET['my_client'];
		$co_group = $_GET['co_group'];
		$billing = $_GET['billing'];
		$the_client = substr($my_client,0,-1);
		$client_arr = explode(',',$the_client);
		$yes = ''; 
		foreach($client_arr as $val){
			$v = substr($val, -1);
			if($v == '2'){
				$yes = 1; //存在按客户结算的渠道，禁用按渠道导出明细
				break;
			}
		}
		$client_count = count($client_arr);
		$my_cid = $_GET['my_cid'];
		$this -> assign('yes', $yes);
		$this -> assign('client_count',$client_count);
		$this -> assign('chname',$chname);
		$this -> assign('client_name',$client_name);
		$this -> assign('co_group',$co_group);
		$this -> assign('billing',$billing);
		$this -> assign('start_tm',$_GET['start_tm']);
		$this -> assign('end_tm',$_GET['end_tm']);
		$this -> assign('p',$p);
		$this -> assign('lr',$lr);
		$this -> assign('my_client',$my_client);
		$this -> assign('my_cid',$my_cid);
		$this -> display();
	}
	
	// 导出对账单接口
	function drive_settle(){
		ini_set('memory_limit','512M');
		$coefficient_model = D('Channel_cooperation.channel_coefficient_p');
		$model = new Model();
		$client_model = D('Channel_cooperation.channel_cooperation_p');
		include (dirname(__FILE__).'/../../ORG/PHPExcel/PHPExcel.php');
		$objPHPExcel = new PHPExcel();
		//设置属性
		$objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
		->setLastModifiedBy("Maarten Balliauw")
		->setTitle("Office 2007 XLSX Test Document")
		->setSubject("Office 2007 XLSX Test Document")
		->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
		->setKeywords("office 2007 openxml php")
		->setCategory("Test result file");
		
		
		$drive_type = $_POST['drive_type'];
		
		$my_client = $_POST['my_client']; //客户id_month_主体
		$my_cid = $_POST['my_cid']; //客户id_cid_month
		$chname = $_POST['chname'];
		$client_name = $_POST['client_name'];
		$co_group = $_GET['co_group'];
		$billing = $_GET['billing'];
		$p = $_POST['p'];
		$lr = $_POST['lr'];
		$start_tm = $_POST['start_tm'];
		$end_tm = $_POST['end_tm'];
		if($chname){
			$where_go .= "/chname/{$chname}";
		}
		if($client_name){
			$where_go .= "/client_name/{$client_name}";
		}
		if(isset($co_group)){
			$where_go .= "/co_group/{$co_group}";
		}
		if($billing){
			$where_go .="/billing/{$billing}";
		}
		if($p){
			$where_go .= "/p/{$p}";
		}
		if($lr){
			$where_go .= "/lr/{$lr}";
		}
		if($start_tm){
			$where_go .= "/start_tm/{$start_tm}";
		}
		if($end_tm){
			$where_go .= "/end_tm/{$end_tm}";
		}
		$my_client_arr = explode(',',substr($my_client,0,-1));
		$my_cid_arr = explode(',',substr($my_cid,0,-1));
	
		if(count($my_client_arr) < 2 && count($my_cid_arr) > 1){
			$client_id = explode('_',$my_client_arr[0]);
			$client_name_result = $client_model -> table('co_client_list') -> where(array('id' => $client_id[0])) -> select();
			$excel_title = $client_name_result[0]['client_name'].'系列_'.$client_id[1].'_对账单';
		}elseif(count($my_client_arr) < 2 && count($my_cid_arr) < 2){
			$cid_ex = explode('_',$my_cid_arr[0]);
			$cid_result = $model -> table('sj_channel') -> where(array('cid' => $cid_ex[1])) -> select();
			$excel_title = $cid_result[0]['chname'].'_'.$cid_ex[2].'_对账单';
		}else{
			$excel_title = '运营审核对账单'.date("Y-m-d",time());
		}
		header ( 'Content-Type: application/vnd.ms-excel;charset=utf-8' );
		$ua = $_SERVER["HTTP_USER_AGENT"];
		if (preg_match("/MSIE/", $ua)) {  
			header('Content-Disposition: attachment; filename="' . urlencode($excel_title) . '.xls"');
		} else if (preg_match("/Firefox/", $ua)) {  
		header('Content-Disposition: attachment; filename="' . $excel_title . '.xls"');
		} else {  
			header('Content-Disposition: attachment; filename="'.$excel_title.'.xls"');
		}	
		
		header('Cache-Control: max-age=0');
		
		foreach ($drive_type as $v) {
            if ($v == 1) {
                $client_str_go = substr($my_client,0,-1);
				$client_arr_go = explode(',',$client_str_go);
				$client_arr = array();
				$client_month_arr = array();
				foreach($client_arr_go as $key => $val ){
					$client_arr_to = explode('_',$val);
					if($client_arr_to[1]){
						$client_month_arr[] = $client_arr_to[1];
					}
					if($client_arr_to[2]){ //加入结算主体，1：按渠道 2：按客户
						$client_arr[$key]['settle_entity'] = $client_arr_to[2];
					}
					if($client_arr_to[0]){
						$client_arr[$key]['client_id'] = $client_arr_to[0];
						$cid_str_go = substr($my_cid,0,-1);
						$cid_arr_go = explode(',',$cid_str_go);
						$cid_arr = array();
						foreach($cid_arr_go as $k => $v){
							$cid_arr_to = explode('_',$v);
							if($cid_arr_to[2] == $client_arr_to[1] && $client_arr_to[0] == $cid_arr_to[0]){
								$cid_arr[$k] = $cid_arr_to[1];
							}
						}
						$client_arr[$key]['cid'] = $cid_arr;
					}
				}
				foreach($cid_arr_go as $key => $val){
					$cid_arr_to = explode('_',$val);
					if($cid_arr_to[0]){
						$cid_arr[] = $cid_arr_to[1];
					}
				}
				$this -> my_client_settle($objPHPExcel,$client_arr,$client_month_arr);
			}elseif($v == 2){
				$cid_str_go = substr($my_cid,0,-1);
				$cid_arr_go = explode(',',$cid_str_go);
				$cid_arr = array();
				$cid_month_arr = array();
				foreach($cid_arr_go as $key => $val){
					$cid_arr_to = explode('_',$val);
					if($cid_arr_to[1]){
						$cid_arr[] = $cid_arr_to[1];
						$cid_month_arr[] = $cid_arr_to[2];
					}
				}
				$count = count($client_arr);
				$this -> my_cid_settle($objPHPExcel,$cid_arr,$cid_month_arr,$count);
			}
		}

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
	}
	
	// 按客户导出汇总
	function my_client_settle(&$objPHPExcel,$client_arr,$client_month_arr){
		$client_model = D('Channel_cooperation.channel_cooperation_p');
		$coefficient_model = D('Channel_cooperation.channel_coefficient_p');
		$model = new Model();
		
		foreach($client_arr as $m => $n){
			if ($n['settle_entity'] == '2') {
				$client_id = $n['client_id'];
				$month = $client_month_arr[$m];
				$my_cid_arr = $n['cid'];
				$cid_str = implode(',', $my_cid_arr);; //cid,cid,cid
				$zhouqi_start_tm = date('Y.m.d',strtotime($month.'01'));
				$zhouqi_end_tm = date('Y.m.t',strtotime($month.'01'));
				$zhouqi_tm = $zhouqi_start_tm.'-'.$zhouqi_end_tm;
				$client_channel_where['_string'] = "account_attr<>4 and client_id = {$client_id} and month = {$month} and cid in ({$cid_str})"; 
				$client_channel_result = $client_model -> table('co_channel_check') -> where($client_channel_where) ->order('amount_pay desc')-> select();
				$client_name_result = $client_model -> table('co_client_list') -> where(array('id' => $client_id)) -> select(); //用于关联客户名称
				$show_taxt = false; //是否显示税率
				$my_channel_result = array();
				foreach($client_channel_result as $key => $val){
					$channel_result = $model -> table('sj_channel') -> where(array('cid' => $val['cid'])) -> select();
					$price_result = $client_model -> table('co_channel_check') -> where(array('cid' => $val['cid'],'account_attr'=>array('neq', '4'),'month' => $month))-> select();
					$val['chname'] = $channel_result[0]['chname']; //渠道名称
					$val['price'] = $price_result[0]['price']; //单价
					$val['price'] = number_format($val['price'],2,'.',',');
					$val['activation_sum'] = number_format($val['activation_sum']);
					$val['taxt'] = number_format($val['taxt'],2,'.',',');
					$val['cny_money'] = $this -> cny($val['amount_pay']);
					$val['amount_pay'] = number_format($val['amount_pay'],2,'.',',');
					if($val['taxt']>0){
						$show_taxt = true;
					}
					$my_channel_result[] = $val;
				}
				unset($client_channel_result);
				$sheet1 = $objPHPExcel->createSheet();
				// 第一、二、三行
				$objPHPExcel->setActiveSheetIndex($m)
				->setCellValue('A1', '安智市场对账单')
				->setCellValue('A2', '客户名称')
				->setCellValue('B2', '渠道名称')
				->setCellValue('C2', '结算周期')
				->setCellValue('D2', '合计激活')
				->setCellValue('E2', '单价(元)')
				->setCellValue('A3', "{$client_name_result[0]['client_name']}")
				->setCellValue('B3', "{$my_channel_result[0]['chname']}")
				->setCellValue('C3', "{$zhouqi_tm}")
				->setCellValue('D3', "{$my_channel_result[0]['activation_sum']}")
				->setCellValue('E3', "{$my_channel_result[0]['price']}");
				if($show_taxt){
					$objPHPExcel->setActiveSheetIndex($m)
					->setCellValue('F2', "税点")
					->setCellValue('G2', "结算金额(元)")
					->setCellValue('F3', "{$my_channel_result[0]['taxt']}")
					->setCellValue('G3', "{$my_channel_result[0]['amount_pay']}");
				}else{
					$objPHPExcel->setActiveSheetIndex($m)
					->setCellValue('F2', "结算金额(元)")
					->setCellValue('F3', "{$my_channel_result[0]['amount_pay']}");
				}

				//第四行(如果有)
				for($i=1;$i<count($my_channel_result);$i++){
					$objPHPExcel->setActiveSheetIndex($m)
					->setCellValue('A'.($i+3).'',"{$client_name_result[0]['client_name']}")
					->setCellValue('B'.($i+3).'',"{$my_channel_result[$i]['chname']}");
				}

				//填充合计激活和金额
				$objPHPExcel->setActiveSheetIndex($m)
				->setCellValue('A'.(count($my_channel_result) + 3).'', "合计")
				->setCellValue('D'.(count($my_channel_result) + 3).'', "{$my_channel_result[0]['activation_sum']}");
				if ($show_taxt) {
					$objPHPExcel->setActiveSheetIndex($m)
				    ->setCellValue('G'.(count($my_channel_result) + 3).'', "{$my_channel_result[0]['amount_pay']}");
				} else {
					$objPHPExcel->setActiveSheetIndex($m)
				    ->setCellValue('F'.(count($my_channel_result) + 3).'', "{$my_channel_result[0]['amount_pay']}");
				}

				//最后一行
				$objPHPExcel->setActiveSheetIndex($m)
				->setCellValue('A'.(count($my_channel_result) + 5).'', "结算金额合计:")
				->setCellValue('B'.(count($my_channel_result) + 5).'',"{$my_channel_result[0]['amount_pay']}")
				->setCellValue('C'.(count($my_channel_result) + 5).'', "人民币(大写)：{$my_channel_result[0]['cny_money']}");

				$objPHPExcel->getActiveSheet()->setTitle($this->convertUTF8("运营审核客户{$client_name_result[0]['client_name']}对账单"));

				//合并单元格
				if($show_taxt){
					$objPHPExcel->getActiveSheet()->mergeCells('A1:G1');
					$objPHPExcel->getActiveSheet()->mergeCells("C".(count($my_channel_result)+5).":G".(count($my_channel_result)+5)."");
				}else{
					$objPHPExcel->getActiveSheet()->mergeCells('A1:F1');
					$objPHPExcel->getActiveSheet()->mergeCells("C".(count($my_channel_result)+5).":F".(count($my_channel_result)+5)."");
				}

				//设置宽度
				$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
				$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
				$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
				$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(10);
				$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(10);
				if($show_taxt){
					$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(10);
					$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
				}else{
					$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
				}

				//设置居中
				$objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$objPHPExcel->getActiveSheet()->getStyle('A'.(count($my_channel_result)+5))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$objPHPExcel->getActiveSheet()->getStyle('B'.(count($my_channel_result)+5))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				if($show_taxt){
					$col_max = 'G';
				}else{
					$col_max = 'F';
				}
				for($i='A';$i<=$col_max;$i++){
					for($j=2;$j<count($my_channel_result)+5;$j++){
						$objPHPExcel->getActiveSheet()->getStyle($i.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
						$objPHPExcel->getActiveSheet()->getStyle($i.$j)->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
						$objPHPExcel->getActiveSheet()->getStyle($i.$j)->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
					}
				}

				$objPHPExcel->getActiveSheet()->getStyle('A1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
				$objPHPExcel->getActiveSheet()->getStyle('A1')->getFill()->getStartColor()->setARGB('#458B00');

				//设置边框
				for($i='A';$i<=$col_max;$i++){
					for($j=1;$j<count($my_channel_result)+6;$j++){
						$objPHPExcel->getActiveSheet()->getStyle($i.$j)->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
						$objPHPExcel->getActiveSheet()->getStyle($i.$j)->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
					}
				}
				unset($my_channel_result);
			} else {
				$my_cid_str_go = '';
				$co_group_arr =  C('co_group');
				foreach($n['cid'] as $k => $v){
					$my_cid_str_go .= $v.',';
				}
				$my_cid = substr($my_cid_str_go,0,-1);
			
				$client_channel_where['_string'] = "account_attr<>4 and client_id = {$n['client_id']} and month = {$client_month_arr[$m]} and cid in ({$my_cid})";
				$client_channel_result = $client_model -> table('co_channel_check') -> where($client_channel_where) ->order('amount_pay desc')-> select();
			
				$start_tm = strtotime($client_month_arr[$m].'01');
				$end_tm = strtotime(date('Ymt',strtotime($client_month_arr[$m].'01')));
				$my_month = date('Y年m月',$start_tm);
				$client_name_result = $client_model -> table('co_client_list') -> where(array('id' => $n['client_id'])) -> select();
				$my_channel_result = array();
				$all_activation_arr = array();
				$all_account_arr = array();
				//是否显示税率
				$show_taxt = false;
				$show_billing = array();
				foreach($client_channel_result as $key => $val){
					$channel_result = $model -> table('sj_channel') -> where(array('cid' => $val['cid'])) -> select();				
					$price_result = $client_model -> table('co_channel_check') -> where(array('cid' => $val['cid'],'account_attr'=>array('neq', '4'),'month' => $client_month_arr[$m])) -> select();
					$activation_where['_string'] = "cid = {$val['cid']} and submit_tm >= {$start_tm} and submit_tm <= {$end_tm} and status = 1";

					$activation_count = $val['activation'];
					$amount_account = sprintf('%.2f',$activation_count*$price_result[0]['price']);
					$val['chname'] = $channel_result[0]['chname'];
					$show_billing[] = $channel_result[0]['billing'];
					$val['price'] = $price_result[0]['price'];
					$val['activation_count'] = $activation_count;
					$val['amount_account'] = $amount_account;
					$pay_account = $val['amount_account'] + $price_result[0]['salvation'];
					$val['no_amount_account'] = $pay_account;
					$val['pay_account'] = number_format($pay_account,2,'.',',');
					$val['activation_count'] = number_format($val['activation_count']);
					$val['amount_account'] = number_format($val['amount_account'],2,'.',',');
					$val['co_group'] = $co_group_arr[$channel_result[0]['co_group']];
					if($val['taxt']>0){
						$show_taxt = true;
					}
					$my_channel_result[] = $val;
					$all_activation_arr[] = $activation_count;
					$all_pay_account_arr[] = $pay_account;
					$all_account_arr[]= $pay_account;
				}
				unset($client_channel_result);
				//结算方式如果有不一样的则显示空，没有则直接显示
				$show_billing=array_unique($show_billing);
				if(count($show_billing)>1){
					$show_bill = '';
				}else{
					$bill_arr = array('1'=>'激活','2'=>'预装');
					$show_bill = $bill_arr[$show_billing[0]];
				}
				$all_activation = array_sum($all_activation_arr);
				$all_activation = number_format($all_activation);
				$all_account = $no_all_account = array_sum($all_account_arr);
				$all_pay_account = array_sum($all_pay_account_arr);
				$all_account = number_format($all_account,2,'.',',');
				$all_pay_account = number_format($all_pay_account,2,'.',',');
				$sheet1 = $objPHPExcel->createSheet();
				$objPHPExcel->setActiveSheetIndex($m)
				->setCellValue('A1', '安智市场对账单')
				->setCellValue('A2', '客户名称：')
				->setCellValue('B2', "{$client_name_result[0]['client_name']}")
				->setCellValue('A3', "月份：")
				->setCellValue('B3', "{$my_month}")
				->setCellValue('A4', "渠道名称")
				->setCellValue('B4', "渠道分类")
				->setCellValue('C4', "单价(元)")
				->setCellValue('D4', "合计激活")
				->setCellValue('E4', "金额(元)");
				for($j=0;$j<count($my_channel_result);$j++){
				$objPHPExcel->setActiveSheetIndex($m)
					->setCellValue('A'.($j+5).'',"{$my_channel_result[$j]['chname']}");
				}
				for($j=0;$j<count($my_channel_result);$j++){
				$objPHPExcel->setActiveSheetIndex($m)
					->setCellValue('B'.($j+5).'',"{$my_channel_result[$j]['co_group']}");
				}
				for($j=0;$j<count($my_channel_result);$j++){
				$objPHPExcel->setActiveSheetIndex($m)
					->setCellValue('C'.($j+5).'',"{$my_channel_result[$j]['price']}");
				}
				for($j=0;$j<count($my_channel_result);$j++){
				$objPHPExcel->setActiveSheetIndex($m)
					->setCellValue('D'.($j+5).'',"{$my_channel_result[$j]['activation_count']}");
				}
				for($j=0;$j<count($my_channel_result);$j++){
				$objPHPExcel->setActiveSheetIndex($m)
					->setCellValue('E'.($j+5).'',"￥{$my_channel_result[$j]['pay_account']}");
				}
				$all_kou_money = 0;
				if($show_taxt){
					for($j=0;$j<count($my_channel_result);$j++){
						$kou_money = round($my_channel_result[$j]['no_amount_account']*(1-$my_channel_result[$j]['taxt']/100),2);
						$all_kou_money += $kou_money;
						$objPHPExcel->setActiveSheetIndex($m)
									->setCellValue('F'.($j+5).'',"￥".$kou_money);
					}
				}
				
				//设置表格内容
				
				$objPHPExcel->setActiveSheetIndex($m)
				->setCellValue('A'.(count($my_channel_result)+5).'',"合计")
				->setCellValue('D'.(count($my_channel_result)+5).'',"{$all_activation}")
				->setCellValue('E'.(count($my_channel_result)+5).'',"￥{$all_pay_account}");
				if($show_taxt){
					$objPHPExcel->setActiveSheetIndex($m)
					->setCellValue('F'.(count($my_channel_result)+5).'',"{$all_kou_money}");
				}
				
				

				if($show_taxt){
					$objPHPExcel->setActiveSheetIndex($m)
					->setCellValue('F4', "扣税点{$my_channel_result[0]['taxt']}%")
					->setCellValue('G4', "ROI")
					->setCellValue('H4', "建议结算金额")
					->setCellValue('I4', "建议扣减金额")
					->setCellValue('G2', "结算方式")
					->setCellValue('I2', $show_bill);
					$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
				}else{
					$objPHPExcel->setActiveSheetIndex($m)
					->setCellValue('F4', "ROI")
					->setCellValue('G4', "建议结算金额")
					->setCellValue('H4', "建议扣减金额")
					->setCellValue('F2', "结算方式")
					->setCellValue('H2', $show_bill);
				}
				$objPHPExcel->setActiveSheetIndex($m)
				->setCellValue('A'.(count($my_channel_result)+8).'', "部门主管确认：\r\n日期：")
				->setCellValue('D'.(count($my_channel_result)+8).'', "运营总监确认：\r\n日期：")
				->setCellValue('A'.(count($my_channel_result)+6).'',"技术确认(激活数)：\r\n日期：")
				->setCellValue('D'.(count($my_channel_result)+6).'',"部门确认：\r\n日期：");
				$objPHPExcel->getActiveSheet()->getStyle('A'.(count($my_channel_result)+6).'')->getAlignment()->setWrapText(true);
				$objPHPExcel->getActiveSheet()->getStyle('C'.(count($my_channel_result)+6).'')->getAlignment()->setWrapText(true);
				$objPHPExcel->getActiveSheet()->setTitle($this->convertUTF8("运营审核客户{$client_name_result[0]['client_name']}对账单"));
				
				$row_index = 'F';
				if($show_taxt){
					$max_index = 'I';
				}else{
					$max_index = 'H';
				}
				//合并单元格
				if($show_taxt){
					$objPHPExcel->getActiveSheet()->mergeCells('A1:I1');
					$objPHPExcel->getActiveSheet()->mergeCells('B2:F2');
					$objPHPExcel->getActiveSheet()->mergeCells('G2:H2');
					$objPHPExcel->getActiveSheet()->mergeCells('B3:I3');
				}else{
					$objPHPExcel->getActiveSheet()->mergeCells('A1:H1');
					$objPHPExcel->getActiveSheet()->mergeCells('B2:E2');
					$objPHPExcel->getActiveSheet()->mergeCells('F2:G2');
					$objPHPExcel->getActiveSheet()->mergeCells('B3:H3');
				}

				$objPHPExcel->getActiveSheet()->mergeCells("A".(count($my_channel_result)+5).":C".(count($my_channel_result)+5)."");
				$objPHPExcel->getActiveSheet()->mergeCells("A".(count($my_channel_result)+6).":C".(count($my_channel_result)+7)."");
				$objPHPExcel->getActiveSheet()->mergeCells("A".(count($my_channel_result)+8).":C".(count($my_channel_result)+9)."");	
				if($show_taxt){
					$objPHPExcel->getActiveSheet()->mergeCells("D".(count($my_channel_result)+6).":I".(count($my_channel_result)+7)."");
					$objPHPExcel->getActiveSheet()->mergeCells("D".(count($my_channel_result)+8).":I".(count($my_channel_result)+9)."");
				}else{
					$objPHPExcel->getActiveSheet()->mergeCells("D".(count($my_channel_result)+6).":H".(count($my_channel_result)+7)."");
					$objPHPExcel->getActiveSheet()->mergeCells("D".(count($my_channel_result)+8).":H".(count($my_channel_result)+9)."");
				}			
		
				//设置宽度
				$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
				$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(10);
				$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(10);
				$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(10);
				$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15.71);
				if($show_taxt){
					$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15.71);
					$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(9);
					$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
					$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
					$objPHPExcel->getActiveSheet()->getStyle('I4')->getAlignment()->setWrapText(true);
				}else{
					$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(9);
					$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
					$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
					$objPHPExcel->getActiveSheet()->getStyle('H4')->getAlignment()->setWrapText(true);
				}

				//设置居中
	            for($i='A';$i<='J';$i++){
					$objPHPExcel->getActiveSheet()->getStyle($i.'4')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
				}
				$objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$objPHPExcel->getActiveSheet()->getStyle('A'.(count($my_channel_result)+5))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$objPHPExcel->getActiveSheet()->getStyle('A2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$objPHPExcel->getActiveSheet()->getStyle('B2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$objPHPExcel->getActiveSheet()->getStyle('A3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$objPHPExcel->getActiveSheet()->getStyle('B3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$objPHPExcel->getActiveSheet()->getStyle('A4')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$objPHPExcel->getActiveSheet()->getStyle('B4')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$objPHPExcel->getActiveSheet()->getStyle('C4')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$objPHPExcel->getActiveSheet()->getStyle('D4')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$objPHPExcel->getActiveSheet()->getStyle('E4')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				if($show_taxt){
					$objPHPExcel->getActiveSheet()->getStyle('G2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
					$objPHPExcel->getActiveSheet()->getStyle('I2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
					$objPHPExcel->getActiveSheet()->getStyle('I4')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				}else{
					$objPHPExcel->getActiveSheet()->getStyle('F2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
					$objPHPExcel->getActiveSheet()->getStyle('H2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
					$objPHPExcel->getActiveSheet()->getStyle('H4')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				}
				for($i=$row_index;$i<$max_index;$i++){
					$objPHPExcel->getActiveSheet()->getStyle($i.'4')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				}
				for($i=0;$i<count($my_channel_result);$i++){
					$objPHPExcel->getActiveSheet()->getStyle('A'.($i+5))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
					$objPHPExcel->getActiveSheet()->getStyle('B'.($i+5))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
					$objPHPExcel->getActiveSheet()->getStyle('C'.($i+5))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
					$objPHPExcel->getActiveSheet()->getStyle('D'.($i+5))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
					$objPHPExcel->getActiveSheet()->getStyle('E'.($i+5))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
					for($j=$row_index;$j<$max_index;$j++){
						$objPHPExcel->getActiveSheet()->getStyle($j.($i+5))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
					}
				}
				$objPHPExcel->getActiveSheet()->getStyle('A'.(count($my_channel_result)+5))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$objPHPExcel->getActiveSheet()->getStyle('C'.(count($my_channel_result)+5))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$objPHPExcel->getActiveSheet()->getStyle('D'.(count($my_channel_result)+5))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$objPHPExcel->getActiveSheet()->getStyle('A1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
				$objPHPExcel->getActiveSheet()->getStyle('A1')->getFill()->getStartColor()->setARGB('#458B00');
				$objPHPExcel->getActiveSheet()->getStyle('E'.(count($my_channel_result) + 5).'')->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
				$objPHPExcel->getActiveSheet()->getStyle('E'.(count($my_channel_result)+5))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$objPHPExcel->getActiveSheet()->getStyle('F'.(count($my_channel_result)+5))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);			
				$objPHPExcel->getActiveSheet()->getStyle('F'.(count($my_channel_result) + 5).'')->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
				
				//设置边框
				$objPHPExcel->getActiveSheet()->getStyle('A1')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('A1')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('A1')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('B1')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('B1')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('C1')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('C1')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('D1')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('D1')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('E1')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('E1')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

				for($i=$row_index;$i<=$max_index;$i++){
					for($j=1;$j<=4;$j++){
						$objPHPExcel->getActiveSheet()->getStyle($i.$j)->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
						$objPHPExcel->getActiveSheet()->getStyle($i.$j)->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
						$objPHPExcel->getActiveSheet()->getStyle($i.$j)->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
					}
					
				}
				
				$objPHPExcel->getActiveSheet()->getStyle('A2')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('A2')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('A2')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('B2')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('C2')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('D2')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('E2')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('E2')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				
				$objPHPExcel->getActiveSheet()->getStyle('A3')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('A3')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('A3')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('B3')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('C3')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('D3')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('E3')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				
				$objPHPExcel->getActiveSheet()->getStyle('A4')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('A4')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('A4')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('B4')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('B4')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('C4')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('C4')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('D4')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('D4')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('E4')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('E4')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				
				for($i=0;$i<count($my_channel_result);$i++){
					$objPHPExcel->getActiveSheet()->getStyle('A'.($i+5))->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
					$objPHPExcel->getActiveSheet()->getStyle('A'.($i+5))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
					$objPHPExcel->getActiveSheet()->getStyle('A'.($i+5))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
					$objPHPExcel->getActiveSheet()->getStyle('B'.($i+5))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
					$objPHPExcel->getActiveSheet()->getStyle('B'.($i+5))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
					$objPHPExcel->getActiveSheet()->getStyle('C'.($i+5))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
					$objPHPExcel->getActiveSheet()->getStyle('C'.($i+5))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
					$objPHPExcel->getActiveSheet()->getStyle('D'.($i+5))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
					$objPHPExcel->getActiveSheet()->getStyle('D'.($i+5))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
					$objPHPExcel->getActiveSheet()->getStyle('E'.($i+5))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
					$objPHPExcel->getActiveSheet()->getStyle('E'.($i+5))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
					for($j=$row_index;$j<=$max_index;$j++){
						$objPHPExcel->getActiveSheet()->getStyle($j.($i+5))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
						$objPHPExcel->getActiveSheet()->getStyle($j.($i+5))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
					}
				}
				
				$objPHPExcel->getActiveSheet()->getStyle('A'.(count($my_channel_result)+5))->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('A'.(count($my_channel_result)+5))->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('A'.(count($my_channel_result)+5))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('A'.(count($my_channel_result)+5))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('B'.(count($my_channel_result)+5))->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('B'.(count($my_channel_result)+5))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('B'.(count($my_channel_result)+5))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('C'.(count($my_channel_result)+5))->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('C'.(count($my_channel_result)+5))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('C'.(count($my_channel_result)+5))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('D'.(count($my_channel_result)+5))->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('D'.(count($my_channel_result)+5))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('D'.(count($my_channel_result)+5))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('E'.(count($my_channel_result)+5))->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('E'.(count($my_channel_result)+5))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('E'.(count($my_channel_result)+5))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

				for($j=$row_index;$j<=$max_index;$j++){
					$objPHPExcel->getActiveSheet()->getStyle($j.(count($my_channel_result)+5))->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
					$objPHPExcel->getActiveSheet()->getStyle($j.(count($my_channel_result)+5))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
					$objPHPExcel->getActiveSheet()->getStyle($j.(count($my_channel_result)+5))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				}
				$objPHPExcel->getActiveSheet()->getStyle('A'.(count($my_channel_result)+6))->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('A'.(count($my_channel_result)+6))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('A'.(count($my_channel_result)+6))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('B'.(count($my_channel_result)+6))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('B'.(count($my_channel_result)+6))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('C'.(count($my_channel_result)+6))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('C'.(count($my_channel_result)+6))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('D'.(count($my_channel_result)+6))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('E'.(count($my_channel_result)+6))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('F'.(count($my_channel_result)+6))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('F'.(count($my_channel_result)+6))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				
				$objPHPExcel->getActiveSheet()->getStyle('A'.(count($my_channel_result)+7))->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('A'.(count($my_channel_result)+7))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('A'.(count($my_channel_result)+7))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('B'.(count($my_channel_result)+7))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('B'.(count($my_channel_result)+7))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('C'.(count($my_channel_result)+7))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('C'.(count($my_channel_result)+7))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('D'.(count($my_channel_result)+7))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('D'.(count($my_channel_result)+7))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('E'.(count($my_channel_result)+7))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('E'.(count($my_channel_result)+7))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('F'.(count($my_channel_result)+7))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('F'.(count($my_channel_result)+7))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('A'.(count($my_channel_result)+8))->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('A'.(count($my_channel_result)+9))->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('A'.(count($my_channel_result)+9))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('B'.(count($my_channel_result)+9))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('B'.(count($my_channel_result)+8))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('C'.(count($my_channel_result)+8))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('B'.(count($my_channel_result)+9))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('C'.(count($my_channel_result)+9))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('C'.(count($my_channel_result)+9))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('D'.(count($my_channel_result)+9))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('E'.(count($my_channel_result)+9))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				
				for($j=$row_index;$j<=$max_index;$j++){
					$objPHPExcel->getActiveSheet()->getStyle($j.(count($my_channel_result)+6))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
					$objPHPExcel->getActiveSheet()->getStyle($j.(count($my_channel_result)+6))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
					$objPHPExcel->getActiveSheet()->getStyle($j.(count($my_channel_result)+7))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
					$objPHPExcel->getActiveSheet()->getStyle($j.(count($my_channel_result)+7))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
					$objPHPExcel->getActiveSheet()->getStyle($j.(count($my_channel_result)+8))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
					$objPHPExcel->getActiveSheet()->getStyle($j.(count($my_channel_result)+9))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
					$objPHPExcel->getActiveSheet()->getStyle($j.(count($my_channel_result)+9))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				}
				$objPHPExcel->getActiveSheet()->getStyle('A'.(count($my_channel_result) + 8).'')->getAlignment()->setWrapText(true);
				$objPHPExcel->getActiveSheet()->getStyle('D'.(count($my_channel_result) + 8).'')->getAlignment()->setWrapText(true);
				$objPHPExcel->getActiveSheet()->getStyle('D'.(count($my_channel_result) + 6).'')->getAlignment()->setWrapText(true);
				unset($my_channel_result);
			}
		}
	}
	
	// 按渠道导出明细
	function my_cid_settle(&$objPHPExcel,$cid_arr,$cid_month_arr,$count){
		$coefficient_model = D('Channel_cooperation.channel_coefficient_p');
		$model = new Model();
		$client_model = D('Channel_cooperation.channel_cooperation_p');
	
		foreach($cid_arr as $m => $n){
			$start_tm = strtotime($cid_month_arr[$m].'01');
			$end_tm = strtotime(date('Ymt',strtotime($cid_month_arr[$m].'01')));
			$my_month = date('Y年m月',$start_tm);
			$channel_result = $model -> table('sj_channel') -> where(array('cid' => $n)) -> select();
			$where['_string'] = "cid = {$n} and submit_tm >= {$start_tm} and submit_tm <= {$end_tm} and status = 1";
			$activation_result = $coefficient_model -> table('activation_coefficient_state') -> where($where) -> order('submit_tm') -> select();
			$activation_arr = array();
			foreach($activation_result as $key => $val){
				$activation_arr[] = $val['counts'];
				$val['counts'] = number_format($val['counts']);
				$activation_result[$key] = $val;
			}
			$all_activation = array_sum($activation_arr);
			
			$price_result = $client_model -> table('co_channel_check') -> where(array('cid' => $n,'account_attr'=>array('neq', '4'),'month' => $cid_month_arr[$m])) -> select();
			$amount_account = sprintf('%.2f',$all_activation*	$price_result[0]['price']);
			$all_activation = number_format($all_activation);
			$amount_account = number_format($amount_account,2,'.',',');
			$my_count = $m + $count;
		
			$sheet1 = $objPHPExcel->createSheet();
			$objPHPExcel->setActiveSheetIndex($my_count)
			->setCellValue('A1', '安智市场对账单')
			->setCellValue('A2', '渠道名称：')
			->setCellValue('B2', "{$channel_result[0]['chname']}")
			->setCellValue('A3', "月份：")
			->setCellValue('B3', "{$my_month}")
			->setCellValue('A4', "日期")
			->setCellValue('B4', "激活量");
			for($i=0;$i<count($activation_result);$i++){
				$objPHPExcel->setActiveSheetIndex($my_count)
				->setCellValue('A'.($i+5).'',date('Y/m/d',$activation_result[$i]['submit_tm']))
				->setCellValue('B'.($i+5).'', "{$activation_result[$i]['counts']}");
			}
			
			$objPHPExcel->setActiveSheetIndex($my_count)
			->setCellValue('A'.(count($activation_result)+5).'', '总激活量')
			->setCellValue('B'.(count($activation_result)+5).'', "{$all_activation}")
			->setCellValue('A'.(count($activation_result)+6).'', "单价")
			->setCellValue('B'.(count($activation_result)+6).'', "￥"."{$price_result[0]['price']}")
			->setCellValue('A'.(count($activation_result)+7).'', "结算金额")
			->setCellValue('B'.(count($activation_result)+7).'', "￥"."{$amount_account}")
			->setCellValue('A'.(count($activation_result)+8).'', "技术确认(激活数)：\r\n日期：")
			->setCellValue('A'.(count($activation_result)+10).'', "部门确认：\r\n日期：");
			$objPHPExcel->getActiveSheet()->getStyle('A'.(count($activation_result)+8).'')->getAlignment()->setWrapText(true);
			$objPHPExcel->getActiveSheet()->getStyle('A'.(count($activation_result)+10).'')->getAlignment()->setWrapText(true);
			$objPHPExcel->getActiveSheet()->setTitle($this->convertUTF8("运营审核渠道_{".$channel_result[0]['cid']."}_".$my_month."对账单"));
			//合并单元格
			$objPHPExcel->getActiveSheet()->mergeCells('A1:B1');
			$objPHPExcel->getActiveSheet()->mergeCells('A'.(count($activation_result)+8).':B'.(count($activation_result)+9).'');
			$objPHPExcel->getActiveSheet()->mergeCells('A'.(count($activation_result)+10).':B'.(count($activation_result)+11).'');
		
			//设置宽度
			$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
			$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
			$objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$objPHPExcel->getActiveSheet()->getStyle('A2')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
			$objPHPExcel->getActiveSheet()->getStyle('A2')->getFill()->getStartColor()->setARGB('#EE9A00');
			$objPHPExcel->getActiveSheet()->getStyle('B2')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
			$objPHPExcel->getActiveSheet()->getStyle('B2')->getFill()->getStartColor()->setARGB('#EE9A00');
			$objPHPExcel->getActiveSheet()->getStyle('A2')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
			$objPHPExcel->getActiveSheet()->getStyle('A2')->getFill()->getStartColor()->setARGB('#EE9A00');
			$objPHPExcel->getActiveSheet()->getStyle('B2')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
			$objPHPExcel->getActiveSheet()->getStyle('B2')->getFill()->getStartColor()->setARGB('#EE9A00');
			$objPHPExcel->getActiveSheet()->getStyle('A3')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
			$objPHPExcel->getActiveSheet()->getStyle('A3')->getFill()->getStartColor()->setARGB('#EE9A00');
			$objPHPExcel->getActiveSheet()->getStyle('B3')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
			$objPHPExcel->getActiveSheet()->getStyle('B3')->getFill()->getStartColor()->setARGB('#EE9A00');
			$objPHPExcel->getActiveSheet()->getStyle('A4')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
			$objPHPExcel->getActiveSheet()->getStyle('A4')->getFill()->getStartColor()->setARGB('#EE9A00');
			$objPHPExcel->getActiveSheet()->getStyle('B4')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
			$objPHPExcel->getActiveSheet()->getStyle('B4')->getFill()->getStartColor()->setARGB('#EE9A00');
			
			//数据在左
			$objPHPExcel->getActiveSheet()->getStyle('B3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
			for($i=0;$i<count($activation_result);$i++){
				$objPHPExcel->getActiveSheet()->getStyle('B'.($i+5))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
			}
			$objPHPExcel->getActiveSheet()->getStyle('B'.(count($activation_result)+5))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
			$objPHPExcel->getActiveSheet()->getStyle('B'.(count($activation_result)+6))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
			$objPHPExcel->getActiveSheet()->getStyle('B'.(count($activation_result)+7))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
			
			//设置边框
			
			$objPHPExcel->getActiveSheet()->getStyle('A1')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A1')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A1')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A1')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B1')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B1')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B1')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A2')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A2')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A2')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A2')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B2')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B2')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B2')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A3')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A3')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A3')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A3')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B3')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B3')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B3')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A4')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A4')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A4')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A4')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B4')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B4')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B4')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			for($i=0;$i<count($activation_result);$i++){
				$objPHPExcel->getActiveSheet()->getStyle('A'.($i+5))->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('A'.($i+5))->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('A'.($i+5))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('A'.($i+5))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('B'.($i+5))->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('B'.($i+5))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle('B'.($i+5))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			}
			$objPHPExcel->getActiveSheet()->getStyle('A'.(count($activation_result)+5))->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A'.(count($activation_result)+5))->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A'.(count($activation_result)+5))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A'.(count($activation_result)+5))->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A'.(count($activation_result)+5))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B'.(count($activation_result)+5))->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B'.(count($activation_result)+5))->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B'.(count($activation_result)+5))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B'.(count($activation_result)+5))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			
			$objPHPExcel->getActiveSheet()->getStyle('A'.(count($activation_result)+6))->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A'.(count($activation_result)+6))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A'.(count($activation_result)+6))->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A'.(count($activation_result)+6))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B'.(count($activation_result)+6))->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B'.(count($activation_result)+6))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B'.(count($activation_result)+6))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			
			$objPHPExcel->getActiveSheet()->getStyle('A'.(count($activation_result)+7))->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A'.(count($activation_result)+7))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A'.(count($activation_result)+7))->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A'.(count($activation_result)+7))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B'.(count($activation_result)+7))->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B'.(count($activation_result)+7))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B'.(count($activation_result)+7))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			
			$objPHPExcel->getActiveSheet()->getStyle('A'.(count($activation_result)+8))->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A'.(count($activation_result)+8))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A'.(count($activation_result)+8))->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A'.(count($activation_result)+8))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B'.(count($activation_result)+8))->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B'.(count($activation_result)+8))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B'.(count($activation_result)+8))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			
			$objPHPExcel->getActiveSheet()->getStyle('A'.(count($activation_result)+9))->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A'.(count($activation_result)+9))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A'.(count($activation_result)+9))->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A'.(count($activation_result)+9))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B'.(count($activation_result)+9))->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B'.(count($activation_result)+9))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B'.(count($activation_result)+9))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			
			$objPHPExcel->getActiveSheet()->getStyle('A'.(count($activation_result)+10))->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A'.(count($activation_result)+10))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A'.(count($activation_result)+10))->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A'.(count($activation_result)+10))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B'.(count($activation_result)+10))->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B'.(count($activation_result)+10))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B'.(count($activation_result)+10))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			
			$objPHPExcel->getActiveSheet()->getStyle('A'.(count($activation_result)+11))->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A'.(count($activation_result)+11))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A'.(count($activation_result)+11))->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A'.(count($activation_result)+11))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B'.(count($activation_result)+11))->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B'.(count($activation_result)+11))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('B'.(count($activation_result)+11))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			
		}
		
	}
	
	function freeze_show(){
		$chname = $_GET['chname'];
		$client_name = $_GET['client_name'];
		$p = $_GET['p'];
		$lr = $_GET['lr'];
		$start_tm = $_GET['start_tm'];
		$end_tm = $_GET['end_tm'];
		$change = $_GET['change'];
		$client_id = $_GET['client_id'];
		$month = $_GET['month'];
		$the_client = substr($my_client,0,-1);
		$client_arr = explode(',',$the_client);
		$client_count = count($client_arr);
		$my_cid = $_GET['my_cid'];
		$the_status = $_GET['the_status'];
		$my_status = $_GET['my_status'];
		$this -> assign('client_count',$client_count);
		$this -> assign('the_status',$the_status);
		$this -> assign('chname',$chname);
		$this -> assign('client_name',$client_name);
		$this -> assign('start_tm',$_GET['start_tm']);
		$this -> assign('end_tm',$_GET['end_tm']);
		$this -> assign('change',$_GET['change']);
		$this -> assign('month',$_GET['month']);
		$this -> assign('p',$p);
		$this -> assign('lr',$lr);
		$this -> assign('client_id',$client_id);
		$this -> assign('from', $_GET['from']);
		$this -> assign('my_cid',$my_cid);
		$this -> display();
	
	}
	
	function financial_reject(){
		$model = new Model();
		$client_model = D('Channel_cooperation.channel_cooperation_p');
		$chname = $_GET['chname'];
		$client_name = $_GET['client_name'];
		$p = $_GET['p'];
		$lr = $_GET['lr'];
		$start_tm = $_GET['start_tm'];
		$end_tm = $_GET['end_tm'];
		$client_id = $_GET['client_id'];
		$my_cid = $_GET['my_cid'];		
		$my_cid_str = substr($my_cid,0,-1);
		$month = $_GET['month'];
		$my_status = $_GET['my_status'];
		if($chname){
			$where_go .= "/chname/{$chname}";
		}
		if($client_name){
			$where_go .= "/client_name/{$client_name}";
		}
		if($p){
			$where_go .= "/p/{$p}";
		}
		if($lr){
			$where_go .= "/lr/{$lr}";
		}
		if($start_tm){
			$where_go .= "/start_tm/{$start_tm}";
		}
		if($end_tm){
			$where_go .= "/end_tm/{$end_tm}";
		}
		if($my_status){
			$where_go .= "/status/{$my_status}";
		}
		
		$need_result = $client_model -> table('co_channel_check') -> where(array('client_id' => $client_id,'account_attr'=>array('neq', '4'),'month' => $month)) -> select();

		$my_cid_arr = explode(',',$my_cid_str);
		foreach($need_result as $key => $val){		
			if($val['status'] == 3 && in_array($val['id'],$my_cid_arr)){
				$log_result = $this -> logcheck(array('client_id' => $client_id,'month' => $month,'cid' => $val['cid']),'co_channel_check',array('status' => 4),$client_model);
				$result = $client_model -> table('co_channel_check') -> where(array('client_id' => $client_id,'month' => $month,'cid' => $val['cid'],'id'=>array('exp',"in ({$my_cid_str})"))) -> save(array('status' => 4));
				if($result){
					$this -> writelog("已编辑id为{$client_id}，月份为{$month}的结算信息".$log_result, 'co_channel_check', $client_id,__ACTION__ ,'','edit');
				}
			}
		}
		if($result){
			$this -> assign('jumpUrl','/index.php/Channel_cooperation/Channelsettle_p/financial_settle'.$where_go);
			$this -> success("编辑成功");
		}else{
			$this -> error("编辑失败");
		}
	
	}
	
	function  go_business(){
		$client_model = D('Channel_cooperation.channel_cooperation_p');
		$chname = $_GET['chname'];
		$client_name = $_GET['client_name'];
		$p = $_GET['p'];
		$lr = $_GET['lr'];
		$start_tm = $_GET['start_tm'];
		$end_tm = $_GET['end_tm'];
		$cid = $_GET['cid'];
		$month = $_GET['month'];
		if($chname){
			$where_go .= "/chname/{$chname}";
		}
		if($client_name){
			$where_go .= "/client_name/{$client_name}";
		}
		if($p){
			$where_go .= "/p/{$p}";
		}
		if($lr){
			$where_go .= "/lr/{$lr}";
		}
		if($start_tm){
			$where_go .= "/start_tm/{$start_tm}";
		}
		if($end_tm){
			$where_go .= "/end_tm/{$end_tm}";
		}
		$client_id = $_GET['client_id'];
		$status = $_GET['status'];
		$my_cid = $_GET['my_cid'];
		
		$cid_str_go = substr($my_cid,0,-1);
		$cid_str_arr = explode(',',$cid_str_go);
		foreach($cid_str_arr as $key => $val){
			$cid_arr = explode('_',$val);
			if($cid_arr[1]){
				$cid_arr_go []= $cid_arr[1];
			}
		}

		foreach($cid_arr_go as $key => $val){
			$where['_string'] = "client_id = {$client_id} and month = {$month} and cid = {$val}";
			$data = array(
				'status' => 6,
				'update_tm' => time()
			);
			$log_result = $this -> logcheck(array('client_id' => $client_id,'month' => $month,'cid' => $val),'co_channel_check',$data,$client_model);
		
			$result = $client_model -> table('co_channel_check') -> where($where) -> save($data);
		
			if($result){
				$this -> writelog("已编辑客户id为{$client_id}，月份为{$month}商务确认".$log_result, 'co_channel_check', $client_id,__ACTION__ ,'','edit');
			}
			$results[] = $result;
		}

		if($results){
			//$this -> writelog("已编辑客户id为{$client_id}，月份为{$month}商务确认".$log_result);
			$this -> assign('jumpUrl','/index.php/Channel_cooperation/Channelsettle_p/operation_check_list'.$where_go);
			$this -> success("编辑成功");
		}else{
			$this -> error("编辑失败");
		}
	
	}
	
	// 运营审核列表
	function business_list(){
		$model = new Model();
		$client_model = D('Channel_cooperation.channel_cooperation_p');
		$co_group_arr =  C('co_group');
		//限制客户查看权限
		$admin_id = $_SESSION['admin']['admin_id'];
		$admin_filter_result = $model -> table('sj_admin_filter') -> where(array('source_type' => 1,'source_value' => $admin_id,'target_type' => 2,'filter_type' => 2)) -> field('target_value') -> select();
		foreach($admin_filter_result as $key => $val){
			$admin_cid[] = $val['target_value'];
		}
		
		$all_client = $client_model -> table('co_client_list') -> order('id') -> select();
		if($admin_cid){
			foreach($all_client as $key => $val){
				$client_channel_result = $client_model -> table('co_client_channel') -> where(array('client_id' => $val['id'])) -> select();
				$my_cid_power = array();
				foreach($client_channel_result as $k => $v){
					$my_cid_power[] = $v['cid'];
				}
				if(!array_diff($my_cid_power,$admin_cid)){
					$my_client_id_str .= $val['id'].',';
				}
			}
		}else{
			$my_client_id = '';
		}
		$my_client_id = substr($my_client_id_str,0,-1);
		
		$client_name = trim($_GET['client_name']);
		$chname = trim($_GET['chname']);
		$co_group = $_GET['co_group'];
		
		$start_tm  = date('Ym',strtotime($_GET['start_tm'].'-01'));
		$end_tm = date('Ym',strtotime($_GET['end_tm'].'-01'));
		if($_GET['start_tm'] && strtotime($_GET['start_tm'].'-01') > strtotime($_GET['end_tm'].'-01')){
			$this -> error("开始时间不能大于结束时间");
		}
		
		$count_power_result = $model -> table('sj_admin_filter') -> where(array('source_type' => 1,'source_value' => $admin_id,'target_type' => 9)) -> find();
	
		if($count_power_result['filter_type'] == 2){
			$where_go .= " and client_id in({$my_client_id})";
		}
		
		if($chname) $channel_where['_string'] = "chname like '%{$chname}%' and status = 1";
		if($co_group!='') $co_group_bo = true;
		if($co_group_bo){
			$this->assign("co_group", $co_group);
			$channel_where['co_group'] =  $co_group;
		}	
		if(isset($_GET['billing'])){
			$this->assign("billing", $_GET['billing']);
			$channel_where['billing'] =  $_GET['billing'];
		}
		if($chname || $co_group_bo || isset($_GET['billing'])){
			$channel_result = $model -> table('sj_channel') -> where($channel_where) -> select();
			foreach($channel_result as $key => $val){
				$channel_cid_str .= $val['cid'].',';
			}
			$cid_str = substr($channel_cid_str,0,-1);
			$where_client = "cid in ({$cid_str})";
			$where_my_channel = "account_attr<>4 AND cid in ({$cid_str}) and (status = 6 or status = 4)";
			$my_channel_result = $client_model -> table('co_channel_check') -> where($where_my_channel) -> select();
	
			foreach($my_channel_result as $key => $val){
				$my_month_str_go .= $val['month'].',';
			}
		
			$my_month_str = substr($my_month_str_go,0,-1);
		
			$client_channel_result = $client_model -> table('co_client_channel') -> where($where_client) -> select();
			foreach($client_channel_result as $key => $val){
				$channel_client_id_arr[] = $val['client_id'];
			}
		}
		if($client_name){
			$client_where['_string'] = "client_name like '%{$client_name}%' and status != 0";
			$client_result = $client_model -> table('co_client_list') -> where($client_where) -> select();
			foreach($client_result as $key => $val){
				$client_id_arr[] = $val['id'];
			}
		}
		
		if($_GET['from'] == 1 && $_GET['check_ids']){
			$check_ids = $_GET['check_ids']; //629,638,612,613,614,
			$check_ids_str = substr($check_ids,0,-1); //629,638,612,613,614
			$where_go .= " and id in ({$check_ids_str})";		
		}elseif($client_name && ($chname||$co_group_bo || isset($_GET['billing']))){
			$client_arr = array_intersect($channel_client_id_arr,$client_id_arr);
			foreach($client_arr as $key => $val){
				$client_str_go .= $val.',';
			}
			$client_str = substr($client_str_go,0,-1);
			if($my_month_str){
				$where_go .= " and client_id in ({$client_str}) and month in ({$my_month_str})";
			}else{
				$where_go .= " and client_id in ({$client_str})";
			}
		}elseif($client_name && !($chname||$co_group_bo|| isset($_GET['billing']))){
			$client_arr = $client_id_arr;
			foreach($client_arr as $key => $val){
				$client_str_go .= $val.',';
			}
			$client_str = substr($client_str_go,0,-1);
			$where_go .= " and client_id in ({$client_str})";
		}elseif(!$client_name && ($chname||$co_group_bo|| isset($_GET['billing']))){
			$client_arr = $channel_client_id_arr;
			foreach($client_arr as $key => $val){
				$client_str_go .= $val.',';
			}
			$client_str = substr($client_str_go,0,-1);
			if($my_month_str){
				$where_go .= " and client_id in ({$client_str}) and month in ({$my_month_str})";
			}else{
				$where_go .= " and client_id in ({$client_str})";
			}
		}

		if($_GET['start_tm']){
			$where_go .= " and month >= {$start_tm}";
		}
		if($_GET['end_tm']){
			$where_go .= " and month <= {$end_tm}";
		}
		$where['_string'] = "account_attr<>4 AND (status = 4 or status = 6)".$where_go;
		if($cid_str){
			$where['cid'] = array('in',$cid_str);
		}				
		$count = $client_model -> table('co_channel_check') -> where($where) -> group('client_id,month') ->  order('month DESC') -> select();
		import("@.ORG.Page");
		$param = http_build_query($_GET);
		$Page = new Page(count($count), 50, $param);
                    if($_GET['from'] == 1){
		    $result = $client_model -> table('co_channel_check') -> where($where) -> field('client_id,month,sum(activation)') -> group('client_id,month') -> order('month DESC,sum(activation) DESC') -> select();
                    }else{
		    $result = $client_model -> table('co_channel_check') -> where($where) -> limit($Page->firstRow . ',' . $Page->listRows) -> field('client_id,month,sum(activation)') -> group('client_id,month') -> order('month DESC,sum(activation) DESC') -> select();
                    }

		if(!$_GET['p']){
			$_GET['p'] = 1;
		}
		if(!$_GET['lr']){
			$_GET['lr'] = 50;
		}
		foreach($result as $key => $val){
			$val['num'] = 1+$key+$_GET['lr']*($_GET['p']-1);
			$channel_where1['_string'] = "account_attr<>4 AND (status = 4 or status = 6) and month = {$val['month']} and client_id = {$val['client_id']}";
			if($cid_str){
				$channel_where1['cid'] = array('in',$cid_str);
			}				
			$channel_result = $client_model -> table('co_channel_check') -> where($channel_where1) -> order('activation DESC') -> select();
			$client_result = $client_model -> table('co_client_list') -> where(array('id' => $val['client_id'])) -> select();
			$val['client_name'] = $client_result[0]['client_name'];
			
			$all_activation_arr = array();
			$all_settle_amount_arr = array();
			$all_salvation_arr = array();
			$all_amount_pay_arr = array();
			$all_pre_amount_arr = array();
			$settle_entity_status = 1;
			foreach($channel_result as $k => $v){
				$v['comments'] = mb_substr($v['comment'],0,4,'utf-8');
				$all_activation_arr[] = $v['activation'];
				$v['activation'] = number_format($v['activation'],0,'.',',');
				$v['average'] = number_format($v['average'],0,'.',',');
				$chname_result = $model -> table('sj_channel') -> where(array('cid' => $v['cid'])) -> select();
				$v['chname'] = $chname_result[0]['chname'];
				$v['billing'] = $chname_result[0]['billing'];
				$v['co_group'] = $co_group_arr[$chname_result[0]['co_group']];
				if ($v['settle_entity'] == '2') {
					$settle_entity_status = 2;
				}
				$all_settle_amount_arr[] = $v['settle_amount'];
				$all_salvation_arr[] = $v['salvation'];
				$all_amount_pay_arr[] = $v['amount_pay'];
				$all_pre_amount_arr[] = $v['pre_amount'];
				//最低结算值
				$min_result = $model -> table('pu_config') -> where(array('config_type' => 'saveminpay','status' => 1)) -> select();
				if($v['settle_amount'] < $min_result[0]['configcontent']){
					$v['warning'] = 1;
				}else{
					$v['warning'] = 0;
				}
				$v['settle_amount'] = number_format($v['settle_amount'],2,'.',',');
				$v['amount_pay'] = number_format($v['amount_pay'],2,'.',',');
				if($v['price_type'] == 1){
					$where_price['_string'] = "cid = {$v['cid']} and month <= {$v['month']} and price_type = 1";
					$price_result = $client_model -> table('co_channel_price') -> where($where_price) -> order('month DESC') -> limit('0,1') -> select();
					$my_price_result = $client_model -> table('co_price_history') -> where(array('did' => $price_result[0]['id'])) -> select();
					$v['price_name'] = $my_price_result[0]['price_name'];
					$v['my_price'] = json_decode($my_price_result[0]['price'],true);
			
				}
				$channel_result[$k] = $v;
			}
			if($settle_entity_status == 1){
				$the_settle_amount_arr[] = array_sum($all_settle_amount_arr);
				$the_salvation_arr[] = array_sum($all_salvation_arr);
				$the_amount_pay_arr[] = array_sum($all_amount_pay_arr);
			}else{
				$the_settle_amount_arr[] = $all_settle_amount_arr[0];
				$the_salvation_arr[] = $all_salvation_arr[0];
				$the_amount_pay_arr[] = $all_amount_pay_arr[0];
			}
			$the_activation_arr[] = array_sum($all_activation_arr);
			$the_pre_amount_arr[] = array_sum($all_pre_amount_arr);
			if($channel_result)	 $val['cid_result'] = $channel_result;
			$result[$key] = $val;
		}
	
		$all_activation = number_format(array_sum($the_activation_arr),0,'.',',');
		$all_settle_amount = number_format(array_sum($the_settle_amount_arr),2,'.',',');
		$all_salvation = number_format(array_sum($the_salvation_arr),2,'.',',');
		$all_amount_pay = number_format(array_sum($the_amount_pay_arr),2,'.',',');
		$all_pre_amount = number_format(array_sum($the_pre_amount_arr),0,'.',',');
		$Page->setConfig('header', '篇记录');
		$Page->setConfig('first', '<<');
		$Page->setConfig('last', '>>');
		$show = $Page->show();
		$this->assign("co_group_arr", $co_group_arr);
		$this -> assign('all_activation',$all_activation);
		$this -> assign('all_settle_amount',$all_settle_amount);
		$this -> assign('all_salvation',$all_salvation);
		$this -> assign('all_amount_pay',$all_amount_pay);
		$this -> assign('all_pre_amount',$all_pre_amount);
		$this -> assign('min_pay',$min_result[0]['configcontent']);
		$this->assign("page", $show);
		$this -> assign('p',$_GET['p']);
		$this -> assign('lr',$_GET['lr']);
		$this -> assign('all_settle_amount',$all_settle_amount);
		$this -> assign('all_salvation',$all_salvation);
		$this -> assign('all_amount_pay',$all_amount_pay);
		$this -> assign('result',$result);
		$this -> assign('client_name',$client_name);
		$this -> assign('chname',$chname);
		$this -> assign('start_tm',$_GET['start_tm']);
		$this -> assign('end_tm',$_GET['end_tm']);
        if($_GET['from'] == 1){
            $file_xls = '运营审核报表_'.date('Ymd').".xls";//文件名
            header("Content-type:text/csv");
            header("Content-Disposition:attachment;filename=".$file_xls);
            header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
            header('Expires:0');
            header('Pragma:public');
            $str = $this -> fetch('business_list2');
            echo $str;
            exit;

            /*foreach($result as $key => $val){
				foreach($val['cid_result'] as $k => $v){
					$file_str .= $val['num'].','.$val['month'].','.$val['client_name'].','.$v['chname'].',"'.$v['activation'].'","'.$v['average'].'",'.$v['price'].',"'.$v['settle_amount'].'","'.$v['salvation'].'",'.$v['taxt'].'%,"'.$v['amount_pay'].'"'."\n";
				}
			}
		
			$file_gos = '运营审核报表_'.date('Ymd').".csv";//文件名
			if(strpos($_SERVER["HTTP_USER_AGENT"],"MSIE")){  
				$file_go = urlencode($file_gos); 
			}else{
				$file_go = $file_gos;
			}
			header( "Cache-Control: public" );
			header( "Pragma: public" );
			header("Content-type:application/vnd.ms-excel");
			header('Content-Disposition:attachment;filename='.$file_go);
			header('Content-Type:APPLICATION/OCTET-STREAM');
			ob_start();
			$header_str =  iconv("UTF-8",'GBK',"序号,月份,客户名称,渠道名称,激活量,日均激活量,单价,结算金额,补差,税率,应付金额\n");
			$file_str_go=  iconv("UTF-8",'GBK',$file_str);
			echo $header_str;
			echo $file_str_go;
			echo iconv("UTF-8",'GBK','总计,-,,-,"'.$all_activation.'",,-,"'.$all_settle_amount.'","'.$all_salvation.'",,"'.$all_amount_pay.'"');
			ob_end_flush();
			exit;*/
		}

		$this -> display('business_list');
	}
	
	function business_reject(){
		$client_model = D('Channel_cooperation.channel_cooperation_p');
		$chname = $_GET['chname'];
		$client_name = $_GET['client_name'];
		$co_group = $_GET['co_group'];
		$billing = $_GET['billing'];
		$p = $_GET['p'];
		$lr = $_GET['lr'];
		$start_tm = $_GET['start_tm'];
		$end_tm = $_GET['end_tm'];
		$cid = $_GET['cid'];
		$month = $_GET['month'];
		if($chname){
			$where_go .= "/chname/{$chname}";
		}
		if($client_name){
			$where_go .= "/client_name/{$client_name}";
		}
		if(isset($co_group)){
			$where_go .= "/co_group/{$co_group}";
		}
		if($billing){
			$where_go .= "/billing/{$billing}";
		}
		if($p){
			$where_go .= "/p/{$p}";
		}
		if($lr){
			$where_go .= "/lr/{$lr}";
		}
		if($start_tm){
			$where_go .= "/start_tm/{$start_tm}";
		}
		if($end_tm){
			$where_go .= "/end_tm/{$end_tm}";
		}
		$client_id = $_GET['client_id'];
		$status = $_GET['status'];
		$my_cid = $_GET['my_cid'];

		$cid_str_go = substr($my_cid,0,-1);
		$cid_str_arr = explode(',',$cid_str_go);
		foreach($cid_str_arr as $key => $val){
			$cid_arr = explode('_',$val);
			if($cid_arr[1]){
				$cid_arr_go []= $cid_arr[1];
			}
		}

		foreach($cid_arr_go as $key => $val){
			$where['_string'] = "client_id = {$client_id} and month = {$month} and cid = {$val}";
			$data = array(
				'status' => 1,
				'update_tm' => time()
			);
			$log_result = $this -> logcheck(array('client_id' => $client_id,'month' => $month,'cid' => $val),'co_channel_check',$data,$client_model);
		
			$result = $client_model -> table('co_channel_check') -> where($where) -> save($data);
		
			if($result){
				$this -> writelog("已编辑客户id为{$client_id}，月份为{$month}运营审核".$log_result, 'co_channel_check', $client_id,__ACTION__ ,'','edit');
			}
			$results[] = $result;
		}
		
		if($results){
			//$this -> writelog("已编辑客户id为{$client_id}，月份为{$month}运营审核".$log_result);
			$this -> assign('jumpUrl','/index.php/Channel_cooperation/Channelsettle_p/business_list'.$where_go);
			$this -> success("编辑成功");
		}else{
			$this -> error("编辑失败");
		}

	}
	
	/**
	 * 2014.10.28 jiwei
	 * 汉化推端需求
	 * 论坛推端结算列表控制器
	 */
	public function forum_settle()
	{
		$client_model = D('Channel_cooperation.channel_cooperation_p');
		$model = new Model();
		$client_name = trim($_GET['client_name']);
		$chname = trim($_GET['chname']);
		$charge_name = trim($_GET['charge_name']);
		$start_tm  = date('Ym',strtotime($_GET['start_tm'].'-01'));
		$end_tm = date('Ym',strtotime($_GET['end_tm'].'-01'));
		if(strtotime($_GET['start_tm'].'-01') > strtotime($_GET['end_tm'].'-01')){
			$this -> error("开始时间不能大于结束时间");
		}
		
		//限制客户查看权限
		$admin_id = $_SESSION['admin']['admin_id'];
		$admin_filter_result = $model -> table('sj_admin_filter') -> where(array('source_type' => 1,'source_value' => $admin_id,'target_type' => 2,'filter_type' => 2)) -> field('target_value') -> select();
		foreach($admin_filter_result as $key => $val){
			$admin_cid[] = $val['target_value'];
		}
		$all_client = $client_model -> table('co_client_list') -> order('id') -> select();
		
		if($admin_cid){
			foreach($all_client as $key => $val){
				$client_channel_result = $client_model -> table('co_client_channel') -> where(array('client_id' => $val['id'])) -> select();
				$my_cid_power = array();
				foreach($client_channel_result as $k => $v){
					$my_cid_power[] = $v['cid'];
				}
				if(!array_diff($my_cid_power,$admin_cid)){
					$my_client_id_str .= $val['id'].',';
				}
			}
		}else{
			$my_client_id = '';
		}
		$my_client_id = substr($my_client_id_str,0,-1);
		
		$count_power_result = $model -> table('sj_admin_filter') -> where(array('source_type' => 1,'source_value' => $admin_id,'target_type' => 9)) -> find();
		
		if($count_power_result['filter_type'] == 2){
			$where_go .= " and client_id in({$my_client_id})";
		}
		if($chname){
			$channel_where['_string'] = "chname like '%{$chname}%' and status = 1";
			$channel_result = $model -> table('sj_channel') -> where($channel_where) -> select();
		
			foreach($channel_result as $key => $val){
				$channel_cid_str .= $val['cid'].',';
			}
			$cid_str = substr($channel_cid_str,0,-1);
				
			$where_client = "cid in ({$cid_str}) AND account_attr=4";
			$where_my_channel = "cid in ({$cid_str}) AND account_attr=4"; //账号属性为论坛
			$my_channel_result = $client_model -> table('co_channel_check') -> where($where_my_channel) -> select();
				
			foreach($my_channel_result as $key => $val){
				$my_id_go .= $val['id'].',';
			}
		
			$my_id = substr($my_id_go,0,-1);
			//$my_month_str = substr($my_month_str_go,0,-1);
				
			$client_channel_result = $client_model -> table('co_channel_check') -> where($where_client) -> select();
				
			foreach($client_channel_result as $key => $val){
				$channel_client_id_arr[] = $val['client_id'];
			}
				
			$channel_client_id_arr = array_unique($channel_client_id_arr);
		}
		
		$charge_id = false;
		if($charge_name)
		{
			$charge_result = $client_model -> table('co_charge') -> where(array('charge_name'=>$charge_name)) -> find();
			$charge_id = $charge_result['id'];
		}
		
		$charge_str = '';
		if($charge_id)
		{
			if(!$client_name)
				$client_name = '%';
	
			$charge_str = ' AND charge_id='.$charge_id;
		}
		
		if($client_name){
			$client_where['_string'] = "client_name like '%{$client_name}%' and status != 0".$charge_str;
			$client_result = $client_model -> table('co_client_list') -> where($client_where) -> select();
			foreach($client_result as $key => $val){
				$client_id_arr[] = $val['id'];
			}
		}
		
		if($client_name && $chname){
			$client_arr = array_intersect($channel_client_id_arr,$client_id_arr);
			foreach($client_arr as $key => $val){
				$client_str_go .= $val.',';
			}
			$client_str = substr($client_str_go,0,-1);
			$where_go .= " and client_id in ({$client_str}) and id in ({$my_id})";
		}elseif($client_name && !$chname){
			$client_arr = $client_id_arr;
			foreach($client_arr as $key => $val){
				$client_str_go .= $val.',';
			}
			$client_str = substr($client_str_go,0,-1);
			$where_go .= " and client_id in ({$client_str})";
		}elseif(!$client_name && $chname){
			$client_arr = $channel_client_id_arr;
			foreach($client_arr as $key => $val){
				$client_str_go .= $val.',';
			}
			$client_str = substr($client_str_go,0,-1);
				
			$where_go .= " and client_id in ({$client_str}) and id in ({$my_id})";
		}
		if($client_name='%')
			$client_name = '';
		
		
		if($_GET['start_tm']){
			$where_go .= " and month >= {$start_tm}";
		}
		if($_GET['end_tm']){
			$where_go .= " and month <= {$end_tm}";
		}
		$my_status = $_GET['my_status'];
		
		if($my_status && $my_status != 4){
			if($my_status == 5){
				$where_go .= " and status = 0";
			}else{
				$where_go .= " and status = {$my_status}";
			}
		}
		
		if($_GET['client_id']){
			if(!$my_status){
				$where['_string'] = "(status = 2 or status = 3 or status = 0) and client_id = {$_GET['client_id']} AND account_attr=4";
			}else{
				$where['_string'] = "status = {$my_status} and client_id = {$_GET['client_id']} AND account_attr=4";
			}
		}else{
			if(!$my_status){
				$where['_string'] = "account_attr=4 AND (status = 2 or status = 3 or status = 0)".$where_go;
			}else{
				$where['_string'] = "account_attr=4 AND status = {$my_status}".$where_go;
			}
		}
		
		$count = $client_model -> table('co_channel_check') -> where($where) -> group('bath_tm,client_id,month') ->  order('month DESC') -> select();
		import("@.ORG.Page");
		$param = http_build_query($_GET);
		$Page = new Page(count($count), 50, $param);
		$result = $client_model -> table('co_channel_check') -> where($where) -> limit($Page->firstRow . ',' . $Page->listRows) -> group('bath_tm,status,client_id,month') -> order('month DESC,status DESC') -> select();
		
		if(!$_GET['p']){
			$_GET['p'] = 1;
		}
		if(!$_GET['lr']){
			$_GET['lr'] = 50;
		}
		
		foreach($result as $key => $val){
			$val['num'] = 1+$key+$_GET['lr']*($_GET['p']-1);
			if($my_status && $my_status != 5){
				$where_channel_result['_string'] = "account_attr=4 AND month = {$val['month']} and client_id = {$val['client_id']} and status = {$my_status} and bath_tm = {$val['bath_tm']}";
			}else{
				$where_channel_result['_string'] = "account_attr=4 AND month = {$val['month']} and client_id = {$val['client_id']} and status = {$val['status']} and bath_tm = {$val['bath_tm']}";
			}
			$channel_result = $client_model -> table('co_channel_check') -> where($where_channel_result) -> order('activation DESC') -> select();
				
			$client_result = $client_model	-> table('co_client_list')
											-> field('co_client_list.*, co_charge.charge_name')
											-> join('co_charge ON co_client_list.charge_id=co_charge.id')
											-> where(array('co_client_list.id' => $val['client_id'])) 
											-> select();
			$val['client_name'] = $client_result[0]['client_name'];
			$val['charge_name'] = $client_result[0]['charge_name'];
			$all_activation_arr = array();
			$all_settle_amount_arr = array();
			$all_salvation_arr = array();
			$all_amount_pay_arr = array();
			$all_bill_amount = array();
			$all_my_salvation_arr = array();
			$bill_channel_result = $client_model -> table('co_channel_check') -> where($where_channel_result) -> group('bath_tm,client_id,month') -> order('activation DESC') -> select();
			foreach($bill_channel_result as $k => $v){
				$all_bill_amount[] = $v['bill_amount'];
				$v['bill_amount'] = number_format($v['bill_amount'],2,'.',',');
			}
				
			foreach($channel_result as $k => $v){
				$v['comments'] = mb_substr($v['comment'],0,4,'utf-8');
				$chname_result = $model -> table('sj_channel')
										-> field('sj_channel.*, sj_channel_category.name AS channel_category_name')
										-> join('sj_channel_category ON sj_channel.category_id=sj_channel_category.category_id')
										-> where(array('cid' => $v['cid']))
										-> select();
				$v['chname'] = $chname_result[0]['chname'];
				
				$v['channel_category_name'] = $chname_result[0]['channel_category_name'];
				$v['channel_attribute'] = $chname_result[0]['attribute'];
				$v['channel_fuzeren'] = $chname_result[0]['fuzeren'];
				
				$all_activation_arr[] = $v['activation'];
				$all_average_arr[] = $v['average'];
				$all_settle_amount_arr[] = $v['settle_amount'];
				$all_salvation_arr[] = $v['salvation'];
				$all_amount_pay_arr[] = $v['amount_pay'];
				if($v['status'] == 2){
					$v['self_salvation'] = $v['amount_pay'] - $v['amount_paid'];
				}else{
					$v['self_salvation'] = 0;
				}
				$v['no_paid'] = $v['amount_pay'] - $v['amount_paid'] - $v['self_salvation'];
				$v['self_salvation'] = number_format($v['self_salvation'],2,'.',',');
				$v['settle_amount'] = number_format($v['settle_amount'],2,'.',',');
				$v['amount_pay'] = number_format($v['amount_pay'],2,'.',',');
				$v['amount_paid'] = number_format($v['amount_paid'],2,'.',',');
				$v['activation'] = $v['activation'];
				$v['average'] = $v['average'];
				$v['no_paid'] = number_format($v['no_paid'],2,'.',',');
		
				$channel_result[$k] = $v;
				if($v['status'] == 3){
					$all_no_paid[] = $v['amount_pay'];
				}
			}
				
			$the_no_paid_arr[] = array_sum($all_no_paid);
			$the_activation_arr[] = array_sum($all_activation_arr);
			$the_average_arr[] = array_sum($all_average_arr);
			$the_settle_amount_arr[] = array_sum($all_settle_amount_arr);
			$the_salvation_arr[] = array_sum($all_salvation_arr);
			$the_amount_pay_arr[] = array_sum($all_amount_pay_arr);
			$val['bill_amount'] = array_sum($all_bill_amount);
			if($val['status'] == 2){
				$channel_amount_paid = str_replace(',','',$channel_result[0]['amount_paid']);
				$val['my_salvation'] = array_sum($all_amount_pay_arr) -$channel_amount_paid;
				$val['no_paid'] = array_sum($all_amount_pay_arr) - $channel_amount_paid - $val['my_salvation'];
				$val['no_paid'] = number_format($val['no_paid'],2,'.',',');
			}else{
				$val['my_salvation'] = 0;
				$val['no_paid'] = array_sum($all_amount_pay_arr);
				$val['no_paid'] = number_format($val['no_paid'],2,'.',',');
			}
			$all_my_salvation[] = $val['my_salvation'];
			$val['my_salvation'] = number_format($val['my_salvation'],2,'.',',');
			$all_bill_amount_arr[] = $val['bill_amount'];
			$all_amount_paid_arr[] = $val['amount_paid'];
			$val['amount_paid'] = number_format($val['amount_paid'],2,'.',',');
			$all_no_paid = $all_no_paid_arr[0];
			$all_amount_pay = array_sum($all_amount_pay_arr);
			$val['all_no_paid'] = number_format($all_no_paid,2,'.',',');
			$val['all_amount_pay'] = number_format($all_amount_pay,2,'.',',');
				
				
			$val['all_my_salvation'] = number_format($all_my_salvation,2,'.',',');
			$val['cid_result'] = $channel_result;
			$result[$key] = $val;
		}
		
		$the_my_salvation = number_format(array_sum($all_my_salvation),2,'.',',');
		$the_bill_amount = number_format(array_sum($all_bill_amount_arr),2,'.',',');
		
		$the_activation = array_sum($the_activation_arr);
		$the_average = array_sum($the_average_arr);
		
		$the_settle_amount = number_format(array_sum($the_settle_amount_arr),2,'.',',');
		$the_salvation = number_format(array_sum($the_salvation_arr),2,'.',',');
		$the_amount_pay = number_format(array_sum($the_amount_pay_arr),2,'.',',');
		$the_amount_paid = number_format(array_sum($all_amount_paid_arr),2,'.',',');
		$the_no_paid = number_format(array_sum($the_no_paid_arr),2,'.',',');
		
		$this -> assign('the_bill_amount',$the_bill_amount);
		$this -> assign('the_my_salvation',$the_my_salvation);
		$this -> assign('the_activation',$the_activation);
		$this -> assign('the_average', $the_average);
		$this -> assign('the_settle_amount',$the_settle_amount);
		$this -> assign('the_salvation',$the_salvation);
		$this -> assign('the_amount_pay',$the_amount_pay);
		$this -> assign('the_amount_paid',$the_amount_paid);
		$this -> assign('the_no_paid',$the_no_paid);
		$Page->setConfig('header', '篇记录');
		$Page->setConfig('first', '<<');
		$Page->setConfig('last', '>>');
		$show = $Page->show();
		$this -> assign('min_pay',$min_result[0]['configcontent']);
		$this->assign("page", $show);
		$this -> assign('p',$_GET['p']);
		$this -> assign('lr',$_GET['lr']);
		$this -> assign('result',$result);
		$this -> assign('my_status',$my_status);
		$this -> assign('client_name',$client_name);
		$this -> assign('charge_name',$charge_name);
		$this -> assign('chname',$chname);
		$this -> assign('start_tm',$_GET['start_tm']);
		$this -> assign('end_tm',$_GET['end_tm']);
		
		//频道属性显示设置
		$channel_attribute = array(1=>'线上',2=>'线下',3=>'论坛');
		$this->assign('channel_attribute', $channel_attribute);
		
		if($_GET['from'] == 1){
			$exp_list = explode('|', $_GET['list']);
			
			$_v_activation = $_v_average = $_v_amount_pay = $_v_amount_paid = $_v_no_paid = 0;
			foreach($result as $key => $val){
				foreach($val['cid_result'] as $k => $v){
					if($v['update_tm']){
						$my_time = date('Y-m-d H:i:s',$v['update_tm']);
					}
					$v['salvation'] = number_format($v['salvation'],2,'.',',');
					if($v['status'] == 0){ $tstatus = '已冻结';}elseif($v['status'] == 2){$tstatus='已付款';}elseif($v['status'] == 3){$tstatus='未付款';}
					if(in_array($val['num'], $exp_list))
					{
						$file_str .= $val['num'].','.$val['month'].','.$val['client_name'].','.$v['chname'].','.$channel_attribute[$v['channel_attribute']].','.$v['channel_category_name'].','.$v['activation'].','.$v['average'].',"'.$v['price'].'","'.$v['amount_pay'].'","'.$v['amount_paid'].'","'.($v['amount_pay']-$v['amount_paid'])."\",{$tstatus}"."\n";
						$_v_activation+=$v['activation'];
						$_v_average+=$v['average'];
						$_v_amount_pay+=$v['amount_pay'];
						$_v_amount_paid+=$v['amount_paid'];
					}
				}
			}
			$_v_no_paid = $_v_amount_pay - $_v_amount_paid;
		
			$file_gos = '论坛结算报表_'.date('Ymd').".csv";//文件名
			if(strpos($_SERVER["HTTP_USER_AGENT"],"MSIE")){
				$file_go = urlencode($file_gos);
			}else{
				$file_go = $file_gos;
			}
			header( "Cache-Control: public" );
			header( "Pragma: public" );
			header("Content-type:application/vnd.ms-excel");
			header('Content-Disposition:attachment;filename='.$file_go);
			header('Content-Type:APPLICATION/OCTET-STREAM');
			ob_start();
			//$header_str =  iconv("UTF-8",'GBK',"序号,月份,客户名称,渠道名称,激活量,单价,结算金额,补差,税率,应付金额,已付金额,差额补齐,未付金额,发票金额,操作时间,状态");
			$header_str =  iconv("UTF-8",'GBK',"序号,月份,客户名称,渠道名称,渠道属性,渠道分类,激活量,日均激活量,单价,应付金币,已付金币,未付金币,状态");
			$file_str_go=  iconv("UTF-8",'GBK',$file_str);
			echo $header_str."\n";
			echo $file_str_go;
			echo iconv("UTF-8",'GBK','总计,-,-,-,-,-,'.$_v_activation.','.$_v_average.',-,"'.$_v_amount_pay.'","'.$_v_amount_paid.'","'.$_v_no_paid.'",-');
			ob_end_flush();
			exit;
		
		}
		
		//计算当前月天数，用于计算日均激活量
		$_dd=array(1=>31,2=>28,3=>31,4=>30,5=>31,6=>30,7=>31,8=>31,9=>30,10=>31,11=>30,12=>31);
		$_n = date('n');
		if(date('L') && $_n==2)
			$month_days = $_dd[$_n]+1;
		else
			$month_days = $_dd[$_n];
		
		$this->assign('month_days', $month_days);
		
		
		
		$this -> display();
	}
	
	/**
	 * 处理论坛结算的显示和提交
	 */
	public function forum_settling()
	{
		$client_id = isset($_GET['client_id']) && empty($_GET['client_id'])==FALSE ? $_GET['client_id'] : NULL;
		$month = isset($_GET['month']) && empty($_GET['month'])==FALSE ? $_GET['month'] : NULL;
		
		if(is_null($client_id) || is_null($month))
			$this->error('参数错误');
		
		$cc_model = D('Channel_cooperation.channel_cooperation_p');
		$model = new Model();
		
		//查询渠道结算信息
		$client = $cc_model	->table('co_client_list')
							->field('id,client_name,charge_id,account_attr,company_name,account_gathering,opening_bank,bank_account,contract_num')
							->where(array('status'=>array('neq',0),'id'=>array('eq',$client_id)))
							->find();
		
		//
		//处理结算提交
		//
		if($this->isPost())
		{
			$amount_paid = $_POST['amount_paid'];
			if(empty($amount_paid))
				$this->error('没有填写金币数量');
			
			$data = array(
					'amount_paid' => $amount_paid, //已付金额
					'bill_amount' => 0, //发票金额
					'invoice_tm' => time(), //开发票时间
					'paid_tm' => time(), //付款日期
					'status' => 2, //2-已付款
					'update_tm' => time(), //更新时间
					'account_attr' => 4,//$client['account_attr'], //账号属性：4=论坛
					'company_name' => $client['company_name'], //公司名称
					'account_gathering' => $client['account_gathering'], //收款账户
					'opening_bank' => $client['opening_bank'], //开户行
					'bank_account' => $client['bank_account'], //银行账户
					'contract_num' => $client['contract_num'], //合同编号
					'bath_tm' => time() //录入时间
			);
			
			$where['client_id'] = $client_id;
			$where['month'] = $month;
			$where['status'] = 3;
			
			$log_result = $this -> logcheck(array('client_id'=> $client_id,'month' => $month,'status' => 3),'co_channel_check',$data,$cc_model);
			$result = $cc_model -> table('co_channel_check') -> where($where) -> save($data);

			if($result){
				$this -> writelog("已录入客户id为{$client_id}，月份为{$month}的财务结算".$log_result, 'co_channel_check', $client_id,__ACTION__ ,'','edit');
				$this -> assign('jumpUrl','/index.php/Channel_cooperation/Channelsettle_p/forum_settle');
				$this -> success("编辑成功");
			}else{
				$this -> error("编辑失败");
			}
		}
		
		//查询结算信息
		$check = $cc_model	->table('co_channel_check')
							->where(array('client_id'=>$client_id, 'month'=>$month))
							->select();

		//判断账号属性是否为论坛
		//if($client['account_attr']!=4)
		//{
			//echo '客户属性不为“论坛”！';
			//exit;
		//}
		
		
		
		
		//计算激活量总和
		$activation_total = $price_total = $amount_pay_total = $amount_paid_total = 0;
		foreach($check as $key=>$val)
		{
			$activation_total += $val['activation'];
			$price_total += $val['price'];
			$amount_pay_total += $val['amount_pay'];
			$amount_paid_total += $val['amount_paid'];
			
			//查询渠道信息
			$channel = $model->table('sj_channel')->where(array('cid'=>$val['cid']))->find();
			//判断渠道属性
			//if($channel['attribute']!=3)
			//{
			//	echo "“{$channel['chname']}”渠道属性不为“论坛”！";
				//exit;
			//}
		}
		$price_average = $price_total/count($check);
		
		
		
		
		
		//查询负责人
		$charge = $cc_model	->table('co_charge')
							->field('id,charge_name')
							->where(array('status'=>1,'id'=>$client['charge_id']))
							->find();
		
		
		$this->assign('check', $check);
		$this->assign('client', $client);
		$this->assign('charge', $charge);
		$this->assign('activation_total', $activation_total);
		$this->assign('price_average', $price_average);
		$this->assign('amount_pay_total', $amount_pay_total);
		$this->assign('amount_paid_total', $amount_paid_total);		
		
		$this->display();
	}
	
	/**
	 * 论坛渠道结算内容详情
	 */
	public function forum_detail()
	{
		$client_id = isset($_GET['client_id']) && empty($_GET['client_id'])==FALSE ? $_GET['client_id'] : NULL;
		$month = isset($_GET['month']) && empty($_GET['month'])==FALSE ? $_GET['month'] : NULL;
		
		if(is_null($client_id) || is_null($month))
			$this->error('参数错误');
		
		$cc_model = D('Channel_cooperation.channel_cooperation_p');
		$model = new Model();
		
		//查询结算信息
		$check = $cc_model	->table('co_channel_check')
							->where(array('client_id'=>$client_id, 'month'=>$month))
							->select();
		
		//计算激活量总和
		$activation_total = $price_total = $amount_pay_total = $amount_paid_total = 0;
		foreach($check as $key=>$val)
		{
			$activation_total += $val['activation'];
			$price_total += $val['price'];
			$amount_pay_total += $val['amount_pay'];
			$amount_paid_total = $val['amount_paid'];
		}
		$price_average = $price_total/count($check);
		
		//查询渠道信息
		$client = $cc_model	->table('co_client_list')
							->field('id,client_name,charge_id,account_attr')
							->where(array('status'=>array('neq',0),'id'=>array('eq',$client_id)))
							->find();
		//查询负责人
		$charge = $cc_model	->table('co_charge')
							->field('id,charge_name')
							->where(array('status'=>1,'id'=>$client['charge_id']))
							->find();
		
		$this->assign('check', $check);
		$this->assign('client', $client);
		$this->assign('charge', $charge);
		$this->assign('activation_total', $activation_total);
		$this->assign('price_average', $price_average);
		$this->assign('amount_pay_total', $amount_pay_total);
		$this->assign('amount_paid_total', $amount_paid_total);
		
		$this->display();
	}
	
	// 获取修改预装量日志
	public function get_amount_log(){
		$model = new Model();
		$res = $model->table('sj_admin_log')->where(array('action_id'=>'2909','category'=>$_POST['cid']))->select();
		foreach($res as $k=>$v){
			$admin = $model->table('sj_admin_users')->where(array('admin_user_id'=>$v['admin_id']))->field('admin_user_name')->find();
			$v['admin_id']= $admin['admin_user_name'];
			$v['logtime'] = date("Y-m-d H:i:s",$v['logtime']);
			$res[$k] = $v;
		}
		echo json_encode($res);
	}
	
	// 修改预装量
	public function save_amount(){
		$cc_model = D('Channel_cooperation.channel_cooperation_p');
		$where['id'] = $_POST['cid'];
		$data = array('pre_amount'=>$_POST['pre_amount']);
		$result = $cc_model -> table('co_channel_check') -> where($where) -> save($data);
		//echo $model->getLastSql();
		if($result){
			$this -> writelog("修改id为{$_POST['cid']}的预装量", 'co_channel_check', $_POST['cid'],__ACTION__ ,'','edit');
			echo '1';
		}else{
			echo '0';
		}
	}
	
}
