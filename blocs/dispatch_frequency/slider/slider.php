<? php

<script src="./libs/jquery-1.11.2.min.js"></script><!--Подключаем библиотеку Jquery-->

<link href="./libs/jquery-ui-1.11.2/jquery-ui.min.css" rel="stylesheet" type="text/css"/><!--Подключаем стили CSS для библиотеки Jquery UI-->
<script src="./libs/jquery-ui-1.11.2/jquery-ui.js"></script><!--Подключаем библиотеку Jquery UI-->

<script type="text/javascript">

$(document).ready(function(){
  $( "#slider" ).slider({
value : 0,//Значение, которое будет выставлено слайдеру при загрузке
  min : -150,//Минимально возможное значение на ползунке
  max : 150,//Максимально возможное значение на ползунке
  step : 1,//Шаг, с которым будет двигаться ползунок
  create: function( event, ui ) {
   val = $( "#slider" ).slider("value");//При создании слайдера, получаем его значение в перемен. val
  $( "#contentSlider" ).html( val );//Заполняем этим значением элемент с id contentSlider
 },
 slide: function( event, ui ) {
  $( "#contentSlider" ).html( ui.value );//При изменении значения ползунка заполняем элемент с id contentSlider
            }
        });
});
</script> 



<span id="contentSlider"></span>
<div id="slider"></div>

?>