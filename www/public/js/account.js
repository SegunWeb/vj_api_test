$( document ).ready(function() {

    $(document).on('click', '.save', function() {

        //Включаем прелоадер на время подгрузки
        $(".loaderArea, .loader-box, .loader").show();

        var input = $(this).closest('.form-user').find('input');

        var _this = this;

        var data = { 'value' : input.val(), 'type' : input.attr('name') };

        $.ajax({
            url: '/ajax/account',
            type: "POST",
            data: data,
            dataType: 'JSON',
            cache: false,
            success: function (response) {
                if(response.code === 200){
                    $(_this).css('color', '#16da26');

                    setTimeout(function () {
                        $(_this).css('color', '#08F7FE');
                    }, 3000);
                }
                $(".loaderArea,.loader-box, .loader").hide();
            }
        });

        return false;
    });

    $(document).on('click', '.save-password', function() {

        //Включаем прелоадер на время подгрузки
        $(".loaderArea, .loader-box, .loader").show();

        $('input[name="oldPassword"]').css('border', '1px solid #1fc0ff');
        $('input[name="password"]').css('border', '1px solid #1fc0ff');

        var passwordOld = $('input[name="oldPassword"]').val();
        var passwordNew = $('input[name="password"]').val();

        var _this = this;

        var data = { 'password' : passwordNew, 'passwordOld' : passwordOld, 'type' : 'password' };

        $.ajax({
            url: '/ajax/account',
            type: "POST",
            data: data,
            dataType: 'JSON',
            cache: false,
            success: function (response) {

                if(response.code === 200){

                    $('input[name="oldPassword"]').val('');
                    $('input[name="password"]').val('');

                    $(_this).css('color', '#00ff14');

                    setTimeout(function () {
                        $(_this).css('color', '#08F7FE');
                    }, 3000);
                }else{
                    if(response.message === "password_not_valid"){
                        $('input[name="password"]').css('border', '1px solid red');
                    }
                    if(response.message === "old_password_not_valid"){
                        $('input[name="oldPassword"]').css('border', '1px solid red');
                    }

                    $(_this).css('color', 'red');

                    setTimeout(function () {
                        $(_this).css('color', '#08F7FE');
                    }, 3000);
                }

                $(".loaderArea, .loader-box, .loader").hide();
            }
        });

        return false;
    });

    $(document).on('change', '#load-image', function() {

        var _this = this;

        if(this.files[0].size > 10000000){
            alert($('message[type="image_size"]').attr('text'));
        }else if(this.files[0].type !== 'image/png' && this.files[0].type !== 'image/jpeg' && this.files[0].type !== 'image/jpeg' && this.files[0].type !== 'image/gif'){
            alert($('message[type="image_type"]').attr('text'));
        }else {
            //Включаем прелоадер на время подгрузки
            $(".loaderArea, .loader-box, .loader").show();

            var formData = new FormData();
            formData.append("file", $(_this)[0].files[0]);

            $.ajax({
                url: '/ajax/account/avatar',
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                method: 'POST',
                type: 'POST',
                success: function (response) {
                    if (response.message !== "") {
                        $('#load-image + label').css('color', '#189b3a');
                        $('.user-photo__img image').attr('src', response.message);

                        setTimeout(function () {
                            $('#load-image + label').css('color', '#005dba');
                        }, 3000);
                    } else {

                        $('#load-image + label').css('color', 'red');

                        setTimeout(function () {
                            $('#load-image + label').css('color', '#005dba');
                        }, 3000);
                    }
                    $(".loaderArea, .loader-box, .loader").hide();
                }
            });
        }
    });

    $(document).on('click', '.change-pass', function() {
        $('.change-pass-box').toggle('change');
    });

});