/**
 * Created by yak on 09.02.15.
 */
$(document).ready(function(){
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
        var adress = wwwroot+"/mod/dispatch/update_dispatch_log.php";
        var dataForUpdate = $("#ids").val();
        $.post(adress,{dispatch_data : dataForUpdate}, function(data){
           location.reload();
        });
    });

});

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