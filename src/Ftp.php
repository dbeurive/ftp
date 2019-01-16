<?php

/**
 * This file implements a wrapper around the FTp functionalities provided by PHP.
 */

namespace dbeurive\Ftp;

/**
 * Class Ftp
 *
 * This class implements a wrapper around the FTp functionalities provided by PHP.
 *
 * @package dbeurive\Ftp
 */

class Ftp
{
    const OPTION_PORT       = 'port';
    const OPTION_TIMEOUT    = 'timeout';

    const ENTRY_PERMISSIONS = 'permission';
    const ENTRY_NUMBER = 'number';
    const ENTRY_OWNER = 'owner';
    const ENTRY_GROUP = 'group';
    const ENTRY_SIZE = 'size';
    const ENTRY_MONTH = 'month';
    const ENTRY_DAY = 'day';
    const ENTRY_TIME = 'time';
    const ENTRY_NAME = 'name';
    const ENTRY_TYPE = 'type';

    const ENTRY_TYPE_DIRECTORY = 'directory';
    const ENTRY_TYPE_FILE = 'file';
    const ENTRY_TYPE_LINK = 'link';
    const ENTRY_TYPE_UNKNOWN = 'unknown';

    /** @var string */
    private $__userName;
    /** @var string */
    private $__password;
    /** @var string */
    private $__host;
    /** @var array */
    private $__options = array(self::OPTION_PORT => 21, self::OPTION_TIMEOUT => 90);
    /** @var resource A FTP stream */
    private $__ftpStream;
    /** @var bool */
    private $__logged = false;

    /**
     * Ftp constructor.
     * @param string $in_host Name or IP address of the host that runs the FTP server.
     * @param array $in_options This array may contain the following entries:
     *        - OPTION_PORT. The default value is 21.
     *        - OPTION_TIMEOUT. The default value is 90.
     * @throws Exception
     */
    public function __construct ($in_host, array $in_options=array()) {

        /**
         * @var string $_option
         * @var int|bool $_value
         */
        foreach ($in_options as $_option => $_value) {
            $_option = strtolower($_option);

            if (self::OPTION_TIMEOUT === $_option) {
                if (1 !== preg_match('/^\d+$/', $_value)) { throw new Exception(sprintf('Invalid value for option "%s": %s (not an integer).', $_option, $_value)); }
                $_value = intval($_value);
            } elseif (self::OPTION_PORT === $_option) {
                if (1 !== preg_match('/^\d+$/', $_value)) { throw new Exception(sprintf('Invalid value for option "%s": %s (not an integer).', $_option, $_value)); }
                $_value = intval($_value);
            } else {
                throw new Exception(sprintf('Unknown option "%s".', $_option));
            }

            $this->__options[$_option] = $_value;
        }

        $this->__host = $in_host;
    }

    /**
     * Open a connexion to the server.
     * @return Ftp $this.
     * @throws Exception If the client could not open a connection to the server, then the method throws an exception.
     */
    public function connect() {

        if (false === @$this->__ftpStream = ftp_connect($this->__host,
                $this->__options[self::OPTION_PORT],
                $this->__options[self::OPTION_TIMEOUT])) {

            $error = error_get_last();
            throw new Exception(sprintf('Cannot open a connection to the remote host "%s" (port: %d, timeout: %d). %s',
                $this->__host,
                $this->__options[self::OPTION_PORT],
                $this->__options[self::OPTION_TIMEOUT],
                $error['message']));
        }
        return $this;
    }

    /**
     * Authenticate to the server.
     * @param string $in_user_name Login.
     * @param string $in_password Password.
     * @return bool If the client has already authenticated, the method returns the value false (and no authentication is
     *         performed). Otherwise, the method returns the value true.
     * @throws Exception If the provided credential (login, password) is not valid, then the method throws an exception.
     */
    public function login($in_user_name, $in_password) {

        if ($this->__logged && $this->__userName == $in_user_name && $this->__password == $in_password) {
            return false;
        }

        $this->__userName = $in_user_name;
        $this->__password = $in_password;

        if (false === @ftp_login($this->__ftpStream,
                $this->__userName,
                $this->__password)) {
            $error = error_get_last();
            throw new Exception(sprintf('Cannot log into the remote host "%s" with user "%s" and password "%s". %s',
                $this->__host,
                $this->__userName,
                $this->__password,
                $error['message']));
        }
        $this->__logged = true;
        return true;
    }

    /**
     * Test whether the client has authenticated to the server.
     * @return bool If the client is authenticated, then the method returns the value true.
     *         Otherwise, it returns the value false.
     */
    public function isLogged() {
        return $this->__logged;
    }

    /**
     * List the content of a directory identified by its given path. This function parses the output returned by the server.
     * @param string $in_opt_dir Path to the directory.
     *        The default value is ".".
     * @param bool $in_opt_throw_exception_on_error Specify whether the method should throw an exception if the client
     *        could not perform the required action.
     *        - true: the method will throw an exception.
     *        - false: the method will will return the value false.
     * @return array|bool Depending on the context:
     *         - If the client could not perform the required action:
     *           + If $in_opt_throw_exception_on_error=false: the method returns the value false.
     *           + If $in_opt_throw_exception_on_error=true: the method throws an exception.
     *         - If the client could perform the required action:
     *           The method returns an associative array which keys are the names of the entries (directories, files or
     *           links) found within the given path, and the values are associative arrays that contain information about each entry.
     *           See the static method parseLsRaw() for details.
     * @see parseLsRaw
     * @throws Exception
     */
    public function ls($in_opt_dir='.', $in_opt_throw_exception_on_error=false) {
        $files_raw = ftp_rawlist($this->__ftpStream, $in_opt_dir);
        if (false === $files_raw) {
            if ($in_opt_throw_exception_on_error) {
                throw new Exception(sprintf('Cannot get the list of files on the remote host "%s", in the directory "%s".', $this->__host, $in_opt_dir));
            } else {
                return false;
            }
        }

        return self::parseLsRaw($files_raw);
    }

    /**
     * Put a file from the local host to the remote server.
     * @param string $in_local_file_path Path the file, on the local host, to transfer.
     * @param string $in_remote_file_path Path to the file on the remote server.
     * @param int $in_opt_mode FTP_BINARY or FTP_ASCII
     * @throws Exception
     */
    public function put($in_local_file_path, $in_remote_file_path, $in_opt_mode=FTP_BINARY) {
        if (false === $inLocalFd = @fopen($in_local_file_path, 'r')) {
            $error = error_get_last();
            throw new Exception(sprintf('Cannot open local file "%s". %s',
                $in_local_file_path,
                $error['message']));
        }
        if (false === @ftp_fput($this->__ftpStream, $in_remote_file_path, $inLocalFd, $in_opt_mode)) {
            $error = error_get_last();
            throw new Exception(sprintf('Cannot put local file "%s" to remote location "%s", on host remote "%s". %s',
                $in_local_file_path,
                $in_remote_file_path,
                $this->__host,
                $error['message']));
        }
    }

    /**
     * Get a file from the remote server to the local host.
     * @param string $in_local_lile_path Path to the local file that will be used to store the file.
     * @param string $in_remote_file_path Path to the remove file to get.
     * @param int $in_opt_mode FTP_BINARY or FTP_ASCII
     * @throws Exception
     */
    public function get($in_local_lile_path, $in_remote_file_path, $in_opt_mode=FTP_BINARY) {
        if (false === @ftp_get(
            $this->__ftpStream,
            $in_local_lile_path,
            $in_remote_file_path,
            $in_opt_mode)) {
            $error = error_get_last();
            throw new Exception(sprintf('Cannot get remote file "%s" from remote host "%s" as local file "%s". %s',
                $in_remote_file_path,
                $this->__host,
                $in_local_lile_path,
                $error['message']));
        }
    }

    /**
     * Close the connexion to the server.
     * @throws Exception
     */
    public function disconnect() {
        if (false === ftp_close($this->__ftpStream)) {
            throw new Exception(sprintf('Cannot close the connexion to the host "%s".', $this->__host));
        }
    }

    /**
     * Create a directory on the remote host.
     * @param string $in_directory_path Path to the directory to create.
     * @throws Exception
     */
    public function mkdir($in_directory_path) {
        if (false === @ftp_mkdir($this->__ftpStream, $in_directory_path)) {
            $error = error_get_last();
            throw new Exception(sprintf('Cannot create the directory "%s". %s',
                $in_directory_path,
                $error['message']));
        }
    }

    /**
     * Recursively create a directory identified by its given path, if the directory does not already exist.
     * @param string $in_directory_path Path to the directory to create.
     * @return bool|null The method may return true, null of false, depending on the context:
     *         - If the directory was created, then the method returns the value true.
     *         - If the directory was not created because it already exists, then the method returns the value null.
     *         - If the directory was not created because a file with the same name already exists, then the method returns
     *           the value false.
     *
     * @throws Exception
     */
    public function mkdirRecursiveIfNotExist($in_directory_path) {

        $parts = self::path2parts($in_directory_path);

        if (is_null($parts)) {
            // $in_directory_path is "/" or "." or "./".
            return null;
        }

        $p = $parts[0];
        $result = false;
        for($i=0; $i<count($parts); $i++) {

            if (1 == $i) {
                $p = $parts[1];
            } elseif ($i > 0) {
                $p = $p . '/' . $parts[$i];
            }

            $status = $this->entryExists($p);

            if (true === $status) {
                // $p is "/"
                continue;
            }

            if (false === $status) {
                // Create a directory.
                $this->mkdir($p);
                $result = true;
                continue;
            }

            if (! is_array($status)) {
                throw new Exception('Unexpected error!');
            }

            if (self::ENTRY_TYPE_DIRECTORY != $status[self::ENTRY_TYPE]) {
                return false;
            }
        }

        return $result;
    }

    /**
     * Remove a directory identified by its given path.
     * @param string $in_directory_path Path to the directory to remove.
     * @throws Exception
     */
    public function rmdir($in_directory_path) {
        if (false === @ftp_rmdir($this->__ftpStream, $in_directory_path)) {
            $error = error_get_last();
            throw new Exception(sprintf('Cannot remove the directory "%s". %s',
                $in_directory_path,
                $error['message']));
        }
    }

    /**
     * Delete a file identified by its given path on the remote server.
     * @param string $in_file_path Path to the file on the remote server.
     * @throws Exception An exception is thrown if the client could
     */
    public function delete($in_file_path) {
        if (false === @ftp_delete($this->__ftpStream, $in_file_path)) {
            $error = error_get_last();
            throw new Exception(sprintf('Cannot delete the file "%s". %s',
                $in_file_path,
                $error['message']));
        }
    }

    /**
     * Test whether a file exists, and if it does, then delete it.
     * @param string $in_directory_path Pat to the file to delete on the remote server.
     * @return bool The method returns true of false, depending on the context:
     *         - If the file existed and was successfully deleted, then the method returns the value true.
     *         - If the file did not exist, then the method returns the value false.
     * @throws Exception If the file existed but could not be deleted, then the method throws an exception.
     */
    public function deleteIfExists($in_directory_path) {

        $status = $this->entryExists($in_directory_path);

        if (true === $status) {
            throw new Exception(sprintf('It is not possible to remove the directory "%s"', $in_directory_path));
        }

        if (false === $status) {
            return false;
        }

        if (self::ENTRY_TYPE_FILE != $status[self::ENTRY_TYPE]) {
            throw new Exception(sprintf('The entry identified by the "%s" is a directory!', $in_directory_path));
        }

        $this->delete($in_directory_path);
        return true;
    }

    /**
     * Test whether an entry (directory, file of link), identified by its given path, exists or not.
     * @param string $in_entry_path Path to the entry.
     * @return bool|array The method may return an array, the value true, or the value false, depending on the context:
     *         - If the entry is "/", "." or "./", then the method returns the value true.
     *         - If the entry does not exist, then the method returns the value false.
     *         - If the entry exists, then the method returns an array that contains information about the entry.
     *           See the static method parseLsRaw() for details.
     * @see parseLsRaw
     * @throws Exception
     */
    public function entryExists($in_entry_path) {

        $parts = self::path2parts($in_entry_path);

        if (is_null($parts)) {
            return true;
        }

        $entries = null;
        $p = $parts[0];
        for ($i=0; $i<count($parts)-1; $i++) {

            if (1 == $i) {
                $p = $parts[1];
            } elseif ($i > 0) {
                $p = $p . '/' . $parts[$i];
            }

            if (false === $entries = $this->ls($p)) {
                return false;
            }

            $next = $parts[$i+1];
            if (! array_key_exists($next, $entries)) {
                return false;
            }

            $next_entry = $entries[$next];

            if (count($parts) == $i+2) {
                return $next_entry;
            }

            if (self::ENTRY_TYPE_DIRECTORY != $next_entry[self::ENTRY_TYPE]) {
                return false;
            }
        }

        return false;
    }

    /**
     * Test whether a directory, identified by its given path, exists or not.
     * @param string $in_path Path to the directory.
     * @return bool The method may return true or false, depending on the context:
     *         - If the directory does not exist, or if it represents a file, then the method returns the value false.
     *         - If the directory exists, then the method returns the value true.
     * @throws Exception
     */
    public function directoryExists($in_path) {
        /** @var array|bool $status */
        $status = $this->entryExists($in_path);
        if (is_bool($status)) {
            return $status;
        }
        return $status[self::ENTRY_TYPE] == self::ENTRY_TYPE_DIRECTORY;
    }

    /**
     * Test whether a file, identified by its given path, exists or not.
     * @param string $in_path Path to the file.
     * @return array|bool
     * @return bool|mixed The method may return true or false, depending on the context:
     *         - If the file does not exist, or if it represents a directory, then the method returns the value false.
     *         - If the file exists, then the method returns the value true.
     * @throws Exception
     */
    public function fileExists($in_path) {
        /** @var array|bool $status */
        $status = $this->entryExists($in_path);
        if (is_bool($status)) {
            return false;
        }
        return $status[self::ENTRY_TYPE] == self::ENTRY_TYPE_FILE;
    }

    /**
     * Break a given path into its components.
     * @param string $in_entry_path The path to break.
     * @return array|null If the path does not represent the root of the tree, then the method returns it components.
     *         Please note that the first component is always "/".
     *         Otherwise, the method returns the value null.
     */
    static public function path2parts($in_entry_path) {
        $in_entry_path = preg_replace('/\/+/', '/', $in_entry_path);
        $in_entry_path = preg_replace('/\/$/', '', $in_entry_path);
        $in_entry_path = preg_replace('/^\//', '', $in_entry_path);
        $in_entry_path = preg_replace('/^\.\/?/', '', $in_entry_path);

        if ('' === $in_entry_path) {
            return null;
        }

        $parts = explode('/', $in_entry_path);
        array_unshift($parts, '/');
        return $parts;
    }

    /**
     * Parse the output of the FTP command LIST.
     * @param array $in_raw_list Output of the FTP command LIST.
     * @return array The method returns an associative array which keys are entries (files or directories) names and
     *         values data about the entry. The values are associative arrays that contain the keys listed below:
     *         - ENTRY_PERMISSIONS
     *         - ENTRY_NUMBER
     *         - ENTRY_OWNER
     *         - ENTRY_GROUP
     *         - ENTRY_SIZE
     *         - ENTRY_MONTH
     *         - ENTRY_DAY
     *         - ENTRY_TIME
     * @throws Exception
     * @see http://www.nsftools.com/tips/RawFTP.htm#LIST
     */
    static public function parseLsRaw(array $in_raw_list)
    {
        // -rw-r--r--    1 0        0               1 Jan 15 14:08 file0.txt
        // -rw-r--r--    1 0        0               2 Jan 15 14:08 file1.txt
        // -rw-r--r--    1 0        0               3 Jan 15 14:08 file2.txt
        // -rw-r--r--    1 0        0               4 Jan 15 14:08 file3.txt
        // drwxr-xr-x    2 0        0            4096 Jan 15 14:08 r1
        // drwxr-xr-x    2 0        0            4096 Jan 15 14:08 r2
        // drwxr-xr-x    2 0        0            4096 Jan 15 14:08 r3
        // drwxr-xr-x    2 0        0            4096 Jan 15 14:08 r4
        //
        // d... => directory
        // -... => file
        // l... => link

        $cols = array(
            self::ENTRY_PERMISSIONS,
            self::ENTRY_NUMBER,
            self::ENTRY_OWNER,
            self::ENTRY_GROUP,
            self::ENTRY_SIZE,
            self::ENTRY_MONTH,
            self::ENTRY_DAY,
            self::ENTRY_TIME,
            self::ENTRY_NAME
        );

        $files = array();
        /**
         * @var int $position
         * @var string $data_text
         */
        foreach($in_raw_list as $position => $data_text)
        {
            $parsed_data = preg_split('/\s+/', $data_text);

            if ( count($parsed_data) < count($cols)) {
                throw new Exception(sprintf('Cannot parse the text returned by the function "ftp_rawlist()". This record is not valid: "%s"', $data_text));
            }

            $s = $data_text;
            for ($i=0; $i<8; $i++) {
                $s = preg_replace('/^\s+/', '', $s);
                $s = substr($s, strlen($parsed_data[$i]));
            }

            $name = substr($s, 1);

            $p = array();
            for ($i=0; $i<8; $i++) {
                $p[$cols[$i]] = $parsed_data[$i];
            }
            $type = substr($p[self::ENTRY_PERMISSIONS], 0, 1);

            switch ($type) {
                case 'd': $p[self::ENTRY_TYPE] = self::ENTRY_TYPE_DIRECTORY; break;
                case '-': $p[self::ENTRY_TYPE] = self::ENTRY_TYPE_FILE; break;
                case 'l': $p[self::ENTRY_TYPE] = self::ENTRY_TYPE_LINK; break;
                default:  $p[self::ENTRY_TYPE] = self::ENTRY_TYPE_UNKNOWN;
            }

            $files[$name] = $p;
        }

        return $files;
    }


}