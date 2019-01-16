<?php

/**
 * Usage php delete.php /path/to/a/remote/file
 *
 * Examples:
 *
 *    php delete.php ./files/file.txt
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . 'bootstrap.php';
use dbeurive\Ftp\Ftp;
use dbeurive\Ftp\Exception as FtpException;

$remote_path = $argv[1];

$options = array(Ftp::OPTION_PORT => $port, Ftp::OPTION_TIMEOUT => 60);
$ftp = new Ftp($host, $options);
$ftp->connect();
$ftp->login($user, $password);

try {
    $ftp->delete($remote_path);
} catch (FtpException $e) {
    printf("ERROR: %s\n", $e->getMessage());
}