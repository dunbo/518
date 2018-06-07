<?php
class getchange{
    public $params;
    public function __construct($param)
    {
        $this->params = $param;
    }

    public function getData()
    {
        $img_host = getImageHost();
        $start = isset($this->params['start_time'])?(int)$this->params['start_time']:0;
        $end = isset($this->params['end_time'])?(int)$this->params['end_time']:time();
        $bwn = $end - $start;
        $d7 = 7*24*60*60;
        if ($bwn > $d7) exit("最多获取7天的数据");
        
        $op = isset($this->params['view'])?$this->params['view']:'up';
        
        $softObj = load_model('sjsoft');
        $catalog = $softObj->getDataList('sj_category', array('index' => 'category_id'));

        $sql = "select a.*,b.iconurl,b.min_firmware from sj_soft a left join sj_soft_file b on a.softid=b.softid where   a.last_refresh>=$start";
        if($end){
            $sql .= " and a.last_refresh<=$end";
        }

        $data = array();
        if($op=='up'){//新上架,如果softid相同，调用方得替换掉原来的softid的内容
            $sql .= " and a.hide=1 and a.status=1";
            $result = $softObj->query($sql);
            while( $row = mysql_fetch_assoc($result) ) {
                $apps[] = $row;
            }
            foreach ($apps as $idx => $app)
            {
                extract($app);
                if($category_id[0]==',')
                {
                    $category_id=substr($category_id,1);
                }

                $tnum=strlen($category_id);
                $tnum--;
                if($category_id[$tnum]==',')
                {
                    $category_id=substr($category_id,0,-1);
                }
                $iconurl = $img_host . $iconurl;
                $temp = array();
                $temp['softid']  = $softid;
                $temp['package'] = $package;
                $temp['name'] = $softname;
                $temp['icon'] = $iconurl;
				$temp['version_code'] = $version_code;
				$temp['version'] = $version;
				$temp['min_firmware'] = $min_firmware;
                $temp['type'] = $catalog[$category_id]['name'];
                $temp['typeid'] = $category_id;
                $temp['score'] = $score/2;
                $temp['desc'] = $intro;

                $data[] = $temp;
            }

        }elseif ($op=='down'){//下架
            $sql .= " and (a.hide=3 or a.hide=0 or a.hide=6 or a.status = 0)";//回收站中的
            $result = $softObj->query($sql);
            while( $row = mysql_fetch_assoc($result) ) {
                $apps1[] = $row;
            }
            foreach ($apps1 as $idx => $app)
            {
                extract($app);
                if($category_id[0]==',')
                {
                    $category_id=substr($category_id,1);
                }

                $tnum=strlen($category_id);
                $tnum--;
                if($category_id[$tnum]==',')
                {
                    $category_id=substr($category_id,0,-1);
                }

                $iconurl = $img_host . $iconurl;
                $temp = array();
                $temp['softid']  = $softid;
                $temp['package'] = $package;
                $temp['name'] = $softname;
                $temp['icon'] = $iconurl;
				$temp['version_code'] = $version_code;
				$temp['min_firmware'] = $min_firmware;
                $temp['type'] = $catalog[$category_id]['name'];
                $temp['typeid'] = $category_id;
                $temp['score'] = $score/2;
                $temp['desc'] = $intro;

                $data[] = $temp;
            }

        }
        $return  = array(
			'DATA' => $data,
			'KEY' => strtoupper($this -> params['action']),
        );

        return json_encode($return);
    }
}