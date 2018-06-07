<?php

/**
 * Desc:   安智游戏后台--新用户首页推荐
 * @author Ye Xiaola<yexiaola@anzhi.com>
 * 2013-10-31
*/

class GameHomePageRecommendationModel extends Model {
    protected $trueTableName = 'sj_game_home_recommendation';
    
    // 根据id得到一条记录
    public function get_record($id) {
        $where = array();
        $where['id'] = array('EQ', $id);
        return $this->where($where)->select();
    }
    
    // 添加一条记录
    public function add_record($data) {
        $ret = $this->add($data);
        //file_put_contents("222", $this->getlastsql());
        return $ret;
    }
    
    // 编辑一条记录
    public function edit_record($where, $data) {
        return $this->where($where)->save($data);
    }
    
    // 删除首页推荐的三个软件（将status置为0）
    public function delete_home_page_records() {
        $now = time();
        $where = array();
        $where['status'] = array('EQ', 1);
        $where['type'] = array('EQ', 1);
        $where['endtime'] = array('GT', $now);
        $data = array();
        $data['status'] = 0;
        return $this->where($where)->save($data);
    }
    
    // 根据id删除一条记录（将status置为0）
    public function delete_record($id) {
        $where = array();
        $where['id'] = array('EQ', $id);
        $data = array();
        $data['status'] = 0;
        return $this->where($where)->save($data);
    }
    
    // 获取首页推荐的三个软件
    public function get_home_page_list() {
        $now = time();
        $where = array();
        $where['status'] = array('EQ', 1);
        $where['type'] = array('EQ', 1);
        $where['endtime'] = array('GT', $now);
        $list = $this->where($where)->select();
        // 查询软件名称
        $sj_soft_model = M();
        foreach($list as $key => $record) {
            $where = array();
            $where['package'] = array('EQ', $record['package']);
            $where['status'] = array('EQ', 1);
            $where['hide'] = array('EQ', 1);
            $ret = $sj_soft_model->table("sj_soft")->where($where)->select();
            $list[$key]['softname'] = $ret[0]['softname'];
        }
        return $list;
    }
    
    // 获取备选库的所有软件
    public function get_backup_list() {
        $where = array();
        $where['status'] = array('EQ', 1);
        $where['type'] = array('EQ', 2);
        
        import("@.ORG.Page");
        $count = $this->where($where)->count();//查询满足要求的总记录数
        $Page = new Page($count,10);//实例化分页类传入总记录数和每页显示的记录数
        
        $list = $this->where($where)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        // 分配序号，先获得所有记录（$all_list），然后依次分配序号，再对显示的记录（$list）分配序号
        $all_list = $this->where($where)->order('id desc')->select();
        $i = 1;
        foreach($all_list as $key => $value) {
            $all_list[$key]['rank'] = $i;
            $i++;
        }
        foreach($list as $key => $value) {
            $id = $list[$key]['id'];
            foreach($all_list as $k => $v) {
                if ($all_list[$k]['id'] == $id) {
                    $list[$key]['rank'] = $all_list[$k]['rank'];
                    break;
                }
            }
        }
        // 查询软件名称
        $sj_soft_model = M();
        foreach($list as $key => $record) {
            $where = array();
            $where['package'] = array('EQ', $record['package']);
            $where['status'] = array('EQ', 1);
            $where['hide'] = array('EQ', 1);
            $ret = $sj_soft_model->table("sj_soft")->where($where)->select();
            $list[$key]['softname'] = $ret[0]['softname'];
        }
        $show = $Page->show();//分页显示输出
        
        $ret = array(
            'list' => $list,
            'show' => $show,
        );
        return $ret;
    }
    
    public function check_if_package_exists($package) {
        $where = array();
        $where['package'] = array('EQ', $package);
        $where['status'] = array('EQ', 1);
        $where['hide'] = array('EQ', 1);
        $model = M();
        $ret = $model->table('sj_soft')->where($where)->select();
        if ($ret) {
            return true;
        }
        return false;
    }
    
    public function check_if_package_already_added($package) {
        $now = time();
        $where = array();
        $where['package'] = array('EQ', $package);
        $where['status'] = array('EQ', 1);
        $where['endtime'] = array(array('GT', $now), array('EQ', 0), 'OR');
        $ret = $this->where($where)->select();
        if ($ret) {
            return true;
        }
        return false;
    }
    
}
?>