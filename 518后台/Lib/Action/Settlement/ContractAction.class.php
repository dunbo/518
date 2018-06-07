<?php
/**
 * 安智网产品管理平台 广告结算控制器
 * ============================================================================
 * 版权所有 2009-2014 北京力天无限网络有限公司，并保留所有权利。
 * 网站地址: http://www.goapk.com；
 * author：L&J
 * ----------------------------------------------------------------------------
*/
	class ContractAction extends CommonAction {

		/**
		 *合同列表
		 */
		public function index()
		{
			//搜索条件
			/*
			 * ad_contracts A
			 * ad_clients B
			 */
			$table = "settlement.ad_contracts A,settlement.ad_clients B";
			$field = "
					  A.id,
					  A.create_tm,
					  A.agreement_code,
					  A.contract_code,
					  A.month,
					  B.client_name,
					  A.start_date,
					  A.end_date,
					  A.package_num,
					  A.sign_date,
					  A.responsible,
					  A.app_original_total,
					  A.app_discount_total
			";
			$order = "A.month DESC,CONVERT(B.client_name USING gbk) ASC";
			if(isset($_GET['agreement_code']) && $_GET['agreement_code'] != "")
			{
				$map['A.agreement_code'] = array('like','%'.trim($_GET['agreement_code']).'%');
				$this -> assign("agreement_code",trim($_GET['agreement_code']));
			}

			//合同编号
			if(isset($_GET['contract_code']) && $_GET['contract_code'] != "")
			{
				$map['A.contract_code'] = array('like','%'.trim($_GET['contract_code']).'%');
				$this -> assign("contract_code",trim($_GET['contract_code']));
			}
			//客户名称
			if(isset($_GET['client_name']) && $_GET['client_name'] != "")
			{
				$map['B.client_name'] = array('like','%'.trim($_GET['client_name']).'%');
				$this -> assign("client_name",trim($_GET['client_name']));
			}
			//负责人
			if(isset($_GET['responsible']) && $_GET['responsible'] != "")
			{
				$map['A.responsible'] = array('like','%'.trim($_GET['responsible']).'%');
				$this -> assign("responsible",trim($_GET['responsible']));
			}
			//月份-开始-结束
			if(isset($_GET['start_date']) && $_GET['start_date'] != "")
			{
				$start_m = trim($_GET['start_date']);
				if(isset($_GET['end_date']) && $_GET['end_date'] != "")
				{
					$end_m = trim($_GET['end_date']);
					$map['A.create_tm'] = array('between',strtotime($start_m).','.strtotime($end_m));
					$this -> assign("end_date",trim($_GET['end_date']));
				}else{
					$map['A.create_tm'] = array('egt',strtotime($start_m));
				}
				$this -> assign("start_date",trim($_GET['start_date']));
			}else{
				if(isset($_GET['end_date']) && $_GET['end_date'] != "")
				{
					$end_m = trim($_GET['end_date']);
					$map['A.create_tm'] = array('elt',strtotime($end_m));
					$this -> assign("end_date",trim($_GET['end_date']));
				}
			}
			//签订日期-开始-结束
			if(isset($_GET['sign_date_start']) && $_GET['sign_date_start'] != "")
			{
				$start_m = trim($_GET['sign_date_start']);
				if(isset($_GET['sign_date_end']) && $_GET['sign_date_end'] != "")
				{
					$end_m = trim($_GET['sign_date_end']);
					$map['A.sign_date'] = array('between',$start_m.','.$end_m);
					$this -> assign("sign_date_end",trim($_GET['sign_date_end']));
				}else{
					$map['A.sign_date'] = array('egt',$start_m);
				}
				$this -> assign("sign_date_start",trim($_GET['sign_date_start']));
			}else{
				if(isset($_GET['sign_date_end']) && $_GET['sign_date_end'] != "")
				{
					$end_m = trim($_GET['sign_date_end']);
					$map['A.sign_date'] = array('elt',$end_m);
					$this -> assign("sign_date_end",trim($_GET['sign_date_end']));
				}
			}

			$map['A.status'] = '1';
			$map['_string'] = "A.client_id = B.id";
			$model = new Model();
			$count = $model -> table($table)  -> where($map) -> count();
			import("@.ORG.Page");

			$Page=new Page($count,50);
			$lists = $model -> table($table) -> field($field) -> where($map) -> limit($Page->firstRow.','.$Page->listRows) -> order($order) -> select();
			if($_GET['export'] == '1')
			{
				//2014.12.16 jiwei
				//$export_lists = $model -> table($table) -> field($field) -> where($map) -> order($order) -> select();
				//$this->export($export_lists,"合同列表_".date('Y-m-d-h-i').".csv");
				$this->export($lists,"合同列表_".date('Y-m-d-h-i').".csv");
			}
			$this->assign("lists",$lists);
			$Page->setConfig('header','条记录');
			$Page->setConfig('first','<<');
			$Page->setConfig('last','>>');
			$show =$Page->show();
			$this->assign ( "page", $show );
			$this -> display();
		}
		/**
		 * 导出报表
		 */
		public function export($list,$filename)
		{
			
			header("Content-type:text/csv");
			header("Content-Disposition:attachment;filename=".$filename);
			header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
			header('Expires:0');
			header('Pragma:public');
			$str = "";
			if(empty($list))
			{
				$str.=iconv('utf-8','gb2312','没有任何信息');
			}else
			{
				echo iconv('utf-8','gb2312','合同ID').",";
				echo iconv('utf-8','gb2312','框架协议').",";
				echo iconv('utf-8','gb2312','合同编号').",";
				echo iconv('utf-8','gb2312','客户名称').",";
				echo iconv('utf-8','gb2312',"起始日期").",";
				echo iconv('utf-8','gb2312',"终止日期").",";
				echo iconv('utf-8','gb2312',"合作软件").",";
				echo iconv('utf-8','gb2312',"签订日期").",";
				echo iconv('utf-8','gb2312',"负责人").",";
				echo iconv('utf-8','gb2312',"折扣后总价").",";
				echo iconv('utf-8','gb2312',"刊例总价")."\r\n";
				foreach ($list as $key => $val)
				{
					echo iconv('utf-8','gb2312',$val['id']).",";
					echo iconv('utf-8','gb2312',$val['agreement_code']).",";
					echo iconv('utf-8','gb2312',$val['contract_code']).",";
					echo iconv('utf-8','gb2312',$val['client_name']).",";
					echo iconv('utf-8','gb2312',$val['start_date']).",";
					echo iconv('utf-8','gb2312',$val['end_date']).",";
					echo iconv('utf-8','gb2312',$val['package_num']).",";
					echo iconv('utf-8','gb2312',$val['sign_date']).",";
					echo iconv('utf-8','gb2312',$val['responsible']).",";
					echo iconv('utf-8','gb2312',$val['app_discount_total']).",";
					echo iconv('utf-8','gb2312',$val['app_original_total'])."\r\n";
				}
			}
			exit;
		}

		/**
		 * 合同详情页面
		 */
		public function contract_detail()
		{
			$contract_id = trim($_GET['contract_id']);
			if(!isset($contract_id) || $contract_id == "")
			{
				$this->error("合同id不存在！");
			}
			//根据合同id取出合同表中的数据
			$Contract_db = D("Settlement.Contract");
			$map['id']= array('eq',$contract_id);
			$data = $Contract_db -> where($map) -> select();
			//根据client_id取出客户名称
			$Client_db = D("Settlement.Client");
			$data[0]['client_name'] = $Client_db -> where("id=".$data[0]['client_id']) -> getField("client_name");
			//根据合同id取出附件
			$Attachment_db = D("Settlement.Attachment");
			$attachments = $Attachment_db -> where("contract_id=".$contract_id) -> select();
			//根据合同id取出合作软件
			$App_db = D("Settlement.ContractApp");
			$where['contract_id'] = array('eq',$contract_id);
			$app = $App_db -> where($where) -> select();
			//取出合作软件对应的广告为
			if(!empty($app))
			{
				$Ad_db = D("Settlement.Advertising");
				foreach ($app as $key => $a)
				{
					$app[$key]['Advertising'] = $Ad_db -> where("id=".$a['advertising_id']) -> select();
				}
			}
// 			$this->showArr($data);
			$this->assign("contract",$data[0]);
			$this->assign("apps",$app);
			$this->assign("attachments",$attachments);
			$this -> display();
		}
		/**
		 *添加合同——显示
		 */
		public function add_contract_show()
		{
			//取出所有客户信息
			$Client_db = D("Settlement.Client");
			
			//2014.10.30 jiwei
			//处理按客户名称排序
			//$clients = $Client_db -> where("status=1") -> order("create_tm DESC") -> select();
			$clients = $Client_db -> where("status=1") -> order("CONVERT(client_name USING gbk)") -> select();
			
			$this->assign("clients",$clients);
			//取出所有的框架协议
			$Agreement_db = D("Settlement.Agreement");
			$agreements = $Agreement_db -> where("status = 1") -> field("id,agreement_code") -> order("create_tm") -> select();
			$this->assign("agreements",$agreements);
			//url_suffix
			$this->assign("url_suffix",$_GET['url_suffix']);
			$this -> display();
		}

		/**
		 *添加合同——执行
		 */
		public function add_contract_do()
		{
			$data['client_id'] = trim($_POST['client_id']);
			$data['agreement_id'] = trim($_POST['agreement_id']);
			$data['contract_code'] = trim($_POST['contract_code']);
			$data['sign_date'] = trim($_POST['sign_date']);
			$data['start_date'] = trim($_POST['start_date']);
			$data['end_date'] = trim($_POST['end_date']);
			$data['responsible'] = trim($_POST['responsible']);
			$data['admin_id'] = $_SESSION['admin']['admin_id'];
			$data['admin_name'] = $_SESSION['admin']['admin_user_name'];
			
			//2014.10.30 jiwei
			//增加对月份字段的处理
			$_date = explode('-', trim($_POST['month']));
			if($_date[1]<10)
				$data['month'] = $_date[0].'0'.(int)$_date[1];
			else 
				$data['month'] = $_date[0].$_date[1];
			
			//取得协议编号
			$Agreement_db = D("Settlement.Agreement");
			$data['agreement_code'] = $Agreement_db -> where("id=".$data['agreement_id']) -> getField("agreement_code");
			foreach ($data as $d)
			{
				if($d == "" || !isset($d))
				{
					$this->error("抱歉，数据不全，无法提交！");
				}
			}
			//取出合同编号的前六位，并判断是否为日期

			//$data['month'] = substr($data['contract_code'], 0,6); //2014.10.30 jiwei
			if(!is_numeric($data['month']))
			{
				$this->error("合同编号不规范，应该以“年份月份”开始命名，如“20140920”。");
			}
			if($data['month'] < 197001 || $data['month'] > 210012)
			{
				$this->error("合同编号命名出错，合同编号的应以“年份月份”开始，如“20140920”，且年份段必须在“1970-2100”之间。");
			}

			$data['remark'] = trim($_POST['remark']);
			//计算附件数量
			$attachment_ids = $_POST['attachment_id'];
			$new_attachment_num = count($attachment_ids);
			$data['attachment_num'] = $new_attachment_num;
			$data['create_tm'] = time();
			$data['update_tm'] = time();

			//准备保存数据
			$Contract_db = D("Settlement.Contract");
			//启动事务
			$Contract_db -> startTrans();
			$new_contract_id = $Contract_db -> add($data);
			if(!$new_contract_id)
			{
				//存储失败，回滚操作
				$Contract_db -> rollback();
				$this->error("录入合同失败，请重试！");
			}
			//将合同id更新到附件表
			if(!empty($attachment_ids))
			{
				$Attachment_db = D("Settlement.Attachment");
				$new['contract_id'] = $new_contract_id;
				foreach ($attachment_ids as $id)
				{
					if($Attachment_db -> where("id=".$id) -> save($new) === false)
					{
						//操作失败，回滚
						$Contract_db -> rollback();
						$this -> error("录入新合同失败，请重试！");
					}
				}
			}
			//更新ad_agreements表，字段contract_num加1

			$where['id'] = array('eq',$data['agreement_id']);
			$agreement_old_contract_num = $Agreement_db -> where($where) -> getField("contract_num");
			$new_data['update_tm'] = time();
			//合同数+1
			$new_data['contract_num'] = $agreement_old_contract_num + 1;
			if($Agreement_db -> where($where) -> save($new_data) === false)
			{
				//操作失败，回滚
				$Contract_db -> rollback();
				$this -> error("录入新合同失败，请重试！");
			}

			//更新到ad_clients表，列contract_num加1
			$conditios['id'] = array('eq',$data['client_id']);
			$Client_db = D("Settlement.Client");
			$client_old_contract_num = $Client_db -> where($conditios) -> getField("contract_num");
			//合同数+1
			$save['contract_num'] = $client_old_contract_num + 1;
			$save['update_tm'] = time();
			if($Client_db -> where($conditios) -> save($save) === false)
			{
				//操作失败，回滚
				$Contract_db -> rollback();
				$this -> error("录入新合同失败，请重试！");
			}
			//操作成功！
			$Contract_db -> commit();
			
			
			//邮件报警
			$AlarmNoti_db = D("Settlement.AlarmNoti");
			
			//修改数据发邮件报警使用
			list($soData) = $Contract_db -> where(array('id' => $new_contract_id )) -> select();
			
			$dates = array($save['sign_date'],$save['start_date'],$save['end_date']);

			$result = $AlarmNoti_db->on("add_contract",$dates,$soData);

			
			
			//写日志
			$this -> writelog("广告结算：在框架协议id[".$data['agreement_id']."]下新录入了一条合同，合同id为[".$new_contract_id."]。【广告结算】", 'sj_ad_tj_soft', $new_contract_id,__ACTION__ ,'','add');
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/ContractApp/detail/contract_id/'.$new_contract_id);
			$this -> success("添加合同成功！");

		}
		/**
		 *编辑合同——显示
		 */
		public function edit_contract_show()
		{
			$contract_id = trim($_GET['id']);
			if(!isset($contract_id) || $contract_id == "")
			{
				$this -> error("合同id不存在！");
			}
			//根据合同id取出数据
			$Contract_db = D("Settlement.Contract");
			$map['id'] = array('eq',$contract_id);
			$map['status'] = '1';
			$c = $Contract_db -> where($map) -> select();
			$ls = $c[0];
			//根据client_id取出客户名称
			if($ls['attachment_num'] > 0)
			{
				//根据合同id取出附件
				$Attachment_db = D("Settlement.Attachment");
				$where['contract_id'] = array('eq',$contract_id);
				$a = $Attachment_db -> where($where) -> select();
				$this->assign("attachments",$a);
			}
			//根据客户id取出客户名称
			$Client_db = D("Settlement.Client");
			$ls['client_name'] = $Client_db -> where("id=".$ls['client_id']) -> getField("client_name");
			$this -> assign("contract",$ls);
			$this -> display();
		}
		/**
		 *编辑合同——执行
		 */
		public function edit_contract_do()
		{
			$save['id'] = trim($_POST['id']);
			$save['sign_date'] = trim($_POST['sign_date']);
			$save['start_date'] = trim($_POST['start_date']);
			$save['end_date'] = trim($_POST['end_date']);
			$save['responsible'] = trim($_POST['responsible']);
			
			//2014.12.11 jiwei
			//增加对月份字段的处理
			$_date = explode('-', trim($_POST['month']));
			if($_date[1]<10)
				$save['month'] = $_date[0].'0'.(int)$_date[1];
			else
				$save['month'] = $_date[0].$_date[1];
			//end
			
			foreach ($save as $s)
			{
				if(!isset($s) || $s == "")
				{

					$this -> error("数据不全，无法编辑。");
				}
			}
			//获取附件数量
			$attachment_num = count($_POST['attachment_id']);
			$save['attachment_num'] = $attachment_num;
			$save['update_tm'] = time();
			$save['remark'] = trim($_POST['remark']);
			//准备更新
			$Contract_db  = D("Settlement.Contract");
			

			//修改数据发邮件报警使用
			list($soData) = $Contract_db -> where(array('id' => $save['id'])) -> select();
			

			//事务启动
			$Contract_db -> startTrans();
			$log_result = $this->logcheck(array('id'=>$save['id']), 'settlement.ad_contracts', $save, $Contract_db);
			if($Contract_db -> where("id=".$save['id']) -> save($save) === false)
			{
				//操作失败，回滚
				$Contract_db -> rollback();
				$this -> error("编辑失败。");
			}

			//更新附件

			if($attachment_num > 0)
			{
				$Attachment_db = D("Settlement.Attachment");
				foreach ($_POST['attachment_id'] as $aid)
				{
					if($Attachment_db -> where("id=".$aid) -> setField("contract_id",$save['id']) === false)
					{
						//操作失败，回滚
						$Contract_db -> rollback();
						$this -> error("编辑失败。");
					}
				}
			}

			//操作成功，提交
			$Contract_db -> commit();
			
			//邮件报警
			$AlarmNoti_db = D("Settlement.AlarmNoti");
			
			$dates = array($save['sign_date'],$save['start_date'],$save['end_date']);

			$result = $AlarmNoti_db->on("edit_contract",$dates,$save,$soData);

			//写日志
			$this -> writelog("广告结算：编辑了id为[".$save['id']."]的合同".$log_result, 'sj_ad_tj_soft', $save['id'],__ACTION__ ,'','edit');
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Contract/index'.base64_decode($_POST['url_suffix']));
			$this -> success("编辑合同成功！");


		}

		/**
		 * ajax检查合同编号是否存在
		 */
		public function ajax_check_contract_code()
		{
			$contract_code = trim($_POST['code']);
			if(!isset($contract_code) || $contract_code == "")
			{
				echo "0";exit();
			}
			$Contract_db = D("Settlement.Contract");
			$res = $Contract_db -> where("contract_code='".$contract_code."'") -> count();
			if($res > 0)
			{
				echo "1";exit();
			}else{
				echo "0";exit();
			}
		}
		/**
		 * ajax取得框架协议信息——通过client_id
		 */
		public function ajax_get_agreement_info_by_client_id()
		{
			$client_id = trim($_POST['client_id']);
			$map['client_id'] = array('eq',$client_id);
			$map['status'] = array('eq','1');
			$Agreement_db = D("Settlement.Agreement");
			$data = $Agreement_db -> where($map) -> field("id,agreement_code") -> order("create_tm DESC") -> select();
			if(empty($data))
			{
				echo '<option value="-1">暂未签订框架协议</option>';
				exit();
			}else{
				foreach ($data as $d)
				{
					echo '<option value="'.$d['id'].'">'.$d['agreement_code'].'</option>';
				}
				exit();
			}
		}
		/**
		 * 删除合同
		 */
		public function delete_contract()
		{
			$map['id'] = trim($_GET['id']);
			if(!isset($map['id']) || $map['id'] == "")
			{
				//id不存在
				$this -> error("合同id号 不存在！");
			}
			$Contract_db = D("Settlement.Contract");
			
			//2015.5.4 jiwei
			//如果软件数量不为0，则不允许删除合同，需要先删除软件才可以删合同
			if($Contract_db -> where($map) -> getField("app_num")>0)
				$this->error("合同中有已经排期的软件，请先删除软件再删除合同！");
			
			//修改数据发邮件报警使用
			list($soData) = $Contract_db -> where($map) -> select();
			//获取client_id
			$client_id = $Contract_db -> where($map) -> getField("client_id");
			//获取协议id
			$agreement_id = $Contract_db -> where($map) -> getField("agreement_id");
			//启动事务
			$Contract_db -> startTrans();
			if($Contract_db -> where($map) -> setField("status",'0') === false)
			{
				//操作失败，回滚操作
				$Contract_db -> rollback();
				$this -> error("删除失败！");
			}
			//影响了ad_agreements表的“contract_num”列，自减一
			$Agreement_db = D("Settlement.Agreement");
			if($Agreement_db -> where("id=".$agreement_id) -> setDec("contract_num") === false)
			{
				//操作失败，回滚操作
				$Contract_db -> rollback();
				$this -> error("删除失败！");
			}

			//影响了ad_clients表中的“contract_num”列，自减一
			$Client_db = D("Settlement.Client");
			if($Client_db -> where("id=".$client_id) -> setDec("contract_num") === false)
			{
				//操作失败，回滚操作
				$Contract_db -> rollback();
				$this -> error("删除失败！");
			}

			/*@说明：
			 * 与合同相关的附件（ad_attachments）暂不处理
			 */
			$Contract_db -> commit();
			
			//邮件报警
			$AlarmNoti_db = D("Settlement.AlarmNoti");
			
			$dates = array($soData['sign_date'],$soData['start_date'],$soData['end_date']);

			$result = $AlarmNoti_db->on("del_contract", $dates, array(), $soData);
			
			
			$this -> writelog("广告结算：删除了id为[".$map['id'].']的合同。', 'sj_ad_tj_soft', $map['id'],__ACTION__ ,'','del');
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Contract/index'.base64_decode($_GET['url_suffix']));
			$this -> success("删除合同成功！");


		}

            /*导入数据*/
            public function import_data() 
            {
		import("@.ORG.UploadFile");
		$info = NULL;
		$upload = new UploadFile();
		$upload->maxSize = 3145728;
		$upload->allowExts = array('csv');
		$upload->savePath = '/tmp/';//'/data/att/518/settlement/';
		$upload->saveRule = 'time';
		
		if(!$upload->upload())
		{
			echo "<script>alert('".$upload->getErrorMsg()."');location.href='/index.php/Settlement/Contract/index';</script>";
			exit;
		}
		else 
			$info = $upload->getUploadFileInfo();
		
		if(is_null($info))
		{
			echo "<script>alert('没有获得上传文件信息！');location.href='/index.php/Settlement/Contract/index'</script>";
			exit;
		}

                // 处理数据 读取csv数据
                $handle = fopen($info[0]['savepath'].$info[0]['savename'], "r");
                if ($handle === false) {
                    return -1;
                }
                $i = $j = 0;
                $content_arr = array();
                while (($line_arr = $this->mygetcsv($handle, 1000, ",")) != FALSE) {
                    if ($i == 0) {
                        // 读入标题列
                        $title_arr = $line_arr;
                    } else {
                        // 读入每行内容
                        $content_arr[$j] = $line_arr;
                        $j++;
                    }
                    $i++;
                }
                fclose($handle);

		$Agreement_db = D("Settlement.Agreement");
		$Contract_db = D("Settlement.Contract");
		$Client_db = D("Settlement.Client");
                
                $error_ids = '';
                $succ_ids = '';
                foreach($content_arr as $v){
                        $ht_id = $v[1];
                        if(empty($v[0])||empty($v[1])||empty($v[2])||empty($v[3])||empty($v[4])||empty($v[5])||empty($v[6])||empty($v[7])){
                                $error_ids=$error_ids.$ht_id.',';
                                continue;
                        }
                        $data['agreement_id'] = $v[0];
			$data['agreement_code'] = $Agreement_db -> where("id=".$data['agreement_id']) -> getField("agreement_code");
                        $data['client_id'] = $Agreement_db -> where("id=".$data['agreement_id']) -> getField("client_id");

                        $rs_id = $Contract_db -> where("id=".$ht_id." and status=1") -> getField("id");
                        $rs_ht_code = $Contract_db -> where("contract_code='".trim($v[2])."' and status=1") -> getField("id");

                        if(!empty($rs_id)||!empty($rs_ht_code)){
                                $error_ids=$error_ids.$ht_id.',';
                                continue;
                        }

			$data['id'] = trim($v[1]); //合同ID
			$data['contract_code'] = trim($v[2]);
			$data['sign_date'] = trim($v[4]);
			$data['start_date'] = trim($v[5]);
			$data['end_date'] = trim($v[6]);
			$data['responsible'] = trim(iconv('gb2312','utf-8',$v[7]));
			$data['admin_id'] = $_SESSION['admin']['admin_id'];
			$data['admin_name'] = $_SESSION['admin']['admin_user_name'];
			
			$data['month'] = trim($v[3]);
			//2014.10.30 jiwei
			//增加对月份字段的处理
                        //
                        /*
			$_date = explode('-', trim($v[3]));
			if($_date[1]<10)
				$data['month'] = $_date[0].'0'.(int)$_date[1];
			else 
				$data['month'] = $_date[0].$_date[1];
                            */

                        //todo
                        /*
                        是否日期命名？  那个月的处理是对的么 补000
			foreach ($data as $d)
			{
				if($d == "" || !isset($d))
				{
					$this->error("抱歉，数据不全，无法提交！");
				}
			}
                        
			//取出合同编号的前六位，并判断是否为日期

			//$data['month'] = substr($data['contract_code'], 0,6); //2014.10.30 jiwei
			if(!is_numeric($data['month']))
			{
				$this->error("合同编号不规范，应该以“年份月份”开始命名，如“20140920”。");
			}
			if($data['month'] < 197001 || $data['month'] > 210012)
			{
				$this->error("合同编号命名出错，合同编号的应以“年份月份”开始，如“20140920”，且年份段必须在“1970-2100”之间。");
			}*/

			$data['remark'] = trim(iconv('gb2312','utf-8',$v[8]));
			$data['attachment_num'] = 0;
			$data['create_tm'] = time();
                        $data['update_tm'] = time();

			//准备保存数据
			//启动事务
			$Contract_db -> startTrans();
			$new_contract_id = $Contract_db -> add($data);
			if(!$new_contract_id)
			{
				//存储失败，回滚操作
				$Contract_db -> rollback();
                                $error_ids=$error_ids.$ht_id.',';
                                continue;
			        //echo "<script>alert('录入合同失败，请重试！');location.href='/index.php/Settlement/Contract/index'</script>";
				//$this->error("录入合同失败，请重试！");
			}

			//更新ad_agreements表，字段contract_num加1

			$where['id'] = array('eq',$data['agreement_id']);
			$agreement_old_contract_num = $Agreement_db -> where($where) -> getField("contract_num");
			$new_data['update_tm'] = time();
			//合同数+1
			$new_data['contract_num'] = $agreement_old_contract_num + 1;
			if($Agreement_db -> where($where) -> save($new_data) === false)
			{
				//操作失败，回滚
				$Contract_db -> rollback();
                                $error_ids=$error_ids.$ht_id.',';
                                continue;
			        //echo "<script>alert('录入合同失败2，请重试！');location.href='/index.php/Settlement/Contract/index'</script>";
				//$this -> error("录入新合同失败，请重试！");
			}

			//更新到ad_clients表，列contract_num加1
			$conditios['id'] = array('eq',$data['client_id']);
			$client_old_contract_num = $Client_db -> where($conditios) -> getField("contract_num");
			//合同数+1
			$save['contract_num'] = $client_old_contract_num + 1;
			$save['update_tm'] = time();
			if($Client_db -> where($conditios) -> save($save) === false)
			{
				//操作失败，回滚
				$Contract_db -> rollback();
                                $error_ids=$error_ids.$ht_id.',';
                                continue;
			        //echo "<script>alert('录入合同失败3，请重试！');location.href='/index.php/Settlement/Contract/index'</script>";
				//$this -> error("录入新合同失败，请重试！");
			}
			//操作成功！
                        $succ_ids = $succ_ids.$ht_id.',';
			$Contract_db -> commit();
                }

                $error_ids =substr($error_ids,0,-1);
                $succ_ids =substr($succ_ids,0,-1);
                if($error_ids!=''){
		    echo "<script>alert('ID为".$error_ids."的数据导入失败,请检查必要字段是否为空，或者合同ID合同编号有重复!');location.href='/index.php/Settlement/Contract/index'</script>";
                }else{
		    echo "<script>alert('导入成功');location.href='/index.php/Settlement/Contract/index'</script>";
                }
			
		//写日志
                if($succ_ids!=''){
		    $this -> writelog("广告结算:批量导入了合同,ID为".$succ_ids);
                }
            }
}
