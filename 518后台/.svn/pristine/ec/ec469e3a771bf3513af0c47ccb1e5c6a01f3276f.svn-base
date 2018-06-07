<?php

class UpdatePushAction extends CommonAction {
    public function index() 
	{
        $model = M('update_push');
		$soft_model = M('soft');
        $where = array();
        $where['status'] = 1;
        // 分页
        import("@.ORG.Page");
        $limit = 10;
        $count = $model->table('sj_update_push')->where($where)->count();
        $page  = new Page($count, $limit);
        // 当前页数据
        $list = $model->table('sj_update_push')->where($where)->order("id asc")->limit($page->firstRow . ',' . $page->listRows)->select();
        // 处理list
        foreach ($list as $key => $value) 
		{
			//根据包名获取软件名称
			$where=array(
				'status' =>1,
				'hide' =>1,
				'package' =>$value['soft_package'],
			);
			$soft_name=$soft_model->where($where)->find();
			$list[$key]['soft_name']=$soft_name['softname'];
        }

        $this->assign('list', $list);
        $this->assign("page", $page->show());
        $this->display();
    }
    public function add_content() 
	{
        if ($_POST) 
		{
            $model = M('update_push');
            $map = array();
		
			// 将_POST或_GET传进来的参数统一转成与表里字段一样的名称
			$column_convert_arr = array(
				'soft_package' => 'package',
			);
			$check_column_arr = array();
			foreach($column_convert_arr as $key=>$value) 
			{
				if (array_key_exists($key, $_POST)) {
					$check_column_arr[$value] = $_POST[$key];
				}
			}
			foreach($check_column_arr as $key=>$value) 
			{
				$check_column_arr[$key] = trim($value);
			}
		 
			// 调用通用的检查函数
			$content_arr = array();
			$content_arr[0] = $check_column_arr;
			$error_msg = $this->logic_check($content_arr);
			$qualified_flag = true;
			foreach($error_msg as $key=>$value) 
			{
				if ($value['flag'] == 1)
					$qualified_flag = false;
			}
			if (!$qualified_flag) 
			{
				$msg = $error_msg[0]['msg'];
				// 业务逻辑：设置返回的跳转页面
				$this->error($msg);
			}
		
			//包名
			/*$soft_package=trim($_POST['soft_package']);
            if (!$soft_package) {
                $this->error("包名不能为空");
            }
			//检查包名是否重复
			$where=array(
				'soft_package' => $soft_package,
				'status' => 1,
			);
			$have_result=$model->where($where)->find();
			if($have_result)
			{
				$this->error("包名已经存在，请重新添加");
			}*/
            $map['soft_package'] = trim($_POST['soft_package']);
			$map['create_tm']=time();
			$map['update_tm']=time();
			$map['status']=1;
            // 添加
            $ret = $model->table('sj_update_push')->add($map);
            if ($ret) {
                $this->writelog("更新PUSH设置：添加了id为{$ret}的记录",'sj_update_push',$ret,__ACTION__ ,"","add");
                $this->success("添加成功！");
            } else {
                $this->error("添加失败");
            }
        }
		else 
		{
            $this->display();
        }
    }
    
    public function delete_content() 
	{
        $model = M();
        $id = $_GET['id'];
        $data['status'] = 0;
        $del = $model->table('sj_update_push')->where("id = {$id}")->save($data);
        if($del) {
            $this->writelog("更新PUSH设置：删除了id为{$id}的记录",'sj_update_push',$id,__ACTION__ ,"","del");
            $this -> success("删除成功");
        } else {
            $this->error("删除失败");
        }
    }
	public function import_softs()
	{
		if ($_FILES) 
		{
            $err = $_FILES["upload_file"]["error"];
            if ($err) {
                $this->ajaxReturn($err,"上传文件错误，错误码为{$err}！", -1);
            }
            $file_name = $_FILES['upload_file']['name'];
            $tmp_arr = explode(".", $file_name);
            $name_suffix = array_pop($tmp_arr);
            if (strtoupper($name_suffix) != "CSV") {
                $this->ajaxReturn("",'请上传CSV格式文件！', -2);
            }
            $tmp_name = $_FILES['upload_file']['tmp_name'];
            $content_arr = $this->import_file_to_array($tmp_name);
            if ($content_arr == -1) {
                $this->ajaxReturn("",'文件打开错误，请检查文件是否损坏！', -3);
            } else if (empty($content_arr)) {
                $this->ajaxReturn("",'文件数据内容不能为空！', -4);
            }
            // 返回检查结果的错误信息，如果记录的flag为1表示有错误
            $error_msg = $this->import_array_convert_and_check($content_arr);
            $flag = true;
            foreach($error_msg as $key=>$value) {
                if ($value['flag'] == 1)
                    $flag = false;
            }
            if (!$flag) {
                $this->ajaxReturn($error_msg,'您上传的CSV有如下问题，请修改后重新上传！', -5);
            }
            // 判断后台有没有人正在导入
            $lock_name = 'sj_update_push_package_importing';
            $import_lock = S($lock_name);
            if ($import_lock) {
                $this->ajaxReturn("",'后台有人正在导入，请稍后再尝试！', 1);
            }
            // 上锁，设置60秒内有效
            S($lock_name, 1, 60, 'File');
            // 返回导入结果，如果记录的flag为0表示添加失败
            $result_arr = $this->process_import_array($content_arr);
            // 导入后解锁
            S($lock_name, NULL);
            $flag = true;
            foreach($result_arr as $key=>$value) {
                if ($value['flag'] == 0)
                    $flag = false;
            }
            // save the import file for backups
            $save_dir = IMPORT_FILE_UPLOAD_PATH;
            $this->mkDirs($save_dir);
            $save_name = MODULE_NAME. '_' . ACTION_NAME  . '_' . time() . '_' . $_SESSION['admin']['admin_id'] . '.csv';
            $save_file_name = $save_dir . $save_name;
            move_uploaded_file($_FILES['upload_file']['tmp_name'], $save_file_name);
            $this->writelog("更新PUSH设置：批量导入了{$save_file_name}。");
            if ($flag) {
                $this->ajaxReturn("",'导入成功！', 0);
            } else {
                $this->ajaxReturn($result_arr,'存在部分导入失败记录！', -6);
            }
        } else {
            $this->display("import_softs");
        }
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
	 // 第一行标题列忽略，只保存之后的内容
    function import_file_to_array($file) {
        // $file = "/media/sf_D_DRIVE/shouye-gbk.csv";
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
	function import_array_convert_and_check(&$content_arr) {
        // 文件转换数据前的检查（是否可以转化成与页面数据格式一致）
        $error_msg1 = $this->handwriting_convert_and_check($content_arr);
        // 文件转换数据后的检查（区间是否有效、排期是否冲突等）
        $error_msg2 = $this->logic_check($content_arr);
        // 将$error_msg2合并到$error_msg1里并返回$error_msg1
        foreach($error_msg2 as $key=>$value) {
            if (!array_key_exists($key, $error_msg1)) {
                $this->init_error_msg($error_msg1, $key);
            }
            $this->append_error_msg($error_msg1, $key, $value['flag'], $value['msg']);
        }
        return $error_msg1;
    }
	// 只检查导入文件的手工填写内容，并将其数据格式转成与网页版的添加单条软件一致
    function handwriting_convert_and_check(&$content_arr) {
        // 初始化错误信息数组
        $error_msg = array();
        foreach($content_arr as $key => $value) {
            $this->init_error_msg($error_msg, $key);
        }
        // 业务逻辑：将表里字段名称和模版里列的名称一一对应
        $correct_title_arr = array(
            'package'  =>   '软件包名',
        );
        // trim一下所有的数据
        foreach($content_arr as $key=>$record) 
		{
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
        return $error_msg;
    }
	 // 统一的逻辑检查：检查添加软件数据是否合法
    function logic_check($content_arr) 
	{
        // 初始化错误信息数组
        $error_msg = array();
        foreach($content_arr as $key => $value) 
		{
            $this->init_error_msg($error_msg, $key);
        }
        $update_push_model = M('update_push');
        $soft_model = M("soft");//软件大表
        // 业务逻辑：以下为各项具体检查
        foreach($content_arr as $key=>$record) 
		{
			//检查包名
			if (isset($record['package']) && $record['package'] != "") 
			{ 
				// 检查包名是否在sj_soft表里
				$where = array(
					'package' => $record['package'],
					'status' => 1,
					'hide' => array('EQ', 1),
				);
				$find = $soft_model->where($where)->find();
				if (!$find) 
				{
					$this->append_error_msg($error_msg, $key, 1, "包名【{$record['package']}】不存在于市场软件库中;");
				}
			} else {
				$this->append_error_msg($error_msg, $key, 1, "包名不能为空;");
			}
        }
        
        // 检查行与行之间的数据是否冲突（主要检查相同包名的区间是否有冲突）
        foreach($content_arr as $key1=>$record1) 
		{
            foreach($content_arr as $key2=>$record2) {
                $k1 = $key1 + 1;
                $k2 = $key2 + 1;
                // 比较过的不比较
                if ($key1 >= $key2)
                    continue;
                // 包名相同 不能添加
                if ($record1['package'] == $record2['package']) 
				{ 
					$k1 = $key1 + 1; $k2 = $key2 + 1;
					$this->append_error_msg($error_msg, $key1, 1, "包名与第{$k2}行有重叠;");
					$this->append_error_msg($error_msg, $key2, 1, "包名与第{$k1}行有重叠;");
					
                }
            }
        }
        
        // 检查每一行数据是否与数据库的存储内容相冲突
        foreach($content_arr as $key=>$record) 
		{
            $where = array(
                'soft_package' => array('EQ', $record['package']),
                'status' => array('NEQ', 0),
            );
			//更新push  没有编辑
            // if (isset($record['id']))
            //    $where['id'] = array('NEQ', $record['id']);
            $push_records = $update_push_model->where($where)->find();
			//如果数据库已有软件包名，不能添加
			if (!$push_records)
			{
				continue;
			}
			else
			{
				$this->append_error_msg($error_msg, $key, 1, "包名【{$record['package']}】与后台id为【{$push_records['id']}】的包名重复;");

			}
        }
        return $error_msg;
    }
	 // 业务逻辑：将批量导入文件里所有数据添加进数据库，返回结果为每一行添加是否成功标志符
    function process_import_array($content_arr) {
        $result_arr = array();
        $model = M('update_push');
        foreach($content_arr as $key => $record) 
		{
			$map = array();
       
            // 设置默认值
			$map['status'] = 1;
            $map['create_tm'] = time();
			$map['update_tm'] = time();
            // 赋值，以下为必填的值
			$map['soft_package'] = $record['package'];
		   
            // 添加到表中
			if ($id = $model->add($map)) {
				$this->writelog('定制推送_更新PUSH设置_批量添加包名为'.$map['soft_package'],'sj_update_push',$id,__ACTION__ ,"","add");
                $result_arr[$key]['flag'] = 1;
                $result_arr[$key]['msg'] = "添加成功";
			} else {
                // 未知原因添加失败
                $result_arr[$key]['flag'] = 0;
                $result_arr[$key]['msg'] = "添加失败";
			}
        }
        return $result_arr;
    }
}

