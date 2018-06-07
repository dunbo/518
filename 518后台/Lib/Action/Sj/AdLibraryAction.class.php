<?php
    class AdLibraryAction extends CommonAction {
        
        public function index($app_from = 1) {
            $act = empty($_GET['act']) ? '' : $_GET['act'];

            if($act=='export_data'){
                $this->export_data();
                return;
            }
            // trim 一下
            foreach ($_GET as $key => $value) {
                $_GET[$key] = trim($value);
            }
            $where = array();
            $where['status'] = 1;
			$where['app_from'] = $app_from;			
			$this->assign('app_from', $app_from);
            // 如果有搜索条件
            if ($_GET['search_package']) {
                $search_package = $_GET['search_package'];
                $where['package'] = $search_package;
            }
            if ($_GET['search_principal']) {
                $search_principal = $_GET['search_principal'];
                $where['principal'] = array('like',"%$search_principal%");
            }
            if ($_GET['search_type']) {
                $search_type = $_GET['search_type'];
                $where['type'] = $_GET['search_type'];
            }
            if ($_GET['search_softname']) {
                $search_softname = $_GET['search_softname'];
                $where['softname'] = array('like',"%$search_softname%");
            }
            if ($_GET['search_ad_client']) {
                $search_ad_client = $_GET['search_ad_client'];
                $where['ad_client'] = array('like',"%$search_ad_client%");
            }
            // if ($_GET['search_start_time']) {
            //     $search_start_time = strtotime($_GET['search_start_time']);
            //     $where['start_time'] = array('egt', $search_start_time);
            // }
            // if ($_GET['search_end_time']) {
            //     $search_end_time = strtotime($_GET['search_end_time']);
            //     $where['end_time'] = array('elt', $search_end_time+86399);
            // }
            
            import("@.ORG.Page");
            $model = M();
            $count = $model->table('sj_ad_library')->where($where)->count();
            $page = new Page($count, 20);
            $list = $model->table('sj_ad_library')->where($where)->order('create_tm desc')->limit($page->firstRow.','.$page->listRows)->select();
            header('Content-Type: text/html; charset=utf-8'); 
            $util = D("Sj.Util");
            // $result = array();
            foreach($list as $key=>$val) {
                $typelist = $util->getHomeExtentSoftTypeList($val['type']);
                foreach($typelist as $k => $v){
                    if($v[1] == true){
                        $list[$key]['ad_type'] = $v[0];
                    }
                }
            }
            
            // echo "<pre>";var_dump($list);die;
            $util = D("Sj.Util");
            $typelist = $util->getHomeExtentSoftTypeList();
            $this->assign('typelist',$typelist);
            $show = $page->show();//分页显示输出
            $this->assign('list', $list);
            $this->assign('page', $show);
            $this->assign('search_package', $search_package);
            $this->assign('search_softname', $search_softname);
            $this->assign('search_ad_client', $search_ad_client);
            $this->assign('search_principal', $search_principal);
            $this->assign('search_type', $search_type);
            $this->display('index');
        }
		public function index_operate() {
			$this->index(2);
		}
        public function add_package_operate() {
			if ($_POST) {
				$_POST['app_from'] = 2;
				$this->add_package();
			} else {
                $util = D("Sj.Util");
                $typelist = $util->getHomeExtentSoftTypeList();
                $this->assign('typelist',$typelist);
                $this->assign('app_from',2);
                $this->display('add_package');
            }
		}
        public function add_package() {
            if ($_POST) {
                // 必填字段
                $ness = array(
                    'package' => '包名',
                    'softname' => '软件名称',
                    'ad_client' => '广告主名称',
                    'principal' => '负责人',
                    // 'start_time' => '开始时间',
                    // 'end_time' => '结束时间',
                );
                $data = array();
                foreach ($ness as $key => $value) {
                    if (!$_POST[$key])
                        $this->error("{$ness[$key]}不能为空");
                    $data[$key] = trim($_POST[$key]);
                }
                $data['type'] = $_POST['type'];
                $data['app_from'] = $_POST['app_from'] ? $_POST['app_from'] : 1;
                $data['is_protect'] = $_POST['is_protect'];
                $where = array(
                    'package' => trim($_POST['package']),
                    'status' => 1,
                    'hide' => array('in', '1,1024'),
                );
                $soft_model = M("soft");//软件大表
                $find = $soft_model->order('softid desc')->where($where)->find();
                $category_id=str_replace(',',"",$find['category_id']);
                $find_two = M('category')->where(array('category_id'=>$category_id))->find();
                $data['app_type'] = $find_two['name'];
                $model = M();
                // 检查包名是否存在
                $find = $this->package_search($data['package']);
                if (!$find)
                    $this->error("包名不存在！");
                $where = "package='{$data['package']}' and status=1";
                $conflict = $model->table('sj_ad_library')->where($where)->find();
                if ($conflict) {
					if($conflict['app_from'] == 2){
						$str = "软件来源【运营】";
					}else{
						$str = "软件来源【商务】";
					}
                    $this->error('软件与已存在记录存在冲突！'.$str);
                }
                $data['create_tm']=time();
                $ret = $model->table('sj_ad_library')->add($data);
                // echo $model->getlastsql();die;
                if ($ret) {
					if($_POST['app_from'] == 2){
						$this->assign('jumpUrl', 'index_operate');
					}else{
						$this->assign('jumpUrl', 'index');
					}
					$this->writelog("广告库列表：添加了id为{$ret}的记录",'sj_ad_library',$ret,__ACTION__ ,"","add");
                    $this->success('添加成功！');
                } else {
                    $this->error('添加失败！');
                }
            } else {
                $util = D("Sj.Util");
                $typelist = $util->getHomeExtentSoftTypeList();
                $this->assign('typelist',$typelist);
                $this->display('add_package');
            }
        }
        public function edit_package_operate() {
			$this->assign('app_from',2);
			$this -> edit_package();
		}
        public function edit_package($edit_from=1) {
            if ($_POST) {
                // 必填字段
                $ness = array(
                    'package' => '包名',
                    'softname' => '软件名称',
                    'ad_client' => '广告主名称',
                    'principal' => '负责人',
                );
                $data = array();
                $edit_from=($_POST['edit_from']==2)?2:1;
                foreach ($ness as $key => $value) {
                    if (!$_POST[$key])
                        $this->error("{$ness[$key]}不能为空");
                    $data[$key] = trim($_POST[$key]);
                }
                $data['type'] = $_POST['type'];
                $data['is_protect'] = $_POST['is_protect'];
                $where = array(
                    'package' => trim($_POST['package']),
                    'status' => 1,
                    'hide' => array('in', '1,1024'),
                );
                $find = M('soft')->order('softid desc')->where($where)->find();
                $category_id=str_replace(',',"",$find['category_id']);
                $find_two = M('category')->where(array('category_id'=>$category_id))->find();
                $data['app_type'] = $find_two['name'];
                $model = M();
                // 检查包名是否存在
                $find = $this->package_search($data['package']);
                if (!$find)
                    $this->error("包名不存在！");
                // 检查相同包名有效期冲突
                $where = "id!={$_POST['id']} and package='{$data['package']}' and status=1";
                $conflict = $model->table('sj_ad_library')->where($where)->find();
                if ($conflict) {
					if($conflict['app_from'] == 2){
						$str = "软件来源【运营】";
					}else{
						$str = "软件来源【商务】";
					}
                    $this->error('软件与已存在记录存在冲突！'.$str);
                }
                $where = array(
                    'id' => $_POST['id'],
                );
                $data['update_tm']=time();
                if($edit_from==2){
                    $data['app_from']=1;
                }
				$log = $this->logcheck($where, 'sj_ad_library', $data, $model);
                $ret = $model->table('sj_ad_library')->where($where)->save($data);
                if ($ret || $ret === 0) {
					$this->writelog("广告库列表：编辑了id为{$_POST['id']}的记录，{$log}",'sj_ad_library',$_POST['id'],__ACTION__ ,"","edit");
                    $this->success('编辑成功！');
                } else {
                    $this->error('编辑失败！');
                }
            }
            if ($_GET['id']) {
                $id = $_GET['id'];
                $model = M();
                $where = array(
                    'id' => $id,
                );
                $find = $model->table('sj_ad_library')->where($where)->find();
                $util = D("Sj.Util");
                $typelist = $util->getHomeExtentSoftTypeList($find['type']);
                foreach($typelist as $k => $v){
                    if($v[1] == true){
                        $find['type'] = $v[0];
                    }
                }
                $this->assign('typelist', $typelist);
                $this->assign('record', $find);
                $this->assign('edit_from', $edit_from);
                $this->display('edit_package');
            }
        }
        public function edit_package_new(){
            $this->edit_package(2);
        }
        public function del_pkg_operate() {
			$this-> del_pkg();
		}
        public function del_pkg() {
            if ($_GET['id'] || $_POST['id']) {
                if($_POST['id']) $str='批量';
                $id = $_GET['id']?$_GET['id']:trim($_POST['id'],',');
                $model = M();
                $packages = $model->table('sj_ad_library')->field('id,package,app_from')->where(array('id' => array('in',$id)))->select();
                //屏蔽软件上排期时报警需求 新增  yuesai
                $shield_error=$id_new='';
                $AdSearch = D("Sj.AdSearch");
                foreach($packages as $v){
                    $shield_error_new=$AdSearch->check_ad($v['package'],time(),'','',array('bs_new'=>1,'exclude_fea'=>1));
                    if($shield_error_new)
                        $shield_error.=$shield_error_new;
                    else
                        $id_new.=$v['id'].',';
                }
                if($id_new && $id_new=trim($id_new,',')){
                    $ret = $model->table('sj_ad_library')->where(array('id' => array('in',$id_new)))->save(array('status' => 0,'update_tm'=>time()));
                    if ($ret) {
                        $this->writelog("广告库列表：{$str}删除了id为{$id_new}的记录",'sj_ad_library',$id_new,__ACTION__ ,"","del");
                        if($shield_error)
                             echo $_POST['id']?($shield_error):($this->error($shield_error));
                        else
                            echo $_POST['id']?(1):($this->success("删除成功！"));
                    } else {
                        echo $_POST['id']?(2):($this->success("删除失败！"));
                    }
                }else{
                    echo $_POST['id']?($shield_error):($this->error($shield_error));
                }
            }
        }
        
        // 远程请求检查根据包名查找软件名
        public function pub_check_package() {
            $package = $_POST['package'];
            $find = $this->package_search($package);
            if ($find) {
                $this->ajaxReturn(1, $find, 1);
            } else {
                $this->ajaxReturn(0, '', 0);
            }
        }
        
        private function package_search($package) {
            if (!$package)
                return 0;
            $model = M();
            $where = array(
                'package' => $package,
                'status' => 1,
                'hide' => array('in', '1,1024'),
                // 'hide' => 1,
            );
            $find = $model->table('sj_soft')->where($where)->order('version_code')->order('version_code desc')->find();
            if ($find)
                return $find['softname'];
            return 0;            
        }
		function import_softs_operate() {
            if ($_GET['down_moban']) {
                $this->down_moban();
            } else if ($_FILES) {
				 $this->import_softs(2); 
			} else {
				$this->assign('app_from',2);
                $this->display("import_softs");
            }	
		}
        // 批量导入访问的页面节点
        function import_softs($app_from = 1) {
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
                 // 判断后台有没有人正在导入
                $lock_name = 'sj_ad_library_importing';
                $import_lock = S($lock_name);
                if ($import_lock) {
                    $this->ajaxReturn("",'后台有人正在导入，请稍后再尝试！', 1);
                }
                // 上锁，设置60秒内有效
                S($lock_name, 1, 60, 'File');
                // 返回检查结果的错误信息，如果记录的flag为1表示有错误
				$content_arr['app_from'] = $app_from;
                $error_msg = $this->import_array_convert_and_check($content_arr);
                $flag = true;
                foreach($error_msg as $key=>$value) {
                    if ($value['flag'] == 1)
                        $flag = false;
                }
                // 导入后解锁
                S($lock_name, NULL);
                $save_dir = IMPORT_FILE_UPLOAD_PATH;
                $this->mkDirs($save_dir);
                $save_name = MODULE_NAME. '_' . ACTION_NAME  . '_' . time() . '_' . $_SESSION['admin']['admin_id'] . '.csv';
                $save_file_name = $save_dir . $save_name;
                move_uploaded_file($_FILES['upload_file']['tmp_name'], $save_file_name);
                $this->writelog("广告库管理：批量导入了{$save_file_name}。");
                if ($flag) {
                    $this->ajaxReturn("",'导入成功！', 0);
                } else {
                    $this->ajaxReturn($error_msg,'存在部分导入失败记录！', -6);
                }
            } else {
                $this->display("import_softs");
            }
        }

        // 下载批量导入模版
        function down_moban() {
            $file_dir = C("ADLIST_PATH") . "guanggaoku_import_moban.csv";
            if (file_exists($file_dir)) {
                $file = fopen($file_dir,"r");
                Header("Content-type: application/octet-stream");
                Header("Accept-Ranges: bytes");
                Header("Accept-Length: ".filesize($file_dir));
                Header("Content-Disposition: attachment; filename=" . urlencode('广告库批量导入模版') . ".csv");
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
        // 业务逻辑：将批量导入文件里所有数据添加进数据库，返回结果为每一行添加是否成功标志符
        function process_import_array($record) {
            $model = M('ad_library');
            $map = array();
            // 设置默认值
            $map['status'] = 1;
            $map['create_tm'] = time();
            $map['update_tm'] = time();
            $map['package'] = $record['package'];
            $map['app_from'] = $record['app_from'];
            $where = array(
                'package' => $record['package'],
                'status' => 1,
                'hide' => array('in', '1,1024'),
            );
            $find = M('soft')->order('softid desc')->where($where)->find();
            $map['softname'] = $find['softname'];
            $map['ad_client'] = $record['ad_client'];
            $map['principal'] = $record['principal'];
            $category_id=str_replace(',',"",$find['category_id']);
            $find_two = M('category')->where(array('category_id'=>$category_id))->find();
            $map['app_type'] = $find_two['name'];
            $map['type'] = isset($record['type']) ? $record['type'] : 0;
            // 添加到表中
            if ($id = $model->add($map)) {
                $this->writelog('广告库列表：添加了id为'.$id.',包名为'.$record['package'].'的软件。','sj_ad_library',$id,__ACTION__ ,"","add");
                return 1;
            } 
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
        function handwriting_convert_and_check(&$content_arr) {
            // 初始化错误信息数组
            $error_msg = array();
            foreach($content_arr as $key => $value) {
                $this->init_error_msg($error_msg, $key);
            }
            // 业务逻辑：将表里字段名称和模版里列的名称一一对应
            $correct_title_arr = array(
                'package'  =>   '包名',
                // 'softname'  =>   '软件名称',
                'ad_client'  =>   '广告主名称',
                'principal'  =>   '负责人',
                // 'app_type' => '软件类型',
                'type' => '合作形式',
				'app_from' => '添加来源'
            );
            // trim一下所有的数据
            foreach($content_arr as $key=>$record) {
                foreach($record as $r_key=>$r_record) {
                    $content_arr[$key][$r_key] = trim($r_record);
                    $content_arr[$key][4] = $content_arr['app_from'];
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
            // 业务逻辑：检查列填写是否为预期内容（固定的内容），如果是则换成对应数据，如果不是则添加错误信息
            $expected_words = array();
            // 有些字段输入为空时是合法的，有些字段输入为空不允许，当为空不允许时，将其值设为false以作区别
            // $expected_words['app_type'] = array( "应用" => 1, "游戏" => 2);
            //合作形式
            $util = D("Sj.Util");
            $typelist = $util->get_cooperation();
            $expected_words['type'] =$typelist;
            foreach($content_arr as $key=>$record) {
                // 开始检查每列内容是否为预期内容
                foreach($record as $r_key=>$r_value) {
                    if (array_key_exists($r_key, $expected_words)) {
                        if (!array_key_exists($r_value, $expected_words[$r_key])) {
                            if($r_key!='type'){
                                $column = $correct_title_arr[$r_key];
                                $this->append_error_msg($error_msg, $key, 1, "【{$column}】列内容填写有误;");
                                $content_arr[$key][$r_key] = false;
                            }
                        } else {
                            $tmp = $expected_words[$r_key][$r_value];
                            // 检查是否为false，如果不是，则表示可以为空，替换成相应的数字，否则不处理，即还是为空，在logic_check()里会进行非空值判断
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
            // 初始化错误信息数组
            $error_msg = array();
            foreach($content_arr as $key => $value) {
                $this->init_error_msg($error_msg, $key);
            }
            // 业务逻辑：区间表、区间软件表
            $soft_model = M("soft");//软件大表
            $model = M();
            // 业务逻辑：以下为各项具体检查
            foreach($content_arr as $key=>$record) {
                if($record['package']==null && $record['ad_client']==null && $record['principal']==null && $record['type']==null){
                    unset($content_arr[$key]);
                    continue;
                }
                if (isset($record['package']) && $record['package'] != "") {
                    // 检查包名是否在sj_soft表里
                    $where = array(
                        'package' => $record['package'],
                        'status' => 1,
                        'hide' => array('EQ', 1),
                    );
                    $find = $soft_model->where($where)->find();
                    if (!$find) {
                        $this->append_error_msg($error_msg, $key, 1, "包名【{$record['package']}】不存在于市场软件库中;");
                    }
                } else {
                    $this->append_error_msg($error_msg, $key, 1, "包名不能为空;");
                }
                if (!$record['ad_client']) {
                    $this->append_error_msg($error_msg, $key, 1, "广告主不能为空;");
                } 
                if (!$record['principal']) {
                    $this->append_error_msg($error_msg, $key, 1, "负责人不能为空;");
                } 
            }
            
            // 检查行与行之间的数据是否冲突（主要检查相同包名的区间是否有冲突）
            foreach($content_arr as $key1=>$record1) {
                // 如果1填写时间的不完善，则不比较
                foreach($content_arr as $key2=>$record2) {
                    $k1 = $key1 + 1;
                    $k2 = $key2 + 1;
                    // 比较过的不比较
                    if ($key1 >= $key2)
                        continue;
                    // 包名相同
                    if ($record1['package'] == $record2['package']) {                       
                        $this->append_error_msg($error_msg, $key1, 1, "包名第{$k2}行有重叠;");
                        $this->append_error_msg($error_msg, $key2, 1, "包名与第{$k1}行有重叠;");
                    }
                }
            }
            // 检查每一行数据是否与数据库的存储内容相冲突
            foreach($content_arr as $key=>$record) {
                //根据srh_key获取srh_key的id
                $sj_ad_library=$model->table('sj_ad_library')->where(array('package' => $record['package'],'status'=>1))->find();
                if($sj_ad_library['id']){
					if($sj_ad_library['app_from'] == 2){
						$str = "，软件来源为【运营】";
					}else{
						$str = "，软件来源为【商务】";
					}
                    $this->append_error_msg($error_msg, $key, 1, "同一包名下，与后台id为【{$sj_ad_library['id']}】".$str."，包名为【{$sj_ad_library['package']}】的记录有重叠;");
                }
                if($error_msg[$key]['flag']!=1){
                    if(!$this->process_import_array($record)){
                        $this->append_error_msg($error_msg, $key, 1, "添加失败");
                    }
                }
            }
            $lists=array();
            foreach($content_arr as $key=>$record){
                if($error_msg[$key]['flag']==1){
                    $lists[$key]=$record;
                }
            }
            //待处理
            if(count($lists) && ($filename=$this->export_ad($lists))){
                $this->append_error_msg($error_msg, count($error_msg), 1, $filename);
            }
            return $error_msg;
        }

         // 下载批量导入失败的csv文件
        function pub_down_ad_csv() {
            header( 'Content-Type:text/html;charset=utf-8');  
            $file_dir = "/tmp/shield_failure/".$_GET['file_name'];
            if (file_exists($file_dir)) {
                $file = fopen($file_dir,"r");
                Header("Content-type: application/octet-stream");
                Header("Accept-Ranges: bytes");
                Header("Accept-Length: ".filesize($file_dir));
                Header("Content-Disposition: attachment; filename=" . "导入失败_".date('Y-m-d').'.csv');
                echo fread($file, filesize($file_dir));
                fclose($file);
                exit(0);
            } else {
                echo "File not found!";
                exit;
            }

        }

         //生成导入失败文件
        function export_ad($lists){
            header( 'Content-Type:text/html;charset=utf-8 ');  
            $file_dir="/tmp/shield_failure/";
            if(!is_dir($file_dir)){
                if(!mkdir(iconv("UTF-8", "GBK", $file_dir),0777,true)){
                    jsmsg("创建目录失败", -1);
                }
            }
            $file_name="导入失败_".date('Y-m-d-h-i-s').'.csv';
            $file_re=$file_dir.$file_name;
            $util = D("Sj.Util");
            if(!file_exists($file_re)){
                $fp = fopen($file_re, 'w');
                $heade = array(iconv("UTF-8", "GBK", '包名'),iconv("UTF-8", "GBK", '广告主名称'),iconv("UTF-8", "GBK", '负责人'),iconv("UTF-8", "GBK", '合作形式'));
                fputcsv($fp, $heade);
                foreach($lists as $v){
                    $put_arr = array();
                    $put_arr['package'] = $v['package'] ? iconv("UTF-8", "GBK", $v['package']) : "\t";
                    $put_arr['ad_client'] = $v['ad_client'] ?  iconv("UTF-8", "GBK", $v['ad_client']) : "\t";
                    $put_arr['principal'] = $v['principal'] ?  iconv("UTF-8", "GBK", $v['principal']) : "\t";
                    // $put_arr['type'] = $v['type'] ?  iconv("UTF-8", "GBK", $v['type']) : "\t";
                    $typelist = $util->getHomeExtentSoftTypeList($v['type']);
                    foreach($typelist as $kk => $vv){
                        if($vv[1] == true){
                            $put_arr['type'] = $vv[0] ?  iconv("UTF-8", "GBK", $vv[0]) : "\t";
                        }
                    }
                    fputcsv($fp, $put_arr);
                }
            }
            return $file_name;
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


         //批量导入删除列表
        function edit_more_ad_operate(){
            $model = M('');
            if($_FILES){
                $this->import_more_ad(2);
                exit();
            }
			$this->assign('app_from',2);
            $this->display('edit_more_ad');
        }
         //批量导入删除列表
        function edit_more_ad(){
            $model = M('');
            if($_FILES){
                $this->import_more_ad();
                exit();
            }
            $this->display('edit_more_ad');
        }
        function import_more_ad($app_from=1){
            $model = M('');
            $tmp_name = $_FILES['ad_file']['tmp_name'];
            $content_arr = $this->import_file_to_array($tmp_name);
            $package = $repeat_pack = array();
            foreach($content_arr as $k=>$record){
                if($record[0]){
                    if(in_array($record[0], $package)){
                        $repeat_pack[]=$record[0];
                    }else{
                        $package[]=$record[0];
                    }
                }
            }
			$where = array(
				'package'=>array('in',$package),
				'status'=>1,
				'app_from'=>$app_from
			);
            $whitelist_soft = get_table_data($where,"sj_ad_library",'package','id,package,softname,ad_client,principal,type,app_type,create_tm', '');
            $has_soft = array();
            if($whitelist_soft){
                foreach($whitelist_soft as $k=>$v){
                    $has_soft[] = $v['package'];
                }
                $fail_soft = array_diff($package,$has_soft);
            }else{
                $fail_soft=$package;
            }


            $util = D("Sj.Util");
            
            foreach($whitelist_soft as $key=>$val){
                // $whitelist_soft[$key] = $v;
                $typelist = $util->getHomeExtentSoftTypeList($val['type']);
                foreach($typelist as $k => $v){
                    if($v[1] == true){
                        $whitelist_soft[$key]['ad_type'] = $v[0];
                    }
                }
            }
            // echo "<pre>";var_dump($whitelist_soft);die;
            $num = count($whitelist_soft);
            $this->assign('num',$num);
            $this->assign('fail_soft',$fail_soft);
            // $j_whitelist_soft = base64_encode(json_encode($whitelist_soft));
            // $this->assign('j_whitelist_soft',$j_whitelist_soft);
            $this->assign('whitelist_soft',$whitelist_soft);
            $this->assign('repeat_pack',$repeat_pack);
            $this->assign('app_from',$app_from);
            $this->display('import_more_ad');
        }
        /* 导出数据 */
        private function export_data() {
            header('Content-type: application/csv');
            //下载显示的名字
            $file_name = date("Y-m-d").'.csv';
            header('Content-Disposition: attachment; filename=广告库列表_"'.$file_name); 
            $out = fopen('php://output', 'w');
            fwrite($out,chr(0xEF).chr(0xBB).chr(0xBF)); 
            fputcsv($out,array('ID','包名','软件名称','广告主名称','负责人','合作形式','软件类型','添加来源','创建时间'));
            
            foreach(json_decode($_POST['data'],true) as $v) {
                fputcsv($out,$v);
            }


        }
    }
