<?php

if(!defined("MCR")){ exit("Hacking Attempt!"); }

class module {
	private $core, $db;

	public function __construct($core) {
    $this->core	= $core;
    $this->db = $core->db;
    $this->pages  = $core->cfg_m;
    $this->core->bc = $this->core->gen_bc(["Cтраница" => BASE_URL."page"]);
	}

	public function content() {
    if(!isset($_GET['page']) || empty($_GET['page'])) {
      return $this->core->notify('', "Данной страницы нет на сайте.", 2);
    }

    $page = $this->db->safesql(strtolower($_GET['page']));

    if(!isset($this->pages['pages'][$page]) || !file_exists(MCR_THEME_MOD."page/{$page}.html") || empty($this->pages['pages'][$page]['layout'])) {
      return $this->core->notify('Ошибка!', 'Данной страницы не существует, или она еще не была создана. Пожалуйста, обратитесь к администрации, если вы считаете что это ошибка,', 2);
    }

    $this->core->layout = $this->pages['pages'][$page]['layout'];

    $this->core->bc = $this->core->gen_bc(['Cтраница' => BASE_URL.'page', $this->pages['pages'][$page]['title'] => BASE_URL.'page/'.$page]);

    return Theme::render("modules/page/$page");
  }
}
?>
