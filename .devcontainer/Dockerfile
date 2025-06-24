FROM php:8.2-fpm

# Definir argumentos
ARG USERNAME=vscode
ARG USER_UID=1000
ARG USER_GID=$USER_UID

# Instalar dependências do sistema
RUN apt-get update && apt-get install -y \
    # Dependências básicas
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    wget \
    gnupg2 \
    software-properties-common \
    ca-certificates \
    lsb-release \
    # Dependências MySQL
    default-mysql-client \
    libmysqlclient-dev \
    # Dependências Redis
    redis-tools \
    # Utilitários desenvolvimento
    nano \
    vim \
    htop \
    tree \
    jq \
    # Dependências SSL
    openssl \
    # Dependências imagem
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libwebp-dev \
    libxpm-dev \
    # Dependências Git
    openssh-client \
    && rm -rf /var/lib/apt/lists/*

# Instalar extensões PHP
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp --with-xpm \
    && docker-php-ext-install \
    pdo \
    pdo_mysql \
    mysqli \
    mbstring \
    xml \
    zip \
    bcmath \
    gd \
    exif \
    intl \
    opcache

# Instalar Redis PHP extension
RUN pecl install redis \
    && docker-php-ext-enable redis

# Instalar Xdebug para desenvolvimento
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

# Configurar Xdebug
RUN echo "xdebug.mode=debug,coverage" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.start_with_request=yes" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.client_host=host.docker.internal" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.client_port=9003" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.log=/tmp/xdebug.log" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# Configurar PHP para desenvolvimento
RUN echo "memory_limit=512M" >> /usr/local/etc/php/conf.d/custom.ini \
    && echo "upload_max_filesize=64M" >> /usr/local/etc/php/conf.d/custom.ini \
    && echo "post_max_size=64M" >> /usr/local/etc/php/conf.d/custom.ini \
    && echo "max_execution_time=300" >> /usr/local/etc/php/conf.d/custom.ini \
    && echo "display_errors=On" >> /usr/local/etc/php/conf.d/custom.ini \
    && echo "log_errors=On" >> /usr/local/etc/php/conf.d/custom.ini

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Instalar Node.js 18
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - \
    && apt-get install -y nodejs

# Instalar ferramentas globais Node.js
RUN npm install -g \
    @vue/cli \
    @angular/cli \
    create-react-app \
    vite \
    typescript \
    ts-node \
    eslint \
    prettier \
    pm2 \
    nodemon

# Instalar ferramentas PHP globais
RUN composer global require \
    laravel/installer \
    friendsofphp/php-cs-fixer \
    phpstan/phpstan \
    phpunit/phpunit \
    squizlabs/php_codesniffer \
    psy/psysh

# Adicionar Composer global bin ao PATH
ENV PATH="/root/.composer/vendor/bin:${PATH}"

# Criar usuário vscode
RUN groupadd --gid $USER_GID $USERNAME \
    && useradd --uid $USER_UID --gid $USER_GID -m $USERNAME -s /bin/bash \
    && apt-get update \
    && apt-get install -y sudo \
    && echo $USERNAME ALL=\(root\) NOPASSWD:ALL > /etc/sudoers.d/$USERNAME \
    && chmod 0440 /etc/sudoers.d/$USERNAME

# Instalar Oh My Zsh para o usuário vscode
USER $USERNAME
RUN sh -c "$(curl -fsSL https://raw.github.com/ohmyzsh/ohmyzsh/master/tools/install.sh)" "" --unattended

# Configurar Zsh
RUN echo 'export PATH="/home/vscode/.composer/vendor/bin:$PATH"' >> /home/vscode/.zshrc \
    && echo 'alias art="php artisan"' >> /home/vscode/.zshrc \
    && echo 'alias tinker="php artisan tinker"' >> /home/vscode/.zshrc \
    && echo 'alias migrate="php artisan migrate"' >> /home/vscode/.zshrc \
    && echo 'alias seed="php artisan db:seed"' >> /home/vscode/.zshrc \
    && echo 'alias serve="php artisan serve --host=0.0.0.0"' >> /home/vscode/.zshrc \
    && echo 'alias npm-dev="npm run dev"' >> /home/vscode/.zshrc \
    && echo 'alias npm-build="npm run build"' >> /home/vscode/.zshrc

# Voltar para root para configurações finais
USER root

# Criar diretório de trabalho
WORKDIR /workspace

# Configurar Git (valores padrão que podem ser sobrescritos)
RUN git config --system --add safe.directory /workspace

# Expor portas
EXPOSE 8000 5173 3000 9000

# Comando padrão
CMD ["php-fpm"] 