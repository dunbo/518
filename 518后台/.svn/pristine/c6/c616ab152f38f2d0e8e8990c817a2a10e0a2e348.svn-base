<?php

class FeatureAction extends CommonAction {

    function feature_manage() {
       $featureObj = M("feature");
       $cooperatorObj= D("Coop.Cooperator");
       $cooperFeatureIconObj= D("Coop.CoopFeatureIcon");
       $cooperFeatureObj= D("Coop.CoopFeature");

       #$where = array("status" => 1,"channel_id" => 469); //有效专题
       $feature_list = $featureObj -> where("status = 1 and channel_id like '%,469,%'") ->select();
       $cooper_list = $cooperatorObj -> where("status = 1") -> select();

       $cf_list = $cooperFeatureObj -> where("status=1") -> select();
       $cfi_list = $cooperFeatureIconObj -> where("status=1") -> select();
       $cooper_arr = array();
       foreach($cooper_list as $info){
              $cooper_arr[$info['id']] = $info;
       }
       $feature_arr = array();
       foreach($feature_list as $info){
          $feature_arr[$info['feature_id']] = $info;
       }
       $cfi_arr = array();
       foreach($cfi_list as $info){
           $cfi_arr[$info['coop_id']][$info['feature_id']]['info'] = $info;
        }
        $cf_arr = array();
        foreach($cf_list as $info){
           $cf_arr[$info['coop_id']][$info['feature_id']]['coop_name'] = $cooper_arr[$info['coop_id']]['name'];
           $cf_arr[$info['coop_id']][$info['feature_id']]['feature_name'] = $feature_arr[$info['feature_id']]['name'];
           $cf_arr[$info['coop_id']][$info['feature_id']]['coop_id'] = $info['coop_id'];
           $cf_arr[$info['coop_id']][$info['feature_id']]['feature_id'] = $info['feature_id'];
           $cf_arr[$info['coop_id']][$info['feature_id']]['show_status'] = $info['show_status'];
           $cf_arr[$info['coop_id']][$info['feature_id']]['info'] =$cfi_arr[$info['coop_id']][$info['feature_id']]['info'];
        }
        $cf_info_arr = array();
        foreach($cf_arr as $coop_arr){
          foreach($coop_arr as $info){
              $cf_info_arr[] = $info;
          }
        }
       $this -> assign('cf_arr', $cf_info_arr);
       $this -> assign('cooper_list',$cooper_list);
       $this -> assign('feature_list',$feature_list);
       $this -> display("feature_manage");
    }

   function addFeature() {
      $cooperatorObj= D("Coop.Cooperator");
      $cooperFeatureObj= D("Coop.CoopFeature");
      $feature_id = escape_string($_POST['feature_id']);
      $coop_id = escape_string($_POST['cooper_id']);
      if(empty($feature_id) || empty($coop_id)){
           $this -> error("请选择专题或者合作专版");
      }
      $result = $cooperFeatureObj -> where("feature_id ={$feature_id}  and coop_id = {$coop_id} and status = 1") -> select();
      if($result){
        $this -> error("专题已存在");
      }
      $data = array();
      $data['feature_id'] = (int)$feature_id;
      $data['coop_id'] = (int)$coop_id;
      $data['created_at'] = time();
      $data['status'] = 1;
      $affect = $cooperFeatureObj -> add($data);
      if($affect > 0){
      		$this->writelog("添加专题coop_id为".$data['coop_id'].",feature_id为".$data['feature_id'],'pu_coop_feature',$affect,__ACTION__ ,"","add");
             $this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Feature/feature_manage');
             $this -> success("专题添加成功！");
      }else{
             $this -> error("添加失败");
      }
   }
   function deleteFeature(){
        $cooperFeatureIconObj = D("Coop.CoopFeatureIcon");
        $cooperFeatureObj = D("Coop.CoopFeature");
        $feature_id = escape_string($_GET['feature_id']);
        $coop_id = escape_string($_GET['coop_id']);
        $where= " feature_id = ".$feature_id." and coop_id =".$coop_id;
        $dataf['status'] = 0;
        $dataf['show_status'] = 0;
        $datafi['status'] = 0;
        $log1 = $this->logcheck(array('feature_id'=>$feature_id),'pu_coop_feature_icon',$datafi,$cooperFeatureIconObj);

        $affect1 = $cooperFeatureIconObj -> where($where) -> save($datafi);
        $log2 = $this->logcheck(array('feature_id'=>$feature_id),'pu_coop_feature',$dataf,$cooperFeatureObj);
        $affect2 = $cooperFeatureObj -> where($where) -> save($dataf);
        //$this->writelog("删除专题feature_id为：".$feature_id.",coop_id为：".$coop_id);
        $this->writelog("天翼推广-天翼专题管理删除专题feature_id为：$feature_id".$log1,'pu_coop_feature,pu_coop_feature_icon',"feature_id:{$feature_id}",__ACTION__ ,"","del");
        $this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Feature/feature_manage');
        $this -> success("专题删除成功！");
   }
}
