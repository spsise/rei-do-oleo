#!/bin/bash

# 🧪 Teste Simples de Mensagem de Áudio
# Script rápido para testar webhook com mensagem de áudio

WEBHOOK_URL="http://localhost:8000/api/telegram/webhook"

echo "🎤 Testando webhook com mensagem de áudio..."

# Payload de mensagem de voz
voice_payload='{
  "update_id": 123456789,
  "message": {
    "message_id": 123,
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
    "voice": {
      "file_id": "AwACAgIAAxkBAAIB...",
      "duration": 15,
      "mime_type": "audio/ogg"
    }
  }
}'

echo "📤 Enviando mensagem de voz..."
response=$(curl -s -X POST "$WEBHOOK_URL" \
  -H "Content-Type: application/json" \
  -d "$voice_payload")

echo "📥 Resposta recebida:"
echo "$response" | jq '.' 2>/dev/null || echo "$response"

echo ""
echo "🎯 Teste concluído!"
echo "📊 Verifique os logs: tail -f storage/logs/telegram-webhook.log"
