<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\User;
use App\Services\TelegramBotService;
use App\Services\TelegramWebhookService;
use App\Services\TelegramMessageProcessorService;
use App\Services\Channels\TelegramChannel;
use App\Contracts\LoggingServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class TelegramAudioMessageTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock dos serviços necessários
        $this->mock(TelegramBotService::class);
        $this->mock(TelegramWebhookService::class);
        $this->mock(TelegramMessageProcessorService::class);
        $this->mock(TelegramChannel::class);
        $this->mock(LoggingServiceInterface::class);
    }

    /**
     * Test sending voice message (audio de voz)
     */
    public function test_voice_message_webhook(): void
    {
        $voicePayload = [
            'update_id' => 123456789,
            'message' => [
                'message_id' => 123,
                'from' => [
                    'id' => 987654321,
                    'is_bot' => false,
                    'first_name' => 'Test User',
                    'username' => 'testuser',
                    'language_code' => 'pt'
                ],
                'chat' => [
                    'id' => 987654321,
                    'first_name' => 'Test User',
                    'username' => 'testuser',
                    'type' => 'private'
                ],
                'date' => time(),
                'voice' => [
                    'file_id' => 'AwACAgIAAxkBAAIB...',
                    'file_unique_id' => 'AgADBAADGQEAAl8AAQ',
                    'duration' => 15,
                    'mime_type' => 'audio/ogg'
                ]
            ]
        ];

        $response = $this->postJson('/api/telegram/webhook', $voicePayload);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'message',
            'data'
        ]);
    }

    /**
     * Test sending audio file message
     */
    public function test_audio_file_message_webhook(): void
    {
        $audioPayload = [
            'update_id' => 123456790,
            'message' => [
                'message_id' => 124,
                'from' => [
                    'id' => 987654321,
                    'is_bot' => false,
                    'first_name' => 'Test User',
                    'username' => 'testuser',
                    'language_code' => 'pt'
                ],
                'chat' => [
                    'id' => 987654321,
                    'first_name' => 'Test User',
                    'username' => 'testuser',
                    'type' => 'private'
                ],
                'date' => time(),
                'audio' => [
                    'file_id' => 'AwACAgIAAxkBAAIB...',
                    'file_unique_id' => 'AgADBAADGQEAAl8AAQ',
                    'duration' => 30,
                    'title' => 'Test Audio File',
                    'performer' => 'Test Performer',
                    'mime_type' => 'audio/mpeg',
                    'file_size' => 1024000
                ]
            ]
        ];

        $response = $this->postJson('/api/telegram/webhook', $audioPayload);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'message',
            'data'
        ]);
    }

    /**
     * Test sending voice message with missing required fields
     */
    public function test_voice_message_missing_fields(): void
    {
        $invalidVoicePayload = [
            'update_id' => 123456791,
            'message' => [
                'message_id' => 125,
                'from' => [
                    'id' => 987654321,
                    'first_name' => 'Test User'
                    // Faltando chat.id
                ],
                'voice' => [
                    'file_id' => 'AwACAgIAAxkBAAIB...',
                    'duration' => 10
                ]
            ]
        ];

        $response = $this->postJson('/api/telegram/webhook', $invalidVoicePayload);

        // Deve retornar erro de validação
        $response->assertStatus(422);
    }

    /**
     * Test sending audio message with missing required fields
     */
    public function test_audio_message_missing_fields(): void
    {
        $invalidAudioPayload = [
            'update_id' => 123456792,
            'message' => [
                'message_id' => 126,
                'chat' => [
                    'id' => 987654321,
                    'first_name' => 'Test User'
                    // Faltando from.id
                ],
                'audio' => [
                    'file_id' => 'AwACAgIAAxkBAAIB...',
                    'duration' => 20
                ]
            ]
        ];

        $response = $this->postJson('/api/telegram/webhook', $invalidAudioPayload);

        // Deve retornar erro de validação
        $response->assertStatus(422);
    }

    /**
     * Test sending voice message without webhook secret
     */
    public function test_voice_message_without_secret(): void
    {
        $voicePayload = [
            'update_id' => 123456793,
            'message' => [
                'message_id' => 127,
                'from' => [
                    'id' => 987654321,
                    'is_bot' => false,
                    'first_name' => 'Test User',
                    'username' => 'testuser'
                ],
                'chat' => [
                    'id' => 987654321,
                    'first_name' => 'Test User',
                    'username' => 'testuser',
                    'type' => 'private'
                ],
                'date' => time(),
                'voice' => [
                    'file_id' => 'AwACAgIAAxkBAAIB...',
                    'duration' => 12,
                    'mime_type' => 'audio/ogg'
                ]
            ]
        ];

        // Enviar sem o header de secret
        $response = $this->postJson('/api/telegram/webhook', $voicePayload);

        // Deve retornar 401 se o secret estiver configurado
        if (config('services.telegram.webhook_secret')) {
            $response->assertStatus(401);
        } else {
            $response->assertStatus(200);
        }
    }

    /**
     * Test sending audio message with invalid secret
     */
    public function test_audio_message_with_invalid_secret(): void
    {
        $audioPayload = [
            'update_id' => 123456794,
            'message' => [
                'message_id' => 128,
                'from' => [
                    'id' => 987654321,
                    'is_bot' => false,
                    'first_name' => 'Test User',
                    'username' => 'testuser'
                ],
                'chat' => [
                    'id' => 987654321,
                    'first_name' => 'Test User',
                    'username' => 'testuser',
                    'type' => 'private'
                ],
                'date' => time(),
                'audio' => [
                    'file_id' => 'AwACAgIAAxkBAAIB...',
                    'duration' => 25,
                    'title' => 'Test Audio',
                    'mime_type' => 'audio/mpeg'
                ]
            ]
        ];

        // Enviar com secret inválido
        $response = $this->withHeaders([
            'X-Telegram-Bot-Api-Secret-Token' => 'invalid_secret_123'
        ])->postJson('/api/telegram/webhook', $audioPayload);

        // Deve retornar 401 se o secret estiver configurado
        if (config('services.telegram.webhook_secret')) {
            $response->assertStatus(401);
        } else {
            $response->assertStatus(200);
        }
    }

    /**
     * Test sending voice message with valid secret
     */
    public function test_voice_message_with_valid_secret(): void
    {
        $voicePayload = [
            'update_id' => 123456795,
            'message' => [
                'message_id' => 129,
                'from' => [
                    'id' => 987654321,
                    'is_bot' => false,
                    'first_name' => 'Test User',
                    'username' => 'testuser'
                ],
                'chat' => [
                    'id' => 987654321,
                    'first_name' => 'Test User',
                    'username' => 'testuser',
                    'type' => 'private'
                ],
                'date' => time(),
                'voice' => [
                    'file_id' => 'AwACAgIAAxkBAAIB...',
                    'duration' => 18,
                    'mime_type' => 'audio/ogg'
                ]
            ]
        ];

        // Enviar com secret válido (se configurado)
        $headers = [];
        if (config('services.telegram.webhook_secret')) {
            $headers['X-Telegram-Bot-Api-Secret-Token'] = config('services.telegram.webhook_secret');
        }

        $response = $this->withHeaders($headers)
            ->postJson('/api/telegram/webhook', $voicePayload);

        $response->assertStatus(200);
    }

    /**
     * Test sending different audio formats
     */
    public function test_different_audio_formats(): void
    {
        $audioFormats = [
            'audio/ogg' => [
                'file_id' => 'AwACAgIAAxkBAAIB...',
                'duration' => 20,
                'mime_type' => 'audio/ogg'
            ],
            'audio/mpeg' => [
                'file_id' => 'AwACAgIAAxkBAAIB...',
                'duration' => 25,
                'title' => 'MP3 Audio',
                'mime_type' => 'audio/mpeg'
            ],
            'audio/wav' => [
                'file_id' => 'AwACAgIAAxkBAAIB...',
                'duration' => 15,
                'mime_type' => 'audio/wav'
            ]
        ];

        foreach ($audioFormats as $format => $audioData) {
            $payload = [
                'update_id' => time() + rand(1000, 9999),
                'message' => [
                    'message_id' => rand(100, 999),
                    'from' => [
                        'id' => 987654321,
                        'is_bot' => false,
                        'first_name' => 'Test User',
                        'username' => 'testuser'
                    ],
                    'chat' => [
                        'id' => 987654321,
                        'first_name' => 'Test User',
                        'username' => 'testuser',
                        'type' => 'private'
                    ],
                    'date' => time(),
                    'audio' => $audioData
                ]
            ];

            $response = $this->postJson('/api/telegram/webhook', $payload);

            // Verificar se a resposta é válida
            $this->assertContains($response->status(), [200, 422]);
        }
    }

    /**
     * Test sending voice message with long duration
     */
    public function test_voice_message_long_duration(): void
    {
        $longVoicePayload = [
            'update_id' => 123456796,
            'message' => [
                'message_id' => 130,
                'from' => [
                    'id' => 987654321,
                    'is_bot' => false,
                    'first_name' => 'Test User',
                    'username' => 'testuser'
                ],
                'chat' => [
                    'id' => 987654321,
                    'first_name' => 'Test User',
                    'username' => 'testuser',
                    'type' => 'private'
                ],
                'date' => time(),
                'voice' => [
                    'file_id' => 'AwACAgIAAxkBAAIB...',
                    'duration' => 300, // 5 minutos
                    'mime_type' => 'audio/ogg'
                ]
            ]
        ];

        $response = $this->postJson('/api/telegram/webhook', $longVoicePayload);

        $response->assertStatus(200);
    }

    /**
     * Test sending audio message with large file size
     */
    public function test_audio_message_large_file(): void
    {
        $largeAudioPayload = [
            'update_id' => 123456797,
            'message' => [
                'message_id' => 131,
                'from' => [
                    'id' => 987654321,
                    'is_bot' => false,
                    'first_name' => 'Test User',
                    'username' => 'testuser'
                ],
                'chat' => [
                    'id' => 987654321,
                    'first_name' => 'Test User',
                    'username' => 'testuser',
                    'type' => 'private'
                ],
                'date' => time(),
                'audio' => [
                    'file_id' => 'AwACAgIAAxkBAAIB...',
                    'duration' => 120,
                    'title' => 'Large Audio File',
                    'performer' => 'Test Performer',
                    'mime_type' => 'audio/mpeg',
                    'file_size' => 52428800 // 50MB
                ]
            ]
        ];

        $response = $this->postJson('/api/telegram/webhook', $largeAudioPayload);

        $response->assertStatus(200);
    }

    /**
     * Test sending mixed message types (text + audio)
     */
    public function test_mixed_message_types(): void
    {
        $mixedPayload = [
            'update_id' => 123456798,
            'message' => [
                'message_id' => 132,
                'from' => [
                    'id' => 987654321,
                    'is_bot' => false,
                    'first_name' => 'Test User',
                    'username' => 'testuser'
                ],
                'chat' => [
                    'id' => 987654321,
                    'first_name' => 'Test User',
                    'username' => 'testuser',
                    'type' => 'private'
                ],
                'date' => time(),
                'text' => 'Olá! Aqui está o áudio:',
                'audio' => [
                    'file_id' => 'AwACAgIAAxkBAAIB...',
                    'duration' => 45,
                    'title' => 'Audio Message',
                    'mime_type' => 'audio/mpeg'
                ]
            ]
        ];

        $response = $this->postJson('/api/telegram/webhook', $mixedPayload);

        $response->assertStatus(200);
    }

    /**
     * Test sending audio message with callback query
     */
    public function test_audio_message_with_callback(): void
    {
        $audioWithCallbackPayload = [
            'update_id' => 123456799,
            'message' => [
                'message_id' => 133,
                'from' => [
                    'id' => 987654321,
                    'is_bot' => false,
                    'first_name' => 'Test User',
                    'username' => 'testuser'
                ],
                'chat' => [
                    'id' => 987654321,
                    'first_name' => 'Test User',
                    'username' => 'testuser',
                    'type' => 'private'
                ],
                'date' => time(),
                'audio' => [
                    'file_id' => 'AwACAgIAAxkBAAIB...',
                    'duration' => 30,
                    'title' => 'Audio with Callback',
                    'mime_type' => 'audio/mpeg'
                ]
            ],
            'callback_query' => [
                'id' => '123456789',
                'from' => [
                    'id' => 987654321,
                    'is_bot' => false,
                    'first_name' => 'Test User'
                ],
                'message' => [
                    'message_id' => 133,
                    'chat' => [
                        'id' => 987654321,
                        'type' => 'private'
                    ]
                ],
                'data' => 'process_audio'
            ]
        ];

        $response = $this->postJson('/api/telegram/webhook', $audioWithCallbackPayload);

        $response->assertStatus(200);
    }
}
