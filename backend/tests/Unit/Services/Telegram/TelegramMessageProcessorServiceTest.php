<?php

namespace Tests\Unit\Services\Telegram;

use App\Services\Telegram\TelegramMessageProcessorService;
use App\Services\Telegram\Commands\UnifiedCommandSystem;
use App\Services\Telegram\Commands\CommandResult;
use App\Services\Telegram\TelegramAuthorizationService;
use App\Services\Channels\TelegramChannel;
use App\Services\SpeechToTextService;
use App\Contracts\LoggingServiceInterface;
use Mockery;
use Tests\TestCase;
use ReflectionClass;
use ReflectionMethod;

class TelegramMessageProcessorServiceTest extends TestCase
{
    private TelegramMessageProcessorService $service;
    private UnifiedCommandSystem $commandSystem;
    private TelegramAuthorizationService $authorizationService;
    private SpeechToTextService $speechService;
    private TelegramChannel $telegramChannel;
    private LoggingServiceInterface $loggingService;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock all dependencies
        $this->commandSystem = Mockery::mock(UnifiedCommandSystem::class);
        $this->authorizationService = Mockery::mock(TelegramAuthorizationService::class);
        $this->speechService = Mockery::mock(SpeechToTextService::class);
        $this->telegramChannel = Mockery::mock(TelegramChannel::class);
        $this->loggingService = Mockery::mock(LoggingServiceInterface::class);

        $this->service = new TelegramMessageProcessorService(
            $this->commandSystem,
            $this->authorizationService,
            $this->speechService,
            $this->telegramChannel,
            $this->loggingService
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Test service instantiation
     */
    public function test_service_can_be_instantiated(): void
    {
        $this->assertInstanceOf(TelegramMessageProcessorService::class, $this->service);
    }

    /**
     * Test service constructor with optional parameters
     */
    public function test_service_can_be_instantiated_without_optional_parameters(): void
    {
        $service = new TelegramMessageProcessorService(
            $this->commandSystem,
            $this->authorizationService
        );

        $this->assertInstanceOf(TelegramMessageProcessorService::class, $service);
    }

    /**
     * Test processMessage with callback query
     */
    public function test_process_message_with_callback_query(): void
    {
        $payload = [
            'callback_query' => [
                'id' => '123',
                'from' => ['id' => 456],
                'message' => ['chat' => ['id' => 789]],
                'data' => 'test_callback',
                'date' => time()
            ]
        ];

        $commandResult = new CommandResult(
            success: true,
            message: 'Callback processed',
            data: ['result' => 'success'],
            type: 'callback_success'
        );

        $this->commandSystem
            ->shouldReceive('processCommand')
            ->once()
            ->with('test_callback', Mockery::type('array'))
            ->andReturn($commandResult);

        $result = $this->service->processMessage($payload);

        $this->assertTrue($result['success']);
        $this->assertEquals(789, $result['chat_id']);
        $this->assertEquals('callback_success', $result['type']);
    }

    /**
     * Test processMessage with text message
     */
    public function test_process_message_with_text_message(): void
    {
        $payload = [
            'message' => [
                'chat' => ['id' => 123],
                'from' => ['id' => 456],
                'text' => 'Hello world',
                'date' => time()
            ]
        ];

        $commandResult = new CommandResult(
            success: true,
            message: 'Text processed',
            data: ['result' => 'success'],
            type: 'text_success'
        );

        $this->commandSystem
            ->shouldReceive('processCommand')
            ->once()
            ->with('Hello world', Mockery::type('array'))
            ->andReturn($commandResult);

        $result = $this->service->processMessage($payload);

        $this->assertTrue($result['success']);
        $this->assertEquals(123, $result['chat_id']);
        $this->assertEquals('text_success', $result['type']);
    }

    /**
     * Test processMessage with unsupported message type
     */
    public function test_process_message_with_unsupported_type(): void
    {
        $payload = [
            'message' => [
                'chat' => ['id' => 123],
                'from' => ['id' => 456],
                'document' => ['file_id' => 'doc_123'],
                'date' => time()
            ]
        ];

        $result = $this->service->processMessage($payload);

        $this->assertFalse($result['success']);
        $this->assertEquals('ignored', $result['status']);
        $this->assertEquals('Unsupported message type', $result['message']);
    }

    /**
     * Test processMessage with no message in payload
     */
    public function test_process_message_with_no_message(): void
    {
        $payload = ['update_id' => 123];

        $result = $this->service->processMessage($payload);

        $this->assertFalse($result['success']);
        $this->assertEquals('ignored', $result['status']);
        $this->assertEquals('No message in payload', $result['message']);
    }

    /**
     * Test processMessage with empty text message
     */
    public function test_process_message_with_empty_text(): void
    {
        $payload = [
            'message' => [
                'chat' => ['id' => 123],
                'from' => ['id' => 456],
                'text' => '',
                'date' => time()
            ]
        ];

        $result = $this->service->processMessage($payload);

        $this->assertFalse($result['success']);
        $this->assertEquals('empty_message', $result['type']);
        $this->assertEquals(123, $result['chat_id']);
    }

    /**
     * Test processMessage with command not found
     */
    public function test_process_message_with_command_not_found(): void
    {
        $payload = [
            'message' => [
                'chat' => ['id' => 123],
                'from' => ['id' => 456],
                'text' => 'unknown command',
                'date' => time()
            ]
        ];

        $commandResult = new CommandResult(
            success: false,
            message: 'Command not found',
            data: [
                'fallback_message' => 'Comando não encontrado',
                'suggestions' => ['/help', '/menu']
            ],
            type: 'command_not_found'
        );

        $this->commandSystem
            ->shouldReceive('processCommand')
            ->once()
            ->andReturn($commandResult);

        $this->telegramChannel
            ->shouldReceive('sendTextMessage')
            ->once()
            ->with('Comando não encontrado', '123');

        $result = $this->service->processMessage($payload);

        $this->assertFalse($result['success']);
        $this->assertEquals('command_not_found', $result['type']);
        $this->assertEquals(123, $result['chat_id']);
    }

    /**
     * Test processMessage with empty callback data
     */
    public function test_process_message_with_empty_callback_data(): void
    {
        $payload = [
            'callback_query' => [
                'id' => '123',
                'from' => ['id' => 456],
                'message' => ['chat' => ['id' => 789]],
                'data' => '',
                'date' => time()
            ]
        ];

        $result = $this->service->processMessage($payload);

        $this->assertFalse($result['success']);
        $this->assertEquals('empty_callback', $result['type']);
        $this->assertEquals(789, $result['chat_id']);
    }

    /**
     * Test processMessage with callback error
     */
    public function test_process_message_with_callback_error(): void
    {
        $payload = [
            'callback_query' => [
                'id' => '123',
                'from' => ['id' => 456],
                'message' => ['chat' => ['id' => 789]],
                'data' => 'test_callback',
                'date' => time()
            ]
        ];

        $commandResult = new CommandResult(
            success: false,
            message: 'Callback error',
            data: ['error' => 'Something went wrong'],
            type: 'callback_error'
        );

        $this->commandSystem
            ->shouldReceive('processCommand')
            ->once()
            ->andReturn($commandResult);

        $result = $this->service->processMessage($payload);

        $this->assertFalse($result['success']);
        $this->assertEquals('callback_error', $result['type']);
        $this->assertEquals(789, $result['chat_id']);
    }

    /**
     * Test processMessage with general exception
     */
    public function test_process_message_with_general_exception(): void
    {
        $payload = [
            'message' => [
                'chat' => ['id' => 123],
                'from' => ['id' => 456],
                'text' => 'test',
                'date' => time()
            ]
        ];

        $this->commandSystem
            ->shouldReceive('processCommand')
            ->once()
            ->andThrow(new \Exception('Test exception'));

        $this->loggingService
            ->shouldReceive('logException')
            ->once()
            ->with(Mockery::type(\Exception::class), Mockery::type('array'));

        $result = $this->service->processMessage($payload);

        $this->assertFalse($result['success']);
        $this->assertEquals('error', $result['status']);
        $this->assertEquals('Internal server error', $result['message']);
        $this->assertEquals('Test exception', $result['error']);
    }

    /**
     * Test processMessage without speech service
     */
    public function test_process_message_without_speech_service(): void
    {
        $service = new TelegramMessageProcessorService(
            $this->commandSystem,
            $this->authorizationService
        );

        $payload = [
            'message' => [
                'chat' => ['id' => 123],
                'from' => ['id' => 456],
                'voice' => [
                    'file_id' => 'voice_file_123',
                    'duration' => 10
                ],
                'date' => time()
            ]
        ];

        $result = $service->processMessage($payload);

        $this->assertFalse($result['success']);
        $this->assertEquals('voice_conversion_error', $result['type']);
        $this->assertEquals(123, $result['chat_id']);
    }

    /**
     * Test processMessage without telegram channel
     */
    public function test_process_message_without_telegram_channel(): void
    {
        $service = new TelegramMessageProcessorService(
            $this->commandSystem,
            $this->authorizationService,
            $this->speechService
        );

        $payload = [
            'message' => [
                'chat' => ['id' => 123],
                'from' => ['id' => 456],
                'voice' => [
                    'file_id' => 'voice_file_123',
                    'duration' => 10
                ],
                'date' => time()
            ]
        ];

        $result = $service->processMessage($payload);

        $this->assertFalse($result['success']);
        $this->assertEquals('voice_conversion_error', $result['type']);
        $this->assertEquals(123, $result['chat_id']);
    }

    /**
     * Test processMessage without logging service
     */
    public function test_process_message_without_logging_service(): void
    {
        $service = new TelegramMessageProcessorService(
            $this->commandSystem,
            $this->authorizationService,
            $this->speechService,
            $this->telegramChannel
        );

        $payload = [
            'message' => [
                'chat' => ['id' => 123],
                'from' => ['id' => 456],
                'text' => 'test',
                'date' => time()
            ]
        ];

        $commandResult = new CommandResult(
            success: true,
            message: 'Success',
            data: ['result' => 'ok'],
            type: 'success'
        );

        $this->commandSystem
            ->shouldReceive('processCommand')
            ->once()
            ->andReturn($commandResult);

        $result = $service->processMessage($payload);

        $this->assertTrue($result['success']);
        $this->assertEquals(123, $result['chat_id']);
    }

    /**
     * Test context extraction from message
     */
    public function test_get_context_from_message(): void
    {
        $message = [
            'chat' => ['id' => 123],
            'from' => ['id' => 456],
            'text' => 'test',
            'date' => 1234567890
        ];

        $reflection = new ReflectionClass($this->service);
        $method = $reflection->getMethod('getContextFromMessage');
        $method->setAccessible(true);

        $result = $method->invoke($this->service, $message);

        $this->assertEquals(123, $result['chat_id']);
        $this->assertEquals(456, $result['user_id']);
        $this->assertEquals('text', $result['type']);
        $this->assertEquals(1234567890, $result['timestamp']);
        $this->assertIsArray($result['user_permissions']);
    }

    /**
     * Test context extraction from callback query
     */
    public function test_get_context_from_callback_query(): void
    {
        $callbackQuery = [
            'from' => ['id' => 456],
            'message' => ['chat' => ['id' => 123]],
            'data' => 'test',
            'date' => 1234567890
        ];

        $reflection = new ReflectionClass($this->service);
        $method = $reflection->getMethod('getContextFromCallbackQuery');
        $method->setAccessible(true);

        $result = $method->invoke($this->service, $callbackQuery);

        $this->assertEquals(123, $result['chat_id']);
        $this->assertEquals(456, $result['user_id']);
        $this->assertEquals('callback_query', $result['type']);
        $this->assertEquals(1234567890, $result['timestamp']);
        $this->assertIsArray($result['user_permissions']);
    }

    /**
     * Test message type determination
     */
    public function test_determine_message_type(): void
    {
        $reflection = new ReflectionClass($this->service);
        $method = $reflection->getMethod('determineMessageType');
        $method->setAccessible(true);

        $textMessage = ['text' => 'hello'];
        $voiceMessage = ['voice' => ['file_id' => '123']];
        $audioMessage = ['audio' => ['file_id' => '123']];
        $callbackMessage = ['callback_query' => ['data' => 'test']];
        $unknownMessage = ['document' => ['file_id' => '123']];

        $this->assertEquals('text', $method->invoke($this->service, $textMessage));
        $this->assertEquals('voice', $method->invoke($this->service, $voiceMessage));
        $this->assertEquals('audio', $method->invoke($this->service, $audioMessage));
        $this->assertEquals('callback_query', $method->invoke($this->service, $callbackMessage));
        $this->assertEquals('unknown', $method->invoke($this->service, $unknownMessage));
    }

    /**
     * Test user permissions extraction
     */
    public function test_get_user_permissions(): void
    {
        $reflection = new ReflectionClass($this->service);
        $method = $reflection->getMethod('getUserPermissions');
        $method->setAccessible(true);

        $messageWithUser = ['from' => ['id' => 123]];
        $messageWithoutUser = ['chat' => ['id' => 456]];

        $result1 = $method->invoke($this->service, $messageWithUser);
        $result2 = $method->invoke($this->service, $messageWithoutUser);

        $this->assertIsArray($result1);
        $this->assertIsArray($result2);
        $this->assertEquals(['user'], $result1);
        $this->assertEquals(['user'], $result2);
    }

    /**
     * Test success response creation
     */
    public function test_create_success_response(): void
    {
        $commandResult = new CommandResult(
            success: true,
            message: 'Success',
            data: ['result' => 'ok'],
            type: 'success'
        );

        $reflection = new ReflectionClass($this->service);
        $method = $reflection->getMethod('createSuccessResponse');
        $method->setAccessible(true);

        $result = $method->invoke($this->service, 123, $commandResult);

        $this->assertTrue($result['success']);
        $this->assertEquals(123, $result['chat_id']);
        $this->assertEquals('success', $result['type']);
        $this->assertEquals(['result' => 'ok'], $result['data']);
    }

    /**
     * Test command not found response creation
     */
    public function test_create_command_not_found_response(): void
    {
        $commandResult = new CommandResult(
            success: false,
            message: 'Command not found',
            data: [
                'fallback_message' => 'Comando não encontrado',
                'suggestions' => ['/help']
            ],
            type: 'command_not_found'
        );

        $this->telegramChannel
            ->shouldReceive('sendTextMessage')
            ->once()
            ->with('Comando não encontrado', '123');

        $reflection = new ReflectionClass($this->service);
        $method = $reflection->getMethod('createCommandNotFoundResponse');
        $method->setAccessible(true);

        $result = $method->invoke($this->service, 123, $commandResult);

        $this->assertFalse($result['success']);
        $this->assertEquals('command_not_found', $result['type']);
        $this->assertEquals(123, $result['chat_id']);
        $this->assertEquals('Comando não encontrado', $result['message']);
    }

    /**
     * Test ignored result creation
     */
    public function test_create_ignored_result(): void
    {
        $reflection = new ReflectionClass($this->service);
        $method = $reflection->getMethod('createIgnoredResult');
        $method->setAccessible(true);

        $result = $method->invoke($this->service, 'Test ignored message');

        $this->assertFalse($result['success']);
        $this->assertEquals('ignored', $result['status']);
        $this->assertEquals('Test ignored message', $result['message']);
    }

    /**
     * Test error response creation
     */
    public function test_create_error_response(): void
    {
        $reflection = new ReflectionClass($this->service);
        $method = $reflection->getMethod('createErrorResponse');
        $method->setAccessible(true);

        $result = $method->invoke($this->service, 123, 'Test error');

        $this->assertFalse($result['success']);
        $this->assertEquals('error', $result['type']);
        $this->assertEquals(123, $result['chat_id']);
        $this->assertEquals('Erro interno do sistema: Test error', $result['message']);
    }

    /**
     * Test voice conversion error response creation
     */
    public function test_create_voice_conversion_error_response(): void
    {
        $reflection = new ReflectionClass($this->service);
        $method = $reflection->getMethod('createVoiceConversionErrorResponse');
        $method->setAccessible(true);

        $result = $method->invoke($this->service, 123);

        $this->assertFalse($result['success']);
        $this->assertEquals('voice_conversion_error', $result['type']);
        $this->assertEquals(123, $result['chat_id']);
        $this->assertEquals('Erro ao converter mensagem de voz.', $result['message']);
    }

    /**
     * Test audio conversion error response creation
     */
    public function test_create_audio_conversion_error_response(): void
    {
        $reflection = new ReflectionClass($this->service);
        $method = $reflection->getMethod('createAudioConversionErrorResponse');
        $method->setAccessible(true);

        $result = $method->invoke($this->service, 123);

        $this->assertFalse($result['success']);
        $this->assertEquals('audio_conversion_error', $result['type']);
        $this->assertEquals(123, $result['chat_id']);
        $this->assertEquals('Erro ao converter mensagem de áudio.', $result['message']);
    }

    /**
     * Test empty message response creation
     */
    public function test_create_empty_message_response(): void
    {
        $reflection = new ReflectionClass($this->service);
        $method = $reflection->getMethod('createEmptyMessageResponse');
        $method->setAccessible(true);

        $result = $method->invoke($this->service, 123);

        $this->assertFalse($result['success']);
        $this->assertEquals('empty_message', $result['type']);
        $this->assertEquals(123, $result['chat_id']);
        $this->assertEquals('Mensagem vazia recebida.', $result['message']);
    }

    /**
     * Test empty callback response creation
     */
    public function test_create_empty_callback_response(): void
    {
        $reflection = new ReflectionClass($this->service);
        $method = $reflection->getMethod('createEmptyCallbackResponse');
        $method->setAccessible(true);

        $result = $method->invoke($this->service, 123);

        $this->assertFalse($result['success']);
        $this->assertEquals('empty_callback', $result['type']);
        $this->assertEquals(123, $result['chat_id']);
        $this->assertEquals('Dados de callback vazios.', $result['message']);
    }

    /**
     * Test callback error response creation
     */
    public function test_create_callback_error_response(): void
    {
        $commandResult = new CommandResult(
            success: false,
            message: 'Callback error',
            data: ['error' => 'Something went wrong'],
            type: 'callback_error'
        );

        $reflection = new ReflectionClass($this->service);
        $method = $reflection->getMethod('createCallbackErrorResponse');
        $method->setAccessible(true);

        $result = $method->invoke($this->service, 123, $commandResult);

        $this->assertFalse($result['success']);
        $this->assertEquals('callback_error', $result['type']);
        $this->assertEquals(123, $result['chat_id']);
        $this->assertEquals('Erro ao processar callback.', $result['message']);
    }

    /**
     * Test unauthorized response creation
     */
    public function test_create_unauthorized_response(): void
    {
        $reflection = new ReflectionClass($this->service);
        $method = $reflection->getMethod('createUnauthorizedResponse');
        $method->setAccessible(true);

        $result = $method->invoke($this->service, 123);

        $this->assertFalse($result['success']);
        $this->assertEquals('unauthorized', $result['type']);
        $this->assertEquals(123, $result['chat_id']);
        $this->assertEquals('Você não está autorizado a usar este bot.', $result['message']);
    }

    /**
     * Test unsupported message response creation
     */
    public function test_create_unsupported_message_response(): void
    {
        $reflection = new ReflectionClass($this->service);
        $method = $reflection->getMethod('createUnsupportedMessageResponse');
        $method->setAccessible(true);

        $result = $method->invoke($this->service, 123);

        $this->assertFalse($result['success']);
        $this->assertEquals('unsupported', $result['type']);
        $this->assertEquals(123, $result['chat_id']);
        $this->assertEquals('Tipo de mensagem não suportado.', $result['message']);
    }

    /**
     * Test service has all required methods
     */
    public function test_service_has_required_methods(): void
    {
        $this->assertTrue(method_exists($this->service, 'processMessage'));

        // Test private methods exist through reflection
        $reflection = new ReflectionClass($this->service);

        $expectedMethods = [
            'processTextMessage',
            'processVoiceMessage',
            'processAudioMessage',
            'processCallbackQuery',
            'getContextFromMessage',
            'getContextFromCallbackQuery',
            'determineMessageType',
            'getUserPermissions',
            'downloadVoiceFile',
            'downloadAudioFile',
            'createSuccessResponse',
            'createCommandNotFoundResponse',
            'createUnauthorizedResponse',
            'createUnsupportedMessageResponse',
            'createEmptyMessageResponse',
            'createVoiceConversionErrorResponse',
            'createEmptyCallbackResponse',
            'createCallbackErrorResponse',
            'createErrorResponse',
            'createAudioConversionErrorResponse',
            'createIgnoredResult'
        ];

        foreach ($expectedMethods as $methodName) {
            $this->assertTrue(
                $reflection->hasMethod($methodName),
                "Method {$methodName} should exist"
            );
        }
    }
}
