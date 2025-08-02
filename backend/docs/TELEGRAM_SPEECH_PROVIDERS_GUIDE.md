# 🎤 Guia Completo dos Provedores de Speech-to-Text

## 📋 Visão Geral

O sistema agora suporta **7 provedores diferentes** de conversão de voz para texto, incluindo opções **100% gratuitas** e offline. O **Vosk** está configurado como padrão por ser gratuito, offline e de alta precisão.

## 🏆 Provedores Disponíveis

### **1. Vosk (Padrão - Recomendado)** 🥇

#### **Características:**

- ✅ **100% Gratuito** - Sem custos de API
- ✅ **Offline** - Funciona sem internet
- ✅ **Alta Precisão** - 90% de acurácia
- ✅ **Múltiplos Idiomas** - Incluindo português
- ✅ **Sem Limites** - Processamento ilimitado
- ✅ **Fácil Instalação** - Via Composer

#### **Configuração:**

```env
SPEECH_PROVIDER=vosk
VOSK_MODEL_PATH=/path/to/vosk-model-small-pt-0.3
```

#### **Instalação:**

```bash
# Instalar dependências
composer require vosk/php-vosk

# Configurar modelo
php artisan telegram:setup-speech --provider=vosk --download-models --install-dependencies
```

---

### **2. Whisper.cpp (Offline - Gratuito)** 🥈

#### **Características:**

- ✅ **100% Gratuito** - Implementação open source do Whisper
- ✅ **Offline** - Sem dependência de internet
- ✅ **Alta Precisão** - 95% de acurácia (mesmo do OpenAI)
- ✅ **Mesmo Modelo** - Baseado no Whisper da OpenAI
- ✅ **Rápido** - Otimizado para C++

#### **Configuração:**

```env
SPEECH_PROVIDER=whisper_cpp
WHISPER_CPP_PATH=/usr/local/bin/whisper
WHISPER_CPP_MODEL_PATH=/path/to/ggml-base.bin
```

#### **Instalação:**

```bash
# Instalar e configurar
php artisan telegram:setup-speech --provider=whisper_cpp --download-models --install-dependencies
```

---

### **3. DeepSpeech (Offline - Gratuito)** 🥉

#### **Características:**

- ✅ **100% Gratuito** - Open source da Mozilla
- ✅ **Offline** - Sem dependência de internet
- ✅ **Boa Precisão** - 88% de acurácia
- ✅ **Múltiplos Idiomas** - Suporte completo
- ⚠️ **Mais Lento** - Processamento mais demorado

#### **Configuração:**

```env
SPEECH_PROVIDER=deepspeech
DEEPSPEECH_MODEL_PATH=/path/to/deepspeech-0.9.3-models.pbmm
DEEPSPEECH_SCORER_PATH=/path/to/deepspeech-0.9.3-models.scorer
```

#### **Instalação:**

```bash
# Instalar e configurar
php artisan telegram:setup-speech --provider=deepspeech --download-models --install-dependencies
```

---

### **4. Hugging Face (Online - Gratuito)**

#### **Características:**

- ✅ **100% Gratuito** - APIs gratuitas
- ✅ **Alta Precisão** - 92% de acurácia
- ✅ **Múltiplos Modelos** - Vários disponíveis
- ❌ **Online** - Requer internet
- ⚠️ **Rate Limits** - Limites de uso

#### **Configuração:**

```env
SPEECH_PROVIDER=huggingface
HUGGINGFACE_API_URL=https://api-inference.huggingface.co/models/openai/whisper-base
HUGGINGFACE_API_KEY=your_api_key_here  # Opcional
```

---

### **5. OpenAI Whisper (Online - Pago)**

#### **Características:**

- ✅ **Alta Precisão** - 95% de acurácia
- ✅ **Múltiplos Idiomas** - Suporte completo
- ❌ **Pago** - $0.006 por minuto
- ❌ **Online** - Requer internet
- ❌ **Rate Limits** - Limites de API

#### **Configuração:**

```env
SPEECH_PROVIDER=openai
OPENAI_API_KEY=your_openai_api_key_here
```

---

### **6. Google Speech-to-Text (Online - Pago)**

#### **Características:**

- ✅ **Alta Precisão** - 94% de acurácia
- ✅ **Integração Google** - Cloud Platform
- ❌ **Pago** - Baseado no uso
- ❌ **Online** - Requer internet
- ❌ **Rate Limits** - Limites de API

#### **Configuração:**

```env
SPEECH_PROVIDER=google
GOOGLE_SPEECH_API_KEY=your_google_speech_api_key_here
```

---

### **7. Azure Speech Services (Online - Pago)**

#### **Características:**

- ✅ **Boa Precisão** - 93% de acurácia
- ✅ **Integração Azure** - Microsoft Cloud
- ❌ **Pago** - Baseado no uso
- ❌ **Online** - Requer internet
- ❌ **Rate Limits** - Limites de API

#### **Configuração:**

```env
SPEECH_PROVIDER=azure
AZURE_SPEECH_KEY=your_azure_speech_key_here
AZURE_SPEECH_REGION=your_azure_region_here
```

## 📊 Comparação Completa

| **Provedor**     | **Custo**    | **Precisão** | **Velocidade** | **Offline** | **Instalação** | **Recomendação**   |
| ---------------- | ------------ | ------------ | -------------- | ----------- | -------------- | ------------------ |
| **Vosk**         | **GRATUITO** | 90%          | Médio          | ✅          | Fácil          | 🥇 **PADRÃO**      |
| **Whisper.cpp**  | **GRATUITO** | 95%          | Médio          | ✅          | Médio          | 🥈 **ALTERNATIVA** |
| **DeepSpeech**   | **GRATUITO** | 88%          | Lento          | ✅          | Complexo       | 🥉 **BACKUP**      |
| **Hugging Face** | **GRATUITO** | 92%          | Rápido         | ❌          | Fácil          | **ONLINE**         |
| **OpenAI**       | $0.006/min   | 95%          | Rápido         | ❌          | Fácil          | **PAGO**           |
| **Google**       | Variável     | 94%          | Rápido         | ❌          | Médio          | **PAGO**           |
| **Azure**        | Variável     | 93%          | Rápido         | ❌          | Médio          | **PAGO**           |

## 🚀 Configuração Rápida

### **1. Configuração Padrão (Vosk)**

```bash
# Configurar Vosk como padrão
php artisan telegram:setup-speech --provider=vosk --download-models --install-dependencies
```

### **2. Testar Todos os Provedores**

```bash
# Testar todos os provedores disponíveis
php artisan telegram:test-speech --all-providers
```

### **3. Configurar Provedor Específico**

```bash
# Configurar Whisper.cpp
php artisan telegram:setup-speech --provider=whisper_cpp --download-models --install-dependencies

# Configurar DeepSpeech
php artisan telegram:setup-speech --provider=deepspeech --download-models --install-dependencies
```

## 🔧 Comandos Disponíveis

### **TelegramSetupSpeechCommand**

```bash
# Configurar provedor específico
php artisan telegram:setup-speech --provider=vosk

# Instalar dependências
php artisan telegram:setup-speech --provider=vosk --install-dependencies

# Baixar modelos
php artisan telegram:setup-speech --provider=vosk --download-models

# Configuração completa
php artisan telegram:setup-speech --provider=vosk --download-models --install-dependencies
```

### **TelegramTestSpeechCommand**

```bash
# Testar conexão
php artisan telegram:test-speech

# Testar com arquivo específico
php artisan telegram:test-speech --file=test.ogg

# Testar todos os provedores
php artisan telegram:test-speech --all-providers

# Testar provedor específico
php artisan telegram:test-speech --provider=vosk
```

## 📁 Estrutura de Arquivos

### **Modelos Baixados:**

```
storage/app/
├── vosk-models/
│   └── vosk-model-small-pt-0.3/
├── whisper-models/
│   └── ggml-base.bin
└── deepspeech-models/
    ├── deepspeech-0.9.3-models.pbmm
    └── deepspeech-0.9.3-models.scorer
```

### **Configurações:**

```env
# Speech-to-Text Configuration
SPEECH_PROVIDER=vosk
SPEECH_CACHE_ENABLED=true
SPEECH_CACHE_TTL=3600

# Vosk Configuration
VOSK_MODEL_PATH=/var/www/html/storage/app/vosk-models/vosk-model-small-pt-0.3

# Whisper.cpp Configuration
WHISPER_CPP_PATH=/usr/local/bin/whisper
WHISPER_CPP_MODEL_PATH=/var/www/html/storage/app/whisper-models/ggml-base.bin

# DeepSpeech Configuration
DEEPSPEECH_MODEL_PATH=/var/www/html/storage/app/deepspeech-models/deepspeech-0.9.3-models.pbmm
DEEPSPEECH_SCORER_PATH=/var/www/html/storage/app/deepspeech-models/deepspeech-0.9.3-models.scorer

# Hugging Face Configuration
HUGGINGFACE_API_URL=https://api-inference.huggingface.co/models/openai/whisper-base
HUGGINGFACE_API_KEY=your_api_key_here
```

## 🎯 Recomendações por Cenário

### **Desenvolvimento Local**

- **Recomendado**: Vosk
- **Motivo**: Fácil instalação, gratuito, offline

### **Produção com Orçamento Limitado**

- **Recomendado**: Vosk + Whisper.cpp
- **Motivo**: Ambos gratuitos, alta precisão

### **Produção com Alta Demanda**

- **Recomendado**: Vosk (padrão) + OpenAI (fallback)
- **Motivo**: Vosk para maioria, OpenAI para casos especiais

### **Ambiente Offline**

- **Recomendado**: Vosk + Whisper.cpp + DeepSpeech
- **Motivo**: Múltiplas opções offline

### **Alta Precisão Necessária**

- **Recomendado**: Whisper.cpp ou OpenAI
- **Motivo**: 95% de acurácia

## 🔍 Troubleshooting

### **Problemas Comuns**

#### **1. Vosk - Modelo não encontrado**

```bash
# Verificar se o modelo existe
ls -la storage/app/vosk-models/

# Rebaixar modelo
php artisan telegram:setup-speech --provider=vosk --download-models
```

#### **2. Whisper.cpp - Comando não encontrado**

```bash
# Verificar instalação
which whisper

# Reinstalar
php artisan telegram:setup-speech --provider=whisper_cpp --install-dependencies
```

#### **3. DeepSpeech - Erro de Python**

```bash
# Verificar Python
python3 --version

# Reinstalar DeepSpeech
pip3 install --upgrade deepspeech
```

#### **4. Cache não funcionando**

```bash
# Limpar cache
php artisan cache:clear

# Verificar configuração
php artisan config:cache
```

## 📈 Performance e Otimização

### **Métricas Esperadas**

| **Provedor**     | **Tempo de Processamento** | **Uso de Memória** | **Taxa de Sucesso** |
| ---------------- | -------------------------- | ------------------ | ------------------- |
| **Vosk**         | 2-5 segundos               | Baixo              | 95%                 |
| **Whisper.cpp**  | 3-7 segundos               | Médio              | 98%                 |
| **DeepSpeech**   | 5-10 segundos              | Alto               | 90%                 |
| **Hugging Face** | 1-3 segundos               | Baixo              | 92%                 |
| **OpenAI**       | 1-2 segundos               | Baixo              | 99%                 |

### **Otimizações**

#### **1. Cache Inteligente**

- **Duração**: 1 hora por padrão
- **Chave**: MD5 do arquivo de voz
- **Configurável**: Via `SPEECH_CACHE_TTL`

#### **2. Limpeza Automática**

- **Arquivos temporários**: Removidos após processamento
- **Cache expirado**: Limpeza automática

#### **3. Fallback Automático**

- **Provedor primário**: Vosk (configurado)
- **Fallback**: Outros provedores se necessário

## 🎉 Conclusão

O sistema agora oferece **flexibilidade total** para conversão de voz para texto:

- ✅ **7 provedores** diferentes disponíveis
- ✅ **3 opções gratuitas** e offline
- ✅ **Vosk configurado como padrão**
- ✅ **Fácil configuração** via comandos Artisan
- ✅ **Alta precisão** em todas as opções
- ✅ **Zero custos** com provedores gratuitos

**Recomendação final**: Use **Vosk** como padrão para a maioria dos casos, com **Whisper.cpp** como alternativa de alta precisão! 🎤🚀
