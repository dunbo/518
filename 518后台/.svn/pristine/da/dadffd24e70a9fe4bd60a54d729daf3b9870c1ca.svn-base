<?php

class AuthenticationModel extends Model {	
	//获取soft数据
	public function edit_official_status($id_arr,$status){
		$model = new Model();
		$soft = $model ->table('sj_soft') -> where(array('softid'=>array('in',$id_arr)))->field('package,softid') -> select();
		$return = array();
		$add_result = array();
		foreach ($id_arr as $v) {
			$return[$v] = 1;
			$add_result[$v] = array(
				'package' => '',
			);
		}
		$time = time();
		foreach($soft as $v){
			//修改note表
			$note = $model ->table('sj_soft_note') -> where(array('package'=>$v['package'])) -> field('status,package')->find();
			if($note){
				$map['status'] = $status;
				$map['update_time'] = $time;
				if($status ==2 ){
					//$map['start_time'] = 0;
					//$map['terminal_time'] = 0;
					$map['status'] = 0;
				}
				$edit_note = $model ->table('sj_soft_note') -> where(array('package'=>$note['package'])) -> save($map);
				if(!$edit_note){
					$return[$v['softid']] = 2;
				}
			}else{
				$map['status'] = $status;
				$map['update_time'] = $time;
				if($status !=2 ){
					$map['start_time'] = $time;
					$map['terminal_time'] = $time;
				}
				$map['package'] = $v['package'];
				$add_note = $model ->table('sj_soft_note') -> add($map);
				if(!$add_note){
					$return[$v['softid']] = 6;
				}
			}
			//修改sj_soft_note_single表
			$note_single = $model -> table('sj_soft_note_single') -> where(array('softid'=>$v['softid'])) -> field('status,package,softid')->find();
			if($note_single){
				$map['status'] = $status;
				$map['update_time'] = $time;
				if($status ==2 ){
					//$map['start_time'] = 0;
					//$map['terminal_time'] = 0;
					$map['status'] = 0;
				}
				$edit_note_single = $model ->table('sj_soft_note_single') -> where(array('softid'=>$note_single['softid'])) -> save($map);
				if(!$edit_note_single){
					$return[$v['softid']] =  4;
				}
			}else{
				$map['status'] = $status;
				//if($status ==2 ){
					//$map['start_time'] = 0;
					//$map['terminal_time'] = 0;
				//}
				$map['update_time'] = $time;
				if($status !=2 ){
					$map['start_time'] = $time;
					$map['terminal_time'] = $time;
				}
				$map['package'] = $v['package'];
				$map['softid'] = $v['softid'];
				$add_note = $model ->table('sj_soft_note_single') -> add($map);
				if(!$add_note){
					$return[$v['softid']] = 7;
				}
			}
			$add_result[$v['softid']]['package'] = $v['package'];
		}
		return array($return,$add_result);
	}
}
?>