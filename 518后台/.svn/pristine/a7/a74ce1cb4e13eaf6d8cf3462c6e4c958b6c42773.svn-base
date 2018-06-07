<?php
class LabelManagementAction extends CommonAction {
	//图片大小
	private $xh_width = 64;
	private $xh_height = 64;
	private $h_width = 48;
	private $h_height = 48;
	private $m_width = 32;
	private $m_height = 32;
	
   //精选标签配置
    public function label_list()
	{
        $model = M();
        $where = array();
        $where['status'] = 1;
        $list = $model->table('sj_select_label')->where($where)->order("id asc")->select();
        // echo "<pre>";var_dump($list);die;

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
            $model = M();
			$label_model = D("Sj.Label");
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
					$have_result = $label_model->check_repeat('sj_select_label','label_name',$label_name,'');
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
			$map['create_time']=time();
			$map['update_time']=time();
			$map['status']=1;
			$map['admin_id'] = $_SESSION['admin']['admin_id'];
			$tmp_filename_xh = $_FILES["super_high_image"]["name"];
			$tmp_filename_h = $_FILES["high_image"]["name"];
			$tmp_filename_m = $_FILES["middle_image"]["name"];
			if(empty($tmp_filename_h)||empty($tmp_filename_m) ||empty($tmp_filename_xh))
			{
				$this->error("请上传图片");
			}
			$path=date("Ym/d/",time());
			// echo UPLOAD_PATH;die;
			$config = array(
					'multi_config' => array(
						'super_high_image' => array(
							'savepath' => UPLOAD_PATH. '/img/'. $path,
							'saveRule' => 'getmsec',
							//'img_p_size' =>  1024*50,
						),
						'high_image' => array(
							'savepath' => UPLOAD_PATH. '/img/'. $path,
							'saveRule' => 'getmsec',
							//'img_p_size' =>  1024*50,
						),
						'middle_image' => array(
							'savepath' => UPLOAD_PATH. '/img/'. $path,
							'saveRule' => 'getmsec',
							//'img_p_size' =>  1024*50,
						),
					),
				);
			$lists=$this->_uploadapk(0, $config);
						// echo "<pre>";var_dump($lists);die;
			foreach($lists['image'] as $val) {
				if ($val['post_name'] == 'super_high_image') {
					$map['super_high_image']= $val['url'];
				}
				if ($val['post_name'] == 'high_image') {
					$map['high_image']= $val['url'];
				}
				if ($val['post_name'] == 'middle_image') {
					$map['middle_image']= $val['url'];
				}
			}
				
            // 添加
            $ret = $model->table('sj_select_label')->add($map);
            if ($ret) {
                $this->writelog("市场软件运营推荐-精选标识管理：添加了id为{$ret}的记录",'sj_select_label',$ret,__ACTION__ ,"","add");
                $this->success("添加成功！");
            } else {
                $this->error("添加失败");
            }
        }
		else 
		{
			$this->assign('xh_width',$this->xh_width);
			$this->assign('xh_height',$this->xh_height);
			$this->assign('h_width',$this->h_width);
			$this->assign('h_height',$this->h_height);
			$this->assign('m_width',$this->m_width);
			$this->assign('m_height',$this->m_height);
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
			if($label_name)
			{
				if(mb_strlen($label_name,"utf-8")>6)
				{
					$this->error("标签名称不能超过6个字");
				}
				else
				{
					// 检查数据库是否重复
					$have_result = $label_model->check_repeat('sj_select_label','label_name',$label_name,$id);
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
			$map['update_time']=time();
			$map['admin_id'] = $_SESSION['admin']['admin_id'];
			// $bg_pic=$_FILES['bg_pic']['size'];
			$tmp_filename_xh = $_FILES["super_high_image"]["size"];
			$tmp_filename_h = $_FILES["high_image"]["size"];
			$tmp_filename_m = $_FILES["middle_image"]["size"];
			$path=date("Ym/d/",time());
			if ($tmp_filename_h) {
				$config['multi_config']['super_high_image'] = array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					//'img_p_size' =>  1024*50,
				);
			}
			if ($tmp_filename_m) {
				$config['multi_config']['high_image'] = array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					//'img_p_size' =>  1024*50,
				);
			}
			if ($tmp_filename_xh) {
				$config['multi_config']['middle_image'] = array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					//'img_p_size' =>  1024*50,
				);
			}
		
			if(!empty($config['multi_config'])){
				$lists=$this->_uploadapk(0, $config);
				foreach($lists['image'] as $val) {
					if ($val['post_name'] == 'high_image') {
						$map['high_image']=$val['url'];
					}
					if ($val['post_name'] == 'middle_image') {
						$map['middle_image']= $val['url'];
					}
					if ($val['post_name'] == 'super_high_image') {
						$map['super_high_image']=$val['url'];
					}
				}
			}
			
            // 编辑
            $where = array(
                'id' => $id
            );
            $log = $this->logcheck($where, 'sj_select_label', $map, $model);
			
			$ret = $model->table('sj_select_label')->where($where)->save($map);
			if ($ret || $ret === 0) {
				if ($ret) {
					$this->writelog("市场软件运营推荐-精选标识管理：编辑了id为{$id}的记录，{$log}",'sj_select_label',$id,__ACTION__ ,"","edit");
				}
				$this->success("编辑成功！");
			} else {
				$this->error("编辑失败");
			}
        } 
		else 
		{
            $id = $_GET['id'];
            $find = $model->table('sj_select_label')->where("id = {$id}")->find();
            $this->assign("result", $find);
			$this->assign('xh_width',$this->xh_width);
			$this->assign('xh_height',$this->xh_height);
			$this->assign('h_width',$this->h_width);
			$this->assign('h_height',$this->h_height);
			$this->assign('m_width',$this->m_width);
			$this->assign('m_height',$this->m_height);
            $this->display();
        }
    }
    
    public function delete_label() 
	{
        $model = M();
        $id = $_GET['id'];
        $data['status'] = 0;
		$data['update_time']=time();
        $error=$this->is_del($id);
        if($_GET['count']==1){
        	$this->error("标识最多20个，最少为1个，不能为空");
        }
        if(!empty($error)){
        	$this->error($error);
        }
        $del = $model->table('sj_select_label')->where("id = {$id}")->save($data);
        if($del) {
            $this->writelog("市场软件运营推荐-精选标识管理：删除了id为{$id}的记录",'sj_select_label',$id,__ACTION__ ,"","del");
            $this -> success("删除成功");
        } else {
            $this->error("删除失败");
        }
    }
	public function is_del($id){
		$model = M();
		// $id=$_GET['id'];
		if(!empty($id)){
			$where=array();
	        $where['status'] = 1;
	        $where['is_show'] = 1;
	        $where['select_label_id'] = $id;
	        $res= $model->table('sj_perfect_soft')->where($where)->select();
	        if(!empty($res)){
	        	return "精选标示下有精选页面，不允许删除";
	        }
		}
	}
}
?>
