# Instructions pour la construction du cotainer pour php-apache

FROM php:8.1.0-apache

RUN apt-get update \
    && apt-get install -y nano zip unzip git libicu-dev \
    && docker-php-ext-configure intl \
    && docker-php-ext-install pdo pdo_mysql mysqli intl \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer

# défini le répertoire de travail pour les prochaines commandes
WORKDIR /var/www/html
# WORKDIR /var/www

# RUN git clone https://github.com/K20QuickFast237/InchAssurFinal.git
# COPY ../../* ./InchAssurFinal/

WORKDIR /var/www/html/InchAssurFinal
# WORKDIR /var/www/InchAssurFinal

# RUN composer install

RUN chown -R www-data:www-data /var/www/html \
    && composer self-update

# RUN chmod 777 -R /var/www/InchAssurFinal

COPY inchassur.conf /etc/apache2/sites-available/

RUN a2ensite inchassur.conf \
    && service apache2 reload || true

RUN cd /etc/apache2/sites-available \
    && a2dissite 000-default.conf \
    && service apache2 reload || true

# Pour permettre l'écriture sans index.php dans l'url
RUN a2enmod rewrite

EXPOSE 80