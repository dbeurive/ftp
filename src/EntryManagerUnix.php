<?php

/**
 * This file contains the implementation of the default entry manager suitable for typical UNIX FTP servers.
 */

namespace dbeurive\Ftp;


/**
 * Class EntryManagerUnix
 *
 * This class implements the default entry manager suitable for typical UNIX FTP servers.
 *
 * @package dbeurive\Ftp
 */

class EntryManagerUnix extends AbstractEntryManager
{
    const ENTRY_FIELD_PERMISSIONS = 'permission';
    const ENTRY_FIELD_NUMBER = 'number';
    const ENTRY_FIELD_OWNER = 'owner';
    const ENTRY_FIELD_GROUP = 'group';
    const ENTRY_FIELD_SIZE = 'size';
    const ENTRY_FIELD_MONTH = 'month';
    const ENTRY_FIELD_DAY = 'day';
    const ENTRY_FIELD_TIME = 'time';
    const ENTRY_FIELD_NAME = 'name';
    const ENTRY_FIELD_TYPE = 'type';

    const ENTRY_TYPE_DIRECTORY = 0;
    const ENTRY_TYPE_FILE = 1;
    const ENTRY_TYPE_LINK = 2;
    const ENTRY_TYPE_UNKNOWN = 3;

    const FIELDS = array(
        self::ENTRY_FIELD_PERMISSIONS,
        self::ENTRY_FIELD_NUMBER,
        self::ENTRY_FIELD_OWNER,
        self::ENTRY_FIELD_GROUP,
        self::ENTRY_FIELD_SIZE,
        self::ENTRY_FIELD_MONTH,
        self::ENTRY_FIELD_DAY,
        self::ENTRY_FIELD_TIME,
        self::ENTRY_FIELD_NAME
    );

    /** @var array This associative array contains the fields extracted from one line of the text returned by the FTP
     *       command LIST. Keys are:
     *
     *       - ENTRY_FIELD_PERMISSIONS
     *       - ENTRY_FIELD_NUMBER
     *       - ENTRY_FIELD_OWNER
     *       - ENTRY_FIELD_GROUP
     *       - ENTRY_FIELD_SIZE
     *       - ENTRY_FIELD_MONTH
     *       - ENTRY_FIELD_DAY
     *       - ENTRY_FIELD_TIME
     *       - ENTRY_FIELD_NAME
     *       - ENTRY_FIELD_TYPE
     */
    private $__fields = array();

    /** @var string Path to the directory from which this entry comes from. */
    private $__parent_path;


    /**
     * @see AbstractEntryManager
     */
    public function __construct($in_entry_line, $in_parent_path)
    {
        $this->__parent_path = $in_parent_path;
        $this->__fields = self::parse($in_entry_line);
    }

    /**
     * @see AbstractEntryManager
     */
    public function isFile() {
        return self::ENTRY_TYPE_FILE == $this->__fields[self::ENTRY_FIELD_TYPE];
    }

    /**
     * @see AbstractEntryManager
     */
    public function isDirectory() {
        return self::ENTRY_TYPE_DIRECTORY == $this->__fields[self::ENTRY_FIELD_TYPE];
    }

    /**
     * @see AbstractEntryManager
     */
    public function isLink() {
        return self::ENTRY_TYPE_LINK == $this->__fields[self::ENTRY_FIELD_TYPE];
    }

    /**
     * @see AbstractEntryManager
     */
    public function getBaseName() {
        return $this->__fields[self::ENTRY_FIELD_NAME];
    }

    /**
     * @see AbstractEntryManager
     */
    public function getParentPath() {
        return $this->__parent_path;
    }

    /**
     * @see AbstractEntryManager
     */
    public function getPath() {
        return sprintf('%s/%s', $this->__parent_path, $this->__fields[self::ENTRY_FIELD_NAME]);
    }

    /**
     * @see AbstractEntryManager
     */
    public function __toString() {
        $max = max(array_map(function($v) { return strlen($v); }, self::FIELDS));
        $lines = array();
        foreach (self::FIELDS as $_label) {
            $lines[] = sprintf("%-${max}s: \"%s\"", $_label, $this->__fields[$_label]);
        }
        return implode("\n", $lines);
    }

    /**
     * Return all the data that describe the entry.
     * @return array
     */
    public function getFields() {
        return $this->__fields;
    }

    /**
     * @param string $in_entry_text Text returned by the FTP command LIST that represents the entry.
     *        The FTP command LIST returns a text that looks something like:
     *            -rw-r--r--    1 0        0               1 Jan 15 14:08 file0.txt
     *            -rw-r--r--    1 0        0               2 Jan 15 14:08 file1.txt
     *            -rw-r--r--    1 0        0               3 Jan 15 14:08 file2.txt
     *            -rw-r--r--    1 0        0               4 Jan 15 14:08 file3.txt
     *            drwxr-xr-x    2 0        0            4096 Jan 15 14:08 r1
     *            drwxr-xr-x    2 0        0            4096 Jan 15 14:08 r2
     *            drwxr-xr-x    2 0        0            4096 Jan 15 14:08 r3
     *            drwxr-xr-x    2 0        0            4096 Jan 15 14:08 r4
     * @return array The method returns an associative array. This associative array contains the keys listed below:
     *         - ENTRY_FIELD_PERMISSIONS
     *         - ENTRY_FIELD_NUMBER
     *         - ENTRY_FIELD_OWNER
     *         - ENTRY_FIELD_GROUP
     *         - ENTRY_FIELD_SIZE
     *         - ENTRY_FIELD_MONTH
     *         - ENTRY_FIELD_DAY
     *         - ENTRY_FIELD_TIME
     *         - ENTRY_FIELD_NAME
     *         - ENTRY_FIELD_TYPE
     * @throws Exception
     */
    static public function parse($in_entry_text) {
        $parsed_data = preg_split('/\s+/', $in_entry_text);

        if ( count($parsed_data) < count(self::FIELDS)) {
            throw new Exception(sprintf('Cannot parse the text "%s"', $in_entry_text));
        }

        $s = $in_entry_text;
        for ($i=0; $i<8; $i++) {
            $s = preg_replace('/^\s+/', '', $s);
            $s = substr($s, strlen($parsed_data[$i]));
        }

        $name = substr($s, 1);
        $p = array(self::ENTRY_FIELD_NAME => $name);

        for ($i=0; $i<8; $i++) {
            $p[self::FIELDS[$i]] = $parsed_data[$i];
        }
        $type = substr($p[self::ENTRY_FIELD_PERMISSIONS], 0, 1);

        switch ($type) {
            case 'd': $p[self::ENTRY_FIELD_TYPE] = self::ENTRY_TYPE_DIRECTORY; break;
            case '-': $p[self::ENTRY_FIELD_TYPE] = self::ENTRY_TYPE_FILE; break;
            case 'l': $p[self::ENTRY_FIELD_TYPE] = self::ENTRY_TYPE_LINK; break;
            default:  $p[self::ENTRY_FIELD_TYPE] = self::ENTRY_TYPE_UNKNOWN;
        }

        // Sanity tests.

        if (1 !== preg_match('/^[a-z\-](r|\-)(w|\-)(x|\-)(r|\-)(w|\-)(x|\-)(r|\-)(w|\-)(x|\-)$/i', $p[self::ENTRY_FIELD_PERMISSIONS])) {
            throw new Exception(sprintf('Cannot parse the text "%s" (invalid permission mask: %s)',
                $in_entry_text,
                $p[self::ENTRY_FIELD_PERMISSIONS]));
        }

        if (1 !== preg_match('/^\d+$/', $p[self::ENTRY_FIELD_NUMBER])) {
            throw new Exception(sprintf('Cannot parse the text "%s" (invalid number: %s)',
                $in_entry_text,
                $p[self::ENTRY_FIELD_NUMBER]));
        }

        if (1 !== preg_match('/^\d+$/', $p[self::ENTRY_FIELD_OWNER])) {
            throw new Exception(sprintf('Cannot parse the text "%s" (invalid owner: %s)',
                $in_entry_text,
                $p[self::ENTRY_FIELD_OWNER]));
        }

        if (1 !== preg_match('/^\d+$/', $p[self::ENTRY_FIELD_GROUP])) {
            throw new Exception(sprintf('Cannot parse the text "%s" (invalid group: %s)',
                $in_entry_text,
                $p[self::ENTRY_FIELD_GROUP]));
        }

        if (1 !== preg_match('/^\d+$/', $p[self::ENTRY_FIELD_SIZE])) {
            throw new Exception(sprintf('Cannot parse the text "%s" (invalid size: %s)',
                $in_entry_text,
                $p[self::ENTRY_FIELD_SIZE]));
        }

        if (1 !== preg_match('/^\d+$/', $p[self::ENTRY_FIELD_DAY])) {
            throw new Exception(sprintf('Cannot parse the text "%s" (invalid day: %s)',
                $in_entry_text,
                $p[self::ENTRY_FIELD_DAY]));
        }

        if (1 !== preg_match('/^\d{1,2}:\d{1,2}$/', $p[self::ENTRY_FIELD_TIME])) {
            throw new Exception(sprintf('Cannot parse the text "%s" (invalid time: %s)',
                $in_entry_text,
                $p[self::ENTRY_FIELD_TIME]));
        }

        if (1 !== preg_match('/^[a-z]{3}$/i', $p[self::ENTRY_FIELD_MONTH])) {
            throw new Exception(sprintf('Cannot parse the text "%s" (invalid month: %s)',
                $in_entry_text,
                $p[self::ENTRY_FIELD_MONTH]));
        }

        if (0 == strlen($p[self::ENTRY_FIELD_NAME])) {
            throw new Exception(sprintf('Cannot parse the text "%s" (invalid entry name: the name is empty)',
                $in_entry_text));
        }

        return $p;
    }


}