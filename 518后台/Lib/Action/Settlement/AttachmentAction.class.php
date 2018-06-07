<?php
/**
 * 安智网产品管理平台 广告结算控制器
 * ============================================================================
 * 版权所有 2009-2014 北京力天无限网络有限公司，并保留所有权利。
 * 网站地址: http://www.goapk.com；
 * author：L&J
 *上传附件专用
 * ----------------------------------------------------------------------------
*/
	class AttachmentAction extends CommonAction {

		public $allow_ext = array('doc','docx','xls','xlsx','pdf','jpg','jpeg','png','gif','csv');
		 public $max_size = 10485760;//10M
		/**
		 *ajax上传附件
		 *return：json格式，
		 */
		 public function ajax_upload_file()
		 {
			$return = array();
			$return['error'] = 0;
			$attachment = array();
			$attachment['admin_id'] = $_SESSION['admin']['admin_id'];
			$attachment['admin_name'] = $_SESSION['admin']['admin_user_name'];
			$attachment['path'] = $_SESSION['admin']['admin_id']."/".date("Ymd")."/";
			$attachment['create_tm'] = time();
			$save_path =  "/data/att/518/settlement/".$_SESSION['admin']['admin_id']."/".date("Ymd");
			$uploaded_file = $_FILES['affix'];


			if($uploaded_file['error'])
			{
				$return['error'] = 1;
				$return['msg'] = "未选择文件！";
			}else
			{
				//获取文件扩展名
				$ext = strtolower(substr($uploaded_file['name'], strrpos($uploaded_file['name'], '.')+1));
				//检查扩展名是否允许
				if(!in_array($ext, $this -> allow_ext))
				{
					$return['error'] = 1;
					$return['msg'] = "不允许上传".$ext."类型的文件。";
				}
				//判断上传大小
				$filesize = $uploaded_file['size'];
				if($filesize > $this -> max_size)
				{
					$return['error'] = 1;
					$return['msg'] = "文件大小超出限制！";
				}
				//判断文件目录是否存在
				if(!is_dir($save_path))
				{
					mkdir($save_path,0777,true);
					chmod($save_path, 0777);
				}
				$save_name = "ad_settlement_".date("YmdHis").".".$ext;
				if (!file_exists($save_path ."/".$save_name))
				{
					if(!move_uploaded_file($uploaded_file["tmp_name"],$save_path ."/".$save_name))
					{
						$return['error'] = 1;
						$return['msg'] = "上传失败！";

					}
					$attachment['filename'] = $save_name;
					$attachment['custom_name'] = $uploaded_file['name'];
					$attachment['extname'] = $ext;
					$attachment_db = D("Settlement.Attachment");
					$res = $attachment_db->add($attachment);
					if($res)
					{
						$return['attachment_id'] = $res;
						$return['filename'] = $attachment['filename'];
						$return['path'] = $attachment['path'];
					}else{
						$return['error'] = 1;
						$return['msg'] = "存入数据库失败！";
					}
				}

			}


			echo json_encode($return);
			exit;
		 }

		/**
		 *ajax删除附件
		 */
		 public function ajax_delete_file()
		 {
			$attachment_id = trim($_GET['attachment_id']);
			if($attachment_id == "" || !isset($attachment_id))
			{
				echo "-1";
				exit();
			}
			$attachment_db = D("Settlement.Attachment");
			//根据id号从数据库中取出数据
			$data = $attachment_db->where("id='".$attachment_id."'")->select();
			if(count($data) == 0)
			{
				echo "-2";exit();
			}
			//该操作来自框架协议编辑--删除附件
             $agreement_id = trim($_GET['agreement_id']);
             if(isset($agreement_id) && $agreement_id)
             {
             	//该操作来自框架协议编辑
                 //开始事务
                 $attachment_db -> startTrans();
                 //ad_agreements表中附件数量自减一
                 $agreement_db = D("Settlement.Agreement");
                 $res_agreement = $agreement_db -> where("id=".$agreement_id) -> setDec("attachment_num");
             }
			//处理来自合同列表的删除附件
			$contract_id = trim($_GET['contract_id']);
			if(isset($contract_id) && $contract_id)
			{
				//该操作来自框架协议编辑
				//开始事务
				$attachment_db -> startTrans();
				//ad_contracts表中附件数量自减一
				$contract_db = D("Settlement.Contract");
				$res_contract = $contract_db -> where("id=".$contract_id) -> setDec("attachment_num");
			}
			$filename="/data/att/518/settlement/".$data[0]['path'].$data[0]['filename'];
             $res_attachment = $attachment_db->where("id='".$attachment_id."'")->delete();

			if($res_attachment)
			{
				if($res_agreement || $res_contract)
				{
               	 	$attachment_db -> commit();
				}
				unlink($filename);
				echo "1";
			}else
			{
				if($res_agreement || $res_contract)
				{
					$attachment_db -> rollback();
				}
				echo "0";
				exit();
			}

		 }

		 /**
		  * 附件下载
		  *
		  */
		 public function download()
		 {
		 	$attachment_id = trim($_GET['attachment_id']);
		 	$Attachment_db = D("Settlement.Attachment");

		 	$tmp = $Attachment_db -> where("id=".$attachment_id) -> select();
		 	$a = $tmp[0];
		 	$filename = $a['custom_name'];
		 	$ext = $a['extname'];

		 	$file = "/data/att/518/settlement/".$a['path'].$a['filename'];

		 	if(file_exists($file))
		 	{
			 	header("Content-type:text/".$ext);
			 	header("Content-Disposition:attachment;filename=".$filename);
			 	header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
			 	header('Expires:0');
			 	header('Pragma:public');
			 	header ( 'Content-Length: '  .  filesize ( $file ));
			 	ob_clean ();
			 	flush ();
			 	readfile ( $file );
		 	}else{
		 		$this -> error("附件不存在！");
		 	}

		 }

	}