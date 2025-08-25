<?php

namespace Tests\Unit\Services;

use App\Services\TelegramBotService;
use App\Services\Telegram\Commands\UnifiedCommandSystem;
use App\Services\Telegram\Commands\CommandResult;
use App\Services\Telegram\TelegramAuthorizationService;
use App\Services\Telegram\TelegramMenuBuilder;
use App\Contracts\LoggingServiceInterface;
use Mockery;
use PHPUnit\Framework\TestCase;

class TelegramBotServiceTest extends TestCase
{
    private TelegramBotService $service;
    private UnifiedCommandSystem $commandSystem;
    private TelegramAuthorizationService $authorizationService;
    private TelegramMenuBuilder $menuBuilder;
    private LoggingServiceInterface $loggingService;

    protected function setUp(): void
    {
        // Mock all dependencies manually to avoid service container resolution
        $this->commandSystem = Mockery::mock(UnifiedCommandSystem::class);
        $this->authorizationService = Mockery::mock(TelegramAuthorizationService::class);
        $this->menuBuilder = Mockery::mock(TelegramMenuBuilder::class);
        $this->loggingService = Mockery::mock(LoggingServiceInterface::class);

        // Create service instance manually with mocked dependencies
        $this->service = new TelegramBotService(
            $this->commandSystem,
            $this->authorizationService,
            $this->menuBuilder,
            $this->loggingService
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    /**
     * Test service instantiation
     */
    public function test_service_can_be_instantiated(): void
    {
        $this->assertInstanceOf(TelegramBotService::class, $this->service);
    }

    /**
     * Test processMessage with authorized user and successful command
     */
    public function test_process_message_with_authorized_user_and_successful_command(): void
    {
        $message = [
            'chat' => ['id' => 123],
            'from' => ['id' => 456],
            'text' => '/help',
            'date' => time()
        ];

        $commandResult = new CommandResult(
            success: true,
            message: 'Help command processed',
            data: ['help_text' => 'Available commands'],
            type: 'help_success'
        );

        $this->authorizationService
            ->shouldReceive('isAuthorizedUser')
            ->once()
            ->with(123)
            ->andReturn(true);

        $this->commandSystem
            ->shouldReceive('processCommand')
            ->once()
            ->with('/help', Mockery::type('array'))
            ->andReturn($commandResult);

        $result = $this->service->processMessage($message);

        $this->assertTrue($result['success']);
        $this->assertEquals(123, $result['chat_id']);
        $this->assertEquals('help_success', $result['type']);
        $this->assertEquals(['help_text' => 'Available commands'], $result['data']);
        $this->assertArrayHasKey('command_info', $result);
    }

    /**
     * Test processMessage with unauthorized user
     */
    public function test_process_message_with_unauthorized_user(): void
    {
        $message = [
            'chat' => ['id' => 123],
            'from' => ['id' => 456],
            'text' => '/help',
            'date' => time()
        ];

        $unauthorizedResponse = [
            'success' => false,
            'chat_id' => 123,
            'type' => 'unauthorized',
            'message' => 'Você não está autorizado'
        ];

        $this->authorizationService
            ->shouldReceive('isAuthorizedUser')
            ->once()
            ->with(123)
            ->andReturn(false);

        $this->menuBuilder
            ->shouldReceive('buildUnauthorizedMessage')
            ->once()
            ->with(123)
            ->andReturn($unauthorizedResponse);

        $result = $this->service->processMessage($message);

        $this->assertFalse($result['success']);
        $this->assertEquals('unauthorized', $result['type']);
        $this->assertEquals(123, $result['chat_id']);
    }

    /**
     * Test processMessage with command not found
     */
    public function test_process_message_with_command_not_found(): void
    {
        $message = [
            'chat' => ['id' => 123],
            'from' => ['id' => 456],
            'text' => 'unknown command',
            'date' => time()
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

        $this->authorizationService
            ->shouldReceive('isAuthorizedUser')
            ->once()
            ->with(123)
            ->andReturn(true);

        $this->commandSystem
            ->shouldReceive('processCommand')
            ->once()
            ->with('unknown command', Mockery::type('array'))
            ->andReturn($commandResult);

        $result = $this->service->processMessage($message);

        $this->assertFalse($result['success']);
        $this->assertEquals('command_not_found', $result['type']);
        $this->assertEquals(123, $result['chat_id']);
        $this->assertEquals('Comando não encontrado', $result['message']);
        $this->assertEquals(['/help', '/menu'], $result['suggestions']);
    }

    /**
     * Test processMessage with exception
     */
    public function test_process_message_with_exception(): void
    {
        $message = [
            'chat' => ['id' => 123],
            'from' => ['id' => 456],
            'text' => '/help',
            'date' => time()
        ];

        $this->authorizationService
            ->shouldReceive('isAuthorizedUser')
            ->once()
            ->with(123)
            ->andReturn(true);

        $this->commandSystem
            ->shouldReceive('processCommand')
            ->once()
            ->andThrow(new \Exception('Test exception'));

        $this->loggingService
            ->shouldReceive('logException')
            ->once()
            ->with(Mockery::type(\Exception::class), Mockery::type('array'));

        $result = $this->service->processMessage($message);

        $this->assertFalse($result['success']);
        $this->assertEquals('error', $result['type']);
        $this->assertEquals(123, $result['chat_id']);
        $this->assertEquals('Erro interno do sistema', $result['message']);
    }

    /**
     * Test processMessage with missing chat_id
     */
    public function test_process_message_with_missing_chat_id(): void
    {
        $message = [
            'from' => ['id' => 456],
            'text' => '/help',
            'date' => time()
        ];

        $this->authorizationService
            ->shouldReceive('isAuthorizedUser')
            ->once()
            ->with(null)
            ->andReturn(true);

        $commandResult = new CommandResult(
            success: true,
            message: 'Help command processed',
            data: ['help_text' => 'Available commands'],
            type: 'help_success'
        );

        $this->commandSystem
            ->shouldReceive('processCommand')
            ->once()
            ->andReturn($commandResult);

        $result = $this->service->processMessage($message);

        $this->assertTrue($result['success']);
        $this->assertNull($result['chat_id']);
    }

    /**
     * Test processMessage with missing user_id
     */
    public function test_process_message_with_missing_user_id(): void
    {
        $message = [
            'chat' => ['id' => 123],
            'text' => '/help',
            'date' => time()
        ];

        $this->authorizationService
            ->shouldReceive('isAuthorizedUser')
            ->once()
            ->with(123)
            ->andReturn(true);

        $commandResult = new CommandResult(
            success: true,
            message: 'Help command processed',
            data: ['help_text' => 'Available commands'],
            type: 'help_success'
        );

        $this->commandSystem
            ->shouldReceive('processCommand')
            ->once()
            ->andReturn($commandResult);

        $result = $this->service->processMessage($message);

        $this->assertTrue($result['success']);
        $this->assertEquals(123, $result['chat_id']);
    }

    /**
     * Test processCallbackQuery with authorized user and successful command
     */
    public function test_process_callback_query_with_authorized_user_and_successful_command(): void
    {
        $callbackQuery = [
            'id' => 'callback_123',
            'from' => ['id' => 456],
            'message' => [
                'chat' => ['id' => 123],
                'message_id' => 789
            ],
            'data' => 'menu_main',
            'date' => time()
        ];

        $commandResult = new CommandResult(
            success: true,
            message: 'Menu command processed',
            data: ['menu_items' => ['item1', 'item2']],
            type: 'menu_success'
        );

        $this->authorizationService
            ->shouldReceive('isAuthorizedUser')
            ->once()
            ->with(123)
            ->andReturn(true);

        $this->commandSystem
            ->shouldReceive('processCommand')
            ->once()
            ->with('menu_main', Mockery::type('array'))
            ->andReturn($commandResult);

        $result = $this->service->processCallbackQuery($callbackQuery);

        $this->assertTrue($result['success']);
        $this->assertEquals(123, $result['chat_id']);
        $this->assertEquals('menu_success', $result['type']);
        $this->assertEquals(['menu_items' => ['item1', 'item2']], $result['data']);
        $this->assertArrayHasKey('command_info', $result);
    }

    /**
     * Test processCallbackQuery with unauthorized user
     */
    public function test_process_callback_query_with_unauthorized_user(): void
    {
        $callbackQuery = [
            'id' => 'callback_123',
            'from' => ['id' => 456],
            'message' => [
                'chat' => ['id' => 123],
                'message_id' => 789
            ],
            'data' => 'menu_main',
            'date' => time()
        ];

        $unauthorizedResponse = [
            'success' => false,
            'chat_id' => 123,
            'type' => 'unauthorized',
            'message' => 'Você não está autorizado'
        ];

        $this->authorizationService
            ->shouldReceive('isAuthorizedUser')
            ->once()
            ->with(123)
            ->andReturn(false);

        $this->menuBuilder
            ->shouldReceive('buildUnauthorizedMessage')
            ->once()
            ->with(123)
            ->andReturn($unauthorizedResponse);

        $result = $this->service->processCallbackQuery($callbackQuery);

        $this->assertFalse($result['success']);
        $this->assertEquals('unauthorized', $result['type']);
        $this->assertEquals(123, $result['chat_id']);
    }

    /**
     * Test processCallbackQuery with command not found
     */
    public function test_process_callback_query_with_command_not_found(): void
    {
        $callbackQuery = [
            'id' => 'callback_123',
            'from' => ['id' => 456],
            'message' => [
                'chat' => ['id' => 123],
                'message_id' => 789
            ],
            'data' => 'unknown_action',
            'date' => time()
        ];

        $commandResult = new CommandResult(
            success: false,
            message: 'Action not found',
            data: [
                'fallback_message' => 'Ação não encontrada',
                'suggestions' => ['menu_main', 'menu_settings']
            ],
            type: 'callback_not_found'
        );

        $this->authorizationService
            ->shouldReceive('isAuthorizedUser')
            ->once()
            ->with(123)
            ->andReturn(true);

        $this->commandSystem
            ->shouldReceive('processCommand')
            ->once()
            ->with('unknown_action', Mockery::type('array'))
            ->andReturn($commandResult);

        $result = $this->service->processCallbackQuery($callbackQuery);

        $this->assertFalse($result['success']);
        $this->assertEquals('callback_not_found', $result['type']);
        $this->assertEquals(123, $result['chat_id']);
        $this->assertEquals('Ação não encontrada', $result['message']);
        $this->assertEquals(['menu_main', 'menu_settings'], $result['suggestions']);
    }

    /**
     * Test processCallbackQuery with exception
     */
    public function test_process_callback_query_with_exception(): void
    {
        $callbackQuery = [
            'id' => 'callback_123',
            'from' => ['id' => 456],
            'message' => [
                'chat' => ['id' => 123],
                'message_id' => 789
            ],
            'data' => 'menu_main',
            'date' => time()
        ];

        $this->authorizationService
            ->shouldReceive('isAuthorizedUser')
            ->once()
            ->with(123)
            ->andReturn(true);

        $this->commandSystem
            ->shouldReceive('processCommand')
            ->once()
            ->andThrow(new \Exception('Test exception'));

        $this->loggingService
            ->shouldReceive('logException')
            ->once()
            ->with(Mockery::type(\Exception::class), Mockery::type('array'));

        $result = $this->service->processCallbackQuery($callbackQuery);

        $this->assertFalse($result['success']);
        $this->assertEquals('error', $result['type']);
        $this->assertEquals(123, $result['chat_id']);
        $this->assertEquals('Erro interno do sistema', $result['message']);
    }

    /**
     * Test processCallbackQuery with missing message_id
     */
    public function test_process_callback_query_with_missing_message_id(): void
    {
        $callbackQuery = [
            'id' => 'callback_123',
            'from' => ['id' => 456],
            'message' => [
                'chat' => ['id' => 123]
            ],
            'data' => 'menu_main',
            'date' => time()
        ];

        $commandResult = new CommandResult(
            success: true,
            message: 'Menu command processed',
            data: ['menu_items' => ['item1', 'item2']],
            type: 'menu_success'
        );

        $this->authorizationService
            ->shouldReceive('isAuthorizedUser')
            ->once()
            ->with(123)
            ->andReturn(true);

        $this->commandSystem
            ->shouldReceive('processCommand')
            ->once()
            ->andReturn($commandResult);

        $result = $this->service->processCallbackQuery($callbackQuery);

        $this->assertTrue($result['success']);
        $this->assertEquals(123, $result['chat_id']);
    }

    /**
     * Test processCallbackQuery with empty callback data
     */
    public function test_process_callback_query_with_empty_callback_data(): void
    {
        $callbackQuery = [
            'id' => 'callback_123',
            'from' => ['id' => 456],
            'message' => [
                'chat' => ['id' => 123],
                'message_id' => 789
            ],
            'data' => '',
            'date' => time()
        ];

        $commandResult = new CommandResult(
            success: false,
            message: 'Empty callback data',
            data: [
                'fallback_message' => 'Ação não encontrada',
                'suggestions' => []
            ],
            type: 'callback_not_found'
        );

        $this->authorizationService
            ->shouldReceive('isAuthorizedUser')
            ->once()
            ->with(123)
            ->andReturn(true);

        $this->commandSystem
            ->shouldReceive('processCommand')
            ->once()
            ->with('', Mockery::type('array'))
            ->andReturn($commandResult);

        $result = $this->service->processCallbackQuery($callbackQuery);

        $this->assertFalse($result['success']);
        $this->assertEquals('callback_not_found', $result['type']);
        $this->assertEquals(123, $result['chat_id']);
    }

    /**
     * Test processMessage with command result without command match
     */
    public function test_process_message_with_command_result_without_command_match(): void
    {
        $message = [
            'chat' => ['id' => 123],
            'from' => ['id' => 456],
            'text' => '/help',
            'date' => time()
        ];

        $commandResult = new CommandResult(
            success: true,
            message: 'Help command processed',
            data: ['help_text' => 'Available commands'],
            type: 'help_success'
        );

        $this->authorizationService
            ->shouldReceive('isAuthorizedUser')
            ->once()
            ->with(123)
            ->andReturn(true);

        $this->commandSystem
            ->shouldReceive('processCommand')
            ->once()
            ->with('/help', Mockery::type('array'))
            ->andReturn($commandResult);

        $result = $this->service->processMessage($message);

        $this->assertTrue($result['success']);
        $this->assertEquals(123, $result['chat_id']);
        $this->assertNull($result['command_info']);
    }

    /**
     * Test processCallbackQuery with command result without command match
     */
    public function test_process_callback_query_with_command_result_without_command_match(): void
    {
        $callbackQuery = [
            'id' => 'callback_123',
            'from' => ['id' => 456],
            'message' => [
                'chat' => ['id' => 123],
                'message_id' => 789
            ],
            'data' => 'menu_main',
            'date' => time()
        ];

        $commandResult = new CommandResult(
            success: true,
            message: 'Menu command processed',
            data: ['menu_items' => ['item1', 'item2']],
            type: 'menu_success'
        );

        $this->authorizationService
            ->shouldReceive('isAuthorizedUser')
            ->once()
            ->with(123)
            ->andReturn(true);

        $this->commandSystem
            ->shouldReceive('processCommand')
            ->once()
            ->with('menu_main', Mockery::type('array'))
            ->andReturn($commandResult);

        $result = $this->service->processCallbackQuery($callbackQuery);

        $this->assertTrue($result['success']);
        $this->assertEquals(123, $result['chat_id']);
        $this->assertNull($result['command_info']);
    }

    /**
     * Test service has all required methods
     */
    public function test_service_has_required_methods(): void
    {
        $this->assertTrue(method_exists($this->service, 'processMessage'));
        $this->assertTrue(method_exists($this->service, 'processCallbackQuery'));
        $this->assertTrue(method_exists($this->service, 'getAvailableCommands'));
        $this->assertTrue(method_exists($this->service, 'getAvailableReports'));
    }

    /**
     * Test context structure for processMessage
     */
    public function test_context_structure_for_process_message(): void
    {
        $message = [
            'chat' => ['id' => 123],
            'from' => ['id' => 456],
            'text' => '/help',
            'date' => time()
        ];

        $this->authorizationService
            ->shouldReceive('isAuthorizedUser')
            ->once()
            ->with(123)
            ->andReturn(true);

        $this->commandSystem
            ->shouldReceive('processCommand')
            ->once()
            ->with('/help', Mockery::on(function ($context) {
                return $context['chat_id'] === 123 &&
                       $context['user_id'] === 456 &&
                       $context['type'] === 'text' &&
                       $context['user_permissions'] === ['user'] &&
                       isset($context['timestamp']);
            }))
            ->andReturn(new CommandResult(
                success: true,
                message: 'Success',
                data: [],
                type: 'success'
            ));

        $this->service->processMessage($message);
    }

    /**
     * Test context structure for processCallbackQuery
     */
    public function test_context_structure_for_process_callback_query(): void
    {
        $callbackQuery = [
            'id' => 'callback_123',
            'from' => ['id' => 456],
            'message' => [
                'chat' => ['id' => 123],
                'message_id' => 789
            ],
            'data' => 'menu_main',
            'date' => time()
        ];

        $this->authorizationService
            ->shouldReceive('isAuthorizedUser')
            ->once()
            ->with(123)
            ->andReturn(true);

        $this->commandSystem
            ->shouldReceive('processCommand')
            ->once()
            ->with('menu_main', Mockery::on(function ($context) {
                return $context['chat_id'] === 123 &&
                       $context['user_id'] === 456 &&
                       $context['type'] === 'callback_query' &&
                       $context['user_permissions'] === ['user'] &&
                       $context['callback_data'] === 'menu_main' &&
                       isset($context['timestamp']);
            }))
            ->andReturn(new CommandResult(
                success: true,
                message: 'Success',
                data: [],
                type: 'success'
            ));

        $this->service->processCallbackQuery($callbackQuery);
    }

    /**
     * Test logging exception details for processMessage
     */
    public function test_logging_exception_details_for_process_message(): void
    {
        $message = [
            'chat' => ['id' => 123],
            'from' => ['id' => 456],
            'text' => '/help',
            'date' => time()
        ];

        $this->authorizationService
            ->shouldReceive('isAuthorizedUser')
            ->once()
            ->with(123)
            ->andReturn(true);

        $this->commandSystem
            ->shouldReceive('processCommand')
            ->once()
            ->andThrow(new \Exception('Test exception'));

        $this->loggingService
            ->shouldReceive('logException')
            ->once()
            ->with(Mockery::type(\Exception::class), Mockery::on(function ($context) {
                return $context['operation'] === 'telegram_message_processing' &&
                       $context['chat_id'] === 123 &&
                       $context['user_id'] === 456 &&
                       $context['message'] === $this->getMessage();
            }));

        $this->service->processMessage($message);
    }

    /**
     * Test logging exception details for processCallbackQuery
     */
    public function test_logging_exception_details_for_process_callback_query(): void
    {
        $callbackQuery = [
            'id' => 'callback_123',
            'from' => ['id' => 456],
            'message' => [
                'chat' => ['id' => 123],
                'message_id' => 789
            ],
            'data' => 'menu_main',
            'date' => time()
        ];

        $this->authorizationService
            ->shouldReceive('isAuthorizedUser')
            ->once()
            ->with(123)
            ->andReturn(true);

        $this->commandSystem
            ->shouldReceive('processCommand')
            ->once()
            ->andThrow(new \Exception('Test exception'));

        $this->loggingService
            ->shouldReceive('logException')
            ->once()
            ->with(Mockery::type(\Exception::class), Mockery::on(function ($context) {
                return $context['operation'] === 'telegram_callback_processing' &&
                       $context['chat_id'] === 123 &&
                       $context['user_id'] === 456 &&
                       $context['callback_query'] === $this->getCallbackQuery();
            }));

        $this->service->processCallbackQuery($callbackQuery);
    }

    /**
     * Helper method to get message for comparison
     */
    private function getMessage(): array
    {
        return [
            'chat' => ['id' => 123],
            'from' => ['id' => 456],
            'text' => '/help',
            'date' => time()
        ];
    }

    /**
     * Helper method to get callback query for comparison
     */
    private function getCallbackQuery(): array
    {
        return [
            'id' => 'callback_123',
            'from' => ['id' => 456],
            'message' => [
                'chat' => ['id' => 123],
                'message_id' => 789
            ],
            'data' => 'menu_main',
            'date' => time()
        ];
    }
}
