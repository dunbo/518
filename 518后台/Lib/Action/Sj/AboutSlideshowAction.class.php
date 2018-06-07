<?php

/**
 * 客户端-关于页面-新版介绍的轮播图管理
 *
 */
class AboutSlideshowAction extends CommonAction {
	
	/**
	 * 轮播图管理首页，列表页
	 */
	public function index()
	{
		$sj_about_slideshow=M("aboutslideshow");
		import("@.ORG.Page");
		$where=array(
			'status'=>1,
		);
		$count = $sj_about_slideshow->where($where)->count();
        $param = http_build_query($_GET);
        $Page = new Page($count, 10, $param);
	    $about_slide_list=$sj_about_slideshow->where($where)->order("id desc")->limit($Page->firstRow . ',' . $Page->listRows)->select();
	    $show = $Page->show();
        $this->assign("page", $show);
        $this->assign("slide_list",$about_slide_list);
		$this->display();

	}
	
	/**
	 * 处理添加轮播图页面的展示，和处理添加操作
	 */
	public function add()
	{
		if ($_POST) 
		{  
			$sj_about_slideshow = M("aboutslideshow");
			$slide_info['verc']=trim($_POST['version_code']);
			$slide_info['create_tm']=time();
			$slide_info['update_tm']=time();
			$slide_info['status']=1;
			
			if (!empty($_FILES["img1"]["name"])||!empty($_FILES["img2"]["name"])||!empty($_FILES["img3"]["name"])||!empty($_FILES["img4"]["name"])||!empty($_FILE["img5"]["name"])) 
			{  
				$path = date('Ym/d');
				$config = array(
				  'savepath' => UPLOAD_PATH. '/img/'. $path,
				  'img_p_size' => 1024 * 100,
				  'img_p_width'=> 480, //图片常规压缩宽度
				  'img_p_height'=>860, //图片常规压缩宽度
				   'saveRule' => 'getmsec',
				);
				
				$list = $this->_uploadapk(0, $config);
				foreach($list['image'] as $key => $val)
				{
					$slide_info[$val['post_name']] = $val['url'];
				}
			}
			$result = $sj_about_slideshow->add($slide_info);
			if ($result) 
			{
				$this->writelog("添加客户端关于页面轮播图id为[{$result}]",'sj_aboutslideshow',$result,__ACTION__ ,"","add");
				$this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/AboutSlideshow/index');
				$this->success("添加成功!!");
			} 
			else 
			{
				$this->error("添加失败！！");
			}	   
        } 
		else 
		{
            $this->display();
        }
    }
	/**
	 * 编辑轮播图页面的展示，和编辑操作
	 */
	public function edit()
	{
		if($_POST)
		{ 
			$slide_info = array();
			if($_POST['del_1'] == 1)
			{
				$slide_info['img1'] = '';
			}
			if($_POST['del_2'] == 1)
			{
				$slide_info['img2'] = '';
			}
			if($_POST['del_3'] == 1)
			{
				$slide_info['img3'] = '';
			}
			if($_POST['del_4'] == 1)
			{
				$slide_info['img4'] = '';
			}
			if($_POST['del_5'] == 1)
			{
				$slide_info['img5'] = '';
			} 
		    $sj_about_slideshow = M("aboutslideshow");
		    $slide_info['verc']=$_POST['version_code']; 
		    $slide_info['id'] = $_POST['id'];
            $slide_info['update_tm']=time();
			$path = date('Ym/d');
			if (!empty($_FILES['img1']['tmp_name']) ||
			!empty($_FILES['img2']['tmp_name']) ||
			!empty($_FILES['img3']['tmp_name']) ||
			!empty($_FILES['img4']['tmp_name']) ||
			!empty($_FILES['img5']['tmp_name'])) 
			{
				$config = array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'img_p_size' => 1024 * 100,
					'img_p_width'=> 480, //图片常规压缩宽度
					'img_p_height'=>860, //图片常规压缩宽度
					'saveRule' => 'getmsec',
				);
				$list = $this->_uploadapk(0, $config);
				foreach($list['image'] as $key => $val)
				{
					$slide_info[$val['post_name']] = $val['url'];
				}
			}
			$log_result = $this->logcheck(array('id'=> $_POST['id']), 'sj_aboutslideshow', $slide_info, $sj_about_slideshow);
            $result = $sj_about_slideshow->save($slide_info);
            if ($result)
			{
            	$this -> writelog("编辑客户端关于页面轮播图id为[{$_POST['id']}]".$log_result,'sj_aboutslideshow',$_POST['id'],__ACTION__ ,"","edit");
                $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/AboutSlideshow/index');
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
			$DB = M('aboutslideshow');
			$result = $DB -> where($where) -> select();
			$this->assign("slide_list",$result[0]);
			$this->display();
		}
	}
 	 
	/**
	 * 删除某个版本号的所有轮播图
	 */
	public function delete()
	{
		$id = $_GET['id'];
		$DB = M('aboutslideshow');
		$result = $DB -> table('sj_aboutslideshow') -> where(array('id' => $id)) -> save(array('status' => 0));
	    if($result)
		{
			$this -> writelog("删除客户端关于页面轮播图id为[{$_GET['id']}]",'sj_aboutslideshow',$_GET['id'],__ACTION__ ,"","del");
			$this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/AboutSlideshow/index');
			$this -> success("数据删除成功！");	
        }
		else
		{
			$this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/AboutSlideshow/index');
			$this -> error("数据删除失败！");
		}
    }
	//添加的时候审查
	function check_version()
	{
		$sj_about_slideshow = M("aboutslideshow");
		$where['verc']=trim($_GET['version_code']);
		$where['status']=1;
		$is_exist= $sj_about_slideshow->where($where)->select();
		if($is_exist)
		{
			echo 1;
			return 1;
		}
		else
		{
			echo 2;
			return 2;
		}	
	}
}
