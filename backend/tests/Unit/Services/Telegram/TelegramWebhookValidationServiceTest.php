<?php

namespace Tests\Unit\Services\Telegram;

use App\Services\Telegram\TelegramWebhookValidationService;
use App\Contracts\LoggingServiceInterface;
use Mockery;
use PHPUnit\Framework\TestCase;

class TelegramWebhookValidationServiceTest extends TestCase
{
    private TelegramWebhookValidationService $service;
    private LoggingServiceInterface $loggingService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loggingService = Mockery::mock(LoggingServiceInterface::class);
        $this->service = new TelegramWebhookValidationService($this->loggingService);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_validate_and_mark_processing_without_update_id_returns_true()
    {
        $result = $this->service->validateAndMarkProcessing(null);

        $this->assertTrue($result);
    }

    public function test_service_can_be_instantiated()
    {
        $this->assertInstanceOf(TelegramWebhookValidationService::class, $this->service);
    }

    public function test_service_has_required_methods()
    {
        $this->assertTrue(method_exists($this->service, 'validateAndMarkProcessing'));
        $this->assertTrue(method_exists($this->service, 'cleanupProcessedUpdate'));
        $this->assertTrue(method_exists($this->service, 'getCacheStats'));
    }
}
