<?php
class ContentPlatformModel extends Model {
    public function get_video($where,$field='*'){
        if(isset($where['id'])){
            $id_str = implode("','",$where['id']);
            $where['id'] = array('exp'," in ('{$id_str}')");
        }
        $list = $this->table('sj_soft_extra')->where($where)->field($field)->select();
        $res = array();
        foreach($list as $k=>$v){
            $v['pic'] = IMGATT_HOST.$v['video_pic'];
            $res[$v['id']] = $v;
        }
        return $res;
    }

    public function get_content($where,$field='*'){
        if(isset($where['id'])){
            $id_str = implode("','",$where['id']);
            $where['id'] = array('exp'," in ('{$id_str}')");
        }
        $list = $this->table('sj_soft_content_explicit')->where($where)->field($field)->select();
        $res = array();
        foreach($list as $k=>$v){
            if($v['explicit_pic']){
                $tmp_pic = json_decode($v['explicit_pic'], true);
                $v['pic'] = IMGATT_HOST.$tmp_pic['pic0'];
            }
            $res[$v['id']] = $v;
        }
        return $res;
    }

    public function get_user($where, $field='*'){
        $user = $this->table('content_platform_user')->where($where)->field($field)->select();
        $return = array();
        foreach($user as $k=>$v){
            $return[$v['userid']] = $v;
        }
        return $return;
    }

    public function get_param(){
        $res = $this->table('content_platform_parm')->where("status != 0")->select();

        $list1 = $list2 = $list3 = $list4 = array();
        foreach($res as $k=>$v){
            if($v['id']==1){
                $list1[] = $v;
            }
            if($v['parent_id']==1){
                $list1[$v['type']][] = $v;
            }
            if($v['id']==2||$v['parent_id']==2){
                $list2[] = $v;
            }
            if($v['parent_id']==3){
                $list3[] = $v;
            }
            if($v['parent_id']==4){
                $list4[] = $v;
            }
        }
        return array($list1, $list2, $list3, $list4);
    }

    public function get_flexible_name(){
        $res = $this->table('content_platform_parm')->where("id = 2")->find();
        return $res['name'];
    }

    /**
     * 系数修改后同步下月的结算系数
     * 当月的系数不更改
     */
    public function set_next_month_param($parent_level_id){
        $month = date("Ym");
        $res = $this->table('content_platform_parm_config')->where("month > {$month}")->find();
        $value = json_decode($res['value'], true);
        $level1and2_ids = $parent_level_id[1].','.$parent_level_id[2];
        $level1and2 = $this->table('content_platform_parm')->where("id in({$level1and2_ids})")->field('id,name,status')->select();
        $level1and2_arr = array();
        foreach($level1and2 as $k=>$v){
            $level1and2_arr[$v['id']] = $v['status'];
        }
        $where = array('status'=>1);
        $data = array('update_tm'=>time());
        $data['status'] = 2;
        if($level1and2_arr[$parent_level_id[1]]['status']==2||$level1and2_arr[$parent_level_id[2]]['status']==2){
            //基础价值停用
            unset($value[1]);
            $not_in = '0';
            if($level1and2_arr[$parent_level_id[1]]['status']==2){
                $data['status'] = 0;
                $not_in .= ',1';
            }
            if($level1and2_arr[$parent_level_id[2]]['status']==2){
                $not_in .= ',2';
            }
            $where['parent_id'] = array('exp', " not in ({$not_in})");
        }
        $all_config = $this->table('content_platform_parm')->where($where)->field('id,name,parent_id,status,type')->select();
        $all_info = array();
        foreach($all_config as $k1=>$v1){
            $all_info[$v1['parent_id']][$v1['id']] = $v1;
        }
        $new_value = array();
        //基础价值
        $type1 = $type2 =array();
        foreach($all_info[$parent_level_id[1]] as $k=>$v){
            if($v['type']==1){
                $type1[] = $v;
            }else{
                $type2[] = $v;
            }
        }

        foreach($type1 as $level1){
            foreach($type2 as $level2){
                $tmp_id = $level1['id'].'-'.$level2['id'];
                $tmp_v = array('id'=>$tmp_id,'name'=>$level1['name'].$level2['name']);
                if(isset($value[$parent_level_id[1]][$tmp_id]['value'])){
                    $tmp_v['value'] = $value[$parent_level_id[1]][$tmp_id]['value'];
                }
                $new_value[$parent_level_id[1]][$tmp_id] = $tmp_v;
            }
        }
        //除基础价值外其他（手动加权，灵活系数，额外奖励）
        foreach($all_info as $k1=>$v1){
            foreach($v1 as $k2=>$v2){
                if($k1!=0&&$k1!=1){
                    $tmp1 = array('id'=>$v2['id'],'name'=>$v2['name']);
                    if(isset($value[$k1][$v2['id']]['value'])){
                        $tmp1['value'] = $value[$k1][$v2['id']]['value'];
                    }
                    $new_value[$k1][$tmp1['id']] = $tmp1;
                }
            }

        }

        $new_value = json_encode($new_value);
        $data['value'] = $new_value;
        $this->table('content_platform_parm_config')->where("month = {$res['month']}")->save($data);
    }

    public function get_parm_config($where){
        if($where['month']){
            $where['month'] = array_unique($where['month']);
            $tmp_m = implode("','",$where['month']);
            $where['month'] = array('exp'," in ('{$tmp_m}')");
        }
        $res = $this->table('content_platform_parm_config')->where($where)->select();
        $return = array();
        foreach($res as $k=>$v){
            $return[$v['month']] = $v;
        }
        return $return;
    }

    //内容外显、视频数据  同步到内容统计表
    public function sync_content($type, $arr){
        $time = time();
        if($type=='add'){
            $return = $this->table('content_platform_content')->add(array(
                'type' => $arr['type'],
                'content_id' => $arr['content_id'],
                'title' => $arr['title'],
                'user_id' => $arr['dev_id'],
                'pass_tm' => $time,
                'create_tm' => $time,
            ));
        }elseif($type='edit'){
            $edit = array();
            if($arr['title']){
                $edit['title'] = $arr['title'];
            }
            if($arr['pass_tm']){
                $edit['pass_tm'] = $arr['pass_tm'];
            }
            if($arr['status']){
                $edit['status'] = $arr['status'];
            }
            $return = $this->table('content_platform_content')->where(array('type'=>$arr['type'],'content_id'=>$arr['content_id']))->save($edit);
        }
        return $return;
    }

    //内容评分后同步用户结算数据状态
    public function relate_user_settle_status($user_id){
        $res = $this->table('content_platform_user_settle')->where("user_id = '{$user_id}' and status = 2")->field('id,status,month')->select();
        if($res){
            foreach($res as $k1=>$v1){
                $day = strtotime('+1 months', strtotime($v1['month'].'01'));
                //状态为内容待评价的
                $content_status = $this->table('content_platform_content')->where("user_id = '{$user_id}' and parm_status = 0 and pass_tm < '{$day}' and status = 1")->select();
                if(!$content_status){
                    $this->table('content_platform_user_settle')->where("id = {$v1['id']}")->save(array('status'=>3,'update_tm'=>time()));
                }
            }
        }
    }
}	