#!/usr/bin/env bash

if [[ "$TRAVIS" == "true" ]]; then
    npm install --only=dev --prefix ./cypress/
    composer install --no-dev
fi

wp_host='localhost'
windows=`echo $OSTYPE | grep -i -e "win" -e "msys" -e "cygw" | wc -l`
args='-it';
if [[ $windows -gt 0 ]]; then
    wp_host=`docker-machine ip`
    args=''
fi

# exit on error
set -e

export CYPRESS_HOST=$wp_host

# test free - sources
export CYPRESS_SPEC_TO_RUN="free-sources.js"
npm run cypress:run
