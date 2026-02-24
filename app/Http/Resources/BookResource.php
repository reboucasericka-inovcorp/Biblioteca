<?php

namespace App\Http\Resources;

use App\Models\Requisition;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'isbn' => $this->isbn,
            'isbn_13' => $this->isbn_13,
            'google_volume_id' => $this->google_volume_id,
            'bibliography' => $this->bibliography,
            'published_date' => $this->published_date,
            'price' => $this->price,

            'publisher' => [
                'id' => $this->publisher?->id,
                'name' => $this->publisher?->name,
            ],

            'authors' => $this->authors->map(fn ($author) => [
                'id' => $author->id,
                'name' => $author->name,
            ]),

            'cover_url' => $this->cover
                ? asset('storage/' . $this->cover)
                : $this->thumbnail_url,

            'is_available' => isset($this->active_requisitions_count)
                ? $this->active_requisitions_count == 0
                : $this->resource->isAvailable(),

            'has_pdf' => !empty($this->file_path),
            'can_download' => $this->canDownload($request),
        ];
    }

    private function canDownload(Request $request): bool
    {
        if (empty($this->file_path)) {
            return false;
        }

        $user = $request->user();
        if (!$user) {
            return false;
        }

        if ($user->hasRole('Admin')) {
            return true;
        }

        return Requisition::where('book_id', $this->id)
            ->where('user_id', $user->id)
            ->where('status', Requisition::STATUS_ACTIVE)
            ->exists();
    }
}