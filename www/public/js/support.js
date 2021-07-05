$( document ).ready(function() {

    $(document).on('click', '.btn__testim-sub', function() {

        var emailReg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;

        $('textarea, input[name="email"]').css('border', '1px solid #ced4da');

        if($('textarea').val() !== "" && $('textarea').val().length > 3) {

            if($('input[name="email"]').val() !== "" && emailReg.test($('input[name="email"]').val())) {

                if($('input[name="fullName"]').val() !== "") {
                    //Включаем прелоадер на время подгрузки
                    $(".loaderArea, loader-box, .loader").show();

                    var form = $('form#supports').serialize();

                    $.ajax({
                        url: '/ajax/add/feedback',
                        type: "POST",
                        data: form,
                        dataType: 'JSON',
                        cache: false,
                        success: function (response) {
                            if (response.code === 200) {
                                $('textarea').val('');
                                $('.testim-info').show();
                            }
                            $(".loaderArea, loader-box, .loader").hide();
                        }
                    });
                }else{
                    $('input[name="fullName"]').css('border', '1px solid red');
                }
            }else{
                $('input[name="email"]').css('border', '1px solid red');
            }
        }else{
            $('textarea').css('border', '1px solid red');
        }

        return false;
    });
});