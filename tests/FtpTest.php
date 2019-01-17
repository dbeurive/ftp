<?php

use dbeurive\Ftp\Ftp;
use dbeurive\Ftp\Exception as FtpException;
use PHPUnit\Framework\TestCase;

/**
 * Class FtpTest
 *
 * Before you run the test suite, please setup the environment as follows:
 *
 * 1. Edit the file "setenv.sh".
 * 2. Source the file "setenv.sh".
 * 3. Edit the file "clear_on_remote.sh".
 */

class FtpTest extends TestCase
{
    static private $__file2put;
    static private $__remoteHostOk;
    static private $__userOk;
    static private $__passwordOk;
    static private $__portOk;
    static private $__remoteHostKo;
    static private $__userKo;
    static private $__passwordKo;
    static private $__portKo;
    static private $__rootOnRemote;
    static private $__badDirOnRemote;

    const RV_TRUE = 1;
    const RV_FALSE = 2;
    const RV_FILE = 3;
    const RV_DIRECTORY = 4;

    static private $__expected_types = array(
        'file.txt' => self::RV_FILE,
        'file 1.txt' => self::RV_FILE,
        'file 2.txt ' => self::RV_FILE,
        '  file  3.txt  ' => self::RV_FILE,
        'r1' => self::RV_DIRECTORY,
        'directory with spaces 1' => self::RV_DIRECTORY,
        '  directory  with  spaces 2  ' => self::RV_DIRECTORY
    );

    public static function setUpBeforeClass() {

        if (false === self::$__remoteHostOk = getenv('FTP_REMOTE_HOST_OK')) {
            throw new \Exception("Environment variable FTP_REMOTE_HOST_OK is not defined. Did you source \"./tests/setenv.sh\" before running the tests ?");
        }

        if (false === self::$__userOk = getenv('FTP_USER_OK')) {
            throw new \Exception("Environment variable FTP_USER_OK is not defined. Did you source \"./tests/setenv.sh\" before running the tests ?");
        }

        if (false === self::$__passwordOk = getenv('FTP_PASSWORD_OK')) {
            throw new \Exception("Environment variable FTP_PASSWORD_OK is not defined. Did you source \"./tests/setenv.sh\" before running the tests ?");
        }

        if (false === self::$__portOk = getenv('FTP_PORT_OK')) {
            throw new \Exception("Environment variable FTP_PORT_OK is not defined. Did you source \"./tests/setenv.sh\" before running the tests ?");
        }

        // ---

        if (false === self::$__remoteHostKo = getenv('FTP_REMOTE_HOST_KO')) {
            throw new \Exception("Environment variable FTP_REMOTE_HOST_KO is not defined. Did you source \"./tests/setenv.sh\" before running the tests ?");
        }

        if (false === self::$__userKo = getenv('FTP_USER_KO')) {
            throw new \Exception("Environment variable FTP_USER_KO is not defined. Did you source \"./tests/setenv.sh\" before running the tests ?");
        }

        if (false === self::$__passwordKo = getenv('FTP_PASSWORD_KO')) {
            throw new \Exception("Environment variable FTP_PASSWORD_KO is not defined. Did you source \"./tests/setenv.sh\" before running the tests ?");
        }

        if (false === self::$__portKo = getenv('FTP_PORT_KO')) {
            throw new \Exception("Environment variable FTP_PORT_KO is not defined. Did you source \"./tests/setenv.sh\" before running the tests ?");
        }

        // ---

        if (false === getenv('SSH_ARG')) {
            throw new \Exception("Environment variable SSH_ARG is not defined. Did you source \"./tests/setenv.sh\" before running the tests ?");
        }

        if (false === self::$__rootOnRemote = getenv('ROOT_ON_REMOTE')) {
            throw new \Exception("Environment variable ROOT_ON_REMOTE is not defined. Did you source \"./tests/setenv.sh\" before running the tests ?");
        }

        if (false === self::$__badDirOnRemote = getenv('BAD_DIR_ON_REMOTE')) {
            throw new \Exception("Environment variable BAD_DIR_ON_REMOTE is not defined. Did you source \"./tests/setenv.sh\" before running the tests ?");
        }

        // ---

        self::$__file2put = tempnam(sys_get_temp_dir(), 'ftpTest-');
        file_put_contents(self::$__file2put, '0123456789');
    }

    public static function tearDownAfterClass() {
        unlink(self::$__file2put);
    }

    public function setUp() {

        $output = array();
        $status = null;
        exec(sprintf('%s/%s', __DIR__, 'prepare.sh'), $output, $status);
        if (0 != $status) {
            exit(1);
        }
    }

    // ------------------------------------------------------------------------------
    // Connect
    // ------------------------------------------------------------------------------

    public function testConnectOk() {
        $options = array(Ftp::OPTION_PORT => self::$__portOk, Ftp::OPTION_TIMEOUT => 60);
        $ftp = new Ftp(self::$__remoteHostOk, $options);
        $ftp->connect();
        $this->addToAssertionCount(1);
        $ftp->disconnect();
    }

    public function testConnectKoBadHost() {
        $this->expectException(FtpException::class);
        $options = array(Ftp::OPTION_PORT => self::$__portOk, Ftp::OPTION_TIMEOUT => 2);
        $ftp = new Ftp(self::$__remoteHostKo, $options);
        $ftp->connect();
        $ftp->disconnect();
    }

    public function testConnectKoBadPort() {
        $this->expectException(FtpException::class);
        $options = array(Ftp::OPTION_PORT => self::$__portKo, Ftp::OPTION_TIMEOUT => 2);
        $ftp = new Ftp(self::$__remoteHostOk, $options);
        $ftp->connect();
        $ftp->disconnect();
    }

    // ------------------------------------------------------------------------------
    // Login
    // ------------------------------------------------------------------------------

    public function testLoginOk() {
        $options = array(Ftp::OPTION_PORT => self::$__portOk, Ftp::OPTION_TIMEOUT => 60);
        $ftp = new Ftp(self::$__remoteHostOk, $options);
        $ftp->connect();
        $this->assertTrue($ftp->login(self::$__userOk, self::$__passwordOk));
        $this->assertFalse($ftp->login(self::$__userOk, self::$__passwordOk));
        $ftp->disconnect();
    }

    public function testLoginKoBadLogin() {
        $this->expectException(FtpException::class);
        $options = array(Ftp::OPTION_PORT => self::$__portOk, Ftp::OPTION_TIMEOUT => 60);
        $ftp = new Ftp(self::$__remoteHostOk, $options);
        $ftp->connect();
        $ftp->login(self::$__userKo, self::$__passwordOk);
        $ftp->disconnect();
    }

    public function testLoginKoBadPassword() {
        $this->expectException(FtpException::class);
        $options = array(Ftp::OPTION_PORT => self::$__portOk, Ftp::OPTION_TIMEOUT => 60);
        $ftp = new Ftp(self::$__remoteHostOk, $options);
        $ftp->connect();
        $ftp->login(self::$__userOk, self::$__passwordKo);
        $ftp->disconnect();
    }

    // ------------------------------------------------------------------------------
    // Ls
    // ------------------------------------------------------------------------------

    public function testLsOk() {
        $options = array(Ftp::OPTION_PORT => self::$__portOk, Ftp::OPTION_TIMEOUT => 60);
        $ftp = new Ftp(self::$__remoteHostOk, $options);
        $ftp->connect();
        $ftp->login(self::$__userOk, self::$__passwordOk);
        /** @var array $entries Key:<name of the entry> and Value:<entry object> */
        $entries = $ftp->ls(self::$__rootOnRemote, true);


        foreach (self::$__expected_types as $entry_name => $expected_type) {
            /** @var \dbeurive\Ftp\AbstractEntry $entry */
            $entry = $entries[$entry_name];
            $this->assertArrayHasKey($entry_name, $entries);
            switch ($expected_type) {
                case self::RV_FILE: $this->assertTrue($entry->isFile()); break;
                case self::RV_DIRECTORY: $this->assertTrue($entry->isDirectory()); break;
                default: throw new \Exception('Unexpected error');
            }
        }

        $ftp->disconnect();

        // TODO: test all entries properties.
        // file_put_contents('debug.txt', print_r($entries, true));
    }

    // ------------------------------------------------------------------------------
    // Get
    // ------------------------------------------------------------------------------

    public function testGetOk() {
        $options = array(Ftp::OPTION_PORT => self::$__portOk, Ftp::OPTION_TIMEOUT => 60);
        $ftp = new Ftp(self::$__remoteHostOk, $options);
        $ftp->connect();
        $ftp->login(self::$__userOk, self::$__passwordOk);

        /**
         * @var string $_entry_name
         * @var int $_expected_entry_type
         */
        foreach (self::$__expected_types as $_entry_name => $_expected_entry_type) {
            if (self::RV_FILE != $_expected_entry_type) {
                continue;
            }
            $local =  sprintf('%s/%s', sys_get_temp_dir(), $_entry_name);
            $remote = sprintf('%s/%s', self::$__rootOnRemote, $_entry_name);
            $ftp->get($local, $remote);
            $this->assertFileExists($local);
            unlink($local);
        }

        $ftp->disconnect();
    }

    public function testGetKoFileDoesNotExist() {
        $this->expectException(FtpException::class);
        $options = array(Ftp::OPTION_PORT => self::$__portOk, Ftp::OPTION_TIMEOUT => 60);
        $ftp = new Ftp(self::$__remoteHostOk, $options);
        $ftp->connect();
        $ftp->login(self::$__userOk, self::$__passwordOk);
        $local =  sprintf('%s/toto', sys_get_temp_dir());
        $remote = sprintf('%s/toto', self::$__rootOnRemote);
        $ftp->get($local, $remote);
        $ftp->disconnect();
    }

    public function testGetKoDirectory() {
        $this->expectException(FtpException::class);
        $options = array(Ftp::OPTION_PORT => self::$__portOk, Ftp::OPTION_TIMEOUT => 60);
        $ftp = new Ftp(self::$__remoteHostOk, $options);
        $ftp->connect();
        $ftp->login(self::$__userOk, self::$__passwordOk);

        /**
         * @var string $_entry_name
         * @var int $_expected_entry_type
         */
        foreach (self::$__expected_types as $_entry_name => $_expected_entry_type) {
            if (self::RV_DIRECTORY != $_expected_entry_type) {
                continue;
            }
            $local =  sprintf('%s/%s', sys_get_temp_dir(), $_entry_name);
            $remote = sprintf('%s/%s', self::$__rootOnRemote, $_entry_name);
            $ftp->get($local, $remote);
            break;
        }

        $ftp->disconnect();
    }

    // ------------------------------------------------------------------------------
    // Put
    // ------------------------------------------------------------------------------

    public function testPutOk() {
        $options = array(Ftp::OPTION_PORT => self::$__portOk, Ftp::OPTION_TIMEOUT => 60);
        $ftp = new Ftp(self::$__remoteHostOk, $options);
        $ftp->connect();
        $ftp->login(self::$__userOk, self::$__passwordOk);
        $remote = sprintf('%s/file2put', self::$__rootOnRemote);
        $ftp->put(self::$__file2put, $remote);
        $ftp->disconnect();
        $this->addToAssertionCount(1);
    }

    public function testPutKoLocalFileDoesNotExist() {
        $this->expectException(FtpException::class);
        $options = array(Ftp::OPTION_PORT => self::$__portOk, Ftp::OPTION_TIMEOUT => 60);
        $ftp = new Ftp(self::$__remoteHostOk, $options);
        $ftp->connect();
        $ftp->login(self::$__userOk, self::$__passwordOk);
        $local = tempnam(sys_get_temp_dir(), 'ftpTestKo-');
        $remote = sprintf('%s/file2put', self::$__rootOnRemote);
        $this->assertTrue(unlink($local));
        $ftp->put($local, $remote);
        $ftp->disconnect();
    }

    public function testPutKoRemotePathDoesNotExist() {
        $this->expectException(FtpException::class);
        $options = array(Ftp::OPTION_PORT => self::$__portOk, Ftp::OPTION_TIMEOUT => 60);
        $ftp = new Ftp(self::$__remoteHostOk, $options);
        $ftp->connect();
        $ftp->login(self::$__userOk, self::$__passwordOk);
        $remote = sprintf('%s/file2put', self::$__badDirOnRemote);
        $ftp->put(self::$__file2put, $remote);
        $ftp->disconnect();
        $this->addToAssertionCount(1);
    }

    // ------------------------------------------------------------------------------
    // Mkdir
    // ------------------------------------------------------------------------------

    public function testMkdirOk() {
        $options = array(Ftp::OPTION_PORT => self::$__portOk, Ftp::OPTION_TIMEOUT => 60);
        $ftp = new Ftp(self::$__remoteHostOk, $options);
        $ftp->connect();
        $ftp->login(self::$__userOk, self::$__passwordOk);
        $remote = sprintf('%s/new_directory', self::$__rootOnRemote);
        $ftp->mkdir($remote);
        $remote = sprintf('%s/new_directory/r1', self::$__rootOnRemote);
        $ftp->mkdir($remote);
        $ftp->disconnect();
        $this->addToAssertionCount(1);
    }

    public function testMkdirKoDirectoryAlreadyExists() {
        $this->expectException(FtpException::class);
        $options = array(Ftp::OPTION_PORT => self::$__portOk, Ftp::OPTION_TIMEOUT => 60);
        $ftp = new Ftp(self::$__remoteHostOk, $options);
        $ftp->connect();
        $ftp->login(self::$__userOk, self::$__passwordOk);
        $remote = sprintf('%s/new_directory', self::$__rootOnRemote);
        $ftp->mkdir($remote);
        $ftp->mkdir($remote);
        $ftp->disconnect();
    }

    // ------------------------------------------------------------------------------
    // Rmdir
    // ------------------------------------------------------------------------------

    public function testRmdirOk() {
        $options = array(Ftp::OPTION_PORT => self::$__portOk, Ftp::OPTION_TIMEOUT => 60);
        $ftp = new Ftp(self::$__remoteHostOk, $options);
        $ftp->connect();
        $ftp->login(self::$__userOk, self::$__passwordOk);
        $remote = sprintf('%s/new_directory', self::$__rootOnRemote);
        $ftp->mkdir($remote);
        $ftp->rmdir($remote);
        $ftp->disconnect();
        $this->addToAssertionCount(1);
    }

    public function testRmdirKoDirectoryDoesNotExist() {
        $this->expectException(FtpException::class);
        $options = array(Ftp::OPTION_PORT => self::$__portOk, Ftp::OPTION_TIMEOUT => 60);
        $ftp = new Ftp(self::$__remoteHostOk, $options);
        $ftp->connect();
        $ftp->login(self::$__userOk, self::$__passwordOk);
        $remote = sprintf('%s/new_directory', self::$__rootOnRemote);
        $ftp->rmdir($remote);
        $ftp->disconnect();
        $this->addToAssertionCount(1);
    }

    // ------------------------------------------------------------------------------
    // Exists
    // ------------------------------------------------------------------------------

    public function testExists() {

        $paths = array(
            '/' => self::RV_TRUE,
            '.' => self::RV_TRUE,
            './' => self::RV_TRUE,
            '/files' => self::RV_DIRECTORY,
            './files' => self::RV_DIRECTORY,
            'files' => self::RV_DIRECTORY,
            '/files/file.txt' => self::RV_FILE,
            './files/file.txt' => self::RV_FILE,
            'files/file.txt' => self::RV_FILE,
            '/files/r1' => self::RV_DIRECTORY,
            './files/r1' => self::RV_DIRECTORY,
            'files/r1' => self::RV_DIRECTORY,
            '/files/r1/file.txt' => self::RV_FILE,
            './files/r1/file.txt' => self::RV_FILE,
            'files/r1/file.txt' => self::RV_FILE,
            '/files/r1/rr1/file.txt' => self::RV_FILE,
            './files/r1/rr1/file.txt' => self::RV_FILE,
            'files/r1/rr1/file.txt' => self::RV_FILE,
            '/files/r1/rr1/rrr1' => self::RV_DIRECTORY,
            './files/r1/rr1/rrr1' => self::RV_DIRECTORY,
            'files/r1/rr1/rrr1' => self::RV_DIRECTORY,
            '/files/r1/rr1/rrr1/toto' => self::RV_FALSE,
            './files/r1/rr1/rrr1/toto' => self::RV_FALSE,
            'files/r1/rr1/rrr1/toto' => self::RV_FALSE,
            '/files/directory with spaces 1' => self::RV_DIRECTORY,
            './files/directory with spaces 1' => self::RV_DIRECTORY,
            'files/directory with spaces 1' => self::RV_DIRECTORY,
            '/files/  directory  with  spaces 2  ' => self::RV_DIRECTORY,
            './files/  directory  with  spaces 2  ' => self::RV_DIRECTORY,
            'files/  directory  with  spaces 2  ' => self::RV_DIRECTORY,
            '/files/  file  3.txt  ' => self::RV_FILE,
            './files/  file  3.txt  ' => self::RV_FILE,
            'files/  file  3.txt  ' => self::RV_FILE,
            '/files/toto' => self::RV_FALSE,
            './files/toto' => self::RV_FALSE,
            'files/toto' => self::RV_FALSE,
            '/files/toto/titi' => self::RV_FALSE,
            './files/toto/titi' => self::RV_FALSE,
            'files/toto/titi' => self::RV_FALSE,
            '/r1' => self::RV_FALSE,
            './r1' => self::RV_FALSE,
            'r1' => self::RV_FALSE,
            '/r1/' => self::RV_FALSE,
            './r1/' => self::RV_FALSE,
            'r1/' => self::RV_FALSE,
            '/files/file.txt/' => self::RV_FILE,
            './files/file.txt/' => self::RV_FILE,
            'files/file.txt/' => self::RV_FILE
        );

        $options = array(Ftp::OPTION_PORT => self::$__portOk, Ftp::OPTION_TIMEOUT => 60);
        $ftp = new Ftp(self::$__remoteHostOk, $options);
        $ftp->connect();
        $ftp->login(self::$__userOk, self::$__passwordOk);

        /** @var string $_path */
        foreach ($paths as $_path => $_expected_status) {
            /** @var bool|\dbeurive\Ftp\AbstractEntry $entry */
            $entry = $ftp->entryExists($_path);
            switch ($_expected_status) {
                case self::RV_TRUE: $this->assertTrue($entry); break;
                case self::RV_FALSE: $this->assertFalse($entry); break;
                case self::RV_FILE: {
                    $this->assertInstanceOf(\dbeurive\Ftp\AbstractEntry::class, $entry);
                    $this->assertTrue($entry->isFile());

                }; break;
                case self::RV_DIRECTORY: {
                    $this->assertInstanceOf(\dbeurive\Ftp\AbstractEntry::class, $entry);
                    $this->assertTrue($entry->isDirectory());
                }; break;
                default: throw new \Exception('Unexpected error!');
            }
        }

        $ftp->disconnect();
    }

    // ------------------------------------------------------------------------------
    // directoryExists
    // ------------------------------------------------------------------------------

    public function testDirectoryExists() {

        $paths = array(
            '/' => true,
            '.' => true,
            './' => true,
            '/files' => true,
            './files' => true,
            'files' => true,
            '/files/file.txt' => false,
            './files/file.txt' => false,
            'files/file.txt' => false,
            '/files/r1' => true,
            './files/r1' => true,
            'files/r1' => true,
            '/files/directory with spaces 1' => true,
            './files/directory with spaces 1' => true,
            'files/directory with spaces 1' => true,
            '/files/  directory  with  spaces 2  ' => true,
            './files/  directory  with  spaces 2  ' => true,
            'files/  directory  with  spaces 2  ' => true,
            '/files/  file  3.txt  ' => false,
            './files/  file  3.txt  ' => false,
            'files/  file  3.txt  ' => false,
            '/files/toto' => false,
            './files/toto' => false,
            'files/toto' => false,
            '/files/toto/titi' => false,
            './files/toto/titi' => false,
            'files/toto/titi' => false,
            '/r1' => false,
            './r1' => false,
            'r1' => false,
            '/r1/' => false,
            './r1/' => false,
            'r1/' => false,
            '/files/file.txt/' => false,
            './files/file.txt/' => false,
            'files/file.txt/' => false,

            '/files/r1/rr1/file.txt' => false,
            './files/r1/rr1/file.txt' => false,
            'files/r1/rr1/file.txt' => false,
            '/files/r1/rr1/rrr1' => true,
            './files/r1/rr1/rrr1' => true,
            'files/r1/rr1/rrr1' => true,
            '/files/r1/rr1/rrr1/toto' => false,
            './files/r1/rr1/rrr1/toto' => false,
            'files/r1/rr1/rrr1/toto' => false,
        );

        $options = array(Ftp::OPTION_PORT => self::$__portOk, Ftp::OPTION_TIMEOUT => 60);
        $ftp = new Ftp(self::$__remoteHostOk, $options);
        $ftp->connect();
        $ftp->login(self::$__userOk, self::$__passwordOk);

        /** @var string $_path */
        foreach ($paths as $_path => $_expected_status) {
            $status = $ftp->directoryExists($_path);
            switch ($_expected_status) {
                case true: $this->assertTrue($status); break;
                case false: $this->assertFalse($status); break;
                default: throw new \Exception('Unexpected error!');
            }
        }

        $ftp->disconnect();
    }

    // ------------------------------------------------------------------------------
    // fileExists
    // ------------------------------------------------------------------------------

    public function testFileExists() {

        $paths = array(
            '/' => false,
            '.' => false,
            './' => false,
            '/files' => false,
            './files' => false,
            'files' => false,
            '/files/file.txt' => true,
            './files/file.txt' => true,
            'files/file.txt' => true,
            '/files/r1' => false,
            './files/r1' => false,
            'files/r1' => false,
            '/files/directory with spaces 1' => false,
            './files/directory with spaces 1' => false,
            'files/directory with spaces 1' => false,
            '/files/  directory  with  spaces 2  ' => false,
            './files/  directory  with  spaces 2  ' => false,
            'files/  directory  with  spaces 2  ' => false,
            '/files/  file  3.txt  ' => true,
            './files/  file  3.txt  ' => true,
            'files/  file  3.txt  ' => true,
            '/files/toto' => false,
            './files/toto' => false,
            'files/toto' => false,
            '/files/toto/titi' => false,
            './files/toto/titi' => false,
            'files/toto/titi' => false,
            '/r1' => false,
            './r1' => false,
            'r1' => false,
            '/r1/' => false,
            './r1/' => false,
            'r1/' => false,
            '/files/file.txt/' => true,
            './files/file.txt/' => true,
            'files/file.txt/' => true,

            '/files/r1/rr1/file.txt' => true,
            './files/r1/rr1/file.txt' => true,
            'files/r1/rr1/file.txt' => true,
            '/files/r1/rr1/rrr1' => false,
            './files/r1/rr1/rrr1' => false,
            'files/r1/rr1/rrr1' => false,
            '/files/r1/rr1/rrr1/toto' => false,
            './files/r1/rr1/rrr1/toto' => false,
            'files/r1/rr1/rrr1/toto' => false,
        );

        $options = array(Ftp::OPTION_PORT => self::$__portOk, Ftp::OPTION_TIMEOUT => 60);
        $ftp = new Ftp(self::$__remoteHostOk, $options);
        $ftp->connect();
        $ftp->login(self::$__userOk, self::$__passwordOk);

        /** @var string $_path */
        foreach ($paths as $_path => $_expected_status) {
            $status = $ftp->fileExists($_path);
            switch ($_expected_status) {
                case true: $this->assertTrue($status); break;
                case false: $this->assertFalse($status); break;
                default: throw new \Exception('Unexpected error!');
            }
        }

        $ftp->disconnect();
    }

    // ------------------------------------------------------------------------------
    // mkdirIfNotExist
    // ------------------------------------------------------------------------------

    public function testMkdirIfNotExist() {

        $paths = array(
            '/' => null,
            '.' => null,
            './' => null,

            '/files/rr1' => true,
            './files/rr1' => null, // Already created
            'files/rr1' => null, // Already created

            '/files/rr1/rrr1/final' => true,
            './files/rr1/rrr1/final' => null, // Already created
            'files/rr1/rrr1/final' => null, // Already created

            '/files/rr1/rrr2/final' => true,
            './files/rr1/rrr2/final' => null, // Already created
            'files/rr1/rrr2/final' => null, // Already created

            '/files/r1' => null, // Already created
            './files/r1' => null, // Already created
            'files/r1' => null, // Already created

            '/files/file.txt' => false, // This is a file
            './files/file.txt' => false, // This is a file
            'files/file.txt' => false, // This is a file

            '/files/r1/rr1/file.txt' => false, // This is a file
            './files/r1/rr1/file.txt' => false, // This is a file
            'files/r1/rr1/file.txt' => false, // This is a file
        );

        $options = array(Ftp::OPTION_PORT => self::$__portOk, Ftp::OPTION_TIMEOUT => 60);
        $ftp = new Ftp(self::$__remoteHostOk, $options);
        $ftp->connect();
        $ftp->login(self::$__userOk, self::$__passwordOk);

        foreach ($paths as $_path => $_expected_status) {
            $status = $ftp->mkdirRecursiveIfNotExist($_path);
            $this->assertEquals($status, $_expected_status);
        }

        $ftp->disconnect();
    }

    // ------------------------------------------------------------------------------
    // delete
    // ------------------------------------------------------------------------------

    public function testDeleteOk() {
        $options = array(Ftp::OPTION_PORT => self::$__portOk, Ftp::OPTION_TIMEOUT => 60);
        $ftp = new Ftp(self::$__remoteHostOk, $options);
        $ftp->connect();
        $ftp->login(self::$__userOk, self::$__passwordOk);

        $ftp->delete('files/file.txt');
        $ftp->disconnect();
        $this->addToAssertionCount(1);
    }

    public function testDeleteKoNotAFile() {
        $this->expectException(FtpException::class);
        $options = array(Ftp::OPTION_PORT => self::$__portOk, Ftp::OPTION_TIMEOUT => 60);
        $ftp = new Ftp(self::$__remoteHostOk, $options);
        $ftp->connect();
        $ftp->login(self::$__userOk, self::$__passwordOk);
        $ftp->delete('files/r1');
        $ftp->disconnect();
    }

    public function testDeleteKoFileDoesNotExist() {
        $this->expectException(FtpException::class);
        $options = array(Ftp::OPTION_PORT => self::$__portOk, Ftp::OPTION_TIMEOUT => 60);
        $ftp = new Ftp(self::$__remoteHostOk, $options);
        $ftp->connect();
        $ftp->login(self::$__userOk, self::$__passwordOk);
        $ftp->delete('files/toto.txt');
        $ftp->disconnect();
    }

    // ------------------------------------------------------------------------------
    // deleteIfExists
    // ------------------------------------------------------------------------------

    public function testDeleteIfExistsOk() {
        $options = array(Ftp::OPTION_PORT => self::$__portOk, Ftp::OPTION_TIMEOUT => 60);
        $ftp = new Ftp(self::$__remoteHostOk, $options);
        $ftp->connect();
        $ftp->login(self::$__userOk, self::$__passwordOk);
        $this->assertTrue($ftp->deleteIfExists('files/file.txt'));
        $this->assertFalse($ftp->deleteIfExists('files/toto.txt'));
        $ftp->disconnect();
    }

    public function testDeleteIfExistsKo1() {
        $this->expectException(FtpException::class);
        $options = array(Ftp::OPTION_PORT => self::$__portOk, Ftp::OPTION_TIMEOUT => 60);
        $ftp = new Ftp(self::$__remoteHostOk, $options);
        $ftp->connect();
        $ftp->login(self::$__userOk, self::$__passwordOk);
        $ftp->deleteIfExists('/');
        $ftp->disconnect();
    }

    public function testDeleteIfExistsKo2() {
        $this->expectException(FtpException::class);
        $options = array(Ftp::OPTION_PORT => self::$__portOk, Ftp::OPTION_TIMEOUT => 60);
        $ftp = new Ftp(self::$__remoteHostOk, $options);
        $ftp->connect();
        $ftp->login(self::$__userOk, self::$__passwordOk);
        $ftp->deleteIfExists('.');
        $ftp->disconnect();
    }

    public function testDeleteIfExistsKo3() {
        $this->expectException(FtpException::class);
        $options = array(Ftp::OPTION_PORT => self::$__portOk, Ftp::OPTION_TIMEOUT => 60);
        $ftp = new Ftp(self::$__remoteHostOk, $options);
        $ftp->connect();
        $ftp->login(self::$__userOk, self::$__passwordOk);
        $ftp->deleteIfExists('.');
        $ftp->disconnect();
    }
}