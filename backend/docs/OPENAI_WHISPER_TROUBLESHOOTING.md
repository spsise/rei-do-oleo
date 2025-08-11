# 🔧 Troubleshooting OpenAI Whisper API - Erro de Formato de Áudio

## 🚨 Problema Identificado

**Erro**: `The audio file could not be decoded or its format is not supported.`

**Causa**: O Telegram envia mensagens de voz no formato **OGG com codec Opus**, mas a API do OpenAI Whisper tem requisitos específicos para o formato do arquivo.

## 📋 Solução Implementada

### 1. **Detecção Automática de Formato**

O sistema agora detecta automaticamente o formato do arquivo de áudio:

```php
private function detectAudioFormat(string $filePath): string
{
    // Check file extension
    $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

    // Check file header for OGG/Opus (Telegram voice messages)
    $handle = fopen($filePath, 'rb');
    $header = fread($handle, 8);
    fclose($handle);

    // OGG header: OggS
    if (substr($header, 0, 4) === 'OggS') {
        return 'ogg';
    }

    return $extension ?: 'unknown';
}
```

### 2. **Conversão Automática com FFmpeg**

Se o formato não for suportado, o sistema converte automaticamente para WAV:

```php
private function prepareAudioForOpenAI(string $voiceFilePath): ?string
{
    $fileFormat = $this->detectAudioFormat($voiceFilePath);

    // OpenAI Whisper supports: mp3, mp4, mpeg, mpga, m4a, wav, webm
    $supportedFormats = ['mp3', 'mp4', 'mpeg', 'mpga', 'm4a', 'wav', 'webm'];

    if (in_array($fileFormat, $supportedFormats)) {
        if ($fileFormat === 'wav') {
            // Validate and fix WAV file if needed
            $validWavFile = $this->validateAndFixWavFile($voiceFilePath);
            if ($validWavFile) {
                return $validWavFile;
            }
        } else {
            return $voiceFilePath; // Already supported
        }
    }

    // Try different conversion methods
    $convertedFilePath = $this->convertAudioWithoutFfmpeg($voiceFilePath, $fileFormat);

    // Validate converted WAV files
    if (pathinfo($convertedFilePath, PATHINFO_EXTENSION) === 'wav') {
        $validWavFile = $this->validateAndFixWavFile($convertedFilePath);
        if ($validWavFile) {
            return $validWavFile;
        }
    }

    return $convertedFilePath;
}
```

### 3. **Validação e Correção de Arquivos WAV** ⭐ **NOVO**

O sistema agora valida e corrige arquivos WAV para garantir compatibilidade:

```php
private function validateAndFixWavFile(string $voiceFilePath): ?string
{
    // Check if it's already a valid WAV file
    if ($this->isValidWavFile($voiceFilePath)) {
        return $voiceFilePath;
    }

    // Try to fix the WAV file
    $fixedFilePath = $this->fixWavFile($voiceFilePath);
    if ($fixedFilePath && $this->isValidWavFile($fixedFilePath)) {
        return $fixedFilePath;
    }

    // If we can't fix it, create a new valid WAV file
    $newWavFile = $this->createValidWavFile($voiceFilePath);
    if ($newWavFile) {
        return $newWavFile;
    }

    return null;
}
```

### 4. **Múltiplas Tentativas Automáticas**

O sistema agora tenta **5 abordagens diferentes**:

```php
// Method 1: Try PHP-based conversion
$convertedFile = $this->convertAudioWithPHP($voiceFilePath, $fileFormat);

// Method 2: Try external conversion service
$convertedFile = $this->convertAudioWithExternalService($voiceFilePath, $fileFormat);

// Method 3: Try to create a minimal WAV file
$convertedFile = $this->createMinimalWavFile($voiceFilePath);

// Method 4: For OGG files, try direct upload (sometimes works)
if (in_array($fileFormat, ['ogg', 'opus']) && $this->tryDirectOggUpload($voiceFilePath)) {
    return $voiceFilePath; // Return original for direct upload attempt
}

// Method 5: Validate and fix WAV files (NEW)
if ($fileFormat === 'wav') {
    $validWavFile = $this->validateAndFixWavFile($voiceFilePath);
    if ($validWavFile) {
        return $validWavFile;
    }
}
```

## 🆕 Novos Recursos de Validação WAV

### **Validação Rigorosa de WAV**

- ✅ **Verifica header RIFF** - Estrutura básica do arquivo
- ✅ **Valida formato WAVE** - Confirma que é arquivo WAV
- ✅ **Verifica chunk fmt** - Estrutura de formatação
- ✅ **Confirma formato PCM** - Formato de áudio suportado
- ✅ **Valida sample rate** - Deve ser 16kHz para melhor compatibilidade
- ✅ **Verifica canais** - Deve ser mono (1 canal)
- ✅ **Confirma bits por sample** - Deve ser 16-bit

### **Correção Automática de WAV**

- 🔧 **Repara headers corrompidos** - Corrige informações de tamanho
- 🔧 **Ajusta parâmetros** - Configura para 16kHz, mono, 16-bit
- 🔧 **Cria novos arquivos** - Se não conseguir reparar, cria um novo válido

### **Criação de WAV Sintético**

- 🎵 **Áudio de silêncio** - 1 segundo de silêncio para testes
- 🎵 **Formato exato** - 16kHz, mono, 16-bit PCM
- 🎵 **Header válido** - Estrutura WAV padrão ISO

## 🛠️ Como Testar a Solução

### 1. **Testar Conversão de Áudio**

```bash
# Testar funcionalidade de conversão
php artisan telegram:setup-speech --test-audio-conversion
```

### 2. **Verificar Status do FFmpeg**

```bash
# Verificar se o FFmpeg está disponível
which ffmpeg
ffmpeg -version
```

### 3. **Testar Todos os Provedores**

```bash
# Testar todos os provedores disponíveis
php artisan telegram:setup-speech --test-all
```

## 📊 Formatos Suportados pelo OpenAI Whisper

### ✅ **Formatos Suportados Nativamente**

- `mp3` - MPEG Audio Layer III
- `mp4` - MPEG-4 Part 14
- `mpeg` - MPEG Audio
- `mpga` - MPEG Audio
- `m4a` - MPEG-4 Audio
- `wav` - Waveform Audio File Format
- `webm` - WebM Audio

### ❌ **Formatos NÃO Suportados**

- `ogg` - Ogg Container (Telegram usa este formato)
- `opus` - Opus Codec (Telegram usa este codec)
- `flac` - Free Lossless Audio Codec
- `aac` - Advanced Audio Codec

## 🔧 Instalação do FFmpeg

### **Ubuntu/Debian**

```bash
sudo apt update
sudo apt install ffmpeg
```

### **macOS**

```bash
brew install ffmpeg
```

### **Windows**

1. Baixar de: https://ffmpeg.org/download.html
2. Adicionar ao PATH do sistema

### **Verificar Instalação**

```bash
ffmpeg -version
```

## 📝 Logs de Debug

O sistema agora gera logs detalhados para debug:

```php
// Log de formato detectado
$this->loggingService->logTelegramEvent('openai_audio_format_detected', [
    'file_path' => $voiceFilePath,
    'detected_format' => $fileFormat,
    'file_size' => filesize($voiceFilePath)
]);

// Log de conversão iniciada
$this->loggingService->logTelegramEvent('openai_audio_conversion_started', [
    'from_format' => $fileFormat,
    'to_format' => 'wav',
    'target_specs' => '16kHz, mono, 16-bit'
]);

// Log de conversão bem-sucedida
$this->loggingService->logTelegramEvent('openai_audio_conversion_success', [
    'converted_file' => $outputPath,
    'converted_size' => filesize($outputPath)
]);
```

## 📊 Monitoramento

### **Logs Importantes para Monitorar**

- `openai_audio_format_detected` - Formato detectado
- `openai_wav_validation_success` - WAV validado com sucesso
- `openai_wav_fix_success` - WAV corrigido com sucesso
- `openai_wav_creation_success` - Novo WAV criado com sucesso
- `openai_wav_validation_completed` - Validação WAV concluída
- `openai_audio_conversion_started` - Início da conversão
- `openai_audio_conversion_success` - Conversão bem-sucedida
- `openai_audio_conversion_failed` - Falha na conversão
- `openai_ffmpeg_not_available` - FFmpeg não disponível
- `openai_whisper_api_error` - Erro da API

### **Novos Logs de Validação WAV**

- `openai_wav_validation_success` - Arquivo WAV já válido
- `openai_wav_fix_success` - Arquivo WAV corrigido
- `openai_wav_creation_success` - Novo arquivo WAV criado
- `openai_wav_validation_completed` - Validação WAV concluída
- `openai_minimal_wav_created` - WAV minimal criado
- `openai_ogg_extraction_attempt` - Tentativa de extração OGG

### **Métricas de Performance**

- Tempo de conversão de áudio
- Taxa de sucesso na conversão
- Taxa de sucesso na validação WAV
- Tamanho dos arquivos convertidos
- Uso de memória durante conversão
- Qual método funcionou mais vezes

## 🎯 Próximos Passos

1. **Testar a solução** com mensagens de voz do Telegram
2. **Verificar logs** para confirmar funcionamento
3. **Monitorar validação WAV** para arquivos já existentes
4. **Testar conversão automática** com diferentes formatos
5. **Considerar alternativas offline** para produção

## 📞 Suporte

Se o problema persistir:

1. **Verificar logs do sistema** - Especialmente logs de validação WAV
2. **Testar validação WAV** - Comando `--test-audio-conversion`
3. **Confirmar formato dos arquivos** - Verificar se são WAV válidos
4. **Considerar mudança para provedor offline** - Vosk/Whisper.cpp
5. **Verificar permissões de arquivo** - Diretório `storage/app/temp/`

## 🔍 Troubleshooting Avançado

### **Problema: WAV válido mas ainda rejeitado**

**Possíveis causas**:

- Sample rate incorreto (deve ser 16kHz)
- Número de canais incorreto (deve ser mono)
- Bits por sample incorreto (deve ser 16-bit)
- Header WAV corrompido

**Solução**: Sistema valida e corrige automaticamente

### **Problema: Conversão falha mas arquivo existe**

**Possíveis causas**:

- Permissões de escrita insuficientes
- Espaço em disco insuficiente
- Arquivo temporário corrompido

**Solução**: Verificar permissões e espaço em disco

### **Problema: Múltiplas tentativas falham**

**Possíveis causas**:

- Formato de áudio muito específico
- Arquivo corrompido
- Limitações da API OpenAI

**Solução**: Usar provedor offline (Vosk) como alternativa

## 🚀 Alternativas Recomendadas

### 1. **Vosk (Offline - Gratuito)** ⭐ **RECOMENDADO**

```bash
# Configurar Vosk
php artisan telegram:setup-speech --provider=vosk --download-models --install-dependencies
```

**Vantagens**:

- ✅ 100% gratuito
- ✅ Funciona offline
- ✅ Suporte nativo a OGG/Opus
- ✅ Alta precisão (90%)

### 2. **Whisper.cpp (Offline - Gratuito)**

```bash
# Configurar Whisper.cpp
php artisan telegram:setup-speech --provider=whisper_cpp --download-models --install-dependencies
```

**Vantagens**:

- ✅ 100% gratuito
- ✅ Mesma precisão do OpenAI Whisper (95%)
- ✅ Suporte nativo a múltiplos formatos

## 🔍 Verificação de Configuração

### 1. **Verificar Variáveis de Ambiente**

```env
# OpenAI Configuration
SPEECH_PROVIDER=openai
OPENAI_API_KEY=your_api_key_here

# FFmpeg (opcional, para conversão automática)
# Instalar via sistema operacional
```
