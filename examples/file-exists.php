<?php

/**
 * Usage php file-exists.php /path/to/a/remote/file
 *
 * Examples:
 *
 *    php file-exists.php /files/file.txt
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . 'bootstrap.php';
use dbeurive\Ftp\Ftp;
use dbeurive\Ftp\Exception as FtpException;

$path = $argv[1];

$options = array(Ftp::OPTION_PORT => $port, Ftp::OPTION_TIMEOUT => 60);
$ftp = new Ftp($host, $options);
$ftp->connect();
$ftp->login($user, $password);

$status = null;

try {
    $status = $ftp->fileExists($path);
} catch (FtpException $e) {
    printf("ERROR: %s\n", $e->getMessage());
}


if (true === $status) {
    printf("The file '%s' exists.\n", $path);
} else {
    printf("The file '%s' does not exist, or this past point to a directory.\n", $path);
}


