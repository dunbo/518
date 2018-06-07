<?php

class ChannelModel extends Model {
	//调整表前缀
    protected $trueTableName = 'sl_activate_channel';
	 
	public function getCategory($option){
        $data_list = $this -> where('`status` = 1 and `parent_id` = 0') -> field('id,channel_name') -> select();
		foreach($data_list as $m => $n){
			$lists = $this->where('`status` = 1 and `parent_id`='.$n['id']) -> select();
			$data_list[$m][$n['id']] = $lists;
			 foreach($lists as $key => $val){
				$lists[$key]['id'] = $val['id'];
				$list_three = $this -> where('status = 1 and `parent_id`='.$lists[$key]['id']) -> select();
				$data_list[$m][$n['id']][$val['id']] = $list_three;
			} 
		}
		$category = '<select name="'.$option['label_name'].'" id="'.$option['label_id'].'">
			<option value="0" selected="selected">根频道</option>';
		if($option['key'] == 1){
			foreach($data_list as $m => $n){
				$category.='<option value="'.$n['id'];
							if($n['id'] == $option['selected']){
								$category .= '" selected="selected"';
							}else{
								$category .= '"';
							}
							$category .= '>-'.$n['channel_name'].'-</option>'; 
					foreach($n[$n['id']] as $k => $val){
						if($val['channel_name']){
							$category .= '<option value="'.$val['id'];
							if($val['id'] == $option['selected']){
								$category .= '" selected="selected" ';
							}else{
								$category .= '"';
							}
							$category.='>--'.$val["channel_name"].'--</option>';
						}
						foreach($n[$n['id']][$val['id']] as $key => $info){
							$category.='<option value="'.$info['id'];
							if($info['id'] == $option['selected']){
								$category .= '" selected="selected"';
							}else{
								$category .= '"';
							}
							$category .= '>'.$info['channel_name'].'</option>'; 
						}
					}
			}
		}else{
			foreach($data_list as $m => $n){
			   $category.='<optgroup label='.$n['channel_name'].'>'; 
					foreach($n[$n['id']] as $k => $val){
						if($val['channel_name']){
							$category .= '<optgroup label=——'.$val['channel_name'].'——>';
						}
						foreach($n[$n['id']][$val['id']] as $key => $info){
							$category .= '<option value="'.$info['id'];
							if($info['id'] == $option['selected']){
								$category .='" selected="selected" ';
							}else{
								$category .='" ';
							}
							$category .= '>'.$info['channel_name'].'</option>'; 
						}
					}
			}
		}
		$category .='</select>';
		return $category;
	}
	
}
?>