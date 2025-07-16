#!/usr/bin/env node

/**
 * Script de Deploy via Hostinger API
 *
 * Requisitos:
 * - Node.js 18+
 * - Conta Hostinger com API habilitada
 * - Token de API configurado
 */

const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');

// Configurações
const config = {
  hostinger: {
    apiToken: process.env.HOSTINGER_API_TOKEN,
    domain: process.env.HOSTINGER_DOMAIN,
    username: process.env.HOSTINGER_USERNAME,
  },
  paths: {
    backend: './backend',
    frontend: './frontend',
    dist: './dist',
  },
};

class HostingerDeployer {
  constructor() {
    this.apiBase = 'https://api.hostinger.com/v1';
  }

  async deploy() {
    try {
      console.log('🚀 Iniciando deploy via Hostinger API...');

      // 1. Build das aplicações
      await this.buildApplications();

      // 2. Criar pacote de deploy
      await this.createDeployPackage();

      // 3. Upload via API
      await this.uploadToHostinger();

      // 4. Executar deploy
      await this.executeDeploy();

      console.log('✅ Deploy concluído com sucesso!');
    } catch (error) {
      console.error('❌ Erro no deploy:', error.message);
      process.exit(1);
    }
  }

  async buildApplications() {
    console.log('🔨 Buildando aplicações...');

    // Build Frontend
    console.log('⚛️ Buildando React...');
    execSync('npm ci', { cwd: config.paths.frontend, stdio: 'inherit' });
    execSync('npm run build', {
      cwd: config.paths.frontend,
      stdio: 'inherit',
      env: {
        ...process.env,
        VITE_API_URL: `https://${config.hostinger.domain}/api`,
      },
    });

    // Build Backend
    console.log('🔧 Otimizando Laravel...');
    execSync('composer install --no-dev --optimize-autoloader', {
      cwd: config.paths.backend,
      stdio: 'inherit',
    });
    execSync('php artisan config:cache', {
      cwd: config.paths.backend,
      stdio: 'inherit',
    });
    execSync('php artisan route:cache', {
      cwd: config.paths.backend,
      stdio: 'inherit',
    });
    execSync('php artisan view:cache', {
      cwd: config.paths.backend,
      stdio: 'inherit',
    });
  }

  async createDeployPackage() {
    console.log('📦 Criando pacote de deploy...');

    const deployDir = path.join(process.cwd(), 'deploy-package');

    // Limpar diretório anterior
    if (fs.existsSync(deployDir)) {
      fs.rmSync(deployDir, { recursive: true });
    }
    fs.mkdirSync(deployDir);

    // Copiar backend
    const backendDir = path.join(deployDir, 'api');
    fs.mkdirSync(backendDir);
    this.copyDirectory(config.paths.backend, backendDir);

    // Remover arquivos de desenvolvimento do backend
    const devFiles = [
      'tests',
      '.phpunit.cache',
      'storage/logs',
      'storage/framework/cache',
    ];
    devFiles.forEach((file) => {
      const filePath = path.join(backendDir, file);
      if (fs.existsSync(filePath)) {
        fs.rmSync(filePath, { recursive: true });
      }
    });

    // Copiar frontend buildado
    const frontendDist = path.join(config.paths.frontend, 'dist');
    this.copyDirectory(frontendDist, deployDir);

    // Criar .htaccess para API
    const htaccessContent = `
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^ index.php [L]

# Security headers
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY
Header always set X-XSS-Protection "1; mode=block"
    `.trim();

    fs.writeFileSync(path.join(backendDir, '.htaccess'), htaccessContent);

    console.log('✅ Pacote de deploy criado');
  }

  async uploadToHostinger() {
    console.log('📤 Fazendo upload via API...');

    // Aqui você implementaria a lógica de upload via Hostinger API
    // Como a API específica pode variar, vou mostrar um exemplo genérico

    const deployDir = path.join(process.cwd(), 'deploy-package');

    // Criar arquivo ZIP do pacote
    const zipPath = path.join(process.cwd(), 'deploy.zip');
    execSync(`zip -r ${zipPath} .`, { cwd: deployDir, stdio: 'inherit' });

    // Upload via curl (exemplo)
    const uploadUrl = `${this.apiBase}/domains/${config.hostinger.domain}/files/upload`;

    execSync(
      `curl -X POST ${uploadUrl} \
      -H "Authorization: Bearer ${config.hostinger.apiToken}" \
      -H "Content-Type: multipart/form-data" \
      -F "file=@${zipPath}" \
      -F "path=/public_html`,
      { stdio: 'inherit' }
    );

    // Limpar arquivo ZIP
    fs.unlinkSync(zipPath);
  }

  async executeDeploy() {
    console.log('🚀 Executando deploy...');

    // Executar comandos pós-deploy via API
    const commands = [
      'cd /public_html/api && composer install --no-dev --optimize-autoloader',
      'cd /public_html/api && php artisan migrate --force',
      'chmod -R 755 /public_html/api/storage',
      'chmod -R 755 /public_html/api/bootstrap/cache',
    ];

    for (const command of commands) {
      try {
        // Executar comando via API
        const executeUrl = `${this.apiBase}/domains/${config.hostinger.domain}/execute`;

        execSync(
          `curl -X POST ${executeUrl} \
          -H "Authorization: Bearer ${config.hostinger.apiToken}" \
          -H "Content-Type: application/json" \
          -d '{"command": "${command}"}'`,
          { stdio: 'inherit' }
        );
      } catch (error) {
        console.warn(`⚠️ Comando falhou: ${command}`);
      }
    }
  }

  copyDirectory(src, dest) {
    if (!fs.existsSync(dest)) {
      fs.mkdirSync(dest, { recursive: true });
    }

    const entries = fs.readdirSync(src, { withFileTypes: true });

    for (const entry of entries) {
      const srcPath = path.join(src, entry.name);
      const destPath = path.join(dest, entry.name);

      if (entry.isDirectory()) {
        this.copyDirectory(srcPath, destPath);
      } else {
        fs.copyFileSync(srcPath, destPath);
      }
    }
  }
}

// Executar deploy
if (require.main === module) {
  const deployer = new HostingerDeployer();
  deployer.deploy();
}

module.exports = HostingerDeployer;
