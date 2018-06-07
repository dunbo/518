<?php

class CustomListAction extends CommonAction {

    public function _initialize()
    {
        parent::_initialize();
        $data_type_map = array(
            "1" => '手动',
            "2" => '推荐',
            "3" => '最新',
            "4" => '最热',
            "5" => '单机',
            "6" => '预约',
            "101" => '精选',
            "102" => '视频',
            "103" => '应用',
            "104" => '游戏',
        );
        $this->assign('data_type_map', $data_type_map);
    }

    public function index() {
        $where = array();
        // 搜索条件
        $search_name = $_GET['search_name'];
        if ($search_name) {
            $where['name'] = array('like', "%{$search_name}%");
            $this->assign('search_name', $search_name);
        }
        if($_GET['channel_id']>0){
            $where['channel_id'] = $_GET['channel_id'];
            $this->assign('channel_id', $_GET['channel_id']);
        }
        $where['status'] = 1;        
        $model = M();
        import("@.ORG.Page");
        if(!$where['channel_id'])
            $where['channel_id'] = array('exp', ' != 27');
        $count = $model->table('sj_custom_list_name')->where($where)->count();
        $page = new Page($count, 10);
        $show = $page->show();//分页显示输出
        $list = $model->table('sj_custom_list_name')->where($where)->order('rank asc, id desc')->limit($page->firstRow.','.$page->listRows)->select();
        foreach ($list as $key => $record) {
            $id = $record['id'];
            // 统计sj_custom_list_name_soft表里关联此名称id的软件数
            $now = time();
            $where = array(
                'name_id' => $id,
                'end_time' => array('egt', $now),
                'status' => 1,
            );
            $count = $model->table('sj_custom_list_name_soft')->where($where)->count();
            $list[$key]['count'] = $count;  
        }
        $channel = $this->get_channel_data();
        $this->assign('channel', $channel);     
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    
    public function add_name() {
        if ($_POST) {
            $map = array();
            $name = $_POST['name'];
            if (!$name) {
                $this->error("自定义列表名称不能为空");
            }
            // 检查名称长度
            $mb_len = mb_strlen($name, 'utf-8');
            if ($mb_len > 5 || $mb_len < 2) {
                $this->error("自定义列表名称长度限制2-5个字符");
            }
            $map['name'] = $name;
            // 检查名称是否已存在
            $exists_id = $this->check_if_name_exists($name);
            if ($exists_id) {
                $this->error("自定义列表名称与id为{$exists_id}的列表重复");
            }
            $map['rank'] = $_POST['rank'];
            if( $_POST['channel_name']){
                $res = $this->check_channel_exists($_POST['channel_name'],$_POST['channel_id']);
                if(!$res){
                    $this->success("添加自定义频道失败！");
                }
                $map['channel_id'] = $_POST['channel_id'];
            }else{
                $map['channel_id'] = '';
                $map['rank'] = '';
            }
            
            $now = time();
            $map['create_time'] = $now;
            $map['update_time'] = $now;         
            
            $map['data_type'] = $_POST['data_type'];
            $map['status'] = 1;
            $map['admin_id'] = $_SESSION['admin']['admin_id'];
            $map['normal_type'] = $_POST['normal_type'] ? $_POST['normal_type'] : 0;
            $map['filter_installed_type'] = $_POST['filter_installed_type'] ? $_POST['filter_installed_type'] : 0;
            if($map['normal_type'] == 1) $map['filter_installed_type'] = 0;
            $model = M();
            $ret = $model->table('sj_custom_list_name')->add($map);
            if ($ret) {
                if($_POST['data_type']==3||$_POST['data_type']==4){
                    $s_data = array(
                        'name_id' => $ret,
                        'package' => 'cn.goapk.market',
                        'start_time' => time(),
                        'end_time' => strtotime("+10 year"),
                    );
                    $model->table('sj_custom_list_name_soft')->add($s_data);
                }
                $this->writelog("自定义列表：新增id为{$ret}的自定义列表",'sj_custom_list_name', $ret,__ACTION__ ,"","add");
                $this->success("添加成功！");
            } else {
                $this->success("添加失败！");
            }
        } else {
            $this->display('add_name');
        }
    }
    public function get_channel_data($channel_id=''){
        $model = M();
        if($channel_id){
            if(is_array($channel_id)){
                $where['id'] = array('in',$channel_id);
            }else{
                $where['id'] = $channel_id;
            }
        }
        $where['status'] = 1;
        return $model->table('sj_custom_list_channel')->where($where)->getField('id,channel_name');     
    }
    public function check_channel_exists($channel_name,$channel_id = ''){
        $model = M();
        $channel_name = trim($channel_name);
        if($channel_name){
              $where = array(
                'channel_name' => $channel_name,
                //'status' => 1,
            );
            $channel = $model->table('sj_custom_list_channel')->where($where)->find();
            if(!$channel){
                $map['channel_name'] = trim($channel_name);
                $map['create_time'] = time();
                $map['admin_id'] = $_SESSION['admin']['admin_id'];
                $res = $model->table('sj_custom_list_channel')->add($map);
                if($res){$_POST['channel_id'] = $res;}
            }else{
                $_POST['channel_id'] = $channel['id'];
            }
            return true;
        }else{
            return false;
        }

    }
    public function edit_name() {
        if ($_POST) {
            $map = array();
            $name = $_POST['name'];
            if (!$name) {
                $this->error("名称不能为空");
            }
            // 检查名称长度
            $mb_len = mb_strlen($name, 'utf-8');
            if ($mb_len > 10) {
                $this->error("名称长度限制10字以内");
            }
            $map['name'] = $name;
            // 检查名称是否已存在
            $id = $_POST['id'];
            $exists_id = $this->check_if_name_exists($name, $id);
            if ($exists_id) {
                $this->error("名称与id为{$exists_id}的列表重复");
            }
            $map['rank'] = $_POST['rank'];
            if( $_POST['channel_name']){
                $res = $this->check_channel_exists($_POST['channel_name'],$_POST['channel_id']);
                if(!$res){
                    $this->success("添加自定义频道失败！");
                }
                $map['channel_id'] = $_POST['channel_id'];
            }else{
                $map['channel_id'] = '';
                $map['rank'] = '';
            }
            
            $now = time();
            $map['update_time'] = $now;
            $map['admin_id'] = $_SESSION['admin']['admin_id'];
            
            $map['data_type'] = $_POST['data_type'];
            $map['normal_type'] = $_POST['normal_type'] ? $_POST['normal_type'] : 0;
            $map['filter_installed_type'] = $_POST['filter_installed_type'] ? $_POST['filter_installed_type'] : 0;
            if($map['normal_type'] == 1) $map['filter_installed_type'] = 0;
            $model = M();
            $where = array(
                'id' => $id,
                'status' => 1,
            );
            $log = $this->logcheck($where, 'sj_custom_list_name', $map, $model);
            $ret = $model->table('sj_custom_list_name')->where($where)->save($map);
            if ($ret) {
                $this->writelog("自定义列表：编辑了id为{$id}的自定义列表，{$log}",'sj_custom_list_name', $id,__ACTION__ ,"","edit");
                $this->success("编辑成功！");
            } else {
                $this->error("编辑失败！");
            }
        } else if ($_GET) {
            $id = $_GET['id'];
            $where = array(
                'id' => $id,
                'status' => 1,
            );
            $model = M();
            $find = $model->table('sj_custom_list_name')->where($where)->find();
            $channel = $this->get_channel_data($find['channel_id']);
            // $find['rank']=in_array($find['data_type'], array(1,2,3,4,101,102,103,104))?$find['rank']:'';
            $this->assign('channel', $channel);
            $this->assign('list', $find);
            $this->display('add_name');
        }
    }
    
    public function delete_name() {
        if ($_GET) {
            $id = $_GET['id'];
            $map = array();
            $now = time();
            $map['status'] = 0;
            $map['update_time'] = $now;
            $model = M();
            $where = array(
                'id' => $id,
                'status' => 1,
            );
            $ret = $model->table('sj_custom_list_name')->where($where)->save($map);
            if ($ret) {
                $this->writelog("自定义列表：删除了id为{$id}的自定义列表",'sj_custom_list_name', $id,__ACTION__ ,"","del");
                $this->success("删除成功！");
            } else {
                $this->success("删除失败！");
            }
        }
    }
    
    function check_if_name_exists($name, $except_id = 0) {
        $where = array(
            'name' => $name,
            'status' => 1,
        );
        if ($except_id) {
            $where['id'] = array('neq', $except_id);
        }
        $model = M();
        $find = $model->table('sj_custom_list_name')->where($where)->find();
        if ($find) {
            return $find['id'];
        }
        return 0;
    }
    
    public function list_content() {
        $model = M();
        $name_id = $_GET['name_id'];
        // 查找name
        $where = array(
            'id' => $name_id,
        );
        $find = $model->table('sj_custom_list_name')->where($where)->find();
        $name = $find['name'];
        // 查找该名称列表下的软件
        $where = array();
        $where['name_id'] = $name_id;
        $where['status'] = 1;
        
        // 列表排序
        $order = '';
        
        $period = $_GET['period'];
        $now = time();
        if ($period == 1) {
            $where['end_time'] = array('lt', $now);
            //$order = 'end_time desc';
            $order = 'start_time asc';
        } else if ($period == 3) {
            $where['start_time'] = array('gt', $now);
            $order = 'rank asc,start_time asc';
        } else {
            $where['start_time'] = array('elt', $now);
            $where['end_time'] = array('egt', $now);
            $period = 2;
            $order = 'rank asc,start_time asc';
        }
        $this->assign('period', $period);
        
        import("@.ORG.Page");
        $count = $model->table('sj_custom_list_name_soft')->where($where)->count();
        $page = new Page($count, 10);
        $show = $page->show();//分页显示输出
        $list = $model->table('sj_custom_list_name_soft')->where($where)->order($order)->limit($page->firstRow.','.$page->listRows)->select();
        
        foreach ($list as $key => $record) {
            // 查找软件名称
            $package = $record['package'];
            $where = array(
                'package' => $package,
                'status' => 1,
            );
            $find = $model->table('sj_soft')->where($where)->order('version_code desc')->find();
            $list[$key]['softname'] = $find['softname'];
        }
        
        $this->assign('name', $name);
        $this->assign('name_id', $name_id);
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    
    public function add_content() {
        if ($_POST) {
            $map = array();
            $name_id = $_POST['name_id'];
            if (!$name_id) {
                $this->error("添加失败");
            }
            $map['name_id'] = $name_id;
            $package = $_POST['package'];
            if (!$package) {
                $this->error("包名不能为空");
            }
            // 检查包名是否存在
            if (!ContentTypeModel::convertPackage2Softname($package)) {
                $this->error("包名不存在");
            }
            $map['package'] = $package;
            $beid = $_POST['beid'] ? $_POST['beid'] : 0;
            if (!preg_match('/^\d+$/', $beid)) {
                $this->error("行为id请填写大于等于0的整数");
            }
            $map['beid'] = $beid;
            $rank = $_POST['rank'];
            if (!$rank) {
                $this->error("排序不能为空");
            }
            $map['rank'] = $rank;
            $start_time = $_POST['start_time'];
            if (!$start_time) {
                $this->error("开始时间不能为空");
            }
            $start_time = strtotime($start_time);
            $map['start_time'] = $start_time;
            $end_time = $_POST['end_time'];
            if (!$end_time) {
                $this->error("结束时间不能为空");
            }
            $end_time = strtotime($end_time);
            $map['end_time'] = $end_time;
            $map['admin_id'] = $_SESSION['admin']['admin_id'];
            if ($start_time >= $end_time) {
                $this->error("结束时间需大于开始时间");
            }
             $life=$_POST['life'];
            // 排期冲突检查：检查排序
            $rank_conflicts_id = $this->check_if_conflicts($name_id, 'rank', $rank, $start_time, $end_time, $life);
            if ($rank_conflicts_id) {
                $this->error("与id为{$rank_conflicts_id}的记录排序相同！");
            }
            // 排期冲突检查：检查包名
            $package_conflicts_id = $this->check_if_conflicts($name_id, 'package', $package, $start_time, $end_time, $life);
            if ($package_conflicts_id) {
                $this->error("与id为{$package_conflicts_id}的记录排期有冲突！");
            }
            
            $model = M();
            $ret = $model->table('sj_custom_list_name_soft')->add($map);
            if ($ret) {
                $this->writelog("自定义列表：添加了id为{$ret}的软件",'sj_custom_list_name_soft', $ret,__ACTION__ ,"","add");
                $this->success("添加成功！");
            } else {
                $this->error("添加失败！");
            }
            
        } else if ($_GET) {
            $name_id = $_GET['name_id'];
            $this->assign('name_id', $name_id);
            $this->display();
        }
    }
    
    public function edit_content() {
        if($_GET['edit_batch']==1){
            $idlist=rtrim($_GET['idlist'],',');
            $this->assign('idlist',$idlist);

            $num=count(explode(',',$idlist));

            $this->assign('num',$num);
            $this->display('edit_all');
            return;
        }
        if($_POST['batch_edit_do']==1){
            $this->batch_edit_content_do();
            return;
        }
        if ($_POST) {
            $model = M();
            $map = array();
            $id = $_POST['id'];
            if (!$id) {
                $this->error("编辑失败");
            }
            $package = $_POST['package'];
            if (!$package) {
                $this->error("包名不能为空");
            }
            // 检查包名是否存在
            if (!ContentTypeModel::convertPackage2Softname($package)) {
                $this->error("包名不存在");
            }
            $map['package'] = $package;
            $beid = $_POST['beid'] ? $_POST['beid'] : 0;
            if (!preg_match('/^\d+$/', $beid)) {
                $this->error("行为id请填写大于等于0的整数");
            }
            $map['beid'] = $beid;
            $rank = $_POST['rank'];
            if (!$rank) {
                $this->error("排序不能为空");
            }
            $map['rank'] = $rank;
            $start_time = $_POST['start_time'];
            if (!$start_time) {
                $this->error("开始时间不能为空");
            }
            $start_time = strtotime($start_time);
            $map['start_time'] = $start_time;
            $end_time = $_POST['end_time'];
            if (!$end_time) {
                $this->error("结束时间不能为空");
            }
            $end_time = strtotime($end_time);
            $map['end_time'] = $end_time;
            $map['admin_id'] = $_SESSION['admin']['admin_id'];
            if ($start_time >= $end_time) {
                $this->error("结束时间需大于开始时间");
            }
            $life=$_POST['life'];
            //已过期的信息复制上线判断
            if($_POST['life']==1)
            {
              if( $end_time<time())
              {
                $this->error("您修改的复制上线的日期还是无效日期");
              }
            }
            // 检查排序
            // 先取得编辑内容所在的名称id
            $find = $model->table('sj_custom_list_name_soft')->where(array('id'=>$id))->find();
            $name_id = $find['name_id'];
            $rank_conflicts_id = $this->check_if_conflicts($name_id, 'rank', $rank, $start_time, $end_time, $id, $life);
            if ($rank_conflicts_id) {
                $this->error("与id为{$rank_conflicts_id}的记录排序相同！");
            }
            
            // 检查排期有重叠的是否有相同包名
            $package_conflicts_id = $this->check_if_conflicts($name_id, 'package', $package, $start_time, $end_time, $id, $life);
            if ($package_conflicts_id) {
                $this->error("与id为{$package_conflicts_id}的记录排期有冲突！");
            }
            
            $where = array(
                'id' => $id,
            );
            $log = $this->logcheck($where, 'sj_custom_list_name_soft', $map, $model);
            //已过期的信息复制上线判断
            if($_POST['life']==1)
            {
               $select = $model->table('sj_custom_list_name_soft')->where($where)->select();
               $map['name_id']=$select[0]['name_id'];
               $ret = $model->table('sj_custom_list_name_soft')->add($map);
               if ($ret || $ret === 0) 
               {
                    if ($ret) 
                    {
                        $this->writelog("自定义列表：编辑了id为{$id}的软件，{$log}",'sj_custom_list_name_soft', $id,__ACTION__ ,"","edit");
                    }
                        $this->success("复制上线成功");
                }
                else 
                {
                    $this->error("复制上线失败");
                }
            }
            else
            {
                $ret = $model->table('sj_custom_list_name_soft')->where($where)->save($map);
                if ($ret || $ret === 0) {
                    if ($ret) {
                        $this->writelog("自定义列表：编辑了id为{$id}的软件，{$log}",'sj_custom_list_name_soft', $id,__ACTION__ ,"","edit");
                    }
                    $this->success("编辑成功！");
                } else {
                    $this->success("编辑失败！");
                }
            }
        } else if ($_GET) {
            $id = $_GET['id'];
            
            $where = array(
                'id' => $id,
                'status' => 1,
            );
            $model = M();
            $find = $model->table('sj_custom_list_name_soft')->where($where)->find();
            
            $this->assign('record', $find);
            $this->display();
        }
    }
    function batch_edit_content_do(){
        //批量编辑执行
        $idlist=explode(',', $_POST['idlist']);
        // $start_time=$_POST['start_time'];
        // $end_time=$_POST['end_time'];
        $model = M('custom_list_name_soft');
        $datas = $model->where(array('id'=>array('in',$idlist)))->select();
        $error=array();
        foreach($datas as $k=>$v){

            $start_time=$_POST['start_time']?strtotime($_POST['start_time']):$v['start_time'];
            $end_time=$_POST['end_time']?strtotime($_POST['end_time']):$v['end_time'];

            $id=$v['id'];
            $rank=$v['rank'];
            $package=$v['package'];
            $name_id = $v['name_id'];
            
            if($start_time>$end_time){
                $error[$id]['package']=$package;
                $error[$id]['error']='结束时间不可早于开始时间';
                continue;
            }

            $rank_conflicts_id = $this->check_if_conflicts($name_id, 'rank', $rank, $start_time, $end_time, $id, 0);
            if ($rank_conflicts_id) {
                $error[$id]['package']=$package;
                $error[$id]['error']="此条id{$id}与id为{$rank_conflicts_id}的记录排序相同！";
                continue;
            }
            
            // 检查排期有重叠的是否有相同包名
            $package_conflicts_id = $this->check_if_conflicts($name_id, 'package', $package, $start_time, $end_time, $id, 0);
            if ($package_conflicts_id) {
                $error[$id]['package']=$package;
                $error[$id]['error']="此条id{$id}与id为{$package_conflicts_id}的记录排期有冲突！";
                continue;
            }
            $log = $this->logcheck(array('id'=>$id), 'sj_custom_list_name_soft', array('end_time'=>$end_time,'start_time'=>$start_time), $model);

            $ret = $model->where(array('id'=>$id))->save(array('end_time'=>$end_time,'start_time'=>$start_time));
            if ($ret || $ret === 0) {
                if ($ret) {
                    $this->writelog("自定义列表：编辑了id为{$id}的软件，{$log}",'sj_custom_list_name_soft', $id,__ACTION__ ,"","edit");
                }
            }
        }
        $re_error="";
        foreach ($error as $key => $value) {
            $re_error.=$value['package']."(".$value['error'].")"."<br>";
        }
        if($error_num=count($error)){
            $success_num=count($idlist)-$error_num;
            echo json_encode(array('code'=>0,'success_num'=>$success_num?$success_num:0,'error_num'=>$error_num,'error'=>$re_error));
            return;
        }else{
            echo json_encode(array('code'=>1));
            return;
        }

    }
    public function delete_content() {
        $id = $_GET['id'];
        $map = array(
            'status' => 0
        );
        $model = M();
        $ret = $model->table('sj_custom_list_name_soft')->where(array('id' => $id))->save($map);
        if ($ret) {
            $this->writelog("自定义列表：删除了id为{$id}的软件",'sj_custom_list_name_soft', $id,__ACTION__ ,"","del");
            $this->success("删除成功！");
        } else {
            $this->success("删除失败！");
        }
    }
    
    // 排期冲突检查：检查排期有重叠的某些字段不能相同，如排序，包名
    function check_if_conflicts($name_id, $column_name, $column_value, $start_time, $end_time, $except_id = 0, $life) {
        $where = array(
            'name_id' => $name_id,
            $column_name => $column_value,
            'start_time' => array('elt', $end_time),
            'end_time' => array('egt', $start_time),
            'status' => 1,
        );
        if ($except_id&&$life!=1) {
            $where['id'] = array('neq', $except_id);
        }
        
        $model = M();
        $find = $model->table('sj_custom_list_name_soft')->where($where)->find();
        if ($find) {
            return $find['id'];
        }
        return 0;
    }
    
    public function pub_show_channel_name(){
        $model = M();
        $channel_name = trim($_GET['query']);  
        $channel_name = escape_string($channel_name);       
        $where = array(
            'channel_name' => array('like', "%{$channel_name}%"),
            'status' => 1,
        );
        $list = $model->table('sj_custom_list_channel')->where($where)->select();
        $data = array(
            'query' => $channel_name,
            'suggestions' => array(),
            'data' => array(),
        );
        foreach($list as $v) {
            $data['suggestions'][] = "{$v['channel_name']}";
            $data['data'][] = $v['channel_name'];
        }       
        exit(json_encode($data));
    }



    // 批量导入访问的页面节点
    function import_softs() {
        $CustomList = D("Sj.CustomList");
        
        if ($_GET['down_moban']) {
            $CustomList->down_moban(1);
        } else if ($_FILES) {
            $err = $_FILES["upload_file"]["error"];
            if ($err) {
                $this->ajaxReturn($err,"上传文件错误，错误码为{$err}！", -1);
            }
            $file_name = $_FILES['upload_file']['name'];
            $tmp_arr = explode(".", $file_name);
            $name_suffix = array_pop($tmp_arr);
            if (strtoupper($name_suffix) != "CSV") {
                $this->ajaxReturn("",'请上传CSV格式文件！', -2);
            }
            $tmp_name = $_FILES['upload_file']['tmp_name'];
            $content_arr = $CustomList->import_file_to_array($tmp_name,$this);
            if ($content_arr == -1) {
                $this->ajaxReturn("",'文件打开错误，请检查文件是否损坏！', -3);
            } else if (empty($content_arr)) {
                $this->ajaxReturn("",'文件数据内容不能为空！', -4);
            }
            // 返回检查结果的错误信息，如果记录的flag为1表示有错误
            $error_msg = $CustomList->import_array_convert_and_check($content_arr,$this);
            $flag = true;
            foreach($error_msg as $key=>$value) {
                if ($value['flag'] == 1)
                    $flag = false;
            }

            if (!$flag) {
                $this->ajaxReturn($error_msg,'您上传的CSV有如下问题，请修改后重新上传！', -5);
            }
            // 判断后台有没有人正在导入
            $lock_name = 'sj_custom_list_name_soft_importing';
            // S($lock_name, NULL);die;
            $import_lock = S($lock_name);
            if ($import_lock) {
                $this->ajaxReturn("",'后台有人正在导入，请稍后再尝试！', 1);
            }
            // 上锁，设置60秒内有效
            S($lock_name, 1, 60, 'File');
            // 返回导入结果，如果记录的flag为0表示添加失败
            $result_arr = $this->process_import_array($content_arr);
            
            // 导入后解锁
            S($lock_name, NULL);
            $flag = true;
            $count=0;
            foreach($result_arr as $key=>$value) {
                if ($value['flag'] == 0)
                    $flag = false;
                if($value['flag'] == 1){
                    $count++;
                }
            }
            
            $save_dir = IMPORT_FILE_UPLOAD_PATH;
            $this->mkDirs($save_dir);
            $save_name = MODULE_NAME. '_' . ACTION_NAME . '_' . time() . '_' . $_SESSION['admin']['admin_id'] . '.csv';
            $save_file_name = $save_dir . $save_name;
            $ret = move_uploaded_file($_FILES['upload_file']['tmp_name'], $save_file_name);
            $this->writelog("自定义列表-批量添加软件：批量导入了{$save_file_name}。");
            if ($flag) {
                $this->ajaxReturn("","成功添加{$count}款软件", 0);
            } 
        } else {
            $this->display("import_softs");
        }
    }
    
    // 业务逻辑：将批量导入文件里所有数据添加进数据库，返回结果为每一行添加是否成功标志符
    function process_import_array($content_arr) {
        $result_arr = array();
        $extent_soft_model = M('custom_list_name_soft');
        foreach($content_arr as $key => $record) {

            $map = array();
            // 设置默认值
            $map['status'] = 1;

            $map['admin_id'] = $_SESSION['admin']['admin_id'];
            // 赋值，以下为必填的值
            $map['name_id'] = $record['name_id'];
            $map['package'] = $record['package'];
            $map['beid'] = $record['beid'];
            $map['rank'] = $record['rank'];
            $map['start_time'] = strtotime($record['start_time']);
            $map['end_time'] = strtotime($record['end_time']);
            // 添加到表中
            if ($id = $extent_soft_model->add($map)) {
                $this->writelog("在区间[{$record['name_id']}]中添加了软件[{$record['package']}],排序为[{$record['rank']}],开始时间为:[{$record['start_time']}],结束时间为:[{$record['end_time']}]的记录",'sj_custom_list_name_soft',$id,__ACTION__ ,"","add");
                $result_arr[$key]['flag'] = 1;
                $result_arr[$key]['msg'] = "添加成功";
            }else {
                //未知原因添加失败
                $result_arr[$key]['flag'] = 0;
                $result_arr[$key]['msg'] = "添加失败";
            }
        }
        return $result_arr;
    }
    

    function handwriting_convert_and_check(&$content_arr) {
        $CustomList = D("Sj.CustomList");
        // 初始化错误信息数组
        $error_msg = array();
        foreach($content_arr as $key => $value) {
            $CustomList->init_error_msg($error_msg, $key);
        }

        // 业务逻辑：将表里字段名称和模版里列的名称一一对应
        $correct_title_arr = array(
            'name_id'     =>  '区间ID',
            'package'  =>   '包名',
            'rank'  =>   '排序',
            'start_time'  =>   '开始时间(yyyy/MM/dd)',
            'end_time'  =>   '结束时间(yyyy/MM/dd)', 
            'beid'  => '行为id',
        );
        // trim一下所有的数据
        foreach($content_arr as $key=>$record) {
            foreach($record as $r_key=>$r_record) {
                $content_arr[$key][$r_key] = trim($r_record);
            }
        }
        // 给$content_arr里的每一行记录的每一列下标由数字改成对应名称
        $new_content_arr = array();
        $new_key = array();
        // 将$correct_title_arr里的key值提取出来依次放在$new_key里
        foreach($correct_title_arr as $key => $value) {
            $new_key[] = $key;
        }
        foreach($content_arr as $key=>$record) {
            foreach($new_key as $new_key_key=>$new_key_value) {
                if (isset($record[$new_key_key])) {
                    $new_content_arr[$key][$new_key_value] = $record[$new_key_key];
                }
            }
        }
        $content_arr = $new_content_arr;
                
        foreach($content_arr as $key=>$record) {
            // 开始检查每列内容是否为预期内容
            foreach($record as $r_key=>$r_value) {
                // 自动填充批量导入的时间
                if ($r_key == 'start_time' || $r_key == 'end_time') {
                    if ($r_key == 'start_time') {
                        $type = 0;
                        $hint = '开始';
                    } elseif($r_key == 'end_time') {
                        $type = 1;
                        $hint = '结束';
                    }
                    //批量投放 统一修改时间带T
                    if (!preg_match('/^T/', $content_arr[$key][$r_key])) {
                        $CustomList->append_error_msg($error_msg, $key, 1, "{$hint}时间前必须加“T”;");
                    } else {
                        $content_arr[$key][$r_key] = preg_replace('/^T/', '', $content_arr[$key][$r_key]);
                    }
                    
                    $ret = $this->auto_convert_time($content_arr[$key][$r_key], $type);
                    if ($ret) {
                        $content_arr[$key][$r_key] = $ret;
                    }
                }
            }
        }
        
        return $error_msg;
    }

    // 统一的逻辑检查：检查添加软件数据是否合法
    function logic_check($content_arr) {
        $CustomList = D("Sj.CustomList");
        // 初始化错误信息数组
        $error_msg = array();
        foreach($content_arr as $key => $value) {
            $CustomList->init_error_msg($error_msg, $key);
        }
        // 业务逻辑：区间表、区间软件表
        $M_extent_table = 'custom_list_name';
        $M_extent_soft_table = 'custom_list_name_soft';
        
        // 获得三个表的model
        $extent_model = M($M_extent_table);
        $extent_soft_model = M($M_extent_soft_table);
        
        // 业务逻辑：以下为各项具体检查

        foreach($content_arr as $key=>$record) {

            // 检测区间ID
            if (isset($record['name_id']) && $record['name_id'] != "") {
                $where = array(
                    'id' => array('EQ', $record['name_id']),
                    'status' => array('EQ', 1),
                    'data_type' => array('EQ', 1),
                );
                $find = $extent_model->where($where)->find();
                if (!$find) {
                    $CustomList->append_error_msg($error_msg, $key, 1, "自定义列表ID【{$record['name_id']}】不存在;");
                }
            }else{
                $CustomList->append_error_msg($error_msg, $key, 1, "自定义列表ID不能为空;");
            }
            
            // 检查行为id
            if (isset($record['beid']) && $record['beid'] != "") {
                if (!preg_match("/^\d+$/", $record['beid'])) {
                    $CustomList->append_error_msg($error_msg, $key, 1, "行为id应为大于等于0的整数;");
                }
            }
            
            // 检查排序是否为数字
            if (isset($record['rank']) && $record['rank'] != "") {
                if (!preg_match("/^\d+$/", $record['rank'])) {
                    $CustomList->append_error_msg($error_msg, $key, 1, "排序应为整数;");
                }
            } else {
                $CustomList->append_error_msg($error_msg, $key, 1, "排序不能为空;");
            }

            // 检查开始时间
            if (isset($record['start_time']) && $record['start_time'] != "") {
                if (!preg_match("/^\d{4}(\-|\/|\.)\d{1,2}(\-|\/|\.)\d{1,2}\s+\d{1,2}:\d{1,2}:\d{1,2}$/", $record['start_time'])) {
                    $CustomList->append_error_msg($error_msg, $key, 1, "开始时间日期格式不对;");
                } 
                else {
                    $time = strtotime($record['start_time']);
                    if ($time) {
                        $content_arr[$key]['bk_start_time'] = $time;
                    } else {
                        $CustomList->append_error_msg($error_msg, $key, 1, "开始时间日期格式不对;");
                    }
                }
            } else {
                $CustomList->append_error_msg($error_msg, $key, 1, "开始时间不能为空;");
            }
            // 检查结束时间
            if (isset($record['end_time']) && $record['end_time'] != "") {
                if (!preg_match("/^\d{4}(\-|\/|\.)\d{1,2}(\-|\/|\.)\d{1,2}\s+\d{1,2}:\d{1,2}:\d{1,2}$/", $record['end_time'])) {
                    $CustomList->append_error_msg($error_msg, $key, 1, "结束时间日期格式不对;");
                }
                else {
                    $time = strtotime($record['end_time']);
                    if ($time) {
                        $content_arr[$key]['bk_end_time'] = $time;
                    } else {
                        $CustomList->append_error_msg($error_msg, $key, 1, "结束时间日期格式不对;");
                    }
                }
            } else {
                $CustomList->append_error_msg($error_msg, $key, 1, "结束时间不能为空;");
            }
            
            // 检查开始时间是否小于结束时间
            if (isset($content_arr[$key]['bk_start_time']) && isset($content_arr[$key]['bk_end_time'])) {
                if ($content_arr[$key]['bk_start_time']> $content_arr[$key]['bk_end_time']) {
                    $CustomList->append_error_msg($error_msg, $key, 1, "开始时间需小于结束时间;");
                }
            }
            if (!ContentTypeModel::convertPackage2Softname($content_arr[$key]['package'])) {
                $CustomList->append_error_msg($error_msg, $key, 1, "包名【{$content_arr[$key]['package']}】不存在于市场软件库中;");
            }
        }

        // 检查行与行之间的数据是否冲突（主要检查相同包名的区间是否有冲突）
        foreach($content_arr as $key1=>$record1) {

            // 如果开始时间或结束时间无效，则不比较
            if (!isset($record1['bk_start_time']) || !isset($record1['bk_end_time']))
                continue;
            foreach($content_arr as $key2=>$record2) {
                // 比较过的不比较
                if ($key1 >= $key2)
                    continue;
                
                if ($record1['package'] != $record2['package'] && $record1['rank'] != $record2['rank'])
                        continue;

                // 如果开始时间或结束时间无效，则不比较
                if (!isset($record2['bk_start_time']) || !isset($record2['bk_end_time']))
                    continue;

                if ($record1['package'] == $record2['package'] && $record1['bk_start_time'] <= $record2['bk_end_time'] && $record2['bk_start_time'] <= $record1['bk_end_time']&& $record2['name_id'] == $record1['name_id']) {
                    $k1 = $key1 + 1; $k2 = $key2 + 1;
                    $CustomList->append_error_msg($error_msg, $key1, 1, "包名相同:投放区间与第{$k2}行有重叠;");
                    $CustomList->append_error_msg($error_msg, $key2, 1, "包名相同:投放区间与第{$k1}行有重叠;");
                   
                }
                if ($record1['rank'] == $record2['rank'] && $record1['bk_start_time'] <= $record2['bk_end_time'] && $record2['bk_start_time'] <= $record1['bk_end_time']&& $record2['name_id'] == $record1['name_id']) {
                    $k1 = $key1 + 1; $k2 = $key2 + 1;
                    $CustomList->append_error_msg($error_msg, $key1, 1, "排序相同:投放区间与第{$k2}行有重叠;");
                    $CustomList->append_error_msg($error_msg, $key2, 1, "排序相同:投放区间与第{$k1}行有重叠;");
                    
                }
            }
        }

        // 检查每一行数据是否与数据库的存储内容相冲突
        foreach($content_arr as $key=>$record) {

            
            if (!isset($record['bk_start_time']) || !isset($record['bk_end_time']))
                continue;
            
                $where = array(
                    'name_id' => $record['name_id'],
                    'start_time' => array('elt', $record['bk_end_time']),
                    'end_time' => array('egt', $record['bk_start_time']),
                    'status' => 1,
                );
                
                $db_records = $extent_soft_model->where($where)->select();
                foreach($db_records as $db_record){

                    if($db_record['package'] == $record['package'] || $db_record['rank'] == $record['rank']){
                        $start_at_str = date('Y-m-d H:i:s',$db_record['start_time']);
                        $end_at_str = date('Y-m-d H:i:s',$db_record['end_time']);
                        $extent_data = $extent_model->where(array('id'=>$db_record['name_id']))->field('*')->find();
                        if($db_record['package'] == $record['package']){
                            $CustomList->append_error_msg($error_msg, $key, 1, "包名相同:投放区间与后台区间【{$extent_data['name']}】里ID为【{$db_record['id']}】，包名为【{$db_record['package']}】的记录有重叠（该投放时间从【{$start_at_str}】到【{$end_at_str}）,排序为{$db_record['rank']};");
                        }
                        if($db_record['rank'] == $record['rank']){
                            $CustomList->append_error_msg($error_msg, $key, 1, "排序相同:投放区间与后台区间【{$extent_data['name']}】里ID为【{$db_record['id']}】，包名为【{$db_record['package']}】的记录有重叠（该投放时间从【{$start_at_str}】到【{$end_at_str}）,排序为{$db_record['rank']};");
                        }
                        
                    }

                }

        }

        return $error_msg;
    }


    


    
}

?>