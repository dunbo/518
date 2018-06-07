<?php
class ContAttributeModel extends AdvModel {
    
    // protected $trueTableName = 'cont_level_stat';

	//获取栏目列表
	public function getall_cont_column_list(){
		$column_list = array();
		$all_column = $this->table('cont_column_advertise')->where(array('status'=>1))->select();
		foreach ($all_column as $value) {
			$column_list[$value['cont_id']] = $value;
		}
		return $column_list;
	}
	
	public function get_cont_column($checked=array()){
		$column_list = array();
		$all_column = $this->table('cont_column_advertise')->where(array('status'=>1))->select();
		foreach ($all_column as $value) {
			$column_list[$value['cont_id']] = $value;
		}
		$arr = $tags = '';
	    $arr .='<ul style="list-style:none;">';
	    $arr .='<li><div style="float:left">已选择栏目名称：</div><span id="selectlist"></span></li><div style="clear: both"></div>';
	    $arr .= '<li style="list-style: none">';
	    foreach ($column_list as $column_value) {
	        $arr .= '<input type="checkbox" onclick="column_select(this)" id="column_'.$column_value['cont_id'].'" title="'.$column_value['name'].'" name="cont_column[]" value="'.$column_value['cont_id'].'"'; 
	        if(in_array($column_value['cont_id'],$checked)) $arr .= 'checked ';
	        $arr .= '>'.$column_value['name'].'&nbsp;&nbsp;&nbsp;&nbsp;';
	    }
	    $arr .= '</li></ul><script type="text/javascript"> column_select(1); </script>';
	    $tags = $arr;
	    return $tags;
	}
	
	//内容性质
	public function get_cont_quality($nature_val='',$type=''){
		$list = C('CONTENT_NATURE');
	    $type = $type ? $type : 'select';
	    $tags = '';
	    if($type == 'select'){
	        $tags .= "<select id='content_nature' name='content_nature'>";
	        $tags .= '<option value="0" selected>请选择内容性质</option>';
	        foreach($list as $k => $val){
	            if($nature_val == $k){
	                $tags .= '<option selected="selected" value="'.$k.'">'.$val.'</option>';
	            }else{
	                $tags .= '<option value="'.$k.'">'.$val.'</option>';
	            }
	        }
	        $tags .= "</select>";
	    }elseif($type == 'radio'){
	        foreach($list as $k => $val){
	            if($nature_val == $k){
	                $tags .= '<input name="content_nature" checked type="radio" value="'.$k.'"/>'.$val.'&nbsp;&nbsp;&nbsp;&nbsp;';
	            }else{
	                $tags .= '<input name="content_nature" type="radio" value="'.$k.'"/>'.$val.'&nbsp;&nbsp;&nbsp;&nbsp;';
	            }
	        }
	    }
	      
	    return $tags;
	}

	//内容质量 
	public function get_cont_level($level_val='',$type=''){
		$list = C('CONTENT_LEVEL');
	    $type = $type ? $type : 'select';
	    $tags = '';
	    if($type == 'select'){
	        $tags .= "<select id='content_level' name='content_level'>";
	        $tags .= '<option value="0" selected>全部</option>';
	        foreach($list as $k => $val){
	            if($level_val == $k){
	                $tags .= '<option selected="selected" value="'.$k.'">'.$val.'</option>';
	            }else{
	                $tags .= '<option value="'.$k.'">'.$val.'</option>';
	            }
	        }
	        $tags .= "</select>";
	    }elseif($type == 'radio'){
	        foreach($list as $k => $val){
	            if($level_val == $k){
	                $tags .= '<input name="content_level" checked type="radio" value="'.$k.'"/>'.$val.'&nbsp;&nbsp;&nbsp;&nbsp;';
	            }else{
	                $tags .= '<input name="content_level" type="radio" value="'.$k.'"/>'.$val.'&nbsp;&nbsp;&nbsp;&nbsp;';
	            }
	        }
	    }
	    
	    return $tags;
	}

	//内容来源
	function get_cont_src($config){
		$list = C($config['key']);
		if(empty($list)){
		    return '';
		}
		$tags = '';
		if($config['type'] == "select"){
			$tags .= "<select id='{$config['tag_id']}' name='{$config['tag_name']}'>";
			$tags .= $config['tag_tip'] ? '<option value="0" selected>'.$config['tag_tip'].'</option>' : '';
			foreach($list as $k => $val){
			    if($config['default'] == $k){
			        $tags .= '<option selected="selected" value="'.$k.'">'.$val.'</option>';
			    }else{
			          $tags .= '<option value="'.$k.'">'.$val.'</option>';
			    }
			}
			$tags .= "</select>";
		}elseif($config['type'] == "radio"){
		    foreach($list as $k => $val){
		        if($config['default'] == $k){
		            $tags .= "<input name='{$config['tag_name']}' checked type='radio' value='".$k."'/>".$val."&nbsp;&nbsp;&nbsp;&nbsp;";
		        }else{
		            $tags .= "<input name='{$config['tag_name']}' type='radio' value='".$k."'/>".$val."&nbsp;&nbsp;&nbsp;&nbsp;";
		        }
		    }
		}
		return $tags;
	}

	//用户倾向
	function get_user_tend($config_user){
		$list = C($config_user['key']);
		if(empty($list)){
		    return '';
		}
		$tags = '';
		if($config_user['type'] == "select"){
			$tags .= "<select id='{$config_user['tag_id']}' name='{$config_user['tag_name']}'>";
			$tags .= $config_user['tag_tip'] ? '<option value="0" selected>'.$config_user['tag_tip'].'</option>' : '';
			foreach($list as $k => $val){
			    if($config_user['default'] == $k){
			        $tags .= '<option selected="selected" value="'.$k.'">'.$val.'</option>';
			    }else{
			          $tags .= '<option value="'.$k.'">'.$val.'</option>';
			    }
			}
			$tags .= "</select>";
		}elseif($config_user['type'] == "radio"){
		    foreach($list as $k => $val){
		        if($config_user['default'] == $k){
		            $tags .= "<input name='{$config_user['tag_name']}' checked type='radio' value='".$k."'/>".$val."&nbsp;&nbsp;&nbsp;&nbsp;";
		        }else{
		            $tags .= "<input name='{$config_user['tag_name']}' type='radio' value='".$k."'/>".$val."&nbsp;&nbsp;&nbsp;&nbsp;";
		        }
		    }
		}
		return $tags;
	}

	//内容标签
	function get_cont_tags($tagid,$tag_rank){
		//获取一级标签
        $tagfirst = $this->table('cont_tags_group')->field(array('groupid','groupname'))->where('status = 1')->select();
        $tag_first = $tagpinfo = $taglist = $taginfo= array();
        foreach ($tagfirst as $tagfirstkey => $tagfirstvalue) {
            $tag_first[$tagfirstvalue['groupid']]['groupid'] = $tagfirstvalue['groupid'];
            $tag_first[$tagfirstvalue['groupid']]['groupname'] = $tagfirstvalue['groupname'];
        }
        
        $result = $this->table('cont_tags')->field(array('tagid','tagname','group','parentid'))->where('status = 1')->select();
        foreach($result as $val){
            if($val['parentid'] == 0){//二级标签
                $taginfo[$val['group']][] = $val;
            }
            if($val['parentid'] > 0){
                $tagpinfo[$val['parentid']][] = $val;
            }
        }
        $tagshtml = '';
        $tagshtml .= '<li style="list-style:none"><span style="font-size: 16px;">内容标签</span></li>';
		$tagshtml .= '<li style="list-style:none"><div style="float:left;">已选标签：</div><span id="tags_select"></span></li>';
		$tagshtml .= '<div style="clear: both"></div>';
		
		foreach ($tag_first as $tagfirst) {
		
			$tagshtml .= '<div><div>'.$tagfirst['groupname'].'：</div>';
				$tagshtml .= '<div>';
				foreach($taginfo[$tagfirst['groupid']] as $tagseond){ 
					$tagshtml .= '<input type="checkbox" class="tag_first_'.$tagfirst['groupid'].'" title="'.$tagseond['tagname'].'" id="tag_'.$tagseond['tagid'].'" name="tags[]" value="'.$tagseond['tagid'].'" onclick="tag_three(this)"';
					if(in_array($tagseond['tagid'],$tagid)) $tagshtml .= " checked "; 
					$tagshtml .= '><span onclick="show_three('.$tagseond['tagid'].')">'.$tagseond['tagname'].'</span>&nbsp;&nbsp;&nbsp;&nbsp';
				}	
				$tagshtml .= '</div>';
				$tagshtml .= '<div id="tagf_'.$tagfirst['groupid'].'">';
					foreach($taginfo[$tagfirst['groupid']] as $tagseond){ 
						
						$tagshtml .= '<div style="display:none;" id="tagt_'.$tagseond['tagid'].'">';
						foreach($tagpinfo[$tagseond['tagid']] as $tagthree){
						
							$tagshtml .= '<span style="display:block;float:left;border:1px solid #ccc;">&nbsp;&nbsp;<input type="checkbox" class="tagth_'.$tagseond['tagid'].'" title="'.$tagthree['tagname'].'" id="tag_'.$tagthree['tagid'].'" name="tags[]" value="'.$tagthree['tagid'].'" onclick="tag_rank(this)"';
							if(in_array($tagthree['tagid'],$tagid)) $tagshtml .= " checked ";
							$tagshtml .= '><span onclick="tag_trank('.$tagthree['tagid'].')">'.$tagthree['tagname'].'</span>&nbsp;&nbsp;&nbsp;&nbsp;';
							if(in_array($tagthree['tagid'],$tagid)){
								$tagshtml .= '<span id="rank_'.$tagthree['tagid'].'" style="display:block;float:right;">';
							}else{
								$tagshtml .= '<span id="rank_'.$tagthree['tagid'].'" style="display:none;float:right;">';
							}
							$tagshtml .= '<input type="radio" name="'.$tagthree['tagid'].'_rank" title="'.$tagthree['tagname'].'(高)" onclick="select_tag(this)" value="'.$tagthree['tagid'].'_1"';
							if(in_array($tagthree['tagid'].'_1',$tag_rank)) $tagshtml .= " checked ";
							$tagshtml .= '><span onclick="select_tt(\''.$tagthree['tagid'].'_1\')">高</span>
								<input type="radio" name="'.$tagthree['tagid'].'_rank" title="'.$tagthree['tagname'].'(中)" onclick="select_tag(this)" value="'.$tagthree['tagid'].'_2"';
							if(in_array($tagthree['tagid'].'_2',$tag_rank)) $tagshtml .= " checked ";
							$tagshtml .= '><span onclick="select_tt(\''.$tagthree['tagid'].'_2\')">中</span>
								<input type="radio" name="'.$tagthree['tagid'].'_rank" title="'.$tagthree['tagname'].'(低)" onclick="select_tag(this)" value="'.$tagthree['tagid'].'_3"';
							if(in_array($tagthree['tagid'].'_3',$tag_rank)) $tagshtml .= " checked ";
							$tagshtml .= '><span onclick="select_tt(\''.$tagthree['tagid'].'_3\')">低</span>&nbsp;&nbsp;&nbsp;&nbsp;';
							$tagshtml .= '</span></span>';
						}	
						$tagshtml .= '</div>';
					} 
				$tagshtml .= '</div>';
			$tagshtml .= '</div>';
			$tagshtml .= '<div style="clear: both"></div><br>';
		}
		$tagshtml .= '温馨提示<li style="list-style:none"><span style="color:#666;">1、点击二级标签文案，可查看其下的三级标签</span></li><li style="list-style:none"><span style="color:#666;">2、删除其二级标签，同时删除该二级标签下的三级标签</span></li><script type="text/javascript"> show_tags(); </script>';
		return $tagshtml;
	}


	//添加标签映射数据
	public function add_cont_attrribute($post_data){
		$data = array();
		if($post_data['cid']) $data['cid'] = $post_data['cid'];
		if($post_data['vid']) $data['vid'] = $post_data['vid'];
		if($post_data['cvid']) $data['cvid'] = $post_data['cvid'];
		if($post_data['eid']) $data['eid'] = $post_data['eid'];
		if($post_data['pkgname']) $data['pkgname'] = $post_data['pkgname'];
		$data['status'] = 1;
		$data['up_time'] = time();
		$tag = $post_data['tags'];
		if($tag){
			$tags = explode(',',$tag);
			foreach ($tags as $tag_value) {
				if(!$tag_value) continue;
				$tagvalue = explode('_',$tag_value);
				if(count($tagvalue) == 2){
					$data['first_tagid'] = $tagvalue[0];
					$data['second_tagid'] = $tagvalue[1];
					$data['three_tagid'] = '';
					$data['tagidlevel'] = '';
				}elseif(count($tagvalue) == 3){
					$data['first_tagid'] = $tagvalue[0];
					$data['second_tagid'] = $tagvalue[1];
					$data['three_tagid'] = $tagvalue[2];
					$data['tagidlevel'] = '';
				}else if(count($tagvalue) == 4){
					$data['first_tagid'] = $tagvalue[0];
					$data['second_tagid'] = $tagvalue[1];
					$data['three_tagid'] = $tagvalue[2];
					$data['tagidlevel'] = $tagvalue[3];
				}
				//添加数据
				$tag_result = $this->table('cont_tags_content')->add($data);
			}
		}
        
      	return 'succedd';  
	}

	//软件类型
	function get_soft_type($softid='',$type=''){
		$list = C('CONTENT_SOFT_TYPE');
		$tags = '';
		$type == $type ? $type : 'select' ;
		if($type == 'select'){
			$tags .= "<select id='' name='soft_type'>";
			foreach($list as $k => $val){
			    if($softid == $k){
			        $tags .= '<option selected="selected" value="'.$k.'">'.$val.'</option>';
			    }else{
			        $tags .= '<option value="'.$k.'">'.$val.'</option>';
			    }
			}
			$tags .= "</select>";
		}elseif($type == 'radio'){
			foreach($list as $k => $val){
			    if($softid == $k){
			        $tags .= '<input type="radio" name="soft_type" checked value="'.$k.'">'.$val.'&nbsp;&nbsp;&nbsp;&nbsp;';
			    }else{
			        $tags .= '<input type="radio" name="soft_type" value="'.$k.'">'.$val.'&nbsp;&nbsp;&nbsp;&nbsp;';
			    }
			}
		}  
		
		return $tags;
	}

	/**获取性质、质量、来源 和用户倾向
	 * $data_xz    string   内容性质选中项
	 * $data_zl    string   内容质量选中项
	 * $data_ly    string   内容来源选中项
	 * $user_td    string   用户倾向选中项
	 * $data_lm    array    内容栏目选中项
	 * $data_tag1  array    标签选中id
	 * $data_tag2  array    选中三级标签的优先级
	 *
	*/
	public function show_conattibute($data_xz='',$data_zl='',$data_ly='',$user_td='',$data_lm=array(),$data_tag1=array(),$data_tag2=array()){

        $content_xz = $this -> get_cont_quality($data_xz,'radio');
        $content_zl = $this -> get_cont_level($data_zl,'radio');
        $config = array(
            'key'=>'CONTENT_SOURCE',
            'type'=>'radio',
            'tag_id'=>'con_source',
            'tag_name'=>'con_source',
            'tag_tip'=>'全部',
            'default'=> isset($data_ly) ? $data_ly : 0,
        );
        //用户倾向
        $config_user = array(
            'key'=>'USER_TEND',
            'type'=>'radio',
            'tag_id'=>'user_tend', 
            'tag_name'=>'user_tend',
            'tag_tip'=>'全部',
            'default'=> isset($user_td) ? $user_td : 0,
        );
        $user_tend = $this -> get_user_tend($config_user);
        $con_source = $this -> get_cont_src($config);
        $content_lm = $this -> get_cont_column($data_lm);
        $content_tag = $this -> get_cont_tags($data_tag1,$data_tag2);
        return array($content_xz,$content_zl,$con_source,$user_tend,$content_lm,$content_tag);
	}

}