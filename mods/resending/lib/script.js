/**
 * Created by yak on 09.02.15.
 */

var saveButtonValue = "Сохранить";
var editButtonValue;

$(document).ready(function(){
    ActivateEditButtons();

    /*
    var users_data = {
        ids:[]
    };

    $(".user_checkbox").change(function(){
        update_data();
    });

    $("#check_all").click(function(){
        check_all('checked');

    });

    $("#check_all_cb").change(function(){
        check_all($(this).prop('checked'));
    });

    $("#send_data").click(function(){
        var wwwroot = $("#wwwroot").val();
        var adress = wwwroot+"/mod/dispatch/update_users_frequency.php";
        var dataForUpdate = $("#ids").val();
        $.post(adress,{dispatch_data : dataForUpdate}, function(data){
           location.reload();
        });
    });
    */
});

function ActivateEditButtons(){

    $(".editButton").click(function(){

        //Если на кнопке надпись, соответствующая Изменить

        if($(this).attr("value")!= saveButtonValue){
            console.log($(this).attr("name"));
            //Активируем поле
            ActivateEditField($(this).attr("name"));
            //Запоминаем надпись на кнопке
            editButtonValue = $(this).attr("value");
            //После чего меняем её
            ChangeButtonValue($(this), saveButtonValue);
        //Если надпись на кнопке соответствует Сохранить
        }else{
            //Отправляем данные об изменении на сервер
            SendDataToServer($(this).attr("name"));
            //Блокируем поле
            DisactivateEditField($(this).attr("name"));
            //Меняем надпись на кнопке
            ChangeButtonValue($(this), editButtonValue);
            }


    });

}

function update_data(){
    users_data = {
        ids:[]
    };

    $(".user_checkbox:checked").each(function(){
       users_data.ids.push(
           {
               userid:$(this).val(),
               sectionid:$(this).parent().parent().children("td:last-child").attr("id"),
               type_of_record:$(this).parent().parent().attr("class")
            }
           );
    });

    $("#ids").val(JSON.stringify(users_data));
};

function check_all(status){
    var group = '.user_checkbox';
    $(group).prop('checked', status);

    update_data();
}

//Активируем текстовое поле рядом с кнопкой
function  ActivateEditField(name){
    $("#"+name).attr("disabled","");
    console.log($("#"+name).attr("disabled", false));
}

//Деактивируем поле
function DisactivateEditField(name){
    $("#"+name).attr("disabled","disabled");
}

//Меняем надпись на кнопке
function ChangeButtonValue(obj, text){
    obj.attr("value", text);
}

//Функция отправки данных на сервер
function SendDataToServer(userid){
    //Сбор данных
    var userFrequency = $("#"+userid).val();
    var data = {
        userid:userid,
        frequency:userFrequency
    }
    data = JSON.stringify(data);

    //Отправка
    var wwwroot = $("#wwwroot").val();
    var adress = wwwroot+"/mod/frequency/update_users_frequency.php";
    var dataForUpdate = data;
    $.post(adress,{dispatch_data : dataForUpdate}, function(data){
        location.reload();
    });
}