<?php

require_once '..' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

$host = null;
$user = null;
$password = null;
$port = null;
$timeout = null;

$errors = array();

if (false === $host = getenv('FTP_HOST')) {
    $errors[] = "Environment variable FTP_HOST is not defined.";
}

if (false === $user = getenv('FTP_USER')) {
    $errors[] = "Environment variable FTP_USER is not defined.";
}

if (false === $password = getenv('FTP_PASSWORD')) {
    $errors[] = "Environment variable FTP_PASSWORD is not defined.";
}

if (false === $port = getenv('FTP_PORT')) {
    $port = 21;
}

if (false === $timeout = getenv('FTP_TIMEOUT')) {
    $timeout = 10;
}


if (count($errors) > 0) {
    $script = __DIR__ . DIRECTORY_SEPARATOR . 'setenv.sh';
    printf("The script could not be executed because some configuration parameter are not defined:\n\n%s\n\n", implode("\n", $errors));
    printf("Did you \"sourced\" the script \"%s\" ?\n\n", $script);
    printf("If not, then execute the following command:\n\n. %s\n\n", $script);
    exit(1);
}



