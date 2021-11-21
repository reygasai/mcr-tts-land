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

		if(!$this->core->is_access('sys_adm_logs')) { 
			$this->core->notify($this->core->lng['403'], $this->core->lng['e_403']); 
		}

		$bc = array(
			$this->lng['mod_name'] => ADMIN_URL,
			$this->lng['logs'] => ADMIN_URL."&do=logs"
		);

		$this->core->bc = $this->core->gen_bc($bc);
	}

	private function logs_array() {
		$ctables	= $this->cfg->db['tables'];

		$ug_f		= $ctables['ugroups']['fields'];
		$logs_f		= $ctables['logs']['fields'];
		$us_f		= $ctables['users']['fields'];

		$start		= $this->core->pagination($this->cfg->pagin['adm_logs'], 0, 0);
		$end		= $this->cfg->pagin['adm_logs'];

		$where		= "";
		$sort		= "`l`.`{$logs_f['id']}`";
		$sortby		= "DESC";

		if(isset($_GET['search']) && !empty($_GET['search'])){
			$search = $this->db->safesql(urldecode($_GET['search']));
			$where = "WHERE `l`.`{$logs_f['msg']}` LIKE '%$search%'";
		}

		if(isset($_GET['sort']) && !empty($_GET['sort'])) {
			$expl = explode(' ', $_GET['sort']);

			$sortby = ($expl[0]=='asc') ? "ASC" : "DESC";

			switch(@$expl[1]) {
				case 'user': $sort = "`u`.`{$logs_f['login']}`"; break;
				case 'msg': $sort = "`l`.`{$logs_f['msg']}`"; break;
				case 'date': $sort = "`l`.`{$logs_f['date']}`"; break;
			}
		}

		$query = $this->db->query("SELECT `l`.`{$logs_f['id']}`, `l`.`{$logs_f['uid']}`, `l`.`{$logs_f['msg']}`, `l`.`{$logs_f['date']}`,
										`u`.`{$us_f['login']}`, `u`.`{$us_f['color']}`, `g`.`{$ug_f['color']}` AS `gcolor`
									FROM `{$this->cfg->tabname('logs')}` AS `l`
									LEFT JOIN `{$this->cfg->tabname('users')}` AS `u`
										ON `u`.`{$us_f['id']}`=`l`.`{$logs_f['uid']}`
									LEFT JOIN `{$this->cfg->tabname('ugroups')}` AS `g`
										ON `g`.`{$ug_f['id']}`=`u`.`{$us_f['group']}`
									$where
									ORDER BY $sort $sortby
									LIMIT $start, $end");

		if(!$query || $this->db->num_rows($query) <= 0) { 
			return Theme::render("modules/admin/logs/log-none"); 
		}

		ob_start();
		while($ar = $this->db->fetch_assoc($query)) {
			$color = (empty($ar[$us_f['color']])) ? $this->db->HSC($ar['gcolor']) : $this->db->HSC($ar[$us_f['color']]);
			$login = (!is_null($ar[$us_f['login']])) ? $this->db->HSC($ar['login']) : $this->lng['mod_name'];

			echo Theme::render("modules/admin/logs/log-id", [
				"{id}" => intval($ar[$logs_f['id']]),
				"{uid}" => intval($ar[$logs_f['uid']]),
				"{message}" => $this->db->HSC($ar[$logs_f['msg']]),
				"{date}" => date("d.m.Y в H:i:s", $ar[$logs_f['date']]),
				"{user_login}" => $this->core->colorize($login, $color),
			]);
		}

		return ob_get_clean();
	}

	public function content(){
		$ctables	= $this->cfg->db['tables'];
		$logs_f		= $ctables['logs']['fields'];

		$sql = "SELECT COUNT(*) FROM `{$this->cfg->tabname('logs')}`";
		$page = "?mode=admin&do=logs";

		if(isset($_GET['search']) && !empty($_GET['search'])) {
			$search = $this->db->safesql(urldecode($_GET['search']));
			$sql = "SELECT COUNT(*) FROM `{$this->cfg->tabname('logs')}` WHERE `{$logs_f['msg']}` LIKE '%$search%'";
			$search = $this->db->HSC(urldecode($_GET['search']));
			$page = "?mode=admin&do=logs&search=$search";
		}

		if(isset($_GET['sort']) && !empty($_GET['sort'])) {
			$page .= '&sort='.$this->db->HSC(urlencode($_GET['sort']));
		}

		$query = $this->db->query($sql);
		$ar = @$this->db->fetch_array($query);

		return Theme::render("modules/admin/logs/log-list", [
			"{pagination}" => $this->core->pagination($this->cfg->pagin['adm_logs'], $page.'&pid=', intval($ar[0])),
			'{logs}'	   => $this->logs_array()
		]);
	}
}

?>