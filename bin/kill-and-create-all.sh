# to be executed from the plugin main directory (visualizer-pro)
docker kill $(docker ps -q)
docker system prune -a

windows=`echo $OSTYPE | grep -i -e "win" -e "msys" -e "cygw" | wc -l`
if [[ $windows -gt 0 ]]; then
    docker-machine start default
fi

docker-compose -f docker-compose.travis.yml up -d
./bin/wp-init.sh
./bin/run-e2e-tests.sh