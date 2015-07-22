<?php
define('AJAX_SCRIPT', true);
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/mod/dispatch/lib.php');

// This should be accessed by only valid logged in user.
if (!isloggedin() or isguestuser()) {
    die('Invalid access.');
}


foreach($_POST as $key => $value) {
    $_POST[$key] = htmlspecialchars($value);
}

//$input_data = json_decode($_POST['dispatch_data']);
try{
    $input_data = required_param('dispatch_data', PARAM_RAW);
    $input_data =  html_entity_decode($input_data);
    $input_data = json_decode($input_data);

}catch(Ecxeption $e){
    echo $e;
}


/*---------------Body-----------------------------*/

foreach ($input_data->ids as $value){
    $userid = $value->userid;
    $sectionid = $value->sectionid;
    $type_of_record = $value->type_of_record;

    //make new row in dispatch log
    try{


        if($type_of_record=="normal"){
            add_to_dispatch_log($userid, $sectionid);
            //find out if this section is last in course
            $next_section = get_next_section_id($sectionid);
            //if it's last section in course - we should make new row in course completion table
            if($next_section== -1){
                add_to_dispatch_completed_course($userid, $sectionid);
            }
            //update data in dispatch_frequency_data
            update_data_in_dispatch_frequency($userid, $sectionid, $next_section);
        }else if($type_of_record=="additional"){
            update_date_in_resending($userid, $sectionid);
        }
    }catch(Ecxeption $e){
        echo $e;
    }

}


// Start capturing output in case of broken plugins.
ajax_capture_output();

ajax_check_captured_output();
echo "updated";

?>