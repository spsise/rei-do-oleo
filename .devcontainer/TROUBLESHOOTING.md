# 🔧 Troubleshooting - Dev Container

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