<?php
/**
 * 安智网产品管理平台 公用控制器
 * ============================================================================
 * 版权所有 2009-2011 北京力天无限网络有限公司，并保留所有权利。
 * 网站地址: http://www.goapk.com；
 * by:金山 2010.4.21
 * ----------------------------------------------------------------------------
*/
class CommonAction extends Action {
    private $actionname;    //控制器名称
    private $action_id;     //控制器id
    private $map;           //条件
    private $admin_node_db; //用户节点权限对应表
    private $admin_log_db;  //用户日志表
    private $sessionid;     //sessionid
    private $adminusers;    //用户表明
	private $new_nodemap = array();

	public function showArr($arr)
	{
		header("charset=utf-8");
		echo "<pre>";
		print_r($arr);
		echo "</pre>";
		exit();
	}
	public function _initialize() {

            // 用户权限检查
        $this->actionname=__ACTION__;

        //dump($this->actionname);
        //dump($_SESSION);

        /*if(!isset($_SESSION['admin']['admin_id'])) {
            session_destroy();
            $this->redirect('Public/login','' , 1,'对不起,登陆超时,页面跳转中~');
        }*/
		
		if (preg_match('/^pub_[a-zA-Z0-9\_]+$/', ACTION_NAME)) {
			return true;
		}

        if(empty($_SESSION['admin']['admin_id'])) {
            if(!checkCookieAdmin()){
				session_destroy();
            	$this->redirect('Public/login','' , 1,'对不起,登陆超时,页面跳转中~');
            }
        }
        $actionname = ACTION_NAME;
        $popedom_name = __ACTION__;
        $popedom_name = str_replace('Debug', 'Sendnum', $popedom_name);
        $non_auth_actions = array(
            'get_softname' => 1,
        );

/*
        if(!isset($non_auth_actions[$actionname]) && !preg_match('/^pub_/', $actionname) && !in_array($popedom_name,$_SESSION['admin']['popedom'])) { 
			$rs = strpos(serialize($_GET),'random_isthickbox');
			if($rs>0){
                unset($_SERVER['HTTP_X_REQUESTED_WITH']);
                $this->assign( 'isbox',1);
            }else{
                $this->assign( 'isbox',0);
                $this->assign( 'jumpUrl',SITE_URL.'/index.php/Public/main/');
            }
            $this->error ('对不起,权限不足!');
        } */
        $this->adminusers = M('admin_users');
        $this->sessionid=$this->adminusers->where("admin_user_id='".$_SESSION['admin']['admin_id']."'")->getField('sessionid');
        $allow_multi_session = C('allow_multi_session');
        if(!$allow_multi_session && $this->sessionid!=session_id()) {
           session_destroy();
            $this->redirect('Public/login','' , 1,'对不起,您的账号已被他人登陆或被停用,请重新登陆,页面跳转中~');
        }
		if(!empty($_SESSION['admin']['type_30']) && ($_SESSION['admin']['type_30']== 1)){
			$this->redirect('Public/password/type_30/1','' , 1,'超过30天没有修改密码，请修改密码,页面跳转中~');
		}
		//登录管理员session中loginip和当前ip比对
		if(isset($_SESSION['admin']['admin_id'])) {
            $relogin = false;
            $white_ip_list = array(
                '218.241.82.226',
                '114.249.*',
                '221.221.*',
            );
            if ($_SESSION['admin']['loginip']!=$_SERVER['REMOTE_ADDR']) {
                $relogin = true;
                foreach($white_ip_list as $key=>$val) {
                    if(strpos($val,'*')===FALSE) {
                        if($val==$_SERVER['REMOTE_ADDR']) {
                            $relogin = false;
                            break;
                        }
                    } else {
                        $val = str_replace('*','[0-9]{1,3}',$val);
                        $val = str_replace('.','\.',$val);
                        if(preg_match("/{$val}/",$_SERVER['REMOTE_ADDR'])) {
                            $relogin = false;
                            break;
                        }
                    }
                }   
            }
            if ($_SESSION['admin']['ua']!=$_SERVER['HTTP_USER_AGENT']) {
                $relogin = true;
            }
            
            if ($_SESSION['admin']['admin_id'] == 26) {
                $relogin = false;
            }
            
            if( $relogin ) {
                $map = array();
                $map['admin_id'] = $_SESSION['admin']['admin_id'];
                $map['action_id'] = 10000;
                $map['actionexp'] = "用户名：{$_SESSION['admin']['admin_user_name']}，登录IP：{$_SESSION['admin']['loginip']}，当前IP：{$_SERVER['REMOTE_ADDR']}，访问页面：{$_SERVER['REQUEST_URI']} 登陆UA {$_SESSION['admin']['ua']} 当前UA {$_SERVER['HTTP_USER_AGENT']}";			
				$map['logtime'] = time();
				$map['fromip'] = $_SERVER['REMOTE_ADDR'];
				$map['log_key'] = $map['action_id'];
				$admin_log_db=M('admin_log');
				$admin_log_db->add($map);

				session_unset();
				session_destroy();
                $this->redirect('Public/login','' , 1,'对不起,登陆超时,页面跳转中~');
			}
		}
    }


    //日志
    protected function writelog($actionexp, $category = '', $value= '', $acname=__ACTION__ , $log_key="",$type='', $admin_id = ''){
        if(empty($admin_id)){
            $admin_id = $_SESSION['admin']['admin_id'];
        }
        
        
		//日志配置
		$action_array=array(
			'11'=>array(
				'edit'=>"11_1",
				'update'=>"11_2",
				'new'=>"11_3",
			),
			'12'=>array(
				'edit'=>"12_1",
				'update'=>"12_2",
				'new'=>"12_3",
			),
			'117'=>array(
				'0'=>"117_1",
				'1'=>"117_2",
			),
			'soft_config'=>array(
				'18'=>"zh_999",
				'722'=>"zh_999",
			),
			'feature_config'=>array(
				'116'=>"zh_888",
				'522'=>"zh_888",
			),
			'rank_config'=>array(
				'666'=>"zh_777",
				'774'=>"zh_777",
				'775' => 'zh_777'
			)
		);
        if(!empty($actionexp)) {
            $this->admin_node_db=M('admin_node');
            $this->action_id=$this->admin_node_db->where('nodename="'.$acname.'"')->getField('node_id');
            //dump($this->action_id);
            //$this->display('Public:header');
            $this->map='';
            $this->map['logtime']=time();
            $this->map['fromip']=get_client_ip();
            $this->map['actionexp']=$actionexp;
            $this->map['action_id']=$this->action_id;
            $this->map['admin_id']=$admin_id;
            $this->map['category']=$category;
            $this->map['value']=$value;
			$this->map['type'] = $type;
			if(isset($action_array[$this->action_id])){
				if($action_array[$this->action_id][$log_key]){
					$this->map['log_key']=$action_array[$this->action_id][$log_key];
				}else{
					if(isset($action_array[$log_key][$this->action_id])){
						$this->map['log_key']=$action_array[$log_key][$this->action_id];
					}else{
						$this->map['log_key']=$this->action_id;
					}
				}
			}else{
				if(isset($action_array[$log_key][$this->action_id])){
					$this->map['log_key']=$action_array[$log_key][$this->action_id];
				}else{
					$this->map['log_key']=$this->action_id;
				}
			}
            

           // var_dump(get_class($this));exit;
            if (strpos(get_class($this), '_pAction')) {
                $this->admin_log_db=M('admin_log_p');
            } else {
                $this->admin_log_db=M('admin_log');
            }

            
            


            //dump($this->map);
            $this->admin_log_db->add($this->map);
			if (empty($this->new_nodemap)) {
				$log_nodemap=M("admin_log_nodemap");
				$res = $log_nodemap->select();
				foreach ($res as $val) {
					$this->new_nodemap[$val['action_id']] = 1;
				}
			}
                    if($acname!='/index.php/Caiji/Collection/add_more_standby'&&$acname!='/index.php/Caiji/Collection/edit_standby'&&$acname!='/index.php/Caiji/Collection/update_status'&&$acname!='/index.php/Caiji/Collection/deletestandby')
                    {
			if (strstr($acname, 'index.php/Dev') || strstr($acname, 'index.php/Caiji/Collection')|| isset($this->new_nodemap[$this->action_id])) {
				$new_db = M('admin_log_new');
				$this->map['extra'] = $log_key;
				unset($this->map['type']);
				unset($this->map['log_key']);
				$new_db->add($this->map);
			}
                    }
            //dump($S);$this->display('Public:header');
        }
    }

    //过滤
    protected function trimmessage($msg) {
	    $msg = str_replace("  ","&nbsp;&nbsp;",$msg);
        include("notallowword.php");     //屏蔽功能
				$msg = eregi_replace($notallowword,"***",$msg);
	    return $msg;
    }
    //防刷新
    protected function reload() {
		if(isset($_POST['__hash__']) && $_POST['__hash__'] != "")
		{
			if($_POST['__hash__'] != $_SESSION['__hash__']) {
				$this->assign('jumpUrl','javascript:history.go(-2);');
				$this->success('请不要刷新本页面或重复提交表单!');
			}
			$_SESSION['__hash__']=md5(rand(1,1000));
		}
    }

   //创建目录
   protected   function mkDirs($path){
            $adir = explode('/',$path);
            $dirlist = '';
            $rootdir = array_shift($adir);
            if(($rootdir!='.'||$rootdir!='..')&&!file_exists($rootdir)){
                @mkdir($rootdir);
            }
            foreach($adir as $key=>$val){
                $dirlist .= "/".$val;
                $dirpath = $rootdir.$dirlist;
                if(!file_exists($dirpath)){
                @mkdir($dirpath);
                @chmod($dirpath,0777);
                }
            }
    }

	/**
	  * @desc   设置排序位置
	  * @param  $table     实际表名称
	  * @param  $field     设置字段名称
	  * @param  $target_id 目标ID
	  * @param  $where     WHERE条件
	  * @param  $target_rank目标排序
	  * @param  $lr        每页记录数
	  * @param  $p         当前页码
	  * @return 若有分页返回array 否则返回false 跳转页面参数
	  */
	protected function _updateRankInfo($table,$field,$target_id,$where,$target_rank,$lr,$p,$model){
		if($model==''){
			$model = new RankModel($table);
		}
		$m     = (int)$target_rank;
		$id    = (int)$target_id;
		$pk    = $model -> getPK();

		$result= $model ->table($table)-> where($where) ->  field("{$pk}, {$field}") -> order("{$field} asc") -> select();
		$rank_db_list = array();
		$rank_list = array();

		$i = 1;
		foreach ($result as $row) {
			$rank_list[$i] = array($row[$pk], $i);
			$rank_db_list[$row[$pk]] = array($row[$field], $i);
			$i++;
		}
		$old_pos = $rank_db_list[$id][1];
		$new_pos = $m;

		//$m新值, $n旧值(直接取修正后的值，不需要通过get方法传递)
		$rank_list[$old_pos][1] = $new_pos;

		if($old_pos > $new_pos){//上升
			for ($j = $new_pos; $j < $old_pos; $j++) {
				$rank_list[$j][1] += 1;
			}
		} elseif($old_pos < $new_pos) { //下降
			for ($j = $new_pos; $old_pos < $j; $j--) {
				$rank_list[$j][1] -= 1;
			}
		}

		$update = array();
		foreach ($rank_list as $k => $v) {
			if ($v[1] != $rank_db_list[$v[0]][0]) {
				$update[$v[0]] = $v[1];
				$w = array(
					$pk => $v[0]
				);
				$d = array(
					$field => $v[1]
				);
				$model ->table($table)-> where($w) -> save($d);
			}
		}

		if(is_int($lr) && $lr > 0){
			//解析分页
			$yu  = $new_pos % $lr;
			if($yu != 0 && $new_pos > $lr){
				$page = floor($new_pos/$lr) + 1;
			}else{
				$page = floor($new_pos/$lr);
			}
			return array('p'=>$page,'lr'=>$lr);
		}else{
		    return true;
		}
	}

    //文件上传
    protected function _uploadapk($type=false, $config = array())
    {
        import("@.ORG.UploadFile");
        $upload = new UploadFile();

        //设置上传文件大小
    //    $upload->maxSize  = 1024*1024*256;
        //设置上传文件类型
        $upload->allowExts  = explode(',','jpg,gif,png,jpeg,apk,bmp,csv,apatch');
        //设置附件上传目录
        //设置附件上传目录
        if (isset($config['savePath'])) {
        	$upload->savePath = $config['savePath'];
        } else {
	        $yearmonth = date("Ym");
	        $day=date('d');
	        $thepath = UPLOAD_PATH.'/'.$yearmonth.'/'.$day.'/'.'';
	        $upload->savePath = $thepath;
        }
        if (isset($config['multi_config'])) {
        	$upload->multi_config = $config['multi_config'];
        }
        if (isset($config['saveRule'])) {
        	$upload->saveRule = $config['saveRule'];
        }

        //设置需要生成缩略图，仅对图像文件有效
        /**
         * 不使用think生成缩略图
         */
        if($type) {
            $upload->thumb =  false;
            //是否加入水印
            $upload->watertype=false; // 废弃tinkphp 的打水印

            $upload->thumbPrefix   =  'N_';
            $upload->thumbMaxWidth =  '320';
            $upload->thumbMaxHeight = '480';
            $upload->thumbRemoveOrigin = false;

        } elseif($type===0) {
			$upload->thumb =  false;
			//是否加入水印
			$upload->watertype=false;
			$upload->thumbPrefix   =  '';
		}


        if(!$upload->upload()) {
        //捕获上传异常
          $this->error($upload->getErrorMsg());

        }else {
        //取得成功上传的文件信息
        	$path = date("Ym/d/");
            $uploadList = $upload->getUploadFileInfo();
            for($i=0;$i<count($uploadList);$i++) {
                if($uploadList[$i]['extension']=='apk') {
                    $apklist[]=$uploadList[$i];
                }elseif($uploadList[$i]['extension']=='jpg' || $uploadList[$i]['extension']=='gif' ||$uploadList[$i]['extension']=='png' ||$uploadList[$i]['extension']=='jpeg' || $uploadList[$i]['extension']=='bmp') {
                    $imglist[]=$uploadList[$i];
                }else{
					$otherlist[] = $uploadList[$i];
				}
            }
            include_once SERVER_ROOT. '/tools/functions.php';
			$otherinfo = array();
			foreach($otherlist as $k => $v){
				$tmp = array();
				$otherpath = $v['savepath']. $v['savename'];
				$tmp['url'] = $otherpath;
				$tmp['size'] = $v['size'];
				$tmp['dburl'] = str_replace(UPLOAD_PATH, '', $otherpath);
				$otherinfo[] = $tmp;
			}
            $apkinfo = array();
            foreach($apklist as $k => $v) {
            	$tmp = array();
            	$apkpath = $apklist[$k]['savepath']. $apklist[$k]['savename'];
                if (!is_file($apkpath)) {
                    echo "APK(${apkpath})上传失败。";
                    exit;
                }
            	$tmp = get_apk_info($apkpath);
            	if($tmp == false) {
            		echo '出现了个小错误···请您重试';
            		exit;
            	}
            	$tmp['abi'] = get_apk_abi($apkpath);
                if (isset($config['name_optimization']) && $config['name_optimization'] == 1) {
                    // 如果名字要优化，则调用get_apk_info函数后得到packagename+时间戳+随机数的方式重命名
                    $old_name = $apklist[$k]['savepath'] . $apklist[$k]['savename'];
                    $tmp_save_name = $tmp['packagename'] . "_" . time(). "_" . rand(10000000,99999999) . ".{$apklist[$k]['extension']}";
                    $new_name = $apklist[$k]['savepath'] . $tmp_save_name;
                    if (rename($old_name, $new_name))
                        $apklist[$k]['savename'] = $tmp_save_name;
                }
            	list($msec, $sec) = explode(' ', microtime());
            	$msec = substr($msec, 2);
            	$package_name = $tmp['packagename'];
            	$iconname = $package_name . '_'. $msec. '_'. $apklist[$k]['key']. substr($tmp['icon'], strrpos($tmp['icon'], '.'));
				$iconname72 = $package_name . '_'. $msec. '_'. $apklist[$k]['key'].'_72'.substr($tmp['icon_72'], strrpos($tmp['icon_72'], '.'));
				$iconname96 = $package_name . '_'. $msec. '_'. $apklist[$k]['key'].'_96'.substr($tmp['icon_96'], strrpos($tmp['icon_96'], '.'));
				$iconname125 = $package_name . '_'. $msec. '_'. $apklist[$k]['key'].'_125'.substr($tmp['icon_125'], strrpos($tmp['icon_125'], '.'));
            	$iconpath = UPLOAD_PATH. '/icon/'. $path;
            	$upload->__mkdir($iconpath);

            	$iconurl = $iconpath. $iconname;
				$iconurl72 = $iconpath. $iconname72;
				$iconurl96 = $iconpath. $iconname96;
				$iconurl125 = $iconpath. $iconname125;
            	$apk_icon_url = tempnam($iconpath, $package_name. "_");
            	$apk_icon_url .= substr($tmp['icon'], strrpos($tmp['icon'], '.'));
            	$apk_icon_url_db = str_replace(UPLOAD_PATH, '', $apk_icon_url);
				$apk_icon_url_db72 = str_replace('.png', '_72.png', $apk_icon_url_db);
				$apk_icon_url_db96 = str_replace('.png', '_96.png', $apk_icon_url_db);
				$apk_icon_url_db125 = str_replace('.png', '_125.png', $apk_icon_url_db);

            	$iconurl_db = str_replace(UPLOAD_PATH, '', $iconurl);
				$iconurl_db_72 = str_replace(UPLOAD_PATH, '', $iconurl72);
				$iconurl_db_96 = str_replace(UPLOAD_PATH, '', $iconurl96);
				$iconurl_db_125 = str_replace(UPLOAD_PATH, '', $iconurl125);
            	$apkurl_db = str_replace(UPLOAD_PATH, '', $apklist[$k]['savepath']). $apklist[$k]['savename'];


            	copy($tmp['icon'], $iconurl);
				copy($tmp['icon_72'], $iconurl72);
				copy($tmp['icon_96'],$iconurl96);
				copy($tmp['icon_125'],$iconurl125);

				// go_make_links($iconurl);
				// go_make_links($iconurl72);
				// go_make_links($iconurl96);
				// go_make_links($iconurl125);
            	# original icon
            	copy($tmp['icon_original'], $apk_icon_url);
            	// go_make_links($apk_icon_url);
                if (!is_file($iconurl)) {
                    echo "图标(${tmp['icon']} => ${iconurl})复制失败(1)。";
                    exit;
                }
                if (!is_file($iconurl72)) {
                    echo "图标(${tmp['icon_72']} => ${iconurl_72})复制失败(2)。";
                    exit;
                }
				if (!is_file($iconurl96)) {
                    echo "图标(${tmp['icon_96']} => ${iconurl_96})复制失败(2)。";
                    exit;
                }
				if (!is_file($iconurl125)) {
                    echo "图标(${tmp['icon_125']} => ${iconurl_125})复制失败(2)。";
                    exit;
                }

            	unlink($tmp['icon']);
				unlink($tmp['icon_72']);
				unlink($tmp['icon_96']);
				unlink($tmp['icon_125']);
            	unlink($tmp['icon_original']);

            	$tmp['icon'] = $iconurl;
				$tmp['icon_72'] = $iconurl72;
				$tmp['icon_96'] = $iconurl96;
				$tmp['icon_125'] = $iconurl125;
            	$tmp['size'] = $apklist[$k]['size'];
            	# XXX: what's this
            	$tmp['savepath'] = $apklist[$k]['savepath']. $apklist[$$k]['savename'];

				$tmp['iconurl_db'] = $iconurl_db;
				$tmp['iconurl_db_72'] = $iconurl_db_72;
				$tmp['iconurl_db_96'] = $iconurl_db_96;
				$tmp['iconurl_db_125'] = $iconurl_db_125;

            	$tmp['apkurl_db'] = $apkurl_db;
            	$tmp['key'] = $apklist[$k]['key'];
            	$tmp['apk_icon_db'] = str_replace(realpath(UPLOAD_PATH),'',$apk_icon_url_db);

            	// $apkurl = $apklist[$k]['savepath']. $apklist[$k]['savename'];
            	// $apkouturl = $apklist[$k]['savepath'];
            	// splitfile($apkurl, $apkouturl);
            	// go_make_links($apkurl);
            	$apkinfo[] = $tmp;
            }

            $imginfo = array();
			$flip = false;
			if (isset($config['flip']) && $config['flip'] == true) $flip = true;
			if (empty($package_name)) {
				$package_name = $GLOBALS['upload_package_name'];
			}
			if (empty($package_name)) {
				$package_name = 'image';
			}
            foreach($imglist as $k => $v) {
            	$tmp = array();
            	$url = str_replace(UPLOAD_PATH, '', $v['savepath']). $v['savename'];
            	$abspath = UPLOAD_PATH. $url;
            	$name = substr($abspath, 0, strrpos($abspath, '.'));

				$v['savepath_ori'] = ($v['savepath_ori']=="")?$v['savepath']:$v['savepath_ori'];
				$url_ori=str_replace(UPLOAD_PATH, '', $v['savepath_ori']). $v['savename'];
				$abspath_ori = UPLOAD_PATH. $url_ori;
            	$name_ori = substr($abspath_ori, 0, strrpos($abspath_ori, '.'));

            	$ext = substr($abspath, strrpos($abspath, '.'));

				if (isset($config['multi_config'][$v['post_name']]['img_p_size'])) {
					$resize = $config['multi_config'][$v['post_name']]['img_p_size'];
				} elseif (isset($config['img_p_size'])) {
					$resize = $config['img_p_size'];
				} else {
					$resize = 61440;
				}

				if (isset($config['multi_config'][$v['post_name']]['img_p_width'])) {
					$width = $config['multi_config'][$v['post_name']]['img_p_width'];
				} elseif (isset($config['img_p_width'])) {
					$width = $config['img_p_width'];
				} else {
					$width = 0;
				}

				if (isset($config['multi_config'][$v['post_name']]['img_p_height'])) {
					$height = $config['multi_config'][$v['post_name']]['img_p_height'];
				} elseif (isset($config['img_p_height'])) {
					$height = $config['img_p_height'];
				} else {
					$height = 0;
				}
				
				
				$has_thumb = false;
				if (isset($config['multi_config'][$v['post_name']]['img_s_size'])) {
					$thumb_resize = $config['multi_config'][$v['post_name']]['img_s_size'];
					$has_thumb = true;
				} elseif (isset($config['img_s_size'])) {
					$thumb_resize = $config['img_s_size'];
					$has_thumb = true;
				} else {
					$thumb_resize = 61440;
				} 

				if (isset($config['multi_config'][$v['post_name']]['img_s_width'])) {
					$thumb_width = $config['multi_config'][$v['post_name']]['img_s_width'];
					$has_thumb = true;
				} elseif (isset($config['img_s_width'])) {
					$thumb_width = $config['img_s_width'];
					$has_thumb = true;
				} else {
					$thumb_width = 0;
				}

				if (isset($config['multi_config'][$v['post_name']]['img_s_height'])) {
					$thumb_height = $config['multi_config'][$v['post_name']]['img_s_height'];
					$has_thumb = true;
				} elseif (isset($config['img_s_height'])) {
					$thumb_height = $config['img_s_height'];
					$has_thumb = true;
				} else {
					$thumb_height = 0;
				}

            	$orgpath = $name_ori . '_o' . $ext;
            	copy($abspath, $orgpath);
            	//go_make_links($orgpath);
				//图片翻转
				if($flip){
					image_size_flip($abspath,$abspath,$width,$height);
				}

				//打水印
				if($type){
				$waterpath =  SERVER_ROOT.'/config/logo.png';
				image_water_mark($abspath,$waterpath,$abspath);
				}

            	//$resize = ($package_name == 'image') ? (isset($config['img_p_size']) ? intval($config['img_p_size']) :  61440) : 51200;
				//旧的图片压缩处理方式
				//go_strip_snapshot($abspath, $abspath, $resize, $flip)
				$ext = isset($config['img_ext']) ? $config['img_ext'] : $ext;
				$destpath = $name.$ext;
					//echo $abspath ."</br>".$destpath."</br>".$resize."</br>".$width."</br>".$height;exit;
				$resizeable = true;

            	if (isset($config['multi_config'][$v['post_name']]['enable_resize']) && $config['multi_config'][$v['post_name']]['enable_resize'] == false) {
					$resizeable = false;
				}
				
				if ($has_thumb) {
					$reisizepath = $name . '_r' . $ext;
					copy($abspath, $reisizepath);
	            	if (!image_strip_size($abspath, $reisizepath, $thumb_resize,$width,$height)) {
	            		echo "无法压缩${abspath}至${resize}以下。";
	            		exit;
	            	}
					@unlink($reisizepath_tmp);
					go_make_links($reisizepath);
					$tmp['url_resize'] = str_replace(UPLOAD_PATH, '', $reisizepath);			
				}
				
				if ($resizeable) {
	            	if (!image_strip_size($abspath, $destpath, $resize,$width,$height)) {
	            		echo "无法压缩${abspath}至${resize}以下。";
	            		exit;
	            	}
            	}
            	$tmp['key'] = $v['key'];
            	$tmp['post_name'] = $v['post_name'];
            	$tmp['url_original'] = str_replace(UPLOAD_PATH, '', $orgpath);
				$tmp['url'] = str_replace(UPLOAD_PATH, '', $destpath);
            	$imginfo[] = $tmp;
            }
        }
        $lists['apk'] = $apkinfo;
        $lists['image'] = $imginfo;
        $lists['other'] = $otherinfo;

        return $lists;
    }

	//登录管理员在管理操作时的密码验证
	protected function login_pwd_chk($id = '_login_password') {
		$_login_pwd_chk = FALSE;
		$_login_password = $id ? $_POST[$id] : $_POST['_login_password'];
		if($_login_password) {
			$conf = D('Sj.Config');
			$hash = $conf->where("configname='hash' and status=1")->getField('configcontent');

			$map = array();
			$map['admin_user_id'] = $_SESSION['admin']['admin_id'];
			$User = M("admin_users");
			$tmp = $User->where($map)->field('admin_user_password')->find();
			if($tmp && $tmp['admin_user_password'] == md5($_login_password.$hash)) {
				$_login_pwd_chk = TRUE;
			}
		}
		return $_login_pwd_chk;
	}

	//通过管理员uid取得用户名
	protected function getNameById($uid) {
		$map = array();
		$map['admin_user_id'] = $uid;
		$User = M("admin_users");
		$tmp = $User->where($map)->field('admin_user_name')->find();

		return ($tmp ? $tmp['admin_user_name'] : '');
	}

	//字符串过滤
	protected function strFilter($val) {
		if(!is_string($val)) return $val;

		$val = htmlspecialchars_decode($val);
		$val = trim(htmlspecialchars($val));

		return $val;
	}

	//数据保留小数点
	protected function keep_point($val,$point){
		$vals = sprintf("%.".$point."f", trim($val));
		return $vals;
	}


	//gpc过滤
	protected function gpcFilter(&$arr=array()) {
		if($arr) {
			array_walk_recursive($arr,'CommonAction::_gpcFilter');
		} else {
			array_walk_recursive($_POST,'CommonAction::_gpcFilter');
			array_walk_recursive($_GET,'CommonAction::_gpcFilter');
			array_walk_recursive($_REQUEST,'CommonAction::_gpcFilter');
		}
	}

	static public function _gpcFilter(&$val) {
		$val = htmlspecialchars_decode(urldecode($val));
		$val = trim(htmlspecialchars($val));
	}
	//消息提醒入库
	protected function dev_remind_add($dev_id,$content,$type='down'){
		$model = new Model();
		if($dev_id && $content){
			$data['dev_id'] = $dev_id;
			$data['content'] = $content;
			$data['create_tm'] = time();
			$data['read_status'] = 0;
			$data['status'] = 1;
			$remindres = $model->table('dev_remind')->data($data)->add();
			if($remindres){
				return true;
			}else{
				$this->permanentlog("dev_remind.log","提醒消息ID为{$dev_id}内容为{$content}插入失败");
				return false;
			}
		}else{
			$this -> error("开发者ID和提醒内容不能为空");
		}
	}

	//渠道合作登录用户权限限制
	function cooperative_manager($admin_id){
		$co_model = D('Cooperative.Incomemanage');
		$where_manage['_string'] = "aid = {$admin_id} and charge_type = 2";
		$manage_result = $co_model -> table('t_manager_purview') -> where($where_manage) -> select();
		$admin_result = $co_model -> table('t_manager') -> where(array('aid' => $admin_id,'status' => 1)) -> select();
		if($admin_result && $admin_result[0]['status'] == 1){
			foreach($manage_result as $key => $val){
				$userid_arr_manage[] = $val['charge_value'];
			}
		}else{
			$userid_arr_manage = array();
		}
		return $userid_arr_manage;
	}


	//选择性排序
	function select_rank($old_rank,$new_rank,$table,$id){
		$model = new Model();
		if($old_rank > $new_rank){
			$where_need['_string'] = "rank >= {$new_rank} and rank < {$old_rank} and status = 1";
			$need_result = $model -> table($table) -> where($where_need) -> select();

			foreach($need_result as $key => $val){
				$update_where['id'] = $val['id'];
				$update_data = array(
					'rank' => $val['rank'] + 1
				);
				$update_result = $model -> table($table) -> where($update_where) -> save($update_data);
			}
		}elseif($old_rank < $new_rank){
			$where_need['_string'] = "rank <= {$new_rank} and rank > {$old_rank} and status = 1";
			$need_result = $model -> table($table) -> where($where_need) -> select();
			foreach($need_result as $key => $val){
				$update_where['id'] = $val['id'];
				$update_data = array(
					'rank' => $val['rank'] - 1
				);
				$update_result = $model -> table($table) -> where($update_where) -> save($update_data);
			}
		}
		$where['id'] = $id;
		$data = array(
			'rank' => $new_rank
		);
		$result = $model -> table($table) -> where($where) -> save($data);
		return $result;
	}

	//上移/下移排序
	function change_rank($action,$table,$id,$type){
		$model = new Model();
		if($action == 'up'){
			$my_result = $model -> table($table) -> where(array('id' => $id)) -> select();
			$need_where['rank'] = $my_result[0]['rank'] - 1;
			//$need_where['status'] = 1;
			if($table=="sj_game_simulator"){
				$need_where['status'] = array('NEQ', 0);
			}else{
				$need_where['status'] = 1;
			}
			if($type){
				$need_where['type'] = $type;
			}
			$need_data['rank'] = $my_result[0]['rank'];
			$need_result = $model -> table($table) -> where($need_where) -> save($need_data);
			$where['id'] = $id;
			$data['rank'] = $my_result[0]['rank'] - 1;
			$result = $model -> table($table) -> where($where) -> save($data);
		}elseif($action == 'down'){
			$my_result = $model -> table($table) -> where(array('id' => $id)) -> select();
			$need_where['rank'] = $my_result[0]['rank'] + 1;
			//$need_where['status'] = 1;
			if($table=="sj_game_simulator"){
				$need_where['status'] = array('NEQ', 0);
			}else{
				$need_where['status'] = 1;
			}
			if($type){
				$need_where['type'] = $type;
			}
			$need_data['rank'] = $my_result[0]['rank'];
			$need_result = $model -> table($table) -> where($need_where) -> save($need_data);
			$where['id'] = $id;
			$data['rank'] = $my_result[0]['rank'] + 1;
			$result = $model -> table($table) -> where($where) -> save($data);
		}
		return $result;
	}


	//置顶
	function top_repick($table,$id,$type){
		$model = new Model();
		$been_result = $model -> table($table) -> where(array('id' => $id)) -> select();
		if($been_result[0]['rank'] == 1){
			$this -> error('当前排序已为置顶');
		}else{
			if($type){
				$need_where['_string'] = "status = 1 and type = {$type} and rank < {$been_result[0]['rank']}";
			}else{
				$need_where['_string'] = "status = 1 and rank < {$been_result[0]['rank']}";
			}
			$need_result = $model -> table($table) -> where($need_where) -> select();
			foreach($need_result as $key => $val){
				$update_where['id'] = $val['id'];
				$update_data['rank'] = $val['rank'] + 1;
				$update_result = $model -> table($table) -> where($update_where) -> save($update_data);
			}
			$where['id'] = $id;
			$data['rank'] = 1;
			$result = $model -> table($table) -> where($where) -> save($data);
			return $result;
		}
	}

	/**
	 *
	 * 日志校验函数
	 * @param array $pk_value like array('xx'=>1,'xx'=>2), pk为表的主键，value为主键值的固定格式数组
	 * @param string $table
	 * @param array $new_columnvalue like array('user_name'=>'xxxxx','login_name'=>'xxxxx') 数组的key为表的字段，value为新值
	 */
	function logcheck($pk_value, $table, $new_columnvalue,&$model) {
		// $model = new Model();

	    $pk = $pk_value['pk'];
	    $id = $pk_value['value'];
	    $string = '';
	    $i = 0;
	    foreach ($pk_value as $key=>$value) {
	        if ($i>0){
	            $string .= ' and ';
			}
			$string .= " $key = '$value' ";
		   	$i++;
	    }

	    $old = $model -> table($table) ->where($string)->select();

	    if (empty($old)) return "get old null!";
	    $old = $old[0];

	    $re_column = array();

	    $sql = "SELECT  COLUMN_NAME, COLUMN_COMMENT
				FROM  `information_schema`.`COLUMNS`
				WHERE TABLE_SCHEMA='{$model -> getdbName()}' AND table_name = '{$table}';
	    ";
	    $column_comment_ary = $model->query($sql);

	    $column_comment = array();
	    foreach ($column_comment_ary as $v) {
			$v['COLUMN_COMMENT'] = str_replace('，',',',$v['COLUMN_COMMENT']);
			$level = explode(',',$v['COLUMN_COMMENT']);
			$cnt = count($level);
			$colume_status = array();
			if($cnt>1){
				$val = array();
				foreach($level as $info){
					$info = str_replace('：',':',$info);
					$val = explode(':',$info);
					if(count($val) > 1){
						$val[0] = str_replace(' ','',$val[0]);
						$colume_status[$val[0]] = $val[1];
					}
				}
			}

	        $column_comment[$v['COLUMN_NAME']] = $colume_status ? $colume_status : $v['COLUMN_COMMENT'];
	    }

			// if($_SESSION['admin']['admin_id'] == 40){
				// var_dump($column_comment,$colume_status,$new_columnvalue);exit;
			// }
	    foreach ($new_columnvalue as $column => $v) {
	        if ($old[$column] != $v) {
				if(is_array($column_comment[$column])){
					if(in_array($v,$column_comment[$column])){
						$re_column[$column] = "{$column} 由 [{$old[$column]} : {$column_comment[$column][$old[$column]]}] 改为  [{$v} : {$column_comment[$column][$v]}]";
					}else if(array_key_exists($v,$column_comment[$column])){
						$re_column[$column] = "{$column} 由 [{$old[$column]} : {$column_comment[$column][$old[$column]]}] 改为  [{$v} : {$column_comment[$column][$v]}]";
					}else{
						$re_column[$column] = "{$column}  由 [{$old[$column]}] 改为 [{$v}]";
					}

				}else{
					if(isset($column_comment[$column])){
						$column_comment[$column] = empty($column_comment[$column])?'字段'.$column:$column_comment[$column];
						$re_column[$column] = "{$column_comment[$column]}  由 [{$old[$column]}] 改为 [{$v}]";
					}
				}
	        }
	    }
	    return implode('<br/>',$re_column);
	}

    // 替代系统读csv函数，系统函数getcsv在较低版本中有bug
    function mygetcsv(& $handle, $length = null, $d = ',', $e = '"') {
        $d = preg_quote($d);
        $e = preg_quote($e);
        $_line = "";
        $eof = false;
        while ($eof != true){
            $_line .= (empty ($length) ? fgets($handle) : fgets($handle, $length));
            $itemcnt = preg_match_all('/' . $e . '/', $_line, $dummy);
            if ($itemcnt % 2 == 0)
            $eof = true;
        }
        $_csv_line = preg_replace('/(?: |[ ])?$/', $d, trim($_line));
        $_csv_pattern = '/(' . $e . '[^' . $e . ']*(?:' . $e . $e . '[^' . $e . ']*)*' . $e . '|[^' . $d . ']*)' . $d . '/';
        preg_match_all($_csv_pattern, $_csv_line, $_csv_matches);
        $_csv_data = $_csv_matches[1];
        for ($_csv_i = 0; $_csv_i < count($_csv_data); $_csv_i++){
            $_csv_data[$_csv_i] = preg_replace('/^' . $e . '(.*)' . $e . '$/s', '$1', $_csv_data[$_csv_i]);
            $_csv_data[$_csv_i] = str_replace($e . $e, $e, $_csv_data[$_csv_i]);
        }
        return empty ($_line) ? false : $_csv_data;
     }

     // 自动识别编码，并转换到指定编码，检测顺序依次是gbk,UTF-8,gb2312,big5
    function convert_encoding($string,$encoding = 'UTF-8') {
        return mb_convert_encoding($string, $encoding, 'gbk,UTF-8,gb2312,big5');
        /*
        $is_utf8 =  preg_match('%^(?:[\x09\x0A\x0D\x20-\x7E]| [\xC2-\xDF][\x80-\xBF]|  \xE0[\xA0-\xBF][\x80-\xBF] | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}    |  \xED[\x80-\x9F][\x80-\xBF] |  \xF0[\x90-\xBF][\x80-\xBF]{2}  | [\xF1-\xF3][\x80-\xBF]{3}  |  \xF4[\x80-\x8F][\x80-\xBF]{2} )*$%xs', $string);
        if ($is_utf8 && $encoding == 'utf8') {
            return $string;
        } elseif ($is_utf8) {
            return mb_convert_encoding($string, $encoding, "UTF-8");
        } else {
            return mb_convert_encoding($string, $encoding, 'gbk,UTF-8,gb2312,big5');
        }
        */
    }

	//搜索条件fun
	function check_where(&$where ,$column, $check_func = 'isset', $where_type = 'eq',$assign = true) {
 		$has_rule = false;
 		$where_key = empty($column_alias) ? $column : $column_alias;
 		$dot_pos = stripos('.', $column);
 		$key = ($dot_pos===false) ? $column : substr($column, $dot_pos);
 		switch ($check_func) {
 			case 'isset':
 				$has_rule = isset($_GET[$key]);
 				break;
 			case 'noempty':
 				$has_rule = !empty($_GET[$key]);
 				break;
 			default:
 				$has_rule = isset($_GET[$key]);
 				break;
 		}
 		if ($has_rule) {
 			if ($where_type == 'eq') {
				$where[$where_key] = trim($_GET[$key]);
 			} else if ($where_type == 'like') {
 				$where[$where_key] = array('like', '%'.escape_string(trim($_GET[$key])).'%');
 			}
			$assign && $this->assign($key, $_GET[$key]);
		}
		return $has_rule;
 	}

 	function check_other_table_where(&$where, $other_where, $column, $join_field, $table, $check_func = 'isset', $where_type = 'eq', $assign = true,$column_alias=null)
 	{
		$model = new Model();
		$has_rule = false;
		if (is_array($join_field)) {
			$inner_key = $join_field[0];
			$join_key = $join_field[1];
		} else {
			$inner_key = $join_key = $join_field;
		}
		if ($this->check_where($other_where, $column, $check_func, $where_type, $assign)) {
 			$dot_pos = stripos('.', $join_key);
 			$field = ($dot_pos===false) ? $join_key : substr($join_key, $dot_pos);

			$res = $model->table($table)->where($other_where)->field($field)->select();
			$ids = array();
			if ($res) {
				foreach ($res as $value) {
					$ids[] = $value[$field];
				}
			}
			$where[$inner_key] = array('in', $ids);
		}
		return $has_rule;
 	}

 	function check_range_where(& $where, $start, $end, $column, $is_time = false, $assign = true)
 	{
 		$has_rule = false;

		if (!empty($_GET[$start]) && !empty($_GET[$end])) {
			$where[$column] = array(
				array('egt', $is_time ? strtotime(urldecode($_GET[$start])) : $_GET[$start]),
				array('elt', $is_time ? strtotime(urldecode($_GET[$end])) : $_GET[$end]),
			);
			$assign && $this->assign($start, $_GET[$start]);
			$assign && $this->assign($end, $_GET[$end]);

		} elseif (!empty($_GET[$start])) {
			$where[$column] = array('egt', $is_time ? strtotime(urldecode($_GET[$start])) : $_GET[$start]);
			$assign && $this->assign($start, $_GET[$start]);
		} elseif (!empty($_GET[$end])) {
			$where[$column] = array('elt', $is_time ? strtotime(urldecode($_GET[$end])) : $_GET[$end]);
			$assign && $this->assign($end, $_GET[$end]);
		}
		return $has_rule;
 	}
		/**
		*获取url参数字符串
		*@param $url_var array|string url中包含的参数变量
		*@param $repalce_arr array 用形式如：array('key'=>val),表示用val来替换key
		*@param $base64encode bool 是否对url进行base64编码
		*@param $method string 请求类型，是get还是post
		*/
		public function get_url_suffix($url_var,$base64encode = false,$replace_arr,$method = "get")
		{
				$url_suffix = "";
				//判断$url_var是字符串还是数组
				if(is_array($url_var))
				{
					//数组形式传入
						if(strtolower($method) == "get")
						{
							foreach ($url_var as $key=>$url)
							{
								if(isset($_GET[$url]) || $_GET[$url] != "")
								{
									$arr_value = $_GET[$url];
									if(count($replace_arr) > 0)
									{
										foreach ($replace_arr as $search => $replace)
										{
											$arr_value = str_ireplace($search,$replace,$arr_value);
										}
									}
									$url_suffix .= "/".$url."/".$arr_value;
								}
							}
						}

				}elseif(is_string($url_var))
				{
						//字符串形式传入
						if(strtolower($method) == "get")
						{
							if(isset($_GET[$url_var]) || $_GET[$url_var] != "")
							{
									$arr_value = $_GET[$url];
									if(count($replace_arr) > 0)
									{
										foreach ($replace_arr as $search => $replace)
										{
											$arr_value = str_ireplace($search,$replace,$arr_value);
										}
									}
								$url_suffix .= "/".$url_var."/".$arr_value;
							}
						}
				}
				if($base64encode){
					return '/url_suffix/'.base64_encode($url_suffix);
				}else{
					return $url_suffix;
				}
		}

	//计算字段长度
	function strlen_az($string, $charset='utf-8')
	{
		$n = $count = 0;
		$length = strlen($string);
		if (strtolower($charset) == 'utf-8')
		{
			while ($n < $length)
			{
				$currentByte = ord($string[$n]);
				if ($currentByte == 9 || $currentByte == 10 || (32 <= $currentByte && $currentByte <= 126))
				{
					$n++;
					$count++;
				} elseif (194 <= $currentByte && $currentByte <= 223)
				{
					$n += 2;
					$count += 2;
				} elseif (224 <= $currentByte && $currentByte <= 239)
				{
					$n += 3;
					$count += 2;
				} elseif (240 <= $currentByte && $currentByte <= 247)
				{
					$n += 4;
					$count += 2;
				} elseif (248 <= $currentByte && $currentByte <= 251)
				{
					$n += 5;
					$count += 2;
				} elseif ($currentByte == 252 || $currentByte == 253)
				{
					$n += 6;
					$count += 2;
				} else
				{
					$n++;
					$count++;
				}
				if ($count >= $length)
				{break;
				}
			}
			return $count;
		} else {
			for ($i = 0; $i < $length; $i++)
			{
				if (ord($string[$i]) > 127) {
					$i++;
					$count++;
				}
				$count++;
			}
			return $count;
		}
	}
    
    // 自动转换时间函数，根据type将日期自动补充小时、分钟、秒，time_str至少需填至年月日，产品需求是只支持年/月/日或年/月/日 时/分/秒两种格式
    function auto_convert_time($time_str, $type = 0) {
        if (empty($time_str)) {
            return false;
        }
        $fullfil_time_str = '';
        $reg_arr = array(
            '/^\s*(\d{4}[\/|-]\d{1,2}[\/|-]\d{1,2})\s*$/',
            //'/^\s*(\d{4}[\/|-]\d{1,2}[\/|-]\d{1,2}\s+\d{1,2})\s*$/',
            //'/^\s*(\d{4}[\/|-]\d{1,2}[\/|-]\d{1,2}\s+\d{1,2}:\d{1,2})\s*$/',
            '/^\s*(\d{4}[\/|-]\d{1,2}[\/|-]\d{1,2}\s+\d{1,2}:\d{1,2}:\d{1,2})\s*$/'
        );
        $fullfil_arr = array(
            array(
                ' 00:00:00',
                //':00:00',
                //':00',
                ''
            ),
            array(
                ' 23:59:59',
                //':59:59',
                //':59',
                ''
            ),
        );
		if($type==2) //刷数据库创建时间
		{
			$time = strtotime($time_str)+rand(1,3600);
			$fulfill_time_str = date('Y/m/d H:i:s', $time);
			return $fulfill_time_str;
		}
		else
		{
			$type = $type ? 1 : 0;
			foreach ($reg_arr as $key => $reg) {
				if (preg_match($reg, $time_str, $matches)) {
					$match_time_str = $matches[1];
					$match_time_str .= $fullfil_arr[$type][$key];
					$time = strtotime($match_time_str);
					$fulfill_time_str = date('Y/m/d H:i:s', $time);
					return $fulfill_time_str;
				}
			}
		}
       
        return false;
    }
	
	
	public function error($msg,$ajax=false){
	
		if($_POST['USEAPI']){
		
			exit($msg);
		}
		
		parent::error($msg,$ajax);
	
	}
	
	
	public function success($msg){
	
		if($_POST['USEAPI']){
			exit($msg);
		}
		parent::success($msg);
	}

	//计算csv中的个数 并返回
	function csv_count()
	{
		if($_FILES['upload_file'])
		{
			$filename=$_FILES['upload_file']['tmp_name'];
			$err = $_FILES["upload_file"]["error"];
			$file_name_csv=$_FILES['upload_file']['name'];
			$tmp_arr = explode(".", $file_name_csv);
			$name_suffix = array_pop($tmp_arr);
		}
		if($_FILES['paichu_upload_file'])
		{
			$filename=$_FILES['paichu_upload_file']['tmp_name'];
			$file_name_csv=$_FILES['paichu_upload_file']['name'];
			$tmp_arr = explode(".", $file_name_csv);
			$name_suffix = array_pop($tmp_arr);
		}
		if(empty($filename))
		{
			$error1=-1;
			echo '{"error1":"'.$error1.'"}';
			return;
		}
		if (strtoupper($name_suffix) != "CSV")
		{
			$error2=-2;
			echo '{"error2":"'.$error2.'"}';
			return;
		}

		$handle=fopen($filename,'r');
		$out = array ();
		$out_unique = array ();
		$n = 0;
		$reapt = 0;
		$i = 0;
		while (!feof($handle))
		{
			$mm = fgets($handle);
			if(!trim($mm)) continue;
			if($i == 0) {
				if( trim($mm) != "IMEI"){
					$error2 = -3;
					break;
				}
			}
			if( strrpos($mm, 'E+') ) {
				$reapt ++;
			}
			if($i != 0) $n++;
			$i++;

		}
		fclose($handle);
		if($error2 == -3) {
			echo '{"error2":"'.$error2.'"}';
			return;
		}
		if( $reapt > 10 ) {
			$error2 = -4;
			echo '{"error2":"'.$error2.'"}';
			return;
		}
		//上传csv不去重，去重太慢
		$path=date("/Ym/d/",time());
		$save_dir = C("MARKET_PUSH_CSV").$path;
		$this->mkDirs($save_dir);
		$save_name = MODULE_NAME. '_' . ACTION_NAME  . '_' . time() . '_' . $_SESSION['admin']['admin_id'] . '.csv';
		$save_file_name = $save_dir . $save_name;
		$db_save=$path.$save_name;
		if($_FILES['upload_file'])
		{
			move_uploaded_file($_FILES['upload_file']['tmp_name'], $save_file_name);
		}
		if($_FILES['paichu_upload_file'])
		{
			move_uploaded_file($_FILES['paichu_upload_file']['tmp_name'], $save_file_name);
		}
		echo '{"out_count":"' . $n . '","csv_url":"' . $db_save . '"}';
	}
}
?>
