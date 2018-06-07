<?php
class NecessaryAction extends CommonAction {
    #sj_necessary | sj_necessary_soft : db_name
        public $nsm;
        public $nm;
        public $pkgLimit = 3;
     function NeceTypeList() {
         $this -> nm = M("necessary");
         $Necessary_type_list = $this ->nm -> where("status = 1") ->order("rank asc") -> select();
         $count = count($Necessary_type_list);
         $this -> assign('count',$count);
         $this -> assign('typeList',$Necessary_type_list);
         $this -> display("NeceTypeList");
     }
     function NecePkgs() {
         $this -> nsm = M("necessary_soft");
         $Necessary_type_list = $this -> nsm -> where("status = 1") -> select();
         $this -> assign('typeList',$Necessary_type_list);
         $this -> display("NecePkgList");
     }
     function NeceTypeList_add() {
         $typename = $_GET['name'];
         $this -> nm = M("necessary");
         $result = $this -> nm -> where(array("name"=>$typename,"status" => 1)) -> select();
         if(count($result) > 0){
              $this -> error("类别已存在！！");
         }
         $count = $this -> nm ->where("status = 1")-> count();
         $data['rank'] = $count;  //默认位置
         $data['name'] = $typename;
		 $data['status'] = 1;
 		 //$data['start_tm'] = strtotime($this ->formTime($_POST['start_tm']));
		 //$data['end_tm'] = strtotime($this ->formTime($_POST['end_tm']));
		 // if($data['start_tm'] >= $data['end_tm']){
		   // $this -> error("开始时间必须小于结束时间！！");
		  //}
         $affect = $this -> nm -> add($data);
         if($affect > 0){
         	   $this->writelog('装机必备-软件必备管理-添加了name为'.$data['name'].',的必备类别');
			   $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Necessary/NeceTypeList');
			   $this->success('添加成功');
         }
     }
     function NeceTypeList_edit() {
         /*
         （类别从位置n变动到位置m，若n>m，则m前、n后均不变，n变为m，[>=m，<n]区间内位置依次+1；
         若n<m，则n前、m后均不变，n变为m，[>n，<=m]区间内位置依次-1）
         */
         $id = escape_string($_GET['id']);
         $m = escape_string($_GET['rank']);           //要替换的rank
         $n = escape_string($_GET['currank']);       //当前rank
         $this -> nm = M("necessary");
         $where = "";
         if($n > $m){   // 由大到小
              $where = "status = 1 and rank >=".$m." and rank <".$n;
              $updatedata =  "rank + 1";
         }else if($n<$m){ // 由小到大
             $where = "status = 1 and rank >".$n." and rank <=".$m;
             $updatedata =  "rank - 1";
         }

         $this -> nm  ->query("update __TABLE__  set rank = ".$updatedata ." where ".$where);
         $this -> nm -> query("update __TABLE__ set rank = ".$m." where id = " .$id);
         $this->writelog('装机必备-软件必备管理-编辑了为'.$id.',的必备类别');
		 $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Necessary/NeceTypeList');
		 $this->success('编辑成功');
     }

     function NeceTypeList_delete() {
         $id = $_GET['id'];
         $this -> nm = M("necessary");
         $this -> nsm = M("necessary_soft");
         $deleteNe = $this -> nm -> where(array("id" => $id)) -> select();
         $rank = $deleteNe[0]['rank'];
		 $data['status'] = 0;
         $affected = $this -> nm -> where(array("id" => $id)) -> save($data);
         $this -> nm -> query("update __TABLE__ set rank = rank -1 where rank > ".$rank." and status =1");
		 $data = array();
		 $data['status'] = 0;
		 $data['end_tm'] = time();
         $this -> nsm -> where(array("nid" => $id)) -> save($data);
         if($affected > 0){
           $this->writelog('装机必备-软件必备管理-删除了为'.$id.',的必备类别');
           $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Necessary/NeceTypeList');
		   $this->success('删除成功');
         }
     }

     function NecePkgs_edit() {
         $typeid = $_GET['id'];
         $this ->nsm = M("necessary_soft");
         $this ->nm = M("necessary");
		 $start_tm = date("m/d/Y",time());
		 $end_tm = date("m/d/Y",time()+3600*24*7);		 
         $nname = $this -> nm -> where(array('id' => $typeid,'status' => 1)) -> getField('name');
         $result = $this -> nsm -> where(array('nid' => $typeid,'status' => 1)) -> select();
         if(!empty($result)) $this -> assign('softList',$result);
		 $this -> assign('start_tm',$start_tm);
		 $this -> assign('end_tm',$end_tm);
		 $this -> assign('nowtime',time());
         $this -> assign('nname',$nname);
         $this -> assign('nid',$typeid);
         $this -> display('NecePkgsEdit');
     }

     function NecePkg_edit() {
          $id= $_POST['id'];
          $nid = $_POST['nid'];
          echo $pm = $_POST['pm'];
          $this ->nsm = M("necessary_soft");
          $ret = $this ->nsm ->where(array('nid' => $nid,'status' => 1))->order("rank asc")->findAll();
          $i = 1;

          foreach($ret as $key => $value){
          	if($value['id'] == $id){
          		$data['rank'] = $pm;
          		if($pm == $i) $i++;
          		$data['package'] = $_POST['package'];
          		$result = $this -> nsm -> where("package ='".$data['package']."' and status = 1 and `id` <> '".$id."'") -> select();
         		if(!empty($result)){
               		$this -> error("该软件已经在[其他]分类中存在，请重新选择");
         		}
		  		//$data['start_tm'] = strtotime($this -> formTime($_POST['start_tm']));
		  		//$data['end_tm'] = strtotime($this ->formTime($_POST['end_tm']))+86399;
		  		//if($data['start_tm'] >= $data['end_tm']){
		    		//$this -> error("开始时间必须小于结束时间！！");
		  		//}
              $log = $this->logcheck(array('id'=>$id),'sj_necessary_soft',$data,$this ->nsm);
          		$affect = $this -> nsm -> where(array("id" => $id)) -> save($data);
          	} else {
          		if($pm == $i) $i++;
          		$data1['rank'] = $i;
              $log = $this->logcheck(array('id'=>$id),'sj_necessary_soft',$data1,$this ->nsm);
          		$affect = $this->nsm-> where("id = ".$value['id']) -> save($data1);
          		$i++;
          	}
          }
		      $this->writelog('装机必备-软件必备管理-编辑了为'.$id.$log);
          //$this->writelog('编辑了为'.$id.',包名为'.$data['package'].'的必备软件',"",$data['package']);
          $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Necessary/NecePkgs_edit/id/'.$nid);
		  $this->success('已修改');
     }
     function NecePkgs_delete(){
         $id = $_GET['id'];
         $this ->nsm = M("necessary_soft");
         $nid = $this -> nsm -> where(array("id" => $id)) -> find();
		 $data['status'] = 0;
		 $data['end_tm'] = time();
         $this -> nsm -> where(array("id" => $id)) -> save($data);
         $this->writelog('装机必备-软件必备管理-删除了为'.$id.'包名为'.$nid['package'].'的必备软件');
         $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Necessary/NecePkgs_edit/id/'.$nid['nid']);
		 $this->success('已删除');
     }
     function NecePkgs_add() {
         $package = $_POST['package'];
         $nid  = $_POST['nid'];
         if($nid == "check" ){
         	$this -> error("请选择分组");
         }
         $this ->nsm = M("necessary_soft");
         $this -> nm = M("necessary");
         $result = $this -> nsm -> where(array("package" => $package, "status" => 1)) -> select();
         if(!empty($result)){
                $this -> error("该软件已经在[其他]分类中存在，请重新选择");
         }

         $result = $this -> nsm -> where(array("package" => $package,"nid" => $nid,"status" => 1)) -> select();
         if(!empty($result)){
                $this -> error("该软件已经在[本]分类中存在，请重新选择");
         }
          $count = $this -> nsm -> where(array("nid" => $nid,"status"=>1)) -> count();
         if($count >=$this -> pkgLimit){
               $this -> error("该分类数量已满");
         }
         $data['package']= $package;
         $data['nid']    = $nid;
         $data['rank']   = $count + 1;
		 $data['status'] = 1;
		 $data['start_tm'] = strtotime($this ->formTime($_POST['start_tm']));
		 $data['end_tm'] = strtotime($this ->formTime($_POST['end_tm'])) + 86399 ;
		  if($data['start_tm'] >= $data['end_tm']){
			$this -> error("开始时间必须小于结束时间！！");
		  }
         $affect = $this -> nsm ->add($data);
         if($affect > 0){
     		 $this->writelog('装机必备-软件必备管理-添加了包名为'.$package.'的软件');
             $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Necessary/NecePkgs_edit/id/'.$nid);
		     $this->success('添加成功');
         }
     }
     function NecePkgs_edit_nname() {
       $this -> nm = M("necessary");
       $id = $_GET['nid'];
	   $old_result_necessary=$this -> nm->where(array("status"=>1,"id"=>$id))->find();
       $data['name'] = $_GET['nname'];
	   $zh_data['name']=$data['name'];
	   $zh_data['status']=1;
       $result = $this -> nm -> where($zh_data) -> select();
       if(count($result) > 0){
           $this -> error("类别已存在！！");
       }
       $log = $this->logcheck(array('id'=>$id),'sj_necessary',$data,$this -> nm);
       $affect = $this -> nm -> where(array('id' => $id)) -> save($data);
       if($affect > 0){
			$configModel = D('Sj.Config');
        	$column_desc = $configModel->getNecessaryColumnDesc();
        	$msg = "装机必备-软件必备管理-编辑了id为[{$id}]的类别 \n";
        	foreach ($data as $key => $val) {
        		if (isset($column_desc[$key]) && $data[$key] != $old_result_necessary[$key]) {
        			$desc = $column_desc[$key];
					$msg .= "将{$desc} 从'{$old_result_necessary[$key]}'修改成 '{$data[$key]}'\n";	
        		}
        	}
		    //$this->writelog($msg);
          $this->writelog("装机必备-软件必备管理-编辑了id为'".$_GET['nid']."'的类别".$log);
            $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Necessary/NecePkgs_edit/id/'.$id);
		    $this->success('编辑成功');
       }else{
       		 $this -> error("修改内容无效");
       }
     }

     function NecePkgList() {
         $this ->nsm = M("necessary_soft");
         $this ->nm = M("necessary");
         $result = $this -> nsm -> query("select * from __TABLE__ where status = 1 order by nid asc");
         $nm = $this -> nm -> where("status = 1") -> getField("id,name");
		 $start_tm = $_POST['start_tm'] ? $_POST['start_tm'] : date("m/d/Y",time());
		 $end_tm = $_POST['end_tm'] ? $_POST['end_tm'] : date("m/d/Y",time()+3600*24*7);
         foreach($result as $idx => $info){
             $nid = $info['nid'];
             $infos[$nid][] = $info;
         }
         $this -> assign('nm',$nm);
		 $this -> assign('start_tm',$start_tm);
		 $this -> assign('end_tm',$end_tm);
		 $this -> assign('nowtime',time());
         $this -> assign('infos' ,$infos);
         $this -> display('NecePkgList');
     }
	 function formTime($date){
	   $time_arr = explode("/",$date);
	   $time_str = $time_arr[2].'-'.$time_arr[0].'-'.$time_arr[1];
	   return $time_str;
	 }
}