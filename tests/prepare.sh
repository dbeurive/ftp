#!/usr/bin/env bash

SOURCE="${BASH_SOURCE[0]}"
while [ -h "$SOURCE" ] ; do SOURCE="$(readlink "$SOURCE")"; done
typeset -r SCRIPT_DIR="$( cd -P "$( dirname "$SOURCE" )" && pwd )"

. "${SCRIPT_DIR}/setenv.sh"

ssh ${SSH_ARG} "bash -s" < "${SCRIPT_DIR}/clear_on_remote.sh"
if [ $? -ne 0 ]; then
    echo "Cannot execute the cleanup script \"${SCRIPT_DIR}/clear_on_remote.sh\" on the remote host!"
    exit 1
fi

