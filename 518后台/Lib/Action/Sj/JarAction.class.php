<?php
class JarAction extends CommonAction {
	


	public $sdk_types  = array(array('id' => 1,'name' => '网游'), array('id' => 2,'name' => '单机'));


	// SDK热更新 显示页面
    function jar_index_list() {
		$model = new Model();
        $where = array(
            'status' => 1,
        );
		
		//搜索 
		$sdk = $model->table('sj_sdk_jar')->where($where)->order('edit_time DESC')->select();//JAR版本//SDK版本
		
        // trim一下
        foreach ($_GET as $key => $value) {
            $_GET[$key] = trim($value);
        }
		//print_r($_GET);
        // 记录页数参数
        $url_param = "";
        foreach ($_GET as $key => $value) {
            if ($url_param != '')
                $url_param .= "&";
            if ($value != '')
                $url_param .= "{$key}={$value}";
        }
		// 搜索JAR版本号
        if ($_GET['search_sdk_type']) {
            $where['sdk_type'] = $_GET['search_sdk_type'];
        }
		
		
        // 搜索开始时间
        if ($_GET['startDate'] != '') {
			$startDate = strtotime($_GET['startDate']);
            $where['edit_time'] =array('egt', $startDate);
        }
        // 搜索结束时间
        if ($_GET['endDate'] != '') {
			$endDate =  strtotime($_GET['endDate'].'+1day');
            $where['edit_time'] =array('elt', $endDate);
        }
		
		 if ($_GET['startDate'] != '') {
			$startDate = strtotime($_GET['startDate']);
            $where['edit_time'] =array('egt', $startDate);
        }
        // 搜索开始 结束时间
        if ($_GET['endDate'] != '' && $_GET['startDate'] != '') {
			$endDate =  strtotime($_GET['endDate'].'+1day');
			$startDate = strtotime($_GET['startDate']);
            $where['edit_time'] =array('between',array($startDate,$endDate));
        }
		
		
        $count = $model->table('sj_sdk_jar')->where($where)->order('edit_time DESC')->count();
        import("@.ORG.Page");
        $page = new Page($count, 10);
        $show = $page->show(); //分页显示输出
        $list = $model->table('sj_sdk_jar')->where($where)->order('edit_time DESC')->limit($page->firstRow . ',' . $page->listRows)->select();
        
		if($startDate != ''){
			$this->assign('startDate', date('Y-m-d',$startDate));//开始时间
		}
		if($endDate != ''){
			$endDate =  strtotime($_GET['endDate']);
			$this->assign('endDate', date('Y-m-d',$endDate));//结束时间
		}
		

		foreach ($list as $k => $v) {
			$list[$k]['sdk_type_name'] = $this->sdk_types[$v['sdk_type'] - 1]['name'];
		}

		//echo $model -> getlastsql();
		// 搜索内容
		$this->assign('sdk', $sdk);//SDK更新版本
		//$this->assign('search_info_type', $_GET['search_info_type']);//搜索 SDK更新版本 
        $this->assign('search_sdk_type', $_GET['search_sdk_type']);
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->assign('sdk_types',$this->sdk_types);
        $this->assign('apkurl', GAMEINFO_ATTACHMENT_HOST);
        $this->assign('function_name', __FUNCTION__);
        $this->assign('url_param', $url_param);
        $this->display();
    }
	
	//Jar编辑显示
    function jar_edit_show() {
        $model = new Model();
		$where['id'] = $_GET['id'];
		$where['status'] = 1;
        $jar = $model->table('sj_sdk_jar')->where($where)->select();//JAR版本
		
		$this->assign('sdk_types',$this->sdk_types);
		$this->assign('jar',$jar[0]);
        $this->display();
    }
	
	//编辑jar 处理程序
	function jar_edit_do(){
		$model =new Model();
		//重复判断
		$where1 = array(
			'jar_version' => $_POST['jar_version'],
            'status' => 1,
			'id' => array('neq',$_POST['id'])
        );
		$sj_sdk_jar = $model->table('sj_sdk_jar')->where($where1)->select();
		foreach($sj_sdk_jar as $v){
			if($v['jar_version'] == $_POST['jar_version']){
				$this->error("修改失败,已经存在版本号：".$_POST['jar_version']);
			}
			/*else if($v['jar_name'] == $_POST['jar_name']){
				$this->error("修改失败,已经存在版本名称：".$_POST['jar_name']);
			};*/
		}
		$where['id'] = $_POST['id'];
		
		//写入数据库
		$data['jar_version'] = $_POST['jar_version']; //接单表数据 jar 版本号
		$data['jar_name'] = $_POST['jar_name']; //接单表数据 jar 版本名称
		$data['sdk_type'] = intval($_POST['sdk_type']);//jar sdk类别
		$data['edit_time'] = time();//更新修改时间
		
		$result = $model->table('sj_sdk_jar')->where($where)->save($data);

		if ($result) {
            $this->writelog("成功修改ID为{$_POST['id']}的Jar版本",'sj_sdk_jar', $_POST['id'],__ACTION__ ,"","edit");
			$this -> assign('jumpUrl', '/index.php/Sj/Jar/jar_index_list');
            $this->success("修改成功");
        }else{
            $this->writelog("修改ID为{$_POST['id']}的Jar版本失败",'sj_sdk_jar', $_POST['id'],__ACTION__ ,"","edit");
			$this->error("修改失败");
		}
	}
	
	//JAR版本删除
    function jar_update_del() {
        $model = new Model();
		$where['id'] = $_GET['id'];
		$data['status'] = 0;
		$result = $model->table("sj_sdk_jar")->where($where)->save($data);
		if($result)
		{
			$this->writelog("删除id为{$_GET['id']}的JAR版本",'sj_sdk_jar', $_GET['id'],__ACTION__ ,"","del");
			$this->success("删除成功");
		}
		else
		{
			$this->success("删除失败");
		}
    }
	
}
