<?php

class ClientAction extends CommonAction{
	
	function client_list(){
		$model = new Model();
		if(isset($_GET['client_name'])){
			$client_name = $_GET['client_name'];
			$where_go .= " and client_name like '%{$_GET['client_name']}%'";
		}
		
		if(isset($_GET['start_tm']) && isset($_GET['end_tm'])){
			$start = $_GET['start_tm'];
			$end = $_GET['end_tm'];
			$start_tm = strtotime($_GET['start_tm']);
			$end_tm = strtotime($_GET['end_tm']);
			if($start_tm > $end_tm){
				$this -> error("开始时间不能大于结束时间");
			}
			$where_go .= " and create_tm >= {$start_tm} and create_tm <= {$end_tm}";
		}else{
			$start = date('Y-m-d 00:00:00',strtotime('-1 year'));
			$start_tm = strtotime(date('Ymd 00:00:00',strtotime('-1 year')));
			$end = date('Y-m-d 23:59:59',time());
			$end_tm = time();
			$where_go .= " and create_tm >= {$start_tm} and create_tm <= {$end_tm}";
		}
		
		$where['_string'] = "status = 1".$where_go;
		$count = $model -> table('ad_client') -> where($where) -> count();
		import("@.ORG.Page");
		$param = http_build_query($_GET);
		$Page = new Page($count, 50, $param);
		$result = $model -> table('ad_client') -> where($where) -> limit($Page->firstRow . ',' . $Page->listRows) -> order('create_tm DESC') -> select();
		
		foreach($result as $key => $val){
			$frame_count = $model -> table('ad_frame_agreement') -> where(array('client_id' => $val['id'],'status' => 1)) -> count();
			$contract_count = $model -> table('ad_contract') -> where(array('client_id' => $val['id'])) -> count();
			$val['frame_count'] = $frame_count;
			$val['contract_count'] = $contract_count;
			$result[$key] = $val;
		}
		if($_GET['p']){
			$p = $_GET['p'];
		}else{
			$p = 1;
		}
		if($_GET['lr']){
			$lr = $_GET['lr'];
		}else{
			$lr = 50;
		}
		
		$this -> assign('client_name',$client_name);
		$this -> assign('start',$start);
		$this -> assign('end',$end);
		$this -> assign('p',$p);
		$this -> assign('lr',$lr);
		$Page -> setConfig('header', '篇记录');
		$Page -> setConfig('first', '<<');
		$Page -> setConfig('last', '>>');
		$show = $Page->show();
		$this -> assign("page", $show);
		$this -> assign('result',$result);
		
		//导出报表
		if($_GET['froms'] == 1){
			$results = $model -> table('ad_client') -> where($where) -> order('create_tm desc') -> select();
			
			foreach($results as $key => $val){
				$frame_count = $model -> table('ad_frame_agreement') -> where(array('client_id' => $val['id'],'status' => 1)) -> count();
				$contract_count = $model -> table('ad_contract') -> where(array('client_id' => $val['id'])) -> count();
				$create_date = date('Y-m-d H:i:s',$val['create_tm']);
				$file_str .= $val['client_name'].",".$val['contact_name'].",".$val['contact_num'].",".$frame_count.",".$contract_count.",".$create_date."\n";
			
			}
			
			$file_gos = '广告结算客户_'.date('Ymd').".csv";//文件名
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
			$header_str =  iconv("UTF-8",'GBK',"客户名称,联系人,联系方式,框架协议,具体合同,创建时间\n");
			$file_str_go=  iconv("UTF-8",'GBK',$file_str);
			echo $header_str;
			echo $file_str_go;
			ob_end_flush();
			exit;
		
		}
		$this -> display();
	}


	function add_client_show(){
		$p = $_GET['p'];
		$lr = $_GET['lr'];
		$client_name = trim($_GET['client_name']);
		$start = $_GET['start'];
		$end = $_GET['end'];
		$this -> assign('p',$p);
		$this -> assign('lr',$lr);
		$this -> assign('client_name',$client_name);
		$this -> assign('start',$start);
		$this -> assign('end',$end);
		$this -> display();
	}
	
	//验证客户名是否存在
	function check_client_name(){
		$model = new Model();
		$client_name = trim($_GET['client_name']);
		$from = $_GET['from'];
		
		if($from == 1){
			$where['_string'] = "client_name = '{$client_name}' and status = 1";
		}elseif($from == 2){
			$id = $_GET['id'];
			$where['_string'] = "client_name = '{$client_name}' and status = 1 and  id != {$id}";
		}
		$result = $model -> table('ad_client') -> where($where) -> select();

		if($result){
			echo 1;
			return 1;
		}else{
			echo 2;
			return 2;
		}
	}
	
	function add_client_do(){
		$model = new Model();
		$client_name = trim($client_name);
		if($client_name){
			$where_go .= "/client_name/{$client_name}";
		}
		$start = $_GET['start'];
		if($start){
			$where_go .= "/start/{$start}";
		}
		$end = $_GET['end'];
		if($end){
			$where_go .= "/end/{$end}";
		}
		$lr = trim($_GET['lr']);
		$p = trim($_GET['p']);
		$client_names = trim($_GET['client_names']);
		$contact_name = trim($_GET['contact_name']);
		$contact_num = trim($_GET['contact_num']);
		if(!$contact_name){
			$this -> error("联系人不能为空");
		}
		if(!$contact_num){
			$this -> error("联系方式不能为空");
		}
		$have_client = $model -> table('ad_client') -> where(array('client_name' => $client_names,'status' => 1)) -> select();
		if($have_client){
			$this -> error("该客户名称已存在");
		}
		$data = array(
			'client_name' => $client_names,
			'contact_name' => $contact_name,
			'contact_num' => $contact_num,
			'create_tm' => time(),
			'update_tm' => time(),
		);
		$result = $model -> table('ad_client') -> add($data);
	
		if($result){
			$this -> writelog("已添加id为{$result}，客户名称为{$client_names}的广告结算客户");
			$this -> assign('jumpUrl',"/index.php/Sendnum/Client/client_list/p/{$p}/lr/{$lr}".$where_go);
			$this -> success("添加成功");
		}else{
			$this -> error("添加失败");
		}
		
	}


	function edit_client_show(){
		$model = new Model();
		$id = $_GET['id'];
		$p = $_GET['p'];
		$lr = $_GET['lr'];
		$client_name = trim($_GET['client_name']);
		$start = $_GET['start'];
		$end = $_GET['end'];
		$result = $model -> table('ad_client') -> where(array('id' => $id)) -> select();
		$this -> assign('result',$result);
		$this -> assign('p',$p);
		$this -> assign('lr',$lr);
		$this -> assign('client_name',$client_name);
		$this -> assign('start',$start);
		$this -> assign('end',$end);
		$this -> display();
	}

	function edit_client_do(){
		$model = new Model();
		$id = $_GET['id'];
		$client_name = trim($_GET['client_name']);
		if($client_name){
			$where_go .= "/client_name/{$client_name}";
		}
		$start = $_GET['start'];
		if($start){
			$where_go .= "/start/{$start}";
		}
		$end = $_GET['end'];
		if($end){
			$where_go .= "/end/{$end}";
		}
		$p = $_GET['p'];
		$lr = $_GET['lr'];
		if(!$_GET['contact_name']){
			$this -> error("联系人不能为空");
		}
		if(!$_GET['contact_num']){
			$this -> error("联系方式不能为空");
		}
		$have_where['_string'] = "client_name = '{$_GET['client_names']}' and status = 1 and id != {$id}";
		$have_client = $model -> table('ad_client') -> where($have_where) -> select();
		
		if($have_client){
			$this -> error("该客户名称已存在");
		}
		$data = array(
			'client_name' => trim($_GET['client_names']),
			'contact_name' => trim($_GET['contact_name']),
			'contact_num' => trim($_GET['contact_num']),
			'update_tm' => time(),
		);
		$log_result = $this -> logcheck(array('id' => $id),'ad_client',$data,$model);
		$result = $model -> table('ad_client') -> where(array('id' => $id)) -> save($data);
		
		if($result){
			$this -> writelog("已编辑id为{$id}的广告结算客户".$log_result);
			$this -> assign('jumpUrl',"/index.php/Sendnum/Client/client_list/p/{$p}/lr/{$lr}{$where_go}");
			$this -> success("编辑成功");
		}else{
			$this -> error("编辑失败");
		}
	}


	function delete_client(){
		$model = new Model();
		$client_name = trim($client_name);
		if($client_name){
			$where_go .= "/client_name/{$client_name}";
		}
		$start = $_GET['start'];
		if($start){
			$where_go .= "/start/{$start}";
		}
		$end = $_GET['end'];
		if($end){
			$where_go .= "/end/{$end}";
		}
		$p = $_GET['p'];
		$lr = $_GET['lr'];
		$id = $_GET['id'];
		$log_result = $this -> logcheck(array('id' => $id),'ad_client',array('status' => 0,'update_tm'=> time()),$model);
		$result = $model -> table('ad_client') -> where(array('id' => $id)) -> save(array('status' => 0,'update_tm' => time()));
		if($result){
			$this -> writelog("已删除id为{$id}的广告结算客户".$log_result);
			$this -> assign('jumpUrl',"/index.php/Sendnum/Client/client_list/p/{$p}/lr/{$lr}".$where_go);
			$this -> success("删除成功");
		}else{
			$this -> error("删除失败");
		}

	}

	
	
}
