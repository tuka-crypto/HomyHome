<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'             => $this->id,
            'booking_number' => $this->booking_number,
            'apartment'      => $this->apartment,
            'start_date'     => $this->start_date,
            'end_date'       => $this->end_date,
            'guest_count'    => $this->guest_count,
            'total_price'    => $this->total_price,
            'status'         => $this->status,
            'owner_approved' => $this->owner_approved,
            'tenant'         => [
                'id'         => $this->tenant->id,
                'first_name' => $this->tenant->first_name,
                'last_name'  => $this->tenant->last_name,
            ],
            'created_at'     => $this->created_at,
            'updated_at'     => $this->updated_at,
        ];
    }
}