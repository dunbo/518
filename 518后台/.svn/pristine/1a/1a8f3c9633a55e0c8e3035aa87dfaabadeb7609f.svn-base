<?php

/**
 * 安智网产品管理平台 CPD流量结算控制器
 * ============================================================================
 * 版权所有 2009-2014 北京力天无限网络有限公司，并保留所有权利。
 * 网站地址: http://www.goapk.com；
 * author：caihuan
 * 结算详情
 * ----------------------------------------------------------------------------
 */
class PmpauditAction extends CommonAction {
    /**
     * 广告客户列表
     */
    public function client_list() {
        $model = D();
        $where = array();


        //搜索条件
        $request = $this->getFormattingRequestData(array('client_name', 'update_tm_one', 'update_tm_two','status', 'content_category','category_type'));
        if (array_key_exists("client_name", $request)) {
            $where['client_name'] = array('like', "%" . trim($request['client_name']) . "%");
            $this->assign("client_name", $request['client_name']);
        }
        if (array_key_exists("status", $request)) {
            if($request['status']!=4){
                $where['status'] = trim($request['status']);
            }
            $this->assign("status", $request['status']);
        }else{
            $where['status'] = 1;
            $this->assign("status", 1);
        }
        if (array_key_exists("content_category", $request)) {
            $categorys_new = $model->table('pmp_content_category')->field('content_category,name')->where(array('parentid'=>trim($request['category_type'])))->select();
            $where['content_category'] = trim($request['content_category']);
            $this->assign("content_category", $request['content_category']);
            $this->assign("categorys_new", $categorys_new);
            $this->assign("category_type", $request['category_type']);
        }else{
            if (array_key_exists("category_type", $request)) {
                $categorys_new = $model->table('pmp_content_category')->field('content_category,name')->where(array('parentid'=>trim($request['category_type'])))->select();
                $this->assign("categorys_new", $categorys_new);
                $category=array();
                foreach($categorys_new as $v){
                    $category[]=$v['content_category'];
                }
                $where['content_category'] = array('in',$category);
                $this->assign("category_type", $request['category_type']);
            }
        }
        
        if (array_key_exists("update_tm_one", $request)) {
            $where['update_tm'] = array('egt',strtotime($request['update_tm_one']));
            $this->assign("update_tm_one", $request['update_tm_one']);
        }
        if (array_key_exists("update_tm_two", $request)) {
            $where['update_tm'] = array('elt',strtotime($request['update_tm_two']));
            $this->assign("update_tm_two", $request['update_tm_two']);
        }
        if (array_key_exists("update_tm_two", $request) && array_key_exists("update_tm_one", $request)) {
            $start=strtotime($request['update_tm_one']);
            $end=strtotime($request['update_tm_two']);
            $where['update_tm'] = array('exp',">=$start and update_tm<=$end");
        }
        
        import("@.ORG.Page");
        $count = $model->table('pmp_advertis_client')->where($where)->count();
        $Page = new Page($count, 20);
        $client_lists = $model->table('pmp_advertis_client')->where($where)->order('create_tm desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        // echo $model->getlastsql();die;
        foreach($client_lists as $k=>$v){
            $content_category = $model->table('pmp_content_category')->where(array('content_category'=>$v['content_category']))->find();
            $content_category_top = $model->table('pmp_content_category')->where(array('content_category'=>$content_category['parentid']))->find();
            $client_lists[$k]['business_name']=$content_category_top['name'].' , '.$content_category['name'];
            $count_advertis = $model->table('pmp_advertis_creative')->where(array('client_id'=>$v['client_id']))->select();
            $client_lists[$k]['count_advertis']=count($count_advertis);
            $truncation=explode('-', $client_lists[$k]['client_id']);
            $client_lists[$k]['client_id_two']=$truncation[0].'-'.$truncation[1].'...';
        }

        $categorys = $model->table('pmp_content_category')->field('content_category,name')->where(array('parentid'=>0))->select();
        $this->assign('categorys', $categorys);
        /*
         * 导出表单
         */
        if ($_GET['export'] == 1) {
            if($_GET['ids']){
                $ids=explode(',', $_GET['ids']);
                $where_two['id']=array('in',$ids);
                $client_list_check = $model->table('pmp_advertis_client')->where($where_two)->select();
                foreach($client_list_check as $k=>$v){
                    $content_category = $model->table('pmp_content_category')->where(array('content_category'=>$v['content_category']))->find();
                    $client_list_check[$k]['low_name']=$content_category['name'];
                    $content_category_top = $model->table('pmp_content_category')->where(array('content_category'=>$content_category['parentid']))->find();
                    $client_list_check[$k]['high_name']=$content_category_top['name'];
                    $count_advertis = $model->table('pmp_advertis_creative')->where(array('client_id'=>$v['client_id']))->select();
                    $client_list_check[$k]['count_advertis']=count($count_advertis);
                    $client_list_check[$k]['update_tm']=date("Y-m-d H:i:s",$v["update_tm"]);
                    if($v['status']==1){
                        $client_list_check[$k]['status']='待审核';
                    }else if($v['status']==2){
                        $client_list_check[$k]['status']='通过';
                    }else if($v['status']==3){
                        $client_list_check[$k]['status']='不通过';
                    }

                    if($v['before_status']==1){
                        $client_list_check[$k]['before_status']='待审核';
                    }else if($v['before_status']==2){
                        $client_list_check[$k]['before_status']='通过';
                    }else if($v['before_status']==3){
                        $client_list_check[$k]['before_status']='不通过';
                    }
                }
                $this->export_advertis($client_list_check,"广告主审核_".date('Y_m_d').".csv",'client');
            }
        }
        // echo "<pre>";var_dump($client_lists);die;
        $this->assign('client_count', count($client_lists));
        $this->assign('client_lists', $client_lists);
        $this->assign('url_suffix', base64_encode($this->get_url_suffix(array('client_name', 'update_tm_one', 'update_tm_two','status', 'content_category','category_type', 'p', 'lr'))));
        $Page->setConfig('header', '条记录');
        $Page->setConfig('first', '<<');
        $Page->setConfig('last', '>>');
        $show = $Page->show();
        $this->assign("page", $show);
        $this->display();
    }
    /**
     * 广告创意列表
     */
    public function creative_list() {
        $model = D();
        $where = array();
        
        
        //搜索条件
        $request = $this->getFormattingRequestData(array('client_id','client_name', 'update_tm_one', 'update_tm_two','status', 'content_category','category_type'));
        if (array_key_exists("client_name", $request)) {
            $where['client_name'] = array('like', "%" . trim($request['client_name']) . "%");
            $this->assign("client_name", $request['client_name']);
        }
        if (array_key_exists("status", $request)) {
           if($request['status']!=4){
                $where['status'] = trim($request['status']);
            }
            $this->assign("status", $request['status']);
        }else{
            $where['status'] = 1;
            $this->assign("status", 1);
        }
        if (array_key_exists("client_id", $request)) {
            $where['client_id'] = trim($request['client_id']);
            $this->assign("client_id", $request['client_id']);
            $where['status'] = array('in',array(1,2,3));
            $this->assign("status", 4);
        }

        if (array_key_exists("content_category", $request)) {
            $categorys_new = $model->table('pmp_content_category')->field('content_category,name')->where(array('parentid'=>trim($request['category_type'])))->select();
            $where['content_category'] = trim($request['content_category']);
            $this->assign("content_category", $request['content_category']);
            $this->assign("categorys_new", $categorys_new);
            $this->assign("category_type", $request['category_type']);
        }else{
            if (array_key_exists("category_type", $request)) {
                $categorys_new = $model->table('pmp_content_category')->field('content_category,name')->where(array('parentid'=>trim($request['category_type'])))->select();
                $this->assign("categorys_new", $categorys_new);
                $category=array();
                foreach($categorys_new as $v){
                    $category[]=$v['content_category'];
                }
                $where['content_category'] = array('in',$category);
                $this->assign("category_type", $request['category_type']);
            }
        }
        
        if (array_key_exists("update_tm_one", $request)) {
            $where['update_tm'] = array('egt',strtotime($request['update_tm_one']));
            $this->assign("update_tm_one", $request['update_tm_one']);
        }
        if (array_key_exists("update_tm_two", $request)) {
            $where['update_tm'] = array('elt',strtotime($request['update_tm_two']));
            $this->assign("update_tm_two", $request['update_tm_two']);
        }
        if (array_key_exists("update_tm_two", $request) && array_key_exists("update_tm_one", $request)) {
            $start=strtotime($request['update_tm_one']);
            $end=strtotime($request['update_tm_two']);
            $where['update_tm'] = array('exp',">=$start and update_tm<=$end");
        }
        
        import("@.ORG.Page");
        $count = $model->table('pmp_advertis_creative')->where($where)->count();
        $Page = new Page($count, 20);
        $creative_lists = $model->table('pmp_advertis_creative')->where($where)->order('create_tm desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        // echo $model->getlastsql();die;
        foreach($creative_lists as $k=>$v){
            $content_category = $model->table('pmp_content_category')->where(array('content_category'=>$v['content_category']))->find();
            $content_category_top = $model->table('pmp_content_category')->where(array('content_category'=>$content_category['parentid']))->find();
            $creative_lists[$k]['business_name']=$content_category_top['name'].' , '.$content_category['name'];
            // $count_advertis = $model->table('pmp_advertis_client')->where(array('client_id'=>$v['client_id']))->select();
            // $creative_lists[$k]['count_advertis']=count($count_advertis);
            $truncation=explode('-', $creative_lists[$k]['client_id']);
            $creative_lists[$k]['client_id_two']=$truncation[0].'-'.$truncation[1].'...';
            $truncation=explode('-', $creative_lists[$k]['creative_id']);
            $creative_lists[$k]['creative_id_two']=$truncation[0].'-'.$truncation[1].'...';
            
        }

        $categorys = $model->table('pmp_content_category')->field('content_category,name')->where(array('parentid'=>0))->select();
        $this->assign('categorys', $categorys);
        /*
         * 导出表单
         */
        if ($_GET['export'] == 1) {
            if($_GET['ids']){
                $ids=explode(',', $_GET['ids']);
                $where_two['id']=array('in',$ids);
                $creative_lists_check = $model->table('pmp_advertis_creative')->where($where_two)->select();
                foreach($creative_lists_check as $k=>$v){
                    $content_category = $model->table('pmp_content_category')->where(array('content_category'=>$v['content_category']))->find();
                    $creative_lists_check[$k]['low_name']=$content_category['name'];
                    $content_category_top = $model->table('pmp_content_category')->where(array('content_category'=>$content_category['parentid']))->find();
                    $creative_lists_check[$k]['high_name']=$content_category_top['name'];
                    $creative_lists_check[$k]['update_tm']=date("Y-m-d H:i:s",$v["update_tm"]);
                    if($v['status']==1){
                        $creative_lists_check[$k]['status']='待审核';
                    }else if($v['status']==2){
                        $creative_lists_check[$k]['status']='通过';
                    }else if($v['status']==3){
                        $creative_lists_check[$k]['status']='不通过';
                    }

                    if($v['before_status']==1){
                        $creative_lists_check[$k]['before_status']='待审核';
                    }else if($v['before_status']==2){
                        $creative_lists_check[$k]['before_status']='通过';
                    }else if($v['before_status']==3){
                        $creative_lists_check[$k]['before_status']='不通过';
                    }
                    //监控地址
                    if($v['monitor_url']){
                        $monitor_url_new="";

                        $monitor_url=json_decode($v['monitor_url'],true);
                        foreach($monitor_url as $kk=>$vv){
                           $monitor_url_new.=$vv."\r\n";
                        }
                    }
                    $creative_lists_check[$k]['monitor_url']="\"$monitor_url_new\"";

                    if($v['click_url']){
                        $click_url_new="";
                            $click_url=json_decode($v['click_url'],true);
                            foreach($click_url as $kk=>$vv){
                               $click_url_new.=$vv."\r\n";
                            }
                    }
                    $creative_lists_check[$k]['click_url']="\"$click_url_new\"";
                }
                $this->export_advertis($creative_lists_check,"广告创意审核_".date('Y_m_d').".csv",'creative');
            }
        }
        // echo "<pre>";var_dump($creative_lists);die;
        $this->assign('creative_count', count($creative_lists));
        $this->assign('creative_lists', $creative_lists);
        $this->assign('url_suffix', base64_encode($this->get_url_suffix(array('client_name', 'update_tm_one', 'update_tm_two','status', 'content_category','category_type', 'p', 'lr'))));
        $Page->setConfig('header', '条记录');
        $Page->setConfig('first', '<<');
        $Page->setConfig('last', '>>');
        $show = $Page->show();
        $this->assign("page", $show);
        $this->display();
    }
     //导出
    public function export_advertis($lists, $filename,$category = "client") {
        
        header("Content-type:text/csv");
        header("Content-Disposition:attachment;filename=" . $filename);
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');
        $str = "";
        if (empty($lists)) {
            $str.=iconv('utf-8', 'gb2312', '没有任何信息');
        } else {
            if ($category == "client") {
                $str = iconv('utf-8', 'gb2312', '广告主id') . "," . iconv('utf-8', 'gb2312', '广告主名称') . "," . iconv('utf-8', 'gb2312', '广告主一级行业') . "," . iconv('utf-8', 'gb2312', '广告主二级行业') . "," . iconv('utf-8', 'gb2312', '广告主网站URL') . "," . iconv('utf-8', 'gb2312', '广告创意') . "," . iconv('utf-8', 'gb2312', '备注信息') . "," . iconv('utf-8', 'gb2312', '上次审核结果') . "," . iconv('utf-8', 'gb2312', '更新时间') . "," . iconv('utf-8', 'gb2312', '状态') ."\r\n";
            } else if ($category == "creative") {
                $str = iconv('utf-8', 'gb2312', '广告主id') . "," . iconv('utf-8', 'gb2312', '广告主名称') . "," . iconv('utf-8', 'gb2312', '广告主一级行业') . "," . iconv('utf-8', 'gb2312', '广告主二级行业') . "," . iconv('utf-8', 'gb2312', '广告素材地址') . "," . iconv('utf-8', 'gb2312', '落地页地址') . "," . iconv('utf-8', 'gb2312', '展示监控') . "," . iconv('utf-8', 'gb2312', '点击监控') . "," . iconv('utf-8', 'gb2312', '备注信息') . "," . iconv('utf-8', 'gb2312', '上次审核结果') . "," . iconv('utf-8', 'gb2312', '更新时间') . "," . iconv('utf-8', 'gb2312', '状态') ."\r\n";
            }
            foreach ($lists as $key => $val) {
                if ($category == "client") {
                    $str.= iconv('utf-8', 'gb2312', $val['client_id']) . "," . iconv('utf-8', 'gb2312', $val['client_name']) . "," . iconv('utf-8', 'gb2312', $val['high_name']). "," .iconv('utf-8', 'gb2312', $val['low_name']) . "," . iconv('utf-8', 'gb2312', $val['url']) . "," . iconv('utf-8', 'gb2312', $val['count_advertis']) . "," . iconv('utf-8', 'gb2312', $val['memo']) . "," . iconv('utf-8', 'gb2312', $val['before_status']) . "," . iconv('utf-8', 'gb2312', $val['update_tm']) .  "," . iconv('utf-8', 'gb2312', $val['status']) ."\r\n";
                } else if ($category == "creative") {
                    $str.= iconv('utf-8', 'gb2312', $val['client_id']) . "," . iconv('utf-8', 'gb2312', $val['client_name']) . "," . iconv('utf-8', 'gb2312', $val['high_name']). "," .iconv('utf-8', 'gb2312', $val['low_name']) . "," . iconv('utf-8', 'gb2312', $val['ad_url']) . "," . iconv('utf-8', 'gb2312', $val['landingpage_url']) . "," . iconv('utf-8', 'gb2312', $val['monitor_url']) . "," . iconv('utf-8', 'gb2312', $val['click_url']) . "," . iconv('utf-8', 'gb2312', $val['memo']) . "," . iconv('utf-8', 'gb2312', $val['before_status']) . "," . iconv('utf-8', 'gb2312', $val['update_tm']) .  "," . iconv('utf-8', 'gb2312', $val['status']) ."\r\n";
                }
            }
        }
        echo $str;
        exit;
    }
     /**
     * 处理请求,并以数组形式返回key=>val
     * 请求的key
     * requestType:请求类型，get、post或者both（二者兼有）
     */
    public function getFormattingRequestData($keys = array(), $requestType = "get") {
        if (empty($keys)) {
            return false;
        }
        $returnData = array();
        if ($requestType == "get") {
            foreach ($keys as $key) {
                if (isset($_GET[$key]) && $_GET[$key] != "" && !empty($_GET[$key])) {
                    $returnData[$key] = $_GET[$key];
                }
            }
        } else if ($requestType == "post") {
            foreach ($keys as $key) {
                if (isset($_POST[$key]) && $_POST[$key] != "" && !empty($_POST[$key])) {
                    $returnData[$key] = $_POST[$key];
                }
            }
        } else if ($requestType == "both") {
            $returnData['get'] = $this->getFormattingRequestData($keys);
            $returnData['post'] = $this->getFormattingRequestData($keys, "post");
        }
        return $returnData;
    }
    public function show_category_name(){
        $model = D('');
        $parentid=$_GET['category_num'];
        $where['parentid'] =$parentid;
        $category = $model->table('pmp_content_category')->where($where)->select();
        if($category){
            echo json_encode($category);
        }else{
            echo 1;
        }
    }

    public function approved_client(){
        $model = D('');
        $id=$_GET['id'];
        $status=$_GET['status'];
        $where['id'] =$id;
        $reject_cause=trim($_GET['reject_cause']);
        if($status==1){
            if($reject_cause){
                $data=array('status'=>3,'before_status'=>1,'reject_cause'=> $reject_cause);
                $log_all_need = $this->logcheck(array('id' => $id), 'pmp_advertis_client', $data, $model);
                $client = $model->table('pmp_advertis_client')->where($where)->save($data);
            }else{
                $data=array('status'=>2,'before_status'=>1);
                $log_all_need = $this->logcheck(array('id' => $id), 'pmp_advertis_client', $data, $model);
                $client = $model->table('pmp_advertis_client')->where($where)->save($data);
            }
            $this->writelog("pmp广告主审核:id为$id,".$log_all_need, 'pmp_advertis_client',$id,__ACTION__ ,'','edit');
        }else if($status==2){
            if($reject_cause){
                $data=array('status'=>3,'before_status'=>2,'reject_cause'=>$reject_cause);
                $log_all_need = $this->logcheck(array('id' => $id), 'pmp_advertis_client', $data, $model);
                $client = $model->table('pmp_advertis_client')->where($where)->save($data);
            }else{
                $data=array('status'=>3,'before_status'=>2);
                $log_all_need = $this->logcheck(array('id' => $id), 'pmp_advertis_client', $data, $model);
                $client = $model->table('pmp_advertis_client')->where($where)->save($data);
            }
            $this->writelog("pmp广告主审核:id为$id,".$log_all_need, 'pmp_advertis_client',$id,__ACTION__ ,'','edit');
            
        }
        if($client){
            echo 1;
        }else{
            echo 2;
        }
    }
    public function approved_creative(){
        $model = D('');
        $id=$_GET['id'];
        $status=$_GET['status'];
        $where['id'] =$id;
        $reject_cause=trim($_GET['reject_cause']);
        if($status==1){
            if($reject_cause){
                $data=array('status'=>3,'before_status'=>1,'reject_cause'=>$reject_cause);
                $log_all_need = $this->logcheck(array('id' => $id), 'pmp_advertis_creative', $data, $model);
                $client = $model->table('pmp_advertis_creative')->where($where)->save($data);
            }else{
                $data=array('status'=>2,'before_status'=>1);
                $log_all_need = $this->logcheck(array('id' => $id), 'pmp_advertis_creative', $data, $model);
                $client = $model->table('pmp_advertis_creative')->where($where)->save($data);
            }
            $this->writelog("pmp广告创意审核:id为$id,".$log_all_need, 'pmp_advertis_creative',$id,__ACTION__ ,'','edit');
        }else if($status==2){
            if($reject_cause){
                $data=array('status'=>3,'before_status'=>2,'reject_cause'=>$reject_cause);
                $log_all_need = $this->logcheck(array('id' => $id), 'pmp_advertis_creative', $data, $model);
                $client = $model->table('pmp_advertis_creative')->where($where)->save($data);
            }else{
                $data=array('status'=>3,'before_status'=>2);
                $log_all_need = $this->logcheck(array('id' => $id), 'pmp_advertis_creative', $data, $model);
                $client = $model->table('pmp_advertis_creative')->where($where)->save($data);
            }
            $this->writelog("pmp广告创意审核:id为$id,".$log_all_need, 'pmp_advertis_creative',$id,__ACTION__ ,'','edit');
            
        }
        if($client){
            echo 1;
        }else{
            echo 2;
        }
    }

    // 广告主驳回
    public function reject_client() {
        $id= trim($_GET['id']);
        $status= trim($_GET['status']);
        $this->assign("status", $status);
        $this->assign("id", $id);
        $this->display();
    }
     // 广告创意驳回
    public function reject_creative() {
        $id= trim($_GET['id']);
        $status= trim($_GET['status']);
        $this->assign("status", $status);
        $this->assign("id", $id);
        $this->display();
    }

     /**
     * 客户——显示
     */
    public function client_show() {
        $id = trim($_GET['id']);
        if (!isset($id) || $id == "") {
            $this->error("error！");
        }
        //根据id取出数据
        $model = D();
        $client = $model->table('pmp_advertis_client')->where("id=" . $id)->find();
        $category_low = $model->table('pmp_content_category')->where(array('content_category'=>$client['content_category']))->find();
        $category_high = $model->table('pmp_content_category')->where(array('content_category'=>$category_low['parentid']))->find();
        $client['low_name']=$category_low['name'];
        $client['high_name']=$category_high['name'];
        $this->assign("client", $client);
        // echo "<pre>";var_dump($client);die;
        $this->assign("url_suffix", $_GET['url_suffix']);
        $this->display();
    }

    //查看监控
    public function monitor_url_show(){
        $map['id'] = trim($_GET['id']);
        $data = D(" ")->table('pmp_advertis_creative')->where($map)->find();
         // var_dump($data['monitor_url']);die;
        if($_GET['num']==1){
             $url=json_decode($data['monitor_url'],true);
             $this->assign("type_name", '展监监控');
        }else{
            $url=json_decode($data['click_url'],true);
            $this->assign("type_name", '点击监控');
        }
        $this->assign("url", $url);
        $this->display();
    }

}
