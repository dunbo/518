<?php
/**
 *  安智内容合作平台
 */

class ContentPlatformSettleAction extends CommonAction {
    public $m_anzhi_host = 'http://m.anzhi.com';
    public $param_level_id = array(1=>1,2=>2,3=>3,4=>4);
    /**
     * @desc 结算审核
     */
    public function settle_audit(){
        $model = M('');
        $tab = isset($_GET['tab'])?$_GET['tab']:1;
        if($tab == 1){
            $where = array('status'=>array('exp', 'in (1,2,3)'));
        }
        if(isset($_GET['begintime'])){
            $where['month'][] = array('egt',date('Ym',strtotime($_GET['begintime'])));
            $this->assign('begintime',$_GET['begintime']);
        }
        if(isset($_GET['endtime'])){
            $where['month'][] = array('elt',date('Ym',strtotime($_GET['endtime'])));
            $this->assign('endtime',$_GET['endtime']);
        }
        if(isset($_GET['status'])){
            $where['status'] = $_GET['status'];
            $this->assign('status',$where['status']);
        }
        $total = $model->table('content_platform_user_settle')->where($where)->count();
        import("@.ORG.Page2");
        $Page=new Page($total, 20);
        $list = $model->table('content_platform_user_settle')->where($where)->limit($Page->firstRow.','.$Page->listRows)->order('month desc')->select();
        foreach($list as $k=>$v){
            $v['settle_num'] = $v['settle_num']/100;
            $v['payable_num'] = $v['payable_num']/100;
            $v['compensate_num'] = $v['compensate_num']/100;
            $v['pay_num'] = $v['pay_num']/100;
            $v['difference_num'] = ($v['pay_num']-$v['payable_num'])/100;
            $list[$k] = $v;
        }
        $this->assign('list', $list);
        $this->assign('tab',$tab);
        $this->assign('page',$Page->show());
        $this->display();
    }

    /**
     * @desc 补差
     */
    public function compensation(){
        $id = $_GET['id'];
        $type = $_GET['type']?$_GET['type']:4;
        $model = M('');
        $res = $model->table('content_platform_user_settle')->where(array('id'=>$id))->find();
        $res['compensate_num'] = $res['compensate_num']/100;
        $res['settle_num'] = $res['settle_num']/100;
        $res['difference_num'] = ($res['pay_num'] - $res['payable_num'])/100;
//        var_dump($res);
        if($_POST){
            $this->save_compensation();
        }
        $this->assign('type',$type);
        $this->assign('id',$id);
        $this->assign('res',$res);
        $this->display();
    }

    /**
     * @desc保存补差及备注
     * @ type 1(修改备注) 2（通过,驳回，冻结） 3(结算) 4(补差)
     */
    public function save_compensation(){
        $id = $_POST['id'];
        if(!$id){
            $this->error('缺少参数');
        }
        $compensate_num = (float)$_POST['compensate_num'];
        $type = $_POST['type'];
        $model = M('');
        $data = array('update_tm'=>time());
        if($type == 1){
            //备注
            $data['info'] = $_POST['info'];
        }elseif($type == 2){
            //通过,驳回,冻结
            $data['status'] = $_POST['status']?$_POST['status']:4;
        }else if($type == 3){
            //结算
            $data['reason'] = $_POST['reason'];
            $data['pay_num'] = $_POST['pay_num']*100;
            $data['pay_tm'] = strtotime($_POST['pay_tm']);
            $data['status'] = 5;
        }else{
            //补差
            if(!$compensate_num){
                $this->error('补差金额可填写小数点两位的任意正负数');
            }
            $num = $compensate_num*100;
            $data['compensate_num'] = $num;
            $data['payable_num'] = array('exp'," settle_num + {$num}");
        }

        $res = $model->table('content_platform_user_settle')->where(array('id'=>$id))->save($data);
        if($res){
            if($type != 1){
                $msg = "安智内容合作平台:结算审核:对id['.$id.']进行了补差,金额为{$compensate_num}";
            }elseif($type == 2){
                $str = '';
                if($data['status'] == 3){
                    $str = '驳回';
                }elseif($data['status']== 4){
                    $str = '通过';
                }elseif($data['status']==7){
                    $str = '冻结';
                }elseif($data['status']==5){
                    $str = '结算';
                }
                $msg = "安智内容合作平台:结算审核:对id['.$id.']进行了{$str}";
            }else{
                $msg = "安智内容合作平台:结算审核:对id['.$id.']进行了备注,备注信息为{$data['info']}";
            }
            $this->writelog($msg, 'content_platform_user_settle', $id,__ACTION__ ,"","edit");
            if($type == 2){
                echo json_encode(array('code'=>200,'msg'=>'保存成功'));exit();
            }else{
                $this->success('保存成功');
            }
        }else{
            if($type == 2){
                echo json_encode(array('code'=>0,'msg'=>'保存失败'));exit();
            }else{
                $this->error('保存失败');
            }
        }
        exit();
    }

    /**
     * @desc 结算查看详情
     */
    function settle_moreinfo(){
        $user_id = $_GET['user_id'];
        $month = $_GET['month'];

        $where = array(
            'a.user_id' => $user_id,
            'a.settle_month' => $month
        );
        if(isset($_GET['content_id'])){
            $where = array('b.id'=>$_GET['content_id']);
            $this->assign('content_id',$_GET['content_id']);
        }
        if(isset($_GET['parm_status'])){
            $where['b.parm_status'] = $_GET['parm_status'];
            $this->assign('parm_status',$_GET['parm_status']);
        }
        $type = $_GET['type']?$_GET['type']:1;
        if(isset($type)){
            $where['b.type'] = $type;
            $this->assign('type',$type);
        }
        $content_platform = D('Dev.ContentPlatform');
        $total = $content_platform->table('content_platform_content_settle as a')->join('content_platform_content as b on a.c_p_cont_id = b.id')->where($where)->count();
        import("@.ORG.Page2");
        $Page=new Page($total, 20);
        $list = $content_platform->table('content_platform_content_settle as a')->join('content_platform_content as b on a.c_p_cont_id = b.id')->limit($Page->firstRow.','.$Page->listRows)->where($where)->field('a.id,a.c_p_cont_id,b.type,a.settle_month,a.settle_num,a.user_id,b.parm_status,b.pass_tm,b.content_id')->order('a.settle_month desc')->select();

        $this->content_settle_pub($user_id,$list,1);
        $this->assign('page',$Page->show());
        $this->display();
    }

    /**
     * @desc 财务结算
     */
    public function settle_passed(){
        $model = M('');
        $tab = $_GET['tab'];
        $where = array('status'=>array('exp', 'in (4,5,7)'));
        if(isset($_GET['begintime'])){
            $where['month'][] = array('egt',date('Ym',strtotime($_GET['begintime'])));
            $this->assign('begintime',$_GET['begintime']);
        }
        if(isset($_GET['endtime'])){
            $where['month'][] = array('elt',date('Ym',strtotime($_GET['endtime'])));
            $this->assign('endtime',$_GET['endtime']);
        }
        if(isset($_GET['user_name'])){
            $where['user_name'] = $_GET['user_name'];
            $this->assign('user_name',$_GET['user_name']);
        }
        if(isset($_GET['status'])){
            $where['status'] = $_GET['status'];
            $this->assign('status',$where['status']);
        }
        $total = $model->table('content_platform_user_settle')->where($where)->count();
        import("@.ORG.Page2");
        $Page=new Page($total, 20);
        $list = $model->table('content_platform_user_settle')->where($where)->limit($Page->firstRow.','.$Page->listRows)->order('create_tm desc')->select();

        foreach($list as $k=>$v){
            $v['settle_num'] = $v['settle_num']/100;
            $v['payable_num'] = $v['payable_num']/100;
            $v['compensate_num'] = $v['compensate_num']/100;
            $v['pay_num'] = $v['pay_num']/100;
            $v['difference_num'] = $v['pay_num']-$v['payable_num'];
            $list[$k] = $v;
        }
        $this->assign('list', $list);
        $this->assign('tab',$tab);
        $this->assign('page',$Page->show());
        $this->display();
    }

    /**
     * @desc 结算系数
     */
    public function settle_param(){
        $_GET['tab'] = $_GET['tab']?$_GET['tab']:1;
        $content_platform = D('Dev.ContentPlatform');
        $total = $content_platform->table('content_platform_parm_config')->count();
        import("@.ORG.Page2");
        $Page=new Page($total, 20);
        $list = $content_platform->table('content_platform_parm_config')->limit($Page->firstRow.','.$Page->listRows)->order('month desc')->select();
        foreach($list as $k=>$v){
            $v['value'] = json_decode($v['value'], true);
            foreach($v['value'][3] as $kk=>$vv){
                //灵活系数
                list($vv['value1'],$vv['value2'],$vv['value3']) = explode(';', $vv['value']);
                $v['value'][3][$kk] = $vv;
            }
            $list[$k] = $v;
        }
        $name = $content_platform->get_flexible_name();
        $this->assign('name', $name);
        $this->assign('list',$list);
        $this->assign('tab',$_GET['tab']);
        $this->assign('page',$Page->show());
        $this->assign('next_month',date("Ym",strtotime('+1 month')));
        $this->display();
    }

    /**
     * @desc 系数管理
     */
    public function manage_settle_param(){
        $content_platform = D('Dev.ContentPlatform');
        $list = $content_platform->get_param();
        $this->assign('list', $list);
        $this->assign('tab',$_GET['tab']);
        $this->display();
    }

    /**
     * @desc 系数操作
     */
    public function handle_param(){
        $content_platform = D('Dev.ContentPlatform');
        $id = $_GET['id'];
        $status = $_GET['status'];
        $show_status = $_GET['show_status'];
        $data = array('update_tm'=>time());
        $msg = '安智内容合作平台:结算管理:';
        if(isset($status)){
            $data['status'] = $status;
            if($status == 1){
                $msg .= "启用";
            }else if($status == 2){
                $msg .= "停用";
            }
            $msg .= "了ID为{$id}的系数";
        }
        if(isset($show_status)){
            $data['show_status'] = $show_status;
            $msg .= "修改了ID为{$id}的展示状态为";
            if($show_status == '1'){
                $msg .= "展示";
            }else if($show_status == '0'){
                $msg .= "不展示";
            }
        }
        $res = $content_platform->table('content_platform_parm')->where("id = {$id}")->save($data);
        if($res){
            if(isset($status)){
                $content_platform->set_next_month_param($this->param_level_id);
            }
            $this->writelog($msg, 'content_platform_user_settle', $id,__ACTION__ ,"","edit");
            $this->success('保存成功');
        }else{
            $this->error('保存失败');
        }
    }

    /**
     * @desc 编辑系数页面
     */
    public function edit_param_page(){
        $type = $_GET['type'];
        $model = M('');
        $res = $model->table('content_platform_parm')->where("(id = $type or parent_id = $type) and status != 0")->select();
        $this->assign('res', $res);
        $this->assign('param_level_id',$this->param_level_id);
        $this->assign('type', $type);
        $this->display();
    }

    /**
     * @desc 添加编辑指标页面
     */
    public function save_param(){
        $level = $_GET['level'];
        $type = $_GET['type'];
        $id = $_GET['id'];
        if($id){
            $model = M('');
            $res = $model->table('content_platform_parm')->where("id = {$id}")->find();
            $this->assign('res',$res);
            $this->assign('id',$id);
        }
        if($_POST){
            $this->save_param_do();
        }
        $this->assign('type',$type);
        $this->assign('level',$level);
        $this->display();
    }

    /**
     * @desc 添加编辑指标
     */
    public function save_param_do(){
        $id = $_POST['id'];
        $rank = $_POST['rank'];
        $name = $_POST['name'];
        $content_platform = D('Dev.ContentPlatform');
        $data = array(
            'rank' => $rank,
            'name' => $name,
            'update_tm' => time()
        );
        $msg = '安智内容合作平台:结算管理:';
        if(!$id){
            $data['parent_id'] = $_POST['level'];
            $data['type'] = $_POST['type'];
            $res = $content_platform->table('content_platform_parm')->add($data);
            $msg .= "添加了ID为{$res}的指标";
            $id = $res;
            $type = 'add';
        }else{
            $res = $content_platform->table('content_platform_parm')->where("id = {$id}")->save($data);
            $msg .= "编辑了ID为{$id}的指标";
            $type = 'edit';
        }
        if($res){
            $content_platform->set_next_month_param($this->param_level_id);
            $this->writelog($msg, 'content_platform_parm', $id,__ACTION__ ,"",$type);
            $this->success('保存成功');
        }
    }

    /**
     * @desc 系数确认或编辑
     */
    public function edit_param(){
        $id = $_REQUEST['id'];
        $content_platform = D('Dev.ContentPlatform');
        $month = date("Ym");
        $res = $content_platform->table('content_platform_parm_config')->where("id = {$id}")->find();
        $res['month'] = date('Ym',strtotime($res['month'].'01'));
        $res['value'] = json_decode($res['value'],true);
        $name = $content_platform->get_flexible_name();
        if($_POST){
            foreach($_POST as $k=>$v){
                if(empty($v)){
                    $this->error('系数不能为空');
                }
            }
            foreach($res['value'] as $k=>$v){
                foreach($v as $kk=>$vv){
                    if($k==1||$k==2||$k==4){
                        //基础价值
                        $vv['value'] = $_POST[$vv['id']];
                    }elseif($k==3){
                        //灵活系数
                        $tmp_val = $_POST["flexible{$vv['id']}_1"].';'.$_POST["flexible{$vv['id']}_2"].';'.$_POST["flexible{$vv['id']}_3"];
                        $vv['value'] = $tmp_val;
                    }
                    $v[$kk] = $vv;
                }
                $res['value'][$k] = $v;
            }
            $res['value'] = json_encode($res['value']);

            $data = array(
                'update_tm' => time(),
                'value' =>$res['value'],
                'complete_status' => 1
            );
            $msg = '';
            if($month>=$res['month']){
                $data['status'] = 1;
                $msg .= '(系数确认)';
            }
            $save = $content_platform->table('content_platform_parm_config')->where("id = {$id}")->save($data);
            if($save){
                if($data['status']==1){
                    //系数确认后将结算表状态为系数确认的数据状态变更为内容待评分
                    $content_platform->table('content_platform_user_settle')->where("month = '{$res['month']}' and status = 1")->save(array('status'=>2,'update_tm'=>time()));
                }
                $this->writelog('安智内容合作平台:结算管理:配置系数值为'.$res['value'].$msg, 'content_platform_parm', $id,__ACTION__ ,"",'edit');
                $_SERVER["HTTP_REFERER"] = 'settle_param/tab/1';
                $this->success('保存成功');
            }else{
                $this->error('保存失败');
            }
        }
        foreach($res['value'][3] as $k=>$v){
            list($v['value1'],$v['value2'],$v['value3']) = explode(';',$v['value']);
            $res['value'][3][$k] = $v;
        }
        if($month==$res['month']){
            $this->assign('now',1);
        }
        $this->assign('name', $name);
        $this->assign('res', $res);
        $this->display();
    }

    /**
     * @desc 内容评分
     */
    public function content_settle(){
        $content_platform = D('Dev.ContentPlatform');
        $where = array('a.status'=>1);
        $type = $_GET['type']?$_GET['type']:1;
        $field = 'a.*';
        if($type == 1){
            $table = 'sj_soft_content_explicit as b';
            $table_num = 'b';
            $title = 'title';
            if(isset($_GET['title']))
            $field .= ',b.title';
        }else{
            $table = 'sj_soft_extra as c';
            $table_num = 'c';
            $title = 'video_title';
            if(isset($_GET['title']))
            $field .= ',c.video_title';
        }
        if(isset($_GET['begintime'])){
            $where["a.pass_tm"][] = array('egt',strtotime($_GET['begintime']));
            $this->assign('begintime',$_GET['begintime']);
        }
        if(isset($_GET['endtime'])){
            $where["a.pass_tm"][] = array('elt',strtotime($_GET['endtime']));
            $this->assign('endtime',$_GET['endtime']);
        }
        if(isset($_GET['parm_status'])){
            $where['a.parm_status'] = $_GET['parm_status'];
            $this->assign('parm_status',$_GET['parm_status']);
        }
        if(isset($_GET['title'])){
            $where["{$table_num}.{$title}"] = array('exp'," like '%{$_GET['title']}%'");
            $this->assign('title',$_GET['title']);
        }
        $where['a.type'] = $type;
        $this->assign('type',$type);

        if(isset($_GET['username'])){
            $user = $content_platform->get_user(array('username'=>array('exp'," like '%{$_GET['username']}%'")), 'userid,username');
            $userids = array();
            foreach($user as $k=>$v){
                $userids[] = $v['userid'];
            }
            $userids_str = implode("','", $userids);
            $where['a.user_id'] = array('exp'," in('{$userids_str}')");
            $this->assign('username',$_GET['username']);
        }
        if(isset($_GET['month'])&&$userids){
            $start_day = strtotime($_GET['month'].'01');
            $end_day = strtotime('+1 months', strtotime($_GET['month'].'01'));
            $where['a.pass_tm'] = array('exp', " >='{$start_day}' and a.pass_tm <= '{$end_day}'");
            $this->assign('month',date("Y-m",strtotime($_GET['month'].'01')));
        }
        $content_platform = D('Dev.ContentPlatform');
        if(isset($_GET['title'])){
            $total = $content_platform->table('content_platform_content as a')->join("{$table} on a.content_id = {$table_num}.id")->where($where)->count();
        }else{
            $total = $content_platform->table('content_platform_content as a')->where($where)->count();
        }
//        var_dump($total);
        import("@.ORG.Page2");
        $Page=new Page($total, 20);
        if(isset($_GET['title'])){
            $list = $content_platform->table('content_platform_content as a')->join("{$table} on a.content_id = {$table_num}.id")->limit($Page->firstRow.','.$Page->listRows)->where($where)->field($field)->order('a.pass_tm desc')->select();
        }else{
            $list = $content_platform->table('content_platform_content as a')->limit($Page->firstRow.','.$Page->listRows)->where($where)->field($field)->order('a.pass_tm desc')->select();
        }
        $this->content_settle_pub('',$list,$_GET['content_type']?$_GET['content_type']:2);
        $this->assign('page',$Page->show());
        $this->display();
    }

    /**
     * @desc 配置内容系数
     */
    public function grade_content(){
        $content_platform = D('Dev.ContentPlatform');
        if($_POST){
            $this->save_grade_content();
        }
        $content_base = $content_platform->table('content_platform_content')->where("id = '{$_GET['id']}'")->find();
        //文章评分使用文章创建时间月份的系数
        $month = date("Ym",$content_base['create_tm']);
        if($content_base['type']==1){
            $content_info =  $content_platform->get_content(array('id'=>(array)$content_base['content_id']),'id,title');
        }else{
            $content_info =  $content_platform->get_video(array('id'=>(array)$content_base['content_id']),'id,video_title as title');
        }
        $param = $content_platform->get_parm_config(array('month'=>(array)$month));

        $res = $param[$month];
        $res['value'] = json_decode($res['value'],true);
        $now_config = $content_platform->table('content_platform_content_config')->where("content_id = '{$content_base['content_id']}'")->find();
        $now_config['value'] = json_decode($now_config['value'],true);
        $this->assign('month',$_GET['month']);
        $this->assign('username',$_GET['username']);
        $this->assign('now_config',$now_config);
        $this->assign('res',$res);
        $this->assign('content_base',$content_base);
        $this->assign('content_info',$content_info);
        $this->display();
    }

    public function save_grade_content(){
        $data = array();
        $content_id = $_POST['id'];

        if(!$_POST['level1']){
            $this->error('请选择基础价值');
        }
        if(!$_POST['level2']){
            $this->error('请选择手动加权');
        }
        $data['value'] = array(
            1 => $_POST['level1'],
            2 => $_POST['level2'],
        );
        if(!empty($_POST['level4'])){
            $data['value'][4] = $_POST['level4'];
        }
        $data['value'] = json_encode($data['value']);
        $data['update_tm'] = time();
        $content_platform = D('Dev.ContentPlatform');
        $content = $content_platform->table('content_platform_content')->where("id = $content_id")->field('id,content_id,pass_tm,user_id,type')->find();

        $has_info = $content_platform->table('content_platform_content_config')->where("content_id = {$content['content_id']} and type ={$content['type']}")->find();
        if($has_info){
            $res = $content_platform->table('content_platform_content_config')->where("content_id = {$content['content_id']} and type ={$content['type']}")->save($data);
            $type = 'edit';
        }else{
            $data['content_id'] = $content['content_id'];
            $data['type'] = $_POST['type'];
            $res = $content_platform->table('content_platform_content_config')->add($data);
            $type = 'add';
        }
        if($res){
            //$res1 = $this->count_cost($content_id,$data,$content);
            //该内容的配置改为已配置
           // if($res1)
            $content_platform->table('content_platform_content')->where("id = {$content_id}")->save(array('parm_status'=>1));
            $user_id = $content['user_id'];
            $content_platform->relate_user_settle_status($user_id);
            $this->writelog("安智内容合作平台:内容评分:配置内容ID为{$content_id}的系数为{$data['value']}", 'content_platform_parm', $content_id,__ACTION__ ,"",$type);
            $url = "content_settle/type/{$_POST['type']}";
            if(!empty($_POST['username'])&&!empty($_POST['month'])){
                $month = date("Ym",strtotime($_POST['month'].'-01'));
                $url .= "/month/{$month}/username/{$_POST['username']}";
            }
            $_SERVER["HTTP_REFERER"] = $url;
            $this->success('保存成功');
        }else{
            $this->error('保存失败');
        }
    }

    //计算内容每月需结算钱数
    public function count_cost($content_id, $data,$res){
        $data = json_decode($data['value'], true);
        $type = $data['type'];
        $content_platform = D('Dev.ContentPlatform');
        $month = date("Ym",$res['pass_tm']);
        $param_config = $content_platform->table('content_platform_parm_config')->where("month = {$month}")->find();
        $param_config['value'] = json_decode($param_config['value'], true);
        //基础价值
        $level1_value = $param_config['value'][1][$data[1]]['value'];
        if(!$level1_value){
            $this->error('基础价值未配置');
        }
        //手动加权
        $level2_value = $param_config['value'][2][$data[2]]['value'];
        if(!$level2_value){
            $this->error('手动加权未配置');
        }
        //灵活系数
        if($type == 1){
            //图文浏览量
            $level3_value_1 = explode(';',$param_config['value'][3][5]['value']);
            //图文下载量
            $level3_value_2 = explode(';',$param_config['value'][3][6]['value']);
        }else{
            //视频播放量
            $level3_value_1 = explode(';',$param_config['value'][3][7]['value']);
            //视频下载量
            $level3_value_2 = explode(';',$param_config['value'][3][8]['value']);
        }
        //额外奖励
        $level4_value = 0;
        foreach($data[4] as $v){
            $level4_value += $param_config['value'][4][$v]['value'];
        }

        $num_info = $content_platform->table('content_platform_statis')->where("content_id = {$content_id} and pid = 1")->field('sum(visit_deduct_num) as visit_num,sum(down_deduct_num) as down_num')->find();

        //内容统计量
        $content_settle = $content_platform->table('content_platform_content_settle')->where("c_p_cont_id = {$content_id}")->field("sum(settle_num) as settle_num")->find();

        //文章已产生的结算总金额
        $content_settle_all = $content_settle['settle_num']?$content_settle['settle_num']:0;
        $content_settle_all = $content_settle_all/100; //数据库单位为分，此处转为元
        //内容最大金额
        $max = $level1_value * $level2_value *(1+$level3_value_1[2]+$level3_value_2[2])+$level4_value;
        $total = 0;
        //内容浏览量系数
        $now_visit_param = floor($num_info['visit_num']/$level3_value_1[0])*$level3_value_1[1];
        if($now_visit_param>$level3_value_1[2]){
            $now_visit_param = $level3_value_1[2];
        }
        //内容下载量系数
        $now_down_num = floor($num_info['down_num']/$level3_value_2[0])*$level3_value_2[1];
        if($now_down_num>$level3_value_2[2]){
            $now_down_num = $level3_value_2[2];
        }
        $level3_value = 1+ $now_visit_param + $now_down_num;

        $total = $level1_value * $level2_value * $level3_value + $level4_value;

        if($total>$max){
            $now_month_settle = $max-$content_settle_all;
        }else{
            $now_month_settle = $total-$content_settle_all;
        }

        if($now_month_settle > 0){
            $insert_data = array(
                'user_id' => $res['user_id'],
                'c_p_cont_id' => $content_id,
                'settle_month' => $month,
                'settle_num' => $now_month_settle*100, //数据库单位为分
                'create_tm' => time()
            );

            $res = $content_platform->table('content_platform_content_settle')->add($insert_data);
            if($res){
                return true;
            }else{
                return false;
            }
        }
        return true;
    }


    public function content_settle_pub($user_id='',$list,$type){
        $content_platform = D('Dev.ContentPlatform');
        $this->assign('url', base64_decode($_GET['url']));
        $base_ids = $content_ids = $video_ids = array();
        $user = array();
        $month = array();
        $ids = array();
        $content_filed = 'content_id';

        foreach($list as $k=>$v){
            if($v['type'] == 1){
                $content_ids[] = $v[$content_filed];
            }else{
                $video_ids[] = $v[$content_filed];
            }
            if($type == 1){
                $v['month'] = $v['settle_month'];
            }else{
                $v['month'] = date("Ym",$v['create_tm']);
            }

            $month[] = $v['month'];
            $v['settle_num'] = $v['settle_num']/100;
            $list[$k] = $v;
            $user[] = $v['user_id'];
            $ids[] = $v['id'];
            $base_ids[] = $v['content_id'];
        }
        $ids_str = implode("','",$ids);
        if($type == 2){
            //内容评分需显示每条内容总金额
            $settle_info = $content_platform->table('content_platform_content_settle')->where("c_p_cont_id in ('{$ids_str}')")->field('sum(settle_num) as total_num,c_p_cont_id')->group('c_p_cont_id')->select();

            $settle_arr = array();
            foreach($settle_info as $sk=>$sv){
                $settle_arr[$sv['c_p_cont_id']] = $sv['total_num']/100;
            }
            $this->assign('settle_arr',$settle_arr);
        }

        if($video_ids){
            $video = $content_platform->get_video(array('id'=>$video_ids),'id,video_title,video_pic,add_tm');
            $this->assign('video', $video);
        }
        if($content_ids){
            $content = $content_platform->get_content(array('id'=>$content_ids),'id,title,explicit_pic,create_tm');
            $this->assign('content', $content);
        }
        $param = $content_platform->get_parm_config(array('month'=>$month));
        foreach($param as $k=>$v){
            $v['value'] = json_decode($v['value'], true);
            $param[$k] = $v;
        }
        $this->assign('param', $param);
        $base_str = implode("','",$base_ids);
        $config = $content_platform->table('content_platform_content_config')->where("content_id in('$base_str')")->select();
        $format_config = array();
        foreach($config as $k=>$v){
            $v['value'] = json_decode($v['value'], true);
            $format_config[$v['type']][$v['content_id']] = $v;
        }
        $this->assign('config',$format_config);
        if($user_id){
            $user = $content_platform->get_user(array('userid'=>$user_id), 'userid,username');
            $this->assign('username',$user[$user_id]['username']);
        }else{
            $user_str = implode("','", $user);
            $users = $content_platform->get_user(array('userid'=>array('exp'," in('{$user_str}')")), 'userid,username');
            $this->assign('users',$users);
        }
        $this->assign('m_anzhi_host',$this->m_anzhi_host);
        $this->assign('list', $list);

    }

    /**
     * @desc 页面权限设置
     */
    public function permission_config(){
        $model = M('');
        $rs = $model->table('pu_config')->where("configname = 'content_platform_permission_config'")->find();
        $rs = $save_data = json_decode($rs['configcontent'], true);
        $change_status = false;
        if(isset($_GET['content_status'])){
            if($_GET['content_status']==1){
                $msg = '启用了内容管理';
            }else{
                $msg = '停用了内容管理';
            }
            $save_data['content_status'] = $_GET['content_status'];
            $change_status = true;
        }
        if(isset($_GET['statis_status'])){
            if($_GET['statis_status']==1){
                $msg = '启用了数据统计';
            }else{
                $msg = '停用了数据统计';
            }
            $save_data['statis_status'] = $_GET['statis_status'];
            $change_status = true;
        }
        if(isset($_GET['settle_status'])){
            if($_GET['settle_status']==1){
                $msg = '启用了结算中心';
            }else{
                $msg = '停用了结算中心';
            }
            $save_data['settle_status'] = $_GET['settle_status'];
            $change_status = true;
        }
        if($change_status){

            $save_data = json_encode($save_data);
            $data = array(
                'configcontent' => $save_data,
                'uptime' => time()
            );
            $res = $model->table('pu_config')->where("configname = 'content_platform_permission_config'")->save($data);
            if($res){
                $this->writelog("安智内容合作平台:用户页面权限:{$msg}", 'pu_config', "content_platform_permission_config",__ACTION__ ,"",'edit');
                $this->success('保存成功');
            }else{
                $this->error('保存失败');
            }
        }
        $this->assign('rs', $rs);
        $this->display();
    }

    function test_dump($data){
        if($_SESSION['admin']['admin_user_name']=="吴乔军"){
            var_dump($data);
        }
    }
}