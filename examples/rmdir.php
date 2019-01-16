<?php

/**
 * Usage php rmdir.php /path/to/a/remote/directory
 *
 * Examples:
 *
 *    php rmdir.php /
 *    php rmdir.php ./files/r1
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
    // Create the directory first, so you won't have any problem with permissions.
    $ftp->mkdirRecursiveIfNotExist($path);
    // Then delete it.
    $ftp->rmdir($path);
} catch (FtpException $e) {
    printf("ERROR: %s\n", $e->getMessage());
}