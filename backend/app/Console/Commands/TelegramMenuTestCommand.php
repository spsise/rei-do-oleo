<?php

namespace App\Console\Commands;

use App\Services\TelegramBotService;
use Illuminate\Console\Command;

class TelegramMenuTestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:menu-test
                            {--chat-id= : Specific chat ID to send to}
                            {--menu=main : Menu to test (main, report, services, products, dashboard)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test interactive menus via Telegram bot';

    private TelegramBotService $telegramBotService;

    public function __construct(TelegramBotService $telegramBotService)
    {
        parent::__construct();
        $this->telegramBotService = $telegramBotService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $chatId = $this->option('chat-id');
        $menu = $this->option('menu');

        if (!$chatId) {
            $recipients = config('services.telegram.recipients', []);
            if (empty($recipients)) {
                $this->error('❌ No recipients configured');
                $this->info('Please set TELEGRAM_RECIPIENTS in your .env file or use --chat-id option');
                return 1;
            }
            $chatId = $recipients[0];
        }

        $this->info('🎮 Telegram Menu Test');
        $this->info('==================');
        $this->info("Chat ID: {$chatId}");
        $this->info("Menu: {$menu}");
        $this->info('');

        try {
            $result = $this->testMenu($chatId, $menu);

            if ($result['success']) {
                $this->info('✅ Menu sent successfully');
                $this->info("Message ID: " . ($result['message_id'] ?? 'N/A'));
            } else {
                $this->error('❌ Failed to send menu');
                $this->error("Error: " . ($result['error'] ?? 'Unknown error'));
                return 1;
            }

        } catch (\Exception $e) {
            $this->error('❌ Exception occurred: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }

    /**
     * Test specific menu
     */
    private function testMenu(string $chatId, string $menu): array
    {
        $message = [
            'chat' => ['id' => $chatId],
            'from' => [
                'id' => $chatId,
                'first_name' => 'CLI Test',
                'username' => 'cli_test'
            ]
        ];

        switch ($menu) {
            case 'main':
                $message['text'] = '/start';
                break;

            case 'report':
                $message['text'] = '/report';
                break;

            case 'services':
                $message['text'] = '/services';
                break;

            case 'products':
                $message['text'] = '/products';
                break;

            case 'dashboard':
                $message['text'] = '/dashboard';
                break;

            default:
                $message['text'] = '/start';
        }

        return $this->telegramBotService->processMessage($message);
    }
}
