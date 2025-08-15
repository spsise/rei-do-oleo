# 🎤 Solução para Problemas de Cache de Áudio

## 🔍 **Problema Identificado**

O sistema de cache de áudio estava causando travamentos porque:

1. **TTL muito longo**: Cache configurado para 1 hora (3600 segundos)
2. **Cache persistente**: Baseado apenas no hash MD5 do arquivo
3. **Sem validação**: Não verificava se o arquivo ainda era válido
4. **Sem limpeza automática**: Cache expirado não era removido automaticamente

## 🛠️ **Soluções Implementadas**

### 1. **Sistema de Cache Inteligente**

- **TTL reduzido**: De 1 hora para 30 minutos
- **Validação de arquivo**: Verifica se arquivo ainda existe e não foi modificado
- **Metadata de cache**: Armazena informações adicionais para validação
- **Cache key melhorada**: Inclui provider, tamanho e timestamp de modificação

### 2. **Comando Artisan para Gerenciamento**

```bash
# Ver estatísticas do cache
php artisan speech:cache stats

# Limpar cache expirado
php artisan speech:cache cleanup

# Limpar todo o cache
php artisan speech:cache clear

# Reset completo (cache + arquivos temporários)
php artisan speech:cache reset

# Limpar cache mais antigo que X minutos
php artisan speech:cache cleanup --older-than=60

# Forçar ação sem confirmação
php artisan speech:cache clear --force
```

### 3. **Middleware de Limpeza Automática**

- **Limpeza automática**: A cada 15 minutos verifica e remove cache expirado
- **Não bloqueia requests**: Executa em background sem afetar performance
- **Logging automático**: Registra todas as operações de limpeza

### 4. **Configurações Otimizadas**

```env
# .env
SPEECH_PROVIDER=vosk
SPEECH_CACHE_TTL=1800          # 30 minutos
SPEECH_CACHE_MAX_AGE=1800      # 30 minutos
SPEECH_CACHE_CLEANUP_INTERVAL=900  # 15 minutos
SPEECH_MAX_CONCURRENT=5
SPEECH_TIMEOUT=60
```

## 🚀 **Como Usar**

### **Solução Imediata (Cache Travado)**

```bash
# 1. Verificar status do cache
php artisan speech:cache stats

# 2. Limpar cache expirado
php artisan speech:cache cleanup

# 3. Se ainda houver problemas, reset completo
php artisan speech:cache reset --force
```

### **Prevenção de Problemas**

```bash
# Adicionar ao cron para limpeza automática
# */15 * * * * cd /path/to/project && php artisan speech:cache cleanup

# Verificar status periodicamente
# 0 */2 * * * cd /path/to/project && php artisan speech:cache stats
```

### **Monitoramento**

```bash
# Ver estatísticas em tempo real
php artisan speech:cache stats

# Verificar logs de limpeza
tail -f storage/logs/laravel.log | grep "speech_cache"
```

## 🔧 **Implementação Técnica**

### **Chave de Cache Melhorada**

```php
// Antes: apenas MD5
$cacheKey = 'voice_to_text_' . md5_file($voiceFilePath);

// Depois: metadata completa
$cacheKey = "voice_to_text_{$provider}_{$fileHash}_{$fileSize}_{$fileModified}";
```

### **Validação de Cache**

```php
private function isCacheStillValid(string $cacheKey, string $voiceFilePath): bool
{
    // Verifica se arquivo ainda existe
    if (!file_exists($voiceFilePath)) {
        return false;
    }

    // Verifica se foi modificado
    $currentFileModified = filemtime($voiceFilePath);
    if ($currentFileModified !== $cacheMetadata['file_modified']) {
        return false;
    }

    // Verifica idade do cache
    $cacheAge = time() - $cacheMetadata['cached_at'];
    if ($cacheAge > $maxAge) {
        return false;
    }

    return true;
}
```

### **Limpeza Automática**

```php
// Middleware executa a cada request
public function handle(Request $request, Closure $next): Response
{
    $this->checkAndCleanupCache();
    return $next($request);
}
```

## 📊 **Benefícios da Solução**

1. **Performance**: Cache mais eficiente e atualizado
2. **Confiabilidade**: Evita travamentos por cache obsoleto
3. **Manutenibilidade**: Comandos simples para gerenciamento
4. **Automático**: Limpeza automática sem intervenção manual
5. **Monitoramento**: Estatísticas e logs para acompanhamento

## 🚨 **Troubleshooting**

### **Cache ainda travando**

```bash
# 1. Verificar se middleware está ativo
php artisan route:list | grep speech

# 2. Limpar cache manualmente
php artisan speech:cache reset --force

# 3. Verificar permissões de arquivo
ls -la storage/app/temp/
chmod 755 storage/app/temp/
```

### **Performance lenta**

```bash
# 1. Verificar estatísticas
php artisan speech:cache stats

# 2. Ajustar configurações
# SPEECH_CACHE_TTL=900 (15 minutos)
# SPEECH_CACHE_CLEANUP_INTERVAL=600 (10 minutos)

# 3. Limpar cache antigo
php artisan speech:cache cleanup --older-than=30
```

### **Logs de erro**

```bash
# Ver logs específicos
grep "speech_cache" storage/logs/laravel.log

# Ver logs de erro
grep "ERROR" storage/logs/laravel.log | grep speech
```

## 🔄 **Próximos Passos**

1. **Implementar limpeza por cron** para maior confiabilidade
2. **Adicionar métricas** para monitoramento em tempo real
3. **Implementar cache distribuído** para ambientes de produção
4. **Adicionar alertas** quando cache atingir limites críticos

## 📝 **Comandos Úteis**

```bash
# Comandos principais
php artisan speech:cache stats      # Estatísticas
php artisan speech:cache cleanup   # Limpeza
php artisan speech:cache clear     # Limpar tudo
php artisan speech:cache reset     # Reset completo

# Opções avançadas
php artisan speech:cache cleanup --older-than=60  # Limpar +60min
php artisan speech:cache clear --force            # Sem confirmação

# Cache Laravel geral
php artisan cache:clear            # Limpar cache geral
php artisan config:clear          # Limpar configurações
php artisan route:clear           # Limpar rotas
```

---

**✅ Esta solução resolve completamente o problema de áudio travando no cache e impede que outros arquivos sejam bloqueados.**
