<?php
require_once('../../config.php');
require_once("$CFG->libdir/formslib.php");

$id = required_param('id', PARAM_INT);           // Course ID

//Ensure that the course specified is valid
if (!$course = $DB->get_record('course', array('id'=> $id))) {
    print_error('Course ID is incorrect');
}

$PAGE->requires->js('/mod/dispatch/lib/script.js');
$PAGE->requires->css('/mod/dispatch/lib/style.css');

        global $CFG, $DB;

        
        //Add main table
        ECHO '<table class="dispatch_log">';
        ECHO '<tr>';
        ECHO '<th><input type="checkbox" name="check_all" id="check_all_cb"/></th>';
        ECHO '<th>'.get_string('fullname').'</th>';
        ECHO '<th>'.get_string('dispatch_date').'</th>';
        ECHO '<th>'.get_string('dispatch_matherial').'</th>';
        ECHO '</tr>';

//Get data from DB
//Find all persons in dispatch_frequency who has next dispatch date early or equal today's date
        $current_date = strtotime('now');
        $sql = "SELECT *
        FROM {dispatch_frequency}
        WHERE next_dispatch_date <= ?
        AND is_active = 1
        ORDER BY user_fullname";

        $params = array($current_date);

        $users_for_dispatch = $DB->get_records_sql_menu($sql, $params);

        foreach($users_for_dispatch as $value){
            $cur_user = $DB->get_record("user",array("id"=>$value->id));

            ECHO '<tr>';
            ECHO '<td><input type="checkbox" name="'.$value->id.'" class="user_checkbox" value="'.$value->id.'"/></td>';
            ECHO '<td>'.$cur_user->firstname.' '.$cur_user->lastname.'</td>';
            ECHO '<td>'.$value->next_dispatch_section_id.'</td>';
            ECHO '</tr>';
        }

        ECHO '<input type="hidden" name="ids" id="ids"/>';
        ECHO '<input type="button" id="check_all" name="check_all" value="'.get_string("check_all").'"/>';
        ECHO '<input type="button" id="send_data" name="send_data" value="'.get_string("send_data").'"/>';





?>