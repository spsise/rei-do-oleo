# 🔗 Configuração do Webhook no GitHub

## 📋 Pré-requisitos

1. ✅ Primeiro deploy realizado
2. ✅ Rota do webhook configurada no Laravel
3. ✅ API funcionando em `https://api-hom.virtualt.com.br`

## 🎯 Passo a Passo

### 1. Acessar Configurações do Repositório

1. Vá para o repositório: https://github.com/spsise/rei-do-oleo
2. Clique em **Settings** (Configurações)
3. No menu lateral, clique em **Webhooks**

### 2. Adicionar Novo Webhook

1. Clique em **Add webhook**
2. Preencha os campos:

#### **Payload URL**

```
https://api-hom.virtualt.com.br/api/webhook/deploy
```

#### **Content type**

```
application/json
```

#### **Secret** (Opcional, mas recomendado)

```
rei-do-oleo-deploy-secret-2024
```

#### **Which events would you like to trigger this webhook?**

- ✅ **Just the push event**
- ❌ ~~Send me everything~~

#### **Branch Filter**

- ✅ **Branch: hostinger-hom**

### 3. Configurações Avançadas

#### **SSL verification**

- ✅ **Enable SSL verification**

#### **Active**

- ✅ **Active**

### 4. Salvar e Testar

1. Clique em **Add webhook**
2. Clique no webhook criado
3. Role para baixo e clique em **Recent Deliveries**
4. Clique em **Redeliver** para testar

## 🔍 Verificação

### Teste Manual do Webhook

```bash
# No servidor, verificar logs
cd /home/seu-usuario/domains/virtualt.com.br/public_html/api-hom
tail -f storage/logs/laravel.log
```

### Teste via cURL

```bash
# Simular webhook
curl -X POST https://api-hom.virtualt.com.br/webhook/deploy \
  -H "Content-Type: application/json" \
  -d '{"ref":"refs/heads/hostinger-hom"}'
```

## 🚨 Troubleshooting

### Problema: Webhook não responde

**Solução:**

```bash
# Verificar se a API está funcionando
curl -I https://api-hom.virtualt.com.br

# Verificar logs do Laravel
tail -f storage/logs/laravel.log
```

### Problema: Erro 404 na rota

**Solução:**

```bash
# Verificar se a rota foi registrada
php artisan route:list | grep webhook

# Limpar cache das rotas
php artisan route:clear
php artisan route:cache
```

### Problema: Permissões negadas

**Solução:**

```bash
# Verificar permissões do script de deploy
chmod +x /home/seu-usuario/rei-do-oleo/deploy.sh

# Verificar se o usuário pode executar
ls -la /home/seu-usuario/rei-do-oleo/deploy.sh
```

## ✅ Checklist Final

- [ ] Webhook criado no GitHub
- [ ] URL correta: `https://api-hom.virtualt.com.br/webhook/deploy`
- [ ] Branch filtrada: `hostinger-hom`
- [ ] Evento: `push`
- [ ] Teste de redelivery funcionando
- [ ] Logs mostrando execução do deploy

## 🎉 Teste Final

1. Faça uma alteração no código
2. Commit e push para branch `hostinger-hom`
3. Verifique se o deploy acontece automaticamente
4. Confirme que os subdomínios foram atualizados

```bash
# Verificar deploy automático
curl -I https://api-hom.virtualt.com.br
curl -I https://app-hom.virtualt.com.br
```
