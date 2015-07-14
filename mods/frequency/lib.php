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
 * Library of interface functions and constants for module frequency
 *
 * All the core Moodle functions, neeeded to allow the module to work
 * integrated in Moodle should be placed here.
 *
 * All the frequency specific functions, needed to implement all the module
 * logic, should go to locallib.php. This will help to save some memory when
 * Moodle is performing actions across all modules.
 *
 * @package    mod_frequency
 * @copyright  2015 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Example constant, you probably want to remove this :-)
 */
define('frequency_ULTIMATE_ANSWER', 42);

/* Moodle core API */

/**
 * Returns the information on whether the module supports a feature
 *
 * See {@link plugin_supports()} for more info.
 *
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed true if the feature is supported, null if unknown
 */
function frequency_supports($feature) {

    switch($feature) {
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_SHOW_DESCRIPTION:
            return true;
        case FEATURE_GRADE_HAS_GRADE:
            return true;
        case FEATURE_BACKUP_MOODLE2:
            return true;
        default:
            return null;
    }
}

/**
 * Saves a new instance of the frequency into the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @param stdClass $frequency Submitted data from the form in mod_form.php
 * @param mod_frequency_mod_form $mform The form instance itself (if needed)
 * @return int The id of the newly inserted frequency record
 */
function frequency_add_instance(stdClass $frequency, mod_frequency_mod_form $mform = null) {
    global $DB;

    $frequency->timecreated = time();

    // You may have to add extra stuff in here.

    $frequency->id = $DB->insert_record('frequency', $frequency);

    frequency_grade_item_update($frequency);

    return $frequency->id;
}

/**
 * Updates an instance of the frequency in the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @param stdClass $frequency An object from the form in mod_form.php
 * @param mod_frequency_mod_form $mform The form instance itself (if needed)
 * @return boolean Success/Fail
 */
function frequency_update_instance(stdClass $frequency, mod_frequency_mod_form $mform = null) {
    global $DB;

    $frequency->timemodified = time();
    $frequency->id = $frequency->instance;

    // You may have to add extra stuff in here.

    $result = $DB->update_record('frequency', $frequency);

    return $result;
}

/**
 * Removes an instance of the frequency from the database
 *
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 */
function frequency_delete_instance($id) {
    global $DB;

    if (! $frequency = $DB->get_record('frequency', array('id' => $id))) {
        return false;
    }

    // Delete any dependent records here.

    $DB->delete_records('frequency', array('id' => $frequency->id));

    //frequency_grade_item_delete($frequency);

    return true;
}

/**
 * Returns a small object with summary information about what a
 * user has done with a given particular instance of this module
 * Used for user activity reports.
 *
 * $return->time = the time they did it
 * $return->info = a short text description
 *
 * @param stdClass $course The course record
 * @param stdClass $user The user record
 * @param cm_info|stdClass $mod The course module info object or record
 * @param stdClass $frequency The frequency instance record
 * @return stdClass|null
 */

/*-----------------------------Мои функции-----------------------------*/


//Обновляет данные в таблице dispatch frequency
function update_frequency_in_dispatch_frequency($userid, $new_frequency){
    global $DB;

    //Получаем данные о текущей частоте рассылки
    $cur_frequency = $DB->get_record('dispatch_frequency', array('user_id'=>$userid));
    //Получаем данные о пользователе
    $cur_user = $DB->get_record('user', array('id' => $userid));


    //Формируем новый объект и заполняем его поля
    $o_newobject = new StdClass();
    $o_newobject->id = $cur_frequency->id;
    $o_newobject->user_id = $cur_user->id;
    $o_newobject->user_fullname = $cur_user->firstname." ".$cur_user->lastname;
    $o_newobject->frequency = $new_frequency;
    $o_newobject->is_active = $cur_frequency->is_active;
    $o_newobject->last_dispatch_date = $cur_frequency->last_dispatch_date;
    $o_newobject->last_dispatch_matherial_id = $cur_frequency->last_dispatch_matherial_id;
    $o_newobject->last_dispatch_matherial_name = $cur_frequency->last_dispatch_matherial_name;
    $o_newobject->next_dispatch_date = strtotime('+'.$new_frequency.' days', $cur_frequency->last_dispatch_date);
    $o_newobject->next_dispatch_matherial_id = $cur_frequency->next_dispatch_matherial_id;
    $o_newobject->next_dispatch_matherial_name = $cur_frequency->next_dispatch_matherial_name;

    //Добавляем объект в базу
    $DB->update_record('dispatch_frequency', $o_newobject);
    return true;
}
/*
//Получаем список назначений для очередного курса
//Назначение - это способ записи на курс, вручную, самостоятельно и т.д. со своим id

function get_enrollments_for_course($course_id){
    global $DB;

    $sql_string = "SELECT *
                   FROM `mdl_enrol`
                   WHERE courseid = ?
                   ";
    $params = array($course_id);

    $a_cur_enrollments = $DB->get_records_sql($sql_string, $params);

    return $a_cur_enrollments;
}

//Получает id записи на курс и возвращает массив записей пользователей, учтенных в этом назначении
function get_list_of_users_for_enrollment($enrollmentid){
    global $DB;

    $sql_string = "
        SELECT *
        FROM `mdl_user_enrolments`
        WHERE enrolid = ?
        ";
    $params = array($enrollmentid);

    $cur_users = $DB->get_records_sql($sql_string, $params);

    return $cur_users;
}


function addNewRowInfrequency($o_user, $db_link){
    $user_record = new StdClass();
    $user_record->user_id = $o_user->id;
    $user_record->user_fullname = $o_user->firstname." ".$o_user->lastname;
    $user_record->frequency = 7;
    $user_record->is_active = 1;
    $user_record->last_dispatch_date=strtotime('2001-01-01');
    $user_record->last_dispatch_matherial_id=0;
    $user_record->last_dispatch_matherial_name=0;
    $user_record->next_dispatch_date = strtotime('today');
    $user_record->next_dispatch_matherial_id = 0;
    $user_record->next_dispatch_matherial_name = 0;
    $db_link->insert_record('dispatch_frequency', $user_record);
}
*/