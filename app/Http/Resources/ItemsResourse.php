<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ItemsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "title" => $this->title,
            "description" => $this->description,
            "item_image" => $this->item_image,
            "code" => $this->code,
            "pro_date" => $this->pro_date,
            "exp_date" => $this->exp_date,
            "start_reminder" => $this->start_reminder,
            "type" => $this->type,
        ];
    }
}