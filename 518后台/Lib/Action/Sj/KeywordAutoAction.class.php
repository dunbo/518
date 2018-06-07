<?php
//关键词自动补全
class KeywordAutoAction extends CommonAction {
	
	public function import_keyword(){
		if(!empty($_POST)) {
			$table_1 = $_POST['table_data_1']?$_POST['table_data_1']:'';
			$table_2 = $_POST['table_data_2']?$_POST['table_data_2']:'';
			$table_3 = $_POST['table_data_3']?$_POST['table_data_3']:'';
			$product_id = $_POST['product_id'] ? $_POST['product_id'] : 1;
			if(empty($table_1) && empty($table_2) && empty($table_3)) {
				$this->error('请上传csv文件');
			}
			$model = M('');
			
			if($table_1) {
				$model->execute("update sj_keyword_auto set `status` = 0 where type=1 and pid={$product_id}");
				$data = array(
						'table_path'	=>	$table_1,
						'status'	=>	1,
						'type'		=>	1,
						'pid' => $product_id,
						'create_tm'	=>	time(),
				);
				$result = $model->table('sj_keyword_auto')->add($data);
			}
			if($table_2) {
				$model->execute("update sj_keyword_auto set `status` = 0 where type=2 and pid={$product_id}");
				$data = array(
						'table_path'=>	$table_2,
						'status'	=>	1,
						'type'		=>	2,
						'pid' => $product_id,						
						'create_tm'	=>	time(),
				);
				$result = $model->table('sj_keyword_auto')->add($data);
			}
			if($table_3) {
				$model->execute("update sj_keyword_auto set `status` = 0 where type=3 and pid={$product_id}");
				$data = array(
						'table_path'=>	$table_3,
						'status'	=>	1,
						'type'		=>	3,
						'pid' => $product_id,						
						'create_tm'	=>	time(),
				);
				$result = $model->table('sj_keyword_auto')->add($data);
			}
			if( $result ) {
				$this -> writelog("已添加id为{$result}的关键字自动补全文件",'sj_keyword_auto',$result,__ACTION__ ,'','add');
				$this -> assign('jumpUrl','/index.php/Sj/AdList/index');
				$this->success('添加成功');
			}else {
				$this->error('添加失败');
			}
		}else {
			#产品列表
			$product_model = M();
			$product_list = $product_model ->table('pu_product')->where('status = 1')->findAll();
			$this-> assign ("product_list", $product_list);			
			$this-> assign ("product_id", 1);		
			$this->display("import_keyword");
		}
	}
	
	
	public function csv_count()
	{
		$opt = $_GET['opt'];
		if($_FILES[$opt])
		{
			$filename=$_FILES[$opt]['tmp_name'];
			$file_name_csv=$_FILES[$opt]['name'];
			$tmp_arr = explode(".", $file_name_csv);
			$name_suffix = array_pop($tmp_arr);
		}
		if(empty($filename))
		{
			exit(json_encode(array('code'=>0,'msg'=>'请上传文件')));
		}
		if (strtoupper($name_suffix) != "CSV")
		{
			exit(json_encode(array('code'=>0,'msg'=>'请上传CSV类型的文件')));
		}
		$handle=fopen($filename,'r');
		$out = array ();
		$out_unique = array ();
		$n = 0;
		$i = 0;
		$str = '';
		while (!feof($handle))
		{
			$mm = fgets($handle);
			$j = $i+1;
			if($i != 0) {
				if(!$mm) {
					continue;
				}
				$clum = iconv('gb2312','utf-8', $mm);
				$clum_arr = explode(',', $clum);
				$out[] = $clum_arr;
				if($opt == 'table_1') {
					if(empty($clum_arr[0])) {
						exit(json_encode(array('code'=>0,'msg'=>"第{$j}项关键词不能为空")));
					}
					$out_unique[] = str_replace(array("\n","\r",'\n\r'),"",$clum_arr[0]);
				}elseif($opt == 'table_2') {
					if(empty($clum_arr[2])) {
						exit(json_encode(array('code'=>0,'msg'=>"第{$j}关键词有为空的项")));
					}
					if(empty($clum_arr[4])) {
						exit(json_encode(array('code'=>0,'msg'=>"第{$j}项包名不能为空")));
					}
					$out_unique[] = str_replace(array("\n","\r",'\n\r'),"",$clum_arr[2]).'--'.str_replace(array("\n","\r",'\n\r'),"",$clum_arr[4]);
				}elseif($opt == 'table_3') {
					if(empty($clum_arr[0])) {
						exit(json_encode(array('code'=>0,'msg'=>"第{$j}项包名不能为空")));
					}
					$out_unique[] = str_replace(array("\n","\r",'\n\r'),"",$clum_arr[0]);
				}
			}
			$i++;
		}
		
		fclose($handle);
		if(empty($out)) {
			exit(json_encode(array('code'=>0,'msg'=>'请先填好数据')));
		}
		$out_count = count($out);
		$out_unique_count = count( array_unique($out_unique) );
		if( $opt == "table_1" ) {
			$de = array_count_values($out_unique);
			//检查是那些关键词重复
			$kw_str = "";
			foreach ($de as $key => $v){
				if($v > 1){
					$kw_str .= $key.',';
				}
			}
			if($kw_str) {
				$error_msg = "关键词为".trim($kw_str,',').'有重复';
				exit(json_encode(array('code'=>0,'msg'=> $error_msg)));
			}
			if($out_count != $out_unique_count) {
				exit(json_encode(array('code'=>0,'msg'=>'关键词有重复')));
			}
		}elseif( $opt == "table_2" ) {
			$de = array_count_values($out_unique);
			//检查是那些关键词重复
			$kw_str = "";
			foreach ($de as $key => $v){
				if($v > 1){
					$kw_str .= $key.',';
				}
			}
			if($kw_str) {
				$error_msg = "同一关键词下的包名重复'".trim($kw_str,',')."'有重复";
				exit(json_encode(array('code'=>0,'msg'=> $error_msg)));
			}
			if($out_count != $out_unique_count) {
				exit(json_encode(array('code'=>0,'msg'=>'同一关键词下的包名重复')));
			}
		}elseif( $opt == 'table_3' ) {
			$de = array_count_values($out_unique);
			//检查是那些关键词重复
			$kw_str = "";
			foreach ($de as $key => $v){
				if($v > 1){
					$kw_str .= $key.',';
				}
			}
			if($kw_str) {
				$error_msg = "包名为".trim($kw_str,',').'有重复';
				exit(json_encode(array('code'=>0,'msg'=> $error_msg)));
			}
			if($out_count != $out_unique_count) {
				exit(json_encode(array('code'=>0,'msg'=>'包名有重复')));
			}
		}
 		$path=date("/Ym/d/",time());
		$save_dir = C("MARKET_PUSH_CSV").$path;
		
		$this->mkDirs($save_dir);
		$save_name = MODULE_NAME. '_' . ACTION_NAME  . '_' . time() . '_' . $_SESSION['admin']['admin_id'] . '.csv';
		$save_file_name = $save_dir . $save_name;
		$db_save=$path.$save_name;
		
		$fp = fopen($save_file_name, 'w');
		foreach ($out as $key => $val) {
			$clum_csv = array();
			foreach ($val as $k => $v) {
				$clum_csv[] = str_replace(array("\n","\r",'\n\r'),"",$v);
			}
			fputcsv($fp, $clum_csv);
		}
		fclose($fp);
// 		if($_FILES[$opt])
// 		{
// 			move_uploaded_file($_FILES[$opt]['tmp_name'], $save_file_name);
// 		}
		exit(json_encode(array('code'=>1,'msg'=>'上传成功','csv_url'=> $db_save)));
	}
	
}