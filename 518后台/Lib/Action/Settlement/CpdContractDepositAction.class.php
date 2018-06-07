<?php

/**
 * 安智网产品管理平台 CPD流量结算控制器
 * ============================================================================
 * 版权所有 2009-2014 北京力天无限网络有限公司，并保留所有权利。
 * 网站地址: http://www.goapk.com；
 * author：yuesai
 * 结算详情
 * ----------------------------------------------------------------------------
 */
class CpdContractDepositAction extends CommonAction {
    public $cpd_model;
    public $flexible_sys;
	public $tmp_path;
    function __construct()
    {
        parent::__construct();
        //主库
        $this->cpd_model = D('Settlement.CpdExtend');
        $this->tmp_path = 'Settlement/CpdContractDeposit:';
		$this->flexible_sys = 1;
    }
    // 结算详情--预存
    public function prestores_list() {
        $map=array();
        $map=$this->sync_search($map,'prestores_tm');
        $model = $this->cpd_model;

        import("@.ORG.Page");
        $count = $model->table('cpd_prestore a')->join('cpd_contract b on a.contract_id=b.id')->join('cpd_contract_config c on b.contact_config_id=c.id')->where($map)->order('a.add_tm desc')->count();
        $Page = new Page($count, 50);
        $lists = $model->table('cpd_prestore a')->join('cpd_contract b on a.contract_id=b.id')->join('cpd_contract_config c on b.contact_config_id=c.id')->where($map)->order('a.add_tm desc')->limit($Page->firstRow . ',' . $Page->listRows)->field('a.*,b.contract_code,b.package,b.category_name,b.client_name,b.softname,c.c_name,b.flexible_sys')->select();
        $recharge_sum_add=0;
        $delivery_sum_add=0;
        foreach($lists as $k=>$v){
            $recharge_sum_add+=$v['recharge_sum'];
            $delivery_sum_add+=$v['delivery_sum'];
            $m_data=$this-> bill_is_generate(array('tm'=>$v['prestores_tm'],'contract_id'=>$v['contract_id'],'bs'=>2));
            $lists[$k]['is_edite']=$m_data?1:0;
            $lists[$k]['money_remain']=$v['recharge_sum']+$v['delivery_sum']-$v['recharge_expend_sum']-$v['delivery_expend_sum']-$v['recharge_sum_zhuan']-$v['delivery_sum_zhuan'];
        }
        if ($_GET['export'] == 1 && $_GET['ids']) {
            $where_two['a.id']=array('in',explode(',', $_GET['ids']));
            $prestores_list_check = $model->table('cpd_prestore a')->join('cpd_contract b on a.contract_id=b.id')->join('cpd_contract_config c on b.contact_config_id=c.id')->where($where_two)->order('a.add_tm desc')->field('a.*,b.contract_code,b.package,b.category_name,b.client_name,b.softname,c.c_name,b.flexible_sys')->select();
            foreach($prestores_list_check as $k=>$v){
                    $prestores_list_check[$k]['prestores_tm']=($v['flexible_sys']==1)?date("Ymd",$v["prestores_tm"]):date("Ym",$v["prestores_tm"]);
                    $contract_product=$v['softname']."\r\n".$v['package']."\r\n".$v['category_name'];
                    $prestores_list_check[$k]['contract_product']="\"$contract_product\"";
                    $remark=$v['remark'];
                    $prestores_list_check[$k]['remark']="\"$remark\"";

                    $arr=array('未收款','同客转出','同客转入','其他用途-转出');
                    $prestores_list_check[$k]['prestore_sta']=$v['receipts_status']?'已收款':$arr[$v['type']];

                    $prestores_list_check[$k]['invoice_sta']=$v['invoice_status']?'已开发票':'未开发票';
                    $prestores_list_check[$k]['recharge_sum']=$this->deal_number_format($v['recharge_sum']);
                    $prestores_list_check[$k]['receipts_sum']=$this->deal_number_format($v['receipts_sum']);
                    $prestores_list_check[$k]['delivery_sum']=$this->deal_number_format($v['delivery_sum']);
                    $prestores_list_check[$k]['recharge_sum_zhuan']=$this->deal_number_format($v['recharge_sum_zhuan']);
                    $prestores_list_check[$k]['delivery_sum_zhuan']=$this->deal_number_format($v['delivery_sum_zhuan']);
                    $prestores_list_check[$k]['recharge_expend_sum']=$this->deal_number_format($v['recharge_expend_sum']);
                    $prestores_list_check[$k]['delivery_expend_sum']=$this->deal_number_format($v['delivery_expend_sum']);
            }
            $this->export_deposit($prestores_list_check,"账户管理_预存详情_".date('Y-m-d').".csv", 'prestores');
        }
        $this->assign("lists", $lists);
        $this->assign("delivery_sum_add", $delivery_sum_add);
        $this->assign("recharge_sum_add", $recharge_sum_add);
        $this->assign('prestores_num', count($lists));
        $this->assign("flexible_sys", $this->flexible_sys);
        $Page->setConfig('header', '条记录');
        $Page->setConfig('first', '<<');
        $Page->setConfig('last', '>>');
        $show = $Page->show();
        $this->assign("page", $show);
        $this->display($this->tmp_path.__FUNCTION__);
    }
    public function deal_number_format($num){
        $num=number_format($num, 2);
        $num=($num!=0)?$num:'无';
        return "\"$num\"";
    }
    // 录入预存
    public function add_prestore_show() {
        $model = $this->cpd_model;
        if ($_POST) {
            $data['contract_id'] = trim($_POST['contract_id']);
            $data['prestores_tm'] = strtotime(trim($_POST['prestores_tm']));
            $data['recharge_sum'] =trim($_POST['recharge_sum']);
            
            foreach ($data as $d) {
                if (!isset($d) || $d == "") {
                    $this->error("数据不全，无法录入！");
                }
            }
            $data['delivery_sum'] =trim($_POST['delivery_sum']);
            if (!is_numeric($data['recharge_sum'])) {
                $this->error("充值金额必须是数字！");
            }
            if ($data['delivery_sum'] && !is_numeric($data['delivery_sum'])) {
                $this->error("配送金额必须是数字！");
            }
            //未产生消耗，预存日期必须必须大于最后一条（已消耗）的预存日期
            $prestore_expend=$model->table('cpd_prestore')->where(array('contract_id'=>$data['contract_id'],'recharge_expend_sum'=>array('exp','>0 || delivery_expend_sum>0')))->order('prestores_tm desc')->find();
            if($prestore_expend['prestores_tm']>$data['prestores_tm']){
                $this->error("预存日期必须必须大于最后一条（已消耗）的预存日期！");
            }
            $data['reviewer'] = $_SESSION['admin']['admin_id'];
            $data['add_tm'] = time();
            $data['update_tm'] = time();
            $data['remark'] = trim($_POST['remark']);
            if($this->bill_is_generate(array('tm'=>$data['prestores_tm'],'contract_id'=>$data['contract_id'],'bs'=>2))){
                $this->error("本月收款审核通过，不可添加！");
            }
            $this->empty_debt($data['contract_id'],$data);
            // if($this->empty_debt($data['contract_id'],$data)!=1){
            //     $debt=$model->table('cpd_prestore')->where(array('contract_id'=>$data['contract_id'],'recharge_sum'=>array('exp','>0 || delivery_sum>0'),'status'=>1,'`recharge_expend_sum`+`recharge_sum_zhuan`-`recharge_sum`'=>array('gt','0')))->find();
            //     $debt_money=$debt['recharge_expend_sum']+$debt['recharge_sum_zhuan']-$debt['recharge_sum'];
            // }
            $result = $model->table('cpd_prestore')->add($data);
            if ($result) {
                $model->table('cpd_balance_change')->add(array('contract_id'=>$data['contract_id'],'recharge_sum'=>$data['recharge_sum'],'delivery_sum'=>$data['delivery_sum'],'add_tm'=>$data['add_tm'],'remark'=>'预存使余额增加','type'=>4));
                //依据欠款重跑数据
                $debt_money=$data['recharge_expend_sum']+$data['delivery_expend_sum'];
                if($debt_money>0){
                    $expend_data=$model->table('cpd_expend')->where(array('contract_id'=>$data['contract_id'],'status'=>1))->order('expend_tm desc')->select();
                    $debt_sum=0;
                    foreach($expend_data as $k=>$v){
                        $debt_sum+=$v['download_recharge']+$v['download_delivery'];
                        if($debt_sum>=$debt_money){
                            $this->change_task_expend(array('id'=>$v['id'],'bs'=>1));
                            break;
                        }
                    }
                }
                $this->month_bill_syns_data(array('tm'=>$data['prestores_tm'],'contract_id'=>$data['contract_id'],'key'=>'month_prestore','key_data'=>($data['recharge_sum']+$data['delivery_sum'])));
                $this->writelog("CPD流量结算：合同-新录入了ID为{$result}，contract_id为[" . $data['contract_id'] . "]的预存。【CPD流量结算】",'cpd_prestore',$result,__ACTION__ ,'','add');
                $this->success("录入预存成功！");
            } else {
                $this->error("添加失败");
            }

            $this->redirect("prestores_list");
        } else {
            $this->assign("all_softnames", $this->get_all_softnames());
            $this->assign("vo_edit", 'add');
            $this->assign("flexible_sys", $this->flexible_sys);
            $this->display($this->tmp_path.__FUNCTION__);
        }
    }
    public function empty_debt($contract_id,&$data,$bs=''){
        $delivery_sum=$data['delivery_sum'];
        $recharge_sum=$data['recharge_sum'];
        $model = $this->cpd_model;
        $debt=$model->table('cpd_prestore')->where(array('contract_id'=>$contract_id,'recharge_sum'=>array('exp','>0 || delivery_sum>0'),'status'=>1,'`recharge_expend_sum`+`recharge_sum_zhuan`-`recharge_sum`'=>array('gt','0')))->find();
        $debt_money=$debt['recharge_expend_sum']+$debt['recharge_sum_zhuan']-$debt['recharge_sum'];
        if(!($debt_money>0)){
            return 1;
        }
       if($bs==1){
            $data['recharge_expend_sum']=$debt_money;
            $model->table('cpd_prestore')->save(array('id'=>$debt['id'],'recharge_expend_sum'=>($debt['recharge_sum']-$debt['recharge_sum_zhuan'])));
            return 1;
       }else{
            if($debt_money<=$recharge_sum){
                $data['recharge_expend_sum']=$debt_money;
                $model->table('cpd_prestore')->save(array('id'=>$debt['id'],'recharge_expend_sum'=>($debt['recharge_sum']-$debt['recharge_sum_zhuan'])));
                return 1;
            }else{
                $model->table('cpd_prestore')->save(array('id'=>$debt['id'],'recharge_expend_sum'=>($debt['recharge_sum']-$debt['recharge_sum_zhuan'])));
                if($delivery_sum==0){
                    $data['recharge_expend_sum']=$debt_money;
                    return 1;
                }else{
                    if($debt_money>=($delivery_sum+$recharge_sum)){
                        $data['recharge_expend_sum']=$debt_money-$delivery_sum;
                        $data['delivery_expend_sum']=$delivery_sum;
                    }else{
                        $data['recharge_expend_sum']=$recharge_sum;
                        $data['delivery_expend_sum']=$debt_money-$recharge_sum;
                    }
                    //需要重跑数据
                    return 0;
                }
            }
       }
        
    }
    // 编辑预存
    public function edit_prestore_show() {
        $model=$this->cpd_model;
        if ($_POST) {
            $data['id'] = trim($_POST['id']);
            $data['contract_id'] = trim($_POST['contract_id']);
            $data['prestores_tm'] = strtotime(trim($_POST['prestores_tm']));
            $data['recharge_sum'] =trim($_POST['recharge_sum']);

            foreach ($data as $d) {
                if (!isset($d) || $d == "") {
                    $this->error("数据不全，无法添加！");
                }
            }
            if (!is_numeric($data['recharge_sum'])) {
                $this->error("收款必须是数字！");
            }
            
            $data['delivery_sum'] =trim($_POST['delivery_sum']);
            if ($data['recharge_sum'] && !is_numeric($data['recharge_sum'])) {
                $this->error("配送金额必须是数字！");
            }
            $data['reviewer'] = $_SESSION['admin']['admin_id'];
            $data['remark'] = trim($_POST['remark']);
            $data['update_tm'] = time();
            //开启事物
            $be_data=$model->table('cpd_prestore')->where(array('id'=>$data['id']))->find();
            if($be_data['delivery_expend_sum']>0&&$be_data['recharge_sum']!=$data['recharge_sum']){
                $this->error("由于充值金额被已经被消耗完了,充值金额不能编辑");
            }
            if($be_data['delivery_expend_sum']>0 && ($be_data['delivery_expend_sum']+$be_data['delivery_sum_zhuan'])>$data['delivery_sum']){
                $this->error("配送金额不能过小");
            }
            if($be_data['recharge_expend_sum']>0 && ($be_data['recharge_expend_sum']+$be_data['recharge_sum_zhuan'])>$data['recharge_sum']){
                $this->error("充值金额不能过小");
            }
            $log_all_need = $this->logcheck(array('id' => $data['id']), 'cpd_prestore', $data, $model);
            if($this->bill_is_generate(array('tm'=>$data['prestores_tm'],'contract_id'=>$data['contract_id'],'bs'=>2))){
                $this->error("本月收款审核通过，不可编辑！");
            }
            if(($be_data['recharge_expend_sum']>0|| $be_data['delivery_expend_sum']>0)&&($be_data['prestores_tm']!=$data['prestores_tm'])){
                $this->error("此预存已产生消耗,不能编辑预存日期！");
            }else{
                //未产生消耗，预存日期必须必须大于最后一条（已消耗）的预存日期
                $prestore_expend=$model->table('cpd_prestore')->where(array('contract_id'=>$data['contract_id'],'recharge_expend_sum'=>array('exp','>0 || delivery_expend_sum>0')))->order('prestores_tm desc')->find();
                if($prestore_expend['prestores_tm']>$data['prestores_tm']){
                    $this->error("预存日期必须必须大于最后一条（已消耗）的预存日期！");
                }
            }
            $m_data = $model->table('cpd_prestore')->save($data);
            if ($m_data) {
                if($be_data['recharge_sum']==$data['recharge_sum']){
                    $recharge_sum=0;
                }else if($be_data['recharge_sum']>$data['recharge_sum']){
                    $recharge_sum=-($be_data['recharge_sum']-$data['recharge_sum']);
                }else if($be_data['recharge_sum']<$data['recharge_sum']){
                    $recharge_sum=$data['recharge_sum']-$be_data['recharge_sum'];
                }
                if($be_data['delivery_sum']==$data['delivery_sum']){
                    $delivery_sum=0;
                }else if($be_data['delivery_sum']>$data['delivery_sum']){
                    $delivery_sum=-($be_data['delivery_sum']-$data['delivery_sum']);
                }else if($be_data['delivery_sum']<$data['delivery_sum']){
                    $delivery_sum=$data['delivery_sum']-$be_data['delivery_sum'];
                }
                if($delivery_sum!=0 || $recharge_sum!=0){
                    $task_data=$model->table('cpd_task')->where(array('contract_id'=>$be_data['contract_id'],'start_tm'=>array('lt',time()),'end_tm'=>array('gt',time())))->find();
                    $model->table('cpd_balance_change')->add(array('contract_id'=>$be_data['contract_id'],'recharge_sum'=>$recharge_sum,'delivery_sum'=>$delivery_sum,'add_tm'=>time(),'remark'=>'编辑预存导致余额的变化','type'=>4));
                }
                $this->month_bill_syns_data(array('tm'=>$data['prestores_tm'],'contract_id'=>$data['contract_id'],'key'=>'month_prestore','key_data'=>($data['recharge_sum']+$data['delivery_sum']-$be_data['recharge_sum']-$be_data['delivery_sum'])));
                $this->writelog("CPD流量结算：账户管理-编辑了预存，id为[" . $data['id'] . "],".$log_all_need,'cpd_prestore',$data['id'],__ACTION__ ,'','edit');
                $this->success("编辑成功！");
            } else {
                $this->error('编辑失败');
            }
        } else {
            $id = trim($_GET['id']);
            if (!isset($id) || $id == "") {
                echo "<script>alert('id不存在');$('#TB_closeWindowButton').click();</script>";
                return;
            }
            $this->assign("vo_id",$id);
             // 获取合同数据
            $contract_id = trim($_GET['contract_id']);
            if($this->check_authority($contract_id)!=1){
                echo "<script>alert('权限不足');$('#TB_closeWindowButton').click();</script>";
                return;
            }
            $this->get_contract_data($contract_id);
            $data = $model->table('cpd_prestore')->where(array('id' => $id))->find();
            $this->assign("data", $data);
            $this->assign("flexible_sys", $this->flexible_sys);
            $this->display($this->tmp_path.'add_prestore_show');
        }
    }
    // 删除预存
    public function delete_prestore_show() {
        $model = $this->cpd_model;
        $map['id'] = trim($_GET['id']);
        $map['contract_id'] = trim($_GET['contract_id']);
        if($this->check_authority($map['contract_id'])!=1) {
            echo "<script>alert('权限不足');window.history.go(-1);</script>";
            // echo "<script>alert('权限不足');$('#TB_closeWindowButton').click();</script>";
            return;
        }
        $receipt_data = $model->table('cpd_prestore')->where(array('id' => $map['id'],'status'=>1))->find();
        if($receipt_data['receipts_status'] || $receipt_data['invoice_status']){
            $this->error("只有删除了关联的收款/发票，才能删除该预存记录！");
        }
        if($receipt_data['delivery_sum_zhuan']>0 || $receipt_data['recharge_sum_zhuan']>0 || $receipt_data['recharge_expend_sum']>0|| $receipt_data['delivery_expend_sum']>0){
            $this->error("此预存已产生消耗或被转出充值金额,不能删除！");
        }
        $data['status']=0;
        $data['update_tm']=time();
        if($this->bill_is_generate(array('tm'=>$receipt_data['prestores_tm'],'contract_id'=>$receipt_data['contract_id'],'bs'=>2))){
            $this->error("本月收款审核通过，不可删除！");
        }
        if (!$model->table('cpd_prestore')->where($map)->save($data)) {
            $this->error("删除失败！");
        }
        
        $task_data=$model->table('cpd_task')->where(array('contract_id'=>$receipt_data['contract_id'],'start_tm'=>array('lt',$data["update_tm"]),'end_tm'=>array('gt',$data["update_tm"])))->find();
        $model->table('cpd_balance_change')->add(array('contract_id'=>$receipt_data['contract_id'],'recharge_sum'=>"-".$receipt_data['recharge_sum'],'delivery_sum'=>"-".$receipt_data['delivery_sum'],'add_tm'=>time(),'remark'=>'预存删除','type'=>4));
        $this->month_bill_syns_data(array('tm'=>$receipt_data['prestores_tm'],'contract_id'=>$receipt_data['contract_id'],'key'=>'month_prestore','key_data'=>(-$receipt_data['recharge_sum']-$receipt_data['delivery_sum'])));
        $this->writelog("CPD流量结算：合同-删除了id为[" . $map['id'] . "]的预存记录。【CPD流量结算】",'cpd_prestore',$map['id'],__ACTION__ ,'','del');
        $this->success("删除成功！");
    }
     // 余额转移
    public function transfer_prestore_show() {
        $model=$this->cpd_model;
        if ($_POST) {            
            $contract_id_one = trim($_POST['contract_id']);
            $prestore_id = trim($_POST['prestore_id']);
            
            $data['recharge_sum'] =trim($_POST['recharge_sum_zhuan'])?trim($_POST['recharge_sum_zhuan']):'0';
            $data['delivery_sum'] =trim($_POST['delivery_sum_zhuan'])?trim($_POST['delivery_sum_zhuan']):'0';
            $type =trim($_POST['type']);
            $contract_id_two =trim($_POST['contract_id_zhuan']);
            if(!$contract_id_two && $type==1){
                $this->error("同客转出，应选择产品");
            }
            $pro_one=$model->table('cpd_contract')->where(array('id'=>$contract_id_one))->find();
            $pro_two=$model->table('cpd_contract')->where(array('id'=>$contract_id_two))->find();
            if (!is_numeric($data['recharge_sum'])) {
                $this->error("转移充值金额必须是数字！");
            }
            
            if ($data['recharge_sum'] && !is_numeric($data['recharge_sum'])) {
                $this->error("转移充值额必须是数字！");
            }
            if(!$type){
                $this->error("转移金额类型必选！");
            }
            if($type==2){
                if (!is_numeric($data['recharge_sum'])) {
                    $this->error("转移配送金额必须是数字！");
                }
                
                if ($data['delivery_sum'] && !is_numeric($data['delivery_sum'])) {
                    $this->error("转移配送金额必须是数字！");
                }
            }
            if($prestore_id){
                $sql="update `cpd_prestore` set `recharge_sum_zhuan` = `recharge_sum_zhuan`+{$data['recharge_sum']},`delivery_sum_zhuan` = `delivery_sum_zhuan`+{$data['delivery_sum']} WHERE `id` = {$prestore_id}";
                $model->query($sql);
            }
            $data['reviewer'] = $_SESSION['admin']['admin_id'];
            $data['remark'] = trim($_POST['remark']);
            $data['add_tm'] = time();

            $data['prestores_tm'] =($this->flexible_sys==1)?strtotime(date('Y-m-d',time())):strtotime(date('Y-m',time()));
            if($type==1){
                $tm=date('Y-m-d',time());
                $data['contract_id']=$contract_id_two;
                if(!$data['remark']){
                    $data['remark']="转入来源于：产品：{$pro_one['softname']},合同id为{$contract_id_one},时间：{$tm}";
                }
                $data['type']=2;

                $this->empty_debt($data['contract_id'],$data,1);

                $re=$model->table('cpd_prestore')->add($data);
                unset($data['recharge_expend_sum']); 

                
                $task_data=$model->table('cpd_task')->where(array('contract_id'=>$data['contract_id'],'start_tm'=>array('lt',time()),'end_tm'=>array('gt',time())))->find();
                $model->table('cpd_balance_change')->add(array('contract_id'=>$data['contract_id'],'recharge_sum'=>$data['recharge_sum'],'delivery_sum'=>$data['delivery_sum'],'add_tm'=>time(),'remark'=>'同客转入导致余额变化','type'=>2));
                $this->month_bill_syns_data(array('tm'=>$data['prestores_tm'],'contract_id'=>$data['contract_id'],'key'=>'month_prestore','key_data'=>($data['recharge_sum']+$data['delivery_sum'])));
                $this->writelog("CPD流量结算：账户管理-编辑了同客转入了余额，id为{$re}",'cpd_prestore',$re,__ACTION__ ,'','add');
                $data['contract_id']=$contract_id_one;
                $data['remark']="转出至：产品：{$pro_two['softname']},预存id为{$re},时间：{$tm}";
                $data['type']=1;
                $data['recharge_sum'] ='-'.trim($_POST['recharge_sum_zhuan']);
                $data['delivery_sum'] ='-'.trim($_POST['delivery_sum_zhuan']);
                $re=$model->table('cpd_prestore')->add($data);
                
                $model->table('cpd_balance_change')->add(array('contract_id'=>$data['contract_id'],'recharge_sum'=>$data['recharge_sum'],'delivery_sum'=>$data['delivery_sum'],'add_tm'=>($this->flexible_sys==1)?strtotime(date('Y-m-d',time())):strtotime(date('Y-m',time())),'remark'=>'同客转出导致余额变化','type'=>1));
                $this->month_bill_syns_data(array('tm'=>$data['prestores_tm'],'contract_id'=>$data['contract_id'],'key'=>'month_out','key_data'=>-($data['recharge_sum']+$data['delivery_sum'])));
                $this->writelog("CPD流量结算：账户管理-编辑了同客转出了余额，id为{$re}",'cpd_prestore',$re,__ACTION__ ,'','add');
            }else if($type==2){
                $data['contract_id']=$contract_id_one;
                if(!$data['remark']){
                    $data['remark']='其他用途-转出';
                }
                $data['recharge_sum'] ='-'.trim($_POST['recharge_sum_zhuan']);
                $data['delivery_sum'] ='-'.trim($_POST['delivery_sum_zhuan']);
                $data['type']=3;
                $re=$model->table('cpd_prestore')->add($data);
                
                $model->table('cpd_balance_change')->add(array('contract_id'=>$data['contract_id'],'recharge_sum'=>$data['recharge_sum'],'delivery_sum'=>$data['delivery_sum'],'add_tm'=>($this->flexible_sys==1)?strtotime(date('Y-m-d',time())):strtotime(date('Y-m',time())),'remark'=>'其他用途转出导致余额变化','type'=>3));
                $this->month_bill_syns_data(array('tm'=>$data['prestores_tm'],'contract_id'=>$data['contract_id'],'key'=>'month_out','key_data'=>-($data['recharge_sum']+$data['delivery_sum'])));
                $this->writelog("CPD流量结算：账户管理-编辑了其他用途转移了余额，id为{$re}",'cpd_prestore',$re,__ACTION__ ,'','add');
            }
            $this->success("余额转移成功！");
        } else {
            $contract_id = trim($_GET['contract_id']);
            $id = trim($_GET['id']);
            if (!isset($contract_id) || $contract_id == "") {
                echo "<script>alert('id不存在');$('#TB_closeWindowButton').click();</script>";
                return;
            }
            if($this->check_authority($contract_id)!=1){
                echo "<script>alert('权限不足');$('#TB_closeWindowButton').click();</script>";
                return;
            }
            $t=time();
            $data = $model->table('cpd_contract')->where(array('id' => $contract_id,'start_tm'=>array('elt',$t),'end_tm'=>array('egt',$t)))->find();
            if(!$data){
                echo "<script>alert('合同结束/终止后,不可以同客转移/转出');$('#TB_closeWindowButton').click();</script>";
                return;
            }

            $data_prestore = $model->table('cpd_prestore')->where(array('id' => $id))->find();
            $data['recharge_sum']=$data_prestore['recharge_sum']-$data_prestore['recharge_expend_sum']-$data_prestore['recharge_sum_zhuan'];
            $data['delivery_sum']=$data_prestore['delivery_sum']-$data_prestore['delivery_expend_sum']-$data_prestore['delivery_sum_zhuan'];
            $data['prestore_id']=$data_prestore['id'];
            $this->assign("data", $data);
            $same_product = $model->table('cpd_contract')->where(array('custom_id' => $data['custom_id'],'end_tm'=>array('gt',time()),'flexible_sys'=>$this->flexible_sys,'status'=>1,'id'=>array('neq',$contract_id)))->select();
            $this->assign("same_product", $same_product);

            $this->display($this->tmp_path.__FUNCTION__);
        }
    }
    // 转出/退款管理
    public function transfer_prestore_list() {
        
        $map=array();
        if($_GET['endtime'] && $this->flexible_sys==2){
            $_GET['endtime']=date('Y-m-d H:i:s',strtotime($_GET['endtime'])+86399+(date('t',$_GET['endtime'])-1)*86400);
        }
        $map=$this->sync_search($map,'prestores_tm');

        $model = $this->cpd_model;
        $map['a.type'] = array('in',array('1','3'));
        import("@.ORG.Page");
        $count = $model->table('cpd_prestore a')->join('cpd_contract b on a.contract_id=b.id')->join('cpd_contract_config c on b.contact_config_id=c.id')->where($map)->order('a.add_tm desc')->count();
        $Page = new Page($count, 50);
        $lists = $model->table('cpd_prestore a')->join('cpd_contract b on a.contract_id=b.id')->join('cpd_contract_config c on b.contact_config_id=c.id')->where($map)->order('a.add_tm desc')->limit($Page->firstRow . ',' . $Page->listRows)->field('a.*,b.contract_code,b.package,b.category_name,b.client_name,b.softname,c.c_name,b.flexible_sys')->select();
        $count = $model->table($cpd_receipts)->where($map)->order($str)->count();
        $recharge_sum_add=0;
        $delivery_sum_add=0;
        foreach($lists as $k=>$v){
            $lists[$k]['recharge_sum']=$v['recharge_sum']*(-1);
            $lists[$k]['delivery_sum']=$v['delivery_sum']*(-1);
            $admin_user= $model->table('sj_admin_users')->where(array('admin_user_id'=>$v['reviewer']))->find();
            if($admin_user){
                $lists[$k]['admin_user_name']=$admin_user['admin_user_name'];
            }
            $recharge_sum_add+=$v['recharge_sum'];
            $delivery_sum_add+=$v['delivery_sum'];
        }
        $recharge_sum_add=$recharge_sum_add*(-1);
        $delivery_sum_add=$delivery_sum_add*(-1);
        $this->assign("lists", $lists);
        $this->assign("delivery_sum_add", $delivery_sum_add);
        $this->assign("recharge_sum_add", $recharge_sum_add);
        $this->assign('prestores_num', count($lists));        
        $Page->setConfig('header', '条记录');
        $Page->setConfig('first', '<<');
        $Page->setConfig('last', '>>');
        $show = $Page->show();
        $this->assign("flexible_sys", $this->flexible_sys);
        $this->assign("page", $show);
        $this->display($this->tmp_path.__FUNCTION__);
    }
    // 余额管理
    public function balance_change_list() {
        $map=array();
        $endtime=$_GET['endtime'];
        if($_GET['endtime'] && $this->flexible_sys==1){
            $_GET['endtime']=date('Y-m-d H:i:s',strtotime($_GET['endtime'])+86399);
        }else if($_GET['endtime'] && $this->flexible_sys==2){
            $_GET['endtime']=date('Y-m-d H:i:s',strtotime($_GET['endtime'])+86399+(date('t',$_GET['endtime'])-1)*86400);
        }

        $map=$this->sync_search($map,'add_tm');
         $this->assign("endtime", $endtime);

        $model = $this->cpd_model;

        import("@.ORG.Page");
        $count = $model->table('cpd_balance_change a')->join('cpd_contract b on a.contract_id=b.id')->join('cpd_contract_config c on b.contact_config_id=c.id')->where($map)->order('a.add_tm desc')->count();
        $Page = new Page($count, 50);
        $lists = $model->table('cpd_balance_change a')->join('cpd_contract b on a.contract_id=b.id')->join('cpd_contract_config c on b.contact_config_id=c.id')->where($map)->order('a.add_tm desc')->limit($Page->firstRow . ',' . $Page->listRows)->field('a.*,b.contract_code,b.package,b.category_name,b.client_name,b.softname,c.c_name,b.flexible_sys')->select();
        $count = $model->table($cpd_receipts)->where($map)->order($str)->count();
        $recharge_sum_add=0;
        $delivery_sum_add=0;
        foreach($lists as $k=>$v){
            $recharge_sum_add+=$v['recharge_sum'];
            $delivery_sum_add+=$v['delivery_sum'];
            
        }
        if ($_GET['export'] == 1 && $_GET['ids']) {
            $ids=explode(',', $_GET['ids']);
            $where_two['a.id']=array('in',$ids);
            $balance_list_check = $model->table('cpd_balance_change a')->join('cpd_contract b on a.contract_id=b.id')->join('cpd_contract_config c on b.contact_config_id=c.id')->where($where_two)->order('a.add_tm desc')->field('a.*,b.contract_code,b.package,b.category_name,b.client_name,b.softname,c.c_name,b.flexible_sys')->select();
            foreach($balance_list_check as $k=>$v){
                    $contract_product=$v['softname']."\r\n".$v['package']."\r\n".$v['category_name'];
                    $balance_list_check[$k]['contract_product']="\"$contract_product\"";

                    $arr=array('同客转出','同客转入','其他用途-转出','预存','消耗');
                    $balance_list_check[$k]['prestore_sta']=$arr[$v['type']-1];

                    $balance_list_check[$k]['add_tm']=($this->flexible_sys==1)?date('Ymd',$v['add_tm']):date('Ym',$v['add_tm']);

                    $balance_list_check[$k]['recharge_sum']=$this->deal_number_format($v['recharge_sum']);
                    $balance_list_check[$k]['delivery_sum']=$this->deal_number_format($v['delivery_sum']);
            }
            $this->export_deposit($balance_list_check,"账户管理_余额详情_".date('Y-m-d').".csv", 'balance');
        }
        $this->assign("lists", $lists);
        $this->assign("delivery_sum_add", $delivery_sum_add);
        $this->assign("recharge_sum_add", $recharge_sum_add);
        $this->assign("flexible_sys", $this->flexible_sys);
        $this->assign('prestores_num', count($lists));
        
        $Page->setConfig('header', '条记录');
        $Page->setConfig('first', '<<');
        $Page->setConfig('last', '>>');
        $show = $Page->show();
        $this->assign("page", $show);
        $this->display($this->tmp_path.__FUNCTION__);
    }
    // 结算详情--收款
    public function receives_list() {
        $map=array();
        $map=$this->sync_search($map,'prestores_tm');
        $map['type']=0;

        $model = $this->cpd_model;
        import("@.ORG.Page");
        $count = $model->table('cpd_prestore a')->join('cpd_contract b on a.contract_id=b.id')->join('cpd_contract_config c on b.contact_config_id=c.id')->where($map)->order('a.add_tm desc')->count();
        $Page = new Page($count, 10);
        $lists = $model->table('cpd_prestore a')->join('cpd_contract b on a.contract_id=b.id')->join('cpd_contract_config c on b.contact_config_id=c.id')->where($map)->order('a.add_tm desc')->limit($Page->firstRow . ',' . $Page->listRows)->field('a.*,b.contract_code,b.package,b.category_name,b.client_name,b.softname,c.c_name,b.flexible_sys')->select();
        $recharge_sum_add=0;
        $delivery_sum_add=0;
        foreach($lists as $k=>$v){
            $recharge_sum_add+=$v['recharge_sum'];
            $delivery_sum_add+=$v['delivery_sum'];
            $m_data=$this-> bill_is_generate(array('tm'=>$v['receipts_tm'],'contract_id'=>$v['contract_id'],'bs'=>2));
            $lists[$k]['is_edite']=$m_data?1:0;
        }

        $this->assign("delivery_sum_add", $delivery_sum_add);
        $this->assign("recharge_sum_add", $recharge_sum_add);

        if ($_GET['export'] == 1 && $_GET['ids']) {
            $ids=explode(',', $_GET['ids']);
            $where_two['a.id']=array('in',$ids);
            $receipts_list_check = $model->table('cpd_prestore a')->join('cpd_contract b on a.contract_id=b.id')->join('cpd_contract_config c on b.contact_config_id=c.id')->where($where_two)->order('a.add_tm desc')->field('a.*,b.contract_code,b.package,b.category_name,b.client_name,b.softname,c.c_name,b.flexible_sys')->select();
            foreach($receipts_list_check as $k=>$v){ 
                    $contract_product=$v['softname']."\r\n".$v['package']."\r\n".$v['category_name'];
                    $receipts_list_check[$k]['contract_product']="\"$contract_product\"";
                    $receipts_list_check[$k]['invoice_status']=$v["invoice_status"]?'已开发票':'未开发票';

                    $receipts_list_check[$k]['prestores_tm']=($v['flexible_sys']==1)?date("Ymd",$v["prestores_tm"]):date("Ym",$v["prestores_tm"]);

                    $receipts_list_check[$k]['prestores_money']=$this->deal_number_format($v['recharge_sum']);
                    $receipts_list_check[$k]['delivery_sum']=$this->deal_number_format($v['delivery_sum']);
                    $receipts_list_check[$k]['receipts_tm']=$v["receipts_tm"]?(($v['flexible_sys']==1)?date("Ymd",$v["receipts_tm"]):date("Ym",$v["receipts_tm"])):'';

                    $receipts_list_check[$k]['receipts_sum']=$this->deal_number_format($v['receipts_sum']);

                    $receipts_list_check[$k]['invoice_tm']=$v["invoice_tm"]?(($v['flexible_sys']==1)?date("Ymd",$v["invoice_tm"]):date("Ym",$v["invoice_tm"])):'';
                    $receipts_list_check[$k]['invoice_number']=$v["invoice_number"]?$v['invoice_number']:'';

                    $receipts_list_check[$k]['invoice_sum']=$this->deal_number_format($v['invoice_sum']);
            }
            $this->export_deposit($receipts_list_check,"账户管理_收款详情_".date('Y-m-d').".csv", 'deposits');
        }
        $this->assign("lists", $lists);
        $this->assign('receipts_num', count($lists));
        $this->assign("flexible_sys", $this->flexible_sys);
        $Page->setConfig('header', '条记录');
        $Page->setConfig('first', '<<');
        $Page->setConfig('last', '>>');
        $show = $Page->show();
        $this->assign("page", $show);
        $this->display($this->tmp_path.__FUNCTION__);
    }
    // 编辑收款
    function edit_receive_show() {
        $model=$this->cpd_model;
        if ($_POST) {
            $data['id'] = trim($_POST['id']);
            $data['contract_id'] = trim($_POST['contract_id']);
            $data['receipts_sum'] = trim($_POST['receipts_sum']);
            $receipts_sum_old=trim($_POST['receipts_sum_old']);
            if (!is_numeric($data['receipts_sum'])) {
                $this->error("收款必须是数字！");
            }
            $data['receipts_tm'] = strtotime(trim($_POST['receipts_tm']));
            foreach ($data as $d) {
                if (!isset($d) || $d == "") {
                    $this->error("数据不全，无法添加！");
                }
            }
            $data['receipts_remark'] = trim($_POST['receipts_remark']);
            $data['update_tm'] = time();
            $data['receipts_status'] = 1;

            $log_all_need = $this->logcheck(array('id' => $data['id']), 'cpd_prestore', $data, $model);
            if($this->bill_is_generate(array('tm'=>$data['receipts_tm'],'contract_id'=>$data['contract_id'],'bs'=>2))){
                $this->error("收款日期的收款审核通过，不可编辑！");
            }
            $receipts_id=$model->table('cpd_prestore')->where(array('id'=>$_POST['id']))->save($data);
            $this->month_bill_syns_data(array('tm'=>$data['receipts_tm'],'contract_id'=>$data['contract_id'],'key'=>'month_receipts','key_data'=>($data['receipts_sum']-$receipts_sum_old)));

            $this->writelog("CPD流量结算：账户管理-编辑了contract_id为[" . $data['contract_id'] . "]的收款，收款id为{$_POST['id']}。".$log_all_need,'cpd_prestore',$_POST['id'],__ACTION__ ,'','edit');
            $this->success("操作成功！");
        } else {
            $id = trim($_GET['id']);
            if (!isset($id) || $id == "") {
                echo "<script>alert('id不存在');$('#TB_closeWindowButton').click();</script>";
                return;
            }
            $this->assign("vo_id",$id);
            $data = $model ->table('cpd_prestore')->where(array('id' => $id))->find();
            $this->assign("vo_edit", 'edit');
            $contract_id = $_GET['contract_id'];
            
            $cpd_contract="cpd_contract";
            // 获取合同数据
            $this->get_contract_data($contract_id);

            if($this->check_authority($contract_id)!=1){
                echo "<script>alert('权限不足');$('#TB_closeWindowButton').click();</script>";
                return;
            }

            $this->assign("data", $data);
            $this->assign("flexible_sys", $this->flexible_sys);
            $this->display($this->tmp_path.'add_receive_show');
        }
    }
    function get_contract_data($contract_id){
        $model = $this->cpd_model;
        $contract_data = $model->table('cpd_contract b')->where(array('b.id' => $contract_id))->join('cpd_contract_config c on b.contact_config_id=c.id')->find(); 
        $this->assign("contract_id", $contract_id);
        $this->assign("softname", $contract_data['softname']);
        $this->assign("client_name", $contract_data['client_name']);
        $this->assign("c_name", $contract_data['c_name']);

        $this->assign("c_start_tm", ($this->flexible_sys==1)?date('Y-m-d',$contract_data['start_tm']):date('Y-m',$contract_data['start_tm']));
        $this->assign("c_end_tm", ($this->flexible_sys==1)?date('Y-m-d',$contract_data['end_tm']):date('Y-m',$contract_data['end_tm']));

        $this->assign("category_name", $contract_data['category_name']);
        $this->assign("contract_data", $contract_data);
    }
    // 驳回收款
    public function delete_receive_show() {
        //获取ID号
        $model = $this->cpd_model;
        $map['id'] = trim($_GET['id']);
        $map['contract_id'] = trim($_GET['contract_id']);
        if($this->check_authority($map['contract_id'])!=1) {
            echo "<script>alert('权限不足');window.history.go(-1);</script>";
            // echo "<script>alert('权限不足');$('#TB_closeWindowButton').click();</script>";
            return;
        }
        $receipt_data = $model->table('cpd_prestore')->where(array('id' => $map['id'],'status'=>1))->find();
        $data['receipts_sum']=0;
        $data['receipts_tm']=0;
        $data['receipts_remark']='';
        $data['receipts_status']=0;
        if($this->bill_is_generate(array('tm'=>$receipt_data['receipts_tm'],'contract_id'=>$receipt_data['contract_id'],'bs'=>2))){
            $this->error("收款日期的收款审核通过，不可驳回！");
        }
        if (!$model->table('cpd_prestore')->where($map)->save($data)) {
            $this->error("驳回失败！");
        }
        $this->month_bill_syns_data(array('tm'=>$receipt_data['receipts_tm'],'contract_id'=>$receipt_data['contract_id'],'key'=>'month_receipts','key_data'=>'-'.$receipt_data['receipts_sum']));

        $this->writelog("CPD流量结算：合同-删除了id为[" . $map['id'] . "]的收款记录。【CPD流量结算】",'cpd_prestore',$map['id'],__ACTION__ ,'','del');
        $this->success("驳回成功！");
    }
    function month_bill_syns_data($data){
        $model=$this->cpd_model;
        $tm=date('Ym',$data['tm']);
        $sql="update `cpd_month_bill` set {$data['key']} = {$data['key']}+{$data['key_data']} WHERE `contract_id` = {$data['contract_id']} and `bill_month`={$tm}";
        $model->query($sql);
        //同步数据数据work
        $task_client = get_task_client();
        $task_client->doBackground("cpd_month_data_db", json_encode(array("contract_id" =>$data['contract_id'],'bill_month'=>date('Y-m',$data['tm'])))); 

    }
    // 发票详情--发票
    public function invoices_list() {
        $map=array();
        $map=$this->sync_search($map,'prestores_tm');
        $map['type']=0;
        $model = $this->cpd_model;
        import("@.ORG.Page");

        $count = $model->table('cpd_prestore a')->join('cpd_contract b on a.contract_id=b.id')->join('cpd_contract_config c on b.contact_config_id=c.id')->where($map)->order('a.add_tm desc')->count();
        $Page = new Page($count, 10);
        $lists = $model->table('cpd_prestore a')->join('cpd_contract b on a.contract_id=b.id')->join('cpd_contract_config c on b.contact_config_id=c.id')->where($map)->order('a.invoice_status asc,a.prestores_tm desc')->limit($Page->firstRow . ',' . $Page->listRows)->field('a.*,b.contract_code,b.package,b.category_name,b.client_name,b.softname,c.c_name,b.flexible_sys')->select();
        if ($_GET['export'] == 1 && $_GET['ids']) {
            $ids=explode(',', $_GET['ids']);
            $where_two['a.id']=array('in',$ids);
            $invoices_list_check = $model->table('cpd_prestore a')->join('cpd_contract b on a.contract_id=b.id')->join('cpd_contract_config c on b.contact_config_id=c.id')->where($where_two)->order('a.add_tm desc')->field('a.*,b.contract_code,b.package,b.category_name,b.client_name,b.softname,c.c_name,b.flexible_sys')->select();
            foreach($invoices_list_check as $k=>$v){
                    $contract_product=$v['softname']."\r\n".$v['package']."\r\n".$v['category_name'];
                    $invoices_list_check[$k]['contract_product']="\"$contract_product\"";

                    $invoices_list_check[$k]['prestores_tm']=($v['flexible_sys']==1)?date("Ymd",$v["prestores_tm"]):date("Ym",$v["prestores_tm"]);

                    $invoices_list_check[$k]['prestores_money']=$this->deal_number_format($v['recharge_sum']);
                    $invoices_list_check[$k]['delivery_sum']=$this->deal_number_format($v['delivery_sum']);
                    $invoices_list_check[$k]['invoice_sum']=$this->deal_number_format($v['invoice_sum']);
                    $invoices_list_check[$k]['invoice_tm']=$v["invoice_tm"]?(($v['flexible_sys']==1)?date("Ymd H:i:s",$v["invoice_tm"]):date("Ym",$v["invoice_tm"])):'';
                    $invoices_list_check[$k]['invoice_number']=$v["invoice_number"];
                    $invoices_list_check[$k]['invoice_remark']=$v["invoice_remark"];
                    $invoices_list_check[$k]['invoice_status']=$v["invoice_status"]?'已开发票':'未开发票';
            }
            $this->export_deposit($invoices_list_check,"账户管理_发票详情_".date('Y-m-d').".csv", 'invoices');
        }

        foreach($lists as $k=>$v){
            $m_data=$this-> bill_is_generate(array('tm'=>$v['invoice_tm'],'contract_id'=>$v['contract_id'],'bs'=>2));
            $lists[$k]['is_edite']=$m_data?1:0;
        }
        $total=0;
        foreach($lists as $v){
            $total+=$v['invoice_sum'];
        }
        $this->assign("total", $total);
        $this->assign("lists", $lists);
        $this->assign('invoicess_num',count($lists));
        $this->assign("flexible_sys", $this->flexible_sys);
        $this->assign("contract_id", $contract_id);
        $Page->setConfig('header', '条记录');
        $Page->setConfig('first', '<<');
        $Page->setConfig('last', '>>');
        $show = $Page->show();
        $this->assign("page", $show);
        $this->display($this->tmp_path.__FUNCTION__);
    }
    public function batch_receipts_confirm(){
        $this->assign('bs_str','收款');
        $this->batch_invoice_receipts_add('receipts_sum','receipts_tm','receipts_status','receipts_remark','收款');
    }
    public function batch_invoice_confirm(){
        $this->assign('bs_str','发票');
        $this->batch_invoice_receipts_add('invoice_sum','invoice_tm','invoice_status','invoice_remark','发票');
    }
    public function batch_invoice_receipts_add($invoice_receipts_sum,$tm,$is_status,$remark,$log){
        $model = $this->cpd_model;
        if($_GET){
            $ids=$_GET['ids'];
            
            $prestore_data=$model->table('cpd_prestore')->where(array('id'=>array('in',explode(',', $ids))))->select();
            $money_sum=0;
            foreach($prestore_data as $k=>$v){
                $money_sum+=$v['recharge_sum'];
            }
            $this->assign('ids',$ids);
            $this->assign('money_sum',$money_sum);
            $this->assign("flexible_sys", $this->flexible_sys);
            $this->display($this->tmp_path.__FUNCTION__);
        }
        if($_POST){
            $ids=$_POST['ids'];
            $id_arr=explode(',', $ids);
            $prestore_data=$model->table('cpd_prestore')->where(array('id'=>array('in',$id_arr)))->select();
            $data=array();
            $data[$tm] = strtotime(trim($_POST['invoice_tm']));

            $authority_arr=array();
            $confirm_arr=array();
            $no_edit_arr=array();
            $id_edit=array();
            foreach($prestore_data as $k=>$v){
                if($this->bill_is_generate(array('tm'=>$data[$tm],'contract_id'=>$v['contract_id'],'bs'=>2))){
                    $no_edit_arr[]=$v['id'];
                    continue;
                }
                if($this->check_authority($v['contract_id'])!=1){
                    $authority_arr[]=$v['id'];
                    continue;
                }
                if($v[$is_status]==1){
                    //已结确认发票
                    $confirm_arr[]=$v['id'];
                    continue;
                }
                $id_edit[]=$v['id'];
            }
            
            
            $data[$remark] = trim($_POST['invoice_remark']);
            $data[$is_status] = 1;
            $data[$invoice_receipts_sum] = array('exp','recharge_sum');
            $data['update_tm'] = time();
            

            $model->table('cpd_prestore')->where(array('id'=>array('in',$id_edit)))->save($data);
            $prestores=$model->table('cpd_prestore')->where(array('id'=>array('in',$id_edit)))->select();
            foreach($prestores as $k=>$v){
                if($invoice_receipts_sum=='receipts_sum'){
                    $this->month_bill_syns_data(array('tm'=>$v[$tm],'contract_id'=>$v['contract_id'],'key'=>'month_receipts','key_data'=>($v[$invoice_receipts_sum])));
                }else{
                     $task_client = get_task_client();
                     $task_client->doBackground("cpd_month_data_db", json_encode(array("contract_id" =>$v['contract_id'],'bill_month'=>date('Y-m',$v[$tm])))); 
                }
            }
            
            $this->writelog("CPD流量结算：账户管理-编辑了预存id为[" . implode(',', $id_edit) . "]的".$log."。".$log_all_need,'cpd_prestore',implode(',', $id_edit),__ACTION__ ,'','edit');
            $str="批量录入".$log."失败如下:\n";
            $i=0;
            if($no_edit_arr){
                $i++;
                $str.="本月收款审核通过，不可录入".$log."的预存：".count($no_edit_arr)."个"."\n";
            }
            if($confirm_arr){
                $i++;
                $str.="已经录入".$log."的预存：".count($confirm_arr)."个"."\n";
            }
            if($authority_arr){
                $i++;
                $str.="录入".$log."预存权限不足：".count($authority_arr)."个"."\n";
            }

            if($i>0){
                $this->error($str);
            }
            $this->success("操作成功！");
        }
    } 
    public function sync_search($map,$key){
        $model=$this->cpd_model;
        if($_GET['s_contract_code'] && $s_contract_code = trim($_GET['s_contract_code'])){
            $map['b.contract_code'] = array('eq', $s_contract_code);
            // $map['b.contract_code'] = array('exp', "='{$s_contract_code}' || b.flexible_c_code='{$s_contract_code}'");
            $this->assign("contract_code", $s_contract_code);
        }
        if($_GET['s_task_id'] && $task_id = trim($_GET['s_task_id'])){
            $map['a.task_id'] = array('eq', $task_id);
            $this->assign("task_id", $task_id);
        }
        if($_GET['type'] && $type = trim($_GET['type'])){
            if($key=='prestores_tm'){
                if($type<=3){
                    $map['a.type'] = array('eq', $type);
                }else{
                    $map['a.receipts_status'] = array('eq', ($type==4)?1:0);
                }
                
            }else{
                $map['a.type'] = array('eq', $type);
            }
            
            $this->assign("type", $type);
        }
        if($_GET['invoice_status'] && $invoice_status = trim($_GET['invoice_status'])){
            $map['a.invoice_status'] = array('eq', ($invoice_status==2)?0:1);
            $this->assign("invoice_status", $invoice_status);
        }
        if($_GET['s_package'] && $s_package = trim($_GET['s_package'])){
            $map['b.package'] = array('eq', $s_package);
            $this->assign("package", $s_package);
        }
        if($_GET['s_softname'] && $s_softname = trim($_GET['s_softname'])){
            // $map['b.softname'] = array('eq', $s_softname);
            $map['b.softname'] = array('like', "%{$s_softname}%");
            $this->assign("softname", $s_softname);
        }
        if($_GET['begintime'] && $begintime = strtotime(trim($_GET['begintime']))){
            $map["a.{$key}"] = array('egt', $begintime);
            $this->assign("begintime", $_GET['begintime']);
        }
        if($_GET['endtime'] && $endtime = strtotime(trim($_GET['endtime']))){
            $map["a.{$key}"] = array('elt', $endtime);
            $this->assign("endtime", $_GET['endtime']);
        }
        if($begintime && $endtime){
            $map["a.{$key}"] = array('exp', ">=$begintime and a.{$key}<=$endtime");
        }

        if($_GET['invoice_begintime'] && $invoice_begintime = strtotime(trim($_GET['invoice_begintime']))){
            $map["a.invoice_tm"] = array('egt', $invoice_begintime);
            $this->assign("invoice_begintime", $_GET['invoice_begintime']);
        }
        if($_GET['invoice_endtime'] && $invoice_endtime = strtotime(trim($_GET['invoice_endtime']))){
            $map["a.invoice_tm"] = array('elt', $invoice_endtime);
            $this->assign("invoice_endtime", $_GET['invoice_endtime']);
        }
        if($invoice_begintime && $invoice_endtime){
            $map["a.invoice_tm"] = array('exp', ">=$invoice_begintime and a.invoice_tm<=$invoice_endtime");
        }

        if($_GET['receipts_begintime'] && $receipts_begintime = strtotime(trim($_GET['receipts_begintime']))){
            $map["a.receipts_tm"] = array('egt', $receipts_begintime);
            $this->assign("receipts_begintime", $_GET['receipts_begintime']);
        }
        if($_GET['receipts_endtime'] && $receipts_endtime = strtotime(trim($_GET['receipts_endtime']))){
            $map["a.receipts_tm"] = array('elt', $receipts_endtime);
            $this->assign("receipts_endtime", $_GET['receipts_endtime']);
        }
        if($begintime && $receipts_endtime){
            $map["a.receipts_tm"] = array('exp', ">=$receipts_begintime and a.receipts_tm<=$receipts_endtime");
        }
        if($_GET['contact_config_id'] && $contact_config_id = trim($_GET['contact_config_id'])){
            $map['b.contact_config_id'] = array('eq', $contact_config_id);
            $this->assign("contact_config_id", $contact_config_id);
        }
        $map['a.status'] = 1;
        // if($key!='price_tm'){
            if($this->flexible_sys==1){
                $map['b.flexible_sys'] = 1;
            }else{
                $map['b.flexible_sys'] = 2;
            }
        // }
        
        $map['b.status'] = array('neq',0);
        $contract_configs=$model->table('cpd_contract_config')->where(array('status'=>1))->select();
        $this->assign("contract_configs", $contract_configs);
        return $map;
    }
    function get_all_softnames($bs=''){
        $model = $this->cpd_model;
        $cpd_contract="cpd_contract";
        $t=strtotime(date('Y-m-d',time()));
        $where=array();
        if(!$bs){
            $where['status']=1;
            $where['start_tm']=array('elt',$t);
            $where['end_tm']=array('egt',$t);
        }else if($bs==1){
            $where['status']=array('neq',0);
        }
        $where['flexible_sys']=$this->flexible_sys;
        $where['flexible_c_code']=array('eq','');
        $all_softname=  $model->table($cpd_contract)->where($where)->order('id desc')->field('softname,contract_code')->select();
        $all_softnames='';
        foreach($all_softname as $v){
            $all_softnames.= '"'.$v['softname'].'-'.$v['contract_code'].'",';
        }
        $all_softnames = substr($all_softnames,0,-1);
        return $all_softnames;
    }
    //获取软件信息
    function get_soft_info(){
        $model = $this->cpd_model;
        if($ids=trim($_POST['ids'])){
            $t=strtotime(date('Y-m-d',time()));
            $where_two['a.id']=array('in',$ids);
            $where_two['b.start_tm']=array('elt',$t);
            $where_two['b.end_tm']=array('egt',$t);

            $expends = $model->table('cpd_expend a')->join('cpd_contract b on a.contract_id=b.id')->where($where_two)->field('a.*,b.contract_code,b.package,b.category_name,b.client_name,b.softname,b.flexible_sys')->find();
            if($expends){
                if($this->flexible_sys!=$expends['flexible_sys']){
                    $arr=array();
                    $arr['msg']='权限不足';
                    $arr['code']=0;
                    echo json_encode($arr);
                    return;
                }else{
                    $arr=array();
                    $arr['code']=1;
                    echo json_encode($arr);
                    return;
                }
                
            }else{
                $arr=array();
                $arr['msg']='合同结束/终止后,不可以录入核减';
                $arr['code']=0;
                echo json_encode($arr);
                return;
            }
        }
        if($package=trim($_POST['package'])){
            $contract_data = $model->table('sj_soft b')->where(array('b.package' => $package,'b.status'=>1,'b.hide'=>1))->order('softid desc')->field('b.*')->find();
            if($contract_data){
                $arr=array();
                $arr['contract_data']=$contract_data;
                $arr['code']=1;
                echo json_encode($arr);
                return;
            }else{
                $arr=array();
                $arr['msg']='软件包名不存在';
                $arr['code']=0;
                echo json_encode($arr);
                return;
            }
            
        }
        if($_POST['softname']){
            $str=explode('-', $_POST['softname']);
            $t=strtotime(date('Y-m-d',time()));
            $softname=$str[0];
            $contract_code=$str[1];
            $contract_data = $model->table('cpd_contract b')->where(array('b.contract_code' => $contract_code,'b.softname' => $softname,'b.flexible_sys'=>$this->flexible_sys,'b.status'=>1,'start_tm'=>array('elt',$t),'end_tm'=>array('egt',$t)))->join('cpd_contract_config c on b.contact_config_id=c.id')->field('b.*,c.c_name')->find();
            if(!$contract_data){
                echo json_encode(array('code'=>0,'msg'=>'软件名称不存在'));
                return;
            }else if($this->flexible_sys!=$contract_data['flexible_sys']){
                echo json_encode(array('code'=>0,'msg'=>'权限不足'));
                return;
            }
            $arr=array();
            if($contract_data['flexible_sys']==1){
                $contract_data['c_start_tm']=date('Y-m-d',$contract_data['start_tm']);
                $contract_data['c_end_tm']=date('Y-m-d',$contract_data['end_tm']);
            }else{
                $contract_data['c_start_tm']=date('Y-m',$contract_data['start_tm']);
                $contract_data['c_end_tm']=date('Y-m',$contract_data['end_tm']);
            }
            $arr['contract_data']=$contract_data;
            $arr['code']=1;
            echo json_encode($arr);
        }else{
            echo json_encode(array('code'=>0,'msg'=>'软件名称不能为空'));
        }
    }
    // 编辑发票
    function edit_invoice_show() {
        $model=$this->cpd_model;
        if ($_POST) {
            $data['id'] = trim($_POST['id']);
            $data['contract_id'] = trim($_POST['contract_id']);
            $data['invoice_sum'] = trim($_POST['invoice_sum']);
            if (!is_numeric($data['invoice_sum'])) {
                $this->error("发票金额必须是数字！");
            }
            $data['invoice_tm'] = strtotime(trim($_POST['invoice_tm']));
            foreach ($data as $d) {
                if (!isset($d) || $d == "") {
                    $this->error("数据不全，无法添加！");
                }
            }
            $data['invoice_number'] = trim($_POST['invoice_number']);
            $data['invoice_remark'] = trim($_POST['invoice_remark']);
            $data['invoice_status'] = 1;
            $data['update_tm'] = time();
            //准备写入数据
            $log_all_need = $this->logcheck(array('id' => $data['id']), 'cpd_prestore', $data, $model);
            if($this->bill_is_generate(array('tm'=>$data['invoice_tm'],'contract_id'=>$data['contract_id'],'bs'=>2))){
                $this->error("本月收款审核通过，不可编辑！");
            }
            $invoice_id=$model->table('cpd_prestore')->where(array('id'=>$_POST['id']))->save($data);
            $task_client = get_task_client();
            $task_client->doBackground("cpd_month_data_db", json_encode(array("contract_id" =>$data['contract_id'],'bill_month'=>date('Y-m',$data['invoice_tm'])))); 
            $this->writelog("CPD流量结算：账户管理-编辑了contract_id为[" . $data['contract_id'] . "]的发票。".$log_all_need,'cpd_prestore',$data['contract_id'],__ACTION__ ,'','edit');
            $this->success("操作成功！");
        } else {
            $id = trim($_GET['id']);
            if (!isset($id) || $id == "") {
                echo "<script>alert('id不存在');$('#TB_closeWindowButton').click();</script>";
                return;
            }
            $this->assign("vo_id",$id);
            $data = $model->table('cpd_prestore')->where(array('id' => $id))->find();
            $this->assign("vo_edit", 'edit');
            $contract_id = $_GET['contract_id'];
            
            $cpd_contract="cpd_contract";
            // 获取合同数据
            $this->get_contract_data($contract_id);
            if($this->check_authority($contract_id)!=1){
                echo "<script>alert('权限不足');$('#TB_closeWindowButton').click();</script>";
                return;
            }
            
            $this->assign("contract_id", $contract_id);
            $this->assign("flexible_sys", $this->flexible_sys);
            $this->assign("data", $data);
            $this->display($this->tmp_path.'add_invoice_show');
        }
    }
    // 驳回发票
    public function delete_invoice_show() {
        //获取ID号
        $map['id'] = trim($_GET['id']);
        $map['contract_id'] = trim($_GET['contract_id']);
        $model = $this->cpd_model;
        $data = $model->table('cpd_prestore')->where(array('id' => $map['id']))->find();
        if($this->check_authority($map['contract_id'])!=1) {
            echo "<script>alert('权限不足');window.history.go(-1);</script>";
            // echo "<script>alert('权限不足');$('#TB_closeWindowButton').click();</script>";
            return;
        }
        if (!$model->table("cpd_prestore")->where($map)->save(array('invoice_status'=>0,'invoice_remark'=>'','invoice_number'=>'','invoice_sum'=>0,'invoice_tm'=>0))) {
            $this->error("驳回失败！");
        }
        $task_client = get_task_client();
        $task_client->doBackground("cpd_month_data_db", json_encode(array("contract_id" =>$data['contract_id'],'bill_month'=>date('Y-m',$data['invoice_tm'])))); 
        $this->writelog("CPD流量结算：合同-删除了id为[" . $map['id'] . "]的发票记录。【CPD流量结算】",'cpd_prestore',$map['id'],__ACTION__ ,'','del');
        // $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/CpdContractDeposit/invoices_list/contract_id/' . $map['contract_id']);
        $this->success("驳回成功！");
    }
    // 自然量详情
    public function nature_list() {
        $map=array();
        $map=$this->sync_search($map,'nature_tm');
        $model = $this->cpd_model;

        import("@.ORG.Page");
        $count = $model->table('cpd_nature a')->join('cpd_contract b on a.contract_id=b.id')->join('cpd_contract_config c on b.contact_config_id=c.id')->where($map)->order('a.add_tm desc')->count();
        $Page = new Page($count, 50);
        $lists = $model->table('cpd_nature a')->join('cpd_contract b on a.contract_id=b.id')->join('cpd_contract_config c on b.contact_config_id=c.id')->where($map)->order('a.add_tm desc')->limit($Page->firstRow . ',' . $Page->listRows)->field('a.*,b.contract_code,b.package,b.category_name,b.client_name,b.softname,c.c_name,b.flexible_sys')->select();
        foreach($lists as $k=>$v){
            //判断此条自然量是否可编辑
            $m_data=$this-> bill_is_generate(array('tm'=>$v['nature_tm'],'contract_id'=>$v['contract_id'],'bs'=>1));
            $lists[$k]['is_edite']=$m_data?1:0;
        }
        if ($_GET['export'] == 1 && $_GET['ids']) {
            $ids=explode(',', $_GET['ids']);
            $where_two['a.id']=array('in',$ids);
            $nature_list_check = $model->table('cpd_nature a')->join('cpd_contract b on a.contract_id=b.id')->join('cpd_contract_config c on b.contact_config_id=c.id')->where($where_two)->order('a.add_tm desc')->field('a.*,b.contract_code,b.package,b.category_name,b.client_name,b.softname,c.c_name')->select();
            foreach($nature_list_check as $k=>$v){
                    $contract_product=$v['softname']."\r\n".$v['package']."\r\n".$v['category_name'];
                    $nature_list_check[$k]['contract_product']="\"$contract_product\"";
                    $nature_list_check[$k]['nature_tm']=($this->flexible_sys==1)?date("Ymd",$v["nature_tm"]):date("Ym",$v["nature_tm"]);
            }
            $this->export_deposit($nature_list_check,"账户管理_自然量详情_".date('Y-m-d').".csv", 'nature');
        }
        $this->assign("lists", $lists);
        $this->assign('receipts_num', count($lists));
        $this->assign("contract_id", $contract_id);
        $this->assign("flexible_sys", $this->flexible_sys);
        
        $Page->setConfig('header', '条记录');
        $Page->setConfig('first', '<<');
        $Page->setConfig('last', '>>');
        $show = $Page->show();
        $this->assign("page", $show);
        $this->display($this->tmp_path.__FUNCTION__);
    }
    //判断此条自然量/单价是否可编辑或添加
    function bill_is_generate($data){
        $model=$this->cpd_model;
        $table_m_data='cpd_month_bill';//假数据
        $tm=date('Ym',$data['tm']);
        $where=array();
        $where['contract_id']=$data['contract_id'];
        $where['status']=array('exp'," =1 && bill_month=$tm ");
        $m_data= $model->table($table_m_data)->where($where)->find();
        //bs=1 针对自然量  bs=2 针对收款之类 bs=3 针对消耗的核减 
        if($data['bs']==1){
            return $m_data?1:0;
        }else if($data['bs']==2){
            return ($m_data['receipt_status']==2)?1:0;
        }else if($data['bs']==3){
            return ($m_data['expend_status']==2)?1:0;
        }
        
    }
    // 录入自然量
    public function add_nature_show() {
        $model=$this->cpd_model;
        if ($_POST) {
            $data['contract_id'] = trim($_POST['contract_id']);
            $data['nature_tm'] = strtotime(trim($_POST['collection_date']));
            $data['nature_num'] =trim($_POST['collection_cash']);
            foreach ($data as $d) {
                if (!isset($d) || $d == "") {
                    $this->error("数据不全，无法录入！");
                }
            }
            if (!preg_match('/^-?\d+$/', $data['nature_num'], $matches)) {
                $this->error("自然量必须是整数！");
            }
            $data['reviewer'] = $_SESSION['admin']['admin_id'];
            $data['add_tm'] = time();
            $data['update_tm'] = time();
            $data['remark'] = trim($_POST['remark']);

            $contract_d=$model->table('cpd_contract')->where(array('id'=>$data['contract_id']))->find();
            if($contract_d['start_tm']>$data['nature_tm']){
                $this->error("自然量生效日期必须大于等于合同的开始时间！");
            }
            //准备写入数据
            $where=array();
            $where['contract_id']=$data['contract_id'];
            $where['nature_tm']=array('egt',$data['nature_tm']);
            $nature=$model->table('cpd_nature')->where($where)->select();
            if($nature){
                $this->error("生效日期有交叉！");
            }
            if($this->bill_is_generate(array('tm'=>$data['nature_tm'],'contract_id'=>$data['contract_id'],'bs'=>1))){
                $this->error("生效日期账单已出，不可添加！");
            }
            //开启事物
            $result =$model->table('cpd_nature')->add($data);
            if ($result) {
                //更新相关数据表
                $model->table('cpd_expend')->where(array('contract_id'=> $data['contract_id'],'expend_tm'=>array('egt',$data['nature_tm'])))->save(array('nature_num'=>$data['nature_num']));
                $this->change_task_expend($data);
                $this->writelog("CPD流量结算：合同-新录入了ID为{$result}，contract_id为[" . $data['contract_id'] . "]的自然量。【CPD流量结算】",'cpd_nature',$result,__ACTION__ ,'','add');
                $this->success("录入自然量成功！");
            } else {
                $this->error("添加失败");
            }
            $this->redirect("nature_list");
        } else {
            $this->assign("all_softnames", $this->get_all_softnames());
            $this->assign("flexible_sys", $this->flexible_sys);
            $this->display($this->tmp_path.__FUNCTION__);
        }
    }

    //核减和自然量,以及单价变化 通用函数
    function change_task_expend($data){
        $model=$this->cpd_model;
        $where=array();
        if($data['bs']==1){
            $where['id']=$data['id'];
        }else{
            $where['status']=1;
            $where['expend_tm']=$data['nature_tm']?$data['nature_tm']:$data['price_tm'];
            $where['contract_id']=$data['contract_id'];
        }
        $expends =$model->table('cpd_expend')->where($where)->find();
        //上线取消注释
        $task_client = get_task_client();
        $task_client->doBackground("cpd_expend_db", json_encode(array("expend_id" =>$expends['id'])));   
    }   
    // 编辑自然量
    public function edit_nature_show() {
        $model=$this->cpd_model;
        if ($_POST) {
            $data['id'] = trim($_POST['id']);
            $data['contract_id'] = trim($_POST['contract_id']);
            if($this->check_authority($data['contract_id'])!=1) $this->error("权限不足");
            $data['nature_tm'] = strtotime(trim($_POST['collection_date']));
            $data['nature_num'] =trim($_POST['collection_cash']);
            foreach ($data as $d) {
                if (!isset($d) || $d == "") {
                    $this->error("数据不全，无法录入！");
                }
            }
            if (!is_numeric($data['nature_num'])) {
                $this->error("自然量必须是数字！");
            }

            $data['remark'] = trim($_POST['remark']);
            $data['update_tm'] = time();
            //准备写入数据

            $contract_d=$model->table('cpd_contract')->where(array('id'=>$data['contract_id']))->find();
            if($contract_d['start_tm']>$data['nature_tm']){
                $this->error("自然量生效日期必须大于等于合同的开始时间！");
            }

            if($this->bill_is_generate(array('tm'=>$data['nature_tm'],'contract_id'=>$data['contract_id'],'bs'=>1))){
                $this->error("生效日期账单已出，不可编辑！");
            }
            //开启事物
            $log_all_need = $this->logcheck(array('id' => $data['id']), 'cpd_nature', $data, $model);
            $re_be =$model->table('cpd_nature')->where(array('id'=>$data['id']))->find();
            $result =$model->table('cpd_nature')->where(array('id'=>$data['id']))->save($data);
            $re_be2 =$model->table('cpd_nature')->where(array('contract_id'=>$data['contract_id'],'id'=>array('gt',$data['id'])))->find();
            if ($result) {
                if($re_be2){
                    $model->table('cpd_expend')->where(array('nature_num'=> $re_be['nature_num'],'contract_id'=> $data['contract_id'],'expend_tm'=>array('exp',">={$data['nature_tm']} && expend_tm<{$re_be2['nature_tm']}")))->save(array('nature_num'=>$data['nature_num']));
                }else{
                    $model->table('cpd_expend')->where(array('nature_num'=> $re_be['nature_num'],'contract_id'=> $data['contract_id'],'expend_tm'=>array('egt',$data['nature_tm'])))->save(array('nature_num'=>$data['nature_num']));
                }
                $this->change_task_expend($data);

                $this->writelog("CPD流量结算：账户管理-编辑了自然量，id为[" . $data['id'] . "]，变化如下$log_all_need",'cpd_expend',$data['id'],__ACTION__ ,'','edit');
                $this->success("编辑成功！");
            } else {
                $this->error('编辑失败');
            }
        } else {
            $id = trim($_GET['id']);
            if (!isset($id) || $id == "") {
                echo "<script>alert('id不存在');$('#TB_closeWindowButton').click();</script>";
                return;
            }
             // 获取合同数据
            $contract_ids=explode('&', trim($_GET['contract_id']));
            $contract_id = $contract_ids[0];
            $this->get_contract_data($contract_id);
            if($this->check_authority($contract_id)!=1) {
                // echo "<script>alert('权限不足');window.history.go(-1);</script>";
                echo "<script>alert('权限不足');$('#TB_closeWindowButton').click();</script>";
                return;
            }
            $data = $model->table('cpd_nature')->where(array('id' => $id))->find();
            if($this->flexible_sys==1){
                $data['nature_tm']=date("Y-m-d",$data["nature_tm"]);
                if(strtotime(date("Y-m",strtotime($data["nature_tm"])))>=strtotime(date("Y-m",time()))){
                    $this->assign("is_show", 1);
                }else{
                    $this->assign("is_show", 0);
                }
            }else if($this->flexible_sys==2){
                $data['nature_tm']=date("Y-m",$data["nature_tm"]);
                if(strtotime($data["nature_tm"])>=strtotime(date("Y-m-d",time()))){
                    $this->assign("is_show", 1);
                }else{
                    $this->assign("is_show", 0);
                }
            }
            $this->assign("data", $data);
            $this->assign("flexible_sys", $this->flexible_sys);
            $this->display($this->tmp_path.__FUNCTION__);
        }
    }

    // 单价详情
    public function unit_price_list() {
        $map=array();
        $map=$this->sync_search($map,'price_tm');
        if($this->flexible_sys!=1){
            unset($map['b.flexible_sys']);
        }
        $model = $this->cpd_model;

        import("@.ORG.Page");
        $count = $model->table('cpd_unit_price a')->join('cpd_contract b on a.contract_id=b.id')->join('cpd_contract_config c on b.contact_config_id=c.id')->where($map)->order('a.price_tm desc')->count();
        $Page = new Page($count, 50);
        $lists = $model->table('cpd_unit_price a')->join('cpd_contract b on a.contract_id=b.id')->join('cpd_contract_config c on b.contact_config_id=c.id')->where($map)->order('a.price_tm desc')->limit($Page->firstRow . ',' . $Page->listRows)->field('a.*,b.contract_code,b.package,b.category_name,b.client_name,b.softname,c.c_name')->select();
        foreach($lists as $k=>$v){
            $m_data=$this-> bill_is_generate(array('tm'=>$v['price_tm'],'contract_id'=>$v['contract_id'],'bs'=>1));
            $lists[$k]['is_edite']=$m_data?1:0;
        }
        if ($_GET['export'] == 1 && $_GET['ids']) {
            $ids=explode(',', $_GET['ids']);
            $where_two['a.id']=array('in',$ids);
            $nature_list_check = $model->table('cpd_unit_price a')->join('cpd_contract b on a.contract_id=b.id')->join('cpd_contract_config c on b.contact_config_id=c.id')->where($where_two)->order('a.price_tm desc')->field('a.*,b.contract_code,b.package,b.category_name,b.client_name,b.softname,c.c_name')->select();
            foreach($nature_list_check as $k=>$v){
                $contract_product=$v['softname']."\r\n".$v['package']."\r\n".$v['category_name'];
                $nature_list_check[$k]['contract_product']="\"$contract_product\"";
                $nature_list_check[$k]['price_tm']=date("Y-m-d",$v["price_tm"]);
            }
            $this->export_deposit($nature_list_check,"账户管理_单价详情_".date('Y-m-d').".csv", 'unit_price');
        }
        $this->assign("lists", $lists);

        $this->assign('receipts_num', count($lists));
        $this->assign("contract_id", $contract_id);
        $this->assign("flexible_sys", $this->flexible_sys);
        
        $Page->setConfig('header', '条记录');
        $Page->setConfig('first', '<<');
        $Page->setConfig('last', '>>');
        $show = $Page->show();
        $this->assign("page", $show);
        $this->display($this->tmp_path.__FUNCTION__);
    }

    // 录入单价
    public function add_unit_price_show() {
        $model=$this->cpd_model;
        if ($_POST) {
            $data['contract_id'] = trim($_POST['contract_id']);
            $data['price_tm'] = strtotime(trim($_POST['collection_date']));
            $data['price_num'] =trim($_POST['collection_cash']);
            foreach ($data as $d) {
                if (!isset($d) || $d == "") {
                    $this->error("数据不全，无法录入！");
                }
            }
            if (!is_numeric($data['price_num'])) {
                $this->error("单价必须是数字！");
            }
            $data['reviewer'] = $_SESSION['admin']['admin_id'];
            $data['add_tm'] = time();
            $data['update_tm'] = time();
            $data['remark'] = trim($_POST['remark']);

            $contract_d=$model->table('cpd_contract')->where(array('id'=>$data['contract_id']))->find();

            $unit_price_data=$model->table('cpd_unit_price')->where(array('contract_id'=>$data['contract_id'],'status'=>1))->find();
            if($unit_price_data){
                if($contract_d['start_tm']>$data['price_tm'] || $contract_d['end_tm']<$data['price_tm']){
                    $this->error("单价生效日期必须大于等于合同的开始时间且小于等于合同的结束时间！");
                }
            }else{
                if($contract_d['start_tm']<$data['price_tm']){
                    $this->error("产品第一条单价的开始时间，必须小于等于合同的开始时间");
                }
            }
            
            //准备写入数据
            $where=array();
            $where['contract_id']=$data['contract_id'];
            $where['price_tm']=array('egt',$data['price_tm']);
            $nature=$model->table('cpd_unit_price')->where($where)->select();
            if($nature){
                $this->error("生效日期有交叉！");
            }
            if($this->bill_is_generate(array('tm'=>$data['price_tm'],'contract_id'=>$data['contract_id'],'bs'=>1))){
                $this->error("生效日期账单已出，不可添加！");
            }
            //开启事物
            $result =$model->table('cpd_unit_price')->add($data);
            if ($result) {
                //更新相关数据表
                // $be_data = $model->table('cpd_contract')->where(array('id' => $contract_id))->find();
                if($this->flexible_sys==1){
                    $expend_data=$model->table('cpd_expend')->where(array('contract_id'=> $data['contract_id'],'expend_tm'=>array('egt',$data['price_tm'])))->order('expend_tm asc')->select();
                }else{
                    $price_tm=strtotime(date('Y-m',$data['price_tm']));
                    $expend_data=$model->table('cpd_expend')->where(array('contract_id'=> $data['contract_id'],'expend_tm'=>array('egt',$price_tm)))->order('expend_tm asc')->select();
                }
                
                $expend_id='';
                if($expend_data){
                    foreach($expend_data as $k=>$v){
                        if($k==0){
                            $expend_id=$v['id'];
                        }
                        $model->table('cpd_expend')->where(array('id'=> $v['id']))->save(array('price'=>$data['price_num'],'price_before'=>$v['price']));
                    }
                    if($expend_id){
                        $this->change_task_expend(array('bs'=>1,'id'=>$expend_id));
                    }
                }
                
                $this->writelog("CPD流量结算：合同-新录入了ID为{$result}，contract_id为[" . $data['contract_id'] . "]的单价。【CPD流量结算】",'cpd_unit_price',$result,__ACTION__ ,'','add');
                $this->success("录入单价成功！");
            } else {
                $this->error("添加失败");
            }
        } else {
            $this->assign("all_softnames", $this->get_all_softnames());
            $this->display($this->tmp_path.__FUNCTION__);
        }
    }

    // 编辑单价
    public function edit_unit_price_show() {
        $model=$this->cpd_model;
        if ($_POST) {
            $data['id'] = trim($_POST['id']);
            $data['contract_id'] = trim($_POST['contract_id']);
            if($this->check_authority($data['contract_id'])!=1) $this->error("权限不足");
            $data['price_tm'] = strtotime(trim($_POST['collection_date']));
            $data['price_num'] =trim($_POST['collection_cash']);
            foreach ($data as $d) {
                if (!isset($d) || $d == "") {
                    $this->error("数据不全，无法录入！");
                }
            }
            if (!is_numeric($data['price_num'])) {
                $this->error("单价必须是数字！");
            }

            $data['remark'] = trim($_POST['remark']);
            $data['update_tm'] = time();
            //准备写入数据
            $unit_price_data=$model->table('cpd_unit_price')->where(array('contract_id'=>$data['contract_id'],'status'=>1))->order('price_tm asc')->find();
            $contract_d=$model->table('cpd_contract')->where(array('id'=>$data['contract_id']))->find();
            if($unit_price_data['id']==$data['id'] && $_POST['copy_online']!=1){
                if($contract_d['start_tm']<$data['price_tm']){
                    $this->error("产品第一条单价的开始时间，必须小于等于合同的开始时间");
                }
            }else{
                if($contract_d['start_tm']>$data['price_tm'] || $contract_d['end_tm']<$data['price_tm']){
                    $this->error("单价生效日期必须大于等于合同的开始时间且小于等于合同的结束时间！");
                }
            }
            
            //准备写入数据
            $where=array();
            $where['contract_id']=$data['contract_id'];
            $where['price_tm']=array('egt',$data['price_tm']);
            
            if($_POST['copy_online']!=1){
                $where['id']=array('neq',$data['id']);
            }
            $nature=$model->table('cpd_unit_price')->where($where)->select();
            if($nature && $_POST['copy_online']==1){
                $this->error("生效日期有交叉！");
            }
            if($this->bill_is_generate(array('tm'=>$data['price_tm'],'contract_id'=>$data['contract_id'],'bs'=>1))){
                if($_POST['copy_online']!=1){
                    $this->error("生效日期账单已出，不可编辑！");
                }else{
                    $this->error("复制上线的生效日期账单已出，不可复制上线！");
                }
                
            }
            //开启事物
            
            if($_POST['copy_online']!=1){
                $log_all_need = $this->logcheck(array('id' => $data['id']), 'cpd_unit_price', $data, $model);
                $re_be =$model->table('cpd_unit_price')->where(array('id'=>$data['id']))->find();
                $result =$model->table('cpd_unit_price')->where(array('id'=>$data['id']))->save($data);
                $re_be2 =$model->table('cpd_unit_price')->where(array('contract_id'=>$data['contract_id'],'id'=>array('gt',$data['id'])))->find();
                if ($result) {
                    $price_tm=($this->flexible_sys==1)?$data['price_tm']:strtotime(date('Y-m',$data['price_tm']));
                    $price_tm2=($this->flexible_sys==1)?$re_be2['price_tm']:strtotime(date('Y-m',$re_be2['price_tm']));
                    if($this->flexible_sys==1){
                        if($re_be2){
                            $expend_data=$model->table('cpd_expend')->where(array('price'=> $re_be['price_num'],'contract_id'=> $data['contract_id'],'expend_tm'=>array('exp',">={$price_tm} && expend_tm<{$price_tm2}")))->order('expend_tm asc')->select();
                        }else{
                            $expend_data=$model->table('cpd_expend')->where(array('price'=> $re_be['price_num'],'contract_id'=> $data['contract_id'],'expend_tm'=>array('exp',">={$price_tm}")))->order('expend_tm asc')->select();
                        }
                    }else{
                        if($re_be2){
                            if($price_tm==$price_tm2){
                                $expend_data=$model->table('cpd_expend')->where(array('contract_id'=> $data['contract_id'],'expend_tm'=>array('exp',">={$price_tm} && expend_tm<={$price_tm2}")))->order('expend_tm asc')->select();
                            }else{
                                $expend_data=$model->table('cpd_expend')->where(array('contract_id'=> $data['contract_id'],'expend_tm'=>array('exp',">={$price_tm} && expend_tm<{$price_tm2}")))->order('expend_tm asc')->select();
                            }
                            
                        }else{
                            $expend_data=$model->table('cpd_expend')->where(array('contract_id'=> $data['contract_id'],'expend_tm'=>array('exp',">={$price_tm}")))->order('expend_tm asc')->select();
                        }
                    }
                    
                    // echo "<pre>";var_dump($expend_data);die;
                    if($expend_data){
                        $expend_id='';
                        foreach($expend_data as $k=>$v){
                            if($k==0){
                                 $expend_id=$v['id'];
                            }
                            $model->table('cpd_expend')->where(array('id'=> $v['id']))->save(array('price'=>$data['price_num'],'price_before'=>$v['price']));
                        }
                        // $this->change_task_expend($data);
                        if($expend_id){
                            $this->change_task_expend(array('bs'=>1,'id'=>$expend_id));
                        }
                    }
                    
                    $this->writelog("CPD流量结算：账户管理-编辑了单价，id为[" . $data['id'] . "]，变化如下$log_all_need",'cpd_unit_price',$data['id'],__ACTION__ ,'','edit');
                    $this->success("编辑成功！");
                } else {
                    $this->error('编辑失败');
                }
            }else{
                //开启事物
                unset($data['id']);
                $result =$model->table('cpd_unit_price')->add($data);
                if ($result) {
                    //更新相关数据表
                    // $be_data = $model->table('cpd_contract')->where(array('id' => $contract_id))->find();
                    $price_tm=($this->flexible_sys==1)?$data['price_tm']:strtotime(date('Y-m',$data['price_tm']));
                    $expend_data=$model->table('cpd_expend')->where(array('contract_id'=> $data['contract_id'],'expend_tm'=>array('egt',$price_tm)))->order('expend_tm asc')->select();
                    $expend_id='';
                    if($expend_data){
                        foreach($expend_data as $k=>$v){
                            if($k==0){
                                 $expend_id=$v['id'];
                            }
                            $model->table('cpd_expend')->where(array('id'=> $v['id']))->save(array('price'=>$data['price_num'],'price_before'=>$v['price']));
                        }
                        if($expend_id){
                            $this->change_task_expend(array('bs'=>1,'id'=>$expend_id));
                        }
                    }
                    
                    
                    $this->writelog("CPD流量结算：合同-新录入了ID为{$result}，contract_id为[" . $data['contract_id'] . "]的单价。【CPD流量结算】",'cpd_unit_price',$result,__ACTION__ ,'','add');
                    $this->success("复制上线成功！");
                } else {
                    $this->error("复制上线失败");
                }
            }
            
            
            
            
            
        } else {
            $id = trim($_GET['id']);
            if (!isset($id) || $id == "") {
                echo "<script>alert('id不存在');$('#TB_closeWindowButton').click();</script>";
                return;
            }
             // 获取合同数据
            $contract_ids=explode('&', trim($_GET['contract_id']));
            $contract_id = $contract_ids[0];
            $this->get_contract_data($contract_id);
            if($this->check_authority($contract_id)!=1) {
                // echo "<script>alert('权限不足');window.history.go(-1);</script>";
                echo "<script>alert('权限不足');$('#TB_closeWindowButton').click();</script>";
                return;
            }

            $data = $model->table('cpd_unit_price')->where(array('id' => $id))->find();
            if($_GET['online']==1){
                $this->assign("copy_online", 1);
            }
            $this->assign("data", $data);
            $this->display($this->tmp_path.__FUNCTION__);
            
        }
    }
    // 其他站点自然量详情
    public function download_config_list() {  
        if($_GET['s_package'] && $s_package = trim($_GET['s_package'])){
            $map['a.package'] = array('eq', $s_package);
            $this->assign("package", $s_package);
        }
        if($_GET['s_softname'] && $s_softname = trim($_GET['s_softname'])){
            // $map['a.softname'] = array('eq', $s_softname);
            $map['a.softname'] = array('like', "%{$s_softname}%");
            $this->assign("softname", $s_softname);
        }
        if($_GET['begintime'] && $begintime = strtotime(trim($_GET['begintime']))){
            $map['a.op_tm'] = array('egt', $begintime);
            $this->assign("begintime", $_GET['begintime']);
        }
        if($_GET['endtime'] && $endtime = strtotime(trim($_GET['endtime']))){
            $map['a.op_tm'] = array('elt', $endtime);
            $this->assign("endtime", $_GET['endtime']);
        }
        if($begintime && $endtime){
            $map['a.op_tm'] = array('exp', ">=$begintime and a.op_tm<=$endtime");
        }
        $model = $this->cpd_model;
        import("@.ORG.Page");
        $str=($_GET['type']==2)?"a.add_tm asc":"a.add_tm desc";
        $this->assign("type",($_GET['type']==2)?1:2);
        $map['a.status'] = 1;
        $map['a.flexible_sys'] = $this->flexible_sys;

        import("@.ORG.Page");
        $count = $model->table('cpd_download_config a')->where($map)->order($str.',a.op_tm desc')->count();

        $Page = new Page($count, 50);
        $lists = $model->table('cpd_download_config a')->where($map)->order($str.',a.op_tm desc')->limit($Page->firstRow . ',' . $Page->listRows)->field('a.*')->select();
        foreach($lists as $k=>$v){
            if($admin_user= $model->table('sj_admin_users')->where(array('admin_user_id'=>$v['reviewer']))->find()){
                $lists[$k]['admin_user_name']=$admin_user['admin_user_name'];
            }
        }
        if ($_GET['export'] == 1 && $_GET['ids']) {
                $where_two['a.id']=array('in',explode(',', $_GET['ids']));
                $str_two=($str=="add_tm asc")?"a.add_tm desc":"a.add_tm asc";
                $nature_list_check = $model->table('cpd_download_config a')->where($where_two)->order($str_two)->field('a.*')->select();
                foreach($nature_list_check as $k=>$v){
                        $nature_list_check[$k]['op_tm']=($v['flexible_sys']==1)?date("Ymd",$v["op_tm"]):date("Ym",$v["op_tm"]);
                        $nature_list_check[$k]['add_tm']=date("Y-m-d H:i:s",$v["add_tm"]);
                        $nature_list_check[$k]['www_downloaded']=($v['www_downloaded'])?$v['www_downloaded']:'不限';
                        $nature_list_check[$k]['m_downloaded']=($v['m_downloaded'])?$v['m_downloaded']:'不限';
                        $nature_list_check[$k]['coop_downloaded']=($v['coop_downloaded'])?$v['coop_downloaded']:'不限';
                        $nature_list_check[$k]['other_downloaded']=($v['other_downloaded'])?$v['other_downloaded']:'不限';
                        $nature_list_check[$k]['finger_play_downloaded']=($v['finger_play_downloaded'])?$v['finger_play_downloaded']:'不限';
                        $nature_list_check[$k]['mm_downloaded']=($v['mm_downloaded'])?$v['mm_downloaded']:'不限';
                        $nature_list_check[$k]['market_downloaded']=($v['market_downloaded'])?$v['market_downloaded']:'不限';
                        $nature_list_check[$k]['yoo_downloaded']=($v['yoo_downloaded'])?$v['yoo_downloaded']:'不限';
                        if($admin_user_new= $model->table('sj_admin_users')->where(array('admin_user_id'=>$v['reviewer']))->find()){
                            $nature_list_check[$k]['admin_user_name']=$admin_user_new['admin_user_name'];
                        }
                }
                $this->export_deposit($nature_list_check,"账户管理_非市场客户端下载计费详情_".date('Y-m-d').".csv", 'download_config');
        }
        $this->assign("lists", $lists);
        $this->assign("flexible_sys", $this->flexible_sys);
        $this->assign('receipts_num', count($lists));        
        $Page->setConfig('header', '条记录');
        $Page->setConfig('first', '<<');
        $Page->setConfig('last', '>>');
        $show = $Page->show();
        $this->assign("page", $show);
        $this->display($this->tmp_path.__FUNCTION__);
    }
    // 录入其他站点自然量
    public function add_download_config_show() {
        $model = $this->cpd_model;
        if ($_POST) {
            $data['package'] = trim($_POST['package']);
            if(!$data['package']){
                $this->error("软件名称不存在");
            }
            $data['softname'] = $_POST['softname'];
            $data['op_tm'] = strtotime(trim($_POST['op_tm']));
            $data['reviewer'] = $_SESSION['admin']['admin_id'];
            $data['add_tm'] = time();
            $data['update_tm'] = time();
            $data['remark'] = trim($_POST['remark']);

            $data['www_downloaded'] = trim($_POST['www_downloaded']);
            $data['m_downloaded'] = trim($_POST['m_downloaded']);
            $data['coop_downloaded'] = trim($_POST['coop_downloaded']);
            $data['other_downloaded'] = trim($_POST['other_downloaded']);
            $data['finger_play_downloaded'] = trim($_POST['finger_play_downloaded']);
            $data['market_downloaded'] = trim($_POST['market_downloaded']);

            $data['yoo_downloaded'] = trim($_POST['yoo_downloaded']);
            $data['mm_downloaded'] = trim($_POST['mm_downloaded']);
            $data['flexible_sys'] = $this->flexible_sys;
            //准备写入数据
            $where=array();
            $where['package']=$data['package'];
            $where['flexible_sys']=$this->flexible_sys;
            $where['op_tm']=array('egt',$data['op_tm']);
            $now=($this->flexible_sys==1)?strtotime(date('Y-m-d')):strtotime(date('Y-m'));
            if($data['op_tm']<$now){
                $this->error("生效日期必须大于等于当前日期！");
            }
            $download_config=$model->table('cpd_download_config')->where($where)->select();
            if($download_config){
                $this->error("生效日期有交叉！");
            }
            $result =$model->table('cpd_download_config')->add($data);
            if ($result) {
                $this->writelog("CPD流量结算：账户管理-新录入了ID为{$result}，contract_id为[" . $data['contract_id'] . "]的分站点下载限额自然量。【CPD流量结算】",'cpd_download_config',$result,__ACTION__ ,'','add');
                $this->success("录入成功！");
            } else {
                $this->error("添加失败");
            }
            $this->redirect("download_config_list");
        } else {
            $pu_config="pu_config";
            // 获取合同数据
            $configname=($this->flexible_sys==1)?'cpd_different_site_down_default_num':'cpd_different_site_down_default_num_m';
            $pu_configs = $model->table($pu_config)->field('config_type,configcontent')->where(array('configname' => $configname))->select();
            $arr=array();
            foreach($pu_configs  as $k=>$v){
                $arr[$v['config_type']]=$v['configcontent'];
            }
            $this->assign("all_softnames", $this->get_all_softnames());
            $this->assign("arr",$arr);
            $this->assign("flexible_sys",$this->flexible_sys);
            $this->display($this->tmp_path.__FUNCTION__);
        }
    }
    //查看第三方站点
    public function third_party_show(){
        $data = $this->cpd_model->table('cpd_download_config')->where(array('id'=>trim($_GET['id'])))->find();
        $this->assign("data", $data);
        $this->display($this->tmp_path.__FUNCTION__);
    }
    //消耗详情
    public function expend_list() {
        $model = $this->cpd_model;
        $map=array();
        $map=$this->sync_search($map,'expend_tm');
        import("@.ORG.Page");
        $count = $model->table('cpd_expend a')->join('cpd_contract b on a.contract_id=b.id')->join('cpd_contract_config c on b.contact_config_id=c.id')->where($map)->order('a.expend_tm desc')->count();
        $Page = new Page($count, 50);
        $lists = $model->table('cpd_expend a')->join('cpd_contract b on a.contract_id=b.id')->join('cpd_contract_config c on b.contact_config_id=c.id')->where($map)->order('a.expend_tm desc')->limit($Page->firstRow . ',' . $Page->listRows)->field('a.*,b.contract_code,b.package,b.category_name,b.client_name,b.softname,c.c_name,b.flexible_sys')->select();
        foreach($lists as $k=>$v){
            $lists[$k]['effective_num']=$v['download_count']-$v['nature_num']-$v['download_invalid'];
            $lists[$k]['expend_money']=$lists[$k]['effective_num']*floatval ($lists[$k]['price']);
        }
        if ($_GET['export'] == 1 && $_GET['ids']) {
            $ids=explode(',', $_GET['ids']);
            $where_two['a.id']=array('in',$ids);
            $expend_list_check = $model->table('cpd_expend a')->join('cpd_contract b on a.contract_id=b.id')->join('cpd_contract_config c on b.contact_config_id=c.id')->where($where_two)->order('a.expend_tm desc')->field('a.*,b.contract_code,b.package,b.category_name,b.client_name,b.softname,c.c_name,b.flexible_sys')->select();
            foreach($expend_list_check as $k=>$v){
                $expend_list_check[$k]['expend_tm']=($v['flexible_sys']==1)?date("Ymd",$v["expend_tm"]):date("Ym",$v["expend_tm"]);
                $expend_list_check[$k]['effective_num']=$v['download_count']-$v['nature_num']-$v['download_invalid'];
                
                $expend_list_check[$k]['expend_money']=$expend_list_check[$k]['effective_num']*floatval ($expend_list_check[$k]['price']);
                
                $contract_product=$v['softname']."\r\n".$v['package']."\r\n".$v['category_name'];
                $expend_list_check[$k]['contract_product']="\"$contract_product\"";
                $anzhi_waitou_count="安智下载：".$v['download_count_anzhi']."\r\n"."CPD外投：".$v['download_count_waitou'];
                $expend_list_check[$k]['anzhi_waitou_count']="\"$anzhi_waitou_count\"";

                $expend_list_check[$k]['download_recharge_amount']=round($v['download_recharge']/$v['price'],2);
                $expend_list_check[$k]['download_delivery_amount']=round($v['download_delivery']/$v['price'],2);

                $expend_list_check[$k]['hejian_count_m']=$v['download_invalid']*$v['price'];
                $expend_list_check[$k]['download_count_n']=$v['download_count']-$v['nature_num'];

                $expend_list_check[$k]['download_count_n_m']=$expend_list_check[$k]['download_count_n']*$v['price'];
                $expend_list_check[$k]['download_count_n_h']=$expend_list_check[$k]['download_count_n']-$v['download_invalid'];
                $expend_list_check[$k]['download_count_n_h_m']=$expend_list_check[$k]['download_count_n_h']*$v['price'];

                $expend_list_check[$k]['expend_money']=$this->deal_number_format($expend_list_check[$k]['expend_money']);
                $expend_list_check[$k]['download_recharge']=$this->deal_number_format($expend_list_check[$k]['download_recharge']);
                $expend_list_check[$k]['download_delivery']=$this->deal_number_format($expend_list_check[$k]['download_delivery']);
                $expend_list_check[$k]['hejian_count_m']=$this->deal_number_format($expend_list_check[$k]['hejian_count_m']);
                $expend_list_check[$k]['download_count_n_m']=$this->deal_number_format($expend_list_check[$k]['download_count_n_m']);
                $expend_list_check[$k]['download_count_n_h_m']=$this->deal_number_format($expend_list_check[$k]['download_count_n_h_m']);
            }
            $this->export_deposit($expend_list_check,"账户管理_消耗详情_".date('Y-m-d').".csv", 'expend');
        }
        $this->assign("lists", $lists);
        $this->assign('expend_num', count($lists));      
        $this->assign('flexible_sys', $this->flexible_sys);      

        $Page->setConfig('header', '条记录');
        $Page->setConfig('first', '<<');
        $Page->setConfig('last', '>>');
        $show = $Page->show();
        $this->assign("page", $show);
        $this->display($this->tmp_path.__FUNCTION__);
    }
    public function check_authority($contract_id){
        $model = $this->cpd_model;
        $be_data = $model->table('cpd_contract')->where(array('id' => $contract_id))->find();
        if($be_data['flexible_c_code']){
            return 0;
        }else{
            if($be_data['flexible_sys']!=$this->flexible_sys){
                return 0;
            }else{
                return 1;
            }
        }
    }
    // 录入核减下载
    public function add_invalid_show() {
        $model = $this->cpd_model;
        if ($_POST) {
            $download_invalid = trim($_POST['download_invalid']);
            $id=$_POST['id'];
            $be_data = $model->table('cpd_expend')->where(array('id' => $id))->find();
            //校验权限不足
            if($this->check_authority($be_data['contract_id'])!=1) $this->error("权限不足");
            if (!is_int($data['download_invalid']+1-1)) {
                $this->error("核减下载量必须是整数！");
            }
            $data['reviewer'] = $_SESSION['admin']['admin_id'];
            $data['remark'] = trim($_POST['remark']);
            $data['download_invalid'] = trim($_POST['download_invalid']);
            if($this->bill_is_generate(array('tm'=>$be_data['expend_tm'],'contract_id'=>$be_data['contract_id'],'bs'=>3))){
                $this->error("生效日期账单已出，不可添加/编辑！");
            }
            $result =$model->table('cpd_expend')->where(array('id'=>$id))->save($data);
            $this->change_task_expend(array('id'=>$id,'download_invalid'=>$download_invalid,'bs'=>1));
            $this->writelog("CPD流量结算：账户管理-消耗详情id为".$_POST['id']."录入了".$_POST['download_invalid'],'cpd_expend',$_POST['id'],__ACTION__ ,'','edit');
            $this->success("录入/编辑核减下载成功！");
            $this->redirect("expend_list");
        } else {
            $ids=explode('&', trim($_GET['ids']));
            $id=$ids[0];
            $where_two['a.id']=array('in',$id);
            $expends = $model->table('cpd_expend a')->join('cpd_contract b on a.contract_id=b.id')->where($where_two)->field('a.*,b.contract_code,b.package,b.category_name,b.client_name,b.softname')->find();
            $this->assign("expends", $expends);
            $this->display($this->tmp_path.__FUNCTION__);
        }
    }
     // 分站点下载限额配置
    public function download_config_manager() {
        $model=D('');
        if ($_POST) {
            $config_type = explode(',',trim($_POST['config_type']));
            $uptime = time();
            $www_downloaded = trim($_POST['www_downloaded']);
            $m_downloaded = trim($_POST['m_downloaded']);
            $coop_downloaded = trim($_POST['coop_downloaded']);
            $other_downloaded = trim($_POST['other_downloaded']);
            $finger_play_downloaded = trim($_POST['finger_play_downloaded']);
            $market_downloaded = trim($_POST['market_downloaded']);
            $yoo_downloaded = trim($_POST['yoo_downloaded']);
            $mm_downloaded = trim($_POST['mm_downloaded']);

            $pu_config = $model->table('pu_config');
            $configname=($this->flexible_sys==1)?'cpd_different_site_down_default_num':'cpd_different_site_down_default_num_m';
            for($i=1;$i<12;$i++){
                if(in_array($i, $config_type)){
                    $pu_config_re = $model->table('pu_config')->field('configcontent')->where(array('config_type' => $i,'configname' => $configname))->find();
                    if($i==1){
                        if($www_downloaded!=$pu_config_re['configcontent']){
                            $this->download_config_manager_edit($i,$uptime,$pu_config,$www_downloaded,'www站点');
                        }
                    }else if($i==2){
                        if($m_downloaded!=$pu_config_re['configcontent']){
                            $this->download_config_manager_edit($i,$uptime,$pu_config,$m_downloaded,'m站点');
                         }
                    }else if($i==3){
                        if($coop_downloaded!=$pu_config_re['configcontent']){
                            $this->download_config_manager_edit($i,$uptime,$pu_config,$coop_downloaded,'合作站点');
                        }
                    }else if($i==4){
                        if($other_downloaded!=$pu_config_re['configcontent']){
                            $this->download_config_manager_edit($i,$uptime,$pu_config,$other_downloaded,'其他站点');
                        }
                    }else if($i==5){
                        if($finger_play_downloaded!=$pu_config_re['configcontent']){
                            $this->download_config_manager_edit($i,$uptime,$pu_config,$finger_play_downloaded,'拇指玩CPD合作');
                        }
                    }else if($i==6){
                        if($market_downloaded!=$pu_config_re['configcontent']){
                            $this->download_config_manager_edit($i,$uptime,$pu_config,$market_downloaded,'客户端');
                        }
                    }else if($i==8){
                        if($yoo_downloaded!=$pu_config_re['configcontent']){
                            $this->download_config_manager_edit($i,$uptime,$pu_config,$yoo_downloaded,'智友下载');
                        }
                    }else if($i==9){
                        if($mm_downloaded!=$pu_config_re['configcontent']){
                            $this->download_config_manager_edit($i,$uptime,$pu_config,$mm_downloaded,'移动mm');
                        }
                    }
                }else{
                    if($i==1){
                        $this->download_config_manager_add($uptime,$www_downloaded,$i,'www站点',$pu_config);
                    }else if($i==2){
                        $this->download_config_manager_add($uptime,$m_downloaded,$i,'m站点',$pu_config);
                    }else if($i==3){
                        $this->download_config_manager_add($uptime,$coop_downloaded,$i,'合作站点',$pu_config);
                    }else if($i==4){
                        $this->download_config_manager_add($uptime,$other_downloaded,$i,'其他站点',$pu_config);
                    }else if($i==5){
                        $this->download_config_manager_add($uptime,$finger_play_downloaded,$i,'拇指玩CPD合作',$pu_config);
                    }else if($i==6){
                        $this->download_config_manager_add($uptime,$market_downloaded,$i,'客户端',$pu_config);
                    }else if($i==8){
                        $this->download_config_manager_add($uptime,$yoo_downloaded,$i,'智友下载',$pu_config);
                    }else if($i==9){
                        $this->download_config_manager_add($uptime,$mm_downloaded,$i,'移动mm',$pu_config);
                    }
                    
                }
            }
            $this->success("保存成功！");
        } else { 
            $pu_config="pu_config";
            // 获取合同数据
            $configname=($this->flexible_sys==1)?'cpd_different_site_down_default_num':'cpd_different_site_down_default_num_m';
            $pu_configs = $model->table($pu_config)->field('config_type,configcontent')->where(array('configname' => $configname))->select();
            $arr=array();
            $config_type=array();
            foreach($pu_configs  as $k=>$v){
                if($v['config_type']==7){
                    continue;
                }
                $arr[$v['config_type']]=$v['configcontent'];
                $config_type[]=$v['config_type'];
            }
            $this->assign("arr", $arr);
            $this->assign("flexible_sys", $this->flexible_sys);
            $this->assign("config_type", implode ( "," ,$config_type));
            $this->display($this->tmp_path.__FUNCTION__);
        }
    }
    //导出
    public function export_deposit($lists, $filename,$category = "deposits") {
        header("Content-type:text/csv");
        header("Content-Disposition:attachment;filename=" . $filename);
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');
        $str = "";
        if (empty($lists)) {
            $str.=iconv('utf-8', 'gb2312', '没有任何信息');
        } else {
            if ($category == "nature") {
                $str = iconv('utf-8', 'gb2312', '自然量ID')  . "," . iconv('utf-8', 'gb2312', '合同编号') . "," . iconv('utf-8', 'gb2312', '合同产品'). "," . iconv('utf-8', 'gb2312', '客户名称'). "," . iconv('utf-8', 'gb2312', '协议主体') . "," . iconv('utf-8', 'gb2312', '生效日期') . "," . iconv('utf-8', 'gb2312', '自然量') . "," . iconv('utf-8', 'gb2312', '备注')  ."\r\n";
            }else if ($category == "unit_price") {
                $str = iconv('utf-8', 'gb2312', '单价ID')  . "," . iconv('utf-8', 'gb2312', '合同编号') . "," . iconv('utf-8', 'gb2312', '合同产品'). "," . iconv('utf-8', 'gb2312', '客户名称'). "," . iconv('utf-8', 'gb2312', '协议主体') . "," . iconv('utf-8', 'gb2312', '生效日期') . "," . iconv('utf-8', 'gb2312', '单价') . "," . iconv('utf-8', 'gb2312', '备注')  ."\r\n";
            } else if ($category == "invoices") {
                $str = iconv('utf-8', 'gb2312', '预存ID') . "," . iconv('utf-8', 'gb2312', '合同编号'). "," . iconv('utf-8', 'gb2312', '合同产品'). "," . iconv('utf-8', 'gb2312', '客户名称'). "," . iconv('utf-8', 'gb2312', '协议主体') . "," . iconv('utf-8', 'gb2312', '充值金额'). "," . iconv('utf-8', 'gb2312', '配送金额') . "," . iconv('utf-8', 'gb2312', '预存时间') . "," . iconv('utf-8', 'gb2312', '发票金额'). "," . iconv('utf-8', 'gb2312', '发票编号'). "," . iconv('utf-8', 'gb2312', '发票时间'). "," . iconv('utf-8', 'gb2312', '发票状态') . "," . iconv('utf-8', 'gb2312', '备注') . "\r\n";
            } else if ($category == "download_config") {
                $str = iconv('utf-8', 'gb2312', '序号') . "," . iconv('utf-8', 'gb2312', '生效日期'). "," . iconv('utf-8', 'gb2312', '产品名称'). "," . iconv('utf-8', 'gb2312', '包名'). "," . iconv('utf-8', 'gb2312', '客户端') . "," . iconv('utf-8', 'gb2312', 'www站点') . "," . iconv('utf-8', 'gb2312', 'm站点') . "," . iconv('utf-8', 'gb2312', '合作下载'). "," . iconv('utf-8', 'gb2312', '其他下载'). "," . iconv('utf-8', 'gb2312', '智友下载'). "," . iconv('utf-8', 'gb2312', '拇指玩'). "," . iconv('utf-8', 'gb2312', '移动MM'). "," . iconv('utf-8', 'gb2312', '备注') . "," . iconv('utf-8', 'gb2312', '录入时间') . "," . iconv('utf-8', 'gb2312', '操作人') . "\r\n";
            } else if ($category == "expend") {
                $str = iconv('utf-8', 'gb2312', '消耗ID') . "," . iconv('utf-8', 'gb2312', '日期'). "," . iconv('utf-8', 'gb2312', '合同编号'). "," . iconv('utf-8', 'gb2312', '任务编号') . "," . iconv('utf-8', 'gb2312', '合同产品'). "," . iconv('utf-8', 'gb2312', '客户名称') . "," . iconv('utf-8', 'gb2312', '协议主体') . "," . iconv('utf-8', 'gb2312', '下载量') . "," . iconv('utf-8', 'gb2312', '下载量(计算)') . "," . iconv('utf-8', 'gb2312', '自然量'). "," . iconv('utf-8', 'gb2312', '下载量(扣除自然量)'). "," . iconv('utf-8', 'gb2312', '单价'). "," . iconv('utf-8', 'gb2312', '消耗金额(核减前)'). "," . iconv('utf-8', 'gb2312', '核减量'). "," . iconv('utf-8', 'gb2312', '核减金额'). "," . iconv('utf-8', 'gb2312', '消耗下载量(核减后)'). "," . iconv('utf-8', 'gb2312', '消耗金额(核减后)'). "," . iconv('utf-8', 'gb2312', '消耗下载量(充值)'). "," . iconv('utf-8', 'gb2312', '消耗金额(充值)'). "," . iconv('utf-8', 'gb2312', '消耗下载量(配送)'). "," . iconv('utf-8', 'gb2312', '消耗金额(配送)') . "," . iconv('utf-8', 'gb2312', '备注') . "\r\n";
            }else if ($category == "prestores") {
                $str = iconv('utf-8', 'gb2312', '预存ID') . "," . iconv('utf-8', 'gb2312', '合同编号') . "," . iconv('utf-8', 'gb2312', '合同产品'). "," . iconv('utf-8', 'gb2312', '客户名称'). "," . iconv('utf-8', 'gb2312', '协议主体') . "," . iconv('utf-8', 'gb2312', '预存日期') . "," . iconv('utf-8', 'gb2312', '充值金额'). "," . iconv('utf-8', 'gb2312', '配送金额'). "," . iconv('utf-8', 'gb2312', '转出充值金额'). "," . iconv('utf-8', 'gb2312', '转出配送金额') . "," . iconv('utf-8', 'gb2312', '消耗充值金额'). "," . iconv('utf-8', 'gb2312', '消耗配送金额'). "," . iconv('utf-8', 'gb2312', '收款状态'). "," . iconv('utf-8', 'gb2312', '发票状态'). "," . iconv('utf-8', 'gb2312', '收款金额') . "," . iconv('utf-8', 'gb2312', '备注') . "\r\n";
            }else if ($category == "balance") {
                $str = iconv('utf-8', 'gb2312', '合同编号'). "," . iconv('utf-8', 'gb2312', '合同产品'). "," . iconv('utf-8', 'gb2312', '客户名称') . "," . iconv('utf-8', 'gb2312', '协议主体') . "," . iconv('utf-8', 'gb2312', '日期'). "," . iconv('utf-8', 'gb2312', '类型')  . "," . iconv('utf-8', 'gb2312', '充值金额'). "," . iconv('utf-8', 'gb2312', '配送金额'). "," . iconv('utf-8', 'gb2312', '备注') . "\r\n";
            }else {
                $str = iconv('utf-8', 'gb2312', '预存ID') . "," . iconv('utf-8', 'gb2312', '合同编号'). "," . iconv('utf-8', 'gb2312', '合同产品'). "," . iconv('utf-8', 'gb2312', '客户名称'). "," . iconv('utf-8', 'gb2312', '协议主体')  . "," . iconv('utf-8', 'gb2312', '充值金额'). "," . iconv('utf-8', 'gb2312', '配送金额') . "," . iconv('utf-8', 'gb2312', '预存时间'). "," . iconv('utf-8', 'gb2312', '发票金额') . "," . iconv('utf-8', 'gb2312', '发票时间'). "," . iconv('utf-8', 'gb2312', '发票编号') . "," . iconv('utf-8', 'gb2312', '收款金额'). "," . iconv('utf-8', 'gb2312', '收款时间') . "," . iconv('utf-8', 'gb2312', '发票状态') . "," . iconv('utf-8', 'gb2312', '备注'). "\r\n";            }
            foreach ($lists as $key => $val) {
                if ($category == "nature") {
                    $str.= iconv('utf-8', 'gb2312', $val['id']) . "," . iconv('utf-8', 'gb2312', $val['contract_code']). "," .iconv('utf-8', 'gb2312', $val['contract_product']). "," . iconv('utf-8', 'gb2312', $val['client_name']). "," . iconv('utf-8', 'gb2312', $val['c_name']). "," . iconv('utf-8', 'gb2312', $val['nature_tm']) . "," . iconv('utf-8', 'gb2312', $val['nature_num']) . "," . iconv('utf-8', 'gb2312', $val['remark'])  ."\r\n";
                }else if ($category == "unit_price") {
                    $str.= iconv('utf-8', 'gb2312', $val['id']) . "," . iconv('utf-8', 'gb2312', $val['contract_code']). "," .iconv('utf-8', 'gb2312', $val['contract_product']). "," . iconv('utf-8', 'gb2312', $val['client_name']). "," . iconv('utf-8', 'gb2312', $val['c_name']). "," . iconv('utf-8', 'gb2312', $val['price_tm']) . "," . iconv('utf-8', 'gb2312', $val['price_num']) . "," . iconv('utf-8', 'gb2312', $val['remark'])  ."\r\n";
                } else if ($category == "invoices") {
                    $str.= iconv('utf-8', 'gb2312', $val['id']). "," . iconv('utf-8', 'gb2312', $val['contract_code']). "," .iconv('utf-8', 'gb2312', $val['contract_product']). "," . iconv('utf-8', 'gb2312', $val['client_name']) . "," . iconv('utf-8', 'gb2312', $val['c_name']). "," . iconv('utf-8', "gb2312//TRANSLIT", $val['prestores_money']) . "," .iconv('utf-8', 'gb2312', $val['delivery_sum']). "," .iconv('utf-8', "gb2312//TRANSLIT", $val['prestores_tm']). "," . iconv('utf-8', 'gb2312', $val['invoice_sum']). "," . iconv('utf-8', 'gb2312', $val['invoice_number']). "," . iconv('utf-8', 'gb2312', $val['invoice_tm']). "," . iconv('utf-8', 'gb2312', $val['invoice_status']). "," . iconv('utf-8', 'gb2312', $val['remark']) . "\r\n";
                }else if ($category == "download_config") {
                    $str.= iconv('utf-8', 'gb2312', $val['id']) . "," . iconv('utf-8', 'gb2312', $val['op_tm']). "," . iconv('utf-8', 'gb2312', $val['softname']). "," . iconv('utf-8', 'gb2312', $val['package']). "," . iconv('utf-8', 'gb2312', $val['market_downloaded']) . "," . iconv('utf-8', 'gb2312', $val['www_downloaded']) . "," . iconv('utf-8', 'gb2312', $val['m_downloaded']). "," . iconv('utf-8', 'gb2312', $val['coop_downloaded']). "," . iconv('utf-8', 'gb2312', $val['other_downloaded']). "," . iconv('utf-8', 'gb2312', $val['yoo_downloaded']) . "," . iconv('utf-8', 'gb2312', $val['finger_play_downloaded']). "," . iconv('utf-8', 'gb2312', $val['mm_downloaded']). "," . iconv('utf-8', 'gb2312', $val['remark']) . "," . iconv('utf-8', 'gb2312', $val['add_tm']) . "," . iconv('utf-8', 'gb2312', $val['admin_user_name']) . "\r\n";
                }else if ($category == "expend") {
                    $str.= iconv('utf-8', 'gb2312', $val['id']) . "," . iconv('utf-8', 'gb2312', $val['expend_tm']) . "," . iconv('utf-8', 'gb2312', $val['contract_code']). "," . iconv('utf-8', 'gb2312', $val['task_id']) . "," . iconv('utf-8', 'gb2312', $val['contract_product']) . "," . iconv('utf-8', 'gb2312', $val['client_name']). "," . iconv('utf-8', 'gb2312', $val['c_name']). "," . iconv('utf-8', 'gb2312', $val['anzhi_waitou_count']). "," . iconv('utf-8', 'gb2312', $val['download_count']). "," . iconv('utf-8', 'gb2312', $val['nature_num']). "," . iconv('utf-8', 'gb2312', $val['download_count_n']). "," . iconv('utf-8', 'gb2312', $val['price']) . "," . iconv('utf-8', 'gb2312', $val['download_count_n_m']). "," . iconv('utf-8', 'gb2312', $val['download_invalid']). "," . iconv('utf-8', 'gb2312', $val['hejian_count_m']). "," . iconv('utf-8', 'gb2312', $val['download_count_n_h']). "," . iconv('utf-8', 'gb2312', $val['download_count_n_h_m']). "," . iconv('utf-8', 'gb2312', $val['download_recharge_amount']). "," . iconv('utf-8', 'gb2312', $val['download_recharge']). "," . iconv('utf-8', 'gb2312', $val['download_delivery_amount']). "," . iconv('utf-8', 'gb2312', $val['download_delivery'])."," . iconv('utf-8', 'gb2312', $val['remark']) . "\r\n";
                }else if ($category == "prestores") {
                    $str.= iconv('utf-8', 'gb2312', $val['id']) . "," . iconv('utf-8', 'gb2312', $val['contract_code']) . "," . iconv('utf-8', 'gb2312', $val['contract_product']) . "," . iconv('utf-8', 'gb2312', $val['client_name']). "," . iconv('utf-8', 'gb2312', $val['c_name']). "," . iconv('utf-8', 'gb2312', $val['prestores_tm']). "," . iconv('utf-8', 'gb2312//TRANSLIT', $val['recharge_sum']). "," . iconv('utf-8', 'gb2312//TRANSLIT', $val['delivery_sum']) . "," . iconv('utf-8', 'gb2312', $val['recharge_sum_zhuan']). "," . iconv('utf-8', 'gb2312', $val['delivery_sum_zhuan']). "," . iconv('utf-8', 'gb2312', $val['recharge_expend_sum']). "," . iconv('utf-8', 'gb2312', $val['delivery_expend_sum']). "," . iconv('utf-8', 'gb2312', $val['prestore_sta']). "," . iconv('utf-8', 'gb2312', $val['invoice_sta']). "," . iconv('utf-8', 'gb2312', $val['receipts_sum']) ."," . iconv('utf-8', 'gb2312', $val['remark']) . "\r\n";
                }else if ($category == "balance") {
                    $str.= iconv('utf-8', 'gb2312', $val['contract_code']) . "," . iconv('utf-8', 'gb2312', $val['contract_product']) . "," . iconv('utf-8', 'gb2312', $val['client_name']). "," . iconv('utf-8', 'gb2312', $val['c_name']). "," . iconv('utf-8', 'gb2312', $val['add_tm']). "," . iconv('utf-8', 'gb2312', $val['prestore_sta']) . "," . iconv('utf-8', 'gb2312//TRANSLIT', $val['recharge_sum']). "," . iconv('utf-8', 'gb2312//TRANSLIT', $val['delivery_sum']) . "," . iconv('utf-8', 'gb2312', $val['remark']). "\r\n";
                } else {
                    $str.= iconv('utf-8', 'gb2312', $val['id']) . "," . iconv('utf-8', 'gb2312', $val['contract_code']) . "," . iconv('utf-8', 'gb2312', $val['contract_product']) . "," . iconv('utf-8', 'gb2312', $val['client_name']). "," . iconv('utf-8', 'gb2312', $val['c_name']). "," . iconv('utf-8', 'gb2312', $val['prestores_money']) . "," . iconv('utf-8', 'gb2312', $val['delivery_sum']). "," . iconv('utf-8', 'gb2312', $val['prestores_tm']). "," . iconv('utf-8', 'gb2312//TRANSLIT', $val['invoice_sum']). "," . iconv('utf-8', 'gb2312', $val['invoice_tm']). "," . iconv('utf-8', 'gb2312', $val['invoice_num']). "," . iconv('utf-8', 'gb2312', $val['receipts_sum']). "," . iconv('utf-8', 'gb2312', $val['receipts_tm']). "," . iconv('utf-8', 'gb2312', $val['invoice_status']) . "," . iconv('utf-8', 'gb2312', $val['remark'])  . "\r\n";
                }
            }
        }
        echo $str;
        exit;
    }
    public function download_config_manager_edit($i,$uptime,$pu_config,$downloaded,$site){
        $model=D('');
        $configname=($this->flexible_sys==1)?'cpd_different_site_down_default_num':'cpd_different_site_down_default_num_m';
        $log_all_need = $this->logcheck(array('config_type' => $i,'configname'=>$configname), 'pu_config', array('uptime' => $uptime,'configcontent'=>$downloaded), $model);
        $result =$model->table('pu_config')->where(array('config_type' => $i,'configname'=>$configname))->save(array('uptime' => $uptime,'configcontent'=>$downloaded));
        if($result){
             $this->writelog("CPD流量结算：分站点下载限额配置，".$site.$log_all_need,'pu_config',$i,__ACTION__ ,'','edit');
        }else{
            $this->error('保存失败');
        } 
    }
    public function download_config_manager_add($uptime,$downloaded,$i,$site,$pu_config){
        $model=D('');
        $configname=($this->flexible_sys==1)?'cpd_different_site_down_default_num':'cpd_different_site_down_default_num_m';
        $result =$model->table('pu_config')->add(array('uptime' => $uptime,'configcontent'=>$downloaded,'config_type'=>$i,'note'=>$site,'status'=>1,'configname'=>$configname));
        if($result){
            $this->writelog("CPD流量结算：分站点下载限额配置,".$site."配置了限额".$downloaded,'pu_config',$result,__ACTION__ ,'','add');
        }else{
            $this->error('保存失败');
        }
    }
}