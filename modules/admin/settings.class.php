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

		if(!$this->core->is_access('sys_adm_settings'))	{ 
			$this->core->notify($this->core->lng['403'], $this->core->lng['e_403']); 
		}

		$bc = array(
			$this->lng['mod_name'] => ADMIN_URL,
			$this->lng['settings'] => ADMIN_URL."&do=settings"
		);

		$this->core->bc = $this->core->gen_bc($bc);

		$this->core->header .= Theme::render("modules/admin/settings/header");
	}

	private function captcha($select=0) {
		$select = intval($select);

		ob_start();
		foreach($this->core->captcha as $key => $value){
			$selected = ($key == $select) ? 'selected' : '';
			echo '<option value="'.$key.'" '.$selected.'>'.$value.'</option>';
		}

		return ob_get_clean();
	}

	private function main() {
		$cfg = $this->cfg->main;

		if($_SERVER['REQUEST_METHOD'] == 'POST') {
			$cfg['s_name']			= $this->core->safestr(@$_POST['s_name']);
			$cfg['s_about']			= $this->core->safestr(@$_POST['s_about']);
			$cfg['s_keywords']		= $this->core->safestr(@$_POST['s_keywords']);
			$cfg['s_dpage']		= $this->core->safestr(@$_POST['s_dpage']);
			$this->cfg->db['log']	= (intval(@$_POST['log']) === 1) ? true : false;
			$cfg['debug']			= (intval(@$_POST['debug']) === 1) ? true : false;
			$cfg['reg_accept']		= (intval(@$_POST['reg_accept']) === 1) ? true : false;
			$cfg['captcha']			= intval(@$_POST['captcha']);
			$cfg['rc_public']		= $this->core->safestr(@$_POST['rc_public']);
			$cfg['rc_private']		= $this->core->safestr(@$_POST['rc_private']);
			$cfg['auth_attempt']	  = intval(@$_POST['auth_attempt']);
			$cfg['auth_time_attempt'] = intval(@$_POST['auth_time_attempt']);
			$cfg['vk_link'] 		  = $this->core->safestr(@$_POST['vk_link']);
			$cfg['discord_link'] 	  = $this->core->safestr(@$_POST['discord_link']);
			$cfg['youtube_link'] 	  = $this->core->safestr(@$_POST['youtube_link']);
			$cfg['jar'] 	  		  = $this->core->safestr(@$_POST['jar']);
			$cfg['exe'] 	  		  = $this->core->safestr(@$_POST['exe']);

			if(!$this->cfg->savecfg($cfg)) { 
				$this->core->notify($this->core->lng["e_msg"], $this->lng['set_e_cfg_save'], 2, '?mode=admin&do=settings'); 
			}
			
			if(!$this->cfg->savecfg($this->cfg->db, 'db.php', 'db')) { 
				$this->core->notify($this->core->lng["e_msg"], $this->lng['set_e_cfg_save'], 2, '?mode=admin&do=settings'); 
			}
			
			$this->core->notify($this->core->lng["e_success"], $this->lng['set_save_success'], 3, '?mode=admin&do=settings');
		}

		return Theme::render("modules/admin/settings/main", [
			"{log}"			=> ($this->cfg->db['log']) ? 'selected' : '',
			"{debug}"		=> ($cfg['debug']) ? 'selected' : '',
			"{reg_accept}"	=> ($cfg['reg_accept']) ? 'selected' : '',
			"{captcha}"		=> $this->captcha($cfg['captcha']),
			"{name_web}"	=> $cfg['s_name'],
			"{about_web}"	=> $cfg['s_about'],
			"{keywords_web}" => $cfg['s_keywords'],
			"{rc_public}"	 => $cfg['rc_public'],
			"{rc_private}"   => $cfg['rc_private'],
			"{auth_attempt}" => intval($cfg['auth_attempt']),
			"{auth_time_attempt}" => intval($cfg['auth_time_attempt']),
			"{vk_link}" 	 => $cfg['vk_link'],
			"{discord_link}" => $cfg['discord_link'],
			"{youtube_link}" => $cfg['youtube_link'],
			"{jar}"			 => $cfg['jar'],
			"{exe}"			 => $cfg['exe'],
			"{s_dpage}"		 => $cfg['s_dpage']
		]);

	}

	private function to_int_keys($array=array()){
		if(empty($array)){ return false; }

		$cfg = $this->cfg->pagin;

		foreach($array as $key => $value){
			$cfg[$key] = (intval($value)<=0) ? 1 : intval($value);
		}

		return $cfg;
	}

	private function pagin() {
		$cfg = $this->cfg->pagin;

		if($_SERVER['REQUEST_METHOD']=='POST') {
			$post = $_POST;

			unset($post['csrf_secure_key']);
			unset($post['submit']);

			$cfg_keys = array_keys($cfg);
			rsort($cfg_keys);

			$post_keys = array_keys($post);
			rsort($post_keys);

			if($cfg_keys !== $post_keys) { 
				$this->core->notify($this->core->lng["e_msg"], $this->lng['set_e_hash'], 2, '?mode=admin&do=settings&op=pagin'); 
			}

			$cfg = $this->to_int_keys($post);
			if(!$this->cfg->savecfg($cfg, 'pagin.php', 'pagin')) { 
				$this->core->notify($this->core->lng["e_msg"], $this->lng['set_e_cfg_save'], 2, '?mode=admin&do=settings&op=pagin'); 
			}
			
			$this->core->notify($this->core->lng["e_success"], $this->lng['set_save_success'], 3, '?mode=admin&do=settings&op=pagin');
		}

		return Theme::render("modules/admin/settings/pagin", Theme::generateTags($cfg, 'pagin'));
	}

	private function _mail() {
		$cfg = $this->cfg->mail;

		if($_SERVER['REQUEST_METHOD']=='POST') {
			$cfg['smtp']			= (intval(@$_POST['smtp']) === 1) ? true : false;
			$cfg['from']			= $this->core->safestr(@$_POST['from']);
			$cfg['from_name']		= $this->core->safestr(@$_POST['from_name']);
			$cfg['reply']			= $this->core->safestr(@$_POST['reply']);
			$cfg['reply_name']		= $this->core->safestr(@$_POST['reply_name']);
			$cfg['smtp_host']		= $this->core->safestr(@$_POST['smtp_host']);
			$cfg['smtp_user']		= $this->core->safestr(@$_POST['smtp_user']);
			$cfg['smtp_pass']		= $this->core->safestr(@$_POST['smtp_pass']);
			$cfg['smtp_tls']		= (intval(@$_POST['smtp_tls']) === 1) ? true : false;

			if(!$this->cfg->savecfg($cfg, 'mail.php', 'mail')) { 
				$this->core->notify($this->core->lng["e_msg"], $this->lng['set_e_cfg_save'], 2, '?mode=admin&do=settings&op=mail'); 
			}
			
			$this->core->notify($this->core->lng["e_success"], $this->lng['set_save_success'], 3, '?mode=admin&do=settings&op=mail');
		}

		return Theme::render("modules/admin/settings/mail", Theme::generateTags(array_merge($cfg, [
			"smtp__option"			=> ($cfg['smtp']) ? 'selected' : '',
			"smtptls__option"		=> ($cfg['smtp_tls']) ? 'selected' : '',
		]), 'mail'));
	}


	private function functions() {
		$this->core->header .= Theme::render("modules/admin/settings/headers");
		$cfg = $this->cfg->func;

		if($_SERVER['REQUEST_METHOD']=='POST') {
			$cfg['breadcrumbs'] = (intval(@$_POST['breadcrumbs'])===1) ? true : false;
			$cfg['close'] = (intval(@$_POST['close'])===1) ? true : false;
			$cfg['close_time'] = (@$_POST['close_time']=='') ? 0 : intval(strtotime(@$_POST['close_time']));
			$cfg['ipreglimit'] = (intval(@$_POST['input_reglimit'])<=0) ? 0 : intval(@$_POST['input_reglimit']);

			if(!$this->cfg->savecfg($cfg, 'functions.php', 'func')) { 
				$this->core->notify($this->core->lng["e_msg"], $this->lng['set_e_cfg_save'], 2, '?mode=admin&do=settings&op=functions'); 
			}

			$this->core->notify($this->core->lng["e_success"], $this->lng['set_save_success'], 3, '?mode=admin&do=settings&op=functions');
		}

		return Theme::render("modules/admin/settings/functions", [
			"{func_breadcrumbs__select}" => ($cfg['breadcrumbs']) ? 'selected' : '',
			"{func_close__select}" => ($cfg['close']) ? 'selected' : '',
			"{func_reglimit}" => intval(@$cfg['ipreglimit']),
			'{func_close_time}' => (intval($cfg['close_time']) <= 0 ) ? '' : date("d.m.Y H:i:s", $cfg['close_time']),
		]);
	}

	public function content() {
		$op = (isset($_GET['op'])) ? $_GET['op'] : 'list';

		switch($op){
			case 'pagin':		$content = $this->pagin(); break;
			case 'mail':		$content = $this->_mail(); break;
			case 'functions':	$content = $this->functions(); break;
			default:		$content = $this->main(); break;
		}

		return $content;
	}
}

?>