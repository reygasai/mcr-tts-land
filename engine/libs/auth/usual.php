<?php

if(!defined("MCR")){ exit("Hacking Attempt!"); }

class auth{
	private $core, $db, $user, $cfg, $lng;

	public function __construct($core){
		$this->core		= $core;
		$this->db		= $core->db;
		$this->user		= $core->user;
		$this->cfg		= $core->cfg;
		$this->lng		= $core->lng_m;
	}

	public function createTmp(){
		return $this->core->random(16);
	}

	public function createHash($password) {
		return $this->core->gen_password($password);
	}

	public function authentificate($user_password, $password) {
		return password_verify($user_password, $password);
	}

	public function check_auth_data($login, $password) {
		if(empty($login)) {
			return false;
		}

		$login = $this->db->safesql($login);
		if(!preg_match("/^[\w\-]{3,}$/i", $login)) { 
			return false; 
		}

		if(empty($password)) {
			return false;
		}

		$password = $this->db->safesql($password);

		return [
			'login' => $login,
			'password' => $password
		];
	}
}

?>