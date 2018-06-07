<?php
    class GameInfoCollectionAction extends CommonAction {
        const HOST_TAG = "<!--{ANZHI_IMAGE_HOST}-->";
        
        public function info_list() {
            $model = D('Sj.GameInfoCollection');
            $where = array(
                'status' => 1,
                'passed' => 0,
            );
            // trim一下
            foreach ($_GET as $key => $value) {
                $_GET[$key] = trim($value);
            }
            $url_param = "";
            foreach ($_GET as $key => $value) {
                if ($url_param != '')
                    $url_param .= "&";
                if ($value != '')
                    $url_param .= "{$key}={$value}";
            }
            // 搜索软件名称
            if ($_GET['search_softname'] != '') {
                $where['softname'] = array('like', '%' . $_GET['search_softname'] . '%');
            }
            // 搜索资讯标题
            if ($_GET['search_news_name'] != '') {
                $where['news_name'] = array('like', '%' . $_GET['search_news_name'] . '%');
            }
            // 搜索资讯类型
            if ($_GET['search_info_type'] != '' && $_GET['search_info_type'] != -1) {
                $where['info_type'] = $_GET['search_info_type'];
            }
			// 搜索采集站点
			if ($_GET['search_website'] != '') 
			{
				$where['website_name'] = array('like', '%' . $_GET['search_website'] . '%');
			}
			 // 搜索是否包含敏感侧
            if ($_GET['include_sensitive'] != '' && $_GET['include_sensitive'] != -1) 
			{
				$where['sensitive_status'] = $_GET['include_sensitive'];
            }
			else
			{
				$_GET['include_sensitive']=-1;
			}
			 // 搜索是否匹配包名
            if ($_GET['match_pck'] != '' && $_GET['match_pck'] != -1) {
				if($_GET['match_pck'] == 1)
				{
					$where['package'] = array('neq','');
				}
				if($_GET['match_pck'] == 0)
				{
					$where['package'] = '';
				}
            }
			else
			{
				$_GET['match_pck']=-1;
			}
			
			// 搜索软件包名
            if ($_GET['search_pckname'] != '') {
                $where['package'] = array('eq',$_GET['search_pckname']);
            }
			
            $count = $model->table('caiji_game_info')->where($where)->count();
            import("@.ORG.Page");
            $page = new Page($count, 10);
            $show = $page->show();//分页显示输出
            
            $list = $model->table('caiji_game_info')->field('id, info_type, news_name, news_pic, softname, website_name, author, create_tm, news_date,sensitive_status,package')->where($where)->order('news_date desc')->limit($page->firstRow.','.$page->listRows)->select();
			//var_dump($model->getLastSql());exit;
            
            // 搜索内容
            $this->assign('search_softname', $_GET['search_softname']);
            $this->assign('search_news_name', $_GET['search_news_name']);
            $this->assign('search_info_type', $_GET['search_info_type']);
			$this->assign('include_sensitive', $_GET['include_sensitive']);
            $this->assign('match_pck', $_GET['match_pck']);
            $this->assign('search_pckname', $_GET['search_pckname']);
			$this->assign('search_website', $_GET['search_website']);
            
            $this->assign('list', $list);
            $this->assign('page', $show);
            $this->assign('apkurl', CAIJI_ATTACHMENT_HOST);
            $this->assign('url_param', $url_param);
            $this->display('info_list');
            
        }
        
        // 预览
        public function info_preview() {
            if ($_GET['id']) {
                $id = $_GET['id'];
                $model = D('Sj.GameInfoCollection');
                $where = array(
                    'id' => $id,
                    'status' => 1,
                );
                $find = $model->table('caiji_game_info')->where($where)->find();
                if ($find) {
                    $module_content = $find['module_content'];
                    $module_content = htmlspecialchars_decode($module_content);
                    // 展示需要将图片host替换上去
                    $module_content = str_replace(self::HOST_TAG, CAIJI_ATTACHMENT_HOST, $module_content);
                    $this->assign('content', $module_content);
                    $this->display('info_preview');
                }
            }
        }
        
        // 根据包名查找软件名
        public function search_softname($package) {
            $package = $_POST['package'];
            $soft_model = M();
            $where = array(
                'package' => $package,
                'status' => 1,
                'hide' => array('in', '0,1,1024'),
            );
            $find = $soft_model->table('sj_soft')->where($where)->order('version_code')->order('version_code desc')->find();
            if ($find) {
                $this->ajaxReturn(1, $find, 1);
            } else
                $this->ajaxReturn(0, '', 0);
        }
        
        // 通过，通过后的资讯将会同步到资讯表里，状态为未发布
        public function pass() {
			$model = D('Sj.GameInfoCollection');
            $soft_model = M();
            if ($_POST) {
                $id = $_POST['id'];
                $where = array(
                    'id' => $id,
                    'status' => 1,
                    'passed' => 0,
                );
                // 根据包名查找版本最高的软件名
                $find = $soft_model->table('sj_soft')->where(array('package'=>$_POST['package'], 'status' => 1))->order('version_code desc')->find();
                if ($find) {
                    $softname = $find['softname'];
                } else {
                    $this->error("通过失败！");
                }
                $now = time();
                $data = array(
                    'package' => $_POST['package'],
                    'passed' => 1,
                    'softname' => $softname,
                    'update_tm' => $now,
                );
                $log = $this->logcheck($where, 'caiji_game_info', $data, $model);
                $ret = $model->table('caiji_game_info')->where($where)->save($data);
                if ($ret) {
                    // 写日志
                    $this->writelog("网游_资讯采集列表：通过了id为{$id}的资讯：{$log}",'caiji_game_info',"{$id}",__ACTION__ ,"","edit");
                    // 将数据同步到表sj_olgame_news
                    $where = array(
                        'id' => $id,
                        'status' => 1,
                        'passed' => 1,
                    );
                    $find = $model->table('caiji_game_info')->where($where)->find();
                    // 将图片从采集服务器拉到CDN上
                    // 1，拉news_pic图片
                    $caiji_news_pic = CAIJI_ATTACHMENT_HOST . $find['news_pic'];
                    $news_pic = UPLOAD_PATH . '/img/' . date("Ym/d/") . basename($find['news_pic']);
                    $news_pic_short = str_replace(UPLOAD_PATH, '', $news_pic);
                    $this->drag_file($news_pic, $caiji_news_pic);
                    // 2，拉module_content里的图片/flash
                    $module_content = htmlspecialchars_decode($find['module_content']);
                    $pic_reg = "/\"(" . str_replace('/', '\/', self::HOST_TAG) . "[^\"]+)\"/";
                    preg_match_all($pic_reg, $module_content, $matches);
                    if ($matches && $matches[1]) {
                        foreach ($matches[1] as $file) {
                            // 通过后缀名决定放在哪个目录
                            if (preg_match('/\.(jpg|png|jpeg)$/', $file)) {
                                $folder = '/img/';
                            } else {
                                $folder = '/flash/';
                            }
                            $news_pic = UPLOAD_PATH . $folder . date("Ym/d/") . basename($file);
                            $file_url = str_replace(self::HOST_TAG, CAIJI_ATTACHMENT_HOST, $file);
                            $this->drag_file($news_pic, $file_url);
                            $news_pic_url = self::HOST_TAG . str_replace(UPLOAD_PATH, '', $news_pic);
                            $module_content = str_replace($file, $news_pic_url, $module_content);
                        }
                    }
                    // module_content再htmlspecialchars回来
                    $module_content = htmlspecialchars($module_content);
                    $now = time();
                    $data = array(
                        'news_name' => $find['news_name'],
                        'news_pic' => $news_pic_short,
                        'author' => $find['author'],
                        'apply_pkg' => $find['package'],
                        'news_content' => $find['news_content'],
                        'module_pic' => $find['module_pic'],
                        'module_content' => $module_content,
                        'catch_tm' => $find['create_tm'],
                        'create_tm' => $now,
                        'update_tm' => $now,
                        'status' => 1,
                        'softname' => $find['softname'],
                        'website_name' => $find['website_name'],
                        'info_type' => $find['info_type'],
                    );
					// 新闻资讯表在主库上，所以用回soft_model
                    $ret = $soft_model->table('sj_olgame_news')->add($data);
                    if ($ret) {
                        // 写日志
                        $this->writelog("网游_新闻资讯：添加了id为{$ret}的新闻资讯",'sj_olgame_news',"{$ret}",__ACTION__ ,"","add");
                        $this->success("通过成功！已同步到新闻资讯未发布列表！");
                    }
                    else
                        $this->error("通过成功，但同步失败！");
                } else {
                    $this->error("通过失败！");
                }
            } else if ($_GET['id']) {
                $id = $_GET['id'];
                $find = $model->table('caiji_game_info')->where(array('id' => $id))->find();
                $this->assign('info', $find);
                $this->display('pass');
            }
        }
        
        public function edit_info() {
            if ($_POST) {
                // trim一下数据
                foreach ($_POST as $key => $value) {
                    $_POST[$key] = trim($value);
                }
                $data = array();
                $data['news_name'] = $_POST['news_name'];
                $data['package'] = $_POST['package'];
                $data['softname'] = $_POST['softname'];
                $data['info_type'] = $_POST['info_type'];
                $data['author'] = $_POST['author'];
                $data['news_content'] = $_POST['news_content'];
                $data['update_tm'] = time();
                // 资讯简介不能超过40个字
                if (mb_strlen($data['news_content'], 'utf-8') > 40) {
                    $this->error("资讯简介不能超过40个字");
                }
                // 保存图片如果有上传
                if ($_FILES['news_pic']['name']) {
                    $date = date("/Ym/d/");
                    preg_match("/\.(?:png|jpg|jpeg)$/i", $_FILES['news_pic']['name'], $matches);
                    if (!$matches) {
                        $this->error("上传图片类型错误！");
                    }
                    $suffix = $matches[0];
                    $name = time() . rand(1000, 9999) . rand(1000, 9999) . $suffix;
                    $rel_path = '/img/' . $date . $name;
                    $path = CAIJI_UPLOAD_PATH . $rel_path;
                    $folder = CAIJI_UPLOAD_PATH . '/img/' . $date;
                    if (!file_exists($folder)) {
                        $this->mkDirs($folder);
                    }
                    $ret = move_uploaded_file($_FILES['news_pic']['tmp_name'], $path);
                    if (!$ret) {
                        $this->error("上传图片失败！");
                    }
                    $data['news_pic'] = $rel_path;
                }
                $data['module_content'] = htmlspecialchars($this->process_KindEditorContent($_POST['module_content']));
                $where = array('id' => $_POST['id']);
                $model = D('Sj.GameInfoCollection');
                // 添加前准备日志数据
                $data_tmp = array();
                foreach ($data as $key => $value) {
                    $data_tmp[$key] = $value;
                }
                unset($data_tmp['module_content']);
                $log = $this->logcheck($where, 'caiji_game_info', $data_tmp, $model);
                $find = $model->table('caiji_game_info')->where($where)->find();
                if ($find['module_content'] != $data['module_content'])
                    $log .= "，module_content字段也被编辑";
                $ret = $model->table('caiji_game_info')->where($where)->save($data);
                if ($ret || $ret === 0) {
                    if ($ret)
                        $this->writelog("网游_资讯采集列表：编辑了id为{$_POST['id']}的资讯：{$log}",'caiji_game_info',"{$_POST['id']}",__ACTION__ ,"","edit");
                    $this->assign('jumpUrl', 'info_list?' . $_POST['url_param']);
                    $this->success("编辑成功！");
                } else {
                    $this->success("编辑失败！");
                }
            } else if ($_GET['id']) {
                $url_param = "";
                foreach ($_GET as $key => $value) {
                    if ($key == 'id')
                        continue;
                    if ($url_param != '')
                        $url_param .= "&";
                    if ($value != '')
                        $url_param .= "{$key}={$value}";
                }
                $id = $_GET['id'];
                $model = D('Sj.GameInfoCollection');
                $where = array(
                    'id' => $id
                );
                $find = $model->table('caiji_game_info')->where($where)->find();
                $module_content = $find['module_content'];
                // 展示需要将图片host换上去
                $module_content = htmlspecialchars_decode($module_content);
                $module_content = str_replace(self::HOST_TAG, CAIJI_ATTACHMENT_HOST, $module_content);
                $find['module_content'] = $module_content;
                if ($find) {
                    $this->assign('info', $find);
                    $this->assign('attachment_host', CAIJI_ATTACHMENT_HOST);
                    $this->assign('url_param', $url_param);
                    $this->display('edit_info');
                } else {
                    $this->error("没有找到对应记录！");
                }
            }
        }
        
        function del() {
            if ($_GET['id']) {
				$first=substr($_GET['id'],0,1);
				if($first==",")
				{
					$nid = substr($_GET['id'],1);
				}
				else
				{
					$nid = $_GET['id'];
				}
				$id_arr = explode(',',$nid);
				foreach($id_arr as $id)
				{
					$model = D('Sj.GameInfoCollection');
					$now = time();
					$ret = $model->table('caiji_game_info')->where(array('id' => $id))->save(array('status' => 0, 'update_tm'=>$now));
					if ($ret) {
						$this->writelog("网游_资讯采集列表：删除了id为{$_GET['id']}的资讯",'caiji_game_info',"{$_GET['id']}",__ACTION__ ,"","del");
						//$this->success('删除成功！');
					} //else {
					  //  $this->error('删除失败！');
					//}
				}
				if($ret)
				{
					$this->success('删除成功！');
				}
				else
				{
					$this->error('删除失败！');
				}
            }
        }
        
        private function process_KindEditorContent($KindEditorContent) {
            $KindEditorContent = stripcslashes($KindEditorContent);
            // 1，将与自己域名相关的图片域名换回约定的标签字符串
            $KindEditorContent = str_replace(CAIJI_ATTACHMENT_HOST, self::HOST_TAG, $KindEditorContent);
            // 2，抓取KindEditor内容里的图片/flash
            $upload_config = array(
                'image' => array(
                    'element_tag' => 'img',
                    'suffix_reg' => 'png|jpg|jpeg',
                    'suffix_error_hint' => '上传图片类型错误！',
                    'folder' => '/img/',
                    'upload_error_hint' => '上传图片失败！',
                ),
                'flash' => array(
                    'element_tag' => 'embed',
                    'suffix_reg' => 'swf|flv',
                    'suffix_error_hint' => '上传flash类型错误！',
                    'folder' => '/flash/',
                    'upload_error_hint' => '上传flash失败！',
                ),
            );
            foreach ($upload_config as $config) {
                $count = preg_match_all("/<{$config['element_tag']}.+?src=\"(\/Public\/js\/kindeditor.*?)\".+?\/>/u", $KindEditorContent, $matches);
                if (!$count) {
                    continue;
                }
                // 得到$files数组：key为将来保存KindEditor匹配到的数据，value为当前图片所在绝对路径
                $files_name = $files = $new_arr = array();
                $pre_path = $_SERVER['DOCUMENT_ROOT'];
                foreach($matches[1] as $key => $val) {
                    $upload_model = D("Dev.Uploadfile");
                    $files_name[$key] = str_replace('.','',microtime(true)).'_'.$upload_model -> rand_code(8);
                }
                foreach($matches[1] as $key => $val) {
                    $files[$val] = $pre_path.$val;
                }
                // 将图片从kindeditor的路径移到指定目录下
                $arr = array();
                foreach ($files as $kindeditor_path => $kindeditor_real_path) {
                    $date = date("/Ym/d/");
                    preg_match("/\.(?:{$config['suffix_reg']})$/i", $kindeditor_real_path, $suffix_matches);
                    if (!$suffix_matches) {
                        $this->error($config['suffix_error_hint']);
                    }
                    $suffix = $suffix_matches[0];
                    $name = time() . rand(1000, 9999) . rand(1000, 9999) . $suffix;
                    $rel_path = $config['folder'] . $date . $name;
                    $path = CAIJI_UPLOAD_PATH . $rel_path;
                    $folder = CAIJI_UPLOAD_PATH . $config['folder'] . $date;
                    if (!file_exists($folder)) {
                        $this->mkDirs($folder);
                    }
                    $ret = copy($kindeditor_real_path, $path);
                    if (!$ret) {
                        $this->error($config['upload_error_hint']);
                    }
                    $arr[$kindeditor_path] = $rel_path;
                }
                //文章内容中图片路径替换
                $new_arr = array();
                foreach ($arr as $kindeditor_path => $rel_path) {
                    $new_arr[$kindeditor_path] = self::HOST_TAG.$rel_path;
                }
                $KindEditorContent = strtr($KindEditorContent, $new_arr);
            }
            return $KindEditorContent;
        }
              
        // 将文件$src_file拉到$des_file
        // 如果目标路径不存在，会尝试主动创建目录
        private function drag_file($des_file, $src_file) {
            if (!$des_file || !$src_file) {
                return false;
            }
            $dirname = dirname($des_file);
            if (!file_exists($dirname)) {
                $this->mkDirs($dirname);
            }
            $src_data = file_get_contents($src_file);
            return file_put_contents($des_file, $src_data);
        }
        
    }
?>