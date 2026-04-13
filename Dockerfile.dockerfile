FROM php:8.2-apache

# ================================
# 🔧 Dependências do sistema
# ================================
RUN apt-get update && apt-get install -y \
    unzip \
    zip \
    git \
    libzip-dev \
    && docker-php-ext-install zip

# ================================
# 🔧 Extensões PHP
# ================================
RUN docker-php-ext-install mysqli pdo pdo_mysql

# ================================
# 🔧 Apache
# ================================
RUN a2enmod rewrite headers

# ================================
# 📦 Composer
# ================================
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# ================================
# 📁 Diretório do app
# ================================
WORKDIR /var/www/html/asy

# ================================
# ⚡ OTIMIZA CACHE DO DOCKER
# ================================

# Copia primeiro só os arquivos do composer
COPY composer.json composer.lock ./

# Instala dependências
RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader

# Agora copia o restante do projeto
COPY . .

# ================================
# 🌐 DocumentRoot
# ================================
ENV APACHE_DOCUMENT_ROOT /var/www/html/asy/public_html

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# ================================
# 🔒 Segurança Apache
# ================================
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf \
    && echo "ServerSignature Off" >> /etc/apache2/apache2.conf \
    && echo "ServerTokens Prod" >> /etc/apache2/apache2.conf

# ================================
# 🔒 Segurança PHP
# ================================
RUN echo "expose_php=Off" > /usr/local/etc/php/conf.d/security.ini

# ================================
# 🔐 Permissões
# ================================
RUN chown -R www-data:www-data /var/www/html

# ================================
# 🚀 Porta
# ================================
EXPOSE 80
