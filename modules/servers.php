<?php
if(!defined("MCR")){ exit("Hacking Attempt!"); }

class module {
	private $core, $db;

	public function __construct($core) {
		$this->core		    = $core;
        $this->db		    = $core->db;
        $this->core->layout = 'singlepage';
    }

    private function explode_data($string, $template_suffix = 'mods') {
        if(strlen($string) == 0) {
            return Theme::render("modules/servers/empty-".$template_suffix);
        }

        $data = explode(",", preg_replace('/\s+/', '', $string));

        ob_start();
        $render_data = [];
        foreach($data as $value) {
            if($template_suffix === 'mods') {
                $render_data = [
                    '{server_mod}' => $value,
                ];
            } else {
                $render_data = [
                    '{server_playername}' => $value,
                    '{server_skin_playername}' => file_exists(MCR_SKIN_PATH.'interface'.$value.'_mini.png') ? $value.'_mini.png' : 'default_mini.png'
                ];
            }

            echo Theme::render("modules/servers/".$template_suffix, $render_data);
        }

        return ob_get_clean();
    }

	public function content() {
        if(empty($_GET['server_id']) || !isset($_GET['server_id'])) {
            return $this->core->notify("Ошибка!", "Отсутсвуют данные о требуем сервере", 1);
        }

        $server_id = intval($_GET['server_id']);
        $this->core->header = Theme::render("modules/servers/headers");

        $query_s = $this->db->query("SELECT `id`, `title`, `text`, `status`, `version`, `mods`, `cover`, `online`, `slots`, `last_update`, `players` FROM `mcr_servers` WHERE `id` = '{$server_id}'");
        if(!$query_s || $this->db->num_rows($query_s) == 0) {
            return $this->core->notify("Ошибка!", "Данного сервера не существует, или он не доступен для просмотра", 1);
        }

        $server = $this->db->fetch_assoc($query_s);
        $server_online = intval($server['online']);
        $server_status = intval($server['status']);

        $data = [
            '{server_id}'         => intval($server['id']),
            '{server_title}'      => $this->db->HSC($server['title']),
            '{server_text}'       => $this->db->HSC($server['text']),
            '{server_version}'    => $this->db->HSC($server['version']),
            '{server_online}'     => ($server_status === 1) ? $server_online : 'OFF',
            '{server_cover}'      => $this->db->HSC($server['cover']),
            '{server_mods}'       => $this->explode_data($server['mods'], 'mods'),
            '{server_players}'    => ($server_online === 0 || $server_status === 0) ? Theme::render("modules/servers/empty-players") : Theme::render("modules/servers/players-list", ['{server_players_list}' => $this->explode_data($server['players'], 'players')]) 
        ];

        $this->core->bc = $this->core->gen_bc([
            "Описание сервера ".$data['{server_title}'] => BASE_URL."servers/".$data['{server_id}']
        ]);

        return Theme::render("modules/servers/server", $data);
    }
}
?>