<?php
/**
 * 已处理反馈渠道统计
 */
class FeedbackLogAction extends CommonAction {
    // 统计展示页面
    public function index(){
        $model = M('');
        $where['status'] = 1;
        $white_soft_arr = $this->process_whitelist_soft();
        if(!isset($_GET['start_tm'])||empty($_GET['start_tm'])){
            $where['count_tm'] = strtotime(date('Y-m-d'));
        }else{
            $where['count_tm'][] = array('exp','>= '.strtotime($_GET['start_tm']));
            $this->assign('start_tm',$_GET['start_tm']);
        }
        if(isset($_GET['end_tm'])&&!empty($_GET['end_tm'])){
            $where['count_tm'][] = array('exp','<= '.strtotime($_GET['end_tm'].' 23:59:59'));
            $this->assign('end_tm',$_GET['end_tm']);
        }
        if(isset($_GET['package'])&&!empty($_GET['package'])){
            $where['package'] = $_GET['package'];
            $this->assign('package',$_GET['package']);
        }
        if(isset($_GET['softname'])&&!empty($_GET['softname'])){
            if($_GET['softname']=="非合作游戏"){
                $where['package'] = "";
            }else{
                $package1 = $model->table('sj_soft')->where(array('softname'=>array('like','%'.$_GET['softname'].'%')))->field('package')->select();
                $package2 = $model->table('sj_soft_whitelist')->where(array('softname'=>array('like','%'.$_GET['softname'].'%'),'status'=>1))->field('package')->select();
                if($package1&&$package2){
                    $package = array_merge($package1,$package2);
                }else if($package1&&!$package2){
                    $package = $package1;
                }else{
                    $package = $package2;
                }
                
                foreach($package as $val){
                    $pack_arr[] = $val['package'];
                }
                $where['package'] = array('in',$pack_arr);
            }
            $this->assign('softname',$_GET['softname']);
        }

        if(!isset($where['package'])){
            $pack_arr = array();
            foreach ($white_soft_arr['all_soft'] as $k => $v) {
                $pack_arr[] = $v['package'];
            }
            //包名增加非合作游戏
            $pack_arr[] = '';
            $where['package'] = array('in', $pack_arr);
        }
        $feed = $this->get_feed_info($where,$white_soft_arr['all_soft']);
        $where['ques_id'] = array('in',$feed['id_str']); 
        
        //分页
        $res = $model->table('sj_feedback_count')->where($where)->group('package')->field('id')->select(); 
        $total = count($res);
        $limit = isset($_GET['limit']) ? $_GET['limit'] : 10;
        $param = http_build_query($_GET);
		$qudao = array('电话','QQ','open后台','518后台','论坛');
		$ques = array('游戏内容','找回账号或密码','解绑手机或邮箱','登录问题','充值问题','VIP返利','活动/礼包','其他问题');
		$ques_count = $last_ques_count = array();
		//问题类型统计
		foreach($feed['feed_arr'] as $k=>$v){
			foreach($v['son'] as $key=>$val){
				if(in_array($val['c_name'],$ques)&&in_array($v['c_name'],$qudao)){
					$ques_count[$val['c_name']][$v['c_name']] = $val['num'];
				}
				
			}
		}
		//问题类型统计数据每列小计
		foreach($ques_count as $key=>$val){
			$ques_count[$key]['xiaoji'] = 0;
			foreach($val as $v_k=>$v_v){
				$ques_count[$key]['xiaoji'] += $v_v;
			}
		}
		
		//将问题类型统计数据键值转为数字
		$count_k = 1;
		foreach($ques as $val){
			$last_ques_count[$count_k] = $ques_count[$val];
			$count_k ++;
		}
        import('@.ORG.Page2');
        $Page = new Page($total, $limit, $param);
        $Page->rollPage = 1;
        $Page->setConfig('header', '篇记录');
        $Page->setConfig('first', '首页');
        $Page->setConfig('last', '尾页');
        //导出统计数据
        if($_GET['import_out']==1){
            $list = $model->table('sj_feedback_count')->where($where)->group('package')->order('type desc,update_tm desc')->field('package,sum(num) as xiaoji')->select();
            //处理结果为列表展示形式
            $list = $this->process_list($list,$feed['ques'],$where,$feed['openid_arr']);
			if($_GET['count']==1){
				$this->import_out_count($qudao,$last_ques_count,$feed);
			}else{
				
				$this->import_out($list['last'],$feed,$_GET);
			}
            
        }else{
            $list = $model->table('sj_feedback_count')->where($where)->limit($Page->firstRow . ',' . $Page->listRows)->group('package')->order('type desc,update_tm desc')->field('package,sum(num) as xiaoji')->select();
            //处理结果为列表展示形式
            $list = $this->process_list($list,$feed['ques'],$where,$feed['openid_arr']);

        }
		$this->assign('ques',$ques);
		$this->assign('ques_count',$last_ques_count);
		$this -> assign('qudao', $qudao);
        $this -> assign('page', $Page->show());
        $this -> assign('total', $total);
        $this -> assign('list',$list);
        $this->assign('heji',$feed['heji']);
        $this->assign('ques_num',$feed['colspan']);
        $this->assign('feed',$feed['feed_arr']);
        $this->assign('abc_list',$white_soft_arr['abc_list']);
        $this->assign('all_soft',$white_soft_arr['all_soft']);
        $this->assign('soft',$white_soft_arr['soft']);
        $this->display();
        
    }
    
    //添加已处理记录
    public function add_record(){
        $model = M('');
        //白名单软件
        $white_soft_arr = $this->process_whitelist_soft();
        $this->assign('abc_list',$white_soft_arr['abc_list']);
        $this->assign('all_soft',$white_soft_arr['all_soft']);
        $this->assign('soft',$white_soft_arr['soft']);
        //渠道类型
        $feed_type = $model->table('sj_feedback_config')->where(array('status'=>1,'parent_id'=>'0'))->field('id,c_name')->select();
        $this->assign('feed_type',$feed_type);
        if($_POST){
            $this->insert_record($_POST);
        }
        $this->display();
    }
    
    //添加已处理记录
    public function insert_record($data){
        $model = M('');
        if(empty($data['package'])&&$data['soft_name']!='非合作游戏'){
            $softname = $model->table('sj_soft_whitelist')->where(array('softname'=>$data['soft_name'],'status'=>1))->field('id,package')->find();
            if(!$softname){
                $softname = $model->table('sj_soft')->where(array('softname'=>$data['soft_name']))->field('softid,package')->order('softid desc')->find();
                if(!$softname){
                    $this->error('软件名称输入错误');
                }
            }
            $data['pkg'] = $softname['package'];
        }
        $insert_data['ques_id'] = $data['ques_type'];
        if($data['soft_name']!='非合作游戏'){
            $insert_data['type'] = 1;
            $insert_data['package'] = $data['pkg'];
        }else{
            $insert_data['type'] = 2;
            $insert_data['package'] = '';
        }
        $feedback = $model->table('sj_feedback_config')->where(array('id'=>$data['feedback_type']))->field('c_name')->find();
        if($feedback['c_name']=="open后台"){
            $insert_data['is_open'] = 1;
        }
        $insert_data['count_tm'] = strtotime(date('Y-m-d'));
        $insert_data['update_tm'] = time();
        $insert_data['num'] = $data['num'];
        $pack = $model->table('sj_feedback_count')->where(array('package'=>$insert_data['package'],'ques_id'=>$insert_data['ques_id'],'count_tm'=>strtotime(date('Y-m-d')),'status'=>1))->find();
        if($pack){
            $data_new=array('num'=>array('exp','num+'.$insert_data['num']),'update_tm'=>time());
            $log_result = $this->logcheck(array('package'=>$insert_data['package'],'ques_id'=>$insert_data['ques_id'],'count_tm'=>strtotime(date('Y-m-d')),'status'=>1),'sj_feedback_count',$data_new,$model->table('sj_feedback_count'));
            $res = $model->table('sj_feedback_count')->where(array('package'=>$insert_data['package'],'ques_id'=>$insert_data['ques_id'],'count_tm'=>strtotime(date('Y-m-d')),'status'=>1))->save($data_new);
            // $this->writelog('日志管理-已处理反馈渠道统计：添加了包名为'.$insert_data['package'].'问题类型id为'.$insert_data['ques_id'].'数量为'.$insert_data['num'].'已处理记录','sj_feedback_count',$id,__ACTION__ ,"","edit");
            $this->writelog('日志管理-已处理反馈渠道统计：编辑了包名为'.$insert_data['package'].'已处理记录.'.$log_result,'sj_feedback_count',"package:{$insert_data['package']}",__ACTION__ ,"","edit");
        }else{
            $res = $model->table('sj_feedback_count')->add($insert_data);
            $this->writelog('日志管理-已处理反馈渠道统计：添加了包名为'.$insert_data['package'].'问题类型id为'.$insert_data['ques_id'].'数量为'.$insert_data['num'].'已处理记录','sj_feedback_count',$res,__ACTION__ ,"","add");
        }
        
        if($res){
            if($insert_data['package']==''){
                $insert_data['package'] = '非合作游戏';
            }
            
            $this->update_process_daily($data['feedback_type'],$data['num']);
            $this->success('添加成功');
        }else{
            $this->error('添加失败');
        }
    }
    
    //更新sj_processed_daily 操作用户已处理表
    public function update_process_daily($pid,$num){
        $model  = M('');
        $time = time();
        $where = array(
                'admin_id' => $_SESSION['admin']['admin_id'],
        );
        $admin_id = $model -> table('sj_staff_config') -> where($where)->field('admin_id')->find();
        if($admin_id){
                //sj_processed_daily 操作用户已处理表
                $day = date("Ymd",$time);
                $where = array(
                        'admin_id' => $_SESSION['admin']['admin_id'],
                        'day_tm' => $day,
                        'ques_id' => $pid,
                        'status' => 1
                );
                $daily = $model -> table('sj_processed_daily') -> where($where)->field('id')->find();
                
                $map = array(
                        'admin_id' => $_SESSION['admin']['admin_id'],
                        'day_tm' => $day,
                        'status' => 1,
                        'num' => array('exp',"`num`+".$num),
                        'update_tm' => $time,
                );
                if($daily){
                        $log_result = $this->logcheck($where,'sj_processed_daily',$map,$model -> table('sj_processed_daily'));
                        $model -> table('sj_processed_daily') -> where($where)->save($map);
                        $this->writelog('操作用户已处理表：编辑了admin_id为'.$_SESSION['admin']['admin_id'].",ques_id为{$pid}的记录.{$log_result}",'sj_processed_daily',"admin_id:{$_SESSION['admin']['admin_id']};ques_id为:{$pid}",__ACTION__ ,"","edit");
                }else{
                        $map['add_tm'] = $time;
                        $map['ques_id'] = $pid;
                        $map['num'] = $num;
                        if(date("H") >= 13){
                                $map['shift'] = 2;
                        }
                        $res=$model -> table('sj_processed_daily')->add($map);
                        $this->writelog("操作用户已处理表：新增了id为{$res}的记录",'sj_processed_daily',$res,__ACTION__ ,"","add");
                }	
        }
    }
    //ajax获取渠道下的问题类型
    public function get_ques(){
        if(isset($_POST['id'])&&!empty($_POST['id'])){
            $model = M('');
            $feed_name = $model->table('sj_feedback_config')->where(array('status'=>1,'id'=>$_POST['id']))->field('c_name')->find();
            if($feed_name['c_name']=='open后台'){
                $url = C('feed_url').C('userfeedback');
                $vals['flag'] = 7;
                $open_info = httpGetInfo($url, $vals);

                $open_info = json_decode($open_info,true);
                foreach($open_info['userfeedbacks'] as $key=>$val){
                    $ques_type[$key]['id'] = $val['code'];
                    $ques_type[$key]['c_name'] = $val['name'];
                }
            }else{
                $ques_type = $model->table('sj_feedback_config')->where(array('status'=>1,'parent_id'=>$_POST['id']))->field('id,c_name')->select();
            }
            echo json_encode($ques_type);
        }
    }
    
    //获取白名单软件
    public function process_whitelist_soft(){
        $model = M('');
        $soft = '';
        $list = $model->table('sj_soft_whitelist a')->field('a.id,a.softname,a.package')->where(array('a.status' => 1))->select();
        
        $pack = array();
        foreach($list as $key=>$val){
            $pack[] = $val['package'];
        }
        $softname = $model->table('sj_soft')->field('softname,package')->where(array('package'=>array('in',$pack)))->order('softid desc')->select();
        $soft_online = array();
        foreach($softname as $s_k=>$s_v){
            $soft_online[$s_v['package']] = $s_v['softname'];
        }
        foreach($list as $key=>$val){
            if($soft_online[$val['package']]){
                $list[$key]['softname'] = $soft_online[$val['package']];
            }
        }

        $all_soft = $list;
        foreach($list as $key=>$val){
            
            $first_char = strtoupper(substr(Pinyin(trim($val['softname'])),0,1));
            $list[$key]['abc'] = $first_char;
            $soft .= '"'.$val['softname'].'",';
        }
        $soft = substr($soft,0,-1);
        $abc_arr = array('other','A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','num');
        $num_arr = array('0','1','2','3','4','5','6','7','8','9');
        $abc_list = array();
        $package = array();
        foreach($list as $key=>$val){
            if(in_array($val['abc'], $abc_arr)){
                $abc_list[$val['abc']][] = $list[$key];
            }else{
                if(in_array($val['abc'],$num_arr)){
                    $abc_list['num'][] = $list[$key];
                }else{
                    $abc_list['other'][] = $list[$key];
                }
                
            }
            $package[] = $val['package'];
        }
        return array('abc_list'=>$abc_list,'package'=>$package,'soft'=>$soft,'all_soft'=>$all_soft);
    }
    
    //获取渠道及对应的问题
    public function get_feed_info($where,$package){
        $model = M('');
        $all =  $model->table('sj_feedback_config')->where(array('status'=>1))->field('id,c_name,parent_id,rank')->order('parent_id asc,rank asc')->select();

        $feed_arr = $ques = $id_arr = $openid_arr = array();
        $colspan = $heji = 0;
        foreach($all as $k=>$v){
            if($v['parent_id']=='0'){
                $feed_arr[$v['id']]['c_name'] = $v['c_name'];
                if($v['c_name']=='open后台'){
                    $url = C('feed_url').C('userfeedback');
                    $vals['flag'] = 7;
                    $open_info = httpGetInfo($url, $vals);
                    $open_info = json_decode($open_info,true);
                    $open_id = $v['id'];
                }
            }else{
                $id_arr[] = $v['id'];
                $feed_arr[$v['parent_id']]['son'][$v['id']]['id'] = $v['id'];
                $feed_arr[$v['parent_id']]['son'][$v['id']]['c_name'] = $v['c_name'];
            }
        }
        foreach($open_info['userfeedbacks'] as $key=>$val){
            $id_arr[] = $val['code'];
            $openid_arr[] = $val['code'];
            $feed_arr[$open_id]['son'][$val['code']]['id'] = 'open_'.$val['code'];
            $feed_arr[$open_id]['son'][$val['code']]['c_name'] = $val['name'];
            $feed_arr[$open_id]['son'][$val['code']]['is_open'] = 1;
        }

        $id_str = implode(',',$id_arr);
        $where['ques_id'] = array('in',$id_str); 
        if(!isset($where['package'])){
           $pack_arr = array();
            foreach ($package as $k => $v) {
                $pack_arr[] = $v['package'];
            }
            //包名增加非合作游戏
            $pack_arr[] = '';
            $where['package'] = array('in', $pack_arr);
        }
        
        $ques_num = $model->table('sj_feedback_count')->where($where)->field('ques_id,sum(num) as num,is_open')->group('ques_id,is_open')->select();
        foreach($feed_arr as $k=>$v){
            if(!$v['son']){
                $colspan += 1;
                $ques[] = '';
            }else{
                $colspan += count($v['son']);
                $feed_arr[$k]['feed_heji'] = 0;
                foreach($v['son'] as $key=>$val){
                    $ques[] = $val['id'];
                   
                    foreach($ques_num as $q_k=>$q_v){  
                        $heji += $q_v['num'];
                        if($q_v['is_open']){
                            if($val['id']=='open_'.$q_v['ques_id']){
                                $feed_arr[$open_id]['son'][$key]['num'] = $q_v['num'];
                                if(!$feed_arr[$open_id]['feed_hejii']) $feed_arr[$open_id]['feed_hejii'] = 0;
                                $feed_arr[$open_id]['feed_hejii'] +=$q_v['num'];
                            }
                            
                        }else{
                            if($val['id']==$q_v['ques_id']){
                                $feed_arr[$k]['son'][$key]['num'] = $q_v['num'];
                                $feed_arr[$k]['feed_heji'] +=$q_v['num'];
                            }
                            
                        }
                        
                    }
                    
                    if($feed_arr[$k]['feed_heji']=='0')$feed_arr[$k]['feed_heji']='';
                }
            }
            
        }
        return array('feed_arr'=>$feed_arr,'colspan'=>$colspan,'heji'=>$heji,'ques'=>$ques,'id_str'=>$id_str,'openid_arr'=>$openid_arr);
    }
    
    //处理查询结果并返回页面所需形式
    public function process_list($data,$ques,$where,$openid_arr){
        $model = M('');
        $pack_arr = array();
        foreach($data as $k=>$v){
                $pack_arr[] = $v['package'];
        }

        $softname = $model->table('sj_soft_whitelist')->where(array('status'=>1,'package'=>array('in',$pack_arr)))->field('package,softname')->select();
        $softonlie = $model->table('sj_soft')->field('softname,package')->where(array('package'=>array('in',$pack_arr)))->order('softid desc')->select();

        $soft_online = array();
        foreach($softonlie as $s_k=>$s_v){
            $soft_online[$s_v['package']] = $s_v['softname'];
        }
        $softid = $model->table('sj_soft')->where(array('hide'=>1,'status'=>1,'package'=>array('in',$pack_arr)))->field('softid,package')->select();
        $where['package'] = array('in',$pack_arr);
        $ques_info  = $model->table('sj_feedback_count')->where($where)->field('package,ques_id,num,is_open')->order('type desc')->select();
        
        $ques_id_arr = explode(',', $where['ques_id'][1]);
        $erray_ques = array();
        foreach($ques_info as $k=>$v){            
            if($v['is_open']==1){
                if(in_array($v['ques_id'], $openid_arr)){
                    $erray_ques[$v['package']]['open_'.$v['ques_id']] += $v['num'];
                }else{
                    unset($ques_info[$k]);
                }
                
            }else{
                $erray_ques[$v['package']][$v['ques_id']] += $v['num'];
            }
            
        }

        $last = $whitelist = array();
        foreach($softname as $k=>$v){
            if($soft_online[$v['package']]){
                $softname[$k]['softname'] = $soft_online[$v['package']];
            }
            $whitelist[] = $v['package'];
        }
        foreach($data as $d_k =>$d_v){
            if($softname){
                foreach($softname as $s_k=>$s_v){
                    if($d_v['package'] == $s_v['package']){
                        $last[$d_v['package']]['softname'] = $s_v['softname'];
                    }else if($d_v['package']==''){
                        $last[$d_v['package']]['softname'] = '非合作游戏';
                    }
                }
            }else{
                if($d_v['package']==''){
                    $last[$d_v['package']]['softname'] = '非合作游戏';
                }
            }

            $last[$d_v['package']]['xiaoji'] = $d_v['xiaoji'];
            foreach($ques as $q_k=>$q_v){
                $last[$d_v['package']][$q_v] = '';
            }
        }

        foreach($erray_ques as $erray_key=>$erray_val){
            foreach($erray_val as $last_key=>$last_val){
                $last[$erray_key][$last_key] = $last_val;
            }
        }

        foreach($last as $k=>$v){
            if(!in_array($k, $whitelist)&&$v['softname']!='非合作游戏'){
                unset($last[$k]);
            }
        }
       
        return array('last'=>$last,'softid'=>$softid);
    }
    
    //删除已处理记录
    public  function del_log(){
        $model = M('');
        if(!isset($_GET['start_tm'])||empty($_GET['start_tm'])){
            $where['count_tm'] = strtotime(date('Y-m-d'));
        }else{
            $where['count_tm'][] = array('exp','>= '.strtotime($_GET['start_tm']));
        }
        if(isset($_GET['end_tm'])&&!empty($_GET['end_tm'])){
            $where['count_tm'][] = array('exp','<= '.strtotime($_GET['end_tm'].' 23:59:59'));
        }
        $where['package'] = empty($_GET['package'])?'':$_GET['package'];
        $res = $model->table('sj_feedback_count')->where($where)->save(array('status'=>0,'update_tm'=>time()));
        if($res){
            $date = date('Y-m-d');
            $this->writelog('日志管理-已处理反馈渠道统计：删除了包名为'.$where['package'].'时间为'.$date.'的已处理统计记录','sj_feedback_count',"package：{$_GET['package']}",__ACTION__ ,"","del");
            $this->success('删除成功');
        }else{
            $this->error('删除失败');
        }
    }
    //导出
    function import_out($data,$feed,$get){
        import('@.ORG.PHPExcel.PHPExcel');
        $objPHPExcel = new PHPExcel();
        $filePath = dirname(__FILE__).'/../../../Public/feedback_log.xlsx';
        $PHPReader = new PHPExcel_Reader_Excel2007();
        if (!$PHPReader->canRead($filePath)) {
            $PHPReader = new PHPExcel_Reader_Excel5();
            if (!$PHPReader->canRead($filePath)) {
                echo 'no Excel';
                return;
            }
        }
        $PHPExcel = $PHPReader->load($filePath);
        $currentSheet = $PHPExcel->getSheet(0);
        if(empty($get['start_tm'])){
            $start_tm = date('Y-m-d 00:00:00');
        }else{
            $start_tm = $get['start_tm'].' 00:00:00';
        }
        if(empty($get['end_tm'])){
            $end_tm = date('Y-m-d H:i:s');
        }else{
            $end_tm =$get['end_tm'].' 23:59:59';
        }

        $merge = 'D';
         for($i=1;$i<$feed['colspan'];$i++){
             $merge++;
         }
         
        $PHPExcel->getActiveSheet()->mergeCells('A1:'.$merge.'1'); 

        $PHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', '已处理反馈渠道统计_'.$start_tm.'——'.$end_tm);
        $PHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        
        $col_index = 'D';
        //渠道类型
        foreach($feed['feed_arr'] as $k=>$v){
                $colstr = '';
                $PHPExcel->setActiveSheetIndex(0)
                                 ->setCellValue($col_index.'2', $v['c_name']); 
                $PHPExcel->getActiveSheet()->getStyle($col_index.'2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $PHPExcel->getActiveSheet()->getStyle($col_index.'2')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                $PHPExcel->getActiveSheet()->getStyle($col_index.'2')->getFill()->getStartColor()->setARGB('FFCCFFCC');
                $PHPExcel->getActiveSheet()->getStyle($col_index.'2')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
                
                $PHPExcel->getActiveSheet()->getStyle($col_index.'2')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
                if($v['son']){
                    $colstr = $col_index;
                    for($i=0;$i<count($v['son'])-1;$i++){      
                        $col_index++;
                    }
                    
                }
                if(count($v['son'])>1){
                    $PHPExcel->getActiveSheet()->mergeCells($colstr.'2:'.$col_index.'2'); 
                    $PHPExcel->getActiveSheet()->getStyle($colstr.'2:'.$col_index.'2')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
                    $PHPExcel->getActiveSheet()->getStyle($colstr.'2:'.$col_index.'2')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
                }
                $col_index++;
                
        }
        //问题类型及处理数量
        $col_index = 'D';
        $neet_mborder = array();
        foreach($feed['feed_arr'] as $k=>$v){
            if($v['son']){
                $num = 0;
                foreach($v['son'] as $key=>$val){
                    $PHPExcel->setActiveSheetIndex(0)
                                     ->setCellValue($col_index.'3', $val['c_name'])
                                     ->setCellValue($col_index.'4', $val['num']); 
                     $PHPExcel->getActiveSheet()->getStyle($col_index.'3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                     $PHPExcel->getActiveSheet()->getStyle($col_index.'3')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                     $PHPExcel->getActiveSheet()->getStyle($col_index.'3')->getFill()->getStartColor()->setARGB('FFFFCC99');
                     $PHPExcel->getActiveSheet()->getStyle($col_index.'4')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                     $PHPExcel->getActiveSheet()->getStyle($col_index.'4')->getFill()->getStartColor()->setARGB('FFFFFF00');
                     $PHPExcel->getActiveSheet()->getStyle($col_index.'4')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
  
                     if(($num+1) == count($v['son'])){
                         $neet_mborder[] = $col_index;
                         $PHPExcel->getActiveSheet()->getStyle($col_index.'3')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
                         $PHPExcel->getActiveSheet()->getStyle($col_index.'4')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
                     }
                     $PHPExcel->getActiveSheet()->getStyle($col_index.'3')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                     $PHPExcel->getActiveSheet()->getStyle($col_index.'4')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                     $PHPExcel->getActiveSheet()->getStyle($col_index.'3')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
                     $PHPExcel->getActiveSheet()->getStyle($col_index.'4')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
                    $num++;
                    $col_index++;
                }
            }else{
                $neet_mborder[] = $col_index;
                $PHPExcel->setActiveSheetIndex(0)
                                    ->setCellValue($col_index.'3', '')
                                     ->setCellValue($col_index.'4', ''); 
                $PHPExcel->getActiveSheet()->getStyle($col_index.'3')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                $PHPExcel->getActiveSheet()->getStyle($col_index.'3')->getFill()->getStartColor()->setARGB('FFFFCC99');
                $PHPExcel->getActiveSheet()->getStyle($col_index.'4')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                $PHPExcel->getActiveSheet()->getStyle($col_index.'4')->getFill()->getStartColor()->setARGB('FFFFFF00');
                $PHPExcel->getActiveSheet()->getStyle($col_index.'3')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
                $PHPExcel->getActiveSheet()->getStyle($col_index.'4')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
                $PHPExcel->getActiveSheet()->getStyle($col_index.'3')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
                $PHPExcel->getActiveSheet()->getStyle($col_index.'4')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
                $PHPExcel->getActiveSheet()->getStyle($col_index.'3')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
                $PHPExcel->getActiveSheet()->getStyle($col_index.'4')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
                $col_index++;
            }
        }

        //合计
        $col_index = 'D';
        foreach($feed['feed_arr'] as $k=>$v){
            $colstr = '';
            if($v['c_name']=='open后台'){
                $PHPExcel->setActiveSheetIndex(0)
                                     ->setCellValue($col_index.'5', $v['feed_hejii']);
            }else{
                $PHPExcel->setActiveSheetIndex(0)
                                     ->setCellValue($col_index.'5', $v['feed_heji']);
            }
            
            $PHPExcel->getActiveSheet()->getStyle($col_index.'5')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $PHPExcel->getActiveSheet()->getStyle($col_index.'5')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
            $PHPExcel->getActiveSheet()->getStyle($col_index.'5')->getFill()->getStartColor()->setARGB('FFFFFF99');
            $PHPExcel->getActiveSheet()->getStyle($col_index.'5')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
            $PHPExcel->getActiveSheet()->getStyle($col_index.'5')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
            if($v['son']){
                $colstr = $col_index;
                for($i=0;$i<count($v['son'])-1;$i++){      
                    $col_index++;
                }
            }
            if(count($v['son'])>1){
                $PHPExcel->getActiveSheet()->mergeCells($colstr.'5:'.$col_index.'5'); 
                $PHPExcel->getActiveSheet()->getStyle($colstr.'5:'.$col_index.'5')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
                $PHPExcel->getActiveSheet()->getStyle($colstr.'5:'.$col_index.'5')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
            }
            $col_index++;
        }
        
         
         //软件详细信息
         $rowindex = '6';
         $num = 0;
         foreach($data as $d_k=>$d_v){
             $PHPExcel->setActiveSheetIndex(0)
                                     ->setCellValue('A'.$rowindex, (string)$d_k);
                $col_index = 'B';
                if(($num+1) == count($data)){
                    $PHPExcel->getActiveSheet()->getStyle('A'.$rowindex)->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
                }else{
                    $PHPExcel->getActiveSheet()->getStyle('A'.$rowindex)->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                }
                
             foreach($d_v as $key=>$val){
                 $PHPExcel->setActiveSheetIndex(0)
                                     ->setCellValue($col_index.$rowindex, (string)$val);
                 if($col_index=='C'){
                     $PHPExcel->getActiveSheet()->getStyle($col_index.$rowindex)->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
                     $PHPExcel->getActiveSheet()->getStyle($col_index.$rowindex)->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
                     $PHPExcel->getActiveSheet()->getStyle($col_index.$rowindex)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                     $PHPExcel->getActiveSheet()->getStyle($col_index.$rowindex)->getFill()->getStartColor()->setARGB('FFFFFF99');
                 }else{
                     $PHPExcel->getActiveSheet()->getStyle($col_index.$rowindex)->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                 }
                 foreach($neet_mborder as $n_k=>$n_v){
                     if($col_index == $n_v){
                         $PHPExcel->getActiveSheet()->getStyle($col_index.$rowindex)->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
                     }
                 }
                 $PHPExcel->getActiveSheet()->getStyle($col_index.$rowindex)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                 if(($num+1) == count($data)){
                        $PHPExcel->getActiveSheet()->getStyle($col_index.$rowindex)->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
                 }else{
                    $PHPExcel->getActiveSheet()->getStyle($col_index.$rowindex)->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                 }
                 $col_index++;
             }
             $num++;
             $rowindex++;
         }
        //生成新的excel     
        $excel_title = '已处理反馈渠道统计'.date('Y-m-d');
        header ( 'Content-Type: application/vnd.ms-excel' );
        $ua = $_SERVER["HTTP_USER_AGENT"];
        if (preg_match("/MSIE/", $ua)) {  
                header('Content-Disposition: attachment; filename="' . urlencode($excel_title) . '.csv"');
        } else if (preg_match("/Firefox/", $ua)) {  
                header('Content-Disposition: attachment; filename="' . $excel_title . '.csv"');
        } else {  
                header('Content-Disposition: attachment; filename="'.$excel_title.'.csv"');
        }	
        $objWriter = PHPExcel_IOFactory::createWriter($PHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }
	
	function import_out_count($qudao,$last_ques_count,$feed){
		import('@.ORG.PHPExcel.PHPExcel');
        $objPHPExcel = new PHPExcel();
        $filePath = dirname(__FILE__).'/../../../Public/feedback_log1.xlsx';
        $PHPReader = new PHPExcel_Reader_Excel2007();
        if (!$PHPReader->canRead($filePath)) {
            $PHPReader = new PHPExcel_Reader_Excel5();
            if (!$PHPReader->canRead($filePath)) {
                echo 'no Excel';
                return;
            }
        }
        $PHPExcel = $PHPReader->load($filePath);

        $currentSheet = $PHPExcel->getSheet(1);
		$col_index = 'C';
		foreach($qudao as $k=>$v){
			$PHPExcel->setActiveSheetIndex(0)
                                 ->setCellValue($col_index.'4',empty($last_ques_count[1][$v])?'0':$last_ques_count[1][$v])
								->setCellValue($col_index.'5',empty($last_ques_count[2][$v])?'0':$last_ques_count[2][$v])
								->setCellValue($col_index.'6',empty($last_ques_count[3][$v])?'0':$last_ques_count[3][$v])
								->setCellValue($col_index.'7',empty($last_ques_count[4][$v])?'0':$last_ques_count[4][$v])
								->setCellValue($col_index.'8',empty($last_ques_count[5][$v])?'0':$last_ques_count[5][$v])
								->setCellValue($col_index.'9',empty($last_ques_count[6][$v])?'0':$last_ques_count[6][$v])
								->setCellValue($col_index.'10',empty($last_ques_count[7][$v])?'0':$last_ques_count[7][$v]) 
								->setCellValue($col_index.'11',empty($last_ques_count[8][$v])?'0':$last_ques_count[8][$v]); 
			$col_index++;	
			
		}
		
		//生成新的excel     
        $excel_title = '用户问题类型统计'.date('Y-m-d');
        header ( 'Content-Type: application/vnd.ms-excel' );
        $ua = $_SERVER["HTTP_USER_AGENT"];
        if (preg_match("/MSIE/", $ua)) {  
                header('Content-Disposition: attachment; filename="' . urlencode($excel_title) . '.csv"');
        } else if (preg_match("/Firefox/", $ua)) {  
                header('Content-Disposition: attachment; filename="' . $excel_title . '.csv"');
        } else {  
                header('Content-Disposition: attachment; filename="'.$excel_title.'.csv"');
        }	
        $objWriter = PHPExcel_IOFactory::createWriter($PHPExcel, 'Excel5');
        $objWriter->save('php://output');
	}
}
