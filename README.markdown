Manul synchronization framework
===============================

Simple two system (at this moment) synchronization framework.

Requirements
------------

* PHP >= 5.2
* Zend Framework (Zend_Registry, Zend_Log, Zend_Db, Zend_Queue, Zend_Validate) in include_path

Installation
------------

Core framework package is available over PEAR channel:

    pear channel-discover capall.shockov.com
    pear install capall/manul

Usage
-----

    require_once 'capall/manul/mnlClassLoader.php';
    
    $loader = new mnlClassLoader();
    spl_autoload_register(array($loader, 'loadClass'));
    
    // Your framework's bootstrap and other code here...
