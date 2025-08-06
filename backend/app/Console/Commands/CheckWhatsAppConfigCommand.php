<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckWhatsAppConfigCommand extends Command
{
    /**
     * The name and signature of the console command
     *
     * @var string
     */
    protected $signature = 'whatsapp:check-config';

    /**
     * The console command description
     *
     * @var string
     */
    protected $description = 'Check WhatsApp configuration';

    /**
     * Execute the console command
     */
    public function handle(): int
    {
        $this->info('🔍 Checking WhatsApp Configuration...');
        $this->newLine();

        // Check API URL
        $apiUrl = config('services.whatsapp.api_url');
        $this->line("📡 API URL: {$apiUrl}");

        // Check Access Token
        $accessToken = config('services.whatsapp.access_token');
        if ($accessToken) {
            $this->line("🔑 Access Token: " . substr($accessToken, 0, 10) . "...");
        } else {
            $this->error("❌ Access Token: NOT CONFIGURED");
        }

        // Check Phone Number ID
        $phoneNumberId = config('services.whatsapp.phone_number_id');
        if ($phoneNumberId) {
            $this->line("📱 Phone Number ID: {$phoneNumberId}");

            // Check if it looks like a phone number instead of ID
            if (strlen($phoneNumberId) <= 15 && is_numeric($phoneNumberId)) {
                $this->warn("⚠️  WARNING: Phone Number ID looks like a phone number!");
                $this->warn("   It should be a long numeric ID from Facebook Developers");
            }
        } else {
            $this->error("❌ Phone Number ID: NOT CONFIGURED");
        }

        // Check Version
        $version = config('services.whatsapp.version');
        $this->line("📋 API Version: {$version}");

        // Check Recipients
        $recipients = config('services.whatsapp.deploy_recipients', []);
        if (!empty($recipients)) {
            $this->line("👥 Deploy Recipients: " . implode(', ', $recipients));
        } else {
            $this->warn("⚠️  Deploy Recipients: NOT CONFIGURED");
        }

        $this->newLine();

        // Summary
        if ($accessToken && $phoneNumberId) {
            $this->info("✅ Configuration appears to be set up");
            $this->info("   Run 'php artisan whatsapp:test' to test connection");
        } else {
            $this->error("❌ Configuration incomplete");
            $this->error("   Please check your .env file");
        }

        return 0;
    }
}
