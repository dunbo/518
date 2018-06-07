<?php
 
 /**
   * @desc 第三方合作软件数据推送方法
   * @author WeiTao Wang(wangweitao@anzhi.com)
   * @modify 2012-06-12
   */
  
  /**
   * @desc 统计执行次数
   * @param  limit  读取softid数目
   * @param  model  模型对象
   * @return array  软件列表
   */
	function statisticExecution($limit,$model){
		$sql = "SELECT count(softid) as total FROM sj_soft WHERE `status` = '1' AND `hide` = '1' LIMIT 1";
		$result_total = $model->query($sql);
		while($rows = $model->fetch($result_total)){
			$total = $rows['total'];
		}
		$count = ceil($total/$limit);
		for($i = 0;$i < $count;$i++){
			$offset       = $i * $limit;
			$softids[$i]  = generateSoftId($offset,$limit,$model);
			$softlist[$i] = collectSoftInfo($softids[$i],$model);
		}
		return $softlist;
	}
  
 /**
   * @desc 拼接softid
   * @param  limit  读取softid数目
   * @param  offset 偏移量
   * @param  model  模型对象
   * @return array  softid数组
   */
	function generateSoftId($offset,$limit,$model){
		$sql = "SELECT softid FROM sj_soft WHERE `status` = '1' AND `hide` = '1' LIMIT $offset,$limit";
		$result_softid = $model->query($sql);
		echo $model->getSql()."\n";
		while($rows = $model->fetch($result_softid)){
			$softids[] = $rows['softid'];
		}
		return $softids;
	}
	 
 /**
   * @desc 收集软件数据
   * @param  softids 每次执行软件数目
   * @param  model   模型对象
   * @return array   软件列表 
   */
	function collectSoftInfo($softids,$model){
		//soft
		$option = array(
				'table' => 'sj_soft',
				'where' => array('status' => 1,'hide' => 1,'softid' => $softids),
			);
		$soft_list = $model->findAll($option);

		//soft_file
		$option = array(
			'table' => 'sj_soft_file',
			'where' => array('package_status' => 1,'softid' => $softids),
		);
		$soft_file_list = $model->findAll($option);

		//soft_thumb
		$option = array(
					'table' => 'sj_soft_thumb',
					'where' => array('status' => 1,'softid' => $softids),
					'limit' => 5,
				);
		$soft_thumb_list = $model->findAll($option);

		for($i = 0;$i < count($soft_list); $i++){
			for ($j = 0; $j < count($soft_file_list); $j++) {
					if ($soft_file_list[$j]['softid'] == $soft_list[$i]['softid']) {
						$soft_list[$i]['file'][] = $soft_file_list[$j];
					}
			}
			for ($k = 0; $k < count($soft_thumb_list); $k++) {
					if ($soft_thumb_list[$k]['softid'] == $soft_list[$i]['softid']) {
						$soft_list[$i]['thumb'][] = $soft_thumb_list[$k];
					}
			}
			for ($m = 0; $m < count($soft_comment_list); $m++) {
					if ($soft_comment_list[$m]['softid'] == $soft_list[$i]['softid']) {
						$soft_list[$i]['comment'][] = $soft_comment_list[$m];
					}
			}
		}
		return $soft_list;
	}
 
  /**
	* desc 压缩XML文件
	* @param  command  压缩命令
	* @return boolean  执行成功则返回true,否则返回错误信息 
	*/
	function compressFile($command){
		  $command = $command. ';echo $?';
		  $result  = shell_exec($command);
		  if($result == 0){
			 return true;
		  }else{
			 echo "执行压缩".$command."有误\n";
			 return false;
		  }
	}
 
	 /**
	   * @desc  推送软件信息
	   * @param  files    推送文件
	   * @param  params   推送参数包含推送IP(ip)、推送用户名(username)、推送密码路径(pwdpath)、推送路径(path) 
	   * @return boolean  推送成功返回true,否则返回false
	   */
	function pushSoftInfo($files,$params){
		$reuslt = true;
		if(is_array($params)){
					if (array_key_exists('username', $params) && array_key_exists('ip', $params) && array_key_exists('ip', $params) && array_key_exists('pwdpath', $params)&& array_key_exists('path', $params)) {
						$suffix  = $params['username'].'@'.$params['ip'].'::'.$params['path'].' --password-file='.$params['pwdpath'];
						if(is_array($files)){
							foreach($files as $file){
								$prefix  = "rsync -av {$file} ";
								$command = $prefix.$suffix.';echo $?';
								$result  = shell_exec($command);
							}   
						}else if(is_string($files)){
							$prefix  = "rsync -av {$files} ";
							$command = $prefix.$suffix.';echo $?';
							$result  = shell_exec($command);
						}else{
							echo "推送文件".$files."有误\n";
						} 
					}else{
						echo "推送参数".$params."有误\n";
					}			
		}	
		return $result == 0 ? true:false;
	}
 
	 /**
	   * @desc   读取日志信息
	   * @param  dir    日志存放目录
	   * @return array  软件信息
	   */
	function readLogFile($dir){
		if(is_dir($dir)){
			$hour = date('H',time());
			//$hour = 2;
			$date = date("Y-m-d",time());
			//$date = '2012-03-26';
			for($i = 0;$i < $hour;$i++){
			    if($i < 10){
				  $i = '0'.$i;
			    }
			    if(!is_file($dir.$date.'/data_modify_'.$i.'.log')) continue;
			    $log_paths[] = $dir.$date.'/data_modify_'.$i.'.log'; 
			}
			
			foreach($log_paths as $log_path){
				$handle = fopen($log_path,"r");
				while(!feof($handle)){
					$line = fgets($handle);
					$info = json_decode($line,true);
					$softlist[] = $info;
				}  
			}
			return $softlist;
		}else{
		   exit($dir."is not exists");
		}
	}

	 /**
	   * @desc 获取软件分类信息
	   * @param  cateid   当前分类编号
	   * @return array    分类信息列表
	   */
	function getCategoryInfoById($cateid){
		$category_logic = pu_load_logic('category');
		$list = $category_logic -> get_all_category();
		$catename   = $list[$cateid]['name'];
		$fatherid   = $list[$cateid]['parentid'];
		$fathername = $list[$fatherid]['name'];
		if($fatherid == 0 ){
			return array( $cateid => $catename ,
				   $fatherid => $fathername);
		}else{
			$grandid    = $list[$fatherid]['parentid']; 
			$grandname  = $list[$grandid]['name']; 
			return array( $cateid => $catename ,
				   $fatherid => $fathername ,
				   $grandid => $grandname);
		}
	}
?>
