<?php

class SoarControlAction extends CommonAction {

    public function index() {
        $model = M();
        $where = array(
            'config_type' => 'SOAR_CONTROL',
            'status' => 1,
        );
        $config = $model->table('pu_config')->where($where)->find();
        $config_arr = json_decode($config['configcontent'], true);
        
        $this->assign('config', $config_arr);
        $this->display();
        
    }
    
    public function edit() {
        $digit_reg = '/^[1-9]+\d*$/';
        
        $in_days = trim($_POST['in_days']);
        $soft_amount = trim($_POST['soft_amount']);
        $factor1 = trim($_POST['factor1']);
        $factor2 = trim($_POST['factor2']);
        $factor3 = trim($_POST['factor3']);
        $factor4 = trim($_POST['factor4']);
        $factor5 = trim($_POST['factor5']);
        $app_weight = trim($_POST['app_weight']);
        $game_weight = trim($_POST['game_weight']);
        
        if (!$in_days) {
            $this->error("筛选范围不能为空");
        }
        if (!preg_match($digit_reg, $in_days)) {
            $this->error("筛选范围必须为正整数");
        }
        if (!$soft_amount) {
            $this->error("飙升页面显示软件数量不能为空");
        }
        if (!preg_match($digit_reg, $soft_amount)) {
            $this->error("飙升页面显示软件数量值必须为正整数");
        }
        if (!$factor1) {
            $this->error("factor1权重不能为空");
        }
        if (!preg_match($digit_reg, $factor1)) {
            $this->error("factor1权重值必须为正整数");
        }
        if (!$factor2) {
            $this->error("factor2权重不能为空");
        }
        if (!preg_match($digit_reg, $factor2)) {
            $this->error("factor2权重值必须为正整数");
        }
        if (!$factor3) {
            $this->error("factor3权重不能为空");
        }
        if (!preg_match($digit_reg, $factor3)) {
            $this->error("factor3权重值必须为正整数");
        }
        if (!$factor4) {
            $this->error("factor4权重不能为空");
        }
        if (!preg_match($digit_reg, $factor4)) {
            $this->error("factor4权重值必须为正整数");
        }
        if (!$factor5) {
            $this->error("factor5权重不能为空");
        }
        if (!preg_match($digit_reg, $factor5)) {
            $this->error("factor5权重值必须为正整数");
        }
        if (!$app_weight) {
            $this->error("应用权重不能为空");
        }
        if (!preg_match($digit_reg, $app_weight)) {
            $this->error("应用权重值必须为正整数");
        }
        if (!$game_weight) {
            $this->error("游戏权重不能为空");
        }
        if (!preg_match($digit_reg, $game_weight)) {
            $this->error("游戏权重值必须为正整数");
        }
        
        $model = M();
        $data = array();
        $data['in_days'] = $in_days;
        $data['soft_amount'] = $soft_amount;
        $data['factor1'] = $factor1;
        $data['factor2'] = $factor2;
        $data['factor3'] = $factor3;
        $data['factor4'] = $factor4;
        $data['factor5'] = $factor5;
        $data['app_weight'] = $app_weight;
        $data['game_weight'] = $game_weight;
        $json_data = json_encode($data);
        $map = array();
        $map['configcontent'] = $json_data;
        $where = array(
            'config_type' => 'SOAR_CONTROL',
            'status' => 1,
        );
        $log = $this->logcheck($where, 'pu_config', $map, $model);
        $ret = $model->table('pu_config')->where($where)->save($map);
        if ($ret || $ret === 0) {
            if ($ret) {
                // 写日志
                $this->writelog("编辑了运营位管理—市场运营基础配置—飙升参数配置：{$log}", 'pu_config','SOAR_CONTROL',__ACTION__ ,'','edit');
            }
            $this->success("编辑成功！");
        } else {
            $this->success("编辑失败！");
        }
    }

}

?>