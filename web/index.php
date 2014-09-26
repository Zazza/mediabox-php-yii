<?php

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: X-Requested-With, Content-Type');
header('Access-Control-Max-Age: 600');

require __DIR__.'/../vendor/autoload.php';

// change the following paths if necessary
$yii = __DIR__.'/../vendor/yiisoft/yii/framework/yii.php';
$config = __DIR__.'/../app/config/main.php';

// remove the following lines when in production mode
defined('YII_DEBUG') or define('YII_DEBUG',true);
// specify how many levels of call stack should be shown in each log message
defined('YII_TRACE_LEVEL') or define('YII_TRACE_LEVEL',3);

require_once($yii);
Yii::createWebApplication($config)->run();
