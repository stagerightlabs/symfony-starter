FROM httpd:2.4

LABEL maintainer="Ryan C. Durham <ryan.durham@firebrandtech.com>"

ARG DEBIAN_FRONTEND=noninteractive

RUN apt-get update && \
    apt-get install -y --no-install-recommends software-properties-common curl

# Configure httpd.conf
RUN echo "Include conf/extra/httpd-vhosts.conf" \
    >> /usr/local/apache2/conf/httpd.conf
RUN echo "LoadModule rewrite_module modules/mod_rewrite.so" >> /usr/local/apache2/conf/httpd.conf && \
    echo "LoadModule proxy_module /usr/local/apache2/modules/mod_proxy.so" >> /usr/local/apache2/conf/httpd.conf && \
    echo "LoadModule proxy_fcgi_module /usr/local/apache2/modules/mod_proxy_fcgi.so" >> /usr/local/apache2/conf/httpd.conf

# Copy apache vhost file to proxy php requests to php-fpm container
COPY symfony-starter.apache.conf /usr/local/apache2/conf/symfony-starter.apache.conf
RUN echo "Include /usr/local/apache2/conf/symfony-starter.apache.conf" \
    >> /usr/local/apache2/conf/httpd.conf
