<?php

namespace App\Contracts\Telegram;

interface TelegramMenuBuilderInterface
{
    /**
     * Build menu
     */
    public function build(int $chatId, array $params = []): array;

    /**
     * Get menu type
     */
    public function getMenuType(): string;

    /**
     * Get menu name
     */
    public function getMenuName(): string;
}
