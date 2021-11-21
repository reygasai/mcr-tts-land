<?php
if (!defined('MCR')) die ('Hacking Attempt!');

return [
  'host' => '',
  'user' => '',
  'pass' => '',
  'base' => '',
  'port' => 3306,
  'tables' =>
  array (
    'ugroups' =>
    array (
      'name' => 'mcr_groups',
      'fields' =>
      array (
        'id' => 'id',
        'title' => 'title',
        'text' => 'description',
        'color' => 'color',
        'perm' => 'permissions',
      ),
    ),
    'logs' =>
    array (
      'name' => 'mcr_logs',
      'fields' =>
      array (
        'id' => 'id',
        'uid' => 'uid',
        'msg' => 'message',
        'date' => 'date',
      ),
    ),
    'users' =>
    array (
      'name' => 'mcr_users',
      'fields' =>
      array (
        'id' => 'id',
        'group' => 'gid',
        'login' => 'login',
        'email' => 'email',
        'pass' => 'password',
        'uuid' => 'uuid',
        'salt' => 'salt',
        'tmp' => 'tmp',
        'is_skin' => 'is_skin',
        'is_cloak' => 'is_cloak',
        'ip_create' => 'ip_create',
        'ip_last' => 'ip_last',
        'color' => 'color',
        'date_reg' => 'time_create',
        'date_last' => 'time_last',
        'fname' => 'firstname',
        'lname' => 'lastname',
        'gender' => 'gender',
        'bday' => 'birthday',
        'ban_server' => 'ban_server',
        'auth_attempt_data' => 'auth_attempt_data',
        'realmoney' => 'realmoney',
      ),
    ),
  ),
];
?>
