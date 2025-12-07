<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class GuideResource extends JsonResource
{
    /**
 * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Convert model to array
        $data = parent::toArray($request);

        // Safely add custom column
        $data['excerpt'] = mb_substr($this->content, 0, 50);
        $data['image_public'] = $data['image']
            ? asset('storage') . '/' . $data['image']
            : null;

        return $data;
    }
}
