<?php
	class TagModel extends Model {
		protected $trueTableName = 'sj_new_tag';
		
		public function getnotagsoftlist($get,$game_category_id='',$notagsoftDefault='')
		{
			
			if($notagsoftDefault==1){
				$order = 'first_time desc';
				if(isset($get['order'])){
					if($get['order']==1){
						$order = 'total_downloaded asc';
					}else if($get['order']==2){
						$order = 'total_downloaded desc';
					}
				}
			}else{
				if(isset($get['order'])){
					if($get['order']==1){
						$order = 'total_downloaded asc';
					}else if($get['order']==2){
						$order = 'total_downloaded desc';
					}else{
						$order = 'last_refresh desc';
					}
				}
				else
				{
					$order = 'total_downloaded desc';
				}
			}
			

			$where['a.status'] = array('eq',1);
			$where['a.hide'] = array('eq',1);
			$no_package = $this->table('sj_new_tag_package')->where(array('status'=>1))->field('package')->select();
			$where_pack = array();
			foreach($no_package as $k=>$v){
				$where_pack[] = $v['package'];
			}
			$where_pack_str = implode("','",$where_pack);
			$where['package']  = array('not in',"'{$where_pack_str}'");

			if(isset($get['softname']))
			{
				$where['a.softname'] = array('like', '%'.$get['softname'].'%');
			}

			if(isset($get['package']))
			{
				$where['a.package'] = array('eq', $get['package']);
			}

			if(isset($get['downloaded_s']))
			{
				$where['a.total_downloaded'][] = array('EGT',$get['downloaded_s']);
			}
			if(isset($get['downloaded_e']))
			{
				$where['a.total_downloaded'][] = array('ELT',$get['downloaded_e']);
			}
			$cids = substr($get['cateid'],0,-1);
			if(strlen($cids)>0){
				$cids = "',".str_replace(",",",',',",$cids).",'";
				$where['a.category_id']  = array('in',$cids);
			}
			if($notagsoftDefault==1){
				$where['a.category_id']  = array('in',$game_category_id);
			}
			if(isset($_GET['count']))
			{
				$count = $_GET['count'];
			}else
			{
				$count = $this->table('sj_soft a')->field("softid")->where($where)->count();
			}
			if($notagsoftDefault==1){
				$count = 200;
			}
			import("@.ORG.Page");
			$page = new Page($count, 10);

			$rs = $this->table('sj_soft a')->field("softid,softname,package,category_id,total_downloaded,a.last_refresh")->where($where)->limit($page->firstRow.','.$page->listRows)->order($order)->select();
			// echo $this->getlastsql();

			$page->parameter ='count='.$count;
			$page->setConfig('header','篇记录');
			$page->setConfig('first','<<');
			$page->setConfig('last','>>');
			$show =$page->show();    
			$res = array(
				'list'=>$rs,
				'page'=>$show,
				'count'=>$count,
			);
			return $res;
		}
		
		public function get_tags($where){
            $where['status'] = 1;
            $tags = $this->table('sj_new_tag')->where($where)->field('tag_id,tag_name')->findAll();
			//echo $this->getLastSql();
            $res = array();
            foreach($tags as $k=>$v){
                $res[$v['tag_id']] = $v['tag_name'];
            }
			return $res;
			
		}
		
		public function save_tags($data,$update_history=0){
			$all_package = explode(',',$data['softid']);
			$tag_str = implode("','",$data['tag_id']);
			foreach($all_package as $k=>$v){
				$insert_id = array();
				$package = $v;
				if($update_history == 1){
					$del_tag  = $this->table('sj_new_tag_package')->where(array('status'=>1,'package'=>$package,'tag_id'=>array('exp'," not in ('{$tag_str}')")))->findAll();
					if($del_tag){
						foreach($del_tag as $k=>$v){
							$this->where(array('tag_id'=>$v['tag_id']))->save(array('soft_num'=>array('exp',' soft_num - 1')));
						}
					}
					$this->table('sj_new_tag_package')->where(array('status'=>1,'package'=>$package,'tag_id'=>array('exp'," not in ('{$tag_str}')")))->save(array('status'=>0));
				}

				//echo $this->getLastSql();
				$has_tag = $this->table('sj_new_tag_package')->where(array('status'=>1,'package'=>$package,'tag_id'=>array('exp'," in ('{$tag_str}')")))->field('id,tag_id')->findAll();
				$has_id = array();
				if($has_tag){
					foreach($has_tag as $h_k=>$h_v){
						$has_id[] = $h_v['tag_id'];
					}
				}
				$insert_id = array_diff($data['tag_id'],$has_id);
				if(count($insert_id)>0){
					$now = time();
					foreach($insert_id as $i_k=>$i_v){
						$idata = array(
							'package' => $package,
							'tag_id' => $i_v,
							'create_tm' => $now
						);
						$res = $this->table('sj_new_tag_package')->add($idata);
						if($res){
							$this->where(array('tag_id'=>$i_v))->save(array('soft_num'=>array('exp',' soft_num + 1')));
						}
					}
				}
			}
			return true;
		}
		
		/**
		 *  获取有标签软件列表
		 */	
		public function getagsoftlist($get)
		{
			if(isset($get['order'])){
				if($get['order']==1){
					$order = 'total_downloaded asc';
				}else if($get['order']==2){
					$order = 'total_downloaded desc';
				}else{
					$order = 'last_refresh desc';
				}
			}else{
				$order = 'total_downloaded desc';
			}

			$where['a.status'] = array('eq',1);
			$where['a.hide'] = array('eq',1);
			if(isset($get['softname']))
			{
				$where['a.softname'] = array('like', '%'.$get['softname'].'%');
			}

			if(isset($get['package']))
			{
				//$where['a.package'] = array('like', '%'.$get['package'].'%');
				$where['a.package'] = array('eq', $get['package']);
			}

			if(isset($get['tag_name']))
			{
				$tags = $get['tag_name'];
				$tag_id = $this->getTagidbyname($tags);
				$where['tag_id'] = array('eq',$tag_id);
			}

			if(isset($get['begintime']))
			{
				$where['a.last_refresh']  = array('between',''.strtotime($get['begintime']).','.strtotime($get['endtime']));
			}

			if(isset($get['downloaded_s']))
			{
				$where['a.total_downloaded'][] = array('EGT',$get['downloaded_s']);
			}
			if(isset($get['downloaded_e']))
			{
				$where['a.total_downloaded'][] = array('ELT',$get['downloaded_e']);
			}
			$cids = substr($get['cateid'],0,-1);
			if(strlen($cids)>0){
				$cids = "',".str_replace(",",",',',",$cids).",'";
				$where['a.category_id']  = array('in',$cids);
			}
			$where['b.status'] = array('eq',1);
			if(isset($_GET['count']))
			{
				$count = $_GET['count'];
			}else
			{
				$res = $this->table('sj_soft a')->field("count(DISTINCT(a.softid)) as tp_count")->join('INNER JOIN sj_new_tag_package b ON a.package = b.package')->where($where)->find();
				$count = $res['tp_count'];
				//echo $this->getlastsql();
			}
			import("@.ORG.Page");
			$page = new Page($count, 10);

			$rs = $this->table('sj_soft a')->field("DISTINCT(a.softid),a.softname,a.package,a.category_id,total_downloaded,a.last_refresh")->join('INNER JOIN sj_new_tag_package b ON a.package = b.package')->where($where)->limit($page->firstRow.','.$page->listRows)->order($order)->select();
			//echo $this->getlastsql();


			$page->parameter ='count='.$count;
			$page->setConfig('header','篇记录');
			$page->setConfig('first','<<');
			$page->setConfig('last','>>');
			$show =$page->show();    
			$res = array(
				'list'=>$rs,
				'page'=>$show,
				'count'=>$count,
			);
			return $res;
		} 

		public function getTagidbyname($tag_name)
		{
			$rs = $this->field('tag_id')->where("tag_name= '$tag_name' and status=1")->find();
			return $rs['tag_id'];
		}
		
		 public function getsoftinfo($softid)
		{
			$sql ="SELECT a.softid,a.softname,a.category_id,a.package,GROUP_CONCAT(c.tag_id ORDER BY c.tag_id ASC)as tag_ids,GROUP_CONCAT(c.tag_name order by c.tag_name ASC) AS tags FROM sj_soft a INNER JOIN  sj_new_tag_package b ON a.package = b.package INNER JOIN sj_new_tag c ON b.`tag_id`=c.`tag_id` WHERE a.status=1 AND a.hide=1 and c.status=1 AND a.`softid`='$softid' and b.status = 1";
			$rs = $this->query($sql);
			return $rs[0];
		}
		
		public function get_package_tag($package){
			$sql = "select a.tag_id,a.package,b.tag_name from sj_new_tag_package as a inner join sj_new_tag as b on a.tag_id = b.tag_id where package = '{$package}' and a.status = 1";
			$rs = $this->query($sql);
			$return = array();
			foreach($rs as $k=>$v){
				$return[] = array('tag_id'=> $v['tag_id'],'tag_name'=>$v['tag_name']);
			}
			return $return;
		}
		
		public function save_import_tags_re($category,$data){
			$failnum = 0;
			$correctnum = 0;
			$category_str = implode("','",$category);
			$cate_info = $this->table('sj_new_tag_category')->where(array('id'=>array('exp'," in ('{$category_str}')"),'type'=>2,'status'=>1))->field('id,name')->findAll();
			$has_cate = array();
			foreach($cate_info as $k=>$v){
				$has_cate[] = $v['id'];
			}
			$no_cate = array_flip(array_diff($category,$has_cate));
			$fail_arr = array();
			foreach($data as $k=>$v){
				if(isset($no_cate[$v[0]])){
					$v['error'] = '标签分类ID不存在';
					$fail_arr[] = $v;
					$failnum++;
					continue;
				}
				$tag = explode(',',$v[1]);
				$tag = array_unique($tag);
				$tag_str = implode("','",$tag);
				$regular = $this->tag_regular();
				$tag_bo = true;
				foreach($tag as $tv){
					$len = mb_strlen($tv, 'utf-8');
					if ($len > 10||$len==0) {
						$tag_bo = false;
						$v['error'] = '标签10个汉字以内';
						$fail_arr[] = $v;
						$failnum++;
						break;
					}
					if(preg_match($regular,$tv)){
						$tag_bo = false;
						$v['error'] = '标签格式错误';
						$fail_arr[] = $v;
						$failnum++;
					}
				}
				if(!$tag_bo) continue;
				$tag_info = $this->tag_process($tag,$tag_str);
				$tag_info_str = implode("','",$tag_info);
				$has_tag_cate = $this->table('sj_new_tag_cat_tag')->where(array('c_id'=>$v[0],'status'=>1,'tag_id'=>array('exp'," in ('{$tag_info_str}')")))->select();
				$has_tag_cate_arr = array();
				foreach($has_tag_cate as $hk=>$hv){
					$has_tag_cate_arr[] = $hv['tag_id'];
				}
				$no_has_tag = array_diff($tag_info,$has_tag_cate_arr);
				$update_num = count($no_has_tag);
				if($update_num>0){
					$now = time();
					$add_num = 0;
					foreach($no_has_tag as $nv){
						$ins = $this->table('sj_new_tag_cat_tag')->add(array('tag_id'=>$nv,'c_id'=>$v[0],'create_tm'=>$now));
						if($ins) $add_num++;
					}
					$this->table('sj_new_tag_category')->where(array('id'=>$v[0]))->save(array('tag_num'=>array('exp'," tag_num + {$add_num}")));
//					echo $this->getLastSql();exit();
				}
				$correctnum++;
			}
			return array($failnum,$correctnum,$fail_arr);
		}

		public function tag_regular(){
			return "/[^\x{4e00}-\x{9fa5}a-z\d-_]/iu";
		}

		public function tag_process($tag,$tag_str){
			$has_tag = $this->table('sj_new_tag')->where(array('tag_name'=>array('exp'," in ('{$tag_str}')"),'status'=>1))->findAll();
//				echo $this->getLastSql();
				$has_tag_arr = $has_tag_info = array();
				if($has_tag){					
					foreach($has_tag as $h_k=>$h_v){
						$has_tag_info[$h_v['tag_name']] = $h_v['tag_id'];
						$has_tag_arr[] = $h_v['tag_name'];
					}
				}
				$no_tag = array_diff($tag,$has_tag_arr);
				$no_tag_arr = array();
				//插入不存在标签
				if(count($no_tag)>0){
					foreach($no_tag as $nk=>$nv){
						$data = array(
							'tag_name' => $nv,
							'abc' => strtoupper(substr(Pinyin(trim($nv)),0,1)),
							'create_tm' => time()
						);
						$id = $this->table('sj_new_tag')->add($data);
						//echo $this->getLastSql();
						$no_tag_arr[$nv] = $id;
					}
				}
				$tag_info = array_merge($has_tag_info,$no_tag_arr);
				return $tag_info;
		}
		
		public function save_import_tags($package,$data){
			$failnum = 0;
			$correctnum = 0;
			$package_str = implode("','",$package);
			$soft = $this->table('sj_soft')->where(array('hide'=>1,'status'=>1,'package'=>array('exp'," in ('{$package_str}')")))->field('softid,softname,package')->findAll();
			$has_pack = array();
			$soft_info = array();
			foreach($soft as $k=>$v){
				$soft_info[$v['package']] = $v;
				$has_pack[] = $v['package'];
			}
			$no_pack = array_flip(array_diff($package,$has_pack));
			$fail_arr = array();
			foreach($data as $k=>$v){
				if(isset($no_pack[$v[0]])){
					$v['error'] = '包名不存在';
					$fail_arr[] = $v;
					$failnum++;
					continue;
				}
				$tag = explode(',',$v[2]);
				$tag = array_unique($tag);
				$tag_str = implode("','",$tag);
				$regular = $this->tag_regular();
				$tag_bo = true;
				foreach($tag as $tv){
					$len = mb_strlen($tv, 'utf-8');

					if ($len > 10||$len==0) {
						$tag_bo = false;
						$v['error'] = '标签10个汉字以内';
						$fail_arr[] = $v;
						$failnum++;
						break;
					}
					if(preg_match($regular,$tv)){
						$tag_bo = false;
						$v['error'] = '标签格式错误';
						$fail_arr[] = $v;
						$failnum++;
						break;
					}
				}
				if(!$tag_bo) continue;
				$has_tag = $this->table('sj_new_tag')->where(array('tag_name'=>array('exp'," in ('{$tag_str}')"),'status'=>1))->findAll();
//				echo $this->getLastSql();
				$has_tag_arr = $has_tag_info = array();
				if($has_tag){					
					foreach($has_tag as $h_k=>$h_v){
						$has_tag_info[$h_v['tag_name']] = $h_v['tag_id'];
						$has_tag_arr[] = $h_v['tag_name'];
					}
				}
				$no_tag = array_diff($tag,$has_tag_arr);
				$no_tag_arr = array();
				//插入不存在标签

				if(count($no_tag)>0){
					foreach($no_tag as $nk=>$nv){
						$data = array(
							'tag_name' => $nv,
							'abc' => strtoupper(substr(Pinyin(trim($nv)),0,1)),
							'create_tm' => time()
						);
						$id = $this->table('sj_new_tag')->add($data);
						//echo $this->getLastSql();
						$no_tag_arr[$nv] = $id;
					}
				}
				$tag_info = array_merge($has_tag_info,$no_tag_arr);
				foreach($tag_info as $t_k=>$t_v){
					$has_p_tag =  $this->table('sj_new_tag_package')->where(array('package'=>$v[0],'tag_id'=>$t_v))->find();
					if(!$has_p_tag){
						$res = $this->table('sj_new_tag_package')->add(array('package'=>$v[0],'tag_id'=>$t_v,'create_tm'=>time()));
						if($res){
							$this->where(array('tag_id'=>$t_v))->save(array('soft_num'=>array('exp',' soft_num + 1')));
						}
					}
				}
				$correctnum++;
			}
			return array($failnum,$correctnum,$fail_arr);
		}
		
		//标签分类入库
		public function save_category($data){
			$i_data = array();
			$i_data['type'] = isset($data['type'])?$data['type']:1;
			$i_data['name'] = $data['name'];
			$i_data['weight'] = $data['weight'];
			if(!empty($data['id'])){
				//编辑
				$res = $this->table('sj_new_tag_category')->where(array('id'=>$data['id']))->save($i_data);
				if($i_data['type'] == 2){
					$this->table('sj_new_tag_cat_type')->where(array('c_id'=>$data['id']))->save(array('p_id'=>$data['p_id']));
				}
			}else{
				//添加
				$res = $this->table('sj_new_tag_category')->add($i_data);
				if($i_data['type'] == 2){
					$this->table('sj_new_tag_cat_type')->add(array('c_id'=>$res,'p_id'=>$data['p_id']));
				}
			}
			return true;
			
		}
		
		//获取分类标签
		public function get_category_by_type($type){
			$res = $this->table('sj_new_tag_category')->where(array('type'=>$type,'status'=>1))->select();
			return $res;
		}
		
		public function get_category($where,$type){
			$res = $this->table('sj_new_tag_category')->where($where)->find();
			if($type == 2){
				$p_id_info = $this->table('sj_new_tag_cat_type')->where(array('c_id'=>$where['id'],'status'=>1))->find();
				$res['p_id'] = $p_id_info['p_id'];
			}
			return $res;
		}
		public function get_tag_category(){
			$where = array('type'=>1,'status'=>1);
			$count = $this->table('sj_new_tag_category')->where($where)->count();
			
			import("@.ORG.Page");
			$page = new Page($count, 10);

			$rs = $this->table('sj_new_tag_category')->where($where)->limit($page->firstRow.','.$page->listRows)->select();
			//echo $this->getlastsql();
			$category1 = array();
			foreach($rs as $k=>$v){
				$category1[] = $v['id'];
				
			}
			$category1_str = implode("','",$category1);
			$son = $this->table('sj_new_tag_cat_type as a')->where(array('a.p_id'=>array('in',"'{$category1_str}'"),'a.status'=>1,'b.status'=>1))->join('sj_new_tag_category as b on a.c_id = b.id')->field('a.c_id,a.p_id,b.name,b.weight,b.tag_num')->select();
			//echo $this->getLastSql();
			$son_arr = $son_id = array();
			foreach($son as $k=>$v){
				$son_id[] = $v['c_id'];
				$son_arr[$v['p_id']][] = $v;
			}
			$son_id_str = implode("','",$son_id);
			$sql = "select count(*) as num,c_id from sj_new_tag_cat_tag where status = 1 group by c_id ";

			$tag_info = $this->query($sql);
			$tag_num = array();
			foreach($tag_info as $tk=>$tv){
				$tag_num[$tv['c_id']] = $tv['num'];
			}
			$page->parameter ='count='.$count;
			$page->setConfig('header','篇记录');
			$page->setConfig('first','<<');
			$page->setConfig('last','>>');
			$show =$page->show();    
			$res = array(
				'list'=>$rs,
				'page'=>$show,
				'count'=>$count,
				'son' => $son_arr,
				'tag_num' => $tag_num
			);
			return $res;
		}
		
		public function del_tags($data){
			if(!isset($data['type'])) return false;
			$id = explode(',',$data['id']);
			if(!$id) return false;
			$ids = implode("','",$id);
			$res = $this->table('sj_new_tag_category')->where(array('id'=>array('exp'," in('{$ids}')")))->save(array('status'=>0));
			if($data['type'] == 1){
				if($res){
					$this->table('sj_new_tag_cat_type')->where(array('p_id'=>array('exp'," in('{$ids}')")))->save(array('status'=>0));
				}
			}
			return true;
		}
		
		public function check_name($data){
			$where = array('name'=>$data['name'],'status'=>1);
			if($data['id']){
				$where['id'] = array('exp'," != '{$data['id']}'");
			}
			$res = $this->table('sj_new_tag_category')->where($where)->find();
			if($res){
				return false;
			}
			return true;
		}
	}