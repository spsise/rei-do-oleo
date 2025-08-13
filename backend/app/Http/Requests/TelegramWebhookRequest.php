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
}
