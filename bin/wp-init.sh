#!/usr/bin/env bash

# if on windows, find out the IP using `docker-machine ip` and provide the IP as the host.
wp_host='localhost'
windows=`echo $OSTYPE | grep -i -e "win" -e "msys" -e "cygw" | wc -l`
args='-it';
if [[ $windows -gt 0 ]]; then
    wp_host=`docker-machine ip`
    args=''
fi

# sleep for sometime till WP initializes successfully
sleep_time=15
if [[ $windows -gt 0 ]]; then
    sleep_time=30
fi
echo "Sleeping for $sleep_time..."
sleep $sleep_time

# install WP
docker exec $args visualizer_wordpress wp --allow-root core install --url="http://$wp_host:8888/" --admin_user="wordpress" --admin_password="wordpress" --admin_email="test1@xx.com" --title="test" --skip-email

# update core
docker exec $args visualizer_wordpress chown -R www-data:www-data /var/www/html/
docker exec $args visualizer_wordpress chmod 0777 -R /var/www/html/wp-content
docker exec $args visualizer_wordpress wp --allow-root core update --version=5.5
docker exec $args visualizer_wordpress wp --allow-root core update-db

# install required external plugins
docker exec $args visualizer_wordpress wp plugin install classic-editor --activate

# install visualizer free
docker exec $args visualizer_wordpress git clone https://github.com/Codeinwp/visualizer /var/www/html/wp-content/plugins/visualizer

# activate
docker exec $args visualizer_wordpress wp --allow-root plugin activate visualizer

# set this constant so that the license is not checked
docker exec $args visualizer_wordpress wp --allow-root config set TI_UNIT_TESTING true --raw

# set this constant so that the specific hooks are loaded
docker exec $args visualizer_wordpress wp --allow-root config set TI_CYPRESS_TESTING true --raw

# debugging
docker exec $args visualizer_wordpress wp --allow-root config set WP_DEBUG true --raw
docker exec $args visualizer_wordpress wp --allow-root config set WP_DEBUG_LOG true --raw
docker exec $args visualizer_wordpress wp --allow-root config set WP_DEBUG_DISPLAY false --raw
