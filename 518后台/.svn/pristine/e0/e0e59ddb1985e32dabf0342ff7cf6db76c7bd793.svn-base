<?php
/**
 * Desc:   报表产品排期申请
 * @author Ye Xiaola<yexiaola@anzhi.com>
 * 
*/

class ScheduleAction extends CommonAction {
    private $yx_schedule_table;
    
    private $correct_title_arr = array('申请时间', '申请类型', '负责人', '产品名称', '包名', '包大小', '评测级别', '游戏类型',
            '申请资源位', '类别', '高低配', '高低配-软件名称', '高低配-软件包名', '活动名称', '开始时间', '结束时间', '活动天数', '活动内容',
            '上周非活动日均流水', '上周日均注册数', '预计注册数', '预计活动流水', '备注');
    
    public function __construct() {
        parent::__construct();
        $this->yx_schedule_table = D('sendNum.Schedule');
    }
    
    public function schedule_list() {
        //根据post得到查询条件
        $res = $this->yx_schedule_table->getlist($_GET, $_POST);
        
        $list = $res['list'];
        foreach($list as $k=>$v) {
            if(strlen($v['activity_content'])>18) {
                $list[$k]['activity_content'] = $this->chgtitle($v['activity_content']);
            }
            if (strlen($v['remark'])>18) {
                $list[$k]['remark'] = $this->chgtitle($v['remark']);
            }
        }
        $show = $res['show'];
        $sqlparam = $res['sqlparam'];
        
        $this->assign("product_name", $_REQUEST['product_name']);
        $this->assign("person_in_charge", $_REQUEST['person_in_charge']);
        $this->assign("category", $_REQUEST['category']);
        $this->assign("activity_name", $_REQUEST['activity_name']);
        $this->assign("package_name", $_REQUEST['package_name']);
        $this->assign("type", $_REQUEST['type']);
        
        if($_GET['begintime']) {
            $this->assign("begintime", date('Y-m-d H:i:s',$_REQUEST['begintime']));
        } else {
            $this->assign("begintime", $_REQUEST['begintime']);
        }

        if($_GET['endtime']) {
            $this->assign("endtime", date('Y-m-d H:i:s',$_REQUEST['endtime']));
        } else {
            $this->assign("endtime", $_REQUEST['endtime']);
        }
        
        if($_GET['start_time_begin']) {
            $this->assign("start_time_begin", date('Y-m-d H:i:s',$_REQUEST['start_time_begin']));
        } else {
            $this->assign("start_time_begin", $_REQUEST['start_time_begin']);
        }

        if($_GET['start_time_end']) {
            $this->assign("start_time_end", date('Y-m-d H:i:s',$_REQUEST['start_time_end']));
        } else {
            $this->assign("start_time_end", $_REQUEST['start_time_end']);
        }
        
        if($_GET['down']) {
            $allist = $res['allist'];
            header("Content-type:application/vnd.ms-excel");
            header("content-Disposition:filename=schedule.csv");

            $desc ="申请时间,类型,负责人,产品名称,包名,包大小,评测级别,游戏类型,申请资源位,类别,高低配,高低配-软件名称,高低配-软件包名,活动名称,开始时间,结束时间,活动天数,活动内容,上周非活动日均收入,上周日均注册数,预计注册数,预计活动收入,备注\r\n";
			$desc = iconv('utf-8','gbk',$desc);
            foreach($allist as $v) {
                $type2 = "活动";
                $v['type'] == 1 ? $type = "常规" : $type = "活动";
                
                // 将活动名称里可能引起格式错误的字符替换掉
                $activity_name = trim($v['activity_name']);
                $activity_name = str_replace(",","，",$activity_name);
                
                // 将活动内容里可能引起格式错误的字符替换掉
                $activity_content= trim($v['activity_content']);
                $activity_content = str_replace("\n"," ",$activity_content);
                $activity_content = str_replace("\r"," ",$activity_content);
                $activity_content = str_replace(",","，",$activity_content);
                
                // 将活动名称里可能引起格式错误的字符替换掉
                $application_resource_position = trim($v['application_resource_position']);
                $application_resource_position = str_replace(",","，",$application_resource_position);
                
                // 将备注里可能引起格式错误的字符替换掉
                $remark= trim($v['remark']);
                $remark = str_replace("\n"," ",$remark);
                $remark = str_replace("\r"," ",$remark);
                $remark = str_replace(",","，",$remark);
                
                $category = "";
                if ($v['category'] == 1) {
                    $category = "网游";
                } else {
                    $category = "单机";
                }
                
                $high_low_config = "无";
                if ($v['high_low_config'] == 1) {
                    $high_low_config = "高配";
                } else if ($v['high_low_config'] == 2) {
                    $high_low_config = "低配";
                }
                
                $str = date('Y-m-d H:i:s',$v['application_time']).",".$type.",".$v['person_in_charge'].",".$v['product_name'].",".$v['package_name']
                    .",".$v['package_size'].",".$v['reviewlevel'].",".$v['game_type'].",".$application_resource_position
                    .",".$category.",".$high_low_config.",".$v['config_softname'].",".$v['config_package'].",\"".$activity_name."\",".date('Y-m-d H:i:s',$v['start_time']).",".date('Y-m-d H:i:s',$v['end_time'])
                    .",".$v['activity_days'].",\"".$activity_content."\",\"".$v['avg_income_lweek_inactive']."\",\"".$v['avg_regist_number_lweek']
                    ."\",\"".$v['expected_regist_number']."\",\"".$v['expected_income']."\",".$remark."\r\n";

				$desc .= iconv('utf-8','gbk',$str);
            }
            
            echo $desc;
            exit(0);
        }
        
        if($_POST){
            $this->redirect('/Schedule/schedule_list?'.$sqlparam);
        }
        $this->assign('list',$list);//赋值数据集
        $this->assign('page',$show);//赋值分页输出
        $this->assign("sqlparam", $sqlparam);
        $this->display('schedule_list');//输出模板
    }
    
    //截取字符串
    function chgtitle($title){
        $length=9;
        $encoding='utf8';
        if(mb_strlen($title,$encoding)>$length){
            $title=mb_substr($title,0,$length,$encoding).'...';
        }
        return $title;
    }
    
    function show_full_content() {
        $id = $_GET['id'];
        $rs = $this->yx_schedule_table->getContent($id);
        if ($_GET['content'] == 1) {
            $content = $rs['activity_content'];
        } else {
            $content = $rs['remark'];
        }
        $this->assign("content", $content);
        $this->display('show_full_content');
    }
    
    function show_high_low_config_info() {
        $id = $_GET['id'];
        $rs = $this->yx_schedule_table->getContent($id);
        $this->assign('config_softname', $rs['config_softname']);
        $this->assign('config_package', $rs['config_package']);
        $this->display('show_high_low_config_info');
    }
    
    function show_remark() {
        $id = $_GET['id'];
        $rs = $this->yx_schedule_table->getActivityContent($id);
        $this->assign("activity_content" ,$rs['activity_content']);
        $this->display('show_activity_content');
    }
    
    function check_person_in_charge() {
        if ($_POST) {
            $map = array();
            $map['admin_user_name'] = array("EQ", $_POST['person_in_charge']);
            $model = M();
            $exsit = $model->table('sj_admin_users')->where($map)->select();
            if (!$exsit) {
                $this->ajaxReturn(0,'用户名不存在', -1);
            }
            $this->ajaxReturn(0,'用户名存在', 0);
        }
    }
    
    //添加或编辑接口
    public function modify() {
        if($_GET) {
            //获取opt，如果opt为1，表示添加，如果opt为2，表示编辑，如果opt为3，表示复制添加
            $opt = $_GET['opt'];
            $this->assign('opt', $opt);
            //opt为2表示编辑接口，传相应的信息到编辑页面
            if ($opt == 2 || $opt == 3) {
                $id = $_GET['id'];
                $arr = $this->yx_schedule_table->getTheRecord($id);
                $record = $arr[0];
                $this->assign('record', $record);
            }
        }
        if ($_POST) {
            $opt = $_POST['opt'];
            if ($opt == 3) {
                unset($_POST['id']);
            }
            $arr_add = array();
            foreach($_POST as $key => $value) {
                if ($key != 'opt') {
                    $arr_add[$key] = trim($value);
                }
            }
            if ($opt == 2) {
                // 获得编辑信息，以方便写数据库
                $where_arr = array('id' => $_POST['id']);
                $table_name = 'yx_schedule';
                // 如果高低配选无，unset掉config_softname和config_package
                if ($arr_add['high_low_config'] == 0) {
                    unset($arr_add['config_softname']);
                    unset($arr_add['config_package']);
                }
                $column_arr = array(
                    'application_time' => strtotime($arr_add['application_time']),
                    'person_in_charge' => $arr_add['person_in_charge'],
                    'product_name' => $arr_add['product_name'],
                    'package_name' => $arr_add['package_name'],
                    'package_size' => $arr_add['package_size'],
                    'reviewlevel' => $arr_add['reviewlevel'],
                    'activity_name' => $arr_add['activity_name'],
                    'category' => $arr_add['category'],
                    'high_low_config' => $arr_add['high_low_config'],
                    'config_softname' => $arr_add['config_softname'],
                    'config_package' => $arr_add['config_package'],
                    'start_time' => strtotime($arr_add['start_time']),
                    'end_time' => strtotime($arr_add['end_time']),
                    'activity_content' => $arr_add['activity_content'],
                    'activity_days' => $arr_add['activity_days'],
                    'avg_income_lweek_inactive' => $arr_add['avg_income_lweek_inactive'],
                    'avg_regist_number_lweek' => $arr_add['avg_regist_number_lweek'],
                    'expected_regist_number' => $arr_add['expected_regist_number'],
                    'expected_income' => $arr_add['expected_income'],
                    'type' => $arr_add['type'],
                    'application_resource_position' => $arr_add['application_resource_position'],
                    'game_type' => $arr_add['game_type'],
                    'remark' => $arr_add['remark'],
                );
                $log_all_need = $this->logcheck($where_arr, $table_name, $column_arr, $this->yx_schedule_table);
                $msg = "报表-排期申请-修改排期申请：编辑了id为{$_POST['id']}的记录：";
                $msg .= $log_all_need;
            }
            // 准备写库
            $id = $this->yx_schedule_table->modifySchedule($arr_add);
            $this->assign("jumpUrl","/index.php/Sendnum/Schedule/schedule_list");
            if ($id == -1) {
                if ($opt == 1 || $opt == 3) {
                    $this->error('添加失败，负责人用户名不存在！');
                } else if ($opt == 2) {
                    $this->error('修改失败，负责人用户名不存在！');
                }
            } else if ($id) {
                $record = $this->yx_schedule_table->getTheRecord($id);
                $arr = $this->yx_schedule_table->deleteSchedule($_GET['schedule_id']);
                $str='';
                foreach($record[0] as $key => $v) {
                    if ($key == 'id')
                        continue;
                    if ($key == 'type') {
                        if ($v == '1')
                            $v = "常规";
                        else
                            $v = "活动";
                    }
                        $str .= $v . ',';
                }
                if ($opt == 1 || $opt == 3) {
                    $this->writelog("报表-排期申请-添加排期申请，添加了id为{$id}的记录:".$str, 'yx_schedule',$id,__ACTION__ ,'','add');
                    $this->success('添加成功');
                } else if ($opt == 2) {
                    $this->writelog($msg, 'yx_schedule',$id,__ACTION__ ,'','edit');
                    $this->success('修改成功');
                }
            } else {
                if ($opt == 1 || $opt == 3) {
                    $this->error('添加失败！');
                } else if ($opt == 2) {
                    if ($id === 0) {
                        $this->success('修改成功！');
                    } else {
                        $this->error('修改失败！');
                    }
                }
            }
            
        }
        $game_type_arr = array('SLG','MMORPG','ARPG','FPS','TPS','策略经营','动作格斗','动作射击','格斗动作','回合角色扮演','卡牌',
            '模拟经营','棋牌','射击','塔防','休闲竞速','休闲益智','益智');
        $this->assign('game_type_arr', $game_type_arr);
        $this->display('modify');
    }
    
    //删除记录
    public function delete() {
        if ($_GET) {
            $ids_arr = explode(',', $_GET['schedule_ids']);
            foreach($ids_arr as $id) {
                $record = $this->yx_schedule_table->getTheRecord($id);
                $arr = $this->yx_schedule_table->deleteSchedule($id);
                $str='';
                foreach($record[0] as $key => $v) {
                    if ($key == 'id')
                        continue;
                    if ($key == 'type') {
                        if ($v == '1')
                            $v = "常规";
                        else
                            $v = "活动";
                    }
                        $str .= $v . ',';
                }
                $this->writelog("报表-排期申请-删除排期申请，删除了id为{$id}的记录:".$str, 'yx_schedule',$id,__ACTION__ ,'','del');
            }
            $this->success('删除成功');
        }
    }
    
    // 批量导入
    public function import() {
        if ($_POST) {
            $tmp_name = $_FILES['upload']['tmp_name'];
            $tmp_houzhui = $_FILES['upload']['name'];
            $tmp_arr = explode('.',$tmp_houzhui);
            $houzhui = array_pop($tmp_arr);
            if(strtoupper($houzhui)!='CSV') {
                echo 2;exit(0);
            }
            $arr = $this->readcsv($tmp_name);
            if($arr===false) {
                echo 2;exit(0);
            }
            $this->writelog('报表-排期申请，批量导入了申请', 'yx_schedule','',__ACTION__ ,'','add');
            $this->ajaxReturn($arr,'导入成功！', 1);
        }
        $this->display('import');
    }
    
    function readcsv($file) {
        $title_arr = array();
        $content_arr = array();
        
        $handle = fopen($file, "r");
        $i = 0;
        $j = 0;
        while (($line_arr = $this->mygetcsv($handle, 10000, ",")) != FALSE) {
            if ($i == 0) {
                // 读入标题列
                $title_arr = $line_arr;
            } else {
                // 读入每行内容
                $content_arr[$j] = $line_arr;
                $j++;
            }
            $i++;
        }
        fclose($handle);
        // 预期的标题列
        // 判断读入的标题列是否与预期的一致
        $count_title_arr = count($title_arr);
        $count_correct_title_arr = count($this->correct_title_arr);
        if ($count_title_arr != $count_correct_title_arr)
            return false;
        foreach($count_title_arr as $k => $v) {
            if ($v != $count_correct_title_arr[$k])
                return false;
        }
        // 开始导入
        $model = D("sendNum.Schedule");
        $ret = $model->importAdd($content_arr);
        return $ret;
    }
    
    function mygetcsv(& $handle, $length = null, $d = ',', $e = '"') {
        $d = preg_quote($d);
        $e = preg_quote($e);
        $_line = "";
        $eof = false;
        while ($eof != true){
            $_line .= (empty ($length) ? fgets($handle) : fgets($handle, $length));
            $itemcnt = preg_match_all('/' . $e . '/', $_line, $dummy);
            if ($itemcnt % 2 == 0)
            $eof = true;
        }
        $_csv_line = preg_replace('/(?: |[ ])?$/', $d, trim($_line));
        $_csv_pattern = '/(' . $e . '[^' . $e . ']*(?:' . $e . $e . '[^' . $e . ']*)*' . $e . '|[^' . $d . ']*)' . $d . '/';
        preg_match_all($_csv_pattern, $_csv_line, $_csv_matches);
        $_csv_data = $_csv_matches[1];
        for ($_csv_i = 0; $_csv_i < count($_csv_data); $_csv_i++){
            $_csv_data[$_csv_i] = preg_replace('/^' . $e . '(.*)' . $e . '$/s', '$1', $_csv_data[$_csv_i]);
            $_csv_data[$_csv_i] = str_replace($e . $e, $e, $_csv_data[$_csv_i]);
        }
        return empty ($_line) ? false : $_csv_data;
     }
     
     function downfail() {
        $thistime = $_GET['thistime'];
        if($thistime) {
            $model = D('sendNum.ScheduleImportFail');
            $desc = $model->get_import_fail($thistime);
            $list = unserialize(iconv('utf-8','gbk',$desc['desc']));
            header("Content-type:application/vnd.ms-excel");
            header("content-Disposition:filename=fail.csv");
            
            $desc = '';
            foreach($this->correct_title_arr as $key => $value) {
                if ($key != 0) {
                    $desc .= ',';
                }
                $desc .= $value;
            }
            $desc .= "\r\n";

            //$desc ="产品名称,包名,包源大小,产品所属性质,公司名称,简介,合作方式,公司提交人联系方式,备注\r";
            $desc = iconv('utf-8','gbk',$desc);
            foreach($list as $v) {
                //$desc = $desc.$v['0'].','.$v['1'].','.$v['2'].','.$v['3'].','.$v['4'].','.$v['5'].','.$v['6'].','.$v['7'].','.$v['8']."\r\n";
                foreach ($v as $k => $v1) {
                    if ($k != 0)
                        $desc .= ',';
                    $desc .= $v1;
                }
                $desc .= "\r\n";
            }
            echo $desc;
            exit(0);
        }
     }
    
}


?>
