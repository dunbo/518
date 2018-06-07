<?php
class ApplicationFlowAction extends CommonAction {
	
	public function application_list() 
	{
		$model = M('application_file');
		$count = $model -> table('sj_application_file')  -> count();
		import("@.ORG.Page");
		$param = http_build_query($_GET);
		$Page = new Page($count, 20, $param);
		$result = $model -> table('sj_application_file') -> limit($Page->firstRow . ',' . $Page->listRows) -> order('create_tm DESC') -> select();
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
        $show = $Page->show();
		$this -> assign('lr',$lr);
		$this -> assign('p',$p);
        $this -> assign('page', $show);
		$this -> assign('result',$result);
		$this->assign('domain_url', ATTACHMENT_HOST);
		$this -> display();
	}
	public function application_add() 
	{
		$model = M('application_file');
		if ($_FILES) 
		{
            $err = $_FILES["upload_file"]["error"];
            if ($err) 
			{
                $this->ajaxReturn($err,"上传文件错误，错误码为{$err}！", -1);
            }
            $file_name = $_FILES['upload_file']['name'];
            $tmp_arr = explode(".", $file_name);
            $name_suffix = array_pop($tmp_arr);
            if (strtoupper($name_suffix) != "CSV") 
			{
                $this->ajaxReturn("",'请上传CSV格式文件！', -2);
            }
            $tmp_name = $_FILES['upload_file']['tmp_name'];
            $content_arr = $this->import_file_to_array($tmp_name);
            if ($content_arr == -1) 
			{
                $this->ajaxReturn("",'文件打开错误，请检查文件是否损坏！', -3);
            } else if (empty($content_arr)) 
			{
                $this->ajaxReturn("",'文件数据内容不能为空！', -4);
            }
            // 返回检查结果的错误信息，如果记录的flag为1表示有错误
            $error_msg = $this->import_array_convert_and_check_package($content_arr);
            $flag = true;
            foreach($error_msg as $key=>$value) 
			{
                if ($value['flag'] == 1)
                    $flag = false;
            }
            if (!$flag) 
			{
                $this->ajaxReturn($error_msg,'您上传的CSV有如下问题，请修改后重新上传！', -5);
            }
            // 判断后台有没有人正在导入
            $lock_name = 'sj_application_package';
            $import_lock = S($lock_name);
            if ($import_lock) 
			{
                $this->ajaxReturn("",'后台有人正在导入，请稍后再尝试！', 1);
            }
            // 上锁，设置60秒内有效
            S($lock_name, 1, 60, 'File');
			// save the import file for backups
			$folder = "/application/" . date("Ym/d/");
            $this->mkDirs(UPLOAD_PATH . $folder);
            $save_name = 'application_flow_coop_'. time() . '.csv';
			$save_dir = $folder.$save_name;
            $save_file_name = UPLOAD_PATH.$save_dir;
            move_uploaded_file($_FILES['upload_file']['tmp_name'], $save_file_name);
			//保存到文件表中
			$file_map = array();
			$file_map['create_tm'] = time();
			$file_map['file_name'] = $save_name;
			$file_map['file_url'] = $save_dir;
			$n = 0; 
			$out = array (); 
			$handle=fopen($save_file_name,'r');
			while (!feof($handle)) 
			{
				$out[$n]=fgets($handle);
				$out[$n]=str_replace(array("\n","\r"),"",$out[$n]);//去掉换行符
				if(!empty($out[$n]))
				{
					$n++;
				}
			} 
			$file_map['pk_count'] = $n-1;
			$file_id = $model->add($file_map);
            // 添加到表中
			if ($file_id) 
			{
				$this->writelog('应用宝流量合作_添加文件'.$save_name,'sj_application_file',$file_id,__ACTION__ ,'','add');
				// 返回导入结果，如果记录的flag为0表示添加失败
				$result_arr = $this->process_import_array_soft_package($content_arr,$file_id);
				// 导入后解锁
				S($lock_name, NULL);
				$flag = true;
				foreach($result_arr as $key=>$value) 
				{
					if ($value['flag'] == 0)
						$flag = false;
				}
				if ($flag) 
				{
					$this->ajaxReturn("",'导入成功！', 0);
				} 
				else 
				{
					$this->ajaxReturn($result_arr,'存在部分导入失败记录！', -6);
				}
			} 
        } 
		else 
		{
            $this->display("application_list");
        }
	}
	function import_file_to_array($file) {
        $handle = fopen($file, "r");
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
        // 自动检测并转化编码
        foreach($content_arr as $key => $record) {
            foreach($record as $r_key => $r_value) {
                $content_arr[$key][$r_key] = $this->convert_encoding($r_value);
            }
        }
        return $content_arr;
    }
	function import_array_convert_and_check_package(&$content_arr) 
	{
        // 文件转换数据前的检查（是否可以转化成与页面数据格式一致）
        $error_msg1 = $this->handwriting_convert_and_check_package($content_arr);
        // 文件转换数据后的检查（区间是否有效、排期是否冲突等）
        $error_msg2 = $this->logic_check_package($content_arr);
        // 将$error_msg2合并到$error_msg1里并返回$error_msg1
        foreach($error_msg2 as $key=>$value) {
            if (!array_key_exists($key, $error_msg1)) {
                $this->init_error_msg($error_msg1, $key);
            }
            $this->append_error_msg($error_msg1, $key, $value['flag'], $value['msg']);
        }
        return $error_msg1;
    }
	//搜索阿拉丁导入文件检查
	function handwriting_convert_and_check_package(&$content_arr) 
	{
        // 初始化错误信息数组
        $error_msg = array();
        foreach($content_arr as $key => $value) 
		{
            $this->init_error_msg($error_msg, $key);
        }
        // 业务逻辑：将表里字段名称和模版里列的名称一一对应
        $correct_title_arr = array(
            'package'      =>  '包名（只可应用，且不可重复）',
            'soft_name'    =>  '软件名称',
         );
        // trim一下所有的数据
        foreach($content_arr as $key=>$record) {
            foreach($record as $r_key=>$r_record) {
                $content_arr[$key][$r_key] = trim($r_record);
            }
        }
        // 给$content_arr里的每一行记录的每一列下标由数字改成对应名称
        $new_content_arr = array();
        $new_key = array();
        // 将$correct_title_arr里的key值提取出来依次放在$new_key里
        foreach($correct_title_arr as $key => $value) {
            $new_key[] = $key;
        }
        foreach($content_arr as $key=>$record) {
            foreach($new_key as $new_key_key=>$new_key_value) {
                if (isset($record[$new_key_key])) {
                    $new_content_arr[$key][$new_key_value] = $record[$new_key_key];
                }
            }
        }
        $content_arr = $new_content_arr;
		//没有期望值检查 返回空的错误信息
        return $error_msg;
    }
	//包名检查
	function logic_check_package(&$content_arr) 
	{
        // 初始化错误信息数组
        $error_msg = array();
        foreach($content_arr as $key => $value) 
		{
            $this->init_error_msg($error_msg, $key);
        }
        // 业务逻辑 检查包名是否为有效的应用包
		$saw = M('category');
        // 业务逻辑：以下为各项具体检查
        foreach($content_arr as $key=>$record) 
		{
            // 检查包名是否为空、是否有效
            if (isset($record['package']) && !empty($record['package'])) 
			{
				$have_package = $saw -> table('sj_soft') -> where(array('package' => $record['package'],'status' => 1,'hide' => 1)) -> select();
				if(!$have_package)
				{
					$this -> append_error_msg($error_msg, $key, 1, "包名".$record['package']."不存在;");
				}
				else
				{
					//检查包是否为应用包  除了游戏包外   游戏包category_id是2
					$lists = $saw->field('category_id')->where('status=1 and parentid=2')->order('orderid')->select();
					$all_ids=array();
					foreach($lists as $k=>$val)
					{
						$all_ids[] = $val['category_id'];
						$list_three=$saw->where('status=1 and parentid='.$val['category_id'])->order('orderid')->select();
						foreach($list_three as $ke=>$info){
							$all_ids[] =$info['category_id'];
						}
					}
					//过滤
					$this->gpcFilter($all_ids);
					$have_category_id = trim($have_package[0]['category_id'],',');
					if(in_array($have_category_id,$all_ids))
					{
						$this -> append_error_msg($error_msg, $key, 1, "包".$record['package']."为游戏包;");
					}
				}
            } 
			else 
			{
                $this->append_error_msg($error_msg, $key, 1, "包名不能为空，请填写有效的包名;");
            }
        }
        // 检查行与行之间的数据是否冲突
        foreach($content_arr as $key1=>$record1) 
		{
            foreach($content_arr as $key2=>$record2) 
			{
                // 比较过的不比较
                if ($key1 >= $key2)
                    continue;
				//包名是否有重复
				$package_arr1 = explode(';',trim($record1['package']));
				$package_arr2 = explode(';',trim($record2['package']));
				// 关键字统一转小写 将每个关键字前后空格去掉
				foreach ($package_arr1 as $ke => $value) 
				{
					$package_arr1[$ke] = strtolower(trim($value));
				}
				foreach ($package_arr2 as $ke2 => $value) 
				{
					$package_arr2[$ke2] = strtolower(trim($value));
				}
				$package1=array_filter($package_arr1);
				$package2=array_filter($package_arr2);
				$intersect=array_intersect($package1,$package2);
				$k1 = $key1 + 2;
                $k2 = $key2 + 2;//都算上标题行
				//包名相同 时间交叉 关键字不能重复	
				if($intersect)
				{
					$this->append_error_msg($error_msg, $key1, 1,
					"包名与第{$k2}行有重复;");
					$this->append_error_msg($error_msg, $key2, 1, 
					"包名与第{$k1}行有重复;");
				}
            }
        }
        return $error_msg;
    }
	// 初始单条错误信息，初始化信息：flag为0，msg为空
    function init_error_msg(&$error_msg, $key) {
        if (!isset($error_msg))
            $error_msg = array();
        $error_msg[$key] = array('flag' => 0,'msg' => '');
    }
    
    // 添加错误信息
    function append_error_msg(&$error_msg, $key, $flag, $msg) {
        if (!isset($error_msg[$key])) {
            $this->init_error_msg($error_msg, $key);
        }
        $error_msg[$key]['flag'] |= $flag;
        $error_msg[$key]['msg'] .= $msg;
    }
	 // 业务逻辑：将批量导入文件里所有数据添加进数据库，返回结果为每一行添加是否成功标志符
    function process_import_array_soft_package($content_arr,$file_id) {
        $result_arr = array();
        $model = M('application_package');
		//添加之前先把之前表中数据删除完
		//$del_sql="TRUNCATE sj_application_package";
		$del_sql = "UPDATE `sj_application_package` SET status = 0";
		$query=mysql_query($del_sql);
        foreach($content_arr as $key => $record) 
		{
			$map = array();
			// 设置默认值
			$map['create_tm'] = time();
			$map['file_id'] = $file_id;
			$map['package'] = trim($record['package']);
			$id = $model->add($map);
			
            // 添加到表中
			if ($id) 
			{
				$this->writelog('应用宝流量合作_添加包名'.$record['package'],'sj_application_package',$id,__ACTION__ ,'','add');
                $result_arr[$key]['flag'] = 1;
                $result_arr[$key]['msg'] = "添加成功";
			} 
			else
			{
                // 未知原因添加失败
                $result_arr[$key]['flag'] = 0;
                $result_arr[$key]['msg'] = "添加失败";
			}
        }
        return $result_arr;
    }
}

