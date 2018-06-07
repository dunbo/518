<?php

class NewCategoryLabelManagementAction extends CommonAction {
    protected $table = 'new_category_label_management';
    private $max_rank = 12;
    
    public function index() {
        $bbs_model = D('Bbs_manage.Bbs_manage');
        
        $where = array(
            'status' => 1
        );
        $list = $bbs_model->table($this->table)->where($where)->order('rank asc')->select();
        
        $this->assign('list', $list);
        $this->display();
        
    }

    public function batch_rank() {
        $bbs_model = D('Bbs_manage.Bbs_manage');
        $arr = array();
        foreach ($_POST as $name => $rank) {
            $ret = preg_match("/^rank_(\d+)$/", $name, $matches);
            if (!$ret)
                continue;
            $id = $matches[1];
            // 判断输入的rank是不是数字
            $rank = trim($rank);
            if (!preg_match('/^\d+$/', $rank)) {
                $this->ajaxReturn('', "排序调整无效，排序值需为小于等于{$this->max_rank}的正整数", -1);
            }
            if ($rank < 1 || $rank > $this->max_rank) {
                $this->ajaxReturn('', "排序调整无效，排序值不可超过该模块最大位置数{$this->max_rank}", -1);
            }
            $arr[$id] = $rank;
        }

        foreach ($arr as $id1 => $rank1) {
            foreach ($arr as $id2 => $rank2) {
                if ($rank1 ==  $rank2 && $id1 != $id2) {
                    $this->ajaxReturn("", "排序调整无效，排序不可以相同", -1);
                }
            }
        }
        
        // 更新表
        $each_log = array();
        $ids = '';
        foreach ($arr as $id => $rank) {
            $ids .= $id.',';
            $where = array(
                'id' => $id
            );
            $data = array(
                'rank' => $rank,
                'update_tm' => time()
            );
            $log_result = $this -> logcheck($where, $this->table, $data, $bbs_model);
            $each_log[] = "编辑了id为{$id}的记录，{$log_result}";
            $ret = $bbs_model->table($this->table)->where($where)->save($data);
        }
        $log = implode(';', $each_log);
        $this->writelog("论坛PC端管理-新精品资源-标签管理：分类标签批量编辑排序，{$log}",$this->table,$ids,__ACTION__ ,"","edit");
        $this->ajaxReturn('', "更新排序成功！", 0);
    }
    
    public function add() {
        if ($_POST) {
            $bbs_model = D('Bbs_manage.Bbs_manage');
            $label_name = trim($_POST['label_name']); 
            $link_url = trim($_POST['link_url']);
            $rank= trim($_POST['rank']);

            if ($label_name == '') {
                $this->ajaxReturn('', '分类名称不能为空', -1);
            }
            if ($link_url == '') {
                $this->ajaxReturn('', '链接地址不能为空', -1);
            }
            if (!$this->check_url($link_url)) {
                $this->ajaxReturn('', '请填写有效的链接地址', -1);
            }
            if ($rank == '') {
                $this->ajaxReturn('', '排序不能为空', -1);
            }
            if (!preg_match('/^[1-9]\d*$/', $rank)) {
                $this->ajaxReturn('', "排序值需为小于等于{$this->max_rank}的正整数");
            }
            if ($rank > $this->max_rank) {
                $this->ajaxReturn('', "排序值不可超过该模块最大位置数{$this->max_rank}");
            }

            // 检查此位置是否已有排序
            $conflict_id = $this->check_rank($rank);
            if ($conflict_id) {
                $this->ajaxReturn($conflict_id, "已存在排序为{$rank}的记录", -1);
            }

            $data = array();
            $data['create_tm'] = $data['update_tm'] = time();
            $data['status'] = 1;

            $data['label_name'] = $label_name;
            $data['link_url'] = $link_url;
            $data['rank'] = $rank;
            
            $ret = $bbs_model->table($this->table)->add($data);
            if ($ret) {
                $this->writelog("论坛PC端管理-新精品资源-标签管理：分类标签新增了id为{$ret}的分类标签",$this->table,$ret,__ACTION__ ,"","add");
                $this->ajaxReturn('', '添加成功！', 0);
            } else {
                $this->ajaxReturn('', '添加失败！', -1);
            }
        } else {
            $this->display();
        }
    }

    public function edit() {
        $bbs_model = D('Bbs_manage.Bbs_manage');
        if ($_GET['id']) {
            $id = $_GET['id'];
            $where = array(
                'id' => $id
            );
            $find = $bbs_model->table($this->table)->where($where)->find();
            $this->assign('list', $find);
            $this->display();
        } else if ($_POST){
            $id = $_POST['id'];
            $label_name = trim($_POST['label_name']);
            $link_url = trim($_POST['link_url']);
            $rank = trim($_POST['rank']);
 
            if ($label_name == '') {
                $this->ajaxReturn('', '分类名称不能为空', -1);
            }
            if ($link_url == '') {
                $this->ajaxReturn('', '链接地址不能为空', -1);
            }
            if (!$this->check_url($link_url)) {
                $this->ajaxReturn('', '请填写有效的链接地址', -1);
            }
            if ($rank == '') {
                $this->ajaxReturn('', '排序不能为空', -1);
            }
            if (!preg_match('/^[1-9]\d*$/', $rank)) {
                $this->ajaxReturn('', "排序值需为小于等于{$this->max_rank}的正整数");
            }
            if ($rank > $this->max_rank) {
                $this->ajaxReturn('', "排序值不可超过该模块最大位置数{$this->max_rank}");
            }

            // 检查此位置是否已有排序
            $conflict_id = $this->check_rank($rank, $id);
            if ($conflict_id) {
                $this->ajaxReturn($conflict_id, "已存在排序为{$rank}的记录", -1);
            }
            $where = array(
                'id' => $id
            );
            $data = array();
            $data['update_tm'] = time();

            $data['label_name'] = $label_name;
            $data['link_url'] = $link_url;
            $data['rank'] = $rank;
            
            $log_result = $this -> logcheck($where, $this->table, $data, $bbs_model);
            $ret = $bbs_model->table($this->table)->where($where)->save($data);
            if ($ret) {
                $this->writelog("论坛PC端管理-新精品资源-标签管理：分类标签编辑了id为{$id}的分类标签，{$log_result}",$this->table,$id,__ACTION__ ,"","edit");
                $this->ajaxReturn('', '编辑成功！', 0);
            } else {
                $this->ajaxReturn('', '编辑失败！', -1);
            }

        }
    }
    
    public function del() {
        if ($_GET['id']) {
            $bbs_model = D('Bbs_manage.Bbs_manage');
            $id = $_GET['id'];
            $where = array(
                'id' => $id,
                'status' => 1
            );
            $data = array(
                'update_tm' => time(),
                'status' => 0
            );
            $ret = $bbs_model->table($this->table)->where($where)->save($data);
            if ($ret) {
                $this->writelog("论坛PC端管理-新精品资源-标签管理：分类标签删除了id为{$id}的分类标签",$this->table,$id,__ACTION__ ,"","del");
                $this->success("删除成功");
            } else {
                $this->error("删除失败");
            }
        }
    }

    private function check_rank($rank, $except_id) {
        // check if rank exists
        $where = array(
            'rank' => $rank,
            'status' => 1
        );
        if ($except_id) {
            $where['id'] = array('neq', $except_id);
        }

        $bbs_model = D('Bbs_manage.Bbs_manage');
        $find = $bbs_model->table($this->table)->where($where)->find();
        if ($find) {
            return $find['id'];
        }
        return 0;
    }
    
    public function check_url($url) {
        $reg = "/^((http:\/\/)|(https:\/\/))([\w\d-]+\.)+[\w-]+(\/[\x{4e00}-\x{9fa5}\d\w-.\/?%&=]*)?$/iu";
        if (!preg_match($reg, $url)) {
            return false;
        }
        return true;
    }
}
