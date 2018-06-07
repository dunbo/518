<?php

class GameSimulatorTypeAction extends CommonAction {
    public function simulator_type() {
        // 获得模拟器类型的配置
        $model = D('Sj.GameSimulatorType');
        $list = $model->get_simulator_type();
        
        $this->assign('list', $list);
        $this->display('simulator_type');
    }
    
    public function save_simulator_type() {
        if ($_POST) {
            $model = D('Sj.GameSimulatorType');
            // 获得已勾选的ids
            $ids_checked = array();
            if ($_POST['ids_checked'] != "") {
                $ids_checked = explode(',', $_POST['ids_checked']);
            }
            // 设置status=1
            foreach($ids_checked as $id) {
                $model->set_status($id, 1);
            }
            // 获得模拟器类型的所有ids
            $list = $model->get_simulator_type();
            $ids = array();
            foreach($list as $value) {
                $ids[] = $value['id'];
            }
            // 获得未勾选的ids
            $ids_unchecked = array();
            foreach($ids as $id) {
                if (!in_array($id, $ids_checked)) {
                    $ids_unchecked[] = $id;
                }
            }
            // 设置status=0
            foreach($ids_unchecked as $id) {
                $model->set_status($id, 0);
            }
            $this->writelog("模拟器--模拟器类型配置：配置id为：{$_POST['ids_checked']}为有效",'sj_game_simulator_type',$_POST['ids_checked'],__ACTION__ ,'','edit');
            $this->ajaxReturn(0, "", 0);
        }
    }
    
    public function simulator_apk_config() {
        $model = D('Sj.GameSimulatorType');
        $list = $model->get_simulator_type();
        
        $this->assign('list', $list);
        $this->display('simulator_apk_config');
    }
    
    public function edit_apk_config() {
        $model = D('Sj.GameSimulatorType');
        if ($_GET) {
            $list = $model->get_record($_GET['id']);
            $this->assign('list', $list[0]);
        }
        if ($_POST) {
            // 查看模拟器名称是否已存在
            if ($_POST['name'] && $_POST['org_name'] != $_POST['name']) {
                $ret = $model->check_if_name_exists($_POST['name']);
                if ($ret) {
                    // 模拟器名称存在
                    echo 1;
                    exit;
                }
            }
            // 查看模拟器包名是否不存在
            if ($_POST['package_name'] && $_POST['org_package_name'] != $_POST['package_name']) {
                $ret = $model->check_if_package_exists($_POST['package_name']);
                if (!$ret) {
                    // 模拟器包名不存在
                    echo 2;
                    exit;
                }
            }
            // 查看模拟器包名是否已添加过
            if ($_POST['package_name'] && $_POST['org_package_name'] != $_POST['package_name']) {
                $ret = $model->check_if_package_already_added($_POST['package_name']);
                if ($ret) {
                    // 模拟器包名已添加过
                    echo 3;
                    exit;
                }
            }
            
            // 获得编辑信息，以方便写数据库
            $where_arr = array('id' => $_POST['id']);
            $table_name = 'sj_game_simulator_type';
            $column_arr = array(
                'name' => $_POST['name'],
                'package' => $_POST['package_name'],
            );
            $log_all_need = $this->logcheck($where_arr, $table_name, $column_arr, $model);
            $msg = "模拟器--模拟器APK配置：编辑了id为{$_POST['id']}的记录：";
            $msg .= $log_all_need;
            
            // 更新数据库
            $ret = $model->update_simulator_type($_POST['id'], $_POST['name'], $_POST['package_name']);
            if ($ret >= 0) {
                // 编辑成功
                if ($ret > 0) {
                    $this->writelog($msg,'sj_game_simulator_type',$_POST['id'],__ACTION__ ,'','edit');
                }
                echo 0;
                exit;
            } else {
                // 编辑失败
                echo 3;
                exit;
            }
        }
        $this->display('edit_apk_config');
    }
}

?>