# 🔧 Troubleshooting - Sistema Rei do Óleo

Este documento contém soluções para problemas comuns encontrados durante o desenvolvimento.

## 🚨 Problemas Críticos

### Erro: "Unable to detect application namespace"

**Sintomas:**
```
In Application.php line 1717:
  Unable to detect application namespace.
```

**Causa:** O Laravel não consegue detectar o namespace da aplicação devido a problemas na estrutura do projeto.

**Solução:**
```bash
# Executar correção automática
npm run fix:laravel

# Ou manualmente
bash .devcontainer/scripts/fix-laravel-namespace.sh
```

**Verificação:**
```bash
# Verificar se o problema foi resolvido
npm run verify:laravel

# Ou manualmente
bash .devcontainer/scripts/verify-laravel.sh
```

### Erro: "Command failed: /bin/sh -c bash /workspace/.devcontainer/scripts/setup.sh"

**Sintomas:** O devcontainer falha durante a inicialização.

**Solução:**
```bash
# Executar troubleshooting completo
npm run troubleshoot

# Ou manualmente
bash .devcontainer/scripts/troubleshoot.sh
```

## 🔍 Diagnóstico Automático

### Script de Troubleshooting Completo

Execute o script de troubleshooting para diagnóstico automático:

```bash
npm run troubleshoot
```

Este script irá:
- ✅ Verificar serviços (MySQL, Redis)
- ✅ Verificar permissões
- ✅ Verificar estrutura do projeto
- ✅ Verificar dependências
- ✅ Verificar Laravel especificamente
- ✅ Verificar banco de dados
- ✅ Verificar frontend
- ✅ Limpar caches
- ✅ Executar testes finais

### Scripts Específicos

#### Verificar Laravel
```bash
npm run verify:laravel
```

#### Corrigir Namespace do Laravel
```bash
npm run fix:laravel
```

#### Configurar Banco de Teste
```bash
npm run test:db:setup
```

## 🛠️ Problemas Comuns

### 1. Problemas de Permissão

**Sintomas:** Erros de permissão ao executar comandos.

**Solução:**
```bash
# Corrigir permissões do workspace
sudo chown -R vscode:vscode /workspace
chmod -R u+rw /workspace

# Corrigir permissões específicas
chmod +x .devcontainer/scripts/*.sh
```

### 2. Problemas de Dependências

**Sintomas:** Erros ao instalar ou executar dependências.

**Solução Backend:**
```bash
cd backend
rm -rf vendor composer.lock
composer install --no-interaction --prefer-dist
composer dump-autoload --optimize
```

**Solução Frontend:**
```bash
cd frontend
rm -rf node_modules package-lock.json
npm install
```

### 3. Problemas de Banco de Dados

**Sintomas:** Erros de conexão com MySQL.

**Solução:**
```bash
# Verificar se MySQL está rodando
docker-compose ps mysql

# Reiniciar MySQL
docker-compose restart mysql

# Aguardar MySQL estar pronto
until mysqladmin ping -h mysql -u root -proot123 --silent; do
    echo "Aguardando MySQL..."
    sleep 2
done
```

### 4. Problemas de Cache

**Sintomas:** Comportamento inesperado, erros de configuração.

**Solução:**
```bash
# Limpar caches do Laravel
cd backend
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Limpar cache do Composer
composer dump-autoload --optimize

# Limpar cache do NPM
cd ../frontend
npm cache clean --force
```

### 5. Problemas de Build

**Sintomas:** Erros ao fazer build do frontend.

**Solução:**
```bash
cd frontend

# Verificar tipos TypeScript
npm run type-check

# Verificar linting
npm run lint

# Tentar build
npm run build

# Se falhar, limpar e reinstalar
rm -rf node_modules package-lock.json
npm install
npm run build
```

## 🔧 Comandos Úteis

### Verificação de Status

```bash
# Status dos containers
docker-compose ps

# Logs dos serviços
docker-compose logs [service]

# Status do Laravel
cd backend && php artisan --version

# Status do banco
cd backend && php artisan migrate:status
```

### Reinicialização

```bash
# Reiniciar todos os serviços
docker-compose restart

# Reiniciar serviço específico
docker-compose restart [service]

# Reconstruir containers
docker-compose down
docker-compose up --build
```

### Limpeza

```bash
# Limpar tudo
docker-compose down -v
rm -rf backend/vendor frontend/node_modules
npm run setup
```

## 📋 Checklist de Troubleshooting

### Antes de Reportar um Problema

- [ ] Execute `npm run troubleshoot`
- [ ] Verifique os logs: `docker-compose logs [service]`
- [ ] Teste com containers limpos: `docker-compose down && docker-compose up --build`
- [ ] Verifique se o problema é reproduzível
- [ ] Consulte este documento

### Informações para Reportar

Ao reportar um problema, inclua:

1. **Comando executado**
2. **Erro completo**
3. **Saída do troubleshooting:**
   ```bash
   npm run troubleshoot > troubleshoot.log 2>&1
   ```
4. **Versões:**
   ```bash
   docker --version
   docker-compose --version
   node --version
   npm --version
   php --version
   composer --version
   ```
5. **Sistema operacional**
6. **Passos para reproduzir**

## 🆘 Suporte

Se os problemas persistirem:

1. **Verifique os issues existentes** no GitHub
2. **Crie um novo issue** com as informações solicitadas acima
3. **Use as tags apropriadas** para categorizar o problema

## 📚 Recursos Adicionais

- [Documentação do Laravel](https://laravel.com/docs)
- [Documentação do React](https://react.dev)
- [Documentação do Docker](https://docs.docker.com)
- [Documentação do DevContainer](https://containers.dev) 