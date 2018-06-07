<?php
class CpdExtendModel extends Model {
    protected $tableName = 'contract';
    protected $tablePrefix = 'cpd_';

    function get_soft_info($where,$field="*"){
        $soft_info = $this->table('sj_soft ')->where($where)->field($field)->order('softid desc')->find();
        //echo $this->getLastSql();
        return $soft_info;
    }

    function get_category_name($category){
        if(is_array($category)){
            $r_arr = array();
            $category = $this->table('sj_category')->where(array('category_id'=>array('in',$category)))->field('category_id,name')->select();
            foreach($category as $k=>$v){
                $r_arr[$v['category_id']] = $v['name'];
            }
            return $r_arr;
        }else{
            $category_id = substr($category,1,-1);
            $category = $this->table('sj_category')->where("category_id = {$category_id}")->field('category_id,name')->find();
            return $category;
        }

    }

    //更改合同
    function update_contract($data,$contract){
        $u_data = $data;
        unset($u_data['id']);
        $u_data['update_tm'] = time();
        $u_data['reviewer'] = $_SESSION['admin']['admin_id'];
        $contract = $this->where(" id = {$data['id']} or flexible_c_code = '{$contract['contract_code']}'")->save($u_data);
        return $contract;
    }
    //添加合同
    function add_contract($data){
        unset($data["__hash__"]);
        unset($data['custom_name']);
        $data['add_tm'] = $data['update_tm'] = time();
        $data['reviewer'] = $_SESSION['admin']['admin_id'];
        //SW_CPD_日期（YYYYMMDD）_0001
        $res = $this->add($data);
        $code_len = strlen($res);
        $code = '';
        if($code_len<4){
            for($i=0;$i<4-$code_len;$i++){
                $code .= '0';
            }

        }
       $l_code =  'SW_CPD_'.date(Ymd).'_'.$code.$res;
       // var_dump($l_code);
        $contract = $this->where(" id = {$res}")->save(array("contract_code" => $l_code));
        if($contract){
            if($data['flexible_sys']==1){
                $data['flexible_c_code'] = $l_code;
                $data['contract_code'] = $l_code;
                $this->add_flexible_contract($data);
            }
            $this->add_c_download($res,$data) ;
            return $l_code;
        }else{
            return false;
        }

        //echo $this->getLastSql();
    }

    //在A系统添加合同时产生一条对应的B系统合同
    function add_flexible_contract($data){
        $data['flexible_sys'] = 2;
        $res = $this->add($data);
//        $code_len = strlen($res);
//        $code = '';
//        if($code_len<4){
//            for($i=0;$i<4-$code_len;$i++){
//                $code .= '0';
//            }
//        }
//        $l_code =  'SW_CPD_'.date(Ymd).'_'.$code.$res;
//        $this->where(" id = {$res}")->save(array("contract_code" => $l_code));
    }

    //添加默认下载量配置
    function add_c_download($contract_id,$data){
        $day = $data['start_tm'];
        $has_down_config = $this->table('cpd_download_config')->where("contract_id = {$contract_id} and op_tm = {$day} and status  = 1")->find();
        $configname=($data['flexible_sys']==1)?'cpd_different_site_down_default_num':'cpd_different_site_down_default_num_m';
        if(!$has_down_config){
            $down_default = $this->table('pu_config')->where("configname = '{$configname}'")->order('CAST(config_type AS SIGNED) asc')->select();
            $add_data = array(
                'www_downloaded' => $down_default[0]['configcontent'],
                'm_downloaded' => $down_default[1]['configcontent'],
                'coop_downloaded' => $down_default[2]['configcontent'],
                'other_downloaded' => $down_default[3]['configcontent'],
                'finger_play_downloaded' => $down_default[4]['configcontent'],
                'market_downloaded' => $down_default[5]['configcontent'],
                // 'business_downloaded' => $down_default[6]['configcontent'],
                'yoo_downloaded' => $down_default[6]['configcontent'],
                'mm_downloaded' => $down_default[7]['configcontent'],
                //'channel_downloaded' => $down_default[8]['configcontent'],
                //'da_download' => $down_default[9]['configcontent'],
                //'contract_id' => $contract_id,
                'op_tm' => $day,
                'add_tm' => time(),
                'update_tm' => time(),
                'reviewer' => $_SESSION['admin']['admin_id'],
                'flexible_sys' => $data['flexible_sys'],
                'softname' => $data['softname'],
                'package' => $data['package']
            );
            $this->table('cpd_download_config')->add($add_data);
            //echo $this->table('cpd_download_config')->getLastSql();
            $msg = $this->getLastSql();
            $this->write_cpd_log($msg);
        }
    }
    function process_custom($custom_name){
        $Client_db = D("Settlement.Client");
        $clients = $Client_db -> where("client_name = '{$custom_name}' and status=1") ->find();
        if($clients){
            return array($clients['id'],0);
        }else{
            //新增客户
            $insert_data = array(
                'admin_id'=>$_SESSION['admin']['admin_id'],
                'admin_name'=>$_SESSION['admin']['admin_user_name'],
                'client_name'=>$custom_name,
                'create_tm'=>time()
            );
            $res = $Client_db->add($insert_data);
            return array($res,1);
        }
    }

    //CPD二期本月2号出上月数据
    function month_day(){
        if(time()>=strtotime(date("Y-m-02"))){
            $month = date("Y-m",strtotime("-1month"));
        }else{
            $month = date("Y-m",strtotime("-2month"));
        }
        $day = strtotime("{$month}-01 + 1month  - 1day");
        return $day;
    }

    /***********************************
     * 获取预存合计值
     * @ where array  合同ID
     ***********************************/

    function get_prestore($where,$flexible_sys){
        $where['status'] = 1;
//        if($flexible_sys == 2){
//            //CPD二期本月2号出上月数据
//            $day = $this->month_day();
//            $where['prestores_tm'] = array('exp'," <= '{$day}'");
//        }
        $prestore = $this->table('cpd_prestore')->where($where)->field('contract_id,sum(recharge_sum) as recharge,sum(delivery_sum) as delivery')->group('contract_id')->select();
// process_custom
        $res = array();
        foreach($prestore as $key=>$val){
            $res[$val['contract_id']]  = array(
                'recharge' => $val['recharge'],
                'delivery' => $val['delivery']
            );
        }
        return $res;

    }

    /*******************************************
     * 获取剩余充值金额和剩余配送金额
     * @ where array() 合同id
     * @ id 任务id
     * return array()
     * ******************************************/
    function get_surplus($where,$id='',$flexible_sys){
        $prestore = $this->get_prestore($where,$flexible_sys);
        $task = $this->get_task($where,$id,$flexible_sys);
//        var_dump($task);
        $surplus = array();
        if($prestore){
            foreach($prestore as $key=>$val){
                $surplus[$key]['all_recharge'] = $val['recharge'];
                $surplus[$key]['all_delivery'] = $val['delivery'];
                $surplus[$key]['recharge'] = $val['recharge']-$task[3][$key];
                $surplus[$key]['delivery'] =$val['delivery']-$task[4][$key];
            }
        }
        return array($surplus,$task);
    }

    //获取收款
    function get_receipts($where,$flexible_sys){
        $where['status'] = 1;
        if($flexible_sys == 2){
            //CPD二期本月2号出上月数据
            $day = $this->month_day();
            $where['receipts_sum'] = array('exp'," <= '{$day}'");
        }
        //$receipts = $this->table('cpd_receipts')->where($where)->field('id,receipts_sum,contract_id')->select();
        //CPD 二期更改为从预存表读取收款
        $receipts = $this->table('cpd_prestore')->where($where)->field('id,receipts_sum,contract_id')->select();
        //var_dump($receipts);
        $total_receipts = array();
        foreach($receipts as $k=>$v){
            if(!isset($total_receipts[$v['contract_id']])) $total_receipts[$v['contract_id']] = 0;
            $total_receipts[$v['contract_id']] += $v['receipts_sum'];
        }
        //var_dump($total_receipts);
        return $total_receipts;
    }

    /************************************
     * 获取任务
     * $count(获取任务总消耗)
     *  2016/11/29 新版新增 return array（充值总消耗，配送总消耗）
     */
    function get_task($where,$id,$flexible_sys){
        $time = strtotime(date("Y-m-d"));
        $where['status'] = 1;
        //$where['flexible_sys'] = $flexible_sys;
        if(isset($id)&&!empty($id)){
            $where['id'] = array('neq',$id);
        }
        if($flexible_sys == 2){

            //CPD二期本月2号出上月数据
            $day = $this->month_day();
            $where['start_tm'] = array('exp'," <= '{$day}'");
            //$contract = $this->table('cpd_contract')->where(array('id'=>$where['contract_id']))->field('id,flexible_sys')->select();
            //$contract = get_table_data(array('id'=>$where['contract_id'],'status'=>1),'cpd_contract','id','id,flexible_sys');
        }
        $task = $this->table('cpd_task')->where($where)->field('id,contract_id,all_budgets,t_total_download,t_total_count,end_tm,download_recharge_sum,download_delivery_sum,price,start_tm,flexible_sys,acture_sys')->select();
        $total_download = $total_count = $count = $recharge = $delivery = array();
        foreach($task as $k=>$v){
            //A系统的消耗在B系统中需实时获取
//            var_dump($contract[$v['contract_id']['flexible_sys']]);
            //var_dump($v['flexible_sys'].'--'.$v['acture_sys']);
            if($flexible_sys == 2&&$flexible_sys !=$v['acture_sys']){
                if (isset($total_download[$v['contract_id']])) continue;
                $flexible_where = array(
                    'contract_id' => $v['contract_id'],
                    'start_tm' => $v['start_tm']
                );
                $flexible_where['end_tm'] = $day;
                $this->get_flexible_task_data($flexible_where,$v);
            }

            if (!isset($total_download[$v['contract_id']])) $total_download[$v['contract_id']] = 0;
            $total_download[$v['contract_id']] += $v['t_total_download'];
            if (!isset($total_count[$v['contract_id']])) $total_count[$v['contract_id']] = 0;
            $total_count[$v['contract_id']] += $v['t_total_count'];
            if (!isset($count[$v['contract_id']])) $count[$v['contract_id']] = 0;
            //充值消耗(已结束的充值消耗和未结算的充值预算)
            if (!isset($recharge[$v['contract_id']])) $recharge[$v['contract_id']] = 0;
            //配送消耗(已结束的配送消耗和未结算的配送预算)
            if (!isset($delivery[$v['contract_id']])) $delivery[$v['contract_id']] = 0;
            //已结束任务算全部消耗，未结束算总预算(新规则所有任务都算消耗)
//            if($v['end_tm']<$time||$count_expend == 1){
                $count[$v['contract_id']] += $v['t_total_count'];
                $recharge[$v['contract_id']] += $v['download_recharge_sum'];
                $delivery[$v['contract_id']] += $v['download_delivery_sum'];
//            }else{
//                $count[$v['contract_id']] += $v['all_budgets'];
//                $recharge[$v['contract_id']] += $v['recharge_budgets'];
//                $delivery[$v['contract_id']] += $v['delivery_budgets'];
//            }
        }
        return array($total_download,$total_count,$count,$recharge,$delivery);
    }

    //获取发票
    function get_invoice($where,$flexible_sys){
        $where['status'] = 1;
        if($flexible_sys == 2){
            //CPD二期本月2号出上月数据
            $day = $this->month_day();
            $where['invoice_tm'] = array('exp'," <= '{$day}'");
        }
        //$invoice =  $this->table('cpd_invoice')->where($where)->field('contract_id,sum(invoice_sum) as invoice_sum')->group('contract_id')->select();
        //CPD二期发票更改为从预存表获取
        $invoice =  $this->table('cpd_prestore')->where($where)->field('contract_id,sum(invoice_sum) as invoice_sum')->group('contract_id')->select();
        $res = array();
        foreach($invoice as $k=>$v){
            $res[$v['contract_id']] = $v;
        }
        return $res;
    }

    //获取自然量
    function get_nature($where,$field){
        //var_dump($where);
        $where['status'] = 1;
        //$nature = get_table_data($where,'cpd_nature','contract_id',$field);
        $nature = array();
        $res = $this->table('cpd_nature')->where($where)->field($field)->order('nature_tm desc')->select();

        foreach($res as $k=>$v){
            $nature[$v['contract_id']]['contract_id'] = $v['contract_id'];
            if(!isset($nature[$v['contract_id']]['nature_num'])){
                if($v['nature_tm']<=time()){
                    $nature[$v['contract_id']]['nature_num'] = $v['nature_num'];
                }
            }

        }
        return $nature;
    }

    //获取分站点计费限额
    function get_download_config($where,$field){
        $where['status'] = 1;
        $download_config = get_table_data($where,'cpd_download_config','contract_id',$field);

        foreach($download_config as $k=>$v){
            $v['total'] = $v['www_downloaded']+$v['m_downloaded']+$v['coop_downloaded']+$v['other_downloaded'];
            $download_config[$k] = $v;
        }
        //var_dump($download_config);
        return $download_config;
    }

    //任务添加编辑
    function save_task($data,$flexible_sys){
        $data['reviewer'] = $_SESSION['admin']['admin_id'];

        if(!empty($data['id'])){
            //编辑
            $data['update_tm'] = time();
            $res = $this->table('cpd_task')->save($data);
            return $res;
        }else{
            //添加
            unset($data['id']);
            $data['flexible_sys'] = $data['acture_sys'] = $flexible_sys;
            list($contract,$l_code) = $this->add_task($data);
            if($contract){
                $this->save_flexible_task($data,$flexible_sys);
                return $l_code;
            }else{
                return false;
            }
        }
    }

    //添加任务
    function add_task($data){
        $data['add_tm'] = $data['update_tm'] = time();
        $today = strtotime(date("Y-m-d"));
        if($data['start_tm']<$today) $data['task_type'] = 1;
        $res = $this->table('cpd_task')->add($data);
//            echo $this->getLastSql();
        $code_len = count($res);
        $code = '';
        if($code_len<4){
            for($i=0;$i<4-$code_len;$i++){
                $code .= '0';
            }
        }
        $l_code =  'CPD_RW_'.date(Ymd).'_'.$code.$res;
        $contract = $this->table('cpd_task')->where(" id = {$res}")->save(array("task_id" => $l_code));
        return array($contract,$l_code);
    }


    //系统A的任务合并到系统B
    function save_flexible_task($data,$flexible_sys){
        $contract = $this->table("cpd_contract")->where(array('id'=>$data['contract_id'],'status'=>array('exp',' != 0')))->field('contract_code')->find();
        $d_contract = $this->table("cpd_contract")->where(array('flexible_c_code'=>$contract['contract_code'],'status'=>array('exp',' != 0')))->field('id')->find();
        $data['contract_id'] = $d_contract['id'];
        if($flexible_sys == 1){
            //算出时间段内包含的月份
            $start_year = date('Y',$data['start_tm']);
            $start_month = date('m',$data['start_tm']);
            $month = array();
            while(true){
                if($data['end_tm'] >=strtotime($start_year.$start_month.'01')){
                    $month[] = $start_year.$start_month;
                    if($start_month>=12){
                        $start_month = '01';
                        $start_year = intval($start_year) + 1;
                    }else{
                        $start_month = intval($start_month) + 1;
                        if($start_month<10) $start_month = '0'.$start_month;
                    }
                }else{
                    break;
                }
            }
            $len = count($month);
            if($len>0){
                $s_month = $month[0];
                $data['flexible_sys'] = 2;
                $data['acture_sys'] = 1;
                $i_month = array();
                $has_id = '';
                for($i=0;$i<$len;$i++){
                    $start_tm = strtotime("{$month[$i]}01");
                    $end_tm = strtotime("{$month[$i]}01 + 1month  - 1day");
                    $where = array(
                        'start_tm' => array('exp'," <= {$start_tm}"),
                        'end_tm'  => array('exp'," >= {$end_tm}"),
                        'flexible_sys' => 2,
                        'contract_id' => $data['contract_id'],
                        'status' => 1
                    );
                    $has_task = $this->table('cpd_task')->where($where)->find();
                    $data['start_tm'] = strtotime($s_month."01");

                    if($has_task){
                        $has_id = empty($has_id)?$has_task['id']:$has_id;
                        $i_month[] = $month[$i];
                        $data['end_tm'] = strtotime("{$month[$i-1]}01 + 1month  - 1day");
                        if(!isset($i_month[$month[$i]])&&$s_month!=$month[$i]&&$has_id==$has_task['id']){
                            $this->add_task($data);
                        }
                        $s_month = $month[$i+1];
                    }else{
                        $data['end_tm'] = strtotime("{$month[$i]}01 + 1month  - 1day");
                        if($has_id){
                            $this->table('cpd_task')->where(array('id'=>$has_id))->save(array('end_tm'=>$data['end_tm']));
//                            echo $this->table('cpd_task')->getLastSql();
                        }else{
                            if($i==$len-1){
                                $this->add_task($data);
                            }
                        }
                    }

                }
            }
        }
    }

    function get_extend_by_day($p_pack,$day,&$list){
        //var_dump($list);
        $where = array(
            'status'=>1,
            'package'=> array('in',$p_pack),
            'expend_tm' => array('in',$day)
        );
       // echo '<hr>';
        $extend = $this->table('cpd_expend')->where($where)->field('id,package,expend_tm,download_invalid')->select();
        //echo $this->getLastSql();
        //var_dump($extend);
        if($extend){
            foreach($list as $k=>$v){
                foreach($v as $m_k=>$m_v){
                    foreach($extend as $e_k=>$e_v){
                        if($k==$e_v['package']&&$m_k==$e_v['expend_tm']){
                            $m_v['download_invalid']  = $e_v['download_invalid'];
                        }
                    }
                    $v[$m_k] = $m_v;
                }
                $list[$k] = $v;
            }
        }
        //echo '<hr>';
       // var_dump($list);

//        foreach($list as $k=>$v) {
//            $where = array('status'=>1);
//            $expend_tm = array();
//            $where['package'] = $k;
//            foreach($v as $m_k=>$m_v){
//                $expend_tm[] = $m_k;
//
//            }
//            $where['expend_tm'] = array('in',$expend_tm);
//            //var_dump($where);
//            $extend = $this->table('cpd_expend')->where($where)->field('id,expend_tm,download_invalid')->select();
//            //echo $this->getLastSql();
//           // var_dump($extend);
//            if($extend){
//                foreach($extend as $e_k=>$e_v){
//                    foreach($v as $l_k=>$l_v){
//                        if($e_v['expend_tm']==$l_k){
//                            $l_v['download_invalid']  = $e_v['download_invalid'];
//                        }
//                        $v[$l_k] = $l_v;
//                    }
//                }
//            }
//            $list[$k] = $v;
//        }
        return $list;
    }

    function get_nature_by_day(&$list){
        foreach($list as $k=>$v){
            $where = $contract_id =  array('status'=>1);
           // $contract_id =  array();
            foreach($v as $i_k=>$i_v){
                //$contract_id[] = $i_v['contract_id'];
                $where['contract_id'] = $i_v['contract_id'];
                $where['nature_tm'] = array('elt',$i_k); //<=
                $nature = $this->table('cpd_nature')->where($where)->order('nature_tm desc')->field('nature_num')->find();
                //echo $this->getLastSql();
                //var_dump($nature);
                $i_v['nature'] = $nature['nature_num'];
                $v[$i_k] = $i_v;
            }
           // $contract_id =  array_unique($contract_id);
            //var_dump($contract_id);
            $list[$k] = $v;
        }
        return $list;
    }

    //获取下载量限额
    function get_download_config_by_day(&$list){
        foreach($list as $k=>$v){
            $where = $contract_id =  array('status'=>1);
            // $contract_id =  array();
            foreach($v as $i_k=>$i_v){
                //$contract_id[] = $i_v['contract_id'];
                $where['contract_id'] = $i_v['contract_id'];
                $where['op_tm'] = array('elt',$i_k); //<=
                $config = $this->table('cpd_download_config')->where($where)->order('op_tm desc')->field('www_downloaded,m_downloaded,coop_downloaded,other_downloaded,finger_play_downloaded')->find();
//                echo $this->getLastSql();
//                var_dump($config);
                $i_v['c_www_downloaded'] = $config['www_downloaded'];
                $i_v['c_m_downloaded'] = $config['m_downloaded'];
                $i_v['c_coop_downloaded'] = $config['coop_downloaded'];
                $i_v['c_other_downloaded'] = $config['other_downloaded'];
                $i_v['c_finger_play_downloaded'] = $config['finger_play_downloaded'];
                $v[$i_k] = $i_v;
            }
            // $contract_id =  array_unique($contract_id);
            //var_dump($contract_id);
            $list[$k] = $v;
        }
        return $list;
    }

    //修改无效下载是修改对应开发者消耗
    function edit_invalid_to_dev($data){
        if($data['day']<strtotime(date("Ymd"))){
            //修改历史数据时才修改
            $where = array(
                'package' => $data['package'],
                'contract_id' => $data['contract_id'],
                'count_tm' => $data['day'],
                'status' => 1
            );
            $find = $this->table('cpd_for_dev')->where($where)->order('id desc')->field('id,download,last_invalid')->find();

            if($find){
                $new_download = $find['download'] + $find['last_invalid'] - $data['invalid'];
                $save_data = array(
                    'download' => $new_download,
                    'last_invalid' => $data['invalid'],
                    'update_tm' => time()
                );
                $res = $this->table('cpd_for_dev')->where("id = {$find['id']}")->save($save_data);

                if($res){
                $msg = "（cpd_for_dev）管理员{$_SESSION['admin']['admin_id']}修改了包名为{$data['package']},合同id为{$data['contract_id']},消耗日为{$data['day']}的无效下载为{$data['invalid']},导致开发者消耗id为{$find['id']}的下载量由[{$find['download']}]变为[{$new_download}]，[last_invalid]由[{$find['last_invalid']}]变为[{$data['invalid']}]";
            }else{
                    $msg = $this->getLastSql();
                }
                $this->write_cpd_log($msg);
                return $res;
            }
        }
    }
    //修改无效下载时修改对应任务消耗详情
    function edit_invalid_to_task($data){
        $where = array(
            'package' => $data['package'],
            'contract_id' => $data['contract_id'],
            'start_tm' => array('elt',$data['day']),
            'end_tm' => array('egt',$data['day']),
            'status' => 1
        );
        $find = $this->table('cpd_task')->where($where)->order('id desc')->field('id,last_invalid,t_total_download,t_total_count,last_invalid,price')->find();
        if($find){
            $new_t_total_download = $find['t_total_download'] + $find['last_invalid'] - $data['invalid'];
            $new_t_total_count = $new_t_total_download*$find['price'];
            $save_data = array(
                't_total_download' => $new_t_total_download,
                't_total_count' => $new_t_total_count,
                'last_invalid' => $data['invalid'],
                'update_tm' => time()
            );
            $res = $this->table('cpd_task')->where("id = {$find['id']}")->save($save_data);
            if($res){
                $msg = "（cpd_task）管理员{$_SESSION['admin']['admin_id']}修改了包名为{$data['package']},合同id为{$data['contract_id']},消耗日为{$data['day']}的无效下载为{$data['invalid']},导致任务id为{$find['id']}的消耗下载量由[{$find['t_total_download']}]变为[{$new_t_total_download}],消耗金额由[{$find['t_total_count']}]变为[{$new_t_total_count}],[last_invalid]由[{$find['last_invalid']}]变为[{$data['invalid']}]";
            }else{
                $msg = $this->getLastSql();
            }
            $this->write_cpd_log($msg);
            return $res;
        }else{
            return false;
        }
    }

    function write_cpd_log($msg){
        $filename = 'cpd.log';
        $now = time();
        $path = "/data/att/permanent_log/admin_data_log/".date("Y-m-d", $now);
        if(!file_exists($path)){
            mkdir($path, 0755, true);
        }
        $path_log = $path."/".$filename;
        $msg = date('Y-m-d H:i:s', $now). " {$msg}\n";
        file_put_contents($path_log, $msg, FILE_APPEND);
    }

    //CPD二期获取任务有效单价
    function get_price($where){
        $where['status'] = 1;
        $price = $this->table('cpd_unit_price')->where($where)->order('price_tm desc')->field('id,contract_id,price_num')->find();
        //echo $this->getLastSql();
        return $price;
    }
    
    //CPD二期获取A系统任务在B系统任务统计数据
    function get_flexible_task_data($where,&$v){
        $expend_where = array(
            'contract_id' => $where['contract_id'],
            'expend_tm' => array('exp'," >= '{$where['start_tm']}' and expend_tm <= '{$where['end_tm']}'"),
            'status' => 1
        );
        $expend = $this->table('cpd_expend')->where($expend_where)->select();
        $v['t_total_download'] = 0;
        $v['t_total_count'] = 0;
        $v['download_recharge_sum'] = 0;
        $v['download_delivery_sum'] = 0;
        if($expend){
            foreach($expend as $e_k=>$e_v){
                $v['t_total_download'] += $e_v['download_count'] -$e_v['download_invalid'] - $e_v['nature_num'];
                $v['t_total_count'] += $e_v['download_recharge'] + $e_v['download_delivery'];
                $v['download_recharge_sum'] += $e_v['download_recharge'];
                $v['download_delivery_sum'] += $e_v['download_delivery'];
            }
        }
    }
}