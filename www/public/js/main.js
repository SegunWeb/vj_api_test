"use strict";



(function () {

    if($('.dropdown-menu.header-lang li').length  === 0){
        $('.dropdown-menu.header-lang').remove();
        $('.dropdown-toggle.lang-menu__button .caret').hide();
    }
    function r(e, n, t) {
        function o(i, f) {
            if (!n[i]) {
                if (!e[i]) {
                    var c = "function" == typeof require && require;
                    if (!f && c) return c(i, !0);
                    if (u) return u(i, !0);
                    var a = new Error("Cannot find module '" + i + "'");
                    throw a.code = "MODULE_NOT_FOUND", a;
                }

                var p = n[i] = {
                    exports: {}
                };
                e[i][0].call(p.exports, function (r) {
                    var n = e[i][1][r];
                    return o(n || r);
                }, p, p.exports, r, e, n, t);
            }

            return n[i].exports;
        }

        for (var u = "function" == typeof require && require, i = 0; i < t.length; i++) {
            o(t[i]);
        }

        return o;
    }

    return r;
})()({
    1: [function (require, module, exports) {
        // ---- preloader ----
        $(window).on('load', function () {
            var preloader = $('.loaderArea');
            var loader = preloader.find('.loader');
            loader.fadeOut();
            preloader.delay(10).fadeOut('slow');
        }); //---------slider----------

        $('.top-slider').slick({
            dots: true,
            infinite: true,
            speed: 500,
            fade: true,
            cssEase: 'linear',
            autoplay: true,
            autoplaySpeed: 3000,
            responsive: [{
                breakpoint: 768,
                settings: {
                    dots: false,
                }
            }]
        });
        $('.video-slider').slick({
            infinite: true,
            slidesToShow: 2,
            slidesToScroll: 1,
            autoplay: true,
            autoplaySpeed: 2000,
            speed: 500,
            dots: false,
            pauseOnFocus:true,
            responsive: [{
                breakpoint: 769,
                settings: {
                    slidesToShow: 1,
                    slidesToScroll: 1
                }
            }]
        });

        $('.multiple-items').slick({
            infinite: true,
            slidesToShow: 6,
            slidesToScroll: 1,
            responsive: [{
                breakpoint: 768,
                settings: {
                    slidesToShow: 1,
                    slidesToScroll: 1
                }
            }]
        });
        $('.testimonials-slider').slick({
            infinite: true,
            dots: true,
            autoplay: true,
            autoplaySpeed: 7000,
            fade: true,
            cssEase: 'linear',
            responsive: [{
                breakpoint: 768,
                settings: {
                    slidesToShow: 1,
                    slidesToScroll: 1
                }
            }]
        }); //--------pop-up------

        $(document).on('click', '.btn__auth', function(){
            $('.filter').show(), $('.header-nav-box').removeClass("d-flex"), $('.btn__hamburger').removeClass("is-active");
        });

        $('.close').click(function () {
            $('.filter, .filters, .register-popap').hide();
            $('.filter-popap.active').removeClass('active');
            $('#selfie').show()
        });

        //pop-up filter video

        $(".btn__filter-video").on("click", function () {
            $(".filter-popap").addClass("active"), $(this).removeClass('button-active'),
                $(document).mouseup(function (e) {

                    var centerForm = $(".aunt");
                    if (!centerForm.is(e.target) && centerForm.has(e.target).length === 0) {

                        $(".filter-popap").removeClass("active")
                        $(".button_form-visible").addClass('button-active');
                    }
                })
        });

        // filter tags
        $(".tag-filter").on("click", function () {
            $('#tags').toggleClass('act-tags');
        });



        var h_hght = $('.header-blue').outerHeight();
        var h_mrg = 0;

        $(function () {

            var elem = $('.btn__filter-video');
            var top = $(this).scrollTop();
            if (top > h_hght) {
                elem.css('top', '0');
            }

            $(window).scroll(function () {
                top = $(this).scrollTop();
                if (top  < h_hght) {
                    elem.removeClass('fixed-menu');
                } else {
                    elem.css('top', h_mrg).addClass('fixed-menu');
                }
            });

        });

        $('.btn__hamburger').click(function () {
            $('.header-nav-box').toggleClass("d-flex");
        });



        // ----- bg img video -----
        let videoBox;
        let source;
        let playBtn = document.querySelector('.play-video');
        let videoPreview = $('.img-video');



        if (videoPreview.next()[0]) {
            videoBox  = videoPreview.next()[0];
            //videoBox.setAttribute('src', '');
            // videoBox.setAttribute('autoplay', '');
            videoBox.setAttribute('controls', '');
            if (videoBox.querySelector('a')) {
                source = videoBox.querySelector('a').href;
            }

        }

        $(document).on('click', '.img-video', function () {

            let src;
            var $video = $(this).next();

            if ($video.attr('data-src')) {
                src = $video.attr('data-src');
            } else if(source) {
                src = source;
                videoBox.setAttribute('src', src);

            } else {
                console.warn('You don\'t have src of video');
            }



            //$video.attr('src', src + '&autoplay=1');
            $video.addClass('d-block');

            var _self = this;

            $(_self).addClass('d-none');

            var _btn = $(this).next().next();

            $(_btn).addClass('d-none');

            if ($video.attr('data-src')) {
                if ($video.attr('src', src + '&autoplay=1')) {
                    $('.img-video, .play-video').click(function () {
                        $video.attr('src', src + '&autoplay=0');
                    });
                }
            }
        });

        var i = 0;

        $(document).on('click', '.play-video', function () {

            let parentEl = this.parentElement;
            let plyBtn = parentEl.querySelector('video');

            if (plyBtn) {
                plyBtn.play();
                plyBtn.setAttribute('controls', 'controls');
            }


            let player;
            let src;
            var $video = $(this).prev();

            if ($video.attr('data-src')) {
                src = $video.attr('data-src');
            } else {
                src = source;
                if (videoBox) {
                    videoBox.setAttribute('src', src);
                }

                if (player) {
                    player.play();
                    i ++;
                }
            }



            if ( $video.attr('data-src')) {
                if ($video.prop('tagName') == 'IFRAME') {
                    $video.attr('src', src + '&autoplay=1')
                }
            }


            $video.addClass('d-block');

            var _self = this;

            $(_self).addClass('d-none');

            var _btn = $(this).prev().prev();

            $(_btn).addClass('d-none');



            if ($video.attr('data-src')) {
                if ($video.prop('tagName') == 'IFRAME') {
                    $('.play-video, .img-video').click(function () {
                        $video.attr('src', src + '&autoplay=0');
                    });
                }
            }
            $('.video-slider').slick('slickPause');
        }); // ---- button burger -----


        (function () {
            var toggles = document.querySelectorAll(".toggle-hamburger");
            var filter = document.getElementById("filt");

            for (var i = toggles.length - 1; i >= 0; i--) {
                var toggle = toggles[i];
                toggleHandler(toggle);
            };

            function toggleHandler(toggle) {
                toggle.addEventListener("click", function (e) {
                    e.preventDefault();
                    this.classList.contains("is-active") === true ? this.classList.remove("is-active") : this.classList.add("is-active");

                    if(this.classList.contains("is-active") === true) {
                        filter.style.display = "block"
                    } else {
                        filter.style.display = "none"
                    }
                });
            }
        })(); // ----- price ------

        //Ready
        $(document).ready(function () {




            if ($(".price-box__discount").hasClass("d-none")) {
                $(".price-box__main p").toggleClass("price-text__discount");
            }
            $('#selfie').on('click', function () {
                $(this).hide();
            })

            // Copied shared link block_view-full-version


            const text = 'Ссылка скопирована';

            let readyBox = document.querySelector('.video-ready__box');

            if (readyBox) {
                readyBox.addEventListener('click', function(e) {
                    if(e.target.classList.contains('btn__save')){
                        console.log(1);
                        return;
                    }

                    if (e.target.tagName == 'P') {
                        let el = e.target;
                        let mainEl = el.closest('.info-box');
                        let spanEl = document.querySelector('.video-ready__box span.link-copied');


                        //CLIPBOARD
                        let clipboard = new ClipboardJS('.copy-link__text');
                        clipboard.on('success', function(e) {});
                        let originText = e.target.textContent;

                        e.target.classList.add('copy-done');
                        e.target.textContent = text;

                        let timoutID = setTimeout( () => {
                            e.target.classList.remove('copy-done');
                            e.target.textContent = originText;
                        }, 2000);

                    }
                })
            }






            let paidsBox = document.querySelector('.container-orders');
            if (paidsBox) {
                paidsBox.addEventListener('click', function(e) {
                    // e.preventDefault();


                    let El = e.target.previousElementSibling;


                    //Check if link was  clicked

                    if (e.target.classList.contains('copy-link__text')) {
                        let curLink = e.target;
                        let input = curLink.previousElementSibling;
                        let mainEl = curLink.closest('.account-info');



                        //CLIPBOARD
                        let clipboard = new ClipboardJS('.copy-link__text');
                        clipboard.on('success', function(e) {});

                        let originText = e.target.textContent;


                        e.target.classList.add('copy-done');
                        e.target.textContent = text;


                        let timoutID = setTimeout( () => {
                            e.target.classList.remove('copy-done');
                            e.target.textContent = originText;
                        }, 2000);

                    }

                });
            }

            let plyrWrap = document.querySelector('.plyr__video-wrapper');

            if (plyrWrap) {
                let el = plyrWrap.closest('.all-video-box');
                if (el) {
                    el.classList.add('plyr');
                }
            }


            //Play stop Plyr player for mobile
            const body = document.querySelectorAll('body');
            function btnPlayVideo(target) {
                let btnPlay = target;
                let parent  = btnPlay.closest('.plyr--full-ui');
                let video = parent.querySelector('.paids-videos');
                video.classList.add('on-play')
                let src = video.dataset.href;
                video.src = src;
            }
            function posterPlayVideo(target) {
                let poster  = target;
                let parent = poster.closest('.plyr__video-wrapper');
                let video = parent.querySelector('video');
                let src = video.dataset.href;
                video.src = src;
            }
            //Plyr control function
            function plyrControl(plyrWrap, videoTag) {
                if (plyrWrap.classList.contains('plyr--playing')) { // Paused if play
                    // console.log('paused');
                    videoTag.removeAttribute("autoplay");
                  

                } else if (!plyrWrap.classList.contains('plyr--stopped')){
                    // console.log('continue');
                } else { // Start play
                    plyrWrap.classList.remove('plyr--paused', 'plyr--stopped');
                    plyrWrap.classList.add('plyr--playing');

                    //Start play change src of video
                    videoTag.setAttribute("autoplay", "");
                    let src = videoTag.dataset.href;
                    videoTag.src = src;
                }
            }


            body[0].addEventListener('click', function (e) {
                //if click on poster
                if (e.target.classList.contains('plyr__poster')) {
                    const parentEl = e.target.closest('.plyr.plyr--full-ui');
                    const videoEl = parentEl.querySelector('video');


                    // if(videoEl.classList.contains('on-play')){
                    //     videoEl.classList.remove('on-play');
                    //     videoEl.pause();
                    // } else {
                    //     videoEl.play();
                    //     videoEl.classList.toggle('on-play');
                    // }

                }
            })

            //HOME PAGE load video after click
            let videoBox = document.querySelector('.video-testimonials-wrap');
            if (videoBox) {
                videoBox.addEventListener('click', function(e) {

                    let parent = e.target.closest('.plyr.plyr--full-ui.plyr--video');
                    if (parent) {
                        let video = parent.querySelector('.paids-videos-home');
                        if (e.target.classList.contains('plyr__poster') || e.target.classList.contains('plyr__control')) {
                            plyrControl(parent,video)
                        }
                    }

                    // //Click on play btn
                    // if (e.target.classList.contains('plyr__control')) {
                    //     let btnPlay = e.target;
                    //     let parent  = btnPlay.closest('.plyr--full-ui');
                    //     let video = parent.querySelector('.paids-videos-home');
                    //     video.classList.add('on-play')
                    //     let src = video.dataset.href;
                    //     video.src = src;
                    //
                    // }
                    //
                    // //click on poster
                    // if (e.target.classList.contains('plyr__poster')) {
                    //     posterPlayVideo(e.target);
                    // }
                })
            }

            //Video page load video after click
            let pageVideoBlock = document.querySelector('.page-video_block');
            if (pageVideoBlock) {
                pageVideoBlock.addEventListener('click', function(e) {
                    console.log('page_video');
                    let parent = e.target.closest('.plyr.plyr--full-ui.plyr--video');

                    if (parent) {
                        let video = parent.querySelector('.paids-videos');
                        if (e.target.classList.contains('plyr__poster') || e.target.classList.contains('plyr__control')) {
                            plyrControl(parent,video)
                        }
                    }
                    // //Click on poster
                    // if (e.target.classList.contains('plyr__poster')) {
                    //     posterPlayVideo(e.target);
                    // }
                    // //Click on play btn
                    // if (e.target.classList.contains('plyr__control')) {
                    //     btnPlayVideo(e.target)
                    // }
                })
            }

            //Demo-video page loading video after click
            let pageDemoVideo = document.querySelector('.video-ready-box.demo');
            if (pageDemoVideo) {
                pageDemoVideo.addEventListener('click', function(e) {

                    let parent = e.target.closest('.plyr.plyr--full-ui.plyr--video');

                    if (parent) {
                        let video = parent.querySelector('.paids-videos');
                        if (e.target.classList.contains('plyr__poster') || e.target.classList.contains('plyr__control')) {
                            plyrControl(parent,video)
                        }
                    }

                    // //Click on poster
                    // if (e.target.classList.contains('plyr__poster')) {
                    //     posterPlayVideo(e.target)
                    // }
                    // //Click on play btn
                    // if (e.target.classList.contains('plyr__control')) {
                    //     btnPlayVideo(e.target);
                    // }
                })
            }

            //Pages: paids and not-paids video  loading after click
            let ordersVideoBox = document.querySelector('.container-orders');
            if (ordersVideoBox) {
                ordersVideoBox.addEventListener('click', function(e) {
                    let parent = e.target.closest('.plyr.plyr--full-ui.plyr--video');

                    if (parent) {
                        let video = parent.querySelector('.paids-videos');
                        if (e.target.classList.contains('plyr__poster') || e.target.classList.contains('plyr__control')) {
                            plyrControl(parent,video)
                        }
                    }
                    // //Click on poster
                    // if (e.target.classList.contains('plyr__poster')) {
                    //     posterPlayVideo(e.target)
                    // }
                    // //Click on play btn
                    // if (e.target.classList.contains('plyr__control')) {
                    //     btnPlayVideo(e.target);
                    // }
                })
            }




        });
        // ----- cropit -------
        $("#load, #load-video").change(function () {
            if ($(this).val()) {
                $(this).next().next().slideDown();
                $(this).next().next().next().css('display', 'flex');
                $(".edit-h").attr("checked", "checked");
                $(this).parent().next().css('display', 'flex');
            }
        });
        $("#load-t").change(function () {
            if ($(this).val()) {
                $(this).next().next().slideDown();
                $(this).next().next().next().css('display', 'flex');
                $(".edit-hor").attr("checked", "checked");
            }
        });

        // ---- user account ----
        $(".form-user input").focus(function () {
            $(this).next().css("opacity", "1");
        }); // --- lists ----

        $(document).on('click', '.elem', function (e) {
            var data = $(this).attr("id");
            var elem = $(this);
            elem.parent(" ul ").prev(" input ").val(elem.text()).attr("data-id", data);
            elem.parent(" ul ").prev(" p ").text(elem.text()).attr("data-id", data);

        });
        $('.list-articles__box p').click(function () {
            $('.list-articles__box p').removeClass("list-act");
            $(this).addClass("list-act");
        }); // ------- steps ----

        $(".btn__next").on('click',function () {
            if( $(this).hasClass('postcard-button') === false ) {
                if( $(this).hasClass('postcard-end') === false ) {
                    if( $(this).hasClass('postcard-auto-end') === false ){
                        if($(this).closest('.steps').next('.steps').hasClass('step-form')){
                            if($(this).closest('.steps').next('.steps').hasClass('d-none')) {
                                if($(this).closest('.steps').next('.steps').find('.btn__sub').length > 0){
                                    $('.step-form .btn__sub').click();
                                }else{
                                    $(this).parent().parent().hide();
                                    $(this).closest('.steps').next('.steps').show().removeClass('d-none');
                                }
                            }else{
                                $(this).parent().parent().hide();
                                $(this).closest('.steps').next('.steps').show().removeClass('d-none');
                            }
                        }else {
                            $(this).parent().parent().hide();
                            $(this).closest('.steps').next('.steps').show().removeClass('d-none');
                        }
                        var vid = $(this).closest('.steps').find('video');
                        if (vid.length > 0) vid.get(0).pause();
                    }
                }
                if( $(this).hasClass('postcard-end')) {
                    $('.popup_foto-animated, .btn-close').addClass('d-none');
                    $('.cropit-image-loaded').removeClass('d-none');
                }
            }
        });
        $('.step-form').on('click', '.btn__sub', function () {
            $('.example-store').remove();
        });
        $(".btn__prev").click(function () {
            if( $(this).hasClass('postcard-step-prev') === false ) {
                $(this).parent().parent().hide();
                $(this).closest('.steps').prev('.steps').show();
            }
            $('.popup_foto-animated, .btn-close').addClass('d-none');
            $('.cropit-image-loaded').removeClass('d-none');
        });

        // ----- testimonials ------
        $('#testi').click(function () {
            $('#testim-text').show();
            $('#testim-video').hide();

            if ($('#testim-text').css('display', 'block')) {
                $('#testim-textarea').children('.testim-mail').hide();
                $('#testim-textarea').children('.testim-info, .testim-textarea').show();
            }
        });
        $('#testi-v').click(function () {
            $('#testim-video').show();
            $('#testim-text').hide();

            if ($('#testim-video').css('display', 'block')) {
                $('#testim-textarea').children('.testim-info, .testim-textarea').hide();
                $('#testim-textarea').children('.testim-mail').css('display', 'flex');
            }
        });
        $(".questions-box__q").click(function () {
            $(this).next().slideToggle();
            $(this).toggleClass("reverse");
        });
    }, {}]
}, {}, [1]);

$(document).ready(function () {

    $(document).on('click', '.btn.btn_auth', function (e) {

        //Включаем прелоадер на время подгрузки
        $(".loaderArea:not(.after-render), .loader-box, .loader").css("display","flex");

        $('.form-login-error').addClass('d-none');

        $.ajax({
            url: '/login_check',
            type: "POST",
            dataType: 'JSON',
            cache: false,
            data: $('form.auth_form').serialize(),
            success: function (data, status, object) {
                if (data.success === true) {
                    //Если на странице видео, то не нужно редиректить
                    if($('form.auth_form input[name="_route"]').val() === "video"){
                        $('.header-nav-box__cont a.link_nav_account').addClass('d-flex').removeClass('d-none').removeClass('btn__auth');
                        $('.header-nav-box__cont button.link_nav_account').addClass('d-none').removeClass('d-flex');
                        $(".loaderArea:not(.after-render),loader-box, .loader").hide();
                        $('.filter').hide();
                        if(data.fullName !== "") {
                            $('form#data #form_name').val(data.fullName);
                        }
                        if(data.phone !== "") {
                            $('form#data #form_phone').val(data.phone).trigger('input');
                        }
                        if(data.email !== "") {
                            $('form#data #form_email').val(data.email);
                        }
                        if(data.city !== "") {
                            $('form#data #form_city').val(data.city);
                        }
                        $('form#data input[type="checkbox"]').prop('checked', true);
                        if($('body').find('.step-box__form').length > 0){
                            $('body').find('.step-box__form .btn__auth').hide();
                            $('.step-form .btn__next').addClass('btn__sub').click();
                        }
                    }else {
                        if (data.url === "") {
                            location.reload();
                        } else {
                            window.location.href = data.url;
                        }
                    }
                } else {
                    $('.form-login-error').removeClass('d-none').text(data.message);

                    $(".loaderArea, loader-box, .loader").hide();
                }
            }
        });

        return false;

    });

    $(document).on('click', '.btn.btn__register', function (e) {

        //Включаем прелоадер на время подгрузки
        $(".loaderArea:not(.after-render), .loader-box, .loader").css("display","flex");

        $('.form-login-error').addClass('d-none');

        $.ajax({
            url: '/registration',
            type: "POST",
            dataType: 'JSON',
            cache: false,
            data: $('form.register_form').serialize(),
            success: function (data, status, object) {
                if (data.success === true) {
                    location.reload();
                } else {
                    $('.form-login-error').removeClass('d-none').text(data.message);

                    $(".loaderArea, loader-box, .loader").hide();
                }
            }
        });

        return false;

    });

    $(document).on('click', '.btn.btn__forgot-pass', function (e) {

        $(this).closest('.filter, .register-popap').hide();

        var form = $("form.resseting_form").closest('.filters');

        form.show();

        return false;

    });

    $(document).on('click', '.btn__register-open', function(){
        $('.filter').hide();
        $('.register-popap').show();
    });

    $(document).on('click', '.btn.btn__resseting', function (e) {

        //Включаем прелоадер на время подгрузки
        $(".loaderArea, loader-box, .loader").css("display","flex");

        $('.form-resseting-error, .form-resseting-success').addClass('d-none');

        $.ajax({
            url: '/resseting',
            type: "POST",
            dataType: 'JSON',
            cache: false,
            data: $('form.resseting_form').serialize(),
            success: function (data, status, object) {
                if (data.code === 200) {
                    $('.form-resseting-success').removeClass('d-none').text(data.message);
                } else {
                    $('.form-resseting-error').removeClass('d-none').text(data.message);
                }
                $(".loaderArea, loader-box, .loader").hide();
            }
        });

        return false;

    });

    // Закрытие попапа после выбора загрузки с утсройства
    $(document).on('click', '.btn__add-label', function(){
        $('.popup-add-foto').hide();
    });

    $('.close').on('click',  function(e){
        $('.webcam').hide();
        e.preventDefault()
    });

    $('.container-face').addClass(" d-none ")
    $(".btn_redactor-foto").on("click", function () {
        $('.container-face').toggleClass(" d-none ");
    });

    //Plyr initialization
    ;(function() {
        const players = Array.from(document.querySelectorAll('.paids-videos')).map(p => new Plyr(p, {

            controls: [
                'play-large', // The large play button in the center
                // 'rewind', // Rewind by the seek time (default 10 seconds)
                'play', // Play/pause playback
                //'fast-forward', // Fast forward by the seek time (default 10 seconds)
                'progress', // The progress bar and scrubber for playback and buffering
                'current-time', // The current time of playback
                'duration', // The full duration of the media
                'mute', // Toggle mute
                'volume', // Volume control
                'captions', // Toggle captions
                'settings', // Settings menu
                'pip', // Picture-in-picture (currently Safari only)
                'airplay', // Airplay (currently Safari only)
                //   'download', // Show a download button with a link to either the current source or a custom URL you specify in your options
                'fullscreen', // Toggle fullscreen
            ],
            settings: ['captions', 'quality', 'loop','volume']
        }));

        const HomePlayers = Array.from(document.querySelectorAll('.paids-videos-home')).map(p => new Plyr(p, {

            controls: [
                'play-large'

            ],
            settings: ['captions', 'quality', 'loop']
        }));

        //Home page one player play other stops
        const HomeVideos = document.querySelectorAll('[data-video]');

        function generateId() {
            return '_' + Math.random().toString(36).substr(2, 9);
        }

        //Generate for all video data-video attributes id
        if (HomeVideos) {
            for(let key in HomeVideos) {
                let id = generateId();
                if(HomeVideos[key].tagName == 'VIDEO') {
                    HomeVideos[key].setAttribute('data-video', id);
                }
            }
        }

        //get node collection
        const videos = document.querySelectorAll('[data-video]');
        function stopOthers () {
            var id  = this.dataset.video,  i = 0;
            for (var j = videos.length; i < j; i++) {

                if (videos[i].dataset.video != id ) {
                    videos[i].pause();
                }
            }
        }
        var i = 0;
        for (var j = videos.length; i < j; i++) {
            if(videos[i].tagName == 'VIDEO') {
                videos[i].addEventListener("play", stopOthers, false);
            }
        }
    })();

    $( document ).ready(function() {
        const seoBtn = document.querySelector('.blockseo__text a.seo-more');
        const seoText = document.querySelector('.seo-content');
        const seoBlock = document.querySelector('.blockseo__text');
        const seoChild = seoText.querySelector('div');


        if(seoBlock !== null && seoBtn !== null) {
            seoBlock.style.display = "";
            seoBtn.addEventListener('click', (e) => {
                e.preventDefault();
                seoText.classList.toggle('hide-seo');
                if (!seoText.classList.contains('hide-seo')) {
                    seoBtn.textContent = 'закрыть';
                } else {
                    seoBtn.textContent = 'раскрыть';
                }
            })
        }
        else if (!seoChild) {
            seoBlock.style.display = "none"
        }
    });
});

$( document).ready(function() {
    // // input phone init
    $(function(){
        $('#form_phone').phonecode({
            preferCo:'ua',
            default_prefix: '380',
            // prefix: 'ua',
        });
    });

    $(function(){
        $('#form_phone_reg').phonecode({
            preferCo:'ua',
            default_prefix: '380',
            // prefix: 'ua',
        });
    });

    // copy link url
    const linkUrl = document.querySelector('#url-link');
    const funcUrl = (consts, texts ) => {
        if(consts !== null) {
            consts.addEventListener('click', function () {
                consts.innerHTML = texts
            });
        }
        return false
    };
    funcUrl(linkUrl, 'ссылка скопирована');

    //clipboard url
    const toClipboard = (el) => {
        if(el !== null) {
            el.setAttribute('data-clipboard-text',location.href+'');
            let clipboard = new ClipboardJS(el);
            clipboard.on('success', function(e) {});
        }
        return false
    };
    toClipboard(linkUrl);

    // click toggle
    (function() {
        const tags = document.querySelector('#tags');
        const filterButton = document.querySelector('.tag-filter');
        if(tags !== null) {
            const notes = tags.childNodes;
            if( notes.length <= 3 ) {
                filterButton.style.display = "none"
            } else {
                filterButton.style.display = "block"
            }
        } else {
            return false
        }
    }());

    // validate
    let buttonOpenReg = document.querySelector('.btn__register-open');
    if(buttonOpenReg !== null) {
        buttonOpenReg.addEventListener('click', function () {
            let buttonSub = document.querySelector("#sub_reg");
            buttonSub.setAttribute('disabled', 'disabled');

            let emailReg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;
            let phoneReg = /[^[0-9]/;
            let usersName = document.querySelector('#form_name_reg');
            let usersEmail = document.querySelector('#form_email_reg');
            let usersPhone = document.querySelector('#form_phone_reg');

            let countVal = {
                userName: false,
                userEmail: false,
                userPhone: false
            };

            let test = () => {
                setTimeout(() => {
                    const nameUsers = countVal.userName;
                    const emailUsers = countVal.userEmail;
                    const phoneUsers = countVal.userPhone;

                    if(nameUsers !== false && emailUsers !== false && phoneUsers !== false) {
                        buttonSub.removeAttribute('disabled');
                    } else {
                        buttonSub.setAttribute('disabled', 'disabled');
                    }
                });
            };

            usersName.addEventListener('input', function (e) {
                const val= e.target.value;
                test();

                if(val.length > 2 ) {
                    countVal.userName = true;
                } else {
                    countVal.userName = false;
                }
            });
            usersEmail.addEventListener('input', function (e) {
                const val = e.target.value;
                const emailTest = emailReg.test(val);
                test();

                if(val.length !== 0 && emailTest === true) {
                    countVal.userEmail = true;

                } else {
                    countVal.userEmail = false;

                }
            });
            usersPhone.addEventListener('input', function (e) {
                const val = e.target.value;
                const phoneTest = phoneReg.test(val);
                test();

                if(val.length > 6 && val.length < 15 && !phoneTest) {
                    countVal.userPhone = true;

                } else {
                    countVal.userPhone = false;

                }
            });
        });
    }

    // Subscribe
    $('#subscribeForm input').on('click', function (e) {
        e.preventDefault();
        var checked = $(this).attr('data-value'),
            id = $(this).closest( 'form' ).find('input[name="id"]').val(),
            order_id_field = $(this).closest( 'form' ).find('input[name="order_id"]'),
            order_id = null;

        if( order_id_field.length > 0 ) {
            order_id = order_id_field.val();
        }

        $.ajax({
            url: '/subscription/'+id,
            type: "POST",
            data: { 'paid' : checked, 'order_id' : order_id },
            dataType: 'JSON',
            cache: false,
            beforeSend: function() {
                $(".loaderArea, loader-box, .loader").show();
            },
            success: function (response) {
                if (response.link !== "" && response.link !== undefined){
                    window.location.href = response.link;
                }else if (response.form !== "" && response.form !== undefined) {
                    $('#hidden-payment').html(response.form);
                    setTimeout(function () {
                        $("#hidden-payment #payment-send-form").find(':submit').click();
                    }, 1000);
                } else {
                    $(".loaderArea, loader-box, .loader").hide();
                }
            },
        });
    });

    $( '#subscribeFormDiscount button[type="submit"]' ).on('click', function (e) {
        e.preventDefault();
        $(this).closest('#subscribeFormDiscount').submit();
    });
    $( '#subscribeFormDiscount' ).on( 'submit', function (e) {
        e.preventDefault();
        var form = $(this),
            id = form.find('input[name="id"]').val();

        $.ajax({
            url: '/subscription/'+id,
            type: "POST",
            data: $( this ).serialize(),
            dataType: 'JSON',
            cache: false,
            beforeSend: function() {
                $(".loaderArea, loader-box, .loader").show();
            },
            success: function (response) {
                if (response.link !== "" && response.link !== undefined){
                    window.location.href = response.link;
                }else if (response.form !== "" && response.form !== undefined) {
                    $('#hidden-payment').html(response.form);
                    setTimeout(function () {
                        $("#hidden-payment #payment-send-form").find(':submit').click();
                    }, 1000);
                } else {
                    if(response.message !== "" && response.message !== undefined) {
                        form.find('.form-control-feedback').text(response.message).css('display', 'block');
                    }
                    $(".loaderArea, loader-box, .loader").hide();
                }
            },
        });
    } );

    $( '#subscribeFormDiscount input[name="promocode"]' ).on( 'keyup', function () {
        if( $( this ).val().length > 0 ) {
            $( this ).closest( 'form' ).find( '.btn__pay' ).prop( 'disabled', false );
        } else {
            $( this ).closest( 'form' ).find( '.btn__pay' ).prop( 'disabled', 'disabled' );
        }
    } );

    $( '#not-paids .hasSubscription' ).on( 'click', function (e) {
        e.preventDefault();

        $(".loaderArea, loader-box, .loader").show();

        var url = $(this).attr( 'href' );

        $.ajax({
            url: url,
            type: "POST",
            data: { 'free' : true },
            dataType: 'JSON',
            cache: false,
            success: function (response) {
                if(response.code === 200){
                    window.location.href = response.message;
                }else{
                    $('#not-paids .form-control-feedback').text(response.message).css('display', 'block');
                    $(".loaderArea, loader-box, .loader").hide();
                }
            }
        });
    } );
/* ----- popups -----*/
    const btnSubs = document.querySelector('.subs-button');
    const btnShare = document.querySelector('.btn-share');
    const popUpFilter = document.querySelector('.popup-wrapp');
    const popUp = document.querySelector('.popup-box');
    const popUpPay = document.querySelector('.subs-popup');
    const popUpOffer = document.querySelector('.offer-popup');
    const popUpClose = document.querySelector('.close-popup');

    if(btnSubs){
        btnSubs.addEventListener("click", function () {
            // popUpFilter.style.display = "block";
            // if(popUpFilter.style.display === "block") {
            //     popUp.style.display = "block";
            //     if(popUpOffer) {
            //         popUpOffer.style.display = "block";
            //         popUpPay.style.display = "none";
            //     }
            //     else {
            //         popUpPay.style.display = "block";
            //     }
            // } else {
            //     popUpOffer.style.display = "none";
            //     popUpPay.style.display = "none";
            // }

            popUpFilter.style.display = "block";
            if(popUpFilter.style.display === "block") {
                popUp.style.display = "block";
                popUpPay.style.display = "block";
            } else {
                popUpOffer.style.display = "none";
                popUpPay.style.display = "none";
            }

        });
    }
    // if(btnShare) {
    //     btnShare.addEventListener("click", function () {
    //         if(popUpFilter.style.display === "block") {
    //             popUpOffer.style.display = "none";
    //             popUpPay.style.display = "block";
    //         } else {
    //             popUpOffer.style.display = "none";
    //             popUpPay.style.display = "none";
    //         }
    //     });
    // }
    if(popUpClose) {
        popUpClose.addEventListener("click", function () {
            popUpFilter.style.display = "none";
        });
    }

/* ----- sales -----*/
    const saleBox = document.querySelector('.sale-box');
    const percPrice = document.querySelector('.perc-price');

    if(percPrice && percPrice.textContent.length >= 11 ) {
        saleBox.style.display = "flex";
    }
});











