<?php


namespace dbeurive\Ftp;


abstract class AbstractEntry
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

    abstract public function isFile();
    abstract public function isDirectory();
    abstract public function isLink();
    abstract public function getBaseName();
    abstract public function getParentPath();
    abstract public function getPath();
    abstract public function __toString();
}
