<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TelegramWebhookRequest extends FormRequest
{
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
            'message.text' => 'required_with:message|string',
            'message.from.id' => 'required_with:message|integer',
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
            'message.text.required_with' => 'Message text is required when message is present',
            'message.text.string' => 'Message text must be a string',
            'callback_query.id.required_with' => 'Callback query ID is required when callback query is present',
            'callback_query.data.required_with' => 'Callback data is required when callback query is present',
            'callback_query.message.chat.id.required_with' => 'Chat ID is required when callback query is present',
        ];
    }
}
