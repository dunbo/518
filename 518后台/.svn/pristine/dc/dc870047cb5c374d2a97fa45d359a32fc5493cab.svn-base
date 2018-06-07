<?php
class BbsPostModel extends Model {
     protected $trueTableName = 'lottery_bbs_post';


    /**
     *  封贴后回复名单查询
     *  @final       2013-08-19
     */	
    public function getbbspost($tid)
    {
        return $this->field("`id`,`tid`,`author`,`authorid`,`message`,`dateline`,`useip`")->where("tid=$tid")->select();
    }
}
?>
