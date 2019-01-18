<?php

/**
 * ./vendor/bin/phpunit ./tests/EntryManagerUnixTestnixTest.php
 */

use dbeurive\Ftp\EntryManagerUnix;
use dbeurive\Ftp\Exception as FtpException;
use PHPUnit\Framework\TestCase;

class EntryManagerUnixTest extends TestCase
{
    public function testParseOkBasic() {
        $input = '-rw-r--r--    1 10       0               11 Jan 15 14:08 file0.txt';
        $f = EntryManagerUnix::parse($input, '/path/to/directory');
        $this->assertEquals('-rw-r--r--', $f[EntryManagerUnix::ENTRY_FIELD_PERMISSIONS]);
        $this->assertEquals('1', $f[EntryManagerUnix::ENTRY_FIELD_NUMBER]);
        $this->assertEquals('10', $f[EntryManagerUnix::ENTRY_FIELD_OWNER]);
        $this->assertEquals('0', $f[EntryManagerUnix::ENTRY_FIELD_GROUP]);
        $this->assertEquals('11', $f[EntryManagerUnix::ENTRY_FIELD_SIZE]);
        $this->assertEquals('Jan', $f[EntryManagerUnix::ENTRY_FIELD_MONTH]);
        $this->assertEquals('15', $f[EntryManagerUnix::ENTRY_FIELD_DAY]);
        $this->assertEquals('14:08', $f[EntryManagerUnix::ENTRY_FIELD_TIME]);
        $this->assertEquals('file0.txt', $f[EntryManagerUnix::ENTRY_FIELD_NAME]);
        $this->assertEquals(EntryManagerUnix::ENTRY_TYPE_FILE, $f[EntryManagerUnix::ENTRY_FIELD_TYPE]);

        $input = 'drw-r--r--    1 10       0               11 Jan 15 14:08 Dir';
        $f = EntryManagerUnix::parse($input, '/path/to/directory');
        $this->assertEquals('drw-r--r--', $f[EntryManagerUnix::ENTRY_FIELD_PERMISSIONS]);
        $this->assertEquals('1', $f[EntryManagerUnix::ENTRY_FIELD_NUMBER]);
        $this->assertEquals('10', $f[EntryManagerUnix::ENTRY_FIELD_OWNER]);
        $this->assertEquals('0', $f[EntryManagerUnix::ENTRY_FIELD_GROUP]);
        $this->assertEquals('11', $f[EntryManagerUnix::ENTRY_FIELD_SIZE]);
        $this->assertEquals('Jan', $f[EntryManagerUnix::ENTRY_FIELD_MONTH]);
        $this->assertEquals('15', $f[EntryManagerUnix::ENTRY_FIELD_DAY]);
        $this->assertEquals('14:08', $f[EntryManagerUnix::ENTRY_FIELD_TIME]);
        $this->assertEquals('Dir', $f[EntryManagerUnix::ENTRY_FIELD_NAME]);
        $this->assertEquals(EntryManagerUnix::ENTRY_TYPE_DIRECTORY, $f[EntryManagerUnix::ENTRY_FIELD_TYPE]);

        $input = 'lrw-r--r--    1 10       0               11 Jan 15 14:08 link.txt';
        $f = EntryManagerUnix::parse($input, '/path/to/directory');
        $this->assertEquals('lrw-r--r--', $f[EntryManagerUnix::ENTRY_FIELD_PERMISSIONS]);
        $this->assertEquals('1', $f[EntryManagerUnix::ENTRY_FIELD_NUMBER]);
        $this->assertEquals('10', $f[EntryManagerUnix::ENTRY_FIELD_OWNER]);
        $this->assertEquals('0', $f[EntryManagerUnix::ENTRY_FIELD_GROUP]);
        $this->assertEquals('11', $f[EntryManagerUnix::ENTRY_FIELD_SIZE]);
        $this->assertEquals('Jan', $f[EntryManagerUnix::ENTRY_FIELD_MONTH]);
        $this->assertEquals('15', $f[EntryManagerUnix::ENTRY_FIELD_DAY]);
        $this->assertEquals('14:08', $f[EntryManagerUnix::ENTRY_FIELD_TIME]);
        $this->assertEquals('link.txt', $f[EntryManagerUnix::ENTRY_FIELD_NAME]);
        $this->assertEquals(EntryManagerUnix::ENTRY_TYPE_LINK, $f[EntryManagerUnix::ENTRY_FIELD_TYPE]);
    }

    public function testParseOkNameWithSpaces() {
        $input = '-rw-r--r--    1 10       0               11 Jan 15 14:08  file0  .txt ';
        $f = EntryManagerUnix::parse($input, '/path/to/directory');
        $this->assertEquals('-rw-r--r--', $f[EntryManagerUnix::ENTRY_FIELD_PERMISSIONS]);
        $this->assertEquals('1', $f[EntryManagerUnix::ENTRY_FIELD_NUMBER]);
        $this->assertEquals('10', $f[EntryManagerUnix::ENTRY_FIELD_OWNER]);
        $this->assertEquals('0', $f[EntryManagerUnix::ENTRY_FIELD_GROUP]);
        $this->assertEquals('11', $f[EntryManagerUnix::ENTRY_FIELD_SIZE]);
        $this->assertEquals('Jan', $f[EntryManagerUnix::ENTRY_FIELD_MONTH]);
        $this->assertEquals('15', $f[EntryManagerUnix::ENTRY_FIELD_DAY]);
        $this->assertEquals('14:08', $f[EntryManagerUnix::ENTRY_FIELD_TIME]);
        $this->assertEquals(' file0  .txt ', $f[EntryManagerUnix::ENTRY_FIELD_NAME]);
        $this->assertEquals(EntryManagerUnix::ENTRY_TYPE_FILE, $f[EntryManagerUnix::ENTRY_FIELD_TYPE]);

        $input = 'drw-r--r--    1 10       0               11 Jan 15 14:08  Dir With Spaces ';
        $f = EntryManagerUnix::parse($input, '/path/to/directory');
        $this->assertEquals('drw-r--r--', $f[EntryManagerUnix::ENTRY_FIELD_PERMISSIONS]);
        $this->assertEquals('1', $f[EntryManagerUnix::ENTRY_FIELD_NUMBER]);
        $this->assertEquals('10', $f[EntryManagerUnix::ENTRY_FIELD_OWNER]);
        $this->assertEquals('0', $f[EntryManagerUnix::ENTRY_FIELD_GROUP]);
        $this->assertEquals('11', $f[EntryManagerUnix::ENTRY_FIELD_SIZE]);
        $this->assertEquals('Jan', $f[EntryManagerUnix::ENTRY_FIELD_MONTH]);
        $this->assertEquals('15', $f[EntryManagerUnix::ENTRY_FIELD_DAY]);
        $this->assertEquals('14:08', $f[EntryManagerUnix::ENTRY_FIELD_TIME]);
        $this->assertEquals(' Dir With Spaces ', $f[EntryManagerUnix::ENTRY_FIELD_NAME]);
        $this->assertEquals(EntryManagerUnix::ENTRY_TYPE_DIRECTORY, $f[EntryManagerUnix::ENTRY_FIELD_TYPE]);

        $input = 'lrw-r--r--    1 10       0               11 Jan 15 14:08  Link With Space .txt ';
        $f = EntryManagerUnix::parse($input, '/path/to/directory');
        $this->assertEquals('lrw-r--r--', $f[EntryManagerUnix::ENTRY_FIELD_PERMISSIONS]);
        $this->assertEquals('1', $f[EntryManagerUnix::ENTRY_FIELD_NUMBER]);
        $this->assertEquals('10', $f[EntryManagerUnix::ENTRY_FIELD_OWNER]);
        $this->assertEquals('0', $f[EntryManagerUnix::ENTRY_FIELD_GROUP]);
        $this->assertEquals('11', $f[EntryManagerUnix::ENTRY_FIELD_SIZE]);
        $this->assertEquals('Jan', $f[EntryManagerUnix::ENTRY_FIELD_MONTH]);
        $this->assertEquals('15', $f[EntryManagerUnix::ENTRY_FIELD_DAY]);
        $this->assertEquals('14:08', $f[EntryManagerUnix::ENTRY_FIELD_TIME]);
        $this->assertEquals(' Link With Space .txt ', $f[EntryManagerUnix::ENTRY_FIELD_NAME]);
        $this->assertEquals(EntryManagerUnix::ENTRY_TYPE_LINK, $f[EntryManagerUnix::ENTRY_FIELD_TYPE]);
    }

    public function testParseKo1() {
        $this->expectException(FtpException::class);
        $input = '-Zw-r--r--    1 10      0               11 Jan 15 14:08 file0.txt';
        EntryManagerUnix::parse($input, '/path/to/directory');
    }

    public function testParseKo2() {
        $this->expectException(FtpException::class);
        $input = '-rw-r--r--    X 10      0               11 Jan 15 14:08 file0.txt';
        EntryManagerUnix::parse($input, '/path/to/directory');
    }

    public function testParseKo3() {
        $this->expectException(FtpException::class);
        $input = '-rw-r--r--    1 XX      0               11 Jan 15 14:08 file0.txt';
        EntryManagerUnix::parse($input, '/path/to/directory');
    }

    public function testParseKo4() {
        $this->expectException(FtpException::class);
        $input = '-rw-r--r--    1 12      X               11 Jan 15 14:08 file0.txt';
        EntryManagerUnix::parse($input, '/path/to/directory');
    }

    public function testParseKo5() {
        $this->expectException(FtpException::class);
        $input = '-rw-r--r--    1 10      0               XX Jan 15 14:08 file0.txt';
        EntryManagerUnix::parse($input, '/path/to/directory');
    }

    public function testParseKo6() {
        $this->expectException(FtpException::class);
        $input = '-rw-r--r--    1 10      0               11 000 15 14:08 file0.txt';
        EntryManagerUnix::parse($input, '/path/to/directory');
    }

    public function testParseKo7() {
        $this->expectException(FtpException::class);
        $input = '-rw-r--r--    1 10      0               11 Jan XX 14:08 file0.txt';
        EntryManagerUnix::parse($input, '/path/to/directory');
    }

    public function testParseKo8() {
        $this->expectException(FtpException::class);
        $input = '-rw-r--r--    1 10      0               11 Jan 15 XX:08 file0.txt';
        EntryManagerUnix::parse($input, '/path/to/directory');
    }

    public function testParseKo9() {
        $this->expectException(FtpException::class);
        $input = '-rw-r--r--    1 10      0               11 Jan 15 11:08';
        EntryManagerUnix::parse($input, '/path/to/directory');
    }

    public function testParseKo10() {
        $this->expectException(FtpException::class);
        $input = '-rw-r--r--    1 10      0               11 Jan 15 11:08 ';
        EntryManagerUnix::parse($input, '/path/to/directory');
    }

    public function testParseKo11() {
        $this->expectException(FtpException::class);
        $input = '-rw-r--r--   0 1 10      0               11 Jan 15 11:08 File.txt';
        EntryManagerUnix::parse($input, '/path/to/directory');
    }
}