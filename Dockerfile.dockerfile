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
# 🔧 Ativar mod_rewrite + headers
# ================================
RUN a2enmod rewrite headers

# ================================
# 📦 Instalar Composer
# ================================
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# ================================
# 🌐 Definir DocumentRoot
# ================================
ENV APACHE_DOCUMENT_ROOT /var/www/html/asy/public_html

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# ================================
# 📁 Criar diretório
# ================================
RUN mkdir -p /var/www/html/asy

# ================================
# 📂 Copiar projeto
# ================================
COPY . /var/www/html/asy

# ================================
# 📦 Rodar Composer (IMPORTANTE)
# ================================
WORKDIR /var/www/html/asy
RUN composer install --no-dev --optimize-autoloader

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
