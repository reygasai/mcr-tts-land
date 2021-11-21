<?php

if (!defined('MCR')) die ('Hacking Attempt!');

class Logger
{
  private $db;

  public function __construct($db)
  {
    $this->db = $db;
  }

  /** Обновляем последние время пользователя
  * @param Object user
  * @return Boolean
  */
  public function updateLastAction($user)
  {
    if($user->is_auth === false) return false;
    $time = time();
    $update = $this->db->query("UPDATE `mcr_users` SET `ip_last`='{$user->ip}', `time_last`='{$time}' WHERE `id`='{$user->id}'");
    if(!$update) return false;
    return true;
  }

  /** Добавление лога действия
  * @param Integer user_id
  * @param Integer type (1 - action, 2 - buy, 3 - pay)
  * @param String message (Сообщение пользователю)
  * @return Boolean
  */
  public function add($user_id, $type, $message)
  {
    $time = time();
    $insert = $this->db->query("INSERT INTO `mcr_logs` (`user_id`, `type`, `time`, `message`)
    VALUES ('{$user_id}', '{$type}', '{$time}', '{$message}')");
    if(!$insert) return false;
    return true;
  }

  /** Добавление лога о покупках
  * @param Integer user_id
  * @param Integer type (1 - permission, 2 - item)
  * @param Integer item id (id предмета или id группы)
  * @param Integer server id
  * @param Integer amount (количество)
  * @param Integer price
  * @return Boolean
  */
  public function addBuy($user_id, $type, $item_id, $server_id, $amount, $price)
  {
    $time = time();
    $insert = $this->db->query("INSERT INTO `mcr_logs_buy` (`user_id`, `type`, `item_id`, `server_id`, `amount`, `price`, `time`)
    VALUES ('{$user_id}', '{$type}', '{$item_id}', '{$server_id}', '{$amount}','{$price}', '{$time}')");
    if(!$insert) return false;
    return true;
  }
}



?>
