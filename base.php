<?php
error_reporting(E_ALL);
session_start();
define('PATH', __DIR__);

require PATH . '/lib/Database.php';
require PATH . '/lib/Template.php';
$tpl = new Template(PATH.'/templates', PATH.'/cache/tpl', $global_pagevars);
