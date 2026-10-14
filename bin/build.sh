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
    plugin_zip=${target}/${plugin_name}.zip

    # Ensure the target directory exists and remove the previous zip file
    mkdir -p ${target}
    rm -f ${plugin_zip}

    # Turn every .deployignore entry into a git exclude pathspec
    excludes=()
    while IFS= read -r line || [ -n "$line" ]; do
        line=${line%$'\r'}
        line=${line%/}
        if [ -n "$line" ] && [ "${line:0:1}" != "#" ]; then
            excludes+=(":(exclude)${line}")
        fi
    done < .deployignore

    # Zip the committed files into a folder named after the plugin. Needs only git, so it also works in Git Bash on Windows.
    git archive --format=zip --prefix=${plugin_name}/ -o ${plugin_zip} HEAD -- . "${excludes[@]}"

    if [ -n "$(git status --porcelain)" ]; then
        log "The working tree has uncommitted changes. They are not in the zip." "WARNING"
    fi

    log "Plugin ready: ${plugin_zip} (built from $(git rev-parse --short HEAD))"
}

if [ "$#" -ne 1 ]; then
    usage
    exit 1
fi

build $1
