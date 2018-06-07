<?php

/**
 * Desc:   安智游戏后台--新用户首页推荐
 * @author Ye Xiaola<yexiaola@anzhi.com>
 * 2013-10-31
*/

class GameHomePageRecommendationAction extends CommonAction {
    
    //（首页）推荐列表
    function home_page_list() {
        $model = D('Sj.GameHomePageRecommendation');        
        $list = $model->get_home_page_list();
        $this->assign('list', $list);
        if ($list) {
            $this->assign('has_data', 1);
            $this->assign('starttime', $list[0]['starttime']);
            $this->assign('endtime', $list[0]['endtime']);
        } else {
            $this->assign('has_data', 0);
        }
        $this->display('home_page_list');
    }
    
    // 备选库
    function backup_list() {
        $model = D('Sj.GameHomePageRecommendation');        
        $ret = $model->get_backup_list();
        $list = $ret['list'];
        $show = $ret['show'];
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display('backup_list');
    }
    
    // 检查是否可以添加首页推荐软件，如果已存在三个有效的软件，则返回-1，否则返回0
    function check_before_add() {
        $model = D('Sj.GameHomePageRecommendation');
        $list = $model->get_home_page_list();
        if ($list)
            $this->ajaxReturn(0,"", -1);
        $this->ajaxReturn(0,"", 0);
    }
    
    function check_package() {
        if ($_POST['package_name']) {
            $model = D('Sj.GameHomePageRecommendation');
            $ret = $model->check_if_package_exists($_POST['package_name']);
            if (!$ret) {
                // 包名不存在
                echo 1;
                exit;
            }
            $ret = $model->check_if_package_already_added($_POST['package_name']);
            if ($ret) {
                // 包名已添加
                echo 2;
                exit;
            }
        }
        echo 0;
        exit;
    }
    
    // 添加首页推荐软件（必须三个一起添加）、备选库软件
    function add() {
        if ($_GET) {
            if ($_GET['add_config'] == 1) {
                $this->assign('add_config', 1);
            } else {
                $this->assign('add_config', 2);
            }
        }
        if ($_POST) {
            $model = D('Sj.GameHomePageRecommendation');
            if ($_POST['config'] == 1) {
                // 当config为1时，表示添加三个推荐软件
                // 先检查是否可以添加
                $exist = $model->get_home_page_list();
                if ($exist) {
                    $this->error('有效期期已添加一组数据，您可以选择修改软件和时间');
                }
                // 开始添加
                $this->assign("jumpUrl","__URL__/home_page_list");
                $data = array();
                $data['package'] = $_POST['package1'];
                $data['note'] = $_POST['note1'];
                $data['starttime'] = strtotime($_POST['starttime']);
                $data['endtime'] = strtotime($_POST['endtime']) + 86399;
                $data['type'] = 1;
                $ret1 = $model->add_record($data);
                if (!$ret1) {
                    $this->error('添加失败');
                }
                $data['package'] = $_POST['package2'];
                $data['note'] = $_POST['note2'];
                $data['starttime'] = strtotime($_POST['starttime']);
                $data['endtime'] = strtotime($_POST['endtime']) + 86399;
                $data['type'] = 1;
                $ret2 = $model->add_record($data);
                if (!$ret2) {
                    // 删除前一个添加成功的
                    $model->delete_record($ret1);
                    $this->error('添加失败');
                }
                $data['package'] = $_POST['package3'];
                $data['note'] = $_POST['note3'];
                $data['starttime'] = strtotime($_POST['starttime']);
                $data['endtime'] = strtotime($_POST['endtime']) + 86399;
                $data['type'] = 1;
                $ret3 = $model->add_record($data);                
                if (!$ret3) {
                    // 删除前二个添加成功的
                    $model->delete_record($ret1);
                    $model->delete_record($ret2);
                    $this->error('添加失败');
                }
                $this->writelog("运营位管理--游戏首页弹窗推荐--推荐列表：新增了id为：{$ret1}、{$ret2}、{$ret3}的记录", 'sj_game_home_recommendation',"{$ret1},{$ret2},{$ret3}",__ACTION__ ,'','add');
                $this->success('添加成功');
            } else if ($_POST['config'] == 2) {
                // 当config为2时，表示添加备选库软件
                $this->assign("jumpUrl","__URL__/backup_list");
                $data = array();
                $data['package'] = $_POST['package_backup'];
                $data['note'] = $_POST['note_backup'];
                $data['type'] = 2;
                $ret = $model->add_record($data);
                if ($ret) {
                    $this->writelog("运营位管理--游戏首页弹窗推荐--备选库软件：新增了id为：{$ret}的记录", 'sj_game_home_recommendation',$ret,__ACTION__ ,'','add');
                    $this->success('添加成功');
                } else {
                    $this->error('添加失败');
                }
            }
        }
        $this->display('add');
    }
    
    // 编辑首页推荐的三个软件
    function edit_home_page() {
        $model = D('Sj.GameHomePageRecommendation');  
        if ($_POST) {
            // 获得编辑信息，以方便写数据库
            $table_name = 'sj_game_home_recommendation';
            $where_arr1 = array(
                'id' => $_POST['id1'],
            );
            $where_arr2 = array(
                'id' => $_POST['id2'],
            );
            $where_arr3 = array(
                'id' => $_POST['id3'],
            );
            $column_arr1 = array(
                'package' => $_POST['package1'],
                'note' => $_POST['note1'],
                'starttime' => strtotime($_POST['starttime']),
                'endtime' => strtotime($_POST['endtime']) + 86399,
            );
            $column_arr2 = array(
                'package' => $_POST['package2'],
                'note' => $_POST['note2'],
                'starttime' => strtotime($_POST['starttime']),
                'endtime' => strtotime($_POST['endtime']) + 86399,
            );
            $column_arr3 = array(
                'package' => $_POST['package3'],
                'note' => $_POST['note3'],
                'starttime' => strtotime($_POST['starttime']),
                'endtime' => strtotime($_POST['endtime']) + 86399,
            );
            $log_all_need1 = $this->logcheck($where_arr1, $table_name, $column_arr1, $model);
            $log_all_need2 = $this->logcheck($where_arr2, $table_name, $column_arr2, $model);
            $log_all_need3 = $this->logcheck($where_arr3, $table_name, $column_arr3, $model);
            $msg1 = "运营位管理--游戏首页弹窗推荐--推荐列表：编辑了id为{$_POST['id1']}的记录：";
            $msg2 = "运营位管理--游戏首页弹窗推荐--推荐列表：编辑了id为{$_POST['id2']}的记录：";
            $msg3 = "运营位管理--游戏首页弹窗推荐--推荐列表：编辑了id为{$_POST['id3']}的记录：";
            $msg1 .= $log_all_need1;
            $msg2 .= $log_all_need2;
            $msg3 .= $log_all_need3;
            // 准备写库
            $data = array();
            $where = array();
            $where['id'] = array('EQ', $_POST['id1']);
            $data['package'] = $_POST['package1'];
            $data['note'] = $_POST['note1'];
            $data['starttime'] = strtotime($_POST['starttime']);
            $data['endtime'] = strtotime($_POST['endtime']) + 86399;
            $ret1 = $model->edit_record($where, $data);
            if ($ret1 > 0) {
                $this->writelog($msg1, 'sj_game_home_recommendation',$_POST['id1'],__ACTION__ ,'','edit');
            }
            $where['id'] = array('EQ', $_POST['id2']);
            $data['package'] = $_POST['package2'];
            $data['note'] = $_POST['note2'];
            $data['starttime'] = strtotime($_POST['starttime']);
            $data['endtime'] = strtotime($_POST['endtime']) + 86399;
            $ret2 = $model->edit_record($where, $data);
            if ($ret2 > 0) {
                $this->writelog($msg2, 'sj_game_home_recommendation',$_POST['id2'],__ACTION__ ,'','edit');
            }
            $where['id'] = array('EQ', $_POST['id3']);
            $data['package'] = $_POST['package3'];
            $data['note'] = $_POST['note3'];
            $data['starttime'] = strtotime($_POST['starttime']);
            $data['endtime'] = strtotime($_POST['endtime']) + 86399;
            $ret3 = $model->edit_record($where, $data);
            if ($ret3 > 0) {
                $this->writelog($msg3, 'sj_game_home_recommendation',$_POST['id3'],__ACTION__ ,'','edit');
            }
            $this->assign("jumpUrl","__URL__/home_page_list");
            //$this->writelog("运营位管理--游戏首页弹窗推荐--推荐列表：编辑了id为：{$_POST['id1']}、{$_POST['id2']}、{$_POST['id3']}的记录");
            $this->success('编辑成功');
        } else {     
            $list = $model->get_home_page_list();
            $starttime_str = date('Y-m-d', $list[0]['starttime']);
            $endtime_str = date('Y-m-d', $list[0]['endtime']);
            $this->assign('list', $list);
            $this->assign('starttime_str', $starttime_str);
            $this->assign('endtime_str', $endtime_str);
        }
        $this->display('edit_home_page');
    }
    
    // 编辑备选库的软件
    function edit_backup() {
        $model = D('Sj.GameHomePageRecommendation');  
        if ($_GET) {
            $list = $model->get_record($_GET['id']);
            $this->assign('list', $list[0]);
        } else if ($_POST) {
            // 获得编辑信息，以方便写数据库
            $where_arr = array(
                'id' => $_POST['id'],
            );
            $table_name = 'sj_game_home_recommendation';
            $column_arr = array(
                'package' => $_POST['package_backup'],
                'note' => $_POST['note_backup'],
            );
            $log_all_need = $this->logcheck($where_arr, $table_name, $column_arr, $model);
            $msg = "运营位管理--游戏首页弹窗推荐--备选库软件：编辑了id为{$_POST['id']}的记录：";
            $msg .= $log_all_need;
            // 准备写库
            $where = array();
            $data = array();
            $where['id'] = $_POST['id'];
            $data['package'] = $_POST['package_backup'];
            $data['note'] = $_POST['note_backup'];
            $ret = $model->edit_record($where, $data);
            if ($ret === false) {
                $this->error('编辑失败');
            }
            if ($ret > 0) {
                $this->writelog($msg, 'sj_game_home_recommendation',$_POST['id'],__ACTION__ ,'','edit');
            }
            $this->assign("jumpUrl","__URL__/backup_list");
            $this->success('编辑成功');
        }
        $this->display('edit_backup');
    }
    
    // 删除首页推荐的三个软件
    function delete_home_page_records() {
        $model = D('Sj.GameHomePageRecommendation');
        $where = array();
        $where['status'] = array('EQ', 1);
        $where['type'] = array('EQ', 1);
        $where['endtime'] = array('GT', time());
        $info = $model->where($where)->field('id')->findAll();
        $ids = '';
        if($info){
            foreach($info as $k=>$v){
                $ids .= $v['id'].',';
            }
            $ids = substr($ids,0,-1);
        }
        $ret = $model->delete_home_page_records();
        if ($ret == 3) {
            $this->writelog("运营位管理--游戏首页弹窗推荐--推荐列表：删除了三个软件,id为{$ids}", 'sj_game_home_recommendation',$ids,__ACTION__ ,'','del');
            $this->ajaxReturn(0, "删除成功！", 0);
        } else {
            $this->ajaxReturn(0, "删除失败！", -1);
        }
    }
    
    // 删除备选库的软件
    function delete_backup_record() {
        if ($_POST) {
            $model = D('Sj.GameHomePageRecommendation');
            $model->delete_record($_POST['id']);
            $this->writelog("运营位管理--游戏首页弹窗推荐--备选库软件：删除了id为：{$_POST['id']}的记录", 'sj_game_home_recommendation',$_POST['id'],__ACTION__ ,'','del');
            $this->ajaxReturn(0, "", 0);
        }
    }
}
?>