# 🐧 Rei do Óleo - Setup WSL2 + Ubuntu

## ✅ Seu Ambiente Atual

Baseado na verificação, você já tem:
- ✅ Docker 27.3.1 instalado e funcionando
- ✅ Usuário no grupo `docker` 
- ✅ WSL2 com Ubuntu configurado

## 🚀 Passos para Iniciar o Dev Container

### 1. Verificar Recursos WSL2

Primeiro, vamos verificar se você tem recursos suficientes:

```bash
# Verificar memória disponível
free -h

# Verificar espaço em disco
df -h
```

**Recursos recomendados:**
- RAM: 8GB+ disponível
- Disco: 10GB+ livres
- CPU: 4+ cores

### 2. Limpar Containers Antigos (Opcional)

Se você quiser começar limpo:

```bash
# Parar containers que não estão sendo usados
docker stop $(docker ps -q) 2>/dev/null || true

# Limpar containers antigos (cuidado com outros projetos!)
docker system prune -f

# Verificar espaço liberado
docker system df
```

### 3. Iniciar o Dev Container

```bash
# 1. Abrir o projeto no VSCode
code .

# 2. No VSCode, pressionar Ctrl+Shift+P
# 3. Digitar: "Dev Containers: Reopen in Container"
# 4. Aguardar construção (~10-15 minutos na primeira vez)
```

### 4. Verificar se Funcionou

Após o container iniciar, execute no terminal integrado do VSCode:

```bash
# Verificar PHP
php --version

# Verificar Composer
composer --version

# Verificar Node.js
node --version

# Verificar conexão MySQL
mysql -h mysql -u rei_do_oleo -psecret123 -e "SHOW DATABASES;"

# Verificar Redis
redis-cli -h redis ping
```

## 🔧 Comandos de Desenvolvimento

Após o devcontainer estar rodando:

```bash
# Navegar para o backend
cd /workspace/backend

# Instalar dependências PHP (se necessário)
composer install

# Configurar Laravel
cp .env.example .env
php artisan key:generate

# Executar migrations
php artisan migrate

# Iniciar servidor Laravel
php artisan serve --host=0.0.0.0 --port=8000
```

## 🌐 URLs Disponíveis

Após tudo configurado:

- **Laravel API:** http://localhost:8000
- **phpMyAdmin:** http://localhost:8081 (root/root123)
- **Redis Commander:** http://localhost:6380 (admin/secret123)
- **MailHog:** http://localhost:8025

## ⚠️ Troubleshooting WSL2

### Problema: Container não inicia

```bash
# Verificar Docker está rodando
sudo systemctl status docker

# Se não estiver, iniciar
sudo systemctl start docker

# Verificar logs do Docker
sudo journalctl -u docker.service -f
```

### Problema: Memória insuficiente

Criar/editar `~/.wslconfig` no Windows:

```ini
[wsl2]
memory=8GB
processors=4
swap=2GB
localhostForwarding=true
```

Depois reiniciar WSL2:
```powershell
# No PowerShell do Windows
wsl --shutdown
wsl
```

### Problema: Portas ocupadas

```bash
# Verificar quais portas estão sendo usadas
netstat -tulpn | grep -E ':(3306|6379|8000|8080)'

# Parar containers conflitantes se necessário
docker stop $(docker ps -q --filter "expose=3306")
```

### Problema: Performance lenta

```bash
# Verificar recursos do sistema
htop

# Verificar uso do Docker
docker stats

# Limpar cache do Docker
docker builder prune -f
```

## 🎯 Próximos Passos

1. **Testar o devcontainer** seguindo os passos acima
2. **Reportar qualquer erro** específico que aparecer
3. **Configurar o projeto Laravel** dentro do container
4. **Começar o desenvolvimento!**

## 📞 Se Precisar de Ajuda

Se algo não funcionar, execute e me envie a saída:

```bash
# Informações do sistema
echo "=== SISTEMA ==="
uname -a
free -h
df -h

echo "=== DOCKER ==="
docker --version
docker ps
docker images | head -10

echo "=== DEVCONTAINER ==="
ls -la .devcontainer/
``` 