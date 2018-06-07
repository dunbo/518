<?php

class GameSimulatorTypeModel extends Model {
    protected $trueTableName = "sj_game_simulator_type";
    
    public function get_simulator_type() {
        return $this->order('id')->select();
    }
    
    public function get_record($id) {
        $where = array();
        $where['id'] = array('EQ', $id);
        return $this->where($where)->select();
    }
    
    public function update_simulator_type($id, $name, $package) {
        $where = array();
        $where['id'] = array('EQ', $id);
        $data = array();
        $data['name'] = $name;
        $data['package'] = $package;
        return $this->where($where)->save($data);
    }
    
    public function set_status($id, $status) {
        $where = array();
        $where['id'] = array('EQ', $id);
        $data = array();
        $data['status'] = $status;
        return $this->where($where)->save($data);
    }
    
    public function get_all_status() {
        return $this->field('name, status')->order('id')->select();
    }
    
    public function check_if_name_exists($name) {
        $where = array();
        $where['name'] = array('EQ', $name);
        $ret = $this->where($where)->select();
        if ($ret) {
            return true;
        }
        return false;
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
        $where = array();
        $where['package'] = array('EQ', $package);
        $ret = $this->where($where)->select();
        if ($ret) {
            return true;
        }
        return false;
    }
    
}

?>