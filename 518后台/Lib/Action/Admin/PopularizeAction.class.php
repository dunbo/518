<?php
/**
 * 安智网产品管理平台 公用控制器
 * ============================================================================
 * 版权所有 2009-2011 北京力天无限网络有限公司，并保留所有权利。
 * 网站地址: http://www.goapk.com；
 * by:allen 2011/5/17
 * Edit by:金山 2010.5.26
 * 项目位置调整,公用目录调整,部分代码调试
 * ----------------------------------------------------------------------------
*/
class PopularizeAction extends CommonAction{
    /**
     * '&' (ampersand) becomes '&amp;'
     * '"' (double quote) becomes '&quot;' when ENT_NOQUOTES is not set.
     * ''' (single quote) becomes '&#039;' only when ENT_QUOTES is set.
     * '<' (less than) becomes '&lt;'
     * '>' (greater than) becomes '&gt;'
     */
     private $replace = array('&amp;', '&quot;', '&#039;', '&lt;', '&gt;');
     private $becomes = array('&', '"', "'", '<', '>');
     private $pkgtag = "#################apk##################";
     private $startag = "#################star##################";
     private $downnumtag = "#################downnum##################";
     private $destag = "#################des##################";
     private $pic1tag = "#################pic1##################";
     private $pic2tag = "#################pic2##################";
     private $nametag = "#################name##################";
     private $sizetag = "#################size##################";
     private $vertag = "#################ver##################";
     private $Mobile = "#################Mobile##################";
     private $HD = "#################HD##################";
     private $last_refresh = "#################updatetime##################";
     private $puid = "#################puid##################";
     
     

     //模板管理_模板列表
    function moduleList(){
         $DB = M("module");
         $data = $DB -> order('create_time desc') -> select();
         foreach($data as $id => $info){
             extract($info);
             $list[$id]['id'] = $id + 1;
             $list[$id]['mid'] = $mid;
             $list[$id]['name'] = $name;
             $list[$id]['comment'] = $comment;
             $list[$id]['application'] = $application;
             $list[$id]['create_time'] = date("Y-m-d H:i:s", $create_time);
             $list[$id]['update_time'] = date("Y-m-d H:i:s", $update_time);
             }
         $this -> assign('list', $list);
         $this -> display('moduleList');
         }
     //模板管理_生成模板
    function createModule(){
         $DB = M("module");
         $mid = $_GET['mid'];
         $where['mid'] = $mid;
         $result = $DB -> where($where) -> select();
         $content = $result[0]['contect'];
         $content = stripslashes($content);
         $content = str_replace($this -> replace, $this -> becomes, $content);
         if(!file_exists(DIR_MODULE)) mkdir(DIR_MODULE, 0777,true);
         $affect = file_put_contents(DIR_MODULE . "module_" . $mid . ".html", $content);
         if($affect > 0){
             $this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Popularize/moduleList');
             $this -> success("静态页面生成成功！");
             }else
            {
             $this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Popularize/moduleList');
             $this -> error("取消显示失败！");
             }
         }
     //模板管理_修改模板
    function modifyModule(){
         $DB = M("module");
         $add = $_POST['add'];
         $edit = $_POST['edit'];
         $opt = $_REQUEST['opt'];
         $mid = $_POST['mid'];
         $type = ($opt == "add" ? "添加模板" : "编辑模板");

         if($_POST['mname']){
             $data['name'] = $_POST['mname'];
             }

         if($_POST['application']){
             $data['application'] = $_POST['application'];
             }

         if($_POST['contect']){
             $content = htmlspecialchars(stripslashes($_POST['contect']));
             $data['contect'] = $content;
             }

         if($_POST['comment']){
             $data['comment'] = $_POST['comment'];
             }
          $modifytime = time();
         if(count($data) > 0){
             if($opt == "add"){ // 添加记录
                 $data['create_time'] = $modifytime;
                 $data['update_time'] = $modifytime;
                 $affect = $DB -> add($data);
				  $this -> writelog("模板管理-模板列表已添加模板id为{$affect}",'sj_module',$affect,__ACTION__ ,"","add");
                 }else if($opt == "edit"){ // 编辑后的信息 准备入库
                 $data['update_time'] = $modifytime;
				 $log = $this -> logcheck(array('mid'=>$mid),'sj_module',$data,$DB);
                 $affect = $DB -> where(array('mid'=>$mid)) -> save($data);
				 $this -> writelog("模板管理-模板列表已编辑id为{$mid}的模板".$log,'sj_module',$mid,__ACTION__ ,"","edit");
                 }
             if($affect > 0){
                 $this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Popularize/moduleList');
                 $opt == 'add' ? $this -> success("数据插入成功！") : $this -> success("数据修改成功！");
                 }else
                {
                 $this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Popularize/modifyModule');
                 $this -> error("取消显示失败！");
                 }
             }


         $this -> assign("opt", $opt);
         $this -> assign("type", $type);
         $this -> display('modifyModule');
         }

     //模板管理_修改模板_显示
    function editModule(){
         $mid = $_GET['mid'];
         $DB = M('module');
         $where['mid'] = $mid;
         $result = $DB -> where($where) -> select();
         $result[0]['content'] = stripslashes($result[0]['contect']);
         $this -> assign('mid', $mid);
         $this -> assign('opt', 'edit');
         $this -> assign('type', '编辑模板');
         $this -> assign('result', $result[0]);
         $this -> display("modifyModule");
         }
     //模板管理_模板预览
    function profile(){
         $mid = $_GET['mid'];
         $DB = M('module');
         $result = $DB -> where(array('mid'=>$mid)) -> select();
         $content = $result[0]['contect'];
         $content = str_replace($this -> replace, $this -> becomes, $content);
         $this -> assign("profile", $content);
         $this -> display("profile");
         }
     //模板管理_删除模板
    function delModule(){
         $where['mid'] = $_GET['mid'];
         $DB = M('module');
         $result = $DB -> where($where) -> select();

         if(count($result) > 0){
             $sql = "delete from __TABLE__ where mid=" . $_GET['mid'];
             $affect = $DB -> execute($sql);
             if($affect > 0){
                 unlink(DIR_MODULE."module_".$_GET['mid'].'.html');
				 $this -> writelog("模板管理-模板列表已删除id为{$_GET['mid']}的模板",'sj_module',$_GET['mid'],__ACTION__ ,"","del");
                 $this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Popularize/moduleList');
                 $this -> success("数据删除成功！");
                 }else
                {
                 $this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Popularize/modifyModule');
                 $this -> error("取消显示失败！");
                 }
             }
         }
     //模板管理_模板内容管理
    function moduleContentList(){
         $DB = M('module_content');
         $result = $DB -> order('create_time desc') -> select();
         foreach($result as $id => $info){
            $result[$id]['create_time'] = date("Y-m-d H:i:s",$info['create_time']);
            $result[$id]['update_time'] = date("Y-m-d H:i:s",$info['update_time']);
         }
         $remote_addr = $_SERVER['REMOTE_ADDR'];
         $m_url = (strpos($remote_addr, "192.168.0.") === 0 or $remote_addr === "127.0.0.1") ? '9.m.goapk.com' : 'm.anzhi.com';
         $this -> assign('m_url' , $m_url);
         $this -> assign('result', $result);
         $this -> display("moduleContentList");
         }
     //模板管理_添加应用模板
    function addModuleContent(){
         $opt = $_REQUEST['opt'];
         $MDB = M("module");
         if($opt == 'add'){
             $DB = M('module_content');
             $name = $_POST['name'];
             $pkgurl = $_POST['pkgurl'];
             $mid = $_POST['mid'];
             $comment = $_POST['comment'];
             $data['name'] = $name;
             $data['pkgurl'] = $pkgurl;
             $data['mid'] = $mid;
             $data['comment'] = $comment;
             $data['create_time'] = time();
             $data['update_time'] = time();
             $data['staticurl'] = "module_" . $mid;
             $result = $MDB -> where(array('mid'=>$mid)) -> select();
             if(!file_exists(DIR_MODULE . 'module_' . $mid . '.html')){
                 $this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Popularize/moduleList');
                 $this -> error("你现在的选用  模板-》" . $result[0]['name'] . " ！模板还未生成！");
                 }
             $where['name'] = $name;
             $exists = $DB ->where($where) -> select();
             if(!$exists){
               $affect = $DB -> add($data);
             }else{
               $this -> error("文件名已存在");
             }
             if($affect > 0){
                 if($_POST['qrcode'] == 1){
                      include_once (SITE_PATH."../ORG/phpqrcode/qrlib.php");
                      $minfo = $DB ->where($where) -> select();
                       $errorCorrentionLevel = "H";
                       $MatrixPointSize = 7;
                       $PNG_WEB_DIR = SITE_PATH.'../m.goapk.com/static_module/qrpng/';
                       if(!file_exists($PNG_WEB_DIR))   mkdir($PNG_WEB_DIR);
                       $url = $minfo[0]['pkgurl'];
                       $filename = $minfo[0]['mcid'].".png";
                       $fileurl     = $PNG_WEB_DIR .$filename;
                      QRcode::png($url,$fileurl,$errorCorrentionLevel,$MatrixPointSize,2);
                 }

                 // apk 混合模板
                if(count($result) > 0){
                     $replace[] = $this -> pkgtag;
                     $module = file_get_contents(DIR_MODULE . 'module_' . $mid . '.html');
                     $to[] = "<a href=\"" . $pkgurl . "\">点击下载</a>";
                     if($_POST['qrcode'] == 1){
                       $replace[] = "qrcode";
                       $to[] = "qrpng/".$filename;
                     }
                     if($module){
                         $moduleapk = str_replace($replace, $to, $module);
                         if(!file_exists(STATIC_MODULE_DIR)) mkdir(STATIC_MODULE_DIR, 0777);
                         file_put_contents(STATIC_MODULE_DIR . "module_" . $mid . "_" . $affect . ".html", $moduleapk);
						 $this -> writelog("模板管理-模板内容管理已添加生成模板id为{$affect}",'sj_module_content',$affect,__ACTION__ ,"","add");
                         $this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Popularize/moduleContentList');
                         $this -> success("数据插入成功,并生成静态页面！");
                    }
				 }else{
				 $this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Popularize/moduleList');
				 $this -> error("你现在的" . $result[0]['name'] . "模板还未生成！");
				 }
                 // end
            }else
                {
                 $this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Popularize/addModuleContent');
                 $this -> error("数据生成失败！");
                 }
             }
         $mresult = $MDB -> select();
         $this -> assign('mresult', $mresult);
         $this -> assign('opt', 'add');
         $this -> assign('type', "添加应用模板");
         $this -> display('addModuleContent');
         }
     //模板管理_编辑应用模板
    function editModuleContent(){
         $opt = $_REQUEST['opt'];
         $type = ($opt == 'add' ? "添加应用模板" : "编辑应用模板");
         $DB = M('module_content');
         $MDB = M('module');
         $mcid = $_REQUEST['mcid'];
         $where['mcid'] = $mcid;
         $result = $DB -> where(array('mcid'=>$mcid)) -> select();
         $mresult = $MDB -> select();
         if($opt == 'edit'){
             $where['mcid'] = $_REQUEST['mcid'];
             $data['name'] = $_POST['name'];
             $data['mid'] = $_POST['mid'];
             $data['comment'] = $_POST['comment'];
             $data['pkgurl'] = $_POST['pkgurl'];
             $data['staticurl'] = "module_" . $_POST['mid'];
             $data['update_time'] = time();
			 $log = $this -> logcheck(array('mcid'=>$mcid),'sj_module_content',$data,$DB);
             $affect = $DB -> where($where) -> save($data);

             if(!file_exists(DIR_MODULE . 'module_' . $_POST['mid'] . '.html')){
                 $this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Popularize/moduleList');
                 $this -> error("你现在的选用  模板-》" . $result[0]['name'] . " ！模板还未生成！");
             }
             if($affect > 0){
                 // apk 混合模板
                $result = $MDB -> where(array('mid'=>$_POST['mid'])) -> select();
                 if(count($result) > 0){
                     $replace = $this -> pkgtag;
                     if(!file_exists(STATIC_MODULE_DIR)) mkdir(STATIC_MODULE_DIR, 0777);
                     $module = file_get_contents(DIR_MODULE . 'module_' . $_POST['mid'] . '.html');
                     $pkg = "<a href=\"" . $_POST['pkgurl'] . "\">点击下载</a>";
                     if($module){
                         $moduleapk = str_replace($replace, $pkg, $module);
                         file_put_contents(STATIC_MODULE_DIR . "module_" . $_POST['mid'] . "_" . $mcid . ".html", $moduleapk);
						 $this -> writelog("模板管理-模板内容管理已编辑id为{$_POST['mcid']}的模板".$log,'sj_module_content',$_POST['mcid'],__ACTION__ ,"","edit");
                         $this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Popularize/moduleContentList');
                         $this -> success("数据修改成功！");
                         }else{
                         $this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Popularize/moduleList');
                         $this -> error("你现在的" . $result[0]['name'] . "模板还未生成,点击返回模板管理！");
                         }
                     }
                 // end
            }else
                {
                 $this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Popularize/editModuleContent/mcid/' . $mcid);
                 $this -> error("取消显示失败！");
                 }
             }else if($opt == 'delete'){
             $sql = "delete from __TABLE__ where mcid=" . $mcid;
             $affect = $DB -> execute($sql);
             if($affect > 0){
                 unlink(STATIC_MODULE_DIR . 'module_'.$result[0]['mid'].'_'.$mcid.'.html' );
				 $this -> writelog("模板管理-模板内容管理已删除id为".$mcid."的模板内容",'sj_module_content',$mcid,__ACTION__ ,"","del");
                 $this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Popularize/moduleContentList');
                 $this -> success("数据删除成功！");
                 }else
                {
                 $this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Popularize/moduleContentList');
                 $this -> error("取消显示失败！");
                 }
             }
         $this -> assign('opt', 'edit');
         $this -> assign('mcid', $result[0]['mcid']);
         $this -> assign('result' , $result[0]);
         $this -> assign('mresult', $mresult);
         $this -> assign('type', $type);
         $this -> display("editModuleContent");
         }
		 
		 
		//模板管理_关键词模板列表
	function keyword_moduleList(){
		$DB = M('keyword_module');
		$where['status'] = 1;
		$data = $DB -> where($where) -> order('create_tm desc') -> select();
        foreach($data as $key => $val){
            $list[$key]['id'] = $val['id'];
            $list[$key]['name'] = $val['name'];
            $list[$key]['comment'] = $val['comment'];
            $list[$key]['application'] = $val['application'];
            $list[$key]['create_tm'] = date("Y-m-d H:i:s", $val['create_tm']);
            $list[$key]['update_tm'] = date("Y-m-d H:i:s", $val['update_tm']);
        }

        $this -> assign('list', $list);
        $this -> display('keyword_moduleList');
	}
		//模板管理_修改/添加关键词模板
	function keyword_modifyModule(){
		$DB = M("keyword_module");
		$add = $_POST['add'];
		$edit = $_POST['edit'];
		$opt = $_REQUEST['opt'];
		$id = $_POST['id'];
		$type = ($opt == "add" ? "添加模板" : "编辑模板");

		if($_POST['mname']){
			$data['name'] = $_POST['mname'];
		}

		if($_POST['application']){
			$data['application'] = $_POST['application'];
		}

		if($_POST['contect']){
			$content = htmlspecialchars(stripslashes($_POST['contect']));
			$data['contect'] = $content;
		}

		if($_POST['comment']){
			$data['comment'] = $_POST['comment'];
		}
		$modifytime = time();
		if(count($data) > 0){
			if($opt == "add"){ // 添加记录
				$data['create_tm'] = $modifytime;
				$data['update_tm'] = $modifytime;
				$data['status'] = 1;
				$affect = $DB -> add($data);
				$this -> writelog("模板管理-关键词合作模板列表已添加id为{$affect}的关键词模板",'sj_keyword_module',$affect,__ACTION__ ,"","add");
			}else if($opt == "edit"){ // 编辑后的信息 准备入库
				$data['update_tm'] = $modifytime;
				$log = $this -> logcheck(array('id' =>$id),'sj_keyword_module',$data,$DB);
				$affect = $DB -> where(array('id'=>$id)) -> save($data);
				$this -> writelog("模板管理-关键词合作模板列表已编辑id为{$id}的关键词模板".$log,'sj_keyword_module',$id,__ACTION__ ,"","edit");
			}
			
			if($affect > 0){
				$this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Popularize/keyword_moduleList');
				$opt == 'add' ? $this -> success("数据插入成功！") : $this -> success("数据修改成功！");
			}else{
				$this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Popularize/keyword_modifyModule');
				$this -> error("取消显示失败！");
			}
		}

		$this -> assign("opt", $opt);
		$this -> assign("type", $type);
		$this -> display('keyword_modifyModule');
			
	}
	
	
		//模板管理_删除关键词合作模板
	function keyword_delModule(){
		$DB = M("keyword_module");
		$id = $_GET['id'];

		if($id){
			$data['status'] = 0;
			$where['id'] = $id;
			$affect = $DB -> where($where) -> save($data);
		}

		if($affect){
			$this -> writelog("模板管理-关键词合作模板列表已删除id为".$id."的关键词合作模板",'sj_keyword_module',$id,__ACTION__ ,"","del");
			$this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Popularize/keyword_moduleList');
			$this -> success("删除成功");
		}
	}
	
	    //模板管理_关键词合作修改模板_显示
    function keyword_editModule(){
		$mid = $_GET['id'];
		$DB = M('keyword_module');
		$where['id'] = $mid;
		$result = $DB -> where($where) -> select();
		$result[0]['content'] = stripslashes($result[0]['contect']);
		$this -> assign('id', $id);
		$this -> assign('opt', 'edit');
		$this -> assign('type', '编辑模板');
		$this -> assign('result', $result[0]);
		$this -> display("keyword_modifyModule");
    }
	
	     //模板管理_关键词合作生成模板
	function keyword_createModule(){
		$DB = M("keyword_module");
		$id = $_GET['id'];
		$where['id'] = $id;
		$result = $DB -> where($where) -> select();
		$content = $result[0]['contect'];
		$content = stripslashes($content);
		$content = str_replace($this -> replace, $this -> becomes, $content);
	
		//echo KEY_DIR_MODULE;exit;
	   if(!file_exists(KEY_DIR_MODULE)){
			mkdir(KEY_DIR_MODULE, 0777);
	   }

		$affect = file_put_contents(KEY_DIR_MODULE . "keywordmodule_" . $id . ".html", $content);
		if($affect > 0){
			$this -> writelog("已生成id为{$id}模板的静态页面",'sj_keyword_module',$id,__ACTION__ ,"","add");
			$this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Popularize/keyword_moduleList');
			$this -> success("静态页面生成成功！");
		}else{
			$this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Popularize/keyword_moduleList');
			$this -> error("取消显示失败！");
		}
	}
	
	 //模板管理_关键词合作模板预览
    function keyword_profile(){
        $id = $_GET['id'];
        $DB = M('keyword_module');
        $result = $DB -> where(array('id'=>$id)) -> select();
        $content = $result[0]['contect'];
        $content = str_replace($this -> replace, $this -> becomes, $content);
        $this -> assign("keyword_profile", $content);
        $this -> display("keyword_profile");
    }
	
	//模板管理_关键词合作模板内容管理
	function keyword_moduleConList(){
		$DB = M('keyword_modcontent');
		$where['status'] = 1;
        $result = $DB -> where($where) -> order('create_tm desc') -> select();
        foreach($result as $key => $val){
           $result[$key]['create_tm'] = date("Y-m-d H:i:s",$val['create_tm']);
           $result[$key]['update_tm'] = date("Y-m-d H:i:s",$val['update_tm']);
        }
        $remote_addr = $_SERVER['REMOTE_ADDR'];
        $m_url = (strpos($remote_addr, "192.168.0.") === 0 or $remote_addr === "127.0.0.1") ? '9.m.goapk.com' : 'm.anzhi.com';
        $this -> assign('m_url' , $m_url);
        $this -> assign('result', $result);
        $this -> display("keyword_moduleConList");
	}
	
	//模板管理_关键词合作模板添加
	function keyword_addModContent(){
		$opt = $_REQUEST['opt'];
		$MDB = M("keyword_module");
		if($opt == 'add'){
			$DB = M('keyword_modcontent');
			$result_to = $DB -> field('id') -> order('id DESC') -> select();
			$mid = $result_to[0]['id'] + 1;
			$name = $_POST['name'];
			$pkgurl = $_POST['pkgurl'];
			$id = $_POST['id'];
			$comment = $_POST['comment'];
			$title = $_POST['title'];
			$descript = $_POST['descript'];
			$image = $_FILES['image'];
			$data['name'] = $name;
			$data['pkgurl'] = $pkgurl;
			$data['mid'] = $id;
			$data['comment'] = $comment;
			$data['title'] = $title;
			$data['descript'] = $descript;
			$data['create_tm'] = time();
			$data['update_tm'] = time();
			$data['status'] = 1;
			$data['staticurl'] = "keywordmodcontent_" . $id . "_" . $mid;
			$result = $MDB -> where(array('id'=>$id)) -> select();

			if($_FILES['image']['size'] > 204800){
				$this -> error("对不起，上传图片过大");
			}
			if(!file_exists(KEY_DIR_MODULE . 'keywordmodule_' . $id . '.html')){
				$this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Popularize/keyword_moduleList');
				$this -> error("你现在的选用  模板-》" . $result[0]['name'] . " ！模板还未生成！");
			}
			
			$img_dir = UPLOAD_PATH ."/img/". date('Ym/d/', time());
			if(!empty($_FILES['image']['size'])){
				$config['multi_config']['image'] = array(
					'savepath' => $img_dir,
					'saveRule' => 'getmsec',
					'img_p_size' => 1024*50,
					'img_p_width' => 440,
					'img_p_height' => 374,
				);
				$list = $this->_uploadapk(0, $config);
				$image = $list['image'][0]['url'];
				if(!$list){
					$this -> error("生成图片失败");
				}
				$data['imgurl'] = $list['image'][0]['url'];

			} else {
				$this->error("请选择上传图片！");
			}
			
			$where['name'] = $name;
			$where['status'] = 1;
			$exists = $DB ->where($where) -> select();
			if(!$exists){
				$affect = $DB -> add($data);
			}else{
				$this -> error("文件名已存在");
			}
			
			if($affect > 0){
				// apk 混合模板
				if(count($result) > 0){
					$replace[] = $this -> pkgtag;
					$module = file_get_contents(KEY_DIR_MODULE . 'keywordmodule_' . $id . '.html');
					$to['apk'] =  $pkgurl ;
					$to['title'] = $title;
					$to['comment'] = $comment;
					$to['descript'] = $descript;
					$to['image'] = IMGATT_HOST.$image;
					if($module){
						$module= str_replace('######apk######',$to['apk'],$module);
						$module= str_replace('######title######',$to['title'],$module);
						$module= str_replace('######image######',$to['image'],$module);
						$module= str_replace('######comment######',$to['comment'],$module);
						$module= str_replace('######descript######',$to['descript'],$module);
						if(!file_exists(KEY_STATIC_MODULE)) mkdir(KEY_STATIC_MODULE, 0777);
						file_put_contents(KEY_STATIC_MODULE . "keywordmodcontent_" . $id . "_" . $mid . ".html", $module);
						$this -> writelog("模板管理-关键词合作模板内容管理已添加id为{$affect}的关键词模板",'sj_keyword_modcontent',$id,__ACTION__ ,"","add");
						$this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Popularize/keyword_moduleConList');
						$this -> success("数据插入成功,并生成静态页面！");
					}
				}else{
					$this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Popularize/keyword_moduleList');
					$this -> error("你现在的" . $result[0]['name'] . "模板还未生成！");
				}
				// end

			}else{
				$this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Popularize/keyword_addModContent');
				$this -> error("数据生成失败！");
			}
		}
		$where_go['status'] = 1; 
		$mresult = $MDB -> where($where_go) -> select();
		$this -> assign('mresult', $mresult);
		$this -> assign('opt', 'add');
		$this -> assign('type', "添加应用模板");
		$this -> display('keyword_addModContent');

	}
	
	//模板管理_删除关键词模板内容
	function keyword_delModContent(){
		$DB = M("keyword_modcontent");
		$where['id'] = $_GET['id'];
		$data['status'] = 0;
		$affect = $DB -> where($where) -> save($data);
		if($affect){
			$this -> writelog("模板管理-关键词合作模板内容管理已删除id为{$_GET['id']}的关键词模板内容",'sj_keyword_modcontent',$_GET['id'],__ACTION__ ,"","del");
			$this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Popularize/keyword_moduleConList');
			$this -> success("删除成功");
		}else{
			$this -> error("删除失败");
		}
	}
	//模板管理_编辑关键词模板内容显示
	function keyword_editModContent(){
		$DB = M("keyword_modcontent");
		$MDB = M("keyword_module");
		$mresult = $MDB -> where(array('status' => 1)) -> select();
		$id = $_GET['id'];
		$where['id'] = $id;
		$result = $DB -> where($where) -> select();
		foreach($result as $key => $val){
			$module = explode('_',$val['staticurl']);
			$result[$key]['mid'] = $module[1];
		}
		$this -> assign("mresult",$mresult);
		$this -> assign("result",$result[0]);
		$this -> display();
	}
	//模板管理_编辑关键词模板内容
	function keyword_editModContent_do(){
		$DB = M("keyword_modcontent");
		$MDB = M("keyword_module");
		$id = $_POST['id'];
		$name = $_POST['name'];
		$pkgurl =$_POST['pkgurl'];
		$staticurl = $_POST['mid'];
		$title = $_POST['title'];
		$descript = $_POST['descript'];
		$comment = $_POST['comment'];
		$image = $_FILES['image'];
		$data['name'] = $name;
		$data['pkgurl'] = $pkgurl;
		$data['title'] = $title;
		$data['descript'] = $descript;
		$data['comment'] = $comment;
		$data['staticurl'] = 'keywordmodcontent_'.$staticurl."_".$id;
		$result = $MDB -> where(array('status' =>1,'id' => $staticurl)) -> field('name') -> select();
		
		if(!file_exists(KEY_DIR_MODULE . 'keywordmodule_' . $staticurl . '.html')){
			$this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Popularize/moduleList');
			$this -> error("你现在的选用  模板-》" . $result[0]['name'] . " ！模板还未生成！");
		}
		$where['id'] = $id;
		$where['status'] = 1;
		
		$img_dir = UPLOAD_PATH ."/img/". date('Ym/d/', time());
		$image_to = $DB -> where($where) -> field('imgurl') -> select();
		if(!empty($_FILES['image']['size'])){
			$config['multi_config']['img'] = array(
				'savepath' => $img_dir,
				'saveRule' => 'getmsec',
				'img_p_width' => 440,
				'img_p_height' => 374,
				'img_p_size' => 1024 * 50,
			);
			//$this->_uploadapk(0, $config);
			$lists=$this->_uploadapk(0, $config);
			$image = $lists['image'][0]['url'];
		} else {
			$image = $image_to[0]['imgurl'];
		}
		$data['imgurl'] = $image;
		$log_result = $this->logcheck(array('id' => $id),'sj_keyword_modcontent',$data,$DB);

		$affect = $DB -> where($where) -> save($data);
		if($affect > 0){
			// apk 混合模板
			$result = $DB -> where(array('id'=>$id)) -> select();
			if(count($result) > 0){
				$replace = $this -> pkgtag;
				if(!file_exists(KEY_STATIC_MODULE)) mkdir(KEY_STATIC_MODULE, 0777);
				$module = file_get_contents(KEY_DIR_MODULE . 'keywordmodule_' . $staticurl . '.html');
				$pkg =  $_POST['pkgurl'] ;
				$to['apk'] =  $pkgurl ;
				$to['title'] = $title;
				$to['comment'] = $comment;
				$to['descript'] = $descript;
				$to['image'] = IMGATT_HOST.$image;

				if($module){
					$module= str_replace('######apk######',$to['apk'],$module);
					$module= str_replace('######title######',$to['title'],$module);
					$module= str_replace('######image######',$to['image'],$module);
					$module= str_replace('######comment######',$to['comment'],$module);
					$module= str_replace('######descript######',$to['descript'],$module);
					file_put_contents(KEY_STATIC_MODULE . "keywordmodcontent_" . $staticurl . "_" . $_POST['id'] . ".html", $module);
					$this -> writelog("模板管理-关键词合作模板内容管理已编辑id为{$id}的关键词模板内容".$log_result,'sj_keyword_modcontent',$id,__ACTION__ ,"","edit");
					$this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Popularize/keyword_moduleConList');
					$this -> success("数据修改成功！");
				}else{
					$this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Popularize/keyword_moduleList');
					$this -> error("你现在的" . $result[0]['name'] . "模板还未生成,点击返回模板管理！");
				}
			}
			// end
		}else{
				$this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Popularize/keyword_editModContent/id/' . $id);
				$this -> error("取消显示失败！");
		}
		
	
		$this -> assign('id', $result[0]['id']);
		$this -> assign('result' , $result[0]);

		$this -> assign('mresult', $mresult);
		$this -> assign('type', $type);
		$this -> display("keyword_editModContent");
	}

	// Wap模板列表
	function WapModuleList() {
		$module = M('wap_module');
		$res = $module->where("status = 1")->order('create_time desc')->select();
		$this->assign('list', $res);
		$this->display();
	}
	function WapModule_del(){
		$id = $_GET['id'];
		if (!$id){
			$this->ajaxReturn(0, '未知错误！', 0);
			return ;
		}
		$saw = M('wap_module');
		$data['status'] = 0;
		if ($saw->where("id=$id")->save($data)){
			$this -> writelog("模板管理-Wap模板列表已删除id为{$id}的wap模板",'sj_wap_module',$id,__ACTION__ ,"","del");
			unlink(DIR_MODULE."wapmodule_".$id.".html");
			$this->ajaxReturn(1, '删除成功！', 1);
		} else {
			$this->ajaxReturn(0, '删除 失败！', 0);
		}
		return ;
	}	
    function wapprofile() {
    	$id = $_GET['id'];
    	$module = M('wap_module');
    	$res = $module->where(array('id' => $id))->select();
    	$content = $res[0]['content'];
    	$content = str_replace($this->replace, $this->becomes, $content);
    	$this->assign('wapprofile', $content);
    	$this->display();
    }
	function WapCreateMoule() {
		$id = $_GET['id'];
		$module = M('wap_module');
		$res = $module->where("id = $id")->select();
		$content = $res[0]['content'];
		$content = stripslashes($content);
		$content = str_replace($this->replace, $this->becomes, $content);
		if (!file_exists(DIR_MODULE)){
			mkdir(DIR_MODULE, 0777, TRUE);
		}
		$affect = file_put_contents(DIR_MODULE."wapmodule_".$id.".html", $content);
		if ($affect > 0){
			$this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Popularize/WapModuleList');
			 $this -> success("静态页面生成成功！");
		} else {
			 $this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Popularize/WapModuleList');
			 $this -> error("静态页面生成失败！");
		}
	}
    function WapModule_add() {
    	if ($_POST){
	    	$data['name'] = trim($_POST['name']);
	    	$data['application'] = trim($_POST['application']);
	    	$data['content'] = trim($_POST['content']);
	    	if (!$data['name'] || !$data['application'] || !$data['content']){
	    		$this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Popularize/WapModule_add');
                $this -> error("请按提示填写必要的项！");
	    		return ;
	    	}
	    	$data['note'] = trim($_POST['note']);
	    	$data['update_time'] = $data['create_time'] = time();
	    	
	    	$module = M('wap_module');
	    	if ($module->where("name = '{$data['name']}' AND status = 1")->select()){
	    		$this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Popularize/WapModule_add');
				$this->error("添加失败,模板名称已存在！");
	    		return ;
	    	}
    		if($id=$module->add($data)){
				$this->writelog('模板管理-Wap模板列表添加了名称ID为['.$id.']为的模板', 'sj_wap_module', $id,__ACTION__ ,"","add");
				$this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Popularize/WapModuleList');
				$this->success("添加成功！");
			}else{
				$this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Popularize/WapModule_add');
				$this->error("添加失败,发生错误！");
			}
    	} else {
	    	$this->display();
    	}
    }
	function WapModule_edit() {
		$module = M('wap_module');
		if ($_POST){
			$id = trim($_POST['id']);
			print_r($id);
			$data['name'] = trim($_POST['name']);
	    	$data['application'] = trim($_POST['application']);
	    	$data['content'] = trim($_POST['content']);
	    	$data['note'] = trim($_POST['note']);
	    	$data['update_time'] = time();
	    	$log = $this -> logcheck(array('id' =>$id),'sj_wap_module',$data,$module);
    		if($id=$module->where("id='$id'")->save($data)){
				$this->writelog("模板管理-Wap模板列表编辑了名称ID为".$_POST['id']."的模板".$log, 'sj_wap_module', $id,__ACTION__ ,"","edit");
				$this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Popularize/WapModuleList');
				$this->success("编辑成功！");
			}else{
				$this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Popularize/WapModule_edit/id'.$id);
				$this->error("编辑失败,发生错误！");
			}
		} else {
			$id = $_GET['id'];
			$res = $module->where(array('id' => $id))->select();
			$this->assign('info', $res[0]);
			$this->display();
		}
    }
	// Wap模板内容管理
	function WapModuleContentList() {
		$module = M('wap_module_content');
		$where = array('status'=>1);
		$this->check_where($where, 'name', 'isset', 'like'); 
		$res = $module->where($where)->order('create_time desc')->select();
		$remote_addr = $_SERVER['REMOTE_ADDR'];
		$m_url = (strpos($remote_addr, "192.168.0.") === 0 or $remote_addr === "127.0.0.1") ? '9.m.goapk.com' : 'm.anzhi.com';
		if(C('is_test') == 1 ){
			$m_url = "m.test.anzhi.com";
		}
		$update_time11 = $module ->field('update_time') ->order('update_time desc')-> select();
		if($update_time11 != ''){
			$update_time111 = $update_time11[0];

		}else{
			$update_time111 = '';
		}

		$this -> assign('update_time111' , $update_time111);
                
        $this -> assign('m_url' , $m_url);
		$this->assign('list', $res);
		$this->display();
	}
	function WapModuleContent_del(){
		$id = $_GET['id'];
		if (!$id){
			$this->ajaxReturn(0, '未知错误！', 0);
			return ;
		}
		$saw = M('wap_module_content');
		$data['status'] = 0;
		$res = $saw->where("id=$id")->select();
		if ($saw->where("id=$id")->save($data)){
			$this -> writelog("模板管理-Wap模板内容管理已删除id为{$id}的wap模板内容", 'sj_wap_module_content', $id,__ACTION__ ,"","del");
			unlink(STATIC_MODULE_DIR . $res[0]['staticurl']);
			$this->ajaxReturn(1, '删除成功！', 1);
		} else {
			$this->ajaxReturn(0, '删除 失败！', 0);
		}
		return ;
	}
	function WapModuleContent_add() {
        $mod1 = M('soft');
		if ($_POST){
			$data['mtype'] = trim($_POST['modtype']);
			$data['name'] = trim($_POST['modname']);
			$data['pkgurl'] = trim($_POST['apkpath']);
			$data['star'] = trim($_POST['star']);
			$data['downnum'] = trim($_POST['downnum']);
			$data['summary'] = trim($_POST['summary']);
			$data['note'] = trim($_POST['note']);	// 不是必填
			if ($_FILES['pic1']['tmp_name'] || $_FILES['pic2']['tmp_name']){
				$path=date('Ym/d/', time());
				$config = array(
					'multi_config' => array(
							'pic1' => array(
								'savepath' => UPLOAD_PATH. '/images/'. $path,
								'saveRule' => 'getmsec',
								'img_p_size' =>  1024*15,
							),
							'pic2' => array(
								'savepath' => UPLOAD_PATH. '/images/'. $path,
								'saveRule' => 'getmsec',
								'img_p_size' =>  1024*15,
							),
						),
				);
				$lists=$this->_uploadapk(0, $config);
				foreach($lists['image'] as $val) {
						if ($val['post_name'] == 'pic1') {
							$data['pic_1'] = $val['url'];
							$replace[] = $this -> pic1tag;
							$to[] = IMGATT_HOST . $data['pic_1'];
						}
						if ($val['post_name'] == 'pic2') {
							$data['pic_2']= $val['url'];
							$replace[] = $this -> pic2tag;
							$to[] = IMGATT_HOST . $data['pic_2'];
						}
				}
			}
			$this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Popularize/WapModuleContent_add');
			
			if ($data['mtype'] != '安智市场游戏推广' && $data['mtype'] != '安智市场小说' && (!$data['mtype'] || !$data['name'] || !$data['pkgurl'] || !$data['star'] || !$data['downnum'] || !$data['summary'])){				
				$this -> error("请按提示填写必要的项！");
			} else {
				if(!$data['name'] || !$data['pkgurl']){
					$this -> error("请按提示填写必要的项！");
				}
			}				
			$module = M('wap_module');
			$res = $module->where("name = '{$data['mtype']}' AND status = 1")->select();
			if(!file_exists(DIR_MODULE."wapmodule_".$res[0]['id'].".html")){
				$this -> error("你现在的选用  模板-》" . $res[0]['name'] . " ！还未生成或者已删除！");
			}
			$data['module_id'] = $res[0]['id'];							
			$mod = M('wap_module_content');
			$exists = $mod->where("name = '{$data['name']}' AND status = 1")->select();
	            if(!$exists){
					if($id=$mod->add($data)){
						$replace[] = $this -> pkgtag;
						$replace[] = $this -> startag;
						$replace[] = $this -> downnumtag;
						$replace[] = $this -> destag;
						$replace[] = $this -> nametag;
						$replace[] = $this -> sizetag;
						$replace[] = $this -> vertag;
						$replace[] = $this -> last_refresh;
						$replace[] = $this -> puid;
						$to[] = $data['pkgurl'];
						if ($data['star']){
							$img = '';
							for ($i = 0; $i < $data['star']; $i++){
								$img .= "<img src='images/star_01.png' />";
							}
							for ($j = 0; $j < 5 - $data['star']; $j++){
								$img .= "<img src='images/star_03.png' />";
							}
							$to[] =$img;
						}
		                $to[] = $data['downnum'];
		                $to[] = nl2br($data['summary']);
                                     
						$Pupo = $this ->table_db = D('Sj.Popularlink');
						$Pupos = $Pupo->where("pu_link = '{$data['pkgurl']}' AND status = 1")->select();
                                     
						$to[] = $Pupos[0]['pkg_name'];
						$to[] = round($Pupos[0]['pkg_size'] / 1048576 ,2 ) . 'M';
						$to[] = $Pupos[0]['pkg_ver'];
						$package = $Pupos[0]['package'];
						$update_time_ = $mod1 -> where("package ='{$package}' AND status=1 and hide=1 ") -> order('softid desc') ->limit(1) -> select();                                   
						$update_time_1 = $update_time_[0]['last_refresh'];
		                $to[] = date("Y-m-d", $update_time_1);
                        $to[] = $Pupos[0]['puid'];
		                $m = file_get_contents(DIR_MODULE."wapmodule_".$res[0]['id'].".html");
		                    
						if($m){
							$moduleapk = str_replace($replace, $to, $m);
							if(!file_exists(STATIC_MODULE_DIR)) mkdir(STATIC_MODULE_DIR, 0777);
							file_put_contents(STATIC_MODULE_DIR . "wapmodule_" . $res[0]['id'] . "_" . $id . ".html", $moduleapk);
						 
							$data = '';
							$data['staticurl'] = "wapmodule_" . $res[0]['id'] . "_" . $id . ".html";
							$data['update_time'] = $data['create_time'] = time();
							$mod->where("id = '$id'")->save($data);
							$this->writelog('模板管理-Wap模板内容管理添加了名称ID为['.$id.']为的模板', 'sj_wap_module_content', $id,__ACTION__ ,"","add");
							$this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Popularize/WapModuleContentList');
							$this -> success("数据插入成功,并生成静态页面！");
						}else{
							$this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Popularize/WapModuleContent_add');
							$this -> error("你选择的模板还未生成或者已删除！");
						}
					}else{
						$this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Popularize/WapModuleContent_add');
						$this->error("添加失败,发生错误！");
					}
	            }else{
	               $this -> error("文件名已存在");
	            }
		} else {
			$module = M('wap_module');
			$res = $module->where("status = 1")->order('create_time desc')->select();
			$this->assign('list', $res);
			$this->display();
		}
    }
	function WapModuleContent_edit() {
    	$mod = M('wap_module_content');
    	$module = M('wap_module');
        $mod1 = M('soft');
		
		if ($_POST){
			$id = $_POST['id'];
			$data['mtype'] = trim($_POST['modtype']);
			$data['name'] = trim($_POST['modname']);
			$data['pkgurl'] = trim($_POST['apkpath']);
			$data['star'] = trim($_POST['star']);
			$data['downnum'] = trim($_POST['downnum']);
			$data['summary'] = trim($_POST['summary']);
			$data['note'] = trim($_POST['note']);	// 不是必填
			$data['update_time'] = time();
			if ($_FILES['pic1']['tmp_name'] || $_FILES['pic2']['tmp_name']){
				$path=date('Ym/d/', time());
				$config = array(
					'multi_config' => array(
							'pic1' => array(
								'savepath' => UPLOAD_PATH. '/images/'. $path,
								'saveRule' => 'getmsec',
								'img_p_size' =>  1024*15,
							),
							'pic2' => array(
								'savepath' => UPLOAD_PATH. '/images/'. $path,
								'saveRule' => 'getmsec',
								'img_p_size' =>  1024*15,
							),
						),
				);
				$lists=$this->_uploadapk(0, $config);
				foreach($lists['image'] as $val) {
						if ($val['post_name'] == 'pic1') {
							$data['pic_1']= $val['url'];
							$replace[] = $this -> pic1tag;
							$to[] = IMGATT_HOST . $data['pic_1'];
						}
						if ($val['post_name'] == 'pic2') {
							$data['pic_2']= $val['url'];
							$replace[] = $this -> pic2tag;
							$to[] = IMGATT_HOST . $data['pic_2'];
						}
				}
			}
			$this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Popularize/WapModuleContent_edit/id/'.$id);
			if ($data['mtype'] != '安智市场游戏推广' && $data['mtype'] != '安智市场小说' && (!$data['mtype'] || !$data['name'] || !$data['pkgurl'] || !$data['star'] || !$data['downnum'] || !$data['summary'])){				
				$this -> error("请按提示填写必要的项！");
			} else {
				if(!$data['name'] || !$data['pkgurl']){
					$this -> error("请按提示填写必要的项！");
				}
			}
			$result = $module->where("name = '{$data['mtype']}'")->select();
			if(!file_exists(DIR_MODULE."wapmodule_".$result[0]['id'].".html")){
				$this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Popularize/WapModuleContentList');
				$this -> error("你现在的选用  模板-》" . $result[0]['name'] . " ！还未生成！");
			}
			$log_result = $this->logcheck(array('id'=>$id),'sj_wap_module_content',$data,$mod);
			if($mod->where("id = '$id'")->save($data)){
				if(count($result) > 0){
					$replace[] = $this -> pkgtag;
					$replace[] = $this -> startag;
					$replace[] = $this -> downnumtag;
					$replace[] = $this -> destag;
					$replace[] = $this -> nametag;
					$replace[] = $this -> sizetag;
					$replace[] = $this -> vertag;
					$replace[] = $this -> last_refresh;
					$replace[] = $this -> puid;
					$to[] = $data['pkgurl'];
					if ($data['star']){
						$img = '';
						for ($i = 0; $i < $data['star']; $i++){
							$img .= "<img src='images/star_01.png' />";
						}
						for ($j = 0; $j < 5 - $data['star']; $j++){
							$img .= "<img src='images/star_03.png' />";
						}
						$to[] =$img;
					}
					$to[] = $data['downnum'];
					$to[] = nl2br($data['summary']);
					$Pupo = $this ->table_db = D('Sj.Popularlink');
					$Pupos = $Pupo->where("pu_link = '{$data['pkgurl']}' AND status = 1")->select();
					$to[] = $Pupos[0]['pkg_name'];
					$to[] = round($Pupos[0]['pkg_size'] / 1048576 ,2 ) . 'M';
					$to[] = $Pupos[0]['pkg_ver'];
					$package = $Pupos[0]['package'];					
					$update_time_ = $mod1 -> where("package ='{$package}' AND status=1 and hide=1 ") -> order('softid desc') ->limit(1) -> select();
					$update_time_1 = $update_time_[0]['last_refresh'];	   
					$to[] = date("Y-m-d", $update_time_1);
					$to[] = $Pupos[0]['puid'];
					$res = $mod->where("id = '$id'")->select();
					if (!$data['pic_1']){
						$replace[] = $this -> pic1tag;
						$to[] = IMGATT_HOST . $res[0]['pic_1'];
					}
					if (!$data['pic_2']){
						$replace[] = $this -> pic2tag;
						$to[] = IMGATT_HOST . $res[0]['pic_2'];
					}
					$m = file_get_contents(DIR_MODULE."wapmodule_".$result[0]['id'].".html");
					if($m){
						$moduleapk = str_replace($replace, $to, $m);
						if(!file_exists(STATIC_MODULE_DIR)) mkdir(STATIC_MODULE_DIR, 0777);
						file_put_contents( STATIC_MODULE_DIR . "wapmodule_" . $result[0]['id'] . "_" . $id . ".html", $moduleapk);
						 
						$data = '';
						$data['staticurl'] = "wapmodule_" . $result[0]['id'] . "_" . $id . ".html";
						$data['update_time'] = time();
						$mod->where("id = '$id'")->save($data);
						$this->writelog('模板管理-Wap模板内容管理编辑了名称ID为['.$id.']的模板'.$log_result, 'sj_wap_module_content', $id,__ACTION__ ,"","edit");
						$this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Popularize/WapModuleContentList');
						$this -> success("编辑成功,并生成静态页面！");
					}
				 }else{
					$this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Popularize/WapModuleContentList');
					$this -> error("你现在的" . $result[0]['name'] . "模板还未生成或已删除！");
				 }
			}else{
				$this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Popularize/WapModuleContent_edit/id/'.$id);
				$this->error("编辑失败,发生错误！");
			}
			
		} else {
			$id = $_GET['id'];
			$res = $mod->where("id = $id")->select();
			$id_ = $res[0][id];
			if ($r = $module -> where("id = $id_")->select()){
				$res[0]['mname'] = $r[0]['name'];
			}
			$this->assign('info', $res[0]);
			$this->display();
		}
    }
    
    function wapanzhi(){
    	$mod = M('wapanzhi_module');
    	$res = $mod->where("status=1")->select();
	
    	$this->assign('list',$res);
    	$this->display();
    }
    function wapanzhi_add(){
        if ($_POST){
	    	$data['name'] = trim($_POST['name']);
	    	$data['content'] = trim($_POST['content']);
	    	if (!$data['name'] || !$data['content']){
	    		$this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Popularize/wapanzhi_add');
                $this -> error("请按提示填写必要的项！");
	    		return ;
	    	}
	    	$data['note'] = trim($_POST['note']);
	    	$data['update_time'] = $data['create_time'] = time();
	    	
	    	$module = M('wapanzhi_module');
	    	if ($module->where("name = '{$data['name']}' AND status = 1")->select()){
	    		$this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Popularize/wapanzhi_add');
				$this->error("添加失败,模板名称已存在！");
	    		return ;
	    	}
    		if($id=$module->add($data)){
				$this->writelog('模板管理-anzhi安智添加了名称ID为['.$id.']为的模板', 'sj_wapanzhi_module', $id,__ACTION__ ,"","add");
				$this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Popularize/wapanzhi');
				$this->success("添加成功！");
			}else{
				$this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Popularize/wapanzhi_add');
				$this->error("添加失败,发生错误！");
			}
    	} else {
	    	$this->display();
    	}
    }
    function wapanzhi_edit(){
    	$module = M('wapanzhi_module');
		if ($_POST){
			$id = trim($_POST['id']);
			$data['name'] = trim($_POST['name']);
	    	$data['content'] = trim($_POST['content']);
	    	$data['note'] = trim($_POST['note']);
	    	$data['update_time'] = time();
	    	$log = $this -> logcheck(array('id' =>$id),'sj_wapanzhi_module',$data,$module);
    		if($module->where("id='$id'")->save($data)){
				$this->writelog('模板管理-anzhi安智编辑了名称ID为['.$id.']的模板'.$log, 'sj_wapanzhi_module', $id,__ACTION__ ,"","edit");
				$this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Popularize/wapanzhi');
				$this->success("编辑成功！");
			}else{
				$this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Popularize/wapanzhi_edit/id'.$id);
				$this->error("编辑失败,发生错误！");
			}
		} else {
			$id = $_GET['id'];
			$res = $module->where(array('id' => $id))->select();
			$this->assign('info', $res[0]);
			$this->display();
		}
    }
    function wapanzhi_del(){
		$id = $_GET['id'];
		if (!$id){
			$this->ajaxReturn(0, '未知错误！', 0);
			return ;
		}
		$mod = M('wapanzhi_module');
		$data['status'] = 0;
		if ($mod->where("id=$id")->save($data)){
			$this -> writelog("模板管理-anzhi安智已删除id为{$id}的wapanzhi模板", 'sj_wapanzhi_module', $id,__ACTION__ ,"","del");
			unlink(DIR_MODULE."wapanzhimodule_".$id.".html");
			$this->ajaxReturn(1, '删除成功！', 1);
		} else {
			$this->ajaxReturn(0, '删除 失败！', 0);
		}
		return ;    	
    }
    /*function wapanzhi_proview(){
    	$id = $_GET['id'];
    	$module = M('wapanzhi_module');
    	$res = $module->where(array('id' => $id))->select();
    	$content = $res[0]['content'];
    	$content = str_replace($this->replace, $this->becomes, $content);
    	$this->assign('wapprofile', $content);
    	$this->display('wapprofile');    	
    }*/
    function wapanzhi_create(){
    	$id = $_GET['id'];
		$module = M('wapanzhi_module');
		$res = $module->where("id = $id")->select();
		$content = $res[0]['content'];
		$content = stripslashes($content);
		$content = str_replace($this->replace, $this->becomes, $content);
		if (!file_exists(DIR_MODULE)){
			mkdir(DIR_MODULE, 0777, TRUE);
		}
		$affect = file_put_contents(DIR_MODULE."wapanzhimodule_".$id.".html", $content);
		if ($affect > 0){
			$this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Popularize/wapanzhi');
			 $this -> success("静态页面生成成功！");
		} else {
			 $this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Popularize/wapanzhi');
			 $this -> error("静态页面生成失败！");
		}    	
    }
    function wapanzhicontent(){
		$module = M('wapanzhi_content');
		$res = $module->where("status = 1")->order('create_time desc')->select();
		$remote_addr = $_SERVER['REMOTE_ADDR'];
		$m_url = (strpos($remote_addr, "192.168.0.") === 0 or $remote_addr === "127.0.0.1") ? '9.m.goapk.com' : 'm.anzhi.com';
		
        $this -> assign('m_url' , $m_url);
		$this->assign('list', $res);
		$this->display();    	
    }
    function wapanzhicontent_add(){
    	if ($_POST){
			$data['mid'] = trim($_POST['mid']);
			$data['M'] = trim($_POST['M']);
			$data['H'] = trim($_POST['H']);
			
			if ($data['mid'] && $data['M'] && $data['H']){
				$tplpath = DIR_MODULE."wapanzhimodule_".$data['mid'].".html";
				if(!file_exists($tplpath)){
					$this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Popularize/wapanzhicontent_add');
					$this -> error("模板不存在,请生成先！");
				}
				
				$mod = M('wapanzhi_content');
				if($id=$mod->add($data)){
	                     $replace[] = $this -> Mobile;
	                     $replace[] = $this -> HD;
	                     $to[] = $data['M'];
	                     $to[] = $data['H'];
	                     $m = file_get_contents($tplpath);
	                     if($m){
	                         $moduleapk = str_replace($replace, $to, $m);
	                         if(!file_exists(STATIC_MODULE_DIR)) mkdir(STATIC_MODULE_DIR, 0777);
	                         
	                         $statcurl = STATIC_MODULE_DIR . "wapanzhi_" . $data['mid'] . "_" . $id . ".html";
	                         file_put_contents($statcurl, $moduleapk);
	                         
	                         $d['staticurl'] = "wapanzhi_" . $data['mid'] . "_" . $id . ".html";
	                         $d['update_time'] = $d['create_time'] = time();
	                         $mod->where("id = '$id'")->save($d);
	                         $this->writelog('模板管理-anzhi安智模板内容管理添加了名称ID为['.$id.']为的模板', 'sj_anzhiwap_content', $id,__ACTION__ ,"","add");
	                         $this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Popularize/wapanzhicontent');
	                         $this -> success("添加成功,并生成静态页面！");
	                    }else{
						 $this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Popularize/wapanzhicontent_add');
						 $this -> error("模板还未生成或者已删除！");
					 	}
				}else{
					$this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Popularize/wapanzhicontent_add');
					$this->error("添加失败,发生错误！");
				}
			} else {
				$this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Popularize/wapanzhicontent_add');
                $this -> error("请按提示填写必要的项！");
			}
		} else {
			$module = M('wapanzhi_module');
			$res = $module->where("status = 1")->field('id,name')->order('create_time desc')->select();
			
			$this->assign('list', $res);
			$this->display();
		}    	
    }
    function wapanzhicontent_edit(){
    	$mod = M('wapanzhi_content');
    	
		if ($_POST){
			$id = trim($_POST['id']);
			$data['mid'] = trim($_POST['mid']);
			$data['M'] = trim($_POST['M']);
			$data['H'] = trim($_POST['H']);
			$data['update_time'] = time();
			
			if ($data['mid'] && $data['M'] && $data['H']){
				$tplpath = DIR_MODULE."wapanzhimodule_".$data['mid'].".html";
				if(!file_exists($tplpath)){
					$this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Popularize/wapanzhicontent_add');
					$this -> error("模板不存在！");
				}
				$log = $this -> logcheck(array('id' =>$id),'sj_wapanzhi_content',$data,$mod);
				if($mod->where("id='$id'")->save($data)){
	                     $replace[] = $this -> Mobile;
	                     $replace[] = $this -> HD;
	                     $to[] = $data['M'];
	                     $to[] = $data['H'];
	                     $m = file_get_contents($tplpath);
	                     
	                     if($m){
	                         $moduleapk = str_replace($replace, $to, $m);
	                         if(!file_exists(STATIC_MODULE_DIR)) mkdir(STATIC_MODULE_DIR, 0777);
	                         
	                         $statcurl = STATIC_MODULE_DIR . "wapanzhi_" . $data['mid'] . "_" . $id . ".html";
	                         file_put_contents($statcurl, $moduleapk);
	                         
	                         $d['staticurl'] = "wapanzhi_" . $data['mid'] . "_" . $id . ".html";
	                         $d['update_time'] = $d['create_time'] = time();
	                         $mod->where("id = '$id'")->save($d);
	                         $this->writelog('模板管理-anzhi安智模板内容管理编辑了名称ID为['.$id.']为的模板'.$log, 'sj_wapanzhi_content', $id,__ACTION__ ,"","edit");
	                         $this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Popularize/wapanzhicontent');
	                         $this -> success("编辑成功！");
	                    }else{
						 $this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Popularize/wapanzhicontent_edit/id/'.$id);
						 $this -> error("模板还未生成或者已删除！");
					 	}
				}else{
					$this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Popularize/wapanzhicontent_edit/id/'.$id);
					$this->error("添加失败,发生错误！");
				}
			} else {
				$this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Popularize/wapanzhicontent_edit/id/'.$id);
                $this -> error("请按提示填写必要的项！");
			}			
		} else {
			$id = $_GET['id'];
			$res = $mod->where("id = $id")->select();
			$mid = $res[0]['mid'];
			$module = M('wapanzhi_module');
			if ($r = $module -> where("id = $mid") -> field('id,name') ->select()){
				$res[0]['name'] = $r[0]['name'];
			}
			
			$this->assign('info', $res[0]);
			$this->display();
		}    	
    }
    function wapanzhicontent_del(){
		$id = $_GET['id'];
		if (!$id){
			$this->ajaxReturn(0, '未知错误！', 0);
			return ;
		}
		$mod = M('wapanzhi_content');
		$data['status'] = 0;
		$res = $mod->where("id=$id")->select();
		if ($mod->where("id=$id")->save($data)){
			$this -> writelog("模板管理-anzhi安智模板内容管理已删除id为{$id}的wapanzhi模板内容", 'sj_wapanzhi_content', $id,__ACTION__ ,"","del");
			unlink(STATIC_MODULE_DIR . $res[0]['staticurl']);
			$this->ajaxReturn(1, '删除成功！', 1);
		} else {
			$this->ajaxReturn(0, '删除 失败！', 0);
		}
		return ;    	
    }
    //更新发版后，更新模版中，市场版本号/安装包大小
    function update_module(){
        $mod = M('wap_module_content');
    	$module = M('wap_module');
        $mod1 = M('soft');
        $get_ids = $mod ->field('id,module_id') -> where("status = 1") -> select();

		if($get_ids){
			foreach ($get_ids as $k =>$val){
				$to         =   array(); 
				$replace    =   array(); 
				$data       =   array();
				$get_data1 = $mod -> where("id = '{$val['id']}'") -> select();
				$id = $get_data1[0]['id'];			
				$data['name'] = trim($get_data1[0]['name']);
				$data['pkgurl'] = trim($get_data1[0]['pkgurl']);
				$data['staticurl'] = trim($get_data1[0]['staticurl']);
				$data['star'] = trim($get_data1[0]['star']);
				$data['downnum'] = trim($get_data1[0]['downnum']);
				$data['summary'] = trim($get_data1[0]['summary']);
				$data['pic_1'] = trim($get_data1[0]['pic_1']);
				$data['pic_2'] = trim($get_data1[0]['pic_2']);
				$data['note'] = trim($get_data1[0]['note']);	
				$data['update_time'] = time();
				if ($data['pic2'] || $data['pic1']){
					$path=date('Ym/d/', time());
					$config = array(
						'multi_config' => array(
							'pic1' => array(
								'savepath' => UPLOAD_PATH. '/images/'. $path,
								'saveRule' => 'getmsec',
								'img_p_size' =>  1024*15,
							),
							'pic2' => array(
								'savepath' => UPLOAD_PATH. '/images/'. $path,
								'saveRule' => 'getmsec',
								'img_p_size' =>  1024*15,
							),
						),
					);
					$lists=$this->_uploadapk(0, $config);
					foreach($lists['image'] as $val) {
						if ($val['post_name'] == 'pic1') {
							$data['pic_1']= $val['url'];
							$replace[] = $this -> pic1tag;
							$to[] = IMGATT_HOST . $data['pic_1'];
						}
						if ($val['post_name'] == 'pic2') {
							$data['pic_2']= $val['url'];
							$replace[] = $this -> pic2tag;
							$to[] = IMGATT_HOST . $data['pic_2'];
						}
					}
				}
				$result = $module->where("status=1 and id = '{$val['module_id']}'")->select();
				$data['mtype'] = trim($result[0]['name']);
				if ($result && $data['name'] && $data['pkgurl'] && $data['star'] && $data['downnum'] &&$data['summary']){
						if(!file_exists(DIR_MODULE."wapmodule_".$result[0]['id'].".html")){
							$this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Popularize/WapModuleContentList');
							$this -> error("你现在的选用  模板-》" . $result[0]['name'] . " ！还未生成！");
						}
						$log_result = $this->logcheck(array('id'=>$id),'sj_wap_module_content',$data,$mod);
						$update_data = $mod-> where("id = '$id'") -> save($data);
						if($update_data){
							if(count($result) > 0){
								$replace[] = $this -> pkgtag;
								$replace[] = $this -> startag;
								$replace[] = $this -> downnumtag;
								$replace[] = $this -> destag;
								$replace[] = $this -> nametag;
								$replace[] = $this -> sizetag;
								$replace[] = $this -> vertag;
								$replace[] = $this -> last_refresh;
								$replace[] = $this -> puid;
								$to[] = $data['pkgurl'];
								if ($data['star']){
									$img = '';
									for ($i = 0; $i < $data['star']; $i++){
									   $img .= "<img src='images/star_01.png' />";
									}
									for ($j = 0; $j < 5 - $data['star']; $j++){
									   $img .= "<img src='images/star_03.png' />";
									}
									$to[] =$img;
								}
								$to[] = $data['downnum'];
								$to[] = nl2br($data['summary']);
								$Pupo = $this ->table_db = D('Sj.Popularlink');
								$Pupos = $Pupo->where("pu_link = '{$data['pkgurl']}' AND status = 1")->select();
								$to[] = $Pupos[0]['pkg_name'];
								$to[] = round($Pupos[0]['pkg_size'] / 1048576 ,2 ) . 'M';
								$to[] = $Pupos[0]['pkg_ver'];
								$package = $Pupos[0]['package'];
								$update_time_ = $mod1 -> where("package ='{$package}' AND status=1 and hide=1 ") -> order('softid desc') ->limit(1) -> select();                                   
								$update_time_1 = $update_time_[0]['last_refresh'];
								$to[] = date("Y-m-d", $update_time_1);
								$to[] = $Pupos[0]['puid'];
									
								$res = $mod->where("id = '$id'")->select();

								if (!$data['pic_1']){
									   $replace[] = $this -> pic1tag;
									   $to[] = IMGATT_HOST . $res[0]['pic_1'];
								}
								if (!$data['pic_2']){
									   $replace[] = $this -> pic2tag;
									   $to[] = IMGATT_HOST . $res[0]['pic_2'];
								}
								$m = file_get_contents(DIR_MODULE."wapmodule_".$result[0]['id'].".html");
								if($m){
									$moduleapk = str_replace($replace, $to, $m);
									if(!file_exists(STATIC_MODULE_DIR)) mkdir(STATIC_MODULE_DIR, 0777);
									file_put_contents(STATIC_MODULE_DIR . "wapmodule_" . $result[0]['id'] . "_" . $id . ".html", $moduleapk);
									$this->writelog('模板管理-Wap模板内容管理更新了名称ID为['.$id.']的模板'.$log_result, 'sj_wap_module_content', $id,__ACTION__ ,"","edit");
								}
							 }else{
									 $this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Popularize/WapModuleContentList');
									 $this -> error("你现在的" . $result[0]['name'] . "模板还未生成或已删除！");
							 }
						}else{
								$this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Popularize/WapModuleContent_edit/id/'.$id);
								$this->error("更新失败,发生错误！");
						}
				} 			
			}
		}       
        $this ->redirect('WapModuleContentList');
    }
// 类结束	
}