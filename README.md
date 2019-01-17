# Introduction

This repository contains a wrapper around the (pretty good) PHP functionalities for FTP. 

# Synopsis

    $options = array(Ftp::OPTION_PORT => $port, Ftp::OPTION_TIMEOUT => 60);
    
    $ftp = new Ftp($host, $options);
    $ftp->connect();
    $ftp->login($user, $password);

    $ftp->get($local_path, $remote_path); 
    $ftp->put($local_path, $remote_path); 
    $ftp->delete($remote_path);           
    $ftp->mkdir($remote_path);
    $entries = $ftp->ls($remote_path, true);
        
    $status = $ftp->deleteIfExists($remote_path);
    $status = $ftp->fileExists($remote_path);
    $status = $ftp->directoryExists($remote_path);
    $entry = $ftp->entryExists($remote_path);
    $status = $ftp->mkdirRecursiveIfNotExist($remote_path);
    
    $ftp->setEntryClassName($your_own_class);

# Installation

From the command line:

    composer require dbeurive/ftp

Or, from within the file composer.json:

    "require": {
        "dbeurive/ftp": "*"
    }

# Documentation

The code is heavily documented and there is a simple example for each method.  

* **connect()**: Open a connexion to the server.
* **disconnect()**: Close the connexion to the server.
* **login($in_user_name, $in_password)**: Authenticate to the server.
* **isLogged()**: Test whether the client has authenticated to the server.
* **ls($in_opt_dir='.', $in_opt_throw_exception_on_error=false)**: List the content of a directory identified by its given path. This function parses the output returned by the server.
* **put($in_local_file_path, $in_remote_file_path, $in_opt_mode=FTP_BINARY)**: Put a file from the local host to the remote server.
* **get($in_local_file_path, $in_remote_file_path, $in_opt_mode=FTP_BINARY)**: Get a file from the remote server to the local host.
* **mkdir($in_directory_path)**: Create a directory on the remote host.
* **mkdirRecursiveIfNotExist($in_directory_path)**: Recursively create a directory identified by its given path, if the directory does not already exist.
* **rmdir($in_directory_path):** Remove a directory identified by its given path.
* **delete($in_file_path)**: Delete a file identified by its given path on the remote server.
* **deleteIfExists($in_directory_path)**: Test whether a file exists, and if it does, then delete it.
* **entryExists($in_entry_path)**: Test whether an entry (directory, file of link), identified by its given path, exists or not.
* **directoryExists($in_directory_path)**: Test whether a directory, identified by its given path, exists or not.
* **fileExists($in_file_path)**: Test whether a file, identified by its given path, exists or not.
* **setEntryClassName($in_class_name)**: Set the name of the class that represents en entry.

The last method (`setEntryClassName`) needs to be described with more details.

The FTP command LIST returns a text that represents the list of entries (directories, files or links) in the remote
directory. For example:

    -rw-r--r--    1 0        0               1 Jan 15 14:08 file0.txt
    -rw-r--r--    1 0        0               2 Jan 15 14:08 file1.txt
    -rw-r--r--    1 0        0               3 Jan 15 14:08 file2.txt
    -rw-r--r--    1 0        0               4 Jan 15 14:08 file3.txt
    drwxr-xr-x    2 0        0            4096 Jan 15 14:08 r1
    drwxr-xr-x    2 0        0            4096 Jan 15 14:08 r2
    drwxr-xr-x    2 0        0            4096 Jan 15 14:08 r3
    drwxr-xr-x    2 0        0            4096 Jan 15 14:08 r4

Please note that:

* the organisation of the text returned by the FTP command LIST may vary from one OS to another, or from 
one FTP server to another.
* depending on the OS or the FTP server, the properties associated with the entries may not be identical. 

However, this text must be parsed in order to extract the list of entries, along with their properties (permissions,
owners, groups...).

It is not possible to handle all possible text organisations (for all OS and maybe, all FTP servers) and all possible
properties.

Thus, the FTP wrapper lets the user the possibility to declare its own class that handles the parsing of the text and
manages the properties. The default class used is [EntryUnix](https://github.com/dbeurive/ftp/blob/master/src/EntryUnix.php).

However, you can write and declare your own class to handle another use case. Your class must extends the abstract class
[AbstractEntry](https://github.com/dbeurive/ftp/blob/master/src/AbstractEntry.php).

# Unit tests

In order to run the unit tests, you need the following elements:

* a host running an FTP server and an SSH server.
* access to the FTP server and to the SSH server.

> SSH is used to execute shell commands on the host that runs the FTP server.
> These commands will prepare the environment for the tests.

## Preparation

### Edit tests/setenv.sh

This script defines parameters used during the unit tests.
These parameters are:

#### The parameters below used to (unit) test valid test cases

* **FTP_REMOTE_HOST_OK**: host name or IP address of the FTP server.
* **FTP_USER_OK**: user name used to authenticate to the FTP server.
* **FTP_PASSWORD_OK**: password used to authenticate to the FTP server.
* **FTP_PORT_OK**: TCP port used by the TP server (ex: 21).

#### The parameters used to (unit) test invalid test cases

* **FTP_REMOTE_HOST_KO**: name or IP address of a host that does not listen for FTP connexion requests.
* **FTP_USER_KO**: invalid user name.
* **FTP_PASSWORD_KO**: invalid password
* **FTP_PORT_KO**: invalid TCP port number.
* **ROOT_ON_REMOTE**: _FTP path_, on the remote host (that runs the FTP server), used to get and put files through FTP.
* **BAD_DIR_ON_REMOTE**: invalid _FTP path_, on the remote host (that runs the FTP server).

#### The parameters used to set up the environment on the host that run the FTP server

* **SSH_ARG**: command line argument(s) for the `ssh̀` command used to connect, through SSH, to the host that runs the FTP server.

Example:

    export FTP_REMOTE_HOST_OK='10.11.12.13'
    export FTP_USER_OK='good_ftp_user_name'
    export FTP_PASSWORD_OK='goot_ftp_password'
    export FTP_PORT_OK=21    
    export FTP_REMOTE_HOST_KO='yahoo.com'
    export FTP_USER_KO='invalid_user_name'
    export FTP_PASSWORD_KO='invalid password'
    export FTP_PORT_KO=10021
    export ROOT_ON_REMOTE='/files';
    export BAD_DIR_ON_REMOTE='/tmp';
    export SSH_ARG='root@127.0.0.1'

### Edit tests/clear_on_remote.sh

The script `tests/clear_on_remote.sh` will be executed on the host that runs the FTP server.
It will prepare the environment for the unit tests.

Set the correct value for **FTP_REMOTE_DIR**.

**FTP_REMOTE_DIR**: absolute _system path_, on the host that runs the server, used to get or put files through FTP.

Example:

    FTP_REMOTE_DIR=/home/ftpuser/ftp/files

### Run the unit tests

Run the commands below:

    . ./tests/setenv.sh
    ./vendor/bin/phpunit

