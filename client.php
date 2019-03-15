<?php
/**
 * Created by PhpStorm.
 * User: gengyang
 * Date: 2017/8/8
 * Time: 10:06
 */
$client = new swoole_client(SWOOLE_SOCK_TCP);
if (!$client->connect('127.0.0.1', 9501, -1))
{
    exit("connect failed. Error: {$client->errCode}\n");
}
$client->send("hello world".rand(0,10)."\n");
echo $client->recv();
$client->close();