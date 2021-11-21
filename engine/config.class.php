<?php

if(!defined("MCR")){ exit("Hacking Attempt!"); }

class config {
	public $main = [];
	public $db = [];
	public $func = [];
	public $pagin	= [];
	public $mail = [];

	public function __construct()
	{
		$this->main	= Utils::loadConfig('main/site');
		$this->mail	= Utils::loadConfig('main/mail');
		$this->db	= Utils::loadConfig('main/db');
		$this->func	= Utils::loadConfig('main/functions');
		$this->pagin = Utils::loadConfig('main/pagin');
	}

	public function tabname($name)
	{
		return $this->db['tables'][$name]['name'];
	}

	public function savecfg($cfg = [], $file = 'site.php', $var = 'site')
	{
		if(!is_array($cfg) || empty($cfg)) return false;

		$filename = MCR_CONF_PATH.'main/'.$file;

		$txt  = '<?php'.PHP_EOL;
		$txt .= 'return '.var_export($cfg, true).';'.PHP_EOL;
		$txt .= '?>';

		$result = file_put_contents($filename, $txt);

		return ($result === false) ? false : true;
	}
}


?>
