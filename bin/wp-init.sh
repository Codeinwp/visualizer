#!/usr/bin/env bash

# Download required plugin and theme ZIPs from GitHub if not already present.
WP_INIT_DIR="$(cd "$(dirname "$0")/.." && pwd)"
mkdir -p "$WP_INIT_DIR/bin/plugins" "$WP_INIT_DIR/bin/themes"
if [ ! -f "$WP_INIT_DIR/bin/plugins/classic-editor.zip" ]; then
    echo "Downloading classic-editor plugin..."
    curl -sL "https://github.com/WordPress/classic-editor/archive/refs/heads/trunk.zip" -o /tmp/classic-editor-raw.zip
    mkdir -p /tmp/classic-editor-build
    unzip -q /tmp/classic-editor-raw.zip -d /tmp/classic-editor-build
    mv /tmp/classic-editor-build/classic-editor-trunk /tmp/classic-editor-build/classic-editor
    ( cd /tmp/classic-editor-build && zip -r "$WP_INIT_DIR/bin/plugins/classic-editor.zip" classic-editor/ -q )
    rm -rf /tmp/classic-editor-raw.zip /tmp/classic-editor-build
fi
if [ ! -f "$WP_INIT_DIR/bin/themes/twentytwentyone.zip" ]; then
    echo "Downloading twentytwentyone theme..."
    curl -sL "https://github.com/WordPress/twentytwentyone/archive/refs/heads/trunk.zip" -o /tmp/twentytwentyone-raw.zip
    mkdir -p /tmp/twentytwentyone-build
    unzip -q /tmp/twentytwentyone-raw.zip -d /tmp/twentytwentyone-build
    mv /tmp/twentytwentyone-build/twentytwentyone-trunk /tmp/twentytwentyone-build/twentytwentyone
    ( cd /tmp/twentytwentyone-build && zip -r "$WP_INIT_DIR/bin/themes/twentytwentyone.zip" twentytwentyone/ -q )
    rm -rf /tmp/twentytwentyone-raw.zip /tmp/twentytwentyone-build
fi

docker compose -f $DOCKER_FILE up -d
# Wait for mysql container to be ready.
while docker compose -f $DOCKER_FILE run --rm -u root cli wp --allow-root db check ; [ $? -ne 0 ];  do
	  echo "Waiting for db to be ready... "
    sleep 1
done
# install WP
docker compose -f $DOCKER_FILE run  --rm -u root cli bash -c "/var/www/html/bin/cli-setup.sh"
