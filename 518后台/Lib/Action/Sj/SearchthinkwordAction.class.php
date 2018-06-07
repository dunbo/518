<?php
class SearchthinkwordAction extends CommonAction{
	
	function search_keyword_manage(){
		$model = new model();
		$texttime =  strtotime(date('Y-m-d H:i:s'));
		$_GET['product_id'] = isset($_GET['product_id']) ? $_GET['product_id'] : 1;		
		$where = array(
			'status' => 1,
			'end_time' => array('exp',">={$texttime}")
		);
		if($_GET['product_id']){
			$where['pid'] = $_GET['product_id'];
			$this->assign('product_id',$_GET['product_id']);			
		}		
		import("@.ORG.Page");
		$count = $model->table("sj_think_words")->where($where)->count();
		$Page = new Page($count, 15);
		$show = $Page->show();		
		$list = $model->table("sj_think_words")->where($where)->order("start_time asc")->limit($Page->firstRow.','.$Page->listRows)->select();
		//获取合作样式列表
		$util = D("Sj.Util"); 
		//根据包名获取软件名称  在列表中显示
		foreach($list as $k=>$v)
		{
			$where=array(
				'status'=>1,
				'package'=>$v['package'],
				'hide'=>1,
			);
			$result=$model->table('sj_soft')->where($where)->find();
			$list[$k]['soft_name']=$result['softname'];
			//合作形式展示
			$typelist = $util->getHomeExtentSoftTypeList($v['type']);
			foreach($typelist as $key => $val){
				if($val[1] == true)
				{
					$list[$k]['types'] = $val[0];
				}
			}
		}
		$this->assign("list",$list);
		$this->assign('page', $show);	
		#产品列表
        $product_model = M();
        $product_list = $product_model ->table('pu_product')->where('status = 1')->findAll();
        $this-> assign ("product_list", $product_list);				
		$this->display();
	}

	function add_thinkkeywords_to_show(){
		$texttime =  strtotime(date('Y-m-d H:i:s',strtotime('+1 day')));
		//合作形式
		$util = D("Sj.Util");
		$typelist = $util->getHomeExtentSoftTypeList();
		$this->assign('typelist',$typelist);
		$this->display();	
	}

	function searchkeywords_out_show()
	{
	    import("@.ORG.Page2");
	    $size = 50;	   
	    $param = http_build_query($_GET);
		$model = new model();
		$texttime =  strtotime(date('Y-m-d H:i:s'));
		$where = "end_time < $texttime and status = 1";
		$texttime =  strtotime(date('Y-m-d H:i:s'));
		
		$count = $model->table("sj_think_words")->where($where)->count();
		$Page = new Page($count, $size);
		
		$list = $model->table("sj_think_words")->where($where)->order('start_time asc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
		//获取合作样式列表
		$util = D("Sj.Util"); 
		//根据包名获取软件名称  在列表中显示
		foreach($list as $k=>$v)
		{
			$where=array(
				'status'=>1,
				'package'=>$v['package'],
				'hide'=>1,
			);
			$result=$model->table('sj_soft')->where($where)->find();
			$list[$k]['soft_name']=$result['softname'];
			//合作形式展示
			$typelist = $util->getHomeExtentSoftTypeList($v['type']);
			foreach($typelist as $key => $val){
				if($val[1] == true)
				{
					$list[$k]['types'] = $val[0];
				}
			}
		}
		$show = $Page->show();
		$this -> assign("page", $show);
		$this -> assign('param',$param);
		$this -> assign('count',$count);
		$this->assign("list",$list);
		$this->display();
	}

	function add_thinkwords_to()
	{
		if (!empty($_POST))
	    {  
			$model = new model();
			$string  = $_POST['search_words'];
			$package = trim($_POST['package']);
			$soft_rank = trim($_POST['soft_rank']);
			$start_time = strtotime($_POST['start_time']);
			//$end_time = strtotime($_POST['end_time'])+86399;
			$end_time = strtotime($_POST['end_time']);
			if(isset($_POST['type'])){
				$type = $_POST['type'];
			}else{
				$type = 0;
			}
			$beid = isset($_POST['beid']) ? trim($_POST['beid']) : 0;
		    // tpl（网页）里的名称和数据库字段对应数组
            $column_convert_arr = array(
                'package' => 'package',
                'search_words' => 'search_words',
                'start_time' => 'start_time',
                'end_time' => 'end_time',
				'type' =>'type',
				'soft_rank' => 'soft_rank',
				'beid' => 'beid',
				'pid' => 'pid',
            );
			 // $check_column_arr数组存放_POST/_GET对应数据库字段的值（因为logic_check里的变量名跟数据库字段名一样）
            $check_column_arr = array();
            foreach($column_convert_arr as $key=>$value) 
			{
                if (array_key_exists($key, $_POST)) 
				{
                    $check_column_arr[$value] = trim($_POST[$key]);
                }
            }
            // trim一下
            foreach($check_column_arr as $key=>$value) 
			{
                $check_column_arr[$key] = trim($value);
            }
            // 调用通用的检查函数
            $content_arr = array();
            $content_arr[0] = $check_column_arr;
            $error_msg = $this->logic_check_suggest($content_arr);
            $qualified_flag = true;
            foreach($error_msg as $key=>$value) 
			{
                if ($value['flag'] == 1)
                    $qualified_flag = false;
            }
            if (!$qualified_flag) 
			{
                $msg = $error_msg[0]['msg'];
                $this->error($msg);
            }
			$model = new Model();
			$data = array(
				'search_words' => trim($string),
				'package' => $package,
				'up_time' => time(),
				'create_time' => time(),
				'start_time' => $start_time,
				'end_time' => $end_time,
				'status' => 1,
				'type' =>$type,
				'soft_rank' =>$soft_rank,
				'beid' => $beid,
				'short_describe' => trim($_POST['short_describe']),
				'pid' => $_POST['pid'],
				'admin_id'=>$_SESSION['admin']['admin_id'],
			);
			if($data['package']){
				//屏蔽软件上排期时报警需求 新增  yuesai
		        $AdSearch = D("Sj.AdSearch");
		        $shield_error=$AdSearch->check_shield($data['package'],$data['start_time'],$data['end_time'],1);
				if($shield_error){
					$this -> error($shield_error);
				}
			}
			
			$result = $model -> table('sj_think_words') -> add($data);
			$this->writelog("搜索关键字管理-搜索联想词管理-添加包名'".$data['package']."'的搜索词为$string","sj_think_words",$result,__ACTION__ ,"",'add');
			if($result!='')
			{
				$this->success("添加成功");
			}
			else
			{
				$this->error("添加失败");
			}
			
	    }
	}

	function update_searchkeywords_show(){
		$model = new model();
		$id = $_GET['id'];
		$list = $model ->table("sj_think_words")->where("id = $id and status =1")->find();
		//合作形式
		$util = D("Sj.Util");
		$typelist = $util->getHomeExtentSoftTypeList($list['type']);
		$this->assign('typelist',$typelist);  
		
		$this->assign("list",$list);
		$this->display();
	}

	function update_thinkwords_to(){
		$model = new model();
		$id = $_POST['id'];
		$package = trim($_POST['package']);
		$soft_rank = trim($_POST['soft_rank']);
		$start_time = strtotime($_POST['start_time']);
		$end_time = strtotime($_POST['end_time']);
		$string = trim($_POST['search_words']);
		if(isset($_POST['type'])){
			$type = $_POST['type'];
		}else{
			$type = 0;
		}
		$beid = isset($_POST['beid']) ? trim($_POST['beid']) : 0 ;
		if($package==''){
			$this->error("包名不能为空");
		}
		if($string==''){
			$this->error("搜索热词不能为空");
		}
		$pack = $model->table("sj_soft")->where("package = '$package' and status = 1")->find();
		if($pack==''){
			$this->error("您填写的包名不存在");
		}
		if($start_time==''){
			$this->error("开始时间不能为空");
		}
		if($end_time==86399){
			$this->error("结束时间不能为空");
		}
		if ($start_time>$end_time) {
			$this->error("开始时间不能大于结束时间");
		}
		if($_POST['life']==1)
	    {
		  if($end_time<time())
		  {
			$this->error("您修改的复制上线的日期还是无效日期");
		  }
		}
		$arr = explode(',',$string);
		$num = count($arr);
		$a = array_unique($arr);
		$new_num = count($a);
	
		foreach($arr as $k=>$v){
			if(!preg_match("/^[\x{4e00}-\x{9fa5}0-9a-zA-Z]+$/u",$v)){
				$this->error("您填写的内容有误");
				break;
			}	
		}
		if($num!=$new_num){
			$this->error("您填写的内容有重复");
		}
		if($_POST['life']==1)
	    {
		  $sel = $model->table("sj_think_words")->where("((start_time <=$end_time and end_time>=$start_time) or (start_time <=$start_time and end_time>=$end_time)) and soft_rank=$soft_rank and status =1")->select();
		}
		else
		{
		 $sel = $model->table("sj_think_words")->where("((start_time <=$end_time and end_time>=$start_time) or (start_time <=$start_time and end_time>=$end_time)) and soft_rank=$soft_rank and status =1 and id not in ($id)")->select();
		}
		foreach($sel as $k=>$v){
			$new_str[] =  $v['search_words'];
			$package_arr[]=$v['package'];
		}
		$a_arr = implode(',',$new_str);
		$newarr = explode(',',$a_arr);
		$res = array_intersect($newarr,$arr);
        /*if(in_array($package,$package_arr))
		{
		 $this->error("当前排期中您填写的包名有冲突");
		}*/
		if(!empty($res)){
			$this->error("当前排期中您填写的搜索词有冲突");
		}else{
			$data['package'] = $package;
			$data['search_words'] = trim($_POST['search_words']);
			$data['start_time'] = strtotime($_POST['start_time']);
			$data['end_time'] = strtotime($_POST['end_time']);
			$data['up_time'] = time();
			$data['status'] = 1;
			$data['type'] = $type;
			$data['soft_rank'] = $soft_rank;
			$data['beid'] = $beid;
			$data['short_describe']= trim($_POST['short_describe']);
			$data['admin_id'] = $_SESSION['admin']['admin_id'];
			if($data['package']){
				//屏蔽软件上排期时报警需求 新增  yuesai
		        $AdSearch = D("Sj.AdSearch");
		        $shield_error=$AdSearch->check_shield($data['package'],$data['start_time'],$data['end_time'],1);
				if($shield_error){
					$this -> error($shield_error);
				}
			}
			
			$log = $this->logcheck(array('id'=>$id),'sj_think_words',$data,$model);
			//复制上线  保留原来记录
			if($_POST['life']==1)
	        {
			    $data['create_time']=time();
				$add = $model->table("sj_think_words")->add($data);
				$this->writelog("搜索关键字管理-搜索联想词管理-复制上线package为{$data['package']}".$log,"sj_think_words",$add,__ACTION__ ,"","add");
				if($add!=''){
					$this->success("复制上线成功");
				}else{
					$this->error("复制上线失败");
				}
		    }
			else
			{
			    $add = $model->table("sj_think_words")->where("id = $id")->save($data);
				$this->writelog("搜索关键字管理-搜索联想词管理-编辑ID为$id".$log,"sj_think_words",$id,__ACTION__ ,"","edit");
				if($add!=''){
					$this->success("编辑成功");
				}else{
					$this->error("编辑失败");
				}
			}
		}
	}


	function delete_searchkeywords(){
		$model = new model();
		$id = $_GET['id'];
		$data['up_time'] = time();
		$data['status'] = 0;
		$log = $this->logcheck(array('id'=>$id),'sj_think_words',$data,$model);
		$del_res = $model->table("sj_think_words")->where("id = $id")->save($data);
		$this->writelog("搜索关键字管理-搜索联想词管理-删除ID为$id".$log,"sj_think_words",$id,__ACTION__ ,"","del");
		if($del_res){
			$this->success("删除成功");
		}else{
			$this->error("删除失败");
		}
	}
	
	// 下载批量导入模版
    function down_moban($type) 
	{
        if ($type == 'suggest') 
		{
            $file_dir = C("ADLIST_PATH") . "suggest_import_moban.csv";
            $file_name = '搜索Suggest';
        } 
        if (file_exists($file_dir)) 
		{
            $file = fopen($file_dir,"r");
            Header("Content-type: application/octet-stream");
            Header("Accept-Ranges: bytes");
            Header("Accept-Length: ".filesize($file_dir));
            Header("Content-Disposition: attachment; filename=" . urlencode($file_name . "批量导入模版") . ".csv");
            echo fread($file, filesize($file_dir));
            fclose($file);
            exit(0);
        } 
		else 
		{
            header("Content-type: text/html; charset=utf-8");
            echo "File not found!";
            exit;
        }
    }	
	//搜索Suggest批量上传广告
	function import_softs_suggest() 
	{
		if ($_GET['down_moban']) 
		{
			$this->down_moban('suggest');
		} 
		else if ($_FILES) 
		{
			$AdSearch = D("Sj.AdSearch");
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
			
            if ($content_arr == -1) {
                $this->ajaxReturn("",'文件打开错误，请检查文件是否损坏！', -3);
            } 
			else if (empty($content_arr)) {
                $this->ajaxReturn("",'文件数据内容不能为空！', -4);
            }
            // 返回检查结果的错误信息，如果记录的flag为1表示有错误
            $error_msg = $this->import_array_convert_and_check_suggest($content_arr);
            $flag = true;
            foreach($error_msg as $key=>$value) {
                if ($value['flag'] == 1)
                    $flag = false;
				//针对rank值处理
				preg_match('/rank=([\d]+)/',$value['msg'],$matches);
				if($matches){
					$value['rank'] = $matches[1];
					if($value['rank']==0){
						$value['rank'] = '已满';
					}else if($value['rank']==3){
						$value['rank'] = '';
					}
					$value['msg'] = preg_replace('/rank=([\d]+)/',' ',$value['msg']);
				}
				$error_msg[$key] = $value;
            }
            if (!$flag) {
				$AdSearch -> get_error_file($error_msg,$content_arr,'suggest');
                $this->ajaxReturn($error_msg,'您上传的CSV有如下问题，请修改后重新上传！', -5);
            }
            // 判断后台有没有人正在导入
            $lock_name = 'sj_search_suggesst_importing';
            $import_lock = S($lock_name);
            if ($import_lock) {
                $this->ajaxReturn("",'后台有人正在导入，请稍后再尝试！', 1);
            }
            // 上锁，设置60秒内有效
            S($lock_name, 1, 60, 'File');
            // 返回导入结果，如果记录的flag为0表示添加失败
            $result_arr = $this->process_import_array_suggest($content_arr);
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
            //$this->writelog("搜索suggest设置：批量导入了{$save_file_name}。");
            if ($flag) {
                $this->ajaxReturn("",'导入成功！', 0);
            } 
			else {
                $this->ajaxReturn($result_arr,'存在部分导入失败记录！', -6);
            }
        } 
		else 
		{
            $this->display("import_softs_suggest");
        }
    }
	// 第一行标题列忽略，只保存之后的内容
	function import_file_to_array($file) 
	{
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
	function import_array_convert_and_check_suggest(&$content_arr) 
	{
        // 文件转换数据前的检查（是否可以转化成与页面数据格式一致）
        $error_msg1 = $this->handwriting_convert_and_check_suggest($content_arr);
        // 文件转换数据后的检查（区间是否有效、排期是否冲突等）
        $error_msg2 = $this->logic_check_suggest($content_arr);
        // 将$error_msg2合并到$error_msg1里并返回$error_msg1
        //屏蔽软件上排期时报警需求 新增  yuesai
		$AdSearch = D("Sj.AdSearch");
        $error_msg3 = $AdSearch->logic_check_shield($content_arr,'start_time','end_time','',1);
        foreach($error_msg2 as $key=>$value) {
            if (!array_key_exists($key, $error_msg1)) {
                $this->init_error_msg($error_msg1, $key);
            }
            $this->append_error_msg($error_msg1, $key, $value['flag'], $value['msg']);
        }
        foreach($error_msg3 as $key=>$value) {
            if (!array_key_exists($key, $error_msg1)) {
                $this->init_error_msg($error_msg1, $key);
            }
            $this->append_error_msg($error_msg1, $key, $value['flag'], $value['msg']);
        }
        return $error_msg1;
    }
	// 只检查导入文件的手工填写内容，并将其数据格式转成与网页版的添加单条软件一致
    // 1，将每一行数组的key由数字转成对应数据库的列名，如0为extend_id，1为extent_name...
    // 2，将某些列的字符串转成数字，如是、否转化成1，0......
    function handwriting_convert_and_check_suggest(&$content_arr) 
	{
        // 初始化错误信息数组
        $error_msg = array();
        foreach($content_arr as $key => $value) 
		{
            $this->init_error_msg($error_msg, $key);
        }
		
        // 业务逻辑：将表里字段名称和模版里列的名称一一对应
        $correct_title_arr = array(
            'package'      =>  '广告位包名',
		    'search_words' =>  '搜索词(多个搜索词以‘,’分隔)',
            'start_time'   =>  '开始时间(yyyy/MM/dd)',
            'end_time'     =>  '结束时间(yyyy/MM/dd)',
			'type' =>   '合作形式',
			'soft_rank' =>   '软件排序（1或2）',
			'beid' => '行为id',
        );
        // trim一下所有的数据
        foreach($content_arr as $key=>$record) 
		{
            foreach($record as $r_key=>$r_record) 
			{
                $content_arr[$key][$r_key] = trim($r_record);
            }
        }
        // 给$content_arr里的每一行记录的每一列下标由数字改成对应名称
        $new_content_arr = array();
        $new_key = array();
        // 将$correct_title_arr里的key值提取出来依次放在$new_key里
        foreach($correct_title_arr as $key => $value) 
		{
            $new_key[] = $key;
        }
		
        foreach($content_arr as $key=>$record) 
		{
            foreach($new_key as $new_key_key=>$new_key_value) 
			{
                if (isset($record[$new_key_key])) 
				{
                    $new_content_arr[$key][$new_key_value] = $record[$new_key_key];
                }
            }
        }
        $content_arr = $new_content_arr;
		// 业务逻辑：检查列填写是否为预期文字，如果是则换成对应数据，如果不是则添加错误信息   
		$expected_words = array();   
		// 当输入为空不允许时，将其值设为false以作区别 
		//合作形式
		$util = D("Sj.Util");
		$typelist = $util->get_cooperation();
		$expected_words['type'] =$typelist;
		$expected_words['soft_rank'] = array("" => 0, "1" => 1, "2" => 2);
        foreach($content_arr as $key=>$record) 
		{
            // 开始检查每列内容是否为预期内容
            foreach($record as $r_key=>$r_value) 
			{
				if (array_key_exists($r_key, $expected_words)) {   
					if (!array_key_exists($r_value, $expected_words[$r_key])) {   
					$column = $correct_title_arr[$r_key];      		
					$this->append_error_msg($error_msg, $key, 1, "{$column}列内容填写有误;");
					} else {
							$tmp = $expected_words[$r_key][$r_value];         
							// 如果是false不处理（在后台的logic_check()里会统一进行非空检查），即还是为空，否则替换成相应的数字
							if ($tmp !== false)     				 
							$content_arr[$key][$r_key] = $tmp;   
					}
                
				}
                // 判断开始时间和结束时间，去掉T如果没有精确到秒后面加上00:00:00,23:59:59
                if ($r_key == 'start_time') 
				{
				    if($r_value!="")
				    {
					  if(strpos($content_arr[$key][$r_key],"T")==0)
					  {
					    $content_arr[$key][$r_key]=substr($content_arr[$key][$r_key], 1);
					  }
					  if(strpos($content_arr[$key][$r_key],"/")==true)
					  {
						$content_arr[$key][$r_key]=str_replace("/","-",$content_arr[$key][$r_key]);
					  }
					  if(strpos($content_arr[$key][$r_key],":")==false)
					  {
						$content_arr[$key][$r_key] .= ' 00:00:00';
					  }
				    }
                } 
				else if ($r_key == 'end_time') 
				{
				   if($r_value!="")
				    {
					   if(strpos($content_arr[$key][$r_key],"T")==0)
					   {
					    $content_arr[$key][$r_key]=substr($content_arr[$key][$r_key], 1);
					   }
					   if(strpos($content_arr[$key][$r_key],"/")==true)
					   {
						$content_arr[$key][$r_key]=str_replace("/","-",$content_arr[$key][$r_key]);
					   }
					   if(strpos($content_arr[$key][$r_key],":")==false)
					   {
						$content_arr[$key][$r_key] .= ' 23:59:59';
					   }
					}
                }
            }
        }
        return $error_msg;
    }
	
	// 初始单条错误信息，初始化信息：flag为0，msg为空
    function init_error_msg(&$error_msg, $key) 
	{
        if (!isset($error_msg))
            $error_msg = array();
        $error_msg[$key] = array('flag' => 0,'msg' => '');
    }
    
    // 添加错误信息
    function append_error_msg(&$error_msg, $key, $flag, $msg) 
	{
        if (!isset($error_msg[$key])) {
            $this->init_error_msg($error_msg, $key);
        }
        $error_msg[$key]['flag'] |= $flag;
        $error_msg[$key]['msg'] .= $msg;
    }
	
	 // 统一的逻辑检查：检查添加软件数据是否合法
    function logic_check_suggest($content_arr) 
	{
        // 初始化错误信息数组
        $error_msg = array();
		foreach($content_arr as $key => $value) 
		{
            $this->init_error_msg($error_msg, $key);
        }
        // 业务逻辑：获得热词表model
        $suggest_model = M("think_words");
        // 业务逻辑：以下为各项具体检查

        foreach($content_arr as $key=>$record) 
		{
			if (!$content_arr[$key]['pid']) {
				$content_arr[$key]['pid'] = 1;
			}
            // 检查搜索suggest包名是否为空
            if (isset($record['package']) && !empty($record['package'])) 
			{
			    //检查包名是否存在
				$model = new Model(); 
				$soft_result = $model -> table('sj_soft') -> where(array('package' => $content_arr[$key]['package'],'status' => 1)) -> select();
				if($soft_result=="")
				{
					$this ->append_error_msg($error_msg, $key, 1, "您填写的包名不存在;");
				}
            } 
			else 
			{
                $this->append_error_msg($error_msg, $key, 1, "搜索suggest广告包名不能为空;");
            }
            // 检查搜索热词是否为空
            if (isset($record['search_words']) && !empty($record['search_words'])) 
			{
				$arr = explode(',',$record['search_words']);
				$num = count($arr);
				$a = array_unique($arr);
				$new_num = count($a);
				foreach($arr as $k=>$v)
				{
					if(!preg_match("/^[\x{4e00}-\x{9fa5}0-9a-zA-Z]+$/u",$v)){
						$this->append_error_msg($error_msg, $key, 1, "您填写的内容有误;");
						break;
					}	
				}
				if($num!=$new_num)
				{
					$this->append_error_msg($error_msg, $key, 1, "您填写的内容有重复;");
				}
            } 
			else 
			{
                $this->append_error_msg($error_msg, $key, 1, "搜索词不能为空;");
            }
            // 检查开始时间
            if (isset($record['start_time']) && $record['start_time'] != "") 
			{
                if (!preg_match("/^\d{4}(\-|\/|\.)\d{1,2}(\-|\/|\.)\d{1,2}\s+\d{1,2}:\d{1,2}:\d{1,2}$/", $record['start_time'])) 
				{
                    $this->append_error_msg($error_msg, $key, 1, "开始时间日期格式不对;");
                } 
				else 
				{
				    $time = strtotime($record['start_time']);
                    if ($time) {
                        $content_arr[$key]['bk_start_tm'] = $time;
                    } else {
                        $this->append_error_msg($error_msg, $key, 1, "开始时间日期格式不对;");
                    }
                }
            }
			else 
			{
                $this->append_error_msg($error_msg, $key, 1, "开始时间不能为空;");
            }
            // 检查结束时间
            if (isset($record['end_time']) && $record['end_time'] != "") 
			{
                if (!preg_match("/^\d{4}(\-|\/|\.)\d{1,2}(\-|\/|\.)\d{1,2}\s+\d{1,2}:\d{1,2}:\d{1,2}$/", $record['end_time'])) 
				{
                    $this->append_error_msg($error_msg, $key, 1, "结束时间日期格式不对;");
                } 
				else 
				{
				    $time = strtotime($record['end_time']);
                    if ($time) {
                        $content_arr[$key]['bk_end_tm'] = $time;
                    } else {
                        $this->append_error_msg($error_msg, $key, 1, "结束时间日期格式不对;");
                    }
                }
            }
			else 
			{
                $this->append_error_msg($error_msg, $key, 1, "结束时间不能为空;");
            }
            // 检查开始时间是否小于结束时间
            if (isset($content_arr[$key]['bk_start_tm']) && isset($content_arr[$key]['bk_end_tm'])) 
			{
                if ($content_arr[$key]['bk_start_tm'] > $content_arr[$key]['bk_end_tm']) 
				{
                    $this->append_error_msg($error_msg, $key, 1, "开始时间需小于结束时间;");
                }
            }
			//检查软件排序必填
			if ($record['soft_rank']==0) 
			{
                $this->append_error_msg($error_msg, $key, 1, "搜索suggest软件排序不能为空;");
            }
			if(isset($record['search_words']) && !empty($record['search_words']))
			{
			    $new_str=array();
				$sel = $suggest_model->table("sj_think_words")->where("((start_time <= {$content_arr[$key]['bk_end_tm']} and end_time >= {$content_arr[$key]['bk_start_tm']}) or (start_time <= {$content_arr[$key]['bk_start_tm']} and end_time >= {$content_arr[$key]['bk_end_tm']})) and (soft_rank = {$record['soft_rank']} or package ='{$record['package']}')  and status =1 and pid={$content_arr[$key]['pid']}")->select();
//				echo $suggest_model->getLastSql();
				foreach($sel as $k=>$v)
				{
					$new_str[] =  $v['search_words'];
				}
				$res = array_unique(array_intersect($new_str,$arr));
				$data=implode(",",$res);
				if($rank) $rank = '';
				if(!empty($res))
				{

					//一个搜索词返回推荐rank
					if(count($arr)==1){
						if($record['soft_rank']==1){
							$rank = 2;
						}else{
							$rank = 1;
						}
						$target_pack = $suggest_model->table("sj_think_words")->where("((start_time <= {$content_arr[$key]['bk_end_tm']} and end_time >= {$content_arr[$key]['bk_start_tm']}) or (start_time <= {$content_arr[$key]['bk_start_tm']} and end_time >= {$content_arr[$key]['bk_end_tm']})) and package ='{$record['package']}' and search_words= '{$data}' and status =1")->find();
						if($target_pack){
							$rank = 0;
						}else{
							$target = $suggest_model->table("sj_think_words")->where("((start_time <= {$content_arr[$key]['bk_end_tm']} and end_time >= {$content_arr[$key]['bk_start_tm']}) or (start_time <= {$content_arr[$key]['bk_start_tm']} and end_time >= {$content_arr[$key]['bk_end_tm']})) and search_words = '{$data}'  and soft_rank in (1,2) status =1")->count();
							if($target >= 2){
								$rank = 0;
							}
						}

					}else{
						$rank = 3;
					}
//					var_dump($data);
					$this->append_error_msg($error_msg, $key, 1, "当前排期中您填写【{$data}】的搜索词与数据库中的搜索词有冲突;rank={$rank}");
				}
			}
		}
//		var_dump($res);
        // 检查行与行之间的数据是否冲突
        foreach($content_arr as $key1=>$record1) 
		{
            // 如果开始时间或结束时间无效，则不比较
            if (!isset($record1['bk_start_tm']) || !isset($record1['bk_end_tm']))
                continue;
            foreach($content_arr as $key2=>$record2) {
                // 比较过的不比较
                if ($key1 >= $key2)
                    continue;
                // 如果开始时间或结束时间无效，则不比较
                if (!isset($record2['bk_start_tm']) || !isset($record2['bk_end_tm']))
                    continue;
				//搜索词是否重复
				$search_words_arr1 = explode(',',trim($record1['search_words']));
				$search_words_arr2 = explode(',',trim($record2['search_words']));
				// 将每个关键字前后空格去掉
				foreach ($search_words_arr1 as $k => $value) 
				{
					$search_words_arr1[$k] = trim($value);
				}
				foreach ($search_words_arr2 as $key22 => $value) 
				{
					$search_words_arr2[$key22] = trim($value);
				}
				$intersect=array_intersect(array_filter($search_words_arr1),array_filter($search_words_arr2));
				$k1 = $key1 + 1;
                $k2 = $key2 + 1;
				//包名相同  时间交叉  搜索词不同可以添加
                if ($record1['package'] == $record2['package']) 
				{
                    if ($record1['bk_start_tm'] <= $record2['bk_end_tm'] && $record2['bk_start_tm'] <= $record1['bk_end_tm']) 
					{
						if($intersect)
						{
							
							//if($record1['soft_rank']==$record2['soft_rank'])
							//{
								$this->append_error_msg($error_msg, $key1, 1, "同一包名下，时间交叉下，排序相同，关联热词与第{$k2}行有冲突;");
								$this->append_error_msg($error_msg, $key2, 1, "同一包名下，时间交叉下，排序相同，关联热词与第{$k1}行有冲突;");
							//}
						}
                    }
                }
				else
				{//包名不同 搜索词相同 时间不能交叉 排序不能相同
					if($intersect)
					{
						$data = implode(',',$intersect);
						if ($record1['bk_start_tm'] <= $record2['bk_end_tm'] && $record2['bk_start_tm']    <= $record1['bk_end_tm']) 
						{
							//排序不同可以添加
							if($record1['soft_rank']==$record2['soft_rank'])
							{
								$this->append_error_msg($error_msg, $key1, 1, "关联词中的“{$data}”，同一排序下投放时间时间与第{$k2}行有交叉;");
								$this->append_error_msg($error_msg, $key2, 1, "关联词中的“{$data}”，同一排序下投放时间与第{$k1}行有交叉;");
							}
						}
					}
				}
            }
        }
        
        // 检查每一行数据是否与数据库的存储内容相冲突
        foreach($content_arr as $key=>$record) 
		{
            // 如果开始时间或结束时间无效，则不比较
            if (!isset($record['bk_start_tm']) || !isset($record['bk_end_tm']))
                continue;
            // 查找包名相同的记录
            $where = array(
                'package' => array('EQ', $record['package']),
                'status' => array('NEQ', 0),
                'start_time' => array('ELT', $record['bk_end_tm']),
                'end_time' => array('EGT', $record['bk_start_tm']),
            );
            // 如果是编辑，需在后台记录中排除自己
            if (isset($record['id'])) {
                $where['id'] = array('NEQ', $record['id']);
            }
            $db_records = $suggest_model->table("sj_think_words")->where($where)->select();
            // 有冲突的记录
            foreach($db_records as $db_key=>$db_record) {
                $start_time_str = date('Y-m-d H:i:s',$db_record['start_time']);
                $end_time_str = date('Y-m-d H:i:s',$db_record['end_time']);
                $status_paused_hint = "";
                if ($db_record['status'] == 2) 
				{
                    //$status_paused_hint = "，该记录处于已停用状态，请前往批量明细列表中操作";
					$this->append_error_msg($error_msg, $key, 1, "该记录处于已停用状态，请前往批量明细列表中操作;");
                }
				
                //$this->append_error_msg($error_msg, $key, 1, "同一包名下，投放时间与后台记录ID为【{$db_record['id']}】、搜索词为【{$db_record['search_words']}】的记录有冲突（其时间从【{$start_time_str}】到【{$end_time_str}】{$status_paused_hint}）;");
            }
        }
        return $error_msg;
    }
	
	// 业务逻辑：将批量导入文件里所有数据添加进数据库，返回结果为每一行添加是否成功标志符
    function process_import_array_suggest($content_arr) {
		if(C('is_test') == 1){
			$anzhi_ad_type = 1;
		}else{
			$anzhi_ad_type = 0;
		}			
        $result_arr = array();
        $model = M('think_words');
		$AdSearch = D("Sj.AdSearch");
        $arr_shields=array();
        foreach($content_arr as $key => $record) {
            $map = array();
            // 设置默认值
			$map['status'] = 1;
			$map['anzhi_ad_type'] = $anzhi_ad_type;
			$map['up_time'] = time();
			$map['create_time'] = time();
			$map['admin_id'] = $_SESSION['admin']['admin_id'];
            // 赋值，以下为必填的值
			$map['search_words'] = $record['search_words'];
			$map['start_time'] = strtotime($record['start_time']);
			$map['end_time'] = strtotime($record['end_time']);
			$map['package']=$record['package'];
			$map['soft_rank']=$record['soft_rank'];
			//赋值，以下为非必填值
			$map['type'] = isset($record['type']) ? $record['type'] : 0;
			$map['beid'] = isset($record['beid']) ? trim($record['beid']) : 0;
            
            $data_error=$AdSearch->pub_check_soft_filter($map['package']);
            if($data_error && $data_error['code']==1){
            	$result_arr[$key]=array('flag'=>0,'msg'=>$data_error['message'],'package'=>$map['package']);
            	$arr_shields[]=$map;
            	continue;
            }
            
            // 添加到表中
			if ($id = $model->add($map)) {
				$this->writelog('广告批量排期管理_广告位列表_搜索Suggest批量添加'.$record['package'],"sj_think_words",$id,__ACTION__ ,"","add");
                $result_arr[$key]['flag'] = 1;
                $result_arr[$key]['msg'] = "添加成功";
			} 
			// else {
                // 未知原因添加失败
                // $result_arr[$key]['flag'] = 0;
                // $result_arr[$key]['msg'] = "添加失败";
			// }
        }
        if(count($arr_shields) && $file_data=$AdSearch->generate_ignore_file($arr_shields,'sj_think_words')){
        	$result_arr['table_name']='sj_think_words';
        	$result_arr['filename']=$file_data['filename'];
        }
        return $result_arr;
    }
}