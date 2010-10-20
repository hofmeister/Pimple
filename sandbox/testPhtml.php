<?php
define('BASEURL',dirname($_SERVER['SCRIPT_NAME']).'/');
require_once '../bootstrap.php';

$phtml = new Phtml();
//echo '<pre>';
//$phtml->setDebug(true);
$node = $phtml->read(file_get_contents('phtml.php'));
//echo '</pre>';
highlight_string($node->toPHP());