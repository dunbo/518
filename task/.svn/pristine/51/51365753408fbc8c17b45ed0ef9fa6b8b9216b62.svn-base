<?php
/*
 *   使用ffmpeg转换视频格式（由h264转h263）
 */
include dirname(__FILE__).'/../init.php';
include_once(dirname(__FILE__).'/../../tools/functions.php');


$model = new GoModel();
ini_set('default_socket_timeout', -1);

// if( $configs['is_test'] ) {
// 	$time = get_now_time();
// }else{
// 	$time  = time();
// }

//$static_data_source = '/data/att/m.goapk.com';
$static_data_source = '/data/caiji';
$static_data = '/data/att/m.goapk.com';
$static_dir = '/data2';
$ffmpeg_path = '/usr/local/bin/';

load_helper('task');
$worker = get_task_worker();
$worker->addFunction("collect_video_change_format", "process_video");
while ($worker->work());

function process_video($jobs){ 
	global $ffmpeg_path;
	global $static_data;
	global $static_dir;
	global $static_data_source;
	$jobs = $jobs->workload();
    $jobs = json_decode($jobs,true);
	/* $jobs = array(
		'type'=> 1,
		'package' => '',
		'softid' => '',
		'tmpid' => '',
		'url' => '/tmp/1420018400412_hd.mp4'
	); */
	$old_url = $jobs['url'];
	writelog("collect_video_change_format.log",var_export($jobs,true));
	//$cmd = "{$ffmpeg_path}ffmpeg -i {$static_data}{$old_url} 2>&1";
	$cmd = "{$ffmpeg_path}ffprobe -print_format json {$static_data_source}{$old_url} 2>&1";
	//echo $cmd;
	writelog("collect_video_change_format.log",$cmd);
	$info = shell_exec($cmd);
	writelog("collect_video_change_format.log",$info);
	preg_match('/Video: [a-z0-9\-A-Z_]+/',$info,$matches);
	//var_dump($matches);
	if($matches[0]){
		$target_format = substr($matches[0],7,strlen($matches[0]));
		//echo $target_format;
		writelog("collect_video_change_format.log",$target_format);
		if($target_format=='h264'){
			$video_dir_tmp = $static_data.$static_dir.'/video_tmp/'.date('Ym/d').'/';
			$video_dir = $static_data.$static_dir.'/video/'.date('Ym/d').'/';
			if(!is_dir($video_dir_tmp)) {
				if(!mkdir($video_dir_tmp,0777,true)) {
					writelog("collect_video_change_format.log","创建{$video_dir_tmp}失败");
				}
			}
			if(!is_dir($video_dir)) {
				if(!mkdir($video_dir,0777,true)) {
					writelog("collect_video_change_format.log","创建{$video_dir}失败");
				}
			}
			$video_name = rand_code_md5().'.mp4';
			$h263_url_tmp = $video_dir_tmp.$video_name;
			$h263_url = $video_dir.$video_name;
			//echo $h263_url;
			//h264格式开始转视频
			$cmd = "{$ffmpeg_path}ffmpeg -y -i {$static_data_source}{$old_url} -f mp4 -vcodec mpeg4 -r 25 -b 1024k -ab 128k -ac 2 -async 1 -strict -2 {$h263_url_tmp} 2>/dev/null &";
			//$cmd = "sh /data/www/wwwroot/task/worker/video.sh {$static_data}{$old_url} {$h263_url_tmp} {$h263_url} 2>/dev/null &";
			shell_exec($cmd);
//			$descriptorspec = array(
//			0 => array("pipe", "r"),  // stdin is a pipe that the child will read from
//			1 => array("pipe", "w"),  // stdout is a pipe that the child will write to
//			2 => array("pipe", "w") // stderr is a file to write to
//			);
//			$proc = proc_open($cmd,$descriptorspec, $pipes);
			
			writelog("collect_video_change_format.log",$cmd);
			$now_tm = time();

			while(true) {
				//$status = proc_get_status($proc);
				$n = shell_exec('ps aux|grep ffm|grep -v grep|grep '. $h263_url_tmp. '| wc -l ');
//				echo 'ps aux|grep ffm|grep '. $h263_url_tmp. '| wc -l ';
				usleep(500);
				if ($n ==0) {
					$c_cmd = "cp {$h263_url_tmp} {$h263_url}";
					shell_exec($c_cmd);
				}

				if (file_exists($h263_url)&&$n ==0) {
					unlink($h263_url_tmp);
					writelog("collect_video_change_format.log",$h263_url);
					$cmd = "{$ffmpeg_path}ffprobe -print_format json {$h263_url} 2>&1";
					writelog("collect_video_change_format.log",$cmd);
					$info = shell_exec($cmd);
					writelog("collect_video_change_format.log",$info);
					preg_match('/[0-9]+x[0-9]+ /',$info,$matches);
					$matches = trim($matches[0]);
					$size = explode('x',$matches);

					if($size[0]>$size[1]){
						$screen_mode = 1;
					}else{
						$screen_mode = 2;
					}
					$new_url = str_replace($static_data,'',$h263_url);
					save_url($jobs,$new_url,$screen_mode);
					sleep(1);
					break;
				}else{
					$check_tm = time();
					$process_tm = $check_tm - $now_tm;

					if($process_tm>60&&$process_tm<600){
						if($n==0){
							writelog("collect_video_change_format.log",$cmd."视频未转码成功，进程异常退出");
							break;
						} 
					}else if($process_tm>600){
						writelog("collect_video_change_format.log",$cmd."已处理10分钟自动退出");
						break;
					}
				}
			}

		}else{
			$new_url = $old_url;
			save_url($jobs,$new_url);
		}

		
	}
	
}   

function save_url($data,$url,$screen_mode='0'){
	global $model;
	$where = array();
	$table = 'sj_soft_extra';
	if($data['id']){
		$where['id']=$data['id'];
	}
	$u_data['__user_table'] = $table;
	$u_data['update_tm'] = time();
	$u_data['video_h263_url'] = $url;
	if(!empty($screen_mode)) $u_data['screen_mode'] = $screen_mode;
	$res = $model->update($where,$u_data,'master');
// 	$time = time();
// 	$con = mysql_connect("192.168.1.107","jyuser","jJy@)**Hi8z20");
// 	if (!$con)
// 	{
// 		writelog("collect_video_change_format.log",'Could not connect: ' . mysql_error());
// 	}
	
// 	mysql_select_db("jydata", $con);
// 	$sql = "UPDATE sj_colle_contentlist SET `update_tm`={$time},`video_h263_url`='{$url}',`screen_mode`={$screen_mode} where `sid`={$data['id']}";
// 	$res = mysql_query($sql);
// 	mysql_close($con);
	if(!$res)
	writelog("video_change_format.log",$model->getSql());
}
function rand_code_md5() {
    return md5(rand(1, 100000) . microtime().uniqid());
}

function writelog($filename,$msg){
	$now = time();
	$path = "/data/att/permanent_log/admin_cron_log/".date("Y-m-d", $now);
	if(!file_exists($path)){
		mkdir($path, 0755, true);
	}	
	$path_log = $path."/".$filename;
	$msg = date('Y-m-d H:i:s', $now). " {$msg}\n";
	file_put_contents($path_log, $msg, FILE_APPEND);
}