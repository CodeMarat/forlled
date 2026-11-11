<?php

namespace App\Http\Requests\Api;

class LocationIndexRequest extends PaginatedIndexRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'state' => ['nullable', 'string', 'size:2'],
            'search' => ['nullable', 'string', 'max:100'],
        ]);
    }
}
