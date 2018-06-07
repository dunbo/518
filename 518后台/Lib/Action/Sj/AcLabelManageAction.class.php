<?php
class AcLabelManageAction extends CommonAction {
   //精选标签配置
    public function label_list()
	{	
        $model = M();
        $where = array();
        $where['status'] = 1;
        $list = $model->table('sj_active_filter_label')->where($where)->order("id asc")->select();
        // echo "<pre>";var_dump($list);die;
        // 处理list
        foreach ($list as $key => $value) 
		{
            
        }
        $this->assign('list', $list);
        $this->assign('count', count($list));
        $this->display('');
    }
    
    public function add_label() 
	{
        if($_POST) 
		{
			// echo "<pre>";var_dump($_POST);die;
            $model = M();
			$label_model = D("Sj.Label");
            $map = array();
			$label_name = trim($_POST['label_name']);
			$rank = trim($_POST['rank']);
			if($label_name)
			{
				if(mb_strlen($label_name,"utf-8")>8 || mb_strlen($label_name,"utf-8")<2)
				{
					$this->error("标签名称为2~8个字");
				}
				else
				{
					//检查数据库是否重复
					$have_result = $label_model->check_repeat('sj_active_filter_label','label_name',$label_name,'');
					if($have_result)
					{
						$this->error("数据库中已经存在此标签");
					}
					else
					{
						$map['label_name'] = $label_name;
					}
				}
			}
			else
			{
				$this->error("标签名称不能为空");
			}
			if(isset($_POST['rank']))
			{
				$matches = "/^[1-9][0-9]*$/";
				if (!preg_match($matches,$rank)) 
				{
					$this -> error("排序为正整数");
				}
				else
				{
					$is_have = $label_model->check_repeat('sj_active_filter_label','rank',$rank,'');
					if($is_have)
					{
						$this->error("数据库中已经存在此排序");
					}
					else
					{
						$map['rank']=$rank;
					}
				}
			}
			else
			{
				$this->error("排序不能为空");
			}
			$map['create_time']=time();
			$map['update_time']=time();
			$map['status']=1;
			if(!isset($_POST['label_color']) || $_POST['label_color']=='#FFFFFF' || $_POST['label_color']=='#ffffff'){
				$this->error("标签颜色必须选择");
			}
			$map['label_color']=trim($_POST['label_color']);
            // 添加
            $ret = $model->table('sj_active_filter_label')->add($map);
            if ($ret) {
                $this->writelog("标签配置：添加了id为{$ret}的记录", 'sj_active_filter_label',$ret,__ACTION__ ,'','add');
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

	public function edit_label() 
	{
		$model = M();
		$label_model = D("Sj.Label");
        if ($_POST) 
		{
            $id = $_POST['id'];
			$map = array();
			$label_name = trim($_POST['label_name']);
			$rank = trim($_POST['rank']);
			if($label_name)
			{
				if(mb_strlen($label_name,"utf-8")>8 || mb_strlen($label_name,"utf-8")<2)
				{
					$this->error("标签名称为2~8个字");
				}
				else
				{
					//检查数据库是否重复
					$have_result = $label_model->check_repeat('sj_active_filter_label','label_name',$label_name,$id);
					if($have_result)
					{
						$this->error("数据库中已经存在此标签");
					}
					else
					{
						$map['label_name'] = $label_name;
					}
				}
			}
			else
			{
				$this->error("标签名称不能为空");
			}
			if(isset($_POST['rank']))
			{
				$matches = "/^[1-9][0-9]*$/";
				if (!preg_match($matches,$rank)) 
				{
					$this -> error("排序为正整数");
				}
				else
				{
					$is_have = $label_model->check_repeat('sj_active_filter_label','rank',$rank,$id);
					if($is_have)
					{
						$this->error("数据库中已经存在此排序");
					}
					else
					{
						$map['rank']=$rank;
					}
				}
			}
			else
			{
				$this->error("排序不能为空");
			}
			$map['update_time']=time();
			$map['status']=1;
			$map['identification']=trim($_POST['identification']);
			if(!isset($_POST['label_color']) || $_POST['label_color']=='#FFFFFF' || $_POST['label_color']=='#ffffff'){
				$this->error("标签颜色必须选择");
			}
			$map['label_color']=trim($_POST['label_color']);
            // 编辑
            $where = array(
                'id' => $id
            );
            $log = $this->logcheck($where, 'sj_active_filter_label', $map, $model);
			
			$ret = $model->table('sj_active_filter_label')->where($where)->save($map);
			if ($ret || $ret === 0) {
				if ($ret) {
					$this->writelog("标签管理：编辑了id为{$id}的记录，{$log}", 'sj_active_filter_label',$id,__ACTION__ ,'','edit');
				}
				$this->success("编辑成功！");
			} else {
				$this->error("编辑失败");
			}
        } 
		else 
		{
            $id = $_GET['id'];
            $find = $model->table('sj_active_filter_label')->where("id = {$id}")->find();
            // echo "<pre>";var_dump($find);die;
            $this->assign("result", $find);
            $this->display();
        }
    }
    
    public function delete_label() 
	{
        $model = M();
        $id = $_GET['id'];
        $data['status'] = 0;
        $del = $model->table('sj_active_filter_label')->where("id = {$id}")->save($data);
        if($del) {
            $this->writelog("标签配置：删除了id为{$id}的记录", 'sj_active_filter_label',$id,__ACTION__ ,'','del');
            $this -> success("删除成功");
        } else {
            $this->error("删除失败");
        }
    }
    public function change_rank()
    {	
		$model = M();
		$label_model = D("Sj.Label");
		$rank = $_GET['rank'];
		$id=$_GET['id'];
		if(!empty($_GET['biaoshi'])){
			$is_have = $label_model->check_repeat('sj_active_filter_label','rank',$rank,$id);
			// echo $model->getLastSql();die;
			if(empty($is_have)){
				echo 1;
			}else{
				echo 2;
			}
		}else{
			$old_rank = $_GET['old_rank'];
			//判断数据库是否有重复排序
			$is_have = $label_model->check_repeat('sj_active_filter_label','rank',$rank,$id);
			if($is_have!=1)
			{
				$data['rank'] = $rank;
				$data['update_time'] = time();
				$log_result = $this -> logcheck(array('id' => $id),'sj_active_filter_label',$data,$model);
				$result = $model -> table('sj_active_filter_label') -> where(array('id' => $id)) -> save($data);
				if($result)
				{
					$this -> writelog("已编辑id为{$id}的标签信息".$log_result, 'sj_active_filter_label',$id,__ACTION__ ,'','edit');
					$value=1;
				}else
				{
					$value=2;
				}
			}
			else
			{
				$value = 3;
			}
			$re_val = array(
				'value' =>$value,
				'rank' => $old_rank,
			);
			echo json_encode($re_val);
		}
	}
}
?>
