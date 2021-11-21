<?php

if(!defined("MCR")){ exit("Hacking Attempt!"); }

class submodule {
	private $core, $db, $cfg, $user, $lng;

	public function __construct($core){
		$this->core		= $core;
		$this->db		= $core->db;
		$this->cfg		= $core->cfg;
		$this->user		= $core->user;
		$this->lng		= $core->lng_m;

		if(!$this->core->is_access('sys_adm_users')) { 
			$this->core->notify($this->core->lng['403'], $this->core->lng['e_403']); 
		}

		$bc = array(
			$this->lng['mod_name'] => ADMIN_URL,
			$this->lng['users'] => ADMIN_URL."&do=users"
		);

		$this->core->bc = $this->core->gen_bc($bc);
	}

	private function user_array() {
		$ctables	= $this->cfg->db['tables'];

		$ug_f		= $ctables['ugroups']['fields'];
		$us_f		= $ctables['users']['fields'];

		$start		= $this->core->pagination($this->cfg->pagin['adm_users'], 0, 0);
		$end		= $this->cfg->pagin['adm_users'];

		$where		= "";
		$sort		= "`u`.`{$us_f['id']}`";
		$sortby		= "DESC";

		if(isset($_GET['search']) && !empty($_GET['search'])){
			$search = $this->db->safesql(urldecode($_GET['search']));
			if(preg_match("/[а-яА-ЯёЁ]+/iu", $search)) { $search = ""; }
			$table = (preg_match("/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/i", $search)) ? $us_f['ip_last'] : $us_f['login'];
			$where = "WHERE `u`.`$table` LIKE '%$search%'";
		}

		if(isset($_GET['sort']) && !empty($_GET['sort'])) {
			$expl = explode(' ', $_GET['sort']);

			$sortby = ($expl[0]=='asc') ? "ASC" : "DESC";

			switch(@$expl[1]){
				case 'user': $sort = "`u`.`{$us_f['login']}`"; break;
				case 'group': $sort = "`g`.`{$ug_f['title']}`"; break;
				case 'email': $sort = "`u`.`{$us_f['email']}`"; break;
				case 'ip': $sort = "`u`.`{$us_f['ip_last']}`"; break;
			}
		}

		$query = $this->db->query("SELECT `u`.`{$us_f['id']}`, `u`.`{$us_f['group']}`, `u`.`{$us_f['login']}`, `u`.`{$us_f['email']}`,
										`u`.`{$us_f['color']}`, `u`.`{$us_f['ip_create']}`, `u`.`{$us_f['ip_last']}`,
										`g`.`{$ug_f['title']}` AS `group`, `g`.`{$ug_f['color']}` AS `gcolor`
									FROM `{$this->cfg->tabname('users')}` AS `u`
									LEFT JOIN `{$this->cfg->tabname('ugroups')}` AS `g`
										ON `g`.`{$ug_f['id']}`=`u`.`{$us_f['group']}`
									$where
									ORDER BY $sort $sortby
									LIMIT $start, $end");

		if(!$query || $this->db->num_rows($query) <= 0)	{
			return Theme::render("modules/admin/users/user-none"); 
		}

		ob_start();
		while($ar = $this->db->fetch_assoc($query))	{
			$ucolor = (!empty($ar[$us_f['color']])) ? $this->db->HSC($ar[$us_f['color']]) : $this->db->HSC($ar['gcolor']);
			$gcolor = $this->db->HSC($ar['gcolor']);

			echo Theme::render("modules/admin/users/user-id", [
				"{id}" => intval($ar[$us_f['id']]),
				"{gid}" => intval($ar[$us_f['group']]),
				"{login}" => $this->core->colorize($this->db->HSC($ar[$us_f['login']]), $ucolor),
				"{email}" => $this->db->HSC($ar[$us_f['email']]),
				"{group}" => $this->core->colorize($this->db->HSC($ar['group']), $gcolor),
				"{ip_last}" => $this->db->HSC($ar[$us_f['ip_last']]),
				"{ip_create}" => $this->db->HSC($ar[$us_f['ip_create']]),
			]);
		}

		return ob_get_clean();
	}

	private function user_list() {
		$ctables	= $this->cfg->db['tables'];
		$us_f		= $ctables['users']['fields'];

		$sql = "SELECT COUNT(*) FROM `{$this->cfg->tabname('users')}`";
		$page = "?mode=admin&do=users";

		if(isset($_GET['search']) && !empty($_GET['search'])){
			$search = $this->db->safesql(urldecode($_GET['search']));
			if(preg_match("/[а-яА-ЯёЁ]+/iu", $search)){ $search = ""; }
			$table = (preg_match("/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/i", $search)) ? $us_f['ip_last'] : $us_f['login'];
			$sql = "SELECT COUNT(*) FROM `{$this->cfg->tabname('users')}` WHERE `$table` LIKE '%$search%'";
			$search = $this->db->HSC(urldecode($_GET['search']));
			$page = "?mode=admin&do=users&search=$search";
		}

		if(isset($_GET['sort']) && !empty($_GET['sort'])) {
			$page .= '&sort='.$this->db->HSC(urlencode($_GET['sort']));
		}

		$query = $this->db->query($sql);
		$ar = @$this->db->fetch_array($query);
		
		return Theme::render("modules/admin/users/user-list", [
			"{pagination}" => $this->core->pagination($this->cfg->pagin['adm_users'], $page.'&pid=', intval($ar[0])),
			"{users}" => $this->user_array()
		]);
	}

	private function ban($list, $ban = 1) {
		if(!$this->core->is_access('sys_adm_users_ban')) {
			$this->core->notify($this->core->lng["e_msg"], $this->core->lng['e_403'], 2, '?mode=admin&do=users'); 
		}

		$ctables	= $this->cfg->db['tables'];
		$us_f		= $ctables['users']['fields'];
		$update = $this->db->query("UPDATE `{$this->cfg->tabname('users')}` SET `{$us_f['ban_server']}`='$ban' WHERE `{$us_f['id']}` IN ($list)");

		if(!$update) {
			$this->core->notify($this->core->lng["e_msg"], $this->core->lng["e_sql_critical"], 2, '?mode=admin&do=users'); 
		}

		$message = ($ban==1) ? $this->lng['user_ban'] : $this->lng['user_unban'];
		$this->core->notify($this->core->lng["e_success"], $this->lng['user_success']." ".$message, 3, '?mode=admin&do=users');
	}

	private function get_logins($list) {
		$ctables	= $this->cfg->db['tables'];
		$us_f		= $ctables['users']['fields'];

		$query = $this->db->query("SELECT `{$us_f['login']}` FROM `{$this->cfg->tabname('users')}` WHERE `{$us_f['id']}` IN ($list)");

		if(!$query || $this->db->num_rows($query) <= 0) { 
			return false; 
		}

		$logins = array();

		while($ar = $this->db->fetch_assoc($query)) {
			$logins[] = $ar[$us_f['login']]; 
		}

		return $logins;
	}

	private function delete() {
		if($_SERVER['REQUEST_METHOD']!='POST') { 
			$this->core->notify($this->core->lng["e_msg"], $this->core->lng['e_hack'], 2, '?mode=admin&do=users'); 
		}
			
		$list = @$_POST['id'];
		if(empty($list)) { 
			$this->core->notify($this->core->lng["e_msg"], $this->lng['user_not_selected'], 2, '?mode=admin&do=users'); 
		}

		$list = $this->core->filter_int_array($list);
		$list = array_unique($list);
		$list = $this->db->safesql(implode(", ", $list));

		$logins = $this->get_logins($list);
		if($logins === false) {
			$this->core->notify($this->core->lng["e_msg"], $this->lng['user_not_found'], 2, '?mode=admin&do=users'); 
		}

		if(isset($_POST['ban'])) {
			$this->ban($list);
		} elseif(isset($_POST['unban'])) {
			$this->ban($list, 0);
		}

		if(!$this->core->is_access('sys_adm_users_delete')) { 
			$this->core->notify($this->core->lng["e_msg"], $this->core->lng['e_403'], 2, '?mode=admin&do=users'); 
		}

		if(!isset($_POST['delete'])) { 
			$this->core->notify($this->core->lng["e_msg"], $this->core->lng['e_hack'], 2, '?mode=admin&do=users'); 
		}

		$ctables	= $this->cfg->db['tables'];
		$us_f		= $ctables['users']['fields'];

		if(!$this->db->remove_fast($this->cfg->tabname('users'), "`{$us_f['id']}` IN ($list)")) { 
			$this->core->notify($this->core->lng["e_msg"], $this->core->lng["e_sql_critical"], 2, '?mode=admin&do=users'); 
		}

		$count = $this->db->affected_rows();
		foreach($logins as $key => $value) {
			if(file_exists(MCR_SKIN_PATH.$value.'.png')){ @unlink(MCR_SKIN_PATH.$value.'.png'); }
			if(file_exists(MCR_SKIN_PATH.'interface/'.$value.'.png')){ @unlink(MCR_SKIN_PATH.'interface/'.$value.'.png'); }
			if(file_exists(MCR_SKIN_PATH.'interface/'.$value.'_mini.png')){ @unlink(MCR_SKIN_PATH.'interface/'.$value.'_mini.png'); }
			if(file_exists(MCR_CLOAK_PATH.$value.'.png')){ @unlink(MCR_CLOAK_PATH.$value.'.png'); }
		}

		$this->core->notify($this->core->lng["e_success"], $this->lng['user_del_elements']." $count", 3, '?mode=admin&do=users');
	}

	private function exist_group($id) {
		$ctables	= $this->cfg->db['tables'];
		$ug_f		= $ctables['ugroups']['fields'];

		$id = intval($id);
		$query = $this->db->query("SELECT COUNT(*) FROM `{$this->cfg->tabname('ugroups')}` WHERE `{$ug_f['id']}`='$id'");
		if(!$query) { 
			return false; 
		}

		$ar = $this->db->fetch_array($query);
		if(intval($ar[0]) <= 0) { 
			return false; 
		}

		return true;
	}

	private function add() {
		if(!$this->core->is_access('sys_adm_users_add')) { 
			$this->core->notify($this->core->lng["e_msg"], $this->core->lng['e_403'], 2, '?mode=admin&do=users'); 
		}

		$bc = array(
			$this->lng['mod_name'] => ADMIN_URL."",
			$this->lng['users'] => ADMIN_URL."&do=users",
			$this->lng['user_add'] => ADMIN_URL."&do=users&op=add",
		);

		$this->core->bc = $this->core->gen_bc($bc);

		if($_SERVER['REQUEST_METHOD']=='POST') {
			$login			= $this->db->safesql(@$_POST['login']);
			$color			= $this->db->safesql(@$_POST['color']);
			$uuid			= $this->db->safesql($this->user->logintouuid(@$_POST['login']));

			$salt		= $this->db->safesql($this->core->random());
			$password	= $this->db->safesql($this->core->gen_password($_POST['password']));

			if(!empty($color) && !preg_match("/^\#[a-f0-9]{6}|[a-f0-9]{3}$/i", $color)) { 
				$this->core->notify($this->core->lng["e_msg"], $this->lng["user_e_color_format"], 2, '?mode=admin&do=users&op=add'); 
			}

			if(mb_strlen($_POST['password'], "UTF-8") < 6) { 
				$this->core->notify($this->core->lng['e_msg'], $this->lng['user_e_reg_pass_length'], 2, '?mode=admin&do=users&op=add'); 
			}

			$email	   = $this->db->safesql(@$_POST['email']);
			$gid	   = intval(@$_POST['gid']);
			$firstname = @$_POST['firstname'];
			$lastname  = @$_POST['lastname'];
			$birthday  = @$_POST['birthday'];

			$gender = (intval(@$_POST['gender']) == 1) ? 1 : 0;

			if(!empty($firstname) && !preg_match("/^[a-zA-Zа-яА-ЯёЁ]+$/iu", $firstname)){ $this->core->notify($this->core->lng['e_msg'], $this->lng['user_e_incorrect_fname'], 2, '?mode=admin&do=users&op=add'); }
			if(!empty($lastname) && !preg_match("/^[a-zA-Zа-яА-ЯёЁ]+$/iu", $lastname)){ $this->core->notify($this->core->lng['e_msg'], $this->lng['user_e_incorrect_lname'], 2, '?mode=admin&do=users&op=add'); }
			if(!empty($birthday) && !preg_match("/^(\d{2}-\d{2}-\d{4})?$/", $birthday)){ $this->core->notify($this->core->lng['e_msg'], $this->lng['user_e_incorrect_bday'], 2, '?mode=admin&do=users&op=add'); }

			$birthday = intval(strtotime($birthday));

			if(!$this->exist_group($gid)) { 
				$this->core->notify($this->core->lng['e_msg'], $this->lng['user_group_not_found'], 1, '?mode=admin&do=users&op=add'); 
			}

			$realmoney = intval(@$_POST['realmoney']);
			$time = time();

			$ctables	= $this->cfg->db['tables'];
			$us_f		= $ctables['users']['fields'];

			$insert = $this->db->query("INSERT INTO `{$this->cfg->tabname('users')}`
											(`{$us_f['group']}`, `{$us_f['login']}`, `{$us_f['email']}`, `{$us_f['pass']}`, `{$us_f['color']}`, `{$us_f['uuid']}`, `{$us_f['salt']}`, `{$us_f['ip_create']}`, `{$us_f['ip_last']}`, `{$us_f['date_reg']}`, `{$us_f['date_last']}`, `{$us_f['fname']}`, `{$us_f['lname']}`, `{$us_f['gender']}`, `{$us_f['bday']}`, `{$us_f['realmoney']}`)
										VALUES
											('$gid', '$login', '$email', '$password', '$color', '$uuid', '$salt', '{$this->user->ip}', '{$this->user->ip}', '$time', '$time', '$firstname', '$lastname', '$gender', '$birthday', '$realmoney')");

			if(!$insert) { 
				$this->core->notify($this->core->lng["e_msg"], $this->core->lng["e_sql_critical"], 2, '?mode=admin&do=users'); 
			}
			
			$this->core->notify($this->core->lng["e_success"], $this->lng['user_add_success'], 3, '?mode=admin&do=users');
		}
		
		return Theme::render("modules/admin/users/user-add", [
			'{page}' => $this->lng['user_add_page_name'],
			'{login}' => '',
			'{email}' => '',
			'{firstname}' => '',
			'{lastname}' => '',
			'{color}' => '',
			'{birthday}' => date("d-m-Y"),
			'{gender}' => '',
			'{groups}' => $this->groups(),
			'{realmomey}' => 0,
			'{button}' => $this->lng['user_add_btn']
		]);
	}

	private function edit() {
		if(!$this->core->is_access('sys_adm_users_edit')) { 
			$this->core->notify($this->core->lng["e_msg"], $this->core->lng['e_403'], 2, '?mode=admin&do=users'); 
		}

		$id = intval($_GET['id']);

		$ctables	= $this->cfg->db['tables'];
		$us_f		= $ctables['users']['fields'];

		$query = $this->db->query("SELECT `u`.`{$us_f['login']}`, `u`.`{$us_f['group']}`, `u`.`{$us_f['email']}`, `u`.`{$us_f['date_reg']}`,
											`u`.`{$us_f['date_last']}`, `u`.`{$us_f['fname']}`, `u`.`{$us_f['lname']}`, `u`.`{$us_f['gender']}`,
											`u`.`{$us_f['bday']}`, `u`.`{$us_f['color']}`, `{$us_f['realmoney']}`
									FROM `{$this->cfg->tabname('users')}` AS `u`
									WHERE `u`.`{$us_f['id']}`='$id'");

		if(!$query || $this->db->num_rows($query) <= 0) { 
			$this->core->notify($this->core->lng["e_msg"], $this->core->lng["e_sql_critical"], 2, '?mode=admin&do=users'); 
		}

		$ar = $this->db->fetch_assoc($query);

		$bc = array(
			$this->lng['mod_name'] => ADMIN_URL."",
			$this->lng['users'] => ADMIN_URL."&do=users",
			$this->lng['user_edit'] => ADMIN_URL."&do=users&op=edit&id=$id",
		);

		$this->core->bc = $this->core->gen_bc($bc);

		if($_SERVER['REQUEST_METHOD']=='POST') {
			$login			= $this->db->safesql(@$_POST['login']);
			$color			= $this->db->safesql(@$_POST['color']);
			$uuid			= $this->db->safesql($this->user->logintouuid(@$_POST['login']));

			$password		= "`password`";
			$salt			= "`salt`";

			if(!empty($color) && !preg_match("/^\#[a-f0-9]{6}|[a-f0-9]{3}$/i", $color)){ $this->core->notify($this->core->lng["e_msg"], $this->lng["user_e_color_format"], 2, '?mode=admin&do=users&op=edit&id='.$id); }

			if(isset($_POST['password']) && !empty($_POST['password'])){
				$salt		= $this->db->safesql($this->core->random());
				$salt		= "'$salt'";
				
				if(mb_strlen($_POST['password'], "UTF-8")<6){ $this->core->notify($this->core->lng['e_msg'], $this->lng['user_e_reg_pass_length'], 2, '?mode=admin&do=users&op=edit&id='.$id); }
				
				$password	= $this->core->gen_password($_POST['password'], $salt);
				$password	= $this->db->safesql($password);
				$password	= "'$password'";
			}

			$email			= $this->db->safesql(@$_POST['email']);

			$gid			= intval(@$_POST['gid']);

			$firstname = @$_POST['firstname'];
			$lastname = @$_POST['lastname'];
			$birthday = @$_POST['birthday'];

			$gender = (intval(@$_POST['gender'])==1) ? 1 : 0;

			if(!empty($firstname) && !preg_match("/^[a-zA-Zа-яА-ЯёЁ]+$/i", $firstname)){ $this->core->notify($this->core->lng['e_msg'], $this->lng['user_e_incorrect_fname'], 2, '?mode=admin&do=users&op=edit&id='.$id); }
			if(!empty($lastname) && !preg_match("/^[a-zA-Zа-яА-ЯёЁ]+$/i", $lastname)){ $this->core->notify($this->core->lng['e_msg'], $this->lng['user_e_incorrect_lname'], 2, '?mode=admin&do=users&op=edit&id='.$id); }
			if(!empty($birthday) && !preg_match("/^(\d{2}-\d{2}-\d{4})?$/", $birthday)){ $this->core->notify($this->core->lng['e_msg'], $this->lng['user_e_incorrect_bday'], 2, '?mode=admin&do=users&op=edit&id='.$id); }

			$birthday = intval(strtotime($birthday));

			if(!$this->exist_group($gid)){ $this->core->notify($this->core->lng['e_msg'], $this->lng['user_group_not_found'], 1, '?mode=admin&do=users&op=edit&id='.$id); }

			$realmoney = intval(@$_POST['realmoney']);

			$time = time();

			$update = $this->db->query("UPDATE `{$this->cfg->tabname('users')}`
										SET `{$us_f['group']}`='$gid', `{$us_f['login']}`='$login', `{$us_f['color']}`='$color', `{$us_f['email']}`='$email',
											`{$us_f['pass']}`=$password, `{$us_f['uuid']}`='$uuid', `{$us_f['salt']}`=$salt, `{$us_f['date_last']}`='$time',
											`{$us_f['fname']}`='$firstname', `{$us_f['lname']}`='$lastname', `{$us_f['gender']}`='$gender',
											`{$us_f['bday']}`='$birthday', `{$us_f['realmoney']}` = '$realmoney'
										WHERE `{$us_f['id']}`='$id'");

			if(!$update){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng["e_sql_critical"], 2, '?mode=admin&do=users&op=edit&id='.$id); }
			
			$old_login = $this->db->safesql($ar[$us_f['login']]);

			if(file_exists(MCR_SKIN_PATH.$old_login.'.png')){
				if(!rename(MCR_SKIN_PATH.$old_login.'.png', MCR_SKIN_PATH.$login.'.png')){
					$this->core->notify($this->lng["e_msg"], $this->lng['user_e_skin_name'], 2, '?mode=admin&do=users&op=edit&id='.$id);
				}
			}

			if(file_exists(MCR_CLOAK_PATH.$old_login.'.png')){
				if(!rename(MCR_CLOAK_PATH.$old_login.'.png', MCR_CLOAK_PATH.$login.'.png')){
					$this->core->notify($this->core->lng["e_msg"], $this->lng['user_e_cloak_name'], 2, '?mode=admin&do=users&op=edit&id='.$id);
				}
			}
			
			$this->core->notify($this->core->lng["e_success"], $this->lng['user_edit_success'], 3, '?mode=admin&do=users&op=edit&id='.$id);
		}

		return Theme::render("modules/admin/users/user-add", [
			'{page}' => $this->lng['user_edit_page_name'],
			'{login}' => $this->db->HSC($ar[$us_f['login']]),
			'{email}' => $this->db->HSC($ar[$us_f['email']]),
			'{firstname}' => $this->db->HSC($ar[$us_f['fname']]),
			'{lastname}' => $this->db->HSC($ar[$us_f['lname']]),
			'{color}' => $this->db->HSC($ar[$us_f['color']]),
			'{birthday}' => date("d-m-Y", $ar[$us_f['bday']]),
			'{gender}' => (intval($ar[$us_f['gender']])==1 || $ar[$us_f['gender']]=='female') ? "selected" : "",
			'{groups}' => $this->groups($ar[$us_f['group']]),
			'{realmomey}' => floatval($ar[$us_f['realmoney']]),
			'{button}' => $this->lng['user_edit_btn']
		]);
	}

	private function groups($select = 1) {
		$ctables	= $this->cfg->db['tables'];
		$ug_f		= $ctables['ugroups']['fields'];

		$select = intval($select);
		$query = $this->db->query("SELECT `{$ug_f['id']}`, `{$ug_f['title']}`
									FROM `{$this->cfg->tabname('ugroups')}`
									ORDER BY `{$ug_f['title']}` ASC");

		if(!$query || $this->db->num_rows($query) <= 0){ 
			return; 
		}

		ob_start();
		while($ar = $this->db->fetch_assoc($query)) {
			$id = intval($ar[$ug_f['id']]);
			$selected = ($id == $select) ? "selected" : "";
			$title = $this->db->HSC($ar[$ug_f['title']]);
			echo "<option value=\"$id\" $selected>$title</option>";
		}

		return ob_get_clean();
	}

	public function content(){
		$op = (isset($_GET['op'])) ? $_GET['op'] : 'list';

		switch($op){
			case 'add':		$content = $this->add(); break;
			case 'edit':	$content = $this->edit(); break;
			case 'delete':	$this->delete(); break;
			case 'ban':		$this->delete(); break;

			default:		$content = $this->user_list(); break;
		}

		return $content;
	}
}

?>