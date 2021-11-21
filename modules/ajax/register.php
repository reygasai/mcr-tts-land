<?php

if(!defined("MCR")){ exit("Hacking Attempt!"); }

class submodule{
	private $core, $db, $cfg, $user, $lng;

	public function __construct($core){
		$this->core		= $core;
		$this->db		= $core->db;
		$this->cfg		= $core->cfg;
		$this->user		= $core->user;
		$this->lng		= $core->load_language('register');
	}

	private function count_ip(){
		$query = $this->db->query("SELECT COUNT(*) FROM `mcr_users` WHERE `ip_create`='{$this->user->ip}' OR `ip_last`='{$this->user->ip}'");
		if(!$query) return 0;
		$ar = $this->db->fetch_array($query);
		return $ar[0];
	}

	public function content(){

		if($_SERVER['REQUEST_METHOD']!='POST') $this->core->js_notify($this->core->lng['e_hack']);
		if($this->user->is_auth) $this->core->js_notify($this->lng['e_already']);
		if(intval($_POST['rules'])!==1) $this->core->js_notify($this->lng['e_rules']);
		if(intval(@$this->cfg->func['ipreglimit'])>0 && $this->count_ip()>=intval(@$this->cfg->func['ipreglimit'])) $this->core->js_notify($this->lng['e_reg_limit']);

		$login = $this->db->safesql(@$_POST['login']);
		$email = $this->db->safesql(@$_POST['email']);
		$uuid = $this->db->safesql($this->user->logintouuid(@$_POST['login']));
		$password = @$_POST['password'];

		if(!preg_match("/^[\w\-]{3,}$/i", $login)) $this->core->js_notify($this->lng['e_login_regexp']);
		if(!filter_var($email, FILTER_VALIDATE_EMAIL)) $this->core->js_notify($this->lng['e_email_regexp']);

		if($login == 'default') $this->core->js_notify($this->lng['e_exist']);

		$query = $this->db->query("SELECT COUNT(*) FROM `mcr_users` WHERE `login`='$login' OR `email`='$email'");
		if(!$query) $this->core->js_notify($this->core->lng['e_sql_critical']);
		$ar = $this->db->fetch_array($query);

		if($ar[0]>0) $this->core->js_notify($this->lng['e_exist']);

		if(mb_strlen($password, "UTF-8") < 6) $this->core->js_notify($this->lng['e_pass_length']);
		if($password !== @$_POST['repassword']) $this->core->js_notify($this->lng['e_pass_match']);

		if(!$this->core->captcha_check()){ $this->core->js_notify($this->core->lng['e_captcha']); }

		$tmp = $this->db->safesql($this->core->random(16));
		$salt = $this->db->safesql($this->core->random());

		$password = $this->db->safesql($password);
		$password = $this->core->gen_password($password);

		$ip = $this->user->ip;
		$time = time();

		$gid = ($this->cfg->main['reg_accept']) ? 1 : 2;

		$notify_message = $this->core->lng['e_success'];

		// Лог действия
		//$this->db->actlog($this->lng['log_reg'], $id);

		if($this->cfg->main['reg_accept']){
			$message = Theme::render("modules/register/mail", [
				'{link}' => $this->cfg->main['s_root_full'].BASE_URL.'?mode=register&op=accept&key='.$id.'_'.md5($salt),
				'{sitename}' => $this->cfg->main['s_name'],
				'{siteurl}' => $this->cfg->main['s_root_full'].BASE_URL,
			]);
			if(!$this->core->send_mail($email, $this->lng['msg_title'], $message)) $this->core->js_notify($this->core->lng['e_mail_send']);
		}

		$insert = $this->db->query("INSERT INTO `mcr_users` (`gid`, `login`, `email`, `password`, `salt`, `uuid`, `tmp`, `ip_create`, `ip_last`, `time_create`, `time_last`)
		VALUES ('$gid', '$login', '$email', '$password', '$salt', '$uuid', '$tmp', '$ip', '$ip', '$time', '$time')");

		if(!$insert) $this->core->js_notify($this->core->lng['e_sql_critical']);
		$id = $this->db->insert_id();

		$this->core->js_notify($this->lng[$this->cfg->main['reg_accept'] ? 'e_success_mail' : 'e_success'], $this->core->lng['e_success'], true);
	}

}

?>
