<?php
class ExtentV1Action extends CommonAction {

    private $image_width_long_multi = 466;
    private $image_height_long_multi = 112;

	function ExtentAction()
	{
		//C('SHOW_RUN_TIME',false);			// 运行时间显示
		//C('SHOW_PAGE_TRACE',false);
		parent::__construct();
	}
	function index()
	{
		if (isset($_GET['act']) && method_exists($this, $_GET['act'].'Act')) {
			$act = $_GET['act'].'Act';
			$this->$act();
			exit;
		}
		$util = D("Sj.Util");
		$pid = isset($_GET['pid']) ? $_GET['pid'] : 1;

		import("@.ORG.Page");
		$param = http_build_query($_GET);
		$limit = 10;
		if(isset($_GET['lr'])){
			$this->assign("lr",(int)$_GET['lr']);
		}else{
			$this->assign("lr",$limit);
		}
		if(isset($_GET['p'])){
			$this->assign("p",(int)$_GET['p']);
		}else{
			$this->assign("p", 1);
		}

		$model = M('extentV1');
		$parent_id = isset($_GET['parent_id']) ? (int)$_GET['parent_id'] : 0;

		$map = array(
			'status' => 1,
			'pid' => $pid,
			'parent_id' => $parent_id,
			'type' => array('exp', '!=3'),
            'extent_type' => array('exp', '!=4'),
		);

		$count_total = $model -> where($map)->count();
		$page  = new Page($count_total, $limit, $param);

		$this->assign('parent_id', $parent_id);
		$this->assign('pid', $pid);

		$now = time();
		$list = $model->where($map)->order('rank asc, type desc')->limit($page->firstRow . ',' . $page->listRows)->select();

		$channel_model = M('channel');
		$channels = $channel_model->field("`cid`,`chname`")->where(array('status' => 1))->select();
		$channels_key = array();
		foreach($channels as $v) {
			$channels_key[$v['cid']] = $v['chname'];
		}

		$operating_db = D('Sj.Operating');
		$operating_list = $operating_db->field('oid,mname')->select();
		$operators_key = array();
		foreach($operating_list as $v) {
			$operators_key[$v['oid']] = $v['mname'];
		}

		foreach ($list as $k => $v) {
			if ($v['type'] == 1) {
				$where = array(
					'extent_id' => $v['extent_id'],
					'start_at' => array('elt',$now),
					'end_at' => array('egt',$now),
					'status' => 1
				);
				$count = $model->table('sj_extent_soft_v1')->where($where)->count();
				$list[$k]['soft_counts'] = intval($count);
			} else {
				$list[$k]['soft_counts'] = '-';
				$list[$k]['extent_size'] = '-';
			}
			$chname = array();
			if (!empty($v['cid'])) {
				$cids = explode(',', $v['cid']);
				foreach ($cids as $cid) {
					if (isset($channels_key[$cid]))
						$chname[] = $channels_key[$cid];
				}
			}
			$list[$k]['chname'] = empty($chname) ? '-' : implode(', ', $chname);
			$list[$k]['mname'] = isset($operators_key[$v['oid']]) ? $operators_key[$v['oid']] : '-';
			if (!empty($v['parent_union_id']))
			{
				$result = $model->where(array('extent_id' => $v['parent_union_id']))->find();
				if (!empty($result['extent_name']))
					$list[$k]['union_name'] = $result['extent_name'];
			}
			else
				$list[$k]['union_name'] = '-';

		}
		$this->assign('list', $list);
		$this->assign('isAjax', $this->isAjax());

		$this->assign('product_list',$util->getProducts($pid));
		$page->setConfig('header', '篇记录');
		$page->setConfig('first', '<<');
		$page->setConfig('last', '>>');
		$this->assign("page", $page->show());

		$this->assign('count',$count_total);
		$html = $this->fetch();
		header("Cache-control: no-store");
		header("pragma:no-cache");
		exit($html);
	}

    // 多软件位区间列表展示
    function index_multiSoftwareExtent() {
        // 平台pid的map表
        $util = D("Sj.Util");
        $pid = isset($_GET['pid']) ? $_GET['pid'] : 1;
        $this->assign('product_list',$util->getProducts($pid));

        // 渠道号cid的map表
        $channel_model = M('channel');
        $channels = $channel_model->field("`cid`,`chname`")->where(array('status' => 1))->select();
		$channels_key = array();
		foreach($channels as $v) {
			$channels_key[$v['cid']] = $v['chname'];
		}

        // 运营商号oid的map表
        $operating_db = D('Sj.Operating');
		$operating_list = $operating_db->field('oid,mname')->select();
		$operators_key = array();
		foreach($operating_list as $v) {
			$operators_key[$v['oid']] = $v['mname'];
		}

        $pid = isset($_GET['pid']) ? $_GET['pid'] : 1;
        $this->assign('pid', $pid);

        import("@.ORG.Page");
		$param = http_build_query($_GET);
		$limit = 10;
		if(isset($_GET['lr'])){
			$this->assign("lr",(int)$_GET['lr']);
		}else{
			$this->assign("lr",$limit);
		}
		if(isset($_GET['p'])){
			$this->assign("p",(int)$_GET['p']);
		}else{
			$this->assign("p", 1);
		}

        $model = M('extentV1');
		$parent_id = 0;
        $this->assign('parent_id', $parent_id);
        $map = array(
			'status' => 1,
			'pid' => $pid,
			'parent_id' => $parent_id,
			'type' => array('exp', '=1'),
            'extent_type' => array('exp', '=4'),
		);
        $count_total = $model -> where($map)->count();
        $page  = new Page($count_total, $limit, $param);
        $list = $model->where($map)->order('rank asc, type desc')->limit($page->firstRow . ',' . $page->listRows)->select();

        $now = time();
        foreach ($list as $k => $v) {
			if ($v['type'] == 1) {
				$where = array(
					'extent_id' => $v['extent_id'],
					'start_at' => array('elt',$now),
					'end_at' => array('egt',$now),
					'status' => 1
				);
				$count = $model->table('sj_extent_soft_v1')->where($where)->count();
				$list[$k]['soft_counts'] = intval($count);
			} else {
				$list[$k]['soft_counts'] = '-';
				$list[$k]['extent_size'] = '-';
			}
			$list[$k]['chname'] = isset($channels_key[$v['cid']]) ? $channels_key[$v['cid']] : '-';
			$list[$k]['mname'] = isset($operators_key[$v['oid']]) ? $operators_key[$v['oid']] : '-';
            $list[$k]['display_description'] = $this->shorten_sentence($v['display_description']);
			if (!empty($v['parent_union_id']))
			{
				$result = $model->where(array('extent_id' => $v['parent_union_id']))->find();
				if (!empty($result['extent_name']))
					$list[$k]['union_name'] = $result['extent_name'];
			}
			else
				$list[$k]['union_name'] = '-';

		}

        $this->assign('list', $list);
        $this->assign('apkurl', IMGATT_HOST);
        $page->setConfig('header', '篇记录');
		$page->setConfig('first', '<<');
		$page->setConfig('last', '>>');
		$this->assign("page", $page->show());
        $this->display('index_multiSoftwareExtent');
    }

    function shorten_sentence($sentence, $len = 10) {
        $sen_len = mb_strlen($sentence, 'utf-8');
        if ($sen_len > $len) {
            $sentence = mb_substr($sentence, 0, $len - 2, 'utf-8') . '...';
        }
        return $sentence;
    }

    function show_content() {
        $extent_id = $_GET['extent_id'];
        $where = array(
            'extent_id' => $extent_id,
            'status' => 1,
        );
        $model = M('extent_v1');
        $find = $model->where($where)->find();
        $content = '';
        if ($find) {
            $content = $find['display_description'];
        }
        $this->assign('content', $content);
        $this->display('show_content');
    }

	function union()
	{
		if (isset($_GET['act']) && method_exists($this, $_GET['act'].'Act')) {
			$act = $_GET['act'].'Act';
			$this->$act();
			exit;
		}
		$util = D("Sj.Util");
		$pid = isset($_GET['pid']) ? $_GET['pid'] : 1;

		import("@.ORG.Page");
		$param = http_build_query($_GET);
		$limit = 10;
		if(isset($_GET['lr'])){
			$this->assign("lr",(int)$_GET['lr']);
		}else{
			$this->assign("lr",$limit);
		}
		if(isset($_GET['p'])){
			$this->assign("p",(int)$_GET['p']);
		}else{
			$this->assign("p", 1);
		}

		$model = M('extentV1');
		$parent_union_id = isset($_GET['parent_union_id']) ? (int)$_GET['parent_union_id'] : 0;

		$map = array(
			'status' => 1,
			'pid' => $pid,
			'parent_union_id' => $parent_union_id,
		);

		if (!empty($parent_union_id))
			$map['type'] = array('NEQ', 3);
		else
			$map['type'] = 3;

		$count_total = $model -> where($map)->count();
		$page  = new Page($count_total, $limit, $param);

		$this->assign('parent_union_id', $parent_union_id);
		$this->assign('pid', $pid);

		$now = time();
		$list = $model->where($map)->order('rank asc, type desc')->limit($page->firstRow . ',' . $page->listRows)->select();

		$channel_model = M('channel');
		$channels = $channel_model->field("`cid`,`chname`")->where(array('status' => 1))->select();
		$channels_key = array();
		foreach($channels as $v) {
			$channels_key[$v['cid']] = $v['chname'];
		}

		$operating_db = D('Sj.Operating');
		$operating_list = $operating_db->field('oid,mname')->select();
		$operators_key = array();
		foreach($operating_list as $v) {
			$operators_key[$v['oid']] = $v['mname'];
		}

		$extent_size = array();
		if (empty($parent_union_id))
		{
			$where = array(
				'status' => 1,
				'type' => array('NEQ', 3),
				'parent_union_id' => array('NEQ', 0),
			);
			$result = $model->field('parent_union_id, sum(extent_size) as c')->group('parent_union_id')->where($where)->select();
			foreach ($result as $v)
			{
				$extent_size[$v['parent_union_id']] = $v['c'];
			}
		}

		foreach ($list as $k => $v) {
			if ($v['type'] == 3) {
				$where = array(
					'extent_id' => $v['extent_id'],
					'start_at' => array('elt',$now),
					'end_at' => array('egt',$now),
					'status' => 1
				);
				$count = $model->table('sj_extent_soft_v1')->where($where)->count();
				$list[$k]['soft_counts'] = intval($count);
				$list[$k]['extent_size'] = isset($extent_size[$v['extent_id']]) ? $extent_size[$v['extent_id']] : 0;
			} else {
				$list[$k]['soft_counts'] = '-';
				$v['cid'] = 0;
				$v['oid'] = 0;
			}
			$list[$k]['chname'] = isset($channels_key[$v['cid']]) ? $channels_key[$v['cid']] : '-';
			$list[$k]['mname'] = isset($operators_key[$v['oid']]) ? $operators_key[$v['oid']] : '-';
		}
		$this->assign('list', $list);
		$this->assign('isAjax', $this->isAjax());
		$this->assign('product_list',$util->getProducts($pid));
		$page->setConfig('header', '篇记录');
		$page->setConfig('first', '<<');
		$page->setConfig('last', '>>');
		$this->assign("page", $page->show());

		$this->assign('count',$count_total);
		$html = $this->fetch();
		header("Cache-control: no-store");
		header("pragma:no-cache");
		exit($html);
	}

	function add_extent()
	{
		if (!empty($_POST)){
			$model = M('extent_v1');
			$channel=M('channel');
			$map = array();
			$map['status'] = 1;
			$map['create_at'] = time();
			$map['update_at'] = time();
			$map['admin_id'] = $_SESSION['admin']['admin_id'];
			$map['show_form'] = $_POST['show_form'];
			isset($_POST['extent_name']) && $map['extent_name'] = $_POST['extent_name'];
			if (empty($map['extent_name']))
				$this->error('区间名称不能为空');
			isset($_POST['filter_installed']) && $map['filter_installed'] = $_POST['filter_installed'];
			isset($_POST['depot_limit']) && $map['depot_limit'] = $_POST['depot_limit'];
			isset($_POST['type']) && $map['type'] = $_POST['type'];
			isset($_POST['oid']) && $map['oid'] = $_POST['oid'];
			if (!empty($_POST['cid']))
				$map['cid'] = ',' . implode(',', $_POST['cid']) . ',';
			else
				$map['cid'] = '';
			isset($_POST['extent_size']) && $map['extent_size'] = $_POST['extent_size'];
			if ($map['type'] == 1 && !is_numeric($_POST['extent_size']) )
			{
				$this->error('区间位置数必须为正整数');
			}
			isset($_POST['start_at']) && $map['start_at'] = strtotime($_POST['start_at']);
			isset($_POST['end_at']) && $map['end_at'] = strtotime($_POST['end_at']);
			!empty($_POST['parent_id']) && $map['parent_id'] = $_POST['parent_id'];
			!empty($_POST['pid']) && $map['pid'] = $_POST['pid'];
			//!empty($_POST['location']) && $map['location'] = $_POST['location'];

			//添加推送区域
			$pro_city=array();
	        $map['push_area'] = $_POST['area_value'];

			if(empty($_POST['cid'])){
				$zh_chname="全部可见";
			}else{
				$zh_chname=$channel->where(array("status"=>1,"cid"=>array('in', $_POST['cid'])))->field('chname')->select();
				$tmp = array();	
				foreach ($zh_chname as $v)
					$tmp[] = $v['chname'];
				$zh_chname = implode(',', $tmp);
			}
			if(empty($map['oid'])){
				$zh_oid_name="全部可见";
			}else{
				$zh_oid_name=$channel->table("pu_operating")->where(array("oid"=>$map['oid']))->getfield("mname");
			}
			$rank  = (int)$_POST['rank'];
			$pid  = $_POST['pid'];

			if ($map['type'] == 3)
			{
				$count = 0;
				$sub_extent = $_POST['sub_extent'];
				$flag = true;
				foreach ($sub_extent as $v)
				{
					if (!empty($v))
					{
						$count++;
						$sub = $model->where(array('extent_id' => $v))->find();
						if ($sub['parent_union_id'] != 0 || $sub['status'] == 0)
							$flag = false;
					}
				}
				if ($count < 2)
					$this->error('联合区间下属区间不能少于两个');
				if (!$flag)
					$this->error('操作冲突，请刷新后重新操作');
			}

			if ($id = $model->add($map)) {
				$where = array(
					'status' => 1,
					'pid' => $pid,
					'type' => array('NEQ', 3),
                    'extent_type' => array('NEQ', 4),
				);
				if(isset($_POST['parent_id'])){
					$where['parent_id'] = (int)$_POST['parent_id'];
				}else{
					$where['parent_id'] = 0;
				}
				//更新排序
				if ($map['type'] != 3)
					$this->_updateRankInfo('sj_extent_v1', 'rank', $id, $where, $rank);
				if (!empty($_POST['sub_extent']))
				{
					$sub_extent = $_POST['sub_extent'];
					foreach ($sub_extent as $v)
					{
						$where = array(
							'status' => 1,
							'pid' => $pid,
							'type' => 1,
							'extent_id' => $v,
						);
						$data = array(
							'parent_union_id' => $id,
						);
						$model->where($where)->save($data);
					}
				}
				//$this->assign('jumpUrl', '/index.php/Sj/ExtentV1/index/pid/'. $pid);
				$msg='市场软件运营推荐-市场首页软件列表:添加了id为'.$id.'的区间';
				$msg .="可见渠道为[{$zh_chname}] \n";
				$msg .="运营商为[{$zh_oid_name}]";
				$this->writelog($msg, 'sj_extent_v1',"{$id}",__ACTION__ ,"","add");
				$this->success('添加成功');
			}

		} else {
			$address_edit_able = true;
			if ($_GET['parent_id']) {
				$extent_model = M('extent_v1');
				$where = array(
					'id' => $_GET['parent_id']
				);

				$extent = $extent_model->where(array('extent_id' => $_GET['parent_id']))->find();
				//if (!empty($extent['location'])) {
				//	$address_edit_able = false;
				//	$this->assign('location',$extent['location']);
				//}
			}

			$channel_model = M('channel');
			$channels = $channel_model->field("`cid`,`chname`")->where(array('status' => 1))->select();
			$this->assign('channel_list', $channels);

			$operating_db = D('Sj.Operating');
			$operating_list = $operating_db->field('oid,mname')->select();
			$this->assign('operatinglist',$operating_list);
			!empty($_GET['parent_id']) && $this->assign('parent_id',$_GET['parent_id']);
			!empty($_GET['pid']) && $this->assign('pid',$_GET['pid']);

			$extent_model = M('extent_v1');
			$pid  = $_GET['pid'];
			$map = array(
				'status' => 1,
				'pid' => $pid,
				'parent_id' => isset($_GET['parent_id']) ? (int)$_GET['parent_id'] : 0,
				'type' => array('NEQ', 3),
                'extent_type' => array('NEQ', 4),
			);
			$count = $extent_model -> where($map)->count() + 1;
			//$model = M('extent_v1');
			$where = array(
				'status' => 1,
				'type' => 1,
				'parent_id' => 0,
				'parent_union_id' => 0,
			);
			$extent_list = $extent_model->where($where)->select();
			$extent_list_id = array();
			foreach ($extent_list as $v)
			{
				$extent_list_id[] = array(
					'id' => $v['extent_id'],
					'name' => $v['extent_name'],
				);
			}
			//var_dump('extent:', $extent_list_id);
			$this->assign('extent_list', $extent_list_id);
			$this->assign('count',$count);
			$this->assign('address_edit_able',$address_edit_able);
			$this->display();
		}
	}

    // 添加多软件位区间
    function add_multiSoftwareExtent() {
        if ($_POST) {
            $map = array();
            $map['status'] = 1;
			$map['create_at'] = time();
			$map['update_at'] = time();
			$map['admin_id'] = $_SESSION['admin']['admin_id'];
            $map['release_time'] = time();
            $map['extent_type'] = 4;
            $map['extent_name'] = trim($_POST['extent_name']);
            if (!$map['extent_name'])
                $this->error('区间名不能为空');
            $map['display_title'] = trim($_POST['display_title']);
            if (!$map['display_title'])
                $this->error('标题不能为空');
            $map['display_type'] = trim($_POST['display_type']);
            if ($map['display_type'] == 1) {
                $_POST['display_description'] = trim($_POST['display_description']);
                if (mb_strlen($_POST['display_description'], 'utf-8') < 30)
                    $this->error("文字描述不得少于30个字");
                $map['display_description'] = $_POST['display_description'];
                if (!$map['display_description'])
                    $this->error('描述不能为空');
            } else {
                // 将图片存储起来
                $folder = "/img/" . date("Ym/d/");
                $this->mkDirs(UPLOAD_PATH . $folder);
                // 取得图片后缀
                $suffix = preg_match("/\.(jpg|png)$/", $_FILES['display_image']['name'], $matches);
                if ($matches) {
                    $suffix = $matches[0];
                } else {
                    $this->error('上传图片格式错误！');
                }
                // 判断图片长和宽
                $img_info_arr = getimagesize($_FILES['display_image']['tmp_name']);
                if (!$img_info_arr) {
                    $this->error('上传图片出错！');
                }
                $width = $img_info_arr[0];
                $height = $img_info_arr[1];
                if ($width != $this->image_width_long_multi || $height != $this->image_height_long_multi)
                    $this->error("上传图片大小错误，宽需为{$this->image_width_long_multi}px，高需为{$this->image_height_long_multi}px");
                $relative_path = $folder . time() . '_' . rand(1000,9999) . $suffix;
                $img_path = UPLOAD_PATH . $relative_path;
                $ret = move_uploaded_file($_FILES['display_image']['tmp_name'], $img_path);
                $map['display_image'] = $relative_path;
            }
            isset($_POST['filter_installed']) && $map['filter_installed'] = $_POST['filter_installed'];
			isset($_POST['depot_limit']) && $map['depot_limit'] = $_POST['depot_limit'];
			$map['type'] = 1;
            isset($_POST['oid']) && $map['oid'] = $_POST['oid'];
            $map['cid'] = isset($_POST['cid']) ? $_POST['cid'] : 0;
            $map['extent_size'] = 1;
            isset($_POST['start_at']) && $map['start_at'] = strtotime($_POST['start_at']);
			isset($_POST['end_at']) && $map['end_at'] = strtotime($_POST['end_at']);
            $map['parent_id'] = 0;
            $map['pid'] = $_POST['pid'];
            $map['rank'] = 0;

            $model = M('extent_v1');
            // 检查是否当前渠道已经添加过多软件位
            $find_where = array(
                'extent_type' => $map['extent_type'],
                'pid' => $map['pid'],
                'status'=>1,
            );
            if (empty($map['cid']))
                $find_where['cid'] = array(array('EQ', 0), array('EQ', ''), array('EQ', null), 'OR');
            else
                $find_where['cid'] = array('EQ', $map['cid']);
            $find = $model->where($find_where)->find();
            if ($find) {
                $this->error('该渠道已添加过多软件位区间，不可重复添加');
            }

            $new_id = $model->add($map);
            if ($new_id) {
                $this->writelog("推荐位区间管理v1：添加id为【{$new_id}】多软件位区间",'sj_extent_v1',"{$new_id}",__ACTION__ ,"","add");
                $this->success('添加成功');
            } else {
                $this->error('添加失败');
            }

        } else {
            $operating_db = D('Sj.Operating');
            $operating_list = $operating_db->field('oid,mname')->select();
            $this->assign('operatinglist',$operating_list);
            $this->assign('pid',$_GET['pid']);
            $this->display('add_multiSoftwareExtent');
        }
    }

	function edit_extent()
	{
		$id = $_REQUEST['extent_id'];
		$where = array(
			'status'=>1,
			'extent_id' => $id
		);
		$model = M('extent_v1');
		$old_extent = $extent = $model->where($where)->find();
		if (empty($old_extent))
			$this->error('操作冲突，请刷新后重试');
		$pid = $extent['pid'];
		$area_list=explode(';',$extent['push_area']);
		$this->assign("push_area",$area_list);

		if (!empty($_POST)){
			if ($old_extent['type'] == 3)
			{
				$count = 0;
				$sub_extent = $_POST['sub_extent'];
				$flag = true;
				foreach ($sub_extent as $v)
				{
					if (!empty($v))
					{
						$count++;
						$sub = $model->where(array('extent_id' => $v))->find();
						if ($sub['parent_union_id'] != 0 && $sub['parent_union_id'] != $id || $sub['status'] == 0)
							$flag = false;
					}
				}
				if ($count < 2)
					$this->error('联合区间下属区间不能少于两个');
				if (!$flag)
					$this->error('操作冲突，请刷新后重新操作');
			}
			$channel=M('channel');
			if(!empty($old_extent['cid'])){
				$old_where['status']=1;
				$old_where['cid']=array('in', explode(',', $old_extent['cid']));
				$old_ch_chname=$channel->where($old_where)->field("chname")->select();
				$tmp = array();
                foreach ($old_ch_chname as $v)
                    $tmp[] = $v['chname'];
                $old_zh_chname = implode(',', $tmp);	
			}else{
				$old_zh_chname="全部可见";
			}
			if(empty($old_extent['oid'])){
				$old_oid_name="全部可见";
			}else{
				$old_oid_name=$channel->table("pu_operating")->where(array("oid"=>$old_extent['oid']))->getfield("mname");
			}
			$map = array();
			$map['update_at'] = time();
			$map['admin_id'] = $_SESSION['admin']['admin_id'];

			isset($_POST['extent_name']) && $map['extent_name'] = $_POST['extent_name'];
			if (isset($_POST['extent_name']) && empty($map['extent_name']))
				$this->error('区间名称不能为空');
			isset($_POST['filter_installed']) && $map['filter_installed'] = $_POST['filter_installed'];
			isset($_POST['depot_limit']) && $map['depot_limit'] = $_POST['depot_limit'];
			isset($_POST['oid']) && $map['oid'] = $_POST['oid'];
			if (!empty($_POST['cid']))
				$map['cid'] = ',' . implode(',', $_POST['cid']) . ',';
			else
				$map['cid'] = '';
			//isset($_POST['rank']) && $map['rank'] = $_POST['rank'];
			if (isset($_POST['extent_size']) && !is_numeric($_POST['extent_size']) )
			{
				$this->error('区间位置数必须为正整数');
			}
			else
			{
				$map['extent_size'] = $_POST['extent_size'];
			}
			isset($_POST['start_at']) && $map['start_at'] = strtotime($_POST['start_at']);
			isset($_POST['end_at']) && $map['end_at'] = strtotime($_POST['end_at']);
			//isset($_POST['location']) && $map['location'] = $_POST['location'];
			$where_rank = array(
				'status' => 1,
				'pid' => $pid,
			);
			if(isset($_POST['parent_id'])){
				$where_rank['parent_id'] = (int)$_POST['parent_id'];
			}else{
				$where_rank['parent_id'] = 0;
			}
			if(!empty($_POST['cid'])){
				$new_where['status']=1;
				$new_where['cid']=array('in', $_POST['cid']);
				$new_ch_chname=$channel->where($new_where)->field("chname")->select();
				$tmp = array();
                foreach ($new_ch_chname as $v)
                    $tmp[] = $v['chname'];
				$new_zh_chname = implode(',', $tmp);
			}else{
				$new_zh_chname="全部可见";
			}
			if(empty($map['oid'])){
				$new_oid_name="全部可见";
			}else{
				$new_oid_name=$channel->table("pu_operating")->where(array("oid"=>$map['oid']))->getfield("mname");
			}
			$where_rank['type'] = array('NEQ', 3);
            $where_rank['extent_type'] = array('NEQ', 4);
			$rank = (int)$_POST['rank'];
			//更新排序
			if ($old_extent['type'] != 3)
				$this->_updateRankInfo('sj_extent_v1', 'rank', $id, $where_rank, $rank);

				//推送区域
			$pro_city=array();
        	$map['push_area'] = $_POST['area_value'];
            //$model['push_area'] = trim(preg_replace('/(\d+|:)/', '', $map['push_area']));

			if ($model->where($where)->save($map)) {
				//if (isset($map['location']) && $map['location'] != $old_extent['location']) {
				//	$soft_where = array(
				//		'extent_id' => $id
				//	);
				//	$soft_data = array(
				//		'location' => '',
				//	);
				//	isset($map['location']) && $soft_data['location'] = $map['location'];
                //
				//	$model->table('sj_extent_soft_v1')->where($soft_where)->save($soft_data);
                //
				//	//活动区间会覆盖子区间的设置
				//	if ($old_extent['type'] == 2) {
				//		$extent_where = array(
				//			'parent_id' => $old_extent['extent_id']
				//		);
				//		$extent_data = $soft_data;
				//		$model->where($extent_where)->save($extent_data);
                //
				//		$extents = $model->where($extent_where)->select();
				//		if ($extents) {
				//			$extent_ids = array();
				//			foreach ($extents as $key => $value) {
				//				$extent_ids[] = $value['extent_id'];
				//			}
				//			$soft_where = array(
				//				'extent_id' => array('in', $extent_ids)
				//			);
				//			$model->table('sj_extent_soft_v1')->where($soft_where)->save($soft_data);
				//		}
				//	}
				//}

				if (!empty($_POST['sub_extent']))
				{
					$sub_extent = $_POST['sub_extent'];
					$model->where(array('parent_union_id' => $id))->save(array('parent_union_id' => 0));
					foreach ($sub_extent as $v)
					{
						$where = array(
							'status' => 1,
							'pid' => $pid,
							'type' => 1,
							'extent_id' => $v,
						);
						$data = array(
							'parent_union_id' => $id,
						);
						$model->where($where)->save($data);
					}
				}

				$configModel = D('Sj.Config');
				$column_desc = $configModel->getExtentColumnDesc();
				$msg = "市场软件运营推荐-市场首页软件列表:编辑了ID为[{$id}],名为[{$old_extent['extent_name']}]的区间\n";
				foreach ($map as $key => $val) {
					if (isset($column_desc[$key]) && $map[$key] != $old_extent[$key]) {
						$desc = $column_desc[$key];
						if($key=="cid"){
							$msg .= "将{$desc} 从'{$old_zh_chname}'修改成'{$new_zh_chname}'\n";
						}else{
							$msg .= "将{$desc} 从'{$old_oid_name}'修改成 '{$new_oid_name}'\n";
						}

					}
				}
				$this->writelog($msg,'sj_extent_v1',"{$id}",__ACTION__ ,"","edit");
				//$this->assign('jumpUrl', '/index.php/Sj/ExtentV1/index/pid/'.$pid);
				$this->success('编辑成功');
			}
		} else {
			$address_edit_able = true;
			if ($old_extent['parent_id']) {
				$where = array(
					'id' => $old_extent['parent_id']
				);

				$pextent = $model->where(array('extent_id' => $old_extent['parent_id']))->find();
				//if (!empty($pextent['location'])) {
				//	$address_edit_able = false;
				//	$this->assign('location',$pextent['location']);
				//}
			}
			$channel_model = M('channel');
			$channels = $channel_model->field("`cid`,`chname`")->where(array('status' => 1))->select();
			$this->assign('channel_list', $channels);

			$operating_db = D('Sj.Operating');
			$operating_list = $operating_db->field('oid,mname')->select();
			$this->assign('operatinglist',$operating_list);
			!empty($_GET['parent_id']) && $this->assign('parent_id',$_GET['parent_id']);


			$condition = array(
				'status' => 1,
				'pid' => $pid,
				'parent_id' => isset($_GET['parent_id']) ? (int)$_GET['parent_id'] : 0,
				'type' => array('NEQ', 3),
                'extent_type' => array('NEQ', 4),
			);

			$count = $model -> where($condition)->count();
			$this->assign('count',$count);

			if(!empty($extent['cid'])){
				$cid_array = array();
				$cid = trim($extent['cid'], ',');
				if (!empty($cid)) {
					$cid = explode(',', $cid);
					foreach ($cid as $v) {
						$tmp=array();
						$tmp['cid'] = $v;
						$tmp['chname'] = $channel_model->where(array('status' => 1,'cid'=>$v))->getfield("chname");
						$cid_array[] = $tmp;
					}
					$this->assign("cid_array",$cid_array);
				}
			}

			if ($extent['type'] == 3)
			{
				$where = array(
					'status' => 1,
					'type' => 1,
					'parent_id' => 0,
					'parent_union_id' => array('EXP', "IN (0, {$extent['extent_id']})"),
                    'extent_type' => array('NEQ', 4),// 多软件位不能被联合
                    'pid' => $pid, // fix: 需在当前平台下的普通区间才能被联合
				);
				$extent_list = $model->where($where)->select();
				$extent_list_id = array();
				foreach ($extent_list as $v)
				{
					$extent_list_id[] = array(
						'id' => $v['extent_id'],
						'name' => $v['extent_name'],
					);
				}
				$this->assign('extent_list', $extent_list_id);

				$where = array(
					'status' => 1,
					'parent_union_id' => $extent['extent_id'],
				);
				$sub_extent = $model->where($where)->select();
				$this->assign('sub_extent', $sub_extent);
			}

			$this->assign('extent', $extent);
			$this->assign('address_edit_able',$address_edit_able);

			$this->display();
		}
	}

    function edit_multiSoftwareExtent() {
        $id = $_REQUEST['extent_id'];
		$where = array(
			'status'=>1,
			'extent_id' => $id
		);
		$model = M('extent_v1');
		$old_extent = $extent = $model->where($where)->find();
		if (empty($old_extent))
			$this->error('操作冲突，请刷新后重试');
        if ($_POST) {
            $map = array();
			$map['update_at'] = time();
			$map['admin_id'] = $_SESSION['admin']['admin_id'];
            $map['extent_name'] = trim($_POST['extent_name']);
            if (!$map['extent_name'])
                $this->error('区间名不能为空');
            $map['display_title'] = trim($_POST['display_title']);
            if (!$map['display_title'])
                $this->error('标题不能为空');
            $map['display_type'] = trim($_POST['display_type']);
            if ($map['display_type'] == 1) {
                $_POST['display_description'] = trim($_POST['display_description']);
                if (mb_strlen($_POST['display_description'], 'utf-8') < 30)
                    $this->error("文字描述不得少于30个字");
                $map['display_description'] = $_POST['display_description'];
                if (!$map['display_description'])
                    $this->error('描述不能为空');
                // 图片清空
                $map['display_image'] = '';
            } else {
                if ($_FILES['display_image']['name']) {
                    // 将图片存储起来
                    $folder = "/img/" . date("Ym/d/");
                    $this->mkDirs(UPLOAD_PATH . $folder);
                    // 取得图片后缀
                    $suffix = preg_match("/\.(jpg|png)$/", $_FILES['display_image']['name'], $matches);
                    if ($matches) {
                        $suffix = $matches[0];
                    } else {
                        $this->error('上传图片格式错误！');
                    }
                    // 判断图片长和宽
                    $img_info_arr = getimagesize($_FILES['display_image']['tmp_name']);
                    if (!$img_info_arr) {
                        $this->error('上传图片出错！');
                    }
                    $width = $img_info_arr[0];
                    $height = $img_info_arr[1];
                    if ($width != $this->image_width_long_multi || $height != $this->image_height_long_multi)
                        $this->error("上传图片大小错误，宽需为{$this->image_width_long_multi}px，高需为{$this->image_height_long_multi}px");
                    $relative_path = $folder . time() . '_' . rand(1000,9999) . $suffix;
                    $img_path = UPLOAD_PATH . $relative_path;
                    $ret = move_uploaded_file($_FILES['display_image']['tmp_name'], $img_path);
                    $map['display_image'] = $relative_path;
                    // 描述清空
                    $map['display_description'] = '';
                }
            }
            isset($_POST['filter_installed']) && $map['filter_installed'] = $_POST['filter_installed'];
			isset($_POST['depot_limit']) && $map['depot_limit'] = $_POST['depot_limit'];
            isset($_POST['oid']) && $map['oid'] = $_POST['oid'];
            $map['cid'] = isset($_POST['cid']) ? $_POST['cid'] : 0;
            isset($_POST['start_at']) && $map['start_at'] = strtotime($_POST['start_at']);
			isset($_POST['end_at']) && $map['end_at'] = strtotime($_POST['end_at']);

            $model = M('extent_v1');
            // 检查是否当前渠道已经添加过多软件位
            $find_where = array(
                'extent_id' => array('NEQ', $id),
                'extent_type'=> $extent['extent_type'],
                'pid' => $extent['pid'],
                'status'=> 1,
            );
            if (empty($map['cid']))
                $find_where['cid'] = array(array('EQ', 0), array('EQ', ''), array('EQ', null), 'OR');
            else
                $find_where['cid'] = array('EQ', $map['cid']);
            $find = $model->where($find_where)->find();
            if ($find) {
                $this->error('该渠道已添加过多软件位区间，不可重复添加');
            }
            $where = array('extent_id'=>$id);
            $log_msg = $this->logcheck($where, 'sj_extent_v1', $map, $model);
            $ret = $model->where($where)->save($map);
            if ($ret || $ret === 0) {
                if ($ret !== 0) {
                    $this->writelog('推荐位区间管理v1_编辑多软件位区间：' .$log_msg,'sj_extent_v1',"{$id}",__ACTION__ ,"","edit");
                }
                $this->success('编辑成功');
            } else {
                $this->error('编辑失败');
            }

        } else {
            $operating_db = D('Sj.Operating');
            $operating_list = $operating_db->field('oid,mname')->select();

            if (!empty($extent['cid'])) {
				$where = array(
					'cid' => $extent['cid']
				);
				$channel = $model -> table('sj_channel') -> where($where)->find();
				$this->assign('chname',$channel['chname']);
			}

            $this->assign('operatinglist',$operating_list);
            $this->assign('pid',$_GET['pid']);
            $this->assign('extent', $extent);
            $this->display();
        }
    }

    function release_extent() {
        $extent_id = $_REQUEST['extent_id'];
		$where = array(
			'extent_id' => $extent_id
		);
        $now = time();
		$map = array(
			'release_time' => $now,
		);
		$model = M('extent_v1');
		$model->where($where)->save($map);
        $this->writelog("推荐位区间管理v1：发布id为【{$extent_id}】的区间",'sj_extent_v1',"{$extent_id}",__ACTION__ ,"","edit");
        $this->success('发布成功');
    }

	//更新某个排序
	function edit_rank(){
		if(isset($_GET)){
			$table       = 'sj_extent_v1';
			$field       = 'rank';
			$where       = '`status` = 1';
			$extent_id   = (int)$_GET['extent_id'];
			$parent_id   = (int)$_GET['parent_id'];
			$pid   = (int)$_GET['pid'];
			$target_rank = (int)$_GET['rank'];
			$lr          = isset($_GET['lr']) ? (int)$_GET['lr'] : 20;
			$p           = isset($_GET['p'])  ? (int)$_GET['p']  : 1;

			$where_rank = array(
				'status' => 1,
				'pid' => $pid,
			);
			if(isset($parent_id)){
				$where_rank['parent_id'] = (int)$_POST['parent_id'];
			}else{
				$where_rank['parent_id'] = 0;
			}
			//更新排序
			$where_rank['type'] = array('NEQ', 3);
            $where_rank['extent_type'] = array('NEQ', 4);
			$param = $this->_updateRankInfo($table,$field,$extent_id, $where_rank, $target_rank,$lr,$p);
			$this -> writelog('在新版推荐位中更新了extent_id为'.$extent_id.'的区间', 'sj_extent_v1', $extent_id,__ACTION__ ,"","edit");
			exit(json_encode($param));
		}
	}

	//批量更新排序
	function batch_rank(){
		if(isset($_GET)){
			$model = M('extent_v1');
			$ids   = (string)$_GET['id'];
			$pid   = (string)$_GET['pid'];
			$ranks = (string)$_GET['rank'];
			$ids   = substr($ids,0,strlen($ids)-1);
			$ranks = substr($ranks,0,strlen($ranks)-1);

			$extent_list = array();
			$allids   = explode(",",$ids);
			$allranks = explode(",", $ranks);

			$extent_list = array_combine($allids,$allranks);
			foreach($extent_list as $id => $rank){
				$model -> query("UPDATE __TABLE__ set rank = ".$rank." WHERE status = 1 AND extent_id = " .$id);
			}

			$this->writelog('在新版推荐位中批量更新了extent_id为'.$ids.'的排序',__TABLE__, $ids,__ACTION__ ,"","edit");
			//$this->assign('jumpUrl','/index.php/Sj/ExtentV1/index/pid/'.$pid);
			$this->success('批量更新成功');
		}
	}

	function add_soft()
	{
		$id = $_REQUEST['id'];
		$show_form = $_REQUEST['show_form']?$_REQUEST['show_form']:0;
		$where = array(
			'id' => $id
		);

		$model = M('extent_soft_v1');

		$extent = $model->table('sj_extent_v1')->where(array('extent_id' => $_REQUEST['extent_id']))->find();
		$address_edit_able = true;
		//if (!empty($extent['location'])) {
		//	$address_edit_able = false;
		//}
		if (!empty($_POST)){
            // tpl（网页）里的名称和数据库字段对应数组
            $column_convert_arr = array(
                'extent_id' => 'extent_id',
                'package_ext' => 'package',
                'phone_dis' => 'phone_dis',
                'old_phone' => 'old_phone',
                'prob' => 'prob',
                'start_at' => 'start_at',
                'end_at' => 'end_at',
                'type' => 'type',
                'beid' => 'beid',
            );
            // $check_column_arr数组存放_POST/_GET对应数据库字段的值（因为logic_check里的变量名跟数据库字段名一样）
            $check_column_arr = array();
            foreach($column_convert_arr as $key=>$value) {
                if (array_key_exists($key, $_POST)) {
                    $check_column_arr[$value] = $_POST[$key];
                }
            }
            // trim一下
            foreach($check_column_arr as $key=>$value) {
                $check_column_arr[$key] = trim($value);
            }
            // 调用通用的检查函数
            $content_arr = array();
            $content_arr[0] = $check_column_arr;
            $error_msg = $this->logic_check($content_arr);
            $qualified_flag = true;
            foreach($error_msg as $key=>$value) {
                if ($value['flag'] == 1)
                    $qualified_flag = false;
            }
            if (!$qualified_flag) {
                $msg = $error_msg[0]['msg'];
                // 业务逻辑：设置返回的跳转页面
                $this->assign('jumpUrl', '/index.php/Sj/ExtentV1/list_soft/extent_id/'. $_POST['extent_id'].'/show_form/'.$show_form);
                $this->error($msg);
            }

			$map = array();
			$map['status'] = 1;
			$map['create_at'] = time();
			$map['update_at'] = time();
            $map['admin_id'] = $_SESSION['admin']['admin_id'];

			isset($_POST['extent_id']) && $map['extent_id'] = $_POST['extent_id'];
			isset($_POST['package_ext']) && $map['package'] = trim($_POST['package_ext']);
			isset($_POST['prob']) && $map['prob'] = $_POST['prob'];
			isset($_POST['start_at']) && $map['start_at'] = strtotime($_POST['start_at']);
			isset($_POST['end_at']) && $map['end_at'] = strtotime($_POST['end_at']);
			isset($_POST['type']) && $map['type'] = $_POST['type'];
			isset($_POST['phone_dis']) && $map['phone_dis'] = $_POST['phone_dis'];
			if(isset($_POST['old_phone'])){
				$map['old_phone'] = $_POST['old_phone'];
			}else{
				$map['old_phone'] = 0;
			}

            isset($_POST['default_display']) && $map['default_display'] = $_POST['default_display'];
            //添加行为id  added by shiting
            isset($_POST['beid']) && $map['beid'] = $_POST['beid'];

			//if ($address_edit_able) {
			//	isset($_POST['location']) && $map['location'] = $_POST['location'];
			//} else {
			//	$map['location'] = $extent['location'];
			//}
			if($map['package']){
				//屏蔽软件上排期时报警需求 新增  yuesai
		        $AdSearch = D("Sj.AdSearch");
		        $shield_error=$AdSearch->check_shield($map['package'],$map['start_at'],$map['end_at']);
		        $shield_error.=$AdSearch->check_shield_old($map['package'],0,1);
				if($shield_error){
					$this -> error($shield_error);
				}
			}
			//6.4.4
			if( $show_form == 1 ) {
				$content_title = $_POST['content_title']?trim($_POST['content_title']):"";
				$resource_id = $_POST['resource_id']?$_POST['resource_id']:0;
				$is_dev = $_POST['is_dev']?$_POST['is_dev']:0;
				$is_tag = $_POST['is_tag']?$_POST['is_tag']:0;
				if(!empty($resource_id)){
					$map['resource_id'] = $resource_id;
				}else{
					$map['content_title'] = $content_title;
					$map['is_dev'] = $is_dev;
					$content_type_one = (int)$_POST['content_type']['one'];
					if( !$content_title ) {
						$this->error('请填写内容标题');
					}
					//不是开发者则需要传图
					if( !$is_dev ) {
						if( empty($_FILES['img']['tmp_name']) ) {
							$this->error('请上传图片');
						}
					}
					if( !$content_type_one ) {
						$this->error('请填写推荐内容');
					}
					if($is_dev && $content_type_one !=9 ) {
						$this->error('选择开发者的时候，推荐内容必须为应该内览');
					}
					$width = 464; $height = 274;
					$date	=	date("Ym/d/");
					if($_FILES['img']['tmp_name']) {
						$pic_path = getimagesize($_FILES['img']['tmp_name']);
						if($pic_path[0] != $width || $pic_path[1] != $height){
							$this->error("分辨率图标大小不符合条件");
						}
						if( !in_array($_FILES['img']['type'], array('image/png','image/jpg','image/jpeg')) ) {
							$this->error("请添加图片格式为：jpg，png的弹窗图片");
						}
						$config['multi_config']['img'] = array(
								'savepath'	 =>	UPLOAD_PATH. '/img/'. $date,
								'saveRule'	 =>	'getmsec',
								'img_p_size' =>	1024 * 200,
						);
					}
					if (!empty($config['multi_config'])) {
						$list = $this->_uploadapk(0, $config);
						foreach($list['image'] as $val) {
							$map[$val['post_name']] = $val['url'];
						}
					}
				}
				
				if( $is_tag == 1 ) {
					$tag_title = $_POST['tag_title']?trim($_POST['tag_title']):'';
					$content_type_two = (int)$_POST['content_type']['two'];
					if( !$tag_title ) {
						$this->error('标签标题不能为空');
					}
					if( strlen($tag_title) > 30 ) {
						$this->error("标签标题字数不能超过10个汉字");
					}
					if( !$content_type_two ) {
						$this->error('请填写推荐内容');
					}
				}
				$map['is_tag'] = $is_tag;
				$map['tag_title'] = $tag_title;
				
				if(empty($resource_id)){
					$conetnt_map_1 = array();
					$rcontent_1 = ContentTypeModel::saveRecommendContent_new($_POST,'',$conetnt_map_1, 'one');
					if($rcontent_1==true)
					{
						$conetnt_map_1['create_at'] = time();
						$conetnt_map_1['update_at'] = time();
						$conetnt_id_1 = M('')->table('sj_common_jump')->add($conetnt_map_1);
						$map['content_id_1'] = $conetnt_id_1;
					}else {
						$this -> error($rcontent_1);
					}
				}
				//展示标签的情况
				if( $is_tag == 1 ) {
					$conetnt_map_2 = array();
					$rcontent_2 = ContentTypeModel::saveRecommendContent_new($_POST,'',$conetnt_map_2, 'two');
					if($rcontent_2==true)
					{
						$conetnt_map_2['create_at'] = time();
						$conetnt_map_2['update_at'] = time();
						$conetnt_id_2 = M('')->table('sj_common_jump')->add($conetnt_map_2);
						$map['content_id_2'] = $conetnt_id_2;
					}else {
						$this -> error($rcontent_2);
					}
				}
			}
			if ($id = $model->add($map)) {
				$this->assign('jumpUrl', '/index.php/Sj/ExtentV1/list_soft/extent_id/'.$_POST['extent_id'].'/show_form/'.$show_form);
				$this->writelog("市场软件运营推荐-市场首页软件列表:在区间ID为[{$_POST['extent_id']}]中添加了软件[{$_POST['package_ext']}],显示概率为{$_POST['prob']},开始时间为{$_POST['start_at']},结束时间为{$_POST['end_at']},合作方式为{$_POST['type']},", 'sj_extent_soft_v1',$id,__ACTION__ ,"","add");
                // 检查导入的区间中有没有软件数大于区间位置数的，有的话要发邮件提醒运营
                $check_extent = array(
                    $_POST['extent_id'] => array(
                        'start_at' => $map['start_at'],
                        'end_at' => $map['end_at']
                    )
                );
                $this->send_size_notice_email($check_extent);
				$this->success('添加成功');
			} else {
				//$this->assign('jumpUrl', '/index.php/Sj/ExtentV1/index');
				$this->error('添加失败');
			}
		} else {
			$util = D("Sj.Util");
			$typelist = $util->getHomeExtentSoftTypeList();
			$this->assign('typelist',$typelist);

			$this->assign('extent_id',$_GET['extent_id']);
            // 根据extent_id查找extent_type
            $model = M();
            $where = array('extent_id' => $_GET['extent_id'], 'status' => 1);
            $find = $model->table('sj_extent_v1')->where($where)->find();
            $extent_type = 1;
            if ($find) {
                $extent_type = $find['extent_type'];
            }
            $this->assign('extent_type',$extent_type);

            $this->assign('show_form',$show_form);
			$this->assign('address_edit_able',$address_edit_able);
			$this->display();
		}
	}

	function list_area_soft()
	{
		$util = D("Sj.Util");
		$pid = isset($_GET['pid']) ? $_GET['pid'] : 1;
		$model = M('extent_soft_v1');
		$extent_where = array(
			'pid' => $pid,
			'status' => 1
		);
		$extents = $model->table('sj_extent_v1')->where($extent_where)->select();
		$extent_ids = array();
		foreach ($extents as $value) {
			$extent_ids[] = $value['extent_id'];
		}
		$srch_type = $_GET['srch_type'];
		$where = array(
			'extent_id' => array('IN', $extent_ids),
			'status' => 1
		);
		//if (empty($_GET['city']) && empty($_GET['province'])) {
		//	$_complex = array(
		//		'location' => array('NEQ', ''),
		//		'_logic' => 'or'
		//	);
		//	$where['_complex'] = $_complex;
		//} else {
		//	if (isset($_GET['province'])){
		//		$where['location'] = array('LIKE', '%'. escape_string($_GET['province']). '%');
		//		$this->assign('province', $_GET['province']);
		//	}
		//	if (isset($_GET['city'])){
		//		$where['location'] = array('LIKE', '%'. escape_string($_GET['city']). '%');
		//		$this->assign('city', $_GET['city']);
		//	}
		//}
		$now = time();
		switch($srch_type) {
			case 'e':
				$where['end_at'] = array('elt',$now);
			break;

			case 'f':
				$where['start_at'] = array('egt',$now);
			break;

			case 'n':
			default:
				$where['start_at'] = array('elt',$now);
				$where['end_at'] = array('egt',$now);
				$srch_type = 'n';
			break;
		}

		$count = $model->where($where)->count();

		import("@.ORG.Page");
		$p = new Page ( $count, 15 );
		$list = $model->limit($p->firstRow.','.$p->listRows)->where($where)->findAll();
		$page = $p->show ();

		$package = array();
		$result_package = array();
		foreach($list as $val) {
			$package[] = $val['package'];
			$result_package[$val['package']] = array();
		}
		$soft = $model->table('sj_soft')->where(array('package' => array('in', $package)))->field('softname,softid,package')->group('package')->select();
		foreach($soft as $val) {
			$result_package[$val['package']]['softname'] = $val['softname'];
			$result_package[$val['package']]['softid'] = $val['softid'];
		}
		$result = array();
		foreach($list as $val) {
			$val['softname'] = $result_package[$val['package']]['softname'];
			$val['softid'] = $result_package[$val['package']]['softid'];
			$result[] = $val;
		}

		$where = array(
			'status' => 1
		);
		$extent_result = $extents = $model->table('sj_extent_v1')->where($where)->order('parent_id asc, rank asc, type desc')->select();
		$extent_list = array();
		foreach($extent_result as $v){
			$extent_list[$v['extent_id']] = $v;
		}

		$extent_select = array();
		foreach($extent_result as $v) {
			if ($v['type'] != 2) {
				if($v['parent_id'] > 0) {
					$extent_select[$v['extent_id']] = $extent_list[$v['parent_id']]['extent_name'] . ' > ' . $v['extent_name'];
				} else {
					$extent_select[$v['extent_id']] = $v['extent_name'];
				}
			}
			if ($v['extent_id'] == $extent_id) {
				$this->assign('pid', $v['pid']);
			}
		}
		$this->assign('extent_name', $extent_select[$extent_id]);
		$this->assign('srch_type', $srch_type);
		$this->assign('extent_id', $extent_id);
		$this->assign('list', $result);
		$this->assign('extent_select', $extent_select);
		$this->assign('extents', $extents);
		$this->assign('isAjax', $this->isAjax());
		$this->assign('page', $page);
		$this->assign('pid', $pid);
		$this->assign('product_list',$util->getProducts($pid));
		$this->display();
	}

	function list_soft()
	{
		$model = M('extent_soft_v1');
		$extent_id = $_GET['extent_id'];
		$srch_type = $_GET['srch_type'];
		$show_form = $_GET['show_form'];
		$where = array(
			'extent_id' => $extent_id,
			'status' => 1
		);
		$now = time();
		switch($srch_type) {
			case 'e':
				$where['end_at'] = array('elt',$now);
			break;

			case 'f':
				$where['start_at'] = array('egt',$now);
			break;

			case 'n':
			default:
				$where['start_at'] = array('elt',$now);
				$where['end_at'] = array('egt',$now);
				$srch_type = 'n';
			break;
		}
		$count = $model->where($where)->count();
		import("@.ORG.Page");
		$p = new Page ( $count, 15 );
		$list = $model->limit($p->firstRow.','.$p->listRows)->where($where)->order('start_at asc')->select();
		$page = $p->show();
		
		$package = array();
		$result_package = array();
		foreach($list as $val) {
			$package[] = $val['package'];
			$result_package[$val['package']] = array();
		}
		$soft = $model->table('sj_soft')->where(array('package' => array('in', $package)))->field('softname,softid,package')->group('package')->select();
		foreach($soft as $val) {
			$result_package[$val['package']]['softname'] = $val['softname'];
			$result_package[$val['package']]['softid'] = $val['softid'];
		}
		$result = array();
		$util = D("Sj.Util");
		$result = array();
		foreach($list as $key=>$val) {
			$val['softname'] = $result_package[$val['package']]['softname'];
			$val['softid'] = $result_package[$val['package']]['softid'];
			$typelist = $util->getHomeExtentSoftTypeList($val['type']);
			foreach($typelist as $k => $v){
				if($v[1] == true){
					$val['types'] = $v[0];
				}
			}
		$val['srch_type']=$_GET['srch_type'];
		$result[] = $val;
		}

		$extent = $model->table('sj_extent_v1')->where(array('extent_id' => $extent_id))->find();
		$pid = $extent['pid'];
		$type = $extent['type'];
        $extent_type = $extent['extent_type'];
		$this->assign('pid', $pid);
		$this->assign('type', $type);
        $this->assign('extent_type', $extent_type);
		$where = array(
			'status' => 1,
			'pid' => $pid
		);

		$extent_result = $extents = $model->table('sj_extent_v1')->where($where)->order('parent_id asc, rank asc, type desc')->select();
		$extent_list = array();
		foreach($extent_result as $v){
			$extent_list[$v['extent_id']] = $v;
		}

		$extent_select = array();
		foreach($extent_result as $v) {
			if ($v['type'] != 2 && $v['parent_union_id'] == 0) {
				if($v['parent_id'] > 0) {
					$extent_select[$v['extent_id']] = $extent_list[$v['parent_id']]['extent_name'] . ' > ' . $v['extent_name'];
				} else {
					$extent_select[$v['extent_id']] = $v['extent_name'];
				}
			}
		}
		
		$this->assign('show_form', $show_form);
		$this->assign('extent_name', $extent_select[$extent_id]);
		$this->assign('srch_type', $srch_type);
		$this->assign('extent_id', $extent_id);
		$this->assign('list', $result);
		$this->assign('extent_select', $extent_select);
		$this->assign('extents', $extents);
		$this->assign('page', $page);
		$this->assign('isAjax', $this->isAjax());
		$this->display();
	}

	function edit_soft()
	{
		$id = $_REQUEST['id'];
		$show_form = $_REQUEST['show_form'];
		$where = array(
			'id' => $id
		);
		$model = M('extent_soft_v1');
		$soft = $model->where($where)->find();

		$extent = $model->table('sj_extent_v1')->where(array('extent_id' => $soft['extent_id']))->find();
        $extent_type = $extent['extent_type'];
        $this->assign('extent_type', $extent_type);
		$address_edit_able = false;
		//if (empty($extent['location'])) {
		//	$address_edit_able = true;
		//}

		if (!empty($_POST)){
            // tpl（网页）里的名称和数据库字段对应数组
            $column_convert_arr = array(
                'id' => 'id',
                'package_ext' => 'package',
                'phone_dis' => 'phone_dis',
                'old_phone' => 'old_phone',
                'prob' => 'prob',
                'start_at' => 'start_at',
                'end_at' => 'end_at',
                'type' => 'type',
				'life'=>'life',
				'beid' => 'beid',
            );
            // $check_column_arr数组存放_POST/_GET对应数据库字段的值（因为logic_check里的变量名跟数据库字段名一样）
            $check_column_arr = array();
            foreach($column_convert_arr as $key=>$value) {
                if (array_key_exists($key, $_POST)) {
                    $check_column_arr[$value] = $_POST[$key];
                }
            }
            // trim一下
            foreach($check_column_arr as $key=>$value) {
                $check_column_arr[$key] = trim($value);
            }
            // 调用通用的检查函数
            $content_arr = array();
            $content_arr[0] = $check_column_arr;
            $error_msg = $this->logic_check($content_arr);
            $qualified_flag = true;
            foreach($error_msg as $key=>$value) {
                if ($value['flag'] == 1)
                    $qualified_flag = false;
            }
            if (!$qualified_flag) {
                $msg = $error_msg[0]['msg'];
                // 业务逻辑：设置返回的跳转页面
                $this->assign('jumpUrl', '/index.php/Sj/ExtentV1/list_soft/extent_id/'. $_POST['extent_id'].'/show_form/'.$show_form);
                $this->error($msg);
            }

			$map = array();
			$map['update_at'] = time();
			$map['admin_id'] = $_SESSION['admin']['admin_id'];
			isset($_POST['package_ext']) && $map['package'] = trim($_POST['package_ext']);
			isset($_POST['prob']) && $map['prob'] = $_POST['prob'];
            $map['default_display'] = $_POST['default_display'] ? $_POST['default_display'] : 0;
			isset($_POST['start_at']) && $map['start_at'] = strtotime($_POST['start_at']);
			isset($_POST['end_at']) && $map['end_at'] = strtotime($_POST['end_at']);
			isset($_POST['type']) && $map['type'] = $_POST['type'];
			isset($_POST['phone_dis']) && $map['phone_dis'] = $_POST['phone_dis'];
			if(isset($_POST['old_phone'])){
				$map['old_phone'] = $_POST['old_phone'];
			}else{
				$map['old_phone'] = 0;
			}
			//if ($address_edit_able) {
			//	isset($_POST['location']) && $map['location'] = $_POST['location'];
			//}
			//已过期的信息复制上线判断
			if($_POST['life']==1)
			{
			  if(strtotime($_POST['end_at'])<time())
			  {
				$this->error("您修改的复制上线的日期还是无效日期");
			  }
			}
			 //添加行为id  added by shiting
            isset($_POST['beid']) && $map['beid'] = $_POST['beid'];

			$log_msg = $this->logcheck($where, 'sj_extent_soft_v1', $map, $model);
			if($map['package']){
				//屏蔽软件上排期时报警需求 新增  yuesai
		        $AdSearch = D("Sj.AdSearch");
		        $shield_error=$AdSearch->check_shield($map['package'],$map['start_at'],$map['end_at']);
		        $shield_error.=$AdSearch->check_shield_old($map['package'],0,1);
				if($shield_error){
					$this -> error($shield_error);
				}
			}
			
			//已过期的信息复制上线 添加
			if($_POST['life']==1)
			{
			    $select=$model->where($where)->select();
				$map['status'] = 1;
				$map['create_at'] = time();
				$map['extent_id'] =$select[0]['extent_id'];
				$ret=$model->table('sj_extent_soft_v1')->add($map);
			   if ($ret) 
				{
					$this->assign('jumpUrl', '/index.php/Sj/ExtentV1/list_soft/extent_id/'. $soft['extent_id'].'/show_form/'.$show_form);
					//$this->writelog("推荐位区间管理v1：{$log_msg}");
					$this->writelog("在新版推荐位中复制上线了软件[".$_POST['package_ext']."]".$log_msg, "",$_POST['package_ext'], 'sj_extent_soft_v1',$ret,__ACTION__ ,"","add");
					// 检查导入的区间中有没有软件数大于区间位置数的，有的话要发邮件提醒运营
					$check_extent = array(
						$_POST['extent_id'] => array(
							'start_at' => $map['start_at'],
							'end_at' => $map['end_at']
						)
					);
					$this->send_size_notice_email($check_extent);
					$this->success('复制上线成功');
				}
			}else {
				//6.4.4
				if( $show_form == 1 ) {
					$content_title = $_POST['content_title']?trim($_POST['content_title']):"";
					$resource_id = $_POST['resource_id']?$_POST['resource_id']:0;
					$is_dev = $_POST['is_dev']?$_POST['is_dev']:0;
					$is_tag = $_POST['is_tag']?$_POST['is_tag']:0;
					$content_type_one = (int)$_POST['content_type']['one'];
					if(!empty($resource_id)){
						$map['resource_id'] = $resource_id;
					}else{
						$map['content_title'] = $content_title;
						$map['is_dev'] = $is_dev;
						if( !$content_title ) {
							$this->error('请填写内容标题');
						}
						//不是开发者则需要传图
						if( !$is_dev ) {
							if( !$soft['img'] && empty($_FILES['img']['tmp_name']) ) {
								$this->error('请上传图片');
							}
						}
						if( !$content_type_one ) {
							$this->error('请填写推荐内容');
						}
						if($is_dev && $content_type_one !=9 ) {
							$this->error('选择开发者的时候，推荐内容必须为应该内览');
						}
						$width = 464; $height = 274;
						$date	=	date("Ym/d/");
						if($_FILES['img']['tmp_name']) {
							$pic_path = getimagesize($_FILES['img']['tmp_name']);
							if($pic_path[0] != $width || $pic_path[1] != $height){
								$this->error("分辨率图标大小不符合条件");
							}
							if( !in_array($_FILES['img']['type'], array('image/png','image/jpg','image/jpeg')) ) {
								$this->error("请添加图片格式为：jpg，png的弹窗图片");
							}
							$config['multi_config']['img'] = array(
									'savepath'	 =>	UPLOAD_PATH. '/img/'. $date,
									'saveRule'	 =>	'getmsec',
									'img_p_size' =>	1024 * 200,
							);
						}
						if (!empty($config['multi_config'])) {
							$list = $this->_uploadapk(0, $config);
							foreach($list['image'] as $val) {
								$map[$val['post_name']] = $val['url'];
							}
						}
					}
					if( $is_tag == 1 ) {
						$tag_title = $_POST['tag_title']?trim($_POST['tag_title']):'';
						$content_type_two = (int)$_POST['content_type']['two'];
						if( !$tag_title ) {
							$this->error('标签标题不能为空');
						}
						if( strlen($tag_title) > 30 ) {
							$this->error("标签标题字数不能超过10个汉字");
						}
						if( !$content_type_two ) {
							$this->error('请填写推荐内容');
						}
					}
					$map['is_tag'] = $is_tag;
					$map['tag_title'] = $tag_title;
				
					if(empty($resource_id)){
						$conetnt_map_1 = array();
						$rcontent_1 = ContentTypeModel::saveRecommendContent_new($_POST,'',$conetnt_map_1, 'one');
						if($rcontent_1==true)
						{
							$conetnt_map_1['create_at'] = time();
							$conetnt_map_1['update_at'] = time();
							$conetnt_id_1 = M('')->table('sj_common_jump')->add($conetnt_map_1);
							$map['content_id_1'] = $conetnt_id_1;
						}else {
							$this -> error($rcontent_1);
						}
					}
					//展示标签的情况
					if( $is_tag == 1 ) {
						$conetnt_map_2 = array();
						$rcontent_2 = ContentTypeModel::saveRecommendContent_new($_POST,'',$conetnt_map_2, 'two');
						if($rcontent_2==true)
						{
							$conetnt_map_2['create_at'] = time();
							$conetnt_map_2['update_at'] = time();
							$conetnt_id_2 = M('')->table('sj_common_jump')->add($conetnt_map_2);
							$map['content_id_2'] = $conetnt_id_2;
						}else {
							$this -> error($rcontent_2);
						}
					}
				}
				if ($model->where($where)->save($map)) 
				{
					$this->assign('jumpUrl', '/index.php/Sj/ExtentV1/list_soft/extent_id/'. $soft['extent_id'].'/show_form/'.$show_form);
					//$this->writelog("推荐位区间管理v1：{$log_msg}");
					$this->writelog("市场软件运营推荐- 市场首页软件列表:编辑了软件[".$_POST['package']."]".$log_msg,'sj_extent_soft_v1',$id,__ACTION__ ,"","edit");
					// 检查导入的区间中有没有软件数大于区间位置数的，有的话要发邮件提醒运营
					$check_extent = array(
						$_POST['extent_id'] => array(
							'start_at' => $map['start_at'],
							'end_at' => $map['end_at']
						)
					);
					$this->send_size_notice_email($check_extent);
					$this->success('编辑成功');
				}
			}
		} else {
			$content_list_one = M('')->table('sj_common_jump')->where(array('id' => $soft['content_id_1']))->find();
			$content_list_two = M('')->table('sj_common_jump')->where(array('id' => $soft['content_id_2']))->find();
			$util = D("Sj.Util");
			$typelist = $util->getHomeExtentSoftTypeList($soft['type']);
			$this->assign('content_list_one',$content_list_one);
			$this->assign('content_list_two',$content_list_two);
			$this->assign('typelist',$typelist);
			$this->assign('soft', $soft);
			$this->assign('show_form', $show_form);
			$this->assign('address_edit_able', $address_edit_able);
			$this->display();
		}
	}

	function del_extent()
	{
		$extent_id = $_REQUEST['extent_id'];
		$model = M('extent_v1');
		$where = array(
			'extent_id' => $extent_id
		);
		$map = array(
			'status' => 0,
			'update_at' =>time(),
		);
		$extent = $model->where($where)->find();
		if ($extent['parent_union_id'] != 0 && $extent['status'] == 1)
			$this->error('该区间已被联合');

		$model->where($where)->save($map);
		$extent = $model->where($where)->find();

		if ($extent['type'] == 3)
		{
			$where = array(
				'status' => 1,
				'parent_union_id' => $extent['extent_id'],
				'pid' => $extent['pid'],
			);
			$data = array(
				'parent_union_id' => 0,
			);
			$model->where($where)->save($data);
		}

		if(isset($_REQUEST['parent_id'])){
			$parent_id = (int)$_REQUEST['parent_id'];
		}else{
			$parent_id = 0;
		}
		$where = array(
			'status' => 1,
			'parent_id' => $parent_id,
			'pid' => $extent['pid'],
			'type' => array('NEQ', 3),
            'extent_type' => array('NEQ', 4),
		);

		$extent_list = $model->where($where)->order('rank ASC')-> select();
		$count = count($extent_list);
		for($i = 1;$i <= $count; $i++){
			$sql   = 'UPDATE __TABLE__ SET rank ='.$i.' WHERE `status` = 1 AND `parent_id` = '.$parent_id.' AND extent_id ='.$extent_list[$i-1]['extent_id'];
			$model -> query($sql);
		}

		$this->writelog('市场软件运营推荐-市场首页软件列表:删除了id为'.$extent_id.'的区间','sj_extent_v1',$extent_id,__ACTION__ ,"","del");
		$this->success('删除成功');
	}

	function cancel_union()
	{
		$extent_id = $_REQUEST['extent_id'];
		$where = array(
			'extent_id' => $extent_id,
		);
		$map = array(
			'parent_union_id' => 0,
		);
		$model = M('extent_v1');
		$detail = $model->where($where)->find();
		if (!empty($detail['parent_union_id']))
		{
			$count_where = array(
				'parent_union_id' => $detail['parent_union_id'],
			);
			$count = $model->where($count_where)->count();
			if ($count <= 2)
			{
				$this->error('联合区间下属区间不能少于2');
			}
		}

		$flag = $model->where($where)->save($map);
		if ($flag)
		{
			$this->writelog('在新版推荐位中修改了id为'.$extent_id.'的区间','sj_extent_v1',$extent_id,__ACTION__ ,"","edit");
			$this->success('取消联合成功');
		}
		else
		{
			$this->error('取消联合失败');
		}
	}

	function move_soft()
	{
		$selected_ids = $_POST['selected_ids'];
		$extent_id = $_POST['extent_id'];
		$where = array(
			'id' => array('in' ,$selected_ids)
		);
		$model = M('extent_soft_v1');
		$extent = $model->table('sj_extent_v1')->where(array('extent_id' => $extent_id))->find();
		$map = array(
			'extent_id' => $extent_id,
		);
		//if ($extent['location'] != '') {
		//	$map['location'] = $extent['location'];
		//}


		$model->where($where)->save($map);
		//$this->assign('jumpUrl', '/index.php/Sj/ExtentV1/index');
		//$selected_ids = implode(',', $selected_ids);
		$this->writelog("在新版推荐位中将id为[{$selected_ids}]的软件移动到了区间{$extent_id}", 'sj_extent_soft_v1',$selected_ids,__ACTION__ ,"","edit");
        // 检查导入的区间中有没有软件数大于区间位置数的，有的话要发邮件提醒运营
        // 找出被移动的所有软件的最早排期时间和最晚排期时间
        $selected_ids_arr = explode(',', $selected_ids);
        $min_start_at = $max_end_at = 0;
        foreach ($selected_ids_arr as $id) {
            if (!$id)continue;
            $where = array(
                'id' => $id,
            );
            $find = $model->table('sj_extent_soft_v1')->where($where)->find();
            if (!$find)continue;
            if ($min_start_at == 0 || $find['start_at'] < $min_start_at) {
                $min_start_at = $find['start_at'];
            }
            if ($max_end_at == 0 || $find['end_at'] > $max_end_at) {
                $max_end_at = $find['end_at'];
            }
        }
        $check_extent = array(
            $_POST['extent_id'] => array(
                'start_at' => $min_start_at,
                'end_at' => $max_end_at
            )
        );
        $this->send_size_notice_email($check_extent);
		$this->success('移动成功');
	}

	function del_soft()
	{
		$id = $_REQUEST['id'];
		$where = array(
			'id' => $id
		);
		$map = array(
			'status' => 0,
			'update_at'=>time(),
		);
		$model = M('extent_soft_v1');
		$package = $model->where($where)->find();
		$model->where($where)->save($map);
		$this->writelog("市场软件运营推荐-市场首页软件列表:删除了id为[$id]包名为{$package['package']}的区间推荐软件", 'sj_extent_soft_v1', $id,__ACTION__ ,"","del");
		$this->success('删除成功');
	}

	function isAjax ()
	{
		if ( isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == "XMLHttpRequest")  return true;
		return false;
	}

	function checkPropAct()
	{
		$extent_id = $_GET['extent_id'];
		$now = time();
		$where = array(
			'extent_id' => $extent_id,
			'status' => 1,
			'start_at' => array('elt',$now),
			'end_at' => array('egt',$now),
		);
		if (isset($_GET['id'])) {
			$where['id'] = array('neq', $_GET['id']);
		}

		$model = M('extent_soft_v1');
		$result = $model->where($where)->field('sum(prob) as prob')->find();
		$total_prob = $result['prob'];

		$where = array(
			'extent_id' => $extent_id,
			'status' => 1,
		);
		$result = $model->table('sj_extent_v1')->where($where)->find();
		$limit_prob = $result['extent_size'] * 100;
		echo $total_prob > $limit_prob ? 0: 1;
		$result = array(
			'total' => $total_prob,
			'max' => $limit_prob
		);
		exit(json_ecode($result));
	}

	function checkCandidateAct()
	{
		$package = $_REQUEST['package'];
		$where = array(
			'package' => $package,
			'status' => 1,
		);
		$model = M('extent_candidate_v1');
		$soft = $model->where($where)->find();

		echo $soft ? 1 : 0;
	}

	function list_candidate_soft()
	{
		$model = M('extent_candidate_v1');
		$srch_type = $_GET['srch_type'];
		$where = array(
			'status' => 1
		);
		if($game_type=$_GET['game_type']){
			$where['type'] = array('eq',$game_type);
			$this->assign('game_type', $game_type);
			$this->assign('srch_type', '');
		}
		if($_GET['start_at2'] && $_GET['end_at2']){
			$start_time = strtotime($_GET['start_at2']);
			$end_time   = strtotime($_GET['end_at2']);
			$where['_string'] = " ( end_at >= {$end_time} and start_at <= {$start_time}) ";
			$this->assign('start_at2', $_GET['start_at2']);
			$this->assign('end_at2', $_GET['end_at2']);
			$this->assign('srch_type', '');
		}else{
			$now = time();
			switch($srch_type) {
				case 'e':
					$where['end_at'] = array('elt',$now);
				break;

				case 'f':
					$where['start_at'] = array('egt',$now);
				break;

				case 'n':
				default:
					$where['start_at'] = array('elt',$now);
					$where['end_at'] = array('egt',$now);
					$srch_type = 'n';
				break;
			}
		}
		
		$count = $model->where($where)->count();
		import("@.ORG.Page");
		$p = new Page ( $count, 15 );
		$page = $p->show ();

		$list = $model->where($where)->order('`order` asc, start_at asc')->limit($p->firstRow.','.$p->listRows)->select();

		$package = array();
		$result = array();
		foreach($list as $val) {
			$soft = $model->table('sj_soft')->where(array('package' => $val['package']))->field('softname,softid,package')->group('package')->find();
			$val['softname'] = $soft['softname'];
			$val['softid'] = $soft['softid'];
			$result[] = $val;
		}
		$this->assign('srch_type', $srch_type);
		$this->assign('list', $result);
		$this->assign('isAjax', $this->isAjax());
		$this->assign('page', $page);
		$this->display();
	}

	function add_candidate_soft()
	{
		if (!empty($_POST)){
			$model = M('extent_candidate_v1');
			$map = array();
			$map['status'] = 1;
			$map['created_at'] = time();
			$map['updated_at'] = time();
            $map['admin_id'] = $_SESSION['admin']['admin_id'];
            if(!$_POST['package_ext']){
            	$this->error('包名不能为空');
            }
            if(!(is_numeric($_POST['order'])&&$_POST['order']==(int)$_POST['order']) || $_POST['order'] <0){
				$this->error("优先级请填写整数");
			}
			isset($_POST['package_ext']) && $map['package'] = $_POST['package_ext'];
			if(isset($_POST['package_ext'])){
				$list = $model -> table('sj_soft')->where(array('package'=>$_POST['package_ext'],'hide'=>1,'status'=>1))->find();
				$category_str='';
				$this->getMenuTree(trim($list['category_id'],','),$category_str);
				$map['type']=$category_str;
			}
			
			isset($_POST['order']) && $map['order'] = $_POST['order'];
			isset($_POST['start_at']) && $map['start_at'] = strtotime($_POST['start_at']);
			isset($_POST['end_at']) && $map['end_at'] = strtotime($_POST['end_at']);
			$data_error = $model->where(array('package'=>$_POST['package_ext'],'start_at'=>array('lt',$map['end_at']),'end_at'=>array('gt',$map['start_at']),'status'=>1))->find();
			if($data_error){
				$this->error("包名{$_POST['package_ext']}的排期冲突!");
			}
			
			$content_title = $_POST['content_title']?trim($_POST['content_title']):"";
			$resource_id = $_POST['resource_id']?$_POST['resource_id']:0;
			$is_dev = $_POST['is_dev']?$_POST['is_dev']:0;
			$is_tag = $_POST['is_tag']?$_POST['is_tag']:0;
			$content_type_one = (int)$_POST['content_type']['one'];
			if(!empty($resource_id)){
				$map['resource_id'] = $resource_id;
			}else{
				$map['content_title'] = $content_title;
				$map['is_dev'] = $is_dev;
				if( !$content_title ) {
					$this->error('请填写内容标题');
				}
				//不是开发者则需要传图
				if( !$is_dev ) {
					if( empty($_FILES['img']['tmp_name']) ) {
						$this->error('请上传图片');
					}
				}
				if( !$content_type_one ) {
					$this->error('请填写推荐内容');
				}
				if($is_dev && $content_type_one !=9 ) {
					$this->error('选择开发者的时候，推荐内容必须为应该内览');
				}
				$width = 464; $height = 274;
				$date	=	date("Ym/d/");
				if($_FILES['img']['tmp_name']) {
					$pic_path = getimagesize($_FILES['img']['tmp_name']);
					if($pic_path[0] != $width || $pic_path[1] != $height){
						$this->error("分辨率图标大小不符合条件");
					}
					if( !in_array($_FILES['img']['type'], array('image/png','image/jpg','image/jpeg')) ) {
						$this->error("请添加图片格式为：jpg，png的弹窗图片");
					}
					$config['multi_config']['img'] = array(
							'savepath'	 =>	UPLOAD_PATH. '/img/'. $date,
							'saveRule'	 =>	'getmsec',
							'img_p_size' =>	1024 * 200,
					);
				}
				if (!empty($config['multi_config'])) {
					$list = $this->_uploadapk(0, $config);
					foreach($list['image'] as $val) {
						$map[$val['post_name']] = $val['url'];
					}
				}
			}
			
			if( $is_tag == 1 ) {
				$tag_title = $_POST['tag_title']?trim($_POST['tag_title']):'';
				$content_type_two = (int)$_POST['content_type']['two'];
				if( !$tag_title ) {
					$this->error('标签标题不能为空');
				}
				if( strlen($tag_title) > 30 ) {
					$this->error("标签标题字数不能超过10个汉字");
				}
				if( !$content_type_two ) {
					$this->error('请填写推荐内容');
				}
			}
			$map['is_tag'] = $is_tag;
			$map['tag_title'] = $tag_title;
			
			if(empty($resource_id)){
				$conetnt_map_1 = array();
				$rcontent_1 = ContentTypeModel::saveRecommendContent_new($_POST,'',$conetnt_map_1, 'one');
				if($rcontent_1==true)
				{
					$conetnt_map_1['create_at'] = time();
					$conetnt_map_1['update_at'] = time();
					$conetnt_id_1 = M('')->table('sj_common_jump')->add($conetnt_map_1);
					$map['content_id_1'] = $conetnt_id_1;
				}else {
					$this -> error($rcontent_1);
				}
			}
			//展示标签的情况
			if( $is_tag == 1 ) {
				$conetnt_map_2 = array();
				$rcontent_2 = ContentTypeModel::saveRecommendContent_new($_POST,'',$conetnt_map_2, 'two');
				if($rcontent_2==true)
				{
					$conetnt_map_2['create_at'] = time();
					$conetnt_map_2['update_at'] = time();
					$conetnt_id_2 = M('')->table('sj_common_jump')->add($conetnt_map_2);
					$map['content_id_2'] = $conetnt_id_2;
				}else {
					$this -> error($rcontent_2);
				}
			}
			
			if ($id = $model->add($map)) {
				// $this->assign('jumpUrl', '/index.php/Sj/ExtentV1/list_candidate_soft');
				$this->writelog("在新版推荐位中添加了id为[{$id}]包名为{$_POST['package_ext']}的备选软件", 'sj_extent_candidate_v1', $id,__ACTION__ ,"","add");
				$this->success('添加成功');
			} else {
				$this->assign('jumpUrl', '/index.php/Sj/ExtentV1/list_candidate_soft');
				$this->error('添加失败');
			}
		} else {
			$this->display();
		}
	}
	Public function getMenuTree($parent_id = 0,&$category_str)
	{
		$model = M('');
		$list = $model -> table('sj_category')->where(array('category_id'=>$parent_id,'status'=>array('neq',0)))->find();
		if($list['parentid']!=0){
			$this->getMenuTree($list['parentid'],$category_str);
		}else{
			$category_str=$list['category_id'];
		}
	}
	function edit_candidate_soft()
	{
		$id = $_REQUEST['id'];
		$where = array(
			'id' => $id
		);
		$model = M('extent_candidate_v1');
		$soft = $model->where($where)->find();
		if (!empty($_POST)){
			$map = array();
			$map['updated_at'] = time();
			if(!(is_numeric($_POST['order'])&&$_POST['order']==(int)$_POST['order']) || $_POST['order'] <0){
				$this->error("优先级请填写整数");
			}
			isset($_POST['package_ext']) && $map['package'] = $_POST['package_ext'];
			isset($_POST['order']) && $map['order'] = $_POST['order'];
			isset($_POST['start_at']) && $map['start_at'] = strtotime($_POST['start_at']);
			isset($_POST['end_at']) && $map['end_at'] = strtotime($_POST['end_at']);
			$data_error = $model->where(array('package'=>$_POST['package_ext'],'id'=>array('neq',$id),'start_at'=>array('lt',$map['end_at']),'end_at'=>array('gt',$map['start_at']),'status'=>1))->find();
			if($data_error){
					$this->error("包名{$_POST['package_ext']}的排期冲突!");
			}
			
			$content_title = $_POST['content_title']?trim($_POST['content_title']):"";
			$resource_id = $_POST['resource_id']?$_POST['resource_id']:0;
			$is_dev = $_POST['is_dev']?$_POST['is_dev']:0;
			$is_tag = $_POST['is_tag']?$_POST['is_tag']:0;
			$content_type_one = (int)$_POST['content_type']['one'];
			$content_type_two = (int)$_POST['content_type']['two'];
			$tag_title = $_POST['tag_title']?trim($_POST['tag_title']):'';

			//无新列表内容
			if($content_title || $is_dev || $resource_id || $is_tag || $content_type_one || $content_type_two || $tag_title || $_FILES['img']['tmp_name']) {  
				if(!empty($resource_id)){
					$map['resource_id'] = $resource_id;
				}else{
					$map['content_title'] = $content_title;
					$map['is_dev'] = $is_dev;
					$width = 464; $height = 274;
					$date	=	date("Ym/d/");
					if($_FILES['img']['tmp_name']) {
						$pic_path = getimagesize($_FILES['img']['tmp_name']);
						if($pic_path[0] != $width || $pic_path[1] != $height){
							$this->error("分辨率图标大小不符合条件");
						}
						if( !in_array($_FILES['img']['type'], array('image/png','image/jpg','image/jpeg')) ) {
							$this->error("请添加图片格式为：jpg，png的弹窗图片");
						}
						$config['multi_config']['img'] = array(
								'savepath'	 =>	UPLOAD_PATH. '/img/'. $date,
								'saveRule'	 =>	'getmsec',
								'img_p_size' =>	1024 * 200,
						);
					}
					if (!empty($config['multi_config'])) {
						$list = $this->_uploadapk(0, $config);
						foreach($list['image'] as $val) {
							$map[$val['post_name']] = $val['url'];
						}
					}

					if( !$content_title ) {
						$this->error('请填写内容标题');
					}
					//不是开发者则需要传图
					if( !$is_dev ) {
						if( !$soft['img'] && empty($_FILES['img']['tmp_name']) ) {
							$this->error('请上传图片');
						}
					}
					if( !$content_type_one ) {
						$this->error('请填写推荐内容');
					}
					if($is_dev && $content_type_one !=9 ) {
						$this->error('选择开发者的时候，推荐内容必须为应该内览');
					}
				}
				
				if( $is_tag == 1 ) {
					if( !$tag_title ) {
						$this->error('标签标题不能为空');
					}
					if( strlen($tag_title) > 30 ) {
						$this->error("标签标题字数不能超过10个汉字");
					}
					if( !$content_type_two ) {
						$this->error('请填写推荐内容');
					}
				}
				$map['is_tag'] = $is_tag;
				$map['tag_title'] = $tag_title;
				
				if(empty($resource_id)){
					$conetnt_map_1 = array();
					$rcontent_1 = ContentTypeModel::saveRecommendContent_new($_POST,'',$conetnt_map_1, 'one');
					if($rcontent_1==true)
					{
						$conetnt_map_1['create_at'] = time();
						$conetnt_map_1['update_at'] = time();
						$conetnt_id_1 = M('')->table('sj_common_jump')->add($conetnt_map_1);
						$map['content_id_1'] = $conetnt_id_1;
					}else {
						$this -> error($rcontent_1);
					}
				}
				//展示标签的情况
				if( $is_tag == 1 ) {
					$conetnt_map_2 = array();
					$rcontent_2 = ContentTypeModel::saveRecommendContent_new($_POST,'',$conetnt_map_2, 'two');
					if($rcontent_2==true)
					{
						$conetnt_map_2['create_at'] = time();
						$conetnt_map_2['update_at'] = time();
						$conetnt_id_2 = M('')->table('sj_common_jump')->add($conetnt_map_2);
						$map['content_id_2'] = $conetnt_id_2;
					}else {
						$this -> error($rcontent_2);
					}
				}
			}
			if ($model->where($where)->save($map)) {
				// $this->assign('jumpUrl', '/index.php/Sj/ExtentV1/list_candidate_soft');
				$this->writelog("在新版推荐位中编辑了id为[{$id}]包名为{$_POST['package_ext']}的备选软件", 'sj_extent_candidate_v1', $id,__ACTION__ ,"","edit");
				$this->success('编辑成功');
			}
		} else {
			if( $soft['content_id_1'] ) {
				$content_list_one = M('')->table('sj_common_jump')->where(array('id' => $soft['content_id_1']))->find();
			}else {
				$content_list_one = '';
			}
			if( $soft['content_id_2'] ) {
				$content_list_two = M('')->table('sj_common_jump')->where(array('id' => $soft['content_id_2']))->find();
			}else {
				$content_list_two = '';
			}
			$this->assign('content_list_one',$content_list_one);
			$this->assign('content_list_two',$content_list_two);
			$this->assign('soft', $soft);
			$this->display();
		}
	}
	function del_candidate_soft()
	{
		$model = M('extent_candidate_v1');
		if($_POST['ids']){
			$ids = trim($_POST['ids'],',');
			$model->where(array('id' => array('in',$ids)))->save(array('status' => 0));
			$this->writelog("在新版推荐位中删除了id为[{$ids}]的备选软件", 'sj_extent_candidate_v1', $ids,__ACTION__ ,"","del");
			$this->success('删除成功');
		}
		$id = $_REQUEST['id'];
		$where = array(
			'id' => $id
		);
		$map = array(
			'status' => 0
		);
		$package = $model->where($where)->find();
		$model->where($where)->save($map);
		$this->writelog("在新版推荐位中删除了id为[{$id}]包名为{$package['package']}的备选软件", 'sj_extent_candidate_v1', $id,__ACTION__ ,"","del");
		$this->success('删除成功');
	}

	//批量修改时间
	function edit_more(){
		if($_POST){
//			var_dump($_POST);
			$ids = $_POST['id'];
//			var_dump($ids);
			$start_tm = !empty($_POST['start'])?strtotime($_POST['start']):'';
			$end_tm =  !empty($_POST['end'])?strtotime($_POST['end']):'';
			$model = M('');
			$where = array('id'=>array('in',$ids));
			$old_info = $model->table('sj_extent_candidate_v1')->where($where)->field('id,package,start_at,end_at')->select();
			$package = $error_pack =$edit_id  = $can_id = array();
			foreach($old_info as $k=>$v){
				if($start_tm>$v['end_at']&&!$end_tm){
					//修改的开始时间不能已有的结束时间
					$error_pack[] = $v['package'];
					continue;
				}
				if($end_tm < $v['start_at']&&!$start_tm){
					//修改的结束时间不能小于开始时间
					$error_pack[] = $v['package'];
					continue;
				}
				$edit_id[$v['package']] = array(
					'id' => $v['id'],
					'start_at' => !empty($start_tm)?$start_tm:$v['start_at'],
					'end_at' =>!empty($end_tm)?$end_tm:$v['end_at']
				);
				$can_id[] = $v['id'];
				$package[] = $v['package'];
			}

			$package = array_unique($package);
//			var_dump($edit_id);
			$pack_info =  $model->table('sj_extent_candidate_v1')->where(array('package'=>array('in',$package),'id'=>array('not in',$ids),'status'=>1))->field('id,package,start_at,end_at')->select();
			foreach($pack_info as $key=>$val){
//				var_dump($val['start_at'].'--'.$val['end_at'].'--'.$edit_id[$val['package']]['start_at'].'--'.$edit_id[$val['package']]['end_at']);
				if(is_time_cross($val['start_at'], $val['end_at'],$edit_id[$val['package']]['start_at'], $edit_id[$val['package']]['end_at'])){
					$error_pack[] = $val['package'];
					$n_k = array_search($edit_id[$val['package']]['id'],$can_id);
					if($n_k)
					unset($can_id[$n_k]);
				}
			}

			$data = array();
			$log_tm = '';
			if(!empty($start_tm)){
				$data['start_at'] = $start_tm;
				$log_tm .= '开始时间为'.$_POST['start'];
			}
			if(!empty($end_tm)){
				$data['end_at'] = $end_tm;
				$log_tm .= '结束时间为'.$_POST['end'];
			}
			$num = count($can_id);
			$error_pack = array_unique($error_pack);
			$error_num = count($error_pack);
			$error_pack_str = implode(',',$error_pack);
			$res = $model->table('sj_extent_candidate_v1')->where(array('id'=>array('in',$can_id)))->save($data);

			if($res){
				$log_id = implode(',',$can_id);

				$this->writelog("批量修改了备选软件列表的时间id为{$log_id}的{$log_tm}", 'sj_extent_candidate_v1',$log_id,__ACTION__ ,"","edit");
				$msg = "成功配置软件：{$num}个\r\n软件时间冲突：{$error_num}个 {$error_pack_str}已存在";
				if($error_num>0){
					echo json_encode(array('code'=>1,'msg'=>$msg));
				}else{
					echo json_encode(array('code'=>1,'msg'=>"成功配置软件：{$num}个"));
				}
			}else{
				if($error_num>0){
					$msg = '修改失败,软件时间冲突';
				}else{
					$msg = '修改失败';
				}
				echo json_encode(array('code'=>0,'msg'=>$msg));
			}

			exit();
		}

		$this->display();
	}


	//批量导入备选库软件
    function edit_more_ad(){
        $model = M('');
        if($_FILES){
            $this->import_more_ad();
            exit();
        }
        $this->display();
    }

    function import_more_ad(){
        $model = M('');
        list($package,$info,$repeat_pack,$not_found_pack,$no_order,$time_error) = $this->import_more_ad_do();
        // if(count($package)>0){
        //     $whitelist_soft = get_table_data(array('package'=>array('in',$package),'status'=>1),"sj_soft_whitelist",'package','id,package,softname,is_accept_ad,accept_ad_remark', '');
        //     // echo $model->getLastSql();
        //     $has_soft = array();
        //     if($whitelist_soft){
        //         foreach($whitelist_soft as $k=>$v){
        //             $has_soft[] = $v['package'];
        //         }
        //         $fail_soft = array_filter(array_diff($package,$has_soft));

        //     }
        //     $soft_info = get_table_data(array('status'=>1,'hide'=>1,'package'=>array('in',$package)),"sj_soft",'package','softname,package','');
        //     // echo "<pre>";var_dump($info);
        //     foreach($whitelist_soft as $k=>$v){
        //         $v['beizhu'] = $info[$v['package']]['beizhu'];
        //         $v['o_softname'] = $soft_info[$v['package']]['softname'];
        //         $whitelist_soft[$k] = $v;
        //     }
        //     $num = count($whitelist_soft);
        //     $this->assign('num',$num);
        //     $this->assign('fail_soft',$fail_soft);
        //     // echo "<pre>";var_dump($whitelist_soft);die;
        //     $j_whitelist_soft = base64_encode(json_encode($whitelist_soft));
        //     $this->assign('j_whitelist_soft',$j_whitelist_soft);
        //     $this->assign('whitelist_soft',$whitelist_soft);
        //     $this->assign('repeat_pack',$repeat_pack);
        // }
        // if(count($package)>0){
            $num = count($package);
            $this->assign('num',$num);
            $this->assign('not_found_pack',$not_found_pack);
            $j_info = base64_encode(json_encode($info));
            $this->assign('j_info',$j_info);
            $this->assign('info',$info);
            $this->assign('repeat_pack',$repeat_pack);
            $this->assign('no_order',$no_order);
            $this->assign('time_error',$time_error);
        // }
        $this->display('import_more_ad');
    }
    function import_more_ad_do(){
    	$model = M('');
        $ad_file = $_FILES['ad_file'];
        if(!$ad_file['size']){
            $this->error('请上传文件');
        }
        $ext = pathinfo($ad_file['name']);
        if(strtolower($ext['extension'])!='csv'){
            $this->error('请上传csv格式文件');
        }
        $shili = fopen($ad_file['tmp_name'], "r");
        $package = $info = $repeat_pack = $not_found_pack= $no_order= $time_error=array();
        $str = '';
        while (!feof($shili)) {
            $shi = fgets($shili, 1024);
            $a = explode(',', $shi);
            if(count($a)>5){
                $this->error("文件格式错误");
            }
            $str .= $shi . ",";
        }
        $str_arr = explode("\r\n", $str);
        foreach($str_arr as $key => $val){
            if(empty($val)||$val === ',,'){
                continue;
            }
            if($key==0){
                continue;
            }else{
                list($a,$pack,$order,$start_at,$end_at) = explode(',',$val);
            }
            // $beizhu = mb_convert_encoding($beizhu,"UTF-8","GBK");
            $pkg_data=$model->table('sj_soft')->where(array('hide'=>1,'status'=>1,'package'=>$pack))->find();
            if($pkg_data){
	            if(isset($info[$pack])){
	                $repeat_pack[] = $pack;
	            }else{
	            	if(!$order || !(is_numeric($order)&&$order==(int)$order) || $order <0){
	            		$no_order[]=$pack;
	            		continue;
	            	}
	                $start_at=explode('/', $start_at);
	                $end_at=explode('/', $end_at);
	                if(!$start_at[1] || !$start_at[1] || strtotime($start_at[1])>strtotime($end_at[1])){
	            		$time_error[]=$pack;
	            		continue;
	            	}
	            	$info[trim($pack)]['order'] = $order;
	                $info[trim($pack)]['start_at'] = $start_at[1];
	                $info[trim($pack)]['end_at'] = $end_at[1];
	                $info[trim($pack)]['softname'] = $pkg_data['softname'];
	                $category_str='';
					$this->getMenuTree(trim($pkg_data['category_id'],','),$category_str);
					$info[trim($pack)]['type'] = $category_str;
					$package[] = trim($pack);

	            }
            }else{
            	$not_found_pack[]=$pack;
            }
            

        }
        $repeat_pack = array_unique(array_filter($repeat_pack));
        $package = array_unique(array_filter($package));
        $not_found_pack = array_unique(array_filter($not_found_pack));

        return array($package,$info,$repeat_pack,$not_found_pack,$no_order,$time_error);
    }
    function pub_update_ad_info(){
        $model = M('');
        $info = json_decode(base64_decode($_POST['info']),true);
        $pkg = explode(',',trim($_POST['pkg'],','));
        $id_str= '';
        $repeat_pack= array();
        $import_count=0;
        foreach($info as $k=>$v){
            if(!in_array($k, $pkg)){
                continue;
            }
            // $pkg_data=$model->table('sj_extent_candidate_v1')->where(array('package'=>$k,'status'=>1))->find();
            // if($pkg_data){
            // 	$repeat_pack[]=$k;
            // 	continue;
            // }
            $i_log = "备选库软件成功添加了";
            $data=array();
            $data['package']=$k;
            $data['type']=$v['type'];
            $data['order']=$v['order'];
            $data['start_at']=strtotime($v['start_at']);
            $data['end_at']=strtotime($v['end_at']);
            $data['created_at']=time();
            $data['status']=1;
            $data['admin_id']=$_SESSION['admin']['admin_id'];
            $data_error = $model->table("sj_extent_candidate_v1")->where(array('package'=>$k,'start_at'=>array('lt',$data['end_at']),'end_at'=>array('gt',$data['start_at']),'status'=>1))->find();
			if($data_error){
				$repeat_pack[]=$k;
            	continue;
			}
			/*$if_new = false;
			$if_new_arr = $model->table("sj_extent_candidate_v1")->where(array('package'=>$k,'status'=>1))->select();
			foreach($if_new_arr as $vv){
				if($vv['content_title'] != ''){
					$if_new = true;
					break;
				}
			}
			if($if_new == false){*/
				$common_jump = $model->table('sj_common_jump')->field('id,content_type')->where(array('status'=>1,'package_643'=>$k,'resource_type'=>1))->select();
				if(!empty($common_jump)){
					$resource_id = 0;
					$tmp_arr = array();
					foreach($common_jump as $val){
						$tmp_arr[$val['content_type']] = $val['id'];
					}
					foreach(array(9,1,2,4,5,3,6,7) as $val){
						if(array_key_exists($val, $tmp_arr)){
							$resource_id = $tmp_arr[$val];
							break;
						}
					}
					$data['resource_id'] = $resource_id;
				}
			/*}*/
            $res = $model->table('sj_extent_candidate_v1')->add($data);
            if($res){
                $import_count++;
                $id_str .= $res.',';
            }
        }
        if($id_str){
            $this->writelog($i_log."id为{$id_str}的数据", 'sj_extent_candidate_v1', $id_str,'/index.php/Dev/SoftWhitelist/edit_more_ad','','add');
        }
        $count=count($pkg);
        if(count($repeat_pack)){
            $import_count_failure=$count-$import_count;
            $str='';
            if($import_count){
            	$str="成功配置软件：{$import_count}个  ";
            }
            $str.="软件时间冲突：{$import_count_failure}个  ";
            foreach($repeat_pack as $k=>$v){
            	if(count($repeat_pack)==($k+1)){
            		$str.=$v.'已存在。';
            	}else{
            		$str.=$v.',';
            	}
            }
            $str.="\n";
            echo json_encode(array('code'=>2,'msg'=>$str));
        }else{
            echo json_encode(array('code'=>1,'msg'=>"成功配置软件：{$count}个"));return 1;
            echo 1;return 1;
        }
    }
	function pub_get_address()
	{
		$util = D("Sj.Util");
		$result = $util->getAddress();
		$json = json_encode($result);
		$this->assign('json', $json);
		$js = $this->fetch('get_address');
		header('content-type:application/x-javascript');
		exit($js);
	}

	function pub_check_extent_address()
	{
		$extent_id = $_POST['extent_id'];
		$model = new Model();
		$extent = $model->table('sj_extent_v1')->where(array('extent_id' => $extent_id))->find();
		//if (empty($extent['location']) && !empty($_POST['location'])) {
		//	$sql = "select count(*) as total from sj_extent_soft_v1 where extent_id='{$extent_id}' and location<>'' ";
		//	$res = $model->query($sql);
		//	if ($res[0]['total'] > 0) {
		//		exit('0');
		//	}
		//	if ($extent['type'] == 2) {
		//		$sql = "select count(*) as total from sj_extent_v1 where parent_id='{$extent_id}' and location<>'' ";
		//		$res = $model->query($sql);
		//		if ($res[0]['total'] > 0) {
		//			exit('0');
		//		}
		//	}
        //
		//}
		exit('1');
	}

	function phone_config_manage_show(){
		$model = new Model();
		$cpu_result = $model -> table('pu_config') -> where(array('config_type' => 'CPU_MAIN_FREQUENCY','status' => 1)) -> select();
		$cpu = json_decode($cpu_result[0]['configcontent']);
		$ram_result = $model -> table('pu_config') -> where(array('config_type' => 'PHONE_RAM','status' => 1)) -> select();
		$ram = json_decode($ram_result[0]['configcontent']);
		$result = $model -> table('pu_config') -> where(array('config_type' => 'PHONE_ACCOUNT_CONFIG','status'  => 1)) -> select();
		$my_result = json_decode($result[0]['configcontent'],true);
		$this -> assign('my_result',$my_result);
		$this -> assign('cpu_result',$cpu);
		$this -> assign('ram_result',$ram);
		$this -> display();
	}


	function phone_config_manage(){
		$model = new Model();
		$high_res = $_GET['high_res'];
		$low_res = $_GET['low_res'];
		if(!$high_res || !eregi("^[0-9]+$",$high_res) || $high_res < 0){
			$this -> error('请输入正整数的分辨率');
		}
		if(!$low_res || !eregi("^[0-9]+$",$low_res) || $low_res < 0){
			$this -> error('请输入正整数的分辨率');
		}
		if($low_res < $high_res){
			$this -> error('分辨率前一位必须小于后一位');
		}
		$cpu_scr = $_GET['cpu_scr'];
		$ram = $_GET['ram'];
		//6.3删除包名弹窗次数配置和gif图片所需最低内存  del_apk_pop_num和gif_ram
		$del_apk_pop_num = $_GET['del_apk_pop_num']?$_GET['del_apk_pop_num']:3;//默认3次
		if($_GET['del_apk_pop_num']!=null&&$_GET['del_apk_pop_num']<=0)
		{
			$this -> error('请输入正整数的次数');
		}
		if(!$del_apk_pop_num || !eregi("^[0-9]+$",$del_apk_pop_num) || $del_apk_pop_num <= 0){
			$this -> error('请输入正整数的次数');
		}
		$gif_ram = $_GET['gif_ram'];
		
		$my_data = json_encode(array('high_res' => $high_res,'low_res' => $low_res,'cpu_scr' => $cpu_scr,'ram' => $ram,'del_apk_pop_num' => $del_apk_pop_num,'gif_ram'=>$gif_ram));
		$data = array(
			'config_type' => 'PHONE_ACCOUNT_CONFIG',
			'configname' => '手机高低配置',
			'configcontent' => $my_data,
			'uptime' => time(),
			'status' => 1
		);

		$been_result = $model -> table('pu_config') -> where(array('config_type' => 'PHONE_ACCOUNT_CONFIG','status' => 1)) -> select();
		if($been_result){
			$log_result = $this -> logcheck(array('config_type' => 'PHONE_ACCOUNT_CONFIG','status' => 1),'pu_config',$data,$model);
			$result = $model -> table('pu_config') -> where(array('config_type' => 'PHONE_ACCOUNT_CONFIG','status' => 1)) -> save($data);
		}else{
			$result = $model -> table('pu_config') -> add($data);
		}

		if($result){
			$this->assign('jumpUrl', '/index.php/Sj/ExtentV1/phone_config_manage_show');
			$this->writelog("已更新手机高低配置".$log_result, 'pu_config',$been_result[0]['conf_id'],__ACTION__ ,"","edit");
			$this->success('配置成功');

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

    // 只检查导入文件的手工填写内容，并将其数据格式转成与网页版的添加一致
    // 1，将每一行数组的key由数字转成对应数据库的列名，如0为extend_id，1为extent_name...
    // 2，将某些列的字符串转成数字，如是、否转化成1，0...
    function handwriting_convert_and_check(&$content_arr) {
        // 初始化错误数组
        $error_msg = array();
        foreach($content_arr as $key => $value) {
            $this->init_error_msg($error_msg, $key);
        }
        // 业务逻辑：将表里字段名称和模版里列的名称一一对应
        $correct_title_arr = array(
            'extent_id'     =>  '区间ID',
            'extent_name'   =>  '区间名',
            'package'  =>   '包名',
            'phone_dis'  =>   '高低配区分展示',
            'old_phone'  =>   '旧版展示(低于V4.4.1)',
            'prob'  =>   '显示概率',
            'start_at'  =>   '开始时间(yyyy/MM/dd)',
            'end_at'  =>   '结束时间(yyyy/MM/dd)',
            'type'  =>   '合作形式',
            'beid'  =>   '行为id',
        	'content_type' => '内容类型',
        );
        // trim一下所有的数据
        foreach($content_arr as $key=>$record) {
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
        // 业务逻辑：检查列填写是否为预期文字，如果是则换成对应数据，如果不是则添加错误信息
        $expected_words = array();
        // 当输入为空不允许时，将其值设为false以作区别
        $expected_words['phone_dis'] = array(""=>false, "不做区分"=>1, "仅高配手机展示"=>2, "仅低配手机展示"=>3);
        $expected_words['old_phone'] = array(""=>0, "是"=>1, "否"=>0);
        $expected_words['content_type'] = array(1=>9, 2=>1, 3=>2, 4=>4, 5=>5, 6=>3, 7=>6, 8=>7, 100=>0);
		//合作形式
		$util = D("Sj.Util");
		$typelist = $util->get_cooperation();
		$expected_words['type'] = $typelist;
		//内容类型必填
		foreach($content_arr as $key => $record){
        	if(!isset($record['content_type'])){
        		$this->append_error_msg($error_msg, $key, 1, "包名：{$record['package']}内容类型为空");
        	}
        }
        foreach($content_arr as $key=>$record){
            // 开始检查每列内容是否为预期内容
            foreach($record as $r_key=>$r_value){
                if (array_key_exists($r_key, $expected_words)) {
                    if (!array_key_exists($r_value, $expected_words[$r_key])) {
                        $column = $correct_title_arr[$r_key];
                        $this->append_error_msg($error_msg, $key, 1, "{$column}列内容填写有误;");
                    } else {
                        $tmp = $expected_words[$r_key][$r_value];
                        // 如果是false不处理（在后台的logic_check()里会统一进行非空检查），即还是为空，否则替换成相应的数字
                        if ($tmp !== false)
                            $content_arr[$key][$r_key] = $tmp;
                    }
                }
                // 自动填充批量导入的时间
                if ($r_key == 'start_at' || $r_key == 'end_at') {
                    if ($r_key == 'start_at') {
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
        // 检查区间类型是否为普通区间类型（现需求暂不支持批量导入多软件区间的软件）
        foreach($content_arr as $key => $record) {
            // 查找该区间的类型
            if (isset($record['extent_id']) && $record['extent_id'] != "") {
                $where = array(
                    'extent_id' => array('EQ', $record['extent_id']),
                    'status' => array('EQ', 1),
                );
                $M_extent_table = 'extent_v1';
                $extent_model = M($M_extent_table);
                $find = $extent_model->where($where)->find();
                if ($find) {
                    $content_arr[$key]['extent_type'] = $find['extent_type'];
                    if ($find['extent_type'] == 4) {
                        $this->append_error_msg($error_msg, $key, 1, "投放区间不能是多软件位区间,多软件位区间不能批量导入软件;");
                    }
                }
            }
        }
        
        //如果有标题列，检查该区间是否是单软件（列表单图），包名是否有有应用内览
        /*foreach ($content_arr as $key => $record) {
        	//不存在标题列则不是单软件列表单图
        	if( !isset($record['content_title']) && empty($record['content_title']) ) {
        		continue;
        	}else {
        		$where_extent = array('extent_id'=>$record['extent_id']);
        		$find = M('extent_v1')->where($where_extent)->find();
        		if( $find['show_form'] != 1 ) {
        			$this->append_error_msg($error_msg, $key, 1, "投放区间列表展示形式不是单软件（列表单图）");
        		}
        		//包名是否存在应用内览
        		$where_pkg = array(
        				'package'	=>	$record['package'],
        				'status'	=>	1,
        				'passed'	=>	2,
        		);
        		$used = M('soft_content_explicit')->table('sj_soft_content_explicit')->where($where_pkg)->order('id desc')->limit(0,1)->find();
        		if(empty($used)) {
        			$this->append_error_msg($error_msg, $key, 1, "包名：{$record['package']},不存在应用内览");
        		}else {
        			$content_arr[$key]['used_id'] = $used['id'];
        		}
        	}
        }*/
        return $error_msg;
    }
    
    // 页面添加或编辑、批量导入共用的逻辑检查
    function logic_check($content_arr) {
        // 初始化错误数组
        $error_msg = array();
        foreach($content_arr as $key => $value) {
            $this->init_error_msg($error_msg, $key);
        }
        // 业务逻辑：区间表、区间软件表
        $M_extent_table = 'extent_v1';
        $M_extent_soft_table = 'extent_soft_v1';
        // 加一下前缀（真正的表名），主要用在join sql里
        $extent_table = 'sj_' . $M_extent_table;
        $extent_soft_table = 'sj_' . $M_extent_soft_table;
        // 获得三个表的model
        $extent_model = M($M_extent_table);
        $extent_soft_model = M($M_extent_soft_table);
        $soft_model = M("soft");//软件大表
        // 业务逻辑：以下为各项具体检查
        foreach($content_arr as $key=>$record) {
            // 检查是不是编辑
            if (isset($record['id'])) {
                $where = array('id' => array('EQ', $record['id']));
                $find = $extent_soft_model->where($where)->find();
                // 获得区间名
                $content_arr[$key]['extent_id'] = $find['extent_id'];
                // 也赋给$record
                $record['extent_id'] = $find['extent_id'];
            }
            // 检测区间ID
            if (isset($record['extent_id']) && $record['extent_id'] != "") {
                $where = array(
                    'extent_id' => array('EQ', $record['extent_id']),
                    'type' => array(array('EQ', 1), array('EQ', 3), 'OR'),
                    'parent_union_id' => array('EQ', 0),
                    'status' => array('EQ', 1),
                );
                $find = $extent_model->where($where)->find();
                if (!$find) {
                    $this->append_error_msg($error_msg, $key, 1, "区间位ID【{$record['extent_id']}】无效;");
                } else {
                    if (isset($record['extent_name'])) {
                        // 检查区间ID与区间名是否对应
                        if ($find['extent_name'] != $record['extent_name']) {
                            $this->append_error_msg($error_msg, $key, 1, "区间位ID与区间名不对应;");
                        }
                    }
                    // 得到该记录区间的cid、oid，并保存起来，方便后面的区间冲突检查
                    $content_arr[$key]['bk_cid'] = $find['cid'];
                    $content_arr[$key]['bk_oid'] = $find['oid'];
                    $content_arr[$key]['bk_pid'] = $find['pid'];
                }
            } else {
                $this->append_error_msg($error_msg, $key, 1, "区间位ID不能为空;");
            }
            // 检查包名是否在sj_soft表里
            if (isset($record['package']) && $record['package'] != "") {
                $where = array(
                    'package' => $record['package'],
                    'status' => 1,
                    'hide' => array('in', array(1, 1024)),
                );
                $find = $soft_model->where($where)->find();
                if (!$find) {
                    $this->append_error_msg($error_msg, $key, 1, "包名【{$record['package']}】不存在于市场软件库中;");
                }
            } else {
                $this->append_error_msg($error_msg, $key, 1, "包名不能为空;");
            }
            // 检查高低配区分展示的值
            if (isset($record['phone_dis']) && $record['phone_dis'] != "") {
            } else {
                $this->append_error_msg($error_msg, $key, 1, "高低配区分展示值不能为空;");
            }
            // 检查显示概率是否为数字
            if (isset($record['prob']) && $record['prob'] != "") {
                if (!preg_match("/^\d+$/", $record['prob'])) {
                    $this->append_error_msg($error_msg, $key, 1, "显示概率应为整数;");
                } else if ($record['prob'] > 100) {
                    $this->append_error_msg($error_msg, $key, 1, "显示概率不能大于100;");
                }
            } else {
                $this->append_error_msg($error_msg, $key, 1, "显示概率值不能为空;");
            }
            // 检查开始时间
            if (isset($record['start_at']) && $record['start_at'] != "") {
                if (!preg_match("/^\d{4}(\-|\/|\.)\d{1,2}(\-|\/|\.)\d{1,2}\s+\d{1,2}:\d{1,2}:\d{1,2}$/", $record['start_at'])) {
                    $this->append_error_msg($error_msg, $key, 1, "开始时间日期格式不对;");
                } else {
                    $time = strtotime($record['start_at']);
                    if ($time) {
                        $content_arr[$key]['bk_start_time'] = $time;
                    } else {
                        $this->append_error_msg($error_msg, $key, 1, "开始时间日期格式不对;");
                    }
                }
            } else {
                $this->append_error_msg($error_msg, $key, 1, "开始时间不能为空;");
            }
            // 检查结束时间
            if (isset($record['end_at']) && $record['end_at'] != "") {
                if (!preg_match("/^\d{4}(\-|\/|\.)\d{1,2}(\-|\/|\.)\d{1,2}\s+\d{1,2}:\d{1,2}:\d{1,2}$/", $record['end_at'])) {
                    $this->append_error_msg($error_msg, $key, 1, "结束时间日期格式不对;");
                } else {
                    $time = strtotime($record['end_at']);
                    if ($time) {
                        $content_arr[$key]['bk_end_time'] = $time;
                    } else {
                        $this->append_error_msg($error_msg, $key, 1, "结束时间日期格式不对;");
                    }
                }
            } else {
                $this->append_error_msg($error_msg, $key, 1, "结束时间不能为空;");
            }
            // 检查开始时间是否小于结束时间
            if (isset($content_arr[$key]['bk_start_time']) && isset($content_arr[$key]['bk_end_time'])) {
                if ($content_arr[$key]['bk_start_time'] > $content_arr[$key]['bk_end_time']) {
                    $this->append_error_msg($error_msg, $key, 1, "开始时间需小于结束时间;");
                }
            }
        }

        // 业务逻辑：检查行与行之间的数据是否冲突
        foreach($content_arr as $key1=>$record1) {
            // 如果开始时间或结束时间无效，则不比较
            if (!isset($record1['bk_start_time']) || !isset($record1['bk_end_time']))
                continue;
            // 如果填写的区间无效，则不比较
            if (!isset($record1['bk_cid']) || !isset($record1['bk_oid']) || !isset($record1['bk_pid']))
                continue;
            foreach($content_arr as $key2=>$record2) {
                // 比较过的不比较
                if ($key1 >= $key2)
                    continue;
                // 如果包名不同，则不比较
                if ($record1['package'] != $record2['package'])
                    continue;
                // 如果开始时间或结束时间无效，则不比较
                if (!isset($record2['bk_start_time']) || !isset($record2['bk_end_time']))
                    continue;
                // 如果填写的区间无效，则不比较
                if (!isset($record2['bk_cid']) || !isset($record2['bk_oid']) || !isset($record2['bk_pid']))
                    continue;
                // 区间属性不同，则不比较
                if ($record1['bk_cid'] != $record2['bk_cid'] || $record1['bk_oid'] != $record2['bk_oid'] || $record1['bk_pid'] != $record2['bk_pid'])
                    continue;
                // 时间是否交叉
                if ($record1['bk_start_time'] <= $record2['bk_end_time'] && $record2['bk_start_time'] <= $record1['bk_end_time']) {
                    $k1 = $key1 + 1; $k2 = $key2 + 1;
                    $this->append_error_msg($error_msg, $key1, 1, "投放区间与第{$k2}行有重叠;");
                    $this->append_error_msg($error_msg, $key2, 1, "投放区间与第{$k1}行有重叠;");
                }
            }
        }

        // 业务逻辑：检查每一行数据是否与数据库的存储内容相冲突
        foreach($content_arr as $key=>$record) {
            // 如果开始时间或结束时间无效，则不比较
            if (!isset($record['bk_start_time']) || !isset($record['bk_end_time']))
                continue;
            // 如果填写的区间无效，则不比较
            if (!isset($record['bk_cid']) || !isset($record['bk_oid']) || !isset($record['bk_pid']))
                continue;
            // 检查是否与sj_extent_soft_v1表里有相同包名且区间冲突的包
            // 业务逻辑：获得当前记录的信息：package、cid、oid
            $es_package = escape_string($record['package']);
            $cid = escape_string($record['bk_cid']);
            $oid = escape_string($record['bk_oid']);
            $pid = escape_string($record['bk_pid']);
            $start_time = escape_string($record['bk_start_time']);
            $end_time = escape_string($record['bk_end_time']);
            // 构造sql语句，查找出与该记录包名相同、也是在相同属性的区间的所有后台记录
            $sql_select = "select {$extent_soft_table}.id as id, {$extent_soft_table}.package as package, {$extent_table}.extent_name, {$extent_soft_table}.start_at as start_at, {$extent_soft_table}.end_at as end_at, {$extent_soft_table}.status as status";
            $sql_from = " from {$extent_soft_table} left join {$extent_table}";
            $sql_on = " on {$extent_soft_table}.extent_id={$extent_table}.extent_id";
            $sql_where = " where {$extent_soft_table}.package='{$es_package}' and {$extent_soft_table}.start_at <= {$end_time} and {$extent_soft_table}.end_at >= {$start_time} and {$extent_soft_table}.status!=0 and {$extent_table}.status=1 and {$extent_table}.cid='{$cid}' and {$extent_table}.oid='{$oid}' and {$extent_table}.pid='{$pid}' and {$extent_table}.parent_union_id=0 ";
            // 如果有传id过来，说明是编辑，这时要排除此id
            $sql_where_except = "";
            if (isset($record['id'])&&$record['life']!=1) {
                $except_id = escape_string($record['id']);
                // 拼接sql，A为下面的$sql里表sj_extent_soft_v1的别名，注意二者需一致
                $sql_where_except = " and {$extent_soft_table}.id != {$except_id}";
            }
            // 将select、from、on、where、except拼接起来
            $sql = $sql_select . ' '. $sql_from . ' ' . $sql_on . ' ' . $sql_where . ' ' .  $sql_where_except;
            // 执行sql
            $db_records = $extent_soft_model->query($sql);
            // 有冲突的后台记录
            foreach($db_records as $db_key=>$db_record) {
                $start_at_str = date('Y-m-d H:i:s',$db_record['start_at']);
                $end_at_str = date('Y-m-d H:i:s',$db_record['end_at']);
                $status_paused_hint = "";
                if ($db_record['status'] == 2) {
                    $status_paused_hint = "，该记录处于已停用状态，请前往批量明细列表中操作";
                }
                $this->append_error_msg($error_msg, $key, 1, "投放区间与后台区间【{$db_record['extent_name']}】里ID为【{$db_record['id']}】，包名为【{$db_record['package']}】的记录有重叠（该投放时间从【{$start_at_str}】到【{$end_at_str}】{$status_paused_hint}）;");
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
        $error_msg2 = $this->logic_check($content_arr);
        // 将$error_msg2合并到$error_msg1里并返回$error_msg1
        //屏蔽软件上排期时报警需求 新增  yuesai
		$AdSearch = D("Sj.AdSearch");
        $error_msg3 = $AdSearch->logic_check_shield($content_arr,'start_at','end_at','sj_extent_soft_v1');
        
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
    function down_moban() {
        $file_dir = C("ADLIST_PATH") . "shouyetuijian_import_moban.csv";
        if (file_exists($file_dir)) {
            $file = fopen($file_dir,"r");
            Header("Content-type: application/octet-stream");
            Header("Accept-Ranges: bytes");
            Header("Accept-Length: ".filesize($file_dir));
            Header("Content-Disposition: attachment; filename=" . urlencode('首页推荐批量导入模版') . ".csv");
            echo fread($file, filesize($file_dir));
            fclose($file);
            exit(0);
        } else {
            header("Content-type: text/html; charset=utf-8");
            echo "File not found!";
            exit;
        }
    }

    // 批量导入访问的页面节点
    function import_softs() {
    	$pid = empty($_GET['pid']) ? 1 : $_GET['pid'];
    	$this->assign('pid', $pid);
        if ($_GET['down_moban']) {
            $this->down_moban();
        } else if ($_FILES) {
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
            }

            if (!$flag) {
                $this->ajaxReturn($error_msg,'您上传的CSV有如下问题，请修改后重新上传！', -5);
            }
            // 判断后台有没有人正在导入
            $lock_name = 'sj_extent_soft_v1_importing';
            $import_lock = S($lock_name);
            if ($import_lock) {
                $this->ajaxReturn("",'后台有人正在导入，请稍后再尝试！', 1);
            }
            // 上锁，设置60秒内有效
            S($lock_name, 1, 60, 'File');
            // 返回导入结果，如果记录的flag为0表示添加失败
            list($result_arr, $no_resource_arr) = $this->process_import_array($content_arr);
            // 导入后解锁
            S($lock_name, NULL);
            $flag = true;
            $no_resource_flag = true;
            foreach($result_arr as $key=>$value) {
                if ($value['flag'] == 0)
                    $flag = false;
            }
            foreach($no_resource_arr as $key=>$value) {
                if ($value['flag'] == 0)
                    $no_resource_flag = false;
            }
            // save the import file for backups
            $save_dir = IMPORT_FILE_UPLOAD_PATH;
            $this->mkDirs($save_dir);
            $save_name = MODULE_NAME. '_' . ACTION_NAME  . '_' . time() . '_' . $_SESSION['admin']['admin_id'] . '.csv';
            $save_file_name = $save_dir . $save_name;
            move_uploaded_file($_FILES['upload_file']['tmp_name'], $save_file_name);
            // 检查导入的区间中有没有软件数大于区间位置数的，有的话要发邮件提醒运营
            $extent_id_arr = array();
            $email_content = '';
            foreach ($content_arr as $content_record) {
                $extent_id = $content_record['extent_id'];
                $start_at = strtotime($content_record['start_at']);
                $end_at = strtotime($content_record['end_at']);
                if (array_key_exists($extent_id, $extent_id_arr)) {
                    if ($start_at < $extent_id_arr[$extent_id]['start_at']) {
                        $extent_id_arr[$extent_id]['start_at'] = $start_at;
                    }
                    if ($end_at > $extent_id_arr[$extent_id]['end_at']) {
                        $extent_id_arr[$extent_id]['end_at'] = $end_at;
                    }
                } else {
                    $extent_id_arr[$extent_id] = array(
                        'start_at' => $start_at,
                        'end_at' => $end_at,
                    );
                }
            }
            $this->send_size_notice_email($extent_id_arr);
            $this->writelog("市场首页软件列表：批量导入了{$save_file_name}。");
            // 返回数据给页面
            if ($flag&&$no_resource_flag) {
                $this->ajaxReturn("",'导入成功！', 0);
            } elseif(!$flag&&$no_resource_flag){
                $this->ajaxReturn(array('result_arr'=>$result_arr),'存在部分导入失败记录！', -6);
            } elseif($flag&&!$no_resource_flag){
            	$this->ajaxReturn(array('no_resource_arr'=>$no_resource_arr),'存在未关联资源库id的记录！', -7);
            } elseif(!$flag&&!$no_resource_flag){
            	$this->ajaxReturn(array('result_arr'=>$result_arr, 'no_resource_arr'=>$no_resource_arr),'同时存在', -8);
            }
        } else {
            $this->display("import_softs");
        }
    }

    // 业务逻辑：将批量导入文件里所有数据添加进数据库，返回结果为每一行添加是否成功标志符
    function process_import_array($content_arr) {
        $result_arr = array();
        $no_resource_arr = array();
        $model = M('extent_soft_v1');
        $AdSearch = D("Sj.AdSearch");
        $arr_shields = array();
        foreach($content_arr as $key => $record) {
            // 根据条件忽视以下值
            if ($record['phone_dis'] == 1) {
                // 忽视old_phone字段（显示在旧版本中）的值
                unset($record['old_phone']);
            }
            $map = array();
            // 设置默认值
			$map['status'] = 1;
			$map['create_at'] = time();
			$map['update_at'] = time();
            $map['admin_id'] = $_SESSION['admin']['admin_id'];
            // 赋值，以下为必填的值
			$map['extent_id'] = $record['extent_id'];
			$map['package'] = $record['package'];
			$map['phone_dis'] = $record['phone_dis'];
			$map['prob'] = $record['prob'];
			$map['start_at'] = strtotime($record['start_at']);
			$map['end_at'] = strtotime($record['end_at']);
            // 赋值，以下为非必填项，有默认值
            $map['type'] = isset($record['type']) ? $record['type'] : 0;
            $map['old_phone'] = isset($record['old_phone']) ? $record['old_phone'] : 0;
            //添加行为id added by shiting
            $map['beid'] =  isset($record['beid'])&&$record['beid']? $record['beid'] :0;

            $data_error=$AdSearch->pub_check_soft_filter($map['package']);
            if($data_error && $data_error['code']==1){
            	$result_arr[$key]=array('flag'=>0,'msg'=>$data_error['message'],'package'=>$map['package']);
            	$arr_shields[]=$map;
            	continue;
            }
            
            /*if( $record['used_id'] || $record['content_title'] ) {
            	$conetnt_map = array();
            	$used_data = array('content_type'=>9,'used_id'=>$record['used_id']);
            	$rcontent_1 = ContentTypeModel::saveRecommendContent($used_data,'',$conetnt_map);
            	if($rcontent_1==true){
            		$conetnt_map['create_at'] = time();
            		$conetnt_map['update_at'] = time();
            		$conetnt_id = M('')->table('sj_common_jump')->add($conetnt_map);
            		$map['content_title'] = $record['content_title'];
            		$map['content_id_1'] = $conetnt_id;
            		$map['is_dev'] = 1;
            		$map['is_tag'] = 0;
            	}else {
            		$result_arr[$key]=array('flag'=>0,'msg'=>'导入单软件（列表单图）失败','package'=>$map['package']);
            		$arr_shields[]=$map;
            		continue;
            	}
            }*/
            // 匹配资源库id开始
            $resource_id = 0;
           	$where['package_643'] = $record['package'];
            $where['status'] = 1;
            $where['resource_type'] = 1;
            if($record['content_type'] != 0){
            	$where['content_type'] = $record['content_type'];
            	//根据包名、资源类型、内容类型确定唯一记录
            	$common_jump_one = M('')->table('sj_common_jump')->field('id')->where($where)->find();
            	if(!empty($common_jump_one)){
            		$resource_id = $common_jump_one['id'];
            	}
            }
            if($record['content_type']==0){
            	if(isset($where['content_type'])){
            		unset($where['content_type']);
            	}
            	//默认规则匹配
        		$common_jump = $model->table('sj_common_jump')->field('id,content_type')->where($where)->select();
				if(!empty($common_jump)){
					$tmp_arr = array();
					foreach($common_jump as $val){
						$tmp_arr[$val['content_type']] = $val['id'];
					}
					foreach(array(9,1,2,4,5,3,6,7) as $val){
						if(array_key_exists($val, $tmp_arr)){
							$resource_id = $tmp_arr[$val];
							break;
						}
					}
				}
            }
            if(!empty($resource_id)){
				$map['resource_id'] = $resource_id;
			}else{
				$no_resource_arr[$key] = array('flag'=>0, 'msg'=>$map['package'].'软件资源库中无对应内容类型内容', 'package'=>$map['package']);
			}
			// 匹配资源库id结束

            // 添加到表中
			if ($id = $model->add($map)) {
				$this->writelog("在新版推荐位中区间[{$record['extent_id']}]中添加了软件[{$record['package']}],显示概率为{$record['prob']},开始时间为{$record['start_at']},结束时间为{$record['end_at']},合作方式为{$record['type']}", 'sj_extent_soft_v1', $id, __ACTION__, "", "add");
				$result_arr[$key]['flag'] = 1;
            	$result_arr[$key]['msg'] = "添加成功";
            	$result_arr[$key]['package'] = 0;
			}/*else{
				$this->error('添加失败');
                //未知原因添加失败
                $result_arr[$key]['flag'] = 0;
                $result_arr[$key]['msg'] = "添加失败";
                $result_arr[$key]['ta_id']=0;
			}*/
        }
        if(count($arr_shields) && $file_data=$AdSearch->generate_ignore_file($arr_shields,'sj_extent_soft_v1')){
        	$result_arr['table_name'] = 'sj_extent_soft_v1';
        	$result_arr['filename'] = $file_data['filename'];
        }
        return array($result_arr, $no_resource_arr);
    }
    //忽略屏蔽new
	public function pub_shield_new_ignore(){
		$table_name=$_GET['table_name'];
		$file_name=$_GET['file_name'];
		$model = M('');
		 $table_config=array(
            "sj_download_recommend_soft"=>array("sj_download_recommend_soft",'start_tm','end_tm','下载推荐','package'),
            "sj_think_words"=> array("sj_think_words",'start_time','end_time',"搜索suggest设置",'package'),
            "sj_animation_ad"=>array("sj_animation_ad",'start_at','end_at',"悬浮窗管理",'package'),
            // "3"=>array("sj_feature_soft",'start_tm','end_tm',"专题配置",'package'),
            "sj_animation_ad"=>array("sj_animation_ad",'start_tm','end_tm',"活动分区",'package'),
            "sj_extent_soft_v1"=>array("sj_extent_soft_v1",'start_at','end_at',"市场首页软件列表",'package'),
            "sj_category_extent_soft"=> array("sj_category_extent_soft",'start_at','end_at',"频道列表软件推荐",'package'),
            // "7"=>array("sj_soft_recommend",'start_tm','end_tm',"软件推荐设置",'soft_package'),
            "sj_lading_soft"=>array("sj_lading_soft",'start_tm','end_tm',"一键装机运营",'package'),
            "sj_ad_new"=>array("sj_ad_new",'start_tm','end_tm',"新版轮播图",'package'),
            "sj_splash_manage"=> array("sj_splash_manage",'start_tm','end_tm',"闪屏管理",'package'),
            "sj_flexible_extent_soft"=>array("sj_flexible_extent_soft",'start_at','end_at',"灵活运营样式",'package'),
            "sj_search_keywords"=>array("sj_search_keywords",'start_tm','end_tm',"搜索热词推荐",'package'),
            "sj_text_page"=> array("sj_text_page",'start_time','end_time',"文字链推广位",'package'),
            "sj_return_back_ad"=> array("sj_return_back_ad",'start_at','end_at',"返回运营",'package'),
            "sj_necessary_extent_soft"=>array("sj_necessary_extent_soft",'start_at','end_at',"必备频道软件推荐",'package'),
            "sj_search_key_package"=>array("sj_search_key_package",'start_tm','stop_tm',"搜索结果运营",'package'),
            "sj_search_tips_package"=>array("sj_search_tips_package",'start_tm','end_tm',"搜索提示运营",'package'),
            "sj_market_push"=>array("sj_market_push",'start_tm','end_tm',"市场手机---PUSH",'soft_package'),
            "sj_soft_associate_hot_word"=>array("sj_soft_associate_hot_word",'begin','end',"搜索阿拉丁",'package'),
            "sj_search_related_content"=>array("sj_search_related_content",'start_tm','end_tm',"搜索相关词管理",'package'),
            "sj_desk_icon"=>array("sj_desk_icon",'start_time','end_time',"桌面预下载配置",'package'),
            "sj_custom_push_silence"=>array("sj_custom_push_silence",'start_at','end_at',"静默下载安装",'silence_package'),
            "sj_custom_push"=>array("sj_custom_push",'start_at','end_at',"定制推送列表",'package'),
        );
		if($packages=$_POST['packages']){
			$packages_one=trim($packages,',');
			$packages=explode ( ",",$packages_one );
			//读取文件 入库
			$file = "/tmp/shield_failure/".$file_name;
	        $file_hwnd=fopen($file,"r");
			$content = fread($file_hwnd, filesize($file)); // 读去文件全部内容
			fclose($file_hwnd);
			unlink($file);
			$content_arr = unserialize($content);
	        $ids=array();
	        
	        if($table_name=='sj_soft_associate_hot_word'){
	        	$ids=$this->soft_hot_words_add($content_arr,$packages);
	        }else if($table_name=='sj_search_key_package'){
	        	$ids=$this->process_import_array_key_package($content_arr,$packages);
	        }else{
	        	foreach($content_arr as $v){
		        	if(in_array($v[$table_config[$table_name][4]], $packages)){
		        		$ids[]=$model->table($table_name)->add($v);
		        	}
		        }
	        }
			if(count($ids)){

				$ids=implode ( "," ,  $ids );
				$place=$table_config[$table_name][3];
				$this->writelog("{$place}列表：批量导入了id为{$ids}的数据",$table_name,$ids,__ACTION__ ,"","add");
				if($table_name=='sj_flexible_extent_soft'){
					$this->assign('jumpUrl', '/index.php/Sj/FlexibleExtent/index');
				}else{
					$this->assign('jumpUrl', '/index.php/Sj/AdList/index');
				}
				
				$this->success("忽略成功");
			}
		}
	}
    // 自动检查要不要发在指定时间段内软件数超出区间位置大小的邮件
    // 参数extent_info_arr是个二维数组，格式大致为array('$extent_id'=>array('extent_id'=>$extent_id, 'start_at'=>$start_at, 'end_at'=>$end_at))
    function send_size_notice_email($extent_info_arr) {
        if (!$extent_info_arr || empty($extent_info_arr))
            return false;
        $email_content = '';
        $model = M();
        foreach ($extent_info_arr as $extent_id => $extent_info) {
            // 先根据extent_id获得区间信息，最主要是获得区间名称及区间大小
            $where = array(
                'status' => 1,
                'extent_id' => $extent_id
            );
            $extent = $model->table('sj_extent_v1')->where($where)->find();
            if (!$extent)
                continue;
            $extent_name = $extent['extent_name'];
            // 获得区间位置数
            if ($extent['type'] == 3) {
                // 所有子区间的大小才构成联合区间的大小
                $where = array(
                    'status' => 1,
                    'type' => array('NEQ', 3),
                    'parent_union_id' => $extent_id,
                );
                $result = $model->table('sj_extent_v1')->field('sum(extent_size) as c')->group('parent_union_id')->where($where)->find();
                $extent_size = $result['c'];
            } else {
                $extent_size = $extent['extent_size'];
            }

            // 开始检查数量
            if (!isset($extent_info['start_at']) || !isset($extent_info['end_at']))
                continue;
            $orginal_start_at = $extent_info['start_at'];
            $orginal_end_at = $extent_info['end_at'];

            // 先将start_at和end_at转成其当天的最早点和最晚点
            $real_start_at = date("Y/m/d", $orginal_start_at);
            $real_start_at = strtotime($real_start_at);

            $real_end_at = date("Y/m/d 23:59:59", $orginal_end_at);
            $real_end_at = strtotime($real_end_at);

            $current = $real_start_at;
            // 记算指定时间范围内的每天的凌晨
            $time_arr = array();
            for ($current = $real_start_at; $current < $real_end_at; $current += 86400) {
                $time_arr[] = $current;
            }
            // 获得和每天的区间软件数，把超出区间大小的排期日期给记录下来
            $exceed_soft_counts_arr = array();
            foreach ($time_arr as $start_at) {
                $end_at = $start_at + 86399;
                // 分别查询不区分高低配、高配、低配的数量
                $common_where = array(
                    'extent_id' => $extent_id,
                    'start_at' => array('elt',$end_at),
                    'end_at' => array('egt',$start_at),
                    'status' => 1
                );
                $nodis_where = array_merge($common_where, array('phone_dis' => 1));
                $high_where = array_merge($common_where, array('phone_dis' => 2));
                $low_where = array_merge($common_where, array('phone_dis' => 3));
                $nodis_count = $model->table('sj_extent_soft_v1')->where($nodis_where)->count();
                $high_count = $model->table('sj_extent_soft_v1')->where($high_where)->count();
                $low_count = $model->table('sj_extent_soft_v1')->where($low_where)->count();
                $soft_counts = $nodis_count + max($high_count, $low_count);
                if ($soft_counts > $extent_size) {
                    $exceed_soft_counts_arr[$start_at] = $soft_counts;
                }
            }
            // 组织语言准备发邮件
            if (empty($exceed_soft_counts_arr)) {
                continue;
            }
            $email_content .= "区间id为【{$extent_id}】，区间名为【{$extent_name}】的区间，";
            foreach ($exceed_soft_counts_arr as $start_at => $soft_counts) {
                $start_at_str = date('Y/m/d', $start_at);
                $email_content .= "{$start_at_str}，";
            }
            $email_content .= '其区间软件数大于区间位置数。<br/>';
        }
        if (!$email_content)
            return true;
        // 发邮件提醒运营人员
        $emailmodel = D("Dev.Sendemail");
        $email_config_find = $emailmodel->table('pu_config')->where(array('config_type'=> 'EXTENDV1_EMAIL_SEND', 'status'=> 1))->find();
        if (!$email_config_find || !$email_config_find['configcontent'])
            return false;
        $subject = '市场首页软件列表';
        $ret = $emailmodel->realsend($email_config_find['configcontent'], $email_config_find['configcontent'], $subject, $email_content);
        return $ret;
    }
    // 校验软件屏蔽列表--new
    function pub_check_soft_filter(){
    	header('Content-Type: text/html; charset=utf-8');
    	$model = M('soft_filter');
    	$where=array('status' => 1,'package'=>trim($_GET['package']));
    	$time=time();
    	$where['end_tm']=array('egt',$time.' && start_tm<='.$time);
    	$data = $model->where($where)->find();
    	if($data){
    		echo $data['package']."软件处于<市场运营基础配置-软件屏蔽列表_new>。";
    	}
    }
    function soft_hot_words_add($content_arr, $packages){
    	$ids=array();
    	$result_arr = array();
        $model = M('soft_associate_hot_word');
    	foreach($content_arr as $key => $record) 
		{
			if(!in_array(trim($record['package']), $packages)){
        		continue;
        	}
		    $map = array();
		    // 设置默认值
			$map['stat'] = 1;
			$map['create_time'] = time();
			$map['update_time'] = time();
			$map['admin_id'] = $_SESSION['admin']['admin_id'];
		    // 赋值，以下为必填的值
			if($record['begin']=="")
			{
			  $record['begin']=date('Y-m-d H:i:s',time());
			}
			if($record['end']=="")
			{
			  $record['end']=date('Y-m-d H:i:s',time());
			}
			$map['package'] = trim($record['package']);
			$map['recommend'] = trim($record['recommend']);
			$map['associate'] = trim($record['associate']);
			$map['begin'] = strtotime($record['begin']);
			$map['end'] = strtotime($record['end']);
		    $map['background'] ="";
			$map['publicimg'] ="";
			$map['type'] = isset($record['type']) ? $record['type'] : 0;
			$ids[] = $model->add($map);
			
			//同步到搜索结果中
			//搜索结果运营有关键字但是没包名，添加的软件是往该关键字中添加
			$key_arr=explode(";",trim($record['associate']));
			if($record['key_ids_words'])
			{
				foreach($record['key_ids_words'] as $k =>$v)
				{
					$map_package=array(
						'kid'=>$k,
						'status' =>1,
						'package'=>$map['package'],
						'weight'=>'',
						'pos'=>1,
						'pid'=>'',
						'type'=>0,
						'create_tm' =>time(),
						'update_tm' =>time(),
						'start_tm' =>$map['begin'],
						'stop_tm' =>$map['end'],
						'pid' =>1,
						'co_type'=>$map['type'],
						'admin_id'=>$_SESSION['admin']['admin_id'],
					);
					$result_package=$model -> table('sj_search_key_package')->add($map_package);
					if($result_package)
					{
						$this->writelog('搜索关键字管理_从搜索阿拉丁同步到搜索结果软件列表（忽略屏蔽）_同步关键字 软件'.$result_package.'包名为'.$map['package'],"sj_search_key_package",$result_package,__ACTION__ ,"","add");
					}
				}
				$key_diff = array_diff(array_filter($key_arr),$record['key_ids_words']);
			}
			else
			{
				$key_diff = array_filter($key_arr);
			}
			//其余关键字 添加关键字列表 同时添加关键字包名
			foreach($key_diff as $k => $val)
			{
				$map_key=array(
					'srh_key'=>$val,
					'status' =>1,
					'create_tm' =>time(),
					'update_tm' =>time(),
					'start_tm' =>$map['begin'],
					'stop_tm' =>$map['end'],
					'pid' =>1,
					'admin_id'=>$_SESSION['admin']['admin_id'],
				);
				$result_key=$model -> table('sj_search_key')->add($map_key);
				$map_package=array(
					'kid'=>$result_key,
					'status' =>1,
					'package'=>$map['package'],
					'weight'=>'',
					'pos'=>1,
					'pid'=>'',
					'type'=>0,
					'create_tm' =>time(),
					'update_tm' =>time(),
					'start_tm' =>$map['begin'],
					'stop_tm' =>$map['end'],
					'pid' =>1,
					'co_type'=>$map['type'],
					'admin_id'=>$_SESSION['admin']['admin_id'],
				);
				$result_package=$model -> table('sj_search_key_package')->add($map_package);
				if($result_key)
				{
					$this->writelog("搜索关键字管理_从搜索阿拉丁同步到搜索结果运营关键字列表（忽略屏蔽无关键字）_同步关键字{$val}id:".$result_key,"sj_search_key",$result_key,__ACTION__ ,"","add");
				}
				if($result_package)
				{
					$this->writelog('搜索关键字管理_从搜索阿拉丁同步到搜索结果运营软件列表（忽略屏蔽无关键字）_同步关键字 软件'.$result_package.'包名为'.$map['package'],"sj_search_key_package",$result_package,__ACTION__ ,"","add");
				}
			}
		}
		return $ids;
    }
    function process_import_array_key_package($content_arr, $packages) {
        $result_arr = array();
        $model = M('search_key_package');
        $SwModel = D('Sj.Search_weight');
        $ids=array();
        foreach($content_arr as $key => $record) {
        	if(!in_array(trim($record['package']), $packages)){
        		continue;
        	}
            // 根据条件忽视或设置以下值
            if ($record['type'] == 1) {
                $record['pos'] = -1;
            }
            $map = array();
            // 设置默认值
			$map['status'] = 1;
            $map['create_tm'] = time();
			$map['update_tm'] = time();
			$map['admin_id'] = $_SESSION['admin']['admin_id'];
			$srh_where =array(
				'srh_key' => $record['srh_key'],
				'status' =>1,
			);
			$srh_result = $SwModel ->getSrh_key($srh_where);
			if(!$srh_result)
			{
				$id = $SwModel ->add_srh($record['srh_key']);
				$this->writelog("搜索关键字管理_搜索关键字列表（忽略屏蔽）_添加关键字【{$record['srh_key']}】id:【".$id,"】sj_search_key","sj_search_key",$id,__ACTION__ ,"","add");
				$map['kid'] = $id;
			}
			else
			{
				$map['kid'] = $srh_result[0]['id'];
			}
			$map['package'] = $record['package'];
			$map['beid'] = trim($record['beid']);
			$map['pos'] = $record['pos'];
			$map['start_tm'] = strtotime($record['start_tm']);
			$map['stop_tm'] = strtotime($record['stop_tm']);
            $map['type'] = $record['type'];
			$map['co_type'] = isset($record['co_type']) ? $record['co_type'] : 0;

			//排序为1的同步到阿拉丁
			if($map['pos']==1)
			{
				$map_hot=array(
					'package' =>$map['package'],
					'associate' =>";".$record['srh_key'].";",
					'begin' =>$map['start_tm'],
					'end' =>$map['stop_tm'],
					'create_time' =>time(),
					'update_time' =>time(),
					'stat' =>1,
					'type' =>$map['co_type'],
					'admin_id'=>$_SESSION['admin']['admin_id'],
				);
				$result= $model ->table('sj_soft_associate_hot_word')->add($map_hot);
			}
			if($result)
			{
				$this->writelog("搜索关键字管理-从搜索结果运营同步到搜索阿拉丁（忽略屏蔽）-同步包名'".$map['package']."'的关联词为'".$record['srh_key'],"sj_soft_associate_hot_word",$result,__ACTION__ ,"","add");
			}
		
            // 添加到表中
           $ids[] = $model->add($map);
			// if ($id = $model->add($map)) {
				// $this->writelog('搜索关键字管理_搜索关键字列表_添加关键字'.$record['srh_key'].' 软件'.$id.'包名为'.$map['package'],"sj_soft_associate_hot_word",$result,__ACTION__ ,"","add");
                // $result_arr[$key]['flag'] = 1;
                // $result_arr[$key]['msg'] = "添加成功";
			// }
        }

        return $ids;
    }
}
