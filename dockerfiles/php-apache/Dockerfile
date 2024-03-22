# Instructions pour la construction du cotainer pour php-apache

FROM php:8.1.0-apache

RUN apt-get update \
    && apt-get install -y nano zip unzip git libicu-dev \
    && docker-php-ext-configure intl \
    && docker-php-ext-install intl \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer

# défini le répertoire de travail pour les prochaines commandes
WORKDIR /var/www/html

RUN composer create-project codeigniter4/appstarter app --prefer-dist --stability=stable --no-interaction

RUN chown -R www-data:www-data /var/www/html \
    && composer self-update

COPY codeigniter.conf /etc/apache2/sites-available/

RUN a2ensite codeigniter.conf \
    && service apache2 reload || true

RUN cd /etc/apache2/sites-available \
    && a2dissite 000-default.conf \
    && service apache2 reload || true

EXPOSE 80