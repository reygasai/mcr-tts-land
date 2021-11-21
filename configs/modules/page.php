<?php
if (!defined('MCR')) die ('Hacking Attempt!');

return [
  'turn' => true,
  'title' => 'Статические страницы',

  'pages' => [
    'donate' => [
      'title' => 'Описание доната',
      'layout' => 'singlepage',
    ],
    
    'rules' => [
      'title' => 'Правила проекта',
      'layout' => 'singlepage',
    ],

    '404' => [
      'title' => 'Страница не найдена',
      'layout' => 'onepage',
    ],

    '403' => [
      'title' => 'Доступ запрещен',
      'layout' => 'onepage',
    ],

    'unactive' => [
      'title' => 'Модуль выключен',
      'layout' => 'onepage',
    ]
  ],

];
?>
