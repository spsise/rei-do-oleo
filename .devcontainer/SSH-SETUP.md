# 🔐 Configuração SSH - DevContainer

## ✅ Solução Implementada

Este DevContainer foi configurado para **reutilizar automaticamente as chaves SSH do seu host**, resolvendo problemas de permissão do Git.

## 🔧 Como Funciona

### 1. Mapeamento Automático

O arquivo `devcontainer.json` está configurado para:

```json
{
  "mounts": [
    "source=${env:HOME}/.ssh,target=/home/vscode/.ssh,type=bind,readonly"
  ],
  "remoteEnv": {
    "GIT_SSH_COMMAND": "ssh -i /home/vscode/.ssh/id_rsa -o StrictHostKeyChecking=no"
  }
}
```

### 2. Setup Automático

O script `ssh-setup.sh` executa automaticamente e:

- ✅ Detecta chaves SSH disponíveis (`id_rsa`, `id_ed25519`, `id_ecdsa`)
- ✅ Configura permissões corretas (700 para .ssh, 600 para chaves)
- ✅ Define `GIT_SSH_COMMAND` automaticamente
- ✅ Testa conexão com GitHub
- ✅ Configura Git para usar SSH por padrão

## 🚀 Como Usar

### Primeira Vez

1. **Certifique-se que você tem chaves SSH no host:**

   ```bash
   ls ~/.ssh/
   # Deve mostrar: id_rsa, id_rsa.pub (ou id_ed25519, etc.)
   ```

2. **Se não tiver chaves, gere uma:**

   ```bash
   ssh-keygen -t ed25519 -C "seu_email@example.com"
   ```

3. **Adicione a chave ao GitHub:**

   ```bash
   cat ~/.ssh/id_ed25519.pub
   # Cole o conteúdo em GitHub > Settings > SSH and GPG keys
   ```

4. **Reconstrua o DevContainer:**
   - No VS Code: `F1` → `Dev Containers: Rebuild Container`

### Após Setup

O Git funcionará automaticamente com SSH:

```bash
# Isso funcionará automaticamente:
git clone git@github.com:usuario/repo.git
git push origin main
```

## 🔍 Verificação

### Testar SSH

```bash
ssh -T git@github.com
# Deve retornar: Hi username! You've successfully authenticated...
```

### Verificar Configuração

```bash
echo $GIT_SSH_COMMAND
# Deve mostrar: ssh -i /home/vscode/.ssh/id_ed25519 -o StrictHostKeyChecking=no

git config --list | grep url
# Deve mostrar: url.git@github.com:.insteadof=https://github.com/
```

## 🐛 Troubleshooting

### Problema: "Permission denied (publickey)"

**Solução:**

1. Verifique se a chave está no GitHub
2. Execute: `ssh-add ~/.ssh/id_ed25519`
3. Teste: `ssh -T git@github.com`

### Problema: "No such file or directory: ~/.ssh"

**Solução:**

1. Crie chaves SSH no host:
   ```bash
   ssh-keygen -t ed25519 -C "seu_email@example.com"
   ```
2. Reconstrua o container

### Problema: Git ainda usa HTTPS

**Solução:**

```bash
git remote set-url origin git@github.com:usuario/repo.git
```

### Forçar Re-configuração

```bash
bash /workspace/.devcontainer/scripts/ssh-setup.sh
```

## 📝 Arquivos Modificados

- `.devcontainer/devcontainer.json` - Mapeamento SSH
- `.devcontainer/scripts/ssh-setup.sh` - Script de configuração
- `.devcontainer/scripts/setup.sh` - Integração no setup

## 🔒 Segurança

- ✅ Chaves SSH são montadas como **readonly**
- ✅ Permissões corretas são aplicadas automaticamente
- ✅ `StrictHostKeyChecking=no` apenas para GitHub
- ✅ Chaves nunca saem do container

## ✨ Vantagens

- 🚀 **Automático**: Funciona sem configuração manual
- 🔒 **Seguro**: Reutiliza chaves do host
- 🛠️ **Inteligente**: Detecta diferentes tipos de chaves
- 📱 **Universal**: Funciona em Windows, macOS, Linux

---

> **Nota**: Após reconstruir o container, o Git funcionará automaticamente com SSH. Não é necessário configurar credenciais manualmente.
