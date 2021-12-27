#!/usr/bin/env bash

# Heavily borrowed from Cris Fidao and the "Shipping Docker" course
# https://serversforhackers.com/shipping-docker

# Set environment variables for local development
# export APP_PORT=${APP_PORT:-80}

COMPOSE="docker-compose"

# If an argument has been provided then we want to delegate that command to a service
if [ $# -gt 0 ];then

    # If the first argument is "console" pass the command through to bin/console
    if [ "$1" == "console" ]; then
        shift 1
        $COMPOSE exec \
            cli \
            php bin/console "$@"

    # If the first argument is "server:dump" start a server:dump session with the FPM service.
    elif [ "$1" == "server:dump" ]; then
        $COMPOSE exec \
            fpm \
            php bin/console server:dump

    # If the first argument is "composer" pass the command through to composer
    elif [ "$1" == "composer" ]; then
        shift 1
        $COMPOSE exec \
            cli \
            composer "$@"

    # # If the first argument is "test" pass the command through to phpunit
    elif [ "$1" == "test" ]; then
        shift 1
        $COMPOSE exec \
            cli \
            ./vendor/bin/phpunit "$@"

    # If the first argument is "npm" pass the command through to npm
    elif [ "$1" == "npm" ]; then
        shift 1
        $COMPOSE run --rm \
            node \
            npm "$@"

    # If the first argument is "psql" drop into the postgres cli
    elif [ "$1" == "psql" ]; then
        shift 1
        $COMPOSE exec \
            --user=postgres \
            postgres \
            psql "$@"

    # Otherwise pass the command to docker-compose
    else
        $COMPOSE "$@"
    fi

else
    # If no argument was provided display the status of the containers
    $COMPOSE ps
fi
