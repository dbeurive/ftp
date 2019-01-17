<?php

/**
 * Usage php entry-exists.php /path/to/a/remote/file_or_directory
 *
 * Examples:
 *
 *    php entry-exists.php /files
 *    php entry-exists.php ./files
 *    php entry-exists.php files
 *    php entry-exists.php /files/r1
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . 'bootstrap.php';
use dbeurive\Ftp\Ftp;

$path = $argv[1];

$options = array(Ftp::OPTION_PORT => $port, Ftp::OPTION_TIMEOUT => 60);
$ftp = new Ftp($host, $options);
$ftp->connect();
$ftp->login($user, $password);

$entry = $ftp->entryExists($path);
if (true === $entry) {
    printf("The entry '%s' exists.\n", $entry);
} elseif (false === $entries) {
    printf("The entry '%s' does not exist.\n", $entry);
} else {
    printf("The entry '%s' exists. And that's the information we have about it:\n\n%s\n\n", $entry, $entry);

}
