<?php

/**
 * This file contains the implementation of the model that represents the minimal interface for an entry manager.
 */

namespace dbeurive\Ftp;

/**
 * Class AbstractEntryManager
 *
 * This class defines the minimal interface for an entry manager.
 *
 * @package dbeurive\Ftp
 */

abstract class AbstractEntryManager
{

    /**
     * Create and return an entry.
     * @param string $in_entry_line Line that represents the entry, as returned by the FTP command LIST.
     *        See http://www.nsftools.com/tips/RawFTP.htm#LIST
     * @param string $in_parent_path Path to the directory that contains the entry.
     * @return object The method returns en entry.
     * @throws \ReflectionException
     * @see @see http://www.nsftools.com/tips/RawFTP.htm#LIST
     */
    static function getInstance($in_entry_line, $in_parent_path) {
        $class_name = get_called_class();
        $class = new \ReflectionClass($class_name);
        return $class->newInstanceArgs(array($in_entry_line, $in_parent_path));
    }

    /**
     * Entry constructor.
     * @param string $in_entry_line One line of text of the whole text returned by the FTP command LIST.
     *        On a typical Linux host, the FTP command LIST returns a text that looks something like:
     *            -rw-r--r--    1 0        0               1 Jan 15 14:08 file0.txt
     *            -rw-r--r--    1 0        0               2 Jan 15 14:08 file1.txt
     *            -rw-r--r--    1 0        0               3 Jan 15 14:08 file2.txt
     *            -rw-r--r--    1 0        0               4 Jan 15 14:08 file3.txt
     *            drwxr-xr-x    2 0        0            4096 Jan 15 14:08 r1
     *            drwxr-xr-x    2 0        0            4096 Jan 15 14:08 r2
     *            drwxr-xr-x    2 0        0            4096 Jan 15 14:08 r3
     *            drwxr-xr-x    2 0        0            4096 Jan 15 14:08 r4
     * @param string $in_parent_path Path to the directory that is being listed.
     * @throws Exception
     */
    abstract public function __construct($in_entry_line, $in_parent_path);

    /**
     * Test whether the entry is a file or not.
     * @return bool If the entry is a file, then the method returns the value true.
     *         Otherwise, it returns the value false.
     */
    abstract public function isFile();

    /**
     * Test whether the entry is a directory or not.
     * @return bool If the entry is a directory, then the method returns the value true.
     *         Otherwise, it returns the value false.
     */
    abstract public function isDirectory();

    /**
     * Test whether the entry is a link or not.
     * @return bool If the entry is a link, then the method returns the value true.
     *         Otherwise, it returns the value false.
     */
    abstract public function isLink();

    /**
     * Return the basename of the entry.
     * @return string The base name of the entry.
     */

    abstract public function getBaseName();

    /**
     * Return the path to the (remote) directory that contains the entry.
     * @return string The path to the (remote) directory that contains the entry.
     */
    abstract public function getParentPath();

    /**
     * Return the path to the entry.
     * @return string The path to the entry.
     */
    abstract public function getPath();

    /**
     * This magic method returns a textual representation of the entry.
     * @return string A textual representation of the entry.
     */
    abstract public function __toString();
}
