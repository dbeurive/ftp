<?php

require_once '..' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

$host = null;
$user = null;
$password = null;
$port = null;
$timeout = null;

$errors = array();
if (false === $host = getenv('FTP_HOST')) {
    $error[] = "Environment variable FTP_HOST is not defined.";
}

if (false === $user = getenv('FTP_USER')) {
    $error[] = "Environment variable FTP_USER is not defined.";
}

if (false === $password = getenv('FTP_PASSWORD')) {
    $error[] = "Environment variable FTP_PASSWORD is not defined.";
}

if (false === $port = getenv('FTP_PORT')) {
    $port = 21;
}

if (false === $timeout = getenv('FTP_TIMEOUT')) {
    $timeout = 10;
}

if (count($errors) > 0) {
    printf("The script could not be executed because some configuration parameter are not defined:\n\n%s\n", implode("\n", $errors));
    exit(1);
}

