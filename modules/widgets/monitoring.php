<?php

if(!defined("MCR")) { exit("Hacking Attempt!"); }

class monitoring {
	private $core, $db;

	public function __construct($core) {
		$this->core	= $core;
		$this->db	= $core->db;
	}

	private function server_array() {
		$query = $this->db->query("SELECT `id`, `title`, `status`, `online`, `slots` FROM `mcr_servers`");
		$rows = $this->db->num_rows($query);
		if(!$query || ($rows <= 0)) return;
		ob_start();
		$col = (($rows % 2) === 0) ? Theme::render("widgets/monitoring/col-double") : Theme::render("widgets/monitoring/col-one");

		while($ar = $this->db->fetch_assoc($query)) {
			echo Theme::render("widgets/monitoring/server-id", [
				'{id}'       => intval($ar['id']),
				'{title}'    => $this->db->HSC($ar['title']),
				'{online}'   => intval($ar['online']),
				'{slots}'    => intval($ar['slots']),
				'{progress}' => ((intval($ar['status']) === 1)) ? ceil(intval($ar['online']) / intval($ar['slots']) * 100) : 100,
				'{status}'   => (intval($ar['status']) === 1) ? Theme::render("widgets/monitoring/online") : Theme::render("widgets/monitoring/offline"),
			]);
		}

		return ob_get_clean();
	}

	public function content() {
		$this->core->header .= Theme::render("widgets/monitoring/headers");

		return Theme::render("widgets/monitoring/index", [
			'{servers}' => $this->server_array()
		]);
	}
}
?>
