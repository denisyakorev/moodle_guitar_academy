<?php
require_once('../../config.php');
require_once('lib.php');


//--------------------------Служебная часть модуля------------------------------
//-----Заимствована из шаблона с небольшими уточнениями------------------------

$id = optional_param('id', 0, PARAM_INT);    // Course Module ID
$d  = optional_param('d', 0, PARAM_INT);  // ... newmodule instance ID - it should be named as the first character of the module.

if ($id) {
    $cm         = get_coursemodule_from_id('dispatch', $id, 0, false, MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $dispatch  = $DB->get_record('dispatch', array('id' => $cm->instance), '*', MUST_EXIST);
} else if ($d) {
    $dispatch  = $DB->get_record('dispatch', array('id' => $d), '*', MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $dispatch->course), '*', MUST_EXIST);
    $cm         = get_coursemodule_from_instance('dispatch', $dispatch->id, $course->id, false, MUST_EXIST);
} else {
    error('You must specify a course_module ID or an instance ID');
}

require_login($course, true, $cm);

$PAGE->requires->js('/lib/jquery/jquery-1.11.1.min.js');
$PAGE->requires->js('/lib/jquery/ui-1.11.1/jquery-ui.js');
//$PAGE->requires->js('/lib/json2/json2.js');
$PAGE->requires->css('/lib/jquery/ui-1.11.1/jquery-ui.css');
/*
 * !!!! User can be enrolled in only 1 unfinished course. He should be enrolled only after finish of last course
 */


/*--------------Making middle part of page-----------------------------*/
/*--------------Создание таблицы - Основная часть модуля---------------*/


//Массив, в котором хранятся id платных курсов
$a_paid_courses = array(2, 3);

$my_content = "";
// Добавляем стили
$my_content .= '<link href="'.$CFG->wwwroot.'/mod/dispatch/lib/style.css" rel="stylesheet" />';
//Создаем таблицу вместе с её шапкой
$my_content .= '<table class="dispatch_log">';
$my_content .= '<tr>';
$my_content .= '<th><input type="checkbox" name="check_all" id="check_all_cb"/></th>';
$my_content .= '<th>'.get_string('fullname','dispatch').'</th>';
$my_content .= '<th>'.get_string('dispatch_date','dispatch').'</th>';
$my_content .= '<th>'.get_string('dispatch_matherial','dispatch').'</th>';
$my_content .= '</tr>';


/*-------------Additional functions-------------------------------------*/
/*------------Дополнительные функции------------------------------------*/


/*--------------Making list persons for dispatch------------------------*/
/*--------------Создание списка учеников, которым нужно отправить лекции----------*/


//Находим сегодняшнюю дату
$current_date = strtotime('today');

//define class for users data
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
}



//from enrollment list get users who has enrollment to nonfree courses
//in the end of this action we will have list of users. Each item in list will contain list of courses for this user

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
    }
    //if next dispatch date doesn't exist - create new row in dispatch frequency table and add this user to result array/ Next dispatch date define as today
    //Если переменная не изменилась, значит записи о частоте рассылки для пользователя не существует
    if($next_dispatch_data==0){
        //Значит, нам следует получить данные об этом пользователе
        $user_data = $DB->get_record('user',array('id'=>$value->userid));
        //И создать новую запись в таблице частоты рассылки
        addNewRowInDispatchFrequency($user_data, $DB);
        //Назначим датой следующей отправки текущую дату
        $value->next_dispatch_date = $current_date;
        //И добавим пользователя в конечный массив, так как ему уже пора отправлять первую лекцию
        array_push($a_result_users_dispatch, $value);
    //Если же переменная изменилась, значит запись о пользователе в таблице рассылки уже есть
    //Если при этом дата меньше, либо равна сегодняшнему числу. Берем на самом деле завтрашний день - чтобы убрать проблемы с разницей в часах отправки
    }else if(($current_date+86400) >= $next_dispatch_data){
    //if date of next dispatch earlier than today - add this user in result array
        //Добавляем пользователя в массив отправки с указанием даты из таблицы частоты отправки
        $value->next_dispatch_date = $next_dispatch_data;
        array_push($a_result_users_dispatch, $value);
    //Если же дата позже сегодняшнего дня - пропускаем пользователя
    }else{
        //if next dispatch date further than today - continue
        continue;
    }

}

/*--------------------------Для каждого из пользователей ищем материал, который ему необходимо отправить----------*/

//Создаем еще один массив
$a_finish = array();
//Перебираем массив пользователей для отправки
//find out matherial for each user in result array
foreach($a_result_users_dispatch as $key=>$value){
    //find in dispatch courses table completed courses for this user
    //completed courses means that all matherials for user were dispatched
    $sql_string = "
        SELECT course_id
        FROM `mdl_dispatch_completed_course`
        WHERE user_id = ?
    ";
    $params = array($value->userid);
    //Получаем из БД сведения о завершенных им курсах
    $completed_courses = $DB->get_records_sql($sql_string, $params);

    //Находим разницу между пройденными курсами и назначенными курсами, и записываем id не совпадающих курсов
    //в массив незаконченных курсов
    $unfinished_courses = array();
    foreach($value->users_courses as $v){
        $is_completed = false;
        foreach($completed_courses as $val){
            if ($v == $val->course_id){
                $is_completed = true;
            }
        }
        if($is_completed==false){
            array_push($unfinished_courses,$v);
        }
    }
    //Определяем, какой из незаконченных курсов идет раньше других в перечне курсов
    $current_course = null;
    if(count($unfinished_courses)>0){
        $minorder = null;

        foreach($unfinished_courses as $v){
            $cur_course = $DB->get_record('course', array('id'=>$v));
            if($cur_course->sortorder < $minorder || $minorder==null){
                $minorder = $cur_course->sortorder;
                $current_course = $v;
            }
        }
    }else{
        $value->next_lesson_name = "Ошибочка вышла";
        $value->next_section_id = -1;
        $value->next_section_number = -1;
        continue;
    }
    $value->current_course = $current_course;


    //get all dispatched sections of uncompleted course
    //Получаем список всех отправленных лекций для незаконченного курса
    $sql_string = "
        SELECT dispatch_session_id
        FROM `mdl_dispatch_log`
        WHERE user_id = ?
        AND dispatch_course_id = ?
    ";
    $params = array($value->userid, $current_course);
    $dispatch_sections = $DB->get_records_sql($sql_string, $params);
    //get information about next section
    //Находим лекцию, которую надо отправить
    $sql_string = "
        SELECT section
        FROM `mdl_course_sections`
        WHERE course=?
    ";
    $params = array($current_course);
    $course_sections = $DB->get_records_sql($sql_string, $params);


    //We remove service section which exists in each course
    if(count($dispatch_sections) != count($course_sections)-1){
        $sql_string = "
            SELECT *
            FROM `mdl_course_sections`
            WHERE course=?
            AND section = ?
        ";

        if(count($dispatch_sections)>0){
            //Make array with only sections numbers
            $section_numbers = array();
            foreach($dispatch_sections as $v){
                array_push($section_numbers, $v->dispatch_session_id);
            }
            $params = array($current_course, max($section_numbers)+1);
        }else{
            $params = array($current_course, 1);
        }
        $next_section = $DB->get_records_sql($sql_string, $params);


        if($next_section){
            foreach($next_section as $k=>$v){
                $next_section_name = $v->name;
                $next_section_id = $v->id;
                $next_section_number = $v->section;
            }
            $value->next_lesson_name = $next_section_name;
            $value->next_section_id = $next_section_id;
            $value->next_section_number = $next_section_number;
        }else{
            $value->next_lesson_name = "Ошибочка вышла";
            $value->next_section_id = -1;
            $value->next_section_number = -1;
        }

    }

}
//find person in list of special dispatch
    //if he is - add user again in dispatch array with information about special dispatch
//Получаем все записи из списка повторной отправки
$sql_string = "
        SELECT *
        FROM `mdl_resending_log`
        WHERE resending_date = 0
       ";
$params = array();
$resending_arr = $DB->get_records_sql($sql_string, $params);





//make table for page
foreach($a_result_users_dispatch as $key=>$value){

    if($value->next_section_id == -1) continue;

    $cur_user = $DB->get_record("user",array("id"=>$value->userid));

    if($value->is_additional==false){
        $my_content .= '<tr class="normal">';
    }else{
        $my_content .= '<tr class="additional">';
    }
    $my_content .= '<td><input type="checkbox" name="'.$value->userid.'" class="user_checkbox" value="'.$value->userid.'"/></td>';
    $my_content .= '<td>'.$cur_user->firstname.' '.$cur_user->lastname.'</td>';
    $my_content .= '<td>'.date('d.m.Y',$value->next_dispatch_date).'</td>';
    $my_content .= '<td id="'.$value->next_section_id.'">'.$value->next_lesson_name.'</td>';
    $my_content .= '</tr>';

}
//Добавляем строки для повторной отправки
//$my_content .= '<tr colspan=4>'.get_string('resending', 'dispatch').'</tr>';
$my_content .= '<tr class="resending"><td colspan="4">Повторная отправка</td></tr>';
foreach($resending_arr as $key=>$value){
    $my_content .= '<tr class="additional">';
    $my_content .= '<td><input type="checkbox" name="'.$value->user_id.'" class="user_checkbox" value="'.$value->user_id.'"/></td>';
    $my_content .= '<td>'.Get_user_fullname_from_id($value->user_id).'</td>';
    $my_content .= '<td>'.date('d.m.Y',strtotime('today')).'</td>';
    $my_content .= '<td id="'.$value->section_id.'">'.Get_section_name_from_id($value->section_id, $value->course_id).'</td>';
    $my_content .= '</tr>';

}

$my_content .= '</table>';
$my_content .= '<input type="hidden" name="ids" id="ids"/>';
$my_content .= '<input type="hidden" name="wwwroot" id="wwwroot" value="'.$CFG->wwwroot.'"/>';
$my_content .= '<input type="button" id="check_all" name="check_all" value="'.get_string("check_all",'dispatch').'"/>';
$my_content .= '<input type="button" id="send_data" name="send_data" value="'.get_string("send_data",'dispatch').'"/>';



// Print the page header.

$PAGE->set_url('/mod/dispatch/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($dispatch->name));
$PAGE->set_heading(format_string($course->fullname));

// Output starts here.
echo $OUTPUT->header();

$PAGE->requires->js('/mod/dispatch/lib/script.js');

echo $OUTPUT->box(format_text($my_content, FORMAT_HTML, array('noclean'=> true), null), 'generalbox mod_introbox', 'dispatchtable');



// Finish the page.
echo $OUTPUT->footer();



?>