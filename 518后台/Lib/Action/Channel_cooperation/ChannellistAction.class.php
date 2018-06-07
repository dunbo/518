<?php

class ChannellistAction extends CommonAction{


    function settle_list(){
        ini_set('memory_limit','512M');
        $model = new Model();
        $co_group_arr =  C('co_group');
        $client_model = D('Channel_cooperation.channel_cooperation');
        $charge_id = $_GET['charge_id'];
        $client_name = $_GET['client_name'];
        $chname = $_GET['chname'];
        $price = $_GET['price'];
        $status_id = $_GET['status_id'];
        $channel_attribute = $_GET['channel_attribute'];
        $start_tm = $_GET['start_tm'];
        $end_tm = $_GET['end_tm'];
        $my_category = substr($_GET['category_id'],0,-1);
        $co_group = $_GET['co_group'];
        $start_tms = date('Ym',strtotime($_GET['start_tm']));
        $end_tms = date('Ym',strtotime($_GET['end_tm']));
        $all_charge_result = $client_model -> table('co_charge') -> where(array('status' => 1)) -> select();
        
        $channel_where = array(
            'status' => 1,
        );
		//屏蔽用户开关新增	
		$my_client_id=$client_model->check_general_switch_client_id();
		if($my_client_id){
			$where_go_new .= " and client_id in({$my_client_id})";
		}
		//屏蔽用户开关新增	
        if($co_group != ''){
            $this->assign("co_group", $co_group);
            $channel_where['co_group'] =  $co_group;
        }   
        if($chname) $channel_where['chname'] = array('like',"%{$chname}%"); 
        if($my_category != ''){
            $channel_where['category_id'] = array('in',$my_category);   
            $this->assign('category_id', $_GET['category_id']);
        }   
        if($channel_attribute){
            $channel_where['attribute'] =  $channel_attribute;
        }
        if($co_group){
            $channel_where['co_group'] = $co_group;
        }
        if(isset($_GET['billing'])){
            $this->assign("billing", $_GET['billing']);
            $channel_where['billing'] =  $_GET['billing'];
        }
        $channel_result = $model -> table('sj_channel') -> where($channel_where) -> field('cid')->select();
        foreach($channel_result as $key => $val){
            $channel_cid_str .= $val['cid'].',';
        }
        $cid_str = substr($channel_cid_str,0,-1);
        if($chname || $my_category || $channel_attribute || $co_group || isset($_GET['billing'])){
            $where_client = "cid in ({$cid_str})";
            $where_my_channel = "account_attr<>4 AND cid in ({$cid_str})";
            $my_channel_result = $client_model -> table('co_channel_check') -> where($where_my_channel) -> select();
            foreach($my_channel_result as $key => $val){
                $my_month_str_go .= $val['month'].',';
            }
            $my_month_str = substr($my_month_str_go,0,-1);
            $client_channel_result = $client_model -> table('co_client_channel') -> where($where_client) -> select();
            foreach($client_channel_result as $key => $val){
                $channel_client_id_arr[] = $val['client_id'];
            }
        }
        
        if($charge_id){
            $charge_result = $client_model -> table('co_client_list') -> where(array('charge_id' => $charge_id,'status' => 1)) -> select();
            foreach($charge_result as $key => $val){
                $charge_client_str_go .= $val['id'].',';
            }
            $charge_client_str = substr($charge_client_str_go,0,-1);
            $where_go .= " and client_id in ({$charge_client_str})";
        }
        
        
        if($client_name){
            $client_where['_string'] = "client_name like '%{$client_name}%' and status != 0";
            $client_result = $client_model -> table('co_client_list') -> where($client_where) -> select();
            foreach($client_result as $key => $val){
                $client_id_arr[] = $val['id'];
            }
        }       
        if($client_name && ($chname || $my_category || $channel_attribute || $co_group || isset($_GET['billing']))){
            $client_arr = array_intersect($channel_client_id_arr,$client_id_arr);
            foreach(array_unique($client_arr) as $key => $val){
                $client_str_go .= $val.',';
            }
            $client_str = substr($client_str_go,0,-1);
            if($my_month_str){
                $where_go .= " and client_id in ({$client_str}) and month in ({$my_month_str})";
            }else{
                $where_go .= " and client_id in ({$client_str})";
            }
        }elseif($client_name && !($chname || $my_category || $channel_attribute || $co_group || isset($_GET['billing']))){
            $client_arr = $client_id_arr;
            foreach(array_unique($client_arr) as $key => $val){
                $client_str_go .= $val.',';
            }
            $client_str = substr($client_str_go,0,-1);
            $where_go .= " and client_id in ({$client_str})";
        }elseif(!$client_name && ($chname || $my_category || $channel_attribute || $co_group || isset($_GET['billing']))){
            $client_arr = $channel_client_id_arr;
            foreach(array_unique($client_arr) as $key => $val){
                $client_str_go .= $val.',';
            }
            $client_str = substr($client_str_go,0,-1);
            if($my_month_str){
                $where_go .= " and client_id in ({$client_str}) and month in ({$my_month_str})";
            }else{
                $where_go .= " and client_id in ({$client_str})";
            }
        }
        
        if($start_tm){
            $where_go .= " and month >= {$start_tms}";
        }
        if($end_tm){
            $where_go .= " and month <= {$end_tms}";
        }
        if($price){
            $where_go .= " and price = {$price}";
        }
        if($status_id){
            $this -> assign('status_id',$status_id);
            $status_id = substr($status_id,0,-1);
            $where_go .= " and status in ({$status_id})";
            $group ='month,client_id';
        }else{
            $group ='status,client_id,month';
        }
        if($cid_str){
            $where_go .= " AND cid in ({$cid_str})";
        }
		$where['_string'] = "account_attr<>4 AND cid != ''".$where_go.$where_go_new;
        $count = $client_model -> table('co_channel_check') -> where($where) -> group($group) ->  order('month DESC') -> select();
        import("@.ORG.Page");
        $param = http_build_query($_GET);
        $Page = new Page(count($count), 50, $param);
        if($_GET['from'] == 1 || $_GET['from'] == 2){
            $result = $client_model -> table('co_channel_check') -> where($where)  -> group($group) -> order('month DESC,sum(activation) DESC') -> select();
        }else{
            $result = $client_model -> table('co_channel_check') -> where($where) -> limit($Page->firstRow . ',' . $Page->listRows) -> group($group) -> order('month DESC,sum(activation) DESC') -> select();
        }
        
        if(!$_GET['p']){
            $_GET['p'] = 1;
        }
        if(!$_GET['lr']){
            $_GET['lr'] = 50;
        }
        $res = array();
        foreach($result as $key => $val){                    
            $val['num'] = 1+$key;
            
            if($status_id){
                
                $where_channel_result['_string'] = "account_attr<>4 AND month = {$val['month']} and client_id = {$val['client_id']} and status in ({$status_id})";
            }else{
                $where_channel_result['_string'] = "account_attr<>4 AND month = {$val['month']} and client_id = {$val['client_id']} and status = {$val['status']} ";
            }
            $client_charge_result = $client_model -> table('co_client_list') -> where(array('id' => $val['client_id'])) -> find();
            $charge_result = $client_model -> table('co_charge') -> where(array('id' => $client_charge_result['charge_id'])) -> find();
            $val['charge_name'] = $charge_result['charge_name'];
            $val['client_name'] = $client_charge_result['client_name'];
            if($cid_str){
                $where_channel_result['cid'] = array('in',$cid_str);
            }
            $channel_result = $client_model -> table('co_channel_check') -> where($where_channel_result) -> order('activation DESC') -> select();
            //echo $client_model->getLastSql().'<br>';exit;
            $client_result = $client_model -> table('co_client_list') -> where(array('id' => $val['client_id'])) -> select();
            $val['client_name'] = $client_result[0]['client_name'];
            $all_activation_arr = array();
            $all_settle_amount_arr = array();
            $all_salvation_arr = array();
            $all_amount_pay_arr = array();
            $all_bill_amount = array();
            $all_my_salvation_arr = array();
            $all_no_paid = array();
            $channel_paid_new = array();
            $bill_amount_new = array();
            $all_pre_amount_arr = array();
            
            $all_no_paid = 0;
            $all_activation_arr = 0;
            $all_settle_amount_arr = 0;
            $all_salvation_arr = 0;
            $all_amount_pay_arr = 0;
            $all_pre_amount_arr = 0;
            
            $bill_channel_result = $client_model -> table('co_channel_check') -> where($where_channel_result) -> group('bath_tm,client_id,month') -> order('activation DESC') -> select();
            foreach($bill_channel_result as $k => $v){
                $all_bill_amount[] = $v['bill_amount'];
                $v['bill_amount'] = number_format($v['bill_amount'],2,'.',',');
            }

            foreach($channel_result as $k => $v){
                $v['comments'] = mb_substr($v['comment'],0,4,'utf-8');
                $chname_result = $model -> table('sj_channel') -> where(array('cid' => $v['cid'])) -> select();
                $channel_category_result = $model -> table('sj_channel_category') -> where(array('category_id' => $chname_result[0]['category_id'])) -> find();
                if($chname_result[0]['category_id'] == 0 || $channel_category_result['status'] == 0){
                    $v['channel_category'] = '未分类';
                }else{
                    $v['channel_category'] = $channel_category_result['name'];
                }
                $v['co_group'] = $co_group_arr[$chname_result[0]['co_group']];
                if($chname_result[0]['attribute'] == 1){
                    $v['channel_attribute'] = '线上';
                }else{
                    $v['channel_attribute'] = '线下';
                }
                $v['chname'] = $chname_result[0]['chname'];
                $v['billing'] = $chname_result[0]['billing'];
                $all_activation_arr += $v['activation'];
                $all_pre_amount_arr += $v['pre_amount'];
                $v['no_paid'] = $v['amount_pay'] - $v['amount_paid'];
                if ($v['settle_entity'] == '2') {
                    $all_settle_amount_arr += $v['settle_amount']/count($channel_result);
                    $all_salvation_arr += $v['salvation']/count($channel_result);
                    $all_amount_pay_arr += $v['amount_pay']/count($channel_result);
                    $channel_paid_new[] = $v['amount_paid']/count($channel_result);
                    $all_no_paid += $v['no_paid']/count($channel_result);
                } else {
                    $all_settle_amount_arr += $v['settle_amount'];
                    $all_salvation_arr += $v['salvation'];
                    $all_amount_pay_arr += $v['amount_pay'];
                    $all_no_paid += $v['no_paid'];
                    //@TODO 逻辑？？？
                    if($v['settle_group']==0){
                        $channel_paid_new[$v['settle_group']] = $v['amount_paid'];
                    }else{
                        $channel_paid_new[] = $v['amount_paid'];
                    }
                }
                if($v['status'] == 3){
                    //$all_no_paid += $v['amount_pay'];
                }
                
                
                if($v['status'] == 2){
                    if ($v['settle_entity'] == '2') {
                        $bill_amount_new[] = $v['bill_amount']/count($channel_result);
                    } else {
                        $bill_amount_new[$v['settle_group']] = $v['bill_amount'];
                    }
                    $v['my_salvation'] = $v['amount_pay'] - $v['amount_paid'];
                }else{
                    $v['my_salvation'] = 0;
                }
                if($_GET['from'] == 2){
                    $res[$v['month']]['billing'][] = $v['billing'];
                    $res[$v['month']]['pre_amount'] += $v['pre_amount'];
                    $res[$v['month']]['activation'] += $v['activation'];
                    $res[$v['month']]['amount_pay'] += $v['amount_pay'];
                    if(in_array($v['status'],array(2,3,6))){                        
                        $res[$v['month']]['actual_pay'] += $v['amount_pay'];
                    }
                    $res[$v['month']]['amount_paid'] += $v['amount_paid'];
                    $res[$v['month']]['no_paid'] += $v['amount_pay'] - $v['amount_paid'];
                }
    
                //$v['my_salvation'] = $v['amount_pay'] - $v['amount_paid'];
                $v['settle_amount'] = number_format($v['settle_amount'],2,'.',',');
                $v['my_salvation'] = number_format($v['my_salvation'],2,'.',',');
                $v['amount_paid'] = number_format($v['amount_paid'],2,'.',',');
                $v['amount_pay'] = number_format($v['amount_pay'],2,'.',',');
                $v['activation'] = number_format($v['activation'],0,'.',',');
                $v['average'] = number_format($v['average'],0,'.',',');
                $v['no_paid'] = number_format($v['no_paid'],2,'.',',');
                
                //最低结算值
                $min_result = $model -> table('pu_config') -> where(array('config_type' => 'saveminpay','status' => 1)) -> select();
                if($v['settle_amount'] < $min_result[0]['configcontent']){
                    $v['warning'] = 1;
                }else{
                    $v['warning'] = 0;
                }
                $channel_result[$k] = $v;
                
                
            }
            $the_no_paid_arr += $all_no_paid;
            $the_activation_arr += $all_activation_arr;
            $the_settle_amount_arr += $all_settle_amount_arr;
            $the_salvation_arr += $all_salvation_arr;
            $the_amount_pay_arr += $all_amount_pay_arr;
            $the_pre_amount_arr += $all_pre_amount_arr;
            //var_dump($bill_amount_new);
            $val['bill_amount'] = array_sum($bill_amount_new);
            if($val['status'] == 2){
                $channel_amount_paid_sum = array_sum($channel_paid_new);
                $val['my_salvation'] = $all_amount_pay_arr -$channel_amount_paid_sum;
                $val['no_paid'] = $all_amount_pay_arr - $channel_amount_paid_sum - $val['my_salvation'];
                $val['no_paid'] = number_format($val['no_paid'],2,'.',',');
            }else{
                $channel_amount_paid_sum = 0;
                $val['my_salvation'] = 0;
                $val['no_paid'] = $all_no_paid;
                $val['no_paid'] = number_format($val['no_paid'],2,'.',',');
            }
            //var_dump($val['bill_amount']);
            $all_my_salvation += $val['my_salvation'];
            $val['my_salvation'] = number_format($val['my_salvation'],2,'.',',');
            $all_bill_amount_arr += $val['bill_amount'];
            $all_amount_paid_arr += $channel_amount_paid_sum;
            $val['amount_paid'] = number_format($channel_amount_paid_sum,2,'.',',');
            //$all_no_paid = $val['no_paid'];
            if($all_no_paid>0){
                $val['no_paid'] = $all_no_paid;
            }           
            $all_amount_pay = $all_amount_pay_arr;
            $val['all_no_paid'] = number_format($all_no_paid,2,'.',',');
            $val['all_amount_pay'] = number_format($all_amount_pay,2,'.',',');
            
            //@TODO 没问题？？？
            $val['all_my_salvation'] = number_format($all_my_salvation,2,'.',',');
            $val['cid_result'] = $channel_result;
            $result[$key] = $val;
        }
        $all_activation = number_format($the_activation_arr,0,'.',',');
        $all_settle_amount = number_format($the_settle_amount_arr,2,'.',',');
        $all_salvation = number_format($the_salvation_arr,2,'.',',');
        $all_amount_pay = number_format($the_amount_pay_arr,2,'.',',');
        $the_my_salvation = number_format($all_my_salvation,2,'.',',');
        $the_bill_amount = number_format($all_bill_amount_arr,2,'.',',');
        $all_pre_amount = number_format($the_pre_amount_arr,0,'.',',');
        $the_activation = number_format($the_activation_arr,0,'.',',');
        $the_settle_amount = number_format($the_settle_amount_arr,2,'.',',');
        $the_salvation = number_format($the_salvation_arr,2,'.',',');
        $the_amount_pay = number_format($the_amount_pay_arr,2,'.',',');
        $the_amount_paid = number_format($all_amount_paid_arr,2,'.',',');
        $the_no_paid = number_format($the_no_paid_arr,2,'.',',');
        $Page->setConfig('header', '篇记录');
        $Page->setConfig('first', '<<');
        $Page->setConfig('last', '>>');
        $show = $Page->show();
        $this->assign("page", $show);
        $this -> assign('p',$_GET['p']);
        $this -> assign('lr',$_GET['lr']);
        $this -> assign('all_pre_amount',$all_pre_amount);
        $this -> assign('all_charge_result',$all_charge_result);
        $this -> assign('all_channel_category_result',$all_channel_category_result);
        $this -> assign('the_bill_amount',$the_bill_amount);
        $this -> assign('the_my_salvation',$the_my_salvation);
        $this -> assign('the_activation',$the_activation);
        $this -> assign('the_settle_amount',$the_settle_amount);
        $this -> assign('the_salvation',$the_salvation);
        $this -> assign('the_amount_pay',$the_amount_pay);
        $this -> assign('the_amount_paid',$the_amount_paid);
        $this -> assign('the_no_paid',$the_no_paid);
        $this -> assign('charge_id',$charge_id);
        $this -> assign('client_name',$client_name);
        $this -> assign('chname',$chname);
        $this -> assign('price',$price);    
        $this -> assign('channel_category',$channel_category);
        $this -> assign('channel_attribute',$channel_attribute);
        $this -> assign('start_tm',$start_tm);
        $this -> assign('end_tm',$end_tm);
        $this -> assign('result',$result);
        $this->assign("co_group_arr",$co_group_arr );
        if($_GET['from'] == 1){
            foreach($result as $key => $val){
                foreach($val['cid_result'] as $k => $v){
                    if($v['update_tm']){
                        $my_time = date('Y-m-d H:i:s',$v['update_tm']);
                    }
                    $v['salvation'] = number_format($v['salvation'],2,'.',',');
                    if($v['status'] == 0){ $tstatus = '已冻结';}elseif($v['status'] == 2){$tstatus='已付款';}elseif($v['status'] == 3){$tstatus='待付款';}elseif($v['status'] == 1){$tstatus='待确认';}elseif($v['status'] == 4){$tstatus='财务驳回';}elseif($v['status'] == 6){$tstatus='待审核';}
                    $billing = $v['billing'] == 1 ? '激活' : '预装';
                    $file_str .= $val['num'].','.$val['month'].','.$val['charge_name'].','.$val['client_name'].','.$v['chname'].','.$v['channel_attribute'].','.$v['channel_category'].','.$v['co_group'].','.$billing.','.$v['pre_amount'].',"'.$v['activation'].'","'.$v['average'].'","'.$v['price'].'","'.$v['settle_amount'].'","'.$v['salvation'].'","'.$val['cid_result'][0]['taxt'].'"%,"'.$v['amount_pay'].'","'.$v['amount_paid'].'","'.$v['my_salvation'].'","'.$v['no_paid'].'","'.$v['bill_amount'].'",'.$my_time.",{$tstatus}"."\n";
                }
            }
            
            $file_gos = '渠道结算报表_'.date('Ymd').".csv";//文件名
            if(strpos($_SERVER["HTTP_USER_AGENT"],"MSIE")){  
                $file_go = urlencode($file_gos); 
            }else{
                $file_go = $file_gos;
            }
            header( "Cache-Control: public" );
            header( "Pragma: public" );
            header("Content-type:application/vnd.ms-excel");
            header('Content-Disposition:attachment;filename='.$file_go);
            header('Content-Type:APPLICATION/OCTET-STREAM');
            ob_start();
            $header_str =  iconv("UTF-8",'GBK',"序号,月份,负责人,客户名称,渠道名称,渠道属性,渠道类型,渠道分类,结算方式,预装量,激活量,日均激活量,单价,结算金额,补差,税率,应付金额,已付金额,差额补齐,未付金额,发票金额,操作时间,状态");
            $file_str_go=  iconv("UTF-8",'GBK',$file_str);
            echo $header_str."\n";
            echo $file_str_go;
            echo iconv("UTF-8",'GBK','总计,-,,-,,-,,,,'.$all_pre_amount.',"'.$the_activation.'",,,"'.$the_settle_amount.'","'.$the_salvation.'",,"'.$the_amount_pay.'","'.$the_amount_paid.'","'.$the_my_salvation.'","'.$the_no_paid.'","'.$the_bill_amount.'"');
            ob_end_flush();
            exit;
        
        }elseif($_GET['from'] == 2){
            $max_m = ''; $min_m = '';
            foreach($res as $key => $val){
                $billing = '';
                if($key>$max_m)  $max_m = $key;                     
                if($key<$min_m || $min_m == '' ) $min_m = $key;
                if(in_array(1,$val['billing']) && in_array(2,$val['billing'])){
                    $billing = '';
                }elseif(in_array(1,$val['billing'])){
                    $billing = '激活';
                }elseif(in_array(2,$val['billing'])){
                    $billing = '预装';
                }
                $val['no_paid'] = number_format($val['no_paid'] , 2 ,'.',',');
                $val['amount_paid'] = number_format($val['amount_paid'] , 2 ,'.',',');
                $val['actual_pay'] = number_format($val['actual_pay'] , 2 ,'.',',');
                $val['amount_pay'] = number_format($val['amount_pay'] , 2 ,'.',',');
                $file_str .= $key.','.$billing.','.$val['pre_amount'].','.$val['activation'].',"'.$val['amount_pay'].'","'.$val['actual_pay'].'","'.$val['amount_paid'].'","'.$val['no_paid'].'"'."\n";
                
            }
            if($min_m == $max_m) $name = $max_m;
            else $name = $min_m.'--'.$max_m;
                
            $file_gos = '渠道结算汇总报表_'.$name.".csv";//文件名
            if(strpos($_SERVER["HTTP_USER_AGENT"],"MSIE")){
                $file_go = urlencode($file_gos);
            }else{
                $file_go = $file_gos;
            }
            header( "Cache-Control: public" );
            header( "Pragma: public" );
            header("Content-type:application/vnd.ms-excel");
            header('Content-Disposition:attachment;filename='.$file_go);
            header('Content-Type:APPLICATION/OCTET-STREAM');
            ob_start();
            $header_str =  iconv("UTF-8",'GBK',"月份,结算方式,预装量,激活量,应付金额,实付金额,已付金额,未付金额");
            $file_str_go=  iconv("UTF-8",'GBK',$file_str);
            echo iconv("UTF-8",'GBK','渠道结算汇总报表_'.$name."\n");;
            echo $header_str."\n";
            echo $file_str_go;
            ob_end_flush();
            exit;
        }       
        $this -> display();
    
    }
    function pub_channel_settlement_status(){
        $settlement_status = array(
            '1' => '待确认',
            '2' => '已付款',
            '3' => '待付款',
            '4' => '财务驳回',
            '6' => '待审核',
            '0' => '冻结',
        );
        if($_GET['status_id']){
            $this -> assign('status_id_arr',explode(',',substr($_GET['status_id'],0,-1)));
        }   
        $this -> assign('settlement_status',$settlement_status);
        $this->display('channel_settlement_status');
    }
}