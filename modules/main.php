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
    }

    private function news_array() {
		$end = $this->cfg->pagin['news_main'];
		$query = $this->db->query("SELECT `n`.id, `n`.cid, `n`.title, `n`.text_html_short, `n`.uid, `n`.`data`,
										`c`.title AS `category`
									FROM `mcr_news` AS `n`
									LEFT JOIN `mcr_news_cats` AS `c` ON `c`.id=`n`.cid
									LIMIT $end");
		ob_start();
		
		if(!$query || $this->db->num_rows($query)<=0) {
            echo Theme::render("modules/news/news-none"); 
            return ob_get_clean(); 
        }

		while($ar = $this->db->fetch_assoc($query)) {
			$date = json_decode($ar['data'], true);

            echo Theme::render("modules/news/news-id", [
                '{id}' => intval($ar['id']),
                '{c_id}' => intval($ar['cid']),
                '{title}' => $this->db->HSC($ar['title']),
                '{category}' => $this->db->HSC($ar['category']),
                '{text}' => substr($ar['text_html_short'], 0, 280),
				'{uid}'  => intval($ar['uid']),
				'{date}' => date("F j, Y, g:i", intval($date['time_create'])) 
            ]); 
		}

		return ob_get_clean();
	}

	public function content() {
        return Theme::render("modules/main/index", [
            '{section_info}' => Theme::render("modules/main/information", [
                '{monitoring}' => $this->core->load_widget('monitoring'),
            ]),
            '{news}' => $this->news_array(),
        ]);
	}
}
?>