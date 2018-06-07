<?php

/**
 * 安智精选网页管理
 * 
 * @author jiwei
 *
 */
class PerfectSoftAction extends CommonAction
{
	public $self_config = array();
	
	/**
	 * 初始化配置内容
	 */	
	function __construct(){
		parent::__construct();

		$this->self_config['upload_api'] = 'service_dev.php'; //上传图片接口名称
		$this->self_config['upload_api_save_path'] = UPLOAD_PATH; //图片保存地址
		$this->self_config['upload_api_image_host'] = '<!--{ANZHI_IMAGE_HOST}-->'; //内容中要替换图片路径的标记
		$this->self_config['upload_api_image_url'] = IMGATT_HOST; //cdn调用地址
		
		if($_SERVER['SERVER_ADDR'] == '118.26.203.23')
		{
			$this->self_config['upload_api_host'] = '118.26.203.23';
			$this->self_config['upload_api_domain'] = 'dummy.goapk.com'; //上传图片接口域名
			$this->self_config['static_url'] = 'http://118.26.203.23/perfect_%d.html'; //静态文件地址
			$this->self_config['perview_url'] = 'http://118.26.203.23/perfect.php?id=%d'; //预览文件地址
		}
		elseif($_SERVER['SERVER_ADDR'] == '192.168.0.99')
		{
			$this->self_config['upload_api_host'] = '192.168.0.99';
			$this->self_config['upload_api_domain'] = '9.dummy.goapk.com';
			$this->self_config['static_url'] = 'http://9.m.anzhi.com/perfect_%d.html';
			$this->self_config['perview_url'] = 'http://9.m.anzhi.com/perfect.php?id=%d';
		}
		else 
		{
			$this->self_config['upload_api_host'] = '192.168.1.18';
			$this->self_config['upload_api_domain'] = 'dummy.goapk.com';
			$this->self_config['static_url'] = 'http://m.anzhi.com/perfect_%d.html';
			$this->self_config['perview_url'] = 'http://m.anzhi.com/perfect.php?id=%d';
		}
		
		
		$this->assign('self_config', $this->self_config);
	}
	
	/**
	 * 安智精选入口，列表页
	 */
	public function index()
	{
		$package = isset($_GET['package']) && empty($_GET['package'])==false ? $_GET['package'] : NULL;
		$hash = isset($_GET['__hash__']) && empty($_GET['__hash__'])==false ? $_GET['__hash__'] : NULL;
		$softname = isset($_GET['softname']) && empty($_GET['softname'])==false ? $_GET['softname'] : NULL;
		$select_label_id = isset($_GET['select_label_id'])? $_GET['select_label_id'] : NULL;
		$p = isset($_GET['p']) && empty($_GET['p'])==FALSE ? $_GET['p'] : 1; //页数
		$lr = isset($_GET['lr']) && empty($_GET['lr'])==FALSE ? $_GET['lr'] : 10; //每页记录数
		
		// 构造查询条件
		$map = array('status'=>1);
		if(!is_null($package))
			$map['package'] = array('like', "%{$package}%");
		if(isset($_GET['is_show'])){
			if($_GET['is_show']!=2){
				$map['is_show'] = $_GET['is_show'];
			}
		}
		if(!is_null($softname))
			$map['softname'] = array('like', "%{$softname}%");
		if(!is_null($select_label_id)){
			$map['select_label_id'] = $select_label_id;
		}

		// 处理查询
		$m_perfect = D('perfect_soft');
		$count = $m_perfect->where($map)->count();
		$a=ceil($count/$lr);
		if($a<$p){
			$p=$a;
		}
		$perfect = $m_perfect->where($map)->page("{$p},{$lr}")->order('id DESC')->select();
		foreach($perfect as $k=>$v){
			$re=$m_perfect->table('sj_select_label')->where(array('id'=>$v['select_label_id']))->find();
			$perfect[$k]['label_name']=$re['label_name'];
		}
		$labels=$m_perfect->field('id,label_name')->table('sj_select_label')->where(array('status'=>1))->select();
		// 处理分页
		import("@.ORG.Page");
		$page = new Page($count, $lr);
		$page->setConfig('header','条记录');
		$page->setConfig('first','<<');
		$page->setConfig('last','>>');
		$page_show = $page->show();
		$this->assign('is_show', isset($_GET['is_show'])?$_GET['is_show']:2);
		$this->assign('select_label_id', $select_label_id);
		$this->assign('labels', $labels);
		$this->assign('package', $package);
		$this->assign('softname', $softname);
		$this->assign('perfect', $perfect);
		$this->assign('page', $page_show);
		$this->assign('url_suffix',base64_encode($this->get_url_suffix(array('package','softname','p','lr'))));
		$this->display();
	}
	
	/**
	 * 添加新的精选
	 */
	public function add()
	{
		$return_url = isset($_GET['return']) && empty($_GET['return'])==false ? base64_decode($_GET['return']) : '';
		
		if($this->isPost())
		{
			$package = isset($_POST['package']) && empty($_POST['package'])==false ? $_POST['package'] : NULL;
			$softname = isset($_POST['softname']) && empty($_POST['softname'])==false ? $_POST['softname'] : NULL;
			$subject = isset($_POST['subject']) && empty($_POST['subject'])==false ? $_POST['subject'] : NULL;
			$comment_num = isset($_POST['comment_num']) && is_numeric($_POST['comment_num']) ? $_POST['comment_num'] : 10;
			$history_num = isset($_POST['history_num']) && is_numeric($_POST['history_num']) ? $_POST['history_num'] : 10;
			$content = isset($_POST['content']) ? stripcslashes($_POST['content']) : NULL;
			$public_date = isset($_POST['public_date']) && empty($_POST['public_date'])==false ? strtotime($_POST['public_date']) : 0; //2014.12.30 jiwei
			$select_label_id = isset($_POST['select_label_id']) && empty($_POST['select_label_id'])==false ? $_POST['select_label_id'] : NULL;
			$is_show = $_POST['is_show']?$_POST['is_show']:0;
			$brief_intro = isset($_POST['brief_intro']) && empty($_POST['brief_intro'])==false ? $_POST['brief_intro'] : NULL;

			//
			// 处理数据验证
			//
			if(is_null($package))
				$this->error('没有输入软件包名！');
			
			if(is_null($subject))
				$this->error('没有输入标题！');
			
			if(mb_strlen($subject,'utf-8')>20)
				$this->error('标题长度不能大于20个字符！');
				
			if(is_null($brief_intro) && $is_show==1)
				$this->error('简介内容不能为空');
			$m_soft = D('soft');
			$soft = $m_soft->where(array('package'=>$package, 'status'=>1, 'hide'=>1))->order('softid DESC')->find();
			
			if(!$soft)
				$this->error('该软件不存在或已经下架！');
			
			//if($soft['hide']==3)
			//	$this->error('该软件已下架！');
			
			//
			// 处理文件上传
			//
			if(isset($_FILES['banner']['name']) && $_FILES['banner']['name']!='')
			{
				$length_x_y=getimagesize($_FILES['banner']['tmp_name']);
				$width_x=$length_x_y[0];
				$width_y=$length_x_y[1];
				if($width_x!=480 || $width_y!=181){
					$this->error('请上传480*181的JPG/PNG/GIF图');
				}
				$save_path = '/img/'.date('Ym').'/';
				if(!is_dir($this->self_config['upload_api_save_path'].$save_path))
					mkdir($this->self_config['upload_api_save_path'].$save_path);		
				
				$info = NULL;
				import("@.ORG.UploadFile");
				$upload = new UploadFile();
				$upload->maxSize = 3145728;
				$upload->allowExts = array('jpg','jpeg','png','gif');
				$upload->savePath = $this->self_config['upload_api_save_path'].$save_path;
				$upload->saveRule = 'time';
				
				if(!$upload->upload())
					$this->error($upload->getErrorMsg());
				else
					$info = $upload->getUploadFileInfo();
				
				if(is_null($info))
					$this->error("没有获得上传文件信息！");
				
				$data['banner_path'] = $save_path.$info[0]['savename'];
			}
			
			//
			// 处理富文本编辑器提交内容中的图片路径
			//
			// 1、获取内容中的图片路径
			$path_arr = $this->parse_images($content);
	
			if(count($path_arr))
			{
				// 2、将图片复制到服务器指定位置
				$result = $this->upload_images($path_arr);
				// echo "<pre>";var_dump($result);die;
				// 3、新路径
				$new_path_arr = array();
				foreach($result['new_path'] as $key=>$path)
				{
					$tmp = explode('_', $key);
					$index = $tmp[1]; 
					$new_path_arr[$index] = $this->self_config['upload_api_image_host'].$path;
				}
	
				// 4、将内容中的路径替换
				$content = str_replace($path_arr, $new_path_arr, $content);
			}
	
			//
			// 处理数据入库
			//
			$data['admin_id'] = $_SESSION['admin']['admin_id'];
			$data['admin_name'] = $_SESSION['admin']['admin_user_name'];
			$data['softid'] = $soft['softid'];
			$data['package'] = $package;
			$data['select_label_id'] = $select_label_id;
			$data['is_show'] = $is_show;
			$data['brief_intro'] = $brief_intro;
			$data['softname'] = $softname;
			$data['subject'] = $subject;
			$data['comment_num'] = $comment_num;
			$data['history_num'] = $history_num;
			$data['update_tm'] = time();
			$data['create_tm'] = time();
			
			//2014.12.30 jiwei
			if(empty($public_date))
				$data['public_date'] = time();
			else
				$data['public_date'] = $public_date;
			
			$m_perfect = D('perfect_soft');
			$m_content = D('perfect_soft_content');
			
			$id = $m_perfect->add($data);
			if($id)
			{
				$m_content->add(array('perfect_soft_id'=>$id, 'content'=>$content));
				
				//记录日志
				$this->writelog("市场软件运营推荐-精选软件页面管理:新增记录（id={$id}）",'sj_perfect_soft',$id,__ACTION__ ,"","add");
				echo '<script>alert("新增成功！");location.href="'.__URL__.'/index'.$return_url.'";</script>';
			}
			else 
				$this->error('创建数据失败！');
		}
		//获取标识名称
		$model = D();
		$where=array();
		$where['status']=1;
		$label_list = $model->table('sj_select_label')->field('id,label_name')->where($where)->select();
		// echo "<pre>";var_dump($label_list);die;
		$this->assign('label_list', $label_list);
		$this->assign('return_url', $return_url);
		$this->display();
	}
	
	/**
	 * 编辑指定的精选
	 */
	public function edit()
	{
		$id = isset($_GET['id']) && is_numeric($_GET['id']) ? $_GET['id'] : NULL;
		$return_url = isset($_GET['return']) && empty($_GET['return'])==false ? base64_decode($_GET['return']) : '';
		
		if(is_null($id))
			$this->error('参数错误！');
		
		$m_perfect = D('perfect_soft');
		$perfect = $m_perfect->where(array('id'=>$id, 'status'=>1))->find();
		
		if(!$perfect)
			$this->error('没有找到软件精品网页信息！');
				
		$m_content = D('perfect_soft_content');
		$content = $m_content->where(array('perfect_soft_id'=>$id))->find();
		$content = str_replace($this->self_config['upload_api_image_host'], $this->self_config['upload_api_image_url'], $content['content']);
		
		if(empty($perfect['banner_path']))
			$banner_path = '';
		else
			$banner_path = $this->self_config['upload_api_image_url'].$perfect['banner_path'];
		
		if($this->isPost())
		{
			$subject = isset($_POST['subject']) && empty($_POST['subject'])==false ? $_POST['subject'] : NULL;
			$comment_num = isset($_POST['comment_num']) && is_numeric($_POST['comment_num']) ? $_POST['comment_num'] : 10;
			$history_num = isset($_POST['history_num']) && is_numeric($_POST['history_num']) ? $_POST['history_num'] : 10;
			$content = isset($_POST['content']) ? stripcslashes($_POST['content']) : NULL;
			// $content = isset($_POST['content']) ? $_POST['content'] : NULL;
			// var_dump($content);
			$public_date = isset($_POST['public_date']) && empty($_POST['public_date'])==false ? strtotime($_POST['public_date']) : 0; //2014.12.30 jiwei
			$select_label_id = isset($_POST['select_label_id']) && empty($_POST['select_label_id'])==false ? $_POST['select_label_id'] : NULL;
			$is_show = $_POST['is_show']?$_POST['is_show'] : 0;
			$brief_intro = isset($_POST['brief_intro']) && empty($_POST['brief_intro'])==false ? $_POST['brief_intro'] : NULL;
			//
			// 处理数据验证
			//			
			if(is_null($subject))
				$this->error('没有输入标题！');
			
			if(mb_strlen($subject,'utf-8')>20)
				$this->error('标题长度不能大于20个字符！');
			if(is_null($brief_intro) && $is_show==1)
				$this->error('简介内容不能为空');
			//
			// 处理文件上传
			//
			if(isset($_FILES['banner']['name']) && $_FILES['banner']['name']!='')
			{
				$length_x_y=getimagesize($_FILES['banner']['tmp_name']);
				$width_x=$length_x_y[0];
				$width_y=$length_x_y[1];
				if($width_x!=480 || $width_y!=181){
					$this->error('请上传480*181的JPG/PNG/GIF图');
				}
				$save_path = '/img/'.date('Ym').'/';
				if(!is_dir($this->self_config['upload_api_save_path'].$save_path))
					mkdir($this->self_config['upload_api_save_path'].$save_path);
				
				$info = NULL;
				import("@.ORG.UploadFile");
				$upload = new UploadFile();
				$upload->maxSize = 3145728;
				$upload->allowExts = array('jpg','jpeg','png','gif');
				$upload->savePath = $this->self_config['upload_api_save_path'].$save_path;
				$upload->saveRule = 'time';
					
				if(!$upload->upload())
					$this->error($upload->getErrorMsg());
				else
					$info = $upload->getUploadFileInfo();
					
				if(is_null($info))
					$this->error("没有获得上传文件信息！");
				
				$data['banner_path'] = $save_path.$info[0]['savename'];
			}
				
			//
			// 处理富文本编辑器提交内容中的图片路径
			//
				
			// 1、获取内容中的图片路径
			$path_arr = $this->parse_images($content);
			// echo "<pre>";var_dump($path_arr);
			if(count($path_arr))
			{
				// 2、将图片复制到服务器指定位置
				$result = $this->upload_images($path_arr);
				// echo "<pre>";var_dump($result);die;
				// 3、新路径
				$new_path_arr = array();
				foreach($result['new_path'] as $key=>$path)
				{
					$tmp = explode('_', $key);
					$index = $tmp[1];
					$new_path_arr[$index] = $this->self_config['upload_api_image_host'].$path;
				}
		
				// 4、将内容中的路径替换
				$content = str_replace($path_arr, $new_path_arr, $content);
			}
			
			//2015.3.17 jiwei
			//处理编辑图片的时候，图片地址替换的问题
			$content = str_replace($this->self_config['upload_api_image_url'], $this->self_config['upload_api_image_host'], $content);
			
		
			//
			// 处理数据入库
			//
			$data['admin_id'] = $_SESSION['admin']['admin_id'];
			$data['admin_name'] = $_SESSION['admin']['admin_user_name'];
			$data['subject'] = $subject;
			$data['comment_num'] = $comment_num;
			$data['history_num'] = $history_num;
			$data['update_tm'] = time();
			$data['select_label_id'] = $select_label_id;
			$data['is_show'] = $is_show;
			$data['brief_intro'] = $brief_intro;
			//2014.12.30 jiwei
			if(empty($public_date))
				$data['public_date'] = time();
			else
				$data['public_date'] = $public_date;
				
			$log_result = $this->logcheck(array('id'=>$id), 'sj_perfect_soft', $data, $m_perfect);
			$content = str_replace($path_arr, $new_path_arr, $content);
			// val.replace(/&lt;/g, '<').replace(/&gt;/g, '>').replace(/&quot;/g, '"').replace(/&amp;/g, '&');
			// var_dump($content);die;
			$m_perfect->where(array('id'=>$id))->save($data);
			$m_content->where(array('perfect_soft_id'=>$id))->save(array('content'=>$content));
		
			//记录日志
			$this->writelog("市场软件运营推荐-精选软件页面管理:编辑记录（id={$id}）".$log_result,'sj_perfect_soft',$id,__ACTION__ ,"","edit");
			
			echo '<script>alert("编辑成功！");location.href="'.__URL__.'/index'.$return_url.'";</script>';
		}
		//获取标识名称
		$model = D();
		$where=array();
		$where['status']=1;
		$label_list = $model->table('sj_select_label')->field('id,label_name')->where($where)->select();
		$this->assign('label_list', $label_list);
		$this->assign('perfect', $perfect);
		$this->assign('content', $content);
		$this->assign('banner_path', $banner_path);
		$this->assign('return_url', $return_url);
		$this->display();
	}
	
	/**
	 * 删除指定的精选
	 */
	public function delete()
	{
		$id = isset($_GET['id']) && is_numeric($_GET['id']) ? $_GET['id'] : NULL;
		$return_url = isset($_GET['return']) && empty($_GET['return'])==false ? base64_decode($_GET['return']) : '';
		
		if(is_null($id))
		{
			echo json_encode(array(
					'result_no' => -1,
					'result_msg' => '参数错误！'
			));
			exit;
		}
		
		$data['status'] = 0;
		$data['update_tm'] = time();
		
		$m_perfect = D('perfect_soft');
		$log_result = $this->logcheck(array('id'=>$id), 'sj_perfect_soft', $data, $m_perfect);
		$m_perfect->where(array('id'=>$id))->save($data);
		$this->writelog("市场软件运营推荐-精选软件页面管理:删除记录（id={$id}）".$log_result,'sj_perfect_soft',$id,__ACTION__ ,"","del");
		
		echo json_encode(array(
				'result_no' => 1,
				'result_msg' => '成功',
		));		
	}
	
	/**
	 * 在后台预览
	 */
	public function preview()
	{
		
	}
	
	/**
	 * ajax请求，获取软件状态信息
	 */
	public function ajax_soft_status()
	{
		$package = isset($_GET['package']) && empty($_GET['package'])==FALSE ? $_GET['package'] : NULL;
		
		if(is_null($package))
		{
			echo json_encode(array(
					'result_no' => -1,
					'result_msg' => '参数错误！'
			));
			exit;
		}
		
		$m_soft = D('soft');
		$soft = $m_soft->where(array('package'=>$package, 'status'=>1, 'hide'=>1))->order('softid DESC')->find();
		
		if($soft)
		{
			echo json_encode(array(
					'result_no' => 1,
					'result_msg' => '成功',
					'result_data' => $soft
			));
		}
		else
		{
			echo json_encode(array(
					'result_no' => -2,
					'result_msg' => '该软件不存或软件已经下架！'
			));
		}
	}
	
	/**
	 * 精选软件统计页面
	 */
	public function stat()
	{
		$ts = time();
		$from_date = isset($_GET['from_date']) && empty($_GET['from_date'])==false ? $_GET['from_date'] : date('Y-m-d', $ts-86400*30);
		$to_date = isset($_GET['to_date']) && empty($_GET['to_date'])==false ? $_GET['to_date'] : date('Y-m-d', $ts);
		$id = isset($_GET['id']) && empty($_GET['id'])==false ? $_GET['id'] : null;
		
		if(is_null(id))
			$this->error('精选软件ID错误！');	

		if(strtotime($from_date)>=strtotime($to_date))
			$this->error('查询起始日期大于结束日期！');
		
		$m_perfect = D('perfect_soft');
		$perfect = $m_perfect->where(array('id'=>$id, 'status'=>1))->find();
		
		$stat = D('Sj.PerfectSoftStat');
		$result = $stat->findStatByPerfectSoftId($id, $from_date, $to_date);
		
		$browser_pv_total = $market_pv_total = 0;
		$stat_date = $browser_pv = $market_pv = array();
		foreach($result as $row)
		{
			$stat_date[] = date('m/d', strtotime($row['stat_date']));
			$browser_pv[] = (int)$row['browser_pv'];
			$market_pv[] = (int)$row['market_pv'];
			$browser_pv_total += $row['browser_pv'];
			$market_pv_total += $row['market_pv'];
		}
		

		
		$this->assign('perfect', $perfect);
		$this->assign('result', $result);
		$this->assign('browser_pv_total', $browser_pv_total);
		$this->assign('market_pv_total', $market_pv_total);
		$this->assign('from_date', $from_date);
		$this->assign('to_date', $to_date);
		$this->assign('stat_date', json_encode($stat_date));
		$this->assign('browser_pv', json_encode($browser_pv));
		$this->assign('market_pv', json_encode($market_pv));
		$this->display();
	}
	
	/**
	 * 从内容中提取出图片路径
	 * @param unknown $content
	 */
	private function parse_images($content, $pattern=NULL)
	{
		if(is_null($pattern))
			$pattern = "/<img.+?src=\"(\/Public\/js\/kindeditor.*?)\".+?\/>/u";
		preg_match_all($pattern, $content, $matches_pic);

		$pattern = "/<embed.+?src=\"(\/Public\/js\/kindeditor.*?)\".+?\/>/u";
		preg_match_all($pattern, $content, $matches_mp4);
		$pattern = "/<embed.+?imgurl=\"(\/Public\/js\/kindeditor.*?)\".+?\/>/u";
		preg_match_all($pattern, $content, $matches_imgurl);
		
	
		if(count($matches_pic[1]) || count($matches_mp4[1]) || count($matches_imgurl[1]))
			return array_merge($matches_pic[1],$matches_mp4[1],$matches_imgurl[1]);
		else
			return array();
	}	
	
	/**
	 * 复制图片到指定服务器位置
	 */
	private function upload_images($path_arr)
	{

		
		$data['do'] = 'save';
		$data['static_data'] = $this->self_config['upload_api_save_path'];

		foreach($path_arr as $key=>$path)
			$data['key_'.$key] = '@'.$_SERVER['DOCUMENT_ROOT'].$path;
	    $upload_model = D("Dev.Uploadfile");
        $upload = $upload_model->_http_post($data);

		return array(	'new_path' => $upload['ret'], 
						'info' => $upload['info'], 
						'errno' => $upload['errno'], 
						'error' => $upload['error']);
	}
}