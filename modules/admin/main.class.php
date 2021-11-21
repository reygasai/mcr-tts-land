<?php

if(!defined("MCR")){ exit("Hacking Attempt!"); }

class submodule{
	private $core, $db, $cfg, $user, $lng;

	public function __construct($core){
		$this->core		= $core;
		$this->db		= $core->db;
		$this->cfg		= $core->cfg;
		$this->user		= $core->user;
		$this->lng		= $core->lng_m;

		$bc = array(
			$this->lng['mod_name'] => ADMIN_URL
		);

		$this->core->bc = $this->core->gen_bc($bc);
	}

	private function get_count_users() {
		$users = $this->db->query("SELECT COUNT(`id`) FROM `mcr_users`")->fetch_array();
		return intval($users[0]);
	} 

	private function get_count_day_donations() {
		return 0;
	}

	private function get_count_money_unitpay() {
		return 0;
	}

	private function get_count_servers_online() {
		$online = $this->db->query("SELECT SUM(`online`) FROM `mcr_servers`");

		if(!$online || $this->db->num_rows($online) <= 0) { 
			return 0; 
		}

		$online = $this->db->fetch_array($online);
		return intval($online[0]);
	}

	private function users_stats() {
		$ctables	= $this->cfg->db['tables'];

		$ug_f		= $ctables['ugroups']['fields'];
		$us_f		= $ctables['users']['fields'];

		$query = $this->db->query("SELECT `g`.`{$ug_f['id']}`, `g`.`{$ug_f['title']}`, COUNT(`u`.`{$us_f['id']}`) AS `count`
									FROM `{$this->cfg->tabname('ugroups')}` AS `g`
									LEFT JOIN `{$this->cfg->tabname('users')}` AS `u`
										ON `u`.`{$us_f['group']}`=`g`.`{$ug_f['id']}`
									GROUP BY `g`.`{$ug_f['id']}`");

		if(!$query || $this->db->num_rows($query) <= 0) { 
			return; 
		}

		ob_start();
		while($ar = $this->db->fetch_assoc($query)){

			switch(intval($ar[$ug_f['id']])){
				case 0: $class='danger'; break;
				case 1: $class='warning'; break;
				case 2: $class='success'; break;
				case 3: $class='info'; break;

				default: $class=''; break;
			}

			echo Theme::render("modules/admin/main/users-data-id", [
				"{style_class}" => $class,
				"{title}" => $this->db->HSC($ar[$ug_f['title']]),
				"{count}" => intval($ar['count'])
			]);
		}

		return ob_get_clean();
	}

	private function cms_stats() {
		$get_database_mem = $this->db->query("SELECT SUM((data_length + index_length) / 1024 / 1024) AS 'size' FROM information_schema.TABLES 
											  WHERE table_schema = '{$this->cfg->db['user']}'
											  LIMIT 1")->fetch_array();

		return Theme::render("modules/admin/main/cms-stats", [
			'{site_enabled}' => ($this->cfg->func['close']) ? Theme::render("modules/admin/main/enabled-bandages/off") : Theme::render("modules/admin/main/enabled-bandages/on"),
			'{db_size}'		 => floatval(round($get_database_mem[0], 2)),
			'{php}'			 => phpversion(),
			'{mysqli}'	     => $this->db->server_info
		]);
	}

	public function content(){
		return Theme::render("modules/admin/main/index", [
			'{users}' => $this->get_count_users(),
			'{day_donations}'  => $this->get_count_day_donations(),
			'{unitpay_money}'  => $this->get_count_money_unitpay(),
			'{servers_online}' => $this->get_count_servers_online(),
			'{users_stats}'    => $this->users_stats(),
			'{cms_stats}'	   => $this->cms_stats()
		]);
	}
}

?>