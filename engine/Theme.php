<?php

if(!defined("MCR")){ exit("Hacking Attempt!"); }

/**
 * Класс работы с шаблонами в CMS
 */
class Theme {
  /**
   * Метод замены контента через теги
   *
   * @param string Строчное представление шаблона
   * @param array Массив данных в виде "ключ => значение", где ключ это тег, а значение заменяемый контент
   * @return string Возвращает замененные данные
   */
  private static function mathTags($content, $array = []) {
    if (count($array) === 0) {
      return $content;
    }

    return str_replace(array_keys($array), array_values($array), $content);
  }

  /**
   * Метод загрузки шаблона
   *
   * @param string Шаблон, который находится в папке с шаблонами с окончанием .html
   * @return bool|string В случае обнаружения файла с шаблоном возвращает его содержимое, иначе если файл не найден вернет false
   */
  private static function load($template) {
    $template_dir = MCR_THEME_PATH . $template . '.html';

    if (!file_exists($template_dir)) {
        return false;
    }

    return file_get_contents($template_dir);
  }

  /**
   * Метод рендера данных из шаблонов
   *
   * @param string Шаблон, который находится в папке с шаблонами с окончанием .html
   * @param array Массив данных в виде "ключ => значение", где ключ это тег, а значение заменяемый контент
   * @return string Возвращает шаблон с замененным контентом
   */
  public static function render($template, $tags = []) {
    return self::mathTags(self::load($template), $tags);
  }

  /**
   * Создание тегов для передачи в метод render по заранее заготовленному массиву
   *
   * @param array Массив вида key => value
   * @param string Префикс для тегов. 
   * @return array Массив в виде {prefixtag_key} => value
   */
  public static function generateTags($array_values, $prefix_tag) {
    if(!is_array($array_values)) {
      return false;
    }

    foreach($array_values as $tag => $value) {
		  $new_tags["{".$prefix_tag."_".$tag."}"] = $value;
    }
    
    return $new_tags;
  }

  /**
   * Возвращает последнее оповещение пользователю
   *
   * @return void Возвращает последнее оповещение пользователю
   */
  public static function notify() {
    if(empty($_SESSION['mcr_notify'])) return;
    
    $type = $icon = '';

    switch(@$_SESSION['notify_type']) {
      case 1: 
        $type = 'warning';
        $icon = self::render("widgets/notify/warning-icon"); 
      break;

      case 2: 
        $type = 'error';
        $icon = self::render("widgets/notify/error-icon"); 
      break;

      case 3: 
        $type = 'success';
        $icon = self::render("widgets/notify/success-icon"); 
      break;

      case 4: 
        $type = 'info';
        $icon = self::render("widgets/notify/info-icon"); 
      break;

      default: 
        $type = '';
        $icon = self::render("widgets/notify/info-icon");
      break;
    }

    $tags = [
      '{type}' => $type,
      '{title}' => @$_SESSION['notify_title'],
      '{message}' => @$_SESSION['notify_msg'],
      '{icon}' => $icon
    ];

		unset($_SESSION['mcr_notify'], $_SESSION['notify_type'], $_SESSION['notify_title'], $_SESSION['notify_msg']);

    return self::render('widgets/notify/item', $tags);
  }
}


?>
