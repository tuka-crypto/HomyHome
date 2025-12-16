<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApartmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'city'            => $this->city,
            'country'         => $this->country,
            'address'         => $this->address,
            'price'           => $this->price,
            'number_of_room'  => $this->number_of_room,
            'space'           => $this->space,
            'description'     => $this->description,
            'is_available'    => (bool) $this->is_available,
            'status'          => $this->status,
            'owner_id'        => $this->owner_id,
            'images'          => $this->images->map(function ($image) {
                return [
                    'id'         => $image->id,
                    'image_path' => asset('storage/' . $image->image_path),
                ];
            }),
            'created_at'      => $this->created_at,
            'updated_at'      => $this->updated_at,
        ];

    }
}
