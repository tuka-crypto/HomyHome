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
    {return [
            'id'            => $this->id,
            'city'          => $this->city,
            'country'       => $this->country,
            'address'       => $this->address,
            'price'         => $this->price,
            'description'   => $this->description,
            'is_available'  => $this->is_available,
            'number_of_room'=> $this->number_of_room,
            'space'         => $this->space,
            'status'        => $this->status,
            'is_favorite'   => $this->is_favorite,
            'images'          => $this->images->map(function ($image) {
                return [
                    'id'         => $image->id,
                    'image_path' => asset('storage/' . $image->image_path),
                ];}),
            'owner'         => [
                'id'         => $this->owner->id,
                'first_name' => $this->owner->first_name,
                'last_name'  => $this->owner->last_name,
            ],
            'created_at'    => $this->created_at->toDateString(),
        ];
    }
}
