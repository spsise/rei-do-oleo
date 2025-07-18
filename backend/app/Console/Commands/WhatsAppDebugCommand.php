<?php

namespace App\Console\Commands;

use App\Services\WhatsAppService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class WhatsAppDebugCommand extends Command
{
    /**
     * The name and signature of the console command
     *
     * @var string
     */
    protected $signature = 'whatsapp:debug';

    /**
     * The console command description
     *
     * @var string
     */
    protected $description = 'Debug WhatsApp configuration and connection';

    /**
     * Execute the console command
     */
    public function handle(): int
    {
        $this->info('🔍 WhatsApp Debug Information');
        $this->newLine();

        // Get configuration
        $apiUrl = config('services.whatsapp.api_url');
        $accessToken = config('services.whatsapp.access_token');
        $phoneNumberId = config('services.whatsapp.phone_number_id');
        $version = config('services.whatsapp.version');

        $this->line("📡 API URL: {$apiUrl}");
        $this->line("🔑 Access Token: " . substr($accessToken, 0, 10) . "...");
        $this->line("📱 Phone Number ID: {$phoneNumberId}");
        $this->line("📋 API Version: {$version}");

        $this->newLine();

        // Test connection to get phone number info
        $this->info('🔗 Testing connection to get phone number info...');

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
            ])->get("{$apiUrl}/{$version}/{$phoneNumberId}");

            if ($response->successful()) {
                $data = $response->json();

                $this->info('✅ Connection successful!');
                $this->line("📞 Phone Number: " . ($data['phone_number'] ?? 'N/A'));
                $this->line("📝 Verified Name: " . ($data['verified_name'] ?? 'N/A'));
                $this->line("✅ Code Verification Status: " . ($data['code_verification_status'] ?? 'N/A'));
                $this->line("📊 Quality Rating: " . ($data['quality_rating'] ?? 'N/A'));

                if (isset($data['quality_rating'])) {
                    $this->newLine();
                    $this->warn('⚠️  Quality Rating Issues:');
                    $this->line("   - GREEN: Good quality");
                    $this->line("   - YELLOW: Some issues");
                    $this->line("   - RED: Major issues");
                    $this->line("   - UNKNOWN: Not rated yet");
                }

            } else {
                $this->error('❌ Connection failed!');
                $this->error('Status: ' . $response->status());
                $this->error('Response: ' . $response->body());

                // Check if it's a phone number ID issue
                if (strlen($phoneNumberId) <= 15) {
                    $this->newLine();
                    $this->warn('⚠️  Phone Number ID Issue:');
                    $this->line("   The Phone Number ID looks like a phone number.");
                    $this->line("   It should be a long numeric ID from Facebook Developers.");
                    $this->line("   Current value: {$phoneNumberId}");
                    $this->line("   Expected format: 123456789012345 (15+ digits)");
                }
            }

        } catch (\Exception $e) {
            $this->error('❌ Exception: ' . $e->getMessage());
        }

        $this->newLine();

        // Test message sending
        $this->info('📤 Testing message sending...');

        try {
            $whatsappService = app(WhatsAppService::class);
            $result = $whatsappService->sendTextMessage('5511996994400', 'Teste de debug - ' . now()->format('H:i:s'));

            if ($result['success']) {
                $this->info('✅ Message sent successfully!');
                $this->line("📨 Message ID: " . ($result['message_id'] ?? 'N/A'));

                // Check message status
                if (isset($result['message_id'])) {
                    $this->newLine();
                    $this->info('📊 Checking message status...');

                    $statusResponse = Http::withHeaders([
                        'Authorization' => 'Bearer ' . $accessToken,
                    ])->get("{$apiUrl}/{$version}/{$result['message_id']}");

                    if ($statusResponse->successful()) {
                        $statusData = $statusResponse->json();
                        $this->line("📈 Status: " . ($statusData['status'] ?? 'N/A'));
                    }
                }

            } else {
                $this->error('❌ Message sending failed!');
                $this->error('Error: ' . json_encode($result['error'] ?? 'Unknown error'));
            }

        } catch (\Exception $e) {
            $this->error('❌ Exception sending message: ' . $e->getMessage());
        }

        $this->newLine();
        $this->info('🎯 Debug completed!');

        return 0;
    }
}
