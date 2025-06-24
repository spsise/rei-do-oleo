# 🛠️ Rei do Óleo - Dev Container

## 🚀 Como Usar

### Pré-requisitos
- **WSL2 + Ubuntu:** Docker instalado nativamente no WSL2
- **Windows:** Docker Desktop com WSL2 backend habilitado
- **Linux:** Docker Engine instalado
- VSCode com extensão Remote-Containers (Dev Containers)

### Iniciando o Dev Container

#### **No WSL2 (Ubuntu):**
1. **Verificar Docker está rodando:**
   ```bash
   sudo systemctl status docker
   # Se não estiver rodando:
   sudo systemctl start docker
   ```

2. **Abrir projeto no VSCode:**
   ```bash
   code .
   ```

3. **Abrir no Dev Container:**
   - Pressione `Ctrl+Shift+P`
   - Digite: `Dev Containers: Reopen in Container`
   - Aguarde a construção (primeira vez pode demorar ~10-15 minutos)

#### **No Windows com Docker Desktop:**
1. **Verificar Docker Desktop está rodando**
2. **Abrir no VSCode e seguir passos 2-3 acima**

### ⚙️ Configuração WSL2 (Recomendado)

Se você está usando WSL2 com Ubuntu, configure recursos adequados:

**Criar/editar `~/.wslconfig` no Windows:**
```ini
[wsl2]
memory=8GB
processors=4
swap=2GB
```

**Reiniciar WSL2 após alterações:**
```powershell
# No PowerShell (Windows)
wsl --shutdown
wsl
```

**Verificar se Docker está no grupo do usuário:**
```bash
# No Ubuntu (WSL2)
sudo usermod -aG docker $USER
# Fazer logout e login novamente
```

### 🔧 Serviços Disponíveis

| Serviço | Porta | URL | Credenciais |
|---------|-------|-----|------------|
| Laravel API | 8000 | http://localhost:8000 | - |
| React App | 3000 | http://localhost:3000 | - |
| MySQL | 3309 | localhost:3309 | `rei_do_oleo` / `secret123` |
| phpMyAdmin | 8081 | http://localhost:8081 | `root` / `root123` |
| Redis | 6379 | localhost:6379 | - |
| Redis Commander | 6380 | http://localhost:6380 | `admin` / `secret123` |
| MailHog | 8025 | http://localhost:8025 | - |

### 🚀 Comandos Úteis

```bash
# Laravel
art serve --host=0.0.0.0    # Iniciar servidor
art migrate:fresh --seed    # Reset database
art tinker                  # Console interativo

# React
npm run dev                 # Iniciar desenvolvimento
npm test                   # Executar testes
npm run build              # Build para produção

# Database
mysql-cli                  # Conectar ao MySQL
redis-cli                  # Conectar ao Redis
```

### 🛠️ Desenvolvimento

O container está configurado com:
- ✅ PHP 8.2 + extensões essenciais
- ✅ Composer + Laravel
- ✅ Node.js 18 + npm/yarn
- ✅ MySQL 8.0 + phpMyAdmin
- ✅ Redis 7 + Commander
- ✅ Xdebug configurado
- ✅ Aliases úteis no Zsh

### 🔍 Troubleshooting

**Container não inicia (WSL2):**
1. Verificar Docker está rodando: `sudo systemctl status docker`
2. Iniciar Docker se necessário: `sudo systemctl start docker`
3. Verificar permissões: `sudo usermod -aG docker $USER` (relogar após)
4. Limpar containers antigos: `docker system prune -a`
5. Reconstruir: `Ctrl+Shift+P` → `Dev Containers: Rebuild Container`

**Container não inicia (Docker Desktop):**
1. Verificar se Docker Desktop está rodando
2. Verificar WSL2 backend está habilitado
3. Seguir passos 4-5 acima

**MySQL não conecta:**
1. Aguardar inicialização completa (~60s)
2. Verificar logs: `docker logs devcontainer-mysql-1`
3. Verificar se o serviço subiu: `docker ps | grep mysql`

**Performance lenta:**
1. **WSL2:** Aumentar recursos no `.wslconfig` (8GB+ RAM)
2. **Docker Desktop:** Aumentar recursos nas configurações
3. Fechar outras aplicações pesadas
4. Usar SSD se possível

### 📊 Status dos Serviços

Verifique se todos os serviços estão rodando:
```bash
docker ps
```

### 🔄 Resetar Ambiente

Para resetar completamente:
```bash
# Parar containers
docker-compose down

# Limpar volumes
docker volume prune

# Reconstruir
docker-compose up --build
``` 