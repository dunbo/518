<?php
/**
 * Desc:CPD推广合作
 * Date: 2016/4/21
 * Time: 10:35
 */

class CpdExtendAction extends CommonAction
{
        private $download_config_arr = array('client_downloaded','www_downloaded','m_downloaded','coop_downloaded','other_downloaded','finger_play_downloaded');
        public $cpd_model;
        public $Client_db;
        public $soft_tmp;
        public $platform_model;
        public $third_model;
        public $bill_status = array('1'=>'待确认','2'=>'已确认','3'=>'驳回','4'=>'已冻结');
        public $receipt_status = array('1'=>'待确认','2'=>'已确认','3'=>'驳回');
        public $expend_status = array('1'=>'待确认','2'=>'已确认','3'=>'驳回');
        public $c_a_config = array();
        public $flexible_sys;
        public $tmp_path;
        public $deposit_path;
        function __construct()
        {
            parent::__construct();
            $this->flexible_sys = 1;
            $this->Client_db = D("Settlement.Client");
            $this->cpd_model = D('Settlement.CpdExtend');
            $this->soft_tmp = D("Dev.Softaudit");
            $this->platform_model = D('Settlement.CpdPlatform');
            $this->third_model = D('Settlement.CpdThird');
            $this->tmp_path = 'Settlement/CpdExtend:';
        }

        //日期控件格式
        function get_datepicker(){
            if($this->flexible_sys==1){
                $tm_formate = "Y-m-d";
                $end_day = "2023-12-31";
                $start_formate = "{startDate: '%y-%M-%d', dateFmt: 'yyyy-MM-dd'}";
                $end_formate = "{dateFmt: 'yyyy-MM-dd'}";
            }else{
                $tm_formate = "Y-m";
                $end_day = "2023-12";
                $start_formate = "{startDate: '%y-%M', dateFmt: 'yyyy-MM'}";
                $end_formate = "{dateFmt: 'yyyy-MM'}";
            }
            $this->assign('end_day',$end_day);  //合同默认结束日期
            $this->assign('tm_formate',$tm_formate);
            $this->assign('start_formate',$start_formate);
            $this->assign('end_formate',$end_formate);
        }

        function get_deposit_path(){
            if($this->flexible_sys==1){
                    $this->deposit_path = '/index.php/Settlement/CpdContractDeposit';
            }else{
                $this->deposit_path = '/index.php/Settlement/CpdContractSys';
            }
            $this->assign('deposit_path',$this->deposit_path);
        }
    // cpd合同列表
        function cpd_contract()
        {
            //软件类别
            $cname = $this->soft_tmp->return_category();
            $this -> assign('cname',$cname);
            $where = array('status'=>array('in','1,2'));
            //if($this->flexible_sys==1){
            $where['flexible_sys'] = $this->flexible_sys;
            //}
            if(!empty($_GET['custom_id'])){
                $where['custom_id'] = $_GET['custom_id'];
            }

            if(!empty($_GET['s_custom_name'])){
                $client_id = $this->Client_db -> where("client_name like '%{$_GET['s_custom_name']}%'")->field('id') ->select();
                $custom_id = array();
                foreach($client_id as $k=>$v){
                    $custom_id[] = $v['id'];
                }
                $where['custom_id'] = array('in',$custom_id);
                $this -> assign('s_custom_name',$_GET['s_custom_name']);
            }

            if(!empty($_GET['s_softname'])){
                $where['softname'] = array('like',"%{$_GET['s_softname']}%");
                $this -> assign('softname',$_GET['s_softname']);
            }
            if(!empty($_GET['s_package'])){
                $where['package'] =trim($_GET['s_package']);
                $this -> assign('package',$_GET['s_package']);
            }
            if(!empty($_GET['contact_config_id'])){
                $where['contact_config_id'] = $_GET['contact_config_id'];
                $this -> assign('contact_config_id',$_GET['contact_config_id']);
            }
            if(!empty($_GET['s_contract_code'])){
                $where['contract_code'] = array('exp'," = '{$_GET['s_contract_code']}' or flexible_c_code = '{$_GET['s_contract_code']}'");
                $this -> assign('contract_code',$_GET['s_contract_code']);
            }
            if(!empty($_GET['begintime'])){
                if($this->flexible_sys==2){
                    $_GET['begintime'] = $_GET['begintime'].'-01';
                }
                $where['start_tm'] = array('egt',strtotime($_GET['begintime']));
                $this -> assign('begintime',$_GET['begintime']);
            }
            if(!empty($_GET['endtime'])){
                $month = date("Y-m",strtotime($_GET['endtime']));
                $_GET['endtime'] = date("Y-m-d",strtotime("{$month}-01 + 1month  - 1day"));
                $where['end_tm'] = array('elt',strtotime($_GET['endtime']));
                $this -> assign('endtime',$_GET['endtime']);
            }

            if(!empty($_GET['contract_status'])){
                if($_GET['contract_status']==1){
                    $where['start_tm'] = array('gt',strtotime(date('Y-m-d')));
                }else if($_GET['contract_status']==2){
                    $where['start_tm'] = array('elt',strtotime(date('Y-m-d')));
                    $where['end_tm'] = array('egt',strtotime(date('Y-m-d')));
                }else if($_GET['contract_status']==3){
                    $where['end_tm'] = array('lt',strtotime(date('Y-m-d')));
                }else if($_GET['contract_status']==4){
                    $where['status'] = 2;
                }
                $this -> assign('contract_status',$_GET['contract_status']);
            }
            //软件类别条件
            if(!empty($_GET['cateid'])){
                $cateids = explode(',',$_GET['cateid']);
                $cateid = array_flip($cateids);
                $where['category_id'] = array('in',$cateids);
                $this -> assign('cateid',$cateid);
                $this -> assign("init_cateid",$_GET['cateid']);
            }
            if(!empty($_GET['account_status'])){
                $all_cpd = $this->cpd_model->where($where)->field('id')->select();
                $a_contract_id = array();
                foreach($all_cpd as $k=>$v){
                    $a_contract_id[] = $v['id'];

                }
                $a_where = array('contract_id'=>array('in',$a_contract_id));
                list($res,$task) = $this->cpd_model->get_surplus($a_where,'',$this->flexible_sys);

                //累计充值
                //$receipts = $this->cpd_model->get_receipts($a_where);
                //var_dump($receipts);
                //累计任务相关信息
                //list($total_download,$total_count,$count) = $this->cpd_model->get_task($a_where);
              //  var_dump($count);
                $res_id = array();
                foreach($a_contract_id as $k=>$v){
                    if($_GET['account_status'] == 1){
                        //正常
                        if(($res[$v]['recharge']+$res[$v]['delivery'])>=0) $res_id[] = $v;
                    }else{
                        //欠费
                        if(($res[$v]['recharge']+$res[$v]['delivery'])<0) $res_id[] = $v;
                    }
                }
                $where['id'] = array('in',$res_id);
                $this -> assign('account_status',$_GET['account_status']);
            }
            $this->contract_config_pub(); //协议主体
            $count = $this->cpd_model  -> where($where) -> count();
            import("@.ORG.Page");
            $Page=new Page($count,50);
            if($_GET['import_out']==1){
                if(!empty($_GET['id_str'])){
                    $id = substr($_GET['id_str'],0,-1);
                    $where['id'] = array('in',$id);
                }
            }
            $cpd_list = $this->cpd_model->where($where)-> limit($Page->firstRow.','.$Page->listRows)->order('id desc')->select();

            $all_contact_config = array();
            $contract_id = $all = array();
            $all = array('all1'=>0,'all2'=>0,'all3'=>0,'all4'=>0,'all5'=>0,'all6'=>0,'all7'=>0,'all8'=>0,'all9'=>0,'all10'=>0,'all11'=>0);
            foreach($cpd_list as $k=>$v){
                $clients = $this->Client_db -> where("id = '{$v['custom_id']}'")->field('client_name') ->find();
                $v['custom_name'] = $clients['client_name'];
                if($v['status']==2){
                    $v['cpd_status'] = '已终止';
                }else{
                    if($v['start_tm']-time()>0){
                        $v['cpd_status'] = '未开始';
                    }else if(time()<=$v['end_tm']+86439&&time()>=$v['start_tm']){
                        $v['cpd_status'] = '进行中';
                    }else{
                        $v['cpd_status'] = '已过期';
                    }
                }

                $format = $this->return_format();
                $v['start_tm'] = date("{$format}",$v['start_tm']);
                $v['end_tm'] = date("{$format}",$v['end_tm']);
				$v['stop_tm'] = date("{$format}",$v['stop_tm']);
                $v['add_tm'] = date("Y-m-d H:i:s",$v['add_tm']);
                //平均单价
                $v['price'] = round((float)$v['total_expend']/(int)$v['total_download'],2);
                if($v['flexible_c_code']){
                    $tmp_v = explode("_",$v['flexible_c_code']);
                    $v['flexible_c_id'] = $flexible_c_id = intval($tmp_v[3]);
                }else{
                    $v['flexible_c_id'] = $v['id'];
                }
                $contract_id[] = $v['flexible_c_code']?$flexible_c_id:$v['id'];
                $cpd_list[$k] = $v;
            }
            //合同协议主体名称
            $where = array('contract_id'=>array('in',$contract_id));
            //合同余额，配送金额，充值金额
            list($res,$task) = $this->cpd_model->get_surplus($where,'',$this->flexible_sys);
            //累计充值
            $receipts = $this->cpd_model->get_receipts($where,$this->flexible_sys);
            //累计任务相关信息
            list($total_download,$total_count,$count) = $task;

            //累计发票金额
            $invoice = $this->cpd_model->get_invoice($where,$this->flexible_sys);
            //自然量
            $nature = $this->cpd_model->get_nature($where,'contract_id,nature_num,nature_tm');
            //分站点计费限额
            //$download_config = $this->cpd_model->get_download_config($where,'id,contract_id,www_downloaded,m_downloaded,coop_downloaded,other_downloaded,finger_play_downloaded');
            if($_GET['import_out']==1){
                $this->import_contract_out($cpd_list,$invoice,$nature,$receipts,$total_download,$total_count,$res,$this->c_a_config);
            }
            foreach($res as $k=>$v){
                $all['all1'] += $v['recharge']+$v['delivery']; //总合同余额
                $all['all2'] += $v['recharge']; //总剩余充值金额
                $all['all3'] += $v['delivery'];  //总剩余配送金额
                $all['all6'] += $v['all_recharge']+$v['all_delivery'];  //总累计预存金额
                $all['all7'] += $v['all_recharge'];  //总累计充值金额
                $all['all8'] += $v['all_delivery'];   //总累计配送金额
            }

            foreach($total_download as $td_k=>$td_v){
                $all['all4'] += $td_v; //总累计下载量
            }

            foreach($total_count as $tc_k=>$tc_v){
                $all['all5'] += $tc_v;  //总累计消耗金额
            }
            foreach($receipts as $r_k=>$r_v){
                $all['all9'] += $r_v;  //总累计收款金额
            }

            foreach($invoice as $i_k=>$i_v){
                $all['all10'] += $i_v['invoice_sum'];  //总累计发票金额
            }

            foreach($nature as $n_k=>$n_v){
                $all['all11'] += $n_v['nature_num'];  //总累计发票金额
            }
            $this->assign('all',$all);
            $this->assign('res',$res);
            $this->assign('receipts',$receipts);
            $this->assign('count',$count);
            $this->assign('total_download',$total_download);
            $this->assign('total_count',$total_count);
            $this->assign('cpd_list',$cpd_list);
            $this->assign('invoice',$invoice);
            $this->assign('nature',$nature);
            //$this->assign('download_config',$download_config);
            $Page->setConfig('header','条记录');
            $Page->setConfig('first','<<');
            $Page->setConfig('last','>>');
            $show =$Page->show();
            $param = $_GET;
            $param = http_build_query($param);
            $this->assign ( "page", $show );
            $this -> assign('param',$param);
            $this->get_deposit_path();
            $this->get_datepicker();
            $this->assign('flexible_sys',$this->flexible_sys);
            $this->display($this->tmp_path.__FUNCTION__);
        }

        //添加cpd合同
        function add_contract(){
            if($_POST){
                $month = date("Y-m",strtotime($_POST['end_tm']));
                $end_day = date("Y-m-d",strtotime("{$month}-01 + 1month  - 1day"));
                if($this->flexible_sys == 2){
                    $_POST['start_tm'] = "{$_POST['start_tm']}-01";
                    $_POST['end_tm'] = $end_day;
                }
                if(!empty($_POST['package'])){
                    //获取客户id
                    list($_POST['custom_id'],$type) = $this->cpd_model->process_custom($_POST['custom_name']);
                    $bo = $this->check_contract($_POST);
                }else{
                    $this->error('包名不能为空');
                }
//                if($_POST['end_tm']<date("Y-m-d")){
//                    $this->error('结束时间小于当前时间');
//                }

                if(strtotime($_POST['start_tm'])>strtotime($_POST['end_tm'])){
                    $this->error('开始时间不能大于结束时间');
                }

                if($this->flexible_sys ==1){
//                    $start = strtotime("{$month}-01");
//                    $end = strtotime("{$month}-01 + 1month  - 1day");
                    $where = array('package'=>$_POST['package'],'custom_id'=>$_POST['custom_id'],'flexible_sys'=>$this->flexible_sys,'status'=>array('exp',' != 0 '),);
                    //'start_tm'=>array('exp'," >= '{$start}' and start_tm <= '{$end}') or (end_tm >= '{$start}' and end_tm <= '{$end}'")
                    if(!empty($_POST['id'])){
                        $where['id'] = array('exp'," != '{$_POST['id']}'");
                    }
                    $has_c = $this->cpd_model->table('cpd_contract')->where($where)->field('start_tm,end_tm')->select();
                    //echo $this->cpd_model->getLastSql();
                    if($has_c){
                        $time_s1 = strtotime(date("Y-m",strtotime($_POST['start_tm'])).'-01');
                        $time_e1 = strtotime("{$month}-01 + 1month  - 1day");
                        foreach($has_c as $h_k=>$h_v){
                            if(is_time_cross($time_s1, $time_e1, $h_v['start_tm'], $h_v['end_tm'])){
                                $this->error("月份已存在合同");
                            }
                        }
                    }
                    //exit();
                }
                if(isset($_POST['c_name'])){
                    $c_name = $this->cpd_model->table('cpd_contract_config')->where(array('c_name'=>$_POST['c_name'],'status'=>1))->find();
                    if(!$c_name){
                        $this->error('主体协议不存在');
                    }else{
                        $_POST['contact_config_id'] = $c_name['id'];
                        unset($_POST['c_name']);
                    }
                }

                if($bo){
                    if(!empty($_POST['custom_name'])){
                        //处理客户（如果没有则添加此客户）
                        if($type==1){
                            $this->writelog("CPD广告：添加合同时添加了客户名称为[{$_POST['custom_name']}]的客户，客户id为[{$_POST['custom_id']}]",'ad_clients',$_POST['custom_id'],__ACTION__ ,'','add');
                        }
                    }
                    $contract = $this->cpd_model->table("cpd_contract")->where("id = '{$_POST['id']}'")->find();
                    unset($_POST["__hash__"]);
                    unset($_POST['custom_name']);
                    if(!empty($_POST['start_tm'])) $_POST['start_tm'] = strtotime($_POST['start_tm']);
                    if(!empty($_POST['end_tm'])) $_POST['end_tm'] = strtotime($_POST['end_tm']);
                    $task = $this->cpd_model->table('cpd_task')->where(array('contract_id'=>$_POST['id'],'status'=>1,'end_tm'=>array('exp'," > '{$_POST['end_tm']}'")))->order('end_tm desc')->find();
                    if($task){
                        $t_end = date("Y-m-d",$task['end_tm']);
                        $this->error("合同结束时间不可小于任务编号为{$task['task_id']}的结束时间{$t_end}");
                    }
                    if(!empty($_POST['id'])){
                        $this->check_authority($_POST['id']);
                        $log_result = $this->logcheck(array('id'=>$_POST['id']), 'cpd_contract', $_POST,$this->cpd_model);
                        $res = $this->cpd_model->update_contract($_POST,$contract);
                        //echo $this->cpd_model->getLastSql();
                        if($res){
                            $this->writelog("CPD广告：编辑了合同id为{$_POST['id']}的合同,".$log_result,'cpd_contract',$_POST['id'],__ACTION__ ,'','edit');
                            if($contract['package']!=$_POST['package']||$contract['custom_id']!=$_POST['custom_id']){
                                $data = array('update_tm'=>time());
                                if($contract['package']!=$_POST['package']) {
                                    $data['package'] = $_POST['package'];
                                    $data['softname'] = $_POST['softname'];
                                }
                                if($contract['custom_id']!=$_POST['custom_id']) {
                                    $data['custom_id'] = $_POST['custom_id'];
                                }
                                $cpd_res = $this->cpd_model->table('cpd_task')->where("contract_id = '{$contract['id']}'")->save($data);

                                if($cpd_res){
                                    $this->writelog("CPD广告：修改了合同影响任务的名称和合同id,".$log_result,'cpd_task',$contract['id'],__ACTION__ ,'','edit');
                                }

                            }
                            $this->success('编辑成功');
                        }else{
                            $this->error('编辑失败');
                        }
                    }else{
                        $client_data=$this->Client_db->where(array('id'=>$_POST['custom_id']))->find();
                        $_POST['client_name']=$client_data['client_name'];
                        $_POST['flexible_sys'] = $this->flexible_sys;
                        $res = $this->cpd_model->add_contract($_POST);
                        if($res){
                            $this->writelog("CPD广告：添加了合同编号为{$res}的合同",'cpd_contract',$res,__ACTION__ ,'','add');
                            //合同数量加1
                            $this->Client_db->setInc('cpd_contract_num',"id = {$_POST['custom_id']}",1);
                            $this->success('添加成功');
                        }else{
                            $this->error('添加失败');
                        }
                    }

                }else{
                    $this->error('此包名合同时间重叠');
                }

            }
            if($_GET['id']){
                $cpd_info = $this->cpd_model->where("id = {$_GET['id']}")->find();
                $clients = $this->Client_db -> where("id = '{$cpd_info['custom_id']}'")->field('client_name') ->find();
                $cpd_info['custom_name'] = $clients['client_name'];
                $c_config = $this->cpd_model->table('cpd_contract_config')->where(array('id'=>$cpd_info['contact_config_id']))->find();
                $cpd_info['c_name'] = $c_config['c_name'];
                $this->assign('cpd_info',$cpd_info);
            }

            //处理按客户名称排序
            $clients = $this->Client_db -> where("status=1") -> order("CONVERT(client_name USING gbk)") -> select();
            $clients_str = $c_name = '';
            foreach($clients as $k=>$v){
                $clients_str .= '"'.$v['client_name'].'",';
            }
            $clients_str = substr($clients_str,0,-1);
            $list = $this->cpd_model->table('cpd_contract_config')->field('c_name')->select();
            foreach($list as $k=>$v){
                $c_name .= '"'.$v['c_name'].'",';
            }
            $c_name = substr($c_name,0,-1);
            $this->assign('c_name',$c_name);
            $this->assign('all_clients',$clients_str);
            $this->get_datepicker();
            $this->display($this->tmp_path.__FUNCTION__);
        }

        //检查合同是否能添加
        function check_contract($data){
            $where = array('package'=>$data['package'],'custom_id'=>$data['custom_id'],'flexible_sys'=>$this->flexible_sys,'flexible_c_code'=>'','status'=>array('exp',' != 0 '));
            if(!empty($data['id'])) $where['id'] = array('neq',$data['id']);
            $has_c = $this->cpd_model->where($where)->field('start_tm,end_tm')->order('end_tm desc')->select();
            if($has_c){
                foreach($has_c as $k=>$v){
                    //判断合同时间是否重合
                    $status = $v['start_tm'] - strtotime($data['start_tm']);
                    if ($status > 0) {
                        $status2 = $v['start_tm'] - strtotime($data['end_tm']);
                        if ($status2 > 0) {
                            continue;
                        } else {
                            return false;
                        }
                    } else {
                        $status2 = $v['end_tm'] - strtotime($data['start_tm']);
                        if ($status2 >= 0) {
                            return false;
                        } else {
                            continue;
                        }
                    }

                }
                return true;
             }else{
                return true;
            }
        }
        //获取软件信息
        function get_soft_info(){
            $package = $_POST['package'];
            if($package){
                $soft_info = $this->cpd_model->get_soft_info("package ='{$package}' and hide = 1 and status = 1",'softname,category_id');
                $category_name = $this->cpd_model->get_category_name($soft_info['category_id']);
                if($_POST['type']==1){
                    //根据包名获取合同信息
                    $now = strtotime(date("Ymd"));
                    $custom = $this->cpd_model->where("package = '{$package}' and status = 1 and start_tm <= '{$now}' and  end_tm >='{$now}' and flexible_sys = {$this->flexible_sys} and flexible_c_code = ''")->field('id,custom_id,contract_code,start_tm,end_tm')->select();
                    //$custom = $this->cpd_model->where("package = '{$package}' and status = 1 and start_tm <= '{$now}' and  end_tm >='{$now}' and flexible_c_code = '' ")->field('id,custom_id,contract_code')->select();
                    $contract_id = array();
                    foreach($custom as $k1=>$v1){
                        $contract_id[] = $v1['id'];
                    }
                    $a_where = array('contract_id'=>array('in',$contract_id));
                    $nature = $this->cpd_model->get_nature($a_where,'contract_id,nature_num,nature_tm');

                    //获取剩余充值及配送
                    $surplus = $this->cpd_model->get_surplus($a_where);
                    $surplus = $surplus[0];
                    //累计充值
                    //$receipts = $this->cpd_model->get_receipts($a_where);
                    //累计任务相关信息
                    //list($total_download,$total_count,$count) = $this->cpd_model->get_task($a_where);
                    foreach($custom as $k2=>$v2){
                        $format = $this->return_format();
                        $v2['start_tm'] = date($format,$v2['start_tm']);
                        $v2['end_tm'] = date($format,$v2['end_tm']);
                        //自然量
                        $v2['nature'] = $nature[$v2['id']]['nature_num']?$nature[$v2['id']]['nature_num']:0;
                        //CPD二期预存不够也可录入任务
                        if(!$surplus[$v2['id']]){
                        //if($surplus[$v2['id']]['recharge']+$surplus[$v2['id']]['delivery']<=0){
                            //unset($custom[$k2]);
                            //没有预存的合同
                            $v2['status'] = 0;
                        }else{
                            $v2['recharge'] = m_number_format($surplus[$v2['id']]['recharge']) ;
                            $v2['delivery'] = m_number_format($surplus[$v2['id']]['delivery']) ;
                            $v2['all_budgets'] =  m_number_format($surplus[$v2['id']]['recharge']+$surplus[$v2['id']]['delivery']);
                        }
                        $custom[$k2] = $v2;
                    }
                    //echo $this->cpd_model->getLastSql();
                    if($custom){
                        foreach($custom as $k3=>$v3){
                            //处理按客户名称排序
                            $clients = $this->Client_db -> where("id = '{$v3['custom_id']}' and status=1") ->field('client_name') -> find();
                            $v3['custom_name'] = $clients['client_name'];
                            $custom[$k3] = $v3;
                        }
                       $custom =  array_values($custom);
                    }
                }

                if(!$soft_info){
                    echo json_encode(array('code'=>0,'msg'=>'未找到该软件'));
                }else{
                    echo json_encode(array('code'=>1,'softname'=>$soft_info['softname'],'category_id'=>$category_name['category_id'],'category_name'=>$category_name['name'],'custom'=>$custom));
                }
            }else{
                echo json_encode(array('code'=>0,'msg'=>'未填写包名'));
            }

        }

        //获取客户是否存在
        function check_custom(){
            $custom_name = $_POST['custom'];
            if(empty($custom_name)){
                echo json_encode(array('code'=>0,'msg'=>'未填写客户名称'));
            }else{
                $clients = $this->Client_db -> where("client_name = '{$custom_name}' and status=1") ->find();
                if($clients){
                    echo json_encode(array('code'=>1,'custom_id'=>$clients['id']));
                }else{
                    echo json_encode(array('code'=>2,'msg'=>'无此客户'));
                }
            }
        }

        function task_list(){
            $where = array('a.status'=>array('neq',0));
            $where['a.flexible_sys'] = $this->flexible_sys;
            if(empty($_GET['orderby'])){
                $_GET['o'] = 1;
                $this->assign("o", $_GET['o']);
            }
            if(empty($_GET['orderby'])||$_GET['orderby']=='desc'){
                $order = 'asc';
            }else if($_GET['orderby']=='asc'){
                $order = 'desc';
            }

            $this->assign("order", $order);
            if(!empty($_GET['custom_id'])){
                $where['a.custom_id'] = $_GET['custom_id'];
            }
            if(!empty($_GET['contact_config_id'])){
                $where['b.contact_config_id'] = $_GET['contact_config_id'];
                $this -> assign('contact_config_id',$_GET['contact_config_id']);
            }
            if(!empty($_GET['s_softname'])){
                $where['a.softname'] = array('like',"%{$_GET['s_softname']}%");
                $this -> assign('softname',$_GET['s_softname']);
            }
            if(!empty($_GET['s_contract_code'])){
                $where['b.contract_code'] = array('exp'," = '{$_GET['s_contract_code']}' or b.flexible_c_code = '{$_GET['s_contract_code']}'");
                $this -> assign('contract_code',$_GET['s_contract_code']);
            }
            if(!empty($_GET['s_task_id'])){
                $where['a.task_id'] = $_GET['s_task_id'];
                $this -> assign('task_id',$_GET['s_task_id']);
            }
            if(!empty($_GET['s_package'])){
                $where['a.package'] = $_GET['s_package'];
                $this -> assign('package',$_GET['s_package']);
            }
            if(!empty($_GET['s_custom_name'])){
                $client_id = $this->Client_db -> where("client_name like '%{$_GET['s_custom_name']}%'")->field('id') ->select();
                $custom_id = array();
                foreach($client_id as $k=>$v){
                    $custom_id[] = $v['id'];
                }
                $where['a.custom_id'] = array('in',$custom_id);
                $this -> assign('s_custom_name',$_GET['s_custom_name']);
            }
            if(!empty($_GET['begintime'])){
                $where['a.start_tm'][] = array('egt',strtotime($_GET['begintime']));
                $this -> assign('begintime',$_GET['begintime']);
            }
            if(!empty($_GET['endtime'])){
                $where['a.end_tm'][] = array('elt',strtotime($_GET['endtime']));
                $this -> assign('endtime',$_GET['endtime']);
            }
            if(!empty($_GET['task_status'])){
                if($_GET['task_status']==1){
                    $where['a.start_tm'] = array('gt',strtotime(date('Y-m-d')));
                }else if($_GET['task_status']==2){
                    $where['a.start_tm'] = array('elt',strtotime(date('Y-m-d')));
                    $where['a.end_tm'] = array('egt',strtotime(date('Y-m-d')));
                }else if($_GET['task_status']==3){
                    $where['a.end_tm'] = array('lt',strtotime(date('Y-m-d')));
                }
                $this -> assign('task_status',$_GET['task_status']);
            }

            $this->contract_config_pub(); //协议主体

            if($_GET['import_out']==1){
                if(!empty($_GET['id_str'])){
                    $id = substr($_GET['id_str'],0,-1);
                    $where['a.id'] = array('in',$id);
                }
            }
            $count = $this->cpd_model ->table('cpd_task as a') ->join('cpd_contract as b on a.contract_id = b.id')-> where($where) -> count();
            import("@.ORG.Page");
            $Page=new Page($count,50);

//            if($order){
//                $task_list = $this->cpd_model->table('cpd_task as a')->join('cpd_contract as b on a.contract_id = b.id')->where($where)-> limit($Page->firstRow.','.$Page->listRows)->order("a.t_total_count/a.all_budgets $order")->field('a.*,b.contact_config_id,b.flexible_sys as c_flexible_sys,b.flexible_c_code')->select();
//            }else{
                $task_list = $this->cpd_model->table('cpd_task as a')->join('cpd_contract as b on a.contract_id = b.id')->where($where)-> limit($Page->firstRow.','.$Page->listRows)->field('a.*,b.contact_config_id,b.flexible_sys as c_flexible_sys,b.flexible_c_code')->order('add_tm desc')->select();
//            }

           //echo $this->cpd_model->getLastSql();
            $contract_id = array();
            foreach($task_list as $k=>$v){

                if($v['flexible_sys']!=$v['acture_sys']){
                    //A系统创建的任务在B系统显示内容
                    if($v['flexible_c_code']){
                        $tmp_v = explode("_",$v['flexible_c_code']);
                        $v['contract_id'] = intval($tmp_v[3]);
                    }
                    $flexible_where = array(
                        'contract_id' => $v['contract_id'],
                        'start_tm' => $v['start_tm']
                    );
                    //本月2号后出上月数据
                    $now_month = strtotime(date("Y-m-02"));
                    $month = date("Y-m",$v['end_tm']);
                    if(time()>= $now_month){
                        //当前时间为2号以后,统计上月数据
                        if($v['end_tm'] >= strtotime(date("Y-m-01"))){
                            $month =  date("Y-m",strtotime("-1month"));
                        }
                    }else{
                        //当前时间为2号之前，上月数据不应该统计
                        $last_two_month = date("Ym",strtotime("-2month"));
                        $last_two_month_day = strtotime("{$last_two_month}01 + 1month  - 1day");
                        if($v['end_tm'] > $last_two_month_day){
                            $month = date("Y-m",$last_two_month_day);
                        }
                    }
                    $flexible_where['end_tm'] = strtotime("{$month}-01 + 1month  - 1day");
                    $this->cpd_model->get_flexible_task_data($flexible_where,$v);
                }
                //CPD二期获取单价
                $v['price'] = m_number_format($v['t_total_count']/$v['t_total_download']);
                $clients = $this->Client_db -> where("id = '{$v['custom_id']}'")->field('client_name') ->find();
                $v['custom_name'] = $clients['client_name'];
                if($v['start_tm']-time()>0){
                    $v['task_status'] = '未开始';
                }else if(time()<=$v['end_tm']+86399&&time()>=$v['start_tm']){
                    $v['task_status'] = '进行中';
                }else{
                    $v['task_status'] = '已结束';
                }
                $format = $this->return_format();
                $v['start_tm'] = date($format,$v['start_tm']);
                $v['end_tm'] = date($format,$v['end_tm']);
                $v['add_tm'] = date("Y-m-d H:i:s",$v['add_tm']);
                //消耗/预算比
//                $v['percent'] = round(($v['t_total_count']/$v['all_budgets'])*100,2).'%';
//                if($v['flexible_c_code']){
//                    $tmp_v = explode("_",$v['flexible_c_code']);
//                    $flexible_c_id = intval($tmp_v[3]);
//                }
                //$contract_id[] = $v['flexible_c_code']?$flexible_c_id:$v['contract_id'];
                $contract_id[] = $v['contract_id'];
                $task_list[$k] = $v;
            }

            $contract_id = array_unique($contract_id);
            $surplus = $this->cpd_model->get_surplus(array('contract_id'=>array('in',$contract_id)),'',$this->flexible_sys);
            if($_GET['import_out']==1){
                $this->import_task_out($task_list,$this->c_a_config,$surplus);
            }
            $this->assign('surplus',$surplus);
            $this->assign('task_list',$task_list);
            $Page->setConfig('header','条记录');
            $Page->setConfig('first','<<');
            $Page->setConfig('last','>>');
            $show =$Page->show();
            $param = $_GET;
            $param = http_build_query($param);
            $this->assign ( "page", $show );
            $this -> assign('param',$param);
            $this->assign('flexible_sys',$this->flexible_sys);
            $this->get_deposit_path();
            $this->display($this->tmp_path.__FUNCTION__);
        }

        //添加或编辑任务
        function save_task(){
            if($_POST){
                list($_POST['custom_id'],$_POST['contract_id']) = explode('_',$_POST['s_custom']);
                $this->check_authority($_POST['contract_id']);
                unset($_POST['s_custom']);
                $custom_id = $_POST['custom_id'];
                unset($_POST['__hash__']);
                unset($_POST['custom']);
                if($this->flexible_sys==2){
                    //CPD二期任务按月添加
                    $_POST['start_tm'] = $_POST['start_tm'].'-01';
                    $month = date("Y-m",strtotime($_POST['end_tm']));
                    $_POST['end_tm'] = date("Y-m-d",strtotime("{$month}-01 + 1month  - 1day"));
                }

                isset($_POST['start_tm'])&&$_POST['start_tm'] = strtotime($_POST['start_tm']);
                isset($_POST['end_tm'])&&$_POST['end_tm'] = strtotime($_POST['end_tm']);
//                if($_POST['end_tm']<time()){
//                    $this->error('结束时间应该为当日或当日之后的时间');
//                }
//                if($_POST['all_budgets']!=$_POST['recharge_budgets']+$_POST['delivery_budgets']){
//                    $this->error('充值金额，配送金额之和不等于总预算，请检查');
//                }
                if(!$_POST['id']){
                    $start = date('Ym',$_POST['start_tm']);
                    $end = date('Ym',$_POST['end_tm']);
                    $where_bill = array(
                        'status' =>1,
                        'contract_id' => $_POST['contract_id'],
                        'bill_month' => array('exp'," >= {$start}")
                    );
                    $bill = $this->cpd_model->table('cpd_month_bill')->where($where_bill)->select();
                    $last_month = intval(date('Ym',strtotime($_POST['end_tm'])));
                    if($bill){
                        $bo = true;
                        $bill_id = array();
                        foreach($bill as $k=>$v){
                            if($v['bill_month']<= $last_month){
                                $bill_id[] = $v['id'];
                            }
                            //有状态已确认就不让建任务
                            if($v['receipt_status']==2||$v['expend_status']==2||$v['bill_status']==2){
                                $bo = false;
                            }
                        }
                        if(!$bo){
                            $this->error('此合同任务日期已有生成确认的月账单');
                        }
                    }
                }
                $a_where = array('contract_id'=>array('in',$_POST['contract_id']));
                //获取剩余充值及剩余充值
//                $surplus = $this->cpd_model->get_surplus($a_where,$_POST['id']);
//                $surplus =  $surplus[0];
//                if($surplus[$_POST['contract_id']]['recharge']<$_POST['recharge_budgets']){
//                    //合同剩余充值金额小于任务充值预算
//                    $this->error('充值金额不能大于总剩余充值金额,充值余额为'.$surplus[$_POST['contract_id']]['recharge']);
//                }
//                if($surplus[$_POST['contract_id']]['delivery']<$_POST['delivery_budgets']){
//                    //合同剩余配送金额小于任务配送预算
//                    $this->error('配送金额不能大于总剩余配送金额,配送余额为'.$surplus[$_POST['contract_id']]['delivery']);
//                }
                //累计充值
                //$receipts = $this->cpd_model->get_receipts($a_where);
                //累计任务相关信息

                //list($total_download,$total_count,$count) = $this->cpd_model->get_task($a_where,$_POST['id']);

//                if($_POST['all_budgets']>($receipts[$_POST['contract_id']]-$count[$_POST['contract_id']])){
//                    $last = $receipts[$_POST['contract_id']]-$count[$_POST['contract_id']];
//                    $this->error('余额不足,总预算大于余额,余额为'.$last);
//                }

                if(!empty($_POST['id'])){
                    $old_task = $this->cpd_model->table('cpd_task')->where(array('id'=>$_POST['id']))->find();
                    if($old_task['start_tm']<strtotime(date("Y-m-d"))&&$old_task['end_tm']>$_POST['end_tm']){
                        $this->error('进行中任务修改的结束时间只能比已有的结束时间大');
                    }
                    unset($_POST['contract_id']);
                    unset($_POST['custom_id']);
                    if($old_task['start_tm']>strtotime(date("Y-m-d"))){
                        //未开始任务不能改成进行中
                        if($_POST['start_tm']<=strtotime(date("Y-m-d"))){
                            $this->error('未开始任务不能改成进行中任务，如需则另行添加任务');
                        }
                    }else{
                        //过期及进行中不能修改开始时间
                        unset($_POST['start_tm']);
                    }


                    $log_result = $this->logcheck(array('id'=>$_POST['id']), 'cpd_task', $_POST,$this->cpd_model);
                }
                $where = array(
                    'contract_id' => $_POST['contract_id'],
//                'start_tm' => array('gt',$now)
                    'flexible_sys' => $this->flexible_sys
                );
                if(!empty($_POST['id'])) $where['id'] = array('neq',$_POST['id']);
                $task = $this->cpd_model->table('cpd_task')->where($where)->order('id desc')->select();
                foreach($task as $k=>$v){
                    if(is_time_cross($_POST['start_tm'], $_POST['end_tm'],$v['start_tm'],$v['end_tm'])){
                        $this->error("任务时间重叠");
                    }
                }
                $res = $this->cpd_model->save_task($_POST,$this->flexible_sys);
                if($res){
                    //如果月账单未确认，则将月账单状态改成产生消耗中，待消耗跑完后状态在置为正常
                    $this->cpd_model->table('cpd_month_bill')->where(array('id',array('in',$bill_id)))->save(array('status'=>2,'update_tm'=>time()));
                    if(!empty($_POST['id'])){
                        $this->writelog("CPD广告：编辑了id为{$_POST['id']}的任务,".$log_result,'cpd_task',$_POST['id'],__ACTION__ ,'','edit');
                        $this->success('编辑成功');
                    }else{
                        $this->writelog("CPD广告：添加了任务编号为{$res}的任务",'cpd_task',$res,__ACTION__ ,'','add');
                        //合同数量加1
                        $this->Client_db->setInc('cpd_task_num',"id = {$custom_id}",1);
                        $this->success('添加成功');
                    }
                }else{
                    $this->error('保存失败');
                }

            }
            if(!empty($_GET['id'])){
                $task_info = $this->cpd_model->table('cpd_task')->where("id = {$_GET['id']}")->find();
                $a_where = array('contract_id'=>array('in',$task_info['contract_id']));
                list($res,$task) = $this->cpd_model->get_surplus($a_where,'',$this->flexible_sys);
                $task_info['recharge_budgets'] = m_number_format($res[$task_info['contract_id']]['recharge']);
                $task_info['delivery_budgets'] = m_number_format($res[$task_info['contract_id']]['delivery']);
                $task_info['all_budgets'] = m_number_format($res[$task_info['contract_id']]['recharge']+$res[$task_info['contract_id']]['delivery']);
                $clients = $this->Client_db -> where("id = '{$task_info['custom_id']}'")->field('client_name') ->find();
                $task_info['custom_name'] = $clients['client_name'];
                //CPD 二期获取单价
                $where = array('price_tm'=>array('exp'," <= '{$task_info['start_tm']}'"));
                $price = $this->cpd_model->get_price($where);
                $task_info['price'] = $price['price_num'];
                $this->assign ( "task_info", $task_info );
            }
            $this->get_datepicker();
            $this->display($this->tmp_path.__FUNCTION__);
        }

        //根据任务时间检测是否合同处于进行中
        function check_task(){
            if($this->flexible_sys ==2){
                $_POST['start_tm'] = $_POST['start_tm'].'-01';
                $month = date("Y-m",strtotime($_POST['end_tm']));
                $_POST['end_tm'] = date("Y-m-d",strtotime("{$month}-01 + 1month  - 1day"));
            }
            $start_tm = strtotime($_POST['start_tm']);
            $end_tm  = strtotime($_POST['end_tm']);

            list($custom_id,$contract_id) = explode('_',$_POST['custom']);
            $this->check_authority($contract_id,1);
            if(empty($contract_id)){
                echo json_encode(array('code'=>0,'msg'=>"未关联客户或客户已欠费"));
                return;
            }
            $contract_info = $this->cpd_model->where("id = '{$contract_id}'")->field('start_tm,end_tm')->find();
//            echo $this->cpd_model->getLastSql();
//            $now = time();
            $where = array(
                'contract_id' => $contract_id,
//                'start_tm' => array('gt',$now)
                'flexible_sys' => $this->flexible_sys
            );
            if(!empty($_POST['id'])) $where['id'] = array('neq',$_POST['id']);
            $task = $this->cpd_model->table('cpd_task')->where($where)->order('id desc')->select();
//           echo $this->cpd_model->getLastSql();
//            $today = strtotime(date("Y-m-d"));
//            if($start_tm<$today&&empty($_POST['id'])){
//                echo json_encode(array('code'=>0,'msg'=>"不能创建已过期任务"));
//                return;
//            }
            foreach($task as $k=>$v){
                //判断合同时间是否重合
                $status = $v['start_tm'] - $start_tm;
                if ($status > 0) {
                    $status2 = $v['start_tm'] - $end_tm;
                    if ($status2 <= 0) {
                        echo json_encode(array('code'=>0,'msg'=>"不能创建此任务，与其他任务时间有重合"));
                        return;
                    }
                } else {
                    $status2 = $v['end_tm'] - $start_tm;
                    if ($status2 >= 0) {
                        echo json_encode(array('code'=>0,'msg'=>"不能创建此任务，与其他任务时间有重合"));
                        return;
                    }
                }
            }
            if($start_tm>=$contract_info['start_tm']&&$end_tm<=$contract_info['end_tm']){
                //可创建任务
                echo json_encode(array('code'=>1));
                return;
            }else{
                if($this->flexible_sys==2){
                    $d_format = "Y-m";
                }else{
                    $d_format = "Y-m-d";
                }
                $start = date($d_format,$contract_info['start_tm']);
                $end =  date($d_format,$contract_info['end_tm']);
                echo json_encode(array('code'=>0,'msg'=>"不能创建此任务，与合同时间不匹配，合同日期为（{$start}/{$end}）"));
                return ;
            }
        }
        //导出合同列表
        function import_contract_out($cpd_list,$invoice,$nature,$receipts,$total_download,$total_count,$res,$c_a_config){
            $filename = "CPD合同列表-".date("Y-m-d").'.csv';
            header("Content-type:text/csv");
            header("Content-Disposition:attachment;filename=".$filename);
            header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
            header('Expires:0');
            header('Pragma:public');
            $str = '';
            if(empty($cpd_list)) {
                $str .= iconv('utf-8','gb2312','没有任何信息');
            }else {
                $str .= iconv('utf-8','gb2312','合同编号').",";
                $str .= iconv('utf-8','gb2312','软件名称').",";
                $str .= iconv('utf-8','gb2312','包名').",";
                $str .= iconv('utf-8','gb2312','类别').",";
                $str .= iconv('utf-8','gb2312','客户名称').",";
                $str .= iconv('utf-8','gb2312','协议主体').",";
                $str .= iconv('utf-8','gb2312',"开始时间").",";
                $str .= iconv('utf-8','gb2312',"结束时间").",";
                $str .= iconv('utf-8','gb2312',"合同余额").",";
                $str .= iconv('utf-8','gb2312',"剩余充值金额").",";
                $str .= iconv('utf-8','gb2312',"剩余配送金额").",";
                $str .= iconv('utf-8','gb2312',"累计下载量(核减后)").",";
                $str .= iconv('utf-8','gb2312',"累计消耗金额(核减后)").",";
                $str .= iconv('utf-8','gb2312',"累计预存金额(商务)").",";
                $str .= iconv('utf-8','gb2312',"累计充值金额").",";
                $str .= iconv('utf-8','gb2312',"累计配送金额").",";
                $str .= iconv('utf-8','gb2312',"累计收款金额(财务)").",";
                $str .= iconv('utf-8','gb2312',"累计发票金额").",";
                $str .= iconv('utf-8','gb2312',"当前自然量").",";
                $str .= iconv('utf-8','gb2312',"账户状态").",";
                $str .= iconv('utf-8','gb2312',"合同状态")."\r\n";
                foreach ($cpd_list as $key => $val)
                {
                    $str .= iconv('utf-8','gb2312',empty($val['flexible_c_code'])?$val['contract_code']:$val['flexible_c_code']).",";
                    $str .= iconv('utf-8','gb2312',$val['softname']).",";
                    $str .= iconv('utf-8','gb2312',$val['package']).",";
                    $str .= iconv('utf-8','gb2312',$val['category_name']).",";
                    $str .= iconv('utf-8','gb2312',$val['custom_name']).",";
                    $str .= iconv('utf-8','gb2312',$c_a_config[$val['contact_config_id']]['c_name']).",";
                    $str .= iconv('utf-8','gb2312',$val['start_tm']).",";
                    $str .= iconv('utf-8','gb2312',$val['end_tm']).",";
                    $tmp1 = m_number_format($res[$val['flexible_c_id']]['recharge']+$res[$val['flexible_c_id']]['delivery']);
                    $str .= iconv('utf-8','gb2312',"\"$tmp1\"").",";
                    $tmp2 = m_number_format($res[$val['flexible_c_id']]['recharge']);
                    $str .= iconv('utf-8','gb2312',"\"$tmp2\"").",";
                    $tmp3 = m_number_format($res[$val['flexible_c_id']]['delivery']);
                    $str .= iconv('utf-8','gb2312',"\"$tmp3\"").",";
                    $str .= ($total_download[$val['flexible_c_id']]?$total_download[$val['flexible_c_id']]:0).",";
                    $tmp4 = m_number_format($total_count[$val['flexible_c_id']]);
                    $str .= iconv('utf-8','gb2312',"\"$tmp4\"").",";
                    $tmp5 = m_number_format($res[$val['flexible_c_id']]['all_recharge']+$res[$val['flexible_c_id']]['all_delivery']);
                    $str .= iconv('utf-8','gb2312',"\"$tmp5\"").",";
                    $tmp6 = m_number_format($res[$val['flexible_c_id']]['all_recharge']);
                    $str .= iconv('utf-8','gb2312',"\"$tmp6\"").",";
                    $tmp7 = m_number_format($res[$val['flexible_c_id']]['all_delivery']);
                    $str .= iconv('utf-8','gb2312',"\"$tmp7\"").",";
                    $tmp8 = m_number_format($receipts[$val['flexible_c_id']]);
                    $str .= iconv('utf-8','gb2312',"\"$tmp8\"").",";
                    $tmp9 = m_number_format($invoice[$val['flexible_c_id']]['invoice_sum']);
                    $str .= iconv('utf-8','gb2312',"\"$tmp9\"").",";
                    $str .= (empty($nature[$val['flexible_c_id']]['nature_num'])?0:$nature[$val['flexible_c_id']]['nature_num']).",";
                    if($res[$val['flexible_c_id']]['recharge']+$res[$val['flexible_c_id']]['delivery']>=0){$str .= iconv('utf-8','gb2312','正常').",";}else{$str .= iconv('utf-8','gb2312','欠费').",";}
                    $str .= iconv('utf-8','gb2312',$val['cpd_status'])."\r\n";
                }
            }
            echo $str;
//            echo chr(0xEF).chr(0xBB).chr(0xBF);
            exit;
        }

        //导出任务
    function import_task_out($task_list,$c_a_config,$surplus){
        $filename = "CPD任务列表-".date("Y-m-d").'.csv';
        header("Content-type:text/csv");
        header("Content-Disposition:attachment;filename=".$filename);
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');

        if(empty($task_list))
        {
            echo iconv('utf-8','gb2312','没有任何信息');
        }else
        {
            echo iconv('utf-8','gb2312','任务ID').",";
            echo iconv('utf-8','gb2312','软件名称').",";
            echo iconv('utf-8','gb2312','包名').",";
            echo iconv('utf-8','gb2312','类别').",";
            echo iconv('utf-8','gb2312','客户名称').",";
            echo iconv('utf-8','gb2312','协议主体').",";
            echo iconv('utf-8','gb2312',"开始时间").",";
            echo iconv('utf-8','gb2312',"结束时间").",";
            echo iconv('utf-8','gb2312',"单价").",";
//            echo iconv('utf-8','gb2312',"期望日下载量").",";
            echo iconv('utf-8','gb2312',"预计需消耗下载").",";
            echo iconv('utf-8','gb2312',"累计消耗量(核减后)").",";
            echo iconv('utf-8','gb2312',"累计消耗(充值)").",";
            echo iconv('utf-8','gb2312',"累计消耗(配送)").",";
            echo iconv('utf-8','gb2312',"累计消耗金额(核减后)").",";
            echo iconv('utf-8','gb2312',"备注")."\r\n";
            foreach ($task_list as $key => $val)
            {
                echo iconv('utf-8','gb2312',$val['task_id']).",";
                echo iconv('utf-8','gb2312',$val['softname']).",";
                echo iconv('utf-8','gb2312',$val['package']).",";
                echo iconv('utf-8','gb2312',$val['category_name']).",";
                echo iconv('utf-8','gb2312',$val['custom_name']).",";
                echo iconv('utf-8','gb2312',$c_a_config[$val['contact_config_id']]['c_name']).",";
                echo iconv('utf-8','gb2312',$val['start_tm']).",";
                echo iconv('utf-8','gb2312',$val['end_tm']).",";
                $tmp1 = m_number_format($val['price']);
                echo iconv('utf-8','gb2312',"\"$tmp1\"").",";
                echo round(($surplus[0][$val['contract_id']]['recharge']+$surplus[0][$val['contract_id']]['delivery'])/$val['price'],2).",";
                echo iconv('utf-8','gb2312',$val['t_total_download']).",";
                $val['download_recharge_sum'] = m_number_format($val['download_recharge_sum']);
                echo iconv('utf-8','gb2312',"\"{$val['download_recharge_sum']}\"").",";
                $val['download_delivery_sum'] = m_number_format($val['download_delivery_sum']);
                echo iconv('utf-8','gb2312',"\"{$val['download_delivery_sum']}\"").",";
                $val['t_total_count'] = m_number_format($val['t_total_count']);
                echo iconv('utf-8','gb2312',"\"{$val['t_total_count']}\"").",";
                echo iconv('utf-8','gb2312',$val['remark'])."\r\n";
            }
        }
        exit;
    }

    //分位置下载统计
    function download_count(){
        $where = array('status'=>1);
        if(!empty($_GET['s_softname'])){
            $where['softname'] = array('like',"%{$_GET['s_softname']}%");
            $this -> assign('softname',$_GET['s_softname']);
        }
        if(!empty($_GET['s_package'])){
            $where['package'] = $_GET['s_package'];
            $this -> assign('package',$_GET['s_package']);
        }

        if(!empty($_GET['begintime'])){
            if(empty($_GET['endtime'])){
                $this->error('请填结束时间');
            }
            $this -> assign('begintime',$_GET['begintime']);
        }
        if(!empty($_GET['endtime'])){
            if(empty($_GET['begintime'])){
                $this->error('请填开始时间');
            }
            $this -> assign('endtime',$_GET['endtime']);
        }
        if((strtotime($_GET['endtime'])-strtotime($_GET['begintime']))>2592000){
            $this->error('最大时间跨度为30天');
        }
        if(!empty($_GET['task_id'])){
            $where['task_id'] = $_GET['task_id'];
            $this -> assign('task_id',$_GET['task_id']);
        }

        $task_list = $this->cpd_model->table('cpd_task')->where($where)->field('contract_id,task_id,softname,package,start_tm,end_tm,price')->order('task_id desc')->select();
        $package = $p_pack = $list = array();
        foreach($task_list as $k=>$v){
            $package[] = $v['package'];
            for($i = $v['start_tm'];$i<=$v['end_tm'];$i=$i+86400){
                if($i>time()) break;
                $tmp = array(
                    'contract_id' => $v['contract_id'],
                    'softname'=>$v['softname'],
                    'task_id' =>$v['task_id'],
                    'price' =>$v['price']
                );
                if(empty($_GET['begintime'])&&empty($_GET['endtime'])){
                    //未填日期默认处理单天数据
                    if($i==strtotime(date("Y-m-d"))){
                        $list[$v['package']][$i] = $tmp;
                    }
                }else{
                    if($i>=strtotime($_GET['begintime'])&&$i<=strtotime($_GET['endtime'])){
                        $list[$v['package']][$i] = $tmp;
                    }
                }

            }
        }
        $day = array();
        foreach($list as $k=>$v){
            krsort($v);
            $list[$k] = $v;
            $p_pack[] = $k;
            foreach($v as $d_k=>$d_v){
                $day[] = $d_k;
            }
        }

        $day = array_unique($day);
        //获取无效下载
        $this->cpd_model->get_extend_by_day($p_pack,$day,$list);
        //var_dump( date("H:i:s:u"));
        //获取自然量
        $this->cpd_model->get_nature_by_day($list);
        //var_dump( date("H:i:s:u"));
        //获取分站点限额
        $this->cpd_model->get_download_config_by_day($list);

       // var_dump( date("H:i:s:u"));
        //获取分站点下载量
        $this->platform_model->get_soft_platform_by_day($list);
        //var_dump( date("H:i:s:u"));
        //获取下载安装，下载完成
        $this->platform_model->get_soft_down_by_day($list);
       // var_dump( date("H:i:s:u"));
        //获取第三方下载量
        $this->third_model->get_third_by_day($list);

        foreach($list as $k=>$v){
            foreach($v as $l_k => $l_v){
                //下载量（计算）
                $l_v['count_download'] = 0;
                $l_v['count_download'] += $l_v['client_downloaded'];

                //www站点下载取最小
                if($l_v['www_downloaded']<$l_v['c_www_downloaded']||empty($l_v['c_www_downloaded'])||$l_v['c_www_downloaded']==-1){
                    $l_v['count_download'] += $l_v['www_downloaded'];
                }else{
                    $l_v['count_download'] += $l_v['c_www_downloaded'];
                }

                //m站点下载量取最小
                if($l_v['m_downloaded']<$l_v['c_m_downloaded']||empty($l_v['c_m_downloaded'])||$l_v['c_m_downloaded']==-1){
                    $l_v['count_download'] += $l_v['m_downloaded'];
                }else{
                    $l_v['count_download'] += $l_v['c_m_downloaded'];
                }

                //合作下载量取最小
                if($l_v['coop_downloaded']<$l_v['c_coop_downloaded']||empty($l_v['c_coop_downloaded'])||$l_v['c_coop_downloaded']==-1){
                    $l_v['count_download'] += $l_v['coop_downloaded'];
                }else{
                    $l_v['count_download'] += $l_v['c_coop_downloaded'];
                }

                //其他站点下载量去最小
                if($l_v['other_downloaded']<$l_v['c_other_downloaded']||empty($l_v['c_other_downloaded'])||$l_v['c_other_downloaded']==-1){
                    $l_v['count_download'] += $l_v['other_downloaded'];
                }else{
                    $l_v['count_download'] += $l_v['c_other_downloaded'];
                }
                //第三方下载量取最小
                if($l_v['third_downloaded']<$l_v['c_finger_play_downloaded']||empty($l_v['c_finger_play_downloaded'])||$l_v['c_finger_play_downloaded']==-1){
                    $l_v['count_download'] += $l_v['third_downloaded'];
                }else{
                    $l_v['count_download'] += $l_v['c_finger_play_downloaded'];
                }
                $v[$l_k] = $l_v;
            }

            $list[$k] = $v;
        }
        if($_GET['import_out']==1){
            if(!empty($_GET['id_str'])){
                $id = substr($_GET['id_str'],0,-1);
                $this->import_download_count_out($id,$list);
            }
        }
        //var_dump($list);
        $this->assign('list',$list);
        $this->display();
    }


    //分小时下载统计
    function download_count_hour(){
        $where1['status'] = 1;
        if(empty($_GET['begintime'])&&empty($_GET['endtime'])){
            $where['pdate'] = date("Ymd");
        }
        if(!empty($_GET['s_softname'])){
            $where1['softname'] = array('like',"%{$_GET['s_softname']}%");
            $this -> assign('softname',$_GET['s_softname']);
        }
        if(!empty($_GET['s_package'])){
            $where1['package'] = $_GET['s_package'];
            $this -> assign('package',$_GET['s_package']);
        }
        //$_GET['begintime'] =  '2016-05-07';
        //$_GET['endtime'] ='2016-05-09';
        if(!empty($_GET['begintime'])){
            $this -> assign('begintime',$_GET['begintime']);
        }
        if(!empty($_GET['endtime'])){
            $this -> assign('endtime',$_GET['endtime']);
        }
        if((strtotime($_GET['endtime'])-strtotime($_GET['begintime']))>2592000){
            $this->error('最大时间跨度为30天');
        }
        $task_list = $this->cpd_model->table('cpd_task')->where($where1)->field('contract_id,task_id,softname,package,start_tm,end_tm,price')->order('task_id desc')->select();
        //echo $this->cpd_model->getLastSql();
    //var_dump($task_list);
        $list = array();
        foreach($task_list as $k=>$v){
            for($i = $v['start_tm'];$i<=$v['end_tm'];$i=$i+86400){
                if($i>time()) break;
                $tmp = array(
                    'contract_id' => $v['contract_id'],
                    'softname'=>$v['softname'],
                    'task_id' =>$v['task_id']
                );
                if(empty($_GET['begintime'])&&empty($_GET['endtime'])){
                    //未填日期默认处理单天数据
                    if($i==strtotime(date("Y-m-d"))){
                        $list[$v['package']][$i] = $tmp;
                    }
                }else{
                    if($i>=strtotime($_GET['begintime'])&&$i<=strtotime($_GET['endtime'])){
                        $list[$v['package']][$i] = $tmp;
                    }
                }

            }
        }
        //var_dump($list);
        $list  = $this->platform_model->download_count_hour($list);
//        print_r($list);
        //整理数组数据
        $package = array();
        foreach($list as $k=>$v){

            foreach($v as $pack_k=>$pack_v){
                $package[] = $pack_k;
                $all_total = 0;
                foreach($pack_v as $hour_k=>$hour_v){
                    $hour_v['h_total'] = 0;
                    foreach($this->download_config_arr as $down_v){
                        $hour_v['h_total'] +=$hour_v[$down_v];
                    }
                    $all_total += $hour_v['h_total'];
                    $pack_v[$hour_k] = $hour_v;
                }
                //var_dump($all_total);
                $pack_v['total'] = $all_total;
                $v[$pack_k] = $pack_v;
            }
            $list[$k] = $v;
        }
        //排序数组
        //print_r($list);
        $last_list = array();
        foreach($list as $k=>$v){
            foreach($v as $day_k=>$day_v){
                $last_list[$k][$day_k] = $day_v['total'];
            }
            arsort($last_list[$k]);
        }
       //var_dump($package);
        $soft_info = get_table_data(array('package'=>array('in',$package)),'sj_soft','package','package,softname');
        foreach($last_list as $k=>$v){
            foreach($v as $pack_k=>$pack_v){
                //var_dump($list[$k][$pack_k]);
                $list[$k][$pack_k]['softname'] = $soft_info[$pack_k]['softname'];
                $v[$pack_k] = $list[$k][$pack_k];
            }
            $last_list[$k] = $v;
        }

        //print_r($last_list);
        if($_GET['import_out']==1){
            if(!empty($_GET['id_str'])){
                $id = substr($_GET['id_str'],0,-1);
                $this->import_download_count_hour_out($id,$last_list);
            }
        }
        $param = $_GET;
        $param = http_build_query($param);
        $this -> assign('param',$param);
        $this->assign('list',$last_list);
        $this->display();
    }


    function import_download_count_out($id,$list){
        $id_arr = $import_out = array();
        if($id){
            $id_arr = explode(',',$id);
        }

        foreach($id_arr as $k=>$v){
            list($package,$day) = explode('--',$v);
            $import_out[$package][$day] = $list[$package][$day];
        }

        $filename = "CPD数据统计-分位置下载-".date("Y-m-d").'.csv';
        header("Content-type:text/csv");
        header("Content-Disposition:attachment;filename=".$filename);
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');
        $str = "";
        if(empty($import_out))
        {
            $str.=iconv('utf-8','gb2312','没有任何信息');
        }else
        {
            echo iconv('utf-8','gb2312','日期').",";
            echo iconv('utf-8','gb2312',"产品名称").",";
            echo iconv('utf-8','gb2312',"计划ID").",";
            echo iconv('utf-8','gb2312','包名').",";
            echo iconv('utf-8','gb2312','下载量(实际)').",";
            echo iconv('utf-8','gb2312','客户端下载量').",";
            echo iconv('utf-8','gb2312','www站点').",";
            echo iconv('utf-8','gb2312',"M站点").",";
            echo iconv('utf-8','gb2312',"合作下载量").",";
            echo iconv('utf-8','gb2312',"第三方合作下载").",";
            echo iconv('utf-8','gb2312',"其他下载量").",";
            echo iconv('utf-8','gb2312',"无效下载").",";
            echo iconv('utf-8','gb2312',"自然量").",";
            echo iconv('utf-8','gb2312',"下载量(计算)").",";
            echo iconv('utf-8','gb2312',"有效下载").",";
            echo iconv('utf-8','gb2312',"单价").",";
            echo iconv('utf-8','gb2312',"消耗金额").",";
            echo iconv('utf-8','gb2312',"下载完成量").",";
            echo iconv('utf-8','gb2312',"安装完成量").",";
            echo iconv('utf-8','gb2312',"下载->安装率").",";
            echo iconv('utf-8','gb2312',"下载->完成率")."\r\n";
            foreach ($import_out as $k => $v)
            {
                foreach($v as $m_k=>$m_v) {
                    echo iconv('utf-8', 'gb2312', date("Y-m-d",$m_k)) . ",";
                    echo iconv('utf-8', 'gb2312', $m_v['softname']) . ",";
                    echo iconv('utf-8', 'gb2312', $m_v['task_id']) . ",";
                    echo iconv('utf-8', 'gb2312', $k) . ",";
                    echo iconv('utf-8', 'gb2312',$m_v['client_downloaded']+$m_v['www_downloaded']+$m_v['m_downloaded']+$m_v['coop_downloaded']+$m_v['other_downloaded']+$m_v['third_downloaded']) . ",";
                    echo iconv('utf-8', 'gb2312',  empty($m_v['client_downloaded'])?0:$m_v['client_downloaded']) . ",";
                    echo iconv('utf-8', 'gb2312', empty($m_v['www_downloaded'])?0:$m_v['www_downloaded']) . ",";
                    echo iconv('utf-8', 'gb2312', empty($m_v['m_downloaded'])?0:$m_v['m_downloaded']) . ",";
                    echo iconv('utf-8', 'gb2312', empty($m_v['coop_downloaded'])?0:$m_v['coop_downloaded']) . ",";
                    echo iconv('utf-8', 'gb2312', empty($m_v['third_downloaded'])?0:$m_v['third_downloaded']) . ",";
                    echo iconv('utf-8', 'gb2312', empty($m_v['other_downloaded'])?0:$m_v['other_downloaded']) . ",";
                    echo iconv('utf-8', 'gb2312', empty($m_v['download_invalid'])?0:$m_v['download_invalid']) . ",";
                    echo iconv('utf-8', 'gb2312', empty($m_v['nature'])?0:$m_v['nature']) . ",";
                    echo iconv('utf-8', 'gb2312', empty($m_v['nature'])?0:$m_v['count_download']) . ",";
                    echo iconv('utf-8', 'gb2312', $m_v['count_download']-($m_v['download_invalid']+$m_v['nature'])) . ",";
                    echo iconv('utf-8', 'gb2312', $m_v['price']) . ",";
                    echo iconv('utf-8', 'gb2312', $m_v['price']*($m_v['count_download']-($m_v['download_invalid']+$m_v['nature']))) . ",";
                    echo iconv('utf-8', 'gb2312', $m_v['down_ok']) . ",";
                    echo iconv('utf-8', 'gb2312', $m_v['install_num']) . ",";
                    echo iconv('utf-8', 'gb2312', ($m_v['install_num_rate']*100).'%') . ",";
                    echo iconv('utf-8', 'gb2312', ($m_v['down_ok_rate']*100).'%') . "\r\n";
                }
            }
        }
        exit;
    }
    function import_download_count_hour_out($id,$list){
        //print_r($list);
        $id_arr = $import_out = array();
        if($id){
            $id_arr = explode(',',$id);
        }
        //var_dump($id_arr);
        foreach($id_arr as $k=>$v){
            list($package,$day) = explode('--',$v);
            $import_out[$package][$day] = $list[$package][$day];
        }
        //print_r($import_out);exit();
        $filename = "CPD数据统计-分小时下载统计-".date("Y-m-d").'.csv';
        header("Content-type:text/csv");
        header("Content-Disposition:attachment;filename=".$filename);
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');
        $str = "";
        if(empty($import_out))
        {
            $str.=iconv('utf-8','gb2312','没有任何信息');
        }else
        {
            echo iconv('utf-8','gb2312','日期').",";
            echo iconv('utf-8','gb2312',"产品名称").",";
            echo iconv('utf-8','gb2312','包名').",";
            echo iconv('utf-8','gb2312','下载量(实际)').",";
            for($i=1;$i<=24;$i++){
                if($i<10){
                    echo iconv('utf-8','gb2312',"0{$i}").",";
                }else{
                    if($i==24){
                        echo iconv('utf-8','gb2312',"{$i}")."\r\n";
                    }else{
                        echo iconv('utf-8','gb2312',"{$i}").",";
                    }
                }
            }

            foreach ($import_out as $k => $v)
            {
                foreach($v as $m_k=>$m_v) {
                    echo iconv('utf-8', 'gb2312', date("Y-m-d",$k)) . ",";
                    echo iconv('utf-8', 'gb2312', $m_v['softname']) . ",";
                    echo iconv('utf-8', 'gb2312', $m_k) . ",";
                    echo iconv('utf-8', 'gb2312', $m_v['total']) . ",";
                    for($i=1;$i<=24;$i++){
                        if($i==24){
                            echo iconv('utf-8', 'gb2312', $m_v[$i]['h_total']) . "\r\n";
                        }else{
                            echo iconv('utf-8', 'gb2312', $m_v[$i]['h_total']) . ",";
                        }
                    }
                }
            }
        }
        exit;
    }
    //第三方合作下载统计详情
    function third_download(){
        $where = array('status'=>1);
        if(!empty($_GET['s_softname'])){
            $where['softname'] = array('like',"%{$_GET['s_softname']}%");
            $this -> assign('softname',$_GET['s_softname']);
        }
        if(!empty($_GET['s_package'])){
            $where['package'] = $_GET['s_package'];
            $this -> assign('package',$_GET['s_package']);
        }

        if(!empty($_GET['begintime'])){
            if(empty($_GET['endtime'])){
                $this->error('请填结束时间');
            }
            $this -> assign('begintime',$_GET['begintime']);
        }
        if(!empty($_GET['endtime'])){
            if(empty($_GET['begintime'])){
                $this->error('请填开始时间');
            }
            $this -> assign('endtime',$_GET['endtime']);
        }
        if((strtotime($_GET['endtime'])-strtotime($_GET['begintime']))>2592000){
            $this->error('最大时间跨度为30天');
        }
        if(!empty($_GET['task_id'])){
            $where['task_id'] = $_GET['task_id'];
            $this -> assign('task_id',$_GET['task_id']);
        }

        $task_list = $this->cpd_model->table('cpd_task')->where($where)->field('contract_id,task_id,softname,package,start_tm,end_tm')->order('task_id desc')->select();
        //echo $this->cpd_model->getLastSql();
        //var_dump($task_list);
        foreach($task_list as $k=>$v) {
            for ($i = $v['start_tm']; $i <= $v['end_tm']; $i = $i + 86400) {
                if ($i > time()) break;
                $tmp = array(
                    'contract_id' => $v['contract_id'],
                    'softname' => $v['softname'],
                    'task_id' => $v['task_id']
                );
                if (empty($_GET['begintime']) && empty($_GET['endtime'])) {
                    //未填日期默认处理单天数据
                    if ($i == strtotime(date("Y-m-d"))) {
                        $list[$v['package']][$i] = $tmp;
                    }
                } else {
                    if ($i >= strtotime($_GET['begintime']) && $i <= strtotime($_GET['endtime'])) {
                        $list[$v['package']][$i] = $tmp;
                    }
                }
            }
        }
        //var_dump($list);
        list($list,$channel_info) = $this->third_model->get_third_more($list);
        if($_GET['import_out']==1){
            if(!empty($_GET['id_str'])){
                $id = substr($_GET['id_str'],0,-1);
                $this->import_third_download_out($id,$list,$channel_info);
            }
        }
       // var_dump($channel_info);
        $this->assign('channel_info',$channel_info);
        $this->assign('list',$list);
        $param = $_GET;
        $param = http_build_query($param);
        //var_dump($param);
        $this -> assign('param',$param);
        $this->display();
    }

    function all_third_download(){
        if(!empty($_GET['begintime'])){
            if(empty($_GET['endtime'])){
                $this->error('请填结束时间');
            }
            $where['submit_day'][] = array("egt",strtotime($_GET['begintime']));
            $this -> assign('begintime',$_GET['begintime']);
        }
        if(!empty($_GET['endtime'])){
            if(empty($_GET['begintime'])){
                $this->error('请填开始时间');
            }
            $where['submit_day'][] = array("elt",strtotime($_GET['endtime']));
            $this -> assign('endtime',$_GET['endtime']);
        }
        if((strtotime($_GET['endtime'])-strtotime($_GET['begintime']))>2592000){
            $this->error('最大时间跨度为30天');
        }
        if(empty($_GET)){
            $where['submit_day'] = strtotime(date("Y-m-d",time()));
        }
        if($_GET['package']){
            $where['package'] = $_GET['package'];
        }
        list($list,$channel_info) = $this->third_model->get_all_third_download($where);
        if($_GET['import_out']==1){
            if(!empty($_GET['id_str'])){
                $id = substr($_GET['id_str'],0,-1);
                $this->import_all_third_download_out($id,$list,$channel_info);
            }
        }
        $this->assign('channel_info',$channel_info);
        $this->assign('list',$list);
        $param = $_GET;
        $param = http_build_query($param);
        //var_dump($param);
        $this -> assign('param',$param);
        $this->display();
    }

    function import_all_third_download_out($id,$list,$channel_info){
        $id_arr = $import_out = array();
        if($id){
            $id_arr = explode(',',$id);
        }

        foreach($id_arr as $k=>$v){
            $import_out[$v] = $list[$v];
        }
        $filename = "第三方合作站点下载-统计-".date("Y-m-d").'.csv';
        header("Content-type:text/csv");
        header("Content-Disposition:attachment;filename=".$filename);
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');
        $str = "";
        if(empty($import_out))
        {
            $str.=iconv('utf-8','gb2312','没有任何信息');
        }else
        {
            echo iconv('utf-8','gb2312','日期').",";
            foreach($channel_info as $c_k=>$c_v){
                echo iconv('utf-8','gb2312',$c_v['chname']).",";
            }
            echo "\r\n";
            foreach ($import_out as $k => $v)
            {
                    echo iconv('utf-8', 'gb2312', date("Y-m-d",$k)) . ",";
                    foreach($channel_info as $c_k=>$c_v){
                        echo $v[$c_k] . ",";
                    }
                    echo  "\r\n";
            }
        }
        exit;
    }


    function import_third_download_out($id,$list,$channel_info){
        $id_arr = $import_out = array();
        if($id){
            $id_arr = explode(',',$id);
        }

        foreach($id_arr as $k=>$v){
            list($package,$day) = explode('--',$v);
            $import_out[$package][$day] = $list[$package][$day];
        }
        $filename = "第三方合作站点下载-统计详情-".date("Y-m-d").'.csv';
        header("Content-type:text/csv");
        header("Content-Disposition:attachment;filename=".$filename);
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');
        $str = "";
        if(empty($import_out))
        {
            $str.=iconv('utf-8','gb2312','没有任何信息');
        }else
        {
            echo iconv('utf-8','gb2312','日期').",";
            echo iconv('utf-8','gb2312',"产品名称").",";
            echo iconv('utf-8','gb2312',"计划ID").",";
            echo iconv('utf-8','gb2312','包名').",";
            foreach($channel_info as $c_k=>$c_v){
                echo iconv('utf-8','gb2312',$c_v['chname']).",";
            }
            echo "\r\n";
            foreach ($import_out as $k => $v)
            {
                foreach($v as $m_k=>$m_v) {
                    echo iconv('utf-8', 'gb2312', date("Y-m-d",$m_k)) . ",";
                    echo iconv('utf-8', 'gb2312', $m_v['softname']) . ",";
                    echo iconv('utf-8', 'gb2312', $m_v['task_id']) . ",";
                    echo iconv('utf-8', 'gb2312', $k) . ",";
                    foreach($channel_info as $c_k=>$c_v){
                      echo $m_v['third_downloaded'][$c_k] . ",";
                    }
                    echo  "\r\n";
                }
            }
        }
        exit;
    }

    //CPD下载量
    function cpd_download(){
        $check_arr = array('s_softname','s_package');
        $bo = false;
        foreach($check_arr as $v){
            if(!empty($_POST[$v])) $bo = true;
        }
        if(!$bo&&$_POST['__hash__']) $this->error('产品包名,产品名称至少一项不能为空');
        $where = array('package'=>array('exp'," != '' "),'add_cnt'=>array('exp'," != '0' "));
        if(!empty($_POST['s_softname'])){
            $where_s = array('a.status'=>1,'a.hide'=>1);
            $where_s['a.softname'] = array('like',"%{$_POST['s_softname']}%");
            $s_pack = $this->get_soft($where_s);
            if($s_pack){
                $package =  array();
                foreach($s_pack as $k=>$v){
                    $package[] = $v['package'];
                    $softname[$v['package']]['softname'] = $v['softname'];
                    $softname[$v['package']]['category'] = $v['name'];
                }
                $package = array_unique($package);
                $where['package'] = array('in',$package);
                foreach($package as $v){
                    $where_bi['package'][] = $v;
                }
            }else{
                $this->error('未找到软件');
            }
            $this -> assign('s_softname',$_POST['s_softname']);
        }
		$_POST['s_package'] = str_replace(' ','',$_POST['s_package']);
        if(!empty($_POST['s_package'])){
            if(strpos($_POST['s_package'],',')!=false){
                $s_package  = explode(',',$_POST['s_package']);
                $where['package'] = array('in',$s_package);
                foreach($s_package as $v){
                    $where_bi['package'][] = $v;
                }

            }else{
                $where['package'] = $_POST['s_package'];
                $where_bi['package'][] = $_POST['s_package'];
                $s_package = array($_POST['s_package']);
            }

            $tmp_softname  =  $this->get_cpdsoft_info($s_package);
            if(!empty($_POST['s_softname'])){
                $softname = array_merge($softname,$tmp_softname);
            }else{
                $softname = $tmp_softname;
            }
            $this -> assign('package',$_POST['s_package']);
        }
        if($bo){
            if(!empty($_POST['begintime'])){
                $s_begin = strtotime($_POST['begintime']);
                $where['submit_day'][] = array('exp'," >= {$s_begin}");
                $where_bi['begintime'] = $_POST['begintime'];
                $this -> assign('begintime',$_POST['begintime']);
            }else{
                $this->error('请选择开始时间');
            }
            if(!empty($_POST['endtime'])){
                $s_end = strtotime($_POST['endtime']);
                $where['submit_day'][] = array('exp'," <= {$s_end}");
                $where_bi['endtime'] = $_POST['endtime'];
                $this -> assign('endtime',$_POST['endtime']);
            }else{
                $this->error('请选择结束时间');
            }
            if($s_end-$s_begin>86400*30*2){
                $this->error('时间跨度不允许超过两个月');
            }
            $package = $chl_cid = $res = array();
            // BI数据
            $bi = $this->third_model->get_bi_download($where_bi);
            if($bi){
                $res = $bi;
            }
            //商务下载量
            $this->third_model->get_bi_buss_download($where_bi,$res);

            //CPD外投数据
            $info = $this->third_model->table('sj_download_add as a')->where($where)->order('submit_day desc')->select();

            if($info){
                foreach($info as $k=>$v){
                    $package[] = $v['package'];
                    if(isset($res[$v['submit_day']][$v['package']]['outer_down'])) $res[$v['submit_day']][$v['package']]['outer_down'] = 0;
                    $res[$v['submit_day']][$v['package']]['outer_down'] += $v['add_cnt'];
                }
            }
            krsort($res);

            $last = array();
            foreach($res as $key=>$val){
                foreach($val as $k=>$v){
                    $last[] = array(
                        'day' => $key,
                        'package' => $k,
                        'market' => $v['market'],
                        'wap' => $v['wap'],
                        'web' => $v['web'],
                        'buss' => $v['buss'],
                        'coop' => $v['coop'],
                        'other'=> $v['other'],
                        'outer_down'=> $v['outer_down'],
                        'ziyoo'=> $v['ziyoo']
                    );
                }
            }
//            var_dump($last);
            if($_POST['import_out']==1){
                $id_str = empty($_POST['id_str'])?'':$_POST['id_str'];
                $this->import_out_cpd_download($softname,$last,$id_str);
            }
            $this->assign('res',$last);
            $this->assign('softname',$softname);
        }
        $param = $_POST;
        $param = http_build_query($param);
        $this -> assign('param',$param);
        $this->display();
    }

    function get_cpdsoft_info($package){
        $package = array_unique($package);
        $s_pack = $this->get_soft(array('a.package'=>array('in',$package)));
        foreach($s_pack as $k=>$v){
            $softname[$v['package']]['softname'] = $v['softname'];
            $softname[$v['package']]['category'] = $v['name'];
        }
        return $softname;
    }
    //CPD外投查询
    function outer_download(){
        $model = M('');
        $softname = array();
//        $check_arr = array('s_softname','s_package','chname');
//        $bo = false;
//        foreach($check_arr as $v){
//            if(!empty($_GET[$v])) $bo = true;
//        }
//        if(!$bo&&$_GET['__hash__']) $this->error('产品包名,产品名称,渠道名称至少一项不能为空');
        $where = array('package'=>array('exp'," != '' "),'add_cnt'=>array('exp'," != '0' "));
        if(!empty($_GET['s_softname'])){
            $where_s = array('status'=>1,'hide'=>1);
            $where_s['a.softname'] = array('like',"%{$_GET['s_softname']}%");
            $s_pack = $this->get_soft($where_s);
            if($s_pack){
                $package =  array();
                foreach($s_pack as $k=>$v){
                    $package[] = $v['package'];
                    $softname[$v['package']] = $v['softname'];
                }
                $package = array_unique($package);
                $where['package'] = array('in',$package);
            }
            $this -> assign('s_softname',$_GET['s_softname']);
        }
        if(!empty($_GET['s_package'])){

            if(strpos($_GET['s_package'],',')!=false){
                $where['package'] = array('in',explode(',',$_GET['s_package']));
            }else{
                $where['package'] = $_GET['s_package'];
            }
            $this -> assign('package',$_GET['s_package']);
        }

        if(!empty($_GET['begintime'])){
            $s_begin = strtotime($_GET['begintime']);
            $where['submit_day'][] = array('exp'," >= {$s_begin}");
            $this -> assign('begintime',$_GET['begintime']);
        }else{
            $s_begin = strtotime('-1 days',strtotime(date('Y-m-d')));
            //默认显示前一天数据
            $this -> assign('begintime',date('Y-m-d',strtotime('-1 days')));
            $where['submit_day'] = array('exp'," >= {$s_begin}");
        }
        if(!empty($_GET['endtime'])){
            $s_end = strtotime($_GET['endtime']);
            $where['submit_day'][] = array('exp'," <= {$s_end}");
            $this -> assign('endtime',$_GET['endtime']);
        }else{
            $s_end = strtotime(date('Y-m-d'));
            $this -> assign('endtime',date('Y-m-d'));
            $where['submit_day'][] = array('exp'," <= {$s_end}");
        }

        if($s_end-$s_begin>86400*30){
            $this->error('时间跨度不允许超过一个月');
        }
        if(!empty($_GET['chname'])){
            $channel = $model->table('sj_channel')->where(array('chname'=>$_GET['chname']))->field('chl_cid')->find();
            $where['chl_cid'] = $channel['chl_cid'];
            $this -> assign('chname',$_GET['chname']);
        }
        import("@.ORG.Page2");
        if($_GET['import_out']==1) {
            if (!empty($_GET['id_str'])) {
                $id = explode(',',substr($_GET['id_str'],0,-1));
                $where['id'] = array('in',$id);
            }
        }

        $count = $this->third_model->table('sj_download_add as a')->where($where)->count();
        $num = $this->third_model->table('sj_download_add as a')->where($where)->field('sum(`add_cnt`) as all_count')->find();
        $Page = new Page($count, 10);
        if($_GET['import_out']==1){
            $this->import_outer_download($where,$count);
            exit();
        }else{
            $info = $this->third_model->table('sj_download_add as a')->where($where)->field('a.*')->limit($Page->firstRow . ',' . $Page->listRows)->order('submit_day desc')->select();
        }

//      echo $this->third_model->getLastSql();
        $package = $chl_cid = array();
//        $all_count = 0;

        foreach($info as $k=>$v){
            $package[] = $v['package'];
            $chl_cid[] = $v['chl_cid'];
//            $all_count += $v['add_cnt'];
        }

        $chname_arr = $this->get_chname($chl_cid);
        if(!$softname){
            $package = array_unique($package);
            $s_pack = $this->get_soft(array('a.package'=>array('in',$package)));
            foreach($s_pack as $k=>$v){
                $softname[$v['package']] = $v['softname'];
            }
        }

        $this->assign('info',$info);
        $this->set_assign_public($Page);
        $this->assign('chname_arr',$chname_arr);
        $this->assign('all_count',$num['all_count']);
        $this->assign("softname",$softname);
        $this->display();
    }

    function set_assign_public($Page=''){
        if($Page){
            $Page->setConfig('header', '篇记录');
            $Page->rollPage = 10;
            $Page->setConfig('first', '首页');
            $Page->setConfig('last', '尾页');
            $show = $Page->show();
            $this->assign("page", $show);
        }
        $param = $_GET;
        $param = http_build_query($param);
        $this -> assign('param',$param);
    }

    function get_soft($where){
        $model = M('');
        $info = $model->table('sj_soft as a')->where($where)->join('sj_category as b on SUBSTR(a.`category_id`,2,LENGTH(a.`category_id`)-2) = b.`category_id`')->field('a.softname,a.package,b.name')->select();
        return $info;
    }

    function  get_chname($chl_cid){
        $model = M('');
        $chl_cid = array_unique($chl_cid);
        $res = get_table_data(array('chl_cid'=>array('in',$chl_cid)),'sj_channel','chl_cid','chl_cid,chname');

        return $res;
    }
    //CPD外投导出
    function import_outer_download($where,$count){
        header( 'Content-Type:text/html;charset=utf-8 ');
        ini_set('memory_limit','256M');
        $file_name=iconv("UTF-8", "GBK", "CPD数据统计-CPD外投数据-".date("Y-m-d").'.csv');
        header ( 'Content-Type: application/vnd.ms-excel' );
        header ( 'Content-Disposition: attachment;filename='.$file_name );
        header ( 'Cache-Control: max-age=0' );
        $fp = fopen ( 'php://output', 'a' );

        fwrite($fp,chr(0xEF).chr(0xBB).chr(0xBF));
        $head = array('日期', '产品名称', '包名', '渠道名称', '下载量');
        fputcsv($fp, $head);
        $model = M('');
        $first = $last = 0;
        $limit = 5000;
        $all_count = 0;
        while(true){
            $info = $this->third_model->table('sj_download_add as a')->where($where)->limit($first . ',' .$limit )->select();
            $softname = $package = $chl_cid = array();
            foreach($info as $k=>$v){
                $package[] = $v['package'];
                $chl_cid[] = $v['chl_cid'];
            }

            $chname_arr = $this->get_chname($chl_cid);
            if(!$softname){
                $package = array_unique($package);
                $s_pack = $this->get_soft(array('package'=>array('in',$package)));
                foreach($s_pack as $k=>$v){
                    $softname[$v['package']] = $v['softname'];
                }
            }

            $first = $first + $limit;

            foreach($info as $i_k=>$i_v){
                $put_arr = array();
                $i_v['softname'] = $softname[$i_v['package']];
                $i_v['chname'] = $chname_arr[$i_v['chl_cid']]['chname'];
                $put_arr['date'] = $i_v['submit_day']?date("Y-m-d",$i_v['submit_day']):"\t";
                $put_arr['softname'] = $i_v['softname']?$i_v['softname']:"\t";
                $put_arr['package'] = $i_v['package']?$i_v['package']:"\t";
                $put_arr['chname'] = $i_v['chname']?$i_v['chname']:"\t";
                $put_arr['add_cnt'] = $i_v['add_cnt']?$i_v['add_cnt']:"\t";
                $all_count += $i_v['add_cnt'];

                fputcsv($fp, $put_arr);
            }
            unset($s_pack);
            unset($put_arr);
            unset($info);
            unset($softname);
            unset($package);
            unset($chl_cid);
            $foot = array('总计', '', '', '', $all_count);
            fputcsv($fp, $foot);
            if($first>$count){
                break;
            }
        }
        fclose($fp);
        exit();
    }

    function import_out_cpd_download($softname,$res,$id_str){
        header( 'Content-Type:text/html;charset=utf-8 ');
        ini_set('memory_limit','256M');
        $file_name=iconv("UTF-8", "GBK", "CPD结算数据统计-".date("Y-m-d").'.csv');
        header ( 'Content-Type: application/vnd.ms-excel' );
        header ( 'Content-Disposition: attachment;filename='.$file_name );
        header ( 'Cache-Control: max-age=0' );
        $fp = fopen ( 'php://output', 'a' );

        if(empty($id_str)){
            $import = $res;
        }else{
            $id_arr = explode(',',$id_str);
            foreach($id_arr as $k=>$v){
                if($v!='')
                $import[] = $res[$v];
            }
        }
        fwrite($fp,chr(0xEF).chr(0xBB).chr(0xBF));
        $head = array('日期', '产品名称', '包名', '分类', '客户端下载','wap','web','商务下载','其他下载','合作下载','CPD外投','智友','总计');
        fputcsv($fp, $head);
        foreach($import as $key=>$val){
            $put_arr = array();
            $put_arr['date'] = date("Y-m-d",$val['day']);
            $put_arr['softname'] = $softname[$val['package']]['softname']?$softname[$val['package']]['softname']:"\t";
            $put_arr['package'] = $val['package'];
            $put_arr['category'] = $softname[$val['package']]['category']?$softname[$val['package']]['category']:"\t";
            $put_arr['market'] = $val['market']?$val['market']:0;
            $put_arr['wap'] = $val['wap']?$val['wap']:0;
            $put_arr['web'] = $val['web']?$val['web']:0;
            $put_arr['buss'] = $val['buss']?$val['buss']:0;
            $put_arr['other'] = $val['other']?$val['other']:0;
            $put_arr['coop'] = $val['coop']?$val['coop']:0;
            $put_arr['CPD'] = $val['outer_down']?$val['outer_down']:0;
            $put_arr['ziyoo'] = $val['ziyoo']?$val['ziyoo']:0;
            $put_arr['all_count'] = $put_arr['market'] +$put_arr['wap']+$put_arr['web']+$put_arr['buss']+$put_arr['other']+$put_arr['coop']+$put_arr['CPD']+$put_arr['ziyoo'];
            fputcsv($fp, $put_arr);
            unset($put_arr);
        }
        fclose($fp);
        exit();
    }

    //终止合同
    function stop_contract(){
        if($_POST){
            $now = time();
            $this->check_authority($_POST['id']);
            $contract = $this->cpd_model->table('cpd_contract')->where(array('id'=>$_POST['id']))->find();
            if($contract['start_tm']>=$now){
                $this->error('合同未开始不可终止');
            }
            if($this->flexible_sys==2){
                $_POST['stop_tm'] = date("Y-m-d",strtotime("{$_POST['stop_tm']}-01 + 1month  - 1day"));
            }
            if(!$_POST['stop_tm']){
                $this->error('终止时间不能为空');
            }
            if(strtotime($_POST['stop_tm'])<$contract['start_tm']){
                $this->error('终止日期不能比合同开始日期小');
            }
            if(strtotime($_POST['stop_tm'])>$contract['end_tm']){
                $this->error('终止时间必须小于合同的结束时间');
            }

            $task_i = $this->cpd_model->table('cpd_task')->where(array('contract_id'=>$_POST['id'],'status'=>1))->order('end_tm desc')->find();
            if($task_i&&$task_i['end_tm']>=strtotime($_POST['stop_tm'])){
                $this->error('合同有进行中/未开始的任务，不可终止');
            }else{
                $old_status = $this->cpd_model->table('cpd_contract')->where(array('id'=>$_POST['id']))->find();

                $s_data = array('stop_tm'=>strtotime($_POST['stop_tm']),'update_tm'=>time());
                if($old_status['status']==2&&strtotime($_POST['stop_tm'])>$now){
                    //由终止改为非终止需检查合同时间是否重叠
                    $bo = $this->check_contract($contract);
                    if(!$bo){
                        $this->error('合同时间重叠');
                    }
                    $s_data['status'] = 1;
                }
                $res = $this->cpd_model->table('cpd_contract')->where(array('id'=>$_POST['id']))->save($s_data);
                if($res){
                    $this->writelog("CPD广告：终止了合同id为{$_POST['id']}，终止时间为{$_POST['stop_tm']}",'cpd_contract',$_POST['id'],__ACTION__ ,'','edit');
                    $this->success('终止成功');
                }else{
                    $this->error('终止失败');
                }
            }
        }
        if($_GET['id']){
            $cpd_info = $this->cpd_model->where("id = {$_GET['id']}")->find();
            $clients = $this->Client_db -> where("id = '{$cpd_info['custom_id']}'")->field('client_name') ->find();
            $cpd_info['custom_name'] = $clients['client_name'];
            $this->assign('cpd_info',$cpd_info);
        }
        $this->get_datepicker();
        $this->display($this->tmp_path.__FUNCTION__);
    }

    //下载终止合同
    function download_stop_contract(){
        require_once(dirname(__FILE__) . '/../../ORG/tcpdf/config/tcpdf_config_alt.php');
        require_once(dirname(__FILE__) . '/../../ORG/tcpdf/tcpdf.php');
        $c_id = $_GET['contract_id'];
        $contract = $this->cpd_model->table('cpd_contract')->where(array('id'=>$c_id))->field('id,softname,custom_id,start_tm,stop_tm,contract_code,contact_config_id')->find();
        if(!$contract){
            $this->error('合同不存在');
        }else {
            $client_info = $this->Client_db->where(array('id' => $contract['custom_id']))->field('cpd_company')->find();
            $contract_config = $this->cpd_model->table('cpd_contract_config')->where(array('id'=>$contract['contact_config_id']))->find();
            if (!$client_info) {
                $this->error('客户不存在');
            }
            require_once(dirname(__FILE__) . '/../../ORG/tcpdf/stop_contract.php');
            $search = array('cpd_company','contract_code','start_y','start_m','start_d','stop_y','stop_m','stop_d','c_name','c_company','c_address','c_tel','c_contact');
            //系统B只显示到月份
            if($this->flexible_sys==1){
                $start_day = date("d",$contract['start_tm']).'日';
                $end_day = date("d",$contract['stop_tm']).'日';
            }else{
                $start_day = '';
                $end_day = '';
            }
            $replace = array($client_info['cpd_company'],$contract['contract_code'],date("Y",$contract['start_tm']),date("m",$contract['start_tm']),$start_day,date("Y",$contract['stop_tm']),date("m",$contract['stop_tm']),$end_day,$contract_config['c_name'],$contract_config['c_company'],$contract_config['c_address'],$contract_config['c_tel'],$contract_config['c_contact']);
            $html = str_replace($search,$replace,$html);
            $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
            $pdf->setPrintHeader(false);
            $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
            $pdf->SetAutoPageBreak(TRUE, 25);
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
            $pdf->AddPage();
            $pdf->SetFont('stsongstdlight', '', 12);
            $pdf->writeHTML($html, true, 0, true, true);
            //$pdf->Image(dirname(realpath(__FILE__)) . '/../lib/tcpdf/hetong.gif', 140, $pdf_height, 50, '', '', '', '', false, 300);
            $pdf->lastPage();
            $pdf->Output(date('Ymd',time()).'.pdf', 'I');
            exit();
        }
    }
    //下载网签合同
    function download_contract(){
        require_once(dirname(__FILE__) . '/../../ORG/tcpdf/config/tcpdf_config_alt.php');
        require_once(dirname(__FILE__) . '/../../ORG/tcpdf/tcpdf.php');
        $c_id = $_GET['contract_id'];
        $contract = $this->cpd_model->table('cpd_contract')->where(array('id'=>$c_id))->field('id,softname,custom_id,start_tm,end_tm,contact_config_id')->find();
        if(!$contract){
            $this->error('合同不存在');
        }else{
            $client_info = $this->Client_db->where(array('id'=>$contract['custom_id']))->field('cpd_company,cpd_company_address,cpd_contact_phone,cpd_contact_name')->find();
            $contract_config = $this->cpd_model->table('cpd_contract_config')->where(array('id'=>$contract['contact_config_id']))->find();
            if(!$client_info){
                $this->error('客户不存在');
            }
            require_once(dirname(__FILE__) . '/../../ORG/tcpdf/contract.php');
            $search = array('cpd_company','cpd_address','cpd_contact_phone','cpd_contact_name','softname','start_y','start_m','start_d','end_y','end_m','end_d','c_name','c_company','c_address','c_tel','c_contact');
            //系统B只显示到月份
            if($this->flexible_sys==1){
                $start_day = date("d",$contract['start_tm']).'日';
                $end_day = date("d",$contract['end_tm']).'日';
            }else{
                $start_day = '';
                $end_day = '';
            }
            $replace = array($client_info['cpd_company'],$client_info['cpd_company_address'],$client_info['cpd_contact_phone'],$client_info['cpd_contact_name'],$contract['softname'],date("Y",$contract['start_tm']),date("m",$contract['start_tm']),$start_day,date("Y",$contract['end_tm']),date("m",$contract['end_tm']),$end_day,$contract_config['c_name'],$contract_config['c_company'],$contract_config['c_address'],$contract_config['c_tel'],$contract_config['c_contact']);
            $html = str_replace($search,$replace,$html);
            $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
            $pdf->setPrintHeader(false);
            $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
            $pdf->SetAutoPageBreak(TRUE, 25);
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
            $pdf->AddPage();
            $pdf->SetFont('stsongstdlight', '', 12);
            $pdf->writeHTML($html, true, 0, true, true);
            //$pdf->Image(dirname(realpath(__FILE__)) . '/../lib/tcpdf/hetong.gif', 140, $pdf_height, 50, '', '', '', '', false, 300);
            $pdf->lastPage();
            $pdf->Output(date('Ymd',time()).'.pdf', 'I');
            exit();
        }
    }

    //协议主体配置
    function contract_config(){
        $list = $this->cpd_model->table('cpd_contract_config')->order('add_tm desc')->select();
        $this->assign('list',$list);
        $this->display();
    }

    function save_contract_config(){
        if($_POST){
            $c_name = $_POST['c_name'];
            $has_info = $this->cpd_model->table('cpd_contract_config')->where(array('c_name'=>$c_name,'status'=>1))->find();
            if($has_info){
                $this->error('协议主体名称已存在，请更换');
            }
            $data = array(
                'c_name' => $c_name,
                'c_company' => $_POST['c_company'],
                'c_address' => $_POST['c_address'],
                'c_tel' => $_POST['c_tel'],
                'c_contact' => $_POST['c_contact'],
                'add_tm' => time()
            );
            $res = $this->cpd_model->table('cpd_contract_config')->add($data);
            if($res){
                $this->writelog("配置管理-协议主体配置：添加了id为{$res}的协议主体配置",'cpd_contract',$_POST['id'],__ACTION__ ,'','edit');
                $this->success('添加成功');
            }else{
                $this->error('添加失败');
            }
        }
        $this->display();
    }

    //对账公共
    function bill_pub($type){
        $where = array('a.status'=>array('neq',0));
        if($this->flexible_sys==1){
            $where['b.flexible_sys'] = 1;
        }
        if(!empty($_GET['s_softname'])){
            $where['b.softname'] = array('like',"%{$_GET['s_softname']}%");
            $this -> assign('softname',$_GET['s_softname']);
        }
        if(!empty($_GET['s_package'])){
            $where['b.package'] = $_GET['s_package'];
            $this -> assign('package',$_GET['s_package']);
        }
        if(!empty($_GET['begintime'])){
            $where['a.bill_month'][] = array('egt',date('Ym',strtotime($_GET['begintime'])));
            $this -> assign('begintime',$_GET['begintime']);
        }
        if(!empty($_GET['endtime'])){
            $where['a.bill_month'][] = array('elt',date('Ym',strtotime($_GET['endtime'])));
            $this -> assign('endtime',$_GET['endtime']);
        }
        if(!empty($_GET['bill_status'])){
            $where['a.bill_status'] = $_GET['bill_status'];
            $this -> assign('s_bill_status',$_GET['bill_status']);
        }
        if(!empty($_GET['expend_status'])){
            $where['a.expend_status'] = $_GET['expend_status'];
            $this -> assign('s_expend_status',$_GET['expend_status']);
        }
        if(!empty($_GET['receipt_status'])){
            $where['a.receipt_status'] = $_GET['receipt_status'];
            $this -> assign('s_receipt_status',$_GET['receipt_status']);
        }
        if(!empty($_GET['contact_config_id'])){
            $where['b.contact_config_id'] = $_GET['contact_config_id'];
            $this -> assign('contact_config_id',$_GET['contact_config_id']);
        }
        if(!empty($_GET['s_contract_code'])){
            $where['b.contract_code'] = $_GET['s_contract_code'];
            $this -> assign('contract_code',$_GET['s_contract_code']);
        }
        if($_GET['import_out']==1){
            if(!empty($_GET['id_str'])){
                $id = substr($_GET['id_str'],0,-1);
                $where['a.id'] = array('in',$id);
            }
        }
        $this->assign('bill_status',$this->bill_status);
        $this->assign('receipt_status',$this->receipt_status);
        $this->assign('expend_status',$this->expend_status);
        $this->contract_config_pub();
        $count = $this->cpd_model ->table('cpd_month_bill as a') ->join('cpd_contract as b on a.contract_id = b.id')-> where($where) -> count();

        import("@.ORG.Page");
        $Page=new Page($count,20);
        $order = '';
        if($type=='bill_status'){
            $order = "IF(bill_status='2',1,0),bill_status ASC";
        }else if($type=='expend_status'){
            $order = "IF(expend_status='2',1,0),expend_status ASC";
        }else if($type=='receipt_status'){
            $order = "IF(receipt_status='2',1,0),receipt_status ASC";
        }
        $order .= ",bill_month desc";
        $bill = $this->cpd_model->table('cpd_month_bill as a')->join('cpd_contract as b on a.contract_id = b.id')->where($where)-> limit($Page->firstRow.','.$Page->listRows)->field('a.*,b.softname,b.package,b.contract_code,b.contact_config_id,b.category_name,b.custom_id')->order($order)->select();
        //echo $this->cpd_model->getLastSql();var_dump($count);
        $this->assign('bill',$bill);
        $custom_id = $custom_name = array();
        foreach($bill as $k=>$v){
            $custom_id[] = $v['custom_id'];
        }
        $custom_id = array_unique($custom_id);
        $clients = $this->Client_db -> where(array('id'=>array('in',$custom_id)))->field('id,client_name') ->select();
//        echo $this->Client_db->getLastSql();

        foreach($clients as $c_k=>$c_v){
            $custom_name[$c_v['id']] = $c_v['client_name'];
        }
        $this->assign ( "custom_name", $custom_name );
        $Page->setConfig('header','条记录');
        $Page->setConfig('first','<<');
        $Page->setConfig('last','>>');
        $show =$Page->show();
        $this->assign ( "page", $show );
        if($_GET['import_out']==1){
            $this->import_out_bill($bill,$custom_name);
        }
    }

    //消耗审核
    function  expend_bill(){
        $this->bill_pub('expend_status');
        $this->display($this->tmp_path.__FUNCTION__);
    }

    //收款审核
    function receipts_bill(){
        $this->bill_pub('receipt_status');
        $this->display($this->tmp_path.__FUNCTION__);
    }

    //月对账
    function month_bill(){
        $this->bill_pub('bill_status');
        $this->display($this->tmp_path.__FUNCTION__);
    }

    //导出账单
    function import_out_bill($bill,$custom_name){
        header( 'Content-Type:text/html;charset=utf-8 ');
        ini_set('memory_limit','256M');
        $file_name=iconv("UTF-8", "GBK", "账单-".date("Y-m-d").'.csv');
        header ( 'Content-Type: application/vnd.ms-excel' );
        header ( 'Content-Disposition: attachment;filename='.$file_name );
        header ( 'Cache-Control: max-age=0' );
        $fp = fopen ( 'php://output', 'a' );

        fwrite($fp,chr(0xEF).chr(0xBB).chr(0xBF));
        $head = array('月份', '合同编号', '产品名称','包名','分类', '客户名称', '协议主体','余额_上月','余额_本月','预存金额','收款金额','转出金额','消耗下载量(核减后)','消耗金额(核减后)','消耗金额(充值)','消耗金额(配送)','收款状态','消耗状态','账单状态','备注');
        fputcsv($fp, $head);
        foreach($bill as $k=>$v){
            $put_arr = array(
                $v['bill_month'],
                $v['contract_code'],
                $v['softname'],
                $v['package'],
                $v['category_name'],
                $custom_name[$v['custom_id']],
                $this->c_a_config[$v['contact_config_id']]['c_name'],
                m_number_format($v['lt_month_balance']),
                m_number_format($v['month_balance']),
                m_number_format($v['month_prestore']),
                m_number_format($v['month_receipts']),
                m_number_format($v['month_out']),
                $v['month_download'],
                m_number_format($v['month_cost']),
                m_number_format($v['month_cost_recharge']),
                m_number_format($v['month_cost_delivery']),
                $this->receipt_status[$v['receipt_status']],
                $this->expend_status[$v['expend_status']],
                $this->bill_status[$v['bill_status']],
                $v['beizhu']
            );
            fputcsv($fp, $put_arr);
            unset($put_arr);
        }
        fclose($fp);
        exit();
    }

    //合同协议主体
    function contract_config_pub(){
        $c_config = $this->cpd_model->table('cpd_contract_config')->where(array('status'=>1))->select();
        foreach($c_config as $k=>$v){
            $this->c_a_config[$v['id']] = $v;
        }
        $this->assign('c_a_config',$this->c_a_config);
    }

    //修改账单状态
    function edit_bill_status(){
        $type_arr = array('receipt_status'=>'收款状态','expend_status'=>'消耗状态','bill_status'=>'账单状态');
        $type = $_GET['type'];
        $id = $_GET['id'];
        $status = $_GET['status'];

        if(!$type||!$id||!$status||!array_key_exists($type,$type_arr)){
            $this->error('参数错误');
        }
        $where = array('id'=>$id);
        $old_info = $this->cpd_model->table('cpd_month_bill')->where($where)->find();
        if($old_info['bill_status']==2){
            if($type!='bill_status') $this->error('账单已确认');
        }
        $this->check_authority($old_info['contract_id']);
        $data = array($type=>$status,'update_tm'=>time());
        if($type=='bill_status'&&$status==1){
            //驳回判断是不是最后一个月
            $bill_s = $this->cpd_model->table('cpd_month_bill')->where(array('bill_status'=>2,'contract_id'=>$old_info['contract_id'],'bill_month'=>array('exp'," > '{$old_info['bill_month']}' ")))->find();
//            echo $this->cpd_model->getLastSql();
//            var_dump($bill_s);
            if($bill_s){
                $this->error('请先驳回最后一个月的月账，依次进行驳回，请核查');
            }
        }
        if($type=='bill_status'&&$status==2){
            //判断是否存在之前月账没有确认
            $bill_s = $this->cpd_model->table('cpd_month_bill')->where(array('bill_status'=>array('exp',' != 2 '),'contract_id'=>$old_info['contract_id'],'bill_month'=>array('exp'," < '{$old_info['bill_month']}' ")))->find();
//            echo $this->cpd_model->getLastSql();
//            var_dump($bill_s);
            if($bill_s){
                $this->error('上月或更早的对账单未确认，请核查');
            }
            //账单确认时计算本月余额
            $last_bill = $this->cpd_model->table('cpd_month_bill')->where(array('contract_id'=>$old_info['contract_id'],'bill_month'=>array('exp'," < '{$old_info['bill_month']}' ")))->order('bill_month desc')->find();
            $data['lt_month_balance'] = $last_bill['month_balance'];
            $data['month_balance'] =  $data['lt_month_balance']+$old_info['month_prestore']+$old_info['month_out']-($old_info['month_cost_recharge']+$old_info['month_cost_delivery']);

        }else{
            $data['month_balance'] = 0;
        }



        $res = $this->cpd_model->table('cpd_month_bill')->where($where)->save($data);
        if($res){
            $log_type = $this->$type;
            $this->writelog("应用推广-CPD：修改了ID为{$id}的账单的{$type_arr[$type]}为{$log_type[$status]}",'cpd_month_bill',$id,__ACTION__ ,'','edit');
            $this->success('修改成功');
        }else{
            $this->error('修改失败');
        }
    }

    //修改备注
    function edit_beizhu(){
        if($_POST){
            $where = array('id'=>$_POST['id']);
            $old_info = $this->cpd_model->table('cpd_month_bill')->where($where)->find();
            $this->check_authority($old_info['contract_id']);
            $res = $this->cpd_model->table('cpd_month_bill')->where($where)->save(array('beizhu'=>$_POST['beizhu'],'update_tm'=>time()));
            if($res){
                $this->writelog("应用推广-CPD：修改了ID为{$id}的账单的备注为{$_POST['beizhu']}",'cpd_month_bill',$id,__ACTION__ ,'','edit');
                $this->success('修改成功');
            }else{
                $this->error('修改失败');
            }
        }
        $id = $_GET['id'];
        $beizhu = $this->cpd_model->table('cpd_month_bill')->where(array('id'=>$id))->find();
        $this->assign('beizhu',$beizhu['beizhu']);
        $this->assign('id',$id);
        $this->display($this->tmp_path.__FUNCTION__);
    }

    //判断当前系统是否有权限操作
    function check_authority($id,$type){
        $contract = $this->cpd_model->table('cpd_contract')->where(array('id'=>$id))->find();
        if($contract['flexible_sys']!=$this->flexible_sys||!empty($contract['flexible_c_code'])){
            if($type==1){
                exit(json_encode(array('code'=>0,'msg'=>'权限不足')));
            }else{
                $this->error('权限不足');
            }

        }
    }

    function pub_get_price(){
        $tm = $_POST['tm'];
        list($custom_id,$contract_id) = explode('_',$_POST['custom']);
        if(!$tm){
            exit(json_encode(array('code'=>0)));
        }else{
            if($this->flexible_sys==2) $tm = "{$tm}-01";
            $tm = strtotime($tm);
            $where = array('price_tm'=>array('exp'," <= '{$tm}'"),'contract_id'=>$contract_id);
            $price = $this->cpd_model->get_price($where);
            //echo $this->cpd_model->getLastSql();
            if($price){
                exit(json_encode(array('code'=>1,'price'=>$price['price_num'])));
            }else{
                exit(json_encode(array('code'=>0)));
            }
        }
    }

    function return_format(){
        if($this->flexible_sys==1){
            $format = "Y-m-d";
        }else{
            $format = "Ym";
        }
        return $format;
    }


    function import_task(){
        if ($_POST) {
            if($_POST['type']==1){
                //导出失败明细
                $this->out_fail(2);
            }
            $rs = $this->read_csv(2);
            if($rs['code']==0){
                echo json_encode($rs);
                exit(0);
            }

        }
        $this->display();
    }
    /******************************
     * 批量导入合同
     */
    function import_contract(){
        if ($_POST) {
            if($_POST['type']==1){
                //导出失败明细
                $this->out_fail(1);
            }
            $rs = $this->read_csv(1);
            if($rs['code']==0){
                echo json_encode($rs);
                exit(0);
            }

        }
        $this->display();
    }

    /*******************************
     * @param $type 1合同 2任务
     * 导出失败明细
     */
    function out_fail($type){
        $allist = json_decode($_POST['fail_soft'],true);
        $this->down_csv($allist,$type);
    }

    /*********************************************************
     * 导入合同，任务公共检测
     * @param $data
     * @param int $type
     * @return array
     */
    function check_pub_import($data,$type=1){
        $step1_arr = array();
        if($type==1){
            $key_arr = array('0'=>'客户ID','1'=>'协议主体序号','2'=>'合同产品（包名）','3'=>'开始时间','4'=>'结束时间');
            $start_key = 3;
            $end_key = 4;
        }else{
            $key_arr = array('0'=>'合同编号','1'=>'开始时间','2'=>'结束时间','3'=>'备注');
            $start_key = 1;
            $end_key = 2;
        }

        foreach($data as $k=>$v){
            foreach($v as $v_k=>$v_v){
                if(!$v_v){
                    if($type==2&&$v_k==3) break;
                    $msg = "{$key_arr[$v_k]}不能为空";
                    $v['error'][] = $msg;
                }else{
                    if($v_k==$start_key||$v_k==$end_key){
                        if(!strtotime($v_v)){
                            if($v_k==$start_key){
                                $str = '开始';
                            }else{
                                $str = '结束';
                            }
                            $msg = $str."时间格式不正确";
                            $v['error'][] = $msg;
                        }
                    }
                }
            }
            $data[$k] = $v;
            if($v['error']) continue;
            if($type==1){
                $step1_arr[$v[0]][$v[2]][$k] = $v;
            }else{
                $step1_arr[$v[0]][$k] = $v;
            }

        }

        if(count($step1_arr)>0){
            if($type==1){
                foreach($step1_arr as $custom_id=>$custom_info){
                    foreach($custom_info as $pack_k=>$pack_info){
                        $tmp_c = $pack_info;
                        foreach($pack_info as $key=>$pack_val){
                            if(strtotime($pack_val[$start_key])>strtotime($pack_val[$end_key])){
                                $data[$key]['error'][] = "开始时间不能大于结束时间";
                                continue;
                            }
                            foreach($tmp_c as $tmp_k=>$tmp_v){
                                if($key!=$tmp_k){
                                    $cross = is_time_cross(strtotime($pack_val[$start_key]), strtotime($pack_val[$end_key]), strtotime($tmp_v[$start_key]), strtotime($tmp_v[$end_key]));
                                    if($cross){
                                        $data[$key]['error'][] = "与第{$tmp_k}行时间交叉";
                                    }
                                }
                            }
                        }
                    }
                }
            }else{
                foreach($step1_arr as $custom_id=>$custom_info){
                    $tmp_c = $custom_info;
                    foreach($custom_info as $c_k=>$c_v){
                        if(strtotime($c_v[$start_key])>strtotime($c_v[$end_key])){
                            $data[$c_k]['error'][] = "开始时间不能大于结束时间";
                            continue;
                        }
                        foreach($tmp_c as $tmp_k=>$tmp_v){
                            if($c_k!=$tmp_k){
                                $cross = is_time_cross(strtotime($c_v[$start_key]), strtotime($c_v[$end_key]), strtotime($tmp_v[$start_key]), strtotime($tmp_v[$end_key]));
                                if($cross){
                                    $line = intval($tmp_k)+1;
                                    $data[$c_k]['error'][] = "与第{$line}行时间交叉";
                                }
                            }
                        }
                    }
                }
            }

        }
        return array($data,$step1_arr);
    }


    function save_import_task($data,$type){
        list($data,$step1_arr) = $this->check_pub_import($data,$type);
        //校验库数据
        $contract_codes = array();
        foreach($data as $k=>$v){
            $contract_codes[] = $v[0];
            $data[$k] = $v;
        }
        $contract_codes = array_filter(array_unique($contract_codes));
        //合同是否存在或进行中
        $now = time();
        $contract = $this->cpd_model->table('cpd_contract')->where(array('contract_code'=>array('in',$contract_codes),'status'=>1,'flexible_sys'=>$this->flexible_sys,'start_tm'=>array('exp'," <= {$now}"),'end_tm'=>array('exp'," >= {$now}")))->field('id,contract_code,start_tm,end_tm,softname,package,category_id,category_name,custom_id')->select();
//        echo $this->cpd_model->getLastSql();
        $has_contract = $has_contract_ids = array();
        if($contract){
            foreach($contract as $c_k=>$c_v){
                $has_contract[$c_v['contract_code']] = $c_v;
                $has_contract_ids[] = $c_v['id'];
            }
        }

//        foreach($data as $k=>$v){
//            if(isset($v['error'])) {
//                $failnum++;
//                continue;
//            }
//            if(!isset($has_contract[$v[0]])){
//                $v['error'][] = '不存在此合同编号或合同不在进行中';
//            }
//            if(isset($v['error'])) $failnum++;
//            $data[$k] = $v;
//        }
//        if($failnum>0) {
//            return array($failnum, $data);
//        }
        //预存
        $a_where = array('contract_id'=>array('in',$has_contract_ids));
        $prestore = $this->cpd_model->get_prestore($a_where,$this->flexible_sys);

        //任务
        $task = $this->cpd_model->table('cpd_task')->where(array('contract_id'=>array('in',$has_contract_ids),'flexible_sys'=>$this->flexible_sys))->field('id,contract_id,start_tm,end_tm,task_id')->select();

        //月账
        $month_bill = $this->cpd_model->table('cpd_month_bill')->where(array('contract_id'=>array('in',$has_contract_ids)))->select();
        if($month_bill){
            $c_month_bill = array();
            foreach($month_bill as $m_k=>$m_v){
                $c_month_bill[$m_v['contract_id']][] = $m_v;
            }
        }
        $error = 0;
        foreach($data as $d_k=>$d_v){
            if(!isset($has_contract[$d_v[0]])){
                $data[$d_k]['error'][] = '不存在此合同编号或合同不在进行中';
                $error++;
                continue;
            }
            $tmp_c_id = $has_contract[$d_v[0]]['id'];

            if(strtotime($d_v[1])<$has_contract[$d_v[0]]['start_tm']||strtotime($d_v[2])>$has_contract[$d_v[0]]['end_tm']){
                $start = date("Y-m-d",$has_contract[$d_v[0]]['start_tm']);
                $end =  date("Y-m-d",$has_contract[$d_v[0]]['end_tm']);
                $data[$d_k]['error'][] = "不能创建此任务，与合同时间不匹配，合同日期为（{$start}/{$end}）";
                $error++;
                continue;
            }
            if(!isset($prestore[$tmp_c_id])){
                $data[$d_k]['error'][] = "此合同未添加预存";
                $error++;
                continue;
            }
            $tm = strtotime($d_v[1]);
            $where = array('price_tm'=>array('exp'," <= '{$tm}'"),'contract_id'=>$tmp_c_id);
            $price = $this->cpd_model->get_price($where);

            if(!$price){
                $data[$d_k]['error'][] = "此任务未添加单价";
                $error++;
                continue;
            }else{
                $has_contract[$d_v[0]]['price'] = $price['price_num'];
            }

            if($task){
                $t_error = false;
                foreach($task as $t_k=>$t_v){
                    $cross = is_time_cross(strtotime($d_v[1]), strtotime($d_v[2]), $t_v['start_tm'], $t_v['end_tm']);
                    if($cross){
                        $data[$d_k]['error'][] = "不能创建此任务，与任务ID为{$t_v['task_id']}时间冲突";
                        $t_error = true;
                    }
                }
                if($t_error){
                    $error++;
                    continue;
                }

                if($c_month_bill[$tmp_c_id]){
                    $start = date('Ym',strtotime($d_v[1]));
                    $end = date('Ym',strtotime($d_v[2]));
                    $bo = false;
                    foreach($c_month_bill[$tmp_c_id] as $c_k=>$c_v){
                        if($start<=$c_v['bill_month']){
                            //有状态已确认就不让建任务
                            if($c_v['receipt_status']==2||$c_v['expend_status']==2||$c_v['bill_status']==2){
                                $bo = true;
                                break;
                            }
                            if($c_v['bill_month']<= $end){
                                $data[$d_k]['bill_id'][] = $v['id'];
                            }
                        }
                    }
                    if($bo){
                        $error++;
                        $data[$d_k]['error'][] = '此合同任务日期已有生成确认的月账单';
                        continue;
                    }
                }
            }
            if($data[$d_k]['error']) $error++;
        }

        if($error>0){
            return array($error, $data);
        }
        foreach($data as $k=>$v){
            $s_data = array(
                'package'=>$has_contract[$v[0]]['package'],
                'softname' =>$has_contract[$v[0]]['softname'],
                'category_id'=>$has_contract[$v[0]]['category_id'],
                'category_name'=>$has_contract[$v[0]]['category_name'],
                'start_tm' => strtotime($v[1]),
                'end_tm' => strtotime($v[2]),
                'price'=>$has_contract[$v[0]]['price'],
                'remark' => $v[3],
                'contract_id' => $has_contract[$v[0]]['id'],
                'custom_id' => $has_contract[$v[0]]['custom_id']
            );
            $res = $this->cpd_model->save_task($s_data,$this->flexible_sys);
            if($res){
                if(isset($v['bill_id'])){
                    $this->cpd_model->table('cpd_month_bill')->where(array('id',array('in',$v['bill_id'])))->save(array('status'=>2,'update_tm'=>time()));
                }
                $this->Client_db->setInc('cpd_task_num',"id = {$has_contract[$v[0]]['custom_id']}",1);
                $this->writelog("CPD广告：添加了任务编号为{$res}的任务",'cpd_task',$res,__ACTION__ ,'','add');
            }

        }
    }


    /*****************************
     * 批量导入合同数据
     * @param $data
     * @return array
     */
    function save_import_contract($data,$type){
        $failnum = 0;
        list($data,$step1_arr) = $this->check_pub_import($data,$type);
        //校验库数据
        $custom_ids = array();
        $package = array();
        $c_config = array();
        foreach($data as $k=>$v){
            $custom_ids[] = $v[0];
            $c_config[] = $v[1];
            $package[] = $v[2];
        }
        $custom_ids = array_filter(array_unique($custom_ids));
        $package = array_filter(array_unique($package));

        //客户
        $custom = $this->Client_db->where(array('id'=>array('in',$custom_ids),'status'=>1))->field('id,client_name')->select();
//        echo $Client_db->getLastSql();
        $has_custom_ids = array();
        if($custom){
            foreach($custom as $c_k=>$c_v){
                $has_custom_ids[$c_v['id']] = $c_v['client_name'];
            }
        }
        //包名
        $online_pack = $this->cpd_model->table('sj_soft')->where(array('hide'=>1,'status'=>1,'package'=>array('in',$package)))->field('package,softname,category_id')->select();
        $category_ids = array();
        if($online_pack){
            foreach($online_pack as $o_k=>$o_v){
                $tmp_cate =  substr($o_v['category_id'],1,-1);
                $has_pack[$o_v['package']] = array(
                    'softname' =>$o_v['softname'],
                    'package' =>$o_v['package'],
                    'category_id' => $tmp_cate
                );
                $category_ids[] = $tmp_cate;
            }
        }
        //协议主体
        $c_configs = $this->cpd_model->table('cpd_contract_config')->where(array('status'=>1,'id'=>array('in',$c_config)))->field('id')->select();
        if($c_configs){
            foreach($c_configs as $c_k=>$c_v){
                $has_config[$c_v['id']] = 1;
            }
        }
        foreach($data as $k=>$v){
            if(isset($v['error'])) {
                $failnum++;
                continue;
            }
            if(!isset($has_custom_ids[$v[0]])){
                $v['error'][] = '不存在此客户ID';
            }
            if(!isset($has_pack[$v[2]])){
                $v['error'][] = '包名不存在';
            }
            if(!isset($has_config[$v[1]])){
                $v['error'][] = '协议主体不存在';
            }
            if(isset($v['error'])) $failnum++;
            $data[$k] = $v;
        }

        if($failnum>0){
            return array($failnum,$data);
        }else{
            //与库中检查时间是否交叉
            $custom_contract = $this->cpd_model->table('cpd_contract')->where(array('custom_id'=>array('in',$custom_ids),'status'=>array('exp',' != 0 '),'flexible_sys'=>$this->flexible_sys))->field('id,custom_id,package,start_tm,end_tm,contract_code')->select();
//            echo $this->cpd_model->getLastSql();
            //var_dump($custom_contract);
            if($custom_contract){
//                var_dump($custom_contract);
                $need_check = array();
                foreach($custom_contract as $c_k=>$c_v){
                    $need_check[$c_v['custom_id']][$c_v['package']][] = $c_v;
                }
//                var_dump($need_check);

                foreach($step1_arr as $c_id => $c_info) {
                    foreach ($c_info as $pack => $pack_info) {
                        foreach ($pack_info as $p_k => $p_v) {
                            foreach($need_check[$p_v[0]][$p_v[2]] as $ne_k=>$ne_v){
                                $cross = is_time_cross(strtotime($p_v[3]), strtotime($p_v[4]), $ne_v['start_tm'], $ne_v['end_tm']);
                                if($cross){
                                    $data[$p_k]['error'][] = "与合同编号为{$ne_v['contract_code']}时间交叉";
                                    $failnum++;
                                    break;
                                }

                                $time_s1 = strtotime(date("Y-m",strtotime($p_v[3])).'-01');
                                $month = date("Y-m",strtotime($p_v[4]));
                                $time_e1 = strtotime("{$month}-01 + 1month  - 1day");
                                $cross = is_time_cross($time_s1, $time_e1, $ne_v['start_tm'], $ne_v['end_tm']);
                                if($cross){
                                    $data[$p_k]['error'][] = "";
                                    $failnum++;
                                    break;
                                }
                            }
                        }
                    }
                }
                if($failnum>0){
                    return array($failnum,$data);
                }
            }

            $category_name = $this->cpd_model->get_category_name($category_ids);
            $error = 0;
            foreach($step1_arr as $c_id => $c_info){
                foreach($c_info as $pack=>$pack_info){
                    foreach($pack_info as $p_k=>$p_v){
                        $i_data['package'] = $p_v[2];
                        $i_data['softname'] = $has_pack[$p_v[2]]['softname'];
                        $i_data['category_id'] = $has_pack[$p_v[2]]['category_id'];
                        $i_data['category_name'] = $category_name[$has_pack[$p_v[2]]['category_id']];
                        $i_data['client_name']=$has_custom_ids[$p_v[0]];
                        $i_data['flexible_sys'] = $this->flexible_sys;
                        $i_data['start_tm'] = strtotime($p_v[3]);
                        $i_data['end_tm'] = strtotime($p_v[4]);
                        $i_data['custom_id'] = $p_v[0];
                        $i_data['contact_config_id'] = $p_v[1];
                        $res = $this->cpd_model->add_contract($i_data);
                        if($res){
                            $this->writelog("CPD广告：添加了合同编号为{$res}的合同",'cpd_contract',$res,__ACTION__ ,'','add');
                            //合同数量加1
                            $this->Client_db->setInc('cpd_contract_num',"id = {$i_data['custom_id']}",1);
                        }else{
                            $data[$p_k]['error'] = '添加失败';
                            $error++;
                        }
                    }
                }
            }
            return array($error,$data);
        }
    }


    /***********************************
     * 读取excel数据
     * @param $file
     * @return bool
     */
    function read_csv($type) {
        $file = $_FILES['upload']['tmp_name'];
        $tmp_houzhui = $_FILES['upload']['name'];
        $tmp_arr = explode('.', $tmp_houzhui);
        $houzhui = array_pop($tmp_arr);

        if (strtoupper($houzhui) != 'CSV') {
            $this->ajaxReturn(array('code'=>0,'msg'=>'请上传格式为csv的文件'), '', 1);
        }

        $arr = array();
        $handel = fopen($file, "r");
        $i = 0;

        while (($num_arr = $this->mygetcsv($handel, 1000, ",")) !== FALSE) {
            //标题行不写入
            if ($i != 0) {
                foreach($num_arr as $k=>$v){
                    $v = $this->convert_encoding($v);
                    $num_arr[$k] = $v;
                }
                $arr[$i] =  $num_arr;
            }
            $i++;
        }
        if(count($arr)>200)
            $this->ajaxReturn(array('code'=>0,'msg'=>'最大导入200条'), '', 1);

        if($type==1){
            list($fail_num,$r_data) = $this->save_import_contract($arr,$type);
        }else{
            list($fail_num,$r_data) = $this->save_import_task($arr,$type);
        }

        fclose($handel);
        //var_dump($r_data);
        if($fail_num>0){
            $this->ajaxReturn(array($fail_num,json_encode($r_data)), '导入失败！', 1);
        }else{
            $this->ajaxReturn(array('code'=>2,'msg'=>'添加成功'), '', 1);
        }
    }



    function down_csv($data,$type){
        header("Content-type:application/vnd.ms-excel");
        header("content-Disposition:filename=new.csv");
        $handel = fopen("new.csv", "r");
        fwrite($handel,chr(0xEF).chr(0xBB).chr(0xBF));
        if($type==1){
            $desc = "客户ID,协议主体序号,合同产品（包名）,开始时间,结束时间,错误提示\r\n";
        }else{
            $desc = "合同编号,开始时间,结束时间,备注,错误提示\r\n";
        }
        foreach ($data as $v) {
            if(!isset($v['error'])){
                $error_str = '正确';
            }else{
                $error_str = implode(';',$v['error']);
            }

            if($type==1){
                $desc = "{$desc}{$v[0]},{$v[1]},{$v[2]},{$v[3]},{$v[4]},{$error_str}\r";
            }else{
                $desc = "{$desc}{$v[0]},{$v[1]},{$v[2]},{$v[3]},{$error_str}\r";
            }

        }
        $desc = iconv('utf-8', 'gbk', $desc);
        echo $desc;
        exit(0);
    }
}