<?php
class SensitiveModel extends Model {
	//更新config表
	public function update_badword($type){
		$model = new Model();
		$pu_config = D('Sj.Config');	
		$sj_sensitive = $model -> table('sj_sensitive')->field('word') -> order('abc')-> where("type = '{$type}'")->select();
		$configcontent = '';
		if($type == 1){
			foreach($sj_sensitive as $v){
				$configcontent .= $v['word']."|";
			}	
			
			$where['config_type'] = 'soft_badword';
			$data['configcontent'] = $configcontent;
			$update_badword = $pu_config -> where($where)->data($data)->save();

			if($update_badword){
				return 1;
			}else{
				return 0;
			}
		}elseif($type ==2){
			foreach($sj_sensitive as $v){
				$configcontent .= $v['word'].";";
			}	
			
			$where['config_type'] = 'soft_shieldpackagename';
			$data['configcontent'] = $configcontent;
			$update_badword = $pu_config -> where($where)->data($data)->save();
			if($update_badword){
				return 1;
			}else{
				return 0;
			}
		}elseif($type ==3){
			foreach($sj_sensitive as $v){
				$configcontent .= $v['word']."|";
			}	
			$where['config_type'] = 'badword';
			$data['configcontent'] = $configcontent;
			$update_badword = $pu_config -> where($where)->data($data)->save();
			if($update_badword){
				return 1;
			}else{
				return 0;
			}
		}elseif($type ==4){
			//@TODO 可能需要调整
			foreach($sj_sensitive as $v){
				$configcontent .= $v['word']."|";
			}	
			$where['config_type'] = 'soft_remind_words';
			$data['configcontent'] = $configcontent;
			$update_badword = $pu_config -> where($where)->data($data)->save();
			if($update_badword){
				return 1;
			}else{
				return 0;
			}
		}elseif($type ==5){
			foreach($sj_sensitive as $v){
				$configcontent .= $v['word']."|";
			}	
			$where['config_type'] = 'soft_cp_highlight_edit';
			$data['configcontent'] = $configcontent;
			$update_badword = $pu_config -> where($where)->data($data)->save();
			if($update_badword){
				return 1;
			}else{
				return 0;
			}
		}
	}
	public function pub_filter_special_word($data){
	    $model	 =  M('special_sensitive');
	    $special = $model->where('status=1')->select();
	    $result['intro'] = array(true, '');
	    if($special){
	        foreach ($special as $v){
	            if(strpos($data['intro'], $v['word']) !== false){
	                $result['intro'][0] = false;
	                $result['intro'][1] .= ' '. $v['word']. ' ';
	            }
	        }
	    }
	    return  $result;
	}
	//敏感词检测
	public function check_special_word($param){
		$where = array();
		if($param['type'] == 'soft_badword'){
			$where['type'] = 1;
		}else if($param['type'] == 'badword'){
			$where['type'] = 3;
		}
		$wordarray = $this->table('sj_sensitive')->where($where)->field('word')->select();	
		$result = array();
		foreach($param as $k => $v) {
			$result[$k] = array(true, '',true,'');
			foreach($wordarray as $value){
				if(strpos($v, $value['word']) !== false){
					$result[$k][0] = false;
					$result[$k][1] .= ' '. $value['word']. ' ';
				}
			}
		}
        return $result;
	}
}
?>
