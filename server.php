<?php
//引用自动加载
require(__DIR__.'/vendor/autoload.php');
//加载配置文件
$config = include(__DIR__.'/config/config.php');

$model = new Libs\Websocket($config['server']['port'],$config['safeCode']['key']);

$model->run();

