FROM php:8.2-apache

ENV APACHE_DOCUMENT_ROOT /var/www/html/

WORKDIR /var/www/html

# To facilite inside container ;-)
RUN echo "\
alias ll='ls -lha' \
" >> ~/.bashrc


# Change document root for Apache
RUN sed -i -e "s|/var/www/html|${APACHE_DOCUMENT_ROOT}public|g" /etc/apache2/sites-available/000-default.conf


RUN apt-get update && apt-get install -y \
		libfreetype-dev \
		libjpeg62-turbo-dev \
		libpng-dev \
    && docker-php-ext-configure pdo_mysql \
    && docker-php-ext-install -j$(nproc) pdo_mysql \
	&& docker-php-ext-configure gd --with-freetype --with-jpeg \
	&& docker-php-ext-install -j$(nproc) gd



RUN apt-get update && apt-get upgrade && apt install curl git unzip ca-certificates apt-transport-https cron -y --allow-downgrades && \
  curl -sS https://getcomposer.org/installer | php \
  && chmod +x composer.phar && mv composer.phar /usr/local/bin/composer

ENV NODE_VERSION=16.13.0
ENV NPM_VERSION=9.7.1
RUN curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.0/install.sh | bash
ENV NVM_DIR=/root/.nvm
RUN . "$NVM_DIR/nvm.sh" && nvm install ${NODE_VERSION}
RUN . "$NVM_DIR/nvm.sh" && nvm use v${NODE_VERSION}
RUN . "$NVM_DIR/nvm.sh" && nvm alias default v${NODE_VERSION}
ENV PATH="/root/.nvm/versions/node/v${NODE_VERSION}/bin/:${PATH}"
RUN npm install -g npm@${NPM_VERSION}
RUN node --version
RUN npm --version


RUN a2enmod rewrite

COPY composer.json ${APACHE_DOCUMENT_ROOT}
COPY .env.example ${APACHE_DOCUMENT_ROOT}.env
