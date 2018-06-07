<?php
/*
    回馈信息logic 
*/

define('FEEDBACK_NO_TYPE', -1);
define('FEEDBACK_SYSTEM_ERROR', -2);
define('FEEDBACK_NO_USER', -3);
define('FEEDBACK_NO_VERSION', -4);

class GoFeedbackLogic
{
    function post_feedback($data)
    {
        if (!$data['feedbacktype']) { return FEEDBACK_NO_TYPE; }
        if (!$data['softid']) { return FEEDBACK_SYSTEM_ERROR; }
        if (!$data['userid']) { return FEEDBACK_NO_USER; }
        if (!$data['version_code']) { return FEEDBACK_NO_VERSION; }
       
        $feedback_model = pu_load_model_new_obj('pu_feedback');
        
        foreach ($data as $k => $v){
        	$feedback_model->data_info[$k] = $v;
        }
        
        $feedback_model->data_info['submit_tm'] = time();
        
        return $feedback_model->save_data_info(); 
    }    
}

