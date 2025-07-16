# 🐳 Guia Completo - Build do DevContainer

## 🎯 Objetivo
Este guia te ajuda a buildar o DevContainer manualmente e acompanhar todo o processo para identificar onde está travando.

## 📋 Pré-requisitos

### 1. **Limpar Docker (OBRIGATÓRIO)**
```bash
# Parar todos os containers
docker compose down --remove-orphans

# Limpar containers antigos
docker stop $(docker ps -q --filter "name=rei-do-oleo") 2>/dev/null || true

# Limpar cache (opcional, mas recomendado)
docker system prune -af
docker volume prune -f
```

### 2. **Verificar Recursos**
```bash
# Verificar espaço em disco
df -h

# Verificar memória disponível
free -h

# Verificar Docker
docker --version
docker compose version
```

## 🔨 Métodos de Build

### **Método 1: Build Manual com Logs Detalhados**
```bash
cd .devcontainer

# Build com logs completos
docker compose build --no-cache --progress=plain devcontainer
```

### **Método 2: Build por Etapas**
```bash
cd .devcontainer

# 1. Build apenas do Dockerfile
docker build -f Dockerfile -t devcontainer-devcontainer . --no-cache --progress=plain

# 2. Build do compose
docker compose build --no-cache devcontainer
```

### **Método 3: Script Automatizado**
```bash
cd .devcontainer

# Executar script de build
./scripts/build-devcontainer.sh full
```

## 📊 Monitoramento do Build

### **Comandos para Acompanhar**

```bash
# 1. Ver logs em tempo real
docker compose logs -f devcontainer

# 2. Ver status dos containers
docker compose ps

# 3. Ver uso de recursos
docker stats

# 4. Ver imagens sendo criadas
docker images | grep devcontainer
```

### **Pontos de Verificação**

| Etapa | Tempo Esperado | Comando de Verificação |
|-------|----------------|------------------------|
| **1. Base Ubuntu** | 2-3 min | `docker images | grep ubuntu` |
| **2. Dependências Sistema** | 3-5 min | `docker ps -a | grep devcontainer` |
| **3. PHP 8.1** | 2-3 min | `docker exec -it <container> php -v` |
| **4. Node.js 20** | 1-2 min | `docker exec -it <container> node -v` |
| **5. Composer** | 30s | `docker exec -it <container> composer -V` |
| **6. Oh My Zsh** | 1 min | `docker exec -it <container> zsh --version` |

## 🚨 Problemas Comuns e Soluções

### **1. Erro de Locale/PPA**
```
softwareproperties.shortcuthandler.ShortcutException
```
**✅ Solução:** Já corrigido no Dockerfile atual (PHP 8.1 oficial)

### **2. Erro de Memória**
```
failed to solve: process did not complete successfully: exit code 137
```
**✅ Soluções:**
- Aumentar memória do Docker (mínimo 4GB)
- Limpar cache: `docker system prune -af`
- Fechar outros programas

### **3. Erro de Disco**
```
no space left on device
```
**✅ Soluções:**
- Limpar imagens: `docker image prune -af`
- Verificar espaço: `df -h`
- Limpar volumes: `docker volume prune -f`

### **4. Erro de Rede**
```
failed to fetch
```
**✅ Soluções:**
- Verificar conexão com internet
- Usar VPN se necessário
- Tentar novamente

### **5. Build Travando**
**✅ Soluções:**
- Cancelar build: `Ctrl+C`
- Verificar logs: `docker compose logs devcontainer`
- Rebuild: `docker compose build --no-cache devcontainer`

## 🔍 Diagnóstico Avançado

### **Script de Troubleshooting**
```bash
# Executar diagnóstico completo
.devcontainer/scripts/troubleshoot.sh
```

### **Verificar Logs Detalhados**
```bash
# Ver logs do build
docker compose logs devcontainer

# Ver logs de todos os serviços
docker compose logs

# Ver logs de um serviço específico
docker compose logs mysql
docker compose logs redis
```

### **Verificar Configuração**
```bash
# Verificar docker-compose.yml
docker compose config

# Verificar Dockerfile
docker build --dry-run -f Dockerfile .
```

## 🚀 Após o Build

### **1. Testar Container**
```bash
# Iniciar serviços
docker compose up -d

# Verificar status
docker compose ps

# Testar conectividade
docker compose exec devcontainer bash
```

### **2. Abrir no VSCode**
```bash
# No VSCode:
# 1. Ctrl+Shift+P
# 2. "Dev Containers: Open Folder in Container"
# 3. Selecionar pasta do projeto
```

### **3. Verificar Funcionamento**
```bash
# Dentro do container:
cd backend && php artisan serve
cd frontend && npm run dev
```

## 📞 Suporte

### **Se o Build Falhar:**

1. **Copiar logs completos**
2. **Executar diagnóstico:**
   ```bash
   .devcontainer/scripts/troubleshoot.sh
   ```
3. **Verificar recursos do sistema**
4. **Tentar build por etapas**

### **Comandos de Emergência**
```bash
# Parar tudo
docker compose down --remove-orphans

# Limpar tudo
docker system prune -af --volumes

# Rebuild do zero
docker compose build --no-cache --progress=plain devcontainer
```

---

## 🎯 Resumo Rápido

```bash
# 1. Limpar
docker compose down --remove-orphans
docker system prune -af

# 2. Buildar
cd .devcontainer
docker compose build --no-cache --progress=plain devcontainer

# 3. Testar
docker compose up -d
docker compose ps

# 4. Abrir no VSCode
# Ctrl+Shift+P > "Dev Containers: Open Folder in Container"
```

**⏱️ Tempo esperado: 5-10 minutos**
**📊 Logs: Visíveis em tempo real**
**🔧 Controle: Total sobre o processo** 