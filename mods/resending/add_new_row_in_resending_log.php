<?php
/**
 * Created by PhpStorm.
 * User: yak
 * Date: 22.07.15
 * Time: 11:37
 */
define('AJAX_SCRIPT', true);

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
require_once((dirname(dirname(__FILE__))).'/dispatch/lib.php');

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


//-----------------------------Тело шаблона----------------------
try{
    $dispatch_id = $input_data;

    //Получаем из БД данных об отправке
    $sql_string = "
            SELECT *
            FROM `mdl_dispatch_log`
            WHERE id = ?

        ";

    $params = array((int)$dispatch_id);

    $section_data = $DB->get_record_sql($sql_string, $params);

    //Формируем новый объект и заполняем его поля
    $o_new_object = new StdClass();
    $o_new_object -> user_id = $section_data->user_id;
    $o_new_object->resending_date = 0;
    $o_new_object->section_id = $section_data->dispatch_session_id;
    $o_new_object->course_id = $section_data->dispatch_course_id;
    //Добавляем его в БД
    $DB->insert_record('resending_log', $o_new_object);


}catch(Ecxeption $e){
    echo $e;
}


// Start capturing output in case of broken plugins.
ajax_capture_output();

ajax_check_captured_output();
echo "updated";

?>