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
define('resending_ULTIMATE_ANSWER', 42);

/* Moodle core API */

/**
 * Returns the information on whether the module supports a feature
 *
 * See {@link plugin_supports()} for more info.
 *
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed true if the feature is supported, null if unknown
 */
function resending_supports($feature) {

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
function resending_add_instance(stdClass $resending, mod_resending_mod_form $mform = null) {
   global $DB;

    $resending->timemodified = time();

    return $DB->insert_record("resending", $resending);
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
function resending_update_instance(stdClass $resending, mod_resending_mod_form $mform = null) {
    global $DB;

    $resending->timemodified = time();
    $resending->id = $resending->instance;

    // You may have to add extra stuff in here.

    $result = $DB->update_record('resending', $resending);

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
function resending_delete_instance($id) {
    global $DB;

    if (! $resending = $DB->get_record('resending', array('id' => $id))) {
        return false;
    }

    // Delete any dependent records here.

    $DB->delete_records('resending', array('id' => $resending->id));

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


