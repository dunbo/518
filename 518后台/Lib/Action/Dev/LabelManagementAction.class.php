<?php
class LabelManagementAction extends CommonAction {
   //大家说  标签配置
    public function label_list()
	{
        $model = M();
        $where = array();
        $where['status'] = 1;
        // 分页
        import("@.ORG.Page");
        $limit = 10;
        $count = $model->table('sj_dev_label')->where($where)->count();
        $page  = new Page($count, $limit);
        // 当前页数据
        $list = $model->table('sj_dev_label')->where($where)->order("rank desc")->limit($page->firstRow . ',' . $page->listRows)->select();
        // 处理list
        foreach ($list as $key => $value) 
		{
            
        }

        $this->assign('list', $list);
        $this->assign("page", $page->show());
        $this->display('');
    }
    
    public function add_label() 
	{
        if($_POST) 
		{
            $model = M();
			$label_model = D("Dev.Label");
            $map = array();
			$label_name = trim($_POST['label_name']);
			$rank = trim($_POST['rank']);
			if($label_name)
			{
				if(mb_strlen($label_name,"utf-8")>6)
				{
					$this->error("标签名称不能超过6个字");
				}
				else
				{
					//检查数据库是否重复
					$have_result = $label_model->check_repeat('sj_dev_label','label_name',$label_name,'');
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
			if($rank)
			{
				$matches = "/^[1-9][0-9]*$/";
				if (!preg_match($matches,$rank)) 
				{
					$this -> error("排序为正整数");
				}
				else
				{
					$is_have = $label_model->check_repeat('sj_dev_label','rank',$rank,'');
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
			$map['admin_id'] = $_SESSION['admin']['admin_id'];
			//上传图片
			$bg_pic=$_FILES['bg_pic']['name'];
			if(empty($bg_pic))
			{
				$this->error("请上传背景图片");
			}
			else
			{
				//shell 命令 s  -i -o 只能处理png的图片
				if($_FILES['bg_pic']['type'] != 'image/png')
				{
					$this->error("背景图片只上传PNG图片");
				}
			}
			$tmp_filename_xh = $_FILES["img_url_xh"]["name"];
			$tmp_filename_h = $_FILES["img_url_h"]["name"];
			$tmp_filename_m = $_FILES["img_url_m"]["name"];
			if(empty($tmp_filename_h)||empty($tmp_filename_m) ||empty($tmp_filename_xh))
			{
				$this->error("请上传图片");
			}
			$path=date("Ym/d/",time());
			$config = array(
					'multi_config' => array(
						'img_url_xh' => array(
							'savepath' => UPLOAD_PATH. '/img/'. $path,
							'saveRule' => 'getmsec',
							//'img_p_size' =>  1024*50,
						),
						'img_url_h' => array(
							'savepath' => UPLOAD_PATH. '/img/'. $path,
							'saveRule' => 'getmsec',
							//'img_p_size' =>  1024*50,
						),
						'img_url_m' => array(
							'savepath' => UPLOAD_PATH. '/img/'. $path,
							'saveRule' => 'getmsec',
							//'img_p_size' =>  1024*50,
						),
						'bg_pic' => array(
							'savepath' => UPLOAD_PATH. '/img/'. $path,
							'saveRule' => 'getmsec',
							'use_aapt' => true,
							'enable_resize' => false,
							//'img_p_size' =>  1024*50,
						),
					),
				);
			$lists=$this->_uploadapk(0, $config);
			foreach($lists['image'] as $val) {
				if ($val['post_name'] == 'img_url_xh') {
					$map['img_url_xh']= $val['url'];
				}
				if ($val['post_name'] == 'img_url_h') {
					$map['img_url_h']= $val['url'];
				}
				if ($val['post_name'] == 'img_url_m') {
					$map['img_url_m']= $val['url'];
				}
				if ($val['post_name'] == 'bg_pic') {
					$map['bg_pic']= $val['url'];
				}
			}
				
            // 添加
            $ret = $model->table('sj_dev_label')->add($map);
            if ($ret) {
                $this->writelog("标签配置：添加了id为{$ret}的记录",'sj_dev_label', $ret,__ACTION__ ,"","add");
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
		$label_model = D("Dev.Label");
        if ($_POST) 
		{
            $id = $_POST['id'];
			$map = array();
			$label_name = trim($_POST['label_name']);
			$rank = trim($_POST['rank']);
			if($label_name)
			{
				if(mb_strlen($label_name,"utf-8")>6)
				{
					$this->error("标签名称不能超过6个字");
				}
				else
				{
					//检查数据库是否重复
					$have_result = $label_model->check_repeat('sj_dev_label','label_name',$label_name,$id);
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
			if($rank)
			{
				$matches = "/^[1-9][0-9]*$/";
				if (!preg_match($matches,$rank)) 
				{
					$this -> error("排序为正整数");
				}
				else
				{
					$is_have = $label_model->check_repeat('sj_dev_label','rank',$rank,$id);
					if($is_have)
					{
						$this->error("数据库中已经存在此排序");
					}
					else
					{
						//编辑便签排序 同时也要修改 大家说列表中的标签排序
						$everybody_said_where=array(
							'status'=>1,
							'label_id' =>$id,
						);
						$find_result = $model->table('sj_dev_everybody_said')->where($everybody_said_where)->select();
						if($find_result)
						{
							foreach($find_result as $key => $val)
							{
								$label_map=array();
								$label_map['label_rank'] = $rank;
								$label_where=array(
									'label_id' =>$val['label_id'],
								);
								$update_rank = $model->table('sj_dev_everybody_said')->where($label_where)->save($label_map);
								if($update_rank)
								{
									$this->writelog("标签管理排序的改变，改变了大家说内容中的laebl_id为{$val['label_id']}的标签排序",'sj_dev_everybody_said', $val['label_id'],__ACTION__ ,"","edit");
								}
							}
						}
						$map['rank']=$rank;
					}
				}
			}
			else
			{
				$this->error("排序不能为空");
			}
			$map['update_time']=time();
			$map['admin_id'] = $_SESSION['admin']['admin_id'];
			
			$bg_pic=$_FILES['bg_pic']['size'];
			$tmp_filename_xh = $_FILES["img_url_xh"]["size"];
			$tmp_filename_h = $_FILES["img_url_h"]["size"];
			$tmp_filename_m = $_FILES["img_url_m"]["size"];
			$path=date("Ym/d/",time());
			if ($tmp_filename_h) {
				$config['multi_config']['img_url_h'] = array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					//'img_p_size' =>  1024*50,
				);
			}
			if ($tmp_filename_m) {
				$config['multi_config']['img_url_m'] = array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					//'img_p_size' =>  1024*50,
				);
			}
			if ($tmp_filename_xh) {
				$config['multi_config']['img_url_xh'] = array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					//'img_p_size' =>  1024*50,
				);
			}
			if($bg_pic) {
				//shell 命令 s  -i -o 只能处理png的图片
				if($_FILES['bg_pic']['type'] != 'image/png')
				{
					$this->error("背景图片只上传PNG图片");
				}
				$config['multi_config']['bg_pic'] = array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					'use_aapt' =>true,
					'enable_resize' => false,
					//'img_p_size' =>  1024*50,
				);
			}
		
			if(!empty($config['multi_config'])){
				$lists=$this->_uploadapk(0, $config);
				foreach($lists['image'] as $val) {
					if ($val['post_name'] == 'img_url_h') {
						$map['img_url_h']=$val['url'];
					}
					if ($val['post_name'] == 'img_url_m') {
						$map['img_url_m']= $val['url'];
					}
					if ($val['post_name'] == 'img_url_xh') {
						$map['img_url_xh']=$val['url'];
					}
					if ($val['post_name'] == 'bg_pic') {
						$map['bg_pic']=$val['url'];
					}
				}
			}
			
            // 编辑
            $where = array(
                'id' => $id
            );
            $log = $this->logcheck($where, 'sj_dev_label', $map, $model);
			
			$ret = $model->table('sj_dev_label')->where($where)->save($map);
			if ($ret || $ret === 0) {
				if ($ret) {
					$this->writelog("标签管理：编辑了id为{$id}的记录，{$log}",'sj_dev_label', $id,__ACTION__ ,"","edit");
				}
				$this->success("编辑成功！");
			} else {
				$this->error("编辑失败");
			}
        } 
		else 
		{
            $id = $_GET['id'];
            $find = $model->table('sj_dev_label')->where("id = {$id}")->find();
            $this->assign("result", $find);
            $this->display();
        }
    }
    
    public function delete_label() 
	{
        $model = M();
        $id = $_GET['id'];
        $data['status'] = 0;
		$data['update_time']=time();
        $del = $model->table('sj_dev_label')->where("id = {$id}")->save($data);
        if($del) {
            $this->writelog("标签配置：删除了id为{$id}的记录",'sj_dev_label', $id,__ACTION__ ,"","del");
            $this -> success("删除成功");
        } else {
            $this->error("删除失败");
        }
    }
    public function change_rank()
    {
		$model = M();
		$label_model = D("Dev.Label");
		$need_data=$_GET['need_data'];
		$arr = explode(',',$need_data);
		$id = $arr[0];
		$old_rank = $arr[1];
		$rank = $_GET['rank'];
		//判断数据库是否有重复排序
		$is_have = $label_model->check_repeat('sj_dev_label','rank',$rank,$id);
		if($is_have!=1)
		{
			$data['rank'] = $rank;
			$data['update_time'] = time();
			$log_result = $this -> logcheck(array('id' => $id),'sj_dev_label',$data,$model);
			//编辑便签排序 同时也要修改 大家说列表中的标签排序
			$everybody_said_where=array(
				'status'=>1,
				'label_id' =>$id,
			);
			$find_result = $model->table('sj_dev_everybody_said')->where($everybody_said_where)->select();
			if($find_result)
			{
				foreach($find_result as $key => $val)
				{
					$label_map=array();
					$label_map['label_rank'] = $rank;
					$label_where=array(
						'label_id' =>$val['label_id'],
					);
					$update_rank = $model->table('sj_dev_everybody_said')->where($label_where)->save($label_map);
					if($update_rank)
					{
						$this->writelog("标签管理排序的改变，改变了大家说内容中的laebl_id为{$val['label_id']}的标签排序",'sj_dev_everybody_said', $val['label_id'],__ACTION__ ,"","edit");
					}
				}
			}
			$result = $model -> table('sj_dev_label') -> where(array('id' => $id)) -> save($data);
			if($result)
			{
				$this -> writelog("已编辑id为{$id}的标签信息".$log_result,'sj_dev_label', $id,__ACTION__ ,"","edit");
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
?>
