<?php

namespace App\Modules\API\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'avatar' => $this->avatar ? url('storage/'.$this->avatar) : null,
            'admission_number' => $this->admission_number,
            'employee_id' => $this->employee_id,
            'roles' => $this->whenLoaded('roles', fn () => $this->roles->pluck('name')),
            'permissions' => $this->whenLoaded('roles', fn () => $this->getAllPermissions()->pluck('name')),
            'department' => $this->whenLoaded('department', fn () => $this->department ? [
                'id' => $this->department->id,
                'name' => $this->department->name,
            ] : null),
            'program' => $this->whenLoaded('program', fn () => $this->program ? [
                'id' => $this->program->id,
                'name' => $this->program->name,
            ] : null),
            'is_active' => $this->is_active,
            'two_factor_enabled' => $this->two_factor_enabled ?? false,
            'email_verified_at' => $this->email_verified_at?->toIso8601String(),
            'last_login_at' => $this->last_login_at?->toIso8601String(),
            'notification_preferences' => $this->notification_preferences,
            'member' => $this->whenLoaded('member', function () {
                if (! $this->member) {
                    return null;
                }

                return [
                    'id' => $this->member->id,
                    'member_id' => $this->member->member_id,
                    'full_name' => $this->member->full_name,
                    'status' => $this->member->status,
                    'membership_type' => $this->member->membership_type,
                    'expires_at' => $this->member->expires_at?->toDateString(),
                    'library_card' => $this->member->relationLoaded('libraryCard') && $this->member->libraryCard ? [
                        'card_number' => $this->member->libraryCard->card_number,
                        'status' => $this->member->libraryCard->status,
                        'issued_at' => $this->member->libraryCard->issued_at?->toDateString(),
                        'expires_at' => $this->member->libraryCard->expires_at?->toDateString(),
                        'qr_code' => $this->member->libraryCard->qr_code,
                        'barcode' => $this->member->libraryCard->barcode,
                    ] : null,
                ];
            }),
            'subscription' => $this->whenLoaded('activeSubscription', function () {
                if (! $this->activeSubscription) {
                    return null;
                }

                return [
                    'plan_name' => $this->activeSubscription->plan?->name,
                    'status' => $this->activeSubscription->status,
                    'end_date' => $this->activeSubscription->end_date?->toDateString(),
                    'auto_renew' => $this->activeSubscription->auto_renew,
                ];
            }),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
