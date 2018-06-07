<?php

/**
 * 安智网产品管理平台 广告结算控制器
 * ============================================================================
 * 版权所有 2009-2014 北京力天无限网络有限公司，并保留所有权利。
 * 网站地址: http://www.goapk.com；
 * author：L
 * ----------------------------------------------------------------------------
 */
class ClientAction extends CommonAction {

    /**
     * 客户列表
     */
    public function index() {

        $where = array();
        $where['status'] = 1;

        //搜索条件
        $request = $this->getFormattingRequestData(array('client_name', 'created_start', 'created_end', 'contact_name'));
        if (array_key_exists("client_name", $request)) {
            $where['client_name'] = array('like', "%" . trim($request['client_name']) . "%");
            $this->assign("client_name", $request['client_name']);
        }
        if (array_key_exists("created_start", $request) && !array_key_exists("created_end", $request)) {
            $where['create_tm'] = array('between', strtotime($request['created_start']) . "," . time());
            $this->assign("created_start", $request['created_start']);
        }
        if (!array_key_exists("created_start", $request) && array_key_exists("created_end", $request)) {
            $where['create_tm'] = array('between', "0," . strtotime($request['created_end']));
            $this->assign("created_end", $request['created_end']);
        }
        if (array_key_exists("created_start", $request) && array_key_exists("created_end", $request)) {
            $where['create_tm'] = array('between', strtotime($request['created_start']) . "," . strtotime($request['created_end']));
            $this->assign("created_start", $request['created_start']);
            $this->assign("created_end", $request['created_end']);
        }
        if (array_key_exists("contact_name", $request) && array_key_exists("contact_name", $request)) {
            // $where['contact_name'] = array('like', "%" . trim($request['contact_name']) . "%");
            // $where['cpd_contact_name'] = array('like', "%" . trim($request['contact_name']) . "%");
            $where['contact_name'] = array('exp', "like '%" . trim($request['contact_name']) . "%' )or (cpd_contact_name like '%" . trim($request['contact_name']) . "%' ");
            $this->assign("contact_name", $request['contact_name']);
        }

        // $this->showArr($where);
        $client_db = D('Settlement.Client');
        import("@.ORG.Page");
        $count = $client_db->where($where)->count();
        $Page = new Page($count, 50);
        $client_lists = $client_db->where($where)->order('create_tm desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        // echo $client_db->getlastsql();
        /*
         * 导出表单
         */
        if ($_GET['export'] == 1) {
            if($_GET['ids']){
                $ids=explode(',', $_GET['ids']);
                $where_two['id']=array('in',$ids);
                $client_list_check = $client_db->where($where_two)->select();
                 foreach($client_list_check as $k=>$v){
                    if($v['client_type']==1){
                        $client_list_check[$k]['client_type_name']='直投';
                    }else if($v['client_type']==2){
                         $client_list_check[$k]['client_type_name']='代理';
                    }
                    $dev_res = $client_db->table('pu_developer')->where(array('dev_id'=>$v['dev_id']))->find();
                    if($dev_res){
                        $client_list_check[$k]['dev_name']=$dev_res['dev_name'];
                    }
                    $client_list_check[$k]['update_tm']=$v['update_tm']?date("Y-m-d H:i:s", $v['update_tm']):'';
                }
                $this->exportClients($client_list_check,"客户列表_".date('Y-m-d-h-i').".csv");
            }
        }
        foreach($client_lists as $k=>$v){
            $dev_re = $client_db->table('pu_developer')->where(array('dev_id'=>$v['dev_id']))->find();
            if($dev_re){
                $client_lists[$k]['dev_name']=$dev_re['dev_name'];
            }
        }
        // echo "<pre>";var_dump($client_lists);die;
        $this->assign('client_count', count($client_lists));
        $this->assign('client_lists', $client_lists);
        $this->assign('url_suffix', base64_encode($this->get_url_suffix(array('client_name', 'created_start', 'created_end', 'p', 'lr'))));
        // var_dump( base64_encode($this->get_url_suffix(array('client_name', 'created_start', 'created_end', 'p', 'lr'))));die;
        $Page->setConfig('header', '条记录');
        $Page->setConfig('first', '<<');
        $Page->setConfig('last', '>>');
        $show = $Page->show();
        $this->assign("page", $show);
        $this->display();
    }

    /**
     * 导出客户表单
     */
    public function exportClients($client_lists,$filename) {
        header("Content-type:text/csv");
        header("Content-Disposition:attachment;filename=" . $filename);
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');
        $str = "";
        if (empty($client_lists)) {
            $str.=iconv('utf-8', 'gb2312', '没有任何节点信息');
        } else {
            $str = "". iconv('utf-8', 'gb2312', '客户ID')."," . iconv('utf-8', 'gb2312', '客户名称') . "," . iconv('utf-8', 'gb2312', '客户类型'). "," . iconv('utf-8', 'gb2312', 'CPT联系人') . "," . iconv('utf-8', 'gb2312', 'CPT联系方式') . "," . iconv('utf-8', 'gb2312', 'CPT协议数量') . "," . iconv('utf-8', 'gb2312', 'CPT合同数量') . "," .iconv('utf-8', 'gb2312', 'CPD联系人') . "," . iconv('utf-8', 'gb2312', 'CPD联系方式') . "," .iconv('utf-8', 'gb2312', 'CPD公司名称') . "," . iconv('utf-8', 'gb2312', 'CPD公司地址') . "," . iconv('utf-8', 'gb2312', 'CPD计划数量') . "," . iconv('utf-8', 'gb2312', 'CPD合同数量') . "," .iconv('utf-8', 'gb2312', '数据查看账号') . ",". iconv('utf-8', 'gb2312', '更新时间') . "," . iconv('utf-8', 'gb2312', '创建时间'). "\r\n";
            foreach ($client_lists as $key => $val) {
                $str.= "".iconv('utf-8', 'gb2312', $val['id'])."," . iconv('utf-8', 'gb2312', $val['client_name']). "," . iconv('utf-8', 'gb2312', $val['client_type_name']) . "," . iconv('utf-8', 'gb2312', $val['contact_name']) . "," . iconv('utf-8', 'gb2312', $val['contact_phone']) . "," . iconv('utf-8', 'gb2312', $val['agreement_num']) . "," . iconv('utf-8', 'gb2312', $val['contract_num']). "," . iconv('utf-8', 'gb2312', $val['cpd_contact_name']) . "," . iconv('utf-8', 'gb2312', $val['cpd_contact_phone']) . "," . iconv('utf-8', 'gb2312', $val['cpd_company']) . "," . iconv('utf-8', 'gb2312', $val['cpd_company_address']) . "," . iconv('utf-8', 'gb2312', $val['cpd_task_num']) . "," . iconv('utf-8', 'gb2312', $val['cpd_contract_num']). "," . iconv('utf-8', 'gb2312', $val['dev_name']) . ","  . date("Y-m-d H:i:s", $val['create_tm']) . "," . iconv('utf-8', 'gb2312', $val['update_tm']). "\r\n";
            }
        }
        echo $str;
        exit;
    }

    /**
     * 添加客户——显示
     */
    public function add_client_show() {
        $this->assign("url_suffix", $_GET['url_suffix']);
        $this->display();
    }

    /**
     * 添加客户——执行
     */
    public function add_client_do() {
        if (!$this->isPost()) {
            $this->error('数据来源错误！');
        }
        $save_data['client_name'] = trim($_POST['client_name']);
        $save_data['contact_name'] = trim($_POST['contact_name']);
        $save_data['contact_phone'] = trim($_POST['contact_phone']);
		$save_data['cpd_contact_name'] = trim($_POST['cpd_contact_name']);
        $save_data['cpd_contact_phone'] = trim($_POST['cpd_contact_phone']);
		$save_data['cpd_company'] = trim($_POST['cpd_company']);
        $save_data['cpd_company_address'] = trim($_POST['cpd_company_address']);
        $save_data['client_type'] = trim($_POST['client_type']);
        $save_data['dev_id'] = trim($_POST['dev_id']);
        $save_data['create_tm'] = time();
        $save_data['update_tm'] = time();
        $save_data['admin_id'] = $_SESSION['admin']['admin_id'];
        $save_data['admin_name'] = $_SESSION['admin']['admin_user_name'];
		
		//判断客户名称 和cpd的信息是否填写
        foreach ($save_data as $key=>$data) 
		{
			if($key=='contact_name'||$key=='contact_phone'||$key=='dev_id') continue;
            if ($data == "" || !isset($data)||$data == '0') {
                $this->error($key.'数据不全！');
            }
        }
		
        $client_db = D('Settlement.Client');
        //判断客户是否存在
        $client_lists = $client_db->where("client_name = '" . $save_data['client_name'] . "' and status = 1 ")->select();
        if (count($client_lists) > 0) {
            $this->error('客户已存在！');
        }
        //判断dev_id是否存在
        if($save_data['dev_id']){
            $dev_lists = $client_db->where("dev_id = '" . $save_data['dev_id'] . "' and status = 1 ")->select();
            if (count($dev_lists) > 0) {
                $this->error('开发者账号已被关联！');
            }
        }
        if ($_client_id = $client_db->add($save_data)) {
        	
        	//2014.11.6 jiwei 记录日志
        	$this->writelog("广告结算：添加客户(client_id={$_client_id})", 'sj_clients', $_client_id,__ACTION__ ,'','add');
        	
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Client/index' . base64_decode($_POST['url_suffix']));
            $this->success('客户添加成功！');
        } else {
            $this->error('客户添加失败！');
        }
    }

    /**
     * ajax获取客户名是否存在
     */
    public function ajax_check_client_name() {
        //数据来源错误
        if (!$this->isAjax() || !$this->isPost()) {
            echo "1";
            exit();
        }
        $client_name = trim($_POST['client_name']);
        //未填写客户名
        if (!isset($client_name) || $client_name == "") {
            echo "2";
            exit();
        }
        $where = array();
        $where['status'] = 1;
        $where['client_name'] = $client_name;
        //如果是来自客户编辑
        if ($_GET['source'] == "edit" && isset($_GET['id'])) {
            $where['id'] = array('neq', trim($_GET['id']));
        }
        $client_db = D('Settlement.Client');
        $client_lists = $client_db->where($where)->select();
        if (count($client_lists) > 0) {
            //客户已存在
            echo "3";
            exit();
        }
        echo "0";
        exit();
    }

    /**
     * 编辑客户——显示
     */
    public function edit_client_show() {
        $id = trim($_GET['id']);
        if (!isset($id) || $id == "") {
            $this->error("error！");
        }
        //根据id取出数据
        $client_db = D("Settlement.Client");
        $tmp = $client_db->where("id=" . $id . " AND status = 1")->select();
        $dev_re = $client_db->table('pu_developer')->where(array('dev_id'=>$tmp[0]['dev_id']))->find();
        if (count($tmp) == 0) {
            $this->error("该客户id不存在！");
        }
        $tmp[0]['dev_name']=$dev_re['dev_name'];
        $this->assign("client", $tmp[0]);
        $this->assign("url_suffix", $_GET['url_suffix']);
        $this->display('add_client_show');
    }

    /**
     * 编辑客户——执行
     */
    public function edit_client_do() {
        if (!$this->isPost()) {
            $this->error('数据来源错误！');
        }
        $save_data['id'] = trim($_POST['id']);
        $save_data['client_name'] = trim($_POST['client_name']);
        $save_data['contact_name'] = trim($_POST['contact_name']);
        $save_data['contact_phone'] = trim($_POST['contact_phone']);
        //$save_data['update_tm'] = time();
        $save_data['cpd_contact_name'] = trim($_POST['cpd_contact_name']);
        $save_data['cpd_contact_phone'] = trim($_POST['cpd_contact_phone']);
		$save_data['cpd_company'] = trim($_POST['cpd_company']);
        $save_data['cpd_company_address'] = trim($_POST['cpd_company_address']);
        $save_data['client_type'] = trim($_POST['client_type']);
        $save_data['dev_id'] = trim($_POST['dev_id']);
        // $save_data['create_tm'] = time();
        $save_data['update_tm'] = time();
        $save_data['admin_id'] = $_SESSION['admin']['admin_id'];
        $save_data['admin_name'] = $_SESSION['admin']['admin_user_name'];
		
		//判断客户名称 和cpd的信息是否填写
        foreach ($save_data as $key=>$data) 
		{
			if($key=='contact_name'||$key=='contact_phone'||$key=='dev_id') continue;
            if ($data == "" || !isset($data)||$data == '0') {
                $this->error('数据不全！');
            }
        }
        // $this->showArr($save_data);

        $client_db = D('Settlement.Client');
        //判断客户是否存在
        $client_lists = $client_db->where("client_name = '" . $save_data['client_name'] . "' and status = 1 AND id != '" . $save_data['id'] . "'")->select();
        if (count($client_lists) > 0) {
            $this->error('客户已存在！');
        }
        //判断dev_id是否存在
        if($save_data['dev_id']){
           $dev_lists = $client_db->where("dev_id = '" . $save_data['dev_id'] . "' and status = 1 AND id != '" . $save_data['id'] . "'")->select();
            if (count($dev_lists) > 0) {
                $this->error('开发者账号已被关联！');
            } 
        }
        

        $log_result = $this->logcheck(array('id'=>$save_data['id']), 'settlement.ad_clients', $save_data, $client_db);
                
        if ($client_db->where("id='" . $save_data['id'] . "'")->save($save_data)) {
        	M('')->table('cpd_contract')->where(array('custom_id'=>$save_data['id']))->save(array('client_name'=>$save_data['client_name']));
        	//2014.11.6 jiwei 记录日志
        	$this->writelog("广告结算：编辑客户信息(client_id={$save_data['id']})".$log_result, 'sj_clients', $save_data['id'],__ACTION__ ,'','edit');
        	
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Client/index' . base64_decode($_POST['url_suffix']));
            $this->success('客户编辑成功！');
        } else {
            $this->error('客户编辑失败！');
        }
    }

    /**
     * 删除客户
     */
    public function delete_client() {
        $id = trim($_GET['id']);
        if (!isset($id) || $id == "") {
            $this->error("客户id号错误！");
        }
        //判断id号是否存在
        $map['id'] = $id;
        $client_db = D('Settlement.Client');
        $agreement_db = D('Settlement.Agreement');
        $contract_db = D('Settlement.Contract');
        if (count($client_db->where($map)->select()) == 0) {
            $this->error("客户id号不存在！");
        }
        //开始删除
        $save['status'] = 0;
        if ($client_db->where($map)->save($save)) {
        	
        	//2014.11.6 jiwei 记录日志
        	$this->writelog("广告结算：删除客户(client_id={$id})", 'sj_clients',$id,__ACTION__ ,'','del');
        	
            $where['client_id'] = $id;
            $agreement_db -> where($where) -> save($save);
            $contract_db -> where($where) -> save($save);
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Client/index' . base64_decode($_GET['url_suffix']));
            $this->success('客户删除成功！');
        } else {
            $this->error("客户删除失败！");
        }
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
    public function show_dev_name(){
        $client_db = D('Settlement.Client');
        $dev_name=$_POST['dev_name'];
        $where['status']=0;
        $where['dev_name'] = array('like', "%" . trim($dev_name) . "%");
        $dev_re = $client_db->table('pu_developer')->where($where)->limit(10)->select();
        if($dev_re){
            echo json_encode($dev_re);
        }else{
            echo 1;
        }
    }
	
	/*导入数据*/
	public function import_data() 
	{
		//添加客户信息 客户信息
		//
		// 处理文件上传
		//
		import("@.ORG.UploadFile");
		$info = NULL;
		$upload = new UploadFile();
		$upload->maxSize = 3145728;
		$upload->allowExts = array('csv');
		$upload->savePath = '/tmp/';//'/data/att/518/settlement/';
		$upload->saveRule = 'time';
		
		if(!$upload->upload())
		{
			echo "<script>alert('".$upload->getErrorMsg()."');location.href='/index.php/Settlement/Client/index';</script>";
			exit;
		}
		else 
			$info = $upload->getUploadFileInfo();
		
		if(is_null($info))
		{
			echo "<script>alert('没有获得上传文件信息！');location.href='/index.php/Settlement/Client/index'</script>";
			exit;
		}

		// 处理数据 读取csv数据
		$client_arr = $this->import_file_to_array($info[0]['savepath'].$info[0]['savename']);
		if ($client_arr == -1) 
		{
			echo "<script>alert('文件打开错误，请检查文件是否损坏！');location.href='/index.php/Settlement/Client/index';</script>";
			exit;
		} else if (empty($client_arr)) {
			echo "<script>alert('文件数据内容不能为空！');location.href='/index.php/Settlement/Client/index';</script>";
			exit;
		}
		
		//
		// 处理批量里面的客户信息
		//
		$client_db = D('Settlement.Client');
       
		$error_arr=array();
		
		 // 业务逻辑：检查行与行之间的数据是否冲突
        foreach($client_arr as $key1=>$val1) 
		{
            foreach($client_arr as $key2=>$val2) 
			{
                // 比较过的不比较
                if ($key1 >= $key2)
                    continue;
                // 如果客户名称不同，则不比较
                if ($val1['client_name'] != $val2['client_name'])
                    continue;
				
                // 客户名称相同，客户id不是同一个
                if ($val1['client_name'] == $val2['client_name'] && $val2['client_id'] != $val1['client_id']) 
				{
                    $k1 = $key1 + 2; $k2 = $key2 + 2;
					$error_arr[$k1]['message'].= "客户名称{$val1['client_name']}与第{$k2}行有重叠！;";
					$error_arr[$k2]['message'].= "客户名称{$val2['client_name']}与第{$k1}行有重叠！;";
                }
            }
        }
		
		$have_client_ids = array();
		
		//逻辑判断  和数据库中对比
		foreach($client_arr as $key => $val)
		{
			$line = $key+2; //包括头部
			
			if(empty($val['client_name']))
			{
				$error_arr[$line]['message'].= "客户名称不能为空！;";
			}
			if(empty($val['client_type']))
			{
				$error_arr[$line]['message'].= "客户类型不能为空！;";
			}
			if(mb_strlen($val['contact_name'],'utf-8')>10)
			{
				$error_arr[$line]['message'].= "cpt联系人名称10个字符以内！;";
			}
			if(mb_strlen($val['contact_phone'],'utf-8')>20)
			{
				$error_arr[$line]['message'].= "cpt联系人名称10个字符以内！;";
			}
			if(mb_strlen($val['cpd_contact_name'],'utf-8')>10||mb_strlen($val['cpd_contact_name'],'utf-8')==0)
			{
				$error_arr[$line]['message'].= "cpd联系人名称10个字符以内！;";
			}
			if(mb_strlen($val['cpd_contact_phone'],'utf-8')>20||mb_strlen($val['cpd_contact_phone'],'utf-8')==0)
			{
				$error_arr[$line]['message'].= "cpd联系人电话20个字符以内！;";
			}
			if(mb_strlen($val['cpd_company'],'utf-8')==0)
			{
				$error_arr[$line]['message'].= "cpd公司名称不能为空！;";
			}
			if(mb_strlen($val['cpd_company_address'],'utf-8')==0)
			{
				$error_arr[$line]['message'].= "cpd公司地址不能为空！;";
			}
		
			//先判断id是否存在
			$client_id = $client_db->where("id = '" . $val['client_id'] . "'")->select();
			if($client_id)
			{
				$have_client_ids[] = $val['client_id'];
				//查看数据库名称是否存在，类似编辑
				$client_lists = $client_db->where("client_name = '" . $val['client_name'] . "' and status = 1 and id != '".$val['client_id']."' ")->select();
			}
			else
			{
				//说明该id数据不存在，类似添加
				//判断客户名称在数据库是否存在
				$client_lists = $client_db->where("client_name = '" . $val['client_name'] . "' and status = 1 ")->select();
			}
			if(count($client_lists) > 0) 
			{
				$error_arr[$line]['message'].= "客户名称{$val['client_name']}已存在！";
			}
			
		}
		if($error_arr)
		{
			foreach($error_arr as $e => $v)
			{
				$error_msg_all .="第{$e}行，{$v['message']} \r\n";
			}
			$this->error($error_msg_all);
			exit;
		}
		else
		{
			//没有问题就添加
			foreach($client_arr as $k_p => $v_p)
			{
				$save_data['id'] = trim($v_p['client_id']);
				$save_data['client_name'] = trim($v_p['client_name']);
				$save_data['contact_name'] = trim($v_p['contact_name']);
				$save_data['cpd_contact_name'] = trim($v_p['cpd_contact_name']);
				$save_data['contact_phone'] = trim($v_p['contact_phone']);
				$save_data['cpd_contact_phone'] = trim($v_p['cpd_contact_phone']);
				$save_data['cpd_company'] = trim($v_p['cpd_company']);
				$save_data['cpd_company_address'] = trim($v_p['cpd_company_address']);
				$save_data['client_type'] = trim($v_p['client_type']);
				//$save_data['dev_id'] = trim($v_p['dev_id']);
				$save_data['create_tm'] = time();
				$save_data['update_tm'] = time();
				$save_data['admin_id'] = $_SESSION['admin']['admin_id'];
				$save_data['admin_name'] = $_SESSION['admin']['admin_user_name'];
				$save_data['status'] = 1;
		
				if(in_array($save_data['id'],$have_client_ids)) //已经存在 就根据id编辑
				{
					if($client_db->where(array('id'=>$save_data['id']))->save($save_data))
					{
						$this->writelog("广告结算-客户管理：编辑了client_id为{$save_data['id']}的客户",'sj_clients',$save_data['id'],__ACTION__ ,"","edit");
					}
					else
					{
						echo "<script>alert('创建数据错误！');location.href='/index.php/Settlement/Client/index'</script>";
						exit;
					}
				}
				else
				{
					$add_client_id = $client_db->add($save_data);
					if($add_client_id)
					{
						$this->writelog("广告结算-客户管理：添加了client_id为{$add_client_id}的客户",'sj_clients',$add_client_id,__ACTION__ ,"","add");
					}
					else
					{
						echo "<script>alert('创建数据错误！');location.href='/index.php/Settlement/Client/index'</script>";
						exit;
					}
				}
			}
			echo "<script>alert('添加编辑成功！');location.href='/index.php/Settlement/Client/index'</script>";
		}
	}
	
	//读取文件数据 
	// 第一行标题列忽略，只保存之后的内容
    function import_file_to_array($file) {
        $handle = fopen($file, "r");
        if ($handle === false) {
            return -1;
        }
        $i = $j = 0;
        $content_arr = array();
        while (($line_arr = $this->mygetcsv($handle, 1000, ",")) != FALSE) {
            if ($i == 0) {
                // 读入标题列
                $title_arr = $line_arr;
            } else {
                // 读入每行内容
                $content_arr[$j] = $line_arr;
                $j++;
            }
            $i++;
        }
        fclose($handle);
        // 自动检测并转化编码
        foreach($content_arr as $key => $record) {
            foreach($record as $r_key => $r_value) {
                $content_arr[$key][$r_key] = $this->convert_encoding($r_value);
				$content_arr[$key][$r_key] = trim($content_arr[$key][$r_key]);
            }
        }
		
		//转化为带键值的
		$content_title_arr = array(
            'client_id'     =>  '客户ID',
            'client_name' => '客户名称',
            'client_type' => '客户类型（1、直投，2、代理）',
            'contact_name'   =>  'CPT广告_联系人（10个字符内）',
		    'contact_phone'  =>   'CPT广告_联系方式',
            'cpd_contact_name'  =>   'CPD广告_联系人（10个字符内）',
            'cpd_contact_phone'  => 'CPD广告_联系方式',
			'cpd_company'  =>   'CPD广告_公司名称',
            'cpd_company_address'  => 'CPD广告_公司地址',
        );
		
		// 给$content_arr里的每一行记录的每一列下标由数字改成对应名称
        $new_content_arr = array();
        $new_key = array();
        // 将$correct_title_arr里的key值提取出来依次放在$new_key里
        foreach($content_title_arr as $k_t => $v_t) {
            $new_key[] = $k_t;
        }
        foreach($content_arr as $k_c=>$v_c) {
            foreach($new_key as $new_key_key=>$new_key_value) {
                if (isset($v_c[$new_key_key])) {
                    $new_content_arr[$k_c][$new_key_value] = $v_c[$new_key_key];
                }
            }
        }
        $content_arr = $new_content_arr;
		
        return $content_arr;
    }
	
}
