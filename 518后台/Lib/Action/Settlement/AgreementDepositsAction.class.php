<?php
/**
 * 安智网产品管理平台 广告结算控制器
 * ============================================================================
 * 版权所有 2009-2014 北京力天无限网络有限公司，并保留所有权利。
 * 网站地址: http://www.goapk.com；
 * author：L&J
 *收款相关
 * ----------------------------------------------------------------------------
*/
	class AgreementDepositsAction extends CommonAction {

		/**
		 *保证金详情页面
		 */
		 public function index()
		 {
		 	//取得协议id
		 	$agreement_id = trim($_GET['agreement_id']);
			//根据协议id取得客户名称以及客户id
			$Deposit_db = D("Settlement.AgreementDeposit");
			import("@.ORG.Page");
			$map['agreement_id'] = array('eq',$agreement_id);
			$count = $Deposit_db -> where($map) ->count();
			$Page=new Page($count,50);
			//根据协议id取出所有该协议下的保证金流水
			$lists = $Deposit_db -> where($map) -> order("create_tm DESC,collection_date DESC,collection_cash DESC") -> limit($Page->firstRow.','.$Page->listRows) -> select();
			//导出报表
			if($_GET['export'] == '1')
			{
				$this->export_deposit($lists,"协议列表_保证金详情".date('Y-m-d-h-i').".csv");
			}
			//取出待抵扣总额
			$Agreement_db = D("Settlement.Agreement");
			$deductible_cash = $Agreement_db -> where("id=".$agreement_id) -> getField("deductible_cash");
			//已收保证金总额
			$total_amount = $Deposit_db -> where($map) -> sum("collection_cash");
			$this -> assign("total_amount",$total_amount);
			$this -> assign("deductible_cash",$deductible_cash);
			$this->assign("lists",$lists);
			$this->assign("agreement_id",$agreement_id);
			$Page->setConfig('header','条记录');
			$Page->setConfig('first','<<');
			$Page->setConfig('last','>>');
			$show =$Page->show();
			$this->assign ( "page", $show );
			$this->display();
		 }
		public function export_deposit($lists,$filename,$category = "deposits")
		{
			
			header("Content-type:text/csv");
			header("Content-Disposition:attachment;filename=".$filename);
			header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
			header('Expires:0');
			header('Pragma:public');
			$str = "";
			if(empty($lists))
			{
				$str.=iconv('utf-8','gb2312','没有任何信息');
			}else
			{
				if($category == "deposits")
				{
					$str = iconv('utf-8','gb2312','序号').",".iconv('utf-8','gb2312','录入时间').",".iconv('utf-8','gb2312','最后一次编辑时间').",".iconv('utf-8','gb2312','收款金额').",".iconv('utf-8','gb2312','收款日期').",".iconv('utf-8','gb2312','备注')."\r\n";

				}else if($category == "invoices")
				{
					$str = iconv('utf-8','gb2312','序号').",".iconv('utf-8','gb2312','录入时间').",".iconv('utf-8','gb2312','最后一次编辑时间').",".iconv('utf-8','gb2312','票据金额').",".iconv('utf-8','gb2312','票据类型').",".iconv('utf-8','gb2312','开票日期').",".iconv('utf-8','gb2312','备注')."\r\n";
					$invoices_type = array('1'=>"发票",'2'=>"收据");
				}
				foreach ($lists as $key => $val)
				{
					if($category == "deposits")
					{
						$str.= iconv('utf-8','gb2312',$key+1).",".date("Y-m-d H:i:s",$val['create_tm']).",".date("Y-m-d H:i:s",$val['update_tm']).",". iconv('utf-8','gb2312',$val['collection_cash']).",". iconv('utf-8','gb2312',$val['collection_date']).",".iconv('utf-8','gb2312',$val['remark'])."\r\n";
					}else if($category == "invoices")
					{
						$str.= iconv('utf-8','gb2312',$key+1).",".date("Y-m-d H:i:s",$val['create_tm']).",".date("Y-m-d H:i:s",$val['update_tm']).",". iconv('utf-8','gb2312',$val['invoice_cash']).",". iconv('utf-8','gb2312',$invoices_type[$val['invoice_type']]).",". iconv('utf-8','gb2312',$val['invoicing_date']).",".iconv('utf-8','gb2312',$val['remark'])."\r\n";
					}
				}
			}
			echo $str;
			exit;
		}
		/**
		 * 发票收据详情页
		 */
		public function invoices_list()
		{
			//取得agreement_id
			$agreement_id = trim($_GET['agreement_id']);
			//根据agreement_id取出所有发票收据的流水
			$Invoice_db = D("Settlement.AgreementInvoice");
			$map['agreement_id'] = array('eq',$agreement_id);
			import("@.ORG.Page");
			$count = $Invoice_db -> where($map) -> order("create_tm desc,invoicing_date desc,invoice_cash desc") -> count();
			$Page=new Page($count,50);
			$lists = $Invoice_db -> where($map) -> order("create_tm desc,invoicing_date desc,invoice_cash desc") -> limit($Page->firstRow.','.$Page->listRows) -> select();
			if($_GET['export'] == "1")
			{
				$this->export_deposit($lists,"协议列表_发票收据详情".date('Y-m-d-h-i').".csv",'invoices');
			}
			//取出已开发票总额
			$map['invoice_type'] = 1;
			$total_invoice = $Invoice_db -> where($map) -> sum("invoice_cash");
			//取出所有收据总额
			$map['invoice_type'] = 2 ;
			$total_shouju = $Invoice_db -> where($map) -> sum("invoice_cash");
			$this -> assign("total_invoice",$total_invoice);
			$this -> assign("total_shouju",$total_shouju);
			$this->assign("lists",$lists);
			$this->assign("agreement_id",$agreement_id);
			$Page->setConfig('header','条记录');
			$Page->setConfig('first','<<');
			$Page->setConfig('last','>>');
			$show =$Page->show();
			$this->assign ( "page", $show );
			$this->display();
		}
		 /**
		  *录入保证金页面_显示
		  */
		 public function  add_deposit_show()
		 {
		 	//取得协议id
		 	$agreement_id = trim($_GET['agreement_id']);
		 	//根据协议id取得客户名称以及客户id
		 	$agreement_db = D("Settlement.Agreement");
		 	//协议编号
		 	$agreement_code = $agreement_db -> where("id=".$agreement_id) ->getField("agreement_code");
		 	//客户id
		 	$client_id = $agreement_db -> where("id=".$agreement_id) ->getField("client_id");
		 	$client_db = D("Settlement.Client");
		 	//客户名称
		 	$client_name = $client_db -> where("id=".$client_id) -> getField("client_name");
			//来源
			$from = trim($_GET['from']);
			//url_suffix
			$url_suffix = trim($_GET['url_suffix']);

		 	$this->assign("url_suffix",$url_suffix);
		 	$this->assign("from",$from);
		 	$this->assign("agreement_id",$agreement_id);
		 	$this->assign("agreement_code",$agreement_code);
		 	$this->assign("client_id",$client_id);
		 	$this->assign("client_name",$client_name);
		 	$this->display();
		 }
		/**
		 *录入保证金_执行
		 */
		 public function add_deposit_do()
		 {
			if(!$this->isPost())
			{
				$this->error("请求来源错误！");
			}
			$data['agreement_id'] = trim($_POST['agreement_id']);
			$data['client_id'] = trim($_POST['client_id']);
			$data['collection_cash'] = trim($_POST['collection_cash']);
			if(!is_numeric($data['collection_cash']))
			{
				$this->error("收款必须是数字！");
			}
			$data['collection_date'] = trim($_POST['collection_date']);
			$data['admin_id'] = $_SESSION['admin']['admin_id'];
			$data['admin_name'] = $_SESSION['admin']['admin_user_name'];
			foreach ($data as $d)
			{
				if(!isset($d) || $d == "")
				{
					$this->error("数据不全，无法添加！");
				}
			}
			$data['remark'] = trim($_POST['remark']);
			$data['update_tm'] = time();
			$data['create_tm'] = time();
			//准备写入数据
			$Deposit_db = D("Settlement.AgreementDeposit");
			//开启事务
			$Deposit_db -> startTrans();
			$d_id = $Deposit_db -> add($data);
			if(!$d_id)
			{
				$Deposit_db -> rollback();
				$this->error("录入收款失败！");
			}
			//处理录入收款会影响到的数据：框架协议中的“已收保证金”和“待抵扣”两列
			$agreement_db = D("Settlement.Agreement");
			$map['id'] = array('eq',$data['agreement_id']);
			//取出原来的保证金和待抵扣
			$agreement_old_collection_cash = $agreement_db -> where($map) -> getField("collection_cash");
			$agreement_old_deductible_cash = $agreement_db -> where($map) -> getField("deductible_cash");
			//已收保证金和待抵扣增加
			$new_data['collection_cash'] = $agreement_old_collection_cash + $data['collection_cash'];
			$new_data['deductible_cash'] = $agreement_old_deductible_cash + $data['collection_cash'];
			//准备更新
			if(!$agreement_db -> where($map) -> save($new_data))
			{
				//更新失败、回滚操作
				$Deposit_db -> rollback();
				$this->error("录入保证金失败！");
			}
			//录入成功。提交操作，写日志
			$this->writelog("广告结算：框架协议-新录入了框架协议保证金，id为[".$d_id."]，录入金额为：".$data['collection_cash']);
			$Deposit_db -> commit();
			$from = trim($_POST['from']);
			if($from == "agreement_list")
			{
				$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Agreement/index'.base64_decode($_POST['url_suffix']));

			}else{
				$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/AgreementDeposits/index/agreement_id/'.$data['agreement_id']);
			}
			$this -> success("录入成功！");
		 }
		/**
		 *录入发票或收据_显示
		 */
		 public function add_invoice_show()
		 {

		 	//取得协议id
		 	$agreement_id = trim($_GET['agreement_id']);
		 	//根据协议id取得客户名称以及客户id
		 	$agreement_db = D("Settlement.Agreement");
		 	//协议编号
		 	$agreement_code = $agreement_db -> where("id=".$agreement_id) ->getField("agreement_code");
		 	//客户id
		 	$client_id = $agreement_db -> where("id=".$agreement_id) ->getField("client_id");
		 	$client_db = D("Settlement.Client");
		 	//客户名称
		 	$client_name = $client_db -> where("id=".$client_id) -> getField("client_name");
		 	//来源
		 	$from = trim($_GET['from']);
		 	//url_suffix
		 	$url_suffix = trim($_GET['url_suffix']);

		 	$this->assign("url_suffix",$url_suffix);
		 	$this->assign("from",$from);
		 	$this->assign("agreement_id",$agreement_id);
		 	$this->assign("agreement_code",$agreement_code);
		 	$this->assign("client_id",$client_id);
		 	$this->assign("client_name",$client_name);
		 	$this->display();
		 }
		/**
		 *录入发票或收据_执行
		 */
		public function add_invoice_do()
		{
// 			$this -> showArr($_POST);


			$data['agreement_id'] = trim($_POST['agreement_id']);
			$data['client_id'] = trim($_POST['client_id']);
			$data['invoice_type'] = trim($_POST['invoice_type']);
			$date = $_POST['invoicing_date'];
			$cash = $_POST['invoice_cash'];
			foreach ($data as $d)
			{
				if(!isset($d) || $d == "")
				{
					$this->error("数据不全，无法录入！");
				}
			}
			$code = $_POST['invoice_code'];
			$tmp = str_ireplace("\r\n", "", $code);
			if(!isset($tmp) || $tmp == "")
			{
				$this->error("发票或收据号没有录入！");
			}
			$data['admin_id'] = $_SESSION['admin']['admin_id'];
			$data['admin_name'] = $_SESSION['admin']['admin_user_name'];
			$data['create_tm'] = time();
			$data['update_tm'] = time();
			$data['remark'] = trim($_POST['remark']);
			//准备写入数据
			$Invoice_db = D("Settlement.AgreementInvoice");
			//启动事务
			$Invoice_db -> startTrans();
			$count = 0;
			foreach ($cash as $i => $c)
			{
				if(!isset($c) || $c == "" || !is_numeric($c))
				{
					$this -> error("输入金额出错！");
				}
				$data['invoice_cash'] = $c;
				if($date[$i] == "" || !isset($date[$i]))
				{
					$this -> error("未填写开票日期！");
				}
				$data['invoicing_date'] = $date[$i];
				$split_code = split("\r\n", $code[$i]);
				foreach ($split_code as $key => $s)
				{
					if($s != "" && isset($s) && !empty($s))
					{
						$data['invoice_code'] = $s;
						if(!$Invoice_db -> add($data))
						{
							//录入失败，回滚
							$Invoice_db -> rollback();
							$this->error("录入失败！");
						}
						$count ++ ;
					}
				}
			}

			//录入成功！提交
			$Invoice_db -> commit();
			$from = trim($_POST['from']);
			if($from == "agreement_list")
			{
				$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Agreement/index'.base64_decode($_POST['url_suffix']));

			}else{
				$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/AgreementDeposits/invoices_list/agreement_id/'.$data['agreement_id']);
			}
			$this->writelog("广告结算：框架协议-新录入了[".$count."]条发票（收据）记录。【广告结算】");
			$this->success("录入发票或收据成功！");
		}
		/**
		 *编辑保证金_显示
		 */
		public function edit_deposit_show()
		{
			//取得保证金id
			$id = trim($_GET['id']);
			if(!isset($id) || $id == "")
			{
				$this->error("id不存在");
			}
			//根据id取得client_id
			$Deposit_db = new Model();
			/**
			 * ad_agreement_deposits A
			 * ad_agreements B
			 * ad_clients C
			 */

			$map["_string"] = "A.client_id = B.client_id AND A.client_id = C.id AND A.agreement_id = B.id";
			$map['A.id'] = array('eq',$id);
			$field = "A.id,A.agreement_id,A.collection_cash,A.collection_date,A.remark,B.agreement_code,C.client_name";
			$data = $Deposit_db -> table("settlement.ad_agreement_deposits A,settlement.ad_agreements B,settlement.ad_clients C") -> where($map) ->field($field) -> select();
			$this->assign("data",$data[0]);
			$this->display();
		}
		/**
		 *编辑保证金_执行
		 */
		public function edit_deposit_do()
		{
			if(!$this->isPost())
			{
				$this->error("请求来源错误！");
			}
			$data['id'] = trim($_POST['id']);
			$data['agreement_id'] = trim($_POST['agreement_id']);
			$data['collection_cash'] = trim($_POST['collection_cash']);
			if(!is_numeric($data['collection_cash']))
			{
				$this->error("收款必须是数字！");
			}
			$data['collection_date'] = trim($_POST['collection_date']);
			foreach ($data as $d)
			{
				if(!isset($d) || $d == "")
				{
					$this->error("数据不全，无法添加！");
				}
			}
			$data['remark'] = trim($_POST['remark']);
			$data['update_tm'] = time();

			$Deposit_db = D("Settlement.AgreementDeposit");
			//获取原来AgreementDeposit表中的收款金额
			$deposit_old_collection_cash = $Deposit_db -> where("id=".$data['id']) -> getField("collection_cash");
			//开启事务
			$Deposit_db -> startTrans();
			//更新表ad_agreement_deposits
			if(!$Deposit_db -> where("id=".$data['id']) -> save($data))
			{
				$Deposit_db -> rollback();
				$this->error("录入收款失败！");
			}
			//处理录入收款会影响到的数据：框架协议中的“已收保证金”和“待抵扣”两列
			$agreement_db = D("Settlement.Agreement");
			$map['id'] = array('eq',$data['agreement_id']);
			//取出原来的保证金和待抵扣
			$agreement_old_collection_cash = $agreement_db -> where($map) -> getField("collection_cash");
			$agreement_old_deductible_cash = $agreement_db -> where($map) -> getField("deductible_cash");
			//已收保证金表ad_agreements表中列collection_cash的减去原来录入的（$deposit_old_collection_cash）加上现在新编辑录入的（$data['collection_cash']）
			//待抵扣和已收保证金类似
			$new_data['collection_cash'] = $agreement_old_collection_cash - $deposit_old_collection_cash + $data['collection_cash'];
			$new_data['deductible_cash'] = $agreement_old_deductible_cash - $deposit_old_collection_cash + $data['collection_cash'];
			$affected_rows = $agreement_db -> where($map) -> save($new_data);
			//准备更新
			if($affected_rows === false)
			{
				//更新失败、回滚操作

				$Deposit_db -> rollback();
				$this->error("编辑失败！");
			}
			//录入成功。提交操作，写日志
			$this->writelog("广告结算：框架协议-编辑了框架协议保证金收款，id为[".$data['id']."]，收款金额由[".$deposit_old_collection_cash."] 变为[".$data['collection_cash']."]");
			$Deposit_db -> commit();
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/AgreementDeposits/index/agreement_id/'.$data['agreement_id']);
			$this -> success("编辑成功！");
		}
		/**
		 *编辑发票收据_显示
		 */
		public function edit_invoice_show()
		{
			//取得保证金id
			$id = trim($_GET['id']);
			if(!isset($id) || $id == "")
			{
				$this->error("id不存在");
			}
			//根据id取得client_id
			$Deposit_db = new Model();
			/**
			 * ad_agreement_invoices A
			 * ad_agreements B
			 * ad_clients C
			*/

			$map["_string"] = "A.client_id = B.client_id AND A.client_id = C.id AND A.agreement_id = B.id";
			$map['A.id'] = array('eq',$id);
			$field = "A.id,A.agreement_id,A.invoice_cash,A.invoice_code,A.invoice_type,A.invoicing_date,A.remark,B.agreement_code,C.client_name";
			$data = $Deposit_db -> table("settlement.ad_agreement_invoices A,settlement.ad_agreements B,settlement.ad_clients C") -> where($map) ->field($field) -> select();
			$this->assign("data",$data[0]);
			$this->display();
		}
		/**
		 *编辑发票或收据_执行
		 */
		public function edit_invoice_do()
		{
				$data['id'] = trim($_POST['id']);
				$data['agreement_id'] = trim($_POST['agreement_id']);
				$data['invoice_cash'] = trim($_POST['invoice_cash']);
				$data['invoice_code'] = trim($_POST['invoice_code']);
				$data['invoice_type'] = trim($_POST['invoice_type']);
				$data['invoicing_date'] = trim($_POST['invoicing_date']);

				foreach ($data as $d)
				{
					if(!isset($d) || $d == "")
					{
						$this->error("数据不全，无法编辑！");
					}
				}
				$data['remark'] = trim($_POST['remark']);
				$data['update_tm'] = time();
				$Invoice_db = D("Settlement.AgreementInvoice");
				//准备更新
				$map['id'] = $data['id'];
				
				$log_result = $this->logcheck(array('id'=>$data['id']), 'settlement.ad_agreement_invoices', $data, $Invoice_db);
				if($Invoice_db -> where($map) -> save($data) === false)
				{
					//更新失败
					$this->error("更新失败！");
				}
				$this->writelog("广告结算：框架协议-编辑了id为[".$data['id']."]的发票（收据）【广告结算】".$log_result);
				$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/AgreementDeposits/invoices_list/agreement_id/'.$data['agreement_id']);
				$this -> success("编辑成功！");

		}
		/**
		 *删除保证金
		 */
		public function delete_deposit()
		{
			//获取ID号
			$map['id'] = trim($_GET['id']);
			$map['agreement_id'] = trim($_GET['agreement_id']);
			$Deposit_db = D("Settlement.AgreementDeposit");
			//取得保证金金额
			$collection_cash = $Deposit_db -> where($map) -> getField("collection_cash");
			if(!isset($collection_cash))
			{
				$this->error("出错啦！");
			}
			//准备删除
			//启动事务
			$Deposit_db -> startTrans();
			if(!$Deposit_db -> where($map) -> delete())
			{
				//删除出错，回滚
				$Deposit_db -> rollback();
				$this->error("删除出错！");
			}
			$agreement_db = D("Settlement.Agreement");
			//将相应ad_agreements表中的“已收保证金”和“待抵扣”减去$collection_cash
			$where['id'] = $map['agreement_id'];
			$agreement_old_collection_cash = $agreement_db -> where($where) -> getField("collection_cash");
			$agreement_old_deductible_cash = $agreement_db -> where($where) -> getField("deductible_cash");

			$new_data['collection_cash'] = $agreement_old_collection_cash - $collection_cash;
			$new_data['deductible_cash'] = $agreement_old_deductible_cash - $collection_cash;
			//开始更新ad_agreements表
			if(!$agreement_db -> where($where) -> save($new_data))
			{
				//更新失败，回滚
				$Deposit_db -> rollback();
				$this->error("删除出错！");
			}
			//更新成功！提交
			$Deposit_db -> commit();
			$this->writelog("广告结算：框架协议-删除了id为[".$map['id']."]的保证金记录。【广告结算】");
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/AgreementDeposits/index/agreement_id/'.$map['agreement_id']);
			$this -> success("删除成功！");
		}
		/**
		 *删除发票或收据
		 */
		public function delete_invoice()
		{
			//获取ID号
			$map['id'] = trim($_GET['id']);
			$map['agreement_id'] = trim($_GET['agreement_id']);
			$Invoice_db = D("Settlement.AgreementInvoice");
			if(!$Invoice_db -> where($map) -> delete())
			{
				//删除失败！
				$this->error("删除失败！");
			}
			$this->writelog("广告结算：框架协议-删除了id为[".$map['id']."]的发票（收据）记录。【广告结算】");
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/AgreementDeposits/invoices_list/agreement_id/'.$map['agreement_id']);
			$this -> success("删除成功！");
		}
		
		/**
		 * 协议相关联合同的所有已抵扣金列表
		 * 2015.5.19 jiwei
		 */
		public function deposits_list()
		{
			// 获取协议信息
	        $agreement_id = trim($_GET['agreement_id']);
	        $Agreement_db = D("Settlement.Agreement");
	        $agreement = $Agreement_db->where(array('id'=>$agreement_id))->find();
	        $this->assign('agreement_id', $agreement_id);

	        // 查询协议相关合同
	        $Contract_db = D("Settlement.Contract");
	        $contract_rows = $Contract_db->field('id,contract_code,month')->where(array('agreement_id'=> $agreement_id))->select();
	        $contract_ids = $contract_codes = array();
	        foreach($contract_rows as $row)
	        {
	        	$contract_ids[] = $row['id'];
	        	$contracts[$row['id']] = array('id'=>$row['id'], 'code'=>$row['contract_code'], 'month'=>$row['month']);
	        }
	        $this->assign('contracts', $contracts);
    
	        // 查询合同已抵扣记录
	        $Receive_db = D("Settlement.ContractDeposit");
			

	        import("@.ORG.Page");
	        $map['contract_id'] = array('in', $contract_ids);
	        $count = $Receive_db->where($map)->count();
	        $Page = new Page($count, 50);
	        $lists = $Receive_db->where($map)->order("create_tm DESC,deduct_date DESC,deduct_cash DESC")->limit($Page->firstRow . ',' . $Page->listRows)->select();
	        //导出报表
	        if ($_GET['export'] == '1') {
	            $this->export_agreement_deposit($lists,$contracts,"框架协议_已抵扣保证金详情".date('Y-m-d-h-i').".csv");
	        }
	        $this->assign("lists", $lists);
	
	        $Page->setConfig('header', '条记录');
	        $Page->setConfig('first', '<<');
	        $Page->setConfig('last', '>>');
	        $show = $Page->show();
	        $this->assign("page", $show);
	        $this->display();
		}
		
		public function edit_deposit_remark()
		{
			$id = trim($_GET['id']);
			if (!isset($id) || $id == "") {
				$this->error("id不存在");
			}
			$agreement_id = trim($_GET['agreement_id']);
			if (!isset($agreement_id) || $agreement_id == "") {
				$this->error("agreement_id不存在");
			}
			
			
			$Deposit_db = D("Settlement.ContractDeposit");
			
			
			if ($_POST) {
				if(mb_strlen($_POST['remark'],'utf-8')>100)
					$this->error("备注不能大于100个字符");
				
	            $data['remark'] = trim($_POST['remark']);

	            $Deposit_db->where(array('id'=>$id))->save($data);
	            
	            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/AgreementDeposits/deposits_list/agreement_id/' . $agreement_id);
	            $this->success("编辑成功！");
	        } else {
	            
	        	$data = $Deposit_db->where(array('id'=>$id))->find();
	        	
	        	$this->assign('id', $id);
	        	$this->assign('agreement_id', $agreement_id);
	            $this->assign('data', $data);
	            
	            $this->display();
	        }
		}
		
		private function export_agreement_deposit($lists, $contracts, $filename)
		{
				
			header("Content-type:text/csv");
			header("Content-Disposition:attachment;filename=".$filename);
			header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
			header('Expires:0');
			header('Pragma:public');
			
			$str = "";
			
			if(empty($lists))
			{
				$str.='没有任何信息';
			}
			else
			{
				$str.="抵扣时间,抵扣金额,合同编号,合同月份,备注\n";
				
				foreach ($lists as $key => $val)
				{
					$str.=date('Y-m-d H:i:s', $val['create_tm']).','.$val['deduct_cash'].','.$contracts[$val['contract_id']]['code'].','.$contracts[$val['contract_id']]['month'].','.str_replace("\n",'',trim($val['remark']))."\n";
				}
			}
			
			$str = iconv('utf-8','gb2312',$str);
			echo $str;
			exit;
		}		
		
	}