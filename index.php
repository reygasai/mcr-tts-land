<?php
define('DEBUG_PLT', microtime(true));
define('MCR', true);

require 'system.php';

$mode = (isset($_GET['mode'])) ? $_GET['mode'] : $core->cfg->main['s_dpage'];

if($core->cfg->func['close'] && !$core->is_access('sys_adm_main')){
	if($core->cfg->func['close_time'] <= 0 || $core->cfg->func['close_time'] > time()){
		$mode = ($mode=='auth') ? 'auth' : 'close';
	}
}

$content = $core->load_mode($mode);

if ($core->layout === 'ajax') {
  header("Content-Type: application/json");
  die($content);
}

$tags = array_merge([
	'{content}' => $content,
	'{title}' => $core->title,
	'{site_name}' => $core->cfg->main['s_name'],
	'{headers}' => Theme::render('headers', [
		'{csrf_key}' => CSRF_SECURE_KEY
	]) . ' ' . $core->header,
	'{notify}' => Theme::notify(),
	'{breadcrumbs}' => $core->bc,
	'{admin_url}' => ADMIN_URL,
	], $core->sys_global_tags());

echo Theme::render("layouts/" . $core->layout, $tags);

if(!$core->cfg->main['debug'] || !@$core->user->permissions->sys_debug){ exit; }

?>
