<?php
define('BASEDIR',exec('pwd'));

if (BASEURL == 'BASEURL')
    throw new Exception("BASEURL must be defined!");

if (!is_dir(BASEDIR))
    throw new Exception(BASEDIR." not found!");

//Various functions
require_once 'lib/functions.php';

//DB Handling
require_once 'lib/Db.php';

//Localization
require_once 'lib/Locale.php';

//Mail handling
require_once 'lib/Mail.php';

//Filesystem handling
require_once 'lib/Url.php';
require_once 'lib/File.php';
require_once 'lib/Dir.php';

//String handling
require_once 'lib/String.php';

//MVC
require_once 'lib/Model.php';
require_once 'lib/View.php';
require_once 'lib/Controller.php';

//Main class
require_once 'lib/taglib/CoreTagLib.php';
require_once 'lib/taglib/FormTagLib.php';
require_once 'lib/Pimple.php';
define('CACHEDIR',Dir::normalize(BASEDIR).'cache');
Dir::ensure(CACHEDIR);


