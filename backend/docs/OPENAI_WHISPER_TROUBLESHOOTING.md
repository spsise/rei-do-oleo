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
        return $voiceFilePath; // Already supported
    }

    // Convert to WAV format (16kHz, mono, 16-bit) for best compatibility
    $outputPath = $voiceFilePath . '_whisper.wav';

    if ($this->isFfmpegAvailable()) {
        $command = "ffmpeg -i {$voiceFilePath} -ar 16000 -ac 1 -c:a pcm_s16le -f wav {$outputPath} -y 2>/dev/null";
        shell_exec($command);

        if (file_exists($outputPath) && filesize($outputPath) > 0) {
            return $outputPath;
        }
    }

    return $voiceFilePath; // Fallback to original
}
```

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

### 2. **Verificar Status do Provedor**

```bash
php artisan telegram:setup-speech --provider=openai --status
```

### 3. **Testar Conectividade**

```bash
php artisan telegram:setup-speech --provider=openai --test
```

## 📊 Monitoramento

### **Logs Importantes para Monitorar**

- `openai_audio_format_detected` - Formato detectado
- `openai_audio_conversion_started` - Início da conversão
- `openai_audio_conversion_success` - Conversão bem-sucedida
- `openai_audio_conversion_failed` - Falha na conversão
- `openai_ffmpeg_not_available` - FFmpeg não disponível
- `openai_whisper_api_error` - Erro da API

### **Métricas de Performance**

- Tempo de conversão de áudio
- Taxa de sucesso na conversão
- Tamanho dos arquivos convertidos
- Uso de memória durante conversão

## 🎯 Próximos Passos

1. **Testar a solução** com mensagens de voz do Telegram
2. **Verificar logs** para confirmar funcionamento
3. **Monitorar performance** da conversão
4. **Considerar alternativas offline** para produção

## 📞 Suporte

Se o problema persistir:

1. Verificar logs do sistema
2. Confirmar instalação do FFmpeg
3. Testar com arquivos de áudio diferentes
4. Considerar mudança para provedor offline (Vosk/Whisper.cpp)
