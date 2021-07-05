//Change form selected
$(document).on('click', 'input[name="category"], input[name="gender"], input[name="lang[]"], input[name="video-option"], .people-list .list__wrap li, .event-list .list__wrap li', function() {
    filter(false);
});

$(document).on('click', '.show-more-video', function(e) {
    e.preventDefault();
    //Узнаем количество страниц прошедших пользователем
    var page = Number($('.all-video').attr('data-page'));
    //Инкремент страницы
    $('.all-video').attr('data-page', Number(page+1) );
    //Запускаем фильтр по этим данным и передаем идентификатор новой страницы
    if($('main.categories').length > 0){
        showMorePaginator(Number(page + 1));
    }else {
        showMorePaginatorWithTags(Number(page + 1));
    }
});

$(document).on('click', '.tag-list a', function(e) {
    e.preventDefault();
    $(".loaderArea, loader-box, .loader").show();
    var newTag = $(this).attr('data-slug');
    var getParam = getAllUrlParams(window.location.href);
    var tags = getParam['tag'] !== undefined ? getParam['tag'] : [] ;
    var url = '';
    if( $(this).hasClass('close-tag') === false) {
        $('.close-tag').show();
        if (tags.indexOf(newTag) === -1) {
            tags.push(newTag);
            $('.all-video a[data-slug="'+newTag+'"]').addClass('active');
        } else {
            var key = tags.indexOf(newTag);
            tags.splice(key, 1);
            $('.all-video a[data-slug="'+newTag+'"]').removeClass('active');
        }
    }else{
        tags = '';
        $('.close-tag').hide();
        $('.tag-list a').removeClass('active');
        url = window.location.pathname;
    }

    if(tags.length > 0) {
        for (var i = 0; i < tags.length; i++) {
            if (i === 0) {
                url = url + '?tag[]=' + tags[i];
            } else {
                url = url + '&tag[]=' + tags[i];
            }
        }
    }else{
        $('.close-tag').hide();
        url = window.location.pathname;
    }
    history.pushState(null, null, url);

    var data = {"lang": $('html').attr('lang'), "category": $('main').attr('data-id'), "tag": tags};

    $.ajax({
        url: '/ajax/category/video',
        type: "POST",
        data: data,
        dataType: 'JSON',
        cache: false,
        success: function (response) {
            if (response.code === 200) {
                $(".loaderArea, loader-box, .loader").hide();
                //Заменяем контент - новыми видео
                $('.all-video .all-video__box').html('');
                $('.all-video .all-video__box').append(response.list);
                //После замены нужно обновить количество просмотренных видео
                var numberLimit = Number(response.limit);
                var allSeeVideo = Number(numberLimit);
                $('.all-video').attr('data-count', allSeeVideo);
                $('.all-video').attr('data-page', 1);

                if (response.count <= allSeeVideo) {
                    $('.show-more-video').hide();
                } else {
                    $('.show-more-video').show();
                }

            }
        }
    });
    return false;
});

/*function filter(page){

    //Забираем все данные фильтра для отправки запроса на получения обновлений
    var language = $('input[name="lang[]"]:checked').map(function() {
        return $(this).val();
    }).get();
    var category = $('input[name="category"]:checked').val();
    var sex = $('input[name="gender"]:checked').val();
    var option = $('input[name="video-option"]:checked').val();
    var event = $('div.event-list .list__title').attr('data-id');
    if(event < 1){ event = 0; }
    var numbers = $('div.people-list .list__title').attr('data-id');
    if(numbers < 1){ numbers = 0; }
    //Узнаем сколько сколько видео уже отобразилось
    var count = $('.all-video').attr('data-count');

    var data = { "lang" : language, "category" : category, "sex" : sex, "event" : event, "option" : option, "number_of_persons" : numbers };

    //Если передан идентификатор страницы - передаем его в адресную строку
    if(page) {
        data['page'] = page;
    }

    //Добавляем элементы фильтра в адресную строку
    history.pushState(null, null, '?'+jQuery.param(data));

    //Добавляем offset перед отправкой на сервер, в адресной строке он не нужен (только если нажата кнопка "показать еще")
    if(page) {
        data['offset'] = count;
    }

    $.ajax({
        url: '/ajax/video',
        type: "POST",
        data: data,
        dataType: 'JSON',
        cache: false,
        success: function (response) {
            if(response.code === 200){
                //Заменяем контент - новыми видео
                $('.all-video').before(response.list).remove();
                //После замены нужно обновить количество просмотренных видео
                var numberLimit = Number(response.limit);
                var allSeeVideo = Number(count) + numberLimit;
                $('.all-video').attr('data-count', allSeeVideo);
                $('.all-video').attr('data-page', page);

                if(response.count <= allSeeVideo){
                    $('.show-more-video').hide();
                }

            }
        }
    });
}*/

function showMorePaginatorWithTags(page) {
//Узнаем сколько сколько видео уже отобразилось
    var count = $('.all-video').attr('data-count');

    var getParam = getAllUrlParams(window.location.href);
    var tags = getParam['tag'] !== undefined ? getParam['tag'] : [] ;

    var data = {"lang": $('html').attr('lang'), "category": $('main').attr('data-id'), "tag": tags};

    //Если передан идентификатор страницы - передаем его в адресную строку
    if(page) {
        data['page'] = page;
        data['offset'] = count;

        $.ajax({
            url: '/ajax/category/video',
            type: "POST",
            data: data,
            dataType: 'JSON',
            cache: false,
            beforeSend: function() {
                $(".loaderArea, loader-box, .loader").show();
            },
            success: function (response) {
                if(response.code === 200){
                    //Заменяем контент - новыми видео
                    $('.all-video__box').append(response.list);
                    //После замены нужно обновить количество просмотренных видео
                    var numberLimit = Number(response.limit);
                    var allSeeVideo = Number(count) + numberLimit;
                    $('.all-video').attr('data-count', allSeeVideo);
                    $('.all-video').attr('data-page', page);

                    if(response.count <= allSeeVideo){
                        $('.show-more-video').hide();
                    } else {
                        $('.show-more-video').show();
                    }

                }
            },
            complete: function () {
                $(".loaderArea, loader-box, .loader").hide();
            }
        });
    }
}

function showMorePaginator(page){
    //Узнаем сколько сколько видео уже отобразилось
    var count = $('.all-video').attr('data-count');

    var data = { "lang" : $('html').attr('lang'), "category" : $('main').attr('data-id') };

    //Если передан идентификатор страницы - передаем его в адресную строку
    if(page) {
        data['page'] = page;
    }

    //Добавляем offset перед отправкой на сервер, в адресной строке он не нужен (только если нажата кнопка "показать еще")
    if(page) {
        data['offset'] = count;
    }

    $.ajax({
        url: '/ajax/category/video',
        type: "POST",
        data: data,
        dataType: 'JSON',
        cache: false,
        success: function (response) {
            if(response.code === 200){
                //Заменяем контент - новыми видео
                $('.all-video__box').append(response.list);
                //После замены нужно обновить количество просмотренных видео
                var numberLimit = Number(response.limit);
                var allSeeVideo = Number(count) + numberLimit;
                $('.all-video').attr('data-count', allSeeVideo);
                $('.all-video').attr('data-page', page);

                if(response.count <= allSeeVideo){
                    $('.show-more-video').hide();
                }

            }
        }
    });
}

$( document ).ready(function() {

    //Достаем значение data-id мероприятия
    var eventID = $('div.event-list .list__title').attr('data-id');
    if(eventID > 0) {
        //Достаем по значению название
        var eventTitle = $('div.event-list ul li[id='+eventID+'] span').html();
        //Запиываем как выбранное
        $('.event-list .list__title').text(eventTitle);
    }

    //Достаем значение data-id количества человек
    var numberOfPersonsID = $('div.people-list .list__title').attr('data-id');
    if(numberOfPersonsID > 0) {
        //Достаем по значению название
        var numberOfPersonsTitle = $('div.people-list ul li[id='+numberOfPersonsID+'] span').html();
        //Запиываем как выбранное
        $('.people-list .list__title').text(numberOfPersonsTitle);
    }
});
function getAllUrlParams(url) {

    // get query string from url (optional) or window
    var queryString = url ? url.split('?')[1] : window.location.search.slice(1);

    // we'll store the parameters here
    var obj = {};

    // if query string exists
    if (queryString) {

        // stuff after # is not part of query string, so get rid of it
        queryString = queryString.split('#')[0];

        // split our query string into its component parts
        var arr = queryString.split('&');

        for (var i = 0; i < arr.length; i++) {
            // separate the keys and the values
            var a = arr[i].split('=');

            // set parameter name and value (use 'true' if empty)
            var paramName = a[0];
            var paramValue = typeof (a[1]) === 'undefined' ? true : a[1];

            // (optional) keep case consistent
            paramName = paramName.toLowerCase();
            if (typeof paramValue === 'string') paramValue = paramValue.toLowerCase();

            // if the paramName ends with square brackets, e.g. colors[] or colors[2]
            if (paramName.match(/\[(\d+)?\]$/)) {

                // create key if it doesn't exist
                var key = paramName.replace(/\[(\d+)?\]/, '');
                if (!obj[key]) obj[key] = [];

                // if it's an indexed array e.g. colors[2]
                if (paramName.match(/\[\d+\]$/)) {
                    // get the index value and add the entry at the appropriate position
                    var index = /\[(\d+)\]/.exec(paramName)[1];
                    obj[key][index] = paramValue;
                } else {
                    // otherwise add the value to the end of the array
                    obj[key].push(paramValue);
                }
            } else {
                // we're dealing with a string
                if (!obj[paramName]) {
                    // if it doesn't exist, create property
                    obj[paramName] = paramValue;
                } else if (obj[paramName] && typeof obj[paramName] === 'string'){
                    // if property does exist and it's a string, convert it to an array
                    obj[paramName] = [obj[paramName]];
                    obj[paramName].push(paramValue);
                } else {
                    // otherwise add the property
                    obj[paramName].push(paramValue);
                }
            }
        }
    }

    return obj;
}