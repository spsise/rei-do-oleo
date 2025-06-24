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
| Laravel API | 8100 | http://localhost:8100 | - |
| React App | 3100 | http://localhost:3100 | - |
| MySQL | 3310 | localhost:3310 | `rei_do_oleo` / `secret123` |
| phpMyAdmin | 8110 | http://localhost:8110 | `root` / `root123` |
| Redis | 6400 | localhost:6400 | - |
| Redis Commander | 6410 | http://localhost:6410 | `admin` / `secret123` |
| MailHog | 8030 | http://localhost:8030 | - |

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

## ✅ Problema Resolvido - Conflito de Portas

O conflito de portas foi resolvido! As portas foram ajustadas para evitar conflitos com outros projetos.

## 🔌 Portas Atualizadas

### 🛠️ Serviços de Desenvolvimento
- **Laravel API**: http://localhost:8100 (antes: 8000)
- **React Frontend**: http://localhost:3100 (antes: 3000) 
- **Vite Dev Server**: http://localhost:5200 (antes: 5173)

### 🗄️ Banco de Dados
- **MySQL**: localhost:3310 (antes: 3309)
  - Database: `rei_do_oleo_dev`
  - User: `rei_do_oleo` / Password: `secret123`
  - Root Password: `root123`

### 📦 Cache & Sessions
- **Redis**: localhost:6400 (antes: 6379)

### 🌐 Ferramentas Web
- **phpMyAdmin**: http://localhost:8110 (antes: 8081)
- **Redis Commander**: http://localhost:6410 (antes: 6380)
  - User: `admin` / Password: `secret123`

### 📧 Email Testing
- **MailHog Web**: http://localhost:8030 (antes: 8025)
- **MailHog SMTP**: localhost:1030 (antes: 1025)

## 🚀 Como Iniciar o Dev Container

1. **Feche o VSCode** se estiver aberto
2. **Reabra o projeto**: `code .`
3. **Clique em "Reopen in Container"** quando solicitado
4. **Aguarde a inicialização** (primeira vez pode demorar ~5-10 minutos)

## ⚠️ Se Ainda Houver Problemas

Se ainda houver conflitos, execute:

```bash
# 1. Pare todos os containers relacionados
docker stop $(docker ps -q --filter "name=rei-do-oleo") 2>/dev/null || true

# 2. Remova containers antigos
docker rm $(docker ps -aq --filter "name=rei-do-oleo") 2>/dev/null || true

# 3. Remova volumes se necessário
docker volume rm $(docker volume ls -q --filter "name=rei-do-oleo") 2>/dev/null || true

# 4. Reabra no VSCode
code .
```

## 📋 Checklist de Verificação

Após inicializar o dev container, verifique:

- [ ] ✅ MySQL conectando em localhost:3310
- [ ] ✅ Redis respondendo em localhost:6400  
- [ ] ✅ phpMyAdmin acessível em http://localhost:8110
- [ ] ✅ Redis Commander em http://localhost:6410
- [ ] ✅ MailHog em http://localhost:8030
- [ ] ✅ Laravel servindo em http://localhost:8100
- [ ] ✅ React servindo em http://localhost:3100

## 🛠️ Configurações de Conexão

### Laravel (.env)
```env
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=rei_do_oleo_dev
DB_USERNAME=rei_do_oleo
DB_PASSWORD=secret123

REDIS_HOST=redis
REDIS_PORT=6379

MAIL_HOST=mailhog
MAIL_PORT=1025
```

### Cliente MySQL Externo
```bash
mysql -h localhost -P 3310 -u rei_do_oleo -psecret123 rei_do_oleo_dev
```

### Redis CLI Externo
```bash
redis-cli -h localhost -p 6400
```

## 📝 Notas Importantes

- **Portas internas** dos containers permanecem as mesmas (3306, 6379, etc.)
- **Apenas as portas expostas** foram alteradas para evitar conflitos
- **Configurações da aplicação** (.env) não precisam mudar
- **Acesso externo** agora usa as novas portas

---
*Configuração atualizada em $(date) - Conflito de portas resolvido* ✨ 