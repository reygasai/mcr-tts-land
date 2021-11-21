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

		if(!$this->core->is_access('sys_adm_monitoring')) { 
			$this->core->notify($this->core->lng['403'], $this->core->lng['e_403']); 
		}

		$bc = array(
			$this->lng['mod_name'] => ADMIN_URL,
			$this->lng['monitoring'] => ADMIN_URL."&do=monitoring"
		);

		$this->core->bc = $this->core->gen_bc($bc);

		require_once MCR_LIBS_PATH . 'upload/class.upload.php';
	}

	private function monitor_array() {
		$start		= $this->core->pagination($this->cfg->pagin['adm_monitoring'], 0, 0);
		$end		= $this->cfg->pagin['adm_monitoring'];

		$where		= "";
		$sort		= "id";
		$sortby		= "DESC";

		if(isset($_GET['search']) && !empty($_GET['search'])){
			$search = $this->db->safesql(urldecode($_GET['search']));
			$where = "WHERE title LIKE '%$search%'";
		}

		if(isset($_GET['sort']) && !empty($_GET['sort'])){
			$expl = explode(' ', $_GET['sort']);

			$sortby = ($expl[0]=='asc') ? "ASC" : "DESC";

			switch(@$expl[1]){
				case 'title': $sort = "title"; break;
				case 'address': $sort = "CONCAT(`ip`, `port`)"; break;
			}
		}

		$query = $this->db->query("SELECT id, title, ip, `port`, `status`, `online`, `slots` FROM `mcr_servers` $where ORDER BY $sort $sortby LIMIT $start, $end");
		if(!$query || $this->db->num_rows($query) <= 0) { 
			return Theme::render("modules/admin/monitoring/monitor-none"); 
		}

		ob_start();
		while($ar = $this->db->fetch_assoc($query)){
			echo Theme::render("modules/admin/monitoring/monitor-id", [
				'{id}'     => intval($ar['id']),
				'{title}'  => $this->db->HSC($ar['title']),
				'{ip}'	   => $this->db->HSC($ar['ip']),
				'{port}'   => intval($ar['port']),
				'{status}' => (intval($ar['status']) === 1) ? 'ON' : 'OFF',
				'{online}' => intval($ar['online']).'/'.intval($ar['slots']),
			]);
		}

		return ob_get_clean();
	}

	private function monitor_list() {
		$sql = "SELECT COUNT(*) FROM `mcr_servers`";
		$page = "?mode=admin&do=monitoring";

		if(isset($_GET['search']) && !empty($_GET['search'])){
			$search = $this->db->safesql(urldecode($_GET['search']));
			$sql = "SELECT COUNT(*) FROM `mcr_servers` WHERE title LIKE '%$search%'";
			$search = $this->db->HSC(urldecode($_GET['search']));
			$page = "?mode=admin&do=monitoring&search=$search";
		}

		if(isset($_GET['sort']) && !empty($_GET['sort'])){
			$page .= '&sort='.$this->db->HSC(urlencode($_GET['sort']));
		}

		$query = $this->db->query($sql);

		if(!$query) { 
			exit("SQL Error"); 
		}

		$ar = $this->db->fetch_array($query);
		return Theme::render("modules/admin/monitoring/monitor-list", [
			'{servers}' => $this->monitor_array(),
			'{pagination}' => $this->core->pagination($this->cfg->pagin['adm_monitoring'], $page.'&pid=', intval($ar[0])),
		]);
	}

	private function delete() {
		if(!$this->core->is_access('sys_adm_monitoring_delete')) { 
			$this->core->notify($this->core->lng["e_msg"], $this->core->lng['e_403'], 2, '?mode=admin&do=monitoring'); 
		}

		if($_SERVER['REQUEST_METHOD']!='POST') { 
			$this->core->notify($this->core->lng["e_msg"], $this->core->lng['e_hack'], 2, '?mode=admin&do=monitoring'); 
		}
			
		$list = @$_POST['id'];

		if(empty($list)) { 
			$this->core->notify($this->core->lng["e_msg"], $this->lng['mon_not_selected'], 2, '?mode=admin&do=monitoring'); 
		}

		$list = $this->core->filter_int_array($list);
		$list = array_unique($list);
		$list = $this->db->safesql(implode(", ", $list));
		if(!$this->db->remove_fast("mcr_monitoring", "id IN ($list)")) { 
			$this->core->notify($this->core->lng["e_msg"], $this->core->lng["e_sql_critical"], 2, '?mode=admin&do=monitoring'); 
		}

		$count = $this->db->affected_rows();
		$this->core->notify($this->core->lng["e_success"], $this->lng['mon_del_elements']." $count", 3, '?mode=admin&do=monitoring');
	}

	private function add() {
		if(!$this->core->is_access('sys_adm_monitoring_add')) { 
			$this->core->notify($this->core->lng["e_msg"], $this->core->lng['e_403'], 2, '?mode=admin&do=monitoring'); 
		}

		$bc = array(
			$this->lng['mod_name'] => ADMIN_URL."",
			$this->lng['monitoring'] => ADMIN_URL."&do=monitoring",
			$this->lng['mon_add'] => ADMIN_URL."&do=monitoring&op=add",
		);

		$this->core->bc = $this->core->gen_bc($bc);

		if($_SERVER['REQUEST_METHOD']=='POST') {
			$title		= $this->db->safesql(@$_POST['title']);
			$text		= $this->db->safesql(@$_POST['text']);
			$ip			= $this->db->safesql(@$_POST['ip']);
			$port		= intval(@$_POST['port']);
			$mods		= $this->db->safesql(@$_POST['mods']);
			$itemban    = $this->db->safesql(@$_POST['itemban']);

			$cover_handle	= new Upload(@$_FILES['cover'], 'ru_RU');
            if ($cover_handle->uploaded) {
                $cover_handle->file_new_name_body = $cover_name = $this->core->random(10, true);
                $cover = $cover_name.'.'.$cover_handle->file_src_name_ext;
                $cover_handle->process(MCR_UPL_PATH.'servers/covers/');
                if ($cover_handle->processed) {
                    $cover_handle->clean();
                } else {
                    return $this->core->notify($this->core->lng["e_msg"], 'Произошла ошибка при загрузке файлов - '.$cover_handle->error, 2, '?mode=admin&do=monitoring');
                }
            }

			if(empty($cover)) {
				$cover = 'default.png';
			}

			$insert = $this->db->query("INSERT INTO `mcr_servers` (`title`, `text`, ip, `port`, `players`, `last_update`, `mods`, `itemban`, `cover`)
										VALUES ('$title', '$text', '$ip', '$port', '', '', '$mods', '$itemban', '$cover')");

			if(!$insert) { $this->core->notify($this->core->lng["e_msg"], $this->core->lng["e_sql_critical"], 2, '?mode=admin&do=monitoring'); }
			
			$this->core->notify($this->core->lng["e_success"], $this->lng['mon_add_success'], 3, '?mode=admin&do=monitoring');
		}

		return Theme::render("modules/admin/monitoring/monitor-add", [
			"{page}"		=> $this->lng['mon_add_page_name'],
			"{name}"		=> "",
			"{text}"		=> "",
			"{mods}"		=> "",
			"{ip}"		    => "127.0.0.1",
			"{port}"		=> "25565",
			'{itemban}'     => "",
			"{button}"	    => $this->lng['mon_add_btn'],
			'{cover}'		=> ''
		]);
	}

	private function edit() {
		if(!$this->core->is_access('sys_adm_monitoring_edit')) { 
			$this->core->notify($this->core->lng["e_msg"], $this->core->lng['e_403'], 2, '?mode=admin&do=monitoring'); 
		}

		$id = intval($_GET['id']);
		$query = $this->db->query("SELECT `title`, `text`, ip, `port`, `players`, `last_update`, `mods`, `itemban`, `cover`
								   FROM `mcr_servers`
								   WHERE id='$id'");

		if(!$query || $this->db->num_rows($query) <= 0) { 
			$this->core->notify($this->core->lng["e_msg"], $this->core->lng["e_sql_critical"], 2, '?mode=admin&do=monitoring'); 
		}

		$ar = $this->db->fetch_assoc($query);

		$bc = array(
			$this->lng['mod_name'] => ADMIN_URL."",
			$this->lng['monitoring'] => ADMIN_URL."&do=monitoring",
			$this->lng['mon_edit'] => ADMIN_URL."&do=monitoring&op=edit&id=$id",
		);

		$this->core->bc = $this->core->gen_bc($bc);

		if($_SERVER['REQUEST_METHOD']=='POST') {
			$title		= $this->db->safesql(@$_POST['title']);
			$text		= $this->db->safesql(@$_POST['text']);
			$ip			= $this->db->safesql(@$_POST['ip']);
			$port		= intval(@$_POST['port']);
			$mods		= $this->db->safesql(@$_POST['mods']);
			$itemban    = $this->db->safesql(@$_POST['itemban']);

			$cover_handle = new Upload(@$_FILES['cover'], 'ru_RU');
			if($cover_handle->uploaded) {
				if(strcasecmp($ar['cover'], 'default.png') !== 0) { unlink(MCR_UPL_PATH.'servers/covers/'.$ar['cover']); }
				$cover_handle->file_new_name_body = $cover_name = $this->core->random(10, true);
				$cover = $cover_name.'.'.$cover_handle->file_src_name_ext; 
				$cover_handle->process(MCR_UPL_PATH.'servers/covers/');
				if ($cover_handle->processed) {
					$cover_handle->clean();
				} else {
					return $this->core->notify($this->core->lng["e_msg"], 'Произошла ошибка при загрузке файлов - '.$cover_handle->error, 2, ADMIN_URL."&do=monitoring&op=edit&id=$id");
				}
			}

			if(empty($cover)) {
				$cover = $ar['cover'];
			}

			$update = $this->db->query("UPDATE `mcr_servers`
										SET `title` = '$title', `text` = '$text', ip = '$ip', `port` = '$port', `mods` = '$mods', `itemban` = '$itemban', `cover` = '$cover'
										WHERE id='$id'");

			if(!$update) { 
				$this->core->notify($this->core->lng["e_msg"], $this->core->lng["e_sql_critical"], 2, '?mode=admin&do=monitoring&op=edit&id='.$id); 
			}
			
			$this->core->notify($this->core->lng["e_success"], $this->lng['mon_edit_success'], 3, '?mode=admin&do=monitoring&op=edit&id='.$id);
		}

		return Theme::render("modules/admin/monitoring/monitor-add", [
			"{page}"		=> $this->lng['mon_edit_page_name'],
			"{name}"		=> $this->db->HSC($ar['title']),
			"{text}"		=> $this->db->HSC($ar['text']),
			"{mods}"		=> $this->db->HSC($ar['mods']),
			"{ip}"		    => $this->db->HSC($ar['ip']),
			"{port}"		=> intval($ar['port']),
			'{itemban}'     => $this->db->HSC($ar['itemban']),
			"{button}"	    => $this->lng['mon_edit_btn'],
			"{cover}"		=> (strcasecmp($ar['cover'], 'default.png') !== 0) ?Theme::render("modules/admin/monitoring/monitor-server-cover", [
				'{img}'	=> $this->db->HSC($ar['cover'])
			]) : '',
		]);

	}

	public function content(){
		$op = (isset($_GET['op'])) ? $_GET['op'] : 'list';

		switch($op){
			case 'add':		$content = $this->add(); break;
			case 'edit':	$content = $this->edit(); break;
			case 'delete':	$this->delete(); break;
			default:		$content = $this->monitor_list(); break;
		}

		return $content;
	}
}
?>