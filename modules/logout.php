<?php

if(!defined("MCR")){ exit("Hacking Attempt!"); }

class module {
	private $core, $db, $user, $lng, $cfg;

	public function __construct($core){
		$this->core		= $core;
		$this->db		= $core->db;
		$this->user		= $core->user;
		$this->cfg		= $core->cfg;
		$this->lng		= $core->lng_m;
	}

	public function content() {
		if($_SERVER['REQUEST_METHOD'] != 'POST') { 
			$this->core->notify('Hacking Attempt!'); 
		}

		if(!$this->user->is_auth) { 
			$this->core->notify($this->core->lng['403'], $this->lng['e_not_auth'], 1, 'page/403'); 
		}

		// Лог действия
		$this->core->logger->add($this->user->id, 1, $this->lng['log_logout']);

		$new_tmp = $this->db->safesql($this->core->random(16));
		$ctables	= $this->cfg->db['tables'];
		$us_f		= $ctables['users']['fields'];
		$time = time();

		$update = $this->db->query("UPDATE `{$this->cfg->tabname('users')}` SET `{$us_f['tmp']}`='$new_tmp', `{$us_f['date_last']}`='$time' WHERE `{$us_f['id']}`='{$this->user->id}' LIMIT 1");

		if(!$update) { 
			$this->core->notify($this->core->lng['e_attention'], $this->core->lng['e_sql_critical']); 
		}
	
		setcookie("mcr_user", "", $time-3600, '/');

		$this->core->notify('', '', 1);
	}

}

?>
