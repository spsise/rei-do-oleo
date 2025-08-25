<?php

namespace Tests\Unit\Services\Telegram;

use App\Services\Telegram\Commands\UnifiedCommandSystem;
use App\Services\Telegram\Commands\CommandRegistry;
use App\Services\Telegram\Commands\Cache\CommandCache;
use App\Services\Telegram\Commands\Learning\CommandLearning;
use App\Services\Telegram\Commands\Repositories\CommandConfigRepository;
use App\Contracts\Telegram\Commands\CommandMatch;
use App\Contracts\Telegram\Commands\CommandInterface;
use App\Models\Telegram\TelegramCommand;
use Mockery;
use PHPUnit\Framework\TestCase;
use Exception;

class UnifiedCommandSystemTest extends TestCase
{
    private UnifiedCommandSystem $unifiedCommandSystem;
    private CommandRegistry $mockRegistry;
    private CommandCache $mockCache;
    private CommandLearning $mockLearning;
    private CommandConfigRepository $mockConfigRepo;
    private CommandMatch $mockCommandMatch;
    private CommandInterface $mockCommand;

    protected function setUp(): void
    {
        parent::setUp();

        // Create mocks for all dependencies
        $this->mockRegistry = Mockery::mock(CommandRegistry::class);
        $this->mockCache = Mockery::mock(CommandCache::class);
        $this->mockLearning = Mockery::mock(CommandLearning::class);
        $this->mockConfigRepo = Mockery::mock(CommandConfigRepository::class);
        $this->mockCommand = Mockery::mock(CommandInterface::class);
        $this->mockCommandMatch = Mockery::mock(CommandMatch::class);

        // Setup mock command with basic methods
        $this->mockCommand->shouldReceive('getId')->andReturn('test_command');
        $this->mockCommand->shouldReceive('getAction')->andReturn([
            'handler' => 'TestHandler',
            'method' => 'handle',
            'parameters' => []
        ]);

        // Setup mock command match
        $this->mockCommandMatch->shouldReceive('getCommand')->andReturn($this->mockCommand);
        $this->mockCommandMatch->shouldReceive('getConfidence')->andReturn(0.95);
        $this->mockCommandMatch->shouldReceive('getMatchedInput')->andReturn('test input');

        // Create the service instance
        $this->unifiedCommandSystem = new UnifiedCommandSystem(
            $this->mockRegistry,
            $this->mockCache,
            $this->mockLearning,
            $this->mockConfigRepo
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
        $this->assertInstanceOf(UnifiedCommandSystem::class, $this->unifiedCommandSystem);
    }

    /**
     * Test processCommand with cache hit
     */
    public function test_process_command_with_cache_hit(): void
    {
        $input = 'test command';
        $context = ['user_id' => 1];

        // Mock cache returning a result
        $this->mockCache
            ->shouldReceive('get')
            ->once()
            ->with($input, $context)
            ->andReturn($this->mockCommandMatch);

        // Mock learning recordHit
        $this->mockLearning
            ->shouldReceive('recordHit')
            ->once()
            ->with($input, $this->mockCommandMatch);

        $result = $this->unifiedCommandSystem->processCommand($input, $context);

        $this->assertTrue($result->isSuccess());
        $this->assertEquals('cache_hit', $result->getType());
        $this->assertEquals($this->mockCommandMatch, $result->getCommandMatch());
    }

    /**
     * Test processCommand with command not found
     */
    public function test_process_command_with_command_not_found(): void
    {
        $input = 'unknown command';
        $context = ['user_id' => 1];

        // Mock cache returning null
        $this->mockCache
            ->shouldReceive('get')
            ->once()
            ->with($input, $context)
            ->andReturn(null);

        // Mock registry not finding a command
        $this->mockRegistry
            ->shouldReceive('findCommand')
            ->once()
            ->with($input, $context)
            ->andReturn(null);

        // Mock registry searchCommands for suggestions
        $this->mockRegistry
            ->shouldReceive('searchCommands')
            ->once()
            ->with($input)
            ->andReturn(['suggestion1', 'suggestion2']);

        $result = $this->unifiedCommandSystem->processCommand($input, $context);

        $this->assertFalse($result->isSuccess());
        $this->assertEquals('command_not_found', $result->getType());
        $this->assertArrayHasKey('suggestions', $result->getData());
        $this->assertArrayHasKey('fallback_message', $result->getData());
    }

    /**
     * Test addCommand successfully
     */
    public function test_add_command_successfully(): void
    {
        $commandConfig = [
            'command_id' => 'new_command',
            'aliases' => ['new', 'nc'],
            'description' => 'New test command'
        ];

        $mockTelegramCommand = Mockery::mock(TelegramCommand::class);
        $mockTelegramCommand->shouldReceive('getId')->andReturn('new_command');
        $mockTelegramCommand->shouldReceive('getAliases')->andReturn(['new', 'nc']);

        // Mock configRepo adding command
        $this->mockConfigRepo
            ->shouldReceive('addCommand')
            ->once()
            ->with($commandConfig)
            ->andReturn($mockTelegramCommand);

        // Mock registry reload
        $this->mockRegistry
            ->shouldReceive('reloadCommands')
            ->once();

        // Mock cache clear
        $this->mockCache
            ->shouldReceive('clear')
            ->once();

        $result = $this->unifiedCommandSystem->addCommand($commandConfig);

        $this->assertTrue($result->isSuccess());
        $this->assertEquals('command_added', $result->getType());
        $this->assertEquals('new_command', $result->getData()['command_id']);
    }

    /**
     * Test updateCommand successfully
     */
    public function test_update_command_successfully(): void
    {
        $commandId = 'existing_command';
        $commandConfig = [
            'description' => 'Updated description'
        ];

        // Mock configRepo updating command
        $this->mockConfigRepo
            ->shouldReceive('updateCommand')
            ->once()
            ->with($commandId, $commandConfig)
            ->andReturn(true);

        // Mock registry reload
        $this->mockRegistry
            ->shouldReceive('reloadCommands')
            ->once();

        // Mock cache clearByPattern
        $this->mockCache
            ->shouldReceive('clearByPattern')
            ->once()
            ->with($commandId);

        $result = $this->unifiedCommandSystem->updateCommand($commandId, $commandConfig);

        $this->assertTrue($result->isSuccess());
        $this->assertEquals('command_updated', $result->getType());
        $this->assertEquals($commandId, $result->getData()['command_id']);
    }

    /**
     * Test removeCommand successfully
     */
    public function test_remove_command_successfully(): void
    {
        $commandId = 'command_to_remove';

        // Mock configRepo removing command
        $this->mockConfigRepo
            ->shouldReceive('removeCommand')
            ->once()
            ->with($commandId)
            ->andReturn(true);

        // Mock registry reload
        $this->mockRegistry
            ->shouldReceive('reloadCommands')
            ->once();

        // Mock cache clearByPattern
        $this->mockCache
            ->shouldReceive('clearByPattern')
            ->once()
            ->with($commandId);

        $result = $this->unifiedCommandSystem->removeCommand($commandId);

        $this->assertTrue($result->isSuccess());
        $this->assertEquals('command_removed', $result->getType());
        $this->assertEquals($commandId, $result->getData()['command_id']);
    }

    /**
     * Test getCommandStats successfully
     */
    public function test_get_command_stats_successfully(): void
    {
        $cacheStats = ['hits' => 100, 'misses' => 50, 'hit_rate' => 0.67];
        $learningStats = ['total_patterns' => 25, 'avg_confidence' => 0.85];
        $commandStats = ['total' => 15, 'active' => 12, 'inactive' => 3];

        // Mock cache stats
        $this->mockCache
            ->shouldReceive('getStats')
            ->once()
            ->andReturn($cacheStats);

        // Mock learning stats
        $this->mockLearning
            ->shouldReceive('getLearningStats')
            ->once()
            ->andReturn($learningStats);

        // Mock configRepo stats
        $this->mockConfigRepo
            ->shouldReceive('getCommandsStats')
            ->once()
            ->andReturn($commandStats);

        $result = $this->unifiedCommandSystem->getCommandStats();

        $this->assertArrayHasKey('cache', $result);
        $this->assertArrayHasKey('learning', $result);
        $this->assertArrayHasKey('commands', $result);
        $this->assertArrayHasKey('total_processed', $result);
        $this->assertArrayHasKey('cache_efficiency', $result);
        $this->assertEquals(150, $result['total_processed']);
        $this->assertEquals(0.67, $result['cache_efficiency']);
    }

    /**
     * Test searchCommands successfully
     */
    public function test_search_commands_successfully(): void
    {
        $query = 'test';
        $searchResults = ['command1', 'command2'];

        // Mock registry searchCommands
        $this->mockRegistry
            ->shouldReceive('searchCommands')
            ->once()
            ->with($query)
            ->andReturn($searchResults);

        $result = $this->unifiedCommandSystem->searchCommands($query);

        $this->assertEquals($searchResults, $result);
    }

    /**
     * Test getCommandsByCategory successfully
     */
    public function test_get_commands_by_category_successfully(): void
    {
        $category = 'general';
        $categoryCommands = ['command1', 'command2'];

        // Mock registry getCommandsByCategory
        $this->mockRegistry
            ->shouldReceive('getCommandsByCategory')
            ->once()
            ->with($category)
            ->andReturn($categoryCommands);

        $result = $this->unifiedCommandSystem->getCommandsByCategory($category);

        $this->assertEquals($categoryCommands, $result);
    }

    /**
     * Test getCommandsByPermission successfully
     */
    public function test_get_commands_by_permission_successfully(): void
    {
        $userPermissions = ['user', 'admin'];
        $permissionCommands = ['command1', 'command2'];

        // Mock registry getCommandsByPermission
        $this->mockRegistry
            ->shouldReceive('getCommandsByPermission')
            ->once()
            ->with($userPermissions)
            ->andReturn($permissionCommands);

        $result = $this->unifiedCommandSystem->getCommandsByPermission($userPermissions);

        $this->assertEquals($permissionCommands, $result);
    }

    /**
     * Test CommandResult class methods
     */
    public function test_command_result_class_methods(): void
    {
        $commandResult = new \App\Services\Telegram\Commands\CommandResult(
            success: true,
            message: 'Test message',
            data: ['test' => 'data'],
            type: 'test_type',
            commandMatch: $this->mockCommandMatch
        );

        $this->assertTrue($commandResult->isSuccess());
        $this->assertEquals('Test message', $commandResult->message);
        $this->assertEquals(['test' => 'data'], $commandResult->getData());
        $this->assertEquals('test_type', $commandResult->getType());
        $this->assertEquals($this->mockCommandMatch, $commandResult->getCommandMatch());

        $arrayResult = $commandResult->toArray();
        $this->assertArrayHasKey('success', $arrayResult);
        $this->assertArrayHasKey('message', $arrayResult);
        $this->assertArrayHasKey('type', $arrayResult);
        $this->assertArrayHasKey('data', $arrayResult);
        $this->assertArrayHasKey('command_match', $arrayResult);
    }

    /**
     * Test CommandResult with null commandMatch
     */
    public function test_command_result_with_null_command_match(): void
    {
        $commandResult = new \App\Services\Telegram\Commands\CommandResult(
            success: false,
            message: 'Error message',
            data: ['error' => 'test error'],
            type: 'error',
            commandMatch: null
        );

        $this->assertFalse($commandResult->isSuccess());
        $this->assertNull($commandResult->getCommandMatch());

        $arrayResult = $commandResult->toArray();
        $this->assertNull($arrayResult['command_match']);
    }
}
