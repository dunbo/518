<?php

class SearchNameCompletelyMatchAction extends CommonAction {
    
    public function index() {
        $model = M();
        $where = array(
            'status' => 1,
        );
        // 搜索
        $search_package = $_GET['search_package'];
        if ($search_package) {
            $where['package'] = $search_package;
            $this->assign('search_package', $search_package);
        }
        //翻页
        $count = $model->table('sj_search_name_completely_match')->where($where)->count();
        import("@.ORG.Page");
        $page = new Page($count, 10);
        $show = $page->show();//分页显示输出
        
        $list = $model->table('sj_search_name_completely_match')->where($where)->limit($page->firstRow.','.$page->listRows)->select();
        
        // search softname
        foreach ($list as $key => $record) {
            $package = $record['package'];
            $where = array(
                'package' => $package,
                'status' => 1,
                'hide' => array('in', array(1,1024)),
            );
            $find = $model->table('sj_soft')->where($where)->order('version desc')->find();
            $list[$key]['softname'] = $find['softname'];
        }
        
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display('index');
    }
    
    public function add_package() {
        if ($_POST) {
            $package = $_POST['package'];
            $start_time = $_POST['start_time'];
            $end_time = $_POST['end_time'];
            
            if (!$package) {
                $this->error("包名不能为空");
            }
            if (!$start_time) {
                $this->error("开始时间不能为空");
            }
            if (!$end_time) {
                $this->error("结束时间不能为空");
            }
            
            $start_time = strtotime($start_time);
            $end_time = strtotime($end_time) + 86399;
            
            if ($start_time >= $end_time) {
                $this->error("开始时间不能大于结束时间");
            }
            
            $model = M();
            $where = array(
                'package' => $package,
                'status' => 1,
                'hide' => array('in', array(1,1024)),
            );
            $find = $model->table('sj_soft')->where($where)->find();
            if (!$find) {
                $this->error("包名不存在");
            }
            
            $map = array();
            $map['package'] = $package;
            $map['start_time'] = $start_time;
            $map['end_time'] = $end_time;
            $map['status'] = 1;
            
            // 检查时间冲突
            $conflict_id = $this->check_conflict($map);
            if ($conflict_id) {
                $this->error("排期时间与id为{$conflict_id}的记录有冲突！");
            }
            
            $id = $model->table('sj_search_name_completely_match')->add($map);
            if ($id) {
                $this->success("添加成功！");
            } else {
                $this->error("添加失败！");
            }
            
        } else {
            $this->display();
        }
    }
    
    public function edit_package() {
        if ($_POST) {
            $id = $_POST['id'];
            $package = $_POST['package'];
            $start_time = $_POST['start_time'];
            $end_time = $_POST['end_time'];
            
            if (!$package) {
                $this->error("包名不能为空");
            }
            if (!$start_time) {
                $this->error("开始时间不能为空");
            }
            if (!$end_time) {
                $this->error("结束时间不能为空");
            }
            
            $start_time = strtotime($start_time);
            $end_time = strtotime($end_time) + 86399;
            
            if ($start_time >= $end_time) {
                $this->error("开始时间不能大于结束时间");
            }
            
            $model = M();
            $where = array(
                'package' => $package,
                'status' => 1,
                'hide' => array('in', array(1,1024)),
            );
            $find = $model->table('sj_soft')->where($where)->find();
            if (!$find) {
                $this->error("包名不存在");
            }
            
            $map = array();
            $map['package'] = $package;
            $map['start_time'] = $start_time;
            $map['end_time'] = $end_time;
            
            // 检查时间冲突
            $conflict_id = $this->check_conflict($map, $id);
            if ($conflict_id) {
                $this->error("排期时间与id为{$conflict_id}的记录有冲突！");
            }
            
            $ret = $model->table('sj_search_name_completely_match')->where(array('id'=>$id))->save($map);
            if ($ret || $ret === 0) {
                $this->success("编辑成功！");
            } else {
                $this->error("编辑失败！");
            }
        } else if ($_GET) {
            $id = $_GET['id'];
            $where = array(
                'id' => $id,
                'status' => 1,
            );
            $model = M();
            $find = $model->table('sj_search_name_completely_match')->where($where)->find();
            $this->assign('record', $find);
            $this->display();
        }
    }
    
    public function delete_package() {
        if ($_GET) {
            $id = $_GET['id'];
            $map = array(
                'status' => 0,
            );
            $model = M();
            $ret = $model->table('sj_search_name_completely_match')->where(array('id'=>$id))->save($map);
            if ($ret) {
                $this->success("删除成功！");
            } else {
                $this->error("删除失败！");
            }
        }
    }
    
    public function check_conflict($record, $except_id = 0) {
        $package = $record['package'];
        $start_time = $record['start_time'];
        $end_time = $record['end_time'];
        
        $where = array(
            'package' => $package,
            'start_time' => array('elt', $end_time),
            'end_time' => array('egt', $start_time),
        );
        if ($except_id) {
            $where['id'] = array('neq', $except_id);
        }
        $model = M();
        $find = $model->table('sj_search_name_completely_match')->where($where)->find();
        if ($find['id'])
            return $find['id'];
        return 0;
    }
}

?>