<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Contracts\LoggingServiceInterface;

class TelegramWebhookRequest extends FormRequest
{
    public function __construct(
        private LoggingServiceInterface $loggingService
    ) {
        parent::__construct();
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Webhook requests are always authorized
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'update_id' => 'required|integer',
            'message' => 'sometimes|array',
            'callback_query' => 'sometimes|array',
            'message.chat.id' => 'required_with:message|integer',
            'message.from.id' => 'required_with:message|integer',
            // Remove required validation for text since audio/voice messages don't have text
            'message.text' => 'sometimes|string',
            // Add validation for voice messages
            'message.voice' => 'sometimes|array',
            'message.voice.file_id' => 'required_with:message.voice|string',
            'message.voice.duration' => 'sometimes|integer',
            'message.voice.mime_type' => 'sometimes|string',
            // Add validation for audio messages
            'message.audio' => 'sometimes|array',
            'message.audio.file_id' => 'required_with:message.audio|string',
            'message.audio.duration' => 'sometimes|integer',
            'message.audio.title' => 'sometimes|string',
            'message.audio.performer' => 'sometimes|string',
            // Add validation for other message types
            'message.photo' => 'sometimes|array',
            'message.document' => 'sometimes|array',
            'message.video' => 'sometimes|array',
            'message.sticker' => 'sometimes|array',
            'message.location' => 'sometimes|array',
            'message.contact' => 'sometimes|array',
            'callback_query.id' => 'required_with:callback_query|string',
            'callback_query.data' => 'required_with:callback_query|string',
            'callback_query.message.chat.id' => 'required_with:callback_query|integer',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'update_id.required' => 'Update ID is required',
            'update_id.integer' => 'Update ID must be an integer',
            'message.array' => 'Message must be an array',
            'callback_query.array' => 'Callback query must be an array',
            'message.chat.id.required_with' => 'Chat ID is required when message is present',
            'message.chat.id.integer' => 'Chat ID must be an integer',
            'message.from.id.required_with' => 'User ID is required when message is present',
            'message.from.id.integer' => 'User ID must be an integer',
            'message.text.string' => 'Message text must be a string',
            'message.voice.array' => 'Voice message must be an array',
            'message.voice.file_id.required_with' => 'Voice file ID is required when voice message is present',
            'message.voice.file_id.string' => 'Voice file ID must be a string',
            'message.voice.duration.integer' => 'Voice duration must be an integer',
            'message.voice.mime_type.string' => 'Voice MIME type must be a string',
            'message.audio.array' => 'Audio message must be an array',
            'message.audio.file_id.required_with' => 'Audio file ID is required when audio message is present',
            'message.audio.file_id.string' => 'Audio file ID must be a string',
            'message.audio.duration.integer' => 'Audio duration must be an integer',
            'message.audio.title.string' => 'Audio title must be a string',
            'message.audio.performer.string' => 'Audio performer must be a string',
            'callback_query.id.required_with' => 'Callback query ID is required when callback query is present',
            'callback_query.data.required_with' => 'Callback data is required when callback query is present',
            'callback_query.message.chat.id.required_with' => 'Chat ID is required when callback query is present',
        ];
    }

    /**
     * Determine if the request expects JSON.
     *
     * @return bool
     */
    public function expectsJson(): bool
    {
        return true; // Always expect JSON for webhook requests
    }

    /**
     * Determine if the request is asking for JSON.
     *
     * @return bool
     */
    public function wantsJson(): bool
    {
        return true; // Always want JSON for webhook requests
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator): void
    {
        // Log detalhes da validação falhada
        $this->loggingService->logTelegramEvent('telegram_webhook_validation_failed', [
            'error' => 'Webhook validation failed',
            'validation_errors' => $validator->errors()->toArray(),
            'request_data' => $this->all(),
            'request_headers' => $this->headers->all(),
            'ip' => $this->ip(),
            'user_agent' => $this->userAgent(),
            'timestamp' => now()->toISOString(),
            'validation_rules' => $this->rules(),
            'failed_fields' => array_keys($validator->errors()->toArray()),
            'request_size' => strlen($this->getContent()),
            'content_type' => $this->header('Content-Type'),
            'telegram_update_id' => $this->input('update_id'),
            'message_type' => $this->getMessageType(),
            'has_message' => $this->has('message'),
            'has_callback_query' => $this->has('callback_query')
        ], 'error');

        // Chamar o método padrão do Laravel para lançar a exceção
        parent::failedValidation($validator);
    }

    /**
     * Get the message type from the request
     */
    private function getMessageType(): string
    {
        if ($this->has('callback_query')) {
            return 'callback_query';
        }

        if ($this->has('message')) {
            $message = $this->input('message', []);

            if (isset($message['text'])) {
                return 'text_message';
            }
            if (isset($message['voice'])) {
                return 'voice_message';
            }
            if (isset($message['audio'])) {
                return 'audio_message';
            }
            if (isset($message['photo'])) {
                return 'photo_message';
            }
            if (isset($message['document'])) {
                return 'document_message';
            }
            if (isset($message['video'])) {
                return 'video_message';
            }
            if (isset($message['sticker'])) {
                return 'sticker_message';
            }
            if (isset($message['location'])) {
                return 'location_message';
            }
            if (isset($message['contact'])) {
                return 'contact_message';
            }

            return 'unknown_message_type';
        }

        return 'no_message';
    }

    /**
     * Log validation attempt for debugging
     */
    public function validateResolved(): void
    {
        // Log successful validation
        // $this->loggingService->logTelegramEvent('telegram_webhook_validation_success', [
        //     'success' => 'Webhook validation passed successfully',
        //     'message_type' => $this->getMessageType(),
        //     'telegram_update_id' => $this->input('update_id'),
        //     'has_message' => $this->has('message'),
        //     'has_callback_query' => $this->has('callback_query'),
        //     'message_content_types' => $this->getMessageContentTypes(),
        //     'timestamp' => now()->toISOString()
        // ], 'info');

        parent::validateResolved();
    }

    /**
     * Get all content types present in the message
     */
    private function getMessageContentTypes(): array
    {
        if (!$this->has('message')) {
            return [];
        }

        $message = $this->input('message', []);
        $contentTypes = [];

        if (isset($message['text'])) {
            $contentTypes[] = 'text';
        }
        if (isset($message['voice'])) {
            $contentTypes[] = 'voice';
        }
        if (isset($message['audio'])) {
            $contentTypes[] = 'audio';
        }
        if (isset($message['photo'])) {
            $contentTypes[] = 'photo';
        }
        if (isset($message['document'])) {
            $contentTypes[] = 'document';
        }
        if (isset($message['video'])) {
            $contentTypes[] = 'video';
        }
        if (isset($message['sticker'])) {
            $contentTypes[] = 'sticker';
        }
        if (isset($message['location'])) {
            $contentTypes[] = 'location';
        }
        if (isset($message['contact'])) {
            $contentTypes[] = 'contact';
        }

        return $contentTypes;
    }
}
