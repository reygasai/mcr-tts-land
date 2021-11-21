<?php

if(!defined("MCR")){ exit("Hacking Attempt!"); }

ini_set("upload_max_filesize", "50M");
ini_set("post_max_size", "50M");
@date_default_timezone_set('Europe/Moscow');

define('MCR_ROOT', dirname(__FILE__).'/');
define('MCR_MODE_PATH', MCR_ROOT.'modules/');
define('MCR_TOOL_PATH', MCR_ROOT.'engine/');
define('MCR_LIBS_PATH', MCR_TOOL_PATH.'libs/');
define('MCR_MON_PATH', MCR_TOOL_PATH.'monitoring/');
define('MCR_SIDE_PATH', MCR_ROOT.'blocks/');
define('MCR_LANG_PATH', MCR_ROOT.'language/');
define('MCR_CONF_PATH', MCR_ROOT.'configs/');
define('MCR_UPL_PATH', MCR_ROOT.'uploads/');
define('MCR_CACHE_PATH', MCR_ROOT.'cache/');

session_save_path(MCR_UPL_PATH.'tmp');
if(!session_start()){ session_start(); }

// Set default charset
header('Content-Type: text/html; charset=UTF-8');

//Load render class and notify method
require_once(MCR_TOOL_PATH.'Theme.php');

require_once(MCR_TOOL_PATH.'Utils.php');

// Load core
require_once(MCR_TOOL_PATH.'core.class.php');

// Create new core object
$core = new core();

// Debug
ini_set("display_errors", $core->cfg->main['debug']);
$warn_type = ($core->cfg->main['debug']) ? E_ALL : 0;
error_reporting($warn_type);


// Csrf security validation
$core->csrf_check();
?>
