$(document).ready(function(){
    //Находим поле ввода и изменяем поведение при изменении
    //Массив со всеми значениями

    $('#filterinput').change(function(){
        //Определяем переменную, содержащую значение в поле ввода
        var filter = $(this).val().toUpperCase();
        //Если что-нибудь введено
        if(filter){
            //Создаём переменную под массив
            var matches=[];
            //Ищем все вхождения такого слова в списке
            //Перебираем все блоки с ФИО пользователей
            $('.user_unit').each(function(){
                //Получаем текст в текущем блоки и переводим его в верхний регистр
                var s_text = $(this).text().toUpperCase();
                //Проверяем вхождение слова-фильтр в значение текущего блока
                if(s_text.indexOf(filter) != -1){
                    //Если вхождение есть, добавляем элемент в массив
                    matches.push($(this));
                }else{
                    //Если вхождения нет, скрываем элемент
                    $(this).parent().parent().slideUp();
                }
            });
            //Перебираем получившийся массив и открываем всё, что в нём есть
            for (var i=0; i<matches.length; i++){
                matches[i].parent().parent().slideDown();

            }
        }else{
            //Если поле поиска очищено - открываем весь список
            $('.user_unit').each(function(){
                $(this).parent().parent().slideDown();
            });
        }
    });

    //Вызываем событие изменения при нажатии кнопки в поле ввода
    $('#filterinput').keyup(function(){
        $('#filterinput').change();
    })
});