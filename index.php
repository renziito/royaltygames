<?php

set_time_limit(0);
$yii = dirname(__FILE__) . '/vendor/yiisoft/yii/framework/yii.php';
$config = dirname(__FILE__) . '/protected/config/main.php';

defined('YII_DEBUG') or define('YII_DEBUG', false);
if ($_SERVER['SERVER_NAME'] == 'localhost') {
    defined('YII_DEBUG') or define('YII_DEBUG', true);
}

defined('YII_TRACE_LEVEL') or define('YII_TRACE_LEVEL', 3);

require_once($yii);
$loader = require(__DIR__ . '/vendor/autoload.php');
Yii::$classMap = $loader->getClassMap();
Yii::createWebApplication($config)->run();
