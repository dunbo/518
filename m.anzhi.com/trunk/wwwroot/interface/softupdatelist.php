<?php
class softupdatelist
{
	public $params;
	public function __construct($param)
	{
		$this->params = $param;
	}
	public function getData()
	{
		$goapk_img_host = load_config("goapk_img_host");
		$model = new GoModel();
		$p_log_dir = P_LOG_DIR;
		$lastFetchtime = $this->params['lastFetchTime'];
		$limit = 50;
		$offset = (isset($this -> params['offset'])|| $this -> params['offset']==0) ? $this -> params['offset'] : 1;
		$lasttime = $lastFetchtime;
		$nowtime = time();
		$option = array(
		  "table" => "sj_category",
		  "where" => array("parentid" => array("exp","<>0")),
		  "field" => array("category_id","name"),
 		);
		$cmd = "";
		$clist = $model -> findAll($option);
		$category_list = array();
		foreach($clist as $list){
		  $category_list[$list['category_id']] = $list;
		}
        $cmd = ""; 
		$Dir = $p_log_dir.'/admin_data_log/';
		for($i=0;$i <=($nowtime-$lasttime)/(3600*24);$i++){
			$date = date( "Y-m-d",$lasttime+3600*24*$i);
			$filename .= $date."_modify.log"." "; 
		}
		$cmd = 'cd '.$Dir.' && cat '.$filename.' | grep "\"type\":\"update\""';
		$content = shell_exec($cmd);
		$content = substr($content,1,-1);
		$content_arr = explode("}\n{",$content);
		foreach($content_arr as $line){
			$softinfo = json_decode("{".$line."}",true);
			if($lasttime > $softinfo['date']) continue;
			if($nowtime  < $softinfo['date']) continue;
			$option = array("table" => "sj_soft_thumb","where" => array("softid" => $softinfo['appid']),"field"=> array("url"));
			$tlist = $model -> findAll($option);
			foreach($tlist as $url){
				$thumbs[] = $goapk_img_host.$url;
			}
			$result[$softinfo['appid']] = array(
				"id" => $softinfo['appid'],
				"title" => $softinfo['title'],
				"iconPath" => $goapk_img_host.$softinfo['smallmaplink'],
				"downloadUrl" => "http://wdj.anzhi.com/dl_app.php?s=".$softinfo['appid']."&channel=wandoujia",
				"description" => $softinfo['description'],
				"versionName" => $softinfo['version'],
				"categoryName" => $category_list[substr($softinfo['category'],1,-1)]['name'],
				"packageName" => $softinfo['package'],
				"detailUrl" => "http://www.anzhi.com/soft_".$softinfo['appid'].".html",
				"versionCode" => $softinfo['versioncode'],
				"lastModification" => date("Y-m-d",$softinfo['date']),
				"price" => '0$',
				"screenshotsUrl" => $thumbs,
			);
		}
		$count = count($result);
		$total['totalPages'] =  ceil($count/ $limit);
		$total['currentPage'] = $offset;
		$total['total_entries'] = $count;
		$result = array_values($result);
		$offset = ($offset > 0) ? $offset-1 : $offset;
		$offset = $limit * $offset;
		$result_slice = array_slice($result,$offset,$limit);
		$total['items']['item'] = $result_slice;
		return json_encode($total);
	}
	public function truncateDataTofile(){
		/* for($i=0;$i <=($nowtime-$lasttime)/(3600*24);$i++){
			$date = date("Y-m-d",$lasttime+3600*24*$i);
			$filename = $Dir.$date."_modify.log";
			$handle = fopen($filename,'r');
			if(!$handle) continue;
			if($handle){
				while(!feof($handle)){
					$line = fgets($handle);
					$softinfo = json_decode($line,true);
					if($lasttime > $softinfo['date']) continue;
					if($nowtime  < $softinfo['date']) continue;
					if($softinfo['type'] != "update") continue;
					$option = array("table" => "sj_soft_thumb","where" => array("softid" => $softinfo['appid']),"field"=> array("url"));
					$tlist = $model -> findAll($option);
					foreach($tlist as $url){
					   $thumbs[] = $url;
					}
					$result[$softinfo['appid']] = array(
					 "id" => $softinfo['appid'],
					 "title" => $softinfo['title'],
					 "iconPath" => $goapk_img_host.$softinfo['smallmaplink'],
					 "downloadUrl" => "http://wdj.anzhi.com/dl_app.php?s=".$softinfo['appid']."&channel=wandoujia",
					 "description" => $softinfo['description'],
					 "versionName" => $softinfo['version'],
					 "categoryName" => $category_list[substr($softinfo['category'],1,-1)]['name'],
					 "packageName" => $softinfo['package'],
					 "detailUrl" => "http://www.anzhi.com/soft_".$softinfo['appid'].".html",
					 "versionCode" => $softinfo['versioncode'],
					 "lastModification" => date("Y-m-d",$softinfo['date']),
					 "price" => '0$',
					 "screenshotsUrl" => $thumbs,
					);
				}
			}
		} */
	}
}

?>