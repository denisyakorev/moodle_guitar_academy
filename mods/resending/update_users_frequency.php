<?php
define('AJAX_SCRIPT', true);
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/mod/frequency/lib.php');

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
    $frequency = $value->frequency;


    //Меняем данные в таблице частоты рассылки
    try{
        update_frequency_in_dispatch_frequency($userid, $frequency);

    }catch(Ecxeption $e){
        echo $e;
    }

}


// Start capturing output in case of broken plugins.
ajax_capture_output();

ajax_check_captured_output();
echo "updated";

?>