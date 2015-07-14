/**
 * Created by yak on 05.05.15.
 */

var address;

$(document).ready(function(){

    //var oPeriodsData = \''.$this->periods.'\';
    var oPeriodsData = $("#oPeriodsData").val();
    console.log(oPeriodsData);
    oPeriodsData = JSON.parse(oPeriodsData);

    var user_start_frequency = $("#user_start_frequency").val();
    var firstValue = get_first_value(user_start_frequency);
    //var is_active = \''.$this->is_active.'\';
    var is_active = $("#is_active").val();
    var curValue = firstValue;
    address = $("#address").val();


    $("#my_slider").slider({
        value:firstValue,
        min: 0,
        max: (oPeriodsData.periodData.length-1),
        step: 1,
        slide: function( event, ui ) {
            $( "#amount" ).val(oPeriodsData.periodData[ui.value].periodName);
            curValue = oPeriodsData.periodData[ui.value].periodQuantity;
            update_value_in_db(curValue);
        }
});

$("#stop_start").click(function(){
    toggle_slider();
    });

$( "#amount" ).val(oPeriodsData.periodData[firstValue].periodName);
if(is_active == "0"){
    hide_slider();
    }
});

function update_value_in_db(sNewValue){
    var iNewValue = parseInt(sNewValue);

    $.post(address, {new_value : iNewValue}, function(data){console.log(data)});

}

function get_first_value(sFrequency){
    var iFrequency=0;
    if(sFrequency==false){
    return 0;
    }else{
    iFrequency = parseInt(sFrequency);
    for (elem in oPeriodsData.periodData){
    if(parseInt(oPeriodsData.periodData[elem].periodQuantity)==iFrequency){
    return elem;
    }
}
return 0;
}
}

function toggle_slider(){
    if ($("#stop_start").attr("value")=="Остановить рассылку"){
    hide_slider();
    }else{
    show_slider();
    }
}

function show_slider(){
    $("#my_slider").show();
    $("#amount").show();
    $("#stop_start").attr("value", "Остановить рассылку");
    $("#slider_label").text("Открывать новую лекцию раз в:");
    update_value_in_db(-1);
    }

function hide_slider(){
    $("#my_slider").hide();
    $("#amount").hide();
    $("#stop_start").attr("value", "Возобновить рассылку");
    $("#slider_label").text("Рассылка материалов приостановлена");
    update_value_in_db(0);
    }
