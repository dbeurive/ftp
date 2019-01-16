#!/usr/bin/env bash

# You should customize this file.

FTP_REMOTE_DIR=/home/ftpuser/ftp/files

cd "${FTP_REMOTE_DIR}"
if [ $? -ne 0 ]; then
    echo "Cannot change directory to \"${FTP_REMOTE_DIR}\" !!!"
    exit 1
fi

if [ ! $(pwd) = "${FTP_REMOTE_DIR}" ]; then
    echo "Cannot change directory to \"${FTP_REMOTE_DIR}\" !!!"
    exit 1
else
    rm -rf *
    if [ $? -ne 0 ]; then
        echo "Error while executing the cleanup script !!!"
        exit 1
    fi

    echo '123456789' > 'file.txt'
    if [ $? -ne 0 ]; then
        echo "Error while executing the cleanup script !!!"
        exit 1
    fi


    echo '123456789' > 'file 1.txt'
    if [ $? -ne 0 ]; then
        echo "Error while executing the cleanup script !!!"
        exit 1
    fi

    echo '123456789' > 'file 2.txt '
    if [ $? -ne 0 ]; then
        echo "Error while executing the cleanup script !!!"
        exit 1
    fi

    echo '123456789' > '  file  3.txt  '
    if [ $? -ne 0 ]; then
        echo "Error while executing the cleanup script !!!"
        exit 1
    fi

    mkdir 'r1'
    if [ $? -ne 0 ]; then
        echo "Error while executing the cleanup script !!!"
        exit 1
    fi

    echo '123456789' > 'r1/file.txt'
    if [ $? -ne 0 ]; then
        echo "Error while executing the cleanup script !!!"
        exit 1
    fi

    mkdir 'r1/rr1'
    if [ $? -ne 0 ]; then
        echo "Error while executing the cleanup script !!!"
        exit 1
    fi

    echo '123456789' > 'r1/rr1/file.txt'
    if [ $? -ne 0 ]; then
        echo "Error while executing the cleanup script !!!"
        exit 1
    fi

    mkdir 'r1/rr1/rrr1'
    if [ $? -ne 0 ]; then
        echo "Error while executing the cleanup script !!!"
        exit 1
    fi

    mkdir 'directory with spaces 1'
    if [ $? -ne 0 ]; then
        echo "Error while executing the cleanup script !!!"
        exit 1
    fi

    mkdir '  directory  with  spaces 2  '
    if [ $? -ne 0 ]; then
        echo "Error while executing the cleanup script !!!"
        exit 1
    fi

    chmod -R a+wr *
fi
