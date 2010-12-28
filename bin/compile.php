<?php
//To be done
return;
require_once '../bootstrap.php';

Cli::getInstance()->setRequiredParams('path','base');

$path = Cli::getInstance()->getParam('path');

if (is_dir($path)) {

} else if (is_file($path)) {

} else {
    Cli::getInstance()->displayErrorAndExit('Not a valid file or directory: '.$path);
}