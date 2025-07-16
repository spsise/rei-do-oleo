# 🔧 Troubleshooting - Dev Container

## 🚨 Problema Crítico: Husky Permission Denied

### ❌ Erro: `sh: 1: husky: Permission denied`

**Sintoma:**
```
npm error code 126
npm error command sh -c husky install
npm error A complete log of this run can be found in: /home/vscode/.cache/npm/_logs/...
```

**Causa:** Husky não consegue executar no ambiente devcontainer devido a problemas de permissão.

**✅ Soluções:**

1. **Configuração Automática Corrigida:**
   - O script de setup agora trata falhas do Husky como não-críticas
   - O setup não falha mais se o Husky não conseguir ser configurado

2. **Configuração Manual do Husky:**
   ```bash
   # Dentro do devcontainer
   npm run setup:husky
   ```

3. **Script de Troubleshooting:**
   ```bash
   # Diagnóstico completo
   npm run troubleshoot
   ```

4. **Configuração Manual Alternativa:**
   ```bash
   # Se o script automático falhar
   npx husky install
   npx husky add .husky/pre-commit "npx lint-staged"
   chmod +x .husky/*
   ```

### 🔧 Scripts de Recuperação Disponíveis

- `npm run setup:husky` - Configuração manual do Husky
- `npm run troubleshoot` - Diagnóstico completo do ambiente
- `npm run dev` - Iniciar desenvolvimento (funciona sem Husky)

## Problemas Comuns e Soluções

### ❌ Erro: Unable to locate package libjpeg62-turbo-dev

**Problema:** Durante a construção do container, ocorre erro sobre pacote não encontrado.

**Solução:** Alterado para `libjpeg-turbo8-dev` (nome correto no Ubuntu 22.04).

### ❌ Erro: Package libavif-dev not available

**Problema:** Pacote libavif-dev pode não estar disponível em certas versões.

**Solução:** Removido da lista de dependências - não é essencial para o desenvolvimento.

### 🔄 Rebuild do Container

Se o container ainda apresentar problemas:

1. **Limpar cache do Docker:**
```bash
docker system prune -af
```

2. **Rebuild do dev container:**
```bash
# No VSCode: Ctrl+Shift+P
# > Dev Containers: Rebuild Container
```

3. **Limpar completamente:**
```bash
docker container prune -f
docker image prune -af
docker volume prune -f
docker network prune -f
```

### 📋 Verificar Logs Detalhados

Para debug avançado:
```bash
# Ver logs durante build
docker build . -f .devcontainer/Dockerfile --progress=plain --no-cache

# Verificar se todos serviços estão funcionando
docker-compose -f .devcontainer/docker-compose.yml ps
```

### 🛠️ Pacotes Críticos Instalados

- **PHP 8.2** com extensões: mysql, gd, zip, redis, xdebug
- **Node.js 18.x** LTS com npm e yarn
- **MySQL 8.0** client
- **Redis** tools
- **Composer** latest
- **Git** + SSH tools

### 📞 Suporte

Se problemas persistirem:
1. Verificar versão do Docker Desktop
2. Verificar se WSL2 está atualizado (Windows)
3. Garantir que extensão Dev Containers está atualizada
4. Reiniciar Docker Desktop completamente

# 🔧 Troubleshooting - Dev Container Rei do Óleo

Este guia contém soluções para problemas comuns encontrados durante o desenvolvimento no Dev Container.

## 🐛 Problemas de Build

### ❌ Erro: `@vueuse/cli` not found
**Sintoma:**
```
npm error 404 Not Found - GET https://registry.npmjs.org/@vueuse%2fcli - Not found
```

**Causa:** O pacote `@vueuse/cli` não existe no registry npm.

**✅ Solução:** Já corrigido no Dockerfile atual. O pacote foi removido e substituído por ferramentas adequadas para React.

**Pacotes corretos instalados:**
- `@vitejs/create-vite` - Para criação de projetos Vite
- `vite` - Build tool moderno
- `serve` - Servidor estático
- `rimraf` - Limpeza de diretórios cross-platform

### ❌ Erro: Container não conecta ao MySQL
**Sintoma:**
```
SQLSTATE[HY000] [2002] Connection refused
```

**✅ Soluções:**
1. Aguarde os serviços iniciarem completamente:
   ```bash
   docker-compose logs mysql
   ```

2. Verifique se o MySQL está rodando:
   ```bash
   docker-compose ps
   ```

3. Teste a conexão manualmente:
   ```bash
   mysql -h mysql -u rei_do_oleo -psecret123
   ```

### ❌ Erro: PHP extensions missing
**Sintoma:**
```
PHP Fatal error: Class 'PDO' not found
```

**✅ Solução:** Rebuild o container:
```bash
# Limpar cache
docker system prune -a

# Rebuild sem cache
docker-compose build --no-cache devcontainer
```

## 🗄️ Problemas de Banco de Dados

### ❌ Erro: Database 'rei_do_oleo_dev' doesn't exist
**✅ Solução:**
```bash
# Entrar no container MySQL
docker exec -it rei-do-oleo-mysql-1 bash

# Conectar como root
mysql -u root -proot123

# Criar database
CREATE DATABASE rei_do_oleo_dev CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT ALL PRIVILEGES ON rei_do_oleo_dev.* TO 'rei_do_oleo'@'%';
FLUSH PRIVILEGES;
```

### ❌ Erro: Redis connection refused
**✅ Soluções:**
1. Verificar se Redis está rodando:
   ```bash
   docker-compose logs redis
   ```

2. Testar conexão:
   ```bash
   redis-cli -h redis ping
   ```

3. Reiniciar Redis se necessário:
   ```bash
   docker-compose restart redis
   ```

## 🔧 Problemas de Performance

### ❌ Container muito lento
**✅ Soluções:**
1. Alocar mais recursos ao Docker:
   - Memory: mínimo 4GB
   - CPU: mínimo 2 cores
   - Disk: mínimo 20GB

2. Limpar cache do Docker:
   ```bash
   docker system prune -a --volumes
   ```

3. Usar volumes para cache:
   ```bash
   # Já configurado no docker-compose.yml
   composer_cache:/home/vscode/.cache/composer
   npm_cache:/home/vscode/.cache/npm
   ```

### ❌ Hot reload não funciona
**✅ Soluções:**
1. Verificar se Vite está rodando na porta correta:
   ```bash
   npm run dev -- --host 0.0.0.0 --port 3000
   ```

2. Limpar cache do Vite:
   ```bash
   rm -rf node_modules/.vite
   npm run dev
   ```

## 🐘 Problemas do Laravel

### ❌ Erro: Application key not set
**✅ Solução:**
```bash
# Gerar chave
php artisan key:generate

# Verificar .env
cat .env | grep APP_KEY
```

### ❌ Erro: Permission denied storage/logs
**✅ Solução:**
```bash
# Corrigir permissões
sudo chown -R vscode:vscode storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

### ❌ Migrations failing
**✅ Soluções:**
1. Verificar conexão com BD:
   ```bash
   php artisan migrate:status
   ```

2. Reset migrations:
   ```bash
   php artisan migrate:fresh --seed
   ```

3. Verificar configuração .env:
   ```bash
   php artisan config:clear
   php artisan config:cache
   ```

## ⚛️ Problemas do React

### ❌ Erro: Module not found
**✅ Soluções:**
1. Reinstalar dependências:
   ```bash
   rm -rf node_modules package-lock.json
   npm install
   ```

2. Limpar cache npm:
   ```bash
   npm cache clean --force
   ```

### ❌ Build falha
**✅ Soluções:**
1. Verificar TypeScript:
   ```bash
   npx tsc --noEmit
   ```

2. Verificar ESLint:
   ```bash
   npm run lint
   ```

3. Build com debug:
   ```bash
   npm run build -- --mode development
   ```

## 🔐 Problemas de Permissões

### ❌ Erro: Permission denied ao executar scripts
**✅ Solução:**
```bash
# Tornar scripts executáveis
chmod +x scripts/*.sh

# Ou executar com bash
bash scripts/setup.sh
```

### ❌ Erro: Cannot write to /workspace
**✅ Solução:**
```bash
# Corrigir ownership
sudo chown -R vscode:vscode /workspace

# Verificar permissões
ls -la /workspace
```

## 🌐 Problemas de Rede

### ❌ Porta já em uso
**✅ Soluções:**
1. Verificar processos:
   ```bash
   sudo lsof -i :8000
   sudo lsof -i :3000
   ```

2. Matar processos:
   ```bash
   sudo kill -9 PID
   ```

3. Usar portas alternativas:
   ```bash
   php artisan serve --port=8001
   npm run dev -- --port 3001
   ```

## 🔄 Reset Completo

### 🆘 Quando nada funciona
```bash
# 1. Parar todos containers
docker-compose down -v

# 2. Limpar sistema Docker
docker system prune -a --volumes

# 3. Rebuild completo
docker-compose build --no-cache

# 4. Iniciar novamente
docker-compose up -d

# 5. Executar setup
bash scripts/setup.sh
```

## 📞 Suporte Adicional

### Logs úteis para debug:
1. **Container logs:**
   ```bash
   docker-compose logs devcontainer
   ```

2. **MySQL logs:**
   ```bash
   docker-compose logs mysql
   ```

3. **Redis logs:**
   ```bash
   docker-compose logs redis
   ```

### Informações do sistema:
```bash
# Versões
php --version
node --version
npm --version
composer --version

# Extensões PHP
php -m

# Status dos serviços
docker-compose ps
```

### Comandos de verificação:
```bash
# Health check completo
php artisan about
composer validate
npm audit

# Conectividade
ping mysql
ping redis
```

---

**💡 Dica:** Sempre consulte os logs primeiro com `docker-compose logs <service>` para identificar a causa raiz do problema.

**🔄 Lembre-se:** Após qualquer mudança significativa, execute `docker-compose down && docker-compose up -d` para garantir que tudo está funcionando corretamente. 