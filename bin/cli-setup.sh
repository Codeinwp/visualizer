
# install WP
wp  --allow-root core install --url="http://localhost:8080" --admin_user="wordpress" --admin_password="wordpress" --admin_email="test1@xx.com" --title="test" --skip-email
mkdir -p /var/www/html/wp-content/uploads
chmod -R 777 /var/www/html/wp-content/uploads/*
wp  --allow-root  plugin install classic-editor --activate

# activate
wp  --allow-root plugin activate visualizer
wp  --allow-root theme activate twentynineteen

# set this constant so that the specific hooks are loaded
wp  --allow-root config set TI_CYPRESS_TESTING true --raw


# debugging
wp  --allow-root config set WP_DEBUG true --raw
wp  --allow-root config set WP_DEBUG_LOG true --raw
wp  --allow-root config set WP_DEBUG_DISPLAY false --raw

