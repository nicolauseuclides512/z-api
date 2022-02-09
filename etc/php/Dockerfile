FROM php:7.2-fpm
MAINTAINER jee.archer@gmail.com

RUN buildDeps=" \
            default-libmysqlclient-dev \
            libbz2-dev \
            libmemcached-dev \
            libsasl2-dev \
        " \
        runtimeDeps=" \
            curl \
            git \
            libfreetype6-dev \
            libicu-dev \
            libjpeg-dev \
            libldap2-dev \
            libmemcachedutil2 \
            libpng-dev \
            libpq-dev \
            libxml2-dev \
        " \
        && apt-get update --fix-missing \
        && DEBIAN_FRONTEND=noninteractive apt-get install -y $buildDeps $runtimeDeps \
        && docker-php-ext-install bcmath \
        bz2 \
        calendar \
        iconv \
        intl \
        mbstring \
#        mysqli \
        opcache \
#        pdo_mysql \
        pdo_pgsql \
        pgsql \
        soap \
        zip \
        && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
        && docker-php-ext-install gd \
        && docker-php-ext-configure ldap --with-libdir=lib/x86_64-linux-gnu/ \
        && docker-php-ext-install ldap \
        && docker-php-ext-install exif \
        && pecl install memcached redis \
        && docker-php-ext-enable memcached.so redis.so \
        && apt-get install -y supervisor \
        && apt-get install -y --no-install-recommends zlib1g fontconfig libfreetype6 libx11-6 libxext6 libxrender1 \
#        && apt-get install -y --force-yes xorg libssl-dev libxrender-dev fontconfig xfonts-75dpi libcurl4-gnutls-dev libxml2-dev libpng-dev libicu-dev libmcrypt-dev libjpeg62-turbo-dev libfreetype6-dev libjpeg62-turbo zlib1g-dev libmemcached11 libmemcached-dev git libgmp-dev psmisc xpdf libmagickwand-dev imagemagick xfonts-utils cabextract wget gnupg \
        && apt-get purge -y --auto-remove $buildDeps \
        && rm -r /var/lib/apt/lists/*


VOLUME /var/www/

WORKDIR /var/www/

COPY config/xdebug.ini /etc/php/7.2/mods-available/xdebug.ini

COPY config/worker.conf /etc/supervisor/conf.d

COPY ./config/run.sh /

RUN touch /var/log/supervisor/worker.log && chmod +x /var/log/supervisor/worker.log

RUN ln -sf /dev/stdout /var/log/supervisor/worker.log

RUN chmod 777 /run.sh

EXPOSE 9000

CMD /run.sh