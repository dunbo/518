<?php
/**
 * Desc:   小卖店活动
 * @author Sun Tao<suntao@anzhi.com>
 *
 */
class StoreAction extends CommonAction {

    function index(){
        $model = D('sendNum.Store');

        $res = $model->getconfig();

        $this->assign("res" , $res);

        $this->display('index');
    }

    //保存配置
    function save_config() {
	$path = UPLOAD_PATH . '/data3' . '/store/' . date('Ym/d') . '/';
        if ($this->isPost()) {
            set_time_limit(300);
            $explain = $_POST['explain'];

            $this->createFolder($path);

            if($_FILES['upload']['size'][0]>204800||$_FILES['upload']['size'][1]>204800){
                echo 6;exit(0);
            }
            $tmp_houzhui = $_FILES['upload']['name'][1];
            $filetype1 = $_FILES['upload']['type'][1];
            if (strlen($tmp_houzhui) > 0) {
                $tmp_arr = explode('.', $tmp_houzhui);
                $houzhui = array_pop($tmp_arr);
                if ($filetype1 != 'image/jpeg' && $filetype1!= 'image/png') { 
                    echo 4;
                    exit(0);
                }
                $imgres = getimagesize($_FILES['upload']['tmp_name'][1]); // 宽高
                if($imgres[0]!=640||$imgres[1]!=270){
                    echo 5;
                    exit(0);
                }
                $buy_path = $path .'buy'.time(). '.' . $houzhui;
                $is_succ_zs = move_uploaded_file($_FILES['upload']['tmp_name'][1], $buy_path);
		$buy_path = str_replace(UPLOAD_PATH, '', $buy_path);  
            }

            if ($_FILES['upload']['name'][0] != "") {
                $filetype2 = $_FILES['upload']['type'][0];
                $tmp_houzhui2 = $_FILES['upload']['name'][0];
                $tmp_arr2 = explode('.', $tmp_houzhui2);
                $houzhui2 = array_pop($tmp_arr2);

                if ($filetype2 != 'image/jpeg' && $filetype2!= 'image/png') { 
                    echo 4;
                    exit(0);
                }
                $imgres = getimagesize($_FILES['upload']['tmp_name'][0]); // 宽高
                if($imgres[0]!=640||$imgres[1]!=270){
                    echo 5;
                    exit(0);
                }

                $sale_path = $path .'sale'.time(). '.' . $houzhui2;

                $is_succ_apk = move_uploaded_file($_FILES['upload']['tmp_name'][0], $sale_path);
		$sale_path = str_replace(UPLOAD_PATH, '', $sale_path);  
                if (empty($is_succ_apk)) {
                    echo 3;
                    exit(0);
                }
            }

            $model = D('sendNum.Store');
            $model->save_img($explain,$buy_path,$sale_path);
            if(!empty($buy_path)&&!empty($sale_path)){
                $this->writelog('修改了活动配置,活动说明为'.$explain.',买家图片为'.$buy_path.',卖家图片为'.$sale_path, 'sj_store_config','',__ACTION__ ,'','edit');
            }else if(!empty($buy_path)&&empty($sale_path)){
                $this->writelog('修改了活动配置,活动说明为'.$explain.',买家图片为'.$buy_path, 'sj_store_config','',__ACTION__ ,'','edit');
            }else if(empty($buy_path)&&!empty($sale_path)){
                $this->writelog('修改了活动配置,活动说明为'.$explain.',卖家图片为'.$sale_path, 'sj_store_config','',__ACTION__ ,'','edit');
            }else{
                $this->writelog('修改了活动配置,活动说明为'.$explain, 'sj_store_config','',__ACTION__ ,'','edit');
            }
            echo 1;exit(0);
        }
    }

    /**
     * 创建目录
     */
    private function createFolder($path) {
        if (!file_exists($path)) {
            $this->createFolder(dirname($path));
            mkdir($path, 0777);
        }
    }


    //兑换查询
    function orderlist()
    {
        $model = D('sendNum.Store');
        if($_GET['is_sub']==1){
            $res = $model->getorderlist($_GET);
        }
        $city = $model->getcity();

        if(isset($_GET['down'])){
            $ua = $_SERVER["HTTP_USER_AGENT"]; 
            $filename = date('Y-m-d H-i');
            $filename = "小卖店_兑换查询_".$filename.".csv";
            $res = $model->getorderlist($_GET,2);
            $allist = $res['list'];
            header("Content-type:application/vnd.ms-excel");

            if (preg_match("/MSIE/", $ua)) {
                header('Content-Disposition: attachment; filename="' . $encoded_filename . '"');
            } else if (preg_match("/Firefox/", $ua)) {
                header('Content-Disposition: attachment; filename*="utf8\'\'' . $filename . '"');
            } else {
                header('Content-Disposition: attachment; filename="' . $filename . '"');
            }

            $desc ="订单号,手机号,IMEI,学校,店铺名称,代金券(元),结算金额(元),领取时间,状态,兑换时间\r\n";
            foreach($allist as $key=>$v)
            {
                if($v['status']==1){
                    $status = '未消费';
                }
                if($v['status']==2){
                    $status = '未结算';
                }
                if($v['status']==3){
                    $status = '已结算';
                }
                $receive_tm = empty($v['receive_tm'])?'':date("Y-m-d H:i:s",$v['receive_tm']);

                $exchange_tm = empty($v['exchange_tm'])?'':date("Y-m-d H:i:s",$v['exchange_tm']);

                $desc = $desc.$v['id'].','.$v['tel'].','.$v['imei'].',"'.$v['school'].'",'.$v['store_name'].',5,10,'.$receive_tm.','.$status.','.$exchange_tm."\r";
            }
            $desc = iconv('utf-8','gbk',$desc);
            echo $desc;
            exit(0);
        }
        $this->assign("get" , $_GET);
        $this->assign("city" , $city);
        $this->assign("select_city" , $_GET['city']);

        $new_data = $res['list'];

        $this->assign("list" , $new_data);
        $this->assign("page" , $res['page']);
        $this->assign("count" , $res['count']);
        $this->display();
    }


    //商户管理
    function salerlist()
    {
        $model = D('sendNum.Store');
        $res = $model->getsalerlist($_GET);
        $city = $model->getcity();

        if(isset($_GET['down'])){
            $ua = $_SERVER["HTTP_USER_AGENT"]; 
            $filename = date('Y-m-d H-i');
            $filename = "小卖店_商户管理_".$filename.".csv";
            $res = $model->getsalerlist($_GET,2);
            $allist = $res['list'];
            foreach($allist as $k=>$v)
            {
                $yjs = $model->get_yjs_count($v['id']);
                $wjs = $model->get_wjs_count($v['id']);
                $allist[$k]['yjs']=$model->doFormatMoney($yjs*10);
                $allist[$k]['wjs']=$model->doFormatMoney($wjs*10);;
                $allist[$k]['yes_wjs']=$model->doFormatMoney($model->get_yeswjs_count($v['id'])*10);
            }
            header("Content-type:application/vnd.ms-excel");

            if (preg_match("/MSIE/", $ua)) {
                header('Content-Disposition: attachment; filename="' . $encoded_filename . '"');
            } else if (preg_match("/Firefox/", $ua)) {
                header('Content-Disposition: attachment; filename*="utf8\'\'' . $filename . '"');
            } else {
                header('Content-Disposition: attachment; filename="' . $filename . '"');
            }

            $desc ="序号,城市,学校,店铺名称,店主,支付宝账户,推荐人,已结算(元),昨日未结算(元),全部未结算(元),创建时间\r\n";
            foreach($allist as $key=>$v){
                $create_tm = empty($v['create_tm'])?'':date("Y-m-d H:i:s",$v['create_tm']);

                $desc = $desc.$v['id'].','.$v['city'].','.$v['school'].',"'.$v['store_name'].'","'.$v['shopkeeper'].'",'.$v['alipay'].','.$v['recommend'].',"'.$v['yjs'].'","'.$v['yes_wjs'].'","'.$v['wjs'].'",'.$create_tm."\r";
            }
            $desc = iconv('utf-8','gbk',$desc);
            echo $desc;
            exit(0);
        }
        $this->assign("get" , $_GET);
        $this->assign("city" , $city);
        $this->assign("select_city" , $_GET['city']);

        $new_data = $res['list'];

        foreach($new_data as $k=>$v)
        {
            $yjs = $model->get_yjs_count($v['id']);
            $wjs = $model->get_wjs_count($v['id']);
            $new_data[$k]['yjs']=$model->doFormatMoney($yjs*10);
            $new_data[$k]['wjs']=$model->doFormatMoney($wjs*10);;
            $new_data[$k]['yes_wjs']=$model->doFormatMoney($model->get_yeswjs_count($v['id'])*10);
            if($yjs==0&&$wjs==0){
                $new_data[$k]['is_del']=1;
            }else{
                $new_data[$k]['is_del']=2;
            }
        }

        $yes_begin = date('Y-m-d',strtotime("-1 days")).' 00:00:00';
        $yes_end = date('Y-m-d',strtotime("-1 days")).' 23:59:59';
        $this->assign("yes_begin" , $yes_begin);
        $this->assign("yes_end" , $yes_end);
        $this->assign("list" , $new_data);
        $this->assign("page" , $res['page']);
        $this->assign("count" , $res['count']);
        $this->display();
    }

    //添加商户
    function add_saler()
    {
        $model = D('sendNum.Store');
        $city = $model->getcity();
        $info= $model->getsalerinfo($_GET['id']);
        $recommend = $model->getrecommend();

        $this->assign("select_city" , $info['city']);
        $this->assign("select_school" , $info['school']);
        $this->assign("select_recommend" , $info['recommend']);

        $this->assign("recommend" , $recommend);
        $this->assign("info" , $info);
        $this->assign("city" , $city);

        $new_data = $res['list'];
        if($_POST){
            if($_POST['selectcity']==1){
                $city = $model->getschool($_POST['city']);
                 $this->ajaxReturn($city,"获取成功！",1);
            }else{
                //提交保存
                if(empty($_POST['saler_id'])){
                    $rs = $model->is_exist_user($_POST['username']);
                    $ret = $model->is_exist_tel($_POST['tel']);
                    if(!empty($rs)){
                        echo 2;exit(0);
                    }
                    if(!empty($ret)){
                        echo 3;exit(0);
                    }
                    $reet = $model->addsaler($_POST);
                    $this->writelog('安智小卖店-商家管理:新增了商家,id为'.$reet, 'sj_store_config',$reet,__ACTION__ ,'','add');
                }else{
                    $rs = $model->is_exist_user($_POST['username'],$_POST['saler_id']);
                    $ret = $model->is_exist_tel($_POST['tel'],$_POST['saler_id']);
                    if(!empty($rs)){
                        echo 2;exit(0);
                    }
                    if(!empty($ret)){
                        echo 3;exit(0);
                    }

                    $logpost = $_POST;
                    unset($logpost['saler_id']);
                    unset($logpost['school_val']);
                    unset($logpost['city_val']);
                    $logpost['update_tm'] = time();
                    $where_arr = array('id' => $_POST['saler_id']);
                    if(empty($logpost['password'])){
                        unset($logpost['password']);
                    }else{
                        $logpost['password'] = md5($logpost['password']);
                    }
                    $log_all_need = $this->logcheck($where_arr, 'store_user', $logpost, $model);
                    $msg = "安智小卖店-商家管理：编辑了id为{$_POST['saler_id']}的记录：";
                    $msg .= $log_all_need;
                    $this->writelog($msg, 'store_user',$_POST['saler_id'],__ACTION__ ,'','edit');
                    $model->editsaler($_POST);
                }
                echo 1;exit(0);
            }
        }
        $this->display();
    }


    //结算页面
    function orderinfo()
    {
        $yesterday_begin = empty($_GET['add_begintime'])?date('Y-m-d',strtotime("-1 days")).' 00:00:00':$_GET['add_begintime'];
        $yesterday_end= empty($_GET['add_endtime'])?date('Y-m-d',strtotime("-1 days")).' 23:59:59':$_GET['add_endtime'];
        //$yesterday_end= date('Y-m-d',strtotime("-1 days")).' 23:59:59';

        $id = $_GET['id'];
        $model = D('sendNum.Store');
        $info= $model->getsalerinfo($_GET['id']);
        $_GET['status']=2;
        $_GET['add_begintime']=$yesterday_begin;
        $_GET['add_endtime']=$yesterday_end;
        $res = $model->getorderlist($_GET);
        $city = $model->getcity();

        $this->assign("get" , $_GET);
        $this->assign("info" , $info);
        $this->assign("city" , $city);
        $this->assign("select_city" , $_GET['city']);

        $new_data = $res['list'];

        $effective=1;
        foreach($new_data as $v){
            if($v['effective']==2){
                $effective=2;
                break;
            }
        }

        $this->assign("effective" , $effective);
        $this->assign("list" , $new_data);
        $this->assign("page" , $res['page']);
        $this->assign("count" , $res['count']);

        if($this->isAjax())
        {
            $str= $_POST['str'];
            $idarr = explode(',',$str);
            foreach($idarr as $v)
            {
                if(!empty($v))
                {
                    $rs = $model->updatestatus_order($v);
                }
            }
            $this->writelog('安智小卖店结算了id为'.$str.',的记录', 'store_order',$str,__ACTION__ ,'','edit');
            echo 1;exit(0);
        }

        $this->display();
    }

    //批量结算相关
    function all_update_status(){
        if($this->isAjax()){
            $str= $_POST['str'];
            $add_begintime= $_POST['add_begintime'];
            $add_endtime= $_POST['add_endtime'];
            $yichang = $_POST['yichang'];
            $idarr = explode(',',$str);
            $sum = 0;
            $yichang_sum = 0;
            $count=0;
            $model = D('sendNum.Store');

            if(empty($add_begintime))//首次
            {
                foreach($idarr as $v){
                    if(!empty($v)){
                        $count++;
                        //$rs = $model->get_yeswjs_count($v);
                        //$rs_yichang = $model->get_yeswjs_count_yichang($v);
                        $rs = $model->get_yeswjs_count($v,$add_begintime,$add_endtime,$yichang);
                        $rs_yichang = $model->get_yeswjs_count_yichang($v,$add_begintime,$add_endtime);
                        $sum +=$rs;
                        $yichang_sum +=$rs_yichang;
                    }
                }
            }else if(empty($_POST['is_save'])){ //选择时间后
                foreach($idarr as $v){
                    if(!empty($v)){
                        $count++;
                        $rs = $model->get_yeswjs_count($v,$add_begintime,$add_endtime,$yichang);
                        $rs_yichang = $model->get_yeswjs_count_yichang($v,$add_begintime,$add_endtime);
                        $sum +=$rs;
                        $yichang_sum +=$rs_yichang;
                    }
                }
            }else{//结算操作
                foreach($idarr as $v){
                    if(!empty($v)){
                        $rs = $model->batch_js($v,$add_begintime,$add_endtime);
                    }
                }
                $this->writelog('安智小卖店批量结算了id为'.$str.',时间从'.$add_begintime.'到'.$add_endtime.'的商家订单', 'store_order',$str,__ACTION__ ,'','edit');
                echo 1;exit(0);
            }

            $sum = 10*$sum;
            $result['count']=$count;
            $result['yichang_sum']=$yichang_sum;
            $result['sum']=$model->doFormatMoney($sum);
            $this->ajaxReturn($result,"获取成功！",1);
        }
    }

    function del_user(){
        $model = D('sendNum.Store');
        if($_POST){
            $d_id = $_POST['d_id'];
            $model->del_user($d_id);
            $this->writelog('安智小卖店删除了id为'.$_POST['d_id'].',的商家记录', 'store_order',$_POST['d_id'],__ACTION__ ,'','del');
            echo 1;exit(0);
        }
    }
}
?>
