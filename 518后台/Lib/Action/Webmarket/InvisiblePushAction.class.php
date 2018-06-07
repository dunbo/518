<?php

class InvisiblePushAction extends CommonAction {

    public function _initialize() {
        #屏蔽该ip绕过权限检查
        if (!isset($_POST['USEAPI']) && $_SERVER['REMOTE_ADDR'] != '192.168.1.13') {
            parent::_initialize();
        }
    }

    public function index() 
	{
       
        $model = M();
		$channel_model = M('channel');
        $where = array();
        $where['status'] = 1;
		$where['push_type'] = 4;
        // 分页
        import("@.ORG.Page");
        $limit = 10;
        $count = $model->table('sj_market_push')->where($where)->count();
        $page  = new Page($count, $limit);
        // 当前页数据
        $list = $model->table('sj_market_push')->where($where)->order("id desc")->limit($page->firstRow . ',' . $page->listRows)->select();
        // 处理list
        foreach ($list as $key => $value) 
		{
            $cid = $value['channel_id'];
			$cid_str = preg_replace('/^,/','',$cid);
			$cid_str = preg_replace('/,$/','',$cid_str);
			$array = explode(',', $cid_str);
			$cname = $channel_model->where("cid in ({$cid_str})")->findAll();
			if(!$cname)
			{
				$list[$key]['chname']="通用";
			}
			else
			{
				if(in_array('0',$array))
				{
					$list[$key]['chname'].="通用,";
				}
				foreach ($cname as $k1 => $v1) 
				{
					$list[$key]['chname'].=$v1['chname'].",";
				}
				$list[$key]['chname']=preg_replace('/,$/','',$list[$key]['chname']);
				if(mb_strlen($list[$key]['chname'],'utf-8')>70)
				{
					$list[$key]['chname']=mb_substr($list[$key]['chname'],0,70,'utf-8')."...";
				}
			}
        }

        $this->assign('list', $list);
        $this->assign("page", $page->show());
        $this->display();
    }
    public function add_content() 
	{
        if ($_POST) 
		{
            $model = M();
            $map = array();
            // 开始时间和结束时间
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
            $map['start_tm'] = $start_at;
            $map['end_tm'] = $end_at;
            // 渠道
			$channel_id_array=$_POST['cid'];
			$cids = array_unique($channel_id_array);
            if (count($cids) > 0) 
			{
                $s = implode(',', $cids);
                $s = ",{$s},";
                $map['channel_id'] = $s;
            }
			$map['create_tm']=time();
			$map['update_tm']=time();
			$map['push_type']=4;
			$map['info_type']=11;
			$map['status']=1;
			$map['push_way']=1;
			$Push = D("Sj.Push");
			$push_arr = array(
				'start_time'=>	$start_at,
				'end_time'	=>	$end_at,
			);
			$id	= $Push->addMarketOldPush($push_arr);
			if(!$id)
			{
				$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/InvisiblePush/add_content');
				$this->error("隐形push失败,发生错误！");
			}
			$map['id'] = $id;

            if ($_POST['use_new']) {
                $map['activation_time'] = 1;
                $map['activation_day_end'] = 0;
                $map['activation_day_start'] = 0;
            }

            // 添加
            $ret = $model->table('sj_market_push')->add($map);
            if ($ret) {
                $this->writelog("隐形PUSH：添加了id为{$ret}的记录",'sj_market_push',$ret,__ACTION__ ,"","add");
                $this->success("添加成功！");
            } else {
                $this->error("添加失败");
            }
        }
		else 
		{
            $this->display();
        }
    }
    
    public function edit_content() 
	{
		$model = M();
		$channel_model = M('channel');
        if ($_POST) 
		{
            $id = $_POST['id'];
            $model = M();
            $map = array();
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
            $map['start_tm'] = $start_at;
            $map['end_tm'] = $end_at;
            // 渠道
			$channel_id_array=$_POST['cid'];
			$cids = array_unique($channel_id_array);
            if (count($cids) > 0) 
			{
                $s = implode(',', $cids);
                $s = ",{$s},";
                $map['channel_id'] = $s;
            }
			$map['update_tm']=time();
            // 编辑
            $where = array(
                'id' => $id
            );
            $log = $this->logcheck($where, 'sj_market_push', $map, $model);
			
			$ret = $model->table('sj_market_push')->where($where)->save($map);
			if ($ret || $ret === 0) {
				if ($ret) {
					$this->writelog("隐形PUSH：编辑了id为{$id}的记录，{$log}",'sj_market_push',$id,__ACTION__ ,"","edit");
				}
				$this->success("编辑成功！");
			} else {
				$this->error("编辑失败");
			}
			
        } 
		else 
		{
            $id = $_GET['id'];
            $find = $model->table('sj_market_push')->where("id = {$id}")->find();
			$cid = $find['channel_id'];
			$cookstr = preg_replace('/^,/','', $cid);
			$cookstr = preg_replace('/,$/','', $cookstr);
			$array = explode(',', $cookstr);
			$chl = $channel_model->field("`cid`,`chname`")->where(' `cid` in (' . $cookstr . ')')->select();
			
			if (in_array("0",$array)&&$chl!=NULL)
            {
              $tong = array("cid"=> "0" ,"chname"=> "通用");
              array_unshift($chl, $tong);
            }
			if (in_array("0",$array)&&$chl==NULL)
            {
              $chl[0]['cid']="0";
              $chl[0]['chname']="通用";
            }
            $this->assign('chl_list',$chl);
            $this->assign("list", $find);
            $this->display();
        }
    }
    
    public function delete_content() 
	{
        $model = M();
        $id = $_GET['id'];
        $data['status'] = 0;
        $del = $model->table('sj_market_push')->where("id = {$id}")->save($data);
        if($del) {
            $this->writelog("隐形PUSH：删除了id为{$id}的记录",'sj_market_push',$id,__ACTION__ ,"","del");
            $this -> success("删除成功");
        } else {
            $this->error("删除失败");
        }
    }
}

?>