#!/usr/bin/php
<?php

if(strtolower(php_sapi_name()) !== 'cli')
{
    die("script can only run in cli mode");
}

error_reporting(E_ALL);
ini_set('display_errors', 1);
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
    'delay' => 1,
    'recursive' => null,
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

if(!function_exists('curl_init'))
{
    die("need php curl" . PHP_EOL);
}

if(empty($args['target'])) die("need --target value" . PHP_EOL);
if(empty($args['bucket'])) die("need --bucket value" . PHP_EOL);
if(empty($args['token'])) die("need --token value" . PHP_EOL);
if(empty($args['url'])) die("need --url value" . PHP_EOL);

$out = [];
if(array_key_exists('update', $args))
{
    $update = 1;
}else{
    $update = 0;
}
if(!empty($args['path']))
{
    $bucket = sprintf('%s/%s', rtrim($args['bucket'], ' /'), ltrim($args['path'], ' /'));
}else{
    $bucket = rtrim($args['bucket'], ' /');
}
if(!empty($args['recursive']))
{
    $ls = 'ls -r';
}else{
    $ls = 'ls';
}
$cmd = vsprintf('%s %s %s/%s', [$args['mc'], $ls, $args['target'], $bucket]);
exec($cmd, $out);
if(!empty($out))
{
    $ch = curl_init();
    foreach($out as $line)
    {
        $line = preg_replace('=^(.*[0-9.]{1,}((k|m|g)ib|b)\s)=i', '', $line);
        if(!empty($line) && !preg_match('=\/$=i', $line))
        {
            if(!empty($args['path']))
            {
                $file = sprintf('%s/%s', trim($args['path'], ' /'), $line);
            }else{
                $file = $line;
            }
            $url = sprintf('%s?token=%s&file=%s&update=%d', $args['url'], $args['token'], $file, $update);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $res = curl_exec($ch);
            if($res === false)
            {
                echo sprintf('%s, error (%s)', $url, curl_error($ch)) . PHP_EOL;
            }else{
                echo sprintf('%s, success (%s)', $url, $res) . PHP_EOL;
            }
            sleep((int)$args['delay']);
        }
    }
    curl_close($ch);
}