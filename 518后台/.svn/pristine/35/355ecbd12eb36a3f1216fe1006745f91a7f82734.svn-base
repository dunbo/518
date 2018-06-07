<?php
class NewsoftwarelistAction extends CommonAction {


	private $lists;             //列表
	private $conf_db;           //配置表
	private $config_list;       //配置列表
	private $hashs;             //默认hashs
	private $map;               //条件
	private $resolution_db;     //分辨率表dsd
	private $resolution_list;   //分辨率列表
	private $category_db;       //类别表
	private $category_list_parent;     //类别列表
	private $category_list_child;     //类别列表
	private $soft_db;           //软件表
	private $soft_list;         //软件列表
	private $soft_note_db;      //软件附属表
	private $soft_note_list;    //软件附属列表
	private $soft_thumb_db;      //软件图片表
	private $soft_thumb_list;    //软件图片列表
	private $soft_file_db;      //软件附属表
	private $soft_file_list;    //软件附属列表
	private $operating_db;      //运营商表
	private $operating_list;    //运营商列表
	private $scan_result_db;    //软件扫描结果表
	private $scan_result_list;  //软件扫描结果列表
	private $softid;            //软件id
	private $sid;               //临时ID
	private $apklist;           //APK列表
	private $imagelist;         //APK列表
	private $order;             //排序
	private $oldsoftlist;       //原软件信息
	private $denyid;            //驳回ID
	private $denymsg;           //驳回信息
	private $cjid;              //采集软件表id

    /*
	function index(){
		$model = new Model();
		$where['status'] = 1;
		$need = $model -> table('cj_partner') -> where($where) -> select();
		foreach($need as $key => $val){
			$need_arr[$val['packagename']] = 1;
		}
		$cond = array();
		$cond[] = "new_status=1";

                $sqlparam ='';
                if(isset($_GET['softname']))
                {
                    $sqlparam = $sqlparam.'softname='.$_GET['softname'].'&';
                    $softname = $_GET['softname'];
                    $this->assign("softname", $softname);
                    $cond[] = "new_sname like '%$softname%'";
                }
                if(isset($_GET['package']))
                {
                    $sqlparam = $sqlparam.'package='.$_GET['package'].'&';
                    $package = $_GET['package'];
                    $this->assign("package", $package);
                    $cond[] = "package='$package'";
                }

		if (isset($_GET['begintime']) && isset($_GET['endtime']))
		{
                    $sqlparam = $sqlparam.'begintime='.$_GET['begintime'].'&';
                    $sqlparam = $sqlparam.'endtime='.$_GET['endtime'].'&';
                    $begintime = $_GET['begintime'];
                    $endtime = $_GET['endtime'];
                    $this->assign("begintime", $begintime);
                    $this->assign("endtime", $endtime);
                    $cond[] = "new_sdate between unix_timestamp('$begintime') and unix_timestamp('$endtime')+86399";
		}



		if (!empty($_POST['softname']))
		{
                    $sqlparam = $sqlparam.'softname='.$_POST['softname'].'&';
                    $softname = $_POST['softname'];
                    $this->assign("softname", $softname);
                    $cond[] = "new_sname like '%$softname%'";
		}
		if (!empty($_POST['package']))
		{
                    $sqlparam = $sqlparam.'package='.$_POST['package'].'&';
                    $package = $_POST['package'];
                    $this->assign("package", $package);
                    $cond[] = "package='$package'";
		}
		if (!empty($_POST['begintime']) && !empty($_POST['endtime']))
		{
                    $sqlparam = $sqlparam.'begintime='.$_POST['begintime'].'&';
                    $sqlparam = $sqlparam.'endtime='.$_POST['endtime'].'&';
                    $begintime = $_POST['begintime'];
                    $endtime = $_POST['endtime'];
                    $this->assign("begintime", $begintime);
                    $this->assign("endtime", $endtime);
                    $cond[] = "new_sdate between unix_timestamp('$begintime') and unix_timestamp('$endtime')+86399";
		}
        
        $cond[] = "az_downloaded > 50000";

		$wherestring = implode(" and ", $cond);

                if(isset($_GET['downbegin'])>0)
                {
                    if($_REQUEST['downtype']==1)
                    {
                        $sqlparam = $sqlparam.'downbegin='.$_GET['downbegin'].'&';
                        $sqlparam = $sqlparam.'downtype='.$_REQUEST['downtype'].'&';
                        if(strlen($_GET['downend'])>0)
                        {
                            $sqlparam = $sqlparam.'downend='.$_GET['downend'].'&';
                        $sqlparam = $sqlparam.'downtype='.$_REQUEST['downtype'].'&';
                            $wherestring =$wherestring." and az_downloaded >= ".$_GET['downbegin']." and az_downloaded  <=".$_GET['downend'];
                        }else
                        {
                            $wherestring =$wherestring." and az_downloaded >= ".$_GET['downbegin'];
                        }
                    
                    }

                    if($_REQUEST['downtype']==2)
                    {
                        $sqlparam = $sqlparam.'downbegin='.$_GET['downbegin'].'&';
                        $sqlparam = $sqlparam.'downtype='.$_REQUEST['downtype'].'&';
                        if(strlen($_GET['downend'])>0)
                        {
                            $sqlparam = $sqlparam.'downend='.$_GET['downend'].'&';
                        $sqlparam = $sqlparam.'downtype='.$_REQUEST['downtype'].'&';
                            $wherestring =$wherestring." and download >= ".$_GET['downbegin']." and download  <=".$_GET['downend'];
                        }else
                        {
                            $wherestring =$wherestring." and download >= ".$_GET['downbegin'];
                        }
                    }
                }else if(strlen($_GET['downend'])>0)
                {
                    if($_REQUEST['downtype']==1)
                    {
                        $sqlparam = $sqlparam.'downend='.$_GET['downend'].'&';
                        $sqlparam = $sqlparam.'downtype='.$_REQUEST['downtype'].'&';
                        $wherestring =$wherestring." and az_downloaded <= ".$_GET['downend'];
                    }

                    if($_REQUEST['downtype']==2)
                    {
                        $sqlparam = $sqlparam.'downend='.$_GET['downend'].'&';
                        $sqlparam = $sqlparam.'downtype='.$_REQUEST['downtype'].'&';
                        $wherestring =$wherestring." and download <= ".$_GET['downend'];
                    }
                }


                if(strlen($_POST['downbegin'])>0)
                {
                    if($_REQUEST['downtype']==1)
                    {
                        $sqlparam = $sqlparam.'downbegin='.$_POST['downbegin'].'&';
                        $sqlparam = $sqlparam.'downtype='.$_REQUEST['downtype'].'&';
                        if(strlen($_POST['downend'])>0)
                        {
                            $sqlparam = $sqlparam.'downend='.$_POST['downend'].'&';
                            $sqlparam = $sqlparam.'downtype='.$_REQUEST['downtype'].'&';
                            $wherestring =$wherestring." and az_downloaded >= ".$_POST['downbegin']." and az_downloaded  <=".$_POST['downend'];
                        }else
                        {
                            $wherestring =$wherestring." and az_downloaded >= ".$_POST['downbegin'];
                        }
                    
                    }

                    if($_REQUEST['downtype']==2)
                    {
                        $sqlparam = $sqlparam.'downbegin='.$_POST['downbegin'].'&';
                        $sqlparam = $sqlparam.'downtype='.$_REQUEST['downtype'].'&';
                        if(strlen($_POST['downend'])>0)
                        {
                            $sqlparam = $sqlparam.'downend='.$_POST['downend'].'&';
                            $sqlparam = $sqlparam.'downtype='.$_REQUEST['downtype'].'&';
                            $wherestring =$wherestring." and download >= ".$_POST['downbegin']." and download  <=".$_POST['downend'];
                        }else
                        {
                            $wherestring =$wherestring." and download >= ".$_POST['downbegin'];
                        }
                    }
                    
                }else if(strlen($_POST['downend'])>0)
                {
                    if($_REQUEST['downtype']==1)
                    {
                        $sqlparam = $sqlparam.'downend='.$_POST['downend'].'&';
                        $sqlparam = $sqlparam.'downtype='.$_REQUEST['downtype'].'&';
                        $wherestring =$wherestring." and az_downloaded <= ".$_POST['downend'];
                    }

                    if($_REQUEST['downtype']==2)
                    {
                        $sqlparam = $sqlparam.'downend='.$_POST['downend'].'&';
                        $sqlparam = $sqlparam.'downtype='.$_REQUEST['downtype'].'&';
                        $wherestring =$wherestring." and download <= ".$_POST['downend'];
                    }
                }

                $thisurl = $_SERVER['REQUEST_URI'];
                $thisurl = str_replace('/byaz/1','',$thisurl);
                $thisurl = str_replace('/bybaidu/1','',$thisurl);
		$this->assign("thisurl",$thisurl);
                //$order = 'new_sid desc';
                //$order = "FROM_UNIXTIME(new_sdate, '%Y%m%d') desc,az_downloaded desc";
                $order = "new_sdate desc";
                if($_GET['byaz']==1)
                {
		    $this->assign("order",'/byaz/1');
                    $sqlparam = $sqlparam.'byaz=1&';
                    $order = 'az_downloaded desc';
                }

                if($_GET['bybaidu']==1)
                {
		    $this->assign("order",'/bybaidu/1');
                    $sqlparam = $sqlparam.'bybaidu=1&';
                    $order = 'download desc';
                }

                $wherestring = $wherestring." and cj_user_config.status=1";

		$this->map = $wherestring;
		import("@.ORG.Page");
		$admin_model = D('Caiji.Newsoftwarelist');
		$count = $admin_model->join('INNER JOIN cj_user_config ON cj_new_sowftware.package = cj_user_config.apkname')->where($this->map)->count();
		$page = new Page($count, 15);
		$Newsoftwarelist = $admin_model->field("`new_sid`,`new_sname`,`new_sver`,cj_new_sowftware.package,`new_stxt`,`new_sapk`,`new_sfromweb`,`new_sdate`,`new_status`,`sid`,download,download_str,az_downloaded, MAX(softid), category_id")->join('INNER JOIN cj_user_config ON cj_new_sowftware.package = cj_user_config.apkname')->join('INNER JOIN sj_soft on cj_new_sowftware.package = sj_soft.package')->where($this->map)->group('cj_new_sowftware.package')->order($order)->limit($page->firstRow.','.$page->listRows)->select();
                //$sql = "select a.new_sname,a.az_downloaded,b.download_str,b.download from cj_new_sowftware a inner join cj_user_config b on a.package=b.apkname where b.status=1";
                //$rs = $admin_model->query($sql);
                //print_r($rs);
                
                //echo $admin_model->getlastsql();
        // 查看分类表
        $category_arr = $model->field('category_id, name')->table('sj_category')->where(array('status'=>1))->select();
        $category_kv_arr = array();
        foreach($category_arr as $key => $value) {
            $category_id = $value['category_id'];
            $category_name = $value['name'];
            $category_kv_arr[$category_id] = $category_name;
        }
        
        
		$this->soft_db = M('soft');

		foreach ($Newsoftwarelist as $key => $val){
			$version_code = $this->soft_db->field("version_code")->where("status = 1 and channel_id='' and package = '${val['package']}'")->order("version_code desc")->limit(1)->select();
			$Newsoftwarelist[$key]['version_code'] = $version_code[0]['version_code'];
			//$Newsoftwarelist[$key]['total_downloaded'] = $version_code[0]['total_downloaded'];
		}

		foreach($Newsoftwarelist as $key => $val) {
			if(isset($need_arr[$val['package']])) {
				$Newsoftwarelist[$key]['category'] = 1;
			} else {
				$Newsoftwarelist[$key]['category'] = 0;
			}
            $Newsoftwarelist[$key]['az_downloaded_str'] = $this->num_format($Newsoftwarelist[$key]['az_downloaded'], 2);
            $category_id = trim($val['category_id'], ',');
            $Newsoftwarelist[$key]['category_name'] = $category_kv_arr[$category_id];
		}
		//var_dump($Newsoftwarelist);
                $page->parameter = $sqlparam;
		$page->setConfig('header','篇记录');
		$page->setConfig('first','<<');
		$page->setConfig('last','>>');
		$show =$page->show();
		$this->assign("page", $show);
        $this->assign("category_id", $_REQUEST["category_id"]);
		$this->assign("downbegin", $_REQUEST['downbegin']);
		$this->assign("downend", $_REQUEST['downend']);
		$this->assign("begintime", $_REQUEST['begintime']);
		$this->assign("endtime", $_REQUEST['endtime']);
		$this->assign("downtype", $_REQUEST['downtype']);
        $this->assign("category_kv_arr", $category_kv_arr);
		$this->assign("Newsoftwarelist" , $Newsoftwarelist);
        
                if($_POST){
                    $this->redirect('/Newsoftwarelist/index?'.$sqlparam);
                }
		$this->display('index');
	}
    */
    
    function index() {
        // 查看分类表
        $category_ret = $this->get_category_arr();
        $category_first_arr = $category_ret['first'];
        $category_third_arr = $category_ret['third'];
        ////////////
        /*
		$model = new Model();
		$where['status'] = 1;
		$need = $model -> table('cj_partner') -> where($where) -> select();
		foreach($need as $key => $val){
			$need_arr[$val['packagename']] = 1;
		}
        */
		$cond = array();
		$cond[] = "new_status=1";

        $sqlparam ='';
        if(isset($_GET['softname']))
        {
            $sqlparam = $sqlparam.'softname='.$_GET['softname'].'&';
            $softname = $_GET['softname'];
            $this->assign("softname", $softname);
            $cond[] = "new_sname like '%$softname%'";
        }
        if(isset($_GET['package']))
        {
            $sqlparam = $sqlparam.'package='.$_GET['package'].'&';
            $package = $_GET['package'];
            $this->assign("package", $package);
            $cond[] = "cj_new_sowftware.package='$package'";
        }
        
        if (!$_GET['category_id']) {
            $_GET['category_id'] = -1;
        }
        
        if (strlen($_GET['category_id']) > 0 && $_GET['category_id'] != -1) {
            $sqlparam = $sqlparam . 'category_id=' . $_GET['category_id'] . '&';
            //$where['category_id'] = array('like', "%,{$_GET['category_id']},%");
            $this->assign("category_id", $_GET['category_id']);
            // 查询其下所有三级分类
            $leaf_category_arr = array();
            foreach($category_third_arr as $key=>$record) {
                if ($record['ancestor_id'] == $_GET['category_id']) {
                    $leaf_category_arr[] = $key;
                }
            }
            $category_cond_str = "";
            $i = 0;
            foreach($leaf_category_arr as $category_id) {
                if ($i > 0) {
                    $category_cond_str .= " or ";
                } else {
                    $category_cond_str .= "(";
                }
                $i++;
                $category_cond_str .= "category_id like '%,{$category_id},%'";
            }
            if ($i > 0)
                $category_cond_str .= ")";
            $cond[] = $category_cond_str;
        }
        
		if (isset($_GET['begintime']) && isset($_GET['endtime']))
		{
            $sqlparam = $sqlparam.'begintime='.$_GET['begintime'].'&';
            $sqlparam = $sqlparam.'endtime='.$_GET['endtime'].'&';
            $begintime = $_GET['begintime'];
            $endtime = $_GET['endtime'];
            $this->assign("begintime", $begintime);
            $this->assign("endtime", $endtime);
            $cond[] = "new_sdate between unix_timestamp('$begintime') and unix_timestamp('$endtime')+86399";
		}
        
        //$cond[] = "az_downloaded > 50000";//二逼条件，于8月7号邮件要求去掉

		$wherestring = implode(" and ", $cond);

        if(isset($_GET['downbegin'])>0)
        {
            if($_REQUEST['downtype']==1)
            {
                $sqlparam = $sqlparam.'downbegin='.$_GET['downbegin'].'&';
                $sqlparam = $sqlparam.'downtype='.$_REQUEST['downtype'].'&';
                if(strlen($_GET['downend'])>0)
                {
                    $sqlparam = $sqlparam.'downend='.$_GET['downend'].'&';
                $sqlparam = $sqlparam.'downtype='.$_REQUEST['downtype'].'&';
                    $wherestring =$wherestring." and az_downloaded >= ".$_GET['downbegin']." and az_downloaded  <=".$_GET['downend'];
                }else
                {
                    $wherestring =$wherestring." and az_downloaded >= ".$_GET['downbegin'];
                }
            
            }

            if($_REQUEST['downtype']==2)
            {
                $sqlparam = $sqlparam.'downbegin='.$_GET['downbegin'].'&';
                $sqlparam = $sqlparam.'downtype='.$_REQUEST['downtype'].'&';
                if(strlen($_GET['downend'])>0)
                {
                    $sqlparam = $sqlparam.'downend='.$_GET['downend'].'&';
                $sqlparam = $sqlparam.'downtype='.$_REQUEST['downtype'].'&';
                    $wherestring =$wherestring." and download >= ".$_GET['downbegin']." and download  <=".$_GET['downend'];
                }else
                {
                    $wherestring =$wherestring." and download >= ".$_GET['downbegin'];
                }
            }
        }else if(strlen($_GET['downend'])>0)
        {
            if($_REQUEST['downtype']==1)
            {
                $sqlparam = $sqlparam.'downend='.$_GET['downend'].'&';
                $sqlparam = $sqlparam.'downtype='.$_REQUEST['downtype'].'&';
                $wherestring =$wherestring." and az_downloaded <= ".$_GET['downend'];
            }

            if($_REQUEST['downtype']==2)
            {
                $sqlparam = $sqlparam.'downend='.$_GET['downend'].'&';
                $sqlparam = $sqlparam.'downtype='.$_REQUEST['downtype'].'&';
                $wherestring =$wherestring." and download <= ".$_GET['downend'];
            }
        }

        $thisurl = $_SERVER['REQUEST_URI'];
        $thisurl = str_replace('/byaz/1','',$thisurl);
        $thisurl = str_replace('/bybaidu/1','',$thisurl);
		$this->assign("thisurl",$thisurl);
        //$order = 'new_sid desc';
        //$order = "FROM_UNIXTIME(new_sdate, '%Y%m%d') desc,az_downloaded desc";
        $order = "new_sdate desc";
        if($_GET['byaz']==1)
        {
            $this->assign("order",'/byaz/1');
            $sqlparam = $sqlparam.'byaz=1&';
            $order = 'az_downloaded desc';
        }

        if($_GET['bybaidu']==1)
        {
            $this->assign("order",'/bybaidu/1');
            $sqlparam = $sqlparam.'bybaidu=1&';
            $order = 'download desc';
        }

        $wherestring = $wherestring." and cj_user_config.status=1";
        
        // 如果是合作软件，在合作期内不展示此软件
        $now = time();
        $wherestring .= " and (packagename is null or (cj_partner.co_start_time>{$now} or cj_partner.co_end_time<{$now}))";

		$this->map = $wherestring;
		import("@.ORG.Page");
		$admin_model = D('Caiji.Newsoftwarelist');
        /*
		//$count = $admin_model->join('INNER JOIN cj_user_config ON cj_new_sowftware.package = cj_user_config.apkname')->where($this->map)->count();
        $Newsoftwarelist_all = $admin_model->field("`new_sid`,`new_sname`,`new_sver`,cj_new_sowftware.package,`new_stxt`,`new_sapk`,`new_sfromweb`,`new_sdate`,`new_status`,`sid`,download,download_str,az_downloaded, MAX(softid), cj_user_config.category_id")->join('INNER JOIN cj_user_config ON cj_new_sowftware.package = cj_user_config.apkname')->join('INNER JOIN sj_soft on cj_new_sowftware.package = sj_soft.package')->where($this->map)->group('cj_new_sowftware.package')->select();
        $count = count($Newsoftwarelist_all);
		$page = new Page($count, 15);
		$Newsoftwarelist = $admin_model->field("`new_sid`,`new_sname`,`new_sver`,cj_new_sowftware.package,`new_stxt`,`new_sapk`,`new_sfromweb`,`new_sdate`,`new_status`,`sid`,download,download_str,az_downloaded, MAX(softid), cj_user_config.category_id")->join('INNER JOIN cj_user_config ON cj_new_sowftware.package = cj_user_config.apkname')->join('INNER JOIN sj_soft on cj_new_sowftware.package = sj_soft.package')->where($this->map)->group('cj_new_sowftware.package')->order($order)->limit($page->firstRow.','.$page->listRows)->select();
        */
                //$sql = "select a.new_sname,a.az_downloaded,b.download_str,b.download from cj_new_sowftware a inner join cj_user_config b on a.package=b.apkname where b.status=1";
                //$rs = $admin_model->query($sql);
                //print_r($rs);
                
                //echo $admin_model->getlastsql();
        $count = $admin_model->join('INNER JOIN cj_user_config on package=apkname')->join('left JOIN cj_partner on package=packagename and cj_partner.status=1')->where($this->map)->count();
        $page = new Page($count, 15);
        $Newsoftwarelist = $admin_model->field("`new_sid`,`new_sname`,`new_sver`,`new_svername`,`package`,`new_stxt`,`new_sapk`,`new_sfromweb`,`new_sdate`,`new_status`,`sid`,download,download_str,az_downloaded,category_id")->join('INNER JOIN cj_user_config on package=apkname')->join('left JOIN cj_partner on package=packagename and cj_partner.status=1')->where($this->map)->order($order)->limit($page->firstRow.','.$page->listRows)->select();
        //var_dump($admin_model->getlastsql());
        
        //var_dump($category_ret);
        
        
		$this->soft_db = M('soft');
		foreach ($Newsoftwarelist as $key => $val){
			$version_code = $this->soft_db->field("version, version_code")->where("status = 1 and channel_id='' and package = '${val['package']}'")->order("version_code desc")->limit(1)->select();
            $Newsoftwarelist[$key]['version_name'] = $version_code[0]['version'];
			$Newsoftwarelist[$key]['version_code'] = $version_code[0]['version_code'];
			//$Newsoftwarelist[$key]['total_downloaded'] = $version_code[0]['total_downloaded'];
		}

		foreach($Newsoftwarelist as $key => $val) {
            /*
			if(isset($need_arr[$val['package']])) {
				$Newsoftwarelist[$key]['category'] = 1;
			} else {
				$Newsoftwarelist[$key]['category'] = 0;
			}
            */
            $Newsoftwarelist[$key]['az_downloaded_str'] = $this->num_format($Newsoftwarelist[$key]['az_downloaded'], 2);
            $category_id = trim($val['category_id'], ',');
            // 祖先结点
            $ancestor_id = $category_third_arr[$category_id]['ancestor_id'];
            $Newsoftwarelist[$key]['category_name'] = $category_first_arr[$ancestor_id]['name'];
		}
		//var_dump($Newsoftwarelist);
        $page->parameter = $sqlparam;
		$page->setConfig('header','篇记录');
		$page->setConfig('first','<<');
		$page->setConfig('last','>>');
		$show =$page->show();
		$this->assign("page", $show);
        $this->assign("category_id", $_GET["category_id"]);
		$this->assign("downbegin", $_REQUEST['downbegin']);
		$this->assign("downend", $_REQUEST['downend']);
		$this->assign("begintime", $_REQUEST['begintime']);
		$this->assign("endtime", $_REQUEST['endtime']);
		$this->assign("downtype", $_REQUEST['downtype']);
        $this->assign("category_first_arr", $category_first_arr);
		$this->assign("Newsoftwarelist" , $Newsoftwarelist);
        
		$this->display('index');
	}

	//软件管理__软件更新_显示
	public function newsoftware_edit() {
		$this->cjid = $_GET['id'];
		if (empty($this->cjid)) {
			$this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Soft/soft_list');
			$this->error("非法操作失败,如频繁出现，请联系管理员！");
		}
		//分辨率
		/*$this->resolution_db = M('resolution');
		$this->resolution_list = $this->resolution_db->field('resolutionid,note')->where('status=1')->select();
		$this->assign('resolutionlist', $this->resolution_list);*/
		//固件版本
		$this->conf_db = D('Sj.Config');
		$this->config_list = $this->conf_db->field('configname,configcontent')->where('config_type="firmware" and status =1')->select();
		$this->assign('configlist', $this->config_list);

		//运营商信息
		$this->operating_db = D('Sj.Operating');
		$this->operating_list = $this->operating_db->where('only_auth=0')->field('oid,mname')->select();
		$this->assign('operatinglist', $this->operating_list);

		$model = new model();
		$software = $model -> table('cj_new_sowftware') -> field("`new_sid`,`new_sname`,`new_sver`,`new_stxt`,`new_sapk`,`package`,`new_sdate`,`sid`")->where(array('new_sid' => $this->cjid))->select();

		$this->assign('new_sname', $software[0]['new_sname']);
		$this->assign('new_stxt', $software[0]['new_stxt']);
		$this->assign('new_sapk',  $software[0]['new_sapk']);
		$this->assign('package',  $software[0]['package']);
		$this->assign('new_sver',  $software[0]['new_sver']);
		$this->assign('softid',  $software[0]['sid']);

		$this->soft_db = M('soft');
		$this->map = 'status=1 and softid=' . $software[0]['sid'] . '';

		$this->soft_list = $this->soft_db->where($this->map)->select();

		$this->soft_note_db = M('soft_note');
		$map = '';
		$map['package'] = $this->soft_list[0]['package'];
		$this->soft_note_list = $this->soft_note_db->where($map)->field('note,auth')->select();

		$this->soft_file_db = M('soft_file');
		$map = '';
		$map['package_status'] = 1;
		$map['softid'] = $this->softid;
		$this->soft_file_list = $this->soft_file_db->where($map)->field('id,url,iconurl,min_firmware,max_firmware,resolutionid')->select();

		$this->soft_thumb_db = M('soft_thumb');
		$map = '';
		$map['softid'] = $software[0]['sid'];
		$map['status'] = 1;
		$this->soft_thumb_list = $this->soft_thumb_db->where($map)->order('rank')->field('url,rank')->select();

		if ($this->soft_list[0]['category_id'][0] == ',') {
			$this->soft_list[0]['category_id'] = substr($this->soft_list[0]['category_id'], 1);
		}

		$tnum = strlen($this->soft_list[0]['category_id']);
		$tnum--;
		if ($this->soft_list[0]['category_id'][$tnum] == ',') {
			$this->soft_list[0]['category_id'] = substr($this->soft_list[0]['category_id'], 0, -1);
		}

		$cid = explode(',', $this->soft_list[0]['category_id']);

		//运营商隐藏
		$operatorhide = explode(',', $this->soft_list[0]['operatorhide']);

		$this->soft_list[0]['note'] = $this->soft_note_list[0]['note'];
		//$this->soft_list[0]['auth'] = $this->soft_note_list[0]['auth'];

		$this->assign('softlist', $this->soft_list[0]);
		$this->assign('thumblist', $this->soft_thumb_list);

		$this->assign('filelist', $this->soft_file_list);
		//分类
		$this->category_db = M('category');
		$category = D('Sj.Category');
		$array_config=array(
			"categoryid"=>"categoryid[]",
			"selected"=>$cid[0]
		);
		$conf_list = $category->getCategory($array_config);
		$this->assign('conflist',$conf_list);
		$this->assign('cid', $cid);
		$this->assign('operatorhide', $operatorhide);

		$this->display('soft_update');
	}
    
    function ignore_software() {
        if ($_POST) {
            $model = M();
            // 将表cj_new_software中id为$_POST['new_sid']的记录状态置为忽略
            $where = array(
                'new_sid' => array('EQ', trim($_POST['new_sid'])),
            );
            $data = array();
            $data['new_status'] = 3;
            $ret = $model->table('cj_new_sowftware')->where($where)->save($data);
            if (!$ret) {
                $this->error("操作失败");
            }
            $this->writelog("采集最新软件管理_软件id为{$_POST['new_sid']}的软件被忽略",'cj_new_sowftware',trim($_POST['new_sid']),__ACTION__ ,"","edit");
            $data = array();
            $data['new_sid'] = trim($_POST['new_sid']);
            $data['ignore_reason'] = trim($_POST['ignore_reason']);
            $data['operator'] = $_SESSION['admin']['admin_user_name'];
            $data['ignore_time'] = time();
            
            $ret = $model->table('cj_new_software_ignored')->add($data);
            if ($ret){
                $this->writelog("采集最新软件忽略原因表新增id为{$ret}的数据,软件id为{$data['new_sid']}",'cj_new_software_ignored',$ret,__ACTION__ ,"","add");
                $this->success("操作成功！");
            }else{
                $this->error("操作失败！");
            }
        }
        $this->assign("new_sid", $_GET['new_sid']);
        $this->display("ignore_software");
    }
    
    function num_format($num, $type) {
        if ($num === '') return $num;
        $num = intval($num);
        switch ($type) {
            case 1 :
                return $num;
                break;
            case 2:
                if ($num <= 100) {
                    return '100+';
                } elseif ($num < 1000) {
                    $n = floor($num / 100); 
                    return "{$n}00+";
                } elseif ($num < 10000) {
                    $n = floor($num / 1000); 
                    return "{$n}000+";
                } elseif ($num < 100000) {
                    $n = floor($num / 10000); 
                    return "{$n}万+";
                } elseif ($num < 1000000) {
                    $n = floor($num / 100000); 
                    return "{$n}0万+";
                } elseif ($num < 10000000) {
                    $n = floor($num / 1000000); 
                    return "{$n}00万+";
                } elseif ($num < 100000000) {
                    $n = floor($num / 10000000); 
                    return "{$n}000万+";
                } elseif ($num < 1000000000) {
                    $n = floor($num / 100000000); 
                    return "{$n}亿+";
                } else {
                    return "10亿+";
                }
                break;
            default :
                return $num;
                break;
        }
    }
    
    // 查找分类表，返回一级分类、二级分类、三级分类
    function get_category_arr() {
        $model=M("category");
        $first_category = array();
        $second_category = array();
        $third_category = array();
        // 一级分类
        $where = array(
            'parentid' => array('EQ', 0),
            'status' => array('EQ', 1),
        );
        $first_ret = $model->where($where)->select();
        foreach($first_ret as $value) {
            $category_id = $value['category_id'];
            $category_name = $value['name'];
            $first_category[$category_id]['name'] = $category_name;
            $first_category[$category_id]['ancestor_id'] = 0;
        }
        unset($first_category[3]);
        // 二级分类
        foreach($first_category as $key=>$value) {
            $where = array(
                'parentid' => array('EQ', $key),
                'status' => array('EQ', 1),
            );
            $second_ret = $model->where($where)->select();
            foreach($second_ret as $value) {
                $category_id = $value['category_id'];
                $category_name = $value['name'];
                $second_category[$category_id]['name'] = $category_name;
                $second_category[$category_id]['ancestor_id'] = $key;
            }
        }
        // 三级分类
        foreach($second_category as $key=>$value) {
            $where = array(
                'parentid' => array('EQ', $key),
                'status' => array('EQ', 1),
            );
            $third_ret = $model->where($where)->select();
            foreach($third_ret as $value) {
                $category_id = $value['category_id'];
                $category_name = $value['name'];
                $third_category[$category_id]['name'] = $category_name;
                $third_category[$category_id]['ancestor_id'] = $second_category[$key]['ancestor_id'];//祖先结点为其父结点的祖先结点
            }
        }
        $ret = array(
            'first' => $first_category,
            'second' => $second_category,
            'third' => $third_category,
        );
        return $ret;
    }
}

?>
