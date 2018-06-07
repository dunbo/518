<?php

class ConfigAction extends CommonAction {

    //系统扫描
    function scanlist(){
        $cjmodel = D('Caiji.Caiji');
        $rs = $cjmodel->getscanlist();
        $new = array_pop($rs);
        $this->assign('list',$rs);
        $this->assign('new',$new);
        
        // 新增的每个网站要分开配置
        $add_config_list = $cjmodel->getAddConfig();
        $this->assign('add_config_list', $add_config_list);
		
		//增加对网站分类的配置
		$add_category_config_list= $cjmodel->getAddCategoryConfig();
		$this->assign('add_category_config_list', $add_category_config_list);

        // 搜索失败的配置
        $search_fail_add_config_list = $cjmodel->getSearchFailAddConfig();
        $this->assign('search_fail_add_config_list', $search_fail_add_config_list);

        // 按包名尾缀限制
        $packagename_not_end_with_config_list = $cjmodel->getPackageNameConfigList('PACKAGENAME_NOT_END_WITH_CONFIG');
        $this->assign('packagename_not_end_with_config_list', $packagename_not_end_with_config_list);

        // 按包名包含限制
        $packagename_not_include_config_list = $cjmodel->getPackageNameConfigList('PACKAGENAME_NOT_INCLUDE_CONFIG');
        $this->assign('packagename_not_include_config_list', $packagename_not_include_config_list);
        
        $this->display();
    }
    
    function packagename_include_exclude_list() {
        $cjmodel = D("Caiji.Caiji");
        $where = array(
            'status' => 1
        );
        $count = $cjmodel->getPackageNameIncludeExcludeListCount($where);
        import("@.ORG.Page");
        $page = new Page($count, 10);
        $list = $cjmodel->getPackageNameIncludeExcludeList($where, $page->firstRow, $page->listRows);

        $this->assign('page', $page->show());
        $this->assign('list', $list);
        $this->display();
    }

    function edit_scan(){
        $cjmodel = D('Caiji.Caiji');
        $id = $_GET['id'];
        $this->assign('id',$id);
        $res = $cjmodel->getscaninfo($id);
        $this -> assign('res',$res);

        if($this->isPost())
        {
            $id = $_POST['id'];
            $download = $_POST['download'];
            $dev_name = $_POST['dev_name'];

            $data = array();
            $data['download']=$download;
            $data['dev_name']=$dev_name;
            $data['update_tm']=time();
            $data['os_username']=$_SESSION['admin']['admin_user_name'];
            $log = $this->logcheck(array('id'=>$id),'cj_scan_config',$data,$cjmodel);
            $this->writelog('系统扫描配置-版本更新编辑了id为'.$id.',的数据'.$log,'cj_scan_config',$id,__ACTION__ ,"","edit");
            $rs = $cjmodel->savescan($id,$download,$dev_name);
            echo 1;exit(0);
        }
        $this->display();
    }

    function update_status()
    {
        $cjmodel = D('Caiji.Caiji');
        if($this->isAjax())
        {
            $id = $_POST['h_id'];
            $status = $_POST['status_v'];
            $data=array();
            $data['status']= $status;
            $data['update_tm']=time();
            $data['os_username']=$_SESSION['admin']['admin_user_name'];
            $log = $this->logcheck(array('id'=>$id),'cj_scan_config',$data,$cjmodel);
            $this->writelog('采集-系统扫描配置,编辑了id为'.$id.',的数据'.$log,'cj_scan_config',$id,__ACTION__ ,"","edit");
            $rs = $cjmodel->updatestatus($id,$status);
            if($rs>0){
                echo 1;exit(0);
            }
        }
    }

    function update_status_mail()
    {
        $cjmodel = D('Caiji.Caiji');
        if($this->isAjax())
        {
            $id = $_POST['h_id'];
            $status = $_POST['status_v'];
            $data = array();
            $data['status']= $status;
            $data['update_tm']=time();
            $data['os_username']=$_SESSION['admin']['admin_user_name'];
            $log = $this->logcheck(array('id'=>$_POST['h_id']),'cj_email_config',$data,$cjmodel);
            $this->writelog('采集-邮件配置，改变了id为'.$_POST['h_id'].',的状态'.$log,'cj_email_config',$_POST['h_id'],__ACTION__ ,"","edit");
            $rs = $cjmodel->updatestatus_mail($id,$status);
            if($rs>0){
                echo 1;exit(0);
            }
        }
    }

    //邮件扫描
    function mailist(){
        $cjmodel = D('Caiji.Caiji');
        $rs = $cjmodel->getmailist();
        $this->assign('list',$rs);
        $this->display();
    }

    //修改备注
    function edit_desc()
    {
        $id = $_GET['id'];
        $cjmodel = D('Caiji.Caiji');
        $rs = $cjmodel->getscaninfo($id);
        $this->assign('desc',$rs['desc']);
        $this->assign('id',$id);

        if($this->isPost())
        {
            $id = $_POST['id'];
            $desc = $_POST['desc'];

            $data = array();
            $data['desc']= $desc;
            $data['update_tm']=time();
            $data['os_username']=$_SESSION['admin']['admin_user_name'];
            $log = $this->logcheck(array('id'=>$id),'cj_scan_config',$data,$cjmodel);
            $this->writelog('采集-系统扫描配置,编辑了id为'.$id.',的备注'.$log,'cj_scan_config',$id,__ACTION__ ,"","edit");
            $rs = $cjmodel->updatedesc($id,$desc);
            if($rs>0){
                echo 1;exit(0);
            }
        }

        $this->display();
    }

    function edit_scan_new()
    {
        $id = $_GET['id'];
        $cjmodel = D('Caiji.Caiji');
        $res = $cjmodel->getscaninfo($id);
        $this->assign('res',$res);
        $this->assign('id',$id);
        if($this->isPost())
        {
            $data = array();
            $data['category_key']=$_POST['category_key'];
            $data['score']=$_POST['score'];
            $data['download']=$_POST['download'];
            $data['dev_name']=$_POST['dev_name'];
            $data['update_tm']=time();
            $data['os_username']=$_SESSION['admin']['admin_user_name'];
            $log = $this->logcheck(array('id'=>$_POST['id']),'cj_scan_config',$data,$cjmodel);
            $this->writelog('采集-系统扫描配置,编辑了id为'.$_POST['id'].',的数据'.$log,'cj_scan_config',$_POST['id'],__ACTION__ ,"","edit");
            $rs = $cjmodel->savescan_new($_POST);
            if($rs>0){
                echo 1;exit(0);
            }
        }
        $this->display();
    }

    //编辑邮件
    function edit_mail()
    {
        $id = $_GET['id'];
        $cjmodel = D('Caiji.Caiji');
        $rs = $cjmodel->getmailinfo($id);
        $this->assign('info',$rs);
        $this->assign('id',$id);

        if($this->isPost())
        {
            $data = array();
            $data['send_mails']=$_POST['send_mails'];
            $data['cc_mails']=$_POST['cc_mails'];
            $data['update_tm']=time();
            $data['os_username']=$_SESSION['admin']['admin_user_name'];
            $log = $this->logcheck(array('id'=>$_POST['id']),'cj_email_config',$data,$cjmodel);
            $this->writelog('采集-邮件配置编辑了id为'.$_POST['id'].',的数据'.$log,'cj_email_config',$_POST['id'],__ACTION__ ,"","edit");
            $rs = $cjmodel->savemail($_POST);
            if($rs>0){
                echo 1;exit(0);
            }
        }

        $this->display();
    }

    /**
     *  黑名单
     */
    function black_list()
    {
        $cj_model = D('Caiji.Caiji');
        $res = $cj_model->get_black_list($_GET);

        if(isset($_GET['down'])){
            $ua = $_SERVER["HTTP_USER_AGENT"];
            $filename = date('Y-m-d');
            $filename = "采集后台_采集黑名单_".$filename.".csv";
            $res = $cj_model->get_black_list($_GET,2);
            $allist = $res['list'];
            header("Content-type:application/vnd.ms-excel");
            //header("content-Disposition:filename=采集后台_待采集_".time().".csv");
            if (preg_match("/MSIE/", $ua)) {
                header('Content-Disposition: attachment; filename="' . $encoded_filename . '"');
            } else if (preg_match("/Firefox/", $ua)) {
                header('Content-Disposition: attachment; filename*="utf8\'\'' . $filename . '"');
            } else {
                header('Content-Disposition: attachment; filename="' . $filename . '"');
            }

            $desc ="软件名称,包名,下载量,类型,备注\r\n";
            foreach($allist as $v)
            {
                $softname= $v['softname'];
                /*
                $softname= str_replace('?','',$softname);
                $softname= str_replace('?','',$softname);
                $softname= str_replace('?','',$softname);
                 */
                if($v['type']==1){
                    $type = '采集忽略';
                }
                if($v['type']==2){
                    $type = '人工添加';
                }
                $desc = $desc.'"'.$softname.'",'.$v['package'].',"'.number_format($v['download_count']).'","'.$type.'",'.$v['desc']."\r";
            }
            echo chr(0xEF).chr(0xBB).chr(0xBF);
            echo $desc;
            exit(0);
        }

        $zhuangtai= isset($_GET['zhuangtai'])?$_GET['zhuangtai']:-1;
        $this->assign("zhuangtai" , $zhuangtai);
        $this->assign("get" , $_GET);

        $new_data = $res['list'];
        foreach($res['list'] as $k=>$v)
        {
            $new_data[$k]['download_count'] = number_format($v['download_count']);
        }
        $this->assign("list" , $new_data);
        $this->assign("page" , $res['page']);
        $this->assign("count" , $res['count']);
        $this->display();
    }

    /**
     *  批量导入csv
     */
    function import_dialog() {
        if (isset($_GET['mobandown'])) {
            $path = C('BAOBIAO_PATH');
            $file_dir = $path . 'black.csv';
            if (!file_exists($file_dir)) {
                header("Content-type: text/html; charset=utf-8");
                echo "File not found!";
                exit;
            } else {
                $file = fopen($file_dir, "r");
                Header("Content-type: application/octet-stream");
                Header("Accept-Ranges: bytes");
                Header("Accept-Length: " . filesize($file_dir));
                Header("Content-Disposition: attachment; filename=moban.csv");
                echo fread($file, filesize($file_dir));
                fclose($file);
                exit(0);
            }
        }
        if ($_POST) {
            $tmp_name = $_FILES['upload']['tmp_name'];

            $tmp_houzhui = $_FILES['upload']['name'];
            $tmp_arr = explode('.', $tmp_houzhui);
            $houzhui = array_pop($tmp_arr);
            if (strtoupper($houzhui) != 'CSV') {
                echo 2;
                exit(0);
            }

            $arr = $this->readcsv($tmp_name);
            if ($arr === false) {
                echo 2;
                exit(0);
            }
            $this->writelog("采集-黑名单-批量导入了数据,id为{$arr}",'cj_black_list',$arr,__ACTION__ ,"","add");
            $this->ajaxReturn($arr, '导入成功！', 1);
        }
        $this->display('import_dialog');
    }

    function readcsv($file) {
        $arr = array();
        $title = array();
        //$file="st.csv";
        $handel = fopen($file, "r");
        $i = 0;

        while (($num_arr = $this->mygetcsv($handel, 1000, ",")) !== FALSE) {
            //标题行不写入
            if ($i != 0) {
                $arr[$i] = $num_arr;
            } else {
                $title[$i] = $num_arr;
            }
            $i++;
        }

        if (count($title[0]) != 3) {
            return false;
        }

        $cj_model = D('Caiji.Caiji');
        $rs = $cj_model->importadd($arr);
        fclose($handel);
        return $rs;
    }

    //保存黑名单
    function save_black_dialog()
    {
        $id = $_GET['id'];
        $cj_model = D('Caiji.Caiji');
        $info = $cj_model->getblackinfo($id);
        $this->assign('info',$info);

        if($this->isPost())
        {
            $id = $_POST['bid'];
            $old_pkg = $_POST['old_pkg'];
            unset($_POST['bid']);
            unset($_POST['old_pkg']);
            if(strlen($id>0))
            {
                if($_POST['package']!=$old_pkg)
                {
                    $ret = $cj_model->get_cj_black_list($_POST['package']);
                    if(!empty($ret))
                    {
                        echo 2;exit(0);
                    }
                }

                $_POST['update_tm'] = time();
                $log = $this->logcheck(array('id'=>$id),'cj_black_list',$_POST,$cj_model);
                $this->writelog('配置管理-黑名单列表,编辑了id为'.$id.',的数据'.$log,'cj_black_list',$id,__ACTION__ ,"","edit");
                $cj_model->save_black($id,$_POST);
            }else
            {
                $ret = $cj_model->get_cj_black_list($_POST['package']);
                if(!empty($ret))
                {
                    echo 2;exit(0);
                }
                $model = new model();
                $res = $model->table('sj_soft')->field('total_downloaded')->where('status=1 and hide=1 and package="'.$_POST['package'].'"')->find();
                $_POST['download_count'] = $res['total_downloaded'];//根据包名查一个下载量
                $_POST['create_tm'] = time();
                $insertid = $cj_model->add_black($_POST);
                $this->writelog('配置管理-黑名单列表,新增了id为'.$insertid.'的数据,包名为'.$_POST['package'].',软件名为'.$_POST['softname'].',备注为'.$_POST['desc'],'cj_black_list',$insertid,__ACTION__ ,"","add");
            }
            echo 1;exit(0);
        }

        $this->display();
    }

    function deleteblack()
    {
        if($this->isPost())
        {
            $id = $_POST['id'];
            $cj_model = D('Caiji.Caiji');
            $info = $cj_model->delblack($id);
            $this->writelog('配置管理-黑名单列表,删除了id为'.$id.',的数据','cj_black_list',$id,__ACTION__ ,"","del");
            echo 1;exit(0);
        }
    
    }

    //版本更新待采集 编辑
    function edit_standby()
    {
        $cj_model = D('Caiji.Caiji');
        $id = $_GET['id'];
        $rs = $cj_model->get_standby_info($id);

        if($this->isPost())
        {
            $model = new model();
            $data = array();
            $data['package']=$_POST['package'];
            $package = $_POST['package'];
            $res = $model->table('sj_soft')->where("package = '$package' and status=1 and hide=1")->find();
            if($res==null){
                $res = $model->table('sj_soft_tmp')->where("package = '$package' and status=2")->find();
                if($res==null){
                    echo -2;exit(0);
                }
            }

            $cid = str_replace(',','',$res['category_id']);
            $sql = "SELECT parentid FROM sj_category WHERE category_id = (SELECT parentid FROM sj_category WHERE category_id = $cid)";
            $ret = $model->query($sql);
            $cname = $ret[0]['parentid']==1?'应用':'游戏';

            $data['softname']=$_POST['softname'];
            $data['examine_type']=$_POST['examine'];
            $data['desc']=$_POST['desc'];
            $data['package_from']=2;
            if($_POST['ptype']==1)
            {
                $log = $this->logcheck(array('id'=>$_POST['pid']),'cj_standby_fetch',$data,$cj_model);
                $this->writelog('采集-版本更新待采集,编辑了id为'.$_POST['pid'].',的数据'.$log,'cj_standby_fetch',$_POST['pid'],__ACTION__ ,"","edit");
            
            }else if($_POST['ptype']==2)
            {
                $this->writelog('采集-版本更新待采集,新增了包名为'.$_POST['package'].',软件名为'.$_POST['softname'].',审核流程类型为'.$_POST['examine'].',备注为'.$_POST['desc'].'的数据','cj_standby_fetch',$_POST['package'],__ACTION__ ,"","add");
            }
            $rs = $cj_model->savestandby($_POST,$cname);

            echo $rs;exit(0);
        }

        $this->assign('type',$_GET['type']);
        $this->assign('info',$rs);
        $this->display();
    }
    
    // 编辑新增网站的限制条件
    function edit_add_config() {
        $cjmodel = D('Caiji.Caiji');
        if ($_POST) {
            $id = $_POST['id'];
            $download_limit = trim($_POST['download_limit']);
            if ($download_limit && !preg_match('/^\d+(\.\d+)?$/', $download_limit)) {
                $this->error("请填写正确的下载限制");
            }
            $category_limit = trim($_POST['category_limit']);
            // 处理一下，1，去重，2，如果不为空，前后加上,方便匹配
            if ($category_limit != '') {
                $arr = array_unique(array_filter(explode(',', $category_limit), 'strlen'));
                if (!empty($arr)) {
                    $category_limit = ',' . implode(',', $arr) . ',';
                } else {
                    $category_limit = '';
                }
            }
            $edit_limit_update_time = time();
            $edit_limit_os_id = $_SESSION['admin']['admin_id'];
            $where = array('id' => $id);
            $data = array();
            $data['download_limit'] = $download_limit;
            $data['category_limit'] = $category_limit;
            $data['edit_limit_update_time'] = $edit_limit_update_time;
            $data['edit_limit_os_id'] = $edit_limit_os_id;
			if(strtotime(trim($_POST['web_update'])))
			{
				$data['website_update_time_limit']=strtotime(trim($_POST['web_update']));
			}
			else
			{
				$data['website_update_time_limit']="";
			}
            $log = $this->logcheck($where, 'cj_add_package_website_config', $data, $cjmodel);
            $ret = $cjmodel->saveAddConfig($where, $data);
            if ($ret) {
                // 记日志
                $this->writelog("采集/质管_配置管理_系统扫描配置_新增第三方网站限制配置:编辑了id为{$id}的记录，{$log}",'cj_add_package_website_config',$id,__ACTION__ ,"","edit");
                $this->success("编辑成功！");
            } else {
                $this->error("编辑失败！");
            }
        } else {
            $id = $_GET['id'];
            $find_list = $cjmodel->getAddConfig($id);
            $find = $find_list[0];
            // 将category_limit前后trim掉,再展示
            $find['category_limit'] = trim($find['category_limit'], ',');
			//最近更新时间显示样式
			$web_time=$find['website_update_time_limit'] ? date('Y-m-d',$find['website_update_time_limit']) : "";
			if($web_time=="1970-01-01")
			{
				$find['web_time']="";
			}
			else
			{
				$find['web_time']=$web_time;
			}
			
            $this->assign('list', $find);
            $this->display();
        }
    }
    
	//添加分类配置 added by shitingting 2015/3/23
	function add_category()
	{
		$cjmodel = D('Caiji.Caiji');
        if ($_POST) 
		{
            $appfrom = $_POST['appfrom'];
			$download_limit = trim($_POST['download_limit']);
			if($download_limit!='')
			{
				if ($download_limit && !preg_match('/^\d+(\.\d+)?$/', $download_limit)) 
				{
					$this->error("请填写正确的下载限制");
				}
				else
				{
					 $data['download_limit'] = $download_limit;
				}
			}
            else
			{
				$data['download_limit'] ="";
			}
			if(trim($_POST['web_update'])!='')
			{
				$data['website_update_time_limit']=strtotime(trim($_POST['web_update']));
			}
            else
			{
				$data['website_update_time_limit']=0;
			}
            $category_name = trim($_POST['category_name']);
            //判断是否为空，判断同一站点分类名称是否重复
            if ($category_name != '') 
			{
				$category=array_filter(explode(',', $category_name), 'strlen');
                $arr = array_unique($category);
                if (!empty($arr)) 
				{
					if(sizeof($category)!=sizeof($arr))
					{
						$this->error("同一站点分类不能重复");
					}
					else
					{
						$check_have=$cjmodel->check_category($arr,$appfrom);
						if($check_have)
						{
							$this->error("同一站点分类不能重复");
						}
						else
						{
							 $category_name = ',' . implode(',', $arr) . ',';
						}
					} 
                } 
				else 
				{
					$this->error("分类不能为空");
                }
            }
            $edit_limit_os_id = $_SESSION['admin']['admin_id'];
			$data['website_id'] = $appfrom;
            $data['category_name'] = $category_name;
            $data['edit_limit_os_id'] = $edit_limit_os_id;
			$data['create_tm'] = time();
			$data['update_tm'] = time();
			$data['status'] = 1;
			
            $ret = $cjmodel->table('cj_add_category_config')->add($data);
            if ($ret) {
                // 记日志
                $this->writelog("采集/质管_配置管理_系统扫描配置_新增网站分类配置:添加了站点为【{$appfrom}】,id为【{$ret}】的记录",'cj_add_category_config',$ret,__ACTION__ ,"","add");
                $this->success("添加成功！");
            } else {
                $this->error("添加失败！");
            }
        } 
		else 
		{
			//来源网站	
			$website_list = $cjmodel ->table('cj_add_package_website_config')->where(array('status'=>1))->field("id,website_name")->order('priority asc')->group('website_name')->select();
			$this -> assign('website_list',$website_list);
            $this->display();
        }
	}
	
	//编辑分类配置 added by shitingting 2015/3/24
	function edit_category()
	{
		$cjmodel = D('Caiji.Caiji');
        if ($_POST) 
		{
			$id=$_POST['id'];
            $appfrom = $_POST['appfrom'];
			$download_limit = trim($_POST['download_limit']);
			if($download_limit!='')
			{
				if ($download_limit && !preg_match('/^\d+(\.\d+)?$/', $download_limit)) 
				{
					$this->error("请填写正确的下载限制");
				}
				else
				{
					 $data['download_limit'] = $download_limit;
				}
			}
            else
			{
				$data['download_limit'] ="";
			}
			if(trim($_POST['web_update'])!='')
			{
				$data['website_update_time_limit']=strtotime(trim($_POST['web_update']));
			}
            else
			{
				$data['website_update_time_limit']=0;
			}
            $category_name = trim($_POST['category_name']);
            //判断是否为空，判断同一站点分类名称是否重复
            if ($category_name != '') 
			{
				$category=array_filter(explode(',', $category_name), 'strlen');
                $arr = array_unique($category);
                if (!empty($arr)) 
				{
					if(sizeof($category)!=sizeof($arr))
					{
						$this->error("同一站点分类不能重复");
					}
					else
					{
						$check_have=$cjmodel->check_category($arr,$appfrom,$id);
						if($check_have)
						{
							$this->error("同一站点分类不能重复");
						}
						else
						{
							 $category_name = ',' . implode(',', $arr) . ',';
						}
					} 
                } 
				else 
				{
					$this->error("分类不能为空");
                }
            }
            $edit_limit_os_id = $_SESSION['admin']['admin_id'];
			$data['website_id'] = $appfrom;
            $data['category_name'] = $category_name;
            $data['edit_limit_os_id'] = $edit_limit_os_id;
			$data['update_tm'] = time();
			$data['status'] = 1;
            $log_result = $this->logcheck(array('id'=>$id),'cj_add_category_config',$data,$cjmodel);
            $ret = $cjmodel->table('cj_add_category_config')->where(array('id'=>$id))->save($data);
            if ($ret) {
                // 记日志
                $this->writelog("采集/质管_配置管理_系统扫描配置_新增网站分类配置:编辑了站点为【{$appfrom}】,id为【{$id}】的记录.{$log_result }",'cj_add_category_config',$id,__ACTION__ ,"","edit");
                $this->success("编辑成功！");
            } else {
                $this->error("编辑失败！");
            }
        } 
		else 
		{
			//来源网站	
			$website_list = $cjmodel ->table('cj_add_package_website_config')->where(array('status'=>1))->field("id,website_name")->order('priority asc')->group('website_name')->select();
			$this -> assign('website_list',$website_list);
			//获取分类信息
			$id = $_GET['id'];
            $find_list = $cjmodel->getAddCategoryConfig($id);
            $find = $find_list[0];
			
            // 将category_name前后trim掉,再展示
            $find['category_name'] = trim($find['category_name'], ';');
			//最近更新时间显示样式
			$web_time=$find['website_update_time_limit'] ? date('Y-m-d',$find['website_update_time_limit']) : "";
			if($web_time=="1970-01-01")
			{
				$find['web_time']="";
			}
			else
			{
				$find['web_time']=$web_time;
			}
            $this->assign('list', $find);
            $this->display();
        }
	}
	
	//删除分类配置 added by shitingting 2015/3/24
	function delete_category()
	{
		$cjmodel = D('Caiji.Caiji');
		$id = $_GET['id'];
		$data = array(
            'status' => 0,
            'update_tm' => time()
        );
		$result = $cjmodel->table('cj_add_category_config')->where(array('id'=>$id))->save($data);
		if($result)
		{
			$this->writelog("采集/质管_配置管理_系统扫描配置_新增网站分类配置:删除了id为{$id}的分类配置",'cj_add_category_config',$id,__ACTION__ ,"","del");
			$this->success("删除成功");
		}
		else
		{
			$this->error("删除失败");
		}
	}
	
    // 编辑搜索失败的添加限制条件
    function edit_search_fail_add_config() {
        $cjmodel = D('Caiji.Caiji');
        if ($_POST) {
            $download_limit = trim($_POST['download_limit']);
            if ($download_limit && !preg_match('/^\d+(\.\d+)?$/', $download_limit)) {
                $this->error("请填写正确的下载限制");
            }
            $category_limit = trim($_POST['category_limit']);
            // 处理一下，1，去重，2，如果不为空，前后加上,方便匹配
            if ($category_limit != '') {
                $arr = array_unique(array_filter(explode(',', $category_limit), 'strlen'));
                if (!empty($arr)) {
                    $category_limit = ',' . implode(',', $arr) . ',';
                } else {
                    $category_limit = '';
                }
            }
            $update_time = time();
            $os_id = $_SESSION['admin']['admin_id'];
            $where = array('config_code' => 'SEARCH_FAIL_ADD_CONFIG');
            $data = array();
            $config_content = array();
            $config_content ['download_limit'] = $download_limit;
            $config_content ['category_limit'] = $category_limit;
            $data['config_content'] = json_encode($config_content);
            $data ['update_time'] = $update_time;
            $data ['os_id'] = $os_id;
            $log = $this->logcheck($where, 'cj_scan_add_config', $data, $cjmodel);
            $ret = $cjmodel->saveSearchFailAddConfig($where, $data);
            if ($ret) {
                // 记日志
                $this->writelog("采集/质管_配置管理_系统扫描配置_搜索失败新增配置:{$log}",'cj_scan_add_config',"config_code:SEARCH_FAIL_ADD_CONFIG",__ACTION__ ,"","edit");
                $this->success("编辑成功！");
            } else {
                $this->error("编辑失败！");
            }
        } else {
            $find = $cjmodel->getSearchFailAddConfig();
            $this->assign('list', $find);
            // 将category_limit前后trim掉,再展示
            $find['category_limit'] = trim($find['category_limit'], ',');
            $this->display();
        }
    }
    
    // 编辑包名限制
    function edit_packagename_end_with_config() {
        $cjmodel = D('Caiji.Caiji');
        if ($_POST) {
            $config_code = 'PACKAGENAME_NOT_END_WITH_CONFIG';
            $config_content = $_POST['config_content'];

            // config_content去重
            $arr = array_unique(array_filter(explode(',', $config_content), 'strlen'));
            if (!empty($arr)) {
                // 先把每个的前后去掉
                foreach ($arr as $key => $tobetested) {
                    $arr[$key] = trim($tobetested);
                }
                // 检查填写内容是否合法，包名只能以小写字母和下划线做首字母，随后的名字中只能出现 [a-z0-9_.] 这些字符
                $legal_reg = '/^[\w.]+$/';
                foreach ($arr as $tobetested) {
                    if (!$tobetested)
                        continue;
                    if (!preg_match($legal_reg, $tobetested)) {
                        $this->error("排除尾缀只能由字母、下划线、点、数字组成");
                    }
                }
                $config_content = ',' . implode(',', $arr) . ',';
            } else {
                $config_content = '';
            }
            $where = array(
                'config_code' => $config_code,
                'status' => 1
            );
            $data = array(
                'config_content' => $config_content,
                'os_id' => $_SESSION['admin']['admin_id'],
                'update_time' => time()
            );
            $log = $this->logcheck($where, 'cj_scan_add_config', $data, $cjmodel);
            $ret = $cjmodel->savePackageNameConfig($where, $data);
            if ($ret) {
                // 记日志
                $this->writelog("采集/质管_配置管理_系统扫描配置_按包名尾缀限制:{$log}",'cj_scan_add_config',"config_code:{$config_code}",__ACTION__ ,"","edit");
                $this->success("编辑成功！");
            } else {
                $this->error("编辑失败！");
            }
        } else {
            $config_code = 'PACKAGENAME_NOT_END_WITH_CONFIG';
            $find = $cjmodel->getPackageNameConfigList($config_code);
            $find['config_content'] = trim($find['config_content'], ',');
            $this->assign('list', $find);
            $this->display();
        }
    }
    
    function add_include_exclude_keyword() {
        if ($_POST) {
            $keyword = trim($_POST['keyword']);
            if ($keyword == '') {
                $this->error("关键字不能为空");
            }
            // 检查填写内容是否合法，包名只能以小写字母和下划线做首字母，随后的名字中只能出现 [a-z0-9_.] 这些字符
            $legal_reg = '/^[\w.]+$/';
            if (!preg_match($legal_reg, $keyword)) {
                $this->error("关键字只能由字母、下划线、点、数字组成");
            }
            $remark = trim($_POST['remark']);
            $now = time();
            $data = array(
                'keyword' => $keyword,
                'remark' => $remark,
                'create_time' => $now,
                'update_time' => $now,
                'status' => 1,
            );
            $cjmodel = D('Caiji.Caiji');
            $ret = $cjmodel->addIncludeExcludeKeyword($data);
            if ($ret) {
                $this->writelog("采集/质管_配置管理_系统扫描配置_按包名包含限制:添加了id为{$ret}的记录",'cj_packagename_include_exclude',$ret,__ACTION__ ,"","add");
                $this->success("添加成功！");
            } else {
                $this->error("添加失败！");
            }
        } else {
            $this->display();
        }
    }
    
    function edit_include_exclude_keyword() {
        $cjmodel = D('Caiji.Caiji');
        if ($_POST) {
            $id = $_POST['id'];
            $keyword = trim($_POST['keyword']);
            if ($keyword == '') {
                $this->error("关键字不能为空");
            }
            // 检查填写内容是否合法，包名只能以小写字母和下划线做首字母，随后的名字中只能出现 [a-z0-9_.] 这些字符
            $legal_reg = '/^[\w.]+$/';
            if (!preg_match($legal_reg, $keyword)) {
                $this->error("关键字只能由小写字母、下划线、点、数字组成");
            }
            $remark = trim($_POST['remark']);
            $now = time();
            $where = array(
                'id' => $id,
                'status' => 1
            );
            $data = array(
                'keyword' => $keyword,
                'remark' => $remark,
                'update_time' => $now,
                'status' => 1,
            );
            $log = $this->logcheck($where, 'cj_packagename_include_exclude', $data, $cjmodel);
            $ret = $cjmodel->editIncludeExcludeKeyword($where, $data);
            if ($ret) {
                // 记日志
                $this->writelog("采集/质管_配置管理_系统扫描配置_按包名包含限制:编辑了id为{$id}的记录，{$log}",'cj_packagename_include_exclude',$id,__ACTION__ ,"","edit");
                $this->success("编辑成功！");
            } else {
                $this->error("编辑失败！");
            }
        } else {
            $id = $_GET['id'];
            $find = $cjmodel->getIncludeExcludeKeyword($id);
            $this->assign('list', $find);
            $this->display();
        }
    }
    
    function delete_include_exclude_keyword() {
        $cjmodel = D('Caiji.Caiji');
        $id = $_GET['id'];
        $where = array(
            'id' => $id,
        );
        $data = array(
            'status' => 0,
            'update_time' => time()
        );
        $ret = $cjmodel->editIncludeExcludeKeyword($where, $data);
        if ($ret) {
            $this->writelog("采集/质管_配置管理_系统扫描配置_按包名包含限制:删除了id为{$id}",'cj_packagename_include_exclude',$id,__ACTION__ ,"","del");
            $this->success("删除成功！");
        } else {
            $this->error("删除失败！");
        }
    }
}
