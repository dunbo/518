<?php
/**
 * 安智网产品管理平台 CPD流量结算控制器2---批量录入历史数据
 * ============================================================================
 * 版权所有 2009-2017 北京力天无限网络有限公司，并保留所有权利
 * author：yuesai
 * cpd
 * ----------------------------------------------------------------------------
 */
include 'CpdContractDepositAction.class.php';
class CpdContractDepositBatchAction extends CpdContractDepositAction {
    //导出失败明细
    public function out_fail_data(){
        $allist = json_decode($_POST['fail_soft'],true);
        $allist=$this->order_arr($allist,'old_order','SORT_ASC');
        $diff_source=$_POST['diff_source'];
        $this->down_csv($allist,$diff_source);
    }
    //下载csv
    function down_csv($data,$diff_source){
        header("Content-type:application/vnd.ms-excel");
        header("content-Disposition:filename=new.csv");
        if($diff_source=='unit_price'){
            $desc = "合同编号,单价,生效时间,备注,错误提示\r\n";
        }else if($diff_source=='invalid'){
            $desc = "合同编号,核减量,核减时间,备注,错误提示\r\n";
        }else if($diff_source=='nature'){
            $desc = "合同编号,自然量,生效时间,备注,错误提示\r\n";
        }else if($diff_source=='prestores'){
            $desc = "合同编号,充值金额,配送金额,预存日期,备注,错误提示\r\n";
        }else if($diff_source=='receipts'){
            $desc = "合同编号,收款金额,收款时间,关联预存ID,备注,错误提示\r\n";
        }else if($diff_source=='invoices'){
            $desc = "合同编号,发票金额,发票时间,关联预存ID,备注,错误提示\r\n";
        }
        $config_key=array(
            "unit_price"=>array('price','price_tm'),
            "invalid"=>array('download_invalid','expend_tm'),
            "nature"=>array('nature_num','nature_tm'),
            "receipts"=>array('receipts_sum','receipts_tm','id'),
            "invoices"=>array('invoice_sum','invoice_tm','id'),
            "prestores"=>array('recharge_sum','prestores_tm','delivery_sum'),
        );
        if($diff_source=='unit_price' || $diff_source=='invalid' || $diff_source=='nature'){
            foreach ($data as $v) {
                $tm = $v[$config_key[$diff_source][1]]?date('Y/m/d',$v[$config_key[$diff_source][1]]):'';
                $desc = $desc . $v['contract_code'] . ',' . $v[$config_key[$diff_source][0]] . ',' . $tm . ',' . $v['remark']. ',' . $v['error_reminder'] . "\r";
            }
        }else if($diff_source=='receipts' || $diff_source=='invoices'){
            foreach ($data as $v) {
                $tm = $v[$config_key[$diff_source][1]]?date('Y/m/d',$v[$config_key[$diff_source][1]]):'';
                $desc = $desc . $v['contract_code'] . ',' . $v[$config_key[$diff_source][0]] . ',' . $tm . ',' . $v[$config_key[$diff_source][2]]. ',' . $v['remark']. ',' . $v['error_reminder'] . "\r";
            }
        }else if($diff_source=='prestores'){
            foreach ($data as $v) {
                $tm = $v[$config_key[$diff_source][1]]?date('Y/m/d',$v[$config_key[$diff_source][1]]):'';
                $desc = $desc . $v['contract_code'] . ',' . $v[$config_key[$diff_source][0]] . ',' . $v[$config_key[$diff_source][2]]. ',' . $tm . ',' . $v['remark']. ',' . $v['error_reminder'] . "\r";
            }
        }
        //还有预存发票收款自然量的导出
        $desc = iconv('utf-8', 'gbk', $desc);
        echo $desc;
        exit(0);
    }
    //读取csv数据
    function read_csv($file) {
        $arr = array();
        $title = array();
        $handel = fopen($file, "r");
        $i = 0;
        while (($num_arr = $this->mygetcsv($handel, 1000, ",")) !== FALSE) {
            //标题行不写入 
            if ($i != 0) {
                $str=implode(',', $num_arr);
                $str=preg_replace("/,/", "", $str); 
                if($str!=''){
                    $arr[$i] = $num_arr;
                    $i++;
                }
            } else {
                $title[$i] = $num_arr;
                $i++;
            }
            
        }
        if (strlen($title[0][0]) != 8) {
            return false;
        }
        fclose($handel);
        foreach($arr as $key => $record) {
            foreach($record as $r_key => $r_value) {
                $arr[$key][$r_key] = $this->convert_encoding(trim($r_value));
            }
            $arr[$key]['old_order']=$key+1;
        }
        return $arr;
    }
    //批量导入更新白名单
    function import_general($diff_source){
        //通用配置
        $diff_source_summary=array(
            'unit_price'=>array(
                'list_url'=>'/index.php/Settlement/CpdContractDeposit/unit_price_list',
                'import_url'=>'import_unit_price',
                'itemplate_name'=>'sample12.csv',//例子
            ),
            'invalid'=>array(
                'list_url'=>'/index.php/Settlement/CpdContractDeposit/expend_list',
                'import_url'=>'import_invalid',
                'itemplate_name'=>'sample13.csv',//例子
            ),
            'nature'=>array(
                'list_url'=>'/index.php/Settlement/CpdContractDeposit/nature_list',
                'import_url'=>'import_nature',
                'itemplate_name'=>'sample14.csv',//例子
            ),
            'prestores'=>array(
                'list_url'=>'/index.php/Settlement/CpdContractDeposit/prestores_list',
                'import_url'=>'import_prestore',
                'itemplate_name'=>'sample15.csv',//例子
            ),
            'receipts'=>array(
                'list_url'=>'/index.php/Settlement/CpdContractDeposit/receives_list',
                'import_url'=>'import_receipts',
                'itemplate_name'=>'sample16.csv',//例子
            ),
            'invoices'=>array(
                'list_url'=>'/index.php/Settlement/CpdContractDeposit/invoices_list',
                'import_url'=>'import_invoice',
                'itemplate_name'=>'sample17.csv',//例子
            ),
        );
        if ($_POST) {
                $tmp_name = $_FILES['upload']['tmp_name'];

                $tmp_houzhui = $_FILES['upload']['name'];
                $tmp_arr = explode('.', $tmp_houzhui);
                $houzhui = array_pop($tmp_arr);
                if (strtoupper($houzhui) != 'CSV') {
                    echo 2;
                    exit(0);
                }

                $arr_csv = $this->read_csv($tmp_name);
                if ($arr_csv === false) {
                    echo 2;
                    exit(0);
                }else if(count($arr_csv)>200){
                    echo 3;
                    exit(0);
                }
                if($diff_source=='prestores'){
                    $arr_csv=$this->order_arr($arr_csv,'3','SORT_ASC');
                }else{
                    $arr_csv=$this->order_arr($arr_csv,'2','SORT_ASC');
                }
                $lock_name = "cpd_{$diff_source}_importing";
                $import_lock = S($lock_name);
                if ($import_lock) {
                    echo 4;
                    exit(0);
                }
                S($lock_name, 1, 60, 'File');
                if($diff_source=='unit_price'){
                    $arr = $this->import_unit_price_add($arr_csv);
                }else if($diff_source=='invalid'){
                    $arr = $this->import_add_invalid_add($arr_csv);
                }else if($diff_source=='nature'){
                    $arr = $this->import_nature_add($arr_csv);
                }else if($diff_source=='prestores'){
                    $arr = $this->import_prestore_add($arr_csv);
                }else if($diff_source=='receipts'){
                    $arr = $this->import_receipts_add($arr_csv);
                }else if($diff_source=='invoices'){
                    $arr = $this->import_invoice_add($arr_csv);
                }
                S($lock_name, NULL);            
                $this->ajaxReturn($arr, '导入成功！', 1);
        }
        $this->assign("diff_source", $diff_source);
        $this->assign("list_url", $diff_source_summary[$diff_source]['list_url']);
        $this->assign("import_url", $diff_source_summary[$diff_source]['import_url']);
        $this->assign("itemplate_name", $diff_source_summary[$diff_source]['itemplate_name']);
        $this->display($this->tmp_path.__FUNCTION__);
    }
    public function import_invalid(){
        $diff_source='invalid';
        $this->import_general($diff_source);
    }
    public function import_unit_price(){
        $diff_source='unit_price';
        $this->import_general($diff_source);
    }
    public function import_nature(){
        $diff_source='nature';
        $this->import_general($diff_source);
    }
    public function import_prestore(){
        $diff_source='prestores';
        $this->import_general($diff_source);
    }
    public function import_receipts(){
        $diff_source='receipts';
        $this->import_general($diff_source);
    }
    public function import_invoice(){
        $diff_source='invoices';
        $this->import_general($diff_source);
    }
    function order_arr($arrData,$field,$direction){
        if($field!='old_order'){
            foreach($arrData AS $uniqid => $row){  
                  $arrData[$uniqid][$field]=date('Y-m-d',strtotime($row[$field]));
            }
        }
        
        $sort = array(    
            'direction' => $direction, //排序顺序标志 SORT_DESC 降序；SORT_ASC 升序  
            'field'     => $field,       //排序字段  
        );  
        $arrSort = array();  
        foreach($arrData AS $uniqid => $row){  
            foreach($row AS $key=>$value){  
                $arrSort[$key][$uniqid] = $value;  
            }  
        } 
        if($sort['direction']){  
            array_multisort($arrSort[$sort['field']], constant($sort['direction']), $arrData);  
        }  
        return $arrData;
    }
    function update_month_bill($data){
        $model=$this->cpd_model;
        $tm=date('Ym',$data['tm']);
        $sql="update `cpd_month_bill` set {$data['key']} = {$data['key']}+{$data['key_data']} WHERE `contract_id` = {$data['contract_id']} and `bill_month`={$tm}";
        $model->query($sql);
    }
    function import_unit_price_add($arr){
        $model=$this->cpd_model;
        $price_time=$storage_data=array();
        $successcount = 0;
        $repeat_line=array();
        foreach($arr as $key=>$val){
            $arr[$key]['contract_code'] = $val[0];
            $arr[$key]['price'] = $val[1];
            $arr[$key]['price_tm'] = strtotime($val[2]);
            $arr[$key]['remark'] = trim($val[3]);
            $arr[$key]['error_reminder'] = "正确";
            
            if(in_array($arr[$key]['price_tm'], $price_time[$arr[$key]['contract_code']])){
                $arr[$key]['error_reminder'] = "此合同的单价生效日期与{$repeat_line[$arr[$key]['contract_code']][$arr[$key]['price_tm']]}行重复";
                continue;
            }else{
                $price_time[$arr[$key]['contract_code']][]=$arr[$key]['price_tm'];
                $repeat_line[$arr[$key]['contract_code']][$arr[$key]['price_tm']]=$arr[$key]['old_order'];
            }
            if(strlen($arr[$key]['remark'])>100){
                $arr[$key]['error_reminder'] = "备注100个字符以内";
                continue;
            }
            if(!preg_match('/^\d{4}(\-|\/|.)\d{1,2}\1\d{1,2}$/', $val[2], $matches)){
                $arr[$key]['error_reminder'] = "生效日期格式不正确";
                continue;
            }
            if(!is_numeric($arr[$key]['price']) || $arr[$key]['price']<=0){
                $arr[$key]['error_reminder'] = "单价必须为大于0的数字";
                continue;
            }

            $contract_d=$model->table('cpd_contract')->where(array('contract_code'=>$arr[$key]['contract_code'],'flexible_sys'=>$this->flexible_sys,'status'=>1,'start_tm'=>array('elt',time()),'end_tm'=>array('egt',time())))->find();
            if(!$contract_d){
                $arr[$key]['error_reminder'] = "合同编号不存在";
                continue;
            }else{
                $arr[$key]['contract_id']=$contract_d['id'];
            }
            $unit_price_data=$model->table('cpd_unit_price')->where(array('contract_id'=>$contract_d['id'],'status'=>1))->find();
            if($unit_price_data){
                if($contract_d['start_tm']>$arr[$key]['price_tm'] || $contract_d['end_tm']<$arr[$key]['price_tm']){
                    $arr[$key]['error_reminder'] = "单价生效日期必须大于等于合同的开始时间且小于等于合同的结束时间！";
                    continue;
                }
            }else{
                if(count($price_time[$arr[$key]['contract_code']])==1){
                    if($contract_d['start_tm']<$price_time[$arr[$key]['contract_code']][0]){
                        $arr[$key]['error_reminder'] = "产品第一条单价的开始时间，必须小于等于合同的开始时间";
                        continue;
                    }
                }
            }

            $where=array();
            $where['contract_id']=$arr[$key]['contract_id'];
            $where['price_tm']=array('egt',$arr[$key]['price_tm']);
            $nature=$model->table('cpd_unit_price')->where($where)->select();
            if($nature){
                $arr[$key]['error_reminder'] = "生效日期有交叉！";
                continue;
            }
            if($this->bill_is_generate(array('tm'=>$arr[$key]['price_tm'],'contract_id'=>$arr[$key]['contract_id'],'bs'=>1))){
                $arr[$key]['error_reminder'] = "生效日期账单已出，不可添加！";
                continue;
            }
            $storage_data[$arr[$key]['contract_id']][]=array(
                'contract_id'=>$arr[$key]['contract_id'],
                'price_num'=>$arr[$key]['price'],
                'price_tm'=>$arr[$key]['price_tm'],
                'remark'=>$val['remark'],
                'add_tm'=>time(),
                'reviewer'=>$_SESSION['admin']['admin_id'],
                );
            $successcount++;

        }
        if($failcount=count($arr)-$successcount) return array('failnum'=>$failcount,'successnum'=>$successcount,'failarr'=>  json_encode($arr));
        
        foreach($storage_data as $k=>$v){
            $count=count($v);
            $expend_id='';
            foreach($v as $kk=>$data){
                $result =$model->table('cpd_unit_price')->add($data);
                if ($result) {
                    if($this->flexible_sys==1){
                        $expend_data=$model->table('cpd_expend')->where(array('contract_id'=> $data['contract_id'],'expend_tm'=>array('egt',$data['price_tm'])))->order('expend_tm asc')->select();
                    }                    
                    if($expend_data){
                        foreach($expend_data as $kkk=>$vvv){
                            if($kkk==0 && $kk==0) $expend_id=$vvv['id'];
                            $model->table('cpd_expend')->where(array('id'=> $vvv['id']))->save(array('price'=>$data['price_num'],'price_before'=>$vvv['price']));
                        }
                        
                    }
                    ($expend_id && $kk==($count-1))&&$this->change_task_expend(array('bs'=>1,'id'=>$expend_id));
                    $this->writelog("CPD流量结算：合同-新录入了ID为{$result}，contract_id为[" . $data['contract_id'] . "]的单价。【CPD流量结算】",'cpd_unit_price',$result,__ACTION__ ,'','add');
                }
            }
            
        }

        return array('failnum'=>0,'successnum'=>$successcount,'failarr'=>'');
    }
    function import_add_invalid_add($arr){
        $model=$this->cpd_model;
        $expend_time=$storage_data=array();
        $successcount = 0;
        $repeat_line=array();
        foreach($arr as $key=>$val){
            $arr[$key]['contract_code'] = $val[0];
            $arr[$key]['download_invalid'] = $val[1];
            $arr[$key]['expend_tm'] = strtotime($val[2]);
            $arr[$key]['remark'] = trim($val[3]);
            $arr[$key]['error_reminder'] = "正确";
            if(strlen($arr[$key]['remark'])>100){
                $arr[$key]['error_reminder'] = "备注100个字符以内";
                continue;
            }
            if(in_array($arr[$key]['expend_tm'], $expend_time[$arr[$key]['contract_code']])){
                $arr[$key]['error_reminder'] = "此合同的核减时间与{$repeat_line[$arr[$key]['contract_code']][$arr[$key]['expend_tm']]}行重复";
                continue;
            }else{
                $expend_time[$arr[$key]['contract_code']][]=$arr[$key]['expend_tm'];
                $repeat_line[$arr[$key]['contract_code']][$arr[$key]['expend_tm']]=$arr[$key]['old_order'];
            }

            if(!preg_match('/^\d{4}(\-|\/|.)\d{1,2}\1\d{1,2}$/', $val[2], $matches)){
                $arr[$key]['error_reminder'] = "核减时间格式不正确";
                continue;
            }
            if(!preg_match('/^-?\d+$/', $arr[$key]['download_invalid'], $matches)){
                $arr[$key]['error_reminder'] = "核减量必须为整数";
                continue;
            }

            $contract_d=$model->table('cpd_contract')->where(array('contract_code'=>$arr[$key]['contract_code'],'flexible_sys'=>$this->flexible_sys,'status'=>1,'start_tm'=>array('elt',time()),'end_tm'=>array('egt',time())))->find();
            if(!$contract_d){
                $arr[$key]['error_reminder'] = "合同编号不存在";
                continue;
            }else{
                $arr[$key]['contract_id']=$contract_d['id'];
            }
            $be_data = $model->table('cpd_expend')->where(array('contract_id' => $arr[$key]['contract_id'],'expend_tm'=>$arr[$key]['expend_tm'],'status'=>1))->find();
            if(!$be_data){
                $arr[$key]['error_reminder'] = "核减时间的消耗不存在";
                continue;
            }else{
                $arr[$key]['id']=$be_data['id'];
            }
            if($this->bill_is_generate(array('tm'=>$arr[$key]['expend_tm'],'contract_id'=>$arr[$key]['contract_id'],'bs'=>3))){
                $arr[$key]['error_reminder'] = "生效日期账单已出，不可添加！";
                continue;
            }
            $storage_data[$arr[$key]['contract_id']][]=array(
                'contract_id'=>$arr[$key]['contract_id'],
                'download_invalid'=>$arr[$key]['download_invalid'],
                'expend_tm'=>$arr[$key]['expend_tm'],
                'id'=>$arr[$key]['id'],
                'remark'=>$arr[$key]['remark'],
                'reviewer'=>$_SESSION['admin']['admin_id'],
                );
            $successcount++;
        }

        if($failcount=count($arr)-$successcount) return array('failnum'=>$failcount,'successnum'=>$successcount,'failarr'=>  json_encode($arr));
        
        foreach($storage_data as $k=>$v){
            $count=count($v);
            $expend_id=$download_invalid='';
            foreach($v as $kk=>$data){
                $result =$model->table('cpd_expend')->save($data);
                if($kk==0){
                    $expend_id=$data['id'];
                    $download_invalid=$data['download_invalid'];
                }
                // if ($result) {
                    ($expend_id && $kk==($count-1))&&$this->change_task_expend(array('id'=>$expend_id,'download_invalid'=>$download_invalid,'bs'=>1));
                    $this->writelog("CPD流量结算：账户管理-消耗详情id为".$data['id']."录入了".$data['download_invalid'],'cpd_expend',$data['id'],__ACTION__ ,'','edit');
                // }
            }
        }

        return array('failnum'=>0,'successnum'=>$successcount,'failarr'=>'');
    }
    function import_nature_add($arr){
        $model=$this->cpd_model;
        $time_all=$storage_data=array();
        $successcount = 0;
        //行重复判断
        $repeat_line=array();
        foreach($arr as $key=>$val){
            $arr[$key]['contract_code'] = $val[0];
            $arr[$key]['nature_num'] = $val[1];
            $arr[$key]['nature_tm'] = strtotime($val[2]);
            $arr[$key]['remark'] = trim($val[3]);
            $arr[$key]['error_reminder'] = "正确";
            if(strlen($arr[$key]['remark'])>100){
                $arr[$key]['error_reminder'] = "备注100个字符以内";
                continue;
            }
            if(in_array($arr[$key]['nature_tm'], $time_all[$arr[$key]['contract_code']])){
                $arr[$key]['error_reminder'] = "此合同的自然量生效时间与{$repeat_line[$arr[$key]['contract_code']][$arr[$key]['nature_tm']]}行重复";
                continue;
            }else{
                $time_all[$arr[$key]['contract_code']][]=$arr[$key]['nature_tm'];
                $repeat_line[$arr[$key]['contract_code']][$arr[$key]['nature_tm']]=$arr[$key]['old_order'];
            }

            if(!preg_match('/^\d{4}(\-|\/|.)\d{1,2}\1\d{1,2}$/', $val[2], $matches)){
                $arr[$key]['error_reminder'] = "自然量生效时间格式不正确";
                continue;
            }
            if(!preg_match('/^\d+$/', $arr[$key]['nature_num'], $matches)){
                $arr[$key]['error_reminder'] = "自然量必须为非负整数";
                continue;
            }

            $contract_d=$model->table('cpd_contract')->where(array('contract_code'=>$arr[$key]['contract_code'],'flexible_sys'=>$this->flexible_sys,'status'=>1,'start_tm'=>array('elt',time()),'end_tm'=>array('egt',time())))->find();
            if(!$contract_d){
                $arr[$key]['error_reminder'] = "合同编号不存在";
                continue;
            }else{
                $arr[$key]['contract_id']=$contract_d['id'];
            }
            if($contract_d['start_tm']>$arr[$key]['nature_tm']){
                $arr[$key]['error_reminder'] = "自然量生效时间必须大于等于合同的开始时间！";
                continue;
            }
            $nature=$model->table('cpd_nature')->where(array('contract_id'=>$contract_d['id'],'nature_tm'=>array('egt',$arr[$key]['nature_tm'])))->select();
            if($nature){
                $arr[$key]['error_reminder'] = "生效日期有交叉！";
                continue;
            }
            if($this->bill_is_generate(array('tm'=>$arr[$key]['nature_tm'],'contract_id'=>$arr[$key]['contract_id'],'bs'=>3))){
                $arr[$key]['error_reminder'] = "生效日期账单已出，不可添加！";
                continue;
            }
            $storage_data[$arr[$key]['contract_id']][]=array(
                'contract_id'=>$arr[$key]['contract_id'],
                'nature_num'=>$arr[$key]['nature_num'],
                'nature_tm'=>$arr[$key]['nature_tm'],
                'remark'=>$arr[$key]['remark'],
                'add_tm'=>time(),
                'reviewer'=>$_SESSION['admin']['admin_id'],
                );
            $successcount++;
        }

        if($failcount=count($arr)-$successcount) return array('failnum'=>$failcount,'successnum'=>$successcount,'failarr'=>  json_encode($arr));
        
        foreach($storage_data as $k=>$v){
            $count=count($v);
            $expend_data='';
            foreach($v as $kk=>$data){
                $result =$model->table('cpd_nature')->add($data);
                if($kk==0) $expend_data=$data;
                if ($result) {
                    $model->table('cpd_expend')->where(array('contract_id'=> $data['contract_id'],'expend_tm'=>array('egt',$data['nature_tm'])))->save(array('nature_num'=>$data['nature_num']));
                    ($expend_data && $kk==($count-1))&&$this->change_task_expend($expend_data);
                    $this->writelog("CPD流量结算：合同-新录入了ID为{$result}，contract_id为[" . $data['contract_id'] . "]的自然量。【CPD流量结算】",'cpd_nature',$result,__ACTION__ ,'','add');
                }
            }
        }

        return array('failnum'=>0,'successnum'=>$successcount,'failarr'=>'');
    }
    function import_prestore_add($arr){
        $model=$this->cpd_model;
        $successcount = 0;
        $storage_data=array();

        foreach($arr as $key=>$val){
            $arr[$key]['contract_code'] = $val[0];
            $arr[$key]['recharge_sum'] = $val[1];
            $arr[$key]['delivery_sum'] = $val[2];
            $arr[$key]['prestores_tm'] = strtotime($val[3]);
            $arr[$key]['remark'] = trim($val[4]);
            $arr[$key]['error_reminder'] = "正确";
            if(strlen($arr[$key]['remark'])>100){
                $arr[$key]['error_reminder'] = "备注100个字符以内";
                continue;
            }
            if(!preg_match('/^\d{4}(\-|\/|.)\d{1,2}\1\d{1,2}$/', $val[3], $matches)){
                $arr[$key]['error_reminder'] = "预存日期格式不正确";
                continue;
            }
            if(!is_numeric($arr[$key]['recharge_sum']) || $arr[$key]['recharge_sum']<0){
                $arr[$key]['error_reminder'] = "充值金额必须大于0";
                continue;
            }
            if($arr[$key]['delivery_sum']){
                if(!is_numeric($arr[$key]['delivery_sum']) || $arr[$key]['delivery_sum']<0){
                    $arr[$key]['error_reminder'] = "配送金额如填写必须大于0";
                    continue;
                }
            }
            $contract_d=$model->table('cpd_contract')->where(array('contract_code'=>$arr[$key]['contract_code'],'flexible_sys'=>$this->flexible_sys,'status'=>1,'start_tm'=>array('elt',time()),'end_tm'=>array('egt',time())))->find();
            if(!$contract_d){
                $arr[$key]['error_reminder'] = "合同编号不存在";
                continue;
            }else{
                $arr[$key]['contract_id']=$contract_d['id'];
            }
            $prestore_expend=$model->table('cpd_prestore')->where(array('contract_id'=>$arr[$key]['contract_id'],'recharge_expend_sum'=>array('exp','>0 || delivery_expend_sum>0')))->order('prestores_tm desc')->find();
            if($prestore_expend['prestores_tm']>$arr[$key]['prestores_tm']){
                $arr[$key]['error_reminder'] = "预存日期必须必须大于最后一条（已消耗）的预存日期！";
                continue;
            }
            if($this->bill_is_generate(array('tm'=>$arr[$key]['prestores_tm'],'contract_id'=>$arr[$key]['contract_id'],'bs'=>2))){
                $arr[$key]['error_reminder'] = "本月收款审核通过，不可添加！";
                continue;
            }
            $storage_data[$val['contract_id']][]=array(
                'contract_id'=>$arr[$key]['contract_id'],
                'recharge_sum'=>$arr[$key]['recharge_sum'],
                'prestores_tm'=>$arr[$key]['prestores_tm'],
                'delivery_sum'=>$arr[$key]['delivery_sum'],
                'remark'=>$arr[$key]['remark'],
                'add_tm'=>time(),
                'reviewer'=>$_SESSION['admin']['admin_id'],
                );
            $successcount++;
        }

        if($failcount=count($arr)-$successcount) return array('failnum'=>$failcount,'successnum'=>$successcount,'failarr'=>  json_encode($arr));
        //以下处理批量录入

        $task_client = get_task_client();

        foreach($storage_data as $k=>$v){
            $count=count($v);
            $debt_money='';
            $prestores_tm='';
            foreach($v as $kk=>$data){
                $this->empty_debt($data['contract_id'],$data);
                $result = $model->table('cpd_prestore')->add($data);
                if($kk==0){
                    $debt_money=$data['recharge_expend_sum']+$data['delivery_expend_sum'];
                    $prestores_tm=$data['prestores_tm'];
                }
                if ($result) {
                    $model->table('cpd_balance_change')->add(array('contract_id'=>$data['contract_id'],'recharge_sum'=>$data['recharge_sum'],'delivery_sum'=>$data['delivery_sum'],'add_tm'=>$data['add_tm'],'remark'=>'预存使余额增加','type'=>4));
                    $this->update_month_bill(array('tm'=>$data['prestores_tm'],'contract_id'=>$data['contract_id'],'key'=>'month_prestore','key_data'=>($data['recharge_sum']+$data['delivery_sum'])));
                    $this->writelog("CPD流量结算：合同-新录入了ID为{$result}，contract_id为[" . $data['contract_id'] . "]的预存。【CPD流量结算】",'cpd_prestore',$result,__ACTION__ ,'','add');
                    if($kk==($count-1)){
                        if($debt_money){
                            $expend_data=$model->table('cpd_expend')->where(array('contract_id'=>$data['contract_id'],'status'=>1))->order('expend_tm desc')->select();
                            $debt_sum=0;
                            foreach($expend_data as $kkk=>$vvv){
                                $debt_sum+=$vvv['download_recharge']+$vvv['download_delivery'];
                                if($debt_sum>=$debt_money){
                                    $this->change_task_expend(array('id'=>$vvv['id'],'bs'=>1));
                                    $task_client->doBackground("cpd_month_data_db", json_encode(array("contract_id" =>$data['contract_id'],'bill_month'=>date('Y-m',$prestores_tm)))); 
                                    break;
                                }
                            }
                        }else{
                            $task_client->doBackground("cpd_month_data_db", json_encode(array("contract_id" =>$data['contract_id'],'bill_month'=>date('Y-m',$prestores_tm)))); 
                        }
                    }
                }
            }
            
        }
        return array('failnum'=>0,'successnum'=>$successcount,'failarr'=>'');
    }
    function import_receipts_add($arr){
        $model=$this->cpd_model;
        $expend_time=$storage_data=array();
        $successcount = 0;
        $repeat_line=array();
        foreach($arr as $key=>$val){
            $arr[$key]['contract_code'] = $val[0];
            $arr[$key]['receipts_sum'] = $val[1];
            $arr[$key]['receipts_tm'] = strtotime($val[2]);
            $arr[$key]['id'] = $val[3];
            $arr[$key]['remark'] = trim($val[4]);
            $arr[$key]['error_reminder'] = "正确";
            if(strlen($arr[$key]['remark'])>100){
                $arr[$key]['error_reminder'] = "备注100个字符以内";
                continue;
            }
            if(in_array($arr[$key]['id'], $expend_time[$arr[$key]['contract_code']])){
                $arr[$key]['error_reminder'] = "此合同的预存id与{$repeat_line[$arr[$key]['contract_code']][$arr[$key]['id']]}行重复";
                continue;
            }else{
                $expend_time[$arr[$key]['contract_code']][]=$arr[$key]['id'];
                $repeat_line[$arr[$key]['contract_code']][$arr[$key]['id']]=$arr[$key]['old_order'];
            }

            if(!preg_match('/^\d{4}(\-|\/|.)\d{1,2}\1\d{1,2}$/', $val[2], $matches)){
                $arr[$key]['error_reminder'] = "收款时间格式不正确";
                continue;
            }
            if(!is_numeric($arr[$key]['receipts_sum']) || $arr[$key]['receipts_sum']<0){
                $arr[$key]['error_reminder'] = "收款金额必须大于0";
                continue;
            }

            $contract_d=$model->table('cpd_contract')->where(array('contract_code'=>$arr[$key]['contract_code'],'flexible_sys'=>$this->flexible_sys,'status'=>1,'start_tm'=>array('elt',time()),'end_tm'=>array('egt',time())))->find();
            if(!$contract_d){
                $arr[$key]['error_reminder'] = "合同编号不存在";
                continue;
            }else{
                $arr[$key]['contract_id']=$contract_d['id'];
            }
            $be_data = $model->table('cpd_prestore')->where(array('contract_id' => $arr[$key]['contract_id'],'id'=>$arr[$key]['id'],'status'=>1))->find();
            if(!$be_data){
                $arr[$key]['error_reminder'] = "收款关联的预存id不存在";
                continue;
            }else{
                if($be_data['receipts_status']==1){
                    $arr[$key]['error_reminder'] = "此预存id已关联收款";
                    continue;
                }
            }
            if($this->bill_is_generate(array('tm'=>$arr[$key]['receipts_tm'],'contract_id'=>$arr[$key]['contract_id'],'bs'=>2))){
                $arr[$key]['error_reminder'] = "收款日期的收款审核通过，不可编辑！";
                continue;
            }
            $storage_data[$arr[$key]['contract_id']][]=array(
                'contract_id'=>$arr[$key]['contract_id'],
                'receipts_sum'=>$arr[$key]['receipts_sum'],
                'receipts_tm'=>$arr[$key]['receipts_tm'],
                'id'=>$arr[$key]['id'],
                'receipts_remark'=>$arr[$key]['remark'],
                'receipts_status'=>1,
                'update_tm'=>time(),
                'reviewer'=>$_SESSION['admin']['admin_id'],
                );
            $successcount++;
        }

        if($failcount=count($arr)-$successcount) return array('failnum'=>$failcount,'successnum'=>$successcount,'failarr'=>  json_encode($arr));
        
        $task_client = get_task_client();

        foreach($storage_data as $k=>$v){
            $count=count($v);
            $receipts_tm='';
            foreach($v as $kk=>$data){
                $receipts_id=$model->table('cpd_prestore')->where(array('id'=>$data['id']))->save($data);
                if($kk==0) $receipts_tm=$data['receipts_tm'];
                if ($receipts_id) {
                    $this->update_month_bill(array('tm'=>$data['receipts_tm'],'contract_id'=>$data['contract_id'],'key'=>'month_receipts','key_data'=>($data['receipts_sum']-0)));
                    $this->writelog("CPD流量结算：账户管理-编辑了contract_id为[" . $data['contract_id'] . "]的收款，预存id为{$data['id']},收款金额为{$data['receipts_sum']}。",'cpd_prestore',$data['id'],__ACTION__ ,'','edit');
                    ($receipts_tm && $kk==($count-1))&&$task_client->doBackground("cpd_month_data_db", json_encode(array("contract_id" =>$data['contract_id'],'bill_month'=>date('Y-m',$receipts_tm)))); 
                }
            }
            
        }

        return array('failnum'=>0,'successnum'=>$successcount,'failarr'=>'');
    }
    function import_invoice_add($arr){
        $model=$this->cpd_model;
        $expend_time=$storage_data=array();
        $successcount = 0;
        $repeat_line=array();
        foreach($arr as $key=>$val){
            $arr[$key]['contract_code'] = $val[0];
            $arr[$key]['invoice_sum'] = $val[1];
            $arr[$key]['invoice_tm'] = strtotime($val[2]);
            $arr[$key]['id'] = $val[3];
            $arr[$key]['remark'] = trim($val[4]);
            $arr[$key]['error_reminder'] = "正确";
            if(strlen($arr[$key]['remark'])>100){
                $arr[$key]['error_reminder'] = "备注100个字符以内";
                continue;
            }
            if(in_array($arr[$key]['id'], $expend_time[$arr[$key]['contract_code']])){
                $arr[$key]['error_reminder'] = "此合同的预存id与{$repeat_line[$arr[$key]['contract_code']][$arr[$key]['id']]}行重复";
                continue;
            }else{
                $expend_time[$arr[$key]['contract_code']][]=$arr[$key]['id'];
                $repeat_line[$arr[$key]['contract_code']][$arr[$key]['id']]=$arr[$key]['old_order'];
            }

            if(!preg_match('/^\d{4}(\-|\/|.)\d{1,2}\1\d{1,2}$/', $val[2], $matches)){
                $arr[$key]['error_reminder'] = "开票时间格式不正确";
                continue;
            }
            if(!is_numeric($arr[$key]['invoice_sum']) || $arr[$key]['invoice_sum']<0){
                $arr[$key]['error_reminder'] = "开票金额必须大于0";
                continue;
            }

            $contract_d=$model->table('cpd_contract')->where(array('contract_code'=>$arr[$key]['contract_code'],'flexible_sys'=>$this->flexible_sys,'status'=>1,'start_tm'=>array('elt',time()),'end_tm'=>array('egt',time())))->find();
            if(!$contract_d){
                $arr[$key]['error_reminder'] = "合同编号不存在";
                continue;
            }else{
                $arr[$key]['contract_id']=$contract_d['id'];
            }
            $be_data = $model->table('cpd_prestore')->where(array('contract_id' => $arr[$key]['contract_id'],'id'=>$arr[$key]['id'],'status'=>1))->find();
            if(!$be_data){
                $arr[$key]['error_reminder'] = "发票关联的预存id不存在";
                continue;
            }else{
                if($be_data['invoice_status']==1){
                    $arr[$key]['error_reminder'] = "此预存id已关联发票";
                    continue;
                }
            }
            if($this->bill_is_generate(array('tm'=>$arr[$key]['invoice_tm'],'contract_id'=>$arr[$key]['contract_id'],'bs'=>2))){
                $arr[$key]['error_reminder'] = "本月收款审核通过，不可编辑！";
                continue;
            }
            $storage_data[$arr[$key]['contract_id']][]=array(
                'contract_id'=>$arr[$key]['contract_id'],
                'invoice_sum'=>$arr[$key]['invoice_sum'],
                'invoice_tm'=>$arr[$key]['invoice_tm'],
                'id'=>$arr[$key]['id'],
                'invoice_remark'=>$arr[$key]['remark'],
                'invoice_status'=>1,
                'update_tm'=>time(),
                'reviewer'=>$_SESSION['admin']['admin_id'],
                );
            $successcount++;
        }

        if($failcount=count($arr)-$successcount) return array('failnum'=>$failcount,'successnum'=>$successcount,'failarr'=>  json_encode($arr));
        
        $task_client = get_task_client();

        foreach($storage_data as $k=>$v){
            $count=count($v);
            $invoice_tm='';
            foreach($v as $kk=>$data){
                $invoice_id=$model->table('cpd_prestore')->where(array('id'=>$data['id']))->save($data);
                if($kk==0) $invoice_tm=$data['invoice_tm'];
                if ($invoice_id) {
                    $this->writelog("CPD流量结算：账户管理-编辑了contract_id为[" . $data['contract_id'] . "]的发票，预存id为{$data['id']},发票金额为{$data['invoice_sum']}。",'cpd_prestore',$data['id'],__ACTION__ ,'','edit');
                    ($invoice_tm && $kk==($count-1))&&$task_client->doBackground("cpd_month_data_db", json_encode(array("contract_id" =>$data['contract_id'],'bill_month'=>date('Y-m',$invoice_tm)))); 
                }
            }
            
        }

        return array('failnum'=>0,'successnum'=>$successcount,'failarr'=>'');
    } 
}