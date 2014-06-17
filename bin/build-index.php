<?php

chdir(dirname(__DIR__));

// Setup autoloading
require 'init_autoloader.php';

Zend\Mvc\Application::init(require 'config/application.config.php');

$indexer = new Application\SearchEngine\Indexer();
$indexer->buildIndex();

echo "Done\n";
