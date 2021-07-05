$(document).on('click', '.step-box.canvas-face .editor-zoom__minus', function(){
    var zoomMinus = $(this).closest('.editor-zoom').find('input');
    var min = zoomMinus.attr('min');
    if (window.innerWidth <= 425) {
		var step = 1.5;
    }else{
    	var step = 3;
	}
    var currentStep = Number(zoomMinus.prop("value"));
    var nexStep = currentStep - step;
    zoom_val = .005 * nexStep;
    if(nexStep <= 0) {
        nexStep = .005 * min;
        zoom_val = 0.005;
    }
    zoomMinus.val(nexStep);
    if($(this).closest('.editor-zoom').hasClass('incline')){
        var e = nexStep - 180;
        photo.rotation(e), imageLayer.draw()
    }else {
        photo.size({
            width: imageSizeInitial.width * zoom_val,
            height: imageSizeInitial.height * zoom_val
        });
       photo.offsetX(centerX*zoom_val);
       photo.offsetY(centerY*zoom_val);
        imageLayer.draw();
    }
});

$(document).on('click', '.step-box.canvas-face .editor-zoom__plus', function(){
    var zoomPlus = $(this).closest('.editor-zoom').find('input');
    var max = zoomPlus.attr('max');
    if (window.innerWidth <= 425) {
		var step = 1.5;
    }else{
    	var step = 3;
	}
    var currentStep = Number(zoomPlus.prop("value"));
    var nexStep = currentStep + step;
    zoom_val = .005 * nexStep;
    if(nexStep > max) {
        zoomPlus.val(max);
        zoom_val = .005 * max;
    }else{
        zoomPlus.val(nexStep);
    }
    if($(this).closest('.editor-zoom').hasClass('incline')){
        if(nexStep > max) nexStep = max;
        var rotateVal = nexStep + 180;
        photo.rotation(rotateVal);
        imageLayer.draw();
    }else {
        photo.size({width: imageSizeInitial.width * zoom_val, height: imageSizeInitial.height * zoom_val});
        photo.offsetX(centerX*zoom_val);
        photo.offsetY(centerY*zoom_val);
        imageLayer.draw();
    }
});

$(document).on('mousemove change', '.step-box.canvas-face #zoom', function() {
    zoomVal = $(this).prop('value') * .005;
    photo.size({width: imageSizeInitial.width * zoomVal, height: imageSizeInitial.height * zoomVal});
    photo.offsetX(centerX*zoomVal);
    photo.offsetY(centerY*zoomVal);
    imageLayer.draw();
    $(".step-box.canvas-face #zoom").prop("disabled", !1);
    $(".step-box.canvas-face #rotate").prop("disabled", !1);
});

$(document).on('mousemove change', '.step-box.canvas-face #rotate', function() {
    var rotateVal = $(this).prop('value') - 180;
    photo.rotation(rotateVal);
    imageLayer.draw();
    $(".step-box.canvas-face #zoom").prop("disabled", !1);
    $(".step-box.canvas-face #rotate").prop("disabled", !1);
});

$(document).on('click', '.not-active-postcard', function(){
    $(this).removeClass('not-active-postcard');
    $('postcard:last').addClass('active');
    if($('postcard').length > 1){
        $('.steps.block__addfoto .step__btn .btn__prev').addClass('postcard-step-prev');
        $(this).closest('.steps').prev('.steps').find('.row_canvas').show().css('opacity', 1);
        $(this).closest('.steps').prev('.steps').find('.cropit-preview').show();
        $(this).closest('.steps').prev('.steps').find('.image-editor__box').show();
        destroyLayers();
        imgUrl = $('postcard:last').attr('data-full');
        initLayers();
    }else{
        $('.steps.block__addfoto .step__btn .btn__prev').removeClass('postcard-step-prev');
        var prevStep = $(this).closest('.steps').prev('.steps');
        if($(prevStep).hasClass('step-first')){
            $(prevStep).find('.step__btn .btn__prev').hide();
        }
        $(prevStep).find('.row_canvas').show();
        destroyLayers();
        imgUrl = $('postcard:last').attr('data-full');
        initLayers();
    }
    $('.steps.block__addfoto .step__btn .btn__next').addClass('postcard-button');
});

$(document).on('click', '.postcard-button', function(){
    var currentStep = $(this).closest('.steps');
    if($(currentStep).hasClass('step-first')){
        $(currentStep).find('.step__btn .btn__prev').show();
    }
    $(currentStep).find('.btn_redactor-foto').removeClass('d-none');
    $(currentStep).find('.image-editor__box').hide();
    $(currentStep).find('.image-editor__descr').hide();
    $(currentStep).find('.btn__load.btn-add-foto').hide();
    var postcard = $('postcard.active');
    $(postcard).attr('data-full', document.getElementsByTagName("canvas")[0].toDataURL('image/png', 1));
    photo.visible(false);
    imageLayer.draw();
    facePath('clip');
    photo.visible(true);
    imageLayer.draw();

    imgUrl_crop = document.querySelector("#container_photo canvas").toDataURL();
    initAnchor__mouth();
    $(this).removeClass('postcard-button').addClass('postcard-end');
    $(this).closest('.step__btn').find('.btn__prev').addClass('postcard-step-prev');
});

$(document).on('click', '.postcard-step-prev', function(){
    var postcard = $('postcard.active');
    var currentStep = $(this).closest('.steps');
    if($(currentStep).hasClass('step-first')){
        $(currentStep).find('.step__btn .btn__next').addClass('act_btn');
    }
    //Если true, значит это шаг к предыдущей картинки которая должна заново отобразится
    //В ином случае отображается картинка которая была последней загруженной
    if( $(this).closest('.step__btn').find('.btn__next').hasClass('postcard-button') ) {
        destroyLayers();
        if ($(postcard).prev('postcard').length > 0) {
            $(currentStep).find('.image-editor__descr.title').html( $(postcard).prev('postcard').attr('data-description'));
            $(postcard).removeClass('active');
            $(postcard).prev('postcard').addClass('active');
            imgUrl = $(postcard).prev('postcard').attr('data-full');
            $(this).closest('.steps').find('.row_canvas').show().css('opacity', 1);
            $(this).closest('.steps').find('.cropit-preview').show();
            $(this).closest('.steps').find('.image-editor__box').show();
            initLayers();
        }
        if ($(postcard).prev('postcard').prev('postcard').length == 0) {
            $(this).removeClass('postcard-step-prev');
             $(this).closest('.steps').find('.step__btn .btn__prev').hide();
        }
    } else if( $(this).closest('.step__btn').find('.btn__next').hasClass('postcard-end') ){
        $(this).closest('.steps').find('.btn_redactor-foto').addClass('d-none');
        $(this).closest('.steps').find('.image-editor__box').show();
        $(this).closest('.steps').find('.image-editor__descr').show();
        $(this).closest('.steps').find('.btn__load.btn-add-foto').show();
        if ($(postcard).prev('postcard').length == 0) {
        	$(this).removeClass('postcard-step-prev');
    	}
        destroyLayers();
        imgUrl = $(postcard).attr('data-full');
        initLayers();
        $(this).closest('.step__btn').find('.btn__next').removeClass('postcard-end').addClass('postcard-button');
    } else if( $(this).closest('.step__btn').find('.btn__next').hasClass('postcard-auto-end') ){
        $('.postcard-result').hide();
        if ($(postcard).prev('postcard').length > 0) {
            $(postcard).removeClass('active');
            $(postcard).prev('postcard').addClass('active');
        }
        var imageEditor = $(this).closest('.steps').find('.image-editor');
        $(imageEditor).closest('.step-box').find('.image-editor__descr.desc').show();
        $(imageEditor).closest('.step-box').find('.btn__load.btn-add-foto').show();
        $('.btn_redactor-foto-edit').addClass('d-none');
        destroyLayers();
        imgUrl = $(postcard).prev().attr('data-full');
        $(imageEditor).find('.postcard-full').attr('src', imgUrl);
        initLayers();
        $(imageEditor).find('.image-editor__box').show();
        $(imageEditor).closest('.steps').find('.row_canvas').show().css('opacity', 1);
        $(imageEditor).closest('.step-box').find('.filter-store-box.step-box__words').show();
        $(imageEditor).find('.square-box-loader').hide();
        $(".step-box.canvas-face #zoom").prop("disabled", !1);
        $(".step-box.canvas-face #rotate").prop("disabled", !1);
        $('input.load-photo').prop( "disabled", false );
        $(imageEditor).closest('.steps').find('.step__btn .btn__next').addClass('act_btn').removeClass('postcard-auto-end').addClass('postcard-button');
        $(postcard).removeClass('auto-face');
        if ($(postcard).prev('postcard').prev('postcard').length == 0) {
             $(imageEditor).closest('.steps').find('.step__btn .btn__prev').hide();
        }
    } else {
        $(this).closest('.steps').find('.btn_redactor-foto').addClass('d-none');
        $(this).closest('.steps').find('.image-editor__box').show();
        $(this).closest('.steps').find('.image-editor__descr').show();
        $(this).closest('.steps').find('.btn__load.btn-add-foto').show();
        destroyLayers();
        imgUrl = $('postcard.active').attr('data-full');
        initLayers();
        $(this).closest('.step__btn').find('.btn__next').removeClass('postcard-end').addClass('postcard-button');
        $(this).removeClass('postcard-step-prev');
    }

    if ($(postcard).prev('postcard').length == 0) {
        $(currentStep).find('.step__btn .btn__prev').hide();
    }
});

$(document).on('click', '.postcard-auto-end', function(){
    var currentStep = $(this).closest('.steps');
    var postcard = $('postcard.active');
    $(currentStep).find('.btn_redactor-foto').addClass('d-none');
    $(currentStep).find('.image-editor__box').show();
    $(currentStep).find('.image-editor__descr').show();
    $(currentStep).find('.btn__load.btn-add-foto').show();
    if($(currentStep).hasClass('step-first')){
        $(currentStep).find('.step__btn .btn__prev').show();
        if( $(postcard).next('postcard').length > 0 ) {
            if($(postcard).next('postcard').attr('data-full').length == 0) {
                $(this).removeClass('act_btn');
            }
        }
    }
    $(postcard).attr('data-mouth', $('.postcard-result .postcard-image-mouth').attr('src') );
    $(postcard).attr('data-face', $('.postcard-result .postcard-image').attr('src') );
    $(postcard).attr('data-full', $('.postcard-result .postcard-image-full').attr('src') );
    $('.postcard-result .postcard-image').attr('src', '');
    $('.postcard-result .postcard-image-mouth').attr('src', '');
    $('.postcard-result .postcard-image-full').attr('src', '');

    $(postcard).removeClass('active');
    if( $(postcard).next('postcard').length > 0 ) {
        $(currentStep).find('.image-editor__descr.title').html( $(postcard).next('postcard').attr('data-description'));
        $(postcard).next('postcard').addClass('active');
        $(this).removeClass('postcard-end').addClass('postcard-button');
        $(this).closest('.step__btn').find('.btn__prev').addClass('postcard-step-prev');
        destroyLayers();
        if($(postcard).next('postcard').attr('data-full').length > 0){
            $(this).closest('.steps').find('.image-editor__box').show();
            $(this).closest('.steps').find('.cropit-preview').show();
            imgUrl = $(postcard).next('postcard').attr('data-full');
            initLayers();
        }else{
            $(this).closest('.steps').find('.image-editor__box').hide();
            $(this).closest('.steps').find('.image-editor__descr').show();
            $(this).closest('.steps').find('.cropit-preview').hide();
        }
    }else{
        $(this).removeClass('postcard-end');
        destroyLayers();
        initLayers();
        if($(this).closest('.steps').next('.steps').hasClass('step-form')){
            if($(this).closest('.steps').next('.steps').hasClass('d-none')) {
                if($(this).closest('.steps').next('.steps').find('.btn__sub').length > 0){
                    $('.step-form .btn__sub').click();
                }else{
                    $(this).parent().parent().hide();
                    $(this).closest('.steps').next('.steps').show();
                }
            }else{
                $(this).parent().parent().hide();
                $(this).closest('.steps').next('.steps').show();
            }
        }else {
            $(this).parent().parent().hide();
            $(this).closest('.steps').next('.steps').show();
        }
        var vid = $(this).closest('.steps').find('video');
        if (vid.length > 0) vid.get(0).pause();
        $(this).closest('.steps').next().find('.btn__prev').addClass('not-active-postcard');
    }

    $(this).removeClass('postcard-auto-end');
    $('.postcard-result').hide();
    $('.btn_redactor-foto-edit').addClass('d-none');
    $(this).closest('.steps').find('.cropit-image-input').val('');
    return false;
});

$(document).on('click', '.postcard-end', function(){
    var currentStep = $(this).closest('.steps');
    $(currentStep).find('#zoom').val(200);
    $(currentStep).find('#rotate').val(180);
    var postcard = $('postcard.active');
    $(currentStep).find('.btn_redactor-foto').addClass('d-none');
    $(currentStep).find('.image-editor__box').show();
    $(currentStep).find('.image-editor__descr').show();
    $(currentStep).find('.btn__load.btn-add-foto').show();
    if($(currentStep).hasClass('step-first')){
        $(currentStep).find('.step__btn .btn__prev').show();
        if( $(postcard).next('postcard').length > 0 ) {
            if($(postcard).next('postcard').attr('data-full').length == 0) {
                $(this).removeClass('act_btn');
            }
            $(currentStep).find('.image-editor__descr.title').html( $(postcard).next('postcard').attr('data-description'));
        }
    }

    imageLayer.draw();
    mouthPath__mouth('clip');
    imageLayer.draw();

    $(postcard).attr('data-mouth', document.getElementsByTagName("canvas")[0].toDataURL('image/png', 1));
    imageLayer.getContext().restore();
    imageLayer.draw();

    mouthPath__mouth('hole');
    $(postcard).attr('data-face', document.getElementsByTagName("canvas")[0].toDataURL('image/png', 1));
    imageLayer.draw();
    $(postcard).attr('data-offset-x', cutX);
    $(postcard).attr('data-offset-y', cutY - 8);
    
    $(postcard).removeClass('active');
    if( $(postcard).next('postcard').length > 0 ) {
        $(postcard).next('postcard').addClass('active');
        $(this).removeClass('postcard-end').addClass('postcard-button');
        destroyLayers();
        if($(postcard).next('postcard').attr('data-full').length > 0){
            imgUrl = $(postcard).next('postcard').attr('data-full');
            initLayers();
        }else{
            $(this).closest('.steps').find('.image-editor__box').hide();
            $(this).closest('.steps').find('.image-editor__descr').show();
            $(this).closest('.steps').find('.cropit-preview').hide();
        }
    }else{
        $(this).removeClass('postcard-end');
        destroyLayers();
        initLayers();
        if($(this).closest('.steps').next('.steps').hasClass('step-form')){
            if($(this).closest('.steps').next('.steps').hasClass('d-none')) {
                if($(this).closest('.steps').next('.steps').find('.btn__sub').length > 0){
                    $('.step-form .btn__sub').click();
                }else{
                    $(this).parent().parent().hide();
                    $(this).closest('.steps').next('.steps').show();
                }
            }else{
                $(this).parent().parent().hide();
                $(this).closest('.steps').next('.steps').show();
            }
        }else {
            $(this).parent().parent().hide();
            $(this).closest('.steps').next('.steps').show();
        }
        var vid = $(this).closest('.steps').find('video');
        if (vid.length > 0) vid.get(0).pause();
        $(this).closest('.steps').next().find('.step__btn .btn__prev').addClass('not-active-postcard');
    }
    $(this).closest('.steps').find('.cropit-image-input').val('');
});

// globals
var width = $(".image-editor.photo.postcard .cropit-preview").width();
var height = $(".image-editor.photo.postcard .cropit-preview").height();
var scale = 1;
var lineWidth = width * .008;
var shift_coord = {'x': 0, 'y': 0};
var imageLayer, lineLayer, pointLayer;
var photo, imageSizeInitial, centerX, centerY, zoomVal = 2, imgUrl_crop, imgUrl, pic_origin;

Konva.pixelRatio = 1;
var stage = new Konva.Stage({
    container: 'container_photo',
    width: width,
    height: height
});

imageLayer = new Konva.Layer();
lineLayer = new Konva.Layer();
// pointLayer = new Konva.Layer();

stage.add(imageLayer);
stage.add(lineLayer);
// stage.add(pointLayer);
//functions
loadСoordinates();
facePath('draw');

 $( document ).ready(function() {
	if ($('body').hasClass('touch'))
		$('.center-editor-zoom').first().hide(); 
});



function initLayers() {
    pic_origin = new Image();
    pic_origin.onload = function () {
        loadImage();
    };
    if(imgUrl !== undefined){
        pic_origin.src = imgUrl;
    }
}

function facePath(mode) {
    if (mode == 'clip') {
        var context = imageLayer.getContext();
    }
    if(mode == 'draw') {
        var context = lineLayer.getContext();
    }
    if(mode == 'dragLimit') {
        // var context = curveLayer.getContext();
        // node.dragBoundFunc(function(pos){
        //   // important pos - is absolute position of the node
        //   // you should return absolute position too
        //   return {
        //     x: this.absolutePosition().x,
        //     y: pos.y
        //   };
        // });
    }

    context.beginPath();
    context.moveTo(bezier.start.attrs.x, bezier.start.attrs.y);
    context.bezierCurveTo(
        bezier.control1.attrs.x,
        bezier.control1.attrs.y,
        bezier.control2.attrs.x,
        bezier.control2.attrs.y,
        bezier.end.attrs.x,
        bezier.end.attrs.y
    );

    context.quadraticCurveTo(
        quad.control.attrs.x,
        quad.control.attrs.y,
        quad.end.attrs.x,
        quad.end.attrs.y
    );

    context.quadraticCurveTo(
        quad1.control.attrs.x,
        quad1.control.attrs.y,
        bezier.start.attrs.x,
        bezier.start.attrs.y
    );

    if (mode == 'clip') {
        context.save();
        context.clip();
    }
    if(mode == 'draw') {
        context.setAttr('strokeStyle', '#08F7FE');
        context.setAttr('lineWidth', lineWidth);
        context.stroke();
    }
}

function loadImage() {
    photo = new Konva.Image({
        x: 0,
        y: 0,
        image: pic_origin,
        draggable: true,
        dragBoundFunc: function(pos){
            var newX = pos.x,
            newY = pos.y;
            var maxWidthRightBottom = width + photo.size().width/2*scale - 50;
            var maxHeightRightBottom = height + photo.size().height/2*scale - 50;
            var maxWidthLeftTop = (photo.size().width/2 * scale) - 50;
            var maxHeightLeftTop = (photo.size().height/2 * scale) - 50;

            if (pos.x < -maxWidthLeftTop) {
                newX = -maxWidthLeftTop;
            }
            if (pos.y < -maxHeightLeftTop) {
                newY = -maxHeightLeftTop;
            }
            if (pos.x > maxWidthRightBottom) {
                newX = maxWidthRightBottom;
            }
            if (pos.y > maxHeightRightBottom) {
                newY = maxHeightRightBottom;
            }

            return {
                x: newX,
                y: newY
            }
        }
    });

    //Photo size not larger than container size
    adaptPhotoSize();

    imageSizeInitial = {
        width: photo.size().width,
        height: photo.size().height
    };
	centerX = imageSizeInitial.width / 2;
	centerY = imageSizeInitial.height / 2;
	photo.offsetX(centerX);
	photo.offsetY(centerY);
        photo.attrs.x = photo.attrs.x + width/2;
	photo.attrs.y = photo.attrs.y + height/2;

 /*   if (photo.size().width > 2500 || photo.size().height > 2500) {
        scale=0.25;
        photo.scale({
            x: scale,
            y: scale
        });
     } */

    imageLayer.add(photo);
    imageLayer.draw();
	//вешаем обработку pinch пальцами
	var imageContainer=$('.cropit-preview')[0];
	var mc = new Hammer.Manager(imageContainer);
	var rotate = new Hammer.Rotate();
	var pinch = new Hammer.Pinch();

	//pinch.recognizeWith(rotate);
	
	mc.add([pinch]);
	
	mc.on("pinchin", function(ev) {
		$('.editor-zoom:not(.incline) .editor-zoom__minus').click();
	});

	mc.on("pinchout", function(ev) {
		$('.editor-zoom:not(.incline) .editor-zoom__plus').click();
	});
	
	/*mc.on("rotate", function(ev) {
		if (ev.rotation > 0) {
		  $('.incline .editor-zoom__plus').click();
		} else {
		  $('.incline .editor-zoom__minus').click();
		}
	}); */
};

function loadСoordinates() {
    var kwidth = 600, kheight = 400;
    var maskWidth = kwidth * .28;
    var maskHeight = kheight * .62;
    shift_coord.x = (width - maskWidth) / 2;
    shift_coord.y = (height - maskHeight) / 2;
    if (window.innerWidth <= 425) {
        var maskCenter = {x: maskWidth / 2 + shift_coord.x, y: maskHeight / 2};
    }else{
        var maskCenter = {x: maskWidth / 2 + shift_coord.x, y: maskHeight / 2 + shift_coord.y};
    }

    bezier = {
        start: {attrs: {x: maskCenter.x - maskWidth * .45, y: maskCenter.y - maskHeight * .16}},
        control1: {attrs: {x: maskCenter.x - maskWidth * .43, y: maskCenter.y - maskHeight * .5}},
        control2: {attrs: {x: maskCenter.x + maskWidth * .43, y: maskCenter.y - maskHeight * .5}},
        end: {attrs: {x: maskCenter.x + maskWidth * .45, y: maskCenter.y - maskHeight * .16}}
    };
    quad = {
        control: {attrs: {x: maskCenter.x + maskWidth * .5, y: maskCenter.y + maskHeight * .5}},
        end: {attrs: {x: maskCenter.x + maskWidth * 0, y: maskCenter.y + maskHeight * .48}}
    };
    quad1 = {
        control: {attrs: {x: maskCenter.x - maskWidth * .5, y: maskCenter.y + maskHeight * .5}},
    };
};

function adaptPhotoSize() {
   if ( photo.size().width > width+width*0.1 ) scale=(width+200)/photo.size().width;
   else scale =1;
    photo.scale({
            x: scale,
            y: scale
        });
    return true;
}

var lineWidthMouth = width * .004;
var radius = 10;
//var radius = width * .008;
var shift_coord;

if (window.innerWidth <= 425) {
    var maskWidth = width * .25;
    var maskHeight = height * .2;
    shift_coord.x = (width - maskWidth)/2;
    shift_coord.y = (height - maskHeight)*.66;
    var maskCenter = {x: maskWidth / 2 + shift_coord.x, y: maskHeight + 10 + shift_coord.y};
}else {
    var maskWidth = width * .13;
    var maskHeight = height * .13;
    shift_coord.x = (width - maskWidth)/2;
    shift_coord.y = (height - maskHeight)*.72;
    var maskCenter = {x: maskWidth / 2 + shift_coord.x, y: maskHeight / 1.5 + shift_coord.y};
}

var photoLayer, lineLayer, anchorLayer, curveLayer;
var photoFace, imageSizeInitial, centerX, centerY, zoomVal = 1,
    imgUrl_crop, imgUrl_face, imgUrl_mouth, imgUrl, pic_origin, pic_face;
var mouthUp, mouthDown;
var step;

anchorLayer = new Konva.Layer();
curveLayer = new Konva.Layer();
photoLayer = new Konva.Layer();
// lineLayer = new Konva.Layer();

//functions
stage.add(photoLayer);
// stage.add(lineLayer);
stage.add(curveLayer);
stage.add(anchorLayer);

function initAnchor__mouth() {
    destroyLayers();
    $(".step-box.canvas-face #zoom").prop('value', 100);
    $(".step-box.canvas-face #rotate").prop('value', 180);
    loadFace__mouth();
    loadСoordinates__mouth();
    anchorLayer.draw();
    mouthPath__mouth('draw');
    facePath('draw');
    step = 2;
};

function loadFace__mouth() {
    pic_origin = new Image();
    pic_origin.onload = function () {
        loadImage__mouth();
    };
    pic_origin.src = imgUrl_crop;
}

// keep curves insync with the lines
anchorLayer.on('beforeDraw', function () {
    if(step == 2) {
        mouthPath__mouth();
    }
});

function loadСoordinates__mouth() {
    mouthUp = {
        start: buildAnchor__mouth(maskCenter.x - maskWidth * .5 , maskCenter.y - maskHeight * .4 ),
        middle: buildAnchor__mouth(maskCenter.x - maskWidth * 0 , maskCenter.y - maskHeight * .06 ),
        end: buildAnchor__mouth(maskCenter.x + maskWidth * .5 , maskCenter.y - maskHeight * .4 )
    };
    mouthDown = {
        middle: buildAnchor__mouth(maskCenter.x - maskWidth * 0 , maskCenter.y + maskHeight * .6 ),
    };
};

const query = () => {
    let size = 12;
    if (window.matchMedia("(min-width: 768px)").matches) {
        size = 2;
    }
    return size;
};

function buildAnchor__mouth(x, y) {


    var anchor = new Konva.Circle({
        x: x,
        y: y,
        radius: radius,
        stroke: '#FE53BB',
        fill: '#FE53BB',
        strokeWidth: query(),
        draggable: true,
        dragBoundFunc: function(pos) {
            var newY = pos.y < (bezier.start.attrs.y + maskHeight * 2) ? (bezier.start.attrs.y + maskHeight * 2) : pos.y;
            var newX = pos.x < (quad1.control.attrs.x + maskWidth * .6) ? (quad1.control.attrs.x + maskWidth * .6) : pos.x;
            if (pos.x > (quad.control.attrs.x - maskWidth * .6)) {
                newX = (quad.control.attrs.x - maskWidth * .6)
            }
            if (pos.y > (quad.end.attrs.y + maskHeight * -.3)) {
                newY = (quad.end.attrs.y + maskHeight * -.3)
            }
            return {
                x: newX,
                y: newY
            };
        }
    });

    // add hover styling
    anchor.on('mouseover', function () {
        document.body.style.cursor = 'pointer';
        this.stroke('#F5D300');
        this.fill('#F5D300');
        this.strokeWidth(12);
        anchorLayer.draw();
    });

    anchor.on('mouseout', function () {
        document.body.style.cursor = 'default';
        this.stroke('#FE53BB');
        this.fill('#FE53BB');
        this.strokeWidth(query());
        anchorLayer.draw();
    });

    anchor.on('dragend', function () {
        // mouthPath();
        // updateDottedLines();
    });

    anchorLayer.add(anchor);
    return anchor;
};

function mouthPath__mouth(mode) {
    if(mode === undefined) mode = 'draw';
    var context;
    if (mode == 'clip') {
        context = imageLayer.getContext();
        context.clear();
    }
    if (mode == 'hole') {
        context = imageLayer.getContext();
    }
    if (mode == 'draw') {
        context = curveLayer.getContext();
        context.clear();
    }

    // draw Up
    context.beginPath();
    context.moveTo(mouthUp.start.attrs.x, mouthUp.start.attrs.y);
    // draw bezier
    context.bezierCurveTo(
        mouthUp.middle.attrs.x - maskWidth * .4,
        mouthUp.middle.attrs.y,
        mouthUp.middle.attrs.x + maskWidth * .4,
        mouthUp.middle.attrs.y,
        mouthUp.end.attrs.x,
        mouthUp.end.attrs.y
    );
    // draw line right
    context.lineTo(mouthUp.end.attrs.x, mouthDown.middle.attrs.y - maskHeight * .5)
    // draw down bezier
    context.bezierCurveTo(
        mouthDown.middle.attrs.x + maskWidth * .4,
        mouthDown.middle.attrs.y,
        mouthDown.middle.attrs.x - maskWidth * .4,
        mouthDown.middle.attrs.y,
        mouthUp.start.attrs.x,
        mouthDown.middle.attrs.y - maskHeight * .5
    );
    // draw line left
    context.lineTo(mouthUp.start.attrs.x, mouthUp.start.attrs.y)

    if (mode == 'clip') {
        context.save();
        context.clip();
    }
    if (mode == 'hole') {
        // context.save();
        cutX = mouthUp.start.attrs.x - ( (width - 154) / 2) - 1;
	    if (window.innerWidth <= 425) {
			cutY = mouthUp.start.attrs.y - ( (height - 223) / 2) + 5;
	    }else{
	        cutY = mouthUp.start.attrs.y - ( (height - 223) / 2) - 2;
	    }
        context.fill();
    }
    if (mode == 'draw') {
        context.setAttr('strokeStyle', '#08F7FE');
        context.setAttr('lineWidth', lineWidthMouth);
        context.stroke();
    }
};

function loadImage__mouth() {
    photo = new Konva.Image({
        x: 0,
        y: 0,
        image: pic_origin,
        draggable: false
    });

    imageLayer.add(photo);
    imageLayer.draw();
};

function destroyLayers() {
    anchorLayer.destroyChildren();
    anchorLayer.draw();
    curveLayer.destroyChildren();
    curveLayer.draw();
    photoLayer.getContext().restore();
    photoLayer.destroyChildren();
    photoLayer.draw();
    imageLayer.getContext().restore();
    imageLayer.destroyChildren();
    imageLayer.draw();
    // lineLayer.destroyChildren();
    // lineLayer.draw();
}
