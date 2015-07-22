<?php
/**
 * Created by PhpStorm.
 * User: yak
 * Date: 21.07.15
 * Time: 19:34
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
require_once((dirname(dirname(__FILE__))).'/dispatch/lib.php');

$id = $_GET['id'];

$cm         = get_coursemodule_from_id('resending', $id, 0, false, MUST_EXIST);
$course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$resending  = $DB->get_record('resending', array('id' => $cm->instance), '*', MUST_EXIST);

require_login($course, true);


$PAGE->requires->js('/lib/jquery/jquery-1.11.1.min.js');
$PAGE->requires->js('/mod/resending/lib/create_table.js');

//ID пользователя
$user_id = $_GET['userid'];

//Отправленные ему материалы
$sql_string = "
        SELECT *
        FROM `mdl_dispatch_log`
        WHERE user_id = ?
    ";

$params = array((int)$user_id);

$user_dispatch_data = $DB->get_records_sql($sql_string, $params);

//Материалы в списке повторной отправки

$sql_string = "
        SELECT *
        FROM `mdl_resending_log`
        WHERE user_id = ?
        AND resending_date = 0
    ";

$params = array((int)$user_id);

$user_resending_data = $DB->get_records_sql($sql_string, $params);

//Шапка таблицы

$my_content = "";
// Добавляем стили
$my_content .= '<link href="'.$CFG->wwwroot.'/mod/resending/lib/style.css" rel="stylesheet" />';
//Создаем таблицу вместе с её шапкой
$my_content .= '<table class="resending_table">';
$my_content .= '<tr>';
$my_content .= '<th>'.get_string('fullname', 'resending').'</th>';
$my_content .= '<th>'.get_string('sectionName','resending').'</th>';
$my_content .= '<th>'.get_string('courseName','resending').'</th>';
$my_content .= '<th>'.get_string('editButton','resending').'</th>';
$my_content .= '</tr>';

//Создаем строки для каждой из записи

//Проверяем есть ли такая запись в списке повторной отправки уже
foreach($user_dispatch_data as $key=>$value){
    $is_sending_now = false;
    foreach($user_resending_data as $k=>$v){
        if($value->dispatch_session_id == $v->section_id && $value->dispatch_course_id == $v->course_id){
           $is_sending_now = true;
        }
    }

    if($is_sending_now){
        $my_content .= '<tr>';
        $my_content .= '<td>'.Get_user_fullname_from_id($user_id).'</td>';
        $my_content .= '<td>'.Get_section_name_from_id($value->dispatch_session_id,$value->dispatch_course_id).'</td>';
        $my_content .= '<td>'.Get_course_shortname_from_id($value->dispatch_course_id).'</td>';
        $my_content .= '<td>'.get_string('sendingNow','resending').'</td>';
        $my_content .= '</tr>';
    }else{
        $my_content .= '<tr>';
        $my_content .= '<td>'.Get_user_fullname_from_id($user_id).'</td>';
        $my_content .= '<td>'.Get_section_name_from_id($value->dispatch_session_id,$value->dispatch_course_id).'</td>';
        $my_content .= '<td>'.Get_course_shortname_from_id($value->dispatch_course_id).'</td>';
        $my_content .= '<td><input type="button" class="editButton" id="'.$value->id.'"  value="'.get_string('editButton','resending').'"></td>';
        $my_content .= '</tr>';
    }


}

$my_content .= '</table>';
$my_content .= '<input type="hidden" name="wwwroot" id="wwwroot" value="'.$CFG->wwwroot.'"/>';



// Print the page header.

$PAGE->set_url('/mod/resending/create_table.php', array('userid' => $user_id));
$PAGE->set_title(format_string($resending->name));
$PAGE->set_heading(format_string($course->fullname));

/*
 * Other things you may want to set - remove if not needed.
 * $PAGE->set_cacheable(false);
 * $PAGE->set_focuscontrol('some-html-id');
 * $PAGE->add_body_class('frequency-'.$somevar);
 */

// Output starts here.
echo $OUTPUT->header();


echo $OUTPUT->box(format_text($my_content, FORMAT_HTML, array('noclean'=> true), null), 'generalbox mod_introbox', 'resendingtable');

// Finish the page.
echo $OUTPUT->footer();
