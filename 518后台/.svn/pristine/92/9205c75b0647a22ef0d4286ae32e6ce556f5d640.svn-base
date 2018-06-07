<?php

class GameSimulatorSoftwareModel extends Model {

    protected $trueTableName = "sj_game_simulator";
    
    public function get_list($type) {
        $where = array();
        $where['type'] = $type;
        $where['status'] = 1;
        import("@.ORG.Page");
        $count = $this->where("type = '{$type}' AND status != 0")->count();//查询满足要求的总记录数
        $Page = new Page($count,10);//实例化分页类传入总记录数和每页显示的记录数
        
        $list = $this->where("type = '{$type}' AND status != 0")->order('rank')->limit($Page->firstRow.','.$Page->listRows)->select();
        $all_list = $this->where("type = '{$type}' AND status != 0")->order('rank')->select();
        $last_rank = 0;
        if ($all_list) {
            $last_record = end($all_list);
            $last_rank = $last_record['rank'];
        }
        $show = $Page->show();//分页显示输出
        
        $ret = array(
            'list' => $list,
            'all_list' => $all_list,
            'last_rank' => $last_rank,
            'show' => $show,
        );
        
        return $ret;
    }
    
    public function get_record($id) {
        $where = array();
        $where['id'] = $id;
        return $this->where($where)->select();
    }
    
    public function check_software_name($software_name) {
        $where = array();
        $where['software_name'] = array("EQ", $_POST['software_name']);
        $where['status'] = array("EQ", 1);
        return $this->where($where)->select();
    }
    
    public function add_software($data) {
        if (!isset($data['type']) || !isset($data['software_name']) || !isset($data['rom_url'])
            || !isset($data['icon_url']) || !isset($data['star'])) {
            return false;
        }
        // 获得添加之前的所有记录
        $ret2 = $this->get_list($data['type']);
        $all_list = $ret2['all_list'];
        // 准备添加
        $map = array();
        $map['status'] = 1;
        $map['type'] = $data['type'];
        $map['software_name'] = $data['software_name'];
        $map['rom_url'] = $data['rom_url'];
        $map['rom_md5'] = $data['rom_md5'];
        $map['rom_size'] = $data['rom_size'];
        $map['icon_url'] = $data['icon_url'];
        $map['star'] = $data['star'];
        if (isset($data['note'])) {
            $map['note'] = $data['note'];
        }
        $map['rank'] = 1;
        $ret = $this->add($map);
        if (!$ret) {
            return $ret;
        }
        // 之前所有有效记录的rank值+1
        foreach($all_list as $value) {
            $where = array();
            $where['id'] = array('EQ', $value['id']);
            $data2 = array();
            $data2['rank'] = $value['rank'] + 1;
            $this->where($where)->save($data2);
        }
        
        return $ret;
    }
    
    public function save_software($id, $data) {
        //获得编辑数据编辑前的内容
        $record = $this->get_record($id);
        $record = $record[0];
        ////////////////////////////
        $where = array();
        $where['id'] = array('EQ', $id);
        $map = array();
        // 用来存储编辑之后类型的所有记录（不包括当前记录）
        $all_list_new = array();
        ///////////////////////////
        if (isset($data['type'])) {
            $map['type'] = $data['type'];
            if ($data['type'] != $record['type']) {
                // 将其置为新类型的rank=1
                $map['rank'] = 1;
                $ret_new = $this->get_list($data['type']);
                $all_list_new = $ret_new['all_list'];
            }
        }
        if (isset($data['software_name'])) {
            $map['software_name'] = $data['software_name'];
        }
        if (isset($data['rom_url'])) {
            $map['rom_url'] = $data['rom_url'];
        }
        if (isset($data['rom_md5'])) {
            $map['rom_md5'] = $data['rom_md5'];
        }
        if (isset($data['rom_size'])) {
            $map['rom_size'] = $data['rom_size'];
        }
        if (isset($data['icon_url'])) {
            $map['icon_url'] = $data['icon_url'];
        }
        if (isset($data['star'])) {
            $map['star'] = $data['star'];
        }
        if (isset($data['note'])) {
            $map['note'] = $data['note'];
        }
        $ret = $this->where($where)->save($map);
        if (!$ret) {
            return $ret;
        }
        
        // 编辑成功后，再次判断类型是否发生变化，以便更改其他记录的rank值
        if (isset($data['type']) && trim($data['type'])) {
            if ($data['type'] != $record['type']) {
                // 将之前同类型的rank全部-1
                // 获得编辑之前类型的所有记录
                $ret_old = $this->get_list($record['type']);
                $all_list_old = $ret_old['all_list'];
                foreach($all_list_old as $value) {
                    $where = array();
                    $where['id'] = array('EQ', $value['id']);
                    $where['rank'] = array('GT', $record['rank']);
                    $data2 = array();
                    $data2['rank'] = $value['rank'] - 1;
                    $this->where($where)->save($data2);
                }
                // 将新类型的rank全部+1
                foreach($all_list_new as $value) {
                    $where = array();
                    $where['id'] = array('EQ', $value['id']);
                    $data2 = array();
                    $data2['rank'] = $value['rank'] + 1;
                    $this->where($where)->save($data2);
                }
            }
        }
        return $ret;
    }
    
    public function delete_software($id) {
        $where = array();
        $where['id'] = array('EQ', $id);
        $data = array();
        $data['status'] = 0;
        $ret = $this->where($where)->save($data);
        if (!$ret) {
            return $ret;
        }
        // 将这条记录后面的所有有效记录rank值-1
        $record = $this->get_record($id);
        $record = $record[0];
        $where = array();
        $where['status'] = array('NEQ', 0);
        $where['type'] = array('EQ', $record['type']);
        $where['rank'] = array('GT', $record['rank']);
        // 得到所有应改变rank值的记录
        $list = $this->where($where)->select();
        foreach($list as $value) {
            $where = array();
            $where['id'] = array('EQ', $value['id']);
            $data = array();
            $data['rank'] = $value['rank'] - 1;
            $this->where($where)->save($data);
        }
        return $ret;
    }
	
	/*
	public function delete_software($id) {
        $where = array();
        $where['id'] = array('EQ', $id);
        $pre_path = UPLOAD_PATH;
        $ret = $this->where($where)->find();
		//echo $this->getLastSql();
        $del = file_exists($pre_path.$ret['rom_url']);
        if($del == 1){
            $del_ret = unlink($pre_path.$ret['rom_url']);
            if($del_ret == 1){
                $ret = $this->where($where)->delete();
            }
        }
        if (!$ret) {
            return $ret;
        }
		 // 将这条记录后面的所有有效记录rank值-1
        $record = $this->get_record($id);
        $record = $record[0];
        $where = array();
        $where['status'] = array('EQ', 1);
        $where['type'] = array('EQ', $record['type']);
        $where['rank'] = array('GT', $record['rank']);
        // 得到所有应改变rank值的记录
        $list = $this->where($where)->select();
        foreach($list as $value) {
            $where = array();
            $where['id'] = array('EQ', $value['id']);
            $data = array();
            $data['rank'] = $value['rank'] - 1;
            $this->where($where)->save($data);
        }
        return $ret;
	}
*/
}
?>