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



/*-----------------------------Мои функции для модуля Frequency-----------------------------*/


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



//Возвращает список пользователей, записанных на платные курсы с массивом их курсов
function Get_users_array_for_paid_courses($a_paid_courses){

//Определяем класс для хранения пользовательских данных
    class UserForDispatch{
        //Свойства класса

        //id пользователя
        public $userid;
        //Массив id курсов, на которые записан пользователь
        public $users_courses = array();
        //id текущего курса
        public $current_course;
        //дата следующей отправки
        public $next_dispatch_date;
        //название следующей лекции
        public $next_lesson_name;
        //id следущего раздела курса
        public $next_section_id;
        //номер номер следующего раздела курса
        public $next_section_number;
        //является ли отправка внеплановой
        public $is_additional;
        //частота рассылки материала
        public $dispatch_frequency;
    }


//Просматриваем таблицу назначений курсов и формируем список пользователей с массивом курсов, на которые они записаны

//define array for enrollments for each course
//Определяем массив для хранения назначений
    $a_enrollment = array();

//search in DB
//Перебираем массив платных курсов
    foreach($a_paid_courses as $key=>$value){
        //Получаем список назначений для очередного курса
        //Назначение - это способ записи на курс, вручную, самостоятельно и т.д. со своим id
        $a_cur_enrollments = get_enrollments_for_course($value);
        //Добавляем список назначений для очередного курса в общий список
        $a_enrollment = array_merge($a_enrollment, $a_cur_enrollments);
    }

//find users for enrollments
//Находим пользователей, зарегистрированных на курс каждым из возможных способов
//define array
//Определяем массив для хранения записей для пользователей и их курсов
    $a_users_and_their_courses = array();

//search in DB
//Перебираем список назначений
    foreach($a_enrollment as $key => $value){
        //echo($key ." : ".$value->id ."<br>");
        //Получаем список пользователей, зарегистрированных на курс по очередному назначению
        $a_cur_users = get_list_of_users_for_enrollment($value->id);

        //Объединяем полученный массив пользователей для текущего назначения и общий массив без дублирования пользователей
        //т.е. каждому пользователю будет соответствовать массив курсов, на которые он записан
        foreach($a_cur_users as $k => $v){
            //Определяем флаг
            $is_dubl = false;
            //Перебираем всех пользователей в массиве
            foreach($a_users_and_their_courses as $k2 => $v2){
                //Если очередной пользователь уже есть в массиве
                if($v->userid == $v2->userid){
                    //Меняем флаг на "повтор"
                    $is_dubl = true;
                    //Добавляем id курса в массив курсов для пользователя
                    array_push($v2->users_courses, $value->courseid);
                    //и прекращаем цикл
                    break;
                }
            }
            //Если такого пользователя в массиве не было
            if($is_dubl==false){
                //Создаем нового пользователя
                $cur_user = new UserForDispatch();
                //Заполняем его свойство id
                $cur_user->userid = $v->userid;
                //Обозначаем, что этот пользователь идет в общем списке, а не дополнительном
                $cur_user->is_additional = false;
                //Добавляем курс в массив курсов пользователя
                array_push($cur_user->users_courses, $value->courseid);
                //Добавляем пользователя в общий массив
                array_push($a_users_and_their_courses, $cur_user);
            }
        }

    }

    return $a_users_and_their_courses;
}

//Функция, возвращающая полное имя пользователя по его ID
function Get_user_fullname_from_id($user_id){
    global $DB;
    //Находим данные о пользователе
    $user_data = $DB->get_record('user',array('id'=>$user_id));
    //Создадим его полное имя
    $s_fullname = $user_data->lastname." ".$user_data->firstname;
    //Вернём его полное имя
    return $s_fullname;

}


//Функция, которая возвращает название лекции по её ID и ID курса
function Get_section_name_from_id($section_id,$course_id){
    global $DB;

    $sql_string = "
        SELECT *
        FROM `mdl_course_sections`
        WHERE course = ?
        AND section = ?
    ";

    $params = array((int)$course_id, (int)$section_id);

    $section_data = $DB->get_record_sql($sql_string, $params);

    return $section_data->name;
}


//Функция, которая возвращает название курса по ID
function Get_course_shortname_from_id($course_id){
    global $DB;

    $sql_string = "
        SELECT *
        FROM `mdl_course`
        WHERE id = ?
        ";

    $params = array((int)$course_id);

    $course_data = $DB->get_record_sql($sql_string, $params);

    return $course_data->shortname;
}

//Обновление даты в списке повторной отправки, а значить простановка отметки об отправке
function update_date_in_resending($userid, $sectionid){
    global $DB;
    try{
        $sql_string = "
            SELECT *
            FROM `mdl_resending_log`
            WHERE user_id = ?
            AND section_id = ?
            AND resending_date = 0
            ";

        $params = array((int)$userid, (int)$sectionid);

        $resending_data = $DB->get_record_sql($sql_string, $params);

        $o_newobject = new StdClass();
        $o_newobject->id = $resending_data->id;
        $o_newobject->user_id = $resending_data->user_id;
        $o_newobject->resending_date = strtotime('today');
        $o_newobject->section_id = $resending_data->section_id;
        $o_newobject->course_id = $resending_data->course_id;

        $DB->update_record('resending_log', $o_newobject);

        return true;
    }catch(Exception $e){
        echo $e;
    }
}

?>