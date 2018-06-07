<?php

class UserconfigAction extends CommonAction {
     
    public function userconfig_list() {
    
        // 查看分类表
        $category_ret = $this->get_category_arr();
        $category_first_arr = $category_ret['first'];
        $category_third_arr = $category_ret['third'];
        ////////////

        $sqlparam ='';
        $cond = array();
        if(strlen($_GET['softname'])>0){
            $sqlparam = $sqlparam.'softname='.$_GET['softname'].'&';
            //$where['searchname'] = array('like', '%'.$_GET['softname'].'%');
            $cond[] = "searchname like '%{$_GET['softname']}%'";
        }

        if(strlen($_GET['package'])>0){
            $sqlparam = $sqlparam.'package='.$_GET['package'].'&';
            //$where['apkname']=$_GET['package'];
            $cond[] = "apkname='{$_GET['package']}'";
        }
        
        if (!$_GET['category_id']) {
            $_GET['category_id'] = -1;
        }
        
        if (strlen($_GET['category_id']) > 0 && $_GET['category_id'] != -1) {
            $sqlparam = $sqlparam . 'category_id=' . $_GET['category_id'] . '&';
            
            // 查询其下所有三级分类
            $leaf_category_arr = array();
            foreach($category_third_arr as $key=>$record) {
                if ($record['ancestor_id'] == $_GET['category_id']) {
                    $leaf_category_arr[] = $key;
                }
            }
            //$category_cond_arr = array();
            //foreach($leaf_category_arr as $category_id) {
            //    $category_cond_arr[] = ",{$category_id},";
            //}
            
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
            
            //$where['category_id'] = array('in', $category_cond_arr);
            $cond[] = "{$category_cond_str}";
        }
        
        if(strlen($_GET['begintime'])>0){
            $sqlparam = $sqlparam.'begintime='.$_GET['begintime'].'&';
            if(strlen($_GET['endtime'])>0)
            {
                $sqlparam = $sqlparam.'endtime='.$_GET['endtime'].'&';
                //$where['addtime']  = array('between',''.strtotime($_GET['begintime']).','.strtotime($_GET['endtime']).'');
                $cond[] = "addtime between " . strtotime($_GET['begintime']) . " and " . strtotime($_GET['endtime']);
                $this->assign("endtime", $_GET['endtime']);
            }else
            {
                //$where['addtime']  = array('between',''.strtotime($_GET['begintime']).',9999999999');
                $cond[] = "addtime between " . strtotime($_GET['begintime']) . " and 9999999999";
            }
            $this->assign("begintime", $_GET['begintime']);
        }else if(strlen($_GET['endtime'])>0){
            $sqlparam = $sqlparam.'endtime='.$_GET['endtime'].'&';
            //$where['addtime']  = array('between','0,'.strtotime($_GET['endtime']).'');
            $cond[] = "addtime between 0 and " . strtotime($_GET['endtime']);
            $this->assign("endtime", $_GET['endtime']);
        }

        if(strlen($_GET['cj_status'])>0&&$_GET['cj_status']>=0)
        {
            $sqlparam = $sqlparam.'cj_status='.$_GET['cj_status'].'&';
            $cj_status=$_GET['cj_status'];
            //$where['cj_user_config.status']=$_GET['cj_status'];
            $cond[] = "cj_user_config.status={$_GET['cj_status']}";
        }else{
            $cj_status=-1;
        }
        
        // 如果是合作软件，在合作期内不展示此软件
        $now = time();
        //$where['packagename'] = array('exp', "is null");
        $cond[] = "(packagename is null or (cj_partner.co_start_time>{$now} or cj_partner.co_end_time<{$now}))";
        
        $wherestring = implode(" and ", $cond);

        import("@.ORG.Page");
        $user_model = D('Caiji.Userconfig');
        $admin_model = D('Caiji.Adminconfig');
        /*
        //$count = $user_model->where($where)->count();
        $userconfig_list_all = $user_model->field("`cid`,`searchname`,`apkname`,`scansite_name`,`addtime`,cj_user_config.status, cj_user_config.category_id, Max('softid')")->where($where)->join('INNER JOIN sj_soft on apkname=sj_soft.package and sj_soft.status=1')->group('apkname')->select();
        $count = count($userconfig_list_all);
        $page = new Page($count, 15);
        $userconfig_list = $user_model->field("`cid`,`searchname`,`apkname`,`scansite_name`,`addtime`,cj_user_config.status, cj_user_config.category_id, Max('softid')")->where($where)->join('INNER JOIN sj_soft on apkname=sj_soft.package and sj_soft.status=1')->group('apkname')->order('cid desc')->limit($page->firstRow.','.$page->listRows)->select();
        //echo $user_model->getlastsql();
        */
        $count = $user_model->field("`cid`,`searchname`,`apkname`,`scansite_name`,`addtime`, cj_user_config.status, `category_id`")->join('left JOIN cj_partner on apkname=packagename and cj_partner.status=1')->where($wherestring)->count();
        $page = new Page($count, 15);
        $userconfig_list = $user_model->field("`cid`,`searchname`,`apkname`,`scansite_name`,`addtime`, cj_user_config.status, `category_id`")->join('left JOIN cj_partner on apkname=packagename and cj_partner.status=1')->where($wherestring)->order('cid desc')->limit($page->firstRow.','.$page->listRows)->select();
        //var_dump($user_model->getlastsql());
        
        $cjwebsite = $admin_model->field("`id`,`cj_website`")->where("`cj_status`=1")->select();
		
		foreach($userconfig_list as $key => $val){
			$userconfig_list[$key] = $val;
            $category_id = trim($val['category_id'], ',');
            // 祖先结点
            $ancestor_id = $category_third_arr[$category_id]['ancestor_id'];
            $userconfig_list[$key]['category_name'] = $category_first_arr[$ancestor_id]['name'];
		}
                
        $page->parameter = $sqlparam;
        $page->setConfig('header','篇记录');
        $page->setConfig('first','<<');
        $page->setConfig('last','>>');
        $show =$page->show();
        $this->assign("page", $show);
        $this->assign("cj_status", $cj_status);
        $this->assign("softname", $_GET['softname']);
        $this->assign("package", $_GET['package']);
        $this->assign("category_id", $_GET["category_id"]);
        $this->assign("category_first_arr", $category_first_arr);
        $this->assign("userconfig" , $userconfig_list);
        $this->assign("cjwebsite" , $cjwebsite);
        $this->display('userconfig_list');
    }

    function addwebsite()
    {
        $admin_model = D('Caiji.Adminconfig');
        $cjwebsite = $admin_model->field("`id`,`cj_website`")->where("`cj_status`=1")->select();
        $this->assign("cjwebsite" , $cjwebsite);
        $this->display('addwebsite');
    }

    public function userconfig_modify() {

        $id=$_GET['cid'];
        $user_model = D('Caiji.Userconfig');
        $admin_model = D('Caiji.Adminconfig');
         
        $userconfig_list = $user_model->field(" `cid`, `searchname`, `apkname`, `status`, `scansite_num`")->where(array('cid' => $id))->select();

        $cjwebsite = $admin_model->field("`id`,`cj_website`")->where("`cj_status`=1")->select();
        $scansite_num = explode(",", $userconfig_list[0]['scansite_num']);
        foreach ($cjwebsite as &$v) {
            if (in_array($v['id'], $scansite_num)) $v['checked'] = "checked='checked'";
            else $v['checked'] = '';
        }

        $this->assign("cjwebsite" , $cjwebsite);
        $this->assign("cj_cid" , $userconfig_list[0]['cid']);
        $this->assign("cj_searchname" , $userconfig_list[0]['searchname']);
        $this->assign("cj_apkname" , $userconfig_list[0]['apkname']);

        $this->display('userconfig_modify');
    }

    public function userconfig_action() {
        $searchname = trim($_POST['searchname']);
        $apkname = trim($_POST['apkname']);
        $scansite = $_POST['scansite'];
        $action = $_GET['action'];
        $id = $_GET['cid'];

        foreach($scansite as $k=>$v){
            $s_name.=$k.',';
            $s_num.=$v.',';
        }
        $s_name=substr($s_name,0,strlen($s_name)-1);
        $s_num=substr($s_num,0,strlen($s_num)-1);
        
        if ($apkname) {
	        $soft_model = M('soft');
	        $isset = $soft_model->where(array('package'=>$apkname,'status'=>1))->count();
	        if ($isset==0) {
	            $this->error("该包名不存在！");
	        }
        }

        $user_model = D('Caiji.Userconfig');
        switch($action){
            case 'midfly':
                $oid = $this->get_other_cid_by_package($apkname, $id);
                if ($oid != $id) {
                    $this->error("已经有个相同的包了！");
                }
                $data=array('searchname'=>$searchname,'apkname'=>$apkname,'scansite_name'=>$s_name,'scansite_num'=>$s_num);
                $log_result = $this->logcheck(array('cid' => $id),'cj_user_config',$data,$user_model);
                $affect = $user_model->where(array('cid' => $id))->save($data);
                if ($affect > 0) {
					$this -> writelog('已修改序列号为'.$id."的采集关键词.$log_result",'cj_user_config',$id,__ACTION__ ,"","edit");
                    $this -> assign('jumpUrl',"__URL__/userconfig_list");
                    $this -> success("修改成功！");
                }
                else {
                    $this->error("修改失败！");
                }
                break;
            case 'update':
                $type = $_GET['type'];
                if($type == 0){
					$status = 0;
                    $affect = $user_model->where(array('cid' => $id))->save(array('status' => $status));
                }else{
					$status = 1;
                    $old_data = $user_model->where(array('cid' => $id))->select();
                    $oid = $this->get_other_cid_by_package($old_data[0]['apkname'], $id);
	                if ($oid != $id) {
	                    $this->error("已经有个相同的包了！");
	                }
                    $affect = $user_model->where(array('cid' => $id))->save(array('status' => $status));
                }
				
                if ($affect > 0) {
					$this -> writelog('已修改序列号为'.$id.'的采集关键词状态为'.$status,'cj_user_config',$id,__ACTION__ ,"","edit");
                    $this->success("修改成功！");
                }
                else {
                    $this->error("修改失败！");
                }
                break;
            default:
                if(empty($searchname) || empty($apkname) || empty($scansite)){
                    $this->error("请完整填写信息");
                }
                $now = time();
                $data = array(
				'searchname' => $searchname,
				'apkname' => $apkname,
				'scansite_num'=>$s_num,
				'scansite_name'=>$s_name,
				'addtime' => $now,
                	
                );
                $oid = $this->get_other_cid_by_package($apkname);
                if ($oid) {
                    $this->error("已经有个相同的包了！");
                }
                // 根据包名查sj_soft表，得到category_id
                $soft_model = M('soft');
                $where = array(
                    'package' => array('EQ', $apkname),
                    'status' => array('EQ', 1),
                );
                $sj_soft_record = $soft_model->field("`category_id`")->where($where)->order('softid desc')->find();
                if (!$sj_soft_record) {
                    $this->error("添加失败！");
                }
                $data['category_id'] = $sj_soft_record['category_id'];
                ////////////////////////////////////////
                $affect = $user_model->add($data);
                //echo $user_model->getlastsql();
                if ($affect > 0) {
					$this -> writelog("已添加采集关键词,软件名为:$searchname,包名为:$apkname,站点id为$s_num,站点名为:$s_name",'cj_user_config',$affect,__ACTION__ ,"","add");
                    $this->success("添加采集任务成功！");
                }
                else {
                    $this->error("添加失败！");
                }
        }
    }
    
    public function get_other_cid_by_package($package, $cid = 0) {
        $user_model = D('Caiji.Userconfig');
        $where = array('apkname'=>$package);
        $data = $user_model->where($where)->field('cid')->select();
        if ($data) {
            foreach ($data as $v) {
                if ($cid == $v['cid']) continue;
                $cid = $data[0]['cid'];
            }
        }

        return $cid;
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
