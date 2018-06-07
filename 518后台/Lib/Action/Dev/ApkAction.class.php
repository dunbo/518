<?php

//软件发布,编辑,升级

if(strpos(strtolower(PHP_OS),'win')!==FALSE) {
	define('LOG','e:/');
} else {
	define('LOG','/data/att/permanent_log/dev.anzhi.com/');
}
define('IS_ADMIN',TRUE);
if(!defined('IMG_HOST')) define('IMG_HOST',IMGATT_HOST);

class ApkAction extends CommonAction {
	//上传新软件
	public function add_new() {
		//对应 dev.anzhi.com/add_new.php的代码
		if(!$_GET['do']) {
			if(empty($_GET['type'])) $_GET['type'] = 'new';
			if($_POST['referer']) $_SESSION['referer'] = $_POST['referer'];

			if($_GET['type']=='new') {
				foreach($_SESSION as $key=>$val) {
					if(preg_match("/^apk_/i",$key)) {
						unset($_SESSION[$key]);
					}
				}
			}
		if(!$_GET['mark']){
			$mark	=	uniqid();
		}else{
		    $mark	=	$_GET['mark'];
		}
			//修改时可以上传apk,也可以不上传
			if(in_array($_GET['type'],array('mod_tmp'))) {	
				$this->assign('go_step','1');
			} else {
				$this->assign('go_step','0');
			}
			$_GET['css_page'] = 'page4';
			$this->assign('mark',$mark);
			$this->display('add_new');
		}
	}

	//编辑线上
	public function mod_line() {
		$apkmodel = D("Dev.Apk");
		//对应 dev.anzhi.com/add_new_confirm.php的代码
		if(empty($_GET['type'])) $_GET['type'] = 'new';

		$referer = $_SERVER['HTTP_REFERER'] ? $_SERVER['HTTP_REFERER'] : "/index.php/Dev/Apk/add_new?type={$_GET['type']}&softid={$_GET['softid']}&tmp_id={$_GET['tmp_id']}";

		if(!$_GET['mark']){
			$mark	=	uniqid();
		}else{
		    $mark	=	$_GET['mark'];
		}
		if(!$_GET['do']) {
			if($_POST['referer']) $_SESSION['referer'] = $_POST['referer'];

			//获取分类信息
			$ret = $apkmodel -> getCategory();
			if($ret!='ok') {
				$this->assign('jumpUrl',$referer);
				$this->error($ret);
			}

			//判断本次操作的类型:record_type
			if($_GET['type']=='mod_line') {	//修改描述(已上架)

				unset($_SESSION['apk_info'.$mark]);
				if(!$_GET['softid'] || !is_numeric($_GET['softid'])) {
					$this->assign('jumpUrl',$referer);
					$this->error('修改描述时软件id参数错误');
				}

				//获取修改描述的软件的信息
				$_vals = array(
					'do' => 'soft',
					'softid' => $_GET['softid'],
				);
				$_arr = $apkmodel ->  _http_post($_vals);
				$_arr = json_decode($_arr, true);
				if(!$_arr || $_arr['code']!=1) {
					$this->assign('jumpUrl',$referer);
					$this->error("修改描述时获取软件(id:{$_GET['softid']})信息失败");
				} else {
					if(!$_arr['ret']) {
						$this->assign('jumpUrl',$referer);
						$this->error("没找到该软件id:{$_GET['softid']}对应的上架或下架软件");
					} else if(!$_arr['ret']['url']) {
						$this->assign('jumpUrl',$referer);
						$this->error("没找到该软件id:{$_GET['softid']}对应的软件包");
					}
					$_SESSION['apk_info'.$mark] = $_arr['ret'];
				}

				if($_SESSION['apk_info'.$mark]['update_content']) {	//编辑升级
					$skip_field = "";
				} else {
					$skip_field = "'update_content'";
				}

				$_SESSION['apk_info'.$mark]['record_type'] = '2';		//修改描述(已上架)
				$_SESSION['apk_info'.$mark]['softid'] = $_GET['softid'];
			}
			//session中apk_info,apk_form处理
			sess_adjust($mark);
			
			$_SESSION['apk_info'.$mark]['get_type'] = $_GET['type'];
			//角标状态,后台专用,可以直接读数据库
			$model = new Model();
			$_SESSION['apk_form'.$mark]['corner_mark'] = $model->table('sj_corner_mark')->where("status='1'")->select();
			$package = $_SESSION['apk_info'.$mark]['package'];
			//$this->copyright($package);
			$this->assign('skip_field',$skip_field);
			$_GET['css_page'] = 'page4';
			$this->assign('mark',$mark);
			//一名话短语
			$note_list = $model->table('sj_soft_note')->where("package='{$package}'")->field('in_short')->find();
			$_SESSION['apk_form'.$mark]['in_short'] = $note_list['in_short'];
			$this->is_show_beian($package,$mark);
			//视频是否显示
			$where = array(
					'package' => $package,
					'status' => 1
			);
			$video =  $model->table('sj_soft_whitelist')->where($where)->find();
			if($video){
				$_SESSION['apk_info'.$mark]['show_video'] = 1;
			}
			$this->display('add_new_confirm');
		}
	}

	//编辑临时表
	public function mod_tmp() {
		$apkmodel = D("Dev.Apk");
		//对应 dev.anzhi.com/add_new_confirm.php的代码
		if(empty($_GET['type'])) $_GET['type'] = 'new';

		$referer = $_SERVER['HTTP_REFERER'] ? $_SERVER['HTTP_REFERER'] : "/index.php/Dev/Apk/add_new?type={$_GET['type']}&softid={$_GET['softid']}&tmp_id={$_GET['tmp_id']}";
		if(!$_GET['mark']){
			$mark	=	uniqid();
		}else{
		    $mark	=	$_GET['mark'];
		}
		if(!$_GET['do']) {
			if($_POST['referer']) $_SESSION['referer'] = $_POST['referer'];

			//获取分类信息
			$ret = $apkmodel -> getCategory();
			if($ret!='ok') {
				$this->assign('jumpUrl',$referer);
				$this->error($ret);
			}	
			//判断本次操作的类型:record_type
			if($_GET['type']=='mod_tmp') {
				if(!$_GET['tmp_id'] || !is_numeric($_GET['tmp_id'])) {
					$this->assign('jumpUrl',$referer);
					$this->error("参数错误({$_GET['type']})");
				}

				//后台编辑全不上传apk
				//获取编辑信息
				$vals = array(
					'do' => 'soft_tmp',
					'tmp_id' => $_GET['tmp_id'],
				);
				$arr = $apkmodel -> _http_post($vals);
				$arr = json_decode($arr, true);
				//软件包内名称
				$_SESSION['apk_info'.$mark]['apk_softname'] = $arr['ret']['apk_softname'];
				if(!$arr || $arr['code']!=1) {
					$this->assign('jumpUrl','/index.php/Dev/Apk/add_new');
					$this->error($arr['msg'] ? $arr['msg'] : '获取软件信息时通讯失败，请重试(1)');
				} else {
					$_SESSION['apk_info'.$mark] = $arr['ret'];
					$_SESSION['apk_info'.$mark]['ok'] = md5(microtime());
				}
				if($arr['ret']['status'] !=2 && $_GET['not'] != 1){
					$this->assign('jumpUrl',$referer);
					$this->error('此软件已经通过或驳回！');
				}
				if($arr['ret']['status'] ==2 && $arr['ret']['record_type'] >4 ){
					$this->assign('jumpUrl',$referer);
					$this->error('此软件在草稿箱中！');
				}
				unset($_SESSION['apk_info'.$mark]['apk']);

				if(in_array($_SESSION['apk_info'.$mark]['record_type'], array(2,3,7))) {	//软件升级
					$skip_field = "";
				} else {
					$skip_field = "'update_content'";
				}
				$_SESSION['apk_info'.$mark]['tmp_id'] = $_GET['tmp_id'];
			}
			if(in_array($_GET['type'], array('mod_tmp'))) {
				if(empty($_SESSION['apk_info'.$mark]['ok']) || strlen($_SESSION['apk_info'.$mark]['ok'])!=32) {
					$this->assign('jumpUrl',$referer);
					$this->error('apk包信息获取失败');
				}
			}

			//session中apk_info,apk_form处理
			sess_adjust($mark);

			$_SESSION['apk_info'.$mark]['get_type'] = $_GET['type'];
			//角标状态,后台专用,可以直接读数据库
			$model = new Model();
			$_SESSION['apk_form'.$mark]['corner_mark'] = $model->table('sj_corner_mark')->where("status='1'")->select();
			$package = $_SESSION['apk_info'.$mark]['package'];
			//print_r($_SESSION['apk_info'.$mark]);
			//$this->copyright($package);
			$this->assign('skip_field',$skip_field);
			$_GET['css_page'] = 'page4';
			//获取标签
			$Tagsmodel = D('Sj.Tags');
			$tag_list = $Tagsmodel -> get_tag($package);	
			$is_new = $Tagsmodel -> getTagidbyname($tag_list[1]);
			$this->assign('is_new',$is_new);					
			$this->assign('tag_list',$tag_list);
			//运营白名单信息是否是定时上架软件
			$where = array(
				'package' => $package,
				//'is_time_shelves' => 1,
				'status' => 1
			);
			$this->is_show_beian($package,$mark);
			$soft_shelves =  $model->table('sj_soft_whitelist')->where($where)->field('id,is_time_shelves')->find();
			if($soft_shelves){
				//视频
				$_SESSION['apk_info'.$mark]['show_video'] = 1 ;
				//定时上架
				if($soft_shelves['is_time_shelves']==1)
				$_SESSION['apk_info'.$mark]['settime_online'] = 1 ;			
			}
			$this->assign('mark',$mark);
			//一名话短语
			$note_list = $model->table('sj_soft_note')->where("package='{$package}'")->field('in_short')->find();
			$_SESSION['apk_form'.$mark]['in_short'] = $note_list['in_short'];
			$this->display('add_new_confirm');
		}
	}
	
	//版本升级
	public function update() {
		//对应 dev.anzhi.com/add_new.php的代码
		if(!$_GET['mark']){
			$mark	=	uniqid();
		}else{
		    $mark	=	$_GET['mark'];
		}
		if(!$_GET['do']) {
			if(empty($_GET['type'])) $_GET['type'] = 'new';
			if($_POST['referer']) $_SESSION['referer'] = $_POST['referer'];

			if($_GET['type']=='update') {
				foreach($_SESSION as $key=>$val) {
					if(preg_match("/^apk_/i",$key)) {
						unset($_SESSION[$key]);
					}
				}
				$model = new Model();
				$list = $model->table('sj_soft')->where("status='1' and softid='{$_GET['softid']}'")->field('package')->find();
				$screen = $model->table('sj_soft_screen')->where("status='2' and package='{$list['package']}'")->find();
				if($screen){
					$this->assign('error','此软件已经通过闪屏申请审核，您上传的APK启动Loading页需带有安智LOGO');
				}
			}
			
			if($_GET['type']=='update_apk') {
			    foreach($_SESSION as $key=>$val) {
			        if(preg_match("/^apk_/i",$key)) {
			            unset($_SESSION[$key]);
			        }
			    }
			}

			if(in_array($_GET['type'],array('mod_tmp','update_apk'))) {	//修改时可以上传apk,也可以不上传
				$this->assign('go_step','1');
			} else {			    
				$this->assign('go_step','0');
			}
			$_GET['css_page'] = 'page4';
			$this->assign('mark',$mark);
			$this->display('add_new');
		}
	}

	public function apk_upload() {
		$apkmodel = D("Dev.Apk");
		if(!$_GET['mark']){
		    $mark	=	uniqid();
		}else{
		    $mark	=	$_GET['mark'];
		}
		//对应 dev.anzhi.com/add_new.php的代码
		if($_GET['do'] == 'apk') {		//apk上传
			//变量初始化
			$ret = array('code' => '0', 'msg' => '未知错误');	//返回上传提示
			$_SESSION['apk_info'.$mark] = '';							//apk信息
			if(empty($_GET['type'])) $_GET['type'] = 'new'; 

			//session检查
			if(empty($_SESSION['admin']['admin_id'])) {
				$ret['msg'] = 'session发生错误，请重试';
				_exit($ret);
			}
			 
			if($_GET['path_apk']){
			    if(is_file($_GET['path_apk'])){
			        $ret['msg'] = '请选择要上传的apk文件！';
			        _exit($ret);
			    }
			    $path_apk = explode('.', $_GET['path_apk']);
			    if($path_apk[count($path_apk)-1] != 'apk'){
			        $ret['msg'] = '请选择要上传的apk文件！';
			        _exit($ret);
			    }
			    $vals = array(
			            'do' => 'apk',
			            'apk' => '@'.$_GET['path_apk'],
			            'name' => uniqid(),
			            'id' => $_SESSION['admin']['admin_id'],
			    );
			    
			}else{
			    //apk检查
			    if($_FILES['apk']['error'] == 4) {
			        $ret['msg'] = '请选择要上传的apk文件！';
			        _exit($ret);
			    }
			    if($_FILES['apk']['error'] != 0) {
			        $ret['msg'] = '上传失败，错误代码：'.$_FILES['apk']['error'];
			        _exit($ret);
			    } else if(strtolower(pathinfo($_FILES['apk']['name'],PATHINFO_EXTENSION)) != 'apk') {	//后缀检查
			        $ret['msg'] = '上传失败，只允许上传apk后缀的文件';
			        _exit($ret);
			    }
			    
			    //获取apk信息
			    $vals = array(
			            'do' => 'apk',
			            'apk' => '@'.$_FILES['apk']['tmp_name'],
			            'name' => $_FILES['apk']['name'],
			            'id' => $_SESSION['admin']['admin_id'],
			    );
			}
			$arr = $apkmodel -> _http_post($vals);
			$arr = json_decode($arr, true);
			if($arr) {
				if($arr['code'] < 1) {
					$ret['msg'] = $arr['msg'];
					_exit($ret);
				}
			} else {
				$ret['msg'] = '上传失败。上传apk返回异常，请重试';
				_exit($ret);
			}

				
			//返回的apk信息
			$_SESSION['apk_info'.$mark] = $arr['ret'];
			$_SESSION['apk_info'.$mark]['_iconurl'] = IMG_HOST.$_SESSION['apk_info'.$mark]['_iconurl'];		//icons
			$_SESSION['apk_info'.$mark]['iconurl'] = $arr['ret']['_iconurl'];

			//返回显示包信息
			$ret['ret'] = $_SESSION['apk_info'.$mark];
			
			//包名规范性检查	
			$pname_chk = packagename_chk ($arr['ret'][packagename]);
			if($pname_chk != 'ok'){
			    $ret['msg'] = $pname_chk;
			    _exit($ret);
			}
			$extension_arr = C('extension_arr');
			foreach($extension_arr as $val){
				if(strpos($arr['ret']['packagename'],$val)){
					$ret['pack_extension'] = "此软件疑似竞品，包名包含{$val}";
					continue;
				}
			}

			if($_GET['type']=='new') {	//新软件

				//软件包检查
				$vals = array(
					'do' => 'package',
					'package' => $_SESSION['apk_info'.$mark]['packagename'],
					'version_code' => $_SESSION['apk_info'.$mark]['versionCode'],
					'abi' => $_SESSION['apk_info'.$mark]['abi'],
				);
				$arr = $apkmodel -> _http_post($vals);
				$arr = json_decode($arr, true);
				if(!$arr || $arr['code'] < 1) {
					$ret['msg'] = $arr['msg'] ? $arr['msg'] : '获取包信息时通讯失败，请重试';
					_exit($ret);
				} else {	//开始包检查
					//线上表检查
					if($arr['ret']) {
						$ret['msg'] = '该软件已上架，请勿重复操作。';
						_exit($ret);
					}

					//可以上传
					$_SESSION['apk_info'.$mark]['ok'] = md5(microtime());	//新软件可以上传
					$ret['code'] = '1';
					$ret['msg'] = '软件上传成功';
					_exit($ret);
				}

			} else if($_GET['type']=='update') {	//版本升级

				$_SESSION['apk_update'.$mark] = '';
				if(!$_GET['softid'] || !is_numeric($_GET['softid'])) {
					$ret['msg'] = '版本升级缺少软件id参数';
					_exit($ret);
				}
				$_vals = array(
					'do' => 'soft',
					'softid' => $_GET['softid'],
				);
				$_arr = $apkmodel -> _http_post($_vals);
				$_arr = json_decode($_arr, true);
				if(!$_arr || $_arr['code']!=1) {
					$ret['msg'] = $_arr['msg'] ? $_arr['msg'] : '获取升级软件信息时通讯失败，请重试';
					_exit($ret);
				} else {
					if(!$_arr['ret']) {
						$ret['msg'] = "没找到该软件id对应的上架软件，无法升级";
						_exit($ret);
					} else {	//package一致,version_code高就可以更新
						$_SESSION['apk_update'.$mark] = $_arr['ret'];	//升级软件信息
						if($_SESSION['apk_update'.$mark]['package']!=$_SESSION['apk_info'.$mark]['packagename']) {
							$ret['msg'] = "包名({$_SESSION['apk_update'.$mark]['package']})与当前包名({$_SESSION['apk_info'.$mark]['packagename']})不同。如果您希望发布软件而非升级，请点击左侧[发布软件]。";
							_exit($ret);
						} else if($_SESSION['apk_update'.$mark]['version_code']>=$_SESSION['apk_info'.$mark]['versionCode']) {
							$ret['msg'] = "请上传高于已上架版本号（version code）的软件。";
							_exit($ret);
						} else {
							$_SESSION['apk_info'.$mark]['ok'] = md5(microtime());	//可以版本升级
							$ret['code'] = '1';
							$ret['msg'] = '软件上传成功';
							_exit($ret);
						}
					}
				}

			}else if($_GET['type']=='update_apk') {	//替换包

				$_SESSION['apk_update'.$mark] = '';
				if(!$_GET['softid'] || !is_numeric($_GET['softid'])) {
					$ret['msg'] = '缺少软件id参数';
					_exit($ret);
				}
				$_vals = array(
					'do' => 'soft',
					'softid' => $_GET['softid'],
				);
				$_arr = $apkmodel -> _http_post($_vals);
				$_arr = json_decode($_arr, true);
				if(!$_arr || $_arr['code']!=1) {
					$ret['msg'] = $_arr['msg'] ? $_arr['msg'] : '获取升级软件信息时通讯失败，请重试';
					_exit($ret);
				} else {
					if(!$_arr['ret']) {
						$ret['msg'] = "没找到该软件id对应的上架软件，无法升级";
						_exit($ret);
					} else {	//package一致,version_code
						$_SESSION['apk_update'.$mark] = $_arr['ret'];	//升级软件信息

						if($_SESSION['apk_update'.$mark]['package']!=$_SESSION['apk_info'.$mark]['packagename']) {
							$ret['msg'] = "包名({$apk_update['package']})与当前包名({$_SESSION['apk_info'.$mark]['packagename']})不同。如果您希望发布软件而非替换包，请点击左侧[发布软件]。";
							_exit($ret);
						} else if($_SESSION['apk_update'.$mark]['version_code']!=$_SESSION['apk_info'.$mark]['versionCode']) {
							$ret['msg'] = "请上传与上架版本号（version code）相同的软件。";
							_exit($ret);
						} else {
							$_SESSION['apk_info'.$mark]['ok'] = md5(microtime());	//可以版本升级
							$ret['code'] = '1';
							$ret['msg'] = '软件上传成功';
							_exit($ret);
						}
					}
				}
			}else if($_GET['type']=='mod_tmp') {	//修改未通过软件
				if($_SESSION['apk_tmp']['package']!=$_SESSION['apk_info'.$mark]['packagename']) {
					$_SESSION['apk_info'.$mark]['ok'] = '';
					$ret['msg'] = '和原来的包名不一致';
					_exit($ret);
				} else {
					$_SESSION['apk_info'.$mark]['ok'] = md5(microtime());
					$ret['code'] = '1';
					$ret['msg'] = '软件上传成功';
					_exit($ret);
				}
			}
		}
	}

public function confirm() {	
	$apkmodel = D("Dev.Apk");
	//对应 dev.anzhi.com/add_new_confirm.php的代码,开始/代码开始 ====================================================
	if(empty($_GET['type'])) $_GET['type'] = 'new';
	$mark = $_GET['mark']?$_GET['mark']:uniqid();
	$referer = $_SERVER['HTTP_REFERER'] ? $_SERVER['HTTP_REFERER'] : "/index.php/Dev/Apk/add_new?type={$_GET['type']}&softid={$_GET['softid']}&tmp_id={$_GET['tmp_id']}";

	//包信息检查
	if(($_GET['from_type'] !='cj_update' && $_GET['from_type'] !='cj_add') && in_array($_GET['type'], array('new','update'))) {	//新软件，版本升级需要上传apk包
		if(empty($_SESSION['apk_info'.$mark]['ok']) || strlen($_SESSION['apk_info'.$mark]['ok']) != 32) {
			$this->assign('jumpUrl','/index.php/Dev/Apk/add_new');
			$this->error('请先上传有效的apk包');
		}
	}

	if(!$_GET['do']) {
		if($_POST['referer']) $_SESSION['referer'] = $_POST['referer'];

		//获取分类信息
		$ret = $apkmodel -> getCategory();
		if($ret!='ok') {
			$this->assign('jumpUrl',$referer);
			$this->error($ret);
		}
		//mod_line,mod_tmp不走该流程
		if(in_array($_GET['type'],array('mod_line','mod_tmp'))) {
			header("Location: /index.php/Dev/Apk/{$_GET['type']}?type={$_GET['type']}&mark={$mark}&softid={$_GET['softid']}&tmp_id={$_GET['tmp_id']}");
			exit;
		}
		//判断本次操作的类型:record_type
		if($_GET['type']=='new') {				//新软件
			unset($_SESSION['apk_form'.$mark]);
			$skip_field = "'update_content'";	//js前端将不检查更新日志
			$_SESSION['apk_info'.$mark]['record_type'] = '1';
			$_SESSION['apk_info'.$mark]['update_from'] = '0';
		} else if($_GET['type']=='update') {	//版本升级
			unset($_SESSION['apk_form'.$mark]);
			$skip_field = "";
			if(!$_SESSION['apk_info'.$mark]) $_SESSION['apk_info'.$mark] = array();
			$_SESSION['apk_info'.$mark]['record_type'] = '3';
			
			$_SESSION['apk_info'.$mark]['update_from'] = $_SESSION['apk_update'.$mark]['softid'] ? $_SESSION['apk_update'.$mark]['softid'] : $_GET['softid'];
			if($_SESSION['apk_update'.$mark]) {	//有上传升级包,编辑升级信息时可能没有新上传升级包
				$_SESSION['apk_info'.$mark] = array_merge($_SESSION['apk_update'.$mark],$_SESSION['apk_info'.$mark]);
			}
			unset($_SESSION['apk_update'.$mark]);

		} else if($_GET['type']=='update_apk') {	//替换包
			unset($_SESSION['apk_form'.$mark]);
			$skip_field = "";
			if(!$_SESSION['apk_info'.$mark]){
				$_SESSION['apk_info'.$mark] = array();
				//获取软件的信息
				$_vals = array(
						'do' => 'soft',
						'softid' => $_GET['softid'],
				);
				$_arr = $apkmodel -> _http_post($_vals);
				$_arr = json_decode($_arr, true);
				if(!$_arr || $_arr['code']!=1) {
					$this->assign('jumpUrl',$referer);
					$this->error("修改描述时获取软件(id:{$_GET['softid']})信息失败");
				} else {
					if(!$_arr['ret']) {
						$this->assign('jumpUrl',$referer);
						$this->error("没找到该软件id:{$_GET['softid']}对应的上架或下架软件");
					} else if(!$_arr['ret']['url']) {
						$this->assign('jumpUrl',$referer);
						$this->error("没找到该软件id:{$_GET['softid']}对应的软件包");
					}
					$_SESSION['apk_info'.$mark] = $_arr['ret'];
				}
			}
			if($_SESSION['apk_update'.$mark]) {	//有上传升级包,编辑升级信息时可能没有新上传升级包
				$_SESSION['apk_info'.$mark] = array_merge($_SESSION['apk_update'.$mark],$_SESSION['apk_info'.$mark]);
			}
			$model = new Model();
			$newicon = $model->table('sj_icon')->where("status='1' and softid = '{$_GET['softid']}'")->find();
			if($newicon){
				$_SESSION['apk_info'.$mark] = array_merge(array('iconurl_gif'=>$newicon['iconurl_gif'],'iconurl_512'=>$newicon['iconurl_512'],'new_icon'=>$newicon['iconurl_96'],'new_apk_icon'=>$newicon['apk_icon']),$_SESSION['apk_info'.$mark]);
			}
			unset($_SESSION['apk_update'.$mark]);
				

		}else if($_GET['type']=='mod_line') {	//修改描述(已上架)
		  
			unset($_SESSION['apk_info'.$mark]);
			if(!$_GET['softid'] || !is_numeric($_GET['softid'])) {
				$this->assign('jumpUrl',$referer);
				$this->error('修改描述时软件id参数错误');
			}
			//获取修改描述的软件的信息
			$_vals = array(
				'do' => 'soft',
				'softid' => $_GET['softid'],
			);
			$_arr = $apkmodel -> _http_post($_vals);
			$_arr = json_decode($_arr, true);
			if(!$_arr || $_arr['code']!=1) {
				$this->assign('jumpUrl',$referer);
				$this->error("修改描述时获取软件(id:{$_GET['softid']})信息失败");
			} else {
				if(!$_arr['ret']) {
					$this->assign('jumpUrl',$referer);
					$this->error("没找到该软件id:{$_GET['softid']}对应的上架或下架软件");
				} else if(!$_arr['ret']['url']) {
					$this->assign('jumpUrl',$referer);
					$this->error("没找到该软件id:{$_GET['softid']}对应的软件包");
				}
				$_SESSION['apk_info'.$mark] = $_arr['ret'];
			}
			$skip_field = "'update_content'";
			$_SESSION['apk_info'.$mark]['record_type'] = '2';		//修改描述(已上架)
			$_SESSION['apk_info'.$mark]['softid'] = $_GET['softid'];
			$package = $_SESSION['apk_info'.$mark]['package'];
			//$this->copyright($package);
		}else if($_GET['type']=='mod_tmp') {
			if(!$_GET['tmp_id'] || !is_numeric($_GET['tmp_id'])) {
				$this->assign('jumpUrl',$referer);
				$this->error("参数错误({$_GET['type']})");
			}
			//后台编辑全不上传apk
			//获取编辑信息
			$vals = array(
				'do' => 'soft_tmp',
				'tmp_id' => $_GET['tmp_id'],
			);
			$arr = $apkmodel -> _http_post($vals);
			$arr = json_decode($arr, true);
			if(!$arr || $arr['code']!=1) {
				$this->assign('jumpUrl','/index.php/Dev/Apk/add_new');
				$this->error($arr['msg'] ? $arr['msg'] : '获取软件信息时通讯失败，请重试(1)');
			} else {
				$_SESSION['apk_info'.$mark] = $arr['ret'];
				$_SESSION['apk_info'.$mark]['ok'] = md5(microtime());
			}

			unset($_SESSION['apk_info'.$mark]['apk']);

			if(in_array($_SESSION['apk_info'.$mark]['record_type'], array(3,7))) {	//软件升级
				$skip_field = "";
			} else {
				$skip_field = "'update_content'";
			}
			$_SESSION['apk_info'.$mark]['tmp_id'] = $_GET['tmp_id'];
		}
		if(in_array($_GET['type'], array('mod_tmp'))) {
			if(empty($_SESSION['apk_info'.$mark]['ok']) || strlen($_SESSION['apk_info'.$mark]['ok'])!=32) {
				$this->assign('jumpUrl',$referer);
				$this->error('apk包信息获取失败');
			}
		}
		//session中apk_info,apk_form处理
		$model = new Model();
		sess_adjust($mark);
		$_SESSION['apk_info'.$mark]['get_type'] = $_GET['type'];
		$this->is_show_beian($_SESSION['apk_info'.$mark]['package'],$mark);
		if($_SESSION['apk_info'.$mark]['show_beian']!=1 && $_SESSION['apk_form'.$mark]['soft_type']=='2'){
			$_SESSION['apk_info'.$mark]['show_beian'] = 1;
			$_SESSION['apk_info'.$mark]['is_not_soft_whitelist'] = 1;
			//获取新$beian
			$beian = $model->table('sj_soft_publication_config')->where(array('package' => $_SESSION['apk_info'.$mark]['package'], 'status' => 1))->field('publication_num')->find();
			if($beian){
				$_SESSION['apk_info'.$mark] = array_merge($beian,$_SESSION['apk_info'.$mark]);
			}
			
		}
		//角标状态,后台专用,可以直接读数据库
		
		$_SESSION['apk_form'.$mark]['corner_mark'] = $model->table('sj_corner_mark')->where("status='1'")->select();
		$package = $_SESSION['apk_info'.$mark]['package'];
		//$this->copyright($package);
		$this->assign('skip_field',$skip_field);
		$_GET['css_page'] = 'page4';
		//获取标签
		$Tagsmodel = D('Sj.Tags');
		$tag_list = $Tagsmodel -> get_tag($package,1);	
		$is_new = $Tagsmodel -> getTagidbyname($tag_list[1]);
		$this->assign('is_new',$is_new);			
		$this->assign('tag_list',$tag_list);	
		//一名话短语
		$note_list = $model->table('sj_soft_note')->where("package='{$package}'")->field('in_short')->find();
		$_SESSION['apk_form'.$mark]['in_short'] = $note_list['in_short'];
		//备注
		if(empty($_SESSION['apk_form'.$mark]['beizhu'])){
		    $beizhu = $model->table('sj_soft_tmp')->where(array('status' => 1,'package'=>$package))->field('beizhu')->order('id desc')->find();
			$_SESSION['apk_form'.$mark]['beizhu'] = $beizhu['beizhu'];
		}
		//视频是否显示
		$where = array(
				'package' => $package,
				'status' => 1
		);
		$video =  $model->table('sj_soft_whitelist')->where($where)->find();
		if($video){
			$_SESSION['apk_info'.$mark]['show_video'] = 1;
		}
		$this->assign('mark',$mark);
		$this->display('add_new_confirm');

	} else if($_GET['do'] == 'submit') {	//提交
		$old_softname = $_SESSION['apk_info'.$mark]['softname'];
		if(!$_POST) {
			parentjs('请提交数据');
		}
		$_SESSION['apk_info'.$mark]['from_type'] = $_GET['from_type'];
		if($_POST['from_icon'] ==1 && isset($_GET['from_type'])){
			$_SESSION['apk_info'.$mark]['from_icon'] = $_POST['from_icon'];
			if(empty($_SESSION['apk_form'.$mark]['new_icon']) && empty($_FILES['new_icon']['tmp_name'])){
				parentjs('请上传软件图标');
			}
		}else if($_POST['from_icon'] ==2 && isset($_GET['from_type'])){
			$_SESSION['apk_info'.$mark]['from_icon'] = $_POST['from_icon'];
			if(empty($_SESSION['apk_info'.$mark]['_iconurl']) && empty($_FILES['cj_new_icon']['tmp_name'])){
				parentjs('请上传软件图标');
			}
		}
		$_POST['intro'] = trim(htmlspecialchars(strip_tags($_POST['intro'])));			
		if($_POST['update_content']=='0~200个字符以内') $_POST['update_content'] = '';
		if($_POST['update_content']){
			$_POST['update_content'] = trim(htmlspecialchars(strip_tags($_POST['update_content'])));	
		}
		if(time()-$_SESSION['last_post']<5) {
			parentjs('数据提交中，请不要频繁操作！');
		} else {
			$_SESSION['last_post'] = time();
		}

		if($_GET['drafts'] == 1) {	//保存草稿
			if($_SESSION['apk_info'.$mark]['record_type']==1) {
				$_SESSION['apk_info'.$mark]['record_type'] = 5;
			} else if($_SESSION['apk_info'.$mark]['record_type']==2) {
				$_SESSION['apk_info'.$mark]['record_type'] = 6;
			} else if($_SESSION['apk_info'.$mark]['record_type']==3) {
				$_SESSION['apk_info'.$mark]['record_type'] = 7;
			}
			$_SESSION['apk_info'.$mark]['drafts'] = $_GET['drafts'];	//草稿
		} else {
			if($_SESSION['apk_info'.$mark]['record_type']==5) {
				$_SESSION['apk_info'.$mark]['record_type'] = 1;
			} else if($_SESSION['apk_info'.$mark]['record_type']==6) {
				$_SESSION['apk_info'.$mark]['record_type'] = 2;
			} else if($_SESSION['apk_info'.$mark]['record_type']==7) {
				$_SESSION['apk_info'.$mark]['record_type'] = 3;
			}
			$_SESSION['apk_info'.$mark]['drafts'] = $_GET['drafts'];
		}
		//文本规则检查
		$tmp_model = D("Dev.Softaudit");
		$book_categoryid = $tmp_model -> get_book_categoryid();		
		if(in_array($_POST['category_id'],$book_categoryid)){
			//关键词处理,开始
			$_POST['tags'] = '';
			$tags = array();
			for($i=0;$i<=4;$i++) {
				$val = trim(str_replace(',','',$_POST['tags'.$i]));
				if($val) {
					$tags[] = $val;
				}
			}
			$_POST['tags'] = implode(',',$tags);
		}else{
			for($i=0;$i<=4;$i++) {
				unset($_POST['tags'.$i]);
			}
			if(!empty($_POST['cj_tag_str']) || !empty($_POST['hot_tag_str'])){
				$i = 0;
				$cj_tag_arr = explode(',',$_POST['cj_tag_str']);
				$hot_tag_arr = explode(',',$_POST['hot_tag_str']);
				foreach($cj_tag_arr as $k => $v){
					if(!empty($v)){
						$i++;
					}else{	
						unset($cj_tag_arr[$k]);
					}
				}
				foreach($hot_tag_arr as $k=> $v){
					if(!empty($v)){
						$i++;
					}else{	
						unset($hot_tag_arr[$k]);
					}
				}
				if($i>5 || $i<1){
					parentjs('标签只能选择1-5个');
				}
				$cj_tag_arr = array_unique($cj_tag_arr);
				$hot_tag_arr = array_unique($hot_tag_arr);
			}	
		}
		//关键词处理,结束
		$_POST['packagename'] = $_SESSION['apk_info'.$mark]['packagename'];	//包名规范检查	
		$vals = array(
			'do' => 'info_chk',
			'anzhi_id' => 'admin',
		);
		$vals = array_merge($_POST, $vals);
		if($_GET['from_type'] == 'cj_add'||$_GET['from_type'] == 'cj_update'||empty($_POST['in_short'])){
		    unset($vals['in_short']);
		}
//		$arr = $apkmodel -> _http_post($vals);
//		$arr = json_decode($arr, true);
//		if(!$arr) {
//			parentjs('数据检查时通讯失败，请重试');
//		} else if($arr['code']!=1) {
//			parentjs($arr['msg']);
//		} else {
//			foreach($arr['ret'] as $field=>$val) {
//				if($val!='ok') {
//					parentjs($val);
//				}
//			}
//		}
		//截图和版权检查
		$j = 0;		//截图计数
		$k = 0;		//版权资料计数
		$j_g = 0;		//gif版截图计数

		if($_FILES) {
			foreach($_FILES as $key=>$val) {
				if($val['error']==4) {
					unset($_FILES[$key]);
					continue;
				}
				if($val['error']!=0) {
					parentjs("上传发生错误({$val['error']})");
				}
				$ext = strtolower(pathinfo($val['name'],PATHINFO_EXTENSION));
				if(strpos($key,'thumb_')!==FALSE) {	//截图
					if($val['size'] > 1048576) {	//1M
						parentjs("每张截图应1M以内，请重新选择");
					} else if(!in_array($ext,array('png','jpg','jpeg'))) {
						parentjs('截图只支持jpg，png格式，请重新选择');
					} else if(getimagesize($val['tmp_name'])===FALSE) {
						parentjs('请上传正常有效的截图');
					}
					$j += 1;
				}else if(strpos($key,'thumbgif_')!==FALSE) {	//截图gif
					if($val['size'] > 5242880) {	//5M
						parentjs("每张截图应5M以内，请重新选择");
					} else if(!in_array($ext,array('gif'))) {
						parentjs('截图只支持gif格式，请重新选择');
					} else if(getimagesize($val['tmp_name'])===FALSE) {
						parentjs('请上传正常有效的gif版截图');
					}
					$j_g += 1;
				} else if(in_array($key,array('business_pic','identity_pic','right_pic'))) {	//版权资料
					if($val['size'] > 2097152) {	//2M
						parentjs("版权资料每张图大小应2M以内，请重新选择");
					} else if(!in_array($ext,array('png','jpg','jpeg'))) {
						parentjs('版权资料只支持jpg，png格式，请重新选择');
					} else if(getimagesize($val['tmp_name'])===FALSE) {
						parentjs('请上传正常有效的版权资料图片');
					}
					$k += 1;
				}else if(in_array($key,array('new_icon'))){
					if($val['size'] > 1048576) {	//1M
						parentjs("每张应用ICON应在1M以内，请重新选择");
					} else if(!in_array($ext,array('png'))) {
						parentjs('icon只支持png格式，请重新选择');
					} else if(getimagesize($val['tmp_name'])===FALSE) {
						parentjs('请上传正常有效的icon');
					}
					$c += 1;
				}else if(in_array($key,array('new_icon_no'))){
					if($val['size'] > 1048576) {	//1M
						parentjs("每张应用ICON应在1M以内，请重新选择");
					} else if(!in_array($ext,array('png'))) {
						parentjs('icon只支持png格式，请重新选择');
					} else if(getimagesize($val['tmp_name'])===FALSE) {
						parentjs('请上传正常有效的icon');
					}
				}else if(in_array($key,array('new_icon_gif'))){
					if($val['size'] > 5242880) {	//5M
						parentjs("每张应用ICON动图应在5M以内，请重新选择");
					} else if(!in_array($ext,array('gif'))) {
						parentjs('icon动图只支持gif格式，请重新选择');
					} else if(getimagesize($val['tmp_name'])===FALSE) {
						parentjs('请上传正常有效的icon动图');
					}
				}else if(in_array($key,array('video_pic'))){
					if($val['size'] > 2097152) {	//2M
						parentjs("视频图片大小应2M以内，请重新选择");
					} else if(!in_array($ext,array('png','jpg','jpeg'))) {
						parentjs('视频图片只支持jpg，png格式，请重新选择');
					} else if(getimagesize($val['tmp_name'])===FALSE) {
						parentjs('请上传正常有效的视频图片');
					}
				}else if(in_array($key,array('video_pic_new'))){
					// if($val['size'] > 2097152) {	//2M
					// 	parentjs("视频图片大小应2M以内，请重新选择");
					// } else 
					if(!in_array($ext,array('png','jpg','jpeg'))) {
						parentjs('顶部配图只支持jpg，png格式，请重新选择');
					} else if(getimagesize($val['tmp_name'])===FALSE) {
						parentjs('请上传正常有效的顶部配图');
					}
				}else if($key=='record_url'){
					$record_url_type = $ext;
					if($val['size'] > 20971520) {	//20M
						parentjs("游戏备案证明材料大小应20M以内，请重新选择");
					} else if(!in_array($ext,array('png','jpg','pdf'))) {
						parentjs('游戏备案证明材料只支持jpg，png,pdf格式，请重新选择');
					} 
				}else if($key=='publication_url'){
					$publication_url_type = $ext;
					if($val['size'] > 20971520) {	//20M
						parentjs("游戏出版证明材料大小应20M以内，请重新选择");
					} else if(!in_array($ext,array('png','jpg','pdf'))) {
						parentjs('游戏出版证明材料只支持jpg，png,pdf格式，请重新选择');
					}
				}
			}
		}

		$_SESSION['apk_info'.$mark]['get_type'] = $_GET['type'];
		if(in_array($_SESSION['apk_info'.$mark]['get_type'],array('mod_line','mod_tmp','update','update_apk'))) {	//修改"描述(已上架)",修改"未通过"
		//$_SESSION['apk_form'.$mark]['thumb']['thumb_5']['url']
			//截图
			for($z=1;$z<=5;$z++) {
				if($_POST["thumb_{$z}_del"]) {
				    unset($_SESSION['apk_form'.$mark]['thumb']["thumb_{$z}"]);
				    unset($_SESSION['apk_info'.$mark]['thumb']["thumb_{$z}"]);
				    unset($_SESSION['apk_info'.$mark]['cj_data']['cj_thumb']["thumb_{$z}"]);
				}
			}
			$j += count($_SESSION['apk_info'.$mark]['thumb']);
			//gif版截图
			for($z=1;$z<=5;$z++) {
				if($_POST["thumbgif_{$z}_del"]) {
				    unset($_SESSION['apk_form'.$mark]['thumbgif']["thumbgif_{$z}"]);
				    unset($_SESSION['apk_info'.$mark]['thumbgif']["thumbgif_{$z}"]);
				}
			}
			$j_g += count($_SESSION['apk_info'.$mark]['thumbgif']);
			//视频图片
			if($_POST['video_pic_del'])
				unset($_SESSION['apk_form'.$mark]['video_pic']);
			//视频图片
			if($_POST['video_pic_new_del'])
				unset($_SESSION['apk_form'.$mark]['video_pic_new']);
			//版权图
			foreach(array('business_pic','identity_pic','right_pic') as $key=>$val) {
				if($_POST["{$val}_del"]) unset($_SESSION['apk_info'.$mark]['copyright_img'][$val]);
			}
			$k += count($_SESSION['apk_info'.$mark]['copyright_img']);
		}
		//采集截图
		if($_GET['from_type'] == 'cj_add'){
			for($z=1;$z<=5;$z++) {
				if($_POST["thumb_{$z}_del"]) {
				    unset($_SESSION['apk_form'.$mark]['thumb']["thumb_{$z}"]);
				    unset($_SESSION['apk_info'.$mark]['thumb']["thumb_{$z}"]);
				    unset($_SESSION['apk_info'.$mark]['cj_data']['cj_thumb']["thumb_{$z}"]);
				}
			}
			$j += count($_SESSION['apk_info'.$mark]['cj_data']['cj_thumb']);
		}
		if(!$_POST['drafts']) {
			if($j < 4) parentjs("上传的截图数量应4-5张，您上传了{$j}张");
			if(!in_array($_GET['type'],array('new','update')) && strpos(','.$_SESSION['copyright_id'].',', ','.$_POST['category_id'].',')!==FALSE) {	//新软件,软件升级后台不需要版权资料
				if($k < 3) parentjs("版权资料应上传3张图，您上传了{$k}张");
			}
		}
	 
		//截图和版权icon处理
		$files = array();
		$fileicon = array();
		$files_copyright = array(); 
		foreach($_FILES as $key=>$val) {
				$files[$key] = '@'.$val['tmp_name'];	
				$fileicon[$key] = '@'.$val['tmp_name'];
				
		}
		if($fileicon['new_icon'] || $fileicon['cj_new_icon'] ){
			if(isset($_POST['from_icon']) && $_POST['from_icon'] ==2){
				$vals = array_merge($fileicon, array('do'=>'new_icon', 'id'=>$_SESSION['admin']['admin_id'],'softid'=>$_SESSION['apk_info'.$mark]['softid'],'package'=>$_SESSION['apk_info'.$mark]['package'],'from_icon'=>$_POST['from_icon']));
			}else if($_GET['from_type'] == 'cj_add'){
				$vals = array_merge($fileicon, array('do'=>'new_icon', 'id'=>$_SESSION['admin']['admin_id'],'softid'=>$_SESSION['apk_info'.$mark]['softid'],'package'=>$_SESSION['apk_info'.$mark]['package']));
			}else{
				$image_file = getimagesize(substr($fileicon['new_icon'],1));
				if($image_file[0] != 512 && $image_file[1] != 512){
					parentjs('支持png格式，图标尺寸512×512，该图标用来在安智市场中显示，需与安装包中的图标相同。');
				}
				$vals = array_merge($fileicon, array('do'=>'new_icon', 'id'=>$_SESSION['admin']['admin_id'],'softid'=>$_SESSION['apk_info'.$mark]['softid'],'package'=>$_SESSION['apk_info'.$mark]['package']));
			}			
			$arr = $apkmodel -> _http_post($vals);
			$arr = json_decode($arr, true);
			if(!$arr || $arr['code']!=1) {
				parentjs($arr['msg'] ? $arr['msg'] : 'ICON处理时通讯失败，请重试');
			} else {
				$_SESSION['apk_info'.$mark] = array_merge($_SESSION['apk_info'.$mark], $arr['ret']);
				$apkmodel -> writelog('dummy_new_icon.txt',print_r($_SESSION['apk_info'.$mark],true));
			} 
			unset($files['new_icon'],$files['cj_new_icon']);
		}
		if($fileicon['new_icon_no']){		
			$image_file = getimagesize(substr($fileicon['new_icon_no'],1));
			if($image_file[0] != 512 && $image_file[1] != 512){
				parentjs('支持png格式，图标尺寸512×512，该图标用来在安智市场中显示，需与安装包中的图标相同。');
			}
			$vals = array('do'=>'new_icon_no', 'id'=>$_SESSION['admin']['admin_id'],'softid'=>$_SESSION['apk_info'.$mark]['softid'],'package'=>$_SESSION['apk_info'.$mark]['package'],'new_icon_no'=>$fileicon['new_icon_no']);					
			$arr = $apkmodel -> _http_post($vals);
			$arr = json_decode($arr, true);
			if(!$arr || $arr['code']!=1) {
				parentjs($arr['msg'] ? $arr['msg'] : '无角标ICON处理时通讯失败，请重试');
			} else {
				$_SESSION['apk_info'.$mark] = array_merge($_SESSION['apk_info'.$mark], $arr['ret']);
				$apkmodel -> writelog('dummy_new_icon_no.txt',print_r($_SESSION['apk_info'.$mark],true));
			} 
			unset($files['new_icon_no']);
		}
		if($_POST['new_icon_gif_del']==1){
			if($_SESSION['apk_info'.$mark]['get_type'] == 'mod_tmp'){
				$sql = "UPDATE sj_icon_tmp SET iconurl_gif='',iconurl_gif_160='' WHERE tmpid='{$_SESSION['apk_info'.$mark]['tmp_id']}'";   
        		$apkmodel->query($sql);
			}else{
				$sql = "UPDATE sj_icon SET iconurl_gif='',iconurl_gif_160='' WHERE softid='{$_SESSION['apk_info'.$mark]['softid']}'";   
        		$apkmodel->query($sql);
			}
		}else{
			if($fileicon['new_icon_gif']){		
				$image_file = getimagesize(substr($fileicon['new_icon_gif'],1));
				if($image_file[0] != 512 && $image_file[1] != 512){
					parentjs('支持gif格式，图标尺寸512×512。');
				}
				$vals = array('do'=>'new_icon_gif', 'id'=>$_SESSION['admin']['admin_id'],'softid'=>$_SESSION['apk_info'.$mark]['softid'],'package'=>$_SESSION['apk_info'.$mark]['package'],'new_icon_gif'=>$fileicon['new_icon_gif']);					
				$arr = $apkmodel -> _http_post($vals);
				$arr = json_decode($arr, true);
				if(!$arr || $arr['code']!=1) {
					parentjs($arr['msg'] ? $arr['msg'] : 'ICON动图处理时通讯失败，请重试');
				} else {
					$_SESSION['apk_info'.$mark] = array_merge($_SESSION['apk_info'.$mark], $arr['ret']);
					$apkmodel -> writelog('dummy_new_icon_gif.txt',print_r($_SESSION['apk_info'.$mark],true));
				} 
				unset($files['new_icon_gif']);
			}
		}
		//视频图片上传
		if($fileicon['video_pic']){
			$vals = array('do'=>'video_pic', 'id'=>$_SESSION['admin']['admin_id'],'video_pic'=>$fileicon['video_pic']);
			$arr = $apkmodel -> _http_post($vals);
			$arr = json_decode($arr, true);
			if(!$arr || $arr['code']!=1) {
				parentjs($arr['msg'] ? $arr['msg'] : '视频图片处理时通讯失败，请重试');
			} else {
				$_SESSION['apk_info'.$mark] = array_merge($_SESSION['apk_info'.$mark], $arr['ret']);
				$apkmodel -> writelog('dummy_video_pic.txt',print_r($_SESSION['apk_info'.$mark],true));
			}
			unset($files['video_pic']);
		}
		//视频图片上传
		if($fileicon['video_pic_new']){
			$image_file = getimagesize(substr($fileicon['video_pic_new'],1));
			if($image_file[0] != 1256 && $image_file[1] != 706){
				parentjs('顶部配图：1256×706px，格式jpg、png');
			}
			$vals = array('do'=>'video_pic_new', 'id'=>$_SESSION['admin']['admin_id'],'video_pic_new'=>$fileicon['video_pic_new']);
			$arr = $apkmodel -> _http_post($vals);
			// var_dump($arr);die;
			$arr = json_decode($arr, true);
			if(!$arr || $arr['code']!=1) {
				parentjs($arr['msg'] ? $arr['msg'] : '视频图片处理时通讯失败，请重试');
			} else {
				$_SESSION['apk_info'.$mark] = array_merge($_SESSION['apk_info'.$mark], $arr['ret']);
				$apkmodel -> writelog('dummy_video_pic.txt',print_r($_SESSION['apk_info'.$mark],true));
			}
			unset($files['video_pic_new']);
		}
		//备案材料上传

		$beian = $this->beian_process($_FILES,$apkmodel,$record_url_type,$publication_url_type);
		unset($files['record_url']);
		unset($files['publication_url']);
		if($files) {
			$vals = array_merge($files, array('do'=>'thumb_admin', 'id'=>$_SESSION['admin']['admin_id']));
			$arr = $apkmodel -> _http_post($vals);
			$arr = json_decode($arr, true);
			if(!$arr || $arr['code']!=1) {
				parentjs($arr['msg'] ? $arr['msg'] : '截图处理时通讯失败，请重试');
			} else {
				$_SESSION['apk_info'.$mark] = array_merge($_SESSION['apk_info'.$mark], $arr['ret']);
				$apkmodel -> writelog('dummy_ret.txt',print_r($_SESSION['apk_info'.$mark],true));
			}
			if($files['thumbgif_1'] || $files['thumbgif_2'] || $files['thumbgif_3'] || $files['thumbgif_4'] || $files['thumbgif_5']){
				$vals = array_merge($files, array('do'=>'thumbgif_admin', 'id'=>$_SESSION['admin']['admin_id']));
				$arr = $apkmodel -> _http_post($vals);
				$arr = json_decode($arr, true);
				if(!$arr || $arr['code']!=1) {
					parentjs($arr['msg'] ? $arr['msg'] : 'gif截图处理时通讯失败，请重试');
				} else {
					$_SESSION['apk_info'.$mark] = array_merge($_SESSION['apk_info'.$mark], $arr['ret']);
					$apkmodel -> writelog('dummy_ret.txt',print_r($_SESSION['apk_info'.$mark],true));
				}
			}
		}
		//采集截图处理
		if($_GET['from_type'] == 'cj_add' && $_SESSION['apk_info'.$mark]['cj_data']['cj_thumb']){
			$cj_thumb = json_encode($_SESSION['apk_info'.$mark]['cj_data']['cj_thumb']);
			$vals = array(
				'do'=>'cj_thumb',
				'id'=>$_SESSION['admin']['admin_id'],
				'cj_thumb_data' => $cj_thumb
			); 
			$arr = $apkmodel -> _http_post($vals);
			$arr = json_decode($arr, true);
			if(!$arr || $arr['code']!=1) {
				parentjs($arr['msg'] ? $arr['msg'] : '采集截图处理时通讯失败，请重试');
			} else {
				$_SESSION['apk_info'.$mark] = array_merge($_SESSION['apk_info'.$mark], $arr['ret']);
				$apkmodel -> writelog('cj_dummy_ret.txt',print_r($_SESSION['apk_info'.$mark],true));
			}	
		}
		if($old_softname!=$_POST['softname']){
			//修改软件名称同步用户中心
			if(!relevance_softname($_SESSION['apk_info'.$mark]['packagename'],array('softname'=>$_POST['softname']))){
				parentjs('修改软件名称同步到用户中心失败，请重试');
			}
		}
		//数据入库
		if($_POST['operating']) $_POST['operating'] = json_encode($_POST['operating']);
		$_SESSION['apk_info'.$mark]['user_ip'] = $_SERVER['REMOTE_ADDR'];
		$do_datadb = $_SESSION['apk_info'.$mark]['get_type'] == 'mod_tmp' ? 'data_db' : 'data_db_admin';
		unset($vals);

		$vals = array_merge($_SESSION['apk_info'.$mark], $_POST, array('do'=>$do_datadb, 'admin_id'=>$_SESSION['admin']['admin_id']));
		unset($vals['id']);	//去掉开发者id
		$vals['intro'] =  preg_replace('/((\n)+(\s)*)/i','',$vals['intro']);
		$vals['intro'] =  preg_replace('/\s(?=\s)/', '', $vals['intro']);		
		$vals['thumb'] = json_encode($vals['thumb']);
		$vals['thumbgif'] = json_encode($vals['thumbgif']);
		$vals['copyright_img'] = json_encode($vals['copyright_img']);
		$vals['update_type'] = 1;	//后台
		if(isset($vals['permission']))
			$vals['permission'] = json_encode($vals['permission']);
		if(isset($vals['library']))
			$vals['library'] = json_encode($vals['library']);
		if(isset($vals['library-optional']))
			$vals['library-optional'] = json_encode($vals['library-optional']);
		//调错日志,开始
		if(!empty($vals['record_type']) && $vals['record_type']<1 && $_GET['type'] != 'update_apk') {
			$apkmodel -> writelog('record_type_admin.log',date('Y-m-d H:i:s')."\n".print_r($vals,true)."\n\n",FILE_APPEND);
			parentjs('数据发生异常，请联系支持人员(1)');
		}
		//调错日志,结束
		if($_GET['from_type'] == 'cj_add'){
			$vals['extra'] = 'cj_add';
		}else if($_GET['from_type'] == 'cj_update'){
			$vals['extra'] = 'cj_update';
		}else{
			$vals['extra'] = $_GET['type'];
		}

		$vals['cj_data'] = json_encode($vals['cj_data']);
		$arr = $apkmodel -> _http_post($vals);
		$arr = json_decode($arr, true);
		if(!$arr || $arr['code']!=1) {
			parentjs($arr['msg'] ? $arr['msg'] : '数据操作时通讯失败，请重试');
		} else {
			//替包时更新baidu_docid_map
			if($vals['get_type']=='update_apk'&&$vals['apk']){
				$apkmodel->table('baidu_docid_map')-> where("softid={$vals['softid']}") -> save(array('status'=>2));
			}
			//sdk渠道游戏升级插入
			$sdkchannel = D('sendNum.SdkChannel');
			if($vals['extra']=="update"){
				$add_result = array('0'=>array('package'=>$vals['package']));
				$soft_record_type = 3;
				$sdkchannel->save_channel_game_sdk($add_result,1,$soft_record_type);
			}
			if($vals['extra']=="update"||$vals['extra']=="cj_update"){
				if(isset($arr['softid'])&&!empty($arr['softid'])){
					$model = M('');
					$icon_info = $model->table('sj_soft_file')->where(array('softid'=>$arr['softid'],'package_status'=>1))->field('filesize,iconurl_125')->order('id desc')->find();
					//推送
					$push_data['package'] = $vals['packagename'];
					$push_data['softname'] = $vals['softname'];
					$push_data['softid'] = $arr['softid'];
					$push_data['version'] = $vals['versionName'];
					$push_data['version_code'] = $vals['versionCode'];
					$push_data['update_content'] = $vals['update_content'];
					$push_data['review_time'] = time();
					$push_data['filesize'] = $icon_info['filesize'];
					$push_data['iconurl'] = 'http://img3.anzhi.com'.$icon_info['iconurl_125'];
					$push_data['end_time'] = time()+(86400*3);
					push_soft_update_msg($push_data);
				}
			}
			//icon使用安装包自带
			if($_POST['my_icon']){
				$this->save_new_icon($_SESSION['apk_info'.$mark]['tmp_id'],$_SESSION['apk_info'.$mark]['softid'],$_GET['type']);
			}
			foreach($_SESSION as $key=>$val) {
				if(preg_match("/^apk_/i",$key)) {
					unset($_SESSION[$key.$mark]);
				}
			}
			//如果是电子书通过
			$Tags = D('Sj.Tags');
			if(in_array($vals['category_id'],$book_categoryid) && $vals['tags'] && $vals['package']){
				$Tags -> del_dev_tag($vals['package']);
				if(!empty($vals['tags'])){
					$Tags -> add_package_tags($vals['package'],$vals['tags']);
				}	
			}else{
				if(!empty($_POST['custom_tags'])){
					$Tags ->  save_tag_history($vals['package'],$_POST['custom_tags']);
				}	
				if(!empty($_POST['cj_tag_str']) || !empty($_POST['hot_tag_str'])){
					$tag = array(
						'2'=>implode(',',$cj_tag_arr),
						'3'=>implode(',',$hot_tag_arr),
					);
					$Tags -> save_cj_hot_tag($vals['package'],$tag);
				}
				if($vals['get_type'] == 'update' || $vals['get_type'] == 'update_apk'||$vals['get_type']=='mod_tmp'){
					$Tags -> del_dev_tag($vals['package']);
					if(!empty($_POST['cj_tag_str']) || !empty($_POST['hot_tag_str']) || !empty($_POST['custom_tags']) ){
						$tag_str = '';
						if($_POST['custom_tags']){
							$tag_str .= $_POST['custom_tags'].",";
						}
						if($_POST['cj_tag_str']){
							$tag_str .= $_POST['cj_tag_str'].",";
						}
						if($_POST['hot_tag_str']){
							$tag_str .= $_POST['hot_tag_str'];
						}
						$Tags -> add_package_tags($vals['package'],$tag_str);
					}
				}
			}
			if($vals['get_type'] == 'update' && $vals['total_downloaded'] >= 50000000){
				$emailmodel = D("Dev.Sendemail");
				if($_GET['from_type'] == 'cj_update'){
					$str = "采集更新包名:";
				}else{
					$str = "更新包名:";
				}
				$emailmodel -> realsend('linhongqing@anzhi.com','linhongqing@anzhi.com','下载量大于5000万',$str.$vals['package']);
			}
			//备案材料保存
			if(count($beian)>0){
				if($_POST['is_not_soft_whitelist']){
					if($publication_config=$apkmodel->table('sj_soft_publication_config')->where(array('package'=>$vals['package'],'status'=>1))->find()){
						$apkmodel->table('sj_soft_publication_config')->where(array('package'=>$vals['package'],'status'=>1))->save(array('publication_num'=>$beian['publication_num'],'update_tm'=>time()));
					}else{
						if($beian['publication_num']){
							$apkmodel->table('sj_soft_publication_config')->add(array('admin_id'=>$_SESSION['admin']['admin_id'],'create_tm'=>time(),'package'=>$vals['package'],'publication_num'=>$beian['publication_num']));
						}
					}
				}else{
					if($_POST['record_url_del']) $beian['record_url'] = '';
					if($_POST['publication_url_del']) $beian['publication_url'] = '';
					$apkmodel->table('yx_product')->where(array('package'=>$vals['package'],'del'=>0))->save($beian);
				}
				
			}
			$this->confirm_log($vals);
			if($_GET['from_type'] == 'cj_add' && $_GET['drafts'] == 2){
				//入库，并添加至“更新采集”。入库后的软件，添加至“版本更新待采集
				// 远程调用CollectionAction.class控制器的add_more_standby操作方法
				unset($_POST);
				$_POST['desc'] = '入库，并添加至“更新采集”';
				$_POST['examine'] = 1;
				$_POST['package'] = $vals['package'].";";
				$_POST['softname'] = $vals['softname'].";";
				$res = R("Caiji.Collection","add_more_standby"); 
			}
			if($_GET['from_type'] == 'cj_add' && $vals['get_type']=='new'){
				if($_GET['from']=='taptap'){
					parentjs('提交成功！',"/index.php/Caiji/Collection/collection_add_audit_taptap");
				}else{
					parentjs('提交成功！',"/index.php/Caiji/Collection/collection_add_audit");
				}
			}else if($_GET['from_type'] == 'cj_update'){
				//增量更新
				$Softaudit = D("Dev.Softaudit");
				$Softaudit -> send_incremental_update($arr['softid'],$vals['package'],$vals['total_downloaded']);
				parentjs('提交成功！',"/index.php/Caiji/Collection/collection_update_audit");
			}else if($vals['get_type']=='new') {	//跳到已上架
				parentjs('提交成功！',"/index.php/Dev/Soft/softlist/package/".$vals['packagename']."/safe/0/");
			} else if($_SESSION['referer']) {
				parentjs('提交成功！',$_SESSION['referer']);
			} else {

				parentjs('提交成功！','/index.php/Dev/Apk/add_new');
			}
			unset($_SESSION['apk_info'.$mark]);
			unset($_SESSION['apk_from'.$mark]);
		}
	}
//代码,结束 ====================================================
}

	public function confirm_log($data){
		$log = '';
		$action = '/index.php/Dev/Apk/confirm';
		if($data['get_type']=='mod_tmp'){
			$log .= "软件管理：修改了包名为{$data['package']},tmp_id为{$data['tmp_id']}为的软件信息";
			$table = 'sj_soft_tmp';
			$id = $data['tmp_id'];
			$type = 'edit';
		}else if($data['get_type']=='mod_line'){
			$log .= "软件管理：修改了包名为{$data['package']},softid为{$data['softid']}为的软件信息";
			$table = 'sj_soft';
			$id = $data['softid'];
			$type = 'edit';
		}
		$this->writelog($log,$table,$id,__ACTION__ ,"",$type);
	}
	//icon替换为包自带
	public function save_new_icon($tmpid,$softid,$type){
		$model = new Model();
		if(in_array($type,array('update_apk','mod_line'))){
			$apk_icon = $model->table('sj_soft_file')->where("softid = '{$softid}' and package_status = 1")->field('iconurl,iconurl_72,iconurl_96,iconurl_125,apk_icon,apk_name')->find();
		}else{
			$apk_icon = $model->table('sj_soft_file_tmp')->where("tmp_id = '{$tmpid}' and package_status = 1")->field('iconurl,iconurl_72,iconurl_96,iconurl_125,apk_icon,apk_name')->order("id desc")->find();
			if(!$apk_icon){
				$apk_icon = $model->table('sj_soft_file')->where("softid = '{$softid}' and package_status = 1")->field('iconurl,iconurl_72,iconurl_96,iconurl_125,apk_icon,apk_name')->find();
			}
		}
		if($apk_icon){
			if(!in_array($type,array('update_apk','mod_line'))){
				$table = 'sj_icon_tmp';
				$where = "tmpid = '{$tmpid}' and status = '1'";
			}else{
				$table = 'sj_icon';
				$where = "softid = '{$softid}' and status = '1'";
			}
			$icon_tmp = $model->table($table)->where($where)->find();
			if($icon_tmp){
				$model->table($table)->where($where)->save(array('status'=>0));
			}
			$add_data = array(
				'package'=>$apk_icon['apk_name'],
				'iconurl'=>$apk_icon['iconurl'],
				'iconurl_72'=>$apk_icon['iconurl_72'],
				'iconurl_96'=>$apk_icon['iconurl_96'],
				'iconurl_125'=>$apk_icon['iconurl_125'],
				'apk_icon'=>$apk_icon['apk_icon'],
				'add_time'=>time(),
				'update_time'=>time(),
				'status'=>1,
				'softid'=>$softid
			);
			if(!in_array($type,array('update_apk','mod_line'))){
				$add_data['tmpid'] = $tmpid;
			}
			$res = $model->table($table)->add($add_data);
			return $res;
		}
		
	}

	public function claim_upload($files,$files_type) {
		$apkmodel = D("Dev.Apk");
		$vals = array(
			'do' => 'claim',
			'static_data' =>'/data/att/m.goapk.com',
		);
		return $apkmodel -> _http_post(array_merge($vals,$files,$files_type));
	}
	public function chk() {	//非法字符检查
	    $model = new Model();
		if($_GET['do'] == 'badword') {			
			if(_checkword($_REQUEST['str'],$model)) {	//对应 dev.anzhi.com/functions.php 中 checkword()
				exit('1');
			} else {
				exit('0');
			}
		}else if($_GET['do'] == 'softname'){
		     $ret = $model->table('sj_soft_whitelist')->where("status='1' AND softname='{$_REQUEST['str']}'")->find();
		     //echo $model->getLastSql();
			 if($ret){
		         if($_GET['packagename'] == $ret['package']){
		             exit('0');
		         }else{
		             exit('1');
		         }
		     }else{
				    exit('0');
			 }		        
		}
	}

	//视频上传
	public function upload_video(){
		//变量初始化
		$ret = array('code' => '0', 'msg' => '未知错误');	//返回上传提示
		//视频检查
		if($_FILES['video']['error'] == 4) {
			$ret['msg'] = '请选择要上传的视频文件！';
			_exit($ret);
		}
		if($_FILES['video']['error'] != 0) {
			$ret['msg'] = '上传失败，错误代码：'.$_FILES['apk']['error'];
			_exit($ret);
		} else if(strtolower(pathinfo($_FILES['video']['name'],PATHINFO_EXTENSION)) != 'mp4') {	//后缀检查
			$ret['msg'] = '上传失败，只允许上传mp4后缀的文件';
			_exit($ret);
		}
		if($_GET['video_num']==2){
			if($_FILES['video']['size'] > 200*1024*1024 ) {
				//50M以下
				$ret['msg'] = '上传失败，只允许上传200M以下的文件';
				_exit($ret);
			}
		}else{
			if($_FILES['video']['size'] > 52428800 ) {
				//50M以下
				$ret['msg'] = '上传失败，只允许上传50M以下的文件';
				_exit($ret);
			}
		}
		
		$video_val = array('do'=>'video','video' => '@'.$_FILES['video']['tmp_name']);
		$apkmodel = D("Dev.Apk");
		$arr = $apkmodel -> _http_post($video_val);
		$arr = json_decode($arr, true);
		if($arr['code']!=1){
			_exit(array('code' => '-2002', 'msg' => '上传视频失败！'));
		}else{
			$show_url = str_replace('/data/att/m.goapk.com',IMG_HOST,$arr['ret']['video']);
			$dst = str_replace('/data/att/m.goapk.com','',$arr['ret']['video']);
			_exit(array('code' => '1', 'msg' => '上传成功！','url'=>$dst,'show_url'=>$show_url));
		}
	}

	public function is_show_beian($package,$mark){
		$model = M('');
		//备案是否显示
		$beian = $model->table('yx_product')->where(array('package' => $package, 'del' => 0))->field('soft_id,record_num,record_url,publication_num,publication_url')->find();
		if($beian){
			$_SESSION['apk_info'.$mark]['show_beian'] = 1;
			$_SESSION['apk_info'.$mark] = array_merge($beian,$_SESSION['apk_info'.$mark]);
		}
		return $beian;
	}

	//备案
	public  function beian_process($file,$apkmodel,$record_url_type,$publication_url_type){
		$beian = array();
		if($file['record_url']||$file['publication_url']){
			list($msec, $sec) = explode(' ', microtime());
			$msec = substr($msec, 2);
			$path = C('BEIAN_PATH');
			if (!file_exists($path)) {
				mkdir($path, 0777,true);
			}
			if($file['record_url']){
				$record_path =  $path.'record_'. $msec . '.' . $record_url_type;
				if (move_uploaded_file($file['record_url']['tmp_name'], $record_path)) {
					$beian['record_url'] = str_replace(UPLOAD_PATH, '', $record_path);
				}
			}
			if($file['publication_url']){
				$publication_path =  $path.'publication_'. $msec . '.' . $publication_url_type;
				if (move_uploaded_file($file['publication_url']['tmp_name'], $publication_path)) {
					$beian['publication_url'] = str_replace(UPLOAD_PATH, '', $publication_path);
				}
			}
		}
		$beian['record_num'] = $_POST['record_num'];
		$beian['publication_num'] = $_POST['publication_num'];
		return $beian;
	}
}


//session中apk_info,apk_form处理
function sess_adjust($mark='') {    
	$apkmodel = D("Dev.Apk");
	//原截图,版权等修改为可显示
	if(in_array($_GET['type'], array('mod_line','mod_tmp','update','update_apk'))) {
		//显示的apk信息
		$_SESSION['apk_info'.$mark]['_iconurl'] = IMG_HOST.($_SESSION['apk_info'.$mark]['iconurl_72'] ? $_SESSION['apk_info'.$mark]['iconurl_72'] : $_SESSION['apk_info'.$mark]['iconurl']);
		$_SESSION['apk_info'.$mark]['versionCode'] = $_SESSION['apk_info'.$mark]['versionCode'] ? $_SESSION['apk_info'.$mark]['versionCode'] : $_SESSION['apk_info'.$mark]['version_code'];
		$_SESSION['apk_info'.$mark]['filesize2'] = size_conv($_SESSION['apk_info'.$mark]['filesize']);
		$_SESSION['apk_info'.$mark]['versionName'] = $_SESSION['apk_info'.$mark]['versionName'] ? $_SESSION['apk_info'.$mark]['versionName'] : $_SESSION['apk_info'.$mark]['version'];
		$_SESSION['apk_info'.$mark]['packagename'] = $_SESSION['apk_info'.$mark]['packagename'] ? $_SESSION['apk_info'.$mark]['packagename'] : $_SESSION['apk_info'.$mark]['package'];

		//原截图处理
		$tmp_thumb = array();
		if(!$_SESSION['apk_info'.$mark]['thumb_data']){
				$_SESSION['apk_info'.$mark]['thumb_data'] = $_SESSION['apk_info'.$mark]['thumb'];
		}
		foreach($_SESSION['apk_info'.$mark]['thumb_data'] as $key=>$val) {
			if(is_numeric($key)) {
				$_k = $key+1;
				$_k = "thumb_{$_k}";
				$tmp_thumb[$_k] = $val;
			}
		}
		
		$_SESSION['apk_info'.$mark]['thumb'] = $tmp_thumb;
		unset($tmp_thumb);
		//原gif截图处理
		$tmp_thumbgif = array();
		if(!$_SESSION['apk_info'.$mark]['thumbgif_data']){
			$_SESSION['apk_info'.$mark]['thumbgif_data'] = $_SESSION['apk_info'.$mark]['thumbgif'];
		}
		foreach($_SESSION['apk_info'.$mark]['thumbgif_data'] as $key=>$val) {
			if(is_numeric($key)) {
				$_k = $key+1;
				$_k = "thumbgif_{$_k}";
				$tmp_thumbgif[$_k] = $val;
			}
		}
		
		$_SESSION['apk_info'.$mark]['thumbgif'] = $tmp_thumbgif;
		unset($tmp_thumbgif);
		//原版权图处理
		foreach(array('business_pic','identity_pic','right_pic') as $key=>$val) {
			$_SESSION['apk_info'.$mark]['copyright_img'][$val] = $_SESSION['apk_info'.$mark][$val];
		}

		//表单信息专用
		$_SESSION['apk_form'.$mark] = $_SESSION['apk_info'.$mark];
		$_SESSION['apk_form'.$mark]['soft_type'] = $apkmodel -> getTopCategory($_SESSION['apk_form'.$mark]['category_id']);
		if($_SESSION['apk_form'.$mark]['thumb']) {
			foreach($_SESSION['apk_form'.$mark]['thumb'] as $key=>$val) {
				if($val['url']) $_SESSION['apk_form'.$mark]['thumb'][$key]['url'] = IMG_HOST.$val['url'];
			}
		}
		if($_SESSION['apk_form'.$mark]['thumbgif']) {
			foreach($_SESSION['apk_form'.$mark]['thumbgif'] as $key=>$val) {
				if($val['image_raw']) $_SESSION['apk_form'.$mark]['thumbgif'][$key]['url'] = IMG_HOST.$val['image_raw'];
			}
		}
		$_SESSION['apk_form'.$mark]['copyright_img'] = array();
		foreach(array('identity_pic','right_pic','business_pic') as $key=>$val) {
			if($_SESSION['apk_form'.$mark][$val]) $_SESSION['apk_form'.$mark]['copyright_img'][$val] = IMG_HOST.$_SESSION['apk_form'.$mark][$val];
		}
		//版权资料检查,开始
		if($_SESSION['apk_form'.$mark]['copyright_img']) {
			foreach($_SESSION['apk_form'.$mark]['copyright_img'] as $key=>$val) {
				if(!$val) unset($_SESSION['apk_form'.$mark]['copyright_img'][$key]);
			}
		}
		//版权资料检查,结束

		$_SESSION['apk_form'.$mark]['thumb_js'] = json_encode(array_keys($_SESSION['apk_form'.$mark]['thumb']));
		$_SESSION['apk_form'.$mark]['thumbgif_js'] = json_encode(array_keys($_SESSION['apk_form'.$mark]['thumbgif']));
		$_SESSION['apk_form'.$mark]['copyright_img_js'] = json_encode(array_keys($_SESSION['apk_form'.$mark]['copyright_img']));

		//分类信息处理
		$_SESSION['apk_form'.$mark]['category_topid'] = '-1';
		$category_id = intval(substr($_SESSION['apk_form'.$mark]['category_id'],1));
		foreach(array_keys($_SESSION['category_arr']) as $val) {
			if(in_array($category_id,$_SESSION['category_arr'][$val])) {
				$_SESSION['apk_form'.$mark]['category_topid'] = $val;
				break;
			}
		}
		unset($category_id);

		//关键词处理
		if($_SESSION['apk_form'.$mark]['tags']) {
			$arr1 = explode(',',$_SESSION['apk_form'.$mark]['tags']);
			$arr2 = explode(' ',$_SESSION['apk_form'.$mark]['tags']);
			if(count($arr1)>count($arr2)){
				$arr = $arr1;
			}else{
				$arr = $arr2;
			}
			if($arr) {
				foreach($arr as $k=>$v) {
					if(!$v) unset($arr[$k]);
				}
			}
			$_SESSION['apk_form'.$mark]['update_type'] = 1 ;//后台管理员编辑开发者名称
			$_SESSION['apk_form'.$mark]['tags_split'] = array_values($arr);
		}
	}
}

//json返回
function _exit($arr) {
	$apkmodel = D("Dev.Apk");
	$apkmodel -> writelog('exit_admin.txt',print_r($arr,true));
	exit(json_encode($arr));
}


//同 dev.anzhi.com/add_new_confirm.php 中 parentjs()
function parentjs($msg, $url = FALSE) {
	header("Content-Type:text/html;charset=utf-8");
	$jump = '';
	if($url) {
		$jump = <<<EOT
parent.location.href = '{$url}';
if(window.ActiveXObject) window.event.returnValue = false;
EOT;
	}

	echo <<<EOT
<script language="javascript">
parent.alert("{$msg}");
{$jump}
</script>
EOT;

	exit;
}

//非法字符检查
function _checkword($str,$model) {
	$flag  = true; 
	if(!empty($str)){
		if(1 || empty($_SESSION['soft_badword'])) {
			$result = $model->table('pu_config')->where("status='1' AND config_type='soft_badword'")->find();
			$_SESSION['soft_badword'] = $result['configcontent'];
		}
		$wordarray = array();
		$wordarray = explode('|',$_SESSION['soft_badword']);
		$flag = false; 
	    foreach($wordarray as $v2){
		 if(strstr($str,$v2)){
			$flag  = true; 
			break; 	 
	   } 
	  }
	} else {
		return false;
	}
	return $flag;
}

//文件大小转换
function size_conv($size) {
	if($size < 1024) {	//KB以下
		return $size.'B';
	} else if($size < 1024 * 1024) {	//M以下
		return round($size / 1024, 2).'KB';	//单位KB
	} else {
		return round($size / (1024*1024), 2).'MB';	//单位KB
	}
}

//包名检查
function packagename_chk ($val)
{
    $model = new Model();
    if (empty($GLOBALS['soft_shieldpackagename'])) {
        $shield_result = $model->table('pu_config')->where("status='1' AND config_type='soft_shieldpackagename'")->find();

        $shield = $shield_result['configcontent'];
        $shield_str = substr($shield, 0, - 1);
        $GLOBALS['soft_shieldpackagename'] = explode(';', $shield_str);
    }

    if (in_array($val, $GLOBALS['soft_shieldpackagename'])) {
        return '软件包名不规范，未通过检查';
    }

    static $key_words = array ('abstract', 'assert', 'boolean', 'break', 'byte', 'case', 'catch', 'char', 'class', 'continue', 'default', 'do', 'double', 'else', 'enum', 'extends', 'final', 'finally', 'float', 'for', 'if', 'implements', 'import', 'instanceof', 'int', 'interface', 'long', 'native', 'new', 'package', 'private', 'protected', 'public', 'return', 'strictfp', 'short', 'static', 'super', 'switch', 'synchronized', 'this', 'throw', 'throws', 'transient', 'try', 'void', 'volatile', 'while', 'abstract', 'assert', 'boolean', 'break', 'byte', 'case', 'catch', 'char', 'class', 'continue', 'default', 'do', 'double', 'else', 'enum', 'extends', 'final', 'finally', 'float', 'for', 'if', 'implements', 'import', 'instanceof', 'int', 'interface', 'long', 'native', 'new', 'package', 'private', 'protected', 'public', 'return', 'strictfp', 'short', 'static', 'super', 'switch', 'synchronized', 'this', 'throw', 'throws', 'transient', 'try', 'void', 'volatile', 'while' );

    static $reg = '/^([a-z_\$][a-z_\$0-9]*\.)*[a-z_\$][a-z_\$0-9]*$/i';
    $reg_key_words_prefix = '/^(' . implode('|', $key_words) . ')\./';
    $reg_key_words_infix = '/\.(' . implode('|', $key_words) . ')\./';
    $reg_key_words_surfix = '/\.(' . implode('|', $key_words) . ')$/';

    if (! preg_match($reg, $val)) {
        return '软件包名不规范，未通过检查(1)';
    }
    if (preg_match($reg_key_words_prefix, $val)) {
        return '软件包名不规范，未通过检查(2)';
    }
    if (preg_match($reg_key_words_infix, $val)) {
        return '软件包名不规范，未通过检查(3)';
    }
    if (preg_match($reg_key_words_surfix, $val)) {
        return '软件包名不规范，未通过检查(4)';
    }

    return 'ok';
}

