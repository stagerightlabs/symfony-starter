FROM php:8.1-fpm

LABEL maintainer="Ryan C. Durham <ryan@stagerightlabs.com>"

ARG DEBIAN_FRONTEND=noninteractive
ARG UID=1000
ARG GID=1000

# Remove the default www-data user and recreate it with a different UID:GID
RUN userdel www-data && \
    groupadd -g ${GID} www-data && \
    useradd -u ${UID} -g ${GID} -ms /bin/bash www-data && \
    mkdir -p /home/www-data/.config/composer /home/www-data/.config/psysh && \
    chown -R www-data:www-data /home/www-data

# Update the base image
RUN apt-get update && apt-get install -y libpq-dev libicu-dev libzip-dev unzip

# Install the required PHP extensions
RUN docker-php-ext-install pdo pgsql pdo_pgsql intl zip

# Clean up apt cache
RUN  rm -rf /var/cache/apt/lists/*

# PHP INI config
COPY local-fpm-config.ini /usr/local/etc/php/conf.d

# set the working directory
WORKDIR /var/www
