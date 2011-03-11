<?php

//require_once 'capall/manul/mnlClassLoader.php';
require_once '../manul/mnlClassLoader.php';

require_once 'Zend/Loader/Autoloader.php';

$autoloader = Zend_Loader_Autoloader::getInstance();

$loader = new mnlClassLoader();
spl_autoload_register(array($loader, 'loadClass'));
