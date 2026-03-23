<?php

namespace App\Http\Requests;

use App\Models\ChatRoom;
use App\Models\DirectConversation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MarkMessagesReadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    public function rules(): array
    {
        return [
            'target_type' => ['required', 'string', Rule::in(['room', 'conversation'])],
            'target_id' => ['required', 'integer'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $targetType = $this->input('target_type');
            $targetId = (int) $this->input('target_id');
            $user = $this->user();

            if ($targetType === 'room') {
                $room = ChatRoom::query()->find($targetId);
                if (! $room) {
                    $validator->errors()->add('target_id', 'Sala não encontrada.');
                    return;
                }

                $isMember = $room->users()->where('users.id', $user->id)->exists();
                if (! $isMember) {
                    $validator->errors()->add('target_id', 'Não autorizado para esta sala.');
                }
            }

            if ($targetType === 'conversation') {
                $conversation = DirectConversation::query()->find($targetId);
                if (! $conversation) {
                    $validator->errors()->add('target_id', 'Conversa não encontrada.');
                    return;
                }

                $allowed = $conversation->user_one_id === $user->id || $conversation->user_two_id === $user->id;
                if (! $allowed) {
                    $validator->errors()->add('target_id', 'Não autorizado para esta conversa.');
                }
            }
        });
    }
}
