<?php
//软件相关的不是特别大的功能 统一放到这里
class DerivesoftAction extends CommonAction{

    function derive_soft(){
        ini_set('memory_limit','500M');
        $model = new Model();
        $result_soft = $model -> table('sj_soft') -> where('status = 1') -> field('softid,softname,package,last_refresh') -> select();
        foreach($result_soft as $key => $val){
                $where['softid'] = $val['softid'];
                $result_url = $model -> table('sj_soft_file') -> where($where) -> field('url') -> select();
                $val['url'] = 'http://apk.goapk.com'.$result_url[0]['url'];
                $soft_list[$key] = $val; 
        }
        $num = ceil(count($soft_list)/50000);
        for($i=0;$i<$num;$i++){
                $go = array_slice($soft_list,$i*50000,50000);
                foreach($go as $key => $val){
                        $soft_str .= $val['softid'].',"'.$val['softname'].'","'.$val['package'].'",'.date('Y-m-d',$val['last_refresh']).',"'.$val['url'].'"'."\n";
                }
                $file = 'soft_'.$i.'.csv';
                file_put_contents($file,$soft_str);
                $soft_str = "";
        }
    }

    /**
    * Desc:   榜单管理
    * @author Sun Tao<suntao@anzhi.com>
    * @final  2014-12-11
    */
    function bdlist()
    {
        $model = M('list');
        if($this->isPost())
        {
            //下拉修改排序
            $id = $_POST['id'];
            $res = $model->where('id='.$id)->find();
            $order = $_POST['order'];
            $old_order = $_POST['old_order'];
            $from = $_POST['from'];
            if(abs($old_order-$order)==1)
            {
			$model->where("order_num='{$order}' and from_chl = {$from} ")->save(array('order_num'=>$old_order));
            }else if($old_order>$order)
            {
                $model->where('order_num>='.$order.' and order_num<'.$old_order." and from_chl = {$from}")->save(array('order_num'=>array('exp','order_num+1')));
            }else
            {
                $model->where('order_num>'.$old_order.' and order_num<='.$order." and from_chl = {$from}")->save(array('order_num'=>array('exp','order_num-1')));
            }
            $model->where('id='.$id." and from_chl = {$from}")->save(array('order_num'=>$order));
			$log = $from == 1 ? '应用频道' : '5.5榜单频道';
            $this->writelog('市场运营基础配置-榜单'.$log.'，修改了ID为"'.$id.'",名称为"'.$res['name'].'"的排序,从'.$old_order.'改成了'.$order, 'sj_list',$id,__ACTION__ ,'','edit');
            echo 1;exit(0);
        }
		$from = $_GET['from'] ? $_GET['from'] : 0 ; 
		$where = array(
			'status' => 1,
			'from_chl' => $from
		);
        $res = $model->where($where)->order('order_num asc')->select();
        $this->assign('list',$res);
        $orderarr = array();
        foreach($res as $k=>$v)
        {
            $orderarr[$k] = $v['order_num'];
        }
        $this->assign('from', $from);
        $this->assign('order',$orderarr);
        $this->display();
    }

    /**
    * Desc:   添加修改榜单
    * @author Sun Tao<suntao@anzhi.com>
    * @final  2014-12-12
    */
    function edit_bd()
    {
        $model = M('list');
        if($this->isPost())
        {
            $bdid = $_POST['bdid'];
            //构造data数据

            $data = array();
            if($_POST['role']==1){
                $role_one= array();
                $role_one['today'] = $_POST['today'];
                $role_one['yesterday'] = $_POST['yesterday'];
                $role_one['deserted'] = $_POST['deserted'];
                $role_one['daqiantian'] = $_POST['daqiantian'];  
                $data['role_two_arr'] = " ";
                $data['role_one_arr'] = json_encode($role_one);
            }
            if($_POST['role']==2){
                $role_two= array();
                $role_two['newdown'] = $_POST['newdown'];
                $role_two['install'] = $_POST['install'];
                $role_two['searchdown'] = $_POST['searchdown'];
                $role_two['pinglun'] = $_POST['pinglun'];
                $role_two['haoping'] = $_POST['haoping'];
                $data['role_two_arr'] = json_encode($role_two);
                $data['role_one_arr'] = " ";
            }
            if($_POST['role']==3){
             //网游和单机的规则三
                $role_three= array();
                $role_three['consume_now'] = $_POST['consume_now'];
                $role_three['consume_one'] = $_POST['consume_one'];
                $role_three['consume_two'] = $_POST['consume_two'];
                $role_three['consume_three'] = $_POST['consume_three'];
                $data['role_one_arr'] = json_encode($role_three);
                $data['role_two_arr'] = " ";
            }
             if($_POST['role']==4){
                //网游和单机的规则二
                $role_four= array();
                $role_four['now_seek'] = $_POST['now_seek'];
                $role_four['seek_one'] = $_POST['seek_one'];
                $role_four['seek_two'] = $_POST['seek_two'];
                $role_four['seek_three'] = $_POST['seek_three'];
                $data['role_one_arr'] = json_encode($role_four);
                $data['role_two_arr'] = " ";
            }   

             if($_POST['role']==5){
                //运营榜单规则
                $role_four= array();
                $role_four['searchday'] = $_POST['searchday'];
                $data['role_one_arr'] = json_encode($role_four);
                $data['role_two_arr'] = " ";
            }             
            
            $data['name'] = $_POST['bdname'];
            $data['num'] = $_POST['bdnum'];
            $data['type'] = $_POST['bdtype'];
            $data['role'] = $_POST['role'];
            $data['scope'] = $_POST['scope'];
            if($_POST['scope']==1){
                $data['xb_days'] = $_POST['xb_days'];
                $data['qx_days'] = " ";  
           }else{
                $data['xb_days'] = " ";
                $data['qx_days'] = $_POST['qx_days'];
           }
            $data['update_tm'] = time();
            $data['from_chl'] = $_POST['from'];
            if($bdid) //修改
            {
                $where_arr = array('id' => $bdid);
                $log = $this->logcheck($where_arr, 'sj_list', $data, $model);
                $msg = "市场运营基础配置-榜单列表：编辑了id为{$_POST['bdid']}的记录：".$log;
                $this->writelog($msg, 'sj_list',$_POST['bdid'],__ACTION__ ,'','edit');
                $rs = $model->where($where_arr)->save($data);
                if($rs!=false){
                    echo 1;exit(0);
                }else{
                    echo 2;exit(0);
                }
            }else{//新增
                $res = $model->field('order_num')->where("status=1 and from_chl = {$_POST['from']}")->order('order_num desc')->find();
                $data['create_tm'] = time();
                $data['order_num'] = $res['order_num']+1;//有效的最高排序加1

                $rs = $model->add($data);
                if($rs!=false){
                    $this->writelog('市场运营基础配置-榜单列表，新增了名称为"'.$_POST["bdname"].'"的数据,其他数据为'.json_encode($data), 'sj_list',$rs,__ACTION__ ,'','add');
                    echo 1;exit(0);
                }else{
                    echo 2;exit(0);
                }
            }
        }

        $bdid = $_GET['bdid'];
        $rett = $model->where("status=1 and id = $bdid")->find();
        if($rett['role']==1){
            $role_one_arr = json_decode($rett['role_one_arr'],true);
            $this->assign('role_one_arr',$role_one_arr);
        }elseif ($rett['role']==2) {
           $role_two_arr = json_decode($rett['role_two_arr'],true);
           $this->assign('role_two_arr',$role_two_arr);
        }elseif ($rett['role']==3) {
            $role_three_arr = json_decode($rett['role_one_arr'],true);
            $this->assign('role_three_arr',$role_three_arr);
        }elseif($rett['role']==4){
            $role_four_arr = json_decode($rett['role_one_arr'],true);
            $this->assign('role_four_arr',$role_four_arr);
        }elseif($rett['role']==5){
            $role_one_arr = json_decode($rett['role_one_arr'],true);
            $this->assign('role_one_arr',$role_one_arr);
        }
        $this->assign('rs',$rett);
        if($bdid){
                $this->assign('title','编辑');
        }else{
                $this->assign('title','新增');
        }
		$this->assign('from',$_GET['from']);
        $this->display();
    }
    
    /**
    * Desc:   删除榜单
    * @author Sun Tao<suntao@anzhi.com>
    * @final  2014-12-15
    */
    function deletebd()
    {
        $model = M('list');
		$from = $_POST['from']; 
        $res = $model->where('id='.$_POST['bdid'] ." and from_chl={$from}")->save(array('status'=>0));
        $category_type = 'bdlist_'.$_POST['bdid'];

        $model->table('sj_category_extent')->where('category_type = "'.$category_type.'"')->delete();

        $model->where('order_num>'.$_POST['order_num']." and from_chl={$from}")->save(array('order_num'=>array('exp','order_num-1')));

        if($res!=false){
            $this->writelog('市场运营基础配置-榜单列表，删除了ID为"'.$_POST['bdid'].'"的记录', 'sj_list',$_POST['bdid'],__ACTION__ ,'','del');
            echo 1;exit(0);
        }
    }


	//网页分享管理 通用跳转  added by shitingting  2015-01-05
	function share_web_list(){
		$model = M('sj_flexible_extent_soft');
		import("@.ORG.Page");
        $where['status'] = 1;
        if(!isset($_GET['resource_type'])){
            $resource_type = 24;
        }else{
            $resource_type = $_GET['resource_type'];
        }
        if($_GET['extent_id']) {
        	$where['extent_id'] = $_GET['extent_id'];
        }
        $where['resource_type'] = $resource_type;
        if(!empty($_GET['srch_title'])){
            $where['title'] = array('like', "%".trim($_GET['srch_title'])."%");
        }
        /*if(!empty($_GET['srch_pkg_name'])){
            $where['package_name'] = array('like', "%".trim($_GET['srch_pkg_name'])."%");
        }*/
        if(!empty($_GET['srch_pkg'])){
            $where['package_643'] = trim($_GET['srch_pkg']);
        }
		$count = $model->table('sj_flexible_extent_soft')->where($where)->count();
        $param = http_build_query($_GET);
        $Page = new Page($count, 20, $param);
		$list = $model->table('sj_flexible_extent_soft')->where($where)->order('id DESC')->limit($Page->firstRow.','.$Page->listRows)->select();
        /*$pkg_arr = array();
        foreach($list as $val){
            if(!in_array($val['package_643'], $pkg_arr)){
                $pkg_arr[] = $val['package_643'];
            }
        }
        $pkg_names = M('')->table('sj_soft')->field('softname,package')->order('softid desc')->where(array('package'=>array('in', $pkg_arr), 'hide'=>1, 'status'=>1))->select();
        $pkg_name_arr = array();
        foreach($pkg_names as $val){
            if(!array_key_exists($val['package'], $pkg_name_arr)){
                $pkg_name_arr[$val['package']] = $val['softname'];
            }
        }*/
		foreach ($list as $key => $value) {
            //$list[$key]['package_name'] = $pkg_name_arr[$value['package_643']];
            $content_type = $value['content_type'];
            if ($content_type == 2) {
                // 活动名称
                $list[$key]['activity_name'] = ContentTypeModel::convertActivityId2ActivityName($value['activity_id']);
            } else if ($content_type == 3) {
                // 专题名称
                $list[$key]['feature_name'] = ContentTypeModel::convertFeatureId2FeatureName($value['feature_id']);
            } else if ($content_type == 4) {
                // 页面名称
                $list[$key]['page_name'] = ContentTypeModel::convertPageType2PageName($value['page_type']);
            } else if($content_type == 9) {
            	$used_info = json_decode($value['parameter_field'], true);
            	$list[$key]['used_title'] = isset($used_info['title']) ? $used_info['title'] : '';
            }
            //单软件(视频)勾选开发者，关联标题和视频默认图
            if($value['resource_type']==29 && $value['is_dev']==1){
                $parameter_field = json_decode($value['parameter_field'], true);
                $video_id = $parameter_field['video_id'];
                $video_one = $model->table('sj_soft_extra')->field('video_title,video_pic')->where("id={$video_id}")->find();
                $list[$key]['title'] = $video_one['video_title'];
                $list[$key]['video_pic'] = $video_one['video_pic'];
            }
        }
		
		$show = $Page->show();
		if($_GET['lr']){
			$lr = $_GET['lr'];
		}else{
			$lr = 20;
		}
		if($_GET['p']){
			$p = $_GET['p'];
		}else{
			$p = 1;
		}
		$Page -> setConfig('header', '篇记录');
		$Page -> setConfig('first', '<<');
		$Page -> setConfig('last', '>>');
        $this -> assign('domain_url', ATTACHMENT_HOST);
		$this -> assign('srch_title', $_GET['srch_title']);
        $this -> assign('srch_pkg', $_GET['srch_pkg']);
        $this -> assign('srch_pkg_name', $_GET['srch_pkg_name']);
        $this -> assign('lr', $lr);
		$this -> assign('p', $p);
        $this -> assign("page", $show);
		$this -> assign('list', $list);
		$this -> assign('extent_id', $_GET['extent_id']);
        $this -> assign('resource_type', $resource_type);
		$this -> display();
	}
	function add_share_web(){
		if($_POST){
			$model = M('sj_flexible_extent_soft');
            $resource_type = trim($_POST['resource_type']);
            $package_643 = trim($_POST['package_643']);
            $package_name = trim($_POST['package_name']);
            $title = trim($_POST['title']);
            $is_dev = trim($_POST['is_dev']);
            $video_id = trim($_POST['video_id']);
            $video_url = trim($_POST['video_url']);
            $video_pic = trim($_POST['video_pic']);
            $s_description = trim($_POST['s_description']);
            $pic_bottom_des = trim($_POST['pic_bottom_des']);
            $map['resource_type'] = $resource_type;
            $map['extent_id'] = $_POST['extent_id'];
            
			//推荐内容处理 合并
			//bug待处理 选择推荐内容 页面类型 普通 填入活动列表  活动类型（这个参数页面未提交过来）
            if($resource_type==24 || $resource_type==26 || $resource_type==0 || $resource_type==2){
                $rcontent = ContentTypeModel::saveRecommendContent($_POST, '', $map);
                if($rcontent !== true){
                    $this->error($rcontent);
                }
            }
            //非推荐内容资源库
            if($resource_type==24 || $resource_type==26 || $resource_type==29 || $resource_type==2 || $resource_type==28){
                if(empty($package_643)){
                    $this->error('请填写软件包名');
                }
                $map['package_643'] = $package_643;
                $map['package_name'] = $package_name;
                if(($resource_type!=29 || empty($is_dev)) && $resource_type!=28){
                    if (empty($title)) {
                        $this->error("标题不能为空");
                    }
                    if (mb_strlen($title, 'utf-8') > 25) {
                        $this->error("标题最多25个字符");
                    }
                    $map['title'] = $title;
                }
                if(!empty($is_dev)){
                    $map['is_dev'] = $is_dev;
                    if($resource_type==29){
                        $map['parameter_field'] = json_encode(array('video_id'=>$video_id));
                    }
                }else{
                    if($resource_type==24){
                        $map['image_url'] = $this->deal_img('image_url', 464, 274);
                    }elseif($resource_type==26){
                        $map['image_url'] = $this->deal_img('image_url', 160, 160);
                        $map['high_image_url'] = $this->deal_img('high_image_url', 160, 160, '图片2');
                        $map['low_image_url'] = $this->deal_img('low_image_url', 160, 160, '图片3');
                    }elseif($resource_type==29){
                        $map['video_url'] = $video_url;
                        //表单中ajax获取的图片链接带域名，入库前要去掉
                        $map['video_pic'] = str_replace(ATTACHMENT_HOST, '', $video_pic);
                    }
                }
            }
            if($resource_type==2) {
                if (empty($title)) {
                    $this->error("标题不能为空");
                }
                if (mb_strlen($title, 'utf-8') > 10) {
                    $this->error("标题最多10个字符");
                }
                $map['title'] = $title;
                $map['image_url'] = $this->deal_img('image_url', 466, 140,'图片');
                $map['high_image_url'] = $this->deal_img('high_image_url', 684, 185, '高分图片');
                $map['low_image_url'] = $this->deal_img('low_image_url', 444, 120, '低分图片');
                $map['gif_image_url'] = $this->deal_img('gif_image_url', 444, 120, 'GIF图片','gif');
                $map['gif_image_url_62'] = $this->deal_img('gif_image_url_62', 684, 185, 'GIF图片','gif');
                $map['pic_bottom_des'] = $pic_bottom_des;
            }
            if($resource_type==28) {
                $map['description'] = $s_description;
            }
			// 创建时间和更新时间
            $map['update_at'] = time();
            //$this->showArr($map);
            $is_edit = trim($_POST['is_edit']);
			if(!empty($is_edit) && $resource_type==24){
                // 同包名同内容类型已存在则编辑
                $where = array('id'=>$is_edit);
                $log = $this->logcheck($where, 'sj_flexible_extent_soft', $map, $model);
                $ret = $model->table('sj_flexible_extent_soft')->where($where)->save($map);
            }else{
                // 添加
                $map['create_at'] = time();
                //print_r($map);die;
                $affect = $model->table('sj_flexible_extent_soft')->add($map);
            }
            if (!empty($is_edit) && $resource_type==24) {
                if ($ret || $ret === 0) {
                    $this->writelog("灵活运营资源库：编辑了id为{$is_edit}的记录，{$log}", 'sj_flexible_extent_soft', $is_edit, __ACTION__ , '', 'edit');
                    $this->success("替换成功！");
                } else {
                    $this->error("替换失败！");
                }
            }else{
                if($affect){
                    $this -> writelog('灵活运营资源库添加了id为['.$affect.']的记录', 'sj_flexible_extent_soft', $affect, __ACTION__, '', 'add');
                    //$this->assign('jumpUrl', '/index.php/Sj/Derivesoft/share_web_list/resource_type/'.$resource_type);
                    $this->success('添加成功');
                }else{
                    $this->error('添加失败');
                }
            }
		}else{
			$extent_id		=	$_GET['extent_id'];
			$resource_type	=	$_GET['resource_type'];
			$this->assign('extent_id', $extent_id);
			$this->assign('resource_type', $resource_type);
			$this->display();
		}
	}
    public function package_check(){
        $model = new Model();
        $id = $_GET['id'];
        $package_643 = trim($_GET['package_643']);
        $content_type = trim($_GET['content_type']);
        $resource_type = trim($_GET['resource_type']);
        $show_style = trim($_GET['show_style']);
        
        $where['status'] = 1;
        $where['package_643'] = $package_643;
        if(!empty($resource_type)){
            $where['resource_type'] = $resource_type;
        }
        if(!empty($show_style)){
            if($show_style==1){
                $where['resource_type'] = 24;
            }elseif($show_style==2){
                $where['resource_type'] = array('in', '24,26');
            }
        }
        if(!empty($content_type)){
            $where['content_type'] = $content_type;
        }
        if(!empty($id)){
            $where['id'] = array('exp', " != {$id}");
        }
        $result = $model->table('sj_soft')->where(array('package'=>$package_643, 'hide'=>1, 'status'=>1))->select();
        if(!empty($result)){
            $res = $model->table('sj_flexible_extent_soft')->where($where)->select();
            //echo $model->getLastSql();die;
            if(!empty($res)){
                exit(json_encode(array('code'=>2, 'msg'=>$result[0]['softname'], 'edit'=>$res[0]['id'])));
            }else{
                exit(json_encode(array('code'=>0, 'msg'=>$result[0]['softname'])));
            }           
        }else{
            exit(json_encode(array('code'=>1)));
        }
    }
	function edit_share_web(){
		$model = M('sj_flexible_extent_soft');
		if($_POST){
			$id = $_POST['id'];
            $resource_type = trim($_POST['resource_type']);
            $title = trim($_POST['title']);
            $is_dev = trim($_POST['is_dev']);
            $s_description  = trim($_POST['s_description']);
            $pic_bottom_des = trim($_POST['pic_bottom_des']);
			 //推荐内容处理 合并
            if($resource_type==24 || $resource_type==26 || $resource_type==0 || $resource_type==2){
                $rcontent = ContentTypeModel::saveRecommendContent($_POST, '', $map);
                if($rcontent!==true){
                    $this->error($rcontent);
                }
            }
            if($resource_type==24 || $resource_type==26 || $resource_type==29 || $resource_type==2){
                if($resource_type!=29 || empty($is_dev)){
                    if (empty($title)) {
                        $this->error("标题不能为空");
                    }
                    if (mb_strlen($title, 'utf-8') > 25) {
                        $this->error("标题最多25个字符");
                    }
                    $map['title'] = $title;
                }
            }
            if($resource_type==24 && $_FILES['image_url']['tmp_name']){
                $map['image_url'] = $this->deal_img('image_url', 464, 274);
            }
            if($resource_type==26){
                if($_FILES['image_url']['tmp_name']){
                    $map['image_url'] = $this->deal_img('image_url', 160, 160);
                }
                if($_FILES['high_image_url']['tmp_name']){
                    $map['high_image_url'] = $this->deal_img('high_image_url', 160, 160, '图片2');
                }
                if($_FILES['low_image_url']['tmp_name']){
                    $map['low_image_url'] = $this->deal_img('low_image_url', 160, 160, '图片3');
                }
            }
             if($resource_type==2){
                if($_FILES['image_url']['tmp_name']){
                    $map['image_url'] = $this->deal_img('image_url', 466, 140);
                }
                if($_FILES['high_image_url']['tmp_name']){
                    $map['high_image_url'] = $this->deal_img('high_image_url', 684, 185, '图片2');
                }
                if($_FILES['low_image_url']['tmp_name']){
                    $map['low_image_url'] = $this->deal_img('low_image_url', 444, 120, '图片3');
                }
                if($_FILES['gif_image_url']['tmp_name']){
                    $map['gif_image_url'] = $this->deal_img('gif_image_url', 444, 120, 'GIF图片','gif');
                }
                if($_FILES['gif_image_url_62']['tmp_name']){
                    $map['gif_image_url_62'] = $this->deal_img('gif_image_url_62', 684, 185, 'GIF图片','gif');
                }
            }
            if($resource_type==2 && $pic_bottom_des) {
                $map['pic_bottom_des'] = $pic_bottom_des;
            }
            if($resource_type==28 && $s_description) {
                $map['description'] = $s_description;
            }
			// 创建时间和更新时间
            $map['update_at'] = time();
            // 添加
            $where = array('id'=>$id);
            $log = $this->logcheck($where, 'sj_flexible_extent_soft', $map, $model);
			$ret = $model->table('sj_flexible_extent_soft')->where($where)->save($map);
			if ($ret || $ret===0) {
				$this->writelog("灵活运营资源库：编辑了id为{$id}的记录，{$log}", 'sj_flexible_extent_soft', $id, __ACTION__, '', 'edit');
				$this->success("编辑成功！");
			} else {
				$this->error("编辑失败！");
			}
		}else{
			$model = M('sj_flexible_extent_soft');
			$id = $_GET['id'];
			$list = $model->table('sj_flexible_extent_soft')->where(array('id'=>$id))->find();
			if($_GET['show_video']==1){
                if(empty($list['video_url'])){
                    $parameter_field = json_decode($list['parameter_field'], true);
                    $video_id = $parameter_field['video_id'];
                    $video_ret = M('')->table('sj_soft_extra')->where("id={$video_id}")->find();
                    $list['video_url'] = $video_ret['video_url'];
                }
                $this->assign('attachment_host', GAMEINFO_ATTACHMENT_HOST);
                $this->assign('list', $list);
                $this->display('video_show');
            }else{
                $this->assign('domain_url', ATTACHMENT_HOST);
                $this->assign('list', $list);
                $this->display();
            }
		}
	}
	function delete_share_web(){
		$model = M('sj_flexible_extent_soft');
		$id = $_GET['id'];
		$extent_id = $_GET['extent_id'];
        $resource_type = $_GET['resource_type'];
		$result = $model -> table('sj_flexible_extent_soft') -> where(array('id' => $id)) -> save(array('status' => 0));
		if($result){
			$this -> writelog("已删除id为{$id}的记录", 'sj_flexible_extent_soft',$id,__ACTION__ ,'','del');
			$this -> assign("jumpUrl","/index.php/Sj/Derivesoft/share_web_list/extent_id/".$extent_id."/resource_type/".$resource_type);
			$this -> success("删除成功");
		}else{
			$this -> error("删除失败");
		}
	}

    private function deal_img($image_url,$image_width=0,$image_height=0,$image_name='图片',$expression='jpg|png'){
        if(!$_FILES[$image_url]['tmp_name']){
            $this->error("请上传{$image_name}！");
        }
        // 取得图片后缀
        $suffix = preg_match("/\.({$expression})$/", $_FILES[$image_url]['name'],$matches);
        if ($matches) {
            $suffix = $matches[0];
        } else {
            $this->error("{$image_name}格式应为{$expression}！");
        }
        // 判断图片长和宽
        $img_info_arr = getimagesize($_FILES[$image_url]['tmp_name']);
        if (!$img_info_arr) {
            $this->error("上传{$image_name}出错！");
        }
        // $width = $img_info_arr[0];
        // $height = $img_info_arr[1];
        // if($image_width!=0&&$image_height!=0){
        //     if ($width!=$image_width || $height!=$image_height){
        //         $this->error("{$image_name}尺寸错误，宽需为{$image_width}px，高需为{$image_height}px");
        //     }
        // }
        $folder = "/img/".date("Ym/d/");
        $this->mkDirs(UPLOAD_PATH . $folder);
        $relative_path = $folder . time() . '_' . rand(1000,9999) . $suffix;
        $img_path = UPLOAD_PATH . $relative_path;
        $ret = move_uploaded_file($_FILES[$image_url]['tmp_name'], $img_path);
        return $relative_path;
    }

    public function get_package(){
        $model = new Model();
        $package_643 = trim($_GET['package_643']);
        $resource_type = trim($_GET['resource_type']);
        if(empty($package_643)){
            exit(json_encode(array('code'=>0)));
        }
        $where['status'] = 1;
        $where['package_643'] = $package_643;
        if(!empty($resource_type)){
            $where['resource_type'] = $resource_type;
        }
        $res = $model->table('sj_flexible_extent_soft')->field('id,content_type,title,description,is_dev,parameter_field,resource_type')->where($where)->select();
        if(!empty($res)){
            foreach ($res as $key => $value) {
                if($value['resource_type']==29 && $value['is_dev']==1 ) {
                    $video_arr  = json_decode($value['parameter_field'], true);
                    $video_info = $model->table('sj_soft_extra')->field('video_title,video_pic')->where(array('id'=>$video_arr['video_id']))->find();
                    $res[$key]['title'] = $video_info['video_title'];
                }
            }
            exit(json_encode(array('code'=>1,'info'=>$res)));
        }else{
            exit(json_encode(array('code'=>0)));
        }
    }
	
}
