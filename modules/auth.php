<?php

if(!defined("MCR")){ exit("Hacking Attempt!"); }

class module {
	private $core, $db, $user, $cfg, $lng;

	public function __construct($core) {
		$this->core		= $core;
		$this->db		= $core->db;
		$this->user		= $core->user;
		$this->cfg		= $core->cfg;
		$this->lng		= $core->lng_m;

		$bc = array(
			$this->lng['mod_name'] => BASE_URL."?mode=auth"
		);

		$this->core->bc = $this->core->gen_bc($bc);

		$this->core->layout = 'onepage';
	}

	private function auth_data_update($user_id, $attempt = 1, $time = 0) {
		$ctables	= $this->cfg->db['tables'];
		$ug_f		= $ctables['ugroups']['fields'];
		$us_f		= $ctables['users']['fields'];

		$user_id = intval($user_id);

		$data = json_encode([
			'time'    => intval($time),
			'attempt' => intval($attempt)
		]);

		$update = $this->db->query("UPDATE `{$this->cfg->tabname('users')}`
			SET `{$us_f['auth_attempt_data']}` = '$data'
			WHERE `{$us_f['id']}`='$user_id'
			LIMIT 1");

		if(!$update) {
			$this->core->notify($this->core->lng['e_attention'], $this->core->lng['e_sql_critical']);
		}
	}

	private function handler() {
		if($_SERVER['REQUEST_METHOD'] != 'POST') {
			$this->core->notify('Hacking Attempt!');
		}

		$auth_data = $this->user->auth->check_auth_data(@$_POST['login'], @$_POST['password']);
		if($auth_data === false && !is_array($auth_data)) {
			$this->core->notify($this->core->lng["e_msg"], $this->lng['e_wrong_pass'], 2, 'auth');
		}

		$login = $this->db->safesql($auth_data['login']);
		$password = $this->db->safesql($auth_data['password']);
		$remember = (isset($_POST['remember']) && intval($_POST['remember']) === 1) ? true : false;

		$ctables	= $this->cfg->db['tables'];
		$ug_f		= $ctables['ugroups']['fields'];
		$us_f		= $ctables['users']['fields'];

		$query = $this->db->query("SELECT `u`.`{$us_f['id']}`, `u`.`{$us_f['pass']}`, `u`.`{$us_f['salt']}`, `u`.`{$us_f['auth_attempt_data']}`,
											`g`.`{$ug_f['perm']}`
									FROM `{$this->cfg->tabname('users')}` AS `u`
									INNER JOIN `{$this->cfg->tabname('ugroups')}` AS `g`
										ON `g`.`{$ug_f['id']}`=`u`.`{$us_f['group']}`
									WHERE `u`.`{$us_f['login']}`='$login'
									LIMIT 1");

		if(!$query || $this->db->num_rows($query) <= 0) {
			$this->core->notify($this->core->lng["e_msg"], $this->lng['e_wrong_pass'], 2, 'auth');
		}

		$ar = $this->db->fetch_assoc($query);
		$uid = intval($ar[$us_f['id']]);

		$permissions = json_decode($ar[$ug_f['perm']], true);
		if(!@$permissions['sys_auth']) {
			$this->core->notify($this->core->lng['403'], $this->lng['e_access'], 2, 'page/403');
		}

		$auth_attempt_time = $auth_attempt = 0;
		$auth_attempt_data = $ar[$us_f['auth_attempt_data']];
		$auth_attempt_data = (empty($auth_attempt_data)) ? null : json_decode($auth_attempt_data, true);

		if(is_array($auth_attempt_data)) {
			$auth_attempt_time = intval($auth_attempt_data['time']);
			$auth_attempt	   = intval($auth_attempt_data['attempt']);
		}

		if($auth_attempt_time > time()) {
			$this->core->notify($this->core->lng["e_msg"], str_replace('{TIME}', $this->cfg->main['auth_time_attempt']/60, $this->lng['e_block_auth_time']));
		} elseif((time() > $auth_attempt_time) && ($auth_attempt_time !== 0) && ($auth_attempt >= $this->cfg->main['auth_attempt'])) {
			$auth_attempt_time = $auth_attempt = 0;
			$this->auth_data_update($uid, $auth_attempt, $auth_attempt_time);
		}

		if(!$this->user->auth->authentificate($password, $ar[$us_f['pass']])) {
			if($auth_attempt + 1 >= $this->cfg->main['auth_attempt']) {
				$this->auth_data_update($uid, $this->cfg->main['auth_attempt'], time() + intval($this->cfg->main['auth_time_attempt']));
			} else {
				$this->auth_data_update($uid, $auth_attempt + 1);
			}

			$this->core->notify($this->core->lng["e_msg"], $this->lng['e_wrong_pass'], 2, 'auth');
		}

		$time = time();
		$new_tmp = $this->db->safesql($this->user->auth->createTmp());
		$new_ip = $this->user->ip;

		$update = $this->db->query("UPDATE `{$this->cfg->tabname('users')}`
									SET `{$us_f['tmp']}`='$new_tmp', `{$us_f['ip_last']}`='$new_ip', `{$us_f['date_last']}`='$time', `{$us_f['auth_attempt_data']}` = ''
									WHERE `{$us_f['id']}`='$uid'
									LIMIT 1");

		if(!$update){ $this->core->notify($this->core->lng['e_attention'], $this->core->lng['e_sql_critical']); }

		$new_hash = $uid.$new_tmp.$new_ip.md5($this->cfg->main['mcr_secury']);
		$new_hash = $uid.'_'.md5($new_hash);
		$safetime = ($remember) ? 3600*24*30+time() : time()+3600;
		setcookie("mcr_user", $new_hash, $safetime, '/');

		// Лог действия
		$this->core->logger->add($uid, 1, $this->lng['log_auth']);

		$this->core->notify($this->core->lng['e_success'], $this->lng['e_success'], 3);
	}

	public function content() {
		if($this->user->is_auth) $this->core->notify('', $this->lng["e_already"], 1);
    if ($_SERVER['REQUEST_METHOD'] == 'POST') return $this->handler();
		$this->core->header .= Theme::render("modules/auth/headers");
		return Theme::render("modules/auth/index");
	}

}

?>
