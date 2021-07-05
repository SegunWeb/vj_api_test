$(document).ready(function() {

    var ajax_call = function () {

        var renderID = parseInt($('.renderID').text());

        var orderID = location.href;

        var orderID = orderID.replace(/\D+/g,"");

        $.ajax({
            url: '/render/status/get',
            type: "POST",
            data: {'renderID': renderID},
            dataType: 'JSON',
            cache: false,
            success: function (response) {

                if (response.code === 200) {

                    if (response.status === "finished" || response.status === "processing" || response.status === "error") {

                        $(".loaderArea, loader-box, .loader").show();

                        window.location = response.url;
                    }


                }
            }
        });
    };

    var interval = 5000;

    setInterval(ajax_call, interval);

});