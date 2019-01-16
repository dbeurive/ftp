<?php

/**
 * Usage php mkdir.php /path/to/a/remote/directory
 *
 * Examples:
 *
 *    php mkdir.php /
 *    php mkdir.php ./files
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . 'bootstrap.php';
use dbeurive\Ftp\Ftp;
use dbeurive\Ftp\Exception as FtpException;

$path = isset($argv[1]) ? $argv[1] : '.';

$options = array(Ftp::OPTION_PORT => $port, Ftp::OPTION_TIMEOUT => 60);
$ftp = new Ftp($host, $options);
$ftp->connect();
$ftp->login($user, $password);

try {
    $ftp->mkdir($path);
} catch (FtpException $e) {
    printf("ERROR: %s\n", $e->getMessage());
}


