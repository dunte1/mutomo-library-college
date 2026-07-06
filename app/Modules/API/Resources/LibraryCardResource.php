<?php

namespace App\Modules\API\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class LibraryCardResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'card_number' => $this->card_number,
            'status' => $this->status,
            'issued_at' => $this->issued_at?->toDateString(),
            'expires_at' => $this->expires_at?->toDateString(),
            'qr_code_svg' => $this->qr_code,
            'barcode' => $this->barcode,
            'member' => $this->whenLoaded('member', fn () => [
                'full_name' => $this->member->full_name,
                'member_id' => $this->member->member_id,
                'membership_type' => $this->member->membership_type,
                'photo' => $this->member->photo ? url('storage/'.$this->member->photo) : null,
                'department' => $this->member->relationLoaded('department') && $this->member->department
                    ? $this->member->department->name : null,
            ]),
        ];
    }
}
