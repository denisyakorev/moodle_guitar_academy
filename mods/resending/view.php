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
 * Prints a particular instance of resending
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod_resending
 * @copyright  2015 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Replace resending with the name of your module and remove this line.

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');

$id = optional_param('id', 0, PARAM_INT); // Course_module ID, or
$n  = optional_param('n', 0, PARAM_INT);  // ... resending instance ID - it should be named as the first character of the module.

if ($id) {
    $cm         = get_coursemodule_from_id('resending', $id, 0, false, MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $resending  = $DB->get_record('resending', array('id' => $cm->instance), '*', MUST_EXIST);
} else if ($n) {
    $resending  = $DB->get_record('resending', array('id' => $n), '*', MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $resending->course), '*', MUST_EXIST);
    $cm         = get_coursemodule_from_instance('resending', $resending->id, $course->id, false, MUST_EXIST);
} else {
    error('You must specify a course_module ID or an instance ID');
}

require_login($course, true, $cm);

$PAGE->requires->js('/lib/jquery/jquery-1.11.1.min.js');
$PAGE->requires->js('/lib/jquery/ui-1.11.1/jquery-ui.js');
$PAGE->requires->js('/mod/resending/lib/script.js');
//$PAGE->requires->js('/lib/json2/json2.js');
$PAGE->requires->css('/lib/jquery/ui-1.11.1/jquery-ui.css');

/*---------------------Тело модуля-------------------------
/*--------------Создание выпадающего списка - Основная часть модуля---------------*/

//-------------------------------------------------
//-------------------------------------------------
//-------------Модель------------------------------
//-------------------------------------------------
//-------------------------------------------------


//Массив, в котором хранятся id платных курсов
$a_paid_courses = array(2, 3);
//Переменная-флаг, отсылались ли пользователю лекции
$is_send_lectures = false;

//Получаем список пользователей, которые записаны на платные курсы (функция определена в модуле frequency/lib)
//Каждый пользователь представлен в формате класса UserForDispatch -определён там же
$a_users_and_their_courses = Get_users_array_for_paid_courses($a_paid_courses);

//Массив объектов для хранения пользователей для конечного вывода. У массива 2 параметра - id пользователя и его ФИО
$a_users_for_list = [];

//Цикл по каждому пользователю
foreach($a_users_and_their_courses as $key=>$value){
    //Получаем полное имя пользователя
    $s_fullname = Get_user_fullname_from_id($value->userid);
    //Добавляем пользователя в массив
    $a_users_for_list[$value->userid] = $s_fullname;
}
//Сортируем массив по полному имени пользователей
asort($a_users_for_list);

//-------------------------------------------------
//-------------------------------------------------
//-------------Представление-----------------------
//-------------------------------------------------
//-------------------------------------------------
$my_content = '';
$my_content .= '<div id="wrap">';
$my_content .= '<div class="users_head">';
$my_content .= '<h1>Список учеников</h1>';
$my_content .= '<div id="form"></div>';
$my_content .= '<form class="filterform" action="#"><input id="filterinput" type="text"></form>';
$my_content .= '<div class="clear"></div>';
$my_content .= '</div>';
$my_content .= '<ul id="list">';
foreach($a_users_for_list as $key=>$value){
    $my_content .= '<li><a href="'.$CFG->wwwroot.'/mod/resending/create_table.php?userid='.$key.'&id='.$id.'"><div class="user_unit" id="'.$key.'">'.$value.'</div></a></li>';
}
$my_content .= '</ul></div>';



// Print the page header.

$PAGE->set_url('/mod/resending/view.php', array('id' => $cm->id));
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
//$PAGE->requires->js('/mod/frequency/lib/script.js');

echo $OUTPUT->box(format_text($my_content, FORMAT_HTML, array('noclean'=> true), null), 'generalbox mod_introbox', 'resendingtable');

// Finish the page.
echo $OUTPUT->footer();


//-------------------------------------------------
//-------------------------------------------------
//-------------Контроллер--------------------------
//-------------------------------------------------
//-------------------------------------------------