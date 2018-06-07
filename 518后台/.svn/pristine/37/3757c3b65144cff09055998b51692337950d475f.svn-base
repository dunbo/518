<?php

class ScheduleModel extends Model {
    protected $trueTableName = 'yx_schedule';
    
    // 返回排期列表
    public function getlist($get, $post) {
        $sqlparam='';
        
        //去掉get和post参数里的的空格
        foreach($get as $key => $value) {
            $get[$key] = trim($get[$key]);
        }
        foreach($post as $key => $value) {
            $post[$key] = trim($post[$key]);
        }
        
        if(isset($get['product_name'])) {
            $sqlparam = $sqlparam.'product_name='.$get['product_name'].'&';
            $where['product_name'] = array('like', '%'.$get['product_name'].'%');
        }
        if(isset($get['person_in_charge'])) {
            $sqlparam = $sqlparam.'person_in_charge='.$get['person_in_charge'].'&';
            $where['person_in_charge'] = array('EQ',$get['person_in_charge']);
        }
        
        if(isset($get['begintime']) || isset($get['endtime'])) {
            $begintime = 0;
            $endtime = 9999999999;
            if (strlen($get['begintime']) > 0) {
                $begintime = $get['begintime'];
                $sqlparam = $sqlparam.'begintime='.$begintime.'&';
            }
            if (strlen($get['endtime']) > 0) {
                $endtime = $get['endtime'];
                $sqlparam = $sqlparam.'endtime='.$endtime.'&';
            }
            $where['application_time']  = array('between',''.$begintime.','.$endtime.'');
        }
        
        if(isset($get['start_time_begin']) || isset($get['start_time_end'])) {
            $start_time_begin = 0;
            $start_time_end = 9999999999;
            if (strlen($get['start_time_begin']) > 0) {
                $start_time_begin = $get['start_time_begin'];
                $sqlparam = $sqlparam.'start_time_begin='.$start_time_begin.'&';
            }
            if (strlen($get['start_time_end']) > 0) {
                $start_time_end = $get['start_time_end'];
                $sqlparam = $sqlparam.'start_time_end='.$start_time_end.'&';
            }
            $where['start_time']  = array('between',''.$start_time_begin.','.$start_time_end.'');
        }
        
        if(isset($get['category']) && $get['category'] != -1) {
            $sqlparam = $sqlparam.'category='.$get['category'].'&';
            $where['category'] = array('EQ',$get['category']);
        }
        if(isset($get['activity_name'])) {
            $sqlparam = $sqlparam.'activity_name='.$get['activity_name'].'&';
            $where['activity_name'] = array('EQ',$get['activity_name']);
        }
        if(isset($get['package_name'])) {
            $sqlparam = $sqlparam.'package_name='.$get['package_name'].'&';
            $where['package_name'] = array('EQ',$get['package_name']);
        }
        if(isset($get['type']) && $get['type'] != -1) {
            $sqlparam = $sqlparam.'type='.$get['type'].'&';
            $where['type'] = array('EQ',$get['type']);
        }
        
        if($post) {
            if(strlen($post['product_name'])>0) {
                $sqlparam = $sqlparam.'product_name='.$post['product_name'].'&';
                $where['product_name'] = array('like', '%'.$post['product_name'].'%');
            }
            if(strlen($post['person_in_charge'])>0) {
                $sqlparam = $sqlparam.'person_in_charge='.$post['person_in_charge'].'&';
                $where['person_in_charge'] = array('EQ',$post['person_in_charge']);
            }            
            
            $begintime = 0;
            $endtime = 9999999999;
            if (strlen($post['begintime']) > 0) {
                $begintime = strtotime($post['begintime']);
                $sqlparam = $sqlparam.'begintime='.$begintime.'&';
            }
            if (strlen($post['endtime']) > 0) {
                $endtime = strtotime($post['endtime']);
                $sqlparam = $sqlparam.'endtime='.$endtime.'&';
            }
            $where['application_time']  = array('between',''.$begintime.','.$endtime.'');
            
            $start_time_begin = 0;
            $start_time_end = 9999999999;
            if (strlen($post['start_time_begin']) > 0) {
                $start_time_begin = strtotime($post['start_time_begin']);
                $sqlparam = $sqlparam.'start_time_begin='.$start_time_begin.'&';
            }
            if (strlen($post['start_time_end']) > 0) {
                $start_time_end = strtotime($post['start_time_end']);
                $sqlparam = $sqlparam.'start_time_end='.$start_time_end.'&';
            }
            $where['start_time']  = array('between',''.$start_time_begin.','.$start_time_end.'');
            
            if(strlen($post['category'])>0 && $post['category'] != -1) {
                $sqlparam = $sqlparam.'category='.$post['category'].'&';
                $where['category'] = array('EQ',$post['category']);
            }
            if(strlen($post['activity_name'])>0) {
                $sqlparam = $sqlparam.'activity_name='.$post['activity_name'].'&';
                $where['activity_name'] = array('EQ',$post['activity_name']);
            }
            if(strlen($post['package_name'])>0) {
                $sqlparam = $sqlparam.'package_name='.$post['package_name'].'&';
                $where['package_name'] = array('EQ',$post['package_name']);
            }
            if(strlen($post['type'])>0 && $post['type'] != -1) {
                $sqlparam = $sqlparam.'type='.$post['type'].'&';
                $where['type'] = array('EQ',$post['type']);
            }
        }
        
        // 判断当前用户是否在权限管理员列表里
        $user_name = $_SESSION['admin']['admin_user_name'];
        $exsit = $this->table('yx_admin')->where("admin_user_name='$user_name'")->select();
        $auth_schedule = $exsit[0]['auth_schedule'];
        if (!$exsit || $auth_schedule != 1) {
            $where['person_in_charge'] = array('EQ',$user_name);
            if(isset($get['person_in_charge'])) {
                $sqlparam = $sqlparam.'person_in_charge='.$get['person_in_charge'].'&';
                if ($get['person_in_charge'] === $user_name) {
                    $where['person_in_charge'] = array('EQ',$user_name);
                } else
                    $where['person_in_charge'] = array('EQ', '');
            }
            if($post && strlen($post['person_in_charge'])>0) {
                $sqlparam = $sqlparam.'person_in_charge='.$post['person_in_charge'].'&';
                if ($post['person_in_charge'] === $user_name) {
                    $where['person_in_charge'] = array('EQ',$user_name);
                } else
                    $where['person_in_charge'] = array('EQ', '');
            }
        }
        
        import("@.ORG.Page");
        $count = $this->where($where)->count();//查询满足要求的总记录数
        //file_put_contents("111.txt", $this->getlastsql());
        $Page = new Page($count,50);//实例化分页类传入总记录数和每页显示的记录数
        
        //进行分页数据查询注意limit方法的参数要使用Page类的属性
        $list = $this->where($where)->order('application_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        $allist = $this->where($where)->order('application_time desc')->select();
        $Page->parameter = $sqlparam;
        $Page->setConfig('header','篇记录');
        $Page->setConfig('first','<<');
        $Page->setConfig('last','>>');
        $show = $Page->show();//分页显示输出
        $res = array(
            'list'=>$list,
            'allist'=>$allist,
            'show'=>$show,
            'sqlparam'=>$sqlparam,
        );
        return $res;
    }
    
    public function getContent($id) {
        return $this->where("id=$id")->find();
    }
    
    // 增加/修改申请表，如果返回-1，表示负责人不存在，如果返回0或者false，表示增加/修改失败，如果返回>0，则正常
    public function modifySchedule($data) {
        // 先检查负责人是否为存在帐号
        $name = $data['person_in_charge'];
        $exsit = $this->table('sj_admin_users')->where("admin_user_name='$name'")->select();
        if (!$exsit)
            return -1;
        $content['application_time'] = strtotime($data['application_time']);
        $content['person_in_charge'] = $data['person_in_charge'];
        $content['product_name'] = $data['product_name'];
        $content['package_name'] = $data['package_name'];
        $content['package_size'] = $data['package_size'];
        $content['reviewlevel'] = $data['reviewlevel'];
        $content['activity_name'] = $data['activity_name'];
        $content['category'] = $data['category'];
        $content['high_low_config'] = $data['high_low_config'];
        $content['start_time'] = strtotime($data['start_time']);
        $content['end_time'] = strtotime($data['end_time']);
        $content['activity_content'] = $data['activity_content'];
        $content['activity_days'] = $data['activity_days'];
        $content['avg_income_lweek_inactive'] = $data['avg_income_lweek_inactive'];
        $content['avg_regist_number_lweek'] = $data['avg_regist_number_lweek'];
        $content['expected_regist_number'] = $data['expected_regist_number'];
        $content['expected_income'] = $data['expected_income'];
        $content['type'] = $data['type'];
        $content['application_resource_position'] = $data['application_resource_position'];
        $content['game_type'] = $data['game_type'];
        // 以下为非必填项
        $content['config_softname'] = isset($data['config_softname']) ? $data['config_softname'] : "";
        $content['config_package'] = isset($data['config_package']) ? $data['config_package'] : "";
        $content['remark'] = isset($data['remark']) ? $data['remark'] : "";
        if($data['id']=='') {
            // 没有id传过来，表示添加新项
            $arr = $this->add($content);
            return $arr;
        } else {
            // 有id传过来，表示更新数据
            $id = $data['id'];
            $arr = $this->where("id=$id")->save($content);
            if (!$arr)
                return $arr;
            return $id;
        }
    }
    
    public function getTheRecord($id) {
        return $this->where("id=$id")->select();
    }
    
    public function updateSchedule($id, $data) {
        $arr = $this->where("id=$id")->save($data);
        //file_put_contents("222.txt", $this->getlastsql());
        return $arr;
    }
    
    public function deleteSchedule($id) {
        $where = array(
            'id' => array('EQ', $id),
        );
        return $this->where($where)->delete();
    }
    
    public function importAdd($content_arr) {
        // count the total number
        $total_num = count($content_arr);
        $fail_arr = array();
        foreach($content_arr as $key => $line_arr) {
            $b_continue = false;
            for($i = 0; $i <= 22; $i++) {
                if ($i == 8 || $i == 9)
                    continue;
                if (!trim($line_arr[$i])) {
                    $b_continue = true;
                    break;
                }
            }
            if ($b_continue) {
                $fail_arr[$key] = $line_arr;
                continue;
            }
            $map = array();
            $map['application_time'] = trim($line_arr[0]);
            if (iconv('gbk','utf-8',trim($line_arr[1])) == '常规')
                $map['type'] = 1;
            else if (iconv('gbk','utf-8',trim($line_arr[1])) == '活动')
                $map['type'] = 2;
            else {
                //fail
                $fail_arr[$key] = $line_arr;
                continue;
            }
            $map['person_in_charge'] = iconv('gbk','utf-8',trim($line_arr[2]));
            $map['product_name'] = iconv('gbk','utf-8',trim($line_arr[3]));
            $map['package_name'] = iconv('gbk','utf-8',trim($line_arr[4]));
            $map['package_size'] = trim($line_arr[5]);
            if (in_array(trim($line_arr[6]), array('A', 'B', 'C', 'D', 'S'))) {
                $map['reviewlevel'] = trim($line_arr[6]);
            } else {
                $fail_arr[$key] = $line_arr;
                continue;
            }
            $map['game_type'] = iconv('gbk','utf-8',trim($line_arr[7]));
            $map['application_resource_position'] = iconv('gbk','utf-8',trim($line_arr[8]));
            if (iconv('gbk','utf-8',trim($line_arr[9])) == '网游')
                $map['category'] = 1;
            else if (iconv('gbk','utf-8',trim($line_arr[9])) == '单机')
                $map['category'] = 2;
            else {
                //fail
                $fail_arr[$key] = $line_arr;
                continue;
            }
            if (iconv('gbk','utf-8',trim($line_arr[10])) == '无')
                $map['high_low_config'] = 0;
            else if (iconv('gbk','utf-8',trim($line_arr[10])) == '高配')
                $map['high_low_config'] = 1;
            else if (iconv('gbk','utf-8',trim($line_arr[10])) == '低配')
                $map['high_low_config'] = 2;
            else {
                //fail
                $fail_arr[$key] = $line_arr;
                continue;
            }
            if ($map['high_low_config'] != 0) {
                if (!trim($line_arr[11]) || !trim($line_arr[12])) {
                    $fail_arr[$key] = $line_arr;
                    continue;
                }
                $map['config_softname'] = iconv('gbk','utf-8',trim($line_arr[11]));
                $map['config_package'] = iconv('gbk','utf-8',trim($line_arr[12]));
            }
            $map['activity_name'] = iconv('gbk','utf-8',trim($line_arr[13]));
            $map['start_time'] = trim($line_arr[14]);
            $map['end_time'] = trim($line_arr[15]);
            $map['activity_days'] = iconv('gbk','utf-8',trim($line_arr[16]));
            $map['activity_content'] = iconv('gbk','utf-8',trim($line_arr[17]));
            $map['avg_income_lweek_inactive'] = iconv('gbk','utf-8',trim($line_arr[18]));
            $map['avg_regist_number_lweek'] = iconv('gbk','utf-8',trim($line_arr[19]));
            $map['expected_regist_number'] = iconv('gbk','utf-8',trim($line_arr[20]));
            $map['expected_income'] = iconv('gbk','utf-8',trim($line_arr[21]));
            $map['remark'] = iconv('gbk','utf-8',trim($line_arr[22]));
            $ret = $this->modifySchedule($map);
            if ($ret <= 0 || $ret === false) {
                // fail
                $fail_arr[$key] = $line_arr;
            }
        }
        
        // calculate the number
        $fail_num = count($fail_arr);
        $succss_num = $total_num - $fail_num;
        $this_time = time();
        // write the $fail_arr into table
        $model = D('sendNum.ScheduleImportFail');
        $model->import_fail_add($fail_arr,$this_time);
        // return
        $ret = array(
            'totalnum' => $total_num,
            'succnum' => $succss_num,
            'failnum' => $fail_num,
            'thistime' => $this_time,
        );
        
        return $ret;
    }
    
}

?>
