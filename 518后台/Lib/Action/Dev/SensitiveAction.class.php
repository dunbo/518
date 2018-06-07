<?php
/*
* 操作设置__敏感词管理
* date : 2013.5.22
*
************************************************/
class SensitiveAction extends CommonAction {
	
	   public function sensitive_list(){ //显示敏感词列表
	   	        $words = trim($_POST['word']); 
	   	        $words_filter = $this->strFilter(trim($_POST['word'])); 
				$abc = trim($_GET['abc']);
				if($_GET['word']){
					$words_filter = $this->strFilter(trim($_GET['word'])); 
				}
	   	        $model = M('sensitive');
	   	        if(!empty($words) && isset($words) || !empty($words_filter)){
	   	        	$abc_arr = $model->field('abc')->where("word like binary '%{$words_filter}%' and type=1")->group('abc')->select();
					if($abc){
						$res = $model->where("word like binary '%{$words_filter}%' and type=1 and abc = '{$_GET['abc']}'")->select();
						$count = $model->where("word like binary '%{$words_filter}%' and type=1 and abc = '{$_GET['abc']}'")->count();
						$abc = $abc ; 
					}else{
						$res = $model->where("word like binary '%{$words_filter}%' and type=1")->select();
						$count2 = $model->where("word like binary '%{$words_filter}%' and type=1")->count();
						S('count2',$count2,300);
					}
					//统计列表总数
					$abc_count = $this -> pub_getcount(1,$abc_arr,$words_filter);
	   	        	$this->assign('count2',S('count2'));
					if(!$count){
						$count = S('count2');
					}		
	   	        	$this->assign('count',$count);
					$this->assign('keys',$words_filter); 					
	   	            $this->assign('abc',$abc);
	   	            $this->assign('abc_count',$abc_count);
	   	            $this->assign('abc_arr',$abc_arr);
	   	            $this->assign('list',$res);
				    $this ->display('Dev:Sensitive:sensitive_seach');
	   	        }else{
	   	        	import('@.ORG.Page2');// 导入分页类
	   	        	// 查询满足要求的总记录数
					$where = array();
					$where['type'] = 1;
					if($abc){
						$where['abc'] = $abc;
					}
	   	        	$count = $model->where($where)->count();// 查询满足要求的总记录数
					$count2 = $model->where("type =1")->count();
	   	        	$Page   = new Page($count,1000);// 实例化分页类 传入总记录数和每页显示的记录数
					$show   = $Page->show();// 分页显示输出
					 // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
					$res = $model->where($where)->order('abc asc')->limit($Page->firstRow.','.$Page->listRows)->select();
					$abc_arr = array('1','A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
					//统计个个列表总数
					$abc_count = $this -> pub_getcount(1,$abc_arr);
					$this->assign('abc',$abc);// 赋值数据集
					$this->assign('count',$count2);
					$this->assign('abc_arr',$abc_arr);
					$this->assign('abc_count',$abc_count);
					$this->assign('list',$res);// 赋值数据集
					$this->assign('page',$show);// 赋值分页输出
					$this ->display();
	   	        }
			}
		public function oper_sensitive(){//批量添加敏感词
		       set_time_limit(0);
			   $model = new Model();
			   $words = trim($_POST['word']); 
			   $type = $_GET['type']; 
			   $words_decode = urldecode(trim($_POST['word'])); 
			   $edit =  trim($_POST['edit']);
			   if(!empty($edit) && isset($edit)){ //判断是否是编辑
			   	       if(empty($words)){//产品让数据为空就删除此分类下全部数据
			       	  	  $model->query("delete from sj_sensitive where abc = '{$edit}' and type={$type}");
                          $result = array ('success' => 'del', 'msg' =>'删除此分类下敏感词成功！');
						  echo json_encode ( $result );
						  exit ();
			       	   }else{ //检测字符个数不少于两个
	                     $words_arr = array_unique(explode("\n",$words));
				   	     foreach ($words_arr as $value){
				   	     	if(isset($value)){
				   	          if(mb_strlen($value,'UTF-8')<2){
								$result = array ('success' => 'del', 'msg' =>'操作失败，请输入两个字符以上的敏感词！');
							    echo json_encode ( $result );
							    exit ();
							  }
							}
						  }
					   }
			    } 
			   if(!empty($words) && isset($words) && $words!='输入敏感词，每个敏感词换行输入，单个字符不允许添加' && mb_strlen($words,'UTF-8')>=2){			       
			        set_time_limit(300);
			        //$model->query("delete from sj_sensitive where abc = '{$edit}'");
			    	$words_arr = array_unique(explode("\n",$words_decode));
					//下面这条sql跑挂了	!!!!!!!
			    	/* $words_sql = $model->query("SELECT word FROM `sj_sensitive`");
			    	$words_sql_arr = array();
			    	foreach ($words_sql as $key => $val){ 
			    			$words_sql_arr[$val['word']] = $val['word'];
			    	} */
			    	
			    	while(!empty($words_arr)) {			    	    
			    	$data = '';
					//数据分批处理 5000条一次
			    	$sub_arr = array_splice($words_arr, 0, 10); 
					//var_dump($words,$words_arr,$sub_arr);exit;
			    	 foreach ($sub_arr as $value){
			    		$value = trim($value);
			    		//$value_str = htmlentities($value, ENT_QUOTES, "UTF-8");
			    		$value_str = $value;
				   	    if(isset($value_str)){
							   if(mb_strlen($value_str,'UTF-8')<2){
									exit(json_encode(array('code'=>'0','msg'=>"操作失败，请输入两个字符以上的敏感词！")));
							   }
							   $sub_str = substr($value_str,0,1);	
					   	       $str = is_numeric($sub_str)?'1': Pinyin($value_str);
				              // $sql = $model->query("SELECT * FROM `sj_sensitive` WHERE word ='{$value_str}' and type ={$type}");
							    if(1){
							   //判断库里是否存在记录
				               //if( !isset($words_sql_arr[$value_str])){ 
									 if(empty($str)){
										 $data.= "('1','{$value_str}','{$type}'),"; 
									 }else{
										$abc_str = strtoupper(substr($str,0,1));
										$other = "#[a-zA-Z0-9]#";
										//检测特殊字符
										if(!preg_match($other,$abc_str)){
											$abc = '1';
										}else{
											$abc= strtoupper(substr($str,0,1));
										}
										$data.= "('{$abc}','{$value_str}','{$type}'),";
									 }
				               }
			             }
			      	}
			      	if($data){
			      	    $sql_str = substr($data,0,-1);
			      	    $res = $model->execute("insert INTO `sj_sensitive`(`abc`, `word`,`type`) VALUES {$sql_str}");
						$id = $model->getLastInsID();
			      	    //if(isset($i)){ echo ++$i; }
			      	    //else { $i= 0; echo $i;}
			      	    if($res == false){
			      	        //echo $model->getLastSql(); exit;
			      	    }else{
			      	    	$this->writelog('批量导入敏感词，执行了此sql'."insert INTO `sj_sensitive`(`abc`, `word`,`type`) VALUES {$sql_str}",'sj_sensitive',$id,__ACTION__ ,"","add");
			      	    }
			      	}
			      	
			  }
			  
			  if($res){
			      $Sensitive = D("Dev.Sensitive");
			      $update_badword = $Sensitive -> update_badword($type);
			      $result = array ('success' => true, 'msg' =>'操作成功！');
			      echo json_encode ( $result );
			  } else {
				 $result = array ('success' => false, 'msg' =>'操作失败！');
				 echo json_encode ( $result );
				 exit ();
			  }
					 
			    /*   if(!empty($data)){
				       $sql_str = substr($data,0,-1);				       
				       $res = $model->query("insert INTO `sj_sensitive`(`abc`, `word`,`type`) VALUES {$sql_str}");
				       if ($res[0]==0) {
							$Sensitive = D("Dev.Sensitive");
							$update_badword = $Sensitive -> update_badword($type);
							$result = array ('success' => true, 'msg' =>'操作成功！');
							echo json_encode ( $result );
							exit ();
						} else {
							$result = array ('success' => false, 'msg' =>'操作失败！');
							echo json_encode ( $result );
							exit ();
						}
			       }else{
			          $result = array ('success' => false, 'msg' =>'操作失败，请检查数据是否已经存在！');
					  echo json_encode ( $result );
					  exit ();
			       } */
			    	
			   }else{
					exit(json_encode(array('code'=>'0','msg'=>"操作失败，请输入正确格式的敏感词！")));
			   }
		}
		public function del_sensitive(){
			$id = trim($_POST['id']);
			$word = urldecode(trim($_POST['word']));
			$type = $_GET['type']; 
			if(isset($id) && !empty($id)){
				if(strlen($id)>=1){
			    	 $model = M('sensitive');
			    	 $res = $model->delete($id);
			    	 $this->writelog("敏感词表删除了id为{$id}的记录",'sj_sensitive',$id,__ACTION__ ,"","del");
			    	 if ($res) {
						//更新pu_config表
						$Sensitive = D("Dev.Sensitive");
						$Sensitive -> update_badword($type);
						$result = array ('success' => true, 'msg' =>'删除成功！');
						echo json_encode ( $result );
						exit ();
					 } else {
						$result = array ('success' => false, 'msg' =>'删除失败！');
						echo json_encode ( $result );
						exit ();
					 }
			    }else{
			        $result = array ('success' => false, 'msg' =>'请选择要删除的敏感词！');
					echo json_encode ( $result );
					exit ();
			    }
		    } elseif(isset($word) && !empty($word)){
		    	 if(strlen($word)>=1){
			    	$model = new Model();
			    	$str = '';
			    	foreach (explode("\n",$word) as $value){
						$value = trim($value);
						//$value_html = htmlentities($value, ENT_QUOTES, "UTF-8");
			    	    // echo $value_html;
			    	   $str.= "'$value'".',';
			    	}
			    	//echo $str; exit;
			    	 $sql_str = substr($str,0,-1);
					 $where = "";
					 $where .= "word in($sql_str) and type='{$type}'";
					 $res =  $model-> table('sj_sensitive')->where($where)->delete();
					 $this->writelog("敏感词表删除了word为如下{$sql_str}，type为{$type}的记录",'sj_sensitive',"word:{$sql_str};type:{$type}",__ACTION__ ,"","del");
					 if (!empty($res)) {
						$Sensitive = D("Dev.Sensitive");
						$update_badword = $Sensitive -> update_badword($type);
						$result = array ('success' => true, 'msg' =>'删除成功！');
						echo json_encode ( $result );
						exit ();
					 } else {
						$result = array ('success' => false, 'msg' =>'删除失败！');
						echo json_encode ( $result );
						exit ();
					 }
			    }else{
			        $result = array ('success' => false, 'msg' =>'请选择要删除的敏感词！');
					echo json_encode ( $result );
					exit ();
			    }
		    
		    }
		}
		public function import_csv(){
			    $type = '';
				$id = '';
				$files_type1 = strtolower(substr(strrchr($_FILES ['add_csv']['name'], '.' ),1));
				$files_type2 = strtolower(substr(strrchr($_FILES ['del_csv']['name'], '.' ),1));
				if ($files_type1 == 'csv' && is_uploaded_file ($_FILES["add_csv"]["tmp_name"])) {
				    	$file = $_FILES ['add_csv']['tmp_name'];
				    	$list = array();
				    	$contents = file_get_contents($file);
				    	$encoding = mb_detect_encoding($contents, array('GB2312','GBK','UTF-16','UCS-2','UTF-8','BIG5','ASCII'));
				    	$fp = fopen($file,"r");
				    	while(!feof($fp)){
						      $line = iconv($encoding,'utf-8',trim(fgets($fp)));
						      $abc = is_numeric(substr(Pinyin($line),0,1))?'1': substr(Pinyin($line),0,1);
						      if(!empty($line)){
						      $list[] = array('abc'=>strtoupper($abc),'word'=>$line);
						      }
						} 
						
						fclose($fp);
						$count = count($list);
				    	$type = '确定添加';
				    	$id = 'add';
				    	$this->assign('count',$count);
				    	$this->assign('type',$type);
				    	$this->assign('id',$id);
				    	$this->assign('list',$list);
				    	$this->assign('types',$_GET['types']);
				    	$this->display('Dev:Sensitive:sensitive_import');
				}elseif ($files_type2 == 'csv' && is_uploaded_file ($_FILES["del_csv"]["tmp_name"])) 					{
				    	$file = $_FILES ['del_csv']['tmp_name'];
				    	$list = array();
				    	$contents = file_get_contents($file);
				    	$encoding = mb_detect_encoding($contents, array('GB2312','GBK','UTF-16','UCS-2','UTF-8','BIG5','ASCII'));
				    	$fp = fopen($file,"r");
						while(!feof($fp)){
						      $line = iconv($encoding,'utf-8',trim(fgets($fp)));
						      if(!empty($line)){
						         $list[] = array('abc'=>strtoupper(substr(Pinyin($line),0,1)),'word'=>$line);
						      }
						} 
						fclose($fp);
				    	$count = count($list);
				    	$type = '确定删除';
				    	$id = 'del';
				    	$this->assign('count',$count);// 赋值数据集
				    	$this->assign('type',$type);// 赋值数据集
				    	$this->assign('id',$id);// 赋值数据集
				    	$this->assign('list',$list);// 赋值数据集
						$this->assign('types',$_GET['types']);
				    	$this->display('Dev:Sensitive:sensitive_import');
				}else{
					 $this->assign('style','style="display:none"');
					 if($_GET['types'] == 1){
							$back = '错误！未选择文件或文件格式不正确！&nbsp&nbsp<a href="/index.php/Dev/Sensitive/sensitive_list">点击返回敏感词列表</a>';
					 }elseif($_GET['types'] == 2){
							$back = '错误！未选择文件或文件格式不正确！&nbsp&nbsp<a href="/index.php/Dev/Sensitive/soft_shieldpackagename_list">点击返回软件包名屏蔽管理</a>';
					 }
					 $this->assign('void',$back);
					// 赋值数据集
				     $this->display('Dev:Sensitive:sensitive_import');
				}
		}

	public  function strFilter($str){
	    $str = str_replace('`', '', $str);
	    $str = str_replace('·', '', $str);
	    $str = str_replace('~', '', $str);
	    $str = str_replace('!', '', $str);
	    $str = str_replace('！', '', $str);
	    $str = str_replace('@', '', $str);
	    $str = str_replace('#', '', $str);
	    $str = str_replace('$', '', $str);
	    $str = str_replace('￥', '', $str);
	    $str = str_replace('%', '', $str);
	    $str = str_replace('^', '', $str);
	    $str = str_replace('……', '', $str);
	    $str = str_replace('&', '', $str);
	    $str = str_replace('*', '', $str);
	    $str = str_replace('(', '', $str);
	    $str = str_replace(')', '', $str);
	    $str = str_replace('（', '', $str);
	    $str = str_replace('）', '', $str);
	    $str = str_replace('-', '', $str);
	    $str = str_replace('_', '', $str);
	    $str = str_replace('——', '', $str);
	    $str = str_replace('+', '', $str);
	    $str = str_replace('=', '', $str);
	    $str = str_replace('|', '', $str);
	    $str = str_replace('\\', '', $str);
	    $str = str_replace('[', '', $str);
	    $str = str_replace(']', '', $str);
	    $str = str_replace('【', '', $str);
	    $str = str_replace('】', '', $str);
	    $str = str_replace('{', '', $str);
	    $str = str_replace('}', '', $str);
	    $str = str_replace(';', '', $str);
	    $str = str_replace('；', '', $str);
	    $str = str_replace(':', '', $str);
	    $str = str_replace('：', '', $str);
	    $str = str_replace('\'', '', $str);
	    $str = str_replace('"', '', $str);
	    $str = str_replace('“', '', $str);
	    $str = str_replace('”', '', $str);
	    $str = str_replace(',', '', $str);
	    $str = str_replace('，', '', $str);
	    $str = str_replace('<', '', $str);
	    $str = str_replace('>', '', $str);
	    $str = str_replace('《', '', $str);
	    $str = str_replace('》', '', $str);
	    $str = str_replace('.', '', $str);
	    $str = str_replace('。', '', $str);
	    $str = str_replace('/', '', $str);
	    $str = str_replace('、', '', $str);
	    $str = str_replace('?', '', $str);
	    $str = str_replace('？', '', $str);
	    $str = str_replace(' ', '', $str);
	    return trim($str);
	}
	//软件包名屏蔽管理
	 public function soft_shieldpackagename_list(){ 
		$words = trim($_POST['word']); 
		//$words_filter = $this->strFilter(trim($_POST['word'])); 
		$words_filter = trim($_POST['word']); 
		$abc = trim($_GET['abc']); 
		if($_GET['word']){
			//$words_filter = $this->strFilter(trim($_GET['word'])); 
			$words_filter = trim($_GET['word']); 
		}
		$model = M('sensitive');
		if(!empty($words) && isset($words) || !empty($words_filter)){
			$abc_arr = $model->field('abc')->where("word like binary '%{$words_filter}%' and type=2")->group('abc')->select();
			if($abc){
				$res = $model->where("word like binary '%{$words_filter}%' and type=2 and abc = '{$_GET['abc']}'")->select();
				$count = $model->where("word like binary '%{$words_filter}%' and type=2 and abc = '{$_GET['abc']}'")->count();
				$abc = $abc ; 
			}else{
				$res = $model->where("word like binary '%{$words_filter}%' and type=2")->select();
				$count2 = $model->where("word like binary '%{$words_filter}%' and type=2 ")->count();
				S('count2',$count2,300);
			}
				//统计列表总数
			$abc_count = $this -> pub_getcount(2,$abc_arr,$words_filter);
			$this->assign('count2',S('count2'));
			$this->assign('abc_count',$abc_count);
			if(!$count){
				$count = S('count2');
			}
			$this->assign('count',$count);
			$this->assign('keys',$words_filter); 					
			$this->assign('abc',$abc);
			$this->assign('abc_arr',$abc_arr);
			$this->assign('list',$res);
			$this ->display('Dev:Sensitive:shieldpackagename');
		}else{
			import('@.ORG.Page2');// 导入分页类
			// 查询满足要求的总记录数
			$where = array();
			$where['type'] = 2;
			if($abc){
				$where['abc'] = $abc;
			}			
			$count = $model->where($where)->count();
			$count2 = $model->where("type=2")->count();
			// 实例化分页类 传入总记录数和每页显示的记录数
			$Page       = new Page($count,1000);
			// 分页显示输出
			$show       = $Page->show();
			 // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
			$res = $model->where($where)->order('abc asc')->limit($Page->firstRow.','.$Page->listRows)->select();
			//echo $model->getlastsql();
			$abc_arr = array('1','A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
			//统计个个列表总数
			$abc_count = $this -> pub_getcount(2,$abc_arr);
			$this->assign('abc_arr',$abc_arr);
			$this->assign('abc_count',$abc_count);
			$this->assign('count',$count2);
			$this->assign('abc',$abc);
			$this->assign('list',$res);// 赋值数据集
			$this->assign('page',$show);// 赋值分页输出
			$this ->display();
		}
	}	
	//评论敏感词管理
	 public function badcomment_list(){ 
		$words = trim($_POST['word']); 
		$words_filter = $this->strFilter(trim($_POST['word'])); 
		$abc = trim($_GET['abc']); 
		if($_GET['word']){
			$words_filter = $this->strFilter(trim($_GET['word'])); 
		}
		$model = M('sensitive');
		if(!empty($words) && isset($words) || !empty($words_filter)){
			$abc_arr = $model->field('abc')->where("word like binary '%{$words_filter}%' and type=3")->group('abc')->select();
			if($abc){
				$res = $model->where("word like binary '%{$words_filter}%' and type=3 and abc = '{$_GET['abc']}'")->select();
				$count = $model->where("word like binary '%{$words_filter}%' and type=3 and abc = '{$_GET['abc']}'")->count();
				$abc = $abc ; 
			}else{
				$res = $model->where("word like binary '%{$words_filter}%' and type=3 ")->select();
				$count2 = $model->where("word like binary '%{$words_filter}%' and type=3 ")->count();
				S('count2',$count2,300);
			}
			if(!$count){
				$count = S('count2');
			}
				//统计列表总数
			$abc_count = $this -> pub_getcount(3,$abc_arr,$words_filter);
			$this->assign('count2',S('count2'));
			$this->assign('abc_count',$abc_count);
			$this->assign('count',$count);
			$this->assign('keys',$words_filter); 					
			$this->assign('abc',$abc);
			$this->assign('abc_arr',$abc_arr);
			$this->assign('list',$res);
			$this ->display('Dev:Sensitive:badcomment');
		}else{
			import('@.ORG.Page2');// 导入分页类
			$where = array();
			$where['type'] = 3;
			if($abc){
				$where['abc'] = $abc;
			}			
			$count = $model->where($where)->count();
			$count2 = $model->where("type=3")->count();
			// 实例化分页类 传入总记录数和每页显示的记录数
			$Page       = new Page($count,1000);
			// 分页显示输出
			$show       = $Page->show();
			 // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
			$res = $model->where($where)->order('abc asc')->limit($Page->firstRow.','.$Page->listRows)->select();
			$abc_arr = array('1','A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
			//统计个个列表总数
			$abc_count = $this -> pub_getcount(3,$abc_arr);
			$this->assign('abc_arr',$abc_arr);
			$this->assign('abc_count',$abc_count);
			$this->assign('count',$count2);
			$this->assign('abc',$abc);// 赋值数据集
			$this->assign('list',$res);// 赋值数据集
			$this->assign('page',$show);// 赋值分页输出
			$this ->display();
		}
	}	
	//软件提醒词管理
	 public function soft_remindwords_list(){ 
		$words = trim($_POST['word']); 
		$words_filter = $this->strFilter(trim($_POST['word'])); 
		$abc = trim($_GET['abc']); 
		if($_GET['word']){
			$words_filter = $this->strFilter(trim($_GET['word'])); 
		}
		$model = M('sensitive');
		if(!empty($words) && isset($words) || !empty($words_filter)){
			$abc_arr = $model->field('abc')->where("word like binary '%{$words_filter}%' and type=4")->group('abc')->select();
			if($abc){
				$res = $model->where("word like binary '%{$words_filter}%' and type=4 and abc = '{$_GET['abc']}'")->select();
				$count = $model->where("word like binary '%{$words_filter}%' and type=4 and abc = '{$_GET['abc']}'")->count();
				$abc = $abc ; 
			}else{
				$res = $model->where("word like binary '%{$words_filter}%' and type=4")->select();
				$count2 = $model->where("word like binary '%{$words_filter}%' and type=4 ")->count();
				S('count2',$count2,300);
			}
			if(!$count){
				$count = S('count2');
			}
				//统计列表总数
			$abc_count = $this -> pub_getcount(4,$abc_arr,$words_filter);
			$this->assign('count2',S('count2'));
			$this->assign('abc_count',$abc_count);
			$this->assign('count',$count);
			$this->assign('keys',$words_filter); 					
			$this->assign('abc',$abc);
			$this->assign('abc_arr',$abc_arr);
			$this->assign('list',$res);
			$this ->display('Dev:Sensitive:soft_remindwords');
		}else{
			import('@.ORG.Page2');// 导入分页类
			$where = array();
			$where['type'] = 4;
			if($abc){
				$where['abc'] = $abc;
			}			
			$count = $model->where($where)->count();
			$count2 = $model->where("type=4")->count();
			// 实例化分页类 传入总记录数和每页显示的记录数
			$Page       = new Page($count,1000);
			// 分页显示输出
			$show       = $Page->show();
			 // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
			$res = $model->where($where)->order('abc asc')->limit($Page->firstRow.','.$Page->listRows)->select();
			//echo $model->getlastsql();
			$abc_arr = array('1','A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
			//统计个个列表总数
			$abc_count = $this -> pub_getcount(4,$abc_arr);
			$this->assign('abc_arr',$abc_arr);
			$this->assign('abc_count',$abc_count);
			$this->assign('count',$count2);			
			$this->assign('abc',$abc);// 赋值数据集
			$this->assign('list',$res);// 赋值数据集
			$this->assign('page',$show);// 赋值分页输出
			$this ->display();
		}
	}	
	//软件包名高亮显示
	 public function package_highlight_list(){ 
		$words = trim($_POST['word']); 
		//$words_filter = $this->strFilter(trim($_POST['word'])); 
		$words_filter = trim($_POST['word']); 
		$abc = trim($_GET['abc']); 
		if($_GET['word']){
			//$words_filter = $this->strFilter(trim($_GET['word'])); 
			$words_filter = trim($_GET['word']); 
		}
		$model = M('sensitive');
		if(!empty($words) && isset($words) || !empty($words_filter)){
			$abc_arr = $model->field('abc')->where("word like binary '%{$words_filter}%' and type=5")->group('abc')->select();
			if($abc){
				$res = $model->where("word like binary '%{$words_filter}%' and type=5 and abc = '{$_GET['abc']}'")->select();
				$count = $model->where("word like binary '%{$words_filter}%' and type=5 and abc = '{$_GET['abc']}'")->count();
				$abc = $abc ;
			}else{
				$res = $model->where("word like binary '%{$words_filter}%' and type=5")->select();
				$count2 = $model->where("word like binary '%{$words_filter}%' and type=5")->count();
				S('count2',$count2,300);
			}
			if(!$count){
				$count = S('count2');
			}
				//统计列表总数
			$abc_count = $this -> pub_getcount(5,$abc_arr,$words_filter);
			$this->assign('count2',S('count2'));
			$this->assign('abc_count',$abc_count);
			$this->assign('count',$count);
			$this->assign('keys',$words_filter); 					
			$this->assign('abc',$abc);
			$this->assign('abc_arr',$abc_arr);
			$this->assign('list',$res);
			$this ->display('Dev:Sensitive:package_highlight');
		}else{
			import('@.ORG.Page2');// 导入分页类
			$where['type'] = 5;
			if($abc){
				$where['abc'] = $abc;
			}			
			$count = $model->where($where)->count();
			$count2 = $model->where("type=5")->count();
			// 实例化分页类 传入总记录数和每页显示的记录数
			$Page       = new Page($count,1000);
			// 分页显示输出
			$show       = $Page->show();
			 // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
			$res = $model->where($where)->order('abc asc')->limit($Page->firstRow.','.$Page->listRows)->select();
			//echo $model->getlastsql();
			$abc_arr = array('1','A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
			//统计个个列表总数
			$abc_count = $this -> pub_getcount(5,$abc_arr);
			$this->assign('abc_arr',$abc_arr);
			$this->assign('abc_count',$abc_count);
			$this->assign('count',$count2);				
			$this->assign('abc',$abc);// 赋值数据集
			$this->assign('list',$res);// 赋值数据集
			$this->assign('page',$show);// 赋值分页输出
			$this ->display();
		}
	}
	//个个列表的总数据
	public function pub_getcount($type,$abc_arr,$words_filter){
		$model = M('sensitive');
		//统计个个列表总数
		if(!$words_filter){
			if(!S('data'.$type)){
				$abc_count = array();
				foreach($abc_arr as $key => $val){
					$abc_count[$val] = $model->where("type = {$type} and abc = '{$val}'")->count();
				}
				//写入缓存
				S('data'.$type,$abc_count,300);
			}else{
				//读取缓存
				$abc_count = S('data'.$type);
			}
		}else{
			if(!S($words_filter.$type)){
				$abc_count = array();
				foreach($abc_arr as $k => $v){
					$abc_count[$v['abc']] =  $model->where("type = {$type} and abc = '{$v['abc']}' and word like binary '%{$words_filter}%'")->count();
					//写入缓存
					S($words_filter.$type,$abc_count,300);
				}
			}else{
				//读取缓存
				$abc_count = S($words_filter.$type);
			}
		}
		return $abc_count;
	}
	//特殊敏感词列表
	public function special_list(){
		$model =  M('special_sensitive');
		$where = array();
		$where['status'] = 1;
		if($_GET){
			if($_GET['word'] != "敏感词"){
				$where['word'] = array('like',"%{$_GET['word']}%");
			}
		}
		// 导入分页类
		import('@.ORG.Page2');
		$count = $model->where($where)->count();
		$Page       = new Page($count,10);
		$show       = $Page->show();
		$list = $model->where($where)->order('add_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		$scope = array(
			'1' => '软件名称',
			'2' => '软件简介',
			'3' => '关键词'
		);
		$softfrom =  array(
			'1' =>'新软件提交',
			'2' =>'修改提交',
			'3' =>'升级提交',
		);
		$category_list = $model->table('sj_category')->where('status=1')->select();
		foreach($category_list as $key => $val){
			$category_list[$val['category_id']] = $val['name'];
		}
		foreach($list as $key => $val){
			if($val['scope']){
				$scope_arr = explode(',',$val['scope']);
				$scope_str = '';
				foreach($scope_arr as $k => $v){
					$scope_str .= $scope[$v]."<br/>";
				}
			}
			$list[$key]['scope'] = $scope_str;
			$list[$key]['add_time'] = date("Y-m-d H:i:s",$val['add_time']);
			if($val['soft_scope']){
				$soft_scope_arr = explode(',',$val['soft_scope']);
				$soft_scope_str = '';
				foreach($soft_scope_arr as $k => $v){
					$soft_scope_str .= $category_list[$v].",";
				}
				$list[$key]['soft_scope'] = substr($soft_scope_str,0,-1);
			}
			if($val['softfrom']){
				$softfrom_arr = explode(',',$val['softfrom']);
				$softfrom_str = '';
				foreach($softfrom_arr as $k => $v){
					$softfrom_str .= $softfrom[$v]."<br/>";
				}
				$list[$key]['softfrom'] = $softfrom_str;
			}
		}
		//软件类别
		$Softaudit = D("Dev.Softaudit");
		$catname = $Softaudit ->getCategoryArray();
		$cname = array();
		foreach($catname[0] as $n){
			$threecat = array();
			foreach($catname[$n['category_id']] as $v){
				foreach( $catname[$v['category_id']] as $m){
					$threecat[] = $m;
				}
			}
			$n['sub'] = $threecat;
			$cname[] = $n;			
		}
		$this -> assign('cname',$cname);
		
		$this->assign('word',$_GET['word'] ? $_GET['word'] : "敏感词");
		$this->assign('list',$list);
		$this->assign('page',$show);
		$this ->display();
	}	
	//添加特殊敏感词
	public function add_special(){
		$model = M('special_sensitive');
		$spcial = $_POST['spcial'];
		$white_obj = $_POST['is_type'];
		$cateid = substr($_POST['cateid'],0,-1);
		$word = trim($_POST['word']);
		
		$softfrom  = $_POST['softfrom'];
		if($word == "输入敏感词，每个敏感词换行输入，单个字符不允许添加" && empty($_FILES['csv']['name']) ){
			$this -> error('请填写敏感词');
		}
		//敏感词
		if($word != "输入敏感词，每个敏感词换行输入，单个字符不允许添加"){
			$words_decode = urldecode(trim($_POST['word'])); 
			$words_arr  = explode("\n",$words_decode);
			$words = array();
			foreach($words_arr as $val){
				$words[] = trim($val);
			}
			$words_arr = array_unique($words);
		}
		//导入文件
		if(!empty($_FILES)){
			if (!empty($_FILES['csv']['tmp_name'])) {
				$array = array('csv');
				$ytypes = $_FILES['csv']['name'];
				$info = pathinfo($ytypes);
				$type =  $info['extension'];//获取文件件扩展名
				if(!in_array($type,$array)){
					$this->assign('jumpUrl',$_SERVER['HTTP_REFERER']);
					$this->error('上传格式错误');
				}
				
				if (!empty($_FILES['csv']['tmp_name'])){		
					$data = file_get_contents($_FILES['csv']['tmp_name']);
					//判断是否是utf-8编辑
					if(mb_check_encoding($data,"utf-8") != true){
						$data = iconv("gbk","utf-8", $data);
					}
					$data = str_replace("\r\n","\n",$data);	
					$data_arr = explode("\n", $data);
				}
			}
			$file_data = array_unique($data_arr);
		}
		//合并数组---去重--去空
		if($words_arr && $file_data){
			$result_word = array_unique(array_merge($words_arr, $file_data));
		}elseif($words_arr){
			$result_word = $words_arr;
		}elseif($file_data){
			$result_word =  $file_data;
		}
		$map = array();		
		//适用范围
		if($spcial){
			$spcial_str = '';
			foreach($spcial as $v){
				$spcial_str .= $v.",";
			}
			$map['scope'] = substr($spcial_str,0,-1);
		}else{
			$this->assign("jumpUrl","/index.php/Dev/Sensitive/special_list/");
			$this ->error("请选择适用范围");
		}
		//软件提交路径
		if($softfrom){
			$softfrom_str = '';
			foreach($softfrom as $v){
				$softfrom_str .= $v.",";
			}
			$map['softfrom'] = substr($softfrom_str,0,-1);
		}
		if($cateid){
			$map['soft_scope'] = $cateid;
		}
		//白名单开放范围
		if($white_obj){
			foreach($white_obj as $v){
				if($v == 1){
					if($_POST['dev_id'] != '输入开发者ID,多个以逗号隔开'){
						$devid_arr = array_unique(explode(',',$_POST['dev_id']));
						$devid_error = '';
						$devid_str = '';
						foreach($devid_arr as $val){
							if($val){
								$devid = $model ->table('pu_developer')-> where("email_verified='1' and status=0 and dev_id='{$val}'")->find();
								if(!$devid){
									$devid_error .= $val.";";
								}else{
									$devid_str .= $devid['dev_id'].",";	
								}
							}
						}
					}else{
						$this -> error('输入ID不为空');
					}
				}
				if($v == 2){
					if(isset($_POST['type'])){
						$map['white_obj'] = $_POST['type'];
					}else{
						$this -> error('请选择类型');
					}
				}
			}
		}
		$error = '';	
		$error2 = '';	
		$map['add_time'] = time();
		$map['update_time'] = time();
		trim($_POST['info']) && $map['info'] = trim($_POST['info']);
		if($devid_str){
			$map['devid_str']  = substr($devid_str,0,-1);
		}
		foreach($result_word as $val){
			if(empty($val)){
				continue;
			}
			$rs = $model -> where("word='{$val}' and status=1")->find();
			$rt = $model ->table('sj_sensitive')-> where("word='{$val}' and type=1")->find();
			if($rs || $rt){
				$error2 .=  "【{$val}】";
				continue;
			}
			$map['word'] = $val;
			$res = $model->add($map);
			if(!$res){
				$error .=  $val;
			}else{
				$this->writelog("特殊敏感词添加了id为{$res}的记录",'sj_special_sensitive',$res,__ACTION__ ,"","add");
			}
		}
		if($devid_error != ''){
			$this->assign("jumpUrl","/index.php/Dev/Sensitive/special_list/");
			//$this -> error("ID添加失败，失败开发者ID包括：{$devid_error}");
			echo "<script> alert('ID添加失败，失败开发者ID包括：{$devid_error}')</script>";
		}
		if(!empty($error)){
			$this->assign("jumpUrl","/index.php/Dev/Sensitive/special_list/");
			$this -> error("{$error}的敏感词添加失败");
		}elseif(!empty($error2)){
			$this->assign("jumpUrl","/index.php/Dev/Sensitive/special_list/");
			$this -> error("{$error2}已经存在请不要重复添加");
		}else{
			$this->assign("jumpUrl","/index.php/Dev/Sensitive/special_list/");
			$this -> success("添加成功");
		}
	}
	//删除特殊敏感词
	public function del_special(){
		$model =  M('special_sensitive');
		$id_arr = explode(',',$_GET['id']);
		if($id_arr) {
			foreach($id_arr as $key=>$val) {
				if(!is_numeric($val)) unset($id_arr[$key]);
			}
		}
		if(!$id_arr) exit(json_encode(array('code'=>'0','msg'=>'请选择要通过的对象！')));
		$id_str = implode(',',$id_arr);
		$list = $model -> where(array('id'=>array('in',$id_arr))) -> field('id,status') -> select();
		foreach($list as $k => $v){
	 		if($v['status'] == 1){	
				$del = $model -> where("id={$v['id']}") -> save(array('status'=>0));
				if(!$del){
					exit(json_encode(array('code'=>'0','msg'=>"ID为{$v['id']}操作不成功")));
				} else {
					$this->writelog("删除了id为{$v['id']}的特殊敏感词",'sj_special_sensitive',$v['id'],__ACTION__ ,"","del");
				}
			}else{
				exit(json_encode(array('code'=>'0','msg'=>"ID为{$v['id']}已经被删除过了")));
			}
			$idarr[] = $v['id'];
		}
		exit(json_encode(array('code'=>1,'msg'=>$idarr)));
	}
	//编辑特殊敏感词_显示
	public function edit_special_list(){
		$model = M('special_sensitive');
		$edit_list =  $model->where("status = 1 and id = {$_GET['id']}")->find();
		if($edit_list){
			$cateids = explode(',',$edit_list['soft_scope']);
			$cateid = array_flip($cateids);
			$this -> assign('cateid',$cateid);
		}else{
			$this -> error('该条记录无效');
		}
		//软件类别
		$Softaudit = D("Dev.Softaudit");
		$catname = $Softaudit ->getCategoryArray();
		$cname = array();
		foreach($catname[0] as $n){
			$threecat = array();
			foreach($catname[$n['category_id']] as $v){
				foreach( $catname[$v['category_id']] as $m){
					$threecat[] = $m;
				}
			}
			$n['sub'] = $threecat;
			$cname[] = $n;			
		}
		$this -> assign('cname',$cname);

		$scope = explode(',',$edit_list['scope']);		
		$scope = array_flip($scope);
		$this -> assign('scope',$scope);
		$softfrom = explode(',',$edit_list['softfrom']);		
		$softfrom = array_flip($softfrom);
		$this -> assign('softfrom_list',$softfrom);		
		$this -> assign('edit_list',$edit_list);
		$this -> assign('id',$_GET['id']);
		$this ->display();
	}
	//编辑特殊敏感词_提交
	public function edit_special(){
		$model =  M('special_sensitive');
		$cateid_edit = $_POST['cateid_edit'];
		//var_dump($cateid_edit);exit;
		$edit_softfrom = $_POST['edit_softfrom'];
		$id = $_POST['edit_id'];
		if($id){
			$res = $model -> where("id='{$id}' and status=1")->find();
			if($res){
				$data = array();
				$spcial= $_POST['edit_spcial'];
				if($spcial){
					$spcial_str = '';
					foreach($spcial as $v){
						$spcial_str .= $v.",";
					}
					$data['scope'] = substr($spcial_str,0,-1);
				}else{
					$this -> error('请选择适用范围');
				}
				if($edit_softfrom){
					$softfrom_str = '';
					foreach($edit_softfrom as $v){
						$softfrom_str .= $v.",";
					}
					$data['softfrom'] = substr($softfrom_str,0,-1);
				}else{
					$data['softfrom'] = '';
				}
				if(isset($_POST['softobj'])){
					if($cateid_edit){
						$data['soft_scope'] = $cateid_edit;
					}else{
						$data['soft_scope'] = '';
					}
				}
				$white_obj = $_POST['edit_type'];
				
				if($white_obj){
					foreach($white_obj as $v){
						if($v == 1){
							if($_POST['devid_edit']){
								$devid_arr = array_unique(explode(',',$_POST['devid_edit']));
								$devid_error = '';
								$devid_str = '';
								foreach($devid_arr as $val){
									if($val){
										$devid = $model ->table('pu_developer')-> where("email_verified='1' and status=0 and dev_id='{$val}'")->find();
										if(!$devid){
											$devid_error .= $val.";";
										}else{
											$devid_str .= $devid['dev_id'].",";
										}
									}
								}
							}else{
								$this -> error('输入ID不为空');
							}
						}
						if($v == 2){
							if(isset($_POST['type_edit'])){
								$data['white_obj'] = $_POST['type_edit'];
							}else{
								$this -> error('请选择类型');
							}
						}
					}
					if(!in_array('1',$white_obj)){
						$devid_str = '';
					}
					if(!in_array('2',$white_obj)){
						$data['white_obj'] = 2;
					}
				}else{
					$data['white_obj'] = 2;
				}
				$data['update_time'] = time();
				if($devid_str){
					$data['devid_str']  = substr($devid_str,0,-1);
				}else{
					$data['devid_str']  = '';
				}
				$data['info'] = trim($_POST['info']);
				$log_result = $this->logcheck(array('id'=>$id),'sj_special_sensitive',$data,$model);
				$result = $model ->  where("id='{$id}' and status=1")->save($data);
				//echo $model->getlastsql();exit;
				if($devid_error != ''){
					echo "<script> alert('ID添加失败，失败开发者ID包括：{$devid_error}')</script>";
				}
				if($result){
					$this->writelog("特殊敏感词编辑了id为{$id}的记录。{$log_result}",'sj_special_sensitive',$id,__ACTION__ ,"","edit");
					$this->assign("jumpUrl","/index.php/Dev/Sensitive/special_list/");
					$this ->  success('编辑成功');
				}else{
					$this->assign("jumpUrl","/index.php/Dev/Sensitive/special_list/");
					$this ->  success('编辑失败');
				}
			}else{
				$this->assign("jumpUrl","/index.php/Dev/Sensitive/special_list/");
				$this -> error('该条记录无效');
			}
		}else{
			$this->assign("jumpUrl","/index.php/Dev/Sensitive/special_list/");
			$this -> error('此ID不存在');
		}	
	}
	
	public function check_word(){
	    $this->display('Public::check_word');
	}
}