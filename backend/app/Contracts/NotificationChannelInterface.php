<?php

namespace App\Contracts;

interface NotificationChannelInterface
{
    /**
     * Send text message
     *
     * @param string $message
     * @param string|null $recipient
     * @return array
     */
    public function sendTextMessage(string $message, ?string $recipient = null): array;

    /**
     * Send notification with data
     *
     * @param array $data
     * @return array
     */
    public function sendNotification(array $data): array;

    /**
     * Test connection
     *
     * @return array
     */
    public function testConnection(): array;

    /**
     * Get channel name
     *
     * @return string
     */
    public function getChannelName(): string;

    /**
     * Check if channel is enabled
     *
     * @return bool
     */
    public function isEnabled(): bool;
}
