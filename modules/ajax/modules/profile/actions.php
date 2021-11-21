<?php
if (!defined('MCR')) die ('Hacking Attempt!');

class submodule
{
  private $core, $db, $cfg_m, $user, $lng;

  public function __construct($core)
  {
		$this->core = $core;
		$this->db = $core->db;
		$this->cfg_m = $core->cfg_m;
		$this->user = $core->user;
	}

  private function setPassword()
  {
    $old_password = $this->db->safesql(@$_POST['old_password']);
    $password = $this->db->safesql(@$_POST['password']);
    $password_repeat = $this->db->safesql(@$_POST['password_repeat']);
    if(strlen($password) < 6) return Utils::jsonResponse('Пароль должен состоять минимум из 6 символов!');
    if($password !== $password_repeat) return Utils::jsonResponse('Повторите новый пароль верно!');
    if($this->user->auth->authentificate($old_password, $this->user->password) === false) return Utils::jsonResponse('Неверный пароль!');
    $password = $this->core->gen_password($password);
    $salt = $this->db->safesql($this->core->random());
    $time = time();
    $update = $this->db->query("UPDATE `mcr_users` SET `password`='{$password}', `salt`='{$salt}', `ip_last`='{$this->user->ip}', `time_last`='{$time}' WHERE `id`={$this->user->id}");
    if (!$update) return Utils::jsonResponse('Неизвестная ошибка!');
    $this->core->logger->add($this->user->id, 1, 'Смена пароля');
    return Utils::jsonResponse('Пароль успешно изменен!', true);
  }

  private function giftcode()
  {
    $giftCode = $this->db->safesql(@$_POST['giftcode']);
    $query = $this->db->query("SELECT * FROM `mcr_giftcodes` WHERE `code`='{$giftCode}' LIMIT 1");
    if ($this->db->num_rows($query)<=0) return Utils::jsonResponse('Проверьте правильность веденного гифт кода!');
    $getGift = $query->fetch_assoc();
    if ($getGift['activated'] == 1) return Utils::jsonResponse('Гифт код уже активирован!');
    $update = $this->db->query("UPDATE `mcr_users` SET `realmoney`=`realmoney`+{$getGift['price']} WHERE `id`={$this->user->id}");
    $update_1 = $this->db->query("UPDATE `mcr_giftcodes` SET `activated`=1 WHERE `id`={$getGift['id']}");
    if (!$update || !$update_1) return Utils::jsonResponse('Неизвестная ошибка!');
    $this->core->logger->add($this->user->id, 3, 'Активация гифт кода');
    return Utils::jsonResponse('Гифт код успешно активирован!', true);
  }

  private function buyGroup()
  {
    $group_id = $this->db->safesql(@$_POST['group_id']);
    $server_id = $this->db->safesql(@$_POST['server_id']);

    $query = $this->db->query("SELECT * FROM `mcr_servers` WHERE `id`='{$server_id}' LIMIT 1");
    if ($this->db->num_rows($query)<=0) return Utils::jsonResponse('Сервер не найден!');
    $server = $query->fetch_assoc();

    $query_1 = $this->db->query("SELECT * FROM `mcr_game_groups` WHERE `id`='{$group_id}' LIMIT 1");
    if ($this->db->num_rows($query_1)<=0) return Utils::jsonResponse('Группа не найдена!');
    $group = $query_1->fetch_assoc();

    $query_2 = $this->db->query("SELECT * FROM `mcr_game_groups_buyed` WHERE `user_id`='{$this->user->id}' AND `server_id`='{$server_id}' AND `group_id`='{$group_id}' LIMIT 1");
    if ($this->db->num_rows($query_2) > 0) return Utils::jsonResponse('Вы уже преобрели данную группу!', false, $this->db->num_rows($query_2));

    if ($this->user->realmoney < $group['price']) return Utils::jsonResponse('У вас недостаточно средств!');
    $time = time();
    $expire_time = time() + ((3600 * 24) * 30);
    $update = $this->db->query("UPDATE `mcr_users` SET `realmoney`=`realmoney`-{$group['price']} WHERE `id`={$this->user->id}");
    $insert = $this->db->query("INSERT INTO `mcr_game_groups_buyed` (`user_id`, `server_id`, `group_id`, `buy_time`, `expire_time`) VALUES ('{$this->user->id}', '{$server_id}', '{$group_id}', '{$time}', '{$expire_time}')");

    if (!$update || !$insert) return Utils::jsonResponse('Неизвестная ошибка!');
    $this->core->logger->add($this->user->id, 2, "Покупка группы {$group['title']} на сервере {$server['title']}");
    $this->core->logger->addBuy($this->user->id, 1, $group['id'], $server['id'], 1, $group['price']);

    /*
    * Никита, ебош сам!
    */
    return Utils::jsonResponse("Вы успешно купили группу {$group['title']} на сервере {$server['title']}", true);
  }

  public function content()
  {
    switch ($_POST['action']) {
      case 'update_password':
        return $this->setPassword();
        break;
      case 'giftcode':
        return $this->giftcode();
        break;
      case 'buy_group':
        return $this->buyGroup();
        break;
      default:
        // code...
        break;
    }
    return Utils::jsonResponse('Успех!');
  }

}





?>
