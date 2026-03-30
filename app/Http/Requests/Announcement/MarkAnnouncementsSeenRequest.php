<?php

namespace App\Http\Requests\Announcement;

use Illuminate\Foundation\Http\FormRequest;

class MarkAnnouncementsSeenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'announcement_ids' => ['required', 'array', 'min:1'],
            'announcement_ids.*' => ['integer', 'exists:announcements,id'],
        ];
    }
}