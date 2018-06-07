<?php
/*
    软件评论model
    index格式 : $id
    data_info格式 : sj_soft_comment数据库表中的字段
*/
class GoPu_webmarket_show_pictureModel extends GoPu_model
{
	public $table = 'sj_webmarket_show_picture';
    public $softid = 0;
    public $index_name = 'id';
    public $id = 0;

    function __construct($index = array())
    {
        parent::__construct(__CLASS__, $index);
    }
    function get_picture($chl,$show_status = 1,$status = 1) {

		$file_option = array(
                'where' => array(
                    'nickname' => $chl,
                    'status' => $status,
                    ),
                 'table' => 'sj_webmarket_channel',
                 'field' => 'id',
                );
             $file = $this -> findOne($file_option);
			 $date_time=time();
			$option = array(
				"where" => array(
					"status" => $show_status,
					"chl_id" => $file['id'],
					"start_tm"=>array("exp" ,"<={$date_time}"),
					"end_tm"=>array("exp",">{$date_time}")
					),
				"order"=>"rank asc");
			$result = $this -> findAll($option);
			$pic = array();
			$count=array();
			foreach($result as $key => $info){
				$option = array(
					"where" => array(
						"status" => $status,
						"id" => $info['picid'],
						),
						"table"=>"sj_webmarket_picture",
						'field'=>'picurl,title,link,link_type',
						);
				$pic_info = $this -> findOne($option);
				$pic[$key]['picurl']=$pic_info['picurl'];
				$pic[$key]['link']=$pic_info['link'];
				$pic[$key]['title']=$pic_info['title'];
				$pic[$key]['link_type']=$pic_info['link_type'];
				/* if($pic[$key]['link_type']==2){
					$zh_data_array=explode("/",$pic[$key]['link']);
					if($zh_data_array[0]=="http:"){
						$pic[$key]['link']=$pic[$key]['link'];
					}else{
						$pic[$key]['link']="http://".$pic[$key]['link'];
					}
				}else{
					$yu=explode("://","http://www.anzhi.com");
					$zh_pic_data=explode("://",$pic[$key]['link']);
					if($zh_pic_data[0]==$yu[0]){
						$zh_pic_data_one=explode("/",$zh_pic_data[1]);
						if($zh_pic_data_one[0]==$yu[1]){
							$pic[$key]['link']=$pic[$key]['link'];
						}else{
							$pic[$key]['link']=$yu[0]."://".$yu[1]."/".$zh_pic_data[1];
						}
					}else{
						$zh_pic_data=explode("/",$pic[$key]['link']);
						if($zh_pic_data[0]==$yu[1]){
							$pic[$key]['link']=$yu[0]."://".$pic[$key]['link'];	
						}else{
							$pic[$key]['link']=$yu[0]."://".$yu[1]."/".$pic[$key]['link'];
						}
					}
				
				} */
				$count[$key] = $key+1;
			}
			return array($pic,$count);
    }
}
