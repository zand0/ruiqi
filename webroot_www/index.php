<?php

header('Content-Type:text/html;charset=utf-8');
date_default_timezone_set('PRC');
define('APP_ROOT', dirname(__DIR__));
define("APP_PATH",  dirname(__DIR__).'/app_www');
define("APP_CONFIG", APP_PATH.'/conf'); 

error_reporting(E_ALL ^ E_NOTICE);
//启用命名空间

//第二个参数用来区分开发环境、测试环境、生产环境配置 对应config中内容
$app  = new Yaf\Application( APP_CONFIG."/application.ini",'develop');

$app->bootstrap()->run();
