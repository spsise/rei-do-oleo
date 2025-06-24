# 🐳 Rei do Óleo - Development Container
# Ubuntu 22.04 LTS otimizado para desenvolvimento PHP + Node.js
FROM ubuntu:22.04

# Definir argumentos
ARG USERNAME=vscode
ARG USER_UID=1000
ARG USER_GID=$USER_UID

# Configurar timezone e locale
ENV TZ=America/Sao_Paulo
ENV DEBIAN_FRONTEND=noninteractive
ENV LANG=pt_BR.UTF-8
ENV LANGUAGE=pt_BR:pt
ENV LC_ALL=pt_BR.UTF-8

# 📦 Instalar dependências do sistema base
RUN apt-get update && apt-get install -y \
    # Sistema Base
    software-properties-common \
    ca-certificates \
    lsb-release \
    apt-transport-https \
    gnupg2 \
    curl \
    wget \
    git \
    unzip \
    zip \
    tree \
    htop \
    nano \
    vim \
    locales \
    tzdata \
    # Dependências de Build
    build-essential \
    gcc \
    g++ \
    make \
    # Dependências SSL
    openssl \
    libssl-dev \
    # Dependências de Imagem
    libfreetype6-dev \
    libjpeg-turbo8-dev \
    libpng-dev \
    libwebp-dev \
    libxpm-dev \
    # Dependências XML/HTML
    libxml2-dev \
    libxslt1-dev \
    # Dependências ZIP
    libzip-dev \
    # Dependências MySQL
    default-mysql-client \
    libmysqlclient-dev \
    # Dependências Redis
    redis-tools \
    # SSH & Git
    openssh-client \
    git-extras \
    # Ferramentas de Rede
    net-tools \
    iputils-ping \
    dnsutils \
    && rm -rf /var/lib/apt/lists/*

# 🌍 Configurar locale
RUN locale-gen pt_BR.UTF-8 && \
    update-locale LANG=pt_BR.UTF-8

# ⏰ Configurar timezone
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# 🐘 Instalar PHP 8.2 com extensões essenciais
RUN add-apt-repository ppa:ondrej/php -y && \
    apt-get update && \
    apt-get install -y --no-install-recommends \
    php8.2 \
    php8.2-cli \
    php8.2-fpm \
    php8.2-common \
    php8.2-mysql \
    php8.2-opcache \
    php8.2-zip \
    php8.2-gd \
    php8.2-mbstring \
    php8.2-curl \
    php8.2-xml \
    php8.2-bcmath \
    php8.2-intl \
    php8.2-readline \
    php8.2-redis \
    php8.2-xdebug \
    && rm -rf /var/lib/apt/lists/* /var/cache/apt/archives/*

# 🎼 Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 🟢 Instalar Node.js 18.x LTS
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - && \
    apt-get install -y nodejs

# 🧶 Instalar Yarn
RUN npm install -g yarn

# 🛠️ Instalar ferramentas globais Node.js
RUN npm install -g \
    create-react-app \
    @vitejs/create-vite \
    vite \
    typescript \
    ts-node \
    eslint \
    prettier \
    pm2 \
    nodemon \
    concurrently \
    cross-env \
    serve \
    rimraf

# 🎯 Instalar ferramentas globais PHP
RUN composer global require \
    laravel/installer \
    friendsofphp/php-cs-fixer \
    phpstan/phpstan \
    squizlabs/php_codesniffer \
    psy/psysh \
    symfony/var-dumper \
    --no-interaction --prefer-dist

# 👤 Criar usuário vscode
RUN groupadd --gid $USER_GID $USERNAME \
    && useradd --uid $USER_UID --gid $USER_GID -m $USERNAME -s /bin/bash \
    && apt-get update \
    && apt-get install -y sudo \
    && echo $USERNAME ALL=\(root\) NOPASSWD:ALL > /etc/sudoers.d/$USERNAME \
    && chmod 0440 /etc/sudoers.d/$USERNAME

# 🔧 Configurar PHP para desenvolvimento
RUN echo "memory_limit=512M" >> /etc/php/8.2/cli/conf.d/99-custom.ini && \
    echo "upload_max_filesize=64M" >> /etc/php/8.2/cli/conf.d/99-custom.ini && \
    echo "post_max_size=64M" >> /etc/php/8.2/cli/conf.d/99-custom.ini && \
    echo "max_execution_time=300" >> /etc/php/8.2/cli/conf.d/99-custom.ini && \
    echo "display_errors=On" >> /etc/php/8.2/cli/conf.d/99-custom.ini && \
    echo "log_errors=On" >> /etc/php/8.2/cli/conf.d/99-custom.ini && \
    echo "error_reporting=E_ALL" >> /etc/php/8.2/cli/conf.d/99-custom.ini

# 🐛 Configurar Xdebug
RUN echo "xdebug.mode=debug,coverage,profile" >> /etc/php/8.2/mods-available/xdebug.ini && \
    echo "xdebug.start_with_request=yes" >> /etc/php/8.2/mods-available/xdebug.ini && \
    echo "xdebug.client_host=host.docker.internal" >> /etc/php/8.2/mods-available/xdebug.ini && \
    echo "xdebug.client_port=9003" >> /etc/php/8.2/mods-available/xdebug.ini && \
    echo "xdebug.log=/tmp/xdebug.log" >> /etc/php/8.2/mods-available/xdebug.ini && \
    echo "xdebug.output_dir=/tmp" >> /etc/php/8.2/mods-available/xdebug.ini

# 🚀 Instalar Oh My Zsh para o usuário vscode
USER $USERNAME
RUN sh -c "$(curl -fsSL https://raw.github.com/ohmyzsh/ohmyzsh/master/tools/install.sh)" "" --unattended

# ⚙️ Configurar Zsh com aliases úteis
RUN echo 'export PATH="/home/vscode/.composer/vendor/bin:$PATH"' >> /home/vscode/.zshrc && \
    echo 'export PATH="/workspace/vendor/bin:$PATH"' >> /home/vscode/.zshrc && \
    echo 'export COMPOSER_CACHE_DIR="/home/vscode/.cache/composer"' >> /home/vscode/.zshrc && \
    echo 'export NPM_CONFIG_CACHE="/home/vscode/.cache/npm"' >> /home/vscode/.zshrc && \
    echo '' >> /home/vscode/.zshrc && \
    echo '# 🚀 Laravel Aliases' >> /home/vscode/.zshrc && \
    echo 'alias art="php artisan"' >> /home/vscode/.zshrc && \
    echo 'alias tinker="php artisan tinker"' >> /home/vscode/.zshrc && \
    echo 'alias migrate="php artisan migrate"' >> /home/vscode/.zshrc && \
    echo 'alias migrate:fresh="php artisan migrate:fresh --seed"' >> /home/vscode/.zshrc && \
    echo 'alias seed="php artisan db:seed"' >> /home/vscode/.zshrc && \
    echo 'alias serve="php artisan serve --host=0.0.0.0 --port=8000"' >> /home/vscode/.zshrc && \
    echo 'alias queue="php artisan queue:work"' >> /home/vscode/.zshrc && \
    echo 'alias cache:clear="php artisan cache:clear && php artisan config:clear && php artisan route:clear && php artisan view:clear"' >> /home/vscode/.zshrc && \
    echo '' >> /home/vscode/.zshrc && \
    echo '# ⚛️ React/Node Aliases' >> /home/vscode/.zshrc && \
    echo 'alias dev="npm run dev"' >> /home/vscode/.zshrc && \
    echo 'alias build="npm run build"' >> /home/vscode/.zshrc && \
    echo 'alias test="npm test"' >> /home/vscode/.zshrc && \
    echo 'alias lint="npm run lint"' >> /home/vscode/.zshrc && \
    echo 'alias vite="npx vite"' >> /home/vscode/.zshrc && \
    echo '' >> /home/vscode/.zshrc && \
    echo '# 🗄️ Database Aliases' >> /home/vscode/.zshrc && \
    echo 'alias mysql-cli="mysql -h mysql -u rei_do_oleo -psecret123 rei_do_oleo_dev"' >> /home/vscode/.zshrc && \
    echo 'alias mysql-root="mysql -h mysql -u root -proot123"' >> /home/vscode/.zshrc && \
    echo 'alias redis-cli="redis-cli -h redis"' >> /home/vscode/.zshrc && \
    echo '' >> /home/vscode/.zshrc && \
    echo '# 🔧 Utility Aliases' >> /home/vscode/.zshrc && \
    echo 'alias ll="ls -alF"' >> /home/vscode/.zshrc && \
    echo 'alias la="ls -A"' >> /home/vscode/.zshrc && \
    echo 'alias l="ls -CF"' >> /home/vscode/.zshrc && \
    echo 'alias ..="cd .."' >> /home/vscode/.zshrc && \
    echo 'alias ...="cd ../.."' >> /home/vscode/.zshrc && \
    echo 'alias grep="grep --color=auto"' >> /home/vscode/.zshrc

# 🏠 Voltar para root para configurações finais
USER root

# 📁 Criar diretório de trabalho
WORKDIR /workspace

# 🔒 Configurar Git (valores padrão que podem ser sobrescritos)
RUN git config --system --add safe.directory /workspace && \
    git config --system --add safe.directory '*'

# 📂 Criar diretórios de cache
RUN mkdir -p /home/vscode/.cache/composer && \
    mkdir -p /home/vscode/.cache/npm && \
    chown -R vscode:vscode /home/vscode/.cache

# 🌐 Expor portas
EXPOSE 8000 3000 5173 9000

# 🎯 Comando padrão
CMD ["sleep", "infinity"] 