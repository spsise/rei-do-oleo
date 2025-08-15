# 🔊 Speech-to-Text Service - Troubleshooting Guide

## 📋 **Problema Identificado: Logs Duplicados de Cache Hit**

### **Sintomas:**

- Múltiplos logs `voice_to_text_cache_hit` para o mesmo arquivo
- Logs excessivos poluindo o sistema
- Possível processamento duplicado de arquivos de áudio

### **Exemplo de Log Problemático:**

```json
{
  "event": "voice_to_text_cache_hit",
  "level": "info",
  "data": {
    "file": "/path/to/voice_file_33.oga",
    "cache_key": "voice_to_text_google_2407559eae79bd9f6440c42a9ac2ff4c_40043",
    "cache_age": 19
  }
}
```

## 🛠️ **Soluções Implementadas**

### **1. Prevenção de Processamento Duplicado**

- **Processing Flags**: Sistema de flags para evitar processamento simultâneo do mesmo arquivo
- **Wait Mechanism**: Aguarda processamento em andamento em vez de reprocessar
- **Timeout Protection**: Timeout automático para flags de processamento travados

### **2. Prevenção de Logs Duplicados**

- **Cache Hit Logging**: Cada cache hit é logado apenas uma vez por arquivo
- **Log Flags**: Sistema de flags para controlar logs de cache hit
- **Automatic Cleanup**: Limpeza automática de flags de log expirados

### **3. Sistema de Cache Inteligente**

- **Metadata Validation**: Validação de metadados para garantir integridade do cache
- **File Size Checking**: Verificação de tamanho do arquivo para detectar mudanças
- **Automatic Expiration**: Expiração automática de entradas de cache antigas

## 🚀 **Comandos de Diagnóstico e Limpeza**

### **Diagnosticar Problemas de Cache**

```bash
# Diagnóstico completo do cache
php artisan speech:cache diagnose

# Estatísticas do cache
php artisan speech:cache stats

# Limpeza de cache expirado e logs duplicados
php artisan speech:cache cleanup

# Limpeza forçada de todos os problemas
php artisan speech:cache force-cleanup

# Limpar todo o cache (cuidado!)
php artisan speech:cache clear --force
```

### **Exemplo de Saída do Diagnóstico:**

```bash
🔊 Speech-to-Text Cache Management
Action: diagnose

🔍 Diagnosing cache issues...

📋 Diagnosis Results:

📊 Cache Statistics:
+----------------------+-------+
| Metric               | Value |
+----------------------+-------+
| Total Entries       | 15    |
| Active Entries      | 12    |
| Expired Entries     | 3     |
| Duplicate Log Flags | 8     |
| Stuck Processing    | 1     |
+----------------------+-------+

🔍 Duplicate Log Analysis:
+--------------------------------+------------------+----------------------------------+
| Cache Key                      | Age (seconds)   | Note                            |
+--------------------------------+------------------+----------------------------------+
| voice_to_text_google_abc123... | 1200            | This key has duplicate log...   |
+--------------------------------+------------------+----------------------------------+

💡 Recommendations:
  • High number of duplicate log flags detected. Consider running cleanup.
  • Stuck processing flags detected. Consider running cleanup.

💡 Use 'php artisan speech:cache cleanup' to clean up issues
💡 Use 'php artisan speech:cache force-cleanup' for aggressive cleanup
```

## ⚙️ **Configurações de Cache**

### **Arquivo: `config/speech.php`**

```php
'cache' => [
    // TTL para resultados em cache (em segundos)
    'ttl' => env('SPEECH_CACHE_TTL', 1800), // 30 minutos

    // Idade máxima para entradas de cache (em segundos)
    'max_age' => env('SPEECH_CACHE_MAX_AGE', 1800), // 30 minutos

    // Prevenir logs duplicados
    'prevent_duplicate_logs' => env('SPEECH_PREVENT_DUPLICATE_LOGS', true),

    // Prevenir processamento duplicado
    'prevent_duplicate_processing' => env('SPEECH_PREVENT_DUPLICATE_PROCESSING', true),

    // Tempo máximo de espera para conclusão do processamento (em segundos)
    'max_wait_time' => env('SPEECH_MAX_WAIT_TIME', 30),

    // Timeout do flag de processamento (em segundos)
    'processing_timeout' => env('SPEECH_PROCESSING_TIMEOUT', 60),
],
```

### **Variáveis de Ambiente (.env)**

```env
# Speech-to-Text Cache Configuration
SPEECH_CACHE_TTL=1800
SPEECH_CACHE_MAX_AGE=1800
SPEECH_PREVENT_DUPLICATE_LOGS=true
SPEECH_PREVENT_DUPLICATE_PROCESSING=true
SPEECH_MAX_WAIT_TIME=30
SPEECH_PROCESSING_TIMEOUT=60

# Provider Configuration
SPEECH_PROVIDER=google
GOOGLE_SPEECH_API_KEY=your_api_key_here
```

## 🔍 **Middleware de Prevenção de Requisições Duplicadas**

### **Arquivo: `app/Http/Middleware/PreventDuplicateSpeechRequests.php`**

- **Funcionalidade**: Previne requisições duplicadas ao serviço de speech-to-text
- **Detecção**: Identifica automaticamente endpoints de speech-to-text
- **Validação**: Verifica arquivos de áudio e gera chaves únicas
- **Rate Limiting**: Retorna erro 429 para requisições duplicadas

### **Registro no Kernel:**

```php
// app/Http/Kernel.php
protected $middlewareGroups = [
    'api' => [
        // ... outros middlewares
        \App\Http\Middleware\PreventDuplicateSpeechRequests::class,
    ],
];
```

## 📊 **Monitoramento e Métricas**

### **Estatísticas Disponíveis:**

- Total de entradas no cache
- Entradas ativas vs. expiradas
- Flags de log duplicados
- Flags de processamento travados
- Tamanho total do cache
- Idade das entradas mais antigas/novas

### **Logs de Eventos:**

- `voice_to_text_cache_hit` - Cache hit (apenas uma vez por arquivo)
- `voice_to_text_already_processing` - Arquivo já sendo processado
- `voice_to_text_retrieved_after_wait` - Resultado obtido após espera
- `duplicate_speech_request_prevented` - Requisição duplicada bloqueada
- `speech_cache_cleanup_completed` - Limpeza de cache concluída

## 🚨 **Problemas Comuns e Soluções**

### **1. Logs Excessivos de Cache Hit**

**Problema**: Múltiplos logs para o mesmo arquivo
**Solução**:

```bash
php artisan speech:cache cleanup
```

### **2. Flags de Processamento Travados**

**Problema**: Arquivos marcados como "em processamento" permanentemente
**Solução**:

```bash
php artisan speech:cache force-cleanup
```

### **3. Cache Corrompido**

**Problema**: Resultados incorretos ou metadados inválidos
**Solução**:

```bash
php artisan speech:cache clear --force
```

### **4. Performance Degradada**

**Problema**: Cache muito grande ou entradas antigas
**Solução**:

```bash
php artisan speech:cache cleanup
# Configurar TTL menor no .env
SPEECH_CACHE_TTL=900  # 15 minutos
```

## 🔧 **Manutenção Preventiva**

### **Cron Job Recomendado:**

```bash
# Adicionar ao crontab
0 */2 * * * cd /path/to/your/app && php artisan speech:cache cleanup
```

### **Monitoramento Automático:**

```bash
# Verificar status do cache a cada hora
0 * * * * cd /path/to/your/app && php artisan speech:cache stats >> /var/log/speech-cache.log
```

## 📝 **Logs de Debug**

### **Habilitar Logs Detalhados:**

```env
SPEECH_LOGGING_ENABLED=true
SPEECH_LOG_LEVEL=debug
SPEECH_LOG_CACHE_HITS=true
SPEECH_LOG_PROCESSING_ATTEMPTS=true
```

### **Exemplo de Log de Debug:**

```json
{
  "event": "voice_to_text_processing_started",
  "file": "/path/to/voice_file.oga",
  "cache_key": "voice_to_text_google_abc123_40043",
  "provider": "google",
  "timestamp": "2024-01-15T10:30:00Z"
}
```

## 🎯 **Resumo das Melhorias**

1. ✅ **Prevenção de Processamento Duplicado**
2. ✅ **Controle de Logs Duplicados**
3. ✅ **Sistema de Cache Inteligente**
4. ✅ **Middleware de Proteção**
5. ✅ **Comandos de Diagnóstico**
6. ✅ **Configurações Flexíveis**
7. ✅ **Monitoramento e Métricas**
8. ✅ **Limpeza Automática**

## 📞 **Suporte**

Para problemas persistentes ou dúvidas:

1. Execute `php artisan speech:cache diagnose`
2. Verifique os logs do sistema
3. Consulte as configurações em `config/speech.php`
4. Execute limpeza com `php artisan speech:cache cleanup`

---

**Última atualização**: Janeiro 2024  
**Versão**: 2.0.0  
**Status**: ✅ Implementado e Testado
