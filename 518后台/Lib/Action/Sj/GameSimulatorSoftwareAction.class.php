<?php

/**
 * Desc:   安智游戏后台--模拟器
 * @author Ye Xiaola<yexiaola@anzhi.com>
 * 2013-10-31
*/

class GameSimulatorSoftwareAction extends CommonAction {

    // 得到模拟器类型配置信息（哪些应显示、不显示）
    private function get_all_status() {
        $simulator_type_model = D("Sj.GameSimulatorType");
        return $simulator_type_model->get_all_status();
    }
    // 任天堂列表
    public function ren_tian_tang() {
        $model = D("Sj.GameSimulatorSoftware");
        $ret = $model->get_list(1);
        $list = $ret['list'];
        $last_rank = $ret['last_rank'];
        $show = $ret['show'];
        $all_status = $this->get_all_status();
        $this->assign('all_status', $all_status);
        $this->assign('list', $list);
        $this->assign('last_rank', $last_rank);
        $this->assign('page', $show);
        $this->assign("apkurl",IMGATT_HOST);
        $this->display('ren_tian_tang');
    }
    // GBA列表
    public function gba() {
        $model = D("Sj.GameSimulatorSoftware");
        $ret = $model->get_list(2);
        $list = $ret['list'];
        $last_rank = $ret['last_rank'];
        $show = $ret['show'];
        
        $all_status = $this->get_all_status();
        $this->assign('all_status', $all_status);
        $this->assign('list', $list);
        $this->assign('last_rank', $last_rank);
        $this->assign('page', $show);
        $this->assign("apkurl",IMGATT_HOST);
        $this->display('gba');
    }
    // 街机列表
    public function jie_ji() {
        $model = D("Sj.GameSimulatorSoftware");
        $ret = $model->get_list(3);
        $list = $ret['list'];
        $last_rank = $ret['last_rank'];
        $show = $ret['show'];
        
        $all_status = $this->get_all_status();
        $this->assign('all_status', $all_status);
        $this->assign('list', $list);
        $this->assign('last_rank', $last_rank);
        $this->assign('page', $show);
        $this->assign("apkurl",IMGATT_HOST);
        $this->display('jie_ji');
    }
    // 超级任天堂列表
    public function super_ren_tian_tang() {
        $model = D("Sj.GameSimulatorSoftware");
        $ret = $model->get_list(4);
        $list = $ret['list'];
        $last_rank = $ret['last_rank'];
        $show = $ret['show'];
        
        $all_status = $this->get_all_status();
        $this->assign('all_status', $all_status);
        $this->assign('list', $list);
        $this->assign('last_rank', $last_rank);
        $this->assign('page', $show);
        $this->assign("apkurl",IMGATT_HOST);
        $this->display('super_ren_tian_tang');
    }
    // 世嘉列表
    public function shi_jia() {
        $model = D("Sj.GameSimulatorSoftware");
        $ret = $model->get_list(5);
        $list = $ret['list'];
        $last_rank = $ret['last_rank'];
        $show = $ret['show'];
        
        $all_status = $this->get_all_status();
        $this->assign('all_status', $all_status);
        $this->assign('list', $list);
        $this->assign('last_rank', $last_rank);
        $this->assign('page', $show);
        $this->assign("apkurl",IMGATT_HOST);
        $this->display('shi_jia');
    }
    // 检查软件名称是否已添加过，添加过则不可用
    public function check_software_name() {
        if ($_POST) {
            $model = D("Sj.GameSimulatorSoftware");
            $exsit = $model->check_software_name($_POST['software_name']);
            if ($exsit) {
                $this->ajaxReturn(0,'软件名称已存在', -1);
            }
            $this->ajaxReturn(0,'软件名称可用', 0);
        }
    }
    // 检查rom包在当天是否已上传过
    public function check_if_rom_exists() {
        if ($_POST) {
            $pre_path = UPLOAD_PATH;
            $rom_mid_path = '/simulator_software/rom/' . date("Ym/d/");
            $rom_pre_path = $pre_path . $rom_mid_path;
            $rom_path = $rom_pre_path . $_POST['rom_upload_file'];
            if (file_exists($rom_path)) {
                $this->ajaxReturn(0,'rom已存在', -1);
            }
        }
        $this->ajaxReturn(0,'', 0);
    }
    // 检查icon在当天是否已上传过
    public function check_if_icon_exists() {
        if ($_POST) {
            $pre_path = UPLOAD_PATH;
            $icon_mid_path = '/simulator_software/icon/' . date("Ym/d/");
            $icon_pre_path = $pre_path . $icon_mid_path;
            $icon_path = $icon_pre_path . $_POST['icon_upload_file'];
            if (file_exists($icon_path)) {
                $this->ajaxReturn(0,'icon已存在', -1);
            }
        }
        $this->ajaxReturn(0,'', 0);
    }
    // 上移
    public function up_the_rank() {
        if ($_GET) {
            $table = 'sj_game_simulator';
            $id = $_GET['id'];
            $type = $_GET['type'];
            $this->change_rank('up',$table,$id,$type);
            $this->writelog("模拟器--模拟器：上移了id为{$id}的记录",'sj_game_simulator',$id,__ACTION__ ,'','edit');
            $this->success("上移成功！");
        }
    }
    // 下移
    public function down_the_rank() {
        if ($_GET) {
            $table = 'sj_game_simulator';
            $id = $_GET['id'];
            $type = $_GET['type'];
            $this->change_rank('down',$table,$id,$type);
            $this->writelog("模拟器--模拟器：下移了id为{$id}的记录",'sj_game_simulator',$id,__ACTION__ ,'','edit');
            $this->success("下移成功！");
        }
    }
    // 置顶
    public function top_the_rank() {
        if ($_GET) {
            $table = 'sj_game_simulator';
            $id = $_GET['id'];
            $type = $_GET['type'];
            $this->top_repick($table,$id,$type);
            $this->writelog("模拟器--模拟器：置顶了id为{$id}的记录",'sj_game_simulator',$id,__ACTION__ ,'','edit');
            $this->success("置顶成功！");
        }
    }
    // 添加模拟器软件
    public function add_simulator_software() {
        if ($_GET) {
            $this->assign('from', $_GET['from']);
        } else if ($_POST) {
            $pre_path = UPLOAD_PATH;
            $model = D('Sj.GameSimulatorSoftware');
            
            $data = array();
            if (isset($_POST['type'])) {
                $data['type'] = $_POST['type'];
            }
            if (isset($_POST['software_name'])) {
                $data['software_name'] = $_POST['software_name'];
            }
            if (isset($_POST['star'])) {
                $data['star'] = $_POST['star'];
            }
            if (isset($_POST['note'])) {
                $data['note'] = $_POST['note'];
            }
            // 获得现在的时间戳作为文件名前缀
            $now_time_stamp = time() . '_';
            if ($_FILES['rom_upload_file']['name']) {
                // 将上传rom文件复制到指定位置
                $rom_mid_path = '/simulator_software/rom/' . date("Ym/d/");
                $rom_new_name = $now_time_stamp . $_FILES['rom_upload_file']['name'];
                $data['rom_url'] = $rom_mid_path . $rom_new_name;
                $rom_pre_path = $pre_path . $rom_mid_path;
                $rom_path = $rom_pre_path . $rom_new_name;
                if (!is_dir($rom_pre_path)) {
                    // 递归创建目录
                    mkdir($rom_pre_path, 0777, true);
                }
                move_uploaded_file($_FILES['rom_upload_file']['tmp_name'], $rom_path);
                // 生成rom文件的md5值
                $data['rom_md5'] = md5_file($rom_path);
                // 获得rom文件的大小
                $data['rom_size'] = filesize($rom_path);
                include_once SERVER_ROOT. '/tools/functions.php';
                splitfile($rom_path, $rom_pre_path);
                go_make_links($rom_path);
            }
            if ($_FILES['icon_upload_file']['name']) {
                // 将上传的icon上传到指定位置
                $icon_mid_path = '/simulator_software/icon/' . date("Ym/d/");
                $icon_new_new = $now_time_stamp . $_FILES['icon_upload_file']['name'];
                $data['icon_url'] = $icon_mid_path . $icon_new_new;
                $icon_pre_path = $pre_path . $icon_mid_path;
                $icon_path = $icon_pre_path . $icon_new_new;
                if (!is_dir($icon_pre_path)) {
                    // 递归创建目录
                    mkdir($icon_pre_path, 0777, true);
                }
                move_uploaded_file($_FILES['icon_upload_file']['tmp_name'], $icon_path);
            }
            $ret = $model->add_software($data);
            $page_arr = array("ren_tian_tang", "gba", "jie_ji", "super_ren_tian_tang", "shi_jia");
            $from = $_POST['from'];
            $page = $page_arr[$from];
            $this->assign("jumpUrl","/index.php/Sj/GameSimulatorSoftware/$page");
            if ($ret) {
                $this->writelog("模拟器--模拟器：添加了id为{$ret}的记录",'sj_game_simulator',$ret,__ACTION__ ,'','add');
                $this->success("添加成功！");
            } else {
                if ($_FILES['rom_upload_file']['name']) {
                    // 添加失败，需删除刚上传的rom
                    $rom_path = $pre_path . $data['rom_url'];
                    unlink($rom_path);
                }
                if ($_FILES['icon_upload_file']['name']) {
                    // 添加失败，需删除刚上传的icon
                    $icon_path = $pre_path . $data['icon_url'];
                    unlink($icon_path);
                }
                $this->error("添加失败！");
            }
        }
        $all_status = $this->get_all_status();
        $this->assign('all_status', $all_status);
        $this->display("add_simulator_software");
    }
    // 编辑模拟器软件
    public function edit_simulator_software() {
        $model = D('Sj.GameSimulatorSoftware');
        if ($_GET) {
            $list = $model->get_record($_GET['id']);
            $record = $list[0];
            $this->assign('list', $record);
            $this->assign('from', $_GET['from']);
        } else if ($_POST) {
            $list = $model->get_record($_POST['id']);
            $record = $list[0];
            $pre_path = UPLOAD_PATH;
            $data = array();
            // 存储写日志的编辑信息
            $column_arr = array();
            /////////////////////
            if (isset($_POST['type'])) {
                $column_arr['type'] = $data['type'] = $_POST['type'];
            }
            if (isset($_POST['software_name'])) {
                $column_arr['software_name'] = $data['software_name'] = $_POST['software_name'];
            }
            if (isset($_POST['star'])) {
                $column_arr['star'] = $data['star'] = $_POST['star'];
            }
            if (isset($_POST['note'])) {
                $column_arr['note'] = $data['note'] = $_POST['note'];
            }
            // 获得现在的时间戳作为文件名前缀
            $now_time_stamp = time() . '_';
            if ($_FILES['rom_upload_file']['name']) {
                // 将上传rom文件复制到指定位置
                $rom_mid_path = '/simulator_software/rom/' . date("Ym/d/");
                $rom_new_name = $now_time_stamp . $_FILES['rom_upload_file']['name'];
                $column_arr['rom_url'] = $data['rom_url'] = $rom_mid_path . $rom_new_name;
                $rom_pre_path = $pre_path . $rom_mid_path;
                $rom_path = $rom_pre_path . $rom_new_name;
                if (!is_dir($rom_pre_path)) {
                    // 递归创建目录
                    mkdir($rom_pre_path, 0777, true);
                }
                move_uploaded_file($_FILES['rom_upload_file']['tmp_name'], $rom_path);
                // 生成rom文件的md5值
                $data['rom_md5'] = md5_file($rom_path);
                // 获得rom文件的大小
                $data['rom_size'] = filesize($rom_path);
                include_once SERVER_ROOT. '/tools/functions.php';
                splitfile($rom_path, $rom_pre_path);
                go_make_links($rom_path);
            }
            if ($_FILES['icon_upload_file']['name']) {
                // 将上传的icon上传到指定位置
                $icon_mid_path = '/simulator_software/icon/' . date("Ym/d/");
                $icon_new_new = $now_time_stamp . $_FILES['icon_upload_file']['name'];
                $column_arr['icon_url'] = $data['icon_url'] = $icon_mid_path . $icon_new_new;
                $icon_pre_path = $pre_path . $icon_mid_path;
                $icon_path = $icon_pre_path . $icon_new_new;
                if (!is_dir($icon_pre_path)) {
                    // 递归创建目录
                    mkdir($icon_pre_path, 0777, true);
                }
                move_uploaded_file($_FILES['icon_upload_file']['tmp_name'], $icon_path);
            }
            
            // 获得编辑信息，以方便写数据库
            $where_arr = array('id' => $_POST['id']);
            $table_name = 'sj_game_simulator';
            $log_all_need = $this->logcheck($where_arr, $table_name, $column_arr, $model);
            $msg = "模拟器--模拟器：编辑了id为{$_POST['id']}的记录：";
            $msg .= $log_all_need;
            
            $ret = $model->save_software($_POST['id'], $data);
			print_r($ret);die;
            $page_arr = array("ren_tian_tang", "gba", "jie_ji", "super_ren_tian_tang", "shi_jia");
            $from = $_POST['from'];
            $page = $page_arr[$from];
            $this->assign("jumpUrl","/index.php/Sj/GameSimulatorSoftware/$page");
            if ($ret >= 0) {
                if ($_FILES['rom_upload_file']['name']) {
                    // 删除原来的rom
                    $rom_path = $pre_path . $record['rom_url'];
                    unlink($rom_path);
                }
                if ($_FILES['icon_upload_file']['name']) {
                    // 删除原来的icon
                    $icon_path = $pre_path . $record['icon_url'];
                    unlink($icon_path);
                }
                if ($ret > 0)
                    $this->writelog($msg,'sj_game_simulator',$_POST['id'],__ACTION__ ,'','edit');
                $this->success("编辑成功！");
            } else {
                $this->error("编辑失败！");
            }
        }
        $all_status = $this->get_all_status();
        $this->assign('all_status', $all_status);
        $this->display('edit_simulator_software');
    }
    // 删除模拟器软件（将status置为0）
    public function delete_simulator_software() {
        if ($_GET) {
            $model = D('Sj.GameSimulatorSoftware');
            $ret = $model->delete_software($_GET['id']);
            if ($ret) {
                $this->writelog("模拟器--模拟器：删除了id为{$_GET['id']}的记录",'sj_game_simulator',$_GET['id'],__ACTION__ ,'','del');
                $this->success("删除成功！");
            } else {
                $this->success("删除失败！");
            }
        }
    }

	public function manage_ren(){
		$User = M('game_simulator');
		$id = $_GET['id'];
		$status = $_GET['status'];
		if($status == 2){
			$User->table('sj_game_simulator')->where("id = $id")->setField('status',1); 
			$this->writelog("模拟器--模拟器：恢复了id为{$_GET['id']}的记录",'sj_game_simulator',$_GET['id'],__ACTION__ ,'','edit');
			$this -> success("成功");
        }else if($status == 1){
			$User->table('sj_game_simulator')->where("id = $id")->setField('status',2); 
			$this->writelog("模拟器--模拟器：禁用了id为{$_GET['id']}的记录",'sj_game_simulator',$_GET['id'],__ACTION__ ,'','edit');
			$this -> success("成功");
        }
    }

    public function manage_gba(){
		$User = M('game_simulator');
		$id = $_GET['id'];
		$status = $_GET['status'];
		if($status == 2){
			$User->table('sj_game_simulator')->where("id = $id")->setField('status',1); 
			$this->writelog("模拟器--模拟器：恢复了id为{$_GET['id']}的记录",'sj_game_simulator',$_GET['id'],__ACTION__ ,'','edit');
			$this -> success("成功");
        }else if($status == 1){
			$User->table('sj_game_simulator')->where("id = $id")->setField('status',2); 
			$this->writelog("模拟器--模拟器：禁用了id为{$_GET['id']}的记录",'sj_game_simulator',$_GET['id'],__ACTION__ ,'','edit');
			$this -> success("成功");
        }
    }

	public function manage_jie_ji(){
		$User = M('game_simulator');
		$id = $_GET['id'];
		$status = $_GET['status'];
		if($status == 2){
			$User->table('sj_game_simulator')->where("id = $id")->setField('status',1); 
			$this->writelog("模拟器--模拟器：恢复了id为{$_GET['id']}的记录",'sj_game_simulator',$_GET['id'],__ACTION__ ,'','edit');
			$this -> success("成功");
        }else if($status == 1){
			$User->table('sj_game_simulator')->where("id = $id")->setField('status',2); 
			$this->writelog("模拟器--模拟器：禁用了id为{$_GET['id']}的记录",'sj_game_simulator',$_GET['id'],__ACTION__ ,'','edit');
			$this -> success("成功");
        }
    }

	public function manage_super_ren_tian_tang(){
		$User = M('game_simulator');
		$id = $_GET['id'];
		$status = $_GET['status'];
		if($status == 2){
			$User->table('sj_game_simulator')->where("id = $id")->setField('status',1);
			$this->writelog("模拟器--模拟器：恢复了id为{$_GET['id']}的记录",'sj_game_simulator',$_GET['id'],__ACTION__ ,'','edit');
			$this -> success("成功");
        }else if($status == 1){
			$User->table('sj_game_simulator')->where("id = $id")->setField('status',2);
			$this->writelog("模拟器--模拟器：禁用了id为{$_GET['id']}的记录",'sj_game_simulator',$_GET['id'],__ACTION__ ,'','edit');
			$this -> success("成功");
        }
    }

	public function manage_shi_jia(){
		$User = M('game_simulator');
		$id = $_GET['id'];
		$status = $_GET['status'];
		if($status == 2){
			$User->table('sj_game_simulator')->where("id = $id")->setField('status',1);
			$this->writelog("模拟器--模拟器：恢复了id为{$_GET['id']}的记录",'sj_game_simulator',$_GET['id'],__ACTION__ ,'','edit');
			$this -> success("成功");
        }else if($status == 1){
			$User->table('sj_game_simulator')->where("id = $id")->setField('status',2);
			$this->writelog("模拟器--模拟器：禁用了id为{$_GET['id']}的记录",'sj_game_simulator',$_GET['id'],__ACTION__ ,'','edit');
			$this -> success("成功");
        }
    }
}
?>