<?php

namespace App\Console\Commands;

use App\Services\WhatsAppService;
use Illuminate\Console\Command;

class TestWhatsAppCommand extends Command
{
    /**
     * The name and signature of the console command
     *
     * @var string
     */
    protected $signature = 'whatsapp:test
                            {--message= : Custom message to send}
                            {--phone= : Phone number to send to (optional)}
                            {--deploy : Test deploy notification}';

    /**
     * The console command description
     *
     * @var string
     */
    protected $description = 'Test WhatsApp functionality';

    /**
     * Execute the console command
     */
    public function handle(WhatsAppService $whatsappService): int
    {
        $this->info('🚀 Testing WhatsApp functionality...');

        // Test connection first
        $this->info('Testing connection...');
        $connectionResult = $whatsappService->testConnection();

        if (!$connectionResult['success']) {
            $this->error('❌ WhatsApp connection failed:');
            $this->error(json_encode($connectionResult, JSON_PRETTY_PRINT));
            return 1;
        }

        $this->info('✅ WhatsApp connection successful!');
        $this->info('Phone Number: ' . ($connectionResult['phone_number'] ?? 'N/A'));
        $this->info('Verified Name: ' . ($connectionResult['verified_name'] ?? 'N/A'));

        // Test deploy notification
        if ($this->option('deploy')) {
            $this->info('Testing deploy notification...');

            $deployData = [
                'status' => 'success',
                'branch' => 'hostinger-hom',
                'commit' => 'abc123def456',
                'message' => 'Test deploy notification',
                'timestamp' => now()->format('d/m/Y H:i:s'),
                'output' => 'Deploy completed successfully'
            ];

            $result = $whatsappService->sendDeployNotification($deployData);

            if ($result['success']) {
                $this->info('✅ Deploy notification sent successfully!');
                $this->info("Sent to: {$result['sent_to']}/{$result['total_recipients']} recipients");
            } else {
                $this->error('❌ Deploy notification failed:');
                $this->error(json_encode($result, JSON_PRETTY_PRINT));
                return 1;
            }
        }

        // Test custom message
        if ($message = $this->option('message')) {
            $phone = $this->option('phone');

            if (!$phone) {
                $this->error('❌ Phone number is required for custom message');
                return 1;
            }

            $this->info("Testing custom message to {$phone}...");

            $result = $whatsappService->sendTextMessage($phone, $message);

            if ($result['success']) {
                $this->info('✅ Custom message sent successfully!');
                $this->info('Message ID: ' . ($result['message_id'] ?? 'N/A'));
            } else {
                $this->error('❌ Custom message failed:');
                $this->error(json_encode($result, JSON_PRETTY_PRINT));
                return 1;
            }
        }

        $this->info('🎉 All tests completed successfully!');
        return 0;
    }
}
