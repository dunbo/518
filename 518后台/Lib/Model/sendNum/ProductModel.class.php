<?php
/**
 * Desc:   报表项目-产品模型
 * @author Sun Tao<suntao@anzhi.com>
 *
 */
class ProductModel extends Model {
     protected $trueTableName = 'yx_product';

    /**
     *  通过CSV导入数据
     *  @final       2013-09-02
     */	
    public function importadd($data)
    {
        $totalnum = count($data);
        $succnum =0;
        $admin_user_name = $_SESSION['admin']['admin_user_name'];
        $failarr = array();
        foreach($data as $key=>$v)
        {
            $softname = iconv('gbk','utf-8',$v[0]);
            $ishas = $this->getproductbysoftname($softname);
            if($ishas)
            {
                $failarr[$key] = $v;
            }else
            {
                $succnum ++;
                $data['softname'] = iconv('gbk','utf-8',$v[0]);
                $data['size'] = $v[1];
                $data['nature'] = iconv('gbk','utf-8',$v[2]);
                $data['companyname'] = iconv('gbk','utf-8',$v[3]);
                $data['jianjie'] = iconv('gbk','utf-8',$v[4]);
                $data['osuser'] = $admin_user_name;
                $data['hztype'] = iconv('gbk','utf-8',$v[5]);
                $data['com_tj_tel'] = iconv('gbk','utf-8',$v[6]);
                $data['createtime'] = time();
                $data['beizhu'] = iconv('gbk','utf-8',$v[7]);
                $this->add($data);
            }
        }
        $thistime = time();
        $model = D('sendNum.Productimfail');
        $model->importfailadd($failarr,$thistime);
        $res = array(
            'totalnum'=>$totalnum,
            'succnum'=>$succnum,
            'failnum'=>count($failarr),
            'thistime'=>$thistime,
        );
        return $res;
    }

    /**
     *  添加单条新数据
     *  @final       2013-09-04
     */	
    public function addproduct($post)
    {
        $admin_user_name = $_SESSION['admin']['admin_user_name'];
        $data = array();
        $data['softname'] = $post['softname'];
        $data['package'] = trim($post['package']);
        $data['size'] = $post['size'];
        $data['nature'] = $post['nature'];
        $data['companyname'] = $post['companyname'];
        $data['jianjie'] = $post['jianjie'];
        $data['osuser'] = $admin_user_name;
        $data['hztype'] = $post['hztype'];
        $data['com_tj_tel'] = $post['com_tj_tel'];
        $data['createtime'] = time();
        $data['beizhu'] = $post['beizhu'];
        $data['record_num'] = $post['record_num'];
        $data['publication_num'] = $post['publication_num'];
        $data['record_url'] = $post['record_url'];
        $data['publication_url'] = $post['publication_url'];
        $data['dev_auth_url'] = $post['dev_auth_url'];
        $data['coop_auth_url'] = $post['coop_auth_url'];
        $data['soft_auth_url'] = $post['soft_auth_url'];
        $data['ip_auth_url'] = $post['ip_auth_url'];
        return $this->add($data);
    }

    /**
     *  编辑单条新数据
     *  @final       2013-09-04
     */	
    public function editproduct($soft_id,$status,$post)
    {
        $admin_user_name = $_SESSION['admin']['admin_user_name'];
        $data = array();
        $data['softname'] = $post['softname'];
        $data['size'] = $post['size'];
        $data['nature'] = $post['nature'];
        $data['companyname'] = $post['companyname'];
        $data['jianjie'] = $post['jianjie'];
        $data['osuser'] = $admin_user_name;
        $data['hztype'] = $post['hztype'];
        $data['com_tj_tel'] = $post['com_tj_tel'];
        $data['beizhu'] = $post['beizhu'];
		$data['customer_tel'] = $post['customer_tel'];
		$data['customer_qq'] = $post['customer_qq'];
		$data['is_accept_account'] = $post['is_accept_account'];
        $data['record_num'] = $post['record_num'];
        $data['publication_num'] = $post['publication_num'];
        $data['record_url'] = $post['record_url'];
        $data['publication_url'] = $post['publication_url'];

        $data['dev_auth_url'] = $post['dev_auth_url'];
        $data['coop_auth_url'] = $post['coop_auth_url'];
        $data['soft_auth_url'] = $post['soft_auth_url'];
        $data['ip_auth_url'] = $post['ip_auth_url'];
        if(isset($_POST['one_category'])&&!empty($_POST['one_category'])){
            $data['p_fenlei'] = $_POST['one_category'];
        }
         if(isset($_POST['p_leixing'])&&!empty($_POST['p_leixing'])){
            $data['p_leixing'] = $_POST['p_leixing'];
        }
        
        if($status==1){
            $data['reason'] = $post['reason'];
        }
        return $this->data($data)->where("soft_id = $soft_id")->save();
    }

    /**
     *  编辑备注
     *  @final       2013-09-05
     */	
    public function editbeizhu($soft_id,$post)
    {
        $admin_user_name = $_SESSION['admin']['admin_user_name'];
        $data = array();
        $data['beizhu'] = $post['beizhu'];
        $data['osuser'] = $admin_user_name;
        return $this->data($data)->where("soft_id = $soft_id")->save();
    }

    /**
     *  编辑待上线
     *  @final       2013-09-09
     */	
    public function editready($post)
    {
        //如果级别未更改则不更新
        if($post['yuanlevel']!=$post['reviewlevel']){
            $this->add_level_log($post['soft_id'],$post['softname'],$post['reviewlevel']);
        }
        $admin_user_name = $_SESSION['admin']['admin_user_name'];
        $data = array();
        $data['softname'] = $post['softname'];
        $data['size'] = $post['size'];
        $data['nature'] = $post['nature'];
        $data['companyname'] = $post['companyname'];
        $data['jianjie'] = $post['jianjie'];
        $data['hztype'] = $post['hztype'];
        $data['com_tj_tel'] = $post['com_tj_tel'];        
        $data['reviewlevel'] = $post['reviewlevel'];
        $data['jierujindu'] = $post['jierujindu'];
        $data['jierutime'] = strtotime($post['jierutime']);
		$data['p_leixing'] = $post['p_leixing'];
        $data['beizhu'] = $post['beizhu'];
        $data['pack_limit'] = $post['pack_limit'];
        $data['osuser'] = $admin_user_name;
		$data['customer_tel'] = $post['customer_tel'];
		$data['customer_qq'] = $post['customer_qq'];
        $data['record_num'] = $post['record_num'];
        $data['publication_num'] = $post['publication_num'];
        $data['record_url'] = $post['record_url'];
        $data['publication_url'] = $post['publication_url'];
        $data['dev_auth_url'] = $post['dev_auth_url'];
        $data['coop_auth_url'] = $post['coop_auth_url'];
        $data['soft_auth_url'] = $post['soft_auth_url'];
        $data['ip_auth_url'] = $post['ip_auth_url'];
        return $this->data($data)->where("soft_id = $post[soft_id]")->save();
    }
    
    // 编辑上线记录
    public function editonline($post)
    {
        // 判断负责人用户名是否存在
        if ($post['fuzeren'] != '') {
            $fuzeren = $post['fuzeren'];
            $exsit = $this->table('sj_admin_users')->where("admin_user_name='$fuzeren'")->select();
            if (!$exsit) {
                return -1;
            }
        }
        //如果级别为更改则不更新
        if($post['yuanlevel']!=$post['reviewlevel']){
            //$this->add_level_log($post['soft_id'],$post['package'],$post['reviewlevel']);
            $this->add_level_log($post['soft_id'],$post['softname'],$post['reviewlevel']);
        }
        //$admin_user_name = $_SESSION['admin']['admin_user_name'];
        $data = array();
        $data['softname'] = $post['softname'];
        $data['nature'] = $post['nature'];
        $data['companyname'] = $post['companyname'];
        $data['package'] = $post['package'];
        $data['size'] = $post['size'];
        $data['com_tj_tel'] = $post['com_tj_tel'];
        $data['hztype'] = $post['hztype'];
        $data['jianjie'] = $post['jianjie'];
        $data['beizhu'] = $post['beizhu'];
        $data['reviewlevel'] = $post['reviewlevel'];
        $data['jierujindu'] = $post['jierujindu'];
        $data['jierutime'] = strtotime($post['jierutime']);
        $data['sdk'] = $post['sdk'];
        $data['fuzeren'] = $post['fuzeren'];
        $data['fc_type'] = $post['fc_type'];
        //$data['contractime'] = strtotime($post['contractime']);
        //$data['contractendtime'] = strtotime($post['contractendtime']);
        $data['bili'] = $post['bili'];
        $data['customer_tel'] = $post['customer_tel'];
		$data['customer_qq'] = $post['customer_qq'];
		$data['p_leixing'] = $post['p_leixing'];
        $data['record_num'] = $post['record_num'];
        $data['publication_num'] = $post['publication_num'];
        $data['record_url'] = $post['record_url'];
        $data['publication_url'] = $post['publication_url'];
        $data['dev_auth_url'] = $post['dev_auth_url'];
        $data['coop_auth_url'] = $post['coop_auth_url'];
        $data['soft_auth_url'] = $post['soft_auth_url'];
        $data['ip_auth_url'] = $post['ip_auth_url'];
        return $this->data($data)->where("soft_id = $post[soft_id]")->save();
    }

    /**
     *  根据包名获取某产品
     *  @final       2013-09-02
     */	
    public function getproductbybao($bao)
    {
        return $this->field("`soft_id`")->where("package='$bao'")->find();
    }

    /**
     *  根据产品名称获取某产品
     *  @final       2013-09-02
     */	
    public function getproductbysoftname($softname)
    {
        return $this->field("`soft_id`")->where("softname='$softname' and del = 0")->find();
    }    

    /**
     *  根据softid获取某产品
     *  @final       2013-09-02
     */	
    public function getproductbyid($soft_id)
    {
        return $this->field("soft_id,dev_id,softname,package,p_fenlei,p_leixing,hztype,zs_path,apk_path,test_path,test2_path,jianjie,beizhu,com_tj_tel,reviewlevel,reviewtime,jierujindu,jierutime,bili,`from`,step,is_new_soft,pre_package,contacts,customer_tel,customer_qq,is_accept_account")->where("soft_id='$soft_id'")->find();
    }

    /**
     *  根据package获取某产品
     *  @final       2013-09-02
     */	
    public function get_agreement($package)
    {
        return $this->table('sj_sdk_info')->field("agreement_name,agreement_path,agreement_uptm")->where("package='$package'")->find();
    }

    /**
     *  根据softid获取某产品全部属性
     *  @final       2013-09-02
     */	
    public function getallproductbyid($soft_id)
    {
        //return $this->where("soft_id='$soft_id'")->find();
        $res=$this->where("soft_id='$soft_id'")->join('left join sj_soft_whitelist on yx_product.package=sj_soft_whitelist.package and sj_soft_whitelist.status=1')
            ->field("yx_product.soft_id, yx_product.softname, sj_soft_whitelist.softname as softname_bai, yx_product.package, yx_product.p_fenlei, 
                yx_product.p_leixing, yx_product.size, yx_product.nature, yx_product.companyname, yx_product.jianjie, yx_product.hztype,
                 yx_product.osuser, yx_product.com_tj_tel, yx_product.createtime, yx_product.botime, yx_product.beizhu, yx_product.testime,
                  yx_product.testbotime, yx_product.jierujindu, yx_product.reviewlevel, yx_product.jierutime, yx_product.reviewtime, yx_product.reason,
                   yx_product.sdk, yx_product.fuzeren, yx_product.contractime, yx_product.contractendtime, yx_product.bili, yx_product.fc_type, yx_product.onlinetime,
                   yx_product.zs_path, yx_product.apk_path, yx_product.test_path, yx_product.test2_path, yx_product.test3_path,  yx_product.test4_path, yx_product.test5_path,yx_product.status, yx_product.type,yx_product.`from`,yx_product.pack_limit,yx_product.customer_tel,yx_product.customer_qq,yx_product.is_accept_account,yx_product.record_num,yx_product.record_url,yx_product.publication_num,yx_product.publication_url,yx_product.dev_auth_url,yx_product.coop_auth_url,yx_product.soft_auth_url,yx_product.ip_auth_url")->find();
        
        // $res['record_url']=array_pop(explode('/', $res['record_url']));
        // $res['publication_url']=array_pop(explode('/', $res['publication_url']));
        return $res;
    }
    /**
     *  新提交产品通过
     *  @final       2013-09-03
     */	
    public function newtonguo($soft_id,$package,$fenlei,$leixing,$hztype,$zs_path,$apk_path,$is_accept_account=0)
    {
        $admin_user_name = $_SESSION['admin']['admin_user_name'];
        $model = D('sendNum.Blacklist');
        $model->delblack($package);
        $data['p_fenlei'] = $fenlei;
        $data['p_leixing'] = $leixing;
        $data['hztype'] = $hztype;
        $data['osuser'] = $admin_user_name;
        $data['type'] = 1;
        $data['status'] = 0;
        $data['testime'] = time();
		$data['is_accept_account'] = $is_accept_account;
        if($zs_path!=''){
            $data['zs_path'] = $zs_path;
        }
        if($apk_path!=''){
            $data['apk_path'] = $apk_path;
        }
        
        // 把原因置空
        $data['reason'] = "";
        $data['reasontest'] = "";
        // 把评测报告、评测人都置空
        $data['test_path'] = "";
        $data['test_osname'] = "";
        $res = $this->data($data)->where("soft_id = $soft_id")->save();
        $package = $this->where("soft_id = $soft_id")->field('package')->find();
        if($res){
            update_soft_status(array('sdk_status'=>1), $package['package']);
        }
        
        return $res;
    }

    /**
     *  第二标签页通过
     *  @final       2013-09-06
     */	
    public function newtonguo2($soft_id,$softname,$reviewlevel,$test_path)
    {
        $admin_user_name = $_SESSION['admin']['admin_user_name'];
        //判断test_path  是否为空  或者根据status?

        //$model = D('sendNum.Blacklist');
        //$model->delblack($package);
        $status = $this->getstatus($soft_id);
        /*
        if($status['status']==3)
        {
            $data['test2_path'] = $test_path;
            $data['status'] = 4;
        }else
        {
            $data['test_path'] = $test_path;
            $data['status'] = 3;
            $data['test_osname']=$admin_user_name;
        }
        */
        // 判断status状态
        $test_path_arr = array('test_path', 'test2_path', 'test3_path', 'test4_path', 'test5_path');
        $test_osname_arr = array('test_osname', 'test2_osname', 'test3_osname', 'test4_osname', 'test5_osname');
        $key = $status['status'] == 0 ? 0 : $status['status'] - 2;
        $test_path_column = $test_path_arr[$key];
        $test_osname_column = $test_osname_arr[$key];
        $data[$test_path_column] = $test_path;
        $data[$test_osname_column] = $admin_user_name;
        $data['status'] = $status['status'] == 0 ? 3 : $status['status'] + 1;

        //级别变更日志
        $this->add_level_log($soft_id,$softname,$reviewlevel);

        //$data['type'] = 2;
        $data['reviewlevel'] = $reviewlevel;
        $data['reviewtime'] = time();
		//$data['is_accept_account'] = $is_accept_account;
        $ret = $this->data($data)->where("soft_id = $soft_id")->save();
        return $ret;
    }

    /**
     *  获取第一个上传测试报告的人
     *  @final       2013-09-06
     */	
    public function getestosname($soft_id)
    {
        return $this->field("test_osname")->where("soft_id='$soft_id'")->find();
    }

    /**
     *  更改类型
     *  @final       2013-09-06
     */	
    public function updatetype($soft_id,$type,$step=0)
    {
        $admin_user_name = $_SESSION['admin']['admin_user_name'];
        $data['osuser'] = $admin_user_name;
        $data['type'] = $type;
        $data['step'] = $step;
        return $this->data($data)->where("soft_id = $soft_id")->save();
    }

    /**
     *  根据softid获取某产品的status
     *  @final       2013-01-23
     */	
    public function getstatus($soft_id)
    {
        return $this->field("status,singletype,package,dev_id,softname,p_fenlei,is_accept_account")->where("soft_id='$soft_id'")->find();
    }


    /**
     *  新提交产品驳回
     *  @final       2013-09-03
     */	
    public function newbohui($soft_id,$hztype,$reason)
    {
        $data['reason'] = $reason;
        $data['hztype'] = $hztype;
        $data['botime'] = time();
        $data['status'] = 1;
        $package = $this->where("soft_id= $soft_id")->field('package')->find();
        $res = $this->data($data)->where("soft_id = $soft_id")->save();
        update_soft_status(array('sdk_status'=>0), $package['package']);
        return $res;
    }

    /**
     *  评测页面驳回
     *  @final       2013-09-03
     */	
    public function newbohui2($soft_id,$package,$test_path,$reasontest)
    {
        //删文件 清空字段
        //$data['testbotime'] = time();
        $data['botime'] = time(); // 不用testbotime字段，改用botime字段
        $data['status'] = 2; // 评测驳回
        $data['type'] = 0; // 类型从评测产品改为新提交产品
//        $data['zs_path'] = ""; // 证书路径清空
//        $data['apk_path'] = ""; // apk路径清空
        // 评测报表、评测人都清空
        $test_path_arr = array('test_path', 'test2_path', 'test3_path', 'test4_path', 'test5_path');
        $test_osname_arr = array('test_osname', 'test2_osname', 'test3_osname', 'test4_osname', 'test5_osname');
        foreach ($test_path_arr as $test_path) {
            $data[$test_path] = '';
        }
        foreach ($test_osname_arr as $test_osname) {
            $data[$test_osname] = '';
        }
        // 第一个评测路径存储test_path
        $data['test_path'] = $test_path;
        $data['reasontest'] = $reasontest;
        $res = $this->data($data)->where("soft_id = $soft_id")->save();
        $package = $this->where("soft_id = $soft_id")->field('package')->find();
        if($res){
            update_soft_status(array('sdk_status'=>0), $package['package']);
        }
        return $res;
        
    }

    /**
     *  删除某个ID的产品
     *  @final       2013-09-03
     */	
    public function delpro($soft_id)
    {
        $res = $this->where("soft_id = $soft_id")->save(array('del'=>1));
        return $res;
    }
    
    /**
     * 删除sdk_info
     */
    public function del_sdk($package)
    {
        return $this->table('sj_sdk_info')->where("package = '$package'")->delete();
    }
    /**
     *  获取产品列表 type 0为新提交 1为评测 2为待上线 3为上线
     *  @final       2013-09-03
     */	    
    public function getprolist($get,$post,$type=0)
    {
        $sqlparam='';
		if(isset($get['soft_status']) && $get['soft_status'] == 10){
			$soft_status = 10;//软件定时上架状态
			unset($get['soft_status']);
		}
        if(isset($get['soft_status'])&&$get['soft_status']!=-1){
            $where['yx_product.del'] = ' != 0';
            $sqlparam = $sqlparam.'soft_status='.$get['soft_status'].'&';
			if(strstr($get['soft_status'],'sdk')){
				$get['soft_status'] = substr($get['soft_status'],3);
				$where['yx_product.step'] = '6';
			}elseif(in_array($get['soft_status'],array('4','9'))){
				$where['yx_product.step'] = '4';
			}elseif($get['soft_status']==5){
				$where['yx_product.step'] = '5';
			}else{
				$where['yx_product.step'] = '3';
			}
            $where['sj_soft_status.sdk_status'] = array('EQ', $get['soft_status']);
        }else{
            $where['yx_product.del'] = ' != 0';
        }
        if(isset($get['is_debut'])&&$get['is_debut']!=-1){
            $sqlparam = $sqlparam.'is_debut='.$get['is_debut'].'&';
            $where['yx_product.is_debut'] = array('EQ', $get['is_debut']);
        }
        //出版编号和备案编号查询 start
        if(isset($get['record_status'])&&$get['record_status']==2){
            $sqlparam = $sqlparam.'record_status='.$get['record_status'].'&';
            $where['yx_product.record_num'] = array('NEQ', '');
        }else if(isset($get['record_status'])&&$get['record_status']==3){
            $sqlparam = $sqlparam.'record_status='.$get['record_status'].'&';
            $where['yx_product.record_num'] = array('EQ', '');
        }
        if(isset($get['publication_status'])&&$get['publication_status']==2){
            $sqlparam = $sqlparam.'publication_status='.$get['publication_status'].'&';
            $where['yx_product.publication_num'] = array('NEQ', '');
        }else if(isset($get['publication_status'])&&$get['publication_status']==3){
            $sqlparam = $sqlparam.'publication_status='.$get['publication_status'].'&';
            $where['yx_product.publication_num'] = array('EQ', '');
        }
        //出版编号和备案编号查询 end
        if(isset($get['softname']))
        {
            $sqlparam = $sqlparam.'softname='.$get['softname'].'&';
            $where['yx_product.softname'] = array('like', '%'.$get['softname'].'%');
        }
        if(isset($get['package']))
        {
            $sqlparam = $sqlparam.'package='.$get['package'].'&';
            $where['yx_product.package'] = array('EQ',$get['package']);
        }   
        if(isset($get['companyname']))
        {
            $sqlparam = $sqlparam.'companyname='.$get['companyname'].'&';
            $where['companyname'] = array('EQ',$get['companyname']);
        }
        if(isset($get['hztype']))
        {
            $sqlparam = $sqlparam.'hztype='.$get['hztype'].'&';
            $where['hztype'] = array('EQ',$get['hztype']);
        }	
        if(isset($get['zhuangtai'])&&$get['zhuangtai']!=-1)
        {
            $status =$get['zhuangtai'];
            $sqlparam = $sqlparam.'zhuangtai ='.$status.'&';
            $where['yx_product.status'] = array('EQ',$status);

        }
        if(isset($get['osuser']))
        {
            $sqlparam = $sqlparam.'osuser='.$get['osuser'].'&';
            $where['osuser'] = array('EQ',$get['osuser']);
        }
        if (isset($get['fuzeren'])) {
            $sqlparam = $sqlparam.'fuzeren='.$get['fuzeren'].'&';
            if ($get['fuzeren'] == '无') {
                $where['fuzeren'] = array('EQ','');
            } else {
                $where['fuzeren'] = array('EQ',$get['fuzeren']);
            }
        }
        if (isset($get['p_fenlei'])) {
            $sqlparam = $sqlparam.'p_fenlei='.$get['p_fenlei'].'&';
			if ($get['p_fenlei'] == "单机"){
				$where['p_fenlei'] = array('like',"%{$get['p_fenlei']}%");
			}else if ($get['p_fenlei'] != "全部" && $get['p_fenlei'] != "单机"){
				$where['p_fenlei'] = array('EQ',$get['p_fenlei']);
			}	
        }
        if (isset($get['p_leixing'])) {
            $sqlparam = $sqlparam.'p_leixing='.$get['p_leixing'].'&';
            if ($get['p_leixing'] != "全部")
                $where['p_leixing'] = array('EQ',$get['p_leixing']);
        }
        if (isset($get['fc_type'])) {
            $sqlparam = $sqlparam.'fc_type='.$get['fc_type'].'&';
            $where['fc_type'] = array('EQ',$get['fc_type']);
        }
        if (isset($get['reviewlevel'])) {
            $sqlparam = $sqlparam.'reviewlevel='.urlencode($get['reviewlevel']).'&';
            if ($get['reviewlevel'] != "全部")
                $where['reviewlevel'] = array('EQ',$get['reviewlevel']);
        }
        if(isset($get['begintime']))
        {
            if($get['rqtype']==1)
            {
                $sqlparam = $sqlparam.'begintime='.$get['begintime'].'&';
                $sqlparam = $sqlparam.'rqtype=1&';
                if(strlen($get['endtime'])>0)
                {
                    $where['createtime']  = array('between',''.$get['begintime'].','.$get['endtime'].'');
                }else
                {
                    $where['createtime']  = array('between',''.$get['begintime'].',9999999999');
                }
            }else
            {
                $sqlparam = $sqlparam.'begintime='.$get['begintime'].'&';
                $sqlparam = $sqlparam.'rqtype=2&';
                if(strlen($get['endtime'])>0)
                {
                    $where['botime']  = array('between',''.$get['begintime'].','.$get['endtime'].'');
                }else
                {
                    $where['botime']  = array('between',''.$get['begintime'].',9999999999');
                }                
            }
        }    
        if(isset($get['endtime']))
        {
            if($get['rqtype']==1)
            {
                $sqlparam = $sqlparam.'rqtype=1&';
                $sqlparam = $sqlparam.'endtime='.$get['endtime'].'&';
                if(strlen($get['begintime'])>0)
                {
                    $where['createtime']  = array('between',''.$get['begintime'].','.$get['endtime'].'');
                }else
                {
                    $where['createtime']  = array('between','0,'.$get['endtime'].'');
                }
            }else
            {
                $sqlparam = $sqlparam.'rqtype=2&';
                $sqlparam = $sqlparam.'endtime='.$get['endtime'].'&';
                if(strlen($get['begintime'])>0)
                {
                    $where['botime']  = array('between',''.$get['begintime'].','.$get['endtime'].'');
                }else
                {
                    $where['botime']  = array('between','0,'.$get['endtime'].'');
                }
            }
        }

        if(isset($get['reviewbegintime']))
        {
            $sqlparam = $sqlparam.'reviewbegintime='.$get['reviewbegintime'].'&';
            if(strlen($get['reviewendtime'])>0)
            {
                $where['reviewtime']  = array('between',''.$get['reviewbegintime'].','.$get['reviewendtime'].'');
            }else
            {
                $where['reviewtime']  = array('between',''.$get['reviewbegintime'].',9999999999');
            }
            
        }    
        if(isset($get['reviewendtime']))
        {
            $sqlparam = $sqlparam.'reviewendtime='.$get['reviewendtime'].'&';
            if(strlen($get['reviewbegintime'])>0)
            {
                $where['reviewtime']  = array('between',''.$get['reviewbegintime'].','.$get['reviewendtime'].'');
            }else
            {
                $where['reviewtime']  = array('between','0,'.$get['reviewendtime'].'');
            }
        }        
       
        if($post)
        {
            if(isset($post['is_debut'])&&$post['is_debut']!=-1){
                $sqlparam = $sqlparam.'is_debut='.$post['is_debut'].'&';
                $where['yx_product.is_debut'] = array('EQ', $post['is_debut']);
            }
            if(isset($post['soft_status'])&&$post['soft_status']!=-1){
				$sqlparam = $sqlparam.'soft_status='.$post['soft_status'].'&';
				$where['sj_soft_status.sdk_status'] = array('EQ', $post['soft_status']);
			}
            if(strlen($post['softname'])>0)
            {
                $sqlparam = $sqlparam.'softname='.$post['softname'].'&';
                $where['yx_product.softname'] = array('like', '%'.$post['softname'].'%');
            }
            if(strlen($post['package'])>0)
            {
                $sqlparam = $sqlparam.'package='.$post['package'].'&';
                $where['yx_product.package'] = array('EQ',$post['package']);
            }   
            if(strlen($post['companyname'])>0)
            {
                $sqlparam = $sqlparam.'companyname='.$post['companyname'].'&';
                $where['companyname'] = array('EQ',$post['companyname']);
            }
            //出版编号和备案编号查询 start
            if(isset($post['record_status'])&&$post['record_status']==2){
                $sqlparam = $sqlparam.'record_status='.$post['record_status'].'&';
                $where['yx_product.record_num'] = array('NEQ', '');
            }else if(isset($post['record_status'])&&$post['record_status']==3){
                $sqlparam = $sqlparam.'record_status='.$post['record_status'].'&';
                $where['yx_product.record_num'] = array('EQ', '');
            }
            if(isset($post['publication_status'])&&$post['publication_status']==2){
                $sqlparam = $sqlparam.'publication_status='.$post['publication_status'].'&';
                $where['yx_product.publication_num'] = array('NEQ', '');
            }else if(isset($post['publication_status'])&&$post['publication_status']==3){
                $sqlparam = $sqlparam.'publication_status='.$post['publication_status'].'&';
                $where['yx_product.publication_num'] = array('EQ', '');
            }
            //出版编号和备案编号查询 end
            if(strlen($post['hztype'])>0)
            {
                $sqlparam = $sqlparam.'hztype='.$post['hztype'].'&';
                $where['hztype'] = array('EQ',$post['hztype']);
            }     
            if(isset($post['zhuangtai'])&&$post['zhuangtai']!=-1)
            {
                $status =$post['zhuangtai']-1;
                $sqlparam = $sqlparam.'zhuangtai='.$status.'&';
                $where['status'] = array('EQ',$status);
            }
            if(strlen($post['osuser'])>0)
            {
                $sqlparam = $sqlparam.'osuser='.$post['osuser'].'&';
                $where['osuser'] = array('EQ',$post['osuser']);
            }
            if(strlen($post['fuzeren'])>0) {
                $sqlparam = $sqlparam.'fuzeren='.$post['fuzeren'].'&';
                if ($post['fuzeren'] == '无') {
                    $where['fuzeren'] = array('EQ','');
                } else {
                    $where['fuzeren'] = array('EQ',$post['fuzeren']);
                }
            }
            if(strlen($post['p_fenlei'])>0) {
                $sqlparam = $sqlparam.'p_fenlei='.$post['p_fenlei'].'&';
				if ($get['p_fenlei'] == "单机"){
					$where['p_fenlei'] = array('like',"%{$get['p_fenlei']}%");
				}else if ($get['p_fenlei'] != "全部" && $get['p_fenlei'] != "单机"){
					$where['p_fenlei'] = array('EQ',$get['p_fenlei']);
				}	
            }
            if(strlen($post['p_leixing'])>0) {
                $sqlparam = $sqlparam.'p_leixing='.$post['p_leixing'].'&';
                if ($post['p_leixing'] != "全部")
                    $where['p_leixing'] = array('EQ',$post['p_leixing']);
            }
            if(strlen($post['fc_type'])>0) {
                $sqlparam = $sqlparam.'fc_type='.$post['fc_type'].'&';
                $where['fc_type'] = array('EQ',$post['fc_type']);
            }
            if(strlen($post['reviewlevel'])>0) {
                $sqlparam = $sqlparam.'reviewlevel='.urlencode($post['reviewlevel']).'&';
                if ($post['reviewlevel'] != "全部")
                    $where['reviewlevel'] = array('EQ',$post['reviewlevel']);
            }

            //创建时间
            if(strlen($post['begintime'])>0)
            {
                if($post['rqtype']==1)
                {
                    $sqlparam = $sqlparam.'begintime='.strtotime($post['begintime']).'&';
                    $sqlparam = $sqlparam.'rqtype=1&';
                    if(strlen($post['endtime'])>0)
                    {
                        $where['createtime']  = array('between',''.strtotime($post['begintime']).','.strtotime($post['endtime']).'');
                    }else
                    {
                        $where['createtime']  = array('between',''.strtotime($post['begintime']).',9999999999');
                    }
                }else
                {
                    $sqlparam = $sqlparam.'rqtype=2&';
                    $sqlparam = $sqlparam.'begintime='.strtotime($post['begintime']).'&';
                    if(strlen($post['endtime'])>0)
                    {
                        $where['botime']  = array('between',''.strtotime($post['begintime']).','.strtotime($post['endtime']).'');
                    }else
                    {
                        $where['botime']  = array('between',''.strtotime($post['begintime']).',9999999999');
                    }                    
                }
            }
            if(strlen($post['endtime'])>0)
            {
                if($post['rqtype']==1)
                {
                    $sqlparam = $sqlparam.'endtime='.strtotime($post['endtime']).'&';
                    $sqlparam = $sqlparam.'rqtype=1&';
                    if(strlen($post['begintime'])>0)
                    {
                        $where['createtime']  = array('between',''.strtotime($post['begintime']).','.strtotime($post['endtime']).'');
                    }else
                    {
                        $where['createtime']  = array('between','0,'.strtotime($post['endtime']).'');
                    }                    
                }else
                {
                    $sqlparam = $sqlparam.'endtime='.strtotime($post['endtime']).'&';
                    $sqlparam = $sqlparam.'rqtype=2&';
                    if(strlen($post['begintime'])>0)
                    {
                        $where['botime']  = array('between',''.strtotime($post['begintime']).','.strtotime($post['endtime']).'');
                    }else
                    {
                        $where['botime']  = array('between','0,'.strtotime($post['endtime']).'');
                    }                         
                }
            }

            //评测通过时间
            if(strlen($post['reviewbegintime'])>0)
            {
                $sqlparam = $sqlparam.'reviewbegintime='.strtotime($post['reviewbegintime']).'&';
                if(strlen($post['reviewendtime'])>0)
                {
                    $where['reviewtime']  = array('between',''.strtotime($post['reviewbegintime']).','.strtotime($post['reviewendtime']).'');
                }else
                {
                    $where['reviewtime']  = array('between',''.strtotime($post['reviewbegintime']).',9999999999');
                }
            }
            if(strlen($post['reviewendtime'])>0)
            {
                $sqlparam = $sqlparam.'reviewendtime='.strtotime($post['reviewendtime']).'&';
                if(strlen($post['reviewbegintime'])>0)
                {
                    $where['reviewtime']  = array('between',''.strtotime($post['reviewbegintime']).','.strtotime($post['reviewendtime']).'');
                }else
                {
                    $where['reviewtime']  = array('between','0,'.strtotime($post['reviewendtime']).'');
                }
            }
            
            // 上线时间
            if($type==3)
            {
                $onlinebegintime = 0;
                $onlineendtime = 9999999999;
                if (strlen($post['onlinebegintime']) > 0) {
                    $onlinebegintime = strtotime($post['onlinebegintime']);
                    $sqlparam = $sqlparam.'onlinebegintime='.$onlinebegintime.'&';
                }
                if (strlen($post['onlineendtime']) > 0) {
                    $onlineendtime = strtotime($post['onlineendtime']);
                    $sqlparam = $sqlparam.'onlineendtime='.$onlineendtime.'&';
                }
                $where['onlinetime']  = array('between',''.$onlinebegintime.','.$onlineendtime.'');
            }
        }
		if(isset($get['onlinebegintime']) && isset($get['onlineendtime'])){
			$where['onlinetime']  = array(array("egt",$get['onlinebegintime']),array("elt",$get['onlineendtime']));
		}
        $where['yx_product.type'] = $type;
        // 上线列表的查看，判断当前用户是否在权限管理员列表里
        /*        
        if ($type === 3) {
            $user_name = $_SESSION['admin']['admin_user_name'];
            $exsit = $this->table('yx_admin')->where("admin_user_name='$user_name'")->select();
            $auth_online = $exsit[0]['auth_online'];
            if (!$exsit || $auth_online != 1) {
                $where['fuzeren'] = array('EQ',$user_name);
                if(isset($get['fuzeren'])) {
                    if ($get['fuzeren'] != $user_name)
                        $where[1] = array('EQ', -1);
                }
                if($post && strlen($post['fuzeren'])>0) {
                    if ($post['fuzeren'] != $user_name)
                        $where[1] = array('EQ', -1);
                }
            }
        }
        */
        unset($where['fuzeren']);
        import("@.ORG.Page");
        if(isset($get['soft_status'])&&$get['soft_status']!=-1){
            $count = $this->where($where)->join('left join sj_soft_status on yx_product.package=sj_soft_status.package')->count();
        }else{
			if($soft_status == 10){
				$where['sj_soft_tmp.pass_status'] = 1;
				$where['sj_soft_tmp.pass_time'] = array('gt',time());;
				$count = $this->where($where)->join('left join sj_soft_tmp on yx_product.package=sj_soft_tmp.package')->count();;
			}else{
				$count = $this->where($where)->count();
			}
        }
        
        $page = new Page($count, 50);
        if($_GET['o']){
            if($type==0)
            {
                $order = "status,createtime asc,botime asc";
            }else if($type==1)
            {
                $order = "status,testime asc,testbotime asc";
            }else if($type==2)
            {
                $order = "reviewtime desc";
            }else if ($type == 3) {
                $order = "onlinetime desc";
            }
        }else{
            $order = "pre_tm ".$_GET['orderby'];
        }
        


        //$list = $this->field("soft_id,softname,package,p_fenlei,p_leixing,size,nature,companyname,jianjie,hztype,osuser,com_tj_tel,createtime,botime,beizhu,status,reason,p_fenlei,p_leixing,testime,testbotime,zs_path,apk_path,test_path,jierujindu,reviewlevel,jierutime,reviewtime")->where($where)->limit($page->firstRow.','.$page->listRows)->order($order)->select();
        //$allist = $this->field("soft_id,softname,package,p_fenlei,p_leixing,size,nature,companyname,jianjie,hztype,osuser,com_tj_tel,createtime,botime,beizhu,status,reason,p_fenlei,p_leixing,testime,testbotime,jierujindu,reviewlevel,jierutime,reviewtime")->where($where)->order($order)->select();
        //$list = $this->where($where)->limit($page->firstRow.','.$page->listRows)->order($order)->select();
        //$allist = $this->where($where)->order($order)->select();
        $field_str= 'yx_product.soft_id, yx_product.softname,yx_product.package, yx_product.p_fenlei, 
                    yx_product.p_leixing, yx_product.size, yx_product.nature, yx_product.companyname, yx_product.jianjie, yx_product.hztype,
                     yx_product.osuser, yx_product.com_tj_tel, yx_product.createtime, yx_product.botime, yx_product.beizhu, yx_product.testime,
                      yx_product.testbotime, yx_product.jierujindu, yx_product.reviewlevel, yx_product.jierutime, yx_product.reviewtime, yx_product.reasontest, yx_product.reason,
                       yx_product.sdk, yx_product.fuzeren, yx_product.contractime, yx_product.contractendtime, yx_product.bili, yx_product.fc_type, yx_product.onlinetime,
                       yx_product.zs_path, yx_product.apk_path, yx_product.test_path, yx_product.test2_path, yx_product.test3_path, yx_product.test4_path, yx_product.test5_path, yx_product.status, yx_product.type,yx_product.`from`,yx_product.pre_tm,yx_product.is_debut,yx_product.dev_id,yx_product.is_accept_account,yx_product.step,yx_product.record_num,yx_product.publication_num,yx_product.dev_auth_url,yx_product.coop_auth_url,yx_product.soft_auth_url,yx_product.ip_auth_url,yx_product.record_url,yx_product.publication_url';
		if($soft_status == 10){
			$field_str .= ',sj_soft_tmp.pass_time,sj_soft_tmp.pass_status';
		}
        if ($type === 3) {
            $field_str .= ', sj_soft_whitelist.softname as softname_bai';
            $list = $this->where($where)->join('left join sj_soft_whitelist on yx_product.package=sj_soft_whitelist.package and sj_soft_whitelist.status=1')
                ->field($field_str)
                ->limit($page->firstRow.','.$page->listRows)->order($order)->select();
            $allist = $this->where($where)->join('left join sj_soft_whitelist on yx_product.package=sj_soft_whitelist.package and sj_soft_whitelist.status=1')
                ->field($field_str)
                ->order($order)->select();
        }else if($type == 2&&isset($get['soft_status'])&&$get['soft_status']!=-1){
            $list = $this->where($where)->join('left join sj_soft_status on yx_product.package=sj_soft_status.package left join sj_soft_tmp on yx_product.package=sj_soft_tmp.package')
                ->field($field_str)
                ->limit($page->firstRow.','.$page->listRows)->group("yx_product.package")->order($order)->select();
        }else
        {
            $list = $this->where($where)->join('left join sj_soft_tmp on yx_product.package=sj_soft_tmp.package')
                ->field($field_str)->group("sj_soft_tmp.package")->limit($page->firstRow.','.$page->listRows)->order($order)->select();
            $allist = $this->where($where)->order($order)->select();        
        }
		//定时上架 数据处理
		if($soft_status == 10){
			foreach($list as $k=>$v){
				if($v['pass_status'] != 1 || $v['pass_time'] <time()){
					unset($list[$k]);
				}
			}
		}
        $dec_ids=array();
        $packages=array();
        foreach ($list as $key => $v) {
            $dec_ids[]=$v['dev_id'];
            $packages[]=$v['package'];
        }
        if($type === 3 || $type == 2){
                $where=array();
                $where['package']=array('in',$packages);
                $where['hide']=1;
                $where['status']=1;
                $res=$this->table('sj_soft')->field('package,max(softid) as softid')->where($where)->group('package')->select();                
                $softids=array();
                foreach($res as $re){
                    $softids[]=$re['softid'];
                }
                if($softids){
                    $where=array();
                    $where['softid']=array('in',$softids);
                    $iconurls=$this->table('sj_icon')->field('softid,iconurl_512')->where($where)->select();
                    foreach($res as $key=>$re){
                        foreach($iconurls as $v){
                            if($re['softid']==$v['softid']){
                                $res[$key]['iconurl_512']=$v['iconurl_512'];
                            }
                        }
                    }
                    foreach($list as $key=>$vv){
                        foreach($res as $re){
                            if($vv['package']==$re['package']){
                              $list[$key]['iconurl_512']=$re['iconurl_512'];  
                              $list[$key]['softid']=$re['softid'];  
                            }
                        }
                    }
                }
        }
        //从pu_developer中获取公司名称
        $where=array();
        $where['dev_id']=array('in',$dec_ids);
        $res=$this->table('pu_developer')->field('dev_id,company')->where($where)->select();
        if($res){
            foreach($list as $key=>$v){
                foreach($res as $re){
                    if($v['dev_id']==$re['dev_id']){
                        $list[$key]['company']=$re['company'];
                    }
                }
            }
        }
		// echo "<pre>";print_r($list);die;
        //echo $this->getlastsql();
        $page->parameter = $sqlparam;
        $page->setConfig('header','篇记录');
        $page->setConfig('first','<<');
        $page->setConfig('last','>>');
        $show =$page->show();    
        $res = array(
            'list'=>$list,
            'allist'=>$allist,
            'show'=>$show,
            'sqlparam'=>$sqlparam,
        );
        return $res;
    }

    /**
     *  添加级别变更日志
     *  @final       2013-09-06
     */	
    public function add_level_log($soft_id,$softname,$level)
    {
        $leveltable= $this->table(' yx_level_log');
        $data = array();
        $data['soft_id'] =$soft_id;
        $data['softname'] =$softname;
        $data['level'] =$level;
        $data['createtime'] =time();
        return $leveltable->add($data);
    }

    /**
     *  上线操作
     *  @final       2013-09-09
     */	
    public function setonline($post,$zs_path=NULL)
    {
        if($zs_path!=NULL){
            $data['zs_path'] = $zs_path;
        }

        $admin_user_name = $_SESSION['admin']['admin_user_name'];
        $data['package'] = $post['ol_package'];
        $data['jierujindu'] = $post['jierujindu'];
        $data['reviewlevel'] = $post['reviewlevel'];
        $data['jierutime'] = strtotime($post['jierutime']);
        $data['sdk'] = $post['sdk'];
        $data['fc_type'] = $post['fc_type'];
        $data['bili'] = $post['bili'];
        $data['fuzeren'] = $post['fuzeren'];
        $data['type'] = 3;
        $data['osuser'] = $admin_user_name;
        $data['onlinetime'] = time();
        $this->data($data)->where("soft_id = {$post['soft_id']}")->save();
        //file_put_contents('s1.txt',$this->getlastsql());
    }

    /**
     *  根据包名同步白名单
     *  @final       2013-09-06
     */	
    public function getbaisoftbybao($package)
    {
        $sj_soft_whitelist= $this->table('sj_soft_whitelist');
        $rs = $sj_soft_whitelist->field("softname")->where("package='$package'")->find();
        return $rs['softname'];
    }

    /**
     *  判断某用户名是否存在
     *  @final       2013-09-06
     */	    
    public function is_exsit_admin($name)
    {
        $exsit = $this->table('sj_admin_users')->where("admin_user_name='$name'")->find();
        return $exsit ? 1 : 0;
    }
    
    // 修改负责人
    public function modify_fuzeren($id, $fuzeren) {
        $where = array(
            'soft_id' => $id,
        );
        $data = array(
            'fuzeren' => $fuzeren,
        );
        return $this->where($where)->save($data);
    }
	//获取下载路径
	public function get_sdk_down_path($pkg){
		$where = array(
			'package' => array('in', $pkg),
		);
		$sdk_info = $this->table('sj_sdk_info')->where($where)->field('tmp_id,package,is_agreement,agreement_path')->select();
		$tmpid = array();
		foreach($sdk_info as $k=>$v){
			$tmpid[] = $v['tmp_id'];	
		}
		if($tmpid){	
			$where = array(
				'id' => array('in', $tmpid),
			);
			$tmp_info = get_table_data($where, "sj_soft_tmp", "id", "id,pass_time");
		}
		$list_arr = array(); 
		foreach($sdk_info as $k=>$v){
			$list_arr[$v['package']]['tmp_id'] = $v['tmp_id'];
			$list_arr[$v['package']]['is_agreement'] = $v['is_agreement'];
			$list_arr[$v['package']]['agreement_path'] = $v['agreement_path'];
			$list_arr[$v['package']]['pass_time'] = $tmp_info[$v['tmp_id']]['pass_time'];
		}
		unset($sdk_info,$tmp_info);
		return $list_arr;
	}
	// 
	public function add_soft_whitelist($data){
		$time = time();
		$ret = $this->table('sj_soft_whitelist')->where("package='{$data['package']}'")->field('dev_id,softname')->find();
		$map = array(
			 'status' => 1,
			 'is_sdk' => 0,
			 'is_online' => 0,
		);
		if($ret){
			$map['update_at'] = $time;
			$res = $this -> table('sj_soft_whitelist') -> where("package='{$data['package']}'")->save($map);
		}else{
			$map['create_at'] = $time;
			$map['update_at'] = $time;
			$map['dev_id'] = $data['dev_id'];
			$map['softname'] = $data['softname'];
			$map['package'] = $data['package'];
			$map['fen_lei'] = $data['fen_lei'];
			$map['add_from'] = 1;
			$map['is_accept_account'] =$data['is_accept_account'];
			$map['is_accept_sdk'] = $data['is_accept_sdk'];
			$map['is_accept_pay'] = $data['is_accept_pay'];
			$res = $this -> table('sj_soft_whitelist') -> add($map);
		}
		return $res;
	}
	
	//获取报表软件联运状态
	public function get_soft_status(){
		
	}
	
	
}
?>
