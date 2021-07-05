function initCrop(elem, i){
    if(i === undefined){ i = 0; }
    var width = Number($(elem).closest('.step-box__photo').attr('data-width'));
    var height = Number($(elem).closest('.step-box__photo').attr('data-height'));
    var defaultWidth = 0;
    var viewportWidth=$( window ).width();
    if(width < height) {var orient = 'v';} else {var orient = 'h';}
    if (viewportWidth < 620) defaultWidth = viewportWidth-20;
    else defaultWidth = (orient === 'h')? 600 : 400;

    var ratio = width / height;
    var defaultHeight=defaultWidth / ratio;


    var exportZoom =  (orient === 'h')? (width / defaultWidth) : (height / defaultHeight);
    var normalWidth = defaultWidth;
    var normalHeight = defaultHeight;

    if($(elem).hasClass('phrases')){
        var placeholder = $(elem).closest('.step-box').attr('data-id');
        var checked = $(elem).find('input[name="positions'+placeholder+'['+ (i+1) +']"]:checked').val();
    }else {
        var placeholder = $(elem).closest('.step-box').attr('data-placeholder');
        var checked = $(elem).find('input[name="positions'+placeholder+'['+ placeholder +']"]:checked').val();
    }

    $(elem).cropit({
        allowDragNDrop: false,
        exportZoom: exportZoom,
        smallImage: 'allow',
        width: normalWidth,
        height: normalHeight,
        maxZoom: 32, 
        onFileChange : function (e) {
            $(elem).find('.square-box-loader').show();
            $(elem).find('.cropit-preview-image-container').hide();
        },
        onImageLoading : function (e) {
            $(elem).find('.square-box-loader').show();
            $(elem).find('.cropit-preview-image-container').hide();
        },
        onImageLoaded : function (e) {
            $(elem).find('.square-box-loader').hide();
            $(elem).find('.cropit-preview-image-container').show();
			
			//вешаем обработку pinch пальцами
			 var imageContainer=$(elem).find('.cropit-preview-image-container')[0];
			 var mc = new Hammer.Manager(imageContainer);
			 var pinch = new Hammer.Pinch();
			 mc.add([pinch]);
			 mc.on("pinchin", function(ev) {
				var step = $(elem).cropit('zoom');
				var nextStep = step - 0.005;
				$(elem).cropit('zoom', nextStep);
                $(elem).closest('.editor-zoom').find('.cropit-image-zoom-input').val(nextStep);
			});
			mc.on("pinchout", function(ev) {
				var step = $(elem).cropit('zoom');
				var nextStep = Number(step + 0.005);
				$(elem).cropit('zoom', nextStep);
                $(elem).closest('.editor-zoom').find('.cropit-image-zoom-input').val(nextStep);
			});
        }
    });

    $(elem).closest('.step-box__photo').attr('data-zoom', exportZoom);
}

function reInitCrop(elem){
    var image = $(elem).find('#image').attr('src');
    if(image !== "") {
        $(elem).cropit('imageSrc', image);
    }
}

function loadingPhoto(imageEditor, image){
    var width = $(imageEditor).closest('.step-box').attr('data-width');
    var height = $(imageEditor).closest('.step-box').attr('data-height');
    var placeholder = $(imageEditor).closest('.step-box').attr('data-placeholder');

    var form = new FormData();
	form.append('image', image);
    form.append('width', width);
    form.append('height', height);
    form.append('position', $(imageEditor).find('input.edit:checked').val());
    if($(imageEditor).hasClass('postcard') === true) {
        form.append('face', true);
    }

    $.ajax({
        url: '/upload/image',
        type: "POST",
        data: form,
        dataType: 'JSON',
        cache: false,
        processData: false,
        contentType: false,
        timeout: 300000,
        error: function(){
            alert($('message[type="error-upload"]').attr('text'));
        },
        success: function (response) {
            if(response.url !== "" || response.face === "no") {
                if($(imageEditor).hasClass('postcard') === false) {
                    $(imageEditor).find('#image').attr('src', response.url);
                    $(imageEditor).find('.cropit-preview').show();
                    $(imageEditor).find('.image-editor__box').show();
                    $(imageEditor).closest('.step-box').find('.filter-store-box.step-box__words').show();
                    loadingImageEnableNextButton(imageEditor);
                    if($(imageEditor).closest('.step-box').find('.cropit-image-input').hasClass('load-array')){
                        initCrop(imageEditor, placeholder);
                    }
                    reInitCrop(imageEditor);
                    $(imageEditor).closest('.step-box').find('.row_canvas').css('opacity', 1);
                    $(imageEditor).closest('.step-box').find('.row_canvas').show();
                    $('input.load-photo').prop( "disabled", false );
                }else{
                    destroyLayers();
                    imgUrl = response.url;
                    $(imageEditor).find('.postcard-full').attr('src', response.url);
                    initLayers();
                    loadingImageEnableNextButton(imageEditor);
                    $(imageEditor).find('.image-editor__box').show();
                    $(imageEditor).closest('.step-box').find('.filter-store-box.step-box__words').show();
                    $(imageEditor).find('.square-box-loader').hide();
                    $(".step-box.canvas-face #zoom").prop("disabled", !1);
                    $(".step-box.canvas-face #rotate").prop("disabled", !1);
                    $(imageEditor).closest('.step-box').find('.row_canvas').css('opacity', 1);
                    $(imageEditor).closest('.step-box').find('.row_canvas').show();
                    $('input.load-photo').prop( "disabled", false );
                	$(imageEditor).closest('.steps').find('.btn__next').addClass('act_btn');
                }
            }

             if(response.face !== "" && response.face !== undefined && response.face !== "no"){
                var postcard = $('postcard.active');
                $('.postcard-result .postcard-image').attr('src', response.face);
                $(postcard).attr('data-face', response.face);
                loadingImageEnableNextButton(imageEditor);
                $('.postcard-result .postcard-image-mouth').attr('src', response.mouth).addClass('floating');
                $(postcard).attr('data-mouth', response.mouth);
                $('.postcard-result .postcard-image-full').attr('src', response.faceFull);
                $(postcard).attr('data-full', image);
                $(postcard).attr('data-offset-x', response.mouthX);
                $(postcard).attr('data-offset-y', response.mouthY);
                $('.postcard-result').show();
                $('.btn_redactor-foto-edit').removeClass('d-none');
                $('.row_canvas').hide();
                $('.square-box-loader').hide();
                $(imageEditor).closest('.step-box').find('.image-editor__descr.desc').hide();
                $(imageEditor).closest('.step-box').find('.btn__load.btn-add-foto').hide();
                $(imageEditor).closest('.steps').find('.image-editor__box').hide();
                $(imageEditor).closest('.steps').find('.btn__next').addClass('postcard-auto-end').addClass('postcard-prev').removeClass('postcard-button');
                var postcard = $('postcard.active');
                $(postcard).addClass('auto-face');
                $('input.load-photo').prop( "disabled", false );
                $(imageEditor).closest('.steps').find('.btn__next').addClass('act_btn');
            }

            $(imageEditor).closest('.cropit-image-input').val('');
        }
    });
}

function loadingImageEnableNextButton(_this){
    var photoCount = $(_this).closest('.steps').find('.step-box input.load-photo').length;
    var photoVal = 0;
    var steps = $(_this).closest('.steps');

    //Проверяем это блок просто фото или блок фото+фраза
    if($(steps).hasClass('photo-and-phrases')){
        //Блок фото+фраза, может быть как одно фото так и 10.
        $(steps).find('input.load-photo').each(function (i, elem) {
            var src = $(elem).closest('.step-box__photo.step-box').find('#image').attr('src');
            if (src !== undefined && src !== "") {
                if($(elem).closest('.step-box').find('.form_phrases').val() !== ""){
                    photoVal++;
                }
            }
        });

        if (photoVal === photoCount) {
            $(steps).find('.btn__next').addClass('act_btn');
        } else {
            $(steps).find('.btn__next').removeClass('act_btn');
        }
    } else if($(steps).hasClass('block__addfoto')){
        var photoCount = $(_this).closest('.steps').find('.step-box input.load-photo.postcard.active').length;
        //Блок просто фото - должны быть заполнены все поля
        $(steps).find('input.load-photo.postcard.active').each(function (i, elem) {
            if ($(elem).val() !== "") {
                photoVal++;
            }
        });

        if (photoCount === photoVal) {
            $(steps).find('.btn__next').addClass('act_btn');
        } else {
            $(steps).find('.btn__next').removeClass('act_btn');
        }
    } else {
        //Блок просто фото - должны быть заполнены все поля
        $(steps).find('input.load-photo').each(function (i, elem) {
            var src = $(elem).closest('.step-box__photo.step-box').find('#image').attr('src');
            if (src !== undefined && src !== "") {
                photoVal++;
            }
        });
        if (photoCount === photoVal) {
            $(steps).find('.btn__next').addClass('act_btn');
        } else {
            $(steps).find('.btn__next').removeClass('act_btn');
        }
    }
}

$( document ).ready(function() {

    //Цикл по всем фото если они есть, инициализация КРОПАЛКИ!
    if($('body').find('.image-editor.photo') !== "") {

        $('.image-editor.photo').each(function (i, elem) {
            if($(elem).hasClass('postcard') === false) {
                initCrop(elem, i);
                if($('main').hasClass('editing-videos')){
                    reInitCrop(elem);
                }
            }
        });
    }

    var nextStep = $('main .create-movie').next('.steps');
    if(nextStep !== undefined){
        $(nextStep).addClass('step-first').show();
        $(nextStep).find('.step__btn .btn__prev').hide();
    }

    $(document).on('change', '.load-photo', function() {

        var _this = $(this);
        if(_this.val() !== "") {
            $('input.load-photo').prop("disabled", true);
            $(_this).closest('.step-box').find('.cropit-preview').show();
            $(_this).closest('.step-box').find('.row_canvas').css('opacity', 0);
            $(_this).closest('.step-box').find('.square-box-loader').show();
            $(_this).closest('.step-box').find('.cropit-preview-image-container').hide();
            var imageEditor = $(this).closest('.step-box').find('.image-editor');
            var reader = new FileReader();
            reader.onloadend = function () {
                loadingPhoto(imageEditor, reader.result);
            };
            reader.readAsDataURL($(_this).prop("files")[0]);
        }
    });

    $(document).on('click', '.btn_redactor-foto-edit', function() {
        $('.postcard-result').hide();
        $('.row_canvas').show();
        var imageEditor = $(this).closest('.step-box').find('.image-editor');
        $(imageEditor).closest('.step-box').find('.image-editor__descr.desc').show();
        $(imageEditor).closest('.step-box').find('.btn__load.btn-add-foto').show();
        $('.btn_redactor-foto-edit').addClass('d-none');
        destroyLayers();
        imgUrl = $('.postcard-result .postcard-image-full').attr('src');
        $(imageEditor).find('.postcard-full').attr('src', imgUrl);
        initLayers();
        loadingImageEnableNextButton(imageEditor);
        $(imageEditor).find('.image-editor__box').show();
        $(imageEditor).closest('.step-box').find('.filter-store-box.step-box__words').show();
        $(imageEditor).find('.square-box-loader').hide();
        $(".step-box.canvas-face #zoom").prop("disabled", !1);
        $(".step-box.canvas-face #rotate").prop("disabled", !1);
        $(this).closest('.step-box').find('.row_canvas').css('opacity', 1);
        $('input.load-photo').prop( "disabled", false );
        $(imageEditor).closest('.steps').find('.step__btn .btn__next').addClass('act_btn').removeClass('postcard-auto-end').addClass('postcard-button');
        var postcard = $('postcard.active');
        $(postcard).removeClass('auto-face');
    });

    //Достаем значение data-id мероприятия
    var eventID = $('div.name-list .list__title').attr('data-id');
    if(eventID > 0) {
        //Достаем по значению название
        var eventTitle = $('div.name-list ul li[id='+eventID+'] span').html();
        //Запиываем как выбранное
        $('.name-list .list__title').text(eventTitle);
    }

    //Подгружаем выбранные ранее имена
    if($('body').find('.words-list') !== "") {
        $('div.words-list .list__title').each(function (i, elem) {
            var ids = $(elem).attr('data-id');
            if(ids > 0) {
                var el = $(elem).closest('.words-list');
                //Достаем по значению название
                var elTitle = el.find('ul li[id='+ids+'] span');
                //Запиываем как выбранное
                $(elem).text(elTitle.html());
            }
        });
    }

    //Подгружаем выбаранные имена
    if($('body').find('.checkbox.gender:checked') !== "") {
        $('.checkbox.gender:checked').each(function (i, elem) {
            changeShowFirstName($(elem).val(), $(elem).closest('.filter-store__box'), false);
        });
    }

    $(document).on('click', '.checkbox.gender', function() {
        changeShowFirstName($(this).val(), $(this).closest('.filter-store__box'), true);
    });

    function changeShowFirstName(sex, block, clear){
        var item = $(block).attr('data-item');
        if(clear) {
            $(block).find('.name-list.item'+item+' input').val('');
        }
        if(sex == 2){
            $(block).find('.name-list.item'+item+' ul li[data-sex="2"]').show().removeClass('hide');
            $(block).find('.name-list.item'+item+' ul li[data-sex="1"]').hide();
        }else{
            $(block).find('.name-list.item'+item+' ul li[data-sex="2"]').hide();
            $(block).find('.name-list.item'+item+' ul li[data-sex="1"]').show().removeClass('hide');
        }
    }

    // search words
    $(document).on('input', '.name-list__title', function () {
        var val = this.value.trim().charAt(0).toUpperCase() + this.value.trim().substr(1).toLowerCase();
        var itemsLi = document.querySelectorAll('.name-search .elem');


        if( val != '' ) {
            itemsLi.forEach(function(elem){
                if (elem.innerText.search(val)  == -1 ) {

                    $(elem).addClass('hide');
                }else {
                    $(elem).removeClass('hide');
                }
            })
        }

        else {
            itemsLi.forEach(function(elem){
                $(elem).removeClass('hide');
            })

            $(this).closest('.steps').find('.btn__next').removeClass('act_btn');
        }
    });

    $(document).on('click', '.elem', function() {

        var elemID = $(this).attr("id");

        var type = $(this).closest('ul.list__wrap').attr('data-type');
        $('input#'+type).val(elemID);

    });

    $(document).on('click', '.rotate-cwRotate', function() {
        var block = $(this).closest('.image-editor');
        block.cropit('rotateCW');
    });

    $(document).on('click', '.rotate-ccw', function() {
        var block = $(this).closest('.image-editor');
        block.cropit('rotateCCW');
    });

    $(document).on('click', '#facebook', function() {
        $.ajax({
            url: '/authorize/facebook',
            type: "POST",
            dataType: 'JSON',
            cache: false,
            processData: false,
            contentType: false,
            success: function (response) {
                if(response.page !== ""){
                    $('.popup-add-foto').html(response.page);
                }
            }
        });
    });

    $(document).on('click', '.editor-position', function() {
        $("input.edit").prop("disabled", true);
        var block = $(this).closest('.image-editor.photo');
        var ids = $(block).attr('id');
        var getWidth = $('#'+ids).closest('.step-box').attr('data-width');
        var getHeight = $('#'+ids).closest('.step-box').attr('data-height');
        var defaultWidth = 0;
        var viewportWidth = $( window ).width();
        if (viewportWidth < 620) defaultWidth = viewportWidth-20;
        else defaultWidth = (getWidth > getHeight)? 600 : 500;
        var ratio = (getWidth > getHeight)? (getWidth / getHeight): (getHeight / getWidth);
        var defaultHeight = defaultWidth / ratio;

        console.log(defaultHeight);
        console.log(defaultWidth);

        if($('#'+ids+' input.edit:checked').val() === 'v') {
            block.cropit('previewSize', {
                width: defaultHeight,
                height: defaultWidth
            });
        }else{
            block.cropit('previewSize', {
                width: defaultWidth,
                height: defaultHeight
            });
        }

        $("input.edit").prop("disabled", false);

        return false;
    });

    $(document).on('click', '.horizontal', function() {
        $("input.edit").prop("disabled", true);
        var block = $(this).closest('.image-editor.photo');
        var ids = $(block).attr('id');
        $('#'+ids+' .edit-h').prop('checked', true);
        $('#'+ids+' .edit-h').attr('checked', true);
        $('#'+ids+' .edit-v').attr('checked', false);
        var getWidth = $('#'+ids).closest('.step-box__photo').attr('data-width');
        var getHeight = $('#'+ids).closest('.step-box__photo').attr('data-height');
        var getOrient = $('#'+ids).closest('.step-box__photo').attr('data-orient');

        if($('main').hasClass('editing-videos') && getOrient === "v"){
            $('#'+ids+' .cropit-preview').css('width', getHeight).css('height', getWidth)
        }else{
            $('#'+ids+' .cropit-preview').css('width', getWidth).css('height', getHeight)
        }

        $('#'+ids+' .cropit-preview').addClass('cropit-preview__hor');
        if ($('#'+ids+' .cropit-preview').hasClass('cropit-preview__ver')) {
            $('#'+ids+' .cropit-preview').removeClass('cropit-preview__ver');
        }
        $("input.edit").prop("disabled", false);
    });

    $(document).on('click', '.vertical', function() {
        $("input.edit").prop("disabled", true);
        var block = $(this).closest('.image-editor.photo');
        var ids = $(block).attr('id');
        $('#'+ids+' .edit-v').prop('checked', true);
        $('#'+ids+' .edit-v').attr('checked', true);
        $('#'+ids+' .edit-h').attr('checked', false);
        var getWidth = $('#'+ids).closest('.step-box__photo').attr('data-width');
        var getHeight = $('#'+ids).closest('.step-box__photo').attr('data-height');
        var getOrient = $('#'+ids).closest('.step-box__photo').attr('data-orient');

        if($('main').hasClass('editing-videos')){
            if(getOrient === 'h'){
                $('#'+ids+' .cropit-preview').css('width', getHeight).css('height', getWidth)
            }else{
                $('#'+ids+' .cropit-preview').css('width', getWidth).css('height', getHeight)
            }
        }else{
            $('#'+ids+' .cropit-preview').css('width', getHeight).css('height', getWidth)
        }

        $('#'+ids+' .cropit-preview').addClass('cropit-preview__ver');
        if ($('#'+ids+' .cropit-preview').hasClass('cropit-preview__hor')) {
            $('#'+ids+' .cropit-preview').removeClass('cropit-preview__hor');
        }
        $("input.edit").prop("disabled", false);
    });

    $(document).on('click', '.add-more-photo', function() {

        var block = $(this).closest('.steps');

        var max = $('.steps.photo-and-phrases .step-box:first').attr('data-max');

        var length = $('.steps.photo-and-phrases .step-box').length;

        if(length < max) {
            var getWidth = $('.step-two.steps.photo-and-phrases .step-box__photo:first').attr('data-width');
            var getHeight = $('.step-two.steps.photo-and-phrases .step-box__photo:first').attr('data-height');
            var getDataZoom = $('.step-two.steps.photo-and-phrases .step-box__photo:first').attr('data-zoom');
            var copyBlock = $('.default .step-box__photo').clone();
            var placeholderNumber = Number($(block).find('.step-box__photo.step-box:last').attr('data-id'));
            var pl = $(block).find('.step-box__photo.step-box:last').attr('data-placeholder');
            var placeholder = Number(pl.replace(/\D+/g, ""));
            var newID = 'p' + (placeholder + 1);
            $(copyBlock).attr('data-placeholder', newID).attr('data-id', placeholderNumber);
            $(copyBlock).addClass('step-box');
            $(copyBlock).prepend('<div class="delete-step-box"></div>');
            $(copyBlock).find('input.load-photo').attr('id', 'load-' + newID).addClass('load-array');
            $(copyBlock).find('label.btn__load').attr('for', 'load-' + newID);
            $(copyBlock).find('input.edit').attr('name', placeholderNumber + '[positions][' + newID + ']');
            $(copyBlock).find('.form_phrases').attr('name', placeholderNumber + '[phrase][' + newID + ']').attr('id', 'selection_phrases_' + newID);
            $(copyBlock).find('.image-editor').addClass('photo').attr('id', 'photo-block-' + newID);
            $(copyBlock).find('.list__wrap').attr('data-type', 'selection_phrases_' + newID);
            $(copyBlock).find('.form_phrases').attr('id', 'selection_phrases_' + newID);
            $(block).find('.step__btn.mt-3').before(copyBlock);
            $('#photo-block-' + newID).closest('.step-box__photo').attr('data-width', getWidth).attr('data-height', getHeight).attr('data-zoom', getDataZoom);
            var length = length + 1;
            if(length == max){
                $('.steps.photo-and-phrases .add-more-photo').hide();
            }
        }
    });
    // Проверка кнопки (если ли она) добавления фото при редактировании видео
    function addFotoHide() {
        var max = $('.steps.photo-and-phrases .step-box:first').attr('data-max');

        var length = $('.steps.photo-and-phrases .step-box').length + 1;

        if(max < length){
            $('.steps.photo-and-phrases .add-more-photo').hide();
        }
    }
    addFotoHide();


    // Удаление дополнительного фото
    $(document).on('click', '.delete-step-box', function(){

        var max = $('.steps.photo-and-phrases .step-box:first').attr('data-max');

        var length = $('.steps.photo-and-phrases .step-box').length + 1;

        var steps = $(this).closest('.steps');
        if(max < length){
            $('.steps.photo-and-phrases .add-more-photo').show();
        }

        var list =  $('.steps .step-box__photo.step-box').find('p.words-list__title').attr('data-id');
        if( list !== ""){
            steps.find('.btn__next').addClass('act_btn');
        } else {
            steps.find('.btn__next').removeClass('act_btn');
        }

        $(this).closest('.step-box__photo').remove();
    });

    $(document).on('change', '.load-video', function() {

        var dataID = this.id;
        if(this.files[0].size > 70000000){
            alert($('message[type="video"]').attr('text'));
        }else{
            $('.load-video[id="'+dataID+'"]').val() && (
                $('.load-video[id="'+dataID+'"]').next().next().slideDown(),
                    $('.load-video[id="'+dataID+'"]').parents(" div.step-box ").next().children().addClass("act_btn")
            );

            $(this).closest('.image-editor').find('.cropit-video').removeClass('d-none').show();
            var $source = $(this).closest('.image-editor').find('source');
            $source[0].src = URL.createObjectURL(this.files[0]);
            $source.parent()[0].load();
        }
    });

    $(document).on('click', '.btn__sub', function(e) {

        var ajaxQueue = $({});

        var processing = true;

        var i = 0;

        $.ajaxQueue = function(ajaxOpts) {
            // Hold the original complete function
            var oldComplete = ajaxOpts.complete;

            // Queue our ajax request
            ajaxQueue.queue(function (next) {
                // Create a complete callback to invoke the next event in the queue
                ajaxOpts.complete = function () {
                    // Invoke the original complete if it was there
                    if (oldComplete) {
                        oldComplete.apply(this, arguments);
                    }

                    // Run the next query in the queue
                    if(processing) {
                        next();
                    }else{
                        processing = true;
                    }
                };

                // Run the query
                $.ajax(ajaxOpts);
            });
        };

        //Отображаем прелоадер
        $(".loaderArea, .loader-box, .loader").css('display', 'flex');

        $('.alert-email').hide();

        $('#data .form-control-feedback').css('display', 'none').text('');

        //Собираем все данные для обработки
        var form = new FormData();

        //Пол и имя детей
        var count = $('.filter-store.step-one.steps').attr('data-number');
        if(count > 0) {
            for (i = 0; i < count; i++) {
                var placeholderFilter = $('input[name="gender'+i+'"]').closest('.filter-store__box').attr('data-placeholder');
                if ($('input[name="gender'+i+'"]').is(':checked') === true) {
                    form.append( placeholderFilter+'[sex]', $('input[name="gender'+i+'"]:checked').val() );
                }
                if ($('#child_name_selection'+i).val() > 0) {
                    form.append( placeholderFilter+'[childName]', $('#child_name_selection'+i).val() );
                }
            }
        }

        //ФИО
        form.append('user_name', $('#form_name').val());

        //Телефон
        form.append('user_phone', $('#form_phone').val());
        form.append('__phone_prefix', $('#phone_prefix').val());

        //Эл. почта
        form.append('user_email', $('#form_email').val());

        //Город
        form.append('user_city', $('#form_city').val());

        var edit = false;
        if($(this).hasClass('edit')){
            edit = true;
        }

        if(edit === false) {
            form.append('action', 'create');

            $.ajaxQueue({
                url: location.pathname,
                type: "POST",
                data: form,
                dataType: 'JSON',
                cache: false,
                processData: false,
                contentType: false,
                success: function (response) {
                    if (response.code === 200) {
                        processing = true;
                        form.append('order', response.list);
                    } else {
                        processing = false;
                        $(".loaderArea, .loader-box, .loader").hide();
                        if (response.list !== "") {
                            for (var i = 0; i < response.list.length; i++) {
                                $('#data .feedback-' + response.list[i].field).addClass('has-danger');
                                $('#data .feedback-' + response.list[i].field).next().text(response.list[i].message).css('display', 'block');
                            }
                        }
                    }
                }
            });
        }

        form.append('action', '');

        //Цикл по видео
        if($('body').find('.load-video') !== "") {
            $('.load-video').each(function (i, elem) {
                if($(elem)[0].files.length > 0) {

                    //Собираем все данные для обработки
                    var formVideo = new FormData();

                    //Добавляем видео
                    formVideo.append('file', $(elem)[0].files[0]);
                    formVideo.append('action', 'video');

                    $.ajaxQueue({
                        url: location.pathname,
                        type: "POST",
                        data: formVideo,
                        dataType: 'JSON',
                        cache: false,
                        processData: false,
                        contentType: false,
                        success: function (response) {
                            form.append($(elem).attr("name"), response.list);
                        }
                    });

                }
            });
        }

        //Цикл по именам
        if($('body').find('.form_child_name_selection') !== "") {
            $('.form_child_name_selection').each(function (i, elem) {
                form.append($(elem).attr("name"), $(elem).val());
            });
        }

        //Цикл по фото
        if($('body').find('.image-editor.photo') !== "") {

            $('.image-editor.photo').each(function (i, elem) {

                var placeholder = $(elem).closest('.step-box').attr('data-placeholder');

                if( $(elem).hasClass('postcard') === false) {

                    var placeholderID = $(elem).closest('.step-box').attr('data-id');

                    if (placeholderID > 0) {
                        var crop = $(elem).cropit('export', {
                            type: 'image/jpeg',
                            quality: .9,
                            originalSize: false
                        });

                        if (crop !== undefined) {

                            //Собираем все данные для обработки
                            var formImage = new FormData();

                            //Добавляем картинку
                            formImage.append('file', crop);
                            formImage.append('action', 'image');

                            $.ajaxQueue({
                                url: location.pathname,
                                type: "POST",
                                data: formImage,
                                dataType: 'JSON',
                                cache: false,
                                processData: false,
                                contentType: false,
                                success: function (response) {
                                    form.append(placeholderID + '[images][]', response.list);
                                }
                            });

                            var checked = $(elem).find('input.edit:checked').val();

                            if (checked === undefined) {
                                if ($(elem).attr('data-width') < $(elem).attr('data-height')) {
                                    checked = 'v';
                                } else {
                                    checked = 'h';
                                }
                            }

                            form.append(placeholderID + '[position][]', checked);
                        }

                        var phrase = $('input#selection_phrases_' + placeholder).val();
                        if (phrase > 0) {
                            form.append(placeholderID + '[phrase][]', phrase);
                        }
                    }
                }
            });
        }

        //Цикл по фразам
        if($('body').find('.form_phrases') !== "") {
            $('.form_phrases').each(function (i, elem) {
                if($(elem).hasClass('phrases-and-photo') === false) {
                    form.append($(elem).attr("name"), $(elem).val());
                }
            });
        }

        //Цикл по текстовым данным
        if($('body').find('#text-name') !== "") {
            $('#text-name').each(function (i, elem) {
                form.append($(elem).attr("name"), $(elem).val());
            });
        }

        //Забираем данные для видео-открытки
        if($('main postcard').length > 0){

            $('main postcard').each(function (i, elem) {

                var placeholder = $(elem).attr('data-id');

                //Собираем все данные для обработки
                var formImagePostcard = new FormData();

                //Добавляем картинку
                formImagePostcard.append('file', $(elem).attr('data-face') );
                formImagePostcard.append('action', 'postcard_image');

                $.ajaxQueue({
                    url: location.pathname,
                    type: "POST",
                    data: formImagePostcard,
                    dataType: 'JSON',
                    cache: false,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        form.append(placeholder+'[image]', response.list);
                    }
                });

                //Собираем все данные для обработки
                var formImagePostcardMouth = new FormData();

                //Добавляем картинку
                formImagePostcardMouth.append('file', $(elem).attr('data-mouth') );
                formImagePostcardMouth.append('action', 'postcard_mouth');

                $.ajaxQueue({
                    url: location.pathname,
                    type: "POST",
                    data: formImagePostcardMouth,
                    dataType: 'JSON',
                    cache: false,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        form.append(placeholder+'[mouth]', response.list);
                        form.append(placeholder+'[mouth-offset-x]', $(elem).attr('data-offset-x') );
                        form.append(placeholder+'[mouth-offset-y]', $(elem).attr('data-offset-y'));
                    }
                });

                //Собираем все данные для обработки
                var formImagePostcardFull = new FormData();

                //Добавляем картинку
                formImagePostcardFull.append('file', $(elem).attr('data-full') );
                formImagePostcardFull.append('action', 'postcard_full');

                $.ajaxQueue({
                    url: location.pathname,
                    type: "POST",
                    data: formImagePostcardFull,
                    dataType: 'JSON',
                    cache: false,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        form.append(placeholder+'[full]', response.list);
                    }
                });

            });

        }

        $.ajaxQueue({
            url: location.pathname,
            type: "POST",
            data: form,
            dataType: 'JSON',
            cache: false,
            processData: false,
            contentType: false,
            success: function (response) {
                if(response.code === 200){
                    window.location = response.list;
                }else{
                    $(".loaderArea, .loader-box, .loader").hide();
                    $('.steps').hide();
                    $('.steps.step-form').show().removeClass('d-none');
                    if(response.list !== ""){
                        for (var i = 0; i < response.list.length; i++) {
                            $('#data .feedback-'+response.list[i].field).addClass('has-danger');
                            $('#data .feedback-'+response.list[i].field).next().text(response.list[i].message).css('display', 'block');
                        }
                    }
                }
            }
        });

        return false;
    });

    //ШАГИ
    //Шаг один - Имя и Пол ребенка
    $(document).on('click', '.step-one__box', function() {
        var _this = this;
        var countActive = 0;
        var count = Number($('.filter-store.step-one.steps').attr('data-number'));
        var inputName = $(this).find('.name-list__title').val();


        if(count > 0) {

            for (var i = 0; i < count; i++) {
                if ($('input[name="gender'+i+'"]').is(':checked') === true) {
                    if ($('#child_name_selection'+i).val() > 0) {
                        countActive++;
                    }
                }
            }
        }
        if ($(this).find('input.checkbox.gender').is(':checked') === true) {

            //addClass for input
            $(this).find('.name-list__title').addClass('active').prop('disabled', false)
        }


        if (count === countActive && inputName !== '') {

            $(_this).siblings('.step__btn').find('.btn__next').addClass('act_btn');
        }

        else {

            $(_this).siblings('.step__btn').find('.btn__next').removeClass('act_btn');
        }
    }
);
    //Открываем инпут если пользователь редактирует страницу
    if($('main').hasClass('editing-videos')) {
        $(document).find('.name-list__title').addClass('active').prop('disabled', false)
    }

    //Шаг видео
    $(document).on('change', 'input.load-video', function() {
        var _this = this;
        var videoCount = $('input.load-video').length;
        var videoVal = 0;

        $('input.load-video').each(function (i, elem) {
            if($(elem).val() !== ""){
                videoVal++;
            }
        });

        if(videoCount === videoVal){
            $(_this).closest('.steps').find('.btn__next').addClass('act_btn');
        }else{
            $(_this).closest('.steps').find('.btn__next').removeClass('act_btn');
        }

    });

    //Шаг картинок
    $(document).on('change', 'input.load-photo', function() {
        loadingImageEnableNextButton(this);
    });

    //Шаг фраз которые идут в связке с картинками
    $(document).on('click', '.step-two.steps .elem', function() {
        var _this = this;
        var phraseCount = $('.step-two.steps .step-box__photo.step-box').find('input.form_phrases').length;
        var phraseVal = 0;
        $('.step-two.steps .step-box__photo.step-box').find('input.form_phrases').each(function (i, elem) {
            if($(elem).val() !== ""){
                var src = $(elem).closest('.step-box__photo.step-box').find('#image').attr('src');
                if (src !== undefined && src !== "") {
                    phraseVal++;
                }
            }
        });

        if(phraseCount > 0 && phraseCount === phraseVal){
            $(_this).closest('.steps').find('.btn__next').addClass('act_btn');
        }else{
            $(_this).closest('.steps').find('.btn__next').removeClass('act_btn');
        }
    });

    //Шаг текста
    $(document).on('input', '#text-name', function() {
        var _this = this;
        var textCount = _this.value.length;
        if(textCount >= 3){
            $(_this).closest('.step-text ').find('.btn__next').addClass('act_btn');
        }else{
            $(_this).closest('.step-text ').find('.btn__next').removeClass('act_btn');
        }
    });

    //Шаг фраз
    $(document).on('click', '.step-words.steps', function() {
        var _this = this;
        var phraseCount = $('input.form_child_name_selection').length;
        var phraseVal = 0;

        $('input.form_child_name_selection').each(function (i, elem) {
            if($(elem).val() !== ""){
                phraseVal++;
            }
        });

        if(phraseCount === phraseVal){
            $(_this).find('.btn__next').addClass('act_btn');
        }else{
            $(_this).find('.btn__next').removeClass('act_btn');
        }
    });

    //Шаг формы
    $(document).on('input change', 'form.step-form-box', function() {

        var _this = this;
        var emailReg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;
        var phoneReg = /[^[0-9]/;
        var formCount = $('form.step-form-box input.form-control').length;
        var formVal = 0;

        $('form.step-form-box input.form-control').each(function (i, elem) {

            if($(elem).val() !== " "){
                if($(elem).attr('id') === "form_email") {
                    var emailMy = $(elem).val();
                    if ( emailMy !== " " && emailMy.length > 6 && emailReg.test($(elem).val())) {
                        // $('#form_email').css('border', 'none');
                        formVal++;
                    } else {
                        // $('#form_email').css('border', '1px solid red');
                        formVal--
                    }

                }

                else if($(elem).attr('id') == "form_phone") {
                    var phone = $(elem).val();
                    // var plusSign = phone.substring(0, 1);
                    var otherSign = phone.substring(1);

                    if(phone !== " " && phone.length > 6 && phone.length < 14 && phoneReg.test(otherSign) === false){
                        formVal++;
                    }
                    else {
                        formVal--
                    }
                    // }else if ((otherSign.length > 6 && otherSign.length < 14) && phoneReg.test(otherSign) === false) {
                    //     // $('#form_phone').css('border', 'none');
                    //     formVal++;
                    // }

                    // else {
                    //     formVal++;
                    //     var plusSign = phone.substring(0, 1);
                    //     var otherSign = phone.substring(1);
                    //
                    //     if (plusSign !== '+') {
                    //         $('#form_phone').css('border', '1px solid red');
                    //     } else if ((otherSign.length > 6 && otherSign.length < 14) && phoneReg.test(otherSign) === false) {
                    //         $('#form_phone').css('border', 'none');
                    //         formVal++;
                    //     }
                    // }

                }else {
                       formVal++;
                }
                // if($(elem).attr('id') === "form_phone"){
                //     formVal++;
                // }
            }
        });
        // if($('.checkbox-form').is(":checked") === true){
        //     formVal++;
        // }
        // console.log(`${formCount} -- ${formVal}`);

        if(formCount === formVal && $('.checkbox-form').is(":checked") === true){
            $(_this).closest('.steps').find('.btn__next').addClass('act_btn').removeClass('btn__next').addClass('btn__sub');
        }else{
            $(_this).closest('.steps').find('.btn__next').removeClass('act_btn');
            $(_this).closest('.steps').find('.btn__sub').removeClass('act_btn').removeClass('btn__sub').addClass('btn__next');
        }
    });

    //Если заполнены уже все поля то сразу ставим активную кнопку в форме
    $( document ).ready(function() {

		if ("ontouchstart" in document.documentElement)
		{
			$('body').addClass('touch');
			$('.center-editor-zoom').first().hide(); 
		}




         let input =  document.getElementById('name-box0');
        if(input) {

         input.addEventListener('focus', function() {
             this.nextElementSibling.classList.add('show');
         })
         let nameList = document.querySelector('.list__wrap');

         if (nameList) {
             nameList.addEventListener('click', function(e) {
                 console.log(e.target);
                 if(e.target.tagName == 'LI' || e.target.tagName == 'SPAN') {
                     this.classList.remove('show');
                 }
             })
         }
     }



        var emailReg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;
        var formCount = $('form.step-form-box input').length;
        var formVal = 0;

        $('form.step-form-box input').each(function (i, elem) {
            if($(elem).val() !== " "){

                let input =  document.getElementById('name-box0');
                if($(elem).attr('type') === "email"){
                    if(emailReg.test($(elem).val())){
                        $('#form_email').css('border', 'none');
                        formVal++;

                    }else{
                        $('#form_email').css('border', '1px solid red');

                    }
                }else {
                    formVal++;
                }
                if($('.checkbox-form').is(":checked") == false){
                    formVal++

                }

            }else{
                if($(elem).attr('id') === "form_phone"){
                    formVal++;

                }
            }
        });

        if(formCount === formVal){
            $('form.step-form-box').closest('.steps').find('.btn__next').addClass('act_btn').removeClass('btn__next').addClass('btn__sub');

        }else{
            $('form.step-form-box').closest('.steps').find('.btn__next').removeClass('act_btn');
            $('form.step-form-box').closest('.steps').find('.btn__sub').removeClass('act_btn').removeClass('btn__sub').addClass('btn__next');
        }
    });

    // Закрытие попапа после выбора загрузки с утсройства
    $(document).on('click', '.btn__add-label', function(){
        $('.popup-add-foto').hide();
    });

    //Открытие поп-апа для выбора варианта загрузки картинки
    $(document).on('click', '.btn-add-foto', function(){
        $(this).closest('.step-box').find('.popup-add-foto').show();
        $(this).closest('.step-box').find('.image-editor.postcard').addClass('active');
    });

    $(document).on('click', '.popup-add-foto .close', function(){
        $('.popup-add-foto').hide();
        $(this).closest('.step-box').find('.image-editor.postcard').removeClass('active');
    });
    $(document).on('click', '.btn-close', function(){
        $('.popup_foto-animated').addClass('d-none');
        $('.cropit-image-loaded').removeClass('d-none');
        $('.btn_redactor-foto').removeClass('d-none');
        $(this).addClass('d-none');
        $(this).closest('.image-editor').find('.cropit-preview').removeClass('d-none');
        $(this).closest('.image-editor').find('.btn.btn_redactor-foto').removeClass('d-none');
    });

    $(document).on('click', '.btn_redactor-foto', function(){
        var block = $(this).closest('.step-box').find('.popup_foto-animated');
        imageLayer.draw();
        mouthPath__mouth('clip');
        imageLayer.draw();
        $(block).find('.container-face').removeClass('d-none');
        $(block).find('.postcard-image-mouth').attr('src', document.getElementsByTagName("canvas")[0].toDataURL());
        imageLayer.getContext().restore();
        imageLayer.draw();
        mouthPath__mouth('hole');
        $(block).find('.postcard-image').attr('src', document.getElementsByTagName("canvas")[0].toDataURL());
        imageLayer.draw();
        $('.postcard-image-mouth').addClass('floating');
        $('.popup_foto-animated, .btn-close').removeClass('d-none');
        $('.container-face').css('width', width).css('height', height);
        $('.cropit-image-loaded').addClass('d-none');
        $(this).addClass('d-none');
    });

    //addClass for input active parseFoto
    $(document).on('click', '.aut-box__mob label', function(){
        $('#fileSource').removeClass('active');
        $('#mob-input').addClass('active');
    });
    $(document).on('click', '#filesorce', function(){
        $('#mob-input').removeClass('active');
        $('#fileSource').addClass('active');
    });


    $("#selfie").on('click',function (){
        $('#fileSource').addClass('active');
        $('.aut-box').hide();
        $('.webcam').show();
        // webcam
        Webcam.on( 'error', function(err) {
            $('.aut-box').show();
            $('.webcam').hide();
            alert('Ошибка «Не удалось подключиться к камере»');
            $('#selfie').show();
        } );



        var snapwidth = '640';
        var snapHeight = '480';
        //Detect userAget OS
        let agentStr = window.navigator.userAgent;
        let startIndex = agentStr.indexOf("(");
        let endIndex = agentStr.indexOf(";");
        startIndex += 1;

        let subStr = agentStr.substring(startIndex,endIndex);
        //change capture size if device powered by Mac
        if (subStr == 'Macintosh') {
            snapwidth = '640'
            snapHeight = '360'

        }



        Webcam.set({
            width: 768,
            height:576,
            // dest_width: 640,
            // dest_height: 480,
            dest_width: snapwidth,
            dest_height: snapHeight,
            image_format: 'jpeg',
            jpeg_quality: 90
        });
        Webcam.attach('#my_camera');

    });
    // close block web camera
    $('.popup-add-foto .close').on('click', function(){
        $('.webcam').hide();
        $('.aut-box').show();
        Webcam.reset();
    })
    // add foto on web camera
    $('.button-web').on('click', function(){
        function take_snapshot(_this) {

            // take snapshot and get image data
            Webcam.snap(function (data_uri) {
                // display results in page
		        $('input.load-photo').prop( "disabled", true );
		        $(_this).closest('.step-box').find('.cropit-preview').show();
		        $(_this).closest('.step-box').find('.row_canvas').css('opacity', 0);
		        $(_this).closest('.step-box').find('.square-box-loader').show();
		        $(_this).closest('.step-box').find('.cropit-preview-image-container').hide();
                var imageEditor = $(_this).closest('.step-box').find('.image-editor.postcard.active');
                loadingPhoto(imageEditor, data_uri);
                $('.popup-add-foto').hide();
                $('.aut-box').show();
                $('.webcam').hide();
            });
        }
        take_snapshot(this);
        Webcam.reset();

    })

    //clear value input #name-box
    $(document).on('click', '.gender-group-step label', function () {
        var closes = $(this).closest('.steps');
        closes.find('#name-box').val('').removeAttr('data-id');
        closes.find('#child_name_selection').val('');
        var clear =  closes.find('.step__btn button');
        if (clear.hasClass("act_btn")) {
            clear.removeClass('act_btn');
        }
    });

    // skroll in steps
    $(".btn__next").on('click',function (){
        var x = window.matchMedia("(max-width: 768px)")
        var updiv = $(this).closest('.steps').next();
        if (x.matches) {
            $('html, body').animate({
                scrollTop:$(updiv).offset().top
            }, 500);
        }
    });


    $(document).on('click', '.image-editor.photo .editor-zoom .editor-zoom__minus', function() {
        if($(this).closest('.step-box').hasClass('canvas-face') === false) {
            var step = $(this).closest('.image-editor').cropit('zoom');
            var min = $(this).closest('.editor-zoom').find('.cropit-image-zoom-input').attr('min');
            if (step > min) {
                var nextStep = step - 0.015;
                $(this).closest('.editor-zoom').find('.cropit-image-zoom-input').val(nextStep);
                $(this).closest('.image-editor').cropit('zoom', nextStep);
            }
        }
    });

    $(document).on('click', '.image-editor.photo .editor-zoom .editor-zoom__plus', function() {
        if($(this).closest('.step-box').hasClass('canvas-face') === false) {
            var step = $(this).closest('.image-editor').cropit('zoom');
            var max = $(this).closest('.editor-zoom').find('.cropit-image-zoom-input').attr('max');
            var nextStep = Number(step + 0.015);
            if (nextStep <= max) {
                $(this).closest('.editor-zoom').find('.cropit-image-zoom-input').val(nextStep);
                $(this).closest('.image-editor').cropit('zoom', nextStep);
            }
        }
    });
});