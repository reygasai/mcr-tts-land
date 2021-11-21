<?php

if(!defined("MCR")){ exit("Hacking Attempt!"); }

class module{
	private $core, $db, $cfg, $lng, $user;

	public function __construct($core){
		$this->core		= $core;
		$this->db		= $core->db;
		$this->user		= $core->user;
		$this->cfg		= $core->cfg;
		$this->lng		= $core->lng_m;

		$bc = array(
			$this->lng['mod_name'] => BASE_URL."register"
		);

		$this->core->bc = $this->core->gen_bc($bc);

		$this->core->layout = 'onepage';
	}

	private function accept(){
		if(!isset($_GET['key'])){ $this->core->notify($this->core->lng['e_msg'], $this->core->lng['e_403'], 2, 'page/403'); }

		$key_string = $_GET['key'];

		$array = explode("_", $key_string);

		if(count($array)!==2){ $this->core->notify($this->core->lng['e_msg'], $this->core->lng['e_403'], 2, 'page/403'); }

		$uid = intval($array[0]);

		$key = $array[1];

		$ctables	= $this->cfg->db['tables'];
		$us_f		= $ctables['users']['fields'];

		$query = $this->db->query("SELECT `{$us_f['salt']}` FROM `{$this->cfg->tabname('users')}` WHERE `{$us_f['id']}`='$uid' AND `{$us_f['group']}`='1'");

		if(!$query || $this->db->num_rows($query)<=0){ $this->core->notify($this->core->lng['e_attention'], $this->core->lng['e_sql_critical'], 1, "register"); }

		$ar = $this->db->fetch_assoc($query);

		if($key!==md5($ar['salt'])){ $this->core->notify($this->core->lng['e_msg'], $this->core->lng['e_403'], 2, 'page/403'); }

		$time = time();

		$update = $this->db->query("UPDATE `{$this->cfg->tabname('users')}`
									SET `{$us_f['group']}`='2', `{$us_f['ip_last']}`='{$this->user->ip}', `{$us_f['date_last']}`='$time'
									WHERE `{$us_f['id']}`='$uid' AND `{$us_f['group']}`='1'");

		if(!$update){ $this->core->notify($this->core->lng['e_attention'], $this->core->lng['e_sql_critical'], 1, "register"); }

		// Лог действия
		$this->core->logger->add($uid, 1, "Подтверждение регистрации");

		$this->core->notify($this->core->lng['e_success'], $this->lng['e_accept'], 3);
	}

	public function content(){
		if($this->user->is_auth){
			$this->core->notify($this->core->lng['e_msg'], $this->lng['e_already'], 2, 'page/403');
		}

		$op = (isset($_GET['op'])) ? $_GET['op'] : false;

		switch($op){
			case 'accept':
				$content = $this->accept();
			break;

			default:
				$this->core->header .= Theme::render("modules/register/headers");
				$content = Theme::render("modules/register/index");
			break;
		}

		return $content;
	}

}

?>
