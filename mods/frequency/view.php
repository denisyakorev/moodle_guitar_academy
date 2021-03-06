<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Prints a particular instance of frequency
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod_frequency
 * @copyright  2015 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Replace frequency with the name of your module and remove this line.

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');

$id = optional_param('id', 0, PARAM_INT); // Course_module ID, or
$n  = optional_param('n', 0, PARAM_INT);  // ... frequency instance ID - it should be named as the first character of the module.

if ($id) {
    $cm         = get_coursemodule_from_id('frequency', $id, 0, false, MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $frequency  = $DB->get_record('frequency', array('id' => $cm->instance), '*', MUST_EXIST);
} else if ($n) {
    $frequency  = $DB->get_record('frequency', array('id' => $n), '*', MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $frequency->course), '*', MUST_EXIST);
    $cm         = get_coursemodule_from_instance('frequency', $frequency->id, $course->id, false, MUST_EXIST);
} else {
    error('You must specify a course_module ID or an instance ID');
}

require_login($course, true, $cm);

$PAGE->requires->js('/lib/jquery/jquery-1.11.1.min.js');
$PAGE->requires->js('/lib/jquery/ui-1.11.1/jquery-ui.js');
//$PAGE->requires->js('/lib/json2/json2.js');
$PAGE->requires->css('/lib/jquery/ui-1.11.1/jquery-ui.css');

/*---------------------Тело модуля-------------------------
/*--------------Making middle part of page-----------------------------*/
/*--------------Создание таблицы - Основная часть модуля---------------*/


//Массив, в котором хранятся id платных курсов
$a_paid_courses = array(2, 3);

$my_content = "";
// Добавляем стили
$my_content .= '<link href="'.$CFG->wwwroot.'/mod/dispatch/lib/style.css" rel="stylesheet" />';
//Создаем таблицу вместе с её шапкой
$my_content .= '<table class="frequency_table">';
$my_content .= '<tr>';
$my_content .= '<th>'.get_string('fullname','frequency').'</th>';
$my_content .= '<th>'.get_string('frequency','frequency').'</th>';
$my_content .= '<th>'.get_string('editButton','frequency').'</th>';
$my_content .= '</tr>';


/*-------------Additional functions-------------------------------------*/
/*------------Дополнительные функции------------------------------------*/


/*--------------Making list persons for dispatch------------------------*/
/*--------------Создание списка учеников с указанием частоты рассылки----------*/


//Находим сегодняшнюю дату
$current_date = strtotime('today');

//define class for users data
//Определяем класс для хранения пользовательских данных

class UsersValue{
    public $userid;
    public $frequency;
}



$a_users_and_their_courses = Get_users_array_for_paid_courses($a_paid_courses);


//search this user in dispatch frequency table
//Просматриваем записи о выбранных пользователях в таблице рассылки
$a_result_users_dispatch = array();
//Перебираем всех выбранных на прошлом этапе пользователей
foreach($a_users_and_their_courses as $key=>$value){
    //echo($value->userid ." : ".implode($value->users_courses)."<br>");

    $sql_string = "
        SELECT *
        FROM `mdl_dispatch_frequency`
        WHERE user_id = ?
    ";

    $params = array((int)$value->userid);
    //Для каждого из пользователей находим в таблице частоты рассылки значение частоты
    $user_frequency_data = $DB->get_records_sql($sql_string, $params);
    //Создаем переменную, в которой будет хранится значение частоты, по умолчанию обнулим эту переменную
    $next_dispatch_data = 0;
    //Переберем все полученные из БД записи
    foreach($user_frequency_data as $k=>$v){
        //И изменим переменную частоты
        $next_dispatch_data = $v->next_dispatch_date;
        $value->dispatch_frequency = $v->frequency;

    }
    //if next dispatch date doesn't exist - create new row in dispatch frequency table and add this user to result array/ Next dispatch date define as today
    //Если переменная не изменилась, значит записи о частоте рассылки для пользователя не существует
    if($next_dispatch_data==0){
        //Значит, нам следует получить данные об этом пользователе
        $user_data = $DB->get_record('user',array('id'=>$value->userid));
        //И создать новую запись в таблице частоты рассылки
        addNewRowInfrequency($user_data, $DB);
        //Назначим датой следующей отправки текущую дату
        $value->next_dispatch_date = $current_date;
        //Частота рассылки - по умолчанию 7
        $value->dispatch_frequency = 7;

        //И добавим пользователя в конечный массив
        $newKey = $user_data->lastname." ".$user_data->firstname;
        $newValue = new UsersValue();
        $newValue ->userid = $user_data->id;
        $newValue->frequency = $value->dispatch_frequency;
        $a_result_users_dispatch[$newKey]=$newValue;
        //array_push($a_result_users_dispatch, $value);
        //Если же переменная изменилась, значит запись о пользователе в таблице рассылки уже есть
        //Если при этом дата меньше, либо равна сегодняшнему числу. Берем на самом деле завтрашний день - чтобы убрать проблемы с разницей в часах отправки
    }else{
        //if date of next dispatch earlier than today - add this user in result array
        //Находим данные о пользователе
        $user_data = $DB->get_record('user',array('id'=>$value->userid));
        //И добавим пользователя в конечный массив
        $newKey = $user_data->lastname." ".$user_data->firstname;
        $newValue = new UsersValue();
        $newValue ->userid = $user_data->id;
        $newValue->frequency = $value->dispatch_frequency;
        $a_result_users_dispatch[$newKey]=$newValue;
    }

}
//Сортируем массив по алфавиту
ksort($a_result_users_dispatch);

foreach($a_result_users_dispatch as $key=>$value){
    $my_content .= '<tr>';
    $my_content .= '<td>'.$key.'</td>';
    $my_content .= '<td><input type="number" disabled="disabled" id="'.$value->userid.'" value="'.$value->frequency.'"></td>';
    $my_content .= '<td><input type="button" class="editButton" name="'.$value->userid.'" value="'.get_string('editButton','frequency').'"></td>';
    $my_content .= '</tr>';
}
$my_content .= '</table>';
$my_content .= '<input type="hidden" name="wwwroot" id="wwwroot" value="'.$CFG->wwwroot.'"/>';


// Print the page header.

$PAGE->set_url('/mod/frequency/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($frequency->name));
$PAGE->set_heading(format_string($course->fullname));

/*
 * Other things you may want to set - remove if not needed.
 * $PAGE->set_cacheable(false);
 * $PAGE->set_focuscontrol('some-html-id');
 * $PAGE->add_body_class('frequency-'.$somevar);
 */

// Output starts here.
echo $OUTPUT->header();
$PAGE->requires->js('/mod/frequency/lib/script.js');

echo $OUTPUT->box(format_text($my_content, FORMAT_HTML, array('noclean'=> true), null), 'generalbox mod_introbox', 'frequencytable');

// Finish the page.
echo $OUTPUT->footer();
