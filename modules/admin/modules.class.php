<?php

if(!defined("MCR")){ exit("Hacking Attempt!"); }

class submodule {
	private $core, $db, $cfg, $user, $lng;

	public function __construct($core) {
		$this->core		= $core;
		$this->db		= $core->db;
		$this->cfg		= $core->cfg;
		$this->user		= $core->user;
		$this->lng		= $core->lng_m;

		if(!$this->core->is_access('sys_adm_modules')) { 
			$this->core->notify($this->core->lng['403'], $this->core->lng['e_403']); 
		}

		$bc = array(
			$this->lng['mod_name'] => ADMIN_URL,
			$this->lng['modules'] => ADMIN_URL."&do=modules"
		);

		$this->core->bc = $this->core->gen_bc($bc);
		
		$this->core->header .= Theme::render("modules/admin/modules/header");
	}

	private function module_array() {
		$list = $this->filter_folder(scandir(MCR_MODE_PATH));

		if(!is_array($list) || count($list) <= 0) { 
			return Theme::render("modules/admin/modules/module-none");
		}

		ob_start();
		foreach($list as $id => $name){
			include(MCR_CONF_PATH.'modules/'.$name.'.php');

			echo Theme::render("modules/admin/modules/module-id", [
				'{status}'	=> (@$cfg['MOD_ENABLE']) ? 'fas fa-thumbs-up' : 'fas fa-ban',
				'{name}'	=> $this->db->HSC($name),
				'{title}'	=> $this->db->HSC(@$cfg['MOD_TITLE']),
				'{author}'	=> $this->db->HSC(@$cfg['MOD_AUTHOR']),
				'{version}' => $this->db->HSC(@$cfg['MOD_VERSION']),
			]);
		}

		return ob_get_clean();
	}

	//Переписать
	private function filter_folder($array) {
		$filtered = array();

		foreach($array as $key => $value){
			if($value=='..' || $value=='.') { continue; }
			if(is_dir(MCR_MODE_PATH.$value)){ continue; }
			if(!file_exists(MCR_CONF_PATH.'modules/'.$value)){ continue; }

			$expl = explode('.', $value);

			if(count($expl)!=2 || !isset($expl[1]) || $expl[1]!='php'){ continue; }

			$filtered[] = $expl[0];
		}

		return $filtered;
	}

	private function edit() {
		if(!$this->core->is_access('sys_adm_modules_edit')) { 
			$this->core->notify($this->core->lng["e_msg"], $this->core->lng['e_403'], 2, '?mode=admin&do=modules'); 
		}

		$name = @$_GET['id'];

		if(!file_exists(MCR_CONF_PATH.'modules/'.$name.'.php')) { 
			$this->core->notify($this->core->lng["e_msg"], $this->lng['mod_cfg_not_found'], 2, '?mode=admin&do=modules'); 
		}

		require(MCR_CONF_PATH.'modules/'.$name.'.php');
		if(!$this->core->check_cfg($cfg)){
			$this->core->notify($this->core->lng["e_msg"], $this->lng['mod_cfg_incorrect'], 2, '?mode=admin&do=modules');
		}

		$bc = array(
			$this->lng['mod_name'] => ADMIN_URL."",
			$this->lng['modules'] => ADMIN_URL."&do=modules",
			$this->lng['mod_edit'] => ADMIN_URL."&do=modules&op=edit&id=$name"
		);

		$this->core->bc = $this->core->gen_bc($bc);

		if($_SERVER['REQUEST_METHOD']=='POST') {
			$cfg['MOD_ENABLE'] = (intval(@$_POST['status']) == 1) ? true : false;
			$cfg['MOD_CHECK_UPDATE'] = (intval(@$_POST['updates']) == 1) ? true : false;
			$cfg['MOD_URL_UPDATE'] = $this->core->safestr(@$_POST['update_url']);

			if(!$this->cfg->savecfg($cfg, 'modules/'.$name.'.php', 'cfg')){
				$this->core->notify($this->core->lng["e_msg"], $this->lng['mod_cfg_unsave'], 3, '?mode=admin&do=modules');
			}
		}

		return Theme::render("modules/admin/modules/module-add", [
			"{page}"			=> $this->lng['mod_edit_page_name'],
			"{status}"		=> ($cfg['MOD_ENABLE']) ? 'selected' : '',
			"{name}"			=> $this->db->HSC($cfg['MOD_TITLE']),
			"{desc}"			=> $this->db->HSC($cfg['MOD_DESC']),
			"{author}"		=> $this->db->HSC($cfg['MOD_AUTHOR']),
			"{site}"			=> $this->db->HSC($cfg['MOD_SITE']),
			"{email}"			=> $this->db->HSC($cfg['MOD_EMAIL']),
			"{version}"		=> $this->db->HSC($cfg['MOD_VERSION']),
		]);
	}

	public function content(){
		$op = (isset($_GET['op'])) ? $_GET['op'] : 'list';

		switch($op){
			case 'edit':	$content = $this->edit(); break;

			default:		$content = Theme::render("modules/admin/modules/module-list", [
				'{modules}' => $this->module_array()
			]);
			break;
		}

		return $content;
	}
}

?>