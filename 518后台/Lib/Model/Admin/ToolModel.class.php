<?php
/*****
******系统工具model
*******************/
class ToolModel extends Model {

	//即时刷缓存
	function brush_cache($name,$path){
		$model = new Model();
			
			$now = time();
			$data = array(
				'fromip' => $_SERVER['REMOTE_ADDR'],
				'actionexp'=> "{$_SESSION['admin']['admin_user_name']}, 通过脚本刷新了[{$name}]",
				'log_time' => $now,
				'update_time' => $now,
			);
			$log_id = $model->table('sj_task_client_log')->add($data);
			$task_client = get_task_client();
			$task_val = array(
				"type"=> $type,
				"atime"=> $now,
				"file"=> $path,
				"log_id" =>$log_id,
			);
			$job_handle = $task_client->doBackground("ucenter_callback", json_encode($task_val));
			
			$where = array(
				'id' =>$log_id
			);
			$data = array(
				'job_handle' => $job_handle  
			);
			$model->table('sj_task_client_log')->where($where)->save($data);
	}
}
