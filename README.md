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
    $status = $ftp->deleteIfExists($remote_path);
    $status = $ftp->directoryExists($remote_path);
    $entry = $ftp->entryExists($remote_path);
    $status = $ftp->fileExists($remote_path);
    $entries = $ftp->ls($remote_path, true);
    $ftp->mkdir($remote_path);
    $status = $ftp->mkdirRecursiveIfNotExist($remote_path);
    $ftp->mkdirRecursiveIfNotExist($remote_path);

# Installation

From the command line:

    composer require dbeurive/ftp

Or, from within the file composer.json:

    "require": {
        "dbeurive/ftp": "*"
    }

# Documentation

The code is heavily documented and there is a simple example for each method.  

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

