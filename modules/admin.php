<?php

if(!defined("MCR")){ exit("Hacking Attempt!"); }

class module{
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

		$this->core->layout = 'admin';
	}

	private function get_items_array() {
		$query = $this->db->query("SELECT `m`.id, `m`.gid, `m`.title, `m`.`text`, `m`.`url`, `m`.`target`, `m`.`access`, `i`.img
									FROM `mcr_menu_adm` AS `m`
									LEFT JOIN `mcr_menu_adm_icons` AS `i`
										ON `i`.id=`m`.icon
									ORDER BY `priority` ASC");

		if(!$query || $this->db->num_rows($query) <= 0) { 
			return ''; 
		}

		while($ar = $this->db->fetch_assoc($query)) {
			$gid = intval($ar['gid']);

			if(!$this->core->is_access($ar['access'])) { 
				continue; 
			}

			ob_start();

			$do_url = explode('&', $ar['url']);
            if (isset($do_url[1])) { 
                if (stripos($do_url[1], 'do=') !== false) {
					$do_url = explode('=', $do_url[1]);
                    $do_url = (isset($_GET['do']) && $_GET['do'] === $do_url[1]) ? 'active' : '';
                }
			} elseif(empty($_GET['do']) && stripos($do_url[0], 'do=') === false) {
				$do_url = 'active';
			} else {
				$do_url = '';
			}
			
			echo Theme::render("modules/admin/panel/group-element-id", [
				"{id}" => intval($ar['id']),
				"{gid}" => $gid,
				"{title}" => $this->db->HSC($ar['title']),
				"{text}" => $this->db->HSC($ar['text']),
				"{url}" => $this->db->HSC($ar['url']),
				"{target}" => $this->db->HSC($ar['target']),
				"{icon}" => $this->db->HSC($ar['img']),
				"{active}" => $do_url
			]);

			if(!isset($items[$gid])) {
				$items[$gid] = '';
			}

			$items[$gid] .= ob_get_contents();
			ob_get_clean();
		}

		return $items;
	}

	private function display_menu() {
		$items = $this->get_items_array();
		$query = $this->db->query("SELECT id, title, `text`, `access` FROM `mcr_menu_adm_groups` ORDER BY `priority` ASC");

		if(!$query || $this->db->num_rows($query) <= 0) { 
			return; 
		}

		ob_start();
		while($ar = $this->db->fetch_assoc($query)){
			$id = intval($ar['id']);

			if(!$this->core->is_access($ar['access'])) { 
				continue; 
			}
			
			echo Theme::render("modules/admin/panel/group-id", [
				'{id}' => $id,
				'{title}' => $this->db->HSC($ar['title']),
				'{text}'  => $this->db->HSC($ar['text']),
				'{elements}' => (isset($items[$id])) ? $items[$id] : ''
			]);

		}

		return ob_get_clean();
	}

	public function content() {
		if(!$this->core->is_access('sys_adm_main')) { 
			$this->core->notify($this->core->lng['403'], $this->core->lng['e_403']); 
		}

		$this->core->header .= Theme::render("modules/admin/headers");

		$do = (isset($_GET['do'])) ? $_GET['do'] : 'main';

		if(!preg_match("/^[\w\.\-]+$/i", $do)) { 
			$this->core->notify($this->core->lng['403'], $this->core->lng['e_403']); 
		}

		if(!file_exists(MCR_MODE_PATH.'admin/'.$do.'.class.php')){
			$this->core->notify($this->core->lng['404'], $this->core->lng['e_404']);
		}

		require_once(MCR_MODE_PATH.'admin/'.$do.'.class.php');

		if(!class_exists('submodule')) {
			$this->core->notify($this->core->lng['404'], $this->core->lng['e_404']);
		}

		$this->core->lng_m = $this->core->load_language('admin/'.$do);

		$submodule = new submodule($this->core);

		return Theme::render("modules/admin/index", [
			'{a_content}'     => $submodule->content(),
			'{panel}'         => $this->display_menu()
		]);

	}
}

?>