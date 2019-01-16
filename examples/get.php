<?php

/**
 * Usage php get.php /path/to/a/remote/file /path/to/local/file
 *
 * Examples:
 *
 *    php get.php ./files/file.txt /tmp/my_file.txt
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . 'bootstrap.php';
use dbeurive\Ftp\Ftp;
use dbeurive\Ftp\Exception as FtpException;

$remote_path = isset($argv[1]) ? $argv[1] : '.';
$local_path = isset($argv[2]) ? $argv[2] : 'file.txt';

$options = array(Ftp::OPTION_PORT => $port, Ftp::OPTION_TIMEOUT => 60);
$ftp = new Ftp($host, $options);
$ftp->connect();
$ftp->login($user, $password);

try {
    $ftp->get($local_path, $remote_path);
} catch (FtpException $e) {
    printf("ERROR: %s\n", $e->getMessage());
}