<?php

// Reference: https://avenir.ro/fat-free-framework-tutorials/fat-free-framework-routes-configuration-file/
// Kickstart the framework
$f3=require('lib/fatfree/base.php');

$f3->set('DEBUG', 3);
if ((float)PCRE_VERSION<7.9)
	trigger_error('PCRE version is out of date');

// Load configuration
$f3->config('app/config/config.ini');
$f3->config('app/config/routes.cfg');

$f3->config('.env.ini');

$f3->run();
