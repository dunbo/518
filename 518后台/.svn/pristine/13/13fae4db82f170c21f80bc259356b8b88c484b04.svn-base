<?php
class TextQuickEntryAction extends CommonAction {
    private $image_width = 48;
    private $image_height = 48;
	private $image_width_6 = 60;
    private $image_height_6 = 60;
	private $image_width_62 = 204;
    private $image_height_62 =78;
	private $words_des = 30;
	private $gif_width = 60;
    private $gif_height =60;
    private $gif_width_62 = 204;
    private $gif_height_62 =78;
	private $image_width_64 = 68;
    private $image_height_64 =68;
	private $gif_width_64 = 68;
    private $gif_height_64 =68;
	
    public function index() {
        //////////////////////// 准备工作
        // 产品平台
        $util = D("Sj.Util");
        // 普通频道所有页面
        $category_list = ContentTypeModel::getCategoryTypesOfTextQuickEntry();
		 // 普通频道所有页面
        $bd_list = ContentTypeModel::getbdList();
		$this->assign('bd_list', $bd_list);
		
        // 所有渠道
        $channel_model = M('channel');
        $channels = $channel_model->field("`cid`,`chname`")->where(array('status' => 1))->select();
        $channels_key = array();
        foreach($channels as $v) {
            $channels_key[$v['cid']] = $v['chname'];
        }
        // 所有运营商
        $operating_db = D('Sj.Operating');
		$operating_list = $operating_db->field('oid,mname')->select();
		$operators_key = array();
		foreach($operating_list as $v) {
			$operators_key[$v['oid']] = $v['mname'];
		}
        
		//6.4 增加内容合作  显示红包助手
		
		//合作站点和合作频道 展示
		$coop_new_arr1 =  ContentTypeModel::getCoopChannel(2);
		$coop_new_arr2 =  ContentTypeModel::getCoopChannel(4);
		$coop_new_arr = array_merge($coop_new_arr1,$coop_new_arr2);
		$this->assign('coop_result',$coop_new_arr);
        // 搜索条件
        $where = array();
        
        //////////////////////// 应该处理的GET参数
        // 平台
        $pid = 1;//默认为1
        if ($_GET['pid']) {
            $pid = $_GET['pid'];
        }
        $this->assign('pid', $pid);
        $product_list = $util->getProducts($pid);
        $where['pid'] = $pid;
        // 页面类型
        $general_page_type = $_GET['general_page_type'] ? $_GET['general_page_type'] : 0;
        if ($general_page_type) {
            $where['belong_page_type'] = ContentTypeModel::getWhereConditionOfPageType($general_page_type);
            $this->assign('general_page_type', $general_page_type);
        }
        // 所属具体页面
        $belong_page_type == '';
        if ($_GET['belong_page_type']) {
            // 直接传类型
            $belong_page_type = $_GET['belong_page_type'];
        } else if ($_GET['page_name']) {
            // 如果是通过搜索标签页面名称
            $page_name = $_GET['page_name'];
            $belong_page_type = ContentTypeModel::convertPageName2PageTypeOfTextQuickEntry($page_name, $general_page_type);
            if (!$belong_page_type) {
                $this->error("请输入正确的页面名称");
            }
            $this->assign('page_name', $page_name);
        }
        if ($belong_page_type) {
            // 重新计算类型类型
            $general_page_type = ContentTypeModel::getGeneralPageType($belong_page_type);
            $where['belong_page_type'] = $belong_page_type;            
            $this->assign('general_page_type', $general_page_type);
            $this->assign('belong_page_type', $belong_page_type);
        }
        $where['status'] = 1;
        $model = M();
        $extent_table = 'sj_textquickentry_extent';
        
        // 翻页
        import("@.ORG.Page");
        $limit = 10;
        $count = $model->table('sj_textquickentry_extent')->where($where)->count();
        $page  = new Page($count, $limit);
        
        $list = $model->table($extent_table)->where($where)->limit($page->firstRow.','.$page->listRows)->select();
        
        // 处理list
        foreach ($list as $key => $value) {
            // 渠道名称
            $list[$key]['chname'] = $value['cid'] ? $channels_key[$value['cid']] : '-';
            $list[$key]['oname'] = $value['oid'] ? $operators_key[$value['oid']] : '-';
            $list[$key]['belong_page_name'] = ContentTypeModel::convertPageType2PageNameOfTextQuickEntry($value['belong_page_type']);
            // 计算属于这个区间的软件数（已过期和未开始的不算，只算正在进行的）
            $now = time();
            $where = array(
                'extent_id' => $value['extent_id'],
                'start_at' => array('elt', $now),
                'end_at' => array('egt', $now),
                'status' => 1,
            );
            $list[$key]['count'] = $model->table('sj_textquickentry_extent_soft')->where($where)->count();
        }
        $this->assign('list', $list);
        $this->assign('page', $page->show());
        $this->assign('product_list', $product_list);
        $this->assign('category_list', $category_list);
        $this->display();
    }
    
    public function add_extent() {
		$model = M();
        if ($_POST) {
            $extent_name = trim($_POST['extent_name']);
            if (!$extent_name) {
                $this->error("区间名不能为空");
            }
            $cid = $_POST['cid'];
            if (!$cid)
                $cid = 0;
            $oid = $_POST['oid'];
            if (!$oid)
                $oid = 0;
            $pid = $_POST['pid'];
            if (!$pid) {
                $this->error("平台不能为空");
            }
			//V6.0快捷入口添加入口数量
			$entrance_count=$_POST['entrance_count'];
			if($entrance_count==0)
			{
				$this->error("入口数量不能为空");
			}
            $belong_page_type = trim($_POST['belong_page_type']);
            if (!$belong_page_type) {
                $this->error("所属页面不能为空");
            }
			//V6.2快捷入口添加样式选择
			$entrance_type=$_POST['entrance_type'];
			if($entrance_type==0)
			{
				$this->error("请选择入口样式");
			}
            // 检查区间名是否在当前页面已存在
            $where = array(
                'extent_name' => $extent_name,
                'pid' => $pid,
                'belong_page_type' => $belong_page_type,
                'status' => 1,
            );
            $find = $model->table('sj_textquickentry_extent')->where($where)->find();
            if ($find)
                $this->error("该页面已存在此区间名");
            // 检查相同pid，cid，oid，belong_page_type是否已存在区间
            // $where = array(
            //     'pid' => $pid,
            //     'cid' => $cid,
            //     'oid' => $oid,
            //     'belong_page_type' => $belong_page_type,
            //     'status' => 1,
            // );
            // $find = $model->table('sj_textquickentry_extent')->where($where)->find();
            // if ($find)
            //     $this->error("该页面已有快捷入口存在，请确认页面是否正确");
            // 添加
            $map = array();
            $map['extent_name'] = $extent_name;
            $map['cid'] = $cid;
            $map['oid'] = $oid;
            $map['pid'] = $pid;
            $map['belong_page_type'] = $belong_page_type;
            $map['create_time'] = $map['update_time'] = time();
			$map['entrance_count']= $entrance_count;
			$map['entrance_type']= $entrance_type;
			$map['admin_id'] = $_SESSION['admin']['admin_id'];
            $this->get_version($map);
            $ret = $model->table('sj_textquickentry_extent')->add($map);
            if ($ret) {
                $this->writelog("文字快捷入口：添加了id为{$ret}的区间",'sj_textquickentry_extent', $ret,__ACTION__ ,"","add");
                $this->success("添加成功！");
            } else {
                $this->error("添加失败！");
            }
        } else {
            // 平台
            $pid = $_GET['pid'];
            // 所属页面
            $belong_page_type = $_GET['belong_page_type'];
			//查找该页面是否已经创建了区间 如果第一次创建 样式可以选择 否则样式不能选择
			//2016-1-21 应该没用了
			/*$type_where=array(
				'status'=>1,
				'belong_page_type'=>$belong_page_type,
			);
			$extent_type_result = $model->table('sj_textquickentry_extent')->where($type_where)->find();
			if($extent_type_result)
			{
				$entrance_type = $extent_type_result['entrance_type'];
			}
			else
			{
				$entrance_type = 0;
			}*/
            // 所有运营商
            $operating_db = D('Sj.Operating');
            $operating_list = $operating_db->field('oid,mname')->select();
            $operators_key = array();
            foreach($operating_list as $v) {
                $operators_key[$v['oid']] = $v['mname'];
            }
            $this->assign('pid', $pid);
            $this->assign('belong_page_type', $belong_page_type);
			//$this->assign('entrance_type', $entrance_type);
            $this->assign('operating_list', $operating_list);
            $this->display();
        }
    }
    function get_version(&$map,$extent_id=''){
        $model = M();
        $entrance_type=$map['entrance_type'];
        $map['version_type'] = $_POST['type'];
        if($_POST['type']==1)
        {
            $map['version_code'] = $_POST['version_code1'];
            $this->check_version(array($map['version_code']),$extent_id,$map['pid']);
        }
        if($_POST['type']==2)
        {
            $map['version_code'] = $_POST['version_code2'];

            $this->check_version(array($map['version_code']),$extent_id,$map['pid']);

        }
        if($_POST['type']==3)
        {
            $map['version_code'] = $_POST['force_update_version'];
            $version_arr_new=explode(',', $_POST['force_update_version']);
            $this->check_version($version_arr_new,$extent_id,$map['pid']);
        }
        //6.4市场版本修改  必填(2016-8-4非必填)
        if(empty($map['version_code'])&&$entrance_type==4){
            $this->error('市场版本必填');
        }
        if($map['version_code'])
        {
            /*if($entrance_type==1)
            {
                if($_POST['type']==1)
                {
                    $this->error("该版本不支持该快捷入口");
                }
                else if($_POST['type']==2)
                {
                    if($map['version_code']>6110)
                    {
                        $this->error("该版本不支持该快捷入口");
                    }
                }
                else
                {
                    $ver_arr = explode(",",$map['version_code']);
                    $max_ver=max($ver_arr);
                    if($max_ver>6110)
                    {
                        $this->error("该版本不支持该快捷入口");
                    }
                }
            }*/
            if($entrance_type==2)
            {
                if($_POST['type']==1)
                {
                    if($map['version_code']<6200)
                    {
                        $this->error("该版本不支持该快捷入口");
                    }
                }
                else if($_POST['type']==2)
                {
                    $this->error("该版本不支持该快捷入口");
                }
                else
                {
                    $ver_arr = explode(",",$map['version_code']);
                    $min_ver=min(array_filter($ver_arr));
                    if($min_ver<6200)
                    {
                        $this->error("该版本不支持该快捷入口");
                    }
                }
            }
            else if($entrance_type==3||$entrance_type==4)
            {
                $version = 6400;
                if($entrance_type==4) $version = 6480;
                if($_POST['type']==1)
                {
                    if($map['version_code']<$version)
                    {
                        $this->error("该版本不支持该快捷入口");
                    }
                }
                else if($_POST['type']==2)
                {
                    $this->error("该版本不支持该快捷入口");
                }
                else
                {
                    $ver_arr = explode(",",$map['version_code']);
                    $min_ver=min(array_filter($ver_arr));
                    if($min_ver<$version)
                    {
                        $this->error("该版本不支持该快捷入口");
                    }
                }
            }
        }
      
    }
    function check_version($version_arr_new,$extent_id='',$pid=1){
        $belong_page_type=trim($_POST['belong_page_type']);
        $cid = trim($_POST['cid']);
        $model=M('');
        // var_dump($version_arr_new);
        // if(count($version_arr_new)==1 && !$version_arr_new[0]){
        //     return;
        // }
        // var_dump($version_arr_new);
        foreach($version_arr_new as $vv){
            if($extent_id){
                if($_POST['type']==1){
                    $ret1 = $model->table('sj_textquickentry_extent')->where(array('pid'=>$pid,'cid'=>$cid,'status'=>1,'extent_id'=>array('neq',$extent_id),'version_type'=>1,'belong_page_type'=>$belong_page_type))->find();
                    $ret2 = $model->table('sj_textquickentry_extent')->where(array('pid'=>$pid,'cid'=>$cid,'status'=>1,'extent_id'=>array('neq',$extent_id),'version_type'=>2,'version_code'=>array('egt',$vv),'belong_page_type'=>$belong_page_type))->find();
                }else{
                    if($_POST['type']==3 && !$vv){
                        $ret1 = $model->table('sj_textquickentry_extent')->where(array('pid'=>$pid,'cid'=>$cid,'status'=>1,'extent_id'=>array('neq',$extent_id),'version_type'=>1,'belong_page_type'=>$belong_page_type))->find();
                        // echo $model->getlastsql();
                        $ret2 = $model->table('sj_textquickentry_extent')->where(array('pid'=>$pid,'cid'=>$cid,'status'=>1,'extent_id'=>array('neq',$extent_id),'version_type'=>2,'belong_page_type'=>$belong_page_type))->find();
                        // echo $model->getlastsql();
                    }else{
                        $ret1 = $model->table('sj_textquickentry_extent')->where(array('pid'=>$pid,'cid'=>$cid,'status'=>1,'extent_id'=>array('neq',$extent_id),'version_type'=>1,'version_code'=>array('elt',$vv),'belong_page_type'=>$belong_page_type))->find();
                        // echo $model->getlastsql();
                        $ret2 = $model->table('sj_textquickentry_extent')->where(array('pid'=>$pid,'cid'=>$cid,'status'=>1,'extent_id'=>array('neq',$extent_id),'version_type'=>2,'version_code'=>array('egt',$vv),'belong_page_type'=>$belong_page_type))->find();
                        // echo $model->getlastsql();
                    }
                    
                }
            }else{
                if($_POST['type']==1){
                    $ret1 = $model->table('sj_textquickentry_extent')->where(array('pid'=>$pid,'cid'=>$cid,'status'=>1,'version_type'=>1,'belong_page_type'=>$belong_page_type))->find();
                    $ret2 = $model->table('sj_textquickentry_extent')->where(array('pid'=>$pid,'cid'=>$cid,'status'=>1,'version_type'=>2,'version_code'=>array('egt',$vv),'belong_page_type'=>$belong_page_type))->find();
                }else{
                     if($_POST['type']==3 && !$vv){
                        $ret1 = $model->table('sj_textquickentry_extent')->where(array('pid'=>$pid,'cid'=>$cid,'status'=>1,'version_type'=>1,'belong_page_type'=>$belong_page_type))->find();
                        $ret2 = $model->table('sj_textquickentry_extent')->where(array('pid'=>$pid,'cid'=>$cid,'status'=>1,'version_type'=>2,'belong_page_type'=>$belong_page_type))->find();
                     }else{
                        $ret1 = $model->table('sj_textquickentry_extent')->where(array('pid'=>$pid,'cid'=>$cid,'status'=>1,'version_type'=>1,'version_code'=>array('elt',$vv),'belong_page_type'=>$belong_page_type))->find();
                        $ret2 = $model->table('sj_textquickentry_extent')->where(array('pid'=>$pid,'cid'=>$cid,'status'=>1,'version_type'=>2,'version_code'=>array('egt',$vv),'belong_page_type'=>$belong_page_type))->find();
                     }
                    
                }
            }
            // echo $model->getlastsql();
            if($ret1 || $ret2){
                $this->error("相同市场版本，只能有一个分区");
            }
            $where=array();
            if($extent_id){
                $where=array('extent_id'=>array('neq',$extent_id));
            }
            $where['version_type']=3;
            $where['belong_page_type']=$belong_page_type;
            $where['cid']=$cid;
            $where['status']=1;
            $where['pid']=$pid;
            $ret3 = $model->table('sj_textquickentry_extent')->where($where)->select();   
            // echo $model->getlastsql();
            
            $version_arr=array();
            foreach($ret3 as $k=>$v){
                $version_arr=array_merge($version_arr,explode(',', $v['version_code']));
            }
            array_filter($version_arr);
            // var_dump($version_arr);die;
            foreach($version_arr as $v){
                if($_POST['type']==3 && $v==$vv['version_code']){
                   $this->error("相同市场版本，只能有一个分区");
                }else if($_POST['type']==2 && $v<=$vv['version_code']){
                     $this->error("相同市场版本，只能有一个分区");
                }else if($_POST['type']==1 && $v>=$map['version_code']){
                    $this->error("相同市场版本，只能有一个分区");
                }
            }
        }
    }
    public function edit_extent() {
        if ($_POST) {
            $model = M();
            $extent_id = $_POST['extent_id'];
            $extent_name = trim($_POST['extent_name']);
            if (!$extent_name) {
                $this->error("区间名不能为空");
            }
            $cid = $_POST['cid'];
            if (!$cid)
                $cid = 0;
            $oid = $_POST['oid'];
            if (!$oid)
                $oid = 0;
            $pid = $_POST['pid'];
            if (!$pid) {
                $this->error("平台不能为空");
            }
            $belong_page_type = trim($_POST['belong_page_type']);
			//V6.0快捷入口添加入口数量
			$entrance_count=$_POST['entrance_count'];
			if($entrance_count==0)
			{
				$this->error("入口数量不能为空");
			}
            if (!$belong_page_type) {
                $this->error("所属页面不能为空");
            }
			//V6.2快捷入口添加样式选择
			$entrance_type=$_POST['entrance_type'];
			/*$where = array(
				'extent_id' => $extent_id,
				'status' => 1,
			);
			$have_result = $model->table('sj_textquickentry_extent_soft')->where($where)->select();*/
			if($entrance_type==0)
			{
				$this->error("请选择入口样式");
			}
			//V6.4编辑的时候样式不能修改了 所以不用判断了 2016-7-15
			/*else if($entrance_type==1)
			{
				foreach($have_result as $k => $v)
				{
					if($v['version_code'])
					{
						if($v['version_type']==1)
						{
							$this->error("该版本不支持该快捷入口");
						}
						else if($v['version_type']==2)
						{
							if($v['version_code']>6110)
							{
								$this->error("该版本不支持该快捷入口");
							}
						}
						else
						{
							$ver_arr = explode(",",$v['version_code']);
							$max_ver=max($ver_arr);
							if($max_ver>6110)
							{
								$this->error("该版本不支持该快捷入口");
							}
						}
					}
					else
					{
						$this->error("该区间下面的所有数据的市场版本没有填全，请编辑后再修改");
					}
				}
			}
			else if($entrance_type==2)
			{
				//如果选择6.2样式  要保证这个区间下面的所有数据都要有 6.2图片和文字说明
				foreach($have_result as $k => $v)
				{
					if(!$v['image_url_62']||!$v['words_des'])
					{
						$this->error("该区间下面的所有数据的6.2图片和文字说明两项没有填全，请编辑后再修改");
					}
					//6.4市场版本修改  必填
					if($v['version_code'])
					{
						if($v['version_type']==1)
						{
							if($v['version_code']<6200)
							{
								$this->error("该版本不支持该快捷入口");
							}
						}
						else if($v['version_type']==2)
						{
							$this->error("该版本不支持该快捷入口");
						}
						else
						{
							$ver_arr = explode(",",$v['version_code']);
							$min_ver=min($ver_arr);
							if($min_ver<6200)
							{
								$this->error("该版本不支持该快捷入口");
							}
						}
					}
					else
					{
						$this->error("该区间下面的所有数据的市场版本没有填全，请编辑后再修改");
					}
				}
			}
			else if($entrance_type==3) //V6.4添加6.4样式  必须添加6.4图片
			{
				//如果选择6.4样式  要保证这个区间下面的所有数据都要有 6.4图片
				foreach($have_result as $k => $v)
				{
					if(!$v['image_url_64'])
					{
						$this->error("该区间下面的所有数据的6.4图片没有填全，请编辑后再修改");
					}
					if($v['version_code'])
					{
						if($v['version_type']==1)
						{
							if($v['version_code']<6400)
							{
								$this->error("该版本不支持该快捷入口");
							}
						}
						else if($v['version_type']==2)
						{
							$this->error("该版本不支持该快捷入口");
						}
						else
						{
							$ver_arr = explode(",",$v['version_code']);
							$min_ver=min($ver_arr);
							if($min_ver<6400)
							{
								$this->error("该版本不支持该快捷入口");
							}
						}
					}
					else
					{
						$this->error("该区间下面的所有数据的市场版本没有填全，请编辑后再修改");
					}
				}
			}*/
            // 检查区间名是否在当前平台的当前页面已存在
            $where = array(
                'extent_name' => $extent_name,
                'pid' => $pid,
                'belong_page_type' => $belong_page_type,
                'status' => 1,
                'extent_id' => array('neq', $extent_id),
            );
            $find = $model->table('sj_textquickentry_extent')->where($where)->find();
            if ($find)
                $this->error("该页面已存在此区间名");
            // 检查相同pid，cid，oid，belong_page_type是否已存在区间
            // $where = array(
            //     'pid' => $pid,
            //     'cid' => $cid,
            //     'oid' => $oid,
            //     'belong_page_type' => $belong_page_type,
            //     'status' => 1,
            //     'extent_id' => array('neq', $extent_id),
            // );
            // $find = $model->table('sj_textquickentry_extent')->where($where)->find();
            // if ($find)
            //     $this->error("该页面已有快捷入口存在，请确认页面是否正确");
            // 添加
            $map = array();
            $map['extent_name'] = $extent_name;
            $map['cid'] = $cid;
            $map['oid'] = $oid;
            $map['create_time'] = time();
			$map['update_time'] = time();
			$map['entrance_count'] = $entrance_count;
			$map['entrance_type'] = $entrance_type;
			$map['admin_id'] = $_SESSION['admin']['admin_id'];
            $where = array('extent_id' => $extent_id);
            $this->get_version($map,$extent_id);
            $log = $this->logcheck($where, 'sj_textquickentry_extent', $map, $model);
            $ret = $model->table('sj_textquickentry_extent')->where($where)->save($map);
            if ($ret || $ret === 0) {
                if ($ret) {
                    $this->writelog("文字快捷入口：编辑了id为{$extent_id}的区间，{$log}",'sj_textquickentry_extent', $extent_id,__ACTION__ ,"","edit");
                }
                $this->success("编辑成功！");
            } else {
                $this->error("编辑失败！");
            }
            
        } else {
            $model = M();
            $extent_id = $_GET['extent_id'];
            $where = array(
                'extent_id' => $extent_id,
                'status' => 1,
            );
            $find = $model->table('sj_textquickentry_extent')->where($where)->find();
            // 所有运营商
            $operating_db = D('Sj.Operating');
            $operating_list = $operating_db->field('oid,mname')->select();
            $operators_key = array();
            foreach($operating_list as $v) {
                $operators_key[$v['oid']] = $v['mname'];
            }
            // 所有渠道
            $channel_model = M('channel');
            $channels = $channel_model->field("`cid`,`chname`")->where(array('status' => 1))->select();
            $channels_key = array();
            foreach($channels as $v) {
                $channels_key[$v['cid']] = $v['chname'];
            }
            if ($find) {
                $find['chname'] = $channels_key[$find['cid']];
            }
			//编辑展示样式
			$entrance_type = $find['entrance_type'];
			$this->assign('entrance_type', $entrance_type);
            $this->assign('list', $find);
			$this->assign('belong_page_type',$find['belong_page_type']);
            $this->assign('operating_list', $operating_list);
            $this->display();
        }
    }
    
    public function delete_extent() {
        $extent_id = $_GET['extent_id'];
        $model = M();
        $where = array(
            'extent_id' => $extent_id,
            'status' => 1,
        );
        $map = array('status' => 0, 'update_time' => time());
        $ret = $model->table('sj_textquickentry_extent')->where($where)->save($map);
        if ($ret) {
            // 删除该区间下的所有软件
            $where = array(
                'extent_id' => $extent_id,
                'status' => 1,
            );
            $softs = $model->table('sj_textquickentry_extent_soft')->where($where)->select();
            foreach ($softs as $soft) {
                $where = array('id' => $soft['id']);
                $map = array('status' => 0, 'update_at' => time());
                $model->table('sj_textquickentry_extent_soft')->where($where)->save($map);
            }
            $this->writelog("文字快捷入口：删除了id为{$extent_id}的区间，同时也删除该入口下的所有内容",'sj_textquickentry_extent', $extent_id,__ACTION__ ,"","del");
            $this->success("删除成功！");
        } else {
            $this->error("删除失败！");
        }
    }
    
    public function list_content() {
        $model = M();
        $extent_id = $_GET['extent_id'];
		$entrance_type = $_GET['entrance_type'];
        $color =    C('text_quick_color');
        $this->assign('color', $color);
        $where = array(
            'extent_id' => $extent_id,
            'status' => 1,
        );
        // 请求的是哪个时间段
        $period = $_GET['period'];
        if ($period != 1 && $period != 2 && $period != 3) {
            $period = 2;
        }
        $now = time();
        if ($period == 1) {
            // 已过期
            $where['end_at'] = array('lt', $now);
            //$order = "end_at desc";
			$order = "start_at asc";
        } else if ($period == 2) {
            // 正在排期中的
            $where['start_at'] = array('elt', $now);
            $where['end_at'] = array('egt', $now);
            $order = "rank asc,start_at asc";
        } else {
            // 还未排期中的
            $where['start_at'] = array('gt', $now);
            $order = "rank asc, start_at  asc";
        }
        // 翻页
        import("@.ORG.Page");
        $limit = 10;
        $count = $model->table('sj_textquickentry_extent_soft')->where($where)->count();
        $page  = new Page($count, $limit);
        $list = $model->table('sj_textquickentry_extent_soft')->where($where)->limit($page->firstRow . ',' . $page->listRows)->order($order)->select();
        // 处理list
        foreach ($list as $key => $value) {
            $content_type = $value['content_type'];
            if ($content_type == 2) {
                // 活动名称
                $list[$key]['activity_name'] = ContentTypeModel::convertActivityId2ActivityName($value['activity_id']);
                $list[$key]['lead_content'] = $list[$key]['activity_name'];
            } else if ($content_type == 3) {
                // 专题名称
                $list[$key]['feature_name'] = ContentTypeModel::convertFeatureId2FeatureName($value['feature_id']);
                $list[$key]['lead_content'] = $list[$key]['feature_name'];
            } else if ($content_type == 4) {
                // 页面名称
                $list[$key]['page_name'] = ContentTypeModel::convertPageType2PageName($value['page_type']);
                $list[$key]['lead_content'] = $list[$key]['page_name'];
            } else if ($content_type == 5) {
                $list[$key]['lead_content'] = $list[$key]['website'];
            }else if ($content_type == 6) {
                $list[$key]['lead_content'] = $list[$key]['gift_id'];
            }else if ($content_type == 7) {
                $list[$key]['lead_content'] = $list[$key]['strategy_id'];
            }
			//处理角标显示
			if(($value['show_corner']==1)&&($value['corner_end_tm']>time()))
			{
				$list[$key]['show_corners'] = "火";
			}
			elseif(($value['show_corner']==2)&&($value['corner_end_tm']>time()))
			{
				$list[$key]['show_corners'] = "热";
			}
			elseif($value['show_corner']==3)
			{
				$list[$key]['show_corners'] = "new";
			}
			if($value['is_show_red_point']==1)
			{
				$list[$key]['show_red_points'] = "是";
			}
			else
			{
				$list[$key]['show_red_points'] = "否";
			}
			//处理市场版本
			if($value['version_type']==3)
			{
				$list[$key]['version_code']=trim($value['version_code'], ',');
			}
			if($value['version_type']==1)
			{
				$list[$key]['version_code']="大于等于".$value['version_code'];
			}
			if($value['version_type']==2)
			{
				$list[$key]['version_code']="小于等于".$value['version_code'];
			}
        }
        // 判断extent_id在哪个页面中，方便导航栏数据
        $where = array(
            'extent_id' => $extent_id,
            'status' => 1,
        );
        $extent = $model->table('sj_textquickentry_extent')->where($where)->find();
        $belong_page_type = $extent['belong_page_type'];
        $belong_page_name = ContentTypeModel::convertPageType2PageNameOfTextQuickEntry($belong_page_type);
        $general_page_type = ContentTypeModel::getGeneralPageType($belong_page_type);
        $general_page_name = ContentTypeModel::convertGeneralPageType2GeneralPageName($general_page_type);
        $page_name = $extent['page_name'];
        $this->assign('general_page_type', $general_page_type);
        $this->assign('general_page_name', $general_page_name);
        $this->assign('belong_page_type', $belong_page_type);
        $this->assign('belong_page_name', $belong_page_name);
        $this->assign('extent_name', $extent['extent_name']);
        /////////////////////////////////////////////////////
        $this->assign('list', $list);
        $this->assign('page', $page->show());
        $this->assign('domain_url', ATTACHMENT_HOST);
        $this->assign('extent_id', $extent_id);
		$this->assign('entrance_type', $entrance_type);
        $this->assign('period', $period);
        $this->display();
    }
    
    public function add_content() {
        $color =    C('text_quick_color');
        $this->assign('color', $color);
        if ($_POST) {
            $map = array();
            $extent_id = $_POST['extent_id'];
            if (!$extent_id) {
                $this->error("区间id不能为空");
            }
            $map['extent_id'] = $extent_id;
            $title = $_POST['title'];
            if (!$title) {
                $this->error("标题不能为空");
            }
            // 根据extent_id获得该区间所在页面可以添加内容的标题最长长度
            $arr_info = $this->title_limit_length($extent_id);
			$entrance_type = $arr_info['entrance_type'];
			$entrance_count = $arr_info['entrance_count'];
			$title_limit_length = $arr_info['length'];
			$rank = $_POST['rank'];
			
            if (mb_strlen($title, 'utf-8') > $title_limit_length) {
                $this->error("标题长度不能大于{$title_limit_length}");
            }
			// V6.2图文样式新增文字说明和6.2图片 修改为必填 2015/12/16 入口为6必填，入口为7并且排序为1、2、3的也为必填其余为非必填
			if($entrance_type==2)
			{
				//if(($entrance_count ==6)||($entrance_count ==7&&($rank==1||$rank==2||$rank==3)))
				//{
					if(empty($_POST['words_des']))
					{
						$this->error("文字说明必填");
					}
				//}
			}
			//2016-1-21 6.0样式也可以传  
			if($_POST['words_des'])
			{
				if (mb_strlen(trim($_POST['words_des']), 'utf-8') > $this->words_des) 
				{
					$this->error("文字说明不能大于{$this->words_des}");
				}
				else
				{
					$map['words_des'] = trim($_POST['words_des']);
				}
			}
			
            $map['title'] = $title;
			//V6.4修改   6.0的之前样式的是图片（必填），6.0图片（必填），6.0gif图片（选填）;6.2样式 6.0图片（必填），6.2图片（必填）6.0gif（选填）6.2gif（选填）;6.4样式 6.4图片（必填）6.4gif（选填）;
			if($entrance_type==1)
			{
				if (!$_FILES['image_url']['name']) {
					$this->error("图片不能为空");
				}
			}
            if($entrance_type==1||$entrance_type==2)
			{
				if (!$_FILES['image_url_6']['name']) {
					$this->error("6.0图片不能为空");
				}	
				if($entrance_type==2)
				{
					if (!$_FILES['image_url_62']['name']) 
					{
						$this->error("6.2图片不能为空");
					}
				}
			}
			if($entrance_type==3||$entrance_type==4)
			{
				if (!$_FILES['image_url_64']['name']) {
                    $msg = '6.4图片不能为空';
                    if($entrance_type==4) $msg = '6.4.8图片不能为空';
					$this->error($msg);
				}	
			}
			// 将图片存储起来
			$folder = "/img/" . date("Ym/d/");
			$this->mkDirs(UPLOAD_PATH . $folder);
			if($_FILES['image_url']['name'])
			{
				// 取得图片后缀
				$suffix = preg_match("/\.(jpg|png)$/", $_FILES['image_url']['name'],$matches);
				if ($matches) {
					$suffix = $matches[0];
				} else {
					$this->error('上传图片格式错误！');
				}	
				 // 判断图片长和宽
				$img_info_arr = getimagesize($_FILES['image_url']['tmp_name']);
				if (!$img_info_arr) {
					$this->error('上传图片出错！');
				}
				$width = $img_info_arr[0];
				$height = $img_info_arr[1];
				if ($width != $this->image_width || $height != $this->image_height)
					$this->error("上传图片大小错误，宽需为{$this->image_width}px，高需为{$this->image_height}px");
				$relative_path = $folder . time() . '_' . rand(1000,9999) . $suffix;
				$img_path = UPLOAD_PATH . $relative_path;
				$ret = move_uploaded_file($_FILES['image_url']['tmp_name'], $img_path);
				$map['image_url'] = $relative_path;
			}
            if($_FILES['image_url_6']['name'])
			{
				//6.0图片不能为空
				$suffix_6 = preg_match("/\.(jpg|png)$/", $_FILES['image_url_6']['name'],$matches_6);
				if ($matches_6) {
					$suffix_6 = $matches_6[0];
				} else {
					$this->error('6.0上传图片格式错误！');
				}
				//6.0图片判断图片长和宽
				$img_info_arr_6 = getimagesize($_FILES['image_url_6']['tmp_name']);
				if (!$img_info_arr_6) {
					$this->error('上传图片出错！');
				}
				$width_6 = $img_info_arr_6[0];
				$height_6 = $img_info_arr_6[1];
				if ($width_6 != $this->image_width_6 || $height_6 != $this->image_height_6)
				$this->error("上传6.0图片大小错误，宽需为{$this->image_width_6}px，高需为{$this->image_height_6}px");
				$relative_path_6 = $folder . time() . '_' . rand(1000,9999) . $suffix_6;
				$img_path_6 = UPLOAD_PATH . $relative_path_6;
				$ret_6 = move_uploaded_file($_FILES['image_url_6']['tmp_name'], $img_path_6);
				$map['image_url_6'] = $relative_path_6;
			}
	
			//V6.2图片样式 新增图片
			if($_FILES['image_url_62']['name'])
			{
				//图片格式
				$suffix_62 = preg_match("/\.(jpg|png)$/", $_FILES['image_url_62']['name'],$matches_62);
				if ($matches_62) {
					$suffix_62 = $matches_62[0];
				} else {
					$this->error('6.2上传图片格式错误！');
				}
				//6.2图片判断图片长和宽
				$img_info_arr_62 = getimagesize($_FILES['image_url_62']['tmp_name']);
				if (!$img_info_arr_62) 
				{
					$this->error('上传图片出错！');
				}
				$width_62 = $img_info_arr_62[0];
				$height_62 = $img_info_arr_62[1];
				if ($width_62 != $this->image_width_62 || $height_62 != $this->image_height_62)
				$this->error("上传6.2图片大小错误，宽需为{$this->image_width_62}px，高需为{$this->image_height_62}px");
				$relative_path_62 = $folder . time() . '_' . rand(1000,9999) . $suffix_62;
				$img_path_62 = UPLOAD_PATH . $relative_path_62;
				$ret_62 = move_uploaded_file($_FILES['image_url_62']['tmp_name'], $img_path_62);
				$map['image_url_62'] = $relative_path_62;
			}
			//V6.3GIF图片上传保存
			if($_FILES['gif_image']['name'])
			{
				//图片格式
				$suffix_gif = preg_match("/\.(gif)$/", $_FILES['gif_image']['name'],$matches_gif);
				if ($matches_gif) {
					$suffix_gif = $matches_gif[0];
				} else {
					$this->error('gif上传图片格式错误！');
				}
				//gif图片判断图片长和宽
				$img_info_arr_gif = getimagesize($_FILES['gif_image']['tmp_name']);
				if (!$img_info_arr_gif) 
				{
					$this->error('上传图片出错！');
				}
				$width_gif = $img_info_arr_gif[0];
				$height_gif = $img_info_arr_gif[1];
				if ($width_gif != $this->gif_width || $height_gif != $this->gif_height)
				$this->error("上传gif图片大小错误，宽需为{$this->gif_width}px，高需为{$this->gif_height}px");
				$relative_path_gif = $folder . time() . '_' . rand(1000,9999) . $suffix_gif;
				$img_path_gif = UPLOAD_PATH . $relative_path_gif;
				$ret_62 = move_uploaded_file($_FILES['gif_image']['tmp_name'], $img_path_gif);
				$map['gif_image_url'] = $relative_path_gif;
			}
			if($_FILES['gif_image_62']['name'])
			{
				//图片格式
				$suffix_gif_62 = preg_match("/\.(gif)$/", $_FILES['gif_image_62']['name'],$matches_gif_62);
				if ($matches_gif_62) {
					$suffix_gif_62 = $matches_gif_62[0];
				} else {
					$this->error('6.2gif上传图片格式错误！');
				}
				//gif图片判断图片长和宽
				$img_info_arr_gif_62 = getimagesize($_FILES['gif_image_62']['tmp_name']);
				if (!$img_info_arr_gif_62) 
				{
					$this->error('上传图片出错！');
				}
				$width_gif_62 = $img_info_arr_gif_62[0];
				$height_gif_62 = $img_info_arr_gif_62[1];
				if ($width_gif_62 != $this->gif_width_62 || $height_gif_62 != $this->gif_height_62)
				$this->error("上传6.2gif图片大小错误，宽需为{$this->gif_width_62}px，高需为{$this->gif_height_62}px");
				$relative_path_gif_62 = $folder . time() . '_' . rand(1000,9999) . $suffix_gif_62;
				$img_path_gif_62 = UPLOAD_PATH . $relative_path_gif_62;
				$ret_62 = move_uploaded_file($_FILES['gif_image_62']['tmp_name'], $img_path_gif_62);
				$map['gif_image_url_62'] = $relative_path_gif_62;
			}
            //V6.4新增加图片和gif图片
			if($_FILES['image_url_64']['name'])
			{
				//图片格式
				$suffix_64 = preg_match("/\.(jpg|png)$/", $_FILES['image_url_64']['name'],$matches_64);
				if ($matches_64) {
					$suffix_64 = $matches_64[0];
				} else {
					$this->error('6.4上传图片格式错误！');
				}
				//6.4图片判断图片长和宽
				$img_info_arr_64 = getimagesize($_FILES['image_url_64']['tmp_name']);
				if (!$img_info_arr_64) 
				{
					$this->error('上传图片出错！');
				}
				$width_64 = $img_info_arr_64[0];
				$height_64 = $img_info_arr_64[1];
				if ($width_64 != $this->image_width_64 || $height_64 != $this->image_height_64)
				$this->error("上传6.4图片大小错误，宽需为{$this->image_width_64}px，高需为{$this->image_height_64}px");
				$relative_path_64 = $folder . time() . '_' . rand(1000,9999) . $suffix_64;
				$img_path_64 = UPLOAD_PATH . $relative_path_64;
				$ret_64 = move_uploaded_file($_FILES['image_url_64']['tmp_name'], $img_path_64);
				$map['image_url_64'] = $relative_path_64;
			}
			if($_FILES['gif_image_64']['name'])
			{
				//图片格式
				$suffix_gif_64 = preg_match("/\.(gif)$/", $_FILES['gif_image_64']['name'],$matches_gif_64);
				if ($matches_gif_64) {
					$suffix_gif_64 = $matches_gif_64[0];
				} else {
					$this->error('6.4gif上传图片格式错误！');
				}
				//gif图片判断图片长和宽
				$img_info_arr_gif_64 = getimagesize($_FILES['gif_image_64']['tmp_name']);
				if (!$img_info_arr_gif_64) 
				{
					$this->error('上传图片出错！');
				}
				$width_gif_64 = $img_info_arr_gif_64[0];
				$height_gif_64 = $img_info_arr_gif_64[1];
				if ($width_gif_64!= $this->gif_width_64 || $height_gif_64 != $this->gif_height_64)
				$this->error("上传6.4gif图片大小错误，宽需为{$this->gif_width_64}px，高需为{$this->gif_height_64}px");
				$relative_path_gif_64 = $folder . time() . '_' . rand(1000,9999) . $suffix_gif_64;
				$img_path_gif_64 = UPLOAD_PATH . $relative_path_gif_64;
				$ret_64 = move_uploaded_file($_FILES['gif_image_64']['tmp_name'], $img_path_gif_64);
				$map['gif_image_url_64'] = $relative_path_gif_64;
			}
			
			if(!$_POST['bg_color'])
			{
				$this->error("请选择一个背景颜色");
			}
			else
			{
				$map['bg_color'] = $_POST['bg_color'];
			}
			
            $content_type = $_POST['content_type'];
            if (!$content_type) {
                $this->error("推荐类型不能为空");
            }
            //推荐内容处理 合并 文字快捷入口没有软件
			$rcontent=ContentTypeModel::saveRecommendContent($_POST,'',$map);
			if($rcontent!==true)
			{
				$this -> error($rcontent);
			}
            // 开始和结束时间
            $start_at = strtotime($_POST['start_at']);
            $end_at = strtotime($_POST['end_at']);
            if (!$start_at) {
                $this->error("开始时间不能为空");
            }
            if (!$end_at) {
                $this->error("结束时间不能为空");
            }
            if ($start_at > $end_at) {
                $this->error("开始时间不能大于结束时间");
            }
            $map['start_at'] = $start_at;
            $map['end_at'] = $end_at;
			//显示角标
			if($_POST['show_corner'])
			{
				$map['show_corner'] = $_POST['show_corner'];
				if($_POST['show_corner']==1||$_POST['show_corner']==2)
				{
					$corner_start_tm = strtotime($_POST['corner_start_at']);
					$corner_end_tm = strtotime($_POST['corner_end_at']);
					if (!$corner_start_tm) {
						$this->error("角标开始时间不能为空");
					}
					if (!$corner_end_tm) {
						$this->error("角标结束时间不能为空");
					}
					if ($corner_start_tm > $corner_end_tm) {
						$this->error("角标开始时间不能大于角标结束时间");
					}
					$map['corner_start_tm'] = $corner_start_tm;
					$map['corner_end_tm'] = $corner_end_tm;
				}
			}
			if($_POST['show_red_point'])
			{
				$map['is_show_red_point'] = $_POST['show_red_point'];
			}
			//市场版本选择 
			$map['version_type'] = $_POST['type'];
			if($_POST['type']==1)
			{
				$map['version_code'] = $_POST['version_code1'];
			}
			if($_POST['type']==2)
			{
				$map['version_code'] = $_POST['version_code2'];
			}
			if($_POST['type']==3)
			{
				$map['version_code'] = $_POST['force_update_version'];
			}
			//6.4市场版本修改  必填(2016-8-4非必填)
			if($map['version_code'])
			{
				//6.0样式不限制版本号
				/*if($entrance_type==1)
				{
					if($_POST['type']==1)
					{
						$this->error("该版本不支持该快捷入口");
					}
					else if($_POST['type']==2)
					{
						if($map['version_code']>6110)
						{
							$this->error("该版本不支持该快捷入口");
						}
					}
					else
					{
						$ver_arr = explode(",",$map['version_code']);
						$max_ver=max($ver_arr);
						if($max_ver>6110)
						{
							$this->error("该版本不支持该快捷入口");
						}
					}
				}*/
				if($entrance_type==2)
				{
					if($_POST['type']==1)
					{
						if($map['version_code']<6200)
						{
							$this->error("该版本不支持该快捷入口");
						}
					}
					else if($_POST['type']==2)
					{
						$this->error("该版本不支持该快捷入口");
					}
					else
					{
						$ver_arr = explode(",",$map['version_code']);
						$min_ver=min(array_filter($ver_arr));
						if($min_ver<6200)
						{
							$this->error("该版本不支持该快捷入口");
						}
					}
				}
				else if($entrance_type==3||$entrance_type==4)
				{
                    $version = 6400;
                    if($entrance_type==4) $version = 6480;
					if($_POST['type']==1)
					{
						if($map['version_code']<$version)
						{
							$this->error("该版本不支持该快捷入口");
						}
					}
					else if($_POST['type']==2)
					{
						$this->error("该版本不支持该快捷入口");
					}
					else
					{
						$ver_arr = explode(",",$map['version_code']);
						$min_ver=min(array_filter($ver_arr));
						if($min_ver<$version)
						{
							$this->error("该版本不支持该快捷入口");
						}
					}
				}
			}
			/*else
			{
				$this->error("市场版本必填");
			}*/

            $this->deal_cover_user($map);
            // 其他
            $now = time();
            $map['create_at'] = $now;
            $map['update_at'] = $now;
            $map['status'] = 1;
			$map['admin_id'] = $_SESSION['admin']['admin_id'];
            // 检查冲突
            $conflict_id = $this->check_conflict($map);
            if ($conflict_id) {
                $this->error("排期与id为{$conflict_id}的记录相冲突");
            }
            // 检查排序冲突
            if (!ctype_digit($rank) || $rank == 0) {
                $this->error("排序请输入正整数");
            }
            $map['rank'] = $rank;
            $rank_conflict_id = $this->check_rank_conflict($map);
            if ($rank_conflict_id) {
                $this->error("排序与该区间里id为{$rank_conflict_id}的记录相冲突");
            }
            // 添加
            $model = M();
            $ret = $model->table('sj_textquickentry_extent_soft')->add($map);
            if ($ret) {
                $this->writelog("文字快捷入口：添加了id为{$ret}的内容",'sj_textquickentry_extent_soft',$ret,__ACTION__ ,"","add");
                $this->success("添加成功");
            } else {
                $this->error("添加失败");
            }
        } else {
            $this->assign('extent_id', $_GET['extent_id']);
            $this->assign('image_width', $this->image_width);
            $this->assign('image_height', $this->image_height);
			$this->assign('image_width_6', $this->image_width_6);
            $this->assign('image_height_6', $this->image_height_6);
			$this->assign('image_width_62', $this->image_width_62);
            $this->assign('image_height_62', $this->image_height_62);
			$this->assign('gif_width', $this->gif_width);
            $this->assign('gif_height', $this->gif_height);
			$this->assign('gif_width_62', $this->gif_width_62);
            $this->assign('gif_height_62', $this->gif_height_62);
			//V6.4添加图片和gif图片
			$this->assign('image_width_64', $this->image_width_64);
            $this->assign('image_height_64', $this->image_height_64);
			$this->assign('gif_width_64', $this->gif_width_64);
            $this->assign('gif_height_64', $this->gif_height_64);
			
			$arr_info = $this->title_limit_length($_GET['extent_id']);
			$entrance_type = $arr_info['entrance_type'];
			$entrance_count = $arr_info['entrance_count'];
			$title_limit_length = $arr_info['length'];
            //$title_limit_length = $this->title_limit_length($_GET['extent_id']);
            $this->assign('title_limit_length', $title_limit_length);
			$this->assign('entrance_count', $entrance_count);
			$this->assign('entrance_type', $entrance_type);
			$this->assign('words_des', $this->words_des);
            $this->display();
        }
    }
    
    public function edit_content() {
        $color =    C('text_quick_color');
        $this->assign('color', $color);
        if ($_POST) {
            $model = M();
            $map = array();
            $id = $_POST['id'];
            $title = $_POST['title'];
            if (!$title) {
                $this->error("标题不能为空");
            }
            // 根据extent_id获得该区间所在页面可以添加内容的标题最长长度
            $find = $model->table('sj_textquickentry_extent_soft')->where(array('id'=>$id))->find();
            $extent_id = $find['extent_id'];
            $content_type = $find['content_type'];
			$arr_info = $this->title_limit_length($extent_id);
			$title_limit_length = $arr_info['length'];
			$entrance_count = $arr_info['entrance_count'];
			$entrance_type = $arr_info['entrance_type'];
			$rank = $_POST['rank'];
			
            //$title_limit_length = $this->title_limit_length($extent_id);
            if (mb_strlen($title, 'utf-8') > $title_limit_length) {
                $this->error("标题长度不能大于{$title_limit_length}");
            }
			if($entrance_type==2)
			{
				//文字说明 修改为必填
				//if(($entrance_count ==6)||($entrance_count ==7&&($rank==1||$rank==2||$rank==3)))
				//{
					if(empty($_POST['words_des']))
					{
						$this->error("文字说明必填");
					}
				//}
			}
			if($_POST['words_des'])
			{
				if (mb_strlen(trim($_POST['words_des']), 'utf-8') > $this->words_des) 
				{
					$this->error("文字说明不能大于{$this->words_des}");
				}
			}
			$map['words_des'] = trim($_POST['words_des']);
			
			
            $map['title'] = $title;
			$map['extent_id']=$extent_id;
			
			// 将图片存储起来
            $folder = "/img/" . date("Ym/d/");
            $this->mkDirs(UPLOAD_PATH . $folder);
			
			//V6.4修改   6.0的之前样式的是图片（必填），6.0图片（必填），6.0gif图片（选填）;6.2样式 6.0图片（必填），6.2图片（必填）6.0gif（选填）6.2gif（选填）;6.4样式 6.4图片（必填）6.4gif（选填）;
			if($entrance_type==1)
			{
				if (!$_FILES['image_url']['name']&&$find['image_url']=='') {
					$this->error("图片不能为空");
				}
			}
            if($entrance_type==1||$entrance_type==2)
			{
				if (!$_FILES['image_url_6']['name']&&$find['image_url_6']=='') {
					$this->error("6.0图片不能为空");
				}	
				if($entrance_type==2)
				{
					if (!$_FILES['image_url_62']['name']&&$find['image_url_62']=='') 
					{
						$this->error("6.2图片不能为空");
					}
				}
			}
			if($entrance_type==3||$entrance_type==4)
			{
				if (!$_FILES['image_url_64']['name']&&$find['image_url_64']=='') {
                    $msg = '6.4图片不能为空';
                    if($entrance_type==4) $msg = '6.4.8图片不能为空';
					$this->error($msg);
				}	
			}
			
            if ($_FILES['image_url']['name']) 
			{
                // 取得图片后缀
                $suffix = preg_match("/\.(jpg|png)$/", $_FILES['image_url']['name'],$matches);
                if ($matches) {
                    $suffix = $matches[0];
                } else {
                    $this->error('上传图片格式错误！');
                }
                // 判断图片长和宽
                $img_info_arr = getimagesize($_FILES['image_url']['tmp_name']);
                if (!$img_info_arr) {
                    $this->error('上传图片出错！');
                }
                $width = $img_info_arr[0];
                $height = $img_info_arr[1];
                if ($width != $this->image_width || $height != $this->image_height)
                    $this->error("上传图片大小错误，宽需为{$this->image_width}px，高需为{$this->image_height}px");
                $relative_path = $folder . time() . '_' . rand(1000,9999) . $suffix;
                $img_path = UPLOAD_PATH . $relative_path;
                $ret = move_uploaded_file($_FILES['image_url']['tmp_name'], $img_path);
                $map['image_url'] = $relative_path;
            }
			if ($_FILES['image_url_6']['name']) 
			{
                // 取得图片后缀
                $suffix = preg_match("/\.(jpg|png)$/", $_FILES['image_url_6']['name'],$matches_6);
                if ($matches_6) {
                    $suffix = $matches_6[0];
                } else {
                    $this->error('上传6.0图片格式错误！');
                }
                // 判断图片长和宽
                $img_info_arr_6 = getimagesize($_FILES['image_url_6']['tmp_name']);
                if (!$img_info_arr_6) {
                    $this->error('上传6.0图片出错！');
                }
                $width_6 = $img_info_arr_6[0];
                $height_6 = $img_info_arr_6[1];
                if ($width_6 != $this->image_width_6 || $height_6 != $this->image_height_6)
                    $this->error("上传6.0图片大小错误，宽需为{$this->image_width_6}px，高需为{$this->image_height_6}px");
                $relative_path_6 = $folder . time() . '_' . rand(1000,9999) . $suffix;
                $img_path_6 = UPLOAD_PATH . $relative_path_6;
                $ret_6 = move_uploaded_file($_FILES['image_url_6']['tmp_name'], $img_path_6);
                $map['image_url_6'] = $relative_path_6;
            }
			
			if ($_FILES['image_url_62']['name']) 
			{
				// 取得图片后缀
				$suffix = preg_match("/\.(jpg|png)$/", $_FILES['image_url_62']['name'],$matches_62);
				if ($matches_62) {
					$suffix = $matches_62[0];
				} else {
					$this->error('上传6.2图片格式错误！');
				}
				// 判断图片长和宽
				$img_info_arr_62 = getimagesize($_FILES['image_url_62']['tmp_name']);
				if (!$img_info_arr_62) {
					$this->error('上传6.2图片出错！');
				}
				$width_62 = $img_info_arr_62[0];
				$height_62 = $img_info_arr_62[1];
				if ($width_62 != $this->image_width_62 || $height_62 != $this->image_height_62)
					$this->error("上传6.2图片大小错误，宽需为{$this->image_width_62}px，高需为{$this->image_height_62}px");
				$relative_path_62 = $folder . time() . '_' . rand(1000,9999) . $suffix;
				$img_path_62 = UPLOAD_PATH . $relative_path_62;
				$ret_62 = move_uploaded_file($_FILES['image_url_62']['tmp_name'], $img_path_62);
				$map['image_url_62'] = $relative_path_62;
			}
			//V6.3GIF图片上传保存
			if($_FILES['gif_image']['name'])
			{
				//图片格式
				$suffix_gif = preg_match("/\.(gif)$/", $_FILES['gif_image']['name'],$matches_gif);
				if ($matches_gif) {
					$suffix_gif = $matches_gif[0];
				} else {
					$this->error('gif上传图片格式错误！');
				}
				//gif图片判断图片长和宽
				$img_info_arr_gif = getimagesize($_FILES['gif_image']['tmp_name']);
				if (!$img_info_arr_gif) 
				{
					$this->error('上传图片出错！');
				}
				$width_gif = $img_info_arr_gif[0];
				$height_gif = $img_info_arr_gif[1];
				if ($width_gif != $this->gif_width || $height_gif != $this->gif_height)
				$this->error("上传gif图片大小错误，宽需为{$this->gif_width}px，高需为{$this->gif_height}px");
				$relative_path_gif = $folder . time() . '_' . rand(1000,9999) . $suffix_gif;
				$img_path_gif = UPLOAD_PATH . $relative_path_gif;
				$ret_62 = move_uploaded_file($_FILES['gif_image']['tmp_name'], $img_path_gif);
				$map['gif_image_url'] = $relative_path_gif;
			}
			if($_FILES['gif_image_62']['name'])
			{
				//图片格式
				$suffix_gif_62 = preg_match("/\.(gif)$/", $_FILES['gif_image_62']['name'],$matches_gif_62);
				if ($matches_gif_62) {
					$suffix_gif_62 = $matches_gif_62[0];
				} else {
					$this->error('6.2gif上传图片格式错误！');
				}
				//gif图片判断图片长和宽
				$img_info_arr_gif_62 = getimagesize($_FILES['gif_image_62']['tmp_name']);
				if (!$img_info_arr_gif_62) 
				{
					$this->error('上传图片出错！');
				}
				$width_gif_62 = $img_info_arr_gif_62[0];
				$height_gif_62 = $img_info_arr_gif_62[1];
				if ($width_gif_62 != $this->gif_width_62 || $height_gif_62 != $this->gif_height_62)
				$this->error("上传6.2gif图片大小错误，宽需为{$this->gif_width_62}px，高需为{$this->gif_height_62}px");
				$relative_path_gif_62 = $folder . time() . '_' . rand(1000,9999) . $suffix_gif_62;
				$img_path_gif_62 = UPLOAD_PATH . $relative_path_gif_62;
				$ret_62 = move_uploaded_file($_FILES['gif_image_62']['tmp_name'], $img_path_gif_62);
				$map['gif_image_url_62'] = $relative_path_gif_62;
			}
			//V6.4新增加图片和gif图片
			if($_FILES['image_url_64']['name'])
			{
				//图片格式
				$suffix_64 = preg_match("/\.(jpg|png)$/", $_FILES['image_url_64']['name'],$matches_64);
				if ($matches_64) {
					$suffix_64 = $matches_64[0];
				} else {
					$this->error('6.4上传图片格式错误！');
				}
				//6.4图片判断图片长和宽
				$img_info_arr_64 = getimagesize($_FILES['image_url_64']['tmp_name']);
				if (!$img_info_arr_64) 
				{
					$this->error('上传图片出错！');
				}
				$width_64 = $img_info_arr_64[0];
				$height_64 = $img_info_arr_64[1];
				if ($width_64 != $this->image_width_64 || $height_64 != $this->image_height_64)
				$this->error("上传6.4图片大小错误，宽需为{$this->image_width_64}px，高需为{$this->image_height_64}px");
				$relative_path_64 = $folder . time() . '_' . rand(1000,9999) . $suffix_64;
				$img_path_64 = UPLOAD_PATH . $relative_path_64;
				$ret_64 = move_uploaded_file($_FILES['image_url_64']['tmp_name'], $img_path_64);
				$map['image_url_64'] = $relative_path_64;
			}
			if($_FILES['gif_image_64']['name'])
			{
				//图片格式
				$suffix_gif_64 = preg_match("/\.(gif)$/", $_FILES['gif_image_64']['name'],$matches_gif_64);
				if ($matches_gif_64) {
					$suffix_gif_64 = $matches_gif_64[0];
				} else {
					$this->error('6.4gif上传图片格式错误！');
				}
				//gif图片判断图片长和宽
				$img_info_arr_gif_64 = getimagesize($_FILES['gif_image_64']['tmp_name']);
				if (!$img_info_arr_gif_64) 
				{
					$this->error('上传图片出错！');
				}
				$width_gif_64 = $img_info_arr_gif_64[0];
				$height_gif_64 = $img_info_arr_gif_64[1];
				if ($width_gif_64!= $this->gif_width_64 || $height_gif_64 != $this->gif_height_64)
				$this->error("上传6.4gif图片大小错误，宽需为{$this->gif_width_64}px，高需为{$this->gif_height_64}px");
				$relative_path_gif_64 = $folder . time() . '_' . rand(1000,9999) . $suffix_gif_64;
				$img_path_gif_64 = UPLOAD_PATH . $relative_path_gif_64;
				$ret_64 = move_uploaded_file($_FILES['gif_image_64']['tmp_name'], $img_path_gif_64);
				$map['gif_image_url_64'] = $relative_path_gif_64;
			}
			
			if($_POST['bg_color'])
			{
				$map['bg_color']=trim($_POST['bg_color']);
			}
            // content_type不允许编辑
            // $content_type = $_POST['content_type'];
            if (!$content_type) {
                $this->error("推荐类型不能为空");
            }
            $map['content_type'] = $content_type;
            // 先清除一下内容字段数据
            $this->clear_content($map);
            // 不同内容存不同字段
			//推荐内容处理 合并 文字快捷入口没有软件
			$rcontent=ContentTypeModel::saveRecommendContent($_POST,$content_type, $map);
			if($rcontent!==true)
			{
				$this -> error($rcontent);
			}
            // 开始和结束时间
            $start_at = strtotime($_POST['start_at']);
            $end_at = strtotime($_POST['end_at']);
            if (!$start_at) {
                $this->error("开始时间不能为空");
            }
            if (!$end_at) {
                $this->error("结束时间不能为空");
            }
            if ($start_at > $end_at) {
                $this->error("开始时间不能大于结束时间");
            }
			//已过期的信息复制上线判断
			if($_POST['life']==1)
			{
			  if($end_at<time())
			  {
				$this->error("您修改的复制上线的日期还是无效日期");
			  }
			}

            $map['start_at'] = $start_at;
            $map['end_at'] = $end_at;
			//显示角标
			if($_POST['show_corner'])
			{
				$map['show_corner'] = $_POST['show_corner'];
				if($_POST['show_corner']==1||$_POST['show_corner']==2)
				{
					$corner_start_tm = strtotime($_POST['corner_start_at']);
					$corner_end_tm = strtotime($_POST['corner_end_at']);
					if (!$corner_start_tm) {
						$this->error("角标开始时间不能为空");
					}
					if (!$corner_end_tm) {
						$this->error("角标结束时间不能为空");
					}
					if ($corner_start_tm > $corner_end_tm) {
						$this->error("角标开始时间不能大于角标结束时间");
					}
					$map['corner_start_tm'] = $corner_start_tm;
					$map['corner_end_tm'] = $corner_end_tm;
				}
				else
				{
					$map['corner_start_tm'] = 0;
					$map['corner_end_tm'] = 0;
				}
			}
			if($_POST['show_red_point'])
			{
				$map['is_show_red_point'] = $_POST['show_red_point'];
			}
			else
			{
				$map['is_show_red_point'] = 0;
			}
			//V6.2市场版本控制
			$map['version_type'] = $_POST['type'];
			if($_POST['type']==1)
			{
				$map['version_code'] = $_POST['version_code1'];
			}
			if($_POST['type']==2)
			{
				$map['version_code'] = $_POST['version_code2'];
			}
			if($_POST['type']==3)
			{
				$map['version_code'] = $_POST['force_update_version'];
			}
			//6.4市场版本修改  必填(2016-8-4非必填)
			if($map['version_code'])
			{
				/*if($entrance_type==1)
				{
					if($_POST['type']==1)
					{
						$this->error("该版本不支持该快捷入口");
					}
					else if($_POST['type']==2)
					{
						if($map['version_code']>6110)
						{
							$this->error("该版本不支持该快捷入口");
						}
					}
					else
					{
						$ver_arr = explode(",",$map['version_code']);
						$max_ver=max($ver_arr);
						if($max_ver>6110)
						{
							$this->error("该版本不支持该快捷入口");
						}
					}
				}*/
				if($entrance_type==2)
				{
					if($_POST['type']==1)
					{
						if($map['version_code']<6200)
						{
							$this->error("该版本不支持该快捷入口");
						}
					}
					else if($_POST['type']==2)
					{
						$this->error("该版本不支持该快捷入口");
					}
					else
					{
						$ver_arr = explode(",",$map['version_code']);
						$min_ver=min(array_filter($ver_arr));
						if($min_ver<6200)
						{
							$this->error("该版本不支持该快捷入口");
						}
					}
				}
				else if($entrance_type==3||$entrance_type==4)
				{
                    $version = 6400;
                    if($entrance_type == 4) $version = 6480;
					if($_POST['type']==1)
					{
						if($map['version_code']<$version)
						{
							$this->error("该版本不支持该快捷入口");
						}
					}
					else if($_POST['type']==2)
					{
						$this->error("该版本不支持该快捷入口");
					}
					else
					{
						$ver_arr = explode(",",$map['version_code']);
						$min_ver=min(array_filter($ver_arr));
						if($min_ver<$version)
						{
							$this->error("该版本不支持该快捷入口");
						}
					}
				}
			}
			/*else
			{
				$this->error("市场版本必填");
			}*/
			$this->deal_cover_user($map);
            // 其他
            $now = time();
            $map['update_at'] = $now;
            $map['status'] = 1;
			$map['life']=$_POST['life'];
			$map['admin_id'] = $_SESSION['admin']['admin_id'];
            // 检查冲突
            $conflict_id = $this->check_conflict($map, $id);
            if ($conflict_id) {
                $this->error("排期与id为{$conflict_id}的记录相冲突");
            }
            // 检查排序冲突
            if (!ctype_digit($rank) || $rank == 0) {
                $this->error("排序请输入正整数");
            }
            $map['rank'] = $rank;
            $rank_conflict_id = $this->check_rank_conflict($map, $id);
            if ($rank_conflict_id) {
                $this->error("排序与该区间里id为{$rank_conflict_id}的记录相冲突");
            }
            // 编辑
            $where = array('id' => $id);
            $log = $this->logcheck($where, 'sj_textquickentry_extent_soft', $map, $model);
			//已过期的信息复制上线 添加
			if($_POST['life']==1)
			{
			   $select = $model->table('sj_textquickentry_extent_soft')->where($where)->select();
			   $map['extent_id']=$select[0]['extent_id'];
			   if($_FILES['image_url']['name']=="")
			   {
					$map['image_url']=$select[0]['image_url'];
			   }
			   if($_FILES['image_url_6']['name']=="")
			   {
			    	$map['image_url_6']=$select[0]['image_url_6'];
			   }
			   if($_FILES['image_url_62']['name']=="")
			   {
			    	$map['image_url_62']=$select[0]['image_url_62'];
			   }
			   if($_FILES['gif_image']['name']=="")
			   {
			    	$map['gif_image_url']=$select[0]['gif_image_url'];
			   }
			    if($_FILES['gif_image_62']['name']=="")
			   {
			    	$map['gif_image_url_62']=$select[0]['gif_image_url_62'];
			   }
			   if($_FILES['image_url_64']['name']=="")
			   {
			    	$map['image_url_64']=$select[0]['image_url_64'];
			   }
			    if($_FILES['gif_image_64']['name']=="")
			   {
			    	$map['gif_image_url_64']=$select[0]['gif_image_64'];
			   }
			   $map['create_at']=time();
			   unset($map['life']);
			    $ret = $model->table('sj_textquickentry_extent_soft')->add($map);
				if ($ret) {
					$this->writelog("文字快捷入口：复制上线了id为{$id}的内容，{$log}",'sj_textquickentry_extent_soft',$ret,__ACTION__ ,"","add");
					$this->success("复制上线成功");
				} else {
					$this->error("复制上线失败");
				}
			}
			else
			{
			    unset($map['life']);
				$ret = $model->table('sj_textquickentry_extent_soft')->where($where)->save($map);
				if ($ret) {
					$this->writelog("文字快捷入口：编辑了id为{$id}的内容，{$log}",'sj_textquickentry_extent_soft',$id,__ACTION__ ,"","edit");
					$this->success("编辑成功");
				} else {
					$this->error("编辑失败");
				}
			}
        } else {
            $model = M();
            $id = $_GET['id'];
            $where = array(
                'id' => $id,
                'status' => 1,
            );
            $find = $model->table('sj_textquickentry_extent_soft')->where($where)->find();
            if(!empty($find['parameter_field'])){
                $json_arr = json_decode($find['parameter_field'],true);
                $find['cover_user_type'] = $json_arr['cover_user_type'];
                $find['activation_date_start'] = !empty($json_arr['activation_date_start'])?$json_arr['activation_date_start']:'';
                $find['activation_date_end'] = !empty($json_arr['activation_date_end'])?$json_arr['activation_date_end']:'';
            }else{
                $find['activation_date_start'] = '';
                $find['activation_date_end'] = '';
            }
            // 获得当前内容标题最长可以几个字
            //$title_limit_length = $this->title_limit_length($find['extent_id']);
			$arr_info = $this->title_limit_length($find['extent_id']);
			$title_limit_length = $arr_info['length'];
			$entrance_count = $arr_info['entrance_count'];
			$entrance_type = $arr_info['entrance_type'];
            $this->assign('list', $find);
            $this->assign('image_width', $this->image_width);
            $this->assign('image_height', $this->image_height);
			$this->assign('image_width_6', $this->image_width_6);
            $this->assign('image_height_6', $this->image_height_6);
			$this->assign('image_width_62', $this->image_width_62);
            $this->assign('image_height_62', $this->image_height_62);
			$this->assign('gif_width', $this->gif_width);
            $this->assign('gif_height', $this->gif_height);
			$this->assign('gif_width_62', $this->gif_width_62);
            $this->assign('gif_height_62', $this->gif_height_62);
			//V6.4添加图片和gif图片
			$this->assign('image_width_64', $this->image_width_64);
            $this->assign('image_height_64', $this->image_height_64);
			$this->assign('gif_width_64', $this->gif_width_64);
            $this->assign('gif_height_64', $this->gif_height_64);
            $this->assign('title_limit_length', $title_limit_length);
			$this->assign('entrance_count', $entrance_count);
			$this->assign('entrance_type', $entrance_type);
			$this->assign('words_des', $this->words_des);
            $this->display();
        }
    }
    
    function delete_content() {
        $model = M();
        $id = $_GET['id'];
        $where = array(
            'id' => $id,
            'status' => 1,
        );
        $map = array('status' => 0, 'update_at' => time());
        $ret = $model->table('sj_textquickentry_extent_soft')->where($where)->save($map);
        if ($ret) {
            $this->writelog("文字快捷入口：删除了id为{$id}的内容",'sj_textquickentry_extent_soft',$id,__ACTION__ ,"","del");
            $this->success("删除成功");
        } else {
            $this->error("删除失败");
        }
    }
    
    // 返回冲突id，否则返回0
    private function check_conflict($record, $id = 0) {
        $content_type = $record['content_type'];
        $start_at = $record['start_at'];
        $end_at = $record['end_at'];
        $extent_id = $record['extent_id'];
		$life=$record['life'];
        $model = M();
        if ($content_type == 1) {
            // 查找包名
            $content_key = 'package';
            $content_value = $record['package'];
        } else if ($content_type == 2) {
            // 查找活动
            $content_key = 'activity_id';
            $content_value = $record['activity_id'];
        } else if ($content_type == 3) {
            // 查找专题
            $content_key = 'feature_id';
            $content_value = $record['feature_id'];
        } else if ($content_type == 4) {
            // 查找页面
            $content_key = 'page_type';
            $content_value = $record['page_type'];
			$parameter_field = $record['parameter_field'];
        } else if ($content_type == 5) {
            // 查找网页
            $content_key = 'website';
            $content_value = $record['website'];
        } else {
            return false;
        }
		if($parameter_field)
		{
			$where = array(
				"{$content_key}" => $content_value,
				'extent_id' => $extent_id,
				'status' => 1,
				'start_at' => array('elt', $end_at),
				'end_at' => array('egt', $start_at),
				'parameter_field'=>$parameter_field,
			);
		}
		else
		{
			$where = array(
				"{$content_key}" => $content_value,
				'extent_id' => $extent_id,
				'status' => 1,
				'start_at' => array('elt', $end_at),
				'end_at' => array('egt', $start_at),
			);
		}
        
        if ($id&&$life!=1) {
            $where['id'] = array('neq', $id);
        }
        $find = $model->table('sj_textquickentry_extent_soft')->where($where)->find();
        if ($find['id'])
            return $find['id'];
        return 0;
    }
    
    private function check_rank_conflict($record, $id=0) {
        $model = M();
        // 找出其所在区间排期有交集且排序相同的数据，不管是哪个内容类型
        $rank = $record['rank'];
        $start_at = $record['start_at'];
        $end_at = $record['end_at'];
        $extent_id = $record['extent_id'];
		$life=$record['life'];
        if (!$extent_id) {
            if (!$id) {
                $this->error("程序出错啦");
            } else {
                // 找出所在区间
                $find = $model->table('sj_textquickentry_extent_soft')->where(array('id' => $id))->find();
                if (!$find['extent_id']) {
                    $this->error("程序出错啦");
                }
                $extent_id = $find['extent_id'];
            }
        }
        // 找冲突
        $where = array(
            'rank' => $rank,
            'status' => 1,
            'start_at' => array('elt', $end_at),
            'end_at' => array('egt', $start_at),
            'extent_id' => $extent_id,
        );
        if ($id&&$life!=1) {
            $where['id'] = array('neq', $id);
        }
        $find = $model->table('sj_textquickentry_extent_soft')->where($where)->find();
        if ($find['id'])
            return $find['id'];
        return 0;
    }
    
    private function clear_content(&$map) {
        $map['activity_id'] = 0;
        $map['feature_id'] = 0;
        $map['page_type'] = '';
        $map['website'] = '';
        $map['page_flag'] = '';
        $map['page_id1'] = 0;
        $map['page_id2'] = 0;
		$map['parameter_field'] ='';
    }
    
    // 根据区间id得到其标题受限字数
	/* V6.0 区间设置2个入口 标题限制6个字
	        设置3个入口   标题限制4个字
			设置4个入口   标题限制2个字
			应用-最热和游戏最热页面 设置2或3个入口前台标题不显示*/
	//V6.2增加 入口的样式 第一个区间如果是图文 以后该页面都是图文
    private function title_limit_length($extent_id) {
        /*$page_length_arr = array(
            'top_1_hot' => 2,
            'top_2_hot' => 2,
            'otherfixed_homepage_recommend' => 2,
        );*/
        // $default_length = 6;
        // 先得到区间所在页面
        $model = M();
        $where = array(
            'extent_id' => $extent_id
        );
        $find = $model->table('sj_textquickentry_extent')->where($where)->find();
        /*$belong_page_type = $find['belong_page_type'];
        $length = $default_length;
        if (array_key_exists($belong_page_type, $page_length_arr)) {
            $length = $page_length_arr[$belong_page_type];
        }*/
		if($find['entrance_count']==2||$find['entrance_count']==0)
		{
			$length = 6;
		}
		elseif($find['entrance_count']==3||$find['entrance_type']==4)
		{
			$length = 4;
		}
		else
		{
			$length = 2;
		}
		$arr_info =array();
		$arr_info['length'] = $length;
		$arr_info['entrance_count'] = $find['entrance_count'];
		$arr_info['entrance_type'] = $find['entrance_type'];
        return $arr_info;
    }

    public function pub_csv_count(){
        $this->csv_count();
    }

    public function deal_cover_user(&$map){
        if(!empty($_POST['cover_user_type'])){
            $json_arr = array('cover_user_type'=>$_POST['cover_user_type']);
        }else{
            $json_arr = array('cover_user_type'=>0);
        }
        if($_POST['cover_user_type'] != 2){
            $json_arr['activation_date_start'] = 0;
            $json_arr['activation_date_end'] = 0;
        }else{
            $json_arr['activation_date_start'] = $_POST['activation_date_start']?strtotime($_POST['activation_date_start']):0;
            $json_arr['activation_date_end'] = $_POST['activation_date_end']?strtotime($_POST['activation_date_end']):0;
        }

        if(!empty($map['parameter_field'])){
            $map['parameter_field'] = json_decode($map['parameter_field'],true);
            $map['parameter_field'] = array_merge($map['parameter_field'],$json_arr);
        }else{
            $map['parameter_field'] = $json_arr;
        }
        $map['parameter_field'] = json_encode($map['parameter_field']);
        if($_FILES['upload_file']['size'])
        {
            $filename=$_FILES['upload_file']['name'];
            if(!$filename&&!$_POST['csv_count'])
            {
                $map['dl_count'] = 0;
                $map['csv_url'] = "";
                $map['is_upload_csv'] = 0; //标注是否上传csv
            }
            if($filename&&!$_POST['csv_count'])
            {
                $this->error("选择好的文件请点击上传才有效");
            }
            if($_POST['csv_count']&&$_POST['csv_url'])
            {
                $map['dl_count'] = trim($_POST['csv_count']);
                $map['csv_url'] = trim($_POST['csv_url']);
                $map['is_upload_csv'] = 1;
            }
            unset($_FILES['upload_file']);
        }else{
            $map['dl_count'] = trim($_POST['csv_count']);
            $map['csv_url'] = trim($_POST['csv_url']);
            $map['is_upload_csv'] = !empty($map['csv_url'])?1:0;
        }
    }
}
?>