<?php

if(!defined("MCR")){ exit("Hacking Attempt!"); }

class module{
	private $core, $db, $cfg, $lng, $user;

	public function __construct($core) {
		$this->core		= $core;
		$this->db		= $core->db;
		$this->cfg		= $core->cfg;
		$this->user		= $core->user;
		$this->lng		= $core->lng_m;

		$bc = array(
			$this->lng['mod_name'] => BASE_URL."restore"
		);

		$this->core->bc = $this->core->gen_bc($bc);

		$this->core->layout = 'onepage';
	}

	private function check_exist($value = '', $email = false) {
		$ctables	= $this->cfg->db['tables'];
		$us_f		= $ctables['users']['fields'];

		$selector = (!$email) ? "`{$us_f['login']}`='$value'" : "`{$us_f['email']}`='$value'";

		$query = $this->db->query("SELECT COUNT(*) FROM `{$this->cfg->tabname('users')}` WHERE $selector");

		if(!$query) { return true; }

		$ar = $this->db->fetch_array($query);

		if($ar[0] > 0) { return true; }

		return false;
	}

	private function send() {
		if($_SERVER['REQUEST_METHOD']=='POST') {

			$_SESSION['m_send_id'] = (isset($_SESSION['m_send_id'])) ? $_SESSION['m_send_id']+1 : 1;

			if($_SESSION['m_send_id'] > 5) {
				$this->core->notify($this->core->lng['e_msg'], $this->lng['e_limit'], 1, "restore");
			}

			$email = $this->db->safesql(@$_POST['email']);

			if(empty($email)) {
				$this->core->notify($this->core->lng['e_msg'], $this->lng['invalid_email'], 1, "restore");
			}

			$ctables	= $this->cfg->db['tables'];
			$us_f		= $ctables['users']['fields'];

			$query = $this->db->query("SELECT `id`, `tmp` FROM `mcr_users` WHERE `email`='$email'");

			if(!$query || $this->db->num_rows($query) <= 0) {
				$this->core->notify($this->core->lng['e_msg'], $this->lng['email_not_found'], 1, "restore");
			}

			$ar = $this->db->fetch_assoc($query);

			$id = intval($ar['id']);
			$tmp = md5($ar['tmp']);

			$message = Theme::render("modules/restore/mail", [
				'{link}'     => $this->cfg->main['s_root_full'].BASE_URL.'?mode=restore&op=accept&key='.$id.'_'.$tmp,
				'{sitename}' => $this->cfg->main['s_name'],
			]);

			if(!$this->core->send_mail($email, $this->lng['email_title'], $message)) {
				$this->core->notify($this->core->lng['e_msg'], $this->core->lng['e_critical'], 1, "restore");
			}

			$this->core->logger->add($id, 1, "Отправка запроса на сброс пароля");

			$this->core->notify('', $this->lng['e_success'], 3);
		}

		return Theme::render("modules/restore/index");
	}

	private function accept() {
		if(!isset($_GET['key'])) {
			$this->core->notify($this->core->lng['e_msg'], $this->core->lng['e_403'], 2, 'page/403');
		}

		$key_string = $_GET['key'];

		$array = explode("_", $key_string);

		if(count($array)!==2) {
			$this->core->notify($this->core->lng['e_msg'], $this->core->lng['e_403'], 2, 'page/403');
		}

		$uid = intval($array[0]);

		$key = $array[1];

		$ctables	= $this->cfg->db['tables'];
		$us_f		= $ctables['users']['fields'];

		$query = $this->db->query("SELECT `{$us_f['tmp']}` FROM `{$this->cfg->tabname('users')}` WHERE `{$us_f['id']}`='$uid'");

		if(!$query || $this->db->num_rows($query) <= 0){
			$this->core->notify($this->core->lng['e_attention'], $this->core->lng['e_sql_critical'], 1, "restore");
		}

		$ar = $this->db->fetch_assoc($query);

		if($key !== md5($ar[$us_f['tmp']])) {
			$this->core->notify($this->core->lng['e_msg'], $this->core->lng['e_403'], 2, 'page/403');
		}

		if($_SERVER['REQUEST_METHOD']=='POST') {
			$newpass = @$_POST['newpass'];

			if(mb_strlen($newpass, "UTF-8") < 6) {
				$this->core->notify($this->core->lng['e_msg'], $this->lng['e_pass_length'], 2, '?mode=restore&op=accept&key='.$key_string);
			}

			$tmp = $this->db->safesql($this->core->random(16));
			$salt = $this->db->safesql($this->core->random());
			$password = $this->core->gen_password($newpass, $salt);
			$time = time();

			$update = $this->db->query("UPDATE `{$this->cfg->tabname('users')}`
										SET `{$us_f['pass']}`='$password', `{$us_f['salt']}`='$salt', `{$us_f['tmp']}`='$tmp', `{$us_f['ip_last']}`='{$this->user->ip}', `{$us_f['date_last']}`='$time'
										WHERE `{$us_f['id']}`='$uid'");

			if(!$update) {
				$this->core->notify($this->core->lng['e_attention'], $this->core->lng['e_sql_critical'], 1, "restore");
			}

			// Лог действия
			$this->core->logger->add($uid, 1, "Сброс пароля");
			$this->core->notify($this->core->lng['e_success'], $this->lng['e_success2'], 3);
		}

		return Theme::render("modules/restore/new");
	}

	public function content() {
		if($this->user->is_auth) {
			$this->core->notify($this->core->lng['e_msg'], $this->core->lng['e_403'], 2, 'page/403');
		}

		if(!$this->core->is_access('sys_restore')) {
			$this->core->notify($this->core->lng['e_msg'], $this->lng['e_perm'], 1, "page/403");
		}

		$this->core->header .= Theme::render("modules/restore/headers");

		$op = (isset($_GET['op'])) ? $_GET['op'] : false;
		switch($op){
			case 'accept': $content = $this->accept(); break;
			default: $content = $this->send(); break;
		}

		return $content;
	}

}

?>
