#!/usr/bin/php
<?php

if(strtolower(php_sapi_name()) !== 'cli')
{
    die("script can only run in cli mode");
}

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('memory_limit', '1024M');
set_time_limit(0);

$args =
[
    'mc' => 'mc',
    'target' => null,
    'bucket' => null,
    'path' => null,
    'token' => null,
    'url' => null,
    'delay' => 1
];

if($argv)
{
    foreach((array)$argv as $arg)
    {
        $arg = trim($arg);
        if(substr($arg, 0, 2) === '--')
        {
            if(stripos($arg, '=') !== false){
                $arg = explode('=', $arg);
                $args[substr($arg[0], 2)] = $arg[1];
            }else{
                $args[substr($arg, 2)] = null;
            }
        }
    }
}

if(empty($args['target'])) die("need --target value" . PHP_EOL);
if(empty($args['bucket'])) die("need --bucket value" . PHP_EOL);
if(empty($args['token'])) die("need --token value" . PHP_EOL);
if(empty($args['url'])) die("need --url value" . PHP_EOL);

$out = [];
if(!empty($args['path']))
{
    $bucket = sprintf('%s/%s', rtrim($args['bucket'], ' /'), ltrim($args['path'], ' /'));
}else{
    $bucket = rtrim($args['bucket'], ' /');
}

$cmd = vsprintf('%s ls -r %s/%s', [$args['mc'], $args['target'], $bucket]);
exec($cmd, $out);
if(!empty($out))
{
    foreach($out as $line)
    {
        $line = preg_replace('=^(.*[0-9.]{1,}((k|m|g)ib|b)\s)=i', '', $line);
        if(!empty($line))
        {
            $url = sprintf('%s?token=%s&file=%s', $args['url'], $args['token'], $line);
            $res = file_get_contents($url);
            if($res === false)
            {
                echo sprintf('%s, error (%s)', $url, 'stream read error') . PHP_EOL;
            }else{
                echo sprintf('%s, success (%d)', $url, $res) . PHP_EOL;
            }
            sleep((int)$args['delay']);
        }
    }
}