<?php

if(!defined("MCR")){ exit("Hacking Attempt!"); }

/**
 * Class upload Russian translation
 *
 * @version   0.25
 * @codepage  UTF-8 
 * @author    Chup (chupzzz@ya.ru)
 * @license   http://opensource.org/licenses/gpl-license.php GNU Public License
 * @copyright Free to change
 * @package   cmf
 * @subpackage external
 */

    $translation = array();
    $translation['file_error']                  = 'Файловая ошибка. Попробуйте еще раз.';
    $translation['local_file_missing']          = 'Локальный файл не существует.';
    $translation['local_file_not_readable']     = 'Локальный файл закрыт для чтения.';
    $translation['uploaded_too_big_ini']        = 'Ошибка загрузки файла (загруженный файл превышает лимит директивы the upload_max_filesize из php.ini).';
    $translation['uploaded_too_big_html']       = 'Ошибка загрузки файла (загруженный файл превышает лимит директивы MAX_FILE_SIZE определенной в HTML-форме).';
    $translation['uploaded_partial']            = 'Ошибка загрузки файла (файл загружен частично).';
    $translation['uploaded_missing']            = 'Ошибка загрузки файла (файл не был загружен).';
    $translation['uploaded_unknown']            = 'Ошибка загрузки файла (неизвестный код ошибки).';
    $translation['try_again']                   = 'Ошибка загрузки файла. Попробуйте еще раз.';
    $translation['file_too_big']                = 'Файл очень большой.';
    $translation['no_mime']                     = 'Невозможно определить MIME-тип файла.';
    $translation['incorrect_file']              = 'Некорректный тип файла.';
    $translation['image_too_wide']              = 'Изображение очень широкое.';
    $translation['image_too_narrow']            = 'Изображение очень узкое.';
    $translation['image_too_high']              = 'Изображение очень высокое.';
    $translation['image_too_short']             = 'Изображение очень короткое.';
    $translation['ratio_too_high']              = 'Соотношение сторон очень велико (изображение очень широкое).';
    $translation['ratio_too_low']               = 'Соотношение сторон очень мало (изображение очень высокое).';
    $translation['too_many_pixels']             = 'В изображении очень много пикселей.';
    $translation['not_enough_pixels']           = 'В изображении недостаточно пикселей.';
    $translation['file_not_uploaded']           = 'Файл не загружен. Невозможно продолжить процесс.';
    $translation['already_exists']              = '%s существует. Измените имя файла.';
    $translation['temp_file_missing']           = 'Некорректный временый файл. Невозможно продолжить процесс.';
    $translation['source_missing']              = 'Некорректный загруженный файл. Невозможно продолжить процесс.';
    $translation['destination_dir']             = 'Директория назначения не может быть создана. Невозможно продолжить процесс.';
    $translation['destination_dir_missing']     = 'Директория назначения не существует. Невозможно продолжить процесс.';
    $translation['destination_path_not_dir']    = 'Путь назначения не является директорией. Невозможно продолжить процесс.';
    $translation['destination_dir_write']       = 'Директория назначения закрыта для записи. Невозможно продолжить процесс.';
    $translation['destination_path_write']      = 'Путь назначения закрыт для записи. Невозможно продолжить процесс.';
    $translation['temp_file']                   = 'Невозможно создать временный файл. Невозможно продолжить процесс.';
    $translation['source_not_readable']         = 'Исходный файл нечитабельный. Невозможно продолжить процесс.';
    $translation['no_create_support']           = 'Создание из %s не поддерживается.';
    $translation['create_error']                = 'Ошибка создания %s изображения из оригинала.';
    $translation['source_invalid']              = 'Невозможно прочитать исходный файл.';
    $translation['gd_missing']                  = 'Библиотека GD не обнаружена.';
    $translation['watermark_no_create_support'] = '%s не поддерживается, невозможно прочесть водный знак.';
    $translation['watermark_create_error']      = '%s не поддерживается чтение, невозможно создать водный знак.';
    $translation['watermark_invalid']           = 'Неизвестный формат изображения, невозможно прочесть водный знак.';
    $translation['file_create']                 = '%s не поддерживается.';
    $translation['no_conversion_type']          = 'Тип конверсии не указан.';
    $translation['copy_failed']                 = 'Ошибка копирования файла на сервер. Команда copy() выполнена с ошибкой.';
    $translation['reading_failed']              = 'Ошибка чтения файла.';   
        
?>
