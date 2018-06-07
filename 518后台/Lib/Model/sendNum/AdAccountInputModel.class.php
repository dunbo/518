<?php

class AdAccountInputModel extends Model {
    
    public function getGroupListFromContract($where, $firstRow, $listRows) {
        if (!$firstRow) $firstRow = 0;
        if (!$listRows) $firstRow = 10;
        $group_list = $this->field('ad_tj_soft.id as id, ad_tj_soft.contract_id as contract_id, contract_number,framework_id, month, client_name')->table('ad_tj_soft')->where($where)->group('contract_id, month')->join('left join ad_contract on ad_tj_soft.contract_id=ad_contract.contract_id')->limit($firstRow.','.$listRows)->order('month desc, CONVERT(client_name USING gbk) COLLATE gbk_chinese_ci ASC')->select();
        return $group_list;
    }
    
    public function getAllGroupListFromContract($where) {
        $all_group_list = $this->field('ad_tj_soft.id as id, ad_tj_soft.contract_id as contract_id, contract_number, month, client_name')->table('ad_tj_soft')->where($where)->group('contract_id, month')->join('left join ad_contract on ad_tj_soft.contract_id=ad_contract.contract_id')->order('month desc, CONVERT(client_name USING gbk) COLLATE gbk_chinese_ci ASC')->select();
        return $all_group_list;
    }
    
    public function getGroupListFromChannel($where, $firstRow, $listRows) {
        if (!$firstRow) $firstRow = 0;
        if (!$listRows) $firstRow = 10;
        $group_list = $this->field('id, month, channel')->table('ad_tj_soft')->where($where)->group('channel, month')->limit($firstRow.','.$listRows)->order('month desc')->select();
        return $group_list;
    }
    
    public function getAllGroupListFromChannel($where) {
        $all_group_list = $this->field('id, month, channel')->table('ad_tj_soft')->where($where)->group('channel, month')->order('month desc')->select();
        return $all_group_list;
    }
    
    // 根据合同id、月份，返回该合同这个月已收款总额
    public function get_total_input_account($contract_id, $month, $id=0) {
        $where = array(
            'contract_id' => $contract_id,
            'month' => $month,
            'status' => 1,
        );
        if ($id) {
            $where['id'] = array('neq', $id);
        }
        $find = $this->field("sum(account_input) as total_input")->table('ad_settlement_account')->where($where)->find();
        if ($find['total_input'])
            $total_input = $find['total_input'];
        else
            $total_input = 0;
        return $total_input;
    }
    
    // 根据合同id、月份，返回该合同这个月已开发票总额
    public function get_total_input_invoice($contract_id, $month, $id=0) {
        $where = array(
            'contract_id' => $contract_id,
            'month' => $month,
            'status' => 1,
        );
        if ($id) {
            $where['id'] = array('neq', $id);
        }
        $find = $this->field("sum(invoice_input) as total_input")->table('ad_settlement_invoice')->where($where)->find();
        if ($find['total_input'])
            $total_input = $find['total_input'];
        else
            $total_input = 0;
        return $total_input;
    }
    
    // 根据合同id、月份，返回该合同这个月保证金已抵扣总额
    public function get_total_input_withhold($contract_id, $month, $id=0) {
        $where = array(
            'contract_id' => $contract_id,
            'month' => $month,
            'status' => 1,
        );
        if ($id) {
            $where['id'] = array('neq', $id);
        }
        $find = $this->field("sum(withhold_input) as total_input")->table('ad_settlement_withhold')->where($where)->find();
        if ($find['total_input'])
            $total_input = $find['total_input'];
        else
            $total_input = 0;
        return $total_input;
    }
    
    // TODO，暂时替代框架协议model里的已交保证金
    public function get_paid_withhold($contract_id) {
        return 100;
    }
    
    // TODO，暂时替代框架协议model里的已开发票
    public function get_paid_withhold_invoice($contract_id) {
        return 80;
    }
    
    // 根据合同id得到其归属框架的已被使用（即已被抵扣掉）的保证金
    public function get_used_withhold($contract_id, $id = 0) {
        // 根据contract_id找到上面所属的有效框架协议
        $ContractModel = D("sendNum.Contract");
        $framework_id = $ContractModel->getFrameworkId($contract_id);
        if (!$framework_id)
            return 0;
        // 根据framework_id找其下的所有contract
        $contract_id_arr = $ContractModel->getAllContractsOfFramework($framework_id);
        if (!$contract_id_arr)
            return 0;
        // 挨个合同查找已用的保证金
        $used = 0;
        foreach ($contract_id_arr as $contract_id) {
            $used += $this->get_total_input_withhold_of_contract($contract_id, $id);
        }
        return $used;
    }
    
    // 根据合同id获得该合同已用的保证金抵扣
    public function get_total_input_withhold_of_contract($contract_id, $id = 0) {
        $where = array(
            'contract_id' => $contract_id,
            'status' => 1,
        );
        if ($id) {
            $where['id'] = array('neq', $id);
        }
        $find = $this->field("sum(withhold_input) as total_input")->table('ad_settlement_withhold')->where($where)->find();
        if ($find['total_input'])
            $total_input = $find['total_input'];
        else
            $total_input = 0;
        return $total_input;
    }
    
    // 根据id查找录入金额的记录
    public function get_account_record($id) {
        $where = array('id'=>$id);
        $find = $this->table('ad_settlement_account')->where($where)->find();
        return $find;
    }
    
    // 录入金额
    public function add_account($data) {
        $ret = $this->table('ad_settlement_account')->add($data);
        return $ret;
    }
    
    // 编辑金额
    public function edit_account($id, $data) {
        $where = array('id' => $id);
        $ret = $this->table('ad_settlement_account')->where($where)->save($data);
        return $ret;
    }
    
    // 删除金额
    public function delete_account($id) {
        $map = array(
            'status' => 0,
        );
        $ret = $this->table('ad_settlement_account')->where(array('id' => $id))->save($map);
        return $ret;
    }
    
    // 根据id查找录入发票的记录
    public function get_invoice_record($id) {
        $where = array('id'=>$id);
        $find = $this->table('ad_settlement_invoice')->where($where)->find();
        return $find;
    }
    
    // 添加发票
    public function add_invoice($data) {
        $ret = $this->table('ad_settlement_invoice')->add($data);
        return $ret;
    }
    
    // 编辑发票
    public function edit_invoice($id, $data) {
        $where = array('id' => $id);
        $ret = $this->table('ad_settlement_invoice')->where($where)->save($data);
        return $ret;
    }
    
    // 删除发票
    public function delete_invoice($id) {
        $map = array(
            'status' => 0,
        );
        $ret = $this->table('ad_settlement_invoice')->where(array('id' => $id))->save($map);
        return $ret;
    }
    
    // 根据id查找保证金抵扣的记录
    public function get_withhold_record($id) {
        $where = array('id'=>$id);
        $find = $this->table('ad_settlement_withhold')->where($where)->find();
        return $find;
    }
    
    // 录入保证金抵扣
    public function add_withhold($data) {
        $ret = $this->table('ad_settlement_withhold')->add($data);
        return $ret;
    }
    
    // 编辑保证金抵扣
    public function edit_withhold($id, $data) {
        $where = array('id' => $id);
        $ret = $this->table('ad_settlement_withhold')->where($where)->save($data);
        return $ret;
    }
    
    // 删除保证金抵扣
    public function delete_withhold($id) {
        $map = array(
            'status' => 0,
        );
        $ret = $this->table('ad_settlement_withhold')->where(array('id' => $id))->save($map);
        return $ret;
    }
    
    // 某个合同某个月的录入金额详细记录
    public function add_account_detail_list($contract_id, $month) {
        $where = array(
            'contract_id' => $contract_id,
            'month' => $month,
            'status' => 1,
        );
        $list = $this->table('ad_settlement_account')->where($where)->order('create_time desc, account_input_time desc, account_input desc')->select();
        // 增加一个自增长字段list_number以展示用
        $i = 0;
        foreach ($list as $key => $value) {
            $list[$key]['list_number'] = ++$i;
        }
        return $list;
    }
    
    // 某个合同某个月的录入发票详细记录
    public function add_invoice_detail_list($contract_id, $month) {
        $where = array(
            'contract_id' => $contract_id,
            'month' => $month,
            'status' => 1,
        );
        $list = $this->table('ad_settlement_invoice')->where($where)->order('create_time desc')->select();
        // 增加一个自增长字段list_number以展示用
        $i = 0;
        foreach ($list as $key => $value) {
            $list[$key]['list_number'] = ++$i;
        }
        return $list;
    }
    
    // 某个合同某个月的录入保证金抵扣详细记录
    public function add_withhold_detail_list($contract_id, $month) {
        $where = array(
            'contract_id' => $contract_id,
            'month' => $month,
            'status' => 1,
        );
        $list = $this->table('ad_settlement_withhold')->where($where)->order('create_time desc, withhold_input desc')->select();
        // 增加一个自增长字段list_number以展示用
        $i = 0;
        foreach ($list as $key => $value) {
            $list[$key]['list_number'] = ++$i;
        }
        return $list;
    }
    
}

?>