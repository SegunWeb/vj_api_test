$(document).on('click', '.show-more-review', function() {
    filter(1);
});

$(document).on('click', '.show-more-review-video', function() {
    filter(2);
});

function filter(type){
    //Включаем прелоадер на время подгрузки
    $(".loaderArea, loader-box, .loader").show();

    //Узнаем сколько сколько видео уже отобразилось
    if(type === 1 ){
        var count = $('.testim__text').attr('data-count');
    }else {
        var count = $('.testim-video').attr('data-count');
    }

    //Добавляем offset перед отправкой на сервер, в адресной строке он не нужен (только если нажата кнопка "показать еще")
    var data = { 'offset' : count, 'type' : type };

    $.ajax({
        url: '/ajax/review',
        type: "POST",
        data: data,
        dataType: 'JSON',
        cache: false,
        success: function (response) {
            if(response.code === 200){
                if(type === 1 ){
                    //Заменяем контент - новыми отзывами
                    $('.container-review').append(response.list);
                    //После замены нужно обновить количество просмотренных видео
                    var numberLimit = Number(response.limit);
                    var allSeeReview = Number(count) + numberLimit;
                    $('.testim__text').attr('data-count', allSeeReview);

                    if(response.count <= allSeeReview){
                        $('.show-more-review').hide();
                    }
                }else{
                    //Заменяем контент - новыми отзывами
                    $('.container-video-review .video-testimonials-wrap').append(response.list);
                    //После замены нужно обновить количество просмотренных видео
                    var numberLimit = Number(response.limit);
                    var allSeeReview = Number(count) + numberLimit;
                    $('.testim-video').attr('data-count', allSeeReview);

                    if(response.count <= allSeeReview){
                        $('.show-more-review-video').hide();
                    }
                }

                $(".loaderArea, loader-box, .loader").hide();
            }
        }
    });
}

$( document ).ready(function() {

    var url = location.toString().split('#');
    if (url[1] === 'video') {
        $('input#testi-v').prop("checked", "checked");
        $('#testim-video').show();
        $('#testim-text').hide();
        $('.testim-textarea').hide();
        $('.testim-info').hide();
        $('.testim-mail').show();
    }

    $(document).on('click', '#testi-v', function() {
        history.pushState(null, null, '#video');
    });
    $(document).on('click', '#testi', function() {
        history.pushState(null, null, '#');
    });

    $(document).on('click', '.add-review', function() {
        //Включаем прелоадер на время подгрузки
        $(".loaderArea, loader-box, .loader").show();

        var textarea = $('textarea[name="review"]').val();

        $.ajax({
            url: '/ajax/add/review',
            type: "POST",
            data: { 'text' : textarea },
            dataType: 'JSON',
            cache: false,
            success: function (response) {
                if(response.code === 200){
                    $('textarea[name="review"]').val('');
                    $('.testim-info').hide();
                    $('.testim-success').show();
                }
                $(".loaderArea, loader-box, .loader").hide();
            }
        });

        return false;
    });
});