<?php
    class SoftDownloadMapAction extends CommonAction {
        private $pre_url = "http://m.anzhi.com/download_ptn.php?id=";
        
        public function index() {
            // trim 一下
            foreach ($_GET as $key => $value) {
                $_GET[$key] = trim($value);
            }
            // 记录翻页参数
            $url_param = "";
            if ($_GET['p'] && $_GET['lr']) {
                $url_param = "p={$_GET['p']}&lr={$_GET['lr']}";
            }
            $where = array();
            $where['status'] = 1;
            // 如果有搜索条件
            if ($_GET['search_promotion_name']) {
                $search_promotion_name = $_GET['search_promotion_name'];
                $where['promotion_name'] = array('like', "%{$search_promotion_name}%");
                // 也添加在翻页参数里
                if ($url_param != "")
                    $url_param .= "&";
                $search_promotion_name2 = urlencode($_GET['search_promotion_name']);
                $url_param .= "search_promotion_name={$search_promotion_name2}";
            }
            if ($_GET['search_package']) {
                $search_package = $_GET['search_package'];
                $where['package'] = $search_package;
                // 也添加在翻页参数里
                if ($url_param != "")
                    $url_param .= "&";
                $search_package2 = urlencode($_GET['search_package']);
                $url_param .= "search_package={$search_package2}";
            }
            
            import("@.ORG.Page");
            $model = M();
            $count = $model->table('sj_soft_download_map')->where($where)->count();
            $page = new Page($count, 10);
            $list = $model->table('sj_soft_download_map')->where($where)->limit($page->firstRow.','.$page->listRows)->order('create_tm desc')->select();
            
            $show = $page->show();//分页显示输出
            $this->assign('list', $list);
            $this->assign('pre_url', $this->pre_url);
            $this->assign('page', $show);
            $this->assign('search_package', $search_package);
            $this->assign('search_promotion_name', $search_promotion_name);
            $this->assign('url_param', $url_param);
            $this->display('index');
        }
        
        public function add() {
            if ($_POST) {
                // trim一下$_POST数据
                foreach ($_POST as $key => $value) {
                    $_POST[$key] = trim($value);
                }
                // 必填字段
                $ness = array(
                    'promotion_name' => '推广名称',
                    'package' => '包名',
                );
                $data = array();
                foreach ($ness as $key => $value) {
                    if (!$_POST[$key])
                        $this->error("{$vlaue}不能为空");
                    $data[$key] = $_POST[$key];
                }
                $model = M();
                // 检查包名是否存在
                $find = $this->package_search($data['package']);
                if (!$find)
                    $this->error("包名不存在！");
                // 检查该包名是否已添加过
                $exists = $this->db_package_exists($data['package']);
                if ($exists)
                    $this->error("该包已被添加过！");
                $now = time();
                // 生成hash值
                $short_hash = $this->get_available_short_hash($data['package']);
                if (!$short_hash)
                    $this->error("无法生成有效短hash，添加失败，请重新尝试！");
                $data['hash'] = $short_hash;
                // 赋默认值
                $data['create_tm'] = $now;
                $data['update_at'] = $now;
                $data['status'] = 1;
                $ret = $model->table('sj_soft_download_map')->add($data);
                if ($ret) {
                    $this->writelog("广告排期管理_软件下载链接映射列表：添加了id为{$ret}的记录", 'sj_soft_download_map',$ret,__ACTION__ ,'','add');
                    $this->assign('jumpUrl', "index?{$_POST['url_param']}");
                    $this->success('添加成功！');
                } else {
                    $this->error('添加失败！');
                }
            } else {
                // trim 一下
                foreach ($_GET as $key => $value) {
                    $_GET[$key] = trim($value);
                }
                // 记录翻页参数
                $url_param = "";
                if ($_GET['p'] && $_GET['lr']) {
                    $url_param = "p={$_GET['p']}&lr={$_GET['lr']}";
                }
                if ($_GET['search_promotion_name']) {
                    // 也添加在翻页参数里
                    if ($url_param != "")
                        $url_param .= "&";
                    $search_promotion_name = urlencode($_GET['search_promotion_name']);
                    $url_param .= "search_promotion_name={$search_promotion_name}";
                }
                if ($_GET['search_package']) {
                    // 也添加在翻页参数里
                    if ($url_param != "")
                        $url_param .= "&";
                    $search_package = urlencode($_GET['search_package']);
                    $url_param .= "search_package={$search_package}";
                }
                $this->assign('url_param', $url_param);
                $this->display('add');
            }
        }
        
        public function edit() {
            if ($_POST) {
                // trim一下$_POST数据
                foreach ($_POST as $key => $value) {
                    $_POST[$key] = trim($value);
                }
                // 必填字段
                $ness = array(
                    'promotion_name' => '推广名称',
                    'package' => '包名',
                );
                $data = array();
                foreach ($ness as $key => $value) {
                    if (!$_POST[$key])
                        $this->error("{$vlaue}不能为空");
                    $data[$key] = $_POST[$key];
                }
                $model = M();
                // 检查包名是否存在
                $find = $this->package_search($data['package']);
                if (!$find) {
                    $this->error("包名不存在！");
                }
                // 检查该包名是否已添加过
                $exists = $this->db_package_exists($data['package'], $_POST['id']);
                if ($exists)
                    $this->error("该包已被添加过！");
                if ($_POST['package'] != $_POST['bf_package']) {
                    // 如果包名不一样了，重新生成hash值
                    $short_hash = $this->get_available_short_hash($data['package']);
                    if (!$short_hash)
                        $this->error("无法生成有效短hash，添加失败，请重新尝试！");
                    $data['hash'] = $short_hash;
                }
                // 赋默认值
                $now = time();
                $data['update_at'] = $now;
                $where = array(
                    'id' => $_POST['id'],
                );
                // 添加前记录日志
                $log = $this->logcheck($where, 'sj_soft_download_map', $data, $model);
                $ret = $model->table('sj_soft_download_map')->where($where)->save($data);
                if ($ret || $ret === 0) {
                    if ($ret)
                        $this->writelog("广告排期管理_软件下载链接映射列表：编辑了id为{$_POST['id']}的记录，{$log}",'sj_soft_download_map',$_POST['id'],__ACTION__ ,'','edit');
                    $this->assign('jumpUrl', "index?{$_POST['url_param']}");
                    $this->success('编辑成功！');
                } else {
                    $this->error('编辑失败！');
                }
            }
            if ($_GET['id']) {
                // trim 一下
                foreach ($_GET as $key => $value) {
                    $_GET[$key] = trim($value);
                }
                $id = $_GET['id'];
                $model = M();
                $where = array(
                    'id' => $id,
                );
                $find = $model->table('sj_soft_download_map')->where($where)->find();
                $this->assign('record', $find);
                // 记录翻页参数
                $url_param = "";
                if ($_GET['p'] && $_GET['lr']) {
                    $url_param = "p={$_GET['p']}&lr={$_GET['lr']}";
                }
                if ($_GET['search_promotion_name']) {
                    // 也添加在翻页参数里
                    if ($url_param != "")
                        $url_param .= "&";
                    $search_promotion_name = urlencode($_GET['search_promotion_name']);
                    $url_param .= "search_promotion_name={$search_promotion_name}";
                }
                if ($_GET['search_package']) {
                    // 也添加在翻页参数里
                    if ($url_param != "")
                        $url_param .= "&";
                    $search_package = urlencode($_GET['search_package']);
                    $url_param .= "search_package={$search_package}";
                }
                $this->assign('url_param', $url_param);
                $this->display('edit');
            }
        }
        
        // 删除
        public function del() {
            if ($_GET['id']) {
                $id = $_GET['id'];
                $model = M();
                $where = array('id' => $id);
                $data = array('status' => 0);
                $ret = $model->table('sj_soft_download_map')->where($where)->save($data);
                if ($ret) {
                    $this->writelog("广告排期管理_软件下载链接映射列表：删除了id为{$_GET['id']}的记录",'sj_soft_download_map',$_GET['id'],__ACTION__ ,'','del');
                    $this->success("删除成功！");
                } else {
                    $this->success("删除失败！");
                }
            }
        }
        
        // 远程请求检查根据包名是否存在，调用本地检查函数package_search($package)
        public function check_package() {
            $package = $_POST['package'];
            $find = $this->package_search($package);
            if ($find) {
                $this->ajaxReturn(1, $find, 1);
            } else {
                $this->ajaxReturn(0, '', 0);
            }
        }
        
        // 本地检查包名是否存在
        private function package_search($package) {
            if (!$package)
                return 0;
            $model = M();
            $where = array(
                'package' => $package,
                'status' => 1,
                'hide' => 1,
            );
            $find = $model->table('sj_soft')->where($where)->order('version_code')->order('version_code desc')->find();
            if ($find)
                return 1;
            return 0;            
        }
        
        // 检查包名是否已添加过
        private function db_package_exists($package, $except_id=0) {
            $model = M();
            $where = array(
                'package' => $package,
                'status' => 1,
            );
            if ($except_id)
                $where['id'] = array('neq', $except_id);
            $find = $model->table('sj_soft_download_map')->where($where)->find();
            if ($find)
                return 1;
            return 0;
        }
        
        // 根据包名得到有效的短hash值
        private function get_available_short_hash($package) {
            $flag = false;
            $availabe_short_hash = "";
            for ($j = 0; $j < 10; $j++) {
                // 包名+时间戳+随机数
                $str = $data['package'] . '_' . time() . '_' . rand(1000, 9999);
                // 生成短hash
                $hash_arr = $this->short_hash($str);
                // 检查生成的短hash是否可用
                for ($i = 0; $i < count($hash_arr); $i++) {
                    $exists = $this->db_short_hash_exists($hash_arr[$i]);
                    if (!$exists) {
                        $availabe_short_hash = $hash_arr[$i];
                        $flag = true;
                        break;
                    }
                }
                if ($flag && $availabe_short_hash)
                    return $availabe_short_hash;
            }
            return false;
        }
        
        // 检查短hash在数据库中是否存在
        private function db_short_hash_exists($short_hash) {
            $model = M();
            $where = array(
                'hash' => $short_hash,
                'status' => 1,
            );
            $find = $model->table('sj_soft_download_map')->where($where)->find();
            if ($find)
                return 1;
            return 0;
        }
        
        // 利用短链原理来生成短md5值
        private function short_hash($str) {
            $charset = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
            $key = "alexis";
            $urlhash = md5($key . $str);
            $len = strlen($urlhash);

            #将加密后的串分成4段，每段4字节，对每段进行计算，一共可以生成四组短连接
            for ($i = 0; $i < 4; $i++) {
                $urlhash_piece = substr($urlhash, $i * $len / 4, $len / 4);
                #将分段的位与0x3fffffff做位与，0x3fffffff表示二进制数的30个1，即30位以后的加密串都归零
                $hex = hexdec($urlhash_piece) & 0x3fffffff; #此处需要用到hexdec()将16进制字符串转为10进制数值型，否则运算会不正常

                $short_url = "";
                #生成6位短连接
                for ($j = 0; $j < 6; $j++) {
                    #将得到的值与0x0000003d,3d为61，即charset的坐标最大值
                    $short_url .= $charset[$hex & 0x0000003d];
                    #循环完以后将hex右移5位
                    $hex = $hex >> 5;
                }
                $short_url_list[] = $short_url;
            }
            return $short_url_list;
        }

 
    }
?>