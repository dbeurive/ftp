<?php

/**
 * Usage php directory-exists.php /path/to/a/remote/file_or_directory
 *
 * Examples:
 *
 *    php directory-exists.php /files
 *    php directory-exists.php ./files
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
    $status = $ftp->directoryExists($path);
} catch (FtpException $e) {
    printf("ERROR: %s\n", $e->getMessage());
}


if (true === $status) {
    printf("The directory '%s' exists.\n", $path);
} else {
    printf("The directory '%s' does not exist, or this past point to a file.\n", $path);
}
