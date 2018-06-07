<?php
/*
 * 已处理反馈渠道统计
 */
class FeedbackCountAction extends CommonAction {
    //反馈渠道&问题类型配置
    public function feedback_config(){
        $res = $this->get_feedback(true);
               // var_dump($res);
        $this->assign('res',$res);
        $this->display();
    }
    
    //添加或修改反馈渠道&问题类型
    public function operate_feedback(){
        $feedback_type = $this->get_feedback();
        $this->assign('feedback_type',$feedback_type);
        if(isset($_GET['edit'])&&$_GET['edit']=='1'){
            if(!empty($_GET['id'])){                
                $model = M('');
                $feed_type = $model->table('sj_feedback_config')->where(array('id'=>$_GET['parent_id'],'status'=>1))->field('c_name')->find();
                if($feed_type['c_name']=="open后台"){
                    $edit_info['type'] = '2';
                    $edit_info['parent_id'] = $_GET['parent_id'];
                    $vals['flag'] = 8;
                    $vals['code'] = $_GET['id'];
                    $url =  C('feed_url').C('userfeedback');
                    $open = httpGetInfo($url, $vals);
                    $open = json_decode($open,true);
                    $edit_info['c_name'] = $open['userfeedbacks']['name'];
                    $edit_info['rank'] = $open['userfeedbacks']['sort'];
                    $edit_info['is_open'] = 1; 
                    $edit_info['id'] = $open['userfeedbacks']['code']; 
                }else{
                    $edit_info = $model->table('sj_feedback_config')->where(array('id'=>$_GET['id'],'status'=>1))->find();
                }
                
                $this->assign('edit_info',$edit_info);
            }
            
        }
        if($_POST){
            $this->add_feedback($_POST);
        }
        $this->display();
    }
    
    //渠道类型
    private function get_feedback($show_all){
        $model = M('');
        $feedback_type = $model->table('sj_feedback_config')->where(array('type'=>1,'status'=>1))->field('id,c_name,rank,add_tm')->order('rank asc')->select();
        $feedback_type_arr = array();
        $res = array();
        if($show_all){
            foreach($feedback_type as $k=>$v){
                $res[$v['id']]['parent'] = $v;
                if($v['c_name']=='open后台'){
                    $vals['flag'] = 7;
                    $url =  C('feed_url').C('userfeedback');
                    $open = httpGetInfo($url, $vals);
                    $open_id = $v['id'];
                    $open_info = json_decode($open,true);
                }else{
                    $feedback_type_arr[] = $v['id'];
                }
            }
           foreach($open_info['userfeedbacks'] as $o_k=>$o_v){
                $res[$open_id]['son'][$o_k]['id'] = $o_v['code'];
                $res[$open_id]['son'][$o_k]['parent_id'] = $open_id;
                $res[$open_id]['son'][$o_k]['rank'] = $o_v['sort'];
                $res[$open_id]['son'][$o_k]['add_tm'] = $o_v['createTime'];
                $res[$open_id]['son'][$o_k]['c_name'] = $o_v['name'];
                $res[$open_id]['son'][$o_k]['is_open'] = 1;
            }
            $ques = $model->table('sj_feedback_config')->where(array('type'=>2,'status'=>1,'parent_id'=>array('in',$feedback_type_arr)))->field('id,parent_id,c_name,rank,add_tm')->order('rank asc')->select();
            foreach($ques as $key=>$val){
                $res[$val['parent_id']]['son'][] = $val;
            }
             return $res;
        }else{
            return $feedback_type;
        }

    }
    //添加反馈渠道&问题类型
    private function add_feedback($data){
        $url = C('feed_url').C('userfeedback');
        $model  = M('');
        $insert_data = array();
        if(isset($data['feedback'])&&$data['feedback']=="1"){
            //渠道
             if(empty($data['canal_type'])){
                 $this->error('请填写项目名称');
             }else if($data['canal_type']==3){
                 if(empty($data['canal_name'])){
                     $this->error('请填写项目名称');
                 }
                 $insert_data['c_name'] = $data['canal_name'];
             }else{
                 $name = array('1'=>'518后台','2'=>'open后台');
                 $insert_data['c_name'] = $name[$data['canal_type']];
             }
             $insert_data['type'] = 1;
             $insert_data['parent_id'] = 0;
        }else if($data['feedback']=="2"){
             //问题
            if(empty($data['choose_canal'])){
                $this->error('请选择渠道');
            }else{
                $insert_data['parent_id'] = $data['choose_canal'];
            }
            if(empty($data['ques_name'])){
                $this->error('请填写项目名称');
            }else{
                $insert_data['c_name'] = $data['ques_name'];
            }
            $insert_data['type'] = 2;
        }else{
            $this->error('请选择添加项目');
        }
        if(empty($data['rank'])||!is_numeric($data['rank'])){
            $this->error('排序值错误，请重新输入');
        }
        
        //判断名字是否唯一
        if($data['edit'] ==1){    
            $can_insert = $this->confirm_name($insert_data,$_POST['id']);    
        }else{
            $can_insert = $this->confirm_name($insert_data);
        }
        if($insert_data['type']==1){
            $info = '反馈渠道';
        }else{
            $info = '问题类型';
        }
        $choose_canal_name = $model->table('sj_feedback_config')->where(array('id'=>$data['choose_canal']))->field('c_name')->find();
        if($can_insert){
            $insert_data['update_tm'] = time();
            if($data['edit']==1){
                //编辑open后台问题类型
                if($data['is_open']=='1'){
                    $vals['code'] = $_POST['id'];
                    $vals['userid'] = $_SESSION['admin']['admin_id'];
                    if($choose_canal_name['c_name']=="open后台"){       
                        $vals['flag'] = 6;
                        $vals['name'] = $data['ques_name'];
                        $vals['sort'] = $data['rank'];
                        $edit_info = httpGetInfo($url, $vals);
                        $edit_info = json_decode($edit_info,true);
                        if($edit_info['success']){
                            $this->writelog('操作设置-反馈渠道&问题类型配置：更新了open后台id为' . $_POST['id'] . '的问题类型');
                            $this->success('更新成功');
                        }else{
                            $this->error('更新失败');
                        }
                    }else{
                        //open后台更换到其他渠道
                        //先删除open后台的，再入到其他渠道
                        $vals['flag'] = 5;
                        $vals['code'] = $_POST['id'];
                        $del_info = httpGetInfo($url, $vals);
                        $del_info = json_decode($del_info,true);
                        if($del_info['success']){
                            $insert_data['feedback']= 2;
                            $insert_data['choose_canal']= $data['choose_canal'];
                            $insert_data['ques_name']= $data['ques_name'];
                            $insert_data['rank']= $data['rank'];
                            $this->writelog('操作设置-反馈渠道&问题类型配置：更新了open后台id为' . $_POST['id'] . '的问题类型到渠道类型id为'.$data['choose_canal']);
                            $this->add_feedback($insert_data);
                        }else{
                            $this->error('更新失败');
                        }
                    }
                }else{
                    if($choose_canal_name['c_name']=="open后台"){
                        //添加open后台问题类型调用接口                    
                        $vals['flag'] = 4;
                        $vals['name'] = $insert_data['c_name'];
                        $vals['sort']  = $data['rank'];
                        $vals['userid'] = $_SESSION['admin']['admin_id'];
                        $add_info = httpGetInfo($url, $vals);
                        $add_info = json_decode($add_info,true);
                        if(!$add_info['success']){
                            if($add_info['msg']=='该类型已经存在'){
                                $this->error('同一渠道下的问题类型不可重复添加');
                            }else if($add_info['msg']=='排序重复，请重新输入'){
                                $this->error('排序值错误，请重新输入');
                            }else{
                                $this->error('添加失败');
                            }
                        }else{
                              $res = $model->table('sj_feedback_config')->where(array('id' => $_POST['id']))->save(array('status'=>0));
                              $this->writelog('操作设置-反馈渠道&问题类型配置：添加了open后台名称为' . $insert_data['c_name'] . '的问题类型并删除了id为'.$_POST['id'].'问题类型(换渠道到open后台)','sj_feedback_config',$_POST['id'],__ACTION__ ,"","del");
                              $this->success('添加成功');
                        }
                    }else{
                        $res = $model->table('sj_feedback_config')->where(array('id' => $_POST['id']))->save($insert_data);
                        if ($res) {
                            $rank = $this->_updateRankInfo('sj_feedback_config', 'rank', $_POST['id'], array('status' => 1, 'type' => $insert_data['type'], 'parent_id' => $insert_data['parent_id']), (int) $data['rank']);
                            if (!$rank) {
                                $this->error('更新排序失败');
                            } else {
                                $this->writelog('操作设置-反馈渠道&问题类型配置：更新了id为' . $_POST['id'] . '的' . $info,'sj_feedback_config',$_POST['id'],__ACTION__ ,"","edit");
                                $this->success('更新成功');
                            }
                        } else {
                            $this->error('更新失败');
                        }
                    }
                    
                }
                
            }else{
                if($choose_canal_name['c_name']=="open后台"){
                    //添加open后台问题类型调用接口                    
                    $vals['flag'] = 4;
                    $vals['name'] = $insert_data['c_name'];
                    $vals['sort']  = $data['rank'];
                    $vals['userid'] = $_SESSION['admin']['admin_id'];
                    $add_info = httpGetInfo($url, $vals);
                    $add_info = json_decode($add_info,true);
                    if(!$add_info['success']){
                        if($add_info['msg']=='该类型已经存在'){
                            $this->error('同一渠道下的问题类型不可重复添加');
                        }else if($add_info['msg']=='排序重复，请重新输入'){
                            $this->error('排序值错误，请重新输入');
                        }else{
                            $this->error('添加失败');
                        }
                    }else{
                          $this->writelog('操作设置-反馈渠道&问题类型配置：添加了名称为' . $insert_data['c_name'] . '的open后台问题类型');
                          $this->success('添加成功');
                    }
                }else{
                    $insert_data['add_tm'] = time();
                    $res = $model->table('sj_feedback_config')->add($insert_data);
                    if ($res) {
                        $rank = $this->_updateRankInfo('sj_feedback_config', 'rank', $res, array('status' => 1, 'type' => $insert_data['type'], 'parent_id' => $insert_data['parent_id']), (int) $data['rank']);
                        if (!$rank) {
                            $this->error('更新排序失败');
                        } else {
                            $this->writelog('操作设置-反馈渠道&问题类型配置：添加了id为' . $res . '名称为"' . $insert_data['c_name'] . '"的' . $info,'sj_feedback_config',$res,__ACTION__ ,"","add");
                            $this->success('添加成功');
                        }
                    } else {
                        $this->error('添加失败');
                    }
                }
                
            }
            
        }else{
            if($insert_data['type']=="1"){
                $this->error('渠道不可重复添加');
            }else{
                $this->error('同一渠道下的问题类型不可重复添加');
            }
        } 
    }
    

    //删除反馈或问题
    public function  del_feedback(){
        $model = M('');
        if(isset($_GET['id'])&&!empty($_GET['id'])){
            if($_GET['is_open']=='1'){
                $url = C('feed_url').C('userfeedback');
                $vals['flag'] = 5;
                $vals['code'] = $_GET['id'];
                $vals['userid'] = $_SESSION['admin']['admin_id'];
                $del_info = httpGetInfo($url, $vals);
                $del_info = json_decode($del_info,true);
                if($del_info['success']){
                    $model->table('sj_feedback_count')->where(array('ques_id'=>$_GET['id'],'is_open'=>1))->save(array('status'=>0));
                    $this->writelog('操作设置-反馈渠道&问题类型配置：删除了open后台id为' . $_GET['id'] . '的' . '问题类型','sj_feedback_count',"ques_id:{$_GET['id']}",__ACTION__ ,"","del");
                    $this->success('删除成功');
                    
                }else{
                    $this->error('删除失败');
                }
            }else{
                $where['id'] = $_GET['id'];
                $data['status'] = 0;
                $data['update_tm'] = time();
                $res = $model->table('sj_feedback_config')->where($where)->save($data);
                if ($res) {
                    //删除后更新排序
                    $model->table('sj_feedback_count')->where(array('ques_id'=>$_GET['id']))->save(array('status'=>0));
                    $del_data = $model->table('sj_feedback_config')->where($where)->find();
                    $target_id = $model->table('sj_feedback_config')->where(array('type' => $del_data['type'], 'parent_id' => $del_data['parent_id'], 'status' => 1, 'rank' => array('exp', ' > ' . $del_data['rank'])))->order('rank asc')->field('id')->find();
                    if ($target_id) {
                        $rank = $this->_updateRankInfo('sj_feedback_config', 'rank', $target_id['id'], array('status' => 1, 'type' => $del_data['type'], 'parent_id' => $del_data['parent_id']), (int) $del_data['rank']);
                    }
                    if ($del_data['type'] == 1) {
                        $info = '反馈渠道';
                    } else {
                        $info = '问题类型';
                    }
                    $this->writelog('操作设置-反馈渠道&问题类型配置：删除了ques_id为' . $_GET['id'] . '的' . $info,'sj_feedback_count',"ques_id:{$_GET['id']}",__ACTION__ ,"","del");
                    $this->success('删除成功');
                } else {
                    $this->error('删除失败');
                }
            }
            
        }
    }
    //判断名字是否唯一
    private  function confirm_name($insert_data,$id){
        $model  = M('');
        $where = array();
        $where['c_name'] = $insert_data['c_name'];
        $where['status'] = 1;
        if($insert_data['type']=="1"){
            $where['type'] = 1;
        }else{
            $where['type'] = 2;
            $where['parent_id'] = $insert_data['parent_id'];
        }
        if(isset($id)&&!empty($id)){
            $where['id']=array('exp',' != '.$id);
        }
        $name = $model->table('sj_feedback_config')->where($where)->find();
        if($name){
            return false;
        }else{
            return true;
        }
    }
}

