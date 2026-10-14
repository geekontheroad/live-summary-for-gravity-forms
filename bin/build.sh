#!/bin/bash
set -e

function log() {
    message=$1
    level=${2:-"INFO"}

    echo "[$(date -u)] ${level}: ${message}"
}

function usage() {
    echo "Usage: $0 PLUGIN_NAME"
}

function build() {
    plugin_name=$1

    target=target
    plugin_dir=${plugin_name}
    plugin_zip=${plugin_name}.zip

    # Ensure the target directory exists
    mkdir -p ${target}

    # Clean up previous build folder and zip file
    rm -rf ${target}/${plugin_dir}
    rm -f ${target}/${plugin_zip}

    # Create a temporary directory with the plugin name
    mkdir -p ${target}/${plugin_dir}

    # Copy all files (excluding .deployignore entries) into the temporary folder
    rsync -av --exclude-from=.deployignore ./ ${target}/${plugin_dir}/

    # Create the zip file containing the folder
    (cd ${target} && zip -r ${plugin_zip} ${plugin_dir})

    # Clean up the temporary folder
    rm -rf ${target}/${plugin_dir}

    log "Plugin ready: ${target}/${plugin_zip}"
}

if [ "$#" -ne 1 ]; then
    usage
    exit 1
fi

build $1
