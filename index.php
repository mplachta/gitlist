<?php

/**
 * GitList 0.3
 * https://github.com/klaussilveira/gitlist
 */

require 'vendor/autoload.php';

// Load configuration
$config = new GitList\Config('config.ini');
$config->set('git', 'repositories', rtrim($config->get('git', 'repositories'), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);

// Startup and configure Silex application
$app = new GitList\Application($config, __DIR__);

// Mount the controllers
$app->mount('', new GitList\Controller\MainController());
$app->mount('', new GitList\Controller\BlobController());
$app->mount('', new GitList\Controller\CommitController());
$app->mount('', new GitList\Controller\TreeController());
$app->mount('/tag', new GitList\Controller\TagController());
$app->run();
