<?php 

class InstallrecommendAction extends CommonAction{

	function recommend_list(){
		$model = new Model();
		$config_result = $model -> table('pu_config') -> where(array('config_type' => 'INSTALL_RECOMMEND','status' => 1)) -> find();
		
		$soft_name = $_GET['soft_name'];
		if($soft_name){
			$soft_name_where['_string'] = "softname like '%{$soft_name}%' and status = 1 and hide = 1";
			$soft_name_result = $model -> table('sj_soft') -> where($soft_name_where) -> select();
		
			foreach($soft_name_result as $key => $val){
				$package_str_go .= "'".$val['package']."',";
			}
			$package_str = substr($package_str_go,0,-1);
			$where_go .= " and package in ({$package_str})";
		}
		$package = $_GET['package'];
		if($package){
			$where_go .= " and package = '{$package}'";
		}
		$where['_string'] = "status = 1".$where_go;
		$count = $model -> table('sj_install_recommend') -> where($where) -> count();
		
		import("@.ORG.Page");
		$param = http_build_query($_GET);
		$Page = new Page($count, 10, $param);
		$result = $model -> table('sj_install_recommend') -> where($where) -> limit($Page->firstRow . ',' . $Page->listRows) -> select();
		
		foreach($result as $key => $val){
			$soft_result = $model -> table('sj_soft') -> where(array('package' => $val['package'],'status' => 1,'hide' => 1)) -> select();
			$val['soft_name'] = $soft_result[0]['softname'];
			//渠道没填写 默认是0
			if($val['cid']=="0"){
				$val['chname'] = '';
			}else{
				//渠道填写用英文,隔开
				$cid_arr = explode(',',$val['cid']);
				$chname_go = '';
				foreach($cid_arr as $k => $v){
					//填写的渠道有通用渠道的处理
					if($v==0&&$v!=''){
						$chname_go .="通用,";
					}else{
						$channel_result = $model -> table('sj_channel') -> where(array('cid' => $v)) -> select();
						$chname_go .= $channel_result[0]['chname'].',';
					}
				}
				$val['chname'] = substr($chname_go,1,-2);
			}
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
			$lr = 10;
		}

		$Page->setConfig('header', '篇记录');
		$Page->setConfig('`first', '<<');
		$Page->setConfig('last', '>>');
		$show = $Page->show();
		$this->assign("page", $show);
		$this -> assign('p',$p);
		$this -> assign('lr',$lr);
		$this -> assign('result',$result);
		$this -> assign('config_result',$config_result);
		$this -> assign('soft_name',$soft_name);
		$this -> assign('package',$package);
		$this -> display();
	}

	function edit_config(){
		$model = new Model();
		$config = $_GET['config'];
		$result = $model -> table('pu_config') -> where(array('config_type' => 'INSTALL_RECOMMEND','status' => 1)) -> save(array('configcontent' => $config,'uptime'=>time()));
		
		if($config == 1){
			$configs = "全部安装成功页";
		}elseif($config == 2){
			$configs = "指定软件安装成功页";
		}elseif($config == 0){
			$configs = "关闭";
		}
		if($result){
			$this -> writelog("已编辑下载推荐范围为{$configs}",'pu_config','INSTALL_RECOMMEND',__ACTION__ ,"","edit");
			echo $config;
			return $config;
		}else{
			echo -1;
			return -1;
		}
	}

	function edit_recommend(){
		$model = new Model();
		if($_POST){
			$id = $_POST['id'];
			$soft_names = $_POST['soft_names'];
			if($soft_names){
				$where_go .= "/soft_name/{$soft_names}";
			}
			$packages = $_POST['packages'];
			if($packages){
				$where_go .= "/package/{$packages}";
			}
			$p = $_POST['p'];
			$where_go .= "/p/{$p}";
			$lr = $_POST['lr'];
			$where_go .= "/lr/{$lr}";

			$package = $_POST['package'];
			if(!$package){
				$this -> error("请填写软件包名");
			}
			$have_package = $model -> table('sj_soft') -> where(array('package' => $package,'status' => 1,'hide' => 1)) -> select();
			if(!$have_package){
				$this -> error("该软件包名不存在");
			}

			$cid = $_POST['cid'];
			if(!$cid){
				$cid = 0;
			}
			if($cid == 0){
				$have_where_all['_string'] = "package = '{$package}' and status = 1";
				if ($id) {
					$have_where_all['_string'] .= ' and id !='.$id;
				}
				$have_been_result_all = $model -> table('sj_install_recommend') -> where($have_where_all) -> select();
				if($have_been_result_all){
					$this -> error("相同渠道内不能添加同一包名");
				}
				$cid_str = 0;
			}else{
				foreach($cid as $key => $val){
					$have_where['_string'] = "package = '{$package}' and (cid like '%,{$val},%' or cid = '0') and status = 1";
					if ($id) {
						$have_where['_string'] .= ' and id !='.$id;
					}
					$have_been_result = $model -> table('sj_install_recommend') -> where($have_where) -> select();
					if($have_been_result){
						$this -> error("相同渠道内不能添加同一包名");
					}
					$cid_str_go .= $val.',';
				}
				if($cid_str_go){
					$cid_str = ','.$cid_str_go;
				}
			}
			$data = array(
				'package' => $package,
				'cid' => $cid_str,
				'update_tm' => time(),
				'admin_id'=>$_SESSION['admin']['admin_id'],
			);

			if(!$id){
				$data['status'] = 1;
				$data['create_tm'] = time();
				$result = $model -> table('sj_install_recommend') -> add($data);
			}else{
				$log_result = $this -> logcheck(array('id' => $id),'sj_install_recommend',$data,$model);
				$result = $model -> table('sj_install_recommend') -> where(array('id' => $id)) -> save($data);
			}		
			
			if($result){
				if(!$id){
					$this -> writelog("已添加id为{$result}的下载推荐",'sj_install_recommend',$result,__ACTION__ ,"","add");
					$this -> assign('jumpUrl','/index.php/Sj/Installrecommend/recommend_list'.$where_go);
					$this -> success("添加成功");
				}else{
					$this -> writelog("已编辑id为{$id}的下载推荐".$log_result,'sj_install_recommend',$id,__ACTION__ ,"","edit");
					$this -> assign('jumpUrl','/index.php/Sj/Installrecommend/recommend_list'.$where_go);
					$this -> success("编辑成功");
				}
			}else{
				$this -> error("操作失败");
			}
		}else{
			$id = $_GET['id'];
			$soft_names = $_GET['soft_name'];
			$packages = $_GET['package'];
			$p = $_GET['p'];
			$lr = $_GET['lr'];
			$operating_result = $model -> table('pu_operating') -> select();
			if($id){
				$result = $model -> table('sj_install_recommend') -> where(array('id' => $id)) -> find();
				$the_cid = substr($result['cid'],1,-1);
				$chl_where['_string'] = "cid in ({$the_cid})";
				$chl_result = $model -> table('sj_channel') -> where($chl_where) -> select();
				$softinfo = $model->table('sj_soft')->where(array('status'=>1,'hide'=>1,'package'=>$result['package']))->find();
				$this -> assign('softname', $softinfo['softname']);
				$this -> assign('result',$result);
				$this -> assign('chl_list',$chl_result);
			}
		
			$this -> assign('soft_names',$soft_names);
			$this -> assign('packages',$packages);
			$this -> assign('p',$p);
			$this -> assign('lr',$lr);
			$this -> assign('operating_result',$operating_result);
			$this -> display();
		}
	}

	function del_recommend(){
		$model = new Model();
		$id = $_GET['id'];
		$soft_names = $_GET['soft_name'];
		if($soft_names){
			$where_go .= "/soft_name/{$soft_names}";
		}
		$packages = $_GET['package'];
		if($packages){
			$where_go .= "/package/{$packages}";
		}
		$p = $_GET['p'];
		$where_go .= "/p/{$p}";
		$lr = $_GET['lr'];
		$where_go .= "/lr/{$lr}";
		$result = $model -> table('sj_install_recommend') -> where(array('id' => $id)) -> save(array('status' => 0,'update_tm' => time()));
		if($result){
			$this -> writelog("已删除id为{$id}的安装推荐",'sj_install_recommend',$id,__ACTION__ ,"","del");
			$this -> assign('jumpUrl','/index.php/Sj/Installrecommend/recommend_list'.$where_go);
			$this -> success("删除成功");
		}else{
			$this -> error("删除失败");
		}
	}

	//批量导入
    function import_softs() {
        if ($_GET['down_moban']) {
            $this->down_moban();
        } else if ($_FILES) {
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
            $lock_name = 'sj_install_recommend_soft_importing';
            $import_lock = S($lock_name);
            if ($import_lock) {
                $this->ajaxReturn("",'后台有人正在导入，请稍后再尝试！', 1);
            }
            // 上锁，设置60秒内有效
            S($lock_name, 1, 60, 'File');
            // 返回导入结果，如果记录的flag为0表示添加失败
            $fail_arr = $this->process_import_array($content_arr);
            // 导入后解锁
            S($lock_name, NULL);
            $flag = true;
            foreach($fail_arr as $key=>$value) {
                if ($value['flag'] == 1)
                    $flag = false;
            }
            // save the import file for backups
            $save_dir = IMPORT_FILE_UPLOAD_PATH;
            $this->mkDirs($save_dir);
            $save_name = MODULE_NAME. '_' . ACTION_NAME  . '_' . time() . '_' . $_SESSION['admin']['admin_id'] . '.csv';
            $save_file_name = $save_dir . $save_name;
            move_uploaded_file($_FILES['upload_file']['tmp_name'], $save_file_name);
            $this->writelog("安装推荐：批量导入了{$save_file_name}。");
            if ($flag) {
                $this->ajaxReturn("",'导入成功！', 0);
            } else {
                $this->ajaxReturn($fail_arr,'存在部分导入失败记录！', -6);
            }
        } else {
            $this->display("import_softs");
        }
    }

    // 下载批量导入模版
    function down_moban() {
        $file_dir = C("ADLIST_PATH") . "install_recommend_import_moban.csv";
        if (file_exists($file_dir)) {
            $file = fopen($file_dir,"r");
            Header("Content-type: application/octet-stream");
            Header("Accept-Ranges: bytes");
            Header("Accept-Length: ".filesize($file_dir));
            Header("Content-Disposition: attachment; filename=" . urlencode('安装推荐批量导入模版') . ".csv");
            echo fread($file, filesize($file_dir));
            fclose($file);
            exit(0);
        } else {
            header("Content-type: text/html; charset=utf-8");
            echo "File not found!";
            exit;
        }
    }

    // 第一行标题列忽略，只保存之后的内容
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

    function import_array_convert_and_check(&$content_arr) {
        // 文件转换数据前的检查（是否可以转化成与页面数据格式一致）
        $error_msg1 = $this->handwriting_convert_and_check($content_arr);
        // 文件转换数据后的检查（区间是否有效、排期是否冲突等）
        $error_msg2 = $this->logic_check($content_arr);
        // 将$error_msg2合并到$error_msg1里并返回$error_msg1
        //屏蔽软件上排期时报警需求 新增  yuesai
		$AdSearch = D("Sj.AdSearch");
        $error_msg3 = $AdSearch->logic_check_shield($content_arr,'start_tm','end_tm');
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

    // 只检查导入文件的手工填写内容，并将其数据格式转成与网页版的添加一致
    // 1，将每一行数组的key由数字转成对应数据库的列名，如0为extend_id，1为extent_name...
    // 2，将某些列的字符串转成数字，如是、否转化成1，0...
    function handwriting_convert_and_check(&$content_arr) {
        // 初始化错误数组
        $error_msg = array();
        foreach($content_arr as $key => $value) {
            $this->init_error_msg($error_msg, $key);
        }
        // 业务逻辑：将表里字段名称和模版里列的名称一一对应
        $correct_title_arr = array(
            'package'  =>   '推荐软件包名',
			'cid' =>'渠道',
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
        // 业务逻辑：检查列填写是否为预期文字，如果是则换成对应数据，如果不是则添加错误信息
        $expected_words = array();
        // 当输入为空不允许时，将其值设为false以作区别
		//合作形式
		$util = D("Sj.Util");
		$typelist = $util->get_cooperation();
		$expected_words['type'] =$typelist;
		
        foreach($content_arr as $key=>$record) {
            // 开始检查每列内容是否为预期内容
            foreach($record as $r_key=>$r_value) {
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
            }
        }
        return $error_msg;
    }

    // 统一的逻辑检查：检查添加软件数据是否合法
    function logic_check($content_arr) {
        // 初始化错误数组
        $error_msg = array();
        foreach($content_arr as $key => $value) {
            $this->init_error_msg($error_msg, $key);
        }
        // 业务逻辑：
        $install_recommend = M('install_recommend');
        $soft_model = M("soft");

        // 业务逻辑：以下为各项具体检查
        foreach($content_arr as $key=>$record) {
			// 检查包名是否在sj_soft表里
            if (!empty($record['package'])) {
                $where = array(
                    'package' => $record['package'],
                    'status' => 1,
                    'hide' => 1,
                );
                $find = $soft_model->where($where)->find();
                if (!$find) {
                    $this->append_error_msg($error_msg, $key, 1, "包名【{$record['package']}】不存在于市场软件库中;");
                }
            } else {
                $this->append_error_msg($error_msg, $key, 1, "包名不能为空;");
            }

			$have_no_cid = 0;
			//有渠道  判断渠道名称是否正确  然后判断渠道下面的包是否存在
			if($record['cid']){
				$chname_arr = explode(',',$record['cid']);
				foreach($chname_arr as $k =>$val){
					$channel_where = array(
						'chname' => trim($val),
						'status' => 1,
					);
					//根据渠道名称获取渠道id
					$cid_result = $install_recommend->table('sj_channel')->where($channel_where)->find();
					if($cid_result){
						$have_where['_string'] = "package = '{$record['package']}' and (cid like '%,{$cid_result['cid']},%' or cid = '0') and status = 1";
						$have_result = $install_recommend->where($have_where)->select();
						if($have_result)
							$this->append_error_msg($error_msg, $key, 1, "填写的渠道名称【{$record['cid']}】内已存在包名【{$record['package']}】;");
							break;
					}else{
						$this->append_error_msg($error_msg, $key, 1, "填写的渠道名称【{$record['cid']}】错误;");
						break;
					}
				}
			}else{
				$have_where['_string'] = "package = '{$record['package']}' and cid = '0' and status = 1";
				$have_result = $install_recommend->where($have_where)->select();
				if($have_result)
					$this->append_error_msg($error_msg, $key, 1, "安装推荐列表已存在包名【{$record['package']}】;");
			}
        }

        // 检查行与行之间的数据是否冲突（主要检查相同包名的区间是否有冲突）
        foreach($content_arr as $key1=>$record1) {
            foreach($content_arr as $key2=>$record2) {
                // 比较过的不比较
                if ($key1 >= $key2)
                    continue;
				$k1 = $key1 + 1; $k2 = $key2 + 1;
				// 包名相同
                if ($record1['package'] == $record2['package']){
                	$this->append_error_msg($error_msg, $key1, 1, "包名与第{$k2}行重复;");
                    $this->append_error_msg($error_msg, $key2, 1, "包名与第{$k1}行重复;");
                    continue;
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
    function process_import_array($content_arr) {
        $fail_arr = array();
        $model = M('install_recommend');
        $AdSearch = D("Sj.AdSearch");
        $arr_shields=array();
        foreach($content_arr as $key => $record) {
            $map = array();
            // 设置默认值
			$map['status'] = 1;
            $map['create_tm'] = time();
            $map['update_tm'] = time();
			$map['admin_id'] = $_SESSION['admin']['admin_id'];
			$map['package'] = $record['package'];
			if($record['cid']){
				$chname_arr = explode(',', $record['cid']);
				$cid_str = ",";
				foreach($chname_arr as $key =>$val){
					$channel_where=array(
						'chname' =>trim($val),
						'status' =>1,
					);
					//根据渠道名称获取渠道id
					$cid_result = $model->table('sj_channel')->where($channel_where)->find();
					$cid_str .= $cid_result['cid'].',';
				}
			}else{
				$cid_str = 0;
			}
			$map['cid'] = $cid_str;
			
            $data_error = $AdSearch->pub_check_soft_filter($map['package']);
            if($data_error && $data_error['code']==1){
            	$fail_arr[$key] = array('flag'=>1,'msg'=>$data_error['message'],'package'=>$map['package']);
            	$arr_shields[]=$map;
            	continue;
            }
            // 添加到表中
			if ($id = $model->add($map)) {
				$this->writelog("安装推荐-添加了ID为[{$id}]包名为{$map['package']}的软件",'sj_install_recommend',$id,__ACTION__ ,"","add");
                $fail_arr[$key]['flag'] = 0;
                $fail_arr[$key]['msg'] = "添加成功";
			} 
			// else {
                // $fail_arr[$key]['flag'] = 1;
                // $fail_arr[$key]['msg'] = "添加失败";
			// }
        }
        if(count($arr_shields) && $file_data = $AdSearch->generate_ignore_file($arr_shields,'sj_install_recommend')){
        	$fail_arr['table_name']='sj_install_recommend';
        	$fail_arr['filename'] = $file_data['filename'];
        }
        return $fail_arr;
    }
}