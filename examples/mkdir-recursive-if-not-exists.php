<?php

/**
 * Usage php mkdir-recursive-if-not-entry-exists.php /path/to/a/remote/directory
 *
 * Examples:
 *
 *    php mkdir-recursive-if-not-entry-exists.php /files/a1/a2/a3/a4
 *
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . 'bootstrap.php';
use dbeurive\Ftp\Ftp;
use dbeurive\Ftp\Exception as FtpException;

$path = isset($argv[1]) ? $argv[1] : '.';

$options = array(Ftp::OPTION_PORT => $port, Ftp::OPTION_TIMEOUT => 60);
$ftp = new Ftp($host, $options);
$ftp->connect();
$ftp->login($user, $password);

$status = null;

try {
    $status = $ftp->mkdirRecursiveIfNotExist($path);
} catch (FtpException $e) {
    printf("ERROR: %s\n", $e->getMessage());
}

if (true === $status) {
    print("The directory has been created on the remote host.\n");
} elseif (false === $status) {
    print("The directory could not be created because the given path points to a file.\n");
} elseif (is_null($status)) {
    print("The directory has not been created on the remote host because the directory already existed.\n");
} else {
    print("Unexpected error\n");
}
