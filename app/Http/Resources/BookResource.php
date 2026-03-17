<?php

namespace App\Http\Resources;

use App\Models\Requisition;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $isAdmin = $request->user()?->hasRole('Admin') ?? false;
        $availableStock = (int) $this->resource->available_stock;

        $base = [
            'id' => $this->id,
            'title' => $this->name,
            'name' => $this->name,
            'isbn' => $this->isbn,
            'isbn_13' => $this->isbn_13,
            'google_volume_id' => $this->google_volume_id,
            'bibliography' => $this->bibliography,
            'published_date' => $this->published_date,
            'price' => $this->price !== null ? (float) $this->price : null,
            'discount' => $this->discount ?? 0,
            'pages' => $this->pages,
            'language' => $this->language,
            'dimensions' => $this->dimensions,

            'publisher' => [
                'id' => $this->publisher?->id,
                'name' => $this->publisher?->name,
            ],

            'authors' => $this->authors->map(fn ($author) => [
                'id' => $author->id,
                'name' => $author->name,
            ]),

            'cover_url' => $this->cover
                ? asset('storage/'.$this->cover)
                : $this->thumbnail_url,

            'is_available' => (isset($this->active_requisitions_count)
                ? $this->active_requisitions_count == 0
                : $this->resource->isAvailable())
                && $this->resource->available_stock > 0,
            'has_pending_availability_alert' => (bool) ($this->resource->getAttribute('has_pending_availability_alert') ?? false),
            'has_subscribed_availability_alert' => (bool) ($this->resource->getAttribute('has_subscribed_availability_alert') ?? false),
            'can_subscribe_availability_alert' => $request->user()?->hasRole('Cidadao') ?? false,

            'is_favorite' => (bool) ($this->resource->getAttribute('is_favorite') ?? false),
            'has_pdf' => ! empty($this->file_path),
            'can_download' => $this->canDownload($request),
            'reviews' => $this->whenLoaded('reviews', function () {
                return $this->reviews->map(fn ($review) => [
                    'id' => $review->id,
                    'rating' => $review->rating,
                    'comment' => $review->comment,
                    'status' => $review->status,
                    'user' => [
                        'id' => $review->user?->id,
                        'name' => $review->user?->name,
                    ],
                ]);
            }, []),
            'related_books' => collect($this->resource->getAttribute('related_books') ?? [])->map(function ($relatedBook) {
                return [
                    'id' => $relatedBook->id,
                    'title' => $relatedBook->name,
                    'author' => $relatedBook->authors->first()?->name ?? '-',
                    'cover_url' => $relatedBook->cover
                        ? asset('storage/'.$relatedBook->cover)
                        : $relatedBook->thumbnail_url,
                    'score' => (int) ($relatedBook->related_score ?? 0),
                ];
            })->values(),
        ];

        // Stock e quantidade só para administradores; clientes veem apenas disponibilidade (sim/não)
        $base['available'] = $availableStock > 0;
        if ($isAdmin) {
            $base['stock'] = (int) ($this->stock ?? 0);
            $base['available_stock'] = $availableStock;
        }

        return $base;
    }

    private function canDownload(Request $request): bool
    {
        if (empty($this->file_path)) {
            return false;
        }

        $user = $request->user();
        if (! $user) {
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
