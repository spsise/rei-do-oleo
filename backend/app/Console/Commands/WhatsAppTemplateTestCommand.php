<?php

namespace App\Console\Commands;

use App\Services\WhatsAppService;
use Illuminate\Console\Command;

class WhatsAppTemplateTestCommand extends Command
{
    /**
     * The name and signature of the console command
     *
     * @var string
     */
    protected $signature = 'whatsapp:test-template {phone}';

    /**
     * The console command description
     *
     * @var string
     */
    protected $description = 'Test WhatsApp template message';

    /**
     * Execute the console command
     */
    public function handle(): int
    {
        $phone = $this->argument('phone');

        $this->info("📤 Testing template message to: {$phone}");

        try {
            $whatsappService = app(WhatsAppService::class);

            // Test with template message (more reliable)
            $result = $whatsappService->sendTemplateMessage(
                $phone,
                'hello_world', // Default template
                []
            );

            if ($result['success']) {
                $this->info('✅ Template message sent successfully!');
                $this->line("📨 Message ID: " . ($result['message_id'] ?? 'N/A'));
            } else {
                $this->error('❌ Template message failed!');
                $this->error('Error: ' . json_encode($result['error'] ?? 'Unknown error'));
            }

        } catch (\Exception $e) {
            $this->error('❌ Exception: ' . $e->getMessage());
        }

        return 0;
    }
}
