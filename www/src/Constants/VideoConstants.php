<?php

namespace App\Constants;

class VideoConstants
{
    const IMAGE = 1, IMAGE_MANY = 2, TEXT = 3, URL_AUDIO = 4, AUDIO_PHRASE = 5, VIDEO = 6, POSTCARD = 7, AUDIO_SEX_PHRASE = 8;
    
    const ONE = 1, TWO = 2, THREE = 3, FOUR = 4, FIVE = 5, SIX = 6;
    
    const IMAGE_TITLE = 'Изображение', IMAGE_MANY_TITLE = 'Массив изображений', TEXT_TITLE = 'Текст', URL_AUDIO_TITLE = 'Аудио-имя', AUDIO_SEX_PHRASE_TITLE = 'Аудио-фраза с полом', AUDIO_PHRASE_TITLE = 'Аудио-фраза', VIDEO_TITLE = 'Видео', POSTCARD_TITLE = 'Видео визитка';
    
    const IMAGE_JPEG = '.jpeg', IMAGE_JPG = '.jpg', IMAGE_PNG = '.png';
    
    const TYPE_DEMO = 'Демо', TYPE_FULL = 'Полное';
    
    const VIDEO_AVI = '.avi', VIDEO_MP4 = '.mp4', VIDEO_SWF = '.swf', VIDEO_GIF = '.gif', VIDEO_MOV = '.mov';
    
    const AUDIO_WAV = '.wav', AUDIO_MP3 = '.mp3', AUDIO_AAC = '.aac', AUDIO_AIF = '.aif';
    
    const IMAGE_ORIENTATION_YES = 'Да', IMAGE_ORIENTATION_NO = 'Нет';
    
    static public function loadVideoType()
    {
        return [
            self::TYPE_DEMO => self::ONE,
            self::TYPE_FULL => self::TWO
        ];
    }
    
    static public function loadVideoPlaceholderValues()
    {
        return [
            self::IMAGE_TITLE            => self::IMAGE,
            self::IMAGE_MANY_TITLE       => self::IMAGE_MANY,
            self::TEXT_TITLE             => self::TEXT,
            self::URL_AUDIO_TITLE        => self::URL_AUDIO,
            self::AUDIO_SEX_PHRASE_TITLE => self::AUDIO_SEX_PHRASE,
            self::AUDIO_PHRASE_TITLE     => self::AUDIO_PHRASE,
            self::VIDEO_TITLE            => self::VIDEO,
            self::POSTCARD_TITLE         => self::POSTCARD
        ];
    }
    
    static public function loadVideoOrientation()
    {
        return [
            self::IMAGE_ORIENTATION_YES => self::ONE,
            self::IMAGE_ORIENTATION_NO  => self::TWO
        ];
    }
}