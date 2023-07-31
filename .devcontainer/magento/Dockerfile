ARG PHP_VERSION=8.1
# https://hub.docker.com/_/php
FROM php:$PHP_VERSION-apache-bookworm

ENV LANG=C.UTF-8

RUN set -eux; \
    # enable Apache2 modules
    a2enmod rewrite expires include deflate remoteip headers; \
    # disable Apache2 server signature
    echo 'ServerSignature Off' >> /etc/apache2/apache2.conf; \
    echo 'ServerTokens Prod' >> /etc/apache2/apache2.conf; \
	# enable support for TLS termination
	echo 'SetEnvIf X-Forwarded-Proto https HTTPS=on' >> /etc/apache2/apache2.conf; \
    # install common PHP extensions
    savedAptMark="$(apt-mark showmanual)"; \
    apt-get update; \
    apt-get install -y --no-install-recommends \
    libfreetype6-dev libicu-dev libjpeg62-turbo-dev libonig-dev \
    libpng-dev libxml2-dev libxslt1-dev libzip-dev libwebp-dev \
    ${PHP_EXTRA_BUILD_DEPS:-}; \
    # https://www.php.net/manual/en/image.installation.php
    docker-php-ext-configure gd --enable-gd --with-freetype --with-jpeg --with-webp; \
    docker-php-ext-install -j$(nproc) opcache \
    intl gd mysqli pcntl pdo_mysql soap xsl zip; \
    # reset apt-mark's "manual" list so that "purge --auto-remove" will remove all build dependencies
    apt-mark auto '.*' > /dev/null; \
    [ -z "$savedAptMark" ] || apt-mark manual $savedAptMark; \
    find /usr/local -type f -executable -exec ldd '{}' ';' \
    | awk '/=>/ { so = $(NF-1); if (index(so, "/usr/local/") == 1) { next }; gsub("^/(usr/)?", "", so); print so }' \
    | sort -u \
    | xargs -r dpkg-query --search \
    | cut -d: -f1 \
    | sort -u \
    | xargs -r apt-mark manual; \
    apt-get purge -y --auto-remove -o APT::AutoRemove::RecommendsImportant=false; \
    rm -rf /var/lib/apt/lists/*;
RUN set -eux; \
    # https://github.com/FriendsOfPHP/pickle
    curl -Lo /usr/local/bin/pickle https://github.com/FriendsOfPHP/pickle/releases/latest/download/pickle.phar && \
    chmod +x /usr/local/bin/pickle; \
    pickle install --no-interaction apcu; \
    pickle install --no-interaction redis; \
    pickle install --no-interaction xdebug; \
    docker-php-ext-enable apcu redis xdebug; \
    # https://github.com/colinmollenhour/modman
    curl -fSL -o /usr/local/bin/modman https://raw.githubusercontent.com/colinmollenhour/modman/master/modman && \
    chmod +x /usr/local/bin/modman; \
    # https://github.com/netz98/n98-magerun
    curl -fSL -o /usr/local/bin/n98-magerun https://files.magerun.net/n98-magerun.phar && \
    chmod +x /usr/local/bin/n98-magerun;

# https://hub.docker.com/_/composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

ARG OPENMAGE_VERSION=20.0.20
# Download and extract to web volume
RUN set -ex; \
	curl -o magento.tar.gz -SL https://github.com/OpenMage/magento-lts/archive/refs/tags/v${OPENMAGE_VERSION}.tar.gz; \
	tar -xf magento.tar.gz -C /usr/src/; \
	rm magento.tar.gz; \
    mv /usr/src/magento-lts-${OPENMAGE_VERSION} /usr/src/magento; \
	chown -R www-data:www-data /usr/src/magento; \
    chmod -R g+w /usr/src/magento;