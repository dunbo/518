<?php
/*
    软件评论logic
*/
define('COMMENT_NO_LOGIN', -1);
define('COMMENT_NO_CONTENT', -2);
define('COMMENT_SYSTEM_ERROR', -3);

class GoCommentLogic
{
    function get_soft_commnet($softid, $c_start, $c_page, $c_num)
    {
        $comment = array();
        $soft_obj = pu_load_model_obj('pu_soft', $softid);
        if ($comment_id_arr = $soft_obj->get_comment_id_arr()) {
            $comment['count'] = count($comment_id_arr);
            $f_comment_id_arr =  array_slice($comment_id_arr, $c_start, $c_num);
            $o_list = pu_load_model_data('pu_comment', $f_comment_id_arr);
            $list = array();
            foreach ($f_comment_id_arr as $id) {
                $list[$id] = $o_list[$id];
                $list[$id]['imei'] = substr($o_list[$id]['imei'],0,4).'...'.substr($o_list[$id]['imei'],-4);
                $list[$id]['score'] = $o_list[$id]['new_score'];
            }
            $comment['list'] = $list;
            $comment['page']  = pagination_arr($c_page, $comment['count'], $c_num, 10, 'c_page');
        }
        return $comment;
    }
    function get_soft_comment_simple($softid, $c_start, $c_num) {
        $comment = array();
        $soft_obj = pu_load_model_obj('pu_soft', $softid);
        if ($comment_id_arr = $soft_obj->get_comment_id_arr()) {
            $comment['count'] = count($comment_id_arr);
            $f_comment_id_arr =  array_slice($comment_id_arr, $c_start, $c_num);
            $o_list = pu_load_model_data('pu_comment', $f_comment_id_arr);
            $list = array();
            foreach ($f_comment_id_arr as $id) {
                $list[$id] = $o_list[$id];
                $list[$id]['imei'] = substr($o_list[$id]['imei'],0,4).substr($o_list[$id]['imei'],-4);
				$list[$id]['score'] = $o_list[$id]['new_score'];
            }
            $comment['list'] = $list;
        }
        return $comment;
    }

    function post_comment($data)
    {
        if (!$data['content']) { return COMMENT_NO_CONTENT; }
        if (!$data['user_name'] || !$data['userid']) { return COMMENT_NO_CONTENT;  }
        if (!($data['softid'] = $_POST['softid']) ||  !($data['package'] = $_POST['package'])) { $error = COMMENT_SYSTEM_ERROR; }

        $comment_model = pu_load_model_new_obj('pu_comment');
        $comment_model->data_info['score'] = (int)$data['score'];
        $comment_model->data_info['content'] = $data['content'];
        $comment_model->data_info['user_name'] = $data['user_name'];
        $comment_model->data_info['userid'] = $data['userid'];
        $comment_model->data_info['create_time'] = time();
        $comment_model->data_info['package'] = $data['package'];
        $comment_model->data_info['softid'] = $data['softid'];
        $comment_model->data_info['status'] = $data['status'];
        $comment_model->data_info['ipmsg'] = onlineip();
        return $comment_model->save_data_info();
    }
}

