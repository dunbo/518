<?php
class OldsoftwarelistAction extends CommonAction {
    private $map;               //条件
 
    function index(){
		$model = new Model();
		$where['status'] = 1;
		$need = $model -> table('cj_partner') -> where($where) -> select();
		foreach($need as $key => $val){
			$need_arr[] = $val['packagename'];
		}
	    $softname = $_POST['softname'];
        $this->map='new_status=0';
	    if(!empty($softname)) {
		    $this->assign("softname", $softname);
			$this->map.=' and new_sname like "%'.preg_replace('/[\s]+/','',$softname).'%"';
		}
		import("@.ORG.Page");
		$model = new model;
        $count = $model-> table('cj_new_sowftware')-> where($this->map)->count();
        $page = new Page($count, 15);
        $Newsoftwarelist = $model -> table('cj_new_sowftware') ->field("`new_sid`,`new_sname`,`new_sver`,`package`,`new_stxt`,`new_sapk`,`new_sfromweb`,`new_sdate`,`new_status`,`sid`")->where($this->map)->order('new_sid desc')->limit($page->firstRow.','.$page->listRows)->select();
		foreach($Newsoftwarelist as $key => $val){
			if(in_array($val['package'],$need_arr)){
				$val['category'] = 1;
			}else{
				$val['category'] = 0;
			}
			$Newsoftwarelist[$key] = $val;
		}
        $page->setConfig('header','篇记录');
        $page->setConfig('first','<<');
        $page->setConfig('last','>>');
        $show =$page->show();
        $this->assign("page", $show);
        $this->assign("Newsoftwarelist" , $Newsoftwarelist);
	 
        $this->display('index');
   }
   

   

   
}

?>