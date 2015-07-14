<?php

defined('MOODLE_INTERNAL') || die;

function dispatch_add_instance($dispatch){
    global $DB;


    $dispatch->timemodified = time();

    return $DB->insert_record("dispatch", $dispatch);
};

function dispatch_update_instance($dispatch){
    global $DB;


    $dispatch->timemodified = time();
    $dispatch->id = $dispatch->instance;

    return $DB->update_record("dispatch", $dispatch);
};

function dispatch_delete_instance($id){
    global $DB;

    if (! $dispatch = $DB->get_record("dispatch", array("id"=>$id))) {
        return false;
    }

    $result = true;

    if (! $DB->delete_records("dispatch", array("id"=>$dispatch->id))) {
        $result = false;
    }

    return $result;
};




/*----------------------My own module functions--------------------------*/

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

//Добавляем записи в таблицу рассылки
function add_to_dispatch_log($userid, $sectionid){
    global $DB;

    //Получаем все необходимые данные из БД
    //Пользователя
    $cur_user = $DB->get_record('user', array('id' => $userid));
    //Данные о секции (главе) курса
    $cur_section = $DB->get_record('course_sections', array('id'=>$sectionid));
    //Данные о частоте отправки
    $frequency = $DB->get_record('dispatch_frequency', array('user_id'=>$userid));


    //Создаем новый объект для хранения данных
    $new_object = new StdClass();
    //Заполняем свойства объекта
    $new_object->user_id = $cur_user->id;
    $new_object->user_fullname = $cur_user->firstname.' '.$cur_user->lastname;
    $new_object->frequency = $frequency->id;
    $new_object->dispatch_date = strtotime('today');
    $new_object->dispatch_session_id = $cur_section->section;
    $new_object->dispatch_course_id = $cur_section->course;
    //Добавляем запись в БД

    $DB->insert_record('dispatch_log', $new_object);
    return true;
}

//Принимаем id раздела и возвращает id следующего раздела, либо -1, если раздел последний
function get_next_section_id($sectionid){
    global $DB;

    //Получаем данные о секции
    $cur_section = $DB->get_record('course_sections', array('id'=>$sectionid));
    //По id курса получаем все его секции
    $sql_string = "
        SELECT *
        FROM `mdl_course_modules`
        WHERE course = ?
     ";

    $params = array($cur_section->course);
    $a_course_sections = $DB->get_records_sql($sql_string, $params);
    //Находим последнюю секцию и сравниваем ее номер с переданным id
    $o_last_section = 0;
    foreach($a_course_sections as $value){
        $cur_module = $DB->get_record('course_sections', array('id'=>$value->section));
        if($cur_module->section > $o_last_section){
            $o_last_section = $cur_module->section;
        }
    }
        //Если не равны, находим id следующего раздела
    if($o_last_section != $cur_section->section){
        $next_section = $DB->get_record('course_sections', array('section'=>($cur_section->section+1), 'course'=>$cur_section->course));
        return $next_section->id;
    }else{
        //Иначе
        //Возвращаем -1
        return -1;
    }
}

//Добавляет запись в каталог о законченных курсах
function add_to_dispatch_completed_course($userid, $lastsectionid){
    global $DB;

    //Получаем данные о секции
    $cur_section = $DB->get_record('course_sections', array('id'=>$lastsectionid));


    //Создаем новый объект
    $o_newobject = new StdClass();
    //Заполняем его свойства
    $o_newobject->user_id = $userid;
    $o_newobject->course_id = $cur_section->course;
    $o_newobject->completed_date = strtotime('today');

    //Добавляем запись
    $DB->insert_record('dispatch_completed_course', $o_newobject);
    return true;
}

//Обновляет данные в таблице dispatch frequency
function update_data_in_dispatch_frequency($userid, $sectionid, $next_section){
    global $DB;

    //Получаем данные о текущей частоте рассылки
    $cur_frequency = $DB->get_record('dispatch_frequency', array('user_id'=>$userid));
    //Получаем данные о пользователе
    $cur_user = $DB->get_record('user', array('id' => $userid));
    //Получаем данные о текущец секции
    $cur_section = $DB->get_record('course_sections', array('id'=>$sectionid));
    //Получаем данные о следующей секции
    $next_section = $DB->get_record('course_sections', array('id'=>$next_section));

    //Формируем новый объект и заполняем его поля
    $o_newobject = new StdClass();
    $o_newobject->id = $cur_frequency->id;
    $o_newobject->user_id = $cur_user->id;
    $o_newobject->user_fullname = $cur_user->firstname." ".$cur_user->lastname;
    $o_newobject->frequency = $cur_frequency->frequency;
    $o_newobject->is_active = $cur_frequency->is_active;
    $o_newobject->last_dispatch_date = strtotime('today');
    $o_newobject->last_dispatch_matherial_id = $sectionid;
    $o_newobject->last_dispatch_matherial_name = $cur_section->name;
    $o_newobject->next_dispatch_date = strtotime('+'.$cur_frequency->frequency.' days');
    $o_newobject->next_dispatch_matherial_id = $next_section->id;
    $o_newobject->next_dispatch_matherial_name = $next_section->name;

    //Добавляем объект в базу
    $DB->update_record('dispatch_frequency', $o_newobject);
    return true;
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


function addNewRowInDispatchFrequency($o_user, $db_link){
    /*
        $user_record = new StdClass();
        $user_record->user_id = $o_user->id;
        $user_record->user_fullname = $o_user->firstname." ".$o_user->lastname;
        $user_record->frequency = 7;
        $user_record->is_active = 1;
        $user_record->last_dispatch_date=strtotime('2001-01-01');
        $user_record->last_dispatch_course_id=0;
        $user_record->last_dispatch_section_id=0;
        $user_record->next_dispatch_date = strtotime('today');
        $user_record->next_dispatch_course_id = 0;
        $user_record->next_dispatch_section_id = 0;
        $db_link->insert_record('dispatch_frequency', $user_record);
    */
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


?>