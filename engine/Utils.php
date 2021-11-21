<?php

/**
 * Utils
 */
class Utils
{
  public static function loadConfig($name)
  {
    $filedir = MCR_CONF_PATH . $name . '.php';
    if (!file_exists($filedir)) return false;
    return include_once $filedir;
  }

  public static function jsonResponse($message, $type = false, $data = [])
  {
    return json_encode([
      'message' => $message,
      'type' => $type,
      'data' => $data
    ]);
  }
}


?>
