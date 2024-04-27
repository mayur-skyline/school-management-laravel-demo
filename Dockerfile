# htaccess for Redirect
FROM scratch as htaccess
WORKDIR /htaccess/html/
COPY setup/.htaccess .htaccess

# RM UNIFY SSO
FROM scratch as rmunify
WORKDIR /sso/rmunify/
COPY setup/sso/rmunify rmfile/rmunify
COPY setup/sso/simplesamlphp php/simplesamlphp


# WONDE SSO
FROM scratch as wonde
WORKDIR /sso/wonde/
COPY setup/sso/wonde wonde


# Simple Saml Php Dependencies
FROM composer:latest as simplesamlphpvendor
WORKDIR /app/simplesamlphp/
COPY /setup/sso/simplesamlphp/composer.json composer.json
COPY /setup/sso/simplesamlphp/composer.lock composer.lock

RUN composer install --ignore-platform-reqs



#
# PHP Dependencies
#
FROM composer:latest as vendor

COPY database/ database/

COPY composer.json composer.json
COPY composer.lock composer.lock

RUN composer install \
  --ignore-platform-reqs \
  --no-interaction \
  --no-plugins \
  --no-scripts \
  --prefer-dist

#React/NodeJs
FROM node:16 as frontend

RUN mkdir -p /staticapp/html/astnext
WORKDIR /staticapp/html/astnext/
COPY package.json yarn.lock tsconfig.json webpack.mix.js tailwind.config.js /staticapp/html/astnext/
COPY resources/ /staticapp/html/astnext/resources/

#WORKDIR /staticapp
#add env file dynamically
ARG ENV_URL=forcefail
RUN curl ${ENV_URL} -o .env
RUN NODE_OPTIONS="--max-old-space-size=8192" && yarn install && yarn production
RUN ls -la
#RUN cat .env

#Application
FROM php:8.1-apache-buster as prod

RUN apt-get update && apt-get install -y \
  libpng-dev \
  zlib1g-dev \
  libxml2-dev \
  libzip-dev \
  libssl-dev \
  libonig-dev \
  zip \
  supervisor \
  curl \
  unzip \
  wkhtmltopdf \
  && docker-php-ext-configure gd \
  && docker-php-ext-install -j$(nproc) gd \
  && docker-php-ext-install pdo_mysql \
  && docker-php-ext-install mysqli \
  && docker-php-ext-install zip \
  && docker-php-source delete \
  && rm -rf /var/lib/apt/lists/*


# Configure PHP for Cloud Run.
# Precompile PHP code with opcache.
RUN docker-php-ext-install -j "$(nproc)" opcache
RUN set -ex; \
  { \
  echo "; Cloud Run enforces memory & timeouts"; \
  echo "memory_limit = -1"; \
  echo "max_execution_time = 0"; \
  echo "; File upload at Cloud Run network limit"; \
  echo "upload_max_filesize = 32M"; \
  echo "post_max_size = 32M"; \
  echo "; Configure Opcache for Containers"; \
  echo "opcache.enable = On"; \
  echo "opcache.validate_timestamps = Off"; \
  echo "; Configure Opcache Memory (Application-specific)"; \
  echo "opcache.memory_consumption = 32"; \
  } > "$PHP_INI_DIR/conf.d/cloud-run.ini"

USER root
RUN rm -rf /resources/styles

# Enable apache rewrite
COPY /setup/000-default.conf /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/html/platform/edu/
COPY . ./

# copy supervisor configuration
COPY setup/docker/supervisord.conf /etc/supervisord.conf

COPY setup/media/ /var/www/html/platform/edu/storage/app/public/

COPY --from=vendor /usr/bin/composer /usr/local/bin/composer
# #COPY --from=vendor /app/vendor/ /var/www/html/vendor/
COPY --from=frontend /staticapp/html/astnext/html/astnext /var/www/html/platform/edu/html/astnext
COPY --from=htaccess /htaccess/html /var/www/html
COPY --from=rmunify /sso/rmunify/rmfile/ /var/www/html/sso
COPY --from=rmunify /sso/rmunify/php/ /var
COPY --from=simplesamlphpvendor /app/simplesamlphp/vendor/ /var/simplesamlphp/vendor/
COPY --from=wonde /sso/wonde /var/www/html/sso


RUN rm -rf composer.lock
RUN composer update
#add env file dynamically
ARG ENV_URL=forcefail
RUN curl ${ENV_URL} -o .env

RUN rm -rf /resources/astnext

# Use the PORT environment variable in Apache configuration files.
# https://cloud.google.com/run/docs/reference/container-contract#port
RUN sed -i 's/80/8080/g' /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf
RUN sed -i -e 's/ServerSignature On/ServerSignature Off/g' -e 's/ServerTokens OS/ServerTokens Prod/g' /etc/apache2/conf-enabled/security.conf
RUN cat /etc/apache2/conf-enabled/security.conf

#RUN cat .env
COPY BootstrapEndpoint.php /var/www/html/platform/edu/vendor/wondeltd/php-client/src/Endpoints/BootstrapEndpoint.php
COPY setup/login/ /var/www/html/login/
RUN ls -la

RUN chown -R www-data:www-data /var/www/html \
  && chmod -R 755 /var/www/html/platform/edu/storage \
  && chmod -R 777 /var/www/html/platform/edu/storage/app/public \
  && chmod -R 777 /var/simplesamlphp/ \
  && chmod u+x /etc/supervisord.conf \
  && a2enmod rewrite

# run supervisor
CMD ["/usr/bin/supervisord", "-n", "-c", "/etc/supervisord.conf"]
