#!/bin/bash

# Script to generate POT file via Docker
# Usage: ./bin/make-pot.sh [plugin-path] [destination-path]

# Set defaults
PLUGIN_PATH="${1:-.}"
DESTINATION="${2:-.}"

# Resolve absolute paths
PLUGIN_PATH="$(cd "$PLUGIN_PATH" 2>/dev/null && pwd)" || {
    echo "Error: Plugin path '$1' does not exist"
    exit 1
}

DESTINATION="$(cd "$(dirname "$DESTINATION")" 2>/dev/null && pwd)/$(basename "$DESTINATION")" || {
    echo "Error: Unable to resolve destination path"
    exit 1
}

# Extract destination filename and directory
DEST_DIR="$(dirname "$DESTINATION")"
DEST_FILE="$(basename "$DESTINATION")"

# Ensure destination directory exists
mkdir -p "$DEST_DIR"

echo "Generating POT file..."
echo "Plugin Path: $PLUGIN_PATH"
echo "Destination: $DESTINATION"
echo ""

# Run Docker container with wp-cli to generate POT
docker run --user root --rm \
    --volume "$PLUGIN_PATH:/var/www/html/plugin" \
    wordpress:cli \
    bash -c 'php -d memory_limit=512M "$(which wp)" --version --allow-root && wp i18n make-pot plugin ./plugin/languages/'"$DEST_FILE"' --include=admin,includes,libs,assets,views --allow-root --domain=anti-spam'

# Check if the file was created inside the container
if [ $? -eq 0 ]; then
    echo ""
    echo "✓ POT file successfully generated at: $DESTINATION"
else
    echo ""
    echo "✗ Error generating POT file"
    exit 1
fi
