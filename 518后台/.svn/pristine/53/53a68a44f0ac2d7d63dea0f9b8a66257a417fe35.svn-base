<?php
/* 搜索相关词管理 */
class Search_relatedAction extends CommonAction{
	function search_related_list()
	{
        // 搜索条件拼成个字符串
        $param = '';
        foreach ($_GET as $key => $value) 
		{
            $param .= $param ? '&' : '';
            $param .= "{$key}={$value}";
        }
		$search_related = M("search_related");
		$key = escape_string($_GET['search_key_words']);
		if($this->strlen_az($key) > 16)
		{
			$this -> error('您要搜索的关键字不存在！！');
		}
		$where=array(
			'srh_key' =>array('like','%'.$key.'%'),
			'status' => 1,
		);
		//$key_where = $key ? " and srh_key like '%".$key."%'" : '';
        //$where = 'status = 1'.$key_where;
		$search_related_count = $search_related -> where($where) -> count();
        import("@.ORG.Page");
		$p = new Page ($search_related_count, 25);
		$search_related_list = $search_related -> where($where)->limit($p->firstRow.','.$p->listRows) ->order("update_tm desc") -> select();
        $page = $p->show();
		$this -> assign("page",$page);
		$this -> assign("key",$key);
		$this -> assign('key_list',$search_related_list);
        $this -> assign('param', $param);
		$this -> display('search_related_list');
	}
	
	function search_related_add()
	{
		$search_related = M("search_related");
		$srh_key = trim($_POST['srh_key']);
        // 关键字统一转小写
        $srh_key = strtolower($srh_key);
		$data['srh_key'] = $srh_key;
		/*if(mb_strlen($data['srh_key'],'UTF-8')>8)
		{
			$this -> error('添加的关键字不要超过8个字');
		}*/
		if($this->strlen_az($data['srh_key'])>16)
		{
			$this -> error('请输入8个汉字，16个字符内');
		}
		$data['create_tm'] = time();
		$data['update_tm'] = time();
		$data['status'] = 1;
		$data['admin_id'] = $_SESSION['admin']['admin_id'];
		$where=array(
			'srh_key'=>$srh_key,
			'status' =>1,
		);
		$result = $search_related ->where($where) ->select();
		if($result)
		{
			$this -> error("搜索相关词字已存在！！");
		}
		else
		{
			$save_data = $search_related ->add($data);
			if($save_data)
			{
				$this->writelog("搜索相关词管理:添加关键字{$srh_key}id:".$save_data,"sj_search_related",$save_data,__ACTION__ ,"","add");
				$this -> assign('jumpUrl','/index.php/'.GROUP_NAME.'/Search_related/search_related_list');
				$this -> success("关键字添加成功"); 
			}
			else
			{
				$this -> error("关键字添加失败");
			}
		}
	}
	function search_related_update()
	{
        // 接受条件拼成个字符串并传出去
		//跳转页面使用
        $param = '';
        foreach ($_GET as $key => $value) 
		{
            if ($key == 'id') continue;
            $param .= $param ? '&' : '';
            $param .= "{$key}={$value}";
        }
		$id = escape_string($_GET['id']);
		if(strlen($id) > 12)
		{
			$this -> error('你的操作失误 请确认你的操作');
		}
		$sk_db = M("search_related");
		$where=array(
			'id' => $id,
		);
		$info = $sk_db -> where($where)-> find();
		$this -> assign("key_info",$info);
		$this -> assign("id",$id);
        $this -> assign("param", $param);
		$this -> display("search_related_update");
	}
    function search_related_update_do()
	{
		$id = escape_string($_POST['id']);
		$srh_key = $_POST['srh_key'];
        // 关键字统一转小写
        $srh_key = strtolower($srh_key);
		if(strlen($id) > 12)
		{
			$this -> error('您的操作有误');
		}
		if($this->strlen_az($srh_key)>16)
		{
			$this -> error('请输入8个汉字，16个字符以内');
		}
		$sk_db = M("search_related");
		$where=array(
			'id' => $id,
			'status' => 1,
		);
		$old_sk = $sk_db->where($where)->find();
		$where_have=array(
			'srh_key' => escape_string($srh_key),
			'id' => array('neq',$id),
			'status' =>1,
		);
		//$count = $sk_db -> where("srh_key = '".escape_string($srh_key)."' and id <> ".$id." and status = 1") -> count('id');
		$have_result = $sk_db ->where($where_have)->find();
		if($have_result)
		{
		  $this -> error("关键词已存在");
		}
        $data['srh_key'] = $srh_key;
		$data['update_tm'] = time();
		$data['admin_id'] = $_SESSION['admin']['admin_id'];
		
		$log = $this->logcheck(array('id'=>$id),'sj_search_related',$data,$sk_db);
		$affect = $sk_db -> where($where) -> save($data);

		if($affect)
		{
			$this->writelog("搜索相关词管理:编辑了id为{$id}的{$log}的内容","sj_search_related",$id,__ACTION__ ,"","edit");
		    $this -> assign('jumpUrl','/index.php/'.GROUP_NAME.'/Search_related/search_related_list?'.$_POST['param']);
		    $this -> success("关键字修改成功");
		}
		else
		{
		    $this -> error("关键字修改失败");
		}
	}
	function search_related_content_add()
	{
		$sk_db = M("search_related");
	    $sk_pkg_db = M("search_related_content");
		$id = escape_string($_GET['id']);
		$related_keys = escape_string($_GET['related_keys']);
		//新增加搜索状态
		$search_status = $_GET['search_status'];
		if(strlen($id) > 12)
		{
			$this -> error('您的操作有误');
		}
		$sk_info = $sk_db -> where(array('id' => $id)) -> find();
		if($search_status==3)//未开始
		{
			$where=array(
			'kid' => $id,
			'status' =>1,
			'related_keys' =>array('like','%'.$related_keys.'%'),
			'start_tm' =>array('gt',time()),
			);
		}
		else if($search_status==2)//已过期
		{
			$where=array(
			'kid' => $id,
			'status' =>1,
			'related_keys' =>array('like','%'.$related_keys.'%'),
			'end_tm' =>array('lt',time()),
			);
		}
		else//默认正在运营的
		{
			$where=array(
			'kid' => $id,
			'status' =>1,
			'related_keys' =>array('like','%'.$related_keys.'%'),
			'start_tm' =>array('elt',time()),
			'end_tm' =>array('egt',time()),
			);
		}
		
		$count = $sk_pkg_db -> where($where) -> count();
		import("@.ORG.Page");
		$p = new Page($count, 25);
		$sk_pkg_list = $sk_pkg_db -> where($where)->limit($p->firstRow.','.$p->listRows) ->order("rank asc,start_tm asc") -> select();
		$page = $p->show ();
		$this -> assign("page",$page);
		$util = D("Sj.Util"); 
		foreach($sk_pkg_list as $key => $info)
		{
			//$srh_key = $sk_db -> where(array('id' => $info['kid'])) -> getField("srh_key");
			if($info['guide_page']==1)
			{
				$sk_pkg_list[$key]['guid_page_info'] = "搜索结果页";
			}
			else if($info['guide_page']==2)
			{
				$soft_where=array(
					'package' =>$info['package'],
					'status' =>1,
					'hide' =>1,
				);
				$soft_result = $sk_pkg_db->table('sj_soft')->where($soft_where)->find();
				$sk_pkg_list[$key]['guid_page_info'] = "软件详情页:".$info['package']."/".$soft_result['softname'];
			}
			$sk_pkg_list[$key]['key_name'] = $srh_key;
			$typelist = $util->getHomeExtentSoftTypeList($info['co_type']);
			foreach($typelist as $k => $v){
				if($v[1] == true)
				{
					$sk_pkg_list[$key]['types'] = $v[0];
				}
			}
		}
		//合作形式
		$typelist = $util->getHomeExtentSoftTypeList();
		$this->assign('typelist',$typelist);

		$this -> assign("page",$page);
		$this -> assign("end_tm",date("Y-m-d 23:59:59",time()+24*3600*7));
		$this -> assign("start_tm",date("Y-m-d 00:00:00",time()));
		$this -> assign("sk_info",$sk_info);
		$this -> assign("srh_key",$sk_info['srh_key']);
		$this -> assign("related_keys",$_GET['related_keys']);
		$this -> assign("search_status",$_GET['search_status']);
		$this -> assign("sk_pkg_list",$sk_pkg_list);
		$this -> display("search_related_content_add");
	}
    
    
    
	function search_related_content_add_do()
	{
		$sk_db = M("search_related");
	    $sk_pkg_db = M("search_related_content");
		$soft_db = M("soft");

		//如果已经有有效页面存在不允许添加其他
		//$this->search_related_content_add_precheck(false);
        // 将_POST或_GET传进来的参数统一转成与表里字段一样的名称
        $column_convert_arr = array(
			'srh_key' => 'srh_key',
			'related_keys' =>'related_keys',
			'guide_page' =>'guide_page',
			'package' => 'package',
            'rank' => 'rank',
            'start_tm' => 'start_tm',
            'end_tm' => 'end_tm',
			'co_type' => 'co_type'
        );
        $check_column_arr = array();
        foreach($column_convert_arr as $key=>$value) 
		{
            if (array_key_exists($key, $_POST)) {
                $check_column_arr[$value] = $_POST[$key];
            }
        }
		
        foreach($check_column_arr as $key=>$value) 
		{
            $check_column_arr[$key] = trim($value);
        }
		 
        // 调用通用的检查函数
        $content_arr = array();
        $content_arr[0] = $check_column_arr;
		$AdSearch = D("Sj.AdSearch");
        $error_msg = $AdSearch->logic_check_related($content_arr);
        $qualified_flag = true;
        foreach($error_msg as $key=>$value) 
		{
            if ($value['flag'] == 1)
                $qualified_flag = false;
        }
        if (!$qualified_flag) 
		{
            $msg = $error_msg[0]['msg'];
            // 业务逻辑：设置返回的跳转页面
            $this->error($msg);
        }
		
		$data['related_keys'] = $_POST['related_keys'];
		$data['guide_page'] = $_POST['guide_page'];
		if($_POST['guide_page']==2)
		{
			$data['package'] = $_POST['package'];
		}
		else
		{
			$data['package'] = '';
		}
        $data['kid'] = $_POST['kid'];
		$data['update_tm'] = time();
		$data['create_tm'] = time();
		$data['start_tm'] = strtotime($_POST['start_tm']);
		$data['end_tm'] = strtotime($_POST['end_tm']);
		$data['status'] = 1;
		$data['rank'] = $_POST['rank'];
		$data['admin_id'] = $_SESSION['admin']['admin_id'];
		
		if(isset($_POST['co_type']))
		{
			$data['co_type'] = $_POST['co_type'];
		}else{
			$data['co_type'] = 0;
		}

		if(strlen($data['kid']) >= 10)
		{
			$this -> error('您的操作有误');
		}
		//屏蔽软件上排期时报警需求 新增  yuesai
		if($data['package'])
		{
			$AdSearch = D("Sj.AdSearch");
			$shield_error=$AdSearch->check_shield($data['package'],$data['start_tm'],$data['end_tm'],1);
			if($shield_error){
				$this -> error($shield_error);
			}
		}
		$affect = $sk_pkg_db -> add($data);
		
		if($affect)
		{
			$this->writelog('搜索相关词管理:添加相关词内容'.$affect.'相关词为'.$data['related_keys'],"sj_search_related_content",$affect,__ACTION__ ,"","add");
		    $this -> assign('jumpUrl','/index.php/'.GROUP_NAME.'/Search_related/search_related_content_add/id/'.$_POST['kid']);
		    $this -> success("相关词添加成功");
		}else{
			$this -> error("添加失败！！");
		}
	}
	function search_related_delete()
	{
		$id = escape_string($_GET['id']);
		$sk_db = M("search_related");
		$sk_pkg_db = M("search_related_content");
		if(strlen($id) > 12)
		{
			$this -> error('您的操作有误');
		}
		$data=array(
			'update_tm' =>time(),
			'status' =>0,
		);
		$affect = $sk_db -> where(array('id' => $id)) -> save($data);
		if($affect)
		{
			$have_content = $sk_pkg_db -> where(array('kid' => $id)) -> select();
			if($have_content)
			{
				$affected = $sk_pkg_db -> where(array('kid' => $id)) -> save($data);
				if($affected)
				{
					$this->writelog('搜索相关词管理:删除关键字id为'.$id.'的记录',"sj_search_related",$id,__ACTION__ ,"","del");
					$this -> assign('jumpUrl','/index.php/'.GROUP_NAME.'/Search_related/search_related_list');
					$this -> success("关键字及包删除成功");
				}
				else
				{
					$data['status'] = 1;
					$data['update_tm'] = time();
					$affect = $sk_db -> where(array('id' => $id)) -> save($data);
					$this -> error("关键字删除失败");
				}
			}
			else
			{
				//说明关键字没有相关词 
				$this->writelog('搜索相关词管理:删除关键字id为'.$id.'的记录',"sj_search_related",$id,__ACTION__ ,"","del");
				$this -> assign('jumpUrl','/index.php/'.GROUP_NAME.'/Search_related/search_related_list');
				$this -> success("关键字删除成功");
			}
			//$this -> assign('jumpUrl','/index.php/'.GROUP_NAME.'/search_related/search_related_list');
			$this -> success("关键字删除成功");
		}
		else
		{
			$this -> error("关键字删除失败");
		}
	}
	function search_related_content_delete()
	{
		$id = escape_string($_GET['id']);
		$sk_pkg_db = M("search_related_content");
		if(strlen($id) > 10)
		{
			$this -> error('您的操作有误');
		}
		$data['status'] = 0;
		$data['update_tm'] = time();
		$result=$sk_pkg_db -> where(array("id"=>$id,"status"=>1)) -> find();
		$affect = $sk_pkg_db -> where("id = ".$id) -> save($data);
	
		if($affect)
		{
			$this->writelog('搜索相关词管理:删除相关词 id为'.$id.'相关词为'.$result['related_keys'],"sj_search_related_content",$id,__ACTION__ ,"","del");
		    $this -> assign('jumpUrl','/index.php/'.GROUP_NAME.'/Search_related/search_related_content_add/id/'.$_GET['kid']);
		    $this -> success("相关词已经删除");
		}else{
		   $this -> error("相关词删除失败");
		}
	}
	
	function search_related_content_update()
	{
		
		$kid = escape_string($_GET['kid']);
		$id_str = $_GET['id'];
		$arr = explode("&",$id_str);
		$id = escape_string($arr[0]);
		$sk_pkg_db = M("search_related_content");
		$sk_db = M("search_related");
		
		if(is_numeric($kid))
		{
			$srh_key = $sk_db -> where(array('id' => $kid)) -> getField("srh_key");
			$info = $sk_pkg_db -> where(array('kid' =>$kid,'id' =>$id)) -> find();
		}
		else
		{
			$info = $sk_pkg_db -> where(array('id' =>$id)) -> find();
			$srh_key = $sk_db -> where(array('id' => $info['kid'])) -> getField("srh_key");
		}
		$util = D("Sj.Util");
		$typelist = $util->getHomeExtentSoftTypeList($info['co_type']);
		$this->assign('typelist',$typelist);
		$this -> assign("srh_key",$srh_key);
		$this -> assign("pkginfo",$info);
		$this -> display("search_related_content_update");
	}

	function search_related_content_update_do(){
		$sk_pkg_db = M("search_related_content");
		$sk_db = M("search_related");
        
        // 将_POST或_GET传进来的参数统一转成与表里字段一样的名称
		$column_convert_arr = array(
			'id' => 'id',
			'srh_key' => 'srh_key',
			'related_keys' => 'related_keys',
			'guide_page' => 'guide_page',
			'package' => 'package',
            'rank' => 'rank',
            'start_tm' => 'start_tm',
            'end_tm' => 'end_tm',
			'co_type' => 'co_type'
        );
        $check_column_arr = array();
        foreach($column_convert_arr as $key=>$value) 
		{
            if (array_key_exists($key, $_POST)) {
                $check_column_arr[$value] = $_POST[$key];
            }
        }
        foreach($check_column_arr as $key=>$value) 
		{
            $check_column_arr[$key] = trim($value);
        }
        // 调用通用的检查函数
        $content_arr = array();
        $content_arr[0] = $check_column_arr;
		$AdSearch = D("Sj.AdSearch");
        $error_msg = $AdSearch->logic_check_related($content_arr);
        $qualified_flag = true;
        foreach($error_msg as $key=>$value) 
		{
            if ($value['flag'] == 1)
                $qualified_flag = false;
        }
        if (!$qualified_flag) 
		{
            $msg = $error_msg[0]['msg'];
            // 业务逻辑：设置返回的跳转页面
            $this->error($msg);
        }
        
		$kid = escape_string($_POST['kid']);
		$id = escape_string($_POST['id']);
		if($_POST['guide_page']==2)
		{
			$data['package'] = $_POST['package'];
		}
		else
		{
			$data['package'] = "";
		}
		
		$data['start_tm'] = strtotime($_POST['start_tm']);
		$data['end_tm'] = strtotime($_POST['end_tm']);
		$data['update_tm'] = time();
		$data['related_keys'] =  trim($_POST['related_keys']);
		$data['rank'] = $_POST['rank'];
		$data['guide_page'] = $_POST['guide_page'];
		$data['admin_id'] = $_SESSION['admin']['admin_id'];
		
		if(isset($_POST['co_type'])){
				$data['co_type'] = $_POST['co_type'];
			}else{
				$data['co_type'] = 0;
			}

		if($data['rank'] <= 0 || $data['rank'] >20)
		{
			$this -> error("排序必须大于0小于20");
		}
		//屏蔽软件上排期时报警需求 新增  yuesai
		if($data['package'])
		{
			$AdSearch = D("Sj.AdSearch");
			$shield_error=$AdSearch->check_shield($data['package'],$data['start_tm'],$data['end_tm'],1);
			if($shield_error){
				$this -> error($shield_error);
			}
		}
		
		$log_result = $this -> logcheck(array('id' => $id),'sj_search_related_content',$data,$sk_pkg_db);
		
		$affect = $sk_pkg_db -> where(array('kid' => $kid,'id' =>$id)) -> save($data);
		if($affect)
		{
			$this->writelog("搜索相关词管理:编辑了id为{$id}的{$log_result}的内容","sj_search_related_content",$id,__ACTION__ ,"","edit");
		    $this -> assign('jumpUrl','/index.php/'.GROUP_NAME.'/Search_related/search_related_content_add/id/'.$kid);
		    $this -> success("相关词修改成功");
		}else{
			$this -> error("相关词修改失败");
		}
	}
    
    // 初始单条错误信息，初始化信息：flag为0，msg为空
    function init_error_msg(&$error_msg, $key) {
        if (!isset($error_msg))
            $error_msg = array();
        $error_msg[$key] = array('flag' => 0,'msg' => '');
    }
    
    // 添加错误信息
    function append_error_msg(&$error_msg, $key, $flag, $msg) {
        if (!isset($error_msg[$key])) {
            $this->init_error_msg($error_msg, $key);
        }
        $error_msg[$key]['flag'] |= $flag;
        $error_msg[$key]['msg'] .= $msg;
    }
    
    // 只检查导入文件的手工填写内容，并将其数据格式转成与网页版的添加单条软件一致
    // 1，将每一行数组的key由数字转成对应数据库的列名，如0为extend_id，1为extent_name...
    // 2，将某些列的字符串转成数字，如是、否转化成1，0......
    function handwriting_convert_and_check(&$content_arr) {
        // 初始化错误信息数组
        $error_msg = array();
        foreach($content_arr as $key => $value) {
            $this->init_error_msg($error_msg, $key);
        }
        // 业务逻辑：将表里字段名称和模版里列的名称一一对应
        $correct_title_arr = array(
            'srh_key'     =>  '关键字',
			'related_keys'  =>   '相关词',
			'guide_page' => '导向页面(1:搜索结果页,2:软件详情页)',
            'package'  =>   '包名',
            'rank'  =>   '排序',
            'start_tm'  =>   '开始时间(yyyy/MM/dd)',
            'end_tm'  =>   '结束时间(yyyy/MM/dd)',
			'co_type' => '合作形式',
        );
        // trim一下所有的数据
        foreach($content_arr as $key=>$record) 
		{
            foreach($record as $r_key=>$r_record) {
                $content_arr[$key][$r_key] = trim($r_record);
            }
        }
        // 给$content_arr里的每一行记录的每一列下标由数字改成对应名称
        $new_content_arr = array();
        $new_key = array();
        // 将$correct_title_arr里的key值提取出来依次放在$new_key里
        foreach($correct_title_arr as $key => $value) {
            $new_key[] = $key;
        }
        foreach($content_arr as $key=>$record) {
            foreach($new_key as $new_key_key=>$new_key_value) {
                if (isset($record[$new_key_key])) {
                    $new_content_arr[$key][$new_key_value] = $record[$new_key_key];
                }
            }
        }
        $content_arr = $new_content_arr;
        // 业务逻辑：检查列填写是否为预期内容（固定的内容），如果是则换成对应数据，如果不是则添加错误信息
        $expected_words = array();
        // 有些字段输入为空时是合法的，有些字段输入为空不允许，当为空不允许时，将其值设为false以作区别
		//合作形式
		$util = D("Sj.Util");
		$typelist = $util->get_cooperation();
		$expected_words['co_type'] =$typelist;
		$expected_words['guide_page'] = array("1" => 1, "2" => 2);
        foreach($content_arr as $key=>$record) {
            // 开始检查每列内容是否为预期内容
            foreach($record as $r_key=>$r_value) {
                if (array_key_exists($r_key, $expected_words)) {
                    if (!array_key_exists($r_value, $expected_words[$r_key])) {
                        $column = $correct_title_arr[$r_key];
                        $this->append_error_msg($error_msg, $key, 1, "【{$column}】列内容填写有误;");
                        $content_arr[$key][$r_key] = false;
                    } else {
                        $tmp = $expected_words[$r_key][$r_value];
                        // 检查是否为false，如果不是，则表示可以为空，替换成相应的数字，否则不处理，即还是为空，在logic_check()里会进行非空值判断
                        if ($tmp !== false)
                            $content_arr[$key][$r_key] = $tmp;
                    }
                }
                // 自动填充批量导入的时间
                if ($r_key == 'start_tm' || $r_key == 'end_tm') {
                    if ($r_key == 'start_tm') {
                        $type = 0;
                        $hint = '开始';
                    } else {
                        $type = 1;
                        $hint = '结束';
                    }
                    if (!preg_match('/^T/', $content_arr[$key][$r_key])) {
                        $this->append_error_msg($error_msg, $key, 1, "{$hint}时间需以T开头;");
                    } else {
                        $content_arr[$key][$r_key] = preg_replace('/^T/', '', $content_arr[$key][$r_key]);
                    }
                    $ret = $this->auto_convert_time($content_arr[$key][$r_key], $type);
                    if ($ret) {
                        $content_arr[$key][$r_key] = $ret;
                    }// else转换错误，保持原始值，后面的logic_check会校验原始格式
                }
            }
        }
        return $error_msg;
    }
    
    // 统一的逻辑检查：检查添加软件数据是否合法
    function logic_check_related($content_arr) 
	{
        // 初始化错误信息数组
        $error_msg = array();
        foreach($content_arr as $key => $value) 
		{
            $this->init_error_msg($error_msg, $key);
        }
        // 业务逻辑：区间表、区间软件表
        // 获得三个表的model
        $keyword_model = M('search_related');
        $keyword_soft_model = M('search_related_content');
        $soft_model = M("soft");//软件大表
        // 业务逻辑：以下为各项具体检查
        foreach($content_arr as $key=>$record) 
		{
			// 检查开始时间
            if (isset($record['start_tm']) && $record['start_tm'] != "") 
			{
                if (!preg_match("/^\d{4}(\-|\/|\.)\d{1,2}(\-|\/|\.)\d{1,2}\s+\d{1,2}:\d{1,2}:\d{1,2}$/", $record['start_tm'])) 
				{
                    $this->append_error_msg($error_msg, $key, 1, "开始时间日期格式不对;");
                } 
				else 
				{
                    $time = strtotime($record['start_tm']);
                    if ($time) 
					{
                        $content_arr[$key]['bk_start_time'] = $time;
                    }
					else 
					{
                        $this->append_error_msg($error_msg, $key, 1, "开始时间日期格式不对;");
                    }
                }
            } else {
                $this->append_error_msg($error_msg, $key, 1, "开始时间不能为空;");
            }
            // 检查结束时间
            if (isset($record['end_tm']) && $record['end_tm'] != "") 
			{
                if (!preg_match("/^\d{4}(\-|\/|\.)\d{1,2}(\-|\/|\.)\d{1,2}\s+\d{1,2}:\d{1,2}:\d{1,2}$/", $record['end_tm'])) 
				{
                    $this->append_error_msg($error_msg, $key, 1, "结束时间日期格式不对;");
                } else {
                    $time = strtotime($record['end_tm']);
                    if ($time) 
					{
                        $content_arr[$key]['bk_end_time'] = $time;
                    } else {
                        $this->append_error_msg($error_msg, $key, 1, "结束时间日期格式不对;");
                    }
                }
            } else {
                $this->append_error_msg($error_msg, $key, 1, "结束时间不能为空;");
            }
            // 检查开始时间是否小于结束时间
            if (isset($content_arr[$key]['bk_start_time']) && isset($content_arr[$key]['bk_end_time'])) 
			{
                if ($content_arr[$key]['bk_start_time'] > $content_arr[$key]['bk_end_time']) {
                    $this->append_error_msg($error_msg, $key, 1, "开始时间需小于结束时间;");
                }
            }
			if(isset($record['guide_page']))
			{
				if($record['guide_page']==2)
				{
					//检查包名
					if (isset($record['package']) && $record['package'] != "") 
					{ 
						// 检查包名是否在sj_soft表里
						$where = array(
							'package' => $record['package'],
							'status' => 1,
							'hide' => array('EQ', 1),
						);
						$find = $soft_model->where($where)->find();
						if (!$find) 
						{
							$this->append_error_msg($error_msg, $key, 1, "包名【{$record['package']}】不存在于市场软件库中;");
						}
					} else {
						$this->append_error_msg($error_msg, $key, 1, "包名不能为空;");
					}
				}
			}else{
				$this->append_error_msg($error_msg, $key, 1, "导向页面不能为空;");
			}
		
			// 检查排序是否为数字
			if (isset($record['rank']) && $record['rank'] != "") 
			{
				if (!preg_match("/^\d+$/", $record['rank'])) 
				{
					$this->append_error_msg($error_msg, $key, 1, "排序应为整数;");
				}
				if($record['rank']<1||$record['rank']>20)
				{
					$this->append_error_msg($error_msg, $key, 1, "排序应为1~20的整数;");
				}
			}
			else 
			{
				$this->append_error_msg($error_msg, $key, 1, "排序值不能为空;");
			}
			//关键字 	单条记录的关键字为默认，批量上传检查是否有关键字，没有添加关键字
			if (isset($record['srh_key']) && $record['srh_key'] != "") 
			{
                /*$where = array(
                    'srh_key' => array('EQ', $record['srh_key']),
                    'status' => array('EQ', 1),
                );
                $find = $keyword_model->where($where)->find();
                if (!$find) 
				{
					//没有关键字就添加关键字
					$data=array(
						'srh_key' => $record['srh_key'],
						//'start_tm' => $content_arr[$key]['bk_start_time'],
						//'end_tm' => $content_arr[$key]['bk_end_time'],
						'create_tm' => time(),
						'update_tm' => time(),
						'status' => 1,
					);
					$result= $keyword_model->add($data);
					if($result)
					{
						$this->writelog("市场搜索管理_搜索提示运营_添加关键字{$record['srh_key']}id:".$result,"sj_search_related");
					}
					else
					{
						$this->append_error_msg($error_msg, $key, 1, "关键字【{$record['srh_key']}】添加失败;");
					}
                } */
            }
			else
			{
                $this->append_error_msg($error_msg, $key, 1, "关键字不能为空;");
            }
			//相关词	单条记录的关键字为默认，批量上传检查是否有关键字，没有添加关键字
			if (isset($record['related_keys']) && $record['related_keys'] != "") 
			{
				//相关词8个汉字16个字符内
				if($this->strlen_az($record['related_keys'])>16)
				{
					$this->append_error_msg($error_msg, $key, 1, "相关词请输入8个汉字，16个字符内;");
				}
            }
			else
			{
                $this->append_error_msg($error_msg, $key, 1, "相关词不能为空;");
            }
        }
        
        // 检查行与行之间的数据是否冲突（主要检查相同包名的区间是否有冲突）
        foreach($content_arr as $key1=>$record1) 
		{
            // 如果1填写时间的不完善，则不比较
            if (!isset($record1['bk_start_time']) || !isset($record1['bk_end_time']))
                continue;
            foreach($content_arr as $key2=>$record2) {
                $k1 = $key1 + 1;
                $k2 = $key2 + 1;
                // 比较过的不比较
                if ($key1 >= $key2)
                    continue;
                // 关键字不一样，不比较
                if ($record1['srh_key'] != $record2['srh_key'])
                    continue;
               
                // 如果2填写时间的不完善，则不比较
                if (!isset($record2['bk_start_time']) || !isset($record2['bk_end_time']))
                    continue;
				 // 相关词相同
                if ($record1['related_keys'] == $record2['related_keys']) 
				{ 
					// 时间是否交叉
					if ($record1['bk_start_time'] <= $record2['bk_end_time'] && $record2['bk_start_time'] <= $record1['bk_end_time']) {
						$k1 = $key1 + 1; $k2 = $key2 + 1;
						$this->append_error_msg($error_msg, $key1, 1, "同一相关词下，投放区间与第{$k2}行有重叠;");
						$this->append_error_msg($error_msg, $key2, 1, "同一相关词下，投放区间与第{$k1}行有重叠;");
					}
                }
                // 排序相同
                if ($record1['rank'] == $record2['rank']) 
				{
					// 时间是否交叉
					if ($record1['bk_start_time'] <= $record2['bk_end_time'] && $record2['bk_start_time'] <= $record1['bk_end_time']) {
						$k1 = $key1 + 1; $k2 = $key2 + 1;
						$this->append_error_msg($error_msg, $key1, 1, "同一排序下，投放区间与第{$k2}行有重叠;");
						$this->append_error_msg($error_msg, $key2, 1, "同一排序下，投放区间与第{$k1}行有重叠;");
					}  
                }
            }
        }
        
        // 检查每一行数据是否与数据库的存储内容相冲突
        foreach($content_arr as $key=>$record) 
		{
			//根据srh_key获取srh_key的id
			$srh_key_result=$keyword_model->where(array('srh_key' => $record['srh_key'],'status'=>1 ))->find();
			if(!$srh_key_result['id'])
				continue; //关键词没有ID说明是新增加的词，数据库中肯定没有不用比较
            $where = array(
                'kid' => array('EQ', $srh_key_result['id']),
                'status' => array('NEQ', 0),
            );
            if (isset($record['id']))
                $where['id'] = array('NEQ', $record['id']);
            $db_records = $keyword_soft_model->where($where)->select();
			if (!$db_records)
				continue;
			// 检查时间、位置（排序）冲突
			// 如果填写时间的不完善，则不比较
			if (!isset($record['bk_start_time']) || !isset($record['bk_end_time']))
				continue;
			foreach($db_records as $db_record) 
			{
				if ($record['related_keys'] == $db_record['related_keys']) 
				{
					// 将开始时间和结束时间转成时间戳
					$start1 = $record['bk_start_time']; 
					$end1 = $record['bk_end_time'];
					$start2 = $db_record['start_tm']; 
					$end2 = $db_record['end_tm'];
					if ($start1 <= $end2 && $start2 <= $end1) 
					{
						$start_at_str = date('Y-m-d H:i:s',$db_record['start_tm']);
						$end_at_str = date('Y-m-d H:i:s',$db_record['end_tm']);
						$status_paused_hint = "";
						if ($db_record['status'] == 2) 
						{
							$status_paused_hint = "，该记录处于已停用状态，请前往批量明细列表中操作";
						}
						$this->append_error_msg($error_msg, $key, 1, "同一相关词下，投放时间与后台id为【{$db_record['id']}】，相关词为【{$db_record['related_keys']}】的记录有重叠（该投放时间从【{$start_at_str}】到【{$end_at_str}】{$status_paused_hint}）;");
					}
				}
				if ($record['rank'] == $db_record['rank']) 
				{
					// 将开始时间和结束时间转成时间戳
					$start1 = $record['bk_start_time']; 
					$end1 = $record['bk_end_time'];
					$start2 = $db_record['start_tm']; 
					$end2 = $db_record['end_tm'];
					if ($start1 <= $end2 && $start2 <= $end1) 
					{
						$start_at_str = date('Y-m-d H:i:s',$db_record['start_tm']);
						$end_at_str = date('Y-m-d H:i:s',$db_record['end_tm']);
						$status_paused_hint = "";
						if ($db_record['status'] == 2)
						{
							$status_paused_hint = "，该记录处于已停用状态，请前往批量明细列表中操作";
						}
						$this->append_error_msg($error_msg, $key, 1, "同一位置下，投放时间与后台id为【{$db_record['id']}】，相关词为【{$db_record['related_keys']}】的记录有重叠（该投放时间从【{$start_at_str}】到【{$end_at_str}】{$status_paused_hint}）;");
					}
				}
			}  
        }
        return $error_msg;
    }
    
    // 第一行标题列忽略，只保存之后的内容
    function import_file_to_array($file) {
        // $file = "/media/sf_D_DRIVE/shouye-gbk.csv";
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
            }
        }
        return $content_arr;
    }
    
    function import_array_convert_and_check(&$content_arr) {
        // 文件转换数据前的检查（是否可以转化成与页面数据格式一致）
        $error_msg1 = $this->handwriting_convert_and_check($content_arr);
        // 文件转换数据后的检查（区间是否有效、排期是否冲突等）
		$AdSearch = D("Sj.AdSearch");
        $error_msg2 = $AdSearch->logic_check_related($content_arr);
		//$error_msg2 = $this->logic_check_related($content_arr);
        // 将$error_msg2合并到$error_msg1里并返回$error_msg1
         //屏蔽软件上排期时报警需求 新增  yuesai
		//$AdSearch = D("Sj.AdSearch");
        $error_msg3 = $AdSearch->logic_check_shield($content_arr,'start_tm','end_tm','',1);
        foreach($error_msg2 as $key=>$value) {
            if (!array_key_exists($key, $error_msg1)) {
                $this->init_error_msg($error_msg1, $key);
            }
            $this->append_error_msg($error_msg1, $key, $value['flag'], $value['msg']);
        }
        foreach($error_msg3 as $key=>$value) {
            if (!array_key_exists($key, $error_msg1)) {
                $this->init_error_msg($error_msg1, $key);
            }
            $this->append_error_msg($error_msg1, $key, $value['flag'], $value['msg']);
        }
        return $error_msg1;
    }
    // 下载批量导入模版
   /* function down_moban() {
        $file_dir = C("ADLIST_PATH") . "search_related_import_moban.csv";
        if (file_exists($file_dir)) {
            $file = fopen($file_dir,"r");
            Header("Content-type: application/octet-stream");
            Header("Accept-Ranges: bytes");
            Header("Accept-Length: ".filesize($file_dir));
            Header("Content-Disposition: attachment; filename=" . urlencode('搜索相关词列表批量导入模版') . ".csv");
            echo fread($file, filesize($file_dir));
            fclose($file);
            exit(0);
        } else {
            header("Content-type: text/html; charset=utf-8");
            echo "File not found!";
            exit;
        }
    }*/
    
    // 批量导入访问的页面节点
    function import_softs() 
	{
        /*if ($_GET['down_moban']) 
		{
            $this->down_moban();
        } 
		else */
		if ($_FILES) 
		{
			$AdSearch = D("Sj.AdSearch");
            $err = $_FILES["upload_file"]["error"];
            if ($err) {
                $this->ajaxReturn($err,"上传文件错误，错误码为{$err}！", -1);
            }
            $file_name = $_FILES['upload_file']['name'];
            $tmp_arr = explode(".", $file_name);
            $name_suffix = array_pop($tmp_arr);
            if (strtoupper($name_suffix) != "CSV") {
                $this->ajaxReturn("",'请上传CSV格式文件！', -2);
            }
            $tmp_name = $_FILES['upload_file']['tmp_name'];
            $content_arr = $this->import_file_to_array($tmp_name);
            if ($content_arr == -1) {
                $this->ajaxReturn("",'文件打开错误，请检查文件是否损坏！', -3);
            } else if (empty($content_arr)) {
                $this->ajaxReturn("",'文件数据内容不能为空！', -4);
            }
            // 返回检查结果的错误信息，如果记录的flag为1表示有错误
            $error_msg = $this->import_array_convert_and_check($content_arr);
            $flag = true;
            foreach($error_msg as $key=>$value) {
                if ($value['flag'] == 1)
                    $flag = false;
				//针对rank值处理
				preg_match('/rank=([\d]+)/',$value['msg'],$matches);
				if($matches){
					$value['rank'] = $matches[1];
					$value['msg'] = preg_replace('/rank=([\d]+)/',' ',$value['msg']);
				}
				$error_msg[$key] = $value;
            }
            if (!$flag) {
				$AdSearch -> get_error_file($error_msg,$content_arr,'related');	
                $this->ajaxReturn($error_msg,'您上传的CSV有如下问题，请修改后重新上传！', -5);
            }
            // 判断后台有没有人正在导入
            $lock_name = 'sj_search_related_content_importing';
            $import_lock = S($lock_name);
            if ($import_lock) {
                $this->ajaxReturn("",'后台有人正在导入，请稍后再尝试！', 1);
            }
            // 上锁，设置60秒内有效
            S($lock_name, 1, 60, 'File');
            // 返回导入结果，如果记录的flag为0表示添加失败
            $result_arr = $this->process_import_array($content_arr);
            // 导入后解锁
            S($lock_name, NULL);
            $flag = true;
            foreach($result_arr as $key=>$value) {
                if ($value['flag']==0)
                    $flag = false;
            }
            // save the import file for backups
            $save_dir = IMPORT_FILE_UPLOAD_PATH;
            $this->mkDirs($save_dir);
            $save_name = MODULE_NAME. '_' . ACTION_NAME  . '_' . time() . '_' . $_SESSION['admin']['admin_id'] . '.csv';
            $save_file_name = $save_dir . $save_name;
            move_uploaded_file($_FILES['upload_file']['tmp_name'], $save_file_name);
            $this->writelog("搜索相关词管理：批量导入了{$save_file_name}。");
            if ($flag) {
                $this->ajaxReturn("",'导入成功！', 0);
            } else {
                $this->ajaxReturn($result_arr,'存在部分导入失败记录！', -6);
            }
        } else {
            $this->display("import_softs");
        }
    }
    
    // 业务逻辑：将批量导入文件里所有数据添加进数据库，返回结果为每一行添加是否成功标志符
    function process_import_array($content_arr) {
		if(C('is_test') == 1){
			$anzhi_ad_type = 1;
		}else{
			$anzhi_ad_type = 0;
		}		
        $result_arr = array();
        $model = M('search_related_content');
		$related_model = M('search_related');
		$AdSearch = D("Sj.AdSearch");
        $arr_shields=array();
        foreach($content_arr as $key => $record) 
		{
			 $map = array();
			//根据关键字获取关键字的ID 即kid
			$where=array(
				'srh_key' => $record['srh_key'],
				'status'  =>1,
			);
			$result=$related_model-> where($where) ->find();
			if (!$result) 
			{
				//没有关键字就添加关键字
				$data=array(
					'srh_key' => $record['srh_key'],
					'create_tm' => time(),
					'update_tm' => time(),
					'anzhi_ad_type' => $anzhi_ad_type,
					'status' => 1,
				);
				$result_related= $related_model->add($data);
				if($result_related)
				{
					$map['kid'] = $result_tips;
					$this->writelog("市场搜索管理_搜索相关词管理_添加关键字{$record['srh_key']}id:".$result_related,"sj_search_related",$result_related,__ACTION__ ,"","add");
				}
			} 
			else
			{
				$map['kid'] = $result['id'];
			}
       
            // 设置默认值
			$map['status'] = 1;
			$map['anzhi_ad_type'] = $anzhi_ad_type;
            $map['create_tm'] = time();
			$map['update_tm'] = time();
            // 赋值，以下为必填的值
			$map['related_keys'] = $record['related_keys'];
			$map['guide_page'] = $record['guide_page'];
			if($record['guide_page']==2)
			{
				$map['package'] = $record['package'];
			}
			else
			{
				$map['package'] = "";
			}
			$map['rank'] = $record['rank'];
			$map['start_tm'] = strtotime($record['start_tm']);
			$map['end_tm'] = strtotime($record['end_tm']);
			$map['co_type'] = isset($record['co_type']) ? $record['co_type'] : 0;
			$map['admin_id'] = $_SESSION['admin']['admin_id'];
		
		    $data_error=$AdSearch->pub_check_soft_filter($map['package']);
            if($data_error && $data_error['code']==1){
            	$result_arr[$key]=array('flag'=>0,'msg'=>$data_error['message'],'package'=>$map['package']);
            	$arr_shields[]=$map;
            	continue;
            }

            // 添加到表中
			if ($id = $model->add($map)) {
				$this->writelog('搜索关键字管理_搜索相关词管理_添加关键字'.$record['srh_key'].' 内容'.$id.'相关词为'.$map['related_keys'],"sj_search_related",$result_related,__ACTION__ ,"","add");
                $result_arr[$key]['flag'] = 1;
                $result_arr[$key]['msg'] = "添加成功";
			} 
			// else {
                // 未知原因添加失败
                // $result_arr[$key]['flag'] = 0;
                // $result_arr[$key]['msg'] = "添加失败";
			// }
        }
        if(count($arr_shields) && $file_data=$AdSearch->generate_ignore_file($arr_shields,'sj_search_related_content')){
        	$result_arr['table_name']='sj_search_related_content';
        	$result_arr['filename']=$file_data['filename'];
        }
        return $result_arr;
    }
}


