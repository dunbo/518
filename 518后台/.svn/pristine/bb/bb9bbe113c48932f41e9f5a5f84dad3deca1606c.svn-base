<?php
class EverybodySaidAction extends CommonAction {
   //大家说  内容配置
    public function content_list()
	{
        $model = M();
		$label_model = D("Dev.Label");
        $where = array();
        $where['_string'] = "status = 1 ";
		if(!empty($_GET['search_label_name']))
		{
            $where['_string'] .="and label_id = {$_GET['search_label_name']}";
            $this->assign("search_label_name",$_GET['search_label_name']);
        }
	
        // 没有软件/专题名称查询的数据
        $list = $model->table('sj_dev_everybody_said')->where($where)->order("rank desc")->select();
        // 处理list 根据标签id获取标签  根据评论ID 获取用户名、软件/专题名称和评论内容
        foreach ($list as $key => $value) 
		{
			$label_id[] = $value['label_id'];
			$comment_id[] = $value['comment_id'];
        }
		 //获取标签
		$where_label=array(
			'id' => array('in',$label_id),
			'status' =>1,
		);
		$labelinfo = get_table_data($where_label,"sj_dev_label","id","id,label_name");
		$this->assign('labelinfo', $labelinfo);
		
		//获取评论的softid 用户名和评论
		$where_comment=array(
			'id' => array('in',$comment_id),
			'status' =>1,
		);
		$commentinfo = get_table_data($where_comment,"sj_soft_comment","id","id,softid,user_name,content,comment_type");
		
		foreach ($commentinfo as $key => $v) 
		{
			if($v['comment_type']==1)//软件ID
			{
				$soft_id[] = $v['softid'];
			}
			if($v['comment_type']==2)
			{
				$feature_id[] = $v['softid'];//专题id
			}
        }
		//获取软件名称
		$where_soft=array(
			'softid' => array('in',$soft_id),
		);
		$softinfo = get_table_data($where_soft,"sj_soft","softid","softid,softname");
		//获取专题名称
		$where_feature=array(
			'feature_id' => array('in',$feature_id),
		);
		$featureinfo = get_table_data($where_feature,"sj_feature","feature_id","feature_id,name");
		
		foreach ($commentinfo as $key => $v) 
		{
			if(in_array($v['softid'],$soft_id))
			{
				$commentinfo[$key]['soft_feature_name'] = $softinfo[$v['softid']]['softname'];
			}
			if(in_array($v['softid'],$feature_id))
			{
				$commentinfo[$key]['soft_feature_name'] = $featureinfo[$v['softid']]['name'];
			}
        }
		$this->assign('commentinfo', $commentinfo);
		//软件/专题名称 模糊查询
 		if(!empty($_GET['search_name']))
		{
			$search_comment_id =array();
			foreach ($commentinfo as $key => $v) 
			{
				$name_arr = $label_model->utf8_str_split($v['soft_feature_name']);//分割成一个字一个字的数组
				$search_arr = $label_model->utf8_str_split($_GET['search_name']);
				if(array_intersect($name_arr,$search_arr))
				{ 	
					//有交集 记下comment_id
					$search_comment_id[] = (string)$key;
				}
			}
			$search_comment_str = implode(",",$search_comment_id);
			$where['_string'] .=" and comment_id in ({$search_comment_str})";
			$this->assign("search_name",$_GET['search_name']);
        }
		
		// 分页
        import("@.ORG.Page");
        $limit = 10;
        $count = $model->table('sj_dev_everybody_said')->where($where)->count();
        $page  = new Page($count, $limit);
        // 当前页数据
        $list_result = $model->table('sj_dev_everybody_said')->where($where)->order("rank desc")->limit($page->firstRow . ',' . $page->listRows)->select();
		
        $this->assign('list', $list_result);
        $this->assign("page", $page->show());
		//标签展示
		$label_list = $label_model->get_label_list();
		$this->assign("label_list",$label_list);
        $this->display('');
    }
    
    public function add_content() 
	{
		$model = M();
		$label_model = D("Dev.Label");
        if($_POST) 
		{
            $map = array();
			$label_id = trim($_POST['label_name']);
			$rank = trim($_POST['rank']);
		    $comment_id = trim($_POST['comment_id']);
			//同一评论只能添加一个标签
			if($comment_id)
			{
				$is_have_label = $label_model->check_repeat('sj_dev_everybody_said','comment_id',$comment_id,'');
				if($is_have_label)
				{
					$this->error("该评论已经添加大家说");
				}
				else
				{
					$map['comment_id']=$comment_id;
				}
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
					//同一标签下排序不能相同
					$is_have = $label_model->check_repeat_special('sj_dev_everybody_said','rank',$rank,'label_id',$label_id,'');
					if($is_have)
					{
						$this->error("该标签下已经存在此排序");
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
			//根据label_id获取标签的排序
			$label_where=array(
				'id' => $label_id,
			);
			$label_result = $model->table('sj_dev_label')->where($label_where)->find();
			$map['label_rank'] = $label_result['rank'];
			
			$map['label_id'] = $label_id;
			$map['create_time']=time();
			$map['update_time']=time();
			$map['status']=1;
			$map['admin_id'] = $_SESSION['admin']['admin_id'];
            // 添加
            $ret = $model->table('sj_dev_everybody_said')->add($map);
			//同时 在软件评论表中id=$coment_id中增加大家说ID 
			$comment = array();
			$comment['everybody_said_id'] = $ret;
			$result = $model->table('sj_soft_comment') -> where(array('id' => $comment_id)) ->save($comment);
            if ($ret&&$result) {
                $this->writelog("大家说内容管理：添加了id为{$ret}的记录",'sj_dev_everybody_said', $comment_id,__ACTION__ ,"","add");
                $this->success("添加成功！");
            } else {
                $this->error("添加失败");
            }
        }
		elseif($_GET) 
		{
			//标签展示
			$label_list = $label_model->get_label_list();
			$this->assign("label_list",$label_list);
			//soft_comment表的id
			$comment_id = $_GET['id'];
			$this->assign("comment_id",$comment_id);
            $this->display();
        }
    }

	public function edit_content() 
	{
		$model = M();
		$label_model = D("Dev.Label");
        if ($_POST) 
		{
            $id = $_POST['id'];
			$map = array();
			$label_id = trim($_POST['label_name']);
			$rank = trim($_POST['rank']);
			
			if($rank)
			{
				$matches = "/^[1-9][0-9]*$/";
				if (!preg_match($matches,$rank)) 
				{
					$this -> error("排序为正整数");
				}
				else
				{
					$is_have = $label_model->check_repeat_special('sj_dev_everybody_said','rank',$rank,'label_id',$label_id,$id);
					if($is_have)
					{
						$this->error("该标签下已经存在此排序");
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
			$map['label_id'] = $label_id;
			$map['rank'] =$rank;
			$map['update_time']=time();
			$map['admin_id'] = $_SESSION['admin']['admin_id'];
            // 编辑
            $where = array(
                'id' => $id
            );
            $log = $this->logcheck($where, 'sj_dev_everybody_said', $map, $model);
			
			$ret = $model->table('sj_dev_everybody_said')->where($where)->save($map);
			if ($ret || $ret === 0) {
				if ($ret) {
					$this->writelog("大家说内容管理：编辑了id为{$id}的记录，{$log}",'sj_dev_everybody_said', $id,__ACTION__ ,"","edit");
				}
				$this->success("编辑成功！");
			} else {
				$this->error("编辑失败");
			}
        } 
		else 
		{
            $id = $_GET['id'];
            $find = $model->table('sj_dev_everybody_said')->where("id = {$id}")->find();
			//标签展示
			$label_list = $label_model->get_label_list();
			$this->assign("label_list",$label_list);
			
            $this->assign("result", $find);
            $this->display();
        }
    }
    
    public function delete_content() 
	{
        $model = M();
        $id = $_GET['id'];
		$comment_id = $_GET['comment_id'];
        $data['status'] = 0;
		$data['update_time'] = time();
        $del = $model->table('sj_dev_everybody_said')->where("id = {$id}")->save($data);
		//同步删除软件评论的大家说ID
		$comment=array();
		$comment['everybody_said_id'] = 0;
		$result = $model->table('sj_soft_comment')->where("id = {$comment_id}")->save($comment);
        if($del&&$result) {
            $this->writelog("大家说内容配置：删除了id为{$id}的记录",'sj_dev_everybody_said', $id,__ACTION__ ,"","del");
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
		$label_id = $arr[2];
		$rank = $_GET['rank'];
		//判断数据库是否有重复排序 check_repeat_special
		$is_have = $label_model->check_repeat_special('sj_dev_everybody_said','rank',$rank,'label_id',$label_id,$id);
		if($is_have!=1)
		{
			$data['rank'] = $rank;
			$data['update_time'] = time();
			$log_result = $this -> logcheck(array('id' => $id),'sj_dev_everybody_said',$data,$model);
			$result = $model -> table('sj_dev_everybody_said') -> where(array('id' => $id)) -> save($data);
			if($result)
			{
				$this -> writelog("已编辑id为{$id}的大家说内容信息".$log_result,'sj_dev_everybody_said', $id,__ACTION__ ,"","edit");
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
