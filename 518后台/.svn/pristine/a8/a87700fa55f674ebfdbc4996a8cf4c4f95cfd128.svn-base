<?php

/**
  *  author:wangweitao
  *  desc:Acer合作方主题APK管理
  **/

class ThemeAction extends CommonAction {
    
	//主题列表
    function list_theme() {
	    $third_theme_model = M("third_theme");
		//$map['status'] = array('eq',1);
		$third_theme_total = $third_theme_model -> field('*') -> select();
		import("@.ORG.Page");
		$Page = new Page(count($third_theme_total), 10);
		$third_theme_list = $third_theme_model -> order("theme_id DESC") -> limit($Page->firstRow . ',' . $Page->listRows) -> select();
		$this -> assign("third_theme_list",$third_theme_list);
		$Page->setConfig('header', '篇记录');
        $Page->setConfig('first', '<<');
        $Page->setConfig('last', '>>');
        $show = $Page->show();
        $this->assign("page", $show);
        $this -> display('list_theme');
    }
    
	//主题添加显示
    function add_theme() {
       $this -> display('add_theme');
    }
	
	//主题添加处理
	function add_theme_do(){
		import("@.ORG.Input");
		$Input = Input::getInstance();
		$data['theme_name'] = $Input->REQUEST('theme_name');
		$data['desc']       = $Input->REQUEST('desc');
		$data['created_at'] = time();
		$data['update_time']= time();
		$data['status']     = 1;
		if(empty($data['theme_name'])){
		   $this -> error("请输入主题名称！");
		}
		if(empty($data['desc'])){
		   $this -> error("请输入主题描述！");
		}
		$third_theme_model = M("third_theme");
		
		$apkinfo = $this->_apkupload();
		$iconinfo = $this->_iconupload($apkinfo['package']);
		
		$data['apk_url']  = $apkinfo['apk_url'];
		$data['acertheme']= $apkinfo['acertheme'];
		$data['package']  = $apkinfo['package'];
		$data['filesize'] = $apkinfo['filesize'];
		$data['version_name'] = $apkinfo['version_name'];
		$data['version_code'] = $apkinfo['version_code'];
		$data['min_firmware'] = $apkinfo['min_firmware'];
		$data['icon_url']     = $iconinfo['icon_url'];
		
		$result = $third_theme_model -> add($data); 
		if($result){
			$this -> writelog('添加主题上传apk'.$data['theme_name'],'sj_third_theme',$result,__ACTION__ ,"","add");
			$this -> assign('jumpUrl','/index.php/Coop/Theme/list_theme');
			$this -> success("上传主题成功");	
		}
	}
	
	//主题删除
    function del_theme(){
    }
	
	//主题编辑
    function edit_theme(){
	    import("@.ORG.Input");
		$Input = Input::getInstance();
		$theme_id = $Input->REQUEST('theme_id');
		
		$third_theme_model = M("third_theme");
		$third_themes = $third_theme_model -> where("theme_id = '$theme_id'") -> select();
		foreach($third_themes as $third_theme){
		    $this ->assign('theme',$third_theme);
		}
		$this -> display('edit_theme');
    }
	
	//主题编辑处理
    function edit_theme_do(){
	    import("@.ORG.Input");
		$Input = Input::getInstance();
		$data['theme_id']    = $Input->REQUEST('theme_id');
		$data['theme_name']  = $Input->REQUEST('theme_name');
		$data['status']      = $Input->REQUEST('status');
		$data['update_time'] = time(); 
		
		$third_theme_model = M("third_theme");

		$log = $this->logcheck(array('theme_id'=>$data['theme_id']),'sj_third_theme',$data,$third_theme_model);
		$result = $third_theme_model->save($data);
		if($result){
			$this->writelog("Acer推广-主题列表管理编辑theme_id为'".$data['theme_id']."'".$log,'sj_third_theme',$data['theme_id'],__ACTION__ ,"","edit");
		    //$this -> writelog('主题编辑theme_id:'.$data['theme_id'].'名为:'.$data['theme_name']);
		    $this -> assign('jumpUrl','/index.php/Coop/Theme/list_theme');
		    $this -> success("主题编辑已经成功");
		}
    }
	  
	//icon文件上传
	private function _iconupload($package){
		
		$uploaddir  = '/tmp/icon/';
        $uploadfile = $uploaddir . basename($_FILES['icon']['name']);	
		$newfile    = array();

		if(!empty($_FILES['icon'])){
		    if (!preg_match("/\w+\.\w+/",$_FILES['icon']['name'])) {
                 $this -> error('文件名只能为英文数字！');
				 //echo '上传文件过大！';
				 return ;    
                }
		      if($_FILES['icon']['size'] >= 5*1024){
			     $this -> error('上传icon文件不大于5KB！');
				 //echo '上传文件过大！';
				 return ;
			  }else{
			     if(!is_dir($uploaddir)) {
					// 检查目录是否编码后的
					if(is_dir(base64_decode($uploaddir))) {
						$uploaddir	=	base64_decode($uploaddir);
					}else{
						// 尝试创建目录
						if(!mkdir($uploaddir)){
							$this -> error('上传目录'.$uploaddir.'不存在!');
							//echo '上传目录'.$uploaddir.'不存在!';
							return ;
						}
					}
				}else {
					if(!is_writeable($uploaddir)) {
						$this -> error('上传目录'.$uploaddir.'不可写!');
						//echo '上传目录'.$uploaddir.'不可写!';
						return ;
					}
                }
				
				if(!in_array($this->getExt($_FILES['icon']['name']),array('png','gif','jpeg','jpg'))){
				   $this -> error('文件上传类型有误!');
				   //echo '文件上传类型有误!';
				   return ;
				}
			    if(!move_uploaded_file($_FILES['icon']['tmp_name'],$uploadfile)){
				   $this -> error('文件上传失败!');
				   //echo '文件上传失败!';
				   return ;
				}else{
				    		
				    //保留文件信息
					$newfile['path']      = $uploaddir;
					$newfile['filename']  = $_FILES['icon']['name'];
					$newfile['extension'] = $this->getExt($_FILES['icon']['name']);
					
					//移动文件
					if(is_file($uploaddir.$newfile['filename'])){
					    /*
						//压缩图标
						include_once SERVER_ROOT. '/tools/functions.php';
						$resize = 1024*5;
						//$width  = 48;
						if (!image_strip_size($uploaddir.$newfile['filename'], $uploaddir.$newfile['filename'], $resize)) {
							$this -> error('无法压缩'.$newfile["filename"].'至5KB以下!');
							//echo '无法压缩'.$newfile["filename"].'至5KB以下!';
						}
						*/
						$savepath = UPLOAD_PATH . '/icon/' . date("Ym/d/");
						if(!is_dir($savepath)){
						   mkdir($savepath,0755,true);
						}
						list($msec, $sec) = explode(' ', microtime());
			            $msec = substr($msec, 2);
						$filename = $package."_".$msec.".".$newfile['extension'];
						if(!copy($uploaddir.$newfile['filename'],$savepath.$filename)){
						   $this -> error('移动文件出错!');
						   //echo '移动文件出错!';
						}else{
						   $icon['icon_url'] = '/icon/'.date("Ym/d/").$filename;
						}
						unlink($uploaddir.$newfile['filename']);
					}
				}
			 }
		}else{
		    $this->error ('请选择Icon图标文件!');
		}
		
		if(!empty($icon)){
		   return $icon;
		}
	}
	
	//apk文件上传
	private function _apkupload(){
	    import("@.ORG.UploadFile");   
		$upload = new UploadFile();
        $upload->maxSize    = 1024*1024*100 ;
        $upload->allowExts  = array('apk');
		$savepath = UPLOAD_PATH . '/apk/' . date("Ym/d/");
		$upload->savePath   = $savepath;
		$upload->saveRule   = 'time';
		$apkinfos = $upload->uploadOne($_FILES['apk'],$upload->savePath);
		$apk = array();
		foreach($apkinfos as $apkinfo){
		    $file = $savepath.$apkinfo['savename'];
			$extinfo = $this->_getapkinfo($file);
			list($msec, $sec) = explode(' ', microtime());
			$msec = substr($msec, 2);
			$apk ['package']   = $extinfo['package'];
			$apk ['acertheme'] = $extinfo['acertheme'];	
			$apk ['version_name'] = $extinfo['version_name'];	
			$apk ['version_code'] = $extinfo['version_code'];
			$apk ['min_firmware'] = $extinfo['min_sdk_ver'];
			$apk ['filesize']     = $apkinfo['size'];
			
			$oldname = $savepath.$apkinfo['savename'];
			$newname = $savepath.$extinfo['package']."_".$msec.".".$apkinfo['extension'];
			if (!copy($oldname, $newname)) {
               echo "failed to copy $oldname...\n";
            }
			unlink($savepath.$oldname);
			//apk包过大时进行切割
			splitfile($newname, $savepath);
			$apk['apk_url'] = '/apk/'.date("Ym/d/").$extinfo['package']."_".$msec.".".$apkinfo['extension'];
		}
        if(!$upload->error){
		     return $apk;
		}
	}
	
	private function _getapkinfo($file){
		if (!is_file($file))
			return false;
		
		//$info = shell_exec("aapt.exe d xmltree \"{$file}\" AndroidManifest.xml");
	
		$bin_path = '/data/www/wwwroot/config/gnu';
		$info = shell_exec("{$bin_path}/aapt d xmltree \"{$file}\" AndroidManifest.xml 2>/dev/null");
	
		$lines  = explode("\n", $info);
		$result = array();

        $meta_start = false;
        $meta_data = '';

		foreach($lines as $str){
		    if (preg_match('/package="([^"]+)"/', $str, $m)) {
				$result['package'] = $m[1];
				continue;
			}
			if (preg_match('/A: android:versionCode\([^\)]+\)=\([^\)]+\)([0-9a-fx]+)/', $str, $m)) {
				$result['version_code'] = hexdec($m[1]);
				continue;
			}
			if (preg_match('/A: android:versionName[^"]+"([^"]+)"/', $str, $m)) {
				$result['version_name'] = $m[1];
				continue;
			}
			if (preg_match('/E: meta-data/', $str)) {
				$meta_start = true;
				continue;
			}
			
			if ($meta_start && preg_match('/A: android:name[^"]+"([^"]+)"/', $str, $m)){
				$meta_data = $m[1];
				continue;
			}
			
			if ($meta_start && $meta_data && preg_match('/A: android:value[^"]+"([^"]+)"/', $str, $m)){
				$result[$meta_data] = $m[1];
				$meta_start = false;
				$meta_data = '';
				continue;
			}
		}
		
	    $info = shell_exec("{$bin_path}/aapt d badging \"{$file}\" 2>/dev/null");
        $lines  = explode("\n", $info);
        foreach ($lines as $str) {
            if (preg_match("/sdkVersion:'([^']*?)'/", $str, $m)) {
                $result['min_sdk_ver'] = $m[1];
                continue;
            }
        }
		
		$third_theme_model = M("third_theme");
		$package_count = $third_theme_model -> where(" package = '".$result['package']."' ") -> select();
		if(count($package_count) > 0){
		   $this -> error("对不起，你上传的APK包已经存在，请重新上传！");
		}else{
		   return $result;
		}
	}
	
	//获取文件后缀
	private function getExt($filename)
    {
        $pathinfo = pathinfo($filename);
        return $pathinfo['extension'];
    }
}
