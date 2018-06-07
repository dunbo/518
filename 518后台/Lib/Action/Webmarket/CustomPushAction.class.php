<?php
class CustomPushAction extends CommonAction {
	
	private $icon_width=48;
    private $icon_height=48;
	private $pub_pic_width=442;
    private $pub_pic_height=228;
	
	//文本编辑器内容保存
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
		$pro_env = C('PRO_ENV');
		if($pro_env == 1){
			//线上
			$this->self_config['upload_api_host'] = '192.168.1.18';
			$this->self_config['upload_api_domain'] = 'dummy.goapk.com';
		}else if($pro_env == 2){
			$this->self_config['upload_api_host'] = '118.26.203.23';
			$this->self_config['upload_api_domain'] = 'dummy.goapk.com'; //上传图片接口域名
		}else if($pro_env == 3){
			$this->self_config['upload_api_host'] = '192.168.0.99';
			$this->self_config['upload_api_domain'] = '9.dummy.goapk.com';
		}
		
		$this->assign('self_config', $this->self_config);
	}
	
    function custom_push_list()
	{
        $sj_custom_push=M("custom_push");
        $channel=M("channel");
        import("@.ORG.Page");
        $where="status=1 ";
        if($_GET['zh_type']){
            if($_GET['zh_type']==1){//推送中
                $where .= " and start_at <= ".time()." and end_at >= ".time()."";
                $this->assign("zh_type",$_GET['zh_type']);
            }elseif($_GET['zh_type']==2){//未推送
                $where .= " and start_at > ".time()."";
                $this->assign("zh_type",$_GET['zh_type']);
            }elseif($_GET['zh_type']==3){//已推送
                $where .= " and end_at < ".time()."";
                $this->assign("zh_type",$_GET['zh_type']);
			}
        }

        if(!empty($_GET['soft_name'])){
            $where.=" and monitor_soft_name like '%{$_GET['soft_name']}%'";
            $this->assign("so_soft_name",$_GET['soft_name']);
        }
        if(!empty($_GET['package'])){
            $where.=" and monitor_soft_package like '%{$_GET['package']}%' or replace_soft_packages like '%{$_GET['package']}%'";
            $this->assign("so_package",$_GET['package']);
        }
        if( !empty($_GET['fromdate']) ) {
        	$where .=" and start_at >=".strtotime($_GET['fromdate']);
        	$this->assign("so_start_tm",$_GET['fromdate']);
        }
        if( !empty($_GET['todate']) ) {
        	$where .= " and end_at<=".strtotime($_GET['todate']);
        	$this->assign("so_end_tm",$_GET['todate']);
        }
		if(!empty($_GET['search_push_type']))
		{
			$where.=" and push_type='{$_GET['search_push_type']}'";
            $this->assign("search_push_type",$_GET['search_push_type']);
		}

        $count = $sj_custom_push->where($where)->count();
        $param = http_build_query($_GET);
        $Page = new Page($count, 10, $param);
        $custom_push_list=$sj_custom_push->where($where)->order("id desc")->limit($Page->firstRow . ',' . $Page->listRows)->select();
        
        foreach ($custom_push_list as $k => $v) 
		{
			$suffix_arr = explode(',',$v['package_replace_suffix']);
			$replace_packages_arr = explode(',',$v['replace_soft_packages']);
			$last_pkgs = '';
			foreach($suffix_arr as $su_k=>$su_v)
			{
				$su_v = substr($su_v,1);
				foreach($replace_packages_arr as $re_k => $re_v)
				{
					$every_package_arr = explode('.',$re_v);
					$every_package_arr[count($every_package_arr)-1] = $su_v;
					$every_last_pkg =implode('.',$every_package_arr);
					$last_pkgs .=$every_last_pkg.',';
				}
			}
			$last_pkgs = substr($last_pkgs,0,strlen($last_pkgs)-1); 
			$custom_push_list[$k]['have_replace_packages'] = $last_pkgs;
			
			$cid_str = preg_replace('/^,/','',$v['cid']);
			$cid_str = preg_replace('/,$/','',$cid_str);
            $array = explode(',', $cid_str);
            $cname = $channel->where("cid in ({$cid_str})")->findAll();
			if (in_array("0",$array))
			{
				$custom_push_list[$k]['chname'] .= "<p>通用</p>";
			}
            foreach ($cname as $k1 => $v1) 
			{
				if($custom_push_list[$k]['chname']=="")
				{
					if($k1==0)
					{
						if(mb_strlen($v1['chname'],'utf-8')>10)
						{
						 $short_chname=mb_substr($v1['chname'],0,10,'utf-8');
						 $custom_push_list[$k]['chname'].="<p>{$short_chname}</p>";
						}
						else
						{
						   $custom_push_list[$k]['chname'] .= "<p>{$v1['chname']}</p>";
						}
					}
					if($k1>=1)
					{
					  $short=mb_substr($v1['chname'],0,6,'utf-8');
					  $custom_push_list[$k]['chname'] .= "<p>{$short}...</p>";
					  break;
					}
				}
				else
				{
					if($k1>=0)
					{
					  $short=mb_substr($v1['chname'],0,6,'utf-8');
					  $custom_push_list[$k]['chname'] .= "<p>{$short}...</p>";
					  break;
					}
				}
            }
			$content_type_arr=array('软件','活动','专题','页面','网页','礼包','攻略');
			$custom_push_list[$k]['content_type_name']=$content_type_arr[$v['content_type']-1];
			//获取推送节点名称
			$push_node_arr=array('启动','安装成功','退出','卸载成功');
			$custom_push_list[$k]['push_node_name']=$push_node_arr[$v['push_node']-1];
			//获取推送频率名称
			$push_frequency_arr=array('不限','推送一次','推送二次','推送三次');
			$custom_push_list[$k]['push_frequency_name']=$push_frequency_arr[$v['push_frequency']];
			//推送类型
			$push_type_arr=array('','定制推送','安智游戏助手','弹窗样式1','弹窗样式2','弹窗样式3');
			$custom_push_list[$k]['push_types']=$push_type_arr[$v['push_type']];
			//推送状态
			if($v['start_at']>time())
			{
				$custom_push_list[$k]['push_status']="未推送";
			}
			elseif($v['end_at']<time())
			{
				$custom_push_list[$k]['push_status']="已推送";
			}
			else
			{
				$custom_push_list[$k]['push_status']="推送中";
			}
			//显示导向页面
			$content_type = $v['content_type'];
			if ($content_type == 2) 
			{
                // 活动名称
                $custom_push_list[$k]['activity_name'] = ContentTypeModel::convertActivityId2ActivityName($v['activity_id']);
            } else if ($content_type == 3) 
			{
				// 专题名称
				$custom_push_list[$k]['feature_name'] = ContentTypeModel::convertFeatureId2FeatureName($v['feature_id']);
            } else if ($content_type == 4) 
			{
				// 页面名称
				$custom_push_list[$k]['page_name'] = ContentTypeModel::convertPageType2PageName($v['page_type']);
            }
        }
        if ($_GET['p'])
            $this->assign('p', $_GET['p']);
        else

        $this->assign('p', '1');
        $show = $Page->show();
        $this->assign("page", $show);
        $this->assign("push_list",$custom_push_list);
        $this->display();
    }

    function custom_push_add()
	{
		/**
        *获取专题类别列表
		**/
        $feature_db=M('feature');
        $map['status']=1;
        $feature_list=$feature_db->where($map)->field('feature_id,name')->select();
        $this->assign('featurelist',$feature_list);

		/**
        *获取活动列表
		**/
        $activity = D('Sj.Activity');
        $activity_list = $activity->where(array('status' => 1))->field('id,name')->select();
        $this -> assign("push_type",$_GET['push_type']);
        $this->assign('activitylist', $activity_list);
		//定制推送软件icon图片大小
		$this->assign('icon_width',$this->icon_width);
		$this->assign('icon_height',$this->icon_height);
		
		//V6.4定制推送新增加弹窗样式 宣传图片大小
		$this->assign('pub_pic_width',$this->pub_pic_width);
		$this->assign('pub_pic_height',$this->pub_pic_height);
		
		
		//合作形式
		$util = D("Sj.Util");
		$typelist = $util->getHomeExtentSoftTypeList();
		$this->assign('typelist',$typelist);
		
        $this->display();
    }
    function custom_push_addto()
	{
        $model = new Model();
        $sj_custom_push=M("custom_push");

		$monitor_soft_package=trim($_POST['monitor_soft_package']);
		$monitor_soft_package=trim($monitor_soft_package,',');
		$replace_soft_packages=trim($_POST['replace_soft_packages']);
		$replace_soft_packages=trim($replace_soft_packages,',');
		
		$replace_suffix=implode(',', $_POST['replace_suffix']);
		
		$monitor_soft_name=trim($_POST['monitor_soft_name']);
		$push_type = trim($_POST['push_type']);
		if(!$monitor_soft_package&&!$replace_soft_packages)
		{
			$this -> error("监控包名为必填项");
		}
		else
		{
			$map['monitor_soft_package']=$monitor_soft_package;
			$map['replace_soft_packages']=$replace_soft_packages;//增加多个包名和替换后缀 2016-10-27
			$map['package_replace_suffix']=$replace_suffix;
		}
		if(!$monitor_soft_name)
		{
			$this -> error("监控软件名为必填项");
		}
		else
		{
			$map['monitor_soft_name']=$monitor_soft_name;
		}
		$map['push_node']=trim($_POST['push_node']);
		$map['push_frequency']=trim($_POST['push_frequency']);
		//增加概率
		if($_POST['chance'])
		{
			$matches = "/^[1-9][0-9]*$/";
			if (!preg_match($matches,trim($_POST['chance']))) 
			{
				$this -> error("概率为正整数");
			}
			else
			{
				$map['chance']=trim($_POST['chance']);
			}
		}
		else
		{
			$map['chance'] = 100;
		}

		//每日推送时间段
		$daily_count = count($_POST['daily_fromtime']);
		//判断每一个时间段格式是不是正确
		if($daily_count>0)
		{
			for($i=0;$i<$daily_count;$i++)
			{
				if (empty($_POST['daily_fromtime'][$i]) ^ empty($_POST['daily_totime'][$i])) 
				{
					$this -> error("请填写完整的推送时间段");
				}
				if (!empty($_POST['daily_fromtime'][$i]) && !empty($_POST['daily_totime'][$i])) 
				{
					if (!preg_match('/^[0-9]{2}:[0-9]{2}:[0-9]{2}$/', $_POST['daily_fromtime'][$i])
						|| !preg_match('/^[0-9]{2}:[0-9]{2}:[0-9]{2}$/', $_POST['daily_totime'][$i])
						|| !strtotime($_POST['daily_fromtime'][$i]) || !strtotime($_POST['daily_totime'][$i])
						) 
					{
						$this -> error("推送时间段时间格式错误");
					}
					if ($_POST['daily_totime'][0] <= $_POST['daily_fromtime'][0]) 
					{
						$this -> error("推送时间段结束时间应大于开始时间");
					}
				}
			}
			//逻辑判断 填写的每个时间段不能重叠 
			for($i=0;$i<$daily_count;$i++)
			{
				for($j=$i+1;$j<$daily_count;$j++)
				{
					if($_POST['daily_fromtime'][$i]<$_POST['daily_totime'][$j]&&$_POST['daily_fromtime'][$j]<$_POST['daily_totime'][$i])
					{
						$this -> error("填写的时间段不能有重复");
					}
				}
				if($_POST['daily_fromtime'][$i]!==""&&$_POST['daily_totime'][$i]!=="")
				{
					$map['daily_time'] .= $_POST['daily_fromtime'][$i].",".$_POST['daily_totime'][$i].";";
				}
			}
			if($map['daily_time'])
			{
				$map['daily_time'] = substr($map['daily_time'],0,-1);//去掉末尾的分号;
			}
		}
		
		//开始时间和结束时间
		if($_POST['fromdate'] && $_POST['todate'])
		{
			$map['start_at']=strtotime($_POST['fromdate']);
			$map['end_at']=strtotime($_POST['todate']);
			if($map['start_at'] > $map['end_at'])
			{
				$this -> error("开始时间不能大于结束时间");
			}
        }
		else
		{
            $this -> error("请选择开始时间和结束时间");
		}
		if($push_type==1||$push_type==3||$push_type==4||$push_type==5)//V6.2新增 1表示定制推送  2表示安智市场手机助手（没有推荐内容和描述详情） V6.4增加弹窗样式1、2、3都有推荐内容和描述详情 在此基础上还有其他字段
		{
			if(trim($_POST['pop_show_time']))
			{
				$matches = "/^[0-9]\d*$/";
				if (!preg_match($matches,trim($_POST['pop_show_time']))) 
				{
					$this -> error("弹窗显示时间为正整数");
				}
				else
				{
					$map['pop_show_time']=trim($_POST['pop_show_time']);
				}
			}
			else
			{
				$this -> error("弹窗显示时间不能为空");
			}
			//处理推荐内容
			$rcontent=ContentTypeModel::saveRecommendContent($_POST,'',$map);
			if($rcontent!==true)
			{
				$this -> error($rcontent);
			}
			if($map['package']){
				//屏蔽软件上排期时报警需求 新增  yuesai
				$AdSearch = D("Sj.AdSearch");
				$shield_error=$AdSearch->check_shield($map['package'],$map['start_at'],$map['end_at']);
				// $shield_error.=$AdSearch->check_shield_old($map['package'],0,1);
				if($shield_error){
					$this -> error($shield_error);
				}
			}
			//定制推送 已安装5，低版本5，
			$content_type=$_POST['content_type'];
			//$map['uninstall_setting'] = 1; 后来新增点击后下载的选项
			$map['install_setting'] =5;
			$map['lowversion_setting'] = 5;
			
			//定制推送 增加推荐内容为软件是 增加展示位置、展示ICON和点击后操作
			if($content_type==1)
			{
				$map['show_position'] = $_POST['show_positon'];
				//icon图片处理
				if($_FILES['show_icon']['size'])
				{
					$map['show_icon'] = $_FILES['show_icon'];
						
					//检查图片尺寸
					$icon_arr = getimagesize($map['show_icon']['tmp_name']);
					if (!$icon_arr) 
					{
						$this->error("上传icon图片出错");
					}
					$width = $icon_arr[0];
					$height = $icon_arr[1];
					if ($width != $this->icon_width || $height != $this->icon_height) 
					{
						$this->error("请添加尺寸为{$this->icon_width}*{$this->icon_height}的图片");
					}
					//检查图片格式
					if ($map['show_icon']['type'] != 'image/png' && $map['show_icon']['type'] != 'image/jpg'&&$map['show_icon']['type'] != 'image/jpeg') 
					{
						$this->error("请添加icon图片格式为：jpg，png的图片");
					}
					include_once SERVER_ROOT. '/tools/functions.php';
					//上传图片
					// 将图片存储起来
					$suffix = preg_match("/\.(jpg|png)$/", $map['show_icon']['name'],$matches);
					if($matches)
					{
						$suffix = $matches[0];
					}
					$folder = "/img/" . date("Ym/d/");
					$this->mkDirs(UPLOAD_PATH . $folder);
					$relative_path = $folder . time() . '_' . rand(1000,9999) . $suffix;
					$img_path = UPLOAD_PATH . $relative_path;
					$ret = move_uploaded_file($map['show_icon']['tmp_name'], $img_path);
					$map['show_icon'] = $relative_path;
				}
				else
				{
					$map['show_icon'] ="";
				}
			}
			if($push_type==3)
			{
				$push_text=trim($_POST['editor_content']);
			}
			else
			{
				$push_text=trim($_POST['push_text']);
			}
			
			if($push_text)
			{
				
				if($push_type==1)
				{
					if($content_type==1)
					{
						if(mb_strlen($push_text,'utf-8')>50)
						{
							$this->error("推荐内容为软件时，描述详情不能超过50个字");
						}
					}
					else
					{
						if(mb_strlen($push_text,'utf-8')>18)
						{
							$this->error("推荐内容为非软件时，描述详情不能超过18个字");
						}
					}
				}
				if($push_type==3)//6.4弹窗内容是html文本
				{
					$content = stripcslashes($push_text);
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
						$push_text = str_replace($path_arr, $new_path_arr, $content);
					}
				}
			}
			else
			{
				$this->error("描述详情为必填项");
			}
			$map['push_text']=$push_text;
			$map['content_type'] = $content_type;
		}
		if($push_type==2)
		{
			//V6.2选择安智市场手机助手 对应我司包名为必填项
			$my_soft_package = $_POST['my_soft_package'];
			$my_soft_name = $_POST['my_soft_name'];
			if(!$my_soft_package)
			{
				$this->error("对应我司包名为必填项");
			}
			else
			{
				$soft_name_where=array(
					'package'=>$my_soft_package,
					'status'=>1,
					'hide'=>1,
				);
				$find_result = $model->table('sj_soft')->where($soft_name_where)->find();
				if($find_result)
				{
					$map['my_own_soft_package']=$my_soft_package;
				}
				else
				{
					$this->error("对应我司包名不存在");
				}
			}
			if(!$my_soft_name)
			{
				$this->error("对应我司软件名称为必填项");
			}
			$map['content_type'] = 0;
			$map['push_text']='';
			$map['pop_show_time']=0;//弹窗展示时间也没必要
		}
		//V6.4新增加 弹窗样式1、2、3
		if($push_type==3||$push_type==5)
		{
			$title_content = $_POST['title_content'];
			if(!$title_content)
			{
				$this->error("标题内容是必填项");
			}
			else
			{
				$map['title_content'] = $title_content;
			}
		}
		if($push_type==4||$push_type==5)
		{
			//宣传图片处理
			if($_FILES['pub_pic']['size'])
			{
				$pub_pic = $_FILES['pub_pic'];
					
				//检查图片尺寸
				$pub_pic_arr = getimagesize($pub_pic['tmp_name']);
				if (!$pub_pic_arr) 
				{
					$this->error("上传宣传图片出错");
				}
				$pub_pic_width = $pub_pic_arr[0];
				$pub_pic_height = $pub_pic_arr[1];
				if ($pub_pic_width != $this->pub_pic_width || $pub_pic_height != $this->pub_pic_height) 
				{
					$this->error("请添加尺寸为{$this->pub_pic_width}*{$this->pub_pic_height}的图片");
				}
				//检查图片格式
				if ($pub_pic['type'] != 'image/png' && $pub_pic['type'] != 'image/jpg'&&$pub_pic['type'] != 'image/jpeg') 
				{
					$this->error("请添加宣传图片格式为：jpg，png的图片");
				}
				include_once SERVER_ROOT. '/tools/functions.php';
				//上传图片
				// 将图片存储起来
				$pic_suffix = preg_match("/\.(jpg|png)$/", $pub_pic['name'],$pic_matches);
				if($pic_matches)
				{
					$pic_suffix = $pic_matches[0];
				}
				$folder = "/img/" . date("Ym/d/");
				$this->mkDirs(UPLOAD_PATH . $folder);
				$pub_relative_path = $folder . time() . '_' . rand(1000,9999) . $pic_suffix;
				$pub_img_path = UPLOAD_PATH . $pub_relative_path;
				$ret = move_uploaded_file($pub_pic['tmp_name'], $pub_img_path);
				$map['pub_pic_url'] = $pub_relative_path;
			}
			else
			{
				$this->error("宣传图片是必填项");
			}
		}
		//V6.4新增加的样式 都有的
		if($push_type==3||$push_type==4||$push_type==5)
		{
			$btn_name = $_POST['btn_name'];
			if($btn_name)
			{
				$map['btn_name'] = $btn_name;
			}
			$map['closed_type'] = $_POST['closed_type'];
		}
		//V6.5新增加的样式 桌面红包
		if($push_type==6)
		{
			//处理桌面红包内容
			//桌面红包内容
			$size_arr['pop_width']=80;
			$size_arr['pop_height']=80;
			$size_arr['high_width']=106;
			$size_arr['high_height']=106;
			$size_arr['low_width']=60;
			$size_arr['low_height']=60;
			$size_arr['title_limit']=20;  //20个汉字 40个字符
			$size_arr['subtitle_limit']=14;
			$size_arr['des_limit']=20;
			$table="sj_custom_push";
			$info_list = array();
			$Market = D("Webmarket.MarketPush");
			$rcontent = $Market->desk_red_saved($_FILES,$_POST,'',$info_list,$size_arr,$table);
			if($rcontent!==true)
			{
				$this -> error($rcontent);
			}
			else
			{
				$map = array_merge($map,$info_list);
			}
			//覆盖人数
			if($_POST['red_cover_num'] == '全部'){
				$map['cover_num'] = 0;
			}else{
				$map['cover_num'] = trim($_POST['red_cover_num']);
			}
			if($_POST['cover_num']){
				if(!preg_match('/^\d+$/',$_POST['cover_num']) && $_POST['cover_num'] != "全部"){
					$this -> error("覆盖人数格式错误");
				}
			}
			
			//合作形式
			if(isset($_POST['co_type_desk']))
			{
				$map['co_type'] = $_POST['co_type_desk'];
			}else{
				$map['co_type'] = 0;
			}
		}
		$map['push_type'] = $push_type;
        $map['status']=1;
        $map['create_at']=time();
        $map['update_at']=time();
		//上传csv
		$filename=$_FILES['upload_file']['name'];
		if(!$filename&&!$_POST['csv_count'])
		{
			$map['csv_count'] = 0;
			$map['csv_url'] = "";
			$map['is_upload_csv'] = 0; //标注是否上传csv
		}
		if($filename&&!$_POST['csv_count'])
		{
			$this -> error("选择好的文件请点击上传才有效");
		}
		if($_POST['csv_count']&&$_POST['csv_url'])
		{
			$map['csv_count'] = $_POST['csv_count'];
			$map['csv_url'] = $_POST['csv_url'];
			$map['is_upload_csv'] = 1;
		}
		unset($_FILES['upload_file']);
        $map['oid'] = ','. implode(',', $_POST['oid']). ',';
        $map['firmware'] = ','. implode(',', $_POST['firmware']). ',';
        $map['version_code'] = ','. implode(',', $_POST['version_code']). ',';
        $abi = 0;
        foreach($_POST['abi'] as $v) {
            $abi += $v;
        }
        $map['abi'] = $abi;
        $channel_id_array=$_POST['cid'];
        $cids = array_unique($channel_id_array);
            if (count($cids) > 0) {
                $s = implode(',', $cids);
                $s = ",{$s},";
                $map['cid'] = $s;
            }

        $device_did_array=$_POST['did'];
        $dids = array_unique($device_did_array);
		if (count($dids) > 0) 
		{
			$d= implode(',', $dids);
			$d = ",{$d},";
			$map['device_did'] = $d;
		}

        $pro_city=array();
        $map['push_area'] = $_POST['area_value'];
		//增加排除区域
		$map['paichu_area'] = $_POST['paichu_area_value'];
		
		//可以重复  2015-9-16
		//添加之前 验证一下当前时间段是否有推荐内容
		/*$where=array(
			'start_at' => array('elt',$map['end_at']),
			'end_at' => array('egt',$map['start_at']),
			'status' => 1,
		);
		$have_result=$sj_custom_push->where($where)->find();
		if($have_result)
		{
			$this->error("时间段与ID为【{$have_result['id']}】有冲突,请重新填写");
		}
		else
		{
			$id=$sj_custom_push->add($map);
		}*/
		$id=$sj_custom_push->add($map);
        if($id) 
		{
			//桌面红包
			if($push_type==6)
			{
				//红包是直接发放类型
				$red_info = json_decode($info_list['desk_red_text'], true);
				$bind_ext_data	=	array('name'=>'桌面红包');
				if( $red_info['red_id'] ) {
					$bind_info = D('Sj.RedActivity')->bind_red_pagckage(1, $id, $red_info['red_id'],'', $red_info['task_id'], $bind_ext_data);
					if( $bind_info['STATUS'] == 1 ) {
						//更改sj_red_packet_conf中红包id为$_post['red_id']的状态
						$desk_status = array(
								'desktop' => 1, //是否是桌面红包 0，不是， 1，定制 2 push
						);
						$red_conf_update = $model->table("sj_red_packet_conf")->where(array('id'=>$red_info['red_id']))->save($desk_status);
						$this->writelog("修改ID".$red_info['red_id']."为定制推送的桌面红包",'sj_red_packet_conf',$red_info['red_id'],__ACTION__ ,"","edit");
					}else {
						$msg_error = $bind_info['MSG'];
						
						$red_info['red_type']		=	1;
						$red_info['red_soft_pkg']	=	'';
						$red_info['red_soft_name']	=	'';
						$red_info['red_id']			=	'';
						$red_info['task_id']		=	'';
						$red_info['red_num']		=	'';
						$red_info['totalmon']		=	'';
						$red_info['red_task_content1']	=	'';
						$red_info['red_task_content2']	=	'';
						$desk_data_json = array(
								'status'		=>	0,
								'desk_red_text'	=>	json_encode($red_info)
						);
						$sj_custom_push->where(array('id' => $id))->save($desk_data_json);
						$this->writelog('增加了名称ID为['.$id.']的定制推送信息', 'sj_custom_push', $id,__ACTION__ ,"","add");
						$this->error($msg_error);
					}
				}
			}
            $this->writelog('增加了名称ID为['.$id.']包名为['.$map['monitor_soft_package'].']的定制推送信息', 'sj_custom_push', $id,__ACTION__ ,"","add");
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/CustomPush/custom_push_list');
            $this->success("添加定制推送成功！");
        }
		else
        {
            $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/CustomPush/custom_push_list');
            $this->error("添加定制推送失败！");
        }
    }
    function custom_push_del()
	{
        $sj_custom_push=M("custom_push");
        $id=$_GET['id'];
		$where = array(
            'id' => $id,
            'status' => 1,
        );
		//推送方式为:桌面红包下解除绑定的红包
		$my_result = $sj_custom_push -> where(array('id' => $id)) -> find();
		$red_info = json_decode($my_result['desk_red_text'], true);
		if( $red_info['red_id'] ) {
			//解除绑定
			$bind_info = D('Sj.RedActivity')->bind_red_pagckage(1, $id, $red_info['red_id'], $red_info['red_id']);
			if( $bind_info['STATUS'] == 1) {
				$desk_status = array(
						'desktop' => 0, //是否是桌面红包 0，不是， 1，定制 2，push
				);
				$model = new model();
				$model->table("sj_red_packet_conf")->where(array('id'=>$red_info['red_id']))->save($desk_status);
			}else {
				$this->error($bind_info['MSG']);
			}
		}
        $map = array('status' => 0, 'update_at' => time());
        $ret = $sj_custom_push->table('sj_custom_push')->where($where)->save($map);
        if ($ret) 
		{
            $this->writelog("定制推送：删除了id为{$id}的内容", 'sj_custom_push', $id,__ACTION__ ,"","del");
            $this->success("删除成功");
        } 
		else 
		{
            $this->error("删除失败");
        }
    }
    function custom_push_edit()
	{
        $sj_custom_push=M("custom_push");
		/**
        *获取专题类别列表
		**/
        $feature_db=M('feature');
        $map['status']=1;
        $feature_list=$feature_db->where($map)->field('feature_id,name')->select();
        $this->assign('featurelist',$feature_list);

		/**
        *获取活动列表
		**/
        $activity = D('Sj.Activity');
        $activity_list = $activity->where(array('status' => 1))->field('id,name')->select();
        $this->assign('activitylist', $activity_list);
		
		$where=array(
			'id' => $_GET['id'],
		);
        $sj_custom_push_one=$sj_custom_push->where($where)->find();
		if($sj_custom_push_one['package_replace_suffix'])
		{
			$suffix_arr = explode(',',$sj_custom_push_one['package_replace_suffix']);
		}
		if($sj_custom_push_one['pop_show_time']==0)
		{
			$sj_custom_push_one['pop_show_times'] ="";
		}
		else
		{
			$sj_custom_push_one['pop_show_times'] =$sj_custom_push_one['pop_show_time'];
		}
        $sj_custom_push_one['start_tms'] = date('Y-m-d H:i:s',$sj_custom_push_one['start_at']);
        $sj_custom_push_one['end_tms'] = date('Y-m-d H:i:s',$sj_custom_push_one['end_at']);
		
		if($sj_custom_push_one['daily_time'])
		{
			//每日推送时间段处理 数据库存放样式这样的 开始,结束;开始,...
			$daily_time_str = $sj_custom_push_one['daily_time'];//去掉末尾的分号;
			$daily_time_arr = explode(";",$daily_time_str);//每一条开始时间和结束时间的数组
			for($i=0;$i<count($daily_time_arr);$i++)
			{
				foreach($daily_time_arr as $key=>$val)
				{
					$start_end_arr = explode(",",$val);
					$daily_time[$key][0] = $start_end_arr[0];
					$daily_time[$key][1] = $start_end_arr[1];
				}
			}
			$this->assign("daily_time_arr",$daily_time);
		}
		//文本编辑器内容展示
		if($sj_custom_push_one['push_type']==3)
		{
			$page_content = $sj_custom_push_one['push_text'];
			// 展示需要将图片host换上去
			$page_content = htmlspecialchars_decode($page_content);
			$page_content = str_replace($this->self_config['upload_api_image_host'],$this->self_config['upload_api_image_url'], $page_content);
			$sj_custom_push_one['push_text'] = $page_content;
		}
		//根据包名获取软件名称
		$soft_name_where=array(
			'package'=>$sj_custom_push_one['my_own_soft_package'],
			'status'=>1,
			'hide'=>1,
		);
		$find_result = $sj_custom_push->table('sj_soft')->where($soft_name_where)->find();
		$this->assign('my_soft_name',$find_result['softname']);
		
		
		//V6.5红包id和任务软件展示
		if($sj_custom_push_one['desk_red_text']&&$sj_custom_push_one['push_type']==6)
		{
			$red_detail_arr = json_decode($sj_custom_push_one['desk_red_text'],true);
			$this->assign('red_detail_arr',$red_detail_arr);
		}
		$this->assign('id',$_GET['id']);
		
		//合作形式
		$util = D("Sj.Util");
		$typelist = $util->getHomeExtentSoftTypeList($sj_custom_push_one['co_type']);
		$this->assign('typelist',$typelist);
		
        $this->assign("thepid",$sj_custom_push_one['feature_id']);
        $this->assign("custom_list",$sj_custom_push_one);
		$this->assign("suffix_arr",$suffix_arr);
        $this -> assign("p",$_GET['p']);
		//定制推送软件icon图片大小
		$this->assign('icon_width',$this->icon_width);
		$this->assign('icon_height',$this->icon_height);
		
		//V6.4定制推送新增加弹窗样式 宣传图片大小
		$this->assign('pub_pic_width',$this->pub_pic_width);
		$this->assign('pub_pic_height',$this->pub_pic_height);
		
        $this->display();
    }
    function custom_push_editto()
	{
        $model=M("custom_push");
        //$model = new Model();
        $channel_model = M('channel');
		$id= $_POST['id'];
		
		$monitor_soft_package=trim($_POST['monitor_soft_package']);
		$monitor_soft_package=trim($monitor_soft_package,',');
		$replace_soft_packages=trim($_POST['replace_soft_packages']);
		$replace_soft_packages=trim($replace_soft_packages,',');
		
		$replace_suffix=implode(',', $_POST['replace_suffix']);
		
		$monitor_soft_name=trim($_POST['monitor_soft_name']);
		$push_type = trim($_POST['push_type']);
		if($monitor_soft_package||$replace_soft_packages)
		{
			$map['monitor_soft_package']=$monitor_soft_package;
			$map['replace_soft_packages']=$replace_soft_packages;//增加多个包名和替换后缀 2016-10-27
			$map['package_replace_suffix']=$replace_suffix;
		}
		if($monitor_soft_name)
		{
			$map['monitor_soft_name']=$monitor_soft_name;
		}
		
		$map['push_node']=trim($_POST['push_node']);
		$map['push_frequency']=trim($_POST['push_frequency']);
		if($_POST['chance'])
		{
			$matches = "/^[1-9][0-9]*$/";
			if (!preg_match($matches,trim($_POST['chance']))) 
			{
				$this -> error("概率为正整数");
			}
			else
			{
				$map['chance']=trim($_POST['chance']);
			}
		}
		else
		{
			$map['chance'] = 100;
		}
		//每日推送时间段
		$daily_count = count($_POST['daily_fromtime']);
		//判断每一个时间段格式是不是正确
		if($daily_count>0)
		{
			for($i=0;$i<$daily_count;$i++)
			{
				if (empty($_POST['daily_fromtime'][$i]) ^ empty($_POST['daily_totime'][$i])) 
				{
					$this -> error("请填写完整的推送时间段");
				}
				if (!empty($_POST['daily_fromtime'][$i]) && !empty($_POST['daily_totime'][$i])) 
				{
					if (!preg_match('/^[0-9]{2}:[0-9]{2}:[0-9]{2}$/', $_POST['daily_fromtime'][$i])
						|| !preg_match('/^[0-9]{2}:[0-9]{2}:[0-9]{2}$/', $_POST['daily_totime'][$i])
						|| !strtotime($_POST['daily_fromtime'][$i]) || !strtotime($_POST['daily_totime'][$i])
						) 
					{
						$this -> error("推送时间段时间格式错误");
					}
					if ($_POST['daily_totime'][0] <= $_POST['daily_fromtime'][0]) 
					{
						$this -> error("推送时间段结束时间应大于开始时间");
					}
				}
			}
			//逻辑判断 填写的每个时间段不能重叠 
			for($i=0;$i<$daily_count;$i++)
			{
				for($j=$i+1;$j<$daily_count;$j++)
				{
					if($_POST['daily_fromtime'][$i]<$_POST['daily_totime'][$j]&&$_POST['daily_fromtime'][$j]<$_POST['daily_totime'][$i])
					{
						$this -> error("填写的时间段不能有重复");
					}
				}
				if($_POST['daily_fromtime'][$i]!==""&&$_POST['daily_totime'][$i]!=="")
				{
					$map['daily_time'] .= $_POST['daily_fromtime'][$i].",".$_POST['daily_totime'][$i].";";
				}
			}
			if($map['daily_time'])
			{
				$map['daily_time'] = substr($map['daily_time'],0,-1);//去掉末尾的分号;
			}
		}
		
		if($_POST['fromdate'] && $_POST['todate'])
		{
			$map['start_at']=strtotime($_POST['fromdate']);
			$map['end_at']=strtotime($_POST['todate']);
			if($map['start_at'] > $map['end_at'])
			{
				$this -> error("开始时间不能大于结束时间");
			}
        }
		else
		{
            $this -> error("请选择开始时间和结束时间");
		}
		if($push_type==1||$push_type==3||$push_type==4||$push_type==5)//V6.2新增 1表示定制推送  2表示安智市场手机助手（没有推荐内容和描述详情） V6.4增加弹窗样式1、2、3都有推荐内容和描述详情 在此基础上还有其他字段
		{
			//弹窗弹出时间
			if(trim($_POST['pop_show_time']))
			{
				$matches = "/^[0-9]\d*$/";
				if (!preg_match($matches,trim($_POST['pop_show_time']))) 
				{
					$this -> error("弹窗显示时间为正整数");
				}
				else
				{
					$map['pop_show_time']=trim($_POST['pop_show_time']);
				}
			}
			else
			{
				$this -> error("弹窗显示时间必填");
			}
			//处理推荐内容
			$rcontent=ContentTypeModel::saveRecommendContent($_POST,'',$map);
			if($rcontent!==true)
			{
				$this -> error($rcontent);
			}
			if($map['package']){
				//屏蔽软件上排期时报警需求 新增  yuesai
				$AdSearch = D("Sj.AdSearch");
				$shield_error=$AdSearch->check_shield($map['package'],$map['start_at'],$map['end_at']);
				// $shield_error.=$AdSearch->check_shield_old($map['package'],0,1);
				if($shield_error){
					$this -> error($shield_error);
				}
			}
			//定制推送 已安装5，低版本5，
			$content_type=$_POST['content_type'];
			//$map['uninstall_setting'] = 1; 后来新增点击后下载的选项
			$map['install_setting'] =5;
			$map['lowversion_setting'] = 5;
			
			//定制推送 增加推荐内容为软件是 增加展示位置、展示ICON
			if($content_type==1)
			{
				$map['show_position'] = $_POST['show_positon'];
				//icon图片处理
				if($_FILES['show_icon']['size'])
				{
					$map['show_icon'] = $_FILES['show_icon'];
						
					//检查图片尺寸
					$icon_arr = getimagesize($map['show_icon']['tmp_name']);
					if (!$icon_arr) 
					{
						$this->error("上传icon图片出错");
					}
					$width = $icon_arr[0];
					$height = $icon_arr[1];
					if ($width != $this->icon_width || $height != $this->icon_height) 
					{
						$this->error("请添加尺寸为{$this->icon_width}*{$this->icon_height}的图片");
					}
					//检查图片格式
					if ($map['show_icon']['type'] != 'image/png' && $map['show_icon']['type'] != 'image/jpg'&&$map['show_icon']['type'] != 'image/jpeg') 
					{
						$this->error("请添加icon图片格式为：jpg，png的图片");
					}
					include_once SERVER_ROOT. '/tools/functions.php';
					//上传图片
					// 将图片存储起来
					$suffix = preg_match("/\.(jpg|png)$/", $map['show_icon']['name'],$matches);
					if($matches)
					{
						$suffix = $matches[0];
					}
					$folder = "/img/" . date("Ym/d/");
					$this->mkDirs(UPLOAD_PATH . $folder);
					$relative_path = $folder . time() . '_' . rand(1000,9999) . $suffix;
					$img_path = UPLOAD_PATH . $relative_path;
					$ret = move_uploaded_file($map['show_icon']['tmp_name'], $img_path);
					$map['show_icon'] = $relative_path;
				}
			}
			if($push_type==3)
			{
				$push_text=trim($_POST['editor_content']);
			}
			else
			{
				$push_text=trim($_POST['push_text']);
			}
			
			if($push_text)
			{
				
				if($push_type==1)
				{
					if($content_type==1)
					{
						if(mb_strlen($push_text,'utf-8')>50)
						{
							$this->error("推荐内容为软件时，描述详情不能超过50个字");
						}
					}
					else
					{
						if(mb_strlen($push_text,'utf-8')>18)
						{
							$this->error("推荐内容为非软件时，描述详情不能超过18个字");
						}
					}
				}
				if($push_type==3)//6.4弹窗内容是html文本
				{
					$content = stripcslashes($push_text);
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
						$push_text = str_replace($path_arr, $new_path_arr, $content);
					}
				}
				$map['push_text']=$push_text;
			}
			$map['content_type'] = $content_type;
		}
		if($push_type==2)
		{
			//如果是安智手机助手 推荐内容的一些字段全是为空
			$map['package'] = '';
			$map['uninstall_setting'] = '';
			$map['install_setting'] = '';
		    $map['start_to_page'] = '';
			$map['lowversion_setting'] = '';
			$map['page_flag'] = '';
			$map['activity_id'] ='';
			$map['feature_id'] = '';
			$map['page_type'] = '';
			$map['page_id1'] = '';
			$map['page_id2'] = '';
			$map['website'] = '';
			$map['website_open_type'] = 1;
			$map['gift_id'] ='';
			$map['strategy_id'] ='';
			$map['content_type'] = 0;
			$map['push_text']='';
			$map['show_position']=0;
			$map['show_icon']='';
			$map['pop_show_time']=0;
			//V6.4新增加的样式
			$map['title_content']="";
			$map['pub_pic_url']="";
			$map['btn_name']="";
			$map['closed_type']=1;
			//V6.2选择安智市场手机助手 对应我司包名为必填项
			$my_soft_package = $_POST['my_soft_package'];
			$my_soft_name = $_POST['my_soft_name'];
			if(!$my_soft_package)
			{
				$this->error("对应我司包名为必填项");
			}
			else
			{
				$soft_name_where=array(
					'package'=>$my_soft_package,
					'status'=>1,
					'hide'=>1,
				);
				$find_result = $model->table('sj_soft')->where($soft_name_where)->find();
				if($find_result)
				{
					$map['my_own_soft_package']=$my_soft_package;
				}
				else
				{
					$this->error("对应我司包名不存在");
				}
			}
			if(!$my_soft_name)
			{
				$this->error("对应我司软件名称为必填项");
			}
		}
		//V6.4新增加 弹窗样式1、2、3
		if($push_type==3||$push_type==5)
		{
			$title_content = $_POST['title_content'];
			if(!$title_content)
			{
				$this->error("标题内容是必填项");
			}
			else
			{
				$map['title_content'] = $title_content;
			}
		}
		if($push_type==4||$push_type==5)
		{
			//宣传图片处理
			if($_FILES['pub_pic']['size'])
			{
				$pub_pic = $_FILES['pub_pic'];
					
				//检查图片尺寸
				$pub_pic_arr = getimagesize($pub_pic['tmp_name']);
				if (!$pub_pic_arr) 
				{
					$this->error("上传宣传图片出错");
				}
				$pub_pic_width = $pub_pic_arr[0];
				$pub_pic_height = $pub_pic_arr[1];
				if ($pub_pic_width != $this->pub_pic_width || $pub_pic_height != $this->pub_pic_height) 
				{
					$this->error("请添加尺寸为{$this->pub_pic_width}*{$this->pub_pic_height}的图片");
				}
				//检查图片格式
				if ($pub_pic['type'] != 'image/png' && $pub_pic['type'] != 'image/jpg'&&$pub_pic['type'] != 'image/jpeg') 
				{
					$this->error("请添加宣传图片格式为：jpg，png的图片");
				}
				include_once SERVER_ROOT. '/tools/functions.php';
				//上传图片
				// 将图片存储起来
				$pic_suffix = preg_match("/\.(jpg|png)$/", $pub_pic['name'],$pic_matches);
				if($pic_matches)
				{
					$pic_suffix = $pic_matches[0];
				}
				$folder = "/img/" . date("Ym/d/");
				$this->mkDirs(UPLOAD_PATH . $folder);
				$pub_relative_path = $folder . time() . '_' . rand(1000,9999) . $pic_suffix;
				$pub_img_path = UPLOAD_PATH . $pub_relative_path;
				$ret = move_uploaded_file($pub_pic['tmp_name'], $pub_img_path);
				$map['pub_pic_url'] = $pub_relative_path;
			}
			else
			{
				//查看以前的数据是否有图片  没图片就报错
				$is_have_where=array(
					'id'=>$id,
				);
				$is_result = $model->table('sj_custom_push')->where($is_have_where)->find();
				if(!$is_result['pub_pic_url'])
				{
					$this->error("请上传宣传图片！");
				}
			}
		}
		//V6.4新增加的样式 都有的
		if($push_type==3||$push_type==4||$push_type==5)
		{
			$btn_name = $_POST['btn_name'];
			if($btn_name)
			{
				$map['btn_name'] = $btn_name;
			}
			else
			{
				$map['btn_name'] = "";
			}
			$map['closed_type'] = $_POST['closed_type'];
		}
		//V6.5新增加的样式 桌面红包
		if($push_type==6)
		{
			//处理桌面红包内容
			//桌面红包内容
			$size_arr['pop_width']=80;
			$size_arr['pop_height']=80;
			$size_arr['high_width']=106;
			$size_arr['high_height']=106;
			$size_arr['low_width']=60;
			$size_arr['low_height']=60;
			$size_arr['title_limit']=20;  //20个汉字 40个字符
			$size_arr['subtitle_limit']=14;
			$size_arr['des_limit']=20;
			$table="sj_custom_push";
			$info_list = array();
			$Market = D("Webmarket.MarketPush");
			$rcontent=$Market->desk_red_saved($_FILES,$_POST,$id,$info_list,$size_arr,$table);
			if($rcontent!==true)
			{
				$this -> error($rcontent);
			}else {
				$map = array_merge($map,$info_list);
			}
			//覆盖人数
			if($_POST['red_cover_num'] == '全部'){
				$map['cover_num'] = 0;
			}else{
				$map['cover_num'] = trim($_POST['red_cover_num']);
			}
			if($_POST['cover_num']){
				if(!preg_match('/^\d+$/',$_POST['cover_num']) && $_POST['cover_num'] != "全部"){
					$this -> error("覆盖人数格式错误");
				}
			}
			//合作形式
			if(isset($_POST['co_type_desk']))
			{
				$map['co_type'] = $_POST['co_type_desk'];
			}else{
				$map['co_type'] = 0;
			}
		}
		
		$map['push_type'] = $push_type;
        $map['status']=1;
        $map['update_at']=time();
		//csv文件
		$filename=$_FILES['upload_file']['name'];
		if(!$filename&&!trim($_POST['csv_count'])&&trim($_POST['have_pre_dl']))
		{
			$map['csv_count'] = trim($_POST['pre_dl_count']);
			$map['csv_url'] = trim($_POST['have_pre_dl']);
			$map['is_upload_csv'] = 1;
		}
		if(!$filename&&!$_POST['csv_url']&&!trim($_POST['have_pre_dl']))
		{
			$map['csv_count'] = 0;
			$map['csv_url'] = "";
		}
		if($filename&&!$_POST['csv_count'])
		{
			$this -> error("选择好的文件请点击上传才有效");
		}
		if(trim($_POST['csv_url'])&&trim($_POST['csv_count']))
		{
			$map['csv_count'] = $_POST['csv_count'];
			$map['csv_url'] = $_POST['csv_url'];
			$map['is_upload_csv'] = 1;
		}
		unset($_FILES['upload_file']);
        $map['oid'] = ','. implode(',', $_POST['oid']). ',';
        $map['firmware'] = ','. implode(',', $_POST['firmware']). ',';
        $map['version_code'] = ','. implode(',', $_POST['version_code']). ',';
        $abi = 0;
        foreach($_POST['abi'] as $v) {
            $abi += $v;
        }
        $map['abi'] = $abi;
        $channel_id_array=$_POST['cid'];
        $cids = array_unique($channel_id_array);
		if (count($cids) > 0) 
		{
			$s = implode(',', $cids);
			$s = ",{$s},";
			$map['cid'] = $s;
		}

        $device_did_array=$_POST['did'];
        $dids = array_unique($device_did_array);
		if (count($dids) > 0) 
		{
			$d= implode(',', $dids);
			$d = ",{$d},";
			$map['device_did'] = $d;
		}

        $pro_city=array();
        $map['push_area'] = $_POST['area_value'];
		$map['paichu_area'] = $_POST['paichu_area_value'];
		
		//可以重复 2015-9-16
		//添加之前 验证一下当前时间段是否有推荐内容
		/*$where=array(
			'start_at' => array('elt',$map['end_at']),
			'end_at' => array('egt',$map['start_at']),
			'status' => 1,
			'id' => array('neq',$id),
		);
		$have_result=$model->where($where)->find();
		if($have_result)
		{
			$this->error("时间段与ID为【{$have_result['id']}】有冲突,请重新填写");
		}
		else
		{
			$log = $this->logcheck(array('id'=>$id),'sj_custom_push',$map,$model);
			$result=$model->where(array('id' =>$id))->save($map);
			$this->writelog("编辑了定制推送ID为{$id}的{$log}的内容");
		}*/
		if($push_type==6) {
			//编辑桌面红包时 处理红包绑定与解绑
			$bind_data = $info_list['bind_data'];
				if( !empty($bind_data) ) {
					if( $bind_data['bind_red_type'] == 1 ) {
						if( $bind_data['red_id'] ) {
							//解除绑定
							$bind_info = D('Sj.RedActivity')->bind_red_pagckage(1, $bind_data['push_id'], $bind_data['red_id'], $bind_data['red_id']);
							if( $bind_info['STATUS'] == 1) {
								$desk_status = array(
									'desktop' => 0, //是否是桌面红包 0，不是， 1，定制 2，push
								);
								$model->table("sj_red_packet_conf")->where(array('id'=>$bind_data['red_id']))->save($desk_status);
							}else {
								$this->error($bind_info['MSG']);
							}
						}
						//绑定红包
						$bind_info = D('Sj.RedActivity')->bind_red_pagckage(1, $bind_data['push_id'], $bind_data['new_red_id'], '', '',$bind_data['note']);
						if( $bind_info['STATUS'] == 1) {
							$red_conf_find2 = $model->table("sj_red_packet_conf")->where(array('id'=>$bind_data['new_red_id']))->find();
							if($bind_data['table'] == 'sj_market_push') {
								$desk = 2;
							}elseif($bind_data['table'] == 'sj_custom_push') {
								$desk = 1;
							}else {
								$desk = 0;
							}
							$desk_status = array(
								'desktop' => $desk, //是否是桌面红包 0，不是， 1，定制 2，push
							);
							$model->table("sj_red_packet_conf")->where(array('id'=>$bind_data['new_red_id']))->save($desk_status);
						}else {
							$this->error($bind_info['MSG']);
						}
				}elseif( $bind_data['bind_red_type'] == 2 ) {
					if( $bind_data['red_id'] ) {
						//解除绑定
						$bind_info = D('Sj.RedActivity')->bind_red_pagckage(1, $bind_data['push_id'], $bind_data['red_id'], $bind_data['red_id']);
						if( $bind_info['STATUS'] == 1) {
							$desk_status = array(
								'desktop' => 0, //是否是桌面红包 0，不是， 1，定制 2，push
							);
							$model->table("sj_red_packet_conf")->where(array('id'=>$bind_data['red_id']))->save($desk_status);
						}else {
							$this->error($bind_info['MSG']);
						}
					}
					//绑定新的任务红包
					$bind_info = D('Sj.RedActivity')->bind_red_pagckage(1, $bind_data['push_id'], $bind_data['new_red_id'],'',$bind_data['task_id'],$bind_data['note']);
					if( $bind_info['STATUS'] == 1 ) {
						$red_conf_find2 = $model->table("sj_red_packet_conf")->where(array('id'=>$bind_data['new_red_id']))->find();
						if($bind_data['table'] == 'sj_market_push') {
							$desk = 2;
						}elseif($bind_data['table'] == 'sj_custom_push') {
							$desk = 1;
						}else {
							$desk = 0;
						}
						$desk_status = array(
							'desktop' => $desk, //是否是桌面红包 0，不是， 1，定制 2，push
						);
						$model->table("sj_red_packet_conf")->where(array('id'=>$bind_data['new_red_id']))->save($desk_status);
					}else {
						$this->error($bind_info['MSG']);
					}
				}
			}
		}
		
		$log = $this->logcheck(array('id'=>$id),'sj_custom_push',$map,$model);
		$result=$model->where(array('id' =>$id))->save($map);
		//待绑定红包
		$this->writelog("编辑了定制推送ID为{$id}的{$log}的内容",'sj_custom_push', $id,__ACTION__ ,"","edit");
			
		if($result)
		{
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME."/CustomPush/custom_push_list/p/{$_POST['p']}");
			$this->success("编辑推送成功！");
		}
        else
        {
            $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/CustomPush/custom_push_list');
            $this->error("编辑推送失败,发生错误！");
        }
    }

	function custom_push_list_silence(){
		$sj_custom_push=M("custom_push_silence");
		$util = D("Sj.Util"); 
		$channel=M("channel");
		import("@.ORG.Page");
		$where="status=1 ";
		if($_GET['zh_type']){
			if($_GET['zh_type']==1){//推送中
				$where .= " and start_at <= ".time()." and end_at >= ".time()."";
				$this->assign("zh_type",$_GET['zh_type']);
			}elseif($_GET['zh_type']==2){//未推送
				$where .= " and start_at > ".time()."";
				$this->assign("zh_type",$_GET['zh_type']);
			}elseif($_GET['zh_type']==3){//已推送
				$where .= " and end_at < ".time()."";
				$this->assign("zh_type",$_GET['zh_type']);
			}
		}
		        //////////////////////// 应该处理的GET参数
        // 添加平台默认为市场
        $pid = 1;//默认为1
        if ($_GET['pid']) {
            $pid = $_GET['pid'];
        }
        $this->assign('pid', $pid);
        $product_list = $util->getProducts($pid);
        $where .= " and pid={$pid}";
        $this->assign('product_list',$product_list);

//		if(!empty($_GET['soft_name'])){
//			$where.=" and silence_softname like '%{$_GET['soft_name']}%'";
//			$this->assign("so_soft_name",$_GET['soft_name']);
//		}

		if(!empty($_GET['package'])){
			$where.=" and package like '%{$_GET['package']}%'";
			$this->assign("s_package",$_GET['package']);

		}
		if(!empty($_GET['silence_package'])){
			$where.=" and silence_package ='{$_GET['silence_package']}'";
			$this->assign("so_package",$_GET['silence_package']);

		}
		if(!empty($_GET['fromdate'])&& !empty($_GET['todate']))
		{
			$where.=" and start_at <=".strtotime(date('Y-m-d H:i:s',strtotime($_GET['todate'])))." and end_at>=".strtotime(date('Y-m-d H:i:s',strtotime($_GET['fromdate'])));
			$this->assign("so_start_tm",$_GET['fromdate']);
			$this->assign("so_end_tm",$_GET['todate']);
		}
		
		$count = $sj_custom_push->where($where)->count();
		$param = http_build_query($_GET);
		$Page = new Page($count, 10, $param);
		$custom_push_list=$sj_custom_push->where($where)->order("id desc")->limit($Page->firstRow . ',' . $Page->listRows)->select();
		
		foreach ($custom_push_list as $k => $v)
		{
			$cid_str = preg_replace('/^,/','',$v['cid']);
			$cid_str = preg_replace('/,$/','',$cid_str);
			$array = explode(',', $cid_str);
			$cname = $channel->where("cid in ({$cid_str})")->findAll();
			if (in_array("0",$array))
			{
				$custom_push_list[$k]['chname'] .= "<p>通用</p>";
			}
			foreach ($cname as $k1 => $v1)
			{
				if($custom_push_list[$k]['chname']=="")
				{
					if($k1==0)
					{
						if(mb_strlen($v1['chname'],'utf-8')>10)
						{
							$short_chname=mb_substr($v1['chname'],0,10,'utf-8');
							$custom_push_list[$k]['chname'].="<p>{$short_chname}</p>";
						}
						else
						{
							$custom_push_list[$k]['chname'] .= "<p>{$v1['chname']}</p>";
						}
					}
					if($k1>=1)
					{
						$short=mb_substr($v1['chname'],0,6,'utf-8');
						$custom_push_list[$k]['chname'] .= "<p>{$short}...</p>";
						break;
					}
				}
				else
				{
					if($k1>=0)
					{
						$short=mb_substr($v1['chname'],0,6,'utf-8');
						$custom_push_list[$k]['chname'] .= "<p>{$short}...</p>";
						break;
					}
				}
			}



			//推送状态
			if($v['start_at']>time())
			{
				$custom_push_list[$k]['push_status']="未推送";
			}
			elseif($v['end_at']<time())
			{
				$custom_push_list[$k]['push_status']="已推送";
			}
			else
			{
				$custom_push_list[$k]['push_status']="推送中";
			}
			//V6.4root用户配置
			//非root用户
			if($v['no_root']==1)
			{
				$custom_push_list[$k]['no_roots']="不下载";
			}
			else if($v['no_root']==2)
			{
				$custom_push_list[$k]['no_roots']="下载";
			}
			//root用户
			if($v['root']==1)
			{
				$custom_push_list[$k]['roots']="下载";
			}
			else if($v['root']==2)
			{
				$custom_push_list[$k]['roots']="安装";
			}
			//非root用户下载方式
			if($v['no_root_down']==1)
			{
				$custom_push_list[$k]['no_root_downs']="静默下载";
			}
			else if($v['no_root_down']==2)
			{
				$custom_push_list[$k]['no_root_downs']="普通下载";
			}
		}
		if ($_GET['p'])
			$this->assign('p', $_GET['p']);
		else
			$this->assign('p', '1');
		$show = $Page->show();
		$this->assign("page", $show);
		$this->assign("push_list",$custom_push_list);
		$this->display();
	}
	
	function pub_custom_push_silence_ajax_info()
	{
		$id				=	$_GET['id'];
		$package		=	!empty($_GET['package']) ? trim($_GET['package']) : '';
		//$silence_packag	=	!empty($_GET['sp']) ? trim($_GET['sp']) : '';
		$start_at		=	!empty($_GET['start_at']) ? strtotime($_GET['start_at']) : '';
		$end_at			=	!empty($_GET['end_at']) ? strtotime($_GET['end_at']) : '';
		
		if( !$package ) {
			exit(json_encode(array('code'=>0)));
		}elseif($start_at > $end_at) {
			exit(json_encode(array('code'=>0)));
		}
		
		$sj_custom_push = M("custom_push_silence");
		$pkArr = explode(',', $package);
		//$spArr = explode(',', $silence_packag);
		$data	=	array();
		$i		=	0;
		
		foreach ($pkArr as $k => $v  ) {
			$sql  = "select id,package,silence_package,FROM_UNIXTIME(start_at,'%Y-%m-%d %H:%i:%s') as start_at,FROM_UNIXTIME(end_at,'%Y-%m-%d %H:%i:%s') as end_at from sj_custom_push_silence where ";
			$sql .= " package like '%{$v}%' ";
			if( $id ) {
				$sql .= " and id !={$id} ";
			}
			$sql .= " and  ( ({$end_at}<=end_at and {$end_at}>=start_at) or ({$start_at}<=end_at and {$start_at}>=start_at)  or (start_at<{$start_at} and end_at>{$end_at}) or ({$start_at} <= start_at and {$end_at} >= end_at ) )  ";
			$sj_custom_push_list = $sj_custom_push->query($sql);
			
			if( !empty($sj_custom_push_list) ) {
				foreach ($sj_custom_push_list as $key => $val ) {
					$data[$i]['package']			=	$v;
					$data[$i]['silence_package']	=	$val['silence_package'];
					$data[$i]['start_at']			=	$val['start_at'];
					$data[$i]['end_at']				=	$val['end_at'];
					$i++;
				}
			}
		}
		
		if( empty($data) ) {
			exit(json_encode(array('code'=>0)));
		}else {
			exit(json_encode(array('code'=>1, 'data'=>$data)));
		}
		
	}
	
	function custom_push_silence_save()
	{
		$feature_db=M('feature');
		$sj_custom_push=M("custom_push_silence");
		if($_POST){
			$this->custom_push_silence_save_do($_POST);
		}
		if($_GET['id']){
			$where=array(
					'id' => $_GET['id'],
			);
			$sj_custom_push_one=$sj_custom_push->where($where)->find();

			$sj_custom_push_one['start_tms'] = date('Y-m-d H:i:s',$sj_custom_push_one['start_at']);
			$sj_custom_push_one['end_tms'] = date('Y-m-d H:i:s',$sj_custom_push_one['end_at']);
			$this -> assign("p",$_GET['p']);
			$this->assign("custom_list",$sj_custom_push_one);
			$this->assign('pid',$sj_custom_push_one['pid']);
		}else{
			$_GET['pid'] = $_GET['pid']? $_GET['pid'] : 1;
			$this->assign('pid',$_GET['pid']);
		}
		$this->display();
	}

	function custom_push_silence_del()
	{
		$sj_custom_push=M("custom_push_silence");
		$id=$_GET['id'];
		$where = array(
				'id' => $id,
				'status' => 1,
		);
		$map = array('status' => 0, 'update_at' => time());
		$ret = $sj_custom_push->where($where)->save($map);
		if ($ret)
		{
			$this->writelog("静默下载安装：删除了id为{$id}的内容",'sj_custom_push_silence', $id,__ACTION__ ,"","del");
			$this->success("删除成功");
		}
		else
		{
			$this->error("删除失败");
		}
	}

	function custom_push_silence_save_do($data){
		$sj_custom_push=M("custom_push_silence");
		$monitor_soft_package=trim($data['package']);
		$silence_package = trim($data['silence_package']);
//		$silence_softname=trim($data['silence_softname']);
		$push_type = trim($data['push_type']);
		//添加平台默认为市场
		$map['pid'] = $data['pid']?$data['pid']:1;
		if(!$monitor_soft_package)
		{
			$this -> error("监控包名为必填项");
		}
		else
		{
			$map['package']=$monitor_soft_package;
		}
		//开始时间和结束时间
		if($data['fromdate'] && $data['todate'])
		{
			$map['start_at']=strtotime($data['fromdate']);
			$map['end_at']=strtotime($data['todate']);
			if($map['start_at'] > $map['end_at'])
			{
				$this -> error("开始时间不能大于结束时间");
			}
		}
		else
		{
			$this -> error("请选择开始时间和结束时间");
		}
		if(!$silence_package)
		{
			$this -> error("静默包名为必填项");
		}
		else
		{
			$map['silence_package']=$silence_package;
			$silence_packages = explode(',',$silence_package);
			$shield_error="";
			for($index=0;$index<count($silence_packages);$index++)
			{
				//屏蔽软件上排期时报警需求 新增  yuesai
				$AdSearch = D("Sj.AdSearch");
				$shield_error.=$AdSearch->check_shield($silence_packages[$index],$map['start_at'],$map['end_at']);
			} 
			if($shield_error){
				$this -> error($shield_error);
			}
		}
//		if(!$silence_softname)
//		{
//			$this -> error("静默软件名为必填项");
//		}
//		else
//		{
//			$map['silence_softname']=$silence_softname;
//		}
		//V6.4添加root用户配置
		$map['no_root'] = trim($data['no_root']);
		$map['root'] = trim($data['root']);
		$map['no_root_down'] = trim($data['no_root_down']);

		$map['status']=1;
		//添加行为id
		$beid = trim($data['beid']);
		$map['beid'] = $beid;
		$map['update_at']=time();
		//上传csv
		$filename=$_FILES['upload_file']['name'];
		if(!$filename&&!trim($data['csv_count'])&&trim($data['have_pre_dl']))
		{
			$map['csv_count'] = trim($data['pre_dl_count']);
			$map['csv_url'] = trim($data['have_pre_dl']);
			$map['is_upload_csv'] = 1;
		}
		if(!$filename&&!$data['csv_url']&&!trim($data['have_pre_dl']))
		{
			$map['csv_count'] = 0;
			$map['csv_url'] = "";
			$map['is_upload_csv'] = 0; //标注是否上传csv
		}

		if($filename&&!$data['csv_count'])
		{
			$this -> error("选择好的文件请点击上传才有效");
		}
		if($data['csv_count']&&$data['csv_url'])
		{
			$map['csv_count'] = $data['csv_count'];
			$map['csv_url'] = $data['csv_url'];
			$map['is_upload_csv'] = 1;
		}
		unset($_FILES['upload_file']);
		$map['oid'] = ','. implode(',', $data['oid']). ',';
		$map['firmware'] = ','. implode(',', $data['firmware']). ',';
		$map['version_code'] = ','. implode(',', $data['version_code']). ',';
		$abi = 0;
		foreach($data['abi'] as $v) {
			$abi += $v;
		}
		$map['abi'] = $abi;
		$channel_id_array=$data['cid'];
		$cids = array_unique($channel_id_array);
		if (count($cids) > 0) {
			$s = implode(',', $cids);
			$s = ",{$s},";
			$map['cid'] = $s;
		}

		$device_did_array=$data['did'];
		$dids = array_unique($device_did_array);
		if (count($dids) > 0)
		{
			$d= implode(',', $dids);
			$d = ",{$d},";
			$map['device_did'] = $d;
		}

		$pro_city=array();
		$map['push_area'] = $data['area_value'];
		//增加排除区域
		$map['paichu_area'] = $data['paichu_area_value'];
		$this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/CustomPush/custom_push_list_silence');
		if($data['id']){
			$log = $this->logcheck(array('id'=>$data['id']),'sj_custom_push_silence',$map,$sj_custom_push);
			$res = $sj_custom_push->where(array('id' =>$data['id']))->save($map);
			if($res){
				$this->writelog("编辑了静默下载安装ID为{$data['id']}的{$log}的内容",'sj_custom_push_silence',$data['id'],__ACTION__ ,"","edit");
				$this->success("编辑静默下载安装成功！");
			}else{
				$this->error("编辑静默下载安装失败！");
			}
		}else {
			$map['create_at']=time();
			$res = $sj_custom_push->add($map);
			if ($res) {
				$this->writelog('增加了名称ID为[' . $res . ']包名为[' . $map['package'] . ']的静默下载安装', 'sj_custom_push_silence', $res,__ACTION__ ,"","add");
				$this->success("添加静默下载安装成功！");
			} else {

				$this->error("添加静默下载安装失败！");
			}
		}
	}
	//文本编辑器内容处理
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
}

?>
