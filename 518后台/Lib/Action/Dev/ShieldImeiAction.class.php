<?php
    class ShieldImeiAction extends CommonAction {
        
        public function imei_list() {
            // trim 一下
            foreach ($_GET as $key => $value) {
                $_GET[$key] = trim($value);
            }
            $where = array();
            $where['status'] = 1;
            // 如果有搜索条件
            if ($_GET['imei']) {
                $imei = $_GET['imei'];
                $where['imei'] = array('like',"%$imei%");
                // $where['imei'] = $imei;
            }
            if($_GET['type']){
                $where['type']=$_GET['type'];
            }else{
                $where['type']=1;
            }
            import("@.ORG.Page");
            $model = M();
            $count = $model->table('sj_shield_imei')->where($where)->count();
            $page = new Page($count, 20);
            $list = $model->table('sj_shield_imei')->where($where)->limit($page->firstRow.','.$page->listRows)->select();
            
            $show = $page->show();//分页显示输出
            $this->assign('list', $list);
            $this->assign('page', $show);
            $this->assign('imei', $imei);
            $this->assign('count', $count);
            $this->assign('type', $_GET['type']?$_GET['type']:1);
            // $this->assign('search_start_time', $search_start_time);
            // $this->assign('search_end_time', $search_end_time);
            $this->display('imei_list');
        }
        
        public function add_imei() {
            if ($_POST) {
                // 必填字段
                $ness = array(
                    'imei_two' => 'IMEI',
                    // 'start_time' => '开始时间',
                    // 'end_time' => '结束时间',
                );
                $data = array();
                foreach ($ness as $key => $value) {
                    if (!$_POST[$key])
                        $this->error("{$vlaue}不能为空");
                        $data['imei'] = $_POST[$key];
                }
                $data['type']=trim($_GET['type']);
                $model = M();
                // 检查相同包名有效期冲突
                $where = "imei='{$data['imei']}' and status=1 and type='{$data['type']}";
                $conflict = $model->table('sj_shield_imei')->where($where)->find();
                if ($conflict) {
                    $this->error('已存在此记录！');
                }
                $data['create_tm']=time();
               
                $ret = $model->table('sj_shield_imei')->add($data);
                if ($ret) {
                    if($_GET['type']==1){
                        $this->writelog("屏蔽预下载-百度流量：添加了id为{$ret}的记录",'sj_shield_imei',$ret,__ACTION__ ,"","add");
                    }else if($_GET['type']==2){
                        $this->writelog("屏蔽静默预下载：添加了id为{$ret}的记录",'sj_shield_imei',$ret,__ACTION__ ,"","add");
                    }
                    $this->success('添加成功！');
                } else {
                    $this->error('添加失败！');
                }
            } else {
                $this->assign('type', $_GET['type']);
                $this->display('add_imei');
            }
        }
        
        public function del_imei() {
            $model=M('shield_imei');
            if($_POST['imeis']){
                $ids =$_POST['imeis'];
                $ids_one=trim($ids,',');
                $ids=explode ( ",",$ids_one );
                $data['status'] = 0;
                $data['update_tm']=time();
                $where=array();
                $where['status']=1;
                $where['id']=array('in',$ids);
                $del = $model->where($where)->save($data);
                if($del){
                    if($_GET['type']==1){
                        $this->writelog("屏蔽预下载-百度流量：删除了id为{$ids_one}的记录",'sj_shield_imei',$ids_one,__ACTION__ ,"","del");
                    }else if($_GET['type']==2){
                        $this->writelog("屏蔽静默预下载：删除了id为{$ids_one}的记录",'sj_shield_imei',$ids_one,__ACTION__ ,"","del");
                    }
                     $this->success("删除成功");
                }
            }else if ($_GET['id']) {
                $id = $_GET['id'];
                $where = array('id' => $id);
                $data['status'] = 0;
                $data['update_tm']=time();
                $ret = $model->where($where)->save($data);
                if ($ret) {
                    if($_GET['type']==1){
					   $this->writelog("屏蔽预下载-百度流量：删除了id为{$id}的记录",'sj_shield_imei',$id,__ACTION__ ,"","del");
                    }else if($_GET['type']==2){
                       $this->writelog("屏蔽静默预下载：删除了id为{$id}的记录",'sj_shield_imei',$id,__ACTION__ ,"","del");
                    }
                    $this->success("删除成功！");
                } else {
                    $this->success("删除失败！");
                }
            }
        }
        
        // // 远程请求检查根据包名查找软件名
        // public function check_package() {
        //     $package = $_POST['package'];
        //     $find = $this->package_search($package);
        //     if ($find) {
        //         $this->ajaxReturn(1, $find, 1);
        //     } else {
        //         $this->ajaxReturn(0, '', 0);
        //     }
        // }
        
        // private function package_search($package) {
        //     if (!$package)
        //         return 0;
        //     $model = M();
        //     $where = array(
        //         'package' => $package,
        //         'status' => 1,
        //         'hide' => array('in', '1,1024'),
        //     );
        //     $find = $model->table('sj_soft')->where($where)->order('version_code')->order('version_code desc')->find();
        //     if ($find)
        //         return 1;
        //     return 0;            
        // }
        
    }
?>