<?php

namespace App\Services\Telegram;

class TelegramAuthorizationService
{
    /**
     * Check if user is authorized
     */
    public function isAuthorizedUser(int $chatId): bool
    {
        $authorizedUsers = config('services.telegram.recipients', []);
        return in_array($chatId, $authorizedUsers);
    }

    /**
     * Get authorized users list
     */
    public function getAuthorizedUsers(): array
    {
        return config('services.telegram.recipients', []);
    }

    /**
     * Add authorized user
     */
    public function addAuthorizedUser(int $chatId): bool
    {
        $authorizedUsers = $this->getAuthorizedUsers();

        if (!in_array($chatId, $authorizedUsers)) {
            $authorizedUsers[] = $chatId;
            // Here you would typically save to database or config
            return true;
        }

        return false;
    }

    /**
     * Remove authorized user
     */
    public function removeAuthorizedUser(int $chatId): bool
    {
        $authorizedUsers = $this->getAuthorizedUsers();

        if (in_array($chatId, $authorizedUsers)) {
            $authorizedUsers = array_diff($authorizedUsers, [$chatId]);
            // Here you would typically save to database or config
            return true;
        }

        return false;
    }
}
