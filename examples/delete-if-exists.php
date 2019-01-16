<?php

/**
 * Usage php delete-if-entry-exists.php /path/to/a/remote/file
 *
 * Examples:
 *
 *    php delete-if-entry-exists.php ./files/file.txt
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . 'bootstrap.php';
use dbeurive\Ftp\Ftp;
use dbeurive\Ftp\Exception as FtpException;

$remote_path = $argv[1];

$options = array(Ftp::OPTION_PORT => $port, Ftp::OPTION_TIMEOUT => 60);
$ftp = new Ftp($host, $options);
$ftp->connect();
$ftp->login($user, $password);

$status = null;

// Do it once... so we delete the file.

try {
    $status = $ftp->deleteIfExists($remote_path);
} catch (FtpException $e) {
    printf("ERROR: %s\n", $e->getMessage());
}

if ($status) {
    print("The file has been deleted.\n");
} else {
    print("The file has not been deleted because it does not exist.\n");
}

// Do it again... so we can't delete the file since is does not exist anymore.

try {
    $status = $ftp->deleteIfExists($remote_path);
} catch (FtpException $e) {
    printf("ERROR: %s\n", $e->getMessage());
}

if ($status) {
    print("The file has been deleted.\n");
} else {
    print("The file has not been deleted because it does not exist.\n");
}
