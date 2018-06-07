<?php

/**
 * 里约奥运大冒险活动拼图图片管理
 *
 */
class RioOlympicGamesAction extends CommonAction {
	private $pic_num = 8;
	//拼图图片大小
	private $image_width=285;
	private $image_height=261;
	/**
	 * 拼图图片首页，列表页
	 */
	public function index()
	{
		$model=M("rio_olympic_pics");
		import("@.ORG.Page");
		$where=array(
			'status'=>1,
		);
		$count = $model->where($where)->count();
        $param = http_build_query($_GET);
        $Page = new Page($count, 10, $param);
	    $pics_list=$model->where($where)->order("start_tm asc")->limit($Page->firstRow . ',' . $Page->listRows)->select();
	    $show = $Page->show();
        $this->assign("page", $show);
		$this->assign('pic_num',$this->pic_num);
        $this->assign("pics_list",$pics_list);
		$this->display();

	}

	public function add()
	{
		if ($_POST) 
		{  
			$model = M("rio_olympic_pics"); 
			$map['title']=trim($_POST['title']);
			$map['start_tm']=strtotime($_POST['start_tm']);
			$map['end_tm']=strtotime($_POST['end_tm']);
			$map['create_tm']=time();
			$map['update_tm']=time();
			$map['status']=1;
			
			$is_pic = false;
			for($i=1;$i<=$this->pic_num;$i++)
			{
				if(!empty($_FILES["img".$i]["name"]))
				{
					$is_pic = true;
					$img.$i_pic = getimagesize($_FILES['img'.$i]['tmp_name']);
					if($img.$i_pic[0] != $this->image_width ||$img.$i_pic[1] != $this->image_height){
						$this->error("图片{$i}：请上传{$this->image_width}*{$this->image_height}的图片");
					}
				}
			}
			$path = date('Ym/d');
			$pic_arr = array();
			if($is_pic)
			{
				$config = array(
				  'savepath' => UPLOAD_PATH. '/img/'. $path,
				   'saveRule' => 'getmsec',
				   'enable_resize' => false,
				);
				
				$list = $this->_uploadapk(0, $config);
				foreach($list['image'] as $key => $val)
				{
					$pic_arr[$val['post_name']] = $val['url'];
				}
			}
			
			//把所有图片 以json格式存放在一个字段中
			$map['pics'] = json_encode($pic_arr);	
			
			/*if (!empty($_FILES["img1"]["name"])||!empty($_FILES["img2"]["name"])||!empty($_FILES["img3"]["name"])) 
			{  
				if($_FILES["img1"]["name"])
				{
					$img1_pic = getimagesize($_FILES['img1']['tmp_name']);
					if($img1_pic[0] != $this->image_width ||$img1_pic[1] != $this->image_height){
						$this->error("图片1：请上传{$this->image_width}*{$this->image_height}的图片");
					}
				}
				if($_FILES["img2"]["name"])
				{
					$img2_pic = getimagesize($_FILES['img2']['tmp_name']);
					if($img2_pic[0] != $this->image_width||$img2_pic[1] != $this->image_height){
						$this->error("图片2：请上传{$this->image_width}*{$this->image_height}的图片");
					}
				}
				if($_FILES["img3"]["name"])
				{
					$img3_pic = getimagesize($_FILES['img3']['tmp_name']);
					if($img3_pic[0] != $this->image_width||$img3_pic[1] != $this->image_height){
						$this->error("图片3：请上传{$this->image_width}*{$this->image_height}的图片");
					}
				}
				$path = date('Ym/d');
				$config = array(
				  'savepath' => UPLOAD_PATH. '/img/'. $path,
				   'saveRule' => 'getmsec',
				   'enable_resize' => false,
				);
				
				$list = $this->_uploadapk(0, $config);
				foreach($list['image'] as $key => $val)
				{
					$map[$val['post_name']] = $val['url'];
				}
			}*/
			$result = $model->add($map);
			if ($result) 
			{
				$this->writelog("添加里约奥运拼图id为[{$result}]的数据",'sj_rio_olympic_pics',$result,__ACTION__ ,"","add");
				$this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/RioOlympicGames/index');
				$this->success("添加成功!!");
			} 
			else 
			{
				$this->error("添加失败！！");
			}	   
        } 
		else 
		{
			$this->assign('pic_num',$this->pic_num);
			$this->assign('image_width',$this->image_width);
			$this->assign('image_height',$this->image_height);
            $this->display();
        }
    }
	/**
	 * 编辑轮播图页面的展示，和编辑操作
	 */
	public function edit()
	{
		$model = M("rio_olympic_pics");
		if($_POST)
		{ 
			$map = array();
			$map['id'] = $_POST['id'];
			$map['title']=trim($_POST['title']);
			$map['start_tm']=strtotime($_POST['start_tm']);
			$map['end_tm']=strtotime($_POST['end_tm']);
			$map['update_tm']=time();
			$map['status']=1;
			
			$is_pic = false;
			for($i=1;$i<=$this->pic_num;$i++)
			{
				if(!empty($_FILES["img".$i]["name"]))
				{
					$is_pic = true;
					$img.$i_pic = getimagesize($_FILES['img'.$i]['tmp_name']);
					if($img.$i_pic[0] != $this->image_width ||$img.$i_pic[1] != $this->image_height){
						$this->error("图片{$i}：请上传{$this->image_width}*{$this->image_height}的图片");
					}
				}
			}
			$pic_arr = array();
			if($is_pic)
			{
				$path = date('Ym/d');
				$config = array(
				  'savepath' => UPLOAD_PATH. '/img/'. $path,
				   'saveRule' => 'getmsec',
				   'enable_resize' => false,
				);
				
				$list = $this->_uploadapk(0, $config);
				foreach($list['image'] as $key => $val)
				{
					$pic_arr[$val['post_name']] = $val['url'];
				}
			}
			
			//和之前的对比较，没有的追加，有的更改
			$edit_result = $model->where(array('id' => $_POST['id']))->find();
			$old_pics = json_decode($edit_result['pics'],true);
			//不管是否有交集  如果键值一样，后者都会覆盖前者
			if($old_pics)
			{
				$pic_arr = array_merge($old_pics,$pic_arr);
			}
			//把所有图片 以json格式存放在一个字段中
			$map['pics'] = json_encode($pic_arr);	
			
			
			/*$path = date('Ym/d');
			if (!empty($_FILES['img1']['tmp_name']) ||
			!empty($_FILES['img2']['tmp_name']) ||
			!empty($_FILES['img3']['tmp_name'])) 
			{
				if($_FILES["img1"]["name"])
				{
					$img1_pic = getimagesize($_FILES['img1']['tmp_name']);
					if($img1_pic[0] != $this->image_width ||$img1_pic[1] != $this->image_height){
						$this->error("图片1：请上传{$this->image_width}*{$this->image_height}的图片");
					}
				}
				if($_FILES["img2"]["name"])
				{
					$img2_pic = getimagesize($_FILES['img2']['tmp_name']);
					if($img2_pic[0] != $this->image_width||$img2_pic[1] != $this->image_height){
						$this->error("图片2：请上传{$this->image_width}*{$this->image_height}的图片");
					}
				}
				if($_FILES["img3"]["name"])
				{
					$img3_pic = getimagesize($_FILES['img3']['tmp_name']);
					if($img3_pic[0] != $this->image_width||$img3_pic[1] != $this->image_height){
						$this->error("图片3：请上传{$this->image_width}*{$this->image_height}的图片");
					}
				}
				$config = array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					'enable_resize' => false,
				);
				$list = $this->_uploadapk(0, $config);
				foreach($list['image'] as $key => $val)
				{
					$map[$val['post_name']] = $val['url'];
				}
			}*/
			$log_result = $this->logcheck(array('id'=> $_POST['id']), 'sj_rio_olympic_pics', $map, $model);
            $result = $model->save($map);
            if ($result)
			{
            	$this -> writelog("编辑里约奥运拼图id为[{$_POST['id']}]".$log_result,'sj_rio_olympic_pics',$_POST['id'],__ACTION__ ,"","edit");
                $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/RioOlympicGames/index');
                $this->success("编辑成功!!");
            } 
			else 
			{
                $this->error("编辑失败！！");
            }
		}
		else 
		{
			$where['id'] = $_GET['id'];
			$result = $model -> where($where) -> find();
			$this->assign("pics_list",$result);
			$this->assign('pic_num',$this->pic_num);
			$this->assign('image_width',$this->image_width);
			$this->assign('image_height',$this->image_height);
			$this->display();
		}
	}
}
