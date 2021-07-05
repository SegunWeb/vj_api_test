$( document ).ready(function() {
    $('.select2-container').css('width', '100%');
    $(document).on('click', '.btn.btn-app.btn-block', function() {
        var tooltip = $(this).attr('aria-describedby');
        $('.tooltip#'+tooltip).remove();
    });

    //Проверка наличие сообщений и отзывов на которые нет ответов
    $.ajax({
        url: '/admin/notify',
        type: "POST",
        dataType: 'JSON',
        cache: false,
        processData: false,
        contentType: false,
        success: function (response) {
            if(response !== ""){
                if(response.review > 0){
                    $.notify({
                        icon: 'glyphicon glyphicon-warning-sign',
                        title: 'У Вас есть новые отзывы',
                        message: '',
                        url: response.review_path,
                        target: '_blank'
                    });
                }
                if(response.feedback > 0){
                    $.notify({
                        icon: 'glyphicon glyphicon-warning-sign',
                        title: 'У Вас есть новые сообщения',
                        message: '',
                        url: response.feedback_path,
                        target: '_blank'
                    });
                }
            }
        }
    });

    $('.toggle-columns-wrapper input').on('change', function () {
        var $this = $(this),
            sonataTableList = $('table.sonata-ba-list'),
            columnIndex = null;

        sonataTableList.find('thead tr th').each(function (index) {
            if( $(this).text().trim() === $this.val() ) {
                columnIndex = index;
                $(this).toggle();
                return false;
            }
        });
        if(columnIndex) {
            sonataTableList.find('tbody tr').each(function () {
                $($(this).find('td').get(columnIndex)).toggle();
            })
        }
    });

    function toggleEditableField () {
        var sonataTableList = $('table.sonata-ba-list'),
            form = sonataTableList.closest('form');

        if(form.length > 0 && form.attr('action').split('?')[0] === '/admin/app/video/batch') {
            sonataTableList.find('tbody tr').each(function () {
                if($(this).find('span[data-title="USD"]').hasClass('editable-empty')) {
                    $(this).find('span[data-title="UAH"].editable-empty').hide();
                    $(this).find('span[data-title="RUB"].editable-empty').hide();
                    $(this).find('span[data-title="EUR"].editable-empty').hide();
                }
            })
        }
    }
    toggleEditableField();

    var edit = (function () {
        var $editable = $('.editable');
        var setInputStep = function(editable, step) {
            var input = editable.input;
            if (input.type == 'number')
                input.$input.attr('step', step);
        };
        var shownEvent = function (e, editable) {
            setInputStep(editable, 0.01);
        };
        var init = function () {
            $editable.editable();
            $editable.on('shown', shownEvent);
        };
        return {
            init: init
        };
    })();
    edit.init();
});