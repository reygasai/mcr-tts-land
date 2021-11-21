<?php

if(!defined("MCR")){ exit("Hacking Attempt!"); }

class module {
	private $core, $db, $cfg, $user, $lng;

	public function __construct($core){
		$this->core		= $core;
		$this->db		= $core->db;
		$this->cfg		= $core->cfg;
		$this->user		= $core->user;
		$this->lng		= $core->lng_m;

		$this->core->bc = $this->core->gen_bc([$this->lng['mod_name'] => BASE_URL."profile"]);
		$this->core->layout = 'singlepage';
	}

	private function delete_skin() {
		if(!$this->user->is_skin) {
			$this->core->notify("", $this->lng['skin_not_set'], 1, 'profile');
		}

		if(file_exists(MCR_SKIN_PATH.$this->user->skin.'.png')) {
			unlink(MCR_SKIN_PATH.$this->user->skin.'.png');
		}

		if(file_exists(MCR_SKIN_PATH.'interface/'.$this->user->skin.'.png')) {
			unlink(MCR_SKIN_PATH.'interface/'.$this->user->skin.'.png');
		}

		if(file_exists(MCR_SKIN_PATH.'interface/'.$this->user->skin.'_mini.png')) {
			unlink(MCR_SKIN_PATH.'interface/'.$this->user->skin.'_mini.png');
		}

		if($this->user->is_cloak) {
			$cloak = array(
				"tmp_name" => MCR_CLOAK_PATH.$this->user->cloak.'.png',
				"size" => filesize(MCR_CLOAK_PATH.$this->user->cloak.'.png'),
				"error" => 0,
				"name" => $this->user->cloak.'.png'
			);
			require_once(MCR_TOOL_PATH.'cloak.class.php');
			$cloak = new cloak($this->core, $cloak);
		}

		$ctables	= $this->cfg->db['tables'];
		$us_f		= $ctables['users']['fields'];

		$update = $this->db->query("UPDATE `{$this->cfg->tabname('users')}` SET `{$us_f['is_skin']}`='0' WHERE `{$us_f['id']}`='{$this->user->id}'");
		if(!$update) {
			$this->core->notify($this->core->lng['e_attention'], $this->core->lng['e_sql_critical']);
		}

		$this->core->notify($this->core->lng['e_success'], $this->lng['skin_success_del'], 3, 'profile');
	}

	private function delete_cloak() {
		if(!$this->user->is_cloak) {
			$this->core->notify("", $this->lng['cloak_not_set'], 1, 'profile');
		}

		if(file_exists(MCR_CLOAK_PATH.$this->user->login.'.png')) {
			unlink(MCR_CLOAK_PATH.$this->user->login.'.png');
		}

		if(!$this->user->is_skin) {
			unlink(MCR_SKIN_PATH.'interface/'.$this->user->login.'.png');
			unlink(MCR_SKIN_PATH.'interface/'.$this->user->login.'_mini.png');
		} else {
			$skin = array(
				"tmp_name" => MCR_SKIN_PATH.$this->user->login.'.png',
				"size" => filesize(MCR_SKIN_PATH.$this->user->login.'.png'),
				"error" => 0,
				"name" => $this->user->login.'.png'
			);
			require_once(MCR_TOOL_PATH.'skin.class.php');
			$skin = new skin($this->core, $skin);
		}

		$ctables	= $this->cfg->db['tables'];
		$us_f		= $ctables['users']['fields'];

		$update = $this->db->query("UPDATE `{$this->cfg->tabname('users')}` SET `{$us_f['is_cloak']}`='0' WHERE `{$us_f['id']}`='{$this->user->id}'");
		if(!$update) {
			$this->core->notify($this->core->lng['e_attention'], $this->core->lng['e_sql_critical']);
		}

		$this->core->notify($this->core->lng['e_success'], $this->lng['cloak_success_del'], 3, 'profile');
	}

	private function upload_skin(){
		require_once(MCR_TOOL_PATH.'skin.class.php');
		$skin = new skin($this->core, $_FILES['skin']); // create new skin in folder

		if($this->user->is_cloak){
			$cloak = array(
				"tmp_name" => MCR_CLOAK_PATH.$this->user->login.'.png',
				"size" => (!file_exists(MCR_CLOAK_PATH.$this->user->login.'.png')) ? 0 : filesize(MCR_CLOAK_PATH.$this->user->login.'.png'),
				"error" => 0,
				"name" => $this->user->login.'.png'
			);
			require_once(MCR_TOOL_PATH.'cloak.class.php');
			$cloak = new cloak($this->core, $cloak);
		}

		$ctables	= $this->cfg->db['tables'];
		$us_f		= $ctables['users']['fields'];

		$update = $this->db->query("UPDATE `{$this->cfg->tabname('users')}` SET `{$us_f['is_skin']}`='1' WHERE `{$us_f['id']}`='{$this->user->id}'");

		if(!$update){ $this->core->notify($this->core->lng['e_attention'], $this->core->lng['e_sql_critical']); }

		$this->core->notify($this->core->lng['e_success'], $this->lng['skin_success_edit'], 3, 'profile');
	}

	private function upload_cloak(){
		require_once(MCR_TOOL_PATH.'cloak.class.php');
		$cloak = new cloak($this->core, $_FILES['cloak']); // create new cloak in folder

		$ctables	= $this->cfg->db['tables'];
		$us_f		= $ctables['users']['fields'];

		$update = $this->db->query("UPDATE `{$this->cfg->tabname('users')}` SET `{$us_f['is_cloak']}`='1' WHERE `{$us_f['id']}`='{$this->user->id}'");

		if(!$update){ $this->core->notify($this->core->lng['e_attention'], $this->core->lng['e_sql_critical']); }

		$this->core->notify($this->core->lng['e_success'], $this->lng['cloak_success_edit'], 3, 'profile');
	}

	private function displayServers()
	{
		$query = $this->db->query("SELECT * FROM `mcr_servers`");
		if ($this->db->num_rows($query)<= 0) return;
		ob_start();
		while ($server = $query->fetch_assoc()) {
			echo Theme::render('modules/profile/server_id', [
				'{server_id}' => $server['id'],
				'{title}' => $server['title'],
				'{description}' => $server['description'],
				'{image_src}' => $server['cover']
			]);
		}
		return ob_get_clean();
	}

	private function displayGroups()
	{
		$query = $this->db->query("SELECT * FROM `mcr_game_groups`");
		if ($this->db->num_rows($query)<= 0) return;
		ob_start();
		while ($group = $query->fetch_assoc()) {
			echo Theme::render('modules/profile/group_id', [
				'{group_id}' => $group['id'],
				'{server_id}' => $group['server_id'],
				'{title}' => $group['title'],
				'{description}' => $group['description'],
				'{price}' => $group['price']
			]);
		}
		return ob_get_clean();
	}

	public function content(){
		if(!$this->user->is_auth) {
			$this->core->notify($this->core->lng['e_403'], $this->lng['auth_required'], 1, "page/403");
		}

		if(!$this->core->is_access('sys_profile')) {
			$this->core->notify($this->core->lng['e_403'], $this->lng['access_by_admin'], 1, "page/403");
		}

		$this->core->header .= Theme::render("modules/profile/headers");

		if($_SERVER['REQUEST_METHOD']=='POST'){
			if(isset($_POST['del-skin'])){
				if(!$this->core->is_access('sys_profile_del_skin')){ $this->core->notify($this->core->lng['e_403'], $this->lng['skin_access_by_admin'], 1, "page/403"); }
				$this->delete_skin();
			}elseif(isset($_POST['del-cloak'])){
				if(!$this->core->is_access('sys_profile_del_cloak')){ $this->core->notify($this->core->lng['e_403'], $this->lng['cloak_access_by_admin'], 1, "page/403"); }
				$this->delete_cloak();
			}elseif(isset($_FILES['skin'])){
				if(!$this->core->is_access('sys_profile_skin')){ $this->core->notify($this->core->lng['e_403'], $this->lng['skin_edit_by_admin'], 1, "page/403"); }
				$this->upload_skin();
			}elseif(isset($_FILES['cloak'])){
				if(!$this->core->is_access('sys_profile_cloak')){ $this->core->notify($this->core->lng['e_403'], $this->lng['cloak_edit_by_admin'], 1, "page/403"); }
				$this->upload_cloak();
			}elseif(isset($_POST['settings'])){
				if(!$this->core->is_access('sys_profile_settings')){ $this->core->notify($this->core->lng['e_403'], $this->lng['set_save_by_admin'], 1, "page/403"); }
				$this->settings();
			}else{
				$this->core->notify('', '', 3, '?mode=profile');
			}
		}

		return Theme::render("modules/profile/index", [
			'{profile_header_username}' => SKIN_URL.'interface/'.$this->user->skin.'_mini.png?'.mt_rand(1000,9999),
			'{profile_dataskin}'		=> SKIN_URL.''.$this->user->skin.'.png?'.mt_rand(1000,9999),
			'{profile_datacloak}'		=> CLOAK_URL.''.$this->user->skin.'.png?'.mt_rand(1000,9999),
			'{profile_regdate}'			=> $this->user->time_create,
			'{profile_actdate}'			=> $this->user->time_last,
			'{profile_lastserver}'		=> '',
			'{profile_lastip}'			=> $this->user->ip,
			'{profile_usermoney}'		=> $this->user->realmoney,
			'{profile_servers}' => $this->displayServers(),
			'{profile_groups}' => $this->displayGroups()
		]);
	}
}

?>
