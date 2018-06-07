<?php
    class Search_first_softAction extends CommonAction {

        //上传关键词  added by shitingting 2015-01-06
        public function up_download_search()
		{
            if($_FILES)
			{
			    $model=M('sj_upload_jobs');
				$filename=$_FILES['upload_file']['tmp_name'];
				$err = $_FILES["upload_file"]["error"];
				if(empty($filename))
				{
				  $error1=-1;
				}
				$file_name_csv=$_FILES['upload_file']['name'];
				$tmp_arr = explode(".", $file_name_csv);
				$name_suffix = array_pop($tmp_arr);
				if (strtoupper($name_suffix) != "CSV") 
				{
				 $error2=-2;
				}
				if($error1!=-1&&$error2!=-2)
				{
					// 保存文件
					$path=date("/Ym/d/",time());
					$save_dir = C("SEARCH_FIRST_SOFT").$path;
					$this->mkDirs($save_dir);
					
					$save_name = MODULE_NAME.'_' . time() . '_' . $tmp_arr[0] . '.csv';
					
					$save_db=$path.$save_name;
					$save_file_name = $save_dir . $save_name;
					$old_file=move_uploaded_file($_FILES['upload_file']['tmp_name'], $save_file_name);
				}
				if($old_file)
				{
					 $data=array(
						 'file_name'=>$save_db,
						 'state'=>0,
						 'create_tm'=>time(),
						 'update_tm'=>time(),
						 'status'=>1,
						 'admin_id'=>$_SESSION['admin']['admin_id'],
						);
					$affect = $model -> table('sj_upload_jobs') -> add($data);
					if($affect)
					{
					 $this -> writelog('市场搜索管理_搜索软件下载中 在sj_upload_jobs添加了id'.affect.'下载任务','sj_upload_jobs',$affect,__ACTION__ ,"","add");
					 $success=1;
					 echo '{"file_name_path":"' . $save_file_name . '","error1":"'.$error1.'","error2":"'.$error2.'","path":"'.$path.'","success":"'.$success.'"}';	 
					}
					else
					{
					 $success=0;
					 echo '{"file_name_path":"' . $save_file_name . '","error1":"'.$error1.'","error2":"'.$error2.'","path":"'.$path.'","success":"'.$success.'"}';
					}
				}
				
			}
			else
			{
			 $model=M('sj_upload_jobs');
			 import("@.ORG.Page");
			 $count = $model->table('sj_upload_jobs')->where(array('status' => 1))->count();
			 $param = http_build_query($_GET);
			 $Page = new Page($count, 20, $param);
			 $result = $model -> table('sj_upload_jobs') -> where(array('status' => 1)) -> order('create_tm DESC')->limit($Page->firstRow . ',' . $Page->listRows) -> select();
			 $show = $Page->show();
			 if($_GET['lr']){
				$lr = $_GET['lr'];
			 }else{
				$lr = 20;
			 }
			 if($_GET['p']){
				$p = $_GET['p'];
			 }else{
				$p = 1;
			 }
			 $Page->setConfig('header', '篇记录');
			 $Page->setConfig('`first', '<<');
			 $Page->setConfig('last', '>>');
			 $this -> assign('lr',$lr);
			 $this -> assign('p',$p);
			 $this -> assign("page", $show);
			 $this -> assign('result',$result);
			 
             $this->display();
			}
        }
		
		// 下载
		function down_first_soft() 
		{
			$url=C("SEARCH_FIRST_SOFT").base64_decode($_GET['file_name']);
			//$path=date("/Ym/d/",time());
			//$save_dir = C("SEARCH_FIRST_SOFT").$path.$url;
			$file_name = 'first_soft_'.time(); 
			if($url)
			{
				$file = fopen($url,"r");
				Header("Content-type: application/octet-stream");
				Header("Accept-Ranges: bytes");
				Header("Accept-Length: ".filesize($url));
				Header("Content-Disposition: attachment; filename=" . urlencode($file_name . "_detail_info") . ".csv");
				echo fread($file, filesize($url));
				fclose($file);
				exit(0); 
			}
			else
			{
				header("Content-type: text/html; charset=utf-8");
				echo "您还没有上传搜索词!";
				exit;
			}
		}
        function delete_file()
		{
			$model = M('sj_upload_jobs');
			$id = $_GET['id'];
			$result = $model -> table('sj_upload_jobs') -> where(array('id' => $id)) -> save(array('status' => 0,'update_tm'=>time()));
			if($result)
			{
				$this -> writelog("已删除id为{$id}的搜索文件记录",'sj_upload_jobs',$id,__ACTION__ ,"","del");
				$this->assign('jumpUrl','/index.php/Sj/Search_first_soft/up_download_search');
				$this -> success("删除成功");
			}
			else
			{
				$this -> error("删除失败");
			}	
		}
		
    }
