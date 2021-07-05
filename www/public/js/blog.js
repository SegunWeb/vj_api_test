$(document).on('click', '.show-more-blog', function() {

    var category = $.urlParam('category');

    filter(category);

});

$.urlParam = function(name){
    var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);

    if(results !== null) {
        return results[1] || 0;
    }else{
        return false;
    }
};

function filter(category){
    //Включаем прелоадер на время подгрузки
    $(".loaderArea, .loader-box, .loader").show();

    //Узнаем сколько сколько видео уже отобразилось
    var count = $('.articles__box').attr('data-count');

    var data = { 'offset' : count, 'category' : category, '_locale' : $('html').attr('lang') };

    $.ajax({
        url: '/ajax/blog',
        type: "POST",
        data: data,
        dataType: 'JSON',
        cache: false,
        success: function (response) {
            if(response.code === 200){
                //Заменяем контент - новыми отзывами
                $('.articles__box').append(response.list);
                //После замены нужно обновить количество просмотренных видео
                var numberLimit = Number(response.limit);
                var allSeeReview = Number(count) + numberLimit;
                $('.articles__box').attr('data-count', allSeeReview);

                if(response.count <= allSeeReview){
                    $('.show-more-blog').hide();
                }

                $(".loaderArea, .loader-box, .loader").hide();
            }
        }
    });
}