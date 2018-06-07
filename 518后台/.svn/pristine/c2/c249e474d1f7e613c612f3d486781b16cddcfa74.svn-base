<?php

class AdFrameworkModel extends Model {

    public function getAllFrameworkCount($where) {
        $list = $this->table('ad_framework')->join('left join ad_client on ad_framework.client_id = ad_client.id')->join('left join sj_admin_users on sj_admin_users.admin_user_id=ad_framework.charge_id')->where($where)->count();
        return $list;
    }
    
    public function getFrameworkList($where, $firstRow=0, $listRows=0) {
        $field = "ad_framework.id as id, ad_framework.client_id as client_id, ad_framework.framework_number as framework_number, ad_framework.sign_time as sign_time, ad_framework.cooperate_account as cooperate_account, ad_framework.start_time as start_time, ad_framework.end_time as end_time, ad_framework.charge_id as charge_id, ad_framework.purchase_channels as purchase_channels, ad_framework.affix_name as affix_name, ad_framework.affix_url as affix_url, ad_framework.expected_deposit as expected_deposit, ad_framework.remark as remark";
        $limit = "";
        if ($firstRow || $listRows) {
            $limit = "{$firstRow},{$listRows}";
        }
        $list = $this->field($field)->table('ad_framework')->join('left join ad_client on ad_framework.client_id = ad_client.id')->join('left join sj_admin_users on sj_admin_users.admin_user_id=ad_framework.charge_id')->where($where)->limit($limit)->select();
        return $list;
    }
    
    // 根据client_id获得客户名称
    public function getClientName($client_id) {
        $where = array(
            'id' => $client_id,
            'status' => 1
        );
        $find = $this->table('ad_client')->where($where)->find();
        if (!$find['client_name'])
            return false;
        return $find['client_name'];
    }
    
    // 根据client_name获得客户id
    public function getClientId($client_name) {
        $where = array(
            'client_name' => $client_name,
            'status' => 1
        );
        $find = $this->table('ad_client')->where($where)->find();
        if (!$find['id'])
            return false;
        return $find['id'];
    }
    
    // 返回所有有效客户列表
    public function getAllClients() {
        $where = array(
            'status' => 1
        );
        $list = $this->table('ad_client')->where($where)->select();
        return $list;
    }
    
    // 判断framework_number是否存在
    public function checkFrameworkNumberExists($framework_number, $except_id=0) {
        $where = array(
            'framework_number' => $framework_number,
            'status' => 1,
        );
        if ($except_id) {
            $where['id'] = array('neq', $except_id);
        }
        $find = $this->table('ad_framework')->where($where)->find();
        if (!$find['id'])
            return false;
        return $find['id'];
    }
    
    // 获得负责人名字
    public function getChargeName($charge_id) {
        $where = array(
            'admin_user_id' => $charge_id,
            'admin_state' => 1
        );
        $find = $this->table('sj_admin_users')->where($where)->find();
        if (!$find['admin_user_name'])
            return false;
        return $find['admin_user_name'];
    }
    
    // 获得负责人id
    public function getChargeId($charge_name) {
        $where = array(
            'admin_user_name' => $charge_name,
            'admin_state' => 1
        );
        $find = $this->table('sj_admin_users')->where($where)->find();
        if (!$find['admin_user_id'])
            return false;
        return $find['admin_user_id'];
    }
    
    // 获得购买频道名称
    public function getPurchaseChannelNames($purchase_channels) {
        $ContractModel = D("sendNum.Contract");
        $firstNames = $ContractModel->getFirstNames();
        $purchase_channel_arr = explode(',', $purchase_channels);
        $purchase_channel_names = '';
        foreach ($purchase_channel_arr as $channel) {
            if (!$channel)
                continue;
            if ($purchase_channel_names)
                $purchase_channel_names .= ',';
            $purchase_channel_names .= $firstNames[$channel];
        }
        return $purchase_channel_names;
    }
    
    // 添加框架协议
    public function addFramework($map) {
        $ret = $this->table('ad_framework')->add($map);
        return $ret;
    }
    
    // 更新框架协议
    public function updateFramework($id, $map) {
        $ret = $this->table('ad_framework')->where(array('id'=>$id))->save($map);
        return $ret;
    }
    
    // 根据id获得框架协议
    public function getFramework($id) {
        $where = array(
            'id' => $id,
            'status' => 1
        );
        $find = $this->table('ad_framework')->where($where)->find();
        return $find;
    }
    
    
    //////////////////////////// 保证金相关函数
    // 录入保证金
    public function add_account($data) {
        $ret = $this->table('ad_framework_account')->add($data);
        return $ret;
    }
    
    // 编辑保证金
    public function edit_account($id, $data) {
        $where = array('id' => $id);
        $ret = $this->table('ad_framework_account')->where($where)->save($data);
        return $ret;
    }
    
    // 删除保证金
    public function delete_account($id) {
        $map = array(
            'status' => 0,
        );
        $ret = $this->table('ad_framework_account')->where(array('id' => $id))->save($map);
        return $ret;
    }
    
    // 根据id查找录入金额的记录
    public function get_account_record($id) {
        $where = array('id'=>$id);
        $find = $this->table('ad_framework_account')->where($where)->find();
        return $find;
    }
    
    // 添加发票/票据
    public function add_invoice($data) {
        $ret = $this->table('ad_framework_invoice')->add($data);
        return $ret;
    }
    
    // 编辑发票/票据
    public function edit_invoice($id, $data) {
        $where = array('id' => $id);
        $ret = $this->table('ad_framework_invoice')->where($where)->save($data);
        return $ret;
    }
    
    // 删除发票/票据
    public function delete_invoice($id) {
        $map = array(
            'status' => 0,
        );
        $ret = $this->table('ad_framework_invoice')->where(array('id' => $id))->save($map);
        return $ret;
    }
    
    // 根据id查找录入发票的记录
    public function get_invoice_record($id) {
        $where = array('id'=>$id);
        $find = $this->table('ad_framework_invoice')->where($where)->find();
        return $find;
    }
    
    // 框架协议已收保证金
    public function get_total_input_account($framework_id) {
        $where = array(
            'framework_id' => $framework_id,
            'status' => 1,
        );
        $find = $this->field("sum(account_input) as total_input")->table('ad_framework_account')->where($where)->find();
        if ($find['total_input'])
            $total_input = $find['total_input'];
        else
            $total_input = 0;
        return $total_input;
    }
    
    // 框架协议已抵扣的保证金
    public function get_used_account($framework_id) {
        $AdAccountInputModel = D("sendNum.AdAccountInput");
        $ContractModel = D("sendNum.Contract");
        // 查找该框架协议下的所有合同
        $contract_list = $ContractModel->getAllContractsOfFramework($framework_id);
        $used_account = 0;
        foreach ($contract_list as $contract_id) {
            $used_account += $AdAccountInputModel->get_total_input_withhold_of_contract($contract_id);
        }
        return $used_account;
    }
    
    // 待抵扣保证金
    public function get_left_account($framework_id) {
        $left_account = $this->get_total_input_account($framework_id) - $this->get_used_account($framework_id);
        return $left_account;
    }
    
    // 待抵扣发票
    public function get_left_invoice($framework_id) {
        $left_invoice = $this->get_total_input_invoice($framework_id, 1) - $this->get_used_account($framework_id);
        return $left_invoice;
    }
    
    // 待抵扣 = 待抵扣保证金 <= 待抵扣发票 ? 待抵扣保证金 : 待抵扣发票
    public function get_left_amount($framework_id) {
        $left_account = $this->get_left_account($framework_id);
        $left_invoice = $this->get_left_invoice($framework_id);
        return $left_account <= $left_invoice ? $left_account : $left_invoice;
    }
    
    // 框架协议已已开发票/票据总额
    public function get_total_input_invoice($framework_id, $invoice_type = 0) {
        $where = array(
            'framework_id' => $framework_id,
            'status' => 1,
        );
        if ($invoice_type) {
            $where['invoice_type'] = $invoice_type;
        }
        $find = $this->field("sum(invoice_input) as total_input")->table('ad_framework_invoice')->where($where)->find();
        if ($find['total_input'])
            $total_input = $find['total_input'];
        else
            $total_input = 0;
        return $total_input;
    }
    
    
    
    // 某个框架协议的录入保证金金额详细记录
    public function add_account_detail_list($framework_id) {
        $where = array(
            'framework_id' => $framework_id,
            'status' => 1,
        );
        $list = $this->table('ad_framework_account')->where($where)->order('create_time desc, account_input_time desc, account_input desc')->select();
        // 增加一个自增长字段list_number以展示用
        $i = 0;
        foreach ($list as $key => $value) {
            $list[$key]['list_number'] = ++$i;
        }
        return $list;
    }
    
    // 某个合同某个月的录入发票详细记录
    public function add_invoice_detail_list($framework_id) {
        $where = array(
            'framework_id' => $framework_id,
            'status' => 1,
        );
        $list = $this->table('ad_framework_invoice')->where($where)->order('create_time desc')->select();
        // 增加一个自增长字段list_number以展示用
        $i = 0;
        foreach ($list as $key => $value) {
            $list[$key]['list_number'] = ++$i;
        }
        return $list;
    }
    
}

?>