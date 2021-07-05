<?php
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

use CV\CascadeClassifier, CV\Scalar;
use CV\Face\LBPHFaceRecognizer;
use function CV\{imread, imwrite, cvtColor, equalizeHist, rectangleByRect};
use const CV\{COLOR_BGR2GRAY};

//Расположение фото
$photo = 'upload/tmp/5d3ffebcae35250831564475068.jpg';
$src = imread($photo);
$gray = cvtColor($src, COLOR_BGR2GRAY);

//Ищем лицо по алгоритму lbpcascade_frontalface
$faceClassifier = new CascadeClassifier();
$faceClassifier->load('opencv/lbpcascade_frontalface.xml');
$faceClassifier->detectMultiScale($gray, $faces);

//Ищем максимально большой блок, там и находится лицо
if ($faces) {
	$faceOld = 0; $faceOldWidth = 0;
	foreach ($faces as $key => $face) {
		if($face->width > $faceOldWidth){
			$faceOld = $key;
			$faceOldWidth = $face->width;
		}
	}
}

//Обрезаем фото по координатам лица, оставляя лишь его
$faceLong = new \Imagick($photo);
$faceLong->cropImage($faces[$faceOld]->width, 0, $faces[$faceOld]->x, $faces[$faceOld]->y);

//Этап №1. Применение маски лица
//Получаем маску и ресайзим лицо под размер маски
$maskFace = new \Imagick('opencv/mask_opencv.png');
$faceLong->resizeImage($maskFace->getimagewidth() + $maskFace->getimagewidth()*0.34, 0, \Imagick::FILTER_LANCZOS, 1);

//Создаем прозрачную картинк с альфа каналоми налаживаем маску
$imgFaceTmp = 'upload/tmp/'.uniqid().rand(11111, 99999).'.png';

$canvas = new \Imagick();
$canvas->newimage($faceLong->getimagewidth(), $faceLong->getimageheight(), "transparent");
$canvas->compositeImage($faceLong, \Imagick::COMPOSITE_DEFAULT, -$maskFace->getimagewidth()*0.17, 0 );
$canvas->compositeImage($maskFace, \Imagick::COMPOSITE_DSTIN, 0, 0, \Imagick::CHANNEL_ALPHA);
$canvas->trimImage(0);
$canvas->setImageFormat('png');
$faceBase64 = 'data:image/png;base64,'.base64_encode($canvas->getImageBlob());
$canvas->writeImage($imgFaceTmp); //Сохраняем исключительно для OPENCVб удаляем после обработки

//---------------------------------------------------

$srcFace = imread($imgFaceTmp);
$grayMouth = cvtColor($srcFace, COLOR_BGR2GRAY);

//Ищем лицо по алгоритму lbpcascade_frontalface
$faceClassifierMouth = new CascadeClassifier();
$faceClassifierMouth->load('opencv/Mouth.xml');
$faceClassifierMouth->detectMultiScale($grayMouth, $mouth, 1.01, 10);//3, 10; 7.85, 10
print_r($mouth);

//Ищем максимально большой блок, там и находится лицо
if ($mouth) {
	$mouthOld = 0; $mouthOldCoorY = 0;
	$mouthOldBig = 0; $mouthOldBigWidth = 0;
	$scalar = new Scalar(0, 0, 255); //blue
	foreach ($mouth as $key => $mout) {
		if($mout->y > $mouthOldCoorY){
			$mouthOld = $key;
			$mouthOldCoorY = $mout->y;
		}
		rectangleByRect($srcFace, $mout, $scalar, 3);
		/*if($mout->y > 130){
			if($mout->width > $mouthOldBigWidth){
				$mouthOldBig = $key;
				$mouthOldBigWidth = $mout->width;
			}
		}*/
	}

	/*if($mouthOldBigWidth > 0){
		rectangleByRect($srcFace, $mouth[$mouthOldBig], $scalar, 3);
	}else{
		rectangleByRect($srcFace, $mouth[$mouthOld], $scalar, 3);
	}*/
	
}
imwrite("upload/tmp/test.jpg", $srcFace);
/*
$mouthOffset = 15;

$faceCropMouth = new \Imagick($imgFaceTmp);
$faceCropMouth->cropImage($mouth[$mouthOld]->width, 0, $mouth[$mouthOld]->x, $mouth[$mouthOld]->y + $mouthOffset);
$faceCropMouth->setImageFormat('png');
$faceCropMouth->writeImage('results/test_yes.png');

//---------------------------------------------------------

$faceCropMouth->floodFillPaintImage('rgb(0,0,0)', 100, 'rgb(255,255,255)', 0, 0, true);
$canvas->compositeImage($faceCropMouth, \Imagick::COMPOSITE_DEFAULT, $mouth[$mouthOld]->x, $mouth[$mouthOld]->y + $mouthOffset, \Imagick::CHANNEL_ALPHA);
$canvas->setImageFormat('png');
$canvas->writeImage('results/test_face.png');*/