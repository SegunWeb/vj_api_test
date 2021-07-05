$( document ).ready(function() {

    $(document).on('click', '.btn__pay', function() {

        $('.pay-box .form-control-feedback').text('').css('display', 'none');

        //Если есть класс payment, значит есть форма выбора
        if($(this).hasClass('payment')){

            var checked = Number($(this).attr('data-value'));

            $(".loaderArea, loader-box, .loader").show();
            var url = document.URL.split("/");
            $.ajax({
                url: '/checkout/'+url[6],
                type: "POST",
                data: { 'paid' : checked },
                dataType: 'JSON',
                cache: false,
                success: function (response) {
                    if (response.link !== "" && response.link !== undefined) {
                        window.location.href = response.link;
                    }else if (response.form !== "") {
                        if(checked == 1 || checked == 2 || checked == 3) {
                            $('#hidden-payment').html(response.form);
                            setTimeout(function () {
                                $("#hidden-payment #payment-send-form").find(':submit').click();
                            }, 1000);
                        } else if (checked == 0){
                            if(response.link !== ""){
                                window.location.href = response.link;
                            }else{
                                $(".loaderArea, loader-box, .loader").hide();
                            }
                        }
                    }else{
                        $(".loaderArea, loader-box, .loader").hide();
                    }
                }
            });
        }else if($(this).hasClass('promocode')){

            var promocode = $('form#promocode input#promo').val();

            $(".loaderArea, loader-box, .loader").show();

            var url = document.URL.split("/");

            $.ajax({
                url: '/checkout/'+url[6],
                type: "POST",
                data: { 'promocode' : promocode },
                dataType: 'JSON',
                cache: false,
                success: function (response) {
                    if(response.code === 200 && response.message !== "" && response.message !== undefined) {
                        window.location.href = response.message;
                    } else if (response.link !== "" && response.link !== undefined){
                        window.location.href = response.link;
                    }else if (response.form !== "" && response.form !== undefined) {
                        $('#hidden-payment').html(response.form);
                        setTimeout(function () {
                            $("#hidden-payment #payment-send-form").find(':submit').click();
                        }, 1000);
                    }else{
                        $('.pay-box .form-control-feedback').text(response.message).css('display', 'block');
                        $(".loaderArea, loader-box, .loader").hide();
                    }
                }
            });
        }else if($(this).hasClass('free')){

            $(".loaderArea, loader-box, .loader").show();

            var url = document.URL.split("/");

            $.ajax({
                url: '/checkout/'+url[6],
                type: "POST",
                data: { 'free' : true },
                dataType: 'JSON',
                cache: false,
                success: function (response) {
                    if(response.code === 200){
                        window.location.href = response.message;
                    }else{
                        $('.pay-box .form-control-feedback').text(response.message).css('display', 'block');
                        $(".loaderArea, loader-box, .loader").hide();
                    }
                }
            });
        }else if($(this).hasClass('subscription')){

            $(".loaderArea, loader-box, .loader").show();

            var url = document.URL.split("/");

            $.ajax({
                url: '/checkout/'+url[6],
                type: "POST",
                data: { 'subscription' : true },
                dataType: 'JSON',
                cache: false,
                success: function (response) {
                    if(response.code === 200){
                        window.location.href = response.message;
                    }else{
                        $('.pay-box .form-control-feedback').text(response.message).css('display', 'block');
                        $(".loaderArea, loader-box, .loader").hide();
                    }
                }
            });
        }

        return false;

    });

});