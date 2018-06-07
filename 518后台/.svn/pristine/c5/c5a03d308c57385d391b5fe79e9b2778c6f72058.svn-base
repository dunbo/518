<?php
class SoftEditAction extends CommonAction {
	//获取专题列表
	public function getfeaturelist() {
        $model = M('feature');
        $featurelist = $model->where('status=1')->field('feature_id, name')->order('orderid')->select();
        $this->assign('featurelist', $featurelist);
    }
	//显示开发者编辑软件列表
	function soft_list(){
		import("@.ORG.Page");
		$soft_list=M("soft_temporary");
		$soft=M("soft");
		$soft_file=M("soft_file");
		$category_db=M("category");
		$this->getfeaturelist();
		//获取软件分类
		$category = D('Sj.Category');
		$array_config=array(
			"categoryid"=>"categoryid",
			"selected"=>$_GET['categoryid']
		);
		$conf_list = $category->getCategory($array_config);
        $this->assign('conflist',$conf_list);
		//获取分类结束
		$where="status=1 ";
		if ($_REQUEST['sosotype'] == "clear") {
            unset($_GET);
        }
		if($_GET){
			if(!empty($_GET['softid'])){
				$softid=trim($_GET['softid']);
                $where.=' and softid="' . $softid . '"';
				$this->assign("softid",$softid);
			}
			
			if(!empty($_GET['terrace']) && $_GET['terrace'] != 'all'){
				$this->map.=' and (terrace&'.$_GET['terrace'].') = '.$_GET['terrace'].'';
				$this -> assign('terrace',$_GET['terrace']);
			}
			
			if(!empty($_GET['softname'])){
				$softname=trim($_GET['softname']);
                $where.=' and softname like "%' . $softname . '%"';
				$this->assign("softname",$softname);
			}
			if(!empty($_GET['package'])){
				$package=trim($_GET['package']);
                $where.=' and package like "%' . $package . '%"';
				$this->assign("package",$package);
			}
			if(!empty($_GET['categoryid'])){
				$categoryid=trim($_GET['categoryid']);
				$this->assign("categoryid", $categoryid);
				$where.=' and (SELECT find_in_set  (' . trim($_GET['categoryid']) . ',`category_id`)>0)';
                $where.=' and categoryid="' . $categoryid . '"';
			}
			if(!empty($_GET['dev_name'])){
				$dev_name=trim($_GET['dev_name']);
                $where.=' and dev_name like "%' . $dev_name . '%"';
				$this->assign("dev_name",$dev_name);
			}
			if(!empty($_GET['dever_email'])){
				$dever_email=trim($_GET['dever_email']);
                $where.=' and dever_email="' . $dever_email . '"';
				$this->assign("dever_email",$dever_email);
			} 
			if(!empty($_GET['soft_status'])){
				$soft_status=trim($_GET['soft_status']);
                $where.=' and hide="' . $soft_status . '"';
			}elseif($_GET['soft_status']==""){
				$where.='and hide=9';
				$soft_status=9;
			}
		}else{
			$where.=' and hide=9';
			$soft_status=9;
		}
		//白名单软件
		$_soft_white = $soft_white = array();
		$_soft_white = M('safe_white_package');
		$_soft_white = $_soft_white->where("status=1")->field('package')->select();
		foreach($_soft_white as $val) {
			$soft_white[] = $val['package'];
		}
		$this->assign('soft_white', $soft_white);
		$this->assign("soft_status",$soft_status);
		$conf_db = D('Sj.Config');
        $config_list = $conf_db->field('configname,configcontent')->where('config_type="denymsg" and status=1')->select();
		$this->assign("configlist",$config_list);
		//查询所有审核中的softid
		$result_softid=$soft_list->where($where)->field("softid")->select();
		foreach($result_softid as $ksoft=>$vsoft){
			$soft_array[]=$vsoft['softid'];
		}
		$soft_where_in['status']=1;
		$soft_where_in['hide']=1;
		$soft_where_in['softid']=array("in",$soft_array);
		$soft_result_in=$soft->where($soft_where_in)->field("softid")->select();
		$count=count($soft_result_in);
		//获取有效的softid
		foreach($soft_result_in as $knew=>$vnew){
			$new_softid[]=$vnew['softid'];
		}
		//$count = $soft_list->where($where)->count();
        $param = http_build_query($_GET);
		$Page = new Page($count, 10, $param);
		$new_softid_str=implode(",",$new_softid);
		$where.=" and softid in({$new_softid_str})";
		$soft_result=$soft_list->where($where)->order("id desc")->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$category_list_child = $category_db->getField('category_id,name');
		foreach($soft_result as $k=>$v){
			$map="";
			$map['softid'] = $v['softid'];
			$map['package_status'] = array('gt', 0);
			$icon_url=$soft_file->where($map)->getField("iconurl");
			$soft_where="";
			$soft_where['softid']=$v['softid'];
			$soft_where['status']=1;
			$soft_where['hide']=1;
			$old_softname=$soft->where($soft_where)->getField("softname");
			if(trim($old_softname)!=trim($v["softname"])){
				$soft_result[$k]['old_softname']=$old_softname;
			}
			$soft_result[$k]['iconurl']=$icon_url;
			$cid=array();
			$cid = explode(',', $v['category_id']);
            for ($m = 0; $m < count($cid); $m++) {
                $soft_result[$k]['category'].=$category_list_child[$cid[$m]];
            }
		}
		if ($_REQUEST['p'])
            $this->assign('p', $_REQUEST['p']);
		else
            $this->assign('p', 1);

        if ($_REQUEST['lr'])
            $this->assign('lr', $_REQUEST['lr']);
		else
            $this->assign('lr', 10);
		$SoftNameCount=D('Sj.SoftNameCount');
		//盗版风险
		foreach($soft_result as $key => $val){
			$result_count = "";
			$result_count = $SoftNameCount -> get_soft_count($val['softname'],$val['package']);
			if($result_count){
				$val['soft_count'] = $result_count;
			}
			$soft_result[$key] = $val;
		}
		$terrace_model = M('soft');
			//TV认证
		foreach($soft_result as $key => $val){
			$terrace_where['softid'] = $val['softid'];
			$terrace_result = $terrace_model -> where($terrace_where) -> field('terrace') -> select();

			if($terrace_result[0]['terrace']){
				$val['terrace_status'] = 1;
			}else{
				$val['terrace_status'] = 0;
			}
			$soft_result[$key] = $val;
		}
		
		$this->assign("page", $Page->show());
		$this->assign("soft_result",$soft_result);
		$this->display();
	}
	
		//软件平台认证显示
	function soft_terrace(){
		$model = new Model();
		$softid = $_GET['softid'];
		$where['softid'] = $softid;
		$terrace = $model -> table('sj_soft') -> field('terrace') -> where($where) -> select();
		$soft_ter = C('authentication_system');
		$ter = array();
		foreach($soft_ter as $key => $val){
			if((intval($terrace[0]['terrace']) & $val) == $val){
				$ter[] = $val;
			}
		}
		$this -> assign('ter',$ter);
		$this -> assign('terrace',$soft_ter);
		$this -> assign('softid',$softid);
		$this -> display('soft_terrace');
	}

	function soft_terrace_do(){
		$model = new Model();
		$soft_ter = $_GET['soft_ter'];
		if(is_array($soft_ter)){
			foreach($soft_ter as $key => $val){
				$terrace = $terrace + $val;
			}
		}else{
			$terrace = $soft_ter;
		}

		$data['terrace'] = $terrace;
		$where['softid'] = $_GET['softid'];
		$result = $model -> table('sj_soft') -> where($where) -> save($data);
		if($result){
			$this -> success("认证成功");
			$this -> assign('jumpUrl','/index.php/'.GROUP_NAME);
		}else{
			$this -> success("未作修改");
			$this -> assign('jumpUrl','/index.php/'.GROUP_NAME);
		}
	}
	
	# 在审核和驳回前检查是否有1条以上的审核中的记录，如果有提示报告错误
	public function soft_permit_deny_hook($softid) {
        $soft_db = M('soft');
        $package = $soft_db->where(array("softid"=> $softid,"status"=>1,"hide"=>1))->field('package')->select();
		if (empty($package)) {
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/SoftEdit/soft_list' . '/p/' . $_GET['p'] . '/soft_status/'.$_GET['type']);
            $this->error("找不到${softid}对应的包，请报告开发人员。");
            return true;
        }
        $package = $package[0]['package'];
        # 2新软件 4编辑软件 5更新软件
        $info = $soft_db->where("package='${package}' AND status=1 AND hide in(2,4,5)")->select();
        if (count($info) > 1) {
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Soft/soft_list' . '/p/' . $_GET['p'] . '/soft_status/'.$_GET['type']);
            $this->error("软件ID（${softid}）状态可能存在问题，请报告开发人员。");
            return true;
        }
        return false;
    }
	//通过软件处理
	//软件管理__软件审核_编辑及新软件通过
    public function soft_permit() {
		$soft=M("soft");//soft表
		$soft_temporary=M("soft_temporary");//soft_临时表
		$soft_thumb=M("soft_thumb");//图片表
		$soft_thumb_temporary=M("soft_thumb_temporary");//图片临时表
		$soft_bookright=M("soft_bookright");//认证表
        $softid = $_GET['softid'];
		$type = isset($_GET['type']) ? $_GET['type'] : "0";
		if($_GET['p']){
		  $p = (int)$_GET['p'];
		}else{
		  $p = 1;
		}
		if($_GET['lr']){
		  $lr  = (int)$_GET['lr'];
		}else{
		  $lr  = 10;
		}
        $softids = array();
        if (strstr($softid, ",")) {
            $temp = explode(",", $softid);

            foreach ($temp as $val) {
                if (strlen($val) > 0)
                    $softids[] = $val;
            }
        }
        else {
            $softids[] = $softid;
        }
		$in_softid=implode(",",$softids);
		$soft_temporary_info_softid=$soft_temporary->where(array("status"=>1,"softid"=>array("in","{$in_softid}"),"hide"=>array(9)))->select();
        $n = 0;
        $c = count($soft_temporary_info_softid);
		foreach($soft_temporary_info_softid as $km=>$vm){
			$softids_array[]=$vm['softid'];
		}
        $package = '';
        //$curr_package = array();
		
        foreach ($softids_array as $k=>$v) {
			if ($this->soft_permit_deny_hook($v))//做个软件验证
            return;
		//以下代码就是开始更新数据库
			$soft_temporary_info=$soft_temporary->where(array("status"=>1,"softid"=>$v,"hide"=>array(9)))->find();
			if(empty($soft_temporary_info)||empty($soft_temporary_info['softname'])||empty($soft_temporary_info['category_id'])||empty($soft_temporary_info['intro'])){
				$soft_json_info=json_encode($soft_temporary_info).$soft_temporary->getlastsql();
				permanentlog("select_temporary_edit.log","softid为{$v}的查询记录为空:".$soft_json_info);
				continue;
			} 
			$soft_data['softname']=$soft_temporary_info['softname'];
			$soft_data['ename']=$soft_temporary_info['ename'];
			$soft_data['category_id']=$soft_temporary_info['category_id'];
			$soft_data['dev_name']=$soft_temporary_info['dev_name'];
			$soft_data['dever_page']=$soft_temporary_info['dever_page'];
			$soft_data['update_content']=$soft_temporary_info['update_content'];
			$soft_data['intro']=$soft_temporary_info['intro'];
			$soft_data['tags']=$soft_temporary_info['tags'];
			$soft_data['dever_email']=$soft_temporary_info['dever_email'];
			$soft_data['last_refresh']=$soft_temporary_info['update_tm'];
			$old_result=$soft->where(array("status"=>1,"hide"=>1,"softid"=>$v))->find();
			$result=$soft->where(array("status"=>1,"hide"=>1,"softid"=>$v))->save($soft_data);
			if(!$result){
				
				$soft_json=json_encode($soft_data).$soft->getlastsql();
				permanentlog("update_soft_edit.log",$soft_json);
				continue;
			}else{
				//$curr_package[$old_result['package']]=1;
				//更新临时表
				$soft_temporary->where(array("status"=>1,"hide"=>9,"softid"=>$v))->save(array("hide"=>1));
				$n += 1;
			}
			//写log日志
			if($soft_temporary_info['identity_pic']||$soft_temporary_info['right_pic']||$soft_temporary_info['business_pic']){
				if(!empty($soft_temporary_info['identity_pic'])){
					$soft_bookright_data['identity_pic']=$soft_temporary_info['identity_pic'];
				}
				if(!empty($soft_temporary_info['right_pic'])){
					$soft_bookright_data['right_pic']=$soft_temporary_info['right_pic'];
				}
				if(!empty($soft_temporary_info['business_pic'])){
					$soft_bookright_data['business_pic']=$soft_temporary_info['business_pic'];
				}
				$soft_bookright_data['upload_tm']=$soft_temporary_info['update_tm'];
				//查询是否存在过认证
				$count=$soft_bookright->where(array("softid"=>$v,"status"=>1))->count();
				if(empty($count)){
					$soft_bookright_data['softid']=$v;
					$soft_bookright_data['status']=1;
					$soft_bookright_result=$soft_bookright->add($soft_bookright_data);
				}else{
					$soft_bookright_result=$soft_bookright->where(array("softid"=>$v,"status"=>1))->save($soft_bookright_data);
				}
				if(!$soft_bookright_result){
					$soft_bookright_son=json_encode($soft_bookright_data).$soft_bookright->getlastsql();
					permanentlog("update_soft_bookright_edit.log",$soft_bookright_son);
					$n=$n-1;
				}
			}
			$soft_thumb_temporary_info=$soft_thumb_temporary->where(array("status"=>1,"softid"=>$v))->select();
			if($soft_thumb_temporary_info){
				$thumb_where=array(
					"softid"=>$v,
					"status"=>1,
				);
				$thumb_data=array(
					"status"=>0,
				);
				$thumb_result=$soft_thumb->where($thumb_where)->save($thumb_data);
				if($thumb_result){
					foreach($soft_thumb_temporary_info as $key=>$val){
						unset($val['id']);
						$thumb_new_result=$soft_thumb->add($val);
					}
				}else{
					$thumb_json=$soft_thumb->getlastsql();
					permanentlog("update_soft_thumb_error.log",$thumb_json);
				}
			}
            $this->writelog('审核通过了软件ID为' . $v . '软件包名为'.$old_result['package'].'的软件',"sj_soft",$v,__ACTION__);
        }
	if ($n == $c) {
            if ($c == 1  && !strstr($softid, ",")){
                $this->ajaxReturn(1,"通过成功！",1);
            }
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/SoftEdit/soft_list/p/' . $p . '/lr/'.$lr.'/soft_status/'.$type);
            $this->success("通过成功！");
        } else {
        	if ($c == 1 && $type != 'below' && !strstr($softid, ",")){
                $this->ajaxReturn(0,"通过失败（${n}/${c}）！",0);
            }
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/SoftEdit/soft_list/p/' . $p . '/lr/'.$lr.'/soft_status'.$type);
            $this->error("通过失败（${n}/${c}）！");

        }
    }	
	
	   //软件管理__软件驳回
    public function soft_deny() {
        if ($_GET['denymsg'] == "未通过" || $_GET['denymsg'] == "type") {
            $this->error("请填写具体驳回信息！！！");
        }
        $type = $_GET['type'];
        if (empty($type)) {
            $type = 0;
        }
        $softid = $_GET['softid'];
        if ($softid[0] == ',') {
            $softid = substr($softid, 1);
        }
        $denyid = $_GET['denyid'];
        if (empty($denyid)) {
            $denymsg = $_GET['denymsg'];
            if (empty($denymsg)) {
                $this->error("驳回信息不能为空！！！");
            }
        } else {
            $conf_db = D('Sj.Config');
            $map = '';
            $map['configname'] = $denyid;
            $map['config_type'] = 'denymsg';
			$map['status'] = 1;
            $denymsg = $conf_db->where($map)->getField('configcontent');
			if($_GET['denymsg']!="gggg"){
				$denymsg=$denymsg."\n\r".$_GET['denymsg'];
			}
        }
		//程序代码处理了
		$softid_array=explode(",",$softid );
		$c = count($softid_array);
		$n=0;
		foreach($softid_array as $k=>$v){
			if ($this->soft_permit_deny_hook($v))//做个软件验证
            return;
			$result=$this->update_soft($denymsg,$v);
			if($result){
				$n +=1;
			}else{
				continue;
			}
		}
		if($c==$n){
			if($c==1 && !strstr($_GET['softid'], ",")){
				$this->ajaxReturn(1,"驳回成功！",1);
			}
			$this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/SoftEdit/soft_list/soft_status/' . $type . '/p/' . $_GET['p'] . '/');
            $this->success("驳回成功！");
		}else{
			if($c==1&&!strstr($_GET['softid'], ",")){
				$this->ajaxReturn(0,"驳回失败（${n}/${c}）！",0);
			}
			$this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/SoftEdit/soft_list/soft_status/' . $type . '/p/' . $_GET['p'] . '/');
            $this->error("驳回失败！");
		}
    }
	//驳回功能更新软件方法
	function update_soft($denymsg,$softid){
		$soft=M("soft");
		$soft_temporary=M("soft_temporary");
		$package = $soft->where(array("status"=>1,"softid"=>$softid))->find();
		$result=$soft->where(array("status"=>1,"softid"=>$softid))->save(array("deny_msg"=>$denymsg));
		if($result){
			$result_temporary=$soft_temporary->where(array("status"=>1,"softid"=>$softid))->save(array("deny_msg"=>$denymsg,"hide"=>6));
			$denymsg_json=$soft_temporary->getlastsql()." | 执行结果为：{$result_temporary}\n";
			permanentlog("soft_deny_sql.log",$denymsg_json);
			if($result_temporary){
				$this->writelog('驳回了软件ID为' . $softid . '软件包名为'.$package['package'].'的软件，内容为' . $denymsg,"sj_soft_temporary");
				return true;
			}else{
				$denymsg_json=$soft_temporary->getlastsql();
				permanentlog("update_soft_denymsg_error.log",$denymsg_json);
				return false;
			}
		}else{
			$denymsg_json=$soft->getlastsql();
			permanentlog("update_soft_denymsg_error.log",$denymsg_json);
			return false;
		}
	}
	
	  //软件管理__软件编辑_显示
    public function soft_edit() {
        $softid = $_GET['softid'];
		$type = isset($_GET['type']) ? $_GET['type'] : "0";
		$p=isset($_GET['p']) ? $_GET['p'] : "1";
		if($_GET['lr']){
		  $lr  = (int)$_GET['lr'];
		}else{
		  $lr  = 10;
		}
		$return_url="soft_status/".$type."/p/{$p}/lr/{$lr}";
        if (empty($softid)) {
            $this->assign("jumpUrl", "/index.php/" . GROUP_NAME . "/SoftEdit/soft_list/{$return_url}");
            $this->error("非法操作失败,如频繁出现，请联系管理员！");
        }
		$bookright_result=array();
		//获取临时表数据
		$soft_list=M("soft_temporary");
		$soft_result=$soft_list->where(array("status"=>1,"hide"=>9,"softid"=>$softid))->find();
		//获取版权认证图片
		if($soft_result['identity_pic']){
			$bookright_result['identity_pic']=$soft_result['identity_pic'];
		}
		if($soft_result['right_pic']){
			$bookright_result['right_pic']=$soft_result['right_pic'];
		}
		if($soft_result['business_pic']){
			$bookright_result['business_pic']=$soft_result['business_pic'];
		}
		if(empty($bookright_result)){
			$bookright_result=0;
		}
		$this->assign('bookright_result', $bookright_result);
		
   
        $soft_thumb_db = M('soft_thumb_temporary');
        $map = '';
        $map['softid'] = $softid;
        $map['status'] = 1;
        $soft_thumb_list = $soft_thumb_db->where($map)->order('rank')->field('id,url,rank')->select();
        $thumblist = array();
        for ($i = 0; $i < count($soft_thumb_list); $i++) {
            $thumblist[$soft_thumb_list[$i]['rank']]['id'] = $soft_thumb_list[$i]['id'];
            $thumblist[$soft_thumb_list[$i]['rank']]['url'] = $soft_thumb_list[$i]['url'];
            $thumblist[$soft_thumb_list[$i]['rank']]['rank'] = $soft_thumb_list[$i]['rank'];
        }
        $soft_thumb_list = $thumblist;
        if ($soft_result['category_id'][0] == ',') {
            $soft_result['category_id'] = substr($soft_result['category_id'], 1);
        }
        $tnum = strlen($soft_result['category_id']);
        $tnum--;
        if ($soft_result['category_id'][$tnum] == ',') {
            $soft_result['category_id'] = substr($soft_result['category_id'], 0, -1);
        }
        $cid = explode(',', $soft_result['category_id']);
   		$soft_result['softname_r']=checkword($soft_result['softname'],$soft_list);
		$soft_result['ename_r']=checkword($soft_result['ename'],$soft_list);
		$soft_result['tags_r']=checkword($soft_result['tags'],$soft_list);
		$soft_result['intro_r']=checkword_intro($soft_result['intro'],$soft_list);
		$soft_result['dev_name_r']=checkword($soft_result['dev_name'],$soft_list);
        $this->assign('softlist', $soft_result);
        $this->assign('thumblist', $soft_thumb_list);
		$category = D('Sj.Category');
		$array_config=array(
			"categoryid"=>"categoryid[]",
			"selected"=>$cid[0]
		);
		$conf_list = $category->getCategory($array_config);
        $this->assign('conflist',$conf_list);
        $this->assign('soft_status', $type);
        $this->assign('return_url', $return_url);
        $this->display();
    }
	
	//获取编辑软件信息
	function soft_preview() {
        if (empty($_GET['softid'])) {
            $this->error("软件ID未提供。");
        }
        $softid = $_GET['softid'];
        $soft_db = M('soft_temporary');
        $soft_info = $soft_db->where(array("softid" => $softid,"status"=>1))->find();
		$category_id = $soft_info['category_id'];
		$right_categoryid = array(',55,',',53,'); //需要验证版权的类别
		if(in_array($category_id,$right_categoryid)){
			$bookright_thumb =array();
			if($soft_info['identity_pic']){
			$bookright_thumb['identity_pic']=$soft_info['identity_pic'];
			}
			if($soft_info['right_pic']){
				$bookright_thumb['right_pic']=$soft_info['right_pic'];
			}
			if($soft_info['business_pic']){
				$bookright_thumb['business_pic']=$soft_info['business_pic'];
			}
			if($bookright_thumb){
				$this -> assign('soft_bookright_img' ,$bookright_thumb);
			}
		}
        if (empty($soft_info)) {
            $this->error("软件${softid}未找到。");
        }
        $soft_thumb_db = M('soft_thumb_temporary');
        $soft_thumb = M('soft_thumb');
        $soft_thumb_info = $soft_thumb_db->where(array("softid" => $softid, "status" => 1))->select();
		if(empty($soft_thumb_info)){
			$soft_thumb_info = $soft_thumb->where(array("softid" => $softid, "status" => 1))->select();
		}
        $soft_file_db = M('soft_file');
        $soft_file_info = $soft_file_db->where("softid=${softid} AND package_status > 0")->select();
        foreach ($soft_file_info as $v) {
            $soft_info['iconurl'] = $v['iconurl'];
            $soft_info['fileurl'] = $v['url'];
        }
		///print_r($soft_info);exit;

		$soft_info['softname']=checkword($soft_info['softname'],$soft_db);
		$soft_info['ename']=checkword($soft_info['ename'],$soft_db);
		$soft_info['tags']=checkword($soft_info['tags'],$soft_db);
		$soft_info['intro']=checkword($soft_info['intro'],$soft_db);
        $this->assign('soft_info', $soft_info);
        $this->assign('soft_thumb_info', $soft_thumb_info);
        $this->assign('category_id', $category_id);
        $this->display();
    }
	
	
	//编辑处理页面
	    //软件管理__软件编辑_提交
    public function soft_edit_upload() {
		$soft_db = M('soft_temporary');           //软件表
		$model = new Model();
        $softid = trim($_POST['softid']);
        $soft_status = $_POST['soft_status'];
        $return_url = $_POST['return_url'];
        if (empty($soft_status)) {
            $soft_status=9;
        }
		if (empty($softid)) {
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/SoftEdit/soft_list/' . $return_url . '');
            $this->error('出现了一些小错误，请返回列表页');
        } 

        $soft_list['softname'] = trim($_POST['softname']);
        $soft_list['ename'] = trim($_POST['ename']);
		$soft_list['category_id'] = implode(',', $_POST['categoryid']);
        $soft_list['category_id'] = ',' . $soft_list['category_id'] . ',';
		$soft_list['dev_name'] = $_POST['dev_name'];
        $soft_list['dever_email'] = $_POST['dever_email'];
        $soft_list['dever_page'] = $_POST['dever_page'];
        $soft_list['intro'] = $_POST['intro'];
        $soft_list['update_content'] = $_POST['update_content'];

        $thumbcid = '';
        $thumbcid = $_POST['categoryid'];
        if ($thumbcid[0] == 0) {
            $this->error('请选择软件类别');
        }
        if ($thumbcid[0] == $thumbcid[1] && $thumbcid[0] != 0) {
            $this->assign("jumpUrl", "/index.php/" . GROUP_NAME . "/SoftEdit/soft_edit/softid/{$softid}/type/{$soft_status}/p/{$p}/");
            $this->error('对不起,软件类别，不可重复');
        }
        if ($thumbcid[1] == $thumbcid[2] && $thumbcid[1] != 0) {
            $this->assign("jumpUrl", "/index.php/" . GROUP_NAME . "/SoftEdit/soft_edit/softid/{$softid}/type/{$soft_status}/p/{$p}/");
            $this->error('对不起,软件类别，不可重复');
        }
        if ($thumbcid[0] == $thumbcid[2] && $thumbcid[1] != 0) {
            $this->assign("jumpUrl", "/index.php/" . GROUP_NAME . "/SoftEdit/soft_edit/softid/{$softid}/type/{$soft_status}/p/{$p}/");
            $this->error('对不起,软件类别，不可重复');
        }

        if (!eregi("[\u4e00-\u9fa5]", $soft_list['ename']) && $soft_list['ename'] != '') {
            $this->assign("jumpUrl", "/index.php/" . GROUP_NAME . "/SoftEdit/soft_edit/softid/{$softid}/type/{$soft_status}/p/{$p}/");
            $this->error('对不起,软件英文名暂不支持中文');
            exit;
        }
        if (mb_strlen($_POST['intro'], 'utf-8') > 1500) {
            $this->assign("jumpUrl", "/index.php/" . GROUP_NAME . "/SoftEdit/soft_edit/softid/{$softid}/type/{$soft_status}/p/{$p}/");
            $this->error('对不起，软件描述不得超过1500字');
        }

        if (empty($_POST['softname'])) {
            $this->assign("jumpUrl", "/index.php/" . GROUP_NAME . "/SoftEdit/soft_edit/softid/{$softid}/type/{$soft_status}/p/{$p}/");
            $this->error('软件名为必填项');
        }

        if (empty($_POST['intro'])) {
            $this->assign("jumpUrl", "/index.php/" . GROUP_NAME . "/SoftEdit/soft_edit/softid/{$softid}/type/{$soft_status}/p/{$p}/");
            $this->error('软件描述为必填项');
        }

		
		$util = D('Sj.Util');
		$param = array(
			'softname' => $soft_list['softname'],
			'dev_name' => $soft_list['dev_name'],
			'intro' => $soft_list['intro'],
		);

		$result = $util->filter_word($param);
	    if ($result['softname'][0] == false) {
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . "/SoftEdit/soft_edit/softid/{$softid}/type/{$soft_status}/p/{$p}/");
            $this->error("软件名含有非法字符 {$result['softname'][1]}，请重新编辑后提交！");
        }

		if ($result['dev_name'][0] == false) {
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . "/SoftEdit/soft_edit/softid/{$softid}/type/{$soft_status}/p/{$p}/");
            $this->error("开发者含有非法字符 {$result['dev_name'][1]}，请重新编辑后提交！");
        }
		if ($result['intro'][0] == false) {
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . "/SoftEdit/soft_edit/softid/{$softid}/type/{$soft_status}/p/{$p}/");
            $this->error("软件描含有非法字符 {$result['intro'][1]}，请重新编辑后提交！");
        }
		$soft_list['tags'] = $_POST['tags'];
        $soft_list['update_tm'] = time();
		if (empty($soft_list['tags'])) {
            $soft_list['tags'] = $soft_list['softname'];
        }
        $tags = explode(' ', $soft_list['tags']);
        if (strpos($soft_list['tags'], ',')){
        	$tag_arr = str_replace(',', ' ', $soft_list['tags']);
        	$tags = explode(' ', $tag_arr);
        }
    	for ($i=0; $i<count($tags); $i++){
			if (mb_strlen(trim($tags[$i]), 'utf-8') > 10 || mb_strlen(trim($tags[$i]), 'utf-8') < 0){
            	$this->assign('jumpUrl', '/index.php/' . GROUP_NAME . "/SoftEdit/soft_edit/softid/{$softid}/type/{$soft_status}/p/{$p}/");
            	$this->error("每个关键词最多5个字！谢谢");
				return ;
			}
		}
        if (count($tags) > 5) {
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . "/SoftEdit/soft_edit/softid/{$softid}/type/{$soft_status}/p/{$p}/");
            $this->error("最多5个关键字，谢谢");
        }
		//删除指定图片
        $dellist = '';
        $dellist = $_POST['delimg'];
        if (!empty($dellist)) {

            for ($i = 0; $i < count($dellist); $i++) {
                $map = '';
                $map['status'] = '0';
                $soft_thumb_db->where('id=' . $dellist[$i])->save($map);
            }
            $log_mesage .= "删除软件截图\n";
        }
		//图片处理
        $num = count($_FILES["image"]["name"]);
        for ($i = 0; $i < $num; $i++) {
            if (empty($_FILES["image"]["name"][$i])) {
                unset($_FILES["image"]["name"][$i]);
                unset($_FILES["image"]["type"][$i]);
                unset($_FILES["image"]["tmp_name"][$i]);
                unset($_FILES["image"]["error"][$i]);
                unset($_FILES["image"]["size"][$i]);
            } else {
                $imagerank[] = $i;
            }
        }
        //附件上传
        if (!empty($_FILES['image']['size'])) {
            $path = date("Ym/d/");
            $config = array(
                'multi_config' => array(
                    'image' => array(
                        'savepath' => UPLOAD_PATH . '/thumb/' . $path,
						'savepath_ori' => UPLOAD_PATH . '/thumb_ori/' . $path,
                        'saveRule' => 'thumbname',
                    )
                ),
                'flip' => true,
				'img_p_size' => 1024*30,  //图片常规压缩大小
				'img_p_width'=> 320, //图片常规压缩宽度
				'img_p_height'=> 534, //图片常规压缩宽度
				'img_s_size' => 1024*10, //图片缩略图大小
				'img_s_width' => 150, //缩略图宽
				'img_ext' => '.jpg', //截图文件扩展名
            );
            $lists = $this->_uploadapk(true, $config);
			$soft_thumb_db = M('soft_thumb_temporary');
			$soft_thumb_db -> ping();
			$soft_thumb_db -> flush();
            $imagelist = $lists['image'];
            $thumb_log = '';
            for ($i = 0; $i < count($imagelist); $i++) {
                $map = '';
                $map['softid'] = $softid;
                $map['status'] = 1;
                $map['rank'] = $imagerank[$i];
                $thumbmap = '';
                $thumbmap['status'] = 0;
                $soft_thumb_db->where($map)->save($thumbmap);
                $soft_thumb_list['softid'] = $softid;
                $soft_thumb_list['url'] = $imagelist[$i]['url'];
                $soft_thumb_list['image_raw'] = $imagelist[$i]['url_original'];
                $soft_thumb_list['image_thumb'] = $imagelist[$i]['url_resize'];
                $soft_thumb_list['status'] = 1;
                $soft_thumb_list['rank'] = $imagelist[$i]['key'];
                $soft_thumb_list['upload_time'] = time();
                $soft_thumb_list['last_refresh'] = time();
                if (false == $soft_thumb_db->add($soft_thumb_list)) {
                    $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . "/SoftEdit/soft_edit/softid/{$softid}/type/{$soft_status}/p/{$p}/");
                    $this->error("编辑软件截图失败,数据插入发生错误！");
                } else {
                    $thumb_log = "更新软件截图\n";
                }
            }
		}
		$msg.=$thumb_log;
        //更新临时表软件
		$update_where=array(
			"status"=>1,
			"hide"=>9,
			"softid"=>$softid,
		);
		$old_soft_list=$soft_db->where($update_where)->find();
        if (false == $soft_db->where($update_where)->save($soft_list)) {
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME ."/SoftEdit/soft_edit/softid/{$softid}/type/{$soft_status}/p/{$p}/");
            $this->error("编辑软件失败,数据插入发生错误！");
        }else{
			$configModel = D('Sj.Config');
        	$column_desc = $configModel->getSoftColumnDesc();
			$zh_msg='';
        	foreach ($soft_list as $key => $val) {
        		if (isset($column_desc[$key]) && $soft_list[$key] != $old_soft_list[$key]) {
        			$desc = $column_desc[$key];
					if($key=="intro"){
							$zh_msg .= "修改了描述 \n";
						}else{
							$zh_msg .= "将{$desc} 从'{$old_soft_list[$key]}'修改成 '{$soft_list[$key]}'\n";
					}
        		}
        	}
			$msg .=$zh_msg."\n".$log_mesage;
            $this->writelog($msg,"sj_soft_temporary",$softid);
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . "/SoftEdit/soft_list/{$return_url}");
            $this->success("编辑成功！");
		}
    }
}
?>
