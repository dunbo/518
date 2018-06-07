<?php
class ExPromotionAction extends CommonAction {
	
	public $self_config = array();
	
	/**
	 * 初始化配置内容
	 */	
	function __construct(){
		parent::__construct();

		$this->self_config['upload_api'] = 'service_dev.php'; //上传图片接口名称
		$this->self_config['upload_api_save_path'] = UPLOAD_PATH; //图片保存地址
		$this->self_config['upload_api_image_host'] = "<!--{ANZHI_IMAGE_HOST}-->"; //内容中要替换图片路径的标记
		$this->self_config['upload_api_image_url'] = IMGATT_HOST; //cdn调用地址
		
		if($_SERVER['SERVER_ADDR'] == '118.26.203.23')
		{
			$this->self_config['upload_api_host'] = '118.26.203.23';
			$this->self_config['upload_api_domain'] = 'dummy.goapk.com'; //上传图片接口域名
			$this->self_config['static_url'] = 'http://118.26.203.23/promotion_'; //静态文件地址
			$this->self_config['perview_url'] = 'http://118.26.203.23/lottery/promotion.php?id='; //预览文件地址
		}
		elseif($_SERVER['SERVER_ADDR'] == '192.168.0.99')
		{
			$this->self_config['upload_api_host'] = '192.168.0.99';
			$this->self_config['upload_api_domain'] = '9.dummy.goapk.com';
			$this->self_config['static_url'] = 'http://9.m.anzhi.com/promotion_';
			$this->self_config['perview_url'] = 'http://9.m.anzhi.com/lottery/promotion.php?id=';
		}
		else 
		{
			$this->self_config['upload_api_host'] = '192.168.1.18';
			$this->self_config['upload_api_domain'] = 'dummy.goapk.com';
			$this->self_config['static_url'] = 'http://fx.anzhi.com/promotion_';
			$this->self_config['perview_url'] = 'http://fx.anzhi.com/lottery/promotion.php?id=';
		}
		
		
		$this->assign('self_config', $this->self_config);
	}

    // 外部推广列表
    function external_promotion_list() 
	{
        $model = new Model();
        $where = array(
            'promotion.status' => 1,
			'jump.status' => 1,
        );
        // trim一下
        foreach ($_GET as $key => $v) {
            $_GET[$key] = trim($v);
        }
        // 记录页数参数
        $url_param = "";
        foreach ($_GET as $key => $v) 
		{
            if ($url_param != '')
                $url_param .= "&";
            if ($v != '')
                $url_param .= "{$key}={$v}";
        }
		$where['_string'] .="promotion.content_id = jump.id";
		
        // 搜索页面名称
        if ($_GET['search_page_name'] != '') 
		{
			$where['_string'] .=' and promotion.page_name like "%'.$_GET['search_page_name'] . '%"';
        }
		
		
		$count = $model->table('sj_external_promotion promotion,sj_common_jump jump')->where($where)->field('promotion.id as prid, promotion.page_name, promotion.content_id, promotion.channel_id, promotion.preview_url,promotion.create_tm,jump.*')->order('create_tm DESC')->count();
		
        import("@.ORG.Page");
        $page = new Page($count, 10);
        $show = $page->show(); //分页显示输出
    
		//联合查询
		$list = $model->table('sj_external_promotion promotion,sj_common_jump jump')->where($where)->field('promotion.id as prid, promotion.page_name, promotion.content_id, promotion.channel_id, promotion.preview_url,promotion.create_tm,jump.*')->order('promotion.create_tm DESC')->limit($page->firstRow . ',' . $page->listRows)->select();
		
		//每行数据进行处理
		foreach ($list as $k => $v) 
		{
			//推荐内容展示
			$content_type = $v['content_type'];
			if ($content_type == 2) {
				// 活动名称
				$list[$k]['activity_name'] = ContentTypeModel::convertActivityId2ActivityName($v['activity_id']);
			} else if ($content_type == 3) {
				// 专题名称
				$list[$k]['feature_name'] = ContentTypeModel::convertFeatureId2FeatureName($v['feature_id']);
			} else if ($content_type == 4) {
				// 页面名称
				$list[$k]['content_page_name'] = ContentTypeModel::convertPageType2PageName($v['page_type']);
			}
			
			//获取渠道名称
			$channel_model = M('channel');
			if($v['channel_id'])
			{
				$chl = $channel_model->field("`cid`,`chname`")->where(array('cid' => $v['channel_id']))->find();
				$list[$k]['chname'] = $chl['chname'];
			}
			else
			{
				$list[$k]['chname'] = "通用";
			}
		
			/*$channel=M("channel");
            $cid_str = substr($v['channel_id'], 1, -1);
            $array = explode(',', $cid_str);
            $cname = $channel->where("cid in ({$cid_str})")->findAll();
			if(in_array("0",$array)||in_array("",$array))
			{
				$list[$k]['chname'] .= "<p>通用</p>";
			}
            foreach ($cname as $k1 => $v1) 
			{
				$list[$k]['chname'] .= "<p>{$v1['chname']}<p>";
            }*/
        }
        // 搜索内容
        $this->assign('search_page_name', $_GET['search_page_name']);
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->assign('apkurl', GAMEINFO_ATTACHMENT_HOST);
        $this->assign('function_name', __FUNCTION__);
        $this->assign('url_param', $url_param);
        $this->display();
    }

    //外部推广增加展示
    function promotion_add_show() {
        $this->assign('function_name', $_GET['from']);
        // 记录页数参数，方便跳回第几页
        $url_param = "";
        foreach ($_GET as $key => $value) {
            if ($key == 'id' || $key == 'from')
                continue;
            if ($url_param != '')
                $url_param .= "&";
            if ($value != '')
                $url_param .= "{$key}={$value}";
        }
        $this->assign('url_param', $url_param);
        $this->display();
    }

    //添加外部推广数据
    function promotion_add_do() 
	{
        $model = new Model();
        $page_name = $_POST['page_name'];
        if (!$page_name) 
		{
            $this->error("页面名称不能为空");
        }
        $btn_type = $_POST['btn_type'];
        if($btn_type ==1)
		{
			$text_content = $_POST['text_content'];
			if (!$text_content) 
			{
				$this->error("文本内容不能为空");
			}
			$data['btn_text_color'] = $_POST['text_color'];
			$data['btn_text_font_size'] = $_POST['text_font_size'];
			$data['btn_text_alignment'] = $_POST['text_alignment'];
			$data['btn_text_content'] = $text_content;
		}
		else
		{
			$btn_pic = $_FILES['btn_pic'];
			if ($btn_pic['size']) 
			{
				$high_wd = getimagesize($btn_pic['tmp_name']);
				
				$width = $high_wd[0];
				$height = $high_wd[1];
				
				if ($width > 480) 
				{
					$this -> error("按钮图片宽度不能超过480px");
				}
				preg_match("/\.(?:png|jpg|jpeg)$/i", $btn_pic['name'], $matches);
				if (!$matches) 
				{
					$this->error("上传图片类型错误！");
				}

				/*if ($btn_pic['size'] > 35 * 1024) {
					$this->error("默认图片尺寸大于35K");
				}*/
				$path=date("Ym/d/",time());
				$config = array(
					'multi_config' => array(
						'btn_pic' => array(
							'savepath' => UPLOAD_PATH . '/img/' . $path,
							'saveRule' => 'getmsec'
						),
					),
				);
				$list = $this->_uploadapk(0, $config);
				$news_url = $list['image'][0]['url'];
				$data['btn_pic'] = $news_url;
			}
			else 
			{
				$this->error("按钮图片不能为空");
			}
		}
		//推荐内容
		if($_POST['content_type'])
		{
			//推荐内容处理 合并
            $rcontent=ContentTypeModel::saveRecommendContent($_POST,'',$map);
			if($rcontent !==true)
			{
				$this -> error($rcontent);
			}
			// 创建时间和更新时间
			$map['create_at'] = time();
            $map['update_at'] = time();
			$map['status'] = 1;
		}
		else
		{
			$this->error("推荐内容不能为空");
		}
        //渠道
		$channel_id=$_POST['cid'];
		if($channel_id)
		{
			$data['channel_id'] = $channel_id;
		}
		/*$channel_id_array=$_POST['cid'];
        $cids = array_unique($channel_id_array);
		if (count($cids) > 0) {
			$s = implode(',', $cids);
			$s = ",{$s},";
			$data['channel_id'] = $s;
		}*/
		//页面标题
		$page_title = $_POST['page_title'];
        if (empty($page_title))
		{
            $this->error("页面标题不能为空");
        }
		//页面正文
        $page_content = $_POST['editor_content'];
        if (empty($page_content) || $page_content == "<p>&nbsp;</p>") 	
		{
            $this->error("页面正文不能为空");
        }
        $content = stripcslashes($_POST['editor_content']);
		// 1、获取内容中的图片路径
		$path_arr = $this->parse_images($content);
		if(count($path_arr))
		{
			// 2、将图片复制到服务器指定位置
			$result = $this->upload_images($path_arr);
			
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
		//处理数据入库
        $data['page_name'] = $page_name;
        $data['page_btn_type'] = $btn_type;
       
		
		$data['page_title'] = $page_title;
		
        $data['preview_url'] = $this->self_config['static_url'];
        $data['page_content'] = $content;
        $data['create_tm'] = time();
        $data['update_tm'] = time();
        $data['status'] = 1;

		// 添加通用跳转
        $id = $model->table('sj_common_jump')->add($map);
		
		$data['content_id'] = $id;
		
		//添加外部推广数据
        $result = $model->table('sj_external_promotion')->add($data);
		
        if ($result) {
            $this->writelog("已添加id为{$result}的外部推广", 'sj_external_promotion',$result,__ACTION__ ,'','add');
            $from = $_POST['from'];
            $this->assign('jumpUrl', "/index.php/Sendnum/ExPromotion/external_promotion_list/{$from}?{$_POST['url_param']}");
            $this->success("添加成功");
        }
    }
	/**
	 * 从内容中提取出图片路径
	 * @param unknown $content
	 */
	private function parse_images($content, $pattern=NULL)
	{
		if(is_null($pattern))
			$pattern = "/<img.+?src=\"(\/Public\/js\/kindeditor.*?)\".+?\/>/u";
	
		preg_match_all($pattern, $content, $matches);
		if(count($matches[1]))
			return $matches[1];
		else
			return array();
	}
	/**
	 * 复制图片到指定服务器位置
	 */
	private function upload_images($path_arr)
	{
		$host = $this->self_config['upload_api_host'];
		$host_dam = 'Host:'.$this->self_config['upload_api_domain'];
		
		$data['do'] = 'save';
		$data['static_data'] = $this->self_config['upload_api_save_path'];
		foreach($path_arr as $key=>$path)
			$data['key_'.$key] = '@'.$_SERVER['DOCUMENT_ROOT'].$path;
		$res = curl_init();
		curl_setopt($res, CURLOPT_URL, "http://{$host}/".$this->self_config['upload_api']);
		curl_setopt($res, CURLOPT_HTTPHEADER, array($host_dam));
		curl_setopt($res, CURLOPT_POST, true);
		curl_setopt($res, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($res, CURLOPT_POSTFIELDS, $data);
		$result = curl_exec($res);
		
		$info = curl_getinfo($res);
		$errno = curl_errno($res);
		$error = curl_error($res);
		curl_close($res);
		
		return array(	'new_path' => json_decode($result, true), 
						'info' => $info, 
						'errno' => $errno, 
						'error' => $error);
	}
	

    //外部推广预览
    function page_preview() 
	{
		//如果有按钮图片先存放某个地方
		$folder = "/img/" . date("Ym/d/");
		$this->mkDirs(UPLOAD_PATH . $folder);
		$path = $folder . time() . rand(1000,9999) . $_FILES['btn_pic']['name'];
		$img_path = UPLOAD_PATH . $path;
		//$upFilePath = "../attachment/";
		$ok=@move_uploaded_file($_FILES['btn_pic']['tmp_name'],$img_path);
		if($ok === FALSE)
		{
			$file_infor = 0;
			echo '{"file_infor":"' . $file_infor .'"}';
		}else
		{
			$file_infor = 1;
			echo '{"file_infor":"' . $file_infor . '","file_url":"' . $path . '"}';
		}
    }

    //编辑外部推广显示
    function promotion_edit_show() 
	{
        $model = new Model();
        $id = $_GET['id'];
        $result = $model->table('sj_external_promotion')->where(array('id' => $id))->find();
        $page_content = $result['page_content'];
        // 展示需要将图片host换上去
        $page_content = htmlspecialchars_decode($page_content);
        $page_content = str_replace($this->self_config['upload_api_image_host'],$this->self_config['upload_api_image_url'], $page_content);
        $result['page_content'] = $page_content;
		
		//渠道
		$channel_model = M('channel');
		if($result['channel_id'])
		{
			$chl = $channel_model->field("`cid`,`chname`")->where(array('cid' => $result['channel_id']))->find();
			$chl_name = $chl['chname'];
		}
		else
		{
			$chl_name = "通用";
		}
        /*$cookstr = substr($result['channel_id'], 1, -1);
        $array = explode(',', $cookstr);
		$channel_model = M('channel');
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
		$this->assign('chl_list', $chl);*/
		$this->assign('chname', $chl_name);
		//推荐内容
		$jump_id = $result['content_id'];
		$list = $model -> table('sj_common_jump') -> where(array('id' => $jump_id)) -> find();
		$this -> assign('list',$list);
		
        $this->assign("result", $result);
        $this->assign('attachment_host',$this->self_config['upload_api_image_url']);
        $this->assign('function_name', $_GET['from']);
        // 记录页数参数，方便跳回第几页
        $url_param = "";
        foreach ($_GET as $key => $value) {
            if ($key == 'id' || $key == 'from')
                continue;
            if ($url_param != '')
                $url_param .= "&";
            if ($value != '')
                $url_param .= "{$key}={$value}";
        }
        $this->assign('url_param', $url_param);
        $this->display();
    }

    //编辑外部推广数据
    function promotion_edit_do() 
	{
        $model = new Model();
        $page_name = $_POST['page_name'];
		$id = $_POST['id'];
		$content_id = $_POST['content_id'];
        if (!$page_name) 
		{
            $this->error("页面名称不能为空");
        }
        $btn_type = $_POST['btn_type'];
        if($btn_type ==1)
		{
			$text_content = $_POST['text_content'];
			if (!$text_content) 
			{
				$this->error("文本内容不能为空");
			}
			$data['btn_text_color'] = $_POST['text_color'];
			$data['btn_text_font_size'] = $_POST['text_font_size'];
			$data['btn_text_alignment'] = $_POST['text_alignment'];
			$data['btn_text_content'] = $text_content;
			$data['btn_pic'] = "";//按钮图片的字段就是为空
		}
		else
		{
			$btn_pic = $_FILES['btn_pic'];
			if ($btn_pic['size']) 
			{
				$high_wd = getimagesize($btn_pic['tmp_name']);
				
				$width = $high_wd[0];
				$height = $high_wd[1];
				
				if ($width > 480) 
				{
					$this -> error("按钮图片宽度不能超过480px");
				}
				preg_match("/\.(?:png|jpg|jpeg)$/i", $btn_pic['name'], $matches);
				if (!$matches) 
				{
					$this->error("上传图片类型错误！");
				}

				/*if ($btn_pic['size'] > 35 * 1024) {
					$this->error("默认图片尺寸大于35K");
				}*/
				$path=date("Ym/d/",time());
				$config = array(
					'multi_config' => array(
						'btn_pic' => array(
							'savepath' => UPLOAD_PATH . '/img/' . $path,
							'saveRule' => 'getmsec'
						),
					),
				);
				$list = $this->_uploadapk(0, $config);
				$news_url = $list['image'][0]['url'];
				$data['btn_pic'] = $news_url;
				//按钮文本清空数据或者是默认值
				$data['btn_text_color'] = "#FFFFFF";
				$data['btn_text_font_size'] = 9;
				$data['btn_text_alignment'] = 1;
				$data['btn_text_content'] = "";
			}
			else 
			{
				$have_result = $model->table('sj_external_promotion')->where(array('id' => $id))->find();
				if(!$have_result['btn_pic'])
				{
					$this->error("按钮图片不能为空");
				}
			}
		}
		//推荐内容
		if($_POST['content_type'])
		{
			//推荐内容处理 合并
            $rcontent=ContentTypeModel::saveRecommendContent($_POST,'',$map);
			if($rcontent !==true)
			{
				$this -> error($rcontent);
			}
			// 更新时间
            $map['update_at'] = time();
		}
		else
		{
			$this->error("推荐内容不能为空");
		}
        //渠道
		$channel_id=$_POST['cid'];
		if($channel_id)
		{
			$data['channel_id'] = $channel_id;
		}
		/*$channel_id_array=$_POST['cid'];
        $cids = array_unique($channel_id_array);
		if (count($cids) > 0) {
			$s = implode(',', $cids);
			$s = ",{$s},";
			$data['channel_id'] = $s;
		}*/
		//页面标题
		$page_title = $_POST['page_title'];
        if (empty($page_title))
		{
            $this->error("页面标题不能为空");
        }
		//页面正文
        $page_content = $_POST['editor_content'];
        if (empty($page_content) || $page_content == "<p>&nbsp;</p>") 	
		{
            $this->error("页面正文不能为空");
        }
        $content = stripcslashes($_POST['editor_content']);
		// 1、获取内容中的图片路径
		$path_arr = $this->parse_images($content);
		if(count($path_arr))
		{
			// 2、将图片复制到服务器指定位置
			$result = $this->upload_images($path_arr);
			
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
		//处理数据入库
        $data['page_name'] = $page_name;
        $data['page_btn_type'] = $btn_type;
       
		
		$data['page_title'] = $page_title;
		
        $data['preview_url'] = $this->self_config['static_url'];
        $data['page_content'] = $content;
        $data['create_tm'] = time();
        $data['update_tm'] = time();
        $data['status'] = 1;

		// 保存通用跳转
		$where_jump = array('id'=>$content_id);
		$log_jump = $this->logcheck($where_jump, 'sj_common_jump', $map, $model);
		$ret = $model->table('sj_common_jump')->where($where_jump)->save($map);
		
		//保存推广数据
		$where = array('id'=>$id);
		$log = $this->logcheck($where, 'sj_external_promotion', $data, $model);
		$result = $model->table('sj_external_promotion')->where($where)->save($data);

        if ($result) 
		{
            $this->writelog("外部推广_已编辑id为{$id}的推广：" . $log."\n".$log_jump, 'sj_external_promotion',$id,__ACTION__ ,'','edit');
            $from = $_POST['from'];
            $this->assign('jumpUrl', "/index.php/Sendnum/ExPromotion/external_promotion_list/{$from}?{$_POST['url_param']}");
            $this->success("编辑成功");
        } else {
            $this->error("编辑失败");
        }
    }

    //外部推广删除
    function promotion_del() 
	{
        $model = new Model();
		$id=$_GET['id'];
		$have_result = $model->table('sj_external_promotion')->where(array('id' => $id))->find();
		$content_id = $have_result['content_id'];
		
		$data['status'] = $map['status'] = 0;
		$data['update_tm'] = time();
		$map['update_at'] =time();
		//先删除通用跳转的数据
		$result_jump = $model->table('sj_common_jump')->where(array('id' => $content_id))->save($map);
		//删除外部推广数据
		$result = $model->table('sj_external_promotion')->where(array('id' => $id))->save($data);
		
		if ($result&&$result_jump)
		{
			$this->writelog("活动管理_外部推广_已删除id为{$id}的数据", 'sj_external_promotion',$id,__ACTION__ ,'','del');
			$this -> assign("jumpUrl","/index.php/Sendnum/ExPromotion/external_promotion_list");
			$this->success("删除成功");
		}
		else
		{
			$this->success("删除失败");
		}
    }
}