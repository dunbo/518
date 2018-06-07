<?php

ini_set('display_errors', 1);
ini_set('memory_limit','2048M');
error_reporting(E_ALL);
set_time_limit(0);
require_once(dirname(__FILE__).'/../upload_init.php');
$ip = getServerIp();
if ($ip != '192.168.1.18' && $ip != '192.168.0.99') {
    exit;
}
$worker->addFunction("batch_upload", "batch_upload_worker");
while ($worker->work());

// XXX： 代码规范化
// XXX： 不要留没意义的log

// TODO: make this in common headers
function my_log($msg) {
    $date = date('[Y-m-d H:i:s]', time());
    $args = func_get_args();
    $log = $date. " ". call_user_func_array('sprintf', $args). "\n";
    file_put_contents('/tmp/batch_upload_worker.log', $log, FILE_APPEND);
}

function batch_upload_worker($job)
{
    if ( !($p = unserialize($job->workload())) ) {
        return False;
    }
    $new_package = $p['new_package'];
    $session = $p['session'];
    $id = $p['id'];
    my_log("`${new_package}': %s", json_encode(array('id' => $id, 'session' => $session)));
    if (empty($new_package) || empty($session) || empty($id)) {
        return False;
    }
    $result = is_unzip(UPLOAD_DIR. "/". $new_package, $id);
    my_log("`${new_package}': %s", $result ? "passed" : "rejected");
    if($result!==false){
        my_log("`${new_package}': started");
        unzip($new_package, $id, $session,$result);
        my_log("`${new_package}': ended");
    }
}

// apply file name rules
function check_one_folder($list, $spec) {
    $map = array();
    foreach ($list as $n) {
        $p = strrpos($n, '.');
        if ($p === false)
            return false;
        $ext = substr($n, $p + 1);
        $ext = strtolower($ext);
        if (!isset($spec[$ext]))
            return false;
        if (!isset($map[$ext]))
            $map[$ext] = 0;
        $map[$ext] += 1;
    }
    foreach ($map as $name => $count) {
        $expect = $spec[$name];
        if (is_array($expect)) {
            $min = $expect[0];
            if ($count < $min)
                return false;
            $max = $expect[1];
            if ($count > $max)
                return false;
        } else {
            if ($count != $expect)
                return false;
        }
    }
    return true;
}

//解压前的判定
function is_unzip($file,$id){
        $model = new GoModel();
	$file_name_list=array();
	$file_list=array();
	$zh_database = DATABASE;
	$package_table="sj_upload_package";
	$array_key=array("jpg","apk","txt");//后缀名称
	$status=4;
	//$dir=UPLOAD_DIR;
	//$tmpdir=UPLOAD_TMP;
	//echo realpath(".")."/".$dir."/".$file;exit;
	///data/att/zh_upload/uploads/20120925194953_9448.zip
        $zip=zip_open($file);
	//echo realpath(".")."/".$dir."/".$file;exit;
    if(!is_resource($zip)) {
        $e = "无法读取`{$file}'。";
        echo "${e}\n";
        upload_package($model,$status,$e,$package_table,$id,$zh_database);
        return false;
    }
    $e='';
    while($zip_entry=zip_read($zip)) {
       $zdir=dirname(zip_entry_name($zip_entry));
	   $zdir_array=explode("/",$zdir);
       $zname=zip_entry_name($zip_entry);
	   //echo $zname."</br>";
	   $file_count=substr_count($zname ,'/');
	   if($file_count<=0){
			$e="根目录下存在文件'{$zname}'不符合打包结构";
			$result=upload_package($model,$status,$e,$package_table,$id,$zh_database);
			return false;
			//exit;
		}
	   $zname_array=explode("/",$zname);
	   $count=count($zname_array);
	   //echo $count."</br>";
		if($count>3){
			$e="文件名'{$zname}'不符合打包结构";
			$result=upload_package($model,$status,$e,$package_table,$id,$zh_database);
			return false;
			//exit;
		}
		if($count==2){
			if($zname_array[1]!=""){
				$e="根目录下存在文件'{$zname_array[1]}'不符合打包结构";
				$result=upload_package($model,$status,$e,$package_table,$id,$zh_database);
				return false;
				//exit;
			}
		}
		$ts=explode(".",$file);
		if($count==3&&$zname_array[2]!=''){
			$file_name_list[]=$zname_array[1];
			if (!ereg("^[a-zA-Z0-9_.]+$",$zname_array[1])){
				$e="内部文件夹名不符合命名规则，文件名只能带大小写字母、数字或英文下划线";
				$result=upload_package($model,$status,$e,$package_table,$id,$zh_database);
				return false;
				//exit;
			}else{
				if($zname_array[2]!="Thumbs.db"){
					$file_list[$zname_array[1]][]=$zname_array[2];
				}
			}
		}
	}
        //supwater
        $err_arr = array();
	foreach($file_list as $k=>$v){
		foreach($v as $m=>$n){
			$file_array=explode(".",$n);
			$count_file=count($file_array);
			if (!ereg("^[a-zA-Z0-9_.]+$",$n)){
				$e="文件夹{$k}下文件名不符合命名规则，文件名只能带大小写字母、数字或英文下划线";
                                $err_arr[$k]=$e;
				//$result=upload_package($model,$status,$e,$package_table,$id,$zh_database);
				//return false;
				//exit;
			}else{
				if(!in_array(strtolower($file_array[$count_file-1]),$array_key)){
					$e="文件夹{$k}下含有除了jpg、apk、txt以外的文件{$file_array[$count_file-1]}";
                                        $err_arr[$k]=$e;
					//$result=upload_package($model,$status,$e,$package_table,$id,$zh_database);
					//return false;
					//exit;
				}else{
					$file_list[$k][$file_array[$count_file-1]]+=1;
				}
			}
		}
	}
	foreach($file_list as $key=>$val){
		if($val['jpg'] <= 1 || $val['jpg']>5){
                        if(strlen($e)==0)
                        {
			    $e="文件夹{$key}下图片的数量不够或者超出";
                            $err_arr[$key]=$e;
                        }
			//$result=upload_package($model,$status,$e,$package_table,$id,$zh_database);
			//return false;
			//exit;
		}
		if($val['txt']!=1){
                        if(strlen($e)==0)
                        {
                            $e="文件夹{$key}下缺少文本或者文本过多";
                            $err_arr[$key]=$e;
                        }
			//$result=upload_package($model,$status,$e,$package_table,$id,$zh_database);
			//return false;
			//exit;
		}if($val['apk']!=1){
                        if(strlen($e)==0)
                        {
                            $e="文件夹{$key}下apk的缺少或者超出";
                            $err_arr[$key]=$e;
                        }
			//$result=upload_package($model,$status,$e,$package_table,$id,$zh_database);
			//return false;
			//exit;
		}
	}
        if(empty($file_list)){
            return false;
        }else{
            return $err_arr;
        }
}


//开始解压
//unzip($new_package,$id);
function unzip($file,$id,$session,$err_arr){
    echo "start unzip\n";
    $model = new GoModel();
    $file_name_list=array();
    $zh_database = DATABASE;
    $package_table="sj_upload_package";
    $soft_table="sj_upload_package_soft";
	//取前缀

	$get_upload_user_name=array(
		'where'=>array(
			'uid'=>$session['bbs_uid'],
			'status'=>1,
		),
		'table' => 'sj_upload_user',
		'field' => 'name',
	);
	$user_result= $model->findOne($get_upload_user_name,$zh_database);
	$config_name=$user_result['name'];
    $zh_soft="sj_soft";
    $soft_file_table="sj_soft_file";
    $soft_note_table="sj_soft_note";
    $soft_thumb_table="sj_soft_thumb";
    $soft_permission_details_table="sj_soft_permission_details";
    $soft_permission_table="sj_soft_permission";
    $array_key=array("jpg","apk","txt");//后缀名称
    $status=4;
    $dir=UPLOAD_DIR;
    $tmpdir=UPLOAD_TMP;
    $zip=zip_open($dir."/".$file);
    if(!is_resource($zip)) {return("Unable to proccess file '{$file}'");}
    echo "has zip\n";
    $e='';
    while($zip_entry=zip_read($zip)) {
        $zname=zip_entry_name($zip_entry);
        $zdir=dirname($zname);
        $zdir_array=explode("/",$zdir);
        $zname_array=explode("/",$zname);
        $count=count($zname_array);
        $ts=explode(".",$file);
        $zdir_array[0]=$ts[0];
        $zdir=implode("/",$zdir_array);
        
        if($count==2){
            $zname=$ts[0]."/".$zname_array[1];
        }else{
            $zname_array[0]=$ts[0];
            $zname=implode("/",$zname_array);
        }
        if($count==3&&$zname_array[2]!=''){
            $file_name_suffix=explode(".",$zname_array[2]);//获取后缀名
            $count_suffix=count($file_name_suffix);
            $zname_array[2]=$tmpdir."/".$ts[0]."/".$zname_array[1]."/".$zname_array[2];
			if(strtolower($file_name_suffix[$count_suffix-1]!="db")){
				$file_name_list[$zname_array[1]][strtolower($file_name_suffix[$count_suffix-1])][]=$zname_array[2];
			}
        }
        if(!zip_entry_open($zip,$zip_entry,"r")) {
            $e.="Unable to proccess file '{$zname}'";
            $result=upload_package($model,$status,$e,$package_table,$id,$zh_database);
            return false;
        }
        $zdir=$tmpdir."/".$zdir;
        $zname=$tmpdir."/".$zname;
        if(!is_dir($zdir)) {
            $re = mkdir($zdir,0755,true);
            if (!$re) {
            zip_entry_close($zip_entry);
            zip_close($zip);
            return false;
            }
        }
        	
        $zip_fs=zip_entry_filesize($zip_entry);
        if($zip_fs == 0) continue;
        $zz=zip_entry_read($zip_entry,$zip_fs);
        $z=fopen($zname,"w");
        fwrite($z,$zz);
        fclose($z);
        zip_entry_close($zip_entry);
    }
    zip_close($zip);
    $dir1=$tmpdir."/".$ts[0];//遍历的文件目录
    if(empty($e)){//如果没有错误信息返回往下执行
        $forder_e=file_list($dir1);
        if(!empty($forder_e)){
            $result=upload_package($model,$status,$forder_e,$package_table,$id,$zh_database);
            return false;
        }else{//遍历文件夹对文件进行判定
            if (empty($file_name_list)) {
			    $file_e = "空目录，请修改后重新上传";
			    $result=upload_package($model,$status,$file_e,$package_table,$id,$zh_database);
				return false;
			}
                        foreach($err_arr as $k=>$v)
                        {
                            $newerr[$k]['msg']=$v;
                            $newerr[$k]['apk']=$file_name_list[$k]['apk'];
                            $newerr[$k]['txt']=$file_name_list[$k]['txt'];
                            unset($file_name_list[$k]);
                        }


            foreach($file_name_list as $k=>$v){
                $count_type=count($v);
                if($count_type!=3){
                    $file_e="内部文件架构错误，请修改后重新上传";
                    $result=upload_package($model,$status,$file_e,$package_table,$id,$zh_database);
                    return false;
                }else{
                    //处理文件了
                    $file=$v['txt'][0];
                    $size=go_file_size($v['apk'][0]);
                    $txt_array=get_txt($file, $session["bbs_uid"]);//获取txt文本里面的信息
                    //失败插入

                    $realnamearr = explode('/',$v['apk'][0]);
                    $realname = array_pop($realnamearr);
                    $dev_package_soft=array(
						'cid' => $id,
						'dev_name' => $session['user_name'],
						'dev_id' => $session['bbs_uid'],
						'folder' => $k,
						'file_name' => $v['apk'][0],
                                                'file_realname' => $realname,
						'book_name' => $txt_array['softname'],
						'size' =>$size,
						'name' => $txt_array['dev_name'],
						'remark' => $txt_array['intro'],
						'__user_table' => 'sj_upload_package_soft'
						);

						if(!empty($txt_array['msg'])){
						    //插入数据库软件表
						    $status=0;
						    $msg=$txt_array['msg'];
						    $result=add_package_soft($dev_package_soft,$msg,$status,$model,$zh_database);
						    continue;
						}
						//图片处理
						$img_info=get_img_list($v['jpg']);
						if(!empty($img_info["msg"])){
						    //插入数据库软件表
						    $status=0;
						    $msg=$img_info["msg"];
						    $result=add_package_soft($dev_package_soft,$msg,$status,$model,$zh_database);
						    continue;
						}else{
						    foreach($img_info as $img_key=>$img_val){
						        if($img_key!="msg"){
						            $fields_img[$img_key]="@".$img_val;
						        }
						    }
						}
						//fields
						//echo "haohaoahaoahoahaohaoahaohaoahaohaoahoahbaoahaohaohaohaohaohao</br>";
							
						//print_r($fields);
						//echo "haohaoahaoahoahaohaoahaohaoahaohaoahoahbaoahaohaohaohaohaohao</br>";
						$fields_other = array(
						'do' => 'upload',
						'from' => 'developer',
						'name' => trim($txt_array['softname']),
                        'package_new' => $txt_array['package_new'],
						'tags' => trim($txt_array['tags']),
						'developer_id' =>$session['bbs_uid'],
						'developer_name' => trim($txt_array['dev_name']),
						'category_id' => CATEGORY_ID,//先写死的
						// 'costs' => 0,
						'description' => trim($txt_array['intro']),
						// 'hide'=>1,
						'batch'=>true,
						//'operator_deny'=>0,
						//'granted'=>2,
						// 'upload_tm'=>time(),
						// 'last_refresh'=>time(),
						//'status'=>1,
						'apk' => "@".$v['apk'][0],
						//'update_type' => 2
						);
					$ebook_uid = load_config('ebook/uid');
					if (isset($ebook_uid[$session["bbs_uid"]])) { //如果为ebook用户
					    $fields_other['costs'] = $txt_array['costs'];
					    $fields_other['category_id'] = trim($txt_array['category_id']);
					}
					
					$info_uid = load_config('info/uid');
					if (isset($info_uid[$session['bbs_uid']])) { //如果为资讯的用户
						$fields_other['category_id'] = load_config('info/cid');
					}
					
					$info_uid = load_config('bbs/uid');
					if (isset($info_uid[$session['bbs_uid']])) { //如果为论坛的用户
						$fields_other['category_id'] = trim($txt_array['category_id']);
					}
					
					$game_uid = load_config('game/uid');
					if (isset($game_uid[$session['bbs_uid']])) { //如果为游戏的用户
						$fields_other['costs'] = $txt_array['costs'];
					    $fields_other['category_id'] = trim($txt_array['category_id']);
					}

						$fields=array_merge($fields_img,$fields_other);
						//var_dump($fields);
						$host=load_config("upload_host_list");
						$ip=load_config("upload_host_ip");
						$h = curl_init("http://{$ip}/upload.php");
						curl_setopt($h, CURLOPT_POST, true);
						curl_setopt($h, CURLOPT_HTTPHEADER, array("Host:{$host}"));
						curl_setopt($h, CURLOPT_RETURNTRANSFER, true);
						curl_setopt($h, CURLOPT_POSTFIELDS, $fields);
						$ret = curl_exec($h);
						//var_dump(curl_getinfo($h));
						if ($ret === false)
						$ret = curl_error($h);
						curl_close($h);
						echo "upload.php: ". $ret."\n";
						$ret_array=json_decode(trim($ret),true);
						if(!$ret_array || $ret_array['status']=="error"){
						    $status=0;
						    $msg=$ret_array ? $ret_array['data']['reason'] : $ret;
							$result=add_package_soft($dev_package_soft,$msg,$status,$model,$zh_database);
							continue;
						}elseif($ret_array['status']=="ok"){
						    $status=1;
						    $msg="";
						    $result=add_package_soft($dev_package_soft,$msg,$status,$model,$zh_database,$ret_array['data']['softid']);
						    continue;
						}
                }
            }

                        foreach($newerr as $k=>$v)
                        {
                            $realnamearr = explode('/',$v['apk'][0]);
                            $realname = array_pop($realnamearr);
                            $file=$v['txt'][0];
                            $size=go_file_size($v['apk'][0]);
                            $txt_array=get_txt($file,  $session["bbs_uid"]);//获取txt文本里面的信息
                            //失败插入
                            $dev_package_soft=array(
                                    'cid' => $id,
                                    'dev_name' => $session['user_name'],
                                    'dev_id' => $session['bbs_uid'],
                                    'folder' => $k,
                                    'file_name' => iconv('gbk','utf-8',$v['apk'][0]),
                                    'file_realname' => iconv('gbk','utf-8',$realname),
                                    //'book_name' => iconv('gbk','utf-8',$txt_array['softname']),
                                    'book_name' => $txt_array['softname'],
                                    'size' =>$size,
                                    //'name' => iconv('gbk','utf-8',$txt_array['dev_name']),
                                    'name' => $txt_array['dev_name'],
                                    //'remark' => iconv('gbk','utf-8',$txt_array['intro']),
                                    'remark' => $txt_array['intro'],
                                    '__user_table' => 'sj_upload_package_soft'
                            );
			    $result=add_package_soft($dev_package_soft,$v['msg'],0,$model,$zh_database);
                        }
                        

            $package_status=2;
            $package_msessage="处理完成";
            upload_package($model,2,$package_msessage,$package_table,$id,$zh_database);
        }
    }
}
//获取图片数组
function get_img_list($list){
    $e="";
    $img_list=array();
    foreach($list as $k=>$val){
        $img_array=explode("/",$val);
        $count_img=count($img_array);
        $img_name=substr($img_array[$count_img-1],0, strrpos($img_array[$count_img-1], '.'));
        if(!ereg("^[1-5]{1}$",$img_name)){
            $e.="图片{$img_array[$count_img-1]}的命名不符合规范，请重新命名。标准命名规则为1-5任意一个值";
        }
	    
		if (go_file_size($val)>IMG_SIZE) {
		    $kb = IMG_SIZE/1024;
		    $e.="图片{$img_array[$count_img-1]}的大小不符合规范，标准大小为{$kb}K";
		}
		
		$img_list["screenshot".$img_name]=$val;
    }
    $img_list["msg"]=$e;
    return $img_list;
}

//获取文件本数据
function get_txt($file, $uid=''){
	$txt_array=array();
	$msg="";
	$txt_array=file($file);
	/*
	$new_txt_array[0]=iconv('UTF-8', 'UTF-8', $txt_array[0]); //转码
	if($new_txt_array[0]!=$txt_array[0]){
		$msg.="文本字符必须是UTF-8</br>";
	}
	*/
	
	$file_str = implode(" ",$txt_array);
	$file_str_1 = iconv('UTF-8', 'UTF-8', $file_str);
	if($file_str_1 != $file_str){
		$msg.="txt文件编码只能为utf8无bom的编码格式2</br>";
	}
	
	
    $n = count($txt_array);
    $ebook_uid = load_config('ebook/uid');
	
	$bbs_uid = load_config('bbs/uid');
	//游戏用户
	$game_uid = load_config('game/uid');
	$model = new GoModel();
	
	if (isset($ebook_uid[$uid])) { //如果为ebook用户
	    $option = array(
			'where' => array(
				'parentid' => EBOOK_CATEGORY_ID,//ireader分类的二级分类
				'status' => 1
			),
			'table' => 'sj_category',
			'field' => 'category_id, name',
			'order' => 'orderid',
			'cache' => 600,
		);
		$category = $model->findAll($option);
		$ireader_category = array();
		foreach ($category as $key=>$v) {
		    $ireader_category[$v['name']] = $v;
		}
	}
	if (isset($game_uid[$uid])) { //如果为游戏用户
	    $game_option = array(
			'where' => array(
				'parentid' => GAME_CATEGORY_ID,//游戏分类的二级分类
				'status' => 1
			),
			'table' => 'sj_category',
			'field' => 'category_id, name',
			'order' => 'orderid',
			'cache' => 600,
		);
		$category_game2 = $model->findAll($game_option);
		foreach($category_game2 as $k2=>$v2){
			$parentid_arr[] = $v2['category_id'];
		}
		$game_options = array(
			'where' => array(
				'parentid' => $parentid_arr,//游戏分类的三级分类
				'status' => 1
			),
			'table' => 'sj_category',
			'field' => 'category_id, name',
			'order' => 'orderid',
			'cache' => 600,
		);
		$category_game = $model->findAll($game_options);
		$game_category = array();
		foreach ($category_game as $key_game=>$v_game) {
		    $game_category[$v_game['name']] = $v_game;
		}
	}
    if($uid == 5361479){
        $new_array['package_new'] = trim($txt_array[0]);
        unset($txt_array[0]);
        $txt_array = array_values($txt_array);
    }

	foreach($txt_array as $k=>$v){
		$v = str_replace(array('。', '，', '、', '！', '？', '【', '】', '"', "'", "－", "（", "）", "：", "｛", "｝", ),array('.', ',', ',', '!', '?', '[', ']', '', '', '-', "(", ")", ":", "{", "}",),$v);
		$v = preg_replace('/\x{a0}/u', '', $v);
	    if($k==0){
			$new_array['softname']=trim($v);
			if (empty($new_array['softname'])) {
			    $msg.="中文软件名不能为空</br>";
			    continue;
			}
			/*
			$code = TestUtf8($new_array['softname']);
			if ($code!=2) {
			    $msg.="txt文件编码只能为utf8无bom的编码格式</br>";
			}
			*/
			if(strlen_az($new_array['softname'],'UTF-8') > 28){
				$msg.="中文软件名不能大于28个字节</br>";
			}
		}
		if($k==1){
			$new_array['dev_name']=trim($v);
		    if(empty($new_array['dev_name'])) {
				$msg.="作者名不能为空</br>";
			}
			if(mb_strlen($new_array['dev_name'],'UTF-8') > 64){
				$msg.="作者名不能大于64个字符</br>";
			}
		}
		if($k==2){
			$v = str_replace(array(' ',"、","|",",","，"),",",$v);
            $new_array['tags']=trim($v);
		    if (empty($new_array['tags'])) {
			    $msg.="关键字为空!</br>";
			    continue;
			}
			$tags_array = explode(',',$new_array['tags']);
			$tags_count = count($tags_array);
			if($tags_count <= 5){
				if($tags_count == 1 && mb_strlen($tags_array[0],'UTF-8') > 5){
					$msg.="请根据提示的分割符提交关键字!</br>";
				}
				
			    for($iarr = 0 ; $iarr<$tags_count ; $iarr++)
				{
					if(mb_strlen(trim($tags_array[$iarr]),'UTF-8') > 5){
                                            unset($tags_array[$iarr]);
						//$msg.="您的关键字每个不能超过5个字符！</br>";
						//break;
					}
                                }
                                if(count($tags_array)==0)
                                {
                                    $msg.="您的关键字每个不能超过5个字符！</br>";
                                }else if(count($tags_array)==1&&empty($tags_array[1])&&empty($tags_array[0]))
                                {
                                    $msg.="您的关键字每个不能超过5个字符！</br>";
                                }else
                                {
				    $tags = trim(implode(',',$tags_array));
                                }
				
				//$tags = trim($v);
			} elseif ($tags_count == 0) {
			    $msg.="关键字为空!</br>";
			} else {
				$msg.="您的关键字个数不能超过5个！</br>";
			}
			$new_array['tags']=$tags;
		}
		
		$line_of_info = 3;//软件描述的开始行
		if (isset($ebook_uid[$uid])) {//如果为ebook的用户
		    $line_of_info = 5;
		    if ($k == 3) {
    		    $category_name = trim($v);
                $new_array['category_id'] = isset($ireader_category[$category_name]['category_id']) ? $ireader_category[$category_name]['category_id'] : 0;
                if ($new_array['category_id'] == 0) {
                    $msg.="您填写的分类有误！</br>";
                }
		    }
		    if ($k == 4) {
		        $tariff = trim($v);
                if ($tariff == '免费') {
                    $tariff = 0;
                } else if ($tariff == '部分章节收费') {
                    $tariff = 1;
                //} else if ($tariff == '按本计费') {
                //    $tariff = 2;
                } else {
                    $tariff = 0;
                    $msg.="您填写的资费情况有误！</br>";
                }
                $new_array['costs'] = $tariff;
		    }
		}
		
		if (isset($bbs_uid[$uid])) {//如果是论坛z
			$line_of_info = 5;
			if ($k == 3) {
				$category_name = trim($v);
				$bbs_category = load_config('bbs/cid');
				$new_array['category_id'] = isset($bbs_category[$category_name]) ? $bbs_category[$category_name] : 0;
				if ($new_array['category_id'] == 0) {
					$msg.="您填写的分类有误！</br>";
				}
			}
			if ($k == 4) {
				$tariff = trim($v);
				if ($tariff == '免费') {
					$tariff = 0;
				} else {
					$tariff = 0;
					$msg.="您填写的资费情况有误！</br>";
				}
				$new_array['costs'] = $tariff;
			}
		}
		
		if (isset($game_uid[$uid])) {//如果为游戏的用户
		    $line_of_info = 5;
		    if ($k == 3) {
				$category_name = trim($v);
                $new_array['category_id'] = isset($game_category[$category_name]['category_id']) ? $game_category[$category_name]['category_id'] : 0;
                if ($new_array['category_id'] == 0) {
                    $msg.="您填写的分类有误！</br>";
                }
		    }
		    if ($k == 4) {
		        $tariff = trim($v);
                if ($tariff == '免费') {
                    $tariff = 0;
                } else {
                    $tariff = 0;
                    $msg.="您填写的资费情况有误！</br>";
                }
                $new_array['costs'] = $tariff;
		    }
		}

	    if($k>=$line_of_info){
            $new_array['intro'].=trim($v);
        }
    }
    if(mb_strlen($new_array['intro'],'UTF-8') > 1000){
        $msg.="软件描述不能大于 1000个字符</br>";
    }
    if(mb_strlen($new_array['intro'],'UTF-8') == 0){
        $msg.="软件描述不能为空</br>";
    }
    $new_array['msg']=$msg;
	return $new_array;
}


// XXX: 不要重写mkdir，用mkdir(path, mode, true)
function mkdirr($pn,$mode=null) {

    if(is_dir($pn)||empty($pn)) return true;
    $pn=str_replace(array('/', ''),DIRECTORY_SEPARATOR,$pn);

    if(is_file($pn)) {trigger_error('mkdirr() File exists', E_USER_WARNING);return false;}

    $next_pathname=substr($pn,0,strrpos($pn,DIRECTORY_SEPARATOR));
    if(mkdirr($next_pathname,$mode)) {if(!file_exists($pn)) {return mkdir($pn,$mode);} }
    return false;
}
// XXX： 好好改个意思正确的名字
//更新上传包表
function   upload_package($model,$status,$message,$table,$id,$database){
    if (!$model)
        return;
    $data=array(
		'status'=>$status,
		'message'=>$message,
		'__user_table'=>$table,
    );
    $where = array(
		'id'=>$id,
    );
    $result=$model->update($where, $data,DATABASE);
    return $result;
}
// XXX： 好好改个意思正确的名字
//更新软件表 sj_upload_package_soft
function   upload_package_soft($model,$status,$remark,$table,$id,$database){
    $data=array(
		'status'=>$status,
		'remark'=>$remark,
		'__user_table'=>$table,
    );
    $where = array(
		'id'=>$id,
    );
    $result=$model->update($where, $data,DATABASE);
    return $result;
}
// XXX： 好好改个意思正确的名字
//插入数据库软件表sj_upload_package_soft
function add_package_soft($data,$msg,$status,$model,$database,$soft_id = 0){
    $data['status']=$status;
    $data['msg']=$msg;
	$data['soft_id']=$soft_id;
    $result=$model->insert($data,$database);
    return $result;
}

//删除解压失败的文件夹
//$dir="D:/gongju/APMServ5.2.6/www/htdocs/wwwroot/upload/tmp/20120924104810_7288";
//D:/gongju/APMServ5.2.6/www/htdocs/wwwroot/upload/tmp/20120924104409_4091
//deltree($dir);
// TODO： 写复杂了啊，先序递归删除就完了，后面两个全部是多余的
function deltree($pathdir)
{
    //echo $pathdir;//调试时用的
    if(is_empty_dir($pathdir))//如果是空的
    {
        rmdir($pathdir);//直接删除
    }
    else
    {//否则读这个目录，除了.和..外
        $d=dir($pathdir);
        while($a=$d->read())
        {
            if(is_file($pathdir.'/'.$a) && ($a!='.') && ($a!='..')){unlink($pathdir.'/'.$a);}



            //如果是文件就直接删除
            if(is_dir($pathdir.'/'.$a) && ($a!='.') && ($a!='..'))
            {//如果是目录
                if(!is_empty_dir($pathdir.'/'.$a))//是否为空
                {//如果不是，调用自身，不过是原来的路径+他下级的目录名
                    deltree($pathdir.'/'.$a);
                }
                if(is_empty_dir($pathdir.'/'.$a))
                {//如果是空就直接删除
                    rmdir($pathdir.'/'.$a);



                }
            }
        }
        $d->close();
        //echo "必须先删除目录下的所有文件";//我调试时用的
        deltree($pathdir);
    }
}
function is_empty_dir($pathdir)
{
    //判断目录是否为空
    $d=opendir($pathdir);
    $i=0;
    while($a=readdir($d))
    {
        $i++;
    }
    closedir($d);
    if($i>2){return false;}
    else return true;
}

//遍历文件夹下是否有文件
function file_list($path)
{
    if ($handle = opendir($path))//打开路径成功
    {
        while (false !== ($file = readdir($handle)))//循环读取目录中的文件名并赋值给$file
        {
            if ($file != "." && $file != "..")//排除当前路径和前一路径
            {
                if (!is_dir($path."/".$file))
                {
                    //file_list($path."/".$file);
                    $e .= "根目录下面有不是文件夹的文件";
                }
            }
        }
    }
    return $e;
}

/**
 *返回 1 表示纯 ASCII(即是所有字符都不大于127)
 *返回 2 表示UTF8无bom
 *返回 3 表示UTF8有bom
 *返回 0 表示正常gb编码
**/
function TestUtf8($text)
{
    //if(strlen($text) < 3) return false;
	$lastch = 0;
	$begin = 0;
	$BOM = true;
	$BOMchs = array(0xEF, 0xBB, 0xBF);
	$good = 0;
	$bad = 0;
	$notAscii = 0;
	for($i=0; $i < strlen($text) ;$i++)
	{
		$ch = ord($text[$i]);
		
		if($ch >= 0x80) $notAscii++;
		
		if($begin < 3)
		{
			$BOM = $BOM &&($BOMchs[$begin]==$ch);
			$begin += 1;
			continue;
		}

		if($begin==3 && $BOM) break;


		if( ($ch&0xC0) == 0x80 )
		{
			if( ($lastch&0xC0) == 0xC0 )
			{
				$good += 1;
			}
			else if( ($lastch&0x80) == 0 )
			{
				$bad += 1;
			}
		}
		else if( ($lastch&0xC0) == 0xC0 )
		{
			$bad += 1;
		}
		$lastch = $ch;
	}
	if($begin == 3 && $BOM)
	{
		return 3;
	}
	else if($notAscii==0)
	{
		return 1;
	}
	else if ($good >= $bad )
	{
		return 2;
	}
	else
	{
		return 0;
	}
}

/**
 * 
 * 检查utf-8字符串的字节数
 * 汉字算2个，其他算1个
 * @param $string
 * @param $charset
 */
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
