<?php
/**
 * 安智网产品管理平台 公用控制器
============================================================================
 * 版权所有 2009-2011 北京力天无限网络有限公司，并保留所有权利。
网站地址: http://www.goapk.com；
by:allen 2011/6/30
 * ----------------------------------------------------------------------------
 */

class PopularlinkAction extends CommonAction{
     protected $table_db;
     protected $upload_dir = UPLOAD_PATH; //'/data/att/m.goapk.com';
     //protected $upload_dir = 'F://popularapk/';
     protected $static_host = "http://m.anzhi.com/redirect.php?do=dlapk&puid=";
     function linkList(){
         $this -> table_db = D('Sj.Popularlink');
         // $p = $_GET['p']? $_GET['p'] : 1;
         import("@.ORG.Page");
         $pu_db = D('Sj.Popularparter');
         $condition['status'] = 1;
		 $condition1 = array();
		 if(isset($_GET['word']) && !empty($_GET['word'])){
			$key = trim($_GET['word']);
			$condition1['pu_name'] = array('like','%'.$key.'%');
			
		 }
		 if(isset($_GET['pid']) && !empty($_GET['pid'])){
			$pid = str_ireplace(' ','',trim($_GET['pid']));
			if(!is_numeric($pid))
			{
				$this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Popularlink/linkList');
                $this -> error("推广id必须为数字");
			}
			$condition1['id'] = $pid;
			
		 }
		 $puid_list = $pu_db -> where($condition1) -> getField('id,pu_name');
		 if($puid_list) $condition['puid'] = array("in",array_keys($puid_list));
         $count = $this -> table_db ->where($condition) -> count();
         $Page = new Page($count, 10);
         $linkList = $this -> table_db -> where($condition) -> limit($Page -> firstRow . ',' . $Page -> listRows)->order('upload_time desc') -> select();
         $pudata = $pu_db ->select();
         $pulist = array();
         foreach($pudata as $info){
             $pulist[$info['id']] = $info;
         }
         foreach($linkList as $idx => $linkinfo){
             $linkList[$idx]['pu_name'] = $pulist[$linkinfo['puid']]['pu_name'];
             $linkList[$idx]['pkg_size'] = round($linkinfo['pkg_size'] / 1048576 ,2 ) . 'M';
         }
         $Page -> setConfig('header', '篇记录');
         $Page -> setConfig('first', '<<');
         $Page -> setConfig('last', '>>');
         $show = $Page -> show();
         // if(isset($p)) $this -> assign('p','/p/'.$p);
		 $this -> assign('word',trim($_GET['word']));
		 $this -> assign('pid',trim($_GET['pid']));
         $this -> assign ("page", $show);
         $this -> assign('pulist', $pulist);
         $this -> assign('linkList', $linkList);
		 $this->url_suffix = $this->get_url_suffix(array('pid','word','p','lr'));
         $this -> display('linkList');
         }
     function addPuparter(){
         $pu_db = D('Sj.Popularparter');
         $info['pu_name'] = trim($_GET['pu_name']);
         if($info['pu_name'] == ""){
             $this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Popularlink/uploadForm');
             $this -> error("推广名不能为空！！");
         }
         $info['create_time'] = time();
         $result = $pu_db -> where(array('pu_name' =>$info['pu_name'])) -> select();
         if(count($result) > 0){
             $this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Popularlink/uploadForm');
             $this -> error("该推广已经存在");
             }
         $affect = $pu_db -> add($info);
         if($affect > 0){
			$this->writelog('添加推广 为 id 为:'.$affect,'pu_popularparter',$affect,__ACTION__ ,"","add");
             $this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Popularlink/uploadForm');
             $this -> success("推广生成成功！");
             }else
            {
             $this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Popularlink/uploadForm');
             $this -> error("取消显示失败！");
             }

         }
     function uploadForm(){
         $pu_db = D('Sj.Popularparter');
         // $p = $_GET['p']? $_GET['p'] : 1;
		 $this->url_suffix = $this->get_url_suffix(array('pid','pu_name','p','lr'));
		 //构造搜索条件
		 $map = array();
		 if(isset($_GET['pid']) && $_GET['pid'] != "")
		 {
			if(!is_numeric(trim($_GET['pid'])))
			{
				$this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Popularlink/uploadForm');
                $this -> error("推广id必须为数字");
			}
			$map['id'] = trim($_GET['pid']);
			$this->pid = trim($_GET['pid']);
		 }
		  if(isset($_GET['pu_name']) && $_GET['pu_name'] != "")
		 {
			$map['pu_name'] = array('like',"%".trim($_GET['pu_name'])."%");
			$this->pu_name = trim($_GET['pu_name']);
		 }
		 $this->search = true;
         import("@.ORG.Page");
         $count = $pu_db -> where($map) -> count();
         $Page = new Page($count, 10);
         $pu_list = $pu_db -> where($map) -> limit($Page -> firstRow . ',' . $Page -> listRows) -> order('create_time desc') -> select();
         $Page -> setConfig('header', '篇记录');
         $Page -> setConfig('first', '<<');
         $Page -> setConfig('last', '>>');
         $show = $Page -> show();
         if(isset($p)) $this -> assign('p','/p/'.$p);
          $this -> assign ("page", $show);
         $this -> assign('pu_list', $pu_list);
         $this -> display();
         }
     function updateLink(){
         $id = (int)$_GET['id'];
         $puid = $_GET['puid'];
         // $p    = $_GET['p'] ? $_GET['p'] : 1;
         $pu_db = D('Sj.Popularparter');
         $pu_list = $pu_db -> select();
         $table_db = D('Sj.Popularlink');
         $link = $table_db->where("id = $id")->select();
         
         $this->assign('link', $link[0]);
         $this -> assign('p',$p);
         $this -> assign('puid', $puid);
         $this -> assign('pu_list', $pu_list);
		 $this->url_suffix = $this->get_url_suffix(array('pid','word','p','lr'));
         $this -> display();
         }
     function updatePuLink(){
         $this -> table_db = D('Sj.Popularlink');
         $pkg_name = $_FILES['pkg_name'];
         $tmp_file = $pkg_name['tmp_name'];
         $file_name = $pkg_name['name'];
         $file_alias = trim($_POST['alias']) ? trim($_POST['alias']) :'';
         $p = $_POST['p'];
         $map = array();
         $map['puid'] = $_POST['pu_id'];
         $map['status'] = 1;
         $data = array();
         $data['status'] = 0;
		 $url_suffix = $_POST['url_suffix'];
         if($_POST['pu_id'] == ""){
             $this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Popularlink/linkList'.$url_suffix);
             $this -> error("请选择推广名");
             }
         if($pkg_name['name'] == ""){
             $this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Popularlink/linkList'.$url_suffix);
             $this -> error("请上传apk文件");
             }
		$log_result = $this->logcheck(array('puid'=>$_POST['pu_id']),'pu_pupolarlink',$data,$this->table_db);
         $affect_update = $this -> table_db -> where($map) -> save($data);
         if($affect_update > 0){
             $fileinfo['puid'] = $_POST['pu_id'];
             $fileinfo['pkg_name'] = trim($file_name);
			 $fileinfo['alias'] = $file_alias;
             $fileinfo['upload_time'] = time();
             $fileinfo['status'] = 1;
             $fileinfo['pu_link'] = $this -> static_host . $fileinfo['puid'];
             /*---------*/
                $tmp_filename = $pkg_name['name'];
                $uploadtime = $fileinfo['upload_time'];
                $extpos = strrpos($tmp_filename,".");
                $ext = substr($tmp_filename,$extpos);
                $fname = substr($tmp_filename,0,-4);
                $upload_file_name = $fname.'_'.$uploadtime.$ext;
                $tmp_file_url = $pkg_name["tmp_name"];
                $time_array = explode("-",date("Y-m-d",$uploadtime));
                $timedir = $time_array[0].$time_array[1].'/'.$time_array[2];      //201108/09格式
                $path  = '/apk/'.$timedir.'/';                                            //文件夹
                $fileinfo['url'] = $path;
                $this -> upload_dir .= $path;
                 //if(!is_dir($this -> upload_dir)) mkdir($this -> upload_dir, 777);
                 $this ->_mkdir($this -> upload_dir);        //递归创建文件夹
             /*---------*/
             //if(!is_dir($this -> upload_dir)) mkdir($this -> upload_dir, 777);
             $fileinfo['pkg_name'] = $upload_file_name;
             $affect_file = move_uploaded_file($tmp_file, $this -> upload_dir . $upload_file_name);
             if($affect_file == true){
				 $fileinfo['pkg_size'] = $pkg_name['size'];
             	 $fileinfo['pkg_ver'] = $this->go_apk_vername($this -> upload_dir . $upload_file_name);
             	 $fileinfo['pkg_vercode'] = $this->go_apk_vercode($this -> upload_dir . $upload_file_name);
                 $fileinfo['package'] = $this->go_apk_package($this -> upload_dir . $upload_file_name);
                 $affect = $this -> table_db -> add($fileinfo);
                 if($affect > 0){
					 include_once SERVER_ROOT. '/tools/functions.php';
					 go_make_links($this -> upload_dir . $upload_file_name); //添加大写link
					 $this -> writelog("更新推广包 id为".$affect,'pu_popularparter',$affect,__ACTION__ ,"","edit");
                     $this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Popularlink/linkList'.$url_suffix);
                     $this -> success("推广链生成成功！");
                     }else{
                     $this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Popularlink/linkList'.$url_suffix);
                     $this -> success("数据添加失败！");
                     }
                 }else
                {
                 $this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Popularlink/updateLink'.$url_suffix);
                 $this -> error("取消显示失败！！");
                 }
             }else{
             $this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Popularlink/updateLink'.$url_suffix);
             $this -> error("数据更新失败");
             }
         }
     function Popularupload(){

        /**
         * `id`  '推广渠道id',
         * `pu_name` '推广名',
         * `pkg_name` '软件包名',
         * `pu_link` '推广链接',
         * `status`'状态 0 失效，1 有效',
         * `upload_time` '上传时间'
         */
         $this -> table_db = D('Sj.Popularlink');
         $pkg_name = $_FILES['pkg_name'];
         $tmp_file = $pkg_name['tmp_name'];
         $file_name = $pkg_name['name'];
		 $dl_alias = trim($_POST['alias']) ? trim($_POST['alias']) :'';
		 $fileinfo['puid'] = escape_string($_POST['puid']);
         $fileinfo['upload_time'] = time();
         $fileinfo['status'] = 1;
		 $fileinfo['alias'] = $dl_alias;
         $fileinfo['pu_link'] = $this -> static_host . $fileinfo['puid'];
         $result = $this -> table_db -> where('puid = ' . $fileinfo['puid'] . ' and status = 1') -> select();
         if($_POST['puid'] == ""){
             $this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Popularlink/linkList');
             $this -> error("请选择推广名");
             }
         if($pkg_name['name'] == ""){
             $this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Popularlink/linkList');
             $this -> error("请上传apk文件");
             }
         if(count($result) > 0){
             $this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Popularlink/linkList');
             $this -> error("该推广已经存在，您可以更新推广");
             }
        $tmp_filename = $pkg_name['name'];
        $uploadtime = $fileinfo['upload_time'];
        $extpos = strrpos($tmp_filename,".");
        $ext = substr($tmp_filename,$extpos);
        $fname = substr($tmp_filename,0,-4);
        $upload_file_name = $fname.'_'.$uploadtime.$ext;
        $tmp_file_url = $pkg_name["tmp_name"];
        $time_array = explode("-",date("Y-m-d",$uploadtime));
        $timedir = $time_array[0].$time_array[1].'/'.$time_array[2];      //201108/09格式
        $fileinfo['url'] = $timedir;
        $path  = '/apk/'.$timedir.'/';                                            //文件夹
        $fileinfo['url'] = $path;
        $this -> upload_dir .= $path;
        $fileinfo['pkg_name'] = $upload_file_name;
         //if(!is_dir($this -> upload_dir)) mkdir($this -> upload_dir, 777);
         $this ->_mkdir($this -> upload_dir);        //递归创建文件夹
         $affect_file = move_uploaded_file($tmp_file, $this -> upload_dir . $upload_file_name);
         $fileinfo['pkg_size'] = $pkg_name['size'];
         if($affect_file == true){
         	$fileinfo['pkg_ver'] = $this->go_apk_vername($this -> upload_dir . $upload_file_name);
         	$fileinfo['pkg_vercode'] = $this->go_apk_vercode($this -> upload_dir . $upload_file_name);
                $fileinfo['package'] = $this->go_apk_package($this -> upload_dir . $upload_file_name);
                
             $affect = $this -> table_db -> add($fileinfo);
             if($affect > 0){
				 include_once SERVER_ROOT. '/tools/functions.php';
				 go_make_links($this -> upload_dir . $upload_file_name); //添加大写link
				 $this -> writelog("添加推广包 id为".$affect,'pu_popularparter',$affect,__ACTION__ ,"","add");
                 $this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Popularlink/linkList');
                 $this -> success("推广链生成成功！");
                 }else{
                 $this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Popularlink/linkList');
                 $this -> error("数据添加失败！");
                 }
             }else
            {
             $this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Popularlink/uploadForm');
             $this -> error("取消显示失败！");
             }
         }
     function popularList(){
         $puid = $_GET['puid'];
         $this -> table_db = D('Sj.Popularlink');
         $pudb = D('Sj.Popularparter');
         $pudata = $pudb -> where('id = ' . $puid) -> select();
         $popularlist = $this -> table_db -> where(array('puid'=>$puid)) -> order('status desc') -> select();
         $this -> assign('list', $popularlist);
         $this -> assign('pudata' , $pudata[0]);
         $this -> display();
         }
     function modifyApk(){
         $this -> table_db = D('Sj.Popularlink');
         $id = $_GET['id'];
         $puid = $_GET['puid'];
         $update['status'] = 0;
		 $zh_puid['puid']=$puid;
		 $zh_puid['status']=1;
         $popularing = $this -> table_db -> where($zh_puid) -> save($update);
         if($popularing > 0){
             $data['status'] = 1;
             $affect = $this -> table_db -> where(array('id' =>$id)) -> save($data);
             if($affect > 0){
                 $this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Popularlink/popularList/puid/'.$puid);
				  $this -> writelog("更换推广包 id为".$id,'pu_pupolarlink',$id,__ACTION__ ,"","edit");
                 $this -> success("推广链修改成功！");
                 }else{
                 $this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Popularlink/linkList');
                 $this -> error("数据修改失败！");
                 }
             }else{
             $this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Popularlink/linkList');
             $this -> error("数据修改失败！！");
             }
         $this -> display();
         }
     function updatePupo(){
         $puid = $_GET['id'];
         $p   = $_GET['p']? $_GET['p'] :1;
         import("@.ORG.Page");
         $pu_db = D('Sj.Popularparter');
         $count = $pu_db -> count();
         $Page = new Page($count,10);
         $page -> firstRow = $p;
         $result = $pu_db -> where(array('id'=>$puid)) -> select();
         $pu_list = $pu_db ->limit($Page -> firstRow . ',' . $Page -> listRows)-> select();
         $Page -> setConfig('header', '篇记录');
         $Page -> setConfig('first', '<<');
         $Page -> setConfig('last', '>>');
         $show = $Page -> show();
         $this -> assign("p",'/p/'.$p);
         $this -> assign ("page", $show);
         $this -> assign('pu_list', $pu_list);
         $this -> assign('pu', $result[0]);
         $this -> display('uploadForm');
         }
     function updatePuparter(){
         $puid = $_GET['pu_id'];
         $p    = $_GET['p'] ? $_GET['p'] : 1;
         $data['pu_name'] = trim(escape_string($_GET['pu_name']));
         if($data['pu_name'] == ""){
             $this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Popularlink/uploadForm/p/'.$p);
             $this -> error("推广名不能为空！！");
             }
         $pu_db = D('Sj.Popularparter');
         $result = $pu_db -> where('pu_name = \'' . $data['pu_name'] . '\'') -> select();
         if(count($result) > 0){
             $this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Popularlink/uploadForm/p/'.$p);
             $this -> error("推广名已存在！！");
             }
         $log_result = $this->logcheck(array('id'=> $puid),'pu_popularparter',$data,$pu_db);
         $affect = $pu_db -> where(array('id'=> $puid)) -> save($data);
         if($affect > 0){
			 $this -> writelog("修改推广名 id:".$puid.".{$log_result}",'pu_popularparter',$puid,__ACTION__ ,"","edit");
             $this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Popularlink/uploadForm/p/'.$p);
             $this -> success("推广名修改成功！");
             }else{
             $this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Popularlink/uploadForm/p/'.$p);
             $this -> error("数据修改失败！！");
             }
         }
         function deleteLink() {
             $this -> table_db = D('Sj.Popularlink');
             $id = $_GET['id'];
             $data['status'] = 0;
             $p    = $_GET['p'] ? $_GET['p'] : 1;
             $affect = $this -> table_db -> where(array('id' => $id)) ->save($data);
             if($affect > 0){
				$this->writelog('删除该推广链 id:'.$id,'pu_pupolarlink',$id,__ACTION__ ,"","del");
                 $this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Popularlink/linkList/p/'.$p);
                 $this -> success("该推广已经删除！");
                 }else{
                 $this -> error("数据删除失败！！");
             }
         }
            //递归创建文件夹
        function _mkdir($path, $mod = '0755', $recursive = false){
        static $loop = 0;
        $loop ++;
        if (file_exists($path) || @mkdir($path, $mod, $recursive) ) {
            return true;
        }
        if($loop >20) return false;
        return $this -> _mkdir(dirname($path)) && $this -> _mkdir($path, $mod, $recursive);
        }
		function updateAlias(){
			$id = $_GET['id'];
			$ext_arr = explode("&",$id);
			$id = $ext_arr[0];
			$this -> table_db = D('Sj.Popularlink');
			$pu_db = D('Sj.Popularparter');
			$pu_info = $this -> table_db -> where(array('id' => $id)) -> select();
			$pu_name = $pu_db -> where(array("id" => $pu_info[0]['puid'])) -> getField("pu_name");
			$this -> assign('pu_name',$pu_name);
			$this -> assign('pu_info',$pu_info[0]);
			$this -> display("updateAlias");
		}
		function updateAlias_do(){
		    $id = $_GET['id'];
			$alias = trim($_GET['alias']);
			if(empty($alias)) $this -> error("别名不能为空！");
			$pulink_DB = D('Sj.Popularlink');
			$count = $pulink_DB -> where(array('alias'=> $alias)) -> count();
			if($count > 0){
			  $this -> error("别名已存在！");
			}
			$data = array();
			$data['alias']= $alias;
			//$affect = $pulink_DB -> query("update `pu_pupolarlink` set `alias`='{$alias}' where id =".$id);
            $log_result = $this->logcheck(array('id'=> $id),'pu_pupolarlink',$data,$pulink_DB);
			$affect = $pulink_DB -> where(array('id' => $id)) -> save($data);
			if($affect){
			$this->writelog('修改别名为 id 为:'.$id.$log_result,'pu_pupolarlink',$id,__ACTION__ ,"","edit");
			 $this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Popularlink/linkList');
			 $this -> success("别名修改成功！！");
			}else{
			 $this -> error("别名修改失败！！");			
			}
		}
		function go_apk_vername($file){
		    if (!is_file($file))
		        return false;
		
		    $bin_path = '/data/www/wwwroot/config/gnu';
		    $info = shell_exec("{$bin_path}/aapt d badging \"{$file}\" 2>/dev/null");
		
			if (preg_match("/package: name='([^']*?)' versionCode='([^']*?)' versionName='([^']*?)'/", $info, $m)) {
				/*$result['packagename'] = $m[1];
				$result['versionCode'] = $m[2];
				$result['versionName'] = $m[3];
				if (empty($result['packagename']) || empty($result['versionCode'])) {
					return false;
				}*/
				$versionName = $m[3];
			}
			return $versionName;
		}		

               function go_apk_vercode($file){
		    if (!is_file($file))
		        return false;
		
		    $bin_path = '/data/www/wwwroot/config/gnu';
		    $info = shell_exec("{$bin_path}/aapt d badging \"{$file}\" 2>/dev/null");
		
			if (preg_match("/package: name='([^']*?)' versionCode='([^']*?)' versionName='([^']*?)'/", $info, $m)) {
				/*$result['packagename'] = $m[1];
				$result['versionCode'] = $m[2];
				$result['versionName'] = $m[3];
				if (empty($result['packagename']) || empty($result['versionCode'])) {
					return false;
				}*/
				$versionCode= $m[2];
			}
			return $versionCode;
		}
                
                function go_apk_package($file){
		    if (!is_file($file))
		        return false;
		
		    $bin_path = '/data/www/wwwroot/config/gnu';
		    $info = shell_exec("{$bin_path}/aapt d badging \"{$file}\" 2>/dev/null");
		
			if (preg_match("/package: name='([^']*?)' versionCode='([^']*?)' versionName='([^']*?)'/", $info, $m)) {
				/*$result['packagename'] = $m[1];
				$result['versionCode'] = $m[2];
				$result['versionName'] = $m[3];
				if (empty($result['packagename']) || empty($result['versionCode'])) {
					return false;
				}*/
				$packagename= $m[1];
			}
			return $packagename;
		}
                
                

     }
?>
