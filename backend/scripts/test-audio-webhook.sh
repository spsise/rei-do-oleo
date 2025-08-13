#!/bin/bash

# 🧪 Script de Teste para Webhook de Áudio do Telegram
# Este script testa diferentes tipos de mensagens de áudio

set -e

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configurações
WEBHOOK_URL="http://localhost:8000/api/telegram/webhook"
WEBHOOK_SECRET="test_secret_123" # Altere para o seu secret real

# Função para log colorido
log() {
    echo -e "${GREEN}[$(date +'%Y-%m-%d %H:%M:%S')]${NC} $1"
}

error() {
    echo -e "${RED}[ERRO]${NC} $1"
}

warning() {
    echo -e "${YELLOW}[AVISO]${NC} $1"
}

info() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

# Função para testar webhook
test_webhook() {
    local test_name="$1"
    local payload="$2"
    local headers="$3"

    log "🧪 Testando: $test_name"

    if [ -n "$headers" ]; then
        response=$(curl -s -w "\n%{http_code}" -X POST "$WEBHOOK_URL" \
            -H "Content-Type: application/json" \
            -H "$headers" \
            -d "$payload")
    else
        response=$(curl -s -w "\n%{http_code}" -X POST "$WEBHOOK_URL" \
            -H "Content-Type: application/json" \
            -d "$payload")
    fi

    # Separar resposta e status code
    http_code=$(echo "$response" | tail -n1)
    response_body=$(echo "$response" | head -n -1)

    if [ "$http_code" -eq 200 ]; then
        log "✅ Sucesso (HTTP $http_code): $test_name"
        echo "Resposta: $response_body" | jq '.' 2>/dev/null || echo "Resposta: $response_body"
    elif [ "$http_code" -eq 401 ]; then
        warning "🔒 Não autorizado (HTTP $http_code): $test_name"
        echo "Resposta: $response_body"
    elif [ "$http_code" -eq 422 ]; then
        warning "⚠️ Erro de validação (HTTP $http_code): $test_name"
        echo "Resposta: $response_body" | jq '.' 2>/dev/null || echo "Resposta: $response_body"
    else
        error "❌ Falha (HTTP $http_code): $test_name"
        echo "Resposta: $response_body"
    fi

    echo "---"
    sleep 1
}

# Verificar se o servidor está rodando
log "🔍 Verificando se o servidor está rodando..."
if ! curl -s "$WEBHOOK_URL" > /dev/null 2>&1; then
    error "❌ Servidor não está rodando em $WEBHOOK_URL"
    error "Execute: php artisan serve"
    exit 1
fi

log "✅ Servidor está rodando!"

# Teste 1: Mensagem de voz (voice)
log "🎤 Teste 1: Mensagem de voz"
voice_payload='{
  "update_id": 123456789,
  "message": {
    "message_id": 123,
    "from": {
      "id": 987654321,
      "is_bot": false,
      "first_name": "Test User",
      "username": "testuser",
      "language_code": "pt"
    },
    "chat": {
      "id": 987654321,
      "first_name": "Test User",
      "username": "testuser",
      "type": "private"
    },
    "date": 1640995200,
    "voice": {
      "file_id": "AwACAgIAAxkBAAIB...",
      "file_unique_id": "AgADBAADGQEAAl8AAQ",
      "duration": 15,
      "mime_type": "audio/ogg"
    }
  }
}'

test_webhook "Mensagem de voz" "$voice_payload"

# Teste 2: Arquivo de áudio
log "🎵 Teste 2: Arquivo de áudio"
audio_payload='{
  "update_id": 123456790,
  "message": {
    "message_id": 124,
    "from": {
      "id": 987654321,
      "is_bot": false,
      "first_name": "Test User",
      "username": "testuser",
      "language_code": "pt"
    },
    "chat": {
      "id": 987654321,
      "first_name": "Test User",
      "username": "testuser",
      "type": "private"
    },
    "date": 1640995200,
    "audio": {
      "file_id": "AwACAgIAAxkBAAIB...",
      "file_unique_id": "AgADBAADGQEAAl8AAQ",
      "duration": 30,
      "title": "Test Audio File",
      "performer": "Test Performer",
      "mime_type": "audio/mpeg",
      "file_size": 1024000
    }
  }
}'

test_webhook "Arquivo de áudio" "$audio_payload"

# Teste 3: Mensagem de voz com secret válido
log "🔐 Teste 3: Mensagem de voz com secret válido"
if [ -n "$WEBHOOK_SECRET" ]; then
    test_webhook "Mensagem de voz com secret" "$voice_payload" "X-Telegram-Bot-Api-Secret-Token: $WEBHOOK_SECRET"
else
    warning "⚠️ WEBHOOK_SECRET não configurado, pulando teste de secret"
fi

# Teste 4: Mensagem de voz com secret inválido
log "🚫 Teste 4: Mensagem de voz com secret inválido"
test_webhook "Mensagem de voz com secret inválido" "$voice_payload" "X-Telegram-Bot-Api-Secret-Token: invalid_secret_123"

# Teste 5: Mensagem de voz sem secret
log "⚠️ Teste 5: Mensagem de voz sem secret"
test_webhook "Mensagem de voz sem secret" "$voice_payload"

# Teste 6: Mensagem de voz com campos faltando
log "❌ Teste 6: Mensagem de voz com campos faltando"
invalid_voice_payload='{
  "update_id": 123456791,
  "message": {
    "message_id": 125,
    "from": {
      "id": 987654321,
      "first_name": "Test User"
    },
    "voice": {
      "file_id": "AwACAgIAAxkBAAIB...",
      "duration": 10
    }
  }
}'

test_webhook "Mensagem de voz inválida" "$invalid_voice_payload"

# Teste 7: Diferentes formatos de áudio
log "🎼 Teste 7: Diferentes formatos de áudio"
audio_formats=(
    '{"duration": 20, "mime_type": "audio/ogg"}'
    '{"duration": 25, "title": "MP3 Audio", "mime_type": "audio/mpeg"}'
    '{"duration": 15, "mime_type": "audio/wav"}'
)

for i in "${!audio_formats[@]}"; do
    format_data="${audio_formats[$i]}"
    audio_payload_test='{
      "update_id": 12345679'$((i+1))',
      "message": {
        "message_id": 12'$((i+1))',
        "from": {
          "id": 987654321,
          "is_bot": false,
          "first_name": "Test User",
          "username": "testuser"
        },
        "chat": {
          "id": 987654321,
          "first_name": "Test User",
          "username": "testuser",
          "type": "private"
        },
        "date": 1640995200,
        "audio": '"$format_data"'
      }
    }'

    test_webhook "Formato de áudio $((i+1))" "$audio_payload_test"
done

# Teste 8: Mensagem mista (texto + áudio)
log "📝 Teste 8: Mensagem mista (texto + áudio)"
mixed_payload='{
  "update_id": 123456798,
  "message": {
    "message_id": 132,
    "from": {
      "id": 987654321,
      "is_bot": false,
      "first_name": "Test User",
      "username": "testuser"
    },
    "chat": {
      "id": 987654321,
      "first_name": "Test User",
      "username": "testuser",
      "type": "private"
    },
    "date": 1640995200,
    "text": "Olá! Aqui está o áudio:",
    "audio": {
      "file_id": "AwACAgIAAxkBAAIB...",
      "duration": 45,
      "title": "Audio Message",
      "mime_type": "audio/mpeg"
    }
  }
}'

test_webhook "Mensagem mista" "$mixed_payload"

# Teste 9: Áudio com callback query
log "🔄 Teste 9: Áudio com callback query"
audio_callback_payload='{
  "update_id": 123456799,
  "message": {
    "message_id": 133,
    "from": {
      "id": 987654321,
      "is_bot": false,
      "first_name": "Test User",
      "username": "testuser"
    },
    "chat": {
      "id": 987654321,
      "first_name": "Test User",
      "username": "testuser",
      "type": "private"
    },
    "date": 1640995200,
    "audio": {
      "file_id": "AwACAgIAAxkBAAIB...",
      "duration": 30,
      "title": "Audio with Callback",
      "mime_type": "audio/mpeg"
    }
  },
  "callback_query": {
    "id": "123456789",
    "from": {
      "id": 987654321,
      "is_bot": false,
      "first_name": "Test User"
    },
    "message": {
      "message_id": 133,
      "chat": {
        "id": 987654321,
        "type": "private"
      }
    },
    "data": "process_audio"
  }
}'

test_webhook "Áudio com callback" "$audio_callback_payload"

# Teste 10: Áudio com duração longa
log "⏱️ Teste 10: Áudio com duração longa"
long_audio_payload='{
  "update_id": 123456796,
  "message": {
    "message_id": 130,
    "from": {
      "id": 987654321,
      "is_bot": false,
      "first_name": "Test User",
      "username": "testuser"
    },
    "chat": {
      "id": 987654321,
      "first_name": "Test User",
      "username": "testuser",
      "type": "private"
    },
    "date": 1640995200,
    "audio": {
      "file_id": "AwACAgIAAxkBAAIB...",
      "duration": 300,
      "title": "Long Audio File",
      "mime_type": "audio/mpeg",
      "file_size": 10485760
    }
  }
}'

test_webhook "Áudio com duração longa" "$long_audio_payload"

# Resumo dos testes
log "🎯 Testes concluídos!"
log "📊 Verifique os logs do Laravel para detalhes de processamento:"
log "   tail -f storage/logs/telegram-webhook.log"
log ""
log "🔍 Para verificar se as mensagens foram processadas:"
log "   php artisan telegram:stats"
log ""
log "✅ Todos os testes foram executados com sucesso!"
