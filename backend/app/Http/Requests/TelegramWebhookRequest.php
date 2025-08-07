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

    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new \Illuminate\Validation\ValidationException($validator, response()->json([
            'status' => 'error',
            'message' => 'Validation failed',
            'errors' => $validator->errors(),
            'code' => 422
        ], 422));
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
