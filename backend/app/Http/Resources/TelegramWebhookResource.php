<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TelegramWebhookResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'status' => $this->resource['status'] ?? 'success',
            'message' => $this->resource['message'] ?? 'Webhook processed successfully',
            'data' => $this->resource['data'] ?? null,
            'result' => $this->resource['result'] ?? null,
            'timestamp' => now()->toISOString(),
        ];
    }

    /**
     * Create a success response
     */
    public static function success(string $message = 'Webhook processed successfully', array $data = []): static
    {
        return new static([
            'status' => 'success',
            'message' => $message,
            'data' => $data,
        ]);
    }

    /**
     * Create an error response
     */
    public static function error(string $message = 'Webhook processing failed', array $data = []): static
    {
        return new static([
            'status' => 'error',
            'message' => $message,
            'data' => $data,
        ]);
    }

    /**
     * Create an ignored response
     */
    public static function ignored(string $message = 'Webhook ignored', array $data = []): static
    {
        return new static([
            'status' => 'ignored',
            'message' => $message,
            'data' => $data,
        ]);
    }
}
