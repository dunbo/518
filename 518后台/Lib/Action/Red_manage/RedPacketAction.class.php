<?php

class RedPacketAction extends CommonAction{
    public $savedir;
    public $intformat;
    
    public function _initialize() {
        parent::_initialize();
		$this->savedir = C('RED_PACKET_CSV');
        //如果为0 则是以元为单位 如果为1 则是以分为单位
        $this->intformat = 0;
	}
    
    function showlist(){
        $model = D('Red_manage.RedPacketConf');
        $timemodel = D('Red_manage.RedpacketTimeConf');
        $typemodel = D('Red_manage.RedpkTasktype');
        import("@.ORG.Page");
        
        $ptypes = $typemodel -> select();
        foreach($ptypes as $type){
            $packtypes[$type['id']] = $type;
            
        }
        $wherearr['status'] = 1;
        if(!empty($_GET['pname'])){
            $_GET['pname'] = trim($_GET['pname']);
            $wherearr['pname'] = array('like', "%{$_GET['pname']}%"); 
            $this->assign('pname',$_GET['pname']);
        }
        if(!empty($_GET['id'])){
            $wherearr['id'] = intval($_GET['id']);
            $this->assign('id',$_GET['id']);
        }
        if(!empty($_GET['tasktype'])){
            $wherearr['tasktype'] = intval($_GET['tasktype']);
            $this->assign('tasktype',$_GET['tasktype']);
        }
        if(!empty($_GET['givetype'])){
            $wherearr['givetype'] = intval($_GET['givetype']);
            $this->assign('givetype',$_GET['givetype']);
        }
        $count = $model->where($wherearr)->count();
		
        $prepage = isset($_GET['lr']) ? $_GET['lr'] : 10;
		$Page = new Page($count,$prepage);
        
        $list = $model->where($wherearr)->order('id desc')->limit("{$Page->firstRow},{$Page->listRows} ")->select();
        foreach($list as $pack){
            $pack['typename'] =$packtypes[$pack['tasktype']]['ptypename'];
            $pack['lasttime'] = date('Y-m-d H:i:s',$pack['lasttime']);
            $pack['bindstatus'] = $pack['taskid']>0 ||$pack['activeid']>0 ? '1' :'0';
            $pack['bindtxt'] = $pack['bindstatus'] ? '已绑定' :'未绑定';
            if($pack['taskid']){
                $pack['bindtxt'] .= "<br>任务id:{$pack['taskid']}";
            }
            if($pack['activeid']){
                $pack['bindtxt'] .= "<br>活动id:{$pack['activeid']}";
            }
            if($pack['actpackage']){
                $pack['bindtxt'] .= "<br>软件包名:{$pack['actpackage']}";
            }
            
            
            $pack['numpritxt'] = $pack['numpri'] ? '是' :'否';
            if($this->intformat == 1){
                $pack['oldmon'] = $pack['oldmon']/100;
                $pack['cashmin'] = $pack['cashmin']/100;
                $pack['cashmax'] = $pack['cashmax']/100;
                $pack['getmon'] = $pack['getmon']/100;
                $pack['restmon'] = $pack['restmon']/100;
                $pack['dismon'] = $pack['dismon']/100;
                $pack['totalmon'] = $pack['totalmon']/100;
                
                
            }
            $redpacks[$pack['id']] = $pack;
            
        }
        
        // echo $timemodel->getLastSql();
        $show = $Page->show();
        $Page->setConfig('header', '篇记录');
		$Page->setConfig('`first', '<<');
		$Page->setConfig('last', '>>');
        $this -> assign("page", $show);
        $this->assign('list',$redpacks);
        $this->assign('packtypes',$packtypes);
        $this->display();
    }
    
    function add(){
        $model = D('Red_manage.RedPacketConf');
        $timemodel = D('Red_manage.RedpacketTimeConf');
        $usedtimemodel = D('Red_manage.RedpacketPertimeUsed');
        $usermodel =  D('Red_manage.RedpacketUserLimit');
        $userlimt = $usermodel->find();
        if($_POST['addsubmit']){
            $this -> assign("jumpUrl", $_SERVER['HTTP_REFERER']);
            $nosave = 0;
            //根据提交的文件名判断红包明细数据是否有更新
            if($_POST['filename'] != 'redpack_'.$_POST['id']){
                if($_POST['numpri'] == 1){
                    $file = $this->savedir.'/add'.$_POST['filename'].'.csv';
                }else{
                    $file = $this->savedir.'/origin'.$_POST['filename'].'.csv';
                }
            }else{
                $nosave = 1;
                $file = $this->savedir.'/'.$_POST['filename'].'.csv';
                
            }
            if(!file_exists($file)){
                $this->error('红包生成失败，请重新提交');
            }
            $edit = 0;
            if(isset($_POST['id']) && $_POST['id']>0){
                $_POST['id'] = intval($_POST['id']);
                $edit= 1;
            }
            
            $strlen = mb_strlen($_POST['pname'],'utf8');
            if($strlen>30||$strlen == 0){
                $this->error('红包名请输入1-30个汉字');
            }
            $where_str['pname'] = $_POST['pname'];
            $where_str['status'] = 1;
            
            if($edit){
                $where_str['id'] = array('neq',$_POST['id']);
                $packs = $model -> where("id={$_POST['id']}")->find();
                if(!$packs){
                    $this->error('编辑失败，该红包不存在');
                    
                }
                if($packs['taskid']>0){
                    $this->error('编辑失败，该红包已经绑定任务');
                }
            }
            $exist = $model->where($where_str)->find();
            // echo $model->getLastSql();die;
            if($exist){
                $this->error('红包名称已经存在');
            }
            if($_POST['tasktype']<=0){
                $this->error('请选择红包类型');
            }
            $_POST['totalnum'] = floor($_POST['totalnum']);
            if($_POST['totalnum']<=3){
                $this->error('红包总数必须大于3个');
            }
            $_POST['oldmon'] = floor($_POST['oldmon']);
            if($_POST['oldmon']<1){
                $this->error('红包总额请输入大于0的整数');
            }
            $_POST['cashmin'] = $this->figure_float($_POST['cashmin'],2);
            if($_POST['cashmin']<0.01){
                $this->error('红包最小金额请输入大于0.01金额');
            }
            $_POST['coef'] = $this->figure_float($_POST['coef'],1);
            if($_POST['coef']<1.0||$_POST['coef']>2.8){
                $this->error('系数只可输入大于等于1.0小于等于2.8');
                
            }
            
            if($_POST['givetype']<1||$_POST['givetype']>3){
                $this->error('请选择正确的红包发放类型');
            }
            //if($_POST['tasktype'] != 6){
            $_POST['awardtxt1'] = trim($_POST['awardtxt1']);
            $_POST['awardtxt2'] = trim($_POST['awardtxt2']);
            $_POST['awardtxt3'] = trim($_POST['awardtxt3']);
            if(!$_POST['awardtxt1'] || !$_POST['awardtxt2']){
                $this->error('请填写红包说明');
            }
            //}

            if($_POST['givetype'] == 1){
              /*   if(trim($_POST['starttime'][0]) == ''){
                    $this->error('请配置正确的开始时间');
                }
                $timeconf['starttime'][0] = strtotime($_POST['starttime'][0]); */
                $timeconf['starttime'][0] = 0;
                $timeconf['endtime'][0] = 0;
                $timeconf['pnum'][0] = $_POST['totalnum'];
            }
            
            if($_POST['givetype'] == 2){
                 if($_POST['tasktype']>1){
                    $this->error('只用普通类型的红包才能选择整点发放类型');
                }
                foreach($_POST['starttime'] as $key=>$time){
                    //下一个整点开始时间
                    $nexthour = strtotime(date('Y-m-d H:00:00'))+3600;
                    $time = strtotime($time);
                    if($time<$nexthour){
                        $this->error('第一个时段可开始的整点时间只能是当前时间的下一个整点开始');
                    }
                    $timeconf['starttime'][$key] = $time;
                    $timeconf['endtime'][$key] = strtotime($_POST['endtime'][$key]);
                    //确定整点发放的时间开始于00:00结束于59:59
                    if($timeconf['starttime'][$key]%3600 >0 ){
                        $this->error('整点发放的开始时间必须开始于0分0秒');
                    }
                    if($timeconf['endtime'][$key]%3600 !== 3599){
                        $this->error('整点发放的结束时间必须结束于59分59秒');
                    }
                    
                    if($timeconf['endtime'][$key] <= $timeconf['starttime'][$key]){
                        $this->error('结束时间必须大于开始时间');
                    }
                    $breforepack = $key - 1;
                    //开始时间必须大于上一段的结束时间，否则时间段重复
                    if(isset($timeconf['endtime'][$breforepack])){
                        if($timeconf['starttime'][$key] <= $timeconf['endtime'][$breforepack]){
                            $this->error('时间段配置不能重复');
                        }
                    }
                    
                    //计算总的整点时间数
                    $seg = ($timeconf['endtime'][$key] - $timeconf['starttime'][$key]+1)/3600;
                    $timeconf['seg'][$key] = $seg;
                    $allseg += $seg;
                }
                $pnum = floor($_POST['totalnum']/$allseg);
                
            }
            if($_POST['givetype'] == 3){
                if($_POST['tasktype']>1){
                    $this->error('只用普通类型的红包才能选择自定义发放类型');
                }
                foreach($_POST['starttime'] as $key=>$time){
                    $timeconf['starttime'][$key] = strtotime($time);
                    $timeconf['endtime'][$key] = strtotime($_POST['endtime'][$key]);
                    $timeconf['pnum'][$key] = intval($_POST['pnum'][$key]);
                    if($timeconf['endtime'][$key] <= $timeconf['starttime'][$key]){
                        $this->error('结束时间必须大于开始时间');
                    }
                    $breforepack = $key - 1;
                    if(isset($timeconf['endtime'][$breforepack])){
                        if($timeconf['starttime'][$key] <= $timeconf['endtime'][$breforepack]){
                            $this->error('时间段配置不能重复');
                        }
                    }
                    $totalconfnum += $timeconf['pnum'][$key];
                }
                if($totalconfnum>$_POST['totalnum']){
                    $this->error('每个时间段的红包配置超过了红包总数');
                }
            }
            //从红包文件中获取红包的真实发放总额
            $info = $this->getpackinfo($file);
            $data['totalmon'] = trim($info['usemon']);
            $data['restmon'] = trim($info['usemon']);
            $data['cashmax'] = trim($info['maxcash']);
            //实际生成红包数，小于等于配置红包数
            $data['totalnum'] = trim($info['outnum']);
            //实际剩余红包数，等于实际生成红包数
            $data['restnum'] = trim($info['outnum']);
            if($nosave == 0){
                //是否红包数优先，只有在补充红包金额的情况下值为1
                $data['numpri'] =  $_POST['numpri'] ? 1 : 0;
            }
                           
            $data['awardtxt1'] = $_POST['awardtxt1'] ? $_POST['awardtxt1'] : '';
            $data['awardtxt2'] = $_POST['awardtxt2'] ? $_POST['awardtxt2'] : '';
            $data['awardtxt3'] = $_POST['awardtxt3'] ? $_POST['awardtxt3'] : '';
            #默认所有的文字说明都展示
            // $data['listshow'] = $_POST['listshow'] ? $_POST['listshow'] : 0;
            $data['pname'] = $_POST['pname'];
            $data['oldmon'] = $_POST['oldmon'];
            $data['tasktype'] = $_POST['tasktype'];
            //配置红包数，红包的配置值个数，只做展示使用
            $data['confignum'] = $_POST['totalnum'];
            $data['cashmin'] = $_POST['cashmin'];
            $data['coef'] = $this->figure_float($_POST['coef'],1);
            $data['givetype'] = $_POST['givetype'];
          
            $data['status'] = 1;
            $data['lasttime'] = time();
			
            $data['starttime'] =$_POST['starttime'][0] ? strtotime($_POST['starttime'][0]): 0;     #该红包总的开始时间
            $endtime_data = array_slice($_POST['endtime'],-1,1);
            $data['endtime'] = $_POST['endtime'] ? strtotime($endtime_data[0]): 0; #该红包总结结束时间
            
            if($this->intformat == 1){
                $data['oldmon'] = 100 * $data['oldmon'];
                $data['cashmin'] = 100 * $data['cashmin'];
            }
            
            
            
            if(isset($_POST['id'])){
                $model->where('id='.$_POST['id'])->save($data);
            }else{
                $data['createtime'] = time();
                $packid = $model->add($data);
            }
            if($edit){
                $packid = $_POST['id'];
                $timemodel->where('pid='.$_POST['id'])->delete();
                $usedtimemodel->where('pid='.$_POST['id'])->delete();
            }
            if(!$packid){
                $this->error('红包配置失败，请重新配置');
            }
            if($nosave == 0){                
                //将红包明细重新保存为 红包id.csv
                $filecon = file_get_contents($file);
                $redpackfile = $this->savedir.'/redpack_'.$packid.'.csv';
                file_put_contents($redpackfile,$filecon);
                $this -> writelog("运营合作-红包配置-红包列表 编辑了id为{$packid}的红包,重新生成了红包明细:{$redpackfile}","sj_red_packet_conf",$packid,__ACTION__ ,"","edit");
                #将红包队列状态重置为未生成
                $rdata['questatus'] = 0;
                $model->where('id='.$packid)->save($rdata);
                $task_client = get_task_client();
                $task_client->doBackground("create_red_queue",$packid);
            }
            
            foreach($timeconf['starttime'] as $key =>$timestamp){
                $conf['starttime'] = $timestamp;
                $conf['endtime'] = $timeconf['endtime'][$key];
                $conf['pid'] = $packid;
                $usedconf['pid'] = $packid;
               
                if($data['givetype'] == 2){
                    //将整点发放的红包时间分段
                    $usedconf['restpnum'] = $usedconf['pnum'] = $conf['pnum'] = $pnum;
                    for($i=0;$i<$timeconf['seg'][$key];$i++){
                        $usedconf['starttime'] = $conf['starttime'] + $i*3600;
                        $usedconf['endtime'] = $usedconf['starttime'] + 3599;
                        $usedtimemodel -> add($usedconf);
                    }
                }else{
                    
                    $usedconf['starttime'] = $timestamp;
                    $usedconf['endtime'] = $timeconf['endtime'][$key];
                    $usedconf['restpnum'] = $timeconf['pnum'][$key];
                    $usedconf['pnum'] = $timeconf['pnum'][$key];
                    $usedtimemodel -> add($usedconf);
                    $conf['pnum'] = $timeconf['pnum'][$key];
                }
                //$conf['restpnum'] = $conf['pnum'];
                $timemodel->add($conf);
                    
                
                // echo $timemodel->getLastSql(); 
                // var_dump($conf,$id);die;
                
            }
            if($edit){                
                $this -> writelog("运营合作-红包配置-红包列表 编辑了id为{$packid}的红包","sj_red_packet_conf",$packid,__ACTION__ ,"","edit");
                $tips = '编辑成功';
            }else{
                 $this -> writelog("运营合作-红包配置-红包列表 添加了了id为{$packid}的红包","sj_red_packet_conf",$packid,__ACTION__ ,"","add");
                $tips = '添加成功';
            }
            $this -> assign("jumpUrl",__URL__."/showlist/");
            $this->success($tips);
        }else{
            $typemodel = D('Red_manage.RedpkTasktype');
            $ptypes = $typemodel -> select();
            foreach($ptypes as $type){
                $packtypes[$type['id']] = $type;
                
            }
            $this->assign('packtypes',$packtypes);
            $this->assign('userlimt',$userlimt);
            $this->display();
        }
        
        
        
    }
    
    function edit(){
        $readonly = $_GET['readonly'] ? 1 : 0;
        $copy = $_GET['copy'] ? 1 : 0;
        $model = D('Red_manage.RedPacketConf');
        $timemodel = D('Red_manage.RedpacketTimeConf');            
        $typemodel = D('Red_manage.RedpkTasktype');
        $usermodel =  D('Red_manage.RedpacketUserLimit');
        $userlimt = $usermodel->find();
        $ptypes = $typemodel -> select();
        foreach($ptypes as $type){
            $packtypes[$type['id']] = $type;
        }
        $id = intval($_GET['id']);
        $pack = $model->where(array('id'=>$id))->find();
       
        if($this->intformat){
            $pack['oldmon'] = $pack['oldmon']/100;
            $pack['cashmin'] = $pack['cashmin']/100;
        }else{
            $pack['oldmon'] = intval($pack['oldmon']);
        }
        if(!$copy){
            if($pack['taskid']>0 || $pack['activeid']>0){
                $this->error('对不起，这个红包已经绑定任务/活动，不能编辑');
            } 
            //如果复制红包不需要红包名称与发放类型
            $timeconf = $timemodel ->where(array('pid'=>$id))->select();
            $allseg = $seg = 0;
            foreach($timeconf as $conf){
                $packtime[$conf['id']] = $conf;
                $packtime[$conf['id']]['starttime'] = date('Y-m-d H:i:s',$conf['starttime']);
                $packtime[$conf['id']]['endtime'] = date('Y-m-d H:i:s',$conf['endtime']);
                $seg = ($conf['endtime'] - $conf['starttime']+1)/3600;
                $allseg += $seg;
            }
        
            if($pack['givetype'] == 1){
                 $packtime = $packtime[$conf['id']];
            }
            $totalpnum = $packtime[$conf['id']]['pnum'] * $allseg;   
            // $pack['oldmon'] = number_format($pack['oldmon']) ;           
            // $pack['totalnum'] = number_format($pack['totalnum']) ;           
        }else{
            $pack['pname'] = '';
            $pack['givetype'] = '';
        }
          
        $this->assign('packtypes',$packtypes);
        $this->assign('totalpnum',$totalpnum);
        $this->assign('pack',$pack);
        $this->assign('packtime',$packtime);
        $this->assign('readonly',$readonly);
        $this->assign('copy',$copy);
        $this->assign('userlimt',$userlimt);
        $this->display('add');
         
        
        
    }
    
    
    function del(){
        $id = intval($_GET['id']);
        $model = D('Red_manage.RedPacketConf');
        $packs = $model -> where("id={$id}")->find();
        if($packs['taskid']>0||$packs['activeid']>0){
            $this->error('删除失败，该红包已经绑定任务/活动了');
        }
            
        $this -> writelog("运营合作-红包配置-红包列表 删除了id为{$packid}的红包","sj_red_packet_conf",$packid,__ACTION__ ,"","delete");
            
        $this -> assign("jumpUrl",__URL__."/showlist/");
        
        $model -> where('id='.$id)->save(array('status'=>0));
        $this->success('删除成功');
    }
    
    
    //保留小数点后几位，不进位;
    function figure_float($num,$precision=2){
        $res = bcadd($num,0,$precision);
        return $res;
       
    }
    
    #预览刷新红包
    function calredpack(){
        $newrealnum = 0;
        parse_str($_SERVER['REQUEST_URI'],$params);
        #计算整点发放的时间每小时时间段,配置的总数量
        if($params['givetype'] == 2){
            foreach($params['starttime'] as $key=>$time){
                $time = strtotime($time);
                $timeconf['starttime'][$key] = $time;
                $timeconf['endtime'][$key] = strtotime($params['endtime'][$key]);
                
                $seg = ($timeconf['endtime'][$key] - $timeconf['starttime'][$key]+1)/3600;
                $timeconf['seg'][$key] = $seg;
            }
            foreach($timeconf['starttime'] as $key =>$timestamp){
                for($i=0;$i<$timeconf['seg'][$key];$i++){
                    $usedconf['starttime'][] = date("Y-m-d H:i:s",$timestamp + $i*3600);
                    $usedconf['endtime'][] = date("Y-m-d H:i:s",$timestamp + $i*3600 + 3599);
                    $usedconf['pnum'][] = $params['pnum'];
                }
            }
            $newrealnum = $params['totalpnum'];
        }
        #计算自定义发放配置的总数量
        if($params['givetype'] == 3){
            foreach($params['pnum'] as $num){
                $newrealnum = $newrealnum + $num;
            }            
        }
        
        
        set_time_limit(0);
        $model = D('Red_manage.RedpacketUserLimit');
        $user = $model ->find();
        #检查红包存储目录是否存在
        if(!is_dir($this->savedir)){
            if(!mkdir($this->savedir)){
                $this->error("red packet save dir: {$this->savedir} don't exist");
            }
        }
        if($this->intformat==1){
            $_GET['oldmon'] = $_GET['oldmon'] * 100;
            $_GET['cashmin'] = $_GET['cashmin'] * 100;
            $user['maxcash'] = $user['maxcash'] * 100;
            
        }
        if($this->intformat){
            $redqueue = create_queue_intval($_GET['oldmon'],$_GET['totalnum'],$_GET['cashmin'],$_GET['coef'],$user['maxcash']);
        }else{
            $redqueue = create_queue($_GET['oldmon'],$_GET['totalnum'],$_GET['cashmin'],$_GET['coef'],$user['maxcash']);
        }
        if(empty($redqueue)) $this->error('request params is error');
        $_GET['maxcash'] = $user['maxcash'];
        $filename = date("Y-m-d-H-i-s").".".time();
        $fileinfo = $this->savefile($redqueue,$this->savedir,$filename,$_GET,1);
        #算法生成的红包数，和总金额
        $usemon = $fileinfo['usemon'];
        $outnum = $fileinfo['outnum'];
        #无效红包数=配置的红包数量-算法生成的红包数
        $restnum = $_GET['totalnum'] - $outnum;
        #无效金额数=配置的红包金额-算法生成的红包金额
        if($this->intformat == 1){
            $restmon = $_GET['oldmon'] - $usemon;
        }else{
            $restmon = bcsub($_GET['oldmon'],$usemon,2);
        }
   
        $realnum = $realmon = 0;
   
        $mincash = min($redqueue);
        $maxcash = max($redqueue);
        $tips = '';
        if($_GET['id']){
            $tips = 'id为'.$_GET['id'];
        }
        $this -> writelog("运营合作-红包配置-红包预览 刷新了红包{$tips}的明细生成红包{$filename}.csv","sj_red_packet_conf",$_GET['id'],__ACTION__ ,"","edit");
        
        
        #自定义配置的红包金额
        $needusemoney = 0;
        for($i=0;$i<$newrealnum;$i++){
            if($this->intformat == 1){
                $needusemoney =  $needusemoney + $redqueue[$i];
            }else{
                $needusemoney =  bcadd($needusemoney , $redqueue[$i],2);
            }
        }

        $needusenum = $newrealnum;
        #未配置红包数=算法生成的红包数-自定义配置的红包数
        $nousenum = $outnum - $needusenum;
        #未配置红包金额=算法生成的红包金额-自定义配置的红包金额
       
        if($this->intformat == 1){
            $nousemoney =  $usemon - $needusemoney;
        }else{
            $nousemoney = bcsub($usemon,$needusemoney,2);
        }

        if($restnum>0){
            $addpack = array_fill(0,$restnum,$_GET['cashmin']);
            $redqueue = array_merge($redqueue,$addpack);
            shuffle($redqueue);
            $info = $this->savefile($redqueue,$this->savedir,$filename,$_GET,2);
            #如果使用红包数量优先，补充后红包发放金额
            $realmon = $info['usemon'];
            #如果使用红包数量优先，补充后的红包数量
            $realnum = $info['outnum'];
        }
 
        if($this->intformat == 1){
            $nousemoney = $nousemoney / 100;
            $needusemoney = $needusemoney / 100;
            $restmon = $restmon / 100;
            $realmon = $realmon / 100;
            $usemon = $usemon / 100;
            $mincash = $mincash / 100;
            $maxcash = $maxcash / 100;
        }
        
        
        
        $this->assign('params',$params);
        $this->assign('usedconf',$usedconf);
        $this->assign('nousenum',$nousenum);
        $this->assign('nousemoney',$nousemoney);
        $this->assign('needusenum',$needusenum);
        $this->assign('needusemoney',$needusemoney);
        
        
        
        
        
        $this->assign('usemon',$usemon);
        $this->assign('outnum',$outnum);
        $this->assign('mincash',$mincash);
        $this->assign('maxcash',$maxcash);
        $this->assign('restnum',$restnum);
        $this->assign('restmon',$restmon);
        $this->assign('realmon',$realmon);
        $this->assign('realnum',$realnum);
        $this->assign('filename',$filename);
        $this->display();
    }
    
    #保存文件
    protected function savefile($data,$path,$filename,$param,$type){
        if(!is_dir($path)){
            if(!mkdir($path)){
                $this->error("red packet save dir: {$path} don't exist");
            }
        }
        $pre = $type == 1 ? 'origin' : 'add';
        
        $filename = $pre.$filename;
        $first = "参数：,总金额:{$param['oldmon']},总数量:{$param['totalnum']},最小值:{$param['cashmin']},最大系数:{$param['coef']},单个最大额度:{$param['maxcash']}\n";
        file_put_contents($path.'/'.$filename.'.csv',$first);
        $usemon = 0;
        foreach($data as $k=>$cash){
            $line .= $k.','.$cash."\n";
            if($this->intformat){
                $usemon = $usemon + $cash;
            }else{                
                $usemon = bcadd($usemon,$cash,2);
            }
        }
        $mincash = min($data);
        $maxcash = max($data);
        $outnum = $k + 1;
        $line = "红包总金额:,".$usemon."\n".$line;
        $line = "红包总数:,".$outnum."\n".$line;
        $line = "红包最低金额:,".$mincash."\n".$line;
        $line = "红包最高金额:,".$maxcash."\n".$line;
        file_put_contents($path.'/'.$filename.'.csv',$line,FILE_APPEND);
        return array('usemon'=>$usemon,'outnum'=>$outnum);
  
        
    }
    #下载红包
    function download(){
        $file = $_GET['filename'];
        $type = $_GET['type'] ? $_GET['type'] : '';
        $path = $this->savedir.'/'.$type.$file.'.csv';
        if(file_exists($path)){
            header("Content-type:text/csv"); 
			header("Content-Disposition:attachment;filename=".time().'.csv'); 
			header('Cache-Control:must-revalidate,post-check=0,pre-check=0'); 
			header('Expires:0'); 
			header('Pragma:public'); 
            ob_clean();
            flush();
            $con = file_get_contents($path);
            echo mb_convert_encoding($con,'gbk','utf-8');
        }else{
            $this->error('文件不存在');
        }
        
        
    }
    
    #预览已经配置好的红包
    function preview(){
        $realnum = 0;
        parse_str($_SERVER['REQUEST_URI'],$params);
        $timeconf = '';
        #计算整点发放的时间每小时时间段,配置的总数量
        if($params['givetype'] == 2){
            foreach($params['starttime'] as $key=>$time){
                $time = strtotime($time);
                $timeconf['starttime'][$key] = $time;
                $timeconf['endtime'][$key] = strtotime($params['endtime'][$key]);
                $seg = ($timeconf['endtime'][$key] - $timeconf['starttime'][$key]+1)/3600;
                $timeconf['seg'][$key] = $seg;
                $allseg += $seg;
            }
            foreach($timeconf['starttime'] as $key =>$timestamp){
                for($i=0;$i<$timeconf['seg'][$key];$i++){
                    $usedconf['starttime'][] = date("Y-m-d H:i:s",$timestamp + $i*3600);
                    $usedconf['endtime'][] = date("Y-m-d H:i:s",$timestamp + $i*3600 + 3599);
                    $usedconf['pnum'][] = $params['pnum'];
                }
            }
            $realnum = $params['totalpnum'];
        }
        #计算自定义发放配置的总数量
        if($params['givetype'] == 3){
            foreach($params['pnum'] as $num){
                $realnum = $realnum + $num;
            }            
        }
        
        $packid = intval($_GET['packid']);
        if($packid<=0) $this->error('请选择正确的红包');
        $model = D('Red_manage.RedPacketConf');
        $pack = $model ->field(array('oldmon','totalnum'))->where('id='.$packid)->find();
        if(!file_exists($this->savedir.'/redpack_'.$packid.'.csv')){
            $this->error('红包配置不存在');
        }
        $info = $this->getpackinfo($this->savedir.'/redpack_'.$packid.'.csv',$realnum);
        
        $filename = 'redpack_'.$packid;
        $mincash = $info['mincash'];
        $maxcash = $info['maxcash'];
        #算法生成的红包数和总金额
        $outnum = $info['outnum'];
        $usemon = $info['usemon'];
        #无效红包数 = 配置的红包数量-算法生成的红包数
        $restnum = $pack['totalnum'] - $outnum;
        #无效红包金额 = 配置的红包金额-算法生成的红包金额
        $restmon = bcsub($pack['oldmon'],$usemon,2);
        
        //自定义红包金额
        $needusemoney = $info['needusemoney'];
        //自定义红包数量
        $needusenum = $realnum;
        //未配置的红包数 = 算法生成的红包数-自定义配置的红包数
        $nousenum = $outnum - $needusenum;
        //未配置红包金额 = 算法生成的红包金额-自定义配置的红包金额
        $nousemoney = bcsub($usemon,$info['needusemoney'],2);
        
        if($this->intformat == 1){
            $nousemoney = $nousemoney / 100;
            $needusemoney = $needusemoney / 100;
            $restmon = $restmon / 100;
            $realmon = $realmon / 100;
            $usemon = $usemon / 100;
            $mincash = $mincash / 100;
            $maxcash = $maxcash / 100;
        }
        
        $this->assign('params',$params);
        $this->assign('usedconf',$usedconf);
        $this->assign('nousenum',$nousenum);
        $this->assign('nousemoney',$nousemoney);
        $this->assign('needusenum',$needusenum);
        $this->assign('needusemoney',$needusemoney);
        

        $this->assign('usemon',$usemon);
        $this->assign('outnum',$outnum);
        $this->assign('mincash',$mincash);
        $this->assign('maxcash',$maxcash);
        
        $this->assign('restnum',$restnum);
        $this->assign('restmon',$restmon);
        
        
        $this->assign('filename',$filename);
        
        $this->display('calredpack');
    }
    
    #获取红包明细文件的详情信息
    protected function getpackinfo($path,$countnum = 0){
        // $filecon = file($path);
        $handle = fopen($path,'r');
        for($i=0;$i<5;$i++){
            $line = fgets($handle);
            $info[$i] = explode(',',$line);
        }
        $needusemoney = 0;
        $result = array(
            'mincash' => $info[2][1],
            'maxcash' => $info[1][1],
            'outnum' => intval($info[3][1]),
            'usemon' => floatval($info[4][1]),
        );
        if($countnum>0){
            //计算实际发放的红包数和总额
            for($i=0;$i<$countnum;$i++){
                $line = fgets($handle);
                $packline = explode(',',$line);
                $packmoney = floatval($packline[1]);
                $needusemoney = bcadd($needusemoney, $packmoney,2);
            }
            $result['needusemoney'] = $needusemoney;
            
            
        }
        
        fclose($handle);
        return $result;
        
        
        
    }
    
}