# 🤫 Comandos Ocultos de Voz - Telegram Bot

## 📋 Visão Geral

Este documento descreve os comandos ocultos para gerenciar funcionalidades de voz no bot do Telegram. Estes comandos **não aparecem no menu** e são destinados apenas para uso interno e técnico.

## 🚨 Importante

- **Estes comandos são OCULTOS** e não aparecem no menu principal
- **Apenas usuários autorizados** podem usar estes comandos
- **Não divulgue** estes comandos para usuários finais
- **Use apenas** para testes e configuração interna

## 🎤 Comandos Disponíveis

### 1. Testar Serviço de Voz

**Comandos:**

- `/testvoice`
- `/test_voice`
- `/voice_test`
- `/testvoz`
- `/test_voz`
- `/voz_test`

**Função:** Testa se o serviço de Speech-to-Text está funcionando corretamente.

**Resposta:**

```
🧪 Testando serviço de voz...

✅ Teste de Voz - SUCESSO

🎤 Provider: vosk
📝 Resultado do teste: [texto reconhecido]
🔧 Status: Funcionando corretamente

💡 Como usar: Envie uma mensagem de voz para testar o reconhecimento.
```

### 2. Ativar Serviço de Voz

**Comandos:**

- `/enablevoice`
- `/enable_voice`
- `/voice_enable`
- `/ativarvoz`
- `/ativar_voz`
- `/voz_ativar`

**Função:** Verifica e ativa os providers de voz disponíveis.

**Resposta:**

```
🔧 Ativando serviço de voz...

✅ Serviço de voz ativado

🎤 Providers ativos:
✅ Vosk (Offline - Free)
✅ OpenAI Whisper (Online - Paid)

💡 Como usar: Envie uma mensagem de voz para testar o reconhecimento.
```

### 3. Status do Serviço de Voz

**Comandos:**

- `/voice_status`
- `/status_voz`
- `/voz_status`

**Função:** Mostra o status detalhado de todos os providers de voz.

**Resposta:**

```
📊 Status do Serviço de Voz

✅ Vosk (Offline - Free)
   📋 Tipo: offline
   💰 Custo: free
   🎯 Precisão: 90%
   ⚡ Velocidade: medium
   🔧 Status: Ativo

❌ OpenAI Whisper (Online - Paid)
   📋 Tipo: online
   💰 Custo: paid
   🎯 Precisão: 95%
   ⚡ Velocidade: fast
   🔧 Status: Inativo

💡 Comandos ocultos disponíveis:
• /testvoice - Testar serviço de voz
• /enablevoice - Ativar serviço de voz
• /voice_status - Ver este status
```

## 🔧 Como Usar

### 1. Teste Inicial

```bash
# Teste se o serviço está funcionando
/testvoice
```

### 2. Verificar Status

```bash
# Veja o status de todos os providers
/voice_status
```

### 3. Ativar se Necessário

```bash
# Ative o serviço se não estiver funcionando
/enablevoice
```

### 4. Teste com Voz Real

Após confirmar que está funcionando, envie uma mensagem de voz para testar o reconhecimento.

## 🛠️ Configuração

### Providers Suportados

1. **Vosk (Offline - Free)**

   - ✅ Configurado por padrão
   - 🎯 Precisão: 90%
   - ⚡ Velocidade: Média

2. **OpenAI Whisper (Online - Paid)**

   - ❌ Requer API key
   - 🎯 Precisão: 95%
   - ⚡ Velocidade: Rápida

3. **Google Speech-to-Text (Online - Paid)**

   - ❌ Requer API key
   - 🎯 Precisão: 94%
   - ⚡ Velocidade: Rápida

4. **Azure Speech Services (Online - Paid)**
   - ❌ Requer API key
   - 🎯 Precisão: 93%
   - ⚡ Velocidade: Rápida

## 🔍 Troubleshooting

### Problema: "Teste de Voz - FALHOU"

**Possíveis causas:**

1. Provider não configurado
2. Modelo não baixado
3. Dependências não instaladas

**Soluções:**

1. Execute `/voice_status` para ver o status
2. Execute `/enablevoice` para tentar ativar
3. Verifique as configurações no sistema

### Problema: "Nenhum provider configurado"

**Soluções:**

1. Configure pelo menos um provider no sistema
2. Para Vosk: Execute o comando de setup
3. Para outros: Configure as API keys

## 📝 Logs

Os comandos ocultos geram logs detalhados:

```php
Log::info('Voice command handler called', [
    'chat_id' => $chatId,
    'command' => $command,
    'params' => $params
]);
```

## 🔒 Segurança

- **Autenticação:** Apenas usuários autorizados podem usar
- **Logs:** Todas as ações são registradas
- **Oculto:** Comandos não aparecem no menu público
- **Interno:** Destinado apenas para uso técnico

## 🎯 Casos de Uso

1. **Teste Inicial:** Verificar se o sistema está funcionando
2. **Diagnóstico:** Identificar problemas com providers
3. **Configuração:** Ativar providers necessários
4. **Manutenção:** Verificar status durante manutenção

## 📞 Suporte

Para problemas com comandos ocultos:

1. Verifique os logs do sistema
2. Execute `/voice_status` para diagnóstico
3. Consulte a documentação técnica
4. Entre em contato com a equipe de desenvolvimento
