<?php
define('AJAX_SCRIPT', true);
require_once(dirname(__FILE__) . '/../../config.php');

// This should be accessed by only valid logged in user.
if (!isloggedin() or isguestuser()) {
    die('Invalid access.');
}


foreach($_POST as $key => $value) {
    $_POST[$key] = htmlspecialchars($value);
}

$current_record = $DB->get_record('dispatch_frequency',array('user_id'=>$USER->id));
$new_record = new StdClass();
$new_value = $_POST['new_value'];
switch($new_value)
{
    case 0:
        $new_record->id = $current_record->id;
        $new_record->user_id = $current_record->user_id;
        $new_record->user_fullname = $current_record->user_fullname;
        $new_record->frequency = $current_record->frequency;
        $new_record->is_active = 0;
        $new_record->last_dispatch_date = $current_record->last_dispatch_date;
        $new_record->last_dispatch_matherial_id = $current_record->last_dispatch_matherial_id;
        $new_record->last_dispatch_matherial_name = $current_record->last_dispatch_matherial_name;
        $new_record->next_dispatch_date = $current_record->next_dispatch_date;
        $new_record->next_dispatch_matherial_id = $current_record->next_dispatch_matherial_id;
        $new_record->next_dispatch_matherial_name = $current_record->next_dispatch_matherial_name;
    break;

    case -1:
        $new_record->id = $current_record->id;
        $new_record->user_id = $current_record->user_id;
        $new_record->user_fullname = $current_record->user_fullname;
        $new_record->frequency = $current_record->frequency;
        $new_record->is_active = 1;
        $new_record->last_dispatch_date = $current_record->last_dispatch_date;
        $new_record->last_dispatch_matherial_id = $current_record->last_dispatch_matherial_id;
        $new_record->last_dispatch_matherial_name = $current_record->last_dispatch_matherial_name;
        $new_record->next_dispatch_date = $current_record->next_dispatch_date;
        $new_record->next_dispatch_matherial_id = $current_record->next_dispatch_matherial_id;
        $new_record->next_dispatch_matherial_name = $current_record->next_dispatch_matherial_name;
    break;

    default:
        $new_record->id = $current_record->id;
        $new_record->user_id = $current_record->user_id;
        $new_record->user_fullname = $current_record->user_fullname;
        $new_record->frequency = $new_value;
        $new_record->is_active = 1;
        $new_record->last_dispatch_date = $current_record->last_dispatch_date;
        $new_record->last_dispatch_matherial_id = $current_record->last_dispatch_matherial_id;
        $new_record->last_dispatch_matherial_name = $current_record->last_dispatch_matherial_name;
        if ($current_record->last_dispatch_date==strtotime('2001-01-01')){
            $new_record->next_dispatch_date = strtotime('+'.$new_value.' days');
        }else{
            $new_record->next_dispatch_date = strtotime('+'.$new_value.' days', $current_record->last_dispatch_date);
        }

        $new_record->next_dispatch_matherial_id = $current_record->next_dispatch_matherial_id;
        $new_record->next_dispatch_matherial_name = $current_record->next_dispatch_matherial_name;
    break;

}

$DB->update_record('dispatch_frequency', $new_record);

// Start capturing output in case of broken plugins.
ajax_capture_output();

ajax_check_captured_output();
echo $new_value;


?>