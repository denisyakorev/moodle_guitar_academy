/**
 * Created by yak on 22.07.15.
 */

$(document).ready(function(){
    //Действие при нажатии на кнопку "Отправить повторно"
    $('.editButton').click(function(){
        dispatch_id = $(this).attr('id');
        var wwwroot = $("#wwwroot").val();
        var adress = wwwroot+"/mod/resending/add_new_row_in_resending_log.php";


        $.ajax({
            type: "POST",
            url: adress,
            data: {dispatch_data : dispatch_id},
            success: ChangeState(dispatch_id),
            dataType: 'text'
        });
    });

    function ChangeState(button_id){
        var button = $('#'+button_id);
        button.attr('disabled',true);
        button.val("Уже отправляется");
    }
});