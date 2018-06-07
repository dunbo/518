<?php
    class SearchAdapterAction extends CommonAction {
        
        public function index() {
            // trim 一下
            foreach ($_GET as $key => $value) {
                $_GET[$key] = trim($value);
            }
            $where = array();
            $where['status'] = 1;
            // 如果有搜索条件
            if ($_GET['search_package']) {
                $search_package = $_GET['search_package'];
                $where['package'] = $search_package;
            }
            if ($_GET['name']) {
                $name = $_GET['name'];
                $where['name'] = array('like',"%$name%");
            }
            if ($_GET['search_start_time']) {
                $search_start_time = strtotime($_GET['search_start_time']);
                $where['start_time'] = array('egt', $search_start_time);
            }
            if ($_GET['search_end_time']) {
                $search_end_time = strtotime($_GET['search_end_time']);
                $where['end_time'] = array('elt', $search_end_time+86399);
            }
            
            import("@.ORG.Page");
            $model = M();
            $count = $model->table('sj_search_adapter')->where($where)->count();
            $page = new Page($count, 20);
            $list = $model->table('sj_search_adapter')->where($where)->limit($page->firstRow.','.$page->listRows)->select();
            
            $pkgs=array();
            foreach($list as $k=>$v){
                $pkgs[]=$v['package'];
            }
            $softnames = $model->table('sj_soft')->where(array('package'=>array('in',$pkgs),'status'=>1,'hide' => array('in', '1,1024')))->field('package,softname')->select();
            $softname_data=array();
            foreach($softnames as $k=>$v){
                $softname_data[$v['package']]=$v['softname'];
            }
            $this->assign('softname_data', $softname_data);
            $show = $page->show();//分页显示输出
            $this->assign('list', $list);
            $this->assign('page', $show);
            $this->assign('search_package', $search_package);
            $this->assign('name', $name);
            $this->assign('search_start_time', $search_start_time);
            $this->assign('search_end_time', $search_end_time);
            $this->display('index');
        }
        
        public function add_package() {
            if ($_POST) {
                // 必填字段
                $ness = array(
                    'package' => '包名',
                    'start_time' => '开始时间',
                    'end_time' => '结束时间',
                );
                $data = array();
                foreach ($ness as $key => $value) {
                    if (!$_POST[$key])
                        $this->error("{$vlaue}不能为空");
                    if ($key == 'start_time')
                        $data[$key] = strtotime($_POST[$key]);
                    else if ($key  == 'end_time')
                        $data[$key] = strtotime($_POST[$key]) + 86399;
                    else
                        $data[$key] = $_POST[$key];
                }
                $model = M();
                // 检查包名是否存在
                $find = $this->package_search($data['package']);
                if (!$find)
                    $this->error("包名不存在！");
                // 检查开始时间是否小于结束时间
                if ($data['start_time'] >= $data['end_time'])
                    $this->error("开始时间需小于结束时间");
                // 检查相同包名有效期冲突
                $where = "package='{$data['package']}' and ((start_time>={$data['start_time']} and start_time<={$data['end_time']}) or (end_time>={$data['start_time']} and end_time<={$data['end_time']})) and status=1";
                $conflict = $model->table('sj_search_adapter')->where($where)->find();
                if ($conflict) {
                    $this->error('时间与已存在记录存在冲突！');
                }
                //屏蔽软件上排期时报警需求 新增  yuesai
                $AdSearch = D("Sj.AdSearch");
                $shield_error=$AdSearch->check_ad($data['package'],$data['start_time'],$data['end_time'],1);
                if($shield_error){
                    $this -> error($shield_error);
                }
                $data['name']=$_POST['name'];
                $ret = $model->table('sj_search_adapter')->add($data);
                if ($ret) {
                    $this->assign('jumpUrl', 'index');
					$this->writelog("搜索适配：添加了id为{$ret}的记录",'sj_search_adapter',$ret,__ACTION__ ,"","add");
                    $this->success('添加成功！');
                } else {
                    $this->error('添加失败！');
                }
            } else {
                $this->display('add_package');
            }
        }
        
        public function edit_package() {
            if ($_POST) {
                // 必填字段
                $ness = array(
                    'package' => '包名',
                    'start_time' => '开始时间',
                    'end_time' => '结束时间',
                );
                $data = array();
                foreach ($ness as $key => $value) {
                    if (!$_POST[$key])
                        $this->error("{$vlaue}不能为空");
                    if ($key == 'start_time')
                        $data[$key] = strtotime($_POST[$key]);
                    else if ($key  == 'end_time')
                        $data[$key] = strtotime($_POST[$key]) + 86399;
                    else
                        $data[$key] = $_POST[$key];
                }
                $model = M();
                // 检查包名是否存在
                $find = $this->package_search($data['package']);
                if (!$find)
                    $this->error("包名不存在！");
                // 检查开始时间是否小于结束时间
                if ($data['start_time'] >= $data['end_time'])
                    $this->error("开始时间需小于结束时间");
                // 检查相同包名有效期冲突
                $where = "id!={$_POST['id']} and package='{$data['package']}' and ((start_time>={$data['start_time']} and start_time<={$data['end_time']}) or (end_time>={$data['start_time']} and end_time<={$data['end_time']})) and status=1";
                $conflict = $model->table('sj_search_adapter')->where($where)->find();
                if ($conflict) {
                    $this->error('时间与已存在记录存在冲突！');
                }
                //屏蔽软件上排期时报警需求 新增  yuesai
                $AdSearch = D("Sj.AdSearch");
                $shield_error=$AdSearch->check_ad($data['package'],$data['start_time'],$data['end_time'],1);
                if($shield_error){
                    $this -> error($shield_error);
                }
                $where = array(
                    'id' => $_POST['id'],
                );
                $data['name']=$_POST['name'];
				$log = $this->logcheck($where, 'sj_search_adapter', $data, $model);
                $ret = $model->table('sj_search_adapter')->where($where)->save($data);
                if ($ret || $ret === 0) {
					$this->writelog("搜索适配：编辑了id为{$_POST['id']}的记录，{$log}",'sj_search_adapter',$_POST['id'],__ACTION__ ,"","edit");
                    $this->assign('jumpUrl', 'index');
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
                $find = $model->table('sj_search_adapter')->where($where)->find();
                $this->assign('record', $find);
                $this->display('edit_package');
            }
        }
        
        public function del() {
            if ($_GET['id']) {
                $id = $_GET['id'];
                $model = M();
                $where = array('id' => $id);
                $data = array('status' => 0);
                $ret = $model->table('sj_search_adapter')->where($where)->save($data);
                if ($ret) {
					$this->writelog("搜索适配：删除了id为{$id}的记录",'sj_search_adapter',$id,__ACTION__ ,"","del");
                    $this->success("删除成功！");
                } else {
                    $this->success("删除失败！");
                }
            }
        }
        
        // 远程请求检查根据包名查找软件名
        public function check_package() {
            $package = $_POST['package'];
            $find = $this->package_search($package);
            if ($find) {
                $this->ajaxReturn($find, 1, 1);
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
            );
            $find = $model->table('sj_soft')->where($where)->order('version_code')->order('version_code desc')->find();
            if ($find)
                return $find;
            return 0;            
        }
        
    }
?>