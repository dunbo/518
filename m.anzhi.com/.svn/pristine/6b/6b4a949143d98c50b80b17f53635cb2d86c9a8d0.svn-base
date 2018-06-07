<?php
//桌面控件应用数据接口
class launcher{
	public $params;
	public function __construct($param)
	{
		$this->params = $param;
	}
	public function getData(){
		$api_vesion = (int)$this->params['api_version'];
		$channel = $this -> params['channel'];
		$server = $_SERVER['SERVER_NAME'];
		if ($api_vesion<=0) exit("api_version is must and number");
		$ftype = $this->params['ftype'] ? $this->params['ftype'] : 0;
		$page = $this->params['start'] ? $this->params['start'] : 0;
		$pagesize = $this->params['limit'] ? $this->params['limit'] : 5;
		$pagesize = ($pagesize > 30) ? 30 : $pagesize; //对 limit 进行限制  加入 大于30  设置为30
		$softlist = load_model('softlist');
		$softThumb = load_model('softThumb');
		$filter_option = array(
			'abi' => 3
		);
		$filter_logic = pu_load_logic('filter', array('filter_option' => $filter_option) );
		$soft_logic = pu_load_logic('soft', array('filter_logic' => $filter_logic));
		
		$model = new GoModel();
		$img_host = getImageHost();
		$option = array(
			'table' => 'lg_categories',
			'where' => array(
				'status' => 1,
			),
			'cache_time' => 600,
		);
		$c = $model -> findAll($option,'lg');
		$category = array();
		foreach ($c as $v) {
			$category[$v['id']] = $v;
		}


		$option = array(
			'where' => array(
				'a.launcher_status' => 1,
				'b.lg_status' => 1,
				'b.status' => 1,
			),
			'field' => 'b.softid, b.softname, a.package, icon_url, b.category_id',
			'table' => 'lg_launcher AS a',
			'order' => 'a.launcher_rank asc',
			'join' => array(
				'lg_soft AS b' => array(
					'on' => array('a.package', 'b.package')
				)
			),
			'cache_time' => 600,
		);
		$soft_info = $model -> findAll($option,'lg');
		$package = array();
		$soft_arr = array();
		foreach ($soft_info as $v) {
			if (!isset($package[$v['package']]) || $v['softid']>$package[$v['package']]['softid']) {
				$package[$v['package']] = $v;
			} else {
				continue;
			}
			$softid = $v['softid'];
			$app = $softlist->getSoftInfos($softid);
			$app = $app[$softid];
			$app['iconurl'] = !empty($v['icon_url']) ? $v['icon_url'] : $app['iconurl_96'];
			$app['category_name'] =  isset($category[$v['category_id']]) ? $category[$v['category_id']]['name'] : $category[1]['name'];
			$pics = $softThumb->getThumbs($softid);
			foreach ($pics as $pic) {
				$app['thumb_arr'][]['url'] = $pic['url'];
			}
			
			$soft_arr[] = $app;
		}
		
		//var_dump($soft_arr);exit;
		if($ftype == 'xml'){
			header("Content-Type:text/xml");//发送MIME信息
			$result_str = $this -> get_data_xml($soft_arr,$channel,$api_vesion,$server);
			echo $result_str;
			exit;
		}elseif($ftype == 'json'){
			$result_str = $this -> get_data_json($soft_arr,$channel,$api_vesion,$server);
			echo json_encode($result_str);
			exit;
		}
	}

	protected function get_data_json($infos,$channel,$api,$server){
		$img_host = getImageHost();
		$data = array();
		foreach($infos as $info){
			if($info['package']){
				$thumb_url = array();
				$thumb = array();
				foreach($info['thumb_arr'] as $key => $val){
					$thumb_url[] = $img_host.$val['url'];
				}
				$thumb = array('thumb_url' => $thumb_url,'thumb_count'=> count($info['thumb_arr']));
				$data[] = array('package' => $info["package"],
					"icon" => $img_host.$info['iconurl'],
					'name' => $info["softname"],
					'softid' => $info["softid"],
					'download_url' => "http://".$server."/interface/index.php?action=download&softid=".$info['softid']."&channel=".$channel."&api_version=".$api,
					'upload_tm' => date('Y-m-d',$info['upload_tm']),
					'developer' => $info['dev_name'],
					'version' => $info['version'],
					'version_code' => $info['version_code'],
					'apksize' => $info['filesize'],
					'desc' => $info['intro'],
					'downloads' => num_format($info['downloaded'],2),
					'score' => $info['score'],
					'thumb' => $thumb,
					'category_name' => $info['category_name'],
				);
			}
		}
		$count = count($data);
		$result  = array(
				'TOTAL' => $count,
				'DATA' => $data,
		);
		return $result;
	}
		
	protected function get_data_xml($infos,$channel,$api,$server){
		$img_host = getImageHost();
		foreach($infos as $info){
			if($info['package']){
				$data[] = $info;
				$thumb_str = '';
				$thumb_url = '';
				$thumb_str .= "<thumb_url>";
				foreach($info['thumb_arr'] as $key => $val){
					$thumb_str .= "<url>";
					$thumb_str .= $img_host.$val['url'];
					$thumb_str .= "</url>";
				}
				$thumb_str .= "</thumb_url>";
				$thumb_str .= "<thumb_count>";
				$thumb_str .= count($info['thumb_arr']);
				$thumb_str .= "</thumb_count>";
				
				$data_str .= "<info>";
				$data_str .= "<package>";
				$data_str .= $info['package'];
				$data_str .= "</package>";
				$data_str .= "<icon>";
				$data_str .= $img_host.$info['iconurl'];
				$data_str .= "</icon>";
				$data_str .= "<name>";
				$data_str .= $info['softname'];
				$data_str .= "</name>";
				$data_str .= "<softid>";
				$data_str .= $info['softid'];
				$data_str .= "</softid>";
				$data_str .= "<download_url>";
				$data_str .= "http://".$server."/interface/index.php?action=download&amp;softid=".$info['softid']."&amp;channel=".$channel."&amp;api_version=".$api;
				$data_str .= "</download_url>";
				$data_str .= "<upload_tm>";
				$data_str .= date('Y-m-d',$info['upload_tm']);
				$data_str .= "</upload_tm>";
				$data_str .= "<developer>";
				$data_str .= $info['dev_name'];
				$data_str .= "</developer>";
				$data_str .= "<version>";
				$data_str .= $info['version'];
				$data_str .= "</version>";
				$data_str .= "<version_code>";
				$data_str .= $info['version_code'];
				$data_str .= "</version_code>";
				$data_str .= "<apksize>";
				$data_str .= $info['filesize'];
				$data_str .= "</apksize>";
				$data_str .= "<desc>";
				$data_str .= $info['intro'];
				$data_str .= "</desc>";
				$data_str .= "<downloads>";
				$data_str .= num_format($info['downloaded'],2);
				$data_str .= "</downloads>";
				$data_str .= "<score>";
				$data_str .= $info['score'];
				$data_str .= "</score>";
				$data_str .= "<thumb>";
				$data_str .= $thumb_str;
				$data_str .= "</thumb>";
				$data_str .= "</info>";
			}
		}
		$count = count($data);
		$result_str = '<?xml version="1.0" encoding="UTF-8"?>';
		$result_str .= "<ALL>";
		$result_str .= "<TOTAL>";
		$result_str .= $count;
		$result_str .= "</TOTAL>";
		$result_str .= "<DATA>";
		$result_str .= $data_str;
		$result_str .= "</DATA>";
		$result_str .= "<KEY>";
		$result_str .= "LAUNCHER";
		$result_str .= "</KEY>";
		$result_str .= "</ALL>";
		return $result_str;
	}
}
